<?php
require_once "admin_global.php";
//on verifie que ces champs sont bien rempli sinon ça crée des bug de redirection
if($action=="10ajouter"){
	if(!isset($libelleFR) || $libelleFR==""){
		header('Location: '.BACK_URL.'/catalogue.php?catid='.$parentid);
			exit;
	}
}

include "./header.php";
include "./menu.php";

$bgcounter=1;
if($parentid==""){
	$parentid=0;
}
$i=0;

echo "<form name=\"formu2\" method=\"post\" action=\"ajouter_categorie.php\"><input type=\"hidden\" name=\"action\" value=\"20ajouter\">
	  <input type=\"hidden\" name=\"catid\" value=\"$catid\"></form>";

//*************************************************************************************
//***************INSERTION DE LA CATEGORIE DANS LA BASE DE DONNEES*********************
//*************************************************************************************
if ($action=="10ajouter"){
	
	if ($visible_treeview == 'on'){
		$visible_treeview = '1' ;
	}
	else{
		$visible_treeview = '0' ;
	}
	echo "<form method=\"post\" action=\"ajouter_categorie.php\" name=\"formu\"><input type=\"hidden\" name=action value=\"20ajouter\">";
	if ($parentid==""){
		$parentid=0;
	}
	$ref_titleFR=str_replace( "[boutiquetitre]" , "\${\$title}" , $ref_titleFR );
	$ref_titleFR=str_replace( "[categorietitre]",  "\$pagetitle_catname" , $ref_titleFR );
	$ref_titleFR=str_replace( "[sscatkeywords]", "\$metasscat"  , $ref_titleFR );
	$ref_titleFR=str_replace( "[keydescription]" , "\$keydescription" , $ref_titleFR );
	$ref_titleFR = addslashes($ref_titleFR) ;
	$ref_descriptionFR=str_replace( "[boutiquetitre]" , "\${\$title}" , $ref_descriptionFR );
	$ref_descriptionFR=str_replace( "[categorietitre]",  "\$pagetitle_catname" , $ref_descriptionFR );
	$ref_descriptionFR=str_replace( "[sscatkeywords]", "\$metasscat"  , $ref_descriptionFR );
	$ref_descriptionFR=str_replace( "[keydescription]" , "\$keydescription" , $ref_descriptionFR );
	$ref_descriptionFR = addslashes($ref_descriptionFR) ;
	$ref_keywordsFR=str_replace( "[boutiquetitre]" , "\${\$title}" , $ref_keywordsFR );
	$ref_keywordsFR=str_replace( "[categorietitre]",  "\$pagetitle_catname" , $ref_keywordsFR );
	$ref_keywordsFR=str_replace( "[sscatkeywords]", "\$metasscat"  , $ref_keywordsFR );
	$ref_keywordsFR=str_replace( "[keydescription]" , "\$keydescription" , $ref_keywordsFR );
	$ref_keywordsFR = addslashes($ref_keywordsFR) ;
	$ref_titleDE=str_replace( "[boutiquetitre]" , "\${\$title}" , $ref_titleDE );
	$ref_titleDE=str_replace( "[categorietitre]",  "\$pagetitle_catname" , $ref_titleDE );
	$ref_titleDE=str_replace( "[sscatkeywords]", "\$metasscat"  , $ref_titleDE );
	$ref_titleDE=str_replace( "[keydescription]" , "\$keydescription" , $ref_titleDE );
	$ref_titleDE = addslashes($ref_titleDE) ;
	$ref_descriptionDE=str_replace( "[boutiquetitre]" , "\${\$title}" , $ref_descriptionDE );
	$ref_descriptionDE=str_replace( "[categorietitre]",  "\$pagetitle_catname" , $ref_descriptionDE );
	$ref_descriptionDE=str_replace( "[sscatkeywords]", "\$metasscat"  , $ref_descriptionDE );
	$ref_descriptionDE=str_replace( "[keydescription]" , "\$keydescription" , $ref_descriptionDE );
	$ref_descriptionDE = addslashes($ref_descriptionDE) ;
	$ref_keywordsDE=str_replace( "[boutiquetitre]" , "\${\$title}" , $ref_keywordsDE );
	$ref_keywordsDE=str_replace( "[categorietitre]",  "\$pagetitle_catname" , $ref_keywordsDE );
	$ref_keywordsDE=str_replace( "[sscatkeywords]", "\$metasscat"  , $ref_keywordsDE );
	$ref_keywordsDE=str_replace( "[keydescription]" , "\$keydescription" , $ref_keywordsDE );
	$ref_keywordsDE = addslashes($ref_keywordsDE) ;
	$ref_titleEN=str_replace( "[boutiquetitre]" , "\${\$title}" , $ref_titleEN );
	$ref_titleEN=str_replace( "[categorietitre]",  "\$pagetitle_catname" , $ref_titleEN );
	$ref_titleEN=str_replace( "[sscatkeywords]", "\$metasscat"  , $ref_titleEN );
	$ref_titleEN=str_replace( "[keydescription]" , "\$keydescription" , $ref_titleEN );
	$ref_titleEN = addslashes($ref_titleEN) ;
	$ref_descriptionEN=str_replace( "[boutiquetitre]" , "\${\$title}" , $ref_descriptionEN );
	$ref_descriptionEN=str_replace( "[categorietitre]",  "\$pagetitle_catname" , $ref_descriptionEN );
	$ref_descriptionEN=str_replace( "[sscatkeywords]", "\$metasscat"  , $ref_descriptionEN );
	$ref_descriptionEN=str_replace( "[keydescription]" , "\$keydescription" , $ref_descriptionEN );
	$ref_descriptionEN = addslashes($ref_descriptionEN) ;
	$ref_keywordsEN=str_replace( "[boutiquetitre]" , "\${\$title}" , $ref_keywordsEN );
	$ref_keywordsEN=str_replace( "[categorietitre]",  "\$pagetitle_catname" , $ref_keywordsEN );
	$ref_keywordsEN=str_replace( "[sscatkeywords]", "\$metasscat"  , $ref_keywordsEN );
	$ref_keywordsEN=str_replace( "[keydescription]" , "\$keydescription" , $ref_keywordsEN );
	$ref_keywordsEN = addslashes($ref_keywordsEN) ;
	$ref_titleIT=str_replace( "[boutiquetitre]" , "\${\$title}" , $ref_titleIT );
	$ref_titleIT=str_replace( "[categorietitre]",  "\$pagetitle_catname" , $ref_titleIT );
	$ref_titleIT=str_replace( "[sscatkeywords]", "\$metasscat"  , $ref_titleIT );
	$ref_titleIT=str_replace( "[keydescription]" , "\$keydescription" , $ref_titleIT );
	$ref_titleIT = addslashes($ref_titleIT) ;
	$ref_descriptionIT=str_replace( "[boutiquetitre]" , "\${\$title}" , $ref_descriptionIT );
	$ref_descriptionIT=str_replace( "[categorietitre]",  "\$pagetitle_catname" , $ref_descriptionIT );
	$ref_descriptionIT=str_replace( "[sscatkeywords]", "\$metasscat"  , $ref_descriptionIT );
	$ref_descriptionIT=str_replace( "[keydescription]" , "\$keydescription" , $ref_descriptionIT );
	$ref_descriptionIT = addslashes($ref_descriptionIT) ;
	$ref_keywordsIT=str_replace( "[boutiquetitre]" , "\${\$title}" , $ref_keywordsIT );
	$ref_keywordsIT=str_replace( "[categorietitre]",  "\$pagetitle_catname" , $ref_keywordsIT );
	$ref_keywordsIT=str_replace( "[sscatkeywords]", "\$metasscat"  , $ref_keywordsIT );
	$ref_keywordsIT=str_replace( "[keydescription]" , "\$keydescription" , $ref_keywordsIT );
	$ref_keywordsIT = addslashes($ref_keywordsIT) ;
	$ref_titleSP=str_replace( "[boutiquetitre]" , "\${\$title}" , $ref_titleSP );
	$ref_titleSP=str_replace( "[categorietitre]",  "\$pagetitle_catname" , $ref_titleSP );
	$ref_titleSP=str_replace( "[sscatkeywords]", "\$metasscat"  , $ref_titleSP );
	$ref_titleSP=str_replace( "[keydescription]" , "\$keydescription" , $ref_titleSP );
	$ref_titleSP = addslashes($ref_titleSP) ;
	$ref_descriptionSP=str_replace( "[boutiquetitre]" , "\${\$title}" , $ref_descriptionSP );
	$ref_descriptionSP=str_replace( "[categorietitre]",  "\$pagetitle_catname" , $ref_descriptionSP );
	$ref_descriptionSP=str_replace( "[sscatkeywords]", "\$metasscat"  , $ref_descriptionSP );
	$ref_descriptionSP=str_replace( "[keydescription]" , "\$keydescription" , $ref_descriptionSP );
	$ref_descriptionSP = addslashes($ref_descriptionSP) ;
	$ref_keywordsSP=str_replace( "[boutiquetitre]" , "\${\$title}" , $ref_keywordsSP );
	$ref_keywordsSP=str_replace( "[categorietitre]",  "\$pagetitle_catname" , $ref_keywordsSP );
	$ref_keywordsSP=str_replace( "[sscatkeywords]", "\$metasscat"  , $ref_keywordsSP );
	$ref_keywordsSP=str_replace( "[keydescription]" , "\$keydescription" , $ref_keywordsSP );
	$ref_keywordsSP = addslashes($ref_keywordsSP) ;
	// position
	$maxposition = $DB_site2->query_first("SELECT MAX(position) FROM categorie WHERE parentid = '$parentid'");
	$position = $maxposition[0] + 1;
	$requete="INSERT INTO categorie (parentid, libelleFR, libelleDE, libelleSP, libelleIT, libelleEN, descriptionFR, descriptionEN, 
	descriptionDE, descriptionSP, descriptionIT, visible_treeview, ref_titleFR, ref_titleDE, ref_titleEN, ref_titleIT, ref_titleSP, 
	ref_descriptionFR, ref_descriptionDE, ref_descriptionEN, ref_descriptionIT, ref_descriptionSP, ref_keywordsFR, ref_keywordsDE, 
	ref_keywordsEN, ref_keywordsIT, ref_keywordsSP, position) 
	VALUES ('$parentid', '".mysqli_real_escape_string($DB_site2->link_id,htmlentities($libelleFR))."', '".mysqli_real_escape_string($DB_site2->link_id,htmlentities($libelleDE))."', '".mysqli_real_escape_string($DB_site2->link_id,htmlentities($libelleSP,ENT_COMPAT))."', 
	'".mysqli_real_escape_string($DB_site2->link_id,htmlentities($libelleIT))."', '".mysqli_real_escape_string($DB_site2->link_id,htmlentities($libelleEN))."', '".mysqli_real_escape_string($DB_site2->link_id,$descriptionFR)."', '".addslashes($descriptionEN)."', 
	'".addslashes($descriptionDE)."', '".addslashes($descriptionSP)."', '".addslashes($descriptionIT)."', '$visible_treeview', 
	'$ref_titleFR', '$ref_titleDE', '$ref_titleEN', '$ref_titleIT', '$ref_titleSP', '$ref_descriptionFR', '$ref_descriptionDE', 
	'$ref_descriptionEN', '$ref_descriptionIT', '$ref_descriptionSP', '$ref_keywordsFR', '$ref_keywordsDE', '$ref_keywordsEN', 
	'$ref_keywordsIT', '$ref_keywordsSP', '$position')";
	
	$DB_site2->query($requete);
	$insertid=$DB_site2->insert_id();
	
	echo "<input type=\"hidden\" name=\"catid\" value=\"$insertid\"><input type=\"hidden\" name=\"parentid\" value=\"$parentid\">";	
	if(in_array("FR" , $langues) && $_FILES['imageFR']['name']!=""){
		if ($_FILES['imageFR']['type']=="image/pjpeg") $type_fichier="jpg";
		if ($_FILES['imageFR']['type']=="image/jpeg") $type_fichier="jpg";
		if ($_FILES['imageFR']['type']=="image/gif") $type_fichier="gif";
		if ($_FILES['imageFR']['type']=="image/png") $type_fichier="png";
		$nom_fic=$rootpath."configurations/$host/images/categories/FR/".$insertid.".".$type_fichier;
		//$destination=$rootpath."configurations/$host/images/categories/FR/br/".$insertid.".".$type_fichier;
		$erreur=copier_image($nom_fic,"imageFR");
		if ($erreur=="1"){
			$destination=$rootpath."configurations/$host/images/categories/FR/liste/".$insertid.".".$type_fichier;
			redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
			$destination=$rootpath."configurations/$host/images/categories/FR/description/".$insertid.".".$type_fichier;
			redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
			$destination=$rootpath."configurations/$host/images/categories/FR/br/".$insertid.".".$type_fichier;
			redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
			$update_caract=" UPDATE categorie SET imageFR='$type_fichier' WHERE catid='$insertid' ";
			$DB_site2->query($update_caract);
		}else{
			echo $erreur;
		}
	}
	if(in_array("DE" , $langues) && $_FILES['imageDE']['name']!=""){
		if ($_FILES['imageDE']['type']=="image/pjpeg") $type_fichier="jpg";
		if ($_FILES['imageDE']['type']=="image/jpeg") $type_fichier="jpg";
		if ($_FILES['imageDE']['type']=="image/gif") $type_fichier="gif";
		if ($_FILES['imageDE']['type']=="image/png") $type_fichier="png";
		$nom_fic=$rootpath."configurations/$host/images/categories/DE/".$insertid.".".$type_fichier;
		//$destination=$rootpath."configurations/$host/images/categories/DE/br/".$insertid.".".$type_fichier;
		$erreur=copier_image($nom_fic,"imageDE");
		if ($erreur=="1"){
			//redimentionner_image($nom_fic,$destination,$largeurimages,$hauteurimages);
			$destination=$rootpath."configurations/$host/images/categories/DE/liste/".$insertid.".".$type_fichier;
			redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
			$destination=$rootpath."configurations/$host/images/categories/DE/description/".$insertid.".".$type_fichier;
			redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
			$destination=$rootpath."configurations/$host/images/categories/DE/br/".$insertid.".".$type_fichier;
			redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
			$update_caract=" UPDATE categorie SET imageDE='$type_fichier' WHERE catid='$insertid' ";
			$DB_site2->query($update_caract);
		}else{
			echo $erreur;
		}
	}
	if(in_array("EN" , $langues) && $_FILES['imageEN']['name']!=""){
		if ($_FILES['imageEN']['type']=="image/pjpeg") $type_fichier="jpg";
		if ($_FILES['imageEN']['type']=="image/jpeg") $type_fichier="jpg";
		if ($_FILES['imageEN']['type']=="image/gif") $type_fichier="gif";
		if ($_FILES['imageEN']['type']=="image/png") $type_fichier="png";
		$nom_fic=$rootpath."configurations/$host/images/categories/EN/".$insertid.".".$type_fichier;
		//$destination=$rootpath."configurations/$host/images/categories/EN/br/".$insertid.".".$type_fichier;
		$erreur=copier_image($nom_fic,"imageEN");
		if ($erreur=="1"){
			//redimentionner_image($nom_fic,$destination,$largeurimages,$hauteurimages);
			$destination=$rootpath."configurations/$host/images/categories/EN/liste/".$insertid.".".$type_fichier;
			redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
			$destination=$rootpath."configurations/$host/images/categories/EN/description/".$insertid.".".$type_fichier;
			redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
			$destination=$rootpath."configurations/$host/images/categories/EN/br/".$insertid.".".$type_fichier;
			redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
			$update_caract=" UPDATE categorie SET imageEN='$type_fichier' WHERE catid='$insertid' ";
			$DB_site2->query($update_caract);
		}else{
			echo $erreur;
		}
	}
	if(in_array("IT" , $langues) && $_FILES['imageIT']['name']!="")
		{
		if ($_FILES['imageIT']['type']=="image/pjpeg") $type_fichier="jpg";
		if ($_FILES['imageIT']['type']=="image/jpeg") $type_fichier="jpg";
		if ($_FILES['imageIT']['type']=="image/gif") $type_fichier="gif";
		if ($_FILES['imageIT']['type']=="image/png") $type_fichier="png";
		$nom_fic=$rootpath."configurations/$host/images/categories/IT/".$insertid.".".$type_fichier;
		//$destination=$rootpath."configurations/$host/images/categories/IT/br/".$insertid.".".$type_fichier;
		$erreur=copier_image($nom_fic,"imageIT");
		if ($erreur=="1") 
			{
			//redimentionner_image($nom_fic,$destination,$largeurimages,$hauteurimages);
			$destination=$rootpath."configurations/$host/images/categories/IT/liste/".$insertid.".".$type_fichier;
			redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
			$destination=$rootpath."configurations/$host/images/categories/IT/description/".$insertid.".".$type_fichier;
			redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
			$destination=$rootpath."configurations/$host/images/categories/IT/br/".$insertid.".".$type_fichier;
			redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
			$update_caract=" UPDATE categorie SET imageIT='$type_fichier' WHERE catid='$insertid' ";
			$DB_site2->query($update_caract);
			} 
		else 
			{
			echo $erreur;
			}
		}
	if(in_array("SP" , $langues) && $_FILES['imageSP']['name']!="")
		{
		if ($_FILES['imageSP']['type']=="image/pjpeg") $type_fichier="jpg";
		if ($_FILES['imageSP']['type']=="image/jpeg") $type_fichier="jpg";
		if ($_FILES['imageSP']['type']=="image/gif") $type_fichier="gif";
		if ($_FILES['imageSP']['type']=="image/png") $type_fichier="png";
		$nom_fic=$rootpath."configurations/$host/images/categories/SP/".$insertid.".".$type_fichier;
		//$destination=$rootpath."configurations/$host/images/categories/SP/br/".$insertid.".".$type_fichier;
		$erreur=copier_image($nom_fic,"imageSP");
		if ($erreur=="1") 
			{
			//redimentionner_image($nom_fic,$destination,$largeurimages,$hauteurimages);
			$destination=$rootpath."configurations/$host/images/categories/SP/liste/".$insertid.".".$type_fichier;
			redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
			$destination=$rootpath."configurations/$host/images/categories/SP/description/".$insertid.".".$type_fichier;
			redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
			$destination=$rootpath."configurations/$host/images/categories/SP/br/".$insertid.".".$type_fichier;
			redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
			$update_caract=" UPDATE categorie SET imageSP='$type_fichier' WHERE catid='$insertid' ";
			$DB_site2->query($update_caract);
			} 
		else 
			{
			echo $erreur;
			}
		}
	echo "<table align=center width=400>
			<tr>
				<td>Un nouveau rayon a été ajouté.
					<br>Vous allez maintenant définir les caractéristiques de ce rayon
					<br><br>Merci de patienter quelques secondes...
			  </td>
			 </tr>
			</table>
			  <script language=\"JavaScript\"> 
				setTimeout(\"redirige()\",500);
				function redirige()
					{
					document.formu.submit();
					}
			  </script>
		  </form>";
	include "./footer.php";
	exit;
	}

// Modifier Visible et Filtrable
if( $action == "modifetat" )
	{
	if( $visible != 1 )
		{
		$visible = 0;
		}
	if( $filtrable != 1 )
		{
		$filtrable = 0;
		}
	if( $stockable != 1 )
		{
		$stockable = 0;
		}
		
	$DB_site2->query("UPDATE categorie_caracteristique SET visible='".$visible."', filtrable='".$filtrable."' WHERE caractid='".$caractid."' AND catid='".$catid."';");

	$action = "20ajouter";
	}

if ($action=="20ajouter" && false)
	{
	if( $catid != "" )
		{
		/////////////////////////////////////////////////////////////////////////////////////////////////
		// CARACTERISTIQUES SUR LA CATEGORIE
		/////////////////////////////////////////////////////////////////////////////////////////////////
		$caracteristiques_deja_ajoutees = array();
		foreach($_POST as $cle=>$valeur)
			{
			if (substr($cle,0,7)=="caract_")
				{
				array_push($caracteristiques_deja_ajoutees,substr($cle,7,strlen($cle)-7));
				}
			if ($cle=="newcaract" && $valeur!="")
				{
				array_push($caracteristiques_deja_ajoutees,$valeur);
				}
			}
		echo "<h3>Associer des caractéristiques au rayon</h3>
			<table class=\"table_remises_ajout\">";
		$liste_notin = "0," ;
		$cars=$DB_site2->query("select caractid from categorie_caracteristique where catid=$catid") ;
		while ($car=$DB_site2->fetch_array($cars))
			{
			$liste_notin .= $car[caractid]."," ;
			}
		$lg = strlen($liste_notin) - 1 ;
		$liste_notin = substr($liste_notin, 0, $lg) ;
		$combien=$DB_site2->query_first("select count(*) from caracteristique where caractid not in ($liste_notin)");
		if ($combien[0]>0)
			{
			echo "<tr width=\"100%\">
					<td>
						<form method=\"post\" action=\"ajouter_categorie.php\" name=\"formu\">
						<input type=\"hidden\" name=\"action\" value=\"30ajouter\"><input type=\"hidden\" name=\"caractid\">
						<input type=\"hidden\" name=\"catid\" value=\"$catid\"><input type=\"hidden\" name=\"parentid\" value=\"$parentid\">
				  <table>
					<tr>
						<td><strong>&nbsp;Caractéristique&nbsp;</strong></td>
						<td><strong>&nbsp;Description&nbsp;</strong></td>
						<td align=\"center\" width=\"100%\"><strong>&nbsp;ACTION&nbsp;</strong></td>
					</tr>";
			$liste_notin = "0," ;
			$cars=$DB_site2->query("select caractid from categorie_caracteristique where catid=$catid") ;
			while ($car=$DB_site2->fetch_array($cars))
				{
				$liste_notin .= $car[caractid]."," ;
				}
			$lg = strlen($liste_notin) - 1 ;
			$liste_notin = substr($liste_notin, 0, $lg) ;
			$caracteristiques=$DB_site2->query("select caractid, $libelle, description from caracteristique where caractid not in 
												($liste_notin)");
			while ($caracteristique=$DB_site2->fetch_array($caracteristiques))
				{
				$rowalt = "td_".getrowbg();
				echo "<tr>
						<td class=\"$rowalt\">".stripslashes($caracteristique[$libelle])."</td>
						<td class=\"$rowalt\">".stripslashes($caracteristique[description])."</td>
						<td class=\"$rowalt\" align=\"center\"><input type=\"button\" value=\"Ajouter\" onclick=\"ajouter('$caracteristique[0]');\"></td>
					  </tr>";
				}
			echo "</table>
				</form>
				</td>
			</tr>";
			}
		else
			{
			echo "<tr>
					<td align=\"center\">Aucune caractéristique restante<br><br></td>
				</tr>";
			}
		echo "</table>" ;
		echo "<br><h3>Liste des caractéristiques associées au rayon</h3>";
		if(in_array("4", $modules))
			echo "Attention la suppression d'une caractéristique entraine une remise à zero de l'historique de stocks...<br /><br />";
		echo "<table class=\"table_remises_ajout\">";
		$requete="SELECT caract.$libelle, caract.caractid, caract.description, cat_caract.visible, cat_caract.filtrable
				  FROM caracteristique as caract INNER JOIN categorie_caracteristique AS cat_caract ON 
				  (caract.caractid=cat_caract.caractid) WHERE catid=$catid";
		$caracts=$DB_site2->query($requete);
		if( $DB_site2->num_rows( $caracts ) > 0 )
			{
			echo "<tr>
					<td>
					  <table width=\"100%\">
						<tr>
							<td width=\"20%\"><strong>&nbsp;Caractéristique&nbsp;</strong></td>
							<td><strong>&nbsp;Description&nbsp;</strong></td>
							<td width=\"100%\"></td>
							<td align=\"center\"><strong>&nbsp;ACTION&nbsp;</strong></td>
						</tr>";
			while ($caract=$DB_site2->fetch_array($caracts))
				{
				$rowalt = "td_".getrowbg();
				$visible_checked = "";
				$filtrable_checked = "";
				$stockable_checked = "";
				if( $caract[visible] == "1" ) 
					{
					$visible_checked = "checked";
					}
				if( $caract[filtrable] == "1" ) 
					{
					$filtrable_checked = "checked";
					}
				if( $caract[stockable] == "1" ) 
					{
					$stockable_checked = "checked";
					}
				echo "<tr>
						<td class=\"$rowalt\">".stripslashes($caract[$libelle])."</td>
						<td class=\"$rowalt\">".stripslashes($caract[description])."</td>
						<td class=\"$rowalt\" valign=\"middle\" align=\"center\" >
						<form action=\"ajouter_categorie.php\" method=\"post\">
						  <input type=\"hidden\" name=\"action\" value=\"modifetat\">
						  <input type=\"hidden\" name=\"catid\" value=\"$catid\">
						  <input type=\"hidden\" name=\"caractid\" value=\"$caract[caractid]\">
						  <input type=\"checkbox\" name=\"visible\" value=\"1\" $visible_checked> visible 
						  <input type=\"checkbox\" name=\"filtrable\" value=\"1\" $filtrable_checked> filtrable ";

				echo "<input type=\"submit\" value=\"Modifier\">
						 </form>
						 </td>
						 <td class=\"$rowalt\" align=\"center\">
						 	<form method=\"post\" action=\"ajouter_categorie.php\">
						    <input type=\"hidden\" name=\"action\" value=\"30supprimer\">
							<input type=\"hidden\" name=\"caractid\" value=\"$caract[caractid]\">
						    <input type=\"hidden\" name=\"catid\" value=\"$catid\">
							<input type=\"hidden\" name=\"parentid\" value=\"$parentid\">
						    <input type=\"submit\" value=\"Supprimer\"></form>
						  </td>
					  </tr>";
				}
			echo "</table>
					</td>
				</tr>";
			}
		else
			{
			echo "<tr>
					<td align=\"center\">Aucune caractéristique associée<br><br></td>
				</tr>";
			}
		echo "</table>";
		/////////////////////////////////////////////////////////////////////////////////////////////////
		// REMISES
		/////////////////////////////////////////////////////////////////////////////////////////////////
		if( in_array( "3", $modules ) )
			{
			echo "<br><table class=\"table_modules_boutiques\">
						<tr>
							<td colspan=\"4\" align=\"center\"><h1>Remises sur ce rayon</h1></td>
						</tr>";
			$nb_remises = 0;
			$rs_parentid_categorie = $DB_site2->query_first( "SELECT parentid FROM categorie WHERE catid = $catid" );
			if( $rs_parentid_categorie["parentid"] != 0 )
				{
				$tab_catid = array();
				$tmp_catid = $rs_parentid_categorie["parentid"];
				while( $tmp_catid != 0 )
					{
					$tab_catid[] = $tmp_catid;
					$rs_categorie = $DB_site2->query_first( "SELECT parentid FROM categorie	 WHERE catid = $tmp_catid" );
					$tmp_catid = $rs_categorie["parentid"];
					}
				if( count( $tab_catid ) > 0 )
					{
					for( $i=count($tab_catid)-1; $i>=0; $i-- )
						{
						$rq_remises_categorie = $DB_site2->query( "SELECT * FROM remisecategorie INNER JOIN remise 
														ON (remisecategorie.remiseid=remise.remiseid) WHERE catid = $tab_catid[$i]
														ORDER BY typeremise, valeur" );
						if( $DB_site2->num_rows( $rq_remises_categorie ) > 0 )
							{
							$rs_categorie = $DB_site2->query_first( "SELECT libelle$langue FROM categorie
											 WHERE catid = ".$tab_catid[$i] );
							
							if( $i == count($tab_catid)-1 )
								{
								$tmp_rayon = "rayon"; // Catégorie la + haute dans la hiérarchie des catégories
								}
							else
								{
								$tmp_rayon = "sous-rayon";
								}
							// on affiche la légende des colonnes qu'une seule fois
							if( !isset( $tmp_libelles_menu_remise ) )
								{
								$tmp_libelles_menu_remise = 1;
								echo "<tr>
										<td class=\"td_modules_boutiques\" width=\"20%\">Type de remise</td>
										  <td class=\"td_modules_boutiques\" width=\"25%\" align=\"right\">Valeur (poids,prix ou qté)</td>
										  <td class=\"td_modules_boutiques\" width=\"25%\" align=\"right\">Pourcentage de remise</td>
									  </tr>";
								}
								echo "<tr>
										<td class=\"td_modules_boutiques\" colspan=\"4\">Remises pour le $tmp_rayon : $rs_categorie[$libelle]</td>
									</tr>";
							while( $rs_remises_categorie = $DB_site2->fetch_array( $rq_remises_categorie ) )
								{
								// On affiche les remises de cette catégorie
								$rowalt = "td_".getrowbg();
								echo "<tr>";	
								if( $rs_remises_categorie["typeremise"] == '0' ) 
									{
									echo "  <td class=\"$rowalt\">sur le poids</td>";
									$unite_valeur = "grammes";
									} 
								elseif( $rs_remises_categorie["typeremise"] == '1' ) 
									{
									echo "  <td class=\"$rowalt\">sur le prix</td>";
									$unite_valeur = "€";
									} 
								else 
									{ // $rs_remises_categorie["typeremise"] == '2'
									echo "  <td class=\"$rowalt\">sur la quantité</td>";
									$unite_valeur = "articles";
									}
								echo "<td class=\"$rowalt\" align=\"right\">$rs_remises_categorie[valeur] $unite_valeur</td>
									<td class=\"$rowalt\" align=\"right\">$rs_remises_categorie[pctremise] %</td>
									<td></td>
								</tr>";
								}
							$nb_remises++;
							}
						}
					}
				}
			// Remises sur la catégorie
			$rq_remises_categorie = $DB_site2->query( "SELECT * FROM remisecategorie as rc INNER JOIN remise as r 
													ON (rc.remiseid=r.remiseid) WHERE catid = $catid ORDER BY typeremise, valeur" );
			if( $DB_site2->num_rows( $rq_remises_categorie ) > 0 )
				{
				if( !isset( $tmp_libelles_menu_remise ) )
					{
					echo "<tr>
								<td class=\"td_modules_boutiques\" width=\"20%\">Type de remise</td>
							  <td class=\"td_modules_boutiques\" width=\"25%\" align=\"right\">Valeur (poids,prix ou qté)</td>
							  <td class=\"td_modules_boutiques\" width=\"25%\" align=\"right\">Pourcentage de remise</td>
						  </tr>";
					}
				else
					{
					echo "<tr><td class=\"td_modules_boutiques\" colspan=\"4\">Remises pour ce rayon :</td></tr>";
					}
				while( $rs_remises_categorie = $DB_site2->fetch_array( $rq_remises_categorie ) )
					{
					$rowalt = "td_".getrowbg();
					echo "<tr>";	
					if( $rs_remises_categorie["typeremise"] == '0' ) 
						{
						echo "  <td class=\"$rowalt\">sur le poids</td>";
						$unite_valeur = "grammes";
						} 
					elseif( $rs_remises_categorie["typeremise"] == '1' )  {
						echo "  <td class=\"$rowalt\">sur le prix</td>";
						$unite_valeur = "€";
						} 
					else { // $rs_remises_categorie["typeremise"] == '2'
						echo "  <td class=\"$rowalt\">sur la quantité</td>";
						$unite_valeur = "articles";
						}
					echo "<td class=\"$rowalt\" align=\"right\">$rs_remises_categorie[valeur] $unite_valeur</td>
							<td class=\"$rowalt\" align=\"right\">$rs_remises_categorie[pctremise] %</td>
							<td class=\"$rowalt\" align=\"center\"><a href=ajouter_categorie.php?action=supprimer_remise&catid=$catid&remiseid=$rs_remises_categorie[remiseid]>Supprimer</a></td>
						</tr>";
					}
				$nb_remises++;
				}
			if ($nb_remises == 0)
				echo "<tr>
						<td class=\"td_modules_boutiques\" colspan=\"4\"><br>Aucune remise sur le rayon</td>
					</tr>
					<tr>
						<td class=\"td_modules_boutiques\" align=\"center\" colspan=\"4\"><br><a href=\"ajouter_remise.php?catid=$catid\" target=\"_blank\">Ajouter une remise sur le rayon</a></td>
					</tr>
				</table>";
			}
		?>
		<script language="javascript">
		function ajouter(caractid)
			{
			document.formu.caractid.value=caractid;
			document.formu.action.value="30ajouter";
			document.formu.submit();
			}
		function supprimer(caractid)
			{
			document.formu.caractid.value=caractid;
			document.formu.action.value="30supprimer";
			document.formu.submit();
			}
		function terminer()
			{
			location.href = "catalogue.php?catid=<?=$parentid?>";
			}
		</script>
		<?php
		echo "<div align=\"center\"><br><input type=\"button\" value=\"Terminer\" onClick=\"terminer()\"></div>";

		include "./footer.php";
		exit;
		}
	}

// ajout caracts 2
if ($action=="30ajouter")
	{
	echo "<form name=\"formu\" method=\"post\" action=\"ajouter_categorie.php\"><input type=\"hidden\" name=\"catid\" value=\"$catid\">
		  <input type=\"hidden\" name=\"action\" value=\"20ajouter\"><input type=\"hidden\" name=\"parentid\" value=\"$parentid\"></form>";
	$requete="INSERT INTO categorie_caracteristique (caractid,catid) VALUES ('$caractid','$catid')";
	$DB_site2->query($requete);
	echo "<script language=\"javascript\">document.formu.submit();</script>";
	exit;
	}

// supprimer caracts
if ($action=="30supprimer")	
	{
	echo "<form name=\"formu\" method=\"post\" action=\"ajouter_categorie.php\"><input type=\"hidden\" name=\"catid\" value=\"$catid\">
		  <input type=\"hidden\" name=\"action\" value=\"20ajouter\"><input type=\"hidden\" name=\"parentid\" value=\"$parentid\"></form>";

	//si on a les stocks on supprime la caracteristique des stocks
	if(in_array("4", $modules)) {
	
		//requete de tout les article de la categorie
		$artcat_query = "SELECT artid FROM article WHERE catid = '$catid';";
		$artcat_req = $DB_site2->query($artcat_query);
		
		$artid_array = array();
		while($artcat = $DB_site2->fetch_array($artcat_req))
			$artid_array[] = $artcat['artid'];
			
		//on execute la fonction adeguate
		require_once("function_stock.php");
		stock_delete_caract($DB_site2, array($caractid), $artid_array);
		//voila ya pas plus simple !
	}
	
	//on supprime la categorie
	$requete="delete from categorie_caracteristique where caractid='$caractid' and catid='$catid'";
	$DB_site2->query($requete);
	
	//on suprime les article_caractval
	$DB_site2->query("DELETE FROM article_caractval WHERE artid IN(SELECT artid FROM article WHERE catid = '$catid') AND caractvalid IN(SELECT cv.caractvalid FROM caracteristiquevaleur as cv WHERE cv.caractid = '$caractid');");
		
	echo "<script language=\"javascript\">document.formu.submit();</script>";
	exit;
	}

if( in_array( "3", $modules ) && $action == "supprimer_remise" )
	{
	// Confirmation de suppression de remise
	echo "Etes-vous sûr(e) de vouloir supprimer la remise sur ce rayon ?<br>
			<a href=ajouter_categorie.php?action=supprimer_remise2&catid=$catid&remiseid=$remiseid>Oui</a>
		  | <a href=\"ajouter_categorie.php?action=20ajouter&catid=$catid\">Non</a>";

	include "./footer.php";
	exit;
	}
	
if( in_array( "3", $modules ) && $action == "supprimer_remise2" )
	{
	// Suppression
	$rq_supprimer = "delete from remisecategorie where catid=$catid AND remiseid=$remiseid";
	$DB_site2->query( $rq_supprimer );
	echo "<script language=\"javascript\">location.href=\"ajouter_categorie.php?action=20ajouter&catid=$catid\";</script>";

	include "./footer.php";
	exit;
	}

//*******************************************************************
//****************AFFICHAGE BARRE NAVIGATION*************************
//*******************************************************************
if ($parentid=="0")
	{
	$navigationdebut="<a href=\"catalogue.php\">".${"title$langue"}."</a>";
	}
else
	{
	$navigationdebut="<a href=\"catalogue.php\">".${"title$langue"}."</a> > ";
	}
navigation($parentid ,1 );
echo ($navigationdebut.$navigationbit."Ajouter un rayon<br><br>");

//***************************************************************************************
//*****************AFFICHAGE DES LIBELLE / DESCRIPTION / LANGUES*************************
//***************************************************************************************
echo "<table class=\"table_modules_boutiques\">
		<form name=\"formu\" enctype=\"multipart/form-data\" method=\"post\" action=\"ajouter_categorie.php\">
		<input type=\"hidden\" name=\"parentid\" value=\"$parentid\">";
if(in_array("FR" , $langues))
	{
	echo "<tr><td colspan=\"2\"><strong>Français</strong></td></tr>";
	echo "<tr><td class=\"td_modules_boutiques\">Libellé FR</td><td class=\"td_modules_boutiques\"><textarea name=\"libelleFR\" cols=\"50\" rows=\"2\"></textarea></td></tr>";
	echo "<tr><td class=\"td_modules_boutiques\">Description FR</td><td class=\"td_modules_boutiques\"><textarea name=\"descriptionFR\" class=\"ckeditor\"></textarea></td></tr>";
	echo "<tr><td class=\"td_modules_boutiques\">Image FR</td><td class=\"td_modules_boutiques\"><input type=\"file\" name=\"imageFR\">";
	}
if(in_array("DE" , $langues))
	{
	echo "<tr><td colspan=\"2\"><strong>Allemand</strong></td></tr>";
	echo "<tr><td class=\"td_modules_boutiques\">Libellé DE</td><td class=\"td_modules_boutiques\"><textarea name=\"libelleDE\" cols=\"50\" rows=\"2\"></textarea></td></tr>";
	echo "<tr><td class=\"td_modules_boutiques\">Description DE</td><td class=\"td_modules_boutiques\"><textarea name=\"descriptionDE\" cols=\"50\" rows=\"6\"></textarea></td></tr>";
	echo "<tr><td class=\"td_modules_boutiques\">Image DE</td><td class=\"td_modules_boutiques\"><input type=\"file\" name=\"imageDE\">";
	}
if(in_array("SP" , $langues))
	{
	echo "<tr><td colspan=\"2\"><strong>Espagnol</strong></td></tr>";
	echo "<tr><td class=\"td_modules_boutiques\">Libellé SP</td><td class=\"td_modules_boutiques\"><textarea name=\"libelleSP\" cols=\"50\" rows=\"2\"></textarea></td></tr>";
	echo "<tr><td class=\"td_modules_boutiques\">Description SP</td><td class=\"td_modules_boutiques\"><textarea name=\"descriptionSP\" cols=\"50\" rows=\"6\"></textarea></td></tr>";
	echo "<tr><td class=\"td_modules_boutiques\">Image SP</td><td class=\"td_modules_boutiques\"><input type=\"file\" name=\"imageSP\">";
	}
if(in_array("IT" , $langues))
	{
	echo "<tr><td colspan=\"2\"><strong>Ilatien</strong></td></tr>";
	echo "<tr><td class=\"td_modules_boutiques\">Libellé IT</td><td class=\"td_modules_boutiques\"><textarea name=\"libelleIT\" cols=\"50\" rows=\"2\"></textarea></td></tr>";
	echo "<tr><td class=\"td_modules_boutiques\">Description IT</td><td class=\"td_modules_boutiques\"><textarea name=\"descriptionIT\" cols=\"50\" rows=\"6\"></textarea></td></tr>";
	echo "<tr><td class=\"td_modules_boutiques\">Image IT</td><td class=\"td_modules_boutiques\"><input type=\"file\" name=\"imageIT\">";
	}
if(in_array("EN" , $langues))
	{
	echo "<tr><td colspan=\"2\"><strong>Anglais</strong></td></tr>";
	echo "<tr><td class=\"td_modules_boutiques\">Libellé EN</td><td class=\"td_modules_boutiques\"><textarea name=\"libelleEN\" cols=\"50\" rows=\"2\"></textarea></td></tr>";
	echo "<tr><td class=\"td_modules_boutiques\">Description EN</td><td class=\"td_modules_boutiques\"><textarea name=\"descriptionEN\" cols=\"50\" rows=\"6\"></textarea></td></tr>";
	echo "<tr><td class=\"td_modules_boutiques\">Image EN</td><td class=\"td_modules_boutiques\"><input type=\"file\" name=\"imageEN\">";
	}
echo "</table>
	<br>
	<input type=\"checkbox\" name=\"visible_treeview\" checked> Rayon visible dans la liste<br>
	[ Si vous n'avez qu'un seul produit dans ce rayon, décochez cette case pour que les visiteurs arrivent directement sur ce produit en cliquant sur le rayon ]
	<br><br>
	<strong>OPTIMISATION REFERENCEMENT</strong>
	<br><br>
	<table class=\"table_modules_boutiques\">";
if(in_array("FR" , $langues))
	{
	if ($ref_titleFR == "")
		{
		$contenu=$DB_site2->query_first("select titleFR from pagetitle where scriptname = '/categorie.php'");
		$contenu[0]=str_replace( "\${\$title}" , "[boutiquetitre]" , $contenu[0] );
		$contenu[0]=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $contenu[0] );
		$contenu[0]=str_replace( "\$metasscat" , "[sscatkeywords]" , $contenu[0] );
		$contenu[0]=str_replace( "\$keydescription" , "[keydescription]" , $contenu[0] );
		$ref_titleFR = $contenu[0] ;
		}
	if ($ref_descriptionFR == "")
		{
		$contenu=$DB_site2->query_first("select titleFR from description where scriptname = '/categorie.php'");
		$contenu[0]=str_replace( "\${\$title}" , "[boutiquetitre]" , $contenu[0] );
		$contenu[0]=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $contenu[0] );
		$contenu[0]=str_replace( "\$metasscat" , "[sscatkeywords]" , $contenu[0] );
		$contenu[0]=str_replace( "\$keydescription" , "[keydescription]" , $contenu[0] );
		$ref_descriptionFR = $contenu[0] ;
		}
	if ($ref_keywordsFR == "")
		{
		$contenu=$DB_site2->query_first("select titleFR from keywords where scriptname = '/categorie.php'");
		$contenu[0]=str_replace( "\${\$title}" , "[boutiquetitre]" , $contenu[0] );
		$contenu[0]=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $contenu[0] );
		$contenu[0]=str_replace( "\$metasscat" , "[sscatkeywords]" , $contenu[0] );
		$contenu[0]=str_replace( "\$keydescription" , "[keydescription]" , $contenu[0] );
		$ref_keywordsFR = $contenu[0] ;
		}
	echo "<tr><td colspan=\"2\"><strong>Français</strong></td></tr>";
	echo "<tr><td class=\"td_modules_boutiques\" valign=\"top\">Titre de la fenêtre FR</td><td class=\"td_modules_boutiques\"><textarea name=\"ref_titleFR\" cols=\"50\" rows=\"2\">$ref_titleFR</textarea></td></tr>";
	echo "<tr><td class=\"td_modules_boutiques\" valign=\"top\">META description FR</td><td class=\"td_modules_boutiques\"><textarea name=\"ref_descriptionFR\" cols=\"50\" rows=\"6\">$ref_descriptionFR</textarea></td></tr>";
	echo "<tr><td class=\"td_modules_boutiques\" valign=\"top\">META keywords FR</td><td class=\"td_modules_boutiques\"><textarea name=\"ref_keywordsFR\" cols=\"50\" rows=\"6\">$ref_keywordsFR</textarea>";
	}
if(in_array("DE" , $langues))
	{
	if ($ref_titleDE == "")
		{
		$contenu=$DB_site2->query_first("select titleDE from pagetitle where scriptname = '/categorie.php'");
		$contenu[0]=str_replace( "\${\$title}" , "[boutiquetitre]" , $contenu[0] );
		$contenu[0]=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $contenu[0] );
		$contenu[0]=str_replace( "\$metasscat" , "[sscatkeywords]" , $contenu[0] );
		$contenu[0]=str_replace( "\$keydescription" , "[keydescription]" , $contenu[0] );
		$ref_titleDE = $contenu[0] ;
		}
	if ($ref_descriptionDE == "")
		{
		$contenu=$DB_site2->query_first("select titleDE from description where scriptname = '/categorie.php'");
		$contenu[0]=str_replace( "\${\$title}" , "[boutiquetitre]" , $contenu[0] );
		$contenu[0]=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $contenu[0] );
		$contenu[0]=str_replace( "\$metasscat" , "[sscatkeywords]" , $contenu[0] );
		$contenu[0]=str_replace( "\$keydescription" , "[keydescription]" , $contenu[0] );
		$ref_descriptionDE = $contenu[0] ;
		}
	if ($ref_keywordsDE == "")
		{
		$contenu=$DB_site2->query_first("select titleDE from keywords where scriptname = '/categorie.php'");
		$contenu[0]=str_replace( "\${\$title}" , "[boutiquetitre]" , $contenu[0] );
		$contenu[0]=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $contenu[0] );
		$contenu[0]=str_replace( "\$metasscat" , "[sscatkeywords]" , $contenu[0] );
		$contenu[0]=str_replace( "\$keydescription" , "[keydescription]" , $contenu[0] );
		$ref_keywordsDE = $contenu[0] ;
		}
	echo "<tr><td colspan=\"2\"><strong>Allemand</strong></td></tr>";
	echo "<tr><td class=\"td_modules_boutiques\" valign=\"top\">Titre de la fenêtre DE</td><td class=\"td_modules_boutiques\"><textarea name=\"ref_titleDE\" cols=\"50\" rows=\"2\">$ref_titleDE</textarea></td></tr>";
	echo "<tr><td class=\"td_modules_boutiques\" valign=\"top\">META description DE</td><td class=\"td_modules_boutiques\"><textarea name=\"ref_descriptionDE\" cols=\"50\" rows=\"6\">$ref_descriptionDE</textarea></td></tr>";
	echo "<tr><td class=\"td_modules_boutiques\" valign=\"top\">META keywords DE</td><td class=\"td_modules_boutiques\"><textarea name=\"ref_keywordsDE\" cols=\"50\" rows=\"6\">$ref_keywordsDE</textarea>";
	}
if(in_array("SP" , $langues))
	{
	if ($ref_titleSP == "")
		{
		$contenu=$DB_site2->query_first("select titleSP from pagetitle where scriptname = '/categorie.php'");
		$contenu[0]=str_replace( "\${\$title}" , "[boutiquetitre]" , $contenu[0] );
		$contenu[0]=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $contenu[0] );
		$contenu[0]=str_replace( "\$metasscat" , "[sscatkeywords]" , $contenu[0] );
		$contenu[0]=str_replace( "\$keydescription" , "[keydescription]" , $contenu[0] );
		$ref_titleSP = $contenu[0] ;
		}
	if ($ref_descriptionSP == "")
		{
		$contenu=$DB_site2->query_first("select titleSP from description where scriptname = '/categorie.php'");
		$contenu[0]=str_replace( "\${\$title}" , "[boutiquetitre]" , $contenu[0] );
		$contenu[0]=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $contenu[0] );
		$contenu[0]=str_replace( "\$metasscat" , "[sscatkeywords]" , $contenu[0] );
		$contenu[0]=str_replace( "\$keydescription" , "[keydescription]" , $contenu[0] );
		$ref_descriptionSP = $contenu[0] ;
		}
	if ($ref_keywordsSP == "")
		{
		$contenu=$DB_site2->query_first("select titleSP from keywords where scriptname = '/categorie.php'");
		$contenu[0]=str_replace( "\${\$title}" , "[boutiquetitre]" , $contenu[0] );
		$contenu[0]=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $contenu[0] );
		$contenu[0]=str_replace( "\$metasscat" , "[sscatkeywords]" , $contenu[0] );
		$contenu[0]=str_replace( "\$keydescription" , "[keydescription]" , $contenu[0] );
		$ref_keywordsSP = $contenu[0] ;
		}
	echo "<tr><td colspan=\"2\"><strong>Espagnol</strong></td></tr>";
	echo "<tr><td class=\"td_modules_boutiques\" valign=\"top\">Titre de la fenêtre SP</td><td class=\"td_modules_boutiques\"><textarea name=\"ref_titleSP\" cols=\"50\" rows=\"2\">$ref_titleSP</textarea></td></tr>";
	echo "<tr><td class=\"td_modules_boutiques\" valign=\"top\">META description SP</td><td class=\"td_modules_boutiques\"><textarea name=\"ref_descriptionSP\" cols=\"50\" rows=\"6\">$ref_descriptionSP</textarea></td></tr>";
	echo "<tr><td class=\"td_modules_boutiques\" valign=\"top\">META keywords SP</td><td class=\"td_modules_boutiques\"><textarea name=\"ref_keywordsSP\" cols=\"50\" rows=\"6\">$ref_keywordsSP</textarea>";
	}
if(in_array("IT" , $langues))
	{
	if ($ref_titleIT == "")
		{
		$contenu=$DB_site2->query_first("select titleIT from pagetitle where scriptname = '/categorie.php'");
		$contenu[0]=str_replace( "\${\$title}" , "[boutiquetitre]" , $contenu[0] );
		$contenu[0]=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $contenu[0] );
		$contenu[0]=str_replace( "\$metasscat" , "[sscatkeywords]" , $contenu[0] );
		$contenu[0]=str_replace( "\$keydescription" , "[keydescription]" , $contenu[0] );
		$ref_titleIT = $contenu[0] ;
		}
	if ($ref_descriptionIT == "")
		{
		$contenu=$DB_site2->query_first("select titleIT from description where scriptname = '/categorie.php'");
		$contenu[0]=str_replace( "\${\$title}" , "[boutiquetitre]" , $contenu[0] );
		$contenu[0]=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $contenu[0] );
		$contenu[0]=str_replace( "\$metasscat" , "[sscatkeywords]" , $contenu[0] );
		$contenu[0]=str_replace( "\$keydescription" , "[keydescription]" , $contenu[0] );
		$ref_descriptionIT = $contenu[0] ;
		}
	if ($ref_keywordsIT == "")
		{
		$contenu=$DB_site2->query_first("select titleIT from keywords where scriptname = '/categorie.php'");
		$contenu[0]=str_replace( "\${\$title}" , "[boutiquetitre]" , $contenu[0] );
		$contenu[0]=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $contenu[0] );
		$contenu[0]=str_replace( "\$metasscat" , "[sscatkeywords]" , $contenu[0] );
		$contenu[0]=str_replace( "\$keydescription" , "[keydescription]" , $contenu[0] );
		$ref_keywordsIT = $contenu[0] ;
		}
	echo "<tr><td colspan=\"2\"><strong>Ilatien</strong></td></tr>";
	echo "<tr><td class=\"td_modules_boutiques\" valign=\"top\">Titre de la fenêtre IT</td><td class=\"td_modules_boutiques\"><textarea name=\"ref_titleIT\" cols=\"50\" rows=\"2\">$ref_titleIT</textarea></td></tr>";
	echo "<tr><td class=\"td_modules_boutiques\" valign=\"top\">META description IT</td><td class=\"td_modules_boutiques\"><textarea name=\"ref_descriptionIT\" cols=\"50\" rows=\"6\">$ref_descriptionIT</textarea></td></tr>";
	echo "<tr><td class=\"td_modules_boutiques\" valign=\"top\">META keywords IT</td><td class=\"td_modules_boutiques\"><textarea name=\"ref_keywordsIT\" cols=\"50\" rows=\"6\">$ref_keywordsIT</textarea>";
	}
if(in_array("EN" , $langues))
	{
	if ($ref_titleEN == "")
		{
		$contenu=$DB_site2->query_first("select titleEN from pagetitle where scriptname = '/categorie.php'");
		$contenu[0]=str_replace( "\${\$title}" , "[boutiquetitre]" , $contenu[0] );
		$contenu[0]=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $contenu[0] );
		$contenu[0]=str_replace( "\$metasscat" , "[sscatkeywords]" , $contenu[0] );
		$contenu[0]=str_replace( "\$keydescription" , "[keydescription]" , $contenu[0] );
		$ref_titleEN = $contenu[0] ;
		}
	if ($ref_descriptionEN == "")
		{
		$contenu=$DB_site2->query_first("select titleEN from description where scriptname = '/categorie.php'");
		$contenu[0]=str_replace( "\${\$title}" , "[boutiquetitre]" , $contenu[0] );
		$contenu[0]=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $contenu[0] );
		$contenu[0]=str_replace( "\$metasscat" , "[sscatkeywords]" , $contenu[0] );
		$contenu[0]=str_replace( "\$keydescription" , "[keydescription]" , $contenu[0] );
		$ref_descriptionEN = $contenu[0] ;
		}
	if ($ref_keywordsEN == "")
		{
		$contenu=$DB_site2->query_first("select titleEN from keywords where scriptname = '/categorie.php'");
		$contenu[0]=str_replace( "\${\$title}" , "[boutiquetitre]" , $contenu[0] );
		$contenu[0]=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $contenu[0] );
		$contenu[0]=str_replace( "\$metasscat" , "[sscatkeywords]" , $contenu[0] );
		$contenu[0]=str_replace( "\$keydescription" , "[keydescription]" , $contenu[0] );
		$ref_keywordsEN = $contenu[0] ;
		}
	echo "<tr><td colspan=\"2\"><strong>Anglais</strong></td></tr>";
	echo "<tr><td class=\"td_modules_boutiques\" valign=\"top\">Titre de la fenêtre EN</td><td class=\"td_modules_boutiques\"><textarea name=\"ref_titleEN\" cols=\"50\" rows=\"2\">$ref_titleEN</textarea></td></tr>";
	echo "<tr><td class=\"td_modules_boutiques\" valign=\"top\">META description EN</td><td class=\"td_modules_boutiques\"><textarea name=\"ref_descriptionEN\" cols=\"50\" rows=\"6\">$ref_descriptionEN</textarea></td></tr>";
	echo "<tr><td class=\"td_modules_boutiques\" valign=\"top\">META keywords EN</td><td class=\"td_modules_boutiques\"><textarea name=\"ref_keywordsEN\" cols=\"50\" rows=\"6\">$ref_keywordsEN</textarea>";
	}
echo "</table>
	<br><br>" ;
echo "<input type=\"submit\" value=\"Ajouter\"><input type=\"hidden\" name=\"action\" value=\"10ajouter\"></form>";

echo "				</td>
			</tr>	
		</table>	
     </font>";


include "./footer.php";

//************************************************************************
//**************************** FONCTIONS PHP *****************************
//************************************************************************
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
function redimentionner_image ($nom_fichier, $destination, $largeur, $hauteur){
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
	if ($imagesize[0] > $largeur or $imagesize[1] > $hauteur){
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
	}else{
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
			return ("ereur !!! Le fichier téléchargé excède la taille autorisée par le serveur(>2Mb)");
	}
	elseif ($_FILES[$imagename]['size']>1048576) {
			return ("ereur !!! Le fichier téléchargé excède la taille autorisée:<br>Vous ne pouvez télécharger qu'un fichier dont la taille est inférieur à 200 ko");
	} 
	elseif ($_FILES[$imagename]['error']==3) {
			return ("ereur !!! Le fichier n'a été que partiellement téléchargé.");
	}
	elseif ($_FILES[$imagename]['error']==2) {
			return ("ereur !!! ereur inconnue ...");
	}
	elseif ($_FILES[$imagename]['error']==4) {
			return ("ereur !!! Aucun fichier n'a été téléchargé.");
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