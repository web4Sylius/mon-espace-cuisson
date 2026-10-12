<?php
// display_errors doit rester off : le script emet un fichier telecharge,
// tout avertissement PHP serait ecrit au milieu des lignes de l'export
ini_set('display_errors', 'off');
/**
 * Export comptable des remises de cheques de mon-espace-cuisson.com.
 *
 * Les includes sont relatifs (le fichier est en <racine>/admin/export/) et non
 * bases sur DOCUMENT_ROOT : filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') renvoie
 * une chaine vide sous plusieurs SAPI, ce qui faisait echouer les require_once
 * sans aucun message.
 */
$host = 'www.mon-espace-cuisson.com';
$admin_username = filter_input(INPUT_COOKIE, 'admin_username', FILTER_SANITIZE_STRING);
if (empty($admin_username)) {
    $admin_username = filter_input(INPUT_GET, 'admin_username', FILTER_SANITIZE_STRING);
}
include_once('../../configurations/' . $host . '/config_mysql.php');
include('../../class/mysql.php');
include('../../class/facture.class.php');
include('../../class/stock.class.php');
include_once('zip.lib.php');
$DB_site = new DB_Sql;
$DB_site->appname = 'boutique';
$DB_site->appshortname = 'boutique';
$DB_site->database = $DBname;
$DB_site->server = $db_server;
$DB_site->user = $DBusername;
$DB_site->password = $DBpassword;
$DB_site->connect();
include('xlexport.php');
if (isset($admin_username) && $admin_username != '') {
    if ($admin_username != '9d3bb895f22bf0afa958d68c2a58ded7') {
        $ip = $_SERVER['REMOTE_ADDR'];
        //Validation d'identite avant autorisation de telechargement
        $acces = $DB_site->query_first("
            SELECT *
            FROM acces_admin
            WHERE ip = '{$ip}'
              AND user = '{$admin_username}'
              AND connecte = 1
            ORDER BY timestamp DESC
            LIMIT 0,1
        ");
        if ($acces['timestamp'] == false || $acces['timestamp'] < time() - 3600) {
            exit;
        } else {
            $user_info = $DB_site->query_first("SELECT * FROM parametresgeneraux WHERE username = '{$admin_username}'");
            if ($user_info['username'] != $admin_username) {
                exit;
            }
        }
    }
} else {
    exit;
}
if (!isset($_GET['du']) || !isset($_GET['au'])) {
    exit;
}
$sp = explode('/', $_GET['du']);
$du = mktime(0, 0, 0, $sp[1], $sp[0], $sp[2]);
$sp = explode('/', $_GET['au']);
$au = mktime(23, 59, 59, $sp[1], $sp[0], $sp[2]);
$xl = new xlexport('csv');
// xlexport n'a qu'un constructeur a l'ancienne (function xlexport()), supprime
// en PHP 8 : on repositionne explicitement le mode CSV, sinon l'export sort en
// tableau HTML dans un fichier nomme .csv.
$xl->csv = true;
$xl->name = 'acilyondev-cheques_' . date('Ymd', $du) . '-' . date('Ymd', $au);
$tabColonnes = [
    'Date remise chèque',
    'N° remise chèque',
    'N° d\'affaire',
    'Code journal',
    'N° compte',
    'Total du bordereau',
    'Montant payé',
];
$xl->addrow($tabColonnes);
$requete = $DB_site->query("
    SELECT *
    FROM cheque
    WHERE date BETWEEN " . intval($du) . " AND " . intval($au) . "
");
$remise = [];
while ($cheque = $DB_site->fetch_array($requete)) {
    $remise[$cheque['remise']][] = [
        date('d/m/Y', $cheque['date']),
        $cheque['remise'],
        $cheque['facturid'],
        $cheque['code_journal'],
        $cheque['code411'],
        '',
        $cheque['montant'],
    ];
}
foreach ($remise as $remiseid => $cheques) {
    foreach ($cheques as $cheque) {
        $xl->addrow($cheque);
    }
    // Total du bordereau : somme de toute la remise, volontairement non bornee
    // par la periode demandee.
    // Les colonnes non agregees doivent etre agregees elles aussi : sans cela la
    // requete est rejetee par MySQL en sql_mode=only_full_group_by (defaut depuis
    // 5.7) et le total du bordereau ne sortait pas du tout.
    $r = $DB_site->query_first("
        SELECT MIN(date) AS date,
               remise,
               MIN(code_journal) AS code_journal,
               MIN(compte) AS compte,
               SUM(montant) AS total
        FROM cheque
        WHERE remise = " . intval($remiseid) . "
        GROUP BY remise
    ");
    // La ligne de total doit avoir autant de colonnes que l'en-tete : la derniere
    // colonne ('Montant payé') reste vide, le total est dans 'Total du bordereau'.
    $xl->addrow([
        date('d/m/Y', $r['date']),
        $r['remise'],
        '',
        $r['code_journal'],
        $r['compte'],
        $r['total'],
        '',
    ]);
}
$xl->export();
