param(
    [Parameter(Mandatory = $true)]
    [string]$Username,
    [Parameter(Mandatory = $true)]
    [string]$Password,
    [string]$Php = "php",
    [string]$Npx = "npx",
    [string]$HostName = "127.0.0.1",
    [string]$DbCli = $(if ($env:DHDC_DB_CLI) { $env:DHDC_DB_CLI } else { "C:\Program Files\MariaDB 12.2\bin\mariadb.exe" }),
    [string]$DbHost = $(if ($env:DHDC_DB_HOST) { $env:DHDC_DB_HOST } else { "127.0.0.1" }),
    [int]$DbPort = $(if ($env:DHDC_DB_PORT) { [int]$env:DHDC_DB_PORT } else { 33061 }),
    [string]$DbUser = $env:DHDC_DB_USER,
    [string]$DbPassword = $env:DHDC_DB_PASSWORD,
    [string]$DbName = $(if ($env:DHDC_DB_NAME) { $env:DHDC_DB_NAME } else { "dhdc4" }),
    [string]$ExpectedImportZip = "F43_11207_20260601133018.zip",
    [int]$ExpectedImportFileRows = 52,
    [int]$ExpectedImportRecords = 90042,
    [string]$ExpectedBudgetYear = "2569",
    [switch]$SkipScreenshots
)

$ErrorActionPreference = "Stop"
$ProgressPreference = "SilentlyContinue"
$root = Split-Path -Parent $PSScriptRoot
$previousMariaDbPassword = $env:MYSQL_PWD

function Assert-True {
    param([bool]$Condition, [string]$Message)
    if (-not $Condition) {
        throw $Message
    }
}

function Invoke-Step {
    param([string]$Name, [scriptblock]$Block)
    Write-Output "verify-release: $Name"
    & $Block
}

try {
    Push-Location $root
    Assert-True (-not [string]::IsNullOrWhiteSpace($DbUser)) "DHDC_DB_USER or -DbUser is required"
    Assert-True (-not [string]::IsNullOrWhiteSpace($DbPassword)) "DHDC_DB_PASSWORD or -DbPassword is required"
    $env:MYSQL_PWD = $DbPassword

    Invoke-Step "readonly smoke" {
        & ".\tools\smoke-ui-readonly.ps1" `
            -Php $Php `
            -HostName $HostName `
            -DbCli $DbCli `
            -DbHost $DbHost `
            -DbPort $DbPort `
            -DbUser $DbUser `
            -DbPassword $DbPassword `
            -DbName $DbName `
            -ExpectedImportZip $ExpectedImportZip `
            -ExpectedImportFileRows $ExpectedImportFileRows `
            -ExpectedImportRecords $ExpectedImportRecords `
            -ExpectedBudgetYear $ExpectedBudgetYear
        Assert-True ($LASTEXITCODE -eq 0) "readonly smoke failed"
    }

    Invoke-Step "authenticated smoke" {
        & ".\tools\smoke-ui-authenticated.ps1" `
            -Username $Username `
            -Password $Password `
            -Php $Php `
            -HostName $HostName `
            -DbCli $DbCli `
            -DbHost $DbHost `
            -DbPort $DbPort `
            -DbUser $DbUser `
            -DbPassword $DbPassword `
            -DbName $DbName
        Assert-True ($LASTEXITCODE -eq 0) "authenticated smoke failed"
    }

    if (-not $SkipScreenshots) {
        Invoke-Step "browser screenshots" {
            & ".\tools\capture-ui-screens.ps1" `
                -Php $Php `
                -Npx $Npx `
                -HostName $HostName
            Assert-True ($LASTEXITCODE -eq 0) "browser screenshot capture failed"
        }

        Invoke-Step "frontend layout verification" {
            & ".\tools\verify-ui-layout.ps1" `
                -Username $Username `
                -Password $Password `
                -Php $Php `
                -Npx $Npx `
                -HostName $HostName
            Assert-True ($LASTEXITCODE -eq 0) "frontend layout verification failed"
        }
    }

    Invoke-Step "final database invariants" {
        $expectedZipSql = $ExpectedImportZip.Replace("'", "''")
        $expectedBudgetYearSql = $ExpectedBudgetYear.Replace("'", "''")
        $sql = @"
SELECT COUNT(*) FROM sys_upload_fortythree WHERE file_name = '$expectedZipSql' AND note2 = 'OK';
SELECT COUNT(*) FROM sys_count_import_file WHERE ZIP_NAME = '$expectedZipSql';
SELECT COALESCE(SUM(TOTAL_RECORD), 0) FROM sys_count_import_file WHERE ZIP_NAME = '$expectedZipSql';
SELECT CAST(yearprocess AS UNSIGNED) + 543 FROM pk_byear LIMIT 1;
SELECT COUNT(*) FROM sys_dhdc_count_file WHERE b_year = '$expectedBudgetYearSql';
SELECT COALESCE(SUM(total), 0) FROM sys_dhdc_count_file WHERE b_year = '$expectedBudgetYearSql';
SELECT last_time FROM last_transform LIMIT 1;
SELECT last_time FROM last_err_check LIMIT 1;
SELECT is_running FROM sys_process_running LIMIT 1;
SELECT p_name FROM hdc_log ORDER BY id DESC LIMIT 1;
SELECT fnc_name FROM sys_check_process LIMIT 1;
SELECT @@global.character_set_collations;
SELECT TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$DbName' AND TABLE_NAME = 't_person_db';
SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$DbName' AND TABLE_COLLATION = 'utf8mb3_uca1400_ai_ci';
"@
        $result = & $DbCli --host=$DbHost --port=$DbPort --user=$DbUser --database=$DbName --batch --raw --skip-column-names --execute=$sql
        Assert-True ($LASTEXITCODE -eq 0) "final database invariant query failed"
        Assert-True ([int]$result[0] -ge 1) "expected import ZIP is not OK"
        Assert-True ([int]$result[1] -eq $ExpectedImportFileRows) "unexpected imported file count"
        Assert-True ([int]$result[2] -eq $ExpectedImportRecords) "unexpected imported record total"
        Assert-True ([string]$result[3] -eq $ExpectedBudgetYear) "pk_byear does not resolve to expected budget year $ExpectedBudgetYear"
        Assert-True ([int]$result[4] -gt 0) "sys_dhdc_count_file has no rows for budget year $ExpectedBudgetYear"
        Assert-True ([int]$result[5] -gt 0) "sys_dhdc_count_file total is zero for budget year $ExpectedBudgetYear"
        Assert-True ([string]$result[6] -ne "") "last_transform is empty"
        Assert-True ([string]$result[7] -ne "") "last_err_check is empty"
        Assert-True ([datetime]$result[7] -ge [datetime]$result[6]) "last_err_check is older than last_transform"
        Assert-True ($result[8] -eq "false") "sys_process_running is not false"
        Assert-True ($result[9] -eq "end") "hdc_log did not end cleanly"
        Assert-True ($result[10] -eq "end") "sys_check_process is not end"
        Assert-True ([string]$result[11] -like "*utf8mb3=utf8mb3_general_ci*") "MariaDB does not map utf8mb3 to utf8mb3_general_ci"
        Assert-True ($result[12] -eq "utf8mb3_general_ci") "t_person_db collation is not utf8mb3_general_ci"
        Assert-True ([int]$result[13] -eq 0) "database contains utf8mb3_uca1400_ai_ci tables"
    }

    Write-Output "verify-release: OK"
} finally {
    if ($null -eq $previousMariaDbPassword) {
        Remove-Item Env:MYSQL_PWD -ErrorAction SilentlyContinue
    } else {
        $env:MYSQL_PWD = $previousMariaDbPassword
    }
    Pop-Location
}
