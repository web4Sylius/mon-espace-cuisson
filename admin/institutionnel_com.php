<?php
include_once 'admin_global.php';
if(isset($_GET['blocid']) && isset($_GET['contenu'])){
	$commentaire = htmlspecialchars($_GET['contenu']);
	$blocid = $_GET['blocid'];
	$DB_site2->query("UPDATE institutionnel_bloc SET commentaire ='".$commentaire."' WHERE institutionnel_blocid =".$blocid);

}



?>