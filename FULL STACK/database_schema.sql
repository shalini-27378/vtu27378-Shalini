-- MySQL Database Schema for Ticket Booking System
-- Tailored for MySQL Workbench 8.0 CE

-- Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS ticket_booking_db;
USE ticket_booking_db;

-- -----------------------------------------------------
-- Table `events`
-- Stores all the details about the department events
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `events` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `department` VARCHAR(255) NOT NULL,
  `event_date` DATETIME NOT NULL,
  `venue` VARCHAR(255) NOT NULL,
  `price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `total_tickets` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `bookings`
-- Stores individual ticket bookings linked to an event
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `event_id` INT NOT NULL,
  `user_name` VARCHAR(255) NOT NULL,
  `user_email` VARCHAR(255) NOT NULL,
  `user_department` VARCHAR(255) NOT NULL,
  `tickets_booked` INT NOT NULL,
  `total_amount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `booking_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_bookings_event`
    FOREIGN KEY (`event_id`)
    REFERENCES `events` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE = InnoDB;

-- -----------------------------------------------------
-- Insert initial data (Optional)
-- You can run this block to populate the events table
-- -----------------------------------------------------
INSERT INTO `events` (`name`, `department`, `event_date`, `venue`, `price`, `total_tickets`) 
VALUES ('TechNova 2026', 'Computer Science and Engineering', '2026-04-25 10:00:00', 'Main Auditorium, Block C', 15.00, 100);
