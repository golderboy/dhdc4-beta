param(
    [string]$PackageDirectory = $PSScriptRoot,
    [string]$DbHost = $(if ($env:DHDC4_DB_HOST) { $env:DHDC4_DB_HOST } else { 'localhost' }),
    [int]$DbPort = $(if ($env:DHDC4_DB_PORT) { [int]$env:DHDC4_DB_PORT } else { 0 }),
    [string]$DbName = $(if ($env:DHDC4_DB_NAME) { $env:DHDC4_DB_NAME } else { 'dhdc4' }),
    [string]$RootUser = $(if ($env:DHDC4_DB_ROOT_USER) { $env:DHDC4_DB_ROOT_USER } else { 'root' }),
    [string]$MariaDbBin = $env:DHDC4_MARIADB_BIN,
    [string]$BackupDirectory,
    [switch]$DryRun,
    [switch]$CheckConnection,
    [switch]$Recreate,
    [switch]$ConfirmRecreate,
    [switch]$AllowPasswordlessRoot
)

$ErrorActionPreference = 'Stop'
Set-StrictMode -Version Latest

if ($DbPort -lt 0 -or $DbPort -gt 65535) {
    throw 'DbPort must be an integer from 1 to 65535.'
}
if ($DbPort -eq 0 -and -not $DryRun) {
    throw 'MariaDB port was not supplied. Set -DbPort to the value returned by SELECT @@port or set DHDC4_DB_PORT.'
}

function Get-PlainSecret {
    param([string]$EnvironmentName, [string]$Prompt, [switch]$AllowEmpty)
    $environmentValue = [Environment]::GetEnvironmentVariable($EnvironmentName)
    if ($null -ne $environmentValue) {
        if (-not $AllowEmpty -and [string]::IsNullOrWhiteSpace($environmentValue)) {
            throw "$EnvironmentName must not be empty."
        }
        return $environmentValue
    }
    $secure = Read-Host $Prompt -AsSecureString
    $pointer = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($secure)
    try {
        $plain = [Runtime.InteropServices.Marshal]::PtrToStringBSTR($pointer)
    }
    finally {
        [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($pointer)
    }
    if (-not $AllowEmpty -and [string]::IsNullOrWhiteSpace($plain)) {
        throw "$EnvironmentName must not be empty."
    }
    return $plain
}

function Quote-ProcessArgument {
    param([string]$Value)
    if ($Value -notmatch '[\s"]') {
        return $Value
    }
    return '"' + ($Value -replace '(\\*)"', '$1$1\"' -replace '(\\+)$', '$1$1') + '"'
}

function New-DbProcessInfo {
    param([string]$Executable, [string[]]$Arguments, [string]$Password, [switch]$RedirectInput)
    $info = New-Object Diagnostics.ProcessStartInfo
    $info.FileName = $Executable
    $info.Arguments = (($Arguments | ForEach-Object { Quote-ProcessArgument $_ }) -join ' ')
    $info.UseShellExecute = $false
    $info.CreateNoWindow = $true
    $info.RedirectStandardOutput = $true
    $info.RedirectStandardError = $true
    $info.RedirectStandardInput = [bool]$RedirectInput
    $utf8 = New-Object Text.UTF8Encoding($false)
    $info.StandardOutputEncoding = $utf8
    $info.StandardErrorEncoding = $utf8
    if ([string]::IsNullOrEmpty($Password)) {
        $null = $info.EnvironmentVariables.Remove('MYSQL_PWD')
    }
    else {
        $info.EnvironmentVariables['MYSQL_PWD'] = $Password
    }
    return $info
}

function Invoke-MariaDbInput {
    param(
        [string]$Executable,
        [string[]]$Arguments,
        [string]$Password,
        [string]$PrefixSql = '',
        [string[]]$InputFiles = @()
    )
    $process = New-Object Diagnostics.Process
    $process.StartInfo = New-DbProcessInfo $Executable $Arguments $Password -RedirectInput
    if (-not $process.Start()) {
        throw "Unable to start MariaDB client: $Executable"
    }
    $stdoutTask = $process.StandardOutput.ReadToEndAsync()
    $stderrTask = $process.StandardError.ReadToEndAsync()
    $inputStream = $process.StandardInput.BaseStream
    try {
        if ($PrefixSql) {
            $prefixBytes = (New-Object Text.UTF8Encoding($false)).GetBytes($PrefixSql + "`n")
            $inputStream.Write($prefixBytes, 0, $prefixBytes.Length)
        }
        foreach ($file in $InputFiles) {
            $reader = [IO.File]::OpenRead($file)
            try {
                $reader.CopyTo($inputStream)
                $inputStream.WriteByte(10)
            }
            finally {
                $reader.Dispose()
            }
        }
    }
    finally {
        $inputStream.Close()
    }
    $process.WaitForExit()
    $stdout = $stdoutTask.Result
    $stderr = $stderrTask.Result
    if ($process.ExitCode -ne 0) {
        throw "MariaDB client failed with exit code $($process.ExitCode): $stderr"
    }
    if ($stderr) {
        Write-Verbose $stderr
    }
    return $stdout
}

function Invoke-MariaDbDump {
    param([string]$Executable, [string[]]$Arguments, [string]$Password)
    $process = New-Object Diagnostics.Process
    $process.StartInfo = New-DbProcessInfo $Executable $Arguments $Password
    if (-not $process.Start()) {
        throw "Unable to start MariaDB dump: $Executable"
    }
    $stdoutTask = $process.StandardOutput.ReadToEndAsync()
    $stderrTask = $process.StandardError.ReadToEndAsync()
    $process.WaitForExit()
    $null = $stdoutTask.Result
    $stderr = $stderrTask.Result
    if ($process.ExitCode -ne 0) {
        throw "MariaDB backup failed with exit code $($process.ExitCode): $stderr"
    }
}

function Resolve-MariaDbBin {
    param([string]$Requested)
    $candidates = @()
    if ($Requested) { $candidates += $Requested }
    $command = Get-Command mariadb.exe -ErrorAction SilentlyContinue
    if ($command) { $candidates += (Split-Path -Parent $command.Source) }
    $candidates += @(
        'C:\Program Files\MariaDB 12.2\bin',
        'C:\xampp\mysql\bin',
        'D:\xampp\mysql\bin'
    )
    foreach ($candidate in $candidates | Select-Object -Unique) {
        if ((Test-Path -LiteralPath (Join-Path $candidate 'mariadb.exe')) -and
            (Test-Path -LiteralPath (Join-Path $candidate 'mariadb-dump.exe'))) {
            return (Resolve-Path -LiteralPath $candidate).Path
        }
    }
    throw 'MariaDB client tools were not found. Set DHDC4_MARIADB_BIN to the MariaDB bin directory.'
}

function Resolve-RequiredExecutable {
    param([string]$EnvironmentName, [string[]]$CommandNames, [string[]]$Candidates, [string]$Description)
    $paths = @()
    $environmentPath = [Environment]::GetEnvironmentVariable($EnvironmentName)
    if ($environmentPath) { $paths += $environmentPath }
    foreach ($commandName in $CommandNames) {
        $command = Get-Command $commandName -ErrorAction SilentlyContinue
        if ($command) { $paths += $command.Source }
    }
    $paths += $Candidates
    foreach ($path in $paths | Select-Object -Unique) {
        if ($path -and (Test-Path -LiteralPath $path -PathType Leaf)) {
            return (Resolve-Path -LiteralPath $path).Path
        }
    }
    throw "$Description was not found. Set $EnvironmentName to its executable path."
}

function Get-DatabaseArguments {
    param([string]$User, [switch]$WithDatabase)
    $arguments = @(
        '--protocol=TCP',
        "--host=$DbHost",
        "--port=$DbPort",
        "--user=$User",
        '--default-character-set=utf8mb4',
        '--binary-mode',
        '--batch',
        '--raw',
        '--skip-column-names',
        '--max-allowed-packet=1G'
    )
    if ($WithDatabase) { $arguments += "--database=$DbName" }
    return $arguments
}

$package = (Resolve-Path -LiteralPath $PackageDirectory).Path
$logDirectory = if ($env:DHDC4_INSTALL_LOG_DIR) {
    $env:DHDC4_INSTALL_LOG_DIR
} else {
    Join-Path (Split-Path -Parent $package) 'install-logs'
}
New-Item -ItemType Directory -Path $logDirectory -Force | Out-Null
$logDirectory = (Resolve-Path -LiteralPath $logDirectory).Path
$logPath = Join-Path $logDirectory ("dhdc4-database-install-" + (Get-Date -Format 'yyyyMMdd-HHmmss') + '.log')
[IO.File]::WriteAllText($logPath, '', (New-Object Text.UTF8Encoding($false)))

function Write-InstallMessage {
    param([string]$Message)
    Write-Output $Message
    [IO.File]::AppendAllText(
        $script:logPath,
        $Message + [Environment]::NewLine,
        (New-Object Text.UTF8Encoding($false))
    )
}
Write-InstallMessage "Install log: $logPath"
$manifestPath = Join-Path $package 'manifest.json'
$checksumPath = Join-Path $package 'SHA256SUMS'
$orderPath = Join-Path $package 'install-order.txt'
foreach ($required in @($manifestPath, $checksumPath, $orderPath)) {
    if (-not (Test-Path -LiteralPath $required -PathType Leaf)) {
        throw "Required package file is missing: $required"
    }
}
if ($DbName -notmatch '^[A-Za-z0-9_]+$') { throw 'Unsafe database name.' }
if ($DbHost -notin @('localhost', '127.0.0.1', '::1')) {
    throw "This installer creates 'dhdc4'@'localhost' and must run against the local database server."
}

$manifest = Get-Content -Raw -LiteralPath $manifestPath | ConvertFrom-Json
if ($manifest.database -ne $DbName -or $manifest.owner -ne 'dhdc4@localhost') {
    throw 'Package database or owner does not match the requested installation target.'
}

$checksumRows = Get-Content -LiteralPath $checksumPath | Where-Object { $_ -match '\S' }
foreach ($row in $checksumRows) {
    if ($row -notmatch '^([0-9a-fA-F]{64})  ([^\\]+)$') { throw "Invalid SHA256SUMS row: $row" }
    $relative = $Matches[2]
    if ($relative -match '(^|/)\.\.(/|$)' -or [IO.Path]::IsPathRooted($relative)) {
        throw "Unsafe checksum path: $relative"
    }
    $target = Join-Path $package ($relative -replace '/', [IO.Path]::DirectorySeparatorChar)
    if (-not (Test-Path -LiteralPath $target -PathType Leaf)) { throw "Package file is missing: $relative" }
    $actual = (Get-FileHash -Algorithm SHA256 -LiteralPath $target).Hash.ToLowerInvariant()
    if ($actual -ne $Matches[1].ToLowerInvariant()) { throw "Checksum mismatch: $relative" }
}

$order = @(Get-Content -LiteralPath $orderPath | Where-Object { $_ -match '\S' })
$sqlParts = @()
foreach ($relative in $order) {
    if ($relative -notmatch '^sql/[A-Za-z0-9._-]+\.sql$') { throw "Unsafe install-order path: $relative" }
    $path = Join-Path $package ($relative -replace '/', [IO.Path]::DirectorySeparatorChar)
    if (-not (Test-Path -LiteralPath $path -PathType Leaf)) { throw "SQL part is missing: $relative" }
    $sqlParts += $path
}
if ($sqlParts.Count -ne @($manifest.files).Count) { throw 'install-order and manifest file counts differ.' }

$mariaDbDirectory = Resolve-MariaDbBin $MariaDbBin
$client = Join-Path $mariaDbDirectory 'mariadb.exe'
$dump = Join-Path $mariaDbDirectory 'mariadb-dump.exe'
& $client --version
if ($LASTEXITCODE -ne 0) { throw 'Unable to execute the MariaDB client.' }

$phpExecutable = Resolve-RequiredExecutable 'DHDC4_PHP_EXE' @('php.exe') @(
    'C:\xampp\php\php.exe',
    'D:\xampp\php\php.exe'
) 'PHP CLI'
$phpPreflight = @'
$required = ['curl','fileinfo','gd','intl','mbstring','openssl','pdo_mysql','zip'];
$missing = array_values(array_filter($required, static fn(string $name): bool => !extension_loaded($name)));
if (version_compare(PHP_VERSION, '8.1.0', '<') || $missing) {
    fwrite(STDERR, 'PHP preflight failed: version=' . PHP_VERSION . ' missing=' . implode(',', $missing) . PHP_EOL);
    exit(1);
}
echo 'PHP preflight: version=' . PHP_VERSION . ' extensions=PASS' . PHP_EOL;
'@
& $phpExecutable -r $phpPreflight
if ($LASTEXITCODE -ne 0) { throw 'PHP version or extension preflight failed.' }

$apacheExecutable = Resolve-RequiredExecutable 'DHDC4_APACHE_EXE' @('httpd.exe', 'apache.exe') @(
    'C:\xampp\apache\bin\httpd.exe',
    'D:\xampp\apache\bin\httpd.exe',
    'C:\Apache24\bin\httpd.exe'
) 'Apache HTTP Server'
& $apacheExecutable -v
if ($LASTEXITCODE -ne 0) { throw 'Apache version preflight failed.' }

$packageBytes = (Get-ChildItem -LiteralPath $package -Recurse -File | Measure-Object Length -Sum).Sum
$drive = Get-PSDrive -Name ([IO.Path]::GetPathRoot($package).TrimEnd('\').TrimEnd(':'))
if ($drive.Free -lt ($packageBytes * 3)) { throw 'Free disk space is below three times the uncompressed package size.' }

Write-InstallMessage "Package verification passed: $($sqlParts.Count) SQL parts, database=$DbName, owner=dhdc4@localhost"
if ($DryRun) {
    $portDisplay = if ($DbPort -eq 0) { 'not-supplied' } else { [string]$DbPort }
    Write-InstallMessage "Connection configuration: protocol=TCP host=$DbHost port=$portDisplay"
    Write-InstallMessage 'Dry-run completed. No database connection or mutation was attempted.'
    return
}

$rootPassword = if ($AllowPasswordlessRoot) {
    ''
} else {
    Get-PlainSecret 'DHDC4_DB_ROOT_PASSWORD' "MariaDB administrator password for '$RootUser'" -AllowEmpty
}
$rootArguments = Get-DatabaseArguments $RootUser
$connectionOutput = Invoke-MariaDbInput $client $rootArguments $rootPassword -PrefixSql "SELECT CONCAT(@@port, '|', @@socket);"
$connectionParts = $connectionOutput.Trim().Split('|', 2)
if ($connectionParts.Count -ne 2 -or $connectionParts[0] -notmatch '^\d+$') {
    throw "MariaDB returned invalid connection information: $connectionOutput"
}
$detectedPort = [int]$connectionParts[0]
if ($detectedPort -ne $DbPort) {
    throw "Configured DbPort=$DbPort does not match the running MariaDB port $detectedPort. Nothing was changed."
}
Write-InstallMessage "MariaDB connection verified: protocol=TCP host=$DbHost port=$detectedPort socket=$($connectionParts[1]) administrator=$RootUser"
Write-InstallMessage "Use DHDC_DB_PORT='$detectedPort' in the DHDC4 environment configuration."

$existsOutput = Invoke-MariaDbInput $client $rootArguments $rootPassword -PrefixSql "SELECT COUNT(*) FROM information_schema.SCHEMATA WHERE SCHEMA_NAME='$DbName';"
$databaseExists = [int]$existsOutput.Trim() -gt 0

$settingsOutput = Invoke-MariaDbInput $client $rootArguments $rootPassword -PrefixSql "SELECT CONCAT(@@GLOBAL.local_infile, '|', @@GLOBAL.event_scheduler);"
$settings = $settingsOutput.Trim().Split('|')
if ($settings.Count -ne 2 -or $settings[0] -notin @('1', 'ON')) {
    throw 'MariaDB local_infile must be enabled before installing DHDC4.'
}
if ($CheckConnection) {
    Write-InstallMessage "Connection check passed: database_exists=$databaseExists local_infile=ON event_scheduler=$($settings[1]). No database mutation was attempted."
    return
}

if ($databaseExists) {
    if (-not $Recreate) {
        throw "Database '$DbName' already exists. Nothing was changed. Use -Recreate to back it up and reinstall."
    }
    if (-not $ConfirmRecreate) {
        $confirmation = Read-Host "Type RECREATE-$DbName to back up and replace the database"
        if ($confirmation -ne "RECREATE-$DbName") { throw 'Recreate confirmation did not match. Nothing was changed.' }
    }
}

$ownerPassword = Get-PlainSecret 'DHDC4_DB_OWNER_PASSWORD' "Password for 'dhdc4'@'localhost'"
if ($ownerPassword.Length -lt 32) {
    throw "Password for 'dhdc4'@'localhost' must contain at least 32 characters."
}

Invoke-MariaDbInput $client $rootArguments $rootPassword -PrefixSql 'SET GLOBAL event_scheduler=OFF;' | Out-Null
Write-InstallMessage "MariaDB preflight: local_infile=ON, event_scheduler changed from $($settings[1]) to OFF"

if ($databaseExists) {
    if (-not $BackupDirectory) {
        $BackupDirectory = Join-Path (Split-Path -Parent $package) 'database-backups'
    }
    New-Item -ItemType Directory -Path $BackupDirectory -Force | Out-Null
    $BackupDirectory = (Resolve-Path -LiteralPath $BackupDirectory).Path
    $backupFile = Join-Path $BackupDirectory ("$DbName-before-recreate-" + (Get-Date -Format 'yyyyMMdd-HHmmss') + '.sql')
    $dumpArguments = @(
        '--protocol=TCP', "--host=$DbHost", "--port=$DbPort", "--user=$RootUser",
        '--default-character-set=utf8mb4', '--hex-blob', '--routines', '--events', '--triggers',
        '--databases', $DbName, "--result-file=$backupFile"
    )
    Invoke-MariaDbDump $dump $dumpArguments $rootPassword
    if (-not (Test-Path -LiteralPath $backupFile) -or (Get-Item -LiteralPath $backupFile).Length -eq 0) {
        throw 'Pre-recreate database backup was not created.'
    }
    $backupHash = (Get-FileHash -Algorithm SHA256 -LiteralPath $backupFile).Hash.ToLowerInvariant()
    [IO.File]::WriteAllText("$backupFile.sha256", "$backupHash  $([IO.Path]::GetFileName($backupFile))`n", (New-Object Text.UTF8Encoding($false)))
    Invoke-MariaDbInput $client $rootArguments $rootPassword -PrefixSql "DROP DATABASE ``$DbName``;" | Out-Null
    Write-InstallMessage "Existing database backup: $backupFile"
}

$ownerBytes = (New-Object Text.UTF8Encoding($false)).GetBytes($ownerPassword)
$ownerHex = ([BitConverter]::ToString($ownerBytes)).Replace('-', '')
$accountSql = Join-Path $package 'admin\create-owner-and-grants.sql'
$accountPrefix = "SET @dhdc4_owner_password=CONVERT(0x$ownerHex USING utf8mb4);"
Invoke-MariaDbInput $client $rootArguments $rootPassword -PrefixSql $accountPrefix -InputFiles @($accountSql) | Out-Null

Write-InstallMessage 'Importing SQL parts in one MariaDB session...'
Invoke-MariaDbInput $client $rootArguments $rootPassword -InputFiles $sqlParts | Out-Null

$verifySql = Join-Path $package 'admin\verify-install.sql'
$verification = Invoke-MariaDbInput $client (Get-DatabaseArguments $RootUser -WithDatabase) $rootPassword -InputFiles @($verifySql)
$verificationLine = @($verification -split "`r?`n" | Where-Object { $_ -match '^DHDC4_VERIFY\s' }) | Select-Object -Last 1
if (-not $verificationLine -or $verificationLine -notmatch '^DHDC4_VERIFY\s+PASS\s') {
    throw "Database verification failed: $verification"
}

$ownerTest = Invoke-MariaDbInput $client (Get-DatabaseArguments 'dhdc4' -WithDatabase) $ownerPassword -PrefixSql "SELECT CURRENT_USER(); SELECT AddZero('7',3); CALL z_update_definer();"
if ($ownerTest -notmatch 'dhdc4@localhost' -or $ownerTest -notmatch '007' -or $ownerTest -notmatch 'Legacy direct mysql\.proc updates are disabled') {
    throw "Owner account function/procedure smoke test failed: $ownerTest"
}

Write-InstallMessage $verificationLine
Write-InstallMessage "DHDC4 database installation completed successfully on $DbHost`:$DbPort."
Write-InstallMessage 'MariaDB event_scheduler remains OFF; enable it only after validating event_dhdc and server timezone.'
