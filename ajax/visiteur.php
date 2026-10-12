<?php
require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

$action = mysqli_real_escape_string($DB_site->link_id,$_GET['action']);
$device = mysqli_real_escape_string($DB_site->link_id,$_GET['device']);
$browser = mysqli_real_escape_string($DB_site->link_id,$_GET['browser']);
$vbrowser = mysqli_real_escape_string($DB_site->link_id,$_GET['v']);
$provenance = mysqli_real_escape_string($DB_site->link_id,$_GET['provenance']);
if($provenance == ""){
	$provenance = "site";
}
$commercial = "";
if(isset($_COOKIE['admin_username']) && $_COOKIE['admin_username'] != ""){
    $commercial = $_COOKIE['admin_username'];
}
$mail="";
if($_GET['mail'] != 'undefined'){
	$mail = mysqli_real_escape_string($DB_site->link_id,$_GET['mail']);
}

if($action == "new"){
	if(!isVisiteur($s,$DB_site,$mail) && !isUser($DB_site,$mail)){
		newVisiteur($s,$DB_site,$browser,$vbrowser,$device,$mail,$provenance,$template->langue);
	}elseif(isVisiteur($s,$DB_site,$mail) && !isUser($DB_site,$mail)){
		$action = "update";
		updateVisiteur($DB_site,$s,'mail',$mail);
	}
}

if(isset($_POST['action']) && $_POST['action'] == "update"){
	$nomChamps = mysqli_real_escape_string($DB_site->link_id,$_POST['name']);
	$content = mysqli_real_escape_string($DB_site->link_id,$_POST['content']);
	if($nomChamps == "mail"){
		if(isUser($DB_site,$content)){
			$DB_site->query("DELETE FROM visiteur WHERE s='$s'");
		}else{
			updateVisiteur($DB_site,$s,$name,$content);
		}
	}else{
		updateVisiteur($DB_site,$s,$name,$content);
	}
	
}

function isVisiteur($s,$DB,$mail=""){
	
	$sqlwhere="";
	if($mail !="") $sqlwhere = " OR mail='$mail'";
	$v = $DB->query_first("SELECT * FROM visiteur WHERE s = '$s'".$sqlwhere);
	if($v[0] != "")
		return true;
	else
		return false;
}

function isUser($DB,$mail=""){
	if($mail!=""){
		$u = $DB->query_first("SELECT userid FROM utilisateur WHERE mail = '$mail'");
		if($u[0] != "") 
			return true;
		else
			return false;
	}
	return false;
}

function newVisiteur($s,$DB,$browser,$vbrowser,$device,$mail,$provenance,$langue){
	$DB->query("INSERT INTO visiteur SET s='$s', browser='$browser', browser_version='$vbrowser', device='$device', mail='$mail',dateinscription='".time()."',provenance='$provenance', commercial='$commercial', langue='$langue'");
}

function updateVisiteur($DB,$s,$name,$content){
	$DB->query("UPDATE visiteur SET $name='$content' WHERE s='$s'");
}