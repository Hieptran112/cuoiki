<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../services/database.php';

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
    $stmt = $conn->prepare("SELECT id, name, description, visibility, created_at FROM decks WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $res = $stmt->get_result();
    $decks = $res->fetch_all(MYSQLI_ASSOC);
    echo json_encode(["success" => true, "data" => $decks]);
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

    $stmt = $conn->prepare("UPDATE decks SET name = ?, description = ?, visibility = ? WHERE id = ?");
    $stmt->bind_param("sssi", $name, $description, $visibility, $deckId);
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
    $data = json_decode(file_get_contents('php://input'), true);
    $text = trim($data['text'] ?? '');
    $topK = (int)($data['top_k'] ?? 20);
    $minLen = (int)($data['min_length'] ?? 3);
    $domain = trim($data['domain'] ?? '');
    if ($text === '') { echo json_encode(["success"=>false, "message"=>"Văn bản trống"]); return; }

    // Ensure basic dictionary entries exist
    ensureBasicDictionary();

    // Tokenize english-like words
    $matches = [];
    preg_match_all('/[A-Za-z][A-Za-z\-]{'.max(1,$minLen-1).',}/', $text, $matches);
    $tokens = array_map(function($w){ return strtolower($w); }, $matches[0] ?? []);
    if (empty($tokens)) { echo json_encode(["success"=>true, "data"=>[]]); return; }

    $stop = [ 'the','and','for','are','but','not','you','with','this','that','from','have','was','were','your','will','would','could','there','their','about','which','when','what','where','who','how','why','into','then','than','its','also','because','been','can','all','more','most','other','some','such','only','each','many','much','very','use','used','using','among','between','within','over','under','after','before','again','new','one','two','three' ];
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

    // Add basic words for testing
    $basicWords = [
        ['word' => 'cat', 'vietnamese' => 'mèo', 'english_definition' => 'a small domesticated carnivorous mammal'],
        ['word' => 'dog', 'vietnamese' => 'chó', 'english_definition' => 'a domesticated carnivorous mammal'],
        ['word' => 'water', 'vietnamese' => 'nước', 'english_definition' => 'a colorless, transparent, odorless liquid'],
        ['word' => 'apple', 'vietnamese' => 'táo', 'english_definition' => 'the round fruit of a tree'],
        ['word' => 'book', 'vietnamese' => 'sách', 'english_definition' => 'a written or printed work'],
        ['word' => 'house', 'vietnamese' => 'nhà', 'english_definition' => 'a building for human habitation'],
        ['word' => 'car', 'vietnamese' => 'xe hơi', 'english_definition' => 'a road vehicle with an engine'],
        ['word' => 'tree', 'vietnamese' => 'cây', 'english_definition' => 'a woody perennial plant'],
        ['word' => 'sun', 'vietnamese' => 'mặt trời', 'english_definition' => 'the star around which the earth orbits'],
        ['word' => 'moon', 'vietnamese' => 'mặt trăng', 'english_definition' => 'the natural satellite of the earth'],
        ['word' => 'good', 'vietnamese' => 'tốt', 'english_definition' => 'to be desired or approved of'],
        ['word' => 'bad', 'vietnamese' => 'xấu', 'english_definition' => 'of poor quality or low standard'],
        ['word' => 'big', 'vietnamese' => 'lớn', 'english_definition' => 'of considerable size or extent'],
        ['word' => 'small', 'vietnamese' => 'nhỏ', 'english_definition' => 'of a size that is less than normal'],
        ['word' => 'happy', 'vietnamese' => 'vui', 'english_definition' => 'feeling or showing pleasure'],
        ['word' => 'sad', 'vietnamese' => 'buồn', 'english_definition' => 'feeling or showing sorrow'],
        ['word' => 'love', 'vietnamese' => 'yêu', 'english_definition' => 'an intense feeling of deep affection'],
        ['word' => 'time', 'vietnamese' => 'thời gian', 'english_definition' => 'the indefinite continued progress of existence'],
        ['word' => 'life', 'vietnamese' => 'cuộc sống', 'english_definition' => 'the condition that distinguishes animals and plants'],
        ['word' => 'work', 'vietnamese' => 'công việc', 'english_definition' => 'activity involving mental or physical effort']
    ];

    $stmt = $conn->prepare("INSERT IGNORE INTO dictionary (word, vietnamese, english_definition, part_of_speech, difficulty) VALUES (?, ?, ?, 'noun', 'beginner')");
    foreach ($basicWords as $word) {
        $stmt->bind_param("sss", $word['word'], $word['vietnamese'], $word['english_definition']);
        $stmt->execute();
    }
}

?>


