<?
$prov = "comp" ;
require "../admin_global.php";
include "../fonctions.php";

$liste_catid = "5,6,8";

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
			fwrite($handle, stripslashes(html_entity_decode($contenu)));
			}
		}
	else
		{// on a une ligne
		$contenu = generationligne($article[artid],$article[coloris]) ;
		fwrite($handle, stripslashes(html_entity_decode($contenu)));
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
	$article[libelleFR]=str_replace(";"," ",$article[libelleFR]);
	//image
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
	$commentaire_annonce = 	"Achetez chez un Expert en caves à vin - 32 à 1000 bouteilles - couleur et inox - Bar à vin pour la maison - 100% Fabrication Française - 100% Controlé et Pré-réglé Usine - Tous types : vieillissement, conservation, multi-usages ou service du vin - Garantie 2 ans - Livraison assurée par un professionnel. - Ce modèle existe en ";
	$couleurs_cave = $DB_site2->query("select libelleFR from article_caractval inner join caracteristiquevaleur using(caractvalid) where artid = '$article[artid]'  and caractid = '1'");
	$liste_coloris = "";
	if ($DB_site2->num_rows($couleurs_cave) > 1)
		{
		while ($couleur_cave=$DB_site2->fetch_array($couleurs_cave))
			$liste_coloris .= "$couleur_cave[libelleFR], ";
		}
	else
		$liste_coloris = "$article[coloris]";
	$commentaire_annonce .= "$liste_coloris - Délai de livraison : $article[delailivraison_txt]";	
	//description
	$article[rangements_txt] = str_replace("<br>"," - ",$article[rangements_txt]);
	$article[rangements_txt] = str_replace("\n","",$article[rangements_txt]);
	$article[rangements_txt] = substr($article[rangements_txt], 0, -3);
	$descarticle = "Porte : $article[version_porte] - Rangement inclus : $article[rangements_txt]";
	// references
	$artcode = "$article[artcode]-";
	$artcode .= abbreviation_codecouleur($couleur,$article[version_porte]);
	$artcode .= "-".abbreviation_versionporte($article[version_porte]);
	if (eregi("combiné",$article[libelleFR]))
		$artcode .= "M";
	else
		$artcode .= "D";
			
	if (eregi("vitrée",$article[version_porte]) and !eregi("pleine",$article[version_porte]))	
		{
		$noir = $DB_site2->query_first("select count(*) from article_caractval inner join caracteristiquevaleur using(caractvalid) where artid = '$article[artid]'  and caractid = '2' and caractvalid = '7'");
		$inox = $DB_site2->query_first("select count(*) from article_caractval inner join caracteristiquevaleur using(caractvalid) where artid = '$article[artid]'  and caractid = '2' and caractvalid = '8'");
		$naturel = $DB_site2->query_first("select count(*) from article_caractval inner join caracteristiquevaleur using(caractvalid) where artid = '$article[artid]'  and caractid = '2' and caractvalid = '12'");
		$nb_possiblites = $noir[0] + $inox[0] + $naturel[0];
		if ($noir[0] == "1" and $naturel[0] == "1")
			{
			$artcode .= "N";
			$descarticle .= " - Cadre Vitre :  Alu Noir (Naturel sur simple demande)";
			}
		elseif ($inox[0] == "1" and $noir[0] == "0" and $naturel[0] == "0")
			{
			$artcode .= "I";
			$descarticle .= " - Cadre Vitre :  Inox";
			}	
		elseif ($noir[0] == "1" or $naturel[0] == "1")
			{
			if ($noir[0] == "1")
				{
				$artcode .= "N";
				$descarticle .= " - Cadre Vitre :  Alu Noir";
				}
			elseif ($naturel[0] == "1")
				{
				$artcode .= "S";
				$descarticle .= " - Cadre Vitre :  Alu Naturel";
				}	
			}	
		}
	$titrearticle = "$article[libelleFR] - $article[gamme] - $artcode - Nbre T° : $article[nbr_temperature] - Bouteilles : $article[capacite_bouteilles] (Min/Max : $article[capacite_mini]/$article[capacite_maxi]) - Coloris : ";
	$titrearticle .= "$couleur";
	
	$optionporte = $DB_site2->query_first("SELECT COUNT(*) FROM article_caractval WHERE artid = '$article[artid]' AND caractvalid = '13'");	
	if ($optionporte[0] > 0)
		$descarticle .= " - Charnières Porte : à droite ( à Gauche sur simple demande)";
	$descarticle .= " - T° installation cave : $article[temperature_cave] - Pas de filtre à charbon - Pas de Vibration : Moteur sur système anti-vibration - Cave Sans Entretien - Consommation €/an : $article[conso_euro] TTC";
	$descarticle .= " - Régulation : $article[regulation] - Affichage : $article[affichage] - Hygrométrie : ".stripslashes($article[hygrometrie])." - 4 Pieds sur vérins réglables - Serrures : $article[serrure] - Eclairage : $article[eclairage] - Interrupteur Marche/Arrêt : $article[interrupteur]";
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
	$ligne.="Cave à vin";
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