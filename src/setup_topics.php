<?php
// Script để thiết lập hệ thống topics
require_once 'services/database.php';

echo "<h2>Thiết lập hệ thống Topics</h2>";

try {
    // Đọc và thực thi file SQL chính
    echo "<p>Đang tạo bảng topics...</p>";
    $sql = file_get_contents('updates/2025_08_17_topic_system.sql');

    // Tách các câu lệnh SQL
    $statements = explode(';', $sql);

    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            if ($conn->query($statement)) {
                echo "<p style='color: green;'>✓ Thực thi thành công: " . substr($statement, 0, 50) . "...</p>";
            } else {
                echo "<p style='color: red;'>✗ Lỗi: " . $conn->error . "</p>";
                echo "<p>Câu lệnh: " . $statement . "</p>";
            }
        }
    }

    // Đọc và thực thi file dữ liệu mẫu
    echo "<p>Đang thêm dữ liệu mẫu...</p>";
    $sampleData = file_get_contents('updates/2025_08_17_topic_sample_data.sql');

    $sampleStatements = explode(';', $sampleData);

    foreach ($sampleStatements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            if ($conn->query($statement)) {
                echo "<p style='color: green;'>✓ Thêm dữ liệu thành công: " . substr($statement, 0, 50) . "...</p>";
            } else {
                echo "<p style='color: orange;'>⚠ Có thể đã tồn tại: " . $conn->error . "</p>";
            }
        }
    }

    echo "<h3 style='color: green;'>✅ Hoàn thành thiết lập hệ thống Topics!</h3>";
    echo "<p><a href='topics.php'>Truy cập trang Topics</a></p>";
    echo "<p><a href='index.php'>Quay lại trang chủ</a></p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>Lỗi: " . $e->getMessage() . "</p>";
}
?>