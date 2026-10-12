<?php
include_once("admin_global.php");
include_once ("./header.php");
include_once ("./menu.php");

$path = $rootpath."configurations/".$host."/images/pj_email/";
$extensions_valides = array('jpg','png','gif');

//traitement POST
if(isset($_FILES['ajout_image'])){
	$extension_upload = strtolower(pathinfo($_FILES['ajout_image']['name'], PATHINFO_EXTENSION));
	if ( in_array($extension_upload,$extensions_valides) ){
		$md5=md5_file($_FILES['ajout_image']['tmp_name']);
		if(!img_exist($DB_site2,$md5)){
			$id_img = uniqid();
			$nom = $path.$id_img.".".$extension_upload;
			move_uploaded_file($_FILES['ajout_image']['tmp_name'], $nom);
			insert_img($DB_site2,$md5,$id_img,$extension_upload);
		}

	}
}


?>
<style type="text/css">
	.pjmail img{max-width: 200px;}
</style>
<h1>Gestion des PJ founisseurs</h1>
<br><br>
<p> Ajouter une pièce-jointe :<br>
<form method="post" action="#" enctype="multipart/form-data">
	<input type="file" name="ajout_image">

	<input type="submit" value="enregistrer" style="margin-top: 5px;">
</form>
</p>

<h3>Listes des pièces-jointes déja enregistrées</h3>

<div class="col-xs-12">
	
<?php
//liste des images : 
$html="";
$images = $DB_site2->query("SELECT * FROM pj_mail");
while ($image =$DB_site2->fetch_array($images)) {
	$html .= "<div class=\"col-xs-3 pjmail\"><img src=\"../configurations/".$host."/images/pj_email/".$image['nom'].".".$image['ext']."\"></div>";
}

echo $html;
?>

</div>




<?php

function insert_img($DB,$md5,$nom,$ext){
	$DB->query("INSERT INTO pj_mail SET md5='$md5', nom='$nom', ext='$ext'");
}

function img_exist($DB,$md5){
	$result = $DB->query_first("SELECT id FROM pj_mail WHERE md5='$md5'");
	if(is_null($result['id']))
		return false;
	else
		return true;
}

?>