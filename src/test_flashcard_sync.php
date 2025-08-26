<?php
session_start();
require_once 'services/database.php';

header('Content-Type: text/html; charset=utf-8');

// Simple test to verify flashcard and stats synchronization
echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Flashcard Sync Test</title></head><body>";
echo "<h1>🧪 Flashcard & Statistics Synchronization Test</h1>";

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo "<p style='color: red;'>❌ Please login first to test flashcard functionality.</p>";
        echo "<p><a href='index.php'>Go to login page</a></p>";
        echo "</body></html>";
        exit;
    }
    
    $userId = $_SESSION['user_id'];
    echo "<p>✅ User logged in: ID = $userId</p>";
    
    // Test 1: Check table existence
    echo "<h2>📊 Database Table Check</h2>";
    
    $tables = ['decks', 'flashcards', 'study_progress'];
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            echo "<p style='color: green;'>✅ Table '$table' exists</p>";
        } else {
            echo "<p style='color: red;'>❌ Table '$table' missing</p>";
        }
    }
    
    // Test 2: Check user's decks
    echo "<h2>📚 User's Decks</h2>";
    $stmt = $conn->prepare("SELECT id, name, description FROM decks WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $decks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    if (empty($decks)) {
        echo "<p style='color: orange;'>⚠️ No decks found for user. Creating test deck...</p>";
        
        // Create a test deck
        $stmt = $conn->prepare("INSERT INTO decks (user_id, name, description, visibility) VALUES (?, ?, ?, ?)");
        $testName = "Test Deck";
        $testDesc = "Test deck for synchronization";
        $visibility = "private";
        $stmt->bind_param("isss", $userId, $testName, $testDesc, $visibility);
        
        if ($stmt->execute()) {
            $deckId = $conn->insert_id;
            echo "<p style='color: green;'>✅ Created test deck with ID: $deckId</p>";
            
            // Add test flashcards
            $testCards = [
                ['hello', 'xin chào', 'Hello, how are you?'],
                ['world', 'thế giới', 'The world is beautiful.'],
                ['test', 'kiểm tra', 'This is a test card.']
            ];
            
            $cardStmt = $conn->prepare("INSERT INTO flashcards (deck_id, word, definition, example) VALUES (?, ?, ?, ?)");
            foreach ($testCards as $card) {
                $cardStmt->bind_param("isss", $deckId, $card[0], $card[1], $card[2]);
                $cardStmt->execute();
            }
            echo "<p style='color: green;'>✅ Added " . count($testCards) . " test flashcards</p>";
            
            $decks = [['id' => $deckId, 'name' => $testName, 'description' => $testDesc]];
        } else {
            echo "<p style='color: red;'>❌ Failed to create test deck</p>";
        }
    } else {
        echo "<p style='color: green;'>✅ Found " . count($decks) . " deck(s):</p>";
        foreach ($decks as $deck) {
            echo "<li>ID: {$deck['id']}, Name: {$deck['name']}</li>";
        }
    }
    
    // Test 3: Check flashcards in decks
    echo "<h2>🃏 Flashcards Count</h2>";
    foreach ($decks as $deck) {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM flashcards WHERE deck_id = ?");
        $stmt->bind_param("i", $deck['id']);
        $stmt->execute();
        $count = $stmt->get_result()->fetch_assoc()['count'];
        echo "<p>Deck '{$deck['name']}': $count flashcards</p>";
    }
    
    // Test 4: Check study progress
    echo "<h2>📈 Study Progress</h2>";
    $stmt = $conn->prepare("
        SELECT 
            COUNT(DISTINCT sp.flashcard_id) as studied_cards,
            SUM(CASE WHEN sp.status = 'mastered' THEN 1 ELSE 0 END) as mastered_cards,
            SUM(sp.correct_count) as total_correct,
            SUM(sp.review_count) as total_reviews
        FROM study_progress sp 
        JOIN flashcards f ON sp.flashcard_id = f.id 
        JOIN decks d ON f.deck_id = d.id 
        WHERE sp.user_id = ?
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $progress = $stmt->get_result()->fetch_assoc();
    
    echo "<p>Studied cards: " . ($progress['studied_cards'] ?? 0) . "</p>";
    echo "<p>Mastered cards: " . ($progress['mastered_cards'] ?? 0) . "</p>";
    echo "<p>Total correct answers: " . ($progress['total_correct'] ?? 0) . "</p>";
    echo "<p>Total reviews: " . ($progress['total_reviews'] ?? 0) . "</p>";
    
    // Test 5: Test API endpoints
    echo "<h2>🔌 API Endpoint Tests</h2>";
    
    echo "<div id='api-tests'>";
    echo "<button onclick='testListDecks()'>Test List Decks API</button>";
    echo "<button onclick='testFlashcardStats()'>Test Flashcard Stats API</button>";
    echo "<div id='api-results' style='margin-top: 1rem; padding: 1rem; background: #f5f5f5; border-radius: 5px;'>";
    echo "Click buttons above to test API endpoints...";
    echo "</div>";
    echo "</div>";
    
    echo "<h2>🎯 Next Steps</h2>";
    echo "<p>1. Go to <a href='flashcards.php' target='_blank'>Flashcards page</a> to test the interface</p>";
    echo "<p>2. Select a deck and try studying some cards</p>";
    echo "<p>3. Go to <a href='stats.php' target='_blank'>Statistics page</a> to verify data synchronization</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<script>
function testListDecks() {
    const results = document.getElementById('api-results');
    results.innerHTML = '⏳ Testing list decks API...';
    
    fetch('controllers/flashcards.php?action=list_decks', {
        credentials: 'same-origin'
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            results.innerHTML = `
                <h4 style='color: green;'>✅ List Decks API Success</h4>
                <p>Found \${data.data.length} deck(s)</p>
                <pre>\${JSON.stringify(data, null, 2)}</pre>
            `;
        } else {
            results.innerHTML = `
                <h4 style='color: red;'>❌ List Decks API Failed</h4>
                <p>Error: \${data.message}</p>
            `;
        }
    })
    .catch(err => {
        results.innerHTML = `
            <h4 style='color: red;'>❌ List Decks API Error</h4>
            <p>Error: \${err.message}</p>
        `;
    });
}

function testFlashcardStats() {
    const results = document.getElementById('api-results');
    results.innerHTML = '⏳ Testing flashcard stats API...';
    
    fetch('controllers/stats.php?action=get_flashcard_stats', {
        credentials: 'same-origin'
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            results.innerHTML = `
                <h4 style='color: green;'>✅ Flashcard Stats API Success</h4>
                <pre>\${JSON.stringify(data, null, 2)}</pre>
            `;
        } else {
            results.innerHTML = `
                <h4 style='color: red;'>❌ Flashcard Stats API Failed</h4>
                <p>Error: \${data.message}</p>
            `;
        }
    })
    .catch(err => {
        results.innerHTML = `
            <h4 style='color: red;'>❌ Flashcard Stats API Error</h4>
            <p>Error: \${err.message}</p>
        `;
    });
}
</script>";

echo "</body></html>";
$conn->close();
?>
