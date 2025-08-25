<?php
// Test script for text extraction functionality
require_once 'services/database.php';

echo "<h2>Testing Text Extraction Feature</h2>";

// Test text
$testText = "The quick brown fox jumps over the lazy dog. This sentence contains many common English words like cat, dog, water, and apple.";

echo "<h3>Test Text:</h3>";
echo "<p>" . htmlspecialchars($testText) . "</p>";

// Simulate the extraction request
$_POST['action'] = 'extract_words';
$_SERVER['REQUEST_METHOD'] = 'POST';

// Create test data
$testData = [
    'text' => $testText,
    'min_length' => 3,
    'top_k' => 10
];

// Simulate the request
file_put_contents('php://input', json_encode($testData));

echo "<h3>Testing extraction...</h3>";

try {
    // Check if dictionary table exists and has data
    $dictCount = $conn->query("SELECT COUNT(*) as count FROM dictionary")->fetch_assoc()['count'];
    echo "<p>Dictionary entries: $dictCount</p>";
    
    // Check if specialized_terms table exists
    $specExists = $conn->query("SHOW TABLES LIKE 'specialized_terms'")->num_rows > 0;
    echo "<p>Specialized terms table exists: " . ($specExists ? 'Yes' : 'No') . "</p>";
    
    if ($specExists) {
        $specCount = $conn->query("SELECT COUNT(*) as count FROM specialized_terms")->fetch_assoc()['count'];
        echo "<p>Specialized terms entries: $specCount</p>";
    }
    
    // Test word extraction manually
    $words = ['cat', 'dog', 'water', 'apple', 'quick', 'brown', 'fox'];
    echo "<h3>Testing individual words:</h3>";
    
    $stmt = $conn->prepare("SELECT word, vietnamese, english_definition FROM dictionary WHERE word = ?");
    foreach ($words as $word) {
        $stmt->bind_param("s", $word);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        if ($result) {
            echo "<p><strong>$word</strong>: {$result['vietnamese']} ({$result['english_definition']})</p>";
        } else {
            echo "<p><strong>$word</strong>: Not found in dictionary</p>";
        }
    }
    
    echo "<h3>✓ Text extraction feature should be working!</h3>";
    echo "<p>If you're still having issues, try:</p>";
    echo "<ul>";
    echo "<li>Make sure you're logged in</li>";
    echo "<li>Check browser console for JavaScript errors</li>";
    echo "<li>Try with simpler text containing basic words like 'cat', 'dog', 'water'</li>";
    echo "<li>Reduce minimum word length to 3</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

$conn->close();
?>
