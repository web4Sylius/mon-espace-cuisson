<?php
set_time_limit(1000) ;
require "admin_global.php";
include "./header.php";
include "./menu.php";

echo "
<td valign=\"top\">
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
   	<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
		<tr>
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Copier un rayon</font>
			</td>
		</tr>
		<tr>
			<td background=\"images2/cadre_r3_c2.gif\" width=\"100%\" height=\"2\">
			</td>
		</tr>
	</table>
   </td>
   <td><img name=\"cadre_r2_c5\" src=\"images2/cadre_r2_c5.gif\" width=\"11\" height=\"27\" border=\"0\" alt=\"\"></td>
   <td background=\"images2/cadre_r2_c6.gif\" width=\"100%\" height=\"27\" align=\"center\" valign=\"middle\">
   </td>
   <td><img name=\"cadre_r2_c8\" src=\"images2/cadre_r2_c8.gif\" width=\"13\" height=\"27\" border=\"0\" alt=\"\"></td>
  </tr>
</table>

</tr></td>
<tr><td>

<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td background=\"images2/cadre_r4_c2.gif\" width=\"3\" height=\"100%\"></td>
   <td align=\"justify\" valign=\"middle\"><br>
   	<font class=\"txt_2\">
		<table align=\"center\">
			<tr>
				<td align=\"center\" valign=\"middle\">" ;
				
// fonction arborescence pour mettre des indentations à chaque sous-rayon 
function arborescence($parentid)
{
	global $DB_site2,$select,$niveau,$libelle,$catid,$libellecat;
	$niveau.="--->";
	$requete_arbo=" select $libelle,catid from categorie where parentid='$parentid' ORDER BY $libelle ";
	$arbos=$DB_site2->query($requete_arbo);
	while ($arbo=$DB_site2->fetch_array($arbos)) {
		if($catid==$arbo[catid]) {
			$selected="selected";
			$libellecat=$arbo[$libelle];
		} else {
			$selected="";
		}
		$select.="<option value=\"$arbo[catid]\" $selected>$niveau $arbo[$libelle]</option>";
		arborescence($arbo[catid]);
	}
	$niveau=substr($niveau , 0 , strlen($niveau)-4 );
	return ($select);
}

// fonction navigation pour savoir dans quel sous-rayon de quel sous-rayon on se trouve !!!
function navigation($catidfunc , $isarticle=0)
{
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

// si on n'a pas de categorie, il y a un pb !!??
if ($catid=="" || !isset($catid))
{
	echo "ERREUR !!!";
	include "./footer.php";
	exit;
}

// on récupère les infos sur le rayon à copier et la catégorie à laquelle il appartient
$categorie=$DB_site2->query_first("SELECT * FROM categorie WHERE catid='$catid' ");
$catid=$categorie[catid];


//*******************************************************************
//****************AFFICHAGE BARRE NAVIGATION*************************
//*******************************************************************
$navigationdebut="<a href=\"catalogue.php\">".${"title$langue"}."</a> > ";
navigation($catid,1);
echo ($navigationdebut.$navigationbit."Copier le rayon '".$categorie[$libelle]."'<br><br>");

if($action=="")  // si action est vide, on arrive sur la page donc on affiche la liste des categories ou copier l'article
{
	arborescence(0);
	echo "Copier le rayon '".$categorie[$libelle]."' dans : <br><br>	
		<form action=\"copier_categorie.php\" method=\"post\">
		<select name=copcatid size=25>
		<option>........................................................................................................................................</option>
		<option value=\"0\">".${"title$langue"}."</option>
		$select
		</select><br><br>
		<input type=\"hidden\" name=\"catid\" value=\"$catid\">
		<input type=\"hidden\" name=\"action\" value=\"copier\">
		<input type=\"submit\" value=\"Copier\"><br>
		<br><br><a href=catalogue.php>Retour</a>";
}

// sinon, on copie l'article dans la catégorie choisie
if($action=="copier") 
	{
	if ($copcatid != "")  // si on a selectionné une catégorie dans laquelle copier l'article
		{
		// mise à jour du treeview
		copier_rayon($catid, $copcatid);
		$msg .= "<bR>Ce rayon a bien été copié...<br><br><a href=catalogue.php>Retour</a>" ;
		echo $msg ;
		}
	else  // si on n'en a pas sélectionné, PB!
		{
		echo "ERREUR !!!";
		include "./footer.php";
		exit;
		}
	}


function copier_rayon ($caton, $parent) {
	// on insère d'abord le rayon
	global $DB_site2, $rootpath, $host, $categ_description_largeur,$categ_description_hauteur,$categ_liste_largeur,$categ_liste_hauteur,
	$article_largeur,$article_hauteur,$categorie_largeur,$categorie_hauteur,
	$cdc_categ_largeur,$cdc_categ_hauteur,$cdc_index_largeur,$cdc_index_hauteur,
	$nouveautes_categ_largeur,$nouveates_categ_hauteur,$nouveautes_index_largeur,
	$nouveates_index_hauteur,$promos_categ_largeur,$promos_categ_hauteur,
	$promos_index_largeur,$promos_index_hauteur,$topventes_categ_largeur,
	$topventes_categ_hauteur,$topventes_index_largeur,$topventes_index_hauteur,$largeurimages,$hauteurimages;
	
	$cat = $DB_site2->query_first("SELECT * FROM categorie WHERE catid = '$caton'");
	
	$parentid = $parent;
	$libelleFR = addSlashes($cat[libelleFR]) ;
	$descriptionFR = addSlashes($cat[descriptionFR]) ;
	$imageFR = $cat[imageFR] ;
	$libelleDE = addSlashes($cat[libelleDE]) ;
	$descriptionDE = addSlashes($cat[descriptionDE]) ;
	$imageDE = $cat[imageDE] ;
	$libelleEN = addSlashes($cat[libelleEN]) ;
	$descriptionEN = addSlashes($cat[descriptionEN]) ;
	$imageEN = $cat[imageEN] ;
	$libelleIT = addSlashes($cat[libelleIT]) ;
	$descriptionIT = addSlashes($cat[descriptionIT]) ;
	$imageIT = $cat[imageIT] ;
	$libelleSP = addSlashes($cat[libelleSP]) ;
	$descriptionSP = addSlashes($cat[descriptionSP]) ;
	$imageSP = $cat[imageSP] ;
	$ref_titleFR = addSlashes($cat[ref_titleFR]) ;
	$ref_titleDE = addSlashes($cat[ref_titleDE]) ;
	$ref_titleEN = addSlashes($cat[ref_titleEN]) ;
	$ref_titleIT = addSlashes($cat[ref_titleIT]) ;
	$ref_titleSP = addSlashes($cat[ref_titleSP]) ;
	$ref_descriptionFR = addSlashes($cat[ref_descriptionFR]) ;
	$ref_descriptionDE = addSlashes($cat[ref_descriptionDE]) ;
	$ref_descriptionEN = addSlashes($cat[ref_descriptionEN]) ;
	$ref_descriptionIT = addSlashes($cat[ref_descriptionIT]) ;
	$ref_descriptionSP = addSlashes($cat[ref_descriptionSP]) ;
	$ref_keywordsFR = addSlashes($cat[ref_keywordsFR]) ;
	$ref_keywordsDE = addSlashes($cat[ref_keywordsDE]) ;
	$ref_keywordsEN = addSlashes($cat[ref_keywordsEN]) ;
	$ref_keywordsIT = addSlashes($cat[ref_keywordsIT]) ;
	$ref_keywordsSP = addSlashes($cat[ref_keywordsSP]) ;
	
	$requete="insert into categorie
			(parentid, libelleFR, descriptionFR, imageFR, libelleDE, descriptionDE, imageDE, libelleEN, descriptionEN, imageEN, 
			 libelleIT, descriptionIT, imageIT, libelleSP, descriptionSP, imageSP, ref_titleFR, ref_titleDE, ref_titleEN, ref_titleIT, ref_titleSP,
			 ref_descriptionFR, ref_descriptionDE, ref_descriptionEN, ref_descriptionIT, ref_descriptionSP, ref_keywordsFR, ref_keywordsDE, ref_keywordsEN, ref_keywordsIT, ref_keywordsSP)
			values
			('$parentid', '$libelleFR', '$descriptionFR', '$imageFR', '$libelleDE', '$descriptionDE', '$imageDE', '$libelleEN', '$descriptionEN', '$imageEN', 
			 '$libelleIT', '$descriptionIT', '$imageIT', '$libelleSP', '$descriptionSP', '$imageSP', '$ref_titleFR', '$ref_titleDE', '$ref_titleEN', '$ref_titleIT', '$ref_titleSP',
			 '$ref_descriptionFR', '$ref_descriptionDE', '$ref_descriptionEN', '$ref_descriptionIT', '$ref_descriptionSP', '$ref_keywordsFR', '$ref_keywordsDE', '$ref_keywordsEN', '$ref_keywordsIT', '$ref_keywordsSP')";
	$DB_site2->query($requete);
	$insertid=$DB_site2->insert_id();
	//copier les caracteristique
	$caracts = $DB_site2->query("SELECT * FROM categorie_caracteristique WHERE catid = '$cat[catid]'");
	while ($caract=$DB_site2->fetch_array($caracts))
		{
		$DB_site2->query("INSERT INTO categorie_caracteristique (catid, caractid, visible, filtrable) VALUES ('$insertid', '$caract[caractid]', $caract[visible], $caract[filtrable])");
		}
	//copier les images	
	if ($imageFR!="")
		{
		$source=$rootpath."configurations/$host/images/categories/FR/".$cat[catid].".".$cat[imageFR];
		$destination=$rootpath."configurations/$host/images/categories/FR/".$insertid.".".$cat[imageFR];
		copier_image($source,$destination);
		$nom_fic=$rootpath."configurations/$host/images/categories/FR/".$insertid.".".$cat[imageFR];
		$destination0=$rootpath."configurations/$host/images/categories/FR/liste/".$insertid.".".$cat[imageFR];
		redimentionner_image($nom_fic,$destination0,$categ_liste_largeur,$categ_liste_hauteur);
		$destination1=$rootpath."configurations/$host/images/categories/FR/description/".$insertid.".".$cat[imageFR];
		redimentionner_image($nom_fic,$destination1,$categ_description_largeur,$categ_description_hauteur);
		$destination2=$rootpath."configurations/$host/images/categories/FR/br/".$insertid.".".$cat[imageFR];
		redimentionner_image($nom_fic,$destination2,$categ_description_largeur,$categ_description_hauteur);
		}
	if ($imageDE!="")
		{
		$source=$rootpath."configurations/$host/images/categories/DE/".$cat[catid].".".$cat[imageDE];
		$destination=$rootpath."configurations/$host/images/categories/DE/".$insertid.".".$cat[imageDE];
		copier_image($source,$destination);
		$nom_fic=$rootpath."configurations/$host/images/categories/DE/".$insertid.".".$cat[imageDE];
		$destination0=$rootpath."configurations/$host/images/categories/DE/liste/".$insertid.".".$cat[imageDE];
		redimentionner_image($nom_fic,$destination0,$categ_liste_largeur,$categ_liste_hauteur);
		$destination1=$rootpath."configurations/$host/images/categories/DE/description/".$insertid.".".$cat[imageDE];
		redimentionner_image($nom_fic,$destination1,$categ_description_largeur,$categ_description_hauteur);
		$destination2=$rootpath."configurations/$host/images/categories/DE/br/".$insertid.".".$cat[imageDE];
		redimentionner_image($nom_fic,$destination2,$categ_description_largeur,$categ_description_hauteur);
		}
	if ($imageEN!="")
		{
		$source=$rootpath."configurations/$host/images/categories/EN/".$cat[catid].".".$cat[imageEN];
		$destination=$rootpath."configurations/$host/images/categories/EN/".$insertid.".".$cat[imageEN];
		copier_image($source,$destination);
		$nom_fic=$rootpath."configurations/$host/images/categories/EN/".$insertid.".".$cat[imageEN];
		$destination0=$rootpath."configurations/$host/images/categories/EN/liste/".$insertid.".".$cat[imageEN];
		redimentionner_image($nom_fic,$destination0,$categ_liste_largeur,$categ_liste_hauteur);
		$destination1=$rootpath."configurations/$host/images/categories/EN/description/".$insertid.".".$cat[imageEN];
		redimentionner_image($nom_fic,$destination1,$categ_description_largeur,$categ_description_hauteur);
		$destination2=$rootpath."configurations/$host/images/categories/EN/br/".$insertid.".".$cat[imageEN];
		redimentionner_image($nom_fic,$destination2,$categ_description_largeur,$categ_description_hauteur);
		}
	if ($imageIT!="")
		{
		$source=$rootpath."configurations/$host/images/categories/IT/".$cat[catid].".".$cat[imageIT];
		$destination=$rootpath."configurations/$host/images/categories/IT/".$insertid.".".$cat[imageIT];
		copier_image($source,$destination);
		$nom_fic=$rootpath."configurations/$host/images/categories/IT/".$insertid.".".$cat[imageIT];
		$destination0=$rootpath."configurations/$host/images/categories/IT/liste/".$insertid.".".$cat[imageIT];
		redimentionner_image($nom_fic,$destination0,$categ_liste_largeur,$categ_liste_hauteur);
		$destination1=$rootpath."configurations/$host/images/categories/IT/description/".$insertid.".".$cat[imageIT];
		redimentionner_image($nom_fic,$destination1,$categ_description_largeur,$categ_description_hauteur);
		$destination2=$rootpath."configurations/$host/images/categories/IT/br/".$insertid.".".$cat[imageIT];
		redimentionner_image($nom_fic,$destination2,$categ_description_largeur,$categ_description_hauteur);
		}
	if ($imageSP!="")
		{
		$source=$rootpath."configurations/$host/images/categories/SP/".$cat[catid].".".$cat[imageSP];
		$destination=$rootpath."configurations/$host/images/categories/SP/".$insertid.".".$cat[imageSP];
		copier_image($source,$destination);
		$nom_fic=$rootpath."configurations/$host/images/categories/SP/".$insertid.".".$cat[imageSP];
		$destination0=$rootpath."configurations/$host/images/categories/SP/liste/".$insertid.".".$cat[imageSP];
		redimentionner_image($nom_fic,$destination0,$categ_liste_largeur,$categ_liste_hauteur);
		$destination1=$rootpath."configurations/$host/images/categories/SP/description/".$insertid.".".$cat[imageSP];
		redimentionner_image($nom_fic,$destination1,$categ_description_largeur,$categ_description_hauteur);
		$destination2=$rootpath."configurations/$host/images/categories/SP/br/".$insertid.".".$cat[imageSP];
		redimentionner_image($nom_fic,$destination2,$categ_description_largeur,$categ_description_hauteur);
		}
	//on copie les articles de ce rayon
	$arts = $DB_site2->query("SELECT * FROM article WHERE catid = '$cat[catid]'");
	while ($art=$DB_site2->fetch_array($arts))
		{
		$libelleFR = addSlashes($art[libelleFR]) ;
		$descriptionFR = addSlashes($art[descriptionFR]) ;
		$titre2FR = addSlashes($art[Titre2FR]) ;
		$libelleDE = addSlashes($art[libelleDE]) ;
		$descriptionDE = addSlashes($art[descriptionDE]) ;
		$titre2DE = addSlashes($art[Titre2DE]) ;
		$libelleEN = addSlashes($art[libelleEN]) ;
		$descriptionEN = addSlashes($art[descriptionEN]) ;
		$titre2EN = addSlashes($art[Titre2EN]) ;
		$libelleIT = addSlashes($art[libelleIT]) ;
		$descriptionIT = addSlashes($art[descriptionIT]) ;
		$titre2IT = addSlashes($art[Titre2IT]) ;
		$libelleSP = addSlashes($art[libelleSP]) ;
		$descriptionSP = addSlashes($art[descriptionSP]) ;
		$titre2SP = addSlashes($art[Titre2SP]) ;
		$legende = addSlashes($art[legende]) ;

		// article
		$requete="insert into article
				(artcode, catid, prix, active, tva, tauxchoisi, poid, fraisgestionid, image, legende, libelleFR, descriptionFR,  
				libelleDE, descriptionDE, libelleEN, descriptionEN,  libelleIT, descriptionIT,  libelleSP, descriptionSP, Titre2FR, 
				Titre2DE, Titre2EN, Titre2IT, Titre2SP, upload_images, upload_images_oblig, upload_comm, upload_comm_oblig, commandable, page, pageposition)
				values
				('$art[artcode]','$insertid','$art[prix]', '$art[active]', '$art[tva]', '$art[tauxchoisi]', '$art[poid]',
				'$art[fraisgestionid]','$art[image]', '$legende', '$libelleFR', '$descriptionFR', '$libelleDE', 
				'$descriptionDE', '$libelleEN', '$descriptionEN', '$libelleIT',	'$descriptionIT', '$libelleSP', '$descriptionSP',
				'$titre2FR', '$titre2DE', '$titre2EN', '$titre2IT', '$titre2SP', '$art[upload_images]', '$art[upload_images_oblig]', 
				'$art[upload_comm]', '$art[upload_comm_oblig]', '$art[commandable]', '$art[page]', '$art[pageposition]')";
		$DB_site2->query($requete);
		$insertid_art=$DB_site2->insert_id();
		//copier les caracteristique
		//copier les caracteristique
		$DB_site2->query("INSERT INTO article_caractval (artid, caractvalid, differenceprix) SELECT '$insertid_art' as artid, caractvalid, differenceprix FROM article_caractval WHERE artid = '$art[artid]';");

		// image
		if ($art[image]!="")
			{
			$source=$rootpath."configurations/$host/images/produits/".$art[artid].".".$art[image];
			$destination=$rootpath."configurations/$host/images/produits/".$insertid_art.".".$art[image];
			copier_image($source,$destination);
			$nom_fic=$rootpath."configurations/$host/images/produits/".$insertid_art.".".$art[image];
			$destination0=$rootpath."configurations/$host/images/produits/article/".$insertid_art.".".$art[image];
			redimentionner_image($nom_fic,$destination0,$article_largeur,$article_hauteur);
			$destination1=$rootpath."configurations/$host/images/produits/categorie/".$insertid_art.".".$art[image];
			redimentionner_image($nom_fic,$destination1,$categorie_largeur,$categorie_hauteur);
			$destination2=$rootpath."configurations/$host/images/produits/cdc_categ/".$insertid_art.".".$art[image];
			redimentionner_image($nom_fic,$destination2,$cdc_categ_largeur,$cdc_categ_hauteur);
			$destination3=$rootpath."configurations/$host/images/produits/cdc_index/".$insertid_art.".".$art[image];
			redimentionner_image($nom_fic,$destination3,$cdc_index_largeur,$cdc_index_hauteur);
			$destination4=$rootpath."configurations/$host/images/produits/nouveautes_categ/".$insertid_art.".".$art[image];
			redimentionner_image($nom_fic,$destination4,$nouveautes_categ_largeur,$nouveates_categ_hauteur);
			$destination5=$rootpath."configurations/$host/images/produits/nouveautes_index/".$insertid_art.".".$art[image];
			redimentionner_image($nom_fic,$destination5,$nouveautes_index_largeur,$nouveates_index_hauteur);
			$destination6=$rootpath."configurations/$host/images/produits/promos_categ/".$insertid_art.".".$art[image];
			redimentionner_image($nom_fic,$destination6,$promos_categ_largeur,$promos_categ_hauteur);
			$destination7=$rootpath."configurations/$host/images/produits/promos_index/".$insertid_art.".".$art[image];
			redimentionner_image($nom_fic,$destination7,$promos_index_largeur,$promos_index_hauteur);
			$destination8=$rootpath."configurations/$host/images/produits/topventes_categ/".$insertid.".".$art[image];
			redimentionner_image($nom_fic,$destination8,$topventes_categ_largeur,$topventes_categ_hauteur);
			$destination9=$rootpath."configurations/$host/images/produits/topventes_index/".$insertid_art.".".$art[image];
			redimentionner_image($nom_fic,$destination9,$topventes_index_largeur,$topventes_index_hauteur);
			$destination10=$rootpath."configurations/$host/images/produits/br/".$insertid_art.".".$art[image];
			redimentionner_image($nom_fic,$destination10,$largeurimages,$hauteurimages);
			}

		// article_marque
		$mq=$DB_site2->query_first("select * from article_marque where artid = '$art[artid]'");
		if ($mq[0] != "")
			{
			$DB_site2->query("insert into article_marque (artid, marqueid) values ('$insertid_art', '$mq[marqueid]')");
			}
	   
		// articlephoto
		$phos=$DB_site2->query("select * from articlephoto where artid = '$art[artid]'");
		if ($DB_site2->num_rows($phos) > 0)
			{
			while ($pho=$DB_site2->fetch_array($phos))
				{
				$pho[legende] = addSlashes($pho[legende]) ;
				$DB_site2->query("insert into articlephoto (artid, image, legende) values ('$insertid_art', '$pho[image]', '$pho[legende]')");
				$insertphoto=$DB_site2->insert_id() ;
				$nom_fic2=$rootpath."configurations/$host/images/produits/".$pho[artid]."_".$pho[articlephotoid].".".$pho[image];
				$destination0=$rootpath."configurations/$host/images/produits/".$insertid_art."_".$insertphoto.".".$pho[image];
				redimentionner_image($nom_fic2,$destination0,'10000','10000');
				$destination0=$rootpath."configurations/$host/images/produits/article/".$insertid_art."_".$insertphoto.".".$pho[image];
				redimentionner_image($nom_fic2,$destination0,$article_largeur,$article_hauteur);
				$destination1=$rootpath."configurations/$host/images/produits/categorie/".$insertid_art."_".$insertphoto.".".$pho[image];
				redimentionner_image($nom_fic2,$destination1,$categorie_largeur,$categorie_hauteur);
				$destination2=$rootpath."configurations/$host/images/produits/cdc_categ/".$insertid_art."_".$insertphoto.".".$pho[image];
				redimentionner_image($nom_fic2,$destination2,$cdc_categ_largeur,$cdc_categ_hauteur);
				$destination3=$rootpath."configurations/$host/images/produits/cdc_index/".$insertid_art."_".$insertphoto.".".$pho[image];
				redimentionner_image($nom_fic2,$destination3,$cdc_index_largeur,$cdc_index_hauteur);
				$destination4=$rootpath."configurations/$host/images/produits/nouveautes_categ/".$insertid_art."_".$insertphoto.".".$pho[image];
				redimentionner_image($nom_fic2,$destination4,$nouveautes_categ_largeur,$nouveates_categ_hauteur);
				$destination5=$rootpath."configurations/$host/images/produits/nouveautes_index/".$insertid_art."_".$insertphoto.".".$pho[image];
				redimentionner_image($nom_fic2,$destination5,$nouveautes_index_largeur,$nouveates_index_hauteur);
				$destination6=$rootpath."configurations/$host/images/produits/promos_categ/".$insertid_art."_".$insertphoto.".".$pho[image];
				redimentionner_image($nom_fic2,$destination6,$promos_categ_largeur,$promos_categ_hauteur);
				$destination7=$rootpath."configurations/$host/images/produits/promos_index/".$insertid_art."_".$insertphoto.".".$pho[image];
				redimentionner_image($nom_fic2,$destination7,$promos_index_largeur,$promos_index_hauteur);
				$destination8=$rootpath."configurations/$host/images/produits/topventes_categ/".$insertid_art."_".$insertphoto.".".$pho[image];
				redimentionner_image($nom_fic2,$destination8,$topventes_categ_largeur,$topventes_categ_hauteur);
				$destination9=$rootpath."configurations/$host/images/produits/topventes_index/".$insertid_art."_".$insertphoto.".".$pho[image];
				redimentionner_image($nom_fic2,$destination9,$topventes_index_largeur,$topventes_index_hauteur);
				$destination10=$rootpath."configurations/$host/images/produits/br/".$insertid_art."_".$insertphoto.".".$pho[image];
				redimentionner_image($nom_fic2,$destination10,$largeurimages,$hauteurimages);
				}
			}
	
		// article_piece : articlepieceid, artid, typepiece, contenu, description
		$pieces=$DB_site2->query("select * from article_piece where artid = '$art[artid]'");
		if ($DB_site2->num_rows($pieces) > 0)
			{
			while ($piece=$DB_site2->fetch_array($pieces))
				{
				$DB_site2->query("insert into article_piece (artid, typepiece, contenu, description) values ('$insertid_art', '$piece[typepiece]', '$piece[contenu]', '$piece[description]')");
				$insertpiece=$DB_site2->insert_id() ;
				$source=$rootpath."configurations/$host/images/player/pieces_jointes/".$piece[artid]."_".$piece[articlepieceid].".".$piece[contenu];
				$destination=$rootpath."configurations/$host/images/player/pieces_jointes/".$insertid_art."_".$insertpiece.".".$piece[contenu];
				copier_image($source,$destination);
				}
			}

		// article_caractval
		$caracts=$DB_site2->query("select * from article_caractval where artid = '$art[artid]'");
		if ($DB_site2->num_rows($caracts) > 0)
			{
			while ($caract=$DB_site2->fetch_array($caracts))
				{
				$DB_site2->query("insert into article_caractval (artid, caractvalid, differenceprix) values ('$insertid_art', '$caract[caractvalid]', '$caract[differenceprix]')");
				}
			}
/*
		// stock : artid, nombre, seuil, delaiappro
		if(in_array("4", $modules)) {
			$stock=$DB_site2->query_first("select * from stock where artid = '$art[artid]'");
			$DB_site2->query("insert into stock (artid, nombre, seuil, delaiappro) values ('$insertid_art', '$stock[nombre]', '$stock[seuil]', '$stock[delaiappro]')");
		}*/
		// coupdecoeur
		$cdc=$DB_site2->query_first("select * from coupdecoeur where artid = '$art[artid]'");
		if ($cdc[0] != "")
			{
			$pos=$DB_site2->query_first("select max(position) from coupdecoeur");
			$DB_site2->query("insert into coupdecoeur (artid, position) values ('$insertid_art', '$pos[0]')");
			}
		
		// promotion
		$promos=$DB_site2->query("select * from promotion where artid = '$art[artid]'");
		if ($DB_site2->num_rows($promos) > 0)
			{
			while ($promo=$DB_site2->fetch_array($promos))
				{
				$DB_site2->query("insert into promotion (artid, prix, datedebut, datefin) values ('$insertid_art', '$promo[prix]', '$promo[datedebut]', '$promo[datefin]')");
				}
			}
		}
	//on copie les sous-rayons
	$sscats = $DB_site2->query("SELECT * FROM categorie WHERE parentid = '$cat[catid]'");
	while ($sscat=$DB_site2->fetch_array($sscats))
		{
		copier_rayon ($sscat[catid], $insertid);
		}	
}

// fonction qui copie l'image de l'article
function copier_image($src,$dest){
	if($OpenLocal= fopen($src, "rb")) 
		{
		$LocalFileContent = fread($OpenLocal, filesize($src));
		} 
	else 
		{
		fclose($OpenLocal);
		die("<b>Erreur : </b>impossible d'ouvrir le fichier local '".$src.".");
		}
	fclose($OpenLocal);

	if($OpenRemote = fopen($dest, "wb")) 
		{
		if(fwrite($OpenRemote, $LocalFileContent)) 
			{
			fclose($OpenRemote);
			}
		} 
		else 
		{
		fclose($OpenRemote);
		die("<b>Erreur : </b>impossible d'ouvrir le fichier distant '".$dest.".");
		}
		chmod ("$dest",0777);
		}


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
		if ($imagesize[0] > $imagesize[1]) 
			{
			$width = $largeur ;
			$height = floor ($largeur * $imagesize[1] / $imagesize[0]) ;
			} 
		else 
			{
			$width = floor($largeur * $imagesize[0] / $imagesize[1]) ;
			$height = $largeur ;
			}
		$image = imagecreatetruecolor ($width, $height) ;
		imagecopyresampled ($image, $imagetmp, 0, 0, 0, 0, $width, $height, $imagesize[0], $imagesize[1]) ;
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

echo "				</td>
			</tr>	
		</table>	
     </font>
	</td>
   <td background=\"images2/cadre_r4_c10.gif\" width=\"3\" height=\"100%\" border=\"0\" alt=\"\"></td>
  </tr>
</table>
</td>
</tr>
<tr><td>

<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td rowspan=\"2\"><img name=\"cadre_r6_c2\" src=\"images2/cadre_r6_c2.gif\" width=\"10\" height=\"26\" border=\"0\" alt=\"\"></td>
   <td align=\"center\" height=\"23\" width=\"100%\"></td>
   <td rowspan=\"2\"><img name=\"cadre_r6_c9\" src=\"images2/cadre_r6_c9.gif\" width=\"8\" height=\"26\" border=\"0\" alt=\"\"></td>
  </tr>
  <tr>
   <td background=\"images2/cadre_r7_c4.gif\" width=\"100%\" height=\"3\"></td>
  </tr>
</table>
</td></tr></table>
</td>
</tr><tr>
</tr></table>
				  </td>
              </tr>
            </table>
		  </td>
        </tr>
";


include "./footer.php";
?>