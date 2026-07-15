param(
    [string]$Username = "",
    [string]$Password = "",
    [string]$Php = "php",
    [string]$Npx = "npx",
    [string]$HostName = "127.0.0.1",
    [int]$FrontendPort = 18170
)

$ErrorActionPreference = "Stop"
$ProgressPreference = "SilentlyContinue"
$root = Split-Path -Parent $PSScriptRoot
$runDir = Join-Path $root "_codex_backup\verify-ui-layout"
$outputDir = Join-Path $root "output\playwright\layout"
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

function Stop-TestServer {
    $pidFile = Join-Path $runDir "frontend-server.pid"
    if (Test-Path $pidFile) {
        $serverPid = [int](Get-Content $pidFile)
        $process = Get-Process -Id $serverPid -ErrorAction SilentlyContinue
        if ($process) {
            Stop-Process -Id $serverPid -Force
        }
    }
}

try {
    Push-Location $root

    Write-Output "verify-ui-layout: check tools"
    & $Php -v | Out-Null
    Assert-True ($LASTEXITCODE -eq 0) "PHP is not available"
    & $Npx --yes playwright --version | Out-Null
    Assert-True ($LASTEXITCODE -eq 0) "Playwright CLI is not available through npx"

    Write-Output "verify-ui-layout: start frontend"
    $frontendPid = Start-FrontendServer
    Start-Sleep -Seconds 2
    Assert-True ([bool](Get-Process -Id $frontendPid -ErrorAction SilentlyContinue)) "Frontend server did not start"

    $baseUrl = "http://$HostName`:$FrontendPort"
    Write-Output "verify-ui-layout: inspect $baseUrl"
    $nodeArgs = @(
        "tools\verify-ui-layout.js",
        "--base=$baseUrl",
        "--out=$outputDir"
    )
    if ($Username -and $Password) {
        $nodeArgs += "--username=$Username"
        $nodeArgs += "--password=$Password"
    }
    & node @nodeArgs
    Assert-True ($LASTEXITCODE -eq 0) "Playwright layout verification failed"

    Write-Output "verify-ui-layout: OK"
} finally {
    Stop-TestServer
    Pop-Location
}
