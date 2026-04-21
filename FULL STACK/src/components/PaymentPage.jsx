import React, { useState } from 'react';

const PaymentPage = ({ bookingData, onConfirm, onCancel }) => {
  const [paymentMethod, setPaymentMethod] = useState('upi');

  const handlePayment = () => {
    // Simulate API delay
    setTimeout(() => {
      onConfirm();
    }, 500);
  };

  return (
    <div className="glass-panel animate-fade-in" style={{ animationDelay: '0.1s', textAlign: 'center' }}>
      <h2 className="gradient-text" style={{ fontSize: '1.75rem', marginBottom: '1rem' }}>Complete Payment</h2>
      
      <div style={{ background: 'rgba(15, 23, 42, 0.4)', borderRadius: '1rem', padding: '1.5rem', marginBottom: '1.5rem' }}>
        <p style={{ color: 'var(--text-secondary)', marginBottom: '0.5rem' }}>Total Amount to Pay</p>
        <p style={{ fontSize: '2rem', fontWeight: 'bold', color: 'var(--success-color)' }}>${bookingData.totalAmount}</p>
        <p style={{ fontSize: '0.875rem', color: 'var(--text-secondary)', marginTop: '0.5rem' }}>For {bookingData.tickets} ticket(s)</p>
      </div>

      <div style={{ marginBottom: '1.5rem' }}>
        <p style={{ textAlign: 'left', fontWeight: 'bold', marginBottom: '0.5rem' }}>Select Payment Method:</p>
        <div style={{ display: 'flex', gap: '1rem', justifyContent: 'center', flexWrap: 'wrap' }}>
          <label style={{ cursor: 'pointer', display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
            <input type="radio" name="payment" value="upi" checked={paymentMethod === 'upi'} onChange={() => setPaymentMethod('upi')} />
            UPI / QR Code
          </label>
          <label style={{ cursor: 'pointer', display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
            <input type="radio" name="payment" value="razorpay" checked={paymentMethod === 'razorpay'} onChange={() => setPaymentMethod('razorpay')} />
            Razorpay
          </label>
          <label style={{ cursor: 'pointer', display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
            <input type="radio" name="payment" value="card" checked={paymentMethod === 'card'} onChange={() => setPaymentMethod('card')} />
            Credit / Debit Card
          </label>
        </div>
      </div>

      {paymentMethod === 'upi' && (
        <div style={{ marginBottom: '1.5rem', display: 'flex', flexDirection: 'column', alignItems: 'center' }}>
          <p style={{ marginBottom: '1rem', color: 'var(--text-secondary)' }}>Scan QR Code with any UPI App (GPay, PhonePe, Paytm)</p>
          <div style={{ background: '#fff', padding: '10px', borderRadius: '10px', display: 'inline-block' }}>
            <img src="/qr_code.png" alt="Payment QR Code" style={{ width: '150px', height: '150px' }} />
          </div>
        </div>
      )}

      {paymentMethod === 'razorpay' && (
        <div style={{ marginBottom: '1.5rem', padding: '2rem', border: '1px dashed var(--glass-border)', borderRadius: '1rem' }}>
          <p style={{ color: 'var(--text-secondary)' }}>You will be securely redirected to the Razorpay gateway upon confirming.</p>
        </div>
      )}

      {paymentMethod === 'card' && (
        <div style={{ marginBottom: '1.5rem', padding: '2rem', border: '1px dashed var(--glass-border)', borderRadius: '1rem' }}>
          <p style={{ color: 'var(--text-secondary)' }}>A secure card entry form will appear here in production.</p>
        </div>
      )}

      <div style={{ background: 'rgba(239, 68, 68, 0.1)', border: '1px solid rgba(239, 68, 68, 0.3)', borderRadius: '0.5rem', padding: '1rem', marginBottom: '1.5rem' }}>
        <p style={{ color: '#fca5a5', fontSize: '0.875rem' }}>
          <strong>Important Notice:</strong> Money will be refunded automatically within 15 minutes in case of any transaction failure or error.
        </p>
      </div>

      <div className="btn-group" style={{ marginTop: 0 }}>
        <button className="btn btn-primary" style={{ flex: 1 }} onClick={handlePayment}>
          Confirm Payment
        </button>
        <button className="btn btn-secondary" onClick={onCancel}>
          Cancel
        </button>
      </div>
    </div>
  );
};

export default PaymentPage;
