<?php
require_once 'services/database.php';

header('Content-Type: text/plain; charset=utf-8');

echo "📚 Creating Comprehensive Lessons (Beginner to Advanced)\n";
echo "=======================================================\n\n";

try {
    echo "🔍 Checking existing topics...\n";
    
    // Get all topics
    $result = $conn->query("SELECT id, name FROM topics ORDER BY id");
    $topics = [];
    while ($row = $result->fetch_assoc()) {
        $topics[$row['name']] = $row['id'];
        echo "  📖 Found topic: {$row['name']} (ID: {$row['id']})\n";
    }
    
    if (empty($topics)) {
        echo "❌ No topics found! Please run insert_topics_and_lessons.php first.\n";
        exit(1);
    }
    
    echo "\n📝 Creating comprehensive lessons for each topic...\n";
    
    $stmt = $conn->prepare("INSERT IGNORE INTO topic_lessons (topic_id, title, content, lesson_order, is_active) VALUES (?, ?, ?, ?, 1)");
    $totalLessonsAdded = 0;
    
    // =====================================================
    // BASIC VOCABULARY LESSONS (Beginner to Advanced)
    // =====================================================
    if (isset($topics['Basic Vocabulary'])) {
        echo "\n📚 Creating Basic Vocabulary lessons...\n";
        
        $vocabLessons = [
            // Beginner Level (1-10)
            [1, 'Greetings & Polite Expressions', 'Learn essential greetings: Hello, Hi, Good morning, Good afternoon, Good evening, Good night. Polite expressions: Please, Thank you, You\'re welcome, Excuse me, I\'m sorry, Pardon me.'],
            [2, 'Family Members', 'Family vocabulary: father/dad, mother/mom, brother, sister, son, daughter, grandfather/grandpa, grandmother/grandma, uncle, aunt, cousin, nephew, niece, husband, wife, parents, children.'],
            [3, 'Numbers 1-100', 'Cardinal numbers: 1-20 (one, two, three...), tens (twenty, thirty, forty...), hundreds. Ordinal numbers: first, second, third, fourth, fifth. Usage in dates, ages, quantities.'],
            [4, 'Colors & Shapes', 'Basic colors: red, blue, green, yellow, orange, purple, pink, brown, black, white, gray. Shapes: circle, square, triangle, rectangle, oval, diamond, star, heart.'],
            [5, 'Days, Months & Seasons', 'Days of the week: Monday through Sunday. Months: January through December. Seasons: spring, summer, autumn/fall, winter. Time expressions: today, tomorrow, yesterday.'],
            [6, 'Body Parts', 'Head and face: head, hair, face, eye, nose, mouth, ear, tooth/teeth, neck. Body: arm, hand, finger, leg, foot, toe, back, chest, stomach, shoulder.'],
            [7, 'Food & Drinks', 'Basic foods: bread, rice, meat, fish, chicken, vegetables, fruits, egg, cheese, milk. Drinks: water, coffee, tea, juice, soda. Meals: breakfast, lunch, dinner.'],
            [8, 'House & Home', 'Rooms: bedroom, kitchen, bathroom, living room, dining room, garage. Furniture: table, chair, bed, sofa, desk, bookshelf, lamp, TV, refrigerator, stove.'],
            [9, 'Clothing & Accessories', 'Clothes: shirt, pants, dress, skirt, jacket, coat, shoes, socks, hat, gloves. Accessories: watch, glasses, bag, belt, jewelry, scarf.'],
            [10, 'Transportation', 'Vehicles: car, bus, train, plane, bicycle, motorcycle, boat, ship, taxi, subway. Transportation verbs: drive, ride, fly, walk, take the bus/train.'],
            
            // Intermediate Level (11-20)
            [11, 'Weather & Climate', 'Weather conditions: sunny, cloudy, rainy, snowy, windy, foggy, stormy. Temperature: hot, warm, cool, cold, freezing. Climate: humid, dry, mild, severe.'],
            [12, 'Animals & Pets', 'Domestic animals: dog, cat, bird, fish, rabbit, hamster. Farm animals: cow, pig, sheep, horse, chicken, goat. Wild animals: lion, tiger, elephant, bear, wolf.'],
            [13, 'Jobs & Professions', 'Common jobs: teacher, doctor, nurse, engineer, lawyer, police officer, firefighter, chef, driver, farmer, student, businessman, artist, musician.'],
            [14, 'School & Education', 'School subjects: math, science, English, history, geography, art, music, PE. School items: book, pen, pencil, paper, notebook, backpack, computer, desk.'],
            [15, 'Shopping & Money', 'Shopping vocabulary: store, shop, market, price, cost, expensive, cheap, discount, sale. Money: dollar, cent, cash, credit card, receipt, change.'],
            [16, 'Health & Medical', 'Body systems: heart, lungs, brain, stomach, liver. Health: healthy, sick, pain, headache, fever, cold, flu, medicine, doctor, hospital, pharmacy.'],
            [17, 'Technology & Internet', 'Devices: computer, laptop, phone, tablet, TV, camera. Internet: website, email, social media, download, upload, wifi, password, username.'],
            [18, 'Sports & Recreation', 'Sports: football, basketball, tennis, swimming, running, cycling, golf, baseball. Recreation: movie, music, reading, dancing, hiking, camping.'],
            [19, 'Travel & Tourism', 'Travel: airport, hotel, passport, ticket, luggage, vacation, tourist, guide, map, destination, flight, reservation, check-in, check-out.'],
            [20, 'Emotions & Feelings', 'Basic emotions: happy, sad, angry, excited, nervous, worried, surprised, confused, tired, hungry, thirsty, bored, interested, proud.'],
            
            // Advanced Level (21-25)
            [21, 'Business & Finance', 'Business terms: company, corporation, employee, manager, CEO, profit, loss, investment, budget, marketing, sales, customer, client, meeting, presentation.'],
            [22, 'Science & Nature', 'Scientific terms: experiment, research, theory, hypothesis, data, analysis. Nature: environment, ecosystem, pollution, conservation, renewable energy, climate change.'],
            [23, 'Arts & Culture', 'Arts: painting, sculpture, music, literature, theater, dance, photography, film. Culture: tradition, custom, festival, celebration, heritage, diversity.'],
            [24, 'Government & Politics', 'Political terms: government, president, minister, parliament, election, vote, democracy, law, policy, citizen, rights, freedom, justice.'],
            [25, 'Abstract Concepts', 'Abstract ideas: love, hate, beauty, truth, justice, freedom, peace, war, success, failure, hope, fear, dream, reality, imagination, creativity.']
        ];
        
        foreach ($vocabLessons as $lesson) {
            $stmt->bind_param("issi", $topics['Basic Vocabulary'], $lesson[1], $lesson[2], $lesson[0]);
            if ($stmt->execute() && $conn->affected_rows > 0) {
                echo "  ✅ Added: {$lesson[1]}\n";
                $totalLessonsAdded++;
            }
        }
    }
    
    // =====================================================
    // GRAMMAR FUNDAMENTALS LESSONS
    // =====================================================
    if (isset($topics['Grammar Fundamentals'])) {
        echo "\n📝 Creating Grammar Fundamentals lessons...\n";
        
        $grammarLessons = [
            // Beginner Grammar (1-10)
            [1, 'Parts of Speech', 'Learn the 8 parts of speech: noun (person, place, thing), verb (action word), adjective (describes noun), adverb (describes verb), pronoun (replaces noun), preposition (shows relationship), conjunction (connects words), interjection (expresses emotion).'],
            [2, 'Verb "To Be" (Present)', 'Master the verb "to be": I am, you are, he/she/it is, we are, they are. Contractions: I\'m, you\'re, he\'s, we\'re, they\'re. Negative: I am not, you are not, etc.'],
            [3, 'Articles (a, an, the)', 'Indefinite articles: "a" before consonant sounds (a book), "an" before vowel sounds (an apple). Definite article: "the" for specific things (the book on the table).'],
            [4, 'Plural Nouns', 'Regular plurals: add -s (book→books), add -es after s,x,z,ch,sh (box→boxes). Irregular plurals: child→children, man→men, woman→women, foot→feet, tooth→teeth.'],
            [5, 'Present Simple Tense', 'Form: I/you/we/they + base verb, he/she/it + verb+s. Usage: habits (I drink coffee every morning), facts (The sun rises in the east), schedules (The train leaves at 8 AM).'],
            [6, 'Present Simple Questions', 'Yes/No questions: Do you like coffee? Does she work here? Wh-questions: What do you do? Where does he live? When do they arrive? Why do you study English?'],
            [7, 'Present Simple Negative', 'Negative form: I/you/we/they + don\'t + base verb, he/she/it + doesn\'t + base verb. Examples: I don\'t like spinach. She doesn\'t work on weekends.'],
            [8, 'Possessive Forms', 'Possessive adjectives: my, your, his, her, its, our, their. Possessive pronouns: mine, yours, his, hers, ours, theirs. Possessive \'s: John\'s car, the cat\'s tail.'],
            [9, 'There is/There are', 'Singular: There is a book on the table. Plural: There are three books on the shelf. Questions: Is there a bank nearby? Are there any restaurants here?'],
            [10, 'Prepositions of Place', 'Location prepositions: in (in the box), on (on the table), at (at home), under (under the bed), next to (next to the bank), between (between two chairs).'],
            
            // Intermediate Grammar (11-20)
            [11, 'Past Simple Tense', 'Regular verbs: add -ed (work→worked, play→played). Irregular verbs: go→went, see→saw, have→had, do→did, get→got, come→came, take→took.'],
            [12, 'Past Simple Questions & Negatives', 'Questions: Did you go to the party? Where did she live? Negatives: I didn\'t go to work yesterday. He didn\'t finish his homework.'],
            [13, 'Present Continuous Tense', 'Form: am/is/are + verb-ing. Usage: actions happening now (I am studying), temporary situations (She is living in Paris), future plans (We are meeting tomorrow).'],
            [14, 'Future Tense (will/going to)', 'Will: predictions (It will rain tomorrow), spontaneous decisions (I\'ll help you). Going to: plans (I\'m going to visit my parents), predictions with evidence (Look at those clouds! It\'s going to rain).'],
            [15, 'Modal Verbs (can, could, should)', 'Can: ability (I can swim), permission (Can I go?). Could: past ability (I could run fast when I was young), polite requests (Could you help me?). Should: advice (You should study more).'],
            [16, 'Comparative & Superlative', 'Short adjectives: tall→taller→tallest, big→bigger→biggest. Long adjectives: beautiful→more beautiful→most beautiful. Irregular: good→better→best, bad→worse→worst.'],
            [17, 'Present Perfect Tense', 'Form: have/has + past participle. Usage: experiences (I have been to Japan), unfinished actions (I have lived here for 5 years), recent actions (She has just arrived).'],
            [18, 'Countable vs Uncountable Nouns', 'Countable: can be counted (one book, two books), use a/an, many, few. Uncountable: cannot be counted (water, money, information), use much, little, some, any.'],
            [19, 'Question Words', 'What (thing), Who (person), Where (place), When (time), Why (reason), How (manner), Which (choice), Whose (possession), How much/many (quantity).'],
            [20, 'Conditional Sentences (Type 1)', 'First conditional: If + present simple, will + base verb. Examples: If it rains, I will stay home. If you study hard, you will pass the exam.'],
            
            // Advanced Grammar (21-25)
            [21, 'Past Continuous & Past Perfect', 'Past continuous: was/were + verb-ing (I was sleeping when you called). Past perfect: had + past participle (I had finished work before she arrived).'],
            [22, 'Passive Voice', 'Form: be + past participle. Present: The book is written by the author. Past: The house was built in 1990. Future: The project will be completed next month.'],
            [23, 'Reported Speech', 'Direct: He said, "I am tired." Indirect: He said that he was tired. Reporting verbs: say, tell, ask, explain, suggest. Time changes: today→that day, tomorrow→the next day.'],
            [24, 'Conditional Sentences (Types 2 & 3)', 'Second conditional: If I had money, I would travel. Third conditional: If I had studied harder, I would have passed the exam. Mixed conditionals.'],
            [25, 'Advanced Verb Tenses', 'Future perfect: I will have finished by 6 PM. Future continuous: I will be working tomorrow. Present perfect continuous: I have been studying for 3 hours.']
        ];
        
        foreach ($grammarLessons as $lesson) {
            $stmt->bind_param("issi", $topics['Grammar Fundamentals'], $lesson[1], $lesson[2], $lesson[0]);
            if ($stmt->execute() && $conn->affected_rows > 0) {
                echo "  ✅ Added: {$lesson[1]}\n";
                $totalLessonsAdded++;
            }
        }
    }
    
    // =====================================================
    // EVERYDAY CONVERSATIONS LESSONS
    // =====================================================
    if (isset($topics['Everyday Conversations'])) {
        echo "\n💬 Creating Everyday Conversations lessons...\n";
        
        $conversationLessons = [
            // Beginner Conversations (1-10)
            [1, 'Basic Greetings & Introductions', 'Greetings: Hello, Hi, Good morning/afternoon/evening. Introductions: My name is..., I\'m..., Nice to meet you, How are you? I\'m fine, thank you. Where are you from? I\'m from...'],
            [2, 'Asking for Personal Information', 'Questions: What\'s your name? How old are you? Where do you live? What do you do? Are you married? Do you have children? Phone number? Email address?'],
            [3, 'Talking About Family', 'Family members: This is my father/mother/brother/sister. I have two children. My son is 10 years old. My daughter goes to school. Do you have any siblings?'],
            [4, 'Describing Appearance', 'Physical features: tall/short, thin/fat, young/old, beautiful/handsome. Hair: long/short, black/brown/blonde, straight/curly. Eyes: blue/brown/green eyes.'],
            [5, 'Talking About Hobbies', 'Hobbies: I like reading/swimming/cooking. My hobby is playing guitar. In my free time, I watch movies. Do you like sports? What do you do for fun?'],
            [6, 'Shopping Conversations', 'At the store: How much is this? Can I try this on? Do you have this in a different size/color? I\'ll take it. Where is the fitting room? Can I pay by card?'],
            [7, 'Ordering Food at Restaurant', 'Restaurant phrases: Can I see the menu? I\'d like to order... What do you recommend? Can I have the bill, please? Is service charge included? The food is delicious.'],
            [8, 'Asking for Directions', 'Direction questions: Where is the bank? How do I get to the station? Is it far from here? Directions: Go straight, turn left/right, it\'s next to..., opposite..., between...'],
            [9, 'Making Appointments', 'Scheduling: Can we meet tomorrow? What time is good for you? I\'m available at 3 PM. Let\'s meet at the coffee shop. Can we reschedule? I\'m sorry, I\'m busy.'],
            [10, 'Talking About Weather', 'Weather expressions: It\'s sunny/rainy/cloudy today. It\'s hot/cold/warm. I like this weather. What\'s the weather like tomorrow? It looks like it\'s going to rain.'],
            
            // Intermediate Conversations (11-20)
            [11, 'At the Doctor\'s Office', 'Medical conversations: I don\'t feel well. I have a headache/fever/cough. Where does it hurt? Take this medicine twice a day. You need to rest. Make an appointment.'],
            [12, 'Job Interview Conversations', 'Interview phrases: Tell me about yourself. Why do you want this job? What are your strengths? Do you have any questions? When can you start? Thank you for your time.'],
            [13, 'Travel & Hotel Conversations', 'Travel phrases: I\'d like to book a room. Do you have any vacancies? What time is check-in/check-out? Where is the nearest airport? Can you call a taxi?'],
            [14, 'Phone Conversations', 'Phone phrases: Hello, this is... Can I speak to...? Hold on, please. Can you repeat that? I\'ll call you back. Sorry, wrong number. The line is busy.'],
            [15, 'Complaining & Apologizing', 'Complaints: I\'m not satisfied with... This doesn\'t work properly. I\'d like to return this. Apologies: I\'m sorry for the inconvenience. I apologize for being late.'],
            [16, 'Expressing Opinions', 'Opinion phrases: I think..., In my opinion..., I believe..., I agree/disagree with you. That\'s a good point. I see what you mean. I\'m not sure about that.'],
            [17, 'Making Suggestions', 'Suggestions: Why don\'t we...? How about...? Let\'s... You should... If I were you, I would... That sounds like a good idea. I\'d rather not.'],
            [18, 'Talking About Past Experiences', 'Past experiences: I went to... last year. Have you ever been to...? I\'ve never tried... It was amazing/terrible. I had a great time. I\'ll never forget...'],
            [19, 'Making Plans & Invitations', 'Planning: What are you doing this weekend? Would you like to...? I\'m planning to... Let\'s go together. I can\'t make it. Maybe another time.'],
            [20, 'Small Talk & Social Situations', 'Social phrases: How\'s work? How was your weekend? Did you watch the game? Nice weather, isn\'t it? Have you heard about...? That reminds me of...'],
            
            // Advanced Conversations (21-25)
            [21, 'Business Meetings', 'Meeting phrases: Let\'s get started. The purpose of this meeting is... What do you think about...? I\'d like to propose... Let\'s move on to the next item. Any questions?'],
            [22, 'Negotiations & Discussions', 'Negotiation: What\'s your best price? Can you do better than that? I\'m willing to compromise. That\'s not acceptable. Let\'s find a middle ground. Deal!'],
            [23, 'Presentations & Public Speaking', 'Presentation phrases: Today I\'m going to talk about... First, let me explain... As you can see... To summarize... Are there any questions? Thank you for your attention.'],
            [24, 'Debates & Arguments', 'Debate language: I strongly believe... On the contrary... That\'s not necessarily true. Let me give you an example... I see your point, but... We\'ll have to agree to disagree.'],
            [25, 'Cultural Discussions', 'Cultural topics: In my country... That\'s interesting. How do you celebrate...? What\'s the tradition? I\'ve learned something new. Cultural differences are fascinating.']
        ];
        
        foreach ($conversationLessons as $lesson) {
            $stmt->bind_param("issi", $topics['Everyday Conversations'], $lesson[1], $lesson[2], $lesson[0]);
            if ($stmt->execute() && $conn->affected_rows > 0) {
                echo "  ✅ Added: {$lesson[1]}\n";
                $totalLessonsAdded++;
            }
        }
    }
    
    echo "\n🔍 Final verification...\n";
    
    // Count lessons per topic
    $result = $conn->query("
        SELECT t.name, COUNT(tl.id) as lesson_count 
        FROM topics t 
        LEFT JOIN topic_lessons tl ON t.id = tl.topic_id 
        GROUP BY t.id, t.name 
        ORDER BY t.name
    ");
    
    echo "📊 Lesson Summary:\n";
    $totalLessons = 0;
    while ($row = $result->fetch_assoc()) {
        echo "  📚 {$row['name']}: {$row['lesson_count']} lessons\n";
        $totalLessons += $row['lesson_count'];
    }
    
    echo "\n🎉 SUCCESS! Created comprehensive lesson curriculum.\n\n";
    echo "📈 Results:\n";
    echo "  ✅ Total lessons added: $totalLessonsAdded\n";
    echo "  ✅ Total lessons in system: $totalLessons\n\n";
    
    echo "🎯 Lesson Structure:\n";
    echo "  📚 Basic Vocabulary: 25 lessons (Beginner→Advanced)\n";
    echo "  📝 Grammar Fundamentals: 25 lessons (Basic→Advanced)\n";
    echo "  💬 Everyday Conversations: 25 lessons (Simple→Complex)\n";
    echo "  🎧 Other topics: Basic lessons\n\n";
    
    echo "🚀 Now you can:\n";
    echo "1. Go to topics.php\n";
    echo "2. Click 'Bắt đầu học' on any topic\n";
    echo "3. See comprehensive lessons from beginner to advanced\n";
    echo "4. Each lesson has detailed content and examples\n";
    echo "5. Progressive difficulty for effective learning\n";
    
} catch (Exception $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "\n";
}

$conn->close();
?>
