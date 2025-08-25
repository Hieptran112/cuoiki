-- English Learning Content for Language Learning App
-- Fixed version with proper foreign keys and English questions
-- Date: 2025-08-20

USE eduapp;

-- Add missing column if needed
ALTER TABLE topic_progress 
ADD COLUMN IF NOT EXISTS last_accessed TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Clear existing content safely
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM topic_exercise_results WHERE 1=1;
DELETE FROM topic_progress WHERE 1=1;
DELETE FROM topic_exercises WHERE 1=1;
DELETE FROM topic_lessons WHERE 1=1;
DELETE FROM topics WHERE 1=1;
SET FOREIGN_KEY_CHECKS = 1;

-- Reset auto increment
ALTER TABLE topics AUTO_INCREMENT = 1;
ALTER TABLE topic_lessons AUTO_INCREMENT = 1;
ALTER TABLE topic_exercises AUTO_INCREMENT = 1;

-- Create proper English learning topics
INSERT INTO topics (name, description, icon, color, is_active) VALUES
('Basic Vocabulary', 'Learn essential English words for daily communication', 'fas fa-book', '#4CAF50', 1),
('Grammar Fundamentals', 'Master basic English grammar rules and structures', 'fas fa-language', '#2196F3', 1),
('Everyday Conversations', 'Practice common English phrases and expressions', 'fas fa-comments', '#FF9800', 1),
('Listening Skills', 'Improve your English listening comprehension', 'fas fa-headphones', '#9C27B0', 1),
('Reading Comprehension', 'Develop your English reading skills', 'fas fa-book-open', '#F44336', 1);

-- Create lessons for Basic Vocabulary
INSERT INTO topic_lessons (topic_id, lesson_number, title, description, difficulty) VALUES
(1, 1, 'Family Members', 'Learn words related to family relationships', 'beginner'),
(1, 2, 'Colors and Shapes', 'Basic colors and geometric shapes in English', 'beginner'),
(1, 3, 'Food and Drinks', 'Common food items and beverages', 'beginner'),
(1, 4, 'Animals', 'Domestic and wild animals vocabulary', 'beginner'),
(1, 5, 'Body Parts', 'Parts of the human body', 'intermediate');

-- Create lessons for Grammar Fundamentals  
INSERT INTO topic_lessons (topic_id, lesson_number, title, description, difficulty) VALUES
(2, 1, 'Present Simple Tense', 'Learn to use present simple tense correctly', 'beginner'),
(2, 2, 'Articles (a, an, the)', 'When and how to use English articles', 'beginner'),
(2, 3, 'Plural Forms', 'Regular and irregular plural forms', 'intermediate'),
(2, 4, 'Past Simple Tense', 'Learn past simple tense and irregular verbs', 'intermediate'),
(2, 5, 'Question Formation', 'How to form questions in English', 'intermediate');

-- Create lessons for Everyday Conversations
INSERT INTO topic_lessons (topic_id, lesson_number, title, description, difficulty) VALUES
(3, 1, 'Greetings and Introductions', 'How to greet people and introduce yourself', 'beginner'),
(3, 2, 'Shopping and Prices', 'Useful phrases for shopping situations', 'beginner'),
(3, 3, 'Asking for Directions', 'How to ask for and give directions', 'intermediate'),
(3, 4, 'At the Restaurant', 'Ordering food and restaurant conversations', 'intermediate'),
(3, 5, 'Making Appointments', 'Scheduling meetings and appointments', 'advanced');

-- Create lessons for Listening Skills
INSERT INTO topic_lessons (topic_id, lesson_number, title, description, difficulty) VALUES
(4, 1, 'Basic Sounds', 'English phonetics and pronunciation', 'beginner'),
(4, 2, 'Short Dialogues', 'Understanding simple conversations', 'beginner'),
(4, 3, 'Numbers and Time', 'Listening for specific information', 'intermediate'),
(4, 4, 'Weather Reports', 'Understanding weather forecasts', 'intermediate'),
(4, 5, 'News Headlines', 'Listening to news and current events', 'advanced');

-- Create lessons for Reading Comprehension
INSERT INTO topic_lessons (topic_id, lesson_number, title, description, difficulty) VALUES
(5, 1, 'Simple Sentences', 'Reading and understanding basic sentences', 'beginner'),
(5, 2, 'Short Paragraphs', 'Reading short texts for main ideas', 'beginner'),
(5, 3, 'Signs and Labels', 'Understanding public signs and product labels', 'intermediate'),
(5, 4, 'Emails and Messages', 'Reading personal and business emails', 'intermediate'),
(5, 5, 'News Articles', 'Reading and understanding news stories', 'advanced');

-- Sample exercises for Family Members lesson (lesson_id = 1)
INSERT INTO topic_exercises (lesson_id, question_number, question, option_a, option_b, option_c, option_d, correct_answer, explanation_correct, explanation_wrong) VALUES
(1, 1, 'What does "mother" mean in Vietnamese?', 'Cha', 'Mẹ', 'Chị gái', 'Bà', 'B', 'Correct! "Mother" means "mẹ" in Vietnamese.', 'Incorrect. "Mother" means "mẹ" in Vietnamese.'),
(1, 2, 'What does "father" mean in Vietnamese?', 'Anh trai', 'Cha', 'Chú', 'Ông', 'B', 'Correct! "Father" means "cha" in Vietnamese.', 'Wrong. "Father" means "cha" in Vietnamese.'),
(1, 3, 'What does "sister" mean in Vietnamese?', 'Anh trai', 'Em trai', 'Chị/em gái', 'Bạn gái', 'C', 'Correct! "Sister" means "chị/em gái" in Vietnamese.', 'Incorrect. "Sister" means "chị/em gái" in Vietnamese.'),
(1, 4, 'What does "brother" mean in Vietnamese?', 'Anh/em trai', 'Anh họ', 'Chú', 'Cháu trai', 'A', 'Correct! "Brother" means "anh/em trai" in Vietnamese.', 'Wrong. "Brother" means "anh/em trai" in Vietnamese.'),
(1, 5, 'What does "grandmother" mean in Vietnamese?', 'Mẹ', 'Cô', 'Dì', 'Bà', 'D', 'Correct! "Grandmother" means "bà" in Vietnamese.', 'Incorrect. "Grandmother" means "bà" in Vietnamese.');

-- Sample exercises for Colors and Shapes lesson (lesson_id = 2)
INSERT INTO topic_exercises (lesson_id, question_number, question, option_a, option_b, option_c, option_d, correct_answer, explanation_correct, explanation_wrong) VALUES
(2, 1, 'What does "red" mean in Vietnamese?', 'Xanh lá', 'Đỏ', 'Vàng', 'Xanh dương', 'B', 'Correct! "Red" means "đỏ" in Vietnamese.', 'Incorrect. "Red" means "đỏ" in Vietnamese.'),
(2, 2, 'What does "green" mean in Vietnamese?', 'Xanh dương', 'Xanh lá', 'Vàng', 'Tím', 'B', 'Correct! "Green" means "xanh lá" in Vietnamese.', 'Wrong. "Green" means "xanh lá" in Vietnamese.'),
(2, 3, 'What does "circle" mean in Vietnamese?', 'Hình vuông', 'Hình tam giác', 'Hình tròn', 'Hình chữ nhật', 'C', 'Correct! "Circle" means "hình tròn" in Vietnamese.', 'Incorrect. "Circle" means "hình tròn" in Vietnamese.'),
(2, 4, 'What does "square" mean in Vietnamese?', 'Hình tam giác', 'Hình chữ nhật', 'Hình vuông', 'Hình tròn', 'C', 'Correct! "Square" means "hình vuông" in Vietnamese.', 'Wrong. "Square" means "hình vuông" in Vietnamese.'),
(2, 5, 'What is another word for "big"?', 'Small', 'Large', 'Tiny', 'Little', 'B', 'Correct! "Large" is a synonym for "big".', 'Wrong. "Large" means the same as "big".');

-- Sample exercises for Present Simple Tense lesson (lesson_id = 6)
INSERT INTO topic_exercises (lesson_id, question_number, question, option_a, option_b, option_c, option_d, correct_answer, explanation_correct, explanation_wrong) VALUES
(6, 1, 'Which sentence uses present simple correctly?', 'She go to school', 'She goes to school', 'She going to school', 'She is go to school', 'B', 'Correct! With third person singular, add "s" to the verb.', 'Wrong. Third person singular needs "s" added to the verb.'),
(6, 2, 'What is the negative form of "I like coffee"?', 'I not like coffee', 'I don\'t like coffee', 'I doesn\'t like coffee', 'I am not like coffee', 'B', 'Correct! Use "don\'t" with "I".', 'Wrong. Use "don\'t" with "I", not "doesn\'t".'),
(6, 3, 'How do you make a question from "He plays football"?', 'Does he play football?', 'Do he play football?', 'Is he play football?', 'Does he plays football?', 'A', 'Correct! Use "Does" + base form of verb.', 'Wrong. Use "Does" with third person singular + base verb.'),
(6, 4, 'Which sentence is correct?', 'They doesn\'t work here', 'They don\'t work here', 'They not work here', 'They aren\'t work here', 'B', 'Correct! Use "don\'t" with plural subjects.', 'Wrong. Use "don\'t" with plural subjects.'),
(6, 5, 'What is another word for "good"?', 'Bad', 'Excellent', 'Terrible', 'Awful', 'B', 'Correct! "Excellent" is a synonym for "good".', 'Wrong. "Excellent" means the same as "good".');

-- Sample exercises for Greetings and Introductions lesson (lesson_id = 11)
INSERT INTO topic_exercises (lesson_id, question_number, question, option_a, option_b, option_c, option_d, correct_answer, explanation_correct, explanation_wrong) VALUES
(11, 1, 'What is the most common greeting?', 'Good morning', 'Hello', 'How are you?', 'Nice to meet you', 'B', 'Correct! "Hello" is suitable for any time.', 'Wrong. "Hello" is the most common greeting.'),
(11, 2, 'How do you respond to "How are you?"', 'I am fine, thank you', 'Yes, please', 'You are welcome', 'Excuse me', 'A', 'Correct! "I am fine, thank you" is polite.', 'Wrong. The appropriate response is "I am fine, thank you".'),
(11, 3, 'How do you introduce your name?', 'What is your name?', 'My name is...', 'How old are you?', 'Where are you from?', 'B', 'Correct! "My name is..." introduces yourself.', 'Wrong. "My name is..." is how you introduce yourself.'),
(11, 4, 'What do you say when meeting someone for the first time?', 'See you later', 'Nice to meet you', 'How are you doing?', 'Take care', 'B', 'Correct! "Nice to meet you" is for first meetings.', 'Wrong. "Nice to meet you" is used for first meetings.'),
(11, 5, 'What is another way to say "goodbye"?', 'Hello', 'Good morning', 'See you later', 'Thank you', 'C', 'Correct! "See you later" is another way to say goodbye.', 'Wrong. "See you later" means the same as goodbye.');

-- Add more exercises for Food and Drinks lesson (lesson_id = 3)
INSERT INTO topic_exercises (lesson_id, question_number, question, option_a, option_b, option_c, option_d, correct_answer, explanation_correct, explanation_wrong) VALUES
(3, 1, 'What does "apple" mean in Vietnamese?', 'Cam', 'Táo', 'Chuối', 'Nho', 'B', 'Correct! "Apple" means "táo" in Vietnamese.', 'Incorrect. "Apple" means "táo" in Vietnamese.'),
(3, 2, 'What does "water" mean in Vietnamese?', 'Sữa', 'Nước ép', 'Nước', 'Cà phê', 'C', 'Correct! "Water" means "nước" in Vietnamese.', 'Wrong. "Water" means "nước" in Vietnamese.'),
(3, 3, 'What does "bread" mean in Vietnamese?', 'Bánh mì', 'Cơm', 'Mì', 'Bánh ngọt', 'A', 'Correct! "Bread" means "bánh mì" in Vietnamese.', 'Incorrect. "Bread" means "bánh mì" in Vietnamese.'),
(3, 4, 'What does "egg" mean in Vietnamese?', 'Thịt', 'Cá', 'Trứng', 'Gà', 'C', 'Correct! "Egg" means "trứng" in Vietnamese.', 'Wrong. "Egg" means "trứng" in Vietnamese.'),
(3, 5, 'What is another word for "delicious"?', 'Terrible', 'Tasty', 'Bad', 'Ugly', 'B', 'Correct! "Tasty" is a synonym for "delicious".', 'Wrong. "Tasty" means the same as "delicious".');

-- Add exercises for Animals lesson (lesson_id = 4)
INSERT INTO topic_exercises (lesson_id, question_number, question, option_a, option_b, option_c, option_d, correct_answer, explanation_correct, explanation_wrong) VALUES
(4, 1, 'What does "dog" mean in Vietnamese?', 'Mèo', 'Chó', 'Chim', 'Cá', 'B', 'Correct! "Dog" means "chó" in Vietnamese.', 'Incorrect. "Dog" means "chó" in Vietnamese.'),
(4, 2, 'What does "cat" mean in Vietnamese?', 'Mèo', 'Chim', 'Cá', 'Ngựa', 'A', 'Correct! "Cat" means "mèo" in Vietnamese.', 'Wrong. "Cat" means "mèo" in Vietnamese.'),
(4, 3, 'What does "elephant" mean in Vietnamese?', 'Sư tử', 'Voi', 'Hổ', 'Gấu', 'B', 'Correct! "Elephant" means "voi" in Vietnamese.', 'Incorrect. "Elephant" means "voi" in Vietnamese.'),
(4, 4, 'What does "bird" mean in Vietnamese?', 'Cá', 'Chim', 'Thỏ', 'Chuột', 'B', 'Correct! "Bird" means "chim" in Vietnamese.', 'Wrong. "Bird" means "chim" in Vietnamese.'),
(4, 5, 'What is another word for "fast"?', 'Slow', 'Quick', 'Lazy', 'Tired', 'B', 'Correct! "Quick" is a synonym for "fast".', 'Wrong. "Quick" means the same as "fast".');

-- Add exercises for Listening Skills lessons (lesson_id = 16-20)
INSERT INTO topic_exercises (lesson_id, question_number, question, option_a, option_b, option_c, option_d, correct_answer, explanation_correct, explanation_wrong) VALUES
(16, 1, 'Which sound is /θ/ as in "think"?', 'Like "s"', 'Tongue between teeth', 'Like "f"', 'Like "t"', 'B', 'Correct! /θ/ is made with tongue between teeth.', 'Wrong. /θ/ requires tongue between teeth.'),
(16, 2, 'What sound does "sh" make in "ship"?', '/s/', '/ʃ/', '/tʃ/', '/dʒ/', 'B', 'Correct! "sh" makes the /ʃ/ sound.', 'Wrong. "sh" makes the /ʃ/ sound.'),
(16, 3, 'How is the final "s" pronounced in "cats"?', '/s/', '/z/', '/ɪz/', '/t/', 'A', 'Correct! After /t/, final "s" is pronounced /s/.', 'Wrong. After voiceless /t/, "s" is pronounced /s/.'),
(16, 4, 'Which word has stress on the first syllable?', 'About', 'Begin', 'Happy', 'Forget', 'C', 'Correct! "Happy" has stress on the first syllable.', 'Wrong. "Happy" is stressed on the first syllable.'),
(16, 5, 'How is American /r/ different from other languages?', 'Same as others', 'More tongue rolling', 'No tongue rolling', 'Made with lips', 'C', 'Correct! American /r/ has no tongue rolling.', 'Wrong. American /r/ does not roll the tongue.');

-- Add exercises for Reading Comprehension lessons (lesson_id = 21-25)
INSERT INTO topic_exercises (lesson_id, question_number, question, option_a, option_b, option_c, option_d, correct_answer, explanation_correct, explanation_wrong) VALUES
(21, 1, 'What does this sentence mean: "The cat is on the mat"?', 'Cat under mat', 'Cat above mat', 'Cat beside mat', 'Cat inside mat', 'B', 'Correct! "On" means above or on top of.', 'Wrong. "On" indicates the cat is above the mat.'),
(21, 2, 'In "She runs fast", what does "fast" describe?', 'She', 'Runs', 'Time', 'Place', 'B', 'Correct! "Fast" describes how she runs.', 'Wrong. "Fast" is an adverb describing the verb "runs".'),
(21, 3, 'What is the main idea of: "Dogs are loyal pets. They protect homes."?', 'Dogs are animals', 'Dogs are good pets', 'Dogs are big', 'Dogs are cute', 'B', 'Correct! The text shows dogs are good pets.', 'Wrong. The main idea is about dogs being good pets.'),
(21, 4, 'What does "because" show in a sentence?', 'Time', 'Reason', 'Place', 'Manner', 'B', 'Correct! "Because" shows the reason for something.', 'Wrong. "Because" indicates a reason or cause.'),
(21, 5, 'In "The big red car", which words describe the car?', 'The, car', 'Big, red', 'Big, car', 'Red, car', 'B', 'Correct! "Big" and "red" are adjectives describing the car.', 'Wrong. "Big" and "red" are the descriptive words.');

-- Success message
SELECT 'English Learning Content Applied Successfully!' as Status,
       COUNT(*) as 'Total Topics' FROM topics WHERE is_active = 1
UNION ALL
SELECT 'Total Lessons Created:', COUNT(*) FROM topic_lessons
UNION ALL  
SELECT 'Total Exercises Created:', COUNT(*) FROM topic_exercises;
