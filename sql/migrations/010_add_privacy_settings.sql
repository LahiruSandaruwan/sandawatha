-- Migration: Add privacy settings
-- Description: Add privacy settings column to user_profiles table
-- Version: 010
-- Created: 2024-03-14

ALTER TABLE user_profiles
ADD COLUMN privacy_settings JSON DEFAULT '{"photo": "public", "contact": "private", "horoscope": "private", "income": "private", "bio": "public", "education": "public", "occupation": "public", "goals": "private"}'; 