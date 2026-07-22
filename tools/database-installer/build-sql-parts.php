<?php

declare(strict_types=1);

/**
 * Build a statement-safe, ordered DHDC4 database installer package from a
 * verified MariaDB logical dump (.sql or a .zip containing exactly one .sql).
 *
 * Generated artifacts are written below output/ and are intentionally ignored
 * by Git. No database password is accepted or written by this builder.
 */

const DEFAULT_MAX_PART_BYTES = 32 * 1024 * 1024;
const PACKAGE_FORMAT_VERSION = 1;
const DATABASE_NAME = 'dhdc4';
const OWNER_ACCOUNT = 'dhdc4@localhost';

$projectRoot = dirname(__DIR__, 2);
$options = getopt('', [
    'input:',
    'output:',
    'version:',
    'max-part-bytes:',
    'force',
    'no-archive',
]);

$version = (string) ($options['version'] ?? 'v4.0.2');
if (!preg_match('/^v\d+\.\d+\.\d+(?:[-+][0-9A-Za-z.-]+)?$/', $version)) {
    fail("Invalid semantic version: {$version}");
}

$defaultInput = $projectRoot . '/output/release/dhdc4-database-master-v4.0.0.zip';
$inputPath = absolutePath((string) ($options['input'] ?? $defaultInput), $projectRoot, true);
$defaultOutput = $projectRoot . '/output/database-installer/dhdc4-database-installer-' . $version;
$outputPath = absolutePath((string) ($options['output'] ?? $defaultOutput), $projectRoot, false);
$maxPartBytes = filter_var(
    $options['max-part-bytes'] ?? DEFAULT_MAX_PART_BYTES,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1024 * 1024, 'max_range' => 1024 * 1024 * 1024]]
);
if ($maxPartBytes === false) {
    fail('--max-part-bytes must be between 1 MiB and 1 GiB.');
}

$allowedOutputRoot = normalizePath($projectRoot . '/output/database-installer');
$normalizedOutput = normalizePath($outputPath);
if (!str_starts_with($normalizedOutput . '/', $allowedOutputRoot . '/')) {
    fail('Output must be a child of output/database-installer.');
}
if (file_exists($outputPath)) {
    if (!array_key_exists('force', $options)) {
        fail("Output already exists; use --force to replace it: {$outputPath}");
    }
    removeDirectory($outputPath, $allowedOutputRoot);
}

$sqlDirectory = $outputPath . '/sql';
$adminDirectory = $outputPath . '/admin';
foreach ([$sqlDirectory, $adminDirectory] as $directory) {
    if (!mkdir($directory, 0775, true) && !is_dir($directory)) {
        fail("Unable to create directory: {$directory}");
    }
}

$source = openSqlSource($inputPath);
$sourceHash = hash_init('sha256');
$sourceBytes = 0;
$sourceLines = 0;
$definerReplacements = 0;
$legacyDefinerHelperReplacements = 0;
$delimiter = ';';
$category = 'bootstrap';
$skipLegacyDefinerBody = false;

$categoryConfig = [
    'bootstrap' => ['order' => 0, 'prefix' => '00-bootstrap', 'description' => 'MariaDB session setup and database creation'],
    'schema' => ['order' => 10, 'prefix' => '10-schema', 'description' => 'Tables and temporary view structures'],
    'data' => ['order' => 20, 'prefix' => '20-seed-data', 'description' => 'Reference and baseline seed data'],
    'events' => ['order' => 30, 'prefix' => '30-events', 'description' => 'Scheduled events'],
    'routines' => ['order' => 40, 'prefix' => '40-routines', 'description' => 'Functions and stored procedures'],
    'triggers' => ['order' => 50, 'prefix' => '50-triggers', 'description' => 'Database triggers'],
    'views' => ['order' => 60, 'prefix' => '60-views', 'description' => 'Final view definitions'],
    'finalize' => ['order' => 90, 'prefix' => '90-finalize', 'description' => 'Restore MariaDB session settings'],
];

$writers = [];
$readHandle = $source['handle'];
try {
    while (($line = fgets($readHandle)) !== false) {
        $sourceLines++;
        $sourceBytes += strlen($line);
        hash_update($sourceHash, $line);
        if (!mb_check_encoding($line, 'UTF-8')) {
            fail("Source dump is not valid UTF-8 at line {$sourceLines}.");
        }

        $trimmed = trim($line);
        $nextCategory = detectCategory($trimmed, $category);
        if ($nextCategory !== null) {
            $category = $nextCategory;
        }

        if ($category === 'views' && str_starts_with($trimmed, '/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE')) {
            $category = 'finalize';
        }

        if ($skipLegacyDefinerBody) {
            if ($trimmed === 'END ;;') {
                $lineEnding = str_ends_with($line, "\r\n") ? "\r\n" : "\n";
                $replacement = 'BEGIN' . $lineEnding
                    . "  SELECT 'Legacy direct mysql.proc updates are disabled; definers are managed by the DHDC4 installer.' AS message;" . $lineEnding
                    . 'END ;;' . $lineEnding;
                writeSql($writers, $categoryConfig, $sqlDirectory, $category, $replacement, $maxPartBytes, true, 1);
                $skipLegacyDefinerBody = false;
                $legacyDefinerHelperReplacements++;
            }
            continue;
        }

        if (preg_match('/^CREATE\s+DEFINER=.*\s+PROCEDURE\s+`z_update_definer`\s*\(\s*\)/i', $trimmed)) {
            $skipLegacyDefinerBody = true;
        }

        $normalizedLine = preg_replace_callback(
            '/DEFINER=(?:CURRENT_USER|`[^`]+`@`[^`]+`|\'[^\']+\'@\'[^\']+\')/i',
            static function (): string {
                return 'DEFINER=`dhdc4`@`localhost`';
            },
            $line,
            -1,
            $replaced
        );
        if ($normalizedLine === null) {
            fail("Unable to normalize DEFINER at line {$sourceLines}.");
        }
        $definerReplacements += $replaced;

        $statementCount = 0;
        $safeBoundary = false;
        if (preg_match('/^DELIMITER\s+(\S+)$/i', trim($normalizedLine), $delimiterMatch)) {
            $delimiter = $delimiterMatch[1];
            $safeBoundary = $delimiter === ';';
        } elseif ($delimiter !== '' && str_ends_with(rtrim($normalizedLine), $delimiter)) {
            $statementCount = 1;
            $safeBoundary = $delimiter === ';';
        }

        writeSql(
            $writers,
            $categoryConfig,
            $sqlDirectory,
            $category,
            $normalizedLine,
            $maxPartBytes,
            $safeBoundary,
            $statementCount
        );
    }
    if (!feof($readHandle)) {
        fail('Unexpected read error while processing the SQL source.');
    }
} finally {
    fclose($readHandle);
    if (isset($source['zip']) && $source['zip'] instanceof ZipArchive) {
        $source['zip']->close();
    }
}

if ($skipLegacyDefinerBody) {
    fail('The legacy z_update_definer procedure was not terminated correctly.');
}
if ($delimiter !== ';') {
    fail("The SQL dump ended with an unexpected delimiter: {$delimiter}");
}
if ($definerReplacements !== 514) {
    fail("Expected 514 DEFINER replacements; found {$definerReplacements}.");
}
if ($legacyDefinerHelperReplacements !== 1) {
    fail("Expected one legacy z_update_definer replacement; found {$legacyDefinerHelperReplacements}.");
}

$files = closeAndCollectWriters($writers, $categoryConfig, $outputPath);
if (count($files) < 6) {
    fail('The generated installer has too few SQL parts; source classification likely failed.');
}

$assetMap = [
    __DIR__ . '/install-windows.ps1' => $outputPath . '/install-windows.ps1',
    __DIR__ . '/install-linux.sh' => $outputPath . '/install-linux.sh',
    __DIR__ . '/sql/create-owner-and-grants.sql' => $adminDirectory . '/create-owner-and-grants.sql',
    __DIR__ . '/sql/verify-install.sql' => $adminDirectory . '/verify-install.sql',
    $projectRoot . '/docs/database-installer.md' => $outputPath . '/README-TH.md',
    $projectRoot . '/docs/database-installer-windows.md' => $outputPath . '/INSTALL-WINDOWS-TH.md',
    $projectRoot . '/docs/database-installer-linux.md' => $outputPath . '/INSTALL-LINUX-TH.md',
];
foreach ($assetMap as $sourceAsset => $destinationAsset) {
    if (!is_file($sourceAsset) || !copy($sourceAsset, $destinationAsset)) {
        fail("Unable to copy installer asset: {$sourceAsset}");
    }
}

$installOrder = implode("\n", array_column($files, 'path')) . "\n";
file_put_contents($outputPath . '/install-order.txt', $installOrder);

$manifest = [
    'format_version' => PACKAGE_FORMAT_VERSION,
    'package' => 'DHDC4 Production Master database installer',
    'version' => $version,
    'database' => DATABASE_NAME,
    'database_charset' => 'utf8mb3',
    'database_collation' => 'utf8mb3_general_ci',
    'required_mariadb' => '12.2 or a compatible version validated by the operator',
    'owner' => OWNER_ACCOUNT,
    'source' => [
        'file' => basename($inputPath),
        'sql_entry' => $source['entry'],
        'bytes' => $sourceBytes,
        'lines' => $sourceLines,
        'sha256' => hash_final($sourceHash),
    ],
    'normalization' => [
        'definer_replacements' => $definerReplacements,
        'legacy_definer_helper_replacements' => $legacyDefinerHelperReplacements,
    ],
    'expected' => [
        'tables_and_views' => 821,
        'base_tables' => 820,
        'views' => 1,
        'routines' => 512,
        'functions' => 103,
        'procedures' => 409,
        'events' => 1,
        'triggers' => 0,
        'sys_files_rows' => 43,
        'empty_target_tables' => 560,
        'definer_objects' => 514,
    ],
    'files' => $files,
];
file_put_contents(
    $outputPath . '/manifest.json',
    json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n"
);

$checksumFiles = listFilesRecursively($outputPath);
sort($checksumFiles, SORT_STRING);
$checksumLines = [];
foreach ($checksumFiles as $file) {
    if (basename($file) === 'SHA256SUMS') {
        continue;
    }
    $relative = relativePath($file, $outputPath);
    $checksumLines[] = hash_file('sha256', $file) . '  ' . $relative;
}
file_put_contents($outputPath . '/SHA256SUMS', implode("\n", $checksumLines) . "\n");

$archivePath = $outputPath . '.zip';
if (!array_key_exists('no-archive', $options)) {
    if (file_exists($archivePath)) {
        if (!array_key_exists('force', $options)) {
            fail("Archive already exists; use --force to replace it: {$archivePath}");
        }
        unlink($archivePath);
    }
    createZip($outputPath, $archivePath);
    file_put_contents(
        $archivePath . '.sha256',
        hash_file('sha256', $archivePath) . '  ' . basename($archivePath) . "\n"
    );
}

printf("Package: %s\n", $outputPath);
printf("SQL parts: %d\n", count($files));
printf("Source bytes: %d\n", $sourceBytes);
printf("DEFINER replacements: %d\n", $definerReplacements);
printf("Legacy helper replacements: %d\n", $legacyDefinerHelperReplacements);
if (is_file($archivePath)) {
    printf("Archive: %s\n", $archivePath);
    printf("Archive SHA-256: %s\n", hash_file('sha256', $archivePath));
}

function detectCategory(string $line, string $current): ?string
{
    return match (true) {
        str_starts_with($line, '-- Table structure for table') => 'schema',
        str_starts_with($line, '-- Temporary table structure for view') => 'schema',
        str_starts_with($line, '-- Dumping data for table') => 'data',
        str_starts_with($line, '-- Dumping events for database') => 'events',
        str_starts_with($line, '-- Dumping routines for database') => 'routines',
        str_starts_with($line, '-- Dumping triggers for table') => 'triggers',
        str_starts_with($line, '-- Final view structure for view') => 'views',
        default => null,
    };
}

function writeSql(
    array &$writers,
    array $config,
    string $sqlDirectory,
    string $category,
    string $content,
    int $maxPartBytes,
    bool $safeBoundary,
    int $statementCount
): void {
    if (!isset($writers[$category])) {
        $writers[$category] = [
            'part' => 0,
            'handle' => null,
            'path' => null,
            'bytes' => 0,
            'statements' => 0,
            'rotate' => false,
            'files' => [],
        ];
    }
    $writer =& $writers[$category];
    if ($writer['handle'] === null || $writer['rotate']) {
        if (is_resource($writer['handle'])) {
            fclose($writer['handle']);
            $writer['files'][] = [
                'path' => $writer['path'],
                'bytes' => $writer['bytes'],
                'statements' => $writer['statements'],
            ];
        }
        $writer['part']++;
        $filename = sprintf('%s-part-%03d.sql', $config[$category]['prefix'], $writer['part']);
        $path = $sqlDirectory . '/' . $filename;
        $handle = fopen($path, 'wb');
        if ($handle === false) {
            fail("Unable to create SQL part: {$path}");
        }
        $writer['handle'] = $handle;
        $writer['path'] = $path;
        $writer['bytes'] = 0;
        $writer['statements'] = 0;
        $writer['rotate'] = false;
    }
    $written = fwrite($writer['handle'], $content);
    if ($written === false || $written !== strlen($content)) {
        fail("Unable to write SQL part: {$writer['path']}");
    }
    $writer['bytes'] += $written;
    $writer['statements'] += $statementCount;
    if ($safeBoundary && $writer['bytes'] >= $maxPartBytes) {
        $writer['rotate'] = true;
    }
}

function closeAndCollectWriters(array &$writers, array $config, string $outputPath): array
{
    $result = [];
    foreach ($writers as $category => &$writer) {
        if (is_resource($writer['handle'])) {
            fclose($writer['handle']);
            $writer['files'][] = [
                'path' => $writer['path'],
                'bytes' => $writer['bytes'],
                'statements' => $writer['statements'],
            ];
            $writer['handle'] = null;
        }
        foreach ($writer['files'] as $file) {
            $relative = relativePath($file['path'], $outputPath);
            $result[] = [
                'order' => $config[$category]['order'],
                'category' => $category,
                'description' => $config[$category]['description'],
                'path' => $relative,
                'bytes' => $file['bytes'],
                'sql_statements' => $file['statements'],
                'sha256' => hash_file('sha256', $file['path']),
            ];
        }
    }
    unset($writer);
    usort($result, static function (array $left, array $right): int {
        return [$left['order'], $left['path']] <=> [$right['order'], $right['path']];
    });
    $sequence = 1;
    foreach ($result as &$file) {
        $file['sequence'] = $sequence++;
    }
    unset($file);
    return $result;
}

function openSqlSource(string $inputPath): array
{
    $extension = strtolower(pathinfo($inputPath, PATHINFO_EXTENSION));
    if ($extension === 'sql') {
        $handle = fopen($inputPath, 'rb');
        if ($handle === false) {
            fail("Unable to open SQL input: {$inputPath}");
        }
        return ['handle' => $handle, 'entry' => basename($inputPath)];
    }
    if ($extension !== 'zip') {
        fail('Input must be a .sql file or a .zip containing one .sql file.');
    }
    $zip = new ZipArchive();
    if ($zip->open($inputPath) !== true) {
        fail("Unable to open ZIP input: {$inputPath}");
    }
    $entries = [];
    for ($index = 0; $index < $zip->numFiles; $index++) {
        $name = (string) $zip->getNameIndex($index);
        if (strtolower(pathinfo($name, PATHINFO_EXTENSION)) === 'sql') {
            $entries[] = $name;
        }
    }
    if (count($entries) !== 1) {
        $zip->close();
        fail('The source ZIP must contain exactly one SQL file.');
    }
    $handle = $zip->getStream($entries[0]);
    if ($handle === false) {
        $zip->close();
        fail("Unable to read SQL entry from ZIP: {$entries[0]}");
    }
    return ['handle' => $handle, 'entry' => $entries[0], 'zip' => $zip];
}

function createZip(string $sourceDirectory, string $archivePath): void
{
    $zip = new ZipArchive();
    if ($zip->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        fail("Unable to create archive: {$archivePath}");
    }
    $rootName = basename($sourceDirectory);
    $zip->addEmptyDir($rootName);
    $files = listFilesRecursively($sourceDirectory);
    sort($files, SORT_STRING);
    foreach ($files as $file) {
        $localName = $rootName . '/' . relativePath($file, $sourceDirectory);
        if (!$zip->addFile($file, $localName)) {
            $zip->close();
            fail("Unable to add file to archive: {$file}");
        }
    }
    if (!$zip->close()) {
        fail("Unable to finalize archive: {$archivePath}");
    }
}

function listFilesRecursively(string $directory): array
{
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($iterator as $item) {
        if ($item->isFile()) {
            $files[] = $item->getPathname();
        }
    }
    return $files;
}

function removeDirectory(string $directory, string $allowedRoot): void
{
    $normalized = normalizePath($directory);
    if (!str_starts_with($normalized . '/', $allowedRoot . '/') || $normalized === $allowedRoot) {
        fail("Refusing to remove unsafe directory: {$directory}");
    }
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($iterator as $item) {
        $ok = $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        if (!$ok) {
            fail("Unable to remove: {$item->getPathname()}");
        }
    }
    if (!rmdir($directory)) {
        fail("Unable to remove directory: {$directory}");
    }
}

function absolutePath(string $path, string $base, bool $mustExist): string
{
    $isWindowsAbsolute = strlen($path) >= 3
        && ctype_alpha($path[0])
        && $path[1] === ':'
        && ($path[2] === '\\' || $path[2] === '/');
    $isAbsolute = $isWindowsAbsolute || str_starts_with($path, '/');
    $candidate = $isAbsolute ? $path : $base . '/' . $path;
    if ($mustExist) {
        $resolved = realpath($candidate);
        if ($resolved === false || !is_file($resolved)) {
            fail("Input file does not exist: {$candidate}");
        }
        return $resolved;
    }
    return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $candidate);
}

function normalizePath(string $path): string
{
    return rtrim(str_replace('\\', '/', $path), '/');
}

function relativePath(string $path, string $root): string
{
    $normalizedPath = normalizePath($path);
    $normalizedRoot = normalizePath($root);
    if (!str_starts_with($normalizedPath . '/', $normalizedRoot . '/')) {
        fail("Path is outside package root: {$path}");
    }
    return ltrim(substr($normalizedPath, strlen($normalizedRoot)), '/');
}

function fail(string $message): never
{
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}
