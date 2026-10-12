<?php

require "admin_global.php";
//include "./global.php";
echo "<script language=\"javascript\"> 
<!-- 

function fermer(id)
	{
	window.close() ;
	} 

function charger_fermer(id)
	{
	nav = navigator.appName.substring(0,3);
	ver = navigator.appVersion.substring(0,1);
	if (nav == \"Mic\" && ver >= 4)  
		{
		window.opener.location.href('facture.php?action=editer&facturid='+id);
		}
	else 
		{
		window.opener.location.reload();
		} 
	window.close() ;
	} 

//-->
</script>" ;

echo "
<html>
<LINK href=\"vendre-en-ligne.css\" type=\"text/css\" rel=\"stylesheet\">
<body>
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
   	<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
		<tr>
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Contacter le client</font>
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

if (isset($action) and $action == "ajouter")
	{
	if (isset($sujet) and $sujet != "" and isset($message) and $message != "")
		{
		$facture=$DB_site2->query_first("SELECT * FROM facture where facturid = '$factureid'");
		echo "<br><br>
		<form method=\"post\" action=\"contact_facture.php\" name=\"formu\">
			<input type=\"hidden\" name=\"action\" value=\"ajouter2\">
			<input type=\"hidden\" name=\"factureid\" value=\"$factureid\">
			<input type=\"hidden\" name=\"mail\" value=\"$facture[mail]\">
			<input type=\"hidden\" name=\"sujet\" value=\"".stripslashes($sujet)."\">
			<input type=\"hidden\" name=\"message\" value=\"".stripslashes($message)."\">
			Vous allez envoyer l'e-mail <strong>".stripslashes($sujet)."</strong> à $facture[mail].<br>
			Veuillez confirmer ou annuler l'envoi : <br><br>
			<a href=\"Javascript: document.formu.submit();\">Confirmer</a> &nbsp;&nbsp; <a href=\"Javascript: fermer('$factureid');\">Annuler</a>
		</form>" ;
		}
	else
		{
		echo "<font class=\"cost\">Les deux champs sont obligatoires.</font>" ;
		$action = "" ;
		}
	}

if (isset($action) and $action == "ajouter2")
	{
	$date = date('Y-m-d-H-i-s');
	$DB_site2->query("insert into facture_contact (factureid, datecontact, sujet, contenu) values ('$factureid', '$date', '$sujet', '$message')");
	
	// ENVOI DU MAIL
	$corps = "
	<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">
	<html>
		<head>
			<meta http-equiv=\"content-type\" content=\"text/html;charset=iso-8859-15\">
		</head>
		<body font-family=\"Verdana\">
			$message
		</body>
	</html>";
	$params=$DB_site2->query_first("SELECT mail_contact FROM parametresgeneraux");
	$from = $params[mail_contact] ;
	$to = $mail ;
	require "fonctions.php";
	envoi($from, $to, stripslashes($sujet), stripslashes($corps)) ;
	$action = "quitter" ;
	}


if (isset($action) and $action == "quitter")
	{
	echo "<script language=\"javascript\">charger_fermer('$factureid') ;</script>" ;
	}

if (!isset($action) or $action == "")
	{
	$facture=$DB_site2->query_first("SELECT * FROM facture where facturid = '$factureid'");
	echo "<form method=\"post\" enctype=\"multipart/form-data\" style=\"display:inline\">
			<input type=\"hidden\" name=\"action\" value=\"ajouter\">
			<input type=\"hidden\" name=\"factureid\" value=\"$factureid\">
			<br>
			<br>
			<table>" ;
			echo "<tr>
				<td>Sujet* : </td>
				<td><input type=\"text\" name=\"sujet\" value=\"$sujet\" size=\"57\" maxlength=\"255\"></td>
			</tr>
			<tr>
				<td valign=\"top\">Message* : </td>
				<td><textarea name=\"message\" cols=\"43\" rows=\"6\">$message</textarea></td>
			</tr>
		</table>
		<br>
		<input type=\"submit\" value=\"Envoyer\">&nbsp;&nbsp;<input type=\"button\" value=\"Retour\" onclick=\"Javascript: fermer('$factureid') ;\"></form>" ;
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
</body>
</html>";
			
?>
