-- Create listening exercises table
USE eduapp;
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

-- Create listening results table
CREATE TABLE IF NOT EXISTS listening_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    exercise_id INT NOT NULL,
    user_answer CHAR(1) NOT NULL,
    is_correct BOOLEAN NOT NULL,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (exercise_id) REFERENCES listening_exercises(id) ON DELETE CASCADE
);

-- Insert sample listening exercises
INSERT IGNORE INTO listening_exercises (title, question, audio_url, option_a, option_b, option_c, option_d, correct_answer, explanation) VALUES
('Basic Greeting', 'Nghe đoạn hội thoại và chọn câu trả lời đúng: Người nói đang làm gì?', 'tts:Hello, how are you today? I am fine, thank you.', 'Chào hỏi và hỏi thăm sức khỏe', 'Hỏi đường', 'Mua sắm', 'Đặt món ăn', 'A', 'Đoạn hội thoại là lời chào hỏi cơ bản "Hello, how are you today? I am fine, thank you."'),

('Numbers', 'Nghe và chọn số được đọc:', 'tts:Twenty five', '15', '25', '35', '45', 'B', 'Số được đọc là "twenty five" = 25'),

('Time', 'Nghe và chọn thời gian được đọc:', 'tts:It is three thirty in the afternoon', '3:00 PM', '3:30 PM', '3:15 PM', '3:45 PM', 'B', 'Thời gian được đọc là "three thirty in the afternoon" = 3:30 PM'),

('Weather', 'Nghe và chọn thời tiết được mô tả:', 'tts:Today is sunny and warm. It is a beautiful day.', 'Mưa và lạnh', 'Có nắng và ấm', 'Có mây và mát', 'Có tuyết và lạnh', 'B', 'Thời tiết được mô tả là "sunny and warm" = có nắng và ấm'),

('Food Order', 'Nghe đoạn hội thoại và chọn món ăn được đặt:', 'tts:I would like a hamburger and a cup of coffee, please.', 'Pizza và nước ngọt', 'Hamburger và cà phê', 'Sandwich và trà', 'Salad và nước', 'B', 'Món được đặt là "hamburger and a cup of coffee"'),

('Colors', 'Nghe và chọn màu được mô tả:', 'tts:The car is red and the house is blue.', 'Xe đỏ, nhà xanh', 'Xe xanh, nhà đỏ', 'Xe vàng, nhà xanh', 'Xe đỏ, nhà vàng', 'A', 'Xe màu đỏ (red) và nhà màu xanh (blue)'),

('Directions', 'Nghe và chọn hướng dẫn đúng:', 'tts:Go straight, then turn left at the traffic light.', 'Đi thẳng, rẽ phải', 'Đi thẳng, rẽ trái', 'Rẽ trái, đi thẳng', 'Rẽ phải, đi thẳng', 'B', 'Hướng dẫn là "go straight, then turn left" = đi thẳng, rẽ trái'),

('Family', 'Nghe và chọn thành viên gia đình được nhắc đến:', 'tts:My mother and father are at home with my sister.', 'Mẹ, cha, anh trai', 'Mẹ, cha, chị gái', 'Mẹ, cha, em trai', 'Mẹ, cha, em gái', 'B', 'Được nhắc đến: mother (mẹ), father (cha), sister (chị/em gái)'),

('Shopping', 'Nghe đoạn hội thoại mua sắm và chọn giá tiền:', 'tts:The book costs fifteen dollars and the pen costs three dollars.', '$15 và $3', '$50 và $13', '$15 và $30', '$5 và $3', 'A', 'Sách giá fifteen dollars ($15) và bút giá three dollars ($3)'),

('School', 'Nghe và chọn môn học được nhắc đến:', 'tts:I have math class at nine and English class at ten.', 'Toán và Khoa học', 'Toán và Tiếng Anh', 'Lịch sử và Tiếng Anh', 'Toán và Thể dục', 'B', 'Các môn học: math (toán) và English (tiếng Anh)');

-- Success message
SELECT 'Listening tables and sample data created successfully!' as Status,
       COUNT(*) as 'Total Listening Exercises' FROM listening_exercises;
