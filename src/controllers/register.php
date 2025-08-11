<?php
session_start();

header('Content-Type: application/json');

// Bật hiển thị lỗi cho dev (bạn nên tắt khi deploy)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../services/database.php';

// Đảm bảo không có echo, print, hoặc output nào khác trong database.php, đặc biệt là dòng "Kết nối thành công!"

// Lấy dữ liệu JSON đầu vào
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['username'], $data['email'], $data['password'])) {
    echo json_encode(["success" => false, "message" => "Dữ liệu đầu vào không hợp lệ hoặc thiếu thông tin."]);
    exit;
}

$username = trim($data['username']);
$email = trim($data['email']);
$password_raw = $data['password'];

// Kiểm tra dữ liệu rỗng
if ($username === '' || $email === '' || $password_raw === '') {
    echo json_encode(["success" => false, "message" => "Vui lòng nhập đầy đủ thông tin."]);
    exit;
}

$password = password_hash($password_raw, PASSWORD_DEFAULT);

try {
    // Kiểm tra email tồn tại
    $check = $conn->prepare("SELECT id FROM users WHERE email = $email");
    if (!$check) throw new Exception("Prepare statement failed: " . $conn->error);

    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "Email đã tồn tại"]);
        $check->close();
        exit;
    }
    $check->close();

    // Thêm user mới
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    if (!$stmt) throw new Exception("Prepare statement failed: " . $conn->error);

    $stmt->bind_param("sss", $username, $email, $password);

    $stmt->execute();

    echo json_encode(["success" => true, "message" => "Đăng ký thành công!"]);

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Lỗi server: " . $e->getMessage()]);
    exit;
}
