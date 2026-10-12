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
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Supprimer un produit</font>
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
				<td align=\"center\" valign=\"middle\" width=\"100%\">
" ;
$requete="select catid from article where artid=$artid";
$catid=$DB_site2->query_first($requete);
$catid=$catid[0];


if ($action=="")
	{
	echo "Etes-vous sûr(e) de vouloir supprimer ce produit ?<br><bR>
		<a href=\"supprimer_produit.php?action=delproduit&artid=$artid\">Oui</a> | <a href=\"catalogue.php?catid=$catid\">Non</a>";
	}

if ($action=="delproduit")
	{
	// position
	$positions=$DB_site2->query_first("select artid, page, pageposition, catid from article where artid = '$artid'");
	$autresarticles=$DB_site2->query("select artid, page, pageposition from article where catid = '$positions[catid]' and page >= '$positions[page]' order by page, pageposition");
	if ($DB_site2->num_rows($autresarticles) > 1)
		{
		$tabpositions = array();
		$i = 0 ;
		while ($autrearticle=$DB_site2->fetch_array($autresarticles))
			{
			if ($autrearticle[artid] == $artid)
				$depart = $i + 1 ;
			$tabpositions[$i][artid] = $autrearticle[artid] ;
			$tabpositions[$i][page] = $autrearticle[page] ;
			$tabpositions[$i][pageposition] = $autrearticle[pageposition] ;
			$i++;
			}
		for ($j = $depart ; $j < count($tabpositions) ; $j++)
			{
			$k = $j - 1 ;
			$DB_site2->query("update article set page = '".$tabpositions[$k][page]."', pageposition = '".$tabpositions[$k][pageposition]."' where artid = '".$tabpositions[$j][artid]."'");
			}
		}
	
	
	if (in_array("105", $modules))
		{	// "liens"
		$arts_lies=$DB_site2->query("SELECT a.artid FROM liens_articles la, article a 
		WHERE ((a.artid = la.article_base AND article_lie = '$artid') OR (a.artid = la.article_lie AND article_base = '$artid'))");
		while ($art_lie=$DB_site2->fetch_array($arts_lies))
			{
			$DB_site2->query("delete from article where artid = $art_lie[artid]");
			$DB_site2->query("delete from articlephoto where artid=$art_lie[artid]");
			if (in_array("18", $modules))
				{	// "Votes et Commentaires"
				$DB_site2->query("delete from articlevote where artid=$art_lie[artid]");
				}
			$DB_site2->query("delete from article_cadeau where artid=$art_lie[artid]");
			$DB_site2->query("delete from article_caractval where artid=$art_lie[artid]");
			$DB_site2->query("delete from article_marque where artid=$art_lie[artid]");
			$DB_site2->query("delete from stock where artid=$art_lie[artid]");
			if (in_array("5", $modules))
				{	// "Promotion"
				$DB_site2->query("delete from promotion where artid=$art_lie[artid]");
				}
			if (in_array("3", $modules))
				{	// "Remise"
				$DB_site2->query("delete from remisearticle where artid=$art_lie[artid]");
				}
			if (in_array("21", $modules))
				{	// "Coup de coeur"
				$DB_site2->query("delete from coupdecoeur where artid=$art_lie[artid]");
				}
			if (in_array("4", $modules)) 
				{
				//Supprimer stock et historique
				$DB_site2->query("DELETE FROM stock_historique WHERE stockid IN(SELECT stockid FROM stocks WHERE artid = '$art_lie[artid]');");
				$DB_site2->query("DELETE FROM stocks WHERE artid = '$art_lie[artid]';");
				}
			$DB_site2->query("delete from lignepanier where artid=$art_lie[artid]");
			}
		}
	$DB_site2->query("delete from article where artid = $artid");
	$DB_site2->query("delete from articlephoto where artid=$artid");
	if (in_array("18", $modules))
		{	// "Votes et Commentaires"
		$DB_site2->query("delete from articlevote where artid=$artid");
		}
	$DB_site2->query("delete from article_cadeau where artid=$artid");
	$DB_site2->query("delete from article_caractval where artid=$artid");
	$DB_site2->query("delete from article_marque where artid=$artid");
	$DB_site2->query("delete from stock where artid=$artid");
	if (in_array("5", $modules))
		{	// "Promotion"
		$DB_site2->query("delete from promotion where artid=$artid");
		}
	if (in_array("3", $modules))
		{	// "Remise"
		$DB_site2->query("delete from remisearticle where artid=$artid");
		}
	if (in_array("21", $modules))
		{	// "Coup de coeur"
		$DB_site2->query("delete from coupdecoeur where artid=$artid");
		}
	if (in_array("4", $modules)) 
		{
		//Supprimer stock et historique
		$DB_site2->query("DELETE FROM stock_historique WHERE stockid IN(SELECT stockid FROM stocks WHERE artid = '$artid');");
		$DB_site2->query("DELETE FROM stocks WHERE artid = '$artid';");
		}
	$DB_site2->query("delete from lignepanier where artid=$artid");

// mise à jour du treeview
	$DB_site2->query("update treeview_temporaire set dateline = dateline - 10000");

	echo "<script language=\"javascript\">location.href=\"catalogue.php?catid=$catid\";</script>";
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