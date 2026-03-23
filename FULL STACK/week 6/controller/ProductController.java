package com.example.myproject.controller;

import com.example.myproject.entity.Product;
import com.example.myproject.repository.ProductRepository;  // ADD this import
import org.springframework.beans.factory.annotation.Autowired;  // ADD this import
import org.springframework.web.bind.annotation.*;
import org.springframework.http.HttpStatus;  // ADD this import
import org.springframework.http.ResponseEntity;  // ADD this import
import java.util.*;

@RestController
@RequestMapping("/api/products")
public class ProductController {
    
    // REPLACE this line:
    // private List<Product> products = new ArrayList<>();
    
    // WITH these lines:
    @Autowired
    private ProductRepository productRepository;  // ADD this

    @PostMapping
    public Product addProduct(@RequestBody Product product) {
        // CHANGE this line:
        // products.add(product);
        // TO:
        return productRepository.save(product);  // MODIFY this
    }

    @GetMapping
    public List<Product> getAllProducts() {
        // CHANGE this line:
        // return products;
        // TO:
        return productRepository.findAll();  // MODIFY this
    }

    @GetMapping("/{id}")
    public ResponseEntity<Product> getProductById(@PathVariable Long id) {  // CHANGE return type
        // REPLACE the entire method with this:
        return productRepository.findById(id)
                .map(product -> new ResponseEntity<>(product, HttpStatus.OK))
                .orElse(new ResponseEntity<>(HttpStatus.NOT_FOUND));
    }

    @PutMapping("/{id}")
    public ResponseEntity<Product> updateProduct(@PathVariable Long id, @RequestBody Product updatedProduct) {
        // REPLACE the entire method with this:
        return productRepository.findById(id)
                .map(product -> {
                    product.setName(updatedProduct.getName());
                    product.setPrice(updatedProduct.getPrice());
                    product.setCategory(updatedProduct.getCategory());
                    return new ResponseEntity<>(productRepository.save(product), HttpStatus.OK);
                })
                .orElse(new ResponseEntity<>(HttpStatus.NOT_FOUND));
    }

    @DeleteMapping("/{id}")
    public ResponseEntity<Void> deleteProduct(@PathVariable Long id) {  // CHANGE return type
        // REPLACE the entire method with this:
        if (productRepository.existsById(id)) {
            productRepository.deleteById(id);
            return new ResponseEntity<>(HttpStatus.NO_CONTENT);
        }
        return new ResponseEntity<>(HttpStatus.NOT_FOUND);
    }
    
    // ADD this new method for search (optional)
    @GetMapping("/search")
    public List<Product> searchByCategory(@RequestParam String category) {
        return productRepository.findByCategory(category);
    }
}