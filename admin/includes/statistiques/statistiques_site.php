<?php
$anencours    = 0;
$total_valide = $total_encours = $total_attente = 0;

for($i=1;$i<=12;$i++){

    $ca_valide[$i] = ca($i,$year,"5,8","");

    if($year == $Y){

        $limit=null;

        if($i==$m) $limit=date("d");

    if($i<=$m){
        $ca_validem1[$i] = ca($i,($year-1),"5,8","",$limit);
        $ca_validem2[$i] = ca($i,($year-2),"5,8","",$limit);
    } else {
        $ca_validem1[$i] = $ca_validem2[$i] = 0;
    }

    } else {
        $ca_validem1[$i] = ca($i,($year-1),"5,8","");
        $ca_validem2[$i] = ca($i,($year-2),"5,8","");
    }

    $total_valide += $ca_valide[$i];
    $pcmois       = 100*$ca_valide[$i]/$objectif_facture[$year][$i];

    $prevision = $ca_valide[$i]+$ca_attente[$i]+$ca_encours[$i];
    $pcprev    = 100*$prevision/($objectif_facture[$year][$i]);

    $previsions = ""
    . "<td class=\"ca_nb\" onmouseover=\"showpopup('validesite$i')\" onmouseout=\"hidepopup('validesite$i')\">".number_format($ca_encours[$i],2,',','')." &euro;<div id=\"validesite$i\" class=\"popup\">".formatlist("valide","",$i)."</div></td>"
    . "<td class=\"ca_nb\" onmouseover=\"showpopup('attentesite$i')\" onmouseout=\"hidepopup('attentesite$i')\">".number_format($ca_attente[$i],2,',','')." &euro;<div id=\"attentesite$i\" class=\"popup\">".formatlist("attente","",$i)."</div></td>"
    . "<td class=\"ca_nb\">".number_format($prevision,2,',','')." &euro;</td>"
    . tdvalue($pcprev,"%",false,100);
    
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
    
    $sStatsSite .= "<tr>
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

$sStatsSite .= "<tr style=\"font-weight:bold;\">
  <td>Total année $year</td>
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

$sStatsSite .= "</table>";

?>
<table class="ca">
  <tr><td colspan="14" class="ca_title">Détail : Commande du site</td></tr>
  <tr class="ca_head">
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
  </tr>
  <?php echo $sStatsSite; ?>