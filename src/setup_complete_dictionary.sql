-- =====================================================
-- COMPLETE ENGLISH DICTIONARY SETUP
-- =====================================================
-- This script sets up a comprehensive English-Vietnamese dictionary
-- for the text extraction feature in the flashcards system

-- Create all necessary tables for the flashcard system

-- Create users table if not exists
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create flashcard_decks table if not exists
CREATE TABLE IF NOT EXISTS flashcard_decks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
);

-- Create flashcards table if not exists
CREATE TABLE IF NOT EXISTS flashcards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    deck_id INT NOT NULL,
    front TEXT NOT NULL,
    back TEXT NOT NULL,
    example TEXT,
    difficulty ENUM('easy', 'medium', 'hard') DEFAULT 'medium',
    next_review DATE,
    review_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (deck_id) REFERENCES flashcard_decks(id) ON DELETE CASCADE,
    INDEX idx_deck_id (deck_id),
    INDEX idx_next_review (next_review)
);

-- Create dictionary table if not exists
CREATE TABLE IF NOT EXISTS dictionary (
    id INT AUTO_INCREMENT PRIMARY KEY,
    word VARCHAR(255) NOT NULL UNIQUE,
    vietnamese TEXT,
    english_definition TEXT,
    example TEXT,
    part_of_speech VARCHAR(50) DEFAULT 'noun',
    difficulty ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_word (word),
    INDEX idx_difficulty (difficulty),
    INDEX idx_part_of_speech (part_of_speech)
);

-- Clear existing data (optional - remove this line if you want to keep existing words)
-- DELETE FROM dictionary;

-- ESSENTIAL VOCABULARY FOR TEXT EXTRACTION
INSERT IGNORE INTO dictionary (word, vietnamese, english_definition, part_of_speech, difficulty) VALUES

-- PRONOUNS & QUESTION WORDS
('I', 'tôi', 'first person singular pronoun', 'pronoun', 'beginner'),
('you', 'bạn', 'second person pronoun', 'pronoun', 'beginner'),
('he', 'anh ấy', 'third person masculine pronoun', 'pronoun', 'beginner'),
('she', 'cô ấy', 'third person feminine pronoun', 'pronoun', 'beginner'),
('it', 'nó', 'third person neuter pronoun', 'pronoun', 'beginner'),
('we', 'chúng tôi', 'first person plural pronoun', 'pronoun', 'beginner'),
('they', 'họ', 'third person plural pronoun', 'pronoun', 'beginner'),
('what', 'cái gì', 'interrogative pronoun', 'pronoun', 'beginner'),
('who', 'ai', 'interrogative pronoun for people', 'pronoun', 'beginner'),
('where', 'ở đâu', 'interrogative adverb for place', 'adverb', 'beginner'),
('when', 'khi nào', 'interrogative adverb for time', 'adverb', 'beginner'),
('why', 'tại sao', 'interrogative adverb for reason', 'adverb', 'beginner'),
('how', 'như thế nào', 'interrogative adverb for manner', 'adverb', 'beginner'),

-- ESSENTIAL VERBS
('am', 'là', 'first person singular of be', 'verb', 'beginner'),
('is', 'là', 'third person singular of be', 'verb', 'beginner'),
('are', 'là', 'plural form of be', 'verb', 'beginner'),
('was', 'đã là', 'past tense of be', 'verb', 'beginner'),
('were', 'đã là', 'past tense of be (plural)', 'verb', 'beginner'),
('have', 'có', 'to possess', 'verb', 'beginner'),
('has', 'có', 'third person singular of have', 'verb', 'beginner'),
('had', 'đã có', 'past tense of have', 'verb', 'beginner'),
('do', 'làm', 'to perform an action', 'verb', 'beginner'),
('does', 'làm', 'third person singular of do', 'verb', 'beginner'),
('did', 'đã làm', 'past tense of do', 'verb', 'beginner'),
('will', 'sẽ', 'future auxiliary verb', 'verb', 'beginner'),
('would', 'sẽ', 'conditional auxiliary verb', 'verb', 'beginner'),
('can', 'có thể', 'to be able to', 'verb', 'beginner'),
('could', 'có thể', 'past tense of can', 'verb', 'beginner'),
('should', 'nên', 'ought to', 'verb', 'beginner'),
('must', 'phải', 'to be obliged to', 'verb', 'beginner'),

-- COMMON ACTION VERBS
('go', 'đi', 'to move from one place to another', 'verb', 'beginner'),
('come', 'đến', 'to move toward', 'verb', 'beginner'),
('see', 'nhìn', 'to perceive with eyes', 'verb', 'beginner'),
('look', 'nhìn', 'to direct eyes toward', 'verb', 'beginner'),
('hear', 'nghe', 'to perceive sound', 'verb', 'beginner'),
('listen', 'lắng nghe', 'to pay attention to sound', 'verb', 'beginner'),
('speak', 'nói', 'to say words', 'verb', 'beginner'),
('talk', 'nói chuyện', 'to communicate verbally', 'verb', 'beginner'),
('say', 'nói', 'to express in words', 'verb', 'beginner'),
('tell', 'kể', 'to give information', 'verb', 'beginner'),
('read', 'đọc', 'to look at and understand text', 'verb', 'beginner'),
('write', 'viết', 'to mark letters or words', 'verb', 'beginner'),
('eat', 'ăn', 'to consume food', 'verb', 'beginner'),
('drink', 'uống', 'to consume liquid', 'verb', 'beginner'),
('sleep', 'ngủ', 'to rest with eyes closed', 'verb', 'beginner'),
('walk', 'đi bộ', 'to move on foot', 'verb', 'beginner'),
('run', 'chạy', 'to move quickly on foot', 'verb', 'beginner'),
('play', 'chơi', 'to engage in activity for enjoyment', 'verb', 'beginner'),
('work', 'làm việc', 'to do job or task', 'verb', 'beginner'),
('study', 'học', 'to learn about something', 'verb', 'beginner'),
('learn', 'học', 'to acquire knowledge', 'verb', 'beginner'),
('teach', 'dạy', 'to give knowledge to others', 'verb', 'beginner'),
('help', 'giúp đỡ', 'to assist someone', 'verb', 'beginner'),
('love', 'yêu', 'to feel deep affection', 'verb', 'beginner'),
('like', 'thích', 'to find agreeable', 'verb', 'beginner'),
('want', 'muốn', 'to desire', 'verb', 'beginner'),
('need', 'cần', 'to require', 'verb', 'beginner'),
('know', 'biết', 'to be aware of', 'verb', 'beginner'),
('think', 'nghĩ', 'to use mind to consider', 'verb', 'beginner'),
('feel', 'cảm thấy', 'to experience emotion', 'verb', 'beginner'),
('make', 'làm', 'to create or produce', 'verb', 'beginner'),
('take', 'lấy', 'to get hold of', 'verb', 'beginner'),
('give', 'cho', 'to provide', 'verb', 'beginner'),
('get', 'lấy', 'to obtain', 'verb', 'beginner'),
('put', 'đặt', 'to place', 'verb', 'beginner'),
('find', 'tìm', 'to discover', 'verb', 'beginner'),
('buy', 'mua', 'to purchase', 'verb', 'beginner'),
('sell', 'bán', 'to exchange for money', 'verb', 'beginner'),
('live', 'sống', 'to be alive or reside', 'verb', 'beginner'),
('stay', 'ở lại', 'to remain', 'verb', 'beginner'),
('leave', 'rời đi', 'to depart', 'verb', 'beginner'),
('meet', 'gặp', 'to encounter', 'verb', 'beginner'),
('visit', 'thăm', 'to go to see', 'verb', 'beginner'),
('call', 'gọi', 'to telephone', 'verb', 'beginner'),
('ask', 'hỏi', 'to request information', 'verb', 'beginner'),
('answer', 'trả lời', 'to respond', 'verb', 'beginner'),
('open', 'mở', 'to make accessible', 'verb', 'beginner'),
('close', 'đóng', 'to shut', 'verb', 'beginner'),
('start', 'bắt đầu', 'to begin', 'verb', 'beginner'),
('stop', 'dừng', 'to cease', 'verb', 'beginner'),
('finish', 'hoàn thành', 'to complete', 'verb', 'beginner'),
('try', 'thử', 'to attempt', 'verb', 'beginner'),
('use', 'sử dụng', 'to employ for purpose', 'verb', 'beginner'),
('wait', 'đợi', 'to stay in expectation', 'verb', 'beginner'),
('travel', 'du lịch', 'to journey', 'verb', 'beginner'),
('drive', 'lái xe', 'to operate vehicle', 'verb', 'beginner'),
('cook', 'nấu ăn', 'to prepare food', 'verb', 'beginner'),
('clean', 'dọn dẹp', 'to make tidy', 'verb', 'beginner'),
('wash', 'rửa', 'to clean with water', 'verb', 'beginner'),
('wear', 'mặc', 'to have on body', 'verb', 'beginner'),
('carry', 'mang', 'to transport', 'verb', 'beginner'),
('hold', 'cầm', 'to grasp', 'verb', 'beginner'),
('send', 'gửi', 'to dispatch', 'verb', 'beginner'),
('receive', 'nhận', 'to get', 'verb', 'beginner'),
('pay', 'trả tiền', 'to give money', 'verb', 'beginner'),
('cost', 'có giá', 'to have price', 'verb', 'beginner'),
('save', 'tiết kiệm', 'to keep for later', 'verb', 'beginner'),
('spend', 'tiêu', 'to use money', 'verb', 'beginner'),
('win', 'thắng', 'to be victorious', 'verb', 'beginner'),
('lose', 'thua', 'to be defeated', 'verb', 'beginner'),
('choose', 'chọn', 'to select', 'verb', 'beginner'),
('decide', 'quyết định', 'to make choice', 'verb', 'beginner'),
('understand', 'hiểu', 'to comprehend', 'verb', 'beginner'),
('remember', 'nhớ', 'to recall', 'verb', 'beginner'),
('forget', 'quên', 'to fail to remember', 'verb', 'beginner'),
('hope', 'hy vọng', 'to wish for', 'verb', 'beginner'),
('worry', 'lo lắng', 'to feel anxious', 'verb', 'beginner'),
('smile', 'cười', 'to show happiness', 'verb', 'beginner'),
('laugh', 'cười', 'to express amusement', 'verb', 'beginner'),
('cry', 'khóc', 'to shed tears', 'verb', 'beginner'),

-- COMMON NOUNS - PEOPLE
('man', 'người đàn ông', 'adult male human', 'noun', 'beginner'),
('woman', 'người phụ nữ', 'adult female human', 'noun', 'beginner'),
('person', 'người', 'individual human being', 'noun', 'beginner'),
('people', 'mọi người', 'human beings in general', 'noun', 'beginner'),
('child', 'trẻ em', 'young human being', 'noun', 'beginner'),
('baby', 'em bé', 'very young child', 'noun', 'beginner'),
('boy', 'cậu bé', 'male child', 'noun', 'beginner'),
('girl', 'cô bé', 'female child', 'noun', 'beginner'),
('family', 'gia đình', 'group of related people', 'noun', 'beginner'),
('parent', 'cha mẹ', 'father or mother', 'noun', 'beginner'),
('father', 'cha', 'male parent', 'noun', 'beginner'),
('mother', 'mẹ', 'female parent', 'noun', 'beginner'),
('son', 'con trai', 'male offspring', 'noun', 'beginner'),
('daughter', 'con gái', 'female offspring', 'noun', 'beginner'),
('brother', 'anh/em trai', 'male sibling', 'noun', 'beginner'),
('sister', 'chị/em gái', 'female sibling', 'noun', 'beginner'),
('friend', 'bạn bè', 'person you like and know well', 'noun', 'beginner'),
('teacher', 'giáo viên', 'person who teaches', 'noun', 'beginner'),
('student', 'học sinh', 'person who studies', 'noun', 'beginner'),
('doctor', 'bác sĩ', 'medical professional', 'noun', 'beginner'),

-- COMMON NOUNS - PLACES
('home', 'nhà', 'place where you live', 'noun', 'beginner'),
('house', 'ngôi nhà', 'building for human habitation', 'noun', 'beginner'),
('room', 'phòng', 'space within building', 'noun', 'beginner'),
('school', 'trường học', 'institution for education', 'noun', 'beginner'),
('office', 'văn phòng', 'place of work', 'noun', 'beginner'),
('hospital', 'bệnh viện', 'place for medical care', 'noun', 'beginner'),
('store', 'cửa hàng', 'place for buying things', 'noun', 'beginner'),
('restaurant', 'nhà hàng', 'place for eating', 'noun', 'beginner'),
('city', 'thành phố', 'large town', 'noun', 'beginner'),
('country', 'đất nước', 'nation', 'noun', 'beginner'),
('world', 'thế giới', 'the earth and all people', 'noun', 'beginner'),

-- COMMON NOUNS - OBJECTS
('thing', 'vật', 'object or item', 'noun', 'beginner'),
('book', 'sách', 'written or printed work', 'noun', 'beginner'),
('car', 'xe hơi', 'road vehicle with engine', 'noun', 'beginner'),
('phone', 'điện thoại', 'device for communication', 'noun', 'beginner'),
('computer', 'máy tính', 'electronic device for processing data', 'noun', 'beginner'),
('money', 'tiền', 'medium of exchange', 'noun', 'beginner'),
('job', 'việc làm', 'paid position of employment', 'noun', 'beginner'),
('time', 'thời gian', 'indefinite continued progress of existence', 'noun', 'beginner'),
('day', 'ngày', 'period of 24 hours', 'noun', 'beginner'),
('night', 'đêm', 'time of darkness', 'noun', 'beginner'),
('week', 'tuần', 'period of seven days', 'noun', 'beginner'),
('month', 'tháng', 'period of about 30 days', 'noun', 'beginner'),
('year', 'năm', 'period of 365 days', 'noun', 'beginner'),

-- COMMON ADJECTIVES
('good', 'tốt', 'of high quality', 'adjective', 'beginner'),
('bad', 'xấu', 'of poor quality', 'adjective', 'beginner'),
('big', 'lớn', 'of considerable size', 'adjective', 'beginner'),
('small', 'nhỏ', 'of limited size', 'adjective', 'beginner'),
('new', 'mới', 'recently made or created', 'adjective', 'beginner'),
('old', 'cũ', 'having existed for long time', 'adjective', 'beginner'),
('young', 'trẻ', 'having lived for short time', 'adjective', 'beginner'),
('beautiful', 'đẹp', 'pleasing to look at', 'adjective', 'beginner'),
('happy', 'vui', 'feeling pleasure', 'adjective', 'beginner'),
('sad', 'buồn', 'feeling sorrow', 'adjective', 'beginner'),
('easy', 'dễ', 'not difficult', 'adjective', 'beginner'),
('hard', 'khó', 'difficult to do', 'adjective', 'beginner'),
('important', 'quan trọng', 'of great significance', 'adjective', 'beginner'),
('interesting', 'thú vị', 'arousing curiosity', 'adjective', 'beginner'),
('right', 'đúng', 'correct', 'adjective', 'beginner'),
('wrong', 'sai', 'incorrect', 'adjective', 'beginner'),

-- FOOD & BASIC ITEMS
('food', 'thức ăn', 'nutritious substance consumed', 'noun', 'beginner'),
('water', 'nước', 'colorless liquid essential for life', 'noun', 'beginner'),
('rice', 'cơm', 'staple food grain', 'noun', 'beginner'),
('bread', 'bánh mì', 'baked food made from flour', 'noun', 'beginner'),
('apple', 'táo', 'round red or green fruit', 'noun', 'beginner'),
('coffee', 'cà phê', 'drink made from coffee beans', 'noun', 'beginner'),
('tea', 'trà', 'drink made from tea leaves', 'noun', 'beginner'),

-- COLORS
('red', 'đỏ', 'color of blood', 'adjective', 'beginner'),
('blue', 'xanh dương', 'color of sky', 'adjective', 'beginner'),
('green', 'xanh lá', 'color of grass', 'adjective', 'beginner'),
('yellow', 'vàng', 'color of sun', 'adjective', 'beginner'),
('black', 'đen', 'darkest color', 'adjective', 'beginner'),
('white', 'trắng', 'lightest color', 'adjective', 'beginner'),

-- NUMBERS
('one', 'một', 'number 1', 'noun', 'beginner'),
('two', 'hai', 'number 2', 'noun', 'beginner'),
('three', 'ba', 'number 3', 'noun', 'beginner'),
('four', 'bốn', 'number 4', 'noun', 'beginner'),
('five', 'năm', 'number 5', 'noun', 'beginner'),
('ten', 'mười', 'number 10', 'noun', 'beginner'),

-- NATURE & ANIMALS
('sun', 'mặt trời', 'star that earth orbits', 'noun', 'beginner'),
('moon', 'mặt trăng', 'natural satellite of earth', 'noun', 'beginner'),
('tree', 'cây', 'woody perennial plant', 'noun', 'beginner'),
('cat', 'mèo', 'small domesticated carnivorous mammal', 'noun', 'beginner'),
('dog', 'chó', 'domesticated carnivorous mammal', 'noun', 'beginner'),
('bird', 'chim', 'feathered flying animal', 'noun', 'beginner'),
('fish', 'cá', 'aquatic animal', 'noun', 'beginner'),

-- COMMON NAMES (examples)
('Long', 'Long (tên)', 'Vietnamese given name', 'noun', 'beginner'),
('John', 'John (tên)', 'English given name', 'noun', 'beginner'),
('Mary', 'Mary (tên)', 'English given name', 'noun', 'beginner');

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_word_lower ON dictionary ((LOWER(word)));
CREATE INDEX IF NOT EXISTS idx_difficulty_word ON dictionary (difficulty, word);

-- Display results
SELECT 'Complete dictionary setup finished!' as Status,
       COUNT(*) as 'Total Words Added' FROM dictionary
UNION ALL
SELECT 'Breakdown by difficulty:', '' FROM dictionary LIMIT 1
UNION ALL
SELECT CONCAT(difficulty, ' words'), COUNT(*) FROM dictionary GROUP BY difficulty
UNION ALL
SELECT 'Breakdown by part of speech:', '' FROM dictionary LIMIT 1
UNION ALL
SELECT CONCAT(part_of_speech, ' words'), COUNT(*) FROM dictionary GROUP BY part_of_speech;
