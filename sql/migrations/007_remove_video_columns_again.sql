-- Migration: Remove video and voice columns
-- Description: Remove video_intro and voice_message columns from user_profiles table if they exist
-- Version: 007
-- Created: 2024-03-14

-- Drop video_intro column if exists
SET @dbname = DATABASE();
SET @tablename = "user_profiles";
SET @columnname = "video_intro";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND COLUMN_NAME = @columnname
  ) > 0,
  CONCAT("ALTER TABLE ", @tablename, " DROP COLUMN ", @columnname, ";"),
  "SELECT 1"
));
PREPARE alterIfExists FROM @preparedStatement;
EXECUTE alterIfExists;
DEALLOCATE PREPARE alterIfExists;

-- Drop voice_message column if exists
SET @columnname = "voice_message";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND COLUMN_NAME = @columnname
  ) > 0,
  CONCAT("ALTER TABLE ", @tablename, " DROP COLUMN ", @columnname, ";"),
  "SELECT 1"
));
PREPARE alterIfExists FROM @preparedStatement;
EXECUTE alterIfExists;
DEALLOCATE PREPARE alterIfExists; 