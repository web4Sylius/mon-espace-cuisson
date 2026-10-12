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
   <td align=\"justify\" valign=\"middle\"><br>
   	<font class=\"txt_2\">
		<table align=\"center\">
			<tr>
				<td align=\"center\" valign=\"middle\">" ;

if( !in_array( "5", $modules ) )
	echo "<script language=\"javascript\">location.href=\"index.php\";</script>";

if( $action == "supprimer" )
{
	// Confirmation de suppression
	if( isset($promotionid) && $promotionid != "" )
	{	
		echo "êtes vous sûr de vouloir supprimer la promotion sur cet article ?<br>
			  <a href=\"alerte_promotion.php?action=supprimer2&promotionid=$promotionid\">Oui</a>
			  | <a href=\"alerte_promotion.php\">Non</a>";
	}
}
elseif( $action == "supprimer2" )
{
	if (isset($promotionid) && $promotionid != "")
		{
		$position_page=$DB_site2->query_first("SELECT position FROM promotion WHERE promotionid = '$promotionid'");
		$position_page = $position_page[0];
		$rq_positions_suivantes=$DB_site2->query("SELECT promotionid, position FROM promotion WHERE position > $position_page" );
		while ($rs_positions_suivantes=$DB_site2->fetch_array($rq_positions_suivantes)) 
			{
			$position_temp = $rs_positions_suivantes[position] - 1;
			$DB_site2->query("UPDATE promotion SET position = '$position_temp' WHERE promotionid = '$rs_positions_suivantes[promotionid]'");
			}
		$DB_site2->query("DELETE from promotion WHERE promotionid = '$promotionid'");
		}
	echo "<script language=\"javascript\">location.href=\"alerte_promotion.php\";</script>";
}
else
{
	function tabulation_categorie( $rang )
	{
		$tabulation = "";
		
		if( $rang == 0 )
		{
			$tabulation = "<img src=\"images/point.gif\" height=\"14\"> ";
		}
		else
		{
			for ( $i=0; $i<$rang-1; $i++ )
			{
				$tabulation = $tabulation."<img src=\"images/trait.gif\" height=\"14\"> ";
			}
			$tabulation = $tabulation."<img src=\"images/fleche.gif\" height=\"14\"> <img src=\"images/point.gif\" height=\"14\"> ";
		}
	
		return $tabulation;
	}
	
	function tabulation_article( $rang )
	{
		$tabulation = "";
		if( $rang > 0 )
		{
			for ( $i=0; $i<$rang-1; $i++ )
			{
				$tabulation = $tabulation."<img src=\"images/trait.gif\" height=\"14\"> ";
			}
			$tabulation = $tabulation."<img src=\"images/trait.gif\" height=\"14\"> ";
		}
		return $tabulation;
	}
	
	$rq_categorie = $DB_site2->query( "SELECT * FROM categorie 
										GROUP BY catid
										ORDER BY $libelle" );
	while( $rs_categorie = $DB_site2->fetch_array( $rq_categorie ) )
	{
		$tab_categorie[] = array( $rs_categorie[catid], $rs_categorie[parentid], $rs_categorie[$libelle] );
	}
	
	$nb_categories = $DB_site2->query_first( "select count(*) from categorie" );
	
	function recur_categorie( $tab , $pere, $rang, $nb_elements, $DB_site2, $libelle )
	{
		// Fonction récursive de parcours des catégories
		
		for( $i=0; $i<$nb_elements; $i++ )
		{
			if ( $tab[$i][1] == $pere )
			{
				echo "<tr><td valign=\"middle\">&nbsp".tabulation_categorie($rang)."<b><a href=catalogue.php?catid=".$tab[$i][0].">".$tab[$i][2]."</a></b>				
						  <td></td>	 
					  </tr>";
			
			// Des promotions sur les articles de cette catégorie ?
			
			// Tous les articles de cette catégorie
			$rq_categorie_articles = $DB_site2->query( "SELECT * from article 
														WHERE catid = ".$tab[$i][0]."
														ORDER BY $libelle" );
			while( $rs_categorie_articles = $DB_site2->fetch_array( $rq_categorie_articles ) )
			{
				$rq_promo = $DB_site2->query( "SELECT * FROM promotion 
												WHERE artid = $rs_categorie_articles[artid]
												AND datedebut <= '".date('Y-m-d')."'
											  	AND datefin >= '".date('Y-m-d')."'
												ORDER BY datedebut" );
				if( $DB_site2->num_rows( $rq_promo ) > 0 )
				{
					$rowalt = "td_".getrowbg();
					while( $rs_promo = $DB_site2->fetch_array( $rq_promo ) )
					{
						list($annee_debut, $mois_debut, $jour_debut) = explode( "-", $rs_promo[datedebut] );
						list($annee_fin, $mois_fin, $jour_fin) = explode( "-", $rs_promo[datefin] );

						echo "<tr><td class=\"$rowalt\">&nbsp".tabulation_article($rang)."<img src=\"images/fleche.gif\" height=\"14\"> <a href=\"ajout_produit.php?artid=".$rs_categorie_articles[artid]."&prec=modif\">".$rs_categorie_articles[$libelle]."</a></td>
								  <td class=\"$rowalt\" align=\"center\" nowrap>$jour_debut/$mois_debut/$annee_debut&nbsp;</td>
								  <td class=\"$rowalt\" align=\"center\" nowrap>$jour_fin/$mois_fin/$annee_fin&nbsp;</td>
								  <td class=\"$rowalt\" align=\"center\">$rs_categorie_articles[prix] €</td>
								  <td class=\"$rowalt\" align=\"center\"><b>$rs_promo[prix] €</b></td>
								  <td class=\"$rowalt\" align=\"center\">
									<a href=\"alerte_promotion.php?action=supprimer&promotionid=$rs_promo[promotionid]\" target=\"_self\">Supprimer</a>
								  </td>								
							  </tr>";				
					}
				}
			}
			
			recur_categorie( $tab, $tab[$i][0], $rang+1, $nb_elements, $DB_site2, $libelle );
			}
		}
	}
	
	echo "<h3>Liste des promotions en cours</h3>
	<br>
	<a href=\"positions_promos.php\">Modifier les positions d'affichage</a>
	<br><br>
	<table class=\"table_promos\">
			<tr><td></td>
				<td class=\"td_head_promos\"><b>Début</b></td>
				<td class=\"td_head_promos\"><b>Fin</b></td>
				<td class=\"td_head_promos\"><b>Prix base</b></td>
				<td class=\"td_head_promos\"><b>Prix promo</b></td>
				<td></td>
			</tr>";
	recur_categorie($tab_categorie, 0, 0, $nb_categories[0], $DB_site2, $libelle );
	echo "</table><br>";

}
	echo "<br><br><a href=\"index.php\" target=\"_self\">Retour</a>";
echo "
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