<?php
session_start();
require_once 'services/database.php';

$isLoggedIn = isset($_SESSION['user_id']);
$username = $_SESSION['username'] ?? '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bài học theo chủ đề - SmartDictionary</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Background Styling */
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background:
                radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(120, 219, 255, 0.2) 0%, transparent 50%);
            z-index: -1;
        }

        body::after {
            content: '';
            position: fixed;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.05)"/><circle cx="10" cy="60" r="0.5" fill="rgba(255,255,255,0.05)"/><circle cx="90" cy="40" r="0.5" fill="rgba(255,255,255,0.05)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            animation: float 20s ease-in-out infinite;
            z-index: -1;
            opacity: 0.4;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-20px) rotate(1deg); }
            66% { transform: translateY(10px) rotate(-1deg); }
        }

        .topics-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            position: relative;
            z-index: 1;
        }

        .page-header {
            text-align: center;
            margin-bottom: 3rem;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 3rem 2rem;
            box-shadow:
                0 8px 32px rgba(0, 0, 0, 0.1),
                0 0 0 1px rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .page-title {
            font-size: 2.8rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center; /* Căn giữa tiêu đề */
        }

        .page-subtitle {
            font-size: 1.3rem;
            color: #5a6c7d;
            font-weight: 500;
            line-height: 1.6;
            max-width: 600px;
            margin: 0 auto;
        }

        .topics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .topic-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow:
                0 8px 32px rgba(0, 0, 0, 0.1),
                0 0 0 1px rgba(255, 255, 255, 0.2);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            cursor: pointer;
            border-left: 5px solid;
            border: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
            overflow: hidden;
        }

        .topic-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }

        .topic-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow:
                0 20px 40px rgba(0, 0, 0, 0.15),
                0 0 0 1px rgba(255, 255, 255, 0.3);
        }

        .topic-card:hover::before {
            opacity: 1;
        }

        .topic-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .topic-icon {
            font-size: 2.5rem;
            margin-right: 1rem;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: white;
        }

        .topic-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2c3e50;
            margin: 0;
        }

        .topic-description {
            color: #7f8c8d;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .topic-stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .lesson-count {
            background: #ecf0f1;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            color: #2c3e50;
        }

        .start-btn {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .start-btn:hover {
            background: linear-gradient(135deg, #2980b9, #1f5f8b);
            transform: scale(1.05);
        }

        .lessons-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }

        .modal-content {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            margin: 5% auto;
            padding: 0;
            border-radius: 20px;
            width: 90%;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow:
                0 20px 60px rgba(0, 0, 0, 0.2),
                0 0 0 1px rgba(255, 255, 255, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .modal-header {
            padding: 2rem;
            border-bottom: 1px solid #ecf0f1;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 1.8rem;
            color: #2c3e50;
            margin: 0;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #7f8c8d;
        }

        .lessons-list {
            padding: 1rem 2rem 2rem;
        }

        .lesson-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            margin-bottom: 1rem;
            background: #f8f9fa;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .lesson-item:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }

        .lesson-number {
            background: #3498db;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 1rem;
        }

        .lesson-info {
            flex: 1;
        }

        .lesson-title {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 0.25rem;
        }

        .lesson-description {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .lesson-progress {
            text-align: right;
        }

        .progress-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .progress-completed {
            background: #d4edda;
            color: #155724;
        }

        .progress-in-progress {
            background: #fff3cd;
            color: #856404;
        }

        .progress-not-started {
            background: #f8d7da;
            color: #721c24;
        }

        .difficulty-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 10px;
            font-size: 0.75rem;
            margin-left: 0.5rem;
        }

        .difficulty-beginner {
            background: #d4edda;
            color: #155724;
        }

        .difficulty-intermediate {
            background: #fff3cd;
            color: #856404;
        }

        .difficulty-advanced {
            background: #f8d7da;
            color: #721c24;
        }

        /* Header Styling */
        header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Back Button Styling - giống stats.php */
        .back-btn {
            position: fixed;
            top: 2rem;
            left: 1rem; /* Di chuyển sang trái hơn */
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .back-btn {
                position: static;
                margin-bottom: 2rem;
                display: inline-block;
            }

            .page-title {
                margin-left: 0; /* Bỏ margin trên mobile */
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Include Auth Modal System -->
    <script src="js/auth-modal.js"></script>
    <script>
        // Set login status for JavaScript
        const isUserLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
    </script>
    <a href="index.php" class="back-btn">
        <i class="fas fa-arrow-left"></i>Trang chủ
    </a>

    <main class="topics-container">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-graduation-cap"></i>
                Bài học theo chủ đề
            </h1>
        </div>

        <div id="topicsGrid" class="topics-grid">
            <!-- Topics will be loaded here -->
        </div>
    </main>

    <!-- Modal hiển thị danh sách bài học -->
    <div id="lessonsModal" class="lessons-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle" class="modal-title"></h2>
                <button class="close-btn" onclick="closeLessonsModal()">&times;</button>
            </div>
            <div id="lessonsList" class="lessons-list">
                <!-- Lessons will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        // Load topics when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadTopics();
        });

        function loadTopics() {
            fetch('controllers/topics.php?action=get_topics')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayTopics(data.data);
                    } else {
                        console.error('Error loading topics:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        function displayTopics(topics) {
            const grid = document.getElementById('topicsGrid');
            grid.innerHTML = '';

            topics.forEach(topic => {
                const topicCard = document.createElement('div');
                topicCard.className = 'topic-card';
                topicCard.style.borderLeftColor = topic.color;

                topicCard.innerHTML = `
                    <div class="topic-header">
                        <div class="topic-icon" style="background: ${topic.color}">
                            <i class="${topic.icon}"></i>
                        </div>
                        <h3 class="topic-title">${topic.name}</h3>
                    </div>
                    <p class="topic-description">${topic.description}</p>
                    <div class="topic-stats">
                        <span class="lesson-count">
                            <i class="fas fa-book"></i> ${topic.lesson_count} bài học
                        </span>
                        <button class="start-btn" onclick="openTopicLessons(${topic.id}, '${topic.name}')">
                            Bắt đầu học
                        </button>
                    </div>
                `;

                grid.appendChild(topicCard);
            });
        }

        function openTopicLessons(topicId, topicName) {
            document.getElementById('modalTitle').textContent = topicName;
            document.getElementById('lessonsModal').style.display = 'block';

            fetch(`controllers/topics.php?action=get_topic_lessons&topic_id=${topicId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayLessons(data.data.lessons);
                    } else {
                        console.error('Error loading lessons:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        function displayLessons(lessons) {
            const list = document.getElementById('lessonsList');
            list.innerHTML = '';

            lessons.forEach(lesson => {
                const lessonItem = document.createElement('div');
                lessonItem.className = 'lesson-item';

                let progressBadge = '';
                let progressClass = 'progress-not-started';
                let progressText = 'Chưa bắt đầu';

                if (lesson.progress) {
                    if (lesson.progress.is_completed) {
                        progressClass = 'progress-completed';
                        progressText = 'Hoàn thành';
                    } else if (lesson.progress.completion_percentage > 0) {
                        progressClass = 'progress-in-progress';
                        progressText = `${Math.round(lesson.progress.completion_percentage)}%`;
                    }
                }

                progressBadge = `<span class="progress-badge ${progressClass}">${progressText}</span>`;

                let difficultyClass = `difficulty-${lesson.difficulty}`;
                let difficultyText = lesson.difficulty === 'beginner' ? 'Cơ bản' :
                                   lesson.difficulty === 'intermediate' ? 'Trung bình' : 'Nâng cao';

                lessonItem.innerHTML = `
                    <div class="lesson-number">${lesson.lesson_number}</div>
                    <div class="lesson-info">
                        <div class="lesson-title">
                            ${lesson.title}
                            <span class="difficulty-badge ${difficultyClass}">${difficultyText}</span>
                        </div>
                        <div class="lesson-description">${lesson.description}</div>
                    </div>
                    <div class="lesson-progress">
                        ${progressBadge}
                    </div>
                `;

                lessonItem.onclick = () => startLesson(lesson.id);
                list.appendChild(lessonItem);
            });
        }

        function startLesson(lessonId) {
            requireLogin(function() {
                window.location.href = `lesson.php?id=${lessonId}`;
            }, 'Bạn cần đăng nhập để học bài.');
        }

        function closeLessonsModal() {
            document.getElementById('lessonsModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('lessonsModal');
            if (event.target === modal) {
                closeLessonsModal();
            }
        }
    </script>
</body>
</html>