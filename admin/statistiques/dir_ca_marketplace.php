<?php

require "../admin_global.php";
require "functions_dircamarketplace.php";
include "../header.php";
include "menu_2.php";
include "../../class/facture.class.php";
ini_set("display_errors",0);








echo "
<style>
.stat_rouge {color:red; text-align: right;}
.collapse{
  cursor: pointer;
  display: block;
  text-align: center;
  color: white;
  font-size: 14px;
  background: #af824c;}
.collapse + input{
  display: none;}
.collapse + input + div{
  display:none;}
.collapse + input:checked + div{
  display:block;}
.collapse:hover {color:#af504c;}
.stat_vert {color:green; text-align: right;}
.ca { color:black; border-collapse:collapse; border:solid thin black; width:100%;}
.ca td { border:solid thin black; }
.ca_nb { text-align:right; white-space:nowrap; }
.ca_title { background-color:#4CAF50; text-align:center; color:white;}
.ca_second_title { background-color:#af504c; text-align:center; color:white;}
.ca_third_title { background-color:#af824c; text-align:center; color:white;}
.ca_fourth_title { background-color:#abaf4c; text-align:center; color:white;}
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

<h1>Détails MarketPlaces Facturés</h1>";?>




<?php
include(BACK_PATH.'/statistiques/statistiques_adm.php');



//GESTION DE L'ANNEE
if(!isset($year)) $year=date("Y");
echo "<p style=\"margin-top:40px; margin-bottom:15px;\"align=\"center\">Année : <select name=\"year\" onchange=\"document.location='dir_ca_marketplace.php?year='+this.value\">";
for($y=2010;$y<=(2009+count($objectif_facture));$y++) echo "<option value=\"$y\"".($y==$year?" selected":"").">$y</option>";




echo "</select></p>";

$listfacture=array();

$Y=date("Y");
$m=date("n");
$timeday=date(t);
$yn1 = $year - 1;
$yn2 = $year - 2;
$ca_encours_mp=$ca_encours=$ca_attente=$ca_encours_sav=array();
$total_valide=$total_encours=$total_attente=$total_valide_mp=0;
$ca_validem1=$ca_validem2=$ca_validem1_mp=$ca_validem2_mp=$ca_validem1_sav=$ca_validem2_sav=array();

$ca_encours_mp=ca_valide(null,$year,"mp");
$ca_encours=ca_valide(null,$year);
$ca_attente=ca_attente(null,$year);
$ca_encours_sav=ca_valide(null,$year,"sav");







for($i=1;$i<=12;$i++)
{   
    $ca_valide[$i]=ca($i,$year,"5,8","");
    $ca_valide_mp[$i]=ca($i,$year,"5,8","mp");
    $ca_valide_sav[$i]=ca($i,$year,"5,8","sav")+ca($i,$year,"5,8","avoir");

    $objectif_mois= $total[$a];
    $a++;
    if(isset($objectif_sav[$year][$i])) $objectif_mois+=$objectif_sav[$year][$i];
    
    $pcmois=100*($ca_valide_mp[$i]+$ca_valide[$i]+$ca_valide_sav[$i])/($objectif_mois);    
    $prevision=$ca_valide[$i]+$ca_attente[$i]+$ca_encours[$i]+$ca_valide_mp[$i]+$ca_encours_mp[$i]+$ca_valide_sav[$i]+$ca_encours_sav[$i];
    $pcprev=100*$prevision/($objectif_mois);
    $previsions="<td class=\"ca_nb\" onmouseover=\"showpopup('valide$i')\" onmouseout=\"hidepopup('valide$i')\">".number_format($ca_encours[$i]+$ca_encours_mp[$i]+$ca_encours_sav[$i],2,',',' ')." &euro;<div id=\"valide$i\" class=\"popup\">".formatlist("valide",null,$i)."</div></td><td class=\"ca_nb\" onmouseover=\"showpopup('attente$i')\" onmouseout=\"hidepopup('attente$i')\">".number_format($ca_attente[$i],2,',',' ')." &euro;<div id=\"attente$i\" class=\"popup\">".formatlist("attente",null,$i)."</div></td><td class=\"ca_nb\">".number_format($prevision,2,',',' ')." &euro;</td>".tdvalue($pcprev,"%",false,100);
    
            if($year==$Y)
            {

                  $limit=null;
                  if($i==$m) $limit=date("d");
                  if($i<=$m)
                     {
                        $ca_validem1[$i]=ca($i,($year-1),"5,8","",$limit);
                        $ca_validem2[$i]=ca($i,($year-2),"5,8","",$limit);
                        $ca_validem1_mp[$i]=ca($i,($year-1),"5,8","mp",$limit);
                        $ca_validem2_mp[$i]=ca($i,($year-2),"5,8","mp",$limit);
                        $ca_validem1_sav[$i]=ca($i,($year-1),"5,8","sav",$limit);
                        $ca_validem2_sav[$i]=ca($i,($year-2),"5,8","sav",$limit);
                     }
                 else
                 $ca_validem1[$i]=$ca_validem2[$i]=$ca_validem1_mp[$i]=$ca_validem2_mp[$i]=$ca_validem1_sav[$i]=$ca_validem2_sav[$i]=0;
            }
            else
            {
              $ca_validem1[$i]=ca($i,($year-1),"5,8","");
              $ca_validem2[$i]=ca($i,($year-2),"5,8","");
              $ca_validem1_mp[$i]=ca($i,($year-1),"5,8","mp");
              $ca_validem2_mp[$i]=ca($i,($year-2),"5,8","mp");
              $ca_validem1_sav[$i]=ca($i,($year-1),"5,8","sav");
              $ca_validem2_sav[$i]=ca($i,($year-2),"5,8","sav");
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
  if($year==$Y)
  {
          if($i<$m)
          {
            $anencours+=$objectif_mois;
            $encours=$ca_valide_total-$objectif_mois;
            $pcencours=100*$encours/$objectif_mois;
          }
          if($i==$m)
          {
            $objencours=date("j")*$objectif_mois/date("t");
            $anencours+=$objencours;
            $encours=$ca_valide_total-$objencours;
            $pcencours=100*$encours/$objencours;
          }
  }    
}



$total_attente=array_sum($ca_attente);
$total_encours=array_sum($ca_encours)+array_sum($ca_encours_mp)+array_sum($ca_encours_sav);
$totalobjectif=array_sum($objectif_facture[$year])+array_sum($objectif_mp[$year]);


if(isset($objectif_sav[$year])) $totalobjectif+=array_sum($objectif_sav[$year]);
$pcmois=100*$total_valide/$totalobjectif;
$pcprev=100*($total_attente+$total_encours+$total_valide)/$totalobjectif;
$encours=$pcencours="";

if($year==$Y)
{
  $encours=$total_valide-$anencours;
  $pcencours=100*$encours/$anencours;
}


$total_validem1=array_sum($ca_validem1_mp)+array_sum($ca_validem1)+array_sum($ca_validem1_sav);
$nm1=100*(array_sum($ca_valide_mp)+array_sum($ca_valide)+array_sum($ca_valide_sav)-$total_validem1)/$total_validem1;
$total_validem2=array_sum($ca_validem2_mp)+array_sum($ca_validem2)+array_sum($ca_validem2_sav);
$nm2=100*(array_sum($ca_valide_mp)+array_sum($ca_valide)+array_sum($ca_valide_sav)-$total_validem2)/$total_validem2;


$anencours=0;
$total_valide=$total_encours=$total_attente=0;




//FONCTION OBJ_MONTANT

$chiffre_mp = obj_montant($year, 4);
$chiffre_mp_total = $chiffre_mp[0] + $chiffre_mp[1] + $chiffre_mp[2] + $chiffre_mp[3] + $chiffre_mp[4] + $chiffre_mp[5] + $chiffre_mp[6] + $chiffre_mp[7] + $chiffre_mp[8] + $chiffre_mp[9] + $chiffre_mp[10] + $chiffre_mp[11];




echo "<p></p>
<table class=\"ca\">
<tr><td colspan=\"14\" class=\"ca_second_title\">Chiffres marketplaces</td></tr>
<tr class=\"ca_head\">
  <td></td>
  <td>Objectif mensuel &euro;HT</td>
  <td>Chiffre d'affaires &euro;HT</td>
  <td>Av/ret objectif &euro;HT</td>
  <td>Av/ret objectif %</td>
  <td>Av/ret N-1 &euro;HT</td>
  <td>% N-1</td>
  <td>Av/ret N-2 &euro;HT</td>
  <td>% N-2</td>
  <td>Cde en cours validées</td>
  <td>Cde en attente de paiement &euro;HT</td>
  <td>Chiffre d'affaires prévisionnel</td>
  <td>% Objectif avec cde en cours</td>
</tr>";


$anencours=0;
$total_valide_mp=0;
$a = 0;
for($i=1;$i<=12;$i++)
{

  $total_valide_mp+=$ca_valide_mp[$i];
    
  $previsions="<td></td><td></td><td class=\"ca_nb\">".number_format($total_valide_mp,2,',','')." &euro;</td>".tdvalue($pcmois,"%",false,100);
  $prevision=$ca_valide_mp[$i]+$ca_encours_mp[$i];
  $pcprev=100*$prevision/$objectif_mp[$year][$i];
  $previsions="<td class=\"ca_nb\" onmouseover=\"showpopup('validemp$i')\" onmouseout=\"hidepopup('validemp$i')\">".number_format($ca_encours_mp[$i],2,',','')." &euro;<div id=\"validemp$i\" class=\"popup\">".formatlist("valide","mp",$i)."</div></td><td></td><td class=\"ca_nb\">".number_format($prevision,2,',','')." &euro;</td>".tdvalue($pcprev,"%",false,100);

  $pcmois=100*$ca_valide_mp[$i]/$objectif_mp[$year][$i];

    
  $nm1="";
  $nm2="";
  if($ca_valide_mp[$i]>0 && $ca_validem1_mp[$i]>0)
    {
      $nm1=100*($ca_valide_mp[$i]-$ca_validem1_mp[$i])/$ca_validem1_mp[$i];
    }
  if($ca_valide_mp[$i]>0 && $ca_validem2_mp[$i]>0)
    {
      $nm2=100*($ca_valide_mp[$i]-$ca_validem2_mp[$i])/$ca_validem2_mp[$i];
    }

  $encours=$pcencours="";
    if($year==$Y)
    {
      if($i<$m)
      {
          $anencours+=$objectif_mp[$year][$i];
          $encours=$ca_valide_mp[$i]-$objectif_mp[$year][$i];
          $pcencours=100*$encours/$objectif_mp[$year][$i];
      }
      if($i==$m)
      {
          $objencours=date("j")*$objectif_mp[$year][$i]/date("t");
          $anencours+=$objencours;
          $encours=$ca_valide_mp[$i]-$objencours;
          $pcencours=100*$encours/$objencours;
      }
    }
    elseif($year < $Y)
    {      
      $anencours+=$objectif_mp[$year][$i];
      $encours=$ca_valide_mp[$i]-$objectif_mp[$year][$i];
      $pcencours=100*$encours/$objectif_mp[$year][$i];
    }
    


    echo "<tr>
      <td>".$i."-".$year."</td>
      <td class=\"ca_nb\">".number_format($chiffre_mp[$a],0,',',' ')." &euro;</td>
      <td class=\"ca_nb\">".number_format($ca_valide_mp[$i],2,',',' ')." &euro;</td>".
      tdvalue($encours,"&euro;",true).
      tdvalue($pcencours,"%",true).
      tdvalue($ca_valide_mp[$i]-$ca_validem1_mp[$i],"&euro;",true).
      tdvalue($nm1,"%",true).
      tdvalue($ca_valide_mp[$i]-$ca_validem2_mp[$i],"&euro;",true).
      tdvalue($nm2,"%",true).
      $previsions.
   "</tr>";
   $a++;
}




$totalobjectif_mp=array_sum($objectif_mp[$year]);
$pcmois=100*$total_valide_mp/$totalobjectif_mp;
$encours=$pcencours="";


if($year==$Y)
{
  $encours=$total_valide_mp-$anencours;
  $pcencours=100*$encours/$anencours;
}
elseif($year < $Y)
{
  $encours=$total_valide_mp-$anencours;
  $pcencours=100*$encours/$anencours;  
}


$total_validem1=array_sum($ca_validem1_mp);
$nm1="";
if($total_validem1>0) $nm1=100*(array_sum($ca_valide_mp)-$total_validem1)/$total_validem1;
$total_validem2=array_sum($ca_validem2_mp);
$nm2="";
if($total_validem2>0) $nm2=100*(array_sum($ca_valide_mp)-$total_validem2)/$total_validem2;




echo "<tr style=\"font-weight:bold;\">
  <td>Total année $year</td>
  <td class=\"ca_nb\">".number_format($chiffre_mp_total,2,',',' ')." &euro;</td>
  <td class=\"ca_nb\">".number_format($total_valide_mp,2,',',' ')." &euro;</td>".
  tdvalue($encours,"&euro;",true).
  tdvalue($pcencours,"%",true).
  tdvalue(array_sum($ca_valide_mp)-$total_validem1,"&euro;",true).
  tdvalue($nm1,"%",true).
  tdvalue(array_sum($ca_valide_mp)-$total_validem2,"&euro;",true).
  tdvalue($nm2,"%",true).
  "<td class=\"ca_nb\">".number_format(array_sum($ca_encours_mp),2,',','')." &euro;</td>
  <td></td>
  <td class=\"ca_nb\">".number_format(array_sum($ca_encours_mp)+$total_valide_mp,2,',',' ')." &euro;</td>
  <td class=\"ca_nb\">".number_format(100*($total_encours_mp+$total_valide_mp)/array_sum($objectif_mp[$year]),2,',',' ')."%</td>
</tr>";

echo "</table>";


// FONCTION TTE_CA_MARKETPLACES


//FONCTION CA_MARKETPLACES_DETAIL



$compteur = 0;
$mois = 1;

while ($compteur < 12)
{
  $Ca_annee_amazone_mp[$compteur] = ca_marketplaces_detail($mois, $year, "5,8", "mp", "", "", "01");
  $Ca_annee_priceminister_mp[$compteur] = ca_marketplaces_detail($mois, $year, "5,8", "mp", "", "", "04");
  $Ca_annee_pixmania_mp[$compteur] = ca_marketplaces_detail($mois, $year, "5,8", "mp", "", "", "05");
  $Ca_annee_laredoute_mp[$compteur] = ca_marketplaces_detail($mois, $year, "5,8", "mp", "", "", "14");
  $Ca_annee_rueducom_mp[$compteur] = ca_marketplaces_detail($mois, $year, "5,8", "mp", "", "", "07");
  $Ca_annee_fnac_mp[$compteur] = ca_marketplaces_detail($mois, $year, "5,8", "mp", "", "", "12");
  $Ca_annee_cdiscount_mp[$compteur] = ca_marketplaces_detail($mois, $year, "5,8", "mp", "", "", "13");


  $Ca_n1_amazone_mp[$compteur] = ca_marketplaces_detail($mois, $yn1, "5,8", "mp", "", "", "01");
  $Ca_n1_priceminister_mp[$compteur] = ca_marketplaces_detail($mois, $yn1, "5,8", "mp", "", "", "04");
  $Ca_n1_pixmania_mp[$compteur] = ca_marketplaces_detail($mois, $yn1, "5,8", "mp", "", "", "05");
  $Ca_n1_laredoute_mp[$compteur] = ca_marketplaces_detail($mois, $yn1, "5,8", "mp", "", "", "14");
  $Ca_n1_rueducom_mp[$compteur] = ca_marketplaces_detail($mois, $yn1, "5,8", "mp", "", "", "07");
  $Ca_n1_fnac_mp[$compteur] = ca_marketplaces_detail($mois, $yn1, "5,8", "mp", "", "", "12");
  $Ca_n1_cdiscount_mp[$compteur] = ca_marketplaces_detail($mois, $yn1, "5,8", "mp", "", "", "13");


  $Ca_n2_amazone_mp[$compteur] = ca_marketplaces_detail($mois, $yn2, "5,8", "mp", "", "", "01");
  $Ca_n2_priceminister_mp[$compteur] = ca_marketplaces_detail($mois, $yn2, "5,8", "mp", "", "", "04");
  $Ca_n2_pixmania_mp[$compteur] = ca_marketplaces_detail($mois, $yn2, "5,8", "mp", "", "", "05");
  $Ca_n2_laredoute_mp[$compteur] = ca_marketplaces_detail($mois, $yn2, "5,8", "mp", "", "", "14");
  $Ca_n2_rueducom_mp[$compteur] = ca_marketplaces_detail($mois, $yn2, "5,8", "mp", "", "", "07");
  $Ca_n2_fnac_mp[$compteur] = ca_marketplaces_detail($mois, $yn2, "5,8", "mp", "", "", "12");
  $Ca_n2_cdiscount_mp[$compteur] = ca_marketplaces_detail($mois, $yn2, "5,8", "mp", "", "", "13");


  $Ca_total_annee_01 += $Ca_annee_amazone_mp[$compteur];
  $Ca_total_annee_04 += $Ca_annee_priceminister_mp[$compteur];
  $Ca_total_annee_05 += $Ca_annee_pixmania_mp[$compteur];
  $Ca_total_annee_06 += $Ca_annee_laredoute_mp[$compteur];
  $Ca_total_annee_07 += $Ca_annee_rueducom_mp[$compteur];
  $Ca_total_annee_12 += $Ca_annee_fnac_mp[$compteur];
  $Ca_total_annee_13 += $Ca_annee_cdiscount_mp[$compteur];

  $Ca_total_annee_01 += $Ca_n1_amazone_mp[$compteur];
  $Ca_total_annee_04 += $Ca_n1_priceminister_mp[$compteur];
  $Ca_total_annee_05 += $Ca_n1_pixmania_mp[$compteur];
  $Ca_total_annee_06 += $Ca_n1_laredoute_mp[$compteur];
  $Ca_total_annee_07 += $Ca_n1_rueducom_mp[$compteur];
  $Ca_total_annee_12 += $Ca_n1_fnac_mp[$compteur];
  $Ca_total_annee_13 += $Ca_n1_cdiscount_mp[$compteur];

  $Ca_total_annee_01 += $Ca_n2_amazone_mp[$compteur];
  $Ca_total_annee_04 += $Ca_n2_priceminister_mp[$compteur];
  $Ca_total_annee_05 += $Ca_n2_pixmania_mp[$compteur];
  $Ca_total_annee_06 += $Ca_n2_laredoute_mp[$compteur];
  $Ca_total_annee_07 += $Ca_n2_rueducom_mp[$compteur];
  $Ca_total_annee_12 += $Ca_n2_fnac_mp[$compteur];
  $Ca_total_annee_13 += $Ca_n2_cdiscount_mp[$compteur];

  $compteur++;
  $mois++;
}










//TEMPS REEL

if($year < $Y)
{
  $av_ret_n1ht_01 = av_ret_obj_f($Ca_n1_amazone_mp, $Ca_annee_amazone_mp);
  $av_ret_n1prcent_01 = obj_mois_f_n($Ca_n1_amazone_mp, $Ca_annee_amazone_mp);
  $av_ret_n2ht_01 = av_ret_obj_f($Ca_n2_amazone_mp, $Ca_annee_amazone_mp);
  $av_ret_n2prcent_01 = obj_mois_f_n($Ca_n2_amazone_mp, $Ca_annee_amazone_mp);

  $av_ret_n1ht_04 = av_ret_obj_f($Ca_n1_priceminister_mp, $Ca_annee_priceminister_mp);
  $av_ret_n1prcent_04 = obj_mois_f_n($Ca_n1_priceminister_mp, $Ca_annee_priceminister_mp);
  $av_ret_n2ht_04 = av_ret_obj_f($Ca_n2_priceminister_mp, $Ca_annee_priceminister_mp);
  $av_ret_n2prcent_04 = obj_mois_f_n($Ca_n2_priceminister_mp, $Ca_annee_priceminister_mp);

  $av_ret_n1ht_05 = av_ret_obj_f($Ca_n1_pixmania_mp, $Ca_annee_pixmania_mp);
  $av_ret_n1prcent_05 = obj_mois_f_n($Ca_n1_pixmania_mp, $Ca_annee_pixmania_mp);
  $av_ret_n2ht_05 = av_ret_obj_f($Ca_n2_pixmania_mp, $Ca_annee_pixmania_mp);
  $av_ret_n2prcent_05 = obj_mois_f_n($Ca_n2_pixmania_mp, $Ca_annee_pixmania_mp);

  $av_ret_n1ht_06 = av_ret_obj_f($Ca_n1_laredoute_mp, $Ca_annee_laredoute_mp);
  $av_ret_n1prcent_06 = obj_mois_f_n($Ca_n1_laredoute_mp, $Ca_annee_laredoute_mp);
  $av_ret_n2ht_06 = av_ret_obj_f($Ca_n2_laredoute_mp, $Ca_annee_laredoute_mp);
  $av_ret_n2prcent_06 = obj_mois_f_n($Ca_n2_laredoute_mp, $Ca_annee_laredoute_mp);

  $av_ret_n1ht_07 = av_ret_obj_f($Ca_n1_rueducom_mp, $Ca_annee_rueducom_mp);
  $av_ret_n1prcent_07 = obj_mois_f_n($Ca_n1_rueducom_mp, $Ca_annee_rueducom_mp);
  $av_ret_n2ht_07 = av_ret_obj_f($Ca_n2_rueducom_mp, $Ca_annee_rueducom_mp);
  $av_ret_n2prcent_07 = obj_mois_f_n($Ca_n2_rueducom_mp, $Ca_annee_rueducom_mp);

  $av_ret_n1ht_12 = av_ret_obj_f($Ca_n1_fnac_mp, $Ca_annee_fnac_mp);
  $av_ret_n1prcent_12 = obj_mois_f_n($Ca_n1_fnac_mp, $Ca_annee_fnac_mp);
  $av_ret_n2ht_12 = av_ret_obj_f($Ca_n2_fnac_mp, $Ca_annee_fnac_mp);
  $av_ret_n2prcent_12 = obj_mois_f_n($Ca_n2_fnac_mp, $Ca_annee_fnac_mp);

  $av_ret_n1ht_13 = av_ret_obj_f($Ca_n1_cdiscount_mp, $Ca_annee_cdiscount_mp);
  $av_ret_n1prcent_13 = obj_mois_f_n($Ca_n1_cdiscount_mp, $Ca_annee_cdiscount_mp);
  $av_ret_n2ht_13 = av_ret_obj_f($Ca_n2_cdiscount_mp, $Ca_annee_cdiscount_mp);
  $av_ret_n2prcent_13 = obj_mois_f_n($Ca_n2_cdiscount_mp, $Ca_annee_cdiscount_mp);
}
elseif($year == $Y)
{
  $av_ret_n1ht_01 = av_ret_obj_fmois($Ca_n1_amazone_mp, $Ca_annee_amazone_mp, $m, $timeday);
  $av_ret_n1prcent_01 = obj_mois_fmois_n($Ca_n1_amazone_mp, $Ca_annee_amazone_mp, $m, $timeday);
  $av_ret_n2ht_01 = av_ret_obj_fmois($Ca_n2_amazone_mp, $Ca_annee_amazone_mp, $m, $timeday);
  $av_ret_n2prcent_01 = obj_mois_fmois_n($Ca_n2_amazone_mp, $Ca_annee_amazone_mp, $m, $timeday);

  $av_ret_n1ht_04 = av_ret_obj_fmois($Ca_n1_priceminister_mp, $Ca_annee_priceminister_mp, $m, $timeday);
  $av_ret_n1prcent_04 = obj_mois_fmois_n($Ca_n1_priceminister_mp, $Ca_annee_priceminister_mp, $m, $timeday);
  $av_ret_n2ht_04 = av_ret_obj_fmois($Ca_n2_priceminister_mp, $Ca_annee_priceminister_mp, $m, $timeday);
  $av_ret_n2prcent_04 = obj_mois_fmois_n($Ca_n2_priceminister_mp, $Ca_annee_priceminister_mp, $m, $timeday);

  $av_ret_n1ht_05 = av_ret_obj_fmois($Ca_n1_pixmania_mp, $Ca_annee_pixmania_mp, $m, $timeday);
  $av_ret_n1prcent_05 = obj_mois_fmois_n($Ca_n1_pixmania_mp, $Ca_annee_pixmania_mp, $m, $timeday);
  $av_ret_n2ht_05 = av_ret_obj_fmois($Ca_n2_pixmania_mp, $Ca_annee_pixmania_mp, $m, $timeday);
  $av_ret_n2prcent_05 = obj_mois_fmois_n($Ca_n2_pixmania_mp, $Ca_annee_pixmania_mp, $m, $timeday);

  $av_ret_n1ht_06 = av_ret_obj_fmois($Ca_n1_laredoute_mp, $Ca_annee_laredoute_mp, $m, $timeday);
  $av_ret_n1prcent_06 = obj_mois_fmois_n($Ca_n1_laredoute_mp, $Ca_annee_laredoute_mp, $m, $timeday);
  $av_ret_n2ht_06 = av_ret_obj_fmois($Ca_n2_laredoute_mp, $Ca_annee_laredoute_mp, $m, $timeday);
  $av_ret_n2prcent_06 = obj_mois_fmois_n($Ca_n2_laredoute_mp, $Ca_annee_laredoute_mp, $m, $timeday);

  $av_ret_n1ht_07 = av_ret_obj_fmois($Ca_n1_rueducom_mp, $Ca_annee_rueducom_mp, $m, $timeday);
  $av_ret_n1prcent_07 = obj_mois_fmois_n($Ca_n1_rueducom_mp, $Ca_annee_rueducom_mp, $m, $timeday);
  $av_ret_n2ht_07 = av_ret_obj_fmois($Ca_n2_rueducom_mp, $Ca_annee_rueducom_mp, $m, $timeday);
  $av_ret_n2prcent_07 = obj_mois_fmois_n($Ca_n2_rueducom_mp, $Ca_annee_rueducom_mp, $m, $timeday);

  $av_ret_n1ht_12 = av_ret_obj_fmois($Ca_n1_fnac_mp, $Ca_annee_fnac_mp, $m, $timeday);
  $av_ret_n1prcent_12 = obj_mois_fmois_n($Ca_n1_fnac_mp, $Ca_annee_fnac_mp, $m, $timeday);
  $av_ret_n2ht_12 = av_ret_obj_fmois($Ca_n2_fnac_mp, $Ca_annee_fnac_mp, $m, $timeday);
  $av_ret_n2prcent_12 = obj_mois_fmois_n($Ca_n2_fnac_mp, $Ca_annee_fnac_mp, $m, $timeday);

  $av_ret_n1ht_13 = av_ret_obj_fmois($Ca_n1_cdiscount_mp, $Ca_annee_cdiscount_mp, $m, $timeday);
  $av_ret_n1prcent_13 = obj_mois_fmois_n($Ca_n1_cdiscount_mp, $Ca_annee_cdiscount_mp, $m, $timeday);
  $av_ret_n2ht_13 = av_ret_obj_fmois($Ca_n2_cdiscount_mp, $Ca_annee_cdiscount_mp, $m, $timeday);
  $av_ret_n2prcent_13 = obj_mois_fmois_n($Ca_n2_cdiscount_mp, $Ca_annee_cdiscount_mp, $m, $timeday);
}











$total_av_ret_n1ht_01 = $av_ret_n1ht_01[0] + $av_ret_n1ht_01[1] + $av_ret_n1ht_01[2] + $av_ret_n1ht_01[3] + $av_ret_n1ht_01[4] + $av_ret_n1ht_01[5] + $av_ret_n1ht_01[6] + $av_ret_n1ht_01[7] + $av_ret_n1ht_01[8] + $av_ret_n1ht_01[9] + $av_ret_n1ht_01[10] + $av_ret_n1ht_01[11];
$total_av_ret_n2ht_01 = $av_ret_n2ht_01[0] + $av_ret_n2ht_01[1] + $av_ret_n2ht_01[2] + $av_ret_n2ht_01[3] + $av_ret_n2ht_01[4] + $av_ret_n2ht_01[5] + $av_ret_n2ht_01[6] + $av_ret_n2ht_01[7] + $av_ret_n2ht_01[8] + $av_ret_n2ht_01[9] + $av_ret_n2ht_01[10] + $av_ret_n2ht_01[11];

$total_av_ret_n1ht_04 = $av_ret_n1ht_04[0] + $av_ret_n1ht_04[1] + $av_ret_n1ht_04[2] + $av_ret_n1ht_04[3] + $av_ret_n1ht_04[4] + $av_ret_n1ht_04[5] + $av_ret_n1ht_04[6] + $av_ret_n1ht_04[7] + $av_ret_n1ht_04[8] + $av_ret_n1ht_04[9] + $av_ret_n1ht_04[10] + $av_ret_n1ht_04[11];
$total_av_ret_n2ht_04 = $av_ret_n2ht_04[0] + $av_ret_n2ht_04[1] + $av_ret_n2ht_04[2] + $av_ret_n2ht_04[3] + $av_ret_n2ht_04[4] + $av_ret_n2ht_04[5] + $av_ret_n2ht_04[6] + $av_ret_n2ht_04[7] + $av_ret_n2ht_04[8] + $av_ret_n2ht_04[9] + $av_ret_n2ht_04[10] + $av_ret_n2ht_04[11];

$total_av_ret_n1ht_05 = $av_ret_n1ht_05[0] + $av_ret_n1ht_05[1] + $av_ret_n1ht_05[2] + $av_ret_n1ht_05[3] + $av_ret_n1ht_05[4] + $av_ret_n1ht_05[5] + $av_ret_n1ht_05[6] + $av_ret_n1ht_05[7] + $av_ret_n1ht_05[8] + $av_ret_n1ht_05[9] + $av_ret_n1ht_05[10] + $av_ret_n1ht_05[11];
$total_av_ret_n2ht_05 = $av_ret_n2ht_05[0] + $av_ret_n2ht_05[1] + $av_ret_n2ht_05[2] + $av_ret_n2ht_05[3] + $av_ret_n2ht_05[4] + $av_ret_n2ht_05[5] + $av_ret_n2ht_05[6] + $av_ret_n2ht_05[7] + $av_ret_n2ht_05[8] + $av_ret_n2ht_05[9] + $av_ret_n2ht_05[10] + $av_ret_n2ht_05[11];

$total_av_ret_n1ht_06 = $av_ret_n1ht_06[0] + $av_ret_n1ht_06[1] + $av_ret_n1ht_06[2] + $av_ret_n1ht_06[3] + $av_ret_n1ht_06[4] + $av_ret_n1ht_06[5] + $av_ret_n1ht_06[6] + $av_ret_n1ht_06[7] + $av_ret_n1ht_06[8] + $av_ret_n1ht_06[9] + $av_ret_n1ht_06[10] + $av_ret_n1ht_06[11];
$total_av_ret_n2ht_06 = $av_ret_n2ht_06[0] + $av_ret_n2ht_06[1] + $av_ret_n2ht_06[2] + $av_ret_n2ht_06[3] + $av_ret_n2ht_06[4] + $av_ret_n2ht_06[5] + $av_ret_n2ht_06[6] + $av_ret_n2ht_06[7] + $av_ret_n2ht_06[8] + $av_ret_n2ht_06[9] + $av_ret_n2ht_06[10] + $av_ret_n2ht_06[11];

$total_av_ret_n1ht_07 = $av_ret_n1ht_07[0] + $av_ret_n1ht_07[1] + $av_ret_n1ht_07[2] + $av_ret_n1ht_07[3] + $av_ret_n1ht_07[4] + $av_ret_n1ht_07[5] + $av_ret_n1ht_07[6] + $av_ret_n1ht_07[7] + $av_ret_n1ht_07[8] + $av_ret_n1ht_07[9] + $av_ret_n1ht_07[10] + $av_ret_n1ht_07[11];
$total_av_ret_n2ht_07 = $av_ret_n2ht_07[0] + $av_ret_n2ht_07[1] + $av_ret_n2ht_07[2] + $av_ret_n2ht_07[3] + $av_ret_n2ht_07[4] + $av_ret_n2ht_07[5] + $av_ret_n2ht_07[6] + $av_ret_n2ht_07[7] + $av_ret_n2ht_07[8] + $av_ret_n2ht_07[9] + $av_ret_n2ht_07[10] + $av_ret_n2ht_07[11];

$total_av_ret_n1ht_12 = $av_ret_n1ht_12[0] + $av_ret_n1ht_12[1] + $av_ret_n1ht_12[2] + $av_ret_n1ht_12[3] + $av_ret_n1ht_12[4] + $av_ret_n1ht_12[5] + $av_ret_n1ht_12[6] + $av_ret_n1ht_12[7] + $av_ret_n1ht_12[8] + $av_ret_n1ht_12[9] + $av_ret_n1ht_12[10] + $av_ret_n1ht_12[11];
$total_av_ret_n2ht_12 = $av_ret_n2ht_12[0] + $av_ret_n2ht_12[1] + $av_ret_n2ht_12[2] + $av_ret_n2ht_12[3] + $av_ret_n2ht_12[4] + $av_ret_n2ht_12[5] + $av_ret_n2ht_12[6] + $av_ret_n2ht_12[7] + $av_ret_n2ht_12[8] + $av_ret_n2ht_12[9] + $av_ret_n2ht_12[10] + $av_ret_n2ht_12[11];

$total_av_ret_n1ht_13 = $av_ret_n1ht_13[0] + $av_ret_n1ht_13[1] + $av_ret_n1ht_13[2] + $av_ret_n1ht_13[3] + $av_ret_n1ht_13[4] + $av_ret_n1ht_13[5] + $av_ret_n1ht_13[6] + $av_ret_n1ht_13[7] + $av_ret_n1ht_13[8] + $av_ret_n1ht_13[9] + $av_ret_n1ht_13[10] + $av_ret_n1ht_13[11];
$total_av_ret_n2ht_13 = $av_ret_n2ht_13[0] + $av_ret_n2ht_13[1] + $av_ret_n2ht_13[2] + $av_ret_n2ht_13[3] + $av_ret_n2ht_13[4] + $av_ret_n2ht_13[5] + $av_ret_n2ht_13[6] + $av_ret_n2ht_13[7] + $av_ret_n2ht_13[8] + $av_ret_n2ht_13[9] + $av_ret_n2ht_13[10] + $av_ret_n2ht_13[11];











if ($year < $Y)
{
    $total_av_ret_n1prcent_01 = ($Ca_total_annee_01 * 100) / $Ca_total_n1_01 - 100;
    $total_av_ret_n2prcent_01 = ($Ca_total_annee_01 * 100) / $Ca_total_n2_01 - 100;

    $total_av_ret_n1prcent_04 = ($Ca_total_annee_04 * 100) / $Ca_total_n1_04 - 100;
    $total_av_ret_n2prcent_04 = ($Ca_total_annee_04 * 100) / $Ca_total_n2_04 - 100;

    $total_av_ret_n1prcent_05 = ($Ca_total_annee_05 * 100) / $Ca_total_n1_05 - 100;
    $total_av_ret_n2prcent_05 = ($Ca_total_annee_05 * 100) / $Ca_total_n2_05 - 100;

    $total_av_ret_n1prcent_06 = ($Ca_total_annee_06 * 100) / $Ca_total_n1_06 - 100;
    $total_av_ret_n2prcent_06 = ($Ca_total_annee_06 * 100) / $Ca_total_n2_06 - 100;

    $total_av_ret_n1prcent_07 = ($Ca_total_annee_07 * 100) / $Ca_total_n1_07 - 100;
    $total_av_ret_n2prcent_07 = ($Ca_total_annee_07 * 100) / $Ca_total_n2_07 - 100;

    $total_av_ret_n1prcent_12 = ($Ca_total_annee_12 * 100) / $Ca_total_n1_12 - 100;
    $total_av_ret_n2prcent_12 = ($Ca_total_annee_12 * 100) / $Ca_total_n2_12 - 100;

    $total_av_ret_n1prcent_13 = ($Ca_total_annee_13 * 100) / $Ca_total_n1_13 - 100;
    $total_av_ret_n2prcent_13 = ($Ca_total_annee_13 * 100) / $Ca_total_n2_13 - 100;
}

elseif ($year == $Y)
{
    $total_av_ret_n1prcent_01 = total_av_ret_objprcent($Ca_n1_amazone_mp, $Ca_total_annee_01, $m, $timeday);
    $total_av_ret_n2prcent_01 = total_av_ret_objprcent($Ca_n2_amazone_mp, $Ca_total_annee_01, $m, $timeday);

    $total_av_ret_n1prcent_04 = total_av_ret_objprcent($Ca_n1_priceminister_mp, $Ca_total_annee_04, $m, $timeday);
    $total_av_ret_n2prcent_04 = total_av_ret_objprcent($Ca_n2_priceminister_mp, $Ca_total_annee_04, $m, $timeday);

    $total_av_ret_n1prcent_05 = total_av_ret_objprcent($Ca_n1_pixmania_mp, $Ca_total_annee_05, $m, $timeday);
    $total_av_ret_n2prcent_05 = total_av_ret_objprcent($Ca_n2_pixmania_mp, $Ca_total_annee_05, $m, $timeday);

    $total_av_ret_n1prcent_06 = total_av_ret_objprcent($Ca_n1_laredoute_mp, $Ca_total_annee_06, $m, $timeday);
    $total_av_ret_n2prcent_06 = total_av_ret_objprcent($Ca_n2_laredoute_mp, $Ca_total_annee_06, $m, $timeday);

    $total_av_ret_n1prcent_07 = total_av_ret_objprcent($Ca_n1_rueducom_mp, $Ca_total_annee_07, $m, $timeday);
    $total_av_ret_n2prcent_07 = total_av_ret_objprcent($Ca_n2_rueducom_mp, $Ca_total_annee_07, $m, $timeday);

    $total_av_ret_n1prcent_12 = total_av_ret_objprcent($Ca_n1_fnac_mp, $Ca_total_annee_12, $m, $timeday);
    $total_av_ret_n2prcent_12 = total_av_ret_objprcent($Ca_n2_fnac_mp, $Ca_total_annee_12, $m, $timeday);

    $total_av_ret_n1prcent_13 = total_av_ret_objprcent($Ca_n1_cdiscount_mp, $Ca_total_annee_13, $m, $timeday);
    $total_av_ret_n2prcent_13 = total_av_ret_objprcent($Ca_n2_cdiscount_mp, $Ca_total_annee_13, $m, $timeday);
}













//FONCTION C_COMMANDE_CUR_MWCOM


$Com_cur_all_n1 = c_commande_cur_mwcom($year - 1, "all");
$Com_cur_all_n2 = c_commande_cur_mwcom($year - 2, "all");
$Com_total_cur_alln1 = add_month($Com_cur_all_n1);
$Com_total_cur_alln2 = add_month($Com_cur_all_n2);

$Com_cur_01_n1 = c_commande_cur_mwcom($year - 1, "01");
$Com_cur_01_n2 = c_commande_cur_mwcom($year - 2, "01");
$Com_total_cur_01n1 = add_month($Com_cur_01_n1);
$Com_total_cur_01n2 = add_month($Com_cur_01_n2);

$Com_cur_04_n1 = c_commande_cur_mwcom($year - 1, "04");
$Com_cur_04_n2 = c_commande_cur_mwcom($year - 2, "04");
$Com_total_cur_04n1 = add_month($Com_cur_04_n1);
$Com_total_cur_04n2 = add_month($Com_cur_04_n2);

$Com_cur_05_n1 = c_commande_cur_mwcom($year - 1, "05");
$Com_cur_05_n2 = c_commande_cur_mwcom($year - 2, "05");
$Com_total_cur_05n1 = add_month($Com_cur_05_n1);
$Com_total_cur_05n2 = add_month($Com_cur_05_n2);

$Com_cur_06_n1 = c_commande_cur_mwcom($year - 1, "14");
$Com_cur_06_n2 = c_commande_cur_mwcom($year - 2, "14");
$Com_total_cur_06n1 = add_month($Com_cur_06_n1);
$Com_total_cur_06n2 = add_month($Com_cur_06_n2);

$Com_cur_07_n1 = c_commande_cur_mwcom($year - 1, "07");
$Com_cur_07_n2 = c_commande_cur_mwcom($year - 2, "07");
$Com_total_cur_07n1 = add_month($Com_cur_07_n1);
$Com_total_cur_07n2 = add_month($Com_cur_07_n2);

$Com_cur_12_n1 = c_commande_cur_mwcom($year - 1, "12");
$Com_cur_12_n2 = c_commande_cur_mwcom($year - 2, "12");
$Com_total_cur_12n1 = add_month($Com_cur_12_n1);
$Com_total_cur_12n2 = add_month($Com_cur_12_n2);

$Com_cur_13_n1 = c_commande_cur_mwcom($year - 1, "13");
$Com_cur_13_n2 = c_commande_cur_mwcom($year - 2, "13");
$Com_total_cur_13n1 = add_month($Com_cur_13_n1);
$Com_total_cur_13n2 = add_month($Com_cur_13_n2);


















//FONCTION TTE_MARKETPLACES_COMMANDES

$Commandes_annee_all_mp = tte_marketplaces_commandes($year, "5,8");
$Commandes_total_annee_all_mp = $Commandes_annee_all_mp[0] + $Commandes_annee_all_mp[1] + $Commandes_annee_all_mp[2] + $Commandes_annee_all_mp[3] + $Commandes_annee_all_mp[4] + $Commandes_annee_all_mp[5] + $Commandes_annee_all_mp[6] + $Commandes_annee_all_mp[7] + $Commandes_annee_all_mp[8] + $Commandes_annee_all_mp[9] + $Commandes_annee_all_mp[10] + $Commandes_annee_all_mp[11];
$Commandes_n1_all_mp = tte_marketplaces_commandes($yn1, "5,8");
$Commandes_total_n1_all_mp = $Commandes_n1_all_mp[0] + $Commandes_n1_all_mp[1] + $Commandes_n1_all_mp[2] + $Commandes_n1_all_mp[3] + $Commandes_n1_all_mp[4] + $Commandes_n1_all_mp[5] + $Commandes_n1_all_mp[6] + $Commandes_n1_all_mp[7] + $Commandes_n1_all_mp[8] + $Commandes_n1_all_mp[9] + $Commandes_n1_all_mp[10] + $Commandes_n1_all_mp[11];
$Commandes_n2_all_mp = tte_marketplaces_commandes($yn2, "5,8");
$Commandes_total_n2_all_mp = $Commandes_n2_all_mp[0] + $Commandes_n2_all_mp[1] + $Commandes_n2_all_mp[2] + $Commandes_n2_all_mp[3] + $Commandes_n2_all_mp[4] + $Commandes_n2_all_mp[5] + $Commandes_n2_all_mp[6] + $Commandes_n2_all_mp[7] + $Commandes_n2_all_mp[8] + $Commandes_n2_all_mp[9] + $Commandes_n2_all_mp[10] + $Commandes_n2_all_mp[11];





//FONCTION MARKETPLACES_DETAIL_COMMANDES

$Commandes_annee_01 = marketplaces_detail_commandes($year, "5,8", "01");
$Commandes_total_annee_01 = $Commandes_annee_01[0] + $Commandes_annee_01[1] + $Commandes_annee_01[2] + $Commandes_annee_01[3] + $Commandes_annee_01[4] + $Commandes_annee_01[5] + $Commandes_annee_01[6] + $Commandes_annee_01[7] + $Commandes_annee_01[8] + $Commandes_annee_01[9] + $Commandes_annee_01[10] + $Commandes_annee_01[11];

$Commandes_annee_04 = marketplaces_detail_commandes($year, "5,8", "04");
$Commandes_total_annee_04 = $Commandes_annee_04[0] + $Commandes_annee_04[1] + $Commandes_annee_04[2] + $Commandes_annee_04[3] + $Commandes_annee_04[4] + $Commandes_annee_04[5] + $Commandes_annee_04[6] + $Commandes_annee_04[7] + $Commandes_annee_04[8] + $Commandes_annee_04[9] + $Commandes_annee_04[10] + $Commandes_annee_04[11];

$Commandes_annee_05 = marketplaces_detail_commandes($year, "5,8", "05");
$Commandes_total_annee_05 = $Commandes_annee_05[0] + $Commandes_annee_05[1] + $Commandes_annee_05[2] + $Commandes_annee_05[3] + $Commandes_annee_05[4] + $Commandes_annee_05[5] + $Commandes_annee_05[6] + $Commandes_annee_05[7] + $Commandes_annee_05[8] + $Commandes_annee_05[9] + $Commandes_annee_05[10] + $Commandes_annee_05[11];

$Commandes_annee_06 = marketplaces_detail_commandes($year, "5,8", "14");
$Commandes_total_annee_06 = $Commandes_annee_06[0] + $Commandes_annee_06[1] + $Commandes_annee_06[2] + $Commandes_annee_06[3] + $Commandes_annee_06[4] + $Commandes_annee_06[5] + $Commandes_annee_06[6] + $Commandes_annee_06[7] + $Commandes_annee_06[8] + $Commandes_annee_06[9] + $Commandes_annee_06[10] + $Commandes_annee_06[11];

$Commandes_annee_07 = marketplaces_detail_commandes($year, "5,8", "07");
$Commandes_total_annee_07 = $Commandes_annee_07[0] + $Commandes_annee_07[1] + $Commandes_annee_07[2] + $Commandes_annee_07[3] + $Commandes_annee_07[4] + $Commandes_annee_07[5] + $Commandes_annee_07[6] + $Commandes_annee_07[7] + $Commandes_annee_07[8] + $Commandes_annee_07[9] + $Commandes_annee_07[10] + $Commandes_annee_07[11];

$Commandes_annee_12 = marketplaces_detail_commandes($year, "5,8", "12");
$Commandes_total_annee_12 = $Commandes_annee_12[0] + $Commandes_annee_12[1] + $Commandes_annee_12[2] + $Commandes_annee_12[3] + $Commandes_annee_12[4] + $Commandes_annee_12[5] + $Commandes_annee_12[6] + $Commandes_annee_12[7] + $Commandes_annee_12[8] + $Commandes_annee_12[9] + $Commandes_annee_12[10] + $Commandes_annee_12[11];

$Commandes_annee_13 = marketplaces_detail_commandes($year, "5,8", "13");
$Commandes_total_annee_13 = $Commandes_annee_13[0] + $Commandes_annee_13[1] + $Commandes_annee_13[2] + $Commandes_annee_13[3] + $Commandes_annee_13[4] + $Commandes_annee_13[5] + $Commandes_annee_13[6] + $Commandes_annee_13[7] + $Commandes_annee_13[8] + $Commandes_annee_13[9] + $Commandes_annee_13[10] + $Commandes_annee_13[11];






$Commandes_n1_01 = marketplaces_detail_commandes($yn1, "5,8", "01");
$Commandes_total_n1_01 = $Commandes_n1_01[0] + $Commandes_n1_01[1] + $Commandes_n1_01[2] + $Commandes_n1_01[3] + $Commandes_n1_01[4] + $Commandes_n1_01[5] + $Commandes_n1_01[6] + $Commandes_n1_01[7] + $Commandes_n1_01[8] + $Commandes_n1_01[9] + $Commandes_n1_01[10] + $Commandes_n1_01[11];

$Commandes_n1_04 = marketplaces_detail_commandes($yn1, "5,8", "04");
$Commandes_total_n1_04 = $Commandes_n1_04[0] + $Commandes_n1_04[1] + $Commandes_n1_04[2] + $Commandes_n1_04[3] + $Commandes_n1_04[4] + $Commandes_n1_04[5] + $Commandes_n1_04[6] + $Commandes_n1_04[7] + $Commandes_n1_04[8] + $Commandes_n1_04[9] + $Commandes_n1_04[10] + $Commandes_n1_04[11];

$Commandes_n1_05 = marketplaces_detail_commandes($yn1, "5,8", "05");
$Commandes_total_n1_05 = $Commandes_n1_05[0] + $Commandes_n1_05[1] + $Commandes_n1_05[2] + $Commandes_n1_05[3] + $Commandes_n1_05[4] + $Commandes_n1_05[5] + $Commandes_n1_05[6] + $Commandes_n1_05[7] + $Commandes_n1_05[8] + $Commandes_n1_05[9] + $Commandes_n1_05[10] + $Commandes_n1_05[11];

$Commandes_n1_06 = marketplaces_detail_commandes($yn1, "5,8", "14");
$Commandes_total_n1_06 = $Commandes_n1_06[0] + $Commandes_n1_06[1] + $Commandes_n1_06[2] + $Commandes_n1_06[3] + $Commandes_n1_06[4] + $Commandes_n1_06[5] + $Commandes_n1_06[6] + $Commandes_n1_06[7] + $Commandes_n1_06[8] + $Commandes_n1_06[9] + $Commandes_n1_06[10] + $Commandes_n1_06[11];

$Commandes_n1_07 = marketplaces_detail_commandes($yn1, "5,8", "07");
$Commandes_total_n1_07 = $Commandes_n1_07[0] + $Commandes_n1_07[1] + $Commandes_n1_07[2] + $Commandes_n1_07[3] + $Commandes_n1_07[4] + $Commandes_n1_07[5] + $Commandes_n1_07[6] + $Commandes_n1_07[7] + $Commandes_n1_07[8] + $Commandes_n1_07[9] + $Commandes_n1_07[10] + $Commandes_n1_07[11];

$Commandes_n1_12 = marketplaces_detail_commandes($yn1, "5,8", "12");
$Commandes_total_n1_12 = $Commandes_n1_12[0] + $Commandes_n1_12[1] + $Commandes_n1_12[2] + $Commandes_n1_12[3] + $Commandes_n1_12[4] + $Commandes_n1_12[5] + $Commandes_n1_12[6] + $Commandes_n1_12[7] + $Commandes_n1_12[8] + $Commandes_n1_12[9] + $Commandes_n1_12[10] + $Commandes_n1_12[11];

$Commandes_n1_13 = marketplaces_detail_commandes($yn1, "5,8", "13");
$Commandes_total_n1_13 = $Commandes_n1_13[0] + $Commandes_n1_13[1] + $Commandes_n1_13[2] + $Commandes_n1_13[3] + $Commandes_n1_13[4] + $Commandes_n1_13[5] + $Commandes_n1_13[6] + $Commandes_n1_13[7] + $Commandes_n1_13[8] + $Commandes_n1_13[9] + $Commandes_n1_13[10] + $Commandes_n1_13[11];






$Commandes_n2_01 = marketplaces_detail_commandes($yn2, "5,8", "01");
$Commandes_total_n2_01 = $Commandes_n2_01[0] + $Commandes_n2_01[1] + $Commandes_n2_01[2] + $Commandes_n2_01[3] + $Commandes_n2_01[4] + $Commandes_n2_01[5] + $Commandes_n2_01[6] + $Commandes_n2_01[7] + $Commandes_n2_01[8] + $Commandes_n2_01[9] + $Commandes_n2_01[10] + $Commandes_n2_01[11];

$Commandes_n2_04 = marketplaces_detail_commandes($yn2, "5,8", "04");
$Commandes_total_n2_04 = $Commandes_n2_04[0] + $Commandes_n2_04[1] + $Commandes_n2_04[2] + $Commandes_n2_04[3] + $Commandes_n2_04[4] + $Commandes_n2_04[5] + $Commandes_n2_04[6] + $Commandes_n2_04[7] + $Commandes_n2_04[8] + $Commandes_n2_01[9] + $Commandes_n2_04[10] + $Commandes_n2_04[11];

$Commandes_n2_05 = marketplaces_detail_commandes($yn2, "5,8", "05");
$Commandes_total_n2_05 = $Commandes_n2_05[0] + $Commandes_n2_05[1] + $Commandes_n2_05[2] + $Commandes_n2_05[3] + $Commandes_n2_05[4] + $Commandes_n2_05[5] + $Commandes_n2_05[6] + $Commandes_n2_05[7] + $Commandes_n2_05[8] + $Commandes_n2_05[9] + $Commandes_n2_05[10] + $Commandes_n2_05[11];

$Commandes_n2_06 = marketplaces_detail_commandes($yn2, "5,8", "14");
$Commandes_total_n2_06 = $Commandes_n2_06[0] + $Commandes_n2_06[1] + $Commandes_n2_06[2] + $Commandes_n2_06[3] + $Commandes_n2_06[4] + $Commandes_n2_06[5] + $Commandes_n2_06[6] + $Commandes_n2_06[7] + $Commandes_n2_06[8] + $Commandes_n2_06[9] + $Commandes_n2_06[10] + $Commandes_n2_06[11];

$Commandes_n2_07 = marketplaces_detail_commandes($yn2, "5,8", "07");
$Commandes_total_n2_07 = $Commandes_n2_07[0] + $Commandes_n2_07[1] + $Commandes_n2_07[2] + $Commandes_n2_07[3] + $Commandes_n2_07[4] + $Commandes_n2_07[5] + $Commandes_n2_07[6] + $Commandes_n2_07[7] + $Commandes_n2_07[8] + $Commandes_n2_07[9] + $Commandes_n2_07[10] + $Commandes_n2_07[11];

$Commandes_n2_12 = marketplaces_detail_commandes($yn2, "5,8", "12");
$Commandes_total_n2_12 = $Commandes_n2_12[0] + $Commandes_n2_12[1] + $Commandes_n2_12[2] + $Commandes_n2_12[3] + $Commandes_n2_12[4] + $Commandes_n2_12[5] + $Commandes_n2_12[6] + $Commandes_n2_12[7] + $Commandes_n2_12[8] + $Commandes_n2_12[9] + $Commandes_n2_12[10] + $Commandes_n2_12[11];

$Commandes_n2_13 = marketplaces_detail_commandes($yn2, "5,8", "13");
$Commandes_total_n2_13 = $Commandes_n2_13[0] + $Commandes_n2_13[1] + $Commandes_n2_13[2] + $Commandes_n2_13[3] + $Commandes_n2_13[4] + $Commandes_n2_13[5] + $Commandes_n2_13[6] + $Commandes_n2_13[7] + $Commandes_n2_13[8] + $Commandes_n2_13[9] + $Commandes_n2_13[10] + $Commandes_n2_13[11];




















//FONCTION PANIER


$Panier_annee_all_mp = panier($year, "5,8", "all");
$Panier_annee_01 = panier($year, "5,8", "01");
$Panier_annee_04 = panier($year, "5,8", "04");
$Panier_annee_05 = panier($year, "5,8", "05");
$Panier_annee_06 = panier($year, "5,8", "14");
$Panier_annee_07 = panier($year, "5,8", "07");
$Panier_annee_12 = panier($year, "5,8", "12");
$Panier_annee_13 = panier($year, "5,8", "13");




//TEMPS REEL
if ($year < $Y)
{
  $Panier_total_annee_all_mp = ($Panier_annee_all_mp[0] + $Panier_annee_all_mp[1] + $Panier_annee_all_mp[2] + $Panier_annee_all_mp[3] + $Panier_annee_all_mp[4] + $Panier_annee_all_mp[5] + $Panier_annee_all_mp[6] + $Panier_annee_all_mp[7] + $Panier_annee_all_mp[8] + $Panier_annee_all_mp[9] + $Panier_annee_all_mp[10] + $Panier_annee_all_mp[11]) / 12; 
  $Panier_total_annee_01 = ($Panier_annee_01[0] + $Panier_annee_01[1] + $Panier_annee_01[2] + $Panier_annee_01[3] + $Panier_annee_01[4] + $Panier_annee_01[5] + $Panier_annee_01[6] + $Panier_annee_01[7] + $Panier_annee_01[8] + $Panier_annee_01[9] + $Panier_annee_01[10] + $Panier_annee_01[11]) / 12;
  $Panier_total_annee_04 = ($Panier_annee_04[0] + $Panier_annee_04[1] + $Panier_annee_04[2] + $Panier_annee_04[3] + $Panier_annee_04[4] + $Panier_annee_04[5] + $Panier_annee_04[6] + $Panier_annee_04[7] + $Panier_annee_04[8] + $Panier_annee_04[9] + $Panier_annee_04[10] + $Panier_annee_04[11]) / 12;
  $Panier_total_annee_05 = ($Panier_annee_05[0] + $Panier_annee_05[1] + $Panier_annee_05[2] + $Panier_annee_05[3] + $Panier_annee_05[4] + $Panier_annee_05[5] + $Panier_annee_05[6] + $Panier_annee_05[7] + $Panier_annee_05[8] + $Panier_annee_05[9] + $Panier_annee_05[10] + $Panier_annee_05[11]) / 12;
  $Panier_total_annee_06 = ($Panier_annee_06[0] + $Panier_annee_06[1] + $Panier_annee_06[2] + $Panier_annee_06[3] + $Panier_annee_06[4] + $Panier_annee_06[5] + $Panier_annee_06[6] + $Panier_annee_06[7] + $Panier_annee_06[8] + $Panier_annee_06[9] + $Panier_annee_06[10] + $Panier_annee_06[11]) / 12;
  $Panier_total_annee_07 = ($Panier_annee_07[0] + $Panier_annee_07[1] + $Panier_annee_07[2] + $Panier_annee_07[3] + $Panier_annee_07[4] + $Panier_annee_07[5] + $Panier_annee_07[6] + $Panier_annee_07[7] + $Panier_annee_07[8] + $Panier_annee_07[9] + $Panier_annee_07[10] + $Panier_annee_07[11]) / 12;
  $Panier_total_annee_12 = ($Panier_annee_12[0] + $Panier_annee_12[1] + $Panier_annee_12[2] + $Panier_annee_12[3] + $Panier_annee_12[4] + $Panier_annee_12[5] + $Panier_annee_12[6] + $Panier_annee_12[7] + $Panier_annee_12[8] + $Panier_annee_12[9] + $Panier_annee_12[10] + $Panier_annee_12[11]) / 12;
  $Panier_total_annee_13 = ($Panier_annee_13[0] + $Panier_annee_13[1] + $Panier_annee_13[2] + $Panier_annee_13[3] + $Panier_annee_13[4] + $Panier_annee_13[5] + $Panier_annee_13[6] + $Panier_annee_13[7] + $Panier_annee_13[8] + $Panier_annee_13[9] + $Panier_annee_13[10] + $Panier_annee_13[11]) / 12;
}
elseif ($year == $Y)
{
  $Panier_total_annee_all_mp = ($Panier_annee_all_mp[0] + $Panier_annee_all_mp[1] + $Panier_annee_all_mp[2] + $Panier_annee_all_mp[3] + $Panier_annee_all_mp[4] + $Panier_annee_all_mp[5] + $Panier_annee_all_mp[6] + $Panier_annee_all_mp[7] + $Panier_annee_all_mp[8] + $Panier_annee_all_mp[9] + $Panier_annee_all_mp[10] + $Panier_annee_all_mp[11]) / $m; 
  $Panier_total_annee_01 = ($Panier_annee_01[0] + $Panier_annee_01[1] + $Panier_annee_01[2] + $Panier_annee_01[3] + $Panier_annee_01[4] + $Panier_annee_01[5] + $Panier_annee_01[6] + $Panier_annee_01[7] + $Panier_annee_01[8] + $Panier_annee_01[9] + $Panier_annee_01[10] + $Panier_annee_01[11]) / $m;
  $Panier_total_annee_04 = ($Panier_annee_04[0] + $Panier_annee_04[1] + $Panier_annee_04[2] + $Panier_annee_04[3] + $Panier_annee_04[4] + $Panier_annee_04[5] + $Panier_annee_04[6] + $Panier_annee_04[7] + $Panier_annee_04[8] + $Panier_annee_04[9] + $Panier_annee_04[10] + $Panier_annee_04[11]) / $m;
  $Panier_total_annee_05 = ($Panier_annee_05[0] + $Panier_annee_05[1] + $Panier_annee_05[2] + $Panier_annee_05[3] + $Panier_annee_05[4] + $Panier_annee_05[5] + $Panier_annee_05[6] + $Panier_annee_05[7] + $Panier_annee_05[8] + $Panier_annee_05[9] + $Panier_annee_05[10] + $Panier_annee_05[11]) / $m;
  $Panier_total_annee_06 = ($Panier_annee_06[0] + $Panier_annee_06[1] + $Panier_annee_06[2] + $Panier_annee_06[3] + $Panier_annee_06[4] + $Panier_annee_06[5] + $Panier_annee_06[6] + $Panier_annee_06[7] + $Panier_annee_06[8] + $Panier_annee_06[9] + $Panier_annee_06[10] + $Panier_annee_06[11]) / $m;
  $Panier_total_annee_07 = ($Panier_annee_07[0] + $Panier_annee_07[1] + $Panier_annee_07[2] + $Panier_annee_07[3] + $Panier_annee_07[4] + $Panier_annee_07[5] + $Panier_annee_07[6] + $Panier_annee_07[7] + $Panier_annee_07[8] + $Panier_annee_07[9] + $Panier_annee_07[10] + $Panier_annee_07[11]) / $m;
  $Panier_total_annee_12 = ($Panier_annee_12[0] + $Panier_annee_12[1] + $Panier_annee_12[2] + $Panier_annee_12[3] + $Panier_annee_12[4] + $Panier_annee_12[5] + $Panier_annee_12[6] + $Panier_annee_12[7] + $Panier_annee_12[8] + $Panier_annee_12[9] + $Panier_annee_12[10] + $Panier_annee_12[11]) / $m;
  $Panier_total_annee_13 = ($Panier_annee_13[0] + $Panier_annee_13[1] + $Panier_annee_13[2] + $Panier_annee_13[3] + $Panier_annee_13[4] + $Panier_annee_13[5] + $Panier_annee_13[6] + $Panier_annee_13[7] + $Panier_annee_13[8] + $Panier_annee_13[9] + $Panier_annee_13[10] + $Panier_annee_13[11]) / $m;
}




$Panier_n1_all_mp = panier($yn1, "5,8","all");
$Panier_total_n1_all_mp = ($Panier_n1_all_mp[0] + $Panier_n1_all_mp[1] + $Panier_n1_all_mp[2] + $Panier_n1_all_mp[3] + $Panier_n1_all_mp[4] + $Panier_n1_all_mp[5] + $Panier_n1_all_mp[6] + $Panier_n1_all_mp[7] + $Panier_n1_all_mp[8] + $Panier_n1_all_mp[9] + $Panier_n1_all_mp[10] + $Panier_n1_all_mp[11]) / 12;
$Panier_n2_all_mp = panier($yn2, "5,8","all");
$Panier_total_n2_all_mp = ($Panier_n2_all_mp[0] + $Panier_n2_all_mp[1] + $Panier_n2_all_mp[2] + $Panier_n2_all_mp[3] + $Panier_n2_all_mp[4] + $Panier_n2_all_mp[5] + $Panier_n2_all_mp[6] + $Panier_n2_all_mp[7] + $Panier_n2_all_mp[8] + $Panier_n2_all_mp[9] + $Panier_n2_all_mp[10] + $Panier_n2_all_mp[11]) / 12;

$Panier_n1_01 = panier($yn1, "5,8", "01");
$Panier_total_n1_01 = ($Panier_n1_01[0] + $Panier_n1_01[1] + $Panier_n1_01[2] + $Panier_n1_01[3] + $Panier_n1_01[4] + $Panier_n1_01[5] + $Panier_n1_01[6] + $Panier_n1_01[7] + $Panier_n1_01[8] + $Panier_n1_01[9] + $Panier_n1_01[10] + $Panier_n1_01[11]) / 12;
$Panier_n2_01 = panier($yn2, "5,8", "01");
$Panier_total_n2_01 = ($Panier_n2_01[0] + $Panier_n2_01[1] + $Panier_n2_01[2] + $Panier_n2_01[3] + $Panier_n2_01[4] + $Panier_n2_01[5] + $Panier_n2_01[6] + $Panier_n2_01[7] + $Panier_n2_01[8] + $Panier_n2_01[9] + $Panier_n2_01[10] + $Panier_n2_01[11]) / 12;

$Panier_n1_04 = panier($yn1, "5,8", "04");
$Panier_total_n1_04 = ($Panier_n1_04[0] + $Panier_n1_04[1] + $Panier_n1_04[2] + $Panier_n1_04[3] + $Panier_n1_04[4] + $Panier_n1_04[5] + $Panier_n1_04[6] + $Panier_n1_04[7] + $Panier_n1_04[8] + $Panier_n1_04[9] + $Panier_n1_04[10] + $Panier_n1_04[11]) / 12;
$Panier_n2_04 = panier($yn2, "5,8", "04");
$Panier_total_n2_04 = ($Panier_n2_04[0] + $Panier_n2_04[1] + $Panier_n2_04[2] + $Panier_n2_04[3] + $Panier_n2_04[4] + $Panier_n2_04[5] + $Panier_n2_04[6] + $Panier_n2_04[7] + $Panier_n2_04[8] + $Panier_n2_04[9] + $Panier_n2_04[10] + $Panier_n2_04[11]) / 12;

$Panier_n1_05 = panier($yn1, "5,8", "05");
$Panier_total_n1_05 = ($Panier_n1_05[0] + $Panier_n1_05[1] + $Panier_n1_05[2] + $Panier_n1_05[3] + $Panier_n1_05[4] + $Panier_n1_05[5] + $Panier_n1_05[6] + $Panier_n1_05[7] + $Panier_n1_05[8] + $Panier_n1_05[9] + $Panier_n1_05[10] + $Panier_n1_05[11]) / 12;
$Panier_n2_05 = panier($yn2, "5,8", "05");
$Panier_total_n2_05 = ($Panier_n2_05[0] + $Panier_n2_05[1] + $Panier_n2_05[2] + $Panier_n2_05[3] + $Panier_n2_05[4] + $Panier_n2_05[5] + $Panier_n2_05[6] + $Panier_n2_05[7] + $Panier_n2_05[8] + $Panier_n2_05[9] + $Panier_n2_05[10] + $Panier_n2_05[11]) / 12;

$Panier_n1_06 = panier($yn1, "5,8", "14");
$Panier_total_n1_06 = ($Panier_n1_06[0] + $Panier_n1_06[1] + $Panier_n1_06[2] + $Panier_n1_06[3] + $Panier_n1_06[4] + $Panier_n1_06[5] + $Panier_n1_06[6] + $Panier_n1_06[7] + $Panier_n1_06[8] + $Panier_n1_06[9] + $Panier_n1_06[10] + $Panier_n1_06[11]) / 12;
$Panier_n2_06 = panier($yn2, "5,8", "14");
$Panier_total_n2_06 = ($Panier_n2_06[0] + $Panier_n2_06[1] + $Panier_n2_06[2] + $Panier_n2_06[3] + $Panier_n2_06[4] + $Panier_n2_06[5] + $Panier_n2_06[6] + $Panier_n2_06[7] + $Panier_n2_06[8] + $Panier_n2_06[9] + $Panier_n2_06[10] + $Panier_n2_06[11]) / 12;

$Panier_n1_07 = panier($yn1, "5,8", "07");
$Panier_total_n1_07 = ($Panier_n1_07[0] + $Panier_n1_07[1] + $Panier_n1_07[2] + $Panier_n1_07[3] + $Panier_n1_07[4] + $Panier_n1_07[5] + $Panier_n1_07[6] + $Panier_n1_07[7] + $Panier_n1_07[8] + $Panier_n1_07[9] + $Panier_n1_07[10] + $Panier_n1_07[11]) / 12;
$Panier_n2_07 = panier($yn2, "5,8", "07");
$Panier_total_n2_07 = ($Panier_n2_07[0] + $Panier_n2_07[1] + $Panier_n2_07[2] + $Panier_n2_07[3] + $Panier_n2_07[4] + $Panier_n2_07[5] + $Panier_n2_07[6] + $Panier_n2_07[7] + $Panier_n2_07[8] + $Panier_n2_07[9] + $Panier_n2_07[10] + $Panier_n2_07[11]) / 12;

$Panier_n1_12 = panier($yn1, "5,8", "12");
$Panier_total_n1_12 = ($Panier_n1_12[0] + $Panier_n1_12[1] + $Panier_n1_12[2] + $Panier_n1_12[3] + $Panier_n1_12[4] + $Panier_n1_12[5] + $Panier_n1_12[6] + $Panier_n1_12[7] + $Panier_n1_12[8] + $Panier_n1_12[9] + $Panier_n1_12[10] + $Panier_n1_12[11]) / 12;
$Panier_n2_12 = panier($yn2, "5,8", "12");
$Panier_total_n2_12 = ($Panier_n2_12[0] + $Panier_n2_12[1] + $Panier_n2_12[2] + $Panier_n2_12[3] + $Panier_n2_12[4] + $Panier_n2_12[5] + $Panier_n2_12[6] + $Panier_n2_12[7] + $Panier_n2_12[8] + $Panier_n2_12[9] + $Panier_n2_12[10] + $Panier_n2_12[11]) / 12;

$Panier_n1_13 = panier($yn1, "5,8", "13");
$Panier_total_n1_13 = ($Panier_n1_13[0] + $Panier_n1_13[1] + $Panier_n1_13[2] + $Panier_n1_13[3] + $Panier_n1_13[4] + $Panier_n1_13[5] + $Panier_n1_13[6] + $Panier_n1_13[7] + $Panier_n1_13[8] + $Panier_n1_13[9] + $Panier_n1_13[10] + $Panier_n1_13[11]) / 12;
$Panier_n2_13 = panier($yn2, "5,8", "13");
$Panier_total_n2_13 = ($Panier_n2_13[0] + $Panier_n2_13[1] + $Panier_n2_13[2] + $Panier_n2_13[3] + $Panier_n2_13[4] + $Panier_n2_13[5] + $Panier_n2_13[6] + $Panier_n2_13[7] + $Panier_n2_13[8] + $Panier_n2_13[9] + $Panier_n2_13[10] + $Panier_n2_13[11]) / 12;






echo "<br>
<table class=\"ca\">
<tr>
<td colspan=\"7\" class=\"ca_second_title\">Factures</td>
<td colspan=\"1\" style=\"background-color:#ffffff; border: 1px solid #ffffff; border-right:1px solid black;\"></td>
<td colspan=\"6\" class=\"ca_second_title\">Panier moyen</td></tr>
<tr class=\"ca_head\">
  <td></td>
  <td>Nbr Factures</td>
  <td>Nbr Factures N-1</td>
  <td>Av/ret N-1</td>
  <td>% N-1</td>
  <td>Nbr Factures N-2</td>
  <td>% N-2</td>
  <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px;\"></td>
  <td>Panier moyen</td>
  <td>Panier moyen N-1</td>
  <td>% N-1</td>
  <td>Panier moyen N-2</td>
  <td>% N-2</td>
</tr>";



$i = 1;
$a = 0;
while($i <= 12)
{  
    if($year == $Y)
    {
    if($i<$m){
      $statn00 = $Commandes_annee_all_mp[$a] - $Commandes_n1_all_mp[$a];
      $statn1 = round(((($Commandes_annee_all_mp[$a] * 100) / $Commandes_n1_all_mp[$a]) - 100),2);
      $statn2 = round(((($Commandes_annee_all_mp[$a] * 100) / $Commandes_n2_all_mp[$a]) - 100),2);
    }
      if($i==$m){
        $statn00 = $Commandes_annee_all_mp[$a] - $Com_cur_all_n1[$a];
        $statn1 = round(((($Commandes_annee_all_mp[$a] * 100) / $Com_cur_all_n1[$a]) - 100),2);
        $statn2 = round(((($Commandes_annee_all_mp[$a] * 100) / $Com_cur_all_n2[$a]) - 100),2);
      }
      if ($i > $m){
        $statn00 = -1000000;
        $statn1 = -100;
        $statn2 = -100;
      }
  }

  if($year < $Y)
  {
      $statn00 = $Commandes_annee_all_mp[$a] - $Commandes_n1_all_mp[$a];
      $statn1 = round(((($Commandes_annee_all_mp[$a] * 100) / $Commandes_n1_all_mp[$a]) - 100),2);
      $statn2 = round(((($Commandes_annee_all_mp[$a] * 100) / $Commandes_n2_all_mp[$a]) - 100),2);
 }

  $statn3 = round(((($Panier_annee_all_mp[$a] * 100) / $Panier_n1_all_mp[$a]) - 100),2);
  $statn4 = round(((($Panier_annee_all_mp[$a] * 100) / $Panier_n2_all_mp[$a]) - 100),2);










  if ($statn00 < 0)
  {
    $class00 = 'stat_rouge';
    if ($statn00 == -1000000)
      $statn00 = NULL;
  }
  else {
    $statn00 = "+".$statn00;
    $class00 = 'stat_vert';
  }

  if ($statn1 < 0){
      $class = 'stat_rouge';
      $statn1 = $statn1."%";
      if($statn1 == -100)
        $statn1 = NULL;
  }
  else {
      $statn1 = "+".$statn1."%";
      $class = 'stat_vert';
  }


  if ($statn2 < 0){
      $class2 = 'stat_rouge';
      $statn2 = $statn2."%";
      if($statn2 == -100)
        $statn2 = NULL;
  }
  else {
      $statn2 = "+".$statn2."%";
      $class2 = 'stat_vert';
  }




  if ($statn3 < 0){
      $class3 = 'stat_rouge';
      $statn3 = $statn3."%";
      if($statn3 == -100)
        $statn3 = NULL;
  }
  else {
      $statn3 = "+".$statn3."%";
      $class3 = 'stat_vert';
  }


  if ($statn4 < 0){
      $class4 = 'stat_rouge';
      $statn4 = $statn4."%";
      if($statn4 == -100)
        $statn4 = NULL;
  }
  else {
      $statn4 = "+".$statn4."%";
      $class4 = 'stat_vert';
  }







    echo "<tr>
      <td>".$i."-".$year."</td>
      <td style=\"text-align:right\">".$Commandes_annee_all_mp[$a]."</td>
      <td style=\"text-align:right\">".$Commandes_n1_all_mp[$a]."</td>
      <td class=\"".$class00."\">".$statn00."</td>
      <td class=\"".$class."\">".$statn1."</td>
      <td style=\"text-align:right\">".$Commandes_n2_all_mp[$a]."</td>
      <td class=\"".$class2."\">".$statn2."</td>
      <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px;\"></td>
      <td style=\"text-align:right\">".number_format($Panier_annee_all_mp[$a],2,',','')."&euro;</td>
      <td style=\"text-align:right\">".number_format($Panier_n1_all_mp[$a],2,',','')."&euro;</td>
      <td class=\"".$class3."\">".$statn3."</td>
      <td style=\"text-align:right\">".number_format($Panier_n2_all_mp[$a],2,',','')."&euro;</td>
      <td class=\"".$class4."\">".$statn4."</td>
    </tr>";
    $statn1 = 0;
  $a++;
  $i++;
}





if ($year == $Y)
{
  $total_stat_fac_n1 = round(((($Commandes_total_annee_all_mp * 100) / $Com_total_cur_alln1) - 100),2);
  $total_stat_fac_n2 = round(((($Commandes_total_annee_all_mp * 100) / $Com_total_cur_alln2) - 100),2);
  $total_stat_avret_comn1 = $Commandes_total_annee_all_mp - $Com_total_cur_alln1;
}
elseif ($year < $Y)
{
  $total_stat_fac_n1 = round(((($Commandes_total_annee_all_mp * 100) / $Commandes_total_n1_all_mp) - 100),2);
  $total_stat_fac_n2 = round(((($Commandes_total_annee_all_mp * 100) / $Commandes_total_n2_all_mp) - 100),2);
  $total_stat_avret_comn1 = $Commandes_total_annee_all_mp - $Commandes_total_n1_all_mp;
}




  if ($total_stat_avret_comn1 < 0)
  {
    $class09 = 'stat_rouge';
  }
  else {
    $total_stat_avret_comn1 = "+".$total_stat_avret_comn1;
    $class09 = 'stat_vert';
  }


  if ($total_stat_fac_n1 < 0){
      $class9 = 'stat_rouge';
      $total_stat_fac_n1 = $total_stat_fac_n1."%";
      if($total_stat_fac_n1 == -100)
        $total_stat_fac_n1 = NULL;
  }
  else {
      $total_stat_fac_n1 = "+".$total_stat_fac_n1."%";
      $class9 = 'stat_vert';
  }


  if ($total_stat_fac_n2 < 0){
      $class10 = 'stat_rouge';
      $total_stat_fac_n2 = $total_stat_fac_n2."%";
      if($total_stat_fac_n2 == -100)
        $total_stat_fac_n2 = NULL;
  }
  else {
      $total_stat_fac_n2 = "+".$total_stat_fac_n2."%";
      $class10 = 'stat_vert';
  }








$total_stat_pan_n1 = round(((($Panier_total_annee_all_mp * 100) / $Panier_total_n1_all_mp) - 100),2);

  if ($total_stat_pan_n1 < 0){
      $class11 = 'stat_rouge';
      $total_stat_pan_n1 = $total_stat_pan_n1."%";
      if($total_stat_pan_n1 == -100)
        $total_stat_pan_n1 = NULL;
  }
  else {
      $total_stat_pan_n1 = "+".$total_stat_pan_n1."%";
      $class11 = 'stat_vert';
  }

$total_stat_pan_n2 = round(((($Panier_total_annee_all_mp * 100) / $Panier_total_n2_all_mp) - 100),2);

  if ($total_stat_pan_n2 < 0){
      $class12 = 'stat_rouge';
      $total_stat_pan_n2 = $total_stat_pan_n2."%";
      if($total_stat_pan_n2 == -100)
        $total_stat_pan_n2 = NULL;
  }
  else {
      $total_stat_pan_n2 = "+".$total_stat_pan_n2."%";
      $class12 = 'stat_vert';
  }







echo "<tr style=\"font-weight:bold;\">
  <td>Total année $year</td>
  <td class=\"ca_nb\">".$Commandes_total_annee_all_mp."</td>
  <td class=\"ca_nb\">".$Commandes_total_n1_all_mp."</td>
  <td class=\"".$class09."\">".$total_stat_avret_comn1."</td>
  <td class=\"".$class9."\">".$total_stat_fac_n1."</td>
  <td class=\"ca_nb\">".$Commandes_total_n2_all_mp."</td>
  <td class=\"".$class10."\">".$total_stat_fac_n2."</td>
  <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
  <td class=\"ca_nb\">".number_format($Panier_total_annee_all_mp,2,',','')."&euro;</td>
  <td class=\"ca_nb\">".number_format($Panier_total_n1_all_mp,2,',','')."&euro;</td>
  <td class=\"".$class11."\">".$total_stat_pan_n1."</td>
  <td class=\"ca_nb\">".number_format($Panier_total_n2_all_mp,2,',','')."&euro;</td>
  <td class=\"".$class12."\">".$total_stat_pan_n2."</td>
</tr>";

echo "</table>";

echo "<br><br><br><br>";

?>
























<p style="text-align:center; font-size:14px;">Cliquez sur les Market Places suivantes pour faire apparaitre un tableau détaillé</p>
<label class="collapse" for="_1">Amazon</label>
<input id="_1" type="checkbox"> 
<div>

<?php
/*
  AAA   MM    MM   AAA   ZZZZZ  OOOOO  NN   NN 
 AAAAA  MMM  MMM  AAAAA     ZZ OO   OO NNN  NN 
AA   AA MM MM MM AA   AA   ZZ  OO   OO NN N NN 
AAAAAAA MM    MM AAAAAAA  ZZ   OO   OO NN  NNN 
AA   AA MM    MM AA   AA ZZZZZ  OOOO0  NN   NN */
echo "<table class=\"ca\">
<tr class=\"ca_head\">
  <td></td>
  <td>Chiffre d'affaires &euro;HT</td>
  <td>Av/ret N-1 &euro;HT</td>
  <td>% N-1</td>
  <td>Av/ret N-2 &euro;HT</td>
  <td>% N-2</td>
  <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-top:1px solid #af824c;\"></td>
  <td>Nbr Factures</td>
  <td>Nbr Factures N-1</td>
  <td>Av/ret N-1</td>
  <td>% N-1</td>
  <td>Nbr Factures N-2</td>
  <td>% N-2</td>
  <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-top:1px solid #af824c;\"></td>
  <td>Panier</td>
  <td>Panier N-1</td>
  <td>% N-1</td>
  <td>Panier N-2</td>
  <td>% N-2</td>
</tr>";

$a = 0;
for($i=1;$i<=12;$i++){

      if($year == $Y)
    {
    if($i<$m){
      $statn00 = $Commandes_annee_01[$a] - $Commandes_n1_01[$a];
      $statn1 = round(((($Commandes_annee_01[$a] * 100) / $Commandes_n1_01[$a]) - 100),2);
      $statn2 = round(((($Commandes_annee_01[$a] * 100) / $Commandes_n2_01[$a]) - 100),2);
    }
      if($i==$m){
        $statn00 = $Commandes_annee_01[$a] - $Com_cur_01_n1[$a];
        $statn1 = round(((($Commandes_annee_01[$a] * 100) / $Com_cur_01_n1[$a]) - 100),2);
        $statn2 = round(((($Commandes_annee_01[$a] * 100) / $Com_cur_01_n2[$a]) - 100),2);
      }
      if ($i > $m){
        $statn00 = -1000000;
        $statn1 = -100;
        $statn2 = -100;
      }
  }

  if($year < $Y)
  {
      $statn00 = $Commandes_annee_01[$a] - $Commandes_n1_01[$a];
      $statn1 = round(((($Commandes_annee_01[$a] * 100) / $Commandes_n1_01[$a]) - 100),2);
      $statn2 = round(((($Commandes_annee_01[$a] * 100) / $Commandes_n2_01[$a]) - 100),2);
 }

  $statn3 = round(((($Panier_annee_01[$a] * 100) / $Panier_n1_01[$a]) - 100),2);
  $statn4 = round(((($Panier_annee_01[$a] * 100) / $Panier_n2_01[$a]) - 100),2);



  if ($statn00 < 0)
  {
    $class00 = 'stat_rouge';
    if ($statn00 == -1000000)
      $statn00 = NULL;
  }
  else {
    $statn00 = "+".$statn00;
    $class00 = 'stat_vert';
  }
  if ($statn1 < 0){
      $class = 'stat_rouge';
      $statn1 = $statn1."%";
      if($statn1 == -100)
        $statn1 = NULL;
  }
  else {
      $statn1 = "+".$statn1."%";
      $class = 'stat_vert';
  }
  if ($statn2 < 0){
      $class2 = 'stat_rouge';
      $statn2 = $statn2."%";
      if($statn2 == -100)
        $statn2 = NULL;
  }
  else {
      $statn2 = "+".$statn2."%";
      $class2 = 'stat_vert';
  }
  if ($statn3 < 0){
      $class3 = 'stat_rouge';
      $statn3 = $statn3."%";
      if($statn3 == -100)
        $statn3 = NULL;
  }
  else {
      $statn3 = "+".$statn3."%";
      $class3 = 'stat_vert';
  }
  if ($statn4 < 0){
      $class4 = 'stat_rouge';
      $statn4 = $statn4."%";
      if($statn4 == -100)
        $statn4 = NULL;
  }
  else {
      $statn4 = "+".$statn4."%";
      $class4 = 'stat_vert';
  }


    echo "<tr>
      <td>".$i."-".$year."</td>
      <td class=\"ca_nb\">".number_format($Ca_annee_amazone_mp[$a],2,',',' ')." &euro;</td>".
      tdvalue($av_ret_n1ht_01[$a],"&euro;",true).
      tdvalue($av_ret_n1prcent_01[$a],"%",false,100).
      tdvalue($av_ret_n2ht_01[$a],"&euro;",true).
      tdvalue($av_ret_n2prcent_01[$a],"%",false,100)."
      <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px;\"></td>
      <td class=\"ca_nb\">".$Commandes_annee_01[$a]."</td>
      <td class=\"ca_nb\">".$Commandes_n1_01[$a]."</td>
      <td class=\"".$class00."\">".$statn00."</td>
      <td class=\"".$class."\">".$statn1."</td>
      <td class=\"ca_nb\">".$Commandes_n2_01[$a]."</td>
      <td class=\"".$class2."\">".$statn2."</td>
      <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px;\"></td>
      <td class=\"ca_nb\">".number_format($Panier_annee_01[$a],2,',',' ')."</td>
      <td class=\"ca_nb\">".number_format($Panier_n1_01[$a],2,',',' ')."</td>
      <td class=\"".$class3."\">".$statn3."</td>
      <td class=\"ca_nb\">".number_format($Panier_n2_01[$a],2,',',' ')."</td>
      <td class=\"".$class4."\">".$statn3."</td>
    </tr>";
   $a++;
}


if ($year == $Y)
{
  $total_stat_fac_n1 = round(((($Commandes_total_annee_01 * 100) / $Com_total_cur_01n1) - 100),2);
  $total_stat_fac_n2 = round(((($Commandes_total_annee_01 * 100) / $Com_total_cur_01n2) - 100),2);
  $total_stat_avret_comn1 = $Commandes_total_annee_01 - $Com_total_cur_01n1;
}
elseif ($year < $Y)
{
  $total_stat_fac_n1 = round(((($Commandes_total_annee_01 * 100) / $Commandes_total_n1_01) - 100),2);
  $total_stat_fac_n2 = round(((($Commandes_total_annee_01 * 100) / $Commandes_total_n2_01) - 100),2);
  $total_stat_avret_comn1 = $Commandes_total_annee_01 - $Commandes_total_n1_01;
}



  if ($total_stat_avret_comn1 < 0)
  {
    $class09 = 'stat_rouge';
  }
  else {
    $total_stat_avret_comn1 = "+".$total_stat_avret_comn1;
    $class09 = 'stat_vert';
  }
  if ($total_stat_fac_n1 < 0){
      $class9 = 'stat_rouge';
      $total_stat_fac_n1 = $total_stat_fac_n1."%";
      if($total_stat_fac_n1 == -100)
        $total_stat_fac_n1 = NULL;
  }
  else {
      $total_stat_fac_n1 = "+".$total_stat_fac_n1."%";
      $class9 = 'stat_vert';
  }
  if ($total_stat_fac_n2 < 0){
      $class10 = 'stat_rouge';
      $total_stat_fac_n2 = $total_stat_fac_n2."%";
      if($total_stat_fac_n2 == -100)
        $total_stat_fac_n2 = NULL;
  }
  else {
      $total_stat_fac_n2 = "+".$total_stat_fac_n2."%";
      $class10 = 'stat_vert';
  }




$total_stat_pan_n1 = round(((($Panier_total_annee_01 * 100) / $Panier_total_n1_01) - 100),2);

  if ($total_stat_pan_n1 < 0){
      $class11 = 'stat_rouge';
      $total_stat_pan_n1 = $total_stat_pan_n1."%";
      if($total_stat_pan_n1 == -100)
        $total_stat_pan_n1 = NULL;
  }
  else {
      $total_stat_pan_n1 = "+".$total_stat_pan_n1."%";
      $class11 = 'stat_vert';
  }

$total_stat_pan_n2 = round(((($Panier_total_annee_01 * 100) / $Panier_total_n2_01) - 100),2);

  if ($total_stat_pan_n2 < 0){
      $class12 = 'stat_rouge';
      $total_stat_pan_n2 = $total_stat_pan_n2."%";
      if($total_stat_pan_n2 == -100)
        $total_stat_pan_n2 = NULL;
  }
  else {
      $total_stat_pan_n2 = "+".$total_stat_pan_n2."%";
      $class12 = 'stat_vert';
  }



echo "<tr style=\"font-weight:bold;\">
  <td>Total année $year</td>
  <td class=\"ca_nb\">".number_format($Ca_total_annee_01,2,',',' ')." &euro;</td>".
  tdvalue($total_av_ret_n1ht_01,"&euro;",true).
  tdvalue($total_av_ret_n1prcent_01,"%",true).
  tdvalue($total_av_ret_n2ht_01,"&euro;",true).
  tdvalue($total_av_ret_n2prcent_01,"%",true)."
  <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
  <td class=\"ca_nb\">".$Commandes_total_annee_01."</td>
  <td class=\"ca_nb\">".$Commandes_total_n1_01."</td>
  <td class=\"".$class09."\">".$total_stat_avret_comn1."</td>
  <td class=\"".$class9."\">".$total_stat_fac_n1."</td>
  <td class=\"ca_nb\">".$Commandes_total_n2_01."</td>
  <td class=\"".$class10."\">".$total_stat_fac_n2."</td>
  <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
  <td class=\"ca_nb\">".number_format($Panier_total_annee_01,2,',','')."&euro;</td>
  <td class=\"ca_nb\">".number_format($Panier_total_n1_01,2,',','')."&euro;</td>
  <td class=\"".$class11."\">".$total_stat_pan_n1."</td>
  <td class=\"ca_nb\">".number_format($Panier_total_n2_01,2,',','')."&euro;</td>
  <td class=\"".$class12."\">".$total_stat_pan_n2."</td>
</tr>";

echo "</table>";
echo "<br>";
?>
</div>



































<label class="collapse" for="_2">Price Minister</label>
<input id="_2" type="checkbox">
<div>
<?php
/*
PPPPPP  RRRRRR  IIIII  CCCCC  EEEEEEE MM    MM IIIII NN   NN IIIII  SSSSS  TTTTTTT EEEEEEE RRRRRR  
PP   PP RR   RR  III  CC    C EE      MMM  MMM  III  NNN  NN  III  SS        TTT   EE      RR   RR 
PPPPPP  RRRRRR   III  CC      EEEEE   MM MM MM  III  NN N NN  III   SSSSS    TTT   EEEEE   RRRRRR  
PP      RR  RR   III  CC    C EE      MM    MM  III  NN  NNN  III       SS   TTT   EE      RR  RR  
PP      RR   RR IIIII  CCCCC  EEEEEEE MM    MM IIIII NN   NN IIIII  SSSSS    TTT   EEEEEEE RR   RR                                                                                                  
*/
echo "<table class=\"ca\">
<tr class=\"ca_head\">
  <td></td>
  <td>Chiffre d'affaires &euro;HT</td>
  <td>Av/ret N-1 &euro;HT</td>
  <td>% N-1</td>
  <td>Av/ret N-2 &euro;HT</td>
  <td>% N-2</td>
  <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-top:1px solid #af824c;\"></td>
  <td>Nbr Factures</td>
  <td>Nbr Factures N-1</td>
  <td>Av/ret N-1</td>
  <td>% N-1</td>
  <td>Nbr Factures N-2</td>
  <td>% N-2</td>
  <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-top:1px solid #af824c;\"></td>
  <td>Panier</td>
  <td>Panier N-1</td>
  <td>% N-1</td>
  <td>Panier N-2</td>
  <td>% N-2</td>
</tr>";


$a = 0;
for($i=1;$i<=12;$i++){

      if($year == $Y)
    {
    if($i<$m){
      $statn00 = $Commandes_annee_04[$a] - $Commandes_n1_04[$a];
      $statn1 = round(((($Commandes_annee_04[$a] * 100) / $Commandes_n1_04[$a]) - 100),2);
      $statn2 = round(((($Commandes_annee_04[$a] * 100) / $Commandes_n2_04[$a]) - 100),2);
    }
      if($i==$m){
        $statn00 = $Commandes_annee_04[$a] - $Com_cur_04_n1[$a];
        $statn1 = round(((($Commandes_annee_04[$a] * 100) / $Com_cur_04_n1[$a]) - 100),2);
        $statn2 = round(((($Commandes_annee_04[$a] * 100) / $Com_cur_04_n2[$a]) - 100),2);
      }
      if ($i > $m){
        $statn00 = -1000000;
        $statn1 = -100;
        $statn2 = -100;
      }
  }

  if($year < $Y)
  {
      $statn00 = $Commandes_annee_04[$a] - $Commandes_n1_04[$a];
      $statn1 = round(((($Commandes_annee_04[$a] * 100) / $Commandes_n1_04[$a]) - 100),2);
      $statn2 = round(((($Commandes_annee_04[$a] * 100) / $Commandes_n2_04[$a]) - 100),2);
 }


  $statn3 = round(((($Panier_annee_04[$a] * 100) / $Panier_n1_04[$a]) - 100),2);
  $statn4 = round(((($Panier_annee_04[$a] * 100) / $Panier_n2_04[$a]) - 100),2);

  if ($statn00 < 0)
  {
    $class00 = 'stat_rouge';
    if ($statn00 == -1000000)
      $statn00 = NULL;
  }
  else {
    $statn00 = "+".$statn00;
    $class00 = 'stat_vert';
  }
  if ($statn1 < 0){
      $class = 'stat_rouge';
      $statn1 = $statn1."%";
      if($statn1 == -100)
        $statn1 = NULL;
  }
  else {
      $statn1 = "+".$statn1."%";
      $class = 'stat_vert';
  }
  if ($statn2 < 0){
      $class2 = 'stat_rouge';
      $statn2 = $statn2."%";
      if($statn2 == -100)
        $statn2 = NULL;
  }
  else {
      $statn2 = "+".$statn2."%";
      $class2 = 'stat_vert';
  }

  if ($statn3 < 0){
      $class3 = 'stat_rouge';
      $statn3 = $statn3."%";
      if($statn3 == -100)
        $statn3 = NULL;
  }
  else {
      $statn3 = "+".$statn3."%";
      $class3 = 'stat_vert';
  }

  if ($statn4 < 0){
      $class4 = 'stat_rouge';
      $statn4 = $statn4."%";
      if($statn4 == -100)
        $statn4 = NULL;
  }
  else {
      $statn4 = "+".$statn4."%";
      $class4 = 'stat_vert';
  }

    echo "<tr>
      <td>".$i."-".$year."</td>
      <td class=\"ca_nb\">".number_format($Ca_annee_priceminister_mp[$a],2,',',' ')." &euro;</td>".
      tdvalue($av_ret_n1ht_04[$a],"&euro;",true).
      tdvalue($av_ret_n1prcent_04[$a],"%",false,100).
      tdvalue($av_ret_n2ht_04[$a],"&euro;",true).
      tdvalue($av_ret_n2prcent_04[$a],"%",false,100)."
      <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px;\"></td>
      <td class=\"ca_nb\">".$Commandes_annee_04[$a]."</td>
      <td class=\"ca_nb\">".$Commandes_n1_04[$a]."</td>
      <td class=\"".$class00."\">".$statn00."</td>
      <td class=\"".$class."\">".$statn1."</td>
      <td class=\"ca_nb\">".$Commandes_n2_04[$a]."</td>
      <td class=\"".$class2."\">".$statn2."</td>
      <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px;\"></td>
      <td class=\"ca_nb\">".number_format($Panier_annee_04[$a],2,',',' ')."</td>
      <td class=\"ca_nb\">".number_format($Panier_n1_04[$a],2,',',' ')."</td>
      <td class=\"".$class3."\">".$statn3."</td>
      <td class=\"ca_nb\">".number_format($Panier_n2_04[$a],2,',',' ')."</td>
      <td class=\"".$class4."\">".$statn3."</td>
    </tr>";
   $a++;
}

if ($year == $Y)
{
  $total_stat_fac_n1 = round(((($Commandes_total_annee_04 * 100) / $Com_total_cur_04n1) - 100),2);
  $total_stat_fac_n2 = round(((($Commandes_total_annee_04 * 100) / $Com_total_cur_04n2) - 100),2);
  $total_stat_avret_comn1 = $Commandes_total_annee_04 - $Com_total_cur_04n1;
}
elseif ($year < $Y)
{
  $total_stat_fac_n1 = round(((($Commandes_total_annee_04 * 100) / $Commandes_total_n1_04) - 100),2);
  $total_stat_fac_n2 = round(((($Commandes_total_annee_04 * 100) / $Commandes_total_n2_04) - 100),2);
  $total_stat_avret_comn1 = $Commandes_total_annee_04 - $Commandes_total_n1_04;
}

  if ($total_stat_avret_comn1 < 0)
  {
    $class09 = 'stat_rouge';
  }
  else {
    $total_stat_avret_comn1 = "+".$total_stat_avret_comn1;
    $class09 = 'stat_vert';
  }

  if ($total_stat_fac_n1 < 0){
      $class9 = 'stat_rouge';
      $total_stat_fac_n1 = $total_stat_fac_n1."%";
      if($total_stat_fac_n1 == -100)
        $total_stat_fac_n1 = NULL;
  }
  else {
      $total_stat_fac_n1 = "+".$total_stat_fac_n1."%";
      $class9 = 'stat_vert';
  }

  if ($total_stat_fac_n2 < 0){
      $class10 = 'stat_rouge';
      $total_stat_fac_n2 = $total_stat_fac_n2."%";
      if($total_stat_fac_n2 == -100)
        $total_stat_fac_n2 = NULL;
  }
  else {
      $total_stat_fac_n2 = "+".$total_stat_fac_n2."%";
      $class10 = 'stat_vert';
  }


$total_stat_pan_n1 = round(((($Panier_total_annee_04 * 100) / $Panier_total_n1_04) - 100),2);

  if ($total_stat_pan_n1 < 0){
      $class11 = 'stat_rouge';
      $total_stat_pan_n1 = $total_stat_pan_n1."%";
      if($total_stat_pan_n1 == -100)
        $total_stat_pan_n1 = NULL;
  }
  else {
      $total_stat_pan_n1 = "+".$total_stat_pan_n1."%";
      $class11 = 'stat_vert';
  }

$total_stat_pan_n2 = round(((($Panier_total_annee_04 * 100) / $Panier_total_n2_04) - 100),2);

  if ($total_stat_pan_n2 < 0){
      $class12 = 'stat_rouge';
      $total_stat_pan_n2 = $total_stat_pan_n2."%";
      if($total_stat_pan_n2 == -100)
        $total_stat_pan_n2 = NULL;
  }
  else {
      $total_stat_pan_n2 = "+".$total_stat_pan_n2."%";
      $class12 = 'stat_vert';
  }

echo "<tr style=\"font-weight:bold;\">
  <td>Total année $year</td>
  <td class=\"ca_nb\">".number_format($Ca_total_annee_04,2,',',' ')." &euro;</td>".
  tdvalue($total_av_ret_n1ht_04,"&euro;",true).
  tdvalue($total_av_ret_n1prcent_04,"%",true).
  tdvalue($total_av_ret_n2ht_04,"&euro;",true).
  tdvalue($total_av_ret_n2prcent_04,"%",true)."
  <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
  <td class=\"ca_nb\">".$Commandes_total_annee_04."</td>
  <td class=\"ca_nb\">".$Commandes_total_n1_04."</td>
  <td class=\"".$class09."\">".$total_stat_avret_comn1."</td>
  <td class=\"".$class9."\">".$total_stat_fac_n1."</td>
  <td class=\"ca_nb\">".$Commandes_total_n2_04."</td>
  <td class=\"".$class10."\">".$total_stat_fac_n2."</td>
  <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
  <td class=\"ca_nb\">".number_format($Panier_total_annee_04,2,',','')."&euro;</td>
  <td class=\"ca_nb\">".number_format($Panier_total_n1_04,2,',','')."&euro;</td>
  <td class=\"".$class11."\">".$total_stat_pan_n1."</td>
  <td class=\"ca_nb\">".number_format($Panier_total_n2_04,2,',','')."&euro;</td>
  <td class=\"".$class12."\">".$total_stat_pan_n2."</td>
</tr>";

echo "</table>";
echo "<br>";
?>
</div>




































<label class="collapse" for="_3">Pixmania</label>
<input id="_3" type="checkbox">
<div>

<?php
/*
PPPPPP  IIIII XX    XX MM    MM   AAA   NN   NN IIIII   AAA   
PP   PP  III   XX  XX  MMM  MMM  AAAAA  NNN  NN  III   AAAAA  
PPPPPP   III    XXXX   MM MM MM AA   AA NN N NN  III  AA   AA 
PP       III   XX  XX  MM    MM AAAAAAA NN  NNN  III  AAAAAAA 
PP      IIIII XX    XX MM    MM AA   AA NN   NN IIIII AA   AA 
*/
echo "<table class=\"ca\">
<tr class=\"ca_head\">
  <td></td>
  <td>Chiffre d'affaires &euro;HT</td>
  <td>Av/ret N-1 &euro;HT</td>
  <td>% N-1</td>
  <td>Av/ret N-2 &euro;HT</td>
  <td>% N-2</td>
  <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-top:1px solid #af824c;\"></td>
  <td>Nbr Factures</td>
  <td>Nbr Factures N-1</td>
  <td>Av/ret N-1</td>
  <td>% N-1</td>
  <td>Nbr Factures N-2</td>
  <td>% N-2</td>
  <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-top:1px solid #af824c;\"></td>
  <td>Panier</td>
  <td>Panier N-1</td>
  <td>% N-1</td>
  <td>Panier N-2</td>
  <td>% N-2</td>
</tr>";

$a = 0;
for($i=1;$i<=12;$i++){

      if($year == $Y)
    {
    if($i<$m){
      $statn00 = $Commandes_annee_05[$a] - $Commandes_n1_05[$a];
      $statn1 = round(((($Commandes_annee_05[$a] * 100) / $Commandes_n1_05[$a]) - 100),2);
      $statn2 = round(((($Commandes_annee_05[$a] * 100) / $Commandes_n2_05[$a]) - 100),2);
    }
      if($i==$m){
        $statn00 = $Commandes_annee_05[$a] - $Com_cur_05_n1[$a];
        $statn1 = round(((($Commandes_annee_05[$a] * 100) / $Com_cur_05_n1[$a]) - 100),2);
        $statn2 = round(((($Commandes_annee_05[$a] * 100) / $Com_cur_05_n2[$a]) - 100),2);
      }
      if ($i > $m){
        $statn00 = -1000000;
        $statn1 = -100;
        $statn2 = -100;
      }
  }

  if($year < $Y)
  {
      $statn00 = $Commandes_annee_05[$a] - $Commandes_n1_05[$a];
      $statn1 = round(((($Commandes_annee_05[$a] * 100) / $Commandes_n1_05[$a]) - 100),2);
      $statn2 = round(((($Commandes_annee_05[$a] * 100) / $Commandes_n2_05[$a]) - 100),2);
 }

  $statn3 = round(((($Panier_annee_05[$a] * 100) / $Panier_n1_05[$a]) - 100),2);
  $statn4 = round(((($Panier_annee_05[$a] * 100) / $Panier_n2_05[$a]) - 100),2);

  if ($statn00 < 0)
  {
    $class00 = 'stat_rouge';
    if ($statn00 == -1000000)
      $statn00 = NULL;
  }
  else {
    $statn00 = "+".$statn00;
    $class00 = 'stat_vert';
  }

  if ($statn1 < 0){
      $class = 'stat_rouge';
      $statn1 = $statn1."%";
      if($statn1 == -100)
        $statn1 = NULL;
  }
  else {
      $statn1 = "+".$statn1."%";
      $class = 'stat_vert';
  }

  if ($statn2 < 0){
      $class2 = 'stat_rouge';
      $statn2 = $statn2."%";
      if($statn2 == -100)
        $statn2 = NULL;
  }
  else {
      $statn2 = "+".$statn2."%";
      $class2 = 'stat_vert';
  }

  if ($statn3 < 0){
      $class3 = 'stat_rouge';
      $statn3 = $statn3."%";
      if($statn3 == -100)
        $statn3 = NULL;
  }
  else {
      $statn3 = "+".$statn3."%";
      $class3 = 'stat_vert';
  }


  if ($statn4 < 0){
      $class4 = 'stat_rouge';
      $statn4 = $statn4."%";
      if($statn4 == -100)
        $statn4 = NULL;
  }
  else {
      $statn4 = "+".$statn4."%";
      $class4 = 'stat_vert';
  }

    echo "<tr>
      <td>".$i."-".$year."</td>
      <td class=\"ca_nb\">".number_format($Ca_annee_pixmania_mp[$a],2,',',' ')." &euro;</td>".
      tdvalue($av_ret_n1ht_05[$a],"&euro;",true).
      tdvalue($av_ret_n1prcent_05[$a],"%",false,100).
      tdvalue($av_ret_n2ht_05[$a],"&euro;",true).
      tdvalue($av_ret_n2prcent_05[$a],"%",false,100)."
      <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px;\"></td>
      <td class=\"ca_nb\">".$Commandes_annee_05[$a]."</td>
      <td class=\"ca_nb\">".$Commandes_n1_05[$a]."</td>
      <td class=\"".$class00."\">".$statn00."</td>
      <td class=\"".$class."\">".$statn1."</td>
      <td class=\"ca_nb\">".$Commandes_n2_05[$a]."</td>
      <td class=\"".$class2."\">".$statn2."</td>
      <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px;\"></td>
      <td class=\"ca_nb\">".number_format($Panier_annee_05[$a],2,',',' ')."</td>
      <td class=\"ca_nb\">".number_format($Panier_n1_05[$a],2,',',' ')."</td>
      <td class=\"".$class3."\">".$statn3."</td>
      <td class=\"ca_nb\">".number_format($Panier_n2_05[$a],2,',',' ')."</td>
      <td class=\"".$class4."\">".$statn3."</td>
    </tr>";
   $a++;
}

if ($year == $Y)
{
  $total_stat_fac_n1 = round(((($Commandes_total_annee_05 * 100) / $Com_total_cur_05n1) - 100),2);
  $total_stat_fac_n2 = round(((($Commandes_total_annee_05 * 100) / $Com_total_cur_05n2) - 100),2);
  $total_stat_avret_comn1 = $Commandes_total_annee_05 - $Com_total_cur_05n1;
}
elseif ($year < $Y)
{
  $total_stat_fac_n1 = round(((($Commandes_total_annee_05 * 100) / $Commandes_total_n1_05) - 100),2);
  $total_stat_fac_n2 = round(((($Commandes_total_annee_05 * 100) / $Commandes_total_n2_05) - 100),2);
  $total_stat_avret_comn1 = $Commandes_total_annee_05 - $Commandes_total_n1_05;
}

  if ($total_stat_avret_comn1 < 0)
  {
    $class09 = 'stat_rouge';
  }
  else {
    $total_stat_avret_comn1 = "+".$total_stat_avret_comn1;
    $class09 = 'stat_vert';
  }

  if ($total_stat_fac_n1 < 0){
      $class9 = 'stat_rouge';
      $total_stat_fac_n1 = $total_stat_fac_n1."%";
      if($total_stat_fac_n1 == -100)
        $total_stat_fac_n1 = NULL;
  }
  else {
      $total_stat_fac_n1 = "+".$total_stat_fac_n1."%";
      $class9 = 'stat_vert';
  }

  if ($total_stat_fac_n2 < 0){
      $class10 = 'stat_rouge';
      $total_stat_fac_n2 = $total_stat_fac_n2."%";
      if($total_stat_fac_n2 == -100)
        $total_stat_fac_n2 = NULL;
  }
  else {
      $total_stat_fac_n2 = "+".$total_stat_fac_n2."%";
      $class10 = 'stat_vert';
  }


$total_stat_pan_n1 = round(((($Panier_total_annee_05 * 100) / $Panier_total_n1_05) - 100),2);

  if ($total_stat_pan_n1 < 0){
      $class11 = 'stat_rouge';
      $total_stat_pan_n1 = $total_stat_pan_n1."%";
      if($total_stat_pan_n1 == -100)
        $total_stat_pan_n1 = NULL;
  }
  else {
      $total_stat_pan_n1 = "+".$total_stat_pan_n1."%";
      $class11 = 'stat_vert';
  }

$total_stat_pan_n2 = round(((($Panier_total_annee_05 * 100) / $Panier_total_n2_05) - 100),2);

  if ($total_stat_pan_n2 < 0){
      $class12 = 'stat_rouge';
      $total_stat_pan_n2 = $total_stat_pan_n2."%";
      if($total_stat_pan_n2 == -100)
        $total_stat_pan_n2 = NULL;
  }
  else {
      $total_stat_pan_n2 = "+".$total_stat_pan_n2."%";
      $class12 = 'stat_vert';
  }


echo "<tr style=\"font-weight:bold;\">
  <td>Total année $year</td>
  <td class=\"ca_nb\">".number_format($Ca_total_annee_05,2,',',' ')." &euro;</td>".
  tdvalue($total_av_ret_n1ht_05,"&euro;",true).
  tdvalue($total_av_ret_n1prcent_05,"%",true).
  tdvalue($total_av_ret_n2ht_05,"&euro;",true).
  tdvalue($total_av_ret_n2prcent_05,"%",true)."
  <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
  <td class=\"ca_nb\">".$Commandes_total_annee_05."</td>
  <td class=\"ca_nb\">".$Commandes_total_n1_05."</td>
  <td class=\"".$class09."\">".$total_stat_avret_comn1."</td>
  <td class=\"".$class9."\">".$total_stat_fac_n1."</td>
  <td class=\"ca_nb\">".$Commandes_total_n2_05."</td>
  <td class=\"".$class10."\">".$total_stat_fac_n2."</td>
  <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
  <td class=\"ca_nb\">".number_format($Panier_total_annee_05,2,',','')."&euro;</td>
  <td class=\"ca_nb\">".number_format($Panier_total_n1_05,2,',','')."&euro;</td>
  <td class=\"".$class11."\">".$total_stat_pan_n1."</td>
  <td class=\"ca_nb\">".number_format($Panier_total_n2_05,2,',','')."&euro;</td>
  <td class=\"".$class12."\">".$total_stat_pan_n2."</td>
</tr>";

echo "</table>";
echo "<br>";
?>
</div>





































<label class="collapse" for="_4">La Redoute</label>
<input id="_4" type="checkbox">
<div>
<?php
/*
LL        AAA    RRRRRR  EEEEEEE DDDDD    OOOOO  UU   UU TTTTTTT EEEEEEE 
LL       AAAAA   RR   RR EE      DD  DD  OO   OO UU   UU   TTT   EE      
LL      AA   AA  RRRRRR  EEEEE   DD   DD OO   OO UU   UU   TTT   EEEEE   
LL      AAAAAAA  RR  RR  EE      DD   DD OO   OO UU   UU   TTT   EE      
LLLLLLL AA   AA  RR   RR EEEEEEE DDDDDD   OOOO0   UUUUU    TTT   EEEEEEE 
                                                                         
*/
echo "<table class=\"ca\">
<tr class=\"ca_head\">
  <td></td>
  <td>Chiffre d'affaires &euro;HT</td>
  <td>Av/ret N-1 &euro;HT</td>
  <td>% N-1</td>
  <td>Av/ret N-2 &euro;HT</td>
  <td>% N-2</td>
  <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-top:1px solid #af824c;\"></td>
  <td>Nbr Factures</td>
  <td>Nbr Factures N-1</td>
  <td>Av/ret N-1</td>
  <td>% N-1</td>
  <td>Nbr Factures N-2</td>
  <td>% N-2</td>
  <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-top:1px solid #af824c;\"></td>
  <td>Panier</td>
  <td>Panier N-1</td>
  <td>% N-1</td>
  <td>Panier N-2</td>
  <td>% N-2</td>
</tr>";

$a = 0;
for($i=1;$i<=12;$i++){

      if($year == $Y)
    {
    if($i<$m){
      $statn00 = $Commandes_annee_06[$a] - $Commandes_n1_06[$a];
      $statn1 = round(((($Commandes_annee_06[$a] * 100) / $Commandes_n1_06[$a]) - 100),2);
      $statn2 = round(((($Commandes_annee_06[$a] * 100) / $Commandes_n2_06[$a]) - 100),2);
    }
      if($i==$m){
        $statn00 = $Commandes_annee_06[$a] - $Com_cur_06_n1[$a];
        $statn1 = round(((($Commandes_annee_06[$a] * 100) / $Com_cur_06_n1[$a]) - 100),2);
        $statn2 = round(((($Commandes_annee_06[$a] * 100) / $Com_cur_06_n2[$a]) - 100),2);
      }
      if ($i > $m){
        $statn00 = -1000000;
        $statn1 = -100;
        $statn2 = -100;
      }
  }

  if($year < $Y)
  {
      $statn00 = $Commandes_annee_06[$a] - $Commandes_n1_06[$a];
      $statn1 = round(((($Commandes_annee_06[$a] * 100) / $Commandes_n1_06[$a]) - 100),2);
      $statn2 = round(((($Commandes_annee_06[$a] * 100) / $Commandes_n2_06[$a]) - 100),2);
 }

  $statn3 = round(((($Panier_annee_06[$a] * 100) / $Panier_n1_06[$a]) - 100),2);
  $statn4 = round(((($Panier_annee_06[$a] * 100) / $Panier_n2_06[$a]) - 100),2);

  if ($statn00 < 0)
  {
    $class00 = 'stat_rouge';
    if ($statn00 == -1000000)
      $statn00 = NULL;
  }
  else {
    $statn00 = "+".$statn00;
    $class00 = 'stat_vert';
  }

  
  if ($statn1 < 0){
      $class = 'stat_rouge';
      $statn1 = $statn1."%";
      if($statn1 == -100)
        $statn1 = NULL;
  }
  else {
      $statn1 = "+".$statn1."%";
      $class = 'stat_vert';
  }


  if ($statn2 < 0){
      $class2 = 'stat_rouge';
      $statn2 = $statn2."%";
      if($statn2 == -100)
        $statn2 = NULL;
  }
  else {
      $statn2 = "+".$statn2."%";
      $class2 = 'stat_vert';
  }

  if ($statn3 < 0){
      $class3 = 'stat_rouge';
      $statn3 = $statn3."%";
      if($statn3 == -100)
        $statn3 = NULL;
  }
  else {
      $statn3 = "+".$statn3."%";
      $class3 = 'stat_vert';
  }

  if ($statn4 < 0){
      $class4 = 'stat_rouge';
      $statn4 = $statn4."%";
      if($statn4 == -100)
        $statn4 = NULL;
  }
  else {
      $statn4 = "+".$statn4."%";
      $class4 = 'stat_vert';
  }

    echo "<tr>
      <td>".$i."-".$year."</td>
      <td class=\"ca_nb\">".number_format($Ca_annee_laredoute_mp[$a],2,',',' ')." &euro;</td>".
      tdvalue($av_ret_n1ht_06[$a],"&euro;",true).
      tdvalue($av_ret_n1prcent_06[$a],"%",false,100).
      tdvalue($av_ret_n2ht_06[$a],"&euro;",true).
      tdvalue($av_ret_n2prcent_06[$a],"%",false,100)."
      <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px;\"></td>
      <td class=\"ca_nb\">".$Commandes_annee_06[$a]."</td>
      <td class=\"ca_nb\">".$Commandes_n1_06[$a]."</td>
      <td class=\"".$class00."\">".$statn00."</td>
      <td class=\"".$class."\">".$statn1."</td>
      <td class=\"ca_nb\">".$Commandes_n2_06[$a]."</td>
      <td class=\"".$class2."\">".$statn2."</td>
      <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px;\"></td>
      <td class=\"ca_nb\">".number_format($Panier_annee_06[$a],2,',',' ')."</td>
      <td class=\"ca_nb\">".number_format($Panier_n1_06[$a],2,',',' ')."</td>
      <td class=\"".$class3."\">".$statn3."</td>
      <td class=\"ca_nb\">".number_format($Panier_n2_06[$a],2,',',' ')."</td>
      <td class=\"".$class4."\">".$statn3."</td>
    </tr>";
   $a++;
}


if ($year == $Y)
{
  $total_stat_fac_n1 = round(((($Commandes_total_annee_06 * 100) / $Com_total_cur_06n1) - 100),2);
  $total_stat_fac_n2 = round(((($Commandes_total_annee_06 * 100) / $Com_total_cur_06n2) - 100),2);
  $total_stat_avret_comn1 = $Commandes_total_annee_06 - $Com_total_cur_06n1;
}
elseif ($year < $Y)
{
  $total_stat_fac_n1 = round(((($Commandes_total_annee_06 * 100) / $Commandes_total_n1_06) - 100),2);
  $total_stat_fac_n2 = round(((($Commandes_total_annee_06 * 100) / $Commandes_total_n2_06) - 100),2);
  $total_stat_avret_comn1 = $Commandes_total_annee_06 - $Commandes_total_n1_06;
}

  if ($total_stat_avret_comn1 < 0)
  {
    $class09 = 'stat_rouge';
  }
  else {
    $total_stat_avret_comn1 = "+".$total_stat_avret_comn1;
    $class09 = 'stat_vert';
  }

  if ($total_stat_fac_n1 < 0){
      $class9 = 'stat_rouge';
      $total_stat_fac_n1 = $total_stat_fac_n1."%";
      if($total_stat_fac_n1 == -100)
        $total_stat_fac_n1 = NULL;
  }
  else {
      $total_stat_fac_n1 = "+".$total_stat_fac_n1."%";
      $class9 = 'stat_vert';
  }

  if ($total_stat_fac_n2 < 0){
      $class10 = 'stat_rouge';
      $total_stat_fac_n2 = $total_stat_fac_n2."%";
      if($total_stat_fac_n2 == -100)
        $total_stat_fac_n2 = NULL;
  }
  else {
      $total_stat_fac_n2 = "+".$total_stat_fac_n2."%";
      $class10 = 'stat_vert';
  }


$total_stat_pan_n1 = round(((($Panier_total_annee_06 * 100) / $Panier_total_n1_06) - 100),2);

  if ($total_stat_pan_n1 < 0){
      $class11 = 'stat_rouge';
      $total_stat_pan_n1 = $total_stat_pan_n1."%";
      if($total_stat_pan_n1 == -100)
        $total_stat_pan_n1 = NULL;
  }
  else {
      $total_stat_pan_n1 = "+".$total_stat_pan_n1."%";
      $class11 = 'stat_vert';
  }

$total_stat_pan_n2 = round(((($Panier_total_annee_06 * 100) / $Panier_total_n2_06) - 100),2);

  if ($total_stat_pan_n2 < 0){
      $class12 = 'stat_rouge';
      $total_stat_pan_n2 = $total_stat_pan_n2."%";
      if($total_stat_pan_n2 == -100)
        $total_stat_pan_n2 = NULL;
  }
  else {
      $total_stat_pan_n2 = "+".$total_stat_pan_n2."%";
      $class12 = 'stat_vert';
  }

echo "<tr style=\"font-weight:bold;\">
  <td>Total année $year</td>
  <td class=\"ca_nb\">".number_format($Ca_total_annee_06,2,',',' ')." &euro;</td>".
  tdvalue($total_av_ret_n1ht_06,"&euro;",true).
  tdvalue($total_av_ret_n1prcent_06,"%",true).
  tdvalue($total_av_ret_n2ht_06,"&euro;",true).
  tdvalue($total_av_ret_n2prcent_06,"%",true)."
  <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
  <td class=\"ca_nb\">".$Commandes_total_annee_06."</td>
  <td class=\"ca_nb\">".$Commandes_total_n1_06."</td>
  <td class=\"".$class09."\">".$total_stat_avret_comn1."</td>
  <td class=\"".$class9."\">".$total_stat_fac_n1."</td>
  <td class=\"ca_nb\">".$Commandes_total_n2_06."</td>
  <td class=\"".$class10."\">".$total_stat_fac_n2."</td>
  <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
  <td class=\"ca_nb\">".number_format($Panier_total_annee_06,2,',','')."&euro;</td>
  <td class=\"ca_nb\">".number_format($Panier_total_n1_06,2,',','')."&euro;</td>
  <td class=\"".$class11."\">".$total_stat_pan_n1."</td>
  <td class=\"ca_nb\">".number_format($Panier_total_n2_06,2,',','')."&euro;</td>
  <td class=\"".$class12."\">".$total_stat_pan_n2."</td>
</tr>";

echo "</table>";
echo "<br>";
?>
</div>











































<label class="collapse" for="_5">Rue Du Commerce</label>
<input id="_5" type="checkbox">
<div>

<?php

/*
RRRRRR  UU   UU EEEEEEE  DDDDD   UU   UU   CCCCC   OOOOO  MM    MM MM    MM EEEEEEE RRRRRR   CCCCC  EEEEEEE 
RR   RR UU   UU EE       DD  DD  UU   UU  CC    C OO   OO MMM  MMM MMM  MMM EE      RR   RR CC    C EE      
RRRRRR  UU   UU EEEEE    DD   DD UU   UU  CC      OO   OO MM MM MM MM MM MM EEEEE   RRRRRR  CC      EEEEE   
RR  RR  UU   UU EE       DD   DD UU   UU  CC    C OO   OO MM    MM MM    MM EE      RR  RR  CC    C EE      
RR   RR  UUUUU  EEEEEEE  DDDDDD   UUUUU    CCCCC   OOOO0  MM    MM MM    MM EEEEEEE RR   RR  CCCCC  EEEEEEE                                                                                                           
*/
echo "<table class=\"ca\">
<tr class=\"ca_head\">
  <td></td>
  <td>Chiffre d'affaires &euro;HT</td>
  <td>Av/ret N-1 &euro;HT</td>
  <td>% N-1</td>
  <td>Av/ret N-2 &euro;HT</td>
  <td>% N-2</td>
  <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-top:1px solid #af824c;\"></td>
  <td>Nbr Factures</td>
  <td>Nbr Factures N-1</td>
  <td>Av/ret N-1</td>
  <td>% N-1</td>
  <td>Nbr Factures N-2</td>
  <td>% N-2</td>
  <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-top:1px solid #af824c;\"></td>
  <td>Panier</td>
  <td>Panier N-1</td>
  <td>% N-1</td>
  <td>Panier N-2</td>
  <td>% N-2</td>
</tr>";

$a = 0;
for($i=1;$i<=12;$i++){

      if($year == $Y)
    {
    if($i<$m){
      $statn00 = $Commandes_annee_07[$a] - $Commandes_n1_07[$a];
      $statn1 = round(((($Commandes_annee_07[$a] * 100) / $Commandes_n1_07[$a]) - 100),2);
      $statn2 = round(((($Commandes_annee_07[$a] * 100) / $Commandes_n2_07[$a]) - 100),2);
    }
      if($i==$m){
        $statn00 = $Commandes_annee_07[$a] - $Com_cur_07_n1[$a];
        $statn1 = round(((($Commandes_annee_07[$a] * 100) / $Com_cur_07_n1[$a]) - 100),2);
        $statn2 = round(((($Commandes_annee_07[$a] * 100) / $Com_cur_07_n2[$a]) - 100),2);
      }
      if ($i > $m){
        $statn00 = -1000000;
        $statn1 = -100;
        $statn2 = -100;
      }
  }

  if($year < $Y)
  {
      $statn00 = $Commandes_annee_07[$a] - $Commandes_n1_07[$a];
      $statn1 = round(((($Commandes_annee_07[$a] * 100) / $Commandes_n1_07[$a]) - 100),2);
      $statn2 = round(((($Commandes_annee_07[$a] * 100) / $Commandes_n2_07[$a]) - 100),2);
 }

  $statn3 = round(((($Panier_annee_07[$a] * 100) / $Panier_n1_07[$a]) - 100),2);
  $statn4 = round(((($Panier_annee_07[$a] * 100) / $Panier_n2_07[$a]) - 100),2);

  if ($statn00 < 0)
  {
    $class00 = 'stat_rouge';
    if ($statn00 == -1000000)
      $statn00 = NULL;
  }
  else {
    $statn00 = "+".$statn00;
    $class00 = 'stat_vert';
  }


  
  if ($statn1 < 0){
      $class = 'stat_rouge';
      $statn1 = $statn1."%";
      if($statn1 == -100)
        $statn1 = NULL;
  }
  else {
      $statn1 = "+".$statn1."%";
      $class = 'stat_vert';
  }


  if ($statn2 < 0){
      $class2 = 'stat_rouge';
      $statn2 = $statn2."%";
      if($statn2 == -100)
        $statn2 = NULL;
  }
  else {
      $statn2 = "+".$statn2."%";
      $class2 = 'stat_vert';
  }




  if ($statn3 < 0){
      $class3 = 'stat_rouge';
      $statn3 = $statn3."%";
      if($statn3 == -100)
        $statn3 = NULL;
  }
  else {
      $statn3 = "+".$statn3."%";
      $class3 = 'stat_vert';
  }


  if ($statn4 < 0){
      $class4 = 'stat_rouge';
      $statn4 = $statn4."%";
      if($statn4 == -100)
        $statn4 = NULL;
  }
  else {
      $statn4 = "+".$statn4."%";
      $class4 = 'stat_vert';
  }

    echo "<tr>
      <td>".$i."-".$year."</td>
      <td class=\"ca_nb\">".number_format($Ca_annee_rueducom_mp[$a],2,',',' ')." &euro;</td>".
      tdvalue($av_ret_n1ht_07[$a],"&euro;",true).
      tdvalue($av_ret_n1prcent_07[$a],"%",false,100).
      tdvalue($av_ret_n2ht_07[$a],"&euro;",true).
      tdvalue($av_ret_n2prcent_07[$a],"%",false,100)."
      <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px;\"></td>
      <td class=\"ca_nb\">".$Commandes_annee_07[$a]."</td>
      <td class=\"ca_nb\">".$Commandes_n1_07[$a]."</td>
      <td class=\"".$class00."\">".$statn00."</td>
      <td class=\"".$class."\">".$statn1."</td>
      <td class=\"ca_nb\">".$Commandes_n2_07[$a]."</td>
      <td class=\"".$class2."\">".$statn2."</td>
      <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px;\"></td>
      <td class=\"ca_nb\">".number_format($Panier_annee_07[$a],2,',',' ')."</td>
      <td class=\"ca_nb\">".number_format($Panier_n1_07[$a],2,',',' ')."</td>
      <td class=\"".$class3."\">".$statn3."</td>
      <td class=\"ca_nb\">".number_format($Panier_n2_07[$a],2,',',' ')."</td>
      <td class=\"".$class4."\">".$statn3."</td>
    </tr>";
   $a++;
}

if ($year == $Y)
{
  $total_stat_fac_n1 = round(((($Commandes_total_annee_07 * 100) / $Com_total_cur_07n1) - 100),2);
  $total_stat_fac_n2 = round(((($Commandes_total_annee_07 * 100) / $Com_total_cur_07n2) - 100),2);
  $total_stat_avret_comn1 = $Commandes_total_annee_07 - $Com_total_cur_07n1;
}
elseif ($year < $Y)
{
  $total_stat_fac_n1 = round(((($Commandes_total_annee_07 * 100) / $Commandes_total_n1_07) - 100),2);
  $total_stat_fac_n2 = round(((($Commandes_total_annee_07 * 100) / $Commandes_total_n2_07) - 100),2);
  $total_stat_avret_comn1 = $Commandes_total_annee_07 - $Commandes_total_n1_07;
}

  if ($total_stat_avret_comn1 < 0)
  {
    $class09 = 'stat_rouge';
  }
  else {
    $total_stat_avret_comn1 = "+".$total_stat_avret_comn1;
    $class09 = 'stat_vert';
  }

  if ($total_stat_fac_n1 < 0){
      $class9 = 'stat_rouge';
      $total_stat_fac_n1 = $total_stat_fac_n1."%";
      if($total_stat_fac_n1 == -100)
        $total_stat_fac_n1 = NULL;
  }
  else {
      $total_stat_fac_n1 = "+".$total_stat_fac_n1."%";
      $class9 = 'stat_vert';
  }

  if ($total_stat_fac_n2 < 0){
      $class10 = 'stat_rouge';
      $total_stat_fac_n2 = $total_stat_fac_n2."%";
      if($total_stat_fac_n2 == -100)
        $total_stat_fac_n2 = NULL;
  }
  else {
      $total_stat_fac_n2 = "+".$total_stat_fac_n2."%";
      $class10 = 'stat_vert';
  }


$total_stat_pan_n1 = round(((($Panier_total_annee_07 * 100) / $Panier_total_n1_07) - 100),2);

  if ($total_stat_pan_n1 < 0){
      $class11 = 'stat_rouge';
      $total_stat_pan_n1 = $total_stat_pan_n1."%";
      if($total_stat_pan_n1 == -100)
        $total_stat_pan_n1 = NULL;
  }
  else {
      $total_stat_pan_n1 = "+".$total_stat_pan_n1."%";
      $class11 = 'stat_vert';
  }

$total_stat_pan_n2 = round(((($Panier_total_annee_07 * 100) / $Panier_total_n2_07) - 100),2);

  if ($total_stat_pan_n2 < 0){
      $class12 = 'stat_rouge';
      $total_stat_pan_n2 = $total_stat_pan_n2."%";
      if($total_stat_pan_n2 == -100)
        $total_stat_pan_n2 = NULL;
  }
  else {
      $total_stat_pan_n2 = "+".$total_stat_pan_n2."%";
      $class12 = 'stat_vert';
  }


echo "<tr style=\"font-weight:bold;\">
  <td>Total année $year</td>
  <td class=\"ca_nb\">".number_format($Ca_total_annee_07,2,',',' ')." &euro;</td>".
  tdvalue($total_av_ret_n1ht_07,"&euro;",true).
  tdvalue($total_av_ret_n1prcent_07,"%",true).
  tdvalue($total_av_ret_n2ht_07,"&euro;",true).
  tdvalue($total_av_ret_n2prcent_07,"%",true)."
  <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
  <td class=\"ca_nb\">".$Commandes_total_annee_07."</td>
  <td class=\"ca_nb\">".$Commandes_total_n1_07."</td>
  <td class=\"".$class09."\">".$total_stat_avret_comn1."</td>
  <td class=\"".$class9."\">".$total_stat_fac_n1."</td>
  <td class=\"ca_nb\">".$Commandes_total_n2_07."</td>
  <td class=\"".$class10."\">".$total_stat_fac_n2."</td>
  <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
  <td class=\"ca_nb\">".number_format($Panier_total_annee_07,2,',','')."&euro;</td>
  <td class=\"ca_nb\">".number_format($Panier_total_n1_07,2,',','')."&euro;</td>
  <td class=\"".$class11."\">".$total_stat_pan_n1."</td>
  <td class=\"ca_nb\">".number_format($Panier_total_n2_07,2,',','')."&euro;</td>
  <td class=\"".$class12."\">".$total_stat_pan_n2."</td>
</tr>";

echo "</table>";
echo "<br>";
?>
</div>
































































<label class="collapse" for="_6">Fnac</label>
<input id="_6" type="checkbox">
<div>

<?php
/*
FFFFFFF NN   NN   AAA    CCCCCC  
FF      NNN  NN  AAAAA  CC     
FFFF    NN N NN AA   AA CC      
FF      NN  NNN AAAAAAA CC     
FF      NN   NN AA   AA  CCCCCC  
*/
echo "<table class=\"ca\">
<tr class=\"ca_head\">
  <td></td>
  <td>Chiffre d'affaires &euro;HT</td>
  <td>Av/ret N-1 &euro;HT</td>
  <td>% N-1</td>
  <td>Av/ret N-2 &euro;HT</td>
  <td>% N-2</td>
  <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-top:1px solid #af824c;\"></td>
  <td>Nbr Factures</td>
  <td>Nbr Factures N-1</td>
  <td>Av/ret N-1</td>
  <td>% N-1</td>
  <td>Nbr Factures N-2</td>
  <td>% N-2</td>
  <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-top:1px solid #af824c;\"></td>
  <td>Panier</td>
  <td>Panier N-1</td>
  <td>% N-1</td>
  <td>Panier N-2</td>
  <td>% N-2</td>
</tr>";

$a = 0;
for($i=1;$i<=12;$i++){ 

      if($year == $Y)
    {
    if($i<$m){
      $statn00 = $Commandes_annee_12[$a] - $Commandes_n1_12[$a];
      $statn1 = round(((($Commandes_annee_12[$a] * 100) / $Commandes_n1_12[$a]) - 100),2);
      $statn2 = round(((($Commandes_annee_12[$a] * 100) / $Commandes_n2_12[$a]) - 100),2);
    }
      if($i==$m){
        $statn00 = $Commandes_annee_12[$a] - $Com_cur_12_n1[$a];
        $statn1 = round(((($Commandes_annee_12[$a] * 100) / $Com_cur_12_n1[$a]) - 100),2);
        $statn2 = round(((($Commandes_annee_12[$a] * 100) / $Com_cur_12_n2[$a]) - 100),2);
      }
      if ($i > $m){
        $statn00 = -1000000;
        $statn1 = -100;
        $statn2 = -100;
      }
  }

  if($year < $Y)
  {
      $statn00 = $Commandes_annee_12[$a] - $Commandes_n1_12[$a];
      $statn1 = round(((($Commandes_annee_12[$a] * 100) / $Commandes_n1_12[$a]) - 100),2);
      $statn2 = round(((($Commandes_annee_12[$a] * 100) / $Commandes_n2_12[$a]) - 100),2);
 }

  $statn3 = round(((($Panier_annee_12[$a] * 100) / $Panier_n1_12[$a]) - 100),2);
  $statn4 = round(((($Panier_annee_12[$a] * 100) / $Panier_n2_12[$a]) - 100),2);

  if ($statn00 < 0)
  {
    $class00 = 'stat_rouge';
    if ($statn00 == -1000000)
      $statn00 = NULL;
  }
  else {
    $statn00 = "+".$statn00;
    $class00 = 'stat_vert';
  }

  if ($statn1 < 0){
      $class = 'stat_rouge';
      $statn1 = $statn1."%";
      if($statn1 == -100)
        $statn1 = NULL;
  }
  else {
      $statn1 = "+".$statn1."%";
      $class = 'stat_vert';
  }


  if ($statn2 < 0){
      $class2 = 'stat_rouge';
      $statn2 = $statn2."%";
      if($statn2 == -100)
        $statn2 = NULL;
  }
  else {
      $statn2 = "+".$statn2."%";
      $class2 = 'stat_vert';
  }

  if ($statn3 < 0){
      $class3 = 'stat_rouge';
      $statn3 = $statn3."%";
      if($statn3 == -100)
        $statn3 = NULL;
  }
  else {
      $statn3 = "+".$statn3."%";
      $class3 = 'stat_vert';
  }


  if ($statn4 < 0){
      $class4 = 'stat_rouge';
      $statn4 = $statn4."%";
      if($statn4 == -100)
        $statn4 = NULL;
  }
  else {
      $statn4 = "+".$statn4."%";
      $class4 = 'stat_vert';
  }

    echo "<tr>
      <td>".$i."-".$year."</td>
      <td class=\"ca_nb\">".number_format($Ca_annee_fnac_mp[$a],2,',',' ')." &euro;</td>".
      tdvalue($av_ret_n1ht_12[$a],"&euro;",true).
      tdvalue($av_ret_n1prcent_12[$a],"%",false,100).
      tdvalue($av_ret_n2ht_12[$a],"&euro;",true).
      tdvalue($av_ret_n2prcent_12[$a],"%",false,100)."
      <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px;\"></td>
      <td class=\"ca_nb\">".$Commandes_annee_12[$a]."</td>
      <td class=\"ca_nb\">".$Commandes_n1_12[$a]."</td>
      <td class=\"".$class00."\">".$statn00."</td>
      <td class=\"".$class."\">".$statn1."</td>
      <td class=\"ca_nb\">".$Commandes_n2_12[$a]."</td>
      <td class=\"".$class2."\">".$statn2."</td>
      <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px;\"></td>
      <td class=\"ca_nb\">".number_format($Panier_annee_12[$a],2,',',' ')."</td>
      <td class=\"ca_nb\">".number_format($Panier_n1_12[$a],2,',',' ')."</td>
      <td class=\"".$class3."\">".$statn3."</td>
      <td class=\"ca_nb\">".number_format($Panier_n2_12[$a],2,',',' ')."</td>
      <td class=\"".$class4."\">".$statn3."</td>
    </tr>";
   $a++;
}

if ($year == $Y)
{
  $total_stat_fac_n1 = round(((($Commandes_total_annee_12 * 100) / $Com_total_cur_12n1) - 100),2);
  $total_stat_fac_n2 = round(((($Commandes_total_annee_12 * 100) / $Com_total_cur_12n2) - 100),2);
  $total_stat_avret_comn1 = $Commandes_total_annee_12 - $Com_total_cur_12n1;
}
elseif ($year < $Y)
{
  $total_stat_fac_n1 = round(((($Commandes_total_annee_12 * 100) / $Commandes_total_n1_12) - 100),2);
  $total_stat_fac_n2 = round(((($Commandes_total_annee_12 * 100) / $Commandes_total_n2_12) - 100),2);
  $total_stat_avret_comn1 = $Commandes_total_annee_12 - $Commandes_total_n1_12;
}

  if ($total_stat_avret_comn1 < 0)
  {
    $class09 = 'stat_rouge';
  }
  else {
    $total_stat_avret_comn1 = "+".$total_stat_avret_comn1;
    $class09 = 'stat_vert';
  }


  if ($total_stat_fac_n1 < 0){
      $class9 = 'stat_rouge';
      $total_stat_fac_n1 = $total_stat_fac_n1."%";
      if($total_stat_fac_n1 == -100)
        $total_stat_fac_n1 = NULL;
  }
  else {
      $total_stat_fac_n1 = "+".$total_stat_fac_n1."%";
      $class9 = 'stat_vert';
  }

  if ($total_stat_fac_n2 < 0){
      $class10 = 'stat_rouge';
      $total_stat_fac_n2 = $total_stat_fac_n2."%";
      if($total_stat_fac_n2 == -100)
        $total_stat_fac_n2 = NULL;
  }
  else {
      $total_stat_fac_n2 = "+".$total_stat_fac_n2."%";
      $class10 = 'stat_vert';
  }

$total_stat_pan_n1 = round(((($Panier_total_annee_12 * 100) / $Panier_total_n1_12) - 100),2);

  if ($total_stat_pan_n1 < 0){
      $class11 = 'stat_rouge';
      $total_stat_pan_n1 = $total_stat_pan_n1."%";
      if($total_stat_pan_n1 == -100)
        $total_stat_pan_n1 = NULL;
  }
  else {
      $total_stat_pan_n1 = "+".$total_stat_pan_n1."%";
      $class11 = 'stat_vert';
  }

$total_stat_pan_n2 = round(((($Panier_total_annee_12 * 100) / $Panier_total_n2_12) - 100),2);

  if ($total_stat_pan_n2 < 0){
      $class12 = 'stat_rouge';
      $total_stat_pan_n2 = $total_stat_pan_n2."%";
      if($total_stat_pan_n2 == -100)
        $total_stat_pan_n2 = NULL;
  }
  else {
      $total_stat_pan_n2 = "+".$total_stat_pan_n2."%";
      $class12 = 'stat_vert';
  }


echo "<tr style=\"font-weight:bold;\">
  <td>Total année $year</td>
  <td class=\"ca_nb\">".number_format($Ca_total_annee_12,2,',',' ')." &euro;</td>".
  tdvalue($total_av_ret_n1ht_12,"&euro;",true).
  tdvalue($total_av_ret_n1prcent_12,"%",true).
  tdvalue($total_av_ret_n2ht_12,"&euro;",true).
  tdvalue($total_av_ret_n2prcent_12,"%",true)."
  <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
  <td class=\"ca_nb\">".$Commandes_total_annee_12."</td>
  <td class=\"ca_nb\">".$Commandes_total_n1_12."</td>
  <td class=\"".$class09."\">".$total_stat_avret_comn1."</td>
  <td class=\"".$class9."\">".$total_stat_fac_n1."</td>
  <td class=\"ca_nb\">".$Commandes_total_n2_12."</td>
  <td class=\"".$class10."\">".$total_stat_fac_n2."</td>
  <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
  <td class=\"ca_nb\">".number_format($Panier_total_annee_12,2,',','')."&euro;</td>
  <td class=\"ca_nb\">".number_format($Panier_total_n1_12,2,',','')."&euro;</td>
  <td class=\"".$class11."\">".$total_stat_pan_n1."</td>
  <td class=\"ca_nb\">".number_format($Panier_total_n2_12,2,',','')."&euro;</td>
  <td class=\"".$class12."\">".$total_stat_pan_n2."</td>
</tr>";

echo "</table>";
echo "<br>";
?>
</div>









































<label class="collapse" for="_7">CDiscount</label>
<input id="_7" type="checkbox">
<div>


<?php
/*
 CCCCC  DDDDD   IIIII  SSSSS   CCCCC   OOOOO  UU   UU NN   NN TTTTTTT 
CC      DD  DD   III  SS      CC      OO   OO UU   UU NNN  NN   TTT   
CC      DD   DD  III   SSSSS  CC      OO   OO UU   UU NN N NN   TTT   
CC      DD   DD  III       SS CC      OO   OO UU   UU NN  NNN   TTT   
 CCCCC  DDDDDD  IIIII  SSSSS   CCCCC   OOOO0   UUUUU  NN   NN   TTT                                                                         
*/
echo "<table class=\"ca\">
<tr class=\"ca_head\">
  <td></td>
  <td>Chiffre d'affaires &euro;HT</td>
  <td>Av/ret N-1 &euro;HT</td>
  <td>% N-1</td>
  <td>Av/ret N-2 &euro;HT</td>
  <td>% N-2</td>
  <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-top:1px solid #af824c;\"></td>
  <td>Nbr Factures</td>
  <td>Nbr Factures N-1</td>
  <td>Av/ret N-1</td>
  <td>% N-1</td>
  <td>Nbr Factures N-2</td>
  <td>% N-2</td>
  <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-top:1px solid #af824c;\"></td>
  <td>Panier</td>
  <td>Panier N-1</td>
  <td>% N-1</td>
  <td>Panier N-2</td>
  <td>% N-2</td>
</tr>";

$a = 0;
for($i=1;$i<=12;$i++){

      if($year == $Y)
    {
    if($i<$m){
      $statn00 = $Commandes_annee_13[$a] - $Commandes_n1_13[$a];
      $statn1 = round(((($Commandes_annee_13[$a] * 100) / $Commandes_n1_13[$a]) - 100),2);
      $statn2 = round(((($Commandes_annee_13[$a] * 100) / $Commandes_n2_13[$a]) - 100),2);
    }
      if($i==$m){
        $statn00 = $Commandes_annee_13[$a] - $Com_cur_13_n1[$a];
        $statn1 = round(((($Commandes_annee_13[$a] * 100) / $Com_cur_13_n1[$a]) - 100),2);
        $statn2 = round(((($Commandes_annee_13[$a] * 100) / $Com_cur_13_n2[$a]) - 100),2);
      }
      if ($i > $m){
        $statn00 = -1000000;
        $statn1 = -100;
        $statn2 = -100;
      }
  }

  if($year < $Y)
  {
      $statn00 = $Commandes_annee_13[$a] - $Commandes_n1_13[$a];
      $statn1 = round(((($Commandes_annee_13[$a] * 100) / $Commandes_n1_13[$a]) - 100),2);
      $statn2 = round(((($Commandes_annee_13[$a] * 100) / $Commandes_n2_13[$a]) - 100),2);
 }

  $statn3 = round(((($Panier_annee_13[$a] * 100) / $Panier_n1_13[$a]) - 100),2);
  $statn4 = round(((($Panier_annee_13[$a] * 100) / $Panier_n2_13[$a]) - 100),2);

  if ($statn00 < 0)
  {
    $class00 = 'stat_rouge';
    if ($statn00 == -1000000)
      $statn00 = NULL;
  }
  else {
    $statn00 = "+".$statn00;
    $class00 = 'stat_vert';
  }

  if ($statn1 < 0){
      $class = 'stat_rouge';
      $statn1 = $statn1."%";
      if($statn1 == -100)
        $statn1 = NULL;
  }
  else {
      $statn1 = "+".$statn1."%";
      $class = 'stat_vert';
  }


  if ($statn2 < 0){
      $class2 = 'stat_rouge';
      $statn2 = $statn2."%";
      if($statn2 == -100)
        $statn2 = NULL;
  }
  else {
      $statn2 = "+".$statn2."%";
      $class2 = 'stat_vert';
  }


  if ($statn3 < 0){
      $class3 = 'stat_rouge';
      $statn3 = $statn3."%";
      if($statn3 == -100)
        $statn3 = NULL;
  }
  else {
      $statn3 = "+".$statn3."%";
      $class3 = 'stat_vert';
  }


  if ($statn4 < 0){
      $class4 = 'stat_rouge';
      $statn4 = $statn4."%";
      if($statn4 == -100)
        $statn4 = NULL;
  }
  else {
      $statn4 = "+".$statn4."%";
      $class4 = 'stat_vert';
  }


    echo "<tr>
      <td>".$i."-".$year."</td>
      <td class=\"ca_nb\">".number_format($Ca_annee_cdiscount_mp[$a],2,',',' ')." &euro;</td>".
      tdvalue($av_ret_n1ht_13[$a],"&euro;",true).
      tdvalue($av_ret_n1prcent_13[$a],"%",false,100).
      tdvalue($av_ret_n2ht_13[$a],"&euro;",true).
      tdvalue($av_ret_n2prcent_13[$a],"%",false,100)."
      <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px;\"></td>
      <td class=\"ca_nb\">".$Commandes_annee_13[$a]."</td>
      <td class=\"ca_nb\">".$Commandes_n1_13[$a]."</td>
      <td class=\"".$class00."\">".$statn00."</td>
      <td class=\"".$class."\">".$statn1."</td>
      <td class=\"ca_nb\">".$Commandes_n2_13[$a]."</td>
      <td class=\"".$class2."\">".$statn2."</td>
      <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px;\"></td>
      <td class=\"ca_nb\">".number_format($Panier_annee_13[$a],2,',',' ')."</td>
      <td class=\"ca_nb\">".number_format($Panier_n1_13[$a],2,',',' ')."</td>
      <td class=\"".$class3."\">".$statn3."</td>
      <td class=\"ca_nb\">".number_format($Panier_n2_13[$a],2,',',' ')."</td>
      <td class=\"".$class4."\">".$statn3."</td>
    </tr>";
   $a++;
}

if ($year == $Y)
{
  $total_stat_fac_n1 = round(((($Commandes_total_annee_13 * 100) / $Com_total_cur_13n1) - 100),2);
  $total_stat_fac_n2 = round(((($Commandes_total_annee_13 * 100) / $Com_total_cur_13n2) - 100),2);
  $total_stat_avret_comn1 = $Commandes_total_annee_13 - $Com_total_cur_13n1;
}
elseif ($year < $Y)
{
  $total_stat_fac_n1 = round(((($Commandes_total_annee_13 * 100) / $Commandes_total_n1_13) - 100),2);
  $total_stat_fac_n2 = round(((($Commandes_total_annee_13 * 100) / $Commandes_total_n2_13) - 100),2);
  $total_stat_avret_comn1 = $Commandes_total_annee_13 - $Commandes_total_n1_13;
}

  if ($total_stat_avret_comn1 < 0)
  {
    $class09 = 'stat_rouge';
  }
  else {
    $total_stat_avret_comn1 = "+".$total_stat_avret_comn1;
    $class09 = 'stat_vert';
  }

  if ($total_stat_fac_n1 < 0){
      $class9 = 'stat_rouge';
      $total_stat_fac_n1 = $total_stat_fac_n1."%";
      if($total_stat_fac_n1 == -100)
        $total_stat_fac_n1 = NULL;
  }
  else {
      $total_stat_fac_n1 = "+".$total_stat_fac_n1."%";
      $class9 = 'stat_vert';
  }

  if ($total_stat_fac_n2 < 0){
      $class10 = 'stat_rouge';
      $total_stat_fac_n2 = $total_stat_fac_n2."%";
      if($total_stat_fac_n2 == -100)
        $total_stat_fac_n2 = NULL;
  }
  else {
      $total_stat_fac_n2 = "+".$total_stat_fac_n2."%";
      $class10 = 'stat_vert';
  }

$total_stat_pan_n1 = round(((($Panier_total_annee_13 * 100) / $Panier_total_n1_13) - 100),2);

  if ($total_stat_pan_n1 < 0){
      $class11 = 'stat_rouge';
      $total_stat_pan_n1 = $total_stat_pan_n1."%";
      if($total_stat_pan_n1 == -100)
        $total_stat_pan_n1 = NULL;
  }
  else {
      $total_stat_pan_n1 = "+".$total_stat_pan_n1."%";
      $class11 = 'stat_vert';
  }

$total_stat_pan_n2 = round(((($Panier_total_annee_13 * 100) / $Panier_total_n2_13) - 100),2);

  if ($total_stat_pan_n2 < 0){
      $class12 = 'stat_rouge';
      $total_stat_pan_n2 = $total_stat_pan_n2."%";
      if($total_stat_pan_n2 == -100)
        $total_stat_pan_n2 = NULL;
  }
  else {
      $total_stat_pan_n2 = "+".$total_stat_pan_n2."%";
      $class12 = 'stat_vert';
  }

echo "<tr style=\"font-weight:bold;\">
  <td>Total année $year</td>
  <td class=\"ca_nb\">".number_format($Ca_total_annee_13,2,',',' ')." &euro;</td>".
  tdvalue($total_av_ret_n1ht_13,"&euro;",true).
  tdvalue($total_av_ret_n1prcent_13,"%",true).
  tdvalue($total_av_ret_n2ht_13,"&euro;",true).
  tdvalue($total_av_ret_n2prcent_13,"%",true)."
  <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
  <td class=\"ca_nb\">".$Commandes_total_annee_13."</td>
  <td class=\"ca_nb\">".$Commandes_total_n1_13."</td>
  <td class=\"".$class09."\">".$total_stat_avret_comn1."</td>
  <td class=\"".$class9."\">".$total_stat_fac_n1."</td>
  <td class=\"ca_nb\">".$Commandes_total_n2_13."</td>
  <td class=\"".$class10."\">".$total_stat_fac_n2."</td>
  <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
  <td class=\"ca_nb\">".number_format($Panier_total_annee_13,2,',','')."&euro;</td>
  <td class=\"ca_nb\">".number_format($Panier_total_n1_13,2,',','')."&euro;</td>
  <td class=\"".$class11."\">".$total_stat_pan_n1."</td>
  <td class=\"ca_nb\">".number_format($Panier_total_n2_13,2,',','')."&euro;</td>
  <td class=\"".$class12."\">".$total_stat_pan_n2."</td>
</tr>";

echo "</table>";
?>
</div>

<?php


echo "<br>";
echo "<br>";
echo "<br>";


include "./footer.php";

?>