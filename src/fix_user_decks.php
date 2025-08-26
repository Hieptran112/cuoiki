<?php
session_start();
require_once 'services/database.php';

echo "<h2>🔧 Fix User Decks Issue</h2>";

if (!isset($_SESSION['user_id'])) {
    echo "<p style='color: red;'>❌ Please login first</p>";
    exit;
}

$currentUserId = $_SESSION['user_id'];
echo "<p><strong>Current session user_id:</strong> $currentUserId</p>";

// Check current user info
$stmt = $conn->prepare("SELECT id, username, email FROM users WHERE id = ?");
$stmt->bind_param("i", $currentUserId);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    echo "<p><strong>User info:</strong> " . htmlspecialchars($user['username']) . " (" . htmlspecialchars($user['email']) . ")</p>";
} else {
    echo "<p style='color: red;'>❌ User not found in database!</p>";
}

// Check decks for current user
echo "<h3>Decks for current user:</h3>";
$stmt = $conn->prepare("SELECT id, name, description FROM flashcard_decks WHERE user_id = ?");
$stmt->bind_param("i", $currentUserId);
$stmt->execute();
$result = $stmt->get_result();

$userDecks = [];
while ($row = $result->fetch_assoc()) {
    $userDecks[] = $row;
}

echo "<p>Found " . count($userDecks) . " decks for user $currentUserId</p>";

if (count($userDecks) > 0) {
    foreach ($userDecks as $deck) {
        echo "<p>• " . htmlspecialchars($deck['name']) . " (ID: " . $deck['id'] . ")</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ No decks found for current user</p>";
    
    // Check all decks in system
    echo "<h3>All decks in system:</h3>";
    $result = $conn->query("SELECT id, user_id, name FROM flashcard_decks ORDER BY created_at DESC");
    
    if ($result && $result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Deck ID</th><th>User ID</th><th>Name</th><th>Action</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['user_id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
            echo "<td>";
            if ($row['user_id'] != $currentUserId) {
                echo "<button onclick='transferDeck(" . $row['id'] . ")'>Transfer to me</button>";
            } else {
                echo "Already yours";
            }
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<p style='color: blue;'>ℹ️ If you see decks that should belong to you, click 'Transfer to me'</p>";
    } else {
        echo "<p>No decks found in system at all.</p>";
        echo "<p><a href='flashcards.php'>Create your first deck</a></p>";
    }
}

// Test API call
echo "<h3>Test API:</h3>";
echo "<button onclick='testGetDecks()'>Test get_decks API</button>";
echo "<div id='api-result' style='margin-top: 1rem; padding: 1rem; background: #f5f5f5;'></div>";

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

function transferDeck(deckId) {
    if (!confirm('Transfer this deck to your account?')) return;
    
    fetch('controllers/flashcards.php?action=transfer_deck', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({ deck_id: deckId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Deck transferred successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Network error: ' + error.message);
    });
}
</script>

<style>
body { font-family: Arial, sans-serif; max-width: 1000px; margin: 0 auto; padding: 20px; }
table { margin: 1rem 0; }
th, td { padding: 0.5rem; text-align: left; }
th { background: #f0f0f0; }
button { padding: 0.3rem 0.6rem; background: #007bff; color: white; border: none; border-radius: 3px; cursor: pointer; }
button:hover { background: #0056b3; }
</style>
