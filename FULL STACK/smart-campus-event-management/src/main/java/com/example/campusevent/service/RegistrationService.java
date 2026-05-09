package com.example.campusevent.service;

import com.example.campusevent.entity.Event;
import com.example.campusevent.entity.Registration;
import com.example.campusevent.exception.OverbookingException;
import com.example.campusevent.repository.RegistrationRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.util.List;

@Service
@Transactional
public class RegistrationService {

    @Autowired
    private RegistrationRepository registrationRepository;

    @Autowired
    private EventService eventService;

    public Registration register(Registration registration, Long eventId) {
        Event event = eventService.getEventById(eventId);

        if (event.isFull()) {
            throw new OverbookingException("This event is fully booked.");
        }
        if (event.getAvailableSeats() < registration.getTicketsBooked()) {
            throw new OverbookingException("Only " + event.getAvailableSeats() + " seats available.");
        }
        if (registrationRepository.existsByEmailIgnoreCaseAndEventId(registration.getEmail(), eventId)) {
            throw new IllegalStateException("You have already registered for this event with this email.");
        }

        registration.setEvent(event);
        Registration saved = registrationRepository.save(registration);
        eventService.reduceSeats(eventId, registration.getTicketsBooked());
        return saved;
    }

    public List<Registration> getRegistrationsByEmail(String email) {
        return registrationRepository.findByEmailIgnoreCaseOrderByRegisteredAtDesc(email);
    }

    public List<Registration> getRegistrationsByEvent(Long eventId) {
        return registrationRepository.findByEventIdOrderByRegisteredAtDesc(eventId);
    }

    public List<Registration> getAllRegistrations() {
        return registrationRepository.findAll();
    }

    public List<Object[]> getRegistrationStats() {
        return registrationRepository.getRegistrationStats();
    }

    public long countAllRegistrations() {
        return registrationRepository.countAllRegistrations();
    }

    public long sumAllTickets() {
        return registrationRepository.sumAllTickets();
    }

    public Integer sumTicketsByEvent(Long eventId) {
        return registrationRepository.sumTicketsByEventId(eventId);
    }
}
