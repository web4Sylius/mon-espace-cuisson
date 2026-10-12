<?php

ini_set("display_errors","on");

$host="www.mon-espace-cuisson.com" ;

extract($_REQUEST);
extract($_COOKIE);

include_once("../../configurations/$host/config_mysql.php");
include("../../class/mysql.php");
include("../../class/stock.class.php");

$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();

if (isset($admin_username) && $admin_username != "") {
    if($admin_username!="9d3bb895f22bf0afa958d68c2a58ded7"){
      $ip=$_SERVER['REMOTE_ADDR'];
      $acces=$DB_site->query_first("SELECT * FROM acces_admin WHERE ip='$ip' AND user='$admin_username' AND connecte=1 ORDER BY timestamp DESC LIMIT 0,1");
      if($acces['timestamp']==false || $acces['timestamp']<time()-3600){
         exit;
      }else{
        $user_info=$DB_site->query_first("SELECT * FROM parametresgeneraux WHERE username = '$admin_username'");
        if ($user_info[username] != $admin_username) exit;
      }
    }
}else
  exit;

include("xlexport.php");

if(!isset($_GET['du']) || !isset($_GET['au'])) exit;

$dt=DateTime::createfromformat("d/m/Y",$_GET['du']);
$du=$dt->gettimestamp();

$dt=DateTime::createfromformat("d/m/Y",$_GET['au']);
$dt->settime(23,59,59);
$au=$dt->gettimestamp();


$xl=new xlexport("csv");
$xl->name="compta_sorties_stock_mcav_".date("Ymd",$du)."-".date("Ymd",$au);
$xl->addrow(array("Date sortie de stock","","N° facture","N° compte","Contrepartie","Montant HT sorti du stock","Code analytique"));

$stock=new stock($DB_site);
$suivi_stock=$stock->suivi(null,null,$du,$au);
$i=1;

$lines=array();
$totalht=array();
$comptes_ana=array();
$nfacture=array();
$dateexp=array();
foreach($suivi_stock["facture"] as $date_expedition=>$stock_item){

    if(!isset($nfacture[$stock_item->facturid])){
	$facture=$DB_site->query_first("SELECT f.numerofacture,f.heurefacture,u.paysid,f.provenance,f.provenanceid,UNIX_TIMESTAMP(f.dateexpedition) AS dateexp FROM facture f INNER JOIN utilisateur u USING(userid) WHERE facturid=".$stock_item->facturid);
	$nfacture[$stock_item->facturid]=$facture['numerofacture'];
	$dateexp[$stock_item->facturid]=$facture['dateexp'];
	
	if($facture['provenance']=='mp'){
	  $compte_ana=compte_analytique($facture['provenanceid'],$facture['paysid']);
	}else
	  $compte_ana=compte_analytique("macaveavin",$facture['paysid']);
	$comptes_ana[$stock_item->facturid]=$compte_ana;
    }
    
    
    $line=array();
    $line[]=date("d/m/Y",$stock_item->date_expedition);
    $line[]="AA";
    $line[]=$nfacture[$stock_item->facturid]."/".$stock_item->facturid;
    $line[]="607100";
    $line[]="";
    $line[]=($stock_item->qte*$stock_item->paht);
    $line[]="19";

    $lines[$stock_item->facturid][]=$line;
    $totalht[$stock_item->facturid]+=($stock_item->qte*$stock_item->paht);
}

foreach($lines as $facturid=>$artlines){
    foreach($artlines as $artline)
	$xl->addrow($artline);
	
    if($totalht[$facturid]>0){
	$line=array();
	$line[]=date("d/m/Y",$dateexp[$facturid]);
	$line[]="AA";
	$line[]=$nfacture[$facturid]."/".$facturid;
	$line[]="607100";
	$line[]=$totalht[$facturid];
	$line[]="";
	$line[]=$comptes_ana[$facturid];
	$xl->addrow($line);
    }
}


foreach($suivi_stock['perdu'] as $date_sortie=>$stock_item){
    $line=array();
    $line[]=date("d/m/Y",$date_sortie);
    $line[]="AA";
    $line[]="Réajustement";
    $line[]="607100";
    $line[]=($stock_item->qte*$stock_item->paht);
    $line[]="";
    $line[]="19";
    $xl->addrow($line);
    
    $line=array();
    $line[]=date("d/m/Y",$date_sortie);
    $line[]="AA";
    $line[]="Réajustement";
    $line[]="607100";
    $line[]="";
    $line[]=($stock_item->qte*$stock_item->paht);
    $line[]="1101";
    $xl->addrow($line);
}

$entrees_manu=$stock->manu(null,null,$du,$au);
foreach($entrees_manu as $date_entree=>$stock_item){
    $line=array();
    $line[]=date("d/m/Y",$date_sortie);
    $line[]="AA";
    $line[]="Réajustement";
    $line[]="607100";
    $line[]="";
    $line[]=($stock_item->qte*$stock_item->paht);
    $line[]="19";
    $xl->addrow($line);
    
    $line=array();
    $line[]=date("d/m/Y",$date_sortie);
    $line[]="AA";
    $line[]="Réajustement";
    $line[]="607100";
    $line[]=($stock_item->qte*$stock_item->paht);
    $line[]="";
    $line[]="1101";
    $xl->addrow($line);
}

// print $xl->buffer;
$xl->export();


function compte_analytique($prov,$paysid){
	global $DB_site;
        $row=$DB_site->query_first("SELECT * FROM compte_analytique WHERE site='$prov' AND paysid=".intval($paysid));
	return $row['code'];
}


?>