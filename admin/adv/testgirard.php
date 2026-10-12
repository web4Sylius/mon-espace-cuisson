<?php

include("../admin_global.php");
include("../../class/facture.class.php");
include("../../class/stock.class.php");
include("../../class/fpdf.php");
include("../../class/php-barcode.php");
include("../../class/fournisseur.class.php");
include("adv.class.php");
include("girard_agediss.class.php");

$adv=new adv($DB_site2,149872); //cmd site
// $adv=new adv($DB_site2,147379); //cmd stock
$heppner=new girard_agediss($DB_site2,$adv);
$t=microtime(true);
// var_dump(microtime(true)-$t);

// print $heppner->barcode(1);
// print $heppner->encode();
// var_dump($heppner->error);
$heppner->etiquette();
