<?php
session_start();
require_once 'services/database.php';

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
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
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
                <p>Theo dõi tiến độ học tập và thành tích của bạn</p>
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
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-number" id="totalWords">0</div>
                    <div class="stat-label">Từ đã học</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-number" id="correctAnswers">0</div>
                    <div class="stat-label">Câu trả lời đúng</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-fire"></i>
                    </div>
                    <div class="stat-number" id="streakDays">0</div>
                    <div class="stat-label">Ngày liên tiếp</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-target"></i>
                    </div>
                    <div class="stat-number" id="accuracy">0%</div>
                    <div class="stat-label">Độ chính xác</div>
                </div>
            </div>

            <!-- Dictionary Stats -->
            <div class="chart-section">
                <h3 class="chart-title">Thống kê từ điển</h3>
                <div class="stats-grid" style="margin-bottom: 0;">
                    <div class="stat-card">
                        <div class="stat-icon" style="color: #28a745;">
                            <i class="fas fa-seedling"></i>
                        </div>
                        <div class="stat-number" id="beginnerWords">0</div>
                        <div class="stat-label">Từ cơ bản</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="color: #ffc107;">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-number" id="intermediateWords">0</div>
                        <div class="stat-label">Từ trung cấp</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="color: #dc3545;">
                            <i class="fas fa-crown"></i>
                        </div>
                        <div class="stat-number" id="advancedWords">0</div>
                        <div class="stat-label">Từ nâng cao</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="color: #667eea;">
                            <i class="fas fa-database"></i>
                        </div>
                        <div class="stat-number" id="totalDictWords">0</div>
                        <div class="stat-label">Tổng từ trong từ điển</div>
                    </div>
                </div>
            </div>

            <!-- Progress Bars -->
            <div class="chart-section">
                <h3 class="chart-title">Tiến độ học tập theo mức độ</h3>
                <div id="progressBars">
                    <!-- Progress bars will be generated here -->
                </div>
            </div>

            <!-- Learning Goals -->
            <div class="chart-section">
                <h3 class="chart-title">Mục tiêu học tập</h3>
                <div class="stats-grid" style="margin-bottom: 0;">
                    <div class="stat-card">
                        <div class="stat-icon" style="color: #17a2b8;">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <div class="stat-number" id="dailyGoal">10</div>
                        <div class="stat-label">Từ mới mỗi ngày</div>
                        <div class="progress-bar" style="margin-top: 1rem;">
                            <div class="progress-fill" id="dailyProgress" style="width: 0%; background: #17a2b8;"></div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="color: #fd7e14;">
                            <i class="fas fa-calendar-week"></i>
                        </div>
                        <div class="stat-number" id="weeklyGoal">50</div>
                        <div class="stat-label">Từ mới mỗi tuần</div>
                        <div class="progress-bar" style="margin-top: 1rem;">
                            <div class="progress-fill" id="weeklyProgress" style="width: 0%; background: #fd7e14;"></div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="color: #6f42c1;">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div class="stat-number" id="monthlyGoal">200</div>
                        <div class="stat-label">Từ mới mỗi tháng</div>
                        <div class="progress-bar" style="margin-top: 1rem;">
                            <div class="progress-fill" id="monthlyProgress" style="width: 0%; background: #6f42c1;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($isLoggedIn): ?>
            <!-- Recent Activity -->
            <div class="recent-activity">
                <h3 class="chart-title">Hoạt động gần đây</h3>
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
            loadDictionaryStats();
            loadUserStats();
            <?php if ($isLoggedIn): ?>
            loadRecentActivity();
            <?php endif; ?>
        }

        function loadDictionaryStats() {
            fetch('controllers/dictionary.php?action=get_stats')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const stats = data.data;
                        document.getElementById('totalDictWords').textContent = stats.total || 0;
                        document.getElementById('beginnerWords').textContent = stats.beginner || 0;
                        document.getElementById('intermediateWords').textContent = stats.intermediate || 0;
                        document.getElementById('advancedWords').textContent = stats.advanced || 0;
                        
                        // Generate progress bars
                        generateProgressBars(stats);
                    }
                })
                .catch(err => console.error('Error loading dictionary stats:', err));
        }

        function loadUserStats() {
            fetch('controllers/dictionary.php?action=get_user_stats', { credentials: 'same-origin' })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const stats = data.data;

                        // Animate numbers
                        animateNumber('totalWords', stats.totalWords);
                        animateNumber('correctAnswers', stats.correctAnswers);
                        animateNumber('streakDays', stats.streakDays);
                        animateNumber('accuracy', stats.accuracy, '%');

                        // Update learning goals with real data
                        updateLearningGoalsReal(stats);
                    }
                })
                .catch(err => {
                    console.error('Error loading user stats:', err);
                    // Fallback to default values
                    animateNumber('totalWords', 0);
                    animateNumber('correctAnswers', 0);
                    animateNumber('streakDays', 0);
                    animateNumber('accuracy', 0, '%');
                });
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

        function generateProgressBars(stats) {
            const total = stats.total || 1;
            const progressBarsContainer = document.getElementById('progressBars');
            
            const levels = [
                { name: 'Cơ bản', value: stats.beginner || 0, color: '#28a745' },
                { name: 'Trung cấp', value: stats.intermediate || 0, color: '#ffc107' },
                { name: 'Nâng cao', value: stats.advanced || 0, color: '#dc3545' }
            ];

            let html = '';
            levels.forEach(level => {
                const percentage = Math.round((level.value / total) * 100);
                html += `
                    <div style="margin-bottom: 1.5rem;">
                        <div class="progress-label">
                            <span>${level.name}</span>
                            <span>${level.value} từ (${percentage}%)</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: ${percentage}%; background: ${level.color};"></div>
                        </div>
                    </div>
                `;
            });

            progressBarsContainer.innerHTML = html;
        }

        function updateLearningGoalsReal(stats) {
            // Sử dụng dữ liệu thực từ database
            const dailyCorrect = stats.todayCorrect || 0;
            const weeklyCorrect = stats.weeklyCorrect || 0;
            const monthlyCorrect = stats.monthlyCorrect || 0;

            const dailyGoal = 10;
            const weeklyGoal = 50;
            const monthlyGoal = 200;

            // Calculate progress percentages
            const dailyProgress = Math.min((dailyCorrect / dailyGoal) * 100, 100);
            const weeklyProgress = Math.min((weeklyCorrect / weeklyGoal) * 100, 100);
            const monthlyProgress = Math.min((monthlyCorrect / monthlyGoal) * 100, 100);

            // Animate progress bars
            setTimeout(() => {
                document.getElementById('dailyProgress').style.width = dailyProgress + '%';
                document.getElementById('weeklyProgress').style.width = weeklyProgress + '%';
                document.getElementById('monthlyProgress').style.width = monthlyProgress + '%';
            }, 1000);

            // Update goal numbers with current progress
            document.getElementById('dailyGoal').textContent = `${dailyCorrect}/${dailyGoal}`;
            document.getElementById('weeklyGoal').textContent = `${weeklyCorrect}/${weeklyGoal}`;
            document.getElementById('monthlyGoal').textContent = `${monthlyCorrect}/${monthlyGoal}`;
        }

        // Keep old function for backward compatibility
        function updateLearningGoals(totalWords) {
            updateLearningGoalsReal({
                todayCorrect: 0,
                weeklyCorrect: 0,
                monthlyCorrect: 0
            });
        }

        <?php if ($isLoggedIn): ?>
        function loadRecentActivity() {
            fetch('controllers/dictionary.php?action=get_answer_breakdown', { credentials: 'same-origin' })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const correct = data.data.correct || [];
                        const wrong = data.data.wrong || [];
                        
                        let html = '';
                        
                        // Show recent correct answers
                        correct.slice(0, 5).forEach(item => {
                            html += `
                                <div class="activity-item">
                                    <div class="activity-icon activity-correct">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-word">${item.word}</div>
                                        <div class="activity-meaning">${item.vietnamese}</div>
                                    </div>
                                </div>
                            `;
                        });

                        // Show recent wrong answers
                        wrong.slice(0, 3).forEach(item => {
                            html += `
                                <div class="activity-item">
                                    <div class="activity-icon activity-wrong">
                                        <i class="fas fa-times"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-word">${item.word}</div>
                                        <div class="activity-meaning">${item.vietnamese} - Cần ôn lại</div>
                                    </div>
                                </div>
                            `;
                        });

                        if (html === '') {
                            html = '<div style="text-align: center; color: #666; padding: 2rem;">Chưa có hoạt động nào hôm nay</div>';
                        }

                        document.getElementById('recentActivity').innerHTML = html;
                    }
                })
                .catch(err => console.error('Error loading recent activity:', err));
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
