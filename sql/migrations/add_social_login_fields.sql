-- Migration: Add social login fields to users table
-- Run this SQL script to add support for social login

-- Add social login fields to users table
ALTER TABLE users 
ADD COLUMN social_provider VARCHAR(50) DEFAULT NULL COMMENT 'Social login provider (google, facebook, etc.)',
ADD COLUMN social_provider_id VARCHAR(100) DEFAULT NULL COMMENT 'Social provider user ID';

-- Add indexes for better performance
ALTER TABLE users 
ADD INDEX idx_social_provider (social_provider),
ADD INDEX idx_social_provider_id (social_provider_id),
ADD UNIQUE INDEX idx_social_provider_combo (social_provider, social_provider_id);

-- Update the existing users table to handle social login users who may not have phone numbers initially
ALTER TABLE users 
MODIFY COLUMN phone VARCHAR(20) NULL COMMENT 'Phone number (optional for social login users)';

-- Add a note for future reference
INSERT INTO migration_log (migration_name, executed_at) 
VALUES ('add_social_login_fields', NOW())
ON DUPLICATE KEY UPDATE executed_at = NOW(); 