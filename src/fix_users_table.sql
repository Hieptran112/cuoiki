-- Fix users table structure
-- Sửa lỗi đăng ký: thêm các cột cần thiết

USE eduapp;

-- Thêm cột username nếu chưa có (cho phép NULL để tránh lỗi với dữ liệu cũ)
ALTER TABLE users
ADD COLUMN IF NOT EXISTS username VARCHAR(50) UNIQUE NULL AFTER id;

-- Thêm cột full_name nếu chưa có
ALTER TABLE users
ADD COLUMN IF NOT EXISTS full_name VARCHAR(100) NULL AFTER email;

-- Thêm cột major nếu chưa có
ALTER TABLE users
ADD COLUMN IF NOT EXISTS major VARCHAR(100) NULL AFTER full_name;

-- Hiển thị cấu trúc bảng sau khi sửa
SELECT 'Cấu trúc bảng users sau khi cập nhật:' as message;
DESCRIBE users;

-- Hiển thị số lượng users hiện tại
SELECT COUNT(*) as total_users FROM users;
