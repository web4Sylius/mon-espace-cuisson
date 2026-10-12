<?php
require "admin_global.php";
include "./header.php";
include "./menu.php";
?>
<html>
  <head>
   <title>Affecter des caractéristiques à un rayon</title>
  </head>
  <body background="/images/template/fond.gif">
<link rel="stylesheet" href="/arobases.css">
<?php

echo "<div align=\"center\"><b>Ajouter une caractéristique au rayon</b></div>";

if( isset( $action ) && $action == "ajouter" )
{
	// Si le formulaire a été posté
	foreach( $_POST as $cle=>$valeur )
	{		
		if( substr( $cle, 0, 4 ) == "chk_" )
		{
			// On ajoute les remises dans la table remisecategorie
			$DB_site2->query( "INSERT INTO categorie_caracteristique ( catid, caractid ) 
							   VALUES ( '$catid', '$valeur' )" );
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
	echo "<form name=\"form_caract\" method=\"post\" action=\"ajouter_caracteristique_cat.php\">";
	echo "	<table class=\"table_modules_boutiques\">
			  <tr><td colspan=\"2\" class=\"td_modules_boutiques\"><b>Caractéristiques :</b></td></tr>
			  <tr><td class=\"td_modules_boutiques\" width=\"25%\" align=\"left\">Libellé</td>
				  <td class=\"td_modules_boutiques\" width=\"25%\" align=\"center\">Ajouter</td>
			  </tr>";
	
	$caracteristiques = $DB_site2->query( "SELECT caractid, $libelle FROM caracteristique
										   ORDER BY $libelle" );
	
	if( $DB_site2->num_rows( $caracteristiques ) > 0 )
	{
		while ( $caracteristique = $DB_site2->fetch_array( $caracteristiques ) )
		{
			// On teste si la caractéristique n'est pas déja affectée à cette catégorie
			$rq_caracteristique_categorie = $DB_site2->query( "SELECT * FROM categorie_caracteristique
															   WHERE catid = '$catid'
															   AND caractid = '$caracteristique[caractid]'" );
			if( $DB_site2->num_rows( $rq_caracteristique_categorie ) == 0 )
			{
				$rowalt = "td_".getrowbg();

				echo "<tr><td class=\"$rowalt\" align=\"left\">$caracteristique[$libelle]</td>
						  <td class=\"$rowalt\" align=\"center\">
							<input type=\"checkbox\" name=\"chk_caract$caracteristique[caractid]\" value=\"$caracteristique[caractid]\"> 
						  </td>
					  </tr>";
			}	 
		}
	}
	else
		echo "<tr><td colspan=\"5\"><font face=\"verdana\" size=\"2\"><b>Il n'y a aucune caractéristique</b></font></td></tr>";

	echo "	</table><br>
			<input type=\"hidden\" name=\"action\" value=\"ajouter\">
			<input type=\"hidden\" name=\"catid\" value=\"$catid\">
			<div align=\"center\"><input type=\"submit\" name=\"btn_valider\" value=\"Valider\"></div>
			</form>";
}
include "./footer.php";

?>
