-- =====================================================
-- COMPLETE DATA INSERTION SCRIPT
-- =====================================================
-- This script inserts all sample data for the education app
-- Run this AFTER tables are created with robust_database_setup.php
-- Contains: Users, Dictionary, Topics, Lessons, Decks, Flashcards, Listening Exercises

USE eduapp;

-- =====================================================
-- 1. USERS DATA
-- =====================================================
INSERT IGNORE INTO users (username, email, password, full_name, major) VALUES 
('admin', 'admin@eduapp.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'Computer Science'),
('testuser', 'test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test User', 'English Literature'),
('student1', 'student1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Student One', 'Business'),
('student2', 'student2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Student Two', 'Engineering'),
('teacher1', 'teacher@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'English Teacher', 'Education');

-- =====================================================
-- 2. TOPICS AND LESSONS DATA
-- =====================================================
INSERT IGNORE INTO topics (name, description, color, icon, is_active) VALUES
('Basic Vocabulary', 'Learn essential English words for daily communication', '#4CAF50', 'fas fa-book', 1),
('Grammar Fundamentals', 'Master basic English grammar rules and structures', '#2196F3', 'fas fa-language', 1),
('Everyday Conversations', 'Practice common English phrases and expressions', '#FF9800', 'fas fa-comments', 1),
('Listening Skills', 'Improve your English listening comprehension', '#9C27B0', 'fas fa-headphones', 1),
('Reading Comprehension', 'Develop your English reading skills', '#F44336', 'fas fa-book-open', 1),
('Computer Science', 'Learn IT and computer terminology in English', '#607D8B', 'fas fa-laptop', 1);

-- Create lessons for Basic Vocabulary
INSERT IGNORE INTO topic_lessons (topic_id, title, content, lesson_order) VALUES
(1, 'Family Members', 'Learn words related to family relationships: father, mother, brother, sister, son, daughter, grandfather, grandmother, uncle, aunt, cousin, nephew, niece', 1),
(1, 'Colors and Shapes', 'Basic colors: red, blue, green, yellow, black, white, brown, pink, purple, orange. Shapes: circle, square, triangle, rectangle, oval', 2),
(1, 'Food and Drinks', 'Common food items: bread, rice, meat, fish, vegetables, fruits, milk, water, coffee, tea, juice', 3),
(1, 'Animals', 'Domestic animals: dog, cat, bird, fish. Wild animals: lion, tiger, elephant, monkey, bear', 4),
(1, 'Body Parts', 'Parts of the human body: head, face, eye, nose, mouth, ear, hand, arm, leg, foot', 5),
(1, 'House and Home', 'Rooms: bedroom, kitchen, bathroom, living room. Furniture: table, chair, bed, sofa, desk', 6),
(1, 'Transportation', 'Vehicles: car, bus, train, plane, bicycle, motorcycle, boat, ship', 7),
(1, 'Weather', 'Weather conditions: sunny, rainy, cloudy, windy, hot, cold, warm, cool, snow, storm', 8);

-- Create lessons for Grammar Fundamentals  
INSERT IGNORE INTO topic_lessons (topic_id, title, content, lesson_order) VALUES
(2, 'Present Simple Tense', 'Learn to use present simple tense: I work, You work, He/She works, We work, They work. Used for habits and facts.', 1),
(2, 'Articles (a, an, the)', 'When to use articles: "a" before consonant sounds, "an" before vowel sounds, "the" for specific things', 2),
(2, 'Plural Forms', 'Regular plurals: add -s (book→books). Irregular plurals: child→children, man→men, woman→women', 3),
(2, 'Past Simple Tense', 'Regular verbs: add -ed (work→worked). Irregular verbs: go→went, see→saw, have→had', 4),
(2, 'Question Formation', 'Yes/No questions: Do you...? Does he...? Did they...? Wh-questions: What, Where, When, Why, How', 5),
(2, 'Present Continuous', 'Form: am/is/are + verb-ing. Used for actions happening now: I am studying English', 6);

-- Create lessons for Everyday Conversations
INSERT IGNORE INTO topic_lessons (topic_id, title, content, lesson_order) VALUES
(3, 'Greetings and Introductions', 'Hello, Hi, Good morning, Good afternoon, Good evening. My name is..., Nice to meet you', 1),
(3, 'Asking for Directions', 'Where is...? How can I get to...? Go straight, turn left/right, It is next to...', 2),
(3, 'Shopping', 'How much is this? Can I try this on? I would like to buy... Do you have...?', 3),
(3, 'At a Restaurant', 'I would like to order... Can I have the menu? The bill, please. What do you recommend?', 4),
(3, 'Making Appointments', 'Can we meet at...? What time is convenient? I am available on... Let us reschedule', 5);

-- Create lessons for Computer Science
INSERT IGNORE INTO topic_lessons (topic_id, title, content, lesson_order) VALUES
(6, 'Computer Basics', 'Learn basic computer terms: CPU, RAM, hard drive, software, hardware, operating system', 1),
(6, 'Internet and Networks', 'Internet terminology: website, browser, email, download, upload, Wi-Fi, router', 2),
(6, 'Programming Concepts', 'Basic programming terms: code, algorithm, variable, function, loop, condition', 3);

-- =====================================================
-- 3. COMPREHENSIVE DICTIONARY DATA
-- =====================================================

-- Basic Essential Words
INSERT IGNORE INTO dictionary (word, phonetic, vietnamese, english_definition, example, part_of_speech, difficulty) VALUES
-- Greetings and Basic Communication
('hello', '/həˈloʊ/', 'xin chào', 'Used as a greeting or to begin a phone conversation', 'Hello, how are you today?', 'interjection', 'beginner'),
('goodbye', '/ˌɡʊdˈbaɪ/', 'tạm biệt', 'Used to express good wishes when parting', 'Goodbye, see you tomorrow!', 'interjection', 'beginner'),
('thank you', '/ˈθæŋk juː/', 'cảm ơn', 'Used to express gratitude', 'Thank you for your help.', 'interjection', 'beginner'),
('please', '/pliːz/', 'xin vui lòng', 'Used to make a polite request', 'Please help me with this.', 'adverb', 'beginner'),
('sorry', '/ˈsɔːri/', 'xin lỗi', 'Used to express regret or apology', 'Sorry, I am late.', 'interjection', 'beginner'),
('excuse me', '/ɪkˈskjuːz miː/', 'xin lỗi (để xin phép)', 'Used to politely get attention', 'Excuse me, where is the bathroom?', 'interjection', 'beginner'),

-- Family Members
('family', '/ˈfæməli/', 'gia đình', 'A group of people related to each other', 'I love my family very much.', 'noun', 'beginner'),
('father', '/ˈfɑːðər/', 'bố, cha', 'A male parent', 'My father works in an office.', 'noun', 'beginner'),
('mother', '/ˈmʌðər/', 'mẹ', 'A female parent', 'My mother cooks delicious food.', 'noun', 'beginner'),
('brother', '/ˈbrʌðər/', 'anh trai, em trai', 'A male sibling', 'My brother is older than me.', 'noun', 'beginner'),
('sister', '/ˈsɪstər/', 'chị gái, em gái', 'A female sibling', 'My sister studies at university.', 'noun', 'beginner'),
('son', '/sʌn/', 'con trai', 'A male child', 'He has two sons.', 'noun', 'beginner'),
('daughter', '/ˈdɔːtər/', 'con gái', 'A female child', 'She has one daughter.', 'noun', 'beginner'),
('grandfather', '/ˈɡrænfɑːðər/', 'ông', 'Father of one parent', 'My grandfather tells great stories.', 'noun', 'beginner'),
('grandmother', '/ˈɡrænmʌðər/', 'bà', 'Mother of one parent', 'My grandmother makes the best cookies.', 'noun', 'beginner'),

-- Common Objects and Places
('house', '/haʊs/', 'nhà', 'A building where people live', 'My house has three bedrooms.', 'noun', 'beginner'),
('school', '/skuːl/', 'trường học', 'A place where children learn', 'I go to school every day.', 'noun', 'beginner'),
('book', '/bʊk/', 'sách', 'A set of printed pages bound together', 'This is a very interesting book.', 'noun', 'beginner'),
('car', '/kɑːr/', 'xe hơi', 'A motor vehicle with four wheels', 'My car is red.', 'noun', 'beginner'),
('computer', '/kəmˈpjuːtər/', 'máy tính', 'An electronic device for processing data', 'I use my computer for work.', 'noun', 'beginner'),
('phone', '/foʊn/', 'điện thoại', 'A device used for communication', 'My phone is ringing.', 'noun', 'beginner'),
('table', '/ˈteɪbəl/', 'bàn', 'A piece of furniture with a flat top', 'Put the book on the table.', 'noun', 'beginner'),
('chair', '/tʃer/', 'ghế', 'A seat for one person', 'Please sit on this chair.', 'noun', 'beginner'),

-- Food and Drinks
('food', '/fuːd/', 'thức ăn', 'Things that people eat', 'This food is very delicious.', 'noun', 'beginner'),
('water', '/ˈwɔːtər/', 'nước', 'A clear liquid essential for life', 'I drink eight glasses of water daily.', 'noun', 'beginner'),
('bread', '/bred/', 'bánh mì', 'A food made from flour and water', 'I eat bread for breakfast.', 'noun', 'beginner'),
('rice', '/raɪs/', 'cơm, gạo', 'A cereal grain used as food', 'Rice is a staple food in Asia.', 'noun', 'beginner'),
('meat', '/miːt/', 'thịt', 'Animal flesh used as food', 'I do not eat meat.', 'noun', 'beginner'),
('fish', '/fɪʃ/', 'cá', 'An aquatic animal', 'Fish is good for your health.', 'noun', 'beginner'),
('milk', '/mɪlk/', 'sữa', 'A white liquid from mammals', 'Children need to drink milk.', 'noun', 'beginner'),
('coffee', '/ˈkɔːfi/', 'cà phê', 'A hot drink made from coffee beans', 'I drink coffee every morning.', 'noun', 'beginner'),
('tea', '/tiː/', 'trà', 'A hot drink made from tea leaves', 'Would you like some tea?', 'noun', 'beginner'),

-- Common Verbs
('be', '/biː/', 'là, thì, ở', 'To exist or have identity', 'I am a student.', 'verb', 'beginner'),
('have', '/hæv/', 'có', 'To possess or own', 'I have a new car.', 'verb', 'beginner'),
('do', '/duː/', 'làm', 'To perform an action', 'What do you do for work?', 'verb', 'beginner'),
('go', '/ɡoʊ/', 'đi', 'To move from one place to another', 'I go to work by bus.', 'verb', 'beginner'),
('come', '/kʌm/', 'đến', 'To move toward the speaker', 'Please come here.', 'verb', 'beginner'),
('see', '/siː/', 'nhìn thấy', 'To perceive with the eyes', 'I can see the mountains.', 'verb', 'beginner'),
('know', '/noʊ/', 'biết', 'To have information about', 'I know the answer.', 'verb', 'beginner'),
('think', '/θɪŋk/', 'nghĩ', 'To use one mind', 'I think it will rain today.', 'verb', 'beginner'),
('say', '/seɪ/', 'nói', 'To speak words', 'What did you say?', 'verb', 'beginner'),
('get', '/ɡet/', 'lấy, nhận', 'To obtain or receive', 'I need to get some milk.', 'verb', 'beginner'),
('make', '/meɪk/', 'làm, tạo', 'To create or produce', 'I will make dinner tonight.', 'verb', 'beginner'),
('take', '/teɪk/', 'lấy, mang', 'To carry or remove', 'Take this book with you.', 'verb', 'beginner'),
('want', '/wɑːnt/', 'muốn', 'To desire', 'I want to learn English.', 'verb', 'beginner'),
('like', '/laɪk/', 'thích', 'To enjoy or prefer', 'I like chocolate ice cream.', 'verb', 'beginner'),
('love', '/lʌv/', 'yêu', 'To have strong affection for', 'I love my family.', 'verb', 'beginner'),
('eat', '/iːt/', 'ăn', 'To consume food', 'I eat breakfast at 7 AM.', 'verb', 'beginner'),
('drink', '/drɪŋk/', 'uống', 'To consume liquid', 'I drink coffee every morning.', 'verb', 'beginner'),
('work', '/wɜːrk/', 'làm việc', 'To do a job', 'I work in an office.', 'verb', 'beginner'),
('study', '/ˈstʌdi/', 'học', 'To learn about something', 'I study English every day.', 'verb', 'beginner'),
('live', '/lɪv/', 'sống', 'To be alive or reside', 'I live in New York.', 'verb', 'beginner'),
('play', '/pleɪ/', 'chơi', 'To engage in games or sports', 'Children love to play.', 'verb', 'beginner'),

-- Common Adjectives
('good', '/ɡʊd/', 'tốt', 'Of high quality or standard', 'This is a good book.', 'adjective', 'beginner'),
('bad', '/bæd/', 'xấu, tệ', 'Of poor quality', 'The weather is bad today.', 'adjective', 'beginner'),
('big', '/bɪɡ/', 'to, lớn', 'Of considerable size', 'That is a big house.', 'adjective', 'beginner'),
('small', '/smɔːl/', 'nhỏ', 'Of limited size', 'I have a small car.', 'adjective', 'beginner'),
('new', '/nuː/', 'mới', 'Recently made or created', 'I bought a new phone.', 'adjective', 'beginner'),
('old', '/oʊld/', 'cũ, già', 'Having existed for a long time', 'My grandfather is very old.', 'adjective', 'beginner'),
('beautiful', '/ˈbjuːtɪfəl/', 'đẹp', 'Pleasing to look at', 'She is very beautiful.', 'adjective', 'beginner'),
('happy', '/ˈhæpi/', 'vui vẻ', 'Feeling pleasure or joy', 'I am happy today.', 'adjective', 'beginner'),
('sad', '/sæd/', 'buồn', 'Feeling sorrow', 'She looks sad.', 'adjective', 'beginner'),
('hot', '/hɑːt/', 'nóng', 'Having high temperature', 'It is very hot today.', 'adjective', 'beginner'),
('cold', '/koʊld/', 'lạnh', 'Having low temperature', 'The water is too cold.', 'adjective', 'beginner'),
('easy', '/ˈiːzi/', 'dễ', 'Not difficult', 'This test is easy.', 'adjective', 'beginner'),
('hard', '/hɑːrd/', 'khó, cứng', 'Difficult or solid', 'Math is hard for me.', 'adjective', 'beginner'),
('fast', '/fæst/', 'nhanh', 'Moving quickly', 'He runs very fast.', 'adjective', 'beginner'),
('slow', '/sloʊ/', 'chậm', 'Moving at low speed', 'The turtle is slow.', 'adjective', 'beginner'),

-- Time and Numbers
('time', '/taɪm/', 'thời gian', 'The indefinite continued progress of existence', 'What time is it?', 'noun', 'beginner'),
('day', '/deɪ/', 'ngày', 'A 24-hour period', 'Today is a beautiful day.', 'noun', 'beginner'),
('week', '/wiːk/', 'tuần', 'A period of seven days', 'I work five days a week.', 'noun', 'beginner'),
('month', '/mʌnθ/', 'tháng', 'One of twelve divisions of a year', 'January is the first month.', 'noun', 'beginner'),
('year', '/jɪr/', 'năm', 'A period of 365 days', 'I am twenty years old.', 'noun', 'beginner'),
('today', '/təˈdeɪ/', 'hôm nay', 'This present day', 'Today is Monday.', 'noun', 'beginner'),
('tomorrow', '/təˈmɔːroʊ/', 'ngày mai', 'The day after today', 'I will see you tomorrow.', 'noun', 'beginner'),
('yesterday', '/ˈjestərdeɪ/', 'hôm qua', 'The day before today', 'Yesterday was Sunday.', 'noun', 'beginner'),

-- Colors
('red', '/red/', 'đỏ', 'The color of blood', 'I like red roses.', 'adjective', 'beginner'),
('blue', '/bluː/', 'xanh dương', 'The color of the sky', 'The sky is blue.', 'adjective', 'beginner'),
('green', '/ɡriːn/', 'xanh lá', 'The color of grass', 'Trees have green leaves.', 'adjective', 'beginner'),
('yellow', '/ˈjeloʊ/', 'vàng', 'The color of the sun', 'Bananas are yellow.', 'adjective', 'beginner'),
('black', '/blæk/', 'đen', 'The darkest color', 'I wear black shoes.', 'adjective', 'beginner'),
('white', '/waɪt/', 'trắng', 'The lightest color', 'Snow is white.', 'adjective', 'beginner'),

-- Technology Terms
('internet', '/ˈɪntərnet/', 'mạng internet', 'Global computer network', 'I browse the internet daily.', 'noun', 'intermediate'),
('website', '/ˈwebsaɪt/', 'trang web', 'A location on the internet', 'This website is very useful.', 'noun', 'intermediate'),
('email', '/ˈiːmeɪl/', 'thư điện tử', 'Electronic mail', 'Please send me an email.', 'noun', 'intermediate'),
('software', '/ˈsɔːftwer/', 'phần mềm', 'Computer programs', 'This software is very helpful.', 'noun', 'intermediate'),
('hardware', '/ˈhɑːrdwer/', 'phần cứng', 'Physical computer components', 'The hardware needs upgrading.', 'noun', 'intermediate'),
('database', '/ˈdeɪtəbeɪs/', 'cơ sở dữ liệu', 'Organized collection of data', 'The database stores user information.', 'noun', 'intermediate'),
('programming', '/ˈproʊɡræmɪŋ/', 'lập trình', 'Writing computer code', 'I am learning programming.', 'noun', 'intermediate'),
('algorithm', '/ˈælɡərɪðəm/', 'thuật toán', 'Step-by-step procedure', 'This algorithm is efficient.', 'noun', 'advanced');

-- =====================================================
-- 4. FLASHCARD DECKS DATA
-- =====================================================

-- Get user IDs for deck creation
SET @admin_id = (SELECT id FROM users WHERE username = 'admin' LIMIT 1);
SET @test_user_id = (SELECT id FROM users WHERE username = 'testuser' LIMIT 1);
SET @teacher_id = (SELECT id FROM users WHERE username = 'teacher1' LIMIT 1);

-- Create comprehensive flashcard decks
INSERT IGNORE INTO decks (user_id, name, description, visibility) VALUES
(@admin_id, 'Essential Vocabulary', 'Most important English words for beginners', 'public'),
(@admin_id, 'Common Verbs', 'Frequently used English verbs with examples', 'public'),
(@admin_id, 'Descriptive Adjectives', 'Adjectives to describe people, places, and things', 'public'),
(@admin_id, 'Family & Relationships', 'Words related to family members and relationships', 'public'),
(@admin_id, 'Food & Drinks', 'Vocabulary for food, beverages, and dining', 'public'),
(@admin_id, 'Technology Terms', 'Computer and internet terminology', 'public'),
(@admin_id, 'Colors & Shapes', 'Basic colors and geometric shapes', 'public'),
(@admin_id, 'Time & Calendar', 'Time-related vocabulary and calendar terms', 'public'),
(@teacher_id, 'Grammar Basics', 'Essential grammar terms and concepts', 'public'),
(@teacher_id, 'Conversation Starters', 'Phrases for beginning conversations', 'public'),
(@test_user_id, 'My Personal Deck', 'Personal study collection', 'private'),
(@test_user_id, 'Business English', 'Professional and business vocabulary', 'private');

-- =====================================================
-- 5. FLASHCARDS DATA
-- =====================================================

-- Get deck IDs for flashcard creation
SET @deck1_id = (SELECT id FROM decks WHERE name = 'Essential Vocabulary' AND user_id = @admin_id LIMIT 1);
SET @deck2_id = (SELECT id FROM decks WHERE name = 'Common Verbs' AND user_id = @admin_id LIMIT 1);
SET @deck3_id = (SELECT id FROM decks WHERE name = 'Descriptive Adjectives' AND user_id = @admin_id LIMIT 1);
SET @deck4_id = (SELECT id FROM decks WHERE name = 'Family & Relationships' AND user_id = @admin_id LIMIT 1);
SET @deck5_id = (SELECT id FROM decks WHERE name = 'Food & Drinks' AND user_id = @admin_id LIMIT 1);
SET @deck6_id = (SELECT id FROM decks WHERE name = 'Technology Terms' AND user_id = @admin_id LIMIT 1);
SET @deck7_id = (SELECT id FROM decks WHERE name = 'Colors & Shapes' AND user_id = @admin_id LIMIT 1);

-- Essential Vocabulary Deck
INSERT IGNORE INTO flashcards (deck_id, word, definition, example, source_dictionary_id) VALUES
(@deck1_id, 'hello', 'xin chào', 'Hello, how are you today?', (SELECT id FROM dictionary WHERE word = 'hello')),
(@deck1_id, 'thank you', 'cảm ơn', 'Thank you for your help.', (SELECT id FROM dictionary WHERE word = 'thank you')),
(@deck1_id, 'please', 'xin vui lòng', 'Please help me with this.', (SELECT id FROM dictionary WHERE word = 'please')),
(@deck1_id, 'sorry', 'xin lỗi', 'Sorry, I am late.', (SELECT id FROM dictionary WHERE word = 'sorry')),
(@deck1_id, 'house', 'nhà', 'My house has three bedrooms.', (SELECT id FROM dictionary WHERE word = 'house')),
(@deck1_id, 'school', 'trường học', 'I go to school every day.', (SELECT id FROM dictionary WHERE word = 'school')),
(@deck1_id, 'book', 'sách', 'This is a very interesting book.', (SELECT id FROM dictionary WHERE word = 'book')),
(@deck1_id, 'water', 'nước', 'I drink eight glasses of water daily.', (SELECT id FROM dictionary WHERE word = 'water')),
(@deck1_id, 'food', 'thức ăn', 'This food is very delicious.', (SELECT id FROM dictionary WHERE word = 'food')),
(@deck1_id, 'time', 'thời gian', 'What time is it?', (SELECT id FROM dictionary WHERE word = 'time'));

-- Common Verbs Deck
INSERT IGNORE INTO flashcards (deck_id, word, definition, example, source_dictionary_id) VALUES
(@deck2_id, 'be', 'là, thì, ở', 'I am a student.', (SELECT id FROM dictionary WHERE word = 'be')),
(@deck2_id, 'have', 'có', 'I have a new car.', (SELECT id FROM dictionary WHERE word = 'have')),
(@deck2_id, 'do', 'làm', 'What do you do for work?', (SELECT id FROM dictionary WHERE word = 'do')),
(@deck2_id, 'go', 'đi', 'I go to work by bus.', (SELECT id FROM dictionary WHERE word = 'go')),
(@deck2_id, 'come', 'đến', 'Please come here.', (SELECT id FROM dictionary WHERE word = 'come')),
(@deck2_id, 'see', 'nhìn thấy', 'I can see the mountains.', (SELECT id FROM dictionary WHERE word = 'see')),
(@deck2_id, 'know', 'biết', 'I know the answer.', (SELECT id FROM dictionary WHERE word = 'know')),
(@deck2_id, 'think', 'nghĩ', 'I think it will rain today.', (SELECT id FROM dictionary WHERE word = 'think')),
(@deck2_id, 'make', 'làm, tạo', 'I will make dinner tonight.', (SELECT id FROM dictionary WHERE word = 'make')),
(@deck2_id, 'take', 'lấy, mang', 'Take this book with you.', (SELECT id FROM dictionary WHERE word = 'take'));

-- Descriptive Adjectives Deck
INSERT IGNORE INTO flashcards (deck_id, word, definition, example, source_dictionary_id) VALUES
(@deck3_id, 'good', 'tốt', 'This is a good book.', (SELECT id FROM dictionary WHERE word = 'good')),
(@deck3_id, 'bad', 'xấu, tệ', 'The weather is bad today.', (SELECT id FROM dictionary WHERE word = 'bad')),
(@deck3_id, 'big', 'to, lớn', 'That is a big house.', (SELECT id FROM dictionary WHERE word = 'big')),
(@deck3_id, 'small', 'nhỏ', 'I have a small car.', (SELECT id FROM dictionary WHERE word = 'small')),
(@deck3_id, 'beautiful', 'đẹp', 'She is very beautiful.', (SELECT id FROM dictionary WHERE word = 'beautiful')),
(@deck3_id, 'happy', 'vui vẻ', 'I am happy today.', (SELECT id FROM dictionary WHERE word = 'happy')),
(@deck3_id, 'sad', 'buồn', 'She looks sad.', (SELECT id FROM dictionary WHERE word = 'sad')),
(@deck3_id, 'hot', 'nóng', 'It is very hot today.', (SELECT id FROM dictionary WHERE word = 'hot')),
(@deck3_id, 'cold', 'lạnh', 'The water is too cold.', (SELECT id FROM dictionary WHERE word = 'cold')),
(@deck3_id, 'easy', 'dễ', 'This test is easy.', (SELECT id FROM dictionary WHERE word = 'easy'));

-- Family & Relationships Deck
INSERT IGNORE INTO flashcards (deck_id, word, definition, example, source_dictionary_id) VALUES
(@deck4_id, 'family', 'gia đình', 'I love my family very much.', (SELECT id FROM dictionary WHERE word = 'family')),
(@deck4_id, 'father', 'bố, cha', 'My father works in an office.', (SELECT id FROM dictionary WHERE word = 'father')),
(@deck4_id, 'mother', 'mẹ', 'My mother cooks delicious food.', (SELECT id FROM dictionary WHERE word = 'mother')),
(@deck4_id, 'brother', 'anh trai, em trai', 'My brother is older than me.', (SELECT id FROM dictionary WHERE word = 'brother')),
(@deck4_id, 'sister', 'chị gái, em gái', 'My sister studies at university.', (SELECT id FROM dictionary WHERE word = 'sister')),
(@deck4_id, 'son', 'con trai', 'He has two sons.', (SELECT id FROM dictionary WHERE word = 'son')),
(@deck4_id, 'daughter', 'con gái', 'She has one daughter.', (SELECT id FROM dictionary WHERE word = 'daughter')),
(@deck4_id, 'grandfather', 'ông', 'My grandfather tells great stories.', (SELECT id FROM dictionary WHERE word = 'grandfather')),
(@deck4_id, 'grandmother', 'bà', 'My grandmother makes the best cookies.', (SELECT id FROM dictionary WHERE word = 'grandmother'));

-- Food & Drinks Deck
INSERT IGNORE INTO flashcards (deck_id, word, definition, example, source_dictionary_id) VALUES
(@deck5_id, 'bread', 'bánh mì', 'I eat bread for breakfast.', (SELECT id FROM dictionary WHERE word = 'bread')),
(@deck5_id, 'rice', 'cơm, gạo', 'Rice is a staple food in Asia.', (SELECT id FROM dictionary WHERE word = 'rice')),
(@deck5_id, 'meat', 'thịt', 'I do not eat meat.', (SELECT id FROM dictionary WHERE word = 'meat')),
(@deck5_id, 'fish', 'cá', 'Fish is good for your health.', (SELECT id FROM dictionary WHERE word = 'fish')),
(@deck5_id, 'milk', 'sữa', 'Children need to drink milk.', (SELECT id FROM dictionary WHERE word = 'milk')),
(@deck5_id, 'coffee', 'cà phê', 'I drink coffee every morning.', (SELECT id FROM dictionary WHERE word = 'coffee')),
(@deck5_id, 'tea', 'trà', 'Would you like some tea?', (SELECT id FROM dictionary WHERE word = 'tea'));

-- Technology Terms Deck
INSERT IGNORE INTO flashcards (deck_id, word, definition, example, source_dictionary_id) VALUES
(@deck6_id, 'computer', 'máy tính', 'I use my computer for work.', (SELECT id FROM dictionary WHERE word = 'computer')),
(@deck6_id, 'internet', 'mạng internet', 'I browse the internet daily.', (SELECT id FROM dictionary WHERE word = 'internet')),
(@deck6_id, 'website', 'trang web', 'This website is very useful.', (SELECT id FROM dictionary WHERE word = 'website')),
(@deck6_id, 'email', 'thư điện tử', 'Please send me an email.', (SELECT id FROM dictionary WHERE word = 'email')),
(@deck6_id, 'software', 'phần mềm', 'This software is very helpful.', (SELECT id FROM dictionary WHERE word = 'software')),
(@deck6_id, 'hardware', 'phần cứng', 'The hardware needs upgrading.', (SELECT id FROM dictionary WHERE word = 'hardware')),
(@deck6_id, 'database', 'cơ sở dữ liệu', 'The database stores user information.', (SELECT id FROM dictionary WHERE word = 'database')),
(@deck6_id, 'programming', 'lập trình', 'I am learning programming.', (SELECT id FROM dictionary WHERE word = 'programming'));

-- Colors & Shapes Deck
INSERT IGNORE INTO flashcards (deck_id, word, definition, example, source_dictionary_id) VALUES
(@deck7_id, 'red', 'đỏ', 'I like red roses.', (SELECT id FROM dictionary WHERE word = 'red')),
(@deck7_id, 'blue', 'xanh dương', 'The sky is blue.', (SELECT id FROM dictionary WHERE word = 'blue')),
(@deck7_id, 'green', 'xanh lá', 'Trees have green leaves.', (SELECT id FROM dictionary WHERE word = 'green')),
(@deck7_id, 'yellow', 'vàng', 'Bananas are yellow.', (SELECT id FROM dictionary WHERE word = 'yellow')),
(@deck7_id, 'black', 'đen', 'I wear black shoes.', (SELECT id FROM dictionary WHERE word = 'black')),
(@deck7_id, 'white', 'trắng', 'Snow is white.', (SELECT id FROM dictionary WHERE word = 'white'));

-- =====================================================
-- 6. LISTENING EXERCISES DATA
-- =====================================================

INSERT IGNORE INTO listening_exercises (title, question, audio_url, option_a, option_b, option_c, option_d, correct_answer, explanation, difficulty) VALUES
-- Basic Level Exercises
('Basic Greeting', 'Nghe đoạn hội thoại và chọn câu trả lời đúng: Người nói đang làm gì?', 'tts:Hello, how are you today? I am fine, thank you.', 'Chào hỏi và hỏi thăm sức khỏe', 'Hỏi đường', 'Mua sắm', 'Đặt món ăn', 'A', 'Đoạn hội thoại là lời chào hỏi cơ bản "Hello, how are you today? I am fine, thank you."', 'beginner'),

('Numbers Practice', 'Nghe và chọn số được đọc:', 'tts:Twenty five', '15', '25', '35', '45', 'B', 'Số được đọc là "twenty five" = 25', 'beginner'),

('Time Telling', 'Nghe và chọn thời gian được đọc:', 'tts:It is three thirty in the afternoon', '3:00 PM', '3:30 PM', '3:15 PM', '3:45 PM', 'B', 'Thời gian được đọc là "three thirty in the afternoon" = 3:30 PM', 'beginner'),

('Weather Description', 'Nghe và chọn thời tiết được mô tả:', 'tts:Today is sunny and warm. It is a beautiful day.', 'Mưa và lạnh', 'Có nắng và ấm', 'Có mây và mát', 'Có tuyết và lạnh', 'B', 'Thời tiết được mô tả là "sunny and warm" = có nắng và ấm', 'beginner'),

('Food Ordering', 'Nghe đoạn hội thoại và chọn món ăn được đặt:', 'tts:I would like a hamburger and a cup of coffee, please.', 'Pizza và nước ngọt', 'Hamburger và cà phê', 'Sandwich và trà', 'Salad và nước', 'B', 'Món được đặt là "hamburger and a cup of coffee"', 'beginner'),

('Asking Directions', 'Nghe và chọn hướng dẫn đúng:', 'tts:Go straight, then turn left at the traffic light.', 'Đi thẳng rồi rẽ phải', 'Đi thẳng rồi rẽ trái tại đèn giao thông', 'Rẽ trái rồi đi thẳng', 'Rẽ phải tại ngã tư', 'B', 'Hướng dẫn: "Go straight, then turn left at the traffic light"', 'beginner'),

('Shopping Prices', 'Nghe đoạn hội thoại mua sắm và chọn giá tiền:', 'tts:The book costs fifteen dollars and the pen costs three dollars.', '$15 và $3', '$50 và $13', '$15 và $30', '$5 và $3', 'A', 'Sách giá fifteen dollars ($15) và bút giá three dollars ($3)', 'beginner'),

('School Subjects', 'Nghe và chọn môn học được nhắc đến:', 'tts:I have math class at nine and English class at ten.', 'Toán và Khoa học', 'Toán và Tiếng Anh', 'Lịch sử và Tiếng Anh', 'Toán và Thể dục', 'B', 'Các môn học: math (toán) và English (tiếng Anh)', 'beginner'),

('Family Members', 'Nghe và chọn thành viên gia đình được nhắc đến:', 'tts:My father is a doctor and my mother is a teacher.', 'Bố là bác sĩ, mẹ là y tá', 'Bố là giáo viên, mẹ là bác sĩ', 'Bố là bác sĩ, mẹ là giáo viên', 'Bố là kỹ sư, mẹ là giáo viên', 'C', 'Father is a doctor (bố là bác sĩ) và mother is a teacher (mẹ là giáo viên)', 'beginner'),

('Color Description', 'Nghe và chọn màu sắc được mô tả:', 'tts:The car is red and the house is blue.', 'Xe đỏ, nhà xanh lá', 'Xe xanh, nhà đỏ', 'Xe đỏ, nhà xanh dương', 'Xe vàng, nhà đỏ', 'C', 'Car is red (xe đỏ) và house is blue (nhà xanh dương)', 'beginner'),

-- Intermediate Level Exercises
('Daily Routine', 'Nghe và chọn hoạt động được mô tả:', 'tts:I wake up at seven, have breakfast at eight, and go to work at nine.', 'Dậy 6h, ăn sáng 7h, đi làm 8h', 'Dậy 7h, ăn sáng 8h, đi làm 9h', 'Dậy 8h, ăn sáng 9h, đi làm 10h', 'Dậy 7h, ăn sáng 9h, đi làm 8h', 'B', 'Wake up at seven (dậy 7h), breakfast at eight (ăn sáng 8h), work at nine (đi làm 9h)', 'intermediate'),

('Transportation', 'Nghe và chọn phương tiện giao thông:', 'tts:I usually take the bus to work, but today I am driving my car.', 'Thường đi xe buýt, hôm nay đi bộ', 'Thường đi xe buýt, hôm nay lái xe', 'Thường lái xe, hôm nay đi xe buýt', 'Thường đi xe đạp, hôm nay lái xe', 'B', 'Usually take the bus (thường đi xe buýt), today driving car (hôm nay lái xe)', 'intermediate'),

('Weekend Plans', 'Nghe và chọn kế hoạch cuối tuần:', 'tts:This weekend I will visit my grandmother and go shopping with my sister.', 'Thăm bà và đi mua sắm với chị', 'Thăm ông và đi xem phim với em', 'Thăm bà và đi xem phim với chị', 'Thăm ông và đi mua sắm với em', 'A', 'Visit grandmother (thăm bà) và go shopping with sister (đi mua sắm với chị)', 'intermediate'),

('Job Interview', 'Nghe đoạn phỏng vấn và chọn thông tin đúng:', 'tts:I have five years of experience in marketing and I speak three languages fluently.', '3 năm kinh nghiệm, nói 5 ngôn ngữ', '5 năm kinh nghiệm, nói 3 ngôn ngữ', '5 năm kinh nghiệm, nói 2 ngôn ngữ', '3 năm kinh nghiệm, nói 3 ngôn ngữ', 'B', 'Five years experience (5 năm kinh nghiệm) và three languages (3 ngôn ngữ)', 'intermediate'),

('Restaurant Reservation', 'Nghe cuộc gọi đặt bàn và chọn thông tin đúng:', 'tts:I would like to reserve a table for four people at seven PM tomorrow.', 'Bàn cho 3 người, 6h tối mai', 'Bàn cho 4 người, 7h tối mai', 'Bàn cho 4 người, 8h tối mai', 'Bàn cho 2 người, 7h tối mai', 'B', 'Table for four people (bàn cho 4 người) at seven PM tomorrow (7h tối mai)', 'intermediate');

-- =====================================================
-- 7. ADDITIONAL SAMPLE DATA
-- =====================================================

-- Create some sample learning stats
INSERT IGNORE INTO learning_stats (user_id, words_learned, correct_answers, total_answers, streak_days, last_study_date) VALUES
(@test_user_id, 25, 45, 60, 5, CURDATE()),
((SELECT id FROM users WHERE username = 'student1'), 15, 30, 40, 3, CURDATE() - INTERVAL 1 DAY),
((SELECT id FROM users WHERE username = 'student2'), 35, 70, 85, 7, CURDATE());

-- Create some sample daily stats
INSERT IGNORE INTO daily_stats (user_id, stat_date, exercises_completed, correct_answers, total_answers, study_time_minutes, points_earned) VALUES
(@test_user_id, CURDATE(), 5, 8, 10, 30, 80),
(@test_user_id, CURDATE() - INTERVAL 1 DAY, 3, 5, 7, 20, 50),
((SELECT id FROM users WHERE username = 'student1'), CURDATE(), 4, 6, 8, 25, 60),
((SELECT id FROM users WHERE username = 'student2'), CURDATE(), 6, 10, 12, 40, 100);

-- =====================================================
-- 8. FINAL VERIFICATION AND SUMMARY
-- =====================================================

-- Show comprehensive data summary
SELECT '🎉 COMPLETE DATA INSERTION SUCCESSFUL!' as Status;

SELECT 'DATA SUMMARY:' as Category, '' as Count
UNION ALL
SELECT 'Users', COUNT(*) FROM users
UNION ALL
SELECT 'Dictionary Words', COUNT(*) FROM dictionary
UNION ALL
SELECT 'Topics', COUNT(*) FROM topics
UNION ALL
SELECT 'Topic Lessons', COUNT(*) FROM topic_lessons
UNION ALL
SELECT 'Flashcard Decks', COUNT(*) FROM decks
UNION ALL
SELECT 'Flashcards', COUNT(*) FROM flashcards
UNION ALL
SELECT 'Listening Exercises', COUNT(*) FROM listening_exercises
UNION ALL
SELECT 'Learning Stats', COUNT(*) FROM learning_stats
UNION ALL
SELECT 'Daily Stats', COUNT(*) FROM daily_stats;

-- Show deck breakdown
SELECT 'FLASHCARD DECKS:' as Deck_Name, '' as Card_Count
UNION ALL
SELECT d.name, COUNT(f.id)
FROM decks d
LEFT JOIN flashcards f ON d.id = f.deck_id
GROUP BY d.id, d.name
ORDER BY Deck_Name DESC, Card_Count DESC;

-- Show user breakdown
SELECT 'USER ACCOUNTS:' as Username, '' as Details
UNION ALL
SELECT u.username, CONCAT('Email: ', u.email, ', Decks: ', COUNT(d.id))
FROM users u
LEFT JOIN decks d ON u.id = d.user_id
GROUP BY u.id, u.username, u.email
ORDER BY Username DESC, Details;

SELECT '✅ Ready to use! Next steps:' as Message
UNION ALL
SELECT '1. Login with: testuser / password' as Message
UNION ALL
SELECT '2. Visit flashcards.php to study' as Message
UNION ALL
SELECT '3. Visit stats.php for statistics' as Message
UNION ALL
SELECT '4. Visit listening.php for exercises' as Message
UNION ALL
SELECT '5. Visit topics.php for lessons' as Message
UNION ALL
SELECT '6. All data synchronization is working!' as Message;
