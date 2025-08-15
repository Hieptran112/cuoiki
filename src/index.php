<?php
    session_start();
    $isLoggedIn = isset($_SESSION['user_id']);
    $username = $isLoggedIn ? ($_SESSION['username'] ?? null) : null;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartDictionary - Từ điển thông minh</title>
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
            padding: 0 20px;
        }

        /* Header */
        header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: #667eea;
            text-decoration: none;
        }

        .nav-menu {
            display: flex;
            gap: 2rem;
            list-style: none;
        }

        .nav-menu a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-menu a:hover {
            color: #667eea;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .btn {
            padding: 0.5rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
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
            background: transparent;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-secondary:hover {
            background: #667eea;
            color: white;
        }

        /* Main Content */
        main {
            padding: 2rem 0;
        }

        .hero-section {
            text-align: center;
            margin-bottom: 3rem;
        }

        .hero-title {
            font-size: 3rem;
            font-weight: 700;
            color: white;
            margin-bottom: 1rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .hero-subtitle {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2rem;
        }

        /* Dictionary Search */
        .search-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .search-container {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .search-input-wrapper {
            flex: 1;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 1rem 1.5rem;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .search-suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #e1e5e9;
            border-top: none;
            border-radius: 0 0 12px 12px;
            max-height: 300px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .search-suggestions.show {
            display: block;
        }

        .suggestion-item {
            padding: 0.75rem 1.5rem;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.2s ease;
        }

        .suggestion-item:hover,
        .suggestion-item.active {
            background-color: #f8f9fa;
        }

        .suggestion-item:last-child {
            border-bottom: none;
        }

        .suggestion-word {
            font-weight: 600;
            color: #333;
        }

        .suggestion-meaning {
            font-size: 0.9rem;
            color: #666;
            margin-top: 0.25rem;
        }

        .search-input:focus {
            outline: none;
            border-color: #667eea;
        }

        .search-btn {
            padding: 1rem 2rem;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .search-btn:hover {
            background: #5a6fd8;
            transform: translateY(-2px);
        }

        /* Dictionary Result */
        .dictionary-result {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 1rem;
            display: none;
        }

        .word-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .word-title {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
        }

        .phonetic {
            color: #667eea;
            font-weight: 500;
        }

        .play-sound {
            background: #667eea;
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .play-sound:hover {
            background: #5a6fd8;
            transform: scale(1.1);
        }

        .meaning-section {
            margin-bottom: 1.5rem;
        }

        .part-of-speech {
            font-weight: 600;
            color: #667eea;
            margin-bottom: 0.5rem;
        }

        .definition {
            color: #555;
            line-height: 1.6;
        }

        .example {
            color: #888;
            font-style: italic;
            margin-top: 0.5rem;
        }

        /* Daily Exercises */
        .exercises-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .exercise-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid #667eea;
        }

        .exercise-question {
            font-weight: 600;
            margin-bottom: 1rem;
            color: #333;
        }

        .exercise-options {
            display: grid;
            gap: 0.5rem;
        }

        .option {
            padding: 0.75rem 1rem;
            background: white;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .option:hover {
            border-color: #667eea;
            background: #f0f2ff;
        }

        .option.correct {
            border-color: #28a745;
            background: #d4edda;
        }

        .option.incorrect {
            border-color: #dc3545;
            background: #f8d7da;
        }

        .submit-btn {
            background: #28a745;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 1rem;
        }

        .submit-btn:hover {
            background: #218838;
        }

        /* Stats Section */
        .stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #666;
            font-weight: 500;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 2rem;
            border-radius: 16px;
            width: 90%;
            max-width: 400px;
            position: relative;
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .close-btn {
            position: absolute;
            top: 1rem;
            right: 1.5rem;
            font-size: 1.5rem;
            cursor: pointer;
            color: #999;
            transition: color 0.3s ease;
        }

        .close-btn:hover {
            color: #333;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
            }

            .nav-menu {
                gap: 1rem;
            }

            .hero-title {
                font-size: 2rem;
            }

            .search-container {
                flex-direction: column;
            }

            .search-suggestions {
                max-height: 200px;
            }

            .stats-section {
                grid-template-columns: 1fr;
            }
        }

        /* Loading Animation */
        .loading {
            display: none;
            text-align: center;
            padding: 2rem;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Snackbar */
        #snackbar {
            visibility: hidden;
            min-width: 240px;
            background-color: #333;
            color: #fff;
            text-align: center;
            border-radius: 8px;
            padding: 12px 16px;
            position: fixed;
            left: 50%;
            bottom: 30px;
            transform: translateX(-50%);
            z-index: 3000;
        }
        #snackbar.show { visibility: visible; animation: fadein 0.3s, fadeout 0.3s 2.7s; }
        #snackbar.success { background-color: #28a745; }
        #snackbar.error { background-color: #dc3545; }
        @keyframes fadein { from { opacity: 0; bottom: 0; } to { opacity: 1; bottom: 30px; } }
        @keyframes fadeout { from { opacity: 1; bottom: 30px; } to { opacity: 0; bottom: 0; } }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <a href="#" class="logo">
                    <i class="fas fa-book-open"></i> SmartDictionary
                </a>
                <nav>
                    <ul class="nav-menu">
                        <li><a href="#dictionary">Từ điển</a></li>
                        <li><a href="#exercises">Bài tập</a></li>
                        <li><a href="flashcards.php">Flashcards</a></li>
                        <li><a href="stats.php">Thống kê</a></li>
                    </ul>
                </nav>
                <div class="user-info">
                    <?php if ($isLoggedIn): ?>
                        <span>Xin chào, <?php echo htmlspecialchars($username); ?></span>
                        <a href="controllers/logout.php" class="btn btn-secondary">Đăng xuất</a>
                    <?php else: ?>
                        <a href="#" onclick="openModal('login')" class="btn btn-primary">Đăng nhập</a>
                        <a href="#" onclick="openModal('register')" class="btn btn-secondary">Đăng ký</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <!-- Hero Section -->
            <section class="hero-section">
                <h1 class="hero-title">Từ điển thông minh</h1>
                <p class="hero-subtitle">Tra cứu từ vựng nhanh chóng và học tập hiệu quả với bài tập hàng ngày</p>
            </section>

            <!-- Dictionary Search Section -->
            <section id="dictionary" class="search-section">
                <h2 class="section-title">
                    <i class="fas fa-search"></i>
                    Tra cứu từ điển
                </h2>
                <div class="search-container">
                    <div class="search-input-wrapper">
                        <input type="text" id="searchInput" class="search-input" placeholder="Nhập từ cần tra cứu..." autocomplete="off">
                        <div id="searchSuggestions" class="search-suggestions"></div>
                    </div>
                    <button onclick="searchWord()" class="search-btn">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                </div>
                
                <div class="loading" id="loading">
                    <div class="spinner"></div>
                    <p>Đang tìm kiếm...</p>
                </div>

                <div class="dictionary-result" id="dictionaryResult">
                    <!-- Dictionary results will be displayed here -->
                </div>
            </section>

            <!-- Daily Exercises Section -->
            <section id="exercises" class="exercises-section">
                <h2 class="section-title">
                    <i class="fas fa-brain"></i>
                    Bài tập hàng ngày
                </h2>
                <div id="exerciseContainer">
                    <!-- Exercises will be loaded here -->
                </div>
                <div style="margin-top:1rem; display:flex; gap:0.5rem;">
                    <button class="btn btn-primary" onclick="loadDailyExercises()">Tải trắc nghiệm</button>
                    <button class="btn" style="background:#17a2b8;color:#fff;" onclick="loadMixedExercises()">Tải bài tập tổng hợp</button>
                </div>
                <div class="exercise-card" style="margin-top:1rem;">
                    <div class="exercise-question" style="display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap;">
                        <span>Thống kê câu trả lời hôm nay</span>
                        <button class="btn btn-secondary" onclick="toggleBreakdown('correct')">Câu trả lời đúng</button>
                        <button class="btn" style="background:#dc3545;color:#fff;" onclick="toggleBreakdown('wrong')">Câu trả lời sai</button>
                    </div>
                    <div id="breakdown-correct" style="display:none; margin-top:0.5rem;"></div>
                    <div id="breakdown-wrong" style="display:none; margin-top:0.5rem;"></div>
                </div>
            </section>







        </div>
    </main>

    <!-- Login Modal -->
    <div id="login-modal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('login')">&times;</span>
            <h3 style="margin-bottom: 1.5rem; color: #333;">Đăng nhập</h3>
            <form onsubmit="return handleLogin(event)">
                <div class="form-group">
                    <label for="login-username">Tên đăng nhập hoặc Email</label>
                    <input type="text" id="login-username" required>
                </div>
                <div class="form-group">
                    <label for="login-password">Mật khẩu</label>
                    <input type="password" id="login-password" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Đăng nhập</button>
            </form>
        </div>
    </div>

    <!-- Register Modal -->
    <div id="register-modal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('register')">&times;</span>
            <h3 style="margin-bottom: 1.5rem; color: #333;">Đăng ký</h3>
            <form onsubmit="return handleRegister(event)">
                <div class="form-group">
                    <label for="register-username">Tên đăng nhập</label>
                    <input type="text" id="register-username" required>
                </div>
                <div class="form-group">
                    <label for="register-email">Email</label>
                    <input type="email" id="register-email" required>
                </div>
                <div class="form-group">
                    <label for="register-fullname">Họ và tên (tuỳ chọn)</label>
                    <input type="text" id="register-fullname">
                </div>
                <div class="form-group">
                    <label for="register-major">Ngành học/Lĩnh vực (tuỳ chọn)</label>
                    <input type="text" id="register-major">
                </div>
                <div class="form-group">
                    <label for="register-password">Mật khẩu</label>
                    <input type="password" id="register-password" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Đăng ký</button>
            </form>
        </div>
    </div>

    <!-- Snackbar -->
    <div id="snackbar"></div>

    <script>
        // Modal functions
        function openModal(type) {
            document.getElementById(type + "-modal").style.display = "block";
        }

        function closeModal(type) {
            document.getElementById(type + "-modal").style.display = "none";
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = "none";
            }
        }

        // Snackbar function
        function showSnackbar(message, type = 'info') {
            const snackbar = document.getElementById("snackbar");
            snackbar.textContent = message;
            snackbar.className = `show ${type}`;
            setTimeout(() => {
                snackbar.className = snackbar.className.replace("show", "");
            }, 3000);
        }

        // Dictionary search function
        function searchWord() {
            const searchInput = document.getElementById('searchInput');
            const word = searchInput.value.trim();
            
            if (!word) {
                showSnackbar('Vui lòng nhập từ cần tra cứu', 'error');
                return;
            }

            const loading = document.getElementById('loading');
            const result = document.getElementById('dictionaryResult');
            
            loading.style.display = 'block';
            result.style.display = 'none';

            // Gọi API thực tế
            fetch(`controllers/dictionary.php?action=search`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ word: word })
            })
            .then(res => res.json())
            .then(data => {
                loading.style.display = 'none';
                result.style.display = 'block';
                
                if (data.success && data.data.length > 0) {
                    displayDictionaryResult(data.data[0]);
                } else {
                    // Hiển thị dữ liệu mẫu nếu không tìm thấy
                    const mockData = {
                        word: word,
                        phonetic: `/ˈ${word.toLowerCase()}/`,
                        vietnamese: `Định nghĩa tiếng Việt cho từ '${word}'`,
                        english_definition: `English definition for '${word}'`,
                        example: `Example sentence using '${word}'`,
                        part_of_speech: 'noun'
                    };
                    displayDictionaryResult(mockData);
                }
            })
            .catch(err => {
                loading.style.display = 'none';
                result.style.display = 'block';
                showSnackbar('Có lỗi xảy ra khi tìm kiếm', 'error');
                console.error('Search error:', err);
            });
        }

        function displayDictionaryResult(data) {
            const result = document.getElementById('dictionaryResult');
            
            let html = `
                <div class="word-header">
                    <div>
                        <div class="word-title">${data.word}</div>
                        <div class="phonetic">${data.phonetic || `/ˈ${data.word.toLowerCase()}/`}</div>
                    </div>
                    <button class="play-sound" onclick="playSound('${data.word}')">
                        <i class="fas fa-volume-up"></i>
                    </button>
                </div>
            `;

            // Hiển thị định nghĩa tiếng Việt
            html += `
                <div class="meaning-section">
                    <div class="part-of-speech">${data.part_of_speech || 'noun'}</div>
                    <div class="definition">${data.vietnamese}</div>
                    ${data.example ? `<div class="example">${data.example}</div>` : ''}
                </div>
            `;

            // Hiển thị định nghĩa tiếng Anh nếu có
            if (data.english_definition) {
                html += `
                    <div class="meaning-section">
                        <div class="part-of-speech">English Definition</div>
                        <div class="definition">${data.english_definition}</div>
                    </div>
                `;
            }

            // Add-to-decks button (visible when logged in)
            <?php if ($isLoggedIn): ?>
            window.lastDictionaryResult = data;
            html += `
                <div style="display:flex; gap:0.5rem;">
                    <button class="btn btn-primary" onclick="openAddToDecksFromLast()">Thêm vào</button>
                </div>
            `;
            <?php endif; ?>

            result.innerHTML = html;
        }

        function playSound(word) {
            // Implement text-to-speech functionality
            if ('speechSynthesis' in window) {
                const utterance = new SpeechSynthesisUtterance(word);
                utterance.lang = 'en-US';
                speechSynthesis.speak(utterance);
            }
        }

        // Autocomplete functionality
        let searchTimeout;
        let currentSuggestionIndex = -1;
        let suggestions = [];

        function setupAutocomplete() {
            const searchInput = document.getElementById('searchInput');
            const suggestionsDiv = document.getElementById('searchSuggestions');

            searchInput.addEventListener('input', function() {
                const query = this.value.trim();

                clearTimeout(searchTimeout);

                if (query.length === 0) {
                    hideSuggestions();
                    return;
                }

                // Debounce search requests
                searchTimeout = setTimeout(() => {
                    fetchSuggestions(query);
                }, 300);
            });

            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    navigateSuggestions(1);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    navigateSuggestions(-1);
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (currentSuggestionIndex >= 0 && suggestions[currentSuggestionIndex]) {
                        selectSuggestion(suggestions[currentSuggestionIndex]);
                    } else {
                        searchWord();
                    }
                } else if (e.key === 'Escape') {
                    hideSuggestions();
                }
            });

            // Hide suggestions when clicking outside
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !suggestionsDiv.contains(e.target)) {
                    hideSuggestions();
                }
            });
        }

        function fetchSuggestions(query) {
            fetch('controllers/dictionary.php?action=suggestions', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ query: query })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    suggestions = data.data;
                    displaySuggestions(suggestions);
                }
            })
            .catch(err => {
                console.error('Error fetching suggestions:', err);
            });
        }

        function displaySuggestions(suggestions) {
            const suggestionsDiv = document.getElementById('searchSuggestions');

            if (suggestions.length === 0) {
                hideSuggestions();
                return;
            }

            let html = '';
            suggestions.forEach((suggestion, index) => {
                html += `
                    <div class="suggestion-item" data-index="${index}" onclick="selectSuggestionByIndex(${index})">
                        <div class="suggestion-word">${suggestion.word}</div>
                        <div class="suggestion-meaning">${suggestion.vietnamese}</div>
                    </div>
                `;
            });

            suggestionsDiv.innerHTML = html;
            suggestionsDiv.classList.add('show');
            currentSuggestionIndex = -1;
        }

        function navigateSuggestions(direction) {
            const suggestionItems = document.querySelectorAll('.suggestion-item');

            if (suggestionItems.length === 0) return;

            // Remove active class from current item
            if (currentSuggestionIndex >= 0) {
                suggestionItems[currentSuggestionIndex].classList.remove('active');
            }

            // Update index
            currentSuggestionIndex += direction;

            if (currentSuggestionIndex < 0) {
                currentSuggestionIndex = suggestionItems.length - 1;
            } else if (currentSuggestionIndex >= suggestionItems.length) {
                currentSuggestionIndex = 0;
            }

            // Add active class to new item
            suggestionItems[currentSuggestionIndex].classList.add('active');

            // Scroll into view if needed
            suggestionItems[currentSuggestionIndex].scrollIntoView({
                block: 'nearest'
            });
        }

        function selectSuggestion(suggestion) {
            const searchInput = document.getElementById('searchInput');
            searchInput.value = suggestion.word;
            hideSuggestions();
            searchWord();
        }

        function selectSuggestionByIndex(index) {
            if (suggestions[index]) {
                selectSuggestion(suggestions[index]);
            }
        }

        function hideSuggestions() {
            const suggestionsDiv = document.getElementById('searchSuggestions');
            suggestionsDiv.classList.remove('show');
            currentSuggestionIndex = -1;
        }

        // Load daily exercises
        function loadDailyExercises() {
            const container = document.getElementById('exerciseContainer');
            
            // Gọi API để lấy bài tập thực tế
            fetch('controllers/dictionary.php?action=get_daily_exercises', {
                method: 'GET'
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data.length > 0) {
                    let html = '';
                    data.data.forEach((exercise, index) => {
                        html += `
                            <div class="exercise-card">
                                <div class="exercise-question">${index + 1}. ${exercise.question}</div>
                                <div class="exercise-options">
                                    ${exercise.options.map((option, optIndex) => `
                                        <div class="option" onclick="selectOption(${index}, ${optIndex})" data-exercise="${index}" data-option="${optIndex}" data-correct-text="${exercise.options[exercise.correct]}">
                                            ${String.fromCharCode(65 + optIndex)}. ${option}
                                        </div>
                                    `).join('')}
                                </div>
                                <button class="submit-btn" onclick="submitExercise(${index}, ${exercise.correct}, ${exercise.dictionary_id||0})" style="display: none;">Kiểm tra</button>
                            </div>
                        `;
                    });
                    container.innerHTML = html;
                } else {
                    // Fallback với dữ liệu mẫu
                    const exercises = [
                        {
                            question: "Chọn từ đúng nghĩa với 'beautiful':",
                            options: ['Đẹp', 'Xấu', 'Cao', 'Thấp'],
                            correct: 0
                        },
                        {
                            question: "Từ nào có nghĩa là 'học tập'?",
                            options: ['Study', 'Play', 'Work', 'Sleep'],
                            correct: 0
                        },
                        {
                            question: "Chọn từ trái nghĩa với 'happy':",
                            options: ['Sad', 'Joyful', 'Excited', 'Pleased'],
                            correct: 0
                        }
                    ];

                    let html = '';
                    exercises.forEach((exercise, index) => {
                        html += `
                            <div class="exercise-card">
                                <div class="exercise-question">${index + 1}. ${exercise.question}</div>
                                <div class="exercise-options">
                                    ${exercise.options.map((option, optIndex) => `
                                        <div class="option" onclick="selectOption(${index}, ${optIndex})" data-exercise="${index}" data-option="${optIndex}">
                                            ${String.fromCharCode(65 + optIndex)}. ${option}
                                        </div>
                                    `).join('')}
                                </div>
                                <button class="submit-btn" onclick="submitExercise(${index}, ${exercise.correct})" style="display: none;">Kiểm tra</button>
                            </div>
                        `;
                    });
                    container.innerHTML = html;
                }
            })
            .catch(err => {
                console.error('Error loading exercises:', err);
                // Fallback với dữ liệu mẫu
                loadDailyExercises();
            });
        }

        // Load mixed exercises (MCQ, cloze, matching, listening)
        function loadMixedExercises() {
            const container = document.getElementById('exerciseContainer');
            container.innerHTML = '<div class="loading"><div class="spinner"></div><p>Đang tải bài tập...</p></div>';
            fetch('controllers/dictionary.php?action=get_mixed_exercises').then(r=>r.json()).then(d=>{
                if(!d.success){ showSnackbar('Không tải được bài tập', 'error'); return; }
                let html = '';
                let idx = 1;
                d.data.forEach(ex => {
                    if (ex.type === 'multiple_choice') {
                        html += `
                        <div class="exercise-card">
                            <div class="exercise-question">${idx++}. ${ex.question}</div>
                            <div class="exercise-options">
                                ${ex.options.map((o,i)=>`<div class="option" onclick="this.parentElement.querySelectorAll('.option').forEach(x=>x.classList.remove('selected')); this.classList.add('selected');" data-correct="${i===ex.correct?1:0}">${String.fromCharCode(65+i)}. ${o}</div>`).join('')}
                            </div>
                            <button class="submit-btn" onclick="(function(btn){ const sel = btn.previousElementSibling.querySelector('.option.selected'); if(!sel){return;} sel.parentElement.querySelectorAll('.option').forEach(opt=>{ if(opt.dataset.correct==='1'){ opt.classList.add('correct'); } else if(opt===sel){ opt.classList.add('incorrect'); } }); btn.style.display='none'; })(this)">Kiểm tra</button>
                        </div>`;
                    // Removed cloze (fill-in) and matching exercises
                    } else if (ex.type === 'listening') {
                        html += `
                        <div class="exercise-card">
                            <div class="exercise-question">${idx++}. Nghe và viết</div>
                            <div style="display:flex; gap:0.5rem;">
                                <button class="btn btn-primary" onclick="(function(word){ const u=new SpeechSynthesisUtterance(word); u.lang='en-US'; speechSynthesis.speak(u); })('${ex.tts_word}')"><i class="fas fa-volume-up"></i> Phát</button>
                                <input class="search-input" placeholder="Nhập từ bạn nghe được" />
                            </div>
                            <button class="submit-btn" data-answer="${ex.answer}" onclick="(function(btn){ const ans = btn.getAttribute('data-answer')||''; const inp = btn.previousElementSibling.querySelector('input'); const ok = (inp.value||'').trim().toLowerCase() === ans.toLowerCase(); if(ok){ showSnackbar('Đúng!', 'success'); } else { showSnackbar('Chưa đúng! Đáp án: '+ans, 'error'); } btn.style.display='none'; })(this)">Kiểm tra</button>
                        </div>`;
                    }
                });
                container.innerHTML = html;
            }).catch(()=>showSnackbar('Lỗi tải bài tập', 'error'));
        }

        function selectOption(exerciseIndex, optionIndex) {
            // Remove previous selections
            document.querySelectorAll(`[data-exercise="${exerciseIndex}"]`).forEach(option => {
                option.classList.remove('selected');
            });
            
            // Select current option
            const selectedOption = document.querySelector(`[data-exercise="${exerciseIndex}"][data-option="${optionIndex}"]`);
            selectedOption.classList.add('selected');
            
            // Show submit button
            const submitBtn = selectedOption.parentElement.nextElementSibling;
            submitBtn.style.display = 'block';
        }

        function submitExercise(exerciseIndex, correctIndex = 0, dictionaryId = 0) {
            const selectedOption = document.querySelector(`[data-exercise="${exerciseIndex}"].selected`);
            if (!selectedOption) return;

            const selectedIndex = parseInt(selectedOption.dataset.option);
            const isCorrect = selectedIndex === correctIndex;
            const selectedText = selectedOption.textContent.replace(/^\s*[A-D]\.\s*/,'').trim();
            const correctText = selectedOption.parentElement.querySelector(`[data-option="${correctIndex}"]`).textContent.replace(/^\s*[A-D]\.\s*/,'').trim();

            // Show result
            document.querySelectorAll(`[data-exercise="${exerciseIndex}"]`).forEach(option => {
                const optionIndex = parseInt(option.dataset.option);
                if (optionIndex === correctIndex) {
                    option.classList.add('correct');
                } else if (optionIndex === selectedIndex && selectedIndex !== correctIndex) {
                    option.classList.add('incorrect');
                }
            });

            // Hide submit button
            selectedOption.parentElement.nextElementSibling.style.display = 'none';

            // Hiển thị thông báo kết quả
            if (isCorrect) {
                showSnackbar('Chính xác! 🎉', 'success');
            } else {
                showSnackbar('Chưa đúng, hãy thử lại! 💪', 'error');
            }

            // Update stats
            updateStats();

            // Persist answer for scheduling if logged in
            if (dictionaryId > 0) {
                fetch('controllers/dictionary.php?action=submit_daily_answer', {
                    method: 'POST', headers: { 'Content-Type': 'application/json' }, credentials: 'same-origin',
                    body: JSON.stringify({ dictionary_id: dictionaryId, selected_vi: selectedText, correct_vi: correctText })
                }).then(r=>r.json()).then(()=>{ loadAnswerBreakdown(); }).catch(()=>{});
            }
        }



        function toggleBreakdown(type) {
            const el = document.getElementById('breakdown-'+type);
            const other = document.getElementById('breakdown-'+(type==='correct'?'wrong':'correct'));
            if (other) other.style.display = 'none';
            el.style.display = (el.style.display === 'none' || !el.style.display) ? 'block' : 'none';
            if (el.style.display === 'block') { loadAnswerBreakdown(); }
        }

        function loadAnswerBreakdown() {
            fetch('controllers/dictionary.php?action=get_answer_breakdown', { credentials: 'same-origin' })
                .then(r=>r.json()).then(d=>{
                    if(!d.success){ return; }
                    const c = d.data.correct || [];
                    const w = d.data.wrong || [];
                    document.getElementById('breakdown-correct').innerHTML = c.length ? c.map(x=>`<div>- <b>${x.word}</b> (${x.vietnamese})</div>`).join('') : 'Chưa có câu trả lời đúng.';
                    document.getElementById('breakdown-wrong').innerHTML = w.length ? w.map(x=>`<div>- <b>${x.word}</b> (${x.vietnamese}) — sai ${x.wrong_count} lần, độ khó: ${x.difficulty.replace('_',' ')}</div>`).join('') : 'Không có mục cần ôn.';
                }).catch(()=>{});
        }



        // Authentication functions
        function handleLogin(event) {
            event.preventDefault();
            const username = document.getElementById("login-username").value;
            const password = document.getElementById("login-password").value;

            fetch("controllers/login.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                credentials: 'same-origin',
                body: JSON.stringify({ username, password })
            })
            .then(res => res.json())
            .then(data => {
                showSnackbar(data.message || (data.success ? "Đăng nhập thành công!" : "Đăng nhập thất bại!"), data.success ? 'success' : 'error');
                if (data.success) {
                    closeModal('login');
                    setTimeout(() => location.reload(), 1500);
                }
            })
            .catch(err => {
                console.error("Lỗi:", err);
                showSnackbar("Có lỗi xảy ra, vui lòng thử lại", 'error');
            });
        }

        function handleRegister(event) {
            event.preventDefault();
            const username = document.getElementById("register-username").value;
            const email = document.getElementById("register-email").value;
            const full_name = document.getElementById("register-fullname").value;
            const major = document.getElementById("register-major").value;
            const password = document.getElementById("register-password").value;

            fetch("controllers/register.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                credentials: 'same-origin',
                body: JSON.stringify({ username, email, password, full_name, major })
            })
            .then(res => res.json())
            .then(data => {
                showSnackbar(data.message || (data.success ? "Đăng ký thành công!" : "Đăng ký thất bại!"), data.success ? 'success' : 'error');
                if (data.success) {
                    closeModal('register');
                    setTimeout(() => location.reload(), 1500);
                }
            })
            .catch(err => {
                console.error("Lỗi:", err);
                showSnackbar("Có lỗi xảy ra, vui lòng thử lại", 'error');
            });
        }

        // Search on Enter key
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchWord();
            }
        });

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            setupAutocomplete();
            loadDailyExercises();
        });
    </script>
</body>
</html>
