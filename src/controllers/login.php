<?php
session_start();
require_once __DIR__ . '/../services/database.php';

// Đảm bảo không có output thừa
ob_clean();
header('Content-Type: application/json; charset=utf-8');


header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$usernameOrEmail = trim($data['username']);
$password = trim($data['password']);

$stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if (password_verify($password, $row['password'])) {
        $_SESSION['username'] = $row['username'];
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