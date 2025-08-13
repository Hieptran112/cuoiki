<?php
session_start();

header('Content-Type: application/json');

// Bật hiển thị lỗi cho dev (bạn nên tắt khi deploy)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../services/database.php';

// Lấy dữ liệu JSON đầu vào
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['username'], $data['email'], $data['password'])) {
    echo json_encode(["success" => false, "message" => "Thiếu thông tin bắt buộc."]);
    exit;
}

$username = trim($data['username']);
$email = trim($data['email']);
$password_raw = (string)$data['password'];
$full_name = isset($data['full_name']) ? trim($data['full_name']) : null;
$major = isset($data['major']) ? trim($data['major']) : null;

// Kiểm tra dữ liệu rỗng
if ($username === '' || $email === '' || $password_raw === '') {
    echo json_encode(["success" => false, "message" => "Vui lòng nhập đầy đủ thông tin."]);
    exit;
}

$password = password_hash($password_raw, PASSWORD_DEFAULT);

try {
    // Kiểm tra email/username tồn tại
    $check = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    if (!$check) throw new Exception("Prepare statement failed: " . $conn->error);

    $check->bind_param("ss", $email, $username);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "Email hoặc tên đăng nhập đã tồn tại"]);
        $check->close();
        exit;
    }
    $check->close();

    // Thêm user mới
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, major) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) throw new Exception("Prepare statement failed: " . $conn->error);

    $stmt->bind_param("sssss", $username, $email, $password, $full_name, $major);

    $stmt->execute();

    // Auto-login after successful registration
    $newUserId = $conn->insert_id;
    $_SESSION['user_id'] = (int)$newUserId;
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;

    // Auto-provision preset decks for this user
    try {
        $conn->query("INSERT IGNORE INTO decks (user_id, name, description, visibility)
                       SELECT {$newUserId} as user_id, pd.name, pd.description, 'private' FROM preset_decks pd");
    } catch (Exception $e) {
        // Ignore provisioning errors to not block registration
    }

    echo json_encode(["success" => true, "message" => "Đăng ký thành công!"]);

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Lỗi server: " . $e->getMessage()]);
    exit;
}
