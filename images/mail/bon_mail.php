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
$noir=imagecolorallocate($img,0,0,0);

if(isset($_GET['code'])){
    $cadeau=$DB_site->query_first("SELECT * FROM cadeau WHERE code='".mysql_real_escape_string($_GET['code'])."'");
    
    $codesize=imagettfbbox(18,0,$bold,$cadeau['code']);
    imagettftext($img,18,0,287+(234-$codesize[4]-$codesize[6])/2,183,$noir,$bold,$cadeau['code']);
    
    $logofile="../templates/".$template->site_code()."/".$template->langue."/logo2014.png";
    $logo=imagecreatefrompng($logofile);
    $size=getimagesize($logofile);
    imagecopyresized($img,$logo,294,64,0,0,192,round(192*$size[1]/$size[0]),$size[0],$size[1]);
    
    $valeur="-".($cadeau['typecadeauid']==0?$template->price_format($cadeau['valeurcadeau']):$cadeau['valeurcadeau']."%");
    $valsize=imagettfbbox(36,0,$bold,$valeur);
    imagettftext($img,36,0,(29+(253-$valsize[4]-$valsize[6])/2),120,$rose,$bold,$valeur);

    imagettftext($img,22,0,14,40,$blanc,$bold,$template->translate("/commande/bon_reduction","U"));
    
    imagettftext($img,14,0,150,183,$gris,$bold,$template->translate("/commande/avec_code","F"));
    
    $conditions=$template->translate("/commande/bon_conditions","F");
    $nbchr=strlen($conditions);
    $space=strpos($conditions," ",round($nbchr/2));
    $validite1=substr($conditions,0,$space);
    $validite2=substr($conditions,$space+1);
    $txtsize=imagettfbbox(10,0,$font,$validite1);
    imagettftext($img,10,0,540-($txtsize[4]-$txtsize[6]),216,$blanc,$font,$validite1);
    $txtsize=imagettfbbox(10,0,$font,$validite2);
    imagettftext($img,10,0,540-($txtsize[4]-$txtsize[6]),230,$blanc,$font,$validite2);
}

header("Content-type: image/jpeg");
imagejpeg($img, null, 90);

?>