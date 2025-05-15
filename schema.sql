-- GymManager Database Schema for PostgreSQL

-- Create tenants table
CREATE TABLE IF NOT EXISTS tenants (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    subdomain VARCHAR(50) NOT NULL UNIQUE,
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(100) NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create user roles type
CREATE TYPE user_role AS ENUM('SUPER_ADMIN', 'GYM_ADMIN', 'MEMBER');

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    tenant_id INT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role user_role NOT NULL DEFAULT 'MEMBER',
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

-- Create courses table
CREATE TABLE IF NOT EXISTS courses (
    id SERIAL PRIMARY KEY,
    tenant_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    instructor VARCHAR(100) NOT NULL,
    schedule VARCHAR(255) NOT NULL,
    max_capacity INT NOT NULL DEFAULT 20,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

-- Create course enrollments table (many-to-many relationship)
CREATE TABLE IF NOT EXISTS course_users (
    id SERIAL PRIMARY KEY,
    course_id INT NOT NULL,
    user_id INT NOT NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (course_id, user_id),
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create membership status type
CREATE TYPE membership_status AS ENUM('active', 'expired', 'cancelled');

-- Create memberships table
CREATE TABLE IF NOT EXISTS memberships (
    id SERIAL PRIMARY KEY,
    tenant_id INT NOT NULL,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    status membership_status NOT NULL DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create attendance table
CREATE TABLE IF NOT EXISTS attendance (
    id SERIAL PRIMARY KEY,
    tenant_id INT NOT NULL,
    user_id INT NOT NULL,
    course_id INT NULL,
    date DATE NOT NULL,
    time_in TIME NOT NULL,
    time_out TIME NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL
);

-- Create payments table
CREATE TABLE IF NOT EXISTS payments (
    id SERIAL PRIMARY KEY,
    tenant_id INT NOT NULL,
    user_id INT NOT NULL,
    membership_id INT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (membership_id) REFERENCES memberships(id) ON DELETE SET NULL
);

-- Create indexes for better query performance
CREATE INDEX idx_tenant_user ON users(tenant_id, role);
CREATE INDEX idx_tenant_course ON courses(tenant_id);
CREATE INDEX idx_tenant_membership ON memberships(tenant_id, status);
CREATE INDEX idx_tenant_attendance ON attendance(tenant_id, date);
CREATE INDEX idx_tenant_payment ON payments(tenant_id, payment_date);

-- Create trigger to update the updated_at timestamp
CREATE OR REPLACE FUNCTION update_timestamp()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Add triggers for updated_at columns
CREATE TRIGGER update_tenants_timestamp BEFORE UPDATE ON tenants FOR EACH ROW EXECUTE PROCEDURE update_timestamp();
CREATE TRIGGER update_users_timestamp BEFORE UPDATE ON users FOR EACH ROW EXECUTE PROCEDURE update_timestamp();
CREATE TRIGGER update_courses_timestamp BEFORE UPDATE ON courses FOR EACH ROW EXECUTE PROCEDURE update_timestamp();
CREATE TRIGGER update_memberships_timestamp BEFORE UPDATE ON memberships FOR EACH ROW EXECUTE PROCEDURE update_timestamp();

-- Create default super admin user (password: admin123)
INSERT INTO users (name, email, password, role) 
VALUES ('Super Admin', 'admin@example.com', '$2y$10$b5plA/KpnHsZ0Ew7u3oxLu9QWAgW7SlUPNZwLH5FCqvI7Fz0GHKb.', 'SUPER_ADMIN');

-- Create sample tenant
INSERT INTO tenants (name, subdomain, email, phone, is_active) 
VALUES ('Demo Gym', 'demo', 'info@demogym.com', '123-456-7890', TRUE);

-- Create sample gym admin (password: admin123)
INSERT INTO users (tenant_id, name, email, password, role) 
VALUES (1, 'Gym Admin', 'gymadmin@example.com', '$2y$10$b5plA/KpnHsZ0Ew7u3oxLu9QWAgW7SlUPNZwLH5FCqvI7Fz0GHKb.', 'GYM_ADMIN');

-- Create sample member (password: member123)
INSERT INTO users (tenant_id, name, email, password, role) 
VALUES (1, 'John Member', 'member@example.com', '$2y$10$FJ5.ew8/WrCyvzSNDT0MkOzEYMBQdC.o0c9jPuWnU4n1KUK9icm4G', 'MEMBER');

-- Tabella per gli inviti
CREATE TABLE IF NOT EXISTS invites (
    id SERIAL PRIMARY KEY,
    token VARCHAR(100) NOT NULL UNIQUE,
    tenant_id INT NOT NULL,
    email VARCHAR(100) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending', -- pending, used, expired
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

-- Tabella per le impostazioni SMTP
CREATE TABLE IF NOT EXISTS smtp_settings (
    id SERIAL PRIMARY KEY,
    tenant_id INT NOT NULL UNIQUE,
    host VARCHAR(100) NOT NULL,
    port INT NOT NULL DEFAULT 587,
    username VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    sender_name VARCHAR(100) NOT NULL,
    sender_email VARCHAR(100) NOT NULL,
    encryption VARCHAR(10) DEFAULT 'tls', -- tls, ssl, none
    active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

-- Tabella per i profili utente
CREATE TABLE IF NOT EXISTS user_profiles (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    lastname VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    birthdate DATE NOT NULL,
    tax_code VARCHAR(20) NOT NULL,
    address VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    zip VARCHAR(10) NOT NULL,
    province VARCHAR(20) NOT NULL,
    weight NUMERIC(5,2) NOT NULL,
    height NUMERIC(5,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
