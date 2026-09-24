$ErrorActionPreference = "Stop"
Set-StrictMode -Version Latest

$RepoRoot = (Resolve-Path (Join-Path $PSScriptRoot "..\..")).Path
Set-Location $RepoRoot

function Require-Command([string]$Name) {
    if (-not (Get-Command $Name -ErrorAction SilentlyContinue)) {
        throw "Command '$Name' tidak ditemukan. Pastikan $Name sudah terinstall dan masuk PATH."
    }
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

function New-RandomToken([int]$Bytes = 12) {
    $buffer = New-Object byte[] $Bytes
    [System.Security.Cryptography.RandomNumberGenerator]::Fill($buffer)
    return ([System.BitConverter]::ToString($buffer)).Replace("-", "").ToLowerInvariant()
}

Require-Command "php"
Require-Command "composer"
Require-Command "npm"

Write-Host ""
Write-Host "=== BAST InfinityFree local builder ===" -ForegroundColor Cyan
Write-Host "Repo: $RepoRoot"
Write-Host ""

Write-Host "[1/5] Installing production PHP dependencies..." -ForegroundColor Yellow
composer install --no-dev --prefer-dist --no-interaction --no-progress
if ($LASTEXITCODE -ne 0) { throw "composer install gagal." }

Write-Host "[2/5] Building frontend..." -ForegroundColor Yellow
npm ci
if ($LASTEXITCODE -ne 0) { throw "npm ci gagal." }

npm run build
if ($LASTEXITCODE -ne 0) { throw "npm run build gagal." }

Write-Host "[3/5] Clearing Laravel caches..." -ForegroundColor Yellow
php artisan optimize:clear
if ($LASTEXITCODE -ne 0) { throw "php artisan optimize:clear gagal." }

Write-Host "[4/5] Preparing InfinityFree htdocs..." -ForegroundColor Yellow

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
    "bootstrap\cache"
)
foreach ($relative in $RequiredDirs) {
    New-Item -ItemType Directory -Force -Path (Join-Path $Core $relative) | Out-Null
}

Get-ChildItem (Join-Path $RepoRoot "public") -Force | ForEach-Object {
    Copy-Item $_.FullName $Htdocs -Recurse -Force
}

Copy-Item (Join-Path $RepoRoot "deploy\infinityfree\index.php") (Join-Path $Htdocs "index.php") -Force
Copy-Item (Join-Path $RepoRoot "deploy\infinityfree\.htaccess") (Join-Path $Htdocs ".htaccess") -Force

$Token = New-RandomToken
$TokenHash = Sha256-Hex $Token
$InstallTemplate = Get-Content (Join-Path $RepoRoot "deploy\infinityfree\install.php") -Raw
$InstallTemplate = $InstallTemplate.Replace("__SETUP_TOKEN_HASH__", $TokenHash)
[System.IO.File]::WriteAllText(
    (Join-Path $Htdocs "install.php"),
    $InstallTemplate,
    (New-Object System.Text.UTF8Encoding($false))
)

$Hot = Join-Path $Htdocs "hot"
if (Test-Path $Hot) { Remove-Item $Hot -Force }

$PublicStorage = Join-Path $Htdocs "storage"
if (Test-Path $PublicStorage) { Remove-Item $PublicStorage -Recurse -Force }

[System.IO.File]::WriteAllText(
    (Join-Path $Artifact "SETUP_TOKEN.txt"),
    $Token + [Environment]::NewLine,
    (New-Object System.Text.UTF8Encoding($false))
)

Write-Host "[5/5] Validating InfinityFree file limits..." -ForegroundColor Yellow

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
BAST InfinityFree local package

Folder siap upload:
  dist\htdocs\

1. Gunakan FileZilla/FTP untuk upload SEMUA isi dist\htdocs ke htdocs InfinityFree.
2. Database SQL demo tidak dibuat oleh local builder ini agar database lokal kamu tidak disentuh.
3. Cara paling aman: gunakan artifact dari GitHub Actions "Build InfinityFree Package"; artifact itu menyertakan bast-infinityfree.sql.
4. Import SQL melalui phpMyAdmin sebelum membuka /install.php.
5. Gunakan token dari dist\artifact\SETUP_TOKEN.txt.
6. Setelah instalasi sukses, hapus install.php dari hosting.
7. Jangan upload SETUP_TOKEN.txt.
"@

[System.IO.File]::WriteAllText(
    (Join-Path $Artifact "DEPLOY_INFO.txt"),
    $Info,
    (New-Object System.Text.UTF8Encoding($false))
)

Write-Host ""
Write-Host "DONE." -ForegroundColor Green
Write-Host "Upload folder:"
Write-Host "  $Htdocs" -ForegroundColor Cyan
Write-Host ""
Write-Host "IMPORTANT: SETUP_TOKEN.txt adalah rahasia dan jangan pernah di-upload." -ForegroundColor Yellow
