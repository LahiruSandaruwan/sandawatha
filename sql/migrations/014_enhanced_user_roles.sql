-- Enhanced User Roles and Permissions System
-- Run: php migrate.php

-- Create roles table for better role management
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    name VARCHAR(50) UNIQUE NOT NULL,
    slug VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    permissions JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create permissions table
CREATE TABLE IF NOT EXISTS permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    name VARCHAR(100) UNIQUE NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    module VARCHAR(50) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create user_roles pivot table
CREATE TABLE IF NOT EXISTS user_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    role_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    assigned_by INT,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_user_role (user_id, role_id)
);

-- Create package_features table for better feature management
CREATE TABLE IF NOT EXISTS package_features (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    feature_type ENUM('boolean', 'numeric', 'unlimited') DEFAULT 'boolean',
    default_value JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create subscription_packages table
CREATE TABLE IF NOT EXISTS subscription_packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    price_monthly DECIMAL(10,2) NOT NULL,
    price_yearly DECIMAL(10,2),
    features JSON NOT NULL,
    badge_color VARCHAR(7) DEFAULT '#007bff',
    badge_text VARCHAR(50),
    is_popular BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default roles
INSERT INTO roles (uuid, name, slug, description, permissions) VALUES
(UUID(), 'Super Admin', 'super_admin', 'Full system access', JSON_ARRAY('*')),
(UUID(), 'Admin', 'admin', 'Administrative access', JSON_ARRAY('users.manage', 'content.moderate', 'reports.view')),
(UUID(), 'Moderator', 'moderator', 'Content moderation', JSON_ARRAY('content.moderate', 'reports.view')),
(UUID(), 'Premium User', 'premium_user', 'Premium member', JSON_ARRAY('chat.unlimited', 'profile.highlight')),
(UUID(), 'Basic User', 'basic_user', 'Standard user access', JSON_ARRAY('chat.basic', 'profile.view'));

-- Insert default permissions
INSERT INTO permissions (uuid, name, slug, description, module) VALUES
(UUID(), 'Manage Users', 'users.manage', 'Create, edit, delete users', 'users'),
(UUID(), 'View Reports', 'reports.view', 'View user reports and feedback', 'reports'),
(UUID(), 'Moderate Content', 'content.moderate', 'Approve/reject content', 'content'),
(UUID(), 'Unlimited Chat', 'chat.unlimited', 'Send unlimited messages', 'chat'),
(UUID(), 'Basic Chat', 'chat.basic', 'Send limited messages', 'chat'),
(UUID(), 'Video Calling', 'calls.video', 'Make video calls', 'calls'),
(UUID(), 'Audio Calling', 'calls.audio', 'Make audio calls', 'calls'),
(UUID(), 'Profile Highlighting', 'profile.highlight', 'Highlight profile in search', 'profile'),
(UUID(), 'Advanced Search', 'search.advanced', 'Use advanced search filters', 'search'),
(UUID(), 'Priority Support', 'support.priority', 'Get priority customer support', 'support');

-- Insert default package features
INSERT INTO package_features (uuid, name, slug, description, feature_type, default_value) VALUES
(UUID(), 'Daily Messages', 'daily_messages', 'Number of messages per day', 'numeric', '5'),
(UUID(), 'Profile Views', 'profile_views', 'Number of profile views per day', 'numeric', '10'),
(UUID(), 'Contact Requests', 'contact_requests', 'Contact requests per day', 'numeric', '3'),
(UUID(), 'Video Calling', 'video_calling', 'Video call capability', 'boolean', 'false'),
(UUID(), 'Audio Calling', 'audio_calling', 'Audio call capability', 'boolean', 'false'),
(UUID(), 'Profile Boost', 'profile_boost', 'Profile highlighting in search', 'boolean', 'false'),
(UUID(), 'Advanced Filters', 'advanced_filters', 'Advanced search filters', 'boolean', 'false'),
(UUID(), 'Priority Support', 'priority_support', 'Priority customer support', 'boolean', 'false'),
(UUID(), 'Hide Advertisements', 'hide_ads', 'Remove advertisements', 'boolean', 'false'),
(UUID(), 'Unlimited Likes', 'unlimited_likes', 'Unlimited profile likes', 'boolean', 'false');

-- Insert default subscription packages
INSERT INTO subscription_packages (uuid, name, slug, description, price_monthly, price_yearly, features, badge_color, badge_text, is_popular, sort_order) VALUES
(UUID(), 'Basic', 'basic', 'Perfect for getting started', 0.00, 0.00, JSON_OBJECT(
    'daily_messages', 5,
    'profile_views', 10,
    'contact_requests', 3,
    'video_calling', false,
    'audio_calling', false,
    'profile_boost', false,
    'advanced_filters', false,
    'priority_support', false,
    'hide_ads', false,
    'unlimited_likes', false
), '#6c757d', 'FREE', false, 1),

(UUID(), 'Premium', 'premium', 'Most popular choice for serious dating', 999.00, 9990.00, JSON_OBJECT(
    'daily_messages', 50,
    'profile_views', 100,
    'contact_requests', 15,
    'video_calling', true,
    'audio_calling', true,
    'profile_boost', true,
    'advanced_filters', true,
    'priority_support', false,
    'hide_ads', true,
    'unlimited_likes', true
), '#007bff', 'POPULAR', true, 2),

(UUID(), 'Platinum', 'platinum', 'Ultimate dating experience', 1999.00, 19990.00, JSON_OBJECT(
    'daily_messages', 'unlimited',
    'profile_views', 'unlimited',
    'contact_requests', 'unlimited',
    'video_calling', true,
    'audio_calling', true,
    'profile_boost', true,
    'advanced_filters', true,
    'priority_support', true,
    'hide_ads', true,
    'unlimited_likes', true
), '#ffd700', 'PREMIUM', false, 3);

-- Add indexes
CREATE INDEX idx_roles_slug ON roles(slug);
CREATE INDEX idx_permissions_slug ON permissions(slug);
CREATE INDEX idx_permissions_module ON permissions(module);
CREATE INDEX idx_user_roles_user ON user_roles(user_id);
CREATE INDEX idx_user_roles_role ON user_roles(role_id);
CREATE INDEX idx_package_features_slug ON package_features(slug);
CREATE INDEX idx_subscription_packages_slug ON subscription_packages(slug); 