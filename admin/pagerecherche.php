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
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Page de recherche de la boutique</font>
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

if (isset ($action) and $action == "modif")
	{
	$critere_a_modifier=$DB_site2->query_first("select autorise from recherche_type where recherchetypeid = '$critere'");
	switch ($critere_a_modifier[autorise])
		{
		case 0:
			$modif_accepte = '1' ;
			break ;
		case 1:
			$modif_accepte = '0' ;
			break ;
		}
	$DB_site2->query("UPDATE recherche_type set autorise = '$modif_accepte' where recherchetypeid = '$critere'");
	echo "<script language=\"javascript\">location.href=\"pagerecherche.php\";</script>";
	}

if (isset ($action) and $action == "suppr")
	{
	$DB_site2->query("delete from recherche_prix where trancheid = '$trancheid'");
	echo "<script language=\"javascript\">location.href=\"pagerecherche.php\";</script>";
	}
	
if (isset ($action) and $action == "ajout")
	{
	if (isset($debut) and $debut != "" and isset($fin) and $fin != "")
		{
		$erreur = "" ;
		if (!is_numeric($debut) or !is_numeric($fin))
			{
			$erreur .= "Les deux champs doivent être numériques<bR>" ;
			}
		if ($debut > $fin)
			{
			$erreur .= "Le début doit être inférieur à la fin<bR>" ;
			}
		$tranches=$DB_site2->query("SELECT * from recherche_prix order by debut asc");
		while ($tranche=$DB_site2->fetch_array($tranches)) 
			{
			if (($debut > $tranche[debut] and $debut < $tranche[fin]) or ($fin > $tranche[debut] and $fin < $tranche[fin]))
				{
				$erreur .= "Conflit avec $tranche[debut] / $tranche[fin].<bR>" ;
				}
			}
		if ($erreur == "")
			{
			$DB_site2->query("insert into recherche_prix (debut, fin) values ('$debut', '$fin')");
			echo "<script language=\"javascript\">location.href=\"pagerecherche.php\";</script>";
			}
		}
	}

echo "<h3>Types de recherches autorisées</h3>
<table class=\"table_moyen_paiement\">
	<tr>
		<td class=\"td_head_comparateurs\" align=\"left\">&nbsp;</td>
		<td class=\"td_head_comparateurs\">Critère</td>
		<td class=\"td_head_comparateurs\">Accepté</td>
		<td class=\"td_head_comparateurs\" align=\"left\">&nbsp;</td>
	</tr>" ;
$criteres_recherche=$DB_site2->query("SELECT * from recherche_type order by libelleFR asc");
while ($critere_recherche=$DB_site2->fetch_array($criteres_recherche)) 
	{
	switch ($critere_recherche[autorise])
		{
		case 0:
			$accepte = "Non" ;
			break ;
		case 1:
			$accepte = "Oui" ;
			break ;
		}
	$moy = $critere_recherche[recherchetypeid] ;
	echo "<tr>
			<td class=\"td_location\" align=\"left\">&nbsp;</td>
			<td class=\"td_location\" align=\"left\">$critere_recherche[libelleFR]</td>
			<td class=\"td_location\" align=\"center\">$accepte</td>
			<td class=\"td_location\" align=\"center\" valign=\"middle\"><a href=\"pagerecherche.php?action=modif&critere=$critere_recherche[recherchetypeid]\">
				<img src=\"./images/I_Changer.gif\" height=10 width=10></a></td>
		</tr>" ;
	}
echo "</table>" ;

echo "<br><br><br>
<table width=\"80%\">
	<tr>
		<td align=\"justify\">
			<strong>Recherche par prix</strong><br>Pour la recherche par prix, 
			vous devez au préalable définir les tranches de prix.<br><br>
			<input type=\"hidden\" name=\"action\" value=\"modif_tranches\">
			<table class=\"table_moyen_paiement\" align=\"center\">
				<tr valign=\"middle\">
					<td align=\"left\" valign=\"middle\"><center><strong>$erreur</strong></center>
						<ul>" ;
						$tranches=$DB_site2->query("SELECT * from recherche_prix order by debut asc");
						while ($tranche=$DB_site2->fetch_array($tranches)) 
							{
							echo "<li>De $tranche[debut] € &nbsp;&nbsp; à $tranche[fin] € &nbsp;&nbsp; 
									<a href=\"pagerecherche.php?action=suppr&trancheid=$tranche[trancheid]\">Supprimer</a></li>" ;
							}
						echo "<br><li><form method=\"post\" action=\"pagerecherche.php\">
								<input type=\"hidden\" name=\"action\" value=\"ajout\">
								De <input type=\"text\" name=\"debut\" value=\"$debut\" size=\"3\"> €&nbsp;&nbsp;
								à <input type=\"text\" name=\"fin\" value=\"$fin\" size=\"3\"> € 
								&nbsp;&nbsp;<input type=\"submit\" value=\"Créer\"></form></li>
						</ul>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>" ;

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

?>