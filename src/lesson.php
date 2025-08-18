<?php
session_start();
require_once 'services/database.php';

$isLoggedIn = isset($_SESSION['user_id']);
$username = $_SESSION['username'] ?? '';
$lessonId = $_GET['id'] ?? 0;

if (!$lessonId) {
    header('Location: topics.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bài học - SmartDictionary</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .lesson-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem;
        }

        .lesson-header {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .lesson-title {
            font-size: 2rem;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .lesson-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .topic-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            color: white;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .difficulty-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .lesson-description {
            color: #7f8c8d;
            line-height: 1.6;
        }

        .progress-bar {
            background: #ecf0f1;
            border-radius: 10px;
            height: 8px;
            margin: 1rem 0;
            overflow: hidden;
        }

        .progress-fill {
            background: linear-gradient(135deg, #3498db, #2980b9);
            height: 100%;
            transition: width 0.3s ease;
        }

        .progress-text {
            text-align: center;
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .exercise-container {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .exercise-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .question-number {
            background: #3498db;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: bold;
        }

        .question-text {
            font-size: 1.3rem;
            color: #2c3e50;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .options-container {
            display: grid;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .option {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }

        .option:hover {
            background: #e9ecef;
            border-color: #3498db;
        }

        .option.selected {
            background: #e3f2fd;
            border-color: #3498db;
        }

        .option.correct {
            background: #d4edda;
            border-color: #28a745;
        }

        .option.incorrect {
            background: #f8d7da;
            border-color: #dc3545;
        }

        .option-letter {
            background: #3498db;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 1rem;
            flex-shrink: 0;
        }

        .option.correct .option-letter {
            background: #28a745;
        }

        .option.incorrect .option-letter {
            background: #dc3545;
        }

        .option-text {
            flex: 1;
        }

        .explanation {
            background: #f8f9fa;
            border-left: 4px solid #3498db;
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 0 10px 10px 0;
            display: none;
        }

        .explanation.show {
            display: block;
        }

        .explanation.correct {
            background: #d4edda;
            border-left-color: #28a745;
        }

        .explanation.incorrect {
            background: #f8d7da;
            border-left-color: #dc3545;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .btn {
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2980b9, #1f5f8b);
            transform: scale(1.05);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: scale(1.05);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        .completion-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }

        .completion-content {
            background: white;
            margin: 10% auto;
            padding: 2rem;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            text-align: center;
        }

        .completion-icon {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 1rem;
        }

        .completion-title {
            font-size: 1.8rem;
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        .completion-stats {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin: 1rem 0;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .stat-item:last-child {
            margin-bottom: 0;
        }

        /* Header Styling */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Back Button Styling - giống stats.php */
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

        @media (max-width: 768px) {
            .back-btn {
                position: static;
                margin-bottom: 2rem;
                display: inline-block;
            }
        }

        /* User Info Styling */
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: #2c3e50;
            font-weight: 500;
        }

        .logout-btn {
            padding: 0.5rem 1rem;
            background: #e74c3c;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: #c0392b;
            transform: translateY(-1px);
        }

        .login-btn {
            padding: 0.5rem 1rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .login-btn:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4c93 100%);
            transform: translateY(-1px);
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
        <i class="fas fa-arrow-left"></i>Về trang chủ
    </a>

    <main class="lesson-container">
        <!-- Lesson Header -->
        <div id="lessonHeader" class="lesson-header">
            <!-- Will be populated by JavaScript -->
        </div>

        <!-- Exercise Container -->
        <div id="exerciseContainer" class="exercise-container" style="display: none;">
            <div class="exercise-header">
                <div id="questionNumber" class="question-number"></div>
                <div class="progress-text">
                    <span id="currentQuestion">1</span> / <span id="totalQuestions">15</span>
                </div>
            </div>

            <div id="questionText" class="question-text"></div>

            <div id="optionsContainer" class="options-container"></div>

            <div id="explanation" class="explanation"></div>

            <div class="action-buttons">
                <button id="submitBtn" class="btn btn-primary" onclick="submitAnswer()">Trả lời</button>
                <button id="nextBtn" class="btn btn-secondary" onclick="nextQuestion()" style="display: none;">Câu tiếp theo</button>
            </div>
        </div>
    </main>

    <!-- Completion Modal -->
    <div id="completionModal" class="completion-modal">
        <div class="completion-content">
            <div class="completion-icon">
                <i class="fas fa-trophy"></i>
            </div>
            <h2 class="completion-title">Chúc mừng!</h2>
            <p>Bạn đã hoàn thành bài học này.</p>
            <div id="completionStats" class="completion-stats">
                <!-- Stats will be populated by JavaScript -->
            </div>
            <div class="action-buttons">
                <button class="btn btn-primary" onclick="goBackToTopics()">Quay lại chủ đề</button>
                <button class="btn btn-secondary" onclick="restartLesson()">Làm lại</button>
            </div>
        </div>
    </div>

    <script>
        let lessonData = null;
        let exercises = [];
        let currentExerciseIndex = 0;
        let selectedAnswer = null;
        let results = [];
        const lessonId = <?php echo $lessonId; ?>;

        // Load lesson data when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadLessonData();
        });

        function loadLessonData() {
            fetch(`controllers/topics.php?action=get_lesson_exercises&lesson_id=${lessonId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        lessonData = data.data.lesson;
                        exercises = data.data.exercises;
                        displayLessonHeader();
                        startExercises();
                    } else {
                        alert('Lỗi: ' + data.message);
                        window.location.href = 'topics.php';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi tải bài học');
                    window.location.href = 'topics.php';
                });
        }

        function displayLessonHeader() {
            const header = document.getElementById('lessonHeader');

            let difficultyClass = `difficulty-${lessonData.difficulty}`;
            let difficultyText = lessonData.difficulty === 'beginner' ? 'Cơ bản' :
                               lessonData.difficulty === 'intermediate' ? 'Trung bình' : 'Nâng cao';

            header.innerHTML = `
                <h1 class="lesson-title">${lessonData.title}</h1>
                <div class="lesson-meta">
                    <span class="topic-badge" style="background: ${lessonData.topic_color}">
                        ${lessonData.topic_name}
                    </span>
                    <span class="difficulty-badge ${difficultyClass}">${difficultyText}</span>
                </div>
                <p class="lesson-description">${lessonData.description}</p>
                <div class="progress-bar">
                    <div id="progressFill" class="progress-fill" style="width: 0%"></div>
                </div>
                <div class="progress-text">
                    Tiến độ: <span id="progressText">0%</span>
                </div>
            `;
        }

        function startExercises() {
            if (exercises.length === 0) {
                alert('Bài học này chưa có câu hỏi');
                window.location.href = 'topics.php';
                return;
            }

            document.getElementById('exerciseContainer').style.display = 'block';
            document.getElementById('totalQuestions').textContent = exercises.length;
            displayCurrentExercise();
        }

        function displayCurrentExercise() {
            const exercise = exercises[currentExerciseIndex];

            document.getElementById('questionNumber').textContent = `Câu ${currentExerciseIndex + 1}`;
            document.getElementById('currentQuestion').textContent = currentExerciseIndex + 1;
            document.getElementById('questionText').textContent = exercise.question;

            const optionsContainer = document.getElementById('optionsContainer');
            optionsContainer.innerHTML = '';

            const options = ['A', 'B', 'C', 'D'];
            options.forEach(letter => {
                const option = document.createElement('div');
                option.className = 'option';
                option.onclick = () => selectOption(letter);

                option.innerHTML = `
                    <div class="option-letter">${letter}</div>
                    <div class="option-text">${exercise['option_' + letter.toLowerCase()]}</div>
                `;

                optionsContainer.appendChild(option);
            });

            // Reset state
            selectedAnswer = null;
            document.getElementById('explanation').style.display = 'none';
            document.getElementById('submitBtn').style.display = 'inline-block';
            document.getElementById('nextBtn').style.display = 'none';

            updateProgress();
        }

        function selectOption(letter) {
            // Remove previous selection
            document.querySelectorAll('.option').forEach(opt => {
                opt.classList.remove('selected');
            });

            // Add selection to clicked option
            event.currentTarget.classList.add('selected');
            selectedAnswer = letter;
        }

        function submitAnswer() {
            if (!selectedAnswer) {
                alert('Vui lòng chọn một đáp án');
                return;
            }

            const exercise = exercises[currentExerciseIndex];
            const isCorrect = selectedAnswer === exercise.correct_answer;

            // Save result
            results.push({
                exercise_id: exercise.id,
                selected_answer: selectedAnswer,
                is_correct: isCorrect
            });

            // Show correct/incorrect styling
            document.querySelectorAll('.option').forEach(opt => {
                const letter = opt.querySelector('.option-letter').textContent;
                if (letter === exercise.correct_answer) {
                    opt.classList.add('correct');
                } else if (letter === selectedAnswer && !isCorrect) {
                    opt.classList.add('incorrect');
                }
                opt.onclick = null; // Disable clicking
            });

            // Show explanation
            const explanation = document.getElementById('explanation');
            explanation.textContent = isCorrect ? exercise.explanation_correct : exercise.explanation_wrong;
            explanation.className = `explanation show ${isCorrect ? 'correct' : 'incorrect'}`;

            // Update buttons
            document.getElementById('submitBtn').style.display = 'none';
            document.getElementById('nextBtn').style.display = 'inline-block';

            // Submit to server if logged in
            if (isUserLoggedIn) {
                submitToServer(exercise.id, selectedAnswer);
            }
        }

        function submitToServer(exerciseId, selectedAnswer) {
            fetch('controllers/topics.php?action=submit_topic_exercise', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    lesson_id: lessonId,
                    exercise_id: exerciseId,
                    selected_answer: selectedAnswer
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    console.error('Error submitting answer:', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function nextQuestion() {
            currentExerciseIndex++;

            if (currentExerciseIndex >= exercises.length) {
                // Lesson completed
                showCompletionModal();
            } else {
                displayCurrentExercise();
            }
        }

        function updateProgress() {
            const progress = ((currentExerciseIndex + 1) / exercises.length) * 100;
            document.getElementById('progressFill').style.width = progress + '%';
            document.getElementById('progressText').textContent = Math.round(progress) + '%';
        }

        function showCompletionModal() {
            const correctAnswers = results.filter(r => r.is_correct).length;
            const totalQuestions = results.length;
            const percentage = Math.round((correctAnswers / totalQuestions) * 100);

            const statsHtml = `
                <div class="stat-item">
                    <span>Tổng số câu:</span>
                    <span><strong>${totalQuestions}</strong></span>
                </div>
                <div class="stat-item">
                    <span>Câu trả lời đúng:</span>
                    <span><strong>${correctAnswers}</strong></span>
                </div>
                <div class="stat-item">
                    <span>Câu trả lời sai:</span>
                    <span><strong>${totalQuestions - correctAnswers}</strong></span>
                </div>
                <div class="stat-item">
                    <span>Tỷ lệ chính xác:</span>
                    <span><strong>${percentage}%</strong></span>
                </div>
            `;

            document.getElementById('completionStats').innerHTML = statsHtml;
            document.getElementById('completionModal').style.display = 'block';
        }

        function goBackToTopics() {
            window.location.href = 'topics.php';
        }

        function restartLesson() {
            currentExerciseIndex = 0;
            selectedAnswer = null;
            results = [];
            document.getElementById('completionModal').style.display = 'none';
            displayCurrentExercise();
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('completionModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>