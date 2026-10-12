<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";

if( !in_array( "3", $modules ) )
	echo "<script language=\"javascript\">location.href=\"index.php\";</script>";

if( $action == "supprimer" )
{
	// Confirmation de suppression
	if( isset($artid) && $artid != "" )
	{	
		echo "êtes vous sûr de vouloir supprimer la remise sur cet article ?<br>
			  <a href=\"alerte_remise.php?action=supprimer2&artid=$artid&remiseid=$remiseid\">Oui</a>
			  | <a href=\"alerte_remise.php\">Non</a>";
	}
	elseif( isset($catid) && $catid != "" )
	{	
		echo "êtes vous sûr de vouloir supprimer la remise sur ce rayon ?<br>
			  <a href=\"alerte_remise.php?action=supprimer2&catid=$catid&remiseid=$remiseid\">Oui</a>
			  | <a href=\"alerte_remise.php\">Non</a>";
	}
}
elseif( $action == "supprimer2" )
{
	if( isset($artid) && $artid != "" )
		$rq_supprimer = "DELETE from remisearticle WHERE artid=$artid AND remiseid=$remiseid";
	elseif( isset($catid) && $catid != "" )
		$rq_supprimer = "DELETE from remisecategorie WHERE catid=$catid AND remiseid=$remiseid";

	$DB_site2->query( $rq_supprimer );
	
	echo "<script language=\"javascript\">location.href=\"alerte_remise.php\";</script>";
}
else
{
	
	function tabulation_categorie( $rang, $premiere_img )
	{
		$tabulation = "";
		
		if( $rang == 0 )
		{
			if( $premiere_img == 1 )
				$tabulation = "<img src=\"images/point.gif\" height=\"14\"> ";
			else
				$tabulation = "<img src=\"images/trait.gif\" height=\"14\"> ";
		}
		else
		{
			for ( $i=0; $i<$rang-1; $i++ )
			{
				$tabulation = $tabulation."<img src=\"images/trait.gif\" height=\"14\"> ";
			}
			if( $premiere_img == 1 )
				$tabulation = $tabulation."<img src=\"images/fleche.gif\" height=\"14\"> <img src=\"images/point.gif\" height=\"14\"> ";
			else
				$tabulation = $tabulation."<img src=\"images/trait.gif\" height=\"14\"> ";
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
				// Remises sur la catégorie ?	
				$rq_remise = $DB_site2->query( "SELECT * FROM remisecategorie 
												INNER JOIN remise on (remisecategorie.remiseid=remise.remiseid)
												WHERE catid = ".$tab[$i][0]."
												ORDER BY typeremise, valeur" );
				
				if( $DB_site2->num_rows( $rq_remise ) > 0 )
				{
					$i_image = 0;
					$rowalt = "td_".getrowbg();
					while( $rs_remise = $DB_site2->fetch_array( $rq_remise ) )
					{
						if( $rs_remise[typeremise] == 0 ) {
							$type_remise = "sur le poids :";
							$unite_valeur = "grammes";
						} elseif( $rs_remise[typeremise] == 1 ) {
							$type_remise = "sur le prix :";
							$unite_valeur = "€";
						} else {
							$type_remise = "sur la quantité :";
							$unite_valeur = "articles";
						}
	
						// Si c'est la premiere remise de cette catégorie
						if( $i_image == 0 )
						{
							$tmp_categorie = tabulation_categorie($rang, 1)."<b><a href=modifier_categorie.php?&catid=".$tab[$i][0].">".$tab[$i][2]."</a></b>";
							$i_image = 1;
						}
						else
						{
							// Sinon on ne réaffiche pas le nom de la catégorie
							$tmp_categorie = tabulation_categorie($rang, 0);
						}
						
						echo "<tr><td class=\"$rowalt\" valign=\"middle\">&nbsp".$tmp_categorie."</td>
								  <td class=\"$rowalt\">
									<table border=\"0\" align=center cellspacing=\"0\">
									  <tr> 
										<td width=100>$type_remise</td>
										<td width=100>$rs_remise[valeur] $unite_valeur</td>
										<td width=30>$rs_remise[pctremise] %</td>
									  </tr>
									</table>
								  </td>
								  <td class=\"$rowalt\" align=\"center\">
								    <a href=\"alerte_remise.php?action=supprimer&catid=".$tab[$i][0]."&remiseid=$rs_remise[remiseid]\" target=\"_self\">Supprimer</a>
								  </td>								
							  </tr>";
					}
				}
				else
				{
					// Sinon cette catégorie n'a pas de remise mais elle peut avoir des sous-catégories
					// ou des articles de sous-catégories qui en ont
					
					echo "<tr><td valign=\"middle\">&nbsp".tabulation_categorie($rang, 1)."<b><a href=modifier_categorie.php?&catid=".$tab[$i][0].">".$tab[$i][2]."</a></b>				
							  <td></td>
						  </tr>";
				}
					
				
				// Des remises sur les articles de cette catégorie ?
				
				// Tous les articles de cette catégorie
				$rq_categorie_articles = $DB_site2->query( "SELECT * from article 
															WHERE catid = ".$tab[$i][0]."
															ORDER BY $libelle" );
				while( $rs_categorie_articles = $DB_site2->fetch_array( $rq_categorie_articles ) )
				{
				
					$rq_remise = $DB_site2->query( "SELECT * FROM remise 
													INNER JOIN remisearticle on (remisearticle.remiseid=remise.remiseid)
													WHERE artid = $rs_categorie_articles[artid]
													ORDER BY typeremise, valeur" );
					if( $DB_site2->num_rows( $rq_remise ) > 0 )
					{
						 $i_image = 0;
						 $rowalt = "td_".getrowbg();
						while( $rs_remise = $DB_site2->fetch_array( $rq_remise ) )
						{
							if( $rs_remise[typeremise] == 0 ) {
								$type_remise = "sur le poids :";
								$unite_valeur = "grammes";
							} elseif( $rs_remise[typeremise] == 1 ) {
								$type_remise = "sur le prix :";
								$unite_valeur = "€";
							} else {
								$type_remise = "sur la quantité :";
								$unite_valeur = "articles";
							}
							
							// Si c'est la premiere remise de cet article
							if( $i_image == 0 )
							{
								$tmp_article = tabulation_article($rang)."<img src=\"images/fleche.gif\" height=\"14\"> <a href=ajout_produit.php?artid=$rs_categorie_articles[artid]>".$rs_categorie_articles[$libelle]."</a>";
								$i_image = 1;
							}
							else
							{
								// Sinon on ne réaffiche pas le nom de l'article
								$tmp_article = tabulation_article($rang)."<img src=\"images/trait.gif\" height=\"14\">";
							}
	
							echo "<tr><td class=\"$rowalt\">&nbsp".$tmp_article."</td>
									  <td class=\"$rowalt\">
										<table border=\"0\" align=center cellspacing=\"0\">
										  <tr> 
											<td width=100>$type_remise</td>
											<td width=100>$rs_remise[valeur] $unite_valeur</td>
											<td width=30>$rs_remise[pctremise] %</td>
										  </tr>
										</table>
									  </td>
									  <td class=\"$rowalt\" align=\"center\">
										<a href=\"alerte_remise.php?action=supprimer&artid=$rs_remise[artid]&remiseid=$rs_remise[remiseid]\" target=\"_self\">Supprimer</a>
									  </td>								
								  </tr>";						
						}
					}
				}
				
				recur_categorie( $tab, $tab[$i][0], $rang+1, $nb_elements, $DB_site2, $libelle );
			}
		}
	}
	
	echo "<table class=\"table_modules_boutiques\" cellpadding=\"0\" cellspacing=\"0\">
			<tr><td colspan=\"2\"><b>&nbspLISTE DES REMISES</b></td></tr>
			<tr><td align=center><b></b></td>
				<td align=center><b>Remise</B></td>
			</tr>";
	
	recur_categorie( $tab_categorie, 0, 0, $nb_categories[0], $DB_site2, $libelle );
	
	echo "</table><br>";
	
}
include "./footer.php";
?>