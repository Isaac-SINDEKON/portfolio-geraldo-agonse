<#
    Portfolio Geraldo Perridys AGONSE - Mise au point complete.

    A executer une seule fois, ou apres une coupure / reformatage du PC :
      .\installer.ps1

    Le script est idempotent : il peut etre relance sans rien casser.
#>
param(
    # Identifiants MySQL utilises pour creer la base de donnees
    [string] $DbHost     = '127.0.0.1',
    [int]    $DbPort     = 3306,
    [string] $DbUser     = 'root',
    [string] $DbPassword = '',
    [string] $DbName     = 'geraldo_portfolio',

    # Ne pas reinstaller les dependances deja presentes
    [switch] $SkipDeps
)

$ErrorActionPreference = 'Stop'
$root = $PSScriptRoot

# Execute un programme natif sans que sa sortie "erreur" sur stderr
# ne soit interpretee comme une erreur PowerShell.
function Run-Native {
    param([string]$Exe, [string[]]$NativeArgs, [string]$Cwd)

    $previous = $ErrorActionPreference
    $ErrorActionPreference = 'Continue'

    Push-Location $Cwd
    try {
        $output = & $Exe @NativeArgs 2>&1
        $code = $LASTEXITCODE
    } finally {
        Pop-Location
        $ErrorActionPreference = $previous
    }

    return [pscustomobject]@{ Code = $code; Output = ($output | Out-String) }
}

function Step ($message) { Write-Host ''
                           Write-Host "==> $message" -ForegroundColor Cyan }
function Ok   ($message) { Write-Host "    [OK]     $message" -ForegroundColor Green }
function Warn ($message) { Write-Host "    [AVERT]  $message" -ForegroundColor Yellow }
function Fail ($message) { Write-Host "    [ERREUR] $message" -ForegroundColor Red; exit 1 }

# --- 1. Verification de l'environnement ---------------------------------------
Step "Verification de l'environnement"

foreach ($binaire in @('php', 'composer', 'node', 'npm')) {
    if (-not (Get-Command $binaire -ErrorAction SilentlyContinue)) {
        Fail "'$binaire' est introuvable dans le PATH. Installez-le puis relancez ce script."
    }
}

$phpVersion = (Run-Native 'php' @('-r', 'echo PHP_VERSION;') $root).Output.Trim()
if ([version]$phpVersion -lt [version]'8.2.0') {
    Fail "PHP $phpVersion est trop ancien. PHP 8.2 ou superieur est requis."
}
Ok "PHP $phpVersion"
Ok ("Node " + ((Run-Native 'node' @('-v') $root).Output -split "`n" | Where-Object { $_ -match 'v\d' } | Select-Object -First 1).Trim())

$composerInfo = (Run-Native 'composer' @('--version', '--no-ansi') $root).Output
if ($composerInfo -match 'Composer version\s+(\S+)') {
    Ok "Composer $($Matches[1])"
} else {
    Ok 'Composer installe'
}

# --- 2. Base de donnees -------------------------------------------------------
Step 'Base de donnees MySQL'

$pdoArgs = "mysql:host=$DbHost;port=$DbPort;charset=utf8mb4"

$testConnexion = Run-Native 'php' @('-r',
    "try { new PDO('$pdoArgs', '$DbUser', '$DbPassword'); echo 'ok'; } catch (Throwable `$e) { echo 'ko'; }") $root

if ($testConnexion.Output.Trim() -ne 'ok') {
    Fail "Connexion MySQL impossible sur ${DbHost}:${DbPort}. Verifiez que le service MySQL est demarre et les identifiants."
}
Ok "Connexion reussie sur ${DbHost}:${DbPort}"

$existe = Run-Native 'php' @('-r',
    "`$s = new PDO('$pdoArgs', '$DbUser', '$DbPassword'); `$n = `$s->quote('$DbName'); echo `$s->query('SHOW DATABASES LIKE ' . `$n)->fetchColumn() ? 'oui' : 'non';") $root

if ($existe.Code -ne 0) { Fail "Impossible de verifier la base '$DbName' :`n$($existe.Output)" }

if ($existe.Output.Trim() -eq 'oui') {
    Ok "Base '$DbName' deja presente"
} else {
    $creation = Run-Native 'php' @('-r',
        "`$s = new PDO('$pdoArgs', '$DbUser', '$DbPassword'); `$n = `$s->quote('$DbName'); `$s->exec('CREATE DATABASE ' . `$n . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');") $root

    if ($creation.Code -ne 0) { Fail "Creation de la base '$DbName' impossible :`n$($creation.Output)" }
    Ok "Base '$DbName' creee"
}

# --- 3. Fichiers .env ----------------------------------------------------------
Step 'Fichiers de configuration .env'

foreach ($app in @('backend', 'frontend')) {
    $env     = Join-Path $root "$app\.env"
    $exemple = Join-Path $root "$app\.env.example"

    if (-not (Test-Path -LiteralPath $exemple)) {
        Fail "Le fichier $exemple est introuvable."
    }

    if (-not (Test-Path -LiteralPath $env)) {
        Copy-Item -LiteralPath $exemple -Destination $env
        Ok "$app : .env cree a partir de .env.example"
    } else {
        Ok "$app : .env deja present"
    }
}

$envBackend = Join-Path $root 'backend\.env'
(Get-Content -LiteralPath $envBackend -Raw) `
    -replace '(?m)^APP_URL=.*$',       'APP_URL=http://127.0.0.1:8000' `
    -replace '(?m)^DB_HOST=.*$',       "DB_HOST=$DbHost" `
    -replace '(?m)^DB_PORT=.*$',       "DB_PORT=$DbPort" `
    -replace '(?m)^DB_DATABASE=.*$',   "DB_DATABASE=$DbName" `
    -replace '(?m)^DB_USERNAME=.*$',   "DB_USERNAME=$DbUser" `
    -replace '(?m)^DB_PASSWORD=.*$',   "DB_PASSWORD=$DbPassword" |
    Set-Content -LiteralPath $envBackend -Encoding UTF8
Ok 'backend : APP_URL et identifiants de base de donnees alignes'

$envFrontend = Join-Path $root 'frontend\.env'
(Get-Content -LiteralPath $envFrontend -Raw) `
    -replace '(?m)^APP_URL=.*$', 'APP_URL=http://127.0.0.1:8001' |
    Set-Content -LiteralPath $envFrontend -Encoding UTF8
Ok 'frontend : APP_URL aligne sur le port 8001'

# --- 4. Dependances PHP et JavaScript -----------------------------------------
Step 'Dependances'

if ($SkipDeps) {
    Warn 'Installation des dependances ignoree (-SkipDeps)'
} else {
    foreach ($app in @('backend', 'frontend')) {
        $resultat = Run-Native 'composer' @('install', '--no-interaction', '--prefer-dist', '--no-progress') (Join-Path $root $app)
        if ($resultat.Code -ne 0) { Fail "composer install a echoue dans $app.`n$($resultat.Output)" }
        Ok "$app : dependances PHP installees"
    }

    $npm = Run-Native 'npm' @('install', '--no-audit', '--no-fund') (Join-Path $root 'frontend')
    if ($npm.Code -ne 0) { Fail "npm install a echoue dans frontend.`n$($npm.Output)" }
    Ok 'frontend : dependances JavaScript installees'
}

# --- 5. Cles d'application ----------------------------------------------------
Step 'Cles de chiffrement'

foreach ($app in @('backend', 'frontend')) {
    $env   = Join-Path $root "$app\.env"
    $dossier = Join-Path $root $app

    if (Select-String -LiteralPath $env -Pattern '^APP_KEY=.+' -Quiet) {
        Ok "$app : APP_KEY deja presente"
    } else {
        $resultat = Run-Native 'php' @('artisan', 'key:generate', '--force') $dossier
        if ($resultat.Code -ne 0) { Fail "Generation de la cle impossible dans $app." }
        Ok "$app : APP_KEY generee"
    }
}

# --- 6. Tables et contenu ------------------------------------------------------
Step 'Base de donnees du backend'

$backend = Join-Path $root 'backend'

Run-Native 'php' @('artisan', 'config:clear') $backend | Out-Null

# La sauvegarde est prise AVANT toute ecriture : elle conserve le contenu
# reel (textes, photos, formations) si une etape ulterieure echoue.
$sauvegarde = Run-Native 'php' @('backup_database.php') $backend
if ($sauvegarde.Code -eq 0) {
    Ok 'Sauvegarde du contenu actuel creee dans backend/storage/backups'
} else {
    Warn 'Aucune sauvegarde du contenu existant (base vide ou MySQL indisponible).'
}

$migrate = Run-Native 'php' @('artisan', 'migrate', '--force') $backend
if ($migrate.Code -ne 0) { Fail "Les migrations ont echoue.`n$($migrate.Output)" }
Ok 'Tables creees ou mises a jour'

$seed = Run-Native 'php' @('artisan', 'db:seed', '--force') $backend
if ($seed.Code -ne 0) { Fail "Le remplissage initial a echoue.`n$($seed.Output)" }
Ok 'Contenu de reference insere uniquement dans les tables vides (contenu existant conserve)'

Run-Native 'php' @('artisan', 'storage:link') $backend | Out-Null
Ok 'Lien public/storage cree (affichage des images)'

# --- 7. Compilation des assets ------------------------------------------------
Step 'Assets du site'

$frontend = Join-Path $root 'frontend'

Run-Native 'php' @('artisan', 'storage:link') $frontend | Out-Null

$build = Run-Native 'npm' @('run', 'build') $frontend
if ($build.Code -ne 0) { Fail "La compilation des assets a echoue.`n$($build.Output)" }
Ok 'CSS et JavaScript compiles dans frontend/public/build'

# --- Termine -------------------------------------------------------------------
Write-Host ''
Write-Host '=========================================================' -ForegroundColor Green
Write-Host '  Mise au point terminee' -ForegroundColor Green
Write-Host '=========================================================' -ForegroundColor Green
Write-Host ''
Write-Host '  Prochaine etape :  .\demarrer.ps1' -ForegroundColor White
Write-Host ''
Write-Host '  Site public    : http://127.0.0.1:8001'              -ForegroundColor White
Write-Host '  Administration : http://127.0.0.1:8001/admin'       -ForegroundColor White
Write-Host ''
