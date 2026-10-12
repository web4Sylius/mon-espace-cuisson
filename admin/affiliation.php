<?php
require_once __DIR__.'/../secrets.php';

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
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Gestion des affiliations</font>
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

echo "<h3>Gestion des affiliations</h3>
<br>
<table><tr><td>
<a href=\"affiliation.php\">Menu général</a> | 
<a href=\"affiliation.php?action=parametres\">Paramètres</a> | <a href=\"affiliation.php?action=affilies\">Affiliés</a> |
 <a href=\"affiliation.php?action=demandes\">Demandes de versement</a> | <a href=\"affiliation.php?action=honoraires\">Honoraires</a><br>
</td></tr></table>
" ;

if (!isset($action) or $action == "") // menu prinicipal
	{
	$nb=$DB_site2->query_first("SELECT count(*) FROM affilie where actif = '1'");
	echo "<br><Br>Actuellement $nb[0] affilié(s) actif(s)" ;
	}
elseif (isset($action) and $action == "parametres") // parametres
	{
	if (isset($action2) and $action2 == "enregistrer")
		{
		$modif_params=$DB_site2->query("update parametresgeneraux set pourcentage_default_affilie = '$pct', avoir_minimum_affilie = '$mtt'");
		}
	$params=$DB_site2->query_first("SELECT * FROM parametresgeneraux");
	echo "<br><br><form method=\"post\">
	<input type=\"hidden\" name=\"action2\" value=\"enregistrer\">
	<table><tr><td>Pourcentage par défaut attribué aux affiliés : 
	<input type=\"text\" name=\"pct\" value=\"$params[pourcentage_default_affilie]\" size=\"1\"> %
		<br><Br>Montant minimum à partir duquel l'affilié peut demander un versement : 
		<input type=\"text\" name=\"mtt\" value=\"$params[avoir_minimum_affilie]\" size=\"2\"> €
		</td></tr></table>
		<br>
		<input type=\"submit\" value=\"Enregistrer\">
		</form>" ;
	}
elseif (isset($action) and $action == "affilies") // affiliés
	{
	if (isset($action2) and $action2 == "activer") // activer un affilié
		{
		$active_aff=$DB_site2->query("update affilie set actif = '1' where affilieid = '$aff'");
		$mail=$DB_site2->query_first("SELECT mail_contact FROM parametresgeneraux") ;
		$contenu=$DB_site2->query_first("SELECT contenuFR FROM emails_clients where emailid = '4'") ;
		$aff=$DB_site2->query_first("SELECT * FROM affilie where affilieid = '$aff'") ;
		$temp = "title$langue";
		//require_once "./libmail.php";
		$sujet="${$temp} : Activation de votre compte affilié";
		$corps="
		<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">
		<html>
		<head>
		<meta http-equiv=\"content-type\" content=\"text/html;charset=iso-8859-15\">
		</head>
		<body font-family=\"Verdana\">
		$contenu[0]
		</body>
		</html>";
		/*$m= new Mail; // create the mail
		$m->From("$mail[0]");
		$m->To("$aff[mail]");
		$m->Subject($sujet);
		$m->Body( stripslashes($corps) );  // set the body
		$m->Priority(2) ;  // set the priority to HIGH
		$m->Send();  // send the mail*/
		/* envoi d'un mail par Gmail */
		require_once("./gmail/smtp.php");
		require_once("./gmail/sasl.php");
		$from="arobases@$mondom";
		$smtppass=SMTP_PASSWORD_2;
		$localhost="$mondom";
		$abuse="arobases@$mondom";
		$namefrom="${$title}";
		$to=$aff[mail];
		$subject=$sujet;
		$message=stripslashes($corps) ;
		gmail($from,$to,$subject,$smtppass,$localhost,$namefrom,$abuse,$message);
		/* fin envoi d'un mail par Gmail */
		}
	if (isset($action2) and $action2 == "supprimer") // supprimer un affilié
		{
		$suppr_aff=$DB_site2->query("delete from affilie where affilieid = '$aff'");
		}
	echo "<br><br><strong>Liste des affiliés non actifs</strong><br><br>
	<table class=\"table_modif_categ\">
		<tr>
			<td align=\"center\"><strong>Affilié</strong></td>
			<td align=\"center\"><strong>E-mail</strong></td>
			<td align=\"center\"><strong>ACTION</strong></td>
		</tr>" ;
	$non_actifs=$DB_site2->query("SELECT * FROM affilie where actif = '0'");
	while ($non_actif=$DB_site2->fetch_array($non_actifs))
		{
		echo "<tr>
			<td align=\"center\">$non_actif[nom] $non_actif[prenom]</td>
			<td align=\"center\">$non_actif[mail]</td>
			<td align=\"center\"><a href=\"affiliation.php?action=affilies&action2=activer&aff=$non_actif[affilieid]\">ACTIVER</a> 
			<a href=\"affiliation.php?action=affilies&action2=supprimer&aff=$non_actif[affilieid]\">SUPPRIMER</a></td>
		</tr>" ;
		}
	echo "</table>
	<br><br>
	<strong>Liste des affiliés actifs</strong><br><br>
	<table class=\"table_promos\">
		<tr>
			<td align=\"center\"><strong>Affilié</strong></td>
			<td align=\"center\"><strong>Lots (gains)</strong></td>
			<td align=\"center\"><strong>Honoraires<br>(versements)</strong></td>
		</tr>" ;
	$actifs=$DB_site2->query("SELECT * FROM affilie where actif = '1' order by nom, prenom");
	while ($actif=$DB_site2->fetch_array($actifs))
		{
		echo "<tr>
			<td align=\"left\" valign=\"top\" width=\"130\"><a href=\"affilie.php?aff=$actif[affilieid]\">$actif[nom] $actif[prenom]</a></td>
			<td align=\"center\" width=\"380\"><table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">" ;
		$lots=$DB_site2->query("SELECT * FROM affilie_lot al inner join facture f on(al.factureid = f.facturid) where affilieid = '$actif[affilieid]' and valide in (1, 5)");
		$liste_honoraires = "0" ;
		while ($lot=$DB_site2->fetch_array($lots))
			{
			$lot[date] = substr($lot[date], 8, 2) . "/" . substr($lot[date], 5, 2) . "/" . substr($lot[date], 0, 4) ;
			if ($lot[honoraireid] != 0)
				{
				$liste_honoraires .= ",".$lot[honoraireid] ;
				$hono=$DB_site2->query_first("SELECT * FROM affilie_honoraires where honoraireid = '$lot[honoraireid]'") ;
				if ($hono[dateversement] == "0000-00-00")
					{
					$hono[datedemande] = substr($hono[datedemande], 8, 2) . "/" . substr($hono[datedemande], 5, 2) . "/" . substr($hono[datedemande], 0, 4) ;
					$lot[date] = "<a href=\"\" alt=\"Versement demandé le $hono[datedemande]\" title=\"Versement demandé le $hono[datedemande]\"><font color=\"green\">$lot[date]</font></a>" ;
					}
				else
					{
					$hono[dateversement] = substr($hono[dateversement], 8, 2) . "/" . substr($hono[dateversement], 5, 2) . "/" . substr($hono[dateversement], 0, 4) ;
					$lot[date] = "<a href=\"\" alt=\"Versement effectué le $hono[dateversement]\" title=\"Versement effectué le $hono[dateversement]\"><font color=\"red\">$lot[date]</font></a>" ;
					}
				}
			$lot[gain] = number_format ($lot[pourcentage] * $lot[montant] / 100, 2, ",", " ") ;
			$lot[montant] = number_format ($lot[montant], 2, ",", " ") ;
			echo "<tr>
					<td>Cde $lot[factureid] : $lot[pourcentage]% de $lot[montant]€ = <strong>$lot[gain]€</strong></td>
				</tr>" ;
			}
		echo "</table></td>
			<td align=\"center\" valign=\"top\">" ;
		$honos=$DB_site2->query("SELECT * FROM affilie_honoraires where honoraireid in ($liste_honoraires) and dateversement != '0000-00-00'");
		while ($hono=$DB_site2->fetch_array($honos))
			{
			$hono[dateversement] = substr($hono[dateversement], 8, 2) . "/" . substr($hono[dateversement], 5, 2) . "/" . substr($hono[dateversement], 0, 4) ;
			echo "<a href=\"honoraire.php?honoraireid=$hono[honoraireid]\" target=\"_blank\" alt=\"Feuille d'honoraires du $hono[dateversement]\" title=\"Feuille d'honoraires du $hono[dateversement]\"><font color=\"red\">PDF</font></a>" ;
			}
		echo "</td>
		</tr>" ;
		}
	echo "</table>	" ;
	}
elseif (isset($action) and $action == "demandes") // demandes de versement
	{
	if (isset($action2) and $action2 == "ok") // demandes de versement
		{
		$ajd = date ('Y-m-d') ;
		switch ($moyen)
			{
			case "0" ;
				$moyen = "" ;
				break ;
			case "1" ;
				$moyen = "Chèque" ;
				break ;
			case "2" ;
				$moyen = "Virement" ;
				break ;
			}
		$modif_honoraire=$DB_site2->query("update affilie_honoraires set dateversement = '$ajd', moyen = '$moyen' where honoraireid = '$dde'");
		$aff=$DB_site2->query_first("select * from affilie a, affilie_lot al where a.affilieid = al.affilieid and al.honoraireid = '$dde'");
		$contenu=$DB_site2->query_first("SELECT contenuFR FROM emails_clients where emailid = '5'") ;
		$mail=$DB_site2->query_first("SELECT mail_contact FROM parametresgeneraux") ;
		$temp = "title$langue";
		//require_once "./libmail.php";
		$sujet="${$temp} : Versement d'honoraires effectué";
		$corps="
		<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">
		<html>
		<head>
		<meta http-equiv=\"content-type\" content=\"text/html;charset=iso-8859-15\">
		</head>
		<body font-family=\"Verdana\">
		$contenu[0]
		</body>
		</html>";
		/*$m= new Mail; // create the mail
		$m->From("$mail[0]");
		$m->To("$aff[mail]");
		$m->Subject($sujet);
		$m->Body( stripslashes($corps) );  // set the body
		$m->Priority(2) ;  // set the priority to HIGH
		$m->Send();  // send the mail*/
		/* envoi d'un mail par Gmail */
		require_once("./gmail/smtp.php");
		require_once("./gmail/sasl.php");
		$from="arobases@$mondom";
		$smtppass=SMTP_PASSWORD_2;
		$localhost="$mondom";
		$abuse="arobases@$mondom";
		$namefrom="${$title}";
		$to=$aff[mail];
		$subject=$sujet;
		$message=stripslashes($corps) ;
		gmail($from,$to,$subject,$smtppass,$localhost,$namefrom,$abuse,$message);
		/* fin envoi d'un mail par Gmail */
		}
	echo "<bR><br><strong>Demandes de versement</strong>
	<br><br><table class=\"table_promos\">
		<tr>
			<td align=\"center\"><strong>En date du</strong></td>
			<td align=\"center\"><strong>Lots concernés</strong></td>
			<td align=\"center\"><strong>Montant</strong></td>
			<td align=\"center\"><strong>Règlement</strong></td>
		</tr>" ;
	$demandes=$DB_site2->query("SELECT * FROM affilie_honoraires where dateversement = '0000-00-00'");
	while ($demande=$DB_site2->fetch_array($demandes))
		{
		$demande[datedemande] = substr($demande[datedemande], 8, 2) . "/" . substr($demande[datedemande], 5, 2) . "/" . substr($demande[datedemande], 0, 4) ;
		$demande[montant] = number_format ($demande[montant], 2, ",", " ") ;
		echo "<tr>
				<td align=\"center\" valign=\"top\">$demande[datedemande]</td>
				<td width=\"55%\" valign=\"top\"><table>" ;
		$lots=$DB_site2->query("SELECT * FROM affilie_lot where honoraireid = '$demande[honoraireid]'");
		while ($lot=$DB_site2->fetch_array($lots))
			{
			$lot[date] = substr($lot[date], 8, 2) . "/" . substr($lot[date], 5, 2) . "/" . substr($lot[date], 0, 4) ;
			$lot[gain] = number_format ($lot[pourcentage] * $lot[montant] / 100, 2, ",", " ") ;
			$lot[montant] = number_format ($lot[montant], 2, ",", " ") ;
			echo "<tr><td>Le $lot[date] : $lot[pourcentage] % sur $lot[montant] €</td><td>soit $lot[gain] €</td></tr>" ;
			}
		$aff=$DB_site2->query_first("SELECT distinct(affilieid) FROM affilie_lot where honoraireid = '$demande[honoraireid]'");
		echo "</table></td>
				<td align=\"center\" valign=\"top\">$demande[montant] €</td>
				<td align=\"center\" valign=\"top\"><form method=\"post\">
				<input type=\"hidden\" name=\"action2\" value=\"ok\">
				<input type=\"hidden\" name=\"dde\" value=\"$demande[honoraireid]\">
				<input type=\"hidden\" name=\"aff\" value=\"$aff[0]\">
					<select name=\"moyen\">
						<option value=\"0\" selected></option>
						<option value=\"1\">Chèque</option>
						<option value=\"2\">Virement</option>
					</select><input type=\"submit\" value=\"Ok\"></form>
				</td>
			</tr>" ;
		}
	echo "</table>" ;
	} 
elseif (isset($action) and $action == "honoraires") // honoraires
	{
	echo "<br><br><strong>Feuilles d'honoraires (versements effectués)</strong><br><bR>" ;
	$honoraires=$DB_site2->query("select* from affilie_honoraires where dateversement != '0000-00-00' order by dateversement desc");
	while ($honoraire=$DB_site2->fetch_array($honoraires))
		{
		$honoraire[montant] = number_format ($honoraire[montant], 2, ",", " ") ;
		$honoraire[dateversement] = substr ($honoraire[dateversement], 8, 2) . "/" . substr ($honoraire[dateversement], 5, 2) . "/" . substr ($honoraire[dateversement], 0, 4) ;
		echo "Versement par $honoraire[moyen] en date du $honoraire[dateversement] pour un montant de $honoraire[montant] € &nbsp;&nbsp;
		<a href=\"honoraire.php?honoraireid=$honoraire[honoraireid]\" target=\"_blank\" alt=\"Feuille d'honoraires au format PDF\" title=\"Feuille d'honoraires au format PDF\"><strong>PDF</strong></a><bR>" ;
		}
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
        </tr>";

include "./footer.php";
?>