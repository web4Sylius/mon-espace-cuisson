<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";

echo "
<td valign=\"top\">
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
   	<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
		<tr>
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Uploads de fichiers et commentaires</font>
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

if (in_array ("103", $modules))
	{
	if (isset($img))
		{
		echo "<h3>Copier l'image <strong>$img</strong></h3>
		<form method=\"post\" action=\"copier_upload.php\">
		<input type=\"hidden\" name=\"action\" value=\"copier\">
		<input type=\"hidden\" name=\"img\" value=\"$img\">
		<table width=\"100%\" align=\"center\">
			<tr>
				<td width=\"100%\" align=\"center\">Copier vers : <input type=\"text\" name=\"nom_rep\"></td>
			</tr>
			<tr>
				<td width=\"100%\" align=\"center\"><input type=\"submit\" value=\"copier\"></td>
			</tr>
		</table>
		</form>
		" ;
		}
	if (isset ($action) and $action =="copier")
		{
		$imag = "/home/boutique/www/configurations/$host/images/uploads/$img" ;
		$nom_rep = stripslashes($nom_rep) ;
		//echo "$imag<br>$nom_rep" ;
		//copier_image($nom_rep,$imag);
		}
	}
else
	{
	echo "<script language=\"javascript\">location.href=\"index.php\";</script>";
	}

echo "</td>
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
        </tr>";

include "./footer.php";

function copier_image($nom_fic,$imagename)
	{
	if ($OpenLocal= fopen($imagename, "rb")) 
		{
		$LocalFileContent = fread($OpenLocal, $imagename['size']);
		} 
	else 
		{
		fclose($OpenLocal);
		die("<b>Erreur : </b>impossible d'ouvrir le fichier local '$imagename'");
		}
	fclose($OpenLocal);

	if ($OpenRemote = fopen($nom_fic, "wb")) 
		{
		if (fwrite($OpenRemote, $LocalFileContent)) 
			{
			fclose($OpenRemote);
			}
		} 
	else 
		{
		fclose($OpenRemote);
		die("<b>Erreur : </b>impossible d'ouvrir le fichier distant '$nom_fic'");
		}
	chmod ("$nom_fic",0777);
	}

?>