<?php

/*
 	SCRIPT : Pour chaque categorie, liste les articles les plus vendus (top ventes) 
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

$boutiques=$DB_site->query("SELECT domaine FROM client where boutique = '1' and location = '1' and demo = '0'") ;
while ($boutique=$DB_site->fetch_array($boutiques)) {
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
	
	$DB_site2->query("TRUNCATE TABLE temp_top_ventes") ;
	$date = date("Y-m-d") ;
	$categories=$DB_site2->query("SELECT * FROM categorie") ;
	while ($categorie=$DB_site2->fetch_array($categories)) {
		$tops=$DB_site2->query("SELECT lf.artid, a.catid, count(lf.artid) nb_ventes FROM lignefacture lf inner join article a using(artid) WHERE factureid in 
		(select facturid from facture where valide in(1, 0,5)) and a.catid = '$categorie[catid]' and active = '1' GROUP BY lf.artid");
		while ($top=$DB_site2->fetch_array($tops))
			$art=$DB_site2->query("INSERT INTO temp_top_ventes (catid, artid, nb) VALUES ('$categorie[catid]', '$top[artid]', '$top[nb_ventes]')") ;
		}
	}

?>