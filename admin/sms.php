<?php
require_once __DIR__.'/../secrets.php';
require "admin_global.php";
include "./header.php";
echo "
<SCRIPT LANGUAGE=\"JavaScript\">
function CheckLen(Target)
	{
	StrLen = Target.value.length
	if (StrLen > 160)
		{
		Target.value = Target.value.substring(0,160);
		CharsLeft = 160;
		}
	else
		{
		CharsLeft = StrLen;
		}
	var nb = 160 - CharsLeft ;
	document.formu.nb_caracts.value = nb;
	}
	</SCRIPT>" ;
include "./menu.php";

if (in_array("109", $modules))
	{
	echo "
	<td valign=\"top\">
	<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
	<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
	  <tr>
	   <td>
		<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
			<tr>
				<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Mes SMS</font>
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
	   <td align=\"center\" valign=\"middle\">" ;
	
	if (!isset($action) or $action == "")
		{
		$smss=$DB_site2->query("select * from sms order by date desc");
		echo "<h3>Liste des messages déjà envoyés</h3>
		<table class=\"table_caracts\">
			<tr>
				<td class=\"td_head_promos\"><strong>Date</strong></td>
				<td class=\"td_head_promos\"><strong>Message</strong></td>
				<td class=\"td_head_promos\"><strong>Nombre de SMS</strong></td>
			</tr>" ;
		while ($sms=$DB_site2->fetch_array($smss))
			{
			$rowalt = "td_promos_".getrowbg () ;
			echo "<tr>
					<td class=\"$rowalt\" align=\"center\" valign=\"top\">$sms[date]</td>
					<td class=\"$rowalt\">$sms[texte]</td>
					<td class=\"$rowalt\" align=\"center\">$sms[nb_rcpt]</td>
				</tr>" ;
			}	
		if ($DB_site2->num_rows($smss) == 0)
			{
			echo "<tr>
					<td colspan=\"3\" align=\"center\"><br>Aucun message n'a encore été envoyé.<br><br></td>
				</tr>" ;
			}		
		echo "</table><br>
		<a href=\"sms.php?action=new\">Créer un nouveau message</a><br><bR>
		<h3>Liste des inscrits</h3>
		<table class=\"table_caracts\">
			<tr>
				<td class=\"td_head_promos\"><a href=\"sms.php?action=voirliste\">Voir la liste</a></td>
			</tr>" ;
		echo "</table><br><br>
		L'envoi de SMS est un service de la société Arobases.<br>
		Les SMS envoyés sont comptabilisés et facturés en fin de mois (0.10 € / SMS).<br>
		L'envoi de SMS est limité à 1000 par mois." ;
		}
	
	if (isset($action) and $action == "demandesms")
		{
		if ($nbsms != "" and is_numeric($nbsms))
			{
			$user_info=$DB_site->query_first("SELECT * FROM client WHERE username='$username' ");
			require_once "./libmail.php";
			$mail_contact = $DB_site2->query_first("SELECT mail_contact FROM parametresgeneraux");
			$mail_exp = $mail_contact[0] ;
			$mail_dest = "o.haro@arobases.fr" ;
			//$mail_dest = "c.jullien@arobases.fr" ;
			$sujet="Demande de location de fichier SMS";
			$corps="
			<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">
			<html>
			<head>
			<meta http-equiv=\"content-type\" content=\"text/html;charset=iso-8859-15\">
			</head>
			<body font-family=\"Verdana\">
			<br>$user_info[prenom] $user_info[nom] ($user_info[domaine]) vient de faire une demande de location de $nbsms SMS pour un envoi.<br>
			Merci de le contacter au plus vite au $user_info[telephone] ou par mail : $user_info[mail]<bR><br>
			</body>
			</html>";
			$m= new Mail; // create the mail
			$m->From($mail_exp);
			$m->To($mail_dest);
			$m->Subject($sujet);
			$m->Body( stripslashes($corps) );  // set the body
			$m->Priority(2) ;  // set the priority to HIGH
			$m->Send();  // send the mail
			$erreur = "Votre demande a bien été prise en compte.<br>
			Un responsable vous contactera par e-mail ou téléphone dans les plus brefs délais." ;
			}
		else
			{
			$erreur= "La saisie est incorrecte" ;
			}
		$action = "voirliste" ;
		}
	
	if (isset($action) and $action == "voirliste")
		{
		echo "<h3>Liste des inscrits qui recevront les SMS</h3>
		<table class=\"table_caracts\">
			<tr>
				<td class=\"td_head_promos\"><strong>Nom Prénom</strong></td>
				<td class=\"td_head_promos\"><strong>Téléphone</strong></td>
			</tr>" ;
		$numeros=$DB_site2->query("SELECT * FROM utilisateur WHERE portable != '' ORDER BY nom, prenom");
		while ($numero=$DB_site2->fetch_array($numeros))
			{
			$rowalt = "td_promos_".getrowbg () ;
			echo "<tr>
					<td class=\"$rowalt\">$numero[nom] $numero[prenom]</td>
					<td class=\"$rowalt\">$numero[portable]</td>
				</tr>" ;
			}	
		if ($DB_site2->num_rows($numeros) == 0)
			{
			echo "<tr>
					<td colspan=\"3\" align=\"center\"><br>Aucun visiteur ne s'est encore inscrit.<br><br></td>
				</tr>" ;
			}		
		echo "</table><br>
		<br><br>
		<a href=\"sms.php\">Retour</a>" ;
		}
	
	if (isset($action) and $action == "envoi")
		{
		if (($sender != "") and (strlen($sender) <= 12) and ($data != "") and (count($rcpt) > 0))
			{
			$reussis = 0 ;
			$echoues = 0 ;
			$total = count($rcpt) ;
			echo $total;
			$ajd = date('Y-m-d') ;
			$DB_site2->query("insert into sms (date, expediteur, texte, nb_rcpt) values ('$ajd', '$sender', '$data', '$total')");
			$monsms=$DB_site2->insert_id() ;
			$data = urlencode($data) ;
			$loginmobyt=MOBYT_LOGIN;
			$passmobyt=MOBYT_PASSWORD;
			foreach ($rcpt as $key => $value) 
				{
				$erreur = "" ;
				if ($value == 'on')
					{
					$numerotel=$DB_site2->query_first("SELECT portable FROM utilisateur WHERE userid = '$key'");
					$bonnum = "+33" . substr($numerotel[0], 1) ;
					$bonnum = urlencode($bonnum) ;
					$r = file ("http://multilevel.mobyt.fr/sms/send.php?user=$loginmobyt&pass=$passmobyt&rcpt=$bonnum&data=$data&sender=$sender&qty=l") ;
					if (substr($r[0], 0, 2) == "OK") 
						{
						$reussis++ ;
						$erreur .= "Le message a été envoyé correctement ($numerotel[0])<br>" ;
						}
					elseif (substr($r[0], 0, 2) == "KO")
						{
						$echoues++ ;
						$erreur .= "Message non envoyé ($numerotel[0]) : " . substr($r[0], 3) . "<br>" ;
						}
					echo $erreur ;
					}
				}
			$DB_site2->query("update sms set nb_rcpt = '$reussis'");
			echo "<br><strong>Envoi de $total SMS : $reussis réussi(s) / $echoues échoué(s)</strong>
			<br><bR>Votre message a été sauvegardé.<br>
			<a href=\"sms.php\">Retour</a>" ;
			}
		else
			{
			$msg = "<font class=\"cost\">Tous les champs sont obligatoires !</font><br>" ;
			$action = "new" ;
			}
		}
	
	if (isset($action) and $action == "new")
		{
		echo "<h3>Envoyer un message SMS</h3>
		$msg
		<form method=\"post\" action=\"sms.php\" name=\"formu\">
		<input type=\"hidden\" name=\"action\" value=\"envoi\">
		<table class=\"table_coupscoeur\">
			<tr>
				<td align=\"right\" valign=\"top\"><strong>Expéditeur (11 caractères maximum)</strong>&nbsp;</td>
				<td valign=\"middle\"><input type=\"text\" name=\"sender\" value=\"$sender\" size=\"12\" maxlength=\"12\"></td>
			</tr>
			<tr>
				<td align=\"right\" valign=\"top\"><strong>Message</strong>&nbsp;
				<br>(160 caractères max)
				<br><br>Plus que <input style=\"text-align:center;background-color:#FFFFFF;border:none;color:#000000;font-size:14px;font-family:verdana;\" readonly name=\"nb_caracts\" id=\"nb_caracts\" size=\"2\"></td>
				<td><textarea name=\"data\" cols=\"43\" rows=\"3\" maxlength=\"160\" onFocus=\"CheckLen(this)\" onKeyDown=\"CheckLen(this)\" onKeyUp=\"CheckLen(this)\" onBlur=\"CheckLen(this)\">$data</textarea></td>
			</tr>
			<tr>
				<td align=\"right\" valign=\"top\"><strong>Inscrits</strong>&nbsp;</td>
				<td>
				<table>" ;
				$passage = 0 ;
				$nbtot = 0 ;
				$numeros=$DB_site2->query("SELECT * FROM utilisateur WHERE portable != '' ORDER BY nom, prenom");
				$tot=$DB_site2->num_rows($numeros) ;
				while ($numero=$DB_site->fetch_array($numeros))
					{
					$nbtot++ ;
					$passage++ ;
					if ($passage == 1)
						{
						echo "<tr>" ;
						}
					$num = $numero[userid] ;
					echo "<td align=\"center\"><input type=\"checkbox\" name=\"rcpt[$num]\" checked></td>
							<td align=\"left\">$numero[nom] $numero[prenom]</td>" ;
					if ($passage == 3 or $nbtot == $tot)
						{
						echo "</tr>" ;
						$passage = 0 ;
						}
					}
				echo "</table>
				</td>
			</tr>
		</table><br><input type=\"submit\" value=\"Envoyer\">
		</form><br>
		<a href=\"sms.php\">Retour</a>" ;
		}
	
		echo "</td>
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
	}
else
	echo "Vous n'avez pas les droits nécessaires pour accéder à cette page." ;
echo "
<SCRIPT LANGUAGE=\"JavaScript\">
document.formu.nb_caracts.value = 160;
</SCRIPT>" ;


include "./footer.php";
?>