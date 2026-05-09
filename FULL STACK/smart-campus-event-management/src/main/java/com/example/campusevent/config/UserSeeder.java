package com.example.campusevent.config;

/**
 * UserSeeder is intentionally empty.
 * No hardcoded users are inserted.
 *
 * To create an ADMIN manually, run this SQL in phpMyAdmin:
 *
 * INSERT INTO users (full_name, username, email, password, role, enabled)
 * VALUES ('Admin User', 'admin', 'admin@campus.com',
 *   '$2a$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy',
 *   'ADMIN', true);
 *
 * The BCrypt hash above = "admin123"
 * Or register via /register page and select "Admin" as account type.
 */
public class UserSeeder {
    // No-op — users are created via /register
}
