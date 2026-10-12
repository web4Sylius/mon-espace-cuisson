<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/admin/admin_global.php");
require_once(SITE_PATH . "/class/devises.class.php");

if(!isset($_GET['installateur_id']) || !is_numeric($_GET['installateur_id']))
{
	header('Location: /admin/rubriques/port_installateur/');
	exit;
} 
if(!isset($_GET['zone']) || $_GET['zone'] == '')
{
	header('Location: /admin/rubriques/port_installateur/zones/?installateur_id=' . $_GET['installateur_id']);
	exit;
} 

$fournisseur_id = $_GET['installateur_id'];
$zone_id        = $_GET['zone'];

require_once(SITE_PATH . "/class/fournisseur.class.php");

//Infos sur l'installateur en cours
$oFourn  = new fournisseur($DB_site2,'',$fournisseur_id);
$oListe  = new liste($DB_site2, $_GET, $_POST);
$oDevise = new devises($DB_site2);

//Détails zone
$sql = "SELECT zone_libelle FROM zone WHERE zone_id = " . $zone_id;
$zone_libelle = $DB_site2->query_first($sql)['zone_libelle'];

//Requête de suppression d'enregitrement
if(isset($_POST['del_id']) && is_array($_POST['del_id']))
{
	foreach($_POST['del_id'] as $id)
	{
        $sql = "DELETE FROM prix_installateur";
        $sql .= " WHERE prix_id = " . $id;
        $oListe->delete($sql);
	}
}

//Paramétrage du fil d'ariane (respecter l'ordre dans l'arbo)
$aFilAriane = array(
	array(
		'titre' => 'Installateurs',
		'lien'  => 'port_installateur/'
	),
	array(
		'titre' => 'Zones "' . $oFourn->fournisseur_nom . '"',
		'lien'  => 'port_installateur/zones/?installateur_id=' . $oFourn->fournisseur_id
	),
	array(
		'titre' => 'Tranche(s) de prix zone ' . $zone_libelle
	)
);

$oListe->setFilAriane($aFilAriane);
$oListe->setTitre('Tranche(s) de prix zone ' . $zone_libelle . ' "' . $oFourn->fournisseur_nom . '"');

$oListe->suppression = true;
$oListe->creation    = true;

//Cas particulier Girard Agediss

$oListe->recherche = true;
$oListe->edition   = true;

//Paramétrage des entêtes de colonnes
$aEntetes = array(
    'poidsmin'   => array(
        'libelle'     => 'Poids min (kg)',
        'filtre_type' => 'fourchette'
    ),
    'poidsmax'   => array(
        'libelle'     => 'Poids max (kg)',
        'filtre_type' => 'fourchette'
    ),
    'prix_pvttc' => array(
        'libelle'     => 'Prix de vente (TTC ' . $oDevise->symbole($oFourn->fournisseur_devise) . ')',
        'filtre_type' => 'fourchette'
    ),
    'prix_paht'  => array(
        'libelle'     => 'Prix d\'achat (HT ' . $oDevise->symbole($oFourn->fournisseur_devise) . ')',
        'filtre_type' => 'fourchette'
    ),
    'typeLivr'  => array(
        'libelle'     => 'Type de livraison',
        'filtre_type' => 'select',
        'filtre_sql'  => "SELECT livr_type_id, livr_type_nom FROM livraison_type WHERE fc_id = " . $fournisseur_id
    )
);

echo "SELECT livr_type_id, livr_type_nom FROM livraison_type WHERE fc_id = " . $fournisseur_id;

$sql = "SELECT pi.prix_id, 
    ROUND(pi.prix_poidsmin/1000,1) AS poidsmin, 
    ROUND(pi.prix_poidsmax/1000,1) AS poidsmax, 
    pi.prix_pvttc, 
    pi.prix_paht, 
    lt.livr_type_nom as typeLivr";
$sql .= " FROM prix_installateur pi";
$sql .= " INNER JOIN zone_type zt ON (zt.prix_id = pi.prix_id)";
$sql .= " INNER JOIN livraison_type lt ON (lt.livr_type_id = zt.livr_type_id)";
$sql .= " WHERE pi.zone_id = '" . $zone_id . "'";

//Gestion recherche
if($_POST != array())
{
    //Poids min
    if($_POST['poidsmin_min'] != '' && $_POST['poidsmin_max'] == '') $sql .= " AND pi.prix_poidsmin >= '" . ($_POST['poidsmin_min'] * 1000) . "'";
    if($_POST['poidsmin_min'] == '' && $_POST['poidsmin_max'] != '') $sql .= " AND pi.prix_poidsmax <= '" . ($_POST['poidsmin_max'] * 1000) . "'";
    if($_POST['poidsmin_min'] != '' && $_POST['poidsmin_max'] != '') $sql .= " AND pi.prix_poidsmin >= '" . ($_POST['poidsmin_min'] * 1000) . "' AND pi.prix_poidsmax <= '" . ($_POST['poidsmin_max'] * 1000) . "'";

    //Poids max
    if($_POST['poidsmax_min'] != '' && $_POST['poidsmax_max'] == '') $sql .= " AND pi.prix_poidsmax >= '" . ($_POST['poidsmax_min'] * 1000) . "'";
    if($_POST['poidsmax_min'] == '' && $_POST['poidsmax_max'] != '') $sql .= " AND pi.prix_poidsmax <= '" . ($_POST['poidsmax_max'] * 1000) . "'";
    if($_POST['poidsmax_min'] != '' && $_POST['poidsmax_max'] != '') $sql .= " AND pi.prix_poidsmax >= '" . ($_POST['poidsmax_min'] * 1000) . "' AND pi.prix_poidsmax <= '" . ($_POST['poidsmax_max'] * 1000) . "'";

    //Prix de vente
    if($_POST['prix_pvttc_min'] != '' && $_POST['prix_pvttc_max'] == '') $sql .= " AND pi.prix_pvttc >= '" . $_POST['prix_pvttc_min'] . "'";
    if($_POST['prix_pvttc_min'] == '' && $_POST['prix_pvttc_max'] != '') $sql .= " AND pi.poidsmax <= '" . $_POST['prix_pvttc_max'] . "'";
    if($_POST['prix_pvttc_min'] != '' && $_POST['prix_pvttc_max'] != '') $sql .= " AND pi.prix_pvttc >= '" . $_POST['prix_pvttc_min'] . "' AND pi.prix_poidsmax <= '" . $_POST['prix_pvttc_max'] . "'";

    //Prix d'achat
    if($_POST['prix_paht_min'] != '' && $_POST['prix_paht_max'] == '') $sql .= " AND pi.prix_paht >= '" . $_POST['prix_paht_min'] . "'";
    if($_POST['prix_paht_min'] == '' && $_POST['prix_paht_max'] != '') $sql .= " AND pi.poidsmax <= '" . $_POST['prix_paht_max'] . "'";
    if($_POST['prix_paht_min'] != '' && $_POST['prix_paht_max'] != '') $sql .= " AND pi.prix_paht >= '" . $_POST['prix_paht_min'] . "' AND pi.prix_poidsmax <= '" . $_POST['prix_paht_max'] . "'";

    //Type de livraison
    if($_POST['typeLivr'] != '') $sql .= " AND lt.livr_type_id = " . $_POST['typeLivr'];
}

$oListe->setEntetes($aEntetes);
$oListe->setContentSQL($sql);

include_once($oListe->header);
include_once($oListe->menu);
$oListe->showContent();
?>
<link href="/admin/css/style.css" type="text/css" rel="stylesheet">
<?php
include_once($oListe->footer);
