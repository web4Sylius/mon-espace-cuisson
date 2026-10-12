<?php
// display_errors doit rester off : le script emet un fichier telecharge,
// tout avertissement PHP serait ecrit au milieu des lignes de l'export
ini_set('display_errors', 'off');
/**
 * Export comptable des factures fournisseurs de mon-espace-cuisson.com,
 * societe de facturation 2 (ACILYON DEVELOPPEMENTS). Meme format que
 * export_fournisseur_2026.php (societe 1 ACILYON), seuls changent la societe,
 * le nom du fichier et le compte du type de ligne 'garantie'.
 *
 * Portage de l'export MCAV admin/export/export_fournisseur_2026.php sur la base
 * MEC. Les includes sont relatifs (le fichier est en <racine>/admin/export/,
 * comme cote MCAV) et non bases sur DOCUMENT_ROOT : filter_input(INPUT_SERVER,
 * 'DOCUMENT_ROOT') renvoie une chaine vide sous plusieurs SAPI, ce qui faisait
 * echouer les require_once sans aucun message.
 */
$host = 'www.mon-espace-cuisson.com';
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
/**
 * Plan comptable MEC : il est distinct de celui de MCAV et ne vit pas dans la
 * table code_comptable mais dans ces tableaux. Ne jamais y recopier un code MCAV.
 */
$provenance = 'monespacecuisson';
$societeFacturationId = 2;
$nomFichier = 'acilyondev-fournisseurs-acilyondev';
$codeTVADefault = '445660';
$tabCodesAnalytiques = [
    'sls' => '607610', //Montage mon espace cuisson
    'trspt' => '607510', //Port mon espace cuisson
    'eco' => '607160', //Eco-Part mon espace cuisson
    'gar' => '607150', //Garantie mon espace cuisson
    'garantie' => '607150', //Garantie mon espace cuisson
    'avoir' => '607100',
    'fourn' => '607100', //Achat mon espace cuisson
    'part' => '622500',
    'stock' => '604110',
];
$tabCodesCEE = [
    'france' => '3000',
    'cee_tva' => '3009',
    'cee_notva' => '3099',
    'hors_cee' => '3999',
];
if (isset($_GET['admin_username'])) {
    $admin_username = $_GET['admin_username'];
} else {
    $admin_username = null;
}
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
include('xlexport.php');
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
$xl->name = $nomFichier . '_' . date('Ymd', $du) . '-' . date('Ymd', $au);
$xl->addrow(["Date facture", 'Fournisseur', "Facture", "Commande", "Code journal", "Compte", "Code TVA", "Debit", "Credit", "Code analytique", "Date echeance"]);
$requete = $DB_site->query("
    SELECT ff.*
    FROM facture_fournisseur ff, fournisseur_condition fc
    WHERE fc.societe = ff.fournisseur
      AND ff.date_saisie BETWEEN " . intval($du) . " AND " . intval($au) . "
      AND fc.societe_facturation_id = " . intval($societeFacturationId) . "
    ORDER BY ff.date_facture
");
while ($facture = $DB_site->fetch_array($requete)) {
    $nom = $facture['fournisseur'];
    $txtva = (floatval($facture['tva']) / 100);
    $code401 = code401($facture['fournisseur']);
    $codea = codea($facture['fournisseur'], $facture['tva']);
    $codecee = codecee($nom);
    $total_tva = 0;
    $fclient = '';
    // Description fallback pour la colonne N� commande quand elle est vide
    $numero_safe = mysqli_real_escape_string($DB_site->link_id, $facture['numero']);
    $row_desc = $DB_site->query_first("
        SELECT description
        FROM facture_fournisseur_autre
        WHERE facture = '{$numero_safe}'
        LIMIT 1
    ");
    $descriptionFournisseur = $row_desc['description'] ?? '';
    $r2 = $DB_site->query("SELECT * FROM envoi_fournisseur_ligne WHERE facture='" . $facture['numero'] . "'");
    while ($ligne = $DB_site->fetch_array($r2)) {
        $lignefacture = $DB_site->fetch_array($DB_site->query("
            SELECT u.paysid, f.moyenid, f.facturid, f.numerofacture,
                   UNIX_TIMESTAMP(f.dateexpedition) AS dateexpedition,
                   f.provenanceid, f.provenance
            FROM lignefacture lf, facture f, utilisateur u
            WHERE u.userid = f.userid
              AND f.facturid = lf.factureid
              AND lignefactureid = {$ligne['ligne_facture_id']}
        "));
        if ($lignefacture['provenance'] == 'mp') {
            $compte_ana = compte_analytique($lignefacture['provenanceid'], $lignefacture['paysid']);
        } else {
            $compte_ana = compte_analytique($provenance, $lignefacture['paysid']);
        }
        if ($lignefacture['dateexpedition'] > 0) {
            $factureclient = $lignefacture['facturid'] . ' Fa N� ' . $lignefacture['numerofacture'] . ' du ' . date('d/m/Y', $lignefacture['dateexpedition']);
        } else {
            $factureclient = $lignefacture['facturid'] . ' sans correspondance facture client';
        }
        $dateexpedition = '';
        if ($lignefacture['dateexpedition'] > 0) {
            $dateexpedition = date('d/m/Y', $lignefacture['dateexpedition']);
        }
        if ($ligne['paht'] != 0) {
            $xl->addrow(sens_debit_credit([date('d/m/Y', $facture['date_facture']), $facture['fournisseur'], $facture['numero'], numero_commande($factureclient, $facture['fournisseur']), 'AC', compte6xx($ligne['type'], $codecee), $codea, round($ligne['qte'] * $ligne['paht'], 2), '', $compte_ana, date('d/m/Y', $facture['echeance'])]));
            if ($txtva > 0) {
                $xl->addrow(sens_debit_credit([date('d/m/Y', $facture['date_facture']), $facture['fournisseur'], $facture['numero'], numero_commande($factureclient, $facture['fournisseur']), 'AC', $codeTVADefault, $codea, round($ligne['qte'] * $ligne['paht'] * $txtva, 2), '', $compte_ana, date('d/m/Y', $facture['echeance'])]));
            } else {
                $total_tva += $ligne['qte'] * $ligne['paht'];
            }
        }
        if ($ligne['ecopart'] != 0) {
            $xl->addrow(sens_debit_credit([date('d/m/Y', $facture['date_facture']), $facture['fournisseur'], $facture['numero'], numero_commande($factureclient, $facture['fournisseur']), 'AC', compte6xx('eco', $codecee), $codea, round($ligne['qte'] * $ligne['ecopart'], 2), '', $compte_ana, date('d/m/Y', $facture['echeance'])]));
            if ($txtva > 0) {
                $xl->addrow(sens_debit_credit([date('d/m/Y', $facture['date_facture']), $facture['fournisseur'], $facture['numero'], numero_commande($factureclient, $facture['fournisseur']), 'AC', $codeTVADefault, $codea, round($ligne['qte'] * $ligne['ecopart'] * $txtva, 2), '', $compte_ana, date('d/m/Y', $facture['echeance'])]));
            } else {
                $total_tva += $ligne['qte'] * $ligne['ecopart'];
            }
        }
    }
    $r2 = $DB_site->query("SELECT * FROM facture_fournisseur_autre WHERE facture='" . $facture['numero'] . "'");
    while ($ligne = $DB_site->fetch_array($r2)) {
        $compte_ana = '';
        $fclient = '';
        $dateexpedition = '';
        $factureclient = [];
        // Description propre a la ligne courante, et non celle de la facture
        $descriptionLigne = $ligne['description'] ?? '';
        if ($ligne['facturid'] != '') {
            $fclient = '';
            if (strlen($ligne['facturid']) == 4) {
                $stock_cmd = new stock_cmd($DB_site, $ligne['facturid']);
                if (!is_null($stock_cmd->id)) {
                    $fclient = 'STOCK N�' . $stock_cmd->id;
                }
            } else {
                $factureclient = $DB_site->query_first("
                    SELECT u.paysid, f.facturid, f.numerofacture,
                           UNIX_TIMESTAMP(f.dateexpedition) AS dateexpedition,
                           f.provenance, f.provenanceid
                    FROM facture f, utilisateur u
                    WHERE u.userid = f.userid
                      AND f.facturid = '" . intval($ligne['facturid']) . "'
                ");
                if ($factureclient['facturid'] == '') {
                    $factureclient = $DB_site->query_first("SELECT * FROM mp_orders WHERE id='" . $ligne['facturid'] . "'");
                    if ($factureclient['facturid'] != '') {
                        $compte_ana = compte_analytique_mp($factureclient['marketplace']);
                    }
                } else {
                    $prov = $provenance;
                    if ($factureclient['provenance'] == 'mp') {
                        $prov = $factureclient['provenanceid'];
                    }
                    $compte_ana = compte_analytique($prov, $factureclient['paysid']);
                    $fclient = $factureclient['facturid'] . ' Fa N� ' . $factureclient['numerofacture'] . ' du ' . date('d/m/Y', $factureclient['dateexpedition']);
                    if ($factureclient['dateexpedition'] > 0) {
                        $dateexpedition = date('d/m/Y', $factureclient['dateexpedition']);
                    }
                }
            }
        } else {
            $compte_ana = 1;
        }
        if ($ligne['prix'] != 0) {
            $xl->addrow(sens_debit_credit([date('d/m/Y', $facture['date_facture']), $facture['fournisseur'], $facture['numero'], numero_commande($fclient . '  ' . $descriptionLigne, $facture['fournisseur']), 'AC', compte6xx($ligne['type'], $codecee), $codea, round($ligne['prix'], 2), '', $compte_ana, date('d/m/Y', $facture['echeance'])]));
            if ($txtva > 0) {
                $xl->addrow(sens_debit_credit([date('d/m/Y', $facture['date_facture']), $facture['fournisseur'], $facture['numero'], numero_commande($fclient . '  ' . $descriptionLigne, $facture['fournisseur']), 'AC', $codeTVADefault, $codea, round($ligne['prix'] * $txtva, 2), '', $compte_ana, date('d/m/Y', $facture['echeance'])]));
            } else {
                $total_tva += $ligne['prix'];
            }
        }
    }
    $stock = new stock($DB_site);
    $cmds = $stock->getfacture($facture['numero'], 'fourn');
    foreach ($cmds as $items) {
        foreach ($items as $artid => $item) {
            $xl->addrow(sens_debit_credit([date('d/m/Y', $facture['date_facture']), $facture['fournisseur'], $facture['numero'], numero_commande('STOCK N�' . $item->stock_cmd_id, $facture['fournisseur']), 'AC', compte6xx('fourn', $codecee), $codea, round($item->paht, 2), '', 19, date('d/m/Y', $facture['echeance'])]));
            if ($txtva > 0) {
                $xl->addrow(sens_debit_credit([date('d/m/Y', $facture['date_facture']), $facture['fournisseur'], $facture['numero'], numero_commande('STOCK N�' . $item->stock_cmd_id, $facture['fournisseur']), 'AC', $codeTVADefault, $codea, round($item->paht * $txtva, 2), '', 19, date('d/m/Y', $facture['echeance'])]));
            }
            if ($item->ecopartht > 0) {
                $xl->addrow(sens_debit_credit([date('d/m/Y', $facture['date_facture']), $facture['fournisseur'], $facture['numero'], numero_commande('STOCK N�' . $item->stock_cmd_id, $facture['fournisseur']), 'AC', compte6xx('eco', $codecee), $codea, round($item->ecopartht, 2), '', 19, date('d/m/Y', $facture['echeance'])]));
                if ($txtva > 0) {
                    $xl->addrow(sens_debit_credit([date('d/m/Y', $facture['date_facture']), $facture['fournisseur'], $facture['numero'], numero_commande('STOCK N�' . $item->stock_cmd_id, $facture['fournisseur']), 'AC', $codeTVADefault, $codea, round($item->ecopartht * $txtva, 2), '', 19, date('d/m/Y', $facture['echeance'])]));
                }
            }
        }
    }
    $cmds = $stock->getfacture($facture['numero'], 'trspt');
    foreach ($cmds as $items) {
        foreach ($items as $artid => $item) {
            $xl->addrow(sens_debit_credit([date('d/m/Y', $facture['date_facture']), $facture['fournisseur'], $facture['numero'], numero_commande($descriptionFournisseur, $facture['fournisseur']), 'AC', compte6xx('trspt', $codecee), $codea, round($item->paht, 2), '', '', date('d/m/Y', $facture['echeance'])]));
            if ($txtva > 0) {
                $xl->addrow(sens_debit_credit([date('d/m/Y', $facture['date_facture']), $facture['fournisseur'], $facture['numero'], numero_commande($descriptionFournisseur, $facture['fournisseur']), 'AC', $codeTVADefault, $codea, round($item->paht * $txtva, 2), '', '', date('d/m/Y', $facture['echeance'])]));
            }
        }
    }
    $cmds = $stock->getfacture($facture['numero'], 'cmdtrspt');
    foreach ($cmds as $cmd) {
        $xl->addrow(sens_debit_credit([date('d/m/Y', $facture['date_facture']), $facture['fournisseur'], $facture['numero'], numero_commande($descriptionFournisseur, $facture['fournisseur']), 'AC', compte6xx('trspt', $codecee), $codea, round($cmd->tariftrspt, 2), '', '', date('d/m/Y', $facture['echeance'])]));
        if ($txtva > 0) {
            $xl->addrow(sens_debit_credit([date('d/m/Y', $facture['date_facture']), $facture['fournisseur'], $facture['numero'], numero_commande($descriptionFournisseur, $facture['fournisseur']), 'AC', $codeTVADefault, $codea, round($cmd->tariftrspt * $txtva, 2), '', '', date('d/m/Y', $facture['echeance'])]));
        }
    }
    if ($total_tva > 0) {
        $total_tva = $total_tva * 0.2;
        $xl->addrow(sens_debit_credit([date('d/m/Y', $facture['date_facture']), $facture['fournisseur'], $facture['numero'], numero_commande($fclient, $facture['fournisseur']), 'AC', '4456800000', $codea, round($total_tva, 2), '', '', date('d/m/Y', $facture['echeance'])]));
        $xl->addrow(sens_debit_credit([date('d/m/Y', $facture['date_facture']), $facture['fournisseur'], $facture['numero'], numero_commande($fclient, $facture['fournisseur']), 'AC', '4457800000', $codea, '', round($total_tva, 2), '', date('d/m/Y', $facture['echeance'])]));
    }
    $xl->addrow(sens_debit_credit([date('d/m/Y', $facture['date_facture']), $facture['fournisseur'], $facture['numero'], $facture['fournisseur'], 'AC', code401($facture['fournisseur']), '', '', $facture['montant_ttc'], '', date('d/m/Y', $facture['echeance'])]));
}
$xl->export();
/**
 * La colonne "Commande" est suffixee du nom du fournisseur (colonne 2), separe par " - ".
 * Elle ne doit jamais rester vide : a defaut de reference de commande, on y
 * reporte le seul nom du fournisseur.
 */
function numero_commande($valeur, $fournisseur)
{
    if (trim($valeur) === '' || trim($valeur) === '0') {
        return $fournisseur;
    }
    return $valeur . ' - ' . $fournisseur;
}
/**
 * Pas de montant negatif dans l'export : un debit negatif passe en positif
 * au credit, et un credit negatif (total 401 d'un avoir) passe en positif au debit.
 * Une ligne retournee est marquee AVOIR dans la colonne Commande.
 * Colonnes : 1 = Fournisseur, 3 = Commande, 7 = Debit, 8 = Credit.
 */
function sens_debit_credit($ligne)
{
    if ($ligne[7] !== '' && $ligne[7] < 0) {
        $ligne[8] = -$ligne[7];
        $ligne[7] = '';
        $ligne[3] = numero_commande_avoir($ligne[3], $ligne[1]);
    } elseif ($ligne[8] !== '' && $ligne[8] < 0) {
        $ligne[7] = -$ligne[8];
        $ligne[8] = '';
        $ligne[3] = numero_commande_avoir($ligne[3], $ligne[1]);
    }
    return $ligne;
}
/**
 * Insere AVOIR entre le N� de commande et le nom du fournisseur :
 * "N� commande - AVOIR - fournisseur", ou "fournisseur - AVOIR"
 * sans N� de commande (lignes de totaux).
 */
function numero_commande_avoir($numero, $fournisseur)
{
    if ($numero === $fournisseur) {
        return $fournisseur . ' - AVOIR';
    }
    $suffixe = ' - ' . $fournisseur;
    if (substr($numero, -strlen($suffixe)) === $suffixe) {
        return substr($numero, 0, -strlen($suffixe)) . ' - AVOIR' . $suffixe;
    }
    return $numero . ' - AVOIR';
}
/**
 * Compte d'achat complet : code comptable du type de ligne suivi du suffixe CEE.
 * Si le type n'a aucun code dans $tabCodesAnalytiques, on renvoie une chaine vide
 * plutot que le seul suffixe (ex. '3000'), qui ressemble a un compte valide et
 * passerait tel quel en comptabilite.
 * Le type vide correspond aux avoirs saisis sans type.
 */
function compte6xx($type, $codecee)
{
    global $tabCodesAnalytiques;
    if ($type == '') {
        $type = 'avoir';
    }
    if (!isset($tabCodesAnalytiques[$type]) || $tabCodesAnalytiques[$type] == '') {
        return '';
    }
    return $tabCodesAnalytiques[$type] . $codecee;
}
function code401($fournisseur)
{
    global $DB_site;
    $fournisseur_safe = addslashes($fournisseur);
    // Le compte 401 est cherche en priorite sur la ligne 'facture' ; a defaut on
    // accepte celui d'une ligne 'old' (fournisseur archive), qui reste valide.
    $requete = $DB_site->query("
        SELECT `code`
        FROM fournisseur
        WHERE societe = '{$fournisseur_safe}'
          AND type IN ('facture', 'old')
          AND `code` <> ''
        ORDER BY FIELD(type, 'facture', 'old')
        LIMIT 1
    ");
    $f = $DB_site->fetch_array($requete);
    if ($f == false) {
        return '';
    }
    return $f['code'];
}
function compte_analytique($prov, $paysid)
{
    global $DB_site;
    if ($paysid == '') {
        return '';
    }
    $requete = $DB_site->query("
        SELECT *
        FROM compte_analytique
        WHERE site = '{$prov}'
          AND paysid = " . intval($paysid) . "
    ");
    $row = $DB_site->fetch_array($requete);
    return $row['code'];
}
function compte_analytique_mp($mpid)
{
    global $DB_site;
    $requete = $DB_site->query("SELECT * FROM compte_analytique WHERE site = '{$mpid}'");
    if ($requete == false) {
        return false;
    }
    $row = $DB_site->fetch_array($requete);
    return $row['code'];
}
function codecee($fournisseur)
{
    global $DB_site, $tabCodesCEE;
    $fournisseur_safe = mysqli_real_escape_string($DB_site->link_id, $fournisseur);
    $requete = $DB_site->query("
        SELECT p.*
        FROM pays p
        INNER JOIN fournisseur USING(paysid)
        WHERE societe = '{$fournisseur_safe}'
          AND type = 'facture'
    ");
    if ($requete == false) {
        return '';
    }
    $pays = $DB_site->fetch_array($requete);
    if ($pays == false) {
        return $tabCodesCEE['france'];
    }
    if ($pays['tvaintracom'] == 0) {
        if ($pays['cee'] == 1) {
            return $tabCodesCEE['france']; // france
        } else {
            return $tabCodesCEE['hors_cee']; // hors cee
        }
    } else {
        return $tabCodesCEE['cee_notva']; // cee sans tva
    }
}
function codea($fournisseur, $tva)
{
    global $DB_site;
    $codesa = [
        '0' => 'A4',
        '19.6' => 'A2',
        '20' => 'A5',
        '7' => 'V1',
        '5.5' => 'V1',
    ];
    if ($tva == 0) {
        $return = '';
        $fournisseur_safe = mysqli_real_escape_string($DB_site->link_id, $fournisseur);
        $requete = $DB_site->query("
            SELECT p.*
            FROM pays p
            INNER JOIN fournisseur USING(paysid)
            WHERE societe = '{$fournisseur_safe}'
              AND type = 'facture'
        ");
        if ($requete) {
            $pays = $DB_site->fetch_array($requete);
            if ($pays && $pays['cee'] == 1) {
                return $codesa[$tva];
            }
        }
    } else {
        $return = $codesa[$tva] ?? '';
    }
    return $return;
}
