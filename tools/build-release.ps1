param(
    [string]$Tag = "v4.0.0",
    [string]$OutputDirectory = "output/release"
)

$ErrorActionPreference = "Stop"
Set-StrictMode -Version Latest

$projectRoot = (Resolve-Path (Join-Path $PSScriptRoot "..")).Path
Push-Location $projectRoot

try {
    $dirty = @(git status --porcelain)
    if ($LASTEXITCODE -ne 0) {
        throw "Unable to read Git status."
    }
    if ($dirty.Count -gt 0) {
        throw "Working tree must be clean before building a release."
    }

    git rev-parse --verify --quiet "$Tag^{commit}" | Out-Null
    if ($LASTEXITCODE -ne 0) {
        throw "Release tag '$Tag' does not exist."
    }

    $tagCommit = (git rev-parse "$Tag^{commit}").Trim()
    $headCommit = (git rev-parse HEAD).Trim()
    if ($tagCommit -ne $headCommit) {
        throw "Release tag '$Tag' must point to HEAD."
    }

    $outputPath = Join-Path $projectRoot $OutputDirectory
    New-Item -ItemType Directory -Path $outputPath -Force | Out-Null
    $outputPath = (Resolve-Path $outputPath).Path

    $releaseName = "dhdc4-$Tag"
    $archivePath = Join-Path $outputPath "$releaseName.zip"
    $checksumPath = "$archivePath.sha256"
    if ((Test-Path -LiteralPath $archivePath) -or (Test-Path -LiteralPath $checksumPath)) {
        throw "Release output already exists: $archivePath"
    }

    $stagingRoot = Join-Path $outputPath (".staging-" + [Guid]::NewGuid().ToString("N"))
    $sourceArchive = Join-Path $stagingRoot "source.zip"
    $releaseRoot = Join-Path $stagingRoot $releaseName
    New-Item -ItemType Directory -Path $releaseRoot -Force | Out-Null

    try {
        git archive --format=zip --output=$sourceArchive $Tag
        if ($LASTEXITCODE -ne 0) {
            throw "git archive failed."
        }
        Expand-Archive -LiteralPath $sourceArchive -DestinationPath $releaseRoot

        foreach ($forbiddenPath in @(
            "update",
            ".git",
            ".github",
            "common/tests",
            "common/codeception.yml",
            "common/config/test.php",
            "common/config/test-ci.php",
            "environments/dev"
        )) {
            if (Test-Path -LiteralPath (Join-Path $releaseRoot $forbiddenPath)) {
                throw "Release contains forbidden development path: $forbiddenPath"
            }
        }

        Push-Location $releaseRoot
        try {
            composer install --no-dev --classmap-authoritative --no-interaction --no-progress
            if ($LASTEXITCODE -ne 0) {
                throw "Production Composer install failed."
            }

            php tools/verify-production-readiness.php --strict-release --release-artifact
            if ($LASTEXITCODE -ne 0) {
                throw "Production readiness verification failed inside the release artifact."
            }

            rg --quiet --glob "!vendor/**" "AIza[0-9A-Za-z_-]{20,}" .
            if ($LASTEXITCODE -eq 0) {
                throw "Release contains a Google API key-shaped value."
            }
            if ($LASTEXITCODE -gt 1) {
                throw "Unable to scan the release for embedded API keys."
            }
        }
        finally {
            Pop-Location
        }

        # The production initializer verifier keeps a rollback copy beside the
        # checkout it tests. That evidence belongs to the build workspace, not
        # to the distributable application archive.
        $verificationBackup = Join-Path $releaseRoot "_codex_backup"
        if (Test-Path -LiteralPath $verificationBackup) {
            Remove-Item -LiteralPath $verificationBackup -Recurse -Force
        }

        foreach ($generatedPath in @("_codex_backup", "output", "node_modules")) {
            if (Test-Path -LiteralPath (Join-Path $releaseRoot $generatedPath)) {
                throw "Release contains generated build path: $generatedPath"
            }
        }

        tar.exe -a -c -f $archivePath -C $stagingRoot $releaseName
        if ($LASTEXITCODE -ne 0) {
            throw "Unable to create the release ZIP."
        }

        $archiveEntries = @(tar.exe -tf $archivePath)
        if ($LASTEXITCODE -ne 0 -or $archiveEntries -notcontains "$releaseName/.htaccess") {
            throw "Release ZIP validation failed or the root .htaccess file is missing."
        }

        $hash = (Get-FileHash -Algorithm SHA256 -LiteralPath $archivePath).Hash.ToLowerInvariant()
        [System.IO.File]::WriteAllText($checksumPath, "$hash  $releaseName.zip`n", [System.Text.UTF8Encoding]::new($false))

        Write-Host "Release artifact: $archivePath"
        Write-Host "SHA-256 file: $checksumPath"
    }
    finally {
        if (Test-Path -LiteralPath $stagingRoot) {
            Remove-Item -LiteralPath $stagingRoot -Recurse -Force
        }
    }
}
finally {
    Pop-Location
}
