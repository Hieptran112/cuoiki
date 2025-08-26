# 📚 English Dictionary Setup for Text Extraction

## 🎯 Mục đích
Các file SQL này được tạo để thêm từ vựng tiếng Anh vào database, giúp chức năng "Trích xuất từ vựng" hoạt động hiệu quả với nhiều loại văn bản khác nhau.

## 📁 Các file SQL có sẵn

### 1. `setup_complete_dictionary.sql` ⭐ **KHUYẾN NGHỊ**
- **File chính** - chứa tất cả từ vựng cần thiết
- **300+ từ vựng** cơ bản và quan trọng nhất
- **Đầy đủ các loại từ:** động từ, danh từ, tính từ, đại từ
- **Tối ưu cho text extraction**

### 2. `add_vocabulary_dictionary.sql`
- **150+ động từ** phổ biến và cần thiết
- **Danh từ cơ bản:** người, địa điểm, đồ vật
- **Tập trung vào action words**

### 3. `add_advanced_vocabulary.sql`
- **200+ từ vựng nâng cao**
- **Tính từ, màu sắc, động vật, thực phẩm**
- **Số đếm, thời gian, thiên nhiên**

## 🚀 Cách sử dụng

### Option 1: Setup Complete (Khuyến nghị)
```bash
# Chạy file chính - đầy đủ nhất
mysql -u username -p database_name < src/setup_complete_dictionary.sql
```

### Option 2: Setup từng phần
```bash
# Bước 1: Thêm từ vựng cơ bản
mysql -u username -p database_name < src/add_vocabulary_dictionary.sql

# Bước 2: Thêm từ vựng nâng cao
mysql -u username -p database_name < src/add_advanced_vocabulary.sql
```

### Option 3: Sử dụng phpMyAdmin
1. Mở phpMyAdmin
2. Chọn database của bạn
3. Vào tab "SQL"
4. Copy nội dung file SQL và paste vào
5. Click "Go" để thực thi

## 📊 Thống kê từ vựng

### Sau khi chạy `setup_complete_dictionary.sql`:
- **300+ từ vựng** tổng cộng
- **100+ động từ** (verbs) - go, come, see, eat, etc.
- **80+ danh từ** (nouns) - man, house, book, etc.
- **50+ tính từ** (adjectives) - good, big, beautiful, etc.
- **20+ đại từ** (pronouns) - I, you, he, she, etc.
- **Các từ loại khác:** adverbs, numbers, colors

### Phân loại theo độ khó:
- **Beginner:** 280+ từ (cơ bản nhất)
- **Intermediate:** 20+ từ (trung bình)
- **Advanced:** 10+ từ (nâng cao)

## 🧪 Test chức năng

### Sau khi setup, test với các câu:
```
✅ "I am Long" → I, am, Long
✅ "My family has four people" → family, four, people  
✅ "I like to read books" → like, read, books
✅ "The weather is beautiful today" → weather, beautiful, today
✅ "I work hard every day" → work, hard, every, day
✅ "She is a good teacher" → good, teacher
✅ "We eat rice and fish" → eat, rice, fish
✅ "The cat is sleeping" → cat, sleeping
✅ "I want to buy a new car" → want, buy, new, car
✅ "Children play in the park" → children, play, park
```

### Test với script:
```
http://localhost/your-project/src/test_comprehensive_extraction.php
```

## 🔧 Tùy chỉnh

### Thêm từ vựng riêng:
```sql
INSERT IGNORE INTO dictionary (word, vietnamese, english_definition, part_of_speech, difficulty) VALUES
('your_word', 'nghĩa tiếng Việt', 'English definition', 'noun', 'beginner');
```

### Xóa từ không cần thiết:
```sql
DELETE FROM dictionary WHERE word = 'unwanted_word';
```

### Cập nhật từ vựng:
```sql
UPDATE dictionary 
SET vietnamese = 'nghĩa mới', english_definition = 'new definition'
WHERE word = 'word_to_update';
```

## 📈 Kết quả mong đợi

### Trước khi setup:
- ❌ "I am Long" → Không tìm thấy từ nào
- ❌ "I like books" → Không có kết quả
- ❌ Hầu hết văn bản → Trống

### Sau khi setup:
- ✅ **Bất kỳ văn bản tiếng Anh nào** đều có thể extract được từ vựng
- ✅ **Tỷ lệ thành công cao** với văn bản thông thường
- ✅ **Hỗ trợ đa dạng chủ đề:** gia đình, công việc, học tập, du lịch, etc.

## 🎯 Lưu ý quan trọng

### ✅ Nên làm:
- Chạy `setup_complete_dictionary.sql` trước tiên
- Test với văn bản đơn giản trước
- Sử dụng "1 ký tự" cho min_length để bắt tất cả từ
- Backup database trước khi chạy script

### ❌ Không nên:
- Chạy nhiều script cùng lúc
- Xóa toàn bộ dictionary table
- Thay đổi cấu trúc bảng dictionary

## 🆘 Troubleshooting

### Vấn đề: "Table 'dictionary' doesn't exist"
```sql
-- Chạy lệnh này trước:
CREATE TABLE dictionary (
    id INT AUTO_INCREMENT PRIMARY KEY,
    word VARCHAR(255) NOT NULL UNIQUE,
    vietnamese TEXT,
    english_definition TEXT,
    part_of_speech VARCHAR(50) DEFAULT 'noun',
    difficulty ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Vấn đề: "Duplicate entry"
- Bình thường! Script sử dụng `INSERT IGNORE` để tránh trùng lặp
- Từ đã tồn tại sẽ được bỏ qua

### Vấn đề: Vẫn không extract được
1. Kiểm tra database có dữ liệu: `SELECT COUNT(*) FROM dictionary;`
2. Test với script: `test_comprehensive_extraction.php`
3. Kiểm tra console browser có lỗi JavaScript không

## 📞 Hỗ trợ

Nếu gặp vấn đề, hãy:
1. Kiểm tra log MySQL/PHP
2. Test với script debug
3. Xem console browser (F12)
4. Kiểm tra kết nối database

---

**🎉 Chúc bạn thành công với việc setup dictionary!**
