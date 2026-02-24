package com.example.myproject.repository;


import com.example.myproject.entity.Staff;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

@Repository
public interface StaffRepository extends JpaRepository<Staff, Integer> {
    // JpaRepository<Entity, PrimaryKeyType>
}