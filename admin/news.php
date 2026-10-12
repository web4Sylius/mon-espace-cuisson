<?php

require "admin_global.php";
setcookie("hostname",$host,time()+3600,"/");
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
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Mes actualités</font>
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
				<td align=\"center\" valign=\"middle\" width=\"100%\">
" ;

if( !in_array( "14", $modules ) )
	echo "<script language=\"javascript\">location.href=\"index.php\";</script>";

////////////////////////////////////////////////////////////////////////////////////////////////////////
// AFFICHER LES NEWS
////////////////////////////////////////////////////////////////////////////////////////////////////////
if( !isset( $action ) || $action == "" )
{
	// Effacement de la table temporaire
	$wysiwyg_suppr = $DB_site2->query( "DELETE FROM wysiwyg" );

	// Affichage des news
	$i_news = 0; // Compte le nombre de news
	
	$rq_news = $DB_site2->query( "select * from news order by datenews DESC" );
	echo "<h3>Actualités</h3>
		<table class=\"table_modules_boutiques\">";
	
	while( $rs_news = $DB_site2->fetch_array( $rq_news ) ) 
	{
		$rowalt = "td_".getrowbg();

		echo "<tr>";
		echo "  <td class=\"$rowalt\" align=\"center\" width=\"20%\">" .date( "d/m/Y", $rs_news[datenews] ). "</td>";
		echo "  <td class=\"$rowalt\">$rs_news[$libelle]</td>";
		echo "  <td class=\"$rowalt\" align=\"center\" width=\"20%\">
				  <a href=\"news.php?action=editer&newsid=$rs_news[newsid]&i_langue=0\" target=\"_self\">Editer</a>
				  | <a href=\"news.php?action=supprimer&newsid=$rs_news[newsid]\" target=\"_self\">Supprimer</a>
				</td>";
		echo "</tr>";

		$i_news++;
	}
	if( $i_news == 0 )
		echo "<tr><td align=\"center\">Il n'existe aucune actualité</td></tr>";
	
	echo "</table>";
	echo "<div align=\"center\"><br><a href=\"news.php?action=ajouter\" target=\"_self\">Ajouter une actualité</a></div>";
} 

////////////////////////////////////////////////////////////////////////////////////////////////////////
// EDITER UNE NEWS (action=editer)
////////////////////////////////////////////////////////////////////////////////////////////////////////
elseif( $action == "editer" )
{
	if( !isset( $i_langue ) || $i_langue == "" )
		$i_langue = 0;

	// On vérifie que l'index de la langue passée en paramêtre soit valide
	if( isset( $newsid ) && $i_langue >= 0 && $i_langue < count($langues) )
	{
		// Table temporaire
		$wysiwyg_temp = $DB_site2->query_first( "SELECT * FROM wysiwyg" );
		if( !isset( $wysiwyg_temp[wysiwygid] ) )
		{
			$rs_news = $DB_site2->query_first( "select * from news where newsid = $newsid" );		

			// Copie de la news en cours de modification dans la table temporaire
			$rq_ajout_wysiwyg = "INSERT INTO wysiwyg ( wysiwygid, libelleFR, contenuFR,
																  libelleDE, contenuDE,
																  libelleSP, contenuSP,
																  libelleEN, contenuEN,
																  libelleIT, contenuIT )
								 VALUES ( '1', '" .addslashes( $rs_news[libelleFR] ). "', '"
								 				  .addslashes( $rs_news[contenuFR] ). "', '"
								  			      .addslashes( $rs_news[libelleDE] ). "', '"
												  .addslashes( $rs_news[contenuDE] ). "', '"
								  			      .addslashes( $rs_news[libelleSP] ). "', '"
												  .addslashes( $rs_news[contenuSP] ). "', '"
								  			      .addslashes( $rs_news[libelleEN] ). "', '"
												  .addslashes( $rs_news[contenuEN] ). "', '"
								  			      .addslashes( $rs_news[libelleIT] ). "', '"
												  .addslashes( $rs_news[contenuIT] ). "' )";					   
			$DB_site2->query($rq_ajout_wysiwyg);
			$wysiwyg_temp = $DB_site2->query_first( "SELECT * FROM wysiwyg" );
		}

		//$rootpath = "/home/arobases/www/vendre-en-ligne.fr/";
		include($rootpath."fckeditor/fckeditor.php") ;	

		echo "<div align=\"center\"><b>Modifier une news</b></div>";
		
		echo "<form method=\"post\" action=\"news.php\" name=\"form_news\">
				<table align=\"center\" width=\"500\">";

		// Copie des titre/contenu de la page postée dans la table wysiwyg
		if( isset( $titre ) || isset( $contenu ) )
		{
			$rq_modifier_wysiwyg = "UPDATE wysiwyg SET libelle$langues[$i_langue] = '$titre',
													contenu$langues[$i_langue] = '$content'";
			$DB_site2->query( $rq_modifier_wysiwyg );	
			$wysiwyg_temp = $DB_site2->query_first( "SELECT * FROM wysiwyg" );
		}
	
		// Langue précédente
		if( isset($_POST['btn_prec']) && $_POST['btn_prec']=='Langue précédente' 
		 || isset($_POST['btn_suiv']) && $_POST['btn_suiv']=='Langue suivante' )
		{
			if( isset($_POST['btn_prec']) && $_POST['btn_prec']=='Langue précédente' )
				$i_langue--;
			else // Langue suivante
				$i_langue++;

			$titre = $wysiwyg_temp["libelle$langues[$i_langue]"];
			$content = $wysiwyg_temp["contenu$langues[$i_langue]"];
		}


		// Validation des modifications
		elseif( isset($_POST['btn_valid']) && $_POST['btn_valid']=='Valider les modifications' )
		{
			$rq_modifier_news = "UPDATE news
					 SET libelleFR='" .addslashes( $wysiwyg_temp[libelleFR] ). "', 
						 contenuFR='" .addslashes( $wysiwyg_temp[contenuFR] ). "', 
						 libelleDE='" .addslashes( $wysiwyg_temp[libelleDE] ). "', 
						 contenuDE='" .addslashes( $wysiwyg_temp[contenuDE] ). "', 
					 	 libelleSP='" .addslashes( $wysiwyg_temp[libelleSP] ). "', 
						 contenuSP='" .addslashes( $wysiwyg_temp[contenuSP] ). "', 
						 libelleEN='" .addslashes( $wysiwyg_temp[libelleEN] ). "', 
						 contenuEN='" .addslashes( $wysiwyg_temp[contenuEN] ). "', 
					 	 libelleIT='" .addslashes( $wysiwyg_temp[libelleIT] ). "', 
						 contenuIT='" .addslashes( $wysiwyg_temp[contenuIT] ). "' 
					 WHERE newsid = $newsid";
			$DB_site2->query($rq_modifier_news);
			// Effacement de la table temporaire
			$wysiwyg_suppr = $DB_site2->query( "DELETE FROM wysiwyg" );
	
			echo "<script language=\"javascript\">location.href=\"news.php\";</script>";

		// Si on affiche la première langue de la news à modifier
		} else
		{
			// Effacement de la table temporaire
			$wysiwyg_suppr = $DB_site2->query( "DELETE FROM wysiwyg" );

			$titre = $wysiwyg_temp["libelle$langues[$i_langue]"];
			$content = $wysiwyg_temp["contenu$langues[$i_langue]"];
		}
		
		echo "<tr><td><b>LANGUE : $langues[$i_langue]</b></td></tr>
			  <tr><td>TITRE : <input type=\"text\" size=\"40\" name=\"titre\" value=\"$titre\"></td></tr>
			  <tr><td><br>CONTENU :<br>" ;
		$contenu = "<table align=\"center\" width=\"100%\"><tr><td><br>" ;
		$oFCKeditor = new FCKeditor('content') ;
		$oFCKeditor->BasePath = '/fckeditor/' ;
		$oFCKeditor->Value		= $content ;
		$oFCKeditor->Height	= 400 ;
		$oFCKeditor->Width	= 585 ;
		$contenu .= $oFCKeditor->Create() ;
		$contenu .= "</td></tr></table>";
		
		echo "$contenu</td></tr>";
		

		// Si on est à la dernière langue de la liste des langues on valide les modifications
		if( $i_langue == count($langues)-1 ) {
			echo "<tr>";
			// On affiche le bouton "Langue précédente" si on a + d'une langue
			if( $i_langue > 0 ) {
				echo "<td align=\"center\"><input type=\"submit\" name=\"btn_prec\" value=\"Langue précédente\">";
				echo "					   <input type=\"submit\" name=\"btn_valid\" value=\"Valider les modifications\"></td>";
			}
			else
				echo "<td align=\"center\"><input type=\"submit\" name=\"btn_valid\"value=\"Valider les modifications\"></td>";
			echo "</tr>";
		// Sinon on passe à la langue suivante ou précédente
		} else {
			echo "<tr>";
			if( $i_langue > 0 ) {
				echo "<td align=\"center\"><input type=\"submit\" name=\"btn_prec\" value=\"Langue précédente\">";
				echo "					   <input type=\"submit\" name=\"btn_suiv\" value=\"Langue suivante\"></td>";
			}
			else
				echo "<td align=\"center\"><input type=\"submit\" name=\"btn_suiv\" value=\"Langue suivante\"></td>";
			echo "</tr>";
		}
		
		// Champs "hidden" 
		echo "<input type=\"hidden\" name=\"action\" value=\"editer\">";
		echo "<input type=\"hidden\" name=\"newsid\" value=\"$newsid\">";
		echo "<input type=\"hidden\" name=\"i_langue\" value=\"$i_langue\">";

		echo "</table></form>";
		
		echo "<div align=\"center\"><a href=\"news.php\" target=\"_self\">Annuler</a></div>";
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// AJOUTER UNE NEWS (action=ajouter)
////////////////////////////////////////////////////////////////////////////////////////////////////////	
elseif( $action == "ajouter" )
{
	if( !isset( $i_langue ) || $i_langue == "" )
		$i_langue = 0;

	// On vérifie que l'index de la langue passée en paramêtre soit valide
	if( $i_langue >= 0 && $i_langue < count($langues) )
	{
		// Table temporaire
		$wysiwyg_temp = $DB_site2->query_first( "SELECT * FROM wysiwyg" );
		if( !isset( $wysiwyg_temp[wysiwygid] ) )
		{
			// Ajout d'un enregistrement dans la table temporaire s'il n'existe pas
			$rq_ajout_wysiwyg = "INSERT INTO wysiwyg ( wysiwygid ) VALUES ( '1' )";
			$DB_site2->query($rq_ajout_wysiwyg);
			$wysiwyg_temp = $DB_site2->query_first( "SELECT * FROM wysiwyg" );
		}
		
		//$rootpath = "/home/arobases/www/vendre-en-ligne.fr/";
		include($rootpath."fckeditor/fckeditor.php") ;	
		
		echo "<div align=\"center\"><b>Ajouter une news</b></div>";
		
		echo "<form method=\"post\" action=\"news.php\" name=\"form_news\">
				<table align=\"center\" width=\"500\">";

		// Copie des titre/contenu de la page postée dans la table wysiwyg
		if( isset( $titre ) || isset( $contenu ) )
		{
			$rq_modifier_wysiwyg = "UPDATE wysiwyg SET libelle$langues[$i_langue] = '$titre',
													contenu$langues[$i_langue] = '$content'";
			$DB_site2->query( $rq_modifier_wysiwyg );
			$wysiwyg_temp = $DB_site2->query_first( "SELECT * FROM wysiwyg" );
		}
		
		// Langue précédente
		if( isset($_POST['btn_prec']) && $_POST['btn_prec']=='Langue précédente' 
		 || isset($_POST['btn_suiv']) && $_POST['btn_suiv']=='Langue suivante' )
		{
			if( isset($_POST['btn_prec']) && $_POST['btn_prec']=='Langue précédente' )
				$i_langue--;
			else // Langue suivante
				$i_langue++;

			$titre = $wysiwyg_temp["libelle$langues[$i_langue]"];
			$content = $wysiwyg_temp["contenu$langues[$i_langue]"];
		}
		
		// Ajout de la news
		elseif( isset($_POST['btn_valid']) && $_POST['btn_valid']=='Ajouter la news' )
		{
			$rq_ajouter_news = "INSERT INTO news ( datenews, libelleFR, contenuFR,
															 libelleDE, contenuDE,
															 libelleSP, contenuSP,
															 libelleEN, contenuEN,
															 libelleIT, contenuIT )	
				VALUES ( '" .time(). "', '"
						 .addslashes( $wysiwyg_temp[libelleFR] ). "', '" .addslashes( $wysiwyg_temp[contenuFR] ). "', '"
						 .addslashes( $wysiwyg_temp[libelleDE] ). "', '" .addslashes( $wysiwyg_temp[contenuDE] ). "', '"
						 .addslashes( $wysiwyg_temp[libelleSP] ). "', '" .addslashes( $wysiwyg_temp[contenuSP] ). "', '"
						 .addslashes( $wysiwyg_temp[libelleEN] ). "', '" .addslashes( $wysiwyg_temp[contenuEN] ). "', '"
						 .addslashes( $wysiwyg_temp[libelleIT] ). "', '" .addslashes( $wysiwyg_temp[contenuIT] ). "' )";

			$DB_site2->query($rq_ajouter_news);
			// Effacement de la table temporaire
			$wysiwyg_suppr = $DB_site2->query( "DELETE FROM wysiwyg" );
			
			echo "<script language=\"javascript\">location.href=\"news.php\";</script>";
		}
		else
		{
			// Première fois qu'on charge la page :
			// Effacement de la table temporaire
			$wysiwyg_suppr = $DB_site2->query( "DELETE FROM wysiwyg" );
		}
		

		echo "<tr><td><b>LANGUE : $langues[$i_langue]</b></td></tr>
			  <tr><td>TITRE : <input type=\"text\" size=\"40\" name=\"titre\" value=\"$titre\"></td></tr>
			  <tr><td><br>CONTENU :<br>" ;
		$contenu = "<table align=\"center\" width=\"100%\"><tr><td><br>" ;
		$oFCKeditor = new FCKeditor('content') ;
		$oFCKeditor->BasePath = '/fckeditor/' ;
		$oFCKeditor->Value		= $content ;
		$oFCKeditor->Height	= 400 ;
		$oFCKeditor->Width	= 585 ;
		$contenu .= $oFCKeditor->Create() ;
		$contenu .= "</td></tr></table>";

		echo "$contenu</td></tr>";

		// Si on est à la dernière langue de la liste des langues on affiche le bouton "Valider"
		if( $i_langue == count($langues)-1 ) {
			echo "<tr>";
			// On affiche le bouton "Langue précédente" si on a + d'une langue
			if( $i_langue > 0 ) {
				echo "<td align=\"center\"><input type=\"submit\" name=\"btn_prec\" value=\"Langue précédente\">";
				echo "					   <input type=\"submit\" name=\"btn_valid\" value=\"Ajouter la news\"></td>";
			}
			else
				echo "<td align=\"center\"><input type=\"submit\" name=\"btn_valid\"value=\"Ajouter la news\"></td>";
			echo "</tr>";
		// Sinon on passe à la langue suivante ou précédente
		} else {
			echo "<tr>";
			if( $i_langue > 0 ) {
				echo "<td align=\"center\"><input type=\"submit\" name=\"btn_prec\" value=\"Langue précédente\">";
				echo "					   <input type=\"submit\" name=\"btn_suiv\" value=\"Langue suivante\"></td>";
			}
			else
				echo "<td align=\"center\"><input type=\"submit\" name=\"btn_suiv\" value=\"Langue suivante\"></td>";
			echo "</tr>";
		}
		
		// Champs "hidden" 
		echo "<input type=\"hidden\" name=\"action\" value=\"ajouter\">";
		echo "<input type=\"hidden\" name=\"i_langue\" value=\"$i_langue\">";

		echo "</table></form>";
		
		echo "<div align=\"center\"><a href=\"news.php\" target=\"_self\">Annuler</a></div>";
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// SUPPRIMER UNE NEWS (action=supprimer)
////////////////////////////////////////////////////////////////////////////////////////////////////////	
elseif( $action == "supprimer" )
{
	// Confirmation de suppression
	echo "êtes vous sûr de vouloir supprimer cette news ?<br>
		  <a href=\"news.php?action=supprimer2&newsid=$newsid\">Oui</a>
		  | <a href=\"news.php\">Non</a>";
}
////////////////////////////////////////////////////////////////////////////////////////////////////////
// SUPPRIMER UNE NEWS (action=supprimer2)
////////////////////////////////////////////////////////////////////////////////////////////////////////	
elseif( $action == "supprimer2" )
{
	$rq_supprimer = "delete from news where newsid = $newsid";
	$DB_site2->query( $rq_supprimer );

	echo "<script language=\"javascript\">location.href=\"news.php\";</script>";
}

echo "<br><bR>
<div align=\"left\" style=\"margin-left:20px;\">Prévoyez un minimum de texte contenant des expressions stratégiques à votre référencement.<bR>
Il faut adapter votre texte en fonction des expression sur lesquelles vous souhaitez positionner votre boutique dans les moteurs de recherche.<br>
<strong>Offrez un contenu texte suffisant et faites des mises à jour régulières.</strong></div>
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
        </tr>";


include "./footer.php";
?>