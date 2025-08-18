<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../services/database.php';

// Bật hiển thị lỗi cho dev
ini_set('display_errors', 1);
error_reporting(E_ALL);

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'search':
        searchWord();
        break;
    case 'suggestions':
        getSearchSuggestions();
        break;
    case 'add_word':
        addWord();
        break;
    case 'get_daily_exercises':
        getDailyExercises();
        break;
    case 'submit_exercise':
        submitExercise();
        break;
    case 'get_stats':
        getStats();
        break;
    case 'get_all_words':
        getAllWords();
        break;
    case 'delete_word':
        deleteWord();
        break;
    case 'get_mixed_exercises':
        getMixedExercises();
        break;
    case 'bulk_import':
        bulkImport();
        break;
    case 'submit_daily_answer':
        submitDailyAnswer();
        break;
    case 'get_answer_breakdown':
        getAnswerBreakdown();
        break;
    case 'get_recent_learned':
        getRecentLearned();
        break;
    case 'get_user_stats':
        getUserStats();
        break;
    case 'update_trigger':
        updateLearningStatsTrigger();
        break;
    case 'submit_test_answer':
        submitTestAnswer();
        break;
    default:
        echo json_encode(["success" => false, "message" => "Action không hợp lệ"]);
        break;
}

function searchWord() {
    global $conn;
    
    $data = json_decode(file_get_contents("php://input"), true);
    $word = trim($data['word'] ?? '');
    
    if (empty($word)) {
        echo json_encode(["success" => false, "message" => "Từ cần tìm không được để trống"]);
        return;
    }
    
    try {
        // Tìm kiếm trong database
        $stmt = $conn->prepare("SELECT * FROM dictionary WHERE word LIKE ? OR vietnamese LIKE ?");
        $searchTerm = "%$word%";
        $stmt->bind_param("ss", $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $words = [];
            while ($row = $result->fetch_assoc()) {
                $words[] = [
                    'id' => $row['id'],
                    'word' => $row['word'],
                    'phonetic' => $row['phonetic'],
                    'vietnamese' => $row['vietnamese'],
                    'english_definition' => $row['english_definition'],
                    'example' => $row['example'],
                    'part_of_speech' => $row['part_of_speech'],
                    'difficulty' => $row['difficulty']
                ];
            }
            echo json_encode(["success" => true, "data" => $words]);
        } else {
            // Nếu không tìm thấy, trả về dữ liệu mẫu
            $mockData = [
                'word' => $word,
                'phonetic' => "/ˈ" . strtolower($word) . "/",
                'vietnamese' => "Định nghĩa tiếng Việt cho từ '$word'",
                'english_definition' => "English definition for '$word'",
                'example' => "Example sentence using '$word'",
                'part_of_speech' => 'noun',
                'difficulty' => 'intermediate'
            ];
            echo json_encode(["success" => true, "data" => [$mockData]]);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Lỗi tìm kiếm: " . $e->getMessage()]);
    }
}

function addWord() {
    global $conn;
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    $required_fields = ['word', 'vietnamese', 'english_definition'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            echo json_encode(["success" => false, "message" => "Thiếu thông tin: $field"]);
            return;
        }
    }
    
    $word = trim($data['word']);
    $phonetic = $data['phonetic'] ?? '';
    $vietnamese = trim($data['vietnamese']);
    $english_definition = trim($data['english_definition']);
    $example = $data['example'] ?? '';
    $part_of_speech = $data['part_of_speech'] ?? 'noun';
    $difficulty = $data['difficulty'] ?? 'beginner';
    
    try {
        // Kiểm tra từ đã tồn tại
        $check = $conn->prepare("SELECT id FROM dictionary WHERE word = ?");
        $check->bind_param("s", $word);
        $check->execute();
        $check->store_result();
        
        if ($check->num_rows > 0) {
            echo json_encode(["success" => false, "message" => "Từ '$word' đã tồn tại trong từ điển"]);
            $check->close();
            return;
        }
        $check->close();
        
        // Thêm từ mới
        $stmt = $conn->prepare("INSERT INTO dictionary (word, phonetic, vietnamese, english_definition, example, part_of_speech, difficulty) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $word, $phonetic, $vietnamese, $english_definition, $example, $part_of_speech, $difficulty);
        
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Thêm từ '$word' thành công"]);
        } else {
            echo json_encode(["success" => false, "message" => "Lỗi khi thêm từ"]);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
    }
}

function getDailyExercises() {
    global $conn;

    try {
        // Thay đổi: 10 câu hỏi hoàn toàn ngẫu nhiên
        $limit = 10;

        // Lấy 10 từ ngẫu nhiên từ database
        $sql = "SELECT id, word, vietnamese, english_definition, part_of_speech FROM dictionary ORDER BY RAND() LIMIT $limit";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $exercises = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Tạo bài tập trắc nghiệm
        $quizExercises = [];
        foreach ($exercises as $exercise) {
            // Lấy 3 từ khác làm đáp án sai
            $stmt = $conn->prepare("SELECT vietnamese FROM dictionary WHERE id != ? ORDER BY RAND() LIMIT 3");
            $stmt->bind_param("i", $exercise['id']);
            $stmt->execute();
            $wrongAnswers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            $options = [$exercise['vietnamese']];
            foreach ($wrongAnswers as $wrong) {
                $options[] = $wrong['vietnamese'];
            }

            // Xáo trộn thứ tự đáp án
            shuffle($options);
            $correctIndex = array_search($exercise['vietnamese'], $options);

            $quizExercises[] = [
                'question' => "Từ tiếng Anh '{$exercise['word']}' có nghĩa là gì?",
                'options' => $options,
                'correct' => $correctIndex,
                'dictionary_id' => (int)$exercise['id'],
                'word' => $exercise['word'],
                'part_of_speech' => $exercise['part_of_speech']
            ];
        }

        echo json_encode(["success" => true, "data" => $quizExercises]);

    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
    }
}

function getMixedExercises() {
    global $conn;
    try {
        // Pick 8 random words
        $stmt = $conn->prepare("SELECT id, word, vietnamese, english_definition, example, part_of_speech FROM dictionary ORDER BY RAND() LIMIT 8");
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        if (count($rows) === 0) { echo json_encode(["success"=>true, "data"=>[]]); return; }

        $exercises = [];

        // 1) Multiple choice (from first 6 words, increased from 4)
        $poolVn = array_column($rows, 'vietnamese');
        for ($i = 0; $i < min(6, count($rows)); $i++) {
            $q = $rows[$i];
            $options = [ $q['vietnamese'] ];
            // pick 3 wrong answers
            $wrong = [];
            foreach ($poolVn as $vn) { if ($vn !== $q['vietnamese']) { $wrong[] = $vn; } }
            shuffle($wrong);
            $options = array_merge($options, array_slice($wrong, 0, max(0, 3)));
            shuffle($options);
            $correctIndex = array_search($q['vietnamese'], $options);
            $exercises[] = [
                'type' => 'multiple_choice',
                'question' => "Từ tiếng Anh '{$q['word']}' có nghĩa là gì?",
                'options' => $options,
                'correct' => $correctIndex,
                'meta' => [ 'word' => $q['word'], 'pos' => $q['part_of_speech'] ]
            ];
        }

        // Removed matching exercises

        // 2) Listening (use TTS on client) for 2 words
        for ($i = 0; $i < min(2, count($rows)); $i++) {
            $q = $rows[$i];
            $exercises[] = [
                'type' => 'listening',
                'tts_word' => $q['word'],
                'answer' => $q['word']
            ];
        }

        echo json_encode(["success"=>true, "data"=>$exercises]);
    } catch (Exception $e) {
        echo json_encode(["success"=>false, "message"=>"Lỗi: " . $e->getMessage()]);
    }
}

function submitExercise() {
    global $conn;
    
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["success" => false, "message" => "Vui lòng đăng nhập để lưu kết quả"]);
        return;
    }
    
    $data = json_decode(file_get_contents("php://input"), true);
    $exerciseId = $data['exercise_id'] ?? 0;
    $selectedAnswer = $data['selected_answer'] ?? -1;
    $correctAnswer = $data['correct_answer'] ?? -1;
    $isCorrect = $data['is_correct'] ?? false;
    
    try {
        $stmt = $conn->prepare("INSERT INTO exercise_results (user_id, exercise_id, selected_answer, correct_answer, is_correct, submitted_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("iiiii", $_SESSION['user_id'], $exerciseId, $selectedAnswer, $correctAnswer, $isCorrect ? 1 : 0);
        
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Kết quả đã được lưu"]);
        } else {
            echo json_encode(["success" => false, "message" => "Lỗi khi lưu kết quả"]);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
    }
}

function submitDailyAnswer() {
    global $conn;
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["success"=>false, "message"=>"Cần đăng nhập"]);
        return;
    }
    $data = json_decode(file_get_contents("php://input"), true);
    $dictId = (int)($data['dictionary_id'] ?? 0);
    $selectedVi = trim($data['selected_vi'] ?? '');
    $correctVi = trim($data['correct_vi'] ?? '');
    if ($dictId <= 0 || $correctVi === '') {
        echo json_encode(["success"=>false, "message"=>"Thiếu tham số"]);
        return;
    }
    $isCorrect = ($selectedVi !== '' && strcasecmp($selectedVi, $correctVi) === 0);

    $stmt = $conn->prepare("INSERT INTO exercise_results (user_id, exercise_id, selected_answer, correct_answer, is_correct, submitted_at) VALUES (?, ?, 0, 0, ?, NOW())");
    $isCorrectInt = $isCorrect ? 1 : 0;
    $exerciseId = $dictId;
    $stmt->bind_param("iii", $_SESSION['user_id'], $exerciseId, $isCorrectInt);
    $stmt->execute();
    $stmt->close();

    if (!$isCorrect) {
        $today = (new DateTime('today'))->format('Y-m-d');
        // Check current wrong_count to determine next review day: first wrong => +1 day, 2nd+ => +2 days
        $check = $conn->prepare("SELECT wrong_count FROM user_word_review WHERE user_id = ? AND dictionary_id = ?");
        $check->bind_param("ii", $_SESSION['user_id'], $dictId);
        $check->execute();
        $r = $check->get_result()->fetch_assoc();
        $check->close();
        $addDays = ($r && (int)$r['wrong_count'] >= 1) ? 2 : 1;
        $next = (new DateTime('today'))->modify("+{$addDays} day")->format('Y-m-d');
        $sql = "INSERT INTO user_word_review (user_id, dictionary_id, wrong_count, last_wrong_date, next_review_date, difficulty)
                VALUES (?, ?, 1, ?, ?, 'kha_kho')
                ON DUPLICATE KEY UPDATE 
                    wrong_count = wrong_count + 1,
                    last_wrong_date = VALUES(last_wrong_date),
                    next_review_date = VALUES(next_review_date),
                    difficulty = CASE WHEN wrong_count + 1 >= 2 THEN 'rat_kho' ELSE 'kha_kho' END";
        $ins = $conn->prepare($sql);
        $ins->bind_param("iiss", $_SESSION['user_id'], $dictId, $today, $next);
        $ins->execute();
        $ins->close();
    } else {
        $sql = "UPDATE user_word_review SET next_review_date = NULL WHERE user_id = ? AND dictionary_id = ?";
        $up = $conn->prepare($sql);
        $up->bind_param("ii", $_SESSION['user_id'], $dictId);
        $up->execute();
        $up->close();
    }

    echo json_encode(["success"=>true, "is_correct"=>$isCorrect]);
}

function getAnswerBreakdown() {
    global $conn;
    if (!isset($_SESSION['user_id'])) {
        $resp = array(
            "success" => true,
            "data" => array(
                "correct" => array(),
                "wrong" => array()
            )
        );
        echo json_encode($resp);
        return;
    }
    $today = (new DateTime('today'))->format('Y-m-d');

    $stmt = $conn->prepare("SELECT er.id, er.exercise_id as dictionary_id, d.word, d.vietnamese, er.submitted_at
                            FROM exercise_results er JOIN dictionary d ON d.id = er.exercise_id
                            WHERE er.user_id = ? AND DATE(er.submitted_at) = ? AND er.is_correct = 1
                            ORDER BY er.submitted_at DESC LIMIT 200");
    $stmt->bind_param("is", $_SESSION['user_id'], $today);
    $stmt->execute();
    $correct = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $stmt = $conn->prepare("SELECT uwr.dictionary_id, d.word, d.vietnamese, uwr.wrong_count, uwr.difficulty, uwr.last_wrong_date, uwr.next_review_date
                            FROM user_word_review uwr JOIN dictionary d ON d.id = uwr.dictionary_id
                            WHERE uwr.user_id = ? AND (uwr.last_wrong_date = ? OR (uwr.next_review_date IS NOT NULL AND uwr.next_review_date >= ?))
                            ORDER BY uwr.updated_at DESC LIMIT 200");
    $stmt->bind_param("iss", $_SESSION['user_id'], $today, $today);
    $stmt->execute();
    $wrong = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    echo json_encode(["success"=>true, "data"=>["correct"=>$correct, "wrong"=>$wrong]]);
}

function getRecentLearned() {
    global $conn;
    if (!isset($_SESSION['user_id'])) { echo json_encode(["success"=>true, "data"=>[]]); return; }
    $twoDaysAgo = (new DateTime('today'))->modify('-1 day')->format('Y-m-d');
    $stmt = $conn->prepare("SELECT d.id as dictionary_id, d.word, d.vietnamese, DATE(er.submitted_at) as day
                            FROM exercise_results er JOIN dictionary d ON d.id = er.exercise_id
                            WHERE er.user_id = ? AND er.is_correct = 1 AND DATE(er.submitted_at) >= ?
                            GROUP BY d.id, DATE(er.submitted_at)
                            ORDER BY er.submitted_at DESC LIMIT 100");
    $stmt->bind_param("is", $_SESSION['user_id'], $twoDaysAgo);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    echo json_encode(["success"=>true, "data"=>$rows]);
}

function getStats() {
    global $conn;
    
    try {
        $stats = [];
        
        // Tổng số từ
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM dictionary");
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['total'] = $result->fetch_assoc()['total'];
        
        // Số từ theo mức độ
        $stmt = $conn->prepare("SELECT difficulty, COUNT(*) as count FROM dictionary GROUP BY difficulty");
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $stats[$row['difficulty']] = $row['count'];
        }
        
        echo json_encode(["success" => true, "data" => $stats]);
        
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
    }
}

function getAllWords() {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT * FROM dictionary ORDER BY word ASC");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $words = [];
        while ($row = $result->fetch_assoc()) {
            $words[] = [
                'id' => $row['id'],
                'word' => $row['word'],
                'phonetic' => $row['phonetic'],
                'vietnamese' => $row['vietnamese'],
                'english_definition' => $row['english_definition'],
                'example' => $row['example'],
                'part_of_speech' => $row['part_of_speech'],
                'difficulty' => $row['difficulty'],
                'created_at' => $row['created_at']
            ];
        }
        
        echo json_encode(["success" => true, "data" => $words]);
        
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
    }
}

function deleteWord() {
    global $conn;
    
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['id'] ?? 0;
    
    if (!$id) {
        echo json_encode(["success" => false, "message" => "ID từ không hợp lệ"]);
        return;
    }
    
    try {
        $stmt = $conn->prepare("DELETE FROM dictionary WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(["success" => true, "message" => "Xóa từ thành công"]);
            } else {
                echo json_encode(["success" => false, "message" => "Không tìm thấy từ để xóa"]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Lỗi khi xóa từ"]);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
    }
}

/**
 * Bulk import words. Accepts JSON array items with fields: word, vietnamese, english_definition, example?, part_of_speech?, difficulty?
 * Skips duplicates by word.
 */
function bulkImport() {
    global $conn;
    $data = json_decode(file_get_contents("php://input"), true);
    if (!is_array($data)) { echo json_encode(["success"=>false, "message"=>"Dữ liệu không hợp lệ"]); return; }
    $inserted = 0; $skipped = 0;
    $check = $conn->prepare("SELECT id FROM dictionary WHERE word = ? LIMIT 1");
    $ins = $conn->prepare("INSERT INTO dictionary (word, phonetic, vietnamese, english_definition, example, part_of_speech, difficulty) VALUES (?, '', ?, ?, ?, ?, ?)");
    foreach ($data as $item) {
        $word = trim($item['word'] ?? '');
        $vn = trim($item['vietnamese'] ?? '');
        $def = trim($item['english_definition'] ?? '');
        if ($word === '' || $vn === '' || $def === '') { $skipped++; continue; }
        $check->bind_param('s', $word);
        $check->execute();
        $r = $check->get_result();
        if ($r && $r->num_rows > 0) { $skipped++; continue; }
        $example = $item['example'] ?? '';
        $pos = $item['part_of_speech'] ?? 'noun';
        $diff = $item['difficulty'] ?? 'beginner';
        $ins->bind_param('ssssss', $word, $vn, $def, $example, $pos, $diff);
        if ($ins->execute()) { $inserted++; } else { $skipped++; }
    }
    echo json_encode(["success"=>true, "message"=>"Imported $inserted, skipped $skipped", "inserted"=>$inserted, "skipped"=>$skipped]);
}

function getSearchSuggestions() {
    global $conn;

    $data = json_decode(file_get_contents("php://input"), true);
    $query = trim($data['query'] ?? '');

    if (empty($query)) {
        echo json_encode(["success" => true, "data" => []]);
        return;
    }

    try {
        // Tìm kiếm các từ bắt đầu bằng ký tự nhập vào (ưu tiên cao nhất)
        $stmt = $conn->prepare("SELECT word, vietnamese FROM dictionary WHERE word LIKE ? ORDER BY word ASC LIMIT 10");
        $searchTerm = $query . "%";
        $stmt->bind_param("s", $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();

        $suggestions = [];
        while ($row = $result->fetch_assoc()) {
            $suggestions[] = [
                'word' => $row['word'],
                'vietnamese' => $row['vietnamese']
            ];
        }

        // Nếu chưa đủ 10 kết quả, tìm thêm các từ chứa ký tự đó
        if (count($suggestions) < 10) {
            $stmt2 = $conn->prepare("SELECT word, vietnamese FROM dictionary WHERE word LIKE ? AND word NOT LIKE ? ORDER BY word ASC LIMIT ?");
            $containsTerm = "%" . $query . "%";
            $startsTerm = $query . "%";
            $limit = 10 - count($suggestions);
            $stmt2->bind_param("ssi", $containsTerm, $startsTerm, $limit);
            $stmt2->execute();
            $result2 = $stmt2->get_result();

            while ($row = $result2->fetch_assoc()) {
                $suggestions[] = [
                    'word' => $row['word'],
                    'vietnamese' => $row['vietnamese']
                ];
            }
            $stmt2->close();
        }

        $stmt->close();
        echo json_encode(["success" => true, "data" => $suggestions]);

    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Lỗi tìm kiếm gợi ý: " . $e->getMessage()]);
    }
}

function getUserStats() {
    global $conn;

    if (!isset($_SESSION['user_id'])) {
        // Trả về thống kê mặc định cho user chưa đăng nhập
        echo json_encode([
            "success" => true,
            "data" => [
                "totalWords" => 0,
                "correctAnswers" => 0,
                "totalAnswers" => 0,
                "streakDays" => 0,
                "accuracy" => 0,
                "todayCorrect" => 0,
                "todayTotal" => 0,
                "weeklyCorrect" => 0,
                "weeklyTotal" => 0,
                "monthlyCorrect" => 0,
                "monthlyTotal" => 0
            ]
        ]);
        return;
    }

    try {
        $userId = $_SESSION['user_id'];
        $stats = [];

        // Lấy thống kê tổng từ bảng learning_stats
        $stmt = $conn->prepare("SELECT words_learned, correct_answers, total_answers, streak_days FROM learning_stats WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $stats['totalWords'] = (int)$row['words_learned'];
            $stats['correctAnswers'] = (int)$row['correct_answers'];
            $stats['totalAnswers'] = (int)$row['total_answers'];
            $stats['streakDays'] = (int)$row['streak_days'];
            $stats['accuracy'] = $row['total_answers'] > 0 ? round(($row['correct_answers'] / $row['total_answers']) * 100) : 0;
        } else {
            // Nếu chưa có record trong learning_stats, tạo mới
            $stmt2 = $conn->prepare("INSERT INTO learning_stats (user_id, words_learned, correct_answers, total_answers, streak_days, last_study_date) VALUES (?, 0, 0, 0, 0, CURDATE())");
            $stmt2->bind_param("i", $userId);
            $stmt2->execute();
            $stmt2->close();

            $stats['totalWords'] = 0;
            $stats['correctAnswers'] = 0;
            $stats['totalAnswers'] = 0;
            $stats['streakDays'] = 0;
            $stats['accuracy'] = 0;
        }
        $stmt->close();

        // Thống kê hôm nay
        $today = date('Y-m-d');
        $stmt = $conn->prepare("SELECT COUNT(*) as total, SUM(is_correct) as correct FROM exercise_results WHERE user_id = ? AND DATE(submitted_at) = ?");
        $stmt->bind_param("is", $userId, $today);
        $stmt->execute();
        $result = $stmt->get_result();
        $todayStats = $result->fetch_assoc();
        $stats['todayCorrect'] = (int)$todayStats['correct'];
        $stats['todayTotal'] = (int)$todayStats['total'];
        $stmt->close();

        // Thống kê tuần này
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $stmt = $conn->prepare("SELECT COUNT(*) as total, SUM(is_correct) as correct FROM exercise_results WHERE user_id = ? AND DATE(submitted_at) >= ?");
        $stmt->bind_param("is", $userId, $weekStart);
        $stmt->execute();
        $result = $stmt->get_result();
        $weekStats = $result->fetch_assoc();
        $stats['weeklyCorrect'] = (int)$weekStats['correct'];
        $stats['weeklyTotal'] = (int)$weekStats['total'];
        $stmt->close();

        // Thống kê tháng này
        $monthStart = date('Y-m-01');
        $stmt = $conn->prepare("SELECT COUNT(*) as total, SUM(is_correct) as correct FROM exercise_results WHERE user_id = ? AND DATE(submitted_at) >= ?");
        $stmt->bind_param("is", $userId, $monthStart);
        $stmt->execute();
        $result = $stmt->get_result();
        $monthStats = $result->fetch_assoc();
        $stats['monthlyCorrect'] = (int)$monthStats['correct'];
        $stats['monthlyTotal'] = (int)$monthStats['total'];
        $stmt->close();

        echo json_encode(["success" => true, "data" => $stats]);

    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Lỗi lấy thống kê: " . $e->getMessage()]);
    }
}

function updateLearningStatsTrigger() {
    global $conn;

    try {
        // Drop existing trigger
        $conn->query("DROP TRIGGER IF EXISTS update_learning_stats");

        // Create improved trigger
        $triggerSQL = "
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

            -- Cập nhật streak days
            UPDATE learning_stats
            SET streak_days = CASE
                WHEN DATEDIFF(CURDATE(), last_study_date) = 1 THEN streak_days + 1
                WHEN DATEDIFF(CURDATE(), last_study_date) = 0 THEN streak_days
                ELSE 1
            END
            WHERE user_id = NEW.user_id;
        END";

        $conn->query($triggerSQL);

        echo json_encode(["success" => true, "message" => "Trigger đã được cập nhật thành công"]);

    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Lỗi cập nhật trigger: " . $e->getMessage()]);
    }
}

function submitTestAnswer() {
    global $conn;

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["success" => false, "message" => "Vui lòng đăng nhập"]);
        return;
    }

    $data = json_decode(file_get_contents("php://input"), true);
    $userId = $_SESSION['user_id'];
    $exerciseId = $data['exercise_id'] ?? 1;
    $selectedAnswer = $data['selected_answer'] ?? 0;
    $correctAnswer = $data['correct_answer'] ?? 1;
    $isCorrect = $data['is_correct'] ?? false;

    try {
        $stmt = $conn->prepare("INSERT INTO exercise_results (user_id, exercise_id, selected_answer, correct_answer, is_correct) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiii", $userId, $exerciseId, $selectedAnswer, $correctAnswer, $isCorrect ? 1 : 0);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Đã ghi nhận kết quả"]);
        } else {
            echo json_encode(["success" => false, "message" => "Lỗi ghi nhận kết quả"]);
        }

        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
    }
}
?>