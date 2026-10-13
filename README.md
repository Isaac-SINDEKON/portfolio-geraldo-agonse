# Portfolio Geraldo Perridys AGONSE

Site vitrine professionnel et back-office d'administration.

Une seule application Laravel 12 sert le site public, l'administration et l'API
sur un unique port : plus de dossier `frontend`, plus de serveur à faire
communiquer avec un autre.

```
geraldo-portfolio/
├── installer.ps1          Mise au point complète sur un PC Windows (à lancer une fois, ou après une coupure)
├── demarrer.ps1           Démarre / arrête le serveur local (port 8000)
├── geraldoportfolio/      Application complète (Laravel 12 + Sanctum + Livewire 4 + Tailwind 4)
└── Cahier_des_charges_Portfolio_Geraldo_Perridys_AGONSE.pdf
```

Le contenu éditorial (textes, formations, services, témoignages, photos,
coordonnées) vit uniquement dans la base de données, et se modifie depuis
l'administration : le propriétaire n'a donc jamais à toucher au code.

---

## 1. Remise en route après une coupure du PC

### Étape unique — mise au point

```powershell
.\installer.ps1
```

Le script est **idempotent** : il peut être relancé autant de fois que nécessaire.

Il vérifie PHP 8.2+, Composer, Node et NPM ; crée la base MySQL si elle manque ;
recrée le fichier `.env` ; installe les dépendances ; génère la clé `APP_KEY` ;
exécute les migrations et le remplissage initial ; crée le lien `public/storage` ;
compile les assets ; et dépose une sauvegarde SQL.

### Démarrage

```powershell
.\demarrer.ps1
```

| Adresse | Rôle |
|---|---|
| http://127.0.0.1:8000 | Site public |
| http://127.0.0.1:8000/admin | Administration |
| http://127.0.0.1:8000/api/v1/site | API publique |

MySQL doit tourner. Pour l'arrêter :

```powershell
.\demarrer.ps1 -Stop
```

> **Ces deux scripts servent uniquement au développement sur Windows.** Ils ne
> sont pas nécessaires au site en ligne et ne bloquent aucun déploiement : un
> hébergeur démarre l'application avec ses propres commandes (voir section 9).

> **Après un reformatage du disque**, relancer `.\installer.ps1` restaure
> l'environnement. Les données du site sont dans MySQL ; une sauvegarde manuelle
> se fait avec `php geraldoportfolio\backup_database.php` (dossier
> `geraldoportfolio\storage\backups`).

---

## 2. Prerequis

| Outil | Version |
|---|---|
| PHP | 8.2 ou supérieur (extensions `pdo_mysql` ou `pdo_pgsql`, `mbstring`, `openssl`, `curl`, `fileinfo`) |
| Composer | 2.x |
| Node.js | 18 ou supérieur (avec NPM) |
| Base de données | MySQL / MariaDB 5.7+ / 10.4+, ou PostgreSQL, ou SQLite |

Paramètres modifiables : `.\installer.ps1 -DbUser root -DbPassword secret -DbName geraldo_portfolio`

---

## 3. Administration

`/admin` — accès par l'adresse e-mail de l'administrateur et le mot de passe.

L'administration permet de modifier, sans toucher au code :

- le contenu du site (identité, accroche, textes « à propos », coordonnées, SEO) ;
- les formations (durée, programme, objectifs, public, modalités, publication) ;
- les domaines, services et expériences ;
- les témoignages ;
- les photos de galerie et les photos de profil ;
- les demandes reçues (demandes de formation et devis).

Les accès se changent depuis **Tableau de bord → Mot de passe** : le formulaire
demande le mot de passe actuel, puis le nouveau saisi deux fois (8 caractères
minimum). Par sécurité, toutes les sessions sont fermées et il faut se
reconnecter avec le nouveau mot de passe.

> **Le mot de passe initial est défini dans `geraldoportfolio/database/seeders/DatabaseSeeder.php`**.
> **Changez-le immédiatement à la première connexion** via *Tableau de bord → Mot de passe*.
> En cas d'oubli, réinitialisez en ligne de commande :
>
> ```powershell
> cd geraldoportfolio
> php artisan tinker --execute="App\Models\User::find(1)->update(['password' => 'VotreNouveauMotDePasse2026']);"
> ```

---

## 4. Sauvegardes

```powershell
cd geraldoportfolio
php backup_database.php          # sauvegarder
php backup_database.php --list   # voir les sauvegardes disponibles
```

Chaque sauvegarde produit **deux fichiers indissociables** :

| Fichier | Contenu |
|---|---|
| `geraldo_portfolio_AAAA-MM-JJ_HH-MM-SS.sql` | tables et données (textes, formations, coordonnées, demandes) |
| `geraldo_portfolio_AAAA-MM-JJ_HH-MM-SS_photos.zip` | photos de la galerie et photo de profil |

> **Pourquoi deux fichiers ?** La base ne memorise que le *chemin* d'une image
> (`uploads/xxx.jpg`), pas l'image elle-même. Restaurer le SQL seul laisserait
> la galerie et la photo de profil vides sur le site. Conservez toujours les
> deux, et `--list` signale les sauvegardes dont le ZIP est absent.

### Restauration

```powershell
cd geraldoportfolio
php backup_database.php --restore storage\backups\geraldo_portfolio_AAAA-MM-JJ_HH-MM-SS.sql
```

Restaure la base **et** les photos en une commande, à partir du seul fichier
SQL (le ZIP est retrouvé automatiquement s'il porte le même nom). Le script
refuse les chemins contenant `..`.

> Après une restauration, relancez `.\demarrer.ps1` pour recréer le lien
> `public/storage`.

À automatiser quotidiennement dès la mise en ligne.

---

## 5. Contrôle de bon fonctionnement

Le serveur doit tourner (`.\demarrer.ps1`).

```powershell
cd geraldoportfolio
php artisan test               # suite de tests automatique (31 tests)
php verify_password.php        # changement de mot de passe administrateur
php verify_email.php           # rendu et envoi réel des e-mails de demande
```

- `php artisan test` vérifie le rendu des pages, les couleurs de marque, les
  liens de contact, le relais d'images et les services internes sans dépendre
  d'une base externe (SQLite en mémoire).
- `verify_password.php` crée un compte administrateur temporaire, joue le
  changement de mot de passe de bout en bout via l'API, puis supprime le
  compte. Il ne touche jamais au vrai mot de passe : le lancer sur une
  installation en production est sans risque.
- `verify_email.php` rend le gabarit d'e-mail, affiche la configuration SMTP
  active et tente un envoi réel.

> **Téléphone des clients.** Le client choisit son pays puis saisit son numéro
> national, et les deux sont conservés : l'indicatif change la conversion, donc
> `0151609682` n'est pas le même contact au Bénin (`+229`) et au Togo (`+228`).
>
> | Saisie | Pays choisi | Lien produit |
> |---|---|---|
> | `01 96 12 34 56` | Bénin | `wa.me/2290151609682` |
> | `96 27 52 45` (avant 2024) | Bénin | `wa.me/2290196275245` |
> | `90 11 22 33` | Togo | `wa.me/22890112233` |
> | `06 12 34 56 78` | France | `wa.me/33612345678` |
> | `+229 01 96 27 52 45` | quelconque | accepté tel quel |
>
> Le Bénin impose depuis 2024 un `01` en tête qui fait partie du numéro : il
> n'est jamais retiré. Un numéro trop court ou incohérent avec le pays choisi
> est refusé avec un message qui donne un exemple de saisie, pour éviter une
> demande enregistrée avec un numéro impossible à joindre. Les demandes
> antérieures à ce sélecteur (sans `indicatif_pays`) restent lisibles au Bénin.

> **Liens email.** Un clic sur un lien `mailto:` n'ouvre rien si l'ordinateur
> n'a pas de logiciel de messagerie associé au protocole. Chaque lien email du
> site copie donc aussi l'adresse dans le presse-papiers et affiche une
> confirmation, sans jamais empêcher l'ouverture du client de messagerie.

Deux précautions :

- l'API publique limite les demandes à **5 appels/minute/IP** ; enchaîner
  plusieurs audits coup sur coup peut produire des `429` aléatoires : attendre
  une minute entre deux.
- certains audits modifient le contenu du site (titre, accroche, coordonnées)
  puis le restaurent. Prendre une sauvegarde avant de les lancer sur une
  installation en production.

---

## 6. Email : envoi automatique des demandes

Le site **fonctionne sans email**. Chaque demande est enregistrée en base
*avant* toute tentative d'envoi : si le serveur d'envoi est absent ou en panne,
le client reçoit quand même sa confirmation et la demande reste visible dans
**Administration → Demandes**. Rien n'est perdu, aucun message d'erreur.

L'email n'est donc **pas** un préalable à la mise en ligne : c'est un confort.

### Outlook n'est pas nécessaire

Le projet ne dépend d'aucun logiciel de messagerie. L'envoi passe par un serveur
SMTP externe. Trois solutions, toutes gratuites :

| Solution | Limite | Commentaire |
|---|---|---|
| **Gmail + mot de passe d'application** | 500 msg/jour | Déjà configuré, aucune ligne de code |
| **Brevo** | 300 msg/jour | SDK déjà présent dans Laravel |
| **SMTP de l'hébergeur** | variable | Souvent inclus dans un hébergement mutualisé |

### Procédure Gmail (recommandée, déjà prête)

1. Ouvrir le compte `geraldoagonse@gmail.com` → **Paramètres Google → Comptes**.
2. Activer la **validation en deux étapes** (obligatoire, Gmail refuse
   l'authentification SMTP sans elle).
3. Revenir dans **Paramètres Google → Sécurité → Validation en deux étapes →
   Mots de passe d'application**.
4. Générer un mot de passe d'application nommé `Site portfolio` → Google
   affiche **16 caractères** à copier une seule fois.
5. Renseigner `geraldoportfolio/.env` :

   ```
   MAIL_USERNAME=geraldoagonse@gmail.com
   MAIL_PASSWORD=les 16 caracteres, sans espace
   ```

6. Redémarrer puis tester :

   ```powershell
   .\demarrer.ps1 -Stop ; .\demarrer.ps1
   cd geraldoportfolio ; php verify_email.php
   ```

   `Envoi reel : reussi` confirme que tout est branché.

> **À ne pas confondre** : `MAIL_PASSWORD` attend le **mot de passe
> d'application** de 16 caractères, jamais le mot de passe Google du compte.
> Avec le vrai mot de passe, Gmail répond `535 Authentication Required`.

`MAIL_FROM_ADDRESS` est l'adresse d'expédition, `MAIL_LEADS_ADDRESS` le
destinataire des demandes. Les deux sont volontairement séparés : changer
l'expéditeur (par exemple pour passer à `contact@geraldoagonse.com` le jour où
le domaine existe) ne doit jamais faire perdre une demande.

### Alternative Brevo

`MAIL_HOST=smtp-relay.brevo.com`, `MAIL_PORT=587`, `MAIL_USERNAME` et
`MAIL_PASSWORD` sont la clé API du compte. `MAIL_FROM_ADDRESS` doit être une
adresse vérifiée chez Brevo.

---

## 7. Avant la mise en ligne

Reste à faire, hors développement :

- achat du domaine et hébergement, puis mise à jour de `APP_URL` ;
- certificat HTTPS et redirection `http` → `https` ;
- `APP_DEBUG=false` dans `geraldoportfolio/.env` : avec `true`, une erreur
  affiche le détail technique aux visiteurs ;
- identifiants SMTP (section 6) — facultatif, le site fonctionne sans ;
- **changement du mot de passe administrateur** (défini dans `DatabaseSeeder.php`, à changer via l'interface d'administration) ;
- mesure d'audience et Search Console ;
- pages légales (mentions légales, politique de confidentialité).

---

## 8. Variables d'environnement

L'application lit sa configuration dans `geraldoportfolio/.env` en local, et
dans les variables d'environnement du serveur en ligne. Celles qui comptent :

| Variable | Rôle | Exemple |
|---|---|---|
| `APP_KEY` | Clé de chiffrement (sessions, cookies). **Obligatoire.** | `php artisan key:generate --show` |
| `APP_URL` | Adresse publique du site (liens, images, mails). | `https://mon-domaine.com` |
| `APP_DEBUG` | `false` en ligne (sinon une erreur montre le code). | `false` |
| `APP_ENV` | `production` en ligne. | `production` |
| `DB_CONNECTION` | Type de base : `mysql` (le projet), `pgsql` ou `sqlite`. | `mysql` |
| `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Connexion à la base. | `127.0.0.1`, `3306`, `geraldo_portfolio`, `root`, — |
| `DB_URL` | Alternative : chaîne de connexion complète. | `mysql://user:pass@host/db` |
| `MAIL_*` | Envoi des demandes (section 6). | — |

> **La base du projet est MySQL.** En local, `installer.ps1` crée la base
> `geraldo_portfolio` et renseigne le `.env`. En ligne, indiquer `DB_CONNECTION=mysql`
> avec les identifiants de l'hébergeur (ou une `DB_URL`/`DATABASE_URL` en
> `mysql://…`). PostgreSQL et SQLite restent possibles.

Génération de la clé :

```powershell
cd geraldoportfolio ; php artisan key:generate --show
```

En ligne, générer une clé **une seule fois** et la conserver : sinon chaque
redéploiement fermerait les sessions d'administration.

---

## 9. Déploiement

Le code ne dépend d'**aucun hébergeur en particulier**. Il vous faut simplement :

1. un environnement **PHP 8.2+** (avec `composer`) ;
2. une **base de données** (MySQL, PostgreSQL ou SQLite) ;
3. définir les variables de la section 8 (`APP_KEY`, `APP_URL`, `APP_DEBUG=false`, connexion base, éventuellement `MAIL_*`).

### Option A — Hébergement conteneurisé (Docker)

Un `Dockerfile` est fourni dans `geraldoportfolio/`. Il compile les assets
(`npm run build`), installe PHP, Composer et les extensions de base de données
(`pdo_mysql`, `pdo_pgsql`, `pdo_sqlite`), puis, à chaque démarrage : migrations,
remplissage initial et `storage:link`. Rien à compiler soi-même : l'image est
prête à servir.

- Dossier racine du service : `geraldoportfolio`
- Port d'écoute : celui annoncé par la variable `PORT` (8080 par défaut)
- Contrôle de santé : `/health` (répond `ok` sans toucher à la base)

> **Images téléversées.** Le disque d'un conteneur est effacé à chaque
> redéploiement : les photos ajoutées depuis l'administration disparaissent.
> Pour les conserver, monter un volume persistant sur
> `geraldoportfolio/storage/app/public`.

### Option B — Hébergement PHP classique (mutualisé, VPS)

1. Transférer le dossier `geraldoportfolio/`.
2. Faire pointer la racine web du domaine sur `geraldoportfolio/public`.
3. En ligne de commande, dans `geraldoportfolio/` :

   ```bash
   composer install --no-dev --optimize-autoloader

   # Compilation des styles et scripts (indispensable : sinon erreur 500).
   # Si l'hébergeur n'a pas Node.js, compiler d'abord sur votre PC
   # (npm ci && npm run build) puis transférer le dossier public/build.
   npm ci && npm run build

   php artisan key:generate --force        # une seule fois, ou définir APP_KEY
   php artisan migrate --force
   php artisan db:seed --force
   php artisan storage:link
   php artisan config:cache
   ```

4. Créer le fichier `.env` (ou définir les variables) selon la section 8.

En production, `APP_DEBUG=false` et `APP_URL` = votre domaine. Le serveur doit
avoir les droits d'écriture sur `geraldoportfolio/storage` et
`geraldoportfolio/bootstrap/cache`.

> **Photos.** Les images téléversées depuis l'administration ne sont **pas**
> dans le dépôt de code (dossier `storage/app/public/uploads`). En transférant
> le site, il faut soit les recopier à l'identique, soit les re-téléverser
> depuis l'administration.
