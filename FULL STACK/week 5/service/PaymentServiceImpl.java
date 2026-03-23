package com.example.myproject.service;

import org.springframework.stereotype.Service;
import java.util.UUID;

@Service
public class PaymentServiceImpl implements PaymentService {
    
    @Override
    public String processPayment(double amount) {
        if (amount <= 0) {
            return "Invalid payment amount: $" + amount + ". Amount must be greater than 0.";
        }
        
        String transactionId = "TXN-" + UUID.randomUUID().toString().substring(0, 8).toUpperCase();
        
        if (amount > 10000) {
            return String.format(
                "Large payment of $%.2f requires approval. Transaction ID: %s", 
                amount, transactionId
            );
        }
        
        return String.format(
            "Payment of $%.2f processed successfully. Transaction ID: %s", 
            amount, transactionId
        );
    }
    
    @Override
    public String getPaymentStatus(String transactionId) {
        if (transactionId == null || transactionId.trim().isEmpty()) {
            return "Invalid transaction ID provided";
        }
        
        return String.format(
            "Payment status for transaction %s: COMPLETED", 
            transactionId
        );
    }
}