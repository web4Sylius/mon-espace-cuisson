<?php
// ini_set("display_errors",1);
/**
* Resizes an image to a fixed size with black padding.
*
* @link        http://section31.us/scripts/
* @param        string    $srcFile        Filename of the image you want resized
* @param        int        $_GET['dstWidth']        Resize to Width
* @param        int        $_GET['dstHeight']        Resize to Height
* @param        string    $dstFile        Destination filename
* @param        int        $jpegQuality    Jpeg Quality Rating

* Usage:        resizeImage('test.jpg', 640, 480);
*/
include_once("host_config.php");

$path = "configurations/".$host_conf->path."/images/";

$jpegQuality = 80;
$cachedelai  = 86400*30;
$dstFile     = $path."cache/".$_GET['artid']."_".$_GET['dstWidth']."_".$_GET['dstHeight'].".jpg";
$cache       = true;
if(isset($_GET['nowm'])) $cache=false;
$time        = false;

$srcFile = $path."produits/".$_GET['artid'].".jpg";
if(!file_exists($srcFile)){
     $srcFile = $path."templates/ma-cave-a-vin.jpg";
}


$lastModified = filemtime($srcFile);
$etagFile     = md5($lastModified);

$etagHeader = (isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : false);
if($etagHeader == $etagFile){
    header("HTTP/1.1 304 Not Modified");
    exit;
}

if(file_exists($dstFile) && $cache){
     
     if($lastModified < filemtime($dstFile)){
          
          header("Content-type: image/jpeg");
          $cachelimit=time()+$cachedelai;
          header("Last-Modified: ".gmdate("D, d M Y H:i:s", $lastModified)." GMT");
          header("Etag: $etagFile");
          header("Expires: ".gmdate('D, d M Y H:i:s \G\M\T',$cachelimit));
          print file_get_contents($dstFile);
          exit;
          
     }
     
     unlink($dstFile);
}


list($srcWidth, $srcHeight, $type, ) = getimagesize($srcFile);

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
if ($srcWidth>$_GET['dstWidth']){
     $width   = $_GET['dstWidth'];
     $xOffset = 0;
}else{
     $width   = $srcWidth;
     $xOffset = round(($_GET['dstWidth']-$width)/2);
}

$height = round(($width / $srcWidth) * $srcHeight);
$yOffset = round(($_GET['dstHeight'] - $height) / 2);

if($height>$_GET['dstHeight']){
     
     $height  = $_GET['dstHeight'];
     $width   = round(($height/$srcHeight)*$srcWidth);
     $xOffset = round(($_GET['dstWidth']-$width)/2);
     $yOffset = round(($_GET['dstHeight'] - $height) / 2);
     
}
  
$dstHandle = imagecreatetruecolor($_GET['dstWidth'], $_GET['dstHeight']);
$white     = imagecolorallocate($dstHandle, 255, 255, 255);  
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

if($_GET['dstWidth']>120 && !isset($_GET['nowm'])){
    $srcLogo = imagecreatefromgif($path."templates/logo_wm.gif");

//     $logoWidth=60;
//     $logoHeight=60;
//     $dstLogo=imagecreatetruecolor($logoWidth,$logoHeight);
//     imagecolortransparent($dstLogo,imagecolorallocate($dstLogo, 0, 0, 0));

//     imagecopyresampled($dstLogo,$srcLogo,0,0,0,0,$logoWidth,$logoHeight,75,75);

    imagecopymerge($dstHandle,$srcLogo,intval($xOffset),intval($_GET['dstHeight']/2-40),0,0,56,56,50);
}

header("Content-type: image/jpeg");
header("Last-Modified: ".gmdate("D, d M Y H:i:s", $lastModified)." GMT");
header("Etag: $etagFile");
$cachelimit=time()+$cachedelai;
header("Expires: ".gmdate('D, d M Y H:i:s \G\M\T',$cachelimit));
imagejpeg($dstHandle, $dstFile, $jpegQuality);
print file_get_contents($dstFile);

imagedestroy($dstHandle);

return true;

?> 