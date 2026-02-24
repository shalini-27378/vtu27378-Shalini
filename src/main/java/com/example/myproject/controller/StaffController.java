package com.example.myproject.controller;


import com.example.myproject.entity.Staff;
import com.example.myproject.service.StaffService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.web.bind.annotation.*;

import java.util.List;

@RestController
@RequestMapping("/staff")
public class StaffController {

    @Autowired
    private StaffService staffService; // Field Injection

    @GetMapping("/all")
    public List<Staff> getAll() {
        return staffService.fetchAllStaff();
    }

    @PostMapping("/add")
    public Staff create(@RequestBody Staff staff) {
        return staffService.saveStaffMember(staff);
    }
}