<?php

include("../admin_global.php");
include("xlexport.php");
include("../marketplace/order.php");

if(!isset($_GET['du']) || !isset($_GET['au'])) exit;

$sp=explode("/",$_GET['du']);
$du=mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
$sp2=explode("/",$_GET['au']);
$au=mktime(23,59,59,$sp2[1],$sp2[0],$sp2[2]);

$xl=new xlexport("csv");
$xl->name="compta_fournisseur_mcav_".date("Ymd",$du)."-".date("Ymd",$au);
$xl->addrow(array("Date commande","N° facture","N° commande","code journal","N° compte","Code de TVA","Détail des ligne","Montant payé","Code analytique"));

$txtva=0.196;
/*$requete=$DB_site2->query("SELECT * FROM `envoi_fournisseur` ef LEFT JOIN envoi_fournisseur_ligne efl ON ( efl.efid = ef.id ) WHERE ef.destinataire = 'stock acilyon' AND (ef.date BETWEEN $du AND $au)");
while($efl=$DB_site2->fetch_array($requete)){
    $facture=$DB_site2->query_first("SELECT *,UNIX_TIMESTAMP(datefacture) AS tsdf FROM facture WHERE facturid=".$efl['facturid']);
    $lignes=$DB_site2->query("SELECT * FROM lignefacture WHERE factureid=".$facture['facturid']);
    $compte_ana=compte_analytique($facture['paysid']);
    if($efl['efid']==null){
	while($ligne=$DB_site2->fetch_array($lignes)){
	    $article=$DB_site2->query_first("SELECT * FROM article WHERE artid=".$ligne['artid']);
	    $xl->addrow(array(date("d/m/Y",$facture['tsdf']),$facture['numerofacture'],$facture['facturid'],"AC","607100","A2",$ligne['artcode']." ".$article['artcode_eurocave'],round($ligne['qte']*$article['paht'],2),"",$compte_ana));
	    $xl->addrow(array(date("d/m/Y",$facture['tsdf']),$facture['numerofacture'],$facture['facturid'],"AC","445660","A2",$ligne['artcode']." ".$article['artcode_eurocave'],round($ligne['qte']*$article['paht']*$txtva,2),"",$compte_ana));
	    if($article['ecopart']!=0){
	      $xl->addrow(array(date("d/m/Y",$facture['tsdf']),$facture['numerofacture'],$facture['facturid'],"AC","607160","A2",$ligne['artcode']." ".$article['artcode_eurocave'],round($ligne['qte']*$article['ecopart'],2),"",$compte_ana));
	      $xl->addrow(array(date("d/m/Y",$facture['tsdf']),$facture['numerofacture'],$facture['facturid'],"AC","445660","A2",$ligne['artcode']." ".$article['artcode_eurocave'],round($ligne['qte']*$article['ecopart']*$txtva,2),"",$compte_ana));
	    }
	}
    }else{
	$xl->addrow(array(date("d/m/Y",$facture['tsdf']),$facture['numerofacture'],$facture['facturid'],"AC","607100","A2","",round($efl['qte']*$efl['paht'],2),"",$compte_ana));
	$xl->addrow(array(date("d/m/Y",$facture['tsdf']),$facture['numerofacture'],$facture['facturid'],"AC","445660","A2","",round($efl['qte']*$efl['paht']*$txtva,2),"",$compte_ana));
	if($facture['ecopart']!=0){
	    $xl->addrow(array(date("d/m/Y",$facture['tsdf']),$facture['numerofacture'],$facture['facturid'],"AC","607160","A2","",round($efl['qte']*$efl['ecopart'],2),"",$compte_ana));
	    $xl->addrow(array(date("d/m/Y",$facture['tsdf']),$facture['numerofacture'],$facture['facturid'],"AC","445660","A2","",round($efl['qte']*$efl['ecopart']*$txtva,2),"",$compte_ana));
	}
    }
}*/

$requete=$DB_site2->query("SELECT * FROM mp_orders WHERE (UNIX_TIMESTAMP(billing_date) BETWEEN $du AND $au) AND order_status IN (2,4) ORDER BY billing_date");
while($facture=$DB_site2->fetch_array($requete)){
    $lignes=$DB_site2->query("SELECT * FROM mp_order_line WHERE `order`='".$facture['id']."'");
    $compte_ana=compte_analytique($facture['billing_country'],$facture['marketplace']);
    while($ligne=$DB_site2->fetch_array($lignes)){
	    $article=$DB_site2->query_first("SELECT * FROM article WHERE artcode='".$ligne['ref_tin']."'");
	    $xl->addrow(array($facture['billing_date'],$facture['billing_number'],$facture['id'],"AC","607100","A2",$ligne['ref_tin']." ".$article['artcode_eurocave'],round($article['paht'],2),$compte_ana));
	    $xl->addrow(array($facture['billing_date'],$facture['billing_number'],$facture['id'],"AC","445660","A2",$ligne['ref_tin']." ".$article['artcode_eurocave'],round($article['paht']*$txtva,2),$compte_ana));
	    if($article['ecopart']!=0){
	      $xl->addrow(array($facture['billing_date'],$facture['billing_number'],$facture['id'],"AC","607160","A2",$ligne['ref_tin']." ".$article['artcode_eurocave'],round($article['ecopart'],2),$compte_ana));
	      $xl->addrow(array($facture['billing_date'],$facture['billing_number'],$facture['id'],"AC","445660","A2",$ligne['ref_tin']." ".$article['artcode_eurocave'],round($article['ecopart']*$txtva,2),$compte_ana));
	    }
    }
}

$xl->export();

function compte_analytique($paysid,$prov="macaveavin"){
        $requete=$DB_site2->query("SELECT * FROM compte_analytique WHERE site='$prov' AND paysid=".$paysid);
	if($requete==false) return false;
	$row=$DB_site2->fetch_array($requete);
	return $row['code'];
}
