<?
$prov = "comp" ;
require "../admin_global.php";
include "../fonctions.php";

$liste_catid = "15,26,17,18,30,31,16,33,19,23,20,24,34";

if (!is_dir($rootpath."configurations/$host/exports")) {
	mkdir($rootpath."configurations/$host/exports",0777);
}
$filename = $rootpath."configurations/$host/exports/kelkoo.csv";
if (!$handle = fopen($filename, 'w')) 
	{
	echo "Impossible d'ouvrir le fichier ($filename)";
	exit;
	}

$contenu="id|model|brand|description|price|url|merchantcat|image|ean|sku|used|availability|deliveryprice|deliverytime|deliveryinfo|warranty|ecotax|catkkid|taggingmatching|voucher|voucherdesc|voucherurl|vouchercode|voucherstart|voucherend|pricenorebate|percentagepromo|promostart|promoend|offertype|flag|shopinfo";
if (fwrite($handle, stripslashes(html_entity_decode($contenu))) === FALSE) 
	{
	echo "Impossible d'écrire dans le fichier ($filename)";
	exit;
	}
$articles=$DB_site2->query("select artid, coloris from article WHERE catid not in ($liste_catid) order by catid, artcode");
while ($article=$DB_site2->fetch_array($articles)) 
	{
	// nombre de coloris ==> nombre de ligne dans l'export
	$nbcouleurs = $DB_site2->query_first("SELECT COUNT(*) FROM article_caractval INNER JOIN caracteristiquevaleur USING(caractvalid) WHERE artid = '$article[artid]' AND caractid = '1'");
	if ($nbcouleurs[0] > 0)
		{ // on a plusieurs lignes
		$couleurs = $DB_site2->query("SELECT * FROM article_caractval ac INNER JOIN caracteristiquevaleur c USING(caractvalid) WHERE ac.artid = '$article[artid]' AND c.caractid = '1'");
		while ($couleur=$DB_site2->fetch_array($couleurs))
			{
			$couleur[libelleFR] = stripslashes($couleur[libelleFR]);
			$contenu = generationligne($article[artid],$couleur[libelleFR],$couleur[caractvalid]) ;
			fwrite($handle, stripslashes(html_entity_decode($contenu)));
			}
		}
	else
		{// on a une ligne
		$contenu = generationligne($article[artid],$article[coloris],0) ;
		fwrite($handle, stripslashes(html_entity_decode($contenu)));
		}
	}
fclose($handle);

echo "Votre export de catalogue pour Kelkoo a bien été mis à jour.<br>
Il est disponible à l'adresse suivante :<a href=\"http://$host/configurations/$host/exports/kelkoo.csv\"><strong>http://$host/configurations/$host/exports/kelkoo.csv</strong></a>" ;

function generationligne ($artid,$couleur,$caractvalid)
	{
	global $host,$DB_site2;
	$article = $DB_site2->query_first("select * from article WHERE artid = '$artid'");
	
	$ligne = "";
	$article[libelleFR] = html_entity_decode($article[libelleFR]);
	$article[libelleFR]=str_replace(";"," ",$article[libelleFR]);
	//categorie
	$categorie = $DB_site2->query_first("SELECT libelleFR FROM categorie WHERE catid = '$article[catid]'");
	$categorie[libelleFR] = html_entity_decode($categorie[libelleFR]);
	$categorie[libelleFR]=str_replace(";"," ",$categorie[libelleFR]);
	//image
	$article[urlimage] = "" ;
	if ($article[image] != "")
		$article[urlimage] = "http://$host/configurations/$host/images/produits/$article[artid].$article[image]" ;
	
	//description
	$descarticle = "$article[libelleFR]";
	if ($article[nbr_temperature] != "")
		$descarticle .= ", Nbre T°: $article[nbr_temperature]";
	if ($article[version_porte] != "")
		$descarticle .= ", Porte : $article[version_porte]";
	if ($article[regulation] != "")
		$descarticle .= ", Régulation $article[regulation]";
	if ($article[affichage] != "")
		$descarticle .= ", Thermomètre et Affichage : $article[affichage]";
	if ($article[hygrometre] != "")
		$descarticle .= ", Hygromètre : $article[hygrometre]";
	if ($article[serrure] != "")
		$descarticle .= ", Serrure : $article[serrure]";	
	if ($article[eclairage] != "")
		$descarticle .= ", Eclairage : $article[eclairage]";		
	
	//prix de vente TTC
	$prixvente = $article[prix] - $article[ecopart];	
	$prixvente = number_format($prixvente, 2, '.', '');
	$marque=$DB_site2->query_first("select libelle from article_marque inner join marque using(marqueid) where artid = '$article[artid]'");	
		
	//debut de la ligne
	$ligne.="\n";	
	// id
	$ligne.="$article[artid]";
	$ligne.="|";
	// model
	$denomination = "$marque[libelle] $article[gamme] $article[artcode]";
	if ($article[capacite_bouteilles] != "")
		$denomination .= " - $article[capacite_bouteilles] bouteilles";
	if ($couleur != "")
		$denomination .= " - $couleur";	
	$ligne.="$denomination";
	$ligne.="|";
	// brand
	if ($marque[libelle] == "")
		$marque[libelle] = "Générique";
	$ligne.="$marque[libelle]";
	$ligne.="|";
	// description
	$ligne.= "$descarticle";
	$ligne.="|";
	// price
	$ligne.="$prixvente";
	$ligne.="|";
	// url
	$ligne.="http://$host/art,$article[artid],".url_rewrite2($article[libelleFR]).".htm";
	$ligne.="|";
	// merchantcat
	$ligne.= "$categorie[libelleFR]";
	$ligne.="|";
	// image
	$ligne.="$article[urlimage]";
	$ligne.="|";
	// ean
	$ean = $DB_site2->query_first("SELECT codebarre FROM code_barre WHERE artid = '$article[artid]' AND caractvalid = '$caractvalid'");
	$ligne.="$ean[codebarre]";
	$ligne.="|";
	// sku
	$ligne.="";
	$ligne.="|";
	// used
	$ligne.="neuf";
	$ligne.="|";
	
	// availability
	if ($article[sowine] == "1")
		$ligne.="001";
	else
		{
		//$availability = $article[delailivraison] - 3;
		//$ligne.="sous $availability jours";
		
		$ligne.="005";
		
		}
	$ligne.="|";
	// delivery price
	$fraisdeport = $DB_site2->query_first("SELECT prix FROM fraisport WHERE poiddebut <= '$article[poid]' AND poidfin >= '$article[poid]' 
												and paysid = '57'");
	$ligne.="$fraisdeport[prix]";
	$ligne.="|";
	// delivery time
	if ($article[sowine] == "1")
		$ligne.="sous 24/48 heures";
	else
		$ligne.="sous 3 à 4 jours";	
	$ligne.="|";
	// delivery info
	$ligne.="";
	$ligne.="|";
	
	// warranty
	$article[garantie_incluse]=html_entity_decode($article[garantie_incluse]);
	$ligne.="$article[garantie_incluse]";
	$ligne.="|";
	// ecotax
	$ligne.="$article[ecopart]";
	$ligne.="|";
	// catkkid
	$ligne.="100014413";
	$ligne.="|";
	// taggingMatching
	$ligne.="$marque[libelle] $article[gamme] $article[modele]";
	$ligne.="|";
	
	
	//voucher
	$ligne.="";
	$ligne.="|";
	//voucherdesc
	$ligne.="";
	$ligne.="|";
	//voucherurl
	$ligne.="";
	$ligne.="|";
	//vouchercode
	$ligne.="";
	$ligne.="|";
	//voucherstart
	$ligne.="";
	$ligne.="|";
	//voucherend
	$ligne.="";
	$ligne.="|";
	
	
	if ($article[prixconseilleht]!="" && $article[prixconseilleht]!=$article[prixht]){
		// pricenorebate
		$prixconseillettc=$article[prixconseilleht]*1.196;
		$prixconseillettc = number_format($prixconseillettc, 2, '.', '');
		$ligne.="$prixconseillettc";
		$ligne.="|";
		// percentagepromo
		$pourcent=(($article[prixconseilleht] - $article[prixht]) / $article[prixconseilleht]) * 100;
		$pourcent = floor($pourcent);
		$ligne.="$pourcent";
		$ligne.="|";
	}else{
		// pricenorebate
		$ligne.="";
		$ligne.="|";
		// percentagepromo
		$ligne.="";
		$ligne.="|";
	}
	
	// promostart
	$ligne.="";
	$ligne.="|";
	// promoend
	$ligne.="";
	$ligne.="|";
	// offertype
	$ligne.="";
	$ligne.="|";
	// flag
	$ligne.="";
	$ligne.="|";
	// shop info
	$ligne.="Experts en caves a vin de 6 à 2000 bouteilles, gamme couleur et inox, Grandes marques. Facilités de paiement.";
	return $ligne;
	}

function abbreviation_codecouleur ($couleur, $porte)
	{
	$couleur = strtolower($couleur);
	$porte = strtolower($porte);
	if (eregi("bordeaux",$couleur))
		$code = "B";
	elseif (eregi("ambroise",$couleur))
		$code = "AM";
	elseif (eregi("poirier rouge",$couleur))
		$code = "PR";
	elseif (eregi("calvados",$couleur))
		$code = "PO";
	elseif (eregi("noir",$couleur))
		$code = "NS";
	elseif (eregi("tabac",$couleur))
		$code = "PL";
	elseif (eregi("inox",$couleur))
		{
		if (eregi("vitrée",$porte) and eregi("pleine",$porte))
			$code = "IM";
		elseif (eregi("vitrée",$porte))
			$code = "IV";
		elseif (eregi("pleine",$porte))
			$code = "IP";
		else
			$code = "IM";	
		}
	else
		$code = "";	
	return $code;	
	}
	
function abbreviation_versionporte ($porte)
	{
	$porte = strtolower($porte);
	if (eregi("vitrée",$porte) and ereg("pleine",$porte)) 
		$code = "M";
	elseif (eregi("vitrée",$porte))  
		$code = "V";
	elseif (eregi("pleine",$porte))
		$code = "P";
	else
		$code = "";		
	return $code;	
	}
	
?>