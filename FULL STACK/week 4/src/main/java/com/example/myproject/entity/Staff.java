package com.example.myproject.entity;

import jakarta.persistence.*;
import java.math.BigDecimal;

@Entity
@Table(name = "staff")
public class Staff {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "staff_id")
    private Integer staffId;

    @Column(name = "s_name", length = 50)
    private String sName;

    @Column(name = "role", length = 30)
    private String role;

    @Column(name = "s_phone", length = 15)
    private String sPhone;

    @Column(name = "salary", precision = 8, scale = 2)
    private BigDecimal salary;

    // Default Constructor
    public Staff() {}

    // Getters and Setters
    public Integer getStaffId() { return staffId; }
    public void setStaffId(Integer staffId) { this.staffId = staffId; }

    public String getSName() { return sName; }
    public void setSName(String sName) { this.sName = sName; }

    public String getRole() { return role; }
    public void setRole(String role) { this.role = role; }

    public String getSPhone() { return sPhone; }
    public void setSPhone(String sPhone) { this.sPhone = sPhone; }

    public BigDecimal getSalary() { return salary; }
    public void setSalary(BigDecimal salary) { this.salary = salary; }
}