<?php
// Auto-create default decks for new users
// Include this function in your user registration process

function createDefaultDecksForUser($userId, $conn) {
    try {
        // Default personal decks for every new user
        $defaultDecks = [
            ['My Favorites', 'Từ vựng yêu thích của tôi - những từ tôi muốn nhớ lâu'],
            ['Daily Words', 'Từ vựng hàng ngày - những từ tôi gặp thường xuyên'],
            ['Difficult Words', 'Từ khó - những từ cần ôn tập nhiều lần'],
            ['New Words', 'Từ mới - những từ vừa học được'],
            ['Review Later', 'Ôn tập sau - những từ cần xem lại']
        ];
        
        $stmt = $conn->prepare("INSERT INTO decks (user_id, name, description, visibility) VALUES (?, ?, ?, 'private')");
        $createdCount = 0;
        
        foreach ($defaultDecks as $deck) {
            $stmt->bind_param("iss", $userId, $deck[0], $deck[1]);
            if ($stmt->execute()) {
                $createdCount++;
            }
        }
        
        // Add starter cards to "My Favorites" deck
        $favoriteDeckResult = $conn->query("SELECT id FROM decks WHERE user_id = $userId AND name = 'My Favorites' LIMIT 1");
        if ($favoriteDeckResult && $favoriteDeckResult->num_rows > 0) {
            $deckId = $favoriteDeckResult->fetch_assoc()['id'];
            
            $starterCards = [
                ['hello', 'xin chào', 'Hello, how are you?'],
                ['thank you', 'cảm ơn', 'Thank you very much!'],
                ['please', 'xin vui lòng', 'Please help me.'],
                ['good', 'tốt', 'This is very good.'],
                ['welcome', 'chào mừng', 'Welcome to our app!']
            ];
            
            $cardStmt = $conn->prepare("INSERT INTO flashcards (deck_id, word, definition, example) VALUES (?, ?, ?, ?)");
            foreach ($starterCards as $card) {
                $cardStmt->bind_param("isss", $deckId, $card[0], $card[1], $card[2]);
                $cardStmt->execute();
            }
        }
        
        return $createdCount;
        
    } catch (Exception $e) {
        error_log("Error creating default decks for user $userId: " . $e->getMessage());
        return 0;
    }
}

// Example usage in registration process:
/*
// After successful user registration
if ($registrationSuccessful) {
    $newUserId = $conn->insert_id; // Get the new user's ID
    $decksCreated = createDefaultDecksForUser($newUserId, $conn);
    
    if ($decksCreated > 0) {
        // Success - user has default decks
        echo "Account created successfully with $decksCreated personal flashcard decks!";
    }
}
*/

// Test function - creates decks for a specific user
function testCreateDecksForUser($username) {
    global $conn;
    
    $result = $conn->query("SELECT id FROM users WHERE username = '$username' LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $userId = $result->fetch_assoc()['id'];
        $created = createDefaultDecksForUser($userId, $conn);
        echo "Created $created default decks for user: $username\n";
        return true;
    } else {
        echo "User not found: $username\n";
        return false;
    }
}

// If this file is run directly, test with a user
if (basename(__FILE__) == basename($_SERVER["SCRIPT_NAME"])) {
    require_once 'services/database.php';
    
    header('Content-Type: text/plain; charset=utf-8');
    
    echo "🧪 Testing Auto-Create User Decks Function\n";
    echo "==========================================\n\n";
    
    // Test with existing users
    $testUsers = ['student1', 'student2', 'teacher1'];
    
    foreach ($testUsers as $username) {
        echo "Testing with user: $username\n";
        testCreateDecksForUser($username);
        echo "\n";
    }
    
    echo "✅ Test completed. Check your database to see the new decks.\n";
}
?>
