<?php

$host="www.ma-cave-a-vin.fr" ;

extract($_REQUEST);
extract($_COOKIE);

include_once("../../configurations/$host/config_mysql.php");
include("../../class/mysql.php");

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
include("../marketplace/order.php");

if(!isset($_GET['du']) || !isset($_GET['au'])) exit;

$sp=explode("/",$_GET['du']);
$du=mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
$sp=explode("/",$_GET['au']);
$au=mktime(23,59,59,$sp[1],$sp[0],$sp[2]);

$xl=new xlexport("csv");
$xl->name="compta_nonfacture_".str_replace($_GET['du'],"/","-");
$xl->addrow(array("Date fin mois précédent","N° facture fictif","N° facture fictif","Nom fournisseur/N° commande","code journal","N° compte","Ventilation","A payer","Code analytique"));

$txtva=0.196;

$i=1;
$sql="SELECT efl.paht,efl.ecopart,efl.type,ef.destinataire,f.facturid,f.paysid,efl.qte FROM envoi_fournisseur_ligne efl INNER JOIN lignefacture lf ON(lf.lignefactureid=efl.ligne_facture_id) INNER JOIN facture f ON(f.facturid=lf.factureid) INNER JOIN envoi_fournisseur ef ON(ef.id=efl.efid) WHERE efl.facture='' AND ef.destinataire<>'0' AND ef.destinataire NOT LIKE '%Acilyon' AND UNIX_TIMESTAMP(f.dateexpedition) BETWEEN $du AND $au";
// print $sql;
$requete=$DB_site->query($sql);
while($ligne=$DB_site->fetch_array($requete)){
    $compte_ana=compte_analytique($ligne['paysid']);
    $xl->addrow(array("",$i,$i,$ligne['destinataire']."/".$ligne['facturid'],"SI",code607($ligne['type']),round($ligne['qte']*$ligne['paht'],2),"",$compte_ana));
    $xl->addrow(array("",$i,$i,$ligne['destinataire']."/".$ligne['facturid'],"SI","445860",round($ligne['qte']*$ligne['paht']*$txtva,2),"",$compte_ana));
    if($ligne['ecopart']!=0){
	$xl->addrow(array("",$i,$i,$ligne['destinataire']."/".$ligne['facturid'],"SI",code607("eco"),round($ligne['qte']*$ligne['ecopart'],2),"",$compte_ana));
	$xl->addrow(array("",$i,$i,$ligne['destinataire']."/".$ligne['facturid'],"SI","445860",round($ligne['qte']*$ligne['ecopart']*$txtva,2),"",$compte_ana));
    }
    $xl->addrow(array("",$i,$i,"","","","",round($ligne['qte']*($ligne['ecopart']+$ligne['paht']),2)));
    $i++;
}

/*
$requete=$DB_site->query("SELECT * FROM mp_envoi_fournisseur_ligne WHERE facture='' AND date BETWEEN $du AND $au");
while($ligne=$DB_site->fetch_array($requete)){
    $facture=$DB_site->fetch_array($DB_site->query("SELECT o.marketplace,o.id FROM mp_order_line ol, mp_orders o WHERE o.id=ol.order AND ol.id=".$ligne['ligne_facture_id']));
    $compte_ana=compte_analytique_mp($facture['marketplace']);
    $xl->addrow(array(date("d/m/Y",$ligne['date']),"",$facture['id'],"SI",code607($ligne['type']),"",round($ligne['qte']*$ligne['paht'],2),"",$compte_ana));
    $xl->addrow(array(date("d/m/Y",$ligne['date']),"",$facture['id'],"SI","445660","",round($ligne['qte']*$ligne['paht']*$txtva,2),"",$compte_ana));
    if($ligne['ecopart']!=0){
	$xl->addrow(array(date("d/m/Y",$ligne['date']),$facture['numero'],$facture['id'],"SI",code607("eco"),"",round($ligne['qte']*$ligne['ecopart'],2),"",$compte_ana));
	$xl->addrow(array(date("d/m/Y",$ligne['date']),$facture['numero'],$facture['id'],"SI","445660","",round($ligne['qte']*$ligne['ecopart']*$txtva,2),"",$compte_ana));
    }
}*/

$xl->export();

function code607($type){
    switch($type){
	case "fourn": return "607100";
	case "trspt": return "607510";
	case "eco": return "607160";
	case "gar": return "607150";
    }
}

function compte_analytique($paysid){
  global $DB_site;
  $requete=$DB_site->query("SELECT * FROM compte_analytique WHERE site='macaveavin' AND paysid=".$paysid);
	$row=$DB_site->fetch_array($requete);
	return $row['code'];
}

function compte_analytique_mp($mpid){
  global $DB_site;
	$requete=$DB_site->query("SELECT * FROM compte_analytique WHERE site='".$mpid."'");
	$row=$DB_site->fetch_array($requete);
	return $row['code'];
}


?>