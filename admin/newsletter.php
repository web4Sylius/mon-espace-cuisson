<?php
require "admin_global.php";
//include "../global.php";
setcookie("hostname",$host,time()+3600,"/");
include "./header.php";
include "./menu.php";



function email($destinataire, $sujet ,$messhtml, $mailcontact) {
	global $host, $fichierjoint, $fichierjoint2 ;
	
	$tab_nom_sans_www = explode(".", $host) ;
	$nom_sans_www = $tab_nom_sans_www[1] . "." . $tab_nom_sans_www[2] ;
	$mailfrom = "noreply@$nom_sans_www" ;
	$namefrom = $nom_sans_www ;
	$replyto = $mailcontact ;
	
	$boundary = "_".md5 (uniqid (rand()));
	$entete = "MIME-Version: 1.0\n";
	$entete .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\n";
	$entete .= "X-Sender: <$host>\n";
	$entete .= "X-Mailer: PHP\n";
	$entete .= "X-auth-smtp-user: $mailcontact \n";
	$entete .= "X-abuse-contact: abuse@$nom_sans_www \n";
	$entete .= "Reply-to: $replyto\n";
	$entete .= "From:$namefrom <www-data@ns202967.ovh.net>\n";
	$message .= "--$boundary\n";
	$message .= "Content-Type: text/html; charset=\"iso-8859-1\"\n"; 
	$message .= "Content-Transfer-Encoding: 8bit\n\n"; 
	$message .= $messhtml; 
	if ($fichierjoint[type]!=""){$message .= "\n--$boundary\n";$message .= "Content-Type: $fichierjoint[type]; name=\"$fichierjoint[name]\"\n";$message .= "Content-Transfer-Encoding: base64\n";$message .= "\n";$message .= $fichierjoint[contenu] . "\n";}
	if ($fichierjoint2[type]!=""){$message .= "\n--$boundary\n";$message .= "Content-Type: $fichierjoint2[type]; name=\"$fichierjoint2[name]\"\n";$message .= "Content-Transfer-Encoding: base64\n";$message .= "\n";$message .= $fichierjoint2[contenu] . "\n";}
	$message .="\n\n";
	return @mail($destinataire, $sujet, $message, $entete); 
	}

echo "
<td valign=\"top\">
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
   	<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
		<tr>
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Mes newsletters</font>
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
if( !in_array( "8", $modules ) ) {
	echo "<script language=\"javascript\">location.href=\"index.php\";</script>";
	exit;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// VOIR UNE NEWSLETTER (action=voir)
////////////////////////////////////////////////////////////////////////////////////////////////////////
if( $action == "voir" )
{
	$rs_newsletter = $DB_site2->query_first( "select * from newsletter where newsletterid = '$newsletterid' ");	
	echo "<html><head><title>$rs_newsletter[libelle]</title></head><body>$rs_newsletter[contenu]</body></html>";
	exit;
} 

////////////////////////////////////////////////////////////////////////////////////////////////////////
// SUPPRIMER UNE ADRESSE (action=supprimer_ad1)
////////////////////////////////////////////////////////////////////////////////////////////////////////
if ($action == "supprimer_ad1")
	{
	echo "Etes-vous sûr(e) de bien vouloir supprimer cette adresse des newsletters ?<br><br>
	<a href=\"newsletter.php?action=supprimer_ad&valid_suppr=ok&ad_mail=$ad_mail\">OUI</a> | <a href=\"newsletter.php\">NON</a>" ;
	}
	
////////////////////////////////////////////////////////////////////////////////////////////////////////
// SUPPRIMER UNE ADRESSE (action=supprimer_ad)
////////////////////////////////////////////////////////////////////////////////////////////////////////
if ($action == "supprimer_ad")
	{
	if (isset($valid_suppr) and $valid_suppr == "ok")
		{
		$ad_existe_newsletters=$DB_site2->query_first("select adresse_mail from mails_newsletter where adresse_mail = '$ad_mail'");
		$ad_existe_users=$DB_site2->query_first("select mail as adresse_mail from utilisateur where mail = '$ad_mail'");
		$ad_existe_gl = 0 ;
		$msg_suppr = "" ;
		if ($ad_existe_newsletters[adresse_mail] != "")
			{
			$msg_suppr .= "Adresse e-mail supprimée dans 'mails_newsletter'." ;
			$ajt_ad=$DB_site2->query("update mails_newsletter set allow_email = '0' where adresse_mail = '$ad_mail'");
			$ad_existe_gl = 1 ;
			}	
		if ($ad_existe_users[adresse_mail] != "")
			{
			$msg_suppr .= "<br>Adresse e-mail supprimée dans 'utilisateur'." ;
			$ajt_ad=$DB_site2->query("update utilisateur set allowemail = '0' where mail = '$ad_mail'");
			$ad_existe_gl = 1 ;
			}	
		if ($ad_existe_gl == '0')
			{
			$msg_erreur_suppr = "Adresse non existante !" ;
			}	
		}
	$action = "" ;
	}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// AJOUTER DES ADRESSES (action=ajouter_ad)
////////////////////////////////////////////////////////////////////////////////////////////////////////
if ($action == "ajouter_ad")
	{
	$msg_erreur_import = "" ;
	$msg_import = "" ;
	if (isset($fichier_mails) and $fichier_mails != "")
		{
		if ($_FILES['fichier_mails']['name']!="")
			{
			if ($_FILES['fichier_mails']['type'] == "text/plain") 
				{
				$lines = file ($fichier_mails);
				foreach ($lines as $line_num => $line) 
					{
					$line=str_replace(chr(10), "", $line);
					$line=str_replace(chr(13), "", $line);
					$ad_newsletters=$DB_site2->query_first("select adresse_mail from mails_newsletter where adresse_mail = '$line'");
					$ad_users=$DB_site2->query_first("select mail as adresse_mail from utilisateur where mail = '$line'");
					$ad_existes = "non" ;
					if (ereg("@",$line))
						{
						/*if (ereg(".com$",$line) or ereg(".fr$",$line))
							{*/
							if ($ad_newsletters[adresse_mail] != "")
								{
								$ad_existes = "oui" ;
								$modif_ad_import=$DB_site2->query("update mails_newsletter set allow_email = '1' where  adresse_mail = '$line'");
								}	
							if ($ad_users[adresse_mail] != "") 
								{ 
								$ad_existes = "oui" ;
								$modif_ad=$DB_site2->query("update utilisateur set allowemail = '1' where mail = '$line'");
								}
							if ($ad_existes == "non")
								{
								$ajt_ad_import=$DB_site2->query("INSERT INTO mails_newsletter (adresse_mail, allow_email) VALUES ('$line', '1')");
								}
							$msg_import = "Adresses e-mail ajoutées." ;
							//}
						}
					}				
				}
			else
				{
				$msg_erreur_import = "Vous devez importer un fichier texte !" ;
				}
			}
		else
			{
			$msg_erreur_import = "Vous devez saisir un chemin !" ;
			}
		}
	if (isset($ad_mail) and $ad_mail != "")
		{
		$msg_erreur_saisi = "" ;
		/*if ((!ereg(".com$",$ad_mail)) and (!ereg(".fr$",$ad_mail)))
			{
			$msg_erreur_saisi = "E-mail non valide.<bR>" ;
			}*/
		if (!ereg("@",$ad_mail))
			{
			$msg_erreur_saisi = "E-mail non valide.<bR>" ;
			}
		if ($msg_erreur_saisi == "")
			{
			$ad_existe_newsletters=$DB_site2->query_first("select adresse_mail from mails_newsletter where adresse_mail = '$ad_mail'");
			$ad_existe_users=$DB_site2->query_first("select mail as adresse_mail from utilisateur where mail = '$ad_mail'");
			$ad_existe_gl = 0 ;
			if ($ad_existe_newsletters[adresse_mail] != "")
				{
				$msg_saisi = "Adresse déjà existante : e-mail ajouté." ;
				$modif_ad=$DB_site2->query("update mails_newsletter set allow_email = '1' where  adresse_mail = '$ad_mail'");
				$ad_existe_gl = 1 ;
				}	
			if ($ad_existe_users[adresse_mail] != "")
				{
				$msg_saisi = "Adresse déjà existante : e-mail ajouté." ;
				$modif_ad=$DB_site2->query("update utilisateur set allowemail = '1' where mail = '$ad_mail'");
				$ad_existe_gl = 1 ;
				}
			if ($ad_existe_gl == '0')
				{
				$msg_saisi = "Adresse e-mail ajoutée." ;
				$ajt_ad=$DB_site2->query("INSERT INTO mails_newsletter (adresse_mail, allow_email) VALUES ('$ad_mail', '1')");
				}
			}
		}
	$action = "inscrits" ;
	} 


if (isset($action) and $action == "demandesms")
	{
	if ($nbmails != "" and is_numeric($nbmails))
		{
		$user_info=$DB_site->query_first("SELECT * FROM client WHERE username = '$username'");
		//require_once "./libmail.php";
		$mail_contact=$DB_site2->query_first("SELECT mail_contact FROM parametresgeneraux");
		$mail_exp = $mail_contact[0] ;
		$mail_dest = "c.jullien@arobases.fr" ;
		$sujet="Demande de location de fichier e-mails";
		$corps="
		<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">
		<html>
		<head>
		<meta http-equiv=\"content-type\" content=\"text/html;charset=iso-8859-15\">
		</head>
		<body font-family=\"Verdana\">
		<br>$user_info[prenom] $user_info[nom] ($user_info[domaine]) vient de faire une demande de location de $nbmails adresses e-mail pour un envoi.<br>
		Merci de le contacter au plus vite au $user_info[telephone] ou par mail : $user_info[mail]<bR><br>
		</body>
		</html>";
		email($mail_dest, $sujet, stripslashes($corps), $mail_exp) ;
		$erreur = "Votre demande a bien été prise en compte.<br>
		Un responsable vous contactera par e-mail ou téléphone dans les plus brefs délais." ;
		}
	else
		{
		$erreur= "La saisie est incorrecte" ;
		}
	$action = "inscrits" ;
	}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// VOIR LES INSCRITS (action=inscrits)
////////////////////////////////////////////////////////////////////////////////////////////////////////
if ($action == "inscrits")
	{
	echo "
	<h3>Importer un fichier d'adresses e-mail</h3>
	<form method=\"post\" enctype=\"multipart/form-data\" action=\"newsletter.php?action=ajouter_ad\">
		<font class=\"cost\">$msg_import $msg_erreur_import</font>
		<table class=\"table_caracts\">
			<tr>
				<td align=\"center\" colspan=\"3\" valign=\"middle\">Le fichier doit être de type texte (.txt).<br>Une adresse e-mail par ligne.<br><bR></td>
			</tr>
			<tr>
				<td align=\"right\" width=\"25%\">Fichier : </td>
				<td align=\"left\" width=\"25%\"><input type=\"file\" name=\"fichier_mails\"></td>
				<td align=\"center\" width=\"50%\"><input type=\"submit\" value=\"Importer\"></td>
			</tr>
		</table>
	</form>
	<br><bR>
	<h3>Saisir une adresse e-mail</h3>
	<form method=\"post\" action=\"newsletter.php?action=ajouter_ad\">
		<font class=\"cost\">$msg_saisi $msg_erreur_saisi</font>
		<table class=\"table_caracts\">
			<tr>
				<td align=\"right\" width=\"25%\">Adresse : </td>
				<td align=\"left\" width=\"25%\"><input type=\"text\" name=\"ad_mail\" size=\"20\"></td>
				<td align=\"center\" width=\"50%\"><input type=\"submit\" value=\"Enregistrer\"></td>
			</tr>
		</table>
	</form>
	<br><br>
	<a href=\"newsletter.php\">Retour</a>";
	} 

///////////////////////////////////////////////////////////////////////////////////////////////////////
// AFFICHER LES NEWSLETTERS
////////////////////////////////////////////////////////////////////////////////////////////////////////
if (!isset($action) || $action == "")
	{
	$DB_site2->query("drop table if exists mesmails") ;
	$wysiwyg_suppr=$DB_site2->query("DELETE FROM wysiwyg");
	$rq_newsletter=$DB_site2->query("SELECT * FROM newsletter ORDER BY newsletterid DESC") ;
	echo "<h3>Newsletters existantes</h3>
	<table class=\"table_modules_boutiques\">";
	$nb_pages=$DB_site2->num_rows($rq_newsletter) ;
	while ($rs_newsletter=$DB_site2->fetch_array($rq_newsletter)) 
		{
		$rowalt = "td_" . getrowbg() ;
		echo "<tr>
				<td class=\"$rowalt\" width=\"50%\">$rs_newsletter[libelle]</td>
				<td class=\"$rowalt\" width=\"10%\" align=\"center\"><a href=\"newsletter.php?action=editer&newsletterid=$rs_newsletter[newsletterid]\" alt=\"Visualiser / Modifier\" title=\"Visualiser / Modifier\">Editer</a></td>
				<td class=\"$rowalt\" width=\"20%\" align=\"center\"><a href=\"newsletter.php?action=envoyertest&newsletterid=$rs_newsletter[newsletterid]\" title=\"Faire un test\">Faire un test</a></td>
				<td class=\"$rowalt\" width=\"10%\" align=\"center\"><a href=\"newsletter.php?action=envoyer&newsletterid=$rs_newsletter[newsletterid]\" alt=\"Envoyer la newsletter\" title=\"Envoyer la newsletter\">Envoyer</a></td>
				<td class=\"$rowalt\" width=\"10%\" align=\"center\"><a href=\"newsletter.php?action=supprimer&newsletterid=$rs_newsletter[newsletterid]\" alt=\"Supprimer la newsletter\" title=\"Supprimer la newsletter\">Supprimer</a></td>
			</tr>";
		}
	if ($nb_pages == 0)
		{
		echo "<tr><td align=\"center\">Il n'y a aucune newsletter pour le moment</td></tr>";
		}
	echo "</table>
	<div align=\"center\"><br><a href=\"newsletter.php?action=ajouter\" target=\"_self\" alt=\"Ajouter une newsletter\" title=\"Ajouter une newsletter\">Ajouter une newsletter</a></div>
	<br><br>
	<h3>Inscrits à la newsletter</h3>" ;
	if (!isset($pagenumber) or $pagenumber=="") 
		{
		$pagenumber="1";
		}
	$perpage="30";
	$limitlower=($pagenumber-1)*$perpage;
	$limitupper=($pagenumber)*$perpage;
	$newsletterscount=$DB_site2->query_first("SELECT count(*) FROM mails_newsletter where allow_email = '1'");
	$pagenav1=getpagenav($newsletterscount[0],"newsletter.php?action=");
	
	$userscount=$DB_site2->query_first("SELECT count(*) FROM utilisateur where allowemail = '1'");
	$pagenav2=getpagenav($userscount[0],"newsletter.php?action=");

	$newsletters=$DB_site2->query("select * from mails_newsletter where allow_email = '1' order by adresse_mail LIMIT $limitlower, $perpage");
	$users=$DB_site2->query("select * from utilisateur where allowemail = '1' order by mail LIMIT $limitlower, $perpage");
	echo "
	<a href=\"newsletter.php?action=inscrits\" title=\"Ajouter des adresses\">Ajouter des adresses</a>
	<br><br>
	<table class=\"table_caracts\">
		<tr>
			<td align=\"left\" colspan=\"3\" valign=\"middle\"><strong>Personnes qui ont un compte client sur la boutique</strong><bR><bR>$pagenav2</td>
		</tr>" ;
		while ($user=$DB_site2->fetch_array($users))
			{
			echo "<tr>
					<td align=\"left\" valign=\"middle\">$user[mail]</td>
					<td align=\"left\" valign=\"middle\">$user[prenom] $user[nom]</td>
					<td align=\"left\" valign=\"middle\"><a href=\"newsletter.php?action=supprimer_ad1&ad_mail=$user[mail]\" alt=\"Désinscrire $user[mail]\" title=\"Désinscrire $user[mail]\">Désinscrire</a></td>
				</tr>" ;
			}			
	echo "<tr>
			<td align=\"left\" colspan=\"3\" valign=\"middle\"><bR><br><strong>Personnes seulement inscrites à la newsletter</strong><bR><bR>$pagenav1</td>
		</tr>" ;
		while ($newsletter=$DB_site2->fetch_array($newsletters))
			{
			echo "<tr>
					<td align=\"left\" valign=\"middle\">$newsletter[adresse_mail]</td>
					<td align=\"left\" valign=\"middle\">&nbsp;</td>
					<td align=\"left\" valign=\"middle\"><a href=\"newsletter.php?action=supprimer_ad1&ad_mail=$newsletter[adresse_mail]\" alt=\"Désinscrire $newsletter[adresse_mail]\" title=\"Désinscrire $newsletter[adresse_mail]\">Désinscrire</a></td>
				</tr>" ;
			}			
	echo "</table>" ;
	}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// ENVOYER UNE NEWSLETTER TEST (action=envoyertest2)
////////////////////////////////////////////////////////////////////////////////////////////////////////
if ($action == "envoyertest2")
	{
	if (isset($monmail) and ereg("@", $monmail) and ereg(".", $monmail))
		{
		$rs_newsletter=$DB_site2->query_first("select * from newsletter where newsletterid = '$newsletterid'") ;
		$parametres_generaux=$DB_site2->query_first("select * from parametresgeneraux") ;
		$message="<html><head><title>$rs_newsletter[libelle]</title></head><body>$rs_newsletter[contenu]</body></html>";
		$messagetxt="$rs_newsletter[contenutexte]";
		email($monmail, stripslashes($rs_newsletter[libelle]), stripslashes($message), $parametres_generaux[mail_contact]);
		echo "<h3>Envoyer un e-mail de test</h3>
		<br>
		Email de test envoyé à $monmail.
		<br><br><a href=\"newsletter.php\">Retour</a>" ;
		}
	else
		{
		echo "Mail incorrect" ;
		$action = "envoyertest" ;
		}
	}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// ENVOYER UNE NEWSLETTER TEST (action=envoyertest)
////////////////////////////////////////////////////////////////////////////////////////////////////////
if ($action == "envoyertest")
	{
	echo "<h3>Envoyer un e-mail de test</h3>
	<br>
	<form method=\"post\" action=\"newsletter.php\">
	<input type=\"hidden\" name=\"action\" value=\"envoyertest2\">
	<input type=\"hidden\" name=\"newsletterid\" value=\"$newsletterid\">
	Adresse de réception : <input type=\"text\" name=\"monmail\" value=\"\">
	<input type=\"submit\" value=\"Envoyer\">
	</form>" ;
	}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// ENVOYER UNE NEWSLETTER (action=envoyer)
////////////////////////////////////////////////////////////////////////////////////////////////////////
if ($action == "envoyer")
	{
	echo "<h3>Envoyer une newsletter > Etape 1</h3>
	<b>Choix des critères</b><br>
	(définir la cible)<br><br>
	<table class=\"table_remises_ajout\">
	<form action=\"newsletter.php\" method=\"post\">
		<input type=\"hidden\" name=\"newsletterid\" value=\"$newsletterid\">
		<input type=\"hidden\" name=\"action\" value=\"envoyer2\">
		<tr>
			<td align=\"right\">Civilité :</td>
			<td align=\"left\"><select name=\"civilite\">
					<option value=\"\">Toutes</option>
					<option value=\"0\">Monsieur</option>
					<option value=\"1\">Madame</option>
					</select>
			</td>
		</tr>
		<tr>
			<td align=\"right\">Pays :</td>
			<td align=\"left\">
				<select name=\"paysid\">
					<option value=\"0\">Tous</option>";
		$payss=$DB_site2->query("SELECT * FROM pays order by libelle ");
		while ($pays=$DB_site2->fetch_array($payss)) 
			{
			echo "<option value=\"$pays[paysid]\">$pays[libelle]</option>";
			}
		echo "</select>
			</td>
		</tr>
		<tr>
			<td align=\"right\">Département :<br>
			(France seulement)</td>
			<td align=\"left\"><select name=\"dept\">
					<option value=\"0\">Tous</option>";
		$i=1;
		while($i<95) 
			{
			echo "<option value=\"$i\">$i</option>";
			$i++;
			}
		echo "</select>
			</td>
		</tr>
		</table>
		<br><input type=\"submit\" value=\"Etape suivante\">
		</form>";
	}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// ENVOYER UNE NEWSLETTER 2 (action=envoyer2)
////////////////////////////////////////////////////////////////////////////////////////////////////////
if ($action == "envoyer2")
	{
	if ($civilite != "") 
		{
		$requette .= " and civilite = '$civilite'" ;
		}
	if ($paysid != 0) 
		{
		$requette .= " and paysid = '$paysid'" ;
		}
	if ($dept != 0) 
		{
		if (strlen ($dept) < 2) 
			{
			$dept = "0" . $dept ;
			}
		$requette .= " and codepostal LIKE ('$dept%') and paysid = '57'" ;
		}
	$listeusers=$DB_site2->query("select mail from utilisateur where 1 = 1 $requette and allowemail = '1'") ;
	$listemails=$DB_site2->query("select adresse_mail from mails_newsletter where allow_email = '1'") ;
	$DB_site2->query("create table mesmails (id int(6) auto_increment, mail varchar(255) default NULL, PRIMARY KEY (id))") ;
	while ($listeuser=$DB_site2->fetch_array($listeusers))
		{
	//	$DB_site2->query("insert into mesmails (mail) values ('$listeuser[mail]')") ;
		}
	$DB_site2->query("insert into mesmails (mail) values ('bm@arobases.fr')") ;
	while ($listemail=$DB_site2->fetch_array($listemails))
		{
		$DB_site2->query("insert into mesmails (mail) values ('$listemail[adresse_mail]')") ;
		}
	/*$contage=$DB_site2->query_first("SELECT COUNT(*) FROM utilisateur WHERE 1 = 1 $requette and allowemail = '1'") ;*/
	$nb=$DB_site2->query_first("SELECT COUNT(*) FROM mesmails") ;
	$total = $nb[0] ;
	echo "<h3>Envoyer une newsletter > Etape 2</h3>
		<b>Validation</b><br><br>
		$total e-mails vont être envoyés, êtes vous sûr(e) de vouloir effectuer cette action ?<br><bR>
		<font class=\"cost\">ATTENTION : cette action est irréversible !!!</font><br><br>
		<form action=\"newsletter.php\" method=\"post\" style=\"display:inline; \">
		<input type=\"hidden\" name=\"newsletterid\" value=\"$newsletterid\">
		<input type=\"hidden\" name=\"action\" value=\"envoyer3\">
		<input type=\"hidden\" name=\"civilite\" value=\"$civilite\">
		<input type=\"hidden\" name=\"paysid\" value=\"$paysid\">
		<input type=\"hidden\" name=\"dept\" value=\"$dept\">
		<input type=\"hidden\" name=\"page\" value=\"0\">
		<input type=\"submit\" value=\"Terminer\">
		</form>
		<form action=\"newsletter.php\" method=\"post\" style=\"display:inline; \">
		<input type=\"submit\" value=\"Anuler\">
		</form>";
	}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// ENVOYER UNE NEWSLETTER 3 (action=envoyer3)
////////////////////////////////////////////////////////////////////////////////////////////////////////
if ($action == "envoyer3")
	{
	$nb=$DB_site2->query_first("SELECT COUNT(*) FROM mesmails") ;
	$total = $nb[0] ;

	$mail=$DB_site2->query_first("SELECT mail FROM mesmails LIMIT $page, 1") ;
	$rs_newsletter=$DB_site2->query_first("select * from newsletter where newsletterid = '$newsletterid'") ;

	$parametres_generaux=$DB_site2->query_first("select * from parametresgeneraux") ;
	$message="<html><head><title>$rs_newsletter[libelle]</title></head><body>$rs_newsletter[contenu]</body></html>";
	$messagetxt="$rs_newsletter[contenutexte]";
	email($mail[mail], stripslashes($rs_newsletter[libelle]), stripslashes($message), $parametres_generaux[mail_contact]) ;
	echo "Envoi du message " .($page+1) . " / $total. ($mail[mail])<br>" ;
	$page++ ;

	if (($page) < $total)
		{
		echo"<script language=\"javascript\">
			 setTimeout(\"location.href='newsletter.php?action=envoyer3&newsletterid=$newsletterid&page=$page';\",500);
			 </script>";
		echo "$mail[mail] = <a href=\"newsletter.php?action=envoyer3&newsletterid=$newsletterid&page=$page\">suite</a>";
		}
	echo "<br><a href=\"newsletter.php\">Retour</a>" ;
	}
////////////////////////////////////////////////////////////////////////////////////////////////////////
// EDITER UNE NEWSLETTER (action=editer)
////////////////////////////////////////////////////////////////////////////////////////////////////////
if ($action == "editer")
	{
	// On vérifie que l'index passé en paramêtre soit valide
	if (isset($newsletterid))
		{
		// Table temporaire
		$wysiwyg_temp=$DB_site2->query_first("SELECT * FROM wysiwyg");
		if (!isset($wysiwyg_temp[wysiwygid]))
			{
			$rs_newsletter=$DB_site2->query_first("select * from newsletter where newsletterid = '$newsletterid'");
			// Copie de la news en cours de modification dans la table temporaire
			$rq_ajout_wysiwyg = "INSERT INTO wysiwyg (wysiwygid, libelleFR, contenuFR) VALUES ('1', '".addslashes($rs_newsletter[libelle])."', '".addslashes($rs_newsletter[contenu]). "')";					   
			$DB_site2->query($rq_ajout_wysiwyg);
			$wysiwyg_temp=$DB_site2->query_first("SELECT * FROM wysiwyg");
			}
		echo "<div align=\"center\"><b>Modifier une newsletter</b></div>";
		$rs_newsletter=$DB_site2->query_first("select * from newsletter where newsletterid = '$newsletterid'");
		echo "<form method=\"post\" action=\"newsletter.php\" name=\"form_newsletter\">
				<table align=\"center\" width=\"500\">";
		// Copie des titre/contenu de la page postée dans la table wysiwyg
		if (isset($titre) || isset($contenu))
			{
			$content = str_replace('src="/configurations/', "src=\"http://$host/configurations/", stripslashes($content));
			$rq_modifier_wysiwyg = "UPDATE wysiwyg SET libelleFR = '$titre', contenuFR = '".addslashes($content)."'";
			$DB_site2->query($rq_modifier_wysiwyg);	
			$wysiwyg_temp=$DB_site2->query_first("SELECT * FROM wysiwyg");
			}
		if (isset($_POST['btn_valid']) && $_POST['btn_valid'] == 'Valider les modifications')
			{
			$rq_modifier_newsletter = "UPDATE newsletter SET libelle = '".addslashes($wysiwyg_temp[libelleFR])."', contenu = '".addslashes($wysiwyg_temp[contenuFR])."', 
									   contenutexte = '$contenutexte' WHERE newsletterid = $newsletterid";
			$DB_site2->query($rq_modifier_newsletter);
			// Effacement de la table temporaire
			$wysiwyg_suppr=$DB_site2->query("DELETE FROM wysiwyg");
			echo "<script language=\"javascript\">location.href=\"newsletter.php\";</script>";
			exit;
			}
		else
			{
			// Si on affiche la première langue de la news à modifier
			// Effacement de la table temporaire
			$wysiwyg_suppr=$DB_site2->query("DELETE FROM wysiwyg");
			$titre = $wysiwyg_temp["libelleFR"];
			$content = $wysiwyg_temp["contenuFR"];
			}
		//$rootpath = "/home/arobases/www/vendre-en-ligne.fr/";
		include($rootpath."fckeditor/fckeditor.php") ;	
		echo "<tr><td>TITRE : <input type=\"text\" size=\"40\" name=\"titre\" value=\"$titre\"></td></tr>
			  <tr><td><br>CONTENU TEXTE : <br><textarea cols=\"50\" rows=\"7\" name=\"contenutexte\">$rs_newsletter[contenutexte]</textarea><br>
			  <tr><td><br>CONTENU :<br>";
		$contenu = "<table align=\"center\" width=\"100%\"><tr><td><br>" ;
		$oFCKeditor = new FCKeditor('content') ;
		$oFCKeditor->BasePath = '/fckeditor/' ;
		$oFCKeditor->Value		= $content ;
		$oFCKeditor->Height	= 400 ;
		$oFCKeditor->Width	= 585 ;
		$contenu .= $oFCKeditor->Create() ;
		$contenu .= "</td></tr></table>";
		echo "$contenu</td></tr>
		<tr><td colspan=\"2\" align=\"center\"><input type=\"submit\" name=\"btn_valid\" value=\"Valider les modifications\"></td></tr>
		<input type=\"hidden\" name=\"action\" value=\"editer\">
		<input type=\"hidden\" name=\"newsletterid\" value=\"$newsletterid\">
		</table></form>
		<div align=\"center\"><a href=\"newsletter.php\" target=\"_self\">Annuler</a></div>";
		}
	}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// AJOUTER UNE NEWSLETTER (action=ajouter)
////////////////////////////////////////////////////////////////////////////////////////////////////////	
if( $action == "ajouter" )
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


	echo "<div align=\"center\"><b>Ajouter une newsletter</b></div>";
	
	echo "<form method=\"post\" action=\"newsletter.php\" name=\"form_newsletter\">
			<table align=\"center\" width=\"500\">";
	// Copie des titre/contenu de la page postée dans la table wysiwyg
	if( isset( $titre ) || isset( $contenu ) )
	{
		$content = str_replace('src="/configurations/', "src=\"http://$host/configurations/", stripslashes($content));
		$rq_modifier_wysiwyg = "UPDATE wysiwyg SET libelleFR = '$titre',
					contenuFR = '".addslashes($content)."'";
		$DB_site2->query( $rq_modifier_wysiwyg );
		$wysiwyg_temp = $DB_site2->query_first( "SELECT * FROM wysiwyg" );
	}
		
	if( isset($_POST['btn_valid']) && $_POST['btn_valid']=='Ajouter la newsletter' )
	{
		$rq_ajouter_newsletter = "INSERT INTO newsletter ( libelle, contenu )	
			VALUES (  '" .addslashes( $wysiwyg_temp[libelleFR] ). "', '" .addslashes( $wysiwyg_temp[contenuFR] ). "' )";
		$DB_site2->query($rq_ajouter_newsletter);
		// Effacement de la table temporaire
		$wysiwyg_suppr = $DB_site2->query( "DELETE FROM wysiwyg" );

		echo "<script language=\"javascript\">location.href=\"newsletter.php\";</script>";
		exit;
	}
	else
	{
		// Première fois qu'on charge la page :
		// Effacement de la table temporaire
		$wysiwyg_suppr = $DB_site2->query( "DELETE FROM wysiwyg" );
	}

	//define('DR', "/home/boutique/www/spaw/");
	//$rootpath = "/home/arobases/www/vendre-en-ligne.fr/";
	include($rootpath."fckeditor/fckeditor.php") ;	

	echo "<tr><td><b>LANGUE : $langues[$i_langue]</b></td></tr>
		  <tr><td>TITRE : <input type=\"text\" size=\"40\" name=\"titre\" value=\"$titre\"></td></tr>
		  <tr><td><br>CONTENU :<br>";
		$contenu = "<table align=\"center\" width=\"100%\"><tr><td><br>" ;
		$oFCKeditor = new FCKeditor('content') ;
		$oFCKeditor->BasePath = '/fckeditor/' ;
		$oFCKeditor->Value		= $content ;
		$oFCKeditor->Height	= 400 ;
		$oFCKeditor->Width	= 585 ;
		$contenu .= $oFCKeditor->Create() ;
		$contenu .= "</td></tr></table>";

	echo "$contenu</td></tr>";

	echo "<tr><td colspan=\"2\"><input type=\"submit\" name=\"btn_valid\" value=\"Ajouter la newsletter\"></td></tr>";
	echo "<input type=\"hidden\" name=\"action\" value=\"ajouter\">";
	echo "</table></form>";
		
	echo "<div align=\"center\"><a href=\"newsletter.php\" target=\"_self\">Retour</a></div>";
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// SUPPRIMER NEWSLETTER (action=supprimer)
////////////////////////////////////////////////////////////////////////////////////////////////////////	
if( $action == "supprimer" )
{
	// Confirmation de suppression
	echo "êtes vous sûr de vouloir supprimer cette newsletter ?<br>
		  <a href=\"newsletter.php?action=supprimer2&newsletterid=$newsletterid\">Oui</a>
		  | <a href=\"newsletter.php\">Non</a>";
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// SUPPRIMER UNE NEWSLETTER (action=supprimer2)
////////////////////////////////////////////////////////////////////////////////////////////////////////	
if( $action == "supprimer2" )
{
	// Suppression
	$rq_supprimer = "DELETE FROM newsletter WHERE newsletterid = $newsletterid";
	$DB_site2->query( $rq_supprimer );

	echo "<script language=\"javascript\">location.href=\"newsletter.php\";</script>";
}


echo "</td>
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