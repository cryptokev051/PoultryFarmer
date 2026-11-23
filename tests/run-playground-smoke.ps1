param(
  [Parameter(Mandatory=$true)][ValidateSet('A','B','C','D','E')][string]$Profile,
  [int]$Port = 9440,
  [string]$Wp = '7.1.4',
  [string]$Php = '8.3'
)

$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $PSScriptRoot
$blueprintNames = @{
  A = 'playground-profile-a-wp714.json'
  B = 'playground-profile-b-wp714.json'
  C = 'playground-profile-c-wp714-wc1122.json'
  D = 'playground-profile-d-default-wc1122.json'
  E = 'playground-profile-c-old-wp67-wc997.json'
}
$blueprint = Join-Path $PSScriptRoot $blueprintNames[$Profile]
$artifactDir = Join-Path $PSScriptRoot "artifacts\stability-profile-$Profile"
New-Item -ItemType Directory -Force -Path $artifactDir | Out-Null
$stdout = Join-Path $artifactDir 'playground.stdout.log'
$stderr = Join-Path $artifactDir 'playground.stderr.log'
$portableNpm = Join-Path $PSScriptRoot 'tools\node-v24.18.0-win-x64\npm.cmd'
$localNpm = if (Test-Path -LiteralPath $portableNpm) { $portableNpm } else { 'npm.cmd' }
$args = if (Test-Path -LiteralPath $portableNpm) {
  @('--prefix', $PSScriptRoot, 'exec', '--offline', '--', 'wp-playground-cli', 'start', "--path=$root\theme\poultryfarmer", "--wp=$Wp", "--php=$Php", "--port=$Port", '--skip-browser', '--login=false', "--site-url=http://127.0.0.1:$Port", "--blueprint=$blueprint", '--reset')
} else {
  @('exec', '--yes', '--package=@wp-playground/cli', '--', 'wp-playground-cli', 'start', "--path=$root\theme\poultryfarmer", "--wp=$Wp", "--php=$Php", "--port=$Port", '--skip-browser', '--login=false', "--site-url=http://127.0.0.1:$Port", "--blueprint=$blueprint", '--reset')
}
$proc = Start-Process -FilePath $localNpm -ArgumentList $args -WorkingDirectory $root -RedirectStandardOutput $stdout -RedirectStandardError $stderr -WindowStyle Hidden -PassThru
try {
  $ready = $false
  $deadline = (Get-Date).AddSeconds(90)
  while ((Get-Date) -lt $deadline) {
    if (Test-Path $stdout) { $text = Get-Content -Raw $stdout }
    try {
      $health = Invoke-WebRequest -Uri "http://127.0.0.1:$Port/" -UseBasicParsing -TimeoutSec 3
      if ($health.StatusCode -eq 200 -and $health.Content -notmatch 'maintenance|Fatal error|Warning:') { $ready = $true; break }
    } catch { }
    if ($proc.HasExited) { break }
    Start-Sleep -Seconds 2
  }
  $requests = @('/', '/local-sample-editorial-article/', '/?s=local')
  $results = foreach ($path in $requests) {
    try { $r = Invoke-WebRequest -Uri "http://127.0.0.1:$Port$path" -UseBasicParsing -TimeoutSec 15; [pscustomobject]@{ Path=$path; Status=$r.StatusCode; Length=$r.RawContentLength; Maintenance=($r.Content -match 'maintenance|\.maintenance'); Error=$null } }
    catch { [pscustomobject]@{ Path=$path; Status=$null; Length=$null; Maintenance=$false; Error=$_.Exception.Message } }
  }
  [pscustomobject]@{ Profile=$Profile; Port=$Port; Wp=$Wp; Php=$Php; Ready=$ready; ProcessExited=$proc.HasExited; Requests=$results; Stdout=$stdout; Stderr=$stderr } | ConvertTo-Json -Depth 6 | Set-Content (Join-Path $artifactDir 'result.json')
  Get-Content (Join-Path $artifactDir 'result.json')
}
finally {
  $runtimeProcesses = Get-CimInstance Win32_Process | Where-Object { $_.CommandLine -and $_.CommandLine -match "wp-playground.*--port=$Port" }
  foreach ($runtimeProcess in $runtimeProcesses) { Stop-Process -Id $runtimeProcess.ProcessId -Force -ErrorAction SilentlyContinue }
  if (-not $proc.HasExited) { $proc.Kill() }
}
