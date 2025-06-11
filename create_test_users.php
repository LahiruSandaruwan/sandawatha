<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$users = [
    // Male users
    [
        'email' => 'kasun.perera@gmail.com',
        'phone' => '+94771234001',
        'first_name' => 'Kasun',
        'last_name' => 'Perera',
        'gender' => 'male',
        'date_of_birth' => '1992-05-15',
        'religion' => 'Buddhist',
        'district' => 'Colombo',
        'marital_status' => 'never_married',
        'height_cm' => 178,
        'education' => 'Bachelor Degree',
        'occupation' => 'Software Engineer',
        'income_lkr' => 150000.00,
        'bio' => 'Passionate about technology and innovation. Looking for a life partner who shares similar interests.',
        'goals' => 'Building a successful career in tech while maintaining work-life balance.'
    ],
    [
        'email' => 'nuwan.silva@gmail.com',
        'phone' => '+94771234002',
        'first_name' => 'Nuwan',
        'last_name' => 'Silva',
        'gender' => 'male',
        'date_of_birth' => '1990-08-23',
        'religion' => 'Buddhist',
        'district' => 'Gampaha',
        'marital_status' => 'never_married',
        'height_cm' => 175,
        'education' => 'Masters Degree',
        'occupation' => 'Bank Manager',
        'income_lkr' => 200000.00,
        'bio' => 'Banking professional with a passion for finance and economics. Enjoy reading and traveling.',
        'goals' => 'Looking to settle down with someone who values family and career equally.'
    ],
    [
        'email' => 'mohamed.ali@gmail.com',
        'phone' => '+94771234003',
        'first_name' => 'Mohamed',
        'last_name' => 'Ali',
        'gender' => 'male',
        'date_of_birth' => '1993-11-30',
        'religion' => 'Islam',
        'district' => 'Kandy',
        'marital_status' => 'never_married',
        'height_cm' => 172,
        'education' => 'Bachelor Degree',
        'occupation' => 'Medical Doctor',
        'income_lkr' => 180000.00,
        'bio' => 'Doctor working in public healthcare. Passionate about serving the community.',
        'goals' => 'Seeking a partner who understands the demands of medical profession.'
    ],
    [
        'email' => 'anthony.fernando@gmail.com',
        'phone' => '+94771234004',
        'first_name' => 'Anthony',
        'last_name' => 'Fernando',
        'gender' => 'male',
        'date_of_birth' => '1991-04-12',
        'religion' => 'Catholic',
        'district' => 'Negombo',
        'marital_status' => 'never_married',
        'height_cm' => 180,
        'education' => 'Bachelor Degree',
        'occupation' => 'Business Owner',
        'income_lkr' => 250000.00,
        'bio' => 'Entrepreneur running a successful export business. Love traveling and experiencing new cultures.',
        'goals' => 'Looking for someone to share life adventures and build a future together.'
    ],
    [
        'email' => 'rajiv.kumar@gmail.com',
        'phone' => '+94771234005',
        'first_name' => 'Rajiv',
        'last_name' => 'Kumar',
        'gender' => 'male',
        'date_of_birth' => '1989-07-08',
        'religion' => 'Hindu',
        'district' => 'Colombo',
        'marital_status' => 'never_married',
        'height_cm' => 176,
        'education' => 'Masters Degree',
        'occupation' => 'Chartered Accountant',
        'income_lkr' => 190000.00,
        'bio' => 'Finance professional with a love for classical music and arts.',
        'goals' => 'Seeking a partner who appreciates culture and traditional values.'
    ],
    // Female users
    [
        'email' => 'dilini.fernando@gmail.com',
        'phone' => '+94771234006',
        'first_name' => 'Dilini',
        'last_name' => 'Fernando',
        'gender' => 'female',
        'date_of_birth' => '1994-02-14',
        'religion' => 'Buddhist',
        'district' => 'Colombo',
        'marital_status' => 'never_married',
        'height_cm' => 165,
        'education' => 'Bachelor Degree',
        'occupation' => 'Marketing Manager',
        'income_lkr' => 140000.00,
        'bio' => 'Creative professional working in digital marketing. Love photography and travel.',
        'goals' => 'Looking for a partner who is ambitious and values personal growth.'
    ],
    [
        'email' => 'fathima.hussain@gmail.com',
        'phone' => '+94771234007',
        'first_name' => 'Fathima',
        'last_name' => 'Hussain',
        'gender' => 'female',
        'date_of_birth' => '1993-09-20',
        'religion' => 'Islam',
        'district' => 'Kandy',
        'marital_status' => 'never_married',
        'height_cm' => 162,
        'education' => 'Masters Degree',
        'occupation' => 'University Lecturer',
        'income_lkr' => 160000.00,
        'bio' => 'Academic with a passion for teaching and research. Enjoy reading and writing.',
        'goals' => 'Seeking an intellectual partner who values education and family.'
    ],
    [
        'email' => 'mary.thomas@gmail.com',
        'phone' => '+94771234008',
        'first_name' => 'Mary',
        'last_name' => 'Thomas',
        'gender' => 'female',
        'date_of_birth' => '1992-12-25',
        'religion' => 'Catholic',
        'district' => 'Negombo',
        'marital_status' => 'never_married',
        'height_cm' => 168,
        'education' => 'Bachelor Degree',
        'occupation' => 'HR Manager',
        'income_lkr' => 145000.00,
        'bio' => 'HR professional who believes in work-life balance. Love cooking and music.',
        'goals' => 'Looking for someone who shares Christian values and family orientation.'
    ],
    [
        'email' => 'priya.sharma@gmail.com',
        'phone' => '+94771234009',
        'first_name' => 'Priya',
        'last_name' => 'Sharma',
        'gender' => 'female',
        'date_of_birth' => '1991-06-15',
        'religion' => 'Hindu',
        'district' => 'Colombo',
        'marital_status' => 'never_married',
        'height_cm' => 163,
        'education' => 'Bachelor Degree',
        'occupation' => 'Software Developer',
        'income_lkr' => 155000.00,
        'bio' => 'Tech enthusiast working in software development. Passionate about yoga and meditation.',
        'goals' => 'Looking for a partner who respects tradition while embracing modernity.'
    ],
    [
        'email' => 'chamari.silva@gmail.com',
        'phone' => '+94771234010',
        'first_name' => 'Chamari',
        'last_name' => 'Silva',
        'gender' => 'female',
        'date_of_birth' => '1994-08-30',
        'religion' => 'Buddhist',
        'district' => 'Gampaha',
        'marital_status' => 'never_married',
        'height_cm' => 166,
        'education' => 'Masters Degree',
        'occupation' => 'Bank Officer',
        'income_lkr' => 130000.00,
        'bio' => 'Banking professional who enjoys dancing and traditional arts.',
        'goals' => 'Seeking a partner who values both career and cultural heritage.'
    ]
];

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    $pdo->beginTransaction();

    foreach ($users as $userData) {
        // Create user
        $stmt = $pdo->prepare("
            INSERT INTO users (email, phone, password, role, status, email_verified, phone_verified)
            VALUES (:email, :phone, :password, 'user', 'active', 1, 1)
        ");
        
        $hashedPassword = password_hash('Test@123', PASSWORD_DEFAULT);
        $stmt->execute([
            ':email' => $userData['email'],
            ':phone' => $userData['phone'],
            ':password' => $hashedPassword
        ]);
        
        $userId = $pdo->lastInsertId();
        
        // Create profile
        $stmt = $pdo->prepare("
            INSERT INTO user_profiles (
                user_id, first_name, last_name, date_of_birth, gender,
                religion, district, marital_status, height_cm, education,
                occupation, income_lkr, bio, goals, profile_completion,
                privacy_settings
            ) VALUES (
                :user_id, :first_name, :last_name, :date_of_birth, :gender,
                :religion, :district, :marital_status, :height_cm, :education,
                :occupation, :income_lkr, :bio, :goals, 100,
                '{\"photo\":\"public\",\"contact\":\"registered\",\"horoscope\":\"private\",\"income\":\"private\",\"bio\":\"public\",\"education\":\"public\",\"occupation\":\"public\",\"goals\":\"private\"}'
            )
        ");
        
        $stmt->execute([
            ':user_id' => $userId,
            ':first_name' => $userData['first_name'],
            ':last_name' => $userData['last_name'],
            ':date_of_birth' => $userData['date_of_birth'],
            ':gender' => $userData['gender'],
            ':religion' => $userData['religion'],
            ':district' => $userData['district'],
            ':marital_status' => $userData['marital_status'],
            ':height_cm' => $userData['height_cm'],
            ':education' => $userData['education'],
            ':occupation' => $userData['occupation'],
            ':income_lkr' => $userData['income_lkr'],
            ':bio' => $userData['bio'],
            ':goals' => $userData['goals']
        ]);
        
        echo "Created user and profile for {$userData['first_name']} {$userData['last_name']}\n";
    }
    
    $pdo->commit();
    echo "\nAll test users created successfully!\n";
    echo "You can log in with any of the email addresses and password: Test@123\n";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
} 