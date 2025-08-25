<?php
session_start();
require_once 'services/database.php';

echo "<h2>🎯 Create Decks for Current User</h2>";

if (!isset($_SESSION['user_id'])) {
    echo "<p style='color: red;'>❌ Please login first</p>";
    exit;
}

$userId = $_SESSION['user_id'];
echo "<p><strong>Creating decks for user ID:</strong> $userId</p>";

// Sample decks to create
$sampleDecks = [
    [
        'name' => 'Từ vựng cơ bản',
        'description' => 'Các từ vựng tiếng Anh cơ bản hàng ngày'
    ],
    [
        'name' => 'Động từ thường dùng', 
        'description' => 'Các động từ tiếng Anh thường gặp'
    ],
    [
        'name' => 'Tính từ mô tả',
        'description' => 'Các tính từ mô tả tính cách và đặc điểm'
    ],
    [
        'name' => 'Từ vựng công việc',
        'description' => 'Từ vựng liên quan đến công việc và nghề nghiệp'
    ],
    [
        'name' => 'Từ vựng gia đình',
        'description' => 'Từ vựng về gia đình và mối quan hệ'
    ]
];

echo "<h3>Creating sample decks...</h3>";

$createdCount = 0;
foreach ($sampleDecks as $deck) {
    try {
        // Check if deck already exists
        $checkStmt = $conn->prepare("SELECT id FROM flashcard_decks WHERE user_id = ? AND name = ?");
        $checkStmt->bind_param("is", $userId, $deck['name']);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows > 0) {
            echo "<p style='color: blue;'>ℹ️ Deck '" . htmlspecialchars($deck['name']) . "' already exists</p>";
            continue;
        }
        
        // Create new deck
        $stmt = $conn->prepare("INSERT INTO flashcard_decks (user_id, name, description) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $userId, $deck['name'], $deck['description']);
        
        if ($stmt->execute()) {
            $deckId = $conn->insert_id;
            echo "<p style='color: green;'>✅ Created deck: " . htmlspecialchars($deck['name']) . " (ID: $deckId)</p>";
            $createdCount++;
            
            // Add some sample flashcards
            addSampleCards($conn, $deckId, $deck['name']);
        } else {
            echo "<p style='color: red;'>❌ Failed to create deck: " . htmlspecialchars($deck['name']) . "</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error creating deck '" . htmlspecialchars($deck['name']) . "': " . $e->getMessage() . "</p>";
    }
}

echo "<h3>Summary:</h3>";
echo "<p><strong>Created $createdCount new decks</strong></p>";

// Check final count
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM flashcard_decks WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$totalDecks = $row['count'];

echo "<p><strong>Total decks for user $userId: $totalDecks</strong></p>";

if ($totalDecks > 0) {
    echo "<p style='color: green;'>🎉 Success! Now you can use the 'Add to deck' feature.</p>";
    echo "<p><a href='index.php'>Go back to homepage</a> and try adding words to your decks!</p>";
} else {
    echo "<p style='color: red;'>❌ Still no decks. Please check the error messages above.</p>";
}

// Test the API
echo "<h3>Test API:</h3>";
echo "<button onclick='testAPI()'>Test get_decks API</button>";
echo "<div id='api-result' style='margin-top: 1rem; padding: 1rem; background: #f5f5f5;'></div>";

$conn->close();

function addSampleCards($conn, $deckId, $deckName) {
    $sampleCards = [];
    
    switch ($deckName) {
        case 'Từ vựng cơ bản':
            $sampleCards = [
                ['hello', 'xin chào', 'Hello, how are you?'],
                ['thank you', 'cảm ơn', 'Thank you for your help.'],
                ['good morning', 'chào buổi sáng', 'Good morning, everyone!']
            ];
            break;
        case 'Động từ thường dùng':
            $sampleCards = [
                ['go', 'đi', 'I go to school every day.'],
                ['come', 'đến', 'Please come here.'],
                ['eat', 'ăn', 'I eat breakfast at 7 AM.']
            ];
            break;
        case 'Tính từ mô tả':
            $sampleCards = [
                ['beautiful', 'đẹp', 'She is very beautiful.'],
                ['smart', 'thông minh', 'He is a smart student.'],
                ['kind', 'tốt bụng', 'She is very kind to everyone.']
            ];
            break;
    }
    
    foreach ($sampleCards as $card) {
        try {
            $stmt = $conn->prepare("INSERT INTO flashcards (deck_id, front, back, example) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $deckId, $card[0], $card[1], $card[2]);
            $stmt->execute();
        } catch (Exception $e) {
            // Ignore duplicate errors
        }
    }
}
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
    background: #28a745; 
    color: white; 
    border: none; 
    border-radius: 4px; 
    cursor: pointer; 
}
button:hover { 
    background: #218838; 
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
</style>
