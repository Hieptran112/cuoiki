<?php
session_start();
require_once 'services/database.php';

echo "<h2>🔍 Check Database Structure</h2>";

if (!isset($_SESSION['user_id'])) {
    echo "<p style='color: red;'>❌ Please login first</p>";
    exit;
}

$userId = $_SESSION['user_id'];
echo "<p><strong>Current user ID:</strong> $userId</p>";

// Check flashcards table structure
echo "<h3>1. Flashcards Table Structure:</h3>";
try {
    $result = $conn->query("DESCRIBE flashcards");
    if ($result) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Check if 'front' column exists
        $frontExists = false;
        $result = $conn->query("DESCRIBE flashcards");
        while ($row = $result->fetch_assoc()) {
            if ($row['Field'] === 'front') {
                $frontExists = true;
                break;
            }
        }
        
        if ($frontExists) {
            echo "<p style='color: green;'>✅ Column 'front' exists</p>";
        } else {
            echo "<p style='color: red;'>❌ Column 'front' does NOT exist</p>";
            echo "<p><strong>Need to add 'front' column!</strong></p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Cannot describe flashcards table</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

// Check flashcard_decks table and show all decks
echo "<h3>2. All Flashcard Decks in System:</h3>";
try {
    $result = $conn->query("SELECT id, user_id, name, description, created_at FROM flashcard_decks ORDER BY created_at DESC");
    if ($result && $result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>User ID</th><th>Name</th><th>Description</th><th>Created At</th></tr>";
        while ($row = $result->fetch_assoc()) {
            $highlight = ($row['user_id'] == $userId) ? "style='background: #ffffcc;'" : "";
            echo "<tr $highlight>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['user_id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['description'] ?? '') . "</td>";
            echo "<td>" . $row['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<p><em>Yellow rows belong to current user (ID: $userId)</em></p>";
    } else {
        echo "<p style='color: orange;'>⚠️ No decks found</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

// Check current user's decks specifically
echo "<h3>3. Current User's Decks (ID: $userId):</h3>";
try {
    $stmt = $conn->prepare("SELECT id, name, description FROM flashcard_decks WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $userDecks = [];
    while ($row = $result->fetch_assoc()) {
        $userDecks[] = $row;
    }
    
    echo "<p><strong>Found " . count($userDecks) . " decks for user $userId:</strong></p>";
    if (count($userDecks) > 0) {
        echo "<ul>";
        foreach ($userDecks as $deck) {
            echo "<li><strong>" . htmlspecialchars($deck['name']) . "</strong>";
            if ($deck['description']) {
                echo " - " . htmlspecialchars($deck['description']);
            }
            echo " (ID: " . $deck['id'] . ")</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: orange;'>⚠️ No decks found for current user</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

// Test get_decks API
echo "<h3>4. Test get_decks API:</h3>";
echo "<button onclick='testGetDecks()'>Test API</button>";
echo "<div id='api-result' style='margin-top: 1rem; padding: 1rem; background: #f5f5f5;'></div>";

// Fix buttons
echo "<h3>5. Fix Options:</h3>";
echo "<div style='margin: 1rem 0;'>";
echo "<button onclick='fixFrontColumn()' style='background: #dc3545; margin-right: 0.5rem;'>Fix 'front' Column</button>";
echo "<button onclick='syncDecks()' style='background: #28a745; margin-right: 0.5rem;'>Sync Decks</button>";
echo "<a href='transfer_all_decks.php'><button style='background: #ffc107; color: black;'>Transfer All Decks</button></a>";
echo "</div>";

$conn->close();
?>

<script>
function testGetDecks() {
    const resultDiv = document.getElementById('api-result');
    resultDiv.innerHTML = '<p>⏳ Testing get_decks API...</p>';
    
    fetch('controllers/flashcards.php?action=get_decks', {
        method: 'GET',
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        console.log('API Response:', data);
        
        if (data.success) {
            resultDiv.innerHTML = `
                <h4 style="color: green;">✅ API Success!</h4>
                <p><strong>Debug User ID:</strong> ${data.debug_user_id}</p>
                <p><strong>Found ${data.data.length} decks:</strong></p>
                <ul>${data.data.map(deck => `<li>${deck.name} (ID: ${deck.id})</li>`).join('')}</ul>
            `;
        } else {
            resultDiv.innerHTML = `
                <h4 style="color: red;">❌ API Failed!</h4>
                <p>Error: ${data.message}</p>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        resultDiv.innerHTML = `<h4 style="color: red;">❌ Network Error!</h4><p>${error.message}</p>`;
    });
}

function fixFrontColumn() {
    if (!confirm('Add missing "front" column to flashcards table?')) return;
    
    fetch('fix_flashcards_table.php', {
        method: 'POST',
        credentials: 'same-origin'
    })
    .then(response => response.text())
    .then(data => {
        alert('Result: ' + data);
        location.reload();
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}

function syncDecks() {
    if (!confirm('Sync decks between different sources?')) return;
    
    fetch('sync_decks.php', {
        method: 'POST',
        credentials: 'same-origin'
    })
    .then(response => response.text())
    .then(data => {
        alert('Result: ' + data);
        location.reload();
    })
    .catch(error => {
        alert('Error: ' + error.message);
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
h2, h3 { 
    color: #333; 
}
ul {
    line-height: 1.6;
}
</style>
