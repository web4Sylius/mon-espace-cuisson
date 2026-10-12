<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/admin/admin_global.php");

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
$oFourn = new fournisseur($DB_site2,'',$fournisseur_id);
$oListe = new liste($DB_site2, $_GET);


//Détails zone
$sql = "SELECT zone_libelle FROM zone WHERE zone_id = " . $zone_id;
$zone_libelle = $DB_site2->query_first($sql)['zone_libelle'];

//Requête de suppression d'enregitrement
if(isset($_POST['del_id']) && is_array($_POST['del_id']))
{
	foreach($_POST['del_id'] as $id)
	{
        $sql = "DELETE FROM zone_departement";
        $sql .= " WHERE zd_id = " . $id;
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
		'titre' => 'Département(s) zone ' . $zone_libelle
	)
);

$oListe->setFilAriane($aFilAriane);
$oListe->setTitre('Département(s) zone ' . $zone_libelle . ' "' . $oFourn->fournisseur_nom . '"');
$oListe->suppression = true;
$oListe->creation    = true;

//Cas particulier Girard Agediss
$sql = "";

//Paramétrage des entêtes de colonnes
$aEntetes = array(
    'id_departement' => 'Département'
);

$sql = "SELECT zd.zd_id, CONCAT(d.code,' (',d.nom_departement,')') as departement, d.id_departement";
$sql .= " FROM zone_departement zd";
$sql .= " INNER JOIN departement d ON (d.id_departement = zd.departement_id)";
$sql .= " WHERE zd.zone_id = '" . $zone_id . "'";

$oListe->setEntetes($aEntetes);
$oListe->setContentSQL($sql);

include_once($oListe->header);
include_once($oListe->menu);
$oListe->showContent();
?>
<link href="/admin/css/style.css" type="text/css" rel="stylesheet">
<?php
include_once($oListe->footer);
