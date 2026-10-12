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
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Suppression d'un rayon</font>
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
		<table align=\"center\">
			<tr>
				<td align=\"center\" valign=\"middle\">" ;

if ($action=="")
	{
	echo "Etes-vous sûr(e) de vouloir supprimer ce rayon?<br><br>
	La supression de ce rayon entrainera la suppression de tous les sous-rayons et articles associés<br><br>
	<a href=\"supprimer_categorie.php?action=delcategorie&catid=$catid\">Oui</a>&nbsp;&nbsp;
	<a href=\"catalogue.php?catid=$catid\">Non</a>";
	}

if ($action=="delcategorie")
	{
	$position_page=$DB_site2->query_first("SELECT position, parentid FROM categorie WHERE catid = '$catid'");
	$position_page=$position_page[0];
	$rq_positions_suivantes=$DB_site2->query("SELECT catid, position FROM categorie WHERE position > $position_page and parentid = '$position_page[parentid]'" );
	while ($rs_positions_suivantes=$DB_site2->fetch_array($rq_positions_suivantes)) 
		{
		$position_temp = $rs_positions_suivantes[position] - 1;
		$DB_site2->query("UPDATE categorie SET position = '$position_temp' WHERE catid = '$rs_positions_suivantes[catid]'");
		}	
	$catids=lister_catid($catid);$catids=substr($catids,0,strlen($catids)-1);
	$parentid=$DB_site2->query_first("select parentid from categorie where catid=$catid");
	$parentid=$parentid[0];
	$arts=$DB_site2->query("select artid from article where catid IN ($catids)");
	$liste_arts = "0" ;
	while ($art=$DB_site2->fetch_array($arts))
		{
		$liste_arts .= ",".$art[artid] ;
		$arts_lies=$DB_site2->query("SELECT a.artid, a.libelleFR FROM liens_articles la, article a 
		WHERE ((a.artid = la.article_base AND article_lie = '$art[artid]') OR (a.artid = la.article_lie AND article_base = '$art[artid]'))");
		while ($art_lie=$DB_site2->fetch_array($arts_lies))
			{
			$liste_arts .= ",".$art_lie[artid] ;
			}
		}


	if (in_array("3", $modules))
		{	// Si le module "Remise" est pris en compte
		$DB_site2->query("delete from remisecategorie where catid in ($catids)");
		$DB_site2->query("delete from remisearticle where artid IN ($liste_arts)");
		}
	if (in_array("5", $modules))
		{	// Si le module "Promotion" est pris en compte
		$requete="delete from promotion where artid IN ($liste_arts)";
		$DB_site2->query($requete);
		}
	if (in_array("21", $modules))
		{	// Si le module "Coup de coeur" est pris en compte
		$DB_site2->query("delete from coupdecoeur where artid IN ($liste_arts)");
		}
	if( in_array( "18", $modules ) )
		{	// Si le module "Votes et Commentaires" est pris en compte
		$DB_site2->query("delete from articlevote where artid IN ($liste_arts)");
		}
	if (in_array("4", $modules)) 
		{
		//Modules Stocks, suppression des stocks et des historiques de stocks
		$DB_site2->query("DELETE FROM stock_historique WHERE stockid IN(SELECT stockid FROM stocks WHERE artid IN($liste_arts));");
		$DB_site2->query("DELETE FROM stocks WHERE artid IN($liste_arts);");
		}

	$DB_site2->query("delete from categorie where catid in ($catids)");
	$DB_site2->query("delete from categorie_caracteristique where catid in ($catids)");
	$DB_site2->query("delete from article_caractval where artid in ($liste_arts)");
	$DB_site2->query("delete from article where catid in ($catids)");
	$DB_site2->query("delete from article where artid in ($liste_arts)");

	// mise à jour du treeview
	$DB_site2->query("update treeview_temporaire set dateline = dateline - 10000");

	echo "<script language=\"javascript\">location.href=\"catalogue.php?catid=$parentid\";</script>";
	}

function lister_catid($catid)
	{
	global $DB_site2;
	$cats=$DB_site2->query("select catid from categorie where parentid=$catid");
	while ($cat=$DB_site2->fetch_array($cats)) 
		{
		$retour.=lister_catid($cat['catid']);
		}
	$retour.=$catid.",";
	return ($retour);
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
?>