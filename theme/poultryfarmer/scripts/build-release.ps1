[CmdletBinding()]
param(
    [string]$OutputPath = ''
)

$ErrorActionPreference = 'Stop'
$themeRoot = [IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$projectRoot = [IO.Path]::GetFullPath((Join-Path $themeRoot '..\..'))
$releaseRoot = [IO.Path]::GetFullPath((Join-Path $projectRoot 'releases'))

if (-not $OutputPath) { $OutputPath = Join-Path $releaseRoot 'poultryfarmer.zip' }
$outputFullPath = [IO.Path]::GetFullPath($OutputPath)
if (-not $outputFullPath.StartsWith($releaseRoot + [IO.Path]::DirectorySeparatorChar, [StringComparison]::OrdinalIgnoreCase)) {
    throw 'The release archive must be written inside the project releases directory.'
}

# Explicit allowlist: source artwork and internal documents cannot enter the installable ZIP by accident.
$explicitFiles = @(
    'functions.php', 'style.css', 'theme.json', 'assets/css/theme.css', 'assets/images/analysis-placeholder.svg',
    'assets/images/brand/01_Primary_Logo/Primary_FullColor_Transparent.png',
    'assets/images/brand/01_Primary_Logo/Primary_Reverse_OnForest.png',
    'assets/images/brand/02_Alternate_Lockups/Horizontal_Reverse_OnForest.png',
    'assets/images/brand/02_Alternate_Lockups/Horizontal_Transparent.png',
    'assets/images/brand/03_Emblem_and_Wordmark/Emblem_FullColor_Transparent.png',
    'assets/images/brand/04_Favicons_and_App_Icons/Apple_Touch_Icon_180.png',
    'assets/images/brand/04_Favicons_and_App_Icons/favicon.ico',
    'assets/images/brand/04_Favicons_and_App_Icons/PF_Icon_192x192.png',
    'assets/images/brand/04_Favicons_and_App_Icons/PF_Icon_512x512.png',
    'assets/images/brand/06_Website_and_Email/Footer_Dark_Background.png',
    'assets/images/brand/06_Website_and_Email/Header_Desktop_Transparent_1400.png',
    'assets/images/brand/06_Website_and_Email/Header_Desktop_Transparent_1400.webp',
    'assets/images/brand/06_Website_and_Email/Header_Mobile_Transparent_780.png'
)
$productionDirectories = @('inc', 'parts', 'patterns', 'templates')
$files = @()
foreach ($relativePath in $explicitFiles) {
    $path = Join-Path $themeRoot ($relativePath -replace '/', '\')
    if (-not (Test-Path -LiteralPath $path -PathType Leaf)) { throw "Required release file is missing: $relativePath" }
    $files += Get-Item -LiteralPath $path
}
foreach ($relativeDirectory in $productionDirectories) {
    $path = Join-Path $themeRoot $relativeDirectory
    if (-not (Test-Path -LiteralPath $path -PathType Container)) { throw "Required release directory is missing: $relativeDirectory" }
    $files += Get-ChildItem -LiteralPath $path -Recurse -File
}

$textExtensions = @('.php', '.css', '.json', '.html', '.svg', '.txt', '.md')
$credentialPattern = '(?i)(BEGIN (RSA |EC |OPENSSH )?PRIVATE KEY|API[_-]?KEY\s*[:=]|SECRET[_-]?KEY\s*[:=]|PASSWORD\s*[:=]\s*["''][^"'']+["''])'
foreach ($file in $files) {
    if ($textExtensions -contains $file.Extension -and (Get-Content -LiteralPath $file.FullName -Raw) -match $credentialPattern) {
        throw "Possible credential material found in: $($file.FullName)"
    }
}

New-Item -ItemType Directory -Path $releaseRoot -Force | Out-Null
if (Test-Path -LiteralPath $outputFullPath) { Remove-Item -LiteralPath $outputFullPath -Force }
Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem
$stream = [IO.File]::Open($outputFullPath, [IO.FileMode]::CreateNew)
try {
    $archive = [IO.Compression.ZipArchive]::new($stream, [IO.Compression.ZipArchiveMode]::Create, $false)
    try {
        foreach ($file in ($files | Sort-Object FullName)) {
            $relative = $file.FullName.Substring($themeRoot.Length).TrimStart('\', '/') -replace '\\', '/'
            $entry = $archive.CreateEntry("poultryfarmer/$relative", [IO.Compression.CompressionLevel]::Optimal)
            $entryStream = $entry.Open(); $fileStream = [IO.File]::OpenRead($file.FullName)
            try { $fileStream.CopyTo($entryStream) } finally { $fileStream.Dispose(); $entryStream.Dispose() }
        }
    } finally { $archive.Dispose() }
} finally { $stream.Dispose() }

$verification = [IO.Compression.ZipFile]::OpenRead($outputFullPath)
try {
    $entries = @($verification.Entries | ForEach-Object { $_.FullName })
    if (-not $entries -or @($entries | Where-Object { $_.Contains('\') }).Count -gt 0) { throw 'Release archive contains a Windows-style path or no entries.' }
    if (@($entries | Where-Object { -not $_.StartsWith('poultryfarmer/', [StringComparison]::Ordinal) }).Count -gt 0) { throw 'Release archive has an entry outside poultryfarmer/.' }
    if (@($entries | Where-Object { $_ -match '(?i)(\.zip$|\.pdf$|\.csv$|\.txt$|(^|/)(dist|scripts|\.git|node_modules|tests|docs)(/|$))' }).Count -gt 0) { throw 'Release archive contains an excluded document, development path, or nested archive.' }
    foreach ($requiredEntry in @('poultryfarmer/style.css', 'poultryfarmer/theme.json', 'poultryfarmer/functions.php')) {
        if ($entries -notcontains $requiredEntry) { throw "Release archive is missing: $requiredEntry" }
    }
} finally { $verification.Dispose() }

[pscustomobject]@{
    Archive = $outputFullPath
    Entries = $entries.Count
    Bytes = (Get-Item -LiteralPath $outputFullPath).Length
    RootFolder = 'poultryfarmer/'
    ExplicitManifestCheck = 'PASS'
    ArchiveIntegrityCheck = 'PASS'
}
