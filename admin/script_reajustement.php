<?php

ini_set("display_errors","on");

$rootpath="/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/";

$host="www.ma-cave-a-vin.fr" ;

include_once($rootpath."configurations/$host/config_mysql.php");
include($rootpath."class/mysql.php");
include_once($rootpath."admin/email.php");

$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();

$dayofweek=date("w");
$dayofmonth=intval(date("d"));

if($dayofweek==4 && ($dayofmonth>=15 || $dayofmonth<=21)){

    $from=new DateTime("first day of last month");

    $mail=new email();
    $mail->expediteur="contact@mon-espace-cuisson.com";
    $mail->expname="Ma Cave à Vin";
    $mail->destinataire="j3mexp@gmail.com";
    $mail->to=array("webmaster@mon-espace-cuisson.com","pr@mon-espace-cuisson.com");
//     $mail->destinataire="webmaster@mon-espace-cuisson.com";
    $mail->subject="Comptabilité mon espace cuisson";
    $mail->body="";
    $mail->attachfile("http://www.ma-cave-a-vin.fr/admin/export/export_reajustement.php?admin_username=9d3bb895f22bf0afa958d68c2a58ded7","reajustement-".$from->format("Ym").".ods","application/vnd.oasis.opendocument.spreadsheet-template");
   
    $mail->sendmail();
}

?>