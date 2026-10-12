<?php
set_time_limit(600) ;

require "admin_global.php";
include "./header.php";
include "./menu.php";

$dossierencours = "photos produits";
$nbpaquet = 15;

echo "<td valign=\"top\">
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
   	<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
		<tr>
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Importer des images</font>
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
	$nb_import = 0;
	$dossier_import = opendir($rootpath."ftp/PHOTOS SITES/$dossierencours/"); 
	while (false !== ($image = readdir($dossier_import)))
		{ 
		if (substr($image,0,1) != ".") 
			{
			if ($nb_import < $nbpaquet)
				{
				$chemin = $rootpath."ftp/PHOTOS SITES/$dossierencours/".$image;
				$file = explode(".",$image);
				$nomimage =  explode("_",$file[0]);
				$reference = $nomimage[0];
				$numimage = $nomimage[1];
				$type_fichier = strtolower($file[1]);
				if ($type_fichier == "jpeg")
					{
					$type_fichier = "jpg";
					}
				
				$existe = $DB_site2->query_first("SELECT artid ,image FROM article WHERE artcode = '$reference'");
				if ($type_fichier != "jpg")
					$msg_erreur_import .= "$image n'est pas un image jpg.<br>" ;
				elseif ($existe[artid] != "")
					{
					$artid = $existe[artid];
					if ($numimage != "1")
						{
						$DB_site2->query("insert into articlephoto (artid, image) values ('$artid', '$type_fichier')");
						$num=$DB_site2->insert_id() ;
						 
						$nom_fic2=$rootpath."configurations/$host/images/produits/".$artid."_".$num.".".$type_fichier;
						copier_image($nom_fic2,$chemin);
						
						$destination0=$rootpath."configurations/$host/images/produits/article/".$artid."_".$num.".".$type_fichier;
						redimentionner_image($nom_fic2,$destination0,$article_largeur,$article_hauteur);
						$destination1=$rootpath."configurations/$host/images/produits/categorie/".$artid."_".$num.".".$type_fichier;
						redimentionner_image($nom_fic2,$destination1,$categorie_largeur,$categorie_hauteur);
						$destination2=$rootpath."configurations/$host/images/produits/cdc_categ/".$artid."_".$num.".".$type_fichier;
						redimentionner_image($nom_fic2,$destination2,$cdc_categ_largeur,$cdc_categ_hauteur);
						$destination3=$rootpath."configurations/$host/images/produits/cdc_index/".$artid."_".$num.".".$type_fichier;
						redimentionner_image($nom_fic2,$destination3,$cdc_index_largeur,$cdc_index_hauteur);
						$destination4=$rootpath."configurations/$host/images/produits/nouveautes_categ/".$artid."_".$num.".".$type_fichier;
						redimentionner_image($nom_fic2,$destination4,$nouveautes_categ_largeur,$nouveates_categ_hauteur);
						$destination5=$rootpath."configurations/$host/images/produits/nouveautes_index/".$artid."_".$num.".".$type_fichier;
						redimentionner_image($nom_fic2,$destination5,$nouveautes_index_largeur,$nouveates_index_hauteur);
						$destination6=$rootpath."configurations/$host/images/produits/promos_categ/".$artid."_".$num.".".$type_fichier;
						redimentionner_image($nom_fic2,$destination6,$promos_categ_largeur,$promos_categ_hauteur);
						$destination7=$rootpath."configurations/$host/images/produits/promos_index/".$artid."_".$num.".".$type_fichier;
						redimentionner_image($nom_fic2,$destination7,$promos_index_largeur,$promos_index_hauteur);
						$destination8=$rootpath."configurations/$host/images/produits/topventes_categ/".$artid."_".$num.".".$type_fichier;
						redimentionner_image($nom_fic2,$destination8,$topventes_categ_largeur,$topventes_categ_hauteur);
						$destination9=$rootpath."configurations/$host/images/produits/topventes_index/".$artid."_".$num.".".$type_fichier;
						redimentionner_image($nom_fic2,$destination9,$topventes_index_largeur,$topventes_index_hauteur);
						$destination10=$rootpath."configurations/$host/images/produits/br/".$artid."_".$num.".".$type_fichier;
						redimentionner_image($nom_fic2,$destination10,$largeurimages,$hauteurimages);
						if (isset($hauteurphotos) && isset($largeurphotos)) {
							$destination11=$rootpath."configurations/$host/images/produits/photos/".$artid."_".$num.".".$type_fichier;
							redimentionner_image($nom_fic2,$destination11,$largeurphotos,$hauteurphotos);
							}
						}
					else
						{
						$nom_fic=$rootpath."configurations/$host/images/produits/$artid.$type_fichier";
						copier_image($nom_fic,$chemin);
		
						$destination0=$rootpath."configurations/$host/images/produits/article/$artid.$type_fichier";
						redimentionner_image($nom_fic,$destination0,$article_largeur,$article_hauteur);
						$destination1=$rootpath."configurations/$host/images/produits/categorie/$artid.$type_fichier";
						redimentionner_image($nom_fic,$destination1,$categorie_largeur,$categorie_hauteur);
						$destination2=$rootpath."configurations/$host/images/produits/cdc_categ/$artid.$type_fichier";
						redimentionner_image($nom_fic,$destination2,$cdc_categ_largeur,$cdc_categ_hauteur);
						$destination3=$rootpath."configurations/$host/images/produits/cdc_index/$artid.$type_fichier";
						redimentionner_image($nom_fic,$destination3,$cdc_index_largeur,$cdc_index_hauteur);
						$destination4=$rootpath."configurations/$host/images/produits/nouveautes_categ/$artid.$type_fichier";
						redimentionner_image($nom_fic,$destination4,$nouveautes_categ_largeur,$nouveates_categ_hauteur);
						$destination5=$rootpath."configurations/$host/images/produits/nouveautes_index/$artid.$type_fichier";
						redimentionner_image($nom_fic,$destination5,$nouveautes_index_largeur,$nouveates_index_hauteur);
						$destination6=$rootpath."configurations/$host/images/produits/promos_categ/$artid.$type_fichier";
						redimentionner_image($nom_fic,$destination6,$promos_categ_largeur,$promos_categ_hauteur);
						$destination7=$rootpath."configurations/$host/images/produits/promos_index/$artid.$type_fichier";
						redimentionner_image($nom_fic,$destination7,$promos_index_largeur,$promos_index_hauteur);
						$destination8=$rootpath."configurations/$host/images/produits/topventes_categ/$artid.$type_fichier";
						redimentionner_image($nom_fic,$destination8,$topventes_categ_largeur,$topventes_categ_hauteur);
						$destination9=$rootpath."configurations/$host/images/produits/topventes_index/$artid.$type_fichier";
						redimentionner_image($nom_fic,$destination9,$topventes_index_largeur,$topventes_index_hauteur);
						$destination10=$rootpath."configurations/$host/images/produits/br/$artid.$type_fichier";
						redimentionner_image($nom_fic,$destination10,$largeurimages,$hauteurimages);
						$DB_site2->query("UPDATE article SET image = '$type_fichier' WHERE artid = '$artid'");
						}
					
	
					unlink($chemin); 
					$nb_import ++;
					}
				else
					{
					$msg_erreur_import .= "Aucun article trouvé pour l'image $image.<br>" ;
					}	
				}	
			}
		} 
	closedir($dossier_import);
	$msg_erreur_import .= "<br>==> $nb_import image(s) importée(s)<br><br>";
	$action = "";
	} 

if (!isset($action) or $action == "")
	{
	$countfiles = 0;
	$handle = opendir("/home/macavavin/www/ftp/PHOTOS SITES/$dossierencours" );
	while($file = readdir($handle)) {
	$countfiles += 1;
	}
	$countfiles -= 2;
	
	if ($countfiles == 0)
		{
		echo "<br><br>
		<h3>Importer des images (par référence)</h3>
			<table class=\"table_caracts\">
				<tr>
					<td align=\"center\" colspan=\"2\">Aucune image à importer.</td>
				</tr>
			</table>
		<br><br>" ;
		}
		else
		{
		echo "<br><br>
		<h3>Importer des images (par référence)</h3>
		<form method=\"post\" action=\"importimages_ref.php\">
			<input type=\"hidden\" name=\"action\" value=\"import\">
			<font class=\"cost\">$msg_import $msg_erreur_import</font>
			<table class=\"table_caracts\">
				<tr>
					<td align=\"center\" colspan=\"2\">Il reste actuellement <font class=\"cost\">$countfiles image(s)</font> à importer.<br>
					(Chaque import se fait par paquet de $nbpaquet images maximum)<br><br><br></td>
				</tr>
				<tr>
					<td align=\"center\" colspan=\"2\"><input type=\"submit\" value=\"Importer\"></td>
				</tr>
			</table>
		<br><br>
		</form>" ;
		}
	}
		
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
		@chmod ($destination, 0777) ;
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
		@chmod ($destination, 0777) ;
		imagedestroy ($image) ;
		}
	}
	

function copier_image($nom_fic,$imagename)
	{
	copy ($imagename, $nom_fic);
	chmod ("$nom_fic",0777);
	}

?>