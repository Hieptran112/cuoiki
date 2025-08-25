<?php
require_once 'services/database.php';

echo "<h2>🔍 Database Conflicts Analysis</h2>";

try {
    // Get all tables
    $result = $conn->query("SHOW TABLES");
    $tables = [];
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }
    
    echo "<h3>1. Current Tables (" . count($tables) . "):</h3>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
    // Analyze conflicts
    echo "<h3>2. Identified Conflicts:</h3>";
    
    $conflicts = [];
    $duplicates = [];
    $unused = [];
    
    // Check for deck-related conflicts
    if (in_array('decks', $tables) && in_array('flashcard_decks', $tables)) {
        $conflicts[] = [
            'type' => 'Table Conflict',
            'issue' => 'Both "decks" and "flashcard_decks" tables exist',
            'impact' => 'Foreign key constraints reference wrong table',
            'solution' => 'Drop "decks" table, keep "flashcard_decks"'
        ];
    }
    
    // Check for unused tables
    $potentiallyUnused = ['preset_decks', 'learning_progress', 'specialized_terms', 'exercise_results', 'learning_stats'];
    foreach ($potentiallyUnused as $table) {
        if (in_array($table, $tables)) {
            $unused[] = $table;
        }
    }
    
    // Check foreign key constraints
    $result = $conn->query("
        SELECT 
            CONSTRAINT_NAME,
            TABLE_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND REFERENCED_TABLE_NAME IS NOT NULL
        ORDER BY TABLE_NAME
    ");
    
    $fkConflicts = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            if ($row['REFERENCED_TABLE_NAME'] === 'decks' && in_array('flashcard_decks', $tables)) {
                $fkConflicts[] = $row;
            }
        }
    }
    
    // Check flashcards table structure
    $flashcardsStructure = [];
    if (in_array('flashcards', $tables)) {
        $result = $conn->query("DESCRIBE flashcards");
        while ($row = $result->fetch_assoc()) {
            $flashcardsStructure[$row['Field']] = $row;
        }
    }
    
    $structureIssues = [];
    if (!isset($flashcardsStructure['front'])) {
        $structureIssues[] = 'Missing "front" column';
    }
    if (!isset($flashcardsStructure['back'])) {
        $structureIssues[] = 'Missing "back" column';
    }
    if (isset($flashcardsStructure['word']) && isset($flashcardsStructure['definition'])) {
        $structureIssues[] = 'Has old "word/definition" columns that should be migrated to "front/back"';
    }
    
    // Display conflicts
    if (!empty($conflicts)) {
        echo "<h4 style='color: red;'>❌ Table Conflicts:</h4>";
        foreach ($conflicts as $conflict) {
            echo "<div style='border: 1px solid #dc3545; padding: 1rem; margin: 0.5rem 0; border-radius: 4px; background: #f8d7da;'>";
            echo "<strong>{$conflict['type']}:</strong> {$conflict['issue']}<br>";
            echo "<strong>Impact:</strong> {$conflict['impact']}<br>";
            echo "<strong>Solution:</strong> {$conflict['solution']}";
            echo "</div>";
        }
    }
    
    if (!empty($fkConflicts)) {
        echo "<h4 style='color: red;'>❌ Foreign Key Conflicts:</h4>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Constraint</th><th>Table</th><th>Column</th><th>References</th><th>Issue</th></tr>";
        foreach ($fkConflicts as $fk) {
            echo "<tr style='background: #f8d7da;'>";
            echo "<td>{$fk['CONSTRAINT_NAME']}</td>";
            echo "<td>{$fk['TABLE_NAME']}</td>";
            echo "<td>{$fk['COLUMN_NAME']}</td>";
            echo "<td>{$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}</td>";
            echo "<td>References non-existent or wrong table</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    if (!empty($structureIssues)) {
        echo "<h4 style='color: orange;'>⚠️ Structure Issues:</h4>";
        echo "<ul>";
        foreach ($structureIssues as $issue) {
            echo "<li>$issue</li>";
        }
        echo "</ul>";
    }
    
    if (!empty($unused)) {
        echo "<h4 style='color: blue;'>ℹ️ Potentially Unused Tables:</h4>";
        echo "<ul>";
        foreach ($unused as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
    }
    
    // Show what will be cleaned up
    echo "<h3>3. Cleanup Plan:</h3>";
    echo "<div style='background: #d4edda; padding: 1rem; border-radius: 4px; border: 1px solid #c3e6cb;'>";
    echo "<h4>✅ Actions that will be performed:</h4>";
    echo "<ol>";
    echo "<li><strong>Remove problematic foreign key constraints</strong> (especially those referencing 'decks' table)</li>";
    if (in_array('decks', $tables)) {
        echo "<li><strong>Drop 'decks' table</strong> (conflicts with 'flashcard_decks')</li>";
    }
    foreach ($unused as $table) {
        echo "<li><strong>Drop '$table' table</strong> (unused in current system)</li>";
    }
    echo "<li><strong>Standardize 'flashcards' table structure</strong> (ensure front/back columns exist)</li>";
    if (!empty($structureIssues)) {
        echo "<li><strong>Migrate data from old columns</strong> (word/definition → front/back)</li>";
    }
    echo "<li><strong>Ensure all core tables exist</strong> (users, flashcard_decks, flashcards, dictionary)</li>";
    echo "</ol>";
    echo "</div>";
    
    // Show data that will be preserved
    echo "<h3>4. Data Preservation:</h3>";
    echo "<div style='background: #fff3cd; padding: 1rem; border-radius: 4px; border: 1px solid #ffeaa7;'>";
    echo "<h4>📊 Current data counts (will be preserved):</h4>";
    
    $preservedTables = ['users', 'flashcard_decks', 'flashcards', 'dictionary', 'listening_exercises', 'listening_results'];
    foreach ($preservedTables as $table) {
        if (in_array($table, $tables)) {
            try {
                $result = $conn->query("SELECT COUNT(*) as count FROM $table");
                $count = $result->fetch_assoc()['count'];
                echo "<p>• <strong>$table:</strong> $count records</p>";
            } catch (Exception $e) {
                echo "<p>• <strong>$table:</strong> Error counting records</p>";
            }
        } else {
            echo "<p>• <strong>$table:</strong> Table does not exist (will be created)</p>";
        }
    }
    echo "</div>";
    
    // Action buttons
    echo "<h3>5. Actions:</h3>";
    echo "<div style='margin: 1rem 0;'>";
    
    if (!empty($conflicts) || !empty($fkConflicts) || !empty($structureIssues)) {
        echo "<a href='cleanup_database.sql' download style='background: #dc3545; color: white; padding: 0.75rem 1.5rem; text-decoration: none; border-radius: 4px; margin-right: 1rem;'>📥 Download Cleanup Script</a>";
        echo "<button onclick='runCleanup()' style='background: #28a745; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 4px; cursor: pointer; margin-right: 1rem;'>🧹 Run Cleanup Now</button>";
    } else {
        echo "<p style='color: green;'>✅ No conflicts detected! Your database structure is clean.</p>";
    }
    
    echo "<button onclick='location.reload()' style='background: #007bff; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 4px; cursor: pointer;'>🔄 Refresh Analysis</button>";
    echo "</div>";
    
    echo "<div id='cleanup-result' style='margin-top: 1rem;'></div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error analyzing database: " . $e->getMessage() . "</p>";
}

$conn->close();
?>

<script>
function runCleanup() {
    if (!confirm('This will clean up your database structure. Are you sure?\n\nThis action will:\n- Remove conflicting tables\n- Fix foreign key constraints\n- Standardize table structure\n\nYour data will be preserved.')) {
        return;
    }
    
    const resultDiv = document.getElementById('cleanup-result');
    resultDiv.innerHTML = '<p>⏳ Running database cleanup...</p>';
    
    fetch('run_cleanup.php', {
        method: 'POST',
        credentials: 'same-origin'
    })
    .then(response => response.text())
    .then(data => {
        resultDiv.innerHTML = `
            <div style="background: #d4edda; padding: 1rem; border-radius: 4px; border: 1px solid #c3e6cb; margin-top: 1rem;">
                <h4>🎉 Cleanup Results:</h4>
                <pre style="white-space: pre-wrap; background: white; padding: 1rem; border-radius: 4px;">${data}</pre>
                <button onclick="location.reload()" style="background: #28a745; color: white; padding: 0.5rem 1rem; border: none; border-radius: 4px; cursor: pointer; margin-top: 1rem;">Refresh Page</button>
            </div>
        `;
    })
    .catch(error => {
        resultDiv.innerHTML = `
            <div style="background: #f8d7da; padding: 1rem; border-radius: 4px; border: 1px solid #dc3545; margin-top: 1rem;">
                <h4>❌ Cleanup Error:</h4>
                <p>${error.message}</p>
            </div>
        `;
    });
}
</script>

<style>
body { 
    font-family: Arial, sans-serif; 
    max-width: 1200px; 
    margin: 0 auto; 
    padding: 20px; 
    background: #f8f9fa;
}
table { 
    margin: 1rem 0; 
    font-size: 0.9rem;
    width: 100%;
}
th, td { 
    padding: 0.5rem; 
    text-align: left; 
    border: 1px solid #ddd;
}
th { 
    background: #f0f0f0; 
    font-weight: bold;
}
h2, h3, h4 { 
    color: #333; 
}
pre {
    white-space: pre-wrap;
    word-wrap: break-word;
}
</style>
