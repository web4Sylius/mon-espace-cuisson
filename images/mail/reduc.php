<?php

$pc="-".intval($_GET['pc'])."%";
$size=intval($_GET['size']);
$fontsize=intval($size/4);

$img=imagecreatetruecolor($size,$size);
// imageantialias($img, true);

$white=imagecolorallocate($img,255,255,255);
$pink=imagecolorallocate($img,212,31,85);
$black = imagecolorallocate($img, 0, 0, 0);
$font="../FreeSans.ttf";

imagecolortransparent($img, $black);

imagefilledellipse($img,($size/2),($size/2),$size-1,$size-1,$pink);
imagefilledrectangle($img,0,0,($size/2),($size/2),$pink);

$txtsize=imagettfbbox($fontsize,0,$font,$pc);
imagettftext($img,$fontsize,0,($size-$txtsize[4]-$txtsize[6])/2,($size-$txtsize[7]-$txtsize[1])/2,$white,$font,$pc);


header("Content-type: image/png");
imagepng($img,null,7);
// imagejpeg($img, null, 90);

?>