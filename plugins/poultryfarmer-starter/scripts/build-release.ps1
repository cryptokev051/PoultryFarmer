[CmdletBinding()]
param(
	[string] $Destination = ''
)

$ErrorActionPreference = 'Stop'
$pluginRoot = (Resolve-Path -LiteralPath (Join-Path $PSScriptRoot '..')).Path
$projectRoot = (Resolve-Path -LiteralPath (Join-Path $pluginRoot '..\..')).Path

if ( -not $Destination ) {
	$Destination = Join-Path $projectRoot 'releases\poultryfarmer-starter.zip'
}

$destinationPath = [System.IO.Path]::GetFullPath($Destination)
$releaseRoot = [System.IO.Path]::GetFullPath((Join-Path $projectRoot 'releases'))
if ( -not $destinationPath.StartsWith($releaseRoot + [System.IO.Path]::DirectorySeparatorChar, [System.StringComparison]::OrdinalIgnoreCase) ) {
	throw 'Starter release destination must remain inside the project releases directory.'
}

New-Item -ItemType Directory -Force -Path (Split-Path -Parent $destinationPath) | Out-Null

if ( Test-Path -LiteralPath $destinationPath ) {
	Remove-Item -LiteralPath $destinationPath -Force
}
Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem
$archive = [System.IO.Compression.ZipFile]::Open($destinationPath, [System.IO.Compression.ZipArchiveMode]::Create)
try {
	[System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile(
		$archive,
		(Join-Path $pluginRoot 'poultryfarmer-starter.php'),
		'poultryfarmer-starter/poultryfarmer-starter.php',
		[System.IO.Compression.CompressionLevel]::Optimal
	) | Out-Null
	[System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile(
		$archive,
		(Join-Path $pluginRoot 'readme.txt'),
		'poultryfarmer-starter/readme.txt',
		[System.IO.Compression.CompressionLevel]::Optimal
	) | Out-Null
} finally {
	$archive.Dispose()
}

Get-Item -LiteralPath $destinationPath | Select-Object FullName, Length, LastWriteTime
Get-FileHash -Algorithm SHA256 -LiteralPath $destinationPath

