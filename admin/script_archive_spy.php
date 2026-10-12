<?php

if(isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'localhost') !== false){ // LOCAL
    $rootpath   = "d:/wamp/www/ma-cave-a-vin/"; 
} else if(isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'test2015') !== false) {
    $rootpath   = "/home/ma-cave-a-vin-2015/";
} else if(isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'test') !== false) {
    $rootpath   = $_SERVER['DOCUMENT_ROOT']."/";
} else {
    $rootpath   = "/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/";
	$_SERVER['SERVER_NAME']='';
}

ini_set('display_errors',1);

$host="www.ma-cave-a-vin.fr" ;

include_once($rootpath."configurations/$host/config_mysql.php");
include($rootpath."class/mysql.php");
include_once($rootpath."class/email.php");

$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();

$timestampfrom = time()- (2*365*24*60*60);
echo $timestampfrom;
flush;
// exit;

$tabStat=array();

$sql = "DELETE FROM history ";
$sql.= " WHERE timestamp < " . $timestampfrom . "   ";
$query = $DB_site->query($sql);

echo "done";

?>