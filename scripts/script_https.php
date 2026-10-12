<?php
require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

$aTable = array('article','conseil','gallery_photo','institutionnel','landing','liens_boutons','marque','marque_archive','news','newsletter','presse','traduction','vente_flash');

foreach($aTable as $la_table)
{
	$sql = 'SHOW COLUMNS FROM ' . $la_table;
	$req = $DB_site->query($sql);

	while($row = $DB_site->fetch_row($req))
	{
		$colonne_nom = $row[0];

		$sql = "UPDATE " . $la_table . " SET `" . $colonne_nom . "` = REPLACE(`" . $colonne_nom . "`, 'http://www.ma-cave-a-vin.fr', 'https://www.ma-cave-a-vin.fr')";
		$DB_site->query($sql);

		$sql = "UPDATE " . $la_table . " SET `" . $colonne_nom . "` = REPLACE(`" . $colonne_nom . "`, 'http://www.my-wine-cabinet.com', 'https://www.my-wine-cabinet.com')";
		$DB_site->query($sql);
	}
}