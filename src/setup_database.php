<?php
// Database setup script - creates all necessary tables
require_once 'services/database.php';

echo "<h2>🔧 Database Setup</h2>";

// Check database connection
if (!$conn) {
    echo "<p style='color: red;'>❌ Database connection failed!</p>";
    echo "<p>Please check your database configuration in services/database.php</p>";
    exit;
}

echo "<p style='color: green;'>✅ Database connected successfully</p>";

// Function to create table if not exists
function createTable($conn, $tableName, $createSQL) {
    try {
        // Check if table exists
        $result = $conn->query("SHOW TABLES LIKE '$tableName'");
        if ($result && $result->num_rows > 0) {
            echo "<p style='color: blue;'>ℹ️ Table '$tableName' already exists</p>";
            return true;
        }
        
        // Create table
        if ($conn->query($createSQL)) {
            echo "<p style='color: green;'>✅ Table '$tableName' created successfully</p>";
            return true;
        } else {
            echo "<p style='color: red;'>❌ Error creating table '$tableName': " . $conn->error . "</p>";
            return false;
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Exception creating table '$tableName': " . $e->getMessage() . "</p>";
        return false;
    }
}

echo "<h3>Creating Tables...</h3>";

// Create users table
$usersSQL = "CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
createTable($conn, 'users', $usersSQL);

// Create flashcard_decks table
$decksSQL = "CREATE TABLE flashcard_decks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id)
)";
createTable($conn, 'flashcard_decks', $decksSQL);

// Create flashcards table
$flashcardsSQL = "CREATE TABLE flashcards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    deck_id INT NOT NULL,
    front TEXT NOT NULL,
    back TEXT NOT NULL,
    example TEXT,
    difficulty ENUM('easy', 'medium', 'hard') DEFAULT 'medium',
    next_review DATE,
    review_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_deck_id (deck_id),
    INDEX idx_next_review (next_review)
)";
createTable($conn, 'flashcards', $flashcardsSQL);

// Create dictionary table
$dictionarySQL = "CREATE TABLE dictionary (
    id INT AUTO_INCREMENT PRIMARY KEY,
    word VARCHAR(255) NOT NULL UNIQUE,
    vietnamese TEXT,
    english_definition TEXT,
    example TEXT,
    part_of_speech VARCHAR(50) DEFAULT 'noun',
    difficulty ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_word (word),
    INDEX idx_difficulty (difficulty),
    INDEX idx_part_of_speech (part_of_speech)
)";
createTable($conn, 'dictionary', $dictionarySQL);

// Create specialized_terms table
$specializedSQL = "CREATE TABLE specialized_terms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    word VARCHAR(255) NOT NULL,
    vietnamese TEXT,
    english_definition TEXT,
    example TEXT,
    domain VARCHAR(100),
    difficulty ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'intermediate',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_word (word),
    INDEX idx_domain (domain)
)";
createTable($conn, 'specialized_terms', $specializedSQL);

echo "<h3>Checking Current Data...</h3>";

// Check current data in tables
$tables = ['users', 'flashcard_decks', 'flashcards', 'dictionary', 'specialized_terms'];
foreach ($tables as $table) {
    try {
        $result = $conn->query("SELECT COUNT(*) as count FROM $table");
        if ($result) {
            $row = $result->fetch_assoc();
            $count = $row['count'];
            echo "<p>📊 Table '$table': $count records</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠️ Could not count records in '$table': " . $e->getMessage() . "</p>";
    }
}

// Check if current user has decks (if logged in)
session_start();
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    echo "<h3>Current User's Data (ID: $userId)</h3>";
    
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM flashcard_decks WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $deckCount = $row['count'];
        
        echo "<p>📚 Your flashcard decks: $deckCount</p>";
        
        if ($deckCount > 0) {
            $stmt = $conn->prepare("SELECT id, name, description FROM flashcard_decks WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            echo "<ul>";
            while ($deck = $result->fetch_assoc()) {
                echo "<li><strong>" . htmlspecialchars($deck['name']) . "</strong>";
                if ($deck['description']) {
                    echo " - " . htmlspecialchars($deck['description']);
                }
                echo " (ID: " . $deck['id'] . ")</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color: orange;'>⚠️ You don't have any flashcard decks yet.</p>";
            echo "<p><a href='flashcards.php'>Create your first deck</a></p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error checking user data: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: blue;'>ℹ️ Not logged in. <a href='index.php'>Login</a> to see your personal data.</p>";
}

echo "<h3>Next Steps</h3>";
echo "<ul>";
echo "<li>✅ All tables are now created</li>";
echo "<li>📚 <a href='setup_complete_dictionary.sql'>Run dictionary setup</a> to add vocabulary</li>";
echo "<li>🏠 <a href='index.php'>Go to homepage</a></li>";
echo "<li>📋 <a href='flashcards.php'>Manage flashcards</a></li>";
echo "</ul>";

$conn->close();
?>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    background: #f8f9fa;
}

h2, h3 {
    color: #333;
}

p {
    line-height: 1.5;
}

ul {
    line-height: 1.6;
}

a {
    color: #667eea;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}
</style>
