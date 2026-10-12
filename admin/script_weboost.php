<?php
$rootpath="/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/";
// $rootpath="/var/www/macave/site/";

$host="www.ma-cave-a-vin.fr" ;
$nostat=1;
include_once($rootpath."configurations/$host/config_mysql.php");
include($rootpath."class/mysql.php");
include_once($rootpath."admin/email.php");


$destinataire="o.pezzetta@weboost-group.com";

$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();

$ts=time();
$m1s=$ts-7*86400;
$m1m=$ts-30*86400;

$mail=new email();
$mail->expediteur="contact@ma-cave-a-vin.fr";
$mail->expname="Ma Cave à Vin";
$mail->destinataire=$destinataire;
$mail->to=array("pr@ma-cave-a-vin.fr","webmaster@ma-cave-a-vin.fr");
$mail->subject="Stat commandes Ma Cave à Vin";
$mail->attachfile("http://www.ma-cave-a-vin.fr/admin/export/export_weboost.php","Ma Cave a Vin semaine ".date("W",$m1s).".ods","application/vnd.oasis.opendocument.spreadsheet");
if(date("d")<=7) $mail->attachfile("http://www.ma-cave-a-vin.fr/admin/export/export_weboost.php?mode=month&ts=$m1m","Ma Cave a Vin ".date("Y-m",$m1m).".ods","application/vnd.oasis.opendocument.spreadsheet");
$mail->sendmail();

?>