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
$nostat=1;
include_once($rootpath."configurations/$host/config_mysql.php");
include($rootpath."class/mysql.php");
include_once($rootpath."class/email.php");


//$destinataire="l.russo@adcom.fr";
// $destinataire="webmaster1@ma-cave-a-vin.fr";

$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();

/*
$ts=time();
$jour=date("w");
$d=date("d");
*/

$mail=new email();
$mail->expediteur="contact@ma-cave-a-vin.fr";
$mail->expname="Ma Cave à Vin";
$mail->destinataire=$destinataire;
// paola.vitale@adcom.fr
$to=array("contact@ma-cave-a-vin.fr","marketing@ma-cave-a-vin.fr");
$mail->body=" Le tableau du spy du jour est ci-joint. ";
$mail->subject="Spy Ma Cave à Vin";

/*
$dt=new datetime();
$ts2=$dt->setTime(0,0,0)->getTimestamp();
$dt->modify("previous day");
$ts1=$dt->setTime(0,0,0)->getTimestamp();
*/
$htmfile="https://www.ma-cave-a-vin.fr/admin/export/export_spy_adcom.php?admin_username=9d3bb895f22bf0afa958d68c2a58ded7&mode=free";
$mail->attachfile($htmfile,"Ma Cave a Vin SPY ".date("Y-m-d").".html","text/html");

//if($d==1 || $d==7){
	/*
    $m1m=date_create("first day of last month")->gettimestamp();
    $mail->attachfile("https://www.ma-cave-a-vin.fr/admin/export/export_spy_adcom.php?mode=month&ts=$m1m&admin_username=9d3bb895f22bf0afa958d68c2a58ded7","Ma Cave a Vin ".date("Y-m",$m1m).".ods","application/vnd.oasis.opendocument.spreadsheet");
	*/
    /*
	$m2m=date_create("first day of last month")->modify("1 month ago")->settime(0,0,0)->gettimestamp();
    $mail->attachfile("https://www.ma-cave-a-vin.fr/admin/export/export_spy_adcom.php?mode=month&ts=$m2m&admin_username=9d3bb895f22bf0afa958d68c2a58ded7","Ma Cave a Vin ".date("Y-m",$m2m).".ods","application/vnd.oasis.opendocument.spreadsheet");
    //$to[]="marketing1@ma-cave-a-vin.fr";
	*/
// }
/*
if($jour==1){
    $m1s=$ts-7*86400;
    $mail->attachfile("https://www.ma-cave-a-vin.fr/admin/export/export_spy_adcom.php?admin_username=9d3bb895f22bf0afa958d68c2a58ded7","Ma Cave a Vin semaine ".date("W",$m1s).".ods","application/vnd.oasis.opendocument.spreadsheet");
    //$to[]="marketing1@ma-cave-a-vin.fr";
}
*/

$mail->to=array_unique($to);
$mail->sendmail();


?>