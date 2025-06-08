-- Update marital_status ENUM to include more options
ALTER TABLE user_profiles 
MODIFY COLUMN marital_status ENUM('single', 'never_married', 'divorced', 'separated', 'widowed', 'annulled') DEFAULT 'single'; 