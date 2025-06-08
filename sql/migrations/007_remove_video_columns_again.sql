-- Migration: Remove video and voice columns
-- Description: Remove video_intro and voice_message columns from user_profiles table
-- Version: 007
-- Created: 2024-03-14

ALTER TABLE user_profiles
DROP COLUMN video_intro,
DROP COLUMN voice_message; 