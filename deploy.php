<?php
/**
 * Automated Deployment Webhook
 * 
 * Instructions:
 * 1. Change the SECRET_TOKEN below to a strong, random string.
 * 2. In your GitHub/GitLab repository settings, add a Webhook.
 * 3. Payload URL: https://www.sigereja.com/deploy.php?token=YOUR_SECRET_TOKEN
 * 4. Content type: application/json
 * 5. Events: Push events
 */

// Define your secure token here
define('SECRET_TOKEN', '9a8juK5r7apoAka5ghau78f7b2c9d');

// Timing-safe comparison helper
function safe_compare(string $a, string $b): bool {
    if (function_exists('hash_equals')) {
        return hash_equals($a, $b);
    }
    return $a === $b;
}

$authenticated = false;

// 1. Verify via X-Hub-Signature-256 header (when using GitHub webhook Secret field)
$hub_signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
if (!empty($hub_signature)) {
    $raw_post = file_get_contents('php://input');
    $parts = explode('=', $hub_signature, 2);
    $algo = $parts[0] ?? '';
    $hash = $parts[1] ?? '';
    if ($algo === 'sha256' && !empty($hash)) {
        $payload_hash = hash_hmac('sha256', $raw_post, SECRET_TOKEN);
        if (safe_compare($payload_hash, $hash)) {
            $authenticated = true;
        }
    }
}

// 2. Verify via URL query parameter (fallback)
if (!$authenticated) {
    $provided_token = $_GET['token'] ?? '';
    if (!empty($provided_token) && safe_compare(SECRET_TOKEN, $provided_token)) {
        $authenticated = true;
    }
}

if (!$authenticated) {
    header('HTTP/1.1 403 Forbidden');
    die('Access Denied. Invalid token or signature.');
}

// Ensure the request is a POST request (Webhooks usually are)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    die('Method not allowed.');
}

// The path to your repository (usually the current directory if deploy.php is in root)
$repo_dir = __DIR__;

// Commands to execute
$commands = [
    'echo "Starting Deployment: ' . date('Y-m-d H:i:s') . '"',
    'cd ' . escapeshellarg($repo_dir),
    'git fetch origin',
    'git reset --hard origin/main', // Change 'main' to 'master' if your default branch is master
    'git pull origin main',
    // 'composer install --no-dev --optimize-autoloader', // Uncomment if you add Composer later
];

$output = '';

// Execute the commands
foreach ($commands as $command) {
    // Run the command and capture output and errors
    $tmp = shell_exec("$command 2>&1");
    $output .= "<span style=\"color: #6BE236;\">\$</span> <span style=\"color: #729FCF;\">{$command}\n</span>";
    $output .= htmlentities(trim($tmp)) . "\n\n";
}

// Log the output
file_put_contents(__DIR__ . '/deploy.log', strip_tags($output) . "\n-------------------------\n", FILE_APPEND);

// Return response
header('Content-Type: text/html');
echo <<<HTML
<!DOCTYPE HTML>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <title>Deployment Triggered</title>
</head>
<body style="background-color: #000000; color: #FFFFFF; font-weight: bold; padding: 0 10px;">
<pre>
{$output}
</pre>
</body>
</html>
HTML;
?>
