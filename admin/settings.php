<?php

require "admin_global.php";
//include "./global.php";
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
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Informations de ma boutique</font>
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

$userinfo=$DB_site2->query_first("SELECT * FROM parametresgeneraux");

if ($action=="dopasswordchange") {
	if (isset($pass) and $pass!="" and isset($newpass) and $newpass!="" and isset($newpassconfirm) and $newpassconfirm!="") {
		if ($newpassconfirm==$newpass) {
			if (MD5($pass)==$user_info[password]) {
				$DB_site2->query("UPDATE parametresgeneraux SET password = MD5('$newpass')");
				echo "Votre nouveau mot de passe est actuellement actif.<br>Vous allez devoir vous reloguer à present...";
				$action="";
			} else {
				echo "Le mot de passe actuel que vous avez saisi n'est pas bon ...";
				$action="passwordchange";
			}
		} else {
			echo "Vous devez saisir deux fois le même nouveau mot de passe.";
			$action="passwordchange";
		}
	} else {
		echo "Vous devez remplir tous les champs demandés ...";
		$action="passwordchange";
	}
}


if ($action=="passwordchange") 
	{
	echo "<h3>Modifier mon mot de passe</h3>
		<form action= \"settings.php\" method=\"POST\">
		<TABLE CLASS=\"table_remises\" align=\"center\">
			<TR CLASS=\"td_location\">
				<TD align=\"right\" width=\"50%\">Mot de passe actuel :&nbsp;</TD>
				<TD><input type=password name=pass></TD>
			</TR>
			<TR CLASS=\"td_location\">
				<TD align=\"right\">Nouveau mot de passe :&nbsp;</TD>
				<TD><input type=password name=newpass></TD>
			</TR>
			<TR CLASS=\"td_location\">
				<TD align=\"right\">Confirmation nouveau mot de passe :&nbsp;</TD>
				<TD><input type=password name=newpassconfirm></TD>
			</TR>
		</TABLE>
		<br><input type=\"hidden\" name=\"action\" value=\"dopasswordchange\"><input type=\"submit\" value=\"Valider\"><br>
		</form>
		<br><a href=settings.php>Retour</a>";
}


if ($action=="doraisonchange") {
	$DB_site2->query("UPDATE parametresgeneraux SET raison_sociale='$raison_sociale'");
	echo "Changement de raison sociale effectué";
	$action="";
}


if ($action=="raisonchange") {
	echo "<h3>Modifier ma raison sociale</h3>
		<form action= \"settings.php\" method=\"POST\">
		<TABLE CLASS=\"table_remises\" align=\"center\">
			<TR CLASS=\"td_location\">
				<TD align=\"right\" width=\"50%\">Raison sociale :&nbsp;</TD>
				<TD><input type=text name=\"raison_sociale\" value=\"$userinfo[raison_sociale]\"></TD>
			</TR>
		</TABLE>
		<br><input type=\"hidden\" name=\"action\" value=\"doraisonchange\"><input type=\"submit\" value=\"Valider\"><br>
		</form>
		<br><a href=\"settings.php\">Retour</a>";
}


if ($action=="donomchange") {
	$DB_site2->query("UPDATE parametresgeneraux SET nom_contact='$nom $prenom'");
	echo "Changement de nom de contact réussi";
	$action="";
}


if ($action=="nomchange") {
	echo "<h3>Modifier mon nom</h3>
	<form action= \"settings.php\" method=\"POST\">
	<TABLE CLASS=\"table_remises\" align=\"center\">
		<TR CLASS=\"td_location\">
			<td align=\"right\">Nom :&nbsp;</td>
			<td><input type=text name=\"nom\" value=\"$userinfo[nom]\"></td>
		</tr>
		<TR CLASS=\"td_location\">
			<td align=\"right\">Prénom :&nbsp;</td>
			<td><input type=text name=\"prenom\" value=\"$userinfo[prenom]\"></td>
		</tr>
	</table>
	<br><input type=hidden name=action value=donomchange><input type=submit value=Valider><br>
	</form>
	<br><a href=settings.php>Retour</a>";
}


if ($action=="doadressechange") {
	$DB_site2->query("UPDATE parametresgeneraux SET adresse='$adresse' , cp='$codepostal' , ville='$ville'");
	echo "Changement d'adresse réussi";
	$action="";
}


if ($action=="adressechange") {
	echo "<h3>Modifier mon adresse</h3>
	<form action= \"settings.php\" method=\"POST\">
	<TABLE CLASS=\"table_remises\" align=\"center\">
		<TR CLASS=\"td_location\">
			<td align=\"right\">Adresse :&nbsp;</td>
			<tD><input type=text name=\"adresse\" value=\"$userinfo[adresse]\"></td>
		</tr>
		<TR CLASS=\"td_location\">
			<td align=\"right\">Code postal :&nbsp;</td>
			<tD><input type=text name=\"codepostal\" value=\"$userinfo[codepostal]\">
		<tr>
			<td align=\"right\">ville :&nbsp;</td>
			<tD><input type=text name=\"ville\" value=\"$userinfo[ville]\"></td>
		</tr>
	</table>
	<br><input type=hidden name=action value=doadressechange><input type=submit value=Valider><br>
	</form>
	<br><a href=settings.php>Retour</a>";
}


if ($action=="dotelephonechange") {
	$DB_site2->query("UPDATE parametresgeneraux SET telephone='$telephone'");
	echo "Changement de numero de telephone réussi";
	$action="";
}

if ($action=="telephonechange") {
	echo "<h3>Modifier mon téléphone</h3>
	<form action= \"settings.php\" method=\"POST\">
	<TABLE CLASS=\"table_remises\" align=\"center\">
		<TR CLASS=\"td_location\">
			<td align=\"right\">Téléphone :&nbsp;</td>
			<td><input type=text name=\"telephone\" value=\"$userinfo[telephone]\"></td>
		</tr>
	</table>
	<br><input type=\"hidden\" name=\"action\" value=\"dotelephonechange\"><input type=\"submit\" value=\"Valider\"><br>
	</form>
	<br><a href=settings.php>Retour</a>";
}




if ($action=="dofaxchange") {
	$DB_site2->query("UPDATE parametresgeneraux SET fax='$fax'");
	echo "Changement de numero de fax réussi";
	$action="";
}

if ($action=="faxchange") {
	echo "<h3>Modifier mon fax</h3>
	<form action= \"settings.php\" method=\"POST\">
	<TABLE CLASS=\"table_remises\" align=\"center\">
		<TR CLASS=\"td_location\">
			<td align=\"right\">Fax :&nbsp;</td>
			<td><input type=text name=\"fax\" value=\"$userinfo[fax]\"></td>
		</tr>
	</table>
	<br><input type=\"hidden\" name=\"action\" value=\"dofaxchange\"><input type=\"submit\" value=\"Valider\"><br>
	</form>
	<br><a href=settings.php>Retour</a>";
}

if ($action=="dosiretchange") {
	$DB_site2->query("UPDATE parametresgeneraux SET siret='$siret'");
	echo "Changement de numero de siret réussi";
	$action="";
}

if ($action=="siretchange") {
	echo "<h3>Modifier mon siret</h3>
	<form action= \"settings.php\" method=\"POST\">
	<TABLE CLASS=\"table_remises\" align=\"center\">
		<TR CLASS=\"td_location\">
			<td align=\"right\">Siret :&nbsp;</td>
			<td><input type=text name=\"siret\" value=\"$userinfo[siret]\"></td>
		</tr>
	</table>
	<br><input type=\"hidden\" name=\"action\" value=\"dosiretchange\"><input type=\"submit\" value=\"Valider\"><br>
	</form>
	<br><a href=settings.php>Retour</a>";
}

if ($action=="dotvachange") {
	$DB_site2->query("UPDATE parametresgeneraux SET tva='$tva'");
	echo "Changement de numero de tva réussi";
	$action="";
}

if ($action=="tvachange") {
	echo "<h3>Modifier mon numéro de tva</h3>
	<form action= \"settings.php\" method=\"POST\">
	<TABLE CLASS=\"table_remises\" align=\"center\">
		<TR CLASS=\"td_location\">
			<td align=\"right\">N° TVA :&nbsp;</td>
			<td><input type=text name=\"tva\" value=\"$userinfo[tva]\"></td>
		</tr>
	</table>
	<br><input type=\"hidden\" name=\"action\" value=\"dotvachange\"><input type=\"submit\" value=\"Valider\"><br>
	</form>
	<br><a href=settings.php>Retour</a>";
}




if ($action=="domailchange") {
	if (preg_match('/^[-!#$%&\'*+\\.\/0-9=?A-Z^_{|}~]+@([-0-9A-Z]+\.)+([0-9A-Z]){2,4}$/i', $mail)) {
		$DB_site2->query("UPDATE parametresgeneraux SET mail_contact='$mail'");
		echo "Changement d'adresse e-mail réussi";
		$action="";
	} else {
		echo "Erreur de changement d'adresse e-mail";
		$action="mailchange";
	}
}


if ($action=="mailchange") {
	echo "<h3>Modifier mon e-mail</h3>
	<form action= \"settings.php\" method=\"POST\">
	<TABLE CLASS=\"table_remises\" align=\"center\">
		<TR CLASS=\"td_location\">
			<td align=\"right\">Adresse e-mail :&nbsp;</td>
			<td><input type=text name=\"mail\" value=\"$userinfo[mail]\"></td>
		</tr>
	</table>
	<br><input type=hidden name=action value=domailchange><input type=submit value=Valider><br>
	</form>
	<br><a href=settings.php>Retour</a>";
}


if (!isset($action) or $action=="") 
	{
	$userinfo=$DB_site2->query_first(" SELECT * FROM parametresgeneraux WHERE username='$user_info[username]' ");
	echo "<br><h3>Mon compte</h3>
	<bR><TABLE CLASS=\"table_modules_boutiques\" align=\"center\">
	<TR CLASS=\"td_alt1\"><TD align=\"right\" width=\"40%\">Login :&nbsp;</b></td><td COLSPAN=2><b>$userinfo[username]</b></td></tr>
	<TR CLASS=\"td_alt2\"><td align=\"right\">Mot de passe :&nbsp;</td><td COLSPAN=2><a href=settings.php?action=passwordchange>Changer de mot de passe</a></td></tr>
	<TR CLASS=\"td_alt1\"><td align=\"right\">Raison sociale :&nbsp;</td><td><b>$userinfo[raison_sociale]</b> 
		<TD><A HREF='settings.php?action=raisonchange' onmouseOver=\"document.images['image10'].src='./images/I_Changer2.gif';\" onmouseOut=\"document.images['image10'].src='./images/I_Changer.gif';\">
		<IMG  SRC=\"./images/I_Changer.gif\" NAME=\"image10\" BORDER=0 Height=15 WIDTH=15></A></TD>
	</td></tr>
	<TR CLASS=\"td_alt2\"><td align=\"right\">Nom du contact :&nbsp;</td><td><b>$userinfo[nom_contact]</b> 
		<TD><A HREF='settings.php?action=nomchange' onmouseOver=\"document.images['image11'].src='./images/I_Changer2.gif';\" onmouseOut=\"document.images['image11'].src='./images/I_Changer.gif';\">
		<IMG  SRC=\"./images/I_Changer.gif\" NAME=\"image11\" BORDER=0 Height=15 WIDTH=15></A></TD>
	</td></tr>
	<TR CLASS=\"td_alt1\"><td align=\"right\">Adresse :&nbsp;</td><td><b>$userinfo[adresse] $userinfo[cp] $userinfo[ville]</b> 
		<TD><A HREF='settings.php?action=adressechange' onmouseOver=\"document.images['image12'].src='./images/I_Changer2.gif';\" onmouseOut=\"document.images['image12'].src='./images/I_Changer.gif';\">
		<IMG  SRC=\"./images/I_Changer.gif\" NAME=\"image12\" BORDER=0 Height=15 WIDTH=15></A></TD>
	</td></tr>
	<TR CLASS=\"td_alt2\"><td align=\"right\">Adresse e-mail :&nbsp;</td><td><b>$userinfo[mail_contact]</b> 
		<TD><A HREF='settings.php?action=mailchange' onmouseOver=\"document.images['image13'].src='./images/I_Changer2.gif';\" onmouseOut=\"document.images['image13'].src='./images/I_Changer.gif';\">
		<IMG  SRC=\"./images/I_Changer.gif\" NAME=\"image13\" BORDER=0 Height=15 WIDTH=15></A></TD>
	</td></tr>
	<TR CLASS=\"td_alt1\"><td align=\"right\">Téléphone :&nbsp;</b></td><td><b>$userinfo[telephone]</b> 
		<TD><A HREF='settings.php?action=telephonechange' onmouseOver=\"document.images['image14'].src='./images/I_Changer2.gif';\" onmouseOut=\"document.images['image14'].src='./images/I_Changer.gif';\">
		<IMG  SRC=\"./images/I_Changer.gif\" NAME=\"image14\" BORDER=0 Height=15 WIDTH=15></A></TD>
	</td></tr>
	<TR CLASS=\"td_alt2\"><td align=\"right\">Fax :&nbsp;</td><td><b>$userinfo[fax]</b> 
		<TD><A HREF='settings.php?action=faxchange' onmouseOver=\"document.images['image15'].src='./images/I_Changer2.gif';\" onmouseOut=\"document.images['image15'].src='./images/I_Changer.gif';\">
		<IMG  SRC=\"./images/I_Changer.gif\" NAME=\"image15\" BORDER=0 Height=15 WIDTH=15></A></TD>
	</td></tr>
	<TR CLASS=\"td_alt2\"><td align=\"right\">Siret :&nbsp;</td><td><b>$userinfo[siret]</b> 
		<TD><A HREF='settings.php?action=siretchange' onmouseOver=\"document.images['image16'].src='./images/I_Changer2.gif';\" onmouseOut=\"document.images['image16'].src='./images/I_Changer.gif';\">
		<IMG  SRC=\"./images/I_Changer.gif\" NAME=\"image16\" BORDER=0 Height=15 WIDTH=15></A></TD>
	</td></tr>
	<TR CLASS=\"td_alt2\"><td align=\"right\">N° TVA :&nbsp;</td><td><b>$userinfo[tva]</b> 
		<TD><A HREF='settings.php?action=tvachange' onmouseOver=\"document.images['image17'].src='./images/I_Changer2.gif';\" onmouseOut=\"document.images['image17'].src='./images/I_Changer.gif';\">
		<IMG  SRC=\"./images/I_Changer.gif\" NAME=\"image17\" BORDER=0 Height=15 WIDTH=15></A></TD>
	</td></tr>
	</table><br><a href=\"index.php\">Retour</a><br><br>
	";
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