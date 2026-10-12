<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/admin/admin_global.php");
require_once(SITE_PATH . "/class/fournisseur.class.php");

if(!isset($_GET['fournisseur_id']) || !is_numeric($_GET['fournisseur_id']))
{
	header('Location: /admin/rubriques/livraison_type/');
	exit;
} 

$fournisseur_id = $_GET['fournisseur_id'];

//Infos sur l'installateur en cours
$oFourn = new fournisseur($DB_site2,'',$fournisseur_id);
$oListe = new liste($DB_site2, $_GET);

//Requête de suppression d'enregitrement
if(isset($_POST['del_id']) && is_array($_POST['del_id']))
{
	foreach($_POST['del_id'] as $id)
	{
		$sql = "DELETE FROM livraison_type WHERE livr_type_id = " . $id;
		$oListe->delete($sql);
	}
}

//Paramétrage du fil d'ariane (respecter l'ordre dans l'arbo)
$aFilAriane = array(
	array(
		'titre' => 'Transporteurs & Installateurs',
		'lien'  => 'livraison_type/'
	),
	array(
		'titre' => 'Types de livraison "' . $oFourn->fournisseur_nom . '"'
	)
);

//Paramétrage des entêtes de colonnes
$aEntetes = array(
	'livr_type_nom' => 'Type',
	'livr_type_desc' => 'Description'
);

$oListe->setFilAriane($aFilAriane);
$oListe->setTitre('Types de livraison "' . $oFourn->fournisseur_nom . '"');
$oListe->setEntetes($aEntetes);
$oListe->creation    = true;
$oListe->suppression = true;
$oListe->edition     = true;

//Cas particulier Girard Agediss
$sql = "SELECT livr_type_id, livr_type_nom, livr_type_desc";
$sql .= " FROM livraison_type";

$oListe->setContentSQL($sql);

include_once($oListe->header);
include_once($oListe->menu);
$oListe->showContent();
?>
<link href="/admin/css/style.css" type="text/css" rel="stylesheet">
<?php
include_once($oListe->footer);