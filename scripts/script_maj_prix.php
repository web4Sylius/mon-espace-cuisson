<?php
set_time_limit(1200) ; 

$passession = "oui" ;
include_once "/home/macavavin/www/mysql.php";
include "/home/macavavin/www/configurations/www.ma-cave-a-vin.fr/config.php";

$DB_site2=new DB_Sql;
$DB_site2->appname='boutique';
$DB_site2->appshortname='boutique';
$DB_site2->database=$DBname;
$DB_site2->server=$db_server;
$DB_site2->user=$DBusername;
$DB_site2->password=$DBpassword;
$DB_site2->connect();
$DBpassword="";

$articles=$DB_site2->query("select * from article where artid not in (26,138) order by artid") ;
while ($article=$DB_site2->fetch_array($articles))
	{
	/*$newprixht = $article[prixht] * 1.1;
	$newprixht = number_format($newprixht, 2, '.', '');
	echo "<b>$article[artid] ==></b> HT avant:".$article[prixht]." après:".$newprixht."<br>";
	$newprix = $newprixht * 1.196;
	$newprix = number_format($newprix, 2, '.', '');
	echo "<b>$article[artid] ==></b> TTC avant:".$article[prix]." après:".$newprix."<br>";
	$DB_site2->query("UPDATE article SET prix = '$newprix', prixht = '$newprixht' WHERE artid = '$article[artid]'");*/

	$newprixht = $article[prixconseilleht];
	$newprixht = number_format($newprixht, 2, '.', '');
	echo "<b>$article[artid] ==></b> HT avant:".$article[prixht]." après:".$newprixht."<br>";
	$newprix = $newprixht * 1.196;
	$newprix = number_format($newprix, 2, '.', '');
	echo "<b>$article[artid] ==></b> TTC avant:".$article[prix]." après:".$newprix."<br>";
	$DB_site2->query("UPDATE article SET prix = '$newprix', prixht = '$newprixht' WHERE artid = '$article[artid]'");
	
	}

?>
