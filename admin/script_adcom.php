<?php

ini_set("display_errors","1");
error_reporting(E_ALL);
ini_set("memory_limit","1024M");
set_time_limit(600);
ini_set('display_errors',1);

$nostat=1;
include_once("host_config_admin.php");
include_once($rootpath."class/email.php");


//$destinataire="l.russo@adcom.fr";
 //$destinataire="webmaster3@mon-espace-cuisson.com";

$DB_site=clone $DB_site2;

$ts=time();
$jour=date("w");
$d=date("d");


$mail=new email();
$mail->expediteur="contact@mon-espace-cuisson.com";
$mail->expname="Ma Cave à Vin";
$mail->destinataire=$destinataire;
// paola.vitale@adcom.fr
//$to=array("e.rey@adcom.fr");
//$to=array("webmaster1@mon-espace-cuisson.com");
$mail->subject="Stat commandes Ma Cave à Vin";
$mail->body=" Les stats de commandes sont ci-jointes. ";

$dt=new datetime();
$ts2=$dt->setTime(0,0,0)->getTimestamp();
$dt->modify("previous day");
$ts1=$dt->setTime(0,0,0)->getTimestamp();
$odsfile1="http://dedie.ma-cave-a-vin.fr/admin/export/export_adcom.php?admin_username=9d3bb895f22bf0afa958d68c2a58ded7&ts=$ts1&ts2=$ts2&mode=free&request=cde";
$odsfile2="http://dedie.ma-cave-a-vin.fr/admin/export/export_adcom.php?admin_username=9d3bb895f22bf0afa958d68c2a58ded7&ts=$ts1&ts2=$ts2&mode=free&request=vente";
$odsfile3="http://dedie.ma-cave-a-vin.fr/admin/export/export_adcom.php?admin_username=9d3bb895f22bf0afa958d68c2a58ded7&ts=$ts1&ts2=$ts2&mode=free&request=article";


// juillet 2019
// https://dedie.ma-cave-a-vin.fr/admin/export/export_adcom.php?mode=month&ts=1561939200&admin_username=9d3bb895f22bf0afa958d68c2a58ded7



$mail->attachfile($odsfile1,"MCAV_Commandes_".date("Y-m-d",$ts1).".ods","application/vnd.oasis.opendocument.spreadsheet");
$mail->attachfile($odsfile2,"MCAV_Meilleures_Ventes_".date("Y-m-d",$ts1).".ods","application/vnd.oasis.opendocument.spreadsheet");
$mail->attachfile($odsfile3,"MCAV_Articles_".date("Y-m-d",$ts1).".ods","application/vnd.oasis.opendocument.spreadsheet");

if($d==1 || $d==7){
    $m1m=date_create("first day of last month")->gettimestamp();
    $mail->attachfile("https://dedie.ma-cave-a-vin.fr/admin/export/export_adcom.php?mode=month&ts=$m1m&admin_username=9d3bb895f22bf0afa958d68c2a58ded7&request=all","Ma Cave a Vin ".date("Y-m",$m1m).".ods","application/vnd.oasis.opendocument.spreadsheet");
    $m2m=date_create("first day of last month")->modify("1 month ago")->settime(0,0,0)->gettimestamp();
    $mail->attachfile("http://dedie.ma-cave-a-vin.fr/admin/export/export_adcom.php?mode=month&ts=$m2m&admin_username=9d3bb895f22bf0afa958d68c2a58ded7&request=all","Ma Cave a Vin ".date("Y-m",$m2m).".ods","application/vnd.oasis.opendocument.spreadsheet");
    $to[]="marketing1@mon-espace-cuisson.com";
}
if($jour==1){
    $m1s=$ts-7*86400;
    $mail->attachfile("http://dedie.ma-cave-a-vin.fr/admin/export/export_adcom.php?admin_username=9d3bb895f22bf0afa958d68c2a58ded7&request=all","Ma Cave a Vin semaine ".date("W",$m1s).".ods","application/vnd.oasis.opendocument.spreadsheet");
    $to[]="marketing1@mon-espace-cuisson.com";
}

$mail->to=array_unique($to);
$mail->sendmail();


?>