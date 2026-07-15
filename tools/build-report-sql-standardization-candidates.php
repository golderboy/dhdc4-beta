<?php

declare(strict_types=1);

ini_set('memory_limit', '2048M');

$root = dirname(__DIR__);
require $root . '/common/config/connect_database.php';
require_once $root . '/components/ReportSqlHelper.php';

$options = getopt('', ['output::', 'sample-hospcode::']);
$outDir = $options['output'] ?? ($root . '/output/query-audit');
$sampleHospcode = isset($options['sample-hospcode']) ? (string) $options['sample-hospcode'] : '06879';

if (!is_dir($outDir)) {
    mkdir($outDir, 0777, true);
}

$dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4";
$pdo = new PDO($dsn, $db_user, $db_pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

function fetchRows(PDO $pdo, string $sql, array $params = []): array
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    $stmt->closeCursor();
    return $rows;
}

function profileSql(string $sql): array
{
    $profile = \components\ReportSqlHelper::classifySql($sql);
    $risks = [];

    if ($profile['has_top_level_order_by']) {
        $risks[] = 'top_level_order_by';
    }
    if ($profile['has_top_level_group_by']) {
        $risks[] = 'top_level_group_by';
    }
    if ($profile['has_top_level_having']) {
        $risks[] = 'top_level_having';
    }
    if ($profile['has_top_level_limit']) {
        $risks[] = 'top_level_limit';
    }
    if (!$profile['has_top_level_where']) {
        $risks[] = 'no_top_level_where';
    }
    if ($profile['has_multiple_statements']) {
        $risks[] = 'multiple_statements';
    }
    if ($profile['has_select_star']) {
        $risks[] = 'select_star';
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

    $profile['risks'] = array_values(array_unique($risks));
    return $profile;
}

function riskLevel(array $risks): string
{
    if (array_intersect($risks, ['unresolved_hospcode_expression', 'top_level_order_by', 'unquoted_numeric_code_literal'])) {
        return 'high';
    }
    if (array_intersect($risks, ['multiple_statements', 'no_hospcode', 'select_star'])) {
        return 'medium';
    }
    if (!empty($risks)) {
        return 'low';
    }
    return 'none';
}

function reasons(array $risks): array
{
    $map = [
        'top_level_order_by' => 'มี ORDER BY ระดับบนสุด ต้องแทรก filter ก่อน ORDER BY ไม่ใช่ต่อท้าย SQL',
        'top_level_group_by' => 'มี GROUP BY ระดับบนสุด ต้องรักษาลำดับ clause เมื่อเติม filter',
        'top_level_having' => 'มี HAVING ระดับบนสุด ต้องเติม filter ใน WHERE ก่อน HAVING',
        'top_level_limit' => 'มี LIMIT ระดับบนสุด ต้องเติม filter ก่อน LIMIT',
        'no_top_level_where' => 'ไม่มี WHERE ระดับบนสุด ควรปรับต้นฉบับให้มี WHERE 1=1 ถ้าต้องรองรับ filter เพิ่ม',
        'multiple_statements' => 'มีหลาย statement ใน SQL เดียว ควรแยก preparation กับ final SELECT ให้ชัดเจน',
        'select_star' => 'ใช้ SELECT * เสี่ยงคอลัมน์ซ้ำเมื่อครอบ query หรือ join/derived table',
        'unresolved_hospcode_expression' => 'พบ HOSPCODE แต่ helper ระบุ expression ที่ปลอดภัยไม่ได้ ต้องให้ผู้ดูแลรายงานกำหนด alias ชัดเจน',
        'no_hospcode' => 'ไม่พบ HOSPCODE ใน SQL ต้นฉบับ ต้องยืนยันว่ารายงานนี้ควรกรองหน่วยบริการหรือไม่',
        'unquoted_numeric_code_literal' => 'มีการเทียบ field รหัสแบบ varchar กับ numeric literal เช่น TYPEAREA in(1,3) เสี่ยง error ใน SQL mode เข้มงวด',
        'missing_exp_office_placeholder' => 'Data-Exchange ไม่มี {exp_office} จึงไม่มีกลไก filter หน่วยบริการมาตรฐาน',
    ];

    return array_values(array_map(static function (string $risk) use ($map): string {
        return $map[$risk] ?? $risk;
    }, $risks));
}

function checksum(string $sql): string
{
    return hash('sha256', $sql);
}

$candidates = [];

$hdcRows = fetchRows($pdo, "
    SELECT r.id, r.report_id, r.report_name, s.sql_indiv, s.sql_sum
    FROM sys_report_dhdc r
    LEFT JOIN hdc_rpt_sql s ON s.rpt_id = r.id
    WHERE r.id NOT IN (SELECT id FROM sys_report_drop)
    ORDER BY r.report_id, r.id
");

foreach ($hdcRows as $row) {
    foreach (['sql_sum', 'sql_indiv'] as $field) {
        $before = trim((string) ($row[$field] ?? ''));
        if ($before === '') {
            continue;
        }
        $profile = profileSql($before);
        $after = $field === 'sql_indiv'
            ? \components\ReportSqlHelper::applyHospcodeFilter($before, $sampleHospcode)
            : \components\ReportSqlHelper::normalizeProcedureBody($before);
        $risks = $profile['risks'];
        $level = riskLevel($risks);
        if ($level === 'none') {
            continue;
        }

        $candidates[] = [
            'module' => 'hdc',
            'record_key' => $row['id'],
            'sql_field' => $field,
            'title' => $row['report_name'],
            'risk_level' => $level,
            'risks' => $risks,
            'reasons' => reasons($risks),
            'proposed_action' => $field === 'sql_indiv'
                ? 'ปรับ SQL ต้นฉบับให้ expose HOSPCODE/WHERE/clause order ชัดเจน แล้วเทียบกับ after_sql_preview จาก dry-run'
                : 'ปรับ summary SQL เฉพาะโครงสร้างที่เสี่ยง โดยไม่เติม HOSPCODE filter อัตโนมัติ',
            'before_checksum' => checksum($before),
            'after_preview_checksum' => checksum($after),
            'before_sql' => $before,
            'after_sql_preview' => $after,
            'sql_profile' => $profile,
        ];
    }
}

$hdcexRows = fetchRows($pdo, "SELECT ex_id, title, ex_sql FROM sys_data_exchange WHERE active=1 ORDER BY cat_id, weight, ex_id");
foreach ($hdcexRows as $row) {
    $before = trim((string) ($row['ex_sql'] ?? ''));
    if ($before === '') {
        continue;
    }
    $profile = profileSql($before);
    $risks = $profile['risks'];
    if (strpos($before, '{exp_office}') === false) {
        $risks[] = 'missing_exp_office_placeholder';
    }
    $risks = array_values(array_unique($risks));
    $level = riskLevel($risks);
    if ($level === 'none') {
        continue;
    }
    $after = str_replace('{exp_office}', " and t1.hospcode = '$sampleHospcode' ", $before);
    $after = str_replace('tmp_export_exchange', 'tmp_export_exchange_{ex_id}', $after);
    $after = str_replace('chospital', 'chospital_amp', $after);

    $candidates[] = [
        'module' => 'hdcex',
        'record_key' => $row['ex_id'],
        'sql_field' => 'ex_sql',
        'title' => $row['title'],
        'risk_level' => $level,
        'risks' => $risks,
        'reasons' => reasons($risks),
        'proposed_action' => 'คง placeholder {exp_office} และปรับ SQL ให้ output/filter hospcode ชัดเจนก่อนแก้ต้นฉบับ',
        'before_checksum' => checksum($before),
        'after_preview_checksum' => checksum($after),
        'before_sql' => $before,
        'after_sql_preview' => $after,
        'sql_profile' => $profile,
    ];
}

$sqlQueryRows = fetchRows($pdo, "SELECT id, topic, sql_script FROM sqlscript ORDER BY id");
foreach ($sqlQueryRows as $row) {
    $before = trim((string) ($row['sql_script'] ?? ''));
    if ($before === '') {
        continue;
    }
    $profile = profileSql($before);
    $risks = $profile['risks'];
    $level = riskLevel($risks);
    if ($level === 'none') {
        continue;
    }
    $candidates[] = [
        'module' => 'sqlquery',
        'record_key' => (string) $row['id'],
        'sql_field' => 'sql_script',
        'title' => $row['topic'],
        'risk_level' => $level,
        'risks' => $risks,
        'reasons' => reasons($risks),
        'proposed_action' => 'เป็น user-managed SQL script ต้อง review เจ้าของ script ก่อนแก้ไข',
        'before_checksum' => checksum($before),
        'after_preview_checksum' => null,
        'before_sql' => $before,
        'after_sql_preview' => null,
        'sql_profile' => $profile,
    ];
}

$sourceInventory = [
    [
        'module' => 'backend-hdcreportsetup',
        'path' => 'backend/modules/hdcreportsetup/controllers/HdcsqlController.php',
        'database_surface' => 'hdc_rpt_sql',
        'note' => 'เป็นหน้าจัดการ/export/import SQL รายงาน HDC ต้องใช้ candidate report และ backup ก่อนแก้ฐานข้อมูลจริง',
    ],
    [
        'module' => 'hdc-runtime',
        'path' => 'frontend/modules/hdc/views/default/report-id.php',
        'database_surface' => 'hdc_rpt_sql.sql_sum, hdc_rpt_sql.sql_indiv',
        'note' => 'runtime ใช้ ReportSqlHelper สำหรับ sql_indiv filter แล้ว',
    ],
    [
        'module' => 'hdcex-runtime',
        'path' => 'frontend/modules/hdcex/views/default/report-id.php',
        'database_surface' => 'sys_data_exchange.ex_sql',
        'note' => 'runtime ใช้ {exp_office} placeholder และ temp export table',
    ],
    [
        'module' => 'sqlquery',
        'path' => 'modules/sqlquery/controllers/RunqueryController.php',
        'database_surface' => 'sqlscript.sql_script',
        'note' => 'user-managed scripts are audited but not auto-standardized',
    ],
];

$summary = [];
foreach ($candidates as $candidate) {
    $summary[$candidate['module']][$candidate['risk_level']] = ($summary[$candidate['module']][$candidate['risk_level']] ?? 0) + 1;
}

$report = [
    'generated_at' => date('c'),
    'sample_hospcode' => $sampleHospcode,
    'candidate_count' => count($candidates),
    'summary' => $summary,
    'source_inventory' => $sourceInventory,
    'candidates' => $candidates,
];

$stamp = date('Ymd-His');
$jsonFile = rtrim((string) $outDir, "\\/") . "/report-sql-standardization-candidates-$stamp.json";
$latestJson = rtrim((string) $outDir, "\\/") . '/report-sql-standardization-candidates-latest.json';
$markdownFile = rtrim((string) $outDir, "\\/") . '/report-sql-standardization-candidates-latest.md';

file_put_contents($jsonFile, json_encode($report, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
file_put_contents($latestJson, json_encode($report, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

$lines = [
    '# Report SQL Standardization Candidates',
    '',
    '- Generated: ' . $report['generated_at'],
    '- Sample HOSPCODE for dry-run preview: ' . $sampleHospcode,
    '- Candidate count: ' . count($candidates),
    '- Full before/after SQL is stored in `report-sql-standardization-candidates-latest.json` fields `before_sql` and `after_sql_preview`.',
    '',
    '## Summary',
    '',
    '| Module | Risk level | Count |',
    '| --- | --- | ---: |',
];

foreach ($summary as $module => $levels) {
    ksort($levels);
    foreach ($levels as $level => $count) {
        $lines[] = "| $module | $level | $count |";
    }
}

$lines[] = '';
$lines[] = '## Source Inventory';
$lines[] = '';
$lines[] = '| Module | Path | Database surface | Note |';
$lines[] = '| --- | --- | --- | --- |';
foreach ($sourceInventory as $item) {
    $lines[] = '| ' . $item['module'] . ' | `' . $item['path'] . '` | `' . $item['database_surface'] . '` | ' . $item['note'] . ' |';
}

$lines[] = '';
$lines[] = '## Candidates';
$lines[] = '';
$lines[] = '| Module | Key | Field | Risk | Risks | Before SHA256 | After Preview SHA256 |';
$lines[] = '| --- | --- | --- | --- | --- | --- | --- |';
foreach ($candidates as $candidate) {
    $lines[] = '| ' . $candidate['module']
        . ' | `' . $candidate['record_key'] . '`'
        . ' | `' . $candidate['sql_field'] . '`'
        . ' | ' . $candidate['risk_level']
        . ' | `' . implode(', ', $candidate['risks']) . '`'
        . ' | `' . $candidate['before_checksum'] . '`'
        . ' | `' . ($candidate['after_preview_checksum'] ?? '-') . '` |';
}

$lines[] = '';
$lines[] = '## Guardrails';
$lines[] = '';
$lines[] = '- รายงานนี้เป็น dry-run proposal เท่านั้น ห้ามใช้ `after_sql_preview` update ฐานข้อมูลแบบอัตโนมัติ';
$lines[] = '- ก่อนแก้ฐานข้อมูลจริงต้อง export table, review owner, dry-run temp procedure และมี rollback ต่อรายการ';
$lines[] = '- รายการ `no_hospcode` ต้องให้ผู้ดูแลรายงานยืนยันก่อนว่าควรกรองหน่วยบริการหรือยกเว้น';

file_put_contents($markdownFile, implode(PHP_EOL, $lines) . PHP_EOL);

echo "WROTE\t$jsonFile\n";
echo "WROTE\t$latestJson\n";
echo "WROTE\t$markdownFile\n";
foreach ($summary as $module => $levels) {
    ksort($levels);
    echo $module . "\t";
    foreach ($levels as $level => $count) {
        echo "$level=$count ";
    }
    echo "\n";
}
