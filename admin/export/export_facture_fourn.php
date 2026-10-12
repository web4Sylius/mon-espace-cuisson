<?php

include("../admin_global.php");
include("xlexport.php");

$_GET['fournisseur']=stripslashes($_GET['fournisseur']);

$xl=new xlexport("csv");
$xl->name="export_".$_GET['fournisseur'];

$typesdate=array("date_facture"=>"Date de facture","echeance"=>"Date d'échéance","date_saisie"=>"Date de saisie");

$sql="SELECT * FROM facture_fournisseur WHERE fournisseur = '".mysqli_real_escape_string($DB_site2->link_id,$_GET['fournisseur'])."'";
if($_GET['from']!="" && $_GET['to']!="" && array_key_exists($_GET['typedate'],$typesdate)) $sql.=" AND `".$_GET['typedate']."` BETWEEN ".DateTime::createFromFormat("j/n/Y",$_GET['from'])->setTime(0,0,0)->getTimestamp()." AND ".(DateTime::createFromFormat("j/n/Y",$_GET['to'])->setTime(23,59,59)->getTimestamp());
$sql.=" ORDER BY date_facture";

$xl->addrow(array("N° facture","Date facture","Montant HT","Montant TTC","Taux TVA","Devise","Echéance","Date saisie"));

$total_ht=$total_ttc=0;

$req=$DB_site2->query($sql);
while($f=$DB_site2->fetch_array($req)){
    $xl->addrow(array($f['numero'],date("d/m/Y",$f['date_facture']),$f['montant_ht'],$f['montant_ttc'],$f['tva'],$f['devise'],date("d/m/Y",$f['echeance']),date("d/m/Y",$f['date_saisie'])));
    $total_ht+=$f['montant_ht'];
    $total_ttc+=$f['montant_ttc'];
}

$xl->addrow(array("","",$total_ht,$total_ttc));

$xl->export();

?>