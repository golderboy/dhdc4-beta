param(
    [Parameter(Mandatory = $true)]
    [string]$BackupDirectory,
    [Parameter(Mandatory = $true)]
    [string]$RestoreDirectory,
    [Parameter(Mandatory = $true)]
    [string]$SecretFile,
    [int]$Port = 33062,
    [string]$MariaDbBin = "C:\Program Files\MariaDB 12.2\bin",
    [switch]$VerifyManifestOnly
)

$ErrorActionPreference = "Stop"
$backup = (Resolve-Path -LiteralPath $BackupDirectory).Path
$backupRoot = (Resolve-Path -LiteralPath (Split-Path -Parent $backup)).Path
$restoreParent = (Resolve-Path -LiteralPath (Split-Path -Parent $RestoreDirectory)).Path
$restore = [IO.Path]::GetFullPath($RestoreDirectory)

if (-not $restore.StartsWith($restoreParent + [IO.Path]::DirectorySeparatorChar, [StringComparison]::OrdinalIgnoreCase)) {
    throw "Restore directory must be a child of its resolved parent."
}
if ($restore.StartsWith($backup + [IO.Path]::DirectorySeparatorChar, [StringComparison]::OrdinalIgnoreCase)) {
    throw "Restore directory must not be inside the backup."
}
if ($backupRoot -ne $restoreParent) {
    throw "Backup and restore directories must share the same controlled parent."
}
if (Test-Path -LiteralPath $restore) {
    throw "Restore directory already exists: $restore"
}

$physical = Join-Path $backup "physical"
$checkpoint = Join-Path $physical "mariadb_backup_checkpoints"
$manifestFile = Join-Path $backup "sha256-manifest.csv"
if (-not (Test-Path -LiteralPath $manifestFile)) {
    throw "Backup SHA-256 manifest is missing."
}
$manifestRows = @(Import-Csv -LiteralPath $manifestFile)
if ($manifestRows.Count -eq 0) {
    throw "Backup SHA-256 manifest is empty."
}
foreach ($row in $manifestRows) {
    $candidate = [IO.Path]::GetFullPath((Join-Path $backup ([string]$row.RelativePath)))
    if (-not $candidate.StartsWith($backup + [IO.Path]::DirectorySeparatorChar, [StringComparison]::OrdinalIgnoreCase)) {
        throw "Manifest path escapes the backup directory: $($row.RelativePath)"
    }
    if (-not (Test-Path -LiteralPath $candidate -PathType Leaf)) {
        throw "Manifest file is missing: $($row.RelativePath)"
    }
    $item = Get-Item -LiteralPath $candidate
    if ($item.Length -ne [long]$row.Length) {
        throw "Manifest file size mismatch: $($row.RelativePath)"
    }
    $actualHash = (Get-FileHash -LiteralPath $candidate -Algorithm SHA256).Hash
    if ($actualHash -ne [string]$row.SHA256) {
        throw "Manifest hash mismatch: $($row.RelativePath)"
    }
}
Write-Output "Backup SHA-256 manifest verification passed ($($manifestRows.Count) files)."

if ($VerifyManifestOnly) {
    return
}

if (-not (Test-Path -LiteralPath $checkpoint)) {
    throw "Backup checkpoint file is missing."
}
if ((Get-Content -LiteralPath $checkpoint -Raw) -notmatch '(?m)^backup_type\s*=\s*log-applied\s*$') {
    throw "Backup is not prepared (backup_type must be log-applied)."
}

$rawSecret = Get-Content -LiteralPath $SecretFile -Raw
function Get-SecretString {
    param([string]$Name)
    $match = [regex]::Match($rawSecret, '\$' + [regex]::Escape($Name) + '\s*=\s*''([^'']+)''')
    if (-not $match.Success) {
        throw "Secret variable is missing: $Name"
    }
    return $match.Groups[1].Value
}

$dbUser = Get-SecretString "db_user"
$dbPassword = Get-SecretString "db_pass"
$backupExe = Join-Path $MariaDbBin "mariadb-backup.exe"
$serverExe = Join-Path $MariaDbBin "mariadbd.exe"
$clientExe = Join-Path $MariaDbBin "mariadb.exe"
$checkExe = Join-Path $MariaDbBin "mariadb-check.exe"
$adminExe = Join-Path $MariaDbBin "mariadb-admin.exe"
foreach ($executable in @($backupExe, $serverExe, $clientExe, $checkExe, $adminExe)) {
    if (-not (Test-Path -LiteralPath $executable)) {
        throw "MariaDB executable is missing: $executable"
    }
}

$dataDirectory = Join-Path $restore "datadir"
$tempDirectory = Join-Path $restore "tmp"
New-Item -ItemType Directory -Path $dataDirectory, $tempDirectory -Force | Out-Null

$ErrorActionPreference = "Continue"
& $backupExe --copy-back --target-dir=$physical --datadir=$dataDirectory `
    1>(Join-Path $restore "copy-back.stdout.log") `
    2>(Join-Path $restore "copy-back.stderr.log")
$copyExit = $LASTEXITCODE
$ErrorActionPreference = "Stop"
if ($copyExit -ne 0) {
    throw "mariadb-backup copy-back failed with exit code $copyExit"
}

$previousPassword = $env:MYSQL_PWD
$env:MYSQL_PWD = $dbPassword
$server = $null
try {
    $serverArguments = @(
        "--no-defaults",
        "--datadir=$dataDirectory",
        "--port=$Port",
        "--bind-address=127.0.0.1",
        "--skip-name-resolve",
        "--event-scheduler=OFF",
        "--read-only",
        "--innodb-page-size=32768",
        "--innodb-buffer-pool-size=256M",
        "--tmpdir=$tempDirectory",
        "--log-error=$(Join-Path $restore 'mariadb-error.log')",
        "--pid-file=$(Join-Path $restore 'mariadb.pid')"
    )
    $server = Start-Process -FilePath $serverExe `
        -ArgumentList $serverArguments `
        -WorkingDirectory $restore `
        -RedirectStandardOutput (Join-Path $restore "server.stdout.log") `
        -RedirectStandardError (Join-Path $restore "server.stderr.log") `
        -WindowStyle Hidden `
        -PassThru

    $ready = $false
    for ($attempt = 0; $attempt -lt 60; $attempt++) {
        Start-Sleep -Seconds 1
        if ($server.HasExited) {
            throw "Restore MariaDB exited early with code $($server.ExitCode)."
        }
        $tcp = New-Object Net.Sockets.TcpClient
        try {
            $async = $tcp.BeginConnect("127.0.0.1", $Port, $null, $null)
            if ($async.AsyncWaitHandle.WaitOne(250)) {
                $tcp.EndConnect($async)
                $ready = $true
                break
            }
        } catch {
            # Retry until the server is ready or the bounded loop expires.
        } finally {
            $tcp.Close()
        }
    }
    if (-not $ready) {
        throw "Restore MariaDB did not become ready."
    }

    $sql = @"
SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='dhdc4';
SELECT TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA='dhdc4' AND TABLE_NAME='t_person_db';
SELECT last_time FROM dhdc4.last_transform LIMIT 1;
SELECT last_time FROM dhdc4.last_err_check LIMIT 1;
SELECT is_running FROM dhdc4.sys_process_running LIMIT 1;
SELECT fnc_name FROM dhdc4.sys_check_process LIMIT 1;
SELECT COUNT(*) FROM dhdc4.user WHERE username LIKE 'dhdc_release_%';
SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='dhdc4' AND TABLE_COLLATION='utf8mb3_uca1400_ai_ci';
"@
    $result = @(& $clientExe --host=127.0.0.1 --port=$Port --user=$dbUser `
        --batch --raw --skip-column-names --execute=$sql)
    if ($LASTEXITCODE -ne 0) {
        throw "Restore invariant query failed."
    }
    if ([int]$result[0] -lt 890) {
        throw "Unexpected dhdc4 table count: $($result[0])"
    }
    if ($result[1] -ne "utf8mb3_general_ci") {
        throw "Restored t_person_db collation mismatch: $($result[1])"
    }
    if ([datetime]$result[3] -lt [datetime]$result[2]) {
        throw "Restored QC is older than Transform."
    }
    if ($result[4] -ne "false" -or $result[5] -ne "end") {
        throw "Restored process state is not clean."
    }
    if ([int]$result[6] -ne 0) {
        throw "Release test accounts remain in the restored database."
    }
    if ([int]$result[7] -ne 0) {
        throw "Restored database contains utf8mb3_uca1400_ai_ci tables."
    }

    $ErrorActionPreference = "Continue"
    & $checkExe --host=127.0.0.1 --port=$Port --user=$dbUser `
        --all-databases --quick --silent `
        1>(Join-Path $restore "mariadb-check.stdout.log") `
        2>(Join-Path $restore "mariadb-check.stderr.log")
    $checkExit = $LASTEXITCODE
    $ErrorActionPreference = "Stop"
    if ($checkExit -ne 0) {
        throw "mariadb-check failed with exit code $checkExit"
    }

    Write-Output (
        "Database restore verification passed: tables={0}, transform={1}, qc={2}, collation={3}" -f `
            $result[0], $result[2], $result[3], $result[1]
    )

    & $adminExe --host=127.0.0.1 --port=$Port --user=$dbUser shutdown | Out-Null
    $server.WaitForExit(30000) | Out-Null
} finally {
    if ($server -and -not $server.HasExited) {
        Stop-Process -Id $server.Id -Force -ErrorAction SilentlyContinue
    }
    if ($null -eq $previousPassword) {
        Remove-Item Env:MYSQL_PWD -ErrorAction SilentlyContinue
    } else {
        $env:MYSQL_PWD = $previousPassword
    }
}
