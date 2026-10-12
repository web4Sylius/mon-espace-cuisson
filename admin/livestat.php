<?php
ini_set("display_errors","on");

// print utf8_encode("Désactivé, le serveur est lent !");
// exit;

$rootpath=$_SERVER['DOCUMENT_ROOT']."/";
if(isset($_SERVER['SERVER_NAME'])){
    switch($_SERVER['SERVER_NAME']){
        case 'localhost.ma-cave-a-vin.fr':
            $rootpath="d:/wamp/www/ma-cave-a-vin/";
            break;
        case 'test.ma-cave-a-vin.fr':
            $rootpath="/var/www/ma-cave-a-vin.fr/";
            break;
        case 'www.ma-cave-a-vin.fr':
            $rootpath="/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/";
            break;
    }
}


// $rootpath="/var/www/macave/site/";
$HTTP_HOST="www.ma-cave-a-vin.fr";
$host="www.ma-cave-a-vin.fr";

require ($rootpath."configurations/".$HTTP_HOST."/config.php");
include($rootpath."class/mysql.php");

$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();
$DBpassword="";

function tohms($sec){
    $hms = "";

    $hours = intval(intval($sec) / 3600);
    $hms .= $hours. ":";
    $minutes = intval(($sec / 60) % 60); 
    $hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT). ":";
    $seconds = intval($sec % 60);
    $hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);

    return $hms;
}

include_once("../class/ccstat.class.php");
$stat=new ccstat();
$lc=$stat->livestat();

$requete=$DB_site->query("SELECT ip FROM bloc_ip");
while($bip=$DB_site->fetch_array($requete)) $blocip[]=$bip['ip'];

$txt=""; 

if($lc!=false){
  foreach($lc as $visit){
    $style="";
    if(in_array($visit->ip,$blocip)) $style="style=\"color:red;text-decoration:line-through;\"";
    $ts=time()-$visit->timestamp;

    ksort($visit->pages);
    $last=array_pop($visit->pages);
    $div="<div id=\"lvs".$visit->statid."\" style=\"visibility:hidden;position:absolute;background-color:#dddddd;border:solid thin black;\">Entrée à ".date("H:i",$visit->timestamp)."
    <br>Sur le site depuis ".tohms($ts)."
    <br>Pays : ".$visit->country."
    <br>FAI : ".$visit->fai['fai']." ".$visit->fai['loc']."
    <br>Page actuelle : ".$last."
    <br>Pages vues : ".(count($visit->pages)+1)."
    <br>Provenance : ".$visit->provenance."
    </div>";
    $links="onmouseover=\"document.getElementById('lvs".$visit->statid."').style.visibility='visible';\" onmouseout=\"document.getElementById('lvs".$visit->statid."').style.visibility='hidden';\"";
    if($visit->bot!=false)
      $txt.= "<p $links $style>&#9881; ".$visit->bot['name']."</p>";
    elseif($visit->user!=0){
	$user=$DB_site->query_first("SELECT nom,prenom FROM utilisateur WHERE userid=".$visit->user);
	$txt.= "<p $links $style>&#9786; ".$user['nom']." ".$user['prenom']."</p>";
    }
    elseif($visit->fai['fai']!="")
      $txt.= "<p $links $style>&#9909; ".$visit->fai['fai']." ".$visit->fai['loc']."</p>";
    else{
      if(strlen($visit->host)>20) $visit->host=substr($visit->host,0,20)."...";
      $txt.="<p $links $style>&#8906; ".$visit->host."</p>";
    }

    $txt.=$div;
  }
}else{
  $txt.="Aucun visiteur";
}

header('Content-Type: text/html'); 

print $txt;

?>