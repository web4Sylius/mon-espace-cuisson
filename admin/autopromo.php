<?php
require "admin_global.php";
//include "global.php";
include "./header.php";
include "./menu.php";

echo "<td valign=\"top\">
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
   	<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
		<tr>
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Ajout / Modification de produit</font>
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

////////////////////////////////////////////////////////////////////////////////////////////////////////
// AFFICHER LA PAGE
////////////////////////////////////////////////////////////////////////////////////////////////////////
if (!isset($action) || $action == "" ) {
	$params=$DB_site2->query_first("SELECT autopromo_largeur, autopromo_hauteur FROM parametresgeneraux");
	echo "<h3>Dimension de l'espace publicitaire</h3>
		<font class=\"cost\">$erreursauv</font>
		<form action=\"autopromo.php\" name=\"formu_autopromo\" method=\"post\">
		<input type=\"hidden\" name=\"action\" value=\"sauvegarder\"> 	
		<table >
			<tr><td>Largeur :</td><td><input type=\"text\" readonly name=\"largeur\" value=\"$params[autopromo_largeur]\" size=\"4\"> px</td></tr>
			<tr><td>Hauteur :</td><td><input type=\"text\" readonly name=\"hauteur\" value=\"$params[autopromo_hauteur]\" size=\"4\"> px</td></tr>
		</table>
		<br>
		<br><br><br>
		</form>";
	
	$rs_promo=$DB_site2->query_first( "SELECT autopromo_fichier FROM parametresgeneraux");
	if (isset($rs_promo[autopromo_fichier]) && $rs_promo[autopromo_fichier]!=""){
		echo "<h3>Fichier</h3>
			<table class=\"table_modules_boutiques\">";
			
			echo "<tr>";
			echo "  <td><a href=\"http://$host/configurations/$host/images/autopromo/caveavin.$rs_promo[autopromo_fichier]\" target=\"_blank\">autopromo</a></td>";
			echo "  <td align=\"center\" width=\"30%\"><a href=\"autopromo.php?action=supprimer\" target=\"_self\">Supprimer</a></td>";
			echo "</tr>";
		echo "</table>";
	}else
		echo "<div align=\"center\"><br><a href=\"autopromo.php?action=ajouter\" target=\"_self\">Ajouter un fichier</a></div>";
} 

////////////////////////////////////////////////////////////////////////////////////////////////////////
// AJOUTER UN FICHIER (action=ajouter)
////////////////////////////////////////////////////////////////////////////////////////////////////////	
elseif( $action == "ajouter" )
{
	echo "<h3>Ajouter un fichier</h3>";
	echo "<form method=\"post\" action=\"autopromo.php\" name=\"form_fichier\" enctype=\"multipart/form-data\">
			<table align=\"center\" width=\"500\">";
	if( isset($_POST['btn_valid']))
	{
	if ($_FILES['fichier']['name']=="")
		{
		echo "<br><br><font class=\"cost\"><center>Veuillez saisir un fichier !</center></font><br><br>";
		$action="ajouter";
		}
		else
		{
		$erreur = "";
		if ($_FILES['fichier']['name']!="")
			{
			if ($_FILES['fichier']['error']==1)
				{
				$erreur.="Erreur !!! Le fichier téléchargé excède la taille autorisée par le serveur (> 2Mb)";
				}
			elseif ($_FILES['fichier']['size']>1048576)
				{
				$erreur.="erreur !!! Le fichier téléchargé excède la taille autorisée:<br>Vous ne pouvez télécharger qu'un fichier dont la taille est inférieur à 1 Mo";
				}
			elseif ($_FILES['fichier']['error']==3)
				{
				$erreur.="erreur !!! Le fichier n'a été que partiellement téléchargé.";
				}
			elseif ($_FILES['fichier']['error']==2)
				{
				$erreur.="erreur !!! ereur inconnue ...";
				}
			elseif ($_FILES['fichier']['error']==4)
				{
				$erreur.="erreur !!! Aucun fichier n'a été téléchargé (image de base).";
				}
			elseif ($_FILES['fichier']['type']!="image/pjpeg" && $_FILES['fichier']['type']!="image/gif" && $_FILES['fichier']['type']!="image/jpeg" && $_FILES['fichier']['type']!="application/x-shockwave-flash")
				{
				$erreur.="erreur !!! Le fichier téléchargé n'est pas du type autorisé:<br>Vous ne pouvez télécharger qu'un fichier du type JPEG , JPG , GIF ou SWF (type envoyé : ".$_FILES['fichier']['type'].")";
				}
			if ($erreur == "")
				{
				switch ($_FILES['fichier']['type'])  // définition de l'extension
					{
					case "image/pjpeg":
						$file = "jpg" ;
						break;
					case "image/gif":
						$file = "gif" ;
						break;
					case "image/jpeg":
						$file = "jpg" ;
						break;
					case "application/x-shockwave-flash":
						$file = "swf" ;
						break;
					}
				$titre = $_FILES['fichier']['name'];
				$infos_upload=$DB_site2->query("UPDATE parametresgeneraux SET autopromo_fichier='$file' ") ;
				$nom_fic=$rootpath."configurations/$host/images/autopromo/caveavin.".$file ;
				copier_fichier($nom_fic,"fichier") ;
				echo "<script language=\"javascript\">location.href=\"autopromo.php\";</script>";
				}
				else
				{
				echo "<br><br><font class=\"cost\"><center>$erreur</center></font><br><br>";
				$action="ajouter";
				}
			}
		}
	}
	echo "<tr><td><b>Fichier :</b></td><td><input type=\"file\" name=\"fichier\" size=\"50\"><br>
		  <i>(uniquement .jpg, .jpeg, .gif, .swf)</i></td></tr>";
	// Champs "hidden" 
	echo "<input type=\"hidden\" name=\"action\" value=\"ajouter\">";
	echo "<tr><td align=\"center\" colspan=\"2\"><br><input type=\"submit\" name=\"btn_valid\" value=\"Ajouter le fichier\"></td></tr>";
	echo "</table></form>";
	
	echo "<div align=\"center\"><a href=\"autopromo.php\" target=\"_self\">Annuler</a></div>";
}
////////////////////////////////////////////////////////////////////////////////////////////////////////
// SUPPRIMER UN FICHIER (action=supprimer)
////////////////////////////////////////////////////////////////////////////////////////////////////////	
elseif( $action == "supprimer" )
	{
	// Confirmation de suppression
	echo "êtes vous sûr de vouloir supprimer ce fichier ?<br>
		  <a href=\"autopromo.php?action=supprimer2\">Oui</a>
		  | <a href=\"autopromo.php\">Non</a>";
	}
////////////////////////////////////////////////////////////////////////////////////////////////////////
// SUPPRIMER UN FICHIER (action=supprimer2)
////////////////////////////////////////////////////////////////////////////////////////////////////////	
elseif( $action == "supprimer2" )
	{
	$extension = $DB_site2->query_first("SELECT autopromo_fichier FROM parametresgeneraux");
	$cheminfic = $rootpath."configurations/$host/images/autopromo/caveavin.".$extension[autopromo_fichier];
	unlink($cheminfic);
	
	$rq_supprimer = "UPDATE parametresgeneraux SET autopromo_fichier=''";
	$DB_site2->query( $rq_supprimer);
	
	echo "<script language=\"javascript\">location.href=\"autopromo.php\";</script>";
	}
////////////////////////////////////////////////////////////////////////////////////////////////////////
// ACTIVER UN FICHIER (action=activer)
////////////////////////////////////////////////////////////////////////////////////////////////////////	
elseif( $action == "activer" )
	{
	$DB_site2->query( "UPDATE autopromo SET active = '1' WHERE autopromoid = '$autopromoid'" );
	echo "<script language=\"javascript\">location.href=\"autopromo.php\";</script>";
	}

echo "
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
        </tr>";
	  
include "./footer.php";

function copier_fichier($nom_fic,$imagename)
	{
	if($OpenLocal= fopen($_FILES[$imagename]['tmp_name'], "rb"))
		{
		$LocalFileContent = fread($OpenLocal, $_FILES[$imagename]['size']);
		}
	else
		{
		fclose($OpenLocal);
		die("<b>Erreur : </b>impossible d'ouvrir le fichier local '".$_POST[$imagename].".");
		}
	fclose($OpenLocal);
	if($OpenRemote = fopen($nom_fic, "wb"))
		{
		if(fwrite($OpenRemote, $LocalFileContent))
			{
			fclose($OpenRemote);
			}
		}
	else
		{
		fclose($OpenRemote);
		die("<b>Erreur : </b>impossible d'ouvrir le fichier distant '".$nom_fic.".");
		}
	chmod ("$nom_fic",0777);
	}

?>