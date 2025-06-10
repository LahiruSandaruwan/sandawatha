-- Insert test user
INSERT INTO users (email, phone, password, role, status, email_verified, phone_verified) 
VALUES ('test@example.com', '+94771234568', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'active', TRUE, TRUE);

-- Insert test user profile
INSERT INTO user_profiles (
    user_id,
    first_name,
    last_name,
    date_of_birth,
    gender,
    religion,
    district,
    city,
    marital_status,
    height_cm,
    education,
    occupation,
    income_lkr,
    bio,
    goals,
    wants_migration,
    career_focused,
    wants_early_marriage,
    profile_photo,
    profile_completion
)
SELECT 
    id, -- user_id from the user we just created
    'John',
    'Doe',
    '1990-01-01',
    'male',
    'Buddhist',
    'Colombo',
    'Colombo',
    'single',
    175,
    'Bachelor Degree',
    'Software Engineer',
    150000.00,
    'I am a software engineer looking for a life partner who shares my values and interests.',
    'Looking to settle down and start a family within the next few years.',
    1,
    1,
    0,
    NULL,
    90
FROM users WHERE email = 'test@example.com'; 