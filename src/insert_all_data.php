<?php
require_once 'services/database.php';

header('Content-Type: text/plain; charset=utf-8');

echo "📊 COMPLETE DATA INSERTION SCRIPT\n";
echo "==================================\n\n";

try {
    // Check if tables exist
    echo "🔍 Checking database structure...\n";
    
    $requiredTables = ['users', 'dictionary', 'topics', 'topic_lessons', 'decks', 'flashcards', 'listening_exercises'];
    $missingTables = [];
    
    foreach ($requiredTables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows === 0) {
            $missingTables[] = $table;
        }
    }
    
    if (!empty($missingTables)) {
        echo "❌ Missing tables: " . implode(', ', $missingTables) . "\n";
        echo "⚠️  Please run 'robust_database_setup.php' first to create tables.\n";
        exit(1);
    }
    
    echo "✅ All required tables exist\n\n";
    
    // Read and execute the data insertion SQL
    echo "📝 Loading data insertion script...\n";
    
    $sqlFile = __DIR__ . '/complete_data_insertion.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("Data insertion file not found: $sqlFile");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Split into statements
    $statements = array_filter(
        array_map('trim', preg_split('/;(?=(?:[^\']*\'[^\']*\')*[^\']*$)/', $sql)),
        function($stmt) {
            return !empty($stmt) && 
                   !preg_match('/^\s*--/', $stmt) && 
                   !preg_match('/^\s*\/\*/', $stmt) &&
                   strlen(trim($stmt)) > 0;
        }
    );
    
    echo "📊 Found " . count($statements) . " SQL statements to execute\n\n";
    
    // Execute statements with progress tracking
    $successCount = 0;
    $errorCount = 0;
    $currentSection = '';
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement)) continue;
        
        // Track sections for better progress reporting
        if (strpos($statement, '-- ===') !== false) {
            if (preg_match('/-- =+ (.+) =+/', $statement, $matches)) {
                $currentSection = trim($matches[1]);
                echo "📂 $currentSection\n";
            }
            continue;
        }
        
        // Skip comments
        if (strpos($statement, '--') === 0) {
            continue;
        }
        
        try {
            $result = $conn->query($statement);
            
            if ($result === false) {
                echo "  ❌ Error: " . $conn->error . "\n";
                echo "     Statement: " . substr($statement, 0, 80) . "...\n";
                $errorCount++;
            } else {
                $successCount++;
                
                // Show results for SELECT statements
                if (stripos($statement, 'SELECT') === 0 && $result instanceof mysqli_result) {
                    while ($row = $result->fetch_assoc()) {
                        // Display summary information
                        if (isset($row['Status'])) {
                            echo "  🎉 " . $row['Status'] . "\n";
                        } elseif (isset($row['Category']) && isset($row['Count'])) {
                            if (!empty($row['Category']) && !empty($row['Count'])) {
                                echo "  📊 " . $row['Category'] . ": " . $row['Count'] . "\n";
                            }
                        } elseif (isset($row['Deck_Name']) && isset($row['Card_Count'])) {
                            if (!empty($row['Deck_Name']) && !empty($row['Card_Count'])) {
                                echo "  🃏 " . $row['Deck_Name'] . ": " . $row['Card_Count'] . " cards\n";
                            }
                        } elseif (isset($row['Username']) && isset($row['Details'])) {
                            if (!empty($row['Username']) && !empty($row['Details'])) {
                                echo "  👤 " . $row['Username'] . " - " . $row['Details'] . "\n";
                            }
                        } elseif (isset($row['Message'])) {
                            echo "  ✨ " . $row['Message'] . "\n";
                        }
                    }
                }
                
                // Show progress for INSERT statements
                if (stripos($statement, 'INSERT') === 0) {
                    $affectedRows = $conn->affected_rows;
                    if ($affectedRows > 0) {
                        echo "  ✅ Inserted $affectedRows record(s)\n";
                    }
                }
            }
        } catch (Exception $e) {
            echo "  ❌ Exception: " . $e->getMessage() . "\n";
            $errorCount++;
        }
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "📈 EXECUTION SUMMARY\n";
    echo "✅ Successful statements: $successCount\n";
    echo "❌ Failed statements: $errorCount\n";
    
    if ($errorCount === 0) {
        echo "\n🎉 SUCCESS! All data inserted successfully!\n\n";
        
        // Final verification
        echo "🔍 FINAL VERIFICATION:\n";
        
        $verifyQueries = [
            'Users' => 'SELECT COUNT(*) as count FROM users',
            'Dictionary Words' => 'SELECT COUNT(*) as count FROM dictionary',
            'Topics' => 'SELECT COUNT(*) as count FROM topics',
            'Topic Lessons' => 'SELECT COUNT(*) as count FROM topic_lessons',
            'Flashcard Decks' => 'SELECT COUNT(*) as count FROM decks',
            'Flashcards' => 'SELECT COUNT(*) as count FROM flashcards',
            'Listening Exercises' => 'SELECT COUNT(*) as count FROM listening_exercises'
        ];
        
        foreach ($verifyQueries as $name => $query) {
            try {
                $result = $conn->query($query);
                $count = $result->fetch_assoc()['count'];
                echo "  ✅ $name: $count\n";
            } catch (Exception $e) {
                echo "  ❌ $name: Error\n";
            }
        }
        
        echo "\n🚀 READY TO USE!\n";
        echo "================\n";
        echo "1. 🔐 Login: testuser / password\n";
        echo "2. 🃏 Flashcards: Visit flashcards.php\n";
        echo "3. 📊 Statistics: Visit stats.php\n";
        echo "4. 🎧 Listening: Visit listening.php\n";
        echo "5. 📚 Topics: Visit topics.php\n";
        echo "6. 🧪 Test: Visit test_flashcard_sync.php\n\n";
        
        echo "💡 Sample Content Available:\n";
        echo "   - 100+ dictionary words with phonetics\n";
        echo "   - 12 flashcard decks with 60+ cards\n";
        echo "   - 15 listening exercises\n";
        echo "   - 6 learning topics with lessons\n";
        echo "   - 5 user accounts for testing\n";
        echo "   - Complete data synchronization\n\n";
        
        echo "🎯 All features are now fully functional!\n";
        
    } else {
        echo "\n⚠️  Some errors occurred during insertion.\n";
        echo "The app may still work, but some data might be missing.\n";
        echo "Check the errors above and run the script again if needed.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

$conn->close();
?>
