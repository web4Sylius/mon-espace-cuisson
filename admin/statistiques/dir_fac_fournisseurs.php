<?php

require "../admin_global.php";
require "functions_dirfacfournisseurs.php";
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
.ca_prc { text-align:center; white-space:nowrap; }
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

<h1>Facturation Fournisseurs</h1>";?>


<?php
include(BACK_PATH.'/statistiques/statistiques_adm.php');


echo "<div style=\"margin-top:3%; margin-bottom:3%;\">";
echo "<form method=\"post\" name=\"infos\" action=\"dir_fac_fournisseurs.php\">";


echo "Méthode de tri : ";

echo "<select name=\"tri\">";
echo "<option value=paht>Montant facturé</option>";
echo "<option value=alpha>Alphabétique</option>";
echo "</select>";

echo "&nbsp;";
echo "&nbsp;";

if(!isset($year)) $year=date("Y");
echo "Année : <select name=\"year\">";
for($y=2010;$y<=(2009+count($objectif_facture));$y++) echo "<option value=\"$y\"".($y==$year?" selected":"").">$y</option>";
echo "</select>";

echo "&nbsp;";
echo "&nbsp;";


$type2="Produits principaux";
$type3="Produits secondaire";
$type4="Produits étrangers";
$type5="Transporteur";
$type6="Installateur";
$type7="Garantie /sav";
$type8="Market place";
$type9="Service generaux";
echo "Type : <select name=\"type\">";
echo "<option value=\"Produits principaux\"".($type==$type2?" selected":"").">Produits principaux</option>";
echo "<option value=\"Produits secondaires\"".($type==$type3?" selected":"").">Produits secondaire</option>";
echo "<option value=\"Produits étrangers\"".($type==$type4?" selected":"").">Produits étrangers</option>";
echo "<option value=\"Transporteur\"".($type==$type5?" selected":"").">Transporteurs</option>";
echo "<option value=\"Installateur\"".($type==$type6?" selected":"").">Installateurs</option>";
echo "<option value=\"Garantie /sav\"".($type==$type7?" selected":"").">Garantie</option>";
echo "<option value=\"Market place\"".($type==$type8?" selected":"").">Market Places</option>";
echo "<option value=\"Service generaux\"".($type==$type9?" selected":"").">Services Generaux</option>";
echo "</select>";


echo "&nbsp;";
echo "&nbsp;";

 echo "<input value=\"valider\" type=\"submit\">";
 echo "</form>";
 echo "</div>";


if ($tri == "paht")
{
  $titre = "Montant facturé";
  $method = "IFNULL(SUM(ff.montant_ht),0) DESC";
}

if ($tri == "alpha")
{
  $titre = "Ordre alphabétique";
  $method = "fc.societe ASC";
}

echo "<table class=\"ca\">
  <tr><td colspan=\"14\" class=\"ca_second_title\">".$type." - ".$titre."</td></tr>
  <tr class=\"ca_head\">
    <td>Fournisseurs</td>
    <td>Facturation &euro;HT</td>
    <td>Facturation N-1 global en &euro;HT</td>
    <td>Av/ret N-1 &euro;HT</td>
    <td>% N-1</td>
    <td>Facturation N-2 global en &euro;HT</td>
    <td>Av/ret N-2</td>
    <td>% N-2</td>
  </tr>";



$a = 0;
if ($type != "")
{
  $tab_supply = get_supply($type);
  $size_long = count($tab_supply);
  $tab_fac = get_fac($type, $year, $method);
  $tab_fac_n1global = get_fac($type, $year - 1, $method);
  $tab_fac_n2global = get_fac($type, $year - 2, $method);

  $tab_fac_avn1 = get_fac_tr($type, $year - 1, $method);
  $tab_fac_avn2 = get_fac_tr($type, $year - 2, $method);
}

for($i=1;$i<=$size_long;$i++)
{


  if ($tab_fac_avn1[$a+1] > 0)
    $per_fac_n1 = ((100 * $tab_fac[$a+1]) / $tab_fac_avn1[$a+1]) - 100;
  else
    $per_fac_n1 = "";

  if ($tab_fac_avn2[$a+1] > 0)
    $per_fac_n2 = ((100 * $tab_fac[$a+1]) / $tab_fac_avn2[$a+1]) - 100;
  else
    $per_fac_n2 = "";

  $tab_fac_avn1[$a+1] = $tab_fac[$a+1] - $tab_fac_avn1[$a+1];
  $tab_fac_avn2[$a+1] = $tab_fac[$a+1] - $tab_fac_avn2[$a+1];

  echo "<tr>
  <td>".$tab_fac[$a]."</td>
  ".tdvalue_nocolor($tab_fac[$a+1],"&euro;")."
  ".tdvalue_nocolor($tab_fac_n1global[$a+1],"&euro;")."
  ".tdvalue($tab_fac_avn1[$a+1],"&euro;",true)."
  ".tdvalue_prc($per_fac_n1,"%",true)."
  ".tdvalue_nocolor($tab_fac_n2global[$a+1],"&euro;")."
  ".tdvalue($tab_fac_avn2[$a+1],"&euro;",true)."
  ".tdvalue_prc($per_fac_n2,"%",true)."
  </tr>";
  $a++;
  $a++;
}




echo "</table>";


echo "<br><br><br>";
//include "./footer.php";


?>