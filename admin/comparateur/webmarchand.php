<?
$prov = "comp" ;
require "../admin_global.php";

$contenu="categorie;identifiant_unique;titre;prix;url_produit;url_image;description;frais_de_livraison;D3E;disponibilite;marque;ean;garantie;prix_barre;reference_modele;occasion;devise\n";

//include "../fonctions.php";

function url_rewrite($string) {
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

function recur($catid){
	global $DB_site2,$select, $i;
	$arbo=$DB_site2->query_first("select parentid, libelleFR from categorie where catid = '$catid'");
	//$select .= " > $arbo[libelleFR]";
	$arbo[libelleFR]=str_replace("\n"," ",$arbo[libelleFR]);
	$arbo[libelleFR]=str_replace("\r"," ",$arbo[libelleFR]);
	$arbo[libelleFR]=str_replace("\t"," ",$arbo[libelleFR]);
	$arbo[libelleFR]=str_replace(";"," ",$arbo[libelleFR]);
	$select[$i] = $arbo[libelleFR] ;
	$i++ ;
	if ($arbo[parentid] != 0)
		recur($arbo[parentid]);
	return ($select);
}
if (!is_dir($rootpath."configurations/$host/exports")) {
	mkdir($rootpath."configurations/$host/exports",0777);
}
$filename = $rootpath."configurations/$host/exports/leguide_fr.csv";
if (!$handle = fopen($filename, 'w')) 
	{
	echo "Impossible d'ouvrir le fichier ($filename)";
	exit;
	}
if (fwrite($handle, stripslashes(html_entity_decode($contenu))) === FALSE) 
	{
	echo "Impossible d'écrire dans le fichier ($filename)";
	exit;
	}
$articles=$DB_site2->query("SELECT artid, a.catid, a.prix, p.prix as prixport, tva, image, $libelle as libelle, $description as description 
						    FROM article a, fraisport p WHERE a.poid between p.poiddebut and p.poidfin and p.paysid = '57'");
while ($article=$DB_site2->fetch_array($articles)) 
	{
	// categorie
	$contenu="";
	$select = array() ;
	$i = 0 ;
	recur($article[catid]) ;
	$j = count($select) ;
	$select2 = "" ;
	$o = "" ;
	while ($j > 0)
		{
		$j-- ;
		if ($o == "")
			{
			$select2 .= $select[$j] ;
			}
		else
			{
			$select2 .= ">" . $select[$j] ;
			}
		$o = 1 ;
		}
	$contenu .= $select2.";" ; 
	// identifiant_unique
	$contenu .= $article[artid].";" ;
	// titre
	$contenu .= $article[libelle].";" ;
	// prix
	$datejour=date('Y-m-d') ;
	$articlepromo=$DB_site2->query_first("SELECT * FROM promotion WHERE artid = '$article[artid]' and datedebut < '$datejour' and datefin > '$datejour'");
	if ($articlepromo[promotionid] != "")
		{	// Article en promo -> on affiche le prix promo
		$temp = $articlepromo[prix] ;
		} 
	else
		{
		$temp = $article[prix] ;
		}
	$prix = $temp * (1 + ($article[tva] / 100)) ;
	$contenu .= number_format($prix, 2, ",", "").";";
	// url_produit
	$contenu .= "http://$host/art,$article[artid],".url_rewrite($article[libelle]).".htm".";";
	// url_image
	if ($article[image] != "")
		{
		$contenu.="http://$host/configurations/$host/images/produits/".$article[artid].".".$article[image].";";
		}
	else
		{
		$contenu.=";";
		}
	// description
	$article[description] = strip_tags(html_entity_decode($article[description])); 
	$article[description]=str_replace("\n"," ",$article[description]);
	$article[description]=str_replace("\r"," ",$article[description]);
	$article[description]=str_replace("\t"," ",$article[description]);
	$article[description]=str_replace(";",".",$article[description]);
	/*$article[description]=str_replace(";"," ",$article[description]);*/
	$description = substr($article[description], 0, 250) ;
	if ($description == "") 
		{
		$description="Pas de description pour cet article";
		}
	$contenu .= "$article[description]".";";
	// frais_de_livraison
	$article[prixport] = number_format($article[prixport], 2, ",", "");
	$contenu.="$article[prixport];";
	// D3E
	$contenu.=";";
	// disponibilite
	$stock=$DB_site2->query_first("SELECT * FROM stock WHERE artid = '$article[artid]'");
	if ($stock[nombre] == "")
		{
		$contenu.=";";
		}
	elseif ($stock[nombre] > 0)
		{
		$contenu.="0;";
		}
	elseif ($stock[delaiappro] != 0)
		{
		$contenu.="$stock[delaiappro];";
		}
	else
		{
		$contenu.=";";
		}
	// marque
	$marque=$DB_site2->query_first("SELECT * FROM article_marque am, marque m WHERE am.marqueid = m.marqueid and am.artid = '$article[artid]'");
	if ($marque[0] != "")
		{
		$contenu.="$marque[libelle];";
		}
	else
		{
		$contenu.=";";
		}
	// ean
	$contenu.=";";
	// garantie
	$contenu.=";";
	// prix_barre
	if ($articlepromo[promotionid] != "")
		{	// Article en promo -> on affiche le prix promo
		$article[prix] = number_format($article[prix], 2, ",", "");
		$contenu.="$article[prix];";
		} 
	else
		{
		$contenu.=";";
		}
	// reference_modele
	$contenu .= $article[artcode].";" ;
	// occasion
	$contenu.="0;";
	// devise	
	$contenu.="EUR;";
	$contenu .= "\n" ;
	fwrite($handle, stripslashes(html_entity_decode($contenu)));
	}

fclose($handle);
/*
$file = "/home/arobases/www/vendre-en-ligne.fr/admin/comparateur/".$filename; 
header('Content-Description: File Transfer'); 
header("Content-Type: text/csv; charset=utf-8") ;
//header('Content-Type: application/force-download'); 
header('Content-Length: ' . filesize($file)); 
header('Content-Disposition: attachment; filename=' . basename($file)); 
readfile($file); 
exit;
*/
echo "Votre export de catalogue pour le guide a bien été mis à jour.<br>
Il est disponible à l'adresse suivante :<a href=\"http://$host/configurations/$host/exports/leguide_fr.csv\"><strong>http://$host/configurations/$host/exports/leguide_fr.csv</strong></a>" ;
?>