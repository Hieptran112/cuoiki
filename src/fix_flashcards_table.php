<?php
require_once 'services/database.php';

header('Content-Type: text/plain');

try {
    // Check if 'front' column exists
    $result = $conn->query("DESCRIBE flashcards");
    $frontExists = false;
    $backExists = false;
    
    while ($row = $result->fetch_assoc()) {
        if ($row['Field'] === 'front') $frontExists = true;
        if ($row['Field'] === 'back') $backExists = true;
    }
    
    $changes = [];
    
    // Add 'front' column if missing
    if (!$frontExists) {
        $conn->query("ALTER TABLE flashcards ADD COLUMN front TEXT NOT NULL AFTER deck_id");
        $changes[] = "Added 'front' column";
    }
    
    // Add 'back' column if missing
    if (!$backExists) {
        $conn->query("ALTER TABLE flashcards ADD COLUMN back TEXT NOT NULL AFTER front");
        $changes[] = "Added 'back' column";
    }
    
    // Check if we have old columns that need to be migrated
    $result = $conn->query("DESCRIBE flashcards");
    $hasQuestion = false;
    $hasAnswer = false;
    
    while ($row = $result->fetch_assoc()) {
        if ($row['Field'] === 'question') $hasQuestion = true;
        if ($row['Field'] === 'answer') $hasAnswer = true;
    }
    
    // Migrate data from old columns if they exist
    if ($hasQuestion && $hasAnswer && $frontExists && $backExists) {
        $conn->query("UPDATE flashcards SET front = question, back = answer WHERE front = '' OR front IS NULL");
        $changes[] = "Migrated data from question/answer to front/back";
    }
    
    if (empty($changes)) {
        echo "No changes needed - table structure is correct";
    } else {
        echo "Fixed flashcards table:\n" . implode("\n", $changes);
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

$conn->close();
?>
