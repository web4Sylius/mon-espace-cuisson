<?php
include_once 'admin_global.php';
if(isset($_GET['blocid']) && isset($_GET['contenu'])){
	$commentaire = mysqli_real_escape_string($DB_site2->link_id,($_GET['contenu']));
	$blocid = $_GET['blocid'];
	$DB_site2->query("UPDATE conseil_bloc_".$_GET['version']." SET commentaire ='".$commentaire."' WHERE conseil_blocid =".$blocid);

}



?>