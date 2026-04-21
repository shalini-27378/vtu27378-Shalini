import React from 'react';

const BookingSummary = ({ summary, onBookAnother }) => {
  return (
    <div className="summary-container glass-panel animate-fade-in">
      <div style={{ textAlign: 'center', marginBottom: '2rem' }}>
        <div style={{ 
          width: '64px', 
          height: '64px', 
          background: 'rgba(16, 185, 129, 0.2)', 
          borderRadius: '50%', 
          display: 'flex', 
          alignItems: 'center', 
          justifyContent: 'center',
          margin: '0 auto 1rem auto'
        }}>
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--success-color)" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
            <polyline points="20 6 9 17 4 12"></polyline>
          </svg>
        </div>
        <h2 className="gradient-text">Booking Confirmed!</h2>
        <p style={{ color: 'var(--text-secondary)' }}>Thank you, {summary.name}. Your tickets have been successfully booked.</p>
      </div>

      <div style={{ background: 'rgba(15, 23, 42, 0.4)', borderRadius: '1rem', padding: '1.5rem', marginBottom: '2rem' }}>
        <h3 style={{ fontSize: '1.1rem', marginBottom: '1rem', borderBottom: '1px solid var(--glass-border)', paddingBottom: '0.5rem' }}>Booking Summary</h3>
        
        <ul className="event-info-list" style={{ marginTop: 0 }}>
          <li className="event-info-item">
            <span className="event-info-label">Event</span>
            <span className="event-info-value">{summary.eventName}</span>
          </li>
          <li className="event-info-item">
            <span className="event-info-label">Name</span>
            <span className="event-info-value">{summary.name}</span>
          </li>
          <li className="event-info-item">
            <span className="event-info-label">Email</span>
            <span className="event-info-value">{summary.email}</span>
          </li>
          <li className="event-info-item">
            <span className="event-info-label">Tickets Booked</span>
            <span className="event-info-value badge" style={{ background: 'rgba(59, 130, 246, 0.3)', color: '#93c5fd' }}>
              {summary.tickets}
            </span>
          </li>
          <li className="event-info-item" style={{ borderTop: '2px dashed var(--glass-border)', marginTop: '0.5rem', paddingTop: '1rem' }}>
            <span className="event-info-label" style={{ fontWeight: 'bold', color: 'var(--text-primary)' }}>Total Amount</span>
            <span className="event-info-value gradient-text" style={{ fontSize: '1.25rem', fontWeight: 'bold' }}>
              ${summary.totalAmount}
            </span>
          </li>
        </ul>
      </div>

      <div style={{ textAlign: 'center' }}>
        <button className="btn btn-primary" onClick={onBookAnother}>
          Book Another Ticket
        </button>
      </div>
    </div>
  );
};

export default BookingSummary;
