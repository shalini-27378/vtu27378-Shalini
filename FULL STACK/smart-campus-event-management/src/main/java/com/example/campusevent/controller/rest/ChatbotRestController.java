package com.example.campusevent.controller.rest;

import com.example.campusevent.entity.Event;
import com.example.campusevent.entity.Registration;
import com.example.campusevent.entity.User;
import com.example.campusevent.repository.UserRepository;
import com.example.campusevent.service.EventService;
import com.example.campusevent.service.RegistrationService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.http.*;
import org.springframework.security.core.Authentication;
import org.springframework.security.core.context.SecurityContextHolder;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.client.RestTemplate;

import java.time.LocalDateTime;
import java.time.temporal.ChronoUnit;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.stream.Collectors;

@RestController
@RequestMapping("/api/chat")
public class ChatbotRestController {

    @Value("${gemini.api.key:}")
    private String geminiApiKey;

    @Autowired
    private EventService eventService;

    @Autowired
    private RegistrationService registrationService;

    @Autowired
    private UserRepository userRepository;

    @PostMapping
    public ResponseEntity<?> handleChat(@RequestBody Map<String, String> payload) {
        String userMessage = payload.get("message");
        if (userMessage == null || userMessage.trim().isEmpty()) {
            return ResponseEntity.ok(Map.of("reply", "Please ask me a question."));
        }

        Authentication auth = SecurityContextHolder.getContext().getAuthentication();
        if (auth == null || !auth.isAuthenticated() || "anonymousUser".equals(auth.getPrincipal())) {
            return ResponseEntity.status(HttpStatus.UNAUTHORIZED).body(Map.of("reply", "Please log in to use the chatbot."));
        }

        String username = auth.getName();
        User currentUser = userRepository.findByUsernameOrEmail(username, username).orElse(null);
        if (currentUser == null) {
            return ResponseEntity.status(HttpStatus.UNAUTHORIZED).body(Map.of("reply", "User not found."));
        }

        String role = currentUser.getRole(); // "STUDENT" or "ADMIN"
        
        // Build Context
        String contextStr = buildContext(currentUser, role);

        // Check if Gemini API key exists
        if (geminiApiKey != null && !geminiApiKey.trim().isEmpty()) {
            return callGeminiApi(userMessage, role, contextStr);
        } else {
            return runFallbackLogic(userMessage, role, currentUser);
        }
    }

    private String buildContext(User user, String role) {
        StringBuilder sb = new StringBuilder();
        List<Event> allEvents = eventService.getAllEvents();
        
        if ("ADMIN".equals(role)) {
            sb.append("You are the Admin Assistant for SmartCampus. ");
            sb.append("Current System Events:\n");
            for (Event e : allEvents) {
                sb.append(String.format("- [%d] %s at %s on %s\n", e.getId(), e.getTitle(), e.getVenue(), e.getEventDate().toString()));
            }
        } else {
            sb.append("You are a Student Assistant for SmartCampus. ");
            sb.append("Current Available Events:\n");
            for (Event e : allEvents) {
                sb.append(String.format("- [%d] %s at %s on %s\n", e.getId(), e.getTitle(), e.getVenue(), e.getEventDate().toString()));
            }
            sb.append("\nStudent's Current Registrations:\n");
            List<Registration> registrations = registrationService.getRegistrationsByEmail(user.getEmail());
            if (registrations.isEmpty()) {
                sb.append("No registrations yet.\n");
            } else {
                for (Registration r : registrations) {
                    sb.append(String.format("- Registered for: %s (Event ID %d)\n", r.getEvent().getTitle(), r.getEvent().getId()));
                }
            }
        }
        sb.append("\nIMPORTANT RULES:\n");
        sb.append("1. An event lasts exactly 2 hours.\n");
        sb.append("2. An event 'clash' happens if two events overlap in time.\n");
        sb.append("3. Only answer questions related to campus events, scheduling, or clashes.\n");
        return sb.toString();
    }

    private ResponseEntity<?> callGeminiApi(String message, String role, String contextStr) {
        String prompt = contextStr + "\nUser Query: " + message + "\nResponse:";

        RestTemplate restTemplate = new RestTemplate();
        String url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=" + geminiApiKey;

        Map<String, Object> requestBody = new HashMap<>();
        List<Map<String, Object>> contents = new ArrayList<>();
        Map<String, Object> partsMap = new HashMap<>();
        Map<String, Object> textMap = new HashMap<>();
        
        textMap.put("text", prompt);
        partsMap.put("parts", List.of(textMap));
        contents.add(partsMap);
        requestBody.put("contents", contents);

        try {
            HttpHeaders headers = new HttpHeaders();
            headers.setContentType(MediaType.APPLICATION_JSON);
            HttpEntity<Map<String, Object>> entity = new HttpEntity<>(requestBody, headers);

            ResponseEntity<Map> response = restTemplate.postForEntity(url, entity, Map.class);
            Map<String, Object> responseBody = response.getBody();

            if (responseBody != null && responseBody.containsKey("candidates")) {
                List<Map<String, Object>> candidates = (List<Map<String, Object>>) responseBody.get("candidates");
                if (!candidates.isEmpty()) {
                    Map<String, Object> content = (Map<String, Object>) candidates.get(0).get("content");
                    List<Map<String, Object>> parts = (List<Map<String, Object>>) content.get("parts");
                    if (!parts.isEmpty()) {
                        String text = (String) parts.get(0).get("text");
                        return ResponseEntity.ok(Map.of("reply", text));
                    }
                }
            }
            return ResponseEntity.ok(Map.of("reply", "I couldn't generate a proper response at this time."));
        } catch (Exception e) {
            e.printStackTrace();
            return ResponseEntity.ok(Map.of("reply", "Gemini API Error: " + e.getMessage() + ". Check your API key."));
        }
    }

    private ResponseEntity<?> runFallbackLogic(String message, String role, User currentUser) {
        String lowerMsg = message.toLowerCase();
        StringBuilder reply = new StringBuilder();

        if (lowerMsg.contains("hi") || lowerMsg.contains("hello")) {
            reply.append("Hello! I am your rough fallback assistant. How can I help you with events today?");
        } else if (lowerMsg.contains("clash") || lowerMsg.contains("overlap")) {
            reply.append(detectClashes(role, currentUser));
        } else if (lowerMsg.contains("event")) {
            List<Event> events = eventService.getAllEvents();
            reply.append("We currently have ").append(events.size()).append(" events in the system. Check the events page for more details!");
        } else {
            reply.append("I am currently in Fallback Mode (No API Key). I only understand basic keywords like 'events' and 'clash'.");
        }

        return ResponseEntity.ok(Map.of("reply", reply.toString()));
    }

    private String detectClashes(String role, User user) {
        StringBuilder result = new StringBuilder();
        List<Event> allEvents = eventService.getAllEvents();

        if ("ADMIN".equals(role)) {
            // Check for venue/time clashes globally
            boolean clashFound = false;
            for (int i = 0; i < allEvents.size(); i++) {
                for (int j = i + 1; j < allEvents.size(); j++) {
                    Event e1 = allEvents.get(i);
                    Event e2 = allEvents.get(j);
                    if (e1.getVenue() != null && e1.getVenue().equalsIgnoreCase(e2.getVenue())) {
                        if (isOverlap(e1.getEventDate(), e2.getEventDate())) {
                            result.append(String.format("⚠️ CLASH DETECTED: '%s' and '%s' are both booked at '%s' around the same time!\n",
                                    e1.getTitle(), e2.getTitle(), e1.getVenue()));
                            clashFound = true;
                        }
                    }
                }
            }
            if (!clashFound) {
                result.append("✅ Great news Admin! There are no venue scheduling clashes right now.");
            }
        } else {
            // Check for student's personal clashes
            List<Registration> registrations = registrationService.getRegistrationsByEmail(user.getEmail());
            if (registrations.size() < 2) {
                return "✅ You don't have enough registrations for any clashes to occur!";
            }
            
            boolean clashFound = false;
            for (int i = 0; i < registrations.size(); i++) {
                for (int j = i + 1; j < registrations.size(); j++) {
                    Event e1 = registrations.get(i).getEvent();
                    Event e2 = registrations.get(j).getEvent();
                    if (isOverlap(e1.getEventDate(), e2.getEventDate())) {
                        result.append(String.format("⚠️ PERSONAL CLASH DETECTED: Your registered events '%s' and '%s' overlap in time!\n",
                                e1.getTitle(), e2.getTitle()));
                        clashFound = true;
                    }
                }
            }
            if (!clashFound) {
                result.append("✅ Great news! None of your registered events clash with each other.");
            }
        }
        return result.toString();
    }

    // Assume events are 2 hours long. Overlap if start times are within 2 hours of each other.
    private boolean isOverlap(LocalDateTime dt1, LocalDateTime dt2) {
        if (dt1 == null || dt2 == null) return false;
        long minutesDiff = Math.abs(ChronoUnit.MINUTES.between(dt1, dt2));
        return minutesDiff < 120;
    }
}
