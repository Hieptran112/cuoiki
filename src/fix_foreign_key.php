<?php
require_once 'services/database.php';

echo "<h2>🔧 Fix Foreign Key Constraint</h2>";

try {
    // Check current foreign key constraints
    echo "<h3>1. Current Foreign Key Constraints:</h3>";
    $result = $conn->query("
        SELECT 
            CONSTRAINT_NAME,
            TABLE_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND REFERENCED_TABLE_NAME IS NOT NULL
        ORDER BY TABLE_NAME
    ");
    
    if ($result && $result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Constraint</th><th>Table</th><th>Column</th><th>References Table</th><th>References Column</th></tr>";
        while ($row = $result->fetch_assoc()) {
            $highlight = ($row['REFERENCED_TABLE_NAME'] === 'decks') ? "style='background: #ffcccc;'" : "";
            echo "<tr $highlight>";
            echo "<td>" . $row['CONSTRAINT_NAME'] . "</td>";
            echo "<td>" . $row['TABLE_NAME'] . "</td>";
            echo "<td>" . $row['COLUMN_NAME'] . "</td>";
            echo "<td>" . $row['REFERENCED_TABLE_NAME'] . "</td>";
            echo "<td>" . $row['REFERENCED_COLUMN_NAME'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<p><em>Red rows show problematic constraints referencing 'decks' table</em></p>";
    } else {
        echo "<p>No foreign key constraints found</p>";
    }
    
    // Check what tables exist
    echo "<h3>2. Available Tables:</h3>";
    $result = $conn->query("SHOW TABLES");
    $tables = [];
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }
    echo "<p><strong>Tables in database:</strong> " . implode(", ", $tables) . "</p>";
    
    $hasDecks = in_array('decks', $tables);
    $hasFlashcardDecks = in_array('flashcard_decks', $tables);
    
    echo "<p>• Table 'decks': " . ($hasDecks ? "✅ EXISTS" : "❌ NOT EXISTS") . "</p>";
    echo "<p>• Table 'flashcard_decks': " . ($hasFlashcardDecks ? "✅ EXISTS" : "❌ NOT EXISTS") . "</p>";
    
    // Show fix options
    echo "<h3>3. Fix Options:</h3>";
    
    if (isset($_POST['fix_option'])) {
        $option = $_POST['fix_option'];
        
        switch ($option) {
            case 'drop_constraint':
                echo "<h4>Dropping problematic foreign key constraint...</h4>";
                try {
                    // Find and drop the constraint
                    $result = $conn->query("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_SCHEMA = DATABASE() 
                        AND TABLE_NAME = 'flashcards'
                        AND REFERENCED_TABLE_NAME = 'decks'
                    ");
                    
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $constraintName = $row['CONSTRAINT_NAME'];
                            $conn->query("ALTER TABLE flashcards DROP FOREIGN KEY $constraintName");
                            echo "<p style='color: green;'>✅ Dropped constraint: $constraintName</p>";
                        }
                    } else {
                        echo "<p style='color: orange;'>⚠️ No problematic constraints found</p>";
                    }
                } catch (Exception $e) {
                    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
                }
                break;
                
            case 'create_correct_constraint':
                echo "<h4>Creating correct foreign key constraint...</h4>";
                try {
                    // First drop any existing constraints
                    $result = $conn->query("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_SCHEMA = DATABASE() 
                        AND TABLE_NAME = 'flashcards'
                        AND COLUMN_NAME = 'deck_id'
                        AND REFERENCED_TABLE_NAME IS NOT NULL
                    ");
                    
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $constraintName = $row['CONSTRAINT_NAME'];
                            $conn->query("ALTER TABLE flashcards DROP FOREIGN KEY $constraintName");
                            echo "<p style='color: blue;'>ℹ️ Dropped existing constraint: $constraintName</p>";
                        }
                    }
                    
                    // Create correct constraint
                    $conn->query("
                        ALTER TABLE flashcards 
                        ADD CONSTRAINT fk_flashcards_flashcard_decks 
                        FOREIGN KEY (deck_id) REFERENCES flashcard_decks(id) ON DELETE CASCADE
                    ");
                    echo "<p style='color: green;'>✅ Created correct foreign key constraint</p>";
                    
                } catch (Exception $e) {
                    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
                }
                break;
                
            case 'remove_all_constraints':
                echo "<h4>Removing all foreign key constraints from flashcards table...</h4>";
                try {
                    $result = $conn->query("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_SCHEMA = DATABASE() 
                        AND TABLE_NAME = 'flashcards'
                        AND REFERENCED_TABLE_NAME IS NOT NULL
                    ");
                    
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $constraintName = $row['CONSTRAINT_NAME'];
                            $conn->query("ALTER TABLE flashcards DROP FOREIGN KEY $constraintName");
                            echo "<p style='color: green;'>✅ Dropped constraint: $constraintName</p>";
                        }
                        echo "<p style='color: green;'>🎉 All foreign key constraints removed. You can now add words to decks.</p>";
                    } else {
                        echo "<p style='color: orange;'>⚠️ No foreign key constraints found</p>";
                    }
                } catch (Exception $e) {
                    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
                }
                break;
        }
        
        // Refresh the page to show updated constraints
        echo "<p><a href='fix_foreign_key.php'>Refresh to see updated constraints</a></p>";
        
    } else {
        // Show fix options
        echo "<form method='POST'>";
        echo "<div style='margin: 1rem 0;'>";
        echo "<h4>Choose a fix option:</h4>";
        
        echo "<label style='display: block; margin: 0.5rem 0;'>";
        echo "<input type='radio' name='fix_option' value='remove_all_constraints' checked> ";
        echo "<strong>Remove all foreign key constraints (Recommended)</strong>";
        echo "<br><small>This will allow adding words without constraint issues</small>";
        echo "</label>";
        
        if ($hasFlashcardDecks) {
            echo "<label style='display: block; margin: 0.5rem 0;'>";
            echo "<input type='radio' name='fix_option' value='create_correct_constraint'> ";
            echo "<strong>Fix constraint to reference 'flashcard_decks'</strong>";
            echo "<br><small>This will create proper foreign key relationship</small>";
            echo "</label>";
        }
        
        echo "<label style='display: block; margin: 0.5rem 0;'>";
        echo "<input type='radio' name='fix_option' value='drop_constraint'> ";
        echo "<strong>Only drop problematic constraints</strong>";
        echo "<br><small>Remove constraints that reference non-existent 'decks' table</small>";
        echo "</label>";
        
        echo "</div>";
        echo "<button type='submit' style='background: #dc3545; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem;'>Apply Fix</button>";
        echo "</form>";
    }
    
    // Test section
    echo "<h3>4. Test Add Word:</h3>";
    echo "<button onclick='testAddWord()'>Test Add Word to Deck</button>";
    echo "<div id='test-result' style='margin-top: 1rem; padding: 1rem; background: #f5f5f5;'></div>";
    
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
        if (data.success && data.data.length > 0) {
            const firstDeckId = data.data[0].id;
            
            // Now test adding a word
            return fetch('controllers/flashcards.php?action=add_word_to_deck', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({
                    deck_id: firstDeckId,
                    word: 'test',
                    definition: 'test definition',
                    example: 'test example'
                })
            });
        } else {
            throw new Error('No decks available for testing');
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Add word response:', data);
        
        if (data.success) {
            resultDiv.innerHTML = `
                <h4 style="color: green;">✅ Add Word Success!</h4>
                <p>Message: ${data.message}</p>
            `;
        } else {
            resultDiv.innerHTML = `
                <h4 style="color: red;">❌ Add Word Failed!</h4>
                <p>Error: ${data.message}</p>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        resultDiv.innerHTML = `
            <h4 style="color: red;">❌ Test Error!</h4>
            <p>Error: ${error.message}</p>
        `;
    });
}
</script>

<style>
body { 
    font-family: Arial, sans-serif; 
    max-width: 1200px; 
    margin: 0 auto; 
    padding: 20px; 
    background: #f8f9fa;
}
table { 
    margin: 1rem 0; 
    font-size: 0.9rem;
    width: 100%;
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
button { 
    padding: 0.5rem 1rem; 
    color: white; 
    border: none; 
    border-radius: 4px; 
    cursor: pointer; 
    background: #007bff;
}
button:hover { 
    opacity: 0.8; 
}
h2, h3, h4 { 
    color: #333; 
}
label {
    cursor: pointer;
}
input[type="radio"] {
    margin-right: 0.5rem;
}
</style>
