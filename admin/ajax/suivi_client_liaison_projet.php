<?php
/*

fichier qui gère les projets des suivi clients
=> permet d'ajouter ou supprimer un projet d'un suivi client


*/


require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");

if(!isset($_GET['action'])  || empty($_GET['action'])) exit;
if(!isset($_GET['table'])   || empty($_GET['table'])) exit;
if(!isset($_GET['suiviid']) || empty($_GET['suiviid'])) exit;

$action  = $_GET['action'];
$suiviid = $_GET['suiviid'];
$table	 = $_GET['table'];

if($action=="delete"){
	if(isset($_GET['projetid']) && !empty($_GET['projetid'])){
		$userid = $DB_site2->query_first("SELECT userid FROM ".$table." WHERE id='".$suiviid."'");
		if($userid[0] == 0){ // si il n'ya pas de userid on risque de perdre le mail alors on le delie du projet en l'associant au client principal du projet
			$user = $DB_site2->query_first("SELECT userid FROM projet WHERE projetid ='".mysqli_real_escape_string($DB_site2->link_id,$_GET['projetid'])."'");
			$sql = "UPDATE ".$table." SET projetid='0', userid = '".$user[0]."' WHERE id='".$suiviid."' ";
		}else{
			$sql="UPDATE ".$table." SET projetid='0' WHERE id='".$suiviid."' ";
		}
		$DB_site2->query($sql);
	}elseif(isset($_GET['facturid']) && !empty($_GET['facturid'])){
		$sql="UPDATE ".$table." SET facturid='0' WHERE id='".$suiviid."' ";
		$DB_site2->query($sql);
	}
}

if($action=="update"){
	if(isset($_GET['projetid']) && !empty($_GET['projetid'])){
		$sql="UPDATE ".$table." SET projetid='".$_GET['projetid']."' WHERE id='".$suiviid."' ";
		$DB_site2->query($sql);
	}
}


if($action=="assoc"){

	if(isset($_GET['projetid']) && !empty($_GET['projetid'])){
		$projetid = $_GET['projetid'];
		$sql="UPDATE ".$table." SET projetid='".$projetid."' WHERE id='".$suiviid."' ";
		$DB_site2->query($sql);
	}elseif (isset($_GET['facturid']) && !empty($_GET['facturid'])) {
		$facturid = $_GET['facturid'];
		$sql="UPDATE ".$table." SET facturid='".$facturid."' WHERE id='".$suiviid."' ";
		$DB_site2->query($sql);
	}


	
}



echo "màj";

?>