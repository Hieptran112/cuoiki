<?php
session_start();
require_once 'services/database.php';

header('Content-Type: text/plain');

if (!isset($_SESSION['user_id'])) {
    echo "Error: Please login first";
    exit;
}

$userId = $_SESSION['user_id'];

try {
    // Get all decks in system
    $result = $conn->query("SELECT id, user_id, name, description FROM decks ORDER BY created_at DESC");
    $allDecks = [];
    while ($row = $result->fetch_assoc()) {
        $allDecks[] = $row;
    }

    // Get current user's decks
    $stmt = $conn->prepare("SELECT id, name FROM decks WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $userDecks = [];
    while ($row = $result->fetch_assoc()) {
        $userDecks[] = $row['name'];
    }
    
    echo "Current situation:\n";
    echo "- Total decks in system: " . count($allDecks) . "\n";
    echo "- Your decks: " . count($userDecks) . "\n";
    echo "- Your deck names: " . implode(", ", $userDecks) . "\n\n";
    
    // Check if user has the "correct" decks from flashcards.php
    $expectedDecks = ["Trò chơi", "Công nghệ thông tin", "Âm nhạc", "Y tế", "Kinh doanh"];
    $hasExpectedDecks = true;
    
    foreach ($expectedDecks as $expectedDeck) {
        if (!in_array($expectedDeck, $userDecks)) {
            $hasExpectedDecks = false;
            break;
        }
    }
    
    if ($hasExpectedDecks) {
        echo "✅ You already have the correct decks from flashcards.php\n";
    } else {
        echo "❌ You don't have the expected decks from flashcards.php\n";
        echo "Expected: " . implode(", ", $expectedDecks) . "\n";
        
        // Option 1: Transfer all existing decks to current user
        if (count($allDecks) > 0) {
            echo "\nOption 1: Transfer all existing decks to your account\n";
            $conn->query("UPDATE decks SET user_id = $userId");
            $affected = $conn->affected_rows;
            echo "✅ Transferred $affected decks to your account\n";
        }
        
        // Option 2: Create the expected decks if they don't exist
        echo "\nOption 2: Ensure you have the expected deck names\n";
        foreach ($expectedDecks as $deckName) {
            // Check if deck with this name exists for user
            $stmt = $conn->prepare("SELECT id FROM flashcard_decks WHERE user_id = ? AND name = ?");
            $stmt->bind_param("is", $userId, $deckName);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 0) {
                // Create the deck
                $description = "Từ vựng chuyên ngành " . $deckName;
                $stmt = $conn->prepare("INSERT INTO flashcard_decks (user_id, name, description) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $userId, $deckName, $description);
                if ($stmt->execute()) {
                    echo "✅ Created deck: $deckName\n";
                } else {
                    echo "❌ Failed to create deck: $deckName\n";
                }
            } else {
                echo "ℹ️ Deck already exists: $deckName\n";
            }
        }
    }
    
    // Final check
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM decks WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $finalCount = $result->fetch_assoc()['count'];

    echo "\n🎉 Final result: You now have $finalCount decks\n";

    // List final decks
    $stmt = $conn->prepare("SELECT name FROM decks WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $finalDecks = [];
    while ($row = $result->fetch_assoc()) {
        $finalDecks[] = $row['name'];
    }
    
    echo "Your decks now: " . implode(", ", $finalDecks) . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

$conn->close();
?>
