<?php
// backend/seed_data.php
require_once 'config/db.php';

echo "Seeding data for Jimma Institute of Technology...\n";

try {
    // 1. Ensure we have a creator user
    $email = 'student@ju.edu.et';
    $password = password_hash('password123', PASSWORD_DEFAULT);
    $checkUser = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkUser->execute([$email]);
    $user = $checkUser->fetch();

    if (!$user) {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, department, year_of_study, bio, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute(['Abebe Kebede', $email, $password, 'Software Engineering', '3rd Year', 'Passionate about mobile dev and AI.', 'user']);
        $userId = $conn->lastInsertId();
        echo "Created dummy user: Abebe (ID: $userId)\n";
    } else {
        $userId = $user['id'];
        echo "Using existing user ID: $userId\n";
    }

    // 2. Clear existing sample events (optional, maybe just add?) 
    // Let's just add for now to avoid deleting user data if they have any.

    // 3. Define Events
    $events = [
        [
            'title' => 'Flutter Development Workshop',
            'category' => 'Workshop',
            'description' => 'Join us for a hands-on session on building mobile apps with Flutter. Brought to you by GDG Jimma Campus. bring your laptops!',
            'date' => date('Y-m-d', strtotime('+2 days')),
            'time' => '14:00:00',
            'location' => 'JIT Computer Lab 3',
            'image' => 'flutter_workshop.jpg'
        ],
        [
            'title' => 'Data Structures Study Group',
            'category' => 'Study',
            'description' => 'Preparing for the upcoming algorithm analysis mid-exam. We will cover Graph traversals and DP.',
            'date' => date('Y-m-d', strtotime('+1 day')),
            'time' => '16:30:00',
            'location' => 'JIT Library, Group Study Room A',
            'image' => 'algorithms_study.jpg'
        ],
        [
            'title' => 'Freshman Tech Welcome Party',
            'category' => 'Social',
            'description' => 'Welcoming all new 1st year engineering students! Come network, eat snacks, and meet seniors.',
            'date' => date('Y-m-d', strtotime('+5 days')),
            'time' => '10:00:00',
            'location' => 'Main Hall',
            'image' => 'welcome_party.jpg'
        ],
        [
            'title' => 'AI Research Seminar: NLP for Amharic',
            'category' => 'Workshop',
            'description' => 'A seminar on the challenges and opportunities in Natural Language Processing for Ethiopian languages. Guest lecture from PhD candidates.',
            'date' => date('Y-m-d', strtotime('+3 days')),
            'time' => '09:00:00',
            'location' => 'Postgrad Seminar Room 101',
            'image' => 'ai_seminar.jpg'
        ],
        [
            'title' => 'Ethio-Robotics Club Meeting',
            'category' => 'Study',
            'description' => 'Working on the automated farming bot project. Electrical and Software students welcome.',
            'date' => date('Y-m-d', strtotime('+4 days')),
            'time' => '15:00:00',
            'location' => 'Robotics Lab (Block 4)',
            'image' => 'robotics.jpg'
        ]
    ];

    $stmt = $conn->prepare("INSERT INTO events (title, category, description, event_date, event_time, location, created_by, status) VALUES (:title, :category, :desc, :date, :time, :loc, :uid, 'approved')");

    foreach ($events as $evt) {
        $stmt->execute([
            ':title' => $evt['title'],
            ':category' => $evt['category'],
            ':desc' => $evt['description'],
            ':date' => $evt['date'],
            ':time' => $evt['time'],
            ':loc' => $evt['location'],
            ':uid' => $userId
        ]);
        echo "Created event: {$evt['title']}\n";
    }

    echo "Seeding Complete!\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>