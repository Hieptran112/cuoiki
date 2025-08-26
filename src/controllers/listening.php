<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../services/database.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_exercise':
        requireLogin();
        getListeningExercise();
        break;
    case 'submit_answer':
        requireLogin();
        submitListeningAnswer();
        break;
    default:
        echo json_encode(["success" => false, "message" => "Action không hợp lệ"]);
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["success" => false, "message" => "Vui lòng đăng nhập để thực hiện chức năng này"]);
        exit;
    }
}

function getListeningExercise() {
    global $conn;

    try {
        // Ensure tables exist first
        createSampleExercises();

        // Get a random listening exercise
        $stmt = $conn->prepare("SELECT * FROM listening_exercises WHERE is_active = 1 ORDER BY RAND() LIMIT 1");
        $stmt->execute();
        $exercise = $stmt->get_result()->fetch_assoc();

        if (!$exercise) {
            // If still no exercises, add sample data
            addSampleData();

            // Try again
            $stmt->execute();
            $exercise = $stmt->get_result()->fetch_assoc();
        }
        
        if ($exercise) {
            echo json_encode(["success" => true, "data" => $exercise]);
        } else {
            echo json_encode(["success" => false, "message" => "Không có bài tập nghe nào"]);
        }
        
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
    }
}

function submitListeningAnswer() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    $exerciseId = $data['exercise_id'] ?? 0;
    $answer = $data['answer'] ?? '';
    
    if (!$exerciseId || !$answer) {
        echo json_encode(["success" => false, "message" => "Dữ liệu không hợp lệ"]);
        return;
    }
    
    try {
        // Get exercise details
        $stmt = $conn->prepare("SELECT * FROM listening_exercises WHERE id = ?");
        $stmt->bind_param("i", $exerciseId);
        $stmt->execute();
        $exercise = $stmt->get_result()->fetch_assoc();
        
        if (!$exercise) {
            echo json_encode(["success" => false, "message" => "Bài tập không tồn tại"]);
            return;
        }
        
        $isCorrect = ($answer === $exercise['correct_answer']);
        
        // Save result
        $stmt = $conn->prepare("INSERT INTO listening_results (user_id, exercise_id, user_answer, is_correct, completed_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("iisi", $_SESSION['user_id'], $exerciseId, $answer, $isCorrect);
        $stmt->execute();
        
        echo json_encode([
            "success" => true,
            "data" => [
                "is_correct" => $isCorrect,
                "correct_answer" => $exercise['correct_answer'],
                "explanation" => $exercise['explanation']
            ]
        ]);
        
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
    }
}

function createSampleExercises() {
    global $conn;

    // Check if table exists, if not create it
    $conn->query("CREATE TABLE IF NOT EXISTS listening_exercises (
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
    )");

    // Create results table
    $conn->query("CREATE TABLE IF NOT EXISTS listening_results (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        exercise_id INT NOT NULL,
        user_answer CHAR(1) NOT NULL,
        is_correct BOOLEAN NOT NULL,
        completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (exercise_id) REFERENCES listening_exercises(id) ON DELETE CASCADE
    )");
}

function addSampleData() {
    global $conn;

    // Check if we already have data
    $count = $conn->query("SELECT COUNT(*) as count FROM listening_exercises")->fetch_assoc()['count'];
    if ($count > 0) return; // Already has data

    // Insert sample exercises with text-to-speech URLs
    $sampleExercises = [
        [
            'title' => 'Basic Greeting',
            'question' => 'Nghe đoạn hội thoại và chọn câu trả lời đúng: Người nói đang làm gì?',
            'audio_url' => 'tts:Hello, how are you today? I am fine, thank you.',
            'option_a' => 'Chào hỏi và hỏi thăm sức khỏe',
            'option_b' => 'Hỏi đường',
            'option_c' => 'Mua sắm',
            'option_d' => 'Đặt món ăn',
            'correct_answer' => 'A',
            'explanation' => 'Đoạn hội thoại là lời chào hỏi cơ bản "Hello, how are you today? I am fine, thank you."'
        ],
        [
            'title' => 'Numbers',
            'question' => 'Nghe và chọn số được đọc:',
            'audio_url' => 'tts:Twenty five',
            'option_a' => '15',
            'option_b' => '25',
            'option_c' => '35',
            'option_d' => '45',
            'correct_answer' => 'B',
            'explanation' => 'Số được đọc là "twenty five" = 25'
        ],
        [
            'title' => 'Time',
            'question' => 'Nghe và chọn thời gian được đọc:',
            'audio_url' => 'tts:It is three thirty in the afternoon',
            'option_a' => '3:00 PM',
            'option_b' => '3:30 PM',
            'option_c' => '3:15 PM',
            'option_d' => '3:45 PM',
            'correct_answer' => 'B',
            'explanation' => 'Thời gian được đọc là "three thirty in the afternoon" = 3:30 PM'
        ],
        [
            'title' => 'Weather',
            'question' => 'Nghe và chọn thời tiết được mô tả:',
            'audio_url' => 'tts:Today is sunny and warm. It is a beautiful day.',
            'option_a' => 'Mưa và lạnh',
            'option_b' => 'Có nắng và ấm',
            'option_c' => 'Có mây và mát',
            'option_d' => 'Có tuyết và lạnh',
            'correct_answer' => 'B',
            'explanation' => 'Thời tiết được mô tả là "sunny and warm" = có nắng và ấm'
        ],
        [
            'title' => 'Food Order',
            'question' => 'Nghe đoạn hội thoại và chọn món ăn được đặt:',
            'audio_url' => 'tts:I would like a hamburger and a cup of coffee, please.',
            'option_a' => 'Pizza và nước ngọt',
            'option_b' => 'Hamburger và cà phê',
            'option_c' => 'Sandwich và trà',
            'option_d' => 'Salad và nước',
            'correct_answer' => 'B',
            'explanation' => 'Món được đặt là "hamburger and a cup of coffee"'
        ]
    ];
    
    $stmt = $conn->prepare("INSERT INTO listening_exercises (title, question, audio_url, option_a, option_b, option_c, option_d, correct_answer, explanation) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($sampleExercises as $exercise) {
        $stmt->bind_param("sssssssss", 
            $exercise['title'],
            $exercise['question'],
            $exercise['audio_url'],
            $exercise['option_a'],
            $exercise['option_b'],
            $exercise['option_c'],
            $exercise['option_d'],
            $exercise['correct_answer'],
            $exercise['explanation']
        );
        $stmt->execute();
    }
}

$conn->close();
?>
