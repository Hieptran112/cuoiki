<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../src/services/database.php';

// Bật hiển thị lỗi cho dev
ini_set('display_errors', 1);
error_reporting(E_ALL);

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_topics':
        getTopics();
        break;
    case 'get_topic_lessons':
        getTopicLessons();
        break;
    case 'get_lesson_exercises':
        getLessonExercises();
        break;
    case 'submit_topic_exercise':
        requireLogin();
        submitTopicExercise();
        break;
    case 'get_topic_progress':
        requireLogin();
        getTopicProgress();
        break;
    default:
        echo json_encode(["success" => false, "message" => "Action không hợp lệ"]);
        break;
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["success" => false, "message" => "Vui lòng đăng nhập để thực hiện chức năng này"]);
        exit;
    }
}

function getTopics() {
    global $conn;

    try {
        $stmt = $conn->prepare("SELECT * FROM topics WHERE is_active = 1 ORDER BY id");
        $stmt->execute();
        $topics = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Lấy số lượng bài học cho mỗi topic
        foreach ($topics as &$topic) {
            $stmt = $conn->prepare("SELECT COUNT(*) as lesson_count FROM topic_lessons WHERE topic_id = ? AND is_active = 1");
            $stmt->bind_param("i", $topic['id']);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $topic['lesson_count'] = $result['lesson_count'];
        }

        echo json_encode(["success" => true, "data" => $topics]);

    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
    }
}

function getTopicLessons() {
    global $conn;

    $topicId = $_GET['topic_id'] ?? 0;

    if (!$topicId) {
        echo json_encode(["success" => false, "message" => "Topic ID không hợp lệ"]);
        return;
    }

    try {
        // Lấy thông tin topic
        $stmt = $conn->prepare("SELECT * FROM topics WHERE id = ? AND is_active = 1");
        $stmt->bind_param("i", $topicId);
        $stmt->execute();
        $topic = $stmt->get_result()->fetch_assoc();

        if (!$topic) {
            echo json_encode(["success" => false, "message" => "Topic không tồn tại"]);
            return;
        }

        // Lấy danh sách bài học
        $stmt = $conn->prepare("SELECT * FROM topic_lessons WHERE topic_id = ? AND is_active = 1 ORDER BY lesson_order");
        $stmt->bind_param("i", $topicId);
        $stmt->execute();
        $lessons = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Lấy tiến độ của user nếu đã đăng nhập
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId) {
            foreach ($lessons as &$lesson) {
                $stmt = $conn->prepare("SELECT * FROM topic_progress WHERE user_id = ? AND lesson_id = ?");
                $stmt->bind_param("ii", $userId, $lesson['id']);
                $stmt->execute();
                $progress = $stmt->get_result()->fetch_assoc();
                $lesson['progress'] = $progress;
            }
        }

        echo json_encode([
            "success" => true,
            "data" => [
                "topic" => $topic,
                "lessons" => $lessons
            ]
        ]);

    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
    }
}

function getLessonExercises() {
    global $conn;

    $lessonId = $_GET['lesson_id'] ?? 0;

    if (!$lessonId) {
        echo json_encode(["success" => false, "message" => "Lesson ID không hợp lệ"]);
        return;
    }

    try {
        // Lấy thông tin bài học
        $stmt = $conn->prepare("
            SELECT tl.*, t.name as topic_name, t.color as topic_color
            FROM topic_lessons tl
            JOIN topics t ON tl.topic_id = t.id
            WHERE tl.id = ? AND tl.is_active = 1
        ");
        $stmt->bind_param("i", $lessonId);
        $stmt->execute();
        $lesson = $stmt->get_result()->fetch_assoc();

        if (!$lesson) {
            echo json_encode(["success" => false, "message" => "Bài học không tồn tại"]);
            return;
        }

        // Lấy danh sách câu hỏi
        $stmt = $conn->prepare("SELECT * FROM topic_exercises WHERE lesson_id = ? ORDER BY question_number");
        $stmt->bind_param("i", $lessonId);
        $stmt->execute();
        $exercises = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        echo json_encode([
            "success" => true,
            "data" => [
                "lesson" => $lesson,
                "exercises" => $exercises
            ]
        ]);

    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
    }
}

function submitTopicExercise() {
    global $conn;

    $data = json_decode(file_get_contents("php://input"), true);
    $userId = $_SESSION['user_id'] ?? null;
    $lessonId = $data['lesson_id'] ?? 0;
    $exerciseId = $data['exercise_id'] ?? 0;
    $selectedAnswer = $data['selected_answer'] ?? '';

    if (!$userId) {
        echo json_encode(["success" => false, "message" => "Vui lòng đăng nhập để lưu kết quả"]);
        return;
    }

    if (!$lessonId || !$exerciseId || !$selectedAnswer) {
        echo json_encode(["success" => false, "message" => "Dữ liệu không hợp lệ"]);
        return;
    }

    try {
        // Lấy thông tin câu hỏi
        $stmt = $conn->prepare("SELECT * FROM topic_exercises WHERE id = ?");
        $stmt->bind_param("i", $exerciseId);
        $stmt->execute();
        $exercise = $stmt->get_result()->fetch_assoc();

        if (!$exercise) {
            echo json_encode(["success" => false, "message" => "Câu hỏi không tồn tại"]);
            return;
        }

        $isCorrect = ($selectedAnswer === $exercise['correct_answer']);

        // Lưu kết quả
        $stmt = $conn->prepare("
            INSERT INTO topic_exercise_results (user_id, lesson_id, question_text, user_answer, correct_answer, is_correct, answered_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
            user_answer = VALUES(user_answer),
            is_correct = VALUES(is_correct),
            answered_at = NOW()
        ");
        $stmt->bind_param("iisssi", $userId, $lessonId, $exercise['question'], $selectedAnswer, $exercise['correct_answer'], $isCorrect);
        $stmt->execute();

        // Cập nhật tiến độ
        updateTopicProgress($userId, $lessonId);

        $explanation = $isCorrect ? $exercise['explanation_correct'] : $exercise['explanation_wrong'];

        echo json_encode([
            "success" => true,
            "data" => [
                "is_correct" => $isCorrect,
                "correct_answer" => $exercise['correct_answer'],
                "explanation" => $explanation
            ]
        ]);

    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
    }
}

function updateTopicProgress($userId, $lessonId) {
    global $conn;

    try {
        // Lấy thông tin bài học
        $stmt = $conn->prepare("SELECT topic_id FROM topic_lessons WHERE id = ?");
        $stmt->bind_param("i", $lessonId);
        $stmt->execute();
        $lesson = $stmt->get_result()->fetch_assoc();

        if (!$lesson) return;

        // Đếm tổng số câu hỏi và số câu trả lời đúng
        $stmt = $conn->prepare("
            SELECT
                COUNT(DISTINCT te.id) as total_questions,
                COUNT(DISTINCT CASE WHEN ter.is_correct = 1 THEN ter.id END) as correct_answers
            FROM topic_exercises te
            LEFT JOIN topic_exercise_results ter ON te.question = ter.question_text AND ter.user_id = ? AND ter.lesson_id = ?
            WHERE te.lesson_id = ?
        ");
        $stmt->bind_param("iii", $userId, $lessonId, $lessonId);
        $stmt->execute();
        $stats = $stmt->get_result()->fetch_assoc();

        $totalQuestions = $stats['total_questions'];
        $correctAnswers = $stats['correct_answers'] ?? 0;
        $completionPercentage = $totalQuestions > 0 ? ($correctAnswers / $totalQuestions) * 100 : 0;
        $isCompleted = $completionPercentage >= 80; // 80% để hoàn thành

        // Cập nhật hoặc tạo mới tiến độ
        $stmt = $conn->prepare("
            INSERT INTO topic_progress (user_id, topic_id, lesson_id, total_questions, correct_answers, completion_percentage, is_completed, completed_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            total_questions = VALUES(total_questions),
            correct_answers = VALUES(correct_answers),
            completion_percentage = VALUES(completion_percentage),
            is_completed = VALUES(is_completed),
            completed_at = CASE WHEN VALUES(is_completed) = 1 AND is_completed = 0 THEN CURRENT_TIMESTAMP ELSE completed_at END,
            last_attempt_at = CURRENT_TIMESTAMP
        ");

        $completedAt = $isCompleted ? date('Y-m-d H:i:s') : null;
        $stmt->bind_param("iiiidiss", $userId, $lesson['topic_id'], $lessonId, $totalQuestions, $correctAnswers, $completionPercentage, $isCompleted, $completedAt);
        $stmt->execute();

    } catch (Exception $e) {
        // Log error but don't break the flow
        error_log("Error updating topic progress: " . $e->getMessage());
    }
}

function getTopicProgress() {
    global $conn;

    $userId = $_SESSION['user_id'] ?? null;
    $topicId = $_GET['topic_id'] ?? 0;

    if (!$userId) {
        echo json_encode(["success" => false, "message" => "Vui lòng đăng nhập"]);
        return;
    }

    try {
        $stmt = $conn->prepare("
            SELECT
                tp.*,
                tl.title as lesson_title,
                tl.lesson_order
            FROM topic_progress tp
            JOIN topic_lessons tl ON tp.lesson_id = tl.id
            WHERE tp.user_id = ? AND tp.topic_id = ?
            ORDER BY tl.lesson_order
        ");
        $stmt->bind_param("ii", $userId, $topicId);
        $stmt->execute();
        $progress = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        echo json_encode(["success" => true, "data" => $progress]);

    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
    }
}
?>