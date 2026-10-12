<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";
include "../class/facture.class.php";
ini_set('display_errors',0);
ini_set("max_execution_time",180);
ini_set("memory_limit","256M");


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

<h1>CA enregistré validé</h1>";
/*
$chiffrejour = 0 ;
$cajs=$DB_site2->query("select factureid, SUM((prix/(1+tva/100))*qte) montant, SUM(qte*prix) AS ttc, prixport/(1+tvaport/100) AS prixportht, montantcadeau from lignefacture lf inner join facture f 
                        on (f.facturid = lf.factureid) where remboursement = '0' and valide in (1, 5, 7, 8) and heurefacture >= ".date_create("today")->gettimestamp()." group by factureid");
while ($caj=$DB_site2->fetch_array($cajs)) {
    if($caj['montant']!=0){
  $tvamoy=$caj['ttc']/$caj['montant'];
  $chiffrejour += $caj[montant] + $caj[prixportht] - $caj[montantcadeau]/$tvamoy ;
    }
}
$chiffrejour = number_format($chiffrejour, 2, ",", " ") ;

$chiffremois = 0 ;
$cams=$DB_site2->query("select factureid, SUM((prix/(1+tva/100))*qte) montant, SUM(qte*prix) AS ttc, prixport/(1+tvaport/100) AS prixportht, montantcadeau from lignefacture lf inner join facture f 
                        on (f.facturid = lf.factureid) where valide in (1, 5, 7, 8) and heurefacture >= ".date_create("first day of this month")->gettimestamp()." group by factureid");
while ($cam=$DB_site2->fetch_array($cams)) {
    if($cam['montant']!=0){
  $tvamoy=$cam['ttc']/$cam['montant'];
  $chiffremois += $cam[montant] + $cam[prixportht] - $cam[montantcadeau]/$tvamoy ;
    }
}
$chiffremois = number_format($chiffremois, 2, ",", " ") ;

$chiffreannee = 0 ;
$caas=$DB_site2->query("select factureid, SUM((prix/(1+tva/100))*qte) montant, SUM(qte*prix) AS ttc, prixport/(1+tvaport/100) AS prixportht, montantcadeau from lignefacture lf left join facture f 
                        on (f.facturid = lf.factureid) where valide in (1, 5, 7, 8) and heurefacture >= ".date_create("first day of january")->gettimestamp()." group by factureid");

while ($caa=$DB_site2->fetch_array($caas)) {
    if($caa['montant']!=0){
  $tvamoy=$caa['ttc']/$caa['montant'];
  $chiffreannee += $caa[montant] + $caa[prixportht] - $caa[montantcadeau]/$tvamoy ;
    }
}
$chiffreannee = number_format($chiffreannee, 2, ",", " ") ;

$chiffretotal = 0 ;
$cats=$DB_site2->query("select factureid, SUM((prix/(1+tva/100))*qte) montant, SUM(qte*prix) AS ttc, prixport/(1+tvaport/100) AS prixportht, montantcadeau from lignefacture lf inner join facture f 
                        on (f.facturid = lf.factureid) where remboursement = '0' and valide in (1, 5, 7, 8) group by factureid");
while ($cat=$DB_site2->fetch_array($cats)) {
    if($cat['montant']!=0){
  $tvamoy=$cat['ttc']/$cat['montant'];
  $chiffretotal += $cat[montant] + $cat[prixportht] - $cat[montantcadeau]/$tvamoy ;
    }
}
$chiffretotal = number_format($chiffretotal, 2, ",", " ") ; 

echo "<br><bR><center>
        <h3>Chiffre d'affaires HT</h3>
        <table class=\"table_caracts\">
                <tr><tD class=\"td_promos_alt2\" align=\"center\"><strong>Au total</strong></td><tD class=\"td_promos_alt2\" align=\"center\"><strong>Cette année</strong></td><td class=\"td_promos_alt2\" align=\"center\"><strong>Ce mois</strong></td><td class=\"td_promos_alt2\" align=\"center\"><strong>Ce jour</strong></td></tr>
                <tr><tD align=\"center\"></td><td align=\"center\"></td><td align=\"center\"></td></tr>
                <tr><tD align=\"center\">$chiffretotal €</td><tD align=\"center\">$chiffreannee €</td><td align=\"center\">$chiffremois €</td><td align=\"center\">$chiffrejour €</td></tr>
            </table>
            </center>" ;
*/

$Y=date("Y");
$m=date("n");

if(isset($_GET['year']))
  $year=$_GET['year'];
else
  $year=date("Y");


$to1=$to2=null;
if($year==$Y){
   $to1=mktime(23,59,59,date("n"),date("j"),$year-1);
   $to2=mktime(23,59,59,date("n"),date("j"),$year-2);
}
$list=ca($year);
// print_r($list);
$list1=ca($year-1,$to1);
$list2=ca($year-2,$to2);

$inscrit=inscrit($year);
$inscrit1=inscrit($year-1,$to1);
$inscrit2=inscrit($year-2,$to2);

$inscrit_site=inscrit($year,null," AND provenance IN('', 'administrateur', 'configurateur', 'devisrapide', 'mobile', 'site')");
$inscrit1_site=inscrit($year-1,$to1," AND provenance IN('', 'administrateur', 'configurateur', 'devisrapide', 'mobile', 'site')");
$inscrit2_site=inscrit($year-2,$to2," AND provenance IN('', 'administrateur', 'configurateur', 'devisrapide', 'mobile', 'site')");

$inscrit_mp=inscrit($year,null," AND provenance IN('mp')");
$inscrit1_mp=inscrit($year-1,$to1," AND provenance IN('mp')");
$inscrit2_mp=inscrit($year-2,$to2," AND provenance IN('mp')");


echo "<p align=\"center\">Année : <select name=\"year\" onchange=\"document.location='chiffre_enregistre.php?year='+this.value\">";

for($y=2010;$y<=(2009+count($objectif_enregistre));$y++) echo "<option value=\"$y\"".($y==$year?" selected":"").">$y</option>";

echo "</select></p>";


print "<table class=\"ca\">
<tr><td colspan=\"20\" class=\"ca_title\">Commandes globales</td></tr>
<tr>
  <td class=\"ca_head\">Mois</td>
  <td class=\"ca_head\">Objectif mensuel HT</td>
  <td class=\"ca_head\">Chiffre d'affaires HT</td>
  <td class=\"ca_head\">Dont non validé</td>
  <td class=\"ca_head\">% objectif du mois</td>
  <td class=\"ca_head\">Av/ret / objectif &euro;HT</td>
  <td class=\"ca_head\">Av/ret / objectif %</td>
  <td class=\"ca_head\">Av/ret / N-1 &euro;HT</td>
  <td class=\"ca_head\">% N-1</td>
  <td class=\"ca_head\">Av/ret / N-2 &euro;HT</td>
  <td class=\"ca_head\">% N-2</td>
  <td class=\"ca_head\">Nbre inscrits</td>
  <td class=\"ca_head\">% N-1</td>
  <td class=\"ca_head\">% N-2</td>
  <td class=\"ca_head\">Nbre cde</td>
  <td class=\"ca_head\">% N-1</td>
  <td class=\"ca_head\">% N-2</td>
  <td class=\"ca_head\">Panier moyen</td>
  <td class=\"ca_head\">% N-1</td>
  <td class=\"ca_head\">% N-2</td>
</tr>";

$anencours=0;
for($mois=1;$mois<=12;$mois++){
    $total=$list['ca'][''][$mois]+$list['ca']['mp'][$mois]+$list['ca']['sav'][$mois]+$list['ca']['avoir'][$mois]+$list['ca']['mobile'][$mois];
	  $totalnv=$list['canv'][''][$mois]+$list['canv']['mp'][$mois]+$list['canv']['sav'][$mois]+$list['canv']['avoir'][$mois]+$list['canv']['mobile'][$mois];
    $total1=$list1['ca'][''][$mois]+$list1['ca']['mp'][$mois]+$list1['ca']['sav'][$mois]+$list1['ca']['avoir'][$mois]+$list1['ca']['mobile'][$mois];
    $total2=$list2['ca'][''][$mois]+$list2['ca']['mp'][$mois]+$list2['ca']['sav'][$mois]+$list2['ca']['avoir'][$mois]+$list2['ca']['mobile'][$mois];
    $nb=$list['nb'][''][$mois]+$list['nb']['mp'][$mois]+$list['nb']['sav'][$mois]+$list['nb']['avoir'][$mois]+$list['nb']['mobile'][$mois];
    $nb1=$list1['nb'][''][$mois]+$list1['nb']['mp'][$mois]+$list1['nb']['sav'][$mois]+$list1['nb']['avoir'][$mois]+$list1['nb']['mobile'][$mois];
    $nb2=$list2['nb'][''][$mois]+$list2['nb']['mp'][$mois]+$list2['nb']['sav'][$mois]+$list2['nb']['avoir'][$mois]+$list2['nb']['mobile'][$mois];
    $total_objectif=$objectif_enregistre[$year][$mois]+$objectif_mp[$year][$mois];
    if(isset($objectif_sav[$year][$mois])) $objectif_mois+=$objectif_sav[$year][$mois];
    
    $encours=$pcencours="";
    if($year==$Y){
	  if($mois==$m){
		  $objencours=date("j")*$total_objectif/date("t");
		  $encours=$total-$objencours;
		  $pcencours=100*($encours/$objencours);
		  $anencours+=$objencours;
	  }
	  if($mois<$m){
		  $encours=$total-$total_objectif;
		  $pcencours=100*($encours/$total_objectif);
		  $anencours+=$total_objectif;
	  }
    }else{
	  $encours=$total-$total_objectif;
	  $pcencours=100*($encours/$total_objectif);
	  $anencours+=$total_objectif;
    }

    $nm1=$nm2="";
    if($total>0){
	  $nm1=100*($total-$total1)/$total1;
	  $nm2=100*($total-$total2)/$total2;
    }

    $insnm1=$insnm2=0;
    if($inscrit[$mois]>0){
	  if($inscrit1[$mois]>0) $insnm1=100*(($inscrit[$mois]-$inscrit1[$mois])/$inscrit1[$mois]);
	  if($inscrit2[$mois]>0) $insnm2=100*(($inscrit[$mois]-$inscrit2[$mois])/$inscrit2[$mois]);
    }

    $pcnb1=$pcnb2="";
    if($list['nb'][''][$mois]>0){
	  $pcnb1=100*($nb-$nb1)/$nb1;
	  $pcnb2=100*($nb-$nb2)/$nb2;
    }
    
    $panier=$panier1=$panier2=$pcpanier1=$pcpanier2=0;
    if($nb>0){
	  $panier=$total/$nb;
	  $panier1=$total1/$nb1;
	  $panier2=$total2/$nb2;
	  $pcpanier1=100*($panier-$panier1)/$panier1;
	  $pcpanier2=100*($panier-$panier2)/$panier2;
    }
    $td_nvalide = "<div id=\"nvalide$mois\" class=\"popup\" style=\"visibility:hidden;background-color: rgb(170, 255, 170);\">".formatlist("canvf",null,$mois,$list)."</div>";
    print "<tr><td>".$mois."/".$year."</td>
      <td class=\"ca_nb\">".number_format($total_objectif,2,',',' ')."&euro;</td>
      ".tdvalue($total,"&euro;",null,$total_objectif)
	    ."<td class=\"ca_nb\" onmouseover=\"showpopup('nvalide".$mois."')\" onmouseout=\"hidepopup('nvalide".$mois."')\">".number_format($totalnv,2,',',' ')."&euro;$td_nvalide</td>"
      .tdvalue(100*$total/$total_objectif,"%",false,100)
      .tdvalue($encours,"&euro;",true)
      .tdvalue($pcencours,"%",true)
      .tdvalue($total-$total1,"&euro;",true)
      .tdvalue($nm1,"%",true)
      .tdvalue($total-$total2,"&euro;",true)
      .tdvalue($nm2,"%",true)
      ."<td class=\"ca_nb\">".intval($inscrit[$mois])."</td>"
      .tdvalue($insnm1,"%",true)
      .tdvalue($insnm2,"%",true)
      ."<td class=\"ca_nb\">".$nb."</td>"
      .tdvalue($pcnb1,"%",true)
      .tdvalue($pcnb2,"%",true)
      ."<td class=\"ca_nb\">".number_format($panier,2,',',' ')."&euro;</td>"
      .tdvalue($pcpanier1,"%",true)
      .tdvalue($pcpanier2,"%",true)."
    </tr>";
}
$objtotal=array_sum($objectif_enregistre[$year])+array_sum($objectif_mp[$year]);
if(isset($objectif_sav[$year])) $objtotal+=array_sum($objectif_sav[$year]);
$total=array_sum($list['ca'][''])+array_sum($list['ca']['mp'])+array_sum($list['ca']['sav'])+array_sum($list['ca']['avoir'])+array_sum($list['ca']['mobile']);
$totalnv=array_sum($list['canv'][''])+array_sum($list['canv']['mp'])+array_sum($list['canv']['sav'])+array_sum($list['canv']['avoir'])+array_sum($list['canv']['mobile']);
$total1=array_sum($list1['ca'][''])+array_sum($list1['ca']['mp'])+array_sum($list1['ca']['sav'])+array_sum($list1['ca']['avoir'])+array_sum($list1['ca']['mobile']);
$total2=array_sum($list2['ca'][''])+array_sum($list2['ca']['mp'])+array_sum($list2['ca']['sav'])+array_sum($list2['ca']['avoir'])+array_sum($list2['ca']['mobile']);
$nb=array_sum($list['nb'][''])+array_sum($list['nb']['mp'])+array_sum($list['nb']['sav'])+array_sum($list['nb']['avoir'])+array_sum($list['nb']['mobile']);
$nb1=array_sum($list1['nb'][''])+array_sum($list1['nb']['mp'])+array_sum($list1['nb']['sav'])+array_sum($list1['nb']['avoir'])+array_sum($list1['nb']['mobile']);
$nb2=array_sum($list2['nb'][''])+array_sum($list2['nb']['mp'])+array_sum($list2['nb']['sav'])+array_sum($list2['nb']['avoir'])+array_sum($list2['nb']['mobile']);
$panier=0;
if($nb>0) $panier=$total/$nb;
$panier1=$total1/$nb1;
$panier2=$total2/$nb2;
$total_inscrit=array_sum($inscrit);
$total_inscrit1=array_sum($inscrit1);
$total_inscrit2=array_sum($inscrit2);

print "<tr style=\"font-weight:bold;\">
    <td>Total</td>
    <td class=\"ca_nb\">".number_format($objtotal,2,',',' ')."&euro;</td>"
    .tdvalue($total,"&euro;",false,$anencours)
	."<td class=\"ca_nb\">".number_format($totalnv,2,',',' ')."&euro;</td>"
    .tdvalue(100*$total/$anencours,"%",false,100)
    .tdvalue($total-$anencours,"&euro;",true)
    .tdvalue(100*($total-$anencours)/$anencours,"%",true)
    .tdvalue($total-$total1,"&euro;",true)
    .tdvalue(100*($total-$total1)/$total1,"%",true)
    .tdvalue($total-$total2,"&euro;",true)
    .tdvalue(100*($total-$total2)/$total2,"%",true)
    ."<td class=\"ca_nb\">".$total_inscrit."</td>"
    .tdvalue(100*($total_inscrit-$total_inscrit1)/$total_inscrit1,"%",true)
    .tdvalue(100*($total_inscrit-$total_inscrit2)/$total_inscrit2,"%",true)
    ."<td class=\"ca_nb\">".$nb."</td>"
    .tdvalue(100*($nb-$nb1)/$nb1,"%",true)
    .tdvalue(100*($nb-$nb2)/$nb2,"%",true)
    ."<td class=\"ca_nb\">".number_format($panier,2,',',' ')."</td>"
    .tdvalue(100*($panier-$panier1)/$panier1,"%",true)
    .tdvalue(100*($panier-$panier2)/$panier2,"%",true)
."</tr>
<tr><td colspan=\"17\">&nbsp;</td></tr>";


print "
<tr><td colspan=\"20\" class=\"ca_title\">Commandes site</td></tr>
<tr>
  <td class=\"ca_head\">Mois</td>
  <td class=\"ca_head\">Objectif mensuel HT</td>
  <td class=\"ca_head\">Chiffre d'affaires HT</td>
  <td class=\"ca_head\">Dont non validé</td>
  <td class=\"ca_head\">% objectif du mois</td>
  <td class=\"ca_head\">Av/ret / objectif &euro;HT</td>
  <td class=\"ca_head\">Av/ret / objectif %</td>
  <td class=\"ca_head\">Av/ret / N-1 &euro;HT</td>
  <td class=\"ca_head\">% N-1</td>
  <td class=\"ca_head\">Av/ret / N-2 &euro;HT</td>
  <td class=\"ca_head\">% N-2</td>
  <td class=\"ca_head\">Nbre inscrits</td>
  <td class=\"ca_head\">% N-1</td>
  <td class=\"ca_head\">% N-2</td>
  <td class=\"ca_head\">Nbre cde</td>
  <td class=\"ca_head\">% N-1</td>
  <td class=\"ca_head\">% N-2</td>
  <td class=\"ca_head\">Panier moyen</td>
  <td class=\"ca_head\">% N-1</td>
  <td class=\"ca_head\">% N-2</td>
</tr>";

$anencours=0;
for($mois=1;$mois<=12;$mois++){
    $total=$list['ca'][''][$mois]+$list['ca']['mobile'][$mois];
	$totalnv=$list['canv'][''][$mois]+$list['canv']['mobile'][$mois];
    $encours=$pcencours="";
    
    if($year==$Y){
      if($mois==$m){
		  $objencours=date("j")*$objectif_enregistre[$year][$mois]/date("t");
		  $encours=$total-$objencours;
		  $pcencours=100*($encours/$objencours);
		  $anencours+=$objencours;
      }elseif($mois<$m){
		  $encours=$total-$objectif_enregistre[$year][$mois];
		  $pcencours=100*($encours/$objectif_enregistre[$year][$mois]);
		  $anencours+=$objectif_enregistre[$year][$mois];
      }
    }else{
	  $encours=$total-$objectif_enregistre[$year][$mois];
	  $pcencours=100*($encours/$objectif_enregistre[$year][$mois]);
	  $anencours+=$objectif_enregistre[$year][$mois];
    }


    $nm1=$nm2="";
    if($total>0){
	  $nm1=100*($total-$list1['ca'][''][$mois]-$list1['ca']['mobile'][$mois])/$list1['ca'][''][$mois]+$list1['ca']['mobile'][$mois];
	  $nm2=100*($total-$list2['ca'][''][$mois]-$list2['ca']['mobile'][$mois])/$list2['ca'][''][$mois]+$list2['ca']['mobile'][$mois];
    }

    $insnm1=$insnm2=0;
    if($inscrit_site[$mois]>0){
	  if($inscrit1_site[$mois]>0) $insnm1=100*(($inscrit_site[$mois]-$inscrit1_site[$mois])/$inscrit1_site[$mois]);
	  if($inscrit2_site[$mois]>0) $insnm2=100*(($inscrit_site[$mois]-$inscrit2_site[$mois])/$inscrit2_site[$mois]);
    }

    $pcnb1=$pcnb2="";
    if($list['nb'][''][$mois]+$list['nb']['mobile'][$mois]>0){
	  $pcnb1=100*(($list['nb'][''][$mois]+$list['nb']['mobile'][$mois])-($list1['nb'][''][$mois]+$list1['nb']['mobile'][$mois]))/($list1['nb'][''][$mois]+$list1['nb']['mobile'][$mois]);
	  $pcnb2=100*(($list['nb'][''][$mois]+$list['nb']['mobile'][$mois])-($list2['nb'][''][$mois]+$list2['nb']['mobile'][$mois]))/($list2['nb'][''][$mois]+$list2['nb']['mobile'][$mois]);
    }
    
    $panier=$panier1=$panier2=$pcpanier1=$pcpanier2=0;
    if($list['nb'][''][$mois]>0){
	  $panier=$total/$list['nb'][''][$mois];
	  $panier1=$list1['ca'][''][$mois]/$list1['nb'][''][$mois];
	  $panier2=$list2['ca'][''][$mois]/$list2['nb'][''][$mois];
	  $pcpanier1=100*($panier-$panier1)/$panier1;
	  $pcpanier2=100*($panier-$panier2)/$panier2;
    }
    $td_nvalidesite = "<div id=\"nvalidesite$mois\" class=\"popup\" style=\"visibility:hidden;background-color: rgb(170, 255, 170);\">".formatlist("canvf",null,$mois,$list)."</div>";
    print "<tr><td>".$mois."/".$year."</td>
      <td class=\"ca_nb\">".number_format($objectif_enregistre[$year][$mois],2,',',' ')."&euro;</td>
      ".tdvalue($total,"&euro;",null,$objectif_enregistre[$year][$mois])
	  ."<td class=\"ca_nb\" onmouseover=\"showpopup('nvalidesite".$mois."')\" onmouseout=\"hidepopup('nvalidesite".$mois."')\">".number_format($totalnv,2,',',' ')."&euro;$td_nvalidesite</td>"
      .tdvalue(100*$total/$objectif_enregistre[$year][$mois],"%",false,100)
      .tdvalue($encours,"&euro;",true)
      .tdvalue($pcencours,"%",true)
      .tdvalue($total-$list1['ca'][''][$mois]-$list1['ca']['mobile'][$mois],"&euro;",true)
      .tdvalue($nm1,"%",true)
      .tdvalue($total-$list2['ca'][''][$mois]-$list2['ca']['mobile'][$mois],"&euro;",true)
      .tdvalue($nm2,"%",true)
      ."<td class=\"ca_nb\">".intval($inscrit_site[$mois])."</td>"
      .tdvalue($insnm1,"%",true)
      .tdvalue($insnm2,"%",true)
      ."<td class=\"ca_nb\">".$list['nb'][''][$mois]."</td>"
      .tdvalue($pcnb1,"%",true)
      .tdvalue($pcnb2,"%",true)
      ."<td class=\"ca_nb\">".number_format($panier,2,',',' ')."&euro;</td>"
      .tdvalue($pcpanier1,"%",true)
      .tdvalue($pcpanier2,"%",true)."
    </tr>";
}
$objtotal=array_sum($objectif_enregistre[$year]);
$total=array_sum($list['ca']['']) + array_sum($list['ca']['mobile']);
$totalnv=array_sum($list['canv']['']) + array_sum($list['canv']['mobile']);
$total1=array_sum($list1['ca']['']) + array_sum($list1['ca']['mobile']);
$total2=array_sum($list2['ca']['']) + array_sum($list2['ca']['mobile']);
$nb=array_sum($list['nb']['']) + array_sum($list['nb']['mobile']);
$nb1=array_sum($list1['nb']['']) + array_sum($list1['nb']['mobile']);;
$nb2=array_sum($list2['nb']['']) + array_sum($list2['nb']['mobile']);
$panier=0;
if($nb>0) $panier=$total/$nb;
$panier1=$total1/$nb1;
$panier2=$total2/$nb2;
$total_inscrit=array_sum($inscrit_site);
$total_inscrit1=array_sum($inscrit1_site);
$total_inscrit2=array_sum($inscrit2_site);

print "<tr style=\"font-weight:bold;\">
    <td>Total</td>
    <td class=\"ca_nb\">".number_format($objtotal,2,',',' ')."&euro;</td>"
    .tdvalue($total,"&euro;",false,$anencours)
	."<td class=\"ca_nb\">".number_format($totalnv,2,',',' ')."&euro;</td>"
    .tdvalue(100*$total/$anencours,"%",false,100)
    .tdvalue($total-$anencours,"&euro;",true)
    .tdvalue(100*($total-$anencours)/$anencours,"%",true)
    .tdvalue($total-$total1,"&euro;",true)
    .tdvalue(100*($total-$total1)/$total1,"%",true)
    .tdvalue($total-$total2,"&euro;",true)
    .tdvalue(100*($total-$total2)/$total2,"%",true)
    ."<td class=\"ca_nb\">".$total_inscrit."</td>"
    .tdvalue(100*($total_inscrit-$total_inscrit1)/$total_inscrit1,"%",true)
    .tdvalue(100*($total_inscrit-$total_inscrit2)/$total_inscrit2,"%",true)
    ."<td class=\"ca_nb\">".$nb."</td>"
    .tdvalue(100*($nb-$nb1)/$nb1,"%",true)
    .tdvalue(100*($nb-$nb2)/$nb2,"%",true)
    ."<td class=\"ca_nb\">".number_format($panier,2,',','')."</td>"
    .tdvalue(100*($panier-$panier1)/$panier1,"%",true)
    .tdvalue(100*($panier-$panier2)/$panier2,"%",true)
."</tr>
<tr><td colspan=\"17\">&nbsp;</td></tr>";


print "
<tr><td colspan=\"20\" class=\"ca_title\">Commandes SAV</td></tr>
<tr>
  <td class=\"ca_head\">Mois</td>
  <td class=\"ca_head\">Objectif mensuel HT</td>
  <td class=\"ca_head\">Chiffre d'affaires HT</td>
  <td class=\"ca_head\">Dont non validé</td>
  <td class=\"ca_head\">% objectif du mois</td>
  <td class=\"ca_head\">Av/ret / objectif &euro;HT</td>
  <td class=\"ca_head\">Av/ret / objectif %</td>
  <td class=\"ca_head\">Av/ret / N-1 &euro;HT</td>
  <td class=\"ca_head\">% N-1</td>
  <td class=\"ca_head\">Av/ret / N-2 &euro;HT</td>
  <td class=\"ca_head\">% N-2</td>
  <td class=\"ca_head\">Nbre inscrits</td>
  <td class=\"ca_head\">% N-1</td>
  <td class=\"ca_head\">% N-2</td>
  <td class=\"ca_head\">Nbre cde</td>
  <td class=\"ca_head\">% N-1</td>
  <td class=\"ca_head\">% N-2</td>
  <td class=\"ca_head\">Panier moyen</td>
  <td class=\"ca_head\">% N-1</td>
  <td class=\"ca_head\">% N-2</td>
</tr>";

for($mois=1;$mois<=12;$mois++){
    $total=$list['ca']['sav'][$mois]+$list['ca']['avoir'][$mois];
	$totalnv=$list['canv']['sav'][$mois]+$list['canv']['avoir'][$mois];
    $total1=$list1['ca']['sav'][$mois]+$list1['ca']['avoir'][$mois];
    $total2=$list2['ca']['sav'][$mois]+$list2['ca']['avoir'][$mois];
    $nb=$list['nb']['sav'][$mois]+$list['nb']['avoir'][$mois];
    $nb1=$list1['nb']['sav'][$mois]+$list1['nb']['avoir'][$mois];
    $nb2=$list2['nb']['sav'][$mois]+$list2['nb']['avoir'][$mois];

    $objectif_mois=$pcmois=$encours=$pcencours="";
    if(isset($objectif_sav[$year][$mois])){
	  $objectif_mois=$objectif_sav[$year][$mois];
	  $pcmois=100*$total/$objectif_sav[$year][$mois];
	  
	  $objencours=$objectif_sav[$year][$mois];
	  if($year<=$Y){
		  $encours=$total-$objectif_sav[$year][$mois];
		  $pcencours=100*$encours/$objectif_sav[$year][$mois];
		  if($Y==$year && $mois==$m){
			$objencours=date("j")*$objectif_sav[$year][$mois]/date("t");
			$encours=$total-$objencours;
			$pcencours=100*$encours/$objencours;
		  }
	  }
	  $anencours+=$objencours;
    }
    
    $nm1=$nm2="";
    if($total>0){
	  if($total1>0) $nm1=100*($total-$total1)/$total1;
	  if($total2>0) $nm2=100*($total-$total2)/$total2;
    }

    $insnm1=$insnm2=0;
    if($inscrit[$mois]>0){
	  if($inscrit1[$mois]>0) $insnm1=100*(($inscrit[$mois]-$inscrit1[$mois])/$inscrit1[$mois]);
	  if($inscrit2[$mois]>0) $insnm2=100*(($inscrit[$mois]-$inscrit2[$mois])/$inscrit2[$mois]);
    }

    $pcnb1=$pcnb2="";
    if($nb>0){
  if($nb1>0) $pcnb1=100*($nb-$nb1)/$nb1;
  if($nb2>0) $pcnb2=100*($nb-$nb2)/$nb2;
    }
    
    $panier=$panier1=$panier2=$pcpanier1=$pcpanier2=0;
    if($nb>0){
  $panier=$total/$nb;
  if($nb1>0){
      $panier1=$total1/$nb1;
      $pcpanier1=$panier1>0?100*($panier-$panier1)/$panier1:0;
  }
  if($nb2>0){
      $panier2=$total2/$nb2;
      $pcpanier2=100*($panier-$panier2)/$panier2;
  }
    }
    $td_nvalidesav = "<div id=\"nvalidesav$mois\" class=\"popup\" style=\"visibility:hidden;background-color: rgb(170, 255, 170);\">".formatlist("canvf","sav",$mois,$list)."</div>";
    print "<tr><td>".$mois."/".$year."</td>
      <td class=\"ca_nb\">".$objectif_mois." &euro;</td>
      ".tdvalue($total,"&euro;")
	  ."<td class=\"ca_nb\" onmouseover=\"showpopup('nvalidesav".$mois."')\" onmouseout=\"hidepopup('nvalidesav".$mois."')\">".number_format($totalnv,2,',',' ')."&euro;$td_nvalidesav</td>"
      .tdvalue($pcmois,"%",false,100)
      .tdvalue($encours,"&euro;",true)
      .tdvalue($pcencours,"%",true)
      .tdvalue($total-$total1,"&euro;",true)
      .tdvalue($nm1,"%",true)
      .tdvalue($total-$total2,"&euro;",true)
      .tdvalue($nm2,"%",true)
      ."<td class=\"ca_nb\"></td>"
      ."<td></td>
      <td></td>
      <td class=\"ca_nb\">".$nb."</td>"
      .tdvalue($pcnb1,"%",true)
      .tdvalue($pcnb2,"%",true)
      ."<td class=\"ca_nb\">".number_format($panier,2,',',' ')."&euro;</td>"
      .tdvalue($pcpanier1,"%",true)
      .tdvalue($pcpanier2,"%",true)."
    </tr>";
}

$total=array_sum($list['ca']['sav'])+array_sum($list['ca']['avoir']);
$totalnv=array_sum($list['canv']['sav'])+array_sum($list['canv']['avoir']);
$total1=array_sum($list1['ca']['sav'])+array_sum($list1['ca']['avoir']);
$total2=array_sum($list2['ca']['sav'])+array_sum($list2['ca']['avoir']);
$nb=array_sum($list['nb']['sav'])+array_sum($list['nb']['avoir']);
$nb1=array_sum($list1['nb']['sav'])+array_sum($list['nb']['avoir']);
$nb2=array_sum($list2['nb']['sav'])+array_sum($list['nb']['avoir']);

$totalobjectif="";
if(isset($objectif_sav[$year])){
    $totalobjectif=array_sum($objectif_sav[$year]);
    $pcmois=100*$total/$totalobjectif;
    $encours=$total-$anencours;
    if($anencours>0) $pcencours=100*$encours/$anencours;
    
}

$panier=0;
if($nb>0) $panier=$total/$nb;
$panier1=$total1/$nb1;
$panier2=$total2/$nb2;
$pcpanier1=$pcpanier2="";
if($panier1>0) $pcpanier1=100*($panier-$panier1)/$panier1;
if($panier2>0) $pcpanier2=100*($panier-$panier2)/$panier2;
$nm1=$nm2="";
if($total1>0) $nm1=100*($total-$total1)/$total1;
if($total2>0) $nm2=100*($total-$total2)/$total2;

print "<tr style=\"font-weight:bold;\">
    <td>Total</td>
    <td class=\"ca_nb\">".$totalobjectif." &euro;</td>"
    .tdvalue($total,"&euro;",false)
	."<td class=\"ca_nb\">".number_format($totalnv,2,',',' ')."&euro;</td>"
    ."<td></td>
    <td></td>
    <td></td>"
    .tdvalue($total-$total1,"&euro;",true)
    .tdvalue($nm1,"%",true)
    .tdvalue($total-$total2,"&euro;",true)
    .tdvalue($nm2,"%",true)
    ."<td></td>
    <td></td>
    <td></td>
    <td class=\"ca_nb\">".$nb."</td>"
    .tdvalue(100*($nb-$nb1)/$nb1,"%",true)
    .tdvalue(100*($nb-$nb2)/$nb2,"%",true)
    ."<td class=\"ca_nb\">".number_format($panier,2,',',' ')."</td>"
    .tdvalue($pcpanier1,"%",true)
    .tdvalue($pcpanier2,"%",true)
."</tr>
<tr><td colspan=\"17\">&nbsp;</td></tr>";

print "
<tr><td colspan=\"20\" class=\"ca_title\">Commandes Market Place</td></tr>
<tr>
  <td class=\"ca_head\">Mois</td>
  <td class=\"ca_head\">Objectif mensuel HT</td>
  <td class=\"ca_head\">Chiffre d'affaires HT</td>
  <td class=\"ca_head\">Dont non validé</td>
  <td class=\"ca_head\">% objectif du mois</td>
  <td class=\"ca_head\">Av/ret / objectif &euro;HT</td>
  <td class=\"ca_head\">Av/ret / objectif %</td>
  <td class=\"ca_head\">Av/ret / N-1 &euro;HT</td>
  <td class=\"ca_head\">% N-1</td>
  <td class=\"ca_head\">Av/ret / N-2 &euro;HT</td>
  <td class=\"ca_head\">% N-2</td>
  <td class=\"ca_head\">Nbre inscrits</td>
  <td class=\"ca_head\">% N-1</td>
  <td class=\"ca_head\">% N-2</td>
  <td class=\"ca_head\">Nbre cde</td>
  <td class=\"ca_head\">% N-1</td>
  <td class=\"ca_head\">% N-2</td>
  <td class=\"ca_head\">Panier moyen</td>
  <td class=\"ca_head\">% N-1</td>
  <td class=\"ca_head\">% N-2</td>
</tr>";

$anencours=0;
for($mois=1;$mois<=12;$mois++){
    $total=$list['ca']['mp'][$mois];
	$totalnv=$list['canv']['mp'][$mois];
    $total1=$list1['ca']['mp'][$mois];
    $total2=$list2['ca']['mp'][$mois];
    $nb=$list['nb']['mp'][$mois];
    $nb1=$list1['nb']['mp'][$mois];
    $nb2=$list2['nb']['mp'][$mois];

    $encours="";
    if($year==$Y){
	  if($mois==$m){
		  $objencours=date("j")*$objectif_mp[$year][$mois]/date("t");
		  $encours=$total-$objencours;
		  $anencours+=$objencours;
		  $pcencours=100*($encours/$objencours);
	  }
	  if($mois<$m){
		  $encours=$total-$objectif_mp[$year][$mois];
		  $anencours+=$objectif_mp[$year][$mois];
		  $pcencours=100*($encours/$objectif_mp[$year][$mois]);
	  }
    }else{
	  $encours=$total-$objectif_mp[$year][$mois];
	  $anencours+=$objectif_mp[$year][$mois];
	  $pcencours=100*($encours/$objectif_mp[$year][$mois]);
    }

    $nm1=$nm2="";
    if($total>0){
	  if($total1>0) $nm1=100*($total-$total1)/$total1;
	  if($total2>0) $nm2=100*($total-$total2)/$total2;
    }

    $insnm1=$insnm2=0;
    if($inscrit_mp[$mois]>0){
	  if($inscrit1_mp[$mois]>0) $insnm1=100*(($inscrit_mp[$mois]-$inscrit1_mp[$mois])/$inscrit1_mp[$mois]);
	  if($inscrit2_mp[$mois]>0) $insnm2=100*(($inscrit_mp[$mois]-$inscrit2_mp[$mois])/$inscrit2_mp[$mois]);
    }

    $pcnb1=$pcnb2="";
    if($nb>0){
	  if($nb1>0) $pcnb1=100*($nb-$nb1)/$nb1;
	  if($nb2>0) $pcnb2=100*($nb-$nb2)/$nb2;
    }
    
    $panier=$panier1=$panier2=$pcpanier1=$pcpanier2=0;
    if($nb>0){
	  $panier=$total/$nb;
	  if($nb1>0){
		  $panier1=$total1/$nb1;
		  $pcpanier1=100*($panier-$panier1)/$panier1;
	  }
	  if($nb2>0){
		  $panier2=$total2/$nb2;
		  $pcpanier2=100*($panier-$panier2)/$panier2;
	  }
    }
    $td_nvalidemp = "<div id=\"nvalidemp$mois\" class=\"popup\" style=\"visibility:hidden;background-color: rgb(170, 255, 170);\">".formatlist("canvf","mp",$mois,$list)."</div>";
    print "<tr>
	   <td>".$mois."/".$year."</td>
       <td class=\"ca_nb\">".number_format($objectif_mp[$year][$mois],2,',',' ')."&euro;</td>
      ".tdvalue($total,"&euro;",false,$objectif_mp[$year][$mois])
	  ."<td class=\"ca_nb\" onmouseover=\"showpopup('nvalidemp".$mois."')\" onmouseout=\"hidepopup('nvalidemp".$mois."')\">".number_format($totalnv,2,',',' ')."&euro;$td_nvalidemp</td>"
      .tdvalue(100*$total/$objectif_mp[$year][$mois],"%",false,100)
      .tdvalue($encours,"&euro;",true)
      .tdvalue($pcencours,"%",true)
      .tdvalue($total-$total1,"&euro;",true)
      .tdvalue($nm1,"%",true)
      .tdvalue($total-$total2,"&euro;",true)
      .tdvalue($nm2,"%",true)
	  ."<td class=\"ca_nb\">".intval($inscrit_mp[$mois])."</td>"
      .tdvalue($insnm1,"%",true)
      .tdvalue($insnm2,"%",true)
      ."<td class=\"ca_nb\">".$nb."</td>"
      .tdvalue($pcnb1,"%",true)
      .tdvalue($pcnb2,"%",true)
      ."<td class=\"ca_nb\">".number_format($panier,2,',',' ')."&euro;</td>"
      .tdvalue($pcpanier1,"%",true)
      .tdvalue($pcpanier2,"%",true)."
    </tr>";
}
$objtotal=array_sum($objectif_mp[$year]);
$total=array_sum($list['ca']['mp']);
$totalnv=array_sum($list['canv']['mp']);
$total1=array_sum($list1['ca']['mp']);
$total2=array_sum($list2['ca']['mp']);
$nb=array_sum($list['nb']['mp']);
$nb1=array_sum($list1['nb']['mp']);
$nb2=array_sum($list2['nb']['mp']);
$panier=0;
if($nb>0) $panier=$total/$nb;
$panier1=$panier2=0;
if($nb1>0) $panier1=$total1/$nb1;
if($nb2>0) $panier2=$total2/$nb2;
$nm1=$nm2="";
if($total1>0) $nm1=100*($total-$total1)/$total1;
if($total2>0) $nm2=100*($total-$total2)/$total2;
$pcpanier1=$pcpanier2="";
if($panier1>0) $pcpanier1=100*($panier-$panier1)/$panier1;
if($panier2>0) $pcpanier2=100*($panier-$panier2)/$panier2;
$pcnb1=$pcnb2="";
if($nb1>0) $pcnb1=100*($nb-$nb1)/$nb1;
if($nb2>0) $pcnb2=100*($nb-$nb2)/$nb2;
$total_inscrit=array_sum($inscrit_mp);
$total_inscrit1=array_sum($inscrit1_mp);
$total_inscrit2=array_sum($inscrit2_mp);

print "<tr style=\"font-weight:bold;\">
    <td>Total</td>
    <td class=\"ca_nb\">".number_format($objtotal,2,',',' ')."&euro;</td>"
    .tdvalue($total,"&euro;",false,$anencours)
	."<td class=\"ca_nb\">".number_format($totalnv,2,',',' ')."&euro;</td>"
    .tdvalue(100*$total/$anencours,"%",false,100)
    .tdvalue($total-$anencours,"&euro;",true)
    .tdvalue(100*($total-$anencours)/$anencours,"%",true)
    .tdvalue($total-$total1,"&euro;",true)
    .tdvalue($nm1,"%",true)
    .tdvalue($total-$total2,"&euro;",true)
    .tdvalue($nm2,"%",true)
    ."<td class=\"ca_nb\">".$total_inscrit."</td>"
    .tdvalue(100*($total_inscrit-$total_inscrit1)/$total_inscrit1,"%",true)
    .tdvalue(100*($total_inscrit-$total_inscrit2)/$total_inscrit2,"%",true)
    ."<td class=\"ca_nb\">".$nb."</td>"
    .tdvalue($pcnb1,"%",true)
    .tdvalue($pcnb2,"%",true)
    ."<td class=\"ca_nb\">".number_format($panier,2,',',' ')."</td>"
    .tdvalue($pcpanier1,"%",true)
    .tdvalue($pcpanier2,"%",true)
."</tr>
<tr><td colspan=\"17\">&nbsp;</td></tr>";


print "
<tr><td colspan=\"20\" class=\"ca_title\">Commandes code partenaire</td></tr>
<tr>
  <td class=\"ca_head\">Mois</td>
  <td class=\"ca_head\">Objectif mensuel HT</td>
  <td class=\"ca_head\">Chiffre d'affaires HT</td>
  <td class=\"ca_head\">Dont non validé</td>
  <td class=\"ca_head\">% objectif du mois</td>
  <td class=\"ca_head\">Av/ret / objectif &euro;HT</td>
  <td class=\"ca_head\">Av/ret / objectif %</td>
  <td class=\"ca_head\">Av/ret / N-1 &euro;HT</td>
  <td class=\"ca_head\">% N-1</td>
  <td class=\"ca_head\">Av/ret / N-2 &euro;HT</td>
  <td class=\"ca_head\">% N-2</td>
  <td class=\"ca_head\">Nbre inscrits</td>
  <td class=\"ca_head\">% N-1</td>
  <td class=\"ca_head\">% N-2</td>
  <td class=\"ca_head\">Nbre cde</td>
  <td class=\"ca_head\">% N-1</td>
  <td class=\"ca_head\">% N-2</td>
  <td class=\"ca_head\">Panier moyen</td>
  <td class=\"ca_head\">% N-1</td>
  <td class=\"ca_head\">% N-2</td>
</tr>";

$partlist=ca($year,null," AND f.cadeauid = cv.code ",1);
$partlist1=ca($year-1,$to1," AND f.cadeauid = cv.code ",1);
$partlist2=ca($year-2,$to2," AND f.cadeauid = cv.code ",1);
$anencours=0;
for($mois=1;$mois<=12;$mois++){
    $total=floatval($partlist['ca'][''][$mois]);
	$totalnv=floatval($partlist['canv'][''][$mois]);
    $total1=floatval($partlist1['ca'][''][$mois]);
    $total2=floatval($partlist2['ca'][''][$mois]);
    $nb=intval($partlist['nb'][''][$mois]);
    $nb1=intval($partlist1['nb'][''][$mois]);
    $nb2=intval($partlist2['nb'][''][$mois]);

    $encours="";
    if($year==$Y){
  if($mois==$m){
      $objencours=date("j")*$objectif_mp[$year][$mois]/date("t");
      $encours=$total-$objencours;
      $anencours+=$objencours;
  }
  if($mois<$m){
      $encours=$total-$objectif_mp[$year][$mois];
      $anencours+=$objectif_mp[$year][$mois];
  }
    }else{
  $encours=$total-$objectif_mp[$year][$mois];
  $anencours+=$objectif_mp[$year][$mois];
    }

    $nm1=$nm2="";
    if($total>0){
  if($total1>0) $nm1=100*($total-$total1)/$total1;
  if($total2>0) $nm2=100*($total-$total2)/$total2;
    }

    $insnm1=$insnm2=0;
    if($inscrit[$mois]>0){
  if($inscrit1[$mois]>0) $insnm1=100*(($inscrit[$mois]-$inscrit1[$mois])/$inscrit1[$mois]);
  if($inscrit2[$mois]>0) $insnm2=100*(($inscrit[$mois]-$inscrit2[$mois])/$inscrit2[$mois]);
    }

    $pcnb1=$pcnb2="";
    if($nb>0){
  if($nb1>0) $pcnb1=100*($nb-$nb1)/$nb1;
  if($nb2>0) $pcnb2=100*($nb-$nb2)/$nb2;
    }
    
    $panier=$panier1=$panier2=$pcpanier1=$pcpanier2=0;
    if($nb>0){
  $panier=$total/$nb;
  if($nb1>0){
      $panier1=$total1/$nb1;
      $pcpanier1=100*($panier-$panier1)/$panier1;
  }
  if($nb2>0){
      $panier2=$total2/$nb2;
      $pcpanier2=100*($panier-$panier2)/$panier2;
  }
    }
    print "<tr><td>".$mois."/".$year."</td>
      <td class=\"ca_nb\"></td>
      ".tdvalue($total,"&euro;",false)
	  ."<td class=\"ca_nb\">".number_format($totalnv,2,',',' ')."&euro;</td>"
      ."<td></td>
      <td></td>
      <td></td>"
      .tdvalue($total-$total1,"&euro;",true)
      .tdvalue($nm1,"%",true)
      .tdvalue($total-$total2,"&euro;",true)
      .tdvalue($nm2,"%",true)
      ."<td class=\"ca_nb\"></td>"
      ."<td></td>
      <td></td>
      <td class=\"ca_nb\">".$nb."</td>"
      .tdvalue($pcnb1,"%",true)
      .tdvalue($pcnb2,"%",true)
      ."<td class=\"ca_nb\">".number_format($panier,2,',',' ')."&euro;</td>"
      .tdvalue($pcpanier1,"%",true)
      .tdvalue($pcpanier2,"%",true)."
    </tr>";
}

$total=array_sum($partlist['ca']['']);
$totalnv=array_sum($partlist['canv']['']);
$total1=array_sum($partlist1['ca']['']);
$total2=array_sum($partlist2['ca']['']);
$nb=array_sum($partlist['nb']['']);
$nb1=array_sum($partlist1['nb']['']);
$nb2=array_sum($partlist2['nb']['']);
$panier=$nb>0?$total/$nb:0;
$panier1=$panier2=0;
if($nb1>0) $panier1=$total1/$nb1;
if($nb2>0) $panier2=$total2/$nb2;
$nm1=$nm2="";
if($total1>0) $nm1=100*($total-$total1)/$total1;
if($total2>0) $nm2=100*($total-$total2)/$total2;
$pcpanier1=$pcpanier2="";
if($panier1>0) $pcpanier1=100*($panier-$panier1)/$panier1;
if($panier2>0) $pcpanier2=100*($panier-$panier2)/$panier2;
$pcnb1=$pcnb2="";
if($nb1>0) $pcnb1=100*($nb-$nb1)/$nb1;
if($nb2>0) $pcnb2=100*($nb-$nb2)/$nb2;
print "<tr style=\"font-weight:bold;\">
    <td>Total</td>
    <td class=\"ca_nb\"></td>"
    .tdvalue($total,"&euro;",false,$anencours)
	."<td class=\"ca_nb\">".number_format($totalnv,2,',',' ')."&euro;</td>"
    ."<td></td>
    <td></td>
    <td></td>"
    .tdvalue($total-$total1,"&euro;",true)
    .tdvalue($nm1,"%",true)
    .tdvalue($total-$total2,"&euro;",true)
    .tdvalue($nm2,"%",true)
    ."<td></td>
    <td></td>
    <td></td>
    <td class=\"ca_nb\">".$nb."</td>"
    .tdvalue($pcnb1,"%",true)
    .tdvalue($pcnb2,"%",true)
    ."<td class=\"ca_nb\">".number_format($panier,2,',',' ')."</td>"
    .tdvalue($pcpanier1,"%",true)
    .tdvalue($pcpanier2,"%",true)
."</tr>
</table>";

echo "A NOTER : les inscrits comptabilisés dans 'commande site' comprennent les inscrits du SAV et des codes partenaires<br>";

include "./footer.php";


function ca($annee, $to=null, $and="", $caviste=0){
    
    global $DB_site2;

    if(is_null($to)) $to = mktime(23,59,59,12,31,$annee);

    $ca = $nb = $canv = array(
        ""      => array(),
        "mp"    => array(),
        "sav"   => array(),
        "avoir" => array(),
        "mobile" => array()
    );

    //Remise HT = TOT HT hors remise x (Remise TTC / TOT TTC hors remise)
    $sql = "SELECT f.facturid, f.heurefacture, f.valide, f.provenance, SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) as 'prixHT', f.prixport / (1 + f.tvaport / 100) as 'portHT', CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) + (f.prixport / (1 + f.tvaport / 100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END as remiseHT";
    //$sql = "SELECT f.facturid, f.heurefacture, f.provenance, SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) as 'prixHT', f.prixport / (1 + f.tvaport / 100) as 'portHT', (SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) + (f.prixport / (1 + f.tvaport / 100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) as remiseHT";
    $sql .= " FROM ";
	if($caviste==1){
		$sql.=" code_caviste AS cv, ";
	}
	$sql .= " facture AS f";
    $sql .= " INNER JOIN lignefacture lf ON (lf.factureid = f.facturid)";
    $sql .= " WHERE f.heurefacture BETWEEN ".mktime(0,0,0,1,1,$annee)." AND ".$to;
    // $sql .= " AND f.valide IN (1,5,7,8)";
	$sql .= " AND ( (valide IN (1,5,7,8) ) OR (valide = 0 AND moyenid in (2,4,5,14,17,18,99)) ) ";
    $sql .= " ".$and;
    $sql .= " GROUP BY f.facturid";
	if($caviste==1){
		// echo $sql."<br><br><br>";
	}
	// echo $sql."<br><br><br>";
    $req = $DB_site2->query($sql);
    while($facture = $DB_site2->fetch_array($req)){
        $f = new facture($DB_site2,$facture);
		  if($facture['provenance']=="mobile"){
			 $facture['provenance']="";
			 // echo "la facture ".$facture[facturid]."est sur mobile<br>";
		  }
        $month = date("n",$facture['heurefacture']);
        $ca[$facture['provenance']][$month] += $facture['prixHT'] + $facture['portHT'] - $facture['remiseHT'];
		if($facture['valide']==0){
      $canvf[$facture['provenance']][$month][]=$f;
			$canv[$facture['provenance']][$month] += $facture['prixHT'] + $facture['portHT'] - $facture['remiseHT'];
		}
        $nb[$facture['provenance']][$month]++;
    }

    return array(
        "ca" => $ca,
		    "canv" => $canv,
        "nb" => $nb,
        "canvf"=>$canvf
    );
}

function inscrit($year,$to=null, $and=""){
    global $DB_site2;
  
    if(is_null($to)) $to=mktime(23,59,59,12,31,$year);

    $ret=array();
    $sql="SELECT userid,UNIX_TIMESTAMP(dateinscription) FROM utilisateur WHERE UNIX_TIMESTAMP(dateinscription) BETWEEN ".mktime(0,0,0,1,1,$year)." AND ".$to." ".$and;
    $req=$DB_site2->query($sql);
    while($user=$DB_site2->fetch_array($req)){
		$month=date("n",$user[1]);
		$ret[$month]++;
    }

    return $ret;
}

function tdvalue($value,$sym="",$sign=null,$climit=0){
  if($value=="") return "<td></td>";
  return "<td class=\"ca_nb\" style=\"color:".($value>$climit?"green":"red").";\">".($sign&&$value>0?"+":"").number_format($value,2,',',' ').$sym."</td>";
}

function formatlist($type,$prov=null,$mois=null,$list=null){
  $html="<table>";
  if($prov===null){
     if(!is_array($list[$type][""][$mois])) $list[$type][""][$mois]=array();
     if(!is_array($list[$type]["mp"][$mois])) $list[$type]["mp"][$mois]=array();
     if(!is_array($list[$type]["sav"][$mois])) $list[$type]["sav"][$mois]=array();
     $list=array_merge($list[$type][""][$mois],$list[$type]["mp"][$mois],$list[$type]["sav"][$mois]);
  }else
     $list=$list[$type][$prov][$mois];

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


?>