<?php
// display_errors doit rester off : le script emet un fichier telecharge,
// tout avertissement PHP serait ecrit au milieu du document
ini_set('display_errors', 'off');
/**
 * Cut off (FNP) de mon-espace-cuisson.com : commandes fournisseur / transporteur
 * / installateur pas encore facturees.
 *
 * Portage de l'export MCAV admin/export/export_cutoff.php sur la base MEC.
 * ATTENTION : ce fichier va de pair avec ooo.php, qui doit etre deploye en meme
 * temps. ooo.php inclut lui-meme zip.lib.php, inutile de le reprendre ici.
 */

$host = 'www.mon-espace-cuisson.com';

if (isset($_GET['admin_username'])) {
    $admin_username = $_GET['admin_username'];
} elseif (isset($_COOKIE['admin_username'])) {
    $admin_username = $_COOKIE['admin_username'];
} else {
    $admin_username = '';
}

include_once('../../configurations/' . $host . '/config_mysql.php');
include('../../class/mysql.php');
include('ooo.php');
// Les classes de ooo.php n'avaient que des constructeurs a l'ancienne, supprimes
// en PHP 8 : sans la version corrigee, aucune cellule n'est remplie et le .ods
// sort vide, sans la moindre erreur. On le detecte ici plutot que de livrer un
// document muet.
$sonde = new ooo_cell('sonde');
if ($sonde->content === null) {
    exit('ooo.php doit etre redeploye en meme temps que cet export : sa version actuelle est incompatible PHP 8.');
}

$DB_site = new DB_Sql;
$DB_site->appname = 'boutique';
$DB_site->appshortname = 'boutique';
$DB_site->database = $DBname;
$DB_site->server = $db_server;
$DB_site->user = $DBusername;
$DB_site->password = $DBpassword;
$DB_site->connect();

if (isset($admin_username) && $admin_username != '') {
    if ($admin_username != '9d3bb895f22bf0afa958d68c2a58ded7') {
        $ip = $_SERVER['REMOTE_ADDR'];
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

$doc = new ooo();
$doc->name = 'acilyondev-FNP-' . date('Y-m-d', $au);
$t = new ooo_table('Cut off');

$s4 = new ooo_style('total');
$s4->family = 'table-cell';
$s4->text_align = 'right';
$s4->background_color = '#f6ff6a';
$s4->border_bottom = '1pt solid #909090';
$s4->border_top = $s4->border_left = $s4->border_right = $s4->border_bottom;
$s5 = new ooo_style('title');
$s5->family = 'table-cell';
$s5->background_color = '#DCDCDC';
$s5->text_align = 'center';
$s5->border_bottom = '1pt solid #909090';
$s5->border_top = $s5->border_left = $s5->border_right = $s5->border_bottom;
$s6 = new ooo_style('prix');
$s6->family = 'table-cell';
$s6->font_color = '#000000';
$s6->text_align = 'right';
$s6->border_bottom = '1pt solid #909090';
$s6->border_top = $s6->border_left = $s6->border_right = $s6->border_bottom;
$doc->addstyle($s4);
$doc->addstyle($s5);
$doc->addstyle($s6);

$t->set_col(1, '4cm');
$t->set_col(2, '6cm');
$t->set_col(4, '4cm');

$c = new ooo_cell('Cut off au ' . date('d/m/Y', $au));
$t->addcell(1, 1, $c);

$types = ['fourn' => 'fournisseur', 'trspt' => 'transporteur', 'sls' => 'installateur'];

$top = 3;

foreach ($types as $type => $ntype) {
    $r = [];
    $r[] = new ooo_cell('Commandes ' . $ntype, 'title');
    $r[] = new ooo_cell('', 'title');
    $r[] = new ooo_cell('', 'title');
    $r[] = new ooo_cell('', 'title');
    $r[] = new ooo_cell('', 'title');
    $t->addrow($top, $r);
    $r = [];
    $r[] = new ooo_cell('Date commande ' . $ntype, 'title');
    $r[] = new ooo_cell(ucfirst($ntype), 'title');
    $r[] = new ooo_cell('N commande', 'title');
    $r[] = new ooo_cell('Date facture', 'title');
    $r[] = new ooo_cell('Total HT', 'title');
    $t->addrow($top + 1, $r);

    $totalht = 0;
    $i = $top + 2;
    $sql = "
        SELECT ((efl.paht + efl.ecopart) * efl.qte) AS tarif,
               ef.type, ef.destinataire, f.facturid, f.paysid, ef.date, f.heurefacture,
               UNIX_TIMESTAMP(f.dateexpedition) AS dateexpedition
        FROM envoi_fournisseur_ligne efl
        INNER JOIN lignefacture lf ON lf.lignefactureid = efl.ligne_facture_id
        INNER JOIN facture f ON f.facturid = lf.factureid
        INNER JOIN envoi_fournisseur ef ON ef.id = efl.efid
        WHERE efl.facture = ''
          AND ef.destinataire <> '0'
          AND ef.type = '{$type}'
          AND ef.destinataire NOT LIKE '%Acilyon'
          AND UNIX_TIMESTAMP(f.dateexpedition) BETWEEN {$du} AND {$au}
    ";
    $requete = $DB_site->query($sql);
    while ($ligne = $DB_site->fetch_array($requete)) {
        $ligneht = $ligne['tarif'];
        if ($ligneht > 0) {
            $totalht += $ligneht;
            $r = [];
            $r[] = new ooo_cell(date('d/m/Y', $ligne['date']));
            $r[] = new ooo_cell(mb_convert_encoding($ligne['destinataire'], 'UTF-8', 'ISO-8859-1'));
            $r[] = new ooo_cell($ligne['facturid']);
            $r[] = new ooo_cell(date('d/m/Y', $ligne['dateexpedition']));
            $r[] = new ooo_cell($ligneht, 'prix', 'currency');
            $t->addrow($i, $r);
            $i++;
        }
    }
    $c = new ooo_cell('Total cut off ' . $ntype);
    $c2 = new ooo_cell($totalht, 'total', 'currency');
    $t->addcell(3, $i, $c);
    $t->addcell(5, $i, $c2);
    $top = $i + 2;
}

$doc->addtable($t);
$doc->generate();

?>
