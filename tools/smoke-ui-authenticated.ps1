param(
    [Parameter(Mandatory = $true)]
    [string]$Username,
    [Parameter(Mandatory = $true)]
    [string]$Password,
    [string]$Php = "php",
    [string]$HostName = "127.0.0.1",
    [int]$FrontendPort = 18180,
    [int]$BackendPort = 18181,
    [string]$DbCli = $(if ($env:DHDC_DB_CLI) { $env:DHDC_DB_CLI } else { "C:\Program Files\MariaDB 12.2\bin\mariadb.exe" }),
    [string]$DbHost = $(if ($env:DHDC_DB_HOST) { $env:DHDC_DB_HOST } else { "127.0.0.1" }),
    [int]$DbPort = $(if ($env:DHDC_DB_PORT) { [int]$env:DHDC_DB_PORT } else { 33061 }),
    [string]$DbUser = $env:DHDC_DB_USER,
    [string]$DbPassword = $env:DHDC_DB_PASSWORD,
    [string]$DbName = $(if ($env:DHDC_DB_NAME) { $env:DHDC_DB_NAME } else { "dhdc4" })
)

$ErrorActionPreference = "Stop"
$ProgressPreference = "SilentlyContinue"
$root = Split-Path -Parent $PSScriptRoot
$previousMariaDbPassword = $env:MYSQL_PWD
$runDir = Join-Path $root "_codex_backup\smoke-ui-authenticated"
New-Item -ItemType Directory -Path $runDir -Force | Out-Null

function Assert-True {
    param([bool]$Condition, [string]$Message)
    if (-not $Condition) {
        throw $Message
    }
}

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

function Get-Url {
    param([string]$Url, [Microsoft.PowerShell.Commands.WebRequestSession]$Session)
    return Invoke-WebRequest -Uri $Url -WebSession $Session -UseBasicParsing -TimeoutSec 30
}

function Get-CsrfValue {
    param([string]$Html, [string]$Name)
    $match = [regex]::Match($Html, 'name="' + [regex]::Escape($Name) + '" value="([^"]+)"')
    Assert-True ($match.Success) "CSRF value not found: $Name"
    return $match.Groups[1].Value
}

function Login-Dektrium {
    param(
        [string]$BaseUrl,
        [string]$LoginPath,
        [string]$CsrfName,
        [Microsoft.PowerShell.Commands.WebRequestSession]$Session
    )

    $loginPage = Get-Url "$BaseUrl$LoginPath" $Session
    Assert-True ($loginPage.Content -match "login-form") "Login page did not render form: $LoginPath"
    $csrf = Get-CsrfValue $loginPage.Content $CsrfName
    $response = Invoke-WebRequest -Uri "$BaseUrl$LoginPath" `
        -WebSession $Session `
        -Method Post `
        -Body @{
            $CsrfName = $csrf
            "login-form[login]" = $Username
            "login-form[password]" = $Password
            "login-form[rememberMe]" = "1"
        } `
        -UseBasicParsing `
        -MaximumRedirection 5 `
        -TimeoutSec 30
    Assert-True ($response.Content -notmatch "Incorrect username or password") "Login failed for $LoginPath"
    return $response
}

function Assert-NoRenderedError {
    param([string]$Content, [string]$Label)
    Assert-True ($Content -notmatch "Database Exception") "Database Exception rendered for $Label"
    Assert-True ($Content -notmatch "PHP Warning") "PHP Warning rendered for $Label"
    Assert-True ($Content -notmatch "PHP Notice") "PHP Notice rendered for $Label"
    Assert-True ($Content -notmatch "Forbidden \\(#403\\)") "Forbidden page rendered for $Label"
    Assert-True ($Content -notmatch "Not Found \\(#404\\)") "Not Found page rendered for $Label"
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

function Get-ProcessInvariants {
    $result = & $DbCli --host=$DbHost --port=$DbPort --user=$DbUser --database=$DbName --batch --raw --skip-column-names --execute="SELECT COALESCE((SELECT CAST(last_time AS CHAR) FROM last_transform LIMIT 1), 'EMPTY'); SELECT COALESCE((SELECT CAST(last_time AS CHAR) FROM last_err_check LIMIT 1), 'EMPTY'); SELECT COALESCE((SELECT is_running FROM sys_process_running LIMIT 1), 'EMPTY');"
    Assert-True ($LASTEXITCODE -eq 0) "Process invariant query failed"
    Assert-True ($result.Count -eq 3) "Unexpected process invariant result"
    return ($result -join "|")
}

function Get-ActiveModuleRoutes {
    $routes = & $DbCli --host=$DbHost --port=$DbPort --user=$DbUser --database=$DbName --batch --raw --skip-column-names --execute="SELECT route FROM sys_dhdc_plugin WHERE type='module' AND status='on' ORDER BY id;"
    Assert-True ($LASTEXITCODE -eq 0) "Active module route query failed"
    return @($routes)
}

try {
    Push-Location $root
    Assert-True (-not [string]::IsNullOrWhiteSpace($DbUser)) "DHDC_DB_USER or -DbUser is required"
    Assert-True (-not [string]::IsNullOrWhiteSpace($DbPassword)) "DHDC_DB_PASSWORD or -DbPassword is required"
    $env:MYSQL_PWD = $DbPassword

    Write-Output "smoke-ui-authenticated: database least privilege"
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

    Write-Output "smoke-ui-authenticated: lint"
    foreach ($file in @(
        "modules\student\views\default\index.php",
        "modules\hrp\views\hrpinput\index.php",
        "modules\Tbmaps\controllers\DefaultController.php",
        "backend\modules\exec\views\default\index.php"
    )) {
        & $Php -l $file | Out-Null
        Assert-True ($LASTEXITCODE -eq 0) "PHP lint failed: $file"
    }

    Write-Output "smoke-ui-authenticated: database invariants before"
    $processBefore = Get-ProcessInvariants

    $frontendAppLog = Join-Path $root "frontend\runtime\logs\app.log"
    $backendAppLog = Join-Path $root "backend\runtime\logs\app.log"
    $frontendLogOffset = Get-FileLineCount $frontendAppLog
    $backendLogOffset = Get-FileLineCount $backendAppLog

    Write-Output "smoke-ui-authenticated: start servers"
    $frontendPid = Start-YiiServer -Name "frontend" -Port $FrontendPort -WebRoot "frontend\web" -Router "frontend\web\index.php"
    $backendPid = Start-YiiServer -Name "backend" -Port $BackendPort -WebRoot "backend\web" -Router "backend\web\index.php"
    Start-Sleep -Seconds 2
    Assert-True ([bool](Get-Process -Id $frontendPid -ErrorAction SilentlyContinue)) "Frontend server did not start"
    Assert-True ([bool](Get-Process -Id $backendPid -ErrorAction SilentlyContinue)) "Backend server did not start"

    $frontendBase = "http://$HostName`:$FrontendPort"
    $backendBase = "http://$HostName`:$BackendPort"
    $frontendSession = New-Object Microsoft.PowerShell.Commands.WebRequestSession
    $backendSession = New-Object Microsoft.PowerShell.Commands.WebRequestSession

    Write-Output "smoke-ui-authenticated: login frontend"
    $frontendLogin = Login-Dektrium -BaseUrl $frontendBase -LoginPath "/user/login" -CsrfName "_csrf-frontend" -Session $frontendSession
    Assert-True ($frontendLogin.BaseResponse.ResponseUri.AbsoluteUri -notmatch "/user/login") "Frontend remained on login page"

    Write-Output "smoke-ui-authenticated: GET student dashboard"
    $student = Get-Url "$frontendBase/student/default/index" $frontendSession
    Assert-True ($student.StatusCode -eq 200) "Unexpected HTTP status for student dashboard"
    Assert-True ($student.Content -match "dhdc-page-header") "Student dashboard header missing"
    Assert-True ($student.Content -match "dhdc-stat-card") "Student dashboard stat cards missing"
    Assert-NoRenderedError $student.Content "student dashboard"

    Write-Output "smoke-ui-authenticated: GET Tbmaps default"
    $tbmapsDefault = Get-Url "$frontendBase/Tbmaps/default/index" $frontendSession
    Assert-True ($tbmapsDefault.StatusCode -eq 200) "Unexpected HTTP status for Tbmaps default"
    Assert-True ($tbmapsDefault.BaseResponse.ResponseUri.AbsoluteUri -match "/Tbmaps/map/") "Tbmaps default did not redirect to map controller"
    Assert-True ($tbmapsDefault.Content -match "id='map'") "Tbmaps map container missing"
    Assert-NoRenderedError $tbmapsDefault.Content "Tbmaps default"

    Write-Output "smoke-ui-authenticated: GET active frontend module routes"
    $activeFrontendRoutes = @(
        "/ehr/default/index",
        "/vaccine/default/index",
        "/special/default/index",
        "/sqlquery/default/index",
        "/gis/default/index",
        "/population/default/index",
        "/hrp/hrpinput/index",
        "/student/default/index",
        "/Tbmaps/map/index"
    )
    $dbActiveFrontendRoutes = Get-ActiveModuleRoutes
    Assert-True (
        (($activeFrontendRoutes | ConvertTo-Json -Compress) -eq ($dbActiveFrontendRoutes | ConvertTo-Json -Compress))
    ) "Authenticated smoke active route list does not match sys_dhdc_plugin"

    foreach ($route in $activeFrontendRoutes) {
        Write-Output "smoke-ui-authenticated: GET active route $route"
        $moduleResponse = Get-Url "$frontendBase$route" $frontendSession
        Assert-True ($moduleResponse.StatusCode -eq 200) "Unexpected HTTP status for active route $route"
        Assert-True ($moduleResponse.BaseResponse.ResponseUri.AbsolutePath -notmatch "/user/login") "Active route redirected to login: $route"
        Assert-NoRenderedError $moduleResponse.Content "active route $route"
    }

    Write-Output "smoke-ui-authenticated: login backend"
    $backendLogin = Login-Dektrium -BaseUrl $backendBase -LoginPath "/user/security/login" -CsrfName "_csrf-backend" -Session $backendSession
    Assert-True ($backendLogin.BaseResponse.ResponseUri.AbsoluteUri -notmatch "/user/security/login") "Backend remained on login page"

    Write-Output "smoke-ui-authenticated: GET backend exec"
    $backendExec = Get-Url "$backendBase/exec/default/index" $backendSession
    Assert-True ($backendExec.StatusCode -eq 200) "Unexpected HTTP status for backend exec"
    Assert-True ($backendExec.Content -match "Process Dashboard") "Backend exec dashboard missing"
    Assert-NoRenderedError $backendExec.Content "backend exec"

    Write-Output "smoke-ui-authenticated: application logs"
    $frontendNewLog = Get-NewFileContent $frontendAppLog $frontendLogOffset
    $backendNewLog = Get-NewFileContent $backendAppLog $backendLogOffset
    Assert-True ($frontendNewLog -notmatch "\[error\]") "New frontend application error log entries were written"
    Assert-True ($backendNewLog -notmatch "\[error\]") "New backend application error log entries were written"

    Write-Output "smoke-ui-authenticated: database invariants after"
    $processAfter = Get-ProcessInvariants
    Assert-True ($processBefore -eq $processAfter) "Authenticated UI smoke changed transform/QC process invariants"

    Write-Output "smoke-ui-authenticated: OK"
} finally {
    Stop-YiiServers
    if ($null -eq $previousMariaDbPassword) {
        Remove-Item Env:MYSQL_PWD -ErrorAction SilentlyContinue
    } else {
        $env:MYSQL_PWD = $previousMariaDbPassword
    }
    Pop-Location
}
