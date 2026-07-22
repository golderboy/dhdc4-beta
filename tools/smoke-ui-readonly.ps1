param(
    [string]$Php = "php",
    [string]$HostName = "127.0.0.1",
    [int]$FrontendPort = 18130,
    [int]$BackendPort = 18131,
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
    [switch]$MasterBaseline
)

$ErrorActionPreference = "Stop"
$ProgressPreference = "SilentlyContinue"
$root = Split-Path -Parent $PSScriptRoot
$previousMariaDbPassword = $env:MYSQL_PWD
$runDir = Join-Path $root "_codex_backup\smoke-ui-readonly"
$frontendTestSessionFile = $null
New-Item -ItemType Directory -Path $runDir -Force | Out-Null

function Start-YiiServer {
    param(
        [string]$Name,
        [int]$Port,
        [string]$WebRoot,
        [string]$Router
    )

    $pidFile = Join-Path $runDir "$Name-server.pid"
    if (Test-Path $pidFile) {
        $oldPid = [int](Get-Content $pidFile)
        $oldProcess = Get-Process -Id $oldPid -ErrorAction SilentlyContinue
        if ($oldProcess) {
            Stop-Process -Id $oldPid -Force
        }
    }

    $out = Join-Path $runDir "$Name-server-out.log"
    $err = Join-Path $runDir "$Name-server-err.log"
    $process = Start-Process -FilePath $Php `
        -ArgumentList @("-S", "$HostName`:$Port", "-t", $WebRoot, $Router) `
        -WorkingDirectory $root `
        -RedirectStandardOutput $out `
        -RedirectStandardError $err `
        -WindowStyle Hidden `
        -PassThru
    Set-Content -Path $pidFile -Value $process.Id
    return $process.Id
}

function Stop-YiiServers {
    foreach ($pidFile in @(
        (Join-Path $runDir "frontend-server.pid"),
        (Join-Path $runDir "backend-server.pid")
    )) {
        if (Test-Path $pidFile) {
            $serverPid = [int](Get-Content $pidFile)
            $process = Get-Process -Id $serverPid -ErrorAction SilentlyContinue
            if ($process) {
                Stop-Process -Id $serverPid -Force
            }
        }
    }
}

function Assert-True {
    param([bool]$Condition, [string]$Message)
    if (-not $Condition) {
        throw $Message
    }
}

function Get-Url {
    param([string]$Url)
    return Invoke-WebRequest -Uri $Url -UseBasicParsing -TimeoutSec 30
}

function Get-UrlWithSession {
    param(
        [string]$Url,
        [Microsoft.PowerShell.Commands.WebRequestSession]$Session
    )
    return Invoke-WebRequest -Uri $Url -WebSession $Session -UseBasicParsing -TimeoutSec 30
}

function Get-FileLineCount {
    param([string]$Path)
    if (Test-Path $Path) {
        return @((Get-Content -Path $Path -ErrorAction SilentlyContinue)).Count
    }
    return 0
}

function Get-NewFileContent {
    param([string]$Path, [int]$LineOffset)
    if (-not (Test-Path $Path)) {
        return ""
    }
    $lines = @(Get-Content -Path $Path -ErrorAction SilentlyContinue)
    if ($lines.Count -le $LineOffset) {
        return ""
    }
    return ($lines | Select-Object -Skip $LineOffset) -join "`n"
}

try {
    Push-Location $root
    Assert-True (-not [string]::IsNullOrWhiteSpace($DbUser)) "DHDC_DB_USER or -DbUser is required"
    Assert-True (-not [string]::IsNullOrWhiteSpace($DbPassword)) "DHDC_DB_PASSWORD or -DbPassword is required"
    $env:MYSQL_PWD = $DbPassword

    Write-Output "smoke-ui-readonly: database least privilege"
    $privilegeRows = @(& $DbCli --host=$DbHost --port=$DbPort --user=$DbUser --database=$DbName --batch --raw --skip-column-names --execute="SELECT CURRENT_USER(); SHOW GRANTS;")
    Assert-True ($LASTEXITCODE -eq 0) "Database privilege query failed"
    Assert-True ($privilegeRows.Count -ge 2) "Database privilege query returned incomplete results"
    $currentDbUser = [string]$privilegeRows[0]
    $grantRows = @($privilegeRows | Select-Object -Skip 1)
    $grantText = $grantRows -join "`n"
    $unsafeGlobalGrants = @($grantRows | Where-Object {
        $_ -match '(?i)\bON\s+\*\.\*' -and
        $_ -notmatch '(?i)^\s*GRANT\s+USAGE\s+ON\s+\*\.\*\s+TO\b'
    })
    Assert-True ($currentDbUser -notmatch '(?i)^root@') "Application database account must not be root"
    Assert-True ($unsafeGlobalGrants.Count -eq 0) "Application database account must not have global privileges other than USAGE"
    Assert-True ($grantText -notmatch '(?i)\bWITH\s+GRANT\s+OPTION\b') "Application database account must not have GRANT OPTION"

    Write-Output "smoke-ui-readonly: lint"
    $lintFiles = @(
        "frontend\modules\import\views\upload\index.php",
        "frontend\modules\import\views\upload\view.php",
        "frontend\modules\import\views\count-file\index.php",
        "frontend\modules\hdc\views\default\index.php",
        "frontend\modules\hdc\views\default\report-group.php",
        "frontend\modules\plugin\views\default\index.php",
        "frontend\views\site\login.php",
        "frontend\views\user\security\login.php",
        "modules\hrp\views\hrpinput\index.php",
        "modules\Qof\views\default\index.php",
        "modules\Qof\views\qof\index.php",
        "backend\modules\exec\views\default\index.php",
        "backend\views\site\login.php",
        "backend\views\user\security\login.php",
        "frontend\modules\import\controllers\AjaxController.php",
        "backend\modules\exec\controllers\TransformController.php",
        "backend\modules\exec\controllers\QcController.php"
    )

    foreach ($file in $lintFiles) {
        & $Php -l $file | Out-Null
        Assert-True ($LASTEXITCODE -eq 0) "PHP lint failed: $file"
    }

    Write-Output "smoke-ui-readonly: migrations"
    $migration = & $Php yii migrate/new
    Assert-True ($LASTEXITCODE -eq 0) "yii migrate/new failed"
    Assert-True (($migration -join "`n") -match "No new migrations found") "There are pending migrations"

    Write-Output "smoke-ui-readonly: database invariants"
    $dbResult = & $DbCli --host=$DbHost --port=$DbPort --user=$DbUser --database=$DbName --batch --raw --skip-column-names --execute="SELECT COUNT(*) FROM sys_upload_fortythree WHERE note2='OK'; SELECT COUNT(*) FROM sys_count_import_file; SELECT is_running FROM sys_process_running LIMIT 1; SELECT COUNT(*) FROM sys_reportcategory_dhdc; SELECT cat_id FROM sys_reportcategory_dhdc ORDER BY cat_id LIMIT 1; SELECT CAST(yearprocess AS UNSIGNED) + 543 FROM pk_byear LIMIT 1;"
    Assert-True ($LASTEXITCODE -eq 0) "Database invariant query failed"
    if ($MasterBaseline) {
        Assert-True ([int]$dbResult[0] -eq 0) "Master baseline contains successful 43-file upload rows"
        Assert-True ([int]$dbResult[1] -eq 0) "Master baseline contains imported-file count rows"
    } else {
        Assert-True ([int]$dbResult[0] -ge 1) "No successful 43-file upload found"
        Assert-True ([int]$dbResult[1] -ge 1) "sys_count_import_file is empty"
    }
    Assert-True ($dbResult[2] -eq "false") "sys_process_running is not false"
    Assert-True ([int]$dbResult[3] -ge 1) "No HDC report category found"
    Assert-True ([string]$dbResult[5] -eq $ExpectedBudgetYear) "pk_byear does not resolve to expected budget year $ExpectedBudgetYear"
    $hdcCategoryId = [uri]::EscapeDataString([string]$dbResult[4])

    if ($MasterBaseline) {
        Write-Output "smoke-ui-readonly: master baseline workflow invariants"
        $workflowSql = @"
SELECT COUNT(*) FROM sys_upload_fortythree;
SELECT COUNT(*) FROM sys_count_import_file;
SELECT COUNT(*) FROM last_transform;
SELECT COUNT(*) FROM last_err_check;
SELECT COUNT(*) FROM hdc_log;
SELECT COUNT(*) FROM sys_check_process WHERE fnc_name IS NOT NULL OR time IS NOT NULL;
SELECT COUNT(*) FROM sys_dhdc_count_file;
"@
        $workflow = & $DbCli --host=$DbHost --port=$DbPort --user=$DbUser --database=$DbName --batch --raw --skip-column-names --execute=$workflowSql
        Assert-True ($LASTEXITCODE -eq 0) "Master baseline invariant query failed"
        Assert-True ($workflow.Count -eq 7) "Master baseline invariant query returned incomplete results"
        foreach ($value in $workflow) {
            Assert-True ([int]$value -eq 0) "Master baseline contains import, transform, QC, or report result data"
        }
    } else {
        Write-Output "smoke-ui-readonly: 43-file workflow invariants"
        $expectedZipSql = $ExpectedImportZip.Replace("'", "''")
        $expectedBudgetYearSql = $ExpectedBudgetYear.Replace("'", "''")
        $workflowSql = @"
SELECT COUNT(*) FROM sys_upload_fortythree WHERE file_name = '$expectedZipSql' AND note2 = 'OK';
SELECT COUNT(*) FROM sys_count_import_file WHERE ZIP_NAME = '$expectedZipSql';
SELECT COALESCE(SUM(TOTAL_RECORD), 0) FROM sys_count_import_file WHERE ZIP_NAME = '$expectedZipSql';
SELECT last_time FROM last_transform LIMIT 1;
SELECT last_time FROM last_err_check LIMIT 1;
SELECT p_name FROM hdc_log ORDER BY id DESC LIMIT 1;
SELECT COUNT(*) FROM hdc_log;
SELECT fnc_name FROM sys_check_process LIMIT 1;
SELECT COUNT(*) FROM sys_dhdc_count_file WHERE b_year = '$expectedBudgetYearSql';
SELECT COALESCE(SUM(total), 0) FROM sys_dhdc_count_file WHERE b_year = '$expectedBudgetYearSql';
"@
        $workflow = & $DbCli --host=$DbHost --port=$DbPort --user=$DbUser --database=$DbName --batch --raw --skip-column-names --execute=$workflowSql
        Assert-True ($LASTEXITCODE -eq 0) "43-file workflow invariant query failed"
        Assert-True ([int]$workflow[0] -ge 1) "Expected import ZIP is not marked OK: $ExpectedImportZip"
        Assert-True ([int]$workflow[1] -eq $ExpectedImportFileRows) "Unexpected imported 43-file row count for $ExpectedImportZip"
        Assert-True ([int]$workflow[2] -eq $ExpectedImportRecords) "Unexpected imported record total for $ExpectedImportZip"
        Assert-True ([string]$workflow[3] -ne "") "last_transform is empty"
        Assert-True ([string]$workflow[4] -ne "") "last_err_check is empty"
        Assert-True ([datetime]$workflow[4] -ge [datetime]$workflow[3]) "last_err_check is older than last_transform"
        Assert-True ($workflow[5] -eq "end") "hdc_log did not end cleanly"
        Assert-True ([int]$workflow[6] -gt 0) "hdc_log is empty"
        Assert-True ($workflow[7] -eq "end") "sys_check_process is not end"
        Assert-True ([int]$workflow[8] -gt 0) "sys_dhdc_count_file has no rows for budget year $ExpectedBudgetYear"
        Assert-True ([int]$workflow[9] -gt 0) "sys_dhdc_count_file total is zero for budget year $ExpectedBudgetYear"
    }

    $frontendAppLog = Join-Path $root "frontend\runtime\logs\app.log"
    $backendAppLog = Join-Path $root "backend\runtime\logs\app.log"
    $frontendLogOffset = Get-FileLineCount $frontendAppLog
    $backendLogOffset = Get-FileLineCount $backendAppLog

    Write-Output "smoke-ui-readonly: start servers"
    $frontendPid = Start-YiiServer -Name "frontend" -Port $FrontendPort -WebRoot "frontend\web" -Router "frontend\web\index.php"
    $backendPid = Start-YiiServer -Name "backend" -Port $BackendPort -WebRoot "backend\web" -Router "backend\web\index.php"
    Start-Sleep -Seconds 2

    Assert-True ([bool](Get-Process -Id $frontendPid -ErrorAction SilentlyContinue)) "Frontend server did not start"
    Assert-True ([bool](Get-Process -Id $backendPid -ErrorAction SilentlyContinue)) "Backend server did not start"

    $frontendUrls = @(
        "http://$HostName`:$FrontendPort/import/default/dashboard",
        "http://$HostName`:$FrontendPort/qc/default/index",
        "http://$HostName`:$FrontendPort/hdc/default/index",
        "http://$HostName`:$FrontendPort/hdc/default/report-group?cat_id=$hdcCategoryId",
        "http://$HostName`:$FrontendPort/plugin/default/index",
        "http://$HostName`:$FrontendPort/Qof/default/index",
        "http://$HostName`:$FrontendPort/Qof/qof/index"
    )

    foreach ($url in $frontendUrls) {
        Write-Output "smoke-ui-readonly: GET $url"
        $response = Get-Url $url
        Assert-True ($response.StatusCode -eq 200) "Unexpected HTTP status for $url"
        Assert-True ($response.Content -match "dhdc-page-header") "New UI header missing for $url"
        Assert-True ($response.Content -match "dhdc-stat-card") "New UI stat cards missing for $url"
        Assert-True ($response.Content -notmatch "Database Exception") "Database Exception rendered for $url"
        Assert-True ($response.Content -notmatch "PHP Warning") "PHP Warning rendered for $url"
    }

    Write-Output "smoke-ui-readonly: create local User test session"
    $sessionJson = & $Php tools/create-yii-test-session.php --role=User --app=frontend
    Assert-True ($LASTEXITCODE -eq 0) "Could not create local frontend test session"
    $sessionInfo = ($sessionJson -join "`n") | ConvertFrom-Json
    Assert-True ([bool]$sessionInfo.canAccessRole) "Local frontend test session lacks User role"
    Assert-True (-not [string]::IsNullOrWhiteSpace([string]$sessionInfo.sessionName)) "Frontend session name is empty"
    Assert-True (-not [string]::IsNullOrWhiteSpace([string]$sessionInfo.sessionId)) "Frontend session id is empty"

    $frontendTestSession = New-Object Microsoft.PowerShell.Commands.WebRequestSession
    $frontendTestCookie = New-Object System.Net.Cookie(
        [string]$sessionInfo.sessionName,
        [string]$sessionInfo.sessionId,
        "/",
        $HostName
    )
    $frontendTestSession.Cookies.Add($frontendTestCookie)
    $phpSessionPath = & $Php -r "echo session_save_path();"
    if (-not [string]::IsNullOrWhiteSpace([string]$phpSessionPath)) {
        $frontendTestSessionFile = Join-Path ([string]$phpSessionPath) ("sess_" + [string]$sessionInfo.sessionId)
    }

    $protectedFrontendUiUrls = @(
        "http://$HostName`:$FrontendPort/import/upload/index",
        "http://$HostName`:$FrontendPort/import/count-file/index"
    )
    if (-not $MasterBaseline) {
        $protectedFrontendUiUrls += "http://$HostName`:$FrontendPort/import/upload/view?id=1"
    }
    foreach ($url in $protectedFrontendUiUrls) {
        Write-Output "smoke-ui-readonly: authenticated GET $url"
        $response = Get-UrlWithSession $url $frontendTestSession
        Assert-True ($response.StatusCode -eq 200) "Unexpected HTTP status for authenticated $url"
        Assert-True ($response.BaseResponse.ResponseUri.AbsolutePath -notmatch "/(?:user/security|site)/login") "Authenticated route redirected to login for $url"
        Assert-True ($response.Content -match "dhdc-page-header") "New UI header missing for authenticated $url"
        Assert-True ($response.Content -match "dhdc-stat-card") "New UI stat cards missing for authenticated $url"
        Assert-True ($response.Content -notmatch "Database Exception") "Database Exception rendered for authenticated $url"
        Assert-True ($response.Content -notmatch "PHP Warning") "PHP Warning rendered for authenticated $url"
    }

    Write-Output "smoke-ui-readonly: GET frontend login"
    $frontendLogin = Get-Url "http://$HostName`:$FrontendPort/site/login"
    Assert-True ($frontendLogin.StatusCode -eq 200) "Unexpected HTTP status for frontend login"
    Assert-True ($frontendLogin.Content -match "dhdc-login-card") "New login UI missing for frontend login"
    Assert-True ($frontendLogin.Content -match "login-form") "Login form id missing for frontend login"
    Assert-True ($frontendLogin.Content -match "LoginForm\[username\]") "Username field missing for frontend login"
    Assert-True ($frontendLogin.Content -match "LoginForm\[password\]") "Password field missing for frontend login"
    Assert-True ($frontendLogin.Content -match "_csrf-frontend") "CSRF field missing for frontend login"

    Write-Output "smoke-ui-readonly: GET frontend user login"
    $frontendUserLogin = Get-Url "http://$HostName`:$FrontendPort/user/login"
    Assert-True ($frontendUserLogin.StatusCode -eq 200) "Unexpected HTTP status for frontend user login"
    Assert-True ($frontendUserLogin.Content -match "dhdc-login-card") "New login UI missing for frontend user login"
    Assert-True ($frontendUserLogin.Content -match "login-form") "Login form id missing for frontend user login"
    Assert-True ($frontendUserLogin.Content -match "login-form\[login\]") "Login field missing for frontend user login"
    Assert-True ($frontendUserLogin.Content -match "login-form\[password\]") "Password field missing for frontend user login"
    Assert-True ($frontendUserLogin.Content -match "_csrf-frontend") "CSRF field missing for frontend user login"

    $protectedFrontendUrls = @(
        "http://$HostName`:$FrontendPort/import/upload/index",
        "http://$HostName`:$FrontendPort/import/upload/view?id=1",
        "http://$HostName`:$FrontendPort/import/count-file/index",
        "http://$HostName`:$FrontendPort/Unitcost/default/index",
        "http://$HostName`:$FrontendPort/student/default/index",
        "http://$HostName`:$FrontendPort/Tbmaps/default/index",
        "http://$HostName`:$FrontendPort/hdc/default/report-id?id=HDC_SMOKE_AUTH_CHECK&rpt=HDC_SMOKE_AUTH_CHECK"
    )

    foreach ($url in $protectedFrontendUrls) {
        Write-Output "smoke-ui-readonly: GET protected $url"
        $response = Get-Url $url
        Assert-True ($response.StatusCode -eq 200) "Unexpected HTTP status for protected $url"
        Assert-True (
            ($response.BaseResponse.ResponseUri.AbsoluteUri -match "/user/security/login") -or
            ($response.BaseResponse.ResponseUri.AbsoluteUri -match "/site/login")
        ) "Protected route did not preserve login redirect for $url"
        Assert-True ($response.Content -notmatch "Database Exception") "Database Exception rendered for protected $url"
        Assert-True ($response.Content -notmatch "PHP Warning") "PHP Warning rendered for protected $url"
    }

    Write-Output "smoke-ui-readonly: GET backend exec"
    $backendResponse = Get-Url "http://$HostName`:$BackendPort/exec/default/index"
    Assert-True ($backendResponse.StatusCode -eq 200) "Unexpected HTTP status for backend exec page"
    Assert-True (
        ($backendResponse.Content -match "Process Dashboard") -or
        ($backendResponse.BaseResponse.ResponseUri.AbsoluteUri -match "/site/login")
    ) "Backend exec page neither rendered dashboard nor preserved auth redirect"
    Assert-True ($backendResponse.Content -notmatch "Database Exception") "Database Exception rendered for backend exec page"

    Write-Output "smoke-ui-readonly: GET backend login"
    $backendLogin = Get-Url "http://$HostName`:$BackendPort/site/login"
    Assert-True ($backendLogin.StatusCode -eq 200) "Unexpected HTTP status for backend login"
    Assert-True ($backendLogin.Content -match "dhdc-login-card") "New login UI missing for backend login"
    Assert-True ($backendLogin.Content -match "login-form") "Login form id missing for backend login"
    Assert-True ($backendLogin.Content -match "LoginForm\[username\]") "Username field missing for backend login"
    Assert-True ($backendLogin.Content -match "LoginForm\[password\]") "Password field missing for backend login"
    Assert-True ($backendLogin.Content -match "_csrf-backend") "CSRF field missing for backend login"

    Write-Output "smoke-ui-readonly: GET backend user login"
    $backendUserLogin = Get-Url "http://$HostName`:$BackendPort/user/security/login"
    Assert-True ($backendUserLogin.StatusCode -eq 200) "Unexpected HTTP status for backend user login"
    Assert-True ($backendUserLogin.Content -match "dhdc-login-card") "New login UI missing for backend user login"
    Assert-True ($backendUserLogin.Content -match "login-form") "Login form id missing for backend user login"
    Assert-True ($backendUserLogin.Content -match "login-form\[login\]") "Login field missing for backend user login"
    Assert-True ($backendUserLogin.Content -match "login-form\[password\]") "Password field missing for backend user login"
    Assert-True ($backendUserLogin.Content -match "_csrf-backend") "CSRF field missing for backend user login"

    Write-Output "smoke-ui-readonly: application logs"
    $frontendNewLog = Get-NewFileContent $frontendAppLog $frontendLogOffset
    $backendNewLog = Get-NewFileContent $backendAppLog $backendLogOffset
    Assert-True ($frontendNewLog -notmatch "\[error\]") "New frontend application error log entries were written"
    Assert-True ($backendNewLog -notmatch "\[error\]") "New backend application error log entries were written"

    Write-Output "smoke-ui-readonly: OK"
} finally {
    Stop-YiiServers
    if ($frontendTestSessionFile -and (Test-Path -LiteralPath $frontendTestSessionFile)) {
        Remove-Item -LiteralPath $frontendTestSessionFile -Force -ErrorAction SilentlyContinue
    }
    if ($null -eq $previousMariaDbPassword) {
        Remove-Item Env:MYSQL_PWD -ErrorAction SilentlyContinue
    } else {
        $env:MYSQL_PWD = $previousMariaDbPassword
    }
    Pop-Location
}
