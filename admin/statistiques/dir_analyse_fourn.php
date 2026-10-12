<?php

require "../admin_global.php";
require "functions_diranalysefourn.php";
include "../header.php";
include "menu_2.php";
include "../../class/facture.class.php";
//ini_set("display_errors",0);



















echo "
<style>
.stat_rouge {color:red; text-align: right;}
.stat_vert {color:green; text-align: right;}
 tr:hover { background-color: #ffff99;}
.ca { color:black; border-collapse:collapse; border:solid thin black; width:100%;}
.ca td { border:solid thin black; }
.ca_nb { text-align:right; white-space:nowrap; }
.ca_title { background-color:#4CAF50; text-align:center; color:white;}
.ca_second_title { background-color:#af504c; text-align:center; color:white;}
.ca_head { font-weight:bold; text-align:center; }
.ca_bold { font-weight:bold; }
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

<h1>Analyse Fournisseurs</h1>";?>


<?php
include(BACK_PATH.'/statistiques/statistiques_adm.php');

echo "<div style=\"margin-top:3%; margin-bottom:3%;\">";
echo "<form method=\"post\" name=\"infos\" action=\"dir_analyse_fourn.php\">";



if(!isset($year)) $year=date("Y");
echo "Année : <select name=\"year\">";
for($y=2010;$y<=(2009+count($objectif_facture));$y++) echo "<option value=\"$y\"".($y==$year?" selected":"").">$y</option>";
echo "</select>";

echo "&nbsp;";
echo "&nbsp;";

$sql = "";
$cams = "";
$i = 0;
$sql = "SELECT societe FROM fournisseur_condition ORDER BY societe ASC";

  $cams = $DB_site2->query($sql);
  while ($row = $DB_site2->fetch_array($cams))
    {
     $Societe[] = $row['societe'];
     $i++;
    }


echo "Type : <select name=\"type\">";
while ($compteur <= $i)
{
  echo "<option value=\"".$Societe[$compteur]."\">".$Societe[$compteur]."</option>";
  $compteur++;
}
  echo "</select>";



echo "&nbsp;";
echo "&nbsp;";

 echo "<input value=\"valider\" type=\"submit\">";
 echo "</form>";
 echo "</div>";



if ($type != "")
{

  echo "<table class=\"ca\">
    <tr><td colspan=\"15\" class=\"ca_second_title\">".$type."</td></tr>
    <tr class=\"ca_head\">
      <td>Mois</td>
      <td>Facturation &euro;HT</td>
      <td>Facturation N-1 global en &euro;HT</td>
      <td>Av/ret N-1 &euro;HT</td>
      <td>% N-1</td>
      <td>Facturation N-2 global en &euro;HT</td>
      <td>Av/ret N-2</td>
      <td>% N-2</td>
      <td>Nbr Facture</td>
      <td>Nbr Facture N-1</td>
      <td>Av/ret N-1</td>
      <td>% N-1</td>
      <td>Nbr Facture N-2</td>
      <td>Av/ret N-2</td>
      <td>% N-2</td>
    </tr>";


    $a = 0;
    $mois[0] = "Janvier";
    $mois[1] = "Fevrier";
    $mois[2] = "Mars";
    $mois[3] = "Avril";
    $mois[4] = "Mai";
    $mois[5] = "Juin";
    $mois[6] = "Juillet";
    $mois[7] = "Aout";
    $mois[8] = "Septembre";
    $mois[9] = "Octobre";
    $mois[10] = "Novembre";
    $mois[11] = "Decembre";

    $tab_fac = get_fac($type, $year);
    $tab_fac_n1global = get_fac($type, $year - 1);
    $tab_fac_n2global = get_fac($type, $year - 2);


    $tab_nbrfac = get_nbrfac($type, $year);
    $tab_nbrfac_n1global = get_nbrfac($type, $year - 1);
    $tab_nbrfac_n2global = get_nbrfac($type, $year - 2);

if($year == date(Y))
{
   $tab_fac_avn1 = get_fac_tr($type, $year - 1);
   $tab_fac_avn2 = get_fac_tr($type, $year - 2);

   $tab_nbrfac_avn1 = get_nbrfac_tr($type, $year - 1);
   $tab_nbrfac_avn2 = get_nbrfac_tr($type, $year - 2);
}
elseif($year < date(Y))
{
   $tab_fac_avn1 = get_fac($type, $year - 1);
   $tab_fac_avn2 = get_fac($type, $year - 2);

   $tab_nbrfac_avn1 = get_nbrfac($type, $year - 1);
   $tab_nbrfac_avn2 = get_nbrfac($type, $year - 2);
}

  for($i=1;$i<=12;$i++)
  {


    if ($tab_fac_avn1[$a] > 0)
      $per_fac_n1 = ((100 * $tab_fac[$a]) / $tab_fac_avn1[$a]) - 100;
    else
      $per_fac_n1 = "";

    if ($tab_fac_avn2[$a] > 0)
      $per_fac_n2 = ((100 * $tab_fac[$a]) / $tab_fac_avn2[$a]) - 100;
    else
      $per_fac_n2 = "";


    if ($tab_nbrfac_avn1[$a] > 0)
      $per_nbrfac_n1 = ((100 * $tab_nbrfac[$a]) / $tab_nbrfac_avn1[$a]) - 100;
    else
      $per_nbrfac_n1 = "";

    if ($tab_nbrfac_avn2[$a] > 0)
      $per_nbrfac_n2 = ((100 * $tab_nbrfac[$a]) / $tab_nbrfac_avn2[$a]) - 100;
    else
      $per_nbrfac_n2 = "";

    $tab_fac_avn1[$a] = $tab_fac[$a] - $tab_fac_avn1[$a];
    $tab_fac_avn2[$a] = $tab_fac[$a] - $tab_fac_avn2[$a];

    $tab_nbrfac_avn1[$a] = $tab_nbrfac[$a] - $tab_nbrfac_avn1[$a];
    $tab_nbrfac_avn2[$a] = $tab_nbrfac[$a] - $tab_nbrfac_avn2[$a];

    echo "<tr>
    <td>".$mois[$a]."</td>
    ".tdvalue_nocolor($tab_fac[$a],"&euro;")."
    ".tdvalue_nocolor($tab_fac_n1global[$a],"&euro;")."
    ".tdvalue($tab_fac_avn1[$a],"&euro;",true)."
    ".tdvalue($per_fac_n1,"%",true)."
    ".tdvalue_nocolor($tab_fac_n2global[$a],"&euro;")."
    ".tdvalue($tab_fac_avn2[$a],"&euro;",true)."
    ".tdvalue($per_fac_n2,"%",true)."
    ".tdvalue_nocolornocoma($tab_nbrfac[$a])."
    ".tdvalue_nocolornocoma($tab_nbrfac_n1global[$a])."
    ".tdvalue_nocoma($tab_nbrfac_avn1[$a],"",true)."
    ".tdvalue($per_nbrfac_n1,"%",true)."
    ".tdvalue_nocolornocoma($tab_nbrfac_n2global[$a],"")."
    ".tdvalue_nocoma($tab_nbrfac_avn2[$a],"",true)."
    ".tdvalue($per_nbrfac_n2,"%",true)."
    </tr>";
    $a++;
  }
$cur_year = $year;

$total_tabfac = sum_twelve_ht($tab_fac, $cur_year, $year, $type);
$total_tabfac_n1global_basic = sum_twelve_basic($tab_fac_n1global, $year - 1);
$total_tabfac_n1global = sum_twelve_ht($tab_fac_n1global, $cur_year, $year - 1, $type);
$total_tabfac_avn1 = $total_tabfac - $total_tabfac_n1global;

if ($total_tabfac_n1global > 0)
  $total_per_fac_n1 = ((100 * $total_tabfac) / $total_tabfac_n1global) - 100;
else
  $total_per_fac_n1 = "";

$total_tabfac_n2global = sum_twelve_ht($tab_fac_n2global, $cur_year, $year - 2, $type);
$total_tabfac_n2global_basic = sum_twelve_basic($tab_fac_n2global, $year - 1);
$total_tabfac_avn2 = $total_tabfac - $total_tabfac_n2global;

if ($total_tabfac_n2global > 0)
  $total_per_fac_n2 = ((100 * $total_tabfac) / $total_tabfac_n2global) - 100;
else
  $total_per_fac_n2 = "";


$total_nbrfac = sum_twelve_fac($tab_nbrfac, $cur_year, $year, $type);
$total_nbrfac_n1global_basic = sum_twelve_basic($tab_nbrfac_n1global, $year - 1);
$total_nbrfac_n1global = sum_twelve_fac($tab_nbrfac_n1global, $cur_year, $year - 1, $type);
$total_nbrfac_avn1 = $total_nbrfac - $total_nbrfac_n1global;

if ($total_nbrfac_n1global > 0)
  $total_nbrper_fac_n1 = ((100 * $total_nbrfac) / $total_nbrfac_n1global) - 100;
else
  $total_nbrper_fac_n1 = "";

$total_nbrfac_n2global = sum_twelve_fac($tab_nbrfac_n2global, $cur_year, $year -2, $type);
$total_nbrfac_n2global_basic = sum_twelve_basic($tab_nbrfac_n2global, $cur_year, $year - 1);
$total_nbrfac_avn2 = $total_nbrfac - $total_nbrfac_n2global;

if ($total_nbrfac_n2global > 0)
  $total_nbrper_fac_n2 = ((100 * $total_nbrfac) / $total_nbrfac_n2global) - 100;
else
  $total_nbrper_fac_n2 = "";


    echo "<tr class=\"ca_bold\">
    <td>Total</td>
    ".tdvalue_nocolor($total_tabfac,"&euro;")."
    ".tdvalue_nocolor($total_tabfac_n1global_basic,"&euro;")."
    ".tdvalue($total_tabfac_avn1,"&euro;",true)."
    ".tdvalue($total_per_fac_n1,"%",true)."
    ".tdvalue_nocolor($total_tabfac_n2global_basic,"&euro;")."
    ".tdvalue($total_tabfac_avn2,"&euro;",true)."
    ".tdvalue($total_per_fac_n2,"%",true)."
    ".tdvalue_nocolornocoma($total_nbrfac)."
    ".tdvalue_nocolornocoma($total_nbrfac_n1global_basic)."
    ".tdvalue_nocoma($total_nbrfac_avn1,"",true)."
    ".tdvalue($total_nbrper_fac_n1,"%",true)."
    ".tdvalue_nocolornocoma($total_nbrfac_n2global_basic,"")."
    ".tdvalue_nocoma($total_nbrfac_avn2,"",true)."
    ".tdvalue($total_nbrper_fac_n2,"%",true)."
    </tr>";

echo "</table>";
}


echo "<br><br><br>";
//include "./footer.php";


?>