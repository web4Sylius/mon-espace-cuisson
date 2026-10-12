<?php

include("../admin_global.php");
include("xlexport.php");
include("../marketplace2/order.php");

if(!isset($_GET['du']) || !isset($_GET['au'])) exit;

$sp=explode("/",$_GET['du']);
$du=mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
$sp2=explode("/",$_GET['au']);
$au=mktime(23,59,59,$sp2[1],$sp2[0],$sp2[2]);

$xl=new xlexport("csv");
$xl->name="compta_mcav_".date("Ymd",$du)."-".date("Ymd",$au);
$xl->addrow(array("Date","Jnl","Pièce","Libellé pièce","Valeur","Fournisseur","Date facture","N° facture","Code"));

$requete=$DB_site2->query("SELECT *,UNIX_TIMESTAMP(f.datefacture) AS datecde,UNIX_TIMESTAMP(f.dateexpedition) AS datef FROM facture f WHERE f.valide=5 AND UNIX_TIMESTAMP(f.dateexpedition) BETWEEN $du AND $au ORDER BY f.numerofacture");
while($facture=$DB_site2->fetch_array($requete)){
    $r2=$DB_site2->query("SELECT lignefactureid,qte FROM lignefacture lf WHERE lf.factureid=".$facture['facturid']);
    while($ligne=$DB_site2->fetch_array($r2)){
	$r3=$DB_site2->query("SELECT efl.paht,ff.fournisseur,ff.numero,ff.date_facture,efl.type FROM envoi_fournisseur_ligne efl left join facture_fournisseur ff ON (ff.numero=efl.facture) WHERE efl.paht<>0 AND efl.ligne_facture_id=".$ligne['lignefactureid']);
	while($efl=$DB_site2->fetch_array($r3)){
	    $efl['date_facture']!=null?$datefacture=date("d/m/Y",$efl['date_facture']):$datefacture="";
	    for($i=1;$i<=$ligne['qte'];$i++) $xl->addrow(array(date("d/m/Y",$facture['datef']),"VE",$facture['numerofacture'],$facture['facturid'],$efl['paht'],$efl['fournisseur'],$datefacture,$efl['numero'],code607($efl['type']),compte_analytique($facture['provenanceid'],$facture['paysid'])));
	}
    }
    $r2=$DB_site2->query("SELECT * FROM facture_fournisseur_autre ffa INNER JOIN facture_fournisseur ff ON (ff.numero=ffa.facture) WHERE ffa.facturid=".$facture['facturid']);
    while($ligne=$DB_site2->fetch_array($r2)){
	$ligne['date_facture']!=null?$datefacture=date("d/m/Y",$ligne['date_facture']):$datefacture="";
	$xl->addrow(array(date("d/m/Y",$facture['datef']),"VE",$facture['numerofacture'],$facture['facturid'],$ligne['prix'],$ligne['fournisseur'],$datefacture,$ligne['numero'],code607($ligne['type']),compte_analytique($facture['provenanceid'],$facture['paysid'])));
    }
}

/*$order=new mp_order();
$mporders=$order->list_orders(array(2),"billing_number","UNIX_TIMESTAMP(billing_date) BETWEEN ".$du." AND ".$au);
foreach($mporders as $mporder){
    $lines=$mporder->list_lines();
    foreach($lines as $line){
	$r3=$DB_site2->query("SELECT efl.paht,ff.fournisseur,ff.numero,ff.date_facture,efl.type FROM mp_envoi_fournisseur_ligne efl left join facture_fournisseur ff ON (ff.numero=efl.facture) WHERE efl.paht<>0 AND efl.ligne_facture_id=".$line->id);
	while($efl=$DB_site2->fetch_array($r3)){
	    $efl['date_facture']!=null?$datefacture=date("d/m/Y",$efl['date_facture']):$datefacture="";
	    for($i=1;$i<=$line->number;$i++) $xl->addrow(array(date("d/m/Y",$mporder->billing_date),"VE",$mporder->billing_number,$mporder->id,$efl['paht'],$efl['fournisseur'],$datefacture,$efl['numero'],code607($efl['type']),compte_analytique($mporder->market_place_id,$mporder->billing_country)));
	}
    }
    $r2=$DB_site2->query("SELECT * FROM facture_fournisseur_autre ffa INNER JOIN facture_fournisseur ff ON (ff.numero=ffa.facture) WHERE ffa.facturid=".$mporder->id);
    while($ligne=$DB_site2->fetch_array($r2)){
	$ligne['date_facture']!=null?$datefacture=date("d/m/Y",$ligne['date_facture']):$datefacture="";
	$xl->addrow(array(date("d/m/Y",$mporder->billing_date),"VE",$mporder->billing_number,$mporder->id,$ligne['prix'],$ligne['fournisseur'],$datefacture,$ligne['numero'],code607($ligne['type']),compte_analytique($mporder->market_place_id,$mporder->billing_country)));
    }
}*/

$xl->export();



function code607($type){
    switch($type){
	case "fourn": return "607100";
	case "trspt": return "607510";
	case "sls": return "611510";
	case "eco": return "607160";
	case "gar": return "607150";
	case "com": return "622210";
    }
}

function compte_analytique($prov,$paysid){
	global $DB_site2;
	if($prov=="") $prov="macaveavin";
        $requete=$DB_site2->query("SELECT * FROM compte_analytique WHERE site='$prov' AND paysid=".$paysid);
	$row=$DB_site2->fetch_array($requete);
	return $row['code'];
}

?>
