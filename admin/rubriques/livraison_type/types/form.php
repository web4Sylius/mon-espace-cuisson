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
$oForm = new form($DB_site2, $_POST, $_GET);

//Paramétrage du fil d'ariane (respecter l'ordre dans l'arbo)
$aFilAriane = array(
	array(
		'titre' => 'Transporteurs & Installateurs',
		'lien'  => 'livraison_type/types/?fournisseur_id=' . $oFourn->fournisseur_id
	),
	array(
		'titre' => 'Nouveau type de livraison "' . $oFourn->fournisseur_nom . '"'
	)	
);

//Définition des champs éditables
$aChamps = array(
	array('libelle' => 'Libellé', 'type' => 'text', 'name' => 'livr_type_nom', 'req' => true),
	array('libelle' => 'Description', 'type' => 'textarea', 'name' => 'livr_type_desc', 'desc' => 'Cette description sera affichée dans l\'email de commande de transport/installation.')
);

$oForm->setFilAriane($aFilAriane);
$oForm->setTitre('Nouveau type de livraison "' . $oFourn->fournisseur_nom . '"');
$oForm->setChamps($aChamps);

if(isset($_GET['action']) && $_GET['action'] == 'edit')
{
	$sql = "SELECT livr_type_nom, livr_type_desc";
	$sql .= " FROM livraison_type";
	$sql .= " WHERE livr_type_id = " . $id;
	$oForm->setValues($sql);
}

//Traitement du formulaire
if($_POST != array())
{ 
	if($oForm->isValid())
	{
		if(isset($_POST['edit_id']))
		{
			$sql = "UPDATE livraison_type SET";
			$sql .= " livr_type_nom = '" . mysqli_real_escape_string($DB_site2->link_id,$_POST['livr_type_nom']). "'";
			$sql .= ", livr_type_desc = '" . mysqli_real_escape_string($DB_site2->link_id,$_POST['livr_type_desc']) . "'";
			$sql .= " WHERE livr_type_id = " . $_POST['edit_id'];
			$DB_site2->query($sql);
		}
		else
		{
			$sql = "INSERT INTO livraison_type (";
			$sql .= "livr_type_nom";
			$sql .= ", livr_type_desc";
			$sql .= ", fc_id";
			$sql .= ") VALUES (";
			$sql .= "'" . mysqli_real_escape_string($DB_site2->link_id,$_POST['livr_type_nom']) . "'";
			$sql .= ", '" . mysqli_real_escape_string($DB_site2->link_id,$_POST['livr_type_desc']) . "'";
			$sql .= ", " . $oFourn->fournisseur_id;
			$sql .= ")";
			$DB_site2->query($sql);
		}
		
		header('Location: index.php?fournisseur_id=' . $oFourn->fournisseur_id . '&ajout=1');
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