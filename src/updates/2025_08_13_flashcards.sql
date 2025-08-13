-- SQL Update: Flashcards schema and user profile fields
-- Run after database_setup.sql

USE eduapp;

-- Add user profile fields if not exist
ALTER TABLE users 
    ADD COLUMN IF NOT EXISTS full_name VARCHAR(100) NULL AFTER email,
    ADD COLUMN IF NOT EXISTS major VARCHAR(100) NULL AFTER full_name;

-- Decks table
CREATE TABLE IF NOT EXISTS decks (
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
CREATE TABLE IF NOT EXISTS flashcards (
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

-- Study progress per user per flashcard
CREATE TABLE IF NOT EXISTS study_progress (
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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_flashcard (user_id, flashcard_id),
    INDEX idx_user_next_due (user_id, next_due_at),
    CONSTRAINT fk_progress_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_progress_flashcard FOREIGN KEY (flashcard_id) REFERENCES flashcards(id) ON DELETE CASCADE
);


-- Preset decks (templates) that will be cloned per user on demand
CREATE TABLE IF NOT EXISTS preset_decks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    description TEXT,
    UNIQUE KEY uniq_slug (slug),
    UNIQUE KEY uniq_name (name)
);

-- Seed some common presets
INSERT IGNORE INTO preset_decks (name, slug, description) VALUES
('Công nghệ thông tin', 'it', 'Thuật ngữ chuyên ngành CNTT: computer, algorithm, database, interface...'),
('Âm nhạc', 'music', 'Thuật ngữ âm nhạc: melody, rhythm, concert, guitar...'),
('Y tế', 'medical', 'Thuật ngữ y học: diagnosis, symptom, treatment...'),
('Kinh doanh', 'business', 'Thuật ngữ kinh doanh: strategy, innovation, efficiency...');

-- Add SM-2 scheduling fields if they do not exist
ALTER TABLE study_progress
    ADD COLUMN IF NOT EXISTS sm2_ease_factor DECIMAL(4,2) DEFAULT 2.50 AFTER incorrect_count,
    ADD COLUMN IF NOT EXISTS sm2_interval_days INT DEFAULT 0 AFTER sm2_ease_factor,
    ADD COLUMN IF NOT EXISTS sm2_repetitions INT DEFAULT 0 AFTER sm2_interval_days;

-- Specialized terms table for domain-specific dictionaries
CREATE TABLE IF NOT EXISTS specialized_terms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    domain VARCHAR(100) NOT NULL,
    word VARCHAR(255) NOT NULL,
    vietnamese VARCHAR(500) NULL,
    english_definition TEXT NULL,
    example TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_domain_word (domain, word),
    INDEX idx_word (word)
);

-- Seed a minimal set of specialized vocabulary
INSERT IGNORE INTO specialized_terms (domain, word, vietnamese, english_definition, example) VALUES
('it', 'algorithm', 'thuật toán', 'A step-by-step procedure for calculations and problem-solving.', 'We optimized the sorting algorithm to run faster.'),
('it', 'database', 'cơ sở dữ liệu', 'An organized collection of structured information, typically stored electronically.', 'The application stores user data in a relational database.'),
('medical', 'diagnosis', 'chẩn đoán', 'The identification of the nature and cause of a condition.', 'Accurate diagnosis is essential for effective treatment.'),
('medical', 'symptom', 'triệu chứng', 'A physical or mental feature indicating a condition.', 'Fever is a common symptom of infection.'),
('business', 'strategy', 'chiến lược', 'A plan of action designed to achieve a long-term goal.', 'Their growth strategy focuses on new markets.');

-- Track wrong answers per user for resurfacing in daily quizzes
CREATE TABLE IF NOT EXISTS user_word_review (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    dictionary_id INT NOT NULL,
    wrong_count INT DEFAULT 0,
    last_wrong_date DATE NULL,
    next_review_date DATE NULL,
    difficulty ENUM('normal','kha_kho','rat_kho') DEFAULT 'normal',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_word (user_id, dictionary_id),
    INDEX idx_user_next (user_id, next_review_date),
    CONSTRAINT fk_review_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_review_dict FOREIGN KEY (dictionary_id) REFERENCES dictionary(id) ON DELETE CASCADE
);

