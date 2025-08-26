<?php
require_once 'services/database.php';

header('Content-Type: text/plain; charset=utf-8');

echo "📚 Topics and Lessons Data Insertion\n";
echo "====================================\n\n";

try {
    echo "🔍 Checking tables...\n";
    
    // Check if required tables exist
    $result = $conn->query("SHOW TABLES LIKE 'topics'");
    if ($result->num_rows === 0) {
        echo "❌ topics table does not exist!\n";
        exit(1);
    }
    
    $result = $conn->query("SHOW TABLES LIKE 'topic_lessons'");
    if ($result->num_rows === 0) {
        echo "❌ topic_lessons table does not exist!\n";
        exit(1);
    }
    
    echo "✅ Both tables exist\n\n";
    
    echo "📊 Inserting topics...\n";
    
    // Insert topics
    $topics = [
        ['Basic Vocabulary', 'Learn essential English words for daily communication', '#4CAF50', 'fas fa-book'],
        ['Grammar Fundamentals', 'Master basic English grammar rules and structures', '#2196F3', 'fas fa-language'],
        ['Everyday Conversations', 'Practice common English phrases and expressions', '#FF9800', 'fas fa-comments'],
        ['Listening Skills', 'Improve your English listening comprehension', '#9C27B0', 'fas fa-headphones'],
        ['Reading Comprehension', 'Develop your English reading skills', '#F44336', 'fas fa-book-open'],
        ['Computer Science', 'Learn IT and computer terminology in English', '#607D8B', 'fas fa-laptop']
    ];
    
    $stmt = $conn->prepare("INSERT IGNORE INTO topics (name, description, color, icon, is_active) VALUES (?, ?, ?, ?, 1)");
    $topicCount = 0;
    
    foreach ($topics as $topic) {
        $stmt->bind_param("ssss", $topic[0], $topic[1], $topic[2], $topic[3]);
        if ($stmt->execute()) {
            $topicCount++;
            echo "  ✅ Added: {$topic[0]}\n";
        } else {
            echo "  ❌ Failed: {$topic[0]} - " . $conn->error . "\n";
        }
    }
    
    echo "\n📝 Inserting topic lessons...\n";
    
    // Get topic IDs for lessons
    $topicIds = [];
    $result = $conn->query("SELECT id, name FROM topics ORDER BY id");
    while ($row = $result->fetch_assoc()) {
        $topicIds[$row['name']] = $row['id'];
    }
    
    // Insert lessons for Basic Vocabulary
    if (isset($topicIds['Basic Vocabulary'])) {
        $basicVocabLessons = [
            ['Family Members', 'Learn words related to family relationships: father, mother, brother, sister, son, daughter, grandfather, grandmother, uncle, aunt, cousin, nephew, niece', 1],
            ['Colors and Shapes', 'Basic colors: red, blue, green, yellow, black, white, brown, pink, purple, orange. Shapes: circle, square, triangle, rectangle, oval', 2],
            ['Food and Drinks', 'Common food items: bread, rice, meat, fish, vegetables, fruits, milk, water, coffee, tea, juice', 3],
            ['Animals', 'Domestic animals: dog, cat, bird, fish. Wild animals: lion, tiger, elephant, monkey, bear', 4],
            ['Body Parts', 'Parts of the human body: head, face, eye, nose, mouth, ear, hand, arm, leg, foot', 5],
            ['House and Home', 'Rooms: bedroom, kitchen, bathroom, living room. Furniture: table, chair, bed, sofa, desk', 6],
            ['Transportation', 'Vehicles: car, bus, train, plane, bicycle, motorcycle, boat, ship', 7],
            ['Weather', 'Weather conditions: sunny, rainy, cloudy, windy, hot, cold, warm, cool, snow, storm', 8]
        ];
        
        $stmt = $conn->prepare("INSERT IGNORE INTO topic_lessons (topic_id, title, content, lesson_order) VALUES (?, ?, ?, ?)");
        foreach ($basicVocabLessons as $lesson) {
            $stmt->bind_param("issi", $topicIds['Basic Vocabulary'], $lesson[0], $lesson[1], $lesson[2]);
            if ($stmt->execute()) {
                echo "  ✅ Added: {$lesson[0]}\n";
            }
        }
    }
    
    // Insert lessons for Grammar Fundamentals
    if (isset($topicIds['Grammar Fundamentals'])) {
        $grammarLessons = [
            ['Present Simple Tense', 'Learn to use present simple tense: I work, You work, He/She works, We work, They work. Used for habits and facts.', 1],
            ['Articles (a, an, the)', 'When to use articles: "a" before consonant sounds, "an" before vowel sounds, "the" for specific things', 2],
            ['Plural Forms', 'Regular plurals: add -s (book→books). Irregular plurals: child→children, man→men, woman→women', 3],
            ['Past Simple Tense', 'Regular verbs: add -ed (work→worked). Irregular verbs: go→went, see→saw, have→had', 4],
            ['Question Formation', 'Yes/No questions: Do you...? Does he...? Did they...? Wh-questions: What, Where, When, Why, How', 5],
            ['Present Continuous', 'Form: am/is/are + verb-ing. Used for actions happening now: I am studying English', 6]
        ];
        
        foreach ($grammarLessons as $lesson) {
            $stmt->bind_param("issi", $topicIds['Grammar Fundamentals'], $lesson[0], $lesson[1], $lesson[2]);
            if ($stmt->execute()) {
                echo "  ✅ Added: {$lesson[0]}\n";
            }
        }
    }
    
    // Insert lessons for Everyday Conversations
    if (isset($topicIds['Everyday Conversations'])) {
        $conversationLessons = [
            ['Greetings and Introductions', 'Hello, Hi, Good morning, Good afternoon, Good evening. My name is..., Nice to meet you', 1],
            ['Asking for Directions', 'Where is...? How can I get to...? Go straight, turn left/right, It is next to...', 2],
            ['Shopping', 'How much is this? Can I try this on? I would like to buy... Do you have...?', 3],
            ['At a Restaurant', 'I would like to order... Can I have the menu? The bill, please. What do you recommend?', 4],
            ['Making Appointments', 'Can we meet at...? What time is convenient? I am available on... Let us reschedule', 5]
        ];
        
        foreach ($conversationLessons as $lesson) {
            $stmt->bind_param("issi", $topicIds['Everyday Conversations'], $lesson[0], $lesson[1], $lesson[2]);
            if ($stmt->execute()) {
                echo "  ✅ Added: {$lesson[0]}\n";
            }
        }
    }
    
    // Insert lessons for Computer Science
    if (isset($topicIds['Computer Science'])) {
        $csLessons = [
            ['Computer Basics', 'Learn basic computer terms: CPU, RAM, hard drive, software, hardware, operating system', 1],
            ['Internet and Networks', 'Internet terminology: website, browser, email, download, upload, Wi-Fi, router', 2],
            ['Programming Concepts', 'Basic programming terms: code, algorithm, variable, function, loop, condition', 3]
        ];
        
        foreach ($csLessons as $lesson) {
            $stmt->bind_param("issi", $topicIds['Computer Science'], $lesson[0], $lesson[1], $lesson[2]);
            if ($stmt->execute()) {
                echo "  ✅ Added: {$lesson[0]}\n";
            }
        }
    }
    
    // Insert basic lessons for other topics
    $otherTopics = ['Listening Skills', 'Reading Comprehension'];
    foreach ($otherTopics as $topicName) {
        if (isset($topicIds[$topicName])) {
            $basicLessons = [
                ['Introduction to ' . $topicName, 'Learn the fundamentals of ' . strtolower($topicName) . ' and basic techniques.', 1],
                ['Practice Exercises', 'Practice exercises to improve your ' . strtolower($topicName) . ' abilities.', 2],
                ['Advanced Techniques', 'Advanced methods and strategies for better ' . strtolower($topicName) . '.', 3]
            ];
            
            foreach ($basicLessons as $lesson) {
                $stmt->bind_param("issi", $topicIds[$topicName], $lesson[0], $lesson[1], $lesson[2]);
                if ($stmt->execute()) {
                    echo "  ✅ Added: {$lesson[0]} (for $topicName)\n";
                }
            }
        }
    }
    
    echo "\n🔍 Final verification...\n";
    
    // Count topics and lessons
    $result = $conn->query("SELECT COUNT(*) as count FROM topics");
    $topicCount = $result->fetch_assoc()['count'];
    
    $result = $conn->query("SELECT COUNT(*) as count FROM topic_lessons");
    $lessonCount = $result->fetch_assoc()['count'];
    
    echo "📊 Results:\n";
    echo "  ✅ Topics: $topicCount\n";
    echo "  ✅ Topic Lessons: $lessonCount\n\n";
    
    // Show topic breakdown
    echo "📚 Topic Breakdown:\n";
    $result = $conn->query("
        SELECT t.name, COUNT(tl.id) as lesson_count 
        FROM topics t 
        LEFT JOIN topic_lessons tl ON t.id = tl.topic_id 
        GROUP BY t.id, t.name 
        ORDER BY t.id
    ");
    
    while ($row = $result->fetch_assoc()) {
        echo "  📖 {$row['name']}: {$row['lesson_count']} lessons\n";
    }
    
    if ($topicCount > 0 && $lessonCount > 0) {
        echo "\n🎉 SUCCESS! Topics and lessons have been created.\n\n";
        echo "🚀 Next steps:\n";
        echo "1. Visit topics.php to see the topics\n";
        echo "2. Run: php src/insert_all_data.php (for complete data)\n";
        echo "3. Test the topics functionality in your app\n\n";
        echo "💡 You now have:\n";
        echo "   - 6 learning topics\n";
        echo "   - 20+ structured lessons\n";
        echo "   - Content covering vocabulary, grammar, conversation, etc.\n";
    } else {
        echo "\n⚠️  Something went wrong. Please check the errors above.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "\n";
}

$conn->close();
?>
