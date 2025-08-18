-- Cập nhật tất cả câu trả lời từ tiếng Anh sang tiếng Việt
-- Ngày: 2025-08-18

-- Cập nhật câu hỏi bài 1: Cơ bản về máy tính
UPDATE topic_exercises SET 
    option_a = 'Bộ xử lý trung tâm',
    option_b = 'Bộ xử lý máy tính', 
    option_c = 'Bộ chương trình trung tâm',
    option_d = 'Bộ chương trình máy tính'
WHERE lesson_id = 1 AND question_number = 1;

UPDATE topic_exercises SET 
    option_a = 'Sổ tay',
    option_b = 'WinRAR',
    option_c = 'Máy tính',
    option_d = 'Sơn'
WHERE lesson_id = 1 AND question_number = 11;

UPDATE topic_exercises SET 
    option_a = 'Từ điển toàn cầu',
    option_b = 'Định vị tài nguyên thống nhất',
    option_c = 'Liên kết tham chiếu toàn cầu',
    option_d = 'Định vị tham chiếu thống nhất'
WHERE lesson_id = 1 AND question_number = 10;

-- Cập nhật câu hỏi bài 2: Mạng máy tính
UPDATE topic_exercises SET 
    option_a = 'Giao thức Internet',
    option_b = 'Chương trình nội bộ',
    option_c = 'Chương trình Internet',
    option_d = 'Giao thức nội bộ'
WHERE lesson_id = 2 AND question_number = 1;

UPDATE topic_exercises SET 
    option_a = 'Mạng diện rộng',
    option_b = 'Mạng cục bộ',
    option_c = 'Mạng tầm xa',
    option_d = 'Mạng giới hạn'
WHERE lesson_id = 2 AND question_number = 6;

UPDATE topic_exercises SET 
    option_a = 'Mạng riêng ảo',
    option_b = 'Mạng riêng rất tốt',
    option_c = 'Mạng công cộng ảo',
    option_d = 'Mạng công cộng rất tốt'
WHERE lesson_id = 2 AND question_number = 15;

-- Cập nhật các câu hỏi có từ viết tắt tiếng Anh khác
UPDATE topic_exercises SET 
    option_a = 'HTTPS',
    option_b = 'HTTP',
    option_c = 'FTP',
    option_d = 'SMTP'
WHERE lesson_id = 2 AND question_number = 5;

UPDATE topic_exercises SET 
    option_a = 'HTTP',
    option_b = 'FTP',
    option_c = 'SMTP',
    option_d = 'DNS'
WHERE lesson_id = 2 AND question_number = 10;

-- Cập nhật các câu hỏi có tên phần mềm/hệ điều hành
UPDATE topic_exercises SET 
    option_a = 'Microsoft Word',
    option_b = 'Adobe Photoshop',
    option_c = 'Google Chrome',
    option_d = 'VLC Media Player'
WHERE lesson_id = 1 AND question_number = 5;

UPDATE topic_exercises SET 
    option_a = 'Windows',
    option_b = 'macOS',
    option_c = 'Linux',
    option_d = 'iOS'
WHERE lesson_id = 1 AND question_number = 4;

UPDATE topic_exercises SET 
    option_a = 'Microsoft Word',
    option_b = 'MySQL',
    option_c = 'Adobe Reader',
    option_d = 'Skype'
WHERE lesson_id = 1 AND question_number = 13;

-- Cập nhật các đơn vị đo lường
UPDATE topic_exercises SET 
    option_a = 'Bit',
    option_b = 'Byte',
    option_c = 'Kilobyte',
    option_d = 'Megabyte'
WHERE lesson_id = 1 AND question_number = 3;

UPDATE topic_exercises SET 
    option_a = '32 bit',
    option_b = '48 bit',
    option_c = '64 bit',
    option_d = '128 bit'
WHERE lesson_id = 2 AND question_number = 14;
