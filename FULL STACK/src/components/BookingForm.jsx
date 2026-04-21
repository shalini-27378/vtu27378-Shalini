import React, { useState } from 'react';

const BookingForm = ({ event, availableTickets, onBookingSuccess }) => {
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    department: '',
    tickets: 1
  });

  const [errors, setErrors] = useState({});

  const validate = () => {
    const newErrors = {};
    if (!formData.name.trim()) newErrors.name = 'Name is required';
    
    if (!formData.email.trim()) {
      newErrors.email = 'Email is required';
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
      newErrors.email = 'Invalid email format';
    }

    if (!formData.department.trim()) newErrors.department = 'Department is required';

    if (!formData.tickets) {
      newErrors.tickets = 'Number of tickets is required';
    } else if (isNaN(formData.tickets) || Number(formData.tickets) <= 0) {
      newErrors.tickets = 'Please enter a positive number';
    } else if (Number(formData.tickets) > availableTickets) {
      newErrors.tickets = `Only ${availableTickets} tickets available`;
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
    // Clear error when user types
    if (errors[name]) {
      setErrors(prev => ({ ...prev, [name]: null }));
    }
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    if (validate()) {
      onBookingSuccess({
        ...formData,
        tickets: Number(formData.tickets),
        totalAmount: Number(formData.tickets) * event.price
      });
    }
  };

  const handleReset = () => {
    setFormData({
      name: '',
      email: '',
      department: '',
      tickets: 1
    });
    setErrors({});
  };

  if (availableTickets === 0) {
    return (
      <div className="glass-panel animate-fade-in" style={{ animationDelay: '0.2s', textAlign: 'center' }}>
        <h3 className="gradient-text">Tickets Sold Out</h3>
        <p style={{ color: 'var(--text-secondary)' }}>We're sorry, but all tickets for this event have been booked.</p>
      </div>
    );
  }

  return (
    <div className="glass-panel animate-fade-in" style={{ animationDelay: '0.2s' }}>
      <h3 style={{ marginBottom: '1.5rem', color: 'var(--text-primary)' }}>Book Your Tickets</h3>
      
      <form onSubmit={handleSubmit} noValidate>
        <div className="form-group">
          <label className="form-label" htmlFor="name">Full Name</label>
          <input
            type="text"
            id="name"
            name="name"
            className="form-input"
            placeholder="John Doe"
            value={formData.name}
            onChange={handleChange}
            style={{ borderColor: errors.name ? 'var(--error-color)' : '' }}
          />
          {errors.name && <span className="error-text">{errors.name}</span>}
        </div>

        <div className="form-group">
          <label className="form-label" htmlFor="email">Email Address</label>
          <input
            type="email"
            id="email"
            name="email"
            className="form-input"
            placeholder="john.doe@university.edu"
            value={formData.email}
            onChange={handleChange}
            style={{ borderColor: errors.email ? 'var(--error-color)' : '' }}
          />
          {errors.email && <span className="error-text">{errors.email}</span>}
        </div>

        <div className="form-group">
          <label className="form-label" htmlFor="department">Department</label>
          <input
            type="text"
            id="department"
            name="department"
            className="form-input"
            placeholder="e.g., Computer Science"
            value={formData.department}
            onChange={handleChange}
            style={{ borderColor: errors.department ? 'var(--error-color)' : '' }}
          />
          {errors.department && <span className="error-text">{errors.department}</span>}
        </div>

        <div className="form-group">
          <label className="form-label" htmlFor="tickets">Number of Tickets</label>
          <input
            type="number"
            id="tickets"
            name="tickets"
            className="form-input"
            min="1"
            max={availableTickets}
            value={formData.tickets}
            onChange={handleChange}
            style={{ borderColor: errors.tickets ? 'var(--error-color)' : '' }}
          />
          {errors.tickets && <span className="error-text">{errors.tickets}</span>}
        </div>

        <div className="btn-group">
          <button type="submit" className="btn btn-primary" style={{ flex: 1 }}>
            Proceed to Payment
          </button>
          <button type="button" onClick={handleReset} className="btn btn-secondary">
            Reset
          </button>
        </div>
      </form>
    </div>
  );
};

export default BookingForm;
