<?
$prov = "comp" ;
require "../admin_global.php";

$contenu="merchant-category	offer-id	label	offer-url	prices	description	image-url	delivery-charge	product-type	product-id	delivery-period	brand	ships-in	promotional-text	old-prices	start-date	end-date	release-date	condition	deal-type	offer-attributes\n";

include "../fonctions.php";

$requete="	SELECT c.libelleFR as catlib, m.libelle as marque, a.artid, artcode,a.libelleFR as libelle,	
				a.prix, p.prix as prixport, tva, a.image, a.descriptionFR as description 
			FROM article as a inner  join article_marque as a_m on a.artid = a_m.artid 
							inner join marque as m on a_m.marqueid = m.marqueid,fraisport as p,categorie as c
			WHERE a.catid=c.catid and a.poid between p.poiddebut and p.poidfin and p.paysid=57 
				and a.artid not in (select pr.artid from promotion as pr) " ;

$requetepromo="	SELECT c.libelleFR as catlib, m.libelle as marque, a.artid, artcode,a.libelleFR as libelle,	 
				pr.prix as prix, p.prix as prixport, tva, a.image, a.descriptionFR as description 
			FROM article as a inner  join article_marque as a_m on a.artid = a_m.artid 
					inner join marque as m on a_m.marqueid = m.marqueid,fraisport as p,categorie as c,promotion as pr
			WHERE a.catid=c.catid and a.poid between p.poiddebut and p.poidfin and p.paysid=57 and a.artid=pr.artid
				and a.artid in (select pr.artid from promotion as pr) " ;
$i=0;			
$articles=$DB_site2->query($requete);
while($article=$DB_site2->fetch_array($articles)) {
	$articlename = url_rewrite($article[libelle]);
///////	merchant-category
	$contenu.=$article[catlib]."\t";
///////	offer-id
	$contenu.=$article[artid]."\t";
///////	label
	$contenu.= $article[marque]." ".$article[artcode]." ".substr($article[libelle],0,126)."\t";
///////	offer-url
	$contenu.= "http://".$host."/art".",$article[artid],$articlename.htm\t";
///////	prices
	//$prix=$article[prix]*(1+($article[tva]/100));
	$prix = $article[prix];
	$contenu.="EUR".$prix."\t";
///////	description
	$article[description]=str_replace("\n"," ",$article[description]);
	$article[description]=str_replace("\r"," ",$article[description]);
	$article[description]=str_replace("\t"," ",$article[description]);
	$description=substr(strip_tags($article[description]),0,3999);
	if ($description=="") {
		$description="Pas de description pour cet article";
	}
	$contenu.=$description."\t";
///////	image-url
	$contenu.="http://".$host."/configurations/$host/images/produits/".$article[artid].".".$article[image]."\t";
///////	delivery-charge
	$contenu.="EUR".$article[prixport]."\t";
///////	product-type
	$contenu.="PRODUCT"."\t";
///////	product-id Numéro de référence reconnu mondialement ISBN pour les livres EAN pour tout autre produit 
	$contenu.=""."\t";
///////	delivery-period
	$contenu.="H48"."\t";
///////	brand
	$contenu.=$article[marque]."\t";
///////	ships-in
	$contenu.=""."\t";
///////	promotional-text
	$contenu.=""."\t";
///////	old-prices
	$contenu.=""."\t";
///////	start-date
	$contenu.=""."\t";
///////	end-date
	$contenu.=""."\t";
///////	release-date
	$contenu.=""."\t";
///////	condition
	$contenu.="NEW"."\t";
///////	deal-type
	$contenu.="FIXED_PRICE"."\t";
///////	offer-attributes
	$contenu.="";
	$contenu.="\n";
}
$articles=$DB_site2->query($requetepromo);
while($article=$DB_site2->fetch_array($articles)) {
	$articlename = url_rewrite($article[libelle]);
///////	merchant-category
	$contenu.=$article[catlib]."\t";
///////	offer-id
	$contenu.=$article[artid]."\t";
///////	label
	$contenu.= $article[marque]." ".$article[artcode]." ".substr($article[libelle],0,126)."\t";
///////	offer-url
	$contenu.= "http://".$host."/art".",$article[artid],$articlename.htm\t";
///////	prices
	//$prix=$article[prix]*(1+($article[tva]/100));
	$prix = $article[prix];
	$contenu.="EUR".$prix."\t";
///////	description
	$article[description]=str_replace("\n"," ",$article[description]);
	$article[description]=str_replace("\r"," ",$article[description]);
	$article[description]=str_replace("\t"," ",$article[description]);
	$description=substr(strip_tags($article[description]),0,3999);
	if ($description=="") {
		$description="Pas de description pour cet article";
	}
	$contenu.=$description."\t";
///////	image-url
	$contenu.="http://".$host."/configurations/$host/images/produits/".$article[artid].".".$article[image]."\t";
///////	delivery-charge
	$contenu.="EUR".$article[prixport]."\t";
///////	product-type
	$contenu.="PRODUCT"."\t";
///////	product-id Numéro de référence reconnu mondialement ISBN pour les livres EAN pour tout autre produit 
	$contenu.=""."\t";
///////	delivery-period
	$contenu.="H48"."\t";
///////	brand
	$contenu.=$article[marque]."\t";
///////	ships-in
	$contenu.=""."\t";
///////	promotional-text
	$contenu.="Promotion"."\t";
///////	old-prices
	$contenu.=""."\t";
///////	start-date
	$contenu.=""."\t";
///////	end-date
	$contenu.=""."\t";
///////	release-date
	$contenu.=""."\t";
///////	condition
	$contenu.="NEW"."\t";
///////	deal-type
	$contenu.="FIXED_PRICE"."\t";
///////	offer-attributes
	$contenu.="";
	$contenu.="\n";
}

if (!is_dir($rootpath."configurations/$host/exports")) {
	mkdir($rootpath."www/configurations/$host/exports",0777);
}
$filename = $rootpath."configurations/$host/exports/export_pangora.txt";
if (!$handle = fopen($filename, 'w')) {
	echo "Impossible d'ouvrir le fichier ($filename)";
	exit;
} else {
	if (fwrite($handle, stripslashes(html_entity_decode($contenu))) === FALSE) {
		echo "Impossible d'écrire dans le fichier ($filename)";
		fclose($handle);
		exit;
	} else {
		fclose($handle);
		//header("Location: http://www.arobases.fr/boutique/admin/comparateur/$filename");
		//$file = "/home/arobases/www/vendre-en-ligne.fr/admin/comparateur/".$filename; 
		//header('Content-Description: File Transfer'); 
		//header('Content-Type: application/force-download'); 
		//header('Content-Length: ' . filesize($file)); 
		//header('Content-Disposition: attachment; filename=' . basename($file)); 
		//readfile($file); 
		//exit;
		echo "L'adresse de votre fichier d'export pangora est la suivante : <a href=\"http://$host/configurations/$host/exports/export_pangora.txt\">http://$host/configurations/$host/exports/export_pangora.txt</a><br>";
	}
}


?>