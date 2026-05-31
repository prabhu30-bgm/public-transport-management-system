-- Bus Management System Database

-- Create database
CREATE DATABASE IF NOT EXISTS project_bms;
USE project_bms;

-- Create admin table (increased password capacity to 255 for modern password hashes)
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create drivers table (increased password capacity to 255 for modern password hashes)
CREATE TABLE IF NOT EXISTS drivers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    license_number VARCHAR(50) NOT NULL,
    contact_number VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create buses table
CREATE TABLE IF NOT EXISTS buses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bus_number VARCHAR(20) NOT NULL UNIQUE,
    registration_number VARCHAR(50) NOT NULL,
    capacity INT NOT NULL,
    model VARCHAR(50) NOT NULL,
    status ENUM('active', 'maintenance', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create routes table (added fare column)
CREATE TABLE IF NOT EXISTS routes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    route_name VARCHAR(100) NOT NULL,
    start_location VARCHAR(100) NOT NULL,
    end_location VARCHAR(100) NOT NULL,
    distance DECIMAL(10,2) NOT NULL,
    estimated_time INT NOT NULL COMMENT 'in minutes',
    fare DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create schedules table (added available_seats column)
CREATE TABLE IF NOT EXISTS schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bus_id INT NOT NULL,
    driver_id INT NOT NULL,
    route_id INT NOT NULL,
    departure_time TIME NOT NULL,
    departure_date DATE NOT NULL,
    available_seats INT NOT NULL,
    status ENUM('scheduled', 'departed', 'completed', 'cancelled') DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bus_id) REFERENCES buses(id) ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE CASCADE,
    FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE CASCADE
);

-- Create trip reports table
CREATE TABLE IF NOT EXISTS trip_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    schedule_id INT NOT NULL,
    actual_departure_time DATETIME DEFAULT NULL,
    actual_arrival_time DATETIME DEFAULT NULL,
    status ENUM('on_time', 'delayed', 'completed', 'cancelled') DEFAULT 'on_time',
    remarks TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (schedule_id) REFERENCES schedules(id) ON DELETE CASCADE
);

-- Create users table (for registered passengers)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    schedule_id INT NOT NULL,
    seats INT NOT NULL,
    total_fare DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    status ENUM('confirmed', 'cancelled', 'completed') DEFAULT 'confirmed',
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (schedule_id) REFERENCES schedules(id) ON DELETE CASCADE
);

-- Create tickets table (for individual seat bookings)
CREATE TABLE IF NOT EXISTS tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    passenger_name VARCHAR(100) NOT NULL,
    passenger_age INT NOT NULL,
    passenger_gender VARCHAR(10) NOT NULL,
    seat_number VARCHAR(10) NOT NULL,
    ticket_number VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

-- Create issue reports table (for drivers reporting trip incidents)
CREATE TABLE IF NOT EXISTS issue_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    schedule_id INT NOT NULL,
    driver_id INT NOT NULL,
    issue_type VARCHAR(50) NOT NULL,
    issue_description TEXT NOT NULL,
    location VARCHAR(255) DEFAULT NULL,
    status ENUM('pending', 'acknowledged', 'resolved') DEFAULT 'pending',
    admin_remarks TEXT DEFAULT NULL,
    reported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (schedule_id) REFERENCES schedules(id) ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE CASCADE
);

-- --------------------------------------------------------
-- SEED DATA SETUP
-- --------------------------------------------------------

-- Insert default admin (plain text, will be auto-upgraded or verifyPassword supported)
INSERT INTO admins (username, password, name, email)
VALUES ('admin', 'admin1234', 'System Administrator', 'admin@busmanagement.com');

-- Insert sample drivers
INSERT INTO drivers (username, password, name, license_number, contact_number, email) VALUES
('driver1', 'Driver@123', 'John Doe', 'DL12345678', '1234567890', 'john@example.com'),
('driver2', 'Driver@123', 'Jane Smith', 'DL87654321', '0987654321', 'jane@example.com');

-- Insert sample buses
INSERT INTO buses (bus_number, registration_number, capacity, model) VALUES
('B001', 'REG12345', 40, 'Volvo 9400'),
('B002', 'REG67890', 35, 'Mercedes Benz');

-- Insert sample routes (with fares)
INSERT INTO routes (route_name, start_location, end_location, distance, estimated_time, fare) VALUES
('Route 1', 'City Center', 'Airport', 25.5, 45, 150.00),
('Route 2', 'City Center', 'University', 10.2, 30, 50.00),
('Route 3', 'Airport', 'Shopping Mall', 15.0, 35, 80.00);

-- Insert sample schedules (setting available_seats to initial capacities)
INSERT INTO schedules (bus_id, driver_id, route_id, departure_time, departure_date, available_seats) VALUES
(1, 1, 1, '10:00:00', CURDATE(), 40),
(2, 2, 2, '14:00:00', CURDATE(), 35);

-- Insert sample users (plain text for testing autoupgrade, and registered passenger)
INSERT INTO users (username, password, name, email, phone, address, status) VALUES
('passenger1', 'Passenger@123', 'Robert Johnson', 'robert@example.com', '9876543210', '123 Main St, City Center', 'active'),
('passenger2', 'Passenger@123', 'Emily Davis', 'emily@example.com', '9876543211', '456 Oak Rd, Green Valley', 'active');
