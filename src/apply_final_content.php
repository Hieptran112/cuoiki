<?php
// Simple script to apply final English content
require_once 'services/database.php';

echo "<h2>🔧 Applying Final English Learning Content</h2>";
echo "<p><strong>Fixed Issues:</strong></p>";
echo "<ul>";
echo "<li>✅ Foreign key constraints using direct lesson IDs</li>";
echo "<li>✅ Added missing 'last_accessed' column</li>";
echo "<li>✅ All questions in English format</li>";
echo "<li>✅ Mixed Vietnamese and English answers</li>";
echo "</ul>";

try {
    // Read the SQL file
    $sql = file_get_contents('final_english_content.sql');
    
    // Split into individual statements
    $statements = explode(';', $sql);
    
    $successCount = 0;
    $errorCount = 0;
    
    echo "<h3>Execution Log:</h3>";
    echo "<div style='max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #f9f9f9;'>";
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        
        // Skip empty statements and comments
        if (empty($statement) || strpos($statement, '--') === 0 || strpos($statement, '/*') === 0) {
            continue;
        }
        
        try {
            if ($conn->query($statement)) {
                $successCount++;
                
                // Show different messages for different types
                if (strpos($statement, 'ALTER TABLE') === 0) {
                    echo "<p style='color: blue;'>🔧 Table structure updated</p>";
                } elseif (strpos($statement, 'INSERT INTO topics') === 0) {
                    echo "<p style='color: green;'>📚 Topics created</p>";
                } elseif (strpos($statement, 'INSERT INTO topic_lessons') === 0) {
                    echo "<p style='color: green;'>📖 Lessons created</p>";
                } elseif (strpos($statement, 'INSERT INTO topic_exercises') === 0) {
                    echo "<p style='color: green;'>❓ Exercises created</p>";
                } elseif (strpos($statement, 'DELETE') === 0) {
                    echo "<p style='color: orange;'>🗑️ Old data cleared</p>";
                } elseif (strpos($statement, 'SELECT') === 0) {
                    echo "<p style='color: purple;'>📊 Status check</p>";
                } else {
                    echo "<p style='color: green;'>✅ SQL executed</p>";
                }
            } else {
                $errorCount++;
                echo "<p style='color: red;'>❌ Error: " . $conn->error . "</p>";
                echo "<p style='color: red;'>Statement: " . substr($statement, 0, 100) . "...</p>";
            }
        } catch (Exception $e) {
            $errorCount++;
            echo "<p style='color: red;'>❌ Exception: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "</div>";
    
    if ($errorCount === 0) {
        echo "<h3 style='color: green;'>🎉 Success! All content applied successfully!</h3>";
    } else {
        echo "<h3 style='color: orange;'>⚠️ Completed with some errors</h3>";
    }
    
    echo "<p><strong>Results:</strong></p>";
    echo "<ul>";
    echo "<li>✅ Successful operations: $successCount</li>";
    echo "<li>❌ Errors: $errorCount</li>";
    echo "</ul>";
    
    // Check final results
    $topicsResult = $conn->query("SELECT COUNT(*) as count FROM topics WHERE is_active = 1");
    $topicsCount = $topicsResult->fetch_assoc()['count'];
    
    $lessonsResult = $conn->query("SELECT COUNT(*) as count FROM topic_lessons");
    $lessonsCount = $lessonsResult->fetch_assoc()['count'];
    
    $exercisesResult = $conn->query("SELECT COUNT(*) as count FROM topic_exercises");
    $exercisesCount = $exercisesResult->fetch_assoc()['count'];
    
    echo "<h3>📊 Final Statistics:</h3>";
    echo "<ul>";
    echo "<li>📚 Topics: $topicsCount</li>";
    echo "<li>📖 Lessons: $lessonsCount</li>";
    echo "<li>❓ Exercises: $exercisesCount</li>";
    echo "</ul>";
    
    echo "<h3>🎯 Question Examples:</h3>";
    echo "<ul>";
    echo "<li><strong>Vietnamese meaning:</strong> \"What does 'apple' mean in Vietnamese?\" → A. Táo ✓</li>";
    echo "<li><strong>English synonym:</strong> \"What is another word for 'good'?\" → A. Excellent ✓</li>";
    echo "<li><strong>Grammar:</strong> \"Which sentence is correct?\" → A. She goes to school ✓</li>";
    echo "</ul>";
    
    if ($errorCount === 0) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h4 style='color: #155724; margin: 0 0 10px 0;'>🎉 Ready to Use!</h4>";
        echo "<p style='margin: 0;'>All content has been successfully applied. You can now:</p>";
        echo "<ul style='margin: 10px 0 0 0;'>";
        echo "<li><a href='topics.php' style='color: #155724; font-weight: bold;'>Go to Topics Page</a></li>";
        echo "<li><a href='index.php' style='color: #155724; font-weight: bold;'>Go to Home Page</a></li>";
        echo "<li><a href='lesson.php?id=1' style='color: #155724; font-weight: bold;'>Try First Lesson</a></li>";
        echo "</ul>";
        echo "</div>";
    }

} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;'>";
    echo "<h4 style='color: #721c24;'>❌ Error occurred:</h4>";
    echo "<p style='color: #721c24;'>" . $e->getMessage() . "</p>";
    echo "</div>";
}

$conn->close();
?>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
    background: #f8f9fa;
}

h2, h3 {
    color: #333;
}

ul {
    line-height: 1.6;
}

a {
    color: #007bff;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}
</style>
