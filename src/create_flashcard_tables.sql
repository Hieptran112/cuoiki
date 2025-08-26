-- =====================================================
-- CREATE FLASHCARD SYSTEM TABLES
-- =====================================================
-- This script creates all necessary tables for the flashcard system
USE eduapp;
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

-- Create specialized_terms table if not exists (for domain-specific vocabulary)
CREATE TABLE IF NOT EXISTS specialized_terms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    word VARCHAR(255) NOT NULL,
    vietnamese TEXT,
    english_definition TEXT,
    example TEXT,
    domain VARCHAR(100),
    difficulty ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'intermediate',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_word (word),
    INDEX idx_domain (domain)
);

-- Insert sample data for testing
-- Insert a test user if not exists
INSERT IGNORE INTO users (username, email, password, full_name) VALUES 
('testuser', 'test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test User');

-- Get the test user ID (this will work if the user exists)
SET @test_user_id = (SELECT id FROM users WHERE username = 'testuser' LIMIT 1);

-- Insert sample decks if test user exists
INSERT IGNORE INTO flashcard_decks (user_id, name, description) 
SELECT @test_user_id, 'Từ vựng cơ bản', 'Các từ vựng tiếng Anh cơ bản hàng ngày'
WHERE @test_user_id IS NOT NULL;

INSERT IGNORE INTO flashcard_decks (user_id, name, description) 
SELECT @test_user_id, 'Động từ thường dùng', 'Các động từ tiếng Anh thường gặp'
WHERE @test_user_id IS NOT NULL;

INSERT IGNORE INTO flashcard_decks (user_id, name, description) 
SELECT @test_user_id, 'Tính từ mô tả', 'Các tính từ mô tả tính cách và đặc điểm'
WHERE @test_user_id IS NOT NULL;

INSERT IGNORE INTO flashcard_decks (user_id, name, description) 
SELECT @test_user_id, 'Từ vựng công việc', 'Từ vựng liên quan đến công việc và nghề nghiệp'
WHERE @test_user_id IS NOT NULL;

-- Insert some sample flashcards
SET @deck1_id = (SELECT id FROM flashcard_decks WHERE name = 'Từ vựng cơ bản' AND user_id = @test_user_id LIMIT 1);
SET @deck2_id = (SELECT id FROM flashcard_decks WHERE name = 'Động từ thường dùng' AND user_id = @test_user_id LIMIT 1);

INSERT IGNORE INTO flashcards (deck_id, front, back, example) 
SELECT @deck1_id, 'hello', 'xin chào', 'Hello, how are you?'
WHERE @deck1_id IS NOT NULL;

INSERT IGNORE INTO flashcards (deck_id, front, back, example) 
SELECT @deck1_id, 'thank you', 'cảm ơn', 'Thank you for your help.'
WHERE @deck1_id IS NOT NULL;

INSERT IGNORE INTO flashcards (deck_id, front, back, example) 
SELECT @deck2_id, 'go', 'đi', 'I go to school every day.'
WHERE @deck2_id IS NOT NULL;

INSERT IGNORE INTO flashcards (deck_id, front, back, example) 
SELECT @deck2_id, 'come', 'đến', 'Please come here.'
WHERE @deck2_id IS NOT NULL;

-- Display results
SELECT 'Flashcard system tables created successfully!' as Status;

SELECT 'Table Status:' as Info, '' as Value
UNION ALL
SELECT 'Users table', CASE WHEN COUNT(*) > 0 THEN 'EXISTS' ELSE 'NOT FOUND' END FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'users'
UNION ALL
SELECT 'Flashcard_decks table', CASE WHEN COUNT(*) > 0 THEN 'EXISTS' ELSE 'NOT FOUND' END FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'flashcard_decks'
UNION ALL
SELECT 'Flashcards table', CASE WHEN COUNT(*) > 0 THEN 'EXISTS' ELSE 'NOT FOUND' END FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'flashcards'
UNION ALL
SELECT 'Dictionary table', CASE WHEN COUNT(*) > 0 THEN 'EXISTS' ELSE 'NOT FOUND' END FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'dictionary';

-- Show sample data counts
SELECT 'Data Counts:' as Info, '' as Value
UNION ALL
SELECT 'Users', COUNT(*) FROM users
UNION ALL
SELECT 'Flashcard Decks', COUNT(*) FROM flashcard_decks
UNION ALL
SELECT 'Flashcards', COUNT(*) FROM flashcards
UNION ALL
SELECT 'Dictionary Words', COUNT(*) FROM dictionary;
