<?php
require "admin_global.php";

header("Content-type: image/jpeg");

$out = array('4365', '4366');

/*****************************************************************/
/* PORTRAIT                                                      */
/*****************************************************************/

if($_GET['format'] == 'portrait'){
	//Création d'une image en couleur (l*h)
	$image = imagecreatetruecolor(900,1200); 
	// Définition des couleurs
	$white = imagecolorallocate($image, 255, 255, 255);

	// Coloris le background selon une couleur
	imagefill($image, 0, 0, $white);

	// img src
	$autreimg=$DB_site2->query_first("SELECT * FROM articlephoto WHERE artid=".$_GET['offre']." AND visible=1 AND (langue='' OR langue='".$_GET['langue']."')");
	$autre="";
	if($autreimg!=""){
		$autre="_".$autreimg['articlephotoid'];
		$file_kdo = $rootpath."configurations/".$host."/images/produits/".$_GET['offre'].$autre.".jpg";
	}else{
		$file_kdo = $rootpath."configurations/".$host."/images/produits/".$_GET['offre'].".jpg";
	}
		
	$file_article = $rootpath."configurations/".$host."/images/produits/".$_GET['artid'].".jpg";
	$visu_offre = $rootpath."images/templates/".$_GET['langue']."/visu_offre_v.jpg";
	$visu_sep = $rootpath."images/templates/visu_offre_com.jpg";

	// Taille des images
	list($l_art, $h_art) = getimagesize($file_article);
	list($l_kdo, $h_kdo) = getimagesize($file_kdo);
	list($l_off, $h_off) = getimagesize($visu_offre);
	list($l_sep, $h_sep) = getimagesize($visu_sep);

	// Zoom
	$width = 900;
	$height = 900;
	$ratio_art = $l_art/$h_art;

	$width2 = 900;
	$height2 = 200;
	$ratio_kdo = $l_kdo/$h_kdo;

	$width3 = 900;
	$height3 = 40;
	$ratio_sep = $l_sep/$h_sep;

	if ($width/$height > $ratio_art) {
	   $width = $height*$ratio_art;
	} else {
	   $height = $width/$ratio_art;
	}

	if ($width2/$height2 > $ratio_kdo) {
	   $width2 = $height2*$ratio_kdo;
	} else {
	   $height2 = $width2/$ratio_kdo;
	}

	if ($width3/$height3 > $ratio_sep) {
		$width3 = $height3*$ratio_sep;
	} else {
		$height3 = $width3/$ratio_sep;
	}

	// Créer les images à partir du fichier
	$img_art = imagecreatefromjpeg($file_article);
	$img_kdo = imagecreatefromjpeg($file_kdo);
	$img_offre = imagecreatefromjpeg($visu_offre);
	$img_sep = imagecreatefromjpeg($visu_sep);

	$l = imagesx($img_kdo);

	// Placement
	$right_art = (900 - $width)/2;
	$right_kdo = (900 - $width2)/2;
	$right_sep = (900 - $width3)/2;
	$right_off = ($l - $l_kdo) + $right_kdo - 55;

	$test1 = $l_off*0.47;
	$test2 = $h_off*0.47;

	$top_sep = 900 + 25;
	$top_kdo = $top_sep + $height3 + 15;
	$top_offre = $top_sep + $height3 + 25;

	// Redimensionnement des images
	imagecopyresampled($image, $img_art, $right_art, 0, 0, 0, $width, $height, $l_art, $h_art);
	imagecopyresampled($image, $img_kdo, $right_kdo, $top_kdo, 0, 0, $width2, $height2, $l_kdo, $h_kdo);
	if(!in_array($_GET['offre'],$out)) imagecopyresampled($image, $img_offre, $right_off, $top_offre, 0, 0, $test1, $test2, $l_off, $h_off);
	imagecopyresampled($image, $img_sep, $right_sep, $top_sep, 0, 0, $width3, $height3, $l_sep, $h_sep);



	// Sauvegarde de l'image
	if(isset($_GET['photoid']) && $_GET['photoid'] != ''){
		$image_path = $rootpath."configurations/".$host."/images/produits/".$_GET['packid']."_".$_GET['photoid'].".jpg";
		imagejpeg($image, $image_path);
		header("Location: ajout_offre.php?artid=".$_GET['packid']."&action=image");
	}else{
		// Création de l'image finale
		imagejpeg($image);
	}

	// Libére la mémoire
	imagedestroy($image);
}


/*****************************************************************/
/* PAYSAGE                                                       */
/*****************************************************************/

if($_GET['format'] == 'paysage'){
	//Création d'une image en couleur (l*h)
	$image = imagecreatetruecolor(900,1200); 

	// Img src
	$autreimg=$DB_site2->query_first("SELECT * FROM articlephoto WHERE artid=".$_GET['offre']." AND visible=1 AND (langue='' OR langue='".$_GET['langue']."')");
	$autre="";
	if($autreimg!=""){
		$autre="_".$autreimg['articlephotoid'];
		$file_kdo = $rootpath."configurations/".$host."/images/produits/".$_GET['offre'].$autre.".jpg";
	}else
		$file_kdo = $rootpath."configurations/".$host."/images/produits/".$_GET['offre'].".jpg";

	$file_article = $rootpath."configurations/".$host."/images/produits/".$_GET['artid'].".jpg";
	$visu_offre = $rootpath."images/templates/".$_GET['langue']."/visu_offre_h.jpg";
	$visu_sep = $rootpath."images/templates/visu_offre_com.jpg";

	// Taille des images
	list($l_art, $h_art) = getimagesize($file_article);
	list($l_kdo, $h_kdo) = getimagesize($file_kdo);
	list($l_off, $h_off) = getimagesize($visu_offre);
	list($l_sep, $h_sep) = getimagesize($visu_sep);

	// Zoom
	$width = 600;
	$height = 1198;
	$ratio_art = $l_art/$h_art;

	$width2 = 300;
	$height2 = 400;
	$ratio_kdo = $l_kdo/$h_kdo;

	$width3 = 300;
	$height3 = 50;
	$ratio_sep = $l_sep/$h_sep;

	$width4 = 300;
	$height4 = 45;
	$ratio_off = $l_off/$h_off;

	if ($width/$height > $ratio_art) {
	   $width = $height*$ratio_art;
	} else {
	   $height = $width/$ratio_art;
	}

	if ($width2/$height2 > $ratio_kdo) {
	   $width2 = $height2*$ratio_kdo;
	} else {
	   $height2 = $width2/$ratio_kdo;
	}

	if ($width3/$height3 > $ratio_sep) {
		$width3 = $height3*$ratio_sep;
	} else {
		$height3 = $width3/$ratio_sep;
	}

	if ($width4/$height4 > $ratio_off) {
		$width4 = $height4*$ratio_off;
	} else {
		$height4 = $width4/$ratio_off;
	}

	// Créer les images à partir du fichier
	$img_art = imagecreatefromjpeg($file_article);
	$img_kdo = imagecreatefromjpeg($file_kdo);
	$img_offre = imagecreatefromjpeg($visu_offre);
	$img_sep = imagecreatefromjpeg($visu_sep);

	$l = imagesx($img_kdo);

	$test1 = $l_off*0.47;
	$test2 = $h_off*0.47;

	$top_art = (1200 - $height)/2;
	$top_kdo = (1200-$height2-$height3)/2;
	$top_sep = (1200+$height2+$height3)/2;
	$top_off = $top_sep + 45;
	$right_kdo = $width/2 + (900 - $width2)/2;
	$right_sep = $width/2 + (900 - $width3)/2;
	$right_off = $width/2 + (900 - $width4)/2 + 18;
	$right = (600 - $width)/3;

	// Redimensionnement des images
	imagecopyresampled($image, $img_art, $right, $top_art, 0, 0, $width, $height, $l_art, $h_art);
	imagecopyresampled($image, $img_kdo, $right_kdo, $top_kdo, 0, 0, $width2, $height2, $l_kdo, $h_kdo);
	imagecopyresampled($image, $img_offre, $right_off, $top_off, 0, 0, $test1, $test2, $l_off, $h_off);
	imagecopyresampled($image, $img_sep, $right_sep, $top_sep, 0, 0, $width3, $height3, $l_sep, $h_sep);

	// Définition des couleurs
	$white = imagecolorallocate($image, 255, 255, 255);

	// Coloris le background selon une couleur
	imagefill($image, 0, 0, $white);

	// Sauvegarde de l'image
	if($_GET['photoid'] != '' && $_GET['packid'] != ''){
		$image_path = $rootpath."configurations/".$host."/images/produits/".$_GET['packid']."_".$_GET['photoid'].".jpg";
		imagejpeg($image, $image_path);
		header("Location: ajout_offre.php?artid=".$_GET['packid']."&action=image");
	}else{
		// Création de l'image finale
		imagejpeg($image);
	}

	// Libére la mémoire
	imagedestroy($image);
}

