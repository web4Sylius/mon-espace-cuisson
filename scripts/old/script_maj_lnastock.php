<?php
set_time_limit(1200) ; 

$passession = "oui" ;
include_once "/home/arobases/www/mysql.php";
//include "/home/boutique/www/configurations/www.stock-vente.com/config.php";

$DB_site2=new DB_Sql;
$DB_site2->appname='boutique';
$DB_site2->appshortname='boutique';
$DB_site2->database=$DBname;
$DB_site2->server=$db_server;
$DB_site2->user=$DBusername;
$DB_site2->password=$DBpassword;
$DB_site2->connect();
$DBpassword="";


$rayons=$DB_site2->query("select catid, imageFR from categorie order by catid") ;
while ($rayon=$DB_site2->fetch_array($rayons))
	{
	$nom_fic=$rootpath."configurations/$host/images/categories/FR/$rayon[catid].$rayon[imageFR]";
	if ($rayon[imageFR] != "" and file_exists($nom_fic))
		{
		$destination00=$rootpath."configurations/$host/images/categories/FR/description/$rayon[catid].$rayon[imageFR]";
		if (file_exists($destination00))
			{
			unlink($destination00) ;
			}
		redimentionner_image($nom_fic,$destination00,$categ_description_largeur,$categ_description_hauteur);
		$destination0=$rootpath."configurations/$host/images/categories/FR/liste/$rayon[catid].$rayon[imageFR]";
		if (file_exists($destination0))
			{
			unlink($destination0) ;
			}
		redimentionner_image($nom_fic,$destination0,$categ_liste_largeur,$categ_liste_hauteur);
		}
	}
/**/
// SUPPRIMER LES IMAGES EN TROP DANS LES REPERTOIRES DU SERVEUR 
// articles
$folder = "/home/boutique/www/configurations/$host/images/produits";
$folder_article = "/home/boutique/www/configurations/$host/images/produits/article";
$folder_br = "/home/boutique/www/configurations/$host/images/produits/br";
$folder_categorie = "/home/boutique/www/configurations/$host/images/produits/categorie";
$folder_cdc_categ = "/home/boutique/www/configurations/$host/images/produits/cdc_categ";
$folder_cdc_index = "/home/boutique/www/configurations/$host/images/produits/cdc_index";
$folder_nouveautes_categ = "/home/boutique/www/configurations/$host/images/produits/nouveautes_categ";
$folder_nouveautes_index = "/home/boutique/www/configurations/$host/images/produits/nouveautes_index";
$folder_promos_categ = "/home/boutique/www/configurations/$host/images/produits/promos_categ";
$folder_promos_index = "/home/boutique/www/configurations/$host/images/produits/promos_index";
$folder_topventes_categ = "/home/boutique/www/configurations/$host/images/produits/topventes_categ";
$folder_topventes_index = "/home/boutique/www/configurations/$host/images/produits/topventes_index";
$folder_photos = "/home/boutique/www/configurations/$host/images/produits/photos";
$dossier = opendir($folder_cdc_index);
while ($fichier = readdir($dossier))
	{
	if ($fichier != "." && $fichier != ".." && $fichier != "br" && $fichier != "index.htm" && $fichier != "index.php"
	 && $fichier != "article" && $fichier != "categorie" && $fichier != "cdc_categ" && $fichier != "cdc_index" 
	 && $fichier != "nouveautes_categ" && $fichier != "nouveautes_index" && $fichier != "topventes_categ" 
	 && $fichier != "topventes_index" && $fichier != "promos_categ" && $fichier != "promos_index" && $fichier != "photos")	
		{
		if (!ereg("_", $fichier)) // images normales
			{
			list($libelle_image, $extension_image) = explode(".", $fichier);
			$articles_effaces=$DB_site2->query_first("SELECT artid, image FROM article WHERE artid = '$libelle_image' AND image = '$extension_image'");
			if ($articles_effaces[artid] == "") // Si l'article associé à cette image n'existe plus
				{	
				unlink($folder."/".$fichier);
				if (file_exists($folder_article."/".$fichier)) // article
					{
					unlink($folder_article."/".$fichier);
					}
				if (file_exists($folder_br."/".$fichier)) // br
					{
					unlink($folder_br."/".$fichier);
					}
				if (file_exists($folder_categorie."/".$fichier)) // categorie
					{
					unlink($folder_categorie."/".$fichier);
					}
				if (file_exists($folder_cdc_categ."/".$fichier)) // cdc_categ
					{
					unlink($folder_cdc_categ."/".$fichier);
					}
				if (file_exists($folder_cdc_index."/".$fichier)) // cdc_index
					{
					unlink($folder_cdc_index."/".$fichier);
					}
				if (file_exists($folder_nouveautes_categ."/".$fichier)) // nouveautes_categ
					{
					unlink($folder_nouveautes_categ."/".$fichier);
					}
				if (file_exists($folder_nouveautes_index."/".$fichier)) // nouveautes_index
					{
					unlink($folder_nouveautes_index."/".$fichier);
					}
				if (file_exists($folder_promos_categ."/".$fichier)) // promos_categ
					{
					unlink($folder_promos_categ."/".$fichier);
					}
				if (file_exists($folder_promos_index."/".$fichier)) // promos_index
					{
					unlink($folder_promos_index."/".$fichier);
					}
				if (file_exists($folder_topventes_categ."/".$fichier)) // topventes_categ
					{
					unlink($folder_topventes_categ."/".$fichier);
					}
				if (file_exists($folder_topventes_index."/".$fichier)) // topventes_index
					{
					unlink($folder_topventes_index."/".$fichier);
					}
				if (file_exists($folder_photos."/".$fichier)) // topventes_index
					{
					unlink($folder_photos."/".$fichier);
					}
				}
			}
		else // photos supplémentaires
			{
			list($libelle_image, $extension_image) = explode(".", $fichier);
			list($libelle_image1, $libelle_image2) = explode("_", $libelle_image);
			$articles_photos_effacees=$DB_site2->query_first("SELECT articlephotoid, artid, image FROM articlephoto WHERE articlephotoid = '$libelle_image2' AND artid = '$libelle_image1' AND image = '$extension_image'");
			if ($articles_photos_effacees[0] == "") // Si l'article associé à cette image n'existe plus
				{
				unlink($folder."/".$fichier);
				if (file_exists($folder_article."/".$fichier)) // article
					{
					unlink($folder_article."/".$fichier);
					}
				if (file_exists($folder_br."/".$fichier)) // br
					{
					unlink($folder_br."/".$fichier);
					}
				if (file_exists($folder_categorie."/".$fichier)) // categorie
					{
					unlink($folder_categorie."/".$fichier);
					}
				if (file_exists($folder_cdc_categ."/".$fichier)) // cdc_categ
					{
					unlink($folder_cdc_categ."/".$fichier);
					}
				if (file_exists($folder_cdc_index."/".$fichier)) // cdc_index
					{
					unlink($folder_cdc_index."/".$fichier);
					}
				if (file_exists($folder_nouveautes_categ."/".$fichier)) // nouveautes_categ
					{
					unlink($folder_nouveautes_categ."/".$fichier);
					}
				if (file_exists($folder_nouveautes_index."/".$fichier)) // nouveautes_index
					{
					unlink($folder_nouveautes_index."/".$fichier);
					}
				if (file_exists($folder_promos_categ."/".$fichier)) // promos_categ
					{
					unlink($folder_promos_categ."/".$fichier);
					}
				if (file_exists($folder_promos_index."/".$fichier)) // promos_index
					{
					unlink($folder_promos_index."/".$fichier);
					}
				if (file_exists($folder_topventes_categ."/".$fichier)) // topventes_categ
					{
					unlink($folder_topventes_categ."/".$fichier);
					}
				if (file_exists($folder_topventes_index."/".$fichier)) // topventes_index
					{
					unlink($folder_topventes_index."/".$fichier);
					}
				if (file_exists($folder_photos."/".$fichier)) // topventes_index
					{
					unlink($folder_photos."/".$fichier);
					}
				}
			}
		}
	}
/**/

$articles=$DB_site2->query("select artid, image from article order by artid desc") ;
while ($article=$DB_site2->fetch_array($articles))
	{
	$nom_fic="/home/boutique/www/configurations/$host/images/produits/$article[artid].$article[image]";
	if ($article[image] != "" and file_exists($nom_fic))
		{
		$destination00="/home/boutique/www/configurations/$host/images/produits/article/$article[artid].$article[image]";
		if (file_exists($destination00))
			{
			unlink($destination00) ;
			}
		redimentionner_image($nom_fic,$destination00,$article_largeur,$article_hauteur);
		$destination0="/home/boutique/www/configurations/$host/images/produits/br/$article[artid].$article[image]";
		if (file_exists($destination0))
			{
			unlink($destination0) ;
			}
		redimentionner_image($nom_fic,$destination0,$hauteurimages,$largeurimages);
		$destination1="/home/boutique/www/configurations/$host/images/produits/categorie/$article[artid].$article[image]";
		if (file_exists($destination1))
			{
			unlink($destination1) ;
			}
		redimentionner_image($nom_fic,$destination1,$categorie_largeur,$categorie_hauteur);
		$destination2="/home/boutique/www/configurations/$host/images/produits/cdc_categ/$article[artid].$article[image]";
		if (file_exists($destination2))
			{
			unlink($destination2) ;
			}
		redimentionner_image($nom_fic,$destination2,$cdc_categ_largeur,$cdc_categ_hauteur);
		$destination3="/home/boutique/www/configurations/$host/images/produits/cdc_index/$article[artid].$article[image]";
		if (file_exists($destination3))
			{
			unlink($destination3) ;
			}
		redimentionner_image($nom_fic,$destination3,$cdc_index_largeur,$cdc_index_hauteur);
		$destination4="/home/boutique/www/configurations/$host/images/produits/nouveautes_categ/$article[artid].$article[image]";
		if (file_exists($destination4))
			{
			unlink($destination4) ;
			}
		redimentionner_image($nom_fic,$destination4,$nouveautes_categ_largeur,$nouveates_categ_hauteur);
		$destination5="/home/boutique/www/configurations/$host/images/produits/nouveautes_index/$article[artid].$article[image]";
		if (file_exists($destination5))
			{
			unlink($destination5) ;
			}
		redimentionner_image($nom_fic,$destination5,$nouveautes_index_largeur,$nouveates_index_hauteur);
		$destination6="/home/boutique/www/configurations/$host/images/produits/promos_categ/$article[artid].$article[image]";
		if (file_exists($destination6))
			{
			unlink($destination6) ;
			}
		redimentionner_image($nom_fic,$destination6,$promos_categ_largeur,$promos_categ_hauteur);
		$destination7="/home/boutique/www/configurations/$host/images/produits/promos_index/$article[artid].$article[image]";
		if (file_exists($destination7))
			{
			unlink($destination7) ;
			}
		redimentionner_image($nom_fic,$destination7,$promos_index_largeur,$promos_index_hauteur);
		$destination8="/home/boutique/www/configurations/$host/images/produits/topventes_categ/$article[artid].$article[image]";
		if (file_exists($destination8))
			{
			unlink($destination8) ;
			}
		redimentionner_image($nom_fic,$destination8,$topventes_categ_largeur,$topventes_categ_hauteur);
		$destination9="/home/boutique/www/configurations/$host/images/produits/topventes_index/$article[artid].$article[image]";
		if (file_exists($destination9))
			{
			unlink($destination9) ;
			}
		redimentionner_image($nom_fic,$destination9,$topventes_index_largeur,$topventes_index_hauteur);
		if (isset($hauteurphotos) && isset($largeurphotos)) {
			$destination10="/home/boutique/www/configurations/$host/images/produits/photos/$article[artid].$article[image]";
			if (file_exists($destination10))
				{
				unlink($destination10) ;
				}
			redimentionner_image($nom_fic,$destination10,$largeurphotos,$hauteurphotos);
			}
		}
	$photos=$DB_site2->query("select articlephotoid, artid, image from articlephoto where artid = '$article[artid]' order by articlephotoid") ;
	while ($photo=$DB_site2->fetch_array($photos))
		{
		$nom_fic2="/home/boutique/www/configurations/$host/images/produits/$photo[artid]"."_"."$photo[articlephotoid].$photo[image]";
		if (file_exists($nom_fic2))
			{
			$destination00="/home/boutique/www/configurations/$host/images/produits/article/$photo[artid]"."_"."$photo[articlephotoid].$photo[image]";
			if (file_exists($destination00))
				{
				unlink($destination00) ;
				}
			redimentionner_image($nom_fic2,$destination00,$article_largeur,$article_hauteur);
			$destination0="/home/boutique/www/configurations/$host/images/produits/br/$photo[artid]"."_"."$photo[articlephotoid].$photo[image]";
			if (file_exists($destination0))
				{
				unlink($destination0) ;
				}
			redimentionner_image($nom_fic2,$destination0,$hauteurimages,$largeurimages);
			$destination1="/home/boutique/www/configurations/$host/images/produits/categorie/$photo[artid]"."_"."$photo[articlephotoid].$photo[image]";
			if (file_exists($destination1))
				{
				unlink($destination1) ;
				}
			redimentionner_image($nom_fic2,$destination1,$categorie_largeur,$categorie_hauteur);
			$destination2="/home/boutique/www/configurations/$host/images/produits/cdc_categ/$photo[artid]"."_"."$photo[articlephotoid].$photo[image]";
			if (file_exists($destination2))
				{
				unlink($destination2) ;
				}
			redimentionner_image($nom_fic2,$destination2,$cdc_categ_largeur,$cdc_categ_hauteur);
			$destination3="/home/boutique/www/configurations/$host/images/produits/cdc_index/$photo[artid]"."_"."$photo[articlephotoid].$photo[image]";
			if (file_exists($destination3))
				{
				unlink($destination3) ;
				}
			redimentionner_image($nom_fic2,$destination3,$cdc_index_largeur,$cdc_index_hauteur);
			$destination4="/home/boutique/www/configurations/$host/images/produits/nouveautes_categ/$photo[artid]"."_"."$photo[articlephotoid].$photo[image]";
			if (file_exists($destination4))
				{
				unlink($destination4) ;
				}
			redimentionner_image($nom_fic2,$destination4,$nouveautes_categ_largeur,$nouveates_categ_hauteur);
			$destination5="/home/boutique/www/configurations/$host/images/produits/nouveautes_index/$photo[artid]"."_"."$photo[articlephotoid].$photo[image]";
			if (file_exists($destination5))
				{
				unlink($destination5) ;
				}
			redimentionner_image($nom_fic2,$destination5,$nouveautes_index_largeur,$nouveates_index_hauteur);
			$destination6="/home/boutique/www/configurations/$host/images/produits/promos_categ/$photo[artid]"."_"."$photo[articlephotoid].$photo[image]";
			if (file_exists($destination6))
				{
				unlink($destination6) ;
				}
			redimentionner_image($nom_fic2,$destination6,$promos_categ_largeur,$promos_categ_hauteur);
			$destination7="/home/boutique/www/configurations/$host/images/produits/promos_index/$photo[artid]"."_"."$photo[articlephotoid].$photo[image]";
			if (file_exists($destination7))
				{
				unlink($destination7) ;
				}
			redimentionner_image($nom_fic2,$destination7,$promos_index_largeur,$promos_index_hauteur);
			$destination8="/home/boutique/www/configurations/$host/images/produits/topventes_categ/$photo[artid]"."_"."$photo[articlephotoid].$photo[image]";
			if (file_exists($destination8))
				{
				unlink($destination8) ;
				}
			redimentionner_image($nom_fic2,$destination8,$topventes_categ_largeur,$topventes_categ_hauteur);
			$destination9="/home/boutique/www/configurations/$host/images/produits/topventes_index/$photo[artid]"."_"."$photo[articlephotoid].$photo[image]";
			if (file_exists($destination9))
				{
				unlink($destination9) ;
				}
			redimentionner_image($nom_fic2,$destination9,$topventes_index_largeur,$topventes_index_hauteur);
			if (isset($hauteurphotos) && isset($largeurphotos)) {
				$destination10="/home/boutique/www/configurations/$host/images/produits/photos/$photo[artid]"."_"."$photo[articlephotoid].$photo[image]";
				if (file_exists($destination10))
					{
					unlink($destination10) ;
					}
				redimentionner_image($nom_fic2,$destination10,$largeurphotos,$hauteurphotos);
				}
			}
		}
	
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
?>
