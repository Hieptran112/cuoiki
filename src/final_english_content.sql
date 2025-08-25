-- Final English Learning Content - Fixed All Issues
-- Date: 2025-08-20
-- Fixes: Foreign key constraints + Unknown column errors

USE eduapp;

-- First, let's check and create missing columns if needed
ALTER TABLE topic_progress 
ADD COLUMN IF NOT EXISTS last_accessed TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Clear existing data safely
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

-- Insert Topics
INSERT INTO topics (name, description, icon, color, is_active) VALUES
('Basic Vocabulary', 'Learn essential English words for daily communication', 'fas fa-book', '#4CAF50', 1),
('Grammar Fundamentals', 'Master basic English grammar rules and structures', 'fas fa-language', '#2196F3', 1),
('Everyday Conversations', 'Practice common English phrases and expressions', 'fas fa-comments', '#FF9800', 1),
('Listening Skills', 'Improve your English listening comprehension', 'fas fa-headphones', '#9C27B0', 1),
('Reading Comprehension', 'Develop your English reading skills', 'fas fa-book-open', '#F44336', 1);

-- Insert Lessons for Basic Vocabulary (topic_id = 1)
INSERT INTO topic_lessons (topic_id, lesson_number, title, description, difficulty) VALUES
(1, 1, 'Family Members', 'Learn words related to family relationships', 'beginner'),
(1, 2, 'Colors and Shapes', 'Basic colors and geometric shapes in English', 'beginner'),
(1, 3, 'Food and Drinks', 'Common food items and beverages', 'beginner'),
(1, 4, 'Animals', 'Domestic and wild animals vocabulary', 'beginner'),
(1, 5, 'Body Parts', 'Parts of the human body', 'beginner');

-- Insert Lessons for Grammar (topic_id = 2)
INSERT INTO topic_lessons (topic_id, lesson_number, title, description, difficulty) VALUES
(2, 1, 'Present Simple Tense', 'Learn to use present simple tense correctly', 'beginner'),
(2, 2, 'Articles and Adjectives', 'When and how to use English articles and adjectives', 'beginner'),
(2, 3, 'Question Formation', 'How to form questions in English', 'intermediate'),
(2, 4, 'Past Simple Tense', 'Learn past simple tense and irregular verbs', 'intermediate'),
(2, 5, 'Modal Verbs', 'Can, could, should, must, and their usage', 'intermediate');

-- Insert Lessons for Conversations (topic_id = 3)
INSERT INTO topic_lessons (topic_id, lesson_number, title, description, difficulty) VALUES
(3, 1, 'Greetings and Introductions', 'How to greet people and introduce yourself', 'beginner'),
(3, 2, 'Shopping', 'Useful phrases for shopping situations', 'beginner'),
(3, 3, 'Asking for Directions', 'How to ask for and give directions', 'intermediate'),
(3, 4, 'At the Restaurant', 'Ordering food and restaurant conversations', 'intermediate'),
(3, 5, 'Travel and Hotels', 'Booking hotels and travel conversations', 'advanced');

-- Insert Lessons for Listening (topic_id = 4)
INSERT INTO topic_lessons (topic_id, lesson_number, title, description, difficulty) VALUES
(4, 1, 'Basic Sounds', 'English phonetics and pronunciation', 'beginner'),
(4, 2, 'Short Dialogues', 'Understanding simple conversations', 'beginner'),
(4, 3, 'Numbers and Time', 'Listening for specific information', 'intermediate');

-- Insert Lessons for Reading (topic_id = 5)
INSERT INTO topic_lessons (topic_id, lesson_number, title, description, difficulty) VALUES
(5, 1, 'Simple Sentences', 'Reading and understanding basic sentences', 'beginner'),
(5, 2, 'Short Paragraphs', 'Reading short texts for main ideas', 'beginner');

-- Now insert exercises using the actual lesson IDs
-- Family Members Exercises (Topic 1, Lesson 1)
INSERT INTO topic_exercises (lesson_id, question_number, question, option_a, option_b, option_c, option_d, correct_answer, explanation_correct, explanation_wrong) VALUES
(1, 1, 'What does "mother" mean in Vietnamese?', 'Cha', 'Mẹ', 'Chị gái', 'Bà', 'B', 'Correct! "Mother" means "mẹ" in Vietnamese.', 'Incorrect. "Mother" means "mẹ" in Vietnamese.'),
(1, 2, 'What does "father" mean in Vietnamese?', 'Anh trai', 'Cha', 'Chú', 'Ông', 'B', 'Correct! "Father" means "cha" in Vietnamese.', 'Wrong. "Father" means "cha" in Vietnamese.'),
(1, 3, 'What does "sister" mean in Vietnamese?', 'Anh trai', 'Em trai', 'Chị/em gái', 'Bạn gái', 'C', 'Correct! "Sister" means "chị/em gái" in Vietnamese.', 'Incorrect. "Sister" means "chị/em gái" in Vietnamese.'),
(1, 4, 'What does "brother" mean in Vietnamese?', 'Anh/em trai', 'Anh họ', 'Chú', 'Cháu trai', 'A', 'Correct! "Brother" means "anh/em trai" in Vietnamese.', 'Wrong. "Brother" means "anh/em trai" in Vietnamese.'),
(1, 5, 'What does "grandmother" mean in Vietnamese?', 'Mẹ', 'Cô', 'Dì', 'Bà', 'D', 'Correct! "Grandmother" means "bà" in Vietnamese.', 'Incorrect. "Grandmother" means "bà" in Vietnamese.');

-- Colors and Shapes Exercises (Topic 1, Lesson 2)
INSERT INTO topic_exercises (lesson_id, question_number, question, option_a, option_b, option_c, option_d, correct_answer, explanation_correct, explanation_wrong) VALUES
(2, 1, 'What does "red" mean in Vietnamese?', 'Xanh lá', 'Đỏ', 'Vàng', 'Xanh dương', 'B', 'Correct! "Red" means "đỏ" in Vietnamese.', 'Incorrect. "Red" means "đỏ" in Vietnamese.'),
(2, 2, 'What does "green" mean in Vietnamese?', 'Xanh dương', 'Xanh lá', 'Vàng', 'Tím', 'B', 'Correct! "Green" means "xanh lá" in Vietnamese.', 'Wrong. "Green" means "xanh lá" in Vietnamese.'),
(2, 3, 'What does "circle" mean in Vietnamese?', 'Hình vuông', 'Hình tam giác', 'Hình tròn', 'Hình chữ nhật', 'C', 'Correct! "Circle" means "hình tròn" in Vietnamese.', 'Incorrect. "Circle" means "hình tròn" in Vietnamese.'),
(2, 4, 'What does "square" mean in Vietnamese?', 'Hình tam giác', 'Hình chữ nhật', 'Hình vuông', 'Hình tròn', 'C', 'Correct! "Square" means "hình vuông" in Vietnamese.', 'Wrong. "Square" means "hình vuông" in Vietnamese.'),
(2, 5, 'What is another word for "big"?', 'Small', 'Large', 'Tiny', 'Little', 'B', 'Correct! "Large" is a synonym for "big".', 'Wrong. "Large" means the same as "big".');

-- Food and Drinks Exercises (Topic 1, Lesson 3)
INSERT INTO topic_exercises (lesson_id, question_number, question, option_a, option_b, option_c, option_d, correct_answer, explanation_correct, explanation_wrong) VALUES
(3, 1, 'What does "apple" mean in Vietnamese?', 'Cam', 'Táo', 'Chuối', 'Nho', 'B', 'Correct! "Apple" means "táo" in Vietnamese.', 'Incorrect. "Apple" means "táo" in Vietnamese.'),
(3, 2, 'What does "water" mean in Vietnamese?', 'Sữa', 'Nước ép', 'Nước', 'Cà phê', 'C', 'Correct! "Water" means "nước" in Vietnamese.', 'Wrong. "Water" means "nước" in Vietnamese.'),
(3, 3, 'What does "bread" mean in Vietnamese?', 'Bánh mì', 'Cơm', 'Mì', 'Bánh ngọt', 'A', 'Correct! "Bread" means "bánh mì" in Vietnamese.', 'Incorrect. "Bread" means "bánh mì" in Vietnamese.'),
(3, 4, 'What does "egg" mean in Vietnamese?', 'Thịt', 'Cá', 'Trứng', 'Gà', 'C', 'Correct! "Egg" means "trứng" in Vietnamese.', 'Wrong. "Egg" means "trứng" in Vietnamese.'),
(3, 5, 'What is another word for "delicious"?', 'Terrible', 'Tasty', 'Bad', 'Ugly', 'B', 'Correct! "Tasty" is a synonym for "delicious".', 'Wrong. "Tasty" means the same as "delicious".');

-- Animals Exercises (Topic 1, Lesson 4)
INSERT INTO topic_exercises (lesson_id, question_number, question, option_a, option_b, option_c, option_d, correct_answer, explanation_correct, explanation_wrong) VALUES
(4, 1, 'What does "dog" mean in Vietnamese?', 'Mèo', 'Chó', 'Chim', 'Cá', 'B', 'Correct! "Dog" means "chó" in Vietnamese.', 'Incorrect. "Dog" means "chó" in Vietnamese.'),
(4, 2, 'What does "cat" mean in Vietnamese?', 'Mèo', 'Chim', 'Cá', 'Ngựa', 'A', 'Correct! "Cat" means "mèo" in Vietnamese.', 'Wrong. "Cat" means "mèo" in Vietnamese.'),
(4, 3, 'What does "elephant" mean in Vietnamese?', 'Sư tử', 'Voi', 'Hổ', 'Gấu', 'B', 'Correct! "Elephant" means "voi" in Vietnamese.', 'Incorrect. "Elephant" means "voi" in Vietnamese.'),
(4, 4, 'What does "bird" mean in Vietnamese?', 'Cá', 'Chim', 'Thỏ', 'Chuột', 'B', 'Correct! "Bird" means "chim" in Vietnamese.', 'Wrong. "Bird" means "chim" in Vietnamese.'),
(4, 5, 'What is another word for "fast"?', 'Slow', 'Quick', 'Lazy', 'Tired', 'B', 'Correct! "Quick" is a synonym for "fast".', 'Wrong. "Quick" means the same as "fast".');

-- Body Parts Exercises (Topic 1, Lesson 5)
INSERT INTO topic_exercises (lesson_id, question_number, question, option_a, option_b, option_c, option_d, correct_answer, explanation_correct, explanation_wrong) VALUES
(5, 1, 'What does "head" mean in Vietnamese?', 'Tay', 'Chân', 'Đầu', 'Mắt', 'C', 'Correct! "Head" means "đầu" in Vietnamese.', 'Incorrect. "Head" means "đầu" in Vietnamese.'),
(5, 2, 'What does "hand" mean in Vietnamese?', 'Chân', 'Tay', 'Cánh tay', 'Ngón tay', 'B', 'Correct! "Hand" means "tay" in Vietnamese.', 'Wrong. "Hand" means "tay" in Vietnamese.'),
(5, 3, 'What does "eye" mean in Vietnamese?', 'Tai', 'Mũi', 'Miệng', 'Mắt', 'D', 'Correct! "Eye" means "mắt" in Vietnamese.', 'Incorrect. "Eye" means "mắt" in Vietnamese.'),
(5, 4, 'What does "foot" mean in Vietnamese?', 'Tay', 'Cánh tay', 'Chân', 'Ngón tay', 'C', 'Correct! "Foot" means "chân" in Vietnamese.', 'Wrong. "Foot" means "chân" in Vietnamese.'),
(5, 5, 'What is another word for "small"?', 'Big', 'Tiny', 'Large', 'Huge', 'B', 'Correct! "Tiny" is a synonym for "small".', 'Wrong. "Tiny" means the same as "small".');

-- Grammar Exercises - Present Simple (Topic 2, Lesson 1)
INSERT INTO topic_exercises (lesson_id, question_number, question, option_a, option_b, option_c, option_d, correct_answer, explanation_correct, explanation_wrong) VALUES
(6, 1, 'Which sentence uses present simple correctly?', 'She go to school', 'She goes to school', 'She going to school', 'She is go to school', 'B', 'Correct! With third person singular, add "s" to the verb.', 'Wrong. Third person singular needs "s" added to the verb.'),
(6, 2, 'What is the negative form of "I like coffee"?', 'I not like coffee', 'I don\'t like coffee', 'I doesn\'t like coffee', 'I am not like coffee', 'B', 'Correct! Use "don\'t" with "I".', 'Wrong. Use "don\'t" with "I", not "doesn\'t".'),
(6, 3, 'How do you make a question from "He plays football"?', 'Does he play football?', 'Do he play football?', 'Is he play football?', 'Does he plays football?', 'A', 'Correct! Use "Does" + base form of verb.', 'Wrong. Use "Does" with third person singular + base verb.'),
(6, 4, 'Which sentence is correct?', 'They doesn\'t work here', 'They don\'t work here', 'They not work here', 'They aren\'t work here', 'B', 'Correct! Use "don\'t" with plural subjects.', 'Wrong. Use "don\'t" with plural subjects.'),
(6, 5, 'What is another word for "good"?', 'Bad', 'Excellent', 'Terrible', 'Awful', 'B', 'Correct! "Excellent" is a synonym for "good".', 'Wrong. "Excellent" means the same as "good".');

-- Grammar Exercises - Articles (Topic 2, Lesson 2)
INSERT INTO topic_exercises (lesson_id, question_number, question, option_a, option_b, option_c, option_d, correct_answer, explanation_correct, explanation_wrong) VALUES
(7, 1, 'Choose the correct article: "I have ___ apple"', 'a', 'an', 'the', 'no article', 'B', 'Correct! Use "an" before vowel sounds.', 'Wrong. "Apple" starts with a vowel sound, so use "an".'),
(7, 2, 'Choose the correct article: "___ sun is bright"', 'a', 'an', 'the', 'no article', 'C', 'Correct! Use "the" with unique things.', 'Wrong. "Sun" is unique, so use "the".'),
(7, 3, 'Choose the correct article: "She is ___ teacher"', 'a', 'an', 'the', 'no article', 'A', 'Correct! Use "a" before consonant sounds.', 'Wrong. "Teacher" starts with a consonant sound, so use "a".'),
(7, 4, 'What is another word for "happy"?', 'Sad', 'Joyful', 'Angry', 'Tired', 'B', 'Correct! "Joyful" is a synonym for "happy".', 'Wrong. "Joyful" means the same as "happy".'),
(7, 5, 'What is the opposite of "hot"?', 'Warm', 'Cool', 'Cold', 'Freezing', 'C', 'Correct! "Cold" is the opposite of "hot".', 'Wrong. "Cold" is the antonym of "hot".');

-- Conversation Exercises - Greetings (Topic 3, Lesson 1)
INSERT INTO topic_exercises (lesson_id, question_number, question, option_a, option_b, option_c, option_d, correct_answer, explanation_correct, explanation_wrong) VALUES
(11, 1, 'What is the most common greeting?', 'Good morning', 'Hello', 'How are you?', 'Nice to meet you', 'B', 'Correct! "Hello" is suitable for any time.', 'Wrong. "Hello" is the most common greeting.'),
(11, 2, 'How do you respond to "How are you?"', 'I am fine, thank you', 'Yes, please', 'You are welcome', 'Excuse me', 'A', 'Correct! "I am fine, thank you" is polite.', 'Wrong. The appropriate response is "I am fine, thank you".'),
(11, 3, 'How do you introduce your name?', 'What is your name?', 'My name is...', 'How old are you?', 'Where are you from?', 'B', 'Correct! "My name is..." introduces yourself.', 'Wrong. "My name is..." is how you introduce yourself.'),
(11, 4, 'What do you say when meeting someone for the first time?', 'See you later', 'Nice to meet you', 'How are you doing?', 'Take care', 'B', 'Correct! "Nice to meet you" is for first meetings.', 'Wrong. "Nice to meet you" is used for first meetings.'),
(11, 5, 'What is another way to say "goodbye"?', 'Hello', 'Good morning', 'See you later', 'Thank you', 'C', 'Correct! "See you later" is another way to say goodbye.', 'Wrong. "See you later" means the same as goodbye.');

-- Shopping Exercises (Topic 3, Lesson 2)
INSERT INTO topic_exercises (lesson_id, question_number, question, option_a, option_b, option_c, option_d, correct_answer, explanation_correct, explanation_wrong) VALUES
(12, 1, 'How do you ask for the price?', 'How much is it?', 'What is this?', 'Where is it?', 'When do you open?', 'A', 'Correct! "How much is it?" asks for price.', 'Wrong. "How much is it?" is how you ask for price.'),
(12, 2, 'How do you say "I want to buy this"?', 'I want this', 'I would like to buy this', 'I need help', 'I am looking', 'B', 'Correct! "I would like to buy this" is more polite.', 'Wrong. "I would like to buy this" is the polite way.'),
(12, 3, 'How do you ask about size?', 'What color?', 'What size?', 'What time?', 'What price?', 'B', 'Correct! "What size?" asks about size.', 'Wrong. "What size?" is how you ask about size.'),
(12, 4, 'How do you ask to try something on?', 'Can I try it on?', 'Can I see it?', 'Can I buy it?', 'Can I pay now?', 'A', 'Correct! "Can I try it on?" asks to try clothes.', 'Wrong. "Can I try it on?" is for trying clothes.'),
(12, 5, 'What is another word for "expensive"?', 'Cheap', 'Costly', 'Free', 'Affordable', 'B', 'Correct! "Costly" is a synonym for "expensive".', 'Wrong. "Costly" means the same as "expensive".');

-- Add exercises for Listening Skills lessons
-- Basic Sounds Exercises (lesson_id will be auto-assigned for topic_id=4, lesson_number=1)
INSERT INTO topic_exercises (lesson_id, question_number, question, option_a, option_b, option_c, option_d, correct_answer, explanation_correct, explanation_wrong) VALUES
((SELECT id FROM topic_lessons WHERE topic_id = 4 AND lesson_number = 1), 1, 'Which sound is /θ/ as in "think"?', 'Like "s"', 'Tongue between teeth', 'Like "f"', 'Like "t"', 'B', 'Correct! /θ/ is made with tongue between teeth.', 'Wrong. /θ/ requires tongue between teeth.'),
((SELECT id FROM topic_lessons WHERE topic_id = 4 AND lesson_number = 1), 2, 'What sound does "sh" make in "ship"?', '/s/', '/ʃ/', '/tʃ/', '/dʒ/', 'B', 'Correct! "sh" makes the /ʃ/ sound.', 'Wrong. "sh" makes the /ʃ/ sound.'),
((SELECT id FROM topic_lessons WHERE topic_id = 4 AND lesson_number = 1), 3, 'How is the final "s" pronounced in "cats"?', '/s/', '/z/', '/ɪz/', '/t/', 'A', 'Correct! After /t/, final "s" is pronounced /s/.', 'Wrong. After voiceless /t/, "s" is pronounced /s/.'),
((SELECT id FROM topic_lessons WHERE topic_id = 4 AND lesson_number = 1), 4, 'Which word has stress on the first syllable?', 'About', 'Begin', 'Happy', 'Forget', 'C', 'Correct! "Happy" has stress on the first syllable.', 'Wrong. "Happy" is stressed on the first syllable.'),
((SELECT id FROM topic_lessons WHERE topic_id = 4 AND lesson_number = 1), 5, 'How is American /r/ different from other languages?', 'Same as others', 'More tongue rolling', 'No tongue rolling', 'Made with lips', 'C', 'Correct! American /r/ has no tongue rolling.', 'Wrong. American /r/ does not roll the tongue.');

-- Short Dialogues Exercises (lesson_id will be auto-assigned for topic_id=4, lesson_number=2)
INSERT INTO topic_exercises (lesson_id, question_number, question, option_a, option_b, option_c, option_d, correct_answer, explanation_correct, explanation_wrong) VALUES
((SELECT id FROM topic_lessons WHERE topic_id = 4 AND lesson_number = 2), 1, 'In a greeting dialogue, what does "How are you?" expect as response?', 'Thank you', 'I am fine', 'You are welcome', 'Goodbye', 'B', 'Correct! "I am fine" is the typical response.', 'Wrong. "I am fine" is the expected response.'),
((SELECT id FROM topic_lessons WHERE topic_id = 4 AND lesson_number = 2), 2, 'What does "Nice to meet you" mean?', 'Goodbye', 'First meeting greeting', 'Thank you', 'Excuse me', 'B', 'Correct! It is used when meeting someone for the first time.', 'Wrong. It is used for first meetings.'),
((SELECT id FROM topic_lessons WHERE topic_id = 4 AND lesson_number = 2), 3, 'In "Can I help you?", what is the speaker offering?', 'Assistance', 'Food', 'Money', 'Directions', 'A', 'Correct! The speaker is offering help or assistance.', 'Wrong. The speaker is offering to help.'),
((SELECT id FROM topic_lessons WHERE topic_id = 4 AND lesson_number = 2), 4, 'What does "See you later" mean?', 'Hello', 'Goodbye', 'Thank you', 'Sorry', 'B', 'Correct! It is a way to say goodbye.', 'Wrong. It means goodbye.'),
((SELECT id FROM topic_lessons WHERE topic_id = 4 AND lesson_number = 2), 5, 'In "What time is it?", what information is requested?', 'Date', 'Time', 'Weather', 'Location', 'B', 'Correct! The question asks for the current time.', 'Wrong. The question is asking for time.');

-- Numbers and Time Exercises (lesson_id will be auto-assigned for topic_id=4, lesson_number=3)
INSERT INTO topic_exercises (lesson_id, question_number, question, option_a, option_b, option_c, option_d, correct_answer, explanation_correct, explanation_wrong) VALUES
((SELECT id FROM topic_lessons WHERE topic_id = 4 AND lesson_number = 3), 1, 'How do you say "15" in English?', 'Fifty', 'Fifteen', 'Fourteen', 'Sixteen', 'B', 'Correct! "15" is pronounced "fifteen".', 'Wrong. "15" is "fifteen".'),
((SELECT id FROM topic_lessons WHERE topic_id = 4 AND lesson_number = 3), 2, 'What time is "half past three"?', '3:30', '2:30', '3:15', '3:45', 'A', 'Correct! "Half past three" means 3:30.', 'Wrong. "Half past three" is 3:30.'),
((SELECT id FROM topic_lessons WHERE topic_id = 4 AND lesson_number = 3), 3, 'How do you say "20" in English?', 'Twelve', 'Twenty', 'Two', 'Thirty', 'B', 'Correct! "20" is pronounced "twenty".', 'Wrong. "20" is "twenty".'),
((SELECT id FROM topic_lessons WHERE topic_id = 4 AND lesson_number = 3), 4, 'What does "quarter to four" mean?', '3:45', '4:15', '4:45', '3:15', 'A', 'Correct! "Quarter to four" means 3:45.', 'Wrong. "Quarter to four" is 3:45.'),
((SELECT id FROM topic_lessons WHERE topic_id = 4 AND lesson_number = 3), 5, 'How do you say "100" in English?', 'Ten', 'Hundred', 'One hundred', 'Thousand', 'C', 'Correct! "100" is "one hundred".', 'Wrong. "100" is "one hundred".');

-- Add exercises for Reading Comprehension lessons
-- Simple Sentences Exercises (lesson_id will be auto-assigned for topic_id=5, lesson_number=1)
INSERT INTO topic_exercises (lesson_id, question_number, question, option_a, option_b, option_c, option_d, correct_answer, explanation_correct, explanation_wrong) VALUES
((SELECT id FROM topic_lessons WHERE topic_id = 5 AND lesson_number = 1), 1, 'What does this sentence mean: "The cat is on the mat"?', 'Cat under mat', 'Cat above mat', 'Cat beside mat', 'Cat inside mat', 'B', 'Correct! "On" means above or on top of.', 'Wrong. "On" indicates the cat is above the mat.'),
((SELECT id FROM topic_lessons WHERE topic_id = 5 AND lesson_number = 1), 2, 'In "She runs fast", what does "fast" describe?', 'She', 'Runs', 'Time', 'Place', 'B', 'Correct! "Fast" describes how she runs.', 'Wrong. "Fast" is an adverb describing the verb "runs".'),
((SELECT id FROM topic_lessons WHERE topic_id = 5 AND lesson_number = 1), 3, 'What is the subject in "Dogs are loyal pets"?', 'Are', 'Loyal', 'Dogs', 'Pets', 'C', 'Correct! "Dogs" is the subject of the sentence.', 'Wrong. "Dogs" is what the sentence is about.'),
((SELECT id FROM topic_lessons WHERE topic_id = 5 AND lesson_number = 1), 4, 'What does "because" show in a sentence?', 'Time', 'Reason', 'Place', 'Manner', 'B', 'Correct! "Because" shows the reason for something.', 'Wrong. "Because" indicates a reason or cause.'),
((SELECT id FROM topic_lessons WHERE topic_id = 5 AND lesson_number = 1), 5, 'In "The big red car", which words describe the car?', 'The, car', 'Big, red', 'Big, car', 'Red, car', 'B', 'Correct! "Big" and "red" are adjectives describing the car.', 'Wrong. "Big" and "red" are the descriptive words.');

-- Short Paragraphs Exercises (lesson_id will be auto-assigned for topic_id=5, lesson_number=2)
INSERT INTO topic_exercises (lesson_id, question_number, question, option_a, option_b, option_c, option_d, correct_answer, explanation_correct, explanation_wrong) VALUES
((SELECT id FROM topic_lessons WHERE topic_id = 5 AND lesson_number = 2), 1, 'Read: "Tom likes apples. He eats them every day." What does Tom do every day?', 'Likes apples', 'Eats apples', 'Buys apples', 'Grows apples', 'B', 'Correct! Tom eats apples every day.', 'Wrong. The text says he eats them every day.'),
((SELECT id FROM topic_lessons WHERE topic_id = 5 AND lesson_number = 2), 2, 'Read: "The weather is sunny. People go to the beach." Why do people go to the beach?', 'It is cold', 'It is sunny', 'It is raining', 'It is windy', 'B', 'Correct! People go because the weather is sunny.', 'Wrong. The sunny weather is the reason.'),
((SELECT id FROM topic_lessons WHERE topic_id = 5 AND lesson_number = 2), 3, 'Read: "Mary studies hard. She gets good grades." What is the result of studying hard?', 'Bad grades', 'Good grades', 'No grades', 'Average grades', 'B', 'Correct! Studying hard results in good grades.', 'Wrong. Good grades are the result of studying hard.'),
((SELECT id FROM topic_lessons WHERE topic_id = 5 AND lesson_number = 2), 4, 'Read: "The store opens at 9 AM. It closes at 6 PM." How long is the store open?', '8 hours', '9 hours', '6 hours', '10 hours', 'B', 'Correct! From 9 AM to 6 PM is 9 hours.', 'Wrong. 9 AM to 6 PM is 9 hours.'),
((SELECT id FROM topic_lessons WHERE topic_id = 5 AND lesson_number = 2), 5, 'Read: "John has a dog. The dog is brown and friendly." What do we know about the dog?', 'It is black', 'It is brown and friendly', 'It is small', 'It is old', 'B', 'Correct! The dog is described as brown and friendly.', 'Wrong. The text says the dog is brown and friendly.');

-- Success message
SELECT 'Final English Learning Content Applied Successfully!' as Status,
       COUNT(*) as 'Total Topics' FROM topics WHERE is_active = 1
UNION ALL
SELECT 'Total Lessons Created:', COUNT(*) FROM topic_lessons
UNION ALL
SELECT 'Total Exercises Created:', COUNT(*) FROM topic_exercises;
