<?php
session_start();
require_once 'services/database.php';

$isLoggedIn = isset($_SESSION['user_id']);
if (!$isLoggedIn) {
    header('Location: index.php');
    exit;
}

$username = $_SESSION['username'] ?? '';
$email = $_SESSION['email'] ?? '';

// Get user profile data
$stmt = $conn->prepare("SELECT username, email, full_name, major FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ cá nhân - SmartDictionary</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }

        .header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            position: sticky;
            top: 0;
            z-index: 100;
            margin-bottom: 2rem;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-menu a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: opacity 0.3s ease;
        }

        .nav-menu a:hover {
            opacity: 0.8;
        }

        .profile-container {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .profile-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #eee;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 3rem;
            color: white;
            font-weight: 600;
        }

        .profile-title {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .profile-subtitle {
            color: #666;
            font-size: 1.1rem;
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #333;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #555;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            font-size: 1rem;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5a6fd8;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
        }

        .snackbar {
            visibility: hidden;
            min-width: 250px;
            margin-left: -125px;
            background-color: #333;
            color: #fff;
            text-align: center;
            border-radius: 8px;
            padding: 16px;
            position: fixed;
            z-index: 1000;
            left: 50%;
            bottom: 30px;
            font-size: 17px;
        }

        .snackbar.show {
            visibility: visible;
            animation: fadein 0.5s, fadeout 0.5s 2.5s;
        }

        .snackbar.success {
            background-color: #4CAF50;
        }

        .snackbar.error {
            background-color: #f44336;
        }

        @keyframes fadein {
            from {bottom: 0; opacity: 0;}
            to {bottom: 30px; opacity: 1;}
        }

        @keyframes fadeout {
            from {bottom: 30px; opacity: 1;}
            to {bottom: 0; opacity: 0;}
        }

        .divider {
            height: 1px;
            background: #eee;
            margin: 2rem 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <a href="index.php" class="logo">SmartDictionary</a>
            <nav>
                <ul class="nav-menu">
                    <li><a href="index.php">Trang chủ</a></li>
                    <li><a href="topics.php">Chủ đề</a></li>
                    <li><a href="flashcards.php">Flashcards</a></li>
                    <li><a href="listening.php">Nghe</a></li>
                    <li><a href="stats.php">Thống kê</a></li>
                    <li><a href="controllers/logout.php">Đăng xuất</a></li>
                </ul>
            </nav>
        </div>
    </div>

    <div class="container">
        <div class="profile-container">
            <div class="profile-header">
                <div class="profile-avatar">
                    <?= strtoupper(substr($user['full_name'] ?: $user['username'], 0, 1)) ?>
                </div>
                <h1 class="profile-title"><?= htmlspecialchars($user['full_name'] ?: $user['username']) ?></h1>
                <p class="profile-subtitle"><?= htmlspecialchars($user['email']) ?></p>
            </div>

            <!-- Personal Information -->
            <div class="form-section">
                <h2 class="section-title">
                    <i class="fas fa-user"></i>
                    Thông tin cá nhân
                </h2>
                <form id="profile-form">
                    <div class="form-group">
                        <label class="form-label" for="full_name">Họ và tên</label>
                        <input type="text" id="full_name" name="full_name" class="form-input" 
                               value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" 
                               placeholder="Nhập họ và tên của bạn">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-input" 
                               value="<?= htmlspecialchars($user['email']) ?>" 
                               placeholder="Nhập địa chỉ email">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="major">Chuyên ngành</label>
                        <input type="text" id="major" name="major" class="form-input" 
                               value="<?= htmlspecialchars($user['major'] ?? '') ?>" 
                               placeholder="Nhập chuyên ngành học tập">
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Lưu thay đổi
                        </button>
                    </div>
                </form>
            </div>

            <div class="divider"></div>

            <!-- Change Password -->
            <div class="form-section">
                <h2 class="section-title">
                    <i class="fas fa-lock"></i>
                    Đổi mật khẩu
                </h2>
                <form id="password-form">
                    <div class="form-group">
                        <label class="form-label" for="current_password">Mật khẩu hiện tại</label>
                        <input type="password" id="current_password" name="current_password" class="form-input" 
                               placeholder="Nhập mật khẩu hiện tại">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="new_password">Mật khẩu mới</label>
                        <input type="password" id="new_password" name="new_password" class="form-input" 
                               placeholder="Nhập mật khẩu mới">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="confirm_password">Xác nhận mật khẩu mới</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-input" 
                               placeholder="Nhập lại mật khẩu mới">
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-secondary">
                            <i class="fas fa-key"></i> Đổi mật khẩu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Snackbar -->
    <div id="snackbar" class="snackbar"></div>

    <script>
        // Snackbar function
        function showSnackbar(message, type = 'info') {
            const snackbar = document.getElementById("snackbar");
            snackbar.textContent = message;
            snackbar.className = `snackbar show ${type}`;
            setTimeout(() => {
                snackbar.className = snackbar.className.replace("show", "");
            }, 3000);
        }

        // Profile form submission
        document.getElementById('profile-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);

            fetch('controllers/profile.php?action=update_profile', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(data => {
                showSnackbar(data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    // Update the header display
                    document.querySelector('.profile-title').textContent = document.getElementById('full_name').value || '<?= $user['username'] ?>';
                    document.querySelector('.profile-subtitle').textContent = document.getElementById('email').value;
                }
            })
            .catch(err => {
                console.error('Error:', err);
                showSnackbar('Có lỗi xảy ra khi cập nhật thông tin', 'error');
            });
        });

        // Password form submission
        document.getElementById('password-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (newPassword !== confirmPassword) {
                showSnackbar('Mật khẩu xác nhận không khớp', 'error');
                return;
            }

            if (newPassword.length < 6) {
                showSnackbar('Mật khẩu phải có ít nhất 6 ký tự', 'error');
                return;
            }

            const formData = new FormData(this);
            const data = Object.fromEntries(formData);

            fetch('controllers/profile.php?action=change_password', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(data => {
                showSnackbar(data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    // Clear the form
                    this.reset();
                }
            })
            .catch(err => {
                console.error('Error:', err);
                showSnackbar('Có lỗi xảy ra khi đổi mật khẩu', 'error');
            });
        });
    </script>
</body>
</html>
