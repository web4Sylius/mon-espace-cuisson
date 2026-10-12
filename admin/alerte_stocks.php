<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";
include "./fonctions.php";

echo "
<td valign=\"top\">
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
   	<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
		<tr>
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Gestion des stocks</font>
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



if ($catid=="" || !isset($catid))
	$parentid="0";
else
	$parentid=$catid;

$i=0;

$tabcategorie=array();

//*******************************************************************
//*****************AFFICHAGE DES CATEGORIES**************************
//*******************************************************************
$recherche=str_replace(" ","%",$recherche);


$requete="select count(*) from article where $libelle like '%$recherche%' or artcode like '%$recherche%' order by $libelle";
$nb_article=$DB_site2->query_first($requete);
if ($nb_article[0]>0)
	{
	$requete="select a.*, c.$libelle as catlibelle, sk.seuil, sk.nombre from article as a 
			inner join categorie as c on (a.catid=c.catid) inner join stock as sk on (a.artid=sk.artid) 
			where sk.nombre<=sk.seuil or sk.seuil='' or sk.seuil=0 or sk.nombre='' order by c.$libelle, a.$libelle";
	$articles=$DB_site2->query($requete);
	echo "<h3>Liste des prodtuis en-dessous du seuil d'alerte</h3>
		<table class=\"table_promos\">
			  <tr>
			  	<td class=\"td_head_promos\">Rayon</td>
				<td class=\"td_head_promos\">Produit</td>
				<td class=\"td_head_promos\">Seuil</td>
				<td class=\"td_head_promos\">Stocks</td>
				<td class=\"td_head_promos\">Action</td>
			</tr>";
	while ($article=$DB_site2->fetch_array($articles)) 
		{
		$rowalt="td_".getrowbg();
		echo "<tr>
				<td class=\"td_promos\">$article[catlibelle]</td>
				<td class=\"td_promos\">$article[$libelle] </td>
				<td class=\"td_promos\">$article[seuil]</td>
				<td class=\"td_promos\">$article[nombre]</td>
				<td class=\"td_promos\"><a href=\"http://$domaine_info[domaine]/art,$article[artid],".url_rewrite($article[$libelle]).".htm\" target=\"_blank\">Voir</a> | <a href=\"./ajout_produit.php?artid=$article[artid]\">Editer</a> | <a href=\"./supprimer_produit.php?artid=$article[artid]\">Supprimer</a></td>
			</tr>";
		}
	echo "</table><br>";
	}
else
	{
	echo "<h3>Liste des produits trouvés</h3>
		<table class=\"table_modules_boutiques\">
				<tr>
					<td class=\"td_promos\"><b>Aucun produit ne correspond à ces critères de recherche</b></td>
				</tr>
			</table>";
	}
	echo "<br><br><a href=\"index.php\" target=\"_self\">Retour</a>
		</td>
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