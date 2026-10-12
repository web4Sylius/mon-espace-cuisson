<?php

require "admin_global.php";
//include "./global.php";
include "./header.php";
include "./menu.php";


echo "<td valign=\"top\">
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
   	<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
		<tr>
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Marques</font>
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
   <td align=\"center\" valign=\"middle\">" ;

if (isset($action) and $action == "supprimer")
	{
	echo "Etes-vous sûr(e) de vouloir supprimer ce logo ?<bR><br>
	<a href=\"logofacture.php?action=supprimer2\">OUI</a> | <a href=\"logofacture.php\">NON</a>" ;
	}

if (isset($action) and $action == "supprimer2")
	{
	unlink($rootpath."configurations/$host/factures/logo.jpg");
	$action = "" ;
	}

// AJOUTER UNE MARQUE (validé)
if (isset($action) and $action == "ajouter")
	{
	$erreur = "" ;
	if ($logo != "")
		{
		if ($_FILES['logo']['name']!="") 
			{
			if ($_FILES['logo']['error']==1) 
				{
				$erreur.="Erreur !!! Le fichier téléchargé excède la taille autorisée par le serveur (> 2Mb)";
				}
			elseif ($_FILES['logo']['error']==3) 
				{
				$erreur.="erreur !!! Le fichier n'a été que partiellement téléchargé.";
				}
			elseif ($_FILES['logo']['error']==2) 
				{
				$erreur.="erreur !!! ereur inconnue ...";
				}
			elseif ($_FILES['logo']['error']==4) 
				{
				$erreur.="erreur !!! Aucun fichier n'a été téléchargé.";
				}
			elseif ($_FILES['logo']['type']!="image/pjpeg" && $_FILES['logo']['type']!="image/jpeg") 
				{
				$erreur.="erreur !!! Le fichier téléchargé n'est pas du type autorisé:<br>Vous ne pouvez télécharger qu'un fichier du type JPEG , JPG (type envoyé : ".$_FILES['logo']['type'].")";
				}
			else
				{
				if ($_FILES['logo']['type'] == "image/pjpeg" or $_FILES['logo']['type']!="image/jpeg") 
					{
					$type_fichier = "jpg" ;
					}
				$nom_fic = $rootpath."configurations/$host/factures/logo.jpg";
				copier_image($nom_fic,'logo');
				$taille = getimagesize($rootpath."configurations/$host/factures/logo.jpg") ;
				if ($taille[0] != 320 or $taille[1] != 110)
					{
					unlink($rootpath."configurations/$host/factures/logo.jpg");
					echo "La taille de votre logo ne correspond pas à la taille demandée." ;
					}
				}
			}
		else
			{
			$erreur .= "Erreur dans le chargement du fichier.<br>" ;
			}
		}
	else
		{
		$erreur = "Le fichier est obligatoire.<br>" ;
		}
	$action = "" ;
	}


if (!isset($action) or $action == "")
	{
	echo "$erreur
	<h3>Logo de la facture</h3>
	<strong>Taille obligatoire : 320 x 110 px<br>
	Format obligatoire : jpg</strong><br><br>" ;
	if (@file_exists($rootpath."configurations/$host/factures/logo.jpg"))
		{
		echo "<table align=\"center\">
				<tr>
					<td align=\"right\">Logo actuel : </td>
					<td align=\"left\"><img src=\"http://$host/configurations/$host/factures/logo.jpg\"></td>
					<td align=\"center\"><a href=\"logofacture.php?action=supprimer\">Supprimer</a></td>
				</tr>
			</table>" ;
		}
	else
		{
		echo "<form method=\"post\" action=\"logofacture.php\" enctype=\"multipart/form-data\"><input type=\"hidden\" name=\"action\" value=\"ajouter\">
			<table align=\"center\">
				<tr>

					<td align=\"center\" colspan=\"3\">Il n'existe aucun logo pour l'instant.<br><br></td>
				</tr>
				<tr>
					<td align=\"right\">Logo : </td>
					<td align=\"left\"><input type=\"file\" name=\"logo\"></td>
					<td align=\"center\"><input type=\"submit\" value=\"Ajouter\"></td>
				</tr>
			</table>
			</form>" ;
		}
	}
echo "</td>
   <td background=\"images2/cadre_r4_c10.gif\" width=\"3\" height=\"100%\" border=\"0\" alt=\"\"></td>
  </tr>
</table>
</td>
</tr>
<tr>
<td>
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
</tr>";

include "./footer.php";

function copier_image($nom_fic,$imagename)
	{
	if ($OpenLocal= fopen($_FILES[$imagename]['tmp_name'], "rb")) 
		{
		$LocalFileContent = fread($OpenLocal, $_FILES[$imagename]['size']) ;
		} 
	else 
		{
		fclose ($OpenLocal);
		die ("<b>Erreur : </b>impossible d'ouvrir le fichier local '".$imagename."'.") ;
		}
	fclose ($OpenLocal);
		
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
		die ("<b>Erreur : </b>impossible d'ouvrir le fichier distant '".$nom_fic.".") ;
		}
	chmod ("$nom_fic",0777);
	}

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
		if ($imagesize[0] > $imagesize[1]) 
			{
			$width = $largeur ;
			$height = floor ($largeur * $imagesize[1] / $imagesize[0]) ;
			} 
		else 
			{
			$width = floor($largeur * $imagesize[0] / $imagesize[1]) ;
			$height = $largeur ;
			}
		$image = imagecreatetruecolor ($width, $height) ;
		imagecopyresampled ($image, $imagetmp, 0, 0, 0, 0, $width, $height, $imagesize[0], $imagesize[1]) ;
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

?>