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
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Gestion des coups de coeur</font>
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


if( !in_array( "21", $modules ) )
	echo "<script language=\"javascript\">location.href=\"index.php\";</script>";

echo "<br>";
////////////////////////////////////////////////////////////////////////////////////////////////////////
// MONTER D'UNE POSITION
////////////////////////////////////////////////////////////////////////////////////////////////////////
if( isset( $action ) && $action == "up" )
{
	$i_position = 1;
	$position = $DB_site2->query_first( "SELECT position FROM coupdecoeur
										 WHERE coupdecoeurid = '$coupdecoeurid'" );						
	$nouvelle_position_page = $position[0]-1;
	$position2 = $DB_site2->query_first( "SELECT coupdecoeurid FROM coupdecoeur
									  	  WHERE position = '$nouvelle_position_page'" );
	$rq_modifier_position = "UPDATE coupdecoeur SET position='$nouvelle_position_page' 
							 WHERE coupdecoeurid = '$coupdecoeurid'";
	$DB_site2->query( $rq_modifier_position );
	$rq_modifier_position2 = "UPDATE coupdecoeur SET position='$position[0]' 
							  WHERE coupdecoeurid = '$position2[coupdecoeurid]'";
	$DB_site2->query( $rq_modifier_position2 );

	$action = "";	
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// DESCENDRE D'UNE POSITION
////////////////////////////////////////////////////////////////////////////////////////////////////////
if( isset( $action ) && $action == "down" )
{
	$i_position = 1;
	$position = $DB_site2->query_first( "SELECT position FROM coupdecoeur
										 WHERE coupdecoeurid = '$coupdecoeurid'" );						
	$nouvelle_position_page = $position[0]+1;
	$position2 = $DB_site2->query_first( "SELECT coupdecoeurid FROM coupdecoeur
									  	  WHERE position = '$nouvelle_position_page'" );
	$rq_modifier_position = "UPDATE coupdecoeur SET position='$nouvelle_position_page' 
							 WHERE coupdecoeurid = '$coupdecoeurid'";
	$DB_site2->query( $rq_modifier_position );
	$rq_modifier_position2 = "UPDATE coupdecoeur SET position='$position[0]' 
							  WHERE coupdecoeurid = '$position2[coupdecoeurid]'";
	$DB_site2->query( $rq_modifier_position2 );

	$action = "";	
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// AFFICHER LES COUPS DE COEUR
////////////////////////////////////////////////////////////////////////////////////////////////////////
if( !isset( $action ) || $action == "" )
{
	echo "<h3>Coups de coeur du moment</h3>
	<table class=\"table_coupscoeur\">";
	$i_cdc = 0; // Compte le nombre de coups de coeur
	$rq_cdc = $DB_site2->query( "SELECT cdc.coupdecoeurid, cdc.position, a.$libelle, a.artcode  FROM coupdecoeur AS cdc
								 INNER JOIN article AS a ON (cdc.artid=a.artid) ORDER BY position" );
	$nb_cdc = $DB_site2->num_rows( $rq_cdc );
	$max_cdc = $DB_site2->query_first( "SELECT nb_art_toparticles FROM parametresgeneraux" );
	$max_cdc = $max_cdc[0];
	if( $nb_cdc > $max_cdc )
	{
		$diff_cdc = $nb_cdc - $max_cdc;
	}
	
	while( $rs_cdc = $DB_site2->fetch_array( $rq_cdc ) ) 
	{
		$rowalt = "td_".getrowbg();		

		$i_cdc++;

		if( $nb_cdc > 1 )
		{
			if( $i_cdc == 1 ) // Première page
			{
				$images_position = "<img src=\"images/vide.gif\" height=\"7\">
									<a href=\"alerte_coupdecoeur.php?action=down&coupdecoeurid=$rs_cdc[coupdecoeurid]\"><img src=\"images/descendre.gif\" alt=\"Descendre\" height=\"7\" border=\"0\"></a>";
			}
			elseif( $i_cdc == $nb_cdc ) // Dernière page
			{
				$images_position = "<a href=\"alerte_coupdecoeur.php?action=up&coupdecoeurid=$rs_cdc[coupdecoeurid]\"><img src=\"images/monter.gif\" alt=\"Monter\" height=\"7\" border=\"0\"></a>
									<img src=\"images/vide.gif\" height=\"7\">";
			}
			else
			{
				$images_position = "<a href=\"alerte_coupdecoeur.php?action=up&coupdecoeurid=$rs_cdc[coupdecoeurid]\"><img src=\"images/monter.gif\" alt=\"Monter\" height=\"7\" border=\"0\"></a>
									<a href=\"alerte_coupdecoeur.php?action=down&coupdecoeurid=$rs_cdc[coupdecoeurid]\"><img src=\"images/descendre.gif\" alt=\"Descendre\" height=\"7\" border=\"0\"></a>";
			}
		}
		
		
		echo "<tr>";
		if( $i_cdc <= $max_cdc ) {
			echo "	<td class=\"td_coupscoeur1\">$images_position $rs_cdc[artcode]</td>
					<td class=\"td_coupscoeur2\">";
		}
		else
		{	// On a dépassé le nombre d'articles affichés dans le Top Articles
			echo "	<td class=\"td_coupscoeur1\">$images_position $rs_cdc[artcode]</td>
					<td class=\"td_coupscoeur2\">";
		}
		echo "	  <a href=\"alerte_coupdecoeur.php?action=supprimer&coupdecoeurid=$rs_cdc[coupdecoeurid]\" target=\"_self\">Supprimer</a>
				</td>
			  </tr>";
	}
	
	if( $i_cdc == 0 )
		echo "<tr><td class=\"td_coupscoeur2\"><br>Vous n'avez choisi aucun coup de coeur</td></tr>";

	echo "</table>";
	
	echo "<br><br><a href=\"index.php\" target=\"_self\">Retour</a>";
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// SUPPRIMER LES COUPS DE COEUR (action=supprimer)
////////////////////////////////////////////////////////////////////////////////////////////////////////
elseif( $action == "supprimer" )
{
	// Confirmation de suppression
	if( isset($coupdecoeurid) && $coupdecoeurid != "" )
	{	
		echo "êtes vous sûr de vouloir supprimer ce coup de coeur ?<br>
			  <a href=\"alerte_coupdecoeur.php?action=supprimer2&coupdecoeurid=$coupdecoeurid\">Oui</a>
			  | <a href=\"alerte_coupdecoeur.php\">Non</a>";
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// SUPPRIMER LES COUPS DE COEUR (action=supprimer2)
////////////////////////////////////////////////////////////////////////////////////////////////////////
elseif( $action == "supprimer2" )
{	// Désactiver un coup de coeur

	// On décalle toutes les positions des coupdecoeur suivants
	$position_cdc = $DB_site2->query_first( "SELECT position FROM coupdecoeur
											  WHERE coupdecoeurid = $coupdecoeurid" );
	$position_cdc = $position_cdc[0];

	$rq_positions_suivantes = $DB_site2->query( "SELECT coupdecoeurid, position FROM coupdecoeur
												 WHERE position > $position_cdc" );

	while( $rs_positions_suivantes = $DB_site2->fetch_array( $rq_positions_suivantes ) ) 
	{
		$position_temp = $rs_positions_suivantes[position] - 1;
		$rq_modifier_position = "UPDATE coupdecoeur SET position = '$position_temp' 
								 WHERE coupdecoeurid = '$rs_positions_suivantes[coupdecoeurid]'";
		$DB_site2->query( $rq_modifier_position );
	}
	
	// Suppression
	$DB_site2->query( "DELETE FROM coupdecoeur WHERE coupdecoeurid='$coupdecoeurid'" );
	
	echo "<script language=\"javascript\">location.href=\"alerte_coupdecoeur.php\";</script>";
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