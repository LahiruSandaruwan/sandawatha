-- Drop existing table
DROP TABLE IF EXISTS user_profiles;

-- Recreate table with updated schema
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
    marital_status ENUM('never_married', 'divorced', 'widowed') DEFAULT 'never_married',
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