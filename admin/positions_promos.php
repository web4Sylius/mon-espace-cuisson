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
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Gestion des promotions</font>
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

	
// MONTER D'UNE POSITION 
if (isset ($action) and $action == "up")
	{
	$position=$DB_site2->query_first("SELECT position FROM promotion WHERE promotionid = '$promotionid'");						
	$nouvelle_position_marque = $position[0] - 1 ;
	$position2=$DB_site2->query_first("SELECT promotionid FROM promotion WHERE position = '$nouvelle_position_marque'");
	$DB_site2->query("UPDATE promotion SET position = '$nouvelle_position_marque' WHERE promotionid = '$promotionid'");
	$DB_site2->query("UPDATE promotion SET position = '$position[0]' WHERE promotionid = '$position2[promotionid]'");
	echo "<script language=\"javascript\">location.href=\"positions_promos.php\";</script>";
	}

// DESCENDRE D'UNE POSITION 
if (isset ($action) and $action == "down")
	{
	$position=$DB_site2->query_first("SELECT position FROM promotion WHERE promotionid = '$promotionid'");						
	$nouvelle_position_marque = $position[0] + 1 ;
	$position2=$DB_site2->query_first("SELECT promotionid FROM promotion WHERE position = '$nouvelle_position_marque'" );
	$DB_site2->query("UPDATE promotion SET position = '$nouvelle_position_marque' WHERE promotionid = '$promotionid'");
	$DB_site2->query("UPDATE promotion SET position = '$position[0]' WHERE promotionid = '$position2[promotionid]'");
	echo "<script language=\"javascript\">location.href=\"positions_promos.php\";</script>";
	}

if (!isset($action) or $action == "")
	{
	$promos=$DB_site2->query("SELECT promotionid FROM promotion ORDER BY position");
	$i = 0 ;
	while ($promo=$DB_site2->fetch_array($promos))
		{
		$i++ ;
		$DB_site2->query("update promotion set position = '$i' where promotionid = '$promo[promotionid]'");
		}
	echo "<h3>Affichage des promotions en cours</h3>
	<br>
	<table class=\"table_promos\">
			<tr><td></td>
				<td class=\"td_head_promos\" width=\"35\"><b>&nbsp;</b></td>
				<td class=\"td_head_promos\"><b>Article</b></td>
				<td class=\"td_head_promos\"><b>Début</b></td>
				<td class=\"td_head_promos\"><b>Fin</b></td>
				<td class=\"td_head_promos\"><b>Prix base</b></td>
				<td class=\"td_head_promos\"><b>Prix promo</b></td>
				<td></td>
			</tr>";
	$ajd = date('Y-m-d') ;
	$rq_promo=$DB_site2->query("SELECT * FROM promotion WHERE datedebut <= '$ajd' AND datefin >= '$ajd' ORDER BY position");
	$nb_marques=$DB_site2->num_rows($rq_promo) ;
	$i_marque = 0 ;
	while ($rs_promo=$DB_site2->fetch_array($rq_promo))
		{
		$i_marque++ ;
		if ($nb_marques > 1)
			{
			if ($i_marque == 1) // Première marque
				{
				$images_position = "<img src=\"images/vide.gif\" height=\"7\">
									<a href=\"positions_promos.php?action=down&promotionid=$rs_promo[promotionid]\">
									<img src=\"images/descendre.gif\" alt=\"Descendre\" title=\"Descendre\" height=\"7\" border=\"0\"></a>";
				}
			elseif ($i_marque == $nb_marques) // Dernière marque
				{
				$images_position = "<a href=\"positions_promos.php?action=up&promotionid=$rs_promo[promotionid]\">
									<img src=\"images/monter.gif\" alt=\"Monter\" title=\"Monter\" height=\"7\" border=\"0\"></a>
									<img src=\"images/vide.gif\" height=\"7\">";
				}
			else
				{
				$images_position = "<a href=\"positions_promos.php?action=up&promotionid=$rs_promo[promotionid]\">
									<img src=\"images/monter.gif\" alt=\"Monter\" title=\"Monter\" height=\"7\" border=\"0\"></a>
									<a href=\"positions_promos.php?action=down&promotionid=$rs_promo[promotionid]\">
									<img src=\"images/descendre.gif\" alt=\"Descendre\" title=\"Descendre\" height=\"7\" border=\"0\"></a>";
				}
			}
		$art=$DB_site2->query_first("SELECT $libelle, prix FROM article WHERE artid = '$rs_promo[artid]'");
		$art[prix] = number_format($art[prix], 2, ",", " ") ;
		$rs_promo[prix] = number_format($rs_promo[prix], 2, ",", " ") ;
		$rowalt = "td_".getrowbg();
		echo "<tr><td></td>
				<td class=\"$rowalt\" align=\"center\">$images_position</td>
				<td class=\"$rowalt\">$art[$libelle]</td>
				<td class=\"$rowalt\" align=\"center\">$rs_promo[datedebut]</td>
				<td class=\"$rowalt\" align=\"center\">$rs_promo[datefin]</td>
				<td class=\"$rowalt\" align=\"center\">$art[prix] €</td>
				<td class=\"$rowalt\" align=\"center\"><font class=\"cost\">$rs_promo[prix] €</font></td>
				<td></td>
			</tr>";
		}
	
	echo "</table><br>";
	echo "<br><br><a href=\"index.php\" target=\"_self\">Retour</a>";
	}


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