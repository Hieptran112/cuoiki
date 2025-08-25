<?php
// Test script for listening functionality
require_once 'services/database.php';

echo "<h2>🎧 Test Listening Functionality</h2>";

// Test 1: Check if listening_exercises table exists
echo "<h3>1. Checking Listening Tables</h3>";
$result = $conn->query("SHOW TABLES LIKE 'listening_exercises'");
if ($result->num_rows > 0) {
    echo "<p style='color: green;'>✅ listening_exercises table exists</p>";
    
    // Check content
    $count = $conn->query("SELECT COUNT(*) as count FROM listening_exercises")->fetch_assoc()['count'];
    echo "<p>📊 Table has $count exercises</p>";
    
    if ($count == 0) {
        echo "<p style='color: orange;'>⚠️ Table is empty, running SQL script...</p>";
        
        // Read and execute SQL file
        $sql = file_get_contents('create_listening_tables.sql');
        if ($sql) {
            $queries = explode(';', $sql);
            foreach ($queries as $query) {
                $query = trim($query);
                if (!empty($query) && !strpos($query, 'SELECT')) {
                    $conn->query($query);
                }
            }
            
            $newCount = $conn->query("SELECT COUNT(*) as count FROM listening_exercises")->fetch_assoc()['count'];
            echo "<p style='color: green;'>✅ Added sample data. Table now has $newCount exercises</p>";
        }
    }
} else {
    echo "<p style='color: red;'>❌ listening_exercises table does not exist, creating...</p>";
    
    // Read and execute SQL file
    $sql = file_get_contents('create_listening_tables.sql');
    if ($sql) {
        $queries = explode(';', $sql);
        foreach ($queries as $query) {
            $query = trim($query);
            if (!empty($query) && !strpos($query, 'SELECT')) {
                $result = $conn->query($query);
                if (!$result) {
                    echo "<p style='color: red;'>❌ Error: " . $conn->error . "</p>";
                }
            }
        }
        
        echo "<p style='color: green;'>✅ Tables created successfully</p>";
        $count = $conn->query("SELECT COUNT(*) as count FROM listening_exercises")->fetch_assoc()['count'];
        echo "<p>📊 Added $count sample exercises</p>";
    } else {
        echo "<p style='color: red;'>❌ Could not read SQL file</p>";
    }
}

// Test 2: Test controller response
echo "<h3>2. Testing Controller Response</h3>";
echo "<div id='test-result'></div>";

// Test 3: Show sample exercises
echo "<h3>3. Sample Exercises</h3>";
$exercises = $conn->query("SELECT title, question, audio_url FROM listening_exercises LIMIT 3");
if ($exercises && $exercises->num_rows > 0) {
    echo "<ul>";
    while ($row = $exercises->fetch_assoc()) {
        echo "<li><strong>{$row['title']}</strong><br>";
        echo "Question: {$row['question']}<br>";
        echo "Audio: {$row['audio_url']}</li><br>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: orange;'>⚠️ No exercises found</p>";
}

?>

<script>
// Test the actual listening endpoint
function testListening() {
    fetch('controllers/listening.php?action=get_exercise', {
        method: 'GET'
    })
    .then(response => response.json())
    .then(data => {
        const resultDiv = document.getElementById('test-result');
        if (data.success) {
            resultDiv.innerHTML = `
                <p style='color: green;'>✅ Controller response successful!</p>
                <p><strong>Exercise:</strong> ${data.data.title}</p>
                <p><strong>Question:</strong> ${data.data.question}</p>
                <p><strong>Audio URL:</strong> ${data.data.audio_url}</p>
                <p><strong>Options:</strong></p>
                <ul>
                    <li>A: ${data.data.option_a}</li>
                    <li>B: ${data.data.option_b}</li>
                    <li>C: ${data.data.option_c}</li>
                    <li>D: ${data.data.option_d}</li>
                </ul>
                <p><strong>Correct Answer:</strong> ${data.data.correct_answer}</p>
            `;
        } else {
            resultDiv.innerHTML = `<p style='color: red;'>❌ Controller error: ${data.message}</p>`;
        }
    })
    .catch(error => {
        document.getElementById('test-result').innerHTML = `<p style='color: red;'>❌ Network error: ${error.message}</p>`;
    });
}

// Run test when page loads
document.addEventListener('DOMContentLoaded', testListening);
</script>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    max-width: 800px;
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

p {
    line-height: 1.5;
}

li {
    margin-bottom: 0.5rem;
}
</style>

<?php $conn->close(); ?>
