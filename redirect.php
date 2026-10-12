<?php
if(!isset($_GET['param'])) exit;
//code pour faire les redirections des images dans les bon répertoires
include_once("host_config.php");

$path = "configurations/".$host_conf->path."/images/";
$param = mysqli_real_escape_string($DB_site->link_id,$_GET['param']);
$param = str_replace(array("../","./"), "", $param);
$srcFile = $path.$param;
/*if(!file_exists($srcFile)){
	$srcFile = $path."templates/photo_non_dispo.jpg";
}*/

//TEST format image
switch (exif_imagetype($srcFile)) {
	case 2: //JPG
		$img = imagecreatefromjpeg($srcFile);
		header('Content-Type: image/jpeg');
		imagejpeg($img);
		break;
	case 3://PNG
		$img = imagecreatefrompng($srcFile);
		// integer representation of the color black (rgb: 0,0,0)
        $background = imagecolorallocate($img , 0, 0, 0);
        // removing the black from the placeholder
        imagecolortransparent($img, $background);
        // turning off alpha blending (to ensure alpha channel information
        // is preserved, rather than removed (blending with the rest of the
        // image in the form of black))
        imagealphablending($img, false);

        // turning on alpha channel information saving (to ensure the full range
        // of transparency is preserved)
        imagesavealpha($img, true);

		header('Content-Type: image/png');
		imagepng($img);

		break;
	case 1://GIF
		$img = imagecreatefromgif($srcFile);
		header('Content-Type: image/gif');
		imagegif($img);
		break;		
}

imagedestroy($img);