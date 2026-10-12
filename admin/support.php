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
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Support</font>
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
// MONTER D'UNE POSITION 
if (isset ($action) and $action == "up")
	{
	$position=$DB_site2->query_first("SELECT position FROM support WHERE supportid = '$supportid'");						
	$nouvelle_position_faq = $position[0] - 1 ;
	$position2=$DB_site2->query_first("SELECT supportid FROM support WHERE position = '$nouvelle_position_faq'");
	$DB_site2->query("UPDATE support SET position = '$nouvelle_position_faq' WHERE supportid = '$supportid'");
	$DB_site2->query("UPDATE support SET position = '$position[0]' WHERE supportid = '$position2[supportid]'");
	echo "<script language=\"javascript\">location.href=\"support.php\";</script>";
	}

// DESCENDRE D'UNE POSITION 
if (isset ($action) and $action == "down")
	{
	$position=$DB_site2->query_first("SELECT position FROM support WHERE supportid = '$supportid'");						
	$nouvelle_position_faq = $position[0] + 1 ;
	$position2=$DB_site2->query_first("SELECT supportid FROM support WHERE position = '$nouvelle_position_faq'" );
	$DB_site2->query("UPDATE support SET position = '$nouvelle_position_faq' WHERE supportid = '$supportid'");
	$DB_site2->query("UPDATE support SET position = '$position[0]' WHERE supportid = '$position2[supportid]'");
	echo "<script language=\"javascript\">location.href=\"support.php\";</script>";
	}

if (isset($action) and $action == "supprimer")
	{
	echo "Etes-vous sûr(e) de vouloir supprimer cette question ?<br><br>
	<a href=\"support.php?action=supprok&question=$question\">OUI</a> | <a href=\"support.php\">NON</a>" ;
	}
	
if (isset($action) and $action == "supprok")
	{// On décale toutes les positions des pages suivantes
	$position_page=$DB_site2->query_first("SELECT position FROM support WHERE supportid = '$question'");
	$position_page = $position_page[0];
	$rq_positions_suivantes = $DB_site2->query("SELECT supportid, position FROM support WHERE position > $position_page" );
	while ($rs_positions_suivantes=$DB_site2->fetch_array($rq_positions_suivantes)) 
		{
		$position_temp = $rs_positions_suivantes[position] - 1;
		$DB_site2->query("UPDATE support SET position = '$position_temp' WHERE supportid = '$rs_positions_suivantes[supportid]'");
		}
	$del=$DB_site2->query("update support set suppr = '1' where supportid = '$question'");
	$action = "" ;
	}
	
if (isset($action) and $action == "visu")
	{
	if (isset($action2) and $action2 == "repondu")
		{
		$reponse=$DB_site2->query("update support set reponse = '$reponse' where supportid = '$question'");
		$mail=$DB_site2->query_first("SELECT mail FROM support where supportid = '$question'");
		/*require "./libmail.php";
		$sujet="Réponse à votre question sur la boutique $host";
		$contenu=$DB_site2->query_first("SELECT contenuFR FROM emails_clients where emailid = '3'") ;
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
		$m= new Mail; // create the mail
		$m->From("forum@$host");
		$m->To("$mail[0]");
		$m->Subject($sujet);
		$m->Body(stripslashes($corps) );  // set the body
		$m->Priority(2) ;  // set the priority to HIGH
		$m->Send();  // send the mail*/
		/* envoi d'un mail par Gmail */
		require_once("../gmail/smtp.php");
		require_once("../gmail/sasl.php");
		$from="arobases@arobases.fr";
		$smtppass=SMTP_PASSWORD_2;
		$localhost="vendre-en-ligne.fr";
		$abuse="arobases@arobases.fr";
		$namefrom="Admin Vendre en ligne";
		$to=$mail[0];
		$subject = $sujet ;
		$message = stripslashes($corps) ;
		gmail($from, $to, $subject, $smtppass, $localhost, $namefrom, $abuse, $message);
		/* fin envoi d'un mail par Gmail */
		}
	echo "<h3>Voir une question posée sur ma boutique</h3>
	<table class=\"table_modules_boutiques\">";
	$question=$DB_site2->query_first("SELECT * FROM support where supportid = '$question'");
	echo "<tr>
	<td align=\"right\"><b>Visiteur : </b></td><td>$question[mail]</td>
	</tr>
	<tr>
	<td align=\"right\"><b>Sujet : </b></td><td><input type=\"text\" value=\"$question[sujet]\" size=\"66\"></td>
	</tr>
	<tr>
	<td align=\"right\" valign=\"top\"><b>Question : </b></td><td><textarea cols=\"50\" rows=\"10\">$question[question]</textarea></td>
	</tr>
	</table>
	<br><br>
	<form method=\"post\" action=\"support.php?action=visu&question=$question[supportid]\">
	<input type=\"hidden\" name=\"action2\" value=\"repondu\">
	<table class=\"table_modules_boutiques\">
	<tr>
	<td align=\"right\" valign=\"top\"><b>Réponse : </b></td><td><textarea name=\"reponse\" cols=\"50\" rows=\"10\">$question[reponse]</textarea></td>
	<td align=\"center\"><input type=\"submit\" value=\"OK\"></td>
	</tr>
	</table>
	</form>
	<br><br><a href=\"support.php\">Retour</a>";
	}

if (!isset($action) or $action == "")
	{
	echo "<h3>Liste des questions déjà posées sur ma boutique</h3>
	<table class=\"table_modules_boutiques\">";
	$questions = $DB_site2->query("SELECT * FROM support where suppr = '0' order by position asc");
	if ($DB_site2->num_rows($questions) > 0)
		{
		echo "<tr>
			   <td align=\"center\"><b>Position</b></td>
			   <td align=\"center\"><b>Visiteur</b></td>
			   <td align=\"center\"><b>Sujet</b></td>
			   <td align=\"center\"><b>Action</b></td>
			  </tr>";
		$nb_faq=$DB_site->num_rows($questions) ;
		$i_faq = 0 ;
		while ($question=$DB_site2->fetch_array($questions))
			{
			$rowalt = "td_promos_".getrowbg();
			$i_faq++ ;
			if ($nb_faq > 1)
				{
				if ($i_faq == 1) // Première marque
					{
					$images_position = "<img src=\"images/vide.gif\" height=\"7\">
										<a href=\"support.php?action=down&supportid=$question[supportid]\">
										<img src=\"images/descendre.gif\" alt=\"Descendre\" title=\"Descendre\" height=\"7\" border=\"0\"></a>";
					}
				elseif ($i_faq == $nb_faq) // Dernière marque
					{
					$images_position = "<a href=\"support.php?action=up&supportid=$question[supportid]\">
										<img src=\"images/monter.gif\" alt=\"Monter\" title=\"Monter\" height=\"7\" border=\"0\"></a>
										<img src=\"images/vide.gif\" height=\"7\">";
					}
				else
					{
					$images_position = "<a href=\"support.php?action=up&supportid=$question[supportid]\">
										<img src=\"images/monter.gif\" alt=\"Monter\" title=\"Monter\" height=\"7\" border=\"0\"></a>
										<a href=\"support.php?action=down&supportid=$question[supportid]\">
										<img src=\"images/descendre.gif\" alt=\"Descendre\" title=\"Descendre\" height=\"7\" border=\"0\"></a>";
					}
				}
			$question[sujet] = substr($question[sujet], 0, 50) ;
			echo "<tr class=\"td_promos\">
					<td class=\"$rowalt\">$images_position</td>
					<td class=\"$rowalt\">$question[mail]</td>
					<td class=\"$rowalt\" align=\"center\">$question[sujet]</td>
					<td class=\"$rowalt\" align=\"center\">
						<a href=\"support.php?action=visu&question=$question[supportid]\">Voir</a>&nbsp;
						<a href=\"support.php?action=supprimer&question=$question[supportid]\">Supprimer</a>
						</td>
				</tr>";
			}
		echo "</table>";
		echo $pagenav ;
		}
	}

echo "				</td>
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