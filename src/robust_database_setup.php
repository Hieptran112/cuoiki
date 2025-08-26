<?php
require_once 'services/database.php';

header('Content-Type: text/plain; charset=utf-8');

echo "🔧 Robust Database Setup - Step by Step Execution...\n\n";

try {
    // Step 1: Create database and use it
    echo "📊 Step 1: Setting up database...\n";
    $conn->query("CREATE DATABASE IF NOT EXISTS eduapp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->query("USE eduapp");
    echo "✅ Database 'eduapp' ready\n\n";

    // Step 2: Clean up existing tables
    echo "🧹 Step 2: Cleaning up existing tables...\n";
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");
    
    $tables = ['study_progress', 'flashcards', 'decks', 'flashcard_decks', 'preset_decks', 
               'specialized_terms', 'learning_progress', 'topic_exercise_results', 
               'topic_progress', 'topic_lessons', 'topics', 'listening_results', 
               'listening_exercises', 'exercise_results', 'learning_stats', 
               'user_word_review', 'daily_stats', 'dictionary', 'users'];
    
    foreach ($tables as $table) {
        $conn->query("DROP TABLE IF EXISTS $table");
        echo "  ✅ Dropped: $table\n";
    }
    
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
    echo "✅ Cleanup completed\n\n";

    // Step 3: Create tables one by one
    echo "🏗️ Step 3: Creating tables...\n";
    
    // Users table
    $sql = "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NULL,
        major VARCHAR(100) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_username (username),
        INDEX idx_email (email)
    )";
    if ($conn->query($sql)) {
        echo "  ✅ Created: users\n";
    } else {
        echo "  ❌ Failed: users - " . $conn->error . "\n";
    }

    // Dictionary table
    $sql = "CREATE TABLE dictionary (
        id INT AUTO_INCREMENT PRIMARY KEY,
        word VARCHAR(255) NOT NULL UNIQUE,
        phonetic VARCHAR(100),
        vietnamese TEXT,
        english_definition TEXT,
        example TEXT,
        part_of_speech ENUM('noun', 'verb', 'adjective', 'adverb', 'pronoun', 'preposition', 'conjunction', 'interjection') DEFAULT 'noun',
        difficulty ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_word (word),
        INDEX idx_difficulty (difficulty),
        INDEX idx_part_of_speech (part_of_speech)
    )";
    if ($conn->query($sql)) {
        echo "  ✅ Created: dictionary\n";
    } else {
        echo "  ❌ Failed: dictionary - " . $conn->error . "\n";
    }

    // Decks table
    $sql = "CREATE TABLE decks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        name VARCHAR(150) NOT NULL,
        description TEXT,
        visibility ENUM('private','public') DEFAULT 'private',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_user_name (user_id, name),
        INDEX idx_user_id (user_id),
        CONSTRAINT fk_decks_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    if ($conn->query($sql)) {
        echo "  ✅ Created: decks\n";
    } else {
        echo "  ❌ Failed: decks - " . $conn->error . "\n";
    }

    // Flashcards table
    $sql = "CREATE TABLE flashcards (
        id INT AUTO_INCREMENT PRIMARY KEY,
        deck_id INT NOT NULL,
        word VARCHAR(255) NOT NULL,
        definition TEXT NOT NULL,
        example TEXT,
        image_url VARCHAR(500),
        audio_url VARCHAR(500),
        source_dictionary_id INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_deck_id (deck_id),
        INDEX idx_word (word),
        CONSTRAINT fk_flashcards_deck FOREIGN KEY (deck_id) REFERENCES decks(id) ON DELETE CASCADE,
        CONSTRAINT fk_flashcards_dictionary FOREIGN KEY (source_dictionary_id) REFERENCES dictionary(id) ON DELETE SET NULL
    )";
    if ($conn->query($sql)) {
        echo "  ✅ Created: flashcards\n";
    } else {
        echo "  ❌ Failed: flashcards - " . $conn->error . "\n";
    }

    // Study progress table
    $sql = "CREATE TABLE study_progress (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        flashcard_id INT NOT NULL,
        status ENUM('new','learning','review','mastered') DEFAULT 'new',
        ease_level ENUM('again','hard','good','easy') DEFAULT 'again',
        review_count INT DEFAULT 0,
        correct_count INT DEFAULT 0,
        incorrect_count INT DEFAULT 0,
        last_reviewed_at DATETIME NULL,
        next_due_at DATETIME NULL,
        sm2_ease_factor DECIMAL(4,2) DEFAULT 2.50,
        sm2_interval_days INT DEFAULT 0,
        sm2_repetitions INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_user_flashcard (user_id, flashcard_id),
        INDEX idx_user_next_due (user_id, next_due_at),
        CONSTRAINT fk_progress_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        CONSTRAINT fk_progress_flashcard FOREIGN KEY (flashcard_id) REFERENCES flashcards(id) ON DELETE CASCADE
    )";
    if ($conn->query($sql)) {
        echo "  ✅ Created: study_progress\n";
    } else {
        echo "  ❌ Failed: study_progress - " . $conn->error . "\n";
    }

    // Topics table
    $sql = "CREATE TABLE topics (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        color VARCHAR(7) DEFAULT '#667eea',
        icon VARCHAR(50) DEFAULT 'fas fa-book',
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_active (is_active)
    )";
    if ($conn->query($sql)) {
        echo "  ✅ Created: topics\n";
    } else {
        echo "  ❌ Failed: topics - " . $conn->error . "\n";
    }

    // Topic lessons table
    $sql = "CREATE TABLE topic_lessons (
        id INT AUTO_INCREMENT PRIMARY KEY,
        topic_id INT NOT NULL,
        title VARCHAR(200) NOT NULL,
        content TEXT NOT NULL,
        lesson_order INT DEFAULT 0,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_topic_order (topic_id, lesson_order),
        CONSTRAINT fk_lessons_topic FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE
    )";
    if ($conn->query($sql)) {
        echo "  ✅ Created: topic_lessons\n";
    } else {
        echo "  ❌ Failed: topic_lessons - " . $conn->error . "\n";
    }

    // Topic progress table
    $sql = "CREATE TABLE topic_progress (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        topic_id INT NOT NULL,
        lesson_id INT NOT NULL,
        total_questions INT DEFAULT 15,
        correct_answers INT DEFAULT 0,
        completion_percentage DECIMAL(5,2) DEFAULT 0.00,
        is_completed BOOLEAN DEFAULT FALSE,
        last_attempt_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        completed_at TIMESTAMP NULL,
        UNIQUE KEY unique_user_lesson (user_id, lesson_id),
        INDEX idx_user_topic (user_id, topic_id),
        INDEX idx_completion (is_completed),
        CONSTRAINT fk_topic_progress_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        CONSTRAINT fk_topic_progress_topic FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE,
        CONSTRAINT fk_topic_progress_lesson FOREIGN KEY (lesson_id) REFERENCES topic_lessons(id) ON DELETE CASCADE
    )";
    if ($conn->query($sql)) {
        echo "  ✅ Created: topic_progress\n";
    } else {
        echo "  ❌ Failed: topic_progress - " . $conn->error . "\n";
    }

    // Topic exercise results table
    $sql = "CREATE TABLE topic_exercise_results (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        lesson_id INT NOT NULL,
        question_text TEXT NOT NULL,
        user_answer TEXT,
        correct_answer TEXT,
        is_correct BOOLEAN NOT NULL,
        answered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_lesson (user_id, lesson_id),
        INDEX idx_answered_at (answered_at),
        CONSTRAINT fk_topic_results_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        CONSTRAINT fk_topic_results_lesson FOREIGN KEY (lesson_id) REFERENCES topic_lessons(id) ON DELETE CASCADE
    )";
    if ($conn->query($sql)) {
        echo "  ✅ Created: topic_exercise_results\n";
    } else {
        echo "  ❌ Failed: topic_exercise_results - " . $conn->error . "\n";
    }

    // Listening results table
    $sql = "CREATE TABLE listening_results (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        exercise_id INT NOT NULL,
        user_answer CHAR(1) NOT NULL,
        is_correct BOOLEAN NOT NULL,
        completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_exercise_id (exercise_id),
        INDEX idx_completed_at (completed_at),
        CONSTRAINT fk_listening_results_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        CONSTRAINT fk_listening_results_exercise FOREIGN KEY (exercise_id) REFERENCES listening_exercises(id) ON DELETE CASCADE
    )";
    if ($conn->query($sql)) {
        echo "  ✅ Created: listening_results\n";
    } else {
        echo "  ❌ Failed: listening_results - " . $conn->error . "\n";
    }

    // User word review table (for dictionary)
    $sql = "CREATE TABLE user_word_review (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        dictionary_id INT NOT NULL,
        correct_count INT DEFAULT 0,
        wrong_count INT DEFAULT 0,
        last_correct_date DATE NULL,
        last_wrong_date DATE NULL,
        next_review_date DATE NULL,
        difficulty ENUM('de', 'kha_kho', 'rat_kho') DEFAULT 'de',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_user_word (user_id, dictionary_id),
        INDEX idx_next_review (next_review_date),
        CONSTRAINT fk_word_review_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        CONSTRAINT fk_word_review_dictionary FOREIGN KEY (dictionary_id) REFERENCES dictionary(id) ON DELETE CASCADE
    )";
    if ($conn->query($sql)) {
        echo "  ✅ Created: user_word_review\n";
    } else {
        echo "  ❌ Failed: user_word_review - " . $conn->error . "\n";
    }

    // Daily stats table
    $sql = "CREATE TABLE daily_stats (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        stat_date DATE NOT NULL,
        exercises_completed INT DEFAULT 0,
        correct_answers INT DEFAULT 0,
        total_answers INT DEFAULT 0,
        study_time_minutes INT DEFAULT 0,
        points_earned INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_user_date (user_id, stat_date),
        INDEX idx_stat_date (stat_date),
        CONSTRAINT fk_daily_stats_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    if ($conn->query($sql)) {
        echo "  ✅ Created: daily_stats\n";
    } else {
        echo "  ❌ Failed: daily_stats - " . $conn->error . "\n";
    }

    // Exercise results table (general)
    $sql = "CREATE TABLE exercise_results (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        exercise_id INT NOT NULL,
        selected_answer INT NOT NULL,
        correct_answer INT NOT NULL,
        is_correct BOOLEAN NOT NULL,
        submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_submitted_at (submitted_at),
        CONSTRAINT fk_exercise_results_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    if ($conn->query($sql)) {
        echo "  ✅ Created: exercise_results\n";
    } else {
        echo "  ❌ Failed: exercise_results - " . $conn->error . "\n";
    }

    // Learning stats table (summary)
    $sql = "CREATE TABLE learning_stats (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        words_learned INT DEFAULT 0,
        correct_answers INT DEFAULT 0,
        total_answers INT DEFAULT 0,
        streak_days INT DEFAULT 0,
        last_study_date DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_user (user_id),
        CONSTRAINT fk_learning_stats_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    if ($conn->query($sql)) {
        echo "  ✅ Created: learning_stats\n";
    } else {
        echo "  ❌ Failed: learning_stats - " . $conn->error . "\n";
    }

    // Listening exercises table
    $sql = "CREATE TABLE listening_exercises (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        question TEXT NOT NULL,
        audio_url VARCHAR(500) NOT NULL,
        option_a VARCHAR(255) NOT NULL,
        option_b VARCHAR(255) NOT NULL,
        option_c VARCHAR(255) NOT NULL,
        option_d VARCHAR(255) NOT NULL,
        correct_answer CHAR(1) NOT NULL,
        explanation TEXT,
        difficulty ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_difficulty (difficulty),
        INDEX idx_active (is_active)
    )";
    if ($conn->query($sql)) {
        echo "  ✅ Created: listening_exercises\n";
    } else {
        echo "  ❌ Failed: listening_exercises - " . $conn->error . "\n";
    }

    echo "✅ Table creation completed\n\n";

    // Step 4: Insert sample data
    echo "📝 Step 4: Inserting sample data...\n";

    // Insert users
    $sql = "INSERT IGNORE INTO users (username, email, password, full_name) VALUES 
            ('admin', 'admin@eduapp.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator'),
            ('testuser', 'test@example.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test User'),
            ('student1', 'student1@example.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Student One')";
    if ($conn->query($sql)) {
        echo "  ✅ Inserted: users (3 records)\n";
    } else {
        echo "  ❌ Failed: users - " . $conn->error . "\n";
    }

    // Get user IDs
    $result = $conn->query("SELECT id FROM users WHERE username = 'admin' LIMIT 1");
    $admin_id = $result->fetch_assoc()['id'];
    
    $result = $conn->query("SELECT id FROM users WHERE username = 'testuser' LIMIT 1");
    $test_user_id = $result->fetch_assoc()['id'];

    echo "  📊 Admin ID: $admin_id, Test User ID: $test_user_id\n";

    // Insert sample dictionary words
    $words = [
        ['hello', '/həˈloʊ/', 'xin chào', 'a greeting used when meeting someone', 'Hello, how are you?', 'interjection'],
        ['world', '/wɜːrld/', 'thế giới', 'the earth and all the people and things on it', 'The world is beautiful.', 'noun'],
        ['computer', '/kəmˈpjuːtər/', 'máy tính', 'an electronic device for processing data', 'I use my computer every day.', 'noun'],
        ['study', '/ˈstʌdi/', 'học tập', 'to learn about something', 'I study English every day.', 'verb'],
        ['book', '/bʊk/', 'sách', 'a set of printed pages bound together', 'This is a good book.', 'noun'],
        ['water', '/ˈwɔːtər/', 'nước', 'a clear liquid that has no color, taste, or smell', 'I drink water every day.', 'noun'],
        ['food', '/fuːd/', 'thức ăn', 'things that people eat', 'This food is delicious.', 'noun'],
        ['house', '/haʊs/', 'nhà', 'a building where people live', 'My house is big.', 'noun'],
        ['family', '/ˈfæməli/', 'gia đình', 'a group of people related to each other', 'I love my family.', 'noun'],
        ['friend', '/frend/', 'bạn bè', 'a person you like and know well', 'He is my best friend.', 'noun']
    ];

    $stmt = $conn->prepare("INSERT IGNORE INTO dictionary (word, phonetic, vietnamese, english_definition, example, part_of_speech, difficulty) VALUES (?, ?, ?, ?, ?, ?, 'beginner')");
    $dictCount = 0;
    foreach ($words as $word) {
        $stmt->bind_param("ssssss", $word[0], $word[1], $word[2], $word[3], $word[4], $word[5]);
        if ($stmt->execute()) $dictCount++;
    }
    echo "  ✅ Inserted: dictionary ($dictCount records)\n";

    // Insert sample decks
    $decks = [
        ['Từ vựng cơ bản', 'Các từ vựng tiếng Anh cơ bản hàng ngày', 'public'],
        ['Động từ thường dùng', 'Các động từ tiếng Anh thường gặp', 'public'],
        ['Tính từ mô tả', 'Các tính từ mô tả tính cách và đặc điểm', 'public'],
        ['Bộ thẻ cá nhân', 'Bộ thẻ riêng của tôi', 'private']
    ];

    $stmt = $conn->prepare("INSERT IGNORE INTO decks (user_id, name, description, visibility) VALUES (?, ?, ?, ?)");
    $deckCount = 0;
    foreach ($decks as $i => $deck) {
        $userId = ($i < 3) ? $admin_id : $test_user_id; // First 3 for admin, last for test user
        $stmt->bind_param("isss", $userId, $deck[0], $deck[1], $deck[2]);
        if ($stmt->execute()) $deckCount++;
    }
    echo "  ✅ Inserted: decks ($deckCount records)\n";

    // Get deck ID for flashcards
    $result = $conn->query("SELECT id FROM decks WHERE name = 'Từ vựng cơ bản' AND user_id = $admin_id LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $deck_id = $result->fetch_assoc()['id'];
        echo "  📊 Basic vocabulary deck ID: $deck_id\n";

        // Insert sample flashcards
        $flashcards = [
            ['hello', 'xin chào', 'Hello, how are you?'],
            ['world', 'thế giới', 'The world is beautiful.'],
            ['computer', 'máy tính', 'I use my computer every day.'],
            ['study', 'học tập', 'I study English every day.'],
            ['book', 'sách', 'This is a good book.']
        ];

        $stmt = $conn->prepare("INSERT IGNORE INTO flashcards (deck_id, word, definition, example) VALUES (?, ?, ?, ?)");
        $cardCount = 0;
        foreach ($flashcards as $card) {
            $stmt->bind_param("isss", $deck_id, $card[0], $card[1], $card[2]);
            if ($stmt->execute()) $cardCount++;
        }
        echo "  ✅ Inserted: flashcards ($cardCount records)\n";
    }

    echo "✅ Sample data insertion completed\n\n";

    // Step 5: Final verification
    echo "🔍 Step 5: Final verification...\n";
    
    $verifyQueries = [
        'users' => 'SELECT COUNT(*) as count FROM users',
        'dictionary' => 'SELECT COUNT(*) as count FROM dictionary',
        'topics' => 'SELECT COUNT(*) as count FROM topics',
        'topic_lessons' => 'SELECT COUNT(*) as count FROM topic_lessons',
        'decks' => 'SELECT COUNT(*) as count FROM decks',
        'flashcards' => 'SELECT COUNT(*) as count FROM flashcards',
        'listening_exercises' => 'SELECT COUNT(*) as count FROM listening_exercises',
        'study_progress' => 'SELECT COUNT(*) as count FROM study_progress',
        'topic_progress' => 'SELECT COUNT(*) as count FROM topic_progress',
        'learning_stats' => 'SELECT COUNT(*) as count FROM learning_stats',
        'daily_stats' => 'SELECT COUNT(*) as count FROM daily_stats'
    ];
    
    $allGood = true;
    foreach ($verifyQueries as $table => $query) {
        try {
            $result = $conn->query($query);
            if ($result) {
                $count = $result->fetch_assoc()['count'];
                echo "  ✅ $table: $count records\n";
            } else {
                echo "  ❌ $table: Query failed\n";
                $allGood = false;
            }
        } catch (Exception $e) {
            echo "  ❌ $table: " . $e->getMessage() . "\n";
            $allGood = false;
        }
    }

    if ($allGood) {
        echo "\n🎉 SUCCESS! Database setup completed successfully!\n\n";
        echo "🚀 Next steps:\n";
        echo "1. Visit: test_flashcard_sync.php\n";
        echo "2. Login with: testuser / password\n";
        echo "3. Test flashcards.php and stats.php\n";
        echo "4. All synchronization should now work properly!\n";
    } else {
        echo "\n⚠️ Setup completed with some issues. Please check the errors above.\n";
    }

} catch (Exception $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "\n";
}

$conn->close();
?>
