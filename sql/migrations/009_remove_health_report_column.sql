-- Migration: Remove health report column
-- Description: Remove health_report column from user_profiles table if it exists
-- Version: 009
-- Created: 2024-03-14

SET @dbname = DATABASE();
SET @tablename = "user_profiles";
SET @columnname = "health_report";
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