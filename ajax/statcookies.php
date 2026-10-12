<?php
require_once($_SERVER['DOCUMENT_ROOT']."/global.php");
$aAdminIPS = array();
if(ESPACE == 'prod'){
    $aAdminIPS[] = '80.12.82.212';
    $aAdminIPS[] = '213.44.169.37';
}
$action = mysqli_real_escape_string($DB_site->link_id,$_POST['action']);
$choix = mysqli_real_escape_string($DB_site->link_id,$_POST['choix']);

$mcav=false;
if((isset($_COOKIE['admin_username']) && $_COOKIE['admin_username'] != "") || in_array($_SERVER['REMOTE_ADDR'], $aAdminIPS)) $mcav = true;

if($action == "stats" && !$mcav){
	if(!statexist($DB_site,$s)){
		$DB_site->query("INSERT INTO rgpd SET s='$s', datec='".time()."', choix='$choix'");
	}
}

function statexist($DB,$s){
	$test = $DB->query_first("SELECT * FROM rgpd WHERE s='$s'");
	if($test[0]!="")
		return true;
	else
		return false;
}