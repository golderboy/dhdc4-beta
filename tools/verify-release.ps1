param(
    [Parameter(Mandatory = $true)]
    [string]$Username,
    [Parameter(Mandatory = $true)]
    [string]$Password,
    [string]$Php = "php",
    [string]$Npx = "npx",
    [string]$HostName = "127.0.0.1",
    [string]$DbCli = "C:\Program Files\MariaDB 12.2\bin\mariadb.exe",
    [string]$DbHost = "127.0.0.1",
    [int]$DbPort = 33061,
    [string]$DbUser = "root",
    [string]$DbPassword = "REDACTED_SECRET_8D969EEF6ECA",
    [string]$DbName = "dhdc4",
    [string]$ExpectedImportZip = "F43_11207_20260601133018.zip",
    [int]$ExpectedImportFileRows = 52,
    [int]$ExpectedImportRecords = 90042,
    [string]$ExpectedBudgetYear = "2569",
    [switch]$SkipScreenshots
)

$ErrorActionPreference = "Stop"
$ProgressPreference = "SilentlyContinue"
$root = Split-Path -Parent $PSScriptRoot

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
"@
        $result = & $DbCli --host=$DbHost --port=$DbPort --user=$DbUser --password=$DbPassword --database=$DbName --batch --raw --skip-column-names --execute=$sql
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
    }

    Write-Output "verify-release: OK"
} finally {
    Pop-Location
}
