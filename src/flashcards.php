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
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
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
                        <li><a href="flashcards.php">Flashcards</a></li>
                        <li><a href="stats.php">Thống kê</a></li>
                    </ul>
                </nav>
                <div class="user-info">
                    <?php if ($isLoggedIn): ?>
                        <span>Xin chào, <?php echo htmlspecialchars($username); ?></span>
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
                <h1><i class="fas fa-layer-group"></i> Flashcards</h1>
                <p>Tạo và quản lý thẻ học từ vựng của bạn</p>
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
                            placeholder="Dán văn bản tiếng Anh vào đây để trích xuất từ vựng...&#10;&#10;Ví dụ: 'The quick brown fox jumps over the lazy dog. This sentence contains many common English words that can be extracted and added to your flashcard collection.'"
                        ></textarea>
                    </div>
                    <div class="import-controls">
                        <button id="extract-btn" class="import-btn" onclick="extractWords()">
                            <i class="fas fa-magic"></i> Trích xuất từ vựng
                        </button>
                        <label>
                            Độ dài tối thiểu: 
                            <select id="min-length" style="padding: 0.5rem; border-radius: 5px; border: 1px solid #ddd;">
                                <option value="3">3 ký tự</option>
                                <option value="4" selected>4 ký tự</option>
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
                    <h4>Tạo bộ thẻ mới</h4>
                    <div class="import-controls">
                        <input id="deck-name" class="import-textarea" style="min-height: auto; padding: 0.75rem;" placeholder="Tên bộ thẻ" />
                        <input id="deck-desc" class="import-textarea" style="min-height: auto; padding: 0.75rem;" placeholder="Mô tả (tuỳ chọn)" />
                        <select id="deck-visibility" class="import-textarea" style="min-height: auto; padding: 0.75rem;">
                            <option value="private">Riêng tư</option>
                            <option value="public">Công khai</option>
                        </select>
                        <button class="import-btn" onclick="createDeck()">
                            <i class="fas fa-plus"></i> Tạo bộ thẻ
                        </button>
                    </div>
                </div>

                <!-- My Decks -->
                <div class="results-container">
                    <h4>Bộ thẻ của tôi</h4>
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
                        <button class="import-btn" onclick="createFlashcard()">
                            <i class="fas fa-plus"></i> Thêm thẻ
                        </button>
                    </div>
                    <div id="card-list" style="margin-top: 1rem;">
                        <!-- Cards will be loaded here -->
                    </div>
                </div>

                <!-- Study Mode -->
                <div id="study-panel" class="results-container" style="display:none;">
                    <h4>Chế độ học</h4>
                    <div id="study-card" style="background: #f8f9fa; border: 2px solid #e1e5e9; border-radius: 12px; padding: 2rem; cursor: pointer; min-height: 120px; display: flex; align-items: center; justify-content: center; font-weight: 600; text-align: center;" onclick="flipCard()">
                        Nhấn để lật thẻ
                    </div>
                    <div style="display: flex; gap: 0.5rem; margin-top: 1rem; flex-wrap: wrap;">
                        <button class="btn btn-secondary" onclick="rateCard('again')">Lại</button>
                        <button class="btn" style="background: #ffc107; color: white;" onclick="rateCard('hard')">Khó nhớ</button>
                        <button class="btn btn-primary" onclick="rateCard('good')">Nhớ tốt</button>
                        <button class="btn" style="background: #28a745; color: white;" onclick="rateCard('easy')">Rất dễ</button>
                    </div>
                </div>

                <!-- Search Flashcards -->
                <div class="results-container">
                    <h4>Tìm kiếm trong bộ thẻ của tôi</h4>
                    <div class="import-controls">
                        <input id="my-search" class="import-textarea" style="min-height: auto; padding: 0.75rem;" placeholder="Nhập từ khoá" />
                        <button class="import-btn" onclick="searchMyFlashcards()">
                            <i class="fas fa-search"></i> Tìm kiếm
                        </button>
                    </div>
                    <div id="my-search-results" style="margin-top: 1rem;">
                        <!-- Search results will be displayed here -->
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

            fetch('controllers/flashcards.php?action=extract_words', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ text: text, min_length: minLength })
            })
            .then(res => res.json())
            .then(data => {
                loading.style.display = 'none';
                extractBtn.disabled = false;
                extractBtn.innerHTML = '<i class="fas fa-magic"></i> Trích xuất từ vựng';

                if (data.success && data.data.length > 0) {
                    displayExtractResults(data.data);
                } else {
                    results.innerHTML = '<p style="text-align: center; color: #666; padding: 2rem;">Không tìm thấy từ vựng phù hợp.</p>';
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

            // For now, just show success message
            // In a real implementation, this would open a modal to select deck
            showSnackbar(`Đã chọn ${checkboxes.length} từ để thêm vào bộ thẻ`, 'success');
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
                        <div class="word-suggestion">
                            <div class="word-info">
                                <div class="word-name">${deck.name}</div>
                                <div class="word-meaning">${deck.description || 'Không có mô tả'}</div>
                                <div class="word-source">${deck.visibility === 'public' ? 'Công khai' : 'Riêng tư'}</div>
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                <button class="btn btn-primary" onclick="selectDeck(${deck.id}, '${deck.name.replace(/'/g, "\\'")}')">Chọn</button>
                                <button class="btn btn-secondary" onclick="deleteDeck(${deck.id})">Xóa</button>
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
            loadFlashcards();
            loadStudyQueue();
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
            if (!studyQueue.length) {
                el.textContent = 'Không còn thẻ để học.';
                return;
            }
            const cur = studyQueue[studyIndex % studyQueue.length];
            el.textContent = showingBack ? (cur.definition || '') : (cur.word || '');
        }

        function flipCard() {
            showingBack = !showingBack;
            renderStudyCard();
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
            })
            .catch(err => {
                console.error('Error searching flashcards:', err);
                document.getElementById('my-search-results').innerHTML = '<p style="color: #666;">Có lỗi xảy ra khi tìm kiếm</p>';
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
