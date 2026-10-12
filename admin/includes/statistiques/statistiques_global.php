<?php
for($i=1;$i<=12;$i++){

	$ca_valide[$i]     = ca($i,$year,"5,8","");
	$ca_valide_mp[$i]  = ca($i,$year,"5,8","mp");
	$ca_valide_sav[$i] = ca($i,$year,"5,8","sav") + ca($i,$year,"5,8","avoir");

    $objectif_mois = $objectif_facture[$year][$i] + $objectif_mp[$year][$i];
    if(isset($objectif_sav[$year][$i])) $objectif_mois += $objectif_sav[$year][$i];
    
    $pcmois = 100*($ca_valide_mp[$i]+$ca_valide[$i]+$ca_valide_sav[$i])/($objectif_mois);

	$prevision  = $ca_valide[$i]+$ca_attente[$i]+$ca_encours[$i]+$ca_valide_mp[$i]+$ca_encours_mp[$i]+$ca_valide_sav[$i]+$ca_encours_sav[$i];
	$pcprev     = 100*$prevision/($objectif_mois);
	
	$previsions = ""
	. "<td class=\"ca_nb\" onmouseover=\"showpopup('valide$i')\" onmouseout=\"hidepopup('valide$i')\">"
		. number_format($ca_encours[$i]+$ca_encours_mp[$i]+$ca_encours_sav[$i],2,',','')." &euro;<div id=\"valide$i\" class=\"popup\">".formatlist("valide",null,$i)."</div>"
	. "</td>"
	. "<td class=\"ca_nb\" onmouseover=\"showpopup('attente$i')\" onmouseout=\"hidepopup('attente$i')\">"
		. number_format($ca_attente[$i],2,',','')." &euro;<div id=\"attente$i\" class=\"popup\">".formatlist("attente",null,$i)."</div>"
	. "</td>"
	. "<td class=\"ca_nb\">".number_format($prevision,2,',','')." &euro;</td>"
	. tdvalue($pcprev,"%",false,100);

	if($year == $Y){

		$limit=null;

		if($i==$m) $limit=date("d");

		if($i<=$m){
			$ca_validem1[$i]       = ca($i,($year-1),"5,8","",$limit);
			//$ca_validem2[$i]     = ca($i,($year-2),"5,8","",$limit);
			$ca_validem1_mp[$i]    = ca($i,($year-1),"5,8","mp",$limit);
			//$ca_validem2_mp[$i]  = ca($i,($year-2),"5,8","mp",$limit);
			$ca_validem1_sav[$i]   = ca($i,($year-1),"5,8","sav",$limit);
			//$ca_validem2_sav[$i] = ca($i,($year-2),"5,8","sav",$limit);
		} else {
			$ca_validem1[$i]=$ca_validem2[$i]=$ca_validem1_mp[$i]=$ca_validem2_mp[$i]=$ca_validem1_sav[$i]=$ca_validem2_sav[$i]=0;
		}
		
	} else {
		$ca_validem1[$i]       = ca($i,($year-1),"5,8","");
		//$ca_validem2[$i]     = ca($i,($year-2),"5,8","");
		$ca_validem1_mp[$i]    = ca($i,($year-1),"5,8","mp");
		//$ca_validem2_mp[$i]  = ca($i,($year-2),"5,8","mp");
		$ca_validem1_sav[$i]   = ca($i,($year-1),"5,8","sav");
		//$ca_validem2_sav[$i] = ca($i,($year-2),"5,8","sav");
	}

	$ca_valide_total   = $ca_valide[$i] + $ca_valide_mp[$i] + $ca_valide_sav[$i];
	$ca_validem1_total = $ca_validem1[$i] + $ca_validem1_mp[$i] + $ca_validem1_sav[$i];
	$ca_validem2_total = $ca_validem2[$i] + $ca_validem2_mp[$i] + $ca_validem2_sav[$i];

	$total_valide += $ca_valide_total;

    $nm1="";
    $nm2="";
    if($ca_valide_total>0 && $ca_validem1_total>0) $nm1=100*($ca_valide_total-$ca_validem1_total)/$ca_validem1_total;
    if($ca_valide_total>0 && $ca_validem2_total>0) $nm2=100*($ca_valide_total-$ca_validem2_total)/$ca_validem2_total;


    $encours=$pcencours="";

    if($year == $Y){

		if($i<$m){

			$anencours += $objectif_mois;
			$encours   = $ca_valide_total-$objectif_mois;
			$pcencours = 100*$encours/$objectif_mois;
		}

		if($i==$m){

			$objencours = date("j")*$objectif_mois/date("t");
			$anencours  += $objencours;
			$encours    = $ca_valide_total-$objencours;
			$pcencours  = 100*$encours/$objencours;
		}
    }

    $sStatsGlobales .= ""
    . "<tr>"
	    . "<td>".$i."-".$year."</td>"
	    . "<td class=\"ca_nb\">".number_format($objectif_mois,2,',','')." &euro;</td>"
	    . "<td class=\"ca_nb\">".number_format($ca_valide_total,2,',','')." &euro;</td>"
	    . tdvalue($pcmois,"%",false,100)
	    . tdvalue($encours,"&euro;",true)
      	. tdvalue($pcencours,"%",true)
      	. tdvalue($ca_valide_total-$ca_validem1_total,"&euro;",true)
      	. tdvalue($nm1,"%",true)
      	. tdvalue($ca_valide_total-$ca_validem2_total,"&euro;",true)
      	. tdvalue($nm2,"%",true)
      	. $previsions
  	. "</tr>";
}

$total_attente = array_sum($ca_attente);
$total_encours = array_sum($ca_encours)+array_sum($ca_encours_mp)+array_sum($ca_encours_sav);
$totalobjectif = array_sum($objectif_facture[$year])+array_sum($objectif_mp[$year]);

if(isset($objectif_sav[$year])) $totalobjectif+=array_sum($objectif_sav[$year]);

$pcmois  = 100*$total_valide/$totalobjectif;
$pcprev  = 100*($total_attente+$total_encours+$total_valide)/$totalobjectif;
$encours = $pcencours = "";

if($year==$Y){
	$encours   = $total_valide-$anencours;
	$pcencours = 100*$encours/$anencours;
}

$total_validem1 = array_sum($ca_validem1_mp)+array_sum($ca_validem1)+array_sum($ca_validem1_sav);
$nm1            = 100*(array_sum($ca_valide_mp)+array_sum($ca_valide)+array_sum($ca_valide_sav)-$total_validem1)/$total_validem1;
$total_validem2 = array_sum($ca_validem2_mp)+array_sum($ca_validem2)+array_sum($ca_validem2_sav);
//$nm2=100*(array_sum($ca_valide_mp)+array_sum($ca_valide)+array_sum($ca_valide_sav)-$total_validem2)/$total_validem2;

$sStatsGlobales .= ""
	. "<tr style=\"font-weight:bold;\">"
		. "<td>Total année $year</td>"
		. "<td class=\"ca_nb\">".$totalobjectif." &euro;</td>"
		. "<td class=\"ca_nb\">".number_format($total_valide,2,',','')." &euro;</td>"
		. tdvalue($pcmois,"%",false,100)
		. tdvalue($encours,"&euro;",true)
	  	. tdvalue($pcencours,"%",true)
	  	. tdvalue(array_sum($ca_valide_mp)+array_sum($ca_valide)+array_sum($ca_valide_sav)-$total_validem1,"&euro;",true)
	  	. tdvalue($nm1,"%",true)
	  	. tdvalue(array_sum($ca_valide_mp)+array_sum($ca_valide)+array_sum($ca_valide_sav)-$total_validem2,"&euro;",true)
	  	. tdvalue($nm2,"%",true)
	  	. "<td class=\"ca_nb\">".number_format($total_encours,2,',','')." &euro;</td>"
	  	. "<td class=\"ca_nb\">".number_format($total_attente,2,',','')." &euro;</td>"
	  	. "<td class=\"ca_nb\">".number_format($total_attente+$total_encours+$total_valide,2,',','')." &euro;</td>"
	  	. "<td class=\"ca_nb\">".number_format($pcprev,2,',','')."%</td>"
  	. "</tr>"
	. "</table>";
?>

<table class="ca">
	<tr>
		<td colspan="14" class="ca_title">Cumul chiffre d'affaire</td>
	</tr>
	<tr class="ca_head">
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
	</tr>
	<?php echo $sStatsGlobales; ?>