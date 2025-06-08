-- Sandawatha.lk Database Schema
CREATE DATABASE IF NOT EXISTS sandawatha_lk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sandawatha_lk;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    status ENUM('pending', 'active', 'blocked', 'reported') DEFAULT 'pending',
    email_verified BOOLEAN DEFAULT FALSE,
    phone_verified BOOLEAN DEFAULT FALSE,
    email_verification_token VARCHAR(255) NULL,
    phone_verification_code VARCHAR(10) NULL,
    reset_token VARCHAR(255) NULL,
    reset_expires DATETIME NULL,
    dark_mode BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- User profiles table
CREATE TABLE user_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    date_of_birth DATE NOT NULL,
    gender ENUM('male', 'female') NOT NULL,
    religion VARCHAR(50) NOT NULL,
    caste VARCHAR(50) NULL,
    district VARCHAR(50) NOT NULL,
    city VARCHAR(100) NULL,
    marital_status ENUM('single', 'never_married', 'divorced', 'separated', 'widowed', 'annulled') DEFAULT 'single',
    height_cm INT NOT NULL,
    education VARCHAR(100) NOT NULL,
    occupation VARCHAR(100) NULL,
    income_lkr DECIMAL(12,2) NULL,
    bio TEXT NULL,
    goals TEXT NULL,
    wants_migration BOOLEAN DEFAULT FALSE,
    career_focused BOOLEAN DEFAULT FALSE,
    wants_early_marriage BOOLEAN DEFAULT FALSE,
    profile_photo VARCHAR(255) NULL,
    video_intro VARCHAR(255) NULL,
    horoscope_file VARCHAR(255) NULL,
    health_report VARCHAR(255) NULL,
    voice_message VARCHAR(255) NULL,
    view_count INT DEFAULT 0,
    profile_completion INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Premium memberships table
CREATE TABLE premium_memberships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    plan_type ENUM('basic', 'premium', 'platinum') NOT NULL,
    plan_name VARCHAR(50) NOT NULL,
    price_lkr DECIMAL(8,2) NOT NULL,
    duration_months INT NOT NULL,
    features JSON NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('active', 'expired', 'cancelled') DEFAULT 'active',
    payment_method VARCHAR(50) DEFAULT 'mock',
    transaction_id VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Contact requests table
CREATE TABLE contact_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
    message TEXT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    responded_at TIMESTAMP NULL,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_request (sender_id, receiver_id)
);

-- Favorites table
CREATE TABLE favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    favorite_user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (favorite_user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, favorite_user_id)
);

-- Messages table
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    is_admin_message BOOLEAN DEFAULT FALSE,
    parent_message_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_message_id) REFERENCES messages(id) ON DELETE SET NULL
);

-- Login logs table
CREATE TABLE login_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NOT NULL,
    browser VARCHAR(100) NULL,
    device VARCHAR(100) NULL,
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    logout_time TIMESTAMP NULL,
    session_duration INT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- AI compatibility scores table
CREATE TABLE compatibility_scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user1_id INT NOT NULL,
    user2_id INT NOT NULL,
    compatibility_score INT NOT NULL,
    explanation TEXT NOT NULL,
    factors JSON NOT NULL,
    horoscope_match_score INT DEFAULT 0,
    calculated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user1_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (user2_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_compatibility (user1_id, user2_id)
);

-- Newsletter subscriptions table
CREATE TABLE newsletter_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(100) NULL,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);

-- Feedback table
CREATE TABLE feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    name VARCHAR(100) NULL,
    email VARCHAR(255) NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_public BOOLEAN DEFAULT FALSE,
    admin_response TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Profile views table
CREATE TABLE profile_views (
    id INT AUTO_INCREMENT PRIMARY KEY,
    viewer_id INT NOT NULL,
    viewed_profile_id INT NOT NULL,
    view_date DATE NOT NULL,
    view_count INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (viewer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (viewed_profile_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_daily_view (viewer_id, viewed_profile_id, view_date)
);

-- Gift suggestions table (static data)
CREATE TABLE gift_suggestions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(50) NOT NULL,
    item_name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    price_range VARCHAR(50) NULL,
    image_url VARCHAR(255) NULL,
    vendor_link VARCHAR(255) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Site settings table
CREATE TABLE site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin user
INSERT INTO users (email, phone, password, role, status, email_verified, phone_verified) 
VALUES ('admin@sandawatha.lk', '+94771234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active', TRUE, TRUE);

-- Insert default gift suggestions
INSERT INTO gift_suggestions (category, item_name, description, price_range, vendor_link) VALUES
('Jewelry', 'Gold Ring', 'Traditional Sri Lankan gold ring', 'LKR 50,000 - 150,000', '#'),
('Flowers', 'Rose Bouquet', 'Fresh red roses with greeting card', 'LKR 2,000 - 5,000', '#'),
('Clothing', 'Silk Saree', 'Traditional Kandy silk saree', 'LKR 15,000 - 40,000', '#'),
('Electronics', 'Smart Watch', 'Fitness tracking smart watch', 'LKR 25,000 - 75,000', '#'),
('Books', 'Poetry Collection', 'Collection of Sinhala love poetry', 'LKR 1,500 - 3,000', '#');

-- Insert default site settings
INSERT INTO site_settings (setting_key, setting_value, description) VALUES
('site_name', 'Sandawatha.lk', 'Website name'),
('site_tagline', 'Find Your Perfect Match in Sri Lanka', 'Website tagline'),
('contact_email', 'info@sandawatha.lk', 'Contact email'),
('contact_phone', '+94112345678', 'Contact phone'),
('privacy_policy_url', '/privacy-policy', 'Privacy policy page URL'),
('terms_url', '/terms-conditions', 'Terms and conditions page URL'),
('enable_ai_matching', '1', 'Enable AI-based matchmaking'),
('enable_horoscope_matching', '1', 'Enable horoscope compatibility'),
('max_profile_photos', '5', 'Maximum profile photos per user'),
('max_video_size_mb', '50', 'Maximum video size in MB'),
('max_voice_size_mb', '10', 'Maximum voice message size in MB');

-- Create indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_phone ON users(phone);
CREATE INDEX idx_users_status ON users(status);
CREATE INDEX idx_profiles_gender ON user_profiles(gender);
CREATE INDEX idx_profiles_religion ON user_profiles(religion);
CREATE INDEX idx_profiles_district ON user_profiles(district);
CREATE INDEX idx_profiles_age ON user_profiles(date_of_birth);
CREATE INDEX idx_contact_requests_status ON contact_requests(status);
CREATE INDEX idx_messages_read ON messages(is_read);
CREATE INDEX idx_compatibility_score ON compatibility_scores(compatibility_score);
CREATE INDEX idx_profile_views_date ON profile_views(view_date);