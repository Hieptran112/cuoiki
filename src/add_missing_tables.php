<?php
require_once 'services/database.php';

header('Content-Type: text/plain; charset=utf-8');

echo "🔧 Adding Missing Tables Script\n";
echo "===============================\n\n";

try {
    echo "🔍 Checking for missing tables...\n";
    
    // Define all required tables and their creation SQL
    $requiredTables = [
        'topic_lessons' => "CREATE TABLE topic_lessons (
            id INT AUTO_INCREMENT PRIMARY KEY,
            topic_id INT NOT NULL,
            title VARCHAR(200) NOT NULL,
            content TEXT NOT NULL,
            lesson_order INT DEFAULT 0,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_topic_order (topic_id, lesson_order),
            CONSTRAINT fk_lessons_topic FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE
        )",
        
        'topic_progress' => "CREATE TABLE topic_progress (
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
        )",
        
        'topic_exercise_results' => "CREATE TABLE topic_exercise_results (
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
        )",
        
        'listening_results' => "CREATE TABLE listening_results (
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
        )",
        
        'user_word_review' => "CREATE TABLE user_word_review (
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
        )",
        
        'daily_stats' => "CREATE TABLE daily_stats (
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
        )",
        
        'exercise_results' => "CREATE TABLE exercise_results (
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
        )",
        
        'learning_stats' => "CREATE TABLE learning_stats (
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
        )"
    ];
    
    $missingTables = [];
    $existingTables = [];
    
    // Check which tables exist
    foreach ($requiredTables as $tableName => $createSQL) {
        $result = $conn->query("SHOW TABLES LIKE '$tableName'");
        if ($result->num_rows === 0) {
            $missingTables[] = $tableName;
        } else {
            $existingTables[] = $tableName;
        }
    }
    
    echo "📊 Table Status:\n";
    foreach ($existingTables as $table) {
        echo "  ✅ $table: EXISTS\n";
    }
    foreach ($missingTables as $table) {
        echo "  ❌ $table: MISSING\n";
    }
    
    if (empty($missingTables)) {
        echo "\n🎉 All tables already exist! No action needed.\n";
        echo "You can now run: php src/insert_all_data.php\n";
        exit(0);
    }
    
    echo "\n🔧 Creating missing tables...\n";
    
    // Disable foreign key checks temporarily
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");
    
    $createdCount = 0;
    $errorCount = 0;
    
    foreach ($missingTables as $tableName) {
        $createSQL = $requiredTables[$tableName];
        
        if ($conn->query($createSQL)) {
            echo "  ✅ Created: $tableName\n";
            $createdCount++;
        } else {
            echo "  ❌ Failed: $tableName - " . $conn->error . "\n";
            $errorCount++;
        }
    }
    
    // Re-enable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "\n📈 Summary:\n";
    echo "✅ Tables created: $createdCount\n";
    echo "❌ Errors: $errorCount\n";
    
    if ($errorCount === 0) {
        echo "\n🎉 SUCCESS! All missing tables have been created.\n\n";
        echo "🚀 Next step: Run the data insertion script\n";
        echo "   php src/insert_all_data.php\n\n";
        
        // Final verification
        echo "🔍 Final verification:\n";
        foreach ($requiredTables as $tableName => $createSQL) {
            $result = $conn->query("SHOW TABLES LIKE '$tableName'");
            if ($result->num_rows > 0) {
                echo "  ✅ $tableName: OK\n";
            } else {
                echo "  ❌ $tableName: STILL MISSING\n";
            }
        }
        
    } else {
        echo "\n⚠️  Some tables could not be created. Check the errors above.\n";
        echo "You may need to create them manually or fix the database permissions.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "\n";
}

$conn->close();
?>
