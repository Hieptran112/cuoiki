<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../services/database.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

$action = $_GET['action'] ?? '';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Vui lòng đăng nhập"]);
    exit;
}

switch ($action) {
    case 'get':
        getProfile();
        break;
    case 'update':
    case 'update_profile':
        updateProfile();
        break;
    case 'change_password':
        changePassword();
        break;
    default:
        echo json_encode(["success" => false, "message" => "Action không hợp lệ"]);
}

function getProfile() {
    global $conn;
    $stmt = $conn->prepare("SELECT id, username, email, full_name, major, created_at, updated_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        echo json_encode(["success" => true, "data" => $row]);
    } else {
        echo json_encode(["success" => false, "message" => "Không tìm thấy hồ sơ"]);
    }
}

function updateProfile() {
    global $conn;
    $data = json_decode(file_get_contents('php://input'), true);
    $email = isset($data['email']) ? trim($data['email']) : '';
    $full_name = isset($data['full_name']) ? trim($data['full_name']) : null;
    $major = isset($data['major']) ? trim($data['major']) : null;

    if ($email === '') { echo json_encode(["success" => false, "message" => "Email không được trống"]); return; }

    // Check duplicate email for other users
    $chk = $conn->prepare("SELECT id FROM users WHERE email = ? AND id <> ?");
    $chk->bind_param("si", $email, $_SESSION['user_id']);
    $chk->execute();
    $chk->store_result();
    if ($chk->num_rows > 0) { echo json_encode(["success" => false, "message" => "Email đã được sử dụng"]); return; }
    $chk->close();

    $stmt = $conn->prepare("UPDATE users SET email = ?, full_name = ?, major = ? WHERE id = ?");
    $stmt->bind_param("sssi", $email, $full_name, $major, $_SESSION['user_id']);
    $ok = $stmt->execute();
    if ($ok) {
        $_SESSION['email'] = $email;
    }
    echo json_encode(["success" => $ok, "message" => $ok ? "Cập nhật hồ sơ thành công" : "Cập nhật thất bại"]);
}

function changePassword() {
    global $conn;
    $data = json_decode(file_get_contents('php://input'), true);

    $currentPassword = $data['current_password'] ?? '';
    $newPassword = $data['new_password'] ?? '';
    $confirmPassword = $data['confirm_password'] ?? '';

    // Validate input
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        echo json_encode(["success" => false, "message" => "Vui lòng điền đầy đủ thông tin"]);
        return;
    }

    if ($newPassword !== $confirmPassword) {
        echo json_encode(["success" => false, "message" => "Mật khẩu xác nhận không khớp"]);
        return;
    }

    if (strlen($newPassword) < 6) {
        echo json_encode(["success" => false, "message" => "Mật khẩu phải có ít nhất 6 ký tự"]);
        return;
    }

    try {
        // Get current password hash
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if (!$result) {
            echo json_encode(["success" => false, "message" => "Không tìm thấy tài khoản"]);
            return;
        }

        // Verify current password
        if (!password_verify($currentPassword, $result['password'])) {
            echo json_encode(["success" => false, "message" => "Mật khẩu hiện tại không đúng"]);
            return;
        }

        // Hash new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update password
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashedPassword, $_SESSION['user_id']);

        if ($stmt->execute()) {
            echo json_encode([
                "success" => true,
                "message" => "Đổi mật khẩu thành công!"
            ]);
        } else {
            echo json_encode(["success" => false, "message" => "Không thể đổi mật khẩu"]);
        }
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Lỗi server: " . $e->getMessage()]);
    }
}

?>


