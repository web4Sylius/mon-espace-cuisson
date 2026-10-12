<?php

$prov = "comp" ;
require "../admin_global.php";
include "../fonctions.php";

$liste_catid = "15,26,17,18,30,31,16,33,19,23,20,24,34";

if (!is_dir($rootpath."configurations/$host/exports")) {
	mkdir($rootpath."configurations/$host/exports",0777);
}
$filename = $rootpath."configurations/$host/exports/priceminister.csv";
if (!$handle = fopen($filename, 'w')) 
	{
	echo "Impossible d'ouvrir le fichier ($filename)";
	exit;
	}


$contenu="Référence fabricant;Référence interne;Prix de vente;Prix public;Quantité;Qualité;Commentaire annonce;URL Image 1;URL Image 2;Marque;Titre;Fiche Technique - Description;Type de Produit;Couleur;Dimensions;Expédition, Enlèvement;Téléphone;Code postal;Pays";
if (fwrite($handle, stripslashes(html_entity_decode($contenu))) === FALSE) 
	{
	echo "Impossible d'écrire dans le fichier ($filename)";
	exit;
	}
$articles=$DB_site2->query("select artid, coloris from article WHERE catid not in ($liste_catid) group by artcode order by catid, artcode");
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
			$contenu = generationligne($article[artid],$couleur[libelleFR]) ;
			if($contenu!=false) fwrite($handle, stripslashes(html_entity_decode($contenu)));
			}
		}
	else
		{// on a une ligne
		$contenu = generationligne($article[artid],$article[coloris]) ;
		if($contenu!=false) fwrite($handle, stripslashes(html_entity_decode($contenu)));
		}
	
		
	}
fclose($handle);

echo "Votre export de catalogue pour PriceMinister a bien été mis à jour.<br>
Il est disponible à l'adresse suivante :<a href=\"http://$host/configurations/$host/exports/priceminister.csv\"><strong>http://$host/configurations/$host/exports/priceminister.csv</strong></a>" ;

function generationligne ($artid,$couleur)
	{

	global $host,$DB_site2;
	$article = $DB_site2->query_first("select * from article WHERE artid = '$artid'");
	
	$ligne = "";
	$article[libelleFR] = html_entity_decode($article[libelleFR]);
	$article[libelleFR] = str_replace("Températures","T°",$article[libelleFR]);	
	$article[libelleFR] = str_replace("Température","T°",$article[libelleFR]);		
	$article[libelleFR] = str_replace("températures","T°",$article[libelleFR]);	
	$article[libelleFR] = str_replace("température","T°",$article[libelleFR]);			
	$article[libelleFR]=str_replace(";"," ",$article[libelleFR]);
	//image
	$imagecoloris[caractvalid]=0;
	$article[urlimage] = "" ;
	if ($couleur != "")
		{
		$coloris = explode("'",$couleur);
		$infocaractvalid = $DB_site2->query_first("SELECT caractvalid FROM caracteristiquevaleur WHERE caractid = '1' AND libelleFR like '%$coloris[0]%'");
		$existeimg = $DB_site2->query_first("SELECT image FROM article_caractval WHERE caractvalid = '$infocaractvalid[caractvalid]' AND artid = '$artid'");
		if ($existeimg[image] == "jpg")
			$article[urlimage] = "http://$host/configurations/$host/images/produits/article_caractval/$article[artid]_".$infocaractvalid[caractvalid].".$article[image]" ;
		elseif ($article[image] != "")
			$article[urlimage] = "http://$host/configurations/$host/images/produits/$article[artid].$article[image]" ;
		}
	elseif ($article[image] != "")
		$article[urlimage] = "http://$host/configurations/$host/images/produits/$article[artid].$article[image]" ;
	//image2
	$article[urlimage2] = "" ;
	if ($couleur != "")
		{
		$coloris = explode("'",$couleur);
		$imagecoloris = $DB_site2->query_first("SELECT image, caractvalid FROM caracteristiquevaleur WHERE caractid = '1' AND libelleFR like '%$coloris[0]%'");
		if ($imagecoloris[image] == "1")
			$article[urlimage2] = "http://$host/configurations/$host/images/caracteristiquevaleur/zoom/$imagecoloris[caractvalid].jpg" ;
		}
	
	//commentaire et titre
	$commentaire_annonce = 	"Achetez chez un Expert en caves à vin et celliers de 6 à 6200 bouteilles - couleur et inox - Tous types : vieillissement, conservation, multi-usages, encastrable ou service et préservation du vin - Pour particuliers et professionnels - Livraison assurée par des professionnels en France et en Europe - Ce modèle existe en ";
	$couleurs_cave = $DB_site2->query("select libelleFR from article_caractval inner join caracteristiquevaleur using(caractvalid) where artid = '$article[artid]'  and caractid = '1'");
	$liste_coloris = "";
	if ($DB_site2->num_rows($couleurs_cave) > 1)
		{
		while ($couleur_cave=$DB_site2->fetch_array($couleurs_cave))
			$liste_coloris .= "$couleur_cave[libelleFR], ";
		}
	else
		$liste_coloris = "$article[coloris]";
		
	$commentaire_annonce .= "$liste_coloris".($article[garantie_incluse]!=""?" - Garantie : $article[garantie_incluse]":"")." - Délai de livraison : $article[delailivraison_txt]";	
	
	//description
	$article[rangements_txt] = str_replace("<br>"," - ",$article[rangements_txt]);
	$article[rangements_txt] = str_replace("\n","",$article[rangements_txt]);
	$article[rangements_txt] = substr($article[rangements_txt], 0, -3);
	$descarticle = ($article[version_porte]!=""?"Porte : $article[version_porte]":"").($article[rangements_txt]!=""?" - Rangement inclus : $article[rangements_txt]":"");
	// references
	$artcode = $article[artcode];
	if(trim($imagecoloris[caractvalid])!="") $artcode .= "-$imagecoloris[caractvalid]";
	//$artcode .= abbreviation_codecouleur($couleur,$article[version_porte]);
	//$artcode .= "-".abbreviation_versionporte($article[version_porte]);
	/*if (eregi("combiné",$article[libelleFR]))
		$artcode .= "M";
	else
		$artcode .= "D";*/
			
	if (eregi("vitrée",$article[version_porte]) and !eregi("pleine",$article[version_porte]))	
		{
		$noir = $DB_site2->query_first("select count(*) from article_caractval inner join caracteristiquevaleur using(caractvalid) where artid = '$article[artid]'  and caractid = '2' and caractvalid = '7'");
		$inox = $DB_site2->query_first("select count(*) from article_caractval inner join caracteristiquevaleur using(caractvalid) where artid = '$article[artid]'  and caractid = '2' and caractvalid = '8'");
		$naturel = $DB_site2->query_first("select count(*) from article_caractval inner join caracteristiquevaleur using(caractvalid) where artid = '$article[artid]'  and caractid = '2' and caractvalid = '12'");
		$nb_possiblites = $noir[0] + $inox[0] + $naturel[0];
		if ($noir[0] == "1" and $naturel[0] == "1")
			{
			//$artcode .= "N";
			$descarticle .= " - Cadre Vitre :  Alu Noir (Naturel sur simple demande)";
			}
		elseif ($inox[0] == "1" and $noir[0] == "0" and $naturel[0] == "0")
			{
			//$artcode .= "I";
			$descarticle .= " - Cadre Vitre :  Inox";
			}	
		elseif ($noir[0] == "1" or $naturel[0] == "1")
			{
			if ($noir[0] == "1")
				{
			//	$artcode .= "N";
				$descarticle .= " - Cadre Vitre :  Alu Noir";
				}
			elseif ($naturel[0] == "1")
				{
			//	$artcode .= "S";
				$descarticle .= " - Cadre Vitre :  Alu Naturel";
				}	
			}	
		}
	$titrearticle = "$article[libelleFR] $article[gamme] $artcode".($article[nbr_temperature]!=""?" - Nbre T° : $article[nbr_temperature]":"").($article[capacite_bouteilles]!=""?" - Bouteilles : $article[capacite_bouteilles]":"")." - Coloris : ";
	$titrearticle .= "$couleur";
	
	$req=$DB_site2->query("SELECT * FROM market_place_champs WHERE market_place='Priceminister' AND artcode='$article[artcode]' AND champ='Titre' AND caractvalid=".intval($imagecoloris[caractvalid]));
	$mpc=$DB_site2->fetch_array($req);
	//if($mpc['enabled']==0) return false;
	//$titrearticle=$mpc['valeur'];
	
	$optionporte = $DB_site2->query_first("SELECT COUNT(*) FROM article_caractval WHERE artid = '$article[artid]' AND caractvalid = '13'");	
	if ($optionporte[0] > 0)
		$descarticle .= " - Charnières Porte : à droite ( à Gauche sur simple demande)";
	$descarticle .= ($article[temperature_cave]!=""?" - T° installation cave : $article[temperature_cave]":"").($article[conso_euro]!=""?" - Consommation €/an : $article[conso_euro] TTC":"");
	$descarticle .= ($article[regulation]!=""?" - Régulation : $article[regulation]":"").($article[affichage]!=""?" - Affichage : $article[affichage]":"").($article[hygrometrie]!=""?" - Hygrométrie : ".stripslashes($article[hygrometrie]):"").($article[serrure]!=""?" - Serrures : $article[serrure]":"").($article[eclairage]!=""?" - Eclairage : $article[eclairage]":"").($article[interrupteur]!=""?" - Interrupteur Marche/Arrêt : $article[interrupteur]":"");
	//prix de vente TTC
	$port = $DB_site2->query_first("SELECT p.prix as prixport, a.poid FROM fraisport p, article a WHERE p.paysid = '57' AND a.poid between p.poiddebut and p.poidfin AND a.artid = '$article[artid]'");
	$prixvente = $article[prix] + $port[prixport] - 36;	
	$prixvente = number_format($prixvente, 2, ',', '');
	//prix de vente TTC
	$prixpublic = ($article[prixconseilleht]*1.196) + $port[prixport];	
	$prixpublic = number_format($prixpublic, 2, ',', '');	
		
	//debut de la ligne
	$ligne.="\n";	
	// reference_fabricant
	$ligne.="$artcode";
	$ligne.=";";
	// reference interne
	$ligne.="$artcode";
	$ligne.=";";
	// prix de vente TTC
	$ligne.="$prixvente";
	$ligne.=";";
	// prix public TTC
	$ligne.="$prixpublic";
	$ligne.=";";	
	// quantité
	$ligne.="*";
	$ligne.=";";
	// qualité
	$ligne.="N";
	$ligne.=";";
	// Commentaire annonce
	$ligne.="$commentaire_annonce";
	$ligne.=";";
	// url image 1
	$ligne.="$article[urlimage]";
	$ligne.=";";
	// url image 2
	$ligne.="$article[urlimage2]";
	$ligne.=";";
	// marque
	$marque=$DB_site2->query_first("select libelle from article_marque inner join marque using(marqueid) where artid = '$article[artid]'");
	$ligne.="$marque[libelle]";
	$ligne.=";";
	// titre
	$ligne.="$titrearticle";
	$ligne.=";";
	// fiche technique
	$ligne.="$descarticle";
	$ligne.=";";
	// type de produit
	$req=$DB_site2->query("SELECT * FROM market_place_champs WHERE market_place='Priceminister' AND artcode='$article[artcode]' AND champ='Type de Produit'");
	$mpc=$DB_site2->fetch_array($req);
	$ligne.=$mpc['valeur'];
	$ligne.=";";
	// Couleur
	$ligne.="$couleur";
	$ligne.=";";
	// dimensions
	$ligne.="$article[dimensions]";
	$ligne.=";";
	// expéditon, enlèvement
	$ligne.="EXP";
	$ligne.=";";
	// telephone
	$ligne.="+33(0)426030543";
	$ligne.=";";
	// code postal
	$ligne.="69003";
	$ligne.=";";
	// pays
	$ligne.="France";
	

	global $_GET;
	if(isset($_GET['tr'])){
	      $DB_site2->query("INSERT INTO market_place_champs SET market_place='Priceminister', artcode='$article[artcode]', champ='Titre', valeur='$titrearticle', limite=255, enabled=1, caractvalid=".intval($imagecoloris[caractvalid]));
	      $DB_site2->query("INSERT INTO market_place_champs SET market_place='Priceminister', artcode='$article[artcode]', champ='Type de Produit', valeur='Cave à vin', limite=50, enabled=1, caractvalid=0");
	}
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
