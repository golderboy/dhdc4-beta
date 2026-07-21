<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$backupRoot = $root . DIRECTORY_SEPARATOR . '_codex_backup';
$workDir = $backupRoot . DIRECTORY_SEPARATOR . 'production-init-verification-'
    . gmdate('YmdHis') . '-' . bin2hex(random_bytes(4));

function copyTree(string $source, string $destination): void
{
    if (!is_dir($source)) {
        throw new RuntimeException("Source directory does not exist: $source");
    }
    if (!is_dir($destination) && !mkdir($destination, 0775, true) && !is_dir($destination)) {
        throw new RuntimeException("Cannot create directory: $destination");
    }

    $iterator = new FilesystemIterator($source, FilesystemIterator::SKIP_DOTS);
    foreach ($iterator as $item) {
        $target = $destination . DIRECTORY_SEPARATOR . $item->getFilename();
        if ($item->isDir() && !$item->isLink()) {
            copyTree($item->getPathname(), $target);
            continue;
        }
        if (!copy($item->getPathname(), $target)) {
            throw new RuntimeException("Cannot copy file: {$item->getPathname()}");
        }
    }
}

function readCookieValidationKey(string $file): string
{
    $contents = file_get_contents($file);
    if ($contents === false) {
        throw new RuntimeException("Cannot read generated config: $file");
    }
    if (!preg_match("/cookieValidationKey'\\s*=>\\s*'([^']+)'/", $contents, $matches)) {
        throw new RuntimeException("Generated cookie validation key is missing: $file");
    }

    return $matches[1];
}

try {
    if (!is_dir($backupRoot) && !mkdir($backupRoot, 0775, true) && !is_dir($backupRoot)) {
        throw new RuntimeException("Cannot create verification root: $backupRoot");
    }
    if (!mkdir($workDir, 0775, true)) {
        throw new RuntimeException("Cannot create verification directory: $workDir");
    }
    if (!copy($root . DIRECTORY_SEPARATOR . 'init', $workDir . DIRECTORY_SEPARATOR . 'init')) {
        throw new RuntimeException('Cannot copy the production initializer.');
    }
    copyTree($root . DIRECTORY_SEPARATOR . 'environments', $workDir . DIRECTORY_SEPARATOR . 'environments');

    $command = escapeshellarg(PHP_BINARY)
        . ' ' . escapeshellarg($workDir . DIRECTORY_SEPARATOR . 'init')
        . ' --env=Production --overwrite=All --delete=All';
    $output = [];
    $status = 0;
    exec($command . ' 2>&1', $output, $status);
    $combinedOutput = implode("\n", $output);

    if ($status !== 0) {
        throw new RuntimeException("Initializer exited with status $status:\n$combinedOutput");
    }
    if (preg_match('/\bError\./', $combinedOutput)) {
        throw new RuntimeException("Initializer reported an error:\n$combinedOutput");
    }

    foreach ([
        'backend/runtime',
        'backend/web/assets',
        'frontend/runtime',
        'frontend/web/assets',
    ] as $directory) {
        $path = $workDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $directory);
        if (!is_dir($path)) {
            throw new RuntimeException("Initializer did not create required directory: $directory");
        }
    }

    $frontendKey = readCookieValidationKey($workDir . DIRECTORY_SEPARATOR . 'frontend/config/main-local.php');
    $backendKey = readCookieValidationKey($workDir . DIRECTORY_SEPARATOR . 'backend/config/main-local.php');
    if (strlen($frontendKey) < 32 || strlen($backendKey) < 32 || hash_equals($frontendKey, $backendKey)) {
        throw new RuntimeException('Initializer generated invalid or duplicate cookie validation keys.');
    }

    $commonConfig = file_get_contents($workDir . DIRECTORY_SEPARATOR . 'common/config/main-local.php');
    if ($commonConfig === false) {
        throw new RuntimeException('Cannot read generated common production config.');
    }
    foreach (['DHDC_DB_DSN', 'DHDC_DB_USER', 'DHDC_DB_PASSWORD', 'DHDC_MAILER_DSN'] as $variable) {
        if (strpos($commonConfig, $variable) === false) {
            throw new RuntimeException("Generated production config does not require $variable.");
        }
    }
    if (!preg_match("/'useFileTransport'\\s*=>\\s*false/", $commonConfig)) {
        throw new RuntimeException('Generated production mailer is not configured for real transport.');
    }

    echo "Production initializer verification passed.\n";
    echo "Verification copy: $workDir\n";
} catch (Throwable $exception) {
    fwrite(STDERR, "Production initializer verification failed: {$exception->getMessage()}\n");
    exit(1);
}
