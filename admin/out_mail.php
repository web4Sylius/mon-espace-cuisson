#!/usr/bin/php

<?php
//plus utilisé

ini_set("display_errors","on");
@ini_set('memory_limit', '128M');

$rootpath="/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/";
// $rootpath="../";

$host="www.ma-cave-a-vin.fr" ;
$nostat=1;
include_once($rootpath."configurations/$host/config_mysql.php");
include($rootpath."class/mysql.php");
include($rootpath."admin/mailreader.php");

$langues=array("FR","EN","SP");
$forbidden_ext=array("exe","php","py");

$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();


$fd = fopen("php://stdin", "r");
$email = "";
while (!feof($fd)) {
$email .= fread($fd, 1024);
}
fclose($fd);

$reader=new mailreader($email);


$body=$reader->message();

$findemail=$reader->to();

$sql="SELECT COUNT(*) FROM parametresgeneraux WHERE '".addslashes($findemail)."' IN (mail_contact";
foreach($langues as $l) $sql.=",mail_contact$l";
$sql.=")";
$cheznous=$DB_site->query_first($sql);
if($cheznous[0]>0) $findemail=false;

if($findemail!=false) {
    foreach($langues as $l) $listsql[]="mail$l";
    $sql="SELECT username FROM admin_contact WHERE '".addslashes($reader->from())."' IN (".implode(",",$listsql).")";
    $admin_contact=$DB_site->query_first($sql);
    
    $utilisateur=$DB_site->query_first("SELECT userid,mail FROM utilisateur WHERE mail='".$findemail."'");
    if($utilisateur['mail']==$findemail){
	$suivitype="suivi_client";
	$suiviid=$utilisateur['userid'];
    }

    $fournisseur=$DB_site->query_first("SELECT societe,mail FROM fournisseur WHERE mail='".$findemail."'");
    if($fournisseur['mail']==$findemail){
	$suivitype="suivi_fournisseur";
	$suiviid=$fournisseur['societe'];
    }
    
    if(!is_null($suivitype)){
	$DB_site->query("INSERT INTO mail_outbox SET date=".intval($reader->date()).", `utilisateur`='".addslashes($admin_contact['username'])."', `to`='".addslashes($reader->to())."', subject='".addslashes($reader->subject())."', body='".addslashes($body)."', suivitype='".$suivitype."', suiviid='".addslashes($suiviid)."'");
	$mailid=$DB_site->insert_id();
	foreach($reader->files() as $name=>$attachment){
	    if($attachment->ext=="") $attachment->getext($DB_site);
	    if(!in_array($attachment->ext,$forbidden_ext)){
		$DB_site->query("INSERT INTO mail_attachment SET moyen='mail_out', mailid=".$mailid.", name='".addslashes($attachment->name)."', ext='".addslashes($attachment->ext)."', size=".intval($attachment->size));
		$pjid=$DB_site->insert_id();
		$attachment->save($rootpath."admin/mail/attachments/".$pjid.".".$attachment->ext);
	    }
	}
    }
}

// var_dump($body);


$f=fopen($rootpath."admin/mail/outbox/".$reader->from()."_".time().".txt","w+");
fputs($f,$email);
fclose($f);


?>