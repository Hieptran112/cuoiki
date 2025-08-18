-- Fix learning stats trigger to properly track words learned
USE eduapp;

-- Drop existing trigger
DROP TRIGGER IF EXISTS update_learning_stats;

-- Create improved trigger
DELIMITER //
CREATE TRIGGER update_learning_stats
AFTER INSERT ON exercise_results
FOR EACH ROW
BEGIN
    DECLARE user_exists INT DEFAULT 0;
    DECLARE unique_words_today INT DEFAULT 0;
    
    -- Kiểm tra xem user đã có trong bảng stats chưa
    SELECT COUNT(*) INTO user_exists FROM learning_stats WHERE user_id = NEW.user_id;
    
    IF user_exists = 0 THEN
        -- Tạo record mới cho user
        INSERT INTO learning_stats (user_id, words_learned, correct_answers, total_answers, streak_days, last_study_date)
        VALUES (NEW.user_id, 0, 0, 0, 0, CURDATE());
    END IF;
    
    -- Đếm số từ unique mà user đã trả lời đúng hôm nay
    SELECT COUNT(DISTINCT er.exercise_id) INTO unique_words_today
    FROM exercise_results er 
    WHERE er.user_id = NEW.user_id 
    AND er.is_correct = 1 
    AND DATE(er.submitted_at) = CURDATE();
    
    -- Cập nhật thống kê
    UPDATE learning_stats 
    SET 
        total_answers = total_answers + 1,
        correct_answers = correct_answers + IF(NEW.is_correct = 1, 1, 0),
        words_learned = unique_words_today,
        last_study_date = CURDATE(),
        updated_at = CURRENT_TIMESTAMP
    WHERE user_id = NEW.user_id;
    
    -- Cập nhật streak days (chỉ khi học ngày mới)
    UPDATE learning_stats 
    SET streak_days = CASE 
        WHEN DATEDIFF(CURDATE(), last_study_date) = 1 THEN streak_days + 1
        WHEN DATEDIFF(CURDATE(), last_study_date) = 0 THEN streak_days
        ELSE 1
    END
    WHERE user_id = NEW.user_id;
END//
DELIMITER ;
