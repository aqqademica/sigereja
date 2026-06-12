<?php
$files = [
    'master_jabatan', 'master_keahlian', 'master_peran_ibadah',
    'master_seksi', 'master_sektor', 'kegiatan', 'keluarga', 'pengurus',
    'jadwal_detail', 'jemaat_edit', 'keluarga_add', 'keluarga_detail'
];

foreach ($files as $name) {
    $f = $name . '.php';
    $path = 'c:/xampp/htdocs/SIGereja/pages/admin/' . $f;
    if (!file_exists($path)) {
        echo "SKIP (not found): $f\n";
        continue;
    }
    $content = file_get_contents($path);

    if (strpos($content, 'csrf_verify') !== false) {
        echo "SKIP (already has csrf_verify): $f\n";
        continue;
    }

    // Add csrf_verify on the line after the first POST check
    $old = "if (\$_SERVER['REQUEST_METHOD'] === 'POST') {";
    $new = "if (\$_SERVER['REQUEST_METHOD'] === 'POST') {\n    csrf_verify('{$name}.php'); // H5";
    $content = str_replace($old, $new, $content, $count);

    // Handle double-condition checks (keluarga_detail.php style)
    $old2 = "if (\$_SERVER['REQUEST_METHOD'] === 'POST' && isset(";
    if (strpos($content, $old2) !== false && $count === 0) {
        // Add verify before the first such check
        $content = preg_replace(
            '/if \(\$_SERVER\[.REQUEST_METHOD.\] === .POST./',
            "csrf_verify('{$name}.php'); // H5\nif (\$_SERVER['REQUEST_METHOD'] === 'POST'",
            $content,
            1,
            $count
        );
    }

    if ($count > 0) {
        file_put_contents($path, $content);
        echo "OK: added csrf_verify to $f ($count replacement)\n";
    } else {
        echo "WARN: no replacement made in $f\n";
    }
}
echo "Done.\n";
