-- Tạo database nếu chưa tồn tại
CREATE DATABASE IF NOT EXISTS eduapp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE eduapp;

-- Bảng users (nếu chưa có)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng từ điển
CREATE TABLE IF NOT EXISTS dictionary (
    id INT AUTO_INCREMENT PRIMARY KEY,
    word VARCHAR(100) NOT NULL UNIQUE,
    phonetic VARCHAR(100),
    vietnamese TEXT NOT NULL,
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

-- Bảng kết quả bài tập
CREATE TABLE IF NOT EXISTS exercise_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    exercise_id INT NOT NULL,
    selected_answer INT NOT NULL,
    correct_answer INT NOT NULL,
    is_correct BOOLEAN NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_submitted_at (submitted_at)
);

-- Bảng thống kê học tập
CREATE TABLE IF NOT EXISTS learning_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    words_learned INT DEFAULT 0,
    correct_answers INT DEFAULT 0,
    total_answers INT DEFAULT 0,
    streak_days INT DEFAULT 0,
    last_study_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user (user_id)
);

-- Thêm dữ liệu mẫu cho từ điển
INSERT INTO dictionary (word, phonetic, vietnamese, english_definition, example, part_of_speech, difficulty) VALUES
-- Beginner level
('hello', '/həˈloʊ/', 'xin chào', 'Used as a greeting or to begin a phone conversation', 'Hello, how are you today?', 'interjection', 'beginner'),
('goodbye', '/ˌɡʊdˈbaɪ/', 'tạm biệt', 'Used to express good wishes when parting or at the end of a conversation', 'Goodbye, see you tomorrow!', 'interjection', 'beginner'),
('thank you', '/ˈθæŋk juː/', 'cảm ơn', 'Used to express gratitude or appreciation', 'Thank you for your help.', 'interjection', 'beginner'),
('please', '/pliːz/', 'xin vui lòng', 'Used to make a polite request', 'Please help me with this.', 'adverb', 'beginner'),
('sorry', '/ˈsɑːri/', 'xin lỗi', 'Used to express apology or regret', 'I am sorry for being late.', 'adjective', 'beginner'),

-- Intermediate level
('beautiful', '/ˈbjuːtɪfʊl/', 'đẹp', 'Pleasing the senses or mind aesthetically', 'She is a beautiful woman.', 'adjective', 'intermediate'),
('important', '/ɪmˈpɔːrtənt/', 'quan trọng', 'Of great significance or value', 'This is an important meeting.', 'adjective', 'intermediate'),
('difficult', '/ˈdɪfɪkəlt/', 'khó khăn', 'Not easy to do, understand, or deal with', 'This is a difficult problem to solve.', 'adjective', 'intermediate'),
('successful', '/səkˈsesfʊl/', 'thành công', 'Accomplishing an aim or purpose', 'He is a successful businessman.', 'adjective', 'intermediate'),
('interesting', '/ˈɪntrəstɪŋ/', 'thú vị', 'Arousing curiosity or interest', 'This is an interesting book.', 'adjective', 'intermediate'),

-- Advanced level
('accomplish', '/əˈkʌmplɪʃ/', 'hoàn thành', 'To succeed in doing or completing something', 'She accomplished all her goals.', 'verb', 'advanced'),
('determine', '/dɪˈtɜːrmɪn/', 'xác định', 'To find out or establish something', 'We need to determine the cause of the problem.', 'verb', 'advanced'),
('establish', '/ɪˈstæblɪʃ/', 'thiết lập', 'To set up or create something', 'They established a new company.', 'verb', 'advanced'),
('maintain', '/meɪnˈteɪn/', 'duy trì', 'To keep something in good condition', 'It is important to maintain good health.', 'verb', 'advanced'),
('achieve', '/əˈtʃiːv/', 'đạt được', 'To successfully reach a goal or target', 'She achieved her dream of becoming a doctor.', 'verb', 'advanced'),

-- Nouns
('knowledge', '/ˈnɑːlɪdʒ/', 'kiến thức', 'Facts, information, and skills acquired through experience or education', 'He has extensive knowledge of history.', 'noun', 'intermediate'),
('experience', '/ɪkˈspɪriəns/', 'kinh nghiệm', 'Practical contact with and observation of facts or events', 'She has years of experience in teaching.', 'noun', 'intermediate'),
('opportunity', '/ˌɑːpərˈtuːnəti/', 'cơ hội', 'A time or set of circumstances that makes it possible to do something', 'This is a great opportunity for you.', 'noun', 'intermediate'),
('challenge', '/ˈtʃælɪndʒ/', 'thử thách', 'A task or situation that tests someone''s abilities', 'This project presents a real challenge.', 'noun', 'intermediate'),
('progress', '/ˈprɑːɡres/', 'tiến bộ', 'Forward or onward movement toward a destination', 'She has made great progress in her studies.', 'noun', 'intermediate'),

-- Adverbs
('quickly', '/ˈkwɪkli/', 'nhanh chóng', 'At a fast speed', 'He quickly finished his homework.', 'adverb', 'beginner'),
('slowly', '/ˈsloʊli/', 'chậm chạp', 'At a slow speed', 'She walked slowly down the street.', 'adverb', 'beginner'),
('carefully', '/ˈkerfəli/', 'cẩn thận', 'In a way that shows care and attention', 'He carefully examined the document.', 'adverb', 'intermediate'),
('easily', '/ˈiːzəli/', 'dễ dàng', 'Without difficulty or effort', 'She easily solved the puzzle.', 'adverb', 'intermediate'),
('completely', '/kəmˈpliːtli/', 'hoàn toàn', 'In every way or as much as possible', 'The task is completely finished.', 'adverb', 'intermediate'),

-- Prepositions
('between', '/bɪˈtwiːn/', 'giữa', 'In or into the space that separates two places, people, or objects', 'The book is between the two notebooks.', 'preposition', 'beginner'),
('among', '/əˈmʌŋ/', 'trong số', 'Surrounded by or in the company of', 'She was among the best students.', 'preposition', 'intermediate'),
('through', '/θruː/', 'xuyên qua', 'Moving in one side and out of the other side of', 'We walked through the park.', 'preposition', 'intermediate'),
('beyond', '/bɪˈjɑːnd/', 'vượt quá', 'On or to the further side of', 'The mountains are beyond the horizon.', 'preposition', 'advanced'),
('within', '/wɪˈðɪn/', 'trong vòng', 'Inside the limits or bounds of', 'The project will be completed within a month.', 'preposition', 'intermediate'),

-- Conjunctions
('although', '/ɔːlˈðoʊ/', 'mặc dù', 'In spite of the fact that', 'Although it was raining, we went for a walk.', 'conjunction', 'intermediate'),
('because', '/bɪˈkɔːz/', 'bởi vì', 'For the reason that', 'I stayed home because I was sick.', 'conjunction', 'beginner'),
('however', '/haʊˈevər/', 'tuy nhiên', 'Used to introduce a statement that contrasts with something that has been said', 'I like the idea; however, I think it needs more work.', 'conjunction', 'intermediate'),
('therefore', '/ˈðerfɔːr/', 'do đó', 'For that reason; consequently', 'He was tired; therefore, he went to bed early.', 'conjunction', 'intermediate'),
('meanwhile', '/ˈmiːnwaɪl/', 'trong khi đó', 'At the same time', 'I was cooking dinner; meanwhile, my husband was setting the table.', 'conjunction', 'intermediate');

-- Tạo trigger để tự động cập nhật thống kê
DELIMITER //
CREATE TRIGGER IF NOT EXISTS update_learning_stats
AFTER INSERT ON exercise_results
FOR EACH ROW
BEGIN
    DECLARE user_exists INT DEFAULT 0;
    
    -- Kiểm tra xem user đã có trong bảng stats chưa
    SELECT COUNT(*) INTO user_exists FROM learning_stats WHERE user_id = NEW.user_id;
    
    IF user_exists = 0 THEN
        -- Tạo record mới cho user
        INSERT INTO learning_stats (user_id, words_learned, correct_answers, total_answers, streak_days, last_study_date)
        VALUES (NEW.user_id, 0, 0, 0, 0, CURDATE());
    END IF;
    
    -- Cập nhật thống kê
    UPDATE learning_stats 
    SET 
        total_answers = total_answers + 1,
        correct_answers = correct_answers + IF(NEW.is_correct = 1, 1, 0),
        last_study_date = CURDATE(),
        updated_at = CURRENT_TIMESTAMP
    WHERE user_id = NEW.user_id;
    
    -- Cập nhật streak days
    UPDATE learning_stats 
    SET streak_days = streak_days + 1
    WHERE user_id = NEW.user_id 
    AND DATEDIFF(CURDATE(), last_study_date) = 1;
END//
DELIMITER ; 