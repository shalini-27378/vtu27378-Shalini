package com.example.myproject.repository;

import com.example.myproject.entity.Product;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import java.util.List;  // ADD this import

@Repository
public interface ProductRepository extends JpaRepository<Product, Long> {
    // ADD this one line - it's required for your controller
    List<Product> findByCategory(String category);  // ADD THIS LINE
}
