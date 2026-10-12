<?php
include_once("../host_config_admin.php");
include("../../class/facture.class.php");
include("../../class/stock.class.php");
include("../../class/fpdf.php");
include("pdf.class.php");
include("../../class/php-barcode.php");
include("adv.class.php");
include("schenker.class.php");
include_once("../../configurations/www.mon-espace-cuisson.com/transporteurs/schenker/schenker.php");


// $adv=new adv($DB_site2,103835); // 2 colis FR
$adv=new adv($DB_site2,7974);
//$adv=new adv($DB_site2,146530); // inter 2 colis
// $adv=new adv($DB_site2,145478); // montaigu 85
// $adv=new adv($DB_site2,148675); // montaigu 02
// $adv=new adv($DB_site2,146390); // luxembourg

//$heppner=new schenker($DB_site2,$adv,$compte_facturation,$ftp);

$t=microtime(true);
// var_dump(microtime(true)-$t);

// print $heppner->barcode(1);
/*$filename="IECSV_ACIL".date("YmdHis")."_".$adv->id.".txt";
$path=__DIR__."/schenker/test/".$filename;
file_put_contents($path,$heppner->encode());*/
//print $heppner->encode();
$schenker = new schenker($DB_site2,$adv,$compte_facturation,$ftp,$host);
//var_dump($schenker->send(true));
// var_dump($heppner->error);
$schenker->etiquette(); /// Pour faire fonctionner l'étiquette, il faut commenter le var_dump $schenker->send(true)
//var_dump($heppner->send(true));
// var_dump($heppner->testftp());
//$heppner->update_plan();
