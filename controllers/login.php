<?php
session_start();
require_once __DIR__ . '/../src/services/database.php';

ob_clean();
header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents("php://input"), true);

$usernameOrEmail = trim($data['username'] ?? '');
$password = trim($data['password'] ?? '');

if ($usernameOrEmail === '' || $password === '') {
    echo json_encode(["success" => false, "message" => "Thiếu thông tin đăng nhập"]);
    exit;
}

$stmt = $conn->prepare("SELECT id, username, email, password FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if (password_verify($password, $row['password'])) {
        $_SESSION['user_id'] = (int)$row['id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['email'] = $row['email'];
        echo json_encode(["success" => true, "message" => "Đăng nhập thành công"]);
        exit;
    } else {
        echo json_encode(["success" => false, "message" => "Sai mật khẩu hoặc tên đăng nhập"]);
        exit;
    }
} else {
    echo json_encode(["success" => false, "message" => "Tài khoản không tồn tại"]);
    exit;
}

$stmt->close();
$conn->close();
?>