<?php

require "admin_global.php";
//include "./global.php";
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
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Gestion des langues</font>
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
				
if (isset($action) and $action == "modif")
	{
	$actif = $langueamodifier."actif" ;
	$affiche=$DB_site2->query_first("SELECT $actif from parametresgeneraux");
	switch ($affiche[0])
		{
		case 0:
			$modif = "'1'" ;
			break ;
		case 1:
			$modif = "'0'" ;
			break ;
		}
	//echo "update parametresgeneraux set $actif = $modif" ;
	$DB_site2->query("update parametresgeneraux set $actif = $modif");
	}
				
echo "<h3>Langues disponibles sur ma boutique</h3>
<table class=\"table_moyen_paiement\">
	<tr>
		<td class=\"td_head_comparateurs\" align=\"left\">&nbsp;</td>
		<td class=\"td_head_comparateurs\">Langue</td>
		<td class=\"td_head_comparateurs\">Affichée</td>
		<td class=\"td_head_comparateurs\" align=\"left\">&nbsp;</td>
	</tr>" ;
foreach ($langues as $key => $value) 
	{
	$actif = $value."actif" ;
	$affiche=$DB_site2->query_first("SELECT $actif from parametresgeneraux");
	switch ($affiche[0])
		{
		case '0':
			$affiche = "Non" ;
			break ;
		case '1':
			$affiche = "Oui" ;
			break ;
		}
	switch ($value)
		{
		case 'FR':
			$libellelangue = "Français" ;
			break ;
		case 'DE':
			$libellelangue = "Allemand" ;
			break ;
		case 'EN':
			$libellelangue = "Anglais" ;
			break ;
		case 'IT':
			$libellelangue = "Italien" ;
			break ;
		case 'SP':
			$libellelangue = "Espagnol" ;
			break ;
		}
	if ($value == $languedefault)
		{
		$modif = "DEFAUT" ;
		}
	else
		{
		$modif = "<a href=\"langues.php?action=modif&langueamodifier=$value\"><img src=\"../images/I_Changer.gif\" height=10 width=10></a>" ;
		}
	echo "<tr>
			<td class=\"td_location\" align=\"left\">&nbsp;</td>
			<td class=\"td_location\" align=\"left\">$libellelangue</td>
			<td class=\"td_location\" align=\"center\">$affiche</td>
			<td class=\"td_location\" align=\"center\" valign=\"middle\">$modif</td>
		</tr>" ;
	}
echo "</table>" ;
	
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
		  </td>
        </tr>
";


include "./footer.php";
?>