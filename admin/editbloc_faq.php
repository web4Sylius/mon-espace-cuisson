<?php
require "admin_global.php";
include_once "./header.php";
include_once "./menu.php";	
$bloc ="faqbloc";
$maxsize = 10485760;
$maxwidth= 430;
$maxheight=290;
$image='';
$contenu='';
$erreur='';
$extensions_valides = array( 'jpg','png' );
$filemtime='';
if(isset($_GET['version'])) {
	$version = $_GET['version'];
	$table_faq_bloc = "faq_bloc_".$version;
	if($_GET['version'] == 'pro') $bloc ="faqblocpro";
}
$path = "../configurations/".$host."/images/".$bloc."/";
/**************************SUPPRIMER UN BLOC****************************/
if(isset($_GET['suppr'])){
	$blocid = $_GET['blocid'];
	$faqid = $_GET['faqid'];
	$l = $_GET['lg'];

	echo "Etes-vous sûr(e) de vouloir supprimer ce bloc ? <br><br>";
	echo "<a href=\"page_faq.php?action=editer&suppr2=1&blocid=".$blocid."&faqid=".$faqid."&lg=".$l."&site_code=base&version=".$version."\" >Oui</a> | <a href=\"page_faq.php?action=editer&faqid=".$faqid."&site_code=base&version=".$version."\">Non</a><br><br><br><br>";
}
/***************** MODIF TYPE BLOC POST  *********************************************/
if(isset($_POST['modif_type_bloc'])){ //Modification du type de BLOC
	$blocid = $_POST['blocid'];
	$newType = $_POST['new_type'];
	$faqid = $_POST['faq'];
	$l = $_POST['lg'];
	$leBloc = $DB_site2->query_first("SELECT * FROM $table_faq_bloc WHERE faq_blocid=".$blocid);
	$oldType = $DB_site2->query_first("SELECT cbtid FROM faq_bloc_type as cbt INNER JOIN $table_faq_bloc as cb ON cb.typeid = cbt.cbtid WHERE cb.faq_blocid='".$blocid."'");
	$req = $DB_site2->query_first("SELECT bloc_image, bloc_contenu FROM faq_bloc_type WHERE cbtid=".$newType);
	if($req['bloc_image'] == 'non'){
		if(file_exists($path.$blocid.".".$leBloc['image'])){
			unlink($path.$blocid.".".$leBloc['image']);
		}
		$DB_site2->query("UPDATE $table_faq_bloc SET image = NULL, alt_img='', url_img='' WHERE faq_blocid='".$blocid."'");
	}else{
		if($oldType[0] == 6 && ($newType == 1 || $newType == 2)){
			$srcImg = $path.$blocid.'.jpg';
			$image_sizes = getimagesize($srcImg);
			$widthDest = $image_sizes[0];
			$heightDest = $image_sizes[1];
			if ($heightDest > $maxwidth) $widthDest = $maxwidth;
			if ($heightDest > $maxheight) $heightDest = $maxheight;
			$resize = resizeImage($srcImg,$widthDest,$heightDest,$srcImg);
		}
	}
	if($req['bloc_contenu'] == 'non'){
		$DB_site2->query("UPDATE $table_faq_bloc SET contenu=NULL WHERE faq_blocid='".$blocid."'");
	}

	$DB_site2->query("UPDATE $table_faq_bloc SET typeid ='".$newType."' WHERE faq_blocid='".$blocid."' ");
}
/***************** MODIF BLOC POST  *********************************************/
if(isset($_POST['modif_content'])){
	$blocid = $_POST['modif_content'];
	$l = mysqli_real_escape_string($DB_site2->link_id,$_POST['modif_lg']);
	$bloc_image = $DB_site2->query_first("SELECT image, typeid FROM $table_faq_bloc WHERE faq_blocid ='$blocid' AND langue='".$l."' ");
	$image = $bloc_image['image'];
	$typeid = $bloc_image['typeid'];
	if($typeid == 6){
		$maxwidth = $maxwidth * 2.3;
		$maxheight = $maxheight * 2.3;
	}
	$bloc_content='';
	if(isset($_FILES['modif_image']) && $_FILES['modif_image']['name'] != '' ){
		//on vérifie si c'est bien une photo
		$extension_upload = strtolower(pathinfo($_FILES['modif_image']['name'], PATHINFO_EXTENSION));
		if ( in_array($extension_upload,$extensions_valides) ){
			if ($_FILES['modif_image']['size'] < $maxsize){
				$image_sizes = getimagesize($_FILES['modif_image']['tmp_name']);
				$widthDest = $image_sizes[0];
				$heightDest = $image_sizes[1];
				if ($heightDest > $maxwidth) $widthDest = $maxwidth;
				if ($heightDest > $maxheight) $heightDest = $maxheight;
					$nom = $path.$blocid.".".$extension_upload;
					$upload = resizeImage($_FILES['modif_image']['tmp_name'],$widthDest,$heightDest,$nom);
					if($upload) $image = $extension_upload;	
			}else{
				echo "<script>alert('attention le fichier est trop lourd');</script>";
			}
		}else{
			if($_FILES['modif_image']['size'] != '0')
				echo "<script>alert('attention le type de fichier n\'est pas autorisé');</script>";
		} 
	}
	$bloc_content = mysqli_real_escape_string($DB_site2->link_id,$_POST['bloc_'.$blocid]);
	$alt_img = mysqli_real_escape_string($DB_site2->link_id,$_POST['alt_'.$blocid]);
	$url_img = mysqli_real_escape_string($DB_site2->link_id,$_POST['url_'.$blocid]);
	$DB_site2->query("UPDATE $table_faq_bloc SET contenu ='".$bloc_content."', image = '".$image."', alt_img='".$alt_img."', url_img='".$url_img."' WHERE faq_blocid=".$blocid." AND langue='".$l."' " );
}

/********************************* AJOUTER UN BLOC FORMULAIRE *************************************/
if(isset($_GET['create'])){
	$faqid = $_GET['faqid'];
	$l = $_GET['lg'];
	$typeid = $_GET['typeid'];
	$typeBloc = $DB_site2->query_first("SELECT * FROM faq_bloc_type WHERE cbtid='".$typeid."' ");
	$action = "page_faq.php?action=editer&faqid=$faqid&site_code=base&version=$version";

	if($typeBloc['bloc_contenu'] == 'oui' && $typeBloc['bloc_image'] == 'oui'){ // image + texte
		$contenu = "<p>Ajouter le contenu : </p>";
		$contenu .= "<textarea name=\"bloc_new\" ></textarea> ";
		$image = "<p>Ajouter une image : </p><br>";
		$image .="<input type=\"file\" name=\"ajout_image\" />";
		$image .="<br><br><p>Balise Alt de l'image :</p><textarea rows=\"4\" cols=\"50\" name=\"alt_img\"></textarea>";
		$image .="<br><p>Url de l'image :</p><input type=\"text\" name=\"url_img\" style=\"width:377px;\">";
		$script = "<script>CKEDITOR.replace('bloc_new');</script>";

		echo "<form id=\"ajout_contenu_bloc\" method=\"post\" action=\"$action\" enctype=\"multipart/form-data\">";
		echo $contenu;
		echo "<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"10485760\" />";
		echo $image;
		echo "<input type=\"hidden\" name=\"ajout_content\" value=\"$faqid\">";
		echo "<input type=\"hidden\" name=\"ajout_type\" value=\"$typeid\">";
		echo "<input type=\"hidden\" name=\"ajout_lg\" value=\"".$l."\">";
		echo "<br><br><br><input type=\"submit\" value=\"Enregistrer\">";
		echo "</form>";
		echo $script;
	}
	elseif ($typeBloc['bloc_contenu'] == 'oui' && $typeBloc['bloc_image'] == 'non') { // texte seul
		$contenu = "<p>Ajouter le contenu : </p>";
		$contenu .= "<textarea name=\"bloc_new\" ></textarea> ";
		$script = "<script>CKEDITOR.replace('bloc_new');</script>";
		echo "<form id=\"ajout_contenu_bloc\" method=\"post\" action=\"$action\" enctype=\"multipart/form-data\">";
		echo $contenu;
		echo "<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"10485760\" />";
		echo "<input type=\"hidden\" name=\"ajout_content\" value=\"$faqid\">";
		echo "<input type=\"hidden\" name=\"ajout_type\" value=\"$typeid\">";
		echo "<input type=\"hidden\" name=\"ajout_lg\" value=\"".$l."\">";
		echo "<br><br><br><input type=\"submit\" value=\"Enregistrer\">";
		echo "</form>";
		echo $script;
	}
	elseif ($typeBloc['bloc_contenu'] == 'non' && $typeBloc['bloc_image'] == 'oui') { // image seule
		$image = "<p>Ajouter une image : </p><br>";
		$image .="<input type=\"file\" name=\"ajout_image\" />";
		$image .="<br><br><p>Balise Alt de l'image :</p><textarea rows=\"4\" cols=\"50\" name=\"alt_img\"></textarea>";
		$image .="<br><p>Url de l'image :</p><input type=\"text\" name=\"url_img\" style=\"width:377px;\">";
		echo "<form id=\"ajout_contenu_bloc\" method=\"post\" action=\"$action\" enctype=\"multipart/form-data\">";
		echo $image;
		echo "<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"10485760\" />";
		echo "<input type=\"hidden\" name=\"ajout_content\" value=\"$faqid\">";
		echo "<input type=\"hidden\" name=\"ajout_type\" value=\"$typeid\">";
		echo "<input type=\"hidden\" name=\"ajout_lg\" value=\"".$l."\">";
		echo "<br><br><br><input type=\"submit\" value=\"Enregistrer\">";
		echo "</form>";
		echo $script;
	}
}



/***************** MODIFIER UN BLOC FORMULAIRE *******************************************************/
if(isset($_GET['modif']) ){
	$blocid = $_GET['blocid'];
	$l = $_GET['lg'];
	$faqid = $_GET['faqid'];
	$bloc = $DB_site2->query_first("SELECT typeid, image, contenu, ordre, alt_img, url_img FROM ".$table_faq_bloc." WHERE faq_blocid ='$blocid' AND langue='".$l."' ");
	$typeBloc = $DB_site2->query_first("SELECT * FROM faq_bloc_type WHERE cbtid=".$bloc['typeid']);
	$action = "editbloc_faq.php?modif=1&blocid=$blocid&faqid=$faqid&lg=$l&version=$version";
	//modifier le type de bloc
	$typeBlocSelect = $DB_site2->query("SELECT cbtid,bloc_libelle as title FROM faq_bloc_type");
	$modifBloc = "<form method=\"post\" name=\"change_type\" action=\"$action\">
					<input type=\"hidden\" name=\"modif_type_bloc\" value=\"1\">
					<input type=\"hidden\" name=\"faq\" value=\"$faqid\">
					<input type=\"hidden\" name=\"lg\" value=\"".$l."\">
					<input type=\"hidden\" name=\"blocid\" value=\"".$blocid."\">
					<select name=\"new_type\" onchange=\"document.forms.change_type.submit();\">
						<option value=\"0\">Choisissez un nouveau type de Bloc </option>";
						while($desBloc = $DB_site2->fetch_array($typeBlocSelect)){
							$selected ='';
							if($desBloc['cbtid'] == $bloc['typeid'])
								$selected = 'selected';
							$modifBloc.="<option value=\"".$desBloc['cbtid']."\" ".$selected.">".$desBloc['title']."</option>";
						}

	$modifBloc.="</select>
				</form>";

	if($typeBloc['bloc_contenu'] == 'oui' && $typeBloc['bloc_image'] == 'oui'){  // image + texte
		if(file_exists($path.$blocid.".".$bloc['image']))
			$filemtime = "?".filemtime($path.$blocid.".".$bloc['image']);
		$contenu = "<p>Modifier le contenu : </p>";
		$contenu .= "<textarea name=\"bloc_".$blocid."\" >".$bloc['contenu']."</textarea> ";
		$image ="<div class=\"row\">";
		$image .= "<p class=\"col-xs-12\">Modifier l'image : </p>";
		$image .="<div class=\"col-xs-6\"><img src=\"".$path.$blocid.".".$bloc['image'].$filemtime."\"><br><br>";
		$image .="<input type=\"file\" name=\"modif_image\" />";
		$image .="</div><div class=\"col-xs-6\">";
		$image .="<p>Balise Alt de l'image :</p><textarea rows=\"4\" cols=\"50\" name=\"alt_".$blocid."\">".$bloc['alt_img']."</textarea>";
		$image .="<br><p>Url de l'image :</p><input type=\"text\" name=\"url_".$blocid."\" value=\"".$bloc['url_img']."\" style=\"width:377px;\">";
		$image .="</div></div>";
		$script = "<script>CKEDITOR.replace('bloc_".$blocid."');</script>";

		echo "<form id=\"modif_contenu_bloc\" method=\"post\" action=\"$action\" enctype=\"multipart/form-data\">";
		echo $contenu;
		echo "<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"10485760\" />";
		echo $image;
		echo "<input type=\"hidden\" name=\"modif_content\" value=\"".$blocid."\">";
		echo "<input type=\"hidden\" name=\"modif_faq\" value=\"$faqid\">";
		echo "<input type=\"hidden\" name=\"modif_lg\" value=\"".$l."\">";
		echo "<br><br><br><input type=\"submit\" value=\"Enregistrer\">";
		echo "</form>";
		echo $script;
	}
	elseif ($typeBloc['bloc_contenu'] == 'oui' && $typeBloc['bloc_image'] == 'non') { // texte seul
		$contenu = "<p>Modifier le contenu : </p>";
		$contenu .= "<textarea name=\"bloc_".$blocid."\" >".$bloc['contenu']."</textarea> ";
		$script = "<script>CKEDITOR.replace('bloc_".$blocid."');</script>";
		echo "<form id=\"modif_contenu_bloc\" method=\"post\" action=\"$action\" enctype=\"multipart/form-data\">";
		echo $contenu;
		echo "<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"10485760\" />";
		echo "<input type=\"hidden\" name=\"modif_content\" value=\"".$blocid."\">";
		echo "<input type=\"hidden\" name=\"modif_faq\" value=\"$faqid\">";
		echo "<input type=\"hidden\" name=\"modif_lg\" value=\"".$l."\">";
		echo "<br><br><br><input type=\"submit\" value=\"Enregistrer\">";
		echo "</form>";
		echo $script;
	}
	elseif ($typeBloc['bloc_contenu'] == 'non' && $typeBloc['bloc_image'] == 'oui') { // image seule
		if(file_exists($path.$blocid.".".$bloc['image']))
			$filemtime = "?".filemtime($path.$blocid.".".$bloc['image']);
		$image ="<div class=\"row\">";
		$image .= "<p class=\"col-xs-12\">Modifier l'image : </p>";
		$image .= "<div class=\"col-xs-12\"><img src=\"".$path.$blocid.".".$bloc['image'].$filemtime."\"><br><br>";
		$image .= "<input type=\"file\" name=\"modif_image\" />";
		$image .="</div><div class=\"col-xs-6\" style=\"margin-top:40px;\">";
		$image .="<p>Balise Alt de l'image :</p><textarea rows=\"4\" cols=\"50\" name=\"alt_".$blocid."\">".$bloc['alt_img']."</textarea>";
		$image .="<br><p>Url de l'image :</p><input type=\"text\" name=\"url_".$blocid."\" value=\"".$bloc['url_img']."\" style=\"width:377px;\">";
		$image .="</div></div>";
		echo "<form id=\"modif_contenu_bloc\" method=\"post\" action=\"$action\" enctype=\"multipart/form-data\">";
		echo $image;
		echo "<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"10485760\" />";
		echo "<input type=\"hidden\" name=\"modif_content\" value=\"".$blocid."\">";
		echo "<input type=\"hidden\" name=\"modif_faq\" value=\"$faqid\">";
		echo "<input type=\"hidden\" name=\"modif_lg\" value=\"".$l."\">";
		echo "<br><br><br><input type=\"submit\" value=\"Enregistrer\">";
		echo "</form>";
		echo $script;
	}

	echo "<br>";
	echo $modifBloc;
	echo "<br>";

}

echo "<a href=\"/admin/page_faq.php?action=editer&faqid=".$_GET['faqid']."&site_code=base&version=".$version."\">Retourner sur la page</a><br><br><br>";

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
