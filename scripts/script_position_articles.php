<?php

/*
 	SCRIPT : Pour chaque categorie, affecte position aux articles 
			
	DATE : 21/04/2008
	AUTEUR : A. Ogier
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
	
	
	echo $boutique[domaine]."<br>";
	$categories=$DB_site2->query("SELECT * FROM categorie") ;
	while ($categorie=$DB_site2->fetch_array($categories))
		{
		$position = 1;
		$articles = $DB_site2->query("SELECT * FROM article WHERE catid = '$categorie[catid]' ORDER BY libelleFR");
		while ($article=$DB_site2->fetch_array($articles))
			{
			$DB_site2->query("UPDATE article SET page = '1', pageposition = '$position' WHERE artid = '$article[artid]'");
			$position ++;
			}
		}
	}

?>