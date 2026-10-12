<?php
require "admin_global.php";
setcookie("hostname",$host,time()+3600,"/");
include "../class/fonctions.php";


$order =1;
$idpage = intval($_POST['idpage']);
foreach($_POST['instart'] as $artid )
{
	$DB_site2->query_first("UPDATE institutionnel_article SET ordre=".$order." WHERE institutionnelid=".$idpage." AND artid=".$artid);

	$order++;
}


?>