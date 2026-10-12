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
				foreach ($montableau as $key => $value)  	{
					$rechercheusage1 = 0 ; 
					$rechercheusage2 = 0 ; 
					$rechercheusage3 = 0 ; 
					$rechercheusage4 = 0 ; 
					$recherchecapacitebouteilles1 = 0 ; 
					$recherchecapacitebouteilles2 = 0 ; 
					$recherchecapacitebouteilles3 = 0 ; 
					$recherchecapacitebouteilles4 = 0 ; 
					$recherchecapacitebouteilles5 = 0 ; 
					$recherchecapacitebouteilles6 = 0 ; 
					$recherchecapacitebouteilles7 = 0 ; 
					$recherchemoderangement1 = 0 ; 
					$recherchemoderangement2 = 0 ;
					$recherchemoderangement3 = 0 ;
					$rechercheemplacement1 = 0 ; 
					$rechercheemplacement2 = 0 ;
					$rechercheemplacement3 = 0 ;
					$recherchetypeporte1 = 0 ; 
					$recherchetypeporte2 = 0 ; 
					$recherchecoloris1 = 0 ; 
					$recherchecoloris2 = 0 ; 
					$artcode_fabricant = "" ; 
					
					$artcode = $montableau[$key][0] ; 
					if ($montableau[$key][1] == "X")
						$rechercheusage1 = 1 ; 
					if ($montableau[$key][2] == "X")
					$rechercheusage2 = 1 ; 
					if ($montableau[$key][3] == "X")
					$rechercheusage3 = 1 ; 
					if ($montableau[$key][4] == "X")
					$rechercheusage4 = 1 ; 
					if ($montableau[$key][5] == "X")
					$recherchecapacitebouteilles1 = 1 ; 
					if ($montableau[$key][6] == "X")
					$recherchecapacitebouteilles2 = 1 ; 
					if ($montableau[$key][7] == "X")
					$recherchecapacitebouteilles3 = 1 ; 
					if ($montableau[$key][8] == "X")
					$recherchecapacitebouteilles4 = 1 ; 
					if ($montableau[$key][9] == "X")
					$recherchecapacitebouteilles5 = 1 ; 
					if ($montableau[$key][10] == "X")
					$recherchecapacitebouteilles6 = 1 ; 
					if ($montableau[$key][11] == "X")
					$recherchecapacitebouteilles7 = 1 ; 
					if ($montableau[$key][12] == "X")
					$recherchemoderangement1 = 1 ; 
					if ($montableau[$key][13] == "X")
					$recherchemoderangement2 = 1 ;
					if ($montableau[$key][14] == "X")
					$recherchemoderangement3 = 1 ;
					if ($montableau[$key][15] == "X")
					$rechercheemplacement1 = 1 ;
					if ($montableau[$key][16] == "X")
					$rechercheemplacement2 = 1 ;
					if ($montableau[$key][17] == "X")
					$rechercheemplacement3 = 1 ;					
					if ($montableau[$key][18] == "X")
					$recherchetypeporte1 = 1 ; 
					if ($montableau[$key][19] == "X")
					$recherchetypeporte2 = 1 ; 
					if ($montableau[$key][20] == "X")
					$recherchecoloris1 = 1 ; 
					if ($montableau[$key][21] == "X")
					$recherchecoloris2 = 1 ; 
					$artcode_fabricant = $montableau[$key][22] ; 
					
					$nb_art++;
					
					if ($artcode  != "") {
						$articles=$DB_site2->query("SELECT artid FROM article WHERE artcode = '$artcode'");
						while ($article=$DB_site2->fetch_array($articles))
							{
							$nb_art++;
							if ($artcode_fabricant) {
								echo "UPDATE article SET artcode_fabricant = '$artcode_fabricant' WHERE artid = '$article[artid]'<br>" ;
								$DB_site2->query("UPDATE article SET artcode_fabricant = '$artcode_fabricant' WHERE artid = '$article[artid]'");
								}
							// usages
							$rechercheusages=$DB_site2->query("SELECT * FROM recherche_usage");
							while ($rechercheusage=$DB_site2->fetch_array($rechercheusages)) {
								$currentvalue = "rechercheusage".$rechercheusage[rechercheusageid] ;
								$artrechercheusage=$DB_site2->query_first("SELECT * FROM articlerecherche_usage WHERE artid = '$article[artid]' and rechercheusageid = '$rechercheusage[rechercheusageid]'");
								if ($artrechercheusage[artid] == $article[artid]) {
									echo "UPDATE articlerecherche_usage SET selectionne = '${$currentvalue}' WHERE artid = '$article[artid]' and rechercheusageid = '$rechercheusage[rechercheusageid]'<br>" ;
									$DB_site2->query("UPDATE articlerecherche_usage SET selectionne = '${$currentvalue}' WHERE artid = '$article[artid]' and rechercheusageid = '$rechercheusage[rechercheusageid]'");
									}
								else {
									echo "insert into articlerecherche_usage (artid, rechercheusageid, selectionne) values ('$article[artid]', '$rechercheusage[rechercheusageid]', '${$currentvalue}')<br>" ;
									$DB_site2->query("insert into articlerecherche_usage (artid, rechercheusageid, selectionne) values ('$article[artid]', '$rechercheusage[rechercheusageid]', '${$currentvalue}')");
									}
								}
							// capacité bouteilles
							$recherchecapacitebouteilles=$DB_site2->query("SELECT * FROM recherche_capacitebouteilles");
							while ($recherchecapacitebouteille=$DB_site2->fetch_array($recherchecapacitebouteilles)) {
								$currentvalue = "recherchecapacitebouteilles".$recherchecapacitebouteille[recherchecapacitebouteillesid] ;
								$artrecherchecapacitebouteille=$DB_site2->query_first("SELECT * FROM articlerecherche_capacitebouteilles WHERE artid = '$article[artid]' and recherchecapacitebouteillesid = '$recherchecapacitebouteille[recherchecapacitebouteillesid]'");
								if ($artrecherchecapacitebouteille[artid] == $article[artid]) {
									echo "UPDATE articlerecherche_capacitebouteilles SET selectionne = '${$currentvalue}' WHERE artid = '$article[artid]' and recherchecapacitebouteillesid = '$recherchecapacitebouteille[recherchecapacitebouteillesid]'<br>" ;
									$DB_site2->query("UPDATE articlerecherche_capacitebouteilles SET selectionne = '${$currentvalue}' WHERE artid = '$article[artid]' and recherchecapacitebouteillesid = '$recherchecapacitebouteille[recherchecapacitebouteillesid]'");
									}
								else {
									echo "insert into articlerecherche_capacitebouteilles (artid, recherchecapacitebouteillesid, selectionne) values ('$article[artid]', '$recherchecapacitebouteille[recherchecapacitebouteillesid]', '${$currentvalue}')<br>" ;
									$DB_site2->query("insert into articlerecherche_capacitebouteilles (artid, recherchecapacitebouteillesid, selectionne) values ('$article[artid]', '$recherchecapacitebouteille[recherchecapacitebouteillesid]', '${$currentvalue}')");
									}
								}
							// emplacement
							$rechercheemplacements=$DB_site2->query("SELECT * FROM recherche_emplacement");
							while ($rechercheemplacement=$DB_site2->fetch_array($rechercheemplacements)) {
								$currentvalue = "rechercheemplacement".$rechercheemplacement[rechercheemplacementid] ;
								$artrechercheemplacement=$DB_site2->query_first("SELECT * FROM articlerecherche_emplacement WHERE artid = '$article[artid]' and rechercheemplacementid = '$rechercheemplacement[rechercheemplacementid]'");
								if ($artrechercheemplacement[artid] == $article[artid]) {
									echo "UPDATE articlerecherche_emplacement SET selectionne = '${$currentvalue}' WHERE artid = '$article[artid]' and rechercheemplacementid = '$rechercheemplacement[rechercheemplacementid]'<br>" ;
									$DB_site2->query("UPDATE articlerecherche_emplacement SET selectionne = '${$currentvalue}' WHERE artid = '$article[artid]' and rechercheemplacementid = '$rechercheemplacement[rechercheemplacementid]'");
									}
								else {
									echo "insert into articlerecherche_emplacement (artid, rechercheemplacementid, selectionne) values ('$article[artid]', '$rechercheemplacement[rechercheemplacementid]', '${$currentvalue}')<br>" ;
									$DB_site2->query("insert into articlerecherche_emplacement (artid, rechercheemplacementid, selectionne) values ('$article[artid]', '$rechercheemplacement[rechercheemplacementid]', '${$currentvalue}')");
									}
								}
							// mode rangement
							$recherchemoderangements=$DB_site2->query("SELECT * FROM recherche_moderangement");
							while ($recherchemoderangement=$DB_site2->fetch_array($recherchemoderangements)) {
								$currentvalue = "recherchemoderangement".$recherchemoderangement[recherchemoderangementid] ;
								$artrecherchemoderangement=$DB_site2->query_first("SELECT * FROM articlerecherche_moderangement WHERE artid = '$article[artid]' and recherchemoderangementid = '$recherchemoderangement[recherchemoderangementid]'");
								if ($artrecherchemoderangement[artid] == $article[artid]) {
									echo "UPDATE articlerecherche_moderangement SET selectionne = '${$currentvalue}' WHERE artid = '$article[artid]' and recherchemoderangementid = '$recherchemoderangement[recherchemoderangementid]'<br>" ;
									$DB_site2->query("UPDATE articlerecherche_moderangement SET selectionne = '${$currentvalue}' WHERE artid = '$article[artid]' and recherchemoderangementid = '$recherchemoderangement[recherchemoderangementid]'");
									}
								else {
									echo "insert into articlerecherche_moderangement (artid, recherchemoderangementid, selectionne) values ('$article[artid]', '$recherchemoderangement[recherchemoderangementid]', '${$currentvalue}')<br>" ;
									$DB_site2->query("insert into articlerecherche_moderangement (artid, recherchemoderangementid, selectionne) values ('$article[artid]', '$recherchemoderangement[recherchemoderangementid]', '${$currentvalue}')");
									}
								}								
							 // type porte
							$recherchetypeportes=$DB_site2->query("SELECT * FROM recherche_typeporte");
							while ($recherchetypeporte=$DB_site2->fetch_array($recherchetypeportes)) {
								$currentvalue = "recherchetypeporte".$recherchetypeporte[recherchetypeporteid] ;
								$artrecherchetypeporte=$DB_site2->query_first("SELECT * FROM articlerecherche_typeporte WHERE artid = '$article[artid]' and recherchetypeporteid = '$recherchetypeporte[recherchetypeporteid]'");
								if ($artrecherchetypeporte[artid] == $article[artid]) {
									echo "UPDATE articlerecherche_typeporte SET selectionne = '${$currentvalue}' WHERE artid = '$article[artid]' and recherchetypeporteid = '$recherchetypeporte[recherchetypeporteid]'<br>" ;
									$DB_site2->query("UPDATE articlerecherche_typeporte SET selectionne = '${$currentvalue}' WHERE artid = '$article[artid]' and recherchetypeporteid = '$recherchetypeporte[recherchetypeporteid]'");
									}
								else {
									echo "insert into articlerecherche_typeporte (artid, recherchetypeporteid, selectionne) values ('$article[artid]', '$recherchetypeporte[recherchetypeporteid]', '${$currentvalue}')<br>" ;
									$DB_site2->query("insert into articlerecherche_typeporte (artid, recherchetypeporteid, selectionne) values ('$article[artid]', '$recherchetypeporte[recherchetypeporteid]', '${$currentvalue}')");
									}
								}
							// coloris
							$recherchecoloriss=$DB_site2->query("SELECT * FROM recherche_coloris");
							while ($recherchecoloris=$DB_site2->fetch_array($recherchecoloriss)) {
								$currentvalue = "recherchecoloris".$recherchecoloris[recherchecolorisid] ;
								$artrecherchecoloris=$DB_site2->query_first("SELECT * FROM articlerecherche_coloris WHERE artid = '$article[artid]' and recherchecolorisid = '$recherchecoloris[recherchecolorisid]'");
								if ($artrecherchecoloris[artid] == $article[artid]) {
									echo "UPDATE articlerecherche_coloris SET selectionne = '${$currentvalue}' WHERE artid = '$article[artid]' and recherchecolorisid = '$recherchecoloris[recherchecolorisid]'<br>" ;
									$DB_site2->query("UPDATE articlerecherche_coloris SET selectionne = '${$currentvalue}' WHERE artid = '$article[artid]' and recherchecolorisid = '$recherchecoloris[recherchecolorisid]'");
									}
								else {
									echo "insert into articlerecherche_coloris (artid, recherchecolorisid, selectionne) values ('$article[artid]', '$recherchecoloris[recherchecolorisid]', '${$currentvalue}')<br>" ;
									$DB_site2->query("insert into articlerecherche_coloris (artid, recherchecolorisid, selectionne) values ('$article[artid]', '$recherchecoloris[recherchecolorisid]', '${$currentvalue}')");
									}
								}
							}
						}
					}
				$msg_erreur_import = "$nb_art articles modifiés !" ;		
				}
			}
		else
			$msg_erreur_import = "Vous devez saisir un chemin !" ;
		}
	} 


echo "<br><br>
<h3>Importer un fichier CSV de fiches produits</h3>
<form method=\"post\" enctype=\"multipart/form-data\" action=\"importupdateCARO.php\">
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