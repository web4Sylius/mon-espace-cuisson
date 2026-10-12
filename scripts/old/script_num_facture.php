<?php
set_time_limit(600) ; 

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

$boutiques=$DB_site->query("SELECT distinct(domaine) FROM bou_domaine bd, bou_client bc where  bc.clientid = bd.clientid 
							and tablename != '' and bc.username != 'pretaporter' and bc.username != 'demimesure' 
							and bc.username != 'surmesure' and bc.demo = '0'") ;

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
		
	$num=$DB_site2->query_first("SELECT max(numerofacture) FROM facture");
	
	$numero = $num[0] ;
	
	$factures=$DB_site2->query("SELECT * FROM facture where valide = '5' order by dateexpedition");
	
	while($facture=$DB_site2->fetch_array($factures)) 
	
				{
	
				$numero++ ;
	
				echo "update facture set numerofacture = '$numero' where facturid = '$facture[facturid]'<br>" ;
	
				$DB_site2->query("update facture set numerofacture = '$numero' where facturid = '$facture[facturid]'");
	
				}
	}
?>
