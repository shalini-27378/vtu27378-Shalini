package com.example.myproject.repository;

import com.example.myproject.entity.User;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

@Repository
public interface UserRepository extends JpaRepository<User, Long> {
    // ADD these two methods
    boolean existsByUsername(String username);  // ADD THIS
    boolean existsByEmail(String email);        // ADD THIS
}