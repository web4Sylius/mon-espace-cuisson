<?
$prov = "comp" ;
require "../admin_global.php";
include "../fonctions.php";

if (!function_exists("url_rewrite2")) {
	function url_rewrite2($string) {
		$string=html_entity_decode($string , ENT_NOQUOTES , "ISO-8859-1" );
		$caracteres = array("¥" => "Y", "µ" => "u", "À" => "A", "Á" => "A", "Â" => "A", "Ã" => "A", "Ä" => "A", "Å" => "A",
		"Æ" => "A", "Ç" => "C", "È" => "E", "É" => "E", "Ê"=> "E", "Ë" => "E", "Ì" => "I", "Í" => "I",
		"Î" => "I", "Ï" => "I", "Ð" => "D", "Ñ" => "N", "Ò" => "O", "Ó" => "O", "Ô" => "O", "Õ" => "O",
		"Ö" => "O", "Ø" => "O", "Ù" => "U", "Ú" => "U", "Û" => "U", "Ü" => "U", "Ý" => "Y", "ß" => "s",
		"à" => "a", "á" => "a", "â" => "a", "ã" => "a", "ä" => "a", "å"=> "a", "æ" => "a", "ç"=> "c",
		"è" => "e", "é" => "e", "ê" => "e", "ë" => "e", "ì" => "i", "í" => "i", "î" => "i", "ï"=> "i",
		"ð"=> "o", "ñ"=> "n", "ò" => "o", "ó" => "o", "ô" => "o", "õ" => "o", "ö" => "o", "ø" => "o",
		"ù" => "u", "ú" => "u", "û" => "u", "ü" => "u", "ý" => "y", "ÿ" => "y");
		$string = strtr($string , $caracteres);
		$string=strtolower($string);
		$len = strlen($string);
		for($a=0; $a<$len; $a++){
			$p = ord($string[$a]);
			if ( ($p > 96 && $p < 123) or ($p > 47 && $p < 58) ) {
				$return .= $string[$a];
			} else {
				$return .= "-";
			}
		}
		while(strpos($return,"--")!==FALSE) {
			$return=str_replace("--","-",$return);
		}
		$return=trim($return,"-");
		return $return;
	}
}

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
if (!is_dir("/home/boutique/www/configurations/$host/exports")) {
	mkdir("/home/boutique/www/configurations/$host/exports",0777);
}
$filename = $rootpath."configurations/$host/exports/aujardin.txt";
if (!$handle = fopen($filename, 'w')) 
	{
	echo "Impossible d'ouvrir le fichier ($filename)";
	exit;
	}


$contenu="Identifiant|Catégorie|Titre|Description|Prix TTC|URL produit|URL photo";
if (fwrite($handle, stripslashes(html_entity_decode($contenu))) === FALSE) 
	{
	echo "Impossible d'écrire dans le fichier ($filename)";
	exit;
	}
$articles=$DB_site2->query("select artid, catid, libelleFR, descriptionFR, prix, image from article order by libelleFR, prix");
while ($article=$DB_site2->fetch_array($articles)) 
	{
	$contenu = "" ;
	$article[libelleFR]=str_replace("|"," ",$article[libelleFR]);
	$article[descriptionFR]=str_replace("|","&nbsp;",$article[descriptionFR]);
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
	$contenu.="|";
	$navigationbit = "" ;
	navigation($article[catid]);
	if (substr($navigationbit,strlen($navigationbit)-3,3)==" > ")
		$navigationbit=substr($navigationbit,0,strlen($navigationbit)-3);
	$contenu.=$navigationbit;
	$contenu.="|";
	$contenu.=$article[libelleFR];
	$contenu.="|";
	$contenu.=$article[descriptionFR];
	$contenu.="|";
	$contenu.=$article[prix];
	$contenu.="|";
	$articlename=url_rewrite2($article[libelleFR]);
	$contenu.="http://$host/art,$article[artid],$articlename.htm";
	$contenu.="|";
	$contenu.="$article[urlimage]"; // en stock
	fwrite($handle, stripslashes(html_entity_decode($contenu)));
	}
fclose($handle);
/*
header('Content-Description: File Transfer'); 
header('Content-Type: application/force-download'); 
header('Content-Length: ' . filesize($file)); 
header('Content-Disposition: attachment; filename=' . basename($file)); 
readfile($file); 
exit;*/
echo "Votre export de catalogue pour aujardin.info a bien été mis à jour.<br>
Il est disponible à l'adresse suivante :<a href=\"http://$host/configurations/$host/exports/aujardin.txt\"><strong>http://$host/configurations/$host/exports/aujardin.txt</strong></a>" ;
?>