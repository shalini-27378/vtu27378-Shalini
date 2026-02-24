package com.example.myproject.service;


import com.example.myproject.entity.Staff;
import com.example.myproject.repository.StaffRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;
import java.util.List;

@Service
public class StaffService {

    @Autowired
    private StaffRepository staffRepository; // Field Injection

    public List<Staff> fetchAllStaff() {
        return staffRepository.findAll();
    }

    public Staff saveStaffMember(Staff staff) {
        return staffRepository.save(staff);
    }
}