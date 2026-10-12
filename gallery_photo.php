<?php
/**
* Resizes an image to a fixed size with black padding.
*
* @link        http://section31.us/scripts/
* @param        string    $srcFile        Filename of the image you want resized
* @param        int        $dstWidth        Resize to Width
* @param        int        $dstHeight        Resize to Height
* @param        string    $dstFile        Destination filename
* @param        int        $jpegQuality    Jpeg Quality Rating

* Usage:        resizeImage('test.jpg', 640, 480);
*/

// function resizeImage($srcFile, $dstWidth = 120, $dstHeight = 90, $dstFile = null, $jpegQuality = 80)

$id=intval($_GET['id']);
$dstWidth=intval($_GET['dstWidth']);
$dstHeight=intval($_GET['dstHeight']);

$jpegQuality=80;
$dstFile="configurations/www.mon-espace-cuisson.com/images/cache/gallery/".$id."_".$dstWidth."_".$dstHeight.".jpg";
$cachedelai=0;
$cache=true;
$time=false;

if(file_exists($dstFile) && $cache){
    if(time()-filemtime($dstFile)<$cachedelai){
	header("Content-type: image/jpeg");
	$cachelimit=time()+$cachedelai;
	header("Expires: ".gmdate('D, d M Y H:i:s \G\M\T',$cachelimit));
	print file_get_contents($dstFile);
	exit;
    }
    unlink($dstFile);
}

$srcFile="configurations/www.mon-espace-cuisson.com/images/gallery/".$_GET['id'].".jpg";
if(!file_exists($srcFile)) $srcFile="images/templates/ma-cave-a-vin.jpg";

list($srcWidth, $srcHeight, $type ) = getimagesize($srcFile);

switch ($type) {
case 1 :
  $srcHandle = imagecreatefromgif($srcFile);
  break;
case 2 :
  $srcHandle = imagecreatefromjpeg($srcFile);
  break;
case 3 :
  $srcHandle = imagecreatefrompng($srcFile);
  break;
default :
  echo 'File Type Not Supported! ';
  return false;
}
if (!$srcHandle) {
  echo 'Could not execute imagecreatefrom() function! ';
  return false;
}


  // Source is wider than the Destination
  if ($srcWidth>$dstWidth) {
    $width = $dstWidth;
    $xOffset = 0;
  }else{
    $width=$srcWidth;
    $xOffset=round(($dstWidth-$width)/2);
  }
  $height = round(($width / $srcWidth) * $srcHeight);
  $yOffset = round(($dstHeight - $height) / 2);

  if($height>$dstHeight){
      $height=$dstHeight;
      $width=round(($height/$srcHeight)*$srcWidth);
      $xOffset=round(($dstWidth-$width)/2);
      $yOffset = round(($dstHeight - $height) / 2);
  }
  
$dstHandle = imagecreatetruecolor($dstWidth, $dstHeight);
$white = imagecolorallocate($dstHandle, 255, 255, 255);  
imagefill($dstHandle, 0, 0, $white); 

// bool imagecopyresampled ( resource dst_image, resource src_image, int dst_x, int dst_y, int src_x, int src_y, int dst_w, int dst_h, int src_w, int src_h )
if (!imagecopyresampled($dstHandle, $srcHandle, $xOffset, $yOffset, 0, 0, $width, $height, $srcWidth, $srcHeight)) {
  echo 'Could not execute imagecopyresampled() function! ';
  return false;
}
if($time){
  $red = imagecolorallocate($dstHandle, 255,0,0); 
  imagestring($dstHandle,3,0,0,date("H:i:s"),$red);
}
imagedestroy($srcHandle);

if($dstWidth>120){
    $srcLogo = imagecreatefromgif("configurations/www.mon-espace-cuisson.com/images/templates/logo_wm.gif");

//     $logoWidth=60;
//     $logoHeight=60;
//     $dstLogo=imagecreatetruecolor($logoWidth,$logoHeight);
//     imagecolortransparent($dstLogo,imagecolorallocate($dstLogo, 0, 0, 0));

//     imagecopyresampled($dstLogo,$srcLogo,0,0,0,0,$logoWidth,$logoHeight,75,75);

    imagecopymerge($dstHandle,$srcLogo,intval($xOffset),intval($dstHeight/2-40),0,0,56,56,50);
}
header("Content-type: image/jpeg");
$cachelimit=time()+$cachedelai;
header("Expires: ".gmdate('D, d M Y H:i:s \G\M\T',$cachelimit));

imagejpeg($dstHandle, $dstFile, $jpegQuality);
print file_get_contents($dstFile);

imagedestroy($dstHandle);

return true;

?> 