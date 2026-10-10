<#
    Portfolio Geraldo Perridys AGONSE - Script de demarrage.

    Une seule application, un seul port : le site public, l'administration
    et l'API sont servis par la meme application Laravel.

      - Site public    : http://127.0.0.1:8000
      - Administration : http://127.0.0.1:8000/admin
      - API            : http://127.0.0.1:8000/api/v1/site

    A utiliser apres avoir demarre MySQL (ou demarrage automatique de MySQL).
#>
param(
    [int]    $Port = 8000,
    [switch] $Stop
)

$ErrorActionPreference = 'Stop'
$root = $PSScriptRoot
$app = Join-Path $root 'geraldoportfolio'
$logs = Join-Path $root 'storage\logs'

function Stop-Servers {
    Write-Host ''
    Write-Host 'Arret du serveur en cours...' -ForegroundColor Yellow

    Get-CimInstance Win32_Process -Filter "Name = 'php.exe'" |
        Where-Object { $_.CommandLine -match 'artisan\s+serve' } |
        ForEach-Object {
            Write-Host "  - arret du PID $($_.ProcessId)"
            Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue
        }

    Write-Host 'Serveur arrete.' -ForegroundColor Green
}

if ($Stop) {
    Stop-Servers
    exit 0
}

# --- Verification de l'environnement -----------------------------------------
if (-not (Get-Command 'php' -ErrorAction SilentlyContinue)) {
    Write-Host "ERREUR : 'php' est introuvable dans le PATH." -ForegroundColor Red
    Write-Host ' Installez-le puis relancez ce script.' -ForegroundColor Red
    exit 1
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

# --- Arret de l'eventuel serveur deja actif -----------------------------------
Stop-Servers | Out-Null

# --- Demarrage -----------------------------------------------------------------
# Le site public, l'administration et l'API sont servis par la meme application :
#   - APP_URL doit pointer vers ce port pour que les liens et les images soient
#     corrects (aucun appel reseau sortant n'est necessaire) ;
#   - PHP_CLI_SERVER_WORKERS reste utile pour servir plusieurs requetes de front
#     en parallele (images, Livewire) sans blocage.
$env:APP_URL = "http://127.0.0.1:$Port"
$env:PHP_CLI_SERVER_WORKERS = '4'

Write-Host ''
Write-Host "Demarrage de l application sur le port $Port..." -ForegroundColor Cyan
Start-Process -FilePath 'php' `
    -ArgumentList 'artisan', 'serve', "--host=127.0.0.1", "--port=$Port" `
    -WorkingDirectory $app `
    -WindowStyle Hidden `
    -RedirectStandardOutput (Join-Path $logs 'serve.log') `
    -RedirectStandardError  (Join-Path $logs 'serve.err.log')

# --- Attente que le serveur reponde -------------------------------------------
$attente = 20
while ($attente -gt 0) {
    Start-Sleep -Seconds 1
    $attente--

    if (Test-NetConnection -ComputerName 127.0.0.1 -Port $Port -InformationLevel Quiet -WarningAction SilentlyContinue) {
        break
    }
}

Write-Host ''
Write-Host '=========================================================' -ForegroundColor Green
Write-Host '  Portfolio Geraldo Perridys AGONSE - demarrage termine' -ForegroundColor Green
Write-Host '=========================================================' -ForegroundColor Green
Write-Host "  Site public    : http://127.0.0.1:$Port"                   -ForegroundColor White
Write-Host "  Administration : http://127.0.0.1:$Port/admin"           -ForegroundColor White
Write-Host "  API            : http://127.0.0.1:$Port/api/v1/site"     -ForegroundColor White
Write-Host ''
Write-Host '  Pour arreter le serveur : .\demarrer.ps1 -Stop'             -ForegroundColor Gray
Write-Host "  Journaux : $logs"                                            -ForegroundColor Gray
Write-Host ''
