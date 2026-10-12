<?php
require "../admin_global.php";
require "functions_markdetailsite.php";
include "../header.php";
include "menu_2.php";
include "../../class/facture.class.php";

ini_set("display_errors",0);



echo "
<style>
.stat_rouge {color:red; text-align: right;}
.stat_vert {color:green; text-align: right;}
.ca { color:black; border-collapse:collapse; border:solid thin black; width:100%;}
.ca td { border:solid thin black; }
.ca_nb { text-align:right; white-space:nowrap; }
.ca_prc { text-align:center; white-space:nowrap; }
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



<h1>Détails sites enregistrés</h1>";?>



<?php
include(BACK_PATH.'/statistiques/statistiques_adm.php');


// GESTION DE L'ANNEE
if(!isset($year)) $year=date("Y");
echo "<p style=\"margin-top:40px; margin-bottom:15px;\"align=\"center\">Année : <select name=\"year\" onchange=\"document.location='mark_ca_detailsite.php?year='+this.value\">";
for($y=2010;$y<=(2009+count($objectif_facture));$y++) echo "<option value=\"$y\"".($y==$year?" selected":"").">$y</option>";



echo "</select></p>
<table class=\"ca\">
<tr><td colspan=\"14\" class=\"ca_second_title\">Détail - Ma cave a vin.fr</td></tr>
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
  </tr>";



$listfacture=array();
$Y=date("Y");
$m=date("n");
$timeday=date("t");

$yn1 = $year - 1;
$yn2 = $year - 2;





//FONCTION OBJ_MONTANT

$obj_mcvfr = obj_montant($year, 1);
$obj_mwccom = obj_montant($year, 2);
$obj_mwcuk = obj_montant($year, 3);
$obj_marketplace = obj_montant($year, 4);
$obj_mvinoes = obj_montant($year, 5);

$total_obj_mcvfr = $obj_mcvfr[0] + $obj_mcvfr[1] + $obj_mcvfr[2] + $obj_mcvfr[3] + $obj_mcvfr[4] + $obj_mcvfr[5] + $obj_mcvfr[6] + $obj_mcvfr[7] + $obj_mcvfr[8] + $obj_mcvfr[9] + $obj_mcvfr[10] + $obj_mcvfr[11];
$total_obj_mwccom = $obj_mwccom[0] + $obj_mwccom[1] + $obj_mwccom[2] + $obj_mwccom[3] + $obj_mwccom[4] + $obj_mwccom[5] + $obj_mwccom[6] + $obj_mwccom[7] + $obj_mwccom[8] + $obj_mwccom[9] + $obj_mwccom[10] + $obj_mwccom[11];
$total_obj_mwcuk = $obj_mwcuk[0] + $obj_mwcuk[0] + $obj_mwcuk[1] + $obj_mwcuk[2] + $obj_mwcuk[3] + $obj_mwcuk[4] + $obj_mwcuk[5] + $obj_mwcuk[6] + $obj_mwcuk[7] + $obj_mwcuk[8] + $obj_mwcuk[9] + $obj_mwcuk[10] + $obj_mwcuk[11];
$total_obj_marketplace = $obj_marketplace[0] + $obj_marketplace[1] + $obj_marketplace[2] + $obj_marketplace[3] + $obj_marketplace[4] + $obj_marketplace[5] + $obj_marketplace[6] + $obj_marketplace[7] + $obj_marketplace[8] + $obj_marketplace[9] + $obj_marketplace[10] + $obj_marketplace[11];
$total_obj_mvinoes = $obj_mvinoes[0] + $obj_mvinoes[1] + $obj_mvinoes[2] + $obj_mvinoes[3] + $obj_mvinoes[4] + $obj_mvinoes[5] + $obj_mvinoes[6] + $obj_mvinoes[7] + $obj_mvinoes[8] + $obj_mvinoes[9] + $obj_mvinoes[10] + $obj_mvinoes[11];







//FONCTION CA_MCVF_MWCOM


$compteur = 0;
$i = 1;
while ($compteur <= 12)
{
  $CA_annee_cav[$compteur] = ca_mcvf_mwcom_old($i, $year, "1,5,7,8", "", "","", "FR");
  $CA_annee_com[$compteur] = ca_mcvf_mwcom_old($i, $year, "1,5,7,8", "", "","", "EN");
  $CA_annee_uk[$compteur] = ca_uk_old($i, $year, "1,5,7,8", "", "", "");
  $CA_n1_cav[$compteur] = ca_mcvf_mwcom_old($i, $yn1, "1,5,7,8", "", "", "", "FR");
  $CA_n1_com[$compteur] = ca_mcvf_mwcom_old($i, $yn1, "1,5,7,8", "", "", "", "EN");
  $CA_n2_cav[$compteur] = ca_mcvf_mwcom_old($i, $yn2, "1,5,7,8", "", "", "", "FR");
  $CA_n2_com[$compteur] = ca_mcvf_mwcom_old($i, $yn2, "1,5,7,8", "", "", "", "EN");
  $CA_n1_uk[$compteur] = ca_uk_old($i, $yn1, "1,5,7,8", "", "", "");
  $CA_n2_uk[$compteur] = ca_uk_old($i, $yn2, "1,5,7,8", "", "", "");

  $compteur++;
  $i++;
}

//$CA_annee_cav = ca_mcvf_mwcom($year, "1,5,7,8", "", "","FR");
//$CA_annee_com = ca_mcvf_mwcom($year, "1,5,7,8", "", "", "EN");
$CA_total_annee_cav = $CA_annee_cav[0] + $CA_annee_cav[1] + $CA_annee_cav[2] + $CA_annee_cav[3] + $CA_annee_cav[4] + $CA_annee_cav[5] + $CA_annee_cav[6] + $CA_annee_cav[7] + $CA_annee_cav[8] + $CA_annee_cav[9] + $CA_annee_cav[10] + $CA_annee_cav[11]; 
$CA_total_annee_com = $CA_annee_com[0] + $CA_annee_com[1] + $CA_annee_com[2] + $CA_annee_com[3] + $CA_annee_com[4] + $CA_annee_com[5] + $CA_annee_com[6] + $CA_annee_com[7] + $CA_annee_com[8] + $CA_annee_com[9] + $CA_annee_com[10] + $CA_annee_com[11];
$total_prcent_ca_cav = ($CA_total_annee_cav * 100) / $total_obj_mcvfr;
$total_prcent_ca_com = ($CA_total_annee_com * 100) / $total_obj_mwccom;
/*$CA_n1_cav = ca_mcvf_mwcom($yn1, "1,5,7,8", "", "","FR");
$CA_n1_com = ca_mcvf_mwcom($yn1, "1,5,7,8", "", "","EN");
$CA_n2_cav = ca_mcvf_mwcom($yn2, "1,5,7,8", "", "","FR");
$CA_n2_com = ca_mcvf_mwcom($yn2, "1,5,7,8", "", "","EN");*/





//FONCTION CA_UK

//$CA_annee_uk = ca_uk($year, "1,5,7,8", "", "");
$CA_total_annee_uk = $CA_annee_uk[0] + $CA_annee_uk[1] + $CA_annee_uk[2] + $CA_annee_uk[3] + $CA_annee_uk[4] + $CA_annee_uk[5] + $CA_annee_uk[6] + $CA_annee_uk[7] + $CA_annee_uk[8] + $CA_annee_uk[9] + $CA_annee_uk[10] + $CA_annee_uk[11];
$total_prcent_ca_uk = ($CA_total_annee_uk * 100) / $total_obj_mwcuk;
/*$CA_n1_uk = ca_uk($yn1, "1,5,7,8", "", "");
$CA_n2_uk = ca_uk($yn2, "1,5,7,8", "", "");*/





if ($year < $Y)
{
  $obj_mois_prcent_cav = obj_mois_f($obj_mcvfr, $CA_annee_cav);
  $av_ret_objht_cav = av_ret_obj_f($obj_mcvfr, $CA_annee_cav);
  $av_ret_objprcent_cav = av_ret_objprcent_f($obj_mcvfr, $CA_annee_cav);
  $av_ret_n1ht_cav = av_ret_obj_f($CA_n1_cav, $CA_annee_cav);
  $av_ret_n1prcent_cav = obj_mois_f_n($CA_n1_cav, $CA_annee_cav);
  $av_ret_n2ht_cav = av_ret_obj_f($CA_n2_cav, $CA_annee_cav);
  $av_ret_n2prcent_cav = obj_mois_f_n($CA_n2_cav, $CA_annee_cav);


  $obj_mois_prcent_com = obj_mois_f($obj_mwccom, $CA_annee_com);
  $av_ret_objht_com = av_ret_obj_f($obj_mwccom, $CA_annee_com);
  $av_ret_objprcent_com = av_ret_objprcent_f($obj_mwccom, $CA_annee_com);
  $av_ret_n1ht_com = av_ret_obj_f($CA_n1_com, $CA_annee_com);
  $av_ret_n1prcent_com = obj_mois_f_n($CA_n1_com, $CA_annee_com);
  $av_ret_n2ht_com = av_ret_obj_f($CA_n2_com, $CA_annee_com);
  $av_ret_n2prcent_com = obj_mois_f_n($CA_n2_com, $CA_annee_com);


  $obj_mois_prcent_uk = obj_mois_f($obj_mwcuk, $CA_annee_uk);
  $av_ret_objht_uk = av_ret_obj_f($obj_mwcuk, $CA_annee_uk);
  $av_ret_objprcent_uk = av_ret_objprcent_f($obj_mwcuk, $CA_annee_uk);
  $av_ret_n1ht_uk = av_ret_obj_f($CA_n1_uk, $CA_annee_uk);
  $av_ret_n1prcent_uk = obj_mois_f_n($CA_n1_uk, $CA_annee_uk);
  $av_ret_n2ht_uk = av_ret_obj_f($CA_n2_uk, $CA_annee_uk);
  $av_ret_n2prcent_uk = obj_mois_f_n($CA_n2_uk, $CA_annee_uk);
}
elseif ($year == $Y)
{
  $obj_mois_prcent_cav = obj_mois_f($obj_mcvfr, $CA_annee_cav);
  $av_ret_objht_cav = av_ret_obj_fmois($obj_mcvfr, $CA_annee_cav, $m, $timeday);
  $av_ret_objprcent_cav = av_ret_objprcent_fmois($obj_mcvfr, $CA_annee_cav, $m, $timeday);
  $av_ret_n1ht_cav = av_ret_obj_fmois($CA_n1_cav, $CA_annee_cav, $m, $timeday);
  $av_ret_n1prcent_cav = obj_mois_fmois_n($CA_n1_cav, $CA_annee_cav, $m, $timeday);
  $av_ret_n2ht_cav = av_ret_obj_fmois($CA_n2_cav, $CA_annee_cav, $m, $timeday);
  $av_ret_n2prcent_cav = obj_mois_fmois_n($CA_n2_cav, $CA_annee_cav, $m, $timeday);


  $obj_mois_prcent_com = obj_mois_f($obj_mwccom, $CA_annee_com);
  $av_ret_objht_com = av_ret_obj_fmois($obj_mwccom, $CA_annee_com, $m, $timeday);
  $av_ret_objprcent_com = av_ret_objprcent_fmois($obj_mwccom, $CA_annee_com, $m, $timeday);
  $av_ret_n1ht_com = av_ret_obj_fmois($CA_n1_com, $CA_annee_com, $m, $timeday);
  $av_ret_n1prcent_com = obj_mois_fmois_n($CA_n1_com, $CA_annee_com, $m, $timeday);
  $av_ret_n2ht_com = av_ret_obj_fmois($CA_n2_com, $CA_annee_com, $m, $timeday);
  $av_ret_n2prcent_com = obj_mois_fmois_n($CA_n2_com, $CA_annee_com, $m, $timeday);


  $obj_mois_prcent_uk = obj_mois_f($obj_mwcuk, $CA_annee_uk);
  $av_ret_objht_uk = av_ret_obj_fmois($obj_mwcuk, $CA_annee_uk, $m, $timeday);
  $av_ret_objprcent_uk = av_ret_objprcent_fmois($obj_mwcuk, $CA_annee_uk, $m, $timeday);
  $av_ret_n1ht_uk = av_ret_obj_fmois($CA_n1_uk, $CA_annee_uk, $m, $timeday);
  $av_ret_n1prcent_uk = obj_mois_fmois_n($CA_n1_uk, $CA_annee_uk, $m, $timeday);
  $av_ret_n2ht_uk = av_ret_obj_fmois($CA_n2_uk, $CA_annee_uk, $m, $timeday);
  $av_ret_n2prcent_uk = obj_mois_fmois_n($CA_n2_uk, $CA_annee_uk, $m, $timeday);
}



$total_av_ret_objht_cav = $av_ret_objht_cav[0] + $av_ret_objht_cav[1] + $av_ret_objht_cav[2] + $av_ret_objht_cav[3] + $av_ret_objht_cav[4] + $av_ret_objht_cav[5] + $av_ret_objht_cav[6] + $av_ret_objht_cav[7] + $av_ret_objht_cav[8] + $av_ret_objht_cav[9] + $av_ret_objht_cav[10] + $av_ret_objht_cav[11];
$total_av_ret_n1ht_cav = $av_ret_n1ht_cav[0] + $av_ret_n1ht_cav[1] + $av_ret_n1ht_cav[2] + $av_ret_n1ht_cav[3] + $av_ret_n1ht_cav[4] + $av_ret_n1ht_cav[5] + $av_ret_n1ht_cav[6] + $av_ret_n1ht_cav[7] + $av_ret_n1ht_cav[8] + $av_ret_n1ht_cav[9] + $av_ret_n1ht_cav[10] + $av_ret_n1ht_cav[11];
$total_av_ret_n2ht_cav = $av_ret_n2ht_cav[0] + $av_ret_n2ht_cav[1] + $av_ret_n2ht_cav[2] + $av_ret_n2ht_cav[3] + $av_ret_n2ht_cav[4] + $av_ret_n2ht_cav[5] + $av_ret_n2ht_cav[6] + $av_ret_n2ht_cav[7] + $av_ret_n2ht_cav[8] + $av_ret_n2ht_cav[9] + $av_ret_n2ht_cav[10] + $av_ret_n2ht_cav[11];

$total_av_ret_objht_com = $av_ret_objht_com[0] + $av_ret_objht_com[1] + $av_ret_objht_com[2] + $av_ret_objht_com[3] + $av_ret_objht_com[4] + $av_ret_objht_com[5] + $av_ret_objht_com[6] + $av_ret_objht_com[7] + $av_ret_objht_com[8] + $av_ret_objht_com[9] + $av_ret_objht_com[10] + $av_ret_objht_com[11];
$total_av_ret_n1ht_com = $av_ret_n1ht_com[0] + $av_ret_n1ht_com[1] + $av_ret_n1ht_com[2] + $av_ret_n1ht_com[3] + $av_ret_n1ht_com[4] + $av_ret_n1ht_com[5] + $av_ret_n1ht_com[6] + $av_ret_n1ht_com[7] + $av_ret_n1ht_com[8] + $av_ret_n1ht_com[9] + $av_ret_n1ht_com[10] + $av_ret_n1ht_com[11];
$total_av_ret_n2ht_com = $av_ret_n2ht_com[0] + $av_ret_n2ht_com[1] + $av_ret_n2ht_com[2] + $av_ret_n2ht_com[3] + $av_ret_n2ht_com[4] + $av_ret_n2ht_com[5] + $av_ret_n2ht_com[6] + $av_ret_n2ht_com[7] + $av_ret_n2ht_com[8] + $av_ret_n2ht_com[9] + $av_ret_n2ht_com[10] + $av_ret_n2ht_com[11];

$total_av_ret_objht_uk = $av_ret_objht_uk[0] + $av_ret_objht_uk[1] + $av_ret_objht_uk[2] + $av_ret_objht_uk[3] + $av_ret_objht_uk[4] + $av_ret_objht_uk[5] + $av_ret_objht_uk[6] + $av_ret_objht_uk[7] + $av_ret_objht_uk[8] + $av_ret_objht_uk[9] + $av_ret_objht_uk[10] + $av_ret_objht_uk[11];
$total_av_ret_n1ht_uk = $av_ret_n1ht_uk[0] + $av_ret_n1ht_uk[1] + $av_ret_n1ht_uk[2] + $av_ret_n1ht_uk[3] + $av_ret_n1ht_uk[4] + $av_ret_n1ht_uk[5] + $av_ret_n1ht_uk[6] + $av_ret_n1ht_uk[7] + $av_ret_n1ht_uk[8] + $av_ret_n1ht_uk[9] + $av_ret_n1ht_uk[10] + $av_ret_n1ht_uk[11];
$total_av_ret_n2ht_uk = $av_ret_n2ht_uk[0] + $av_ret_n2ht_uk[1] + $av_ret_n2ht_uk[2] + $av_ret_n2ht_uk[3] + $av_ret_n2ht_uk[4] + $av_ret_n2ht_uk[5] + $av_ret_n2ht_uk[6] + $av_ret_n2ht_uk[7] + $av_ret_n2ht_uk[8] + $av_ret_n2ht_uk[9] + $av_ret_n2ht_uk[10] + $av_ret_n2ht_uk[11];




if ($year < $Y)
{
    $total_av_ret_objprcent_cav = ($total_av_ret_objht_cav * 100) / $total_obj_mcvfr;
    $total_av_ret_n1prcent_cav = ($total_av_ret_n1ht_cav * 100) / $total_obj_mcvfr;
    $total_av_ret_n2prcent_cav = ($total_av_ret_n2ht_cav * 100) / $total_obj_mcvfr;

    $total_av_ret_objprcent_com = ($total_av_ret_objht_com * 100) / $total_obj_mwccom;
    $total_av_ret_n1prcent_com = ($total_av_ret_n1ht_com * 100) / $total_obj_mwccom;
    $total_av_ret_n2prcent_com = ($total_av_ret_n2ht_com * 100) / $total_obj_mwccom;

    $total_av_ret_objprcent_uk = ($total_av_ret_objht_uk * 100) / $total_obj_mwcuk;
    $total_av_ret_n1prcent_uk = ($total_av_ret_n1ht_uk * 100) / $total_obj_mwcuk;
    $total_av_ret_n2prcent_uk = ($total_av_ret_n2ht_uk * 100) / $total_obj_mwcuk;
}

elseif ($year == $Y)
{
    $total_av_ret_objprcent_cav = total_av_ret_objprcent($obj_mcvfr, $total_av_ret_objht_cav, $m, $timeday);
    $total_av_ret_n1prcent_cav = total_av_ret_objprcent($obj_mcvfr, $total_av_ret_n1ht_cav, $m, $timeday);
    $total_av_ret_n2prcent_cav = total_av_ret_objprcent($obj_mcvfr, $total_av_ret_n2ht_cav, $m, $timeday);

    $total_av_ret_objprcent_com = total_av_ret_objprcent($obj_mwccom, $total_av_ret_objht_com, $m, $timeday);
    $total_av_ret_n1prcent_com = total_av_ret_objprcent($obj_mwccom, $total_av_ret_n1ht_com, $m, $timeday);
    $total_av_ret_n2prcent_com = total_av_ret_objprcent($obj_mwccom, $total_av_ret_n2ht_com, $m, $timeday);

    $total_av_ret_objprcent_uk = total_av_ret_objprcent($obj_mwcuk, $total_av_ret_objht_uk, $m, $timeday);
    $total_av_ret_n1prcent_uk = total_av_ret_objprcent($obj_mwcuk, $total_av_ret_n1ht_uk, $m, $timeday);
    $total_av_ret_n2prcent_uk = total_av_ret_objprcent($obj_mwcuk, $total_av_ret_n2ht_uk, $m, $timeday);
}

























//FONCTION C_INSCRIT_CUR_UK

$C_inscrits_current_uk_n1 = c_inscrit_cur_uk($year - 1);
$C_inscrits_current_uk_n2 = c_inscrit_cur_uk($year - 2);
$C_inscrits_current_total_ukn1 = $C_inscrits_current_uk_n1[0] + $C_inscrits_current_uk_n1[1] + $C_inscrits_current_uk_n1[2] + $C_inscrits_current_uk_n1[3] + $C_inscrits_current_uk_n1[4] + $C_inscrits_current_uk_n1[5] + $C_inscrits_current_uk_n1[6] + $C_inscrits_current_uk_n1[7] + $C_inscrits_current_uk_n1[8] + $C_inscrits_current_uk_n1[9] + $C_inscrits_current_uk_n1[10] + $C_inscrits_current_uk_n1[11];
$C_inscrits_current_total_ukn2 = $C_inscrits_current_uk_n2[0] + $C_inscrits_current_uk_n2[1] + $C_inscrits_current_uk_n2[2] + $C_inscrits_current_uk_n2[3] + $C_inscrits_current_uk_n2[4] + $C_inscrits_current_uk_n2[5] + $C_inscrits_current_uk_n2[6] + $C_inscrits_current_uk_n2[7] + $C_inscrits_current_uk_n2[8] + $C_inscrits_current_uk_n2[9] + $C_inscrits_current_uk_n2[10] + $C_inscrits_current_uk_n2[11];




//FONCTION C_INSCRIT_CUR_MWCOM

$C_inscrits_current_fr_n1 = c_inscrit_cur_mwcom($year - 1, "FR");
$C_inscrits_current_fr_n2 = c_inscrit_cur_mwcom($year - 2, "FR");
$C_inscrits_current_total_frn1 = $C_inscrits_current_fr_n1[0] + $C_inscrits_current_fr_n1[1] + $C_inscrits_current_fr_n1[2] + $C_inscrits_current_fr_n1[3] + $C_inscrits_current_fr_n1[4] + $C_inscrits_current_fr_n1[5] + $C_inscrits_current_fr_n1[6] + $C_inscrits_current_fr_n1[7] + $C_inscrits_current_fr_n1[8] + $C_inscrits_current_fr_n1[9] + $C_inscrits_current_fr_n1[10] + $C_inscrits_current_fr_n1[11];
$C_inscrits_current_total_frn2 = $C_inscrits_current_fr_n2[0] + $C_inscrits_current_fr_n2[1] + $C_inscrits_current_fr_n2[2] + $C_inscrits_current_fr_n2[3] + $C_inscrits_current_fr_n2[4] + $C_inscrits_current_fr_n2[5] + $C_inscrits_current_fr_n2[6] + $C_inscrits_current_fr_n2[7] + $C_inscrits_current_fr_n2[8] + $C_inscrits_current_fr_n2[9] + $C_inscrits_current_fr_n2[10] + $C_inscrits_current_fr_n2[11];

$C_inscrits_current_com_n1 = c_inscrit_cur_mwcom($year - 1, "EN");
$C_inscrits_current_com_n2 = c_inscrit_cur_mwcom($year - 2, "EN");
$C_inscrits_current_total_comn1 = $C_inscrits_current_com_n1[0] + $C_inscrits_current_com_n1[1] + $C_inscrits_current_com_n1[2] + $C_inscrits_current_com_n1[3] + $C_inscrits_current_com_n1[4] + $C_inscrits_current_com_n1[5] + $C_inscrits_current_com_n1[6] + $C_inscrits_current_com_n1[7] + $C_inscrits_current_com_n1[8] + $C_inscrits_current_com_n1[9] + $C_inscrits_current_com_n1[10] + $C_inscrits_current_com_n1[11];
$C_inscrits_current_total_comn2 = $C_inscrits_current_com_n2[0] + $C_inscrits_current_com_n2[1] + $C_inscrits_current_com_n2[2] + $C_inscrits_current_com_n2[3] + $C_inscrits_current_com_n2[4] + $C_inscrits_current_com_n2[5] + $C_inscrits_current_com_n2[6] + $C_inscrits_current_com_n2[7] + $C_inscrits_current_com_n2[8] + $C_inscrits_current_com_n2[9] + $C_inscrits_current_com_n2[10] + $C_inscrits_current_com_n2[11];




// FONCTION INSCRITS_MCVFR_MWCOM

$Inscrits_annee_cav = inscrits_mcvfr_mwcom($year,"FR");
$Inscrits_annee_com = inscrits_mcvfr_mwcom($year,"EN");
$Inscrits_total_annee_cav = $Inscrits_annee_cav[0] + $Inscrits_annee_cav[1] + $Inscrits_annee_cav[2] + $Inscrits_annee_cav[3] + $Inscrits_annee_cav[4] + $Inscrits_annee_cav[5] + $Inscrits_annee_cav[6] + $Inscrits_annee_cav[7] + $Inscrits_annee_cav[8] + $Inscrits_annee_cav[9] + $Inscrits_annee_cav[10] + $Inscrits_annee_cav[11]; 
$Inscrits_total_annee_com = $Inscrits_annee_com[0] + $Inscrits_annee_com[1] + $Inscrits_annee_com[2] + $Inscrits_annee_com[3] + $Inscrits_annee_com[4] + $Inscrits_annee_com[5] + $Inscrits_annee_com[6] + $Inscrits_annee_com[7] + $Inscrits_annee_com[8] + $Inscrits_annee_com[9] + $Inscrits_annee_com[10] + $Inscrits_annee_com[11]; 
$Inscrits_n1_cav = inscrits_mcvfr_mwcom($yn1,"FR");
$Inscrits_n1_com = inscrits_mcvfr_mwcom($yn1,"EN");
$Inscrits_total_n1_cav = $Inscrits_n1_cav[0] + $Inscrits_n1_cav[1] + $Inscrits_n1_cav[2] + $Inscrits_n1_cav[3] + $Inscrits_n1_cav[4] + $Inscrits_n1_cav[5] + $Inscrits_n1_cav[6] + $Inscrits_n1_cav[7] + $Inscrits_n1_cav[8] + $Inscrits_n1_cav[9] + $Inscrits_n1_cav[10] + $Inscrits_n1_cav[11];
$Inscrits_total_n1_com = $Inscrits_n1_com[0] + $Inscrits_n1_com[1] + $Inscrits_n1_com[2] + $Inscrits_n1_com[3] + $Inscrits_n1_com[4] + $Inscrits_n1_com[5] + $Inscrits_n1_com[6] + $Inscrits_n1_com[7] + $Inscrits_n1_com[8] + $Inscrits_n1_com[9] + $Inscrits_n1_com[10] + $Inscrits_n1_com[11];
$Inscrits_n2_cav = inscrits_mcvfr_mwcom($yn2,"FR");
$Inscrits_n2_com = inscrits_mcvfr_mwcom($yn2,"EN");
$Inscrits_total_n2_cav = $Inscrits_n2_cav[0] + $Inscrits_n2_cav[1] + $Inscrits_n2_cav[2] + $Inscrits_n2_cav[3] + $Inscrits_n2_cav[4] + $Inscrits_n2_cav[5] + $Inscrits_n2_cav[6] + $Inscrits_n2_cav[7] + $Inscrits_n2_cav[8] + $Inscrits_n2_cav[9] + $Inscrits_n2_cav[10] + $Inscrits_n2_cav[11];
$Inscrits_total_n2_com = $Inscrits_n2_com[0] + $Inscrits_n2_com[1] + $Inscrits_n2_com[2] + $Inscrits_n2_com[3] + $Inscrits_n2_com[4] + $Inscrits_n2_com[5] + $Inscrits_n2_com[6] + $Inscrits_n2_com[7] + $Inscrits_n2_com[8] + $Inscrits_n2_com[9] + $Inscrits_n2_com[10] + $Inscrits_n2_com[11];







//FONCTION INSCRITS_UK

$Inscrits_annee_uk = inscrits_uk($year);
$Inscrits_total_annee_uk = $Inscrits_annee_uk[0] + $Inscrits_annee_uk[1] + $Inscrits_annee_uk[2] + $Inscrits_annee_uk[3] + $Inscrits_annee_uk[4] + $Inscrits_annee_uk[5] + $Inscrits_annee_uk[6] + $Inscrits_annee_uk[7] + $Inscrits_annee_uk[8] + $Inscrits_annee_uk[9] + $Inscrits_annee_uk[10] + $Inscrits_annee_uk[11]; 
$Inscrits_n1_uk = inscrits_uk($yn1);
$Inscrits_total_n1_uk = $Inscrits_n1_uk[0] + $Inscrits_n1_uk[1] + $Inscrits_n1_uk[2] + $Inscrits_n1_uk[3] + $Inscrits_n1_uk[4] + $Inscrits_n1_uk[5] + $Inscrits_n1_uk[6] + $Inscrits_n1_uk[7] + $Inscrits_n1_uk[8] + $Inscrits_n1_uk[9] + $Inscrits_n1_uk[10] + $Inscrits_n1_uk[11];
$Inscrits_n2_uk = inscrits_uk($yn2);
$Inscrits_total_n2_uk = $Inscrits_n2_uk[0] + $Inscrits_n2_uk[1] + $Inscrits_n2_uk[2] + $Inscrits_n2_uk[3] + $Inscrits_n2_uk[4] + $Inscrits_n2_uk[5] + $Inscrits_n2_uk[6] + $Inscrits_n2_uk[7] + $Inscrits_n2_uk[8] + $Inscrits_n2_uk[9] + $Inscrits_n2_uk[10] + $Inscrits_n2_uk[11];







//FONCTION C_COMMANDE_CUR_UK

$C_com_current_uk_n1 = c_commande_cur_uk($year - 1);
$C_com_current_uk_n2 = c_commande_cur_uk($year - 2);
$C_com_current_total_ukn1 = $C_com_current_uk_n1[0] + $C_com_current_uk_n1[1] + $C_com_current_uk_n1[2] + $C_com_current_uk_n1[3] + $C_com_current_uk_n1[4] + $C_com_current_uk_n1[5] + $C_com_current_uk_n1[6] + $C_com_current_uk_n1[7] + $C_com_current_uk_n1[8] + $C_com_current_uk_n1[9] + $C_com_current_uk_n1[10] + $C_com_current_uk_n1[11];
$C_com_current_total_ukn2 = $C_com_current_uk_n2[0] + $C_com_current_uk_n2[1] + $C_com_current_uk_n2[2] + $C_com_current_uk_n2[3] + $C_com_current_uk_n2[4] + $C_com_current_uk_n2[5] + $C_com_current_uk_n2[6] + $C_com_current_uk_n2[7] + $C_com_current_uk_n2[8] + $C_com_current_uk_n2[9] + $C_com_current_uk_n2[10] + $C_com_current_uk_n2[11];




//FONCTION C_COMMANDE_CUR_MWCOM

$C_com_current_fr_n1 = c_commande_cur_mwcom($year - 1, "FR");
$C_com_current_fr_n2 = c_commande_cur_mwcom($year - 2, "FR");
$C_com_current_total_frn1 = $C_com_current_fr_n1[0] + $C_com_current_fr_n1[1] + $C_com_current_fr_n1[2] + $C_com_current_fr_n1[3] + $C_com_current_fr_n1[4] + $C_com_current_fr_n1[5] + $C_com_current_fr_n1[6] + $C_com_current_fr_n1[7] + $C_com_current_fr_n1[8] + $C_com_current_fr_n1[9] + $C_com_current_fr_n1[10] + $C_com_current_fr_n1[11];
$C_com_current_total_frn2 = $C_com_current_fr_n2[0] + $C_com_current_fr_n2[1] + $C_com_current_fr_n2[2] + $C_com_current_fr_n2[3] + $C_com_current_fr_n2[4] + $C_com_current_fr_n2[5] + $C_com_current_fr_n2[6] + $C_com_current_fr_n2[7] + $C_com_current_fr_n2[8] + $C_com_current_fr_n2[9] + $C_com_current_fr_n2[10] + $C_com_current_fr_n2[11];

$C_com_current_com_n1 = c_commande_cur_mwcom($year - 1, "EN");
$C_com_current_com_n2 = c_commande_cur_mwcom($year - 2, "EN");
$C_com_current_total_comn1 = $C_com_current_com_n1[0] + $C_com_current_com_n1[1] + $C_com_current_com_n1[2] + $C_com_current_com_n1[3] + $C_com_current_com_n1[4] + $C_com_current_com_n1[5] + $C_com_current_com_n1[6] + $C_com_current_com_n1[7] + $C_com_current_com_n1[8] + $C_com_current_com_n1[9] + $C_com_current_com_n1[10] + $C_com_current_com_n1[11];
$C_com_current_total_comn2 = $C_com_current_com_n2[0] + $C_com_current_com_n2[1] + $C_com_current_com_n2[2] + $C_com_current_com_n2[3] + $C_com_current_com_n2[4] + $C_com_current_com_n2[5] + $C_com_current_com_n2[6] + $C_com_current_com_n2[7] + $C_com_current_com_n2[8] + $C_com_current_com_n2[9] + $C_com_current_com_n2[10] + $C_com_current_com_n2[11];






//FONCTION COMMANDES_MCVF_MWCOM

$Commandes_annee_cav = commandes_mcvf_mwcom($year,"FR");
$Commandes_annee_com = commandes_mcvf_mwcom($year,"EN");
$Commandes_total_annee_cav = $Commandes_annee_cav[0] + $Commandes_annee_cav[1] + $Commandes_annee_cav[2] + $Commandes_annee_cav[3] + $Commandes_annee_cav[4] + $Commandes_annee_cav[5] + $Commandes_annee_cav[6] + $Commandes_annee_cav[7] + $Commandes_annee_cav[8] + $Commandes_annee_cav[9] + $Commandes_annee_cav[10] + $Commandes_annee_cav[11]; 
$Commandes_total_annee_com = $Commandes_annee_com[0] + $Commandes_annee_com[1] + $Commandes_annee_com[2] + $Commandes_annee_com[3] + $Commandes_annee_com[4] + $Commandes_annee_com[5] + $Commandes_annee_com[6] + $Commandes_annee_com[7] + $Commandes_annee_com[8] + $Commandes_annee_com[9] + $Commandes_annee_com[10] + $Commandes_annee_com[11];
$Commandes_n1_cav = commandes_mcvf_mwcom($yn1,"FR");
$Commandes_n1_com = commandes_mcvf_mwcom($yn1,"EN");
$Commandes_total_n1_cav = $Commandes_n1_cav[0] + $Commandes_n1_cav[1] + $Commandes_n1_cav[2] + $Commandes_n1_cav[3] + $Commandes_n1_cav[4] + $Commandes_n1_cav[5] + $Commandes_n1_cav[6] + $Commandes_n1_cav[7] + $Commandes_n1_cav[8] + $Commandes_n1_cav[9] + $Commandes_n1_cav[10] + $Commandes_n1_cav[11];
$Commandes_total_n1_com = $Commandes_n1_com[0] + $Commandes_n1_com[1] + $Commandes_n1_com[2] + $Commandes_n1_com[3] + $Commandes_n1_com[4] + $Commandes_n1_com[5] + $Commandes_n1_com[6] + $Commandes_n1_com[7] + $Commandes_n1_com[8] + $Commandes_n1_com[9] + $Commandes_n1_com[10] + $Commandes_n1_com[11];
$Commandes_n2_cav = commandes_mcvf_mwcom($yn2,"FR");
$Commandes_n2_com = commandes_mcvf_mwcom($yn2,"EN");
$Commandes_total_n2_cav = $Commandes_n2_cav[0] + $Commandes_n2_cav[1] + $Commandes_n2_cav[2] + $Commandes_n2_cav[3] + $Commandes_n2_cav[4] + $Commandes_n2_cav[5] + $Commandes_n2_cav[6] + $Commandes_n2_cav[7] + $Commandes_n2_cav[8] + $Commandes_n2_cav[9] + $Commandes_n2_cav[10] + $Commandes_n2_cav[11];
$Commandes_total_n2_com = $Commandes_n2_com[0] + $Commandes_n2_com[1] + $Commandes_n2_com[2] + $Commandes_n2_com[3] + $Commandes_n2_com[4] + $Commandes_n2_com[5] + $Commandes_n2_com[6] + $Commandes_n2_com[7] + $Commandes_n2_com[8] + $Commandes_n2_com[9] + $Commandes_n2_com[10] + $Commandes_n2_com[11];





//FONCTION COMMANDES_UK

$Commandes_annee_uk = commandes_uk($year);
$Commandes_total_annee_uk = $Commandes_annee_uk[0] + $Commandes_annee_uk[1] + $Commandes_annee_uk[2] + $Commandes_annee_uk[3] + $Commandes_annee_uk[4] + $Commandes_annee_uk[5] + $Commandes_annee_uk[6] + $Commandes_annee_uk[7] + $Commandes_annee_uk[8] + $Commandes_annee_uk[9] + $Commandes_annee_uk[10] + $Commandes_annee_uk[11];
$Commandes_n1_uk = commandes_uk($yn1);
$Commandes_total_n1_uk = $Commandes_n1_uk[0] + $Commandes_n1_uk[1] + $Commandes_n1_uk[2] + $Commandes_n1_uk[3] + $Commandes_n1_uk[4] + $Commandes_n1_uk[5] + $Commandes_n1_uk[6] + $Commandes_n1_uk[7] + $Commandes_n1_uk[8] + $Commandes_n1_uk[9] + $Commandes_n1_uk[10] + $Commandes_n1_uk[11];
$Commandes_n2_uk = commandes_uk($yn2);
$Commandes_total_n2_uk = $Commandes_n2_uk[0] + $Commandes_n2_uk[1] + $Commandes_n2_uk[2] + $Commandes_n2_uk[3] + $Commandes_n2_uk[4] + $Commandes_n2_uk[5] + $Commandes_n2_uk[6] + $Commandes_n2_uk[7] + $Commandes_n2_uk[8] + $Commandes_n2_uk[9] + $Commandes_n2_uk[10] + $Commandes_n2_uk[11];




// FONCTION PANIER
$Panier_annee_cav = panier($year, "1,5,7,8", "", "","FR");
$Panier_annee_com = panier($year, "1,5,7,8", "", "","EN"); // pour uk mettre la region en minuscule !
$Panier_annee_uk = panier($year, "1,5,7,8", "", "", "uk");



if ($year < $Y)
{
$Panier_total_annee_cav = ($Panier_annee_cav[0] + $Panier_annee_cav[1] + $Panier_annee_cav[2] + $Panier_annee_cav[3] + $Panier_annee_cav[4] + $Panier_annee_cav[5] + $Panier_annee_cav[6] + $Panier_annee_cav[7] + $Panier_annee_cav[8] + $Panier_annee_cav[9] + $Panier_annee_cav[10] + $Panier_annee_cav[11]) / 12; 
$Panier_total_annee_com = ($Panier_annee_com[0] + $Panier_annee_com[1] + $Panier_annee_com[2] + $Panier_annee_com[3] + $Panier_annee_com[4] + $Panier_annee_com[5] + $Panier_annee_com[6] + $Panier_annee_com[7] + $Panier_annee_com[8] + $Panier_annee_com[9] + $Panier_annee_com[10] + $Panier_annee_com[11]) / 12; 
$Panier_total_annee_uk = ($Panier_annee_uk[0] + $Panier_annee_uk[1] + $Panier_annee_uk[2] + $Panier_annee_uk[3] + $Panier_annee_uk[4] + $Panier_annee_uk[5] + $Panier_annee_uk[6] + $Panier_annee_uk[7] + $Panier_annee_uk[8] + $Panier_annee_uk[9] + $Panier_annee_uk[10] + $Panier_annee_uk[11]) / 12; 
}
elseif ($year == $Y)
{
$Panier_total_annee_cav = ($Panier_annee_cav[0] + $Panier_annee_cav[1] + $Panier_annee_cav[2] + $Panier_annee_cav[3] + $Panier_annee_cav[4] + $Panier_annee_cav[5] + $Panier_annee_cav[6] + $Panier_annee_cav[7] + $Panier_annee_cav[8] + $Panier_annee_cav[9] + $Panier_annee_cav[10] + $Panier_annee_cav[11]) / $m; 
$Panier_total_annee_com = ($Panier_annee_com[0] + $Panier_annee_com[1] + $Panier_annee_com[2] + $Panier_annee_com[3] + $Panier_annee_com[4] + $Panier_annee_com[5] + $Panier_annee_com[6] + $Panier_annee_com[7] + $Panier_annee_com[8] + $Panier_annee_com[9] + $Panier_annee_com[10] + $Panier_annee_com[11]) / $m; 
$Panier_total_annee_uk = ($Panier_annee_uk[0] + $Panier_annee_uk[1] + $Panier_annee_uk[2] + $Panier_annee_uk[3] + $Panier_annee_uk[4] + $Panier_annee_uk[5] + $Panier_annee_uk[6] + $Panier_annee_uk[7] + $Panier_annee_uk[8] + $Panier_annee_uk[9] + $Panier_annee_uk[10] + $Panier_annee_uk[11]) / $m; 
}


$Panier_n1_cav = panier($yn1, "1,5,7,8", "", "","FR");
$Panier_n1_com = panier($yn1, "1,5,7,8", "", "","EN");
$Panier_n1_uk = panier($yn1, "1,5,7,8", "", "", "uk");
$Panier_total_n1_cav = ($Panier_n1_cav[0] + $Panier_n1_cav[1] + $Panier_n1_cav[2] + $Panier_n1_cav[3] + $Panier_n1_cav[4] + $Panier_n1_cav[5] + $Panier_n1_cav[6] + $Panier_n1_cav[7] + $Panier_n1_cav[8] + $Panier_n1_cav[9] + $Panier_n1_cav[10] + $Panier_n1_cav[11]) / 12;
$Panier_total_n1_com = ($Panier_n1_com[0] + $Panier_n1_com[1] + $Panier_n1_com[2] + $Panier_n1_com[3] + $Panier_n1_com[4] + $Panier_n1_com[5] + $Panier_n1_com[6] + $Panier_n1_com[7] + $Panier_n1_com[8] + $Panier_n1_com[9] + $Panier_n1_com[10] + $Panier_n1_com[11]) / 12;
$Panier_total_n1_uk = ($Panier_n1_uk[0] + $Panier_n1_uk[1] + $Panier_n1_uk[2] + $Panier_n1_uk[3] + $Panier_n1_uk[4] + $Panier_n1_uk[5] + $Panier_n1_uk[6] + $Panier_n1_uk[7] + $Panier_n1_uk[8] + $Panier_n1_uk[9] + $Panier_n1_uk[10] + $Panier_n1_uk[11]) / 12;
$Panier_n2_cav = panier($yn2, "1,5,7,8", "", "","FR");
$Panier_n2_com = panier($yn2, "1,5,7,8", "", "","EN");
$Panier_n2_uk = panier($yn2, "1,5,7,8", "", "", "uk");
$Panier_total_n2_cav = ($Panier_n2_cav[0] + $Panier_n2_cav[1] + $Panier_n2_cav[2] + $Panier_n2_cav[3] + $Panier_n2_cav[4] + $Panier_n2_cav[5] + $Panier_n2_cav[6] + $Panier_n2_cav[7] + $Panier_n2_cav[8] + $Panier_n2_cav[9] + $Panier_n2_cav[10] + $Panier_n2_cav[11]) / 12;
$Panier_total_n2_com = ($Panier_n2_com[0] + $Panier_n2_com[1] + $Panier_n2_com[2] + $Panier_n2_com[3] + $Panier_n2_com[4] + $Panier_n2_com[5] + $Panier_n2_com[6] + $Panier_n2_com[7] + $Panier_n2_com[8] + $Panier_n2_com[9] + $Panier_n2_com[10] + $Panier_n2_com[11]) / 12;
$Panier_total_n2_uk = ($Panier_n2_uk[0] + $Panier_n2_uk[1] + $Panier_n2_uk[2] + $Panier_n2_uk[3] + $Panier_n2_uk[4] + $Panier_n2_uk[5] + $Panier_n2_uk[6] + $Panier_n2_uk[7] + $Panier_n2_uk[8] + $Panier_n2_uk[9] + $Panier_n2_uk[10] + $Panier_n2_uk[11]) / 12;





























/*
MM    MM   AAA     CCCCC    AAA   VV     VV EEEEEEE    AAA    VV     VV IIIII NN   NN     FFFFFFF RRRRRR  
MMM  MMM  AAAAA   CC    C  AAAAA  VV     VV EE        AAAAA   VV     VV  III  NNN  NN     FF      RR   RR 
MM MM MM AA   AA  CC      AA   AA  VV   VV  EEEEE    AA   AA   VV   VV   III  NN N NN     FFFF    RRRRRR  
MM    MM AAAAAAA  CC    C AAAAAAA   VV VV   EE       AAAAAAA    VV VV    III  NN  NNN ... FF      RR  RR  
MM    MM AA   AA   CCCCC  AA   AA    VVV    EEEEEEE  AA   AA     VVV    IIIII NN   NN ... FF      RR   RR 
*/
$a = 0;
for($i=1;$i<=12;$i++)
{
    
    echo "<tr>
      <td>".$i."-".$year."</td>
      <td class=\"ca_nb\">".number_format($obj_mcvfr[$a],0,',',' ')." &euro;</td>
      <td class=\"ca_nb\">".number_format($CA_annee_cav[$a],2,',',' ')." &euro;</td>
      ".tdvalue($av_ret_objht_cav[$a],"&euro;",true).
      tdvalue_prc($av_ret_objprcent_cav[$a],"%",true).
      tdvalue($av_ret_n1ht_cav[$a],"&euro;",true).
      tdvalue_prc($av_ret_n1prcent_cav[$a],"%",true).
      tdvalue($av_ret_n2ht_cav[$a],"&euro;",true).
      tdvalue_prc($av_ret_n2prcent_cav[$a],"%",true).
    "</tr>";
    $a++;
}

echo "<tr style=\"font-weight:bold;\">
  <td>Total année $year</td>
  <td class=\"ca_nb\">".number_format($total_obj_mcvfr,0,',',' ')." &euro;</td>
  <td class=\"ca_nb\">".number_format($CA_total_annee_cav,2,',',' ')." &euro;</td>".
  tdvalue($total_av_ret_objht_cav,"&euro;",true).
  tdvalue_prc($total_av_ret_objprcent_cav,"%",true).
  tdvalue($total_av_ret_n1ht_cav,"&euro;",true).
  tdvalue_prc($total_av_ret_n1prcent_cav,"%",true).
  tdvalue($total_av_ret_n2ht_cav,"&euro;",true).
  tdvalue_prc($total_av_ret_n2prcent_cav,"%",true).
"</tr>";

echo "</table>";


echo "<br>
<table class=\"ca\">
  <tr>
     <td colspan=\"7\" class=\"ca_second_title\">Inscrits</td>
     <td colspan=\"1\" style=\"background-color:#ffffff; border: 1px solid #ffffff; border-right:1px solid black;\"></td>
     <td colspan=\"6\" class=\"ca_second_title\">Factures</td>
     <td colspan=\"1\" style=\"background-color:#ffffff; border: 1px solid #ffffff; border-right:1px solid black;\"></td>
     <td colspan=\"5\" class=\"ca_second_title\">Panier moyen</td>
  <tr class=\"ca_head\">
    <td></td>
    <td>Nbr inscrits</td>
    <td>Nbr inscrits N-1</td>
    <td>Av/ret N-1 &euro;HT</td>
    <td>% N-1</td>
    <td>Nbr inscrits N-2</td>
    <td>% N-2</td>
    <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px;\"></td>
    <td>Nbr Facture</td>
    <td>Nbr Facture N-1</td>
    <td>Av/ret N-1 &euro;HT</td>
    <td>% N-1</td>
    <td>Nbr Facture N-2</td>
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
      $statn0 = $Inscrits_annee_cav[$a] - $Inscrits_n1_cav[$a];
      $statn00 = $Commandes_annee_cav[$a] - $Commandes_n1_cav[$a];
      $statn1 = round(((($Inscrits_annee_cav[$a] * 100) / $Inscrits_n1_cav[$a]) - 100),2);
      $statn2 = round(((($Inscrits_annee_cav[$a] * 100) / $Inscrits_n2_cav[$a]) - 100),2);
      $statn3 = round(((($Commandes_annee_cav[$a] * 100) / $Commandes_n1_cav[$a]) - 100),2);
      $statn4 = round(((($Commandes_annee_cav[$a] * 100) / $Commandes_n2_cav[$a]) - 100),2);
    }
      if($i==$m){
        $statn0 = $Inscrits_annee_cav[$a] - $C_inscrits_current_fr_n1[$a];
        $statn00 = $Commandes_annee_cav[$a] - $C_com_current_fr_n1[$a];
        $statn1 = round(((($Inscrits_annee_cav[$a] * 100) / $C_inscrits_current_fr_n1[$a]) - 100),2);
        $statn2 = round(((($Inscrits_annee_cav[$a] * 100) / $C_inscrits_current_fr_n2[$a]) - 100),2);
        $statn3 = round(((($Commandes_annee_cav[$a] * 100) / $C_com_current_fr_n1[$a]) - 100),2);
        $statn4 = round(((($Commandes_annee_cav[$a] * 100) / $C_com_current_fr_n2[$a]) - 100),2);
      }
      if ($i > $m){
        $statn0 = -1000000;
        $statn00 = -1000000;
        $statn1 = -100;
        $statn2 = -100;
        $statn3 = -100;
        $statn4 = -100;
      }
  }

  if($year < $Y)
  {
      $statn0 = $Inscrits_annee_cav[$a] - $Inscrits_n1_cav[$a];
      $statn00 = $Commandes_annee_cav[$a] - $Commandes_n1_cav[$a];
      $statn1 = round(((($Inscrits_annee_cav[$a] * 100) / $Inscrits_n1_cav[$a]) - 100),2);
      $statn2 = round(((($Inscrits_annee_cav[$a] * 100) / $Inscrits_n2_cav[$a]) - 100),2);
      $statn3 = round(((($Commandes_annee_cav[$a] * 100) / $Commandes_n1_cav[$a]) - 100),2);
      $statn4 = round(((($Commandes_annee_cav[$a] * 100) / $Commandes_n2_cav[$a]) - 100),2);
  }

  $statn5 = round(((($Panier_annee_cav[$a] * 100) / $Panier_n1_cav[$a]) - 100),2);
  $statn6 = round(((($Panier_annee_cav[$a] * 100) / $Panier_n2_cav[$a]) - 100),2);
  
  if ($statn0 < 0)
  {
    $class0 = 'stat_rouge';
    if ($statn0 == -1000000)
      $statn0 = NULL;
  }
  else {
    $statn0 = "+".$statn0;
    $class0 = 'stat_vert';
  }

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





  if ($statn5 < 0){
      $class5 = 'stat_rouge';
      $statn5 = $statn5."%";
      if($statn5 == -100)
        $statn5 = NULL;
  }
  else {
      $statn5 = "+".$statn5."%";
      $class5 = 'stat_vert';
  }


  if ($statn6 < 0){
      $class6 = 'stat_rouge';
      $statn6 = $statn6."%";
      if($statn6 == -100)
        $statn6 = NULL;
  }
  else {
      $statn6 = "+".$statn6."%";
      $class6 = 'stat_vert';
  }

    echo "<tr>
      <td>".$i."-".$year."</td>
      <td style=\"text-align:right\">".$Inscrits_annee_cav[$a]."</td>
      <td style=\"text-align:right\">".$Inscrits_n1_cav[$a]."</td>
      <td class=\"".$class0."\">".$statn0."</td>
      <td class=\"".$class."\">".$statn1."</td>
      <td style=\"text-align:right\">".$Inscrits_n2_cav[$a]."</td>
      <td class=\"".$class2."\">".$statn2."</td>
      <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px;\"></td>
      <td style=\"text-align:right\">".$Commandes_annee_cav[$a]."</td>
      <td style=\"text-align:right\">".$Commandes_n1_cav[$a]."</td>
      <td class=\"".$class00."\">".$statn00."</td>
      <td class=\"".$class3."\">".$statn3."</td>
      <td style=\"text-align:right\">".$Commandes_n2_cav[$a]."</td>
      <td class=\"".$class4."\">".$statn4."</td>
      <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px;\"></td>
      <td style=\"text-align:right\">".round($Panier_annee_cav[$a],2)."&euro;</td>
      <td style=\"text-align:right\">".round($Panier_n1_cav[$a],2)."&euro;</td>
      <td class=\"".$class5."\">".$statn5."</td>
      <td style=\"text-align:right\">".round($Panier_n2_cav[$a],2)."&euro;</td>
      <td class=\"".$class6."\">".$statn6."</td>
    </tr>";
    $statn1 = 0;
  $a++;
  $i++;
}

if ($year == $Y)
{
  $total_stat_ins_n1 = round(((($Inscrits_total_annee_cav * 100) / $C_inscrits_current_total_frn1) - 100),2);
  $total_stat_ins_n2 = round(((($Inscrits_total_annee_cav * 100) / $C_inscrits_current_total_frn2) - 100),2);
  $total_stat_avret_insn1 = $Inscrits_total_annee_cav - $C_inscrits_current_total_frn1;
}
elseif ($year < $Y)
{
  $total_stat_ins_n1 = round(((($Inscrits_total_annee_cav * 100) / $Inscrits_total_n1_cav) - 100),2);
  $total_stat_ins_n2 = round(((($Inscrits_total_annee_cav * 100) / $Inscrits_total_n2_cav) - 100),2);
  $total_stat_avret_insn1 = $Inscrits_total_annee_cav - $Inscrits_total_n1_cav;
}

  if ($total_stat_avret_insn1 < 0)
  {
    $class07 = 'stat_rouge';
  }
  else {
    $total_stat_avret_insn1 = "+".$total_stat_avret_insn1;
    $class07 = 'stat_vert';
  }

  if ($total_stat_ins_n1 < 0){
      $class7 = 'stat_rouge';
      $total_stat_ins_n1 = $total_stat_ins_n1."%";
      if($total_stat_ins_n1 == -100)
        $total_stat_ins_n1 = NULL;
  }
  else {
      $total_stat_ins_n1 = "+".$total_stat_ins_n1."%";
      $class7 = 'stat_vert';
  }

  if ($total_stat_ins_n2 < 0){
      $class8 = 'stat_rouge';
      $total_stat_ins_n2 = $total_stat_ins_n2."%";
      if($total_stat_ins_n2 == -100)
        $total_stat_ins_n2 = NULL;
  }
  else {
      $total_stat_ins_n2 = "+".$total_stat_ins_n2."%";
      $class8 = 'stat_vert';
  }

if ($year == $Y)
{
  $total_stat_fac_n1 = round(((($Commandes_total_annee_cav * 100) / $C_com_current_total_frn1) - 100),2);
  $total_stat_fac_n2 = round(((($Commandes_total_annee_cav * 100) / $C_com_current_total_frn2) - 100),2);
  $total_stat_avret_comn1 = $Commandes_total_annee_cav - $C_com_current_total_frn1;
}
elseif ($year < $Y)
{
  $total_stat_fac_n1 = round(((($Commandes_total_annee_cav * 100) / $Commandes_total_n1_cav) - 100),2);
  $total_stat_fac_n2 = round(((($Commandes_total_annee_cav * 100) / $Commandes_total_n2_cav) - 100),2);
  $total_stat_avret_comn1 = $Commandes_total_annee_cav - $Commandes_total_n1_cav;
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


$total_stat_pan_n1 = round(((($Panier_total_annee_cav * 100) / $Panier_total_n1_cav) - 100),2);

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

$total_stat_pan_n2 = round(((($Panier_total_annee_cav * 100) / $Panier_total_n2_cav) - 100),2);

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
  <td>Total $year</td>
  <td class=\"ca_nb\">".$Inscrits_total_annee_cav."</td>
  <td class=\"ca_nb\">".$Inscrits_total_n1_cav."</td>
  <td class=\"".$class07."\">".$total_stat_avret_insn1."</td>
  <td class=\"".$class7."\">".$total_stat_ins_n1."</td>
  <td class=\"ca_nb\">".$Inscrits_total_n2_cav."</td>
  <td class=\"".$class8."\">".$total_stat_ins_n2."</td>
  <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
  <td class=\"ca_nb\">".$Commandes_total_annee_cav."</td>
  <td class=\"ca_nb\">".$Commandes_total_n1_cav."</td>
  <td class=\"".$class09."\">".$total_stat_avret_comn1."</td>
  <td class=\"".$class9."\">".$total_stat_fac_n1."</td>
  <td class=\"ca_nb\">".$Commandes_total_n2_cav."</td>
  <td class=\"".$class10."\">".$total_stat_fac_n2."</td>
  <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
  <td class=\"ca_nb\">".round($Panier_total_annee_cav,2)."&euro;</td>
  <td class=\"ca_nb\">".round($Panier_total_n1_cav,2)."&euro;</td>
  <td class=\"".$class11."\">".($total_stat_pan_n1)."</td>
  <td class=\"ca_nb\">".round($Panier_total_n2_cav,2)."&euro;</td>
  <td class=\"".$class12."\">".($total_stat_pan_n2)."</td>
</tr>";

echo "</table>";

echo "<br>";
echo "<br>";
echo "<br>";
echo "<br>";























/*
MM    MM YY   YY  WW      WW IIIII NN   NN EEEEEEE   CCCCC    AAA   BBBBB   IIIII NN   NN EEEEEEE TTTTTTT      CCCCC   OOOOO  MM    MM 
MMM  MMM YY   YY  WW      WW  III  NNN  NN EE       CC    C  AAAAA  BB   B   III  NNN  NN EE        TTT       CC    C OO   OO MMM  MMM 
MM MM MM  YYYYY   WW   W  WW  III  NN N NN EEEEE    CC      AA   AA BBBBBB   III  NN N NN EEEEE     TTT       CC      OO   OO MM MM MM 
MM    MM   YYY     WW WWW WW  III  NN  NNN EE       CC    C AAAAAAA BB   BB  III  NN  NNN EE        TTT   ... CC    C OO   OO MM    MM 
MM    MM   YYY      WW   WW  IIIII NN   NN EEEEEEE   CCCCC  AA   AA BBBBBB  IIIII NN   NN EEEEEEE   TTT   ...  CCCCC   OOOO0  MM    MM 
                                                                                                                                       

*/
echo "</select></p>
<table class=\"ca\">
<tr><td colspan=\"14\" class=\"ca_third_title\">Détail - My wine cabinet.com</td></tr>
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
  </tr>";

$a = 0;
for($i=1;$i<=12;$i++){
    
    echo "<tr>
      <td>".$i."-".$year."</td>
      <td class=\"ca_nb\">".number_format($obj_mwccom[$a],0,',',' ')." &euro;</td>
      <td class=\"ca_nb\">".number_format($CA_annee_com[$a],2,',',' ')." &euro;</td>
      ".tdvalue($av_ret_objht_com[$a],"&euro;",true).
      tdvalue_prc($av_ret_objprcent_com[$a],"%",true).
      tdvalue($av_ret_n1ht_com[$a],"&euro;",true).
      tdvalue_prc($av_ret_n1prcent_com[$a],"%",true).
      tdvalue($av_ret_n2ht_com[$a],"&euro;",true).
      tdvalue_prc($av_ret_n2prcent_com[$a],"%",true).
    "</tr>";
    $a++;
}

echo "<tr style=\"font-weight:bold;\">
  <td>Total $year</td>
  <td class=\"ca_nb\">".number_format($total_obj_mwccom,0,',',' ')." &euro;</td>
  <td class=\"ca_nb\">".number_format($CA_total_annee_com,2,',',' ')." &euro;</td>".
  tdvalue($total_av_ret_objht_com,"&euro;",true).
  tdvalue_prc($total_av_ret_objprcent_com,"%",true).
  tdvalue($total_av_ret_n1ht_com,"&euro;",true).
  tdvalue_prc($total_av_ret_n1prcent_com,"%",true).
  tdvalue($total_av_ret_n2ht_com,"&euro;",true).
  tdvalue_prc($total_av_ret_n2prcent_com,"%",true).
"</tr>";

echo "</table>";



echo "<br>
<table class=\"ca\">
  <tr>
    <td colspan=\"7\" class=\"ca_third_title\">Inscrits</td>
    <td colspan=\"1\" style=\"background-color:#ffffff; border: 1px solid #ffffff; border-right:1px solid black;\"></td>
    <td colspan=\"6\" class=\"ca_third_title\">Factures</td>
    <td colspan=\"1\" style=\"background-color:#ffffff; border: 1px solid #ffffff; border-right:1px solid black;\"></td>
    <td colspan=\"5\" class=\"ca_third_title\">Panier moyen</td>
  <tr class=\"ca_head\">
    <td></td>
    <td>Nbr inscrits</td>
    <td>Nbr inscrits N-1</td>
    <td>Av/ret N-1 &euro;HT</td>
    <td>% N-1</td>
    <td>Nbr inscrits N-2</td>
    <td>% N-2</td>
    <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px;\"></td>
    <td>Nbr Facture</td>
    <td>Nbr Facture N-1</td>
    <td>Av/ret N-1 &euro;HT</td>
    <td>% N-1</td>
    <td>Nbr Facture N-2</td>
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
      $statn0 = $Inscrits_annee_com[$a] - $Inscrits_n1_com[$a];
      $statn00 = $Commandes_annee_com[$a] - $Commandes_n1_com[$a];
      $statn1 = round(((($Inscrits_annee_com[$a] * 100) / $Inscrits_n1_com[$a]) - 100),2);
      $statn2 = round(((($Inscrits_annee_com[$a] * 100) / $Inscrits_n2_com[$a]) - 100),2);
      $statn3 = round(((($Commandes_annee_com[$a] * 100) / $Commandes_n1_com[$a]) - 100),2);
      $statn4 = round(((($Commandes_annee_com[$a] * 100) / $Commandes_n2_com[$a]) - 100),2);
    }
      if($i==$m){
        $statn0 = $Inscrits_annee_com[$a] - $C_inscrits_current_com_n1[$a];
        $statn00 = $Commandes_annee_com[$a] - $C_com_current_com_n1[$a];
        $statn1 = round(((($Inscrits_annee_com[$a] * 100) / $C_inscrits_current_com_n1[$a]) - 100),2);
        $statn2 = round(((($Inscrits_annee_com[$a] * 100) / $C_inscrits_current_com_n2[$a]) - 100),2);
        $statn3 = round(((($Commandes_annee_com[$a] * 100) / $C_com_current_com_n1[$a]) - 100),2);
        $statn4 = round(((($Commandes_annee_com[$a] * 100) / $C_com_current_com_n2[$a]) - 100),2);
      }
      if ($i > $m){
        $statn0 = -1000000;
        $statn00 = -1000000;
        $statn1 = -100;
        $statn2 = -100;
        $statn3 = -100;
        $statn4 = -100;
      }
  }

  if($year < $Y)
  {
      $statn0 = $Inscrits_annee_com[$a] - $Inscrits_n1_com[$a];
      $statn00 = $Commandes_annee_com[$a] - $Commandes_n1_com[$a];
      $statn1 = round(((($Inscrits_annee_com[$a] * 100) / $Inscrits_n1_com[$a]) - 100),2);
      $statn2 = round(((($Inscrits_annee_com[$a] * 100) / $Inscrits_n2_com[$a]) - 100),2);
      $statn3 = round(((($Commandes_annee_com[$a] * 100) / $Commandes_n1_com[$a]) - 100),2);
      $statn4 = round(((($Commandes_annee_com[$a] * 100) / $Commandes_n2_com[$a]) - 100),2);
  }

  $statn5 = round(((($Panier_annee_com[$a] * 100) / $Panier_n1_com[$a]) - 100),2);
  $statn6 = round(((($Panier_annee_com[$a] * 100) / $Panier_n2_com[$a]) - 100),2);


  if ($statn0 < 0)
  {
    $class0 = 'stat_rouge';
    if ($statn0 == -1000000)
      $statn0 = NULL;
  }
  else {
    $statn0 = "+".$statn0;
    $class0 = 'stat_vert';
  }

  if ($statn00 < 0)
  {
    $class00 = 'stat_rouge';
    if ($statn00 == -1000000)
      $statn00 = NULL;
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

  if ($statn5 < 0){
      $class5 = 'stat_rouge';
      $statn5 = $statn5."%";
      if($statn5 == -100)
        $statn5 = NULL;
  }
  else {
      $statn5 = "+".$statn5."%";
      $class5 = 'stat_vert';
  }


  if ($statn6 < 0){
      $class6 = 'stat_rouge';
      $statn6 = $statn6."%";
      if($statn6 == -100)
        $statn6 = NULL;
  }
  else {
      $statn6 = "+".$statn6."%";
      $class6 = 'stat_vert';
  }

    echo "<tr>
      <td>".$i."-".$year."</td>
      <td style=\"text-align:right\">".$Inscrits_annee_com[$a]."</td>
      <td style=\"text-align:right\">".$Inscrits_n1_com[$a]."</td>
      <td class=\"".$class0."\">".$statn0."</td>
      <td class=\"".$class."\">".$statn1."</td>
      <td style=\"text-align:right\">".$Inscrits_n2_com[$a]."</td>
      <td class=\"".$class2."\">".$statn2."</td>
      <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px;\"></td>
      <td style=\"text-align:right\">".$Commandes_annee_com[$a]."</td>
      <td style=\"text-align:right\">".$Commandes_n1_com[$a]."</td>
      <td class=\"".$class00."\">".$statn00."</td>
      <td class=\"".$class3."\">".$statn3."</td>
      <td style=\"text-align:right\">".$Commandes_n2_com[$a]."</td>
      <td class=\"".$class4."\">".$statn4."</td>
      <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px;\"></td>
      <td style=\"text-align:right\">".round($Panier_annee_com[$a],2)."&euro;</td>
      <td style=\"text-align:right\">".round($Panier_n1_com[$a],2)."&euro;</td>
      <td class=\"".$class5."\">".$statn5."</td>
      <td style=\"text-align:right\">".round($Panier_n2_com[$a],2)."&euro;</td>
      <td class=\"".$class6."\">".$statn6."</td>
    </tr>";
    $statn1 = 0;
  $a++;
  $i++;
}

if ($year == $Y)
{
  $total_stat_ins_n1 = round(((($Inscrits_total_annee_com * 100) / $C_inscrits_current_total_comn1) - 100),2);
  $total_stat_ins_n2 = round(((($Inscrits_total_annee_com * 100) / $C_inscrits_current_total_comn2) - 100),2);
  $total_stat_avret_insn1 = $Inscrits_total_annee_com - $C_inscrits_current_total_comn1;
}
elseif ($year < $Y)
{
  $total_stat_ins_n1 = round(((($Inscrits_total_annee_com * 100) / $Inscrits_total_n1_com) - 100),2);
  $total_stat_ins_n2 = round(((($Inscrits_total_annee_com * 100) / $Inscrits_total_n2_com) - 100),2);
  $total_stat_avret_insn1 = $Inscrits_total_annee_com - $Inscrits_total_n1_com;
}

  if ($total_stat_avret_insn1 < 0)
  {
    $class07 = 'stat_rouge';
  }
  else {
    $total_stat_avret_insn1 = "+".$total_stat_avret_insn1;
    $class07 = 'stat_vert';
  }


  if ($total_stat_ins_n1 < 0){
      $class7 = 'stat_rouge';
      $total_stat_ins_n1 = $total_stat_ins_n1."%";
      if($total_stat_ins_n1 == -100)
        $total_stat_ins_n1 = NULL;
  }
  else {
      $total_stat_ins_n1 = "+".$total_stat_ins_n1."%";
      $class7 = 'stat_vert';
  }

  if ($total_stat_ins_n2 < 0){
      $class8 = 'stat_rouge';
      $total_stat_ins_n2 = $total_stat_ins_n2."%";
      if($total_stat_ins_n2 == -100)
        $total_stat_ins_n2 = NULL;
  }
  else {
      $total_stat_ins_n2 = "+".$total_stat_ins_n2."%";
      $class8 = 'stat_vert';
  }


if ($year == $Y)
{
  $total_stat_fac_n1 = round(((($Commandes_total_annee_com * 100) / $C_com_current_total_comn1) - 100),2);
  $total_stat_fac_n2 = round(((($Commandes_total_annee_com * 100) / $C_com_current_total_comn2) - 100),2);
  $total_stat_avret_comn1 = $Commandes_total_annee_com - $C_com_current_total_comn1;
}
elseif ($year < $Y)
{
  $total_stat_fac_n1 = round(((($Commandes_total_annee_com * 100) / $Commandes_total_n1_com) - 100),2);
  $total_stat_fac_n2 = round(((($Commandes_total_annee_com * 100) / $Commandes_total_n2_com) - 100),2);
  $total_stat_avret_comn1 = $Commandes_total_annee_com - $Commandes_total_n1_com;
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


$total_stat_pan_n1 = round(((($Panier_total_annee_com * 100) / $Panier_total_n1_com) - 100),2);

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

$total_stat_pan_n2 = round(((($Panier_total_annee_com * 100) / $Panier_total_n2_com) - 100),2);

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
  <td>Total $year</td>
  <td class=\"ca_nb\">".$Inscrits_total_annee_com."</td>
  <td class=\"ca_nb\">".$Inscrits_total_n1_com."</td>
  <td class=\"".$class07."\">".$total_stat_avret_insn1."</td>
  <td class=\"".$class7."\">".$total_stat_ins_n1."</td>
  <td class=\"ca_nb\">".$Inscrits_total_n2_com."</td>
  <td class=\"".$class8."\">".$total_stat_ins_n2."</td>
  <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
  <td class=\"ca_nb\">".$Commandes_total_annee_com."</td>
  <td class=\"ca_nb\">".$Commandes_total_n1_com."</td>
  <td class=\"".$class09."\">".$total_stat_avret_comn1."</td>
  <td class=\"".$class9."\">".$total_stat_fac_n1."</td>
  <td class=\"ca_nb\">".$Commandes_total_n2_com."</td>
  <td class=\"".$class10."\">".$total_stat_fac_n2."</td>
  <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
  <td class=\"ca_nb\">".round($Panier_total_annee_com,2)."&euro;</td>
  <td class=\"ca_nb\">".round($Panier_total_n1_com,2)."&euro;</td>
  <td class=\"".$class11."\">".($total_stat_pan_n1)."</td>
  <td class=\"ca_nb\">".round($Panier_total_n2_com,2)."&euro;</td>
  <td class=\"".$class12."\">".($total_stat_pan_n2)."</td>
</tr>";

echo "</table>";




echo "<br>";
echo "<br>";
echo "<br>";
echo "<br>";




































/*

MM    MM YY   YY  WW      WW IIIII NN   NN EEEEEEE   CCCCC    AAA   BBBBB   IIIII NN   NN EEEEEEE TTTTTTT      CCCCC   OOOOO      UU   UU KK  KK 
MMM  MMM YY   YY  WW      WW  III  NNN  NN EE       CC    C  AAAAA  BB   B   III  NNN  NN EE        TTT       CC    C OO   OO     UU   UU KK KK  
MM MM MM  YYYYY   WW   W  WW  III  NN N NN EEEEE    CC      AA   AA BBBBBB   III  NN N NN EEEEE     TTT       CC      OO   OO     UU   UU KKKK   
MM    MM   YYY     WW WWW WW  III  NN  NNN EE       CC    C AAAAAAA BB   BB  III  NN  NNN EE        TTT   ... CC    C OO   OO ... UU   UU KK KK  
MM    MM   YYY      WW   WW  IIIII NN   NN EEEEEEE   CCCCC  AA   AA BBBBBB  IIIII NN   NN EEEEEEE   TTT   ...  CCCCC   OOOO0  ...  UUUUU  KK  KK 
*/
echo "</select></p>
<table class=\"ca\">
<tr><td colspan=\"14\" class=\"ca_fourth_title\">Détail - My wine cabinet.co.uk</td></tr>
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
  </tr>";


$a = 0;
for($i=1;$i<=12;$i++){
    
    echo "<tr>
      <td>".$i."-".$year."</td>
      <td class=\"ca_nb\">".number_format($obj_mwcuk[$a],0,',',' ')." &euro;</td>
      <td class=\"ca_nb\">".number_format($CA_annee_uk[$a],2,',',' ')." &euro;</td>
      ".tdvalue($av_ret_objht_uk[$a],"&euro;",true).
      tdvalue_prc($av_ret_objprcent_uk[$a],"%",true).
      tdvalue($av_ret_n1ht_uk[$a],"&euro;",true).
      tdvalue_prc($av_ret_n1prcent_uk[$a],"%",true).
      tdvalue($av_ret_n2ht_uk[$a],"&euro;",true).
      tdvalue_prc($av_ret_n2prcent_uk[$a],"%",true).
    "</tr>";
    $a++;
}

echo "<tr style=\"font-weight:bold;\">
  <td>Total $year</td>
  <td class=\"ca_nb\">".number_format($total_obj_mwcuk,0,',',' ')." &euro;</td>
  <td class=\"ca_nb\">".number_format($CA_total_annee_uk,2,',',' ')." &euro;</td>".
  tdvalue($total_av_ret_objht_uk,"&euro;",true).
  tdvalue_prc($total_av_ret_objprcent_uk,"%",true).
  tdvalue($total_av_ret_n1ht_uk,"&euro;",true).
  tdvalue_prc($total_av_ret_n1prcent_uk,"%",true).
  tdvalue($total_av_ret_n2ht_uk,"&euro;",true).
  tdvalue_prc($total_av_ret_n2prcent_uk,"%",true).
"</tr>";

echo "</table>";



echo "<br>
<table class=\"ca\">
  <tr>
     <td colspan=\"7\" class=\"ca_fourth_title\">Inscrits</td>
     <td colspan=\"1\" style=\"background-color:#ffffff; border: 1px solid #ffffff; border-right:1px solid black;\"></td>
     <td colspan=\"6\" class=\"ca_fourth_title\">Factures</td>
     <td colspan=\"1\" style=\"background-color:#ffffff; border: 1px solid #ffffff; border-right:1px solid black;\"></td>
     <td colspan=\"5\" class=\"ca_fourth_title\">Panier moyen</td>
  <tr class=\"ca_head\">
    <td></td>
    <td>Nbr inscrits</td>
    <td>Nbr inscrits N-1</td>
    <td>Av/ret N-1 &euro;HT</td>
    <td>% N-1</td>
    <td>Nbr inscrits N-2</td>
    <td>% N-2</td>
    <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px;\"></td>
    <td>Nbr Facture</td>
    <td>Nbr Facture N-1</td>
    <td>Av/ret N-1 &euro;HT</td>
    <td>% N-1</td>
    <td>Nbr Facture N-2</td>
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
      $statn0 = $Inscrits_annee_uk[$a] - $Inscrits_n1_uk[$a];
      $statn00 = $Commandes_annee_uk[$a] - $Commandes_n1_uk[$a];
      $statn1 = round(((($Inscrits_annee_uk[$a] * 100) / $Inscrits_n1_uk[$a]) - 100),2);
      $statn2 = round(((($Inscrits_annee_uk[$a] * 100) / $Inscrits_n2_uk[$a]) - 100),2);
      $statn3 = round(((($Commandes_annee_uk[$a] * 100) / $Commandes_n1_uk[$a]) - 100),2);
      $statn4 = round(((($Commandes_annee_uk[$a] * 100) / $Commandes_n2_uk[$a]) - 100),2);
    }
      if($i==$m){
        $statn0 = $Inscrits_annee_uk[$a] - $C_inscrits_current_uk_n1[$a];
        $statn00 = $Commandes_annee_uk[$a] - $C_com_current_uk_n1[$a];
        $statn1 = round(((($Inscrits_annee_uk[$a] * 100) / $C_inscrits_current_uk_n1[$a]) - 100),2);
        $statn2 = round(((($Inscrits_annee_uk[$a] * 100) / $C_inscrits_current_uk_n2[$a]) - 100),2);
        $statn3 = round(((($Commandes_annee_uk[$a] * 100) / $C_com_current_uk_n1[$a]) - 100),2);
        $statn4 = round(((($Commandes_annee_uk[$a] * 100) / $C_com_current_uk_n2[$a]) - 100),2);
      }
      if ($i > $m){
        $statn0 = -1000000;
        $statn00 = -1000000;
        $statn1 = -100;
        $statn2 = -100;
        $statn3 = -100;
        $statn4 = -100;
      }
  }

  if($year < $Y)
  {
      $statn0 = $Inscrits_annee_uk[$a] - $Inscrits_n1_uk[$a];
      $statn00 = $Commandes_annee_uk[$a] - $Commandes_n1_uk[$a];
      $statn1 = round(((($Inscrits_annee_uk[$a] * 100) / $Inscrits_n1_uk[$a]) - 100),2);
      $statn2 = round(((($Inscrits_annee_uk[$a] * 100) / $Inscrits_n2_uk[$a]) - 100),2);
      $statn3 = round(((($Commandes_annee_uk[$a] * 100) / $Commandes_n1_uk[$a]) - 100),2);
      $statn4 = round(((($Commandes_annee_uk[$a] * 100) / $Commandes_n2_uk[$a]) - 100),2);
  }

  $statn5 = round(((($Panier_annee_uk[$a] * 100) / $Panier_n1_uk[$a]) - 100),2);
  $statn6 = round(((($Panier_annee_uk[$a] * 100) / $Panier_n2_uk[$a]) - 100),2);

  if ($statn0 < 0)
  {
    $class0 = 'stat_rouge';
    if ($statn0 == -1000000)
      $statn0 = NULL;
  }
  else {
    $statn0 = "+".$statn0;
    $class0 = 'stat_vert';
  }

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





  if ($statn5 < 0){
      $class5 = 'stat_rouge';
      $statn5 = $statn5."%";
      if($statn5 == -100)
        $statn5 = NULL;
  }
  else {
      $statn5 = "+".$statn5."%";
      $class5 = 'stat_vert';
  }


  if ($statn6 < 0){
      $class6 = 'stat_rouge';
      $statn6 = $statn6."%";
      if($statn6 == -100)
        $statn6 = NULL;
  }
  else {
      $statn6 = "+".$statn6."%";
      $class6 = 'stat_vert';
  }

    echo "<tr>
      <td>".$i."-".$year."</td>
      <td style=\"text-align:right\">".$Inscrits_annee_uk[$a]."</td>
      <td style=\"text-align:right\">".$Inscrits_n1_uk[$a]."</td>
      <td class=\"".$class0."\">".$statn0."</td>
      <td class=\"".$class."\">".$statn1."</td>
      <td style=\"text-align:right\">".$Inscrits_n2_uk[$a]."</td>
      <td class=\"".$class2."\">".$statn2."</td>
      <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px;\"></td>
      <td style=\"text-align:right\">".$Commandes_annee_uk[$a]."</td>
      <td style=\"text-align:right\">".$Commandes_n1_uk[$a]."</td>
      <td class=\"".$class00."\">".$statn00."</td>
      <td class=\"".$class3."\">".$statn3."</td>
      <td style=\"text-align:right\">".$Commandes_n2_uk[$a]."</td>
      <td class=\"".$class4."\">".$statn4."</td>
      <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px;\"></td>
      <td style=\"text-align:right\">".round($Panier_annee_uk[$a],2)."&euro;</td>
      <td style=\"text-align:right\">".round($Panier_n1_uk[$a],2)."&euro;</td>
      <td class=\"".$class5."\">".$statn5."</td>
      <td style=\"text-align:right\">".round($Panier_n2_uk[$a],2)."&euro;</td>
      <td class=\"".$class6."\">".$statn6."</td>
    </tr>";
    $statn1 = 0;
  $a++;
  $i++;
}

if ($year == $Y)
{
  $total_stat_ins_n1 = round(((($Inscrits_total_annee_uk * 100) / $C_inscrits_current_total_ukn1) - 100),2);
  $total_stat_ins_n2 = round(((($Inscrits_total_annee_uk * 100) / $C_inscrits_current_total_ukn2) - 100),2);
  $total_stat_avret_insn1 = $Inscrits_total_annee_uk - $C_inscrits_current_total_ukn1;
}
elseif ($year < $Y)
{
  $total_stat_ins_n1 = round(((($Inscrits_total_annee_uk * 100) / $Inscrits_total_n1_uk) - 100),2);
  $total_stat_ins_n2 = round(((($Inscrits_total_annee_uk * 100) / $Inscrits_total_n2_uk) - 100),2);
  $total_stat_avret_insn1 = $Inscrits_total_annee_uk - $Inscrits_total_n1_uk;
}

  if ($total_stat_avret_insn1 < 0)
  {
    $class07 = 'stat_rouge';
  }
  else {
    $total_stat_avret_insn1 = "+".$total_stat_avret_insn1;
    $class07 = 'stat_vert';
  }

  if ($total_stat_ins_n1 < 0){
      $class7 = 'stat_rouge';
      $total_stat_ins_n1 = $total_stat_ins_n1."%";
      if($total_stat_ins_n1 == -100)
        $total_stat_ins_n1 = NULL;
  }
  else {
      $total_stat_ins_n1 = "+".$total_stat_ins_n1."%";
      $class7 = 'stat_vert';
  }


  if ($total_stat_ins_n2 < 0){
      $class8 = 'stat_rouge';
      $total_stat_ins_n2 = $total_stat_ins_n2."%";
      if($total_stat_ins_n2 == -100)
        $total_stat_ins_n2 = NULL;
  }
  else {
      $total_stat_ins_n2 = "+".$total_stat_ins_n2."%";
      $class8 = 'stat_vert';
  }


if ($year == $Y)
{
  $total_stat_fac_n1 = round(((($Commandes_total_annee_uk * 100) / $C_com_current_total_ukn1) - 100),2);
  $total_stat_fac_n2 = round(((($Commandes_total_annee_uk * 100) / $C_com_current_total_ukn2) - 100),2);
  $total_stat_avret_comn1 = $Commandes_total_annee_uk - $C_com_current_total_ukn1;
}
elseif ($year < $Y)
{
  $total_stat_fac_n1 = round(((($Commandes_total_annee_uk * 100) / $Commandes_total_n1_uk) - 100),2);
  $total_stat_fac_n2 = round(((($Commandes_total_annee_uk * 100) / $Commandes_total_n2_uk) - 100),2);
  $total_stat_avret_comn1 = $Commandes_total_annee_uk - $Commandes_total_n1_uk;
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


$total_stat_pan_n1 = round(((($Panier_total_annee_uk * 100) / $Panier_total_n1_uk) - 100),2);

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

$total_stat_pan_n2 = round(((($Panier_total_annee_uk * 100) / $Panier_total_n2_uk) - 100),2);

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
  <td>Total $year</td>
  <td class=\"ca_nb\">".$Inscrits_total_annee_uk."</td>
  <td class=\"ca_nb\">".$Inscrits_total_n1_uk."</td>
  <td class=\"".$class07."\">".$total_stat_avret_insn1."</td>
  <td class=\"".$class7."\">".$total_stat_ins_n1."</td>
  <td class=\"ca_nb\">".$Inscrits_total_n2_uk."</td>
  <td class=\"".$class8."\">".$total_stat_ins_n2."</td>
  <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
  <td class=\"ca_nb\">".$Commandes_total_annee_uk."</td>
  <td class=\"ca_nb\">".$Commandes_total_n1_uk."</td>
  <td class=\"".$class09."\">".$total_stat_avret_comn1."</td>
  <td class=\"".$class9."\">".$total_stat_fac_n1."</td>
  <td class=\"ca_nb\">".$Commandes_total_n2_uk."</td>
  <td class=\"".$class10."\">".$total_stat_fac_n2."</td>
  <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
  <td class=\"ca_nb\">".round($Panier_total_annee_uk,2)."&euro;</td>
  <td class=\"ca_nb\">".round($Panier_total_n1_uk,2)."&euro;</td>
  <td class=\"".$class11."\">".($total_stat_pan_n1)."</td>
  <td class=\"ca_nb\">".round($Panier_total_n2_uk,2)."&euro;</td>
  <td class=\"".$class12."\">".($total_stat_pan_n2)."</td>
</tr>";

echo "</table>";

echo "<br>";
echo "<br>";
echo "<br>";
include "./footer.php";

?>