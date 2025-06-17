-- Migration to add UUID columns for secure routing
-- Run: php migrate.php

-- Add UUID column to users table
ALTER TABLE users ADD COLUMN uuid VARCHAR(36) UNIQUE;

-- Add UUID column to user_profiles table  
ALTER TABLE user_profiles ADD COLUMN uuid VARCHAR(36) UNIQUE;

-- Add UUID column to messages table
ALTER TABLE messages ADD COLUMN uuid VARCHAR(36) UNIQUE;

-- Add UUID column to contact_requests table
ALTER TABLE contact_requests ADD COLUMN uuid VARCHAR(36) UNIQUE;

-- Add UUID column to premium_memberships table
ALTER TABLE premium_memberships ADD COLUMN uuid VARCHAR(36) UNIQUE;

-- Generate UUIDs for all records
UPDATE users SET uuid = UUID();
UPDATE user_profiles SET uuid = UUID();  
UPDATE messages SET uuid = UUID();
UPDATE contact_requests SET uuid = UUID();
UPDATE premium_memberships SET uuid = UUID();

-- Add indexes
CREATE INDEX idx_users_uuid ON users(uuid);
CREATE INDEX idx_profiles_uuid ON user_profiles(uuid);
CREATE INDEX idx_messages_uuid ON messages(uuid);
CREATE INDEX idx_contact_requests_uuid ON contact_requests(uuid);
CREATE INDEX idx_premium_uuid ON premium_memberships(uuid); 