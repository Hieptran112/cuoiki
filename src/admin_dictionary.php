<?php
session_start();
$isLoggedIn = isset($_SESSION['username']); 
$username = $isLoggedIn ? $_SESSION['username'] : null;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Quản lý từ điển</title>
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

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        /* Main Content */
        main {
            padding: 2rem 0;
        }

        .admin-section {
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

        /* Form Styles */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
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

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        /* Table Styles */
        .table-container {
            overflow-x: auto;
            margin-top: 2rem;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .admin-table th,
        .admin-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e1e5e9;
        }

        .admin-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        .admin-table tr:hover {
            background: #f8f9fa;
        }

        .difficulty-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
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

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #666;
            font-weight: 500;
        }

        /* Search and Filter */
        .search-filter {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .search-input {
            flex: 1;
            min-width: 200px;
            padding: 0.75rem;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
        }

        .search-input:focus {
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

            .form-grid {
                grid-template-columns: 1fr;
            }

            .search-filter {
                flex-direction: column;
            }

            .action-buttons {
                flex-direction: column;
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

        /* Success/Error Messages */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Back Button Styling */
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
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            z-index: 1000;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
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

    <header>
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">
                    <i class="fas fa-book-open"></i> SmartDictionary Admin
                </a>
                <nav>
                    <ul class="nav-menu">
                        <li><a href="index.php">Trang chủ</a></li>
                        <li><a href="#add-word">Thêm từ</a></li>
                        <li><a href="#manage-words">Quản lý từ</a></li>
                    </ul>
                </nav>
                <div class="user-info">
                    <?php if ($isLoggedIn): ?>
                        <span>Xin chào, <?php echo htmlspecialchars($username); ?></span>
                        <a href="controllers/logout.php" class="btn btn-secondary">Đăng xuất</a>
                    <?php else: ?>
                        <a href="index.php" class="btn btn-primary">Về trang chủ</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <!-- Stats Section -->
            <section class="admin-section">
                <h2 class="section-title">
                    <i class="fas fa-chart-bar"></i>
                    Thống kê từ điển
                </h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number" id="totalWords">0</div>
                        <div class="stat-label">Tổng số từ</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="beginnerWords">0</div>
                        <div class="stat-label">Từ cơ bản</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="intermediateWords">0</div>
                        <div class="stat-label">Từ trung cấp</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="advancedWords">0</div>
                        <div class="stat-label">Từ nâng cao</div>
                    </div>
                </div>
                <div style="margin-top: 1rem; text-align: center;">
                    <button onclick="updateTrigger()" class="btn btn-warning">
                        <i class="fas fa-sync-alt"></i> Cập nhật Trigger Thống kê
                    </button>
                </div>
            </section>

            <!-- Add Word Section -->
            <section id="add-word" class="admin-section">
                <h2 class="section-title">
                    <i class="fas fa-plus-circle"></i>
                    Thêm từ mới
                </h2>
                
                <div id="alert-message"></div>

                <form id="addWordForm">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="word">Từ tiếng Anh *</label>
                            <input type="text" id="word" name="word" required>
                        </div>
                        <div class="form-group">
                            <label for="phonetic">Phiên âm</label>
                            <input type="text" id="phonetic" name="phonetic" placeholder="/ˈwɜːrd/">
                        </div>
                        <div class="form-group">
                            <label for="part_of_speech">Từ loại</label>
                            <select id="part_of_speech" name="part_of_speech">
                                <option value="noun">Danh từ (noun)</option>
                                <option value="verb">Động từ (verb)</option>
                                <option value="adjective">Tính từ (adjective)</option>
                                <option value="adverb">Trạng từ (adverb)</option>
                                <option value="pronoun">Đại từ (pronoun)</option>
                                <option value="preposition">Giới từ (preposition)</option>
                                <option value="conjunction">Liên từ (conjunction)</option>
                                <option value="interjection">Thán từ (interjection)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="difficulty">Mức độ</label>
                            <select id="difficulty" name="difficulty">
                                <option value="beginner">Cơ bản (beginner)</option>
                                <option value="intermediate">Trung cấp (intermediate)</option>
                                <option value="advanced">Nâng cao (advanced)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="vietnamese">Nghĩa tiếng Việt *</label>
                        <input type="text" id="vietnamese" name="vietnamese" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="english_definition">Định nghĩa tiếng Anh</label>
                        <textarea id="english_definition" name="english_definition" placeholder="English definition..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="example">Ví dụ</label>
                        <textarea id="example" name="example" placeholder="Example sentence..."></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-plus"></i> Thêm từ
                    </button>
                </form>
            </section>

            <!-- Manage Words Section -->
            <section id="manage-words" class="admin-section">
                <h2 class="section-title">
                    <i class="fas fa-list"></i>
                    Quản lý từ điển
                </h2>

                <div class="search-filter">
                    <input type="text" id="searchWord" class="search-input" placeholder="Tìm kiếm từ...">
                    <select id="filterDifficulty" class="search-input">
                        <option value="">Tất cả mức độ</option>
                        <option value="beginner">Cơ bản</option>
                        <option value="intermediate">Trung cấp</option>
                        <option value="advanced">Nâng cao</option>
                    </select>
                    <select id="filterPartOfSpeech" class="search-input">
                        <option value="">Tất cả từ loại</option>
                        <option value="noun">Danh từ</option>
                        <option value="verb">Động từ</option>
                        <option value="adjective">Tính từ</option>
                        <option value="adverb">Trạng từ</option>
                        <option value="pronoun">Đại từ</option>
                        <option value="preposition">Giới từ</option>
                        <option value="conjunction">Liên từ</option>
                        <option value="interjection">Thán từ</option>
                    </select>
                </div>

                <div class="loading" id="loading">
                    <div class="spinner"></div>
                    <p>Đang tải dữ liệu...</p>
                </div>

                <div class="table-container">
                    <table class="admin-table" id="wordsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Từ</th>
                                <th>Phiên âm</th>
                                <th>Nghĩa tiếng Việt</th>
                                <th>Từ loại</th>
                                <th>Mức độ</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="wordsTableBody">
                            <!-- Data will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Bulk import section -->
            <section class="admin-section">
                <h2 class="section-title">
                    <i class="fas fa-file-import"></i>
                    Import nhiều từ (JSON)
                </h2>
                <div class="form-group">
                    <label>Dán dữ liệu JSON (mỗi phần tử: word, vietnamese, english_definition, example?, part_of_speech?, difficulty?)</label>
                    <textarea id="bulk-json" placeholder='[{"word":"example","vietnamese":"ví dụ","english_definition":"a thing to be imitated"}]'></textarea>
                </div>
                <button class="btn btn-success" onclick="bulkImport()"><i class="fas fa-upload"></i> Import</button>
            </section>
        </div>
    </main>

    <script>
        // Load stats
        function loadStats() {
            fetch('controllers/dictionary.php?action=get_stats', {
                method: 'GET'
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('totalWords').textContent = data.data.total || 0;
                    document.getElementById('beginnerWords').textContent = data.data.beginner || 0;
                    document.getElementById('intermediateWords').textContent = data.data.intermediate || 0;
                    document.getElementById('advancedWords').textContent = data.data.advanced || 0;
                }
            })
            .catch(err => console.error('Error loading stats:', err));
        }

        // Load words table
        function loadWords() {
            const loading = document.getElementById('loading');
            const tableBody = document.getElementById('wordsTableBody');
            
            loading.style.display = 'block';
            
            fetch('controllers/dictionary.php?action=get_all_words', {
                method: 'GET'
            })
            .then(res => res.json())
            .then(data => {
                loading.style.display = 'none';
                
                if (data.success && data.data.length > 0) {
                    let html = '';
                    data.data.forEach(word => {
                        html += `
                            <tr>
                                <td>${word.id}</td>
                                <td><strong>${word.word}</strong></td>
                                <td>${word.phonetic || '-'}</td>
                                <td>${word.vietnamese}</td>
                                <td>${word.part_of_speech}</td>
                                <td><span class="difficulty-badge difficulty-${word.difficulty}">${word.difficulty}</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-sm btn-secondary" onclick="editWord(${word.id})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteWord(${word.id})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                    tableBody.innerHTML = html;
                } else {
                    tableBody.innerHTML = '<tr><td colspan="7" style="text-align: center;">Không có dữ liệu</td></tr>';
                }
            })
            .catch(err => {
                loading.style.display = 'none';
                console.error('Error loading words:', err);
                tableBody.innerHTML = '<tr><td colspan="7" style="text-align: center;">Lỗi tải dữ liệu</td></tr>';
            });
        }

        // Add word form
        document.getElementById('addWordForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            fetch('controllers/dictionary.php?action=add_word', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(data => {
                const alertDiv = document.getElementById('alert-message');
                if (data.success) {
                    alertDiv.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                    this.reset();
                    loadStats();
                    loadWords();
                } else {
                    alertDiv.innerHTML = `<div class="alert alert-error">${data.message}</div>`;
                }
                
                setTimeout(() => {
                    alertDiv.innerHTML = '';
                }, 3000);
            })
            .catch(err => {
                console.error('Error adding word:', err);
                document.getElementById('alert-message').innerHTML = '<div class="alert alert-error">Có lỗi xảy ra</div>';
            });
        });

        // Search and filter
        document.getElementById('searchWord').addEventListener('input', function() {
            filterWords();
        });

        document.getElementById('filterDifficulty').addEventListener('change', function() {
            filterWords();
        });

        document.getElementById('filterPartOfSpeech').addEventListener('change', function() {
            filterWords();
        });

        function filterWords() {
            const searchTerm = document.getElementById('searchWord').value.toLowerCase();
            const difficulty = document.getElementById('filterDifficulty').value;
            const partOfSpeech = document.getElementById('filterPartOfSpeech').value;
            
            const rows = document.querySelectorAll('#wordsTableBody tr');
            
            rows.forEach(row => {
                const word = row.cells[1].textContent.toLowerCase();
                const wordDifficulty = row.cells[5].textContent;
                const wordPartOfSpeech = row.cells[4].textContent;
                
                const matchesSearch = word.includes(searchTerm);
                const matchesDifficulty = !difficulty || wordDifficulty.includes(difficulty);
                const matchesPartOfSpeech = !partOfSpeech || wordPartOfSpeech.includes(partOfSpeech);
                
                if (matchesSearch && matchesDifficulty && matchesPartOfSpeech) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Edit word
        function editWord(id) {
            // Implement edit functionality
            alert('Chức năng chỉnh sửa sẽ được phát triển sau');
        }

        // Delete word
        function deleteWord(id) {
            if (confirm('Bạn có chắc chắn muốn xóa từ này?')) {
                fetch(`controllers/dictionary.php?action=delete_word`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        loadStats();
                        loadWords();
                        alert('Xóa từ thành công!');
                    } else {
                        alert('Lỗi: ' + data.message);
                    }
                })
                .catch(err => {
                    console.error('Error deleting word:', err);
                    alert('Có lỗi xảy ra khi xóa từ');
                });
            }
        }

        function bulkImport() {
            let raw = document.getElementById('bulk-json').value.trim();
            if(!raw){ alert('Chưa có dữ liệu'); return; }
            let data;
            try { data = JSON.parse(raw); } catch(e) { alert('JSON không hợp lệ'); return; }
            fetch('controllers/dictionary.php?action=bulk_import', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(data) })
                .then(r=>r.json()).then(d=>{ alert(d.message||'Xong'); if(d.success){ loadStats(); loadWords(); } });
        }

        function updateTrigger() {
            if (!confirm('Bạn có chắc muốn cập nhật trigger thống kê? Điều này sẽ cải thiện cách tính toán thống kê.')) {
                return;
            }

            fetch('controllers/dictionary.php?action=update_trigger', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    loadStats();
                }
            })
            .catch(err => {
                console.error('Error updating trigger:', err);
                alert('Có lỗi xảy ra khi cập nhật trigger');
            });
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadStats();
            loadWords();
        });
    </script>
</body>
</html>
