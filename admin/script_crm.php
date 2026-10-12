<?php

$rootpath="/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/";

$host="www.ma-cave-a-vin.fr" ;
$nostat=1;
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

$today=mktime(0,0,0,date("m"),date("d"),date("Y"));
$tomorrow=$today+86400;

$lastuser="";
$body="<style>
    body{font-size:12px;}
    table {font-size:12px; padding-top:10px; border-collapse:collapse;}
    thead {font-weight:bold;}
    td { border:solid thin #808080; padding:2px;}
    </style>
    <p>Rappels CRM programmés le ".date("d/m/Y")." </p>
    <table>
	<thead style=\"font-weight:bold;\">
	<tr><td>Date</td><td>Destinataire</td><td>Contact</td><td>Suivi</td></tr>
	</thead>";

$requete=$DB_site->query("SELECT * FROM crm_calls WHERE (reminder_time BETWEEN $today AND $tomorrow) AND assigned_user_id<>'' ORDER BY assigned_user_id,reminder_time");
while($rappel=$DB_site->fetch_array($requete)){
    if($lastuser!=$rappel['assigned_user_id']){
	$body.="<tr><td colspan=\"4\">&nbsp;</td></tr>";
	$lastuser=$rappel;
    }
    $account=$DB_site->query_first("SELECT * FROM crm_accounts WHERE id='$rappel[parent_id]'");
    if($account['id']==""){
	$contact=$DB_site->query_first("SELECT * FROM crm_contacts WHERE id='$rappel[parent_id]'");
	$account=$DB_site->query_first("SELECT * FROM crm_accounts a INNER JOIN crm_accounts_contacts ac ON (a.id=ac.account_id) WHERE ac.contact_id='$contact[id]'");
	$body.="<tr><td>".date("d/m/Y H:i",$rappel['reminder_time'])."</td><td>".$rappel['assigned_user_id']."</td><td><a href=\"https://www.ma-cave-a-vin.fr/admin/crm.php?action=detail&id=".$account['id']."\">".$account['name']." ".$contact['first_name']." ".$contact['last_name']."</a></td><td>".$rappel['description']."</td></tr>";
    }else
	$body.="<tr><td>".date("d/m/Y H:i",$rappel['reminder_time'])."</td><td>".$rappel['assigned_user_id']."</td><td><a href=\"https://www.ma-cave-a-vin.fr/admin/crm.php?action=detail&id=".$account['id']."\">".$account['name']."</a></td><td>".$rappel['description']."</td></tr>";
}

$body.="</table>";

if($DB_site->num_rows($requete)>0){
    $mail=new email();
    $mail->expediteur="contact@ma-cave-a-vin.fr";
    $mail->expname="Ma Cave à Vin";
    $mail->destinataire="mark1@ma-cave-a-vin.fr";
    $mail->to="pr@ma-cave-a-vin.fr";
    $mail->destname="";
    $mail->subject="Suivi CRM";
    $mail->body=$body;
    $mail->sendmail();
}

?>