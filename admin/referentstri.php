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
echo "
<h3>Moteurs de recherche</h3>
<form method=\"post\" name=\"formu\" action=\"referentstri.php\">
<input type=\"hidden\" name=\"action\" value=\"change\">
<select name=\"moteur\" onchange=\"Javascript:document.formu.submit();\">" ;
$moteursderecherche=$DB_site2->query("SELECT distinct(referent) from referents where motcle != '' order by referent asc") ;
while ($moteurderecherche=$DB_site2->fetch_array($moteursderecherche))
	{		
	if (isset($moteur) and $moteur == $moteurderecherche[0])
		{
		$selected = "selected" ;
		}
	else
		{
		$selected = "" ;
		}
	echo "<option value=\"$moteurderecherche[0]\" $selected>$moteurderecherche[0]</option>" ;
	}
echo "</select>
</form>" ;

if (isset($action) and $action == "change")
	{
	echo "<strong>Mots clés pour le moteur de recherche <font class=\"cost\">$moteur</font></strong> (30 premiers)<br><br><br>
				<table class=\"table_modif_categ\" width=\"98%\" align=\"center\">
					<tr>
						<td class=\"td_head_promos\"><strong>Mots clés</strong></td> 
						<td class=\"td_head_promos\"><strong>Hits</strong></td> 
						<td class=\"td_head_promos\"><strong>Position</strong></td> 
					</tr>
					<tr>
						<td colspan=\"3\" align=\"center\" bgcolor=\"#FFCC99\"><font class=\"titre_refs\">Aujourd'hui</font></td> 
					</tr>" ;
				$i=0;
				$topsliens=$DB_site2->query("SELECT * , sum(hit) as somme , min(position) as position from referents where motcle != '' and dateline = '$ajd' and referent = '$moteur' group by motcle order by somme desc, motcle asc limit 30") ;
				while ($toplien=$DB_site2->fetch_array($topsliens))
					{
					if ($toplien[position] == 0)
						{
						$toplien[position] = "Inconnue" ;
						}
					$rowalt = "td_promos_".getrowbg () ;
					echo "<tr>
							<td class=\"$rowalt\"><a href=\"referentsword.php?action=view&link=$toplien[motcle]\">$toplien[motcle]</a></td>
							<td class=\"$rowalt\" align=\"center\">$toplien[somme] </td>
							<td class=\"$rowalt\" align=\"center\">$toplien[position] </td>
						</tr>" ;
					$i++;
					}
				if($i==0) {
					echo "<tr>
							<td class=\"$rowalt\" colspan=\"3\" align=\"center\">Aucun résultat</td>
						</tr>";
				}
				$i=0;
				echo "<tr>
						<td colspan=\"3\" align=\"center\" bgcolor=\"#FFCC99\"><font class=\"titre_refs\">Hier</font></td> 
					</tr>" ;
				$topsliens=$DB_site2->query("SELECT * , sum(hit) as somme , min(position) as position from referents where motcle != '' and dateline = '$hier' and referent = '$moteur' group by motcle order by somme desc, motcle asc limit 30") ;
				while ($toplien=$DB_site2->fetch_array($topsliens))
					{
					if ($toplien[position] == 0)
						{
						$toplien[position] = "Inconnue" ;
						}
					$rowalt = "td_promos_".getrowbg () ;
					echo "<tr>
							<td class=\"$rowalt\"><a href=\"referentsword.php?action=view&link=$toplien[motcle]\">$toplien[motcle]</a></td>
							<td class=\"$rowalt\" align=\"center\">$toplien[somme] </td>
							<td class=\"$rowalt\" align=\"center\">$toplien[position] </td>
						</tr>" ;
					$i++;
					}
				if($i==0) {
					echo "<tr>
							<td class=\"$rowalt\" colspan=\"3\" align=\"center\">Aucun résultat</td>
						</tr>";
				}
				$i=0;
				echo "<tr>
						<td colspan=\"3\" align=\"center\" bgcolor=\"#FFCC99\"><font class=\"titre_refs\">Ce mois</font></td> 
					</tr>" ;
				$topsliens=$DB_site2->query("SELECT * , sum(hit) as somme , min(position) as position from referents where motcle != '' and dateline LIKE '$mois%' and referent = '$moteur' group by motcle order by somme desc, motcle asc limit 30") ;
				while ($toplien=$DB_site2->fetch_array($topsliens))
					{	
					if ($toplien[position] == 0)
						{
						$toplien[position] = "Inconnue" ;
						}
					$rowalt = "td_promos_".getrowbg () ;
					echo "<tr>
							<td class=\"$rowalt\"><a href=\"referentsword.php?action=view&link=$toplien[motcle]\">$toplien[motcle]</a></td>
							<td class=\"$rowalt\" align=\"center\">$toplien[somme] </td>
							<td class=\"$rowalt\" align=\"center\">$toplien[position] </td>
						</tr>" ;
					$i++;
					}
				if($i==0) {
					echo "<tr>
							<td class=\"$rowalt\" colspan=\"3\" align=\"center\">Aucun résultat</td>
						</tr>";
				}
				$i=0;
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