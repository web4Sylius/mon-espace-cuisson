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
if ($action == "import"){
	$msg_erreur_import = "" ;
	$msg_import = "" ;
	$nb_art = 0;
	if (isset($fichier_mails) and $fichier_mails != "")	{
		if ($_FILES['fichier_mails']['name']!=""){
			$nom_fic=$rootpath."configurations/$host/images/importcsv/import.csv" ;
			copier_image($nom_fic,"fichier_mails") ;
			$rh = fopen($nom_fic, 'rb');
			if ($rh) {
				$row = 1 ;
				while ($data = fgetcsv ($rh, 10000, ";")) {
					$num = count ($data);
					for ($c = 0 ; $c < $num ; $c++) {
						$montableau[$row][$c] = $data[$c] ;
					}
					$row++;
				}
			}
			fclose($rh);				
			$folder = $rootpath."configurations/$host/images/importcsv";
			$dossier = opendir($folder);
			while ($fichier = readdir($dossier)){
				if ($fichier != "." && $fichier != ".."){
					if (file_exists($folder."/import.csv"))	{
						unlink($folder."/import.csv");
					}
				}
			}
			if ((isset($montableau)) and (count($montableau) > 0)){
				foreach ($montableau as $key => $value) {
					//type produit
					$artcode = $montableau[$key][0] ; 
										
					//marque gamme modele 
					//$libelleFR = $montableau[$key][1] ;
					//$libelleFR = addslashes($libelleFR) ;
										
					//option garantie
					//$garantie2ansprix = $montableau[$key][2] ;
					//$garantie2ansprix = str_replace(" €","",$garantie2ansprix);
					//$garantie2ansprix = str_replace(",",".",$garantie2ansprix);
					//if ($garantie2ansprix == "x")
						//$garantie2ansprix = "";					
					//$garantie5ansprix = $montableau[$key][3] ;
					//$garantie5ansprix = str_replace(" €","",$garantie5ansprix);
					//$garantie5ansprix = str_replace(",",".",$garantie5ansprix);
					//if ($garantie5ansprix == "x")
						//$garantie5ansprix = "";					
					
					
					$usage1= $montableau[$key][4] ;
					$usage2= $montableau[$key][5] ;
					$usage3= $montableau[$key][6] ;
					$usage4= $montableau[$key][7] ;
					$usage5= $montableau[$key][8] ;
					
					$capacite1= $montableau[$key][9];
					$capacite2= $montableau[$key][10];
					$capacite3= $montableau[$key][11];
					$capacite4= $montableau[$key][12];
					$capacite5= $montableau[$key][13];
					$capacite6= $montableau[$key][14];
					$capacite7= $montableau[$key][15];
					$capacite8= $montableau[$key][16];
					
					$type1= $montableau[$key][17];
					$type2= $montableau[$key][18];
					
					$coloris1= $montableau[$key][19];
					$coloris2= $montableau[$key][20];
					
					$regulation1=$montableau[$key][21];
					$regulation2=$montableau[$key][22];
					
					$moderangement1=$montableau[$key][23];
					$moderangement2=$montableau[$key][24];
					$moderangement3=$montableau[$key][25];
					
					$emplacement1=$montableau[$key][26];
					$emplacement2=$montableau[$key][27];
					$emplacement3=$montableau[$key][28];
					
					if ($artcode  != ""){
						$nb_art ++;
						$articles=$DB_site2->query("SELECT * FROM article WHERE artcode='$artcode' ");
						while ($article=$DB_site2->fetch_array($articles)){
							$DB_site2->query("UPDATE article SET libelleFR='$libelleFR' WHERE artid='$article[artid]' ");
							//garanties
							if ($garantie2ansprix != ""){
								$prixttc=$garantie2ansprix + $garantie2ansprix * 0.196;
								$prixttc=round($prixttc,2);
								$garanties=$DB_site2->query("UPDATE article_caractval SET differenceprixht='$garantie2ansprix', differenceprix='$prixttc' WHERE artid='$article[artid]' AND caractvalid='10' ");
							}
							if ($garantie5ansprix != ""){
								$prixttc=$garantie5ansprix + $garantie5ansprix * 0.196;
								$prixttc=round($prixttc,2);
								$garanties=$DB_site2->query("UPDATE article_caractval SET differenceprixht='$garantie5ansprix', differenceprix='$prixttc' WHERE artid='$article[artid]' AND caractvalid='11' ");
							}
							
							//usages pour recherche
							if ($usage1=="X"){
								$decompte = $DB_site2->query("SELECT * FROM articlerecherche_usage WHERE artid='$article[artid]' AND rechercheusageid='1' ");
								if ($DB_site2->num_rows($decompte) == 0)
									$DB_site2->query("INSERT INTO articlerecherche_usage (artid,rechercheusageid) VALUES ('$article[artid]',1) ");
							}
							if ($usage1=="")
								$DB_site2->query("DELETE FROM articlerecherche_usage WHERE artid='$article[artid]' AND rechercheusageid='1' ");
							
							if ($usage2=="X"){
								$decompte = $DB_site2->query("SELECT * FROM articlerecherche_usage WHERE artid='$article[artid]' AND rechercheusageid='2' ");
								if ($DB_site2->num_rows($decompte) == 0)
									$DB_site2->query("INSERT INTO articlerecherche_usage (artid,rechercheusageid) VALUES ('$article[artid]',2) ");
							}
							if ($usage2=="")
								$DB_site2->query("DELETE FROM articlerecherche_usage WHERE artid='$article[artid]' AND rechercheusageid='2' ");
							
							if ($usage3=="X"){
								$decompte = $DB_site2->query("SELECT * FROM articlerecherche_usage WHERE artid='$article[artid]' AND rechercheusageid='3' ");
								if ($DB_site2->num_rows($decompte) == 0)
									$DB_site2->query("INSERT INTO articlerecherche_usage (artid,rechercheusageid) VALUES ('$article[artid]',3) ");
							}
							if ($usage3=="")
								$DB_site2->query("DELETE FROM articlerecherche_usage WHERE artid='$article[artid]' AND rechercheusageid='3' ");
							
							
							if ($usage4=="X"){
								$decompte = $DB_site2->query("SELECT * FROM articlerecherche_usage WHERE artid='$article[artid]' AND rechercheusageid='4' ");
								if ($DB_site2->num_rows($decompte) == 0)
									$DB_site2->query("INSERT INTO articlerecherche_usage (artid,rechercheusageid) VALUES ('$article[artid]',4) ");
							}
							if ($usage4=="")
								$DB_site2->query("DELETE FROM articlerecherche_usage WHERE artid='$article[artid]' AND rechercheusageid='4' ");
							
							if ($usage5=="X"){
								$decompte = $DB_site2->query("SELECT * FROM articlerecherche_usage WHERE artid='$article[artid]' AND rechercheusageid='5' ");
								if ($DB_site2->num_rows($decompte) == 0)
									$DB_site2->query("INSERT INTO articlerecherche_usage (artid,rechercheusageid) VALUES ('$article[artid]',5) ");
							}
							if ($usage5=="")
								$DB_site2->query("DELETE FROM articlerecherche_usage WHERE artid='$article[artid]' AND rechercheusageid='5' ");
							
							//capacités pour recherche
							if ($capacite1=="X"){
								$decompte = $DB_site2->query("SELECT * FROM articlerecherche_capacitebouteilles WHERE artid='$article[artid]' AND recherchecapacitebouteillesid='1' ");
								if ($DB_site2->num_rows($decompte) == 0)
									$DB_site2->query("INSERT INTO articlerecherche_capacitebouteilles (artid,recherchecapacitebouteillesid) VALUES ('$article[artid]',1) ");
							}
							if ($capacite1=="")
								$DB_site2->query("DELETE FROM articlerecherche_capacitebouteilles WHERE artid='$article[artid]' AND recherchecapacitebouteillesid='1' ");
							
							if ($capacite2=="X"){
								$decompte = $DB_site2->query("SELECT * FROM articlerecherche_capacitebouteilles WHERE artid='$article[artid]' AND recherchecapacitebouteillesid='2' ");
								if ($DB_site2->num_rows($decompte) == 0)
									$DB_site2->query("INSERT INTO articlerecherche_capacitebouteilles (artid,recherchecapacitebouteillesid) VALUES ('$article[artid]',2) ");
							}
							if ($capacite2=="")
								$DB_site2->query("DELETE FROM articlerecherche_capacitebouteilles WHERE artid='$article[artid]' AND recherchecapacitebouteillesid='2' ");
								
							if ($capacite3=="X"){
								$decompte = $DB_site2->query("SELECT * FROM articlerecherche_capacitebouteilles WHERE artid='$article[artid]' AND recherchecapacitebouteillesid='3' ");
								if ($DB_site2->num_rows($decompte) == 0)
									$DB_site2->query("INSERT INTO articlerecherche_capacitebouteilles (artid,recherchecapacitebouteillesid) VALUES ('$article[artid]',3) ");
							}
							if ($capacite3=="")
								$DB_site2->query("DELETE FROM articlerecherche_capacitebouteilles WHERE artid='$article[artid]' AND recherchecapacitebouteillesid='3' ");
						
							if ($capacite4=="X"){
								$decompte = $DB_site2->query("SELECT * FROM articlerecherche_capacitebouteilles WHERE artid='$article[artid]' AND recherchecapacitebouteillesid='4' ");
								if ($DB_site2->num_rows($decompte) == 0)
									$DB_site2->query("INSERT INTO articlerecherche_capacitebouteilles (artid,recherchecapacitebouteillesid) VALUES ('$article[artid]',4) ");
							}
							if ($capacite5=="")
								$DB_site2->query("DELETE FROM articlerecherche_capacitebouteilles WHERE artid='$article[artid]' AND recherchecapacitebouteillesid='5' ");
						
							if ($capacite5=="X"){
								$decompte = $DB_site2->query("SELECT * FROM articlerecherche_capacitebouteilles WHERE artid='$article[artid]' AND recherchecapacitebouteillesid='5' ");
								if ($DB_site2->num_rows($decompte) == 0)
									$DB_site2->query("INSERT INTO articlerecherche_capacitebouteilles (artid,recherchecapacitebouteillesid) VALUES ('$article[artid]',5) ");
							}
							if ($capacite5=="")
								$DB_site2->query("DELETE FROM articlerecherche_capacitebouteilles WHERE artid='$article[artid]' AND recherchecapacitebouteillesid='5' ");
								
							if ($capacite6=="X"){
								$decompte = $DB_site2->query("SELECT * FROM articlerecherche_capacitebouteilles WHERE artid='$article[artid]' AND recherchecapacitebouteillesid='6' ");
								if ($DB_site2->num_rows($decompte) == 0)
									$DB_site2->query("INSERT INTO articlerecherche_capacitebouteilles (artid,recherchecapacitebouteillesid) VALUES ('$article[artid]',6) ");
							}
							if ($capacite6=="")
								$DB_site2->query("DELETE FROM articlerecherche_capacitebouteilles WHERE artid='$article[artid]' AND recherchecapacitebouteillesid='6' ");
								
							if ($capacite7=="X"){
								$decompte = $DB_site2->query("SELECT * FROM articlerecherche_capacitebouteilles WHERE artid='$article[artid]' AND recherchecapacitebouteillesid='7' ");
								if ($DB_site2->num_rows($decompte) == 0)
									$DB_site2->query("INSERT INTO articlerecherche_capacitebouteilles (artid,recherchecapacitebouteillesid) VALUES ('$article[artid]',7) ");
							}
							if ($capacite7=="")
								$DB_site2->query("DELETE FROM articlerecherche_capacitebouteilles WHERE artid='$article[artid]' AND recherchecapacitebouteillesid='7' ");
								
							if ($capacite8=="X"){
								$decompte = $DB_site2->query("SELECT * FROM articlerecherche_capacitebouteilles WHERE artid='$article[artid]' AND recherchecapacitebouteillesid='8' ");
								if ($DB_site2->num_rows($decompte) == 0)
									$DB_site2->query("INSERT INTO articlerecherche_capacitebouteilles (artid,recherchecapacitebouteillesid) VALUES ('$article[artid]',8) ");
							}
							if ($capacite8=="")
								$DB_site2->query("DELETE FROM articlerecherche_capacitebouteilles WHERE artid='$article[artid]' AND recherchecapacitebouteillesid='8' ");
						
							
							//type de porte
							if ($type1=="X"){
								$decompte = $DB_site2->query("SELECT * FROM  articlerecherche_typeporte WHERE artid='$article[artid]' AND recherchetypeporteid='1' ");
								if ($DB_site2->num_rows($decompte) == 0)
									$DB_site2->query("INSERT INTO articlerecherche_typeporte (artid,recherchetypeporteid) VALUES ('$article[artid]',1) ");
							}
							if ($type1=="")
								$DB_site2->query("DELETE FROM articlerecherche_typeporte WHERE artid='$article[artid]' AND recherchetypeporteid='1' ");
								
							if ($type2=="X"){
								$decompte = $DB_site2->query("SELECT * FROM  articlerecherche_typeporte WHERE artid='$article[artid]' AND recherchetypeporteid='2' ");
								if ($DB_site2->num_rows($decompte) == 0)
									$DB_site2->query("INSERT INTO articlerecherche_typeporte (artid,recherchetypeporteid) VALUES ('$article[artid]',2) ");
							}
							if ($type2=="")
								$DB_site2->query("DELETE FROM articlerecherche_typeporte WHERE artid='$article[artid]' AND recherchetypeporteid='2' ");
							
							
							//coloris
							if ($coloris1=="X"){
								$decompte = $DB_site2->query("SELECT * FROM articlerecherche_coloris WHERE artid='$article[artid]' AND recherchecolorisid='1' ");
								if ($DB_site2->num_rows($decompte) == 0)
									$DB_site2->query("INSERT INTO articlerecherche_coloris (artid,recherchecolorisid) VALUES ('$article[artid]',1) ");
							}
							if ($coloris1=="")
								$DB_site2->query("DELETE FROM articlerecherche_coloris WHERE artid='$article[artid]' AND recherchecolorisid='1' ");
								
							if ($coloris2=="X"){
								$decompte = $DB_site2->query("SELECT * FROM articlerecherche_coloris WHERE artid='$article[artid]' AND recherchecolorisid='2' ");
								if ($DB_site2->num_rows($decompte) == 0)
									$DB_site2->query("INSERT INTO articlerecherche_coloris (artid,recherchecolorisid) VALUES ('$article[artid]',2) ");
							}
							if ($coloris2=="")
								$DB_site2->query("DELETE FROM articlerecherche_coloris WHERE artid='$article[artid]' AND recherchecolorisid='2' ");
								
								
							//type de regulation
							if ($regulation1=="X")
								$DB_site2->query("UPDATE article SET regulation='Electronique' WHERE artid='$article[artid]' ");
							if ($regulation2=="X")
								$DB_site2->query("UPDATE article SET regulation='Mécanique' WHERE artid='$article[artid]' ");
							
							
							//mode de rangement
							if ($moderangement1=="X"){
								$decompte = $DB_site2->query("SELECT * FROM articlerecherche_moderangement WHERE artid='$article[artid]' AND recherchemoderangementid='1' ");
								if ($DB_site2->num_rows($decompte) == 0)
									$DB_site2->query("INSERT INTO articlerecherche_moderangement (artid,recherchemoderangementid) VALUES ('$article[artid]',1) ");
							}
							if ($moderangement1=="")
								$DB_site2->query("DELETE FROM articlerecherche_moderangement WHERE artid='$article[artid]' AND recherchemoderangementid='1' ");
							
							if ($moderangement2=="X"){
								$decompte = $DB_site2->query("SELECT * FROM articlerecherche_moderangement WHERE artid='$article[artid]' AND recherchemoderangementid='2' ");
								if ($DB_site2->num_rows($decompte) == 0)
									$DB_site2->query("INSERT INTO articlerecherche_moderangement (artid,recherchemoderangementid) VALUES ('$article[artid]',2) ");
							}
							if ($moderangement2=="")
								$DB_site2->query("DELETE FROM articlerecherche_moderangement WHERE artid='$article[artid]' AND recherchemoderangementid='2' ");
							
							if ($moderangement3=="X"){
								$decompte = $DB_site2->query("SELECT * FROM articlerecherche_moderangement WHERE artid='$article[artid]' AND recherchemoderangementid='3' ");
								if ($DB_site2->num_rows($decompte) == 0)
									$DB_site2->query("INSERT INTO articlerecherche_moderangement (artid,recherchemoderangementid) VALUES ('$article[artid]',3) ");
							}
							if ($moderangement3=="")
								$DB_site2->query("DELETE FROM articlerecherche_moderangement WHERE artid='$article[artid]' AND recherchemoderangementid='5' ");
						
						
							//emplacement
							if ($emplacement1=="X"){
								$decompte = $DB_site2->query("SELECT * FROM articlerecherche_emplacement WHERE artid='$article[artid]' AND rechercheemplacementid='1' ");
								if ($DB_site2->num_rows($decompte) == 0)
									$DB_site2->query("INSERT INTO articlerecherche_emplacement (artid,rechercheemplacementid) VALUES ('$article[artid]',1) ");
							}
							if ($emplacement1=="")
								$DB_site2->query("DELETE FROM articlerecherche_emplacement WHERE artid='$article[artid]' AND rechercheemplacementid='1' ");
								
							if ($emplacement2=="X"){
								$decompte = $DB_site2->query("SELECT * FROM articlerecherche_emplacement WHERE artid='$article[artid]' AND rechercheemplacementid='2' ");
								if ($DB_site2->num_rows($decompte) == 0)
									$DB_site2->query("INSERT INTO articlerecherche_emplacement (artid,rechercheemplacementid) VALUES ('$article[artid]',2) ");
							}
							if ($emplacement2=="")
								$DB_site2->query("DELETE FROM articlerecherche_emplacement WHERE artid='$article[artid]' AND rechercheemplacementid='2' ");
								
							if ($emplacement3=="X"){
								$decompte = $DB_site2->query("SELECT * FROM articlerecherche_emplacement WHERE artid='$article[artid]' AND rechercheemplacementid='3' ");
								if ($DB_site2->num_rows($decompte) == 0)
									$DB_site2->query("INSERT INTO articlerecherche_emplacement (artid,rechercheemplacementid) VALUES ('$article[artid]',3) ");
							}
							if ($emplacement3=="")
								$DB_site2->query("DELETE FROM articlerecherche_emplacement WHERE artid='$article[artid]' AND rechercheemplacementid='3' ");
							
						
						
						}
						
						/*//garantie
						if ($garantie2ansprix != "" or $garantie5ansprix != "")	{
							//on met à jour la table article_caractval
							$DB_site2->query("INSERT INTO article_caractval (artid, caractvalid, differenceprix) VALUES ('$artid', '9', '0')");								
							if ($garantie2ans != "")
								$DB_site2->query("INSERT INTO article_caractval (artid, caractvalid, differenceprix) VALUES ('$artid', '10', '$garantie2ansprix')");	
							if ($garantie5ans != "")
								$DB_site2->query("INSERT INTO article_caractval (artid, caractvalid, differenceprix) VALUES ('$artid', '11', '$garantie5ansprix')");	
						}*/					
					}
				}
				$msg_erreur_import = "$nb_art articles modifi&eacute;s !" ;		
			}
		}
		else{
			$msg_erreur_import = "Vous devez saisir un chemin !" ;
		}
	}
} 


echo "<br><br>
<h3>Importer un fichier CSV de fiches produits</h3>
<form method=\"post\" enctype=\"multipart/form-data\" action=\"importupdatebenj.php\">
	<input type=\"hidden\" name=\"action\" value=\"import\">
	<font class=\"cost\">$msg_import $msg_erreur_import</font>
	<table class=\"table_caracts\">
		<tr>
			<td align=\"center\" colspan=\"3\" valign=\"middle\">Le fichier doit etre de type csv (.csv).<br><bR></td>
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