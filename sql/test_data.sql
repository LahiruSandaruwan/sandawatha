-- Clear existing data
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE compatibility_scores;
TRUNCATE TABLE contact_requests;
TRUNCATE TABLE favorites;
TRUNCATE TABLE messages;
TRUNCATE TABLE premium_memberships;
TRUNCATE TABLE user_profiles;
TRUNCATE TABLE users;
SET FOREIGN_KEY_CHECKS = 1;

-- Insert admin user
INSERT INTO users (id, email, password, role, created_at, updated_at)
VALUES (1, 'admin@sandawatha.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NOW(), NOW());

-- Insert 500 male users
INSERT INTO users (id, email, password, role, created_at, updated_at)
SELECT 
    n + 2 as id,
    CONCAT('male', n + 1, '@example.com') as email,
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' as password,
    'user' as role,
    NOW() as created_at,
    NOW() as updated_at
FROM (
    SELECT a.N + b.N * 10 + c.N * 100 as N
    FROM (SELECT 0 as N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a,
         (SELECT 0 as N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) b,
         (SELECT 0 as N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4) c
    LIMIT 500
) numbers;

-- Insert 500 female users
INSERT INTO users (id, email, password, role, created_at, updated_at)
SELECT 
    n + 502 as id,
    CONCAT('female', n + 1, '@example.com') as email,
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' as password,
    'user' as role,
    NOW() as created_at,
    NOW() as updated_at
FROM (
    SELECT a.N + b.N * 10 + c.N * 100 as N
    FROM (SELECT 0 as N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a,
         (SELECT 0 as N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) b,
         (SELECT 0 as N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4) c
    LIMIT 500
) numbers;

-- Insert profiles for male users
INSERT INTO user_profiles (user_id, first_name, last_name, gender, date_of_birth, religion, caste, education, occupation, monthly_income, height, weight, marital_status, drinking_habits, smoking_habits, location, bio, preferences)
SELECT 
    id,
    CONCAT('Male', id - 1) as first_name,
    CASE (id % 10)
        WHEN 0 THEN 'Perera'
        WHEN 1 THEN 'Silva'
        WHEN 2 THEN 'Fernando'
        WHEN 3 THEN 'Dissanayake'
        WHEN 4 THEN 'Bandara'
        WHEN 5 THEN 'Rajapaksa'
        WHEN 6 THEN 'Gunawardena'
        WHEN 7 THEN 'Jayawardena'
        WHEN 8 THEN 'Wickramasinghe'
        WHEN 9 THEN 'Kumarasinghe'
    END as last_name,
    'male' as gender,
    DATE_SUB(CURRENT_DATE, INTERVAL 20 + (id % 20) YEAR) as date_of_birth,
    CASE (id % 5)
        WHEN 0 THEN 'Buddhist'
        WHEN 1 THEN 'Buddhist'
        WHEN 2 THEN 'Buddhist'
        WHEN 3 THEN 'Catholic'
        WHEN 4 THEN 'Hindu'
    END as religion,
    CASE (id % 4)
        WHEN 0 THEN 'Govigama'
        WHEN 1 THEN 'Karava'
        WHEN 2 THEN 'N/A'
        WHEN 3 THEN 'Other'
    END as caste,
    CASE (id % 5)
        WHEN 0 THEN 'Bachelors Degree'
        WHEN 1 THEN 'Masters Degree'
        WHEN 2 THEN 'Diploma'
        WHEN 3 THEN 'PhD'
        WHEN 4 THEN 'Professional Qualification'
    END as education,
    CASE (id % 8)
        WHEN 0 THEN 'Software Engineer'
        WHEN 1 THEN 'Doctor'
        WHEN 2 THEN 'Engineer'
        WHEN 3 THEN 'Business Owner'
        WHEN 4 THEN 'Bank Manager'
        WHEN 5 THEN 'Teacher'
        WHEN 6 THEN 'Government Officer'
        WHEN 7 THEN 'Accountant'
    END as occupation,
    50000 + (id % 10) * 25000 as monthly_income,
    165 + (id % 20) as height,
    60 + (id % 20) as weight,
    'never_married' as marital_status,
    CASE (id % 3)
        WHEN 0 THEN 'never'
        WHEN 1 THEN 'occasionally'
        WHEN 2 THEN 'never'
    END as drinking_habits,
    'never' as smoking_habits,
    CASE (id % 8)
        WHEN 0 THEN 'Colombo'
        WHEN 1 THEN 'Kandy'
        WHEN 2 THEN 'Galle'
        WHEN 3 THEN 'Negombo'
        WHEN 4 THEN 'Matara'
        WHEN 5 THEN 'Kurunegala'
        WHEN 6 THEN 'Jaffna'
        WHEN 7 THEN 'Batticaloa'
    END as location,
    CONCAT('Hi, I am a ', 
        CASE (id % 8)
            WHEN 0 THEN 'Software Engineer'
            WHEN 1 THEN 'Doctor'
            WHEN 2 THEN 'Engineer'
            WHEN 3 THEN 'Business Owner'
            WHEN 4 THEN 'Bank Manager'
            WHEN 5 THEN 'Teacher'
            WHEN 6 THEN 'Government Officer'
            WHEN 7 THEN 'Accountant'
        END,
        ' looking for a life partner who shares my values.'
    ) as bio,
    '{"age_range": {"min": 20, "max": 35}, "preferred_religion": "any"}' as preferences
FROM users 
WHERE id > 1 AND id < 502;

-- Insert profiles for female users
INSERT INTO user_profiles (user_id, first_name, last_name, gender, date_of_birth, religion, caste, education, occupation, monthly_income, height, weight, marital_status, drinking_habits, smoking_habits, location, bio, preferences)
SELECT 
    id,
    CONCAT('Female', id - 501) as first_name,
    CASE (id % 10)
        WHEN 0 THEN 'Perera'
        WHEN 1 THEN 'Silva'
        WHEN 2 THEN 'Fernando'
        WHEN 3 THEN 'Dissanayake'
        WHEN 4 THEN 'Bandara'
        WHEN 5 THEN 'Rajapaksa'
        WHEN 6 THEN 'Gunawardena'
        WHEN 7 THEN 'Jayawardena'
        WHEN 8 THEN 'Wickramasinghe'
        WHEN 9 THEN 'Kumarasinghe'
    END as last_name,
    'female' as gender,
    DATE_SUB(CURRENT_DATE, INTERVAL 20 + (id % 15) YEAR) as date_of_birth,
    CASE (id % 5)
        WHEN 0 THEN 'Buddhist'
        WHEN 1 THEN 'Buddhist'
        WHEN 2 THEN 'Buddhist'
        WHEN 3 THEN 'Catholic'
        WHEN 4 THEN 'Hindu'
    END as religion,
    CASE (id % 4)
        WHEN 0 THEN 'Govigama'
        WHEN 1 THEN 'Karava'
        WHEN 2 THEN 'N/A'
        WHEN 3 THEN 'Other'
    END as caste,
    CASE (id % 5)
        WHEN 0 THEN 'Bachelors Degree'
        WHEN 1 THEN 'Masters Degree'
        WHEN 2 THEN 'Diploma'
        WHEN 3 THEN 'PhD'
        WHEN 4 THEN 'Professional Qualification'
    END as education,
    CASE (id % 8)
        WHEN 0 THEN 'Software Engineer'
        WHEN 1 THEN 'Doctor'
        WHEN 2 THEN 'Teacher'
        WHEN 3 THEN 'Bank Officer'
        WHEN 4 THEN 'Accountant'
        WHEN 5 THEN 'Lawyer'
        WHEN 6 THEN 'Government Officer'
        WHEN 7 THEN 'Business Owner'
    END as occupation,
    50000 + (id % 8) * 25000 as monthly_income,
    150 + (id % 15) as height,
    45 + (id % 15) as weight,
    'never_married' as marital_status,
    'never' as drinking_habits,
    'never' as smoking_habits,
    CASE (id % 8)
        WHEN 0 THEN 'Colombo'
        WHEN 1 THEN 'Kandy'
        WHEN 2 THEN 'Galle'
        WHEN 3 THEN 'Negombo'
        WHEN 4 THEN 'Matara'
        WHEN 5 THEN 'Kurunegala'
        WHEN 6 THEN 'Jaffna'
        WHEN 7 THEN 'Batticaloa'
    END as location,
    CONCAT('Hi, I am a ', 
        CASE (id % 8)
            WHEN 0 THEN 'Software Engineer'
            WHEN 1 THEN 'Doctor'
            WHEN 2 THEN 'Teacher'
            WHEN 3 THEN 'Bank Officer'
            WHEN 4 THEN 'Accountant'
            WHEN 5 THEN 'Lawyer'
            WHEN 6 THEN 'Government Officer'
            WHEN 7 THEN 'Business Owner'
        END,
        ' looking for a life partner who shares my values.'
    ) as bio,
    '{"age_range": {"min": 25, "max": 40}, "preferred_religion": "any"}' as preferences
FROM users 
WHERE id > 501;

-- Insert some initial compatibility scores (random sampling)
INSERT INTO compatibility_scores (user_id_1, user_id_2, score)
SELECT 
    m.user_id as user_id_1,
    f.user_id as user_id_2,
    FLOOR(60 + (RAND() * 40)) as score
FROM 
    (SELECT user_id FROM user_profiles WHERE gender = 'male' ORDER BY RAND() LIMIT 100) m
    CROSS JOIN
    (SELECT user_id FROM user_profiles WHERE gender = 'female' ORDER BY RAND() LIMIT 100) f;

-- Insert some premium memberships (random sampling)
INSERT INTO premium_memberships (user_id, plan_type, start_date, end_date, payment_status)
SELECT 
    id,
    CASE (id % 2)
        WHEN 0 THEN 'gold'
        ELSE 'platinum'
    END as plan_type,
    NOW() as start_date,
    DATE_ADD(NOW(), INTERVAL 1 MONTH) as end_date,
    'paid' as payment_status
FROM users 
WHERE role = 'user'
ORDER BY RAND()
LIMIT 50; 