<?php
set_time_limit(1200) ; 

include "/home/arobases/www/config.php";
include_once "/home/arobases/www/mysql.php";
$DB_site=new DB_Sql;
$DB_site->appname='arobases';
$DB_site->appshortname='arobases';
$DB_site->database=$dbname;
$DB_site->server=$servername;
$DB_site->user=$dbusername;
$DB_site->password=$dbpassword;
$DB_site->connect();
$dbpassword="";
$DB_site->password="";

$boutiques=$DB_site->query("select domaine from client where demo = '0' and boutique = '1' and location = '1'");
while ($boutique=$DB_site->fetch_array($boutiques)) {
	include "/home/boutique/www/configurations/$boutique[domaine]/config.php";
	$DB_site2=new DB_Sql;
	$DB_site2->appname='boutique';
	$DB_site2->appshortname='boutique';
	$DB_site2->database=$DBname;
	$DB_site2->server=$db_server;
	$DB_site2->user=$DBusername;
	$DB_site2->password=$DBpassword;
	$DB_site2->connect();
	$DBpassword="";


	$articles=$DB_site2->query("select artid, image from article order by artid") ;
	while ($article=$DB_site2->fetch_array($articles)) {
		if ($article[image] != "") { 
			$rep_racine = $rootpath."configurations/$host/images/produits/$article[artid].$article[image]";
			if (!file_exists($rep_racine)) {
				$reparticle = $rootpath."configurations/$host/images/produits/article/$article[artid].$article[image]";
				if (file_exists($reparticle))
					copy($reparticle, $rep_racine);
				else {
					//echo "$host $article[artid] : fichier .$article[image] n'existe pas => suppression.<br>" ;
					$DB_site2->query("update article set image = '' where artid = '$article[artid]'") ;
					}
				}
			if (file_exists($rep_racine)) {
				$reparticle = $rootpath."configurations/$host/images/produits/article/$article[artid].$article[image]";
				if (!file_exists($reparticle))
					redimentionner_image($rep_racine, $reparticle, $article_largeur, $article_hauteur);
	
				$repbr = $rootpath."configurations/$host/images/produits/br/$article[artid].$article[image]";
				if (!file_exists($repbr))
					redimentionner_image($rep_racine, $repbr, $hauteurimages, $largeurimages);
	
				$repcategorie = $rootpath."configurations/$host/images/produits/categorie/$article[artid].$article[image]";
				if (!file_exists($repcategorie))
					redimentionner_image($rep_racine, $repcategorie, $categorie_largeur, $categorie_hauteur);
	
				$repcoupscoeurcategories = $rootpath."configurations/$host/images/produits/cdc_categ/$article[artid].$article[image]";
				if (!file_exists($repcoupscoeurcategories))
					redimentionner_image($rep_racine, $repcoupscoeurcategories, $cdc_categ_largeur, $cdc_categ_hauteur);
	
				$repcoupscoeurindex = $rootpath."configurations/$host/images/produits/cdc_index/$article[artid].$article[image]";
				if (!file_exists($repcoupscoeurindex))
					redimentionner_image($rep_racine, $repcoupscoeurindex, $cdc_index_largeur, $cdc_index_hauteur);
	
				$repnouveautescategorie = $rootpath."configurations/$host/images/produits/nouveautes_categ/$article[artid].$article[image]";
				if (!file_exists($repnouveautescategorie))
					redimentionner_image($rep_racine, $repnouveautescategorie, $nouveautes_categ_largeur, $nouveates_categ_hauteur);
	
				$repnouveautesindex = $rootpath."configurations/$host/images/produits/nouveautes_index/$article[artid].$article[image]";
				if (!file_exists($repnouveautesindex))
					redimentionner_image($rep_racine, $repnouveautesindex, $nouveautes_index_largeur, $nouveates_index_hauteur);
	
				$reppromotionscategorie = $rootpath."configurations/$host/images/produits/promos_categ/$article[artid].$article[image]";
				if (!file_exists($reppromotionscategorie))
					redimentionner_image($rep_racine, $reppromotionscategorie, $promos_categ_largeur, $promos_categ_hauteur);
	
				$reppromotionsindex = $rootpath."configurations/$host/images/produits/promos_index/$article[artid].$article[image]";
				if (!file_exists($reppromotionsindex))
					redimentionner_image($rep_racine, $reppromotionsindex, $promos_index_largeur, $promos_index_hauteur);
	
				$reptopventescategories = $rootpath."configurations/$host/images/produits/topventes_categ/$article[artid].$article[image]";
				if (!file_exists($reptopventescategories))
					redimentionner_image($rep_racine, $reptopventescategories, $topventes_categ_largeur, $topventes_categ_hauteur);
	
				$reptopventesindex = $rootpath."configurations/$host/images/produits/topventes_index/$article[artid].$article[image]";
				if (!file_exists($reptopventesindex))
					redimentionner_image($rep_racine, $reptopventesindex, $topventes_index_largeur, $topventes_index_hauteur);
				}
			}
		$photos=$DB_site2->query("select articlephotoid, artid, image from articlephoto where artid = '$article[artid]' order by articlephotoid") ;
		while ($photo=$DB_site2->fetch_array($photos)) {
			$rep_racine = $rootpath."configurations/$host/images/produits/$photo[artid]"."_"."$photo[articlephotoid].$photo[image]";
			if (!file_exists($rep_racine)) {
				$reparticle = $rootpath."configurations/$host/images/produits/article/$photo[artid]"."_"."$photo[articlephotoid].$photo[image]";
				if (file_exists($reparticle))
					copy($reparticle, $rep_racine);
				else {
					//echo "$host $article[artid], photo $photo[articlephotoid] : fichier .$photo[image] n'existe pas => suppression.<br>" ;
					$DB_site2->query("delete from articlephoto where articlephotoid = '$photo[articlephotoid]'") ;
					}
				}
			if (file_exists($rep_racine)) {
				$reparticle = $rootpath."configurations/$host/images/produits/article/$photo[artid]"."_"."$photo[articlephotoid].$photo[image]";
				if (!file_exists($reparticle))
					redimentionner_image($rep_racine, $reparticle, $article_largeur, $article_hauteur);
	
				$repbr = $rootpath."configurations/$host/images/produits/br/$photo[artid]"."_"."$photo[articlephotoid].$photo[image]";
				if (!file_exists($repbr))
					redimentionner_image($rep_racine, $repbr, $hauteurimages, $largeurimages);
	
				$repcategorie = $rootpath."configurations/$host/images/produits/categorie/$photo[artid]"."_"."$photo[articlephotoid].$photo[image]";
				if (!file_exists($repcategorie))
					redimentionner_image($rep_racine, $repcategorie, $categorie_largeur, $categorie_hauteur);
	
				$repcoupscoeurcategories = $rootpath."configurations/$host/images/produits/cdc_categ/$photo[artid]"."_"."$photo[articlephotoid].$photo[image]";
				if (!file_exists($repcoupscoeurcategories))
					redimentionner_image($rep_racine, $repcoupscoeurcategories, $cdc_categ_largeur, $cdc_categ_hauteur);
	
				$repcoupscoeurindex = $rootpath."configurations/$host/images/produits/cdc_index/$photo[artid]"."_"."$photo[articlephotoid].$photo[image]";
				if (!file_exists($repcoupscoeurindex))
					redimentionner_image($rep_racine, $repcoupscoeurindex, $cdc_index_largeur, $cdc_index_hauteur);
	
				$repnouveautescategorie = $rootpath."configurations/$host/images/produits/nouveautes_categ/$photo[artid]"."_"."$photo[articlephotoid].$photo[image]";
				if (!file_exists($repnouveautescategorie))
					redimentionner_image($rep_racine, $repnouveautescategorie, $nouveautes_categ_largeur, $nouveates_categ_hauteur);
	
				$repnouveautesindex = $rootpath."configurations/$host/images/produits/nouveautes_index/$photo[artid]"."_"."$photo[articlephotoid].$photo[image]";
				if (!file_exists($repnouveautesindex))
					redimentionner_image($rep_racine, $repnouveautesindex, $nouveautes_index_largeur, $nouveates_index_hauteur);
	
				$reppromotionscategorie = $rootpath."configurations/$host/images/produits/promos_categ/$photo[artid]"."_"."$photo[articlephotoid].$photo[image]";
				if (!file_exists($reppromotionscategorie))
					redimentionner_image($rep_racine, $reppromotionscategorie, $promos_categ_largeur, $promos_categ_hauteur);
	
				$reppromotionsindex = $rootpath."configurations/$host/images/produits/promos_index/$photo[artid]"."_"."$photo[articlephotoid].$photo[image]";
				if (!file_exists($reppromotionsindex))
					redimentionner_image($rep_racine, $reppromotionsindex, $promos_index_largeur, $promos_index_hauteur);
	
				$reptopventescategories = $rootpath."configurations/$host/images/produits/topventes_categ/$photo[artid]"."_"."$photo[articlephotoid].$photo[image]";
				if (!file_exists($reptopventescategories))
					redimentionner_image($rep_racine, $reptopventescategories, $topventes_categ_largeur, $topventes_categ_hauteur);
	
				$reptopventesindex = $rootpath."configurations/$host/images/produits/topventes_index/$photo[artid]"."_"."$photo[articlephotoid].$photo[image]";
				if (!file_exists($reptopventesindex))
					redimentionner_image($rep_racine, $reptopventesindex, $topventes_index_largeur, $topventes_index_hauteur);
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
