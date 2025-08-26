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
    <title>Flashcards - Từ điển thông minh</title>
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

        /* Main Content */
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

        .section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow:
                0 20px 60px rgba(0, 0, 0, 0.1),
                0 0 0 1px rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .section::before {
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

        .section:hover {
            transform: translateY(-5px);
            box-shadow:
                0 30px 80px rgba(0, 0, 0, 0.15),
                0 0 0 1px rgba(255, 255, 255, 0.3);
        }

        .section:hover::before {
            opacity: 1;
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

        .section-title i {
            color: #667eea;
        }

        /* Import Text Section */
        .import-section {
            margin-bottom: 2rem;
        }

        .textarea-container {
            margin-bottom: 1rem;
        }

        .import-textarea {
            width: 100%;
            min-height: 200px;
            padding: 1rem;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 1rem;
            font-family: inherit;
            resize: vertical;
        }

        .import-textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .import-controls {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: center;
        }

        .import-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .import-btn:hover {
            background: #5a6fd8;
            transform: translateY(-2px);
        }

        .import-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        /* Results */
        .results-container {
            margin-top: 2rem;
        }

        .word-suggestion {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            padding: 0.75rem;
            border-bottom: 1px solid #eee;
            transition: background 0.2s ease;
        }

        .word-suggestion:hover {
            background: #f8f9fa;
        }

        .word-suggestion:last-child {
            border-bottom: none;
        }

        .word-suggestion input[type="checkbox"] {
            margin-top: 0.25rem;
        }

        .word-info {
            flex: 1;
        }

        .word-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.25rem;
        }

        .word-meaning {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }

        .word-example {
            color: #888;
            font-style: italic;
            font-size: 0.85rem;
        }

        .word-source {
            color: #999;
            font-size: 0.8rem;
        }

        /* Enhanced Deck Cards */
        .deck-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            color: white;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .deck-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .deck-card:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.3);
        }

        .deck-card:hover::before {
            opacity: 1;
        }

        .deck-card.selected {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(102, 126, 234, 0.4);
            border-color: rgba(255, 255, 255, 0.4);
        }

        .deck-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
        }

        .deck-name {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .deck-description {
            opacity: 0.9;
            font-size: 0.9rem;
            line-height: 1.4;
        }

        .deck-stats {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
            position: relative;
            z-index: 1;
        }

        .deck-stat {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.85rem;
            opacity: 0.9;
        }

        .deck-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
            position: relative;
            z-index: 1;
        }

        .deck-btn {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .deck-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-1px);
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

            .page-header h1 {
                font-size: 2rem;
            }

            .import-controls {
                flex-direction: column;
                align-items: stretch;
            }
        }

        /* Loading */
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
        .snackbar {
            visibility: hidden;
            min-width: 250px;
            margin-left: -125px;
            background-color: #333;
            color: #fff;
            text-align: center;
            border-radius: 2px;
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

        /* Page Header Styling */
        .page-header {
            display: flex;
            align-items: center;
            margin-bottom: 3rem;
            position: relative;
        }

        .page-header .back-btn {
            position: absolute;
            left: 0;
            top: -10px;
        }

        .page-header > div {
            flex: 1;
            text-align: center;
        }

        /* Back Button Styling */
        .back-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .back-btn i {
            font-size: 1rem;
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
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
                    <?php if ($isLoggedIn): ?>
                        <span>Xin chào, <?php echo htmlspecialchars($username); ?></span>
                        <a href="profile.php" class="btn btn-primary">Hồ sơ</a>
                        <a href="controllers/logout.php" class="btn btn-secondary">Đăng xuất</a>
                    <?php else: ?>
                        <a href="index.php" class="btn btn-primary">Đăng nhập</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <a href="index.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>Về trang chủ
                </a>
                <div>
                    <h1><i class="fas fa-layer-group"></i> Flashcards</h1>
                    <p>Tạo và quản lý thẻ học từ vựng của bạn</p>
                </div>
            </div>

            <?php if (!$isLoggedIn): ?>
            <!-- Login Required -->
            <div class="section">
                <div class="section-title">
                    <i class="fas fa-lock"></i>
                    Yêu cầu đăng nhập
                </div>
                <p>Bạn cần đăng nhập để sử dụng tính năng Flashcards.</p>
                <div style="margin-top: 1rem;">
                    <a href="index.php" class="btn btn-primary">Đăng nhập ngay</a>
                </div>
            </div>
            <?php else: ?>
            
            <!-- Import Text Section -->
            <div class="section">
                <div class="section-title">
                    <i class="fas fa-file-import"></i>
                    Nhập từ vựng từ văn bản
                </div>
                <div class="import-section">
                    <div class="textarea-container">
                        <textarea 
                            id="import-text" 
                            class="import-textarea" 
                            placeholder="Dán văn bản tiếng Anh vào đây để trích xuất từ vựng...&#10;&#10;Ví dụ:&#10;• I am Long. I am a student.&#10;• My family has four people. My father works in a company.&#10;• I like to read books and watch movies.&#10;• The weather is beautiful today. The sun is shining.&#10;&#10;Hệ thống sẽ tự động tìm và trích xuất các từ vựng có trong từ điển để bạn học!"
                        ></textarea>
                    </div>
                    <div class="import-controls">
                        <button id="extract-btn" class="import-btn" onclick="extractWords()">
                            <i class="fas fa-magic"></i> Trích xuất từ vựng
                        </button>
                        <label>
                            Độ dài tối thiểu: 
                            <select id="min-length" style="padding: 0.5rem; border-radius: 5px; border: 1px solid #ddd;">
                                <option value="1">1 ký tự (tất cả từ)</option>
                                <option value="2">2 ký tự</option>
                                <option value="3" selected>3 ký tự</option>
                                <option value="4">4 ký tự</option>
                                <option value="5">5 ký tự</option>
                                <option value="6">6 ký tự</option>
                            </select>
                        </label>
                    </div>
                </div>

                <div class="loading" id="extract-loading">
                    <div class="spinner"></div>
                    <p>Đang trích xuất từ vựng...</p>
                </div>

                <div class="results-container" id="extract-results">
                    <!-- Results will be displayed here -->
                </div>
            </div>

            <!-- Flashcards Management Section -->
            <div class="section">
                <div class="section-title">
                    <i class="fas fa-layer-group"></i>
                    Quản lý Flashcards
                </div>

                <!-- Create Deck -->
                <div class="import-section">
                    <h4 style="margin-bottom: 1.5rem;">Tạo bộ thẻ mới</h4>
                    <div class="import-controls">
                        <input id="deck-name" class="import-textarea" style="min-height: auto; padding: 0.75rem;" placeholder="Tên bộ thẻ" />
                        <input id="deck-desc" class="import-textarea" style="min-height: auto; padding: 0.75rem;" placeholder="Mô tả (tuỳ chọn)" />
                        <select id="deck-visibility" class="import-textarea" style="min-height: auto; padding: 0.75rem;">
                            <option value="private">Riêng tư</option>
                            <option value="public">Công khai</option>
                        </select>
                        <button class="import-btn" onclick="requireLogin(createDeck, 'Bạn cần đăng nhập để tạo bộ thẻ.')">
                            <i class="fas fa-plus"></i> Tạo bộ thẻ
                        </button>
                    </div>
                </div>

                <!-- My Decks -->
                <div class="results-container">
                    <h4 style="margin-bottom: 1.5rem;">Bộ thẻ của tôi</h4>
                    <div id="deck-list">
                        <!-- Decks will be loaded here -->
                    </div>
                </div>

                <!-- Selected Deck Management -->
                <div id="deck-detail" class="results-container" style="display:none;">
                    <h4 id="deck-title">Quản lý thẻ</h4>
                    <div class="import-controls">
                        <input id="card-word" class="import-textarea" style="min-height: auto; padding: 0.75rem;" placeholder="Từ vựng" />
                        <input id="card-definition" class="import-textarea" style="min-height: auto; padding: 0.75rem;" placeholder="Định nghĩa" />
                        <input id="card-example" class="import-textarea" style="min-height: auto; padding: 0.75rem;" placeholder="Ví dụ (tuỳ chọn)" />
                        <button class="import-btn" onclick="requireLogin(createFlashcard, 'Bạn cần đăng nhập để thêm thẻ.')">
                            <i class="fas fa-plus"></i> Thêm thẻ
                        </button>
                    </div>
                    <div id="card-list" style="margin-top: 1rem;">
                        <!-- Cards will be loaded here -->
                    </div>
                </div>



                <!-- Search Flashcards -->
                <div class="results-container">
                    <h4>Tìm kiếm trong bộ thẻ của tôi</h4>
                    <div class="import-controls">
                        <input id="my-search" class="import-textarea" style="min-height: auto; padding: 0.75rem; flex: 1;" placeholder="Nhập từ khoá" />
                        <button class="import-btn" onclick="searchMyFlashcards()" style="flex-shrink: 0;">
                            <i class="fas fa-search"></i> Tìm kiếm
                        </button>
                    </div>
                    <div id="my-search-results" style="margin-top: 1rem;">
                        <!-- Search results will be displayed here -->
                    </div>

                    <!-- Dictionary Search Fallback -->
                    <div id="dictionary-search-section" style="margin-top: 2rem; display: none;">
                        <h4>Tìm kiếm trong từ điển</h4>
                        <p style="color: #666; margin-bottom: 1rem;">Không tìm thấy trong bộ thẻ? Tìm kiếm trong từ điển và thêm vào bộ thẻ:</p>
                        <div id="dictionary-search-results">
                            <!-- Dictionary search results will be displayed here -->
                        </div>
                    </div>
                </div>
            </div>

            <?php endif; ?>
        </div>
    </main>

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

        <?php if ($isLoggedIn): ?>
        // Authentication helper
        function requireLogin(callback, message = 'Bạn cần đăng nhập để thực hiện chức năng này.') {
            <?php if ($isLoggedIn): ?>
            callback();
            <?php else: ?>
            showSnackbar(message, 'error');
            <?php endif; ?>
        }

        // Extract words function
        function extractWords() {
            const text = document.getElementById('import-text').value.trim();
            const minLength = parseInt(document.getElementById('min-length').value);

            if (!text) {
                showSnackbar('Vui lòng nhập văn bản cần trích xuất', 'error');
                return;
            }

            const extractBtn = document.getElementById('extract-btn');
            const loading = document.getElementById('extract-loading');
            const results = document.getElementById('extract-results');

            extractBtn.disabled = true;
            extractBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
            loading.style.display = 'block';
            results.innerHTML = '';

            fetch('controllers/flashcards.php?action=extract_keywords', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({
                    text: text,
                    min_length: minLength,
                    top_k: 50,
                    domain: ''
                })
            })
            .then(res => res.json())
            .then(data => {
                loading.style.display = 'none';
                extractBtn.disabled = false;
                extractBtn.innerHTML = '<i class="fas fa-magic"></i> Trích xuất từ vựng';

                console.log('Extract response:', data); // Debug log

                if (data.success) {
                    if (data.data && data.data.length > 0) {
                        displayExtractResults(data.data);
                    } else {
                        results.innerHTML = '<p style="text-align: center; color: #666; padding: 2rem;">Không tìm thấy từ vựng phù hợp trong từ điển. Hãy thử với văn bản khác hoặc giảm độ dài tối thiểu.</p>';
                    }
                } else {
                    results.innerHTML = `<p style="text-align: center; color: #f44336; padding: 2rem;">Lỗi: ${data.message || 'Không thể trích xuất từ vựng'}</p>`;
                }
            })
            .catch(err => {
                console.error('Extract error:', err);
                loading.style.display = 'none';
                extractBtn.disabled = false;
                extractBtn.innerHTML = '<i class="fas fa-magic"></i> Trích xuất từ vựng';
                showSnackbar('Có lỗi xảy ra khi trích xuất từ vựng', 'error');
            });
        }

        function displayExtractResults(words) {
            const results = document.getElementById('extract-results');

            let html = `
                <div style="margin-bottom: 1rem;">
                    <h4>Tìm thấy ${words.length} từ vựng:</h4>
                    <div style="margin-top: 0.5rem; display: flex; gap: 0.5rem;">
                        <button class="btn btn-primary" onclick="selectAllWords(true)">Chọn tất cả</button>
                        <button class="btn btn-secondary" onclick="selectAllWords(false)">Bỏ chọn tất cả</button>
                        <button class="btn btn-primary" onclick="addSelectedToDecks()">Thêm vào bộ thẻ...</button>
                    </div>
                </div>
                <div style="max-height: 400px; overflow-y: auto; border: 1px solid #eee; border-radius: 8px;">
            `;

            words.forEach((word, index) => {
                html += `
                    <div class="word-suggestion">
                        <input type="checkbox" id="word-${index}" data-word="${word.word}" data-def="${(word.english_definition || word.vietnamese || '').replace(/"/g, '&quot;')}" data-example="${(word.example || '').replace(/"/g, '&quot;')}" />
                        <div class="word-info">
                            <div class="word-name">${word.word}</div>
                            ${word.vietnamese ? `<div class="word-meaning">${word.vietnamese}</div>` : ''}
                            ${word.english_definition ? `<div class="word-meaning" style="color: #555;">${word.english_definition}</div>` : ''}
                            ${word.example ? `<div class="word-example">${word.example}</div>` : ''}
                            <div class="word-source">${word.source}${word.domain ? '/' + word.domain : ''}</div>
                        </div>
                    </div>
                `;
            });

            html += '</div>';
            results.innerHTML = html;
        }

        function selectAllWords(select) {
            const checkboxes = document.querySelectorAll('#extract-results input[type="checkbox"]');
            checkboxes.forEach(cb => cb.checked = select);
        }

        function addSelectedToDecks() {
            const checkboxes = document.querySelectorAll('#extract-results input[type="checkbox"]:checked');
            if (checkboxes.length === 0) {
                showSnackbar('Vui lòng chọn ít nhất một từ', 'error');
                return;
            }

            // Get selected words
            const words = Array.from(checkboxes).map(cb => ({
                word: cb.dataset.word,
                definition: cb.dataset.def,
                example: cb.dataset.example || ''
            }));

            // Show deck selection modal
            showDeckSelectionModal(words);
        }

        function showDeckSelectionModal(words) {
            // Create modal HTML
            const modalHtml = `
                <div id="deck-selection-modal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center;">
                    <div style="background: white; padding: 2rem; border-radius: 10px; max-width: 500px; width: 90%;">
                        <h3>Chọn bộ thẻ để thêm ${words.length} từ</h3>
                        <div id="deck-selection-list" style="margin: 1rem 0; max-height: 300px; overflow-y: auto;">
                            <p>Đang tải danh sách bộ thẻ...</p>
                        </div>
                        <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                            <button class="btn btn-secondary" onclick="closeDeckSelectionModal()">Hủy</button>
                            <button class="btn btn-primary" onclick="addToSelectedDecks(${JSON.stringify(words).replace(/"/g, '&quot;')})">Thêm vào bộ thẻ</button>
                        </div>
                    </div>
                </div>
            `;

            document.body.insertAdjacentHTML('beforeend', modalHtml);
            loadDecksForSelection();
        }

        function loadDecksForSelection() {
            fetch('controllers/flashcards.php?action=list_decks', { credentials: 'same-origin' })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const html = data.data.map(deck => `
                            <label style="display: block; margin: 0.5rem 0; padding: 0.5rem; border: 1px solid #ddd; border-radius: 5px; cursor: pointer;">
                                <input type="checkbox" name="selected-decks" value="${deck.id}" style="margin-right: 0.5rem;">
                                <strong>${deck.name}</strong>
                                ${deck.description ? `<br><small style="color: #666;">${deck.description}</small>` : ''}
                            </label>
                        `).join('');
                        document.getElementById('deck-selection-list').innerHTML = html || '<p>Không có bộ thẻ nào.</p>';
                    }
                })
                .catch(err => {
                    console.error('Error loading decks:', err);
                    document.getElementById('deck-selection-list').innerHTML = '<p style="color: red;">Có lỗi xảy ra khi tải danh sách bộ thẻ.</p>';
                });
        }

        function closeDeckSelectionModal() {
            const modal = document.getElementById('deck-selection-modal');
            if (modal) modal.remove();
        }

        function addToSelectedDecks(words) {
            const selectedDecks = Array.from(document.querySelectorAll('input[name="selected-decks"]:checked')).map(cb => cb.value);

            if (selectedDecks.length === 0) {
                showSnackbar('Vui lòng chọn ít nhất một bộ thẻ', 'error');
                return;
            }

            fetch('controllers/flashcards.php?action=add_words_to_decks', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ words: words, deck_ids: selectedDecks })
            })
            .then(res => res.json())
            .then(data => {
                showSnackbar(data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    closeDeckSelectionModal();
                    // Clear extract results
                    document.getElementById('extract-results').innerHTML = '';
                    document.getElementById('import-text').value = '';
                }
            })
            .catch(err => {
                console.error('Error adding words:', err);
                showSnackbar('Có lỗi xảy ra khi thêm từ vào bộ thẻ', 'error');
            });
        }

        // Flashcards Management
        let selectedDeckId = null;
        let studyQueue = [];
        let studyIndex = 0;
        let showingBack = false;

        function createDeck() {
            const name = document.getElementById('deck-name').value.trim();
            const description = document.getElementById('deck-desc').value.trim();
            const visibility = document.getElementById('deck-visibility').value;

            if (!name) {
                showSnackbar('Vui lòng nhập tên bộ thẻ', 'error');
                return;
            }

            fetch('controllers/flashcards.php?action=create_deck', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ name, description, visibility })
            })
            .then(res => res.json())
            .then(data => {
                showSnackbar(data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    document.getElementById('deck-name').value = '';
                    document.getElementById('deck-desc').value = '';
                    loadDecks();
                }
            })
            .catch(err => {
                console.error('Error creating deck:', err);
                showSnackbar('Có lỗi xảy ra khi tạo bộ thẻ', 'error');
            });
        }

        function loadDecks() {
            fetch('controllers/flashcards.php?action=list_decks', { credentials: 'same-origin' })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        document.getElementById('deck-list').innerHTML = '<p style="color: #666;">Không tải được danh sách bộ thẻ.</p>';
                        return;
                    }

                    const html = data.data.map(deck => `
                        <div class="deck-card ${selectedDeckId === deck.id ? 'selected' : ''}" onclick="selectDeck(${deck.id}, '${deck.name.replace(/'/g, "\\'")}')">
                            <div class="deck-header">
                                <div>
                                    <div class="deck-name">${deck.name}</div>
                                    <div class="deck-description">${deck.description || 'Không có mô tả'}</div>
                                </div>
                                <div style="opacity: 0.7;">
                                    <i class="fas ${deck.visibility === 'public' ? 'fa-globe' : 'fa-lock'}"></i>
                                </div>
                            </div>
                            <div class="deck-stats">
                                <div class="deck-stat">
                                    <i class="fas fa-cards-blank"></i>
                                    <span>${deck.card_count || 0} thẻ</span>
                                </div>
                                <div class="deck-stat">
                                    <i class="fas fa-eye"></i>
                                    <span>${deck.visibility === 'public' ? 'Công khai' : 'Riêng tư'}</span>
                                </div>
                            </div>
                            <div class="deck-actions" onclick="event.stopPropagation()">
                                <button class="deck-btn" onclick="selectDeck(${deck.id}, '${deck.name.replace(/'/g, "\\'")}')">
                                    <i class="fas fa-play"></i> Học
                                </button>
                                <button class="deck-btn" onclick="deleteDeck(${deck.id})">
                                    <i class="fas fa-trash"></i> Xóa
                                </button>
                            </div>
                        </div>
                    `).join('');

                    document.getElementById('deck-list').innerHTML = html || '<p style="color: #666;">Chưa có bộ thẻ nào.</p>';
                })
                .catch(err => {
                    console.error('Error loading decks:', err);
                    document.getElementById('deck-list').innerHTML = '<p style="color: #666;">Có lỗi xảy ra khi tải danh sách.</p>';
                });
        }

        function selectDeck(id, name) {
            selectedDeckId = id;
            document.getElementById('deck-title').textContent = `Quản lý thẻ - ${name}`;
            document.getElementById('deck-detail').style.display = 'block';
            document.getElementById('study-panel').style.display = 'block';

            // Update deck card selection
            document.querySelectorAll('.deck-card').forEach(card => {
                card.classList.remove('selected');
            });
            event.target.closest('.deck-card')?.classList.add('selected');

            loadFlashcards();
            loadStudyQueue();
            showSnackbar(`Đã chọn bộ thẻ: ${name}`, 'success');
        }

        function deleteDeck(id) {
            if (!confirm('Bạn có chắc muốn xóa bộ thẻ này?')) return;

            fetch('controllers/flashcards.php?action=delete_deck', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ deck_id: id })
            })
            .then(res => res.json())
            .then(data => {
                showSnackbar(data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    loadDecks();
                    document.getElementById('deck-detail').style.display = 'none';
                    document.getElementById('study-panel').style.display = 'none';
                }
            })
            .catch(err => {
                console.error('Error deleting deck:', err);
                showSnackbar('Có lỗi xảy ra khi xóa bộ thẻ', 'error');
            });
        }

        function createFlashcard() {
            const word = document.getElementById('card-word').value.trim();
            const definition = document.getElementById('card-definition').value.trim();
            const example = document.getElementById('card-example').value.trim();

            if (!selectedDeckId) {
                showSnackbar('Vui lòng chọn bộ thẻ trước', 'error');
                return;
            }

            if (!word || !definition) {
                showSnackbar('Vui lòng nhập từ và định nghĩa', 'error');
                return;
            }

            fetch('controllers/flashcards.php?action=create_flashcard', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({
                    deck_id: selectedDeckId,
                    word: word,
                    definition: definition,
                    example: example
                })
            })
            .then(res => res.json())
            .then(data => {
                showSnackbar(data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    document.getElementById('card-word').value = '';
                    document.getElementById('card-definition').value = '';
                    document.getElementById('card-example').value = '';
                    loadFlashcards();
                }
            })
            .catch(err => {
                console.error('Error creating flashcard:', err);
                showSnackbar('Có lỗi xảy ra khi tạo thẻ', 'error');
            });
        }

        function loadFlashcards() {
            if (!selectedDeckId) return;

            fetch(`controllers/flashcards.php?action=list_flashcards&deck_id=${selectedDeckId}`, { credentials: 'same-origin' })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        document.getElementById('card-list').innerHTML = '<p style="color: #666;">Không tải được danh sách thẻ.</p>';
                        return;
                    }

                    const html = data.data.map(card => `
                        <div class="word-suggestion">
                            <div class="word-info">
                                <div class="word-name">${card.word}</div>
                                <div class="word-meaning">${card.definition}</div>
                                ${card.example ? `<div class="word-example">${card.example}</div>` : ''}
                            </div>
                            <button class="btn btn-secondary" onclick="deleteFlashcard(${card.id})">Xóa</button>
                        </div>
                    `).join('');

                    document.getElementById('card-list').innerHTML = html || '<p style="color: #666;">Chưa có thẻ nào.</p>';
                })
                .catch(err => {
                    console.error('Error loading flashcards:', err);
                    document.getElementById('card-list').innerHTML = '<p style="color: #666;">Có lỗi xảy ra khi tải danh sách thẻ.</p>';
                });
        }

        function deleteFlashcard(id) {
            if (!confirm('Bạn có chắc muốn xóa thẻ này?')) return;

            fetch('controllers/flashcards.php?action=delete_flashcard', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ flashcard_id: id })
            })
            .then(res => res.json())
            .then(data => {
                showSnackbar(data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    loadFlashcards();
                }
            })
            .catch(err => {
                console.error('Error deleting flashcard:', err);
                showSnackbar('Có lỗi xảy ra khi xóa thẻ', 'error');
            });
        }

        function loadStudyQueue() {
            if (!selectedDeckId) return;

            studyIndex = 0;
            showingBack = false;
            studyQueue = [];

            fetch(`controllers/flashcards.php?action=study_queue&deck_id=${selectedDeckId}&limit=50`, { credentials: 'same-origin' })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        studyQueue = data.data;
                        renderStudyCard();
                    }
                })
                .catch(err => {
                    console.error('Error loading study queue:', err);
                });
        }

        function renderStudyCard() {
            const el = document.getElementById('study-card');
            const content = el.querySelector('.study-card-content');

            if (!studyQueue.length) {
                content.innerHTML = '<div style="color: #666;"><i class="fas fa-info-circle"></i><br>Không còn thẻ để học.</div>';
                return;
            }

            const cur = studyQueue[studyIndex % studyQueue.length];
            const progress = `${studyIndex + 1}/${studyQueue.length}`;

            if (showingBack) {
                content.innerHTML = `
                    <div style="margin-bottom: 1rem; font-size: 0.9rem; opacity: 0.8;">Định nghĩa</div>
                    <div style="font-size: 1.1rem; line-height: 1.4;">${cur.definition || 'Không có định nghĩa'}</div>
                    ${cur.example ? `<div style="margin-top: 1rem; font-size: 0.9rem; font-style: italic; opacity: 0.9;">Ví dụ: ${cur.example}</div>` : ''}
                    <div style="margin-top: 1.5rem; font-size: 0.8rem; opacity: 0.7;">${progress}</div>
                `;
            } else {
                content.innerHTML = `
                    <div style="margin-bottom: 1rem; font-size: 0.9rem; opacity: 0.8;">Từ vựng</div>
                    <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem;">${cur.word || 'Không có từ'}</div>
                    <div style="font-size: 0.9rem; opacity: 0.7;">Nhấn để xem định nghĩa</div>
                    <div style="margin-top: 1.5rem; font-size: 0.8rem; opacity: 0.7;">${progress}</div>
                `;
            }
        }

        function flipCard() {
            showingBack = !showingBack;
            const card = document.getElementById('study-card');

            // Add flip animation
            card.style.transform = 'rotateY(180deg)';
            setTimeout(() => {
                renderStudyCard();
                card.style.transform = 'rotateY(0deg)';

                // Add flipped class for styling
                if (showingBack) {
                    card.classList.add('flipped');
                } else {
                    card.classList.remove('flipped');
                }
            }, 150);
        }

        function rateCard(rating) {
            if (!studyQueue.length) return;

            const cur = studyQueue[studyIndex % studyQueue.length];
            fetch('controllers/flashcards.php?action=review', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ flashcard_id: cur.id, rating })
            })
            .then(res => res.json())
            .then(data => {
                showSnackbar(data.message, data.success ? 'success' : 'error');
                studyIndex++;
                showingBack = false;
                loadStudyQueue();
            })
            .catch(err => {
                console.error('Error rating card:', err);
                showSnackbar('Có lỗi xảy ra khi đánh giá thẻ', 'error');
            });
        }

        function searchMyFlashcards() {
            const q = document.getElementById('my-search').value.trim();

            if (!q) {
                document.getElementById('my-search-results').innerHTML = '';
                return;
            }

            fetch('controllers/flashcards.php?action=search_my', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ q })
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    document.getElementById('my-search-results').innerHTML = '<p style="color: #666;">Lỗi tìm kiếm</p>';
                    return;
                }

                const html = data.data.map(r => `
                    <div class="word-suggestion">
                        <div class="word-info">
                            <div class="word-name">${r.word}</div>
                            <div class="word-meaning">${r.definition}</div>
                            <div class="word-source">${r.deck_name}</div>
                        </div>
                    </div>
                `).join('');

                document.getElementById('my-search-results').innerHTML = html || '<p style="color: #666;">Không tìm thấy.</p>';

                // If no results found, search dictionary
                if (data.data.length === 0) {
                    searchDictionaryFallback(q);
                } else {
                    document.getElementById('dictionary-search-section').style.display = 'none';
                }
            })
            .catch(err => {
                console.error('Error searching flashcards:', err);
                document.getElementById('my-search-results').innerHTML = '<p style="color: #666;">Có lỗi xảy ra khi tìm kiếm</p>';
            });
        }

        function searchDictionaryFallback(query) {
            document.getElementById('dictionary-search-section').style.display = 'block';
            document.getElementById('dictionary-search-results').innerHTML = '<p>Đang tìm kiếm trong từ điển...</p>';

            fetch(`controllers/flashcards.php?action=search_all&q=${encodeURIComponent(query)}`, { credentials: 'same-origin' })
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.data.dictionary.length > 0) {
                        const html = data.data.dictionary.map(word => `
                            <div class="word-suggestion">
                                <div class="word-info">
                                    <div class="word-name">${word.word}</div>
                                    <div class="word-meaning">${word.vietnamese}</div>
                                    ${word.english_definition ? `<div class="word-meaning" style="color: #555;">${word.english_definition}</div>` : ''}
                                </div>
                                <button class="btn btn-primary" onclick="addWordToDeck('${word.word}', '${word.vietnamese}', '${word.english_definition || ''}', ${word.id})">
                                    Thêm vào bộ thẻ
                                </button>
                            </div>
                        `).join('');
                        document.getElementById('dictionary-search-results').innerHTML = html;
                    } else {
                        document.getElementById('dictionary-search-results').innerHTML = '<p style="color: #666;">Không tìm thấy từ nào trong từ điển.</p>';
                    }
                })
                .catch(err => {
                    console.error('Error searching dictionary:', err);
                    document.getElementById('dictionary-search-results').innerHTML = '<p style="color: red;">Có lỗi xảy ra khi tìm kiếm từ điển.</p>';
                });
        }

        function addWordToDeck(word, vietnamese, englishDef, dictionaryId) {
            if (!selectedDeckId) {
                showSnackbar('Vui lòng chọn bộ thẻ trước', 'error');
                return;
            }

            const definition = vietnamese + (englishDef ? ` (${englishDef})` : '');

            fetch('controllers/flashcards.php?action=create_flashcard', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({
                    deck_id: selectedDeckId,
                    word: word,
                    definition: definition,
                    example: '',
                    source_dictionary_id: dictionaryId
                })
            })
            .then(res => res.json())
            .then(data => {
                showSnackbar(data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    loadFlashcards();
                }
            })
            .catch(err => {
                console.error('Error adding word:', err);
                showSnackbar('Có lỗi xảy ra khi thêm từ', 'error');
            });
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadDecks();
        });
        <?php endif; ?>
    </script>
</body>
</html>
