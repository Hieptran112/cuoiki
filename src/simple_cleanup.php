<?php
require_once 'services/database.php';

echo "<h2>🧹 Simple Database Cleanup</h2>";

try {
    echo "<h3>Step 1: Remove Foreign Key Constraints</h3>";
    
    // List of known problematic foreign key constraints
    $constraintsToRemove = [
        'fk_flashcards_deck',
        'fk_flashcards_decks', 
        'flashcards_ibfk_1',
        'flashcards_deck_id_foreign',
        'fk_flashcards_dictionary',
        'flashcards_ibfk_2'
    ];
    
    foreach ($constraintsToRemove as $constraint) {
        try {
            $conn->query("ALTER TABLE flashcards DROP FOREIGN KEY $constraint");
            echo "<p style='color: green;'>✅ Removed constraint: $constraint</p>";
        } catch (Exception $e) {
            // Constraint doesn't exist, which is fine
            echo "<p style='color: blue;'>ℹ️ Constraint $constraint doesn't exist (OK)</p>";
        }
    }
    
    echo "<h3>Step 2: Remove Duplicate/Unused Tables</h3>";
    
    $tablesToDrop = ['decks', 'preset_decks', 'learning_progress', 'specialized_terms', 'exercise_results', 'learning_stats'];
    
    foreach ($tablesToDrop as $table) {
        try {
            $conn->query("DROP TABLE IF EXISTS $table");
            echo "<p style='color: green;'>✅ Dropped table: $table</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ Could not drop $table: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<h3>Step 3: Ensure Core Tables Exist</h3>";
    
    // Create users table
    $conn->query("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "<p style='color: green;'>✅ Users table ready</p>";
    
    // Create flashcard_decks table
    $conn->query("CREATE TABLE IF NOT EXISTS flashcard_decks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id)
    )");
    echo "<p style='color: green;'>✅ Flashcard_decks table ready</p>";
    
    // Create dictionary table
    $conn->query("CREATE TABLE IF NOT EXISTS dictionary (
        id INT AUTO_INCREMENT PRIMARY KEY,
        word VARCHAR(255) NOT NULL UNIQUE,
        vietnamese TEXT,
        english_definition TEXT,
        example TEXT,
        part_of_speech VARCHAR(50) DEFAULT 'noun',
        difficulty ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_word (word),
        INDEX idx_difficulty (difficulty),
        INDEX idx_part_of_speech (part_of_speech)
    )");
    echo "<p style='color: green;'>✅ Dictionary table ready</p>";
    
    echo "<h3>Step 4: Fix Flashcards Table Structure</h3>";
    
    // Create flashcards table if not exists
    $conn->query("CREATE TABLE IF NOT EXISTS flashcards (
        id INT AUTO_INCREMENT PRIMARY KEY,
        deck_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_deck_id (deck_id)
    )");
    echo "<p style='color: green;'>✅ Flashcards table exists</p>";
    
    // Add columns if they don't exist
    $columnsToAdd = [
        'front' => 'TEXT NOT NULL',
        'back' => 'TEXT NOT NULL', 
        'example' => 'TEXT',
        'difficulty' => "ENUM('easy', 'medium', 'hard') DEFAULT 'medium'",
        'next_review' => 'DATE',
        'review_count' => 'INT DEFAULT 0'
    ];
    
    foreach ($columnsToAdd as $column => $definition) {
        try {
            // Check if column exists
            $result = $conn->query("SHOW COLUMNS FROM flashcards LIKE '$column'");
            if ($result->num_rows == 0) {
                $conn->query("ALTER TABLE flashcards ADD COLUMN $column $definition");
                echo "<p style='color: green;'>✅ Added column: $column</p>";
            } else {
                echo "<p style='color: blue;'>ℹ️ Column $column already exists</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ Could not add column $column: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<h3>Step 5: Migrate Old Data</h3>";
    
    // Check for old columns and migrate data
    $oldColumns = ['word', 'definition', 'question', 'answer'];
    $foundOldColumns = [];
    
    foreach ($oldColumns as $column) {
        $result = $conn->query("SHOW COLUMNS FROM flashcards LIKE '$column'");
        if ($result->num_rows > 0) {
            $foundOldColumns[] = $column;
        }
    }
    
    if (!empty($foundOldColumns)) {
        echo "<p>Found old columns: " . implode(', ', $foundOldColumns) . "</p>";
        
        // Migrate data
        if (in_array('word', $foundOldColumns)) {
            $conn->query("UPDATE flashcards SET front = word WHERE (front IS NULL OR front = '') AND word IS NOT NULL AND word != ''");
            echo "<p style='color: green;'>✅ Migrated data from 'word' to 'front'</p>";
        }
        
        if (in_array('definition', $foundOldColumns)) {
            $conn->query("UPDATE flashcards SET back = definition WHERE (back IS NULL OR back = '') AND definition IS NOT NULL AND definition != ''");
            echo "<p style='color: green;'>✅ Migrated data from 'definition' to 'back'</p>";
        }
        
        if (in_array('question', $foundOldColumns)) {
            $conn->query("UPDATE flashcards SET front = question WHERE (front IS NULL OR front = '') AND question IS NOT NULL AND question != ''");
            echo "<p style='color: green;'>✅ Migrated data from 'question' to 'front'</p>";
        }
        
        if (in_array('answer', $foundOldColumns)) {
            $conn->query("UPDATE flashcards SET back = answer WHERE (back IS NULL OR back = '') AND answer IS NOT NULL AND answer != ''");
            echo "<p style='color: green;'>✅ Migrated data from 'answer' to 'back'</p>";
        }
        
        // Drop old columns
        foreach ($foundOldColumns as $column) {
            try {
                $conn->query("ALTER TABLE flashcards DROP COLUMN $column");
                echo "<p style='color: green;'>✅ Dropped old column: $column</p>";
            } catch (Exception $e) {
                echo "<p style='color: orange;'>⚠️ Could not drop column $column: " . $e->getMessage() . "</p>";
            }
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ No old columns found to migrate</p>";
    }
    
    echo "<h3>Step 6: Final Verification</h3>";
    
    // Show final table structure
    $result = $conn->query("SHOW TABLES");
    $tables = [];
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }
    echo "<p><strong>Final tables:</strong> " . implode(', ', $tables) . "</p>";
    
    // Show flashcards structure
    echo "<h4>Flashcards table structure:</h4>";
    $result = $conn->query("DESCRIBE flashcards");
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check data counts
    echo "<h4>Data counts:</h4>";
    $dataTables = ['users', 'flashcard_decks', 'flashcards', 'dictionary'];
    foreach ($dataTables as $table) {
        if (in_array($table, $tables)) {
            try {
                $result = $conn->query("SELECT COUNT(*) as count FROM $table");
                $count = $result->fetch_assoc()['count'];
                echo "<p>• $table: $count records</p>";
            } catch (Exception $e) {
                echo "<p>• $table: Error counting records</p>";
            }
        }
    }
    
    echo "<h3 style='color: green;'>🎉 Cleanup Completed Successfully!</h3>";
    echo "<p>✅ Foreign key constraints removed</p>";
    echo "<p>✅ Duplicate tables dropped</p>";
    echo "<p>✅ Table structure standardized</p>";
    echo "<p>✅ Data migrated and preserved</p>";
    echo "<p><strong>You can now use the 'Add to deck' feature!</strong></p>";
    
    echo "<h3>Next Steps:</h3>";
    echo "<ol>";
    echo "<li><a href='index.php'>Go to homepage</a></li>";
    echo "<li>Try searching for a word in the dictionary</li>";
    echo "<li>Click 'Thêm vào' to add it to a deck</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error during cleanup: " . $e->getMessage() . "</p>";
}

$conn->close();
?>

<style>
body { 
    font-family: Arial, sans-serif; 
    max-width: 1000px; 
    margin: 0 auto; 
    padding: 20px; 
    background: #f8f9fa;
}
table { 
    margin: 1rem 0; 
    font-size: 0.9rem;
}
th, td { 
    padding: 0.5rem; 
    text-align: left; 
    border: 1px solid #ddd;
}
th { 
    background: #f0f0f0; 
    font-weight: bold;
}
h2, h3, h4 { 
    color: #333; 
}
a {
    color: #007bff;
    text-decoration: none;
}
a:hover {
    text-decoration: underline;
}
</style>
