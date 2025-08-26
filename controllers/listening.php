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

    // Insert comprehensive listening exercises with text-to-speech URLs
    $sampleExercises = [
        // BEGINNER LEVEL - Basic Conversations
        [
            'title' => 'Basic Greeting',
            'question' => 'Nghe đoạn hội thoại và chọn câu trả lời đúng: Người nói đang làm gì?',
            'audio_url' => 'tts:Hello, how are you today? I am fine, thank you.',
            'option_a' => 'Chào hỏi và hỏi thăm sức khỏe',
            'option_b' => 'Hỏi đường',
            'option_c' => 'Mua sắm',
            'option_d' => 'Đặt món ăn',
            'correct_answer' => 'A',
            'explanation' => 'Đoạn hội thoại là lời chào hỏi cơ bản "Hello, how are you today? I am fine, thank you."',
            'difficulty' => 'beginner'
        ],
        [
            'title' => 'Self Introduction',
            'question' => 'Nghe và chọn thông tin đúng về người nói:',
            'audio_url' => 'tts:My name is Sarah. I am twenty-five years old. I work as a teacher in London.',
            'option_a' => 'Sarah, 25 tuổi, bác sĩ ở London',
            'option_b' => 'Sarah, 25 tuổi, giáo viên ở London',
            'option_c' => 'Sarah, 35 tuổi, giáo viên ở London',
            'option_d' => 'Sarah, 25 tuổi, giáo viên ở Paris',
            'correct_answer' => 'B',
            'explanation' => 'Sarah giới thiệu: tên Sarah, 25 tuổi, làm giáo viên (teacher) ở London.',
            'difficulty' => 'beginner'
        ],
        [
            'title' => 'Family Members',
            'question' => 'Nghe và chọn thành viên gia đình được nhắc đến:',
            'audio_url' => 'tts:I live with my parents, my younger brother, and my grandmother.',
            'option_a' => 'Bố mẹ, em trai, ông nội',
            'option_b' => 'Bố mẹ, anh trai, bà ngoại',
            'option_c' => 'Bố mẹ, em trai, bà nội',
            'option_d' => 'Bố mẹ, chị gái, bà nội',
            'correct_answer' => 'C',
            'explanation' => 'Người nói sống với: parents (bố mẹ), younger brother (em trai), grandmother (bà nội).',
            'difficulty' => 'beginner'
        ],
        [
            'title' => 'Numbers - Basic',
            'question' => 'Nghe và chọn số được đọc:',
            'audio_url' => 'tts:Twenty five',
            'option_a' => '15',
            'option_b' => '25',
            'option_c' => '35',
            'option_d' => '45',
            'correct_answer' => 'B',
            'explanation' => 'Số được đọc là "twenty five" = 25',
            'difficulty' => 'beginner'
        ],
        [
            'title' => 'Time Telling',
            'question' => 'Nghe và chọn thời gian được nhắc đến:',
            'audio_url' => 'tts:The meeting starts at half past three in the afternoon.',
            'option_a' => '3:00 chiều',
            'option_b' => '3:30 chiều',
            'option_c' => '2:30 chiều',
            'option_d' => '4:30 chiều',
            'correct_answer' => 'B',
            'explanation' => '"Half past three" nghĩa là 3:30, "in the afternoon" là buổi chiều.',
            'difficulty' => 'beginner'
        ],
        [
            'title' => 'Colors and Objects',
            'question' => 'Nghe và chọn mô tả đúng:',
            'audio_url' => 'tts:I have a red car and a blue bicycle.',
            'option_a' => 'Xe đỏ và xe đạp xanh',
            'option_b' => 'Xe xanh và xe đạp đỏ',
            'option_c' => 'Xe đỏ và xe đạp vàng',
            'option_d' => 'Xe vàng và xe đạp xanh',
            'correct_answer' => 'A',
            'explanation' => 'Red car (xe đỏ) và blue bicycle (xe đạp xanh).',
            'difficulty' => 'beginner'
        ],
        [
            'title' => 'Weather Description',
            'question' => 'Nghe và chọn thời tiết được mô tả:',
            'audio_url' => 'tts:Today is sunny and warm. The temperature is twenty-eight degrees.',
            'option_a' => 'Mưa và lạnh, 18 độ',
            'option_b' => 'Nắng và ấm, 28 độ',
            'option_c' => 'Có mây và mát, 28 độ',
            'option_d' => 'Nắng và nóng, 38 độ',
            'correct_answer' => 'B',
            'explanation' => 'Sunny (nắng), warm (ấm), twenty-eight degrees (28 độ).',
            'difficulty' => 'beginner'
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
            'explanation' => 'Món được đặt là "hamburger and a cup of coffee"',
            'difficulty' => 'beginner'
        ],

        // INTERMEDIATE LEVEL
        [
            'title' => 'Airport Announcement',
            'question' => 'Nghe thông báo sân bay và chọn thông tin chuyến bay:',
            'audio_url' => 'tts:Flight number BA 205 to London is now boarding at gate fifteen. Please have your boarding pass ready.',
            'option_a' => 'Chuyến bay BA 205 đi London, cổng 15, đang lên máy bay',
            'option_b' => 'Chuyến bay BA 205 đi Paris, cổng 15, bị hoãn',
            'option_c' => 'Chuyến bay BA 250 đi London, cổng 50, đang lên máy bay',
            'option_d' => 'Chuyến bay BA 205 đi London, cổng 15, bị hủy',
            'correct_answer' => 'A',
            'explanation' => 'Flight BA 205 to London, boarding (đang lên máy bay) at gate fifteen (cổng 15).',
            'difficulty' => 'intermediate'
        ],
        [
            'title' => 'Doctor Appointment',
            'question' => 'Nghe cuộc hội thoại với bác sĩ và chọn triệu chứng:',
            'audio_url' => 'tts:I have been feeling tired and I have a headache for three days. I also have a slight fever.',
            'option_a' => 'Mệt mỏi, đau đầu 3 ngày, sốt nhẹ',
            'option_b' => 'Mệt mỏi, đau bụng 3 ngày, sốt cao',
            'option_c' => 'Khó ngủ, đau đầu 1 ngày, sốt nhẹ',
            'option_d' => 'Mệt mỏi, đau lưng 3 ngày, không sốt',
            'correct_answer' => 'A',
            'explanation' => 'Feeling tired (mệt mỏi), headache for three days (đau đầu 3 ngày), slight fever (sốt nhẹ).',
            'difficulty' => 'intermediate'
        ],
        [
            'title' => 'University Lecture',
            'question' => 'Nghe đoạn giảng bài và chọn chủ đề chính:',
            'audio_url' => 'tts:Today we will discuss the impact of climate change on marine ecosystems. Rising sea temperatures affect coral reefs significantly.',
            'option_a' => 'Tác động của biến đổi khí hậu lên hệ sinh thái biển',
            'option_b' => 'Ảnh hưởng của ô nhiễm không khí lên rừng',
            'option_c' => 'Tác động của nông nghiệp lên đất đai',
            'option_d' => 'Ảnh hưởng của công nghiệp lên sông ngòi',
            'correct_answer' => 'A',
            'explanation' => 'Chủ đề: impact of climate change on marine ecosystems (tác động biến đổi khí hậu lên hệ sinh thái biển).',
            'difficulty' => 'intermediate'
        ]
        ,

        // ADVANCED LEVEL
        [
            'title' => 'Business Meeting',
            'question' => 'Nghe cuộc họp kinh doanh và chọn quyết định được đưa ra:',
            'audio_url' => 'tts:After reviewing the quarterly reports, we have decided to expand our operations to three new markets in Asia and increase our marketing budget by thirty percent.',
            'option_a' => 'Mở rộng sang 3 thị trường châu Á, tăng ngân sách marketing 30%',
            'option_b' => 'Mở rộng sang 2 thị trường châu Âu, tăng ngân sách marketing 20%',
            'option_c' => 'Mở rộng sang 3 thị trường châu Á, giảm ngân sách marketing 30%',
            'option_d' => 'Mở rộng sang 5 thị trường châu Á, tăng ngân sách marketing 40%',
            'correct_answer' => 'A',
            'explanation' => 'Expand to three new markets in Asia (mở rộng 3 thị trường châu Á), increase marketing budget by thirty percent (tăng ngân sách marketing 30%).',
            'difficulty' => 'advanced'
        ],
        [
            'title' => 'Scientific Research',
            'question' => 'Nghe báo cáo nghiên cứu và chọn kết quả chính:',
            'audio_url' => 'tts:Our research indicates that the new treatment method shows a seventy-five percent success rate in clinical trials, which is significantly higher than conventional treatments.',
            'option_a' => 'Phương pháp mới có tỷ lệ thành công 65%, thấp hơn phương pháp cũ',
            'option_b' => 'Phương pháp mới có tỷ lệ thành công 75%, cao hơn phương pháp cũ',
            'option_c' => 'Phương pháp mới có tỷ lệ thành công 85%, bằng phương pháp cũ',
            'option_d' => 'Phương pháp mới có tỷ lệ thành công 75%, thấp hơn phương pháp cũ',
            'correct_answer' => 'B',
            'explanation' => 'Seventy-five percent success rate (tỷ lệ thành công 75%), significantly higher than conventional treatments (cao hơn đáng kể so với phương pháp thông thường).',
            'difficulty' => 'advanced'
        ],
        [
            'title' => 'News Report',
            'question' => 'Nghe bản tin và chọn thông tin chính:',
            'audio_url' => 'tts:The government announced today that unemployment rates have decreased by two point five percent this quarter, reaching the lowest level in five years.',
            'option_a' => 'Tỷ lệ thất nghiệp giảm 2.5%, thấp nhất trong 3 năm',
            'option_b' => 'Tỷ lệ thất nghiệp tăng 2.5%, cao nhất trong 5 năm',
            'option_c' => 'Tỷ lệ thất nghiệp giảm 2.5%, thấp nhất trong 5 năm',
            'option_d' => 'Tỷ lệ thất nghiệp giảm 3.5%, thấp nhất trong 5 năm',
            'correct_answer' => 'C',
            'explanation' => 'Unemployment rates decreased by two point five percent (tỷ lệ thất nghiệp giảm 2.5%), lowest level in five years (thấp nhất trong 5 năm).',
            'difficulty' => 'advanced'
        ]
    ];

    $stmt = $conn->prepare("INSERT IGNORE INTO listening_exercises (title, question, audio_url, option_a, option_b, option_c, option_d, correct_answer, explanation, difficulty) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($sampleExercises as $exercise) {
        $difficulty = $exercise['difficulty'] ?? 'beginner';
        $stmt->bind_param("ssssssssss",
            $exercise['title'],
            $exercise['question'],
            $exercise['audio_url'],
            $exercise['option_a'],
            $exercise['option_b'],
            $exercise['option_c'],
            $exercise['option_d'],
            $exercise['correct_answer'],
            $exercise['explanation'],
            $difficulty
        );
        $stmt->execute();
    }
}

$conn->close();
?>
