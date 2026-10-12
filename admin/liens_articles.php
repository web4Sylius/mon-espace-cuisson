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
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Liens entre articles</font>
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

if (in_array ("105", $modules))
	{
	// suppression
	if (isset($action) and $action == "suppr")
		{
		echo "<h3>Supprimer les liens d'un article</h3>
		<br><br>Etes-vous sûr(e) de vouloir supprimer tous les liens de cet article ?<br>
		<b>ATTENTION CETTE ACTION EST IRREVERSIBLE !!!</b><br><br>
		<a href=\"liens_articles.php?action=suppr2&art=$art\">OUI</a> | <a href=\"liens_articles.php\">NON</a>" ;
		}
	if (isset($action) and $action == "suppr2")
		{
		$del=$DB_site2->query("DELETE FROM liens_articles WHERE article_base = '$art'") ;
		echo "<script language=\"javascript\">location.href=\"liens_articles.php?suppr=1\";</script>";
		}
	if (isset($action) and $action == "suppr_uniq")
		{
		echo "<h3>Supprimer le lien d'un article</h3>
		<br><br>Etes-vous sûr(e) de vouloir supprimer ce lien ?<br>
		<b>ATTENTION CETTE ACTION EST IRREVERSIBLE !!!</b><br><br>
		<a href=\"liens_articles.php?action=suppr_uniq2&art_lie=$art_lie&art_base=$art_base\">OUI</a> | <a href=\"liens_articles.php?action=voir&art=$art_base\">NON</a>" ;
		}
	if (isset($action) and $action == "suppr_uniq2")
		{
		$del=$DB_site2->query("DELETE FROM liens_articles WHERE article_lie = '$art_lie' and article_base = '$art_base'") ;
		echo "<script language=\"javascript\">location.href=\"liens_articles.php?action=voir&art=$art_base\";</script>";
		}
	if (isset($action) and $action == "suppr_multi")
		{
		// liste des liens 
		$nb_choisis = count ($chk) ;
		if ($nb_choisis > 0)
			{
			$art_lies = "" ;
			while (list($key, $val) = each($chk))
				{
				$art_lies .= "$key," ;
				}
			$lg = strlen($art_lies) - 1 ;
			$art_lies = substr ($art_lies, 0, -1) ;
			}
		else
			{
			echo "<script language=\"javascript\">location.href=\"liens_articles.php?action=voir&art=$art_base\";</script>";
			}
		echo "<h3>Supprimer certains liens d'un article</h3>
		<br><br>Etes-vous sûr(e) de vouloir supprimer ces liens ?<br>
		<b>ATTENTION CETTE ACTION EST IRREVERSIBLE !!!</b><br><br>
		<a href=\"liens_articles.php?action=suppr_multi2&art_lies=$art_lies&art_base=$art_base\">OUI</a> | <a href=\"liens_articles.php?action=voir&art=$art_base\">NON</a>" ;
		}
	if (isset($action) and $action == "suppr_multi2")
		{
		$del=$DB_site2->query("DELETE FROM liens_articles WHERE article_lie IN ($art_lies) and article_base = '$art_base'") ;
		echo "<script language=\"javascript\">location.href=\"liens_articles.php?action=voir&art=$art_base\";</script>";
		}
	// visualisation
	if (isset($action) and $action == "voir")
		{
		$articles_lies=$DB_site2->query("select * from article a, liens_articles la 
										where a.artid = la.article_lie and la.article_base = '$art'") ;
		if ($DB_site2->num_rows($articles_lies) == 0)
			{
			echo "<script language=\"javascript\">location.href=\"liens_articles.php\";</script>";
			}
		echo "<h3>Liste des liens d'un article</h3>
		<br>
		<form method=\"post\" action=\"liens_articles.php\">
		<input type=\"hidden\" name=\"action\" value=\"suppr_multi\">
		<input type=\"hidden\" name=\"art_base\" value=\"$art\">
			<table class=\"table_caracts\" align=\"center\">
				<tr>
					<td>&nbsp;</td>
					<td class=\"td_head_promos\" width=\"15%\"><strong>N°</strong></td>
					<td class=\"td_head_promos\" width=\"50%\"><strong>Libellé</strong></td>
					<td class=\"td_head_promos\" width=\"30%\"><strong>ACTION</strong></td>
				</tr>" ;
		while ($article_lie=$DB_site2->fetch_array($articles_lies)) 
			{
			$cat=$DB_site2->query_first("select libelleFR from categorie where catid = '$article_lie[catid]'") ;
			echo "<tr>
					<td valign=\"middle\"><input type=\"checkbox\" name=\"chk[$article_lie[artid]]\">&nbsp;</td>
					<td align=\"center\"><font class=\"cost\">$article_lie[artid]</font></td>
					<td valign=\"top\"><b>Catégorie : $cat[0]</b><br>$article_lie[libelleFR]</td>
					<td align=\"center\"><a href=\"liens_articles.php?action=suppr_uniq&art_lie=$article_lie[artid]&art_base=$art\">Supprimer ce lien</a></td>
				</tr>" ;
			}		
		echo "</table>
			<br><input type=\"submit\" value=\"Supprimer les liens sélectionnés\">
		</form>
		<br><br><a href=\"liens_articles.php\">Retour</a><br><br>
		Pour information : Supprimer le lien entre deux articles ne supprime pas les articles,<bR>
		Ils sont simplement dissociés et deviennent indépendants lors des modifications." ;
		}
	//affichage général
	if (!isset($action) or $action == "")
		{
		echo "<h3>Liste des articles possédant des liens</h3>" ;
		if (isset($suppr) and $suppr == 1)
			{
			echo "<font class=\"cost\">Suppression effectuée</font><bR><bR>" ;
			}
		$liens_arts=$DB_site2->query("SELECT * FROM liens_articles la, article a where a.artid = la.article_base group by la.article_base") ;
		if ($DB_site2->num_rows($liens_arts) > 0)
			{
			echo "<table width=\"80%\" align=\"center\">
					<tr>
						<td class=\"td_head_promos\" width=\"15%\"><strong>N°</strong></td>
						<td class=\"td_head_promos\" width=\"55%\"><strong>Libellé</strong></td>
						<td class=\"td_head_promos\" width=\"30%\"><strong>ACTION</strong></td>
					</tr>
					<tr>
						<td colspan=\"3\"><hr></td>
					</tr>
					" ;
			while ($lien_art=$DB_site2->fetch_array($liens_arts)) 
				{
				$cat=$DB_site2->query_first("select libelleFR from categorie where catid = '$lien_art[catid]'") ;
				echo "<tr>
						<td align=\"center\"><font class=\"cost\">$lien_art[artid]</font></td>
						<td valign=\"top\"><b>Catégorie : $cat[0]</b><br>$lien_art[libelleFR]</td>
						<td align=\"center\"><a href=\"liens_articles.php?action=voir&art=$lien_art[artid]\">Voir les articles liés</a>
							<br><a href=\"liens_articles.php?action=suppr&art=$lien_art[artid]\">Supprimer tous les liens</a></td>
					</tr>
					<tr>
						<td colspan=\"3\"><hr></td>
					</tr>" ;
				}		
			echo "</table>
			<br><br><a href=\"index.php\">Retour</a>" ;
			}
		else
			{
			echo "<br>A ce jour, il n'existe aucun lien entre articles.
			<br><br><a href=\"index.php\">Retour</a>" ;
			}
		}
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
        </tr>
";


include "./footer.php";
?>