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

Write-Host "[1/4] Installing production PHP dependencies..." -ForegroundColor Yellow
composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader
if ($LASTEXITCODE -ne 0) { throw "composer install gagal." }

Write-Host "[2/4] Installing Node dependencies and building frontend..." -ForegroundColor Yellow
npm ci
if ($LASTEXITCODE -ne 0) { throw "npm ci gagal." }

npm run build
if ($LASTEXITCODE -ne 0) { throw "npm run build gagal." }

Write-Host "[3/4] Preparing InfinityFree package..." -ForegroundColor Yellow

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

# Copy all public files, including dotfiles.
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

Write-Host "[4/4] Creating upload ZIP parts..." -ForegroundColor Yellow
Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem

# Group by uncompressed size so every archive stays comfortably below InfinityFree's upload limit.
$MaxUncompressedPerPart = 6MB
$Files = Get-ChildItem $Htdocs -Recurse -Force -File | Sort-Object FullName

$groups = New-Object System.Collections.Generic.List[object]
$current = New-Object System.Collections.Generic.List[object]
$currentBytes = [int64]0

foreach ($file in $Files) {
    if ($current.Count -gt 0 -and ($currentBytes + $file.Length) -gt $MaxUncompressedPerPart) {
        $groups.Add(@($current))
        $current = New-Object System.Collections.Generic.List[object]
        $currentBytes = 0
    }

    $current.Add($file)
    $currentBytes += $file.Length
}
if ($current.Count -gt 0) {
    $groups.Add(@($current))
}

$part = 1
foreach ($group in $groups) {
    $zipPath = Join-Path $Artifact ("bast-infinityfree-part-{0:D2}.zip" -f $part)
    $stream = [System.IO.File]::Open($zipPath, [System.IO.FileMode]::Create)
    try {
        $zip = New-Object System.IO.Compression.ZipArchive(
            $stream,
            [System.IO.Compression.ZipArchiveMode]::Create,
            $false
        )
        try {
            foreach ($file in $group) {
                $relative = $file.FullName.Substring($Htdocs.Length).TrimStart("\", "/").Replace("\", "/")
                [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile(
                    $zip,
                    $file.FullName,
                    $relative,
                    [System.IO.Compression.CompressionLevel]::Optimal
                ) | Out-Null
            }
        }
        finally {
            $zip.Dispose()
        }
    }
    finally {
        $stream.Dispose()
    }

    $sizeMb = [math]::Round((Get-Item $zipPath).Length / 1MB, 2)
    Write-Host ("  Created {0} ({1} MB)" -f (Split-Path $zipPath -Leaf), $sizeMb)
    $part++
}

$Info = @"
BAST InfinityFree deployment package

1. Upload ALL bast-infinityfree-part-XX.zip files into htdocs using Upload & Extract, in numeric order.
2. Do NOT upload SETUP_TOKEN.txt.
3. Open https://bast.site.je/install.php after all parts are extracted.
4. Use the token from SETUP_TOKEN.txt.
5. After installation succeeds, delete install.php from htdocs.

Database fields are already prefilled except the MySQL password.
"@

[System.IO.File]::WriteAllText(
    (Join-Path $Artifact "DEPLOY_INFO.txt"),
    $Info,
    (New-Object System.Text.UTF8Encoding($false))
)

Write-Host ""
Write-Host "DONE." -ForegroundColor Green
Write-Host "Output folder:"
Write-Host "  $Artifact" -ForegroundColor Cyan
Write-Host ""
Write-Host "IMPORTANT: SETUP_TOKEN.txt is secret. Keep it on your PC and never upload it to htdocs." -ForegroundColor Yellow
