<?php

include_once("../host_config_admin.php");
include_once($rootpath."configurations/".$host."/transporteurs/heppner/heppner.php");
include("../../class/facture.class.php");
include("../../class/stock.class.php");
include("../../class/fpdf.php");
include("pdf.class.php");
include("../../class/php-barcode.php");
include("adv.class.php");
include("heppner.class.php");

$adv=new adv($DB_site2,172276); //cmd site
// $adv=new adv($DB_site2,147379); //cmd stock
// $adv=new adv($DB_site2,149326);
$heppner=new heppner($DB_site2,$adv,$ftp,$host);
//$t=microtime(true);
// var_dump(microtime(true)-$t);
$heppner->test();
// print $heppner->barcode(1);

 //var_dump($heppner->send());
// var_dump($heppner->error());
//$heppner->etiquette();
