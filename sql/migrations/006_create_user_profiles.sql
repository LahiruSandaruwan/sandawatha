-- Migration: Create user_profiles table
-- Description: Create the user_profiles table with the correct structure
-- Version: 006
-- Created: 2024-03-14

CREATE TABLE IF NOT EXISTS user_profiles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    date_of_birth DATE NOT NULL,
    gender ENUM('male', 'female') NOT NULL,
    religion VARCHAR(50) NOT NULL,
    caste VARCHAR(50),
    district VARCHAR(50) NOT NULL,
    city VARCHAR(50),
    marital_status ENUM('single', 'divorced', 'widowed') NOT NULL DEFAULT 'single',
    height_cm INT NOT NULL,
    education VARCHAR(100) NOT NULL,
    occupation VARCHAR(100),
    income_lkr DECIMAL(12,2),
    bio TEXT,
    goals TEXT,
    wants_migration BOOLEAN DEFAULT 0,
    career_focused BOOLEAN DEFAULT 0,
    wants_early_marriage BOOLEAN DEFAULT 0,
    profile_photo VARCHAR(255),
    horoscope_file VARCHAR(255),
    health_report VARCHAR(255),
    view_count INT DEFAULT 0,
    profile_completion INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 