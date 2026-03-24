<?php

$root = dirname(__DIR__);
$family = PHP_OS_FAMILY;

if ($family === 'Windows') {
    $script = $root . '/build/compile-windows.ps1';
    if (!is_file($script)) {
        fwrite(STDERR, "Missing build script: {$script}\n");
        exit(1);
    }
    
    // Check for pwsh first, then powershell
    $shell = 'powershell';
    exec('where pwsh 2>NUL', $output, $code);
    if ($code === 0) {
        $shell = 'pwsh';
    } else {
        exec('where powershell 2>NUL', $output, $code);
        if ($code !== 0) {
            fwrite(STDERR, "Neither 'pwsh' nor 'powershell' found in PATH.\n");
            exit(1);
        }
    }

    $cmd = "{$shell} -NoProfile -ExecutionPolicy Bypass -File " . escapeshellarg($script);
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
