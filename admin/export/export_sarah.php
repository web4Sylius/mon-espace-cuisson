<?php

ini_set("display_errors","1");
error_reporting(E_ALL);
ini_set("memory_limit","1024M");

$rootpath="/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/";
// $rootpath="/media/disk/www/macave/site2012/";

extract($_REQUEST);
extract($_COOKIE);

$host="www.ma-cave-a-vin.fr" ;
$nostat=1;
include_once($rootpath."configurations/$host/config_mysql.php");
include($rootpath."class/mysql.php");
include($rootpath."configurations/$host/config.php");
include_once($rootpath."class/fonctions.php");
include_once($rootpath."class/email.php");
include($rootpath."admin/export/ooo.php");
include($rootpath."class/ccstat.class.php");
include($rootpath."admin/marge.php");
include($rootpath."class/categorie.class.php");
include($rootpath."class/facture.class.php");
include($rootpath."class/frais_port.php");
include($rootpath."class/DAOgarantie.class.php");

$destinataire="adv@ma-cave-a-vin.fr";
$destinataire="webmaster@ma-cave-a-vin.fr";

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


$articles=array();
$parentcavecatid=array(1,2,13,3,7);
$cavecatid=$parentcavecatid;
$requete=$DB_site->query("SELECT catid FROM categorie WHERE parentid IN (".implode(',',$parentcavecatid).")");
while($c=$DB_site->fetch_array($requete)) $cavecatid[]=$c['catid'];

$doc=new ooo();

if(!isset($_GET['mode'])){
  $m1s=time()-86400*7;
  $m1s=mktime(0,0,0,date("m",$m1s),date("d",$m1s),date("Y",$m1s));
  while(date("w",$m1s)!=1) $m1s-=86400;
  $fins=$m1s+86400*7-1;
  $doc->name="Ma Cave a vin semaine ".date("W",$m1s);
  $txttotal="Semaine ".date("W",$m1s);
}elseif($_GET['mode']=="month"){
  $doc->name="Ma Cave a Vin ".date("Y-m",$_GET['ts']);
  $txttotal=date("m/Y",$_GET['ts']);
  $m1s=mktime(0,0,0,date("m",$_GET['ts']),1,date("Y",$_GET['ts']));
  $fins=$m1s+86400*date("t",$_GET['ts']);
}elseif($_GET['mode']=="week"){
  $m1s=$_GET['ts'];
  $m1s=mktime(0,0,0,date("m",$m1s),date("d",$m1s),date("Y",$m1s));
  while(date("w",$m1s)!=1) $m1s-=86400;
  $fins=$m1s+86400*7-1;
  $doc->name="Ma Cave a vin semaine ".date("W",$m1s);
  $txttotal="Semaine ".date("W",$m1s);
}elseif($_GET['mode']=="year"){
  $m1s=$_GET['ts'];
  $m1s=mktime(0,0,0,1,1,date("Y",$m1s));
  $fins=$m1s+86400*(365+date("L",$m1s))-1;
  $doc->name="Ma Cave a vin année ".date("Y",$m1s);
  $txttotal="Année ".date("Y",$m1s);
}elseif($_GET['mode']=="free"){
  $m1s=mktime(0,0,0,date("m",$_GET['ts']),date("d",$_GET['ts']),date("Y",$_GET['ts']));
  $fins=mktime(0,0,0,date("m",$_GET['ts2']),date("d",$_GET['ts2']),date("Y",$_GET['ts2']));
  $doc->name="Ma Cave à vin ".date("Y-m-d",$_GET['ts'])."-".date("Y-m-d",$_GET['ts2']);
  $txttotal="Total";
}

$categorie=new categorie($DB_site);


$t1=new ooo_table("Cde enregistrées");
$bold=new ooo_style("bold");
$bold->font_weight="bold";
$bold->text_align="center";
$bold->family="table-cell";
$bold->setborders("0.5pt solid #000000");
$doc->addstyle($bold);
$left=new ooo_style("left");
$left->family="table-cell";
$left->setborders("0.5pt solid #000000");
$doc->addstyle($left);
$right=new ooo_style("right");
$right->text_align="end";
$right->family="table-cell";
$right->setborders("0.5pt solid #000000");
$doc->addstyle($right);
$bright=new ooo_style("bright");
$bright->text_align="end";
$bright->family="table-cell";
$bright->setborders("0.5pt solid #000000");
$bright->font_weight="bold";
$doc->addstyle($bright);
$center=new ooo_style("center");
$center->text_align="center";
$center->family="table-cell";
$center->setborders("0.5pt solid #000000");
$doc->addstyle($center);
$red=new ooo_style("red");
$red->family="table-cell";
$red->font_color="#FF0000";
$red->text_align="end";
$red->setborders("0.5pt solid #000000");
$doc->addstyle($red);

$t1->set_col(3,"1.5cm");
$t1->set_col(10,"4cm");
$t1->set_col(12,"5cm");
$t1->set_col(14,"1cm");
$t1->set_row(1,"1cm");

$row=array();
$row[]=new ooo_cell("Mois","bold");
$row[]=new ooo_cell("Provenance","bold");
$row[]=new ooo_cell("Nb inscriptions","bold");
$row[]=new ooo_cell("Nb commandes","bold");
$row[]=new ooo_cell("Qté cave","bold");
$row[]=new ooo_cell("Qté autres","bold");
$row[]=new ooo_cell("Montant €HT","bold");
$row[]=new ooo_cell("Dont Montant\nCaves €HT","bold");
$t1->addrow(1,$row);


$ccstat=new ccstat();
$ccstat->temporary_table("(SELECT * FROM stat_arch WHERE user>0) UNION (SELECT * FROM stat WHERE user>0)");

$totalmois=array();

$f=new facture($DB_site);
$list=$f->search("valide IN (1,5,7,8) AND moyenid>0 AND provenance='' AND site_code IN ('','base') AND heurefacture BETWEEN $m1s AND $fins ORDER BY heurefacture");

// $factures=$DB_site->query("SELECT * FROM facture WHERE valide IN (1,5,7,8) AND moyenid>0 AND provenance='' AND heurefacture BETWEEN $m1s AND $fins ORDER BY heurefacture");
// while($facture=$DB_site->fetch_array($factures)){
foreach($list as $facture){
    $catcave=$catautre=0;
    $totalttc=0;
    $totalht=0;
    $totalcave=0;
    $gar="";
    $artbyd=array();
    $t0h=mktime(0,0,0,date("n",$facture->datecommande),1,date("Y",$facture->datecommande));

    foreach($facture->lignes as $ligne){
	$garantie=$DB_site->query_first("SELECT caractvalid FROM lignefacturecaracteristique WHERE lignefactureid=".$ligne->lignefactureid." AND caractvalid BETWEEN 9 AND 11");
	if($garantie[0]!="" && $garantie[0]!=9){
	    switch($garantie[0]){
		case 10:
		$gar="2 ans";
		break;
		case 11:
		$gar="5 ans";
		break;
	    }
	}

	if($ligne->artid!=0){
	    $article=$DB_site->query_first("SELECT a.artid,a.catid,am.marqueid FROM article a INNER JOIN article_marque am ON (am.artid=a.artid) WHERE a.artid=".$ligne->artid);
	    
	    if(in_array($article['catid'],$cavecatid)){
		$catcave+=$ligne->qte;
		$totalcave+=$ligne->totalht;
	    }elseif(intval($article['catid'])!=0)
		$catautre+=$ligne->qte;
	}
	
    }

    
    $visit=$ccstat->get_visit($facture->utilisateur->userid,$facture->datecommande,"stat_temp");
    $prov=$visit[0]->mrprovenance['type'];
    if($visit[0]->provenance=="") $prov="Accès direct";
    if($visit==false) $prov="?";

    $totalmois[$t0h][$prov]['qtecave']+=$catcave;
    $totalmois[$t0h][$prov]['qteautre']+=$catautre;
    $totalmois[$t0h][$prov]['total']+=$facture->totalttc;
    $totalmois[$t0h][$prov]['totalht']+=$facture->totalht;
    $totalmois[$t0h][$prov]['totalcave']+=$totalcave;
    $totalmois[$t0h][$prov]['qtecmd']++;
}

ksort($totalmois);

$sql="SELECT userid,day(dateinscription) as jour,month(dateinscription) as mois,year(dateinscription) as annee FROM utilisateur WHERE dateinscription >= '".date("Y-m-d",$m1s)."' AND dateinscription < '".date("Y-m-d",$fins)."'";
$req=$DB_site->query($sql);
while($inscription=$DB_site->fetch_array($req)){

    $visit=$ccstat->get_visit($inscription['userid'],mktime(0,0,0,$inscription['mois'],$inscription['jour'],$inscription['annee'])+86400,"stat_temp");
    $prov=$visit[0]->mrprovenance['type'];
    if($visit[0]->provenance=="") $prov="Accès direct";
    if($visit==false) $prov="?";
    
    $t0h=mktime(0,0,0,$inscription['mois'],1,$inscription['annee']);
    $totalmois[$t0h][$prov]['inscription']++;
}

$i=1;

foreach($totalmois as $ts=>$provlist){
    $row=array(new ooo_cell(date("m/Y",$ts),"left"));
    $i++;
    uasort($provlist,"comparetotal");
    foreach($provlist as $prov=>$stats){
	$row[]=new ooo_cell($prov,"left");
	$row[]=new ooo_cell($stats['inscription'],"center");
	$row[]=new ooo_cell($stats['qtecmd'],"center");
	$row[]=new ooo_cell($stats['qtecave'],"center");
	$row[]=new ooo_cell($stats['qteautre'],"center");
	$row[]=new ooo_cell(round($stats['totalht'],2),"right");
	$row[]=new ooo_cell($stats['totalcave'],"right");
	$t1->addrow($i,$row);
	$i++;
	$row=array(new ooo_cell("","left"));
    }
}


$doc->addtable($t1);


$doc->generate();

function comparetotal($a,$b){
    return $b['total']-$a['total'];
}

?>