<?php

require "admin_global.php";
echo "<script language=\"javascript\"> 
<!-- 
window.moveTo(420,375);

function fermer(id)
	{
	if (id != 0)
		{
		window.opener.document.formu.mq2.value = id  ;
		window.opener.document.formu.submit () ;
		} 
	window.close() ;
	} 
//-->
</script>" ;

echo "
<html>
<LINK href=\"vendre-en-ligne.css\" type=\"text/css\" rel=\"stylesheet\">
<body>
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
   	<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
		<tr>
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Ajouter une marque</font>
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
if (isset($action) and $action == "ajouter2")
	{
	$erreur = "" ;
	if ($mq_libelle != "")
		{
		$last=$DB_site2->query_first("select max(position) from marque");
		$position = $last[0] + 1 ;
		$new=$DB_site2->query("insert into marque (libelle, image, position) values ('$mq_libelle', '$type_fichier', '$position')");
		$insertid=$DB_site2->insert_id();
		if ($mq_image != "")
			{
			if ($_FILES['mq_image']['name'] != "")
				{
				if ($_FILES['mq_image']['type'] == "image/pjpeg") 
					{
					$type_fichier = "jpg" ;
					}
				elseif ($_FILES['mq_image']['type']=="image/jpeg") 
					{
					$type_fichier = "jpg" ;
					}
				elseif ($_FILES['mq_image']['type'] == "image/gif") 
					{
					$type_fichier = "gif" ;
					}
				else
					{
					$erreur = "Le format n'est pas autorisé" ;
					}
				if ($erreur == "")
					{
					$nom_fic="/home/boutique/www/configurations/$host/images/marques/".$insertid.".".$type_fichier;
					copier_image($nom_fic,'mq_image');
					echo "<script language=\"javascript\">fermer($insertid) ;</script>" ;
					}
				else
					{
					echo $erreur ;
					}
				}
			}
		else
			{
			echo "<script language=\"javascript\">fermer($insertid) ;</script>" ;
			}
		}
	else
		{
		echo "Le libellé est un champ obligatoire" ;
		}
	}


echo "	<form method=\"post\" enctype=\"multipart/form-data\" style=\"display:inline\" action=\"petit_marque.php\">
<input type=\"hidden\" name=\"action\" value=\"ajouter2\">
	<br><table>
		<tr>
			<td>Libellé : </td>
			<td><input type=\"text\" name=\"mq_libelle\" value=\"$mq_libelle\"></td>
		</tr>
		<tr>
			<td>Image : </td>
			<td><input type=\"file\" name=\"mq_image\"></td>
		</tr>
	</table>
	<input type=\"submit\" value=\"Ajouter\">&nbsp;&nbsp;
	<input type=\"button\" value=\"Retour\" onclick=\"Javascript: fermer(0) ;\">
	</form>" ;

echo "</td>
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
</body>
</html>";
			
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
