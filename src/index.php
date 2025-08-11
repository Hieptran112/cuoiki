<?php
    session_start();
    $isLoggedIn = isset($_SESSION['username']); 
    $username = $isLoggedIn ? $_SESSION['username'] : null;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>FlashLearn - Học từ vựng chuyên ngành</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-image: url('background.png'); background-size: cover; background-position: center; background-attachment: fixed; }
    header { background-color: rgba(0, 85, 128, 0.9); padding: 15px; color: white; text-align: center; }
    nav { background-color: rgba(0, 61, 92, 0.9); overflow: hidden; }
    nav a { float: left; display: block; color: white; text-align: center; padding: 14px 20px; text-decoration: none; }
    nav a:hover { background-color: #0077a6; }
    section { padding: 40px; max-width: 900px; margin: 20px auto; background-color: rgba(255, 255, 255, 0.95); border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.2); }
    h2 { color: #005580; }
    .start-button { display: inline-block; margin-top: 20px; padding: 12px 24px; background-color: #0077a6; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; }
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); justify-content: center; align-items: center; }
    .modal-content { background-color: white; padding: 20px; border-radius: 8px; width: 300px; position: relative; }
    .close-btn { position: absolute; top: 8px; right: 12px; font-size: 20px; cursor: pointer; color: #888; }
    input[type="text"], input[type="password"], input[type="email"] { padding: 10px; width: 100%; margin: 10px 0; border-radius: 5px; border: 1px solid #ccc; box-sizing: border-box; }
    input[type="submit"] { padding: 10px 20px; background-color: #005580; color: white; border: none; border-radius: 5px; cursor: pointer; }
    input[type="submit"]:hover { background-color: #0077a6; }

    /* Snackbar */
    #snackbar {
      visibility: hidden;
      min-width: 250px;
      background-color: #333;
      color: #fff;
      text-align: center;
      border-radius: 8px;
      padding: 12px;
      position: fixed;
      z-index: 2000;
      left: 50%;
      bottom: 30px;
      transform: translateX(-50%);
      font-size: 16px;
    }
    #snackbar.show {
      visibility: visible;
      animation: fadein 0.5s, fadeout 0.5s 2.5s;
    }
    @keyframes fadein { from { bottom: 0; opacity: 0; } to { bottom: 30px; opacity: 1; } }
    @keyframes fadeout { from { bottom: 30px; opacity: 1; } to { bottom: 0; opacity: 0; } }
  </style>
</head>
<body>
  <header>
    <h1>FlashLearn - Flashcard Từ Vựng Chuyên Ngành</h1>
  </header>

  <nav>
    <a href="#home">Trang chủ</a>
    <?php if ($isLoggedIn): ?>
      <a href="#" style="cursor:default; pointer-events:none;">Xin chào, <?php echo htmlspecialchars($username); ?></a>
      <a href="controllers/logout.php">Đăng xuất</a>
    <?php else: ?>
        <a href="#" onclick="openModal('login')">Đăng nhập</a>
        <a href="#" onclick="openModal('register')">Đăng ký</a>
    <?php endif; ?>
    <a href="#about">Giới thiệu</a>
    <a href="#why">Tại sao chọn chúng tôi?</a>
    <a href="#learn">Bài học</a>
  </nav>

  <section id="home">
    <h2>Chào mừng đến với FlashLearn</h2>
    <p>Giúp bạn ghi nhớ từ vựng hiệu quả qua hệ thống flashcard tương tác và thông minh.</p>
    <a href="#learn" class="start-button">🚀 Bắt đầu bài học</a>
  </section>

  <section id="about">
    <h2>Giới thiệu</h2>
    <p>
      Trang web của chúng tôi được thiết kế dành riêng cho người học từ vựng chuyên ngành...
    </p>
  </section>

  <section id="why">
    <h2>Tại sao nên sử dụng FlashLearn?</h2>
    <ul>
      <li>🔁 Ôn tập thông minh theo mức độ ghi nhớ</li>
      <li>📚 Dữ liệu từ vựng chuyên ngành đa dạng</li>
      <li>🧠 Cải thiện trí nhớ dài hạn</li>
      <li>📱 Giao diện thân thiện</li>
      <li>💡 Tùy chỉnh lộ trình học</li>
    </ul>
  </section>

  <section id="learn">
    <h2>Bài học đầu tiên</h2>
    <p>(Phần flashcard sẽ được phát triển sau)</p>
  </section>

  <!-- Modal Đăng nhập -->
  <div id="login-modal" class="modal">
    <div class="modal-content">
      <span class="close-btn" onclick="closeModal('login')">&times;</span>
      <h3>Đăng nhập</h3>
      <form onsubmit="return handleLogin(event)">
        <input type="text" id="login-username" placeholder="Tên người dùng hoặc Email" required>
        <input type="password" id="login-password" placeholder="Mật khẩu" required>
        <input type="submit" value="Đăng nhập">
      </form>
    </div>
  </div>

  <!-- Modal Đăng ký -->
  <div id="register-modal" class="modal">
    <div class="modal-content">
      <span class="close-btn" onclick="closeModal('register')">&times;</span>
      <h3>Đăng ký</h3>
      <form onsubmit="return handleRegister(event)">
        <input type="text" id="register-username" placeholder="Tên người dùng" required>
        <input type="email" id="register-email" placeholder="Email" required>
        <input type="password" id="register-password" placeholder="Mật khẩu" required>
        <input type="submit" value="Đăng ký">
      </form>
    </div>
  </div>

  <!-- Snackbar -->
  <div id="snackbar"></div>

  <script>
    function openModal(type) {
      document.getElementById(type + "-modal").style.display = "flex";
    }
    function closeModal(type) {
      document.getElementById(type + "-modal").style.display = "none";
    }
    function showSnackbar(message) {
      const sb = document.getElementById("snackbar");
      sb.textContent = message || "Thao tác hoàn tất!";
      sb.className = "show";
      setTimeout(() => { sb.className = sb.className.replace("show", ""); }, 3000);
    }

    function handleLogin(event) {
      event.preventDefault();
      const username = document.getElementById("login-username").value;
      const password = document.getElementById("login-password").value;

      fetch("controllers/login.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ username, password })
      })
      .then(res => res.json())
      .then(data => {
        showSnackbar(data.message || (data.success ? "Đăng nhập thành công!" : "Đăng nhập thất bại!"));
        if (data.success) setTimeout(() => location.reload(), 1500);
      }).catch(err => {
            console.error("Lỗi parse JSON hoặc fetch:", err);
            alert("Có lỗi xảy ra, vui lòng thử lại");
        });
    }

    function handleRegister(event) {
      event.preventDefault();
      const username = document.getElementById("register-username").value;
      const email = document.getElementById("register-email").value;
      const password = document.getElementById("register-password").value;

      fetch("controllers/register.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ username, email, password })
      })
      .then(res => res.json())
      .then(data => {
        showSnackbar(data.message || (data.success ? "Đăng ký thành công!" : "Đăng ký thất bại!"));
        if (data.success) setTimeout(() => location.reload(), 1500);});
    }
  </script>
</body>
</html>
