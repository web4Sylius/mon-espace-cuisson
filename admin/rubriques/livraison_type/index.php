<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/admin/admin_global.php");

$oListe = new liste($DB_site2, $_GET, $_POST);
$oListe->recherche = true;

//Paramétrage du fil d'ariane (respecter l'ordre dans l'arbo)
$aFilAriane = array(
	array(
		'titre' => 'Transporteur & Installateurs',
		'lien'  => ''
	)
);

//Paramétrage des entêtes de colonnes
$aEntetes = array(
	'societe'  => array(
		'libelle' => 'Société',
		'filtre_type' => 'libelle'
	),
	'nb_types' => 'Types de livraison',
	'activite' => array(
		'libelle'     => 'Actif',
		'filtre_type' => 'select',
		'filtre_data' => array(1 => 'Actif', 2 => 'Inactif')
	)
);

$oListe->setFilAriane($aFilAriane);
$oListe->setTitre('Transporteur & Installateurs');
$oListe->setEntetes($aEntetes);

$sql = "SELECT id
	, societe
	, CONCAT('<a href=\"/admin/rubriques/livraison_type/types/?fournisseur_id=',id,'\">',(SELECT COUNT(*) FROM livraison_type WHERE fc_id = id),'</a>')
	, CONCAT('<img src=\"/admin/images/icon/button-',CASE activite WHEN '1' THEN 'green' ELSE 'red' END,'.png\" width=\"20\"')
	, (SELECT COUNT(*) FROM livraison_type WHERE fc_id = id) as nb_types
	, activite";
$sql .= " FROM fournisseur_condition";
$sql .= " WHERE (types LIKE '%installateur%' OR types LIKE '%transporteur%')";
if(isset($_POST['recherche']))
{
	if($_POST['societe'] != '') $sql .= " AND societe LIKE '%" . mysqli_real_escape_string($oListe->DB->link_id,$_POST['societe']) . "%'";
	if($_POST['activite'] != '') $sql .= " AND activite = '" . $_POST['activite'] . "'";
}
$oListe->setContentSQL($sql);

include_once($oListe->header);
include_once($oListe->menu);
$oListe->showContent();
?>
<link href="/admin/css/style.css" type="text/css" rel="stylesheet">
<?php
include_once($oListe->footer);