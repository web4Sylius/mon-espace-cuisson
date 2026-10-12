<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";
include "../class/facture.class.php";

echo "
<style>
.ca { color:black; border-collapse:collapse; border:solid thin black; }
.ca td { border:solid thin black; }
.ca_nb { text-align:right; white-space:nowrap; }
.ca_title { background-color:yellow; text-align:center; }
.ca_head { font-weight:bold; text-align:center; }
</style>

<h1>Chiffre d'affaires</h1>";

if(!isset($_GET['year']))
    $year=date("Y");
else
    $year=intval($_GET['year']);

$optyears="";
for($y=2010;$y<=date("Y");$y++)
    $optyears.="<option value=\"".$y."\"".($y==$year?" selected":"").">".$y."</option>";

$tr=array();
$total=$totalpart=$totalpro=0;

for($i=1;$i<=12;$i++){
    $ca=ca($i,$year);
    
    $total+=$ca['total'];
    $totalpro+=$ca['pro'];
    $totalpart+=$ca['part'];
    
    $mois=$i."-".$year;
    $catotal=number_format($ca['total'],2,',','')." &euro;";
    $capart=number_format($ca['part'],2,',','')." &euro;";
    $capro=number_format($ca['pro'],2,',','')." &euro;";
    $pcpart=$pcpro="";
    if($ca['total']>0){
	$pcpart=round(100*($ca['part']/$ca['total']))."%";
	$pcpro=round(100*($ca['pro']/$ca['total']))."%";
    }
    
    $tr[$i]=<<<HTML
    <tr>
      <td>$mois</td>
      <td class="ca_nb">$catotal</td>
      <td class="ca_nb">$capart</td>
      <td class="ca_nb">$pcpart</td>
      <td class="ca_nb">$capro</td>
      <td class="ca_nb">$pcpro</td>
    </tr>
HTML;
}

ksort($tr);
$trlist=implode("",$tr);
    
$pcpart=round(100*($totalpart/$total))."%";
$pcpro=round(100*($totalpro/$total))."%";
    
$total=number_format($total,2,',','')." &euro;";
$totalpart=number_format($totalpart,2,',','')." &euro;";
$totalpro=number_format($totalpro,2,',','')." &euro;";
    
print <<<HTML

<p>Année : <select onchange="document.location='chiffre_pro.php?year='+this.value;">$optyears</select></p>

<table class="ca">
  <tr class="ca_head">
    <td></td>
    <td>Chiffre d'affaires HT</td>
    <td>CA HT part</td>
    <td>% part</td>
    <td>CA HT pro</td>
    <td>% pro</td>
  </tr>
  $trlist
  <tr style="font-weight:bold;">
    <td>Total année $year</td>
    <td class="ca_nb">$total</td>
    <td class="ca_nb">$totalpart</td>
    <td class="ca_nb">$pcpart</td>
    <td class="ca_nb">$totalpro</td>
    <td class="ca_nb">$pcpro</td>
  </tr>
</table>
<br>Ces chiffres concernent le CA facturé (et non pas le CA enregistré).
HTML;


function ca($mois,$annee){
	global $DB_site2;
	if($mois<10) $mois="0".$mois;
	$chiffremois=array("part"=>0,"pro"=>0);
	$etat="5,8";
	$sql="select * from facture WHERE valide in (".$etat.") and dateexpedition LIKE '".$annee."-".$mois."-%'";
	if(!is_null($limit)) $sql.=" AND dateexpedition<='".$annee."-".$mois."-".$limit."'";
	$cams=$DB_site2->query($sql);

	while ($cam=$DB_site2->fetch_array($cams)) {
	      $f=new facture($DB_site2,$cam);
	      $pro=$f->utilisateur->raisonsociale==""?"part":"pro";
	      $chiffremois[$pro] += $f->totalht;
	      $chiffremois["total"] += $f->totalht;
	}
	return $chiffremois;
}

?>