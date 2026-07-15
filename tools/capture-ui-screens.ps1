param(
    [string]$Php = "php",
    [string]$Npx = "npx",
    [string]$HostName = "127.0.0.1",
    [int]$FrontendPort = 18150,
    [int]$BackendPort = 18151
)

$ErrorActionPreference = "Stop"
$ProgressPreference = "SilentlyContinue"
$root = Split-Path -Parent $PSScriptRoot
$runDir = Join-Path $root "_codex_backup\capture-ui-screens"
$outputDir = Join-Path $root "output\playwright"
New-Item -ItemType Directory -Path $runDir -Force | Out-Null
New-Item -ItemType Directory -Path $outputDir -Force | Out-Null

function Assert-True {
    param([bool]$Condition, [string]$Message)
    if (-not $Condition) {
        throw $Message
    }
}

function Start-FrontendServer {
    $pidFile = Join-Path $runDir "frontend-server.pid"
    if (Test-Path $pidFile) {
        $oldPid = [int](Get-Content $pidFile)
        $oldProcess = Get-Process -Id $oldPid -ErrorAction SilentlyContinue
        if ($oldProcess) {
            Stop-Process -Id $oldPid -Force
        }
    }

    $out = Join-Path $runDir "frontend-server-out.log"
    $err = Join-Path $runDir "frontend-server-err.log"
    $process = Start-Process -FilePath $Php `
        -ArgumentList @("-S", "$HostName`:$FrontendPort", "-t", "frontend\web", "frontend\web\index.php") `
        -WorkingDirectory $root `
        -RedirectStandardOutput $out `
        -RedirectStandardError $err `
        -WindowStyle Hidden `
        -PassThru
    Set-Content -Path $pidFile -Value $process.Id
    return $process.Id
}

function Start-BackendServer {
    $pidFile = Join-Path $runDir "backend-server.pid"
    if (Test-Path $pidFile) {
        $oldPid = [int](Get-Content $pidFile)
        $oldProcess = Get-Process -Id $oldPid -ErrorAction SilentlyContinue
        if ($oldProcess) {
            Stop-Process -Id $oldPid -Force
        }
    }

    $out = Join-Path $runDir "backend-server-out.log"
    $err = Join-Path $runDir "backend-server-err.log"
    $process = Start-Process -FilePath $Php `
        -ArgumentList @("-S", "$HostName`:$BackendPort", "-t", "backend\web", "backend\web\index.php") `
        -WorkingDirectory $root `
        -RedirectStandardOutput $out `
        -RedirectStandardError $err `
        -WindowStyle Hidden `
        -PassThru
    Set-Content -Path $pidFile -Value $process.Id
    return $process.Id
}

function Stop-TestServers {
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

function Capture-Screenshot {
    param(
        [string]$Name,
        [string]$Url,
        [string]$Viewport
    )

    $target = Join-Path $outputDir "$Name.png"
    Write-Output "capture-ui-screens: $Name $Viewport $Url"
    & $Npx --yes playwright screenshot --viewport-size=$Viewport $Url $target | Out-Null
    Assert-True ($LASTEXITCODE -eq 0) "Playwright screenshot failed: $Name"
    Assert-True (Test-Path $target) "Screenshot was not created: $target"
    Assert-True ((Get-Item $target).Length -gt 10000) "Screenshot is unexpectedly small: $target"
}

try {
    Push-Location $root

    Write-Output "capture-ui-screens: check tools"
    & $Php -v | Out-Null
    Assert-True ($LASTEXITCODE -eq 0) "PHP is not available"
    & $Npx --yes playwright --version | Out-Null
    Assert-True ($LASTEXITCODE -eq 0) "Playwright CLI is not available through npx"

    Write-Output "capture-ui-screens: start frontend"
    $frontendPid = Start-FrontendServer
    $backendPid = Start-BackendServer
    Start-Sleep -Seconds 2
    Assert-True ([bool](Get-Process -Id $frontendPid -ErrorAction SilentlyContinue)) "Frontend server did not start"
    Assert-True ([bool](Get-Process -Id $backendPid -ErrorAction SilentlyContinue)) "Backend server did not start"

    $frontendBase = "http://$HostName`:$FrontendPort"
    $backendBase = "http://$HostName`:$BackendPort"
    Capture-Screenshot -Name "import-dashboard-desktop" -Viewport "1366,900" -Url "$frontendBase/import/default/dashboard"
    Capture-Screenshot -Name "plugin-dashboard-desktop" -Viewport "1366,900" -Url "$frontendBase/plugin/default/index"
    Capture-Screenshot -Name "hdc-index-desktop" -Viewport "1366,900" -Url "$frontendBase/hdc/default/index"
    Capture-Screenshot -Name "qof-dashboard-desktop" -Viewport "1366,900" -Url "$frontendBase/Qof/default/index"
    Capture-Screenshot -Name "frontend-login-desktop" -Viewport "1366,900" -Url "$frontendBase/site/login"
    Capture-Screenshot -Name "backend-login-desktop" -Viewport "1366,900" -Url "$backendBase/site/login"
    Capture-Screenshot -Name "frontend-user-login-desktop" -Viewport "1366,900" -Url "$frontendBase/user/login"
    Capture-Screenshot -Name "backend-user-login-desktop" -Viewport "1366,900" -Url "$backendBase/user/security/login"
    Capture-Screenshot -Name "import-dashboard-mobile" -Viewport "390,844" -Url "$frontendBase/import/default/dashboard"

    Write-Output "capture-ui-screens: OK"
} finally {
    Stop-TestServers
    Pop-Location
}
