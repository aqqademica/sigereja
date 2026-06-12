<?php
// config/database.php
// Credentials are loaded from the .env file in the project root.
// In production: set real credentials in .env and restrict file permissions.

/**
 * Simple .env parser — reads KEY=VALUE lines, ignores comments and blanks.
 */
function load_env(string $path): void {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (!str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);
        if (!isset($_ENV[$key])) {
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

// Load .env from project root (one level up from /config/)
load_env(__DIR__ . '/../.env');

// Define BASE_URL for PWA and assets
$env_app_url = $_ENV['APP_URL'] ?? '/SIGereja';
define('BASE_URL', rtrim($env_app_url, '/'));

$host    = $_ENV['DB_HOST']    ?? '127.0.0.1';
$db      = $_ENV['DB_NAME']    ?? 'sigereja';
$user    = $_ENV['DB_USER']    ?? 'root';       // fallback for local dev only
$pass    = $_ENV['DB_PASS']    ?? '';           // fallback for local dev only
$charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // In production, do NOT expose the connection error to the browser.
    http_response_code(500);
    error_log('[SIGereja] Database connection failed: ' . $e->getMessage());
    die('Koneksi database gagal. Silakan hubungi administrator.');
}
?>
