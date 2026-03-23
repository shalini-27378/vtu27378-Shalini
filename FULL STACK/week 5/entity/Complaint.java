package com.example.myproject.entity;

import jakarta.persistence.*;
import java.time.LocalDate;

@Entity
@Table(name = "complaint")
public class Complaint {
    
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;
    
    private String description;
    private LocalDate date;
    private String status;
    
    @ManyToOne
    @JoinColumn(name = "res_id")
    private Resident resident;  // This creates proper foreign key relationship
    
    // Default constructor
    public Complaint() {}
    
    // Getters and Setters
    public Long getId() {
        return id;
    }
    
    public void setId(Long id) {
        this.id = id;
    }
    
    public String getDescription() {
        return description;
    }
    
    public void setDescription(String description) {
        this.description = description;
    }
    
    public LocalDate getDate() {
        return date;
    }
    
    public void setDate(LocalDate date) {
        this.date = date;
    }
    
    public String getStatus() {
        return status;
    }
    
    public void setStatus(String status) {
        this.status = status;
    }
    
    public Resident getResident() {
        return resident;
    }
    
    public void setResident(Resident resident) {
        this.resident = resident;
    }
}