<?
$prov = "comp" ;
require "../admin_global.php";
include "../fonctions.php";

function navigation($catidfunc , $isarticle=0)	
	{
	global $DB_site2,$navigationbit,$catid,$pagetitle_catname;
	$requete_categorie="select catid, libelleFR, parentid from categorie where catid='$catidfunc'";
	$navigation_categorie=$DB_site2->query_first($requete_categorie);
	$navigationbit="$navigation_categorie[libelleFR]" . " > " . $navigationbit;
	if ($navigation_categorie[parentid]!=0) 
		{
		navigation($navigation_categorie[parentid]);
		}
	}
if (!is_dir($rootpath."configurations/$host/exports")) {
	mkdir($rootpath."configurations/$host/exports",0777);
}
$filename = $rootpath."configurations/$host/exports/touslesprix.csv";
if (!$handle = fopen($filename, 'w')) 
	{
	echo "Impossible d'ouvrir le fichier ($filename)";
	exit;
	}


$contenu="Identifiant;Nom;Sous-titre;URL produit;Prix TTC;Catégorie;Marque;Prais de port;Description;URL photo";
if (fwrite($handle, stripslashes(html_entity_decode($contenu))) === FALSE) 
	{
	echo "Impossible d'écrire dans le fichier ($filename)";
	exit;
	}
$articles=$DB_site2->query("select artid, libelleFR, Titre2FR, prix, catid, poid, descriptionFR, image from article order by libelleFR, prix");
while ($article=$DB_site2->fetch_array($articles)) 
	{
	$contenu = "" ;
	$article[libelleFR]=str_replace(";"," ",$article[libelleFR]);
	$article[descriptionFR]=str_replace(";","&nbsp;",$article[descriptionFR]);
	$article[descriptionFR] = strip_tags($article[descriptionFR]);
	$article[descriptionFR]=nl2br($article[descriptionFR]);
	$article[descriptionFR] = strip_tags($article[descriptionFR]);
	$article[descriptionFR]=str_replace("\n","&nbsp;",$article[descriptionFR]);
	$article[descriptionFR]=str_replace("\r","&nbsp;",$article[descriptionFR]);
	$article[descriptionFR]=str_replace("\t","&nbsp;",$article[descriptionFR]);
	while (ereg("&nbsp;&nbsp;", $article[descriptionFR]))
		$article[descriptionFR]=str_replace("&nbsp;&nbsp;","&nbsp;",$article[descriptionFR]);
	$article[urlimage] = "" ;
	if ($article[image] != "")
		$article[urlimage] = "http://$host/configurations/$host/images/produits/$article[artid].$article[image]" ;

	$contenu.="\n";	
	$contenu.=$article[artid];
	$contenu.=";";
	$contenu.=$article[libelleFR];
	$contenu.=";";
	$contenu.=$article[Titre2FR];
	$contenu.=";";
	$articlename=url_rewrite($article[libelleFR]);
	$contenu.="http://$host/art,$article[artid],$articlename.htm";
	$contenu.=";";
	$contenu.=$article[prix];
	$contenu.=";";
	$navigationbit = "" ;
	navigation($article[catid]);
	if (substr($navigationbit,strlen($navigationbit)-3,3)==" > ")
		$navigationbit=substr($navigationbit,0,strlen($navigationbit)-3);
	$contenu.=$navigationbit;
	$contenu.=";";
	$marque=$DB_site2->query_first("select libelle from article_marque inner join marque using(marqueid) where artid = '$article[artid]'");
	$contenu.="$marque[libelle]";
	$contenu.=";";
	$fraisport=$DB_site2->query_first("select prix from fraisport where paysid = '57' and poiddebut <= '$article[poid]' and poidfin > '$article[poid]'");
	$contenu.="$fraisport[prix]";
	$contenu.=";";
	$contenu.=$article[descriptionFR];
	$contenu.=";";
	$contenu.="$article[urlimage]"; // en stock
	fwrite($handle, stripslashes(html_entity_decode($contenu)));
	}
fclose($handle);

echo "Votre export de catalogue pour touslesprix.com a bien été mis à jour.<br>
Il est disponible à l'adresse suivante :<a href=\"http://$host/configurations/$host/exports/touslesprix.csv\"><strong>http://$host/configurations/$host/exports/touslesprix.csv</strong></a>" ;
?>