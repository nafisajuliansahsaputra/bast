$ErrorActionPreference = "Stop"
Set-StrictMode -Version Latest

$RepoRoot = (Resolve-Path (Join-Path $PSScriptRoot "..\..")).Path
Set-Location $RepoRoot

function Resolve-LaragonRoot {
    $wwwDirectory = Split-Path $RepoRoot -Parent
    $candidate = Split-Path $wwwDirectory -Parent

    $isLaragonWww = (Split-Path $wwwDirectory -Leaf).ToLowerInvariant() -eq "www"
    $hasLaragonBin = Test-Path (Join-Path $candidate "bin")

    if ($isLaragonWww -and $hasLaragonBin) {
        return $candidate
    }

    if (Test-Path "C:\laragon\bin") {
        return "C:\laragon"
    }

    return $null
}

function Resolve-Php([string]$LaragonRoot) {
    $command = Get-Command "php" -ErrorAction SilentlyContinue

    if ($null -ne $command) {
        return $command.Source
    }

    if ([string]::IsNullOrWhiteSpace($LaragonRoot)) {
        throw "PHP tidak ditemukan di PATH dan instalasi Laragon tidak terdeteksi."
    }

    $phpRoot = Join-Path $LaragonRoot "bin\php"

    if (-not (Test-Path $phpRoot)) {
        throw "Folder PHP Laragon tidak ditemukan di '$phpRoot'."
    }

    $candidates = Get-ChildItem $phpRoot -Filter "php.exe" -File -Recurse |
        ForEach-Object {
            $versionText = ""

            try {
                $versionText = (& $_.FullName -r "echo PHP_VERSION;" 2>$null | Out-String).Trim()
            }
            catch {
                return
            }

            $match = [regex]::Match(
                $versionText,
                '^(?<major>\d+)\.(?<minor>\d+)\.(?<patch>\d+)'
            )

            if (-not $match.Success) {
                return
            }

            $versionTextNormalized = "{0}.{1}.{2}" -f @(
                $match.Groups["major"].Value,
                $match.Groups["minor"].Value,
                $match.Groups["patch"].Value
            )
            $version = New-Object System.Version($versionTextNormalized)

            if ($version -lt [version]"8.3.0") {
                return
            }

            [PSCustomObject]@{
                Path = $_.FullName
                Version = $version
            }
        } |
        Sort-Object Version -Descending

    $selected = $candidates | Select-Object -First 1

    if ($null -eq $selected) {
        throw "PHP 8.3+ tidak ditemukan di Laragon. BAST membutuhkan PHP ^8.3."
    }

    return $selected.Path
}

function Resolve-Composer(
    [string]$LaragonRoot,
    [string]$PhpPath
) {
    if (-not [string]::IsNullOrWhiteSpace($LaragonRoot)) {
        $composerRoot = Join-Path $LaragonRoot "bin\composer"

        if (Test-Path $composerRoot) {
            $phar = Get-ChildItem $composerRoot -Filter "composer.phar" -File -Recurse |
                Select-Object -First 1

            if ($null -ne $phar) {
                return [PSCustomObject]@{
                    Path = $phar.FullName
                    Mode = "phar"
                }
            }

            $native = Get-ChildItem $composerRoot -File -Recurse |
                Where-Object {
                    $_.Name -in @(
                        "composer.exe",
                        "composer.bat",
                        "composer.cmd"
                    )
                } |
                Select-Object -First 1

            if ($null -ne $native) {
                return [PSCustomObject]@{
                    Path = $native.FullName
                    Mode = "native"
                }
            }
        }
    }

    $command = Get-Command "composer" -ErrorAction SilentlyContinue

    if ($null -ne $command) {
        return [PSCustomObject]@{
            Path = $command.Source
            Mode = "native"
        }
    }

    throw "Composer tidak ditemukan. Install Composer lewat Laragon atau composer.org."
}

function Resolve-Npm([string]$LaragonRoot) {
    $command = Get-Command "npm" -ErrorAction SilentlyContinue

    if ($null -ne $command) {
        return $command.Source
    }

    if (-not [string]::IsNullOrWhiteSpace($LaragonRoot)) {
        $nodeRoots = @(
            (Join-Path $LaragonRoot "bin\nodejs"),
            (Join-Path $LaragonRoot "bin\node")
        )

        foreach ($nodeRoot in $nodeRoots) {
            if (-not (Test-Path $nodeRoot)) {
                continue
            }

            $npm = Get-ChildItem $nodeRoot -File -Recurse |
                Where-Object {
                    $_.Name -in @(
                        "npm.cmd",
                        "npm.exe"
                    )
                } |
                Select-Object -First 1

            if ($null -ne $npm) {
                return $npm.FullName
            }
        }
    }

    throw "npm tidak ditemukan. Pastikan Node.js sudah terinstall."
}

function Invoke-Composer {
    param(
        [Parameter(ValueFromRemainingArguments = $true)]
        [string[]]$CommandArguments
    )

    if ($Composer.Mode -eq "phar") {
        & $PhpPath $Composer.Path @CommandArguments
    }
    else {
        & $Composer.Path @CommandArguments
    }
}

function Invoke-Npm {
    param(
        [Parameter(ValueFromRemainingArguments = $true)]
        [string[]]$CommandArguments
    )

    & $NpmPath @CommandArguments
}

function Sha256-Hex([string]$Text) {
    $sha = [System.Security.Cryptography.SHA256]::Create()
    try {
        $bytes = [System.Text.Encoding]::UTF8.GetBytes($Text)
        return ([System.BitConverter]::ToString($sha.ComputeHash($bytes))).Replace("-", "").ToLowerInvariant()
    }
    finally {
        $sha.Dispose()
    }
}

function Fill-RandomBytes([byte[]]$Buffer) {
    $rng = [System.Security.Cryptography.RandomNumberGenerator]::Create()

    try {
        $rng.GetBytes($Buffer)
    }
    finally {
        $rng.Dispose()
    }
}

function New-RandomToken([int]$Bytes = 12) {
    $buffer = New-Object byte[] $Bytes
    Fill-RandomBytes $buffer
    return ([System.BitConverter]::ToString($buffer)).Replace("-", "").ToLowerInvariant()
}

function New-AppKey {
    $buffer = New-Object byte[] 32
    Fill-RandomBytes $buffer
    return "base64:" + [Convert]::ToBase64String($buffer)
}

$LaragonRoot = Resolve-LaragonRoot
$PhpPath = Resolve-Php $LaragonRoot

$env:Path = "$(Split-Path $PhpPath -Parent);$env:Path"

$Composer = Resolve-Composer $LaragonRoot $PhpPath
$NpmPath = Resolve-Npm $LaragonRoot

$PhpVersion = (& $PhpPath -r "echo PHP_VERSION;" | Out-String).Trim()

& $PhpPath -r "exit(extension_loaded('pdo_sqlite') ? 0 : 1);"
if ($LASTEXITCODE -ne 0) {
    throw "PHP pdo_sqlite extension tidak aktif pada '$PhpPath'. Aktifkan extension pdo_sqlite di PHP Laragon."
}

Write-Host ""
Write-Host "=== BAST InfinityFree local builder ===" -ForegroundColor Cyan
Write-Host "Repo: $RepoRoot"
Write-Host "PHP: $PhpPath ($PhpVersion)"
Write-Host "Composer: $($Composer.Path)"
Write-Host "npm: $NpmPath"
Write-Host ""

Write-Host "[1/6] Installing production PHP dependencies..." -ForegroundColor Yellow
Invoke-Composer install --no-dev --prefer-dist --no-interaction --no-progress
if ($LASTEXITCODE -ne 0) { throw "composer install gagal." }

Write-Host "[2/6] Building frontend..." -ForegroundColor Yellow
Invoke-Npm ci
if ($LASTEXITCODE -ne 0) { throw "npm ci gagal." }

Invoke-Npm run build
if ($LASTEXITCODE -ne 0) { throw "npm run build gagal." }

Write-Host "[3/6] Preparing InfinityFree htdocs..." -ForegroundColor Yellow

$Dist = Join-Path $RepoRoot "dist"
$Htdocs = Join-Path $Dist "htdocs"
$Artifact = Join-Path $Dist "artifact"
$Core = Join-Path $Htdocs "app-core"

if (Test-Path $Dist) {
    Remove-Item $Dist -Recurse -Force
}

New-Item -ItemType Directory -Force -Path $Core, $Artifact | Out-Null

$CoreDirs = @("app", "bootstrap", "config", "database", "resources", "routes", "storage", "vendor")
foreach ($dir in $CoreDirs) {
    Copy-Item (Join-Path $RepoRoot $dir) $Core -Recurse -Force
}

$CoreFiles = @("artisan", "composer.json", "composer.lock")
foreach ($file in $CoreFiles) {
    Copy-Item (Join-Path $RepoRoot $file) $Core -Force
}

$RequiredDirs = @(
    "storage\framework\cache",
    "storage\framework\sessions",
    "storage\framework\views",
    "storage\logs",
    "storage\app",
    "bootstrap\cache",
    "database"
)
foreach ($relative in $RequiredDirs) {
    New-Item -ItemType Directory -Force -Path (Join-Path $Core $relative) | Out-Null
}

Get-ChildItem (Join-Path $RepoRoot "public") -Force | ForEach-Object {
    Copy-Item $_.FullName $Htdocs -Recurse -Force
}

Copy-Item (Join-Path $RepoRoot "deploy\infinityfree\index.php") (Join-Path $Htdocs "index.php") -Force
Copy-Item (Join-Path $RepoRoot "deploy\infinityfree\.htaccess") (Join-Path $Htdocs ".htaccess") -Force

$Hot = Join-Path $Htdocs "hot"
if (Test-Path $Hot) { Remove-Item $Hot -Force }

$PublicStorage = Join-Path $Htdocs "storage"
if (Test-Path $PublicStorage) { Remove-Item $PublicStorage -Recurse -Force }

Write-Host "[4/6] Creating packaged SQLite demo database..." -ForegroundColor Yellow

$DatabasePath = Join-Path $Core "database\database.sqlite"

if (Test-Path $DatabasePath) {
    Remove-Item $DatabasePath -Force
}

New-Item -ItemType File -Force -Path $DatabasePath | Out-Null

$env:APP_NAME = "BAST"
$env:APP_ENV = "production"
$env:APP_KEY = New-AppKey
$env:APP_DEBUG = "false"
$env:APP_URL = "http://localhost"
$env:LOG_CHANNEL = "single"
$env:LOG_LEVEL = "error"
$env:DB_CONNECTION = "sqlite"
$env:DB_DATABASE = $DatabasePath
$env:SESSION_DRIVER = "file"
$env:CACHE_STORE = "file"
$env:QUEUE_CONNECTION = "sync"
$env:FILESYSTEM_DISK = "local"
$env:BAST_SUPER_ADMIN_EMAIL = "admin@bast.local"
$env:BAST_SUPER_ADMIN_PASSWORD = New-RandomToken 24
$env:BAST_DEMO_MODE = "true"
$env:BAST_DEMO_EMAIL = "rina.maharani@bast.local"
$env:BAST_DEMO_READ_ONLY = "true"
$env:BAST_DEMO_RESET = "false"

Push-Location $Core
try {
    & $PhpPath artisan migrate:fresh --seed --force
    if ($LASTEXITCODE -ne 0) {
        throw "Pembuatan database demo SQLite gagal."
    }
}
finally {
    Pop-Location
}

if (-not (Test-Path $DatabasePath) -or (Get-Item $DatabasePath).Length -eq 0) {
    throw "database.sqlite tidak berhasil dibuat."
}

Write-Host "[5/6] Creating secure installer..." -ForegroundColor Yellow

$Token = New-RandomToken
$TokenHash = Sha256-Hex $Token
$InstallTemplate = Get-Content (Join-Path $RepoRoot "deploy\infinityfree\install.php") -Raw
$InstallTemplate = $InstallTemplate.Replace("__SETUP_TOKEN_HASH__", $TokenHash)
[System.IO.File]::WriteAllText(
    (Join-Path $Htdocs "install.php"),
    $InstallTemplate,
    (New-Object System.Text.UTF8Encoding($false))
)

[System.IO.File]::WriteAllText(
    (Join-Path $Artifact "SETUP_TOKEN.txt"),
    $Token + [Environment]::NewLine,
    (New-Object System.Text.UTF8Encoding($false))
)

Write-Host "[6/6] Validating InfinityFree file limits..." -ForegroundColor Yellow

$Violations = @()
Get-ChildItem $Htdocs -Recurse -Force -File | ForEach-Object {
    $file = $_
    $name = $file.Name.ToLowerInvariant()
    $extension = $file.Extension.ToLowerInvariant()

    if ($name -eq ".htaccess") {
        $limit = 10KB
    }
    elseif ($extension -in @(".php", ".html", ".htm", ".js")) {
        $limit = 1MB
    }
    else {
        $limit = 10MB
    }

    if ($file.Length -gt $limit) {
        $Violations += "$($file.FullName) - $($file.Length) bytes > $limit bytes"
    }
}

if ($Violations.Count -gt 0) {
    Write-Host "File yang melewati limit InfinityFree:" -ForegroundColor Red
    $Violations | ForEach-Object { Write-Host "  $_" -ForegroundColor Red }
    throw "Package tidak lolos file-size check InfinityFree."
}

$Info = @"
BAST - InfinityFree local package

Package ini sudah membawa database demo SQLite dan lampiran demo.
Tidak perlu membuat atau mengimport MySQL database.

1. Upload SEMUA isi dist\htdocs ke folder htdocs InfinityFree menggunakan FileZilla/FTP.
2. Buka https://DOMAIN-KAMU/install.php.
3. Gunakan token dari dist\artifact\SETUP_TOKEN.txt dan tentukan email/password Super Admin.
4. Setelah installer sukses, hapus install.php dari hosting.
5. Jangan upload SETUP_TOKEN.txt.
6. Database ada di app-core\database\database.sqlite. Jangan overwrite file ini saat update jika ingin mempertahankan data.
"@

[System.IO.File]::WriteAllText(
    (Join-Path $Artifact "DEPLOY_INFO.txt"),
    $Info,
    (New-Object System.Text.UTF8Encoding($false))
)

$Files = Get-ChildItem $Htdocs -Recurse -Force -File
$TotalMb = [math]::Round((($Files | Measure-Object Length -Sum).Sum / 1MB), 2)

Write-Host ""
Write-Host "DONE." -ForegroundColor Green
Write-Host "Files: $($Files.Count), total: $TotalMb MB"
Write-Host "Upload folder:"
Write-Host "  $Htdocs" -ForegroundColor Cyan
Write-Host ""
Write-Host "IMPORTANT: SETUP_TOKEN.txt adalah rahasia dan jangan pernah di-upload." -ForegroundColor Yellow
