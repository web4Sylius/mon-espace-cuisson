<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";
include "../class/facture.class.php";

ini_set("display_errors",0);

echo "
<style>
.ca { color:black; border-collapse:collapse; border:solid thin black; }
.ca td { border:solid thin black; }
.ca_nb { text-align:right; white-space:nowrap; }
.ca_title { background-color:yellow; text-align:center; }
.ca_head { font-weight:bold; text-align:center; }
</style>
<script language=\"javascript\">
function showpopup(id){
    document.getElementById(id).style.visibility='visible';
    document.getElementById(id).style.backgroundColor='#AAFFAA';
    document.getElementById(id).parentNode.style.backgroundColor='#AAFFAA';
}
function hidepopup(id){
    document.getElementById(id).style.visibility='hidden';
    document.getElementById(id).parentNode.style.backgroundColor='';
}
</script>

<h1>Chiffre d'affaire</h1>";

if(!isset($year)) $year=date("Y");

$chiffrejour = 0 ;
$cajs=$DB_site2->query("select factureid, SUM((prix/(1+tva/100))*qte) montant, prixport, montantremise, montantcadeau from lignefacture lf inner join facture f 
                        on (f.facturid = lf.factureid) where remboursement = '0' and valide in (5, 8) and dateexpedition = '".date('Y-m-d')."' group by factureid");
while ($caj=$DB_site2->fetch_array($cajs)) {
    $chiffrejour += $caj[montant] + $caj[prixport] - $caj[montantremise] - $caj[montantcadeau] ;
    }
$chiffrejour = number_format($chiffrejour, 2, ",", " ") ;

$chiffremois = 0 ;
$cams=$DB_site2->query("select factureid, SUM((prix/(1+tva/100))*qte) montant, prixport, montantremise, montantcadeau from lignefacture lf inner join facture f 
                        on (f.facturid = lf.factureid) where remboursement = '0' and valide in (5, 8) and dateexpedition LIKE '".date('Y-m-%')."' group by factureid");
while ($cam=$DB_site2->fetch_array($cams)) {
    $chiffremois += $cam[montant] + $cam[prixport] - $cam[montantremise] - $cam[montantcadeau] ;
    }
$chiffremois = number_format($chiffremois, 2, ",", " ") ;

$chiffreannee = 0 ;
$caas=$DB_site2->query("select factureid, SUM((prix/(1+tva/100))*qte) montant, prixport, montantremise, montantcadeau from lignefacture lf inner join facture f 
                        on (f.facturid = lf.factureid) where remboursement = '0' and valide in (5, 8) and dateexpedition LIKE '".date('Y-%')."' group by factureid");
while ($caa=$DB_site2->fetch_array($caas)) {
    $chiffreannee += $caa[montant] + $caa[prixport] - $caa[montantremise] - $caa[montantcadeau] ;
    }
$chiffreannee = number_format($chiffreannee, 2, ",", " ") ;

$chiffretotal = 0 ;
$cats=$DB_site2->query("select factureid, SUM((prix/(1+tva/100))*qte) montant, prixport, montantremise, montantcadeau from lignefacture lf inner join facture f 
                        on (f.facturid = lf.factureid) where remboursement = '0' and valide in (5, 8) group by factureid");
while ($cat=$DB_site2->fetch_array($cats)) {
    $chiffretotal += $cat[montant] + $cat[prixport] - $cat[montantremise] - $cat[montantcadeau] ;
    }
$chiffretotal = number_format($chiffretotal, 2, ",", " ") ; 

echo "<br><bR><center>
        <h3>Chiffre d'affaires HT</h3>
        <table class=\"table_caracts\">
                <tr><tD class=\"td_promos_alt2\" align=\"center\"><strong>Au total</strong></td><tD class=\"td_promos_alt2\" align=\"center\"><strong>Cette annÃ©e</strong></td><td class=\"td_promos_alt2\" align=\"center\"><strong>Ce mois</strong></td><td class=\"td_promos_alt2\" align=\"center\"><strong>Ce jour</strong></td></tr>
                <tr><tD align=\"center\"></td><td align=\"center\"></td><td align=\"center\"></td></tr>
                <tr><tD align=\"center\">$chiffretotal â‚¬</td><tD align=\"center\">$chiffreannee â‚¬</td><td align=\"center\">$chiffremois â‚¬</td><td align=\"center\">$chiffrejour â‚¬</td></tr>
            </table>
            </center>" ;

echo "<p align=\"center\">Année : <select name=\"year\" onchange=\"document.location='chiffre.php?year='+this.value\">";

for($y=2010;$y<=(2009+count($objectif_facture));$y++) echo "<option value=\"$y\"".($y==$year?" selected":"").">$y</option>";

echo "</select></p>
<table class=\"ca\">
<tr><td colspan=\"14\" class=\"ca_title\">Cumul chiffre d'affaire</td></tr>
<tr class=\"ca_head\">
  <td></td>
  <td>Objectif mensuel &euro;HT</td>
  <td>Chiffre d'affaires &euro;HT</td>
  <td>% Objectif du mois</td>
  <td>Av/ret / objectif &euro;HT</td>
  <td>Av/ret / objectif %</td>
  <td>Av/ret / N-1 &euro;HT</td>
  <td>% N-1</td>
  <td>Av/ret / N-2 &euro;HT</td>
  <td>% N-2</td>
  <td>Cde en cours validées</td>
  <td>Cde en attente de paiement &euro;HT</td>
  <td>Chiffre d'affaires prévisionnel</td>
  <td>% Objectif avec cde en cours</td>
</tr>";

$listfacture=array();
// $report_site=report($year);
// $report_mp=report($year,"mp");
// $report_sav=report($year,"sav");
// echo "<tr><td>Report</td><td></td><td></td><td></td><td></td><td></td><td></td><td class=\"ca_nb\" onmouseover=\"showpopup('report1')\" onmouseout=\"hidepopup('report1')\">".number_format($report_site[1]+$report_mp[1],2,',','')." &euro;<div id=\"report1\" class=\"popup\">".formatlist("report1")."</div></td><td class=\"ca_nb\" onmouseover=\"showpopup('report0')\" onmouseout=\"hidepopup('report0')\">".number_format($report_site[0]+$report_mp[0],2,',','')." &euro;<div id=\"report0\" class=\"popup\">".formatlist("report0")."</div></td><td class=\"ca_nb\">".number_format($report_site[0]+$report_mp[0]+$report_site[1]+$report_mp[1],2,',','')." &euro;</td><td></td></tr>";

$Y=date("Y");
$m=date("n");
$ca_encours_mp=$ca_encours=$ca_attente=$ca_encours_sav=array();
$total_valide=$total_encours=$total_attente=$total_valide_mp=0;
$ca_validem1=$ca_validem2=$ca_validem1_mp=$ca_validem2_mp=$ca_validem1_sav=$ca_validem2_sav=array();

// if($year==$Y){
    $ca_encours_mp=ca_valide(null,$year,"mp");
    $ca_encours=ca_valide(null,$year);
    $ca_attente=ca_attente(null,$year);
    $ca_encours_sav=ca_valide(null,$year,"sav");
// }

for($i=1;$i<=12;$i++){
    
    $ca_valide[$i]=ca($i,$year,"5,8","");
    $ca_valide_mp[$i]=ca($i,$year,"5,8","mp");
    $ca_valide_sav[$i]=ca($i,$year,"5,8","sav")+ca($i,$year,"5,8","avoir");

    $objectif_mois=$objectif_facture[$year][$i]+$objectif_mp[$year][$i];
    if(isset($objectif_sav[$year][$i])) $objectif_mois+=$objectif_sav[$year][$i];
    
    $pcmois=100*($ca_valide_mp[$i]+$ca_valide[$i]+$ca_valide_sav[$i])/($objectif_mois);
    
//     $previsions="<td></td><td></td><td class=\"ca_nb\">".number_format($ca_valide[$i]+$ca_valide_mp[$i]+$ca_valide_sav[$i],2,',','')." &euro;</td>".tdvalue($pcmois,"%",false,100);
    $prevision=$ca_valide[$i]+$ca_attente[$i]+$ca_encours[$i]+$ca_valide_mp[$i]+$ca_encours_mp[$i]+$ca_valide_sav[$i]+$ca_encours_sav[$i];
    $pcprev=100*$prevision/($objectif_mois);
    $previsions="<td class=\"ca_nb\" onmouseover=\"showpopup('valide$i')\" onmouseout=\"hidepopup('valide$i')\">".number_format($ca_encours[$i]+$ca_encours_mp[$i]+$ca_encours_sav[$i],2,',','')." &euro;<div id=\"valide$i\" class=\"popup\">".formatlist("valide",null,$i)."</div></td><td class=\"ca_nb\" onmouseover=\"showpopup('attente$i')\" onmouseout=\"hidepopup('attente$i')\">".number_format($ca_attente[$i],2,',','')." &euro;<div id=\"attente$i\" class=\"popup\">".formatlist("attente",null,$i)."</div></td><td class=\"ca_nb\">".number_format($prevision,2,',','')." &euro;</td>".tdvalue($pcprev,"%",false,100);
	  
    if($year==$Y){

      $limit=null;
      if($i==$m) $limit=date("d");
      if($i<=$m){
	  $ca_validem1[$i]=ca($i,($year-1),"5,8","",$limit);
 	  //$ca_validem2[$i]=ca($i,($year-2),"5,8","",$limit);
	  $ca_validem1_mp[$i]=ca($i,($year-1),"5,8","mp",$limit);
 	  //$ca_validem2_mp[$i]=ca($i,($year-2),"5,8","mp",$limit);
	  $ca_validem1_sav[$i]=ca($i,($year-1),"5,8","sav",$limit);
 	  //$ca_validem2_sav[$i]=ca($i,($year-2),"5,8","sav",$limit);
      }else
	  $ca_validem1[$i]=$ca_validem2[$i]=$ca_validem1_mp[$i]=$ca_validem2_mp[$i]=$ca_validem1_sav[$i]=$ca_validem2_sav[$i]=0;
    }else{
	  $ca_validem1[$i]=ca($i,($year-1),"5,8","");
 	  //$ca_validem2[$i]=ca($i,($year-2),"5,8","");
	  $ca_validem1_mp[$i]=ca($i,($year-1),"5,8","mp");
 	  //$ca_validem2_mp[$i]=ca($i,($year-2),"5,8","mp");
	  $ca_validem1_sav[$i]=ca($i,($year-1),"5,8","sav");
 	  //$ca_validem2_sav[$i]=ca($i,($year-2),"5,8","sav");
    }
    
    $ca_valide_total=$ca_valide[$i]+$ca_valide_mp[$i]+$ca_valide_sav[$i];
    $ca_validem1_total=$ca_validem1[$i]+$ca_validem1_mp[$i]+$ca_validem1_sav[$i];
    $ca_validem2_total=$ca_validem2[$i]+$ca_validem2_mp[$i]+$ca_validem2_sav[$i];
    
    $total_valide+=$ca_valide_total;
    

    $nm1="";
    $nm2="";
    if($ca_valide_total>0 && $ca_validem1_total>0) $nm1=100*($ca_valide_total-$ca_validem1_total)/$ca_validem1_total;
    if($ca_valide_total>0 && $ca_validem2_total>0) $nm2=100*($ca_valide_total-$ca_validem2_total)/$ca_validem2_total;


    $encours=$pcencours="";
    if($year==$Y){
      if($i<$m){
	$anencours+=$objectif_mois;
	$encours=$ca_valide_total-$objectif_mois;
	$pcencours=100*$encours/$objectif_mois;
      }
      if($i==$m){
	$objencours=date("j")*$objectif_mois/date("t");
	$anencours+=$objencours;
	$encours=$ca_valide_total-$objencours;
	$pcencours=100*$encours/$objencours;
      }
    }
    
    echo "<tr>
      <td>".$i."-".$year."</td>
      <td class=\"ca_nb\">".number_format($objectif_mois,2,',','')." &euro;</td>
      <td class=\"ca_nb\">".number_format($ca_valide_total,2,',','')." &euro;</td>
      ".tdvalue($pcmois,"%",false,100).
      tdvalue($encours,"&euro;",true).
      tdvalue($pcencours,"%",true).
      tdvalue($ca_valide_total-$ca_validem1_total,"&euro;",true).
      tdvalue($nm1,"%",true).
      tdvalue($ca_valide_total-$ca_validem2_total,"&euro;",true).
      tdvalue($nm2,"%",true).
      $previsions.
    "</tr>";
}


$total_attente=array_sum($ca_attente);
$total_encours=array_sum($ca_encours)+array_sum($ca_encours_mp)+array_sum($ca_encours_sav);
$totalobjectif=array_sum($objectif_facture[$year])+array_sum($objectif_mp[$year]);
if(isset($objectif_sav[$year])) $totalobjectif+=array_sum($objectif_sav[$year]);
$pcmois=100*$total_valide/$totalobjectif;
$pcprev=100*($total_attente+$total_encours+$total_valide)/$totalobjectif;
$encours=$pcencours="";
if($year==$Y){
  $encours=$total_valide-$anencours;
  $pcencours=100*$encours/$anencours;
}
$total_validem1=array_sum($ca_validem1_mp)+array_sum($ca_validem1)+array_sum($ca_validem1_sav);
$nm1=100*(array_sum($ca_valide_mp)+array_sum($ca_valide)+array_sum($ca_valide_sav)-$total_validem1)/$total_validem1;
$total_validem2=array_sum($ca_validem2_mp)+array_sum($ca_validem2)+array_sum($ca_validem2_sav);
// $nm2=100*(array_sum($ca_valide_mp)+array_sum($ca_valide)+array_sum($ca_valide_sav)-$total_validem2)/$total_validem2;

echo "<tr style=\"font-weight:bold;\">
  <td>Total annÃ©e $year</td>
  <td class=\"ca_nb\">".$totalobjectif." &euro;</td>
  <td class=\"ca_nb\">".number_format($total_valide,2,',','')." &euro;</td>".
  tdvalue($pcmois,"%",false,100).
  tdvalue($encours,"&euro;",true).
  tdvalue($pcencours,"%",true).
  tdvalue(array_sum($ca_valide_mp)+array_sum($ca_valide)+array_sum($ca_valide_sav)-$total_validem1,"&euro;",true).
  tdvalue($nm1,"%",true).
  tdvalue(array_sum($ca_valide_mp)+array_sum($ca_valide)+array_sum($ca_valide_sav)-$total_validem2,"&euro;",true).
  tdvalue($nm2,"%",true).
  "<td class=\"ca_nb\">".number_format($total_encours,2,',','')." &euro;</td>
  <td class=\"ca_nb\">".number_format($total_attente,2,',','')." &euro;</td>
  <td class=\"ca_nb\">".number_format($total_attente+$total_encours+$total_valide,2,',','')." &euro;</td>
  <td class=\"ca_nb\">".number_format($pcprev,2,',','')."%</td>
</tr>";

echo "</table>";


echo "<p></p>
<table class=\"ca\">
<tr><td colspan=\"14\" class=\"ca_title\">Détail : Commande du site</td></tr>
<tr class=\"ca_head\">
  <td></td>
  <td>Objectif mensuel &euro;HT</td>
  <td>Chiffre d'affaires &euro;HT</td>
  <td>% Objectif du mois</td>
  <td>Av/ret / objectif &euro;HT</td>
  <td>Av/ret / objectif %</td>
  <td>Avance/retard / N-1 &euro;HT</td>
  <td>% N-1</td>
  <td>Avance/retard / N-2 &euro;HT</td>
  <td>% N-2</td>
  <td>Cde en cours validées</td>
  <td>Cde en attente de paiement &euro;HT</td>
  <td>Chiffre d'affaires prévisionnel</td>
  <td>% Objectif avec cde en cours</td>
</tr>";

// echo "<tr><td>Report</td><td></td><td></td><td></td><td></td><td></td><td></td><td class=\"ca_nb\" onmouseover=\"showpopup('reportsite1')\" onmouseout=\"hidepopup('reportsite1')\">".number_format($report_site[1],2,',','')." &euro;<div id=\"reportsite1\" class=\"popup\">".formatlist("report1","")."</div></td><td class=\"ca_nb\" onmouseover=\"showpopup('reportsite0')\" onmouseout=\"hidepopup('reportsite0')\">".number_format($report_site[0],2,',','')." &euro;<div id=\"reportsite0\" class=\"popup\">".formatlist("report0","")."</div></td><td class=\"ca_nb\">".number_format($report_site[0]+$report_site[1],2,',','')." &euro;</td><td></td></tr>";

$anencours=0;
$total_valide=$total_encours=$total_attente=0;
for($i=1;$i<=12;$i++){

    $total_valide+=$ca_valide[$i];
    $pcmois=100*$ca_valide[$i]/$objectif_facture[$year][$i];

    $previsions="<td></td><td></td><td class=\"ca_nb\">".number_format($ca_valide[$i],2,',','')." &euro;</td>".tdvalue($pcmois,"%",false,100);
//     if($Y==$year){
	$prevision=$ca_valide[$i]+$ca_attente[$i]+$ca_encours[$i];
	$pcprev=100*$prevision/($objectif_facture[$year][$i]);
	$previsions="<td class=\"ca_nb\" onmouseover=\"showpopup('validesite$i')\" onmouseout=\"hidepopup('validesite$i')\">".number_format($ca_encours[$i],2,',','')." &euro;<div id=\"validesite$i\" class=\"popup\">".formatlist("valide","",$i)."</div></td><td class=\"ca_nb\" onmouseover=\"showpopup('attentesite$i')\" onmouseout=\"hidepopup('attentesite$i')\">".number_format($ca_attente[$i],2,',','')." &euro;<div id=\"attentesite$i\" class=\"popup\">".formatlist("attente","",$i)."</div></td><td class=\"ca_nb\">".number_format($prevision,2,',','')." &euro;</td>".tdvalue($pcprev,"%",false,100);
//     }
    
    $nm1="";
    $nm2="";
    if($ca_valide[$i]>0 && $ca_validem1[$i]>0) $nm1=100*($ca_valide[$i]-$ca_validem1[$i])/$ca_validem1[$i];
    if($ca_valide[$i]>0 && $ca_validem2[$i]>0) $nm2=100*($ca_valide[$i]-$ca_validem2[$i])/$ca_validem2[$i];

    $encours=$pcencours="";
    if($year==$Y){
	if($i<$m){
	    $anencours+=$objectif_facture[$year][$i];
	    $encours=$ca_valide[$i]-$objectif_facture[$year][$i];
	    $pcencours=100*$encours/$objectif_facture[$year][$i];
	}
	if($i==$m){
	    $objencours=date("j")*$objectif_facture[$year][$i]/date("t");
	    $anencours+=$objencours;
	    $encours=$ca_valide[$i]-$objencours;
	    $pcencours=100*$encours/$objencours;
	}
    }
    
    echo "<tr>
      <td>".$i."-".$year."</td>
      <td class=\"ca_nb\">".$objectif_facture[$year][$i]." &euro;</td>
      <td class=\"ca_nb\">".number_format($ca_valide[$i],2,',','')." &euro;</td>".
      tdvalue($pcmois,"%",false,100).
      tdvalue($encours,"&euro;",true).
      tdvalue($pcencours,"%",true).
      tdvalue($ca_valide[$i]-$ca_validem1[$i],"&euro;",true).
      tdvalue($nm1,"%",true).
      tdvalue($ca_valide[$i]-$ca_validem2[$i],"&euro;",true).
      tdvalue($nm2,"%",true).
      $previsions.
    "</tr>";
}

$total_attente=array_sum($ca_attente);
$total_encours=array_sum($ca_encours);
$totalobjectif=array_sum($objectif_facture[$year]);
$pcmois=100*$total_valide/$totalobjectif;
$pcprev=100*($total_attente+$total_encours+$total_valide)/$totalobjectif;
$encours=$pcencours="";
if($year==$Y) {
  $encours=$total_valide-$anencours;
  $pcencours=100*$encours/$anencours;
}
$total_validem1=array_sum($ca_validem1);
$nm1="";
if($total_validem1>0) $nm1=100*(array_sum($ca_valide)-$total_validem1)/$total_validem1;
$total_validem2=array_sum($ca_validem2);
$nm2="";
if($total_validem2>0) $nm2=100*(array_sum($ca_valide)-$total_validem2)/$total_validem2;

echo "<tr style=\"font-weight:bold;\">
  <td>Total annÃ©e $year</td>
  <td class=\"ca_nb\">".$totalobjectif." &euro;</td>
  <td class=\"ca_nb\">".number_format($total_valide,2,',','')." &euro;</td>".
  tdvalue($pcmois,"%",false,100).
  tdvalue($encours,"&euro;",true).
  tdvalue($pcencours,"%",true).
  tdvalue(array_sum($ca_valide)-$total_validem1,"&euro;",true).
  tdvalue($nm1,"%",true).
  tdvalue(array_sum($ca_valide)-$total_validem2,"&euro;",true).
  tdvalue($nm2,"%",true).
  "<td class=\"ca_nb\">".number_format($total_encours,2,',','')." &euro;</td>
  <td class=\"ca_nb\">".number_format($total_attente,2,',','')." &euro;</td>
  <td class=\"ca_nb\">".number_format($total_attente+$total_encours+$total_valide,2,',','')." &euro;</td>
  <td class=\"ca_nb\">".number_format($pcprev,2,',','')."%</td>
</tr>";

echo "</table>";

echo "<p></p>
<table class=\"ca\">
<tr><td colspan=\"14\" class=\"ca_title\">Détail : Commandes SAV</td></tr>
<tr class=\"ca_head\">
  <td></td>
  <td>Objectif mensuel &euro;HT</td>
  <td>Chiffre d'affaires &euro;HT</td>
  <td>% Objectif du mois</td>
  <td>Av/ret / objectif &euro;HT</td>
  <td>Av/ret / objectif %</td>
  <td>Av/ret / N-1 &euro;HT</td>
  <td>% N-1</td>
  <td>Avance/retard / N-2 &euro;HT</td>
  <td>% N-2</td><td>Cde en cours validés</td>
  <td>Cde en attente de paiement &euro;HT</td>
  <td>Chiffre d'affaires prévisionnel</td>
  <td>% Objectif avec cde en cours</td>
</tr>";

// echo "<tr><td>Report</td><td></td><td></td><td></td><td></td><td></td><td></td><td class=\"ca_nb\" onmouseover=\"showpopup('reportsav1')\" onmouseout=\"hidepopup('reportsav1')\">".number_format($report_sav[1],2,',','')." &euro;<div id=\"reportsav1\" class=\"popup\">".formatlist("report1","sav")."</div></td><td class=\"ca_nb\" onmouseover=\"showpopup('reportsav0')\" onmouseout=\"hidepopup('reportsav0')\">".number_format($report_sav[0],2,',','')." &euro;<div id=\"reportsav0\" class=\"popup\">".formatlist("report0","sav")."</div></td><td class=\"ca_nb\">".number_format($report_sav[0]+$report_sav[1],2,',','')." &euro;</td><td></td></tr>";

$anencours=0;
$total_valide=$total_encours=$total_attente=0;
for($i=1;$i<=12;$i++){

    $total_valide+=$ca_valide_sav[$i];

    $previsions="<td></td><td></td><td></td><td></td>";
    if($Y==$year){
	$prevision=$ca_valide_sav[$i]+$ca_encours_sav[$i];
	$previsions="<td class=\"ca_nb\" onmouseover=\"showpopup('validesav$i')\" onmouseout=\"hidepopup('validesav$i')\">".number_format($ca_encours_sav[$i],2,',','')." &euro;<div id=\"validesav$i\" class=\"popup\">".formatlist("valide","sav")."</div></td><td class=\"ca_nb\" onmouseover=\"showpopup('attentesav$i')\" onmouseout=\"hidepopup('attentesav$i')\">".number_format(0,2,',','')." &euro;<div id=\"attentesav$i\" class=\"popup\">".formatlist("attente","sav",$i)."</div></td><td class=\"ca_nb\">".number_format($prevision,2,',','')." &euro;</td><td class=\"ca_nb\"></td>";
    }
    
    $nm1="";
    $nm2="";
    if($ca_valide_sav[$i]>0 && $ca_validem1_sav[$i]>0) $nm1=100*($ca_valide_sav[$i]-$ca_validem1_sav[$i])/$ca_validem1_sav[$i];
    if($ca_valide_sav[$i]>0 && $ca_validem2_sav[$i]>0) $nm2=100*($ca_valide_sav[$i]-$ca_validem2_sav[$i])/$ca_validem2_sav[$i];
    
    $objectif_mois=$pcmois=$encours=$pcencours="";
    if(isset($objectif_sav[$year][$i])){
	$objectif_mois=$objectif_sav[$year][$i];
	$pcmois=100*$ca_valide_sav[$i]/$objectif_sav[$year][$i];
	
	$objencours=$objectif_sav[$year][$i];
	if($year<=$Y){
	    $encours=$ca_valide_sav[$i]-$objectif_sav[$year][$i];
	    $pcencours=100*$encours/$objectif_sav[$year][$i];
	    if($Y==$year && $i==$m){
		$objencours=date("j")*$objectif_sav[$year][$i]/date("t");
		$encours=$ca_valide_sav[$i]-$objencours;
		$pcencours=100*$encours/$objencours;
	    }
	}
	$anencours+=$objencours;
    }
    
    echo "<tr>
      <td>".$i."-".$year."</td>
      <td class=\"ca_nb\">".$objectif_mois." &euro;</td>
      <td class=\"ca_nb\">".number_format($ca_valide_sav[$i],2,',','')." &euro;</td>".
      tdvalue($pcmois,"%",false,100).
      tdvalue($encours,"&euro;",true).
      tdvalue($pcencours,"%",true).
      tdvalue($ca_valide_sav[$i]-$ca_validem1_sav[$i],"&euro;",true).
      tdvalue($nm1,"%",true).
      tdvalue($ca_valide_sav[$i]-$ca_validem2_sav[$i],"&euro;",true).
      tdvalue($nm2,"%",true).
      $previsions.
    "</tr>";
}

$total_encours=array_sum($ca_encours_sav);
$total_validem1=array_sum($ca_validem1_sav);
$nm1="";
if($total_validem1>0) $nm1=100*(array_sum($ca_valide_sav)-$total_validem1)/$total_validem1;
$total_validem2=array_sum($ca_validem2_sav);
$nm2="";
if($total_validem2>0) $nm2=100*(array_sum($ca_valide_sav)-$total_validem2)/$total_validem2;

$totalobjectif=0;
if(isset($objectif_sav[$year])){
    $totalobjectif=array_sum($objectif_sav[$year]);
    if($year<=$Y){
	$pcmois=100*$total_valide/$totalobjectif;
	$encours=$total_valide-$anencours;
	if($anencours>0) $pcencours=100*$encours/$anencours;
    }
}

echo "<tr style=\"font-weight:bold;\">
  <td>Total annÃ©e $year</td>
  <td class=\"ca_nb\">".$totalobjectif." &euro;</td>
  <td class=\"ca_nb\">".number_format($total_valide,2,',','')." &euro;</td>".
  tdvalue($pcmois,"%",false,100).
  tdvalue($encours,"&euro;",true).
  tdvalue($pcencours,"%",true).
  tdvalue(array_sum($ca_valide_sav)-$total_validem1,"&euro;",true).
  tdvalue($nm1,"%",true).
  tdvalue(array_sum($ca_valide_sav)-$total_validem2,"&euro;",true).
  tdvalue($nm2,"%",true).
  "<td class=\"ca_nb\">".number_format($total_encours,2,',','')." &euro;</td>
  <td class=\"ca_nb\">".number_format(0,2,',','')." &euro;</td>
  <td class=\"ca_nb\">".number_format($total_encours+$total_valide,2,',','')." &euro;</td>
  <td class=\"ca_nb\"></td>
</tr>";

echo "</table>";

echo "<p></p>
<table class=\"ca\">
<tr><td colspan=\"14\" class=\"ca_title\">Détail : Commandes market place</td></tr>
<tr class=\"ca_head\">
  <td></td>
  <td>Objectif mensuel &euro;HT</td>
  <td>Chiffre d'affaires &euro;HT</td>
  <td>% Objectif du mois</td>
  <td>Av/ret / objectif &euro;HT</td>
  <td>Av/ret / objectif %</td>
  <td>Av/ret / N-1 &euro;HT</td>
  <td>% N-1</td>
  <td>Av/ret / N-2 &euro;HT</td>
  <td>% N-2</td>
  <td>Cde en cours validées</td>
  <td>Cde en attente de paiement &euro;HT</td>
  <td>Chiffre d'affaires prévisionnel</td>
  <td>% Objectif avec cde en cours</td>
</tr>";

// echo "<tr><td>Report</td><td></td><td></td><td></td><td></td><td></td><td></td><td class=\"ca_nb\" onmouseover=\"showpopup('reportmp1')\" onmouseout=\"hidepopup('reportmp1')\">".number_format($report_mp[1],2,',','')." &euro;<div id=\"reportmp1\" class=\"popup\">".formatlist("report1","mp")."</div></td><td class=\"ca_nb\"></td><td class=\"ca_nb\">".number_format($report_mp[1],2,',','')." &euro;</td><td></td></tr>";

$anencours=0;
$total_valide_mp=0;
for($i=1;$i<=12;$i++){

    $total_valide_mp+=$ca_valide_mp[$i];
    
    $previsions="<td></td><td></td><td class=\"ca_nb\">".number_format($total_valide_mp,2,',','')." &euro;</td>".tdvalue($pcmois,"%",false,100);
//     if($Y==$year){
	$prevision=$ca_valide_mp[$i]+$ca_encours_mp[$i];
	$pcprev=100*$prevision/$objectif_mp[$year][$i];
	$previsions="<td class=\"ca_nb\" onmouseover=\"showpopup('validemp$i')\" onmouseout=\"hidepopup('validemp$i')\">".number_format($ca_encours_mp[$i],2,',','')." &euro;<div id=\"validemp$i\" class=\"popup\">".formatlist("valide","mp",$i)."</div></td><td></td><td class=\"ca_nb\">".number_format($prevision,2,',','')." &euro;</td>".tdvalue($pcprev,"%",false,100);
//     }

    $pcmois=100*$ca_valide_mp[$i]/$objectif_mp[$year][$i];

    
    $nm1="";
    $nm2="";
    if($ca_valide_mp[$i]>0 && $ca_validem1_mp[$i]>0) $nm1=100*($ca_valide_mp[$i]-$ca_validem1_mp[$i])/$ca_validem1_mp[$i];
    if($ca_valide_mp[$i]>0 && $ca_validem2_pp[$i]>0) $nm2=100*($ca_valide_mp[$i]-$ca_validem2_mp[$i])/$ca_validem2_mp[$i];
    

    $encours=$pcencours="";
    if($year==$Y){
	if($i<$m){
	    $anencours+=$objectif_mp[$year][$i];
	    $encours=number_format($ca_valide_mp[$i]-$objectif_mp[$year][$i],2,',','');
	    $pcencours=100*$encours/$objectif_mp[$year][$i];
	}
	if($i==$m){
	    $objencours=date("j")*$objectif_mp[$year][$i]/date("t");
	    $anencours+=$objencours;
	    $encours=number_format($ca_valide_mp[$i]-$objencours,2,',','');
	    $pcencours=100*$encours/$objencours;
	}
    }
    
    echo "<tr>
      <td>".$i."-".$year."</td>
      <td class=\"ca_nb\">".$objectif_mp[$year][$i]." &euro;</td>
      <td class=\"ca_nb\">".number_format($ca_valide_mp[$i],2,',','')." &euro;</td>".
      tdvalue($pcmois,"%",false,100).
      tdvalue($encours,"&euro;",true).
      tdvalue($pcencours,"%",true).
      tdvalue($ca_valide_mp[$i]-$ca_validem1_mp[$i],"&euro;",true).
      tdvalue($nm1,"%",true).
      tdvalue($ca_valide_mp[$i]-$ca_validem2_mp[$i],"&euro;",true).
      tdvalue($nm2,"%",true).
      $previsions.
   "</tr>";
}

$totalobjectif_mp=array_sum($objectif_mp[$year]);
$pcmois=100*$total_valide_mp/$totalobjectif_mp;
$encours=$pcencours="";
if($year==$Y){
  $encours=number_format($total_valide_mp-$anencours,2,',','');
  $pcencours=100*$encours/$anencours;
}
$total_validem1=array_sum($ca_validem1_mp);
$nm1="";
if($total_validem1>0) $nm1=100*(array_sum($ca_valide_mp)-$total_validem1)/$total_validem1;
$total_validem2=array_sum($ca_validem2_mp);
$nm2="";
if($total_validem2>0) $nm2=100*(array_sum($ca_valide_mp)-$total_validem2)/$total_validem2;

echo "<tr style=\"font-weight:bold;\">
  <td>Total annÃ©e $year</td>
  <td class=\"ca_nb\">".$totalobjectif_mp." &euro;</td>
  <td class=\"ca_nb\">".number_format($total_valide_mp,2,',','')." &euro;</td>".
  tdvalue($pcmois,"%",false,100).
  tdvalue($encours,"&euro;",true).
  tdvalue($pcencours,"%",true).
  tdvalue(array_sum($ca_valide_mp)-$total_validem1,"&euro;",true).
  tdvalue($nm1,"%",true).
  tdvalue(array_sum($ca_valide_mp)-$total_validem2,"&euro;",true).
  tdvalue($nm2,"%",true).
  "<td class=\"ca_nb\">".number_format(array_sum($ca_encours_mp),2,',','')." &euro;</td>
  <td></td>
  <td class=\"ca_nb\">".number_format(array_sum($ca_encours_mp)+$total_valide_mp,2,',','')." &euro;</td>
  <td class=\"ca_nb\">".number_format(100*($total_encours_mp+$total_valide_mp)/array_sum($objectif_mp[$year]),2,',','')."%</td>
</tr>";

echo "</table>";


echo "<p></p>
<table class=\"ca\">
<tr><td colspan=\"14\" class=\"ca_title\">Détail :  Commandes code partenaire</td></tr>
<tr class=\"ca_head\">
  <td></td>
  <td>Objectif mensuel &euro;HT</td>
  <td>Chiffre d'affaires &euro;HT</td>
  <td>% Objectif du mois</td>
  <td>Av/ret / objectif &euro;HT</td>
  <td>Av/ret / objectif %</td>
  <td>Av/ret / N-1 &euro;HT</td>
  <td>% N-1</td>
  <td>Av/ret / N-2 &euro;HT</td>
  <td>% N-2</td>
  <td>Cde en cours validées</td>
  <td>Cde en attente de paiement &euro;HT</td>
  <td>Chiffre d'affaires prévisionnel</td>
  <td>% Objectif avec cde en cours</td>
</tr>";


$total_partenaire=$total_partenaire1=$total_partenaire2=0;
for($i=1;$i<=12;$i++){
    $ca_partenaire=ca($i,$year,array(5,8),"",null," AND LENGTH(cadeauid)>4");
    $ca_partenaire1=ca($i,$year-1,array(5,8),"",null," AND LENGTH(cadeauid)>4");
    $ca_partenaire2=ca($i,$year-2,array(5,8),"",null," AND LENGTH(cadeauid)>4");
    
    $total_partenaire+=$ca_partenaire;
    
    $nm1="";
    $nm2="";
    if($ca_partenaire>0 && $ca_partenaire1>0) $nm1=100*($ca_partenaire-$ca_partenaire1)/$ca_partenaire;
    if($ca_partenaire>0 && $ca_partenaire2>0) $nm2=100*($ca_partenaire-$ca_partenaire2)/$ca_partenaire;
    

    $ca_nm1=$ca_nm2="";
    if($year==$Y){
	if($i<=$m){
	    $total_partenaire1+=$ca_partenaire1;
	    $total_partenaire2+=$ca_partenaire2;
	    $ca_nm1=$ca_partenaire-$ca_partenaire1;
	    $ca_nm2=$ca_partenaire-$ca_partenaire2;
	}
    }

    echo "<tr>
      <td>".$i."-".$year."</td>
      <td class=\"ca_nb\"></td>
      <td class=\"ca_nb\">".number_format($ca_partenaire,2,',','')." &euro;</td>
      <td></td>
      <td></td>
      <td></td>".
      tdvalue($ca_nm1,"&euro;",true).
      tdvalue($nm1,"%",true).
      tdvalue($ca_nm2,"&euro;",true).
      tdvalue($nm2,"%",true).
      "<td></td>
      <td></td>
      <td></td>
      <td></td>
    </tr>";
}

$nm1="";
if($total_partenaire1>0) $nm1=100*($total_partenaire-$total_partenaire1)/$total_partenaire1;
$nm2="";
if($total_partenaire2>0) $nm2=100*($total_partenaire-$total_partenaire2)/$total_partenaire2;

echo "<tr style=\"font-weight:bold;\">
  <td>Total année $year</td>
  <td class=\"ca_nb\"></td>
  <td class=\"ca_nb\">".number_format($total_partenaire,2,',','')." &euro;</td>
  <td></td>
  <td></td>
  <td></td>".
  tdvalue($total_partenaire-$total_partenaire1,"&euro;",true).
  tdvalue($nm1,"%",true).
  tdvalue($total_partenaire-$total_partenaire2,"&euro;",true).
  tdvalue($nm2,"%",true).
  "<td></td>
  <td></td>
  <td></td>
  <td></td>
</tr>";

echo "</table>";

include "./footer.php";

function ca($mois,$annee,$etat,$prov="",$limit=null,$and=""){

    global $DB_site2;
	if($mois<10) $mois="0".$mois;
	$chiffremois=0;
	if(is_array($etat)) $etat=implode(",",$etat);
	$sql="select * from facture WHERE valide in (".$etat.") and dateexpedition LIKE '".$annee."-".$mois."-%' AND provenance='".$prov."'".$and;
	if(!is_null($limit)) $sql.=" AND dateexpedition<='".$annee."-".$mois."-".$limit."'";
	$cams=$DB_site2->query($sql);

	while ($cam=$DB_site2->fetch_array($cams)) {
	      $f=new facture($DB_site2,$cam);
	      $chiffremois += $f->totalht; //print "<br>".$f->facturid." ".$f->numerofacture." ".$f->totalht." ".$f->totalttc." ".round(100-100*$f->totalttc/$f->totalht,2)." ".$f->totallignesht." ".$f->portht." ".$f->cadeauht;
	      $ttc+=$f->totalttc;
// 	      if($annee==2014 && $mois==4) print date("d/m/Y",$f->dateexpedition)." ".$f->numerofacture." ".$f->totalht."<br>";
	}
	return $chiffremois;
}

function ca_valide($mois,$annee,$prov=""){
	global $DB_site2;
	global $listfacture;

	if($mois<10) $mois="0".$mois;
	$chiffremois=array();
	if(is_array($etat)) $etat=implode(",",$etat);
	$sql="select * from facture WHERE valide IN (1,7) and heurefacture<".time()." AND provenance='".$prov."'";

	$cams=$DB_site2->query($sql);
	while ($cam=$DB_site2->fetch_array($cams)) {
	      $f=new facture($DB_site2,$cam);
	      if(date("Y",$f->datelivraison)==$annee) {
		  $chiffremois[date("n",$f->datelivraison)] += $f->totalht ;
		  $listfacture["valide"][$prov][date("n",$f->datelivraison)][]=$f;
	      }
	}

// 	$report=report($annee,$prov);
	return $chiffremois;
}

function ca_attente($mois,$annee,$prov=""){
	global $DB_site2;
	global $listfacture;

	if($mois<10) $mois="0".$mois;
	$chiffremois=array();
	if(is_array($etat)) $etat=implode(",",$etat);
	/*$sql="select factureid, SUM(prix*qte)/(1+tva/100) montant, prixport/(1+tvaport/100) prixport, montantremise, montantcadeau from lignefacture lf inner join facture f 
                        on (f.facturid = lf.factureid) where remboursement = '0' and moyenid in (2,4,5) and supprimee='0' and valide=0 and datefacture LIKE '".$annee."-".$mois."-%' AND provenance<>'mp' group by factureid";*/
	$sql="select * from facture WHERE valide=0 and moyenid in (2,4,5) and heurefacture<".time()." AND provenance='".$prov."'";

	$cams=$DB_site2->query($sql);
	while ($cam=$DB_site2->fetch_array($cams)) {
	      $f=new facture($DB_site2,$cam);
	      if(date("Y",$f->datelivraison)==$annee) {
		  $chiffremois[date("n",$f->datelivraison)] += $f->totalht ;
		  $listfacture["attente"][$prov][date("n",$f->datelivraison)][]=$f;
	      }
	}
// 	$report=report($annee,$prov);
	return $chiffremois;
}

function report($annee,$prov=""){
	global $DB_site2;
	global $listfacture;

	$jan1=mktime(0,0,0,1,1,$annee);
	$report=array();

	$sql="select * from facture where ((valide=0 and moyenid in (2,4,5)) or valide in (1,7)) and heurefacture<".$jan1." and provenance='".$prov."'";

	$cams=$DB_site2->query($sql);
	while ($cam=$DB_site2->fetch_array($cams)) {
	      $f=new facture($DB_site2,$cam);
	      if($f->valide==7) $f->valide=1;
	      $report[$f->valide] += $f->totalht ;
	      $listfacture["report".$f->valide][$prov][]=$f;
	}
	return $report;
}

function formatlist($type,$prov=null,$mois=null){
	global $listfacture;

	$html="<table>";
	if($prov===null){
	   if(!is_array($listfacture[$type][""][$mois])) $listfacture[$type][""][$mois]=array();
	   if(!is_array($listfacture[$type]["mp"][$mois])) $listfacture[$type]["mp"][$mois]=array();
	   if(!is_array($listfacture[$type]["sav"][$mois])) $listfacture[$type]["sav"][$mois]=array();
	   $list=array_merge($listfacture[$type][""][$mois],$listfacture[$type]["mp"][$mois]);
	}else
	   $list=$listfacture[$type][$prov][$mois];

	if(is_null($list)) return "";
	
	foreach($list as $f)
	    $html.="<tr>
	      <td><a href=\"facture.php?action=editer&facturid=".$f->facturid."\" target=\"_blank\">".$f->facturid."</a></td>
	      <td>".$f->utilisateur->nom."</td>
	      <td>".date("d/m/Y",$f->datecommande)."</td>
	      <td align=\"right\">".number_format($f->totalht,2,',','')." &euro;</td>
	    </tr>";

	$html.="</table>";
	return $html;
	
}

function tdvalue($value,$sym="",$sign=null,$climit=0){
	if($value=="") return "<td></td>";
	return "<td class=\"ca_nb\" style=\"color:".($value>$climit?"green":"red").";\">".($sign&&$value>0?"+":"").number_format($value,2,',','').$sym."</td>";
}

?>