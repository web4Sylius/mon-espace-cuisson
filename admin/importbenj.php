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
						
					//if ($key!=1){
						//type produit
						$artcode = $montableau[$key][0] ; 
						
						$cat_1 = $montableau[$key][2] ; 
						$cat_2 = $montableau[$key][3] ; 
						$cat_3 = $montableau[$key][4] ; 
						$cat_4 = $montableau[$key][5] ; 
						$cat_5 = $montableau[$key][6] ; 
						$cat_6 = $montableau[$key][7] ;
						$cat_7 = $montableau[$key][8] ; 
						$cat_8 = $montableau[$key][9] ; 
						$cat_9 = $montableau[$key][10] ; 
						$cat_10 = $montableau[$key][11] ; 
						$cat_11 = $montableau[$key][12] ; 
						$cat_12 = $montableau[$key][13] ;
						$cat_13 = $montableau[$key][14] ;
						
						//marque gamme modele 
						$libelleFR = $montableau[$key][16] ; //ok
						$libelleFR = addslashes($libelleFR) ; //ok
						$libmarque = $montableau[$key][17] ; //ok
						$gamme = $montableau[$key][18] ; //ok
						$modele = $montableau[$key][19] ; //ok
						$modele = addslashes($modele) ; //ok
						$artcode_eurocave = $montableau[$key][20] ; //ok
						
						//information fiche article
						$paysfabrication = $montableau[$key][23] ; //ok
						$descriptionFR = $montableau[$key][24] ; //ok
						$descriptionFR = addslashes($descriptionFR) ; //ok
						$nbr_temperature = $montableau[$key][25] ; //ok
						$capacite_bouteilles = $montableau[$key][26] ; //ok
						$capacite_mini = $montableau[$key][27] ;//ok
						$capacite_maxi = $montableau[$key][28] ;//ok
						$temperature_cave = $montableau[$key][29] ;//ok
						$temperature_cave = addslashes($temperature_cave) ; //ok
						$fonction_hiver = $montableau[$key][30] ;//ok
						$filtre_charbon = $montableau[$key][31] ; //ok
						$absence_vibration = $montableau[$key][32] ; //ok
						$version_porte = $montableau[$key][33] ; //ok
						$coloris = $montableau[$key][34] ; //ok
						$coloris = addslashes($coloris) ; //ok
						$colorisporte = $montableau[$key][35] ; //ok
						$dimensions = $montableau[$key][36] ;//ok
						$garantie_incluse = $montableau[$key][37] ; //ok
						$garantie_incluse = addslashes($garantie_incluse) ; //ok
						$hauteur = $montableau[$key][38] ; //ok
						$largeur = $montableau[$key][39] ;//ok
						$profondeur = $montableau[$key][40] ;//ok
						$conso_kw = $montableau[$key][41] ;//ok
						$conso_euro = $montableau[$key][42] ;//ok
						$poid = $montableau[$key][43] ;//ok
						$poid = $poid * 1000; // on convertit en grammes
						$selection_expert = $montableau[$key][44] ; //ok
						$avis_expert = $montableau[$key][45] ;//ok
						
						
						//caracteristique Coloris de la cave (caract id = 1 )
						$coloriscave1 = $montableau[$key][88] ;
						$coloriscave2 = $montableau[$key][89] ;
						$coloriscave3 = $montableau[$key][90] ;
						$coloriscave4 = $montableau[$key][91] ;
						$coloriscave5 = $montableau[$key][92] ;
						$coloriscave6 = $montableau[$key][93] ;
						$coloriscave7 = $montableau[$key][94] ;
						$coloriscave8 = $montableau[$key][95] ;
						$coloriscave9 = $montableau[$key][96] ;
						$coloriscave10 = $montableau[$key][97] ;
						$coloriscave11 = $montableau[$key][98] ;
						$coloriscave12 = $montableau[$key][99] ;
						$coloriscave13 = $montableau[$key][100] ;
						$coloriscave14 = $montableau[$key][101] ;
						$coloriscave15 = $montableau[$key][102] ;
						$coloriscave16 = $montableau[$key][103] ;
						$coloriscave17 = $montableau[$key][104] ;
						$coloriscave18 = $montableau[$key][105] ;
						$coloriscave19 = $montableau[$key][106] ;
						$coloriscave20 = $montableau[$key][107] ;
						$coloriscave21 = $montableau[$key][108] ;
						
						//caracteristique coloris cadre vitrée (caract id = 2 )
						$coloriscadre1 = $montableau[$key][110];
						$coloriscadre2 = $montableau[$key][111];
						$coloriscadre3 = $montableau[$key][112];
						$coloriscadre4 = $montableau[$key][113];
						
						
						//option garantie OK
						$garantie2ans = $montableau[$key][151] ;
						$garantie2ansprix = $montableau[$key][152] ;
						$garantie2ansprix = str_replace(" €","",$garantie2ansprix);
						$garantie2ansprix = str_replace(",",".",$garantie2ansprix);
						if ($garantie2ansprix == "x")
							$garantie2ansprix = "";					
						$garantie5ans = $montableau[$key][153] ;
						$garantie5ansprix = $montableau[$key][154] ;
						$garantie5ansprix = str_replace(" €","",$garantie5ansprix);
						$garantie5ansprix = str_replace(",",".",$garantie5ansprix);
						if ($garantie5ansprix == "x")
							$garantie5ansprix = "";					
						
						// prix
						$paht = $montableau[$key][156] ; //ok
						$paht = str_replace(" €","",$paht); //ok
						$paht = str_replace(",",".",$paht); //ok
						if ($paht == "X")
							$paht = 0;
						
						$prixconseilleht = $montableau[$key][157] ; //ok
						$prixconseilleht = str_replace(" €","",$prixconseilleht); //ok
						$prixconseilleht = str_replace(",",".",$prixconseilleht); //ok
						if ($prixconseilleht == "X")
							$prixconseilleht = 0;
	
						$prixht = $montableau[$key][158] ; //ok
						$prixht = str_replace(" €","",$prixht);
						$prixht = str_replace(",",".",$prixht);
						if ($prixht == "X")
							$prixht = 0;
	
						$prix = $montableau[$key][158] ; //ok
						$prix = str_replace(" €","",$prix);
						$prix = str_replace(",",".",$prix);
						if ($prix == "x")
							$prix = 0;
						$prix+=$prix*19.6/100;
							
						$ecopartht = $montableau[$key][159] ; //ok
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
						$textelivraison = $montableau[$key][163] ;//ok
						$delailivraison = $montableau[$key][164] ;//ok
						
						//regulation
						$regulation = $montableau[$key][166] ; //ok
						//$boutons_reglage = $montableau[$key][80] ;
						//$reglage = $montableau[$key][81] ;
						//$affichage = $montableau[$key][82] ;
						
						$temperature = $montableau[$key][167] ; //ok
						$thermometre = $montableau[$key][168] ; //ok
						
						
						//equipements
						$hygrometrie = $montableau[$key][173] ;//ok
						$hygrometrie = addslashes($hygrometrie) ;
						$hygrometre = $montableau[$key][174] ;//ok$boutonreglage = $montableau[$key][169] //ok
						$reglage = $montableau[$key][170] ; //ok
						$affichage = $montableau[$key][171] ; //ok
						$hygrometre = addslashes($hygrometre) ;
						$pieds_reglables = $montableau[$key][175] ; //ok
						$serrure = $montableau[$key][176] ;//ok
						$poignee = $montableau[$key][177] ;//ok
						$modularite_interieure = $montableau[$key][178] ;//ok
						$roulettes = $montableau[$key][179] ;//ok
						$roulettes = addslashes($roulettes) ;
						$eclairage = $montableau[$key][180] ;//ok
						$interrupteur = $montableau[$key][181] ; //ok
						$boutontest = $montableau[$key][182] ; //ok
						$vide_poche = $montableau[$key][183] ;//ok
						$clayette_fil = $montableau[$key][184] ;//ok
						$panier_transport = $montableau[$key][185] ; //ok
						$vinokit = $montableau[$key][186] ;//ok
						$tablette = $montableau[$key][187] ;//ok
						$clayette_hetre = $montableau[$key][188] ;//ok
						$tapis_basse = $montableau[$key][189] ; //ok
						
						//technologies
						$production_froid = $montableau[$key][191] ; //ok
						$production_froid = addslashes($production_froid) ;
						$production_chaud = $montableau[$key][192] ; //ok
						$declenchement_chaud = $montableau[$key][193] ;//ok
						$fonction_hiver_techno = $montableau[$key][194] ;//ok
						$parois_laterales = $montableau[$key][195] ;//ok
						$parois_laterales = addslashes($parois_laterales) ;
						$parois_fond = $montableau[$key][196] ;//ok
						$parois_fond = addslashes($parois_fond) ;
						$revetement_exterieur = $montableau[$key][197] ;//ok
						$isolation = $montableau[$key][198] ;//ok
						
						//performances et consommation d'énergie
						$puissance_electrique = $montableau[$key][200] ; //ok
						$intensite_electrique = $montableau[$key][201] ;//ok
						$consommation_h = $montableau[$key][202] ;//ok
						$consommation_an = $montableau[$key][203] ;//ok
						$niveau_bruit = $montableau[$key][204] ;//ok
						
						//emballage
						$emballage_hauteur = $montableau[$key][206] ;//ok
						$emballage_largeur = $montableau[$key][207] ;//ok
						$emballage_profondeur = $montableau[$key][208] ;//ok
						$emballage_poids = $montableau[$key][209] ;//ok
						
						//categorie
						if ($cat_1 != "")
							$catid = "1";
						elseif ($cat_2 != "")
							$catid = "13";
						elseif ($cat_3 != "")
							$catid = "2";
						elseif ($cat_4 != "")
							$catid = "3";
						elseif ($cat_5 != "")
							$catid = "9";
						elseif ($cat_6 != "")
							$catid = "4";
						elseif ($cat_7 != "")
							$catid = "10";
						elseif ($cat_8 != "")
							$catid = "5";
						elseif ($cat_9 != "")
							$catid = "6";
						elseif ($cat_10 != "")
							$catid = "7";
						elseif ($cat_11 != "")
							$catid = "8";
						elseif ($cat_12 != "")
							$catid = "12";
						elseif ($cat_13 != "")
							$catid = "11";
						
						if ($paysfabrication=="Fabrication Francaise")
							$paysfabricationid=0;
						if ($paysfabrication=="Fabrication Européenne")
							$paysfabricationid=1;
						if ($paysfabrication=="Fabrication Asiatique")
							$paysfabricationid=2;
							
						
						if ($artcode  != "")
							{
							$nb_art ++;
							$DB_site2->query("INSERT INTO article
							(catid, artcode, libelleFR, gamme, modele, artcode_eurocave, paysfabrication, descriptionFR, nbr_temperature, capacite_bouteilles, capacite_mini, capacite_maxi, temperature_cave, 
							fonction_hiver, filtre_charbon, absence_vibration, version_porte, coloris, coloris_porte, garantie_incluse, dimensions, hauteur, largeur, profondeur, conso_kw, conso_euro, poid, selection_expert, 
							avis_expert, paht, prix, prixconseilleht, prixht, ecopartht, ecopart, delailivraison_txt, delailivraison, regulation, temperature, thermometre, hygrometrie, hygrometre, pieds_reglables, serrure, poignee, modularite_interieure,
							roulettes, eclairage, interrupteur, bouton_test, vide_poche, clayette_fil, panier_transport, vinokit, tablette, clayette_hetre, tapis_basse, production_froid, production_chaud, declenchement_chaud, fonction_hiver_techno,
							parois_laterales, parois_fond, revetement_exterieur, isolation, puissance_electrique, intensite_electrique, consommation_h, consommation_an, niveau_bruit, emballage_hauteur, emballage_largeur, emballage_profondeur, emballage_poids,
							boutons_reglage, reglage, affichage) VALUES
							('$catid', '$artcode', '$libelleFR', '$gamme', '$modele', '$artcode_eurocave', '$paysfabricationid', '$descriptionFR', '$nbr_temperature', '$capacite_bouteilles', '$capacite_mini', '$capacite_maxi', '$temperature_cave', 
							'$fonction_hiver', '$filtre_charbon', '$absence_vibration', '$version_porte', '$coloris', '$colorisporte', '$garantie_incluse', '$dimensions', '$hauteur', '$largeur', '$profondeur', '$conso_kw', '$conso_euro', '$poid', '$selection_expert', 
							'$avis_expert', '$paht', '$prix', '$prixconseilleht', '$prixht', '$ecopartht', '$ecopart', '$textelivraison', '$delailivraison', '$regulation', '$temperature', '$thermometre', '$hygrometrie', '$hygrometre', '$pieds_reglables', '$serrure', '$poignee', '$modularite_interieure',
							'$roulettes','$eclairage', '$interrupteur', '$boutontest', '$vide_poche', '$clayette_fil', '$panier_transport', '$vinokit', '$tablette', '$clayette_hetre', '$tapis_basse', '$production_froid', '$production_chaud', '$declenchement_chaud', '$fonction_hiver_techno',
							'$parois_laterales', '$parois_fond', '$revetement_exterieur', '$isolation', '$puissance_electrique', '$intensite_electrique', '$consommation_h', '$consommation_an', '$niveau_bruit', '$emballage_hauteur', '$emballage_largeur', '$emballage_profondeur', '$emballage_poids',
							'$boutonreglage', '$reglage', '$affichage')");
							$artid=$DB_site2->insert_id(); 
							
							// marque
							$marque = $DB_site2->query_first("SELECT * FROM marque WHERE libelle like '%$libmarque%'");
							if ($marque[marqueid] == ""){
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
							if ($garantie2ans != "" or $garantie5ans != "")	{
								// on associe d'abord la caracteristique au rayon
								$caract_associee = $DB_site2->query_first("SELECT * FROM categorie_caracteristique WHERE catid = '$catid' AND caractid = '3'");
								if ($caract_associee[catid] == "")
									$DB_site2->query("INSERT INTO categorie_caracteristique (catid, caractid) VALUES ('$catid', '3')");	
								//on met à jour la table article_caractval
								$DB_site2->query("INSERT INTO article_caractval (artid, caractvalid, differenceprixht, differenceprix) VALUES ('$artid', '9', '0', '0')");								
								if ($garantie2ans != ""){
									$garantie2ansttc=$garantie2ans*1.196;
									$DB_site2->query("INSERT INTO article_caractval (artid, caractvalid, differenceprixht, differenceprix) VALUES ('$artid', '10', '$garantie2ansprix', '$garantie2ansttc')");	
								}
								if ($garantie5ans != ""){
									$garantie5ansttc=$garantie5ans*1.196;
									$DB_site2->query("INSERT INTO article_caractval (artid, caractvalid, differenceprixht, differenceprix) VALUES ('$artid', '11', '$garantie5ansprix', '$garantie5ansttc')");	
								}
							}					
							//Coloris de la cave
							if ($coloriscave1!="")
								$DB_site2->query("INSERT INTO article_caractval (artid, caractvalid, differenceprix) VALUES ('$artid', '4', '0')");
							if ($coloriscave2!="")
								$DB_site2->query("INSERT INTO article_caractval (artid, caractvalid, differenceprix) VALUES ('$artid', '1', '0')");
							if ($coloriscave3!="")
								$DB_site2->query("INSERT INTO article_caractval (artid, caractvalid, differenceprix) VALUES ('$artid', '6', '0')");
							if ($coloriscave4!="")
								$DB_site2->query("INSERT INTO article_caractval (artid, caractvalid, differenceprix) VALUES ('$artid', '3', '0')");
							if ($coloriscave5!="")
								$DB_site2->query("INSERT INTO article_caractval (artid, caractvalid, differenceprix) VALUES ('$artid', '15', '0')");
							if ($coloriscave6!="")
								$DB_site2->query("INSERT INTO article_caractval (artid, caractvalid, differenceprix) VALUES ('$artid', '5', '0')");
							if ($coloriscave7!="")
								$DB_site2->query("INSERT INTO article_caractval (artid, caractvalid, differenceprix) VALUES ('$artid', '2', '0')");
							
							if ($coloriscave8!="")
								$DB_site2->query("INSERT INTO article_caractval (artid, caractvalid, differenceprix) VALUES ('$artid', '22', '0')");
							if ($coloriscave9!="")
								$DB_site2->query("INSERT INTO article_caractval (artid, caractvalid, differenceprix) VALUES ('$artid', '23', '0')");
							if ($coloriscave10!="")
								$DB_site2->query("INSERT INTO article_caractval (artid, caractvalid, differenceprix) VALUES ('$artid', '24', '0')");
							if ($coloriscave11!="")
								$DB_site2->query("INSERT INTO article_caractval (artid, caractvalid, differenceprix) VALUES ('$artid', '25', '0')");
								
							if ($coloriscave12!="")
								$DB_site2->query("INSERT INTO article_caractval (artid, caractvalid, differenceprix) VALUES ('$artid', '26', '0')");
							if ($coloriscave13!="")
								$DB_site2->query("INSERT INTO article_caractval (artid, caractvalid, differenceprix) VALUES ('$artid', '27', '0')");
							if ($coloriscave14!="")
								$DB_site2->query("INSERT INTO article_caractval (artid, caractvalid, differenceprix) VALUES ('$artid', '28', '0')");
							if ($coloriscave15!="")
								$DB_site2->query("INSERT INTO article_caractval (artid, caractvalid, differenceprix) VALUES ('$artid', '29', '0')");
							if ($coloriscave16!="")
								$DB_site2->query("INSERT INTO article_caractval (artid, caractvalid, differenceprix) VALUES ('$artid', '30', '0')");
						
						
							//coloris cadre vitré
							if ($coloriscadre1!="")
								$DB_site2->query("INSERT INTO article_caractval (artid, caractvalid, differenceprix) VALUES ('$artid', '7', '0')");
							if ($coloriscadre2!="")
								$DB_site2->query("INSERT INTO article_caractval (artid, caractvalid, differenceprix) VALUES ('$artid', '12', '0')");
							if ($coloriscadre3!="")
								$DB_site2->query("INSERT INTO article_caractval (artid, caractvalid, differenceprix) VALUES ('$artid', '8', '0')");
							if ($coloriscadre4!="")
								$DB_site2->query("INSERT INTO article_caractval (artid, caractvalid, differenceprix) VALUES ('$artid', '31', '0')");
							
							}
						}
					//}
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
<form method=\"post\" enctype=\"multipart/form-data\" action=\"importbenj.php\">
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