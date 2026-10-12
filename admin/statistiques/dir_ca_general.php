<?php
require "../admin_global.php";
require "functions_dircageneral.php";
include "../header.php";
include "menu_2.php";
include "../../class/facture.class.php";
ini_set("display_errors",0);

//Recherche l'année la plus récente pour laquelle les objectifs ont été définis
$sql = "SELECT MAX(obj_annee) as annee_max";
$sql .= " FROM objectifs_enregistres";
$annee_max = $DB_site2->query_first($sql)[0];

echo "
<style>
.stat_rouge {color:red; text-align: right;}
.stat_vert {color:green; text-align: right;}
.ca { color:black; border-collapse:collapse; border:solid thin black; }
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

<h1>Chiffre d'affaire facturé</h1>";?>


<?php
include(BACK_PATH.'/statistiques/statistiques_adm.php');

if(!isset($year)) $year=date("Y");
echo "<p style=\"margin-top:40px; margin-bottom:15px;\"align=\"center\">Année : <select name=\"year\" onchange=\"document.location='dir_ca_general.php?year='+this.value\">";
for($y=2010;$y<=$annee_max;$y++) echo "<option value=\"$y\"".($y==$year?" selected":"").">$y</option>";

// PREMIERE PARTE DU TABLEAU

echo "</select></p>
<table class=\"ca\">
  <tr><td colspan=\"14\" class=\"ca_second_title\">Chiffre d'affaires facturé</td></tr>
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

$listfacture=array();
$Y=date("Y");
$m=date("n");
$ca_encours_mp=$ca_encours=$ca_attente=$ca_encours_sav=$ca_encours_mobile=array();
$total_valide=$total_encours=$total_attente=$total_valide_mp=0;
$ca_validem1=$ca_validem2=$ca_validem1_mp=$ca_validem2_mp=$ca_validem1_sav=$ca_validem2_sav=$ca_validem1_mobile=$ca_validem2_mobile=array();

$ca_encours_mp=ca_valide(null,$year,"mp");
$ca_encours=ca_valide(null,$year);
$ca_attente=ca_attente(null,$year);
$ca_encours_sav=ca_valide(null,$year,"sav");
$ca_encours_mobile=ca_valide(null,$year,"mobile");

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








//ON REMPLI LE TABLEAU  - 12 MOIS + TOTAL A LA FIN
$cur = 0;
$a = 0;
for($i=1;$i<=12;$i++)
 {
        $ca_valide[$i]=ca($i,$year,"5,8","");
        $ca_valide_mp[$i]=ca($i,$year,"5,8","mp");
        $ca_valide_sav[$i]=ca($i,$year,"5,8","sav")+ca($i,$year,"5,8","avoir");
        $ca_valide_mobile[$i]=ca($i,$year,"5,8","mobile");

        $objectif_mois= $total[$a];
        $a++;
        if(isset($objectif_sav[$year][$i])) $objectif_mois+=$objectif_sav[$year][$i];
        
        $pcmois=100*($ca_valide_mp[$i]+$ca_valide[$i]+$ca_valide_sav[$i]+$ca_valide_mobile[$i])/($objectif_mois);
        $prevision=$ca_valide[$i]+$ca_attente[$i]+$ca_encours[$i]+$ca_valide_mp[$i]+$ca_encours_mp[$i]+$ca_valide_sav[$i]+$ca_encours_sav[$i]+$ca_valide_mobile[$i]+$ca_encours_mobile[$i];
        $pcprev=100*$prevision/($objectif_mois);
        $previsions="<td class=\"ca_nb\" onmouseover=\"showpopup('valide$i')\" onmouseout=\"hidepopup('valide$i')\">".number_format($ca_encours[$i]+$ca_encours_mp[$i]+$ca_encours_sav[$i]+$ca_encours_mobile[$i],2,',',' ')." &euro;<div id=\"valide$i\" class=\"popup\">".formatlist("valide",null,$i)."</div></td><td class=\"ca_nb\" onmouseover=\"showpopup('attente$i')\" onmouseout=\"hidepopup('attente$i')\">".number_format($ca_attente[$i],2,',',' ')." &euro;<div id=\"attente$i\" class=\"popup\">".formatlist("attente",null,$i)."</div></td><td class=\"ca_nb\">".number_format($prevision,2,',',' ')." &euro;</td>".tdvalue($pcprev,"%",false,100);
    	  
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
                   $ca_validem1_mobile[$i]=ca($i,($year-1),"5,8","mobile",$limit);
                   $ca_validem2_mobile[$i]=ca($i,($year-2),"5,8","mobile",$limit);
                }
            else
          	  $ca_validem1[$i]=$ca_validem2[$i]=$ca_validem1_mp[$i]=$ca_validem2_mp[$i]=$ca_validem1_sav[$i]=$ca_validem2_sav[$i]=$ca_validem1_mobile[$i]=$ca_validem2_mobile[$i]=0;
          }

        else
        {
      	  $ca_validem1[$i]=ca($i,($year-1),"5,8","");
       	  $ca_validem2[$i]=ca($i,($year-2),"5,8","");
      	  $ca_validem1_mp[$i]=ca($i,($year-1),"5,8","mp");
       	  $ca_validem2_mp[$i]=ca($i,($year-2),"5,8","mp");
      	  $ca_validem1_sav[$i]=ca($i,($year-1),"5,8","sav");
       	  $ca_validem2_sav[$i]=ca($i,($year-2),"5,8","sav");
          $ca_validem1_mobile[$i]=ca($i,($year-1),"5,8","mobile");
          $ca_validem2_mobile[$i]=ca($i,($year-2),"5,8","mobile");
        }
        
        $ca_valide_total=$ca_valide[$i]+$ca_valide_mp[$i]+$ca_valide_sav[$i]+$ca_valide_mobile[$i];
        $ca_validem1_total=$ca_validem1[$i]+$ca_validem1_mp[$i]+$ca_validem1_sav[$i]+$ca_validem1_mobile[$i];
        $ca_validem2_total=$ca_validem2[$i]+$ca_validem2_mp[$i]+$ca_validem2_sav[$i]+$ca_validem2_mobile[$i];
        
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
        elseif($year < $Y)
        {     
          $anencours+=$objectif_mois;
          $encours=$ca_valide_total-$objectif_mois;
          $pcencours=100*$encours/$objectif_mois;
        }
        echo "<tr>
          <td>".$i."-".$year."</td>
          <td class=\"ca_nb\">".number_format($objectif_mois,0,',',' ')." &euro;</td>
          <td class=\"ca_nb\">".number_format($ca_valide_total,2,',',' ')." &euro;</td>
          ".tdvalue($encours,"&euro;",true).
          tdvalue($pcencours,"%",true).
          tdvalue($ca_valide_total-$ca_validem1_total,"&euro;",true).
          tdvalue($nm1,"%",true).
          tdvalue($ca_valide_total-$ca_validem2_total,"&euro;",true).
          tdvalue($nm2,"%",true).
          $previsions.
        "</tr>";

        $C_panier_current[$cur] = $ca_valide_total;
        $C_panier_n1[$cur] = $ca_validem1_total;
        $C_panier_n2[$cur] = $ca_validem2_total;
        $cur++;
}













// A chaque fois qu'il y'a un if(year==Y || year<Y) c'est pour calculer du temps réel
// Si year == Y alors on utilise les formules de temps réel, sinon on fait les formules habituelles.


$total_attente=array_sum($ca_attente);
$total_encours=array_sum($ca_encours)+array_sum($ca_encours_mp)+array_sum($ca_encours_sav)+array_sum($ca_encours_mobile);
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
elseif($year < $Y)
{
  $encours=$total_valide-$anencours;
  $pcencours=100*$encours/$anencours;  
}

$total_validem1=array_sum($ca_validem1_mp)+array_sum($ca_validem1)+array_sum($ca_validem1_sav)+array_sum($ca_validem1_mobile);
$nm1=100*(array_sum($ca_valide_mp)+array_sum($ca_valide)+array_sum($ca_valide_mobile)+array_sum($ca_valide_sav)-$total_validem1)/$total_validem1;
$total_validem2=array_sum($ca_validem2_mp)+array_sum($ca_validem2)+array_sum($ca_validem2_sav)+array_sum($ca_validem2_mobile);
$nm2=100*(array_sum($ca_valide_mp)+array_sum($ca_valide)+array_sum($ca_valide_mobile)+array_sum($ca_valide_sav)-$total_validem2)/$total_validem2;







//TOTAL

echo "<tr style=\"font-weight:bold;\">
  <td>Total année $year</td>
  <td class=\"ca_nb\">".number_format($all_total,2,',',' ')." &euro;</td>
  <td class=\"ca_nb\">".number_format($total_valide,2,',',' ')." &euro;</td>".
  tdvalue($encours,"&euro;",true).
  tdvalue($pcencours,"%",true).
  tdvalue(array_sum($ca_valide_mp)+array_sum($ca_valide)+array_sum($ca_valide_mobile)+array_sum($ca_valide_sav)-$total_validem1,"&euro;",true).
  tdvalue($nm1,"%",true).
  tdvalue(array_sum($ca_valide_mp)+array_sum($ca_valide)+array_sum($ca_valide_mobile)+array_sum($ca_valide_sav)-$total_validem2,"&euro;",true).
  tdvalue($nm2,"%",true).
  "<td class=\"ca_nb\">".number_format($total_encours,2,',',' ')." &euro;</td>
  <td class=\"ca_nb\">".number_format($total_attente,2,',',' ')." &euro;</td>
  <td class=\"ca_nb\">".number_format($total_attente+$total_encours+$total_valide,2,',',' ')." &euro;</td>
  <td class=\"ca_nb\">".number_format($pcprev,2,',',' ')."%</td>
</tr>";

echo "</table>";




//FONCTION C_INSCRIT
$C_inscrit_current = c_inscrit($year);
$C_inscrit_n1 = c_inscrit($year - 1);
$C_inscrit_n2 = c_inscrit($year - 2);

//FONCTION C_INSCRIT_CUR
$C_inscrits_current_n1 = c_inscrit_cur($year - 1);
$C_inscrits_current_n2 = c_inscrit_cur($year - 2);

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










// NOUVEAU TABLEAU

echo "<p><br></p>
<table class=\"ca\">
  <tr>
      <td colspan=\"7\" class=\"ca_second_title\">Inscrits</td>
      <td colspan=\"1\" style=\"background-color:#ffffff; border: 1px solid #ffffff; border-right:1px solid black;\"></td>
      <td colspan=\"6\" class=\"ca_second_title\">Factures</td>
      <td colspan=\"1\" style=\"background-color:#ffffff; border: 1px solid #ffffff; border-right:1px solid black;\"></td>
      <td colspan=\"5\" class=\"ca_second_title\">Panier moyen</td>
  </tr>
  <tr class=\"ca_head\">
      <td></td>
      <td>Nbr inscrits</td>
      <td>Nbr inscrits N-1</td>
      <td>Av/ret N-1</td>
      <td>% N-1</td>
      <td>Nbr inscrits N-2</td>
      <td>% N-2</td>
      <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px;\"></td>
      <td>Nbr Facture</td>
      <td>Nbr Facture N-1</td>
      <td>Av/ret N-1</td>
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
















// ON REMPLIS LE TABLEAU 12 + total

$anencours=0;
$total_valide=$total_encours=$total_attente=0;
$i = 1;
$a = 0;
while($i <= 12)
{
  if($year == $Y)
  {
    if($i<$m)
    {
      $statn0 = $C_inscrit_current[$a] - $C_inscrits_current_n1[$a];
      $statn00 = $C_commandes_current[$a] - $C_com_current_n1[$a];
      $statn1 = round(((($C_inscrit_current[$a] * 100) / $C_inscrit_n1[$a]) - 100),2);
      $statn2 = round(((($C_inscrit_current[$a] * 100) / $C_inscrit_n2[$a]) - 100),2);
      $statn3 = round(((($C_commandes_current[$a] * 100) / $C_commandes_n1[$a]) - 100),2);
      $statn4 = round(((($C_commandes_current[$a] * 100) / $C_commandes_n2[$a]) - 100),2);
    }
      if($i==$m)
      {
        $statn0 = $C_inscrit_current[$a] - $C_inscrits_current_n1[$a];
        $statn00 = $C_commandes_current[$a] - $C_com_current_n1[$a];
        $statn1 = round(((($C_inscrit_current[$a] * 100) / $C_inscrits_current_n1[$a]) - 100),2);
        $statn2 = round(((($C_inscrit_current[$a] * 100) / $C_inscrits_current_n2[$a]) - 100),2);
        $statn3 = round(((($C_commandes_current[$a] * 100) / $C_commandes_n1[$a]) - 100),2);
        $statn4 = round(((($C_commandes_current[$a] * 100) / $C_commandes_n2[$a]) - 100),2);
      }
      if ($i > $m)
      {
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







  // CE QUI SUIT DETERMINE SI ON MET LE TEXTE EN ROUGE OU EN VERT, AVEC UN '+' OU UN '-' etc..

  if ($statn0 < 0)
  {
    $class0 = 'stat_rouge';
      if ($statn0 == -1000000)
        $statn0 = NULL;
  }
  else
  {
    $statn0 = "+".$statn0;
    $class0 = 'stat_vert';
  }



  if ($statn00 < 0)
  {
    $class00 = 'stat_rouge';
     if ($statn00 == -1000000)
        $statn00 = NULL;
  }
  else
  {
    $statn00 = "+".$statn00;
    $class00 = 'stat_vert';
  }



  if ($statn1 < 0)
  {
    $class = 'stat_rouge';
    $statn1 = $statn1."%";
      if($statn1 == -100)
        $statn1 = NULL;
  }
  else
  {
    $statn1 = "+".$statn1."%";
    $class = 'stat_vert';
  }



  if ($statn2 < 0)
  {
    $class2 = 'stat_rouge';
    $statn2 = $statn2."%";
      if($statn2 == -100)
        $statn2 = NULL;
  }
  else
  {
    $statn2 = "+".$statn2."%";
    $class2 = 'stat_vert';
  }



  if ($statn3 < 0)
  {
    $class3 = 'stat_rouge';
    $statn3 = $statn3."%";
      if($statn3 == -100)
        $statn3 = NULL;
  }
  else
  {
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






// REMPLISSAGE
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




  // CE QUI SUIT DETERMINE SI ON MET LE TEXTE EN ROUGE OU EN VERT, AVEC UN '+' OU UN '-' etc..

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



  // CE QUI SUIT DETERMINE SI ON MET LE TEXTE EN ROUGE OU EN VERT, AVEC UN '+' OU UN '-' etc..


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


?>