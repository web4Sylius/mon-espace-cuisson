<?php

$rootpath="/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/";
$rootpath="/media/disk/www/macave/site2012/";

$host="www.ma-cave-a-vin.fr" ;
$nostat=1;
include_once($rootpath."configurations/$host/config_mysql.php");
include($rootpath."class/mysql.php");
include_once($rootpath."admin/email.php");
include($rootpath."class/facture.class.php");

$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();

if(!isset($_GET['ts'])){
  $today=mktime(0,0,0,date("m"),date("d"),date("Y"));
  $d=$today-(21*86400);
}else
  $d=$_GET['ts'];

$f=new facture($DB_site);

$factures=$f->search("dateexpedition LIKE '".date("Y-m-d",$d)."' AND valide IN (5,8)");
print "<p>".date("d/m/Y",$d)."</p>";
foreach($factures as $facturid=>$facture){
     $emdata=$DB_site->query_first("SELECT * FROM emails_clients WHERE emailid=55");
     $langue=$facture->utilisateur->langue;

     $email=new email();
     $email->destinataire=$facture->utilisateur->mail;
     $email->destname=$facture->utilisateur->prenom." ".$facture->utilisateur->nom;
     $email->expediteur="Ma Cave à Vin";
     $email->expname="qualite@mon-espace-cuisson.com";
     $email->subject=$emdata['libelle'.$langue];
     $email->body=insertfields($emdata['contenu'.$langue],$facture);

     if(!isset($_GET['test']))
	$email->sendmail();
     else
	print "<p>".$facture->utilisateur->mail." ".$facture->utilisateur->prenom." ".$facture->utilisateur->nom."<br>".$email->subject."<br>".$email->body."</p>";

}

function insertfields($txt,$facture){
     $fdata['nom']=$facture->utilisateur->nom;
     $fdata['prenom']=$facture->utilisateur->prenom;
     $fdata['facturid']=$facture->facturid;
     $fdata['dateexpedition']=date("d/m/Y",$facture->dateexpedition);
     $fdata['datecommande']=date("d/m/Y",$facture->datecommande);
     $fdata['userid']=$facture->utilisateur->userid;
     $fdata['verif']=md5("userid".$facture->utilisateur->userid);
     $ret=preg_replace("/\[(.*?)\]/ie","\$fdata[$1]",$txt);

     return $ret;
}