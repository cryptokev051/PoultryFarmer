[CmdletBinding()]
param(
	[ValidateRange(1024, 65535)]
	[int] $Port = 9460,

	[ValidateSet('Fresh', 'Conflict', 'Package')]
	[string] $Profile = 'Fresh',

	[switch] $Reset,

	[switch] $NoBlueprint
)

$ErrorActionPreference = 'Stop'
$projectRoot = (Resolve-Path -LiteralPath (Join-Path $PSScriptRoot '..')).Path
$nodePath = Join-Path $PSScriptRoot 'tools\node-v24.18.0-win-x64\node.exe'
$cliPath = Join-Path $PSScriptRoot 'node_modules\@wp-playground\cli\wp-playground.js'
$pluginPath = Join-Path $projectRoot 'plugins\poultryfarmer-starter'
$themeZip = Join-Path $projectRoot 'releases\poultryfarmer.zip'
$profileName = switch ( $Profile ) {
	'Conflict' { 'starter-conflict' }
	'Package' { 'starter-package' }
	default { 'starter-fresh' }
}
$blueprintName = if ( 'Conflict' -eq $Profile ) { 'playground-starter-conflict.json' } else { 'playground-starter-fresh.json' }
$blueprintPath = Join-Path $PSScriptRoot $blueprintName
$runtimeHome = Join-Path $PSScriptRoot ('.runtime-home\' + $profileName)
$runtimeTemp = Join-Path $PSScriptRoot ('.runtime-tmp\' + $profileName)

$starterZip = Join-Path $projectRoot 'releases\poultryfarmer-starter.zip'
$requiredPaths = @($nodePath, $cliPath, $themeZip)
if ( 'Package' -eq $Profile ) {
	$requiredPaths += $starterZip
} else {
	$requiredPaths += $pluginPath
}
foreach ( $requiredPath in $requiredPaths ) {
	if ( -not (Test-Path -LiteralPath $requiredPath) ) {
		throw "Required starter-profile dependency is missing: $requiredPath"
	}
}
if ( -not $NoBlueprint -and -not (Test-Path -LiteralPath $blueprintPath) ) {
	throw "Required starter-profile Blueprint is missing: $blueprintPath"
}
if ( 'Package' -eq $Profile -and -not $NoBlueprint ) {
	throw 'The Package profile must use -NoBlueprint so the ZIP can be installed through WordPress administration.'
}

New-Item -ItemType Directory -Force -Path $runtimeHome, $runtimeTemp | Out-Null
$env:USERPROFILE = $runtimeHome
$env:HOMEDRIVE = [System.IO.Path]::GetPathRoot($projectRoot).TrimEnd('\')
$env:HOMEPATH = $runtimeHome.Substring(2)
$env:TEMP = $runtimeTemp
$env:TMP = $runtimeTemp

$cliArguments = @(
	$cliPath,
	'start',
	'--wp=7.1.4',
	'--php=8.3',
	"--port=$Port",
	'--skip-browser'
)
if ( 'Package' -ne $Profile ) {
	$cliArguments += "--path=$pluginPath"
}
if ( -not $NoBlueprint ) {
	$cliArguments += "--blueprint=$blueprintPath"
}
if ( $Reset ) {
	$cliArguments += '--reset'
}

Write-Host "Starting isolated Poultry Farmer starter profile '$Profile' on http://127.0.0.1:$Port"
Write-Host "Generated storage: $runtimeHome"
if ( 'Package' -eq $Profile ) {
	Push-Location -LiteralPath $runtimeTemp
	try {
		& $nodePath @cliArguments
	} finally {
		Pop-Location
	}
} else {
	& $nodePath @cliArguments
}
