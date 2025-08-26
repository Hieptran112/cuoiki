<?php
session_start();
require_once 'services/database.php';

echo "<h2>🔄 Transfer All Decks to Current User</h2>";

if (!isset($_SESSION['user_id'])) {
    echo "<p style='color: red;'>❌ Please login first</p>";
    exit;
}

$userId = $_SESSION['user_id'];
echo "<p><strong>Current user ID:</strong> $userId</p>";

// Check current decks
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM flashcard_decks WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$currentDecks = $result->fetch_assoc()['count'];

echo "<p><strong>Current decks for user $userId:</strong> $currentDecks</p>";

// Check all decks in system
$result = $conn->query("SELECT COUNT(*) as total FROM flashcard_decks");
$totalDecks = $result->fetch_assoc()['total'];

echo "<p><strong>Total decks in system:</strong> $totalDecks</p>";

if ($currentDecks == $totalDecks) {
    echo "<p style='color: green;'>✅ All decks already belong to you!</p>";
} else {
    echo "<h3>Transferring all decks to your account...</h3>";
    
    if (isset($_POST['confirm_transfer'])) {
        try {
            // Transfer all decks to current user
            $stmt = $conn->prepare("UPDATE flashcard_decks SET user_id = ?");
            $stmt->bind_param("i", $userId);
            
            if ($stmt->execute()) {
                $affectedRows = $stmt->affected_rows;
                echo "<p style='color: green;'>✅ Successfully transferred $affectedRows decks to your account!</p>";
                
                // Verify
                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM flashcard_decks WHERE user_id = ?");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                $newCount = $result->fetch_assoc()['count'];
                
                echo "<p><strong>Your decks now:</strong> $newCount</p>";
                
                if ($newCount > 0) {
                    echo "<p style='color: green;'>🎉 Success! Now you can use the 'Add to deck' feature.</p>";
                    echo "<p><a href='index.php'>Go back to homepage</a> and try adding words to your decks!</p>";
                }
            } else {
                echo "<p style='color: red;'>❌ Failed to transfer decks</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
        }
    } else {
        // Show confirmation form
        echo "<div style='background: #fff3cd; padding: 1rem; border-radius: 4px; margin: 1rem 0;'>";
        echo "<p><strong>⚠️ Warning:</strong> This will transfer ALL decks in the system to your account.</p>";
        echo "<p>This includes:</p>";
        
        // Show what decks will be transferred
        $result = $conn->query("SELECT id, user_id, name FROM flashcard_decks ORDER BY created_at DESC");
        if ($result && $result->num_rows > 0) {
            echo "<ul>";
            while ($row = $result->fetch_assoc()) {
                $highlight = ($row['user_id'] == $userId) ? " (already yours)" : " (will be transferred)";
                echo "<li>" . htmlspecialchars($row['name']) . " (ID: " . $row['id'] . ", User: " . $row['user_id'] . ")" . $highlight . "</li>";
            }
            echo "</ul>";
        }
        
        echo "<form method='POST'>";
        echo "<button type='submit' name='confirm_transfer' value='1' style='background: #dc3545; color: white; padding: 0.5rem 1rem; border: none; border-radius: 4px; cursor: pointer;'>Confirm Transfer All Decks</button>";
        echo " <a href='create_user_decks.php' style='margin-left: 1rem;'>Or create new decks instead</a>";
        echo "</form>";
        echo "</div>";
    }
}

// Test API button
echo "<h3>Test API:</h3>";
echo "<button onclick='testAPI()'>Test get_decks API</button>";
echo "<div id='api-result' style='margin-top: 1rem; padding: 1rem; background: #f5f5f5;'></div>";

$conn->close();
?>

<script>
function testAPI() {
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
    background: #007bff; 
    color: white; 
    border: none; 
    border-radius: 4px; 
    cursor: pointer; 
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
ul {
    line-height: 1.6;
}
</style>
