<?php
session_start();
require_once 'services/database.php';

echo "<h2>🔍 Quick Debug - Add to Deck Issue</h2>";

// Check session
echo "<h3>Session Info:</h3>";
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    echo "<p style='color: green;'>✅ User ID: $userId</p>";
} else {
    echo "<p style='color: red;'>❌ No user_id in session</p>";
    echo "<pre>" . print_r($_SESSION, true) . "</pre>";
    exit;
}

// Check user's decks
echo "<h3>User's Decks:</h3>";
$stmt = $conn->prepare("SELECT id, name, description FROM flashcard_decks WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$decks = [];
while ($row = $result->fetch_assoc()) {
    $decks[] = $row;
}

echo "<p><strong>Found " . count($decks) . " decks:</strong></p>";
if (count($decks) > 0) {
    foreach ($decks as $deck) {
        echo "<p>• " . htmlspecialchars($deck['name']) . " (ID: " . $deck['id'] . ")</p>";
    }
} else {
    echo "<p style='color: red;'>❌ No decks found for user $userId</p>";
}

// Test API call
echo "<h3>Test API Call:</h3>";
echo "<button onclick='testAPI()'>Test get_decks API</button>";
echo "<div id='result' style='margin-top: 1rem; padding: 1rem; background: #f5f5f5;'></div>";

$conn->close();
?>

<script>
function testAPI() {
    const resultDiv = document.getElementById('result');
    resultDiv.innerHTML = '<p>⏳ Testing API...</p>';
    
    fetch('controllers/flashcards.php?action=get_decks', {
        method: 'GET',
        credentials: 'same-origin'
    })
    .then(response => {
        console.log('Status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response:', data);
        
        if (data.success) {
            resultDiv.innerHTML = `
                <h4 style="color: green;">✅ API Success!</h4>
                <p>Found ${data.data.length} decks:</p>
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
</script>

<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
button { padding: 0.5rem 1rem; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
</style>
