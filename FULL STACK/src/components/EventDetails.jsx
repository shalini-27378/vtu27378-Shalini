import React from 'react';

const EventDetails = ({ event, availableTickets, onRegisterClick }) => {
  return (
    <div className="glass-panel animate-fade-in" style={{ animationDelay: '0.1s', textAlign: 'center' }}>
      <div style={{ marginBottom: '1.5rem', display: 'flex', justifyContent: 'center' }}>
        <img src="/logo.png" alt="TechNova Logo" style={{ width: '120px', height: '120px', objectFit: 'cover', borderRadius: '50%', border: '3px solid var(--accent-color)' }} />
      </div>

      <div style={{ display: 'flex', justifyContent: 'center', gap: '1rem', alignItems: 'center', marginBottom: '1rem' }}>
        <span className="badge">{event.department}</span>
        {availableTickets <= 10 && availableTickets > 0 && (
          <span className="badge" style={{ background: 'rgba(239, 68, 68, 0.2)', color: '#f87171', borderColor: 'rgba(239, 68, 68, 0.3)' }}>
            Hurry, only {availableTickets} left!
          </span>
        )}
        {availableTickets === 0 && (
          <span className="badge" style={{ background: 'rgba(239, 68, 68, 0.2)', color: '#f87171', borderColor: 'rgba(239, 68, 68, 0.3)' }}>
            Sold Out
          </span>
        )}
      </div>
      
      <h2 className="gradient-text" style={{ fontSize: '2rem' }}>{event.name}</h2>
      <p style={{ color: 'var(--text-secondary)', marginBottom: '1.5rem' }}>
        Join us for an exciting and insightful event organized by the {event.department} department.
      </p>

      <ul className="event-info-list" style={{ textAlign: 'left', marginBottom: '2rem' }}>
        <li className="event-info-item">
          <span className="event-info-label">Date & Time</span>
          <span className="event-info-value">{event.dateTime}</span>
        </li>
        <li className="event-info-item">
          <span className="event-info-label">Venue</span>
          <span className="event-info-value">{event.venue}</span>
        </li>
        <li className="event-info-item">
          <span className="event-info-label">Ticket Price</span>
          <span className="event-info-value" style={{ color: 'var(--success-color)', fontWeight: 'bold' }}>
            {event.price === 0 ? 'Free' : `$${event.price}`}
          </span>
        </li>
        <li className="event-info-item">
          <span className="event-info-label">Available Tickets</span>
          <span className="event-info-value" style={{ color: availableTickets === 0 ? 'var(--error-color)' : 'inherit' }}>
            {availableTickets}
          </span>
        </li>
      </ul>

      {availableTickets > 0 ? (
        <button className="btn btn-primary" style={{ width: '100%', fontSize: '1.25rem', padding: '1rem' }} onClick={onRegisterClick}>
          Register Now
        </button>
      ) : (
        <button className="btn btn-primary" style={{ width: '100%', fontSize: '1.25rem', padding: '1rem' }} disabled>
          Sold Out
        </button>
      )}
    </div>
  );
};

export default EventDetails;
