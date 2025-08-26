<?php
require_once 'services/database.php';

header('Content-Type: text/plain; charset=utf-8');

echo "👥 Creating Default Decks for ALL Users\n";
echo "======================================\n\n";

try {
    echo "🔍 Finding all users...\n";
    
    // Get all users
    $result = $conn->query("SELECT id, username, email FROM users ORDER BY id");
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
        echo "  👤 Found user: {$row['username']} (ID: {$row['id']}, Email: {$row['email']})\n";
    }
    
    if (empty($users)) {
        echo "❌ No users found! Please create some users first.\n";
        exit(1);
    }
    
    echo "\n🃏 Creating default personal decks for each user...\n";
    
    // Default personal decks that every user should have
    $defaultPersonalDecks = [
        ['My Favorites', 'Từ vựng yêu thích của tôi - những từ tôi muốn nhớ lâu'],
        ['Daily Words', 'Từ vựng hàng ngày - những từ tôi gặp thường xuyên'],
        ['Difficult Words', 'Từ khó - những từ cần ôn tập nhiều lần'],
        ['New Words', 'Từ mới - những từ vừa học được'],
        ['Review Later', 'Ôn tập sau - những từ cần xem lại']
    ];
    
    $stmt = $conn->prepare("INSERT IGNORE INTO decks (user_id, name, description, visibility) VALUES (?, ?, ?, 'private')");
    $totalDecksCreated = 0;
    
    foreach ($users as $user) {
        echo "\n📚 Creating decks for user: {$user['username']}\n";
        $userDecksCreated = 0;
        
        foreach ($defaultPersonalDecks as $deck) {
            $stmt->bind_param("iss", $user['id'], $deck[0], $deck[1]);
            if ($stmt->execute()) {
                if ($conn->affected_rows > 0) {
                    echo "  ✅ Created: {$deck[0]}\n";
                    $userDecksCreated++;
                    $totalDecksCreated++;
                } else {
                    echo "  ⚠️  Already exists: {$deck[0]}\n";
                }
            } else {
                echo "  ❌ Failed: {$deck[0]} - " . $conn->error . "\n";
            }
        }
        
        echo "  📊 Created $userDecksCreated new decks for {$user['username']}\n";
    }
    
    echo "\n🎯 Adding starter flashcards to 'My Favorites' decks...\n";
    
    // Add some starter cards to each user's "My Favorites" deck
    $starterCards = [
        ['hello', 'xin chào', 'Hello, how are you?'],
        ['thank you', 'cảm ơn', 'Thank you very much!'],
        ['please', 'xin vui lòng', 'Please help me.'],
        ['good', 'tốt', 'This is very good.'],
        ['beautiful', 'đẹp', 'You are beautiful.']
    ];
    
    // Get all "My Favorites" decks
    $result = $conn->query("
        SELECT d.id, d.user_id, u.username 
        FROM decks d 
        JOIN users u ON d.user_id = u.id 
        WHERE d.name = 'My Favorites'
    ");
    
    $cardStmt = $conn->prepare("INSERT IGNORE INTO flashcards (deck_id, word, definition, example) VALUES (?, ?, ?, ?)");
    $totalCardsAdded = 0;
    
    while ($deck = $result->fetch_assoc()) {
        echo "  📝 Adding starter cards to {$deck['username']}'s 'My Favorites'...\n";
        $userCardsAdded = 0;
        
        foreach ($starterCards as $card) {
            $cardStmt->bind_param("isss", $deck['id'], $card[0], $card[1], $card[2]);
            if ($cardStmt->execute()) {
                if ($conn->affected_rows > 0) {
                    $userCardsAdded++;
                    $totalCardsAdded++;
                }
            }
        }
        
        echo "    ✅ Added $userCardsAdded starter cards\n";
    }
    
    echo "\n🔍 Final verification...\n";
    
    // Count total decks and cards per user
    $result = $conn->query("
        SELECT 
            u.username,
            u.email,
            COUNT(DISTINCT d.id) as deck_count,
            COUNT(DISTINCT f.id) as card_count
        FROM users u
        LEFT JOIN decks d ON u.id = d.user_id
        LEFT JOIN flashcards f ON d.id = f.deck_id
        GROUP BY u.id, u.username, u.email
        ORDER BY u.username
    ");
    
    echo "📊 User Summary:\n";
    while ($row = $result->fetch_assoc()) {
        echo "  👤 {$row['username']}: {$row['deck_count']} decks, {$row['card_count']} cards\n";
    }
    
    // Show deck breakdown
    echo "\n🃏 All Personal Decks:\n";
    $result = $conn->query("
        SELECT u.username, d.name, COUNT(f.id) as card_count
        FROM users u
        JOIN decks d ON u.id = d.user_id AND d.visibility = 'private'
        LEFT JOIN flashcards f ON d.id = f.deck_id
        GROUP BY u.id, u.username, d.id, d.name
        ORDER BY u.username, d.name
    ");
    
    while ($row = $result->fetch_assoc()) {
        echo "  🔒 {$row['username']} → {$row['name']}: {$row['card_count']} cards\n";
    }
    
    echo "\n🎉 SUCCESS! All users now have default personal decks.\n\n";
    echo "✅ What this means:\n";
    echo "1. 🆕 NEW USERS: Will have 5 personal decks ready to use\n";
    echo "2. 🔄 EXISTING USERS: Got any missing default decks added\n";
    echo "3. ➕ 'Thêm vào' button: Works for ALL users now!\n";
    echo "4. 🃏 Flashcard creation: All users can create cards immediately\n\n";
    
    echo "📚 Every user now has these personal decks:\n";
    foreach ($defaultPersonalDecks as $deck) {
        echo "  🔒 {$deck[0]} - {$deck[1]}\n";
    }
    
    echo "\n🚀 Next steps:\n";
    echo "1. Login as any user (existing or new)\n";
    echo "2. Go to index.php - 'Thêm vào' button will work\n";
    echo "3. Go to flashcards.php - Can create flashcards immediately\n";
    echo "4. All users have starter cards in 'My Favorites'\n\n";
    
    echo "💡 For new users in the future:\n";
    echo "   Consider adding this deck creation logic to your user registration process!\n";
    
} catch (Exception $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "\n";
}

$conn->close();
?>
