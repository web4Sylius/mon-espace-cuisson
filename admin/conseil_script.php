<?php
require "admin_global.php";
setcookie("hostname",$host,time()+3600,"/");
include "../class/fonctions.php";

$order =1;
$idpage = intval($_POST['idpage']);
$version = $_POST['version'];
foreach($_POST['conseilart'] as $artid )
{
	$DB_site2->query("UPDATE conseil_article_".$version." SET ordre=".$order." WHERE conseilid=".$idpage." AND artid=".$artid);

	$order++;
}


?>