<?php
require_once __DIR__.'/../secrets.php';

require "admin_global.php";
echo "<SCRIPT LANGUAGE=\"JavaScript\">
<!-- 
function ajouter(action_envoyee)
	{
	document.formu.action2.value=action_envoyee;
	document.formu.submit() ;
	}
//-->
</script>" ;
include "./header.php";
include "./menu.php";
include "./fonctions.php";	


echo "
<td valign=\"top\">
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
   	<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
		<tr>
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Commande</font>
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
   <td align=\"center\" valign=\"middle\">";

if (isset($action) and $action == "etape5") // équivaut à cde3.php
	{
	$facture=$DB_site2->query_first("SELECT * FROM facture WHERE facturid = '$factureid'");
	$prix_contre_remboursement=$DB_site2->query_first("SELECT prix_contre_remboursement FROM parametresgeneraux");
	if ($facture[moyenid] == "3")
		{
		$DB_site2->query("UPDATE facture SET prixport = $prixport - $prix_contre_remboursement[0] WHERE facturid = '$facture[facturid]'");
		}
	$DB_site2->query("UPDATE facture SET moyenid = '2', valide = '0' WHERE facturid = '$facture[facturid]'");
	$lignefactures=$DB_site2->query("SELECT * FROM lignefacture WHERE factureid = '$facture[facturid]'");
	$totalmontant = 0 ;
	while ($lignefacture=$DB_site->fetch_array($lignefactures))
		{
		$totalmontant += $lignefacture[prix] * $lignefacture[qte] ;
		}
	// remise sut tout catalogue 
	if (in_array ("106", $modules))
		{
		if ($facture[pctremise] != 0)
			{
			$fmttremise = $totalmontant * ($facture[pctremise] / 100) ;
			$totalmontant -= $fmttremise ;
			}
		}
	// remise
	if (in_array ("3", $modules))
		{
		if ($facture[montantremise] != 0)
			{
			$totalmontant -= $facture[montantremise] ;
			}
		} 
	// Bons de réduction 
	if (in_array("13", $modules))
		{
		if ($facture[montantcadeau] != 0)
			{
			$totalmontant -= $facture[montantcadeau] ;
			}
		}
	// frais de port
	$totalmontant += $facture[prixport] ;
	// frais de gestion
	$totalmontant += $facture[prixgestion] ;
	// selon moyen
	if (in_array("2" , $modules)) // paiements par CB ...
		{
		if ($moyen == "1")
			{
			$DB_site->query("UPDATE facture SET moyenid = '1', valide = '0' WHERE facturid = '$facture[facturid]'");
			$facturid = $facture[facturid] ;
			include ($pagepaiement) ;
			echo "<blockquote><strong>Règelement par Carte Bleue</strong></blockquote>
				<table><tr><td align=\"center\">$pagepaiementcb</td></tr></table>" ;
			}
		}
	if ($moyen != "1")
		{
		$coord_marchand=$DB_site->query_first("SELECT * FROM client where domaine = '$host'");
		if ($moyen != "3")
			{
			$totalmontant = number_format($totalmontant, 2, ',', ' ');
			}
		while (strlen($facture[facturid] < 8))
			{
			$facture[facturid] = $facture[facturid]."0" ;
			}
		if ($moyen == "2") // cheque
			{
			$DB_site2->query(" UPDATE facture SET moyenid = '2', valide = '0' WHERE facturid = '$facture[facturid]' ");
			echo "<blockquote><strong>Règelment par chèque</strong></blockquote>
			<table><tr><td align=\"center\">Veuillez envoyer un chèque de <font color=red size=3>$totalmontant €</font> à :<br><br>
			<font color=red size=3>$coord_marchand[raison_sociale]<br>
			$coord_marchand[adresse]<br>
			$coord_marchand[codepostal] $coord_marchand[ville]</font><br><br>
			En n'oubliant pas d'indiquer au dos la référence de transaction : <font color=red size=3>$facture[facturid]</font>.</td></tr></table>" ;
			$mail=$DB_site->query_first("select mail as email, username, password from client where domaine = '$host'");
			$mailbou=$mail[0];
			$usernamebou=$mail[1];
			$passcodebou=$mail[2];
			$url="http://www.vendre-en-ligne.fr/admin/facture.php?username=$usernamebou&passcode=$passcodebou";
			$mail_contact = $DB_site2->query_first( "SELECT mail_contact FROM parametresgeneraux" );
			$temp = "title$langue";
			$mailhead = "From: $mail_contact[0]\n";
			$mailhead .= "Content-Type: text/html; charset=iso-8859-1\n";
			$htmlmess = "Bonjour,<br>";
			$htmlmess .= "Vous avez reçu une nouvelle <b>commande par chèque.</b><br>";
			$htmlmess .= "Il s'agit de la commande N° $facture[facturid]<br><br>";
			$htmlmess .= "Retrouvez l'ensemble de vos commandes sur votre panneau d'admnistration à l'adresse :<br>";
			$htmlmess .= "<a href=$url>$url</a>";
			//mail( "$mail_contact[0]", "${$temp} : Réception d'une commande par chèque", $htmlmess, $mailhead );
			/* envoi d'un mail par Gmail */
			require_once("./gmail/smtp.php");
			require_once("./gmail/sasl.php");
			$from="arobases@$mondom";
			$smtppass=SMTP_PASSWORD_2;
			$localhost="$mondom";
			$abuse="arobases@$mondom";
			$namefrom="${$title}";
			$to=$mail_contact[0];
			$subject="${$temp} : Réception d'une commande par chèque";
			$message=$htmlmess ;
			gmail($from,$to,$subject,$smtppass,$localhost,$namefrom,$abuse,$message);
			/* fin envoi d'un mail par Gmail */
			}
		if ($moyen == "3") // contre remboursement
			{
			$DB_site2->query("UPDATE facture SET moyenid = '3', valide = '0' WHERE facturid = '$facture[facturid]'");
			$prix_cr=$DB_site2->query_first("select prix_contre_remboursement from parametresgeneraux");
			if ($prix_cr[0] > 0)
				{
				$totalmontant += $prix_cr[0] ;
				$totalmontant = number_format($totalmontant, 2, ',', ' ');
				$DB_site2->query("UPDATE facture SET prix_contre_remboursement = '$prix_cr[0]' WHERE facturid = '$facture[facturid]'");
				}
			echo "<blockquote><strong>Règlement par contre-remboursement</strong></blockquote>
			<table><tr><td align=\"center\">Vous recevrez un colis contre remboursement de <font color=red size=3>$totalmontant €</font> 
			dans les plus brefs délais.</td></tr></table>" ;
			// Envoi d'un mail pour prévenir le webmaster
			$mail_contact = $DB_site2->query_first( "SELECT mail_contact FROM parametresgeneraux" );
			$temp = "title$langue";
			$mailhead = "From: $mail_contact[0]\n";
			$mailhead .= "Content-Type: text/html; charset=iso-8859-1\n";
			$htmlmess = "Bonjour,<br>";
			$htmlmess .= "Vous avez reçu une nouvelle <b>commande par contre-remboursement</b>.<br>";
			$htmlmess .= "Il s'agit de la commande N° $facture[facturid]<br><br>";
			$htmlmess .= "Retrouvez l'ensemble des commandes sur votre panneau d'admnistration à l'adresse :<br>";
			$htmlmess .= "<a href=http://www.vendre-en-ligne.fr/admin/facture.php>http://www.vendre-en-ligne.fr/admin/facture.php</a>";
			//mail( "$mail_contact[0]", "${$temp} : Réception d'une commande par contre-remboursement", $htmlmess, $mailhead );
			/* envoi d'un mail par Gmail */
			require_once("./gmail/smtp.php");
			require_once("./gmail/sasl.php");
			$from="arobases@$mondom";
			$smtppass=SMTP_PASSWORD_2;
			$localhost="$mondom";
			$abuse="arobases@$mondom";
			$namefrom="${$title}";
			$to=$mail_contact[0];
			$subject="${$temp} : Réception d'une commande par contre-remboursement";
			$message=$htmlmess ;
			gmail($from,$to,$subject,$smtppass,$localhost,$namefrom,$abuse,$message);
			/* fin envoi d'un mail par Gmail */
			}
		if ($moyen == "4") // mandat postal
			{
			$DB_site2->query("UPDATE facture SET moyenid = '4', valide = '0' WHERE facturid = '$facture[facturid]'");
			echo "<blockquote><strong>Règlement par mandat postal</strong></blockquote>
			<table><tr><td align=\"center\">Veuillez envoyer un mandat postal de <font color=red size=3>$totalmontant €</font> 
			à :<br><br><font color=red size=3>$coord_marchand[raison_sociale]<br>$coord_marchand[adresse]<br>
			$coord_marchand[codepostal] $coord_marchand[ville]</font><br><br>En n'oubliant pas d'indiquer au dos la référence de transaction : 
			<font color=red size=3>$facture[facturid]</font>.</td></tr></table>
			<a href=\"commande.php\">Retour</a>" ;
			// Envoi d'un mail pour prévenir le webmaster
			$mail_contact = $DB_site2->query_first( "SELECT mail_contact FROM parametresgeneraux" );
			$temp = "title$langue";
			$mailhead = "From: $mail_contact[0]\n";
			$mailhead .= "Content-Type: text/html; charset=iso-8859-1\n";
			$htmlmess = "Bonjour,<br>";
			$htmlmess .= "Vous avez reçu une nouvelle <b>commande par mandat postal</b>.<br>";
			$htmlmess .= "Il s'agit de la commande N° $facture[facturid]<br><br>";
			$htmlmess .= "Retrouvez l'ensemble des commandes sur votre panneau d'admnistration à l'adresse :<br>";
			$htmlmess .= "<a href=http://www.vendre-en-ligne.fr/admin/facture.php>http://www.vendre-en-ligne.fr/admin/facture.php</a>";
			//mail( "$mail_contact[0]", "${$temp} : Réception d'une commande par mandat postal", $htmlmess, $mailhead );
			/* envoi d'un mail par Gmail */
			require_once("./gmail/smtp.php");
			require_once("./gmail/sasl.php");
			$from="arobases@$mondom";
			$smtppass=SMTP_PASSWORD_2;
			$localhost="$mondom";
			$abuse="arobases@$mondom";
			$namefrom="${$title}";
			$to=$mail_contact[0];
			$subject="${$temp} : Réception d'une commande par mandat postal";
			$message=$htmlmess ;
			gmail($from,$to,$subject,$smtppass,$localhost,$namefrom,$abuse,$message);
			/* fin envoi d'un mail par Gmail */
			}
		if ($moyen == "5") // virement
			{
			$DB_site->query("UPDATE facture SET moyenid = '5', valide = '0' WHERE facturid = '$facture[facturid]'");
			$IBAN1 = substr($coord_marchand[IBAN], 0, 4) ;
			$IBAN2 = substr($coord_marchand[IBAN], 4, 4) ;
			$IBAN3 = substr($coord_marchand[IBAN], 8, 4) ;
			$IBAN4 = substr($coord_marchand[IBAN], 12, 4) ;
			$IBAN5 = substr($coord_marchand[IBAN], 16, 4) ;
			$IBAN6 = substr($coord_marchand[IBAN], 20, 4) ;
			$IBAN7 = substr($coord_marchand[IBAN], 24, 3) ;
			echo "<blockquote><strong>Règlement par virement bancaire</strong></blockquote>
			<table><tr><td align=\"center\">Veuillez réaliser un virement de <font color=red size=3>$totalmontant €</font>.<br><br>
			Utilisez les coordonnées bancaires ci-dessous :<br><br>Depuis la France <font color=red size=3> $coord_marchand[RIBetab] | $coord_marchand[RIBguichet] | $coord_marchand[RIBcompte] | $coord_marchand[RIBrice]</font><br><br>
			Depuis l'étranger<i>IBAN</i> : <font color=red size=3>$IBAN1 | $IBAN2 | $IBAN3 | $IBAN4 | $IBAN5 | $IBAN6 | $IBAN7</font><br>
			<i>BIC</i> : <font color=red size=3>$coord_marchand[BIC]</font><br><br>
			Notez bien la référence de la transaction : <font color=red size=3>$facture[facturid]</font>.</td></tr></table>
			<a href=\"commande.php\">Retour</a>" ;
			// Envoi d'un mail pour prévenir le webmaster
			$mail_contact = $DB_site2->query_first("SELECT mail_contact FROM parametresgeneraux");
			$temp = "title$langue";
			$mailhead = "From: $mail_contact[0]\n";
			$mailhead .= "Content-Type: text/html; charset=iso-8859-1\n";
			$htmlmess = "Bonjour,<br>";
			$htmlmess .= "Vous avez reçu une nouvelle <b>commande par virement</b>.<br>";
			$htmlmess .= "Il s'agit de la commande N° $facture[facturid]<br><br>";
			$htmlmess .= "Retrouvez l'ensemble des commandes sur votre panneau d'admnistration à l'adresse :<br>";
			$htmlmess .= "<a href=http://www.vendre-en-ligne.fr/admin/facture.php>http://www.vendre-en-ligne.fr/admin/facture.php</a>";
			//mail( "$mail_contact[0]", "${$temp} : Réception d'une commande par virement", $htmlmess, $mailhead );
			/* envoi d'un mail par Gmail */
			require_once("./gmail/smtp.php");
			require_once("./gmail/sasl.php");
			$from="arobases@$mondom";
			$smtppass=SMTP_PASSWORD_2;
			$localhost="$mondom";
			$abuse="arobases@$mondom";
			$namefrom="${$title}";
			$to=$mail_contact[0];
			$subject="${$temp} : Réception d'une commande par virement";
			$message=$htmlmess ;
			gmail($from,$to,$subject,$smtppass,$localhost,$namefrom,$abuse,$message);
			/* fin envoi d'un mail par Gmail */
			}
		if ($moyen == "6") // paypal
			{
			$DB_site2->query("UPDATE facture SET moyenid = '6', valide = '0' WHERE facturid = '$facture[facturid]'");
			include ($pagepaiement);
			echo "<blockquote><strong>Règlement par Paypal</strong></blockquote>
					$pagepaiementpaypal
				<a href=\"commande.php\">Retour</a>" ;
			// Envoi d'un mail pour prévenir le webmaster
			$mail_contact = $DB_site2->query_first("SELECT mail_contact FROM parametresgeneraux" );
			$temp = "title$langue";
			$mailhead = "From: $mail_contact[0]\n";
			$mailhead .= "Content-Type: text/html; charset=iso-8859-1\n";
			$htmlmess = "Bonjour,<br>";
			$htmlmess .= "Vous avez reçu une nouvelle <b>commande par paypal</b>.<br>";
			$htmlmess .= "Il s'agit de la commande N° $facture[facturid]<br><br>";
			$htmlmess .= "Retrouvez l'ensemble des commandes sur votre panneau d'admnistration à l'adresse :<br>";
			$htmlmess .= "<a href=http://www.vendre-en-ligne.fr/admin/facture.php>http://www.vendre-en-ligne.fr/admin/facture.php</a>";
			//mail( "$mail_contact[0]", "${$temp} : Réception d'une commande par paypal", $htmlmess, $mailhead );
			/* envoi d'un mail par Gmail */
			require_once("./gmail/smtp.php");
			require_once("./gmail/sasl.php");
			$from="arobases@$mondom";
			$smtppass=SMTP_PASSWORD_2;
			$localhost="$mondom";
			$abuse="arobases@$mondom";
			$namefrom="${$title}";
			$to=$mail_contact[0];
			$subject="${$temp} : Réception d'une commande par paypal";
			$message=$htmlmess ;
			gmail($from,$to,$subject,$smtppass,$localhost,$namefrom,$abuse,$message);
			/* fin envoi d'un mail par Gmail */
			}
		if ($moyen == "7") // espèces
			{
			$DB_site2->query("UPDATE facture SET moyenid = '7', valide = '1', prixport = 0 WHERE facturid = '$facture[facturid]'");
			echo "<blockquote><strong>Règlement en espèces</strong></blockquote>
			<br><bR>
			Commande validée.<br><br>
			<a href=\"commande.php\">Retour</a>" ;
			// Envoi d'un mail pour prévenir le webmaster
			$mail_contact = $DB_site2->query_first("SELECT mail_contact FROM parametresgeneraux" );
			$temp = "title$langue";
			$mailhead = "From: $mail_contact[0]\n";
			$mailhead .= "Content-Type: text/html; charset=iso-8859-1\n";
			$htmlmess = "Bonjour,<br>";
			$htmlmess .= "Vous avez reçu une nouvelle <b>commande en espèces</b>.<br>";
			$htmlmess .= "Il s'agit de la commande N° $facture[facturid]<br><br>";
			$htmlmess .= "Retrouvez l'ensemble des commandes sur votre panneau d'admnistration à l'adresse :<br>";
			$htmlmess .= "<a href=http://www.vendre-en-ligne.fr/admin/facture.php>http://www.vendre-en-ligne.fr/admin/facture.php</a>";
			//mail( "$mail_contact[0]", "${$temp} : Réception d'une commande en espèces", $htmlmess, $mailhead );
			/* envoi d'un mail par Gmail */
			require_once("./gmail/smtp.php");
			require_once("./gmail/sasl.php");
			$from="arobases@$mondom";
			$smtppass=SMTP_PASSWORD_2;
			$localhost="$mondom";
			$abuse="arobases@$mondom";
			$namefrom="${$title}";
			$to=$mail_contact[0];
			$subject="${$temp} : Réception d'une commande en espèces";
			$message=$htmlmess ;
			gmail($from,$to,$subject,$smtppass,$localhost,$namefrom,$abuse,$message);
			/* fin envoi d'un mail par Gmail */
			}
		}
	}

// insertion dans facture
// choix du moyen de paiement
if (isset($action) and $action == "etape4")
	{
	if (isset($qte) and $qte > 0)
		{
		$article_nom=$DB_site2->query_first("SELECT libelleFR FROM article where artid = '$artid'");
		$texte .= "<br>Article $article_nom[0] => Qté = $qte ; " ;
		// insertion dans le panier
		$datepanier = date('Y-m-d') ;
		$panier=$DB_site2->query_first("SELECT * FROM panier where sessionid = '$s'");
		$temppanierid = $panier[panierid] ;
		$DB_site2->query("INSERT IGNORE INTO panier (sessionid, datepanier, prixtva, prixgestion, affilieid) VALUES ('$s', '$datepanier', 0, 0, 0)");
		if ($temppanierid == "") 
			{
			$temppanierid = $DB_site2->insert_id() ;
			}
		$DB_site2->query("INSERT INTO lignepanier (panierid, artid, qte) VALUES ('$temppanierid', '$artid', '$qte')") ;
		$templignepanierid = $DB_site2->insert_id() ;
		// Caractéristiques
		$difference_de_prix = 0 ;
		foreach ($_POST as $cle=>$valeur)
			{
			if (substr($cle, 0, 4) == "lst_")
				{   // Pour chaque zone de liste on met la valeur séléctionnée dans lignepaniercaracteristique
				$DB_site2->query("INSERT INTO lignepaniercaracteristique (lignepanierid, caractvalid)
								  VALUES ('$templignepanierid', '$valeur')");
				$valeurs=$DB_site2->query_first("select cv.libelleFR as libcv, c.libelleFR as libc from caracteristique c, 
												caracteristiquevaleur cv where c.caractid = cv.caractid and cv.caractvalid = '$valeur'");
				$texte .= "$valeurs[libc] = $valeurs[libcv] ; " ;
				$diff=$DB_site2->query_first("select differenceprix from article_caractval where artid = '$artid' and caractvalid = '$valeur'");
				$difference_de_prix += $diff[differenceprix] ;
				}
			}
		$datejour=date("Y-m-d") ;
		$articlepromo=$DB_site2->query_first("SELECT * FROM promotion WHERE artid = '$artid' and datedebut <= '$datejour' and datefin > '$datejour'");
		if ($articlepromo[promotionid] != "")
			{
			$prix_article = $articlepromo[prix] ;
			}
		else
			{
			$px_article=$DB_site2->query_first("SELECT prix FROM article WHERE artid = '$artid'");
			$prix_article = $px_article[prix] ;
			}
		$prix_article += $difference_de_prix ;
		$texte .= "Prix = $prix_article € ; " ;
		// champs
		$champs=$DB_site2->query("select * from article_champ where artid = '$artid'") ;
		$message = "" ;
		$erreur = "" ;
		while ($champ=$DB_site2->fetch_array($champs))
			{
			$nom_champ = $champ[nom] ;
			if ($champ[obligatoire] == '1' and ${$nom_champ} == "")
				{
				$erreur .= "Le champ <strong>$champ[libelle]</strong> est obligatoire.<br>" ;
				}
			elseif (${$nom_champ} != "")
				{
				$texte .= "$champ[nom] = ${$nom_champ} ; " ;
				switch ($champ[type])
					{
					case "1": // boîte texte
						$DB_site2->query("insert into lignepanierchamp (lignepanierid, articlechampid, valeur) 
						values ('$templignepanierid', '$champ[articlechampid]', '${$nom_champ}')") ;
						break ;
					case "2": // Boutons radio
						$valeur=$DB_site2->query_first("select description from article_champ_valeur where articlechampid = '$champ[articlechampid]' and valeur = '${$nom_champ}'") ;
						$DB_site2->query("insert into lignepanierchamp (lignepanierid, articlechampid, valeur) 
						values ('$templignepanierid', '$champ[articlechampid]', '$valeur[description]')") ;
						break ;
					case "3": // Cases à cocher
						$i = 0 ;
						$tab = ${$nom_champ} ;
						while ($i < count($tab))
							{
							$valeur=$DB_site2->query_first("select description from article_champ_valeur where articlechampid = '$champ[articlechampid]' and valeur = '$tab[$i]'") ;
							$DB_site2->query("insert into lignepanierchamp (lignepanierid, articlechampid, valeur) 
							values ('$templignepanierid', '$champ[articlechampid]', '$valeur[description]')") ;
							$i++ ;
							}
						break ;
					case "4": // Champ texte
						$DB_site2->query("insert into lignepanierchamp (lignepanierid, articlechampid, valeur) 
						values ('$templignepanierid', '$champ[articlechampid]', '${$nom_champ}')") ;
						break ;
					case "5": // Liste à sélection unique
						$valeur=$DB_site2->query_first("select description from article_champ_valeur where articlechampid = '$champ[articlechampid]' and valeur = '${$nom_champ}'") ;
						$DB_site2->query("insert into lignepanierchamp (lignepanierid, articlechampid, valeur) 
						values ('$templignepanierid', '$champ[articlechampid]', '$valeur[description]')") ;
						break ;
					case "6": // Liste à sélections multiples
						$i = 0 ;
						$tab = ${$nom_champ} ;
						while ($i < count($tab))
							{
							$valeur=$DB_site2->query_first("select description from article_champ_valeur where articlechampid = '$champ[articlechampid]' and valeur = '$tab[$i]'") ;
							$DB_site2->query("insert into lignepanierchamp (lignepanierid, articlechampid, valeur) 
							values ('$templignepanierid', '$champ[articlechampid]', '$valeur[description]')") ;
							$i++ ;
							}
						break ;
					case "7": // Insertion de fichier
						if ($_FILES[$nom_champ]['name'] != "") 
							{
							if ($_FILES[$nom_champ]['error'] == 1) 
								{
								$msg .= "Le fichier téléchargé excède la taille autorisée par le serveur ( > 2Mb)";
								}
							elseif ($_FILES[$nom_champ]['error'] == 2) 
								{
								$msg .= "Ereur inconnue ...";
								}
							elseif ($_FILES[$nom_champ]['error'] == 3) 
								{
								$msg .= "Le fichier n'a été que partiellement téléchargé.";
								}
							elseif ($_FILES[$nom_champ]['error'] == 4) 
								{
								$msg .= "Aucun fichier n'a été téléchargé.";
								}
							elseif ($_FILES[$nom_champ]['type'] == "application/x-msdos-program" or $_FILES[$nom_champ]['type'] == "application/octet-stream" or $_FILES[$nom_champ]['type'] == "application/x-msdownload") 
								{
								$msg .= "Le type du fichier téléchargé n'est pas autorisé.";
								}
							else // ok
								{
								switch ($_FILES[$nom_champ]['type'])  // définition de l'extension
									{
									case "audio/scpls":
										$file = "pls" ;
										break;
									case "audio/x-mpegurl":
										$file = "m3u" ;
										break;
									case "application/x-shockwave-flash":
										$file = "swf" ;
										break;
									case "application/postscript":
										$file = "eps" ;
										break;
									case "application/msword":
										$file = "doc" ;
										break;
									case "application/msword":
										$file = "rtf" ;
										break;
									case "application/excel":
										$file = "xls" ;
										break;
									case "application/vnd.ms-powerpoint":
										$file = "pps" ;
										break;
									case "application/pdf":
										$file = "pdf" ;
										break;
									case "application/x-gzip":
										$file = "gzip" ;
										break;
									case "application/acad":
										$file = "dwg" ;
										break;
									case "application/clariscad":
										$file = "ccad" ;
										break;
									case "application/drafting":
										$file = "drw" ;
									   break;
									case "application/dxf":
										$file = "dxf" ;
									   break;
									case "application/i-deas":
										$file = "unv" ;
									   break;
									case "application/iges":
										$file = "igs" ;
									   break;
									case "application/oda":
										$file = "oda" ;
									   break;
									case "application/pdf":
										$file = "dwg" ;
									   break;
									case "application/postscript":
										$file = "ai" ;
									   break;
									case "application/pro":
										$file = "prt" ;
									   break;
									case "application/rtf":
										$file = "rtf" ;
									   break;
									case "application/set":
										$file = "set" ;
									   break;
									case "application/sla":
										$file = "stl" ;
									   break;
									case "application/solids":
										$file = "dwg" ;
									   break;
									case "application/step":
										$file = "step" ;
									   break;
									case "application/vda":
										$file = "vda" ;
									   break;
									case "application/x-mif":
										$file = "mif" ;
									   break;
									case "application/x-csh":
										$file = "dwg" ;
									   break;
									case "application/x-dvi":
										$file = "dvi" ;
									   break;
									case "application/hdf":
										$file = "hdf" ;
									   break;
									case "application/x-latex":
										$file = "latex" ;
									   break;
									case "application/x-netcdf":
										$file = "nc" ;
									   break;
									case "application/x-sh":
										$file = "dwg" ;
									   break;
									case "application/x-tcl":
										$file = "tcl" ;
									   break;
									case "application/x-tex":
										$file = "tex" ;
									   break;
									case "application/x-texinfo":
										$file = "texi" ;
									   break;
									case "application/x-troff":
										$file = "troff" ;
									   break;
									case "application/x-troff-man":
										$file = "man" ;
									   break;
									case "application/x-troff-me":
										$file = "me" ;
									   break;
									case "application/x-troff-ms":
										$file = "ms" ;
									   break;
									case "application/x-wais-source":
										$file = "src" ;
									   break;
									case "application/x-bcpio":
										$file = "bcpio" ;
									   break;
									case "application/x-cpio":
										$file = "cpio" ;
									   break;
									case "application/x-gtar":
										$file = "gtar" ;
									   break;
									case "application/x-shar":
										$file = "shar" ;
									   break;
									case "application/x-sv4cpio":
										$file = "sv4cpio" ;
									   break;
									case "application/x-sv4crc":
										$file = "sc4crc" ;
									   break;
									case "application/x-tar":
										$file = "tar" ;
									   break;
									case "application/x-ustar":
										$file = "tar" ;
									   break;
									case "application/zip":
										$file = "ZIP" ;
									   break;
									case "application/x-bzip":
										$file = "bz2" ;
									   break;
									case "audio/vnd.rn-realaudio":
										$file = "ra" ;
									   break;
									case "audio/x-pn-realaudio":
										$file = "ram" ;
									   break;
									case "application/vnd.rn-realmedia":
										$file = "rm" ;
									   break;
									case "video/vnd.rn-realvideo":
										$file = "rv" ;
									   break;
									case "audio/wav":
										$file = "wav" ;
									   break;
									case "audio/ogg":
										$file = "ogg" ;
									   break;
									case "audio/mpeg":
										$file = "mp3" ;
									   break;
									case "audio/aiff":
										$file = "aif" ;
										break;
									case "audio/mid":
										$file = "midi" ;
										break;
									case "audio/basic":
										$file = "snd" ;
									   break;
									case "audio/x-aiff":
										$file = "aif" ;
									   break;
									case "audio/x-wav":
										$file = "wav" ;
									   break;
									case "image/psd":
										$file = "psd" ;
									   break;
									case "image/png":
										$file = "png" ;
									   break;
									case "image/bmp":
										$file = "bmp" ;
									   break;
									case "image/gif":
										$file = "gif" ;
									   break;
									case "image/ief":
										$file = "ief" ;
									   break;
									case "image/jpeg":
										$file = "jpg" ;
									   break;
									case "image/pjpeg":
										$file = "jpg" ;
									   break;
									case "image/tiff":
										$file = "tiff" ;
									   break;
									case "image/x-cmu-raster":
										$file = "cmu" ;
									   break;
									case "image/x-portable-anymap":
										$file = "pnm" ;
									   break;
									case "image/x-portable-bitmap":
										$file = "pbm" ;
									   break;
									case "image/x-portable-graymap":
										$file = "pgm" ;
									   break;
									case "image/x-portable-pixmap":
										$file = "ppm" ;
									   break;
									case "image/x-rgb":
										$file = "rgb" ;
									   break;
									case "image/x-xbitmap":
										$file = "xbm" ;
									   break;
									case "image/x-xpixmap":
										$file = "xpm" ;
									   break;
									case "image/x-xwindowdump":
										$file = "dump" ;
									   break;
									case "multipart/x-zip":
										$file = "zip" ;
									   break;
									case "multipart/x-gzip":
										$file = "gzip" ;
									   break;
									case "text/xml":
										$file = "xml" ;
									   break;
									case "text/xsl":
										$file = "xsl" ;
									   break;
									case "text/html":
										$file = "html" ;
									   break;
									case "text/plain":
										$file = "txt" ;
									   break;
									case "text/richtext":
										$file = "rtx" ;
									   break;
									case "text/tab-separated-value":
										$file = "tsv" ;
									   break;
									case "text/x-setext":
										$file = "etx" ;
									   break;
									case "text/css":
										$file = "css" ;
									   break;
									case "video/x-ms-wmv":
										$file = "wmv" ;
									   break;
									case "video/x-ms-wmx":
										$file = "wmx" ;
									   break;

									case "video/avi":
										$file = "avi" ;
									   break;
									case "video/x-ms-asf":
										$file = "asx" ;
									   break;
									case "video/mpeg":
										$file = "mpeg" ;
									   break;
									case "video/quicktime":
										$file = "mov" ;
									   break;
									case "video/msvideo":
										$file = "avi" ;
									   break;
									case "video/x-sgi-movie":
										$file = "movie" ;
									   break;
									}
								$DB_site2->query("insert into lignepanierchamp (lignepanierid, articlechampid, valeur) 
								values ('$templignepanierid', '$champ[articlechampid]', '$file')") ;
								$insertid=$DB_site2->insert_id() ;
								$nom_fic="/home/boutique/www/configurations/$host/images/uploads/".$insertid.".".$file ;
								echo "copier_image($nom_fic,\"$nom_champ\")" ;
								copier_image($nom_fic,"$nom_champ") ;
								}
							}
						break ;
					}
				}
			}
		if ($erreur != "")
			{ // on vide le panier
			$panier=$DB_site2->query_first("SELECT * FROM panier where sessionid = '$s'");
			if ($panier[panierid] != "")
				{
				$DB_site2->query("DELETE FROM lignepanier WHERE lignepanierid = '$templignepanierid'");
				$DB_site2->query("DELETE FROM lignepaniercaracteristique WHERE lignepanierid = '$templignepanierid'");
				$DB_site2->query("DELETE FROM lignepanierchamp WHERE lignepanierid = '$templignepanierid'");
				$lignepaniercount=$DB_site2->query_first("SELECT count(*) FROM lignepanier WHERE panierid = '$panier[panierid]'");
				if ($lignepaniercount[0] == 0)
					{
					$DB_site2->query("DELETE FROM panier WHERE panierid = '$panier[panierid]'");
					} 
				}
			$erreur = "<br><font class=\"cost\">$erreur</font><br>" ;
			$action = "etape3" ;
			}
		else
			{
			switch ($action2)
				{
				case 'autre':
					$action = "etape2" ;
					break ;
				case 'fin': // équivaut à cde2.php
					// frais de gestion
					$virgule = "";
					if (is_array($fraisgestionid) and count($fraisgestionid) > 0)
						{
						while (list($key, $val) = each($fraisgestionid)) 
							{
							if ($val!=0) 
								{
								$fraisgestionidtmp.="$virgule $val";
								$virgule=",";
								}
							}
						}
					if ($fraisgestionidtmp!="")
						{
						$fraisgestions=$DB_site2->query_first("SELECT MAX(prix) FROM fraisgestion WHERE fraisgestionid IN ($fraisgestionidtmp)");
						$fraisgestion=$fraisgestions[0];
						} 
					else 
						{
						$fraisgestion=0;
						}
					$fraisgestions=$DB_site2->query_first("SELECT prixgestion, prixtva FROM panier WHERE sessionid = '$s'");
					if ($fraisgestions[prixgestion] == $fraisgestion and $fraisgestions[prixtva] == $prixtva)
						{
						$fraisgestion = $fraisgestions[prixgestion] ;
						$prixtva = $fraisgestions[prixtva] ;
						}
					else
						{
						$DB_site2->query("UPDATE panier SET prixgestion = '$fraisgestion', prixtva = '$prixtva' WHERE sessionid = '$s'");
						}
					$poidtotal = 0 ;
					// infos user
					$user_info=$DB_site2->query_first("SELECT * FROM  utilisateur WHERE userid = '$clientid'");
					$user_info[nom] = addslashes($user_info[nom]) ;
					$user_info[prenom] = addslashes($user_info[prenom]) ;
					$user_info[codepostal] = addslashes($user_info[codepostal]) ;
					$user_info[ville] = addslashes($user_info[ville]) ;
					$user_info[adresse] = addslashes($user_info[adresse]) ;
					$user_info[raisonsociale] = addslashes($user_info[raisonsociale]) ;
					$user_info[telephone] = addslashes($user_info[telephone]) ;
					// taux de TVA
					$taux=$DB_site2->query_first("SELECT port, gestion, TVAtauxnormal FROM pays WHERE paysid = '$user_info[paysid]'");
					if ($taux[port] == '0')
						{
						$tauxport = 0 ;
						}
					else
						{
						$tauxport = $taux[TVAtauxnormal] ;
						}
					if ($taux[gestion] == '0')
						{
						$tauxgestion = 0 ;
						}
					else
						{
						$tauxgestion = $taux[TVAtauxnormal] ;
						}
					// insertion dans facture
					$DB_site2->query("INSERT INTO facture (userid, datefacture, prixport, civilite, nom, prenom, codepostal, adresse, ville,
					telephone, paysid, raisonsociale, prixgestion, tvaport, tvagestion, mail, langue) 
					VALUES ('$user_info[userid]', NOW(), '0', '$user_info[civilite]', '$user_info[nom]', '$user_info[prenom]',
					'$user_info[codepostal]', '$user_info[adresse]', '$user_info[ville]', '$user_info[telephone]', '$user_info[paysid]',
					'$user_info[raisonsociale]', '$fraisgestion', '$tauxport', '$tauxgestion', '$user_info[mail]', '$langue')");
					$factureid=$DB_site2->insert_id();
					// transfert de panier vers facture
					$panier=$DB_site2->query_first("SELECT * FROM panier where sessionid = '$s'");
					if ($panier[panierid] != "")
						{
						$TemplateCde2Bit = "" ;
						$lignepaniertmps=$DB_site2->query("SELECT l.qte, a.artid, a.libelleFR, a.libelleDE, a.libelleEN, a.libelleIT, 
						a.libelleSP, a.prix , a.artcode , a.poid , a.tva, a.tauxchoisi, a.catid, p.promotionid, l.lignepanierid 
						FROM lignepanier l INNER JOIN article a on (a.artid = l.artid) LEFT OUTER JOIN promotion p ON (a.artid = p.artid) 
						WHERE panierid = '$panier[panierid]' ORDER BY l.lignepanierid ASC" );
						$taux=$DB_site2->query_first("SELECT TVAtauxnormal, TVAtauxreduit FROM pays WHERE paysid = '$user_info[paysid]'");
						while ($lignepaniertmp=$DB_site2->fetch_array($lignepaniertmps))
							{
							if ($lignepaniertmp[promotionid] != NULL)
								{	
								$rs_promo=$DB_site2->query_first("SELECT prix FROM promotion WHERE promotionid = '$lignepaniertmp[promotionid]'
														AND datedebut <= '" .date( "Y-m-d" ). "' AND datefin > '" .date( "Y-m-d" ). "'");
								if ($rs_promo[prix] != "")
									{	// Article en cours de Promotion
									$lignepaniertmp[prix] = $rs_promo[prix];
									}
								}
							$poidtotal += $lignepaniertmp[9] * $lignepaniertmp[0] ;
							$lignepaniertmp[2] = addslashes($lignepaniertmp[2]) ;
							if ($lignepaniertmp[tauxchoisi] == '1')
								{
								$tauxarticle = $taux[TVAtauxnormal] ;
								}
							elseif ($lignepaniertmp[tauxchoisi] == '2')
								{
								$tauxarticle = $taux[TVAtauxreduit] ;
								}
							else
								{
								$tauxarticle = 0 ;
								}
							$DB_site2->query("INSERT IGNORE INTO lignefacture (factureid, artid, catid, artcode, qte, libelle, prix, tva) 
							VALUES ('$factureid', '$lignepaniertmp[artid]', '$lignepaniertmp[catid]', '$lignepaniertmp[8]', 
							'$lignepaniertmp[0]', '$lignepaniertmp[2]', '$lignepaniertmp[prix]', '$tauxarticle')");
							$lignefactureid=$DB_site2->insert_id();
							// Caractéristiques //
							$rq_lignepaniercaracteristique=$DB_site2->query("SELECT c.$libelle as libelle_caract, cv.$libelle as libelle_caractval,
							lpc.caractvalid FROM lignepaniercaracteristique lpc INNER JOIN caracteristiquevaleur cv 
							ON (lpc.caractvalid = cv.caractvalid) INNER JOIN caracteristique as c ON (cv.caractid = c.caractid)
							WHERE lignepanierid = '$lignepaniertmp[lignepanierid]'");
							$cde2_caracteristique = "" ;
							while ($rs_lignepaniercaracteristique=$DB_site2->fetch_array($rq_lignepaniercaracteristique))
								{	
								$rs_lignepaniercaracteristique[libelle_caract] = addslashes($rs_lignepaniercaracteristique[libelle_caract]) ;
								$rs_lignepaniercaracteristique[libelle_caractval] = addslashes($rs_lignepaniercaracteristique[libelle_caractval]) ;
								$cde2_caracteristique .= "$rs_lignepaniercaracteristique[libelle_caract] : $rs_lignepaniercaracteristique[libelle_caractval]<br>" ;
								$diff=$DB_site2->query_first("select differenceprix from article_caractval where caractvalid = 
								'$rs_lignepaniercaracteristique[caractvalid]' and artid = '$lignepaniertmp[artid]'") ;
								if ($diff[differenceprix] != 0)
									{
									$DB_site2->query("UPDATE lignefacture set prix = prix + '$diff[differenceprix]' where lignefactureid = '$lignefactureid'");
									$lignepaniertmp[prix] += $diff[differenceprix] ;
									}
								$DB_site2->query("INSERT IGNORE INTO lignefacturecaracteristique (lignefactureid, libelle_caract, 
								libelle_caractval) VALUES ('$lignefactureid', '$rs_lignepaniercaracteristique[libelle_caract]', 
								'$rs_lignepaniercaracteristique[libelle_caractval]')");				
								}	
							if (in_array ("113", $modules)) // infos saisies
								{ 
								$champs=$DB_site2->query("select * from article_champ where artid = '$lignepaniertmp[artid]'") ;
								if ($DB_site2->num_rows($champs) > 0)
									{
									$champsaisis=$DB_site2->query("select * from lignepanierchamp where lignepanierid = '$lignepaniertmp[lignepanierid]'") ;
									while ($champaisi=$DB_site2->fetch_array($champsaisis))
										{
										$DB_site2->query("insert into lignefacturechamp (lignefactureid, articlechampid, valeur) 
										values ('$lignefactureid', '$champaisi[articlechampid]', '$champaisi[valeur]')") ;
										$insertid=$DB_site2->insert_id() ;
										$monchamp=$DB_site2->query_first("select lignepanierchampid from article_champ ac, lignepanierchamp lpc
										where lpc.articlechampid = '$champaisi[articlechampid]' and lignepanierid = '$lignepaniertmp[lignepanierid]' and ac.articlechampid = lpc.articlechampid and type = '7'") ;
										if ($monchamp[0] != "")
											{
										echo "fslm" ;
											$folder = "/home/boutique/www/configurations/$host/images/uploads" ;
											$fichier = $monchamp[0].".".$champaisi[valeur] ;
											echo "<bR> $folder"."/"."$fichier" ;
											$fichier2 = $insertid.".".$champaisi[valeur] ;
											if (file_exists($folder."/".$fichier)) // article
												{
												rename ($folder."/".$fichier, $folder."/".$fichier2) ;
												}
											}
										}
									}
								}
							$prix_de_mon_article=$DB_site2->query_first("select prix*qte from lignefacture where lignefactureid = '$lignefactureid'");
							$prixligne = $prix_de_mon_article[0] ;
							$prixtotalpanier += $prixligne ;
							$prixtvaligne = $prixligne - ($prixligne / (1 + ($tauxarticle/100))) ;
							$prixtva += $prixtvaligne ;
							$lignepaniertmp[prix] = number_format($lignepaniertmp[prix], 2, ',', ' ');
							$prixligne = number_format($prixligne, 2, ',', ' ');
							$TemplateCde2Bit.="
							<tr>
								<td class=\"td_alt$i\">$lignepaniertmp[$libelle]</td>
								<td class=\"td_alt$i\" align=\"center\">$cde2_caracteristique</td>
								<td class=\"td_alt$i\" align=\"center\">$lignepaniertmp[prix]</td>
								<td class=\"td_alt$i\" align=\"center\">$lignepaniertmp[qte]</td>
								<td class=\"td_alt$i\" align=\"right\">$prixligne €</td>
							</tr>";
							}
						}
					// Adresse de livraison 
					$rs_facture_coordonnees=$DB_site2->query_first("SELECT raisonsociale, nom, prenom, adresse, codepostal, ville, p.paysid,
																   p.libelle FROM facture f INNER JOIN pays p ON (f.paysid = p.paysid)
																   WHERE userid = '$user_info[userid]' AND facturid = '$factureid'" );
					// frais de port		
					$mini_gratuit=$DB_site2->query_first("SELECT  minimum_frais_port_gratuit FROM parametresgeneraux") ;
					$minimum_frais_port_gratuit = $mini_gratuit[minimum_frais_port_gratuit] ;
					if (($minimum_frais_port_gratuit != '-1') and ($prixtotalpanier >= $minimum_frais_port_gratuit)) // si on paie pas de frais de port !!
						{
						$fraisport = " : Gratuit" ;
						$DB_site2->query("UPDATE facture SET prixport = '0' WHERE facturid = '$factureid'");
						$prix_contre_remboursement=$DB_site2->query_first("SELECT prix_contre_remboursement FROM parametresgeneraux");
						$pays[libelle] = "Gratuit : Total > $minimum_frais_port_gratuit €";
						$fraisport[prix] = "0";
						}
					else // on fait tout le traitement des frais de port !!
						{
						$fraisport=$DB_site2->query_first("SELECT * FROM fraisport WHERE poiddebut <= '$poidtotal' AND poidfin >= '$poidtotal' 
															and paysid = '$rs_facture_coordonnees[paysid]'");
						if ($fraisport[0] == "")
							{	// On ne livre pas dans ce pays
							$pays[libelle] = "en sus";
							$fraisport[prix] = "0";
							$pasdefraisdeport = "oui" ;
							}
						else
							{
							$pays=$DB_site2->query_first("SELECT * FROM pays WHERE paysid = '$rs_facture_coordonnees[paysid]'");
							$DB_site2->query("UPDATE facture SET prixport = '$fraisport[prix]' WHERE facturid = '$factureid'");
							$prixtotalpanier += $fraisport[prix];
							$prix_contre_remboursement=$DB_site2->query_first("SELECT prix_contre_remboursement FROM parametresgeneraux");
							$fraisport[prix] = number_format($fraisport[prix], 2, ',', ' ');
							}
						}
					// moyens de paiement	
					$moyens_affiches = "" ;
					if (!isset($pasdefraisdeport))
						{
						$moyens_paiement=$DB_site2->query("select * from moyenpaiement where accepte = '1'");
						if ($DB_site2->num_rows($moyens_paiement) > 0)
							{
							while ($moyen_paiement=$DB_site2->fetch_array($moyens_paiement))
								{
								if (in_array("2", $modules) and $moyen_paiement[moyenid] == '1')
									{
									$mtt_max=$DB_site2->query_first("select montant_max_cb from parametresgeneraux");
									if ($mtt_max[montant_max_cb] == '0' or ($mtt_max[montant_max_cb] != '0' and $prixtotalpanier_point < $mtt_max[montant_max_cb])) // pas de montant max
										{
										$moyens_affiches .= "<tr><td><input type=\"radio\" name=\"moyen\" value=\"$moyen_paiement[moyenid]\"> $moyen_paiement[libelleFR]</td></tr>" ;
										}
									}
								else
									{
									if ($moyen_paiement[moyenid] == '6') //paypal
										{
										$paypal_gros_mtts=$DB_site2->query_first("select paypal_sup from parametresgeneraux");
										if ($paypal_gros_mtts[paypal_sup] == '1' or ($paypal_gros_mtts[paypal_sup] == '0' and $prixtotalpanier < '750')) // mtt max 750 €
											{
											$moyens_affiches .= "<tr><td><input type=\"radio\" name=\"moyen\" value=\"$moyen_paiement[moyenid]\"> $moyen_paiement[libelleFR]</td></tr>" ;
											}
										}
									else
										{
										if ($moyen_paiement[moyenid] == '3') // contre remboursement
											{
											$supplement = "" ;
											$prix_cr=$DB_site2->query_first("select prix_contre_remboursement from parametresgeneraux");
											if ($prix_cr[0] > 0)
												{
												$supplement = "(+ $prix_cr[0] €)" ;
												}
											$moyens_affiches .= "<tr><td><input type=\"radio\" name=\"moyen\" value=\"$moyen_paiement[moyenid]\"> $moyen_paiement[libelleFR] $supplement</td></tr>" ;
											}
										else
											{
											$moyens_affiches .= "<tr><td><input type=\"radio\" name=\"moyen\" value=\"$moyen_paiement[moyenid]\"> $moyen_paiement[libelleFR]</td></tr>" ;
											}
										}
									}
								}
							}
						else
							{
							$msg_erreur = "<tr><td>Aucun moyen de règlement n'est actuellement disponible</td></tr>" ;
							}
						}
					$especes=$DB_site2->query_first("select * from moyenpaiement where moyenid = '7'");
					if ($especes[0] != "")
						{
						$moyens_affiches .= "<tr><td><input type=\"radio\" name=\"moyen\" value=\"$especes[moyenid]\"> $especes[libelleFR] (Pas de frais de port)</td></tr>" ;
						}
					// frais de gestion		
					if ($fraisgestion != 0)
						{
						$fraisgestion = number_format($fraisgestion, 2, ',', ' ');
						$fraisdegestion = " $multilangue[fraisgestion] : $fraisgestion €";
						}
					// remise
					if (in_array ("3", $modules))
						{
						$prixremise = 0 ;
						$remises=$DB_site2->query("SELECT remiseid, typeremise, valeur, pctremise FROM remise WHERE actif = '1'" );
						if ($DB_site2->num_rows($remises) > 0) // il existe des remises
							{
							$total_facture = $DB_site2->query_first("SELECT SUM(qte*prix) FROM lignefacture WHERE factureid = '$factureid'");
							$i = 0 ;
							$remise0 = array() ;
							$remise1 = array() ;
							$remise2 = array() ;
							$remises_toutes = array() ;
							while ($remise=$DB_site2->fetch_array($remises))
								{
								$rayon=$DB_site2->query_first("SELECT catid from remisecategorie where remiseid = '$remise[remiseid]'");
								$article=$DB_site2->query_first("SELECT * from article a, remisearticle ra where a.artid = ra.artid and ra.remiseid = '$remise[remiseid]'");
								if (($rayon[catid] == "") and ($article[artid] == "")) // remise sans article ni catégorie
									{
									switch ($remise[typeremise])
										{
										case 0: // remise sur le poids
											$poids=$DB_site2->query_first("SELECT SUM(poid) as poids_tot FROM lignefacture lf, article a WHERE lf.artid = a.artid and lf.factureid = '$factureid'");
											if ($poids[poids_tot] > $remise[valeur])
												{
												$remise0[$i] = ($total_facture[0] *($remise[pctremise] / 100)) ;
												$remise0[$i] .= "-Remise : poids > $remise[valeur] grammes) :--$remise[remiseid]" ;
												}
											break;
										case 1: // remise sur le prix
											if ($total_facture[0] > $remise[valeur])
												{
												$remise1[$i] = $total_facture[0] *($remise[pctremise] / 100) ;
												$remise1[$i] .= "-Remise : prix > $remise[valeur] €) :--$remise[remiseid]" ;
												}
											break;
										case 2: // remise sur la quantité
											$qte = $DB_site2 -> query_first ("SELECT SUM(qte) nb FROM lignefacture WHERE factureid = '$factureid'" );
											if ($qte[nb] > $remise[valeur])
												{
												$remise2[$i] = $total_facture[0] *($remise[pctremise] / 100) ;
												$remise2[$i] .= "-Remise : quantité > $remise[valeur]) :--$remise[remiseid]" ;
												}
											break;
										} // fin du switch
									} // fin if remise sans article ni catégorie
								elseif ($article[artid] != "") // remise sur un article
									{
									switch ($remise[typeremise])
										{
										case 0: // remise sur le poids
											$facture_lignes = $DB_site2 -> query("select * from lignefacture where factureid = '$factureid'") ;
											$poids_tot_article = 0 ;
											$prix_tot_article = 0 ;
											while ($facture_ligne=$DB_site2->fetch_array($facture_lignes))
												{
												if ($facture_ligne[artid] == $article[artid])
													{
													$poids=$DB_site2->query_first("SELECT (a.poid * lf.qte) as poids_tot FROM lignefacture lf, article a WHERE lf.artid = a.artid and a.artid = '$article[artid]' and lf.lignefactureid = '$facture_ligne[lignefactureid]'");
													$poids_tot_article+=$poids[poids_tot] ;
													$prix = $DB_site2 -> query_first("SELECT (prix * qte) as prix_tot FROM lignefacture WHERE artid = '$article[artid]' and lignefactureid = '$facture_ligne[lignefactureid]'");
													$prix_tot_article += $prix[prix_tot] ;
													}
												}
											if ($poids_tot_article > $remise[valeur])
												{
												$remise0[$i] = ($prix_tot_article *($remise[pctremise] / 100)) ;
												$remise0[$i] .= "-Remise : poids pour l'article] $article[libelleFR] > $remise[valeur] grammes) :--$remise[remiseid]" ;
												}
											break;
										case 1: // remise sur le prix
											$facture_lignes = $DB_site2 -> query("select * from lignefacture where factureid = '$factureid'") ;
											$prix_tot_article = 0 ;
											while ($facture_ligne=$DB_site2->fetch_array($facture_lignes))
												{
												if ($facture_ligne[artid] == $article[artid])
													{
													$prix = $DB_site2 -> query_first("SELECT (prix * qte) as prix_tot FROM lignefacture WHERE artid = '$article[artid]' and lignefactureid = '$facture_ligne[lignefactureid]'");
													$prix_tot_article += $prix[prix_tot] ;
													}
												}
											if ($prix_tot_article > $remise[valeur])
												{
												$remise1[$i] = $prix_tot_article *($remise[pctremise] / 100) ;
												$remise1[$i] .= "-Remise : prix pour l'article $article[libelleFR] > $remise[valeur] €) :--$remise[remiseid]" ;
												}
											break;
										case 2: // remise sur la quantité
											$facture_lignes = $DB_site2 -> query("select * from lignefacture where factureid = '$factureid'") ;
											$qte_tot_article = 0 ;
											$prix_tot_article = 0 ;
											while ($facture_ligne=$DB_site2->fetch_array($facture_lignes))
												{
												if ($facture_ligne[artid] == $article[artid])
													{
													$qte=$DB_site2->query_first("SELECT SUM(qte) as qte_tot FROM lignefacture WHERE artid = '$article[artid]' and lignefactureid = '$facture_ligne[lignefactureid]'" );
													$qte_tot_article += $qte[qte_tot] ;
													$prix = $DB_site2 -> query_first("SELECT (prix * qte) as prix_tot FROM lignefacture WHERE artid = '$article[artid]' and lignefactureid = '$facture_ligne[lignefactureid]'");
													$prix_tot_article += $prix[prix_tot] ;
													}
												}
											if ($qte_tot_article > $remise[valeur])
												{
												$remise2[$i] = $prix_tot_article *($remise[pctremise] / 100) ;
												$remise2[$i] .= "-Remise : quantité pour l'article $article[libelleFR] > $remise[valeur]) :--$remise[remiseid]" ;
												}
											break;
										} // fin du switch
									} // fin si remise sur un article
								elseif ($rayon[catid] != "") // remise sur une catégorie
									{
									arborescence($rayon[catid]) ;
									switch ($remise[typeremise])
										{
										case 0: // remise sur le poids
											$poids_tot_categ = 0 ;
											$prix_tot_categ = 0 ;
											$poids = $DB_site2 -> query_first( "SELECT (a.poid * lf.qte) as poids_tot 
														FROM lignefacture lf, article a, categorie c 
														WHERE lf.artid = a.artid and a.catid = c.catid 
														and lf.factureid = '$factureid' 
														and lf.catid in ($categs_num $rayon[catid]) ") ;
											$poids_tot_categ += $poids[poids_tot] ;
											$prix = $DB_site2 -> query_first("SELECT (prix * qte) as prix_tot FROM lignefacture 
																				WHERE factureid = '$factureid' and catid in ($categs_num $rayon[catid])");
											$prix_tot_categ += $prix[prix_tot] ;
											if ($poids_tot_categ > $remise[valeur])
												{
												$remise0[$i] .= ($prix_tot_categ *($remise[pctremise] / 100)) ;
												$remise0[$i] .= "-Remise : poids pour le rayon $rayon[libelleFR] > $remise[valeur] grammes) :--$remise[remiseid]" ;
												}
											break;
										case 1: // remise sur le prix
											$prix_tot_categ = 0 ;
											$prix = $DB_site2 -> query_first("SELECT (prix * qte) as prix_tot FROM lignefacture
																			WHERE factureid = '$factureid' and catid in ($categs_num $rayon[catid])");
											$prix_tot_categ += $prix[prix_tot] ;
											if ($prix_tot_categ > $remise[valeur])
												{
												$remise1[$i] = $prix_tot_categ *($remise[pctremise] / 100) ;
												$remise1[$i] .= "-Remise : prix pour le rayon $rayon[libelleFR] > $remise[valeur] €) :--$remise[remiseid]" ;
												}
											break;
										case 2: // remise sur la quantité
											$qte_tot_categ = 0 ;
											$prix_tot_categ = 0 ;
											$qte = $DB_site2 -> query_first ("SELECT SUM(qte) as qte_tot FROM lignefacture 
																			WHERE factureid = '$factureid' and catid in ($categs_num $rayon[catid])" );
											$qte_tot_categ += $qte[qte_tot] ;
											$prix = $DB_site2 -> query_first("SELECT (prix * qte) as prix_tot FROM lignefacture
																			WHERE factureid = '$factureid' and catid in ($categs_num $rayon[catid])");
											$prix_tot_categ += $prix[prix_tot] ;
											if ($qte_tot_categ > $remise[valeur])
												{
												$remise2[$i] = $prix_tot_categ *($remise[pctremise] / 100) ;
												$remise2[$i] .= "-Remise : quantité pour le rayon $rayon[libelleFR] > $remise[valeur]) :--$remise[remiseid]" ;
												}
											break;
										} // fin du switch
									} // fin si remise sur une catégorie
								$i++ ;
								} // fin while
							if ((count ($remise0) > 0)) // on prend la plus grande remise de chaque type
								$remises_toutes[0] = max($remise0) ;
							if ((count ($remise1) > 0))
								$remises_toutes[1] = max($remise1) ;
							if ((count ($remise2) > 0))
								$remises_toutes[2] = max($remise2) ;
							if (count ($remises_toutes) > 0) // si au moins une remise correspond
								{
								$prixremise_plein = "" ;
								$prixremise = "" ;
								$libremise = "" ;
								$idremise = "" ;
								$prixremise_plein = max($remises_toutes) ; // c'est forcément la plus grande 
								eregi("(.*)-Remise", $prixremise_plein, $regs1) ;
								$prixremise = $regs1[1] ;
								eregi ("-(.*):", $prixremise_plein, $regs2) ;
								$libremise = $regs2[1] ;
								eregi ("--(.*)", $prixremise_plein, $regs3) ;
								$idremise = $regs3[1] ;
								if (is_numeric($prixremise) and $libremise != "" and is_numeric($idremise))
									{
									$DB_site2->query("update facture set remiseid = '$idremise', montantremise = '$prixremise' where facturid = '$factureid'");
									$prixtotalpanier -= $prixremise ;
									$prixremise = number_format($prixremise, 2, ',', ' ') ;
									}
								unset($remise0);
								unset($remise1);
								unset($remise2);
								unset($remises_toutes);
								$affichage_remises = " <tr>
															<td colspan=\"4\" class=\"td_head_cde1\" nowrap><h3>$libremise</h3></td>
															<td class=\"td_head_cde1\" align=\"right\" nowrap><h3>-$prixremise €</h3></td>
														 </tr>" ;
								}
							} // fin if remise
						} // fin if in array
					// bons cadeau
					if (in_array("13", $modules))
						{
						$cadeau_formulaire = "<tr>
											   <td colspan=\"5\" class=\"td_head_cde1\" align=\"center\" nowrap>
												 <form method=\"post\">
													 $multilangue[codereduction] <input type=\"text\" name=\"txt_code\" size=\"16\" maxlength=\"12\">
												   <input type=\"hidden\" name=\"action2\" value=\"cadeau\">
												   <input type=\"hidden\" name=\"factureid\" value=\"$factureid\">
												   <input type=\"submit\" value=\"Ok\">
												 </form>
											   </td>
											</tr>
											<tr>
											   <td colspan=\"5\" class=\"td_head_cde1\" align=\"center\" nowrap>
												$erreur_cadeau
											   </td>
											</tr>" ;
						if (($okvalide == 1) and ($okcorrespond == 1))
							{
							$prixreduction = 0 ;
							$cadeau=$DB_site2->query_first("select * from cadeau where cadeauid = '$cadeau_en_cours[cadeauid]'");
							switch ($type_du_cadeau)
								{
								case "CATEGORIE":
									$categ_cadeau=$DB_site2->query_first("select catid, cadeauid from categorie_cadeau where cadeauid = '$cadeau_en_cours[cadeauid]'");
									$liste_cats_cde=$DB_site2->query("select catid, prix, qte from lignefacture where factureid = '$factureid'") ;
									categories_filles ($categ_cadeau[catid]) ;
									$cat_en_cours = $categ_cadeau[catid] ;
									$cats_cde[$cat_en_cours] = $cat_en_cours ;
									while ($liste_cat_cde=$DB_site2->fetch_array($liste_cats_cde))
										{
										if (in_array ($liste_cat_cde[catid], $cats_cde))
											{
											$prix_de_la_ligne = $liste_cat_cde[prix] * $liste_cat_cde[qte] ;
											switch ($cadeau[typecadeauid])
												{
												case 0:
													if ($cadeau[valeurcadeau] < $prix_de_la_ligne)
														{
														$prixreduction += $cadeau[valeurcadeau] ;
														}
													else
														{
														$prixreduction += $prix_de_la_ligne ;
														}
													break ;
												case 1:
													$prixreduction += ($prix_de_la_ligne * $cadeau[valeurcadeau] / 100) ;
													break ;
												}
											}
										}
									break ;
								case "ARTICLE":
									$article_cadeau=$DB_site2->query_first("select artid, cadeauid from article_cadeau where cadeauid = '$cadeau_en_cours[cadeauid]'");
									$liste_arts_cde=$DB_site2->query("select artid, qte, prix from lignefacture where factureid = '$factureid'") ;
									while ($liste_art_cde=$DB_site2->fetch_array($liste_arts_cde))
										{
										if ($liste_art_cde[artid] == $article_cadeau[artid])
											{
											$prix_de_la_ligne = $liste_art_cde[prix] * $liste_art_cde[qte] ;
											switch ($cadeau[typecadeauid])
												{
												case 0:
													if ($cadeau[valeurcadeau] < $prix_de_la_ligne)
														{
														$prixreduction += $cadeau[valeurcadeau] ;
														}
													else
														{
														$prixreduction += $prix_de_la_ligne ;
														}
													break ;
												case 1:
													$prixreduction += ($prix_de_la_ligne * $cadeau[valeurcadeau] / 100) ;
													break ;
												}
											}
										}
									break ;
								case "GENERAL":
									switch ($cadeau[typecadeauid])
										{
										case 0:
											if ($cadeau[valeurcadeau] < $prixtotalpanier_sansfrais)
												{
												$prixreduction += $cadeau[valeurcadeau] ;
												}
											else
												{
												$prixreduction += $prixtotalpanier_sansfrais ;
												}
											break ;
										case 1:
											$prixreduction += ($prixtotalpanier_sansfrais * $cadeau[valeurcadeau] / 100) ;
											break ;
										}
									break ;
								}
							$cadeau_existant=$DB_site2->query_first("select montantcadeau from facture where facturid = '$factureid'");
							if ($cadeau_existant[montantcadeau] != 0)
								{
								if ($prixreduction > $cadeau_existant[montantcadeau])
									{
									$DB_site2->query("update facture set cadeauid = '$cadeau_en_cours[cadeauid]', montantcadeau = '$prixreduction' where facturid = '$factureid'");
									}
								else
									{
									$prixreduction = $cadeau_existant[montantcadeau] ;
									}
								}
							else
								{
								$DB_site2->query("update facture set cadeauid = '$cadeau_en_cours[cadeauid]', montantcadeau = '$prixreduction' where facturid = '$factureid'");
								}
							$prixtotalpanier -= $prixreduction ;
							$affichage_cadeau = " <tr>
													<td colspan=\"4\" class=\"td_head_cde1\" nowrap>Bon de réduction N° $cadeau[code]</td>
													<td class=\"td_head_cde1\" align=\"right\" nowrap>-$prixreduction €</td>
												 </tr>" ;
							}
						} // fin bons cadeaux
					// remise sur tout catalogue
					if (in_array ("106", $modules))
						{
						$util_remise=$DB_site2->query_first("SELECT * FROM utilisateur WHERE userid = '$clientid'") ;
						if ($util_remise[pctremise] != 0)
							{
							$remise_sur_tout_catalogue = $util_remise[pctremise] ;
							}
						elseif ($util_remise[groupeid] != 0)
							{
							$gp_remise=$DB_site2->query_first("SELECT * FROM groupe_utilisateur WHERE groupeid = '$util_remise[groupeid]'") ;
							$remise_sur_tout_catalogue = $gp_remise[pctremise] ;
							}
						if (isset($remise_sur_tout_catalogue) and $remise_sur_tout_catalogue > 0)
							{
							$DB_site2->query("UPDATE facture SET pctremise = '$remise_sur_tout_catalogue' WHERE facturid = '$factureid'");
							$affiche_remise_tt_cat = "<tr>
														<td class=\"td_head_cde1\" colspan=\"6\" align=\"right\">Remise de $remise_sur_tout_catalogue % déduite&nbsp;&nbsp;</td>
													</tr>" ;
							}
						}
					$port=$DB_site2->query_first("select prixport, tvaport from facture where facturid = '$factureid'");
					$prixtvaport = $port[prixport] - ($port[prixport] / (1 + ($port[tvaport]/100))) ;
					$prixtva += $prixtvaport ;
					$prixtotalpanier = number_format($prixtotalpanier, 2, ',', ' ');
					$prixtva = number_format($prixtva, 2, ',', ' ');	
					$cli=$DB_site2->query_first("SELECT nom, prenom from utilisateur where userid = '$clientid'");
					$art=$DB_site2->query_first("SELECT libelleFR from article where artid = '$artid'");
					echo "<h3>Passer une commande</h3>
					   <div align=\"left\"><strong>Etape 1 : Choix du client</strong> : $cli[prenom] $cli[nom]<br><br>
					   <strong>Etape 2 : Choix de l'article</strong> : $art[libelleFR]<br><br>
					   <strong>Etape 3 : Saisie des informations et chargement des fichiers</strong> : $texte<br><br></div><bR>
					   <strong>Etape 5 : Choix du moyen de règlement</strong><br><br>
						<form action=\"commande.php\" method=\"post\" enctype=\"multipart/form-data\">	
						<input type=\"hidden\" name=\"clientid\" value=\"$clientid\">
						<input type=\"hidden\" name=\"factureid\" value=\"$factureid\">
						<input type=\"hidden\" name=\"s\" value=\"$s\">
						<input type=\"hidden\" name=\"action\" value=\"etape5\">
						<input type=\"hidden\" name=\"texte\" value=\"$texte\">
						<table width=\"100%\" align=\"center\" class=\"table_coupscoeur\">
							<tr>
								<td colspan=\"2\" align=\"center\">
								<br>
								<div align=\"left\">Récapitulatif de la commande :</div>
								<bR>
								<table align=\"center\" class=\"table_modules_boutiques\" cellspacing=\"0\" cellpadding=\"0\">
									<tr>
										<td class=\"td_head_cde1\" align=\"center\" nowrap><strong>Désignation</strong></td>
										<td class=\"td_head_cde1\" align=\"center\" nowrap><strong>Caracteristiques</strong></td>
										<td class=\"td_head_cde1\" align=\"center\" nowrap><strong>P U</strong></td>
										<td class=\"td_head_cde1\" align=\"center\" nowrap><strong>Qté</strong></td>
										<td class=\"td_head_cde1\" align=\"center\" nowrap><strong>Montant</strong></td>
									</tr>
									$TemplateCde2Bit
									$affichage_remises
									$affichage_cadeau
									$affich_remise_tt_cat
									<tr>
										<td class=\"td_head_cde1\" align=\"right\" colspan=\"5\" nowrap>$fraisdegestion</td>
									</tr>
									<tr>
										<td class=\"td_head_cde1\" align=\"right\" colspan=\"5\" nowrap> Frais de port ($pays[libelle]) : $fraisport[prix] €</td>
									</tr>
									<tr>
										<td colspan=\"5\" class=\"td_head_cde1\" align=\"right\" nowrap><font color=red>Total : $prixtotalpanier € $multilangue[ttc]</font></td>
									</tr>
									<tr>
										<td colspan=\"5\" class=\"td_head_cde1\" align=\"right\" nowrap>Dont TVA  : $prixtva €</td>
									</tr>
									$TemplateCde2CommandeCadeau
								</table>
								<br>
								<div align=\"left\">Adresse de livraison :</div>
								<bR>
								<table align=\"center\" class=\"table_modules_boutiques\">
									<tr>
										<td align=\"center\">
										$rs_facture_coordonnees[raisonsociale]<br>
										$rs_facture_coordonnees[nom] $rs_facture_coordonnees[prenom]<br>
										$rs_facture_coordonnees[adresse]<br>
										$rs_facture_coordonnees[codepostal] $rs_facture_coordonnees[ville]<br>
										$rs_facture_coordonnees[libelle]<br><br>
										</td>
									</tr>
								</table>
								<br>
								<div align=\"left\">Moyens de règlement :</div>
								<bR>
								<table align=\"center\" class=\"table_modules_boutiques\">
									$moyens_affiches
								</table>
								<bR>
								</td>
							</tr>
							<tr>
								<td align=\"center\" nowrap colspan=\"2\">
									<br><br><input type=\"submit\" value=\"SUIVANT\"><br><br>
								</td>
							</tr>
						</table>
					</form>
					<br><a href=\"commande.php\">Retour</a>" ;
					break ;
				}
			}
		}
	}

// insertion dans panier
// choix des caracteristiques et saisie des informations
if (isset($action) and $action == "etape3")
	{
	if (!isset($artid) or $artid == "")
		{
		$erreur = "Vous devez sélectionner un article" ;
		$action = "etape2" ;
		}
	else
		{
		$libelle = "libelleFR" ;
		$titre2 = "Titre2FR" ;
		$article=$DB_site2->query_first("SELECT * FROM article WHERE artid='$artid' ");
		if ($article[image]!="") 
			{
			$articleimage="<center><a href=\"http://$host/configurations/$host/images/produits/$article[artid].$article[image]\" title=\"$article[$libelle]\" alt=\"$article[$libelle]\" target=\"_blank\"><img src=\"http://$host/configurations/$host/images/produits/article/$article[artid].$article[image]\" alt=\"$article[$libelle]\"></a>";
			if ($article[legende] != "")
				{
				$articlelegende = "<br>$article[legende]" ;
				}
			} 
		else 
			{
			$articleimage="Pas de photo";
			}
		$datejour=date("Y-m-d") ;
		$article[prix]= number_format($article[prix], 2, '.', '');
		$articlepromo=$DB_site2->query_first("SELECT * FROM promotion WHERE artid = '$article[artid]' and datedebut <= '$datejour' 
																	and datefin > '$datejour'");
		if ($articlepromo[promotionid] != "")
			{
			$articlepromo[prix]= number_format($articlepromo[prix], 2, '.', '');
			$temp_datefinpromo = affichDate($articlepromo[datefin]);
			$prix_encours = "$articlepromo[prix] € <br>au lieu de <s>$article[prix] €</s>" ;
			}
		else
			{
			$prix_encours = "$article[prix] €" ;
			}
		// caracteristiques
		$caracteristiques=$DB_site2->query("SELECT c.caractid, $libelle FROM categorie_caracteristique cc, caracteristique c
											WHERE cc.caractid = c.caractid and cc.catid = '$article[catid]'");
		$ok = 0 ;
		$num = 0 ;
		$zonedeliste_choix_caracteristique = "" ;
		while ($caracteristique=$DB_site2->fetch_array($caracteristiques))
			{
			$num++ ;
			$caract_valeurs=$DB_site2->query("SELECT ac.caractvalid, $libelle, ac.differenceprix FROM caracteristiquevaleur cv, 
												article_caractval ac WHERE cv.caractvalid = ac.caractvalid AND 
												caractid = '$caracteristique[caractid]' AND artid = '$artid' ORDER BY cv.position ASC");
			$valeurs_caracteristique_zonedeliste = "" ;
			$lst = "lst_$caracteristique[caractid]" ;
			while ($caract_valeur=$DB_site2->fetch_array($caract_valeurs))
				{
				$sel = "" ;
				if ($caract_valeur[differenceprix] == "0")
					{
					$differenceprix = "" ;
					}
				elseif ($caract_valeur[differenceprix] > 0)
					{
					$differenceprix = "(+ $caract_valeur[differenceprix] €)" ;
					}
				else
					{
					$differenceprix = "($caract_valeur[differenceprix] €)" ;
					}
				if (${$lst} == $caract_valeur[caractvalid])
					{
					$sel = "selected" ;
					}
				$valeurs_caracteristique_zonedeliste .= "<option title=\"$caract_valeur[differenceprix]\" value=\"$caract_valeur[caractvalid]\" $sel>$caract_valeur[$libelle] $differenceprix</option>" ;	
				}
			if ($DB_site2->num_rows($caract_valeurs) > 0)
				{
				$zonedeliste_choix_caracteristique .= "<tr><td align=\"center\"><b>$caracteristique[$libelle]</b><br>
										<select name=\"lst_$caracteristique[caractid]\" onchange=\"Javascript: clique()\">
										 $valeurs_caracteristique_zonedeliste</select></td></tr>" ;
				}
			}	
		$cli=$DB_site2->query_first("SELECT nom, prenom from utilisateur where userid = '$clientid'");
		$art=$DB_site2->query_first("SELECT libelleFR from article where artid = '$artid'");
		echo "<h3>Passer une commande</h3>
		   <div align=\"left\"><strong>Etape 1 : Choix du client</strong> : $cli[prenom] $cli[nom]<br><br>
		   <strong>Etape 2 : Choix de l'article</strong> : $art[libelleFR]<br><br></div><bR>
		   <strong>Etape 3 : Saisie des informations et chargement des fichiers</strong><br><br>
			<form action=\"commande.php\" method=\"post\" enctype=\"multipart/form-data\">	
			<input type=\"hidden\" name=\"clientid\" value=\"$clientid\">
			<input type=\"hidden\" name=\"artid\" value=\"$artid\">
			<input type=\"hidden\" name=\"s\" value=\"$s\">
			<input type=\"hidden\" name=\"action\" value=\"etape4\">
			<input type=\"hidden\" name=\"action2\" value=\"$action2\">
			<input type=\"hidden\" name=\"texte\" value=\"$texte\">
			<table width=\"100%\" align=\"center\" class=\"table_coupscoeur\">
				<tr>
					<td colspan=\"2\" align=\"center\">
						<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
							<tr>
								<td height=\"95\" width=\"135\" valign=\"top\" align=\"center\">
									$articleimage<br>
									<i><font size=\"-2\">$articlelegende</font></i>
								</td>
								<td valign=\"top\" height=\"100%\">
									<strong><u>$article[$libelle]</u></strong><br><br>
									$article[$titre2]<br><br>
									Réf. : <strong>$article[artcode]</strong><br>
								</td>
								<tD valign=\"middle\" align=\"center\">
									Prix de base : <font class=\"cost\">$prix_encours</font>
									<br><table>$zonedeliste_choix_caracteristique</table>
									<br>Qté : <input type=\"text\" name=\"qte\" value=\"1\" size=\"2\">
								</td>
							</tr>
						</table>
					</td>
				</tr>" ;
		$champs=$DB_site2->query("select articlechampid, ac.libelleFR, nom, type, obligatoire from article a, article_champ ac
								where a.artid = '$artid' and  a.artid = ac.artid") ;
		if ($DB_site2->num_rows($champs) > 0)
			{
			echo "<tr>
						<td colspan=\"2\" align=\"center\" nowrap>
							<font color=blue>*</font>= Champ obligatoire<br><br>
							<font class=\"cost\">$erreur</font>
						</td>
					</tr>" ;
			$article=$DB_site2->query_first("select * from article where artid = '$artid'") ;
			while ($champ=$DB_site2->fetch_array($champs))
				{
				$valeurs=$DB_site2->query("select articlechampvaleurid, libelle, description, valeur from article_champ_valeur 
				where articlechampid = '$champ[articlechampid]'") ;
				switch ($champ[type])
					{
					case "1": // boîte texte
						$vals ="" ;
						while ($valeur=$DB_site2->fetch_array($valeurs))
							{
							$vals .= "$valeur[libelle]=\"$valeur[valeur]\" " ;
							}
						$champform = "<textarea name=\"$champ[nom]\" $vals>$_POST[$nom_champ]</textarea>" ;
						break ;
					case "2": // Boutons radio
						$champform = "" ;
						while ($valeur=$DB_site2->fetch_array($valeurs))
							{
							if ($valeur[valeur] == $_POST[$nom_champ])
								{
								$champform .= "<input type=\"radio\" name=\"$champ[nom]\" $valeur[libelle]=\"$valeur[valeur]\" checked> $valeur[description] &nbsp;" ;
								}
							else
								{
								$champform .= "<input type=\"radio\" name=\"$champ[nom]\" $valeur[libelle]=\"$valeur[valeur]\"> $valeur[description] &nbsp;" ;
								}
							}
						break ;
					case "3": // Cases à cocher
						$champform = "" ;
						while ($valeur=$DB_site2->fetch_array($valeurs))
							{
							if ($valeur[valeur] == $_POST[$nom_champ])
								{
								$champform .= "<input type=\"checkbox\" name=\"$champ[nom][]\" $valeur[libelle]=\"$valeur[valeur]\" selected> $valeur[description] &nbsp;" ;
								}
							else
								{
								$champform .= "<input type=\"checkbox\" name=\"$champ[nom][]\" $valeur[libelle]=\"$valeur[valeur]\"> $valeur[description] &nbsp;" ;
								}
							}
						break ;
					case "4": // Champ texte
						$vals ="" ;
						while ($valeur=$DB_site2->fetch_array($valeurs))
							{
							$vals .= "$valeur[libelle]=\"$valeur[valeur]\" " ;
							}
						$champform = "<input type=\"text\" name=\"$champ[nom]\" value=\"$_POST[$nom_champ]\" $vals>" ;
						break ;
					case "5": // Liste à sélection unique
						$options = "" ;
						while ($valeur=$DB_site2->fetch_array($valeurs))
							{
							if ($valeur[valeur] == $_POST[$nom_champ])
								{
								$options .= "<option $valeur[libelle]=$valeur[valeur] selected>$valeur[description]</option>" ;
								}
							else
								{
								$options .= "<option $valeur[libelle]=$valeur[valeur]>$valeur[description]</option>" ;
								}
							}
						$champform = "<select name=\"$champ[nom]\"><option value=\"\"></option>$options</select>" ;
						break ;
					case "6": // Liste à sélections multiples
						$options = "" ;
						while ($valeur=$DB_site2->fetch_array($valeurs))
							{
							if ($valeur[valeur] == $_POST[$nom_champ])
								{
								$options .= "<option $valeur[libelle]=$valeur[valeur] selected>$valeur[description]</option>" ;
								}
							else
								{
								$options .= "<option $valeur[libelle]=$valeur[valeur]>$valeur[description]</option>" ;
								}
							}
						$champform = "<select multiple=\"multiple\" name=\"$champ[nom][]\"><option value=\"\"></option>$options</select>" ;
						break ;
					case "7": // Insertion de fichier
						$vals ="" ;
						while ($valeur=$DB_site2->fetch_array($valeurs))
							{
							$vals .= "$valeur[libelle]=\"$valeur[valeur]\" " ;
							}
						$champform = "<input type=\"file\" name=\"$champ[nom]\" value=\"$_POST[$nom_champ]\" $vals>" ;
						break ;
					}
				if ($champ[obligatoire] == '1')
					{
					$etoile = " <font color=blue>*</font> " ;
					}
				else
					{
					$etoile = "" ;
					}
				echo "
					
					<tr>
						<td align=\"right\" nowrap>
							$champ[libelle] $etoile :&nbsp;
						</td>
						<td align=\"left\" nowrap>
							&nbsp; $champform
						</td>
					</tr>" ;
				}
			}
		else
			{
			echo "<tr><td align=\"center\">Pas d'informations à saisir pour cet article</td></tr>" ;
			}
				echo "
				<tr>
					<td align=\"center\" nowrap colspan=\"2\">
						<br><br><input type=\"submit\" value=\"SUIVANT\"><br><br>
					</td>
				</tr>
			</table>
		</form>
		<br><a href=\"commande.php\">Retour</a>" ;
		}
	}

// choix de l'article
if (isset($action) and $action == "etape2")
	{
	echo "fsdlkm" ;
	$erreur = "" ;
	if ($clientid == 0)
		{
		if ($mail == "")
			{
			$erreur .= "Le champ e-mail est obligatoire<br>" ;
			}
		if ($paysid == "")
			{
			$erreur .= "Le champ pays est obligatoire<br>" ;
			}
		}
	if ($erreur != "")
		{
		$action = "etape1" ;
		}
	else
		{
		if ($clientid == 0)
			{
			if ($civilite != '0' and $civilite != '1' and $civilite != '2')
				{
				$civilite = '0' ;
				}
			$password = "" ;
			$chars = array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z"); 
			$count = count($chars) - 1; 
			srand((float) microtime()*1000000);
			for ($i = 0; $i < 8; $i++) 
				{
				$password .= $chars[rand(0, $count)];
				}
			$dateinscription = date('Y-m-d') ;
			$langue = "FR" ;
			$optionnel = "" ;
			$portable = "" ;
			$DB_site2->query("insert into utilisateur (mail, password, civilite, nom, prenom, codepostal, adresse, ville, telephone, 
			raisonsociale, dateinscription, paysid, langue, allowemail, optionnel, portable) values ('$mail', 
			'".md5($password)."', '$civilite', '$nom', '$prenom', '$codepostal', '$adresse', '$ville', '$telephone', '$raisonsociale', 
			'$dateinscription', '$paysid', '$langue', '$allowemail', '$optionnel', '$portable')");
			$clientid=$DB_site2->insert_id() ;
			//require_once "../admin/libmail.php";
			$mail_contact=$DB_site2->query_first("SELECT mail_contact FROM parametresgeneraux");
			$mail_exp = $mail_contact[0] ;
			$sujet="Votre compte client sur $host!";
			$corps="
			<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">
			<html>
			<head>
			<meta http-equiv=\"content-type\" content=\"text/html;charset=iso-8859-15\">
			</head>
			<body font-family=\"Verdana\">
			Bonjour $prenom $nom,
			<br><br>Votre compte client vient d'être créé sur $host.<bR>
			Votre login : $mail<br>
			Votre mot de passe : $password
			</body>
			</html>";
			/*$m= new Mail; // create the mail
			$m->From($mail_exp);
			$m->To($mail);
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
			$to=$mail;
			$subject=$sujet;
			$message=stripslashes($corps) ;
			gmail($from,$to,$subject,$smtppass,$localhost,$namefrom,$abuse,$message);
			/* fin envoi d'un mail par Gmail */
			}
		$liste_articles = "" ;
		$articles=$DB_site2->query("SELECT a.artid, a.libelleFR as libart, c.libelleFR as libcat FROM article a, categorie c 
							where a.catid = c.catid and a.active = '1' ORDER BY a.libelleFR ASC");
		while ($article=$DB_site2->fetch_array($articles))
			{
			if ($article[artid] == $artid) 
				{
				$selected="selected";
				} 
			else 
				{
				$selected="";
				}
			$liste_articles .= "<option value=$article[artid] $selected>$article[libart] ($article[libcat])</option>";
			}
		$cli=$DB_site2->query_first("SELECT nom, prenom, mail from utilisateur where userid = '$clientid'");
		echo "<h3>Passer une commande</h3>
		   <div align=\"left\"><strong>Etape 1 : Choix du client</strong> : $cli[prenom] $cli[nom]<br><br></div><bR>
		   <strong>Etape 2 : Choix de l'article</strong><br><br>
			<form action=\"commande.php\" method=\"post\" name=\"formu\">	
			<input type=\"hidden\" name=\"clientid\" value=\"$clientid\">
			<input type=\"hidden\" name=\"action\" value=\"etape3\">
			<input type=\"hidden\" name=\"s\" value=\"$s\">
			<input type=\"hidden\" name=\"action2\" value=\"\">
			<input type=\"hidden\" name=\"texte\" value=\"$texte\">
			<table width=\"100%\" align=\"center\" class=\"table_coupscoeur\">
				<tr>
					<td align=\"center\">
					<font class=\"cost\">$erreur</font>
					<br><br>
					Article :&nbsp;<select name=\"artid\">$liste_articles</select>
					<br><br><br><input type=\"button\" value=\"Ajouter un autre article\" onclick=\"Javascript: ajouter('autre');\">&nbsp;&nbsp;&nbsp;<input type=\"button\" value=\"Terminer la commande\" onclick=\"Javascript: ajouter('fin');\">
					</td>
				</tr>
			</table>
		</form>
		<br><a href=\"commande.php\">Retour</a>" ;
		}
	}
	
// choix du client
if (!isset($action) or $action == "etape1")
  	{
	$texte = "" ;
	$zonedeliste_pays = "";
	$payss=$DB_site2->query("SELECT * FROM pays ORDER BY libelle ASC");
	while($pays=$DB_site2->fetch_array($payss))
		{
		if($pays[paysid]==57) 
			{
			$selected="selected";
			} 
		else 
			{
			$selected="";
			}
		$zonedeliste_pays .= "<option value=$pays[paysid] $selected>$pays[libelle]</option>";
		}
	$liste_clients = "" ;
	$liste_clients .= "<option value=0 selected></option>";
	$clients=$DB_site2->query("SELECT * FROM utilisateur ORDER BY nom, prenom");
	while ($client=$DB_site2->fetch_array($clients))
		{
		$liste_clients .= "<option value=$client[userid]>$client[nom] $client[prenom] ($client[mail])</option>";
		}
	echo "<h3>Passer une commande</h3>
	   <strong>Etape 1 : Choix du client</strong><br><br>
	   <ul>
			<li><a href=\"commande.php?choix=1\">Client existant</a></li>
			<li><a href=\"commande.php?choix=2\">Nouveau client</a></li>
	   </ul>" ;
	$s = md5(uniqid(rand(), true));
	 if (isset($choix) and $choix == 1)
	 	{
		echo "<form action=\"commande.php\" method=\"post\">	
		<input type=\"hidden\" name=\"action\" value=\"etape2\">
		<input type=\"hidden\" name=\"s\" value=\"$s\">
		<table width=\"100%\" align=\"center\" class=\"table_coupscoeur\">
			<tr>
				<td align=\"center\" colspan=\"2\" nowrap>
				<br>
				<strong>Client déjà inscrit ?</strong><br>
				<bR><select name=\"clientid\">$liste_clients</select>
				<br>[ Ne pas rempir la fiche ]
				<br><bR>
				</td>
			</tr>
			<tr>
				<td align=\"center\" nowrap colspan=\"2\">
					<br><br><input type=\"submit\" value=\"SUIVANT\"><br><br>
				</td>
			</tr>
		</table>" ;	
		}
	elseif (isset($choix) and $choix == 2)
		{
		echo "<form action=\"commande.php\" method=\"post\">	
		<input type=\"hidden\" name=\"action\" value=\"etape2\">
		<input type=\"hidden\" name=\"s\" value=\"$s\">
		<table width=\"100%\" align=\"center\" class=\"table_coupscoeur\">
			<tr>
				<td colspan=\"2\" align=\"center\" nowrap>
					<font color=blue>*</font>= Champ obligatoire<br><br>
					<font class=\"cost\">$erreur</font>
				</td>
			</tr>
			<tr>
				<td align=\"right\" nowrap>
					Civilité :&nbsp;
				</td>
				<td align=\"left\" nowrap>
					&nbsp;<select name=\"civilite\">
							<option>--Choisissez--</option>
							<option value=0 $selectmr>Monsieur</option>
							<option value=1 $selectmme>Madame</option>
						</select>
				</td>
			</tr>
			<tr>
				<td align=\"right\" nowrap>
					Nom :&nbsp;
				</td>
				<td align=\"left\" nowrap>
					&nbsp;<input type=\"text\" name=\"nom\" value=\"$nom\">
				</td>
			</tr>
			<tr>
				<td align=\"right\" nowrap>
					Prénom :&nbsp;
				</td>
				<td align=\"left\" nowrap>
					&nbsp;<input type=\"text\" name=\"prenom\" value=\"$prenom\">
				</td>
			</tr>
			<tr>
				<td align=\"right\" nowrap>
					E-mail <font color=blue>*</font> :&nbsp;
				</td>
				<td align=\"left\" nowrap>
					&nbsp;<input type=\"text\" name=\"mail\" value=\"$mail\">
				</td>
			</tr>
			<tr>
				<td align=\"right\" nowrap>
					Adresse :&nbsp;
				</td>
				<td align=\"left\" nowrap>
					&nbsp;<input type=\"text\" name=\"adresse\" value=\"$adresse\">
				</td>
			</tr>
			<tr>
				<td align=\"right\" nowrap>
					Code postal :&nbsp;
				</td>
				<td align=\"left\" nowrap>
					&nbsp;<input type=\"text\" name=\"codepostal\" value=\"$codepostal\">
				</td>
			</tr>
			<tr>
				<td align=\"right\" nowrap>
					Ville :&nbsp;
				</td>
				<td align=\"left\" nowrap>
					&nbsp;<input type=\"text\" name=\"ville\" value=\"$ville\">
				</td>
			</tr>
			<tr>
				<td align=\"right\" nowrap>
					Téléphone :&nbsp;
				</td>
				<td align=\"left\" nowrap>
					&nbsp;<input type=\"text\" name=\"telephone\" value=\"$telephone\">
				</td>
			</tr>
			<tr>
				<td align=\"right\" nowrap>
					Raison sociale :&nbsp;
				</td>
				<td align=\"left\" nowrap>
					&nbsp;<input type=\"text\" name=\"raisonsociale\" value=\"$raisonsociale\">
				</td>
			</tr>
			<tr>
				<td align=\"right\" nowrap>
					Pays <font color=blue>*</font> :&nbsp;
				</td>
				<td align=\"left\" nowrap>
					&nbsp;<select name=\"paysid\">
					 $zonedeliste_pays
					</select>
				</td>
			</tr>" ;
			if (in_array("8",$modules))
				{
				echo "<tr>
						<td align=\"right\" nowrap>
						Recevoir la newsletter ?
						</td>
						<td align=\"left\" nowrap>
						&nbsp;&nbsp;<input type=\"checkbox\" name=\"allowemail\" checked>
						</td>
					</tr>" ;
				}	
			// champ optionnel
			$champsup=$DB_site2->query_first("select user_option_actif, user_option_lib, user_option_obligatoire from parametresgeneraux");
			if ($champsup[user_option_actif] == '1')
				{
				if ($champsup[user_option_obligatoire] == '1')
					{
					$etoilechp = "<font color=blue>*</font>" ;
					}
				else
					{
					$etoilechp = "" ;
					}
				echo "<tr>
						<td align=\"right\" nowrap>$champsup[user_option_lib] $etoilechp :&nbsp;</td>
						<td align=\"left\" nowrap>&nbsp;&nbsp;<input type=\"text\" name=\"option\" value=\"$option\">
						</td>
					</tr>" ;
				}
			echo "<tr>
				<td align=\"center\" nowrap colspan=\"2\">
					<br><br><input type=\"submit\" value=\"SUIVANT\"><br><br>
				</td>
			</tr>
			</table>
		</form>" ;
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
        </tr>
";


include "./footer.php";
function copier_image ($nom_fic, $imagename)
	{
	if ($OpenLocal = fopen ($_FILES[$imagename]['tmp_name'], "rb")) 
		{
		$LocalFileContent = fread ($OpenLocal, $_FILES[$imagename]['size']) ;
		} 
	else 
		{
		fclose ($OpenLocal) ;
		die ("<b>Erreur :</b> Impossible d'ouvrir le fichier local '" . $_POST[$imagename] . ".") ;
		}
	fclose ($OpenLocal) ;
	if ($OpenRemote = fopen ($nom_fic, "wb")) 
		{
		if (fwrite ($OpenRemote, $LocalFileContent)) 
			{
			fclose ($OpenRemote) ;
			}
		} 
	else 
		{
		fclose ($OpenRemote) ;
		die ("<b>Erreur :</b> Impossible d'ouvrir le fichier distant $nom_fic") ;
		}
	chmod ("$nom_fic", 0777) ;
	}

function arborescence($parentid)
	{
	global $DB_site2,$categs_num;
	$arbos=$DB_site2->query("select catid from categorie where parentid='$parentid'");
	while ($arbo=$DB_site2->fetch_array($arbos))
		{
		$categs_num .= "$arbo[catid]," ;
		arborescence($arbo[catid]);
		}
	return ($categs_num) ;
	}

function affichDate( $date_entree ) // Date au format aaaa-mm-jj -> jj/mm/aaaa
	{
	list($annee, $mois, $jour) = explode( "-", $date_entree );
	return "$jour/$mois/$annee";
	}

?>