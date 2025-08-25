<?php
require_once 'services/database.php';

echo "<h2>⚡ Quick Fix Foreign Key Constraint</h2>";

try {
    echo "<h3>Removing problematic foreign key constraints...</h3>";
    
    // Get all foreign key constraints on flashcards table
    $result = $conn->query("
        SELECT CONSTRAINT_NAME 
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'flashcards'
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    
    $removedCount = 0;
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $constraintName = $row['CONSTRAINT_NAME'];
            try {
                $conn->query("ALTER TABLE flashcards DROP FOREIGN KEY $constraintName");
                echo "<p style='color: green;'>✅ Removed constraint: $constraintName</p>";
                $removedCount++;
            } catch (Exception $e) {
                echo "<p style='color: orange;'>⚠️ Could not remove $constraintName: " . $e->getMessage() . "</p>";
            }
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ No foreign key constraints found on flashcards table</p>";
    }
    
    if ($removedCount > 0) {
        echo "<h3 style='color: green;'>🎉 Success!</h3>";
        echo "<p>Removed $removedCount foreign key constraints. You can now add words to decks.</p>";
    } else {
        echo "<h3 style='color: orange;'>⚠️ No Changes Made</h3>";
        echo "<p>No problematic constraints were found or removed.</p>";
    }
    
    // Test the fix
    echo "<h3>Test the fix:</h3>";
    echo "<button onclick='testAddWord()'>Test Add Word</button>";
    echo "<div id='test-result' style='margin-top: 1rem; padding: 1rem; background: #f5f5f5;'></div>";
    
    echo "<h3>Next Steps:</h3>";
    echo "<ol>";
    echo "<li><strong>Test adding a word:</strong> Click the test button above</li>";
    echo "<li><strong>Go back to homepage:</strong> <a href='index.php'>index.php</a></li>";
    echo "<li><strong>Try the dictionary feature:</strong> Search a word and click 'Thêm vào'</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

$conn->close();
?>

<script>
function testAddWord() {
    const resultDiv = document.getElementById('test-result');
    resultDiv.innerHTML = '<p>⏳ Testing add word to deck...</p>';
    
    // First get decks
    fetch('controllers/flashcards.php?action=get_decks', {
        method: 'GET',
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        console.log('Get decks response:', data);
        
        if (data.success && data.data.length > 0) {
            const firstDeckId = data.data[0].id;
            const deckName = data.data[0].name;
            
            resultDiv.innerHTML = `<p>Found ${data.data.length} decks. Testing with deck: ${deckName} (ID: ${firstDeckId})</p>`;
            
            // Now test adding a word
            return fetch('controllers/flashcards.php?action=add_word_to_deck', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({
                    deck_id: firstDeckId,
                    word: 'test_' + Date.now(),
                    definition: 'test definition',
                    example: 'This is a test example'
                })
            });
        } else {
            throw new Error('No decks available for testing. Error: ' + (data.message || 'Unknown'));
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Add word response:', data);
        
        if (data.success) {
            resultDiv.innerHTML = `
                <h4 style="color: green;">✅ SUCCESS!</h4>
                <p><strong>Message:</strong> ${data.message}</p>
                <p>The foreign key constraint issue has been fixed. You can now add words to decks!</p>
                <p><a href="index.php" style="background: #28a745; color: white; padding: 0.5rem 1rem; text-decoration: none; border-radius: 4px;">Go to Homepage</a></p>
            `;
        } else {
            resultDiv.innerHTML = `
                <h4 style="color: red;">❌ Still Failed</h4>
                <p><strong>Error:</strong> ${data.message}</p>
                <p>There might be other issues. Please check the database structure.</p>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        resultDiv.innerHTML = `
            <h4 style="color: red;">❌ Test Error</h4>
            <p><strong>Error:</strong> ${error.message}</p>
            <p>Please check the console for more details.</p>
        `;
    });
}
</script>

<style>
body { 
    font-family: Arial, sans-serif; 
    max-width: 800px; 
    margin: 0 auto; 
    padding: 20px; 
    background: #f8f9fa;
}
button { 
    padding: 0.5rem 1rem; 
    color: white; 
    border: none; 
    border-radius: 4px; 
    cursor: pointer; 
    background: #007bff;
    font-size: 1rem;
}
button:hover { 
    background: #0056b3; 
}
h2, h3 { 
    color: #333; 
}
a {
    color: #007bff;
    text-decoration: none;
}
a:hover {
    text-decoration: underline;
}
ol {
    line-height: 1.6;
}
</style>
