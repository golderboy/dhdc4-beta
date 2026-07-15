<?php

declare(strict_types=1);

ini_set('memory_limit', '2048M');
set_time_limit(0);

$root = dirname(__DIR__);
require $root . '/common/config/connect_database.php';
require_once $root . '/components/ReportSqlHelper.php';

$options = getopt('', ['modules::', 'limit::', 'output::', 'sample-hospcode::', 'strict', 'markdown::']);
$moduleFilter = isset($options['modules'])
    ? array_filter(array_map('trim', explode(',', (string) $options['modules'])))
    : ['hdc', 'hdcex', 'population', 'sqlquery', 'ehr', 'import', 'qc'];
$limit = isset($options['limit']) ? max(0, (int) $options['limit']) : 0;
$sampleHospcode = isset($options['sample-hospcode']) ? (string) $options['sample-hospcode'] : null;
$strict = array_key_exists('strict', $options);

$outDir = $options['output'] ?? ($root . '/output/query-audit');
if (!is_dir($outDir)) {
    mkdir($outDir, 0777, true);
}

$dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4";
$pdo = new PDO($dsn, $db_user, $db_pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_TIMEOUT => 60,
]);

tryExec($pdo, "SET SESSION sql_mode=''");
tryExec($pdo, "SET SESSION group_concat_max_len=1000000");
tryExec($pdo, "SET SESSION character_set_collations='utf8mb3=utf8mb3_general_ci'");

$results = [];
$startedAt = date('c');

function enabled(string $module, array $filter): bool
{
    return in_array($module, $filter, true);
}

function tryExec(PDO $pdo, string $sql): void
{
    try {
        $pdo->exec($sql);
    } catch (Throwable $e) {
        // Compatibility knobs are best effort across MySQL/MariaDB variants.
    }
}

function addResult(array &$results, string $module, string $name, string $status, ?string $message = null, array $meta = []): void
{
    $results[] = array_merge([
        'module' => $module,
        'name' => $name,
        'status' => $status,
        'message' => $message,
    ], $meta);
}

function runQuery(PDO $pdo, array &$results, string $module, string $name, string $sql, array $params = []): void
{
    $start = microtime(true);
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        addResult($results, $module, $name, 'ok', null, [
            'ms' => (int) round((microtime(true) - $start) * 1000),
            'has_row' => $row !== false,
        ]);
    } catch (Throwable $e) {
        addResult($results, $module, $name, 'error', $e->getMessage(), [
            'ms' => (int) round((microtime(true) - $start) * 1000),
            'sqlstate' => $e instanceof PDOException ? ($e->errorInfo[0] ?? null) : null,
            'driver_code' => $e instanceof PDOException ? ($e->errorInfo[1] ?? null) : null,
        ]);
    }
}

function callProcedureBody(PDO $pdo, array &$results, string $module, string $name, string $procName, string $body): void
{
    $start = microtime(true);
    $quotedProc = '`' . str_replace('`', '``', $procName) . '`';
    $body = rtrim($body);
    if ($body !== '' && substr($body, -1) !== ';') {
        $body .= ';';
    }

    try {
        $pdo->exec("DROP PROCEDURE IF EXISTS $quotedProc");
        $pdo->exec("CREATE PROCEDURE $quotedProc()\nBEGIN\n" . $body . "\nEND");

        $stmt = $pdo->query("CALL $quotedProc()");
        if ($stmt !== false) {
            $stmt->fetch(PDO::FETCH_ASSOC);
            do {
                while ($stmt->fetch(PDO::FETCH_ASSOC)) {
                    // Drain result sets so the connection can continue safely.
                }
            } while ($stmt->nextRowset());
            $stmt->closeCursor();
        }

        addResult($results, $module, $name, 'ok', null, [
            'ms' => (int) round((microtime(true) - $start) * 1000),
        ]);
    } catch (Throwable $e) {
        addResult($results, $module, $name, 'error', $e->getMessage(), [
            'ms' => (int) round((microtime(true) - $start) * 1000),
            'sqlstate' => $e instanceof PDOException ? ($e->errorInfo[0] ?? null) : null,
            'driver_code' => $e instanceof PDOException ? ($e->errorInfo[1] ?? null) : null,
        ]);
    } finally {
        tryExec($pdo, "DROP PROCEDURE IF EXISTS $quotedProc");
    }
}

function sqlProfile(string $sql): array
{
    $profile = \components\ReportSqlHelper::classifySql($sql);
    $risks = [];

    if ($profile['has_top_level_order_by']) {
        $risks[] = 'top_level_order_by';
    }
    if (!$profile['has_top_level_where']) {
        $risks[] = 'no_top_level_where';
    }
    if ($profile['has_multiple_statements']) {
        $risks[] = 'multiple_statements';
    }
    if ($profile['has_hospcode'] && $profile['hospcode_expression'] === null) {
        $risks[] = 'unresolved_hospcode_expression';
    }
    if (!$profile['has_hospcode']) {
        $risks[] = 'no_hospcode';
    }
    if (preg_match('/\b(?:TYPEAREA|DISCHARGE|SEX|NATION|HOSPCODE)\b\s*(?:=|<>|!=|in\s*\()\s*\d/i', $sql)) {
        $risks[] = 'unquoted_numeric_code_literal';
    }

    $profile['risks'] = $risks;
    return $profile;
}

function getFirstValue(PDO $pdo, string $sql, array $params = []): ?string
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $value = $stmt->fetchColumn();
    $stmt->closeCursor();
    return $value === false || $value === null ? null : (string) $value;
}

function fetchAllAssoc(PDO $pdo, string $sql, array $params = []): array
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
    return $rows;
}

if ($sampleHospcode === null) {
    $sampleHospcode = getFirstValue($pdo, "SELECT hoscode FROM chospital_amp ORDER BY hoscode LIMIT 1") ?? '00000';
}

if (enabled('hdc', $moduleFilter)) {
    $rows = fetchAllAssoc($pdo, "
        SELECT r.id, r.report_name, s.sql_sum, s.sql_indiv
        FROM sys_report_dhdc r
        LEFT JOIN hdc_rpt_sql s ON s.rpt_id = r.id
        WHERE r.id NOT IN (SELECT id FROM sys_report_drop)
        ORDER BY r.report_id, r.id
    ");
    if ($limit > 0) {
        $rows = array_slice($rows, 0, $limit);
    }
    $idx = 0;
    foreach ($rows as $row) {
        $idx++;
        if (empty($row['sql_sum']) && empty($row['sql_indiv'])) {
            addResult($results, 'hdc', $row['id'] . ' metadata', 'missing_sql', 'No hdc_rpt_sql row or no SQL body', [
                'title' => $row['report_name'],
            ]);
            continue;
        }
        if (!empty($row['sql_sum'])) {
            callProcedureBody(
                $pdo,
                $results,
                'hdc',
                $row['id'] . ' sql_sum',
                'audit_hdc_sum_' . $idx,
                \components\ReportSqlHelper::normalizeProcedureBody($row['sql_sum'])
            );
            $results[count($results) - 1]['title'] = $row['report_name'];
            $results[count($results) - 1]['sql_profile'] = sqlProfile((string) $row['sql_sum']);
        } else {
            addResult($results, 'hdc', $row['id'] . ' sql_sum', 'missing_sql', 'Empty sql_sum', ['title' => $row['report_name']]);
        }
        if (!empty($row['sql_indiv'])) {
            $filteredSql = \components\ReportSqlHelper::applyHospcodeFilter($row['sql_indiv'], $sampleHospcode);
            callProcedureBody(
                $pdo,
                $results,
                'hdc',
                $row['id'] . ' sql_indiv filtered',
                'audit_hdc_ind_' . $idx,
                \components\ReportSqlHelper::normalizeProcedureBody($filteredSql)
            );
            $results[count($results) - 1]['title'] = $row['report_name'];
            $results[count($results) - 1]['sql_profile'] = sqlProfile((string) $row['sql_indiv']);
            $results[count($results) - 1]['filtered_sql_profile'] = sqlProfile((string) $filteredSql);
        } else {
            addResult($results, 'hdc', $row['id'] . ' sql_indiv', 'missing_sql', 'Empty sql_indiv', ['title' => $row['report_name']]);
        }
    }
}

if (enabled('hdcex', $moduleFilter)) {
    tryExec($pdo, "UPDATE sys_config a SET a.provincecode = (SELECT provcode FROM sys_config_main LIMIT 1)");
    $rows = fetchAllAssoc($pdo, "SELECT ex_id, title, ex_sql FROM sys_data_exchange WHERE active=1 ORDER BY cat_id, weight, ex_id");
    if ($limit > 0) {
        $rows = array_slice($rows, 0, $limit);
    }
    foreach ($rows as $idx => $row) {
        if (empty($row['ex_sql'])) {
            addResult($results, 'hdcex', $row['ex_id'], 'missing_sql', 'Empty ex_sql', ['title' => $row['title']]);
            continue;
        }
        $table = 'tmp_export_exchange_' . $row['ex_id'];
        $body = str_replace('{exp_office}', ' ', $row['ex_sql']);
        $body = str_replace('tmp_export_exchange', $table, $body);
        $body = str_replace('chospital', 'chospital_amp', $body);
        $body = "DROP TABLE IF EXISTS `$table`;\n" . $body;
        callProcedureBody($pdo, $results, 'hdcex', $row['ex_id'] . ' ex_sql', 'audit_hdcex_' . ($idx + 1), $body);
        $results[count($results) - 1]['title'] = $row['title'];
        $results[count($results) - 1]['sql_profile'] = sqlProfile((string) $row['ex_sql']);
        runQuery($pdo, $results, 'hdcex', $row['ex_id'] . ' output', "SELECT * FROM `$table` LIMIT 1");
    }
}

if (enabled('population', $moduleFilter)) {
    runQuery($pdo, $results, 'population', 'index age_group5 all', "SELECT t.AGE_GROUP_ID,t.AGE_GROUP,SUM(t.MALE) MALE,SUM(t.FEMALE) FEMALE,SUM(t.TOTAL) TOTAL FROM dhdc_population_age_group5 t GROUP BY t.AGE_GROUP_ID");
    runQuery($pdo, $results, 'population', 'index age_group all', "SELECT t.AGE_GROUP_ID,t.AGE_GROUP,SUM(t.MALE) MALE,SUM(t.FEMALE) FEMALE,SUM(t.TOTAL) TOTAL FROM dhdc_population_age_group t GROUP BY t.AGE_GROUP_ID");
    runQuery($pdo, $results, 'population', 'index age_group5 hospcode', "SELECT * FROM dhdc_population_age_group5 WHERE HOSPCODE = :hospcode", [':hospcode' => $sampleHospcode]);
    runQuery($pdo, $results, 'population', 'index age_group hospcode', "SELECT * FROM dhdc_population_age_group WHERE HOSPCODE = :hospcode", [':hospcode' => $sampleHospcode]);
    runQuery($pdo, $results, 'population', 'typearea', "SELECT t.HOSPCODE,h.hosname HOSNAME,SUM(IF(t.TYPEAREA=1,1,0)) TYPE1,SUM(IF(t.TYPEAREA=2,1,0)) TYPE2,SUM(IF(t.TYPEAREA=3,1,0)) TYPE3,SUM(IF(t.TYPEAREA=4,1,0)) TYPE4,SUM(IF(t.TYPEAREA=5,1,0)) TYPE5,SUM(IF(t.TYPEAREA NOT IN (1,2,3,4,5),1,0)) NONTYPE FROM t_person_cid t LEFT JOIN chospital_amp h ON t.HOSPCODE = h.hoscode WHERE t.DISCHARGE = 9 GROUP BY t.HOSPCODE");
    runQuery($pdo, $results, 'population', 'json-tambon', "SELECT t.tamboncodefull TAM_CODE,t.tambonname TAM_NAME,g.COORDINATES,COUNT(p.CID) POP FROM ctambon t INNER JOIN sys_config_main s ON s.district_code = LEFT(t.tamboncodefull,4) INNER JOIN gis_dhdc_tambon g ON CONCAT(g.PROV_CODE,g.AMP_CODE,g.TAM_CODE)=t.tamboncodefull INNER JOIN t_person_cid p ON LEFT(p.vhid,6)=t.tamboncodefull WHERE p.typearea IN (1,3,5) AND p.DISCHARGE=9 GROUP BY t.tamboncodefull");
}

if (enabled('sqlquery', $moduleFilter)) {
    $rows = fetchAllAssoc($pdo, "SELECT id, topic, sql_script FROM sqlscript ORDER BY id");
    if ($limit > 0) {
        $rows = array_slice($rows, 0, $limit);
    }
    foreach ($rows as $idx => $row) {
        if (empty(trim((string) $row['sql_script']))) {
            addResult($results, 'sqlquery', 'script ' . $row['id'], 'missing_sql', 'Empty sql_script', ['title' => $row['topic']]);
            continue;
        }
        callProcedureBody($pdo, $results, 'sqlquery', 'script ' . $row['id'] . ' ' . $row['topic'], 'audit_sqlquery_' . ($idx + 1), $row['sql_script']);
    }
}

if (enabled('ehr', $moduleFilter)) {
    $sample = fetchAllAssoc($pdo, "SELECT p.hospcode, p.pid, p.cid, s.seq FROM person p INNER JOIN service s ON p.HOSPCODE=s.HOSPCODE AND p.PID=s.PID WHERE p.cid IS NOT NULL AND p.cid<>'' LIMIT 1");
    $cid = $sample[0]['cid'] ?? null;
    $hospcode = $sample[0]['hospcode'] ?? $sampleHospcode;
    $pid = $sample[0]['pid'] ?? null;
    $seq = $sample[0]['seq'] ?? null;
    $an = getFirstValue($pdo, "SELECT an FROM admission WHERE hospcode=:hospcode AND pid=:pid LIMIT 1", [':hospcode' => $hospcode, ':pid' => $pid]);
    runQuery($pdo, $results, 'ehr', 'person-search', "SELECT p.cid,CONCAT(n.prename,p.name,' ',p.lname) AS tname,sex,CONCAT('เลขที่ ',h.HOUSE,' ต.',t.tambonname,' อ.',a.ampurname,' จ.',c.changwatname) AS taddr,CONCAT(tc.chronic,' ',i.diagename) AS chronic,birth FROM person p LEFT JOIN cprename n ON n.id_prename=p.prename LEFT JOIN home h ON h.HOSPCODE=p.HOSPCODE AND h.HID=p.HID LEFT JOIN tmp_chronic tc ON tc.cid=p.cid LEFT JOIN cicd10tm i ON i.diagcode=tc.chronic LEFT JOIN campur a ON a.ampurcode=h.AMPUR AND a.changwatcode=h.CHANGWAT LEFT JOIN cchangwat c ON c.changwatcode=h.CHANGWAT LEFT JOIN ctambon t ON t.tamboncode=h.TAMBON AND t.ampurcode=CONCAT(c.changwatcode,a.ampurcode) WHERE p.cid=:cid LIMIT 1", [':cid' => $cid]);
    runQuery($pdo, $results, 'ehr', 'service-timeline', "SELECT CONCAT(s.date_serv,' ',LEFT(time_serv,2),':',SUBSTR(time_serv,3,2),':',RIGHT(time_serv,2)) tdate,s.SEQ,s.HOSPCODE,h.hosname FROM service s LEFT JOIN chospital_amp h ON s.HOSPCODE=h.hoscode WHERE s.HOSPCODE=:hospcode AND s.PID=:pid ORDER BY s.DATE_SERV DESC", [':hospcode' => $hospcode, ':pid' => $pid]);
    runQuery($pdo, $results, 'ehr', 'diag-tab', "SELECT d.diagcode,diagename,diagtype FROM tmp_diag_opd d LEFT JOIN cicd10tm i ON i.diagcode=d.diagcode WHERE cid=:cid AND seq=:seq AND hospcode=:hospcode UNION ALL SELECT d.diagcode,diagename,diagtype FROM diagnosis_ipd d LEFT JOIN cicd10tm i ON i.diagcode=d.diagcode WHERE an=:an AND hospcode=:hospcode", [':cid' => $cid, ':seq' => $seq, ':an' => $an, ':hospcode' => $hospcode]);
    runQuery($pdo, $results, 'ehr', 'chief-complaint', "SELECT date_serv,CHIEFCOMP,sbp,dbp,pr,rr,btemp,h.hosname AS hospname FROM service s LEFT JOIN chospital_amp h ON s.HOSPCODE=h.hoscode WHERE cid=:cid AND seq=:seq AND s.hospcode=:hospcode", [':cid' => $cid, ':seq' => $seq, ':hospcode' => $hospcode]);
    runQuery($pdo, $results, 'ehr', 'lab-tab', "SELECT l.labtest,t.labtest AS tlname,labresult FROM tmp_labfu l LEFT JOIN clabtest t ON l.LABTEST=t.id_labtest WHERE l.cid=:cid AND l.seq=:seq AND l.hospcode=:hospcode", [':cid' => $cid, ':seq' => $seq, ':hospcode' => $hospcode]);
    runQuery($pdo, $results, 'ehr', 'drug-tab', "SELECT d.dname,d.AMOUNT FROM tmp_drug_opd d WHERE d.cid=:cid AND d.seq=:seq AND d.hospcode=:hospcode UNION ALL SELECT d.dname,d.AMOUNT FROM drug_ipd d WHERE an=:an AND d.hospcode=:hospcode", [':cid' => $cid, ':seq' => $seq, ':an' => $an, ':hospcode' => $hospcode]);
}

if (enabled('import', $moduleFilter)) {
    runQuery($pdo, $results, 'import', 'dashboard upload-summary', "SELECT COUNT(*) AS total_uploads,SUM(CASE WHEN note2='OK' THEN 1 ELSE 0 END) AS ok_uploads,SUM(CASE WHEN note2 LIKE '%ผิดพลาด%' THEN 1 ELSE 0 END) AS error_uploads FROM sys_upload_fortythree");
    runQuery($pdo, $results, 'import', 'dashboard latest-upload', "SELECT id,hospcode,file_name,file_size,upload_date,upload_time,note2,note3 FROM sys_upload_fortythree ORDER BY upload_date DESC, upload_time DESC, id DESC LIMIT 1");
    runQuery($pdo, $results, 'import', 'dashboard count-summary', "SELECT COUNT(*) AS imported_files,COALESCE(SUM(TOTAL_RECORD),0) AS imported_records,MAX(IMPORT_DATE) AS latest_import_date FROM sys_count_import_file");
    runQuery($pdo, $results, 'import', 'dashboard process-status', "SELECT (SELECT is_running FROM sys_process_running LIMIT 1) AS is_running,(SELECT fnc_name FROM sys_check_process LIMIT 1) AS fnc_name,(SELECT time FROM sys_check_process LIMIT 1) AS process_time,(SELECT last_time FROM last_transform LIMIT 1) AS last_transform,(SELECT last_time FROM last_err_check LIMIT 1) AS last_err_check");
    runQuery($pdo, $results, 'import', 'dashboard file-counts', "SELECT FILE_NAME,TOTAL_RECORD,IMPORT_DATE FROM sys_count_import_file ORDER BY TOTAL_RECORD DESC, FILE_NAME ASC LIMIT 15");
    runQuery($pdo, $results, 'import', 'dashboard qc-rows', "SELECT file_name,qc FROM sys_files ORDER BY qc ASC,file_name ASC LIMIT 15");
    runQuery($pdo, $results, 'import', 'count-file table', "SELECT h.hoscode,h.hosname,t.* FROM chospital_amp h LEFT JOIN sys_dhdc_count_file t ON h.hoscode=t.hospcode AND t.b_year=:b_year AND t.tb=:tb", [':b_year' => getFirstValue($pdo, 'SELECT CAST(yearprocess AS UNSIGNED)+543 FROM pk_byear LIMIT 1') ?? '2569', ':tb' => 'service']);
    runQuery($pdo, $results, 'import', 'count-file last-process', "SELECT MAX(t.date_process) lat_process FROM sys_dhdc_count_file t WHERE t.tb=:tb AND t.b_year=:b_year GROUP BY t.tb,t.b_year", [':b_year' => getFirstValue($pdo, 'SELECT CAST(yearprocess AS UNSIGNED)+543 FROM pk_byear LIMIT 1') ?? '2569', ':tb' => 'service']);
}

if (enabled('qc', $moduleFilter)) {
    runQuery($pdo, $results, 'qc', 'index sys-files note1', "SELECT * FROM sys_files WHERE note1='y' LIMIT 1");
    runQuery($pdo, $results, 'qc', 'hos-sum current', "SELECT t.HOSPCODE,h.hosname AS HOSPNAME,t.TOTAL,t.ERR,t.QC FROM chospital_amp h RIGHT JOIN (SELECT t.HOSPCODE,SUM(t.TOTAL) AS TOTAL,SUM(t.ERR) AS ERR,100-ROUND(SUM(t.ERR)*100/SUM(t.TOTAL),2) AS QC FROM err_zhos t GROUP BY t.HOSPCODE) t ON t.HOSPCODE=h.hoscode");
    runQuery($pdo, $results, 'qc', 'hos-sum byear', "SELECT t.HOSPCODE,h.hosname AS HOSPNAME,t.TOTAL,t.ERR,t.QC FROM chospital_amp h RIGHT JOIN (SELECT t.HOSPCODE,SUM(t.TOTAL) AS TOTAL,SUM(t.ERR+t.ERR_DATE) AS ERR,100-ROUND(SUM(t.ERR+t.ERR_DATE)*100/SUM(t.TOTAL),2) AS QC FROM err_zall t WHERE t.BYEAR=:byear GROUP BY t.HOSPCODE) t ON t.HOSPCODE=h.hoscode", [':byear' => getFirstValue($pdo, 'SELECT CAST(yearprocess AS UNSIGNED)+543 FROM pk_byear LIMIT 1') ?? '2569']);
    runQuery($pdo, $results, 'qc', 'hos-file current', "SELECT t.HOSPCODE,t.FILE,t.TOTAL,t.ERR,100-ROUND(t.ERR*100/t.TOTAL,2) AS QC FROM err_zhos t WHERE t.HOSPCODE=:hospcode", [':hospcode' => $sampleHospcode]);
    runQuery($pdo, $results, 'qc', 'hos-file byear', "SELECT t.HOSPCODE,t.FILE,t.TOTAL,(t.ERR+t.ERR_DATE) AS ERR,100-ROUND((t.ERR+t.ERR_DATE)*100/t.TOTAL,2) AS QC FROM err_zall t WHERE t.HOSPCODE=:hospcode AND t.BYEAR=:byear", [':hospcode' => $sampleHospcode, ':byear' => getFirstValue($pdo, 'SELECT CAST(yearprocess AS UNSIGNED)+543 FROM pk_byear LIMIT 1') ?? '2569']);
    $files = fetchAllAssoc($pdo, "SELECT file_name FROM sys_files WHERE note1='y' ORDER BY file_name");
    if ($limit > 0) {
        $files = array_slice($files, 0, $limit);
    }
    foreach ($files as $fileRow) {
        $file = strtolower((string) $fileRow['file_name']);
        if (!preg_match('/^[a-z0-9_]+$/', $file)) {
            addResult($results, 'qc', 'data-error ' . $file, 'error', 'Unsafe file_name for err table name');
            continue;
        }
        runQuery($pdo, $results, 'qc', 'data-error ' . $file, "SELECT * FROM `err_$file` ORDER BY ERR_DATE DESC, BYEAR DESC LIMIT 1");
    }
}

$endedAt = date('c');
$summary = [];
$failureCount = 0;
foreach ($results as $result) {
    $module = $result['module'];
    $status = $result['status'];
    $summary[$module][$status] = ($summary[$module][$status] ?? 0) + 1;
    if ($status === 'error') {
        $failureCount++;
    }
}

$report = [
    'started_at' => $startedAt,
    'ended_at' => $endedAt,
    'modules' => $moduleFilter,
    'limit' => $limit,
    'sample_hospcode' => $sampleHospcode,
    'summary' => $summary,
    'failure_count' => $failureCount,
    'results' => $results,
];

$file = rtrim((string) $outDir, "\\/") . '/report-query-audit-' . date('Ymd-His') . '.json';
file_put_contents($file, json_encode($report, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
$markdownFile = isset($options['markdown'])
    ? (string) $options['markdown']
    : rtrim((string) $outDir, "\\/") . '/report-query-audit-latest.md';
$lines = [
    '# Report SQL Audit',
    '',
    '- Started: ' . $startedAt,
    '- Ended: ' . $endedAt,
    '- Modules: ' . implode(', ', $moduleFilter),
    '- Sample HOSPCODE: ' . $sampleHospcode,
    '- Failure count: ' . $failureCount,
    '',
    '## Summary',
    '',
    '| Module | Status | Count |',
    '| --- | --- | ---: |',
];
foreach ($summary as $module => $counts) {
    ksort($counts);
    foreach ($counts as $status => $count) {
        $lines[] = '| ' . $module . ' | ' . $status . ' | ' . $count . ' |';
    }
}
$lines[] = '';
$lines[] = '## Errors';
$lines[] = '';
$errorRows = array_values(array_filter($results, static function (array $result): bool {
    return $result['status'] === 'error';
}));
if (empty($errorRows)) {
    $lines[] = 'ไม่พบ error จากการรัน audit';
} else {
    foreach (array_slice($errorRows, 0, 50) as $result) {
        $lines[] = '- `' . $result['module'] . '` ' . $result['name'] . ': ' . str_replace(["\r", "\n"], ' ', (string) $result['message']);
    }
    if (count($errorRows) > 50) {
        $lines[] = '- ... เพิ่มเติม ' . (count($errorRows) - 50) . ' รายการ ดูรายละเอียดใน JSON';
    }
}
$lines[] = '';
$lines[] = '## HDC SQL Risk Classes';
$lines[] = '';
$riskCounts = [];
foreach ($results as $result) {
    if (($result['module'] ?? null) !== 'hdc' || empty($result['sql_profile']['risks'])) {
        continue;
    }
    foreach ($result['sql_profile']['risks'] as $risk) {
        $riskCounts[$risk] = ($riskCounts[$risk] ?? 0) + 1;
    }
}
if (empty($riskCounts)) {
    $lines[] = 'ไม่พบ risk class จาก SQL HDC ที่ตรวจ';
} else {
    ksort($riskCounts);
    foreach ($riskCounts as $risk => $count) {
        $lines[] = '- `' . $risk . '`: ' . $count;
    }
}
file_put_contents($markdownFile, implode(PHP_EOL, $lines) . PHP_EOL);

echo "WROTE\t$file\n";
echo "WROTE\t$markdownFile\n";
foreach ($summary as $module => $counts) {
    ksort($counts);
    echo $module . "\t";
    foreach ($counts as $status => $count) {
        echo "$status=$count ";
    }
    echo "\n";
}

if ($strict && $failureCount > 0) {
    exit(1);
}
