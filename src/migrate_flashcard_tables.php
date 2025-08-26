<?php
require_once 'services/database.php';

header('Content-Type: text/plain; charset=utf-8');

echo "🔄 Migrating flashcard tables to fix synchronization issues...\n\n";

try {
    // Check if old flashcard_decks table exists
    $result = $conn->query("SHOW TABLES LIKE 'flashcard_decks'");
    $oldTableExists = $result->num_rows > 0;
    
    // Check if new decks table exists
    $result = $conn->query("SHOW TABLES LIKE 'decks'");
    $newTableExists = $result->num_rows > 0;
    
    echo "📊 Current table status:\n";
    echo "- flashcard_decks table exists: " . ($oldTableExists ? "✅ YES" : "❌ NO") . "\n";
    echo "- decks table exists: " . ($newTableExists ? "✅ YES" : "❌ NO") . "\n\n";
    
    if ($oldTableExists && !$newTableExists) {
        echo "🔄 Migrating from flashcard_decks to decks table...\n";
        
        // Create new decks table with proper structure
        $conn->query("CREATE TABLE decks (
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
        )");
        echo "✅ Created new decks table\n";
        
        // Migrate data from old table to new table
        $conn->query("INSERT INTO decks (id, user_id, name, description, created_at, updated_at)
                      SELECT id, user_id, name, description, created_at, updated_at 
                      FROM flashcard_decks");
        echo "✅ Migrated data from flashcard_decks to decks\n";
        
        // Update flashcards table foreign key constraint if needed
        $conn->query("ALTER TABLE flashcards DROP FOREIGN KEY IF EXISTS fk_flashcards_deck");
        $conn->query("ALTER TABLE flashcards ADD CONSTRAINT fk_flashcards_deck 
                      FOREIGN KEY (deck_id) REFERENCES decks(id) ON DELETE CASCADE");
        echo "✅ Updated flashcards foreign key constraint\n";
        
        // Drop old table
        $conn->query("DROP TABLE flashcard_decks");
        echo "✅ Dropped old flashcard_decks table\n";
        
    } elseif (!$newTableExists) {
        echo "🔄 Creating new decks table...\n";
        
        // Create new decks table
        $conn->query("CREATE TABLE IF NOT EXISTS decks (
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
        )");
        echo "✅ Created decks table\n";
        
    } else {
        echo "✅ Tables are already in correct state\n";
    }
    
    // Ensure flashcards table has correct structure
    echo "\n🔄 Checking flashcards table structure...\n";
    
    $result = $conn->query("DESCRIBE flashcards");
    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    
    $requiredColumns = ['id', 'deck_id', 'word', 'definition', 'example', 'image_url', 'audio_url', 'source_dictionary_id'];
    $missingColumns = array_diff($requiredColumns, $columns);
    
    if (!empty($missingColumns)) {
        echo "❌ Missing columns in flashcards table: " . implode(', ', $missingColumns) . "\n";
        
        // Add missing columns
        if (in_array('word', $missingColumns)) {
            $conn->query("ALTER TABLE flashcards ADD COLUMN word VARCHAR(255) NOT NULL AFTER deck_id");
            echo "✅ Added word column\n";
        }
        if (in_array('definition', $missingColumns)) {
            $conn->query("ALTER TABLE flashcards ADD COLUMN definition TEXT NOT NULL AFTER word");
            echo "✅ Added definition column\n";
        }
        if (in_array('example', $missingColumns)) {
            $conn->query("ALTER TABLE flashcards ADD COLUMN example TEXT AFTER definition");
            echo "✅ Added example column\n";
        }
        if (in_array('image_url', $missingColumns)) {
            $conn->query("ALTER TABLE flashcards ADD COLUMN image_url VARCHAR(500) AFTER example");
            echo "✅ Added image_url column\n";
        }
        if (in_array('audio_url', $missingColumns)) {
            $conn->query("ALTER TABLE flashcards ADD COLUMN audio_url VARCHAR(500) AFTER image_url");
            echo "✅ Added audio_url column\n";
        }
        if (in_array('source_dictionary_id', $missingColumns)) {
            $conn->query("ALTER TABLE flashcards ADD COLUMN source_dictionary_id INT NULL AFTER audio_url");
            echo "✅ Added source_dictionary_id column\n";
        }
    } else {
        echo "✅ Flashcards table structure is correct\n";
    }
    
    // Ensure study_progress table exists
    echo "\n🔄 Checking study_progress table...\n";
    
    $result = $conn->query("SHOW TABLES LIKE 'study_progress'");
    if ($result->num_rows === 0) {
        echo "🔄 Creating study_progress table...\n";
        $conn->query("CREATE TABLE study_progress (
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
        )");
        echo "✅ Created study_progress table\n";
    } else {
        echo "✅ study_progress table already exists\n";
    }
    
    // Show final status
    echo "\n📊 Final table counts:\n";
    
    $result = $conn->query("SELECT COUNT(*) as count FROM decks");
    $deckCount = $result->fetch_assoc()['count'];
    echo "- Decks: $deckCount\n";
    
    $result = $conn->query("SELECT COUNT(*) as count FROM flashcards");
    $cardCount = $result->fetch_assoc()['count'];
    echo "- Flashcards: $cardCount\n";
    
    $result = $conn->query("SELECT COUNT(*) as count FROM study_progress");
    $progressCount = $result->fetch_assoc()['count'];
    echo "- Study progress records: $progressCount\n";
    
    echo "\n🎉 Migration completed successfully!\n";
    echo "✅ Flashcard and statistics synchronization should now work properly.\n";
    
} catch (Exception $e) {
    echo "❌ Error during migration: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

$conn->close();
?>
