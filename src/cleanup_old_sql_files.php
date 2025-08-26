<?php
header('Content-Type: text/plain; charset=utf-8');

echo "🧹 Cleaning up old SQL files...\n\n";

// List of old SQL files that are now obsolete
$oldFiles = [
    'create_flashcard_tables.sql',
    'cleanup_database.sql', 
    'database_setup.sql',
    'setup_database.php',
    'setup_complete_dictionary.sql',
    'simple_cleanup.php',
    'run_cleanup.php',
    'analyze_database_conflicts.php',
    'check_database_structure.php',
    'sync_decks.php',
    'fix_flashcards_table.php'
];

$moved = 0;
$errors = 0;

// Create backup directory
$backupDir = __DIR__ . '/old_sql_backup';
if (!is_dir($backupDir)) {
    if (mkdir($backupDir, 0755, true)) {
        echo "✅ Created backup directory: old_sql_backup/\n";
    } else {
        echo "❌ Failed to create backup directory\n";
        exit(1);
    }
}

echo "📦 Moving old SQL files to backup directory...\n\n";

foreach ($oldFiles as $file) {
    $filePath = __DIR__ . '/' . $file;
    $backupPath = $backupDir . '/' . $file;
    
    if (file_exists($filePath)) {
        if (rename($filePath, $backupPath)) {
            echo "✅ Moved: $file\n";
            $moved++;
        } else {
            echo "❌ Failed to move: $file\n";
            $errors++;
        }
    } else {
        echo "ℹ️  Not found: $file\n";
    }
}

echo "\n📊 Summary:\n";
echo "- Files moved: $moved\n";
echo "- Errors: $errors\n";
echo "- Backup location: old_sql_backup/\n\n";

if ($errors === 0) {
    echo "🎉 Cleanup completed successfully!\n\n";
    echo "📋 Current SQL files:\n";
    echo "✅ complete_database_setup.sql - Main database setup script\n";
    echo "✅ setup_complete_database.php - PHP script to run the setup\n";
    echo "✅ migrate_flashcard_tables.php - Migration script (if needed)\n";
    echo "✅ test_flashcard_sync.php - Test script for verification\n\n";
    
    echo "🚀 Next steps:\n";
    echo "1. Run: php setup_complete_database.php\n";
    echo "2. Or visit: setup_complete_database.php in your browser\n";
    echo "3. Test with: test_flashcard_sync.php\n\n";
    
    echo "💡 The old files are safely backed up in old_sql_backup/ directory\n";
    echo "   You can delete this directory once you confirm everything works.\n";
} else {
    echo "⚠️  Some files could not be moved. Please check permissions.\n";
}
?>
