<?php

ini_set("display_errors","on");

include_once("host_config_admin.php");
include_once($rootpath."class/email.php");

$DB_site=clone $DB_site2;



$exclu=array("googlebot","as29017","msn","voilabot","ahref","bingbot","acoon","google","yandex","exabot","torservers","as13285");
$exclip=array("80.12.82.212","88.184.216.175","213.44.169.37");


$dfrom=date_create("yesterday");

$DB_site->query("INSERT INTO stat_article_vus (artid,date,nb) SELECT artid,date,nb FROM article_vu WHERE date >".$dfrom->gettimestamp()."");





?>