# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Projet

Site e-commerce **www.mon-espace-cuisson.com** (électroménager / cuisson), avec son back-office. Code PHP procédural ancien, sans framework ni Composer, servi par Apache depuis `/var/www/html/mec` (PHP 8.0). Le code est partagé historiquement avec www.ma-cave-a-vin.fr (même société), d'où de nombreuses références à `macavavin`.

Fichiers sans rapport avec le site, à ignorer : `README.md`, `composer.json`, `docker-compose.yml`, `CODE_OF_CONDUCT.md`, `license.txt`, `PhpStorm_Reference_Card.pdf` et `workshop/` proviennent du « PhpStorm Workshop » de JetBrains.

## Commandes

Pas de build, pas de tests automatisés, pas de linter configuré. Vérifier la syntaxe de chaque fichier modifié :

```bash
php -l chemin/du/fichier.php
```

Certains fichiers ne passent déjà pas `php -l` (ex. `admin/includes/market_place/Cdiscount_api.php`, les `config.inc.php` Amazon utilisant `__autoload`) : ne pas les considérer comme des régressions.

## Secrets

Tous les identifiants (API, FTP, SMTP, paiement, bases annexes) sont des **constantes PHP définies dans `secrets.php`** à la racine, fichier ignoré par Git. `secrets.example.php` (versionné) liste les constantes attendues avec des valeurs vides.

- Ne jamais réintroduire de valeur en dur : ajouter une constante dans `secrets.php` **et** `secrets.example.php`, puis l'utiliser.
- Un fichier qui utilise une constante doit charger le fichier en tête : `require_once __DIR__.'/<../ selon la profondeur>secrets.php';`
- Dans un heredoc, passer par une variable intermédiaire (`$x = CONSTANTE;` puis `{$x}`).
- Les fichiers `configurations/*/config.php`, `config_mysql.php`, `config_mail.php`, `config_save.php` (identifiants BDD principale, etc.) sont aussi hors Git et propres à chaque serveur.

## Architecture

### Amorçage d'une page front
Chaque page publique (`index.php`, `article.php`, `categorie.php`, `devis.php`…) commence par `include_once('global.php')`, qui :
1. fait `session_start()` puis `extract($_REQUEST)` : les paramètres GET/POST deviennent des variables globales ;
2. inclut `global_include.php` → `host_config.php` (classe `Host` de `class/host.php`, chemin de config figé sur `www.mon-espace-cuisson.com`, connexion `$DB_site`) puis toutes les classes de `class/` et `configurations/www.mon-espace-cuisson.com/config.php` ;
3. instancie `$template` (`class/template.class.php`).

### Environnements
`configurations/www.mon-espace-cuisson.com/config_variables.php` choisit l'environnement selon `$_SERVER['SERVER_NAME']` et définit `ESPACE` (`prod` / `preprod`), `SITE_URL`, `SITE_PATH`, `BACK_PATH`, `ROOT_PATH`, les adresses `MAIL_*`… Le code de paiement et de redirection teste `ESPACE == 'prod'` pour choisir identifiants de production ou de test.

### Accès base de données
Classe maison `DB_Sql` (`class/mysql.php`, sur mysqli) : `$DB_site->query()`, `query_first()`, `fetch_array()`, `insert_id()`. Les requêtes sont construites par concaténation de chaînes ; utiliser `mysqli_real_escape_string($DB_site->link_id, …)` pour toute valeur venant de l'utilisateur. Le back-office utilise aussi `$DB_site2`. Les erreurs SQL sont journalisées dans des fichiers `dberror*.txt` / `dbslow*.txt` (ignorés par Git).

### Templates et traductions
- Gabarits HTML dans `configurations/www.mon-espace-cuisson.com/template/FR/*.html` (~400 fichiers). Les placeholders `[?nom]` sont remplacés par `$template->parts['nom']`.
- `$template->evaltemplate('NomTemplate', 'partie')` charge `NomTemplate.html`, l'évalue et ajoute le résultat à `$template->parts['partie']`. Une page remplit `parts` puis évalue les gabarits englobants.
- `$template->translate('/chemin/cle')` lit les libellés dans la table SQL `traduction` (colonne = langue). Un texte qui ne commence pas par `/` est renvoyé tel quel.
- Cache HTML par langue (`getcache` / `savecache`) dans `configurations/*/cache/`.

### URLs
`.htaccess` (~265 `RewriteRule`) traduit les URLs SEO vers les scripts PHP et redirige les anciens domaines en 301. Pour savoir quel script sert une URL, chercher dans `.htaccess`. `admin/`, `vad/`, `configurations/`, `css/`, `ckeditor/`, `fichiers/` sont servis sans réécriture.

### Back-office (`admin/`)
Environ 530 scripts PHP. Chacun inclut `admin/admin_global.php`, qui fait `extract()` de `$_FILES`, `$_REQUEST` et `$_COOKIE`, gère la connexion (cookie `admin_username`, table `acces_admin`) et fixe `$rootpath = '/var/www/html/mec/'` en dur. Sous-dossiers notables :
- `admin/comparateur/` : flux et API marketplaces (Amazon MWS, Fnac, Cdiscount, La Redoute, Showroomprivé/Mirakl, PriceMinister, Pixmania…), souvent lancés en cron ;
- `admin/marketplace/*.inc` : récupération des commandes marketplaces, incluses par `admin/commande_<marketplace>.php` ;
- `admin/adv/` : transporteurs (GLS, Schenker, Heppner, Girard/Agediss, TNT) ;
- `admin/export/` : exports comptables et statistiques.

### Paiement (`vad/`)
Un sous-dossier par site : `vad/www.mon-espace-cuisson.com/` (Paybox, PayPal) ; `vad/macavavin/` et `vad/macavavincetelem/` (Paybox, PayPal, Adyen) hérités de ma-cave-a-vin. Les retours bancaires écrivent dans des `*.log` locaux.

### Fichiers volumineux ou de données (hors Git)
Images produits `configurations/*/articlefiles/`, uploads `admin/userfiles`, `admin/fournfiles`, `admin/csv`, `admin/mail`, photos SAV `admin/sav/<année>/`, exports CSV `admin/export/**/*.csv`, polices `images/*.ttf`. Voir `.gitignore`.

## Conventions

- Encodage mixte : la plupart des fichiers sont en UTF-8 ou ASCII, mais environ 160 sont en ISO-8859-1 (latin-1). Vérifier avec `file --mime-encoding` et conserver l'encodage d'origine lors d'une modification.
- Code, commentaires, noms de tables et messages en français.
- De nombreux fichiers `*_old.php`, `*.old.php`, `*2.php`, `*_test.php` sont des copies mortes : vérifier qu'un script est réellement inclus ou appelé (`.htaccess`, `include`, cron) avant de le modifier.
