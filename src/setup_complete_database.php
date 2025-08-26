<?php
require_once 'services/database.php';

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Complete Database Setup</title>";
echo "<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
.success { color: green; }
.error { color: red; }
.warning { color: orange; }
.info { color: blue; }
pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
table { border-collapse: collapse; width: 100%; margin: 10px 0; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style></head><body>";

echo "<h1>🚀 Complete Database Setup</h1>";

try {
    // Read the SQL file
    $sqlFile = __DIR__ . '/complete_database_setup.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL file not found: $sqlFile");
    }
    
    echo "<p class='info'>📖 Reading SQL file: complete_database_setup.sql</p>";
    
    $sql = file_get_contents($sqlFile);
    if ($sql === false) {
        throw new Exception("Could not read SQL file");
    }
    
    // Split SQL into statements
    $statements = array_filter(
        array_map('trim', preg_split('/;(?=(?:[^\']*\'[^\']*\')*[^\']*$)/', $sql)),
        function($stmt) {
            return !empty($stmt) && 
                   !preg_match('/^\s*--/', $stmt) && 
                   !preg_match('/^\s*\/\*/', $stmt) &&
                   strlen(trim($stmt)) > 0;
        }
    );
    
    echo "<p class='info'>📊 Found " . count($statements) . " SQL statements to execute</p>";
    
    // Execute statements
    $successCount = 0;
    $errorCount = 0;
    $results = [];
    
    // Disable foreign key checks temporarily
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");
    
    foreach ($statements as $index => $statement) {
        $statement = trim($statement);
        if (empty($statement)) continue;
        
        try {
            $result = $conn->query($statement);
            
            if ($result === false) {
                $error = $conn->error;
                $results[] = [
                    'type' => 'error',
                    'statement' => substr($statement, 0, 100) . (strlen($statement) > 100 ? '...' : ''),
                    'message' => $error
                ];
                $errorCount++;
            } else {
                $successCount++;
                
                // If it's a SELECT statement, capture results
                if (stripos($statement, 'SELECT') === 0 && $result instanceof mysqli_result) {
                    $data = [];
                    while ($row = $result->fetch_assoc()) {
                        $data[] = $row;
                    }
                    if (!empty($data)) {
                        $results[] = [
                            'type' => 'result',
                            'statement' => substr($statement, 0, 100) . (strlen($statement) > 100 ? '...' : ''),
                            'data' => $data
                        ];
                    }
                }
            }
        } catch (Exception $e) {
            $results[] = [
                'type' => 'error',
                'statement' => substr($statement, 0, 100) . (strlen($statement) > 100 ? '...' : ''),
                'message' => $e->getMessage()
            ];
            $errorCount++;
        }
    }
    
    // Re-enable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
    
    // Show summary
    echo "<h2>📈 Execution Summary</h2>";
    echo "<p class='success'>✅ Successful statements: $successCount</p>";
    if ($errorCount > 0) {
        echo "<p class='error'>❌ Failed statements: $errorCount</p>";
    }
    
    // Show results
    if (!empty($results)) {
        echo "<h2>📋 Execution Results</h2>";
        
        foreach ($results as $result) {
            if ($result['type'] === 'error') {
                echo "<div class='error'>";
                echo "<h4>❌ Error in statement:</h4>";
                echo "<pre>" . htmlspecialchars($result['statement']) . "</pre>";
                echo "<p><strong>Error:</strong> " . htmlspecialchars($result['message']) . "</p>";
                echo "</div>";
            } elseif ($result['type'] === 'result' && !empty($result['data'])) {
                echo "<div class='info'>";
                echo "<h4>📊 Query Result:</h4>";
                echo "<table>";
                
                // Table headers
                $headers = array_keys($result['data'][0]);
                echo "<tr>";
                foreach ($headers as $header) {
                    echo "<th>" . htmlspecialchars($header) . "</th>";
                }
                echo "</tr>";
                
                // Table rows
                foreach ($result['data'] as $row) {
                    echo "<tr>";
                    foreach ($row as $value) {
                        echo "<td>" . htmlspecialchars($value ?? '') . "</td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";
                echo "</div>";
            }
        }
    }
    
    // Final verification
    echo "<h2>🔍 Database Verification</h2>";
    
    // Check tables exist
    $result = $conn->query("SHOW TABLES");
    $tables = [];
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }
    
    echo "<p class='success'>✅ Found " . count($tables) . " tables in database</p>";
    echo "<p><strong>Tables:</strong> " . implode(', ', $tables) . "</p>";
    
    // Check sample data
    $sampleChecks = [
        'users' => 'SELECT COUNT(*) as count FROM users',
        'dictionary' => 'SELECT COUNT(*) as count FROM dictionary',
        'decks' => 'SELECT COUNT(*) as count FROM decks',
        'flashcards' => 'SELECT COUNT(*) as count FROM flashcards',
        'listening_exercises' => 'SELECT COUNT(*) as count FROM listening_exercises'
    ];
    
    echo "<h3>📊 Sample Data Verification</h3>";
    foreach ($sampleChecks as $table => $query) {
        if (in_array($table, $tables)) {
            try {
                $result = $conn->query($query);
                $count = $result->fetch_assoc()['count'];
                echo "<p class='success'>✅ $table: $count records</p>";
            } catch (Exception $e) {
                echo "<p class='error'>❌ $table: Error counting records</p>";
            }
        } else {
            echo "<p class='warning'>⚠️ $table: Table not found</p>";
        }
    }
    
    if ($errorCount === 0) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h2 class='success'>🎉 Database Setup Completed Successfully!</h2>";
        echo "<p><strong>Next Steps:</strong></p>";
        echo "<ol>";
        echo "<li>Test login with username: <code>testuser</code>, password: <code>password</code></li>";
        echo "<li>Visit <a href='test_flashcard_sync.php'>test_flashcard_sync.php</a> to verify functionality</li>";
        echo "<li>Go to <a href='flashcards.php'>flashcards.php</a> to test flashcard study</li>";
        echo "<li>Check <a href='stats.php'>stats.php</a> for statistics synchronization</li>";
        echo "<li>Try <a href='listening.php'>listening.php</a> for listening exercises</li>";
        echo "</ol>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h2 class='error'>⚠️ Setup Completed with Errors</h2>";
        echo "<p>Some statements failed to execute. Please review the errors above and fix any issues.</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h2 class='error'>❌ Setup Failed</h2>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "</body></html>";
$conn->close();
?>
