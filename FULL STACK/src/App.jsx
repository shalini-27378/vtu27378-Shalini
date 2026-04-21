import React, { useState } from 'react';
import EventDetails from './components/EventDetails';
import BookingForm from './components/BookingForm';
import BookingSummary from './components/BookingSummary';
import PaymentPage from './components/PaymentPage';

// Global Event Metadata
const EVENT_DATA = {
  name: "TechNova 2026",
  department: "Computer Science and Engineering",
  dateTime: "April 25, 2026 • 10:00 AM",
  venue: "Main Auditorium, Block C",
  price: 15 // Set to 0 for free
};

function App() {
  const [availableTickets, setAvailableTickets] = useState(100);
  const [bookingSummary, setBookingSummary] = useState(null);
  const [showBookingForm, setShowBookingForm] = useState(false);
  const [pendingBooking, setPendingBooking] = useState(null);

  const handleProceedToPayment = (bookingData) => {
    // Temporarily save the form data and move to payment view
    setPendingBooking(bookingData);
    setShowBookingForm(false);
  };

  const handlePaymentConfirm = () => {
    // Now actually confirm the booking
    setAvailableTickets(prev => prev - pendingBooking.tickets);
    
    setBookingSummary({
      ...pendingBooking,
      eventName: EVENT_DATA.name
    });
    setPendingBooking(null); // Clear pending
  };

  const handlePaymentCancel = () => {
    // User cancelled payment, go back to form
    setShowBookingForm(true);
    setPendingBooking(null);
  };

  const handleBookAnother = () => {
    setBookingSummary(null);
    setShowBookingForm(false);
    setPendingBooking(null);
  };

  const handleRegisterClick = () => {
    setShowBookingForm(true);
  };

  return (
    <div className="app-container" style={{ display: 'flex', justifyContent: 'center', width: '100%' }}>
      <div style={{ width: '100%', maxWidth: '600px' }}>
        {bookingSummary ? (
          <BookingSummary 
            summary={bookingSummary} 
            onBookAnother={handleBookAnother} 
          />
        ) : pendingBooking ? (
          <PaymentPage 
            bookingData={pendingBooking}
            onConfirm={handlePaymentConfirm}
            onCancel={handlePaymentCancel}
          />
        ) : showBookingForm ? (
          <BookingForm 
            event={EVENT_DATA} 
            availableTickets={availableTickets} 
            onBookingSuccess={handleProceedToPayment} 
          />
        ) : (
          <EventDetails 
            event={EVENT_DATA} 
            availableTickets={availableTickets} 
            onRegisterClick={handleRegisterClick}
          />
        )}
      </div>
    </div>
  );
}

export default App;
