// Auth Modal System - Hệ thống modal yêu cầu đăng nhập
// Sử dụng cho tất cả các trang cần kiểm tra đăng nhập

// Tạo modal HTML
function createAuthModal() {
    const modalHTML = `
        <div id="authModal" class="auth-modal" style="display: none;">
            <div class="auth-modal-content">
                <div class="auth-modal-header">
                    <i class="fas fa-lock" style="color: #e74c3c; font-size: 2rem; margin-bottom: 1rem;"></i>
                    <h3>Yêu cầu đăng nhập</h3>
                </div>
                <div class="auth-modal-body">
                    <p>Bạn cần đăng nhập để sử dụng tính năng này.</p>
                </div>
                <div class="auth-modal-footer">
                    <button class="auth-btn auth-btn-primary" onclick="goToLogin()">
                        <i class="fas fa-sign-in-alt"></i> Đăng nhập ngay
                    </button>
                    <button class="auth-btn auth-btn-secondary" onclick="closeAuthModal()">
                        <i class="fas fa-times"></i> Hủy
                    </button>
                </div>
            </div>
        </div>
    `;

    // Thêm CSS nếu chưa có
    if (!document.getElementById('authModalCSS')) {
        const style = document.createElement('style');
        style.id = 'authModalCSS';
        style.textContent = `
            .auth-modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.6);
                z-index: 10000;
                display: flex;
                align-items: center;
                justify-content: center;
                backdrop-filter: blur(5px);
            }

            .auth-modal-content {
                background: white;
                border-radius: 15px;
                padding: 2rem;
                max-width: 400px;
                width: 90%;
                text-align: center;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
                animation: authModalSlideIn 0.3s ease-out;
            }

            @keyframes authModalSlideIn {
                from {
                    opacity: 0;
                    transform: translateY(-50px) scale(0.9);
                }
                to {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }

            .auth-modal-header h3 {
                color: #2c3e50;
                margin: 0;
                font-size: 1.5rem;
                font-weight: 600;
            }

            .auth-modal-body p {
                color: #7f8c8d;
                margin: 1.5rem 0;
                font-size: 1.1rem;
                line-height: 1.6;
            }

            .auth-modal-footer {
                display: flex;
                gap: 1rem;
                justify-content: center;
                flex-wrap: wrap;
            }

            .auth-btn {
                padding: 0.75rem 1.5rem;
                border: none;
                border-radius: 10px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                font-size: 1rem;
                min-width: 140px;
                justify-content: center;
            }

            .auth-btn-primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            }

            .auth-btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
                background: linear-gradient(135deg, #5a6fd8 0%, #6a4c93 100%);
            }

            .auth-btn-secondary {
                background: #95a5a6;
                color: white;
            }

            .auth-btn-secondary:hover {
                background: #7f8c8d;
                transform: translateY(-2px);
            }

            @media (max-width: 480px) {
                .auth-modal-content {
                    padding: 1.5rem;
                    margin: 1rem;
                }

                .auth-modal-footer {
                    flex-direction: column;
                }

                .auth-btn {
                    width: 100%;
                }
            }
        `;
        document.head.appendChild(style);
    }

    // Thêm modal vào body nếu chưa có
    if (!document.getElementById('authModal')) {
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }
}

// Hiển thị modal yêu cầu đăng nhập
function showAuthModal(message = 'Bạn cần đăng nhập để sử dụng tính năng này.') {
    createAuthModal();
    const modal = document.getElementById('authModal');
    const messageElement = modal.querySelector('.auth-modal-body p');
    messageElement.textContent = message;
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden'; // Ngăn scroll
}

// Đóng modal
function closeAuthModal() {
    const modal = document.getElementById('authModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto'; // Cho phép scroll lại
    }
}

// Chuyển đến trang đăng nhập
function goToLogin() {
    window.location.href = 'index.php';
}

// Kiểm tra đăng nhập và hiển thị modal nếu cần
function requireLogin(callback, message = 'Bạn cần đăng nhập để sử dụng tính năng này.') {
    // Kiểm tra xem user đã đăng nhập chưa (được set từ PHP)
    if (typeof isUserLoggedIn !== 'undefined' && isUserLoggedIn) {
        // Đã đăng nhập, thực hiện callback
        if (typeof callback === 'function') {
            callback();
        }
    } else {
        // Chưa đăng nhập, hiển thị modal
        showAuthModal(message);
    }
}

// Đóng modal khi click outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('authModal');
    if (modal && event.target === modal) {
        closeAuthModal();
    }
});

// Đóng modal khi nhấn ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeAuthModal();
    }
});