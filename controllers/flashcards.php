<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../src/services/database.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'create_deck':
        requireLogin();
        createDeck();
        break;
    case 'list_decks':
        requireLogin();
        listDecks();
        break;
    case 'update_deck':
        requireLogin();
        updateDeck();
        break;
    case 'delete_deck':
        requireLogin();
        deleteDeck();
        break;
    case 'create_flashcard':
        requireLogin();
        createFlashcard();
        break;
    case 'list_flashcards':
        requireLogin();
        listFlashcards();
        break;
    case 'update_flashcard':
        requireLogin();
        updateFlashcard();
        break;
    case 'delete_flashcard':
        requireLogin();
        deleteFlashcard();
        break;
    case 'study_queue':
        requireLogin();
        getStudyQueue();
        break;
    case 'review':
        requireLogin();
        submitReview();
        break;
    case 'search_my':
        requireLogin();
        searchMyFlashcards();
        break;
    case 'add_from_dictionary':
        requireLogin();
        addFromDictionary();
        break;
    case 'ensure_preset_decks':
        requireLogin();
        ensurePresetDecks();
        break;
    case 'search_all':
        searchAll();
        break;
    case 'extract_words':
    case 'extract_keywords':
        extractKeywords();
        break;
    case 'lookup_specialized':
        lookupSpecialized();
        break;
    case 'add_words_to_decks':
        requireLogin();
        addWordsToDecks();
        break;
    case 'get_decks':
        requireLogin();
        getDecks();
        break;
    case 'add_word_to_deck':
        requireLogin();
        addWordToDeck();
        break;
    case 'transfer_deck':
        requireLogin();
        transferDeck();
        break;
    default:
        echo json_encode(["success" => false, "message" => "Action không hợp lệ"]);
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["success" => false, "message" => "Vui lòng đăng nhập"]);
        exit;
    }
}

function createDeck() {
    global $conn;
    $data = json_decode(file_get_contents('php://input'), true);
    $name = trim($data['name'] ?? '');
    $description = trim($data['description'] ?? '');
    $visibility = in_array(($data['visibility'] ?? 'private'), ['private','public']) ? $data['visibility'] : 'private';
    if ($name === '') { echo json_encode(["success" => false, "message" => "Tên bộ thẻ không được trống"]); return; }

    $stmt = $conn->prepare("INSERT INTO decks (user_id, name, description, visibility) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $_SESSION['user_id'], $name, $description, $visibility);
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Tạo bộ thẻ thành công", "deck_id" => $conn->insert_id]);
    } else {
        echo json_encode(["success" => false, "message" => "Không thể tạo bộ thẻ (tên có thể đã tồn tại)"]);
    }
    $stmt->close();
}

function listDecks() {
    global $conn;

    try {
        $stmt = $conn->prepare("SELECT id, name, description, visibility, created_at FROM decks WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $res = $stmt->get_result();
        $decks = $res->fetch_all(MYSQLI_ASSOC);

        // Add card count for each deck
        foreach ($decks as &$deck) {
            $countStmt = $conn->prepare("SELECT COUNT(*) as card_count FROM flashcards WHERE deck_id = ?");
            $countStmt->bind_param("i", $deck['id']);
            $countStmt->execute();
            $countResult = $countStmt->get_result();
            $deck['card_count'] = $countResult->fetch_assoc()['card_count'];
            $countStmt->close();
        }

        echo json_encode(["success" => true, "data" => $decks]);
    } catch (Exception $e) {
        error_log("listDecks error: " . $e->getMessage());
        echo json_encode(["success" => false, "message" => "Lỗi khi tải danh sách bộ thẻ: " . $e->getMessage()]);
    }
}

function updateDeck() {
    global $conn;
    $data = json_decode(file_get_contents('php://input'), true);
    $deckId = (int)($data['deck_id'] ?? 0);
    $name = trim($data['name'] ?? '');
    $description = trim($data['description'] ?? '');
    $visibility = in_array(($data['visibility'] ?? 'private'), ['private','public']) ? $data['visibility'] : 'private';
    if ($deckId <= 0) { echo json_encode(["success" => false, "message" => "Deck không hợp lệ"]); return; }

    // Ensure ownership
    $own = $conn->prepare("SELECT id FROM decks WHERE id = ? AND user_id = ?");
    $own->bind_param("ii", $deckId, $_SESSION['user_id']);
    $own->execute();
    if ($own->get_result()->num_rows === 0) { echo json_encode(["success" => false, "message" => "Không có quyền"]); return; }

    $stmt = $conn->prepare("UPDATE decks SET name = ?, description = ? WHERE id = ?");
    $stmt->bind_param("ssi", $name, $description, $deckId);
    $ok = $stmt->execute();
    echo json_encode(["success" => $ok, "message" => $ok ? "Cập nhật thành công" : "Cập nhật thất bại"]);
}

function deleteDeck() {
    global $conn;
    $data = json_decode(file_get_contents('php://input'), true);
    $deckId = (int)($data['deck_id'] ?? 0);
    if ($deckId <= 0) { echo json_encode(["success" => false, "message" => "Deck không hợp lệ"]); return; }

    $stmt = $conn->prepare("DELETE FROM decks WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $deckId, $_SESSION['user_id']);
    $ok = $stmt->execute();
    echo json_encode(["success" => $ok, "message" => $ok ? "Đã xóa bộ thẻ" : "Không thể xóa"]);
}

function createFlashcard() {
    global $conn;
    $data = json_decode(file_get_contents('php://input'), true);
    $deckId = (int)($data['deck_id'] ?? 0);
    $word = trim($data['word'] ?? '');
    $definition = trim($data['definition'] ?? '');
    $example = trim($data['example'] ?? '');
    $imageUrl = trim($data['image_url'] ?? '');
    $audioUrl = trim($data['audio_url'] ?? '');
    $sourceId = isset($data['source_dictionary_id']) ? (int)$data['source_dictionary_id'] : null;
    if ($deckId <= 0 || $word === '' || $definition === '') { echo json_encode(["success" => false, "message" => "Thiếu thông tin thẻ"]); return; }

    // Ownership check
    $own = $conn->prepare("SELECT id FROM decks WHERE id = ? AND user_id = ?");
    $own->bind_param("ii", $deckId, $_SESSION['user_id']);
    $own->execute();
    if ($own->get_result()->num_rows === 0) { echo json_encode(["success" => false, "message" => "Không có quyền"]); return; }

    $stmt = $conn->prepare("INSERT INTO flashcards (deck_id, word, definition, example, image_url, audio_url, source_dictionary_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $src = $sourceId; // allow null
    $stmt->bind_param("isssssi", $deckId, $word, $definition, $example, $imageUrl, $audioUrl, $src);
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Đã thêm flashcard", "flashcard_id" => $conn->insert_id]);
    } else {
        echo json_encode(["success" => false, "message" => "Không thể thêm flashcard"]);
    }
}

function listFlashcards() {
    global $conn;
    $deckId = (int)($_GET['deck_id'] ?? 0);
    if ($deckId <= 0) { echo json_encode(["success" => false, "message" => "Deck không hợp lệ"]); return; }

    // Ownership check
    $own = $conn->prepare("SELECT id FROM decks WHERE id = ? AND user_id = ?");
    $own->bind_param("ii", $deckId, $_SESSION['user_id']);
    $own->execute();
    if ($own->get_result()->num_rows === 0) { echo json_encode(["success" => false, "message" => "Không có quyền"]); return; }

    $stmt = $conn->prepare("SELECT id, word, definition, example, image_url, audio_url FROM flashcards WHERE deck_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $deckId);
    $stmt->execute();
    $cards = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode(["success" => true, "data" => $cards]);
}

function updateFlashcard() {
    global $conn;
    $data = json_decode(file_get_contents('php://input'), true);
    $flashcardId = (int)($data['flashcard_id'] ?? 0);
    $word = trim($data['word'] ?? '');
    $definition = trim($data['definition'] ?? '');
    $example = trim($data['example'] ?? '');
    $imageUrl = trim($data['image_url'] ?? '');
    $audioUrl = trim($data['audio_url'] ?? '');
    if ($flashcardId <= 0) { echo json_encode(["success" => false, "message" => "Thẻ không hợp lệ"]); return; }

    // Ownership check via deck
    $own = $conn->prepare("SELECT d.user_id FROM flashcards f JOIN decks d ON f.deck_id = d.id WHERE f.id = ? AND d.user_id = ?");
    $own->bind_param("ii", $flashcardId, $_SESSION['user_id']);
    $own->execute();
    if ($own->get_result()->num_rows === 0) { echo json_encode(["success" => false, "message" => "Không có quyền"]); return; }

    $stmt = $conn->prepare("UPDATE flashcards SET word = ?, definition = ?, example = ?, image_url = ?, audio_url = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $word, $definition, $example, $imageUrl, $audioUrl, $flashcardId);
    $ok = $stmt->execute();
    echo json_encode(["success" => $ok, "message" => $ok ? "Cập nhật thành công" : "Cập nhật thất bại"]);
}

function deleteFlashcard() {
    global $conn;
    $data = json_decode(file_get_contents('php://input'), true);
    $flashcardId = (int)($data['flashcard_id'] ?? 0);
    if ($flashcardId <= 0) { echo json_encode(["success" => false, "message" => "Thẻ không hợp lệ"]); return; }

    // Ownership check via deck
    $own = $conn->prepare("SELECT d.user_id FROM flashcards f JOIN decks d ON f.deck_id = d.id WHERE f.id = ? AND d.user_id = ?");
    $own->bind_param("ii", $flashcardId, $_SESSION['user_id']);
    $own->execute();
    if ($own->get_result()->num_rows === 0) { echo json_encode(["success" => false, "message" => "Không có quyền"]); return; }

    $stmt = $conn->prepare("DELETE FROM flashcards WHERE id = ?");
    $stmt->bind_param("i", $flashcardId);
    $ok = $stmt->execute();
    echo json_encode(["success" => $ok, "message" => $ok ? "Đã xóa thẻ" : "Không thể xóa"]);
}

function getStudyQueue() {
    global $conn;
    $deckId = (int)($_GET['deck_id'] ?? 0);
    $limit = (int)($_GET['limit'] ?? 20);
    if ($deckId <= 0) { echo json_encode(["success" => false, "message" => "Deck không hợp lệ"]); return; }

    // Ownership check
    $own = $conn->prepare("SELECT id FROM decks WHERE id = ? AND user_id = ?");
    $own->bind_param("ii", $deckId, $_SESSION['user_id']);
    $own->execute();
    if ($own->get_result()->num_rows === 0) { echo json_encode(["success" => false, "message" => "Không có quyền"]); return; }

    // Due cards first, then new
    $sql = "SELECT f.id, f.word, f.definition, f.example, f.image_url, f.audio_url,
                   sp.status, sp.ease_level, sp.next_due_at
            FROM flashcards f
            LEFT JOIN study_progress sp ON sp.flashcard_id = f.id AND sp.user_id = ?
            WHERE f.deck_id = ? AND (sp.next_due_at IS NULL OR sp.next_due_at <= NOW())
            ORDER BY (sp.next_due_at IS NULL) DESC, sp.next_due_at ASC
            LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $_SESSION['user_id'], $deckId, $limit);
    $stmt->execute();
    $cards = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode(["success" => true, "data" => $cards]);
}

function submitReview() {
    global $conn;
    $data = json_decode(file_get_contents('php://input'), true);
    $flashcardId = (int)($data['flashcard_id'] ?? 0);
    $rating = $data['rating'] ?? 'hard'; // again|hard|good|easy
    if (!in_array($rating, ['again','hard','good','easy'])) { $rating = 'hard'; }
    if ($flashcardId <= 0) { echo json_encode(["success" => false, "message" => "Thẻ không hợp lệ"]); return; }

    // Ownership via deck
    $own = $conn->prepare("SELECT d.user_id FROM flashcards f JOIN decks d ON f.deck_id = d.id WHERE f.id = ? AND d.user_id = ?");
    $own->bind_param("ii", $flashcardId, $_SESSION['user_id']);
    $own->execute();
    if ($own->get_result()->num_rows === 0) { echo json_encode(["success" => false, "message" => "Không có quyền"]); return; }

    // Scheduling logic (SM-2)
    // Map rating to quality 0..5
    $quality = 3; // default
    if ($rating === 'again') { $quality = 2; }
    elseif ($rating === 'hard') { $quality = 3; }
    elseif ($rating === 'good') { $quality = 4; }
    elseif ($rating === 'easy') { $quality = 5; }

    // Fetch existing progress
    $prog = $conn->prepare("SELECT sm2_ease_factor, sm2_interval_days, sm2_repetitions FROM study_progress WHERE user_id = ? AND flashcard_id = ?");
    $prog->bind_param("ii", $_SESSION['user_id'], $flashcardId);
    $prog->execute();
    $row = $prog->get_result()->fetch_assoc();
    $ef = $row && isset($row['sm2_ease_factor']) ? (float)$row['sm2_ease_factor'] : 2.5;
    $intervalDays = $row && isset($row['sm2_interval_days']) ? (int)$row['sm2_interval_days'] : 0;
    $reps = $row && isset($row['sm2_repetitions']) ? (int)$row['sm2_repetitions'] : 0;

    if ($quality < 3) {
        $reps = 0;
        $intervalDays = 1; // repeat soon
    } else {
        if ($reps == 0) {
            $intervalDays = 1;
        } elseif ($reps == 1) {
            $intervalDays = 6;
        } else {
            $intervalDays = (int)round($intervalDays * $ef);
            if ($intervalDays < 1) { $intervalDays = 1; }
        }
        $reps += 1;
        // Update ease factor
        $ef = $ef + (0.1 - (5 - $quality) * (0.08 + (5 - $quality) * 0.02));
        if ($ef < 1.3) { $ef = 1.3; }
    }

    // Determine status approximately
    $status = 'learning';
    if ($reps >= 2) { $status = 'review'; }
    if ($reps >= 8 && $intervalDays >= 21) { $status = 'mastered'; }

    $nextDue = (new DateTime())->modify("+{$intervalDays} day")->format('Y-m-d H:i:s');

    // Upsert progress
    $sql = "INSERT INTO study_progress (user_id, flashcard_id, status, ease_level, review_count, correct_count, incorrect_count, last_reviewed_at, next_due_at, sm2_ease_factor, sm2_interval_days, sm2_repetitions)
            VALUES (?, ?, ?, ?, 1, ?, ?, NOW(), ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                status = VALUES(status),
                ease_level = VALUES(ease_level),
                review_count = review_count + 1,
                correct_count = correct_count + VALUES(correct_count),
                incorrect_count = incorrect_count + VALUES(incorrect_count),
                last_reviewed_at = NOW(),
                next_due_at = VALUES(next_due_at),
                sm2_ease_factor = VALUES(sm2_ease_factor),
                sm2_interval_days = VALUES(sm2_interval_days),
                sm2_repetitions = VALUES(sm2_repetitions)";

    $isCorrect = in_array($rating, ['good','easy']) ? 1 : 0;
    $isIncorrect = in_array($rating, ['again','hard']) ? 1 : 0;
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissiiisdii", $_SESSION['user_id'], $flashcardId, $status, $rating, $isCorrect, $isIncorrect, $nextDue, $ef, $intervalDays, $reps);
    $ok = $stmt->execute();
    echo json_encode(["success" => $ok, "message" => $ok ? "Đã lưu đánh giá" : "Không thể lưu" , "next_due_at" => $nextDue]);
}

function searchMyFlashcards() {
    global $conn;
    $data = json_decode(file_get_contents('php://input'), true);
    $q = trim($data['q'] ?? '');
    if ($q === '') { echo json_encode(["success" => true, "data" => []]); return; }
    $like = "%$q%";
    // Return all decks containing the term; show duplicates across decks
    $sql = "SELECT f.id, f.word, f.definition, d.name as deck_name, d.id as deck_id
            FROM flashcards f JOIN decks d ON f.deck_id = d.id
            WHERE d.user_id = ? AND (f.word LIKE ? OR f.definition LIKE ?) 
            ORDER BY d.name ASC, f.word ASC LIMIT 200";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $_SESSION['user_id'], $like, $like);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode(["success" => true, "data" => $rows]);
}

/**
 * Add a dictionary word to one or multiple decks of the current user.
 * Input JSON: { deck_ids: number[], word: string, definition?: string, example?: string, source_dictionary_id?: number }
 */
function addFromDictionary() {
    global $conn;
    $data = json_decode(file_get_contents('php://input'), true);
    $deckIds = $data['deck_ids'] ?? [];
    $word = trim($data['word'] ?? '');
    $definition = trim($data['definition'] ?? '');
    $example = trim($data['example'] ?? '');
    $sourceId = isset($data['source_dictionary_id']) ? (int)$data['source_dictionary_id'] : null;

    if (!is_array($deckIds) || count($deckIds) === 0) { echo json_encode(["success" => false, "message" => "Chọn ít nhất 1 bộ thẻ"]); return; }
    if ($word === '' || $definition === '') { echo json_encode(["success" => false, "message" => "Thiếu từ hoặc định nghĩa"]); return; }

    // Ensure all deck ids belong to the user (validate one by one to avoid dynamic binding issues)
    $valid = [];
    $own = $conn->prepare("SELECT id FROM decks WHERE id = ? AND user_id = ?");
    foreach ($deckIds as $did) {
        $deckIdCandidate = (int)$did;
        if ($deckIdCandidate <= 0) { continue; }
        $own->bind_param("ii", $deckIdCandidate, $_SESSION['user_id']);
        $own->execute();
        $r = $own->get_result();
        if ($r && $r->num_rows > 0) { $valid[] = $deckIdCandidate; }
    }
    if (count($valid) === 0) { echo json_encode(["success" => false, "message" => "Bộ thẻ không hợp lệ"]); return; }

    // Insert for each valid deck; ignore duplicates of same word within a deck
    $insert = $conn->prepare("INSERT INTO flashcards (deck_id, word, definition, example, source_dictionary_id) VALUES (?, ?, ?, ?, ?) ");
    $created = 0;
    foreach ($valid as $deckId) {
        // Check duplication by same word in same deck
        $check = $conn->prepare("SELECT id FROM flashcards WHERE deck_id = ? AND word = ? LIMIT 1");
        $check->bind_param("is", $deckId, $word);
        $check->execute();
        if ($check->get_result()->num_rows > 0) { continue; }
        $src = $sourceId;
        $insert->bind_param("isssi", $deckId, $word, $definition, $example, $src);
        if ($insert->execute()) { $created++; }
    }

    echo json_encode(["success" => true, "message" => $created > 0 ? "Đã thêm vào $created bộ thẻ" : "Từ đã tồn tại trong các bộ thẻ đã chọn", "created" => $created]);
}

/**
 * Ensure preset decks exist for current user (one-time per preset)
 * Will create decks named as presets if they don't exist yet.
 */
function ensurePresetDecks() {
    global $conn;
    // Get presets
    $pres = $conn->query("SELECT name, slug, description FROM preset_decks");
    $created = 0;
    while ($p = $pres->fetch_assoc()) {
        // Insert if not exists for this user
        $stmt = $conn->prepare("INSERT IGNORE INTO decks (user_id, name, description, visibility) VALUES (?, ?, ?, 'private')");
        $stmt->bind_param("iss", $_SESSION['user_id'], $p['name'], $p['description']);
        if ($stmt->execute() && $stmt->affected_rows > 0) { $created++; }
    }
    echo json_encode(["success" => true, "message" => $created > 0 ? "Đã tạo $created bộ thẻ mẫu" : "Bộ thẻ mẫu đã sẵn có", "created" => $created]);
}

function searchAll() {
    global $conn;
    $q = trim($_GET['q'] ?? '');
    if ($q === '') { echo json_encode(["success" => true, "data" => []]); return; }
    $like = "%$q%";
    // Search in public decks and dictionary
    $results = [ 'flashcards' => [], 'dictionary' => [] ];

    $stmt = $conn->prepare("SELECT f.id, f.word, f.definition, d.name as deck_name
                             FROM flashcards f JOIN decks d ON f.deck_id = d.id
                             WHERE d.visibility = 'public' AND (f.word LIKE ? OR f.definition LIKE ?) LIMIT 50");
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $results['flashcards'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmt = $conn->prepare("SELECT id, word, vietnamese, english_definition FROM dictionary WHERE word LIKE ? OR vietnamese LIKE ? LIMIT 50");
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $results['dictionary'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode(["success" => true, "data" => $results]);
}

/**
 * Extract keywords from pasted text and suggest definitions from dictionary/specialized_terms
 * Input: { text: string, top_k?: number, min_length?: number, domain?: string }
 */
function extractKeywords() {
    global $conn;

    try {
        $data = json_decode(file_get_contents('php://input'), true);
        if ($data === null) {
            echo json_encode(["success"=>false, "message"=>"Dữ liệu JSON không hợp lệ"]);
            return;
        }

        $text = trim($data['text'] ?? '');
        $topK = (int)($data['top_k'] ?? 20);
        $minLen = (int)($data['min_length'] ?? 1);
        $domain = trim($data['domain'] ?? '');

        if ($text === '') {
            echo json_encode(["success"=>false, "message"=>"Văn bản trống"]);
            return;
        }

    // Ensure basic dictionary entries exist
    ensureBasicDictionary();

    // Improved tokenization for English text
    // Handle contractions and common patterns
    $text = str_replace(["'ll", "'re", "'ve", "'d", "'s", "n't"], [" will", " are", " have", " would", " is", " not"], $text);

    // Extract words using regex - more flexible approach
    preg_match_all('/\b[A-Za-z]+\b/', $text, $matches);
    $words = $matches[0];

    $tokens = [];
    foreach ($words as $word) {
        $cleanWord = strtolower(trim($word));
        if (strlen($cleanWord) >= $minLen && ctype_alpha($cleanWord)) {
            $tokens[] = $cleanWord;
        }
    }

    if (empty($tokens)) {
        echo json_encode(["success"=>true, "data"=>[], "message"=>"Không tìm thấy từ nào trong văn bản"]);
        return;
    }

    // Minimal stop words - only remove truly common function words
    $stop = [ 'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'from', 'up', 'about', 'into', 'through', 'during', 'before', 'after', 'above', 'below', 'between', 'among', 'this', 'that', 'these', 'those', 'it', 'its', 'itself' ];
    $stopSet = array_flip($stop);
    $freq = [];
    foreach ($tokens as $t) {
        if (strlen($t) < $minLen) continue;
        if (isset($stopSet[$t])) continue;
        $freq[$t] = ($freq[$t] ?? 0) + 1;
    }
    arsort($freq);
    $candidates = array_slice(array_keys($freq), 0, $topK);

    $suggestions = [];
    // Prepare statements
    $stmtDict = $conn->prepare("SELECT id, word, vietnamese, english_definition, example FROM dictionary WHERE word = ? LIMIT 1");
    $stmtSpecWordDom = null;
    if ($domain !== '') {
        $stmtSpecWordDom = $conn->prepare("SELECT domain, word, vietnamese, english_definition, example FROM specialized_terms WHERE domain = ? AND word = ? LIMIT 1");
    }
    $stmtSpecWord = $conn->prepare("SELECT domain, word, vietnamese, english_definition, example FROM specialized_terms WHERE word = ? LIMIT 1");

    // Check if tables exist
    $dictExists = $conn->query("SHOW TABLES LIKE 'dictionary'")->num_rows > 0;
    $specExists = $conn->query("SHOW TABLES LIKE 'specialized_terms'")->num_rows > 0;

    foreach ($candidates as $w) {
        $entry = [ 'word' => $w, 'source' => 'none', 'domain' => null, 'vietnamese' => null, 'english_definition' => null, 'example' => null, 'dictionary_id' => null ];
        $found = false;

        // specialized first if domain
        if ($domain !== '' && $stmtSpecWordDom && $specExists) {
            $stmtSpecWordDom->bind_param('ss', $domain, $w);
            $stmtSpecWordDom->execute();
            $rs = $stmtSpecWordDom->get_result();
            if ($rs && $rs->num_rows > 0) {
                $row = $rs->fetch_assoc();
                $entry['source'] = 'specialized';
                $entry['domain'] = $row['domain'];
                $entry['vietnamese'] = $row['vietnamese'];
                $entry['english_definition'] = $row['english_definition'];
                $entry['example'] = $row['example'];
                $found = true;
                $suggestions[] = $entry; continue;
            }
        }
        // specialized any domain
        if (!$found && $specExists) {
            $stmtSpecWord->bind_param('s', $w);
            $stmtSpecWord->execute();
            $rs = $stmtSpecWord->get_result();
            if ($rs && $rs->num_rows > 0) {
                $row = $rs->fetch_assoc();
                $entry['source'] = 'specialized';
                $entry['domain'] = $row['domain'];
                $entry['vietnamese'] = $row['vietnamese'];
                $entry['english_definition'] = $row['english_definition'];
                $entry['example'] = $row['example'];
                $found = true;
                $suggestions[] = $entry; continue;
            }
        }

        // fallback to dictionary
        if (!$found && $dictExists) {
            $stmtDict->bind_param('s', $w);
            $stmtDict->execute();
            $rd = $stmtDict->get_result();
            if ($rd && $rd->num_rows > 0) {
                $row = $rd->fetch_assoc();
                $entry['source'] = 'dictionary';
                $entry['dictionary_id'] = $row['id'];
                $entry['vietnamese'] = $row['vietnamese'];
                $entry['english_definition'] = $row['english_definition'];
                $entry['example'] = $row['example'];
                $found = true;
            }
        }

        // Only add entries that were found in dictionary
        if ($found) {
            $suggestions[] = $entry;
        }
    }

        echo json_encode([ 'success' => true, 'data' => $suggestions ]);

    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
    }
}

/**
 * Lookup a single term from specialized_terms optionally scoped by domain
 * Input: { word: string, domain?: string }
 */
function lookupSpecialized() {
    global $conn;
    $data = json_decode(file_get_contents('php://input'), true);
    $word = trim($data['word'] ?? '');
    $domain = trim($data['domain'] ?? '');
    if ($word === '') { echo json_encode(["success"=>false, "message"=>"Thiếu từ"]); return; }
    if ($domain !== '') {
        $stmt = $conn->prepare("SELECT domain, word, vietnamese, english_definition, example FROM specialized_terms WHERE domain = ? AND word = ? LIMIT 1");
        $stmt->bind_param('ss', $domain, $word);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if ($row) { echo json_encode([ 'success'=>true, 'data'=>$row ]); return; }
    }
    $stmt = $conn->prepare("SELECT domain, word, vietnamese, english_definition, example FROM specialized_terms WHERE word = ? LIMIT 1");
    $stmt->bind_param('s', $word);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if ($row) { echo json_encode([ 'success'=>true, 'data'=>$row ]); return; }
    echo json_encode([ 'success'=>false, 'message'=>'Không tìm thấy' ]);
}

function addWordsToDecks() {
    global $conn;
    $data = json_decode(file_get_contents('php://input'), true);
    $words = $data['words'] ?? [];
    $deckIds = $data['deck_ids'] ?? [];

    if (empty($words) || empty($deckIds)) {
        echo json_encode(["success" => false, "message" => "Thiếu dữ liệu từ vựng hoặc bộ thẻ"]);
        return;
    }

    // Validate deck ownership
    $validDecks = [];
    $ownStmt = $conn->prepare("SELECT id FROM decks WHERE id = ? AND user_id = ?");
    foreach ($deckIds as $deckId) {
        $deckId = (int)$deckId;
        if ($deckId <= 0) continue;
        $ownStmt->bind_param("ii", $deckId, $_SESSION['user_id']);
        $ownStmt->execute();
        if ($ownStmt->get_result()->num_rows > 0) {
            $validDecks[] = $deckId;
        }
    }

    if (empty($validDecks)) {
        echo json_encode(["success" => false, "message" => "Không có bộ thẻ hợp lệ"]);
        return;
    }

    $insertStmt = $conn->prepare("INSERT IGNORE INTO flashcards (deck_id, word, definition, example) VALUES (?, ?, ?, ?)");
    $totalAdded = 0;

    foreach ($words as $word) {
        $wordText = trim($word['word'] ?? '');
        $definition = trim($word['definition'] ?? '');
        $example = trim($word['example'] ?? '');

        if (empty($wordText) || empty($definition)) continue;

        foreach ($validDecks as $deckId) {
            $insertStmt->bind_param("isss", $deckId, $wordText, $definition, $example);
            if ($insertStmt->execute() && $insertStmt->affected_rows > 0) {
                $totalAdded++;
            }
        }
    }

    echo json_encode([
        "success" => true,
        "message" => "Đã thêm $totalAdded từ vào bộ thẻ",
        "added_count" => $totalAdded
    ]);
}

function ensureBasicDictionary() {
    global $conn;

    // Create dictionary table if not exists
    $conn->query("CREATE TABLE IF NOT EXISTS dictionary (
        id INT AUTO_INCREMENT PRIMARY KEY,
        word VARCHAR(255) NOT NULL UNIQUE,
        vietnamese TEXT,
        english_definition TEXT,
        example TEXT,
        part_of_speech VARCHAR(50) DEFAULT 'noun',
        difficulty ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_word (word)
    )");

    // Check if dictionary has basic words
    $result = $conn->query("SELECT COUNT(*) as count FROM dictionary");
    if (!$result) return; // Table doesn't exist or error

    $count = $result->fetch_assoc()['count'];
    if ($count > 10) return; // Already has data

    // Add comprehensive English vocabulary for testing
    $basicWords = [
        // Pronouns & Basic verbs
        ['word' => 'I', 'vietnamese' => 'tôi', 'english_definition' => 'first person singular pronoun'],
        ['word' => 'you', 'vietnamese' => 'bạn', 'english_definition' => 'second person pronoun'],
        ['word' => 'he', 'vietnamese' => 'anh ấy', 'english_definition' => 'third person masculine pronoun'],
        ['word' => 'she', 'vietnamese' => 'cô ấy', 'english_definition' => 'third person feminine pronoun'],
        ['word' => 'we', 'vietnamese' => 'chúng tôi', 'english_definition' => 'first person plural pronoun'],
        ['word' => 'they', 'vietnamese' => 'họ', 'english_definition' => 'third person plural pronoun'],
        ['word' => 'am', 'vietnamese' => 'là', 'english_definition' => 'first person singular of be'],
        ['word' => 'is', 'vietnamese' => 'là', 'english_definition' => 'third person singular of be'],
        ['word' => 'are', 'vietnamese' => 'là', 'english_definition' => 'plural form of be'],
        ['word' => 'was', 'vietnamese' => 'đã là', 'english_definition' => 'past tense of be'],
        ['word' => 'were', 'vietnamese' => 'đã là', 'english_definition' => 'past tense plural of be'],
        ['word' => 'have', 'vietnamese' => 'có', 'english_definition' => 'to possess or own'],
        ['word' => 'has', 'vietnamese' => 'có', 'english_definition' => 'third person singular of have'],
        ['word' => 'had', 'vietnamese' => 'đã có', 'english_definition' => 'past tense of have'],
        ['word' => 'do', 'vietnamese' => 'làm', 'english_definition' => 'to perform an action'],
        ['word' => 'does', 'vietnamese' => 'làm', 'english_definition' => 'third person singular of do'],
        ['word' => 'did', 'vietnamese' => 'đã làm', 'english_definition' => 'past tense of do'],
        ['word' => 'will', 'vietnamese' => 'sẽ', 'english_definition' => 'future tense auxiliary verb'],
        ['word' => 'would', 'vietnamese' => 'sẽ', 'english_definition' => 'conditional auxiliary verb'],
        ['word' => 'can', 'vietnamese' => 'có thể', 'english_definition' => 'to be able to'],
        ['word' => 'could', 'vietnamese' => 'có thể', 'english_definition' => 'past tense of can'],
        ['word' => 'should', 'vietnamese' => 'nên', 'english_definition' => 'ought to'],
        ['word' => 'must', 'vietnamese' => 'phải', 'english_definition' => 'to be obliged to'],

        // Common nouns
        ['word' => 'man', 'vietnamese' => 'người đàn ông', 'english_definition' => 'adult male human'],
        ['word' => 'woman', 'vietnamese' => 'người phụ nữ', 'english_definition' => 'adult female human'],
        ['word' => 'child', 'vietnamese' => 'trẻ em', 'english_definition' => 'young human being'],
        ['word' => 'people', 'vietnamese' => 'mọi người', 'english_definition' => 'human beings in general'],
        ['word' => 'family', 'vietnamese' => 'gia đình', 'english_definition' => 'group of related people'],
        ['word' => 'friend', 'vietnamese' => 'bạn bè', 'english_definition' => 'person you like and know well'],
        ['word' => 'home', 'vietnamese' => 'nhà', 'english_definition' => 'place where you live'],
        ['word' => 'house', 'vietnamese' => 'ngôi nhà', 'english_definition' => 'building for human habitation'],
        ['word' => 'school', 'vietnamese' => 'trường học', 'english_definition' => 'institution for education'],
        ['word' => 'work', 'vietnamese' => 'công việc', 'english_definition' => 'activity involving mental or physical effort'],
        ['word' => 'job', 'vietnamese' => 'việc làm', 'english_definition' => 'paid position of employment'],
        ['word' => 'money', 'vietnamese' => 'tiền', 'english_definition' => 'medium of exchange'],
        ['word' => 'food', 'vietnamese' => 'thức ăn', 'english_definition' => 'nutritious substance consumed'],
        ['word' => 'water', 'vietnamese' => 'nước', 'english_definition' => 'colorless liquid essential for life'],
        ['word' => 'car', 'vietnamese' => 'xe hơi', 'english_definition' => 'road vehicle with engine'],
        ['word' => 'book', 'vietnamese' => 'sách', 'english_definition' => 'written or printed work'],
        ['word' => 'phone', 'vietnamese' => 'điện thoại', 'english_definition' => 'device for communication'],
        ['word' => 'computer', 'vietnamese' => 'máy tính', 'english_definition' => 'electronic device for processing data'],

        // Common adjectives
        ['word' => 'good', 'vietnamese' => 'tốt', 'english_definition' => 'of high quality'],
        ['word' => 'bad', 'vietnamese' => 'xấu', 'english_definition' => 'of poor quality'],
        ['word' => 'big', 'vietnamese' => 'lớn', 'english_definition' => 'of considerable size'],
        ['word' => 'small', 'vietnamese' => 'nhỏ', 'english_definition' => 'of limited size'],
        ['word' => 'new', 'vietnamese' => 'mới', 'english_definition' => 'recently made or created'],
        ['word' => 'old', 'vietnamese' => 'cũ', 'english_definition' => 'having existed for a long time'],
        ['word' => 'young', 'vietnamese' => 'trẻ', 'english_definition' => 'having lived for a short time'],
        ['word' => 'beautiful', 'vietnamese' => 'đẹp', 'english_definition' => 'pleasing to look at'],
        ['word' => 'happy', 'vietnamese' => 'vui', 'english_definition' => 'feeling pleasure'],
        ['word' => 'sad', 'vietnamese' => 'buồn', 'english_definition' => 'feeling sorrow'],
        ['word' => 'easy', 'vietnamese' => 'dễ', 'english_definition' => 'not difficult'],
        ['word' => 'hard', 'vietnamese' => 'khó', 'english_definition' => 'difficult to do'],
        ['word' => 'fast', 'vietnamese' => 'nhanh', 'english_definition' => 'moving quickly'],
        ['word' => 'slow', 'vietnamese' => 'chậm', 'english_definition' => 'moving at low speed'],

        // Common verbs
        ['word' => 'go', 'vietnamese' => 'đi', 'english_definition' => 'to move from one place to another'],
        ['word' => 'come', 'vietnamese' => 'đến', 'english_definition' => 'to move toward'],
        ['word' => 'see', 'vietnamese' => 'nhìn', 'english_definition' => 'to perceive with eyes'],
        ['word' => 'look', 'vietnamese' => 'nhìn', 'english_definition' => 'to direct eyes toward'],
        ['word' => 'hear', 'vietnamese' => 'nghe', 'english_definition' => 'to perceive sound'],
        ['word' => 'listen', 'vietnamese' => 'lắng nghe', 'english_definition' => 'to pay attention to sound'],
        ['word' => 'speak', 'vietnamese' => 'nói', 'english_definition' => 'to say words'],
        ['word' => 'talk', 'vietnamese' => 'nói chuyện', 'english_definition' => 'to communicate verbally'],
        ['word' => 'read', 'vietnamese' => 'đọc', 'english_definition' => 'to look at and understand text'],
        ['word' => 'write', 'vietnamese' => 'viết', 'english_definition' => 'to mark letters or words'],
        ['word' => 'eat', 'vietnamese' => 'ăn', 'english_definition' => 'to consume food'],
        ['word' => 'drink', 'vietnamese' => 'uống', 'english_definition' => 'to consume liquid'],
        ['word' => 'sleep', 'vietnamese' => 'ngủ', 'english_definition' => 'to rest with eyes closed'],
        ['word' => 'walk', 'vietnamese' => 'đi bộ', 'english_definition' => 'to move on foot'],
        ['word' => 'run', 'vietnamese' => 'chạy', 'english_definition' => 'to move quickly on foot'],
        ['word' => 'play', 'vietnamese' => 'chơi', 'english_definition' => 'to engage in activity for enjoyment'],
        ['word' => 'study', 'vietnamese' => 'học', 'english_definition' => 'to learn about something'],
        ['word' => 'learn', 'vietnamese' => 'học', 'english_definition' => 'to acquire knowledge'],
        ['word' => 'teach', 'vietnamese' => 'dạy', 'english_definition' => 'to give knowledge to others'],
        ['word' => 'help', 'vietnamese' => 'giúp đỡ', 'english_definition' => 'to assist someone'],
        ['word' => 'love', 'vietnamese' => 'yêu', 'english_definition' => 'to feel deep affection'],
        ['word' => 'like', 'vietnamese' => 'thích', 'english_definition' => 'to find agreeable'],
        ['word' => 'want', 'vietnamese' => 'muốn', 'english_definition' => 'to desire'],
        ['word' => 'need', 'vietnamese' => 'cần', 'english_definition' => 'to require'],
        ['word' => 'know', 'vietnamese' => 'biết', 'english_definition' => 'to be aware of'],
        ['word' => 'think', 'vietnamese' => 'nghĩ', 'english_definition' => 'to use mind to consider'],
        ['word' => 'feel', 'vietnamese' => 'cảm thấy', 'english_definition' => 'to experience emotion'],
        ['word' => 'make', 'vietnamese' => 'làm', 'english_definition' => 'to create or produce'],
        ['word' => 'take', 'vietnamese' => 'lấy', 'english_definition' => 'to get hold of'],
        ['word' => 'give', 'vietnamese' => 'cho', 'english_definition' => 'to provide'],
        ['word' => 'get', 'vietnamese' => 'lấy', 'english_definition' => 'to obtain'],
        ['word' => 'put', 'vietnamese' => 'đặt', 'english_definition' => 'to place'],
        ['word' => 'find', 'vietnamese' => 'tìm', 'english_definition' => 'to discover'],
        ['word' => 'buy', 'vietnamese' => 'mua', 'english_definition' => 'to purchase'],
        ['word' => 'sell', 'vietnamese' => 'bán', 'english_definition' => 'to exchange for money'],

        // Time & Numbers
        ['word' => 'time', 'vietnamese' => 'thời gian', 'english_definition' => 'indefinite continued progress of existence'],
        ['word' => 'day', 'vietnamese' => 'ngày', 'english_definition' => 'period of 24 hours'],
        ['word' => 'night', 'vietnamese' => 'đêm', 'english_definition' => 'time of darkness'],
        ['word' => 'week', 'vietnamese' => 'tuần', 'english_definition' => 'period of seven days'],
        ['word' => 'month', 'vietnamese' => 'tháng', 'english_definition' => 'period of about 30 days'],
        ['word' => 'year', 'vietnamese' => 'năm', 'english_definition' => 'period of 365 days'],
        ['word' => 'today', 'vietnamese' => 'hôm nay', 'english_definition' => 'this present day'],
        ['word' => 'tomorrow', 'vietnamese' => 'ngày mai', 'english_definition' => 'the day after today'],
        ['word' => 'yesterday', 'vietnamese' => 'hôm qua', 'english_definition' => 'the day before today'],
        ['word' => 'morning', 'vietnamese' => 'buổi sáng', 'english_definition' => 'early part of day'],
        ['word' => 'afternoon', 'vietnamese' => 'buổi chiều', 'english_definition' => 'middle part of day'],
        ['word' => 'evening', 'vietnamese' => 'buổi tối', 'english_definition' => 'end part of day'],
        ['word' => 'one', 'vietnamese' => 'một', 'english_definition' => 'number 1'],
        ['word' => 'two', 'vietnamese' => 'hai', 'english_definition' => 'number 2'],
        ['word' => 'three', 'vietnamese' => 'ba', 'english_definition' => 'number 3'],
        ['word' => 'four', 'vietnamese' => 'bốn', 'english_definition' => 'number 4'],
        ['word' => 'five', 'vietnamese' => 'năm', 'english_definition' => 'number 5'],
        ['word' => 'ten', 'vietnamese' => 'mười', 'english_definition' => 'number 10'],
        ['word' => 'hundred', 'vietnamese' => 'trăm', 'english_definition' => 'number 100'],

        // Colors
        ['word' => 'red', 'vietnamese' => 'đỏ', 'english_definition' => 'color of blood'],
        ['word' => 'blue', 'vietnamese' => 'xanh dương', 'english_definition' => 'color of sky'],
        ['word' => 'green', 'vietnamese' => 'xanh lá', 'english_definition' => 'color of grass'],
        ['word' => 'yellow', 'vietnamese' => 'vàng', 'english_definition' => 'color of sun'],
        ['word' => 'black', 'vietnamese' => 'đen', 'english_definition' => 'darkest color'],
        ['word' => 'white', 'vietnamese' => 'trắng', 'english_definition' => 'lightest color'],

        // Animals
        ['word' => 'cat', 'vietnamese' => 'mèo', 'english_definition' => 'small domesticated carnivorous mammal'],
        ['word' => 'dog', 'vietnamese' => 'chó', 'english_definition' => 'domesticated carnivorous mammal'],
        ['word' => 'bird', 'vietnamese' => 'chim', 'english_definition' => 'feathered flying animal'],
        ['word' => 'fish', 'vietnamese' => 'cá', 'english_definition' => 'aquatic animal'],

        // Food
        ['word' => 'apple', 'vietnamese' => 'táo', 'english_definition' => 'round fruit of tree'],
        ['word' => 'banana', 'vietnamese' => 'chuối', 'english_definition' => 'yellow curved fruit'],
        ['word' => 'orange', 'vietnamese' => 'cam', 'english_definition' => 'citrus fruit'],
        ['word' => 'rice', 'vietnamese' => 'cơm', 'english_definition' => 'staple food grain'],
        ['word' => 'bread', 'vietnamese' => 'bánh mì', 'english_definition' => 'baked food made from flour'],
        ['word' => 'milk', 'vietnamese' => 'sữa', 'english_definition' => 'white liquid from mammals'],
        ['word' => 'coffee', 'vietnamese' => 'cà phê', 'english_definition' => 'drink made from coffee beans'],
        ['word' => 'tea', 'vietnamese' => 'trà', 'english_definition' => 'drink made from tea leaves'],

        // Nature
        ['word' => 'sun', 'vietnamese' => 'mặt trời', 'english_definition' => 'star that earth orbits'],
        ['word' => 'moon', 'vietnamese' => 'mặt trăng', 'english_definition' => 'natural satellite of earth'],
        ['word' => 'star', 'vietnamese' => 'ngôi sao', 'english_definition' => 'luminous celestial body'],
        ['word' => 'tree', 'vietnamese' => 'cây', 'english_definition' => 'woody perennial plant'],
        ['word' => 'flower', 'vietnamese' => 'hoa', 'english_definition' => 'reproductive structure of plant'],
        ['word' => 'grass', 'vietnamese' => 'cỏ', 'english_definition' => 'small green plants'],
        ['word' => 'mountain', 'vietnamese' => 'núi', 'english_definition' => 'large natural elevation'],
        ['word' => 'river', 'vietnamese' => 'sông', 'english_definition' => 'flowing body of water'],
        ['word' => 'sea', 'vietnamese' => 'biển', 'english_definition' => 'large body of salt water'],
        ['word' => 'sky', 'vietnamese' => 'bầu trời', 'english_definition' => 'space above earth'],
        ['word' => 'cloud', 'vietnamese' => 'đám mây', 'english_definition' => 'visible mass of water vapor'],
        ['word' => 'rain', 'vietnamese' => 'mưa', 'english_definition' => 'water falling from clouds'],
        ['word' => 'wind', 'vietnamese' => 'gió', 'english_definition' => 'moving air'],

        // Common names (examples)
        ['word' => 'Long', 'vietnamese' => 'Long (tên)', 'english_definition' => 'Vietnamese given name'],
        ['word' => 'John', 'vietnamese' => 'John (tên)', 'english_definition' => 'English given name'],
        ['word' => 'Mary', 'vietnamese' => 'Mary (tên)', 'english_definition' => 'English given name']
    ];

    $stmt = $conn->prepare("INSERT IGNORE INTO dictionary (word, vietnamese, english_definition, part_of_speech, difficulty) VALUES (?, ?, ?, 'noun', 'beginner')");
    foreach ($basicWords as $word) {
        $stmt->bind_param("sss", $word['word'], $word['vietnamese'], $word['english_definition']);
        $stmt->execute();
    }
}

function getDecks() {
    global $conn;

    // User login is already checked by requireLogin() before this function
    $userId = $_SESSION['user_id'];

    try {
        // Debug: Check what user_id we're using
        error_log("getDecks: Looking for decks for user_id = " . $userId);

        $stmt = $conn->prepare("SELECT id, name, description FROM decks WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $decks = [];
        while ($row = $result->fetch_assoc()) {
            $decks[] = $row;
        }

        // Debug: Log the number of decks found
        error_log("getDecks: Found " . count($decks) . " decks for user " . $userId);

        // If no decks found, let's check if there are any decks at all
        if (count($decks) === 0) {
            $allDecksResult = $conn->query("SELECT COUNT(*) as total, GROUP_CONCAT(DISTINCT user_id) as user_ids FROM decks");
            if ($allDecksResult) {
                $allDecksRow = $allDecksResult->fetch_assoc();
                error_log("getDecks: Total decks in system: " . $allDecksRow['total'] . ", User IDs: " . $allDecksRow['user_ids']);
            }
        }

        echo json_encode(["success" => true, "data" => $decks, "debug_user_id" => $userId]);
    } catch (Exception $e) {
        error_log("getDecks error: " . $e->getMessage());
        echo json_encode(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
    }
}

function addWordToDeck() {
    global $conn;
    $userId = $_SESSION['user_id'];
    $data = json_decode(file_get_contents('php://input'), true);

    $deckId = (int)($data['deck_id'] ?? 0);
    $word = trim($data['word'] ?? '');
    $definition = trim($data['definition'] ?? '');
    $example = trim($data['example'] ?? '');

    if ($deckId <= 0 || $word === '') {
        echo json_encode(["success" => false, "message" => "Dữ liệu không hợp lệ"]);
        return;
    }

    try {
        // Verify deck belongs to user
        $stmt = $conn->prepare("SELECT id FROM decks WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $deckId, $userId);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            echo json_encode(["success" => false, "message" => "Bộ thẻ không tồn tại"]);
            return;
        }

        // Check if word already exists in deck
        $stmt = $conn->prepare("SELECT id FROM flashcards WHERE deck_id = ? AND front = ?");
        $stmt->bind_param("is", $deckId, $word);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            echo json_encode(["success" => false, "message" => "Từ này đã có trong bộ thẻ"]);
            return;
        }

        // Add word to deck
        $stmt = $conn->prepare("INSERT INTO flashcards (deck_id, front, back, example) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $deckId, $word, $definition, $example);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Đã thêm từ vào bộ thẻ"]);
        } else {
            echo json_encode(["success" => false, "message" => "Không thể thêm từ vào bộ thẻ"]);
        }

    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
    }
}

function transferDeck() {
    global $conn;
    $userId = $_SESSION['user_id'];
    $data = json_decode(file_get_contents('php://input'), true);

    $deckId = (int)($data['deck_id'] ?? 0);

    if ($deckId <= 0) {
        echo json_encode(["success" => false, "message" => "Deck ID không hợp lệ"]);
        return;
    }

    try {
        $stmt = $conn->prepare("UPDATE decks SET user_id = ? WHERE id = ?");
        $stmt->bind_param("ii", $userId, $deckId);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            echo json_encode(["success" => true, "message" => "Đã chuyển bộ thẻ thành công"]);
        } else {
            echo json_encode(["success" => false, "message" => "Không thể chuyển bộ thẻ"]);
        }
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
    }
}

?>


