<?php

ini_set("display_errors","on");

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

$ruser=array();
$requete=$DB_site->query("SELECT * FROM suivi_commande WHERE (rappel BETWEEN $today AND $tomorrow) AND pour<>'' ORDER BY rappel");
while($rappel=$DB_site->fetch_array($requete)){
    $facture=$DB_site->query_first("SELECT nom,prenom FROM facture WHERE facturid=".intval($rappel['facturid']));
    $ruser[$rappel['pour']]['commande'].="<tr><td>".date("d/m/Y H:i",$rappel['rappel'])."</td><td><a href=\"http://www.ma-cave-a-vin.fr/admin/facture.php?action=editer&facturid=".$rappel['facturid']."\">".$rappel['facturid']."</a></td><td>".$facture['prenom']." ".$facture['nom']."</td><td>".$rappel['suivi']."</td></tr>";
}

$requete=$DB_site->query("SELECT * FROM suivi_client WHERE (rappel BETWEEN $today AND $tomorrow) AND pour<>'' ORDER BY rappel");
while($rappel=$DB_site->fetch_array($requete)){
    $client=$DB_site->query_first("SELECT nom,prenom FROM utilisateur WHERE userid=".intval($rappel['userid']));
    $ruser[$rappel['pour']]['client'].="<tr><td>".date("d/m/Y H:i",$rappel['rappel'])."</td><td><a href=\"http://www.ma-cave-a-vin.fr/admin/utilisateur.php?action=editer&userid=".$rappel['userid']."\">".$client['prenom']." ".$client['nom']."</a></td><td>".$rappel['suivi']."</td></tr>";
}

$requete=$DB_site->query("SELECT * FROM sav_steps WHERE (rappel BETWEEN $today AND $tomorrow) AND pour<>'' ORDER BY rappel");
while($rappel=$DB_site->fetch_array($requete)){
	$sav=$DB_site->query_first("SELECT * FROM sav WHERE id=".$rappel['savid']);
	$facture=$DB_site->query_first("SELECT nom,prenom FROM facture WHERE facturid=".intval($sav['facturid']));
	$ruser[$rappel['pour']]['sav'].="<tr><td>".date("d/m/Y H:i",$rappel['rappel'])."</td><td>".$rappel['pour']."</td><td><a href=\"http://www.ma-cave-a-vin.fr/admin/facture.php?action=editer&facturid=$rappel[facturid]&savid=$sav[id]#sav\">".$rappel['facturid']."</a></td><td>".$facture['nom']." ".$facture['prenom']."</td><td>".stripslashes($rappel['texte'])."</td></tr>";
}

$requete=$DB_site->query("SELECT * FROM crm_calls WHERE ((reminder_time BETWEEN $today AND $tomorrow) || reminder_time=0) AND assigned_user_id<>'' ORDER BY assigned_user_id");
while($rappel=$DB_site->fetch_array($requete)){
    $account=$DB_site->query_first("SELECT * FROM crm_accounts WHERE id='$rappel[parent_id]'");
    $rappel['reminder_time']>0?$daterappel=date("d/m/Y H:i",$rappel['reminder_time']):$daterappel="";
    if($account['rating']==0 || $account['rating']==1){
	if($account['id']==""){
	    $contact=$DB_site->query_first("SELECT * FROM crm_contacts WHERE id='$rappel[parent_id]'");
	    $account=$DB_site->query_first("SELECT * FROM crm_accounts a INNER JOIN crm_accounts_contacts ac ON (a.id=ac.account_id) WHERE ac.contact_id='$contact[id]'");
	    $ruser[$rappel['assigned_user_id']]['crm'].="<tr><td>".$daterappel."</td><td><a href=\"http://www.ma-cave-a-vin.fr/admin/crm.php?action=detail&id=".$account['id']."\">".stripslashes($account['name'])." ".$contact['first_name']." ".$contact['last_name']."</a></td><td>".stripslashes($rappel['description'])."</td></tr>";
	}else
	    $ruser[$rappel['assigned_user_id']]['crm'].="<tr><td>".$daterappel."</td><td><a href=\"http://www.ma-cave-a-vin.fr/admin/crm.php?action=detail&id=".$account['id']."\">".stripslashes($account['name'])."</a></td><td>".stripslashes($rappel['description'])."</td></tr>";
    }
}


foreach($ruser as $name=>$rappels){
    $param=$DB_site->query_first("SELECT username,mail_contact,nom_contact FROM parametresgeneraux WHERE username='".$name."'");
    
    $body="<style>
    body{font-size:12px;}
    table {font-size:12px; margin-top:10px; border-collapse:collapse;}
    thead {font-weight:bold;}
    td { border:solid thin #808080; padding:2px;}
    </style>
    <p>Ci-joint les rappels que vous avez programmés :</p>";

    if(isset($rappels['commande'])){
	  $body.="<table>
	  <thead>
	  <tr><td>Date</td><td>Commande</td><td>Client</td><td>Suivi</td></tr>
	  </thead>
	  <tr><td colspan=\"4\" align=\"center\">Suivi commandes</td></tr>".$rappels['commande']."</table>";
    }
    if(isset($rappels['sav'])){
	  $body.="<table>
	  <thead>
	  <tr><td>Date</td><td>Commande</td><td>Client</td><td>Suivi</td></tr>
	  </thead>
	  <tr><td colspan=\"4\" align=\"center\">Suivi SAV</td></tr>".$rappels['sav']."</table>";
    }
    if(isset($rappels['client'])){
	  $body.="<table>
	  <thead>
	  <tr><td>Date</td><td>Client</td><td>Suivi</td></tr>
	  </thead><tr><td colspan=\"3\" align=\"center\">Suivi clients</td></tr>".$rappels['client']."</table>";
    }
    if(isset($rappels['crm'])){
	$body.="<table>
	<thead style=\"font-weight:bold;\">
	<tr><td>Date</td><td>Contact</td><td>Suivi</td></tr>
	</thead><tr><td colspan=\"3\" align=\"center\">Suivi CRM</td></tr>".$rappels['crm']."</table>";
    }

    $mail=new email();
    $mail->expediteur="contact@ma-cave-a-vin.fr";
    $mail->expname="Ma Cave à Vin";
    $mail->destinataire=$param['mail_contact'];
//     $mail->destinataire="webmaster@ma-cave-a-vin.fr";
    $mail->destname=$param['nom_contact'];
    $mail->to="webmaster@ma-cave-a-vin.fr";
    $mail->subject="Rappels du jour";
    $mail->body=$body;
    $mail->sendmail();
}

?>