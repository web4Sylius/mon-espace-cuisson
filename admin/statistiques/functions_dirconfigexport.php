<?php

/*
FFFFFFF  OOOOO  NN   NN  CCCCC  TTTTTTT IIIII  OOOOO  NN   NN  SSSSS  
FF      OO   OO NNN  NN CC    C   TTT    III  OO   OO NNN  NN SS      
FFFF    OO   OO NN N NN CC        TTT    III  OO   OO NN N NN  SSSSS  
FF      OO   OO NN  NNN CC    C   TTT    III  OO   OO NN  NNN      SS 
FF       OOOO0  NN   NN  CCCCC    TTT   IIIII  OOOO0  NN   NN  SSSSS                                                                       
*/






/***********************************************
Fonctions suivies d'_28 pick en BDD un champ   *
spécifique pour chacun des 28 pays et le return*
dans un tableau                                *
************************************************/

function name_28()
{
  $i = 0;
  $a = 1;
  while ($i < 26)
  {
    $name[$i] = name($a);
    $i++;
    $a++;
  }
  return $name;
}

function given_28()
{
  $i = 0;
  $a = 1;
  while ($i < 26)
  {
    $given[$i] = given_value($a);
    $i++;
    $a++;
  }
  return $given;
}

function currency_28()
{
  $i = 0;
  $a = 1;
  while ($i < 26)
  {
    $given[$i] = currency($a);
    $i++;
    $a++;
  }
  return $given;
}


function taux_28()
{
  $i = 0;
  $a = 1;
  while ($i < 26)
  {
    $given[$i] = taux($a);
    $i++;
    $a++;
  }
  return $given;
}

function prc_80_alert($data)
{
 $retour = (80 * $data) / 100;
 return $retour;
}


function tva_locale_28()
{
  $i = 0;
  $a = 1;
  while ($i < 26)
  {
    $given[$i] = tva_locale($a);
    $i++;
    $a++;
  }
  return $given;
}


function num_tva_28()
{
  $i = 0;
  $a = 1;
  while ($i < 26)
  {
    $given[$i] = num_tva($a);
    $i++;
    $a++;
  }
  return $given;
}


/************************
Select du taux de change*
*************************/
function taux($pays_id)
{
  global $DB_site2;

        $sql = "";
        $cams = "";
        $sql = "SELECT taux FROM taux_de_change_eu WHERE pays_id='".$pays_id."'";
        $cams = $DB_site2->query($sql);

             while($row = $DB_site2->fetch_array($cams)){
              $chiffre = $row['taux'];
            }
        return $chiffre;
}



/************************
Select du nom du pays   *
*************************/
function name($pays_id)
{
  global $DB_site2;

        $sql = "";
        $cams = "";
        $sql = "SELECT pays_txt FROM taux_de_change_eu WHERE pays_id='".$pays_id."'";
        $cams = $DB_site2->query($sql);

             while($row = $DB_site2->fetch_array($cams)){
              $chiffre = $row['pays_txt'];
            }
        return $chiffre;
}


/********************************
Select du nom de la monnaie pays*
*********************************/
function currency($pays_id)
{
  global $DB_site2;

        $sql = "";
        $cams = "";
        $sql = "SELECT currency FROM taux_de_change_eu WHERE pays_id='".$pays_id."'";
        $cams = $DB_site2->query($sql);

             while($row = $DB_site2->fetch_array($cams)){
              $chiffre = $row['currency'];
            }
        return $chiffre;
}



/***************************************************
Select de la valeur donnée pour le pays en question*
****************************************************/
function given_value($pays_id)
{
  global $DB_site2;

        $sql = "";
        $cams = "";
        $sql = "SELECT given_value FROM taux_de_change_eu WHERE pays_id='".$pays_id."'";
        $cams = $DB_site2->query($sql);

             while($row = $DB_site2->fetch_array($cams)){
              $chiffre = $row['given_value'];
            }
        return $chiffre;
}



/***********************
Select de la tva_locale*
************************/
function tva_locale($pays_id)
{
  global $DB_site2;

        $sql = "";
        $cams = "";
        $sql = "SELECT tva_locale FROM taux_de_change_eu WHERE pays_id='".$pays_id."'";
        $cams = $DB_site2->query($sql);

             while($row = $DB_site2->fetch_array($cams)){
              $chiffre = $row['tva_locale'];
            }
        return $chiffre;
}




/****************************
Select du numéro tva du pays*
*****************************/
function num_tva($pays_id)
{
  global $DB_site2;

        $sql = "";
        $cams = "";
        $sql = "SELECT num_tva FROM taux_de_change_eu WHERE pays_id='".$pays_id."'";
        $cams = $DB_site2->query($sql);

             while($row = $DB_site2->fetch_array($cams)){
              $chiffre = $row['num_tva'];
            }
        return $chiffre;
}

?>