<?php
set_time_limit(600) ;

require "admin_global.php";
include "./header.php";
include "./menu.php";

echo "<h1>mise à jour catalogue</h1>" ;
echo "format du fichier : Référence;Designation;Famille;PPIHT 06.2022;PPITTC 06.2022;PA Revendeur 06.2022;Code Eco Part;Eco part HT;Eco part TTC;Dimension Colis;Poids Brut;Volume<br>";


$marqueid=62; // barazza
$fournisseur_libelle="INTERBAT";
$remise=0.6;

// Référence;Designation;Famille;PPIHT 06.2022;PPITTC 06.2022;PA Revendeur 06.2022;Code Eco Part;Eco part HT;Eco part TTC;Dimension Colis;Poids Brut;Volume 



$refs_available=array(
				"1LVE6I",
				"1LVA6I",
				"1FCA6I",
				"1FCE6I",
				"1ABEVG",
				"1ABEVS",
				"1ABEVEN",
				"1MOEN",
				"1MOI",
				"1MOIV",
				"1FEVGMC",
				"1FEVMMC",
				"1FEVSMC",
				"1FEVTMC",
				"1FEVEMCN",
				"1FEVGVC",
				"1FEVEVCN",
				"1FEVSVC",
				"1FEVGP",
				"1FEVSP",
				"1FEVMP",
				"1FEVTP",
				"1FEVEPN",
				"1CEEVG",
				"1CSEVS",
				"1CSEVG",
				"1CFEVG",
				"1CFEVS",
				"1CFEVEN",
				"1PKM90N",
				"1PMD64",
				"1PMD75",
				"1PMD95",
				"1PUN105",
				"1PKS90N",
				"1PKC80N",
				"1PKZP9N",
				"1PZP9N",
				"1PIDS90N",
				"1PLBC9IDN",
				"1KUNI91",
				"1KMDI9",
				"1KMDI12",
				"1KMDP9",
				"1KBDD9",
				"1KBDD12",
				"1C190",
				"1CI120",
				"1CCA",
				"1CI120N",
				"1CCBN",
				"1CI150",
				"1CI90N",
				"1CBL",
				"1CBLN",
				"1CSP",
				"1CIA180",
				"1CPP",
				"1CPTN",
				"1CPB",
				"1CPPN",
				"1CSPN",
				"1CSTN",
				"1RUBMLBI",
				"1RUBMLBIC",
				"1RUBMBOA",
				"1RUBMBFA",
				"1RUBMSGC",
				"1RUBCY",
				"1RUBMFRK",
				"1LLV100/D",
				"1LLV100/S",
				"1LES91R",
				"1LES12RD",
				"1LB081",
				"1LB08K",
				"1LFS82A",
				"1LFS12D",
				"1LCY91N",
				"1LCY91NK",
				"1LCY91G",
				"1PTF1",
				"1PTF2",
				"1PTFID",
				"1PTFTK",
				"1LLB616",
				"1LLB916",
				"1LLB606",
				"1QR406",
				"1RUB0F316",
				"1RUBMD1",
				"1RUBMDT",
				"1RUBMRKT",
				"1C306");

$colonnes=array("Référence",
				"Designation",
				"Famille",
				"PPIHT",
				"PPITTC",
				"PAHT",
				"Code Eco Part",
				"Eco part HT",
				"Eco part TTC",
				"Dimension Colis",
				"Poids Brut",
				"Volume");


$tryartcode="1FEVEVCN";

if ($action == "maj") {
	$nom_fic="./csv/produits.csv";
	$row = 1;
	$inc=100;
	if (($handle = fopen($nom_fic, "r")) !== FALSE) {
		while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
			$num = count($data);
			// echo "<p> $num fields in line $row: <br /></p>\n";
			if($row>1){
				//for ($c=0; $c < $num; $c++) {
				//	echo $data[$c] . "<br />\n";
				//}
				// $marqueid=$data[0];
				$ref=$data[0];
				// echo "est ce que l'article '".$ref."' existe déjà ?<br>";
				
				$artcode = $data[0];
				$libelleFR = $data[1];
				$libelleFR = addslashes($libelleFR) ;
				$descriptionFR = $libelleFR ;
				
				$dimentions = $data[9];
				$explode=explode("x",$dimentions);
				$hauteur = str_replace(",",".",$explode[0])*10 ;
				$largeur = str_replace(",",".",$explode[1])*10 ;
				$profondeur = str_replace(",",".",$explode[2])*10 ;
				$poid =  str_replace(",",".",$data[10]);
				$poid = $poid * 1000; // on convertit en grammes
				
				// prix
				$paht = str_replace(",",".",$data[5]);
				$prix = str_replace(",",".",$data[4]);
				$prixht = str_replace(",",".",$data[3]);
				$ecopartht = str_replace(",",".",$data[7]);
				$ecopart = str_replace(",",".",$data[8]);
				
				if($artcode==$tryartcode){
					echo implode(";",$data);
					echo "<br>";
				}
				
				$article=$DB_site2->query_first("	SELECT article.artid FROM article 
													INNER JOIN article_marque ON (article.artid=article_marque.artid)
													WHERE article_marque.marqueid=".$marqueid."
													AND artcode_eurocave='".$ref."' ");
				if($article['artid']!=""){
					echo "<b>article trouvé => on le met à jour ...</b><br>";
					$sql = "UPDATE article SET prix = '".$prix."', prixconseilleht = '".$prixht."', prixht = '".$prixht."', paht='".$paht."' WHERE artid = '".$article['artid']."' ";
					
					if($artcode==$tryartcode){
						echo $sql."<br>";
						$DB_site2->query($sql);
					}
				} elseif( in_array($ref, $refs_available) ) {
					$inc++;
					echo "article '".$ref."' non trouvé => on le crée ...<br>";
					//if($artcode==$tryartcode){
						$sql="INSERT INTO article
							(catid, artcode, libelleFR, artcode_eurocave, descriptionFR,
							hauteur, largeur, profondeur, poid, active, pro, refs_fabricant, image,
							emballage_hauteur1, emballage_largeur1, emballage_profondeur1, emballage_poids1, typearticleid,
							paht, prix, prixconseilleht, prixht, ecopartht, ecopart, delailivraison) VALUES
							('517', 'EC-BAR".$inc."', '".$libelleFR."', '".$artcode."', '".$descriptionFR."',
							'".$hauteur."', '".$largeur."', '".$profondeur."', '".$poid."', '6', '3', '".$artcode."', '',
							'".$hauteur."', '".$largeur."', '".$profondeur."', '".$poid."', '1',
							'".$paht."', '".$prix."', '".$prixht."', '".$prixht."', '".$ecopartht."', '".$ecopart."', '30')";
						
						echo $sql;
						$DB_site2->query($sql);
						$artid=$DB_site2->insert_id();
						
						$sql="INSERT IGNORE INTO article_marque (artid, marqueid) VALUES ('".$artid."', '".$marqueid."')";
						echo $sql;
						$DB_site2->query($sql);
						
						$sql= "INSERT INTO article_fournisseur 
								(fournisseur_id, fournisseur, principal, prix, trspt, ecopart, devise, artid, delai, reappro)
								VALUES
								('0','".$fournisseur_libelle."', 'base', '".$paht."', '0', '".$ecopartht."', 'EUR', '".$artid."', '15', '0') ";
						echo $sql;
						$DB_site2->query($sql);
						
						
						$sql="INSERT INTO article_site (`artid`, `site_code`, `prix`, `prixconseilleht`, `tauxchoisi`, `active`) VALUES ('".$artid."', 'base', '".$prix."', '".$prixht."', '1', '6');";
						echo $sql;
						$DB_site2->query($sql);
						
						$DB_site2->query("INSERT INTO suivi_article SET artid=".$artid.", date=".time().", commercial='benoit', suivi='Création article par import informatique' ");
						
						echo "<br>";
						echo "<br>";
						
				//	}
					
					

					
				} else {
					// echo "article '".$ref."' pas dispo ... on fait rien<br>";
					
				}
			}
			$row++;
		}
		fclose($handle);
	}
}


if ($action == "maj_photos") {
	$articles=$DB_site2->query("	SELECT article.* FROM article 
									INNER JOIN article_marque ON (article.artid=article_marque.artid)
									WHERE article_marque.marqueid=".$marqueid." ");
	while($article=$DB_site2->fetch_array($articles)){
		$artcode=$article[artcode_eurocave];
		$artid=$article[artid];
		$type_fichier="jpg";
		$dossier_import = "./csv/img/";
		$chemin=$dossier_import.$artcode.".jpg";
		echo $chemin;
		if(file_exists($chemin)){
			echo "on ajoute le fichier ".$dossier_import.$artcode.".".$type_fichier;
			$nom_fic=$rootpath."configurations/$host/images/produits/$artid.$type_fichier";
			copier_image($nom_fic,$chemin);

			$destination0=$rootpath."configurations/$host/images/produits/article/$artid.$type_fichier";
			redimentionner_image($nom_fic,$destination0,$article_largeur,$article_hauteur);
			$destination1=$rootpath."configurations/$host/images/produits/categorie/$artid.$type_fichier";
			redimentionner_image($nom_fic,$destination1,$categorie_largeur,$categorie_hauteur);
			$destination2=$rootpath."configurations/$host/images/produits/cdc_categ/$artid.$type_fichier";
			redimentionner_image($nom_fic,$destination2,$cdc_categ_largeur,$cdc_categ_hauteur);
			$destination3=$rootpath."configurations/$host/images/produits/cdc_index/$artid.$type_fichier";
			redimentionner_image($nom_fic,$destination3,$cdc_index_largeur,$cdc_index_hauteur);
			$destination4=$rootpath."configurations/$host/images/produits/nouveautes_categ/$artid.$type_fichier";
			redimentionner_image($nom_fic,$destination4,$nouveautes_categ_largeur,$nouveates_categ_hauteur);
			$destination5=$rootpath."configurations/$host/images/produits/nouveautes_index/$artid.$type_fichier";
			redimentionner_image($nom_fic,$destination5,$nouveautes_index_largeur,$nouveates_index_hauteur);
			$destination6=$rootpath."configurations/$host/images/produits/promos_categ/$artid.$type_fichier";
			redimentionner_image($nom_fic,$destination6,$promos_categ_largeur,$promos_categ_hauteur);
			$destination7=$rootpath."configurations/$host/images/produits/promos_index/$artid.$type_fichier";
			redimentionner_image($nom_fic,$destination7,$promos_index_largeur,$promos_index_hauteur);
			$destination8=$rootpath."configurations/$host/images/produits/topventes_categ/$artid.$type_fichier";
			redimentionner_image($nom_fic,$destination8,$topventes_categ_largeur,$topventes_categ_hauteur);
			$destination9=$rootpath."configurations/$host/images/produits/topventes_index/$artid.$type_fichier";
			redimentionner_image($nom_fic,$destination9,$topventes_index_largeur,$topventes_index_hauteur);
			$destination10=$rootpath."configurations/$host/images/produits/br/$artid.$type_fichier";
			redimentionner_image($nom_fic,$destination10,$largeurimages,$hauteurimages);
			$DB_site2->query("UPDATE article SET image = '$type_fichier' WHERE artid = '$artid'");
		}
	}
}

echo "<br><br>
		<a href=\"benoit_import_produits.php?action=maj\">Ajouter les produits</a><br>
		<a href=\"benoit_import_produits.php?action=maj_photos\">mettre à jour les photos</a>
	<br>"; 
include "./footer.php";



// fonction php images
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


function copier_image($nom_fic,$imagename)
	{
	copy ($imagename, $nom_fic);
	chmod ("$nom_fic",0777);
	}

?>