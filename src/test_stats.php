<?php
session_start();
require_once 'services/database.php';

$isLoggedIn = isset($_SESSION['user_id']);
if (!$isLoggedIn) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Thống kê - Từ điển thông minh</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 5px;
            cursor: pointer;
            margin: 0.5rem;
        }
        .btn:hover {
            background: #0056b3;
        }
        .btn-success {
            background: #28a745;
        }
        .btn-danger {
            background: #dc3545;
        }
        .stats-display {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            margin: 1rem 0;
        }
        .question {
            background: #e9ecef;
            padding: 1rem;
            border-radius: 5px;
            margin: 1rem 0;
        }

        /* Back Button Styling */
        .back-btn {
            position: fixed;
            top: 2rem;
            left: 2rem;
            background: rgba(102, 126, 234, 0.2);
            color: #667eea;
            border: 2px solid rgba(102, 126, 234, 0.3);
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            z-index: 1000;
        }

        .back-btn:hover {
            background: rgba(102, 126, 234, 0.3);
            transform: translateY(-2px);
        }

        .back-btn i {
            font-size: 1rem;
        }

        @media (max-width: 768px) {
            .back-btn {
                position: static;
                margin-bottom: 2rem;
                display: inline-block;
            }
        }
    </style>
</head>
<body>
    <!-- Back to Home Button -->
    <a href="index.php" class="back-btn">
        <i class="fas fa-arrow-left"></i>Về trang chủ
    </a>

    <div class="container">
        <h1><i class="fas fa-chart-bar"></i> Test Thống kê</h1>
        <p>Trang này giúp test xem thống kê có cập nhật chính xác không khi làm bài tập.</p>
        
        <div class="stats-display" id="currentStats">
            <h3>Thống kê hiện tại:</h3>
            <div id="statsContent">Đang tải...</div>
        </div>

        <div class="question">
            <h3>Câu hỏi test:</h3>
            <p><strong>Từ "hello" có nghĩa là gì?</strong></p>
            <button class="btn btn-success" onclick="submitAnswer(true)">
                <i class="fas fa-check"></i> Xin chào (Đúng)
            </button>
            <button class="btn btn-danger" onclick="submitAnswer(false)">
                <i class="fas fa-times"></i> Tạm biệt (Sai)
            </button>
        </div>

        <div style="margin-top: 2rem;">
            <button class="btn" onclick="loadStats()">
                <i class="fas fa-sync-alt"></i> Refresh Thống kê
            </button>
            <a href="stats.php" class="btn" style="text-decoration: none;">
                <i class="fas fa-chart-line"></i> Xem Trang Thống kê
            </a>
        </div>
    </div>

    <script>
        function loadStats() {
            fetch('controllers/dictionary.php?action=get_user_stats', { credentials: 'same-origin' })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const stats = data.data;
                        document.getElementById('statsContent').innerHTML = `
                            <p><strong>Tổng từ đã học:</strong> ${stats.totalWords}</p>
                            <p><strong>Câu trả lời đúng:</strong> ${stats.correctAnswers}</p>
                            <p><strong>Tổng câu trả lời:</strong> ${stats.totalAnswers}</p>
                            <p><strong>Độ chính xác:</strong> ${stats.accuracy}%</p>
                            <p><strong>Ngày liên tiếp:</strong> ${stats.streakDays}</p>
                            <hr>
                            <p><strong>Hôm nay:</strong> ${stats.todayCorrect}/${stats.todayTotal} đúng</p>
                            <p><strong>Tuần này:</strong> ${stats.weeklyCorrect}/${stats.weeklyTotal} đúng</p>
                            <p><strong>Tháng này:</strong> ${stats.monthlyCorrect}/${stats.monthlyTotal} đúng</p>
                        `;
                    }
                })
                .catch(err => {
                    console.error('Error loading stats:', err);
                    document.getElementById('statsContent').innerHTML = 'Lỗi tải thống kê';
                });
        }

        function submitAnswer(isCorrect) {
            // Simulate submitting an exercise result
            const exerciseData = {
                user_id: <?php echo $_SESSION['user_id']; ?>,
                exercise_id: 1, // ID của từ "hello" trong dictionary
                selected_answer: isCorrect ? 1 : 0,
                correct_answer: 1,
                is_correct: isCorrect
            };

            // Insert directly into exercise_results table
            fetch('controllers/dictionary.php?action=submit_test_answer', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify(exerciseData)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(isCorrect ? 'Đúng rồi! Thống kê đã được cập nhật.' : 'Sai rồi! Thống kê đã được cập nhật.');
                    // Reload stats after 1 second to see the change
                    setTimeout(loadStats, 1000);
                } else {
                    alert('Lỗi: ' + data.message);
                }
            })
            .catch(err => {
                console.error('Error submitting answer:', err);
                alert('Có lỗi xảy ra');
            });
        }

        // Load stats on page load
        document.addEventListener('DOMContentLoaded', loadStats);
    </script>
</body>
</html>
