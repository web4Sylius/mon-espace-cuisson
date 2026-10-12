<?php

require "admin_global.php";
//on verifie que ces champs sont bien rempli sinon ça crée des bug de redirection
if($action=="modify"){
	foreach ($host_conf->langues as $l) {
		if(!isset($_POST['libelle'.$l]) || $_POST['libelle'.$l]=="" || !isset($_POST['url'.$l]) || $_POST['url'.$l]==""){
			header('Location: '.BACK_URL.'/modifier_categorie.php?catid='.$catid);
			exit;
		}
	}
	
}

include "./header.php";
include "./menu.php";
include "../class/fonctions.php";
// if(file_exists($rootpath."configurations/".$host."/class/cloudflare.class.php")){
include $rootpath."configurations/".$host."/class/cloudflare.class.php";
// }

echo "<h1>Modification d'un rayon</h1>" ;

function compter_produits($catid){
	global $DB_site2;
	$stringbre=0;
	$requete_nb_produits="select count(artid) from article where catid=$catid";
	$nb_produits=$DB_site2->query_first($requete_nb_produits);
	$stringbre=$nb_produits[0];
	$requete_ss_cat="select catid from categorie where parentid='$catid'";
	$sscats=$DB_site2->query($requete_ss_cat);
	$ssnombre=0;
	while ($sscat=$DB_site2->fetch_array($sscats)) {
		$ssnombre+=compter_produits($sscat[catid]);
	}
	$total=$stringbre+$ssnombre;
	return ($total);
}

function navigation($catidfunc , $isarticle=0){
	$separateur_navigation=" > ";
	global $DB_site2,$libelle,$navigationbit,$parentid;
	$requete_categorie="select catid , $libelle , parentid from categorie where catid='$catidfunc' ";
	$navigation_categorie=$DB_site2->query_first($requete_categorie);
	if ($parentid==$catidfunc and $isarticle==0) {
		$navigationbit=$navigation_categorie[$libelle];
	} else {
		$navigationbit="<a href=\"catalogue.php?catid=$navigation_categorie[catid]\">$navigation_categorie[$libelle]</a>" . $separateur_navigation . $navigationbit;
	}
	if ($navigation_categorie[parentid]!=0) {
		navigation($navigation_categorie[parentid]);
	}
}

if ($catid=="" || !isset($catid))
	$parentid="0";
else
	$parentid=$catid;

$i=0;

//*******************************************************************^
//****************MODIFICATION DE LA CATEGORIE***********************
//*******************************************************************
if ($action=="modify") {
	if($catid!="" and isset($catid)){
		if ($visible_treeview == 'on'){
			$visible_treeview = '1' ;
		}else{
			$visible_treeview = '0' ;
		}
		// mise à jour du treeview
		$DB_site2->query("update treeview_temporaire set dateline = dateline - 10000");
		$requete="select parentid from categorie where catid=$catid";
		$parentid=$DB_site2->query_first($requete);
		$parentid=$parentid[0];
		$sql="";

		foreach($langues as $l){
			
		    if(in_array($l,$langue_access)) {
				$sql.="libelle".$l."='".mysqli_real_escape_string($DB_site2->link_id,$_POST['libelle'.$l])."', description".$l."='".mysqli_real_escape_string($DB_site2->link_id,$_POST['description'.$l])."',description2".$l."='".mysqli_real_escape_string($DB_site2->link_id,$_POST['description2'.$l])."', 
				ref_title".$l."='".mysqli_real_escape_string($DB_site2->link_id,$_POST['ref_title'.$l])."', ref_description".$l." = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['ref_description'.$l])."', url".$l."='".$_POST['url'.$l]."',";
				$url=$DB_site2->query_first("SELECT url$l FROM categorie WHERE catid=$catid");
				if($url['url'.$l]!="" && $url['url'.$l]!=$_POST['url'.$l])
					$DB_site2->query("INSERT INTO categorie_url SET catid=$catid, langue='$l', `url`='".$url['url'.$l]."'");
		    }
		}
		$affichage_menu = 1;
		if($parentid == 0){
			if(!isset($_POST['affichage_menu'])){
				$affichage_menu = 0;
			}
		}

		// on supprime le cache du menu
		foreach ($host_conf->langues as $key => $value) {
			$path = "../configurations/$host/cache/".$value."/";
			$rep=opendir($path);
			while($file = readdir($rep)){
				if($file != '..' && $file !='.' && $file !='' && $file!='.htaccess'){
					unlink($path.$file);
				}
			}
		}
		
		if ($_POST['supprimage']!=""){
			$sqlsupprimage=", imageFR='', imageEN='', imageSP='', imageDE='', imageIT='' ";
		}
		
		$sqlfiltres="";
		if(isset($_POST['filtre'])) $sqlfiltres=implode("|",$_POST['filtre']);
		
		$requete="UPDATE categorie SET ".substr($sql,0,-1).",`orderby`='".$_POST['orderby']."', affichage_menu='".$affichage_menu."', filtres='".$sqlfiltres."' ".$sqlsupprimage." WHERE catid='$catid'";
		$DB_site2->query($requete);
		if(in_array("FR" , $langues) && $_FILES['imageFR']['name']!=""){
			if ($_FILES['imageFR']['type']=="image/pjpeg") $type_fichier="jpg";
			if ($_FILES['imageFR']['type']=="image/jpeg") $type_fichier="jpg";
			if ($_FILES['imageFR']['type']=="image/gif") $type_fichier="gif";
			if ($_FILES['imageFR']['type']=="image/png") $type_fichier="png";
			$nom_fic=$rootpath."configurations/$host/images/categories/new/".$catid.".".$type_fichier;
			$erreur=copier_image($nom_fic,"imageFR");
			if ($erreur=="1") {
				$update_caract=" UPDATE categorie SET imageFR='$type_fichier', imageEN='$type_fichier', imageSP='$type_fichier', imageDE='$type_fichier', imageIT='$type_fichier' WHERE catid='$catid' ";
				$DB_site2->query($update_caract);
			} else {
				echo $erreur;
				echo "<br>Veuillez utiliser le bouton précédent de votre navigateur pour modifier votre sélection.";
				include ("./footer.php");
				exit;
			}
			$urls = array(	"https://www.mon-espace-cuisson.com/configurations/www.mon-espace-cuisson.com/images/categories/new/".$catid.".jpg",
							"https://www.mon-espace-cuisson.com/categorie/".$catid."_230_134.jpg",
							"https://www.mon-espace-cuisson.com/categorie/".$catid."_320_192.jpg"
						);
			// echo nl2br($_POST['url']);
			$cloud = new cloudflare_api($DB_site2,$rootpath);
			//$cloud->zone_id=$zone_id;
			while (list($key, $val) = each($urls)) {
				
				$zoneid=$cloudflare_zone_id;
				
				$results = $cloud->purgeFiles($cloudflare_email, $cloudflare_api, $zoneid, array($val) );
				if($results->success === true) {
					echo "<br>URL : $val : <b>Execution r&eacute;alis&eacute;e avec succ&egrave;s.</b>";
				}
				if($results->success === false) {
					echo "<br>URL : $val : <b>ERREUR D'EXECUTION.</b>";
					var_dump($results);
				}
			}
		}
		
		echo "<br><br>mises à jour effectuées.<a href=\"modifier_categorie.php?catid=".$catid."\">retour</a>";
	}
} else {

	//*******************************************************************
	//****************AFFICHAGE BARRE NAVIGATION*************************
	//*******************************************************************
	if ($parentid=="0"){
		$navigationdebut="<table align=\"center\">
							<tr>
								<td class=\"td_modules_boutiques\">".${"title$langue"};
	}else{
		$navigationdebut="<table align==\"center\">
							<tr>
								<td class=\"td_modules_boutiques\"><a href=\"catalogue.php\">".${"title$langue"}."</a> > ";
	}
	navigation($parentid);
	echo ($navigationdebut.$navigationbit."</td></tr></table><br>");

	$categorie=$DB_site2->query_first("SELECT * FROM categorie WHERE catid='$catid'");

	$orderopt="";
	$defaultorders=array("ventes"=>"ventes","ASC"=>"prix croissants","DESC"=>"prix décroissants");
	foreach($defaultorders as $defaultorder=>$name)
		$orderopt.="<option value=\"".$defaultorder."\"".($categorie['orderby']==$defaultorder?" selected":"").">".$name."</option>";

	//*******************************************************************
	//*****************AFFICHAGE DE LA CATEGORIE*************************
	//*******************************************************************

	$catfiltres=explode("|",$categorie['filtres']);
	$filtres=array("categorie"=>"sous catégories","gamme"=>"niveau de gamme","marque"=>"marque","type_pose"=>"Type de pose","type_four"=>"Types de four","type_accessoire"=>"type d'accessoire","type_bbq"=>"Type de BBQ","type_plancha"=>"Type de plancha","type_plaque"=>"type de plaque","type_hotte"=>"type de hotte","type_aspiration"=>"type évacuation","nb_foyers"=>"Nombre de foyers","couleur"=>"coloris","nb_fours"=>"nombre de fours","origine"=>"origine de fabrication","volume_cavite"=>"Capacité (L)","capacite_lavage"=>"Capacité (kg)","capacitecouverts" => "Capacité couverts","prix"=>"prix","largeur"=>"largeur","hauteur"=>"hauteur","profondeur"=>"profondeur","serrure"=>"serrure");
	foreach($filtres as $filtre=>$txt)
        $chkfiltres.="<input type=\"checkbox\" name=\"filtre[]\" value=\"".$filtre."\"".(in_array($filtre,$catfiltres)?" checked":"")."> ".ucfirst($txt)."<br>";
	
	$chksites="";
	/*$ongletsites=explode(",",$categorie['onglet']);
	foreach($sites as $site_code=>$site){
		if($site_code == 'base')*/
			$chksites.="<input type=\"checkbox\" name=\"affichage_menu\" value=\"\" ".(($categorie['affichage_menu']==1)?" checked":"").">base<br><br>";
	//}
	echo "<script language=\"javascript\" src=\"../ckeditor/ckeditor.js\"></script>";
	echo "<form name=\"formu\" method=\"post\" action=\"modifier_categorie.php\" enctype=\"multipart/form-data\">
		<input type=\"hidden\" name=\"action\" value=\"modify\">
		<input type=\"hidden\" name=\"saverefresh\" value=\"0\">
		<input type=\"hidden\" name=\"catid\" value=\"$catid\">
		<table class=\"table_modules_boutiques\">";
		if($categorie['parentid']==0 || $categorie['menu_appartenance']>0){
			  echo "<tr>
				<td>Visible sur le menu :</td>
				<td>$chksites</td>
			  </tr>
			  <tr>
				<td>Tri par défaut :</td>
				<td><select name=\"orderby\">$orderopt</select></td>
			  </tr>
			  <tr>
                <td>Filtres de recherche</td>
                <td>$chkfiltres</td>
            </tr>";
		}
		echo " <tr>
				<td class=\"td_modif_categ1\">Image :<br>Dimensions : 320 x 192 px.</td>
				<td class=\"td_modif_categ2\">";
		if ($categorie['imageFR']!=""){
			echo "<img src=\"/configurations/$host/images/categories/new/".$catid.".".$categorie['imageFR']."\" width=\"100\">
				  &nbsp;&nbsp;<a href=\"javascript:supprimerimage('FR');\">Supprimer</a></td></tr>";
		}else{
			echo "<input type=\"file\" name=\"imageFR\"></td></tr>";
		}
	// if(in_array("FR" , $langues)){
	foreach($langues as $l) {
		$msgurl="";
		if(in_array($l,$langue_access)){
			$libelle="<textarea name=\"libelle$l\" cols=\"50\" rows=\"2\">".$categorie['libelle'.$l]."</textarea>"; 
			$description_part="<textarea name=\"description".$l."\" class=\"ckeditor\">".$categorie['description'.$l]."</textarea>";
			$description_pro="<textarea name=\"description2".$l."\" class=\"ckeditor\">".$categorie['description2'.$l]."</textarea>";
			$url="<input type=\"text\" name=\"url$l\" value=\"".$categorie['url'.$l]."\" size=\"40\">";
			$metatitle="<input type=\"text\" name=\"ref_title".$l."\" value=\"".$categorie['ref_title'.$l]."\" size=\"40\">";
			if($categorie['url'.$l]!=""){
			    $req=$DB_site2->query("SELECT catid,libelleFR FROM categorie WHERE url".$l."='".mysqli_real_escape_string($DB_site2->link_id,$categorie['url'.$l])."' AND catid!=".$catid);
			    if($DB_site2->num_rows($req)>0){
				$msgurl="<br><span style=\"color:red;\">Attention la même url est déjà utilisée :</span>";
				while($errcat=$DB_site2->fetch_array($req)){
				    $msgurl.="<br><a href=\"modifier_categorie.php?catid=".$errcat['catid']."\" target=\"_blank\">".$errcat['libelleFR']."</a>";
				}
			    }
			}
		}else {
			$libelle=$categorie['libelle'.$l];
			$description_part=$categorie['description'.$l];
			$description_pro =$categorie['description2'.$l];
			$url=$categorie['url'.$l];
			$metatitle=$categorie['ref_title'.$l];
		}
		
		echo "<tr>
				<td class=\"td_modif_categ1\" style=\"border-top:solid 1px black;\"><img src=\"images/drapeaux/$l.gif\" width=\"20\"> Libellé $l : </td>
				<td class=\"td_modif_categ2\" style=\"border-top:solid 1px black;\">$libelle</td>
			  </tr>";
		echo "<tr style=\"height:10px;\"></tr>";	  
		echo "<tr>
				<td class=\"td_modif_categ1\" style=\"text-align:left;\"><img src=\"images/drapeaux/$l.gif\" width=\"20\">Description Part $l (haut) : </td>
				<td class=\"td_modif_categ1\" style=\"text-align:left;\"><img src=\"images/drapeaux/$l.gif\" width=\"20\">Description Pro $l (haut) : </td>
			 </tr>
			 <tr>
				<td class=\"td_modif_categ2\">$description_part</td>
				<td class=\"td_modif_categ2\">$description_pro</td>
			  </tr>
			  <tr>";
		echo "<tr><td class=\"td_modif_categ1\">URL $l : $msgurl</td><td>$url</td></tr>";
		echo "<tr><td class=\"td_modif_categ1\">Balise title :<br><small>Nom catégorie si vide</small></td><td>$metatitle</td></tr>";
	} 
	echo "<table>";
	echo "<br><br><a href=\"ajouter_caracteristique_cat.php?catid=$catid\">Modifier les caractéristiques</a>" ;
	echo "<br><div align=\"center\"><input type=\"submit\" value=\"Suivant\"></div>
			<input type=\"hidden\" name=\"supprimage\" value=\"\"></form>
		<script language=\"javascript\">
		function supprimerimage(langue){
			document.formu.supprimage.value=langue;
			document.formu.submit();
		}
		</script>"; 
}
include "./footer.php";

//************************************************************************
//**************************** FONCTIONS PHP *****************************
//************************************************************************
function redimentionner_image ($nom_fichier, $destination, $largeur, $hauteur)
	{
	if (substr ($destination, -3) == "jpg") 
		{
		$imagetmp = imagecreatefromjpeg ($nom_fichier) ;
		}
	if (substr ($destination, -3) == "gif") 
		{
		$imagetmp = imagecreatefromgif ($nom_fichier) ;
		}
	if (substr ($destination, -3) == "png") 
		{
		$imagetmp = imagecreatefrompng ($nom_fichier) ;
		}
	$imagesize = getimagesize ($nom_fichier) ;
	if ($imagesize[0] > $largeur or $imagesize[1] > $hauteur)
		{
		$largeur_en_cours = $imagesize[0] ;
		$hauteur_en_cours = $imagesize[1] ;
		if ($largeur_en_cours > $largeur) // si largeur > 112
			{
			$width = $largeur ; // largeur = 112
			$height = floor ($largeur * $hauteur_en_cours / $largeur_en_cours) ; // hauteur = 112 * (768/1024)
			$largeur_en_cours = $width ;
			$hauteur_en_cours = $height ;
			} 
		if ($hauteur_en_cours > $hauteur) 
			{
			$width = floor($largeur_en_cours * $hauteur / $hauteur_en_cours) ;
			$height = $hauteur ;
			$largeur_en_cours = $width ;
			$hauteur_en_cours = $height ;
			}
		$image = imagecreatetruecolor ($largeur_en_cours, $hauteur_en_cours) ;
		imagecopyresampled ($image, $imagetmp, 0, 0, 0, 0, $largeur_en_cours, $hauteur_en_cours, $imagesize[0], $imagesize[1]) ;
		if (substr ($destination, -3) == "jpg") 
			{
			imagejpeg ($image, $destination , 80) ;
			}
		if (substr ($destination, -3) == "gif") 
			{
			imagegif ($image, $destination , 80) ;
			}
		if (substr ($destination, -3) == "png") {
			
			imagepng ($image, $destination , 80) ;
			}
		chmod ($destination, 0777) ;
		imagedestroy ($image) ;
		} 
	else 
		{
		$image = imagecreatetruecolor ($imagesize[0], $imagesize[1]) ;
		imagecopyresampled ($image, $imagetmp, 0, 0, 0, 0, $imagesize[0], $imagesize[1], $imagesize[0], $imagesize[1]) ;
		if (substr ($destination, -3) == "jpg") 
			{
			imagejpeg($image, $destination, 80) ;
			}
		if (substr ($destination, -3) == "gif") 
			{
			imagegif ($image, $destination, 80) ;
			}
		if (substr ($destination, -3) == "png") 
			{
			imagepng ($image, $destination, 80) ;
			}
		chmod ($destination, 0777) ;
		imagedestroy ($image) ;
		}
	}



function copier_image($nom_fic,$imagename){
	if($_FILES[$imagename]['error']==1) {
			return ("erreur !!! Le fichier téléchargé excède la taille autorisée par le serveur(>2Mb)");
	}
	elseif ($_FILES[$imagename]['error']==3) {
			return ("erreur !!! Le fichier n'a été que partiellement téléchargé.");
	}
	elseif ($_FILES[$imagename]['error']==2) {
			return ("erreur !!! ereur inconnue ...");
	}
	elseif ($_FILES[$imagename]['error']==4) {
			return ("erreur !!! Aucun fichier n'a été téléchargé.");
	}
	elseif ($_FILES[$imagename]['type']!="image/jpeg" and $_FILES[$imagename]['type']!="image/pjpeg" and $_FILES[$imagename]['type']!="image/gif" and $_FILES[$imagename]['type']!="image/png") 
		{
			return ("erreur !!! Le fichier téléchargé n'est pas du type autorisé:<br>Vous ne pouvez télécharger qu'un fichier du type JPEG , JPG , GIF ou PNG (type envoyé : ".$_FILES[$imagename]['type'].")");
		}

	if($OpenLocal= fopen($_FILES[$imagename]['tmp_name'], "rb")) {
			$LocalFileContent = fread($OpenLocal, $_FILES[$imagename]['size']);
	} else {
			fclose($OpenLocal);
			die("<b>Erreur : </b>impossible d'ouvrir le fichier local '".$_POST[$imagename].".");
	}
	fclose($OpenLocal);

	if($OpenRemote = fopen($nom_fic, "wb")) {
			if(fwrite($OpenRemote, $LocalFileContent)) {
					fclose($OpenRemote);
			}
	} else {
			fclose($OpenRemote);
			die("<b>Erreur : </b>impossible d'ouvrir le fichier distant '".$nom_fic.".");
	}
	chmod ("$nom_fic",0777);
	return (1);
}
?>
