<?php
/**
 * Injects <?= csrf_field() ?> after the first <input type="hidden" name="action"
 * or after <form method="POST"> tags that don't already have it.
 * Works by inserting after every <form method="POST"> or <form method="post">.
 */

$dirs = [
    'c:/xampp/htdocs/SIGereja/pages/admin',
    'c:/xampp/htdocs/SIGereja/pages/jemaat',
    'c:/xampp/htdocs/SIGereja',   // root: login.php, register.php
];

$token_tag = '<?= csrf_field() ?>';

foreach ($dirs as $dir) {
    $files = glob($dir . '/*.php');
    foreach ($files as $path) {
        $content = file_get_contents($path);

        // Skip if already done or no POST forms
        if (stripos($content, '<form') === false) continue;
        if (strpos($content, 'csrf_field()') !== false) {
            // May already have some — check if count of form POSTs matches count of csrf_field
            $formCount  = substr_count(strtolower($content), 'method="post"');
            $tokenCount = substr_count($content, 'csrf_field()');
            if ($tokenCount >= $formCount) {
                echo "SKIP (all forms covered): " . basename($path) . "\n";
                continue;
            }
        }

        // Insert token right after <input type="hidden" name="action" ... lines inside POST forms
        // Strategy: after every <form method="POST"> block, insert token before first <input>
        $new = preg_replace(
            '/(<form[^>]*method=["\']POST["\'][^>]*>)([\s\r\n]*(?!<\?= csrf_field))/i',
            "$1\n    $token_tag\n",
            $content,
            -1,
            $count
        );

        if ($count > 0 && $new !== $content) {
            file_put_contents($path, $new);
            echo "OK ($count forms): " . basename($path) . "\n";
        } else {
            echo "NO CHANGE: " . basename($path) . "\n";
        }
    }
}
echo "\nDone injecting csrf_field().\n";
