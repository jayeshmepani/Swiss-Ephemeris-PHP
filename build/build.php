<?php

$root = dirname(__DIR__);
$family = PHP_OS_FAMILY;

if ($family === 'Windows') {
    $script = $root . '/build/compile-windows.ps1';
    if (!is_file($script)) {
        fwrite(STDERR, "Missing build script: {$script}\n");
        exit(1);
    }
    $cmd = 'powershell -NoProfile -ExecutionPolicy Bypass -File ' . escapeshellarg($script);
} elseif ($family === 'Darwin') {
    $script = $root . '/build/compile-macos.sh';
    if (!is_file($script)) {
        fwrite(STDERR, "Missing build script: {$script}\n");
        exit(1);
    }
    $cmd = 'bash ' . escapeshellarg($script);
} else {
    $script = $root . '/build/compile-linux.sh';
    if (!is_file($script)) {
        fwrite(STDERR, "Missing build script: {$script}\n");
        exit(1);
    }
    $cmd = 'bash ' . escapeshellarg($script);
}

passthru($cmd, $code);
exit((int) $code);
