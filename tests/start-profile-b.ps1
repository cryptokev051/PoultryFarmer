[CmdletBinding()]
param(
	[ValidateRange(1024, 65535)]
	[int] $Port = 9450,

	[switch] $Reset
)

$ErrorActionPreference = 'Stop'

$projectRoot = (Resolve-Path -LiteralPath (Join-Path $PSScriptRoot '..')).Path
$nodePath = Join-Path $PSScriptRoot 'tools\node-v24.18.0-win-x64\node.exe'
$cliPath = Join-Path $PSScriptRoot 'node_modules\@wp-playground\cli\wp-playground.js'
$themePath = Join-Path $projectRoot 'theme\poultryfarmer'
$blueprintPath = Join-Path $PSScriptRoot 'playground-profile-b-wp714.json'
$runtimeHome = Join-Path $PSScriptRoot '.runtime-home\profile-b'
$runtimeTemp = Join-Path $PSScriptRoot '.runtime-tmp\profile-b'

foreach ( $requiredPath in @($nodePath, $cliPath, $themePath, $blueprintPath) ) {
	if ( -not (Test-Path -LiteralPath $requiredPath) ) {
		throw "Required Profile B dependency is missing: $requiredPath"
	}
}

New-Item -ItemType Directory -Force -Path $runtimeHome, $runtimeTemp | Out-Null

# Process-scoped overrides keep generated Playground storage inside this project on D:.
$env:USERPROFILE = $runtimeHome
$env:HOMEDRIVE = [System.IO.Path]::GetPathRoot($projectRoot).TrimEnd('\')
$env:HOMEPATH = $runtimeHome.Substring(2)
$env:TEMP = $runtimeTemp
$env:TMP = $runtimeTemp

$cliArguments = @(
	$cliPath,
	'start',
	"--path=$themePath",
	"--blueprint=$blueprintPath",
	'--wp=7.1.4',
	'--php=8.3',
	"--port=$Port",
	'--skip-browser'
)

if ( $Reset ) {
	$cliArguments += '--reset'
}

Write-Host "Starting isolated Poultry Farmer Profile B on http://127.0.0.1:$Port"
Write-Host "Generated storage: $runtimeHome"
& $nodePath @cliArguments
