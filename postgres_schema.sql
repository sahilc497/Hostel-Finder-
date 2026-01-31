-- PostgreSQL Schema for StudentNest

-- Create ENUM types for consistency
DO $$ BEGIN
    CREATE TYPE user_role AS ENUM ('student', 'owner', 'admin');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    CREATE TYPE gender_category AS ENUM ('Male', 'Female', 'Other');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    CREATE TYPE pg_gender_category AS ENUM ('Boys', 'Girls', 'Co-ed');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    CREATE TYPE booking_status_type AS ENUM ('confirmed', 'cancelled');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    CREATE TYPE payment_status_type AS ENUM ('pending', 'paid', 'failed');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    CREATE TYPE payment_method_type AS ENUM ('Online', 'Cash');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    CREATE TYPE listing_status_type AS ENUM ('pending', 'approved', 'rejected');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    gender gender_category,
    role user_role NOT NULL DEFAULT 'student',
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- PG Listings Table
CREATE TABLE IF NOT EXISTS pg_listings (
    id SERIAL PRIMARY KEY,
    owner_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    gender_type pg_gender_category NOT NULL DEFAULT 'Co-ed',
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    description TEXT,
    rules TEXT,
    amenities TEXT,
    rent DECIMAL(10, 2) DEFAULT 0.00,
    deposit DECIMAL(10, 2) DEFAULT 0.00,
    image VARCHAR(255) DEFAULT NULL,
    total_beds INT DEFAULT 10,
    status listing_status_type DEFAULT 'pending',
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Bookings Table
CREATE TABLE IF NOT EXISTS bookings (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    pg_id INT NOT NULL REFERENCES pg_listings(id) ON DELETE CASCADE,
    bed_number INT DEFAULT NULL,
    booking_date DATE DEFAULT CURRENT_DATE,
    status booking_status_type DEFAULT 'confirmed',
    payment_status payment_status_type DEFAULT 'pending',
    payment_method payment_method_type DEFAULT 'Online',
    leave_notice_date TIMESTAMPTZ DEFAULT NULL,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Payments Table
CREATE TABLE IF NOT EXISTS payments (
    id SERIAL PRIMARY KEY,
    booking_id INT NOT NULL REFERENCES bookings(id) ON DELETE CASCADE,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    pg_id INT NOT NULL REFERENCES pg_listings(id) ON DELETE CASCADE,
    room_id INT NOT NULL,
    txn_id VARCHAR(100) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) NOT NULL,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Notifications Table
CREATE TABLE IF NOT EXISTS notifications (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Admins Table
CREATE TABLE IF NOT EXISTS admins (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Indexes for performance
CREATE INDEX IF NOT EXISTS idx_pg_listings_city ON pg_listings(city);
CREATE INDEX IF NOT EXISTS idx_pg_listings_status ON pg_listings(status);
CREATE INDEX IF NOT EXISTS idx_bookings_user_id ON bookings(user_id);
CREATE INDEX IF NOT EXISTS idx_bookings_pg_id ON bookings(pg_id);
CREATE INDEX IF NOT EXISTS idx_notifications_user_id ON notifications(user_id);

-- Default Admin User
INSERT INTO admins (name, email, password) 
SELECT 'Main Admin', 'admin@gmail.com', '$2y$10$yKxRXv666yK90G3.MEJrhos6Pf24yrAS'
WHERE NOT EXISTS (SELECT 1 FROM admins WHERE email = 'admin@gmail.com');
