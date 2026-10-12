<?php
set_time_limit(1200) ; 

$passession = "oui" ;
//include "/home/subtil/www/configurations/www.subtil-diamant.com/config.php";
include "/home/boutique/www/configurations/www.magie-dorient.fr/config.php";

include_once $rootpath."mysql.php";
$DB_site2=new DB_Sql;
$DB_site2->appname='boutique';
$DB_site2->appshortname='boutique';
$DB_site2->database=$DBname;
$DB_site2->server=$db_server;
$DB_site2->user=$DBusername;
$DB_site2->password=$DBpassword;
$DB_site2->connect();
$DBpassword="";

// SUPPRIMER LES IMAGES EN TROP DANS LES REPERTOIRES DU SERVEUR 
// articles
$folder = $rootpath."configurations/$host/images/produits";
$folder_article = $rootpath."configurations/$host/images/produits/article";
$folder_br = $rootpath."configurations/$host/images/produits/br";
$folder_categorie = $rootpath."configurations/$host/images/produits/categorie";
$folder_cdc_categ = $rootpath."configurations/$host/images/produits/cdc_categ";
$folder_cdc_index = $rootpath."configurations/$host/images/produits/cdc_index";
$folder_nouveautes_categ = $rootpath."configurations/$host/images/produits/nouveautes_categ";
$folder_nouveautes_index = $rootpath."configurations/$host/images/produits/nouveautes_index";
$folder_promos_categ = $rootpath."configurations/$host/images/produits/promos_categ";
$folder_promos_index = $rootpath."configurations/$host/images/produits/promos_index";
$folder_topventes_categ = $rootpath."configurations/$host/images/produits/topventes_categ";
$folder_topventes_index = $rootpath."configurations/$host/images/produits/topventes_index";
$dossier = opendir($folder);
while ($fichier = readdir($dossier))
	{
	if ($fichier != "." && $fichier != ".." && $fichier != "109-109" && $fichier != "article" && $fichier != "br" && $fichier != "categorie" && 
	$fichier != "cdc_categ" && $fichier != "cdc_index" && $fichier != "nouveautes_categ" && $fichier != "nouveautes_index" && $fichier != "topventes_categ" 
	 && $fichier != "topventes_index" && $fichier != "promos_categ" && $fichier != "promos_index")	
		{
		if (!ereg("_", $fichier)) // images normales
			{
			list($libelle_image, $extension_image) = explode(".", $fichier);
			$articles_effaces=$DB_site2->query_first("SELECT artid, image FROM article WHERE artid = '$libelle_image' AND image = '$extension_image'");
			if ($articles_effaces[artid] == "") // Si l'article associé à cette image n'existe plus
				{	
				unlink($folder."/".$fichier);
				if (file_exists($folder_article."/".$fichier)) // article
					unlink($folder_article."/".$fichier);
				if (file_exists($folder_br."/".$fichier)) // br
					unlink($folder_br."/".$fichier);
				if (file_exists($folder_categorie."/".$fichier)) // categorie
					unlink($folder_categorie."/".$fichier);
				if (file_exists($folder_cdc_categ."/".$fichier)) // cdc_categ
					unlink($folder_cdc_categ."/".$fichier);
				if (file_exists($folder_cdc_index."/".$fichier)) // cdc_index
					unlink($folder_cdc_index."/".$fichier);
				if (file_exists($folder_nouveautes_categ."/".$fichier)) // nouveautes_categ
					unlink($folder_nouveautes_categ."/".$fichier);
				if (file_exists($folder_nouveautes_index."/".$fichier)) // nouveautes_index
					unlink($folder_nouveautes_index."/".$fichier);
				if (file_exists($folder_promos_categ."/".$fichier)) // promos_categ
					unlink($folder_promos_categ."/".$fichier);
				if (file_exists($folder_promos_index."/".$fichier)) // promos_index
					unlink($folder_promos_index."/".$fichier);
				if (file_exists($folder_topventes_categ."/".$fichier)) // topventes_categ
					unlink($folder_topventes_categ."/".$fichier);
				if (file_exists($folder_topventes_index."/".$fichier)) // topventes_index
					unlink($folder_topventes_index."/".$fichier);
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
					unlink($folder_article."/".$fichier);
				if (file_exists($folder_br."/".$fichier)) // br
					unlink($folder_br."/".$fichier);
				if (file_exists($folder_categorie."/".$fichier)) // categorie
					unlink($folder_categorie."/".$fichier);
				if (file_exists($folder_cdc_categ."/".$fichier)) // cdc_categ
					unlink($folder_cdc_categ."/".$fichier);
				if (file_exists($folder_cdc_index."/".$fichier)) // cdc_index
					unlink($folder_cdc_index."/".$fichier);
				if (file_exists($folder_nouveautes_categ."/".$fichier)) // nouveautes_categ
					unlink($folder_nouveautes_categ."/".$fichier);
				if (file_exists($folder_nouveautes_index."/".$fichier)) // nouveautes_index
					unlink($folder_nouveautes_index."/".$fichier);
				if (file_exists($folder_promos_categ."/".$fichier)) // promos_categ
					unlink($folder_promos_categ."/".$fichier);
				if (file_exists($folder_promos_index."/".$fichier)) // promos_index
					unlink($folder_promos_index."/".$fichier);
				if (file_exists($folder_topventes_categ."/".$fichier)) // topventes_categ
					unlink($folder_topventes_categ."/".$fichier);
				if (file_exists($folder_topventes_index."/".$fichier)) // topventes_index
					unlink($folder_topventes_index."/".$fichier);
				}
			}
		}
	}
	
?>