<?php
require_once 'services/database.php';

header('Content-Type: text/plain; charset=utf-8');

echo "🃏 Creating Default Flashcard Decks & Cards\n";
echo "==========================================\n\n";

try {
    echo "🔍 Checking current state...\n";
    
    // Check if users exist
    $result = $conn->query("SELECT id, username FROM users ORDER BY id");
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
        echo "  👤 Found user: {$row['username']} (ID: {$row['id']})\n";
    }
    
    if (empty($users)) {
        echo "❌ No users found! Please run robust_database_setup.php first.\n";
        exit(1);
    }
    
    // Get main users
    $adminUser = null;
    $testUser = null;
    
    foreach ($users as $user) {
        if ($user['username'] === 'admin') $adminUser = $user;
        if ($user['username'] === 'testuser') $testUser = $user;
    }
    
    if (!$testUser) {
        echo "❌ testuser not found! Using first available user.\n";
        $testUser = $users[0];
    }
    
    if (!$adminUser) {
        echo "❌ admin user not found! Using first available user.\n";
        $adminUser = $users[0];
    }
    
    echo "  🎯 Main user: {$testUser['username']} (ID: {$testUser['id']})\n";
    echo "  👑 Admin user: {$adminUser['username']} (ID: {$adminUser['id']})\n\n";
    
    echo "🃏 Creating default flashcard decks...\n";
    
    // Create default decks for testuser (so "Thêm vào" button works)
    $defaultDecks = [
        // Personal decks for testuser
        ['My Favorites', 'Từ vựng yêu thích của tôi', 'private', $testUser['id']],
        ['Daily Words', 'Từ vựng hàng ngày', 'private', $testUser['id']],
        ['Difficult Words', 'Từ khó cần ôn tập', 'private', $testUser['id']],
        
        // Public decks from admin
        ['Essential Vocabulary', 'Từ vựng thiết yếu cho người mới bắt đầu', 'public', $adminUser['id']],
        ['Common Verbs', 'Động từ thường dùng trong tiếng Anh', 'public', $adminUser['id']],
        ['Family & Friends', 'Từ vựng về gia đình và bạn bè', 'public', $adminUser['id']],
        ['Food & Drinks', 'Từ vựng về đồ ăn và thức uống', 'public', $adminUser['id']],
        ['Colors & Numbers', 'Màu sắc và số đếm', 'public', $adminUser['id']],
        ['Technology Terms', 'Thuật ngữ công nghệ', 'public', $adminUser['id']],
        ['Business English', 'Tiếng Anh thương mại', 'public', $adminUser['id']]
    ];
    
    $stmt = $conn->prepare("INSERT IGNORE INTO decks (name, description, visibility, user_id) VALUES (?, ?, ?, ?)");
    $deckCount = 0;
    
    foreach ($defaultDecks as $deck) {
        $stmt->bind_param("sssi", $deck[0], $deck[1], $deck[2], $deck[3]);
        if ($stmt->execute()) {
            $deckCount++;
            $owner = ($deck[3] == $testUser['id']) ? 'testuser' : 'admin';
            echo "  ✅ Created: {$deck[0]} ({$deck[2]}, owner: $owner)\n";
        } else {
            echo "  ⚠️  Exists: {$deck[0]}\n";
        }
    }
    
    echo "\n🃏 Adding sample flashcards to decks...\n";
    
    // Get deck IDs
    $deckIds = [];
    $result = $conn->query("SELECT id, name, user_id FROM decks");
    while ($row = $result->fetch_assoc()) {
        $deckIds[$row['name']] = $row['id'];
    }
    
    // Add cards to Essential Vocabulary deck
    if (isset($deckIds['Essential Vocabulary'])) {
        $essentialCards = [
            ['hello', 'xin chào', 'Hello, how are you today?'],
            ['thank you', 'cảm ơn', 'Thank you for your help.'],
            ['please', 'xin vui lòng', 'Please help me with this.'],
            ['sorry', 'xin lỗi', 'Sorry, I am late.'],
            ['yes', 'có, vâng', 'Yes, I agree with you.'],
            ['no', 'không', 'No, I do not want that.'],
            ['good', 'tốt', 'This is a good book.'],
            ['bad', 'xấu, tệ', 'The weather is bad today.'],
            ['big', 'to, lớn', 'That is a big house.'],
            ['small', 'nhỏ', 'I have a small car.']
        ];
        
        $stmt = $conn->prepare("INSERT IGNORE INTO flashcards (deck_id, word, definition, example) VALUES (?, ?, ?, ?)");
        foreach ($essentialCards as $card) {
            $stmt->bind_param("isss", $deckIds['Essential Vocabulary'], $card[0], $card[1], $card[2]);
            if ($stmt->execute()) {
                echo "  ✅ Added to Essential Vocabulary: {$card[0]}\n";
            }
        }
    }
    
    // Add cards to Common Verbs deck
    if (isset($deckIds['Common Verbs'])) {
        $verbCards = [
            ['be', 'là, thì, ở', 'I am a student.'],
            ['have', 'có', 'I have a new car.'],
            ['do', 'làm', 'What do you do for work?'],
            ['go', 'đi', 'I go to work by bus.'],
            ['come', 'đến', 'Please come here.'],
            ['see', 'nhìn thấy', 'I can see the mountains.'],
            ['know', 'biết', 'I know the answer.'],
            ['think', 'nghĩ', 'I think it will rain today.'],
            ['want', 'muốn', 'I want to learn English.'],
            ['like', 'thích', 'I like chocolate ice cream.']
        ];
        
        foreach ($verbCards as $card) {
            $stmt->bind_param("isss", $deckIds['Common Verbs'], $card[0], $card[1], $card[2]);
            if ($stmt->execute()) {
                echo "  ✅ Added to Common Verbs: {$card[0]}\n";
            }
        }
    }
    
    // Add cards to Family & Friends deck
    if (isset($deckIds['Family & Friends'])) {
        $familyCards = [
            ['family', 'gia đình', 'I love my family very much.'],
            ['father', 'bố, cha', 'My father works in an office.'],
            ['mother', 'mẹ', 'My mother cooks delicious food.'],
            ['brother', 'anh trai, em trai', 'My brother is older than me.'],
            ['sister', 'chị gái, em gái', 'My sister studies at university.'],
            ['friend', 'bạn bè', 'He is my best friend.'],
            ['teacher', 'giáo viên', 'My teacher is very kind.'],
            ['student', 'học sinh', 'I am a student.']
        ];
        
        foreach ($familyCards as $card) {
            $stmt->bind_param("isss", $deckIds['Family & Friends'], $card[0], $card[1], $card[2]);
            if ($stmt->execute()) {
                echo "  ✅ Added to Family & Friends: {$card[0]}\n";
            }
        }
    }
    
    // Add cards to Food & Drinks deck
    if (isset($deckIds['Food & Drinks'])) {
        $foodCards = [
            ['water', 'nước', 'I drink water every day.'],
            ['food', 'thức ăn', 'This food is delicious.'],
            ['bread', 'bánh mì', 'I eat bread for breakfast.'],
            ['rice', 'cơm, gạo', 'Rice is a staple food in Asia.'],
            ['coffee', 'cà phê', 'I drink coffee every morning.'],
            ['tea', 'trà', 'Would you like some tea?'],
            ['milk', 'sữa', 'Children need to drink milk.'],
            ['apple', 'táo', 'An apple a day keeps the doctor away.']
        ];
        
        foreach ($foodCards as $card) {
            $stmt->bind_param("isss", $deckIds['Food & Drinks'], $card[0], $card[1], $card[2]);
            if ($stmt->execute()) {
                echo "  ✅ Added to Food & Drinks: {$card[0]}\n";
            }
        }
    }
    
    // Add cards to Colors & Numbers deck
    if (isset($deckIds['Colors & Numbers'])) {
        $colorCards = [
            ['red', 'đỏ', 'I like red roses.'],
            ['blue', 'xanh dương', 'The sky is blue.'],
            ['green', 'xanh lá', 'Trees have green leaves.'],
            ['yellow', 'vàng', 'Bananas are yellow.'],
            ['black', 'đen', 'I wear black shoes.'],
            ['white', 'trắng', 'Snow is white.'],
            ['one', 'một', 'I have one book.'],
            ['two', 'hai', 'There are two cats.'],
            ['three', 'ba', 'I need three apples.'],
            ['ten', 'mười', 'I have ten fingers.']
        ];
        
        foreach ($colorCards as $card) {
            $stmt->bind_param("isss", $deckIds['Colors & Numbers'], $card[0], $card[1], $card[2]);
            if ($stmt->execute()) {
                echo "  ✅ Added to Colors & Numbers: {$card[0]}\n";
            }
        }
    }
    
    // Add some cards to testuser's personal decks
    if (isset($deckIds['My Favorites'])) {
        $favoriteCards = [
            ['beautiful', 'đẹp', 'She is very beautiful.'],
            ['happy', 'vui vẻ', 'I am happy today.'],
            ['love', 'yêu', 'I love you.'],
            ['home', 'nhà', 'There is no place like home.'],
            ['dream', 'giấc mơ', 'Follow your dreams.']
        ];
        
        foreach ($favoriteCards as $card) {
            $stmt->bind_param("isss", $deckIds['My Favorites'], $card[0], $card[1], $card[2]);
            if ($stmt->execute()) {
                echo "  ✅ Added to My Favorites: {$card[0]}\n";
            }
        }
    }
    
    echo "\n🔍 Final verification...\n";
    
    // Count decks and cards
    $result = $conn->query("SELECT COUNT(*) as count FROM decks");
    $totalDecks = $result->fetch_assoc()['count'];
    
    $result = $conn->query("SELECT COUNT(*) as count FROM flashcards");
    $totalCards = $result->fetch_assoc()['count'];
    
    echo "📊 Results:\n";
    echo "  ✅ Total Decks: $totalDecks\n";
    echo "  ✅ Total Flashcards: $totalCards\n\n";
    
    // Show deck breakdown
    echo "🃏 Deck Breakdown:\n";
    $result = $conn->query("
        SELECT d.name, d.visibility, u.username, COUNT(f.id) as card_count 
        FROM decks d 
        LEFT JOIN flashcards f ON d.id = f.deck_id 
        LEFT JOIN users u ON d.user_id = u.id
        GROUP BY d.id, d.name, d.visibility, u.username 
        ORDER BY u.username, d.name
    ");
    
    while ($row = $result->fetch_assoc()) {
        $visibility = $row['visibility'] === 'public' ? '🌐' : '🔒';
        echo "  $visibility {$row['name']}: {$row['card_count']} cards (owner: {$row['username']})\n";
    }
    
    if ($totalDecks > 0 && $totalCards > 0) {
        echo "\n🎉 SUCCESS! Default flashcard decks and cards have been created.\n\n";
        echo "🚀 What you can now do:\n";
        echo "1. 🔐 Login as 'testuser' / 'password'\n";
        echo "2. 🏠 Go to index.php - 'Thêm vào' button will now work!\n";
        echo "3. 🃏 Go to flashcards.php - You can create new flashcards!\n";
        echo "4. 📚 Study existing flashcard decks\n";
        echo "5. ➕ Create your own custom flashcards\n\n";
        
        echo "💡 Available decks for testuser:\n";
        echo "   🔒 My Favorites - Your personal favorite words\n";
        echo "   🔒 Daily Words - Words you encounter daily\n";
        echo "   🔒 Difficult Words - Words that need more practice\n";
        echo "   🌐 Plus access to all public decks from admin\n\n";
        
        echo "🎯 The 'Thêm vào' (Add to) button will now show these decks!\n";
    } else {
        echo "\n⚠️  Something went wrong. Please check the errors above.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "\n";
}

$conn->close();
?>
