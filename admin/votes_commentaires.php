<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";

if( !in_array( "18", $modules ) )
	echo "<script language=\"javascript\">location.href=\"index.php\";</script>";
	
$article = $DB_site2->query_first( "select catid, $libelle, prix from article where artid = '$artid'" );

////////////////////////////////////////////////////////////////////////////////////////////////////////
// AFFICHAGE DES COMMENTAIRES
////////////////////////////////////////////////////////////////////////////////////////////////////////
if( !isset($action) || $action == "" )
{
	$rq_votes_commentaires = $DB_site2->query( "select * from articlevote where artid = '$artid'
												ORDER BY datevote" );

	echo "<table class=\"table_modules_boutiques\"><tr><td colspan=\"3\"><b>LISTE DES COMMENTAIRES SUR L'ARTICLE : $article[$libelle]</b></td></tr>";

	echo "<tr><td align=\"center\">Date</td>
			  <td align=\"center\">Titre</td>
			  <td align=\"right\">Note</td>
			  <td></td>";
	echo "</tr>";

	if( $DB_site2->num_rows( $rq_votes_commentaires ) > 0 )
	{
		while( $rs_votes_commentaires = $DB_site2->fetch_array( $rq_votes_commentaires ) )
		{
			list($annee, $mois, $jour) = explode( "-", $rs_votes_commentaires[datevote] );
			$rowalt = "td_".getrowbg();
			
			echo "<tr>";
			echo "  <td class=\"$rowalt\" align=\"center\">$jour/$mois/$annee</td>";
			echo "  <td class=\"$rowalt\" align=\"center\">$rs_votes_commentaires[titre]</td>";
			echo "  <td class=\"$rowalt\" align=\"right\">$rs_votes_commentaires[note]";
			echo "  <td class=\"$rowalt\" align=\"center\">
					  <a href=\"votes_commentaires.php?action=editer&artid=$artid&commid=$rs_votes_commentaires[articlevoteid]\" target=\"_self\">Editer</a>
					  | <a href=\"votes_commentaires.php?action=supprimer&artid=$artid&commid=$rs_votes_commentaires[articlevoteid]\" target=\"_self\">Supprimer</a>
					</td>";
			echo "</tr>";	
		}
	}
	else
		echo "<tr><td><br>Aucun commentaire sur cet article</td></tr>";

	echo "</table>";
	echo "<div align=\"center\"><br><a href=\"catalogue.php?catid=$article[catid]\" target=\"_self\">Retour à la liste des articles</a></div>";

////////////////////////////////////////////////////////////////////////////////////////////////////////
// EDITER UN COMMENTAIRE (action=editer)
////////////////////////////////////////////////////////////////////////////////////////////////////////	
} elseif( $action == "editer" )
{
	echo "<div align=\"center\"><b>ARTICLE : $article[$libelle]</b></div>";
	if( isset( $_POST['btn_editer'] ) && $_POST['btn_editer'] == 'Modifier le commentaire' )
	{
		// Si le commentaire a été modifié
		$erreur_saisie = 0;
		if( !isset($txt_titre) || $txt_titre == "" ) {
			echo "<div align=center><b>Veuillez saisir un titre</b></div>";
			$erreur_saisie = 1;
		}
		if( !isset($txt_note) || $txt_note == "" ) {
			echo "<div align=center><b>Veuillez saisir une note</b></div>";
			$erreur_saisie = 1;
		} elseif( !is_numeric($txt_note) ) {
			echo "<div align=center><b>Veuillez saisir une valeur numérique pour la note</b></div>";
			$erreur_saisie = 1;
		} elseif( $txt_note<0 || $txt_note>5 ) {
			echo "<div align=center><b>Veuillez saisir une note entre 0 et 5</b></div>";
			$erreur_saisie = 1;
		}
		if( !isset($txt_commentaire) || $txt_commentaire == "" ) {
			echo "<div align=center><b>Veuillez saisir le contenu du commentaire</b></div>";
			$erreur_saisie = 1;
		}
		
		if( $erreur_saisie == 1 )
		{
			// Réaffichage du formulaire en cas d'erreur
			$rs_vote_commentaire = $DB_site2->query_first( "select * from articlevote where articlevoteid = '$commid'
													  ORDER BY datevote" );
			list($annee, $mois, $jour) = explode( "-", $rs_vote_commentaire[datevote] );

			echo "<form method=\"post\" action=\"votes_commentaires.php\" name=\"formu\">
					<table border=\"0\">
					  <tr><td>Date :</td>
						  <td>$jour/$mois/$annee</td>
					  </tr>
					  <tr><td>Titre :</td>
						  <td><input type = \"text\" name=\"txt_titre\" value=\"$txt_titre\" size=\"30\"></td>
					  </tr>
					  <tr><td>Note :</td>
						  <td><input type = \"text\" name=\"txt_note\" value=\"$txt_note\" size=\"5\"></td>
					  </tr>
					  <tr><td>Contenu :</td>
						  <td><textarea name=\"txt_commentaire\" cols=30 rows=5>$txt_commentaire</textarea></td>
					  </tr>
					</table>
				  <input type = \"hidden\" name = \"action\" value = \"editer\">
				  <input type = \"hidden\" name = \"artid\" value = \"$artid\">
				  <input type = \"hidden\" name = \"commid\" value = \"$commid\">		  
				  <input type = \"submit\" name = \"btn_editer\" value = \"Modifier le commentaire\">
				  </form>";
		}
		else
		{
			// On applique les modifications
			$rq_modifier = "UPDATE articlevote
								 SET titre ='" .htmlentities($txt_titre). "', 
									 note ='" .htmlentities($txt_note). "', 
									 contenu ='" .htmlentities($txt_commentaire). "' 
								 WHERE articlevoteid = $commid";
			$DB_site2->query( $rq_modifier );
			echo "<script language=\"javascript\">location.href=\"votes_commentaires.php?artid=$artid\";</script>";
		}
	}
	else
	{
		// Affichage du commentaire
		$rs_vote_commentaire = $DB_site2->query_first( "select * from articlevote where articlevoteid = '$commid'
												  ORDER BY datevote" );
		list($annee, $mois, $jour) = explode( "-", $rs_vote_commentaire[datevote] );
		
		echo "<form method=\"post\" action=\"votes_commentaires.php\" name=\"formu\">
				<table border=\"0\">
				  <tr><td>Date :</td>
					  <td>$jour/$mois/$annee</td>
				  </tr>
				  <tr><td>Titre :</td>
					  <td><input type = \"text\" name=\"txt_titre\" value=\"$rs_vote_commentaire[titre]\" size=\"30\"></td>
				  </tr>
				  <tr><td>Note :</td>
					  <td><input type = \"text\" name=\"txt_note\" value=\"$rs_vote_commentaire[note]\" size=\"5\"></td>
				  </tr>
				  <tr><td>Contenu :</td>
					  <td><textarea name=\"txt_commentaire\" cols=30 rows=5>$rs_vote_commentaire[contenu]</textarea></td>
				  </tr>
				</table>
			  <input type = \"hidden\" name = \"action\" value = \"editer\">
			  <input type = \"hidden\" name = \"artid\" value = \"$artid\">
			  <input type = \"hidden\" name = \"commid\" value = \"$commid\">		  
			  <input type = \"submit\" name = \"btn_editer\" value = \"Modifier le commentaire\">
			  </form>";
	}
	
////////////////////////////////////////////////////////////////////////////////////////////////////////
// SUPPRIMER UN COMMENTAIRE (action=supprimer)
////////////////////////////////////////////////////////////////////////////////////////////////////////	
} elseif( $action == "supprimer" ) {
	// Confirmation de suppression
	echo "êtes vous sûr de vouloir supprimer cette promotion ?<br>
		  <a href=\"votes_commentaires.php?action=supprimer2&artid=$artid&commid=$commid\">Oui</a>
		  | <a href=\"votes_commentaires.php?artid=$artid\">Non</a>";

////////////////////////////////////////////////////////////////////////////////////////////////////////
// SUPPRIMER UN COMMENTAIRE (action=supprimer2)
////////////////////////////////////////////////////////////////////////////////////////////////////////	
} elseif( $action == "supprimer2" ) {
	$rq_supprimer = "delete from articlevote where articlevoteid = $commid";
	$DB_site2->query( $rq_supprimer );

	echo "<script language=\"javascript\">location.href=\"votes_commentaires.php?artid=$artid\";</script>";
}

include "./footer.php";
?>