<#
    Portfolio Geraldo Perridys AGONSE - Script de demarrage (mise au point).

    Lance les deux serveurs de developpement :
      - API backend  : http://127.0.0.1:8000
      - Site public  : http://127.0.0.1:8001  (administration : /admin)

    A utiliser apres avoir lanc demarrer-mysql (ou demarrage automatique de MySQL).
#>
param(
    [int]    $PortBackend  = 8000,
    [int]    $PortFrontend = 8001,
    [switch] $Stop
)

$ErrorActionPreference = 'Stop'
$root = $PSScriptRoot
$logs = Join-Path $root 'storage\logs'

function Stop-Servers {
    Write-Host ''
    Write-Host 'Arret des serveurs en cours...' -ForegroundColor Yellow

    Get-CimInstance Win32_Process -Filter "Name = 'php.exe'" |
        Where-Object { $_.CommandLine -match 'artisan\s+serve' } |
        ForEach-Object {
            Write-Host "  - arret du PID $($_.ProcessId)"
            Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue
        }

    Write-Host 'Serveurs arretes.' -ForegroundColor Green
}

if ($Stop) {
    Stop-Servers
    exit 0
}

# --- Verification de l'environnement -----------------------------------------
foreach ($binaire in @('php', 'node', 'npm')) {
    if (-not (Get-Command $binaire -ErrorAction SilentlyContinue)) {
        Write-Host "ERREUR : '$binaire' est introuvable dans le PATH." -ForegroundColor Red
        Write-Host ' Installez-le puis relancez ce script.' -ForegroundColor Red
        exit 1
    }
}

New-Item -ItemType Directory -Path $logs -Force | Out-Null

# --- Connexion a la base de donnees -------------------------------------------
Write-Host 'Verification de la base de donnees...' -ForegroundColor Cyan
$verifDb = & php -r "try { new PDO('mysql:host=127.0.0.1;port=3306', 'root', ''); echo 'ok'; } catch (Throwable `$e) { echo 'ko'; }" 2>$null
if ($verifDb -ne 'ok') {
    Write-Host 'ERREUR : MySQL est inaccessible sur 127.0.0.1:3306.' -ForegroundColor Red
    Write-Host ' Demarrez MySQL puis relancez demarrer.ps1.' -ForegroundColor Red
    exit 1
}
Write-Host '  Base de donnees accessible.' -ForegroundColor Green

# --- Arret des eventuels serveurs deja actifs --------------------------------
Stop-Servers | Out-Null

# --- Demarrage -----------------------------------------------------------------
Write-Host ''
Write-Host 'Demarrage de l API backend...' -ForegroundColor Cyan
Start-Process -FilePath 'php' `
    -ArgumentList 'artisan', 'serve', "--host=127.0.0.1", "--port=$PortBackend" `
    -WorkingDirectory (Join-Path $root 'backend') `
    -WindowStyle Hidden `
    -RedirectStandardOutput (Join-Path $logs 'backend-serve.log') `
    -RedirectStandardError  (Join-Path $logs 'backend-serve.err.log')

Write-Host 'Demarrage du site public...' -ForegroundColor Cyan
Start-Process -FilePath 'php' `
    -ArgumentList 'artisan', 'serve', "--host=127.0.0.1", "--port=$PortFrontend" `
    -WorkingDirectory (Join-Path $root 'frontend') `
    -WindowStyle Hidden `
    -RedirectStandardOutput (Join-Path $logs 'frontend-serve.log') `
    -RedirectStandardError  (Join-Path $logs 'frontend-serve.err.log')

# --- Attente que les deux repondent ------------------------------------------
$attente = 20
while ($attente -gt 0) {
    Start-Sleep -Seconds 1
    $attente--

    $api  = Test-NetConnection -ComputerName 127.0.0.1 -Port $PortBackend  -InformationLevel Quiet -WarningAction SilentlyContinue
    $site = Test-NetConnection -ComputerName 127.0.0.1 -Port $PortFrontend -InformationLevel Quiet -WarningAction SilentlyContinue

    if ($api -and $site) { break }
}

Write-Host ''
Write-Host '=========================================================' -ForegroundColor Green
Write-Host '  Portfolio Geraldo Perridys AGONSE - demarrage termine' -ForegroundColor Green
Write-Host '=========================================================' -ForegroundColor Green
Write-Host "  Site public        : http://127.0.0.1:$PortFrontend"                 -ForegroundColor White
Write-Host "  Administration     : http://127.0.0.1:$PortFrontend/admin"          -ForegroundColor White
Write-Host "  API backend        : http://127.0.0.1:$PortBackend/api/v1/site"    -ForegroundColor White
Write-Host ''
Write-Host '  Pour arreter les serveurs : .\demarrer.ps1 -Stop'                   -ForegroundColor Gray
Write-Host "  Journaux : $logs"                                                   -ForegroundColor Gray
Write-Host ''
