<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/admin/admin_global.php");

$oListe = new liste($DB_site2, $_GET);

//Paramétrage du fil d'ariane (respecter l'ordre dans l'arbo)
$aFilAriane = array(
	array(
		'titre' => 'Installateurs',
		'lien'  => ''
	)
);

//Paramétrage des entêtes de colonnes
$aEntetes = array(
	'installateur' => 'Installateur',
	'activite'     => 'Actif'
);

$oListe->setFilAriane($aFilAriane);
$oListe->setTitre('Frais de port installateurs');
$oListe->setEntetes($aEntetes);

$sql = "SELECT id, CONCAT('<a href=\"/admin/rubriques/port_installateur/zones/?installateur_id=',id,'\">',societe,'</a>'), CONCAT('<img src=\"/admin/images/icon/button-',CASE activite WHEN '1' THEN 'green' ELSE 'red' END,'.png\" width=\"20\"'), societe as installateur, activite";
$sql .= " FROM fournisseur_condition";
$sql .= " WHERE types LIKE '%installateur%'";
$oListe->setContentSQL($sql);

include_once($oListe->header);
include_once($oListe->menu);
$oListe->showContent();
?>
<link href="/admin/css/style.css" type="text/css" rel="stylesheet">
<?php
include_once($oListe->footer);