-- Migration: Remove health report column
-- Description: Remove health_report column from user_profiles table
-- Version: 009
-- Created: 2024-03-14

ALTER TABLE user_profiles
DROP COLUMN IF EXISTS health_report; 