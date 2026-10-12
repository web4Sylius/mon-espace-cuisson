<?php
set_time_limit(600) ;

require "admin_global.php";
include "./header.php";
include "./menu.php";

echo "<td valign=\"top\">
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
   	<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
		<tr>
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Importer des articles</font>
			</td>
		</tr>
		<tr>
			<td background=\"images2/cadre_r3_c2.gif\" width=\"100%\" height=\"2\">
			</td>
		</tr>
	</table>
   </td>
   <td><img name=\"cadre_r2_c5\" src=\"images2/cadre_r2_c5.gif\" width=\"11\" height=\"27\" border=\"0\" alt=\"\"></td>
   <td background=\"images2/cadre_r2_c6.gif\" width=\"100%\" height=\"27\" align=\"center\" valign=\"middle\">
   </td>
   <td><img name=\"cadre_r2_c8\" src=\"images2/cadre_r2_c8.gif\" width=\"13\" height=\"27\" border=\"0\" alt=\"\"></td>
  </tr>
</table>

</tr></td>
<tr><td> 

<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td background=\"images2/cadre_r4_c2.gif\" width=\"3\" height=\"100%\"></td>
   <td align=\"justify\" valign=\"middle\"><br>
   	<font class=\"txt_2\">
		<table align=\"center\" width=\"100%\">
			<tr>
				<td align=\"center\" valign=\"middle\" width=\"100%\">" ;



// import
if ($action == "import")
	{
	$msg_erreur_import = "" ;
	$msg_import = "" ;
	$nb_art = 0;
	if (isset($fichier_mails) and $fichier_mails != "")
		{
		if ($_FILES['fichier_mails']['name']!="")
			{
			$nom_fic=$rootpath."configurations/$host/images/importcsv/import.csv" ;
			copier_image($nom_fic,"fichier_mails") ;
			$rh = fopen($nom_fic, 'rb');
			if ($rh) 
				{
				$row = 1 ;
				while ($data = fgetcsv ($rh, 10000, ";")) 
					{
					$num = count ($data);
					for ($c = 0 ; $c < $num ; $c++) 
						{
						$montableau[$row][$c] = $data[$c] ;
						}
					$row++;
					}
				}
			fclose($rh);				
			$folder = $rootpath."configurations/$host/images/importcsv";
			$dossier = opendir($folder);
			while ($fichier = readdir($dossier))
				{
				if ($fichier != "." && $fichier != "..")	
					{
					if (file_exists($folder."/import.csv"))
						{
						unlink($folder."/import.csv");
						}
					}
				}
			if ((isset($montableau)) and (count($montableau) > 0))
				{
				foreach ($montableau as $key => $value) 
					{
					//type produit
					$artcode = $montableau[$key][0] ; 
					$cat_1 = $montableau[$key][2] ; 
					$cat_2 = $montableau[$key][3] ; 
					$cat_3 = $montableau[$key][4] ; 
					$cat_4 = $montableau[$key][5] ; 
					$cat_5 = $montableau[$key][6] ; 
					$cat_6 = $montableau[$key][7] ;
					
					//marque gamme modele 
					$libelleFR = $montableau[$key][9] ;
					$libelleFR = addslashes($libelleFR) ;
					$libmarque = $montableau[$key][10] ;
					$gamme = $montableau[$key][11] ;
					$modele = $montableau[$key][12] ;
					$modele = addslashes($modele) ;
					$artcode_eurocave = $montableau[$key][13] ;
					
					//information fiche article
					$descriptionFR = $montableau[$key][15] ;
					$descriptionFR = addslashes($descriptionFR) ;
					$nbr_temperature = $montableau[$key][16] ;
					$capacite_bouteilles = $montableau[$key][17] ;
					$capacite_mini = $montableau[$key][18] ;
					$capacite_maxi = $montableau[$key][19] ;
					$temperature_cave = $montableau[$key][20] ;
					$fonction_hiver = $montableau[$key][21] ;
					$filtre_charbon = $montableau[$key][22] ;
					$absence_charbon = $montableau[$key][23] ;
					$version_porte = $montableau[$key][24] ;
					$coloris = $montableau[$key][25] ;
					$garantie_incluse = $montableau[$key][26] ;
					$hauteur = $montableau[$key][27] ;
					$largeur = $montableau[$key][28] ;
					$profondeur = $montableau[$key][29] ;
					$conso_kw = $montableau[$key][30] ;
					$conso_euro = $montableau[$key][31] ;
					$poid = $montableau[$key][32] ;
					$poid = $poid * 1000; // on convertit en grammes
					$selection_expert = $montableau[$key][33] ;
					$avis_expert = $montableau[$key][34] ;
					
					//option garantie
					$garantie2ans = $montableau[$key][63] ;
					$garantie2ansprix = $montableau[$key][64] ;
					$garantie2ansprix = str_replace(" €","",$garantie2ansprix);
					$garantie2ansprix = str_replace(",",".",$garantie2ansprix);
					if ($garantie2ansprix == "x")
						$garantie2ansprix = "";					
					$garantie5ans = $montableau[$key][65] ;
					$garantie5ansprix = $montableau[$key][66] ;
					$garantie5ansprix = str_replace(" €","",$garantie5ansprix);
					$garantie5ansprix = str_replace(",",".",$garantie5ansprix);
					if ($garantie5ansprix == "x")
						$garantie5ansprix = "";					
					
					// prix
					$paht = $montableau[$key][68] ;
					$paht = str_replace(" €","",$paht);
					$paht = str_replace(",",".",$paht);
					if ($paht == "X")
						$paht = 0;
					$prix = $montableau[$key][70] ;
					$prix = str_replace(" €","",$prix);
					$prix = str_replace(",",".",$prix);
					if ($prix == "x")
						$prix = 0;
					$prixconseilleht = $montableau[$key][71] ;
					$prixconseilleht = str_replace(" €","",$prixconseilleht);
					$prixconseilleht = str_replace(",",".",$prixconseilleht);
					if ($prixconseilleht == "X")
						$prixconseilleht = 0;
					$prixht = $montableau[$key][72] ;
					$prixht = str_replace(" €","",$prixht);
					$prixht = str_replace(",",".",$prixht);
					if ($prixht == "X")
						$prixht = 0;
					$ecopartht = $montableau[$key][73] ;
					$ecopartht = str_replace(" €","",$ecopartht);
					$ecopartht = str_replace(",",".",$ecopartht);
					if ($ecopartht == "X")
						$ecopartht = 0;
					$ecopart = 	$ecopartht * 1.196;
					$ecopart = number_format($ecopart, 2, '.', ' ');
					// on ajoute l'eco_part au prix
					$prix += $ecopart;
					$prixht += $ecopartht;
					$prixconseilleht += $ecopartht;
					//livraison
					$delailivraison = $montableau[$key][77] ;
					
					//regulation
					/*$regulation = $montableau[$key][79] ;
					$boutons_reglage = $montableau[$key][80] ;
					$reglage = $montableau[$key][81] ;
					$affichage = $montableau[$key][82] ;
					
					//equipements
					$hygrometrie = $montableau[$key][85] ;
					$hygrometre = $montableau[$key][86] ;
					$hygrometre = addslashes($hygrometre) ;
					$pieds_reglables = $montableau[$key][87] ;
					$serrure = $montableau[$key][88] ;
					$poignee = $montableau[$key][88] ;
					$modularite_interieure = $montableau[$key][90] ;
					$roulettes = $montableau[$key][91] ;
					$vide_poche = $montableau[$key][92] ;
					$clayette_fil = $montableau[$key][93] ;
					$panier_transport = $montableau[$key][94] ;
					$vinokit = $montableau[$key][95] ;
					$tablette = $montableau[$key][96] ;
					$clayette_hetre = $montableau[$key][97] ;
					$tapis_basse = $montableau[$key][98] ;
					
					//technologies
					$production_froid = $montableau[$key][99] ; //97
					$production_chaud = $montableau[$key][ 100] ;
					$declenchement_chaud = $montableau[$key][101] ;
					$fonction_hiver_techno = $montableau[$key][102] ;
					$parois_laterales = $montableau[$key][103] ;
					$parois_fond = $montableau[$key][104] ;
					$revetement_exterieur = $montableau[$key][105] ;
					$isolation = $montableau[$key][106] ;
					
					//performances et consommation d'énergie
					$puissance_electrique = $montableau[$key][108] ;
					$intensite_electrique = $montableau[$key][109] ;
					$consommation_h = $montableau[$key][110] ;
					$consommation_an = $montableau[$key][111] ;
					$niveau_bruit = $montableau[$key][112] ;
					
					//emballage
					$emballage_hauteur = $montableau[$key][114] ;
					$emballage_largeur = $montableau[$key][115] ;
					$emballage_profondeur = $montableau[$key][116] ;
					$emballage_poids = $montableau[$key][117] ;*/
					
					//categorie
					if ($cat_1 != "")
						$catid = "1";
					elseif ($cat_2 != "")
						$catid = "2";
					elseif ($cat_3 != "")
						$catid = "3";
					elseif ($cat_4 != "")
						$catid = "4";
					elseif ($cat_5 != "")
						$catid = "5";
					elseif ($cat_6 != "")
						$catid = "6";
						
					
					if ($artcode  != "")
						{
						$nb_art ++;
						$DB_site2->query("INSERT INTO article
						(catid, artcode, libelleFR, gamme, modele, artcode_eurocave, descriptionFR, nbr_temperature, capacite_bouteilles, capacite_mini, capacite_maxi, temperature_cave, 
						fonction_hiver, filtre_charbon, absence_charbon, version_porte, coloris, garantie_incluse, hauteur, largeur, profondeur, conso_kw, conso_euro, poid, selection_expert, 
						avis_expert, paht, prix, prixconseilleht, prixht, ecopartht, ecopart, delailivraison) VALUES
						('$catid', '$artcode', '$libelleFR', '$gamme', '$modele', '$artcode_eurocave', '$descriptionFR', '$nbr_temperature', '$capacite_bouteilles', '$capacite_mini', '$capacite_maxi', '$temperature_cave', 
						'$fonction_hiver', '$filtre_charbon', '$absence_charbon', '$version_porte', '$coloris', '$garantie_incluse', '$hauteur', '$largeur', '$profondeur', '$conso_kw', '$conso_euro', '$poid', '$selection_expert', 
						'$avis_expert', '$paht', '$prix', '$prixconseilleht', '$prixht', '$ecopartht', '$ecopart', '$delailivraison')");
						$artid=$DB_site2->insert_id(); 
						
						// marque
						$marque = $DB_site2->query_first("SELECT * FROM marque WHERE libelle like '%$libmarque%'");
						if ($marque[marqueid] == "")
							{
							$DB_site2->query("INSERT INTO marque (libelle) VALUES ('$libmarque')");
							$marqueid = $DB_site2->insert_id();
							}
						else
							$marqueid = $marque[marqueid];
						$DB_site2->query("INSERT INTO article_marque (artid, marqueid) VALUES ('$artid', '$marqueid')");	
						
						//position
						$maxposition = $DB_site2->query_first("SELECT MAX(pageposition) FROM article WHERE catid = '$catid'");
						$position = $maxposition[0] + 1;
						$DB_site2->query("UPDATE article SET page = '1', pageposition = '$position' WHERE artid = '$artid'");
						
						//garantie
						if ($garantie2ans != "" or $garantie5ans != "")
							{
							// on associe d'abord la caracteristique au rayon
							$caract_associee = $DB_site2->query_first("SELECT * FROM categorie_caracteristique WHERE catid = '$catid' AND caractid = '3'");
							if ($caract_associee[catid] == "")
								$DB_site2->query("INSERT INTO categorie_caracteristique (catid, caractid) VALUES ('$catid', '3')");	
							//on met à jour la table article_caractval
							$DB_site2->query("INSERT INTO article_caractval (artid, caractvalid, differenceprix) VALUES ('$artid', '9', '0')");								
							if ($garantie2ans != "")
								$DB_site2->query("INSERT INTO article_caractval (artid, caractvalid, differenceprix) VALUES ('$artid', '10', '$garantie2ansprix')");	
							if ($garantie5ans != "")
								$DB_site2->query("INSERT INTO article_caractval (artid, caractvalid, differenceprix) VALUES ('$artid', '11', '$garantie5ansprix')");	
							}					
						}
					}
				$msg_erreur_import = "$nb_art articles ajoutés !" ;		
				}
			}
		else
			{
			$msg_erreur_import = "Vous devez saisir un chemin !" ;
			}
		}
	} 


echo "<br><br>
<h3>Importer un fichier CSV de fiches produits</h3>
<form method=\"post\" enctype=\"multipart/form-data\" action=\"import.php\">
	<input type=\"hidden\" name=\"action\" value=\"import\">
	<font class=\"cost\">$msg_import $msg_erreur_import</font>
	<table class=\"table_caracts\">
		<tr>
			<td align=\"center\" colspan=\"3\" valign=\"middle\">Le fichier doit être de type csv (.csv).<br><bR></td>
		</tr>
		<tr>
			<td align=\"right\">Fichier : </td>
			<td align=\"left\"><input type=\"file\" name=\"fichier_mails\"></td>
		</tr>
		<tr>
			<td align=\"center\" colspan=\"2\"><input type=\"submit\" value=\"Importer\"></td>
		</tr>
	</table>
<br><br>
</form>" ;
		
echo "</tr>	
	</table>	
 </font>
</td>
<td background=\"images2/cadre_r4_c10.gif\" width=\"3\" height=\"100%\" border=\"0\" alt=\"\"></td>
</tr>
</table>
</td>
</tr>
<tr><td>

<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td rowspan=\"2\"><img name=\"cadre_r6_c2\" src=\"images2/cadre_r6_c2.gif\" width=\"10\" height=\"26\" border=\"0\" alt=\"\"></td>
   <td align=\"center\" height=\"23\" width=\"100%\"></td>
   <td rowspan=\"2\"><img name=\"cadre_r6_c9\" src=\"images2/cadre_r6_c9.gif\" width=\"8\" height=\"26\" border=\"0\" alt=\"\"></td>
  </tr>
  <tr>
   <td background=\"images2/cadre_r7_c4.gif\" width=\"100%\" height=\"3\"></td>
  </tr>
</table>
</td></tr></table>
</td>
</tr><tr>
</tr></table>
				  </td>
              </tr>
            </table>
		  </td>
        </tr>
";

include "./footer.php";


// fonction php images
function redimentionner_image ($nom_fichier, $destination, $largeur, $hauteur)
	{
	if (substr ($destination, -3) == "jpg") 
		{
		$imagetmp = imagecreatefromjpeg ($nom_fichier) ;
		}
	if (substr ($destination, -3) == "gif") 
		{
		$imagetmp = imagecreatefromgif ($nom_fichier) ;
		}
	if (substr ($destination, -3) == "png") 
		{
		$imagetmp = imagecreatefrompng ($nom_fichier) ;
		}
	$imagesize = getimagesize ($nom_fichier) ;
	if ($imagesize[0] > $largeur or $imagesize[1] > $hauteur)
		{
		$largeur_en_cours = $imagesize[0] ;
		$hauteur_en_cours = $imagesize[1] ;
		if ($largeur_en_cours > $largeur) // si largeur > 112
			{
			$width = $largeur ; // largeur = 112
			$height = floor ($largeur * $hauteur_en_cours / $largeur_en_cours) ; // hauteur = 112 * (768/1024)
			$largeur_en_cours = $width ;
			$hauteur_en_cours = $height ;
			} 
		if ($hauteur_en_cours > $hauteur) 
			{
			$width = floor($largeur_en_cours * $hauteur / $hauteur_en_cours) ;
			$height = $hauteur ;
			$largeur_en_cours = $width ;
			$hauteur_en_cours = $height ;
			}
		$image = imagecreatetruecolor ($largeur_en_cours, $hauteur_en_cours) ;
		imagecopyresampled ($image, $imagetmp, 0, 0, 0, 0, $largeur_en_cours, $hauteur_en_cours, $imagesize[0], $imagesize[1]) ;
		if (substr ($destination, -3) == "jpg") 
			{
			imagejpeg ($image, $destination , 80) ;
			}
		if (substr ($destination, -3) == "gif") 
			{
			imagegif ($image, $destination , 80) ;
			}
		if (substr ($destination, -3) == "png") {
			
			imagepng ($image, $destination , 80) ;
			}
		chmod ($destination, 0777) ;
		imagedestroy ($image) ;
		} 
	else 
		{
		$image = imagecreatetruecolor ($imagesize[0], $imagesize[1]) ;
		imagecopyresampled ($image, $imagetmp, 0, 0, 0, 0, $imagesize[0], $imagesize[1], $imagesize[0], $imagesize[1]) ;
		if (substr ($destination, -3) == "jpg") 
			{
			imagejpeg($image, $destination, 80) ;
			}
		if (substr ($destination, -3) == "gif") 
			{
			imagegif ($image, $destination, 80) ;
			}
		if (substr ($destination, -3) == "png") 
			{
			imagepng ($image, $destination, 80) ;
			}
		chmod ($destination, 0777) ;
		imagedestroy ($image) ;
		}
	}

function copier_image ($nom_fic, $imagename)
	{
	if ($OpenLocal = fopen ($_FILES[$imagename]['tmp_name'], "rb")) 
		{
		$LocalFileContent = fread ($OpenLocal, $_FILES[$imagename]['size']) ;
		} 
	else 
		{
		fclose ($OpenLocal) ;
		die ("<b>Erreur :</b> Impossible d'ouvrir le fichier local '" . $_POST[$imagename] . ".") ;
		}
	fclose ($OpenLocal) ;
	if ($OpenRemote = fopen ($nom_fic, "wb")) 
		{
		if (fwrite ($OpenRemote, $LocalFileContent)) 
			{
			fclose ($OpenRemote) ;
			}
		} 
	else 
		{
		fclose ($OpenRemote) ;
		die ("<b>Erreur :</b> Impossible d'ouvrir le fichier distant $nom_fic") ;
		}
	chmod ("$nom_fic", 0777) ;
	}

function copier_image_url ($nom_fic, $url, $extension)
	{
	$ok = "non" ;
	switch ($extension)
		{
		case "gif":
			if ($monimage = @imagecreatefromgif($url))
				{
				$ok = "oui" ;
				imagegif ($monimage, $nom_fic , 80) ;
				chmod ($nom_fic, 0777) ;
				imagedestroy ($monimage) ;
				}
			break ;
		case "jpg":
			if ($monimage = @imagecreatefromjpeg($url))
				{
				$ok = "oui" ;
				imagejpeg ($monimage, $nom_fic , 80) ;
				chmod ($nom_fic, 0777) ;
				imagedestroy ($monimage) ;
				}
			break ;
		case "png":
			if ($monimage = @imagecreatefrompng($url))
				{
				$ok = "oui" ;
				imagepng ($monimage, $nom_fic , 80) ;
				chmod ($nom_fic, 0777) ;
				imagedestroy ($monimage) ;
				}
			break ;
		}
	if ($ok == "oui")
		{
		return true ;
		}
	else
		{
		return false ;
		}
	}

?>