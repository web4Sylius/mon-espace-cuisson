<?php
// MISE A JOUR PLAN TRANSPORT SCHENKER

if(isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'localhost') !== false){ //LOCAL
	$rootpath   = "d:/wamp/www/ma-cave-a-vin/";
} else if(isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'test') !== false){ //PREPROD
	$rootpath=dirname(dirname(__DIR__))."/";
} else { //PROD
	$rootpath=dirname(dirname(__DIR__))."/";
}


include($rootpath."configurations/www.ma-cave-a-vin.fr/config_mysql.php");
include($rootpath."class/mysql.php");
include($rootpath."configurations/www.ma-cave-a-vin.fr/transporteurs/schenker/schenker.php");
include($rootpath."admin/adv/schenker.class.php");

$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();



$schenker = new schenker($DB_site,null,$compte_facturation,$ftp);

$schenker->update_plan();
