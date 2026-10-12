<?php

require "../admin_global.php";
require "functions_markanalyseprod.php";
include "../header.php";
include "menu_2.php";
include "../../class/facture.class.php";
include_once("../../class/frais_port.php");
include "../marge.php";
ini_set("display_errors",0);















print "<LINK href=\"cal.css\" type=\"text/css\" rel=\"stylesheet\">
<script language=\"javascript\" src=\"calendrier.js\"></script>
<script language=\"javascript\">
initcalendrier();
</script>
";



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
";
?>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
<script language="javascript">

  function showpopup(id){
      document.getElementById(id).style.visibility='visible';
      document.getElementById(id).style.backgroundColor='#AAFFAA';
      document.getElementById(id).parentNode.style.backgroundColor='#AAFFAA';
  }

  function hidepopup(id){
      document.getElementById(id).style.visibility='hidden';
      document.getElementById(id).parentNode.style.backgroundColor='';
  }
var $jQuery = jQuery.noConflict();

$jQuery(document).ready(function(){
  $jQuery(".brand").change(function(){

        var id=$(this).val();
        var dataString = 'id='+ id;

        $jQuery.ajax({
            type: "POST",
            url: "ajax_categorie.php",
            data: dataString,
            cache: false,
            success: function(html){
                $jQuery(".type").html(html);
            }
        });

    });


  $jQuery(".type").change(function(){

        var id=$(this).val();
        var dataString = 'id_type='+ id;

        $jQuery.ajax({
            type: "POST",
            url: "ajax_categorie.php",
            data: dataString,
            cache: false,
            success: function(html){
                $jQuery(".ss_type").html(html);
            }
        });

    });


});
</script>

<h1>Analyse Produits</h1>
<?php
include(BACK_PATH.'/statistiques/statistiques_adm.php');

$checking1 = "";
$checking2 = "";
$checking3 = "";

if (isset($_POST['prov_site']) == "site")
  $checking1 = "checked";
if (isset($_POST['prov_mp']) == "marketplace")
  $checking2 = "checked";
if (isset($_POST['prov_mobile']) == "mobile")
  $checking3 = "checked";

echo "<div style=\"margin-top:3%; margin-bottom:3%;\">";
echo "<form method=\"post\" name=\"infos\" action=\"mark_analyse_prod.php\">";


echo "<strong>Provenance : </strong>";

echo "<input type=\"checkbox\" name=\"prov_site\" value=\"site\" ".$checking1.">site</option>";
echo "&nbsp;";
echo "<input type=\"checkbox\" name=\"prov_mp\" value=\"marketplace\" ".$checking2.">marketplace</option>";
echo "&nbsp;";
echo "<input type=\"checkbox\" name=\"prov_mobile\" value=\"mobile\" ".$checking3.">mobile</option>";

echo "<br><br>";
echo "<strong>Année : </strong>";
if(!isset($year)) $year=date("Y");
echo "<select name=\"year\">";
for($y=2010;$y<=(2009+count($objectif_facture));$y++) echo "<option value=\"$y\"".($y==$year?" selected":"").">$y</option>";
echo "</select>";
echo "<br><br>";


$sql = "";
$cams = "";
$compteur = 0;
$i = 0;
$sql = "SELECT libelle,marqueid FROM marque ORDER BY libelle ASC";

  $cams = $DB_site2->query($sql);
  while ($row = $DB_site2->fetch_array($cams))
    {
     $Marque[] = $row['libelle'];
     $Marque_id[] = $row['marqueid'];
     $i++;
    }

echo "<strong>Marques : </strong>";
echo "<select name=\"brand\" class=\"brand\">";
echo "<option value=\"none\">Aucune</option>";
echo " <optgroup label=\"-----------\"></optgroup>";
if (isset($_POST['brand']) && $_POST['brand'] != "none")
{
  $valeur = $_POST['brand'];
  $sql = "";
  $cams = "";
  $sql = "SELECT libelle FROM marque WHERE marqueid='".$valeur."'";
  $cams = $DB_site2->query($sql);
  $row = $DB_site2->fetch_array($cams);
  $name_libelle = $row['libelle'];

  echo "<option value=\"$valeur\" selected>$name_libelle</option>";
}
  while($compteur <= $i)
  {
    echo "<option value=\"".$Marque_id[$compteur]."\">".$Marque[$compteur]."</option>";
    $compteur++;
  }
 echo "</select>";


echo "<br>";
echo "<br>";



$sql = "";
$cams = "";
$compteur = 0;
$i = 0;
$sql = "SELECT libelle,typearticleid FROM typearticle";

  $cams = $DB_site2->query($sql);
  while ($row = $DB_site2->fetch_array($cams))
    {
     $Type_art[] = $row['libelle'];
     $Type_id[] = $row['typearticleid'];
     $i++;
    }

echo "<strong>Catégories : </strong>";
echo "<select name=\"type\" class=\"type\">";
echo "<option value=\"all\">Toutes</option>";
echo " <optgroup label=\"-----------\"></optgroup>";
if (isset($_POST['type']) && $_POST['type'] != "all")
{
  $categorie = $_POST['type'];
  $sql = "";
  $cams = "";
  $sql = "SELECT libelle FROM typearticle WHERE typearticleid='".$categorie."'";
  $cams = $DB_site2->query($sql);
  $row = $DB_site2->fetch_array($cams);
  $categorie = $row['libelle'];
}

  while($compteur <= $i)
  {
    echo "<option value=\"".$Type_id[$compteur]."\">".$Type_art[$compteur]."</option>";
    $compteur++;
  }
 echo "</select>";

echo "<br><br>";





echo "<strong>Sous catégories : </strong>";
echo "<select name=\"ss_type\" class=\"ss_type\">";
echo "<option value=\"all_sstype\">Tous</option>";
echo " <optgroup label=\"-----------\"></optgroup>";
if (isset($_POST['ss_type']) && $_POST['ss_type'] != "all_sstype")
{
  $sscategorie = $_POST['ss_type'];
}
echo "</select>";


echo "&nbsp;";echo "&nbsp;";echo "&nbsp;";echo "&nbsp;";
echo "<input value=\"valider\" type=\"submit\">";
echo "</form>";
echo "</div>";


$compteur = 0;
$y1 = $year - 1;
$y2 = $year - 2;

$prov_string = "";

if (isset($_POST['prov_site']) == "site")
{
  $prov_string = "AND (f.provenance='' ";

    if (isset($_POST['prov_mp']) == "marketplace" && isset($_POST['prov_mobile']) == "mobile")
       $prov_string .= "OR f.provenance='mp' OR f.provenance='mobile') ";

    if (isset($_POST['prov_mp']) == "marketplace" && !isset($_POST['prov_mobile']))
       $prov_string .= "OR f.provenance='mp') "; 

    if (isset($_POST['prov_mobile']) == "mobile" && !isset($_POST['prov_mp']))
       $prov_string .= "OR f.provenance='mobile') ";

    if (!isset($_POST['prov_mp']) && !isset($_POST['prov_mobile']))
       $prov_string = "AND f.provenance='' ";
}
if (isset($_POST['prov_mp']) == "mp" && !isset($_POST['prov_site']))
{
  $prov_string = "AND (f.provenance='mp' ";

    if (isset($_POST['prov_mobile']) == "mobile")
      $prov_string .= "OR f.provenance='mobile') ";
    else
      $prov_string = "AND f.provenance='mp' ";
}
if (isset($_POST['prov_mobile']) == "mobile" && !isset($_POST['prov_site']) && !isset($_POST['prov_mp']))
{
  $prov_string = "AND f.provenance='mobile' "; 
}

if (isset($_POST['brand']))
{
  if ($_POST['brand'] == "none")
  {     //tableau toutes marques confondus
                                                                    /*
                                                                       _____         _   _  _____         __  __          _____   ____  _    _ ______ 
                                                                      / ____|  /\   | \ | |/ ____|       |  \/  |   /\   |  __ \ / __ \| |  | |  ____|
                                                                     | (___   /  \  |  \| | (___         | \  / |  /  \  | |__) | |  | | |  | | |__   
                                                                      \___ \ / /\ \ | . ` |\___ \        | |\/| | / /\ \ |  _  /| |  | | |  | |  __|  
                                                                      ____) / ____ \| |\  |____) |       | |  | |/ ____ \| | \ \| |__| | |__| | |____ 
                                                                     |_____/_/    \_\_| \_|_____/        |_|  |_/_/    \_\_|  \_\\___\_\\____/|______|
                                                                    */
     echo "<table class=\"ca\">
    <tr>
     <td colspan=\"9\" class=\"ca_second_title\">Chiffre d'affaires par marque</td>
     <td colspan=\"1\" style=\"background-color:#ffffff; border: 1px solid #ffffff; border-right:1px solid black;\"></td>
     <td colspan=\"8\" class=\"ca_second_title\"></td>
     <td colspan=\"1\" style=\"background-color:#ffffff; border: 1px solid #ffffff; border-right:1px solid black;\"></td>
     <td colspan=\"3\" class=\"ca_second_title\"></td>
    <tr class=\"ca_head\">
      <td>Marque</td>
      <td>CA HT ".$year."</td>
      <td>% CA ".$year."</td>
      <td>CA HT ".$y1."</td>
      <td>% CA ".$y1."</td>
      <td>Av/Ret N-1 en %</td>
      <td>CA HT ".$y2."</td>
      <td>% CA ".$y2."</td>
      <td>Av/Ret N-2 en %</td>
      <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
      <td>Qte ".$year."</td>
      <td>% Qte ".$year."</td>
      <td>Qte ".$y1."</td>
      <td>% Qte ".$y1."</td>
      <td>Av/Ret N-1 en %</td>
      <td>Qte ".$y2."</td>
      <td>% Qte ".$y2."</td>
      <td>Av/Ret N-2 en %</td>
      <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
      <td>Prix moyen ".$year."</td>
      <td>Prix moyen ".$y1."</td>
      <td>Prix moyen ".$y2."</td>
    </tr>";

    if ($year == date(Y))
    {
      $brands_name_ca = get_brand_ca($prov_string,$year);
      $av_brands_name_ca_n1 = get_brand_ca_compare_current_time($prov_string,$year-1);
      $av_brands_name_ca_n2 = get_brand_ca_compare_current_time($prov_string,$year-2);

      $av_brands_name_qte_n1 = get_brand_qte_current_time($prov_string,$year - 1);
      $av_brands_name_qte_n2 = get_brand_qte_current_time($prov_string,$year - 2);      
    }
    else
    {
      $brands_name_ca = get_brand_ca($prov_string,$year);
      $av_brands_name_ca_n1 = get_brand_ca($prov_string,$year-1);
      $av_brands_name_ca_n2 = get_brand_ca($prov_string,$year-2);

      $av_brands_name_qte_n1 = get_brand_qte($prov_string,$year - 1);
      $av_brands_name_qte_n2 = get_brand_qte($prov_string,$year - 2);
    }

    $brands_name_ca_n1 = get_brand_ca($prov_string,$year - 1);
    $brands_name_ca_n2 = get_brand_ca($prov_string,$year - 2);

    $brands_qte = get_brand_qte($prov_string,$year);
    $brands_qte_n1 = get_brand_qte($prov_string,$year-1);
    $brands_qte_n2 = get_brand_qte($prov_string,$year-2);


    $a = 1;
    $b = 0;
    while ($brands_name_ca[$i] != NULL)
    {
      $total_brand_ca += $brands_name_ca[$a];
      $total_brand_ca_n1 += $brands_name_ca_n1[$a];
      $total_brand_ca_n2 += $brands_name_ca_n2[$a];

      $total_brand_qte = $total_brand_qte + $brands_qte[$b];
      $total_brand_qte_n1 = $total_brand_qte_n1 + $brands_qte_n1[$b];
      $total_brand_qte_n2 = $total_brand_qte_n2 + $brands_qte_n2[$b];

      $a = $a + 2;
      $i = $i + 2;

      $b = $b + 1;
    }


    $i = 0;
    $b = 0;
    while ($brands_name_ca[$i] != NULL)
    {

      $perc_ca = ($brands_name_ca[$i + 1] * 100) / $total_brand_ca;
      $perc_ca_n1 = ($brands_name_ca_n1[$i + 1] * 100) / $total_brand_ca_n1;
      $perc_ca_n2 = ($brands_name_ca_n2[$i + 1] * 100) / $total_brand_ca_n2;

      $av_ret_n1_ca = (($brands_name_ca[$i + 1] * 100) / $av_brands_name_ca_n1[$i + 1]);
      if($av_ret_n1_ca != 0)
        $av_ret_n1_ca = $av_ret_n1_ca - 100;
      $av_ret_n2_ca = (($brands_name_ca[$i + 1] * 100) / $av_brands_name_ca_n2[$i + 1]);
      if($av_ret_n2_ca != 0)
        $av_ret_n2_ca = $av_ret_n2_ca - 100;

      ////////////////////////////////////////////////////////////////////////

      $perc_qte = ($brands_qte[$b] * 100) / $total_brand_qte;
      $perc_qte_n1 = ($brands_qte_n1[$b] * 100) / $total_brand_qte_n1;
      $perc_qte_n2 = ($brands_qte_n2[$b] * 100) / $total_brand_qte_n2;

      $av_ret_n1_qte = (($brands_qte[$b] * 100) / $av_brands_name_qte_n1[$b]);
      if($av_ret_n1_qte != 0)
        $av_ret_n1_qte = $av_ret_n1_qte - 100;
      $av_ret_n2_qte = (($brands_qte[$b] * 100) / $av_brands_name_qte_n2[$b]);
      if($av_ret_n2_qte != 0)
        $av_ret_n2_qte = $av_ret_n2_qte - 100;

      $prix_m = $brands_name_ca[$i + 1] / $brands_qte[$b];
      $prix_m_n1 = $brands_name_ca_n1[$i + 1] / $brands_qte_n1[$b];
      $prix_m_n2 = $brands_name_ca_n2[$i + 1] / $brands_qte_n2[$b];
      echo "<tr>
        <td>".$brands_name_ca[$i]."</td>
        ".tdvalue_nocolor($brands_name_ca[$i + 1],"&euro;")."
        ".tdvalue_nocolor($perc_ca,"%",true)."
        ".tdvalue_nocolor($brands_name_ca_n1[$i + 1],"&euro;")."
        ".tdvalue_nocolor($perc_ca_n1,"%",true)."
        ".tdvalue($av_ret_n1_ca,"%",true)."
        ".tdvalue_nocolor($brands_name_ca_n2[$i + 1],"&euro;")."
        ".tdvalue_nocolor($perc_ca_n2,"%",true)."
        ".tdvalue($av_ret_n2_ca,"%",true)."
        <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
        <td style=\"text-align:center;\">".$brands_qte[$b]."</td>
        ".tdvalue_nocolor($perc_qte,"%",true)."
        <td style=\"text-align:center;\">".$brands_qte_n1[$b]."</td>
        ".tdvalue_nocolor($perc_qte_n1,"%",true)."
        ".tdvalue($av_ret_n1_qte,"%",true)."
        <td style=\"text-align:center;\">".$brands_qte_n2[$b]."</td>
        ".tdvalue_nocolor($perc_qte_n2,"%",true)."
        ".tdvalue($av_ret_n2_qte,"%",true)."
        <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
        ".tdvalue_nocolor($prix_m,"&euro;")."
        ".tdvalue_nocolor($prix_m_n1,"&euro;")."
        ".tdvalue_nocolor($prix_m_n2,"&euro;")."
      </tr>";
      $i = $i + 2;
      $b = $b + 1;
    }

    echo"</table>";
                                                                                                            /*
                                                                                                              __  __          _____   ____  _    _ ______ 
                                                                                                             |  \/  |   /\   |  __ \ / __ \| |  | |  ____|
                                                                                                             | \  / |  /  \  | |__) | |  | | |  | | |__   
                                                                                                             | |\/| | / /\ \ |  _  /| |  | | |  | |  __|  
                                                                                                             | |  | |/ ____ \| | \ \| |__| | |__| | |____ 
                                                                                                             |_|  |_/_/    \_\_|  \_\\___\_\\____/|______|
                                                                                                            */
  }
  else
  {    
    if ($_POST['type'] == "all")
    {
     echo "<table class=\"ca\">
    <tr>
      <td colspan=\"9\" class=\"ca_second_title\">Détail mensuel pour la marque ".$name_libelle."</td>
      <td colspan=\"1\" style=\"background-color:#ffffff; border: 1px solid #ffffff; border-right:1px solid black;\"></td>
      <td colspan=\"8\" class=\"ca_second_title\"></td>
      <td colspan=\"1\" style=\"background-color:#ffffff; border: 1px solid #ffffff; border-right:1px solid black;\"></td>
      <td colspan=\"3\" class=\"ca_second_title\"></td>
    <tr class=\"ca_head\">
      <td>Mois</td>
      <td>CA HT ".$year."</td>
      <td>% CA ".$year."</td>
      <td>CA HT ".$y1."</td>
      <td>% CA ".$y1."</td>
      <td>Av/Ret N-1 en %</td>
      <td>CA HT ".$y2."</td>
      <td>% CA ".$y2."</td>
      <td>Av/Ret N-2 en %</td>
      <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
      <td>Qte ".$year."</td>
      <td>% Qte ".$year."</td>
      <td>Qte ".$y1."</td>
      <td>% Qte ".$y1."</td>
      <td>Av/Ret N-1 en %</td>
      <td>Qte ".$y2."</td>
      <td>% Qte ".$y2."</td>
      <td>Av/Ret N-2 en %</td>
      <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
      <td>Prix moyen ".$year."</td>
      <td>Prix moyen ".$y1."</td>
      <td>Prix moyen ".$y2."</td>
    </tr>";




    $month = get_array_month();
    $ca_month = get_ca_month($prov_string, $year, $valeur);
    $ca_month_n1 = get_ca_month($prov_string, $year-1, $valeur);
    $ca_month_n2 = get_ca_month($prov_string, $year-2, $valeur);

    $qte_month = get_qte_month($prov_string,$year,$valeur);
    $qte_month_n1 = get_qte_month($prov_string,$year-1,$valeur);
    $qte_month_n2 = get_qte_month($prov_string,$year-2,$valeur);

    if ($year == date(Y))
      {
        $av_ca_month_n1 = get_ca_month_cr($prov_string,$year-1,$valeur);
        $av_ca_month_n2 = get_ca_month_cr($prov_string,$year-2,$valeur);

        $av_qte_month_n1 = get_qte_month_cr($prov_string,$year - 1,$valeur);
        $av_qte_month_n2 = get_qte_month_cr($prov_string,$year - 2,$valeur);      
      }
    else
      {
        $av_ca_month_n1 = get_ca_month($prov_string,$year-1,$valeur);
        $av_ca_month_n2 = get_ca_month($prov_string,$year-2,$valeur);

        $av_qte_month_n1 = get_qte_month($prov_string,$year-1,$valeur);
        $av_qte_month_n2 = get_qte_month($prov_string,$year-2,$valeur);
      }


    $i = 0;
    while ($i < 12)
      {
        $total_brand_ca += $ca_month[$i];
        $total_brand_ca_n1 += $ca_month_n1[$i];
        $total_brand_ca_n2 += $ca_month_n2[$i];

        $total_brand_qte += $qte_month[$i];
        $total_brand_qte_n1 += $qte_month_n1[$i];
        $total_brand_qte_n2 += $qte_month_n2[$i];

        $total_brand_av_ca_n1 += $av_ca_month_n1[$i];
        $total_brand_av_ca_n2 += $av_ca_month_n2[$i];

        $total_brand_av_qte_n1 += $av_qte_month_n1[$i];
        $total_brand_av_qte_n2 += $av_qte_month_n2[$i];

        $i++;
      }
    
    $total_av_ret_n1_ca = (($total_brand_ca * 100) / $total_brand_av_ca_n1);
    if($total_av_ret_n1_ca != 0)
      $total_av_ret_n1_ca = $total_av_ret_n1_ca - 100;

    $total_av_ret_n2_ca = (($total_brand_ca * 100) / $total_brand_av_ca_n2);
    if($total_av_ret_n2_ca != 0)
      $total_av_ret_n2_ca = $total_av_ret_n2_ca - 100;

    $total_av_ret_n1_qte = (($total_brand_qte * 100) / $total_brand_av_qte_n1);
    if($total_av_ret_n1_qte != 0)
      $total_av_ret_n1_qte = $total_av_ret_n1_qte - 100;

    $total_av_ret_n2_qte = (($total_brand_qte * 100) / $total_brand_av_qte_n2);
    if($total_av_ret_n2_qte != 0)
      $total_av_ret_n2_qte = $total_av_ret_n2_qte - 100;


    $total_prix_m = $total_brand_ca / $total_brand_qte;
    $total_prix_m_n1 = $total_brand_ca_n1 / $total_brand_qte_n1;
    $total_prix_m_n2 = $total_brand_ca_n2 / $total_brand_qte_n2;

    $i = 0;
    while ($i < 12)
    {

      $perc_ca = ($ca_month[$i] * 100) / $total_brand_ca;
      $perc_ca_n1 = ($ca_month_n1[$i] * 100) / $total_brand_ca_n1;
      $perc_ca_n2 = ($ca_month_n2[$i] * 100) / $total_brand_ca_n2;

      $perc_qte = ($qte_month[$i] * 100) / $total_brand_qte;
      $perc_qte_n1 = ($qte_month_n1[$i] * 100) / $total_brand_qte_n1;
      $perc_qte_n2 = ($qte_month_n2[$i] * 100) / $total_brand_qte_n2;

      ////////////////////////////////////////////////////////////////////////

      $av_ret_n1_ca = (($ca_month[$i] * 100) / $av_ca_month_n1[$i]);
      if($av_ret_n1_ca != 0)
        $av_ret_n1_ca = $av_ret_n1_ca - 100;
      $av_ret_n2_ca = (($ca_month[$i] * 100) / $av_ca_month_n2[$i]);
      if($av_ret_n2_ca != 0)
        $av_ret_n2_ca = $av_ret_n2_ca - 100;

      $av_ret_n1_qte = (($qte_month[$i] * 100) / $av_qte_month_n1[$i]);
      if($av_ret_n1_qte != 0)
        $av_ret_n1_qte = $av_ret_n1_qte - 100;
      $av_ret_n2_qte = (($qte_month[$i] * 100) / $av_qte_month_n2[$i]);
      if($av_ret_n2_qte != 0)
        $av_ret_n2_qte = $av_ret_n2_qte - 100;



      $prix_m = $ca_month[$i] / $qte_month[$i];
      $prix_m_n1 = $ca_month_n1[$i] / $qte_month_n1[$i];
      $prix_m_n2 = $ca_month_n2[$i] / $qte_month_n2[$i];

      echo "<tr>
        <td>".$month[$i]."</td>
        ".tdvalue_nocolor($ca_month[$i],"&euro;")."
        ".tdvalue_nocolor($perc_ca,"%",true)."
        ".tdvalue_nocolor($ca_month_n1[$i],"&euro;")."
        ".tdvalue_nocolor($perc_ca_n1,"%",true)."
        ".tdvalue($av_ret_n1_ca,"%",true)."
        ".tdvalue_nocolor($ca_month_n2[$i],"&euro;")."
        ".tdvalue_nocolor($perc_ca_n2,"%",true)."
        ".tdvalue($av_ret_n2_ca,"%",true)."
        <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
        <td style=\"text-align:center;\">".$qte_month[$i]."</td>
        ".tdvalue_nocolor($perc_qte,"%",true)."
        <td style=\"text-align:center;\">".$qte_month_n1[$i]."</td>
        ".tdvalue_nocolor($perc_qte_n1,"%",true)."
        ".tdvalue($av_ret_n1_qte,"%",true)."
        <td style=\"text-align:center;\">".$qte_month_n2[$i]."</td>
        ".tdvalue_nocolor($perc_qte_n2,"%",true)."
        ".tdvalue($av_ret_n2_qte,"%",true)."
        <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
        ".tdvalue_nocolor($prix_m,"&euro;")."
        ".tdvalue_nocolor($prix_m_n1,"&euro;")."
        ".tdvalue_nocolor($prix_m_n2,"&euro;")."
      </tr>";
      $i++;
    }


      echo "<tr class=\"ca_head\">
        <td>Total</td>
        ".tdvalue_nocolor($total_brand_ca,"&euro;")."
        <td></td>
        ".tdvalue_nocolor($total_brand_ca_n1,"&euro;")."
        <td></td>
        ".tdvalue($total_av_ret_n1_ca,"%",true)."
        ".tdvalue_nocolor($total_brand_ca_n2,"&euro;")."
        <td></td>
        ".tdvalue($total_av_ret_n2_ca,"%",true)."
        <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
        <td>".$total_brand_qte."</td>
        <td></td>
        <td>".$total_brand_qte_n1."</td>
        <td></td>
        ".tdvalue($total_av_ret_n1_qte,"%",true)."
        <td>".$total_brand_qte_n2."</td>
        <td></td>
        ".tdvalue($total_av_ret_n2_qte,"%",true)."
        <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
        ".tdvalue_nocolor($total_prix_m,"&euro;")."
        ".tdvalue_nocolor($total_prix_m_n1,"&euro;")."
        ".tdvalue_nocolor($total_prix_m_n2,"&euro;")."
      </tr>";


      echo"</table>";
                                                                                            /*

                                                                                       _____       _______ ______ _____  ____  _____  _____ ______ 
                                                                                      / ____|   /\|__   __|  ____/ ____|/ __ \|  __ \|_   _|  ____|
                                                                                     | |       /  \  | |  | |__ | |  __| |  | | |__) | | | | |__   
                                                                                     | |      / /\ \ | |  |  __|| | |_ | |  | |  _  /  | | |  __|  
                                                                                     | |____ / ____ \| |  | |___| |__| | |__| | | \ \ _| |_| |____ 
                                                                                      \_____/_/    \_\_|  |______\_____|\____/|_|  \_\_____|______|

                                                                                    */
    }
    else
    {
      if ($_POST['ss_type'] == "all_sstype")
      {
       echo "<table class=\"ca\">
      <tr>
        <td colspan=\"9\" class=\"ca_second_title\">Détail mensuel pour la marque ".$name_libelle." - Catégorie : ".$categorie."</td>
        <td colspan=\"1\" style=\"background-color:#ffffff; border: 1px solid #ffffff; border-right:1px solid black;\"></td>
        <td colspan=\"8\" class=\"ca_second_title\"></td>
        <td colspan=\"1\" style=\"background-color:#ffffff; border: 1px solid #ffffff; border-right:1px solid black;\"></td>
        <td colspan=\"3\" class=\"ca_second_title\"></td>
      <tr class=\"ca_head\">
        <td>Mois</td>
        <td>CA HT ".$year."</td>
        <td>% CA ".$year."</td>
        <td>CA HT ".$y1."</td>
        <td>% CA ".$y1."</td>
        <td>Av/Ret N-1 en %</td>
        <td>CA HT ".$y2."</td>
        <td>% CA ".$y2."</td>
        <td>Av/Ret N-2 en %</td>
        <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
        <td>Qte ".$year."</td>
        <td>% Qte ".$year."</td>
        <td>Qte ".$y1."</td>
        <td>% Qte ".$y1."</td>
        <td>Av/Ret N-1 en %</td>
        <td>Qte ".$y2."</td>
        <td>% Qte ".$y2."</td>
        <td>Av/Ret N-2 en %</td>
        <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
        <td>Prix moyen ".$year."</td>
        <td>Prix moyen ".$y1."</td>
        <td>Prix moyen ".$y2."</td>
      </tr>";


      $cat = $_POST['type'];

      $month = get_array_month();
      $ca_month = get_ca_month_cat($prov_string, $year, $valeur, $cat);
      $ca_month_n1 = get_ca_month_cat($prov_string, $year-1, $valeur, $cat);
      $ca_month_n2 = get_ca_month_cat($prov_string, $year-2, $valeur, $cat);

      $qte_month = get_qte_month_cat($prov_string,$year,$valeur, $cat);
      $qte_month_n1 = get_qte_month_cat($prov_string,$year-1,$valeur, $cat);
      $qte_month_n2 = get_qte_month_cat($prov_string,$year-2,$valeur, $cat);

      if ($year == date(Y))
        {
          $av_ca_month_n1 = get_ca_month_cr_cat($prov_string,$year-1,$valeur, $cat);
          $av_ca_month_n2 = get_ca_month_cr_cat($prov_string,$year-2,$valeur, $cat);

          $av_qte_month_n1 = get_qte_month_cr_cat($prov_string,$year - 1,$valeur, $cat);
          $av_qte_month_n2 = get_qte_month_cr_cat($prov_string,$year - 2,$valeur, $cat);      
        }
      else
        {
          $av_ca_month_n1 = get_ca_month_cat($prov_string,$year-1,$valeur, $cat);
          $av_ca_month_n2 = get_ca_month_cat($prov_string,$year-2,$valeur, $cat);

          $av_qte_month_n1 = get_qte_month_cat($prov_string,$year-1,$valeur, $cat);
          $av_qte_month_n2 = get_qte_month_cat($prov_string,$year-2,$valeur, $cat);
        }


      $i = 0;
      while ($i < 12)
        {
          $total_brand_ca += $ca_month[$i];
          $total_brand_ca_n1 += $ca_month_n1[$i];
          $total_brand_ca_n2 += $ca_month_n2[$i];

          $total_brand_qte += $qte_month[$i];
          $total_brand_qte_n1 += $qte_month_n1[$i];
          $total_brand_qte_n2 += $qte_month_n2[$i];

          $total_brand_av_ca_n1 += $av_ca_month_n1[$i];
          $total_brand_av_ca_n2 += $av_ca_month_n2[$i];

          $total_brand_av_qte_n1 += $av_qte_month_n1[$i];
          $total_brand_av_qte_n2 += $av_qte_month_n2[$i];

          $i++;
        }
      

      $total_av_ret_n1_ca = (($total_brand_ca * 100) / $total_brand_av_ca_n1);
      if($total_av_ret_n1_ca != 0)
        $total_av_ret_n1_ca = $total_av_ret_n1_ca - 100;

      $total_av_ret_n2_ca = (($total_brand_ca * 100) / $total_brand_av_ca_n2);
      if($total_av_ret_n2_ca != 0)
        $total_av_ret_n2_ca = $total_av_ret_n2_ca - 100;

      $total_av_ret_n1_qte = (($total_brand_qte * 100) / $total_brand_av_qte_n1);
      if($total_av_ret_n1_qte != 0)
        $total_av_ret_n1_qte = $total_av_ret_n1_qte - 100;

      $total_av_ret_n2_qte = (($total_brand_qte * 100) / $total_brand_av_qte_n2);
      if($total_av_ret_n2_qte != 0)
        $total_av_ret_n2_qte = $total_av_ret_n2_qte - 100;


    $total_prix_m = $total_brand_ca / $total_brand_qte;
    $total_prix_m_n1 = $total_brand_ca_n1 / $total_brand_qte_n1;
    $total_prix_m_n2 = $total_brand_ca_n2 / $total_brand_qte_n2;


      $i = 0;
      while ($i < 12)
      {

        $perc_ca = ($ca_month[$i] * 100) / $total_brand_ca;
        $perc_ca_n1 = ($ca_month_n1[$i] * 100) / $total_brand_ca_n1;
        $perc_ca_n2 = ($ca_month_n2[$i] * 100) / $total_brand_ca_n2;

        $perc_qte = ($qte_month[$i] * 100) / $total_brand_qte;
        $perc_qte_n1 = ($qte_month_n1[$i] * 100) / $total_brand_qte_n1;
        $perc_qte_n2 = ($qte_month_n2[$i] * 100) / $total_brand_qte_n2;

        ////////////////////////////////////////////////////////////////////////

        $av_ret_n1_ca = (($ca_month[$i] * 100) / $av_ca_month_n1[$i]);
        if($av_ret_n1_ca != 0)
          $av_ret_n1_ca = $av_ret_n1_ca - 100;
        $av_ret_n2_ca = (($ca_month[$i] * 100) / $av_ca_month_n2[$i]);
        if($av_ret_n2_ca != 0)
          $av_ret_n2_ca = $av_ret_n2_ca - 100;

        $av_ret_n1_qte = (($qte_month[$i] * 100) / $av_qte_month_n1[$i]);
        if($av_ret_n1_qte != 0)
          $av_ret_n1_qte = $av_ret_n1_qte - 100;
        $av_ret_n2_qte = (($qte_month[$i] * 100) / $av_qte_month_n2[$i]);
        if($av_ret_n2_qte != 0)
          $av_ret_n2_qte = $av_ret_n2_qte - 100;


        $prix_m = $ca_month[$i] / $qte_month[$i];
        $prix_m_n1 = $ca_month_n1[$i] / $qte_month_n1[$i];
        $prix_m_n2 = $ca_month_n2[$i] / $qte_month_n2[$i];

        echo "<tr>
          <td>".$month[$i]."</td>
          ".tdvalue_nocolor($ca_month[$i],"&euro;")."
          ".tdvalue_nocolor($perc_ca,"%",true)."
          ".tdvalue_nocolor($ca_month_n1[$i],"&euro;")."
          ".tdvalue_nocolor($perc_ca_n1,"%",true)."
          ".tdvalue($av_ret_n1_ca,"%",true)."
          ".tdvalue_nocolor($ca_month_n2[$i],"&euro;")."
          ".tdvalue_nocolor($perc_ca_n2,"%",true)."
          ".tdvalue($av_ret_n2_ca,"%",true)."
          <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
          <td style=\"text-align:center;\">".$qte_month[$i]."</td>
          ".tdvalue_nocolor($perc_qte,"%",true)."
          <td style=\"text-align:center;\">".$qte_month_n1[$i]."</td>
          ".tdvalue_nocolor($perc_qte_n1,"%",true)."
          ".tdvalue($av_ret_n1_qte,"%",true)."
          <td style=\"text-align:center;\">".$qte_month_n2[$i]."</td>
          ".tdvalue_nocolor($perc_qte_n2,"%",true)."
          ".tdvalue($av_ret_n2_qte,"%",true)."
          <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
          ".tdvalue_nocolor($prix_m,"&euro;")."
          ".tdvalue_nocolor($prix_m_n1,"&euro;")."
          ".tdvalue_nocolor($prix_m_n2,"&euro;")."
        </tr>";
        $i++;
      }

      echo "<tr class=\"ca_head\">
        <td>Total</td>
        ".tdvalue_nocolor($total_brand_ca,"&euro;")."
        <td></td>
        ".tdvalue_nocolor($total_brand_ca_n1,"&euro;")."
        <td></td>
        ".tdvalue($total_av_ret_n1_ca,"%",true)."
        ".tdvalue_nocolor($total_brand_ca_n2,"&euro;")."
        <td></td>
        ".tdvalue($total_av_ret_n2_ca,"%",true)."
        <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
        <td>".$total_brand_qte."</td>
        <td></td>
        <td>".$total_brand_qte_n1."</td>
        <td></td>
        ".tdvalue($total_av_ret_n1_qte,"%",true)."
        <td>".$total_brand_qte_n2."</td>
        <td></td>
        ".tdvalue($total_av_ret_n2_qte,"%",true)."
        <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
        ".tdvalue_nocolor($total_prix_m,"&euro;")."
        ".tdvalue_nocolor($total_prix_m_n1,"&euro;")."
        ".tdvalue_nocolor($total_prix_m_n2,"&euro;")."
      </tr>";


      echo"</table>";

      echo "<br><br><br>";
/*
  _______    _     _                           _____       __                             
 |__   __|  | |   | |                         |  __ \     / _|                            
    | | __ _| |__ | | ___  __ _ _   _         | |__) |___| |_ ___ _ __ ___ _ __   ___ ___ 
    | |/ _` | '_ \| |/ _ \/ _` | | | |        |  _  // _ \  _/ _ \ '__/ _ \ '_ \ / __/ _ \
    | | (_| | |_) | |  __/ (_| | |_| |        | | \ \  __/ ||  __/ | |  __/ | | | (_|  __/
    |_|\__,_|_.__/|_|\___|\__,_|\__,_|        |_|  \_\___|_| \___|_|  \___|_| |_|\___\___|

*/

       echo "<table class=\"ca\">
      <tr>
        <td colspan=\"9\" class=\"ca_second_title\">Détail des références par marque et catégorie de produits</td>
        <td colspan=\"1\" style=\"background-color:#ffffff; border: 1px solid #ffffff; border-right:1px solid black;\"></td>
        <td colspan=\"8\" class=\"ca_second_title\">Pour la marque : ".$name_libelle." - Catégorie : ".$categorie."</td>
      <tr class=\"ca_head\">
        <td>Référence</td>
        <td>CA HT ".$year."</td>
        <td>% CA ".$year."</td>
        <td>CA HT ".$y1."</td>
        <td>% CA ".$y1."</td>
        <td>Av/Ret N-1 en %</td>
        <td>CA HT ".$y2."</td>
        <td>% CA ".$y2."</td>
        <td>Av/Ret N-2 en %</td>
        <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
        <td>Qte ".$year."</td>
        <td>% Qte ".$year."</td>
        <td>Qte ".$y1."</td>
        <td>% Qte ".$y1."</td>
        <td>Av/Ret N-1 en %</td>
        <td>Qte ".$y2."</td>
        <td>% Qte ".$y2."</td>
        <td>Av/Ret N-2 en %</td>
      </tr>";


      $cat = $_POST['type'];

if ($year == date(Y))
    {
      $brands_name_ca = get_ca_cat_prod($prov_string,$year, $valeur, $cat);
      $av_brands_name_ca_n1 = get_ca_cr_cat_prod($prov_string,$year-1, $valeur, $cat);
      $av_brands_name_ca_n2 = get_ca_cr_cat_prod($prov_string,$year-2, $valeur, $cat);

      $av_brands_name_qte_n1 = get_qte_cr_cat_prod($prov_string,$year - 1, $valeur, $cat);
      $av_brands_name_qte_n2 = get_qte_cr_cat_prod($prov_string,$year - 2, $valeur, $cat);      
    }
    else
    {
      $brands_name_ca = get_ca_cat_prod($prov_string,$year, $valeur, $cat);
      $av_brands_name_ca_n1 = get_ca_cat_prod($prov_string,$year-1, $valeur, $cat);
      $av_brands_name_ca_n2 = get_ca_cat_prod($prov_string,$year-2, $valeur, $cat);

      $av_brands_name_qte_n1 = get_qte_cat_prod($prov_string,$year - 1, $valeur, $cat);
      $av_brands_name_qte_n2 = get_qte_cat_prod($prov_string,$year - 2, $valeur, $cat);
    }

    $brands_name_ca_n1 = get_ca_cat_prod($prov_string,$year - 1, $valeur, $cat);
    $brands_name_ca_n2 = get_ca_cat_prod($prov_string,$year - 2, $valeur, $cat);

    $brands_qte = get_qte_cat_prod($prov_string,$year, $valeur, $cat);
    $brands_qte_n1 = get_qte_cat_prod($prov_string,$year-1, $valeur, $cat);
    $brands_qte_n2 = get_qte_cat_prod($prov_string,$year-2, $valeur, $cat);


    $a = 1;
    while ($brands_name_ca[$i] != NULL)
    {
      $total_brand_ca += $brands_name_ca[$a];
      $total_brand_ca_n1 += $brands_name_ca_n1[$a];
      $total_brand_ca_n2 += $brands_name_ca_n2[$a];

      $total_brand_qte = $total_brand_qte + $brands_qte[$i + 1];
      $total_brand_qte_n1 = $total_brand_qte_n1 + $brands_qte_n1[$i + 1];
      $total_brand_qte_n2 = $total_brand_qte_n2 + $brands_qte_n2[$i + 1];

      $a = $a + 2;
      $i = $i + 2;
    }


    $i = 0;
    while ($brands_name_ca[$i] != NULL)
    {

      $perc_ca = ($brands_name_ca[$i + 1] * 100) / $total_brand_ca;
      $perc_ca_n1 = ($brands_name_ca_n1[$i + 1] * 100) / $total_brand_ca_n1;
      $perc_ca_n2 = ($brands_name_ca_n2[$i + 1] * 100) / $total_brand_ca_n2;

      $av_ret_n1_ca = (($brands_name_ca[$i + 1] * 100) / $av_brands_name_ca_n1[$i + 1]);
      if($av_ret_n1_ca != 0)
        $av_ret_n1_ca = $av_ret_n1_ca - 100;
      $av_ret_n2_ca = (($brands_name_ca[$i + 1] * 100) / $av_brands_name_ca_n2[$i + 1]);
      if($av_ret_n2_ca != 0)
        $av_ret_n2_ca = $av_ret_n2_ca - 100;

      ////////////////////////////////////////////////////////////////////////

      $perc_qte = ($brands_qte[$i + 1] * 100) / $total_brand_qte;
      $perc_qte_n1 = ($brands_qte_n1[$i + 1] * 100) / $total_brand_qte_n1;
      $perc_qte_n2 = ($brands_qte_n2[$i + 1] * 100) / $total_brand_qte_n2;

      $av_ret_n1_qte = (($brands_qte[$i + 1] * 100) / $av_brands_name_qte_n1[$i + 1]);
      if($av_ret_n1_qte != 0)
        $av_ret_n1_qte = $av_ret_n1_qte - 100;
      $av_ret_n2_qte = (($brands_qte[$i + 1] * 100) / $av_brands_name_qte_n2[$i + 1]);
      if($av_ret_n2_qte != 0)
        $av_ret_n2_qte = $av_ret_n2_qte - 100;

      echo "<tr>
        <td>".$brands_name_ca[$i]."</td>
        ".tdvalue_nocolor($brands_name_ca[$i + 1],"&euro;")."
        ".tdvalue_nocolor($perc_ca,"%",true)."
        ".tdvalue_nocolor($brands_name_ca_n1[$i + 1],"&euro;")."
        ".tdvalue_nocolor($perc_ca_n1,"%",true)."
        ".tdvalue($av_ret_n1_ca,"%",true)."
        ".tdvalue_nocolor($brands_name_ca_n2[$i + 1],"&euro;")."
        ".tdvalue_nocolor($perc_ca_n2,"%",true)."
        ".tdvalue($av_ret_n2_ca,"%",true)."
        <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
        <td style=\"text-align:center;\">".$brands_qte[$i + 1]."</td>
        ".tdvalue_nocolor($perc_qte,"%",true)."
        <td style=\"text-align:center;\">".$brands_qte_n1[$i + 1]."</td>
        ".tdvalue_nocolor($perc_qte_n1,"%",true)."
        ".tdvalue($av_ret_n1_qte,"%",true)."
        <td style=\"text-align:center;\">".$brands_qte_n2[$i + 1]."</td>
        ".tdvalue_nocolor($perc_qte_n2,"%",true)."
        ".tdvalue($av_ret_n2_qte,"%",true)."
      </tr>";
      $i = $i + 2;
    }

      echo"</table>";







                                                        /*
                                                           _____  ____  _    _  _____           _____       _______ ______ _____  ____  _____  _____ ______ 
                                                          / ____|/ __ \| |  | |/ ____|         / ____|   /\|__   __|  ____/ ____|/ __ \|  __ \|_   _|  ____|
                                                         | (___ | |  | | |  | | (___          | |       /  \  | |  | |__ | |  __| |  | | |__) | | | | |__   
                                                          \___ \| |  | | |  | |\___ \         | |      / /\ \ | |  |  __|| | |_ | |  | |  _  /  | | |  __|  
                                                          ____) | |__| | |__| |____) |        | |____ / ____ \| |  | |___| |__| | |__| | | \ \ _| |_| |____ 
                                                         |_____/ \____/ \____/|_____/          \_____/_/    \_\_|  |______\_____|\____/|_|  \_\_____|______|
                                                        */

      }
      else
      {
                 echo "<table class=\"ca\">
        <tr>
          <td colspan=\"9\" class=\"ca_second_title\">Détail mensuel pour la marque ".$name_libelle." - Catégorie : ".$categorie."</td>
          <td colspan=\"1\" style=\"background-color:#ffffff; border: 1px solid #ffffff; border-right:1px solid black;\"></td>
          <td colspan=\"8\" class=\"ca_second_title\">Sous Catégorie : ".$_POST['ss_type']."</td>
          <td colspan=\"1\" style=\"background-color:#ffffff; border: 1px solid #ffffff; border-right:1px solid black;\"></td>
          <td colspan=\"3\" class=\"ca_second_title\"></td>
        <tr class=\"ca_head\">
          <td>Mois</td>
          <td>CA HT ".$year."</td>
          <td>% CA ".$year."</td>
          <td>CA HT ".$y1."</td>
          <td>% CA ".$y1."</td>
          <td>Av/Ret N-1 en %</td>
          <td>CA HT ".$y2."</td>
          <td>% CA ".$y2."</td>
          <td>Av/Ret N-2 en %</td>
          <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
          <td>Qte ".$year."</td>
          <td>% Qte ".$year."</td>
          <td>Qte ".$y1."</td>
          <td>% Qte ".$y1."</td>
          <td>Av/Ret N-1 en %</td>
          <td>Qte ".$y2."</td>
          <td>% Qte ".$y2."</td>
          <td>Av/Ret N-2 en %</td>
          <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
          <td>Prix moyen ".$year."</td>
          <td>Prix moyen ".$y1."</td>
          <td>Prix moyen ".$y2."</td>
        </tr>";


$new_sstype = explode("-", $_POST['ss_type']);
$tsscat = array($new_sstype[0]);
$sscat_final = array();


function sort_all_catid($parentid)
{
 global $DB_site2;
 global $sscat_final;
  
 foreach($parentid as $catid){
  $sql = "SELECT catid FROM categorie WHERE parentid='".$catid."'";
  $cams = $DB_site2->query($sql);
  while ($cam = $DB_site2->fetch_array($cams)){
   if(!in_array($cam, $sscat_final)) array_push($sscat_final, $cam['catid']);
   sort_all_catid($cam);
  }
 }
}
sort_all_catid($tsscat);

$sscat_final = implode(",", $sscat_final);
$to_request = "IN (".$sscat_final.")";
/*   function sort_all_catid($parentid)
        {
            global $DB_site2;
            static $list;

            $sql = "";
            $cams = "";
            $sql = "SELECT catid FROM categorie WHERE parentid='".$parentid."'";
            $list .= $parentid;
            $cams = $DB_site2->query($sql);
              while ($cam = $DB_site2->fetch_array($cams))
                $list_catid[] = $cam['catid'];


              foreach ($list_catid as $new_parentid)
                sort_all_catid($new_parentid);

         return $list;
        }



        $cat = $_POST['type'];
        $sscat = 12;
        $list = sort_all_catid($sscat);
        nice($list);
*/
        if ($to_request == "IN ()")
          $to_request = "IN (".$new_sstype[0].")";
        $cat = $_POST['type'];
        $month = get_array_month();
        $ca_month = get_ca_month_sscat($prov_string, $year, $valeur, $cat, $to_request);
        $ca_month_n1 = get_ca_month_sscat($prov_string, $year-1, $valeur, $cat, $to_request);
        $ca_month_n2 = get_ca_month_sscat($prov_string, $year-2, $valeur, $cat, $to_request);

        $qte_month = get_qte_month_sscat($prov_string,$year,$valeur, $cat, $to_request);
        $qte_month_n1 = get_qte_month_sscat($prov_string,$year-1,$valeur, $cat, $to_request);
        $qte_month_n2 = get_qte_month_sscat($prov_string,$year-2,$valeur, $cat, $to_request);

        if ($year == date(Y))
          {
            $av_ca_month_n1 = get_ca_month_cr_sscat($prov_string,$year-1,$valeur, $cat, $to_request);
            $av_ca_month_n2 = get_ca_month_cr_sscat($prov_string,$year-2,$valeur, $cat, $to_request);

            $av_qte_month_n1 = get_qte_month_cr_sscat($prov_string,$year - 1,$valeur, $cat, $to_request);
            $av_qte_month_n2 = get_qte_month_cr_sscat($prov_string,$year - 2,$valeur, $cat, $to_request);      
          }
        else
          {
            $av_ca_month_n1 = get_ca_month_sscat($prov_string,$year-1,$valeur, $cat, $to_request);
            $av_ca_month_n2 = get_ca_month_sscat($prov_string,$year-2,$valeur, $cat, $to_request);

            $av_qte_month_n1 = get_qte_month_sscat($prov_string,$year-1,$valeur, $cat, $to_request);
            $av_qte_month_n2 = get_qte_month_sscat($prov_string,$year-2,$valeur, $cat, $to_request);
          }


        $i = 0;
        while ($i < 12)
          {
            $total_brand_ca += $ca_month[$i];
            $total_brand_ca_n1 += $ca_month_n1[$i];
            $total_brand_ca_n2 += $ca_month_n2[$i];

            $total_brand_qte += $qte_month[$i];
            $total_brand_qte_n1 += $qte_month_n1[$i];
            $total_brand_qte_n2 += $qte_month_n2[$i];


            $total_brand_av_ca_n1 += $av_ca_month_n1[$i];
            $total_brand_av_ca_n2 += $av_ca_month_n2[$i];

            $total_brand_av_qte_n1 += $av_qte_month_n1[$i];
            $total_brand_av_qte_n2 += $av_qte_month_n2[$i];

            $i++;
          }



          $total_av_ret_n1_ca = (($total_brand_ca * 100) / $total_brand_av_ca_n1);
          if($total_av_ret_n1_ca != 0)
            $total_av_ret_n1_ca = $total_av_ret_n1_ca - 100;

          $total_av_ret_n2_ca = (($total_brand_ca * 100) / $total_brand_av_ca_n2);
          if($total_av_ret_n2_ca != 0)
            $total_av_ret_n2_ca = $total_av_ret_n2_ca - 100;

          $total_av_ret_n1_qte = (($total_brand_qte * 100) / $total_brand_av_qte_n1);
          if($total_av_ret_n1_qte != 0)
            $total_av_ret_n1_qte = $total_av_ret_n1_qte - 100;

          $total_av_ret_n2_qte = (($total_brand_qte * 100) / $total_brand_av_qte_n2);
          if($total_av_ret_n2_qte != 0)
            $total_av_ret_n2_qte = $total_av_ret_n2_qte - 100;


        $total_prix_m = $total_brand_ca / $total_brand_qte;
        $total_prix_m_n1 = $total_brand_ca_n1 / $total_brand_qte_n1;
        $total_prix_m_n2 = $total_brand_ca_n2 / $total_brand_qte_n2;
        
        $i = 0;
        while ($i < 12)
        {

          $perc_ca = ($ca_month[$i] * 100) / $total_brand_ca;
          $perc_ca_n1 = ($ca_month_n1[$i] * 100) / $total_brand_ca_n1;
          $perc_ca_n2 = ($ca_month_n2[$i] * 100) / $total_brand_ca_n2;

          $perc_qte = ($qte_month[$i] * 100) / $total_brand_qte;
          $perc_qte_n1 = ($qte_month_n1[$i] * 100) / $total_brand_qte_n1;
          $perc_qte_n2 = ($qte_month_n2[$i] * 100) / $total_brand_qte_n2;

          ////////////////////////////////////////////////////////////////////////

          $av_ret_n1_ca = (($ca_month[$i] * 100) / $av_ca_month_n1[$i]);
          if($av_ret_n1_ca != 0)
            $av_ret_n1_ca = $av_ret_n1_ca - 100;
          $av_ret_n2_ca = (($ca_month[$i] * 100) / $av_ca_month_n2[$i]);
          if($av_ret_n2_ca != 0)
            $av_ret_n2_ca = $av_ret_n2_ca - 100;

          $av_ret_n1_qte = (($qte_month[$i] * 100) / $av_qte_month_n1[$i]);
          if($av_ret_n1_qte != 0)
            $av_ret_n1_qte = $av_ret_n1_qte - 100;
          $av_ret_n2_qte = (($qte_month[$i] * 100) / $av_qte_month_n2[$i]);
          if($av_ret_n2_qte != 0)
            $av_ret_n2_qte = $av_ret_n2_qte - 100;


          $prix_m = $ca_month[$i] / $qte_month[$i];
          $prix_m_n1 = $ca_month_n1[$i] / $qte_month_n1[$i];
          $prix_m_n2 = $ca_month_n2[$i] / $qte_month_n2[$i];

        echo "<tr>
          <td>".$month[$i]."</td>
          ".tdvalue_nocolor($ca_month[$i],"&euro;")."
          ".tdvalue_nocolor($perc_ca,"%",true)."
          ".tdvalue_nocolor($ca_month_n1[$i],"&euro;")."
          ".tdvalue_nocolor($perc_ca_n1,"%",true)."
          ".tdvalue($av_ret_n1_ca,"%",true)."
          ".tdvalue_nocolor($ca_month_n2[$i],"&euro;")."
          ".tdvalue_nocolor($perc_ca_n2,"%",true)."
          ".tdvalue($av_ret_n2_ca,"%",true)."
          <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
          <td style=\"text-align:center;\">".$qte_month[$i]."</td>
          ".tdvalue_nocolor($perc_qte,"%",true)."
          <td style=\"text-align:center;\">".$qte_month_n1[$i]."</td>
          ".tdvalue_nocolor($perc_qte_n1,"%",true)."
          ".tdvalue($av_ret_n1_qte,"%",true)."
          <td style=\"text-align:center;\">".$qte_month_n2[$i]."</td>
          ".tdvalue_nocolor($perc_qte_n2,"%",true)."
          ".tdvalue($av_ret_n2_qte,"%",true)."
          <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
          ".tdvalue_nocolor($prix_m,"&euro;")."
          ".tdvalue_nocolor($prix_m_n1,"&euro;")."
          ".tdvalue_nocolor($prix_m_n2,"&euro;")."
        </tr>";
          $i++;
        }

        echo "<tr class=\"ca_head\">
        <td>Total</td>
        ".tdvalue_nocolor($total_brand_ca,"&euro;")."
        <td></td>
        ".tdvalue_nocolor($total_brand_ca_n1,"&euro;")."
        <td></td>
        ".tdvalue($total_av_ret_n1_ca,"%",true)."
        ".tdvalue_nocolor($total_brand_ca_n2,"&euro;")."
        <td></td>
        ".tdvalue($total_av_ret_n2_ca,"%",true)."
        <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
        <td>".$total_brand_qte."</td>
        <td></td>
        <td>".$total_brand_qte_n1."</td>
        <td></td>
        ".tdvalue($total_av_ret_n1_qte,"%",true)."
        <td>".$total_brand_qte_n2."</td>
        <td></td>
        ".tdvalue($total_av_ret_n2_qte,"%",true)."
        <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px; border-bottom:1px solid #ffffff;\"></td>
        ".tdvalue_nocolor($total_prix_m,"&euro;")."
        ".tdvalue_nocolor($total_prix_m_n1,"&euro;")."
        ".tdvalue_nocolor($total_prix_m_n2,"&euro;")."
      </tr>";


        echo"</table>";
      }
    }
  }
}

  
?>

