-- Cập nhật hệ thống topic và bài học
-- Ngày: 2025-08-17

-- Bảng chủ đề (topics)
CREATE TABLE IF NOT EXISTS topics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    icon VARCHAR(100), -- Font Awesome icon class
    color VARCHAR(7) DEFAULT '#007bff', -- Màu chủ đề (hex)
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_active (is_active)
);

-- Bảng bài học trong topic
CREATE TABLE IF NOT EXISTS topic_lessons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    topic_id INT NOT NULL,
    lesson_number INT NOT NULL, -- Số thứ tự bài học (1, 2, 3...)
    title VARCHAR(300) NOT NULL,
    description TEXT,
    difficulty ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE,
    UNIQUE KEY unique_topic_lesson (topic_id, lesson_number),
    INDEX idx_topic_id (topic_id),
    INDEX idx_lesson_number (lesson_number),
    INDEX idx_active (is_active)
);

-- Bảng câu hỏi cho bài học
CREATE TABLE IF NOT EXISTS topic_exercises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lesson_id INT NOT NULL,
    question_number INT NOT NULL, -- Số thứ tự câu hỏi trong bài (1-15)
    question TEXT NOT NULL,
    option_a VARCHAR(500) NOT NULL,
    option_b VARCHAR(500) NOT NULL,
    option_c VARCHAR(500) NOT NULL,
    option_d VARCHAR(500) NOT NULL,
    correct_answer ENUM('A', 'B', 'C', 'D') NOT NULL,
    explanation_correct TEXT, -- Giải thích khi trả lời đúng
    explanation_wrong TEXT, -- Giải thích khi trả lời sai
    difficulty ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lesson_id) REFERENCES topic_lessons(id) ON DELETE CASCADE,
    UNIQUE KEY unique_lesson_question (lesson_id, question_number),
    INDEX idx_lesson_id (lesson_id),
    INDEX idx_question_number (question_number)
);

-- Bảng kết quả bài tập theo topic
CREATE TABLE IF NOT EXISTS topic_exercise_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    lesson_id INT NOT NULL,
    exercise_id INT NOT NULL,
    selected_answer ENUM('A', 'B', 'C', 'D') NOT NULL,
    is_correct BOOLEAN NOT NULL,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES topic_lessons(id) ON DELETE CASCADE,
    FOREIGN KEY (exercise_id) REFERENCES topic_exercises(id) ON DELETE CASCADE,
    INDEX idx_user_lesson (user_id, lesson_id),
    INDEX idx_completed_at (completed_at)
);

-- Bảng tiến độ học tập theo topic
CREATE TABLE IF NOT EXISTS topic_progress (
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
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES topic_lessons(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_lesson (user_id, lesson_id),
    INDEX idx_user_topic (user_id, topic_id),
    INDEX idx_completion (is_completed)
);

-- Thêm trường topic_id vào bảng dictionary để liên kết từ vựng với chủ đề
ALTER TABLE dictionary
ADD COLUMN topic_id INT NULL AFTER difficulty,
ADD INDEX idx_topic_id (topic_id),
ADD CONSTRAINT fk_dictionary_topic
    FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE SET NULL;

-- Dữ liệu mẫu cho topics
INSERT INTO topics (name, description, icon, color) VALUES
('Công nghệ thông tin', 'Các thuật ngữ và khái niệm về công nghệ thông tin, lập trình, mạng máy tính', 'fas fa-laptop-code', '#007bff'),
('Kinh tế', 'Thuật ngữ kinh tế, tài chính, thương mại và quản trị kinh doanh', 'fas fa-chart-line', '#28a745'),
('Y học', 'Thuật ngữ y khoa, giải phẫu, bệnh học và chăm sóc sức khỏe', 'fas fa-heartbeat', '#dc3545'),
('Khoa học tự nhiên', 'Vật lý, hóa học, sinh học và các khoa học tự nhiên khác', 'fas fa-atom', '#6f42c1'),
('Ngôn ngữ học', 'Ngữ pháp, từ vựng và các khái niệm ngôn ngữ học', 'fas fa-language', '#fd7e14'),
('Lịch sử - Địa lý', 'Sự kiện lịch sử, địa danh và kiến thức xã hội', 'fas fa-globe-americas', '#20c997');

-- Dữ liệu mẫu cho bài học - Công nghệ thông tin
INSERT INTO topic_lessons (topic_id, lesson_number, title, description, difficulty) VALUES
(1, 1, 'Cơ bản về máy tính', 'Các khái niệm cơ bản về phần cứng và phần mềm máy tính', 'beginner'),
(1, 2, 'Mạng máy tính', 'Các khái niệm về mạng, internet và giao thức mạng', 'intermediate'),
(1, 3, 'Lập trình cơ bản', 'Các thuật ngữ và khái niệm lập trình cơ bản', 'intermediate'),
(1, 4, 'Cơ sở dữ liệu', 'Các khái niệm về cơ sở dữ liệu và SQL', 'advanced'),
(1, 5, 'Bảo mật thông tin', 'Các khái niệm về an ninh mạng và bảo mật', 'advanced');

-- Dữ liệu mẫu cho bài học - Kinh tế
INSERT INTO topic_lessons (topic_id, lesson_number, title, description, difficulty) VALUES
(2, 1, 'Kinh tế vi mô', 'Các khái niệm cơ bản về kinh tế vi mô', 'beginner'),
(2, 2, 'Kinh tế vĩ mô', 'Các khái niệm về kinh tế vĩ mô và chính sách', 'intermediate'),
(2, 3, 'Tài chính ngân hàng', 'Các thuật ngữ về tài chính và ngân hàng', 'intermediate'),
(2, 4, 'Thương mại quốc tế', 'Các khái niệm về thương mại và xuất nhập khẩu', 'advanced'),
(2, 5, 'Quản trị kinh doanh', 'Các thuật ngữ về quản lý và điều hành doanh nghiệp', 'advanced');

-- Dữ liệu mẫu cho bài học - Y học
INSERT INTO topic_lessons (topic_id, lesson_number, title, description, difficulty) VALUES
(3, 1, 'Giải phẫu cơ bản', 'Các thuật ngữ giải phẫu cơ bản của cơ thể người', 'beginner'),
(3, 2, 'Sinh lý học', 'Các khái niệm về chức năng của các cơ quan', 'intermediate'),
(3, 3, 'Bệnh học', 'Các thuật ngữ về bệnh tật và triệu chứng', 'intermediate'),
(3, 4, 'Dược học', 'Các khái niệm về thuốc và điều trị', 'advanced'),
(3, 5, 'Y học lâm sàng', 'Các thuật ngữ chuyên ngành lâm sàng', 'advanced');