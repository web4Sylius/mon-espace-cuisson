<?php 

require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");

$actives=array(0=>"Inactif",1=>"Actif",2=>"Supprimé",3=>"Archivé");

$sql = "SELECT artid, artcode, active
		FROM article
		WHERE image IS NOT NULL
		AND active IN ('0','1')
		ORDER BY active DESC,artcode";
$req = $DB_site2->query($sql);
while ($article = $DB_site2->fetch_array($req)) {

	$img_article = $rootpath."configurations/".$host."/images/produits/".$article['artid'].".jpg";
	$path = "/configurations/".$host."/images/produits/".$article['artid'].".jpg";
	
	if(file_exists($img_article)){
		list($l, $h) = getimagesize($img_article);
		$poids = filesize($img_article);

		$largeur = $l;
		$longueur = $h;

		$ko = number_format($poids/1024,2,',','')." Ko";
		//$mo = number_format($poids/1048576,2,',','')." Mo";

		if($largeur == $longueur) $format = "Portrait";
		elseif ($largeur > $longueur) $format = "Portrait";
		else $format = "Paysage";

		$lignes[] = array(
	      	$article['artcode'],
	      	$actives[$article['active']],
	      	$format,
	      	$longueur,
	      	$largeur,
	      	$ko,
	      	SITE_URL.$path
		);
	}else{
		$lignes[] = array(
	      	$article['artcode'],
	      	$actives[$article['active']],
	      	"/",
	      	"/",
	      	"/",
	      	"/",
	      	"PAS D'IMAGE"
	    );
	}
}

//Filename
$filename = "Image_Export_".date("d-m-Y",time());

//CSV file header
header("Content-type: application/vnd.ms-excel");
header("Content-Encoding: UTF-8");
header("Content-type: text/x-csv; charset=UTF-8");
header("Content-disposition: csv" . date("Y-m-d") . ".csv");
header("Content-disposition: filename=".$filename.".csv");

// Création de la ligne d'en-tête
$entete = array("Réf. Acilyon","Etat","Format","Longueur","Largeur","Poids Ko","Poids Mo","Lien");

$separateur = ";";

// Affichage de la ligne de titre, terminée par un retour chariot
echo utf8_decode(implode($separateur, $entete)."\r\n");

// Affichage du contenu du tableau
foreach ($lignes as $ligne) {
	echo utf8_decode(implode($separateur, $ligne)."\r\n");
}