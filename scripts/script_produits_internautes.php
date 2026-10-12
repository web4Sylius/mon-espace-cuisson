<?
set_time_limit(600) ; 

/*
 	SCRIPT : Pour chaque article, liste les articles achetés en même temps par les internautes 
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
	
	$del=$DB_site2->query("TRUNCATE TABLE temp_produits_internautes") ;
	$date = date("Y-m-d") ;
	$articles=$DB_site2->query("SELECT * FROM article where active = '1'") ;
	while ($article=$DB_site2->fetch_array($articles)) {
		$facts=$DB_site2->query("SELECT factureid FROM lignefacture WHERE artid = $article[artid]") ;
		$liste_facts = "0," ;
		while ($fact=$DB_site2->fetch_array($facts))
			$liste_facts .= $fact[factureid]."," ;
		$lg = strlen($liste_facts) - 1 ;
		$liste_facts = substr($liste_facts, 0, $lg) ;
		$articles_mm_tps=$DB_site2->query("SELECT a.artid FROM article a, lignefacture lf WHERE a.artid = lf.artid AND lf.factureid in ($liste_facts)
										  AND a.artid != $article[artid] and a.active = '1' group by libelleFR");
		while ($article_mm_tps=$DB_site2->fetch_array($articles_mm_tps))
			$art=$DB_site2->query("INSERT INTO temp_produits_internautes (artid_base, artid) VALUES ('$article[artid]', '$article_mm_tps[artid]')") ;
		}
	}

?>