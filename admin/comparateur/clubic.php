<?
$prov = "comp" ;
require "../admin_global.php";
include "../fonctions.php";

$contenu="Numéro unique|nom du produit|prix|frais de port|délai de livraison|type de produit|adresse de la page d'achat du produit|En stock|image produit";
$requete=" SELECT artid, a.prix, p.prix as prixport, tva, image, $libelle as libelle, $description as description FROM article as a, fraisport as p WHERE  a.poid between p.poiddebut and p.poidfin and p.paysid=57";
$articles=$DB_site2->query($requete);
while($article=$DB_site2->fetch_array($articles)) {
	$articlename=url_rewrite($article[libelle]);
	$article[libelle]=str_replace("|"," ",$article[libelle]);
	$categorie=$DB_site2->query_first("select c.libelleFR from categorie as c inner join article as a on (a.catid=c.catid) where a.artid=$article[artid]");
	$categorie=$categorie[0];
	$categorie=str_replace("|"," ",$categorie);

	$contenu.="\n";	
	$contenu.=$article[artid]; //identifiant article
	$contenu.="|";
	$contenu.=$article[libelle]; // libelle article
	$contenu.="|";
	$contenu.=round($article[prix]*(1+($article[tva]/100))); // prix article
	$contenu.="|";
	$contenu.=round($article[prixport]); // frais de port
	$contenu.="|";
	$contenu.="2 jours"; // delais de livraison
	$contenu.="|";
	$contenu.=$categorie; // type de produit
	$contenu.="|";
	$contenu.="http://".$host."/art".",$article[artid],$articlename.htm"; //adresse de la page
	$contenu.="|";
	$contenu.="1"; // en stock
	$contenu.="|";
	if($article[image] != "")
		{
		$contenu.="http://".$host."/configurations/".$host."/images/produits/article/$article[artid].$article[image]"; // img produit	
		}
		else
		{
		$contenu.=""; // img produit	
		}
}
if (!is_dir($rootpath."configurations/$host/exports")) {
	mkdir($rootpath."configurations/$host/exports",0777);
}
$filename = $rootpath."configurations/$host/exports/export_clubic.txt";
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

	//$file = "/home/arobases/www/vendre-en-ligne.fr/admin/comparateur/".$filename; 
	//header('Content-Description: File Transfer'); 
	//header('Content-Type: application/force-download'); 
	//header('Content-Length: ' . filesize($file)); 
	//header('Content-Disposition: attachment; filename=' . basename($file)); 
	//readfile($file); 
	//exit;
		echo "L'adresse de votre fichier d'export Clubic est la suivante : <a href=\"http://$host/configurations/$host/exports/export_clubic.txt\">http://$host/configurations/$host/exports/export_clubic.txt</a><br>";
	}
}

?>