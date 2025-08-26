# 🗄️ Complete Database Setup Guide

This guide will help you set up the complete education app database with all tables, relationships, and sample data.

## 🚨 Problem Solved

**Previous Issue:** Multiple conflicting SQL files with different table structures caused errors like:
- `#1054 - Unknown column 'front' in 'field list'`
- Inconsistent table names (`flashcard_decks` vs `decks`)
- Missing foreign key relationships
- Broken flashcard and statistics synchronization

**Solution:** One comprehensive SQL file that creates all tables with consistent structure and proper relationships.

## 📁 Files Overview

### ✅ New Files (Use These)
- **`complete_database_setup.sql`** - Main database setup script with all tables and sample data
- **`setup_complete_database.php`** - PHP script to run the setup safely with error handling
- **`test_flashcard_sync.php`** - Test script to verify everything works
- **`migrate_flashcard_tables.php`** - Migration script (backup option)

### 🗑️ Old Files (Moved to Backup)
- All old conflicting SQL files are moved to `old_sql_backup/` directory

## 🚀 Quick Setup (Recommended)

### Option 1: Using PHP Script (Recommended)
1. **Run the setup script:**
   ```bash
   php src/setup_complete_database.php
   ```
   Or visit in browser: `http://your-domain/src/setup_complete_database.php`

2. **Verify the setup:**
   Visit: `http://your-domain/src/test_flashcard_sync.php`

### Option 2: Using SQL File Directly
1. **Import the SQL file:**
   ```bash
   mysql -u root -p eduapp < src/complete_database_setup.sql
   ```

2. **Or using phpMyAdmin:**
   - Open phpMyAdmin
   - Select `eduapp` database (or create it)
   - Go to Import tab
   - Choose `complete_database_setup.sql`
   - Click Go

## 🧹 Cleanup Old Files (Optional)

To clean up old conflicting SQL files:
```bash
php src/cleanup_old_sql_files.php
```

This moves old files to `old_sql_backup/` directory for safety.

## 📊 What Gets Created

### Core Tables
- **`users`** - User accounts with authentication
- **`dictionary`** - English-Vietnamese dictionary with 30+ sample words
- **`decks`** - Flashcard decks (replaces old `flashcard_decks`)
- **`flashcards`** - Individual flashcards with word/definition structure
- **`study_progress`** - Spaced repetition progress tracking

### Learning System Tables
- **`topics`** - Learning topics (Grammar, Vocabulary, etc.)
- **`topic_lessons`** - Individual lessons within topics
- **`topic_progress`** - User progress through topics
- **`topic_exercise_results`** - Results from topic exercises

### Listening System Tables
- **`listening_exercises`** - Audio-based exercises with TTS
- **`listening_results`** - User results from listening exercises

### Statistics Tables
- **`daily_stats`** - Daily learning statistics
- **`learning_stats`** - Overall learning progress
- **`user_word_review`** - Dictionary word review tracking
- **`exercise_results`** - General exercise results

## 🎯 Sample Data Included

### Users
- **admin** / admin@eduapp.com
- **testuser** / test@example.com  
- **student1** / student1@example.com
- Password for all: `password`

### Flashcard Decks
- **Từ vựng cơ bản** - Basic vocabulary (8 cards)
- **Động từ thường dùng** - Common verbs (8 cards)
- **Tính từ mô tả** - Descriptive adjectives (7 cards)
- **Công nghệ thông tin** - IT terms (8 cards)
- Plus: Âm nhạc, Y tế, Kinh doanh decks

### Dictionary
- 30+ English words with Vietnamese translations
- Phonetic transcriptions
- Example sentences
- Part of speech and difficulty levels

### Listening Exercises
- 10 beginner-level exercises
- Topics: Greetings, Numbers, Time, Weather, Food, etc.
- Text-to-Speech audio URLs

### Topics & Lessons
- 4 main topics with multiple lessons each
- Structured learning progression

## 🔧 Database Structure Features

### ✅ Fixed Issues
- **Consistent table names** - Uses `decks` instead of `flashcard_decks`
- **Proper foreign keys** - All relationships properly defined
- **Unified flashcard structure** - Uses `word/definition` fields consistently
- **Complete spaced repetition** - SM-2 algorithm implementation
- **Statistics synchronization** - Proper data flow between study and stats

### 🔗 Key Relationships
```
users → decks → flashcards → study_progress
users → topic_progress → topics → topic_lessons
users → listening_results → listening_exercises
users → daily_stats, learning_stats, user_word_review
```

## 🧪 Testing & Verification

### 1. Database Structure Test
```bash
php src/test_flashcard_sync.php
```

### 2. Manual Testing Steps
1. **Login:** Use `testuser` / `password`
2. **Flashcards:** Visit `flashcards.php`
   - Select a deck
   - Study some cards
   - Rate cards (Again/Hard/Good/Easy)
3. **Statistics:** Visit `stats.php`
   - Verify flashcard stats update
   - Check deck breakdown
4. **Listening:** Visit `listening.php`
   - Try some exercises
5. **Topics:** Visit `topics.php`
   - Complete some lessons

### 3. API Testing
The test script includes buttons to test:
- List Decks API
- Flashcard Stats API
- Data synchronization

## 🚨 Troubleshooting

### Common Issues

**1. Connection Refused**
```
Solution: Start your MySQL/XAMPP server
```

**2. Database doesn't exist**
```sql
CREATE DATABASE eduapp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

**3. Permission errors**
```
Solution: Check MySQL user permissions
GRANT ALL PRIVILEGES ON eduapp.* TO 'root'@'localhost';
```

**4. Foreign key errors**
```
Solution: The script handles this automatically with:
SET FOREIGN_KEY_CHECKS = 0;
-- create tables
SET FOREIGN_KEY_CHECKS = 1;
```

### Verification Queries
```sql
-- Check all tables exist
SHOW TABLES;

-- Check sample data
SELECT COUNT(*) FROM users;
SELECT COUNT(*) FROM decks;
SELECT COUNT(*) FROM flashcards;
SELECT COUNT(*) FROM dictionary;

-- Check relationships
SELECT d.name, COUNT(f.id) as card_count 
FROM decks d 
LEFT JOIN flashcards f ON d.id = f.deck_id 
GROUP BY d.id;
```

## 🎉 Success Indicators

After successful setup, you should see:
- ✅ 15+ database tables created
- ✅ 3 sample users
- ✅ 8 flashcard decks with 30+ cards
- ✅ 30+ dictionary words
- ✅ 10 listening exercises
- ✅ 4 topics with lessons
- ✅ All foreign key relationships working
- ✅ Flashcard study interface functional
- ✅ Statistics synchronization working

## 📞 Support

If you encounter issues:
1. Check the setup script output for specific errors
2. Run the test script to identify problems
3. Verify your MySQL server is running
4. Check database user permissions
5. Review the troubleshooting section above

The new setup is designed to be bulletproof and handle all edge cases automatically! 🚀
