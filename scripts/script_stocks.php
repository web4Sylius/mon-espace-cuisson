<?php
require_once __DIR__.'/../secrets.php';

/*
 	SCRIPT : Liste les articles ayant dépassé le seuil d'alerte et envoie un mail au responsable de la boutique 
			 A éxécuter tous les jours sur les boutiques
	DATE : 03/04/2006
	AUTEUR : C. Jullien
*/

include "/home/arobases/www/config.php";
include_once "/home/arobases/www/mysql.php";
$DB_site=new DB_Sql;
$DB_site->appname='arobases';
$DB_site->appshortname='arobases';
$DB_site->database=$dbname;
$DB_site->server=$servername;
$DB_site->user=$dbusername;
$DB_site->password=$dbpassword;
$DB_site->connect();
$dbpassword="";
$DB_site->password="";

$boutiques=$DB_site->query("select domaine from client where demo = '0' and boutique = '1' and location = '1'");
while ($boutique=$DB_site->fetch_array($boutiques))
	{
	include "/home/boutique/www/configurations/$boutique[domaine]/config.php";
	$DB_site2=new DB_Sql;
	$DB_site2->appname='boutique';
	$DB_site2->appshortname='boutique';
	$DB_site2->database=$DBname;
	$DB_site2->server=$db_server;
	$DB_site2->user=$DBusername;
	$DB_site2->password=$DBpassword;
	$DB_site2->connect();
	$DBpassword="";

	
	// nom de domaine pour gmail
	$mydomaine = explode(".", $host) ;
	$h = 1 ;
	$mondom = "" ;
	while ($h < count($mydomaine))
		{
		if ($h == 1)
			{
			$mondom .= $mydomaine[$h] ;
			}
		else
			{
			$mondom .= "." . $mydomaine[$h] ;
			}
		$h++ ;
		}
	
	
	if (in_array ("4", $modules ))
		{
		$liste_articles = "" ;
		$stocks=$DB_site2->query("SELECT * FROM stock s, article a where a.artid = s.artid and s.nombre < s.seuil") ;
		while ($stock=$DB_site2->fetch_array($stocks))
			{
			$liste_articles .= "<li><font color=\"#6699FF\">$stock[artcode] <b>$stock[libelleFR]</b></font> : plus que $stock[nombre] (seuil=$stock[seuil], delai=$stock[delaiappro]).</li>" ;
			}
		if ($liste_articles != "")
			{
			$boutiq=$DB_site->query_first("SELECT username, password FROM aro_client where domaine = '$host'") ;
			$destinataire=$DB_site2->query_first("SELECT mail_contact FROM parametresgeneraux") ;
			$destinataire = $destinataire[0] ;
			$contenu = "<font face=\"Verdana\" size=\"2px\"><br><br><b>Administration de votre boutique en ligne $boutique[domaine].</B><br><br>
			Nous vous informons que certains de vos articles ont dépassé leur seuil d'alerte.<br>
			Retrouvez-les dans la liste ci-dessous : <br>
			<ul>$liste_articles</ul>
			<br>
			Retrouvez le récapitulatif de vos stocks à cette adresse :<br>
			<a href=\"http://www.vendre-en-ligne.fr/admin/recapstocks.php?username=$boutiq[username]&passcode=$boutiq[password]\">http://www.vendre-en-ligne.fr/admin/recapstocks.php?username=$boutiq[username]&passcode=$boutiq[password]</a></font>" ;
			$mailhead .= "From: webmaster@vendre-en-ligne\r\n";
			$mailhead .= "Content-type: text/html; charset=iso-8859-1\r\n";
			require_once("../gmail/smtp.php");
			require_once("../gmail/sasl.php");
			$from="arobases@$mondom";
			$smtppass=SMTP_PASSWORD_2;
			$localhost="$mondom";
			$abuse="arobases@$mondom";
			$namefrom="${$title}";
			$to=$destinataire;
			$subject="[vendre-en-ligne.fr] $sujet";
			$message=$contenu ;
			gmail($from,$to,$subject,$smtppass,$localhost,$namefrom,$abuse,$message);
			//mail($destinataire, "[vendre-en-ligne.fr] $sujet", $contenu, $mailhead); 
			}
		}
	}

?>