<?php
session_start();
require_once 'services/database.php';

$isLoggedIn = isset($_SESSION['user_id']);
$username = $_SESSION['username'] ?? '';

// Require login for listening exercises
if (!$isLoggedIn) {
    header('Location: index.php?message=' . urlencode('Bạn cần đăng nhập để làm bài tập nghe.'));
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bài tập Nghe - SmartDictionary</title>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
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

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: white;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .main-content {
            padding: 2rem 0;
        }

        .page-header {
            text-align: center;
            color: white;
            margin-bottom: 3rem;
        }

        .page-header h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .page-header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .back-btn {
            color: white;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 2rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: opacity 0.3s ease;
        }

        .back-btn:hover {
            opacity: 0.8;
        }

        .listening-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 
                0 20px 60px rgba(0, 0, 0, 0.1),
                0 0 0 1px rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: #333;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .audio-player {
            background: #f8f9fa;
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            margin-bottom: 2rem;
            border: 2px solid #e1e5e9;
        }

        .audio-controls {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .play-btn {
            background: #667eea;
            color: white;
            border: none;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            font-size: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .play-btn:hover {
            background: #5a6fd8;
            transform: scale(1.1);
        }

        .audio-info {
            color: #666;
            font-size: 0.9rem;
        }

        .question-container {
            margin-bottom: 2rem;
        }

        .question {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: #333;
        }

        .options {
            display: grid;
            gap: 1rem;
        }

        .option {
            background: white;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .option:hover {
            border-color: #667eea;
            background: #f0f2ff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        }

        .option.selected {
            border-color: #667eea;
            background: #e8ecff;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
        }

        .option-letter {
            background: #667eea;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            flex-shrink: 0;
        }

        .option.selected .option-letter {
            background: #5a6fd8;
            transform: scale(1.1);
        }

        .submit-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 2rem;
        }

        .submit-btn:hover {
            background: #218838;
            transform: translateY(-2px);
        }

        .submit-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .result {
            margin-top: 2rem;
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
            font-weight: 600;
        }

        .result.correct {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .result.incorrect {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">
                    <i class="fas fa-book-open"></i> SmartDictionary
                </a>
                <nav>
                    <ul class="nav-menu">
                        <li><a href="index.php">Trang chủ</a></li>
                        <li><a href="topics.php">Chủ đề</a></li>
                        <li><a href="flashcards.php">Flashcards</a></li>
                        <li><a href="listening.php">Nghe</a></li>
                        <li><a href="stats.php">Thống kê</a></li>
                    </ul>
                </nav>
                <div class="user-info">
                    <span>Xin chào, <?php echo htmlspecialchars($username); ?></span>
                    <a href="profile.php" class="btn btn-primary">Hồ sơ</a>
                    <a href="controllers/logout.php" class="btn btn-secondary">Đăng xuất</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <a href="index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>Về trang chủ
            </a>

            <!-- Page Header -->
            <div class="page-header">
                <h1><i class="fas fa-headphones"></i> Bài tập Nghe</h1>
                <p>Luyện tập kỹ năng nghe tiếng Anh với các bài tập đa dạng</p>
            </div>

            <!-- Listening Exercise -->
            <div class="listening-section">
                <div class="section-title">
                    <i class="fas fa-volume-up"></i>
                    Bài tập nghe hiện tại
                </div>

                <div id="exercise-container">
                    <!-- Exercise content will be loaded here -->
                </div>
            </div>
        </div>
    </main>

    <!-- Snackbar -->
    <div id="snackbar" class="snackbar"></div>

    <script>
        let currentExercise = null;
        let selectedAnswer = null;

        // Snackbar function
        function showSnackbar(message, type = 'info') {
            const snackbar = document.getElementById("snackbar");
            snackbar.textContent = message;
            snackbar.className = `snackbar show ${type}`;
            setTimeout(() => {
                snackbar.className = snackbar.className.replace("show", "");
            }, 3000);
        }

        // Load listening exercise
        function loadListeningExercise() {
            fetch('controllers/listening.php?action=get_exercise')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        currentExercise = data.data;
                        displayExercise(currentExercise);
                    } else {
                        showSnackbar(data.message, 'error');
                    }
                })
                .catch(err => {
                    console.error('Error loading exercise:', err);
                    showSnackbar('Có lỗi xảy ra khi tải bài tập', 'error');
                });
        }

        // Display exercise
        function displayExercise(exercise) {
            const container = document.getElementById('exercise-container');
            container.innerHTML = `
                <div class="audio-player">
                    <div class="audio-controls">
                        <button class="play-btn" onclick="playAudio()">
                            <i class="fas fa-play"></i>
                        </button>
                    </div>
                    <div class="audio-info">
                        <p>Nhấn để nghe đoạn audio</p>
                        <p><small>Bạn có thể nghe lại nhiều lần</small></p>
                    </div>
                    <audio id="audio-player" preload="auto">
                        <source src="${exercise.audio_url}" type="audio/mpeg">
                        Trình duyệt của bạn không hỗ trợ audio.
                    </audio>
                </div>

                <div class="question-container">
                    <div class="question">${exercise.question}</div>
                    <div class="options">
                        <div class="option" onclick="selectOption('A')">
                            <div class="option-letter">A</div>
                            <div>${exercise.option_a}</div>
                        </div>
                        <div class="option" onclick="selectOption('B')">
                            <div class="option-letter">B</div>
                            <div>${exercise.option_b}</div>
                        </div>
                        <div class="option" onclick="selectOption('C')">
                            <div class="option-letter">C</div>
                            <div>${exercise.option_c}</div>
                        </div>
                        <div class="option" onclick="selectOption('D')">
                            <div class="option-letter">D</div>
                            <div>${exercise.option_d}</div>
                        </div>
                    </div>
                    <button class="submit-btn" onclick="submitAnswer()" disabled>
                        Nộp bài
                    </button>
                    <div id="result-container"></div>
                </div>
            `;
        }

        // Play audio
        function playAudio() {
            const playBtn = document.querySelector('.play-btn');

            if (!currentExercise) return;

            // Check if audio_url starts with 'tts:'
            if (currentExercise.audio_url.startsWith('tts:')) {
                const text = currentExercise.audio_url.substring(4);

                // Use Web Speech API
                if ('speechSynthesis' in window) {
                    // Stop any ongoing speech
                    speechSynthesis.cancel();

                    const utterance = new SpeechSynthesisUtterance(text);
                    utterance.lang = 'en-US';
                    utterance.rate = 0.8;
                    utterance.pitch = 1;

                    playBtn.innerHTML = '<i class="fas fa-pause"></i>';

                    utterance.onend = function() {
                        playBtn.innerHTML = '<i class="fas fa-play"></i>';
                    };

                    speechSynthesis.speak(utterance);
                } else {
                    alert('Trình duyệt của bạn không hỗ trợ text-to-speech');
                }
            } else {
                // Use regular audio player
                const audio = document.getElementById('audio-player');

                if (audio.paused) {
                    audio.play();
                    playBtn.innerHTML = '<i class="fas fa-pause"></i>';
                } else {
                    audio.pause();
                    playBtn.innerHTML = '<i class="fas fa-play"></i>';
                }

                audio.onended = function() {
                    playBtn.innerHTML = '<i class="fas fa-play"></i>';
                };
            }
        }

        // Select option
        function selectOption(letter) {
            // Remove previous selection
            document.querySelectorAll('.option').forEach(opt => {
                opt.classList.remove('selected');
            });

            // Add selection to clicked option
            event.target.closest('.option').classList.add('selected');
            selectedAnswer = letter;

            // Enable submit button
            document.querySelector('.submit-btn').disabled = false;
        }

        // Submit answer
        function submitAnswer() {
            if (!selectedAnswer) {
                showSnackbar('Vui lòng chọn một đáp án', 'error');
                return;
            }

            fetch('controllers/listening.php?action=submit_answer', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({
                    exercise_id: currentExercise.id,
                    answer: selectedAnswer
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    displayResult(data.data);
                } else {
                    showSnackbar(data.message, 'error');
                }
            })
            .catch(err => {
                console.error('Error submitting answer:', err);
                showSnackbar('Có lỗi xảy ra khi nộp bài', 'error');
            });
        }

        // Display result
        function displayResult(result) {
            const container = document.getElementById('result-container');
            const isCorrect = result.is_correct;
            
            container.innerHTML = `
                <div class="result ${isCorrect ? 'correct' : 'incorrect'}">
                    <i class="fas ${isCorrect ? 'fa-check-circle' : 'fa-times-circle'}"></i>
                    ${isCorrect ? 'Chính xác!' : 'Sai rồi!'}
                    <br>
                    <small>Đáp án đúng: ${result.correct_answer}</small>
                    ${result.explanation ? `<br><small>${result.explanation}</small>` : ''}
                </div>
            `;

            // Disable submit button
            document.querySelector('.submit-btn').disabled = true;

            // Load next exercise after 3 seconds
            setTimeout(() => {
                loadListeningExercise();
            }, 3000);
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadListeningExercise();
        });
    </script>
</body>
</html>
