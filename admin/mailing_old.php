<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";


echo "<h1>Création d'une Mailing List</h1>";



$contenue="";
$perpage="20";

if( $action =="send")
{
	$mailing = $DB_site->query_first("SELECT * from mailing ORDER BY mailingid DESC LIMIT 1 ");
	$title=stripslashes($mailing[title]);
	$content=$mailing[content];
	$content=str_replace(chr(10),"<br>", $content);
	$debut=$page*$perpage;
	$page++;
	if ($zone=="all") {
		$wherezone="";
	} else {
		$wherezone="and dbprefixe='$zone' ";
	}
	$results = $DB_site->query("SELECT mail FROM  utilisateur WHERE allowemails='1' $wherezone ORDER BY userid LIMIT $debut , $perpage ");
	$isempty=$DB_site->num_rows();
	if ($isempty=="0") {
		echo "<center><font color=\"red\">mailing réalisé avec succes</font></center>";
		exit;
	}
	$contenue.="<table>";
	while ($result = $DB_site->fetch_array($results) )
	{
		$mailhead = "From: ";
		$mailhead .= "webmaster@2night.fr";
		$mailhead .= "\n";
		$mailhead .= "Content-Type: text/html; charset=iso-8859-1\n";
		$htmlmess = "<HTML><HEAD></HEAD><BODY>";
		$htmlmess .= stripslashes($content);
		$htmlmess .= "<p align=center>Message envoyé par <a href=http://www.lyon2night.com>Le groupe 2Night</a><br></p>PS: Vous pouvez choisir de ne plus recevoir d'e-mails de ce type la en cliquant <a href=http://www.lyon2night.com/editoptions.php>ICI </a> où en vous rendant sur la categorie Mes options , puis en décochant la case \"Recevoir les mails faisant promotion des soirées organisées par l'intermediaire de 2Night\"<br></body></html>";
//		@mail( $result[0] , $title , $htmlmess , $mailhead );
//		@mail( "bm@arobases.fr" , $title , $htmlmess , $mailhead );
		$contenue.="<tr><td><font size=1><b>$result[0]</b>$page</font></tr><td>";
	}
	$contenue.="<script language=\"javascript\">\n window.location=\"admin_mailing3.php?&action=send&page=$page&zone=$zone\";\n </script>";
//	$contenue.="<a href=\"admin_mailing3.php?&action=send&page=$page&zone=$zone\">suite</a>";
	$contenue.="</table>";
}

if( $action == "save" )
{
	if (!isset($content) or !isset($title) or $content=="" or $title=="") {
		$msg="<center><font color=\"red\">Remplissez les cases sujet et message avant d'envoyer votre message...</font></center>";
		$action=="";
	} else {
		$results = $DB_site->query("INSERT INTO mailing (title , content , dateline , userid) VALUES ('".addslashes($title)."' , '".addslashes($content)."' , '".time()."' , '$user_info[userid]' ) ");
		$contenue.="vous pouvez commencer l'envoi de la mailing list en cliquant <a href=admin_mailing3.php?&action=send&page=0&zone=$zone>ICI</a>
		<br><font color=red size=6>ATTENTION  : cette action sur ce module est irréverssible ...</font>";
	}
}

if( $action == "envoi" )
{
	if( $opt_mailing == 0  || $opt_mailing == "" )	
	{	// On part d'une Mailing List vierge
		
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
				$DB_site2->query( $rq_ajout_wysiwyg );
				$wysiwyg_temp = $DB_site2->query_first( "SELECT * FROM wysiwyg" );
			}
			
			//define('DR', $DOCUMENT_ROOT. "boutique/admin/");
			$spaw_root = $DOCUMENT_ROOT. 'scripts/spaw_mailing/';
			include '../../scripts/spaw_mailing/spaw_control.class.php';
			

			echo "<form method=\"post\" action=\"mailing.php\" name=\"form_mailing\">
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
				$rq_ajouter_mailing = "INSERT INTO mailing ( libelleFR, contenuFR,
														  	 libelleDE, contenuDE,
														  	 libelleSP, contenuSP,
														  	 libelleEN, contenuEN,
														  	 libelleIT, contenuIT )	
					VALUES ( '"
							 .addslashes( $wysiwyg_temp[libelleFR] ). "', '" .addslashes( $wysiwyg_temp[contenuFR] ). "', '"
							 .addslashes( $wysiwyg_temp[libelleDE] ). "', '" .addslashes( $wysiwyg_temp[contenuDE] ). "', '"
							 .addslashes( $wysiwyg_temp[libelleSP] ). "', '" .addslashes( $wysiwyg_temp[contenuSP] ). "', '"
							 .addslashes( $wysiwyg_temp[libelleEN] ). "', '" .addslashes( $wysiwyg_temp[contenuEN] ). "', '"
							 .addslashes( $wysiwyg_temp[libelleIT] ). "', '" .addslashes( $wysiwyg_temp[contenuIT] ). "' )";
	
				$DB_site2->query( $rq_ajouter_mailing );
				// Effacement de la table temporaire
				$wysiwyg_suppr = $DB_site2->query( "DELETE FROM wysiwyg" );
				
				echo "<script language=\"javascript\">location.href=\"mailing.php\";</script>";
			}
			else
			{
				// Première fois qu'on charge la page :
				// Effacement de la table temporaire
				$wysiwyg_suppr = $DB_site2->query( "DELETE FROM wysiwyg" );
			}
			
			$sw = new SPAW_Wysiwyg( "content" /*name*/, $content /*value*/,
				   'fr' /*language*/, 'full' /*toolbar mode*/, 'default' /*theme*/,
				   '300px' /*width*/, '400px' /*height*/, '' /*stylesheet file*/);
			$contenu.=$sw->show();
	
			echo "<tr><td><b>LANGUE : $langues[$i_langue]</b></td></tr>
				  <tr><td>TITRE : <input type=\"text\" size=\"40\" name=\"titre\" value=\"$titre\"></td></tr>
				  <tr><td><br>CONTENU :<br>$contenu</td></tr>";
	
			// Si on est à la dernière langue de la liste des langues on affiche le bouton "Valider"
			if( $i_langue == count($langues)-1 ) {
				echo "<tr>";
				// On affiche le bouton "Langue précédente" si on a + d'une langue
				if( $i_langue > 0 ) {
					echo "<td align=\"center\"><input type=\"submit\" name=\"btn_prec\" value=\"Langue précédente\">";
					//echo "					   <input type=\"submit\" name=\"btn_valid\" value=\"Ajouter la news\"></td>";
				}
				else
				{
					//echo "<td align=\"center\"><input type=\"submit\" name=\"btn_valid\"value=\"Ajouter la news\"></td>";
				}
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
			
			echo "<div align=\"center\"><a href=\"mailing.php\" target=\"_self\">Annuler</a></div>";
		}
	}
	
	
	
	
	if( $opt_mailing == 1 )	
	{	// On part d'une Mailing List existante
		
	}





	$mailing = $DB_site2->query_first( "SELECT * FROM mailing ORDER BY mailingid DESC LIMIT 1" );
	if( $mailing[mailingid] != "" )
	{
		//$titre 
	}

	if( !isset( $mailing ) || $mailing == "" )
	{

	}
	
	
	if( !isset( $i_langue ) || $i_langue == "" )
		$i_langue = 0;

	// On vérifie que l'index de la langue passée en paramêtre soit valide
	if( $i_langue >= 0 && $i_langue < count($langues) )
	{
		// Table temporaire
		$wysiwyg_temp = $DB_site2->query_first( "SELECT * FROM wysiwyg" );
		if( !isset( $wysiwyg_temp[wysiwygid] ) )
		{
			$rs_mailing = $DB_site2->query_first( "SELECT * FROM mailing WHERE mailingid = $mailingid" );		

			// Copie dans la table temporaire
			$rq_ajout_wysiwyg = "INSERT INTO wysiwyg ( wysiwygid, libelleFR, contenuFR,
																  libelleDE, contenuDE,
																  libelleSP, contenuSP,
																  libelleEN, contenuEN,
																  libelleIT, contenuIT )
								 VALUES ( '1', '" .addslashes( $rs_mailing[libelleFR] ). "', '"
								 				  .addslashes( $rs_mailing[contenuFR] ). "', '"
								  			      .addslashes( $rs_mailing[libelleDE] ). "', '"
												  .addslashes( $rs_mailing[contenuDE] ). "', '"
								  			      .addslashes( $rs_mailing[libelleSP] ). "', '"
												  .addslashes( $rs_mailing[contenuSP] ). "', '"
								  			      .addslashes( $rs_mailing[libelleEN] ). "', '"
												  .addslashes( $rs_mailing[contenuEN] ). "', '"
								  			      .addslashes( $rs_mailing[libelleIT] ). "', '"
												  .addslashes( $rs_mailing[contenuIT] ). "' )";					   
			$DB_site2->query( $rq_ajout_wysiwyg );
			$wysiwyg_temp = $DB_site2->query_first( "SELECT * FROM wysiwyg" );
		}

		//define('DR', $DOCUMENT_ROOT. "boutique/admin/");
		$spaw_root = $DOCUMENT_ROOT. 'scripts/spaw_mailing/';
		include '../../scripts/spaw_mailing/spaw_control.class.php';
		
		echo "<div align=\"center\"><b>Mailing list</b></div>";
		
		echo "<form method=\"post\" action=\"mailing.php\" name=\"form_mailing\">
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
			$rq_modifier_mailing = "UPDATE mailing
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
					 WHERE mailingid = $mailingid";
			$DB_site2->query( $rq_modifier_mailing );
			// Effacement de la table temporaire
			$wysiwyg_suppr = $DB_site2->query( "DELETE FROM wysiwyg" );
	
			echo "<script language=\"javascript\">location.href=\"mailing.php\";</script>";

		// Si on affiche la première langue
		} else
		{
			// Effacement de la table temporaire
			$wysiwyg_suppr = $DB_site2->query( "DELETE FROM wysiwyg" );

			$titre = $wysiwyg_temp["libelle$langues[$i_langue]"];
			$content = $wysiwyg_temp["contenu$langues[$i_langue]"];
		}
		
		$sw = new SPAW_Wysiwyg( "content" /*name*/, $content /*value*/,
			   'fr' /*language*/, 'full' /*toolbar mode*/, 'default' /*theme*/,
			   '500px' /*width*/, '400px' /*height*/, '' /*stylesheet file*/);
		$contenu.=$sw->show();
		
		echo "<tr><td><b>LANGUE : $langues[$i_langue]</b></td></tr>
			  <tr><td>TITRE : <input type=\"text\" size=\"40\" name=\"titre\" value=\"$titre\"></td></tr>
			  <tr><td><br>CONTENU :<br>$contenu</td></tr>";

		// Si on est à la dernière langue de la liste des langues on valide les modifications
		if( $i_langue == count($langues)-1 )
		{
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
		//echo "<input type=\"hidden\" name=\"action\" value=\"\">";
		echo "<input type=\"hidden\" name=\"mailingid\" value=\"$mailingid\">";
		echo "<input type=\"hidden\" name=\"i_langue\" value=\"$i_langue\">";
		echo "<input type=\"hidden\" name=\"page\" value=\"1\">";

		echo "</table></form>";
		
		echo "<div align=\"center\"><a href=\"mailing.php\" target=\"_self\">Annuler</a></div>";
	}













/*	
	
	$titre = "";
	$content = "";
		
	if( $mailing[mailingid] != "" )
	{
		$title=$mailing[title];
		$content=$mailing[content];		
	}
	echo "
		<center>
		<br>
		<form method=\"post\" action=\"mailing.php\">
		<input type=\"hidden\" name=\"action\" value=\"save\">
		<input type=\"hidden\" name=\"page\" value=\"1\">
		<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">
			<tr> 
			  <td> 
				<table cellpadding=\"3\" cellspacing=\"1\" border=\"0\" width=\"100%\">
				  <tr> 
					<td align=\"center\"><b>Envoyer une mailing list</b><br>
					<font color=\"red\">Attention : ce module envoie des e-mail à tous les membres qui sont inscrits à la newsletter. Toute action sur ce module est irréverssible ...</font>
					</td>
				  </tr>
				  <tr valign=\"top\"> 
					<td align=\"center\">Titre du mail : <input type=\"text\" name=\"title\" size=\"50\" value=\"$title\"></td>
				  </tr>
				  <tr valign=\"top\"> 
					<td align=\"center\">Titre du mail : <input type=\"text\" name=\"title\" size=\"50\" value=\"$title\"></td>
				  </tr>
				  <tr valign=\"top\"> 
					<td align=\"center\">Contenu :<textarea name=\"content\" rows=\"10\" cols=\"50\">$content</textarea></td>
				  </tr>
				  <tr> 
					<td align=\"center\"><input type=\"submit\" value=\"Envoyer\"></td>
				  </tr>
				</table></td>
			</tr>
		</table>
		</form> 
		</center>";
*/

}


if( !isset($action) || $action == "" )
{
	$rq_mailing = $DB_site2->query( "SELECT mailingid, libelle$langue FROM mailing ORDER BY mailingid DESC" );
	if( $DB_site2->num_rows( $rq_mailing ) )
	{	// Si on a déja fait au moins une mailing list
		
		$zonedeliste_mailing = "";
		while( $rs_mailing = $DB_site2->fetch_array( $rq_mailing ) )
		{
			$zonedeliste_mailing .= "<option value=\"".$rs_mailing[mailing]."\">".$rs_mailing["libelle$langue"]."</option>";
		}
		
		$choix_mailing = "
			<table align=\"center\">
			 <tr>
			 	<td>A partir de :</td>	
				<td><input type=\"radio\" name=\"opt_mailing\" value=\"0\"> Mailing list vierge<br>
					<input type=\"radio\" name=\"opt_mailing\" value=\"1\"> Mailing list existante : 
						<select name=\"lst_mailing\">
							$zonedeliste_mailing
						</select>
				</td>
			 </tr>
			</table>";
	}
	
	echo "<form action=\"mailing.php?action=envoi\" method=\"post\">
		   $choix_mailing
		   <div align=\"center\">
		   	<input type=submit value=\"Continuer\"><br><br>
		   </div>
		  </form>";

}


//load_template("admin");
//eval( "\$template = \"$templatecache[admin]\";" );
//$template=stripslashes($template);
//echo $template;
//flush();

include "./footer.php";
?>