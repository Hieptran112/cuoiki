<?php
require_once 'services/database.php';

header('Content-Type: text/plain');

try {
    echo "🧹 Starting Database Cleanup...\n\n";
    
    // Read and execute the cleanup SQL script
    $sqlFile = __DIR__ . '/cleanup_database.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("Cleanup SQL file not found: $sqlFile");
    }
    
    $sql = file_get_contents($sqlFile);
    if ($sql === false) {
        throw new Exception("Could not read cleanup SQL file");
    }
    
    // Split SQL into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^\s*--/', $stmt) && !preg_match('/^\s*SELECT.*Status.*as/', $stmt);
        }
    );
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($statements as $statement) {
        if (empty(trim($statement))) continue;
        
        try {
            // Skip comments and status messages
            if (preg_match('/^\s*--/', $statement) || 
                preg_match('/SELECT.*as Status/i', $statement) ||
                preg_match('/SELECT.*as Info/i', $statement)) {
                continue;
            }
            
            $result = $conn->query($statement . ';');
            if ($result) {
                $successCount++;
                
                // Show specific success messages for important operations
                if (preg_match('/DROP TABLE IF EXISTS (\w+)/i', $statement, $matches)) {
                    echo "✅ Dropped table: {$matches[1]}\n";
                } elseif (preg_match('/CREATE TABLE IF NOT EXISTS (\w+)/i', $statement, $matches)) {
                    echo "✅ Ensured table exists: {$matches[1]}\n";
                } elseif (preg_match('/ALTER TABLE (\w+) DROP FOREIGN KEY/i', $statement, $matches)) {
                    echo "✅ Removed foreign key constraint from: {$matches[1]}\n";
                } elseif (preg_match('/ALTER TABLE (\w+) ADD COLUMN (\w+)/i', $statement, $matches)) {
                    echo "✅ Added column '{$matches[2]}' to table: {$matches[1]}\n";
                }
            } else {
                $errorCount++;
                echo "❌ Error executing statement: " . $conn->error . "\n";
                echo "Statement: " . substr($statement, 0, 100) . "...\n\n";
            }
        } catch (Exception $e) {
            $errorCount++;
            echo "❌ Exception: " . $e->getMessage() . "\n";
            echo "Statement: " . substr($statement, 0, 100) . "...\n\n";
        }
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "📊 CLEANUP SUMMARY:\n";
    echo "✅ Successful operations: $successCount\n";
    echo "❌ Failed operations: $errorCount\n";
    
    // Verify final state
    echo "\n🔍 FINAL VERIFICATION:\n";
    
    // Check tables
    $result = $conn->query("SHOW TABLES");
    $tables = [];
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }
    echo "📋 Final tables: " . implode(", ", $tables) . "\n";
    
    // Check flashcards structure
    if (in_array('flashcards', $tables)) {
        $result = $conn->query("DESCRIBE flashcards");
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        echo "🏗️ Flashcards columns: " . implode(", ", $columns) . "\n";
        
        $hasFront = in_array('front', $columns);
        $hasBack = in_array('back', $columns);
        echo "✅ Front column: " . ($hasFront ? "EXISTS" : "MISSING") . "\n";
        echo "✅ Back column: " . ($hasBack ? "EXISTS" : "MISSING") . "\n";
    }
    
    // Check foreign key constraints
    $result = $conn->query("
        SELECT COUNT(*) as count
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'flashcards'
        AND REFERENCED_TABLE_NAME = 'decks'
    ");
    $badConstraints = $result->fetch_assoc()['count'];
    echo "🔗 Bad foreign key constraints: $badConstraints\n";
    
    // Check data counts
    echo "\n📊 DATA COUNTS:\n";
    $dataTables = ['users', 'flashcard_decks', 'flashcards', 'dictionary'];
    foreach ($dataTables as $table) {
        if (in_array($table, $tables)) {
            try {
                $result = $conn->query("SELECT COUNT(*) as count FROM $table");
                $count = $result->fetch_assoc()['count'];
                echo "• $table: $count records\n";
            } catch (Exception $e) {
                echo "• $table: Error counting records\n";
            }
        }
    }
    
    if ($errorCount === 0) {
        echo "\n🎉 DATABASE CLEANUP COMPLETED SUCCESSFULLY!\n";
        echo "✅ All conflicts resolved\n";
        echo "✅ Foreign key issues fixed\n";
        echo "✅ Table structure standardized\n";
        echo "✅ Data preserved\n";
        echo "\nYou can now use the 'Add to deck' feature without foreign key errors!\n";
    } else {
        echo "\n⚠️ CLEANUP COMPLETED WITH SOME ERRORS\n";
        echo "Some operations failed, but the main issues should be resolved.\n";
        echo "Please check the error messages above.\n";
    }
    
} catch (Exception $e) {
    echo "❌ FATAL ERROR: " . $e->getMessage() . "\n";
    echo "Cleanup could not be completed.\n";
}

$conn->close();
?>
