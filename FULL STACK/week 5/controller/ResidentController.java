package com.example.myproject.controller;

import com.example.myproject.entity.Resident;
import com.example.myproject.repository.ResidentRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.PageRequest;
import org.springframework.data.domain.Pageable;
import org.springframework.data.domain.Sort;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

import java.time.LocalDate;
import java.util.List;

@RestController
@RequestMapping("/api/residents")
public class ResidentController {

    @Autowired
    private ResidentRepository repository;

    // ============= PHASE 4: PAGINATION AND SORTING =============
    
    // 1. Get all residents with pagination
    @GetMapping("/paginated")
    public Page<Resident> getAllPaginated(
            @RequestParam(defaultValue = "0") int page,
            @RequestParam(defaultValue = "10") int size) {
        Pageable pageable = PageRequest.of(page, size);
        return repository.findAll(pageable);
    }

    // 2. Get all residents with pagination and sorting
    @GetMapping("/sorted")
    public Page<Resident> getAllPaginatedAndSorted(
            @RequestParam(defaultValue = "0") int page,
            @RequestParam(defaultValue = "10") int size,
            @RequestParam(defaultValue = "name") String sortBy,
            @RequestParam(defaultValue = "asc") String sortDir) {
        
        Sort sort = sortDir.equalsIgnoreCase("asc") ? 
                    Sort.by(sortBy).ascending() : 
                    Sort.by(sortBy).descending();
        
        Pageable pageable = PageRequest.of(page, size, sort);
        return repository.findAll(pageable);
    }

    // 3. Get active residents with pagination
    @GetMapping("/active/paginated")
    public Page<Resident> getActiveResidentsPaginated(
            @RequestParam(defaultValue = "0") int page,
            @RequestParam(defaultValue = "10") int size) {
        Pageable pageable = PageRequest.of(page, size);
        return repository.findByCheckoutDateIsNull(pageable);
    }

    // 4. Search residents with pagination
    @GetMapping("/search/paginated")
    public Page<Resident> searchPaginated(
            @RequestParam String q,
            @RequestParam(defaultValue = "0") int page,
            @RequestParam(defaultValue = "10") int size) {
        Pageable pageable = PageRequest.of(page, size);
        return repository.searchByNameOrEmailPaginated(q, pageable);
    }

    // 5. Get residents by room with pagination
    @GetMapping("/room/{roomId}/paginated")
    public Page<Resident> getByRoomIdPaginated(
            @PathVariable Integer roomId,
            @RequestParam(defaultValue = "0") int page,
            @RequestParam(defaultValue = "10") int size) {
        Pageable pageable = PageRequest.of(page, size);
        return repository.findByRoomId(roomId, pageable);
    }

    // ============= CRUD OPERATIONS =============

    @PostMapping
    @ResponseStatus(HttpStatus.CREATED)
    public Resident addResident(@RequestBody Resident resident) {
        return repository.save(resident);
    }

    @GetMapping
    public List<Resident> getAll() {
        return repository.findAll();
    }

    // FIXED: Changed from Integer to Long
    @GetMapping("/{id}")
    public ResponseEntity<Resident> getById(@PathVariable Long id) {
        return repository.findById(id)
                .map(ResponseEntity::ok)
                .orElse(ResponseEntity.notFound().build());
    }

    // FIXED: Changed from Integer to Long
    @PutMapping("/{id}")
    public ResponseEntity<Resident> updateResident(@PathVariable Long id, @RequestBody Resident residentDetails) {
        return repository.findById(id)
                .map(resident -> {
                    resident.setName(residentDetails.getName());
                    resident.setPNo(residentDetails.getPNo());
                    resident.setEmail(residentDetails.getEmail());
                    resident.setAddress(residentDetails.getAddress());
                    resident.setRoomId(residentDetails.getRoomId());
                    resident.setJoinDate(residentDetails.getJoinDate());
                    resident.setCheckoutDate(residentDetails.getCheckoutDate());
                    return ResponseEntity.ok(repository.save(resident));
                })
                .orElse(ResponseEntity.notFound().build());
    }

    // FIXED: Changed from Integer to Long
    @DeleteMapping("/{id}")
    public ResponseEntity<Void> deleteResident(@PathVariable Long id) {
        if (!repository.existsById(id)) {
            return ResponseEntity.notFound().build();
        }
        repository.deleteById(id);
        return ResponseEntity.noContent().build();
    }

    // FIXED: Changed from Integer to Long for check-in
    @PostMapping("/{id}/checkin")
    public ResponseEntity<Resident> checkIn(@PathVariable Long id) {
        return repository.findById(id)
                .map(resident -> {
                    resident.setJoinDate(LocalDate.now());
                    resident.setCheckoutDate(null);
                    return ResponseEntity.ok(repository.save(resident));
                })
                .orElse(ResponseEntity.notFound().build());
    }

    // FIXED: Changed from Integer to Long for checkout
    @PostMapping("/{id}/checkout")
    public ResponseEntity<Resident> checkOut(@PathVariable Long id) {
        return repository.findById(id)
                .map(resident -> {
                    resident.setCheckoutDate(LocalDate.now());
                    return ResponseEntity.ok(repository.save(resident));
                })
                .orElse(ResponseEntity.notFound().build());
    }

    // These methods are fine as they don't use the ID type
    @GetMapping("/email/{email}")
    public List<Resident> getByEmail(@PathVariable String email) {
        return repository.findByEmail(email);
    }

    @GetMapping("/room/{roomId}")
    public List<Resident> getByRoomId(@PathVariable Integer roomId) {
        return repository.findByRoomId(roomId);
    }

    @GetMapping("/phone/{pNo}")
    public List<Resident> getByPhone(@PathVariable String pNo) {
        return repository.findByPNo(pNo);
    }

    @GetMapping("/active")
    public List<Resident> getActiveResidents() {
        return repository.findByCheckoutDateIsNull();
    }

    @GetMapping("/checked-out")
    public List<Resident> getCheckedOutResidents() {
        return repository.findByCheckoutDateIsNotNull();
    }

    @GetMapping("/search")
    public List<Resident> search(@RequestParam String q) {
        return repository.searchByNameOrEmail(q);
    }
}