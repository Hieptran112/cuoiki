<?php
// Script to apply English learning content
require_once 'services/database.php';

echo "<h2>Applying English Learning Content</h2>";

try {
    // Read and execute the English learning content SQL
    echo "<p>Reading English learning content...</p>";
    $sql = file_get_contents('updates/english_learning_content.sql');

    // Split SQL statements
    $statements = explode(';', $sql);

    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            if ($conn->query($statement)) {
                echo "<p style='color: green;'>✓ Executed: " . substr($statement, 0, 50) . "...</p>";
            } else {
                echo "<p style='color: red;'>✗ Error: " . $conn->error . "</p>";
                echo "<p>Statement: " . $statement . "</p>";
            }
        }
    }

    echo "<h3 style='color: green;'>✓ English learning content applied successfully!</h3>";
    echo "<p>The application now contains proper English learning topics:</p>";
    echo "<ul>";
    echo "<li>Basic Vocabulary (Family, Colors, Food, Animals, Body Parts)</li>";
    echo "<li>Grammar Fundamentals (Present Simple, Articles, Plurals, etc.)</li>";
    echo "<li>Everyday Conversations (Greetings, Shopping, Directions, etc.)</li>";
    echo "<li>Listening Skills</li>";
    echo "<li>Reading Comprehension</li>";
    echo "</ul>";
    
    echo "<p><a href='topics.php'>Go to Topics Page</a> to see the new content.</p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

$conn->close();
?>
