-- =====================================================
-- DATABASE CLEANUP SCRIPT - SIMPLE VERSION
-- =====================================================
-- This script removes duplicate/conflicting tables and standardizes the database structure
-- Run this script to clean up table conflicts and foreign key issues
USE eduapp;
-- Show current tables before cleanup
SELECT 'BEFORE CLEANUP - Current Tables:' as Status;
SHOW TABLES;

-- =====================================================
-- 1. REMOVE PROBLEMATIC FOREIGN KEY CONSTRAINTS (MANUAL)
-- =====================================================

-- Drop known problematic foreign key constraints
-- Note: If these constraints don't exist, the commands will be ignored

-- Drop foreign key constraint that references 'decks' table
ALTER TABLE flashcards DROP FOREIGN KEY IF EXISTS fk_flashcards_deck;
ALTER TABLE flashcards DROP FOREIGN KEY IF EXISTS fk_flashcards_decks;
ALTER TABLE flashcards DROP FOREIGN KEY IF EXISTS flashcards_ibfk_1;
ALTER TABLE flashcards DROP FOREIGN KEY IF EXISTS flashcards_deck_id_foreign;

-- Drop any other problematic constraints
ALTER TABLE flashcards DROP FOREIGN KEY IF EXISTS fk_flashcards_dictionary;
ALTER TABLE flashcards DROP FOREIGN KEY IF EXISTS flashcards_ibfk_2;

SELECT '✅ Removed known problematic foreign key constraints' as Status;

-- =====================================================
-- 2. DROP DUPLICATE/CONFLICTING TABLES
-- =====================================================

-- Drop old 'decks' table if it exists (conflicts with 'flashcard_decks')
DROP TABLE IF EXISTS decks;
SELECT '✅ Dropped old "decks" table' as Status;

-- Drop preset_decks table if it exists (not used in current system)
DROP TABLE IF EXISTS preset_decks;
SELECT '✅ Dropped unused "preset_decks" table' as Status;

-- Drop learning_progress table if it exists (not used in current system)
DROP TABLE IF EXISTS learning_progress;
SELECT '✅ Dropped unused "learning_progress" table' as Status;

-- Drop specialized_terms table if it exists (duplicate of dictionary functionality)
DROP TABLE IF EXISTS specialized_terms;
SELECT '✅ Dropped duplicate "specialized_terms" table' as Status;

-- Drop exercise_results table if it exists (not used in current flashcard system)
DROP TABLE IF EXISTS exercise_results;
SELECT '✅ Dropped unused "exercise_results" table' as Status;

-- Drop learning_stats table if it exists (not used in current flashcard system)
DROP TABLE IF EXISTS learning_stats;
SELECT '✅ Dropped unused "learning_stats" table' as Status;

-- =====================================================
-- 3. STANDARDIZE FLASHCARDS TABLE STRUCTURE
-- =====================================================

-- Create flashcards table if it doesn't exist
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
    INDEX idx_deck_id (deck_id),
    INDEX idx_next_review (next_review)
);

-- Add front column if it doesn't exist
ALTER TABLE flashcards ADD COLUMN IF NOT EXISTS front TEXT NOT NULL AFTER deck_id;

-- Add back column if it doesn't exist
ALTER TABLE flashcards ADD COLUMN IF NOT EXISTS back TEXT NOT NULL AFTER front;

-- Add example column if it doesn't exist
ALTER TABLE flashcards ADD COLUMN IF NOT EXISTS example TEXT AFTER back;

SELECT '✅ Standardized flashcards table structure' as Status;

-- =====================================================
-- 4. ENSURE CORE TABLES EXIST WITH CORRECT STRUCTURE
-- =====================================================

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
    INDEX idx_user_id (user_id)
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

-- Create listening_exercises table if not exists (keep this as it's actively used)
CREATE TABLE IF NOT EXISTS listening_exercises (
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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create listening_results table if not exists (keep this as it's actively used)
CREATE TABLE IF NOT EXISTS listening_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    exercise_id INT NOT NULL,
    user_answer CHAR(1) NOT NULL,
    is_correct BOOLEAN NOT NULL,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_exercise_id (exercise_id)
);

SELECT '✅ Ensured all core tables exist' as Status;

-- =====================================================
-- 5. MIGRATE DATA FROM OLD COLUMNS (IF ANY)
-- =====================================================

-- Migrate data from old columns to new ones (if they exist)
-- This is safe - if columns don't exist, the commands will be ignored

-- Update front/back from word/definition if they exist and are empty
UPDATE flashcards SET front = word WHERE front IS NULL OR front = '' AND word IS NOT NULL AND word != '';
UPDATE flashcards SET back = definition WHERE back IS NULL OR back = '' AND definition IS NOT NULL AND definition != '';

-- Drop old columns if they exist (will be ignored if columns don't exist)
ALTER TABLE flashcards DROP COLUMN IF EXISTS word;
ALTER TABLE flashcards DROP COLUMN IF EXISTS definition;
ALTER TABLE flashcards DROP COLUMN IF EXISTS question;
ALTER TABLE flashcards DROP COLUMN IF EXISTS answer;

SELECT '✅ Migrated data from old columns' as Status;

-- =====================================================
-- 6. FINAL VERIFICATION
-- =====================================================

-- Show final table structure
SELECT 'AFTER CLEANUP - Final Tables:' as Status;
SHOW TABLES;

-- Show table counts
SELECT 'Table Counts:' as Info, '' as Count
UNION ALL
SELECT 'users', COUNT(*) FROM users
UNION ALL
SELECT 'flashcard_decks', COUNT(*) FROM flashcard_decks
UNION ALL
SELECT 'flashcards', COUNT(*) FROM flashcards
UNION ALL
SELECT 'dictionary', COUNT(*) FROM dictionary
UNION ALL
SELECT 'listening_exercises', COUNT(*) FROM listening_exercises
UNION ALL
SELECT 'listening_results', COUNT(*) FROM listening_results;

-- Verify flashcards table structure
SELECT 'Flashcards Table Structure:' as Status;
DESCRIBE flashcards;

-- Check for any remaining foreign key constraints
SELECT 'Remaining Foreign Key Constraints:' as Status;
SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE 
WHERE TABLE_SCHEMA = DATABASE() 
AND REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY TABLE_NAME;

SELECT '🎉 DATABASE CLEANUP COMPLETED SUCCESSFULLY!' as Status;
