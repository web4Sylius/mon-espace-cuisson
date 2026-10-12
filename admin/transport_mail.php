#!/usr/bin/php

<?php
require_once __DIR__.'/../secrets.php';

// ATTENTION : la modification de ce fichier risque de bloquer la distribution des emails

$db_user=MAILDB_USER;
$db_pass=MAILDB_PASSWORD;
$db_base="postfix";

$vmailboxes="/home/vmailboxes";

$log="/var/log/mail.transport";

$db=new mysqli("127.0.0.1",$db_user,$db_pass,$db_base);
if($db->connect_error){
    loginfo("Erreur : connexion mysql ".$db->connect_error);
    exit;
}

$fd = fopen("php://stdin", "r");
$email = "";
while (!feof($fd)) {
$email .= fread($fd, 1024);
}
fclose($fd);

array_shift($argv);
//loginfo(var_export($argv,true));

foreach($argv as $recipient){
    $result=$db->query("SELECT CONCAT(SUBSTRING_INDEX(email,'@',-1),'/',SUBSTRING_INDEX(email,'@',1),'/') FROM users WHERE email='".$recipient."'");
    if($result==false){
	loginfo("Erreur : requête pour ".$recipient);
	exit;
    }
    if($result->num_rows==0){
	loginfo("Error : Adresse ".$recipient." non configurée");
	exit;
    }
    $emaildest=$result->fetch_row();
    $maildir=$vmailboxes."/".$emaildest[0];
    if(!is_dir($maildir)){
	loginfo("Info : dossier ".$maildir." inexistant");
	mkdir($maildir);
    }

    $mailfile=$maildir."new/".uniqid()."_".time();
    $fw=fopen($mailfile,"w");
    fwrite($fw,$email);
    fclose($fw);

    loginfo($recipient." > ".$mailfile);
}

include("/home/mon-espace-cuisson.com/admin/rcv_mail.php");

function loginfo($txt){
    global $log;

    $f=fopen($log,"a");
    if($f==false) return false;
    fwrite($f,date("d/m/Y H:i:s")." ".$txt."\n");
    fclose($f);
}

?>
