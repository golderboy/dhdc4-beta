<?php

declare(strict_types=1);

/**
 * Prepare the current DHDC database as a distributable, data-free baseline.
 *
 * Dry-run (default):
 *   php tools/prepare-master-baseline.php
 *
 * Read-only verification:
 *   php tools/prepare-master-baseline.php --verify [--allow-accounts]
 *
 * Print the exact target table names without row contents:
 *   php tools/prepare-master-baseline.php --list-targets
 *
 * Execute:
 *   php tools/prepare-master-baseline.php --execute --confirm=CLEAR-dhdc4
 *
 * This script never prints credentials or row contents. Its output is limited to
 * table names, categories, and aggregate row counts.
 */

$root = dirname(__DIR__);
require $root . '/common/config/connect_database.php';

$options = getopt('', ['execute', 'verify', 'allow-accounts', 'list-targets', 'confirm:']);
$execute = array_key_exists('execute', $options);
$verifyOnly = array_key_exists('verify', $options);
$allowAccounts = array_key_exists('allow-accounts', $options);
$listTargets = array_key_exists('list-targets', $options);

if (($execute && $verifyOnly)
    || ($allowAccounts && !$verifyOnly)
    || ($listTargets && ($execute || $verifyOnly || $allowAccounts))) {
    fwrite(STDERR, "Choose one of --execute, --verify, or --list-targets; --allow-accounts is valid only with --verify.\n");
    exit(2);
}

$pdo = new PDO(
    "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4",
    $db_user,
    $db_pass,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 30,
    ]
);

$database = (string) $pdo->query('SELECT DATABASE()')->fetchColumn();
$expectedConfirmation = 'CLEAR-' . $database;

if ($execute && (($options['confirm'] ?? '') !== $expectedConfirmation)) {
    fwrite(STDERR, "Refusing to execute. Use --confirm={$expectedConfirmation}\n");
    exit(2);
}

function quoteIdentifier(string $identifier): string
{
    if (!preg_match('/^[A-Za-z0-9_]+$/', $identifier)) {
        throw new RuntimeException("Unsafe database identifier: {$identifier}");
    }

    return '`' . $identifier . '`';
}

function tableCount(PDO $pdo, string $table): int
{
    return (int) $pdo->query('SELECT COUNT(*) FROM ' . quoteIdentifier($table))->fetchColumn();
}

function tableExists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM information_schema.tables
         WHERE table_schema=DATABASE() AND table_name=:table AND table_type='BASE TABLE'"
    );
    $stmt->execute([':table' => $table]);

    return (int) $stmt->fetchColumn() === 1;
}

function addTarget(array &$targets, string $table, string $category): void
{
    $targets[$table] ??= [];
    if (!in_array($category, $targets[$table], true)) {
        $targets[$table][] = $category;
    }
}

$tables = $pdo->query(
    "SELECT table_name
     FROM information_schema.tables
     WHERE table_schema=DATABASE() AND table_type='BASE TABLE'
     ORDER BY table_name"
)->fetchAll(PDO::FETCH_COLUMN);
$tableSet = array_fill_keys($tables, true);

$rawTables = $pdo->query('SELECT file_name FROM sys_files ORDER BY file_name')->fetchAll(PDO::FETCH_COLUMN);
if (count($rawTables) !== 43) {
    throw new RuntimeException('Expected exactly 43 source tables in sys_files; found ' . count($rawTables));
}

$targets = [];
foreach ($rawTables as $table) {
    if (!isset($tableSet[$table])) {
        throw new RuntimeException("Source table listed in sys_files does not exist: {$table}");
    }
    addTarget($targets, $table, '43-file source');
}

// Catch extensions that mirror the import staging tables, including referral files.
foreach ($tables as $table) {
    if (str_starts_with($table, 'dhdc_tmp_')) {
        addTarget($targets, $table, 'import staging');
        $baseTable = substr($table, strlen('dhdc_tmp_'));
        if (isset($tableSet[$baseTable])) {
            addTarget($targets, $baseTable, 'extended import source');
        }
    }
}

// Patient/service identifiers catch legacy and module tables outside normal prefixes.
$identifierColumns = [
    'CID', 'PID', 'HN', 'AN', 'PERSON_ID', 'VISIT_GUID',
    'REFERID', 'REFERID_PROVINCE', 'PID_IN',
];
$placeholders = implode(',', array_fill(0, count($identifierColumns), '?'));
$stmt = $pdo->prepare(
    "SELECT DISTINCT table_name
     FROM information_schema.columns
     WHERE table_schema=DATABASE() AND UPPER(column_name) IN ({$placeholders})"
);
$stmt->execute($identifierColumns);
foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $table) {
    if (isset($tableSet[$table])) {
        addTarget($targets, $table, 'patient/service identifiers');
    }
}

foreach ($tables as $table) {
    if (preg_match('/^(?:t|s|tmp|tmpz|tmz|err|qof|sb|ws)_/i', $table)) {
        addTarget($targets, $table, 'derived/report data');
    }
    if (preg_match('/^(?:temp_|tt_qof_|dhdc_moph_)/i', $table)) {
        addTarget($targets, $table, 'module working data');
    }
    if (preg_match('/^dhdc_module_(?:hrp|unitcost)(?:_|$)/i', $table)
        || preg_match('/^dhdc_population_age_group/i', $table)
        || in_array($table, ['dhdc_input_ancdata', 'dhdc_procedure_dental'], true)) {
        addTarget($targets, $table, 'module result data');
    }
    if (str_ends_with($table, '_correct')) {
        addTarget($targets, $table, 'manual correction data');
    }
    if (preg_match('/^log_/i', $table)
        || in_array($table, ['hdc_log', 'information_log'], true)) {
        addTarget($targets, $table, 'operational log');
    }
}

foreach ([
    'sys_upload_fortythree',
    'sys_count_import',
    'sys_count_import_file',
    'sys_dhdc_count_file',
] as $table) {
    if (isset($tableSet[$table])) {
        addTarget($targets, $table, 'import/process metadata');
    }
}

// A distributable baseline must not contain real identities, password hashes,
// sessions/tokens, or role assignments. RBAC definitions remain untouched.
foreach (['auth_assignment', 'profile', 'social_account', 'token', 'user'] as $table) {
    if (isset($tableSet[$table])) {
        addTarget($targets, $table, 'application account data');
    }
}
if ($allowAccounts) {
    foreach (['auth_assignment', 'profile', 'social_account', 'token', 'user'] as $table) {
        unset($targets[$table]);
    }
}

// These are definitions/state rows and must not be truncated with similarly named data tables.
$preserveTables = [
    'sys_files',
    'sys_transform',
    'sys_transform_plus',
    'sys_transform_all',
    'sys_report',
    'sys_report_dhdc',
    'sys_reportcategory',
    'sys_reportcategory_dhdc',
    'sys_report_drop',
    'dhdc_module_s43_file',
    'dhdc_module_student_class',
    'dhdc_income',
    'dhdc_qof_report',
];
foreach ($preserveTables as $table) {
    unset($targets[$table]);
}

$exchangeTables = [];
foreach (array_keys($targets) as $table) {
    if (preg_match('/^tmp_export_exchange_[a-f0-9]{32}$/i', $table)) {
        $exchangeTables[] = $table;
        unset($targets[$table]);
    }
}

ksort($targets);
sort($exchangeTables);

if ($listTargets) {
    foreach (array_keys($targets) as $table) {
        echo $table, "\n";
    }
    exit(0);
}

$rowsBefore = 0;
$nonEmptyBefore = 0;
foreach (array_keys($targets) as $table) {
    $count = tableCount($pdo, $table);
    $rowsBefore += $count;
    if ($count > 0) {
        $nonEmptyBefore++;
    }
}

printf("Mode: %s\n", $execute ? 'EXECUTE' : 'DRY-RUN');
printf("Database: %s\n", $database);
printf("43-file source tables: %d\n", count($rawTables));
printf("Tables selected for truncation: %d (%d non-empty, %d total rows)\n", count($targets), $nonEmptyBefore, $rowsBefore);
printf("HDC Exchange result tables selected for removal: %d\n", count($exchangeTables));

if (!$execute && $verifyOnly) {
    $failures = [];
    if ($rowsBefore !== 0 || $nonEmptyBefore !== 0) {
        $failures[] = "{$nonEmptyBefore} selected tables still contain {$rowsBefore} rows";
    }
    if (count($exchangeTables) !== 0) {
        $failures[] = count($exchangeTables) . ' HDC Exchange result tables remain';
    }
    foreach (['sys_files', 'sys_report_dhdc', 'sys_reportcategory_dhdc', 'chospital'] as $table) {
        if (!tableExists($pdo, $table) || tableCount($pdo, $table) === 0) {
            $failures[] = "required definition/reference table is empty: {$table}";
        }
    }
    $runningRows = (int) $pdo->query(
        "SELECT COUNT(*) FROM sys_process_running WHERE is_running='false'"
    )->fetchColumn();
    if ($runningRows !== 1) {
        $failures[] = 'sys_process_running is not in the single false-row baseline state';
    }
    $qcRows = (int) $pdo->query('SELECT COUNT(*) FROM sys_files WHERE COALESCE(qc, 0) <> 0')->fetchColumn();
    if ($qcRows !== 0) {
        $failures[] = "{$qcRows} sys_files rows still contain QC result counts";
    }
    $timestampRows = 0;
    foreach (['last_transform', 'last_err_check'] as $table) {
        if (tableExists($pdo, $table)) {
            $timestampRows += tableCount($pdo, $table);
        }
    }
    if ($timestampRows !== 0) {
        $failures[] = "{$timestampRows} transform/QC timestamps remain";
    }
    if (tableExists($pdo, 'dhdc_qof_report')) {
        $qofResultRows = (int) $pdo->query(
            "SELECT COUNT(*) FROM dhdc_qof_report
             WHERE data_json IS NOT NULL AND TRIM(data_json) <> ''"
        )->fetchColumn();
        if ($qofResultRows !== 0) {
            $failures[] = "{$qofResultRows} QOF report result payloads remain";
        }
    }
    if ($failures) {
        fwrite(STDERR, "Master baseline verification failed:\n - " . implode("\n - ", $failures) . "\n");
        exit(1);
    }
    printf("Master baseline verification: PASS (%d empty data tables, 0 HDC Exchange result tables)\n", count($targets));
    exit(0);
}

if (!$execute) {
    foreach ($targets as $table => $categories) {
        $count = tableCount($pdo, $table);
        if ($count > 0) {
            printf("NONEMPTY\t%s\t%d\t%s\n", $table, $count, implode(', ', $categories));
        }
    }
    foreach ($exchangeTables as $table) {
        printf("DROP\t%s\t%d\tHDC Exchange result\n", $table, tableCount($pdo, $table));
    }
    printf("Dry-run only. Execute with --execute --confirm=%s\n", $expectedConfirmation);
    exit(0);
}

$lockName = $database . ':prepare-master-baseline';
$lockStmt = $pdo->prepare('SELECT GET_LOCK(:name, 5)');
$lockStmt->execute([':name' => $lockName]);
if ((int) $lockStmt->fetchColumn() !== 1) {
    throw new RuntimeException('Could not acquire the database cleanup lock.');
}

try {
    if (tableExists($pdo, 'sys_process_running')) {
        $running = $pdo->query("SELECT COUNT(*) FROM sys_process_running WHERE is_running='true'")->fetchColumn();
        if ((int) $running > 0) {
            throw new RuntimeException('A transform/import process is currently marked as running.');
        }
    }

    $pdo->exec('SET SESSION FOREIGN_KEY_CHECKS=0');
    foreach (array_keys($targets) as $table) {
        $pdo->exec('TRUNCATE TABLE ' . quoteIdentifier($table));
    }
    foreach ($exchangeTables as $table) {
        $pdo->exec('DROP TABLE ' . quoteIdentifier($table));
    }

    $routineStmt = $pdo->query(
        "SELECT routine_name FROM information_schema.routines
         WHERE routine_schema=DATABASE()
           AND routine_type='PROCEDURE'
           AND routine_name REGEXP '^tmp_export_exchange_[a-f0-9]{32}$'"
    );
    foreach ($routineStmt->fetchAll(PDO::FETCH_COLUMN) as $routine) {
        $pdo->exec('DROP PROCEDURE ' . quoteIdentifier($routine));
    }

    $pdo->exec('UPDATE sys_files SET qc=0');
    if (tableExists($pdo, 'sys_process_running')) {
        $pdo->exec('DELETE FROM sys_process_running');
        $pdo->exec("INSERT INTO sys_process_running (is_running) VALUES ('false')");
    }
    if (tableExists($pdo, 'sys_check_process')) {
        $pdo->exec('DELETE FROM sys_check_process');
        $pdo->exec('INSERT INTO sys_check_process (fnc_name, time) VALUES (NULL, NULL)');
    }
    foreach (['last_transform', 'last_err_check'] as $table) {
        if (tableExists($pdo, $table)) {
            $pdo->exec('TRUNCATE TABLE ' . quoteIdentifier($table));
        }
    }
    if (tableExists($pdo, 'dhdc_qof_report')) {
        $pdo->exec('UPDATE dhdc_qof_report SET data_json=NULL WHERE data_json IS NOT NULL');
    }
    $pdo->exec('SET SESSION FOREIGN_KEY_CHECKS=1');
} finally {
    try {
        $pdo->exec('SET SESSION FOREIGN_KEY_CHECKS=1');
        $release = $pdo->prepare('SELECT RELEASE_LOCK(:name)');
        $release->execute([':name' => $lockName]);
    } catch (Throwable $ignored) {
        // The original exception, if any, is more useful than cleanup failures here.
    }
}

$failures = [];
foreach (array_keys($targets) as $table) {
    $count = tableCount($pdo, $table);
    if ($count !== 0) {
        $failures[] = "{$table} has {$count} rows";
    }
}

$remainingExchange = (int) $pdo->query(
    "SELECT COUNT(*) FROM information_schema.tables
     WHERE table_schema=DATABASE()
       AND table_name REGEXP '^tmp_export_exchange_[a-f0-9]{32}$'"
)->fetchColumn();
if ($remainingExchange !== 0) {
    $failures[] = "{$remainingExchange} HDC Exchange result tables remain";
}

foreach (['sys_files', 'sys_report_dhdc', 'sys_reportcategory_dhdc', 'chospital'] as $table) {
    if (!tableExists($pdo, $table) || tableCount($pdo, $table) === 0) {
        $failures[] = "required definition/reference table is empty: {$table}";
    }
}

$runningRows = (int) $pdo->query(
    "SELECT COUNT(*) FROM sys_process_running WHERE is_running='false'"
)->fetchColumn();
if ($runningRows !== 1) {
    $failures[] = 'sys_process_running is not in the single false-row baseline state';
}

if ($failures) {
    fwrite(STDERR, "Master baseline verification failed:\n - " . implode("\n - ", $failures) . "\n");
    exit(1);
}

printf("Master baseline verification: PASS (%d cleared tables, %d removed HDC Exchange tables)\n", count($targets), count($exchangeTables));
