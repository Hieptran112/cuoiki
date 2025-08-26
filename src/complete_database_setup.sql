-- =====================================================
-- COMPLETE DATABASE SETUP SCRIPT
-- =====================================================
-- This script creates all tables and inserts sample data
-- Run this script to set up the complete education app database

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS eduapp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE eduapp;

-- =====================================================
-- 1. DROP EXISTING TABLES (Clean Start)
-- =====================================================
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS study_progress;
DROP TABLE IF EXISTS flashcards;
DROP TABLE IF EXISTS decks;
DROP TABLE IF EXISTS flashcard_decks;
DROP TABLE IF EXISTS preset_decks;
DROP TABLE IF EXISTS specialized_terms;
DROP TABLE IF EXISTS learning_progress;
DROP TABLE IF EXISTS topic_exercise_results;
DROP TABLE IF EXISTS topic_progress;
DROP TABLE IF EXISTS topic_lessons;
DROP TABLE IF EXISTS topics;
DROP TABLE IF EXISTS listening_results;
DROP TABLE IF EXISTS listening_exercises;
DROP TABLE IF EXISTS exercise_results;
DROP TABLE IF EXISTS learning_stats;
DROP TABLE IF EXISTS user_word_review;
DROP TABLE IF EXISTS daily_stats;
DROP TABLE IF EXISTS dictionary;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- 2. CREATE CORE TABLES
-- =====================================================

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NULL,
    major VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
);

-- Dictionary table
CREATE TABLE dictionary (
    id INT AUTO_INCREMENT PRIMARY KEY,
    word VARCHAR(255) NOT NULL UNIQUE,
    phonetic VARCHAR(100),
    vietnamese TEXT,
    english_definition TEXT,
    example TEXT,
    part_of_speech ENUM('noun', 'verb', 'adjective', 'adverb', 'pronoun', 'preposition', 'conjunction', 'interjection') DEFAULT 'noun',
    difficulty ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_word (word),
    INDEX idx_difficulty (difficulty),
    INDEX idx_part_of_speech (part_of_speech)
);

-- Decks table (for flashcards)
CREATE TABLE decks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    visibility ENUM('private','public') DEFAULT 'private',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_name (user_id, name),
    INDEX idx_user_id (user_id),
    CONSTRAINT fk_decks_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Flashcards table
CREATE TABLE flashcards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    deck_id INT NOT NULL,
    word VARCHAR(255) NOT NULL,
    definition TEXT NOT NULL,
    example TEXT,
    image_url VARCHAR(500),
    audio_url VARCHAR(500),
    source_dictionary_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_deck_id (deck_id),
    INDEX idx_word (word),
    CONSTRAINT fk_flashcards_deck FOREIGN KEY (deck_id) REFERENCES decks(id) ON DELETE CASCADE,
    CONSTRAINT fk_flashcards_dictionary FOREIGN KEY (source_dictionary_id) REFERENCES dictionary(id) ON DELETE SET NULL
);

-- Study progress table (for spaced repetition)
CREATE TABLE study_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    flashcard_id INT NOT NULL,
    status ENUM('new','learning','review','mastered') DEFAULT 'new',
    ease_level ENUM('again','hard','good','easy') DEFAULT 'again',
    review_count INT DEFAULT 0,
    correct_count INT DEFAULT 0,
    incorrect_count INT DEFAULT 0,
    last_reviewed_at DATETIME NULL,
    next_due_at DATETIME NULL,
    sm2_ease_factor DECIMAL(4,2) DEFAULT 2.50,
    sm2_interval_days INT DEFAULT 0,
    sm2_repetitions INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_flashcard (user_id, flashcard_id),
    INDEX idx_user_next_due (user_id, next_due_at),
    CONSTRAINT fk_progress_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_progress_flashcard FOREIGN KEY (flashcard_id) REFERENCES flashcards(id) ON DELETE CASCADE
);

-- =====================================================
-- 3. LISTENING SYSTEM TABLES
-- =====================================================

-- Listening exercises table
CREATE TABLE listening_exercises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    question TEXT NOT NULL,
    audio_url VARCHAR(500) NOT NULL,
    option_a VARCHAR(255) NOT NULL,
    option_b VARCHAR(255) NOT NULL,
    option_c VARCHAR(255) NOT NULL,
    option_d VARCHAR(255) NOT NULL,
    correct_answer CHAR(1) NOT NULL,
    explanation TEXT,
    difficulty ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_difficulty (difficulty),
    INDEX idx_active (is_active)
);

-- Listening results table
CREATE TABLE listening_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    exercise_id INT NOT NULL,
    user_answer CHAR(1) NOT NULL,
    is_correct BOOLEAN NOT NULL,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_exercise_id (exercise_id),
    INDEX idx_completed_at (completed_at),
    CONSTRAINT fk_listening_results_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_listening_results_exercise FOREIGN KEY (exercise_id) REFERENCES listening_exercises(id) ON DELETE CASCADE
);

-- =====================================================
-- 4. TOPICS SYSTEM TABLES
-- =====================================================

-- Topics table
CREATE TABLE topics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    color VARCHAR(7) DEFAULT '#667eea',
    icon VARCHAR(50) DEFAULT 'fas fa-book',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_active (is_active)
);

-- Topic lessons table
CREATE TABLE topic_lessons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    topic_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    lesson_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_topic_order (topic_id, lesson_order),
    CONSTRAINT fk_lessons_topic FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE
);

-- Topic progress table
CREATE TABLE topic_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    topic_id INT NOT NULL,
    lesson_id INT NOT NULL,
    total_questions INT DEFAULT 15,
    correct_answers INT DEFAULT 0,
    completion_percentage DECIMAL(5,2) DEFAULT 0.00,
    is_completed BOOLEAN DEFAULT FALSE,
    last_attempt_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    UNIQUE KEY unique_user_lesson (user_id, lesson_id),
    INDEX idx_user_topic (user_id, topic_id),
    INDEX idx_completion (is_completed),
    CONSTRAINT fk_topic_progress_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_topic_progress_topic FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE,
    CONSTRAINT fk_topic_progress_lesson FOREIGN KEY (lesson_id) REFERENCES topic_lessons(id) ON DELETE CASCADE
);

-- Topic exercise results table
CREATE TABLE topic_exercise_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    lesson_id INT NOT NULL,
    question_text TEXT NOT NULL,
    user_answer TEXT,
    correct_answer TEXT,
    is_correct BOOLEAN NOT NULL,
    answered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_lesson (user_id, lesson_id),
    INDEX idx_answered_at (answered_at),
    CONSTRAINT fk_topic_results_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_topic_results_lesson FOREIGN KEY (lesson_id) REFERENCES topic_lessons(id) ON DELETE CASCADE
);

-- =====================================================
-- 5. ADDITIONAL SYSTEM TABLES
-- =====================================================

-- User word review table (for dictionary)
CREATE TABLE user_word_review (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    dictionary_id INT NOT NULL,
    correct_count INT DEFAULT 0,
    wrong_count INT DEFAULT 0,
    last_correct_date DATE NULL,
    last_wrong_date DATE NULL,
    next_review_date DATE NULL,
    difficulty ENUM('de', 'kha_kho', 'rat_kho') DEFAULT 'de',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_word (user_id, dictionary_id),
    INDEX idx_next_review (next_review_date),
    CONSTRAINT fk_word_review_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_word_review_dictionary FOREIGN KEY (dictionary_id) REFERENCES dictionary(id) ON DELETE CASCADE
);

-- Daily stats table
CREATE TABLE daily_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    stat_date DATE NOT NULL,
    exercises_completed INT DEFAULT 0,
    correct_answers INT DEFAULT 0,
    total_answers INT DEFAULT 0,
    study_time_minutes INT DEFAULT 0,
    points_earned INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_date (user_id, stat_date),
    INDEX idx_stat_date (stat_date),
    CONSTRAINT fk_daily_stats_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Exercise results table (general)
CREATE TABLE exercise_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    exercise_id INT NOT NULL,
    selected_answer INT NOT NULL,
    correct_answer INT NOT NULL,
    is_correct BOOLEAN NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_submitted_at (submitted_at),
    CONSTRAINT fk_exercise_results_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Learning stats table (summary)
CREATE TABLE learning_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    words_learned INT DEFAULT 0,
    correct_answers INT DEFAULT 0,
    total_answers INT DEFAULT 0,
    streak_days INT DEFAULT 0,
    last_study_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user (user_id),
    CONSTRAINT fk_learning_stats_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

SELECT '✅ All tables created successfully!' as Status;

-- =====================================================
-- 6. INSERT SAMPLE DATA
-- =====================================================

-- Insert sample users
INSERT IGNORE INTO users (username, email, password, full_name) VALUES
('admin', 'admin@eduapp.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator'),
('testuser', 'test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test User'),
('student1', 'student1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Student One');

-- Insert sample topics
INSERT IGNORE INTO topics (name, description, color, icon) VALUES
('Từ vựng cơ bản', 'Học các từ vựng tiếng Anh cơ bản hàng ngày', '#4CAF50', 'fas fa-book'),
('Ngữ pháp', 'Các quy tắc ngữ pháp tiếng Anh cơ bản', '#2196F3', 'fas fa-grammar'),
('Giao tiếp', 'Các mẫu câu giao tiếp thường dùng', '#FF9800', 'fas fa-comments'),
('Phát âm', 'Luyện phát âm tiếng Anh chuẩn', '#9C27B0', 'fas fa-microphone');

-- Insert sample topic lessons
INSERT IGNORE INTO topic_lessons (topic_id, title, content, lesson_order) VALUES
(1, 'Chào hỏi cơ bản', 'Học cách chào hỏi trong tiếng Anh: Hello, Hi, Good morning, Good afternoon, Good evening', 1),
(1, 'Giới thiệu bản thân', 'Cách giới thiệu tên, tuổi, nghề nghiệp: My name is..., I am... years old, I work as...', 2),
(1, 'Gia đình', 'Từ vựng về các thành viên trong gia đình: father, mother, brother, sister, grandfather, grandmother', 3),
(2, 'Thì hiện tại đơn', 'Cách sử dụng thì hiện tại đơn với động từ "to be" và động từ thường', 1),
(2, 'Thì hiện tại tiếp diễn', 'Cách sử dụng thì hiện tại tiếp diễn để diễn tả hành động đang xảy ra', 2),
(3, 'Hỏi đường', 'Các câu hỏi và trả lời khi hỏi đường: Where is...? How can I get to...?', 1),
(3, 'Mua sắm', 'Giao tiếp khi mua sắm: How much is this? Can I try this on?', 2);

-- Insert sample dictionary words
INSERT IGNORE INTO dictionary (word, phonetic, vietnamese, english_definition, example, part_of_speech, difficulty) VALUES
('hello', '/həˈloʊ/', 'xin chào', 'a greeting used when meeting someone', 'Hello, how are you?', 'interjection', 'beginner'),
('world', '/wɜːrld/', 'thế giới', 'the earth and all the people and things on it', 'The world is beautiful.', 'noun', 'beginner'),
('computer', '/kəmˈpjuːtər/', 'máy tính', 'an electronic device for processing data', 'I use my computer every day.', 'noun', 'beginner'),
('beautiful', '/ˈbjuːtɪfəl/', 'đẹp', 'having qualities that give pleasure to see', 'She is very beautiful.', 'adjective', 'beginner'),
('study', '/ˈstʌdi/', 'học tập', 'to learn about something', 'I study English every day.', 'verb', 'beginner'),
('book', '/bʊk/', 'sách', 'a set of printed pages bound together', 'This is a good book.', 'noun', 'beginner'),
('water', '/ˈwɔːtər/', 'nước', 'a clear liquid that has no color, taste, or smell', 'I drink water every day.', 'noun', 'beginner'),
('food', '/fuːd/', 'thức ăn', 'things that people eat', 'This food is delicious.', 'noun', 'beginner'),
('house', '/haʊs/', 'nhà', 'a building where people live', 'My house is big.', 'noun', 'beginner'),
('family', '/ˈfæməli/', 'gia đình', 'a group of people related to each other', 'I love my family.', 'noun', 'beginner'),
('friend', '/frend/', 'bạn bè', 'a person you like and know well', 'He is my best friend.', 'noun', 'beginner'),
('school', '/skuːl/', 'trường học', 'a place where children go to learn', 'I go to school every day.', 'noun', 'beginner'),
('teacher', '/ˈtiːtʃər/', 'giáo viên', 'a person who teaches', 'My teacher is very kind.', 'noun', 'beginner'),
('student', '/ˈstuːdənt/', 'học sinh', 'a person who is learning', 'I am a student.', 'noun', 'beginner'),
('work', '/wɜːrk/', 'làm việc', 'to do a job', 'I work in an office.', 'verb', 'beginner'),
('time', '/taɪm/', 'thời gian', 'the indefinite continued progress of existence', 'What time is it?', 'noun', 'beginner'),
('good', '/ɡʊd/', 'tốt', 'having the right qualities', 'This is a good idea.', 'adjective', 'beginner'),
('bad', '/bæd/', 'xấu', 'not good', 'This is bad weather.', 'adjective', 'beginner'),
('big', '/bɪɡ/', 'to', 'large in size', 'This is a big house.', 'adjective', 'beginner'),
('small', '/smɔːl/', 'nhỏ', 'little in size', 'This is a small car.', 'adjective', 'beginner'),
('happy', '/ˈhæpi/', 'vui vẻ', 'feeling pleasure', 'I am happy today.', 'adjective', 'beginner'),
('sad', '/sæd/', 'buồn', 'feeling unhappy', 'She looks sad.', 'adjective', 'beginner'),
('love', '/lʌv/', 'yêu', 'to have strong feelings for someone', 'I love you.', 'verb', 'beginner'),
('like', '/laɪk/', 'thích', 'to enjoy something', 'I like music.', 'verb', 'beginner'),
('eat', '/iːt/', 'ăn', 'to put food in your mouth', 'I eat breakfast every morning.', 'verb', 'beginner'),
('drink', '/drɪŋk/', 'uống', 'to take liquid into your mouth', 'I drink coffee.', 'verb', 'beginner'),
('go', '/ɡoʊ/', 'đi', 'to move from one place to another', 'I go to work by bus.', 'verb', 'beginner'),
('come', '/kʌm/', 'đến', 'to move toward someone', 'Please come here.', 'verb', 'beginner'),
('see', '/siː/', 'nhìn thấy', 'to look at with your eyes', 'I can see the mountain.', 'verb', 'beginner'),
('hear', '/hɪr/', 'nghe', 'to receive sound through your ears', 'I can hear music.', 'verb', 'beginner');

-- Get user IDs for sample data (after users are inserted)
SET @admin_id = (SELECT id FROM users WHERE username = 'admin' LIMIT 1);
SET @test_user_id = (SELECT id FROM users WHERE username = 'testuser' LIMIT 1);

-- Insert sample decks (after users exist)
INSERT IGNORE INTO decks (user_id, name, description, visibility) VALUES
(@admin_id, 'Từ vựng cơ bản', 'Các từ vựng tiếng Anh cơ bản hàng ngày', 'public'),
(@admin_id, 'Động từ thường dùng', 'Các động từ tiếng Anh thường gặp', 'public'),
(@admin_id, 'Tính từ mô tả', 'Các tính từ mô tả tính cách và đặc điểm', 'public'),
(@admin_id, 'Công nghệ thông tin', 'Thuật ngữ chuyên ngành CNTT', 'public'),
(@admin_id, 'Âm nhạc', 'Thuật ngữ âm nhạc', 'public'),
(@admin_id, 'Y tế', 'Thuật ngữ y học', 'public'),
(@admin_id, 'Kinh doanh', 'Thuật ngữ kinh doanh', 'public'),
(@test_user_id, 'Bộ thẻ cá nhân', 'Bộ thẻ riêng của tôi', 'private');

-- Get deck IDs for flashcards (after decks are inserted)
SET @deck1_id = (SELECT id FROM decks WHERE name = 'Từ vựng cơ bản' AND user_id = @admin_id LIMIT 1);
SET @deck2_id = (SELECT id FROM decks WHERE name = 'Động từ thường dùng' AND user_id = @admin_id LIMIT 1);
SET @deck3_id = (SELECT id FROM decks WHERE name = 'Tính từ mô tả' AND user_id = @admin_id LIMIT 1);
SET @deck4_id = (SELECT id FROM decks WHERE name = 'Công nghệ thông tin' AND user_id = @admin_id LIMIT 1);

-- Insert sample flashcards
INSERT IGNORE INTO flashcards (deck_id, word, definition, example, source_dictionary_id) VALUES
-- Basic vocabulary deck
(@deck1_id, 'hello', 'xin chào', 'Hello, how are you?', (SELECT id FROM dictionary WHERE word = 'hello')),
(@deck1_id, 'world', 'thế giới', 'The world is beautiful.', (SELECT id FROM dictionary WHERE word = 'world')),
(@deck1_id, 'water', 'nước', 'I drink water every day.', (SELECT id FROM dictionary WHERE word = 'water')),
(@deck1_id, 'food', 'thức ăn', 'This food is delicious.', (SELECT id FROM dictionary WHERE word = 'food')),
(@deck1_id, 'house', 'nhà', 'My house is big.', (SELECT id FROM dictionary WHERE word = 'house')),
(@deck1_id, 'family', 'gia đình', 'I love my family.', (SELECT id FROM dictionary WHERE word = 'family')),
(@deck1_id, 'friend', 'bạn bè', 'He is my best friend.', (SELECT id FROM dictionary WHERE word = 'friend')),
(@deck1_id, 'school', 'trường học', 'I go to school every day.', (SELECT id FROM dictionary WHERE word = 'school')),

-- Common verbs deck
(@deck2_id, 'study', 'học tập', 'I study English every day.', (SELECT id FROM dictionary WHERE word = 'study')),
(@deck2_id, 'work', 'làm việc', 'I work in an office.', (SELECT id FROM dictionary WHERE word = 'work')),
(@deck2_id, 'love', 'yêu', 'I love you.', (SELECT id FROM dictionary WHERE word = 'love')),
(@deck2_id, 'like', 'thích', 'I like music.', (SELECT id FROM dictionary WHERE word = 'like')),
(@deck2_id, 'eat', 'ăn', 'I eat breakfast every morning.', (SELECT id FROM dictionary WHERE word = 'eat')),
(@deck2_id, 'drink', 'uống', 'I drink coffee.', (SELECT id FROM dictionary WHERE word = 'drink')),
(@deck2_id, 'go', 'đi', 'I go to work by bus.', (SELECT id FROM dictionary WHERE word = 'go')),
(@deck2_id, 'come', 'đến', 'Please come here.', (SELECT id FROM dictionary WHERE word = 'come')),

-- Descriptive adjectives deck
(@deck3_id, 'beautiful', 'đẹp', 'She is very beautiful.', (SELECT id FROM dictionary WHERE word = 'beautiful')),
(@deck3_id, 'good', 'tốt', 'This is a good idea.', (SELECT id FROM dictionary WHERE word = 'good')),
(@deck3_id, 'bad', 'xấu', 'This is bad weather.', (SELECT id FROM dictionary WHERE word = 'bad')),
(@deck3_id, 'big', 'to', 'This is a big house.', (SELECT id FROM dictionary WHERE word = 'big')),
(@deck3_id, 'small', 'nhỏ', 'This is a small car.', (SELECT id FROM dictionary WHERE word = 'small')),
(@deck3_id, 'happy', 'vui vẻ', 'I am happy today.', (SELECT id FROM dictionary WHERE word = 'happy')),
(@deck3_id, 'sad', 'buồn', 'She looks sad.', (SELECT id FROM dictionary WHERE word = 'sad')),

-- IT terms deck
(@deck4_id, 'computer', 'máy tính', 'I use my computer every day.', (SELECT id FROM dictionary WHERE word = 'computer')),
(@deck4_id, 'software', 'phần mềm', 'This software is very useful.', NULL),
(@deck4_id, 'hardware', 'phần cứng', 'Computer hardware includes CPU and RAM.', NULL),
(@deck4_id, 'internet', 'mạng internet', 'I browse the internet every day.', NULL),
(@deck4_id, 'website', 'trang web', 'This website is very informative.', NULL),
(@deck4_id, 'database', 'cơ sở dữ liệu', 'The database stores all user information.', NULL),
(@deck4_id, 'programming', 'lập trình', 'Programming is my hobby.', NULL),
(@deck4_id, 'algorithm', 'thuật toán', 'This algorithm is very efficient.', NULL);

-- Insert sample listening exercises
INSERT IGNORE INTO listening_exercises (title, question, audio_url, option_a, option_b, option_c, option_d, correct_answer, explanation, difficulty) VALUES
('Basic Greeting', 'Nghe đoạn hội thoại và chọn câu trả lời đúng: Người nói đang làm gì?', 'tts:Hello, how are you today? I am fine, thank you.', 'Chào hỏi và hỏi thăm sức khỏe', 'Hỏi đường', 'Mua sắm', 'Đặt món ăn', 'A', 'Đoạn hội thoại là lời chào hỏi cơ bản "Hello, how are you today? I am fine, thank you."', 'beginner'),
('Numbers', 'Nghe và chọn số được đọc:', 'tts:Twenty five', '15', '25', '35', '45', 'B', 'Số được đọc là "twenty five" = 25', 'beginner'),
('Time', 'Nghe và chọn thời gian được đọc:', 'tts:It is three thirty in the afternoon', '3:00 PM', '3:30 PM', '3:15 PM', '3:45 PM', 'B', 'Thời gian được đọc là "three thirty in the afternoon" = 3:30 PM', 'beginner'),
('Weather', 'Nghe và chọn thời tiết được mô tả:', 'tts:Today is sunny and warm. It is a beautiful day.', 'Mưa và lạnh', 'Có nắng và ấm', 'Có mây và mát', 'Có tuyết và lạnh', 'B', 'Thời tiết được mô tả là "sunny and warm" = có nắng và ấm', 'beginner'),
('Food Order', 'Nghe đoạn hội thoại và chọn món ăn được đặt:', 'tts:I would like a hamburger and a cup of coffee, please.', 'Pizza và nước ngọt', 'Hamburger và cà phê', 'Sandwich và trà', 'Salad và nước', 'B', 'Món được đặt là "hamburger and a cup of coffee"', 'beginner'),
('Directions', 'Nghe và chọn hướng dẫn đúng:', 'tts:Go straight, then turn left at the traffic light.', 'Đi thẳng rồi rẽ phải', 'Đi thẳng rồi rẽ trái tại đèn giao thông', 'Rẽ trái rồi đi thẳng', 'Rẽ phải tại ngã tư', 'B', 'Hướng dẫn: "Go straight, then turn left at the traffic light"', 'beginner'),
('Shopping', 'Nghe đoạn hội thoại mua sắm và chọn giá tiền:', 'tts:The book costs fifteen dollars and the pen costs three dollars.', '$15 và $3', '$50 và $13', '$15 và $30', '$5 và $3', 'A', 'Sách giá fifteen dollars ($15) và bút giá three dollars ($3)', 'beginner'),
('School', 'Nghe và chọn môn học được nhắc đến:', 'tts:I have math class at nine and English class at ten.', 'Toán và Khoa học', 'Toán và Tiếng Anh', 'Lịch sử và Tiếng Anh', 'Toán và Thể dục', 'B', 'Các môn học: math (toán) và English (tiếng Anh)', 'beginner'),
('Family', 'Nghe và chọn thành viên gia đình được nhắc đến:', 'tts:My father is a doctor and my mother is a teacher.', 'Bố là bác sĩ, mẹ là y tá', 'Bố là giáo viên, mẹ là bác sĩ', 'Bố là bác sĩ, mẹ là giáo viên', 'Bố là kỹ sư, mẹ là giáo viên', 'C', 'Father is a doctor (bố là bác sĩ) và mother is a teacher (mẹ là giáo viên)', 'beginner'),
('Colors', 'Nghe và chọn màu sắc được mô tả:', 'tts:The car is red and the house is blue.', 'Xe đỏ, nhà xanh lá', 'Xe xanh, nhà đỏ', 'Xe đỏ, nhà xanh dương', 'Xe vàng, nhà đỏ', 'C', 'Car is red (xe đỏ) và house is blue (nhà xanh dương)', 'beginner');

-- =====================================================
-- 7. FINAL VERIFICATION AND SUMMARY
-- =====================================================

-- Show table creation summary
SELECT '✅ Database setup completed successfully!' as Status;

-- Show table counts
SELECT 'TABLE SUMMARY:' as Info, '' as Count
UNION ALL
SELECT 'Users', COUNT(*) FROM users
UNION ALL
SELECT 'Dictionary Words', COUNT(*) FROM dictionary
UNION ALL
SELECT 'Topics', COUNT(*) FROM topics
UNION ALL
SELECT 'Topic Lessons', COUNT(*) FROM topic_lessons
UNION ALL
SELECT 'Decks', COUNT(*) FROM decks
UNION ALL
SELECT 'Flashcards', COUNT(*) FROM flashcards
UNION ALL
SELECT 'Listening Exercises', COUNT(*) FROM listening_exercises
UNION ALL
SELECT 'Study Progress Records', COUNT(*) FROM study_progress;

-- Show sample user info
SELECT 'SAMPLE USERS:' as Info, '' as Details
UNION ALL
SELECT username, CONCAT('ID: ', id, ', Email: ', email) FROM users
ORDER BY Info DESC, Details;

-- Show sample decks
SELECT 'SAMPLE DECKS:' as Info, '' as Details
UNION ALL
SELECT d.name, CONCAT('Cards: ', COUNT(f.id), ', Owner: ', u.username)
FROM decks d
LEFT JOIN flashcards f ON d.id = f.deck_id
LEFT JOIN users u ON d.user_id = u.id
GROUP BY d.id, d.name, u.username
ORDER BY Info DESC, Details;

SELECT '🎉 Ready to use! You can now:' as Message
UNION ALL
SELECT '1. Login with username: testuser, password: password' as Message
UNION ALL
SELECT '2. Visit flashcards.php to study flashcards' as Message
UNION ALL
SELECT '3. Visit stats.php to view statistics' as Message
UNION ALL
SELECT '4. Visit listening.php for listening exercises' as Message
UNION ALL
SELECT '5. Visit topics.php for topic-based learning' as Message;
