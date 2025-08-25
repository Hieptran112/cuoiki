<?php
session_start();
require_once 'services/database.php';

echo "<h2>🧪 Test Flashcards Page API</h2>";

if (!isset($_SESSION['user_id'])) {
    echo "<p style='color: red;'>❌ Please login first</p>";
    echo "<p><a href='index.php'>Go to login</a></p>";
    exit;
}

$userId = $_SESSION['user_id'];
echo "<p><strong>Testing for user ID:</strong> $userId</p>";

// Test list_decks API
echo "<h3>1. Test list_decks API:</h3>";
echo "<button onclick='testListDecks()'>Test List Decks</button>";
echo "<div id='list-decks-result' style='margin-top: 1rem; padding: 1rem; background: #f5f5f5;'></div>";

// Test get_decks API (for comparison)
echo "<h3>2. Test get_decks API (for comparison):</h3>";
echo "<button onclick='testGetDecks()'>Test Get Decks</button>";
echo "<div id='get-decks-result' style='margin-top: 1rem; padding: 1rem; background: #f5f5f5;'></div>";

// Show current database state
echo "<h3>3. Current Database State:</h3>";
try {
    // Check flashcard_decks table
    $stmt = $conn->prepare("SELECT id, name, description, created_at FROM flashcard_decks WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $decks = [];
    while ($row = $result->fetch_assoc()) {
        $decks[] = $row;
    }
    
    echo "<p><strong>Flashcard_decks table:</strong> " . count($decks) . " decks found</p>";
    if (count($decks) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Description</th><th>Created At</th></tr>";
        foreach ($decks as $deck) {
            echo "<tr>";
            echo "<td>" . $deck['id'] . "</td>";
            echo "<td>" . htmlspecialchars($deck['name']) . "</td>";
            echo "<td>" . htmlspecialchars($deck['description'] ?? '') . "</td>";
            echo "<td>" . $deck['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠️ No decks found for user $userId</p>";
        echo "<p><a href='create_user_decks.php'>Create sample decks</a></p>";
    }
    
    // Check if old 'decks' table still exists
    $result = $conn->query("SHOW TABLES LIKE 'decks'");
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: red;'>❌ Old 'decks' table still exists - this may cause conflicts</p>";
        echo "<p><a href='simple_cleanup.php'>Run database cleanup</a></p>";
    } else {
        echo "<p style='color: green;'>✅ Old 'decks' table has been removed</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
}

$conn->close();
?>

<script>
function testListDecks() {
    const resultDiv = document.getElementById('list-decks-result');
    resultDiv.innerHTML = '<p>⏳ Testing list_decks API...</p>';
    
    fetch('controllers/flashcards.php?action=list_decks', {
        method: 'GET',
        credentials: 'same-origin'
    })
    .then(response => {
        console.log('list_decks response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('list_decks response data:', data);
        
        if (data.success) {
            resultDiv.innerHTML = `
                <h4 style="color: green;">✅ list_decks API Success!</h4>
                <p><strong>Found ${data.data.length} decks:</strong></p>
                <ul>
                    ${data.data.map(deck => `
                        <li>
                            <strong>${deck.name}</strong> 
                            ${deck.description ? '- ' + deck.description : ''}
                            <br><small>ID: ${deck.id}, Cards: ${deck.card_count || 0}, Created: ${deck.created_at}</small>
                        </li>
                    `).join('')}
                </ul>
            `;
        } else {
            resultDiv.innerHTML = `
                <h4 style="color: red;">❌ list_decks API Failed!</h4>
                <p><strong>Error:</strong> ${data.message}</p>
            `;
        }
    })
    .catch(error => {
        console.error('list_decks error:', error);
        resultDiv.innerHTML = `
            <h4 style="color: red;">❌ Network Error!</h4>
            <p><strong>Error:</strong> ${error.message}</p>
        `;
    });
}

function testGetDecks() {
    const resultDiv = document.getElementById('get-decks-result');
    resultDiv.innerHTML = '<p>⏳ Testing get_decks API...</p>';
    
    fetch('controllers/flashcards.php?action=get_decks', {
        method: 'GET',
        credentials: 'same-origin'
    })
    .then(response => {
        console.log('get_decks response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('get_decks response data:', data);
        
        if (data.success) {
            resultDiv.innerHTML = `
                <h4 style="color: green;">✅ get_decks API Success!</h4>
                <p><strong>Found ${data.data.length} decks:</strong></p>
                <ul>
                    ${data.data.map(deck => `
                        <li>
                            <strong>${deck.name}</strong> 
                            ${deck.description ? '- ' + deck.description : ''}
                            <br><small>ID: ${deck.id}</small>
                        </li>
                    `).join('')}
                </ul>
            `;
        } else {
            resultDiv.innerHTML = `
                <h4 style="color: red;">❌ get_decks API Failed!</h4>
                <p><strong>Error:</strong> ${data.message}</p>
            `;
        }
    })
    .catch(error => {
        console.error('get_decks error:', error);
        resultDiv.innerHTML = `
            <h4 style="color: red;">❌ Network Error!</h4>
            <p><strong>Error:</strong> ${error.message}</p>
        `;
    });
}
</script>

<style>
body { 
    font-family: Arial, sans-serif; 
    max-width: 1000px; 
    margin: 0 auto; 
    padding: 20px; 
    background: #f8f9fa;
}
button { 
    padding: 0.5rem 1rem; 
    background: #007bff; 
    color: white; 
    border: none; 
    border-radius: 4px; 
    cursor: pointer; 
    margin-right: 0.5rem;
}
button:hover { 
    background: #0056b3; 
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
ul {
    line-height: 1.6;
}
</style>
