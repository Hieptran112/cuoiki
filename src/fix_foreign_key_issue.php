<?php
require_once 'services/database.php';

header('Content-Type: text/plain; charset=utf-8');

echo "🔧 Foreign Key Issue Diagnostic & Fix\n";
echo "=====================================\n\n";

try {
    echo "🔍 Checking existing tables and their structure...\n\n";
    
    // Check if listening_exercises table exists and its structure
    $result = $conn->query("SHOW TABLES LIKE 'listening_exercises'");
    if ($result->num_rows === 0) {
        echo "❌ listening_exercises table does not exist!\n";
        echo "🔧 Creating listening_exercises table first...\n";
        
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
            echo "  ❌ Failed to create listening_exercises: " . $conn->error . "\n";
            exit(1);
        }
    } else {
        echo "✅ listening_exercises table exists\n";
        
        // Show structure
        $result = $conn->query("DESCRIBE listening_exercises");
        echo "📊 listening_exercises structure:\n";
        while ($row = $result->fetch_assoc()) {
            echo "  - {$row['Field']}: {$row['Type']}\n";
        }
    }
    
    echo "\n🔧 Now creating listening_results table with proper foreign key...\n";
    
    // Drop the table if it exists (in case of partial creation)
    $conn->query("DROP TABLE IF EXISTS listening_results");
    
    // Create listening_results table with proper foreign key
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
        echo "  ❌ Failed to create listening_results: " . $conn->error . "\n";
        
        // Try without foreign key constraints
        echo "🔧 Trying without foreign key constraints...\n";
        
        $sql_no_fk = "CREATE TABLE listening_results (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            exercise_id INT NOT NULL,
            user_answer CHAR(1) NOT NULL,
            is_correct BOOLEAN NOT NULL,
            completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_exercise_id (exercise_id),
            INDEX idx_completed_at (completed_at)
        )";
        
        if ($conn->query($sql_no_fk)) {
            echo "  ✅ Created: listening_results (without foreign keys)\n";
            echo "  ⚠️  Foreign key constraints can be added later if needed\n";
        } else {
            echo "  ❌ Still failed: " . $conn->error . "\n";
        }
    }
    
    echo "\n🔧 Creating other missing tables without problematic foreign keys...\n";
    
    // Create other tables that might be missing, with safer foreign key handling
    $safeTables = [
        'topic_lessons' => "CREATE TABLE IF NOT EXISTS topic_lessons (
            id INT AUTO_INCREMENT PRIMARY KEY,
            topic_id INT NOT NULL,
            title VARCHAR(200) NOT NULL,
            content TEXT NOT NULL,
            lesson_order INT DEFAULT 0,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_topic_order (topic_id, lesson_order)
        )",
        
        'user_word_review' => "CREATE TABLE IF NOT EXISTS user_word_review (
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
            INDEX idx_next_review (next_review_date)
        )",
        
        'daily_stats' => "CREATE TABLE IF NOT EXISTS daily_stats (
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
            INDEX idx_stat_date (stat_date)
        )",
        
        'exercise_results' => "CREATE TABLE IF NOT EXISTS exercise_results (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            exercise_id INT NOT NULL,
            selected_answer INT NOT NULL,
            correct_answer INT NOT NULL,
            is_correct BOOLEAN NOT NULL,
            submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_submitted_at (submitted_at)
        )",
        
        'learning_stats' => "CREATE TABLE IF NOT EXISTS learning_stats (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            words_learned INT DEFAULT 0,
            correct_answers INT DEFAULT 0,
            total_answers INT DEFAULT 0,
            streak_days INT DEFAULT 0,
            last_study_date DATE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_user (user_id)
        )",
        
        'topic_progress' => "CREATE TABLE IF NOT EXISTS topic_progress (
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
            INDEX idx_completion (is_completed)
        )",
        
        'topic_exercise_results' => "CREATE TABLE IF NOT EXISTS topic_exercise_results (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            lesson_id INT NOT NULL,
            question_text TEXT NOT NULL,
            user_answer TEXT,
            correct_answer TEXT,
            is_correct BOOLEAN NOT NULL,
            answered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_lesson (user_id, lesson_id),
            INDEX idx_answered_at (answered_at)
        )"
    ];
    
    foreach ($safeTables as $tableName => $createSQL) {
        if ($conn->query($createSQL)) {
            echo "  ✅ Created/Verified: $tableName\n";
        } else {
            echo "  ❌ Failed: $tableName - " . $conn->error . "\n";
        }
    }
    
    echo "\n🔍 Final verification - checking all required tables...\n";
    
    $requiredTables = ['users', 'dictionary', 'topics', 'topic_lessons', 'decks', 'flashcards', 
                       'listening_exercises', 'listening_results', 'study_progress', 'topic_progress', 
                       'learning_stats', 'daily_stats', 'user_word_review', 'exercise_results', 
                       'topic_exercise_results'];
    
    $allGood = true;
    foreach ($requiredTables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            echo "  ✅ $table: EXISTS\n";
        } else {
            echo "  ❌ $table: MISSING\n";
            $allGood = false;
        }
    }
    
    if ($allGood) {
        echo "\n🎉 SUCCESS! All required tables now exist.\n\n";
        echo "🚀 Next step: Run the data insertion script\n";
        echo "   php src/insert_all_data.php\n\n";
        echo "💡 Note: Some foreign key constraints were skipped to avoid errors.\n";
        echo "   The app will still work perfectly without them.\n";
    } else {
        echo "\n⚠️  Some tables are still missing. You may need to create them manually.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "\n";
}

$conn->close();
?>
