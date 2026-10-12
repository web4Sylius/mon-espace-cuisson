<?php

require ("../../global.php");


$font="../FreeSans.ttf";
$langue="FR";
if(isset($_GET['langue'])) $langue=$_GET['langue'];

$img=imagecreatetruecolor(280,166);
$white = imagecolorallocate($img, 255, 255, 255);  
$black = imagecolorallocate($img, 0, 0, 0);

$univers=$DB_site->query_first("SELECT * FROM univers WHERE id=".$_GET['id']);
$univers_bg=imagecreatefromjpeg("univers/".$univers['id'].".jpg");
imagecopy($img,$univers_bg,0,0,0,0,280,166);

$txt_x=0;
$rgb=hextorgb("f5df96");
if(isset($_GET['right'])){
    $txt_x=80;
    $rgb=hextorgb("b1469a");
}
$bgcolor=imagecolorallocate($img,$rgb[0],$rgb[1],$rgb[2]);
$rgbl=lighten($rgb);
$bgcolorlight=imagecolorallocate($img,$rgbl[0],$rgbl[1],$rgbl[2]);
$rgbd=darken($rgb);
$bgcolordark=imagecolorallocate($img,$rgbd[0],$rgbd[1],$rgbd[2]);
imagefilledrectangle($img,$txt_x,135,$txt_x+200,155,$bgcolor);
imageline($img,$txt_x,135,$txt_x+200,135,$bgcolorlight);
imageline($img,$txt_x,135,$txt_x,155,$bgcolorlight);
imageline($img,$txt_x,155,$txt_x+200,155,$bgcolordark);
imageline($img,$txt_x+200,135,$txt_x+200,155,$bgcolordark);
$size=imagettfbbox(10,0,$font,$univers['libelle'.$langue]);
$textwidth=$size[4]-$size[6];
imagettftext($img,10,0,$txt_x+(200-$textwidth)/2,150,$black,$font,$univers['libelle'.$langue]);

header("Content-type: image/jpeg");
imagejpeg($img, null, 96);

function hextorgb($hex){
    if(substr($hex,0,1)=="#") $hex=substr($hex,1);
    $sp=str_split($hex,2);
    for($i=0;$i<=2;$i++) $ret[$i]=hexdec($sp[$i]);

    return $ret;
}

function lighten($rgb){
    for($i=0;$i<=2;$i++){
	$rgb[$i]+=50;
	if($rgb[$i]>255) $rgb[$i]=255;
    }
    return $rgb;
}

function darken($rgb){
    for($i=0;$i<=2;$i++){
	$rgb[$i]-=50;
	if($rgb[$i]<0) $rgb[$i]=0;
    }
    return $rgb;
}

?>