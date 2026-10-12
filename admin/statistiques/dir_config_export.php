<?php

require "../admin_global.php";
require "functions_dirconfigexport.php"; // <------ FONCTIONS
include "../header.php";
include "menu_2.php";

include "../../class/facture.class.php";
ini_set("display_errors",0);


if(isset($_POST['pays_id']))
{
  if(isset($_POST["given_value"], $_POST["num_tva"], $_POST["tva_locale"]))
  {
     /********************************************************
     ICI ON DECLARE LES VARIABLES PUIS ON UPDATE LA TABLE SQL*
     *********************************************************/

    $pays_id = $_POST['pays_id'];

    $value_tab[1] = $_POST['given_value'];
    $value_tab[3] = $_POST['tva_locale'];
    $value_tab[4] = $_POST['num_tva'];


          $sql = "";
          $cams = "";  
          $sql = "UPDATE taux_de_change_eu SET given_value='".$value_tab[1]."', tva_locale='".$value_tab[3]."', num_tva='".$value_tab[4]."' WHERE pays_id='".$pays_id."'";
          $cams = $DB_site2->query($sql);

  }
}




echo "
<style>
.input {width:100%;}
.stat_rouge {color:red; text-align: right;}
.stat_vert {color:green; text-align: right;}
 tr:hover { background-color: #ffff99;}
.ca_mois { color:black; border-collapse:collapse; border:solid thin black; margin:auto; float:left; }
.ca_mois td { border:solid thin black; width:100px; }
.ca { color:black; border-collapse:collapse; border:solid thin black; margin:auto; float:left;}
.ca td { border:solid thin black; width:180px; }
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
";?>

<h1>Configuration TVA (Export)</h1>

<?php
include(BACK_PATH.'/statistiques/statistiques_adm.php');
echo "<br><br><br>";
if(!isset($year)) $year=date("Y");


/****************************************************************
On va récupérer un fichier XML contenant les taux de changes sur*
le site de la banque centrale européenne. Ensuite on le stock   *
pour pouvoir le lire plus tard                                  *
*****************************************************************/

$XML=simplexml_load_file("http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml");
//-->the file is updated daily between 2.15 p.m. and 3.00 p.m. CET
            
foreach($XML->Cube->Cube->Cube as $rate){
  if ($rate["currency"] == "BGN" || $rate["currency"] == "CZK" || $rate["currency"] == "DKK" || $rate["currency"] == "HUF" ||
  $rate["currency"] == "PLN" || $rate["currency"] == "RON" || $rate["currency"] == "SEK" || $rate["currency"] == "GBP")
    {
          $sql = "";
          $cams = "";  
          $sql = "UPDATE taux_de_change_eu SET taux='".$rate["rate"]."' WHERE currency='".$rate["currency"]."'";
          $cams = $DB_site2->query($sql);
    }
} 


$req=$DB_site2->query("SELECT diminutif FROM pays WHERE tvaintracom=1 ORDER BY libelle");
while($pays=$DB_site2->fetch_array($req)) 
{
  $flag[] = $pays['diminutif'];
}

$name = name_28();
$given = given_28();
$currency = currency_28();
$taux = taux_28();
$tva_locale = tva_locale_28();
$num_tva = num_tva_28();



$seuil[0] = $given[0] / $taux[0]; $seuil[1] = $given[1] / $taux[1]; $seuil[2] = $given[2] / $taux[2]; 
$seuil[3] = $given[3] / $taux[3];$seuil[4] = $given[4] / $taux[4]; $seuil[5] = $given[5] / $taux[5];
$seuil[6] = $given[6] / $taux[6]; $seuil[7] = $given[7] / $taux[7];$seuil[8] = $given[8] / $taux[8]; 
$seuil[9] = $given[9] / $taux[9]; $seuil[10] = $given[10] / $taux[10]; $seuil[11] = $given[11] / $taux[11];
$seuil[12] = $given[12] / $taux[12];$seuil[13] = $given[13] / $taux[13];$seuil[14] = $given[14] / $taux[14]; 
$seuil[15] = $given[15] / $taux[15];$seuil[16] = $given[16] / $taux[16]; $seuil[17] = $given[17] / $taux[17]; 
$seuil[18] = $given[18] / $taux[18]; $seuil[19] = $given[19] / $taux[19];$seuil[20] = $given[20] / $taux[20]; 
$seuil[21] = $given[21] / $taux[21];$seuil[22] = $given[22] / $taux[22]; $seuil[23] = $given[23] / $taux[23];
$seuil[24] = $given[24] / $taux[24]; $seuil[25] = $given[25] / $taux[25];$seuil[26] = $given[26] / $taux[26]; 
$seuil[27] = $given[27] / $taux[27];


$p = 0;
$id_pays = 1;
while ($p < 26)
{
  $alerte[$p] = prc_80_alert($seuil[$p]);

    $sql = "";
    $cams = "";  
    $sql = "UPDATE taux_de_change_eu SET alerte='".$alerte[$p]."' WHERE pays_id='".$id_pays."'";
    $cams = $DB_site2->query($sql);

  $p++;
  $id_pays++;
}


/******************************************
*  _____ _   ___ _    ___   _  _   ___  __*
* |_   _/_\ | _ ) |  | __| /_\| | | \ \/ /*
*   | |/ _ \| _ \ |__| _| / _ \ |_| |>  < *
*   |_/_/ \_\___/____|___/_/ \_\___//_/\_\*
*                                         *
*******************************************/

echo "
<form action=\"\" method=\"post\" id=\"form_1\"></form>
<form action=\"\" method=\"post\" id=\"form_2\"></form>
<form action=\"\" method=\"post\" id=\"form_3\"></form>
<form action=\"\" method=\"post\" id=\"form_4\"></form>
<form action=\"\" method=\"post\" id=\"form_5\"></form>
<form action=\"\" method=\"post\" id=\"form_6\"></form>
<form action=\"\" method=\"post\" id=\"form_7\"></form>
<form action=\"\" method=\"post\" id=\"form_8\"></form>
<form action=\"\" method=\"post\" id=\"form_9\"></form>
<form action=\"\" method=\"post\" id=\"form_10\"></form>
<form action=\"\" method=\"post\" id=\"form_11\"></form>
<form action=\"\" method=\"post\" id=\"form_12\"></form>
<form action=\"\" method=\"post\" id=\"form_13\"></form>
<form action=\"\" method=\"post\" id=\"form_14\"></form>
<form action=\"\" method=\"post\" id=\"form_15\"></form>
<form action=\"\" method=\"post\" id=\"form_16\"></form>
<form action=\"\" method=\"post\" id=\"form_17\"></form>
<form action=\"\" method=\"post\" id=\"form_18\"></form>
<form action=\"\" method=\"post\" id=\"form_19\"></form>
<form action=\"\" method=\"post\" id=\"form_20\"></form>
<form action=\"\" method=\"post\" id=\"form_21\"></form>
<form action=\"\" method=\"post\" id=\"form_22\"></form>
<form action=\"\" method=\"post\" id=\"form_23\"></form>
<form action=\"\" method=\"post\" id=\"form_24\"></form>
<form action=\"\" method=\"post\" id=\"form_25\"></form>
<form action=\"\" method=\"post\" id=\"form_26\"></form>



<table style=\"width:80%; margin-left:10%;\" class=\"ca\">
<tr style=\"height:20px; background-color:#af504c; text-align:center;\">
    <td colspan=2; style=\"color:white;\">Pays</td>
    <td style=\"color:white;\">Seuil HT</td>
    <td style=\"color:white;\">Monaie Locale</td>
    <td style=\"color:white;\">Cours Devise</td>
    <td style=\"color:white;\">Alerte</td>
    <td style=\"color:white;\">TVA Locale</td>
    <td style=\"color:white;\">Numéro TVA</td>
    <td style=\"color:white;\">Valider</td>
</tr>
<tr style=\"height:20px;\">   ";/*
  ___  ___   _____ 
 | _ \/_\ \ / / __|
 |  _/ _ \ V /\__ \
 |_|/_/ \_\_| |___/
                                            style=\"width:20%; border:none;\"               
*/
$i = 0;
$a = 1;
while ($i < 26)
{
 echo "
    <td style=\"text-align:center;\"><img src=\"../images/drapeaux/br/".$flag[$i].".gif\"></td>
    <td style=\"text-align:center;\">".$name[$i]."</td>
    <td style=\"text-align:center;\">".number_format($seuil[$i],0,',',' ')."&euro;</td>
    <td><input style=\"width:70%; text-align:right;\" class=\"input\" type=\"text\" name=\"given_value\" form=\"form_".$a."\" value=\"".$given[$i]."\"> ".$currency[$i]."</td>
    <td style=\"text-align:center;\">".$taux[$i]."</td>
    <td style=\"text-align:center;\">".number_format($alerte[$i],0,',',' ')." &euro;</td>
    <td><input style=\"text-align:center;\" class=\"input\" type=\"text\" name=\"tva_locale\" form=\"form_".$a."\" value=\"".$tva_locale[$i]." %\"></td>
    <td><input class=\"input\" type=\"text\" name=\"num_tva\" form=\"form_".$a."\" value=\"".$num_tva[$i]."\"></td>
    <input type=\"hidden\" name=\"pays_id\" form=\"form_".$a."\" value=\"".$a."\">
    <td style=\"text-align:center;\"><input style=\"width:90%;\" type=\"submit\" form=\"form_".$a."\" value=\"ok\"></td>
</tr>";
$i++;
$a++;
}
echo "</table>
  ";

echo "<br><br><br>";
echo "<br><br><br>";
echo "<br><br><br>";
echo "<br><br><br>";
echo "<br><br><br>";
echo "<br><br><br>";
echo "<br><br><br>";
echo "<br><br><br>";
echo "<br><br><br>";
?><p style="font-size:13px; text-align:center; margin-top: 33%;">NB : Veillez à remplir les lignes une par une.'OK' n'enregistre que les informations de la ligne concernée.</p>
<?php include "../footer.php";
?>