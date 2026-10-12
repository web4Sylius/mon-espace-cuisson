<?php
include_once 'admin_global.php';
$extensions_valides = array( 'png' );
$maxwidth = 1200;
$maxheight = 900;
$pathsim = "../configurations/".$host."/images/simulateur/";
if(isset($_GET['simu'])){
	$isSimu = $_GET['simu'];
	$artid = $_GET['artid'];
	$id_r = NULL;
	if($isSimu == 0){
		$sim = 'oui';
	}else{
		$sim = 'non';
	}
	//On met à jour l'article, si il est oui ou non un article du simulateur
	$DB_site2->query("UPDATE article SET simu ='".$sim."' WHERE artid='".$artid."'");

}
if(isset($_GET['imgsimu'])){
    $req = $DB_site2->query_first("SELECT affiche FROM article_simu_caract WHERE ascid='".mysqli_real_escape_string($DB_site2->link_id,$_GET['ascid'])."'");
    if($req[0] == 0)
        $affiche = 1;
    else
        $affiche = 0;
    $DB_site2->query("UPDATE article_simu_caract SET affiche ='".$affiche."' WHERE ascid='".mysqli_real_escape_string($DB_site2->link_id,$_GET['ascid'])."'");
}
if(isset($_GET['del'])){
    unlink($pathsim.$_GET['artid']."_".$_GET['caractid'].(isset($_GET['caractvalid'])?"_".$_GET['caractvalid']:"").".png");
}
if(isset($_GET['ordre'])){
	$ordre = $_GET['ordre'];
	$artid = $_GET['artid'];
	$caractid = $_GET['caractid'];
	$position = $DB_site2->query_first("SELECT ordre FROM article_simu_caract WHERE artid='".$artid."' AND caractid='".$caractid."'");
	$position1 = $position[0];

	if($ordre == 'up'){
		$new_position = $position1 - 1;

	}elseif ($ordre == 'down') {
		$new_position = $position1 + 1;
	}

	$changed = $DB_site2->query_first("SELECT caractid FROM article_simu_caract WHERE artid='".$artid."' AND ordre='".$new_position."'");
	$DB_site2->query("UPDATE article_simu_caract SET ordre ='".$new_position."' WHERE artid='".$artid."' AND caractid='".$caractid."'");
	$DB_site2->query("UPDATE article_simu_caract SET ordre ='".$position1."' WHERE artid='".$artid."' AND caractid='".$changed[0]."'");
}
if(isset($_GET['changeType'])){
	$artid = $_GET['artid'];
	$caractid = $_GET['caractid'];
	$typeid = $_GET['changeType'];
	$DB_site2->query("UPDATE article_simu_caract SET type_caracteristique ='$typeid' WHERE artid='$artid' AND caractid='$caractid'");
}
if(isset($_GET['maj'])){ // mise a jour des caractéristiques (dans le cas où on a déselectionné des options)
	$artid = $_GET['artid'];
	$DB_site2->query("DELETE FROM article_simu_caractval WHERE artid='".$artid."'");
	$DB_site2->query("DELETE FROM article_simu_caract WHERE artid='".$artid."'");
}

//on recupère les photos 
$artid = $_POST['artid'];
$requete1 = $DB_site2->query("SELECT caractid FROM article_simu_caract WHERE artid='".$artid."'");
while($row = $DB_site2->fetch_array($requete1)){
	$caractid = $row['caractid'];
    //image de base de l'option
    if(isset($_FILES['img_caract_sim_'.$caractid]) && $_FILES['img_caract_sim_'.$caractid]['name'] != ""){
            $extension_upload = strtolower(pathinfo($_FILES['img_caract_sim_'.$caractid]['name'], PATHINFO_EXTENSION));
            if ( in_array($extension_upload,$extensions_valides) ){
                $image_sizes = getimagesize($_FILES['img_caract_sim_'.$caractid]['tmp_name']);
                $widthDest = $image_sizes[0];
                $heightDest = $image_sizes[1];
                if ($heightDest > $maxwidth) $widthDest = $maxwidth;
                if ($heightDest > $maxheight) $heightDest = $maxheight;
                $dest = $pathsim.$artid."_".$caractid.".".$extension_upload;
                $upload = move_uploaded_file($_FILES['img_caract_sim_'.$caractid]['tmp_name'],$dest);
            }else{
                echo "<script>alert('attention le fichier n\'est pas au format PNG');</script>";
            }
        }
    //image de chaque option
	$requete2 = $DB_site2->query("SELECT caractvalid FROM article_simu_caractval WHERE artid='".$artid."'");
	while($row2 = $DB_site2->fetch_array($requete2)){
		$caractvalid = $row2['caractvalid'];
		if(isset($_FILES['opt_'.$caractid.'_'.$caractvalid]) && $_FILES['opt_'.$caractid.'_'.$caractvalid]['name'] != ""){
			$extension_upload = strtolower(pathinfo($_FILES['opt_'.$caractid.'_'.$caractvalid]['name'], PATHINFO_EXTENSION));
			if ( in_array($extension_upload,$extensions_valides) ){
				$image_sizes = getimagesize($_FILES['opt_'.$caractid.'_'.$caractvalid]['tmp_name']);
				$widthDest = $image_sizes[0];
				$heightDest = $image_sizes[1];
				if ($heightDest > $maxwidth) $widthDest = $maxwidth;
				if ($heightDest > $maxheight) $heightDest = $maxheight;
				$dest = $pathsim.$artid."_".$caractid."_".$caractvalid.".".$extension_upload;
				$upload = move_uploaded_file($_FILES['opt_'.$caractid.'_'.$caractvalid]['tmp_name'],$dest);
			}else{
				echo "<script>alert('attention le fichier n\'est pas au format PNG');</script>";
			}
		}
	}
}
?>
<form name="formu" action="ajout_produit.php" method="post">
	<input type="hidden" name="old" value="<?php echo $_POST['old']; ?>">
	<input type="hidden" name="quitter" value="">
	<input type="hidden" name="mq2" value="">
	<input type="hidden" name="modif" value="0">
	<input type="hidden" name="action2" value="<?php echo $_POST['action2']; ?>">
	<input type="hidden" name="artid" value="<?php echo $_POST['artid']; ?>">
	<input type="hidden" name="catid" value="<?php echo $_POST['catid']; ?>">
</form>
<script type="text/javascript">
	document.formu.submit();
</script>

<?php 



function resizeImage($srcFile, $dstWidth = 120, $dstHeight = 90,$dstFile = null, $jpegQuality = 100){
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
    /*if($height > $dstHeight){ pour redimensionner les photos en portrait
    	$ratio = round($height/$dstHeight,2);
    	$height = round($height/$ratio);
    	$width = round($width/$ratio);
    }*/

    $dstHandle = imagecreatetruecolor($width, $height);

    // bool imagecopyresampled ( resource dst_image, resource src_image, int dst_x, int dst_y, int src_x, int src_y, int dst_w, int dst_h, int src_w, int src_h )
    if (!imagecopyresampled($dstHandle, $srcHandle, $xOffset, $yOffset, 0, 0, $width, $height, $srcWidth, $srcHeight)) {
        $msg2='Could not execute imagecopyresampled() function! ';
        return false;
    }
    

    switch ($type) {
    case 1 :
        imagegif($dstHandle, $dstFile);
        break;
    case 2 :
        imagejpeg($dstHandle, $dstFile, $jpegQuality);
        break;
    case 3 :
        imagepng($srcHandle, $dstFile);
        break;
    default :
        $msg2='File Type Not Supported! ';
        return false;
    }
    imagedestroy($srcHandle);
    imagedestroy($dstHandle);

    return true;
} 

?>