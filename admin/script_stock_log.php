<?php

/* **********************************************
CE SCRIPT NE DOIT ETRE EXECUTE QU UNE SEULE FOIS

LE BUT EST DE SAUVEGARDER L'ETAT ACTUEL DU STOCK DANS LA BASE DE LOG_URL

CE SCRIPT NE DOIT ETRE EXECUTE QU UNE SEULE FOIS
********************************************** */


ini_set("display_errors","1");
error_reporting(E_ALL);
ini_set("memory_limit","1024M");
set_time_limit(2000);

if(isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'localhost') !== false){ // LOCAL
    $rootpath   = "d:/wamp/www/ma-cave-a-vin/"; 
    $mondomaine = "www.ma-cave-a-vin.fr";
    $host       = "www.ma-cave-a-vin.fr";
	define('MAIL_DEV', 'webmaster1@ma-cave-a-vin.fr');
    define('MAIL_DEV_SENDER', 'webmaster1@ma-cave-a-vin.fr');
    define('MAIL_COMMERCE','webmaster1@ma-cave-a-vin.fr');
	define('MAIL_COMPTABLE', 'webmaster1@ma-cave-a-vin.fr');
    define('MAIL_COMPTABLE2', 'webmaster1@ma-cave-a-vin.fr');
    define('MAIL_DIRECTION', 'webmaster1@ma-cave-a-vin.fr');
    define('MAIL_COMMERCE2', 'webmaster1@ma-cave-a-vin.fr');
    define('MAIL_CONTACT', 'webmaster1@ma-cave-a-vin.fr');
    define('MAIL_ADV', 'webmaster1@ma-cave-a-vin.fr');
    define('SITE_PREFIX', 'localhost');
    define('SITE_HOST', 'localhost.ma-cave-a-vin.fr');
    define('SITE_URL', 'http://localhost.ma-cave-a-vin.fr');
    define('BACK_URL', 'http://localhost.ma-cave-a-vin.fr/admin');
    define('SITE_PATH', 'd:/Wamp/www/ma-cave-a-vin');
    define('BACK_PATH', 'd:/Wamp/www/ma-cave-a-vin/admin');
    define('BACK_HOST', 'http://localhost.ma-cave-a-vin.fr/admin');
    define('ROOT_PATH', $rootpath);
    define('LOG_URL', 'd:/Wamp/www/ma-cave-a-vin/log');
    define('ESPACE', 'preprod');
} else if(isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'test2015') !== false) {
    $rootpath   = "/home/ma-cave-a-vin-2015/";
    $mondomaine = "www.ma-cave-a-vin.fr" ;
    $host       = "www.ma-cave-a-vin.fr" ;
	define('MAIL_DEV', 'webmaster@ma-cave-a-vin.fr,webmaster1@ma-cave-a-vin.fr');
    define('MAIL_DEV_SENDER', 'webmaster@ma-cave-a-vin.fr');
    define('MAIL_COMMERCE','webmaster1@ma-cave-a-vin.fr');
	define('MAIL_COMPTABLE', 'webmaster1@ma-cave-a-vin.fr');
    define('MAIL_COMPTABLE2', 'webmaster1@ma-cave-a-vin.fr');
    define('MAIL_DIRECTION', 'webmaster1@ma-cave-a-vin.fr');
    define('MAIL_COMMERCE2', 'webmaster1@ma-cave-a-vin.fr');
    define('MAIL_CONTACT', 'webmaster1@ma-cave-a-vin.fr');
    define('MAIL_ADV', 'webmaster1@ma-cave-a-vin.fr');
    define('SITE_PREFIX', 'test2015');
    define('SITE_HOST', 'test2015.ma-cave-a-vin.fr');
    define('SITE_URL', 'http://test2015.ma-cave-a-vin.fr');
    define('BACK_URL', 'http://test2015.ma-cave-a-vin.fr/admin');
    define('SITE_PATH', '/home/ma-cave-a-vin-2015');
    define('BACK_PATH', '/home/ma-cave-a-vin-2015/admin');
	define('BACK_HOST', 'http://test2015.ma-cave-a-vin.fr/admin');
    define('ROOT_PATH', $rootpath);
    define('LOG_URL', '/home/ma-cave-a-vin-2015/log');
	define('ESPACE', 'preprod');
} else if(isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'test') !== false) {
    $rootpath   = $_SERVER['DOCUMENT_ROOT']."/";
    $mondomaine = "www.ma-cave-a-vin.fr" ;
    $host       = "www.ma-cave-a-vin.fr" ;
	define('MAIL_DEV', 'webmaster@ma-cave-a-vin.fr,webmaster1@ma-cave-a-vin.fr');
    define('MAIL_DEV_SENDER', 'webmaster@ma-cave-a-vin.fr');
    define('MAIL_COMMERCE','webmaster1@ma-cave-a-vin.fr');
	define('MAIL_COMPTABLE', 'webmaster1@ma-cave-a-vin.fr');
    define('MAIL_COMPTABLE2', 'webmaster1@ma-cave-a-vin.fr');
    define('MAIL_DIRECTION', 'webmaster1@ma-cave-a-vin.fr');
    define('MAIL_COMMERCE2', 'webmaster1@ma-cave-a-vin.fr');
    define('MAIL_CONTACT', 'webmaster1@ma-cave-a-vin.fr');
    define('MAIL_ADV', 'webmaster1@ma-cave-a-vin.fr');
    define('SITE_PREFIX', 'test');
    define('SITE_HOST', 'test.ma-cave-a-vin.fr');
    define('SITE_URL', 'http://test.ma-cave-a-vin.fr');
    define('BACK_URL', 'http://test.ma-cave-a-vin.fr/admin');
    define('SITE_PATH', $rootpath);
    define('BACK_PATH', $rootpath.'admin');
	define('BACK_HOST', 'http://test.ma-cave-a-vin.fr/admin');
    define('ROOT_PATH', $rootpath);
    define('LOG_URL', '/var/www/ma-cave-a-vin.fr/log');
	define('ESPACE', 'preprod');
} else {
    $rootpath   = "/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/";
    $mondomaine = "www.ma-cave-a-vin.fr" ;
    $host       = "www.ma-cave-a-vin.fr" ;
	define('MAIL_DEV', 'webmaster@ma-cave-a-vin.fr,webmaster1@ma-cave-a-vin.fr');
    define('MAIL_DEV_SENDER', 'webmaster@ma-cave-a-vin.fr');
    define('MAIL_COMMERCE','contact@ma-cave-a-vin.fr');
	define('MAIL_COMPTABLE', 'j3mexp@gmail.com');
    define('MAIL_COMPTABLE2', 'jm.coubes@acilyon.com');
    define('MAIL_DIRECTION', 'pr@ma-cave-a-vin.fr');
    define('MAIL_COMMERCE2', 'cecile@ma-cave-a-vin.fr');
    define('MAIL_CONTACT', 'contact@ma-cave-a-vin.fr');
    define('MAIL_ADV', 'adv@ma-cave-a-vin.fr');
    define('SITE_PREFIX', 'www');
    define('SITE_HOST', 'www.ma-cave-a-vin.fr');
    define('SITE_URL', 'https://www.ma-cave-a-vin.fr');
    define('BACK_URL', 'https://www.ma-cave-a-vin.fr/admin');
    define('SITE_PATH', '/var/www/vhosts/ma-cave-a-vin.fr/httpdocs');
    define('BACK_PATH', '/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/admin');
	define('BACK_HOST', 'https://www.ma-cave-a-vin.fr/admin');
    define('ROOT_PATH', $rootpath);
    define('LOG_URL', '/var/www/vhosts/ma-cave-a-vin.fr/log');
	define('ESPACE', 'prod');
}

ini_set('display_errors',1);
// $_SERVER['SERVER_NAME']='';

$host="www.ma-cave-a-vin.fr" ;
$nostat=1;
require_once($rootpath."configurations/$host/config_mysql.php");
require($rootpath."class/mysql.php");
require_once($rootpath."class/stock.class.php");
/*
include($rootpath."class/fonctions.php");
include($rootpath."class/devises.class.php");
include($rootpath."class/template.class.php");
include($rootpath."configurations/$host/config.php");
*/
// echo "on commence";

$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();

// ON NE DOIT PLUS JAMAIS EXECUTER CE SCRIPT !!!!!!
?>
$stock = new stock($DB_site);
$lieux = $stock->lieux();
$sql = "SELECT artid FROM article ORDER BY artid DESC "; // LIMIT 1000
$articles = $DB_site->query($sql);
while($article=$DB_site->fetch_array($articles)){
	echo "on fait l'article N°".$article[artid];
	$i=0;
	foreach($lieux as $lieuid => $lieu){
		$lesstocks=$stock->search($article[artid], $lieuid );
		if(count($lesstocks)>0){
			echo " -> il est en stock à ".$lieuid." -> (".count($lesstocks).") ";
			$i++;
			//print_r($lesstocks);
			foreach($lesstocks as $stock_itemid => $res){
				$sqllog="INSERT INTO stock_log
						(stock_logid, artid, dateline, stock_table, stock_table_operation, stock_cmd_id, stock_item_id, stock_ajust_id, suivi_stock_id, username, lieu_stockage_id, mouvement, log_sql)
						VALUES 
						(NULL, '".$article[artid]."', '".time()."', 'stock_item', 'insert', 0, '".$stock_itemid."', 0, 0, 'benoit', '".$lieuid."', 1, '".mysql_real_escape_string("/* manuel stock script_stock_log.php */")."' )";
				// echo $sqllog;
				$DB_site->query($sqllog);
			}
		}
		$lesresas=$stock->search($article[artid], $lieuid, null, array(2) );
		if(count($lesresas)>0){
			echo " -> il est en résa à ".$lieuid." -> (".count($lesresas).") ";
			$i++;
			//print_r($lesresas);
			foreach($lesresas as $stock_itemid => $res){
				$sqllog="INSERT INTO stock_log
						(stock_logid, artid, dateline, stock_table, stock_table_operation, stock_cmd_id, stock_item_id, stock_ajust_id, suivi_stock_id, username, lieu_stockage_id, mouvement, log_sql)
						VALUES 
						(NULL, '".$article[artid]."', '".time()."', 'stock_item', 'insert', 0, '".$stock_itemid."', 0, 0, 'benoit', '".$lieuid."', 1, '".mysql_real_escape_string("/* manuel résa script_stock_log.php */")."' )";
				// echo $sqllog;
				$DB_site->query($sqllog);
			}
		}
	}
	if($i==0){
		echo " -> pas en stock";
	}
	echo "<br><br>";
	flush;
}
<?php


?>