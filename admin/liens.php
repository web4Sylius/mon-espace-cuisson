<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";
include "./fonctions.php";	

$description = "description$langue" ;
echo "
<td valign=\"top\">
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
   	<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
		<tr>
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Espace liens</font>
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
				<td align=\"center\" valign=\"middle\" width=\"100%\">
" ;

// Vérification module espace liens
$largeur_max= "215";
$hauteur_max = "170" ;

if ($action == "ajout2")
	{
	$erreur = "" ;
	if ($lien == "" or substr($lien, 0, 7) != "http://" or strlen($lien) == 7)
		{
		$erreur .= "Le lien est indispensable et doit commencer par http://" ;
		}
	if ($logo_test != "")
		{ // test sur l'image !!
		if ($_FILES['logo_test']['name']!="")
			{
			if ($_FILES['logo_test']['type']=="image/pjpeg") $type_fichier="jpg";
			if ($_FILES['logo_test']['type']=="image/jpeg") $type_fichier="jpg";
			if ($_FILES['logo_test']['type']=="image/gif") $type_fichier="gif";
			if ($_FILES['logo_test']['type']=="image/png") $type_fichier="png";
			if ($_FILES['logo_test']['type']=="application/x-shockwave-flash") $type_fichier="swf";
			if ($type_fichier != "jpg" && $type_fichier != "gif" && $type_fichier != "png" && $type_fichier != "swf")
				{
				$erreur .= "Le format de votre logo n'est pas pris en compte !<bR>Il doit être de type jpg, gif, png, ou swf." ;
				}
			$taille = GetImageSize($logo_test);
			if (($taille[0] > $largeur_max) or ($taille[1] > $hauteur_max))
				{
				$erreur .= "La taille de votre logo n'est pas acceptée.<bR>Elle peut être au maximum de $largeur_max x $hauteur_max." ;
				}
			}
		}
	if ($erreur != "")
		{ 
		$action = "ajout" ;
		}
	else
		{
		/*$requetetexte="encarttexte='1',";
		if ($encarttexte=="on")
			$requetetexte="encarttexte='1',";
		if ($encartimage=="on")
			$requeteimage="encartimage='1',";
		*/
		$ajout_lien= $DB_site2->query("INSERT INTO liens (lienid,account_id, lien, logo, $description, script) VALUES ('','$account_id', '$lien', '$type_fichier', '$description_lien', '$script')") ;
		$insertid=$DB_site2->insert_id();
		$nom_fic=$rootpath."configurations/$host/images/liens/".$insertid.".".$type_fichier;
		if ($logo_test != "")
			{
			copier_image($nom_fic,'logo_test');
			}
		$txt = "Le lien a bien été ajouté.<br>" ;
		$action = "" ;
		}
	}

if ($action == "ajout")
	{
	if(isset($_GET['from'])){
	      $lien=$DB_site2->query_first("SELECT lienid FROM liens WHERE account_id='".$_GET['from']."'");
	      if($lien!="") print "<script language=\"javascript\">document.location='/admin/liens.php?action=modif&lien=$lien[lienid]';</script>";
	      $account=$DB_site2->query_first("SELECT * FROM crm_accounts WHERE id='".$_GET['from']."'");
	      $lien[libelle]=$account['name'];
	}
	echo "<h3>Ajouter un lien</h3>
	<font class=\"cost\">$erreur</font>
	<form enctype=\"multipart/form-data\" method=\"post\" action=\"liens.php?action=ajout2\">
	<input type=\"hidden\" name=\"account_id\" value=\"".$account['id']."\">
	<table>
		<tr>
			<td align=\"right\" valign=\"middle\">Libellé du lien (+title) : </td>
			<td><input type=\"text\" name=\"nom\" value=\"$lien[libelle]\" size=\"33\"></td>
		</tr>
		<tr>
			<td align=\"right\" valign=\"middle\">Lien vers : </td>
			<td>&nbsp;<input type=\"text\" name=\"lien\" value=\"".$account['website']."\" size=\"33\"></td>
		</tr>
		<tr>
			<td align=\"right\" valign=\"middle\">Logo (facultatif) : </td>
			<td>&nbsp;<input type=\"file\" name=\"logo_test\" size=\"19\"></td>
		</tr>
		<tr>
			<td align=\"right\" valign=\"top\">Description du site partenaire : </td>
			<td>&nbsp;<textarea cols=\"25\" rows=\"4\" name=\"description_lien\">".$account['description']."</textarea></td>
		</tr>
		<tr>
			<td align=\"right\" valign=\"top\">Code HTML : </td>
			<td>&nbsp;<textarea cols=\"25\" rows=\"4\" name=\"script\"></textarea></td>
		</tr>
		</tr>
			<td align=\"right\" valign=\"middle\" >Encart texte</td>
			<td><input type=\"checkbox\" name=\"encarttexte\"></td>
		<tr>
			<td align=\"right\" valign=\"middle\">Encart image</td>
			<td><input type=\"checkbox\" name=\"encartimage\"></td>
		</tr>
	</table>
	<bR><input type=\"submit\" name=\"valider\" value=\"Ajouter\"></form><br>
	<bR><bR><a href=\"liens.php\">Retour</a><br>" ;
	}

if ($action == "suppr")
	{
	$lien=$DB_site2->query_first("select * from liens where lienid = $lien") ;
	echo "<h3>Suppression</h3><bR>" ;
	echo "Etes-vous sûr de vouloir supprimer le lien vers <b>$lien[lien]</b> ?
	<br><br><a href=\"liens.php?action=suppr2&lien=$lien[lienid]\">OUI</a>&nbsp;&nbsp;<a href=\"liens.php\">NON</a><br><br>" ;
	}

if ($action == "suppr2")
	{
	$suppr_lien=$DB_site2->query("delete from liens where lienid = $lien") ;
	$txt = "Lien supprimé." ;
	$action = "" ;
	}

if ($action == "modif2")
	{
	$erreur = "" ;
	
		
	
	if ($lien == "" or substr($lien, 0, 7) != "http://")
		{
		$erreur .= "Le lien est indispensable et doit commencer par <b>http://</b>" ;
		}
	if ($logo_test != "")
		{ // test sur l'image !!
		if ($_FILES['logo_test']['name']!="")
			{
			if ($_FILES['logo_test']['type']=="image/pjpeg") $type_fichier="jpg";
			if ($_FILES['logo_test']['type']=="image/jpeg") $type_fichier="jpg";
			if ($_FILES['logo_test']['type']=="image/gif") $type_fichier="gif";
			if ($_FILES['logo_test']['type']=="image/png") $type_fichier="png";
			if ($_FILES['logo_test']['type']=="application/x-shockwave-flash") $type_fichier="swf";
			if ($type_fichier != "jpg" && $type_fichier != "gif" && $type_fichier != "png" && $type_fichier != "swf")
				{
				$erreur .= "Le format de votre logo n'est pas pris en compte !<bR>Il doit être de type jpg, gif, png, ou swf." ;
				}
			$taille = GetImageSize($logo_test);
			if (($taille[0] > $largeur_max) or ($taille[1] > $hauteur_max))
				{
				$erreur .= "La taille de votre logo n'est pas acceptée.<bR>Elle peut être au maximum de $largeur_max x $hauteur_max." ;
				}
			$nom_fic=$rootpath."configurations/$host/images/liens/".$lienid.".".$type_fichier;
			}
		}
	if ($erreur != "")
		{ 
		$action = "modif" ;
		$lien = $lienid ;
		echo "<center><font color=\"red\">ERREUR !!!</font><br>$erreur</center>" ;
		}
	else
		{
		if ($logo_test != "")
			{
			if ($encarttexte=="on")
				$requetetexte="encarttexte='1',";
			else
				$requetetexte="encarttexte='0',";
			if ($encartimage=="on")
				$requeteimage="encartimage='1',";
			else
				$requeteimage="encartimage='0',";
			$modif_lien= $DB_site2->query("UPDATE liens SET libelle='$nom', lien = '$lien', $requetetexte $requeteimage script = '$script', logo = '$type_fichier',$description = '$description_lien' WHERE lienid = $lienid") ;
			copier_image($nom_fic,'logo_test');
			}
		else
			{
			if ($encarttexte=="on")
				$requetetexte="encarttexte='1',";
			else
				$requetetexte="encarttexte='0',";
			if ($encartimage=="on")
				$requeteimage="encartimage='1',";
			else
				$requeteimage="encartimage='0',";			
			$modif_lien= $DB_site2->query("UPDATE liens SET libelle='$nom', lien = '$lien', $requetetexte $requeteimage script = '$script', $description = '$description_lien' WHERE lienid = $lienid") ;
			}
		$txt = "Modification effectuée." ;
		$action = "" ;
		}
	}

if ($action == "modif")
	{
	$lien=$DB_site2->query_first("select * from liens where lienid = '$lien'") ;
	if (isset($action2) and $action2 == "supprimage")
		{
		$folder = $rootpath."configurations/$host/images/liens";
		$fichier = $lien[lienid].".".$lien[logo] ;
		$dossier = opendir($folder);
		if (file_exists($folder."/".$fichier)) // article
			{
			unlink($folder."/".$fichier) ;
			}
		}
	if ($lien[logo] != "")
		{
		$folder = $rootpath."configurations/$host/images/liens";
		$fichier = $lien[lienid].".".$lien[logo] ;
		$dossier = opendir($folder);
		if (file_exists($folder."/".$fichier)) // article
			{
			$logo = "<a href=\"http://$host/configurations/$host/images/liens/$fichier\" target=\"_blank\">Voir</a> <a href=\"liens.php?action=modif&action2=supprimage&lien=$lien[lienid]\">Supprimer</a>" ;
			}
		else
			{
			$logo = "<input type=\"file\" name=\"logo_test\" value=\"$lien[logo]\" size=\"19\">" ;
			}
		}
	else
		{
		$logo = "<input type=\"file\" name=\"logo_test\" value=\"$lien[logo]\" size=\"19\">" ;
		}
	
	$checked1="";
	$checked2="";
	if ($lien[encarttexte] == 1)
		$checked1="checked";
	if ($lien[encartimage] == 1)
		$checked2="checked";
		
	echo "<h3>Modification</h3>
	<form enctype=\"multipart/form-data\" method=\"post\" action=\"liens.php?action=modif2&lienid=$lien[lienid]\">
	<table>
		<tr>
			<td align=\"right\" valign=\"middle\">Libellé du lien (+title) : </td>
			<td><input type=\"text\" name=\"nom\" value=\"$lien[libelle]\" size=\"33\"></td>
		</tr>
		<tr>
			<td align=\"right\" valign=\"middle\">Lien vers : </td>
			<td><input type=\"text\" name=\"lien\" value=\"$lien[lien]\" size=\"33\"></td>
		</tr>
		<tr>
			<td align=\"right\" valign=\"middle\">Logo (facultatif) : </td>
			<td>$logo</td>
		</tr>
		<tr>
			<td align=\"right\" valign=\"top\">Description du site partenaire : </td>
			<td><textarea cols=\"25\" rows=\"4\" name=\"description_lien\">$lien[$description]</textarea></td>
		</tr>
		<tr>
			<td align=\"right\" valign=\"top\">Code HTML : </td>
			<td><textarea cols=\"25\" rows=\"4\" name=\"script\">$lien[script]</textarea></td>
		</tr>
		<tr>
		</tr>
			<td align=\"right\" valign=\"middle\" >Encart texte</td>
			<td><input type=\"checkbox\" name=\"encarttexte\" $checked1></td>
		<tr>
			<td align=\"right\" valign=\"middle\">Encart image</td>
			<td><input type=\"checkbox\" name=\"encartimage\" $checked2></td>
		</tr>
	</table>
	<bR><input type=\"submit\" name=\"modifier\" value=\"Modifier\"></form><br>
	<bR><bR><a href=\"liens.php\">Retour</a><br>" ;
	}
	
if (!isset($action) or $action == "") 
	{
	echo "<bR><h3>Liens vers des sites partenaires</h3><br><font class=\"cost\">$txt</font><br>" ;
	echo "<table class=\"table_modules_boutiques\">
			<tr>
				<td align=\"center\"><b>Lien vers</b></td>
				<td align=\"center\"><b>Description</b></td>
				<td align=\"center\"><b>&nbsp;</b></td>
			</tr>" ;
	$liens=$DB_site2->query("select * from liens order by lien asc") ;
	if ($DB_site2->num_rows($liens) > 0)
		{
		while ($lien=$DB_site2->fetch_array($liens))
			{
			$rowalt = "td_".getrowbg();
			echo "<tr>
					<td class=\"$rowalt\" align=\"center\">$lien[lien]</td>
					<td class=\"$rowalt\" align=\"center\">$lien[descriptionFR]</td>
					<td class=\"$rowalt\" align=\"center\"><a href=\"liens.php?action=modif&lien=$lien[lienid]\">Modifier</a>&nbsp;&nbsp;
					<a href=\"liens.php?action=suppr&lien=$lien[lienid]\">Supprimer</a></td></tr>" ;
			} 
		}
	else
		{
		echo "<tr><td></td><td></td><td></td><td></td></tr>" ;
		}
	echo "</table><bR><bR><a href=\"liens.php?action=ajout\">Ajouter un lien</a><br>" ;
	}

echo "<br><br><h5>Taille maximale : $largeur_max x $hauteur_max - Formats autorisés : jpg jpeg gif png swf.</h5				</td>
			</tr>	
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
?>
