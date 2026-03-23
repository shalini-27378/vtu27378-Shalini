package com.example.myproject.service;

public interface PaymentService {
    String processPayment(double amount);
    String getPaymentStatus(String transactionId);
}