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
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Gestion des promos</font>
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


if( !in_array( "5", $modules ) )
	echo "<script language=\"javascript\">location.href=\"index.php\";</script>";

echo "<br>";
////////////////////////////////////////////////////////////////////////////////////////////////////////
// MONTER D'UNE POSITION
////////////////////////////////////////////////////////////////////////////////////////////////////////
if( isset( $action ) && $action == "up" )
{
	$i_position = 1;
	$position = $DB_site2->query_first( "SELECT position FROM promo
										 WHERE promoid = '$promoid'" );						
	$nouvelle_position_page = $position[0]-1;
	$position2 = $DB_site2->query_first( "SELECT promoid FROM promo
									  	  WHERE position = '$nouvelle_position_page'" );
	$rq_modifier_position = "UPDATE promo SET position='$nouvelle_position_page' 
							 WHERE promoid = '$promoid'";
	$DB_site2->query( $rq_modifier_position );
	$rq_modifier_position2 = "UPDATE promo SET position='$position[0]' 
							  WHERE promoid = '$position2[promoid]'";
	$DB_site2->query( $rq_modifier_position2 );

	$action = "";	
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// DESCENDRE D'UNE POSITION
////////////////////////////////////////////////////////////////////////////////////////////////////////
if( isset( $action ) && $action == "down" )
{
	$i_position = 1;
	$position = $DB_site2->query_first( "SELECT position FROM promo
										 WHERE promoid = '$promoid'" );						
	$nouvelle_position_page = $position[0]+1;
	$position2 = $DB_site2->query_first( "SELECT promoid FROM promo
									  	  WHERE position = '$nouvelle_position_page'" );
	$rq_modifier_position = "UPDATE promo SET position='$nouvelle_position_page' 
							 WHERE promoid = '$promoid'";
	$DB_site2->query( $rq_modifier_position );
	$rq_modifier_position2 = "UPDATE promo SET position='$position[0]' 
							  WHERE promoid = '$position2[promoid]'";
	$DB_site2->query( $rq_modifier_position2 );

	$action = "";	
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// AFFICHER LES promos
////////////////////////////////////////////////////////////////////////////////////////////////////////
if( !isset( $action ) || $action == "" )
{
	echo "<h3>Promos du moment</h3>
	<table class=\"table_coupscoeur\">";
	$i_promo = 0; // Compte le nombre de promos
	$rq_promo = $DB_site2->query( "SELECT p.promoid, p.position, a.$libelle, a.artcode  FROM promo AS p
								 INNER JOIN article AS a ON (p.artid=a.artid) ORDER BY position" );
	$nb_promo = $DB_site2->num_rows( $rq_promo );
	$max_promo = $DB_site2->query_first( "SELECT nb_art_toparticles FROM parametresgeneraux" );
	$max_promo = $max_promo[0];
	if( $nb_promo > $max_promo )
	{
		$diff_promo = $nb_promo - $max_promo;
	}
	
	while( $rs_promo = $DB_site2->fetch_array( $rq_promo ) ) 
	{
		$rowalt = "td_".getrowbg();		

		$i_promo++;

		if( $nb_promo > 1 )
		{
			if( $i_promo == 1 ) // Première page
			{
				$images_position = "<img src=\"images/vide.gif\" height=\"7\">
									<a href=\"alerte_promo.php?action=down&promoid=$rs_promo[promoid]\"><img src=\"images/descendre.gif\" alt=\"Descendre\" height=\"7\" border=\"0\"></a>";
			}
			elseif( $i_promo == $nb_promo ) // Dernière page
			{
				$images_position = "<a href=\"alerte_promo.php?action=up&promoid=$rs_promo[promoid]\"><img src=\"images/monter.gif\" alt=\"Monter\" height=\"7\" border=\"0\"></a>
									<img src=\"images/vide.gif\" height=\"7\">";
			}
			else
			{
				$images_position = "<a href=\"alerte_promo.php?action=up&promoid=$rs_promo[promoid]\"><img src=\"images/monter.gif\" alt=\"Monter\" height=\"7\" border=\"0\"></a>
									<a href=\"alerte_promo.php?action=down&promoid=$rs_promo[promoid]\"><img src=\"images/descendre.gif\" alt=\"Descendre\" height=\"7\" border=\"0\"></a>";
			}
		}
		echo "<tr>";
		if( $i_promo <= $max_promo ) {
			echo "	<td class=\"td_coupscoeur1\">$images_position $rs_promo[artcode]</td>
					<td class=\"td_coupscoeur2\">";
		}
		else
		{	// On a dépassé le nombre d'articles affichés dans le Top Articles
			echo "	<td class=\"td_coupscoeur1\">$images_position $rs_promo[artcode]</td>
					<td class=\"td_coupscoeur2\">";
		}
		echo "	  <a href=\"alerte_promo.php?action=supprimer&promoid=$rs_promo[promoid]\" target=\"_self\">Supprimer</a>
				</td>
			  </tr>";
	}
	
	if( $i_promo == 0 )
		echo "<tr><td class=\"td_coupscoeur2\"><br>Vous n'avez choisi aucune promo</td></tr>";

	echo "</table>";
	
	echo "<br><br><a href=\"index.php\" target=\"_self\">Retour</a>";
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// SUPPRIMER LES promos (action=supprimer)
////////////////////////////////////////////////////////////////////////////////////////////////////////
elseif( $action == "supprimer" )
{
	// Confirmation de suppression
	if( isset($promoid) && $promoid != "" )
	{	
		echo "êtes vous sûr de vouloir supprimer cette promo ?<br>
			  <a href=\"alerte_promo.php?action=supprimer2&promoid=$promoid\">Oui</a>
			  | <a href=\"alerte_promo.php\">Non</a>";
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// SUPPRIMER LES promos (action=supprimer2)
////////////////////////////////////////////////////////////////////////////////////////////////////////
elseif( $action == "supprimer2" )
{	// Désactiver une promo

	// On décalle toutes les positions des coupdecoeur suivants
	$position_promo = $DB_site2->query_first( "SELECT position FROM promo
											  WHERE promoid = $promoid" );
	$position_promo = $position_promo[0];

	$rq_positions_suivantes = $DB_site2->query( "SELECT promoid, position FROM promo
												 WHERE position > $position_promo" );

	while( $rs_positions_suivantes = $DB_site2->fetch_array( $rq_positions_suivantes ) ) 
	{
		$position_temp = $rs_positions_suivantes[position] - 1;
		$rq_modifier_position = "UPDATE promo SET position = '$position_temp' 
								 WHERE promoid = '$rs_positions_suivantes[promoid]'";
		$DB_site2->query( $rq_modifier_position );
	}
	
	// Suppression
	$DB_site2->query( "DELETE FROM promo WHERE promoid='$promoid'" );
	
	echo "<script language=\"javascript\">location.href=\"alerte_promo.php\";</script>";
}

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