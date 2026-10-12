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
   <td align=\"center\" valign=\"middle\">" ;

$referent_facture_query = "SELECT referentid, total, nb_fact 
								FROM referents 
							INNER JOIN (SELECT facture.referentid, SUM(lf.subtotal+facture.prixport+facture.prixgestion) as total, COUNT(facture.facturid) as nb_fact FROM facture 
											INNER JOIN 
 												(SELECT SUM(lignefacture.prix) as subtotal, lignefacture.factureid FROM lignefacture GROUP BY factureid) as lf 
											ON(lf.factureid = facture.facturid)
										WHERE valide IN(1,5)
										GROUP BY referentid) as f
							USING(referentid);";
$referent_facture_req = $DB_site2->query($referent_facture_query);
while($referent_facture = $DB_site2->fetch_array($referent_facture_req)) {
	$DB_site2->query("UPDATE referents SET nb_commande = '".$referent_facture['nb_fact']."', montant_commande = '".$referent_facture['total']."' WHERE referentid = '".$referent_facture['referentid']."';");
}



$order = "somme DESC";
$sorder = $_GET['sens'] == 1 ? 'ASC' : 'DESC';
switch($_GET['orderby']) {
	case 10 :
		$order = "motcle $sorder";
	break;
	case 1 :
		$order = "referent $sorder";
	break;
	case 2 :
		$order = "somme $sorder";
	break;
	case 3 :
		$order = "position $sorder";
	break;
	case 4 :
		$order = "nbcom $sorder";
	break;
	case 5 :
		$order = "mtcom $sorder";
	break;
	case 6 :
		$order = "nbcom/somme $sorder";
	break;
}



$ajd = date('Y-m-d') ;
$hier = date('Y-m-d', time() - 3600 * 24);
$mois = date('Y-m') ;
$i=0;
echo "
<h3>Mots clé</h3>
Cliquez sur le mot clé pour plus de détails
<table width=\"95%\" align=\"center\" style=\"border-width:1px; border-style:solid; border-color:#990000;\">
	<tr>
		<td class=\"td_head_promos\"><strong><a href=\"referents.php?orderby=10&sens=".($_GET['sens'] == 1 ? 2 : 1)."\">Mots</a></strong></td> 
		<td class=\"td_head_promos\"><strong><a href=\"referents.php?orderby=2&sens=".($_GET['sens'] == 1 ? 2 : 1)."\">Clics</a></strong></td> 
		<td class=\"td_head_promos\"><strong><a href=\"referents.php?orderby=3&sens=".($_GET['sens'] == 1 ? 2 : 1)."\">Position</a></strong></td> 
		<td class=\"td_head_promos\"><strong><a href=\"referents.php?orderby=4&sens=".($_GET['sens'] == 1 ? 2 : 1)."\">Nbr. Com.</a></strong></td> 
		<td class=\"td_head_promos\"><strong><a href=\"referents.php?orderby=5&sens=".($_GET['sens'] == 1 ? 2 : 1)."\">Valeur Com.</a></strong></td> 
		<td class=\"td_head_promos\"><strong><a href=\"referents.php?orderby=6&sens=".($_GET['sens'] == 1 ? 2 : 1)."\">Taux de Conv.</a></strong></td> 
	</tr>
	<tr>
		<td colspan=\"6\" align=\"center\" bgcolor=\"#FFCC99\"><font class=\"titre_refs\">Aujourd'hui</font></td>
	</tr>
	" ;
$topsliens=$DB_site2->query("SELECT * FROM (SELECT *, sum(hit) as somme, SUM(nb_commande) as nbcom, SUM(montant_commande) as mtcom from referents where motcle != '' and dateline = '$ajd' GROUP BY motcle ORDER BY somme DESC limit 20) as sel ORDER BY $order") ;
while ($toplien=$DB_site2->fetch_array($topsliens))
	{		
	if ($toplien[position] == 0)
		{
		$toplien[position] = "Inconnue" ;
		}
	$rowalt = "td_promos_".getrowbg () ;
	echo "<tr>
			<td class=\"$rowalt\" width=\"80%\"><a href=\"referentsword.php?action=view&link=$toplien[motcle]\">$toplien[motcle]</a></td>
			<td class=\"$rowalt\" align=\"center\">$toplien[somme] </td>
			<td class=\"$rowalt\" align=\"center\">$toplien[position]</td>
			<td class=\"$rowalt\" align=\"center\">$toplien[nbcom]</td>
			<td class=\"$rowalt\" align=\"center\" nowrap>$toplien[mtcom] €</td>
			<td class=\"$rowalt\" align=\"center\">".($toplien['somme'] ? number_format($toplien['nbcom']/$toplien['somme']*100, "2", ",", " ") : 0)."%</td>
		</tr>" ;
	$i++;
	}
if($i==0) {
	echo "<tr>
			<td class=\"$rowalt\" colspan=\"6\" align=\"center\">Aucun résultat</td>
		</tr>";
}
$i=0;
echo "<tr>
		<td colspan=\"6\" align=\"center\" bgcolor=\"#FFCC99\"><font class=\"titre_refs\">Hier</font></td>
	</tr>" ;
$topsliens=$DB_site2->query("SELECT * FROM (SELECT *, sum(hit) as somme, SUM(nb_commande) as nbcom, SUM(montant_commande) as mtcom from referents where motcle != '' and dateline = '$hier' GROUP BY motcle ORDER BY somme DESC limit 20) as sel ORDER BY $order") ;
while ($toplien=$DB_site2->fetch_array($topsliens))
	{		
	if ($toplien[position] == 0)
		{
		$toplien[position] = "Inconnue" ;
		}
	$rowalt = "td_promos_".getrowbg () ;
	echo "<tr>
			<td class=\"$rowalt\" width=\"80%\"><a href=\"referentsword.php?action=view&link=$toplien[motcle]\">$toplien[motcle]</a></td>
			<td class=\"$rowalt\" align=\"center\">$toplien[somme] </td>
			<td class=\"$rowalt\" align=\"center\">$toplien[position]</td>
			<td class=\"$rowalt\" align=\"center\">$toplien[nbcom]</td>
			<td class=\"$rowalt\" align=\"center\" nowrap>$toplien[mtcom] €</td>
			<td class=\"$rowalt\" align=\"center\">".($toplien['somme'] ? number_format($toplien['nbcom']/$toplien['somme']*100, "2", ",", " ") : 0)."%</td>
		</tr>" ;
	$i++;
	}
if($i==0) {
	echo "<tr>
			<td class=\"$rowalt\" colspan=\"6\" align=\"center\">Aucun résultat</td>
		</tr>";
}
$i=0;
echo "<tr>
		<td colspan=\"6\" align=\"center\" bgcolor=\"#FFCC99\"><font class=\"titre_refs\">Ce mois</font></td>
	</tr>" ;
$topsliens=$DB_site2->query("SELECT * FROM (SELECT * , sum(hit) as somme, SUM(nb_commande) as nbcom, SUM(montant_commande) as mtcom from referents where motcle != '' and dateline LIKE '$mois%' group by motcle ORDER BY somme DESC limit 20) as sel ORDER BY $order") ;
while ($toplien=$DB_site2->fetch_array($topsliens))
	{		
	if ($toplien[position] == 0)
		{
		$toplien[position] = "Inconnue" ;
		}
	$rowalt = "td_promos_".getrowbg () ;
	echo "<tr>
			<td class=\"$rowalt\" width=\"80%\"><a href=\"referentsword.php?action=view&link=$toplien[motcle]\">$toplien[motcle]</a></td>
			<td class=\"$rowalt\" align=\"center\">$toplien[somme] </td>
			<td class=\"$rowalt\" align=\"center\">$toplien[position]</td>
			<td class=\"$rowalt\" align=\"center\">$toplien[nbcom]</td>
			<td class=\"$rowalt\" align=\"center\" nowrap>$toplien[mtcom] €</td>
			<td class=\"$rowalt\" align=\"center\">".($toplien['somme'] ? number_format($toplien['nbcom']/$toplien['somme']*100, "2", ",", " ") : 0)."%</td>
		</tr>" ;
	$i++;
	}
if($i==0) {
	echo "<tr>
			<td class=\"$rowalt\" colspan=\"6\" align=\"center\">Aucun résultat</td>
		</tr>";
}
$i=0;
echo "</table>
<br>
<h3>Moteurs de recherche</h3>    <div align=\"right\"><a href=\"referentstri.php\">Voir les mots clés par moteurs</a></div>
Cliquez sur le moteur pour plus de détails
<table width=\"95%\" align=\"center\" style=\"border-width:1px; border-style:solid; border-color:#990000;\">
	<tr>
		<td class=\"td_head_promos\"><strong><a href=\"referents.php?orderby=1&sens=".($_GET['sens'] == 1 ? 2 : 1)."\">Nom</a></strong></td> 
		<td class=\"td_head_promos\"><strong><a href=\"referents.php?orderby=2&sens=".($_GET['sens'] == 1 ? 2 : 1)."\">Clics</a></strong></td> 
		<td class=\"td_head_promos\"><strong><a href=\"referents.php?orderby=4&sens=".($_GET['sens'] == 1 ? 2 : 1)."\">Nbr. Com.</a></strong></td> 
		<td class=\"td_head_promos\"><strong><a href=\"referents.php?orderby=5&sens=".($_GET['sens'] == 1 ? 2 : 1)."\">Valeur Com.</a></strong></td> 
		<td class=\"td_head_promos\"><strong><a href=\"referents.php?orderby=6&sens=".($_GET['sens'] == 1 ? 2 : 1)."\">Taux de Conv.</a></strong></td> 
	</tr>
	<tr>
		<td colspan=\"5\" align=\"center\" bgcolor=\"#FFCC99\"><font class=\"titre_refs\">Aujourd'hui</font></td> 
	</tr>" ;
$topsliens=$DB_site2->query("SELECT * FROM (SELECT * , sum(hit) as somme, SUM(nb_commande) as nbcom, SUM(montant_commande) as mtcom from referents where motcle != '' and dateline = '$ajd' group by (referent) ORDER BY somme DESC limit 20) as sel ORDER BY $order") ;
while ($toplien=$DB_site2->fetch_array($topsliens))
	{		
	$rowalt = "td_promos_".getrowbg () ;
	echo "<tr>
			<td class=\"$rowalt\" width=\"80%\"><a href=\"referentstri.php?action=change&moteur=$toplien[referent]\">$toplien[referent]</a></td>
			<td class=\"$rowalt\" align=\"center\">$toplien[somme] </td>
			<td class=\"$rowalt\" align=\"center\">$toplien[nbcom]</td>
			<td class=\"$rowalt\" align=\"center\" nowrap>$toplien[mtcom] €</td>
			<td class=\"$rowalt\" align=\"center\">".($toplien['somme'] ? number_format($toplien['nbcom']/$toplien['somme']*100, "2", ",", " ") : 0)."%</td>
		</tr>" ;
	$i++;
	}
if($i==0) {
	echo "<tr>
			<td class=\"$rowalt\" colspan=\"5\" align=\"center\">Aucun résultat</td>
		</tr>";
}
$i=0;
echo "<tr>
		<td colspan=\"5\" align=\"center\" bgcolor=\"#FFCC99\"><font class=\"titre_refs\">Hier</font></td> 
	</tr>" ;
$topsliens=$DB_site2->query("SELECT * FROM (SELECT * , sum(hit) as somme, SUM(nb_commande) as nbcom, SUM(montant_commande) as mtcom from referents where motcle != '' and dateline = '$hier' group by (referent) ORDER BY somme DESC limit 20) as sel ORDER BY $order") ;
while ($toplien=$DB_site2->fetch_array($topsliens))
	{		
	$rowalt = "td_promos_".getrowbg () ;
	echo "<tr>
			<td class=\"$rowalt\" width=\"80%\"><a href=\"referentstri.php?action=change&moteur=$toplien[referent]\">$toplien[referent]</a></td>
			<td class=\"$rowalt\" align=\"center\">$toplien[somme] </td>
			<td class=\"$rowalt\" align=\"center\">$toplien[nbcom]</td>
			<td class=\"$rowalt\" align=\"center\" nowrap>$toplien[mtcom] €</td>
			<td class=\"$rowalt\" align=\"center\">".($toplien['somme'] ? number_format($toplien['nbcom']/$toplien['somme']*100, "2", ",", " ") : 0)."%</td>
		</tr>" ;
	$i++;
	}
if($i==0) {
	echo "<tr>
			<td class=\"$rowalt\" colspan=\"5\" align=\"center\">Aucun résultat</td>
		</tr>";
}
$i=0;
echo "<tr>
		<td colspan=\"5\" align=\"center\" bgcolor=\"#FFCC99\"><font class=\"titre_refs\">Ce mois</font></td> 
	</tr>" ;
$topsliens=$DB_site2->query("SELECT * FROM (SELECT * , sum(hit) as somme, SUM(nb_commande) as nbcom, SUM(montant_commande) as mtcom from referents where motcle != '' and dateline LIKE '$mois%' group by (referent) ORDER BY somme DESC limit 20) as sel ORDER BY $order") ;
while ($toplien=$DB_site2->fetch_array($topsliens))
	{		
	$rowalt = "td_promos_".getrowbg () ;
	echo "<tr>
			<td class=\"$rowalt\" width=\"80%\"><a href=\"referentstri.php?action=change&moteur=$toplien[referent]\">$toplien[referent]</a></td>
			<td class=\"$rowalt\" align=\"center\">$toplien[somme] </td>
			<td class=\"$rowalt\" align=\"center\">$toplien[nbcom]</td>
			<td class=\"$rowalt\" align=\"center\" nowrap>$toplien[mtcom] €</td>
			<td class=\"$rowalt\" align=\"center\">".($toplien['somme'] ? number_format($toplien['nbcom']/$toplien['somme']*100, "2", ",", " ") : 0)."%</td>
		</tr>" ;
	$i++;
	}
if($i==0) {
	echo "<tr>
			<td class=\"$rowalt\" colspan=\"5\" align=\"center\">Aucun résultat</td>
		</tr>";
}
$i=0;
echo "</table>
<br>
<h3>Liens entrants</h3>
Cliquez sur le lien pour plus de détails
<table width=\"95%\" align=\"center\" style=\"border-width:1px; border-style:solid; border-color:#990000;\">
	<tr>
		<td class=\"td_head_promos\"><strong><a href=\"referents.php?orderby=1&sens=".($_GET['sens'] == 1 ? 2 : 1)."\">Nom</a></strong></td> 
		<td class=\"td_head_promos\"><strong><a href=\"referents.php?orderby=2&sens=".($_GET['sens'] == 1 ? 2 : 1)."\">Clics</a></strong></td> 
		<td class=\"td_head_promos\"><strong><a href=\"referents.php?orderby=4&sens=".($_GET['sens'] == 1 ? 2 : 1)."\">Nbr. Com.</a></strong></td> 
		<td class=\"td_head_promos\"><strong><a href=\"referents.php?orderby=5&sens=".($_GET['sens'] == 1 ? 2 : 1)."\">Valeur Com.</a></strong></td> 
		<td class=\"td_head_promos\"><strong><a href=\"referents.php?orderby=6&sens=".($_GET['sens'] == 1 ? 2 : 1)."\">Taux de Conv.</a></strong></td> 
	</tr>
	<tr>
		<td colspan=\"5\" align=\"center\" bgcolor=\"#FFCC99\"><font class=\"titre_refs\">Aujourd'hui</font></td>
	</tr>
" ;
$topsliens=$DB_site2->query("SELECT * FROM (SELECT *, sum(hit) as somme, SUM(nb_commande) as nbcom, SUM(montant_commande) as mtcom from referents where motcle = '' and dateline = '$ajd' GROUP BY referent ORDER BY somme DESC limit 20) as sel ORDER BY $order") ;
while ($toplien=$DB_site2->fetch_array($topsliens))
	{		
	if ($toplien[position] == 0)
		{
		$toplien[position] = "Inconnue" ;
		}
	$rowalt = "td_promos_".getrowbg () ;
	echo "<tr>
			<td class=\"$rowalt\" width=\"80%\"><a href=\"referentslink.php?action=view&link=$toplien[referent]\">$toplien[referent]</a></td>
			<td class=\"$rowalt\" align=\"center\">$toplien[somme] </td>
			<td class=\"$rowalt\" align=\"center\">$toplien[nbcom]</td>
			<td class=\"$rowalt\" align=\"center\" nowrap>$toplien[mtcom] €</td>
			<td class=\"$rowalt\" align=\"center\">".($toplien['somme'] ? number_format($toplien['nbcom']/$toplien['somme']*100, "2", ",", " ") : 0)."%</td>
		</tr>" ;
	$i++;
	}
if($i==0) {
	echo "<tr>
			<td class=\"$rowalt\" colspan=\"5\" align=\"center\">Aucun résultat</td>
		</tr>";
}
$i=0;
echo "<tr>
		<td colspan=\"5\" align=\"center\" bgcolor=\"#FFCC99\"><font class=\"titre_refs\">Hier</font></td> 
	</tr>" ;
$topsliens=$DB_site2->query("SELECT * FROM (SELECT * , sum(hit) as somme, SUM(nb_commande) as nbcom, SUM(montant_commande) as mtcom from referents where motcle = '' and dateline = '$hier' GROUP BY referent ORDER BY somme DESC limit 20) as sel ORDER BY $order") ;
while ($toplien=$DB_site2->fetch_array($topsliens))
	{		
	if ($toplien[position] == 0)
		{
		$toplien[position] = "Inconnue" ;
		}
	$rowalt = "td_promos_".getrowbg () ;
	echo "<tr>
			<td class=\"$rowalt\" width=\"80%\"><a href=\"referentslink.php?action=view&link=$toplien[referent]\">$toplien[referent]</a></td>
			<td class=\"$rowalt\" align=\"center\">$toplien[somme] </td>
			<td class=\"$rowalt\" align=\"center\">$toplien[nbcom]</td>
			<td class=\"$rowalt\" align=\"center\" nowrap>$toplien[mtcom] €</td>
			<td class=\"$rowalt\" align=\"center\">".($toplien['somme'] ? number_format($toplien['nbcom']/$toplien['somme']*100, "2", ",", " ") : 0)."%</td>
		</tr>" ;
	$i++;
	}
if($i==0) {
	echo "<tr>
			<td class=\"$rowalt\" colspan=\"5\" align=\"center\">Aucun résultat</td>
		</tr>";
}
$i=0;
echo "<tr>
		<td colspan=\"5\" align=\"center\" bgcolor=\"#FFCC99\"><font class=\"titre_refs\">Ce mois</font></td> 
	</tr>" ;
$topsliens=$DB_site2->query("SELECT * FROM (SELECT * , sum(hit) as somme, SUM(nb_commande) as nbcom, SUM(montant_commande) as mtcom from referents where motcle = '' and dateline LIKE '$mois%' group by referent ORDER BY somme DESC limit 20) as sel ORDER BY $order") ;
while ($toplien=$DB_site2->fetch_array($topsliens))
	{		
	if ($toplien[position] == 0)
		{
		$toplien[position] = "Inconnue" ;
		}
	$rowalt = "td_promos_".getrowbg () ;
	echo "<tr>
			<td class=\"$rowalt\" width=\"80%\"><a href=\"referentslink.php?action=view&link=$toplien[referent]\">$toplien[referent]</a></td>
			<td class=\"$rowalt\" align=\"center\">$toplien[somme] </td>
			<td class=\"$rowalt\" align=\"center\">$toplien[nbcom]</td>
			<td class=\"$rowalt\" align=\"center\" nowrap>$toplien[mtcom] €</td>
			<td class=\"$rowalt\" align=\"center\">".($toplien['somme'] ? number_format($toplien['nbcom']/$toplien['somme']*100, "2", ",", " ") : 0)."%</td>
		</tr>" ;
	$i++;
	}
if($i==0) {
	echo "<tr>
			<td class=\"$rowalt\" colspan=\"5\" align=\"center\">Aucun résultat</td>
		</tr>";
}
$i=0;
echo "</table>
" ;



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