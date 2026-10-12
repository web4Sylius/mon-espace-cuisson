<?php

require "admin_global.php";

if(!isset($_GET['facturid'])) exit;

$facture=$DB_site2->query_first("SELECT facturid,nom,codepostal FROM facture WHERE facturid=".$_GET['facturid']);
if($facture==""){ print "Impossible de trouver cette commande"; exit; }

$fi=fsockopen("www.promium.mysls.fr",80);
$message="GET /loginDest.php HTTP/1.0\r\n";
$message.="\r\n\r\n";
fputs($fi,$message);
$return="";
while(!feof($fi)) $return.=fgets($fi,1024);
fclose($fi);

preg_match("/PHPSESSID=(\w+);/",$return,$phpsessid);

// Les données envoyées en POST sous forme d'url
$data = 'codePostal='.urlencode($facture['codepostal']).'&destinataire='.urlencode($facture['nom']).'&referenceClient='.$facture['facturid'];

// monfichier.php3 est l'URL du fichier devant recevoir la requete POST 
$message  = "POST /identification.php HTTP/1.0\r\n";
$message .= "Cookie: PHPSESSID=".$phpsessid[1].";\r\n";
$message .= "Content-type: application/x-www-form-urlencoded\r\n";
$message .= "Content-length: ".strlen( $data )."\r\n";
$message .= "\r\n";
$message .= $data."\r\n";
 
// monserveur correspond au serveur qui doit recevoir la requete
$fd = fsockopen("www.promium.mysls.fr", 80 );
fputs($fd,$message);
$return="";
while(!feof($fd)) $return.=fgets($fd,1024);
fclose($fd);
preg_match("/\"numeroExpedition\":\"(?<num>\w+)\",/",$return,$matches);

if($matches['num']==""){
//   header("location:http://www.promium.mysls.fr/loginDest.php");
  print $return;
}else
  print "<a href=\"http://www.promium.mysls.fr/suiviExpedition.php?numeroExpedition=".$matches['num']."&PHPSESSID=".$phpsessid[1]."\">http://www.promium.mysls.fr/suiviExpedition.php?numeroExpedition=".$matches['num']."</a>";
//   header("location:http://www.promium.mysls.fr/suiviExpedition.php?numeroExpedition=".$matches['num']);

// print $return;