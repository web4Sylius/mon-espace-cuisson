<?php

require "admin_global.php";
echo "<script language=\"javascript\"> 
<!-- 
window.moveTo(700,425);
function fermersimple()
	{
	window.close() ;
	}
//-->
</script>" ;
/*echo "<script language=\"javascript\"> 
<!-- 
  function fermer()
	{
	window.opener.document.formu.new.value = 1 ;
	window.opener.document.formu.action2.value = 'caract' ;
	window.opener.document.formu.old.value = 'caract' ;
	window.opener.document.formu.submit () ;
	window.close() ;
	}
//-->
</script>" ;*/

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
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Ajouter une valeur de caractéristique</font>
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
	if (in_array("FR" , $langues))
		{
		if ($libelleFR == '')
			{
			$erreur .= " en français " ;
			}
		}
	if (in_array("DE" , $langues))
		{
		if ($libelleDE == '')
			{
			$erreur .= " en allemand " ;
			}
		}
	if (in_array("EN" , $langues))
		{
		if ($libelleEN == '')
			{
			$erreur .= " en anglais " ;
			}
		}
	if (in_array("IT" , $langues))
		{
		if ($libelleIT == '')
			{
			$erreur .= " en italien " ;
			}
		}
	if (in_array("SP" , $langues))
		{
		if ($libelleSP == '')
			{
			$erreur .= " en espagnol " ;
			}
		}
	if ($erreur == "")
		{
		$DB_site2->query("INSERT INTO caracteristiquevaleur (caractid, libelleFR, libelleDE, libelleEN, libelleIT, libelleSP)
		   VALUES ('$caractid', '$libelleFR', '$libelleDE', '$libelleEN','$libelleIT', '$libelleSP')") ;
		echo "<script language=\"javascript\">
			window.opener.document.formu.action2.value = 'caract' ;
			window.opener.document.formu.old.value = 'caract' ;
			window.opener.document.formu.submit () ;
			window.close() ;
			</script>" ;
		}
	else
		{
		echo "<font class=\"cost\">Vous devez saisir un libellé $erreur</font>" ;
		}
	}


echo "<form method=\"post\" style=\"display:inline;\">
		<input type=\"hidden\" name=\"action\" value=\"ajouter2\">
		<input type=\"hidden\" name=\"caractid\" value=\"$caractid\">
		<table>" ;
		
	if (in_array("FR" , $langues))
		{
		echo "<tr><td>Valeur FR :</td><td><input type=\"text\" name=\"libelleFR\" value=\"\"></td></tr>" ;
		}
	if (in_array("DE" , $langues))
		{
		echo "<tr><td>Valeur DE :</td><td><input type=\"text\" name=\"libelleDE\" value=\"\"></td></tr>" ;
		}
	if (in_array("EN" , $langues))
		{
		echo "<tr><td>Valeur EN :</td><td><input type=\"text\" name=\"libelleEN\" value=\"\"></td></tr>" ;
		}
	if (in_array("IT" , $langues))
		{
		echo "<tr><td>Valeur IT :</td><td><input type=\"text\" name=\"libelleIT\" value=\"\"></td></tr>" ;
		}
	if (in_array("SP" , $langues))
		{
		echo "<tr><td>Valeur SP :</td><td><input type=\"text\" name=\"libelleSP\" value=\"\"></td></tr>" ;
		}
		echo "</table>
		<br><input type=\"submit\" value=\"Créer cette valeur\">&nbsp;&nbsp;<input type=\"button\" value=\"Retour\" onclick=\"Javascript: fermersimple() ;\">
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
