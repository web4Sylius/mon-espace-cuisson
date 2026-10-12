<?php
require "admin_global.php";
include "./header.php";
include "./menu.php";


echo "<td valign=\"top\">
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
   	<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
		<tr>
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Panneau d'administration</font>
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
   <br><a href=\"referents.php\"><strong>Retour</strong></a><br><br>" ;

$ajd = date('Y-m-d') ;
$hier = date('Y-m-d', time() - 3600 * 24);
$mois = date('Y-m') ;

echo "<h3>Liens entrants</h3>";

if (isset($action) and $action == "view")
	{
		$topsliencount=$DB_site2->query_first("SELECT sum(hit) from referents where motcle = '$link' ") ;
		echo "<strong>Historique des recherche avec le mot clé <u>$link</u><br><br><br>
		  <table class=\"table_modif_categ\" width=\"98%\" align=\"center\" cellspacing=\"0\">
			<tr>
				<td class=\"td_head_promos\"><strong>Date</strong></td> 
				<td class=\"td_head_promos\"><strong>Hits</strong></td> 
				<td class=\"td_head_promos\"><strong>Position</strong></td> 
			</tr>
			<tr>
				<td colspan=\"3\" align=\"center\" bgcolor=\"#FFCC99\"><font class=\"titre_refs\">Toute la période ($topsliencount[0] clics)</font></td> 
			</tr>" ;
		$i=0;
		$topsliens=$DB_site2->query("SELECT * , min(position) as position from referents where motcle = '$link' group by dateline order by dateline desc ") ;
		while ($toplien=$DB_site2->fetch_array($topsliens))
			{
			$rowalt = "td_promos_".getrowbg () ;
			$datelinetmp=explode("-",$toplien[dateline]);
			$largeur=round($toplien[hit]*400/$topsliencount[0]);
			
			if ($toplien[position] == 0)
				{
				$toplien[position] = "Inconnue" ;
				}
			echo "<tr>
					<td class=\"$rowalt\" align=\"center\">$datelinetmp[2]/$datelinetmp[1]/$datelinetmp[0]</td>
					<td class=\"$rowalt\" align=\"left\"><img src=\"/images/point.gif\" width=\"$largeur\" height=\"15\" align=\"absmiddle\">$toplien[hit]</td>
					<td class=\"$rowalt\" align=\"center\">$toplien[position]</td>
				</tr>" ;
			}

		echo "</table>" ;
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
        </tr>";
include "./footer.php";
?>