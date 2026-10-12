<?php
require_once __DIR__.'/../../secrets.php';

include("../admin_global.php");
include(SITE_PATH."/class/facture.class.php");
include(SITE_PATH."/class/stock.class.php");
include(SITE_PATH."/class/fpdf.php");
include(SITE_PATH."/class/pdf.class.php");
include(SITE_PATH."/class/php-barcode.php");
include(SITE_PATH."/class/fournisseur.class.php");
include("adv.class.php");
include("gls.class.php");


$adv=new adv($DB_site2,172691); //cmd site

// $adv=new adv($DB_site2,147379); //cmd stock
$gls=new gls($DB_site2,$adv,GLS_TEST_USER);
// var_dump(microtime(true)-$t);
$gls->send(true);
$gls->etiquette(false,"",0);
// print $heppner->barcode(1);
// print $heppner->encode();
// var_dump($heppner->error);
//$gls->encode();
//$gls->send();

//var_dump($test);