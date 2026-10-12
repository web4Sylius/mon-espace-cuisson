<?php
include_once($_SERVER['DOCUMENT_ROOT']."/class/host.php");
$host_conf = new Host();
$host=$host_conf->host;

ini_set("max_input_time",300);
ini_set("max_upload_size","2M");

$photopath="/configurations/".$host."/images/presse/";
$photo_width=900;

$funcNum = $_GET['CKEditorFuncNum'] ;
$CKEditor = $_GET['CKEditor'] ;
$langCode = $_GET['langCode'] ;

$acceptfile=array("jpg","jpeg","pdf");
$msg2="";
if(isset($_FILES['upload'])){

    $extension = strtolower(pathinfo($_FILES['upload']['name'], PATHINFO_EXTENSION));
    if(!in_array($extension,$acceptfile)){
	$msg2="Type de fichier interdit";
    }else{
      $url="..".$photopath.sanitize($_FILES['upload']['name']);
      if($extension=="pdf")
	  $return=upload($_FILES['upload']['tmp_name'],$url);
      else
	  $return=resizeImage($_FILES['upload']['tmp_name'],$photo_width,300,$url);
     
      if($return)
	$msg2="Envoi fichier ".strip_tags($_FILES['upload']['name'])." terminé";
    }
}

echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($funcNum, '$url', '$msg2');</script>";


function sanitize($string, $force_lowercase = true, $anal = false) {
    $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]",
                   "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
                   "â¤?", "â¤?", ",", "<", ">", "/", "?");
    $clean = trim(str_replace($strip, "", strip_tags($string)));
    $clean = preg_replace('/\s+/', "-", $clean);
    $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean ;
    return ($force_lowercase) ?
        (function_exists('mb_strtolower')) ?
            mb_strtolower($clean, 'UTF-8') :
            strtolower($clean) :
        $clean;
}

function upload($srcFile,$dstFile){
    return move_uploaded_file($srcFile,$dstFile);
}

function resizeImage($srcFile, $dstWidth = 120, $dstHeight = 90, $dstFile = null, $jpegQuality = 100){
    global $msg2;
    if (!file_exists($srcFile)) {
        $msg2="$srcFile doesn't exist! ";
        return false;        
    }
    if (!$dstFile) {
        $dstFile = 'tn_' . basename($srcFile);
    }
    if (!is_writable($dstDir = dirname($dstFile))) {
        $msg2="$dstDir is not a writeable directory! ";
        return false;
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
        $msg2='File Type Not Supported! ';
        return false;
    }
    if (!$srcHandle) {
        $msg2='Could not execute imagecreatefrom() function! ';
        return false;
    }

    // Source is wider than the Destination
    $width = $dstWidth;
    $xOffset = 0;

    $height = round(($width / $srcWidth) * $srcHeight);
    $yOffset = 0;

    $dstHandle = imagecreatetruecolor($width, $height);

    // bool imagecopyresampled ( resource dst_image, resource src_image, int dst_x, int dst_y, int src_x, int src_y, int dst_w, int dst_h, int src_w, int src_h )
    if (!imagecopyresampled($dstHandle, $srcHandle, $xOffset, $yOffset, 0, 0, $width, $height, $srcWidth, $srcHeight)) {
        $msg2='Could not execute imagecopyresampled() function! ';
        return false;
    }
    imagedestroy($srcHandle);

    switch ($type) {
    case 1 :
        imagegif($dstHandle, $dstFile);
        break;
    case 2 :
        imagejpeg($dstHandle, $dstFile, $jpegQuality);
        break;
    case 3 :
        imagepng($dstHandle, $dstFile);
        break;
    default :
        $msg2='File Type Not Supported! ';
        return false;
    }

    imagedestroy($dstHandle);

    return true;
} 

?>