<?php
$phpFileUploadErrors = array(
    0 => 'There is no error, the file uploaded with success',
    1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
    2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
    3 => 'The uploaded file was only partially uploaded',
    4 => 'No file was uploaded',
    6 => 'Missing a temporary folder',
    7 => 'Failed to write file to disk.',
    8 => 'A PHP extension stopped the file upload.',
);
$dest = $rootpath."configurations/$host/article_description/";
$extensions_valides = array( 'png','jpg','pdf' );

//// tache 7033
//// enregistre ou modifie les images choisies pour les devis de l'article
if(isset($_POST['images_devis'])) {
	foreach ($_POST as $key => $value) {
		if(strpos($key, "imgext")!== false){
			$imgext = explode("_", $key);
			$imgactive=0;
			if(!is_null($_POST['img_'.$imgext[1]."_".$imgext[2]])){
				$imgactive = $_POST['img_'.$imgext[1]."_".$imgext[2]];
			}
			$req = $DB_site2->query_first("SELECT * FROM article_devisdesc WHERE img_ext='".$value."' AND langue='".$imgext[1]."'");
			if(empty($req)){
				$DB_site2->query("INSERT INTO article_devisdesc SET img_ext='".$value."',artid=$artid,active='".$imgactive."',langue='".$imgext[1]."'");
				$image = imagecreatefromjpeg($rootpath."configurations/$host/images/produits/".$value);
				$stamp = imagecreatefrompng($rootpath."configurations/$host/images/templates/base/FR/logo_transparent.png");
				$marge_right = 10;
				$marge_bottom = imagesy($image)/2-20;
				$stampx_avant = imagesx($stamp);
				if(imagesx($stamp) > imagesx($image)) {
					$stamp=imagescale($stamp, imagesx($image), imagesy($stamp));
					$stampx_apres = imagesx($stamp);
					$diff = $stampx_avant / $stampx_apres;
					$stampy_apres = imagesy($stamp) / $diff;
					$stamp=imagescale( $stamp, imagesx($stamp), $stampy_apres);
				}
				$sx = imagesx($stamp);
				$sy = imagesy($stamp);
				imagecopy($image, $stamp, imagesx($image) - $sx - $marge_right, imagesy($image) - $sy - $marge_bottom, 0, 0, imagesx($stamp), imagesy($stamp));
				//// redimensionne les images
				$width = imagesx($image);
				$height = imagesy($image);
				$img = $image;
				if($width > 1000){
					$newwidth = 1000;
					$diff = $width / $newwidth;
					$newheight = $height / $diff;
					$img = imagescale($image, $newwidth, $newheight);
				}
				elseif($height > 1000){
					$newheight = 1000;
					$diff = $height / $newheight;
					$newwidth = $width / $diff;
					$img = imagescale($image, $newwidth, $newheight);
				}
				imagejpeg($img,$dest.$imgext[1]."/".$value);
			}
			else{
				$DB_site2->query("UPDATE article_devisdesc SET active='".$imgactive."' WHERE img_ext='".$value."' AND langue='".$imgext[1]."'");
			}
		}
	}
}

if(isset($_POST['action2']) && empty($_POST['images_devis'])){
	foreach($_FILES as $file_l => $file){
		//var_dump($file,$file_l);
		$langue = explode("_", $file_l);
		$langue = $langue[1];
		if($file['error'] == 0){
			$extension_upload = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
			if ( in_array($extension_upload,$extensions_valides) ){
				$DB_site2->query("INSERT INTO article_devisdesc SET artid=$artid, size='".$file['size']."',ext='".$extension_upload."',langue='".$langue."'");
				$id = $DB_site2->insert_id();
				$dest = $dest.$langue."/".$id.".".$extension_upload;
				move_uploaded_file($file['tmp_name'], $dest);
				if($extension_upload !== 'pdf'){
					if($extension_upload == 'png'){
						$image = imagecreatefrompng($dest);
					}
					else{
						$image = imagecreatefromjpeg($dest);	
					}
					$width = imagesx($image);
					$height = imagesy($image);
					$img = $image;
					//// tache 7033
					//// redimensionne les images
					if($width > 1000){
						$newwidth = 1000;
						$diff = $width / $newwidth;
						$newheight = $height / $diff;
						$img = imagescale($image, $newwidth, $newheight);
					}
					elseif($height > 1000){
						$newheight = 1000;
						$diff = $height / $newheight;
						$newwidth = $width / $diff;
						$img = imagescale($image, $newwidth, $newheight);
					}
					if($extension_upload == 'png'){
						imagepng($img,$dest);
					}
					else{
						imagejpeg($img,$dest);
					}
				}
			}
		}else{
			echo $phpFileUploadErrors[$file['error']];
		}
	}
	foreach ($_POST as $key => $value) {
		if(strpos($key, "listpj")!== false){
			$pj = explode("_", $key);
			$pjid = $pj[2];
			$DB_site2->query("UPDATE article_devisdesc SET active='".$value."' WHERE id=".$pjid);
		}
	}
}
//liste pj
$list_html=array();
$req = $DB_site2->query("SELECT * FROM article_devisdesc WHERE artid=$artid");
while($list = $DB_site2->fetch_array($req)){
	switch ($list['langue']) {
		case 'FR':
			if(file_exists($rootpath."configurations/$host/article_description/".$list['langue']."/".$list['id'].".".$list['ext'])){
				$list_html['FR'].="<p><img src=\"/configurations/$host/article_description/".$list['langue']."/".$list['id'].".".$list['ext']."\" style=\"max-width:100px;\" /><a href=\"/configurations/$host/article_description/".$list['langue']."/".$list['id'].".".$list['ext']."\" target=\"_blank\">".$list['id'].".".$list['ext']."</a> &nbsp;&nbsp;&nbsp;<input type=\"radio\" name=\"listpj_fr_".$list['id']."\" value=\"1\" onclick=\"validForm();\" ".($list['active']=='1'?"checked":"")."> Oui / <input type=\"radio\" name=\"listpj_fr_".$list['id']."\" value=\"0\" onclick=\"validForm();\" ".($list['active']=='0'?"checked":"")."> Non</p>";
			}
			break;
		
		case 'EN':
			if(file_exists($rootpath."configurations/$host/article_description/".$list['langue']."/".$list['id'].".".$list['ext'])){
				$list_html['EN'].="<p><img src=\"/configurations/$host/article_description/".$list['langue']."/".$list['id'].".".$list['ext']."\" style=\"max-width:100px;\" /><a href=\"/configurations/$host/article_description/".$list['langue']."/".$list['id'].".".$list['ext']."\" target=\"_blank\">".$list['id'].".".$list['ext']."</a>&nbsp;&nbsp;&nbsp;<input type=\"radio\" name=\"listpj_en_".$list['id']."\" value=\"1\" onclick=\"validForm();\" ".($list['active']=='1'?"checked":"")."> Oui / <input type=\"radio\" name=\"listpj_en_".$list['id']."\" value=\"0\" onclick=\"validForm();\" ".($list['active']=='0'?"checked":"")."> Non</p>";
			}
			break;
	}
}

$contenu_onglet .="<style type=\"text/css\">
.newpj:before{background-image: linear-gradient(90deg,#00458b,#83d0f2);content: '';display: block;height: 5px;margin-bottom: 12px;}
</style>
<script type=\"text/javascript\">"
		."function validForm(){"
			."document.formu.action2.value='devisdesc';"
			."document.formu.old.value='devisdesc';"
				."document.formu.submit() ;"
		."}"

		."function validForm2(){"
		."document.formu2.action2.value='devisdesc';"
			."document.formu2.old.value='devisdesc';"
				."document.formu2.submit() ;"
		."}
</script>";
$contenu_onglet .='<h3>Devis - description</h3>';
$contenu_onglet .='<p style="font-size:12px;font-weight:600;">Vous pouvez ajouter des pièces jointes qui seront envoyées avec le devis de l\'article. Il faut les cocher en OUI pour qu\'elles soient inclus dans le mail. <br>ATTENTION au poids de la PJ, au dela de 12Mo (toutes pj, devis compris) le mail risque de ne pas passer</p>';
$contenu_onglet .="<form name=\"formu\" action=\"ajout_produit.php\" method=\"post\" enctype=\"multipart/form-data\">
						<input type=\"hidden\" name=\"old\" value=\"\">
						<input type=\"hidden\" name=\"quitter\" value=\"\">
						<input type=\"hidden\" name=\"mq2\" value=\"\">
						<input type=\"hidden\" name=\"modif\" value=\"0\">
						<input type=\"hidden\" name=\"action2\" value=\"\">
						<input type=\"hidden\" name=\"artid\" value=\"$artid\">
						<input type=\"hidden\" name=\"catid\" value=\"$catid\">";
$contenu_onglet .="<div class=\"col-xs-12\">";
$contenu_onglet .="<div class=\"col-xs-6\">";
$contenu_onglet .="<p>FR</p>";



$contenu_onglet .=$list_html['FR'];

$contenu_onglet .="</div>";

$contenu_onglet .="<div class=\"col-xs-6\">";
$contenu_onglet .="<p>EN</p>";
$contenu_onglet .=$list_html['EN'];
$contenu_onglet .="</div>";

$contenu_onglet .="<div class=\"col-xs-12\">";
$contenu_onglet .="<div class=\"col-xs-6 newpj\">";
$contenu_onglet .="<input type=\"hidden\" value=\"addpj_devisdesc\" >
<input type=\"file\" name=\"addpj_FR\" >";
$contenu_onglet .="</div>";
$contenu_onglet .="<div class=\"col-xs-6 newpj\">";
$contenu_onglet .="<input type=\"hidden\" value=\"addpj_devisdesc\" >
<input type=\"file\" name=\"addpj_EN\" >";
$contenu_onglet .="</div>";
$contenu_onglet.="</div>";

$contenu_onglet .="</div>";

$contenu_onglet.="</form>";

//// tache 7033
//// affiche les images de l'onglet "images" 
$contenu_onglet .="<form name=\"formu2\" action=\"ajout_produit.php\" method=\"post\" enctype=\"multipart/form-data\">
<div class=\"col-xs-6\">";
$article=$DB_site2->query_first("select * from article where artid = '$artid'") ;
$req = $DB_site2->query_first("SELECT * FROM article_devisdesc WHERE img_ext='$artid.$article[image]' AND active='1' AND langue='FR'");
$nb=0;
$oui="";
$non="checked";
if(isset($req)){
	$oui="checked";
	$non="";
}
$contenu_onglet .="<div style=\"width:50%;display:inline-block;\"><img src=\"/configurations/$host/images/produits/$artid.$article[image]\" name=\"base\" height=\"80\">
<input type=\"hidden\" name=\"imgext_FR_".$nb."\" value=\"$artid.$article[image]\">
<input type=\"radio\" name=\"img_FR_".$nb."\" value=\"1\" ".$oui."> Oui / <input type=\"radio\" name=\"img_FR_".$nb."\" value=\"0\" ".$non."> Non</p></div>";
$articleimages=$DB_site2->query("select * from articlephoto where artid = '$artid' ORDER BY position") ;
$nbimages=$DB_site2->num_rows($articleimages);
while ($articleimage=$DB_site2->fetch_array($articleimages)){
	$nb++;
	$req = $DB_site2->query_first("SELECT * FROM article_devisdesc WHERE img_ext='$articleimage[artid]_$articleimage[articlephotoid].$articleimage[image]' AND active='1' AND langue='FR'");
	$oui="";
	$non="checked";
	if(isset($req)){
		$oui="checked";
		$non="";
	}
	$contenu_onglet .="<div style=\"width:50%;display:inline-block;\"><img src=\"/configurations/$host/images/produits/$articleimage[artid]_$articleimage[articlephotoid].$articleimage[image]\" width=\"80\">
	<input type=\"hidden\" name=\"imgext_FR_".$nb."\" value=\"$articleimage[artid]_$articleimage[articlephotoid].$articleimage[image]\">
	<input type=\"radio\" name=\"img_FR_".$nb."\" value=\"1\" ".$oui."> Oui / <input type=\"radio\" name=\"img_FR_".$nb."\" value=\"0\" ".$non."> Non</p></div>";
}
$contenu_onglet .="</div><div class=\"col-xs-6\">";
$article=$DB_site2->query_first("select * from article where artid = '$artid'") ;
$req = $DB_site2->query_first("SELECT * FROM article_devisdesc WHERE img_ext='$artid.$article[image]' AND active='1' AND langue='EN'");
$nb=0;
$oui="";
$non="checked";
if(isset($req)){
	$oui="checked";
	$non="";
}
$contenu_onglet .="<div style=\"width:50%;display:inline-block;\"><img src=\"/configurations/$host/images/produits/$artid.$article[image]\" name=\"base\" height=\"80\">
<input type=\"hidden\" name=\"imgext_EN_".$nb."\" value=\"$artid.$article[image]\">
<input type=\"radio\" name=\"img_EN_".$nb."\" value=\"1\" ".$oui."> Oui / <input type=\"radio\" name=\"img_EN_".$nb."\" value=\"0\" ".$non."> Non</p></div>";
$articleimages=$DB_site2->query("select * from articlephoto where artid = '$artid' ORDER BY position") ;
$nbimages=$DB_site2->num_rows($articleimages);
while ($articleimage=$DB_site2->fetch_array($articleimages)){
	$nb++;
	$req = $DB_site2->query_first("SELECT * FROM article_devisdesc WHERE img_ext='$articleimage[artid]_$articleimage[articlephotoid].$articleimage[image]' AND active='1' AND langue='EN'");
	$oui="";
	$non="checked";
	if(isset($req)){
		$oui="checked";
		$non="";
	}

	$contenu_onglet .="<div style=\"width:50%;display:inline-block;\"><img src=\"/configurations/$host/images/produits/$articleimage[artid]_$articleimage[articlephotoid].$articleimage[image]\" width=\"80\">
	<input type=\"hidden\" name=\"imgext_EN_".$nb."\" value=\"$articleimage[artid]_$articleimage[articlephotoid].$articleimage[image]\">
	<input type=\"radio\" name=\"img_EN_".$nb."\" value=\"1\" ".$oui."> Oui / <input type=\"radio\" name=\"img_EN_".$nb."\" value=\"0\" ".$non."> Non</p></div>";
}
$contenu_onglet .="
</div><input type=\"hidden\" name=\"action2\" value=\"\">
<input type=\"hidden\" name=\"old\" value=\"\">
<input type=\"hidden\" name=\"artid\" value=\"$artid\">
<input type=\"hidden\" name=\"catid\" value=\"$catid\">
<input type=\"submit\" name=\"images_devis\" onclick=\"validForm2();\"value=\"Modifier\"></form>";

