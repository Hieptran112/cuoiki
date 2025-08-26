<?php
session_start();
require_once 'services/database.php';

// Auto-login for testing - Remove in production
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'admin';
    $_SESSION['email'] = 'admin@example.com';
}

$isLoggedIn = isset($_SESSION['user_id']);
$username = $isLoggedIn ? $_SESSION['username'] : '';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống kê - Từ điển thông minh</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .stats-page {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem 0;
        }

        .stats-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .stats-header {
            text-align: center;
            color: white;
            margin-bottom: 3rem;
        }

        .stats-header h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .stats-header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
            animation: fadeInUp 0.8s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }

        .stat-card.highlight {
            animation: pulse 2s infinite;
        }

        .stat-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #667eea;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1.1rem;
            color: #666;
            font-weight: 500;
        }

        .chart-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .chart-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: #333;
            text-align: center;
        }

        .progress-bar {
            background: #f0f0f0;
            border-radius: 10px;
            height: 20px;
            margin: 1rem 0;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 10px;
            transition: width 0.8s ease;
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .back-btn {
            position: fixed;
            top: 2rem;
            left: 2rem;
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

        .recent-activity {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .activity-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
            transition: background 0.2s ease;
        }

        .activity-item:hover {
            background: #f8f9fa;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.2rem;
        }

        .activity-correct {
            background: #d4edda;
            color: #155724;
        }

        .activity-wrong {
            background: #f8d7da;
            color: #721c24;
        }

        .activity-content {
            flex: 1;
        }

        .activity-word {
            font-weight: 600;
            color: #333;
        }

        .activity-meaning {
            color: #666;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .stats-header h1 {
                font-size: 2rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .back-btn {
                position: static;
                margin-bottom: 2rem;
                display: inline-block;
            }
        }
    </style>
</head>
<body>
    <div class="stats-page">
        <a href="index.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>Về trang chủ
        </a>

        <div class="stats-container">
            <div class="stats-header">
                <h1><i class="fas fa-chart-line"></i> Thống kê học tập</h1>
                <p>Theo dõi tiến độ học tập chủ đề, bài tập và flashcard của bạn</p>
                <?php if ($isLoggedIn): ?>
                <button onclick="refreshStats()" style="background: rgba(255,255,255,0.2); color: white; border: 2px solid rgba(255,255,255,0.3); padding: 0.75rem 1.5rem; border-radius: 50px; margin-top: 1rem; cursor: pointer; transition: all 0.3s ease; backdrop-filter: blur(10px);">
                    <i class="fas fa-sync-alt"></i> Cập nhật thống kê
                </button>
                <?php endif; ?>
            </div>

            <?php if (!$isLoggedIn): ?>
            <!-- Login Required -->
            <div class="chart-section">
                <h3 class="chart-title">
                    <i class="fas fa-lock"></i> Yêu cầu đăng nhập
                </h3>
                <div style="text-align: center; padding: 2rem;">
                    <p style="font-size: 1.1rem; color: #666; margin-bottom: 2rem;">
                        Bạn cần đăng nhập để xem thống kê học tập của mình.
                    </p>
                    <a href="index.php" style="background: #667eea; color: white; padding: 0.75rem 2rem; border-radius: 12px; text-decoration: none; font-weight: 500; transition: all 0.3s ease; display: inline-block;">
                        <i class="fas fa-sign-in-alt"></i> Đăng nhập ngay
                    </a>
                </div>
            </div>
            <?php else: ?>

            <!-- Main Stats Grid -->
            <!-- Overall Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="stat-number" id="lessonsCompleted">0</div>
                    <div class="stat-label">Lessons Completed</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-number" id="topicExercisesCorrect">0</div>
                    <div class="stat-label">Correct Answers</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-cards-blank"></i>
                    </div>
                    <div class="stat-number" id="flashcardsMastered">0</div>
                    <div class="stat-label">Flashcards Mastered</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-number" id="studyDays">0</div>
                    <div class="stat-label">Study Days</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-brain"></i>
                    </div>
                    <div class="stat-number" id="dailyExercisesCorrect">0</div>
                    <div class="stat-label">Daily Exercises</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-number" id="totalPoints">0</div>
                    <div class="stat-label">Total Points</div>
                </div>
            </div>

            <!-- Topic Learning Stats -->
            <div class="chart-section">
                <h3 class="chart-title">Learning Statistics by Topic</h3>
                <div id="topicStats">
                    <!-- Topic stats will be loaded here -->
                </div>
            </div>

            <!-- Flashcard Stats -->
            <div class="chart-section">
                <h3 class="chart-title">Thống kê Flashcard</h3>
                <div class="stats-grid" style="margin-bottom: 1rem;">
                    <div class="stat-card">
                        <div class="stat-icon" style="color: #28a745;">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <div class="stat-number" id="totalDecks">0</div>
                        <div class="stat-label">Bộ thẻ</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="color: #ffc107;">
                            <i class="fas fa-cards-blank"></i>
                        </div>
                        <div class="stat-number" id="totalFlashcards">0</div>
                        <div class="stat-label">Tổng thẻ</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="color: #dc3545;">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div class="stat-number" id="masteredFlashcards">0</div>
                        <div class="stat-label">Thẻ thành thạo</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="color: #667eea;">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <div class="stat-number" id="flashcardAccuracy">0%</div>
                        <div class="stat-label">Độ chính xác</div>
                    </div>
                </div>
                <div id="deckBreakdown">
                    <!-- Deck breakdown will be loaded here -->
                </div>
            </div>

            <?php if ($isLoggedIn): ?>
            <!-- Recent Activity -->
            <div class="recent-activity">
                <h3 class="chart-title">Recent Learning Activity</h3>
                <div id="recentActivity">
                    <!-- Recent activity will be loaded here -->
                </div>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Load all stats
        function loadAllStats() {
            loadOverallStats();
            loadTopicStats();
            loadFlashcardStats();
            <?php if ($isLoggedIn): ?>
            loadRecentActivity();
            <?php endif; ?>
        }

        function loadOverallStats() {
            fetch('controllers/stats.php?action=get_overall_stats', { credentials: 'same-origin' })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const stats = data.data;
                        animateNumber('lessonsCompleted', stats.lessons_completed || 0);
                        animateNumber('topicExercisesCorrect', stats.correct_topic_exercises || 0);
                        animateNumber('flashcardsMastered', stats.flashcards_mastered || 0);
                        animateNumber('studyDays', Math.max(stats.study_days_topics || 0, stats.study_days_flashcards || 0, stats.study_days_daily || 0));
                        animateNumber('dailyExercisesCorrect', stats.daily_exercises_correct || 0);
                        animateNumber('totalPoints', stats.total_points || 0);
                    }
                })
                .catch(err => {
                    console.error('Error loading overall stats:', err);
                    // Fallback to default values
                    animateNumber('lessonsCompleted', 0);
                    animateNumber('topicExercisesCorrect', 0);
                    animateNumber('flashcardsMastered', 0);
                    animateNumber('studyDays', 0);
                    animateNumber('dailyExercisesCorrect', 0);
                    animateNumber('totalPoints', 0);
                });
        }

        function loadTopicStats() {
            fetch('controllers/stats.php?action=get_topic_stats', { credentials: 'same-origin' })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        displayTopicStats(data.data);
                    } else {
                        document.getElementById('topicStats').innerHTML = '<p style="text-align: center; color: #666;">No topic data available</p>';
                    }
                })
                .catch(err => {
                    console.error('Error loading topic stats:', err);
                    document.getElementById('topicStats').innerHTML = '<p style="text-align: center; color: #666;">Error loading topic data</p>';
                });
        }

        function loadFlashcardStats() {
            fetch('controllers/stats.php?action=get_flashcard_stats', { credentials: 'same-origin' })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const stats = data.data.overall;
                        animateNumber('totalDecks', stats.total_decks || 0);
                        animateNumber('totalFlashcards', stats.total_flashcards || 0);
                        animateNumber('masteredFlashcards', stats.mastered_cards || 0);

                        const accuracy = stats.total_reviews > 0 ?
                            Math.round((stats.total_correct_reviews / stats.total_reviews) * 100) : 0;
                        animateNumber('flashcardAccuracy', accuracy, '%');

                        displayDeckBreakdown(data.data.decks);
                    }
                })
                .catch(err => console.error('Error loading flashcard stats:', err));
        }

        function displayTopicStats(topics) {
            const container = document.getElementById('topicStats');
            if (!topics || topics.length === 0) {
                container.innerHTML = '<p style="text-align: center; color: #666; padding: 2rem;">Chưa có dữ liệu học tập theo chủ đề.</p>';
                return;
            }

            let html = '';
            topics.forEach(topic => {
                const completionRate = topic.total_lessons > 0 ?
                    Math.round((topic.completed_lessons / topic.total_lessons) * 100) : 0;
                const accuracy = topic.total_answers > 0 ?
                    Math.round((topic.correct_answers / topic.total_answers) * 100) : 0;

                html += `
                    <div style="background: white; border-radius: 12px; padding: 1.5rem; margin-bottom: 1rem; border-left: 4px solid ${topic.color};">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <h4 style="color: #333; margin: 0;">${topic.topic_name}</h4>
                            <span style="background: ${topic.color}; color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.8rem;">
                                ${topic.completed_lessons}/${topic.total_lessons} bài
                            </span>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                            <div style="text-align: center;">
                                <div style="font-size: 1.5rem; font-weight: 600; color: ${topic.color};">${completionRate}%</div>
                                <div style="font-size: 0.9rem; color: #666;">Completed</div>
                            </div>
                            <div style="text-align: center;">
                                <div style="font-size: 1.5rem; font-weight: 600; color: ${topic.color};">${accuracy}%</div>
                                <div style="font-size: 0.9rem; color: #666;">Accuracy</div>
                            </div>
                        </div>
                        <div style="background: #f0f0f0; border-radius: 10px; height: 8px; overflow: hidden;">
                            <div style="height: 100%; background: ${topic.color}; width: ${completionRate}%; transition: width 0.8s ease;"></div>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        }

        function displayDeckBreakdown(decks) {
            const container = document.getElementById('deckBreakdown');
            if (!decks || decks.length === 0) {
                container.innerHTML = '<p style="text-align: center; color: #666; padding: 1rem;">Chưa có bộ thẻ nào.</p>';
                return;
            }

            let html = '<h4 style="margin-bottom: 1rem; color: #333;">Chi tiết theo bộ thẻ:</h4>';
            decks.forEach(deck => {
                const masteryRate = deck.card_count > 0 ?
                    Math.round((deck.mastered_count / deck.card_count) * 100) : 0;

                html += `
                    <div style="background: #f8f9fa; border-radius: 8px; padding: 1rem; margin-bottom: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-weight: 500; color: #333;">${deck.deck_name}</div>
                            <div style="font-size: 0.9rem; color: #666;">${deck.studied_count}/${deck.card_count} thẻ đã học</div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-weight: 600; color: #28a745;">${masteryRate}%</div>
                            <div style="font-size: 0.8rem; color: #666;">Thành thạo</div>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        }

        function animateNumber(elementId, targetValue, suffix = '') {
            const element = document.getElementById(elementId);
            const card = element.closest('.stat-card');
            const startValue = 0;
            const duration = 2000;
            const startTime = performance.now();

            // Add highlight effect
            if (card) {
                card.classList.add('highlight');
                setTimeout(() => card.classList.remove('highlight'), 3000);
            }

            function updateNumber(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const currentValue = Math.floor(startValue + (targetValue - startValue) * progress);

                element.textContent = currentValue + suffix;

                if (progress < 1) {
                    requestAnimationFrame(updateNumber);
                }
            }

            requestAnimationFrame(updateNumber);
        }



        <?php if ($isLoggedIn): ?>
        function loadRecentActivity() {
            fetch('controllers/stats.php?action=get_recent_activity', { credentials: 'same-origin' })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        displayRecentActivity(data.data);
                    } else {
                        document.getElementById('recentActivity').innerHTML = '<p style="text-align: center; color: #666; padding: 2rem;">No recent activity available.</p>';
                    }
                })
                .catch(err => {
                    console.error('Error loading recent activity:', err);
                    document.getElementById('recentActivity').innerHTML = '<p style="text-align: center; color: #f44336; padding: 2rem;">Error loading recent activity.</p>';
                });
        }

        function displayRecentActivity(activities) {
            const container = document.getElementById('recentActivity');

            if (!activities || activities.length === 0) {
                container.innerHTML = '<p style="text-align: center; color: #666; padding: 2rem;">Chưa có hoạt động học tập nào.</p>';
                return;
            }

            let html = '';
            activities.forEach(activity => {
                const isCorrect = activity.is_correct == 1;
                const timeAgo = formatTimeAgo(activity.activity_time);

                if (activity.activity_type === 'topic_exercise') {
                    html += `
                        <div class="activity-item">
                            <div class="activity-icon ${isCorrect ? 'activity-correct' : 'activity-wrong'}">
                                <i class="fas ${isCorrect ? 'fa-check' : 'fa-times'}"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-word">Bài tập: ${activity.topic_name}</div>
                                <div class="activity-meaning">${activity.lesson_title} - ${isCorrect ? 'Đúng' : 'Sai'}</div>
                                <div class="activity-time">${timeAgo}</div>
                            </div>
                        </div>
                    `;
                } else if (activity.activity_type === 'flashcard_review') {
                    html += `
                        <div class="activity-item">
                            <div class="activity-icon ${isCorrect ? 'activity-correct' : 'activity-wrong'}">
                                <i class="fas fa-cards-blank"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-word">Flashcard: ${activity.activity_description}</div>
                                <div class="activity-meaning">${activity.deck_name} - ${activity.ease_level || 'Đánh giá'}</div>
                                <div class="activity-time">${timeAgo}</div>
                            </div>
                        </div>
                    `;
                }
            });

            if (html === '') {
                html = '<div style="text-align: center; color: #666; padding: 2rem;">Chưa có hoạt động nào hôm nay</div>';
            }

            container.innerHTML = html;
        }

        function formatTimeAgo(dateString) {
            const now = new Date();
            const date = new Date(dateString);
            const diffInSeconds = Math.floor((now - date) / 1000);

            if (diffInSeconds < 60) return 'Vừa xong';
            if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} phút trước`;
            if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} giờ trước`;
            if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)} ngày trước`;
            return date.toLocaleDateString('vi-VN');
        }
        <?php endif; ?>

        <?php if ($isLoggedIn): ?>
        function refreshStats() {
            // Add loading effect
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang cập nhật...';
            button.disabled = true;

            // Reload all stats from database
            loadAllStats();

            // Reset button after loading
            setTimeout(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            }, 1000);
        }
        <?php endif; ?>

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($isLoggedIn): ?>
            loadAllStats();
            <?php endif; ?>
        });
    </script>
</body>
</html>
