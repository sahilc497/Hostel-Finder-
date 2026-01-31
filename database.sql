CREATE DATABASE IF NOT EXISTS hostel_finder;
USE hostel_finder;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    gender ENUM('Male', 'Female', 'Other'),
    role ENUM('student', 'owner', 'admin') NOT NULL DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS pg_listings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    gender_type ENUM('Boys', 'Girls', 'Co-ed') NOT NULL DEFAULT 'Co-ed',
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    description TEXT,
    rules TEXT,
    amenities TEXT,
    rent DECIMAL(10, 2) DEFAULT 0.00,
    deposit DECIMAL(10, 2) DEFAULT 0.00,
    image VARCHAR(255) DEFAULT NULL,
    total_beds INT DEFAULT 10,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pg_id INT NOT NULL,
    bed_number INT DEFAULT NULL,
    booking_date DATE DEFAULT (CURRENT_DATE),
    status ENUM('confirmed', 'cancelled') DEFAULT 'confirmed',
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    payment_method ENUM('Online', 'Cash') DEFAULT 'Online',
    leave_notice_date DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (pg_id) REFERENCES pg_listings(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    user_id INT NOT NULL,
    pg_id INT NOT NULL,
    room_id INT NOT NULL,
    txn_id VARCHAR(100) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
-- Admin Table
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO admins (name, email, password) VALUES ('Main Admin', 'admin@gmail.com', '$2y$10$yKxRXv666yK90G3.MEJrhos6Pf24yrAS');
