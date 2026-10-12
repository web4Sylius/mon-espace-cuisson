<?php
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

// function resizeImage($srcFile, $_GET['dstWidth'] = 120, $_GET['dstHeight'] = 90, $dstFile = null, $jpegQuality = 80)

ini_set("display_errors",1);

include("class/mysql.php");
include("configurations/www.ma-cave-a-vin.fr/config_mysql.php");
include("class/marque_client.php");

$DB_site = new DB_Sql;
$DB_site->appname      = 'boutique';
$DB_site->appshortname = 'boutique';
$DB_site->database     = $DBname;
$DB_site->server       = $db_server;
$DB_site->user         = $DBusername;
$DB_site->password     = $DBpassword;
$DB_site->logpath      = SITE_PATH."/log";
$DB_site->connect();


$mc=new marque_client($DB_site,$_GET['mcid']);

$jpegQuality=80;
if(!is_dir("images/cache/clients")) mkdir("images/cache/clients");
$dstFile="images/cache/clients/".$mc->id."_".$_GET['dstWidth']."_".$_GET['dstHeight'].".jpg";
$cachedelai=86400*30;
$cache=true;
$time=false;

$srcFile=$mc->image;
if(!file_exists($srcFile)) $srcFile="images/templates/ma-cave-a-vin.jpg";

$lastModified=filemtime($srcFile);
$etagFile = md5($lastModified);

$etagHeader=(isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : false);
if($etagHeader == $etagFile){
    header("HTTP/1.1 304 Not Modified");
    exit;
}

if(file_exists($dstFile) && $cache){
   if($lastModified<filemtime($dstFile)){
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
  if ($srcWidth>$_GET['dstWidth']) {
    $width = $_GET['dstWidth'];
    $xOffset = 0;
  }else{
    $width=$srcWidth;
    $xOffset=round(($_GET['dstWidth']-$width)/2);
  }
  $height = round(($width / $srcWidth) * $srcHeight);
  $yOffset = round(($_GET['dstHeight'] - $height) / 2);

  if($height>$_GET['dstHeight']){
      $height=$_GET['dstHeight'];
      $width=round(($height/$srcHeight)*$srcWidth);
      $xOffset=round(($_GET['dstWidth']-$width)/2);
      $yOffset = round(($_GET['dstHeight'] - $height) / 2);
  }
  
$dstHandle = imagecreatetruecolor($_GET['dstWidth'], $_GET['dstHeight']);
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