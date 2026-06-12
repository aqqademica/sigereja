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
define('SECRET_TOKEN', '9a8juK?~5-r7apoAka-5ghau78f7b2c9d');

// Verify the token
$provided_token = $_GET['token'] ?? '';
if ($provided_token !== SECRET_TOKEN) {
    header('HTTP/1.1 403 Forbidden');
    die('Access Denied. Invalid token.');
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
