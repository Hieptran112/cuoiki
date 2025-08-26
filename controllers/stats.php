<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');
require_once __DIR__ . '/../src/services/database.php';

// Auto-login for testing
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 4;
    $_SESSION['username'] = 'Long';
    $_SESSION['email'] = 'long@example.com';
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_topic_stats':
        getTopicStats();
        break;
    case 'get_flashcard_stats':
        getFlashcardStats();
        break;
    case 'get_recent_activity':
        getRecentActivity();
        break;
    case 'get_overall_stats':
        getOverallStats();
        break;
    case 'update_daily_stats':
        updateDailyStats();
        break;
    default:
        echo json_encode(["success" => false, "message" => "Action không hợp lệ"]);
}

function getTopicStats() {
    global $conn;
    
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["success" => false, "message" => "Vui lòng đăng nhập"]);
        return;
    }
    
    try {
        // Get topic completion stats
        $stmt = $conn->prepare("
            SELECT
                t.name as topic_name,
                t.color,
                COUNT(DISTINCT tl.id) as total_lessons,
                COUNT(DISTINCT CASE WHEN tp.is_completed = 1 THEN tp.lesson_id END) as completed_lessons,
                COALESCE(AVG(CASE WHEN tp.is_completed = 1 THEN tp.completion_percentage END), 0) as avg_completion,
                COUNT(DISTINCT ter.id) as total_exercises_done,
                SUM(CASE WHEN ter.is_correct = 1 THEN 1 ELSE 0 END) as correct_answers,
                COUNT(ter.id) as total_answers
            FROM topics t
            LEFT JOIN topic_lessons tl ON t.id = tl.topic_id AND tl.is_active = 1
            LEFT JOIN topic_progress tp ON tl.id = tp.lesson_id AND tp.user_id = ?
            LEFT JOIN topic_exercise_results ter ON tl.id = ter.lesson_id AND ter.user_id = ?
            WHERE t.is_active = 1
            GROUP BY t.id, t.name, t.color
            ORDER BY t.id
        ");
        $stmt->bind_param("ii", $_SESSION['user_id'], $_SESSION['user_id']);
        $stmt->execute();
        $topics = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode(["success" => true, "data" => $topics]);
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
    }
}

function getFlashcardStats() {
    global $conn;
    
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["success" => false, "message" => "Vui lòng đăng nhập"]);
        return;
    }
    
    try {
        // Get flashcard stats
        $stmt = $conn->prepare("
            SELECT 
                COUNT(DISTINCT d.id) as total_decks,
                COUNT(DISTINCT f.id) as total_flashcards,
                COUNT(DISTINCT sp.flashcard_id) as studied_cards,
                SUM(CASE WHEN sp.status = 'mastered' THEN 1 ELSE 0 END) as mastered_cards,
                SUM(sp.correct_count) as total_correct_reviews,
                SUM(sp.review_count) as total_reviews
            FROM decks d
            LEFT JOIN flashcards f ON d.id = f.deck_id
            LEFT JOIN study_progress sp ON f.id = sp.flashcard_id AND sp.user_id = ?
            WHERE d.user_id = ?
        ");
        $stmt->bind_param("ii", $_SESSION['user_id'], $_SESSION['user_id']);
        $stmt->execute();
        $flashcard_stats = $stmt->get_result()->fetch_assoc();
        
        // Get deck breakdown
        $stmt = $conn->prepare("
            SELECT 
                d.name as deck_name,
                COUNT(f.id) as card_count,
                COUNT(sp.flashcard_id) as studied_count,
                SUM(CASE WHEN sp.status = 'mastered' THEN 1 ELSE 0 END) as mastered_count
            FROM decks d
            LEFT JOIN flashcards f ON d.id = f.deck_id
            LEFT JOIN study_progress sp ON f.id = sp.flashcard_id AND sp.user_id = ?
            WHERE d.user_id = ?
            GROUP BY d.id, d.name
            ORDER BY d.name
        ");
        $stmt->bind_param("ii", $_SESSION['user_id'], $_SESSION['user_id']);
        $stmt->execute();
        $deck_breakdown = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode([
            "success" => true, 
            "data" => [
                "overall" => $flashcard_stats,
                "decks" => $deck_breakdown
            ]
        ]);
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
    }
}

function getRecentActivity() {
    global $conn;
    
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["success" => false, "message" => "Vui lòng đăng nhập"]);
        return;
    }
    
    try {
        // Get recent topic exercise activity
        $stmt = $conn->prepare("
            SELECT
                'topic_exercise' as activity_type,
                ter.question_text as activity_description,
                ter.is_correct,
                ter.answered_at as activity_time,
                t.name as topic_name,
                tl.title as lesson_title
            FROM topic_exercise_results ter
            JOIN topic_lessons tl ON ter.lesson_id = tl.id
            JOIN topics t ON tl.topic_id = t.id
            WHERE ter.user_id = ?
            ORDER BY ter.answered_at DESC
            LIMIT 10
        ");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $topic_activity = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Get recent flashcard activity
        $stmt = $conn->prepare("
            SELECT 
                'flashcard_review' as activity_type,
                f.word as activity_description,
                CASE WHEN sp.ease_level IN ('good', 'easy') THEN 1 ELSE 0 END as is_correct,
                sp.last_reviewed_at as activity_time,
                d.name as deck_name,
                sp.ease_level
            FROM study_progress sp
            JOIN flashcards f ON sp.flashcard_id = f.id
            JOIN decks d ON f.deck_id = d.id
            WHERE sp.user_id = ? AND sp.last_reviewed_at IS NOT NULL
            ORDER BY sp.last_reviewed_at DESC
            LIMIT 10
        ");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $flashcard_activity = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Merge and sort activities
        $all_activities = array_merge($topic_activity, $flashcard_activity);
        usort($all_activities, function($a, $b) {
            return strtotime($b['activity_time']) - strtotime($a['activity_time']);
        });
        
        // Take only the most recent 15
        $recent_activities = array_slice($all_activities, 0, 15);
        
        echo json_encode(["success" => true, "data" => $recent_activities]);
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
    }
}

function getOverallStats() {
    global $conn;

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["success" => false, "message" => "Please login"]);
        return;
    }

    try {
        $userId = $_SESSION['user_id'];

        // Get basic stats that we know exist
        $stats = [];

        // Topic exercises
        $result = $conn->query("SELECT COUNT(*) as total FROM topic_exercise_results WHERE user_id = $userId");
        $stats['total_topic_exercises'] = $result ? $result->fetch_assoc()['total'] : 0;

        $result = $conn->query("SELECT COUNT(*) as correct FROM topic_exercise_results WHERE user_id = $userId AND is_correct = 1");
        $stats['correct_topic_exercises'] = $result ? $result->fetch_assoc()['correct'] : 0;

        // Lessons
        $result = $conn->query("SELECT COUNT(DISTINCT lesson_id) as started FROM topic_progress WHERE user_id = $userId");
        $stats['lessons_started'] = $result ? $result->fetch_assoc()['started'] : 0;

        $result = $conn->query("SELECT COUNT(*) as completed FROM topic_progress WHERE user_id = $userId AND is_completed = 1");
        $stats['lessons_completed'] = $result ? $result->fetch_assoc()['completed'] : 0;

        // Flashcards (default to 0 if table doesn't exist)
        $stats['flashcards_studied'] = 0;
        $stats['flashcards_mastered'] = 0;

        // Study days
        $result = $conn->query("SELECT COUNT(DISTINCT DATE(answered_at)) as days FROM topic_exercise_results WHERE user_id = $userId");
        $stats['study_days_topics'] = $result ? $result->fetch_assoc()['days'] : 0;
        $stats['study_days_flashcards'] = 0;

        // Daily exercises
        $result = $conn->query("SELECT COUNT(*) as total FROM user_stats WHERE user_id = $userId AND exercise_type = 'daily_exercise'");
        $stats['daily_exercises_total'] = $result ? $result->fetch_assoc()['total'] : 0;

        $result = $conn->query("SELECT COUNT(*) as correct FROM user_stats WHERE user_id = $userId AND exercise_type = 'daily_exercise' AND is_correct = 1");
        $stats['daily_exercises_correct'] = $result ? $result->fetch_assoc()['correct'] : 0;

        // Total points
        $result = $conn->query("SELECT COALESCE(SUM(points_earned), 0) as points FROM user_stats WHERE user_id = $userId");
        $stats['total_points'] = $result ? $result->fetch_assoc()['points'] : 0;

        echo json_encode(["success" => true, "data" => $stats]);

    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
}

function updateDailyStats() {
    global $conn;

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["success" => false, "message" => "Chưa đăng nhập"]);
        return;
    }

    $userId = $_SESSION['user_id'];
    $data = json_decode(file_get_contents('php://input'), true);

    $exerciseType = $data['exercise_type'] ?? 'daily_exercise';
    $isCorrect = $data['is_correct'] ?? false;
    $points = $data['points'] ?? 0;

    try {
        // Create user_stats table if not exists
        $conn->query("CREATE TABLE IF NOT EXISTS user_stats (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            exercise_type VARCHAR(50) NOT NULL,
            is_correct BOOLEAN NOT NULL,
            points INT DEFAULT 0,
            completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");

        // Insert stats record
        $stmt = $conn->prepare("INSERT INTO user_stats (user_id, exercise_type, is_correct, points) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isii", $userId, $exerciseType, $isCorrect, $points);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Stats updated successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to update stats"]);
        }

    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
    }
}

// Don't close connection - it's needed for multiple API calls
?>