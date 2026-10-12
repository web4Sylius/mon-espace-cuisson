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
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Exporter le catalogue au format csv</font>
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
   <td align=\"center\" valign=\"middle\">
   <div align=\"left\" style=\"margin:10px;\">   Grâce aux liens ci-dessous, vous pouvez exporter les données principales de votre boutique au format CSV (fichier de données séparées par des virgules).<br>
   Cela vous permet notamment de sauvegarder vos données.
   <br><br>
   <div align=\"left\" style=\"margin:50px;\">
   <a href=\"csvarticles.php\"><strong>Articles</strong></a><br>
   Liste des articles (libellé, description, prix, caractéristiques...)<br>
   <br><a href=\"csvarticles2.php\"><strong>Articles avec Titre2</strong></a><br>
   Liste des articles (libellé, description, prix, caractéristiques...)<br>
 <br> <a href=\"csvfactures.php\"><strong>Factures</strong></a><br>
 Liste des factures (client, liste des articles, montant total, tva...)<br>
 <br> <a href=\"csvusers.php\"><strong>Utilisateurs</strong></a><br>
 Liste des personnes inscrites (nom, mail, coordonnées...)<br>
 <br> <a href=\"csvmails.php\"><strong>Adresses e-mail</strong></a><br>
 Liste des adresses e-mail des personnes inscrites<br>
 </div>
  <br>
  </div></td>
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



?>

