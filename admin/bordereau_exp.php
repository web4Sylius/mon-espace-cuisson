<?php

ini_set("display_errors",1);
include("admin_global.php");
include_once("../class/facture.class.php");
include_once("adv/adv.class.php");

if(!isset($_GET['from']) && !isset($_GET['to']) && !isset($_GET['date'])) $_GET['date']=time();

if(isset($_GET['from'])) $start=intval($_GET['from']);
if(isset($_GET['to'])) $end=intval($_GET['to']);
if(isset($_GET['date'])){
    $dt=new DateTime();
    if(is_numeric($_GET['date']))
        $dt->setTimestamp($_GET['date']);
    else
        $dt=$dt->createFromformat("j/n/Y",$_GET['date']);
    $start=$dt->settime(0,0,0)->getTimestamp();
    $end=$dt->settime(23,59,59)->getTimestamp();
}

$date=date("d/m/Y",$start);

$sql="SELECT ef.id,ef.livr,ef.facturid,ef.trackid FROM envoi_fournisseur ef INNER JOIN envoi_fournisseur_ligne efl ON (efl.efid=ef.id)
WHERE ef.destinataire='".mysqli_real_escape_string($DB_site2->link_id,$_GET['destinataire'])."'
AND efl.indate BETWEEN ".$start." AND ".$end;
if(isset($_GET['enlev'])){
    $sql.=" AND enlev=".intval($_GET['enlev']);
    
    $fourn=$DB_site2->query_first("SELECT societe,adresse1,cp,ville,diminutif FROM fournisseur INNER JOIN pays USING(paysid) WHERE id=".intval($_GET['enlev']));
    $expaddr=$fourn['societe']."<br>".$fourn['adresse1']."<br>".strtoupper($fourn['diminutif'])." ".$fourn['cp']." ".$fourn['ville'];
}
$sql.=" GROUP BY ef.id";

$tr="";
$nbcolis_total=0;

$req=$DB_site2->query($sql);
while($exp=$DB_site2->fetch_array($req)){
    $adv=new adv($DB_site2);
    $adv->load_from_efid($exp['id']);
    $nbcolis=count($adv->colis());
    if($nbcolis==0) $nbcolis=1;
    
    if($exp['livr']==0){
        $facture=new facture($DB_site2,$exp['facturid']);
        $cplivr=strtoupper($facture->diminutif)." ".$facture->codepostal;
        $nomlivr=trim($facture->raisonsociale." ".$facture->nom." ".$facture->prenom);
    }else{
        $fourn=$DB_site2->query_first("SELECT societe,adresse1,cp,ville,diminutif FROM fournisseur INNER JOIN pays USING(paysid) WHERE id=".intval($exp['livr']));
        $cplivr=strtoupper($fourn['diminutif'])." ".$fourn['cp'];
        $nomlivr=trim($fourn['societe']." ".$fourn['nom']." ".$fourn['prenon']);
    }

    $tr.=<<<HTML
        <tr style="height:25px;">
            <td>$exp[trackid]</td>
            <td>$nbcolis</td>
            <td>$exp[facturid]</td>
            <td>$cplivr</td>
            <td>$nomlivr</td>
        </tr>
HTML;
        
    $nbcolis_total+=$nbcolis;
}

print <<<HTML
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
    @media print {
        input { display:none; }
    }
    </style>
</head>

<body style="margin:10px; padding:0; font:12px arial,sans-serif;">
<h1>Liste des expéditions</h1>

<table style="width:100%;font-size:12px;">
<tr>
    <td>Date d'expédition</td>
    <td>$date</td>
</tr>
<tr>
    <td>Transporteur</td>
    <td>$_GET[destinataire]</td>
</tr>
<tr>
    <td>Expéditeur</td>
    <td>$expaddr</td>
</tr>
<tr>
    <td>Nombre total de colis</td>
    <td>$nbcolis_total</td>
</tr>
</table>

<div style="margin-top:20px">
    <div style="float:left; width:25%; height:100px; border:solid 1px black; padding:5px;">
        <b>Signature de l'expéditeur :</b>
    </div>

    <div style="float:right; width:25%; height:100px; border:solid 1px black; padding:5px;">
        <b>Signature du livreur :</b>
    </div>
</div>

<table style="clear:both;margin-top:40px;width:100%;font-size:12px;border-collapse:collapse;">
<thead style="font-weight:bold;">
<tr>
    <td colspan="5" style="border-bottom:solid 2px black; font-size:16px;padding:10px 0;">Détails Colis</td>
</tr>
<tr>
    <td style="padding:5px 0;border-bottom:solid 1px black;">No Colis</td>
    <td style="border-bottom:solid 1px black;">Nb colis</td>
    <td style="border-bottom:solid 1px black;">Référence</td>
    <td style="border-bottom:solid 1px black;">Code Postal</td>
    <td style="border-bottom:solid 1px black;">Nom destinataire</td>
</tr>
</thead>
$tr
</table>
<p><input type="button" value="Imprimer" onClick="window.print();"></p>
</body>
</html>
HTML;


?>