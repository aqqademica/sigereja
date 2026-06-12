<?php
// Enable error reporting for diagnostics
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/database.php';

echo "<h3>SIGereja Database Diagnostic Tool</h3>";

try {
    echo "Database connection: <strong>SUCCESS</strong><br><br>";
    
    // List tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<strong>Existing Tables in Database:</strong><ul>";
    foreach ($tables as $table) {
        echo "<li>" . htmlspecialchars($table) . "</li>";
    }
    echo "</ul>";
    
    // Check key tables
    $expected_tables = ['users', 'tblSektor', 'tblWartaJemaat', 'tblJemaat', 'tblKeluarga'];
    echo "<strong>Verification:</strong><br>";
    foreach ($expected_tables as $t) {
        if (in_array($t, $tables)) {
            echo "✔ Table <code>$t</code>: <strong>EXISTS</strong><br>";
        } else {
            echo "❌ Table <code>$t</code>: <strong style='color:red;'>MISSING</strong><br>";
        }
    }
    
} catch (Exception $e) {
    echo "<strong style='color:red;'>Error:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
}
