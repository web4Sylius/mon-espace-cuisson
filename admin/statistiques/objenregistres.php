<?php

require "../admin_global.php";
include "../header.php";
include "menu_2.php";
include "../../class/facture.class.php";





/***********************************************************************************************************************
 ici on récupère les données envoyées par formulaire.                                                                  *
 Le site_id permet de savoir à qui le formulaire fait référence (.fr, .com, .uk etc)                                   *
 Ensuite chaque mois contient une valeur qu'il va falloir insérer ou update en base de donnée selon l'existance ou non *
 des données pour l'année et le site concerné.                                                                         *
************************************************************************************************************************/

if(isset($_POST['site_id']))
{
  if(isset($_POST["annee"], $_POST["site_id"], $_POST["janvier"], $_POST["fevrier"], $_POST["mars"], $_POST["avril"], $_POST["mai"], $_POST["juin"], $_POST["juillet"], $_POST["aout"], $_POST["septembre"], $_POST["octobre"], $_POST["novembre"], $_POST["decembre"]))
  {

    $annee = $_POST['annee'];
    $site_id = $_POST['site_id'];

    $montant[0] = $_POST['janvier'];
    $montant[1] = $_POST['fevrier'];
    $montant[2] = $_POST['mars'];
    $montant[3] = $_POST['avril'];
    $montant[4] = $_POST['mai'];
    $montant[5] = $_POST['juin'];
    $montant[6] = $_POST['juillet'];
    $montant[7] = $_POST['aout'];
    $montant[8] = $_POST['septembre'];
    $montant[9] = $_POST['octobre'];
    $montant[10] = $_POST['novembre'];
    $montant[11] = $_POST['decembre'];


    $i = 0;
    $monthM = 1;
    while ($i <= 11)
    {
      $sql = "SELECT obj_montant FROM objectifs_enregistres WHERE obj_annee='".$annee."' AND obj_mois='".$monthM."' AND site_id='".$site_id."'";
      $cams = $DB_site2->query($sql);

        if($DB_site2->num_rows($cams)>0)
        {
          $sql = "";
          $cams = "";  
          $sql = "UPDATE objectifs_enregistres SET obj_montant='".$montant[$i]."' WHERE obj_annee='".$annee."' AND obj_mois='".$monthM."' AND site_id='".$site_id."'";
          $cams = $DB_site2->query($sql);
        }
        else {
          $sql = "";
          $cams = "";
          $sql = "INSERT INTO objectifs_enregistres (obj_montant, obj_mois, obj_annee, site_id) VALUES ('".$montant[$i]."', '".$monthM."', '".$annee."', '".$site_id."')";
          $cams = $DB_site2->query($sql);
        }

      $i++;
      $monthM++;
    }
  }
}







/**********************************************************
Ici on initialise les propriétées CSS des tableaux à venir*
***********************************************************/

echo "
<style>
.input {width:100%;}
.stat_rouge {color:red; text-align: right;}
.stat_vert {color:green; text-align: right;}
.ca_mois { color:black; border-collapse:collapse; border:solid thin black; margin:auto; float:left; }
.ca_mois td { border:solid thin black; width:100px; }
.ca { color:black; border-collapse:collapse; border:solid thin black; margin:auto; float:left; }
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


<h1>Objectifs Enregistrés</h1>

<?php
include(BACK_PATH.'/statistiques/statistiques_adm.php');

if(!isset($year)) $year=date("Y");
















/**********************************************************************************************
Fonction qui permet de récupérer les 12 valeurs d'objectif mensuelle d'une année pour un site.*
***********************************************************************************************/
function obj_montant($annee, $site_id)
{
  global $DB_site2;

        $sql = "";
        $cams = "";
        $sql = "SELECT obj_mois, obj_montant FROM `objectifs_enregistres` WHERE obj_annee='".$annee."' AND site_id='".$site_id."' GROUP BY obj_mois";
        $cams = $DB_site2->query($sql);

             while($row = $DB_site2->fetch_array($cams)){
              $chiffre[] = $row['obj_montant'];
            }
        return $chiffre;
}


$chiffre_mcvfr = obj_montant($year, 1);
$chiffre_mwccom = obj_montant($year, 2);
$chiffre_mwcuk = obj_montant($year, 3);
$chiffre_marketplace = obj_montant($year, 4);
$chiffre_mvinoes = obj_montant($year, 5);



/********************************************************************************
Calcul des différents totaux et autres valeurs qui s'afficheront dans le tableau*
*********************************************************************************/
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


$mcv_total = $chiffre_mcvfr[0] + $chiffre_mcvfr[1] + $chiffre_mcvfr[2] + $chiffre_mcvfr[3] + $chiffre_mcvfr[4] + $chiffre_mcvfr[5] + $chiffre_mcvfr[6] + $chiffre_mcvfr[7] + $chiffre_mcvfr[8] + $chiffre_mcvfr[9] + $chiffre_mcvfr[10] + $chiffre_mcvfr[11];

$mwccom_total = $chiffre_mwccom[0] + $chiffre_mwccom[1] + $chiffre_mwccom[2] + $chiffre_mwccom[3] + $chiffre_mwccom[4] + $chiffre_mwccom[5] + $chiffre_mwccom[6] + $chiffre_mwccom[7] + $chiffre_mwccom[8] + $chiffre_mwccom[9] + $chiffre_mwccom[10] + $chiffre_mwccom[11]; 

$mwcuk_total = $chiffre_mwcuk[0] + $chiffre_mwcuk[1] + $chiffre_mwcuk[2] + $chiffre_mwcuk[3] + $chiffre_mwcuk[4] + $chiffre_mwcuk[5] + $chiffre_mwcuk[6] + $chiffre_mwcuk[7] + $chiffre_mwcuk[8] + $chiffre_mwcuk[9] + $chiffre_mwcuk[10] + $chiffre_mwcuk[11];

$marketplaces_total = $chiffre_marketplace[0] + $chiffre_marketplace[1] + $chiffre_marketplace[2] + $chiffre_marketplace[3] + $chiffre_marketplace[4] + $chiffre_marketplace[5] + $chiffre_marketplace[6] + $chiffre_marketplace[7] + $chiffre_marketplace[8] + $chiffre_marketplace[9] + $chiffre_marketplace[10] + $chiffre_marketplace[11];

$mvinoes_total = $chiffre_mvinoes[0] + $chiffre_mvinoes[1] + $chiffre_mvinoes[2] + $chiffre_mvinoes[3] + $chiffre_mvinoes[4] + $chiffre_mvinoes[5] + $chiffre_mvinoes[6] + $chiffre_mvinoes[7] + $chiffre_mvinoes[8] + $chiffre_mvinoes[9] + $chiffre_mvinoes[10] + $chiffre_mvinoes[11];

$all_total = $total[0] + $total[1] + $total[2] + $total[3] + $total[4] + $total[5] + $total[6] + $total[7] + $total[8] + $total[9] + $total[10] + $total[11];

















echo "<p style=\"margin-top:40px; margin-bottom:15px;\"align=\"center\">Année : <select name=\"year\" onchange=\"document.location='objenregistres.php?year='+this.value\">";

for($y=2010;$y<=2025;$y++) echo "<option value=\"$y\"".($y==$year?" selected":"").">$y</option>";

echo "</select></p>
<form action=\"\" method=\"post\" id=\"form_mcv_fr\"></form>
<form action=\"\" method=\"post\" id=\"form_mcv_en\"></form>
<form action=\"\" method=\"post\" id=\"form_mcv_uk\"></form>
<form action=\"\" method=\"post\" id=\"form_mcv_es\"></form>
<form action=\"\" method=\"post\" id=\"form_mcv_mp\"></form>






";
/*
MM    MM  OOOOO  IIIII  SSSSS  
MMM  MMM OO   OO  III  SS      
MM MM MM OO   OO  III   SSSSS  
MM    MM OO   OO  III       SS 
MM    MM  OOOO0  IIIII  SSSSS                                                                                         
*/

echo"

<table class=\"ca_mois\">
<tr style=\"height:20px;\"><td colspan=\"1\" class=\"ca_title\"></td></tr>
<tr><td colspan=\"1\" class=\"ca_second_title\">Mois</td></tr>
    <tr style=\"height:26px;\">
            <td>Janvier</td>
    </tr>
    <tr style=\"height:26px;\">
            <td>Fevrier</td>
    </tr>
    <tr style=\"height:26px;\">
            <td>Mars</td>
    </tr>
    <tr style=\"height:26px;\">
            <td>Avril</td>
    </tr>
    <tr style=\"height:26px;\">
            <td>Mai</td>
    </tr>
    <tr style=\"height:26px;\">
            <td>Juin</td>
    </tr>
    <tr style=\"height:26px;\">
            <td>Juillet</td>
    </tr>
    <tr style=\"height:26px;\">
            <td>Aout</td>
    </tr>
    <tr style=\"height:26px;\">
            <td>Septembre</td>
    </tr>
    <tr style=\"height:26px;\">
            <td>Octobre</td>
    </tr>
    <tr style=\"height:26px;\">
            <td>Novembre</td>
    </tr>
    <tr style=\"height:26px;\">
            <td>Decembre</td>
    </tr>
    <tr style=\"height:26px;\">
            <td style=\"font-weight:bold;\">Total annuel</td>
    </tr>
</table>





";
/*
TTTTTTT  OOOOO  TTTTTTT   AAA   LL      
  TTT   OO   OO   TTT    AAAAA  LL      
  TTT   OO   OO   TTT   AA   AA LL      
  TTT   OO   OO   TTT   AAAAAAA LL      
  TTT    OOOO0    TTT   AA   AA LLLLLLL                                         
*/

echo"


<table class=\"ca\">
<tr style=\"height:20px;\"><td colspan=\"1\" class=\"ca_title\"></td></tr>
<tr><td colspan=\"1\" class=\"ca_second_title\">Total</td></tr>
    <tr style=\"height:26px;\">
            <td>$total[0] &euro;</td>
    </tr>
    <tr style=\"height:26px;\">
            <td>$total[1] &euro;</td>
    </tr>
    <tr style=\"height:26px;\">
            <td>$total[2] &euro;</td>
    </tr>
    <tr style=\"height:26px;\">
            <td>$total[3] &euro;</td>
    </tr>
    <tr style=\"height:26px;\">
            <td>$total[4] &euro;</td>
    </tr>
    <tr style=\"height:26px;\">
            <td>$total[5] &euro;</td>
    </tr>
    <tr style=\"height:26px;\">
            <td>$total[6] &euro;</td>
    </tr>
    <tr style=\"height:26px;\">
            <td>$total[7] &euro;</td>
    </tr>
    <tr style=\"height:26px;\">
            <td>$total[8] &euro;</td>
    </tr>
    <tr style=\"height:26px;\">
            <td>$total[9] &euro;</td>
    </tr>
    <tr style=\"height:26px;\">
            <td>$total[10] &euro;</td>
    </tr>
    <tr style=\"height:26px;\">
            <td>$total[11] &euro;</td>
    </tr>
    <tr style=\"height:26px;\">
            <td style=\"font-weight:bold;\">$all_total &euro;</td>
    </tr>
</table>











";
/*
MM    MM   AAA     CCCCC    AAA   VV     VV EEEEEEE    AAA    VV     VV IIIII NN   NN     FFFFFFF RRRRRR  
MMM  MMM  AAAAA   CC    C  AAAAA  VV     VV EE        AAAAA   VV     VV  III  NNN  NN     FF      RR   RR 
MM MM MM AA   AA  CC      AA   AA  VV   VV  EEEEE    AA   AA   VV   VV   III  NN N NN     FFFF    RRRRRR  
MM    MM AAAAAAA  CC    C AAAAAAA   VV VV   EE       AAAAAAA    VV VV    III  NN  NNN ... FF      RR  RR  
MM    MM AA   AA   CCCCC  AA   AA    VVV    EEEEEEE  AA   AA     VVV    IIIII NN   NN ... FF      RR   RR 
*/

echo"




<table class=\"ca\">
<tr style=\"height:20px;\"><td colspan=\"6\" class=\"ca_title\"></td></tr>

    <tr><td colspan=\"1\" class=\"ca_second_title\">Ma cave a vin.fr</td></tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"janvier\" form=\"form_mcv_fr\" value=\"$chiffre_mcvfr[0]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"fevrier\" form=\"form_mcv_fr\" value=\"$chiffre_mcvfr[1]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"mars\" form=\"form_mcv_fr\" value=\"$chiffre_mcvfr[2]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"avril\" form=\"form_mcv_fr\" value=\"$chiffre_mcvfr[3]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"mai\" form=\"form_mcv_fr\" value=\"$chiffre_mcvfr[4]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"juin\" form=\"form_mcv_fr\" value=\"$chiffre_mcvfr[5]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"juillet\" form=\"form_mcv_fr\" value=\"$chiffre_mcvfr[6]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"aout\" form=\"form_mcv_fr\" value=\"$chiffre_mcvfr[7]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"septembre\" form=\"form_mcv_fr\" value=\"$chiffre_mcvfr[8]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"octobre\" form=\"form_mcv_fr\" value=\"$chiffre_mcvfr[9]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"novembre\" form=\"form_mcv_fr\" value=\"$chiffre_mcvfr[10]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"decembre\" form=\"form_mcv_fr\" value=\"$chiffre_mcvfr[11]\"></td>
    </tr>
            <input type=\"hidden\" name=\"site_id\" form=\"form_mcv_fr\" value=\"1\">
            <input type=\"hidden\" name=\"annee\" form=\"form_mcv_fr\" value=\"$year\">
    <tr style=\"height:26px;\">
            <td>$mcv_total &euro;</td>
    </tr>
    <tr>
            <td><input type=\"submit\" form=\"form_mcv_fr\" value=\"ok\"></td>
    </tr>
</table>



















";
/*
MM    MM YY   YY  WW      WW IIIII NN   NN EEEEEEE   CCCCC    AAA   BBBBB   IIIII NN   NN EEEEEEE TTTTTTT      CCCCC   OOOOO  MM    MM 
MMM  MMM YY   YY  WW      WW  III  NNN  NN EE       CC    C  AAAAA  BB   B   III  NNN  NN EE        TTT       CC    C OO   OO MMM  MMM 
MM MM MM  YYYYY   WW   W  WW  III  NN N NN EEEEE    CC      AA   AA BBBBBB   III  NN N NN EEEEE     TTT       CC      OO   OO MM MM MM 
MM    MM   YYY     WW WWW WW  III  NN  NNN EE       CC    C AAAAAAA BB   BB  III  NN  NNN EE        TTT   ... CC    C OO   OO MM    MM 
MM    MM   YYY      WW   WW  IIIII NN   NN EEEEEEE   CCCCC  AA   AA BBBBBB  IIIII NN   NN EEEEEEE   TTT   ...  CCCCC   OOOO0  MM    MM 
*/

echo"


<table class=\"ca\">
<tr style=\"height:20px;\"><td colspan=\"6\" class=\"ca_title\">Objectifs</td></tr>

    <tr><td colspan=\"1\" class=\"ca_second_title\">My wine cabinet.com</td></tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"janvier\" form=\"form_mcv_en\" value=\"$chiffre_mwccom[0]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"fevrier\" form=\"form_mcv_en\" value=\"$chiffre_mwccom[1]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"mars\" form=\"form_mcv_en\" value=\"$chiffre_mwccom[2]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"avril\" form=\"form_mcv_en\" value=\"$chiffre_mwccom[3]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"mai\" form=\"form_mcv_en\" value=\"$chiffre_mwccom[4]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"juin\" form=\"form_mcv_en\" value=\"$chiffre_mwccom[5]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"juillet\" form=\"form_mcv_en\" value=\"$chiffre_mwccom[6]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"aout\" form=\"form_mcv_en\" value=\"$chiffre_mwccom[7]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"septembre\" form=\"form_mcv_en\" value=\"$chiffre_mwccom[8]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"octobre\" form=\"form_mcv_en\" value=\"$chiffre_mwccom[9]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"novembre\" form=\"form_mcv_en\" value=\"$chiffre_mwccom[10]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"decembre\" form=\"form_mcv_en\" value=\"$chiffre_mwccom[11]\"></td>
    </tr>
            <input type=\"hidden\" name=\"site_id\" form=\"form_mcv_en\" value=\"2\">
            <input type=\"hidden\" name=\"annee\" form=\"form_mcv_en\" value=\"$year\">
    <tr style=\"height:26px;\">
            <td>$mwccom_total &euro;</td>
    </tr>
    <tr>
            <td><input type=\"submit\" form=\"form_mcv_en\" value=\"ok\"></td>
    </tr>
</table>















";
/*
MM    MM YY   YY  WW      WW IIIII NN   NN EEEEEEE   CCCCC    AAA   BBBBB   IIIII NN   NN EEEEEEE TTTTTTT      CCCCC   OOOOO      UU   UU KK  KK 
MMM  MMM YY   YY  WW      WW  III  NNN  NN EE       CC    C  AAAAA  BB   B   III  NNN  NN EE        TTT       CC    C OO   OO     UU   UU KK KK  
MM MM MM  YYYYY   WW   W  WW  III  NN N NN EEEEE    CC      AA   AA BBBBBB   III  NN N NN EEEEE     TTT       CC      OO   OO     UU   UU KKKK   
MM    MM   YYY     WW WWW WW  III  NN  NNN EE       CC    C AAAAAAA BB   BB  III  NN  NNN EE        TTT   ... CC    C OO   OO ... UU   UU KK KK  
MM    MM   YYY      WW   WW  IIIII NN   NN EEEEEEE   CCCCC  AA   AA BBBBBB  IIIII NN   NN EEEEEEE   TTT   ...  CCCCC   OOOO0  ...  UUUUU  KK  KK 
*/

echo"

<table class=\"ca\">
<tr style=\"height:20px;\"><td colspan=\"6\" class=\"ca_title\">Enregistrés</td></tr>

    <tr><td colspan=\"1\" class=\"ca_second_title\">My wine cabinet.co.uk</td></tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"janvier\" form=\"form_mcv_uk\" value=\"$chiffre_mwcuk[0]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"fevrier\" form=\"form_mcv_uk\" value=\"$chiffre_mwcuk[1]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"mars\" form=\"form_mcv_uk\" value=\"$chiffre_mwcuk[2]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"avril\" form=\"form_mcv_uk\" value=\"$chiffre_mwcuk[3]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"mai\" form=\"form_mcv_uk\" value=\"$chiffre_mwcuk[4]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"juin\" form=\"form_mcv_uk\" value=\"$chiffre_mwcuk[5]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"juillet\" form=\"form_mcv_uk\" value=\"$chiffre_mwcuk[6]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"aout\" form=\"form_mcv_uk\" value=\"$chiffre_mwcuk[7]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"septembre\" form=\"form_mcv_uk\" value=\"$chiffre_mwcuk[8]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"octobre\" form=\"form_mcv_uk\" value=\"$chiffre_mwcuk[9]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"novembre\" form=\"form_mcv_uk\" value=\"$chiffre_mwcuk[10]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"decembre\" form=\"form_mcv_uk\" value=\"$chiffre_mwcuk[11]\"></td>
    </tr>
            <input type=\"hidden\" name=\"site_id\" form=\"form_mcv_uk\" value=\"3\">
            <input type=\"hidden\" name=\"annee\" form=\"form_mcv_uk\" value=\"$year\">
    <tr style=\"height:26px;\">
            <td>$mwcuk_total &euro;</td>
    </tr>
    <tr>
            <td><input type=\"submit\" form=\"form_mcv_uk\" value=\"ok\"></td>
    </tr>
</table>










";
/*
MM    MM IIIII  VV     VV IIIII NN   NN  OOOOO  TTTTTTT EEEEEEE  CCCCC    AAA       EEEEEEE  SSSSS  
MMM  MMM  III   VV     VV  III  NNN  NN OO   OO   TTT   EE      CC    C  AAAAA      EE      SS      
MM MM MM  III    VV   VV   III  NN N NN OO   OO   TTT   EEEEE   CC      AA   AA     EEEEE    SSSSS  
MM    MM  III     VV VV    III  NN  NNN OO   OO   TTT   EE      CC    C AAAAAAA ... EE           SS 
MM    MM IIIII     VVV    IIIII NN   NN  OOOO0    TTT   EEEEEEE  CCCCC  AA   AA ... EEEEEEE  SSSSS  
*/

echo"



<table class=\"ca\">
<tr style=\"height:20px;\"><td colspan=\"6\" class=\"ca_title\"></td></tr>

    <tr><td colspan=\"1\" class=\"ca_second_title\">Mi vinoteca.es</td></tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"janvier\" form=\"form_mcv_es\" value=\"$chiffre_mvinoes[0]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"fevrier\" form=\"form_mcv_es\" value=\"$chiffre_mvinoes[1]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"mars\" form=\"form_mcv_es\" value=\"$chiffre_mvinoes[2]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"avril\" form=\"form_mcv_es\" value=\"$chiffre_mvinoes[3]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"mai\" form=\"form_mcv_es\" value=\"$chiffre_mvinoes[4]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"juin\" form=\"form_mcv_es\" value=\"$chiffre_mvinoes[5]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"juillet\" form=\"form_mcv_es\" value=\"$chiffre_mvinoes[6]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"aout\" form=\"form_mcv_es\" value=\"$chiffre_mvinoes[7]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"septembre\" form=\"form_mcv_es\" value=\"$chiffre_mvinoes[8]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"octobre\" form=\"form_mcv_es\" value=\"$chiffre_mvinoes[9]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"novembre\" form=\"form_mcv_es\" value=\"$chiffre_mvinoes[10]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"decembre\" form=\"form_mcv_es\" value=\"$chiffre_mvinoes[11]\"></td>
    </tr>
            <input type=\"hidden\" name=\"site_id\" form=\"form_mcv_es\" value=\"5\">
            <input type=\"hidden\" name=\"annee\" form=\"form_mcv_es\" value=\"$year\">
    <tr style=\"height:26px;\">
            <td>$mvinoes_total &euro;</td>
    </tr>
    <tr>
            <td><input type=\"submit\" form=\"form_mcv_es\" value=\"ok\"></td>
    </tr>
</table>












";
/*
MM    MM   AAA   RRRRRR  KK  KK EEEEEEE TTTTTTT  PPPPPP  LL        AAA    CCCCC  EEEEEEE  SSSSS  
MMM  MMM  AAAAA  RR   RR KK KK  EE        TTT    PP   PP LL       AAAAA  CC    C EE      SS      
MM MM MM AA   AA RRRRRR  KKKK   EEEEE     TTT    PPPPPP  LL      AA   AA CC      EEEEE    SSSSS  
MM    MM AAAAAAA RR  RR  KK KK  EE        TTT    PP      LL      AAAAAAA CC    C EE           SS 
MM    MM AA   AA RR   RR KK  KK EEEEEEE   TTT    PP      LLLLLLL AA   AA  CCCCC  EEEEEEE  SSSSS                                                                                                 
*/

echo"

<table class=\"ca\">
<tr style=\"height:20px;\"><td colspan=\"6\" class=\"ca_title\"></td></tr>

    <tr><td colspan=\"1\" class=\"ca_second_title\">MarketPlaces</td></tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"janvier\" form=\"form_mcv_mp\" value=\"$chiffre_marketplace[0]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"fevrier\" form=\"form_mcv_mp\" value=\"$chiffre_marketplace[1]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"mars\" form=\"form_mcv_mp\" value=\"$chiffre_marketplace[2]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"avril\" form=\"form_mcv_mp\" value=\"$chiffre_marketplace[3]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"mai\" form=\"form_mcv_mp\" value=\"$chiffre_marketplace[4]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"juin\" form=\"form_mcv_mp\" value=\"$chiffre_marketplace[5]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"juillet\" form=\"form_mcv_mp\" value=\"$chiffre_marketplace[6]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"aout\" form=\"form_mcv_mp\" value=\"$chiffre_marketplace[7]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"septembre\" form=\"form_mcv_mp\" value=\"$chiffre_marketplace[8]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"octobre\" form=\"form_mcv_mp\" value=\"$chiffre_marketplace[9]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"novembre\" form=\"form_mcv_mp\" value=\"$chiffre_marketplace[10]\"></td>
    </tr>
    <tr>
            <td><input class=\"input\" type=\"text\" name=\"decembre\" form=\"form_mcv_mp\" value=\"$chiffre_marketplace[11]\"></td>
    </tr>
            <input type=\"hidden\" name=\"site_id\" form=\"form_mcv_mp\" value=\"4\">
            <input type=\"hidden\" name=\"annee\" form=\"form_mcv_mp\" value=\"$year\">
    <tr style=\"height:26px;\">
            <td>$marketplaces_total &euro;</td>
    </tr>
    <tr>
            <td><input type=\"submit\" form=\"form_mcv_mp\" value=\"ok\"></td>
    </tr>
</table>



  ";
echo "<br>";
echo "<br>";
echo "<br>";
?><p style="font-size:13px; text-align:center; margin-top: 33%;">NB : Veillez à remplir les colonnes une par une.'OK' n'enregistre que les informations de la colonne concernée.</p>
<?php include "../footer.php";


?>