<?php
ini_set('memory_limit','600M');
require "admin_global.php";
include "./header.php";
include "./menu.php";
include "./fonctions.php";	

echo "
<td valign=\"top\">
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
   	<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
		<tr>
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Copier un produit</font>
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
				<td align=\"center\" valign=\"middle\">";
				
// fonction arborescence pour mettre des indentations à chaque sous-rayon 
function arborescence($parentid){
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

// si on n'a pas d'article, il y a un pb !!??
if ($artid=="" || !isset($artid)){
	echo "ERREUR !!!";
	include "./footer.php";
	exit;
}

// on récupère les infos sur l'article à copier et la catégorie à laquelle il appartient
$article=$DB_site2->query_first("SELECT * FROM article WHERE artid='$artid' ");
$catid=$article[catid];


//*******************************************************************
//****************AFFICHAGE BARRE NAVIGATION*************************
//*******************************************************************
$navigationdebut="<a href=\"catalogue.php\">".${"title$langue"}."</a> > ";
navigation($catid,1);
echo ($navigationdebut.$navigationbit."Copier le produit '".$article[$libelle]."'<br><br>");

if($action==""){  // si action est vide, on arrive sur la page donc on affiche la liste des categories ou copier l'article
	arborescence(0);
	echo "Copier l'article '".$article[$libelle]."' dans : <br><br>	
		<form action=\"copier_produit.php\" method=\"post\">
		<select name=copcatid size=25>
		<option>........................................................................................................................................</option>
		<option value=\"0\">".${"title$langue"}."</option>
		$select
		</select><br><br>
		<input type=\"hidden\" name=\"artid\" value=\"$artid\">
		<input type=\"hidden\" name=\"action\" value=\"copier\">
		<input type=\"submit\" value=\"Copier\"><br>
		<br><br><a href=catalogue.php>Retour</a>";
}

// sinon, on copie l'article dans la catégorie choisie
if($action=="copier") {
	if ($copcatid != ""){  // si on a selectionné une catégorie dans laquelle copier l'article
		// mise à jour du treeview
		$DB_site2->query("update treeview_temporaire set dateline = dateline - 10000");
		$art=$DB_site2->query_first("select * from article where artid = '$artid'");
		$art_accessoires = $DB_site2->query("select * from article_accessoire where artid='$artid'");
		$art_fourn=$DB_site2->query("SELECT * FROM article_fournisseur WHERE artid='$artid'");		
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
		$hygrometrie = addSlashes($art[hygrometrie]) ;
		$art[garantie_incluse] = addSlashes($art[garantie_incluse]) ;
		$art[artcode]=addSlashes($art[artcode]);
		$copcatid=addSlashes($copcatid);
		$art[prix]=addSlashes($art[prix]);
		$art[active]=addSlashes($art[active]);
		$art[tva]=addSlashes($art[tva]);
		$art[tauxchoisi]=addSlashes($art[tauxchoisi]);
		$art[poid]=addSlashes($art[poid]);
		$art[fraisgestionid]=addSlashes($art[fraisgestionid]);
		$art[image]=addSlashes($art[image]);
		$legende=addSlashes($legende);
		$art[upload_images]=addSlashes($art[upload_images]);
		$art[upload_images_oblig]=addSlashes($art[upload_images_oblig]);
		$art[upload_comm]=addSlashes($art[upload_comm]);
		$art[upload_comm_oblig]=addSlashes($art[upload_comm_oblig]);
		$art[commandable]=addSlashes($art[commandable]);
		$page=addSlashes($page);
		$pageposition=addSlashes($pageposition);
		$art[delailivraison]=addSlashes($art[delailivraison]);
		$art[prixht]=addSlashes($art[prixht]);
		$art[description2FR]=addSlashes($art[description2FR]);
		$art[delailivraison_txt]=addSlashes($art[delailivraison_txt]);
		$art[gamme]=addSlashes($art[gamme]);
		$art[gammeFR]=addSlashes($art[gammeFR]);
		$art[gammeEN]=addSlashes($art[gammeEN]);
		$art[gammeSP]=addSlashes($art[gammeSP]);
		$art[modele]=addSlashes($art[modele]);
		$art[artcode_fabricant]=addSlashes($art[artcode_fabricant]);
		$art[artcode_eurocave]=addSlashes($art[artcode_eurocave]);
		$art[nbr_temperature]=addSlashes($art[nbr_temperature]);
		$art[coloris]=addSlashes($art[coloris]);
		$art[dimensions]=addSlashes($art[dimensions]);
		$art[avis_expert]=addSlashes($art[avis_expert]);
		$art[paht]=addSlashes($art[paht]);
		$art[prixconseilleht]=addSlashes($art[prixconseilleht]);
		$art[ecopart]=addSlashes($art[ecopart]);
		$art[ecopartht]=addSlashes($art[ecopartht]);
		$art[emballage_hauteur1]=addSlashes($art[emballage_hauteur1]);
		$art[emballage_largeur1]=addSlashes($art[emballage_largeur1]);
		$art[emballage_profondeur1]=addSlashes($art[emballage_profondeur1]);
		$art[emballage_poids1]=addSlashes($art[emballage_poids1]);
		$art[emballage_hauteur2]=addSlashes($art[emballage_hauteur2]);
		$art[emballage_largeur2]=addSlashes($art[emballage_largeur2]);
		$art[emballage_profondeur2]=addSlashes($art[emballage_profondeur2]);
		$art[emballage_poids2]=addSlashes($art[emballage_poids2]);
		$art[emballage_hauteur3]=addSlashes($art[emballage_hauteur3]);
		$art[emballage_largeur3]=addSlashes($art[emballage_largeur3]);
		$art[emballage_profondeur3]=addSlashes($art[emballage_profondeur3]);
		$art[emballage_poids3]=addSlashes($art[emballage_poids3]);		
		$art[refs_fabricant]=addslashes($art[refs_fabricant]);
		$art[temp_instal_min]=addslashes($art[temp_instal_min]);
		$art[temp_instal_max]=addslashes($art[temp_instal_max]);
		$art[hauteur_encastrement]=addslashes($art[hauteur_encastrement]);
		$art[largeur_encastrement]=addslashes($art[largeur_encastrement]);
		$art[profondeur_encastrement]=addslashes($art[profondeur_encastrement]);
		$art[infos_encastrement]=addslashes($art[infos_encastrement]);
		$art[typearticleid] = addslashes($art[typearticleid]);
		// article
		if ($art[catid] == $copcatid){
			$msg = "<b>Attention, vous venez de copier l'article dans la même catégorie !</B><br>" ;
			$msg.= "Le nom de l'article sera identique mais il débutera par le numéro de sa copie.<br>" ;
			$libelleFR="(copie) ".$libelleFR;
			// position
			$positions=$DB_site2->query_first("select page, pageposition from article where catid = '$copcatid' order by page desc, pageposition desc limit 1");
			if ($positions[pageposition] < $perpage){
				$page = $positions[page] ;
				$pageposition = $positions[pageposition] + 1 ;
			}else{
				$page = $positions[page] + 1 ;
				$pageposition = 1 ;
			}
			$requete="insert into article
					(artcode,typearticleid, catid, prix, active, pro, tva, tauxchoisi, poid, fraisgestionid, image, legende, libelleFR, descriptionFR,  
					libelleDE, descriptionDE, libelleEN, descriptionEN, Titre2FR, 
					Titre2DE, Titre2EN, upload_images, upload_images_oblig, upload_comm, upload_comm_oblig, commandable, 
					page, pageposition, delailivraison, prixht, prixtrspt, description2FR, delailivraison_txt, gamme, gammeFR, gammeEN, modele, artcode_fabricant,
					artcode_eurocave, coloris, garantie_incluse, dimensions, paht, prixconseilleht, ecopart, ecopartht,hauteur_encastrement,largeur_encastrement, profondeur_encastrement, infos_encastrement, emballage_hauteur1,emballage_hauteur2,emballage_hauteur3, emballage_largeur1,emballage_largeur2,emballage_largeur3, emballage_profondeur1,emballage_profondeur2,emballage_profondeur3, 
					emballage_poids1,emballage_poids2,emballage_poids3,paysfabrication,paysid,douanier,refs_fabricant, facture_diss)
					values
					('$art[artcode]','$art[typearticleid]','$copcatid','$art[prix]', '6', '3', '$art[tva]', '$art[tauxchoisi]', '$art[poid]',
					'$art[fraisgestionid]','$art[image]', '$legende', '$libelleFR', '$descriptionFR', '$libelleDE', 
					'$descriptionDE', '$libelleEN', '$descriptionEN','$titre2FR', '$titre2DE', '$titre2EN', '$art[upload_images]', '$art[upload_images_oblig]', 
					'$art[upload_comm]', '$art[upload_comm_oblig]', '$art[commandable]', '$page', '$pageposition', '$art[delailivraison]', '$art[prixht]', '$art[prixtrspt]',
					'$art[description2FR]', '$art[delailivraison_txt]', '$art[gamme]', '$art[gammeFR]', '$art[gammeEN]', '$art[modele]', '$art[artcode_fabricant]','$art[artcode_eurocave]','$art[coloris]','$art[garantie_incluse]', '$art[dimensions]', '$art[paht]', '$art[prixconseilleht]', '$art[ecopart]', '$art[ecopartht]','$art[hauteur_encastrement]','$art[largeur_encastrement]','$art[profondeur_encastrement]','$art[infos_encastrement]', '$art[emballage_hauteur1]', '$art[emballage_hauteur2]', '$art[emballage_hauteur3]'
					, '$art[emballage_largeur1]', '$art[emballage_largeur2]', '$art[emballage_largeur3]', '$art[emballage_profondeur1]', '$art[emballage_profondeur2]', '$art[emballage_profondeur3]', '$art[emballage_poids1]', '$art[emballage_poids2]', '$art[emballage_poids3]','$art[paysfabrication]','$art[paysid]','$art[douanier]','$art[refs_fabricant]', '$art[facture_diss]')";
			$DB_site2->query($requete);
			$insertid=$DB_site2->insert_id();
			$DB_site2->query("INSERT INTO suivi_article SET artid=$insertid, date=".time().", commercial='".$user_info['username']."', suivi='Création article par duplication $art[artcode]'");
			//article accessoires
			while($art_access = $DB_site2->fetch_array($art_accessoires)){
				$DB_site2->query("INSERT INTO article_accessoire SET artid ='$insertid', acc_artid='$art_access[acc_artid]', libelle='$art_access[libelle]', qte='$art_access[qte]'");
			}
		}else{
			// position
			$positions=$DB_site2->query_first("select page, pageposition from article where catid = '$copcatid' order by page desc, pageposition desc limit 1");
			if ($positions[pageposition] < $perpage){
				$page = $positions[page] ;
				$pageposition = $positions[pageposition] + 1 ;
			}else{
				$page = $positions[page] + 1 ;
				$pageposition = 1 ;
			}
			$requete="insert into article
					(artcode,typearticleid, catid, prix, active, pro, tva, tauxchoisi, poid, fraisgestionid, image, legende, libelleFR, descriptionFR,  
					libelleDE, descriptionDE, libelleEN, descriptionEN, Titre2FR, 
					Titre2DE, Titre2EN, upload_images, upload_images_oblig, upload_comm, upload_comm_oblig, commandable, 
					page, pageposition, delailivraison, prixht, prixtrspt, description2FR, delailivraison_txt, gamme, gammeFR, gammeEN, modele, artcode_fabricant,
					artcode_eurocave, coloris, garantie_incluse, dimensions, paht, prixconseilleht, ecopart, ecopartht,hauteur_encastrement,largeur_encastrement, profondeur_encastrement, infos_encastrement, emballage_hauteur1,emballage_hauteur2,emballage_hauteur3, emballage_largeur1,emballage_largeur2,emballage_largeur3, emballage_profondeur1,emballage_profondeur2,emballage_profondeur3, 
					emballage_poids1,emballage_poids2,emballage_poids3,paysfabrication,paysid,douanier,refs_fabricant, facture_diss)
					values
					('$art[artcode]','$art[typearticleid]','$copcatid','$art[prix]', '6', '3', '$art[tva]', '$art[tauxchoisi]', '$art[poid]',
					'$art[fraisgestionid]','$art[image]', '$legende', '$libelleFR', '$descriptionFR', '$libelleDE', 
					'$descriptionDE', '$libelleEN', '$descriptionEN','$titre2FR', '$titre2DE', '$titre2EN', '$art[upload_images]', '$art[upload_images_oblig]', 
					'$art[upload_comm]', '$art[upload_comm_oblig]', '$art[commandable]', '$page', '$pageposition', '$art[delailivraison]', '$art[prixht]', '$art[prixtrspt]',
					'$art[description2FR]', '$art[delailivraison_txt]', '$art[gamme]', '$art[gammeFR]', '$art[gammeEN]', '$art[modele]', '$art[artcode_fabricant]','$art[artcode_eurocave]','$art[coloris]','$art[garantie_incluse]', '$art[dimensions]', '$art[paht]', '$art[prixconseilleht]', '$art[ecopart]', '$art[ecopartht]','$art[hauteur_encastrement]','$art[largeur_encastrement]','$art[profondeur_encastrement]','$art[infos_encastrement]', '$art[emballage_hauteur1]', '$art[emballage_hauteur2]', '$art[emballage_hauteur3]'
					, '$art[emballage_largeur1]', '$art[emballage_largeur2]', '$art[emballage_largeur3]', '$art[emballage_profondeur1]', '$art[emballage_profondeur2]', '$art[emballage_profondeur3]', '$art[emballage_poids1]', '$art[emballage_poids2]', '$art[emballage_poids3]','$art[paysfabrication]','$art[paysid]','$art[douanier]','$art[refs_fabricant]', '$art[facture_diss]')";
			$DB_site2->query($requete);
			$insertid=$DB_site2->insert_id();
			//copier les caracteristique
			//verifier que la categorie cible integre les meme caracteristique
			$DB_site2->query("INSERT IGNORE INTO categorie_caracteristique (catid, caractid, visible, filtrable) SELECT '$copcatid' as catid, caractid, visible, filtrable FROM categorie_caracteristique WHERE catid = '".$art['catid']."';");
			//copier les caracteristique
			//$DB_site2->query("INSERT INTO article_caractval (artid, caractvalid, differenceprix, differenceprixht) SELECT '$insertid' as artid, caractvalid, differenceprix, differenceprixht FROM article_caractval WHERE artid = '$artid';");
			$DB_site2->query("INSERT INTO suivi_article SET artid=$insertid, date=".time().", commercial='".$user_info['username']."', suivi='Création article par duplication $art[artcode]'");
			//article accessoires
			while($art_access = $DB_site2->fetch_array($art_accessoires)){
				$DB_site2->query("INSERT INTO article_accessoire SET artid ='$insertid', acc_artid='$art_access[acc_artid]', libelle='$art_access[libelle]', qte='$art_access[qte]'");
			}
		}
		// image
		if ($art[image]!=""){
			$source=$rootpath."configurations/$host/images/produits/".$art[artid].".".$art[image];
			$destination=$rootpath."configurations/$host/images/produits/".$insertid.".".$art[image];
			copier_image($source,$destination);
			$nom_fic=$rootpath."configurations/$host/images/produits/".$insertid.".".$art[image];
			$destination0=$rootpath."configurations/$host/images/produits/article/".$insertid.".".$art[image];
			redimentionner_image($nom_fic,$destination0,$article_largeur,$article_hauteur);
			$destination1=$rootpath."configurations/$host/images/produits/categorie/".$insertid.".".$art[image];
			redimentionner_image($nom_fic,$destination1,$categorie_largeur,$categorie_hauteur);
			$destination2=$rootpath."configurations/$host/images/produits/cdc_categ/".$insertid.".".$art[image];
			redimentionner_image($nom_fic,$destination2,$cdc_categ_largeur,$cdc_categ_hauteur);
			$destination3=$rootpath."configurations/$host/images/produits/cdc_index/".$insertid.".".$art[image];
			redimentionner_image($nom_fic,$destination3,$cdc_index_largeur,$cdc_index_hauteur);
			$destination4=$rootpath."configurations/$host/images/produits/nouveautes_categ/".$insertid.".".$art[image];
			redimentionner_image($nom_fic,$destination4,$nouveautes_categ_largeur,$nouveates_categ_hauteur);
			$destination5=$rootpath."configurations/$host/images/produits/nouveautes_index/".$insertid.".".$art[image];
			redimentionner_image($nom_fic,$destination5,$nouveautes_index_largeur,$nouveates_index_hauteur);
			$destination6=$rootpath."configurations/$host/images/produits/promos_categ/".$insertid.".".$art[image];
			redimentionner_image($nom_fic,$destination6,$promos_categ_largeur,$promos_categ_hauteur);
			$destination7=$rootpath."configurations/$host/images/produits/promos_index/".$insertid.".".$art[image];
			redimentionner_image($nom_fic,$destination7,$promos_index_largeur,$promos_index_hauteur);
			$destination8=$rootpath."configurations/$host/images/produits/topventes_categ/".$insertid.".".$art[image];
			redimentionner_image($nom_fic,$destination8,$topventes_categ_largeur,$topventes_categ_hauteur);
			$destination9=$rootpath."configurations/$host/images/produits/topventes_index/".$insertid.".".$art[image];
			redimentionner_image($nom_fic,$destination9,$topventes_index_largeur,$topventes_index_hauteur);
			$destination10=$rootpath."configurations/$host/images/produits/br/".$insertid.".".$art[image];
			redimentionner_image($nom_fic,$destination10,$largeurimages,$hauteurimages);
		}
		// article_marque
		$mq=$DB_site2->query_first("select * from article_marque where artid = '$art[artid]'");
		if ($mq[0] != ""){
			$DB_site2->query("insert into article_marque (artid, marqueid) values ('$insertid', '$mq[marqueid]')");
	    }
		// articlephoto
		$phos=$DB_site2->query("select * from articlephoto where artid = '$art[artid]'");
		if ($DB_site2->num_rows($phos) > 0){
			while ($pho=$DB_site2->fetch_array($phos)){
				$pho[legende] = addSlashes($pho[legende]) ;
				$DB_site2->query("insert into articlephoto (artid, image, legende) values ('$insertid', '$pho[image]', '$pho[legende]')");
				$insertphoto=$DB_site2->insert_id() ;
				$nom_fic2=$rootpath."configurations/$host/images/produits/".$pho[artid]."_".$pho[articlephotoid].".".$pho[image];
				$destination0=$rootpath."configurations/$host/images/produits/".$insertid."_".$insertphoto.".".$pho[image];
				redimentionner_image($nom_fic2,$destination0,'10000','10000');
				$destination0=$rootpath."configurations/$host/images/produits/article/".$insertid."_".$insertphoto.".".$pho[image];
				redimentionner_image($nom_fic2,$destination0,$article_largeur,$article_hauteur);
				$destination1=$rootpath."configurations/$host/images/produits/categorie/".$insertid."_".$insertphoto.".".$pho[image];
				redimentionner_image($nom_fic2,$destination1,$categorie_largeur,$categorie_hauteur);
				$destination2=$rootpath."configurations/$host/images/produits/cdc_categ/".$insertid."_".$insertphoto.".".$pho[image];
				redimentionner_image($nom_fic2,$destination2,$cdc_categ_largeur,$cdc_categ_hauteur);
				$destination3=$rootpath."configurations/$host/images/produits/cdc_index/".$insertid."_".$insertphoto.".".$pho[image];
				redimentionner_image($nom_fic2,$destination3,$cdc_index_largeur,$cdc_index_hauteur);
				$destination4=$rootpath."configurations/$host/images/produits/nouveautes_categ/".$insertid."_".$insertphoto.".".$pho[image];
				redimentionner_image($nom_fic2,$destination4,$nouveautes_categ_largeur,$nouveates_categ_hauteur);
				$destination5=$rootpath."configurations/$host/images/produits/nouveautes_index/".$insertid."_".$insertphoto.".".$pho[image];
				redimentionner_image($nom_fic2,$destination5,$nouveautes_index_largeur,$nouveates_index_hauteur);
				$destination6=$rootpath."configurations/$host/images/produits/promos_categ/".$insertid."_".$insertphoto.".".$pho[image];
				redimentionner_image($nom_fic2,$destination6,$promos_categ_largeur,$promos_categ_hauteur);
				$destination7=$rootpath."configurations/$host/images/produits/promos_index/".$insertid."_".$insertphoto.".".$pho[image];
				redimentionner_image($nom_fic2,$destination7,$promos_index_largeur,$promos_index_hauteur);
				$destination8=$rootpath."configurations/$host/images/produits/topventes_categ/".$insertid."_".$insertphoto.".".$pho[image];
				redimentionner_image($nom_fic2,$destination8,$topventes_categ_largeur,$topventes_categ_hauteur);
				$destination9=$rootpath."configurations/$host/images/produits/topventes_index/".$insertid."_".$insertphoto.".".$pho[image];
				redimentionner_image($nom_fic2,$destination9,$topventes_index_largeur,$topventes_index_hauteur);
				$destination10=$rootpath."configurations/$host/images/produits/br/".$insertid."_".$insertphoto.".".$pho[image];
				redimentionner_image($nom_fic2,$destination10,$largeurimages,$hauteurimages);
			}
		}
		// article_piece : articlepieceid, artid, typepiece, contenu, description
		$pieces=$DB_site2->query("select * from article_piece where artid = '$art[artid]'");
		if ($DB_site2->num_rows($pieces) > 0){
			while ($piece=$DB_site2->fetch_array($pieces)){
				$DB_site2->query("insert into article_piece (artid, typepiece, contenu, description) values ('$insertid', '$piece[typepiece]', '$piece[contenu]', '$piece[description]')");
				$insertpiece=$DB_site2->insert_id() ;
				$source=$rootpath."configurations/$host/images/player/pieces_jointes/".$piece[artid]."_".$piece[articlepieceid].".".$piece[contenu];
				$destination=$rootpath."configurations/$host/images/player/pieces_jointes/".$insertid."_".$insertpiece.".".$piece[contenu];
				copier_image($source,$destination);
			}
		}
		// article_caractval
		$caracts=$DB_site2->query("select * from article_caractval where artid = '$art[artid]'");
		if ($DB_site2->num_rows($caracts) > 0){
			while ($caract=$DB_site2->fetch_array($caracts)){
				$DB_site2->query("insert into article_caractval (artid, caractvalid, differenceprix, differenceprixht,achat) values ('$insertid', '$caract[caractvalid]', '$caract[differenceprix]','$caract[differenceprixht]','$caract[achat]')");
			}
		}
		//article fournisseur	
		while($ln_art_fourn = $DB_site2->fetch_array($art_fourn)){
			$DB_site2->query("INSERT INTO article_fournisseur SET fournisseur_id='$ln_art_fourn[fournisseur_id]', fournisseur='".addslashes($ln_art_fourn[fournisseur])."',principal='$ln_art_fourn[principal]', prix='$ln_art_fourn[prix]',trspt='$ln_art_fourn[trspt]', ecopartht='$ln_art_fourn[ecopartht]',devise='$ln_art_fourn[devise]',artid='$insertid', delai='$ln_art_fourn[delai]',reappro='$ln_art_fourn[reappro]'");
		}	
		$DB_site2->query("INSERT INTO articlerecherche_coloris   (artid, recherchecolorisid) SELECT '$insertid' as artid, recherchecolorisid FROM articlerecherche_coloris WHERE artid = '$artid';");
		$DB_site2->query("INSERT INTO articlerecherche_emplacement  (artid, rechercheemplacementid) SELECT '$insertid' as artid, rechercheemplacementid FROM articlerecherche_emplacement WHERE artid = '$artid';");
		$DB_site2->query("INSERT INTO articlerecherche_moderangement  (artid, recherchemoderangementid) SELECT '$insertid' as artid, recherchemoderangementid FROM articlerecherche_moderangement WHERE artid = '$artid';");
		$DB_site2->query("INSERT INTO articlerecherche_typeporte  (artid, recherchetypeporteid) SELECT '$insertid' as artid, recherchetypeporteid FROM articlerecherche_typeporte WHERE artid = '$artid';");
		$DB_site2->query("INSERT INTO articlerecherche_usage  (artid, rechercheusageid) SELECT '$insertid' as artid, rechercheusageid FROM articlerecherche_usage WHERE artid = '$artid';");
		$DB_site2->query("INSERT INTO articlerecherche_finition  (artid,finitionid) SELECT '$insertid' as artid, finitionid FROM articlerecherche_finition WHERE artid = '$artid';");
		$DB_site2->query("INSERT INTO vcategorie (artid,catid) SELECT '$insertid' AS artid, catid FROM vcategorie WHERE artid='$artid';");
		$r=$DB_site2->query("SELECT * FROM article_conseil WHERE artid=".$artid);
		while($associe=$DB_site2->fetch_array($r)){
			$DB_site2->query("INSERT INTO article_conseil SET artid='$insertid', artid_conseille='".$associe['artid_conseille']."'");
		}
		$r=$DB_site2->query("SELECT * FROM article_rangement WHERE artid=".$artid);
		while($associe=$DB_site2->fetch_array($r)){
			$DB_site2->query("INSERT INTO article_rangement SET artid='$insertid', artid_rangement='".$associe['artid_rangement']."', nombre='".$associe['nombre']."'");
		}
		$r=$DB_site2->query("SELECT * FROM article_lot WHERE lotid=".$artid);
		while($lot=$DB_site2->fetch_array($r)){
			$DB_site2->query("INSERT INTO article_lot SET lotid='$insertid', artid=".$lot['artid'].", qte=".$lot['qte'].", prix=".$lot['prix'].", paht=".$lot['paht'].", caractvalids='".$lot['caractvalids']."'");
		}
		$r=$DB_site2->query("SELECT * FROM article_garantie WHERE artid=".$artid);
		while($gar=$DB_site2->fetch_array($r)){
			$DB_site2->query("INSERT INTO article_garantie SET artid='$insertid', ref='".$gar['ref']."', prix=".$gar['prix']);
		}
		$DB_site2->query("INSERT INTO article_nosconseils (artid,type,path) SELECT '$insertid' as artid, type, path FROM article_nosconseils WHERE artid = '$artid';");		
		$DB_site2->query("INSERT INTO article_site (artid,site_code,prix,prixconseilleht,tauxchoisi,active) SELECT '$insertid' AS artid,site_code,prix,prixconseilleht,tauxchoisi,6 FROM article_site WHERE artid='$artid'");
		$table_spe = $DB_site2->query_first("SELECT bdd_table FROM typearticle WHERE typearticleid=$art[typearticleid]");
		$onglet_spe = $DB_site2->query_first("SELECT * FROM $table_spe[0] WHERE artid=$art[artid]");
		$sql = "INSERT INTO $table_spe[0] SET";
		//var_dump($onglet_spe);
		foreach($onglet_spe as $key => $value){
			if($key == "id"){
				// rien
			}elseif($key=='artid'){
				$sql .= " artid='$insertid'";
			}elseif(!is_numeric($key)){
				$sql .=" ,$key ='$value'";
			}
		}
		$DB_site2->query($sql);
		$msg .= "<bR>Cet article a bien été copié...<br>
				<br><a href=\"catalogue.php?catid=".$copcatid."&all=1\">Retour</a>" ;
		echo $msg ;
	}else{  // si on n'en a pas sélectionné, PB!
		echo "ERREUR !!!";
		include "./footer.php";
		exit;
	}
}




// fonction qui copie l'image de l'article
function copier_image($src,$dest){
	if($OpenLocal= @fopen($src, "rb")){
		$LocalFileContent = fread($OpenLocal, filesize($src));
	} else {
		@fclose($OpenLocal);
		die("<b>Erreur : </b>impossible d'ouvrir le fichier local '".$src.".");
	}
	fclose($OpenLocal);
	if($OpenRemote = fopen($dest, "wb")) {
		if(fwrite($OpenRemote, $LocalFileContent)) {
			fclose($OpenRemote);
		}
	}else {
		fclose($OpenRemote);
		die("<b>Erreur : </b>impossible d'ouvrir le fichier distant '".$dest.".");
	}
	chmod ("$dest",0777);
}


function redimentionner_image ($nom_fichier, $destination, $largeur, $hauteur){
	if (substr ($destination, -3) == "jpg") {
		$imagetmp = imagecreatefromjpeg ($nom_fichier) ;
	}
	if (substr ($destination, -3) == "gif") {
		$imagetmp = imagecreatefromgif ($nom_fichier) ;
	}
	if (substr ($destination, -3) == "png") {
		$imagetmp = imagecreatefrompng ($nom_fichier) ;
	}
	$imagesize = getimagesize ($nom_fichier) ;
	if ($imagesize[0] > $largeur or $imagesize[1] > $hauteur){
		if ($imagesize[0] > $imagesize[1]) {
			$width = $largeur ;
			$height = floor ($largeur * $imagesize[1] / $imagesize[0]) ;
		}else {
			$width = floor($largeur * $imagesize[0] / $imagesize[1]) ;
			$height = $largeur ;
		}
		$image = imagecreatetruecolor ($width, $height) ;
		imagecopyresampled ($image, $imagetmp, 0, 0, 0, 0, $width, $height, $imagesize[0], $imagesize[1]) ;
		if (substr ($destination, -3) == "jpg") {
			imagejpeg ($image, $destination , 80) ;
		}
		if (substr ($destination, -3) == "gif") {
			imagegif ($image, $destination , 80) ;
		}
		if (substr ($destination, -3) == "png") {
			imagepng ($image, $destination , 80) ;
		}
		chmod ($destination, 0777) ;
		imagedestroy ($image) ;
	} else {
		$image = imagecreatetruecolor ($imagesize[0], $imagesize[1]) ;
		imagecopyresampled ($image, $imagetmp, 0, 0, 0, 0, $imagesize[0], $imagesize[1], $imagesize[0], $imagesize[1]) ;
		if (substr ($destination, -3) == "jpg") {
			imagejpeg($image, $destination, 80) ;
		}
		if (substr ($destination, -3) == "gif") {
			imagegif ($image, $destination, 80) ;
		}
		if (substr ($destination, -3) == "png") {
			imagepng ($image, $destination, 80) ;
		}
		chmod ($destination, 0777) ;
		imagedestroy ($image) ;
	}
}

echo "			</td>
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