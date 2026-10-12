<?php
require "admin_global.php";
include "./header.php";
include "./menu.php";
?>
<html>
  <head>
<?php
if( isset( $catid ) && $catid != "" )
   echo "<title>Affecter des remises à un rayon</title>";
else
   echo "<title>Affecter des remises à un article</title>";
?>
  </head>
  <body background="/images/template/fond.gif">
<link rel="stylesheet" href="/arobases.css">
<?

if( !in_array( "3", $modules ) )
	echo "<script language=\"javascript\">location.href=\"index.php\";</script>";

/////////////////////////////////////////////////////////////////////////////////////////////////
// REMISES SUR UNE CATEGORIE
/////////////////////////////////////////////////////////////////////////////////////////////////
if( isset( $catid ) && $catid != "" )
{
	echo "<div align=\"center\"><b>Ajouter une remise au rayon</b></div>";

	if( isset( $action ) && $action == "ajouter" )
	{
		// Si le formulaire a été posté
		foreach( $_POST as $cle=>$valeur )
		{		
			if( substr( $cle, 0, 4 ) == "chk_" )
			{
				// On ajoute les remises dans la table remisecategorie
				$DB_site2->query( "INSERT INTO remisecategorie ( catid, remiseid ) 
								   VALUES ( '$catid', '$valeur' )" );
			}
		}
		
		// Fermeture de la page et refresh de la page parente
		echo "<script language=\"javascript\">
				function valider() {
					window.opener.formu2.submit();
					window.close();
				}
				valider();
			  </script>";
					//window.opener.formu.saverefresh.value='1';
	}			

	else
	{
		echo "<form name=\"form_remise\" method=\"post\" action=\"ajouter_remise.php\">";
		echo "	<table class=\"table_modules_boutiques\">
				  <tr><td colspan=\"4\" class=\"td_modules_boutiques\"><b>Remises :</b></td></tr>
				  <tr><td class=\"td_modules_boutiques\" width=\"25%\">Type de remise</td>
					  <td class=\"td_modules_boutiques\" width=\"25%\" align=\"right\">Valeur (poids,prix ou qté)</td>
					  <td class=\"td_modules_boutiques\" width=\"25%\" align=\"right\">Pourcentage de remise</td>
					  <td class=\"td_modules_boutiques\" width=\"25%\" align=\"center\">Ajouter</td>
				  </tr>";
				$remises = $DB_site2->query( "select * from remise order by typeremise, valeur" );
				
				$i=0;
				while ( $remise = $DB_site2->fetch_array( $remises ) )
				{
					// On teste si la remise n'est pas déja affectée à cette catégorie
					$rq_remises_categorie = $DB_site2->query( "select * from remisecategorie
																where catid = '$catid'" );
					$i_remises_categorie = 0;
					
					while( $rs_remise_categorie = $DB_site2->fetch_array( $rq_remises_categorie ) )
					{
						if( $remise["remiseid"] == $rs_remise_categorie["remiseid"] )
							$i_remises_categorie++;
					}
					
					if( $i_remises_categorie == 0 )
					{
						$rowalt = "td_".getrowbg();
		
						echo "<tr>";	
						if( $remise["typeremise"] == '0' ) {
							echo "    <td class=\"$rowalt\">sur le poids</td>";
							$unite_valeur = "grammes";
						} elseif( $remise["typeremise"] == '1' ) {
							echo "    <td class=\"$rowalt\">sur le prix</td>";
							$unite_valeur = "€";
						} else { // $remise["typeremise"] == '2'
							echo "    <td class=\"$rowalt\">sur la quantité</td>";
							$unite_valeur = "articles";
						}
						echo " 	  <td class=\"$rowalt\" align=\"right\">$remise[valeur] $unite_valeur</td>";
						echo "    <td class=\"$rowalt\" align=\"right\">$remise[pctremise] %</td>";
		
						echo "  <td class=\"$rowalt\" align=\"center\">
								<input type=\"checkbox\" name=\"chk_remise$remise[remiseid]\" value=\"$remise[remiseid]\"> 
								</td>";
						$i++;
					}
				}
				if ( $i == 0 )
					echo "<tr><td colspan=\"5\"><font face=\"verdana\" size=\"2\"><b>Il n'y a aucune remise</b></font></td></tr>";
	
		echo "	</table><br>
				<input type=\"hidden\" name=\"action\" value=\"ajouter\">
				<input type=\"hidden\" name=\"catid\" value=\"$catid\">
				<div align=\"center\"><input type=\"submit\" name=\"btn_valider\" value=\"Valider\"></div>
				</form>";
	}
}

/////////////////////////////////////////////////////////////////////////////////////////////////
// REMISES SUR UN ARTICLE
/////////////////////////////////////////////////////////////////////////////////////////////////
elseif( isset( $artid ) && $artid != "" )
{
	echo "<div align=\"center\"><b>Ajouter une remise à l'article</b></div>";

	if( isset( $action ) && $action == "ajouter" )
	{
		// Si le formulaire a été posté
		foreach( $_POST as $cle=>$valeur )
		{		
			if( substr( $cle, 0, 4 ) == "chk_" )
			{
				// On ajoute les remises dans la table remisearticle
				$DB_site2->query( "INSERT INTO remisearticle ( artid, remiseid ) 
								   VALUES ( '$artid', '$valeur' )" );
			}
		}
		
		// Fermeture de la page et refresh de la page parente
		echo "<script language=\"javascript\">
				function valider() {
					window.opener.formu.saverefresh.value='1';
					window.opener.formu.submit();
					window.close();
				}
				valider();
			  </script>";
	}
	else
	{
		echo "<form name=\"form_remise\" method=\"post\" action=\"ajouter_remise.php\">";
		echo "	<table class=\"table_modules_boutiques\">
				  <tr><td colspan=\"4\" class=\"td_modules_boutiques\"><b>Remises :</b></td></tr>
				  <tr><td class=\"td_modules_boutiques\" width=\"25%\">Type de remise</td>
					  <td class=\"td_modules_boutiques\" width=\"25%\" align=\"right\">Valeur (poids,prix ou qté)</td>
					  <td class=\"td_modules_boutiques\" width=\"25%\" align=\"right\">Pourcentage de remise</td>
					  <td class=\"td_modules_boutiques\" width=\"25%\" align=\"center\">Ajouter</td>
				  </tr>";
		$remises = $DB_site2->query( "select * from remise order by typeremise, valeur" );
		$i=0;
		while ( $remise = $DB_site2->fetch_array( $remises ) )
		{
			// On teste si la remise n'est pas déja affectée à cette catégorie
			$rq_remises_article = $DB_site2->query( "select * from remisearticle
														where artid = '$artid'" );
			$i_remises_article = 0;
			
			while( $rs_remise_article = $DB_site2->fetch_array( $rq_remises_article ) )
			{
				if( $remise["remiseid"] == $rs_remise_article["remiseid"] )
					$i_remises_article++;
			}
			
			if( $i_remises_article == 0 )
			{
				$rowalt = "td_".getrowbg();

				echo "<tr>";	
				if( $remise["typeremise"] == '0' ) {
					echo "    <td class=\"$rowalt\">sur le poids</td>";
					$unite_valeur = "grammes";
				} elseif( $remise["typeremise"] == '1' ) {
					echo "    <td class=\"$rowalt\">sur le prix</td>";
					$unite_valeur = "€";
				} else { // $remise["typeremise"] == '2'
					echo "    <td class=\"$rowalt\">sur la quantité</td>";
					$unite_valeur = "articles";
				}
				echo "    <td class=\"$rowalt\" align=\"right\">$remise[valeur] $unite_valeur</td>";
				echo "    <td class=\"$rowalt\" align=\"right\">$remise[pctremise] %</td>";

				echo "  <td class=\"$rowalt\" align=\"center\">
						<input type=\"checkbox\" name=\"chk_remise$remise[remiseid]\" value=\"$remise[remiseid]\"> 
						</td>";
				$i++;
			}
		}
		if ( $i == 0 )
			echo "<tr><td colspan=\"5\"><font face=\"verdana\" size=\"2\"><b>Il n'y a aucune remise</b></font></td></tr>";
	
		echo "	</table><br>
				<input type=\"hidden\" name=\"action\" value=\"ajouter\">
				<input type=\"hidden\" name=\"artid\" value=\"$artid\">
				<div align=\"center\"><input type=\"submit\" name=\"btn_valider\" value=\"Valider\"></div>
				</form>";
	}
}
include "./footer.php";

?>