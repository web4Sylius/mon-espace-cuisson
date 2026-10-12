<?php
require_once __DIR__.'/../secrets.php';

echo "<html><head>
	<title>Maintenance du site</title>
	<LINK href=\"vendre-en-ligne.css\" type=\"text/css\" rel=\"stylesheet\">
	</head><body>
	<br><br><br>
		<center>Le site est actuellement en maintenance technique.<br><br>
		Merci de votre compréhension.</center>";

/*
include "/home/arobases/www/global.php";
$ret = $_SERVER['HTTP_REFERER'] ;

echo "<html><head><title>Maintenance des bases de données</title></head><body>
		<a href=\"$ret\">Retour</a>
		<div align=\"center\"><h2>Maintenance des bases de données</h2></div>";
		
		
if (isset ($action) and $action == "langues1")
	{
	echo "<center>Etes-vous sûr(e) de bien vouloir copier les informations des tables de <font color=\"red\"><b>$bdd</b></font> dans toutes les langues ?<br>
	ATTENTION : Cette action est irréversible !<br>" ;
	echo "<a href=\"maintenance.php?action=langues2&bdd=$bdd\">OUI</a>&nbsp;&nbsp;<a href=\"maintenance.php\">NON</a></center>" ;
	}

if (isset ($action) and $action == "templates1")
	{
	echo "<center>Etes-vous sûr(e) de bien vouloir copier la table templatebeta de <font color=\"red\"><b>$bdd</b></font> en template ?<br>
	ATTENTION : Cette action est irréversible !<br>" ;
	echo "<a href=\"maintenance.php?action=templates2&bdd=$bdd\">OUI</a>&nbsp;&nbsp;<a href=\"maintenance.php\">NON</a></center>" ;
	}

if (isset ($action) and $action == "langues2")
	{
	echo "<center><form method=\"post\" action=\"maintenance.php\"><input type=\"hidden\" name=\"action\" value=\"mail\"><input type=\"hidden\" name=\"bdd\" value=\"$bdd\">
	Un e-mail va être envoyé au responsable de la boutique, veuillez remplir le corps du message : <bR><br>
	<textarea cols=\"40\" rows=\"10\" name=\"msg\">Votre boutique en ligne vient d'être mise à jour. \nLes modifications effectuées sont les suivantes :\n</textarea>
	<br><input type=\"submit\" value=\"envoyer\"></form></center>" ;
	include $DOCUMENT_ROOT."/config.php";
	include_once $DOCUMENT_ROOT."/mysql.php";
	$DB_site2=new DB_Sql;
	$DB_site2->appname='arobases';
	$DB_site2->appshortname='arobases';
	$DB_site2->database=$bdd;
	$DB_site2->server=$servername;
	$DB_site2->user=$dbusername;
	$DB_site2->password=$dbpassword;
	$DB_site2->connect();
	$DB_site2->query("update article set libelleDE = libelleFR, libelleEN = libelleFR, libelleIT = libelleFR, libelleSP = libelleFR, descriptionDE = descriptionFR, descriptionEN = descriptionFR, descriptionIT = descriptionFR, descriptionSP = descriptionFR");
	$DB_site2->query("update caracteristique set libelleDE = libelleFR, libelleEN = libelleFR, libelleIT = libelleFR, libelleSP = libelleFR") ;
	$DB_site2->query("update categorie set libelleDE = libelleFR, libelleEN = libelleFR, libelleIT = libelleFR, libelleSP = libelleFR, descriptionDE = descriptionFR, descriptionEN = descriptionFR, descriptionIT = descriptionFR, descriptionSP = descriptionFR") ;
	$DB_site2->query("update description set titleDE = titleFR, titleEN = titleFR, titleIT = titleFR, titleSP = titleFR") ;
	$DB_site2->query("update etatfacture set libelleDE = libelleFR, libelleEN = libelleFR, libelleIT = libelleFR, libelleSP = libelleFR") ;
	$DB_site2->query("update fraisgestion set libelleDE = libelleFR, libelleEN = libelleFR, libelleIT = libelleFR, libelleSP = libelleFR") ;
	$DB_site2->query("update institutionnel set libelleDE = libelleFR, libelleEN = libelleFR, libelleIT = libelleFR, libelleSP = libelleFR, contenuDE = contenuFR, contenuEN = contenuFR, contenuIT = contenuFR, contenuSP = contenuFR") ;
	$DB_site2->query("update keywords set titleDE = titleFR, titleEN = titleFR, titleIT = titleFR, titleSP = titleFR") ;
	$DB_site2->query("update liens set descriptionDE = descriptionFR, descriptionEN = descriptionFR, descriptionIT = descriptionFR, descriptionSP = descriptionFR") ;
	$DB_site2->query("update mailing set libelleDE = libelleFR, libelleEN = libelleFR, libelleIT = libelleFR, libelleSP = libelleFR, contenuDE = contenuFR, contenuEN = contenuFR, contenuIT = contenuFR, contenuSP = contenuFR") ;
	$DB_site2->query("update moyenpaiement set libelleDE = libelleFR, libelleEN = libelleFR, libelleIT = libelleFR, libelleSP = libelleFR") ;
	$DB_site2->query("update news set libelleDE = libelleFR, libelleEN = libelleFR, libelleIT = libelleFR, libelleSP = libelleFR, contenuDE = contenuFR, contenuEN = contenuFR, contenuIT = contenuFR, contenuSP = contenuFR") ;
	$DB_site2->query("update pagetitle set titleDE = titleFR, titleEN = titleFR, titleIT = titleFR, titleSP = titleFR") ;
	$DB_site2->query("update template set contenuDE = contenuFR, contenuEN = contenuFR, contenuIT = contenuFR, contenuSP = contenuFR") ;
	$DB_site2->query("update templatebeta set contenuDE = contenuFR, contenuEN = contenuFR, contenuIT = contenuFR, contenuSP = contenuFR") ;
	$DB_site2->query("update templateold set contenuDE = contenuFR, contenuEN = contenuFR, contenuIT = contenuFR, contenuSP = contenuFR") ;
	$lg_done[$bdd] = "DONE" ;
	}	
	
if (isset ($action) and $action == "templates2")
	{
	echo "<center><form method=\"post\" action=\"maintenance.php\"><input type=\"hidden\" name=\"action\" value=\"mail\"><input type=\"hidden\" name=\"bdd\" value=\"$bdd\">
	Un e-mail va être envoyé au responsable de la boutique, veuillez remplir le corps du message : <bR><br>
	<textarea cols=\"40\" rows=\"10\" name=\"msg\">Votre boutique en ligne vient d'être mise à jour. \nLes modifications effectuées sont les suivantes :\n</textarea>
	<br><input type=\"submit\" value=\"envoyer\"></form></center>" ;
	include $DOCUMENT_ROOT."/config.php";
	include_once $DOCUMENT_ROOT."/mysql.php";
	$DB_site2=new DB_Sql;
	$DB_site2->appname='arobases';
	$DB_site2->appshortname='arobases';
	$DB_site2->database=$bdd;
	$DB_site2->server=$servername;
	$DB_site2->user=$dbusername;
	$DB_site2->password=$dbpassword;
	$DB_site2->connect();
	$DB_site2->query("drop table templateold");
	$DB_site2->query("CREATE TABLE $bdd.templateold (templateid int(6) NOT NULL AUTO_INCREMENT, parentid int(6) NOT NULL default '1', libelle varchar(255) NOT NULL default '', bloc enum('0', '1') NOT NULL default '0', titrebloc varchar(255) NOT NULL default '', description varchar(255) NOT NULL default '', contenuFR longtext NOT NULL , contenuDE text NOT NULL , contenuEN text NOT NULL, contenuIT text NOT NULL , contenuSP text NOT NULL, PRIMARY KEY (templateid), KEY libelle (libelle))");
	$DB_site2->query("INSERT INTO $bdd.templateold SELECT * FROM $bdd.template");
	$DB_site2->query("drop table template");
	$DB_site2->query("CREATE TABLE $bdd.template (templateid int(6) NOT NULL AUTO_INCREMENT, parentid int(6) NOT NULL default '1', libelle varchar(255) NOT NULL default '', bloc enum('0', '1') NOT NULL default '0', titrebloc varchar(255) NOT NULL default '', description varchar(255) NOT NULL default '', contenuFR longtext NOT NULL , contenuDE text NOT NULL , contenuEN text NOT NULL, contenuIT text NOT NULL , contenuSP text NOT NULL, PRIMARY KEY (templateid), KEY libelle (libelle))");
	$DB_site2->query("INSERT INTO $bdd.template SELECT * FROM $bdd.templatebeta");
	$tp_done[$bdd] = "DONE" ;
	}
	
	
if (isset ($action) and $action == "mail")
	{
	include $DOCUMENT_ROOT."/config.php";
	include_once $DOCUMENT_ROOT."/mysql.php";
	$infos_bdd=$DB_site->query_first("select mail from aro_client ac, bou_client bc, bou_domaine bd where bd.clientid = bc.clientid
								and bc.username = ac.username and bd.tablename = '$bdd'");
	$sujet = "Mise à jour de votre boutique en ligne" ;
	$corps = "
	<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">
	<html>
	<head>
	<meta http-equiv=\"content-type\" content=\"text/html;charset=iso-8859-15\">
	</head>
	<body font-family=\"Verdana\">
	$msg
	</body>
	</html>";
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
	}

echo "<br><br><center><table border=\"1\" width=\"50%\">
		<tr>
			<td align=\"center\">
				<b>Base de données</b>
			</td>
			<td align=\"center\">
				<B>ACTIONS</b>
			</td>
		</tr>" ;

include "/home/arobases/www/global.php";
// pr chq base on affiche une ligne
$boutiques=$DB_site->query("select tablename from bou_domaine where tablename != ''");
while ($boutique=$DB_site->fetch_array($boutiques)) 
	{
	$nom_bdd = $boutique[tablename] ;
	echo "<tr>
			<td align=\"center\">
				$nom_bdd
			</td>
			<td align=\"center\">
				<table width=\"100%\">
					<tr>
						<td width=\"50%\" align=\"center\">
							<a href=\"maintenance.php?action=langues1&bdd=$nom_bdd\">Copier les langues</a>
							<font color=\"red\">&nbsp;&nbsp;$lg_done[$nom_bdd]</font>
						</td>
						<td width=\"50%\" align=\"center\">
							<a href=\"maintenance.php?action=templates1&bdd=$nom_bdd\">Passer la V2 en V1 (templates)</a>
							<font color=\"red\">&nbsp;&nbsp;$tp_done[$nom_bdd]</font>
						</td>
					</tr>
				</table>
			</td>
		</tr>" ;
	}
echo "</table></center>" ;	
	*/
?>
</body>
</html>