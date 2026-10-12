<?
require "../admin_global.php";
require "../fonctions.php";


$lstcatids = "6" ;
categories_filles(6) ;
$lstcatids .= ",8" ;
categories_filles(8) ;


$filename = $rootpath."admin/comparateur/pixplaceproduit.csv";
if (is_writable($filename)) {
	if (!$handle = fopen($filename, 'w')) {
		echo "Impossible d'ouvrir le fichier ($filename)";
		exit;
		}
	$nfichier = 0 ;
	$longueur = 0 ;
	$contenu = "Id du produit marchand		Langue		MPN		EAN		Id Catégorie		Id Segment		Id Marque		Libellé principal		Libellé secondaire		Description marketing\n" ;
	$longueur += strlen($contenu) ;

	if (fwrite($handle, $contenu) === FALSE) {
		echo "Impossible d'écrire dans le fichier ($filename)";
		exit;
		}
	else {
		fclose($handle);
		$handle = fopen($filename, 'w+') ;
		$i = 0 ;
		$DB_site2->query("truncate table pix_temp");
		$articles=$DB_site2->query("SELECT artid, artcode, a.catid, a.$libelle libarticle, capacite_bouteilles, nbr_temperature, version_porte, regulation, rangements_txt, capacite_mini, capacite_maxi, temperature_cave, dimensions, emballage_poids, a.$description, c.$libelle libcat, garantie_incluse, temperature, thermometre, reglage, affichage, hygrometrie, serrure, eclairage  FROM article a inner join categorie c using(catid) where active = '1' and commandable = '1' and a.image != '' and a.catid not in ($lstcatids) group by artcode order by a.catid, artcode") ;
		while ($article=$DB_site2->fetch_array($articles)) {
			$i++ ;
			$towrite = "" ;
			$libarticle = trim(html_entity_decode($article[libarticle])) ;
			if (strlen($libarticle." - $article[artcode]" < 255))
				$libarticle .= " - $article[artcode]" ;
			if (strlen($libarticle." - Bouteilles : $article[capacite_bouteilles]" < 255))
				$libarticle .= " - Bouteilles : $article[capacite_bouteilles]" ;
			$libarticle2 = "" ;
			if ($article[nbr_temperature])
				$libarticle2 .= "Nbre T° : $article[nbr_temperature]" ;
			if ($article[version_porte])
				$libarticle2 .= " - Porte : $article[version_porte]" ;
			if ($article[regulation])
				$libarticle2 .= " - Régulation : $article[regulation]" ;
			if ($article[filtre_charbon] == "Non")
				$libarticle2 .= " - Sans filtre à charbon" ;
			$article[$description] = str_replace(chr(10), " ", $article[$description]) ;
			$article[$description] = str_replace(chr(13), " ", $article[$description]) ;
			$article[$description] = str_replace(chr(27), " ", $article[$description]) ;
			$article[$description] = stripslashes($article[$description]) ;
			$descarticle = trim(html_entity_decode(strip_tags($article[$description]))) ;
			$descarticle .= " - Rangements inclus : $article[rangements_txt]" ;
			$descarticle .= " - Bouteilles (mini/maxi) : $article[capacite_bouteilles] ($article[capacite_mini] / $article[capacite_maxi])" ;
			$descarticle .= " - Température installation de la cave : $article[temperature_cave]" ;
			$descarticle .= " - Dimensions(mm)/Poids(kg) : $article[dimensions]/$article[emballage_poids]" ;
			$descarticle .= " - Garantie : $article[garantie_incluse]" ;
			$descarticle .= " - Température : $article[temperature]" ;
			$descarticle .= " - Thermomètre : $article[thermometre]" ;
			$descarticle .= " - Réglage : $article[reglage]" ;
			$descarticle .= " - Affichage : $article[affichage]" ;
			$descarticle .= " - Hygrometre : $article[hygrometrie]" ;
			$descarticle .= " - Serrure : $article[serrure]" ;
			$descarticle .= " - Eclairage : $article[eclairage]" ;
			$idmarque = "" ;
			$marque=$DB_site2->query_first("select marqueid from article_marque where artid = '$article[artid]'") ;
			if ($marque[marqueid] == 1)
				$idmarque = 63018 ;
			elseif ($marque[marqueid] == 2)
				$idmarque = 63019 ;
			elseif ($marque[marqueid] == 3)
				$idmarque = 63020 ;
				
			$parents = array();
			$tabparents = pixplace_arbo($DB_site2, $article[artid], $libelle) ;
			foreach ($tabparents as $numligne => $ligne) {
				$contenu = "" ;
				$contenu .= "$ligne[id]		" ;
				$contenu .= "		" ;
				$contenu .= "FR		" ;
				$contenu .= "		" ;
				$contenu .= "$ligne[codebarre]		" ;
				$contenu .= "36		" ;
				$contenu .= "3819		" ;
				$contenu .= "$idmarque		" ;
				$artcouleur = "" ;
				if ($ligne[couleur]) {
					$artcouleur = " - $ligne[couleur]" ;
					if (strlen($libarticle.$artcouleur < 255))
						$contenu .= "$libarticle$artcouleur		" ;
					else
						$contenu .= "$libarticle		" ;
					}
				else
					$contenu .= "$libarticle		" ;
				$contenu .= "$libarticle2		" ;
				$contenu .= "$descarticle" ;
				$contenu .= "\n" ;
					
				

				$towrite .= $contenu ;
				}
			unset($idproduitmarchandpere) ;


			if ($longueur + strlen($towrite) > 8000000) { // 8388608
				fclose($handle);
				$nfichier++ ;
				$filename = "comparateur/pixplaceproduit".$nfichier.".csv" ;
				$longueur = 0 ;
				$handle = fopen($filename, 'w') ;
				$contenu = "Id du produit marchand		Langue		MPN		EAN		Id Catégorie		Id Segment		Id Marque		Libellé principal		Libellé secondaire		Description marketing\n" ;
				if (fwrite($handle, $contenu) === FALSE) {
					echo "Impossible d'écrire dans le fichier ($filename)";
					}
				else {
					fclose($handle);
					$handle = fopen($filename, 'w+') ;
					fwrite($handle, $towrite) ;
					}
				}
			else {
				$longueur += strlen($towrite) ;
				fwrite($handle, $towrite) ;
				}
			//echo $towrite."<br><br><br>" ;
			}
		}

	fclose($handle);
	$nfichier = $nfichier + 1 ;

	echo "<script langugage=\"javascript\">window.location = '../lienspixplace.php?etape=1&nfichier=$nfichier';</script>" ;
	}
else
	echo "Le fichier $filename n'est pas accessible en écriture.";
?>