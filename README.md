# Portfolio Geraldo Perridys AGONSE

Site vitrine professionnel et back-office d'administration.
Deux applications Laravel 12 dans un même dossier.

```
geraldo-portfolio/
├── installer.ps1          Mise au point complète (à lancer une fois, ou après une coupure)
├── demarrer.ps1          Démarre / arrête les deux serveurs locaux
├── backend/              API JSON (Laravel 12 + Sanctum + MySQL)  -> port 8000
├── frontend/             Site public + administration (Blade + Livewire 4 + Tailwind 4) -> port 8001
└── Cahier_des_charges_Portfolio_Geraldo_Perridys_AGONSE.pdf
```

Le contenu éditorial (textes, formations, services, témoignages, photos, coordonnées)
vit uniquement dans la base du `backend`. Le `frontend` le récupère par l'API :
le propriétaire n'a donc jamais à toucher au code.

---

## 1. Remise en route après une coupure du PC

### Étape unique — mise au point

```powershell
.\installer.ps1
```

Le script est **idempotent** : il peut être relancé autant de fois que nécessaire.

Il vérifie PHP 8.2+, Composer, Node et NPM ; crée la base MySQL si elle manque ;
recrée les fichiers `.env` ; installe les dépendances ; génère les clés
`APP_KEY` ; exécute les migrations et le remplissage initial ; crée le lien
`public/storage` ; compile les assets ; et dépose une sauvegarde SQL.

### Démarrage

```powershell
.\demarrer.ps1
```

| Adresse | Rôle |
|---|---|
| http://127.0.0.1:8001 | Site public |
| http://127.0.0.1:8001/admin | Administration |
| http://127.0.0.1:8000/api/v1/site | API backend |

MySQL doit tourner. Pour l'arrêter :

```powershell
.\demarrer.ps1 -Stop
```

> **Après un reformatage du disque**, relancer `.\installer.ps1` restaure
> l'environnement. Les données du site sont dans MySQL ; une sauvegarde manuelle
> se fait avec `php backend\backup_database.php` (dossier `backend\storage\backups`).

---

## 2. Prerequis

| Outil | Version |
|---|---|
| PHP | 8.2 ou supérieur (extensions `pdo_mysql`, `mbstring`, `openssl`, `curl`, `fileinfo`) |
| Composer | 2.x |
| Node.js | 18 ou supérieur (avec NPM) |
| MySQL / MariaDB | 5.7+ / 10.4+ |

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

> Le mot de passe livré est `Geraldo@2026` : **changez-le à la première
> connexion**. Un mot de passe oublié se réinitialise sans interface, en ligne
> de commande :
>
> ```powershell
> cd backend
> php artisan tinker --execute="App\Models\User::find(1)->update(['password' => 'NouveauMotDePasse2026']);"
> ```
>
> > **Le mot de passe est écrit en clair dans ce dépôt** : ici, dans
> > `backend/database/seeders/DatabaseSeeder.php` et dans les cinq scripts
> > d'audit (`verify_cc.php`, `verify_api.php`, `verify_frontend.php`,
> > `verify_extra_phones.php`, `verify_isolated.php`), qui en ont besoin pour
> > ouvrir une session réelle. Un dépôt **public** ne doit donc jamais être
> > associé à ce projet : gardez-le privé, ou changez le mot de passe et
> > mettez ces cinq références à jour avant de publier le code.

---

## 4. Sauvegardes

```powershell
cd backend
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
cd backend
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

Les deux serveurs doivent tourner :

```powershell
php verify_cc.php                    # conformité au cahier des charges
php backend\verify_api.php           # audit de l'API backend
php backend\verify_password.php      # changement de mot de passe administrateur
php frontend\verify_frontend.php     # site public et administration
php frontend\verify_extra_phones.php # numéros supplémentaires et WhatsApp
```

État au dernier passage : `90 conformes / 0 à corriger / 12 à valider`,
`42/0` côté API, `15/0` côté mot de passe, `121/0` côté frontend, `29/0` côté
numéros.

> **Audit du mot de passe.** `verify_password.php` crée un compte
> administrateur temporaire, joue le changement de mot de passe de bout en
> bout, puis supprime le compte. Il ne touche jamais au vrai mot de passe :
> le lancer sur une installation en production est sans risque.

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

- les audits du frontend et des numéros s'envoient entre eux des
  demandes via l'API, qui est limitée à **5 appels/minute/IP**. Les laisser
  s'exécuter coup sur coup produit des `429` aléatoires : attendre une
  minute entre les deux.
- les audits modifient le contenu du site (titre, accroche, coordonnées)
  puis le restaurent. Prendre une sauvegarde avant de les lancer sur une
  installation en production.

### Piège : les réglages ne sont plus écrasés

`PUT /api/v1/admin/settings` n'écrit que les clés réellement transmises.
Un envoi partiel ne vide donc plus les photos, l'accroche ni le SEO — ce
qui arrivait auparavant et faisait perdre le contenu du site. Corollaire :
pour supprimer toute la liste des numéros supplémentaires, le formulaire
envoie le marqueur `extra_phones_present`, car une liste vide ne produit
aucun champ `extra_phones[...]`.

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
5. Renseigner `backend/.env` :

   ```
   MAIL_USERNAME=geraldoagonse@gmail.com
   MAIL_PASSWORD=les 16 caracteres, sans espace
   ```

6. Redémarrer puis tester :

   ```powershell
   .\demarrer.ps1 -Stop ; .\demarrer.ps1
   cd backend ; php verify_email.php
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

- achat du domaine et hébergement, puis mise à jour de `APP_URL` et `API_URL` ;
- certificat HTTPS et redirection `http` → `https` ;
- `APP_DEBUG=false` dans `backend/.env` **et** `frontend/.env` : avec `true`,
  une erreur affiche le détail technique aux visiteurs ;
- identifiants SMTP (section 6) — facultatif, le site fonctionne sans ;
- changement du mot de passe administrateur (mot de passe initial `Geraldo@2026`,
  présent en clair dans le dépôt — voir §3) ;
- mesure d'audience et Search Console ;
- pages légales (mentions légales, politique de confidentialité).
