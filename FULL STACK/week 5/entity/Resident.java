package com.example.myproject.entity;

import jakarta.persistence.*;
import java.time.LocalDate;

@Entity
@Table(name = "resident")
public class Resident {
    
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "res_id")
    private Long resId;
    
    @Column(name = "name")
    private String name;
    
    @Column(name = "p_no")
    private String pNo;
    
    @Column(name = "email")
    private String email;
    
    @Column(name = "address")
    private String address;
    
    @Column(name = "room_id")
    private Integer roomId;
    
    @Column(name = "join_date")
    private LocalDate joinDate;
    
    @Column(name = "checkout_date")
    private LocalDate checkoutDate;
    
    // Default constructor (required by JPA)
    public Resident() {
    }
    
    // Parameterized constructor
    public Resident(String name, String pNo, String email, String address, 
                   Integer roomId, LocalDate joinDate, LocalDate checkoutDate) {
        this.name = name;
        this.pNo = pNo;
        this.email = email;
        this.address = address;
        this.roomId = roomId;
        this.joinDate = joinDate;
        this.checkoutDate = checkoutDate;
    }
    
    // Getters and Setters
    public Long getResId() {
        return resId;
    }
    
    public void setResId(Long resId) {
        this.resId = resId;
    }
    
    public String getName() {
        return name;
    }
    
    public void setName(String name) {
        this.name = name;
    }
    
    public String getPNo() {
        return pNo;
    }
    
    public void setPNo(String pNo) {
        this.pNo = pNo;
    }
    
    public String getEmail() {
        return email;
    }
    
    public void setEmail(String email) {
        this.email = email;
    }
    
    public String getAddress() {
        return address;
    }
    
    public void setAddress(String address) {
        this.address = address;
    }
    
    public Integer getRoomId() {
        return roomId;
    }
    
    public void setRoomId(Integer roomId) {
        this.roomId = roomId;
    }
    
    public LocalDate getJoinDate() {
        return joinDate;
    }
    
    public void setJoinDate(LocalDate joinDate) {
        this.joinDate = joinDate;
    }
    
    public LocalDate getCheckoutDate() {
        return checkoutDate;
    }
    
    public void setCheckoutDate(LocalDate checkoutDate) {
        this.checkoutDate = checkoutDate;
    }
    
    @Override
    public String toString() {
        return "Resident{" +
                "resId=" + resId +
                ", name='" + name + '\'' +
                ", pNo='" + pNo + '\'' +
                ", email='" + email + '\'' +
                ", address='" + address + '\'' +
                ", roomId=" + roomId +
                ", joinDate=" + joinDate +
                ", checkoutDate=" + checkoutDate +
                '}';
    }
}