<?php
require_once($_SERVER['DOCUMENT_ROOT']."/global.php");


//Sélection de tous les articles ayant une vidéo
$articles = $DB_site->query("SELECT artid, video FROM article WHERE video != '' ");
while($row = $DB_site->fetch_array($articles)){
	/* On remplace le text "iframe..." par un tableau (lien, hauteur, largeur) pour 
	le nouveau formulaire */
	$test = preg_split("/ /", htmlentities($row['video']));
	$width = preg_split("/&quot;/", $test[1]);
	$height = preg_split("/&quot;/", $test[2]);
	$src = preg_split("/&quot;/", $test[3]);
	$tab[$row['artid']] = $src[1].",".$width[1].",".$height[1];
}

// Boucle pour mettre à jour le champs video avec les nouvelles données
foreach($tab as $key => $value){
	$sql = "UPDATE article";
	$sql.= " SET video ='".$value."' ";
	$sql.= " WHERE artid =".$key;
	$DB_site->query($sql);
}


?>