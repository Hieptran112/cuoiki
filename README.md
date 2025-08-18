# SmartDictionary - Hệ thống từ điển thông minh

## 📖 Mô tả

SmartDictionary là một ứng dụng web từ điển thông minh được xây dựng bằng PHP, MySQL và JavaScript. Ứng dụng cung cấp các tính năng tra cứu từ điển, bài tập hàng ngày và quản lý dữ liệu từ vựng.

## ✨ Tính năng chính

### 🎯 Cho người dùng
- **Tra cứu từ điển**: Tìm kiếm từ vựng tiếng Anh với định nghĩa tiếng Việt
- **Phát âm**: Nghe phát âm từ vựng bằng text-to-speech
- **Bài tập hàng ngày**: Luyện tập với các câu hỏi trắc nghiệm
- **Thống kê học tập**: Theo dõi tiến độ học tập
- **Giao diện đẹp**: Thiết kế hiện đại, responsive

### 🔧 Cho quản trị viên
- **Quản lý từ điển**: Thêm, sửa, xóa từ vựng
- **Thống kê**: Xem số liệu về từ điển
- **Tìm kiếm và lọc**: Tìm kiếm từ theo nhiều tiêu chí
- **Import dữ liệu**: Thêm nhiều từ cùng lúc

## 🚀 Cài đặt

### Yêu cầu hệ thống
- PHP 7.4 trở lên
- MySQL 5.7 trở lên
- Web server (Apache/Nginx)

### Bước 1: Clone repository
```bash
git clone <repository-url>
cd fe-education-application
```

### Bước 2: Cấu hình database
1. Tạo database MySQL mới
2. Import file `src/database_setup.sql` để tạo bảng và dữ liệu mẫu
3. Cập nhật thông tin kết nối trong `src/services/database.php`

### Bước 3: Cấu hình web server
Đặt thư mục `src` làm document root của web server.

### Bước 4: Thêm dữ liệu từ điển (tùy chọn)
Import file `src/add_more_words.sql` để thêm nhiều từ vựng hơn.

## 📁 Cấu trúc thư mục

```
src/
├── index.php                 # Trang chủ - giao diện người dùng
├── admin_dictionary.php      # Trang quản trị từ điển
├── controllers/              # Controllers xử lý logic
│   ├── login.php            # Xử lý đăng nhập
│   ├── register.php         # Xử lý đăng ký
│   ├── logout.php           # Xử lý đăng xuất
│   └── dictionary.php       # API từ điển
├── services/                 # Services
│   └── database.php         # Kết nối database
├── database_setup.sql       # Script tạo database
├── add_more_words.sql       # Dữ liệu từ điển bổ sung
└── style.css                # CSS styles
```

## 🎮 Cách sử dụng

### Cho người dùng

1. **Truy cập trang chủ**: Mở `index.php` trong trình duyệt
2. **Tra cứu từ điển**:
   - Nhập từ cần tra cứu vào ô tìm kiếm
   - Nhấn Enter hoặc click nút "Tìm kiếm"
   - Xem kết quả với định nghĩa, ví dụ và phát âm
3. **Làm bài tập**:
   - Cuộn xuống phần "Bài tập hàng ngày"
   - Chọn đáp án và nhấn "Kiểm tra"
   - Xem kết quả và thống kê
4. **Đăng ký/Đăng nhập**: Để lưu tiến độ học tập

### Cho quản trị viên

1. **Truy cập trang admin**: Mở `admin_dictionary.php`
2. **Thêm từ mới**:
   - Điền thông tin từ vựng
   - Chọn từ loại và mức độ
   - Nhấn "Thêm từ"
3. **Quản lý từ điển**:
   - Xem danh sách tất cả từ
   - Tìm kiếm và lọc theo tiêu chí
   - Xóa từ không cần thiết
4. **Xem thống kê**: Theo dõi số lượng từ theo mức độ

## 🔧 API Endpoints

### Từ điển
- `GET controllers/dictionary.php?action=search` - Tìm kiếm từ
- `POST controllers/dictionary.php?action=add_word` - Thêm từ mới
- `GET controllers/dictionary.php?action=get_daily_exercises` - Lấy bài tập
- `POST controllers/dictionary.php?action=submit_exercise` - Nộp bài tập
- `GET controllers/dictionary.php?action=get_stats` - Lấy thống kê
- `GET controllers/dictionary.php?action=get_all_words` - Lấy tất cả từ
- `POST controllers/dictionary.php?action=delete_word` - Xóa từ

### Xác thực
- `POST controllers/login.php` - Đăng nhập
- `POST controllers/register.php` - Đăng ký
- `GET controllers/logout.php` - Đăng xuất

## 🎨 Tùy chỉnh giao diện

### Thay đổi màu sắc
Chỉnh sửa biến CSS trong file `index.php`:
```css
:root {
    --primary-color: #667eea;
    --secondary-color: #764ba2;
    --success-color: #28a745;
    --danger-color: #dc3545;
}
```

### Thêm tính năng mới
1. Tạo controller mới trong thư mục `controllers/`
2. Thêm route trong `dictionary.php`
3. Cập nhật giao diện trong `index.php` hoặc `admin_dictionary.php`

## 📊 Cấu trúc Database

### Bảng `users`
- `id`: ID người dùng
- `username`: Tên đăng nhập
- `email`: Email
- `password`: Mật khẩu (đã mã hóa)
- `created_at`: Thời gian tạo

### Bảng `dictionary`
- `id`: ID từ
- `word`: Từ tiếng Anh
- `phonetic`: Phiên âm
- `vietnamese`: Nghĩa tiếng Việt
- `english_definition`: Định nghĩa tiếng Anh
- `example`: Ví dụ
- `part_of_speech`: Từ loại
- `difficulty`: Mức độ (beginner/intermediate/advanced)
- `created_at`: Thời gian tạo

### Bảng `exercise_results`
- `id`: ID kết quả
- `user_id`: ID người dùng
- `exercise_id`: ID bài tập
- `selected_answer`: Đáp án đã chọn
- `correct_answer`: Đáp án đúng
- `is_correct`: Đúng/sai
- `submitted_at`: Thời gian nộp

### Bảng `learning_stats`
- `id`: ID thống kê
- `user_id`: ID người dùng
- `words_learned`: Số từ đã học
- `correct_answers`: Số câu trả lời đúng
- `total_answers`: Tổng số câu trả lời
- `streak_days`: Số ngày liên tiếp học
- `last_study_date`: Ngày học cuối cùng

## 🚀 Tính năng nâng cao

### Tích hợp API từ điển bên ngoài
Có thể tích hợp với các API như:
- Oxford Dictionary API
- Merriam-Webster API
- Free Dictionary API

### Hệ thống gợi ý từ
- Gợi ý từ tương tự
- Từ đồng nghĩa/trái nghĩa
- Từ vựng theo chủ đề

### Hệ thống đánh giá
- Đánh giá độ khó của từ
- Đánh giá chất lượng định nghĩa
- Báo cáo lỗi

## 🐛 Xử lý lỗi thường gặp

### Lỗi kết nối database
- Kiểm tra thông tin kết nối trong `database.php`
- Đảm bảo MySQL service đang chạy
- Kiểm tra quyền truy cập database

### Lỗi hiển thị tiếng Việt
- Đảm bảo database sử dụng charset `utf8mb4`
- Kiểm tra header Content-Type
- Cập nhật file `database.php` với `set_charset("utf8mb4")`

### Lỗi API không hoạt động
- Kiểm tra đường dẫn file
- Đảm bảo PHP có quyền đọc file
- Kiểm tra lỗi trong console trình duyệt

## 📝 Ghi chú phát triển

### Cải thiện hiệu suất
- Thêm cache cho kết quả tìm kiếm
- Tối ưu hóa query database
- Sử dụng CDN cho thư viện bên ngoài

### Bảo mật
- Validate input data
- Sử dụng prepared statements
- Mã hóa mật khẩu
- Bảo vệ chống SQL injection

### Mở rộng
- Thêm nhiều ngôn ngữ
- Tích hợp AI để gợi ý từ
- Hệ thống học tập thông minh
- Mobile app

## 📞 Hỗ trợ

Nếu gặp vấn đề hoặc có câu hỏi, vui lòng:
1. Kiểm tra phần "Xử lý lỗi thường gặp"
2. Xem log lỗi trong console trình duyệt
3. Kiểm tra log PHP error

## 📄 License

Dự án này được phát hành dưới MIT License.

---

**SmartDictionary** - Học từ vựng thông minh, hiệu quả! 🎓📚
