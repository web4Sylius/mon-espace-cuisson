<?php

require "../admin_global.php";
require "functions_diranalyseprod.php";
include "../header.php";
include "menu_2.php";
include "../../class/facture.class.php";
include_once("../../class/frais_port.php");
include "../marge.php";
//ini_set("display_errors",0);















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

<h1>Analyse Produits</h1>";?>


<?php
include(BACK_PATH.'/statistiques/statistiques_adm.php');




echo "<div style=\"margin-top:3%; margin-bottom:3%;\">";
echo "<form method=\"post\" name=\"infos\" action=\"dir_analyse_produits.php\">";


echo "<strong>Méthode de tri : </strong>";

echo "<select name=\"tri\">";
echo "<option value=paht>Montant facturé</option>";
echo "<option value=quantite>Quantité</option>";
echo "<option value=artcode>Référence MCAV</option>";
echo "<option value=artcode_eurocave>Référence fabricant</option>";
echo "</select>";

echo "&nbsp;";
echo "&nbsp;";
echo "<strong>Période : </strong>";
echo "<select name=\"year\">";
for($y=2010;$y<=(2009+count($objectif_facture));$y++)
echo "<option value=\"$y\"".($y==$year?" selected":"").">$y</option>";
echo "</select>";
echo "&nbsp;";
echo "ou entre le";
echo "&nbsp;";

echo "<input type=\"text\" id=\"du\" name=\"du\" value=\"".$_POST['du']."\" onclick=\"calendrier_show(this.id,this.value)\" size=\"8\" readonly> et le
<input type=\"text\" id=\"au\" name=\"au\" value=\"".$_POST['au']."\" onclick=\"calendrier_show(this.id,this.value)\" size=\"8\" readonly> inclus";

echo "&nbsp;";
echo "&nbsp;";


$sql = "";
$cams = "";
$compteur = 0;
$i = 0;
$sql = "SELECT societe FROM fournisseur_condition WHERE types='produits principaux' OR types='produits secondaires' OR types='produits étrangers' ORDER BY societe ASC";

  $cams = $DB_site2->query($sql);
  while ($row = $DB_site2->fetch_array($cams))
    {
     $Societe[] = $row['societe'];
     $i++;
    }

echo "<strong>Fournisseur : </strong>";
echo "<select name=\"type\">";
  while($compteur <= $i)
  {
    echo "<option value=\"".$Societe[$compteur]."\">".$Societe[$compteur]."</option>";
    $compteur++;
  }
 echo "</select>";

echo "&nbsp;";
echo "&nbsp;";

$sql = "";
$cams = "";
$compteur = 0;
$i = 0;
$sql = "SELECT concurrents_nom,concurrents_id FROM concurrents WHERE concurrents_statut='1' ORDER BY concurrents_nom ASC";

  $cams = $DB_site2->query($sql);
  while ($row = $DB_site2->fetch_array($cams))
    {
     $Concurrents[$i]['id'] = $row['concurrents_id'];
     $Concurrents[$i]['nom'] = $row['concurrents_nom'];
     $i++;
    }
echo "<br>";
echo "<br>";
echo "<strong>Concurrents : </strong>";
echo "<table>";
  while($compteur < $i)
  {
    $bmp = 0;
    echo "<tr>";
    while ($bmp < 5)
    {
      $check="";
      if(empty($_POST) || isset($_POST['concur'][$Concurrents[$compteur]['id']]))$check="checked";
      echo "<td><input type=\"checkbox\" name=\"concur[".$Concurrents[$compteur]['id']."]\" value=\"".$Concurrents[$compteur]['nom']."\" ".$check.">".$Concurrents[$compteur]['nom']."</option></td>";
      $bmp++;
      $compteur++;
    }
    echo "</tr>";
  }
echo "</table>";
echo "</select>";

echo "<br>";
echo "<br>";

 echo "<input value=\"valider\" type=\"submit\">";
 echo "</form>";
 echo "</div>";


$compteur = 0;

if ($tri == "paht")
{
  $method = "Montant";
}

if ($tri == "quantite")
{
  $method = "Quantité";
}

if ($tri == "artcode")
{
  $method = "Référence MCAV";
}

if ($tri =="artcode_eurocave")
{
  $method = "Référence fabricant";
}

if ($type != "")
{
  if ($au!="" && $du!="")
  {

    $periode = $du;
    $periode .= " au ";
    $periode .= $au;
    $date_du = str_replace('/', '-', $du);
    $date_au = str_replace('/', '-', $au);

    $new_date_du = date('Y-m-d', strtotime($date_du));
    $new_date_au = date('Y-m-d', strtotime($date_au));


    $new_date_du_n1 = substract_year($new_date_du, 1);
    $new_date_au_n1 = substract_year($new_date_au, 1);

    $new_date_du_n2 = substract_year($new_date_du, 2);
    $new_date_au_n2 = substract_year($new_date_au, 2);



    $compteur = get_supplier_fork_compteur($type, $new_date_du, $new_date_au, $tri);
    $marque = get_supplier_fork_marque($type, $new_date_du, $new_date_au, $tri);
    $refmcav = get_supplier_fork_refmcav($type, $new_date_du, $new_date_au, $tri);
    $reffab = get_supplier_fork_reffab($type, $new_date_du, $new_date_au, $tri);
    $qte = get_supplier_fork_qte($type, $new_date_du, $new_date_au, $tri);
    $montant = get_supplier_fork_montant($type, $new_date_du, $new_date_au, $tri);

    $artid = get_supplier_fork_artid($type, $new_date_du, $new_date_au, $tri);
    $numero = get_supplier_fork_numero($type, $new_date_du, $new_date_au, $tri);

    $TR = 0;
  }
  else
  {
    if ($year == date(Y))
      $TR = 1;
    else
      $TR = 0;


    $periode = $year;
    $compteur = get_supplier_year_compteur($type, $year, $tri);
    $marque = get_supplier_year_marque($type, $year, $tri);
    $refmcav = get_supplier_year_refmcav($type, $year, $tri);
    $reffab = get_supplier_year_reffab($type, $year, $tri);
    $qte = get_supplier_year_qte($type, $year, $tri);
    $montant = get_supplier_year_montant($type, $year, $tri);

    $artid = get_supplier_year_artid($type, $year, $tri);
    $numero = get_supplier_year_numero($type, $year, $tri);

  }
  

  echo "</select></p>
  <table class=\"ca\">
    <tr><td colspan=\"17\" class=\"ca_second_title\">".$type." - ".$periode."</td>
    <td colspan=\"1\" style=\"background-color:#ffffff; border: 1px solid #ffffff; border-right:1px solid black;\"></td>
    <td colspan=\"4\" class=\"ca_second_title\"></td></tr>
    <tr class=\"ca_head\">
      <td>Artid</td>
      <td>Marque</td>
      <td>Ref MCAV</td>
      <td>Ref Fabricant</td>
      <td>Qte Facturée</td>
      <td>Montant Total Facturé &euro;HT</td>
      <td>Qte N-1</td>
      <td>Qte N-1 totale</td>
      <td>Av/ret N-1 en Qte</td>
      <td>% N-1</td>
      <td>Qte N-2</td>
      <td>Qte N-2 totale</td>
      <td>Av/ret N-2 en Qte</td>
      <td>% N-2</td>
      <td>P.V &euro;TTC</td>
      <td>P.A Tarif&euro;HT</td>
      <td>Stock</td>
      <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px;\"></td>
      <td>Marge euro HT/%</td>
      <td>Spy - prix plus bas</td>
      <td>% Mini</td>
      <td>Concurrent</td>
    </tr>";


$i = 0;
$montant_total = 0;
/*if ($marque[0] == NULL && $qte[0] == NULL)
{
  echo "<tr>
  <td colspan=\"17\" style=\"text-align:center;\">Pas de résultat</td>";
}
else
{*/
    while($i < $compteur)
    {
      if ($artid[$i] == NULL)
        $i++;

       if ($au!="" && $du!="")
        {
          $qte_n1 = get_product_fork_qte_n($artid[$i], $type, $new_date_du_n1, $new_date_au_n1);
          $qte_n2 = get_product_fork_qte_n($artid[$i], $type, $new_date_du_n2, $new_date_au_n2);

          $avretqte1 = $qte[$i] - $qte_n1;
          $avretqte2 = $qte[$i] - $qte_n2;

          if($qte_n1 > 0)
            $prc_qte_n1 = ((100 * $qte[$i]) / $qte_n1) - 100;
          elseif($qte_n1 == 0)
          {
            $qte_n1 = 1;
            $prc_qte_n1 = ((100 * $qte[$i]) / $qte_n1);            
            $qte_n1 = NULL;
          }
          if($qte_n2 > 0)
            $prc_qte_n2 = ((100 * $qte[$i]) / $qte_n2) - 100;
          elseif($qte_n2 == 0)
          {
            $qte_n2 = 1;
            $prc_qte_n2 = ((100 * $qte[$i]) / $qte_n2);            
            $qte_n2 = NULL;
          }
        }
        else
        {
          $qte_n1 = get_product_year_qte_n($artid[$i], $type, $year - 1, 1, $TR);
          $qte_n2 = get_product_year_qte_n($artid[$i], $type, $year - 2, 2, $TR);

          $avretqte1 = $qte[$i] - $qte_n1;
          $avretqte2 = $qte[$i] - $qte_n2;

          if($qte_n1 > 0)
            $prc_qte_n1 = ((100 * $qte[$i]) / $qte_n1) - 100;
          elseif($qte_n1 == 0)
          {
            $qte_n1 = 1;
            $prc_qte_n1 = ((100 * $qte[$i]) / $qte_n1);            
            $qte_n1 = NULL;
          }
          if($qte_n2 > 0)
            $prc_qte_n2 = ((100 * $qte[$i]) / $qte_n2) - 100;
          elseif($qte_n2 == 0)
          {
            $qte_n2 = 1;
            $prc_qte_n2 = ((100 * $qte[$i]) / $qte_n2);            
            $qte_n2 = NULL;
          }
        }


      $frais_port=new frais_port($DB_site2,array($artid[$i]=>1),57,0,$montant[$i]);
      $transport=$frais_port->paht;
      
      $total=($frais_port->paht+$frais_port->ecopart+$frais_port->prixtrspt+$frais_port->garantie+$frais_port->options)*$frais_port->qte;
      $total+=$frais_port->transport;



        $prixttc = get_value_ttc($refmcav[$i]);
        $prixHT = get_value_ht($refmcav[$i]);
        $pahtheco = get_value_pahtheco($refmcav[$i]);
        $marge_euro = $prixHT - ($pahtheco + $transport);
        $marge_perc = (100 * $marge_euro) / $prixHT;

        $qt1glob = get_product_year_qte_n($artid[$i], $type, $year - 1, 1, 0);
        $qt2glob = get_product_year_qte_n($artid[$i], $type, $year - 2, 2, 0);

        $montant_total += $montant[$i];
        $stock = get_stock($artid[$i]);
        $spymini = get_mini_value($refmcav[$i], $_POST['concur']);
        $spy_perc = ((100 * $spymini[0]) / $prixttc) - 100;


        if ($spymini[0] < $prixttc && $spymini[0] > 0)
          {
              $spy_perc = number_format($spy_perc,2,',',' ')."%";
              $spymini[0] = $spymini[0]."&euro;";
              $class = 'stat_rouge';
          }
          elseif ($spymini[0] >= $prixttc)
          {
              $spy_perc = "+".number_format($spy_perc,2,',',' ')."%";
              $spymini[0] = $spymini[0]."&euro;";
              $class = 'stat_vert';
          }

              if($spy_perc == -100)
                $spy_perc = NULL;


/*
if($refmcav[$i] == "ACI-VSV444")
{
echo $pahtheco;
echo "<br>";
echo $transport;
echo "<br>";
echo $prixHT;
}*/

      echo "<tr>
      <td>".$artid[$i]."</td>
      <td>".$marque[$i]."</td>
      <td>".$refmcav[$i]."</td>
      <td>".$reffab[$i]."</td>
      <td style=\"text-align:center;\">".$qte[$i]."</td>
      <td style=\"text-align:right;\" nowrap>".number_format($montant[$i],2,',',' ')."&euro;</td>
      <td style=\"text-align:center;\">".$qte_n1."</td>
      <td style=\"text-align:center;\">".$qt1glob."</td>
      ".tdvalue_nocoma($avretqte1,"",true)."
      ".tdvalue($prc_qte_n1,"%",true)."
      <td style=\"text-align:center;\">".$qte_n2."</td>
      <td style=\"text-align:center;\">".$qt2glob."</td>
      ".tdvalue_nocoma($avretqte2,"",true)."
      ".tdvalue($prc_qte_n2,"%",true)."
      <td style=\"text-align:right;\" nowrap>".number_format($prixttc,0,',',' ')."&euro;</td>
      <td style=\"text-align:right;\" nowrap>".number_format($pahtheco,2,',',' ')."&euro;</td>
      <td style=\"text-align:center;\">".$stock."</td>
      <td style=\"background-color:#ffffff; border: 0 solid #ffffff; width:15px;\"></td>
      <td style=\"text-align:right;\" nowrap>".number_format($marge_euro,2,',',' ')."&euro;<br>".number_format($marge_perc,0,',','')."%</td>
      <td class=\"".$class."\" nowrap>".$spymini[0]."</td>
      <td class=\"".$class."\" nowrap>".$spy_perc."</td>
      <td>".$spymini[1]."</td>
      </tr>";
      $i++;
    }  echo "</table>";
}
echo "<br><br><br>";
//include "./footer.php";

?>