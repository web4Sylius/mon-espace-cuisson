<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/admin/admin_global.php");

if(!isset($_GET['installateur_id']) || !is_numeric($_GET['installateur_id']))
{
	header('Location: /admin/rubriques/port_installateur/');
	exit;
}
$fournisseur_id = $_GET['installateur_id'];

require_once(SITE_PATH . "/class/fournisseur.class.php");

//Infos sur l'installateur en cours
$oFourn = new fournisseur($DB_site2,'',$fournisseur_id);
$oForm = new form($DB_site2, $_POST, $_GET);

//Paramétrage du fil d'ariane (respecter l'ordre dans l'arbo)
$aFilAriane = array(
	array(
		'titre' => 'Installateurs',
		'lien'  => 'port_installateur/'
	),
	array(
		'titre' => 'Nouvelle zone "' . $oFourn->fournisseur_nom . '"'
	)	
);

//Définition des champs éditables
$aChamps = array(
	array('libelle' => 'Libellé', 'type' => 'text', 'name' => 'zone_libelle', 'req' => true)
);

$oForm->setFilAriane($aFilAriane);
$oForm->setTitre('Nouvelle zone "' . $oFourn->fournisseur_nom . '"');
$oForm->setChamps($aChamps);

if(isset($_GET['action']) && $_GET['action'] == 'edit')
{
	$sql = "SELECT zone_libelle, zone_id";
	$sql .= " FROM zone";
	$sql .= " WHERE zone_id = " . $id;
	$oForm->setValues($sql);
}

//Traitement du formulaire
if($_POST != array())
{ 
	if($oForm->isValid())
	{
		if(isset($_POST['edit_id']))
		{
			$sql = "UPDATE zone SET";
			$sql .= " zone_libelle = '" . $_POST['zone_libelle'] . "'";
			$sql .= " WHERE zone_id = " . $_POST['edit_id'];
		}
		else
		{
			$sql = "INSERT INTO zone (";
			$sql .= "zone_libelle";
			$sql .= ", fc_id";
			$sql .= ") VALUES (";
			$sql .= "'" . $_POST['zone_libelle'] . "'";
			$sql .= ", " . $oFourn->fournisseur_id;
			$sql .= ")";
		}
		$DB_site2->query($sql);

		header('Location: index.php?installateur_id=' . $oFourn->fournisseur_id . '&ajout=1');
		exit;
	}
}

include_once($oForm->header);
include_once($oForm->menu);
$oForm->showContent();
?>
<link href="/admin/css/style.css" type="text/css" rel="stylesheet">
<?php
include_once($oForm->footer);