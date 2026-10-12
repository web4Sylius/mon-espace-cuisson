<?php

ini_set("display_errors","1");
error_reporting(E_ALL);
ini_set("memory_limit","1024M");

if(isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'localhost') !== false){ // LOCAL
    $rootpath   = "http://localhost/mec"; 
    $mondomaine = "www.mon-espace-cuisson.com";
    $host       = "www.mon-espace-cuisson.com";
	define('MAIL_DEV', 'webmaster1@mon-espace-cuisson.com');
    define('MAIL_DEV_SENDER', 'webmaster1@mon-espace-cuisson.com');
    define('MAIL_COMMERCE','webmaster1@mon-espace-cuisson.com');
	define('MAIL_COMPTABLE', 'webmaster1@mon-espace-cuisson.com');
    define('MAIL_COMPTABLE2', 'webmaster1@mon-espace-cuisson.com');
    define('MAIL_DIRECTION', 'webmaster1@mon-espace-cuisson.com');
    define('MAIL_COMMERCE2', 'webmaster1@mon-espace-cuisson.com');
    define('MAIL_CONTACT', 'webmaster1@mon-espace-cuisson.com');
    define('MAIL_ADV', 'webmaster1@mon-espace-cuisson.com');
    define('SITE_PREFIX', 'localhost');
    define('SITE_HOST', 'localhost.mon-espace-cuisson.com');
    define('SITE_URL', 'http://localhost/mec');
    define('BACK_URL', 'http://localhost/mec/admin');
    define('SITE_PATH', 'dhttp://localhost/mec');
    define('BACK_PATH', 'http://localhost/mec/admin');
    define('BACK_HOST', 'http://localhost/mec/admin');
    define('ROOT_PATH', $rootpath);
    define('LOG_URL', 'd:/Wamp/www/ma-cave-a-vin/log');
    define('ESPACE', 'preprod');
} else if(isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'test2015') !== false) {
    $rootpath   = "/home/ma-cave-a-vin-2015/";
    $mondomaine = "www.mon-espace-cuisson.com" ;
    $host       = "www.mon-espace-cuisson.com" ;
	define('MAIL_DEV', 'webmaster@mon-espace-cuisson.com,webmaster1@mon-espace-cuisson.com');
    define('MAIL_DEV_SENDER', 'webmaster@mon-espace-cuisson.com');
    define('MAIL_COMMERCE','webmaster1@mon-espace-cuisson.com');
	define('MAIL_COMPTABLE', 'webmaster1@mon-espace-cuisson.com');
    define('MAIL_COMPTABLE2', 'webmaster1@mon-espace-cuisson.com');
    define('MAIL_DIRECTION', 'webmaster1@mon-espace-cuisson.com');
    define('MAIL_COMMERCE2', 'webmaster1@mon-espace-cuisson.com');
    define('MAIL_CONTACT', 'webmaster1@mon-espace-cuisson.com');
    define('MAIL_ADV', 'webmaster1@mon-espace-cuisson.com');
    define('SITE_PREFIX', 'test2015');
    define('SITE_HOST', 'test2015.mon-espace-cuisson.com');
    define('SITE_URL', 'http://test2015.mon-espace-cuisson.com');
    define('BACK_URL', 'http://test2015.mon-espace-cuisson.com/admin');
    define('SITE_PATH', '/home/ma-cave-a-vin-2015');
    define('BACK_PATH', '/home/ma-cave-a-vin-2015/admin');
	define('BACK_HOST', 'http://test2015.mon-espace-cuisson.com/admin');
    define('ROOT_PATH', $rootpath);
    define('LOG_URL', '/home/ma-cave-a-vin-2015/log');
	define('ESPACE', 'preprod');
} else if(isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'test') !== false) {
    $rootpath   = $_SERVER['DOCUMENT_ROOT']."/";
    $mondomaine = "www.mon-espace-cuisson.com" ;
    $host       = "www.mon-espace-cuisson.com" ;
	define('MAIL_DEV', 'webmaster@mon-espace-cuisson.com,webmaster1@mon-espace-cuisson.com');
    define('MAIL_DEV_SENDER', 'webmaster@mon-espace-cuisson.com');
    define('MAIL_COMMERCE','webmaster1@mon-espace-cuisson.com');
	define('MAIL_COMPTABLE', 'webmaster1@mon-espace-cuisson.com');
    define('MAIL_COMPTABLE2', 'webmaster1@mon-espace-cuisson.com');
    define('MAIL_DIRECTION', 'webmaster1@mon-espace-cuisson.com');
    define('MAIL_COMMERCE2', 'webmaster1@mon-espace-cuisson.com');
    define('MAIL_CONTACT', 'webmaster1@mon-espace-cuisson.com');
    define('MAIL_ADV', 'webmaster1@mon-espace-cuisson.com');
    define('SITE_PREFIX', 'test');
    define('SITE_HOST', 'test.mon-espace-cuisson.com');
    define('SITE_URL', 'http://test.mon-espace-cuisson.com');
    define('BACK_URL', 'http://test.mon-espace-cuisson.com/admin');
    define('SITE_PATH', $rootpath);
    define('BACK_PATH', $rootpath.'admin');
	define('BACK_HOST', 'http://test.mon-espace-cuisson.com/admin');
    define('ROOT_PATH', $rootpath);
    define('LOG_URL', '/var/www/mon-espace-cuisson.com/log');
	define('ESPACE', 'preprod');
} else {
    $rootpath   = "/var/www/vhosts/mon-espace-cuisson.com/httpdocs/";
    $mondomaine = "www.mon-espace-cuisson.com" ;
    $host       = "www.mon-espace-cuisson.com" ;
	define('MAIL_DEV', 'webmaster@mon-espace-cuisson.com,webmaster1@mon-espace-cuisson.com');
    define('MAIL_DEV_SENDER', 'webmaster@mon-espace-cuisson.com');
    define('MAIL_COMMERCE','contact@mon-espace-cuisson.com');
	define('MAIL_COMPTABLE', 'j3mexp@gmail.com');
    define('MAIL_COMPTABLE2', 'jm.coubes@acilyon.com');
    define('MAIL_DIRECTION', 'pr@mon-espace-cuisson.com');
    define('MAIL_COMMERCE2', 'cecile@mon-espace-cuisson.com');
    define('MAIL_CONTACT', 'contact@mon-espace-cuisson.com');
    define('MAIL_ADV', 'adv@mon-espace-cuisson.com');
    define('SITE_PREFIX', 'www');
    define('SITE_HOST', 'www.mon-espace-cuisson.com');
    define('SITE_URL', 'https://www.mon-espace-cuisson.com');
    define('BACK_URL', 'https://www.mon-espace-cuisson.com/admin');
    define('SITE_PATH', '/var/www/vhosts/mon-espace-cuisson.com/httpdocs');
    define('BACK_PATH', '/var/www/vhosts/mon-espace-cuisson.com/httpdocs/admin');
	define('BACK_HOST', 'https://www.mon-espace-cuisson.com/admin');
    define('ROOT_PATH', $rootpath);
    define('LOG_URL', '/var/www/vhosts/mon-espace-cuisson.com/log');
	define('ESPACE', 'prod');
}

ini_set('display_errors',1);
// $_SERVER['SERVER_NAME']='';

$host="www.mon-espace-cuisson.com" ;
$nostat=1;
require_once($rootpath."configurations/$host/config_mysql.php");
require($rootpath."class/mysql.php");
require_once($rootpath."class/email2.php");

$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();


$mail=new email();
// $mail->expediteur="pr@my-wine-cabinet.com";
$mail->expediteur="webmaster2@mon-espace-cuisson.com";
$mail->expname="Ma Cave à Vin";
$mail->destinataire="webmaster1@mon-espace-cuisson.com";
$mail->to="pr@my-wine-cabinet.com";
$mail->subject="SPY";
$mail->body=date("d/M/Y")." : ".$serverinfo;
// $dt=new datetime();
// $ts2=$dt->setTime(0,0,0)->getTimestamp();
// $dt->modify("previous day");
// $ts1=$dt->setTime(0,0,0)->getTimestamp();
// $odsfile="https://www.mon-espace-cuisson.com/admin/export/export_adcom.php?admin_username=9d3bb895f22bf0afa958d68c2a58ded7&ts=$ts1&ts2=$ts2&mode=free";
// echo $odsfile."<br>";
// $mail->attachfile($odsfile,"Ma Cave a Vin ".date("Y-m-d",$ts1).".ods","application/vnd.oasis.opendocument.spreadsheet");
// $mail->attachstring($data,"spy_".date("Y-m-d").".ods","application/vnd.oasis.opendocument.spreadsheet");
$mail->sendmail();




?>