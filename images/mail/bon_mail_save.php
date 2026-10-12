<?php

require ("../../global.php");
$template->changesite($_GET['site_code'],$_GET['langue']);

$font="../FreeSans.ttf";
$bold="../FreeSansBold.ttf";

$img=imagecreatetruecolor(570,270);
$imgbg=imagecreatefromjpeg("commun/bon.jpg");
imagecopy($img,$imgbg,0,0,0,0,570,270);

$marron=imagecolorallocate($img,48,40,37);
$rose=imagecolorallocate($img,164,0,48);
$blanc=imagecolorallocate($img,255,255,255);
$gris=imagecolorallocate($img,119,119,119);

if(isset($_GET['code'])){
    $cadeau=$DB_site->query_first("SELECT * FROM cadeau WHERE code='".mysql_real_escape_string($_GET['code'])."'");
    
    $codesize=imagettfbbox(18,0,$bold,$cadeau['code']);
    imagettftext($img,18,0,287+(234-$codesize[4]-$codesize[6])/2,180,$rose,$bold,$cadeau['code']);
    
    $valeur="-".($cadeau['typecadeauid']==0?$template->price_format($cadeau['valeurcadeau']):$cadeau['valeurcadeau']."%");
    $valsize=imagettfbbox(36,0,$bold,$valeur);
    imagettftext($img,36,0,(28+(240-$valsize[4]-$valsize[6])/2),150,$rose,$bold,$valeur);
    
    imagettftext($img,22,0,14,49,$blanc,$bold,$template->translate("/commande/bon_reduction","U"));
    
    imagettftext($img,14,0,290,147,$gris,$bold,$template->translate("/commande/avec_code","F"));
    
    if($cadeau['datefin']>0){
	$validite=trim($template->translate("/commande/bon_valable","F"))." ".date("d",$cadeau['datefin'])." ".$template->translate("/site/mois/".date("n",$cadeau['datefin']))." ".date("Y",$cadeau['datefin']);
	$txtsize=imagettfbbox(10,0,$font,$validite);
	imagettftext($img,10,0,524-($txtsize[4]-$txtsize[6]),230,$blanc,$font,$validite);
    }
}

header("Content-type: image/jpeg");
imagejpeg($img, null, 90);

?>