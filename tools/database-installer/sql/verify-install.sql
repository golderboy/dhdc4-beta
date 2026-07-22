SET NAMES utf8mb4;
USE `dhdc4`;

SET @dhdc4_tables_and_views = (
  SELECT COUNT(*) FROM `information_schema`.`tables` WHERE `table_schema`=DATABASE()
);
SET @dhdc4_routines = (
  SELECT COUNT(*) FROM `information_schema`.`routines` WHERE `routine_schema`=DATABASE()
);

DROP TEMPORARY TABLE IF EXISTS `_dhdc4_verify_targets`;
CREATE TEMPORARY TABLE `_dhdc4_verify_targets` (
  `table_name` varchar(64) NOT NULL,
  PRIMARY KEY (`table_name`)
) ENGINE=MEMORY;

INSERT IGNORE INTO `_dhdc4_verify_targets` (`table_name`)
SELECT `file_name` FROM `sys_files`;

INSERT IGNORE INTO `_dhdc4_verify_targets` (`table_name`)
SELECT `table_name`
FROM `information_schema`.`tables`
WHERE `table_schema` = DATABASE()
  AND `table_type` = 'BASE TABLE'
  AND `table_name` LIKE 'dhdc_tmp\\_%';

INSERT IGNORE INTO `_dhdc4_verify_targets` (`table_name`)
SELECT SUBSTRING(`table_name`, 10)
FROM `information_schema`.`tables`
WHERE `table_schema` = DATABASE()
  AND `table_type` = 'BASE TABLE'
  AND `table_name` LIKE 'dhdc_tmp\\_%'
  AND EXISTS (
    SELECT 1 FROM `information_schema`.`tables` AS base_table
    WHERE base_table.`table_schema` = DATABASE()
      AND base_table.`table_type` = 'BASE TABLE'
      AND base_table.`table_name` = SUBSTRING(`information_schema`.`tables`.`table_name`, 10)
  );

INSERT IGNORE INTO `_dhdc4_verify_targets` (`table_name`)
SELECT DISTINCT columns_info.`table_name`
FROM `information_schema`.`columns` AS columns_info
INNER JOIN `information_schema`.`tables` AS tables_info
  ON tables_info.`table_schema` = columns_info.`table_schema`
 AND tables_info.`table_name` = columns_info.`table_name`
 AND tables_info.`table_type` = 'BASE TABLE'
WHERE columns_info.`table_schema` = DATABASE()
  AND UPPER(columns_info.`column_name`) IN ('CID','PID','HN','AN','PERSON_ID','VISIT_GUID','REFERID','REFERID_PROVINCE','PID_IN');

INSERT IGNORE INTO `_dhdc4_verify_targets` (`table_name`)
SELECT `table_name`
FROM `information_schema`.`tables`
WHERE `table_schema` = DATABASE()
  AND `table_type` = 'BASE TABLE'
  AND (
    `table_name` REGEXP '^(t|s|tmp|tmpz|tmz|err|qof|sb|ws)_' OR
    `table_name` REGEXP '^(temp_|tt_qof_|dhdc_moph_)' OR
    `table_name` REGEXP '^dhdc_module_(hrp|unitcost)(_|$)' OR
    `table_name` REGEXP '^dhdc_population_age_group' OR
    `table_name` IN ('dhdc_input_ancdata','dhdc_procedure_dental') OR
    `table_name` LIKE '%\\_correct' OR
    `table_name` REGEXP '^log_' OR
    `table_name` IN ('hdc_log','information_log','sys_upload_fortythree','sys_count_import','sys_count_import_file','sys_dhdc_count_file','auth_assignment','profile','social_account','token','user')
  );

DELETE FROM `_dhdc4_verify_targets`
WHERE `table_name` IN (
  'sys_files','sys_transform','sys_transform_plus','sys_transform_all','sys_report',
  'sys_report_dhdc','sys_reportcategory','sys_reportcategory_dhdc','sys_report_drop',
  'dhdc_module_s43_file','dhdc_module_student_class','dhdc_income','dhdc_qof_report'
);

DELETE FROM `_dhdc4_verify_targets`
WHERE `table_name` REGEXP '^tmp_export_exchange_[a-f0-9]{32}$';

DROP TEMPORARY TABLE IF EXISTS `_dhdc4_verify_nonempty`;
CREATE TEMPORARY TABLE `_dhdc4_verify_nonempty` (
  `table_name` varchar(64) NOT NULL,
  `row_count` bigint unsigned NOT NULL
) ENGINE=MEMORY;

SET SESSION group_concat_max_len = 1048576;
SELECT GROUP_CONCAT(
  CONCAT(
    'SELECT ', QUOTE(`table_name`), ' AS table_name, COUNT(*) AS row_count FROM `',
    REPLACE(`table_name`, '`', '``'), '` HAVING COUNT(*) <> 0'
  )
  ORDER BY `table_name` SEPARATOR ' UNION ALL '
) INTO @dhdc4_verify_union
FROM `_dhdc4_verify_targets`;
SET @dhdc4_verify_sql = CONCAT('INSERT INTO `_dhdc4_verify_nonempty` ', @dhdc4_verify_union);
PREPARE dhdc4_verify_stmt FROM @dhdc4_verify_sql;
EXECUTE dhdc4_verify_stmt;
DEALLOCATE PREPARE dhdc4_verify_stmt;

SELECT
  'DHDC4_VERIFY' AS marker,
  IF(
    @dhdc4_tables_and_views = 821 AND
    @dhdc4_routines = 512 AND
    (SELECT COUNT(*) FROM `_dhdc4_verify_targets`) = 560 AND
    (SELECT COUNT(*) FROM `_dhdc4_verify_nonempty`) = 0 AND
    (SELECT COUNT(*) FROM `sys_files`) = 43 AND
    (SELECT COUNT(*) FROM `sys_files` WHERE COALESCE(`qc`,0) <> 0) = 0 AND
    (SELECT COUNT(*) FROM `information_schema`.`tables` WHERE `table_schema`=DATABASE() AND `table_name` REGEXP '^tmp_export_exchange_[a-f0-9]{32}$') = 0 AND
    (SELECT COUNT(*) FROM `information_schema`.`routines` WHERE `routine_schema`=DATABASE() AND `definer` <> 'dhdc4@localhost') = 0 AND
    (SELECT COUNT(*) FROM `information_schema`.`views` WHERE `table_schema`=DATABASE() AND `definer` <> 'dhdc4@localhost') = 0 AND
    (SELECT COUNT(*) FROM `information_schema`.`triggers` WHERE `trigger_schema`=DATABASE() AND `definer` <> 'dhdc4@localhost') = 0 AND
    (SELECT COUNT(*) FROM `information_schema`.`events` WHERE `event_schema`=DATABASE() AND `definer` <> 'dhdc4@localhost') = 0 AND
    (SELECT COUNT(*) FROM `last_transform`) = 0 AND
    (SELECT COUNT(*) FROM `last_err_check`) = 0 AND
    (SELECT COUNT(*) FROM `sys_process_running` WHERE `is_running`='false') = 1 AND
    (SELECT COUNT(*) FROM `dhdc_qof_report` WHERE `data_json` IS NOT NULL AND TRIM(`data_json`) <> '') = 0,
    'PASS',
    'FAIL'
  ) AS status,
  @dhdc4_tables_and_views AS tables_and_views,
  @dhdc4_routines AS routines,
  (SELECT COUNT(*) FROM `_dhdc4_verify_targets`) AS empty_target_tables,
  (SELECT COUNT(*) FROM `_dhdc4_verify_nonempty`) AS nonempty_target_tables,
  (SELECT COUNT(*) FROM `sys_files`) AS sys_files_rows,
  (
    (SELECT COUNT(*) FROM `information_schema`.`routines` WHERE `routine_schema`=DATABASE() AND `definer` <> 'dhdc4@localhost') +
    (SELECT COUNT(*) FROM `information_schema`.`views` WHERE `table_schema`=DATABASE() AND `definer` <> 'dhdc4@localhost') +
    (SELECT COUNT(*) FROM `information_schema`.`triggers` WHERE `trigger_schema`=DATABASE() AND `definer` <> 'dhdc4@localhost') +
    (SELECT COUNT(*) FROM `information_schema`.`events` WHERE `event_schema`=DATABASE() AND `definer` <> 'dhdc4@localhost')
  ) AS definer_mismatches;

SELECT `table_name`, `row_count`
FROM `_dhdc4_verify_nonempty`
ORDER BY `table_name`;

DROP TEMPORARY TABLE `_dhdc4_verify_nonempty`;
DROP TEMPORARY TABLE `_dhdc4_verify_targets`;
SET @dhdc4_verify_union = NULL;
SET @dhdc4_verify_sql = NULL;
SET @dhdc4_tables_and_views = NULL;
SET @dhdc4_routines = NULL;
