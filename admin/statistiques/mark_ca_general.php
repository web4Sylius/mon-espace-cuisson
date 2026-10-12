<?php
require "../admin_global.php";
include "../header.php";
include "menu_2.php";
include "../../class/facture.class.php";
require "functions_markcageneral.php";
ini_set("display_errors",0);

//Recherche l'année la plus récente pour laquelle les objectifs ont été définis
$sql = "SELECT MAX(obj_annee) as annee_max";
$sql .= " FROM objectifs_enregistres";
$annee_max = $DB_site2->query_first($sql)[0];

echo "
<style>
.stat_rouge {color:red; text-align: right;}
.stat_vert {color:green; text-align: right;}
.ca { color:black; border-collapse:collapse; border:solid thin black; width:100%;}
.ca td { border:solid thin black; }
.ca_nb { text-align:right; white-space:nowrap; }
.ca_title { background-color:#4CAF50; text-align:center; color:white;}
.ca_second_title { background-color:#af504c; text-align:center; color:white;}
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

<h1>Chiffre d'affaire enregistré</h1>";?>



<?php
include(BACK_PATH.'/statistiques/statistiques_adm.php');
if(!isset($year)) $year=date("Y");




echo "<p style=\"margin-top:40px; margin-bottom:15px;\"align=\"center\">Année : <select name=\"year\" onchange=\"document.location='mark_ca_general.php?year='+this.value\">";
for($y=2010;$y<=$annee_max;$y++) echo "<option value=\"$y\"".($y==$year?" selected":"").">$y</option>";




echo "</select></p>
<table class=\"ca\">
<tr><td colspan=\"14\" class=\"ca_second_title\">Chiffre d'affaires enregistré</td></tr>
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
$list1=ca($year-1,$to1);
$list2=ca($year-2,$to2);




//FONCTION OBJ_MONTANT

$chiffre_mcvfr = obj_montant($year, 1);
$chiffre_mwccom = obj_montant($year, 2);
$chiffre_mwcuk = obj_montant($year, 3);
$chiffre_marketplace = obj_montant($year, 4);
$chiffre_mvinoes = obj_montant($year, 5);

$total[0] = $chiffre_mcvfr[0] + $chiffre_mwccom[0] + $chiffre_mwcuk[0] + $chiffre_marketplace[0] + $chiffre_mvinoes[0];
$total[1] = $chiffre_mcvfr[1] + $chiffre_mwccom[1] + $chiffre_mwcuk[1] + $chiffre_marketplace[1] + $chiffre_mvinoes[1];
$total[2] = $chiffre_mcvfr[2] + $chiffre_mwccom[2] + $chiffre_mwcuk[2] + $chiffre_marketplace[2] + $chiffre_mvinoes[2];
$total[3] = $chiffre_mcvfr[3] + $chiffre_mwccom[3] + $chiffre_mwcuk[3] + $chiffre_marketplace[3] + $chiffre_mvinoes[3];
$total[4] = $chiffre_mcvfr[4] + $chiffre_mwccom[4] + $chiffre_mwcuk[4] + $chiffre_marketplace[4] + $chiffre_mvinoes[4];
$total[5] = $chiffre_mcvfr[5] + $chiffre_mwccom[5] + $chiffre_mwcuk[5] + $chiffre_marketplace[5] + $chiffre_mvinoes[5];
$total[6] = $chiffre_mcvfr[6] + $chiffre_mwccom[6] + $chiffre_mwcuk[6] + $chiffre_marketplace[6] + $chiffre_mvinoes[6];
$total[7] = $chiffre_mcvfr[7] + $chiffre_mwccom[7] + $chiffre_mwcuk[7] + $chiffre_marketplace[7] + $chiffre_mvinoes[7];
$total[8] = $chiffre_mcvfr[8] + $chiffre_mwccom[8] + $chiffre_mwcuk[8] + $chiffre_marketplace[8] + $chiffre_mvinoes[8];
$total[9] = $chiffre_mcvfr[9] + $chiffre_mwccom[9] + $chiffre_mwcuk[9] + $chiffre_marketplace[9] + $chiffre_mvinoes[9];
$total[10] = $chiffre_mcvfr[10] + $chiffre_mwccom[10] + $chiffre_mwcuk[10] + $chiffre_marketplace[10] + $chiffre_mvinoes[10];
$total[11] = $chiffre_mcvfr[11] + $chiffre_mwccom[11] + $chiffre_mwcuk[11] + $chiffre_marketplace[11] + $chiffre_mvinoes[11];

$all_total = $total[0] + $total[1] + $total[2] + $total[3] + $total[4] + $total[5] + $total[6] + $total[7] + $total[8] + $total[9] + $total[10] + $total[11];

$anencours=0;
$cur = 0;
for($mois=1;$mois<=12;$mois++){
    $total=$list['ca'][''][$mois]+$list['ca']['mp'][$mois]+$list['ca']['sav'][$mois]+$list['ca']['avoir'][$mois]+$list['ca']['mobile'][$mois];
    $total1=$list1['ca'][''][$mois]+$list1['ca']['mp'][$mois]+$list1['ca']['sav'][$mois]+$list1['ca']['avoir'][$mois]+$list1['ca']['mobile'][$mois];
    $total2=$list2['ca'][''][$mois]+$list2['ca']['mp'][$mois]+$list2['ca']['sav'][$mois]+$list2['ca']['avoir'][$mois]+$list2['ca']['mobile'][$mois];
    $nb=$list['nb'][''][$mois]+$list['nb']['mp'][$mois]+$list['nb']['sav'][$mois]+$list['nb']['avoir'][$mois]+$list['nb']['mobile'][$mois];
    $nb1=$list1['nb'][''][$mois]+$list1['nb']['mp'][$mois]+$list1['nb']['sav'][$mois]+$list1['nb']['avoir'][$mois]+$list1['nb']['mobile'][$mois];
    $nb2=$list2['nb'][''][$mois]+$list2['nb']['mp'][$mois]+$list2['nb']['sav'][$mois]+$list2['nb']['avoir'][$mois]+$list2['nb']['mobile'][$mois];
    $total_objectif=$objectif_facture[$year][$mois]+$objectif_mp[$year][$mois];
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
    print "<tr><td>".$mois."-".$year."</td>
      <td class=\"ca_nb\">".number_format($total_objectif,2,',',' ')."&euro;</td>
      ".tdvalue($total,"&euro;",null,$total_objectif)
      .tdvalue($encours,"&euro;",true)
      .tdvalue($pcencours,"%",true)
      .tdvalue($total-$total1,"&euro;",true)
      .tdvalue($nm1,"%",true)
      .tdvalue($total-$total2,"&euro;",true)
      .tdvalue($nm2,"%",true)."
    </tr>";

        $C_panier_current[$cur] = $total;
        $C_panier_n1[$cur] = $total1;
        $C_panier_n2[$cur] = $total2;
        $cur++;

}
$objtotal=array_sum($objectif_facture[$year])+array_sum($objectif_mp[$year]);
if(isset($objectif_sav[$year])) $objtotal+=array_sum($objectif_sav[$year]);
$total=array_sum($list['ca'][''])+array_sum($list['ca']['mp'])+array_sum($list['ca']['sav'])+array_sum($list['ca']['avoir'])+array_sum($list['ca']['mobile']);
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
    .tdvalue($total-$anencours,"&euro;",true)
    .tdvalue(100*($total-$anencours)/$anencours,"%",true)
    .tdvalue($total-$total1,"&euro;",true)
    .tdvalue(100*($total-$total1)/$total1,"%",true)
    .tdvalue($total-$total2,"&euro;",true)
    .tdvalue(100*($total-$total2)/$total2,"%",true)
."</tr></table>";



// courbes



//FONCTION C_INSCRIT_CUR

$C_inscrits_current_n1 = c_inscrit_cur($year - 1);
$C_inscrits_current_n2 = c_inscrit_cur($year - 2);




//FONCTION C_INSCRIT
$C_inscrit_current = c_inscrit($year);
$C_inscrit_n1 = c_inscrit($year - 1);
$C_inscrit_n2 = c_inscrit($year - 2);


//FONCTION C_COMMANDE
$C_commandes_current = c_commande($year);
$C_commandes_n1 = c_commande($year - 1);
$C_commandes_n2 = c_commande($year - 2);


//FONCTION C_COMMANDE_CUR
$C_com_current_n1 = c_commande_cur($year - 1);
$C_com_current_n2 = c_commande_cur($year - 2);


//FONCTION C_PANIER

$compteur = 0;
$i = 1;
while ($compteur <= 12)
{
  if ($C_commandes_current[$compteur] > 0)
  $C_panier_current[$compteur] = $C_panier_current[$compteur] / $C_commandes_current[$compteur];
  else
  $C_panier_current[$compteur] = 0;

  if ($C_commandes_n1[$compteur] > 0)
  $C_panier_n1[$compteur] = $C_panier_n1[$compteur] / $C_commandes_n1[$compteur];
  else
  $C_panier_n1[$compteur] = 0;

  if ($C_commandes_n2[$compteur] > 0)
  $C_panier_n2[$compteur] = $C_panier_n2[$compteur] / $C_commandes_n2[$compteur];
  else
  $C_panier_n2[$compteur] = 0;



  $total_panier[$compteur] = $C_panier_current[$compteur];
  $total_panier_n1[$compteur] = $C_panier_n1[$compteur];
  $total_panier_n2[$compteur] = $C_panier_n2[$compteur];
  $compteur++;
  $i++;
}



echo "<p><br></p>
<table class=\"ca\">
  <tr>
      <td colspan=\"7\" class=\"ca_second_title\">Inscrits</td>
      <td colspan=\"1\" style=\"background-color:#ffffff; border: 1px solid #ffffff; border-right:1px solid black;\"></td>
      <td colspan=\"6\" class=\"ca_second_title\">Commandes</td>
      <td colspan=\"1\" style=\"background-color:#ffffff; border: 1px solid #ffffff; border-right:1px solid black;\"></td>
      <td colspan=\"5\" class=\"ca_second_title\">Panier moyen</td>
  <tr class=\"ca_head\">
      <td></td>
      <td>Nbr inscrits</td>
      <td>Nbr inscrits N-1</td>
      <td>Av/ret N-1</td>
      <td>% N-1</td>
      <td>Nbr inscrits N-2</td>
      <td>% N-2</td>
      <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px;\"></td>
      <td>Nbr Commandes</td>
      <td>Nbr Commandes N-1</td>
      <td>Av/ret N-1</td>
      <td>% N-1</td>
      <td>Nbr Commandes N-2</td>
      <td>% N-2</td>
      <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px;\"></td>
      <td>Panier moyen</td>
      <td>Panier moyen N-1</td>
      <td>% N-1</td>
      <td>Panier moyen N-2</td>
      <td>% N-2</td>
</tr>";

// echo "<tr><td>Report</td><td></td><td></td><td></td><td></td><td></td><td></td><td class=\"ca_nb\" onmouseover=\"showpopup('reportsite1')\" onmouseout=\"hidepopup('reportsite1')\">".number_format($report_site[1],2,',','')." &euro;<div id=\"reportsite1\" class=\"popup\">".formatlist("report1","")."</div></td><td class=\"ca_nb\" onmouseover=\"showpopup('reportsite0')\" onmouseout=\"hidepopup('reportsite0')\">".number_format($report_site[0],2,',','')." &euro;<div id=\"reportsite0\" class=\"popup\">".formatlist("report0","")."</div></td><td class=\"ca_nb\">".number_format($report_site[0]+$report_site[1],2,',','')." &euro;</td><td></td></tr>";
// round(((($C_inscrit_current[$a] * 100) / $C_inscrit_n1[$a]) - 100),2)

$anencours=0;
$total_valide=$total_encours=$total_attente=0;
$i = 1;
$a = 0;
while($i <= 12)
{
  $statn1 = round(((($C_inscrit_current[$a] * 100) / $C_inscrit_n1[$a]) - 100),2);
  $statn2 = round(((($C_inscrit_current[$a] * 100) / $C_inscrit_n2[$a]) - 100),2);
  $statn3 = round(((($C_commandes_current[$a] * 100) / $C_commandes_n1[$a]) - 100),2);
  $statn4 = round(((($C_commandes_current[$a] * 100) / $C_commandes_n2[$a]) - 100),2);

  if($year == $Y)
  {
    if($i<$m){
      $statn0 = $C_inscrit_current[$a] - $C_inscrits_current_n1[$a];
      $statn00 = $C_commandes_current[$a] - $C_com_current_n1[$a];
      $statn1 = round(((($C_inscrit_current[$a] * 100) / $C_inscrit_n1[$a]) - 100),2);
      $statn2 = round(((($C_inscrit_current[$a] * 100) / $C_inscrit_n2[$a]) - 100),2);
      $statn3 = round(((($C_commandes_current[$a] * 100) / $C_commandes_n1[$a]) - 100),2);
      $statn4 = round(((($C_commandes_current[$a] * 100) / $C_commandes_n2[$a]) - 100),2);
    }
      if($i==$m){
        $statn0 = $C_inscrit_current[$a] - $C_inscrits_current_n1[$a];
        $statn00 = $C_inscrit_current[$a] - $C_com_current_n1[$a];
        $statn1 = round(((($C_inscrit_current[$a] * 100) / $C_inscrits_current_n1[$a]) - 100),2);
        $statn2 = round(((($C_inscrit_current[$a] * 100) / $C_inscrits_current_n2[$a]) - 100),2);
        $statn3 = round(((($C_commandes_current[$a] * 100) / $C_commandes_n1[$a]) - 100),2);
        $statn4 = round(((($C_commandes_current[$a] * 100) / $C_commandes_n2[$a]) - 100),2);
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
      $statn0 = $C_inscrit_current[$a] - $C_inscrit_n1[$a];
      $statn00 = $C_commandes_current[$a] - $C_commandes_n1[$a];
      $statn1 = round(((($C_inscrit_current[$a] * 100) / $C_inscrit_n1[$a]) - 100),2);
      $statn2 = round(((($C_inscrit_current[$a] * 100) / $C_inscrit_n2[$a]) - 100),2);
      $statn3 = round(((($C_commandes_current[$a] * 100) / $C_commandes_n1[$a]) - 100),2);
      $statn4 = round(((($C_commandes_current[$a] * 100) / $C_commandes_n2[$a]) - 100),2);
  }

  $statn5 = round(((($C_panier_current[$a] * 100) / $C_panier_n1[$a]) - 100),2);
  $statn6 = round(((($C_panier_current[$a] * 100) / $C_panier_n2[$a]) - 100),2);

  if ($statn0 < 0){
    $class0 = 'stat_rouge';
    if ($statn0 == -1000000)
      $statn0 = NULL;
  }
  else{
    $statn0 = "+".$statn0;
    $class0 = 'stat_vert';
  }


  if ($statn00 < 0){
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
      <td style=\"text-align:right\">".$C_inscrit_current[$a]."</td>
      <td style=\"text-align:right\">".$C_inscrit_n1[$a]."</td>
      <td class=\"".$class0."\">".$statn0."</td>
      <td class=\"".$class."\">".$statn1."</td>
      <td style=\"text-align:right\">".$C_inscrit_n2[$a]."</td>
      <td class=\"".$class2."\">".$statn2."</td>
      <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px;\"></td>
      <td style=\"text-align:right\">".$C_commandes_current[$a]."</td>
      <td style=\"text-align:right\">".$C_commandes_n1[$a]."</td>
      <td class=\"".$class00."\">".$statn00."</td>
      <td class=\"".$class3."\">".$statn3."</td>
      <td style=\"text-align:right\">".$C_commandes_n2[$a]."</td>
      <td class=\"".$class4."\">".$statn4."</td>
      <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px;\"></td>
      <td style=\"text-align:right\">".round($C_panier_current[$a],2)."&euro;</td>
      <td style=\"text-align:right\">".round($C_panier_n1[$a],2)."&euro;</td>
      <td class=\"".$class5."\">".$statn5."</td>
      <td style=\"text-align:right\">".round($C_panier_n2[$a],2)."&euro;</td>
      <td class=\"".$class6."\">".$statn6."</td>
    </tr>";
    $statn1 = 0;
  $a++;
  $i++;
}
 
$total_inscrits = c_inscrit($year);
$total_inscrits = $total_inscrits[0] + $total_inscrits[1] + $total_inscrits[2] + $total_inscrits[3] + $total_inscrits[4] + $total_inscrits[5] + $total_inscrits[6] + $total_inscrits[7] + $total_inscrits[8] + $total_inscrits[9] + $total_inscrits[10] + $total_inscrits[11];

$total_inscrits_n1 = c_inscrit($year - 1);
$total_inscrits_n1 = $total_inscrits_n1[0] + $total_inscrits_n1[1] + $total_inscrits_n1[2] + $total_inscrits_n1[3] + $total_inscrits_n1[4] + $total_inscrits_n1[5] + $total_inscrits_n1[6] + $total_inscrits_n1[7] + $total_inscrits_n1[8] + $total_inscrits_n1[9] + $total_inscrits_n1[10] + $total_inscrits_n1[11];
$total_inscrits_n1_cur = $C_inscrits_current_n1[0] + $C_inscrits_current_n1[1] + $C_inscrits_current_n1[2] + $C_inscrits_current_n1[3] + $C_inscrits_current_n1[4] + $C_inscrits_current_n1[5] + $C_inscrits_current_n1[6] + $C_inscrits_current_n1[7] + $C_inscrits_current_n1[8] + $C_inscrits_current_n1[9] + $C_inscrits_current_n1[10] + $C_inscrits_current_n1[11];

$total_inscrits_n2 = c_inscrit($year - 2);
$total_inscrits_n2 = $total_inscrits_n2[0] + $total_inscrits_n2[1] + $total_inscrits_n2[2] + $total_inscrits_n2[3] + $total_inscrits_n2[4] + $total_inscrits_n2[5] + $total_inscrits_n2[6] + $total_inscrits_n2[7] + $total_inscrits_n2[8] + $total_inscrits_n2[9] + $total_inscrits_n2[10] + $total_inscrits_n2[11];
$total_inscrits_n2_cur = $C_inscrits_current_n2[0] + $C_inscrits_current_n2[1] + $C_inscrits_current_n2[2] + $C_inscrits_current_n2[3] + $C_inscrits_current_n2[4] + $C_inscrits_current_n2[5] + $C_inscrits_current_n2[6] + $C_inscrits_current_n2[7] + $C_inscrits_current_n2[8] + $C_inscrits_current_n2[9] + $C_inscrits_current_n2[10] + $C_inscrits_current_n2[11];


if ($year == $Y)
{
  $total_stat_ins_n1 = round(((($total_inscrits * 100) / $total_inscrits_n1_cur) - 100),2);
  $total_stat_ins_n2 = round(((($total_inscrits * 100) / $total_inscrits_n2_cur) - 100),2);
  $total_stat_avret_n1 = $total_inscrits - $total_inscrits_n1_cur;
}
elseif ($year < $Y)
{
  $total_stat_ins_n1 = round(((($total_inscrits * 100) / $total_inscrits_n1) - 100),2);
  $total_stat_ins_n2 = round(((($total_inscrits * 100) / $total_inscrits_n2) - 100),2);
  $total_stat_avret_n1 = $total_inscrits - $total_inscrits_n1;
}

  if ($total_stat_avret_n1 < 0)
  {
    $class07 = 'stat_rouge';
  }
  else {
    $total_stat_avret_n1 = "+".$total_stat_avret_n1;
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


$total_factures = c_commande($year);
$total_factures = $total_factures[0] + $total_factures[1] + $total_factures[2] + $total_factures[3] + $total_factures[4] + $total_factures[5] + $total_factures[6] + $total_factures[7] + $total_factures[8] + $total_factures[9] + $total_factures[10] + $total_factures[11];

$total_factures_n1 = c_commande($year - 1);
$total_factures_n1 = $total_factures_n1[0] + $total_factures_n1[1] + $total_factures_n1[2] + $total_factures_n1[3] + $total_factures_n1[4] + $total_factures_n1[5] + $total_factures_n1[6] + $total_factures_n1[7] + $total_factures_n1[8] + $total_factures_n1[9] + $total_factures_n1[10] + $total_factures_n1[11];
$total_factures_n1_cur = $C_com_current_n1[0] + $C_com_current_n1[1] + $C_com_current_n1[2] + $C_com_current_n1[3] + $C_com_current_n1[4] + $C_com_current_n1[5] + $C_com_current_n1[6] + $C_com_current_n1[7] + $C_com_current_n1[8] + $C_com_current_n1[9] + $C_com_current_n1[10] + $C_com_current_n1[11];

$total_factures_n2 = c_commande($year - 2);
$total_factures_n2 = $total_factures_n2[0] + $total_factures_n2[1] + $total_factures_n2[2] + $total_factures_n2[3] + $total_factures_n2[4] + $total_factures_n2[5] + $total_factures_n2[6] + $total_factures_n2[7] + $total_factures_n2[8] + $total_factures_n2[9] + $total_factures_n2[10] + $total_factures_n2[11];
$total_factures_n2_cur = $C_com_current_n2[0] + $C_com_current_n2[1] + $C_com_current_n2[2] + $C_com_current_n2[3] + $C_com_current_n2[4] + $C_com_current_n2[5] + $C_com_current_n2[6] + $C_com_current_n2[7] + $C_com_current_n2[8] + $C_com_current_n2[9] + $C_com_current_n2[10] + $C_com_current_n2[11];



$total_stat_fac_n2 = round(((($total_factures * 100) / $total_factures_n2) - 100),2);
$total_stat_fac_n1 = round(((($total_factures * 100) / $total_factures_n1) - 100),2);


if ($year == $Y)
{
  $total_stat_fac_n1 = round(((($total_factures * 100) / $total_factures_n1_cur) - 100),2);
  $total_stat_fac_n2 = round(((($total_factures * 100) / $total_factures_n2_cur) - 100),2);
  $total_stat_avret_comn1 = $total_factures - $total_factures_n1_cur;
}
elseif ($year < $Y)
{
  $total_stat_fac_n1 = round(((($total_factures * 100) / $total_factures_n1) - 100),2);
  $total_stat_fac_n2 = round(((($total_factures * 100) / $total_factures_n2) - 100),2);
  $total_stat_avret_comn1 = $total_factures - $total_factures_n1;
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






if ($year < $Y)
{
$total_panier = ($total_panier[0] + $total_panier[1] + $total_panier[2] + $total_panier[3] + $total_panier[4] + $total_panier[5] + $total_panier[6] + $total_panier[7] + $total_panier[8] + $total_panier[9] + $total_panier[10] + $total_panier[11]) / 12;

$total_panier_n1 = ($total_panier_n1[0] + $total_panier_n1[1] + $total_panier_n1[2] + $total_panier_n1[3] + $total_panier_n1[4] + $total_panier_n1[5] + $total_panier_n1[6] + $total_panier_n1[7] + $total_panier_n1[8] + $total_panier_n1[9] + $total_panier_n1[10] + $total_panier_n1[11]) / 12;

$total_panier_n2 = ($total_panier_n2[0] + $total_panier_n2[1] + $total_panier_n2[2] + $total_panier_n2[3] + $total_panier_n2[4] + $total_panier_n2[5] + $total_panier_n2[6] + $total_panier_n2[7] + $total_panier_n2[8] + $total_panier_n2[9] + $total_panier_n2[10] + $total_panier_n2[11]) / 12;
}
elseif($year == $Y)
{
$total_panier = ($total_panier[0] + $total_panier[1] + $total_panier[2] + $total_panier[3] + $total_panier[4] + $total_panier[5] + $total_panier[6] + $total_panier[7] + $total_panier[8] + $total_panier[9] + $total_panier[10] + $total_panier[11]) / $m;

$total_panier_n1 = ($total_panier_n1[0] + $total_panier_n1[1] + $total_panier_n1[2] + $total_panier_n1[3] + $total_panier_n1[4] + $total_panier_n1[5] + $total_panier_n1[6] + $total_panier_n1[7] + $total_panier_n1[8] + $total_panier_n1[9] + $total_panier_n1[10] + $total_panier_n1[11]) / $m;

$total_panier_n2 = ($total_panier_n2[0] + $total_panier_n2[1] + $total_panier_n2[2] + $total_panier_n2[3] + $total_panier_n2[4] + $total_panier_n2[5] + $total_panier_n2[6] + $total_panier_n2[7] + $total_panier_n2[8] + $total_panier_n2[9] + $total_panier_n2[10] + $total_panier_n2[11]) / $m;
}

$total_stat_pan_n1 = round(((($total_panier * 100) / $total_panier_n1) - 100),2);

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


$total_stat_pan_n2 = round(((($total_panier * 100) / $total_panier_n2) - 100),2);

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
  <td class=\"ca_nb\">".$total_inscrits."</td>
  <td class=\"ca_nb\">".$total_inscrits_n1."</td>
  <td class=\"".$class07."\">".$total_stat_avret_n1."</td>
  <td class=\"".$class7."\">".$total_stat_ins_n1."</td>
  <td class=\"ca_nb\">".$total_inscrits_n2."</td>
  <td class=\"".$class8."\">".$total_stat_ins_n2."</td>
  <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
  <td class=\"ca_nb\">".$total_factures."</td>
  <td class=\"ca_nb\">".$total_factures_n1."</td>
  <td class=\"".$class09."\">".$total_stat_avret_comn1."</td>
  <td class=\"".$class9."\">".$total_stat_fac_n1."</td>
  <td class=\"ca_nb\">".$total_factures_n2."</td>
  <td class=\"".$class10."\">".$total_stat_fac_n2."</td>
  <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
  <td class=\"ca_nb\">".round($total_panier,2)."&euro;</td>
  <td class=\"ca_nb\">".round($total_panier_n1,2)."&euro;</td>
  <td class=\"".$class11."\">".($total_stat_pan_n1)."</td>
  <td class=\"ca_nb\">".round($total_panier_n2,2)."&euro;</td>
  <td class=\"".$class12."\">".($total_stat_pan_n2)."</td>
</tr>";

echo "</table>";

echo "<br>";
echo "<br>";
echo "<br>";
include "./footer.php";

function ca($annee,$to=null,$and=""){
    
    global $DB_site2;

    if(is_null($to)) $to = mktime(23,59,59,12,31,$annee);

    $ca = $nb = array(
        ""      => array(),
        "mp"    => array(),
        "sav"   => array(),
        "avoir" => array(),
        "mobile" => array()
    );

    //Remise HT = TOT HT hors remise x (Remise TTC / TOT TTC hors remise)
    $sql = "SELECT f.facturid, f.heurefacture, f.provenance, SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) as 'prixHT', f.prixport / (1 + f.tvaport / 100) as 'portHT', CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) + (f.prixport / (1 + f.tvaport / 100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END as remiseHT";
    //$sql = "SELECT f.facturid, f.heurefacture, f.provenance, SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) as 'prixHT', f.prixport / (1 + f.tvaport / 100) as 'portHT', (SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) + (f.prixport / (1 + f.tvaport / 100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) as remiseHT";
    $sql .= " FROM facture f";
    $sql .= " INNER JOIN lignefacture lf ON (lf.factureid = f.facturid)";
    $sql .= " WHERE f.heurefacture BETWEEN ".mktime(0,0,0,1,1,$annee)." AND ".$to;
    $sql .= " AND f.valide IN (1,5,7,8)";
    $sql .= " ".$and;
    $sql .= " GROUP BY f.facturid";
    $req = $DB_site2->query($sql);
    while($facture = $DB_site2->fetch_array($req)){
        //$f = new facture($DB_site2,$facture);

        $month = date("n",$facture['heurefacture']);
        $ca[$facture['provenance']][$month] += $facture['prixHT'] + $facture['portHT'] - $facture['remiseHT'];
        $nb[$facture['provenance']][$month]++;
    }

    return array(
        "ca" => $ca,
        "nb" => $nb
    );
}

function tdvalue($value,$sym="",$sign=null,$climit=0){
  if($value=="") return "<td></td>";
  return "<td class=\"ca_nb\" style=\"color:".($value>$climit?"green":"red").";\">".($sign&&$value>0?"+":"").number_format($value,2,',',' ').$sym."</td>";
}

?>