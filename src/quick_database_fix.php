<?php
require_once 'services/database.php';

header('Content-Type: text/plain; charset=utf-8');

echo "🔧 Quick Database Fix - Testing the corrected SQL file...\n\n";

try {
    // First, let's clean up any existing tables to start fresh
    echo "🧹 Cleaning up existing tables...\n";
    
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");
    
    $tables = ['study_progress', 'flashcards', 'decks', 'flashcard_decks', 'topic_exercise_results', 
               'topic_progress', 'topic_lessons', 'topics', 'listening_results', 'listening_exercises',
               'exercise_results', 'learning_stats', 'user_word_review', 'daily_stats', 'dictionary', 'users'];
    
    foreach ($tables as $table) {
        $conn->query("DROP TABLE IF EXISTS $table");
        echo "  ✅ Dropped table: $table\n";
    }
    
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "\n🚀 Running the corrected SQL setup...\n";
    
    // Read and execute the corrected SQL file
    $sqlFile = __DIR__ . '/complete_database_setup.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL file not found: $sqlFile");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Split into statements and execute
    $statements = array_filter(
        array_map('trim', preg_split('/;(?=(?:[^\']*\'[^\']*\')*[^\']*$)/', $sql)),
        function($stmt) {
            return !empty($stmt) && 
                   !preg_match('/^\s*--/', $stmt) && 
                   !preg_match('/^\s*\/\*/', $stmt) &&
                   strlen(trim($stmt)) > 0;
        }
    );
    
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement)) continue;
        
        $result = $conn->query($statement);
        
        if ($result === false) {
            echo "❌ Error: " . $conn->error . "\n";
            echo "   Statement: " . substr($statement, 0, 100) . "...\n";
            $errorCount++;
        } else {
            $successCount++;
            
            // Show results for SELECT statements
            if (stripos($statement, 'SELECT') === 0 && $result instanceof mysqli_result) {
                while ($row = $result->fetch_assoc()) {
                    if (isset($row['Status']) || isset($row['Info']) || isset($row['Message'])) {
                        $key = $row['Status'] ?? $row['Info'] ?? $row['Message'] ?? '';
                        $value = $row['Count'] ?? $row['Details'] ?? '';
                        if (!empty($key)) {
                            echo "  📊 $key" . (!empty($value) ? ": $value" : "") . "\n";
                        }
                    }
                }
            }
        }
    }
    
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "\n📈 Execution Summary:\n";
    echo "✅ Successful statements: $successCount\n";
    echo "❌ Failed statements: $errorCount\n";
    
    if ($errorCount === 0) {
        echo "\n🎉 SUCCESS! Database setup completed without errors!\n\n";
        
        // Quick verification
        echo "🔍 Quick Verification:\n";
        
        $verifyQueries = [
            'users' => 'SELECT COUNT(*) as count FROM users',
            'decks' => 'SELECT COUNT(*) as count FROM decks', 
            'flashcards' => 'SELECT COUNT(*) as count FROM flashcards',
            'dictionary' => 'SELECT COUNT(*) as count FROM dictionary'
        ];
        
        foreach ($verifyQueries as $table => $query) {
            try {
                $result = $conn->query($query);
                $count = $result->fetch_assoc()['count'];
                echo "  ✅ $table: $count records\n";
            } catch (Exception $e) {
                echo "  ❌ $table: Error - " . $e->getMessage() . "\n";
            }
        }
        
        echo "\n🚀 Ready to test!\n";
        echo "1. Visit: test_flashcard_sync.php\n";
        echo "2. Login with: testuser / password\n";
        echo "3. Test flashcards.php and stats.php\n";
        
    } else {
        echo "\n⚠️ Some errors occurred. Please check the output above.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "\n";
}

$conn->close();
?>
