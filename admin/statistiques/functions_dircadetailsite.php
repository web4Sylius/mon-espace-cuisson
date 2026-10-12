<?php

/*
FFFFFFF  OOOOO  NN   NN  CCCCC  TTTTTTT IIIII  OOOOO  NN   NN  SSSSS  
FF      OO   OO NNN  NN CC    C   TTT    III  OO   OO NNN  NN SS      
FFFF    OO   OO NN N NN CC        TTT    III  OO   OO NN N NN  SSSSS  
FF      OO   OO NN  NNN CC    C   TTT    III  OO   OO NN  NNN      SS 
FF       OOOO0  NN   NN  CCCCC    TTT   IIIII  OOOO0  NN   NN  SSSSS                                                                   
*/




/*

function ca($mois,$annee,$etat,$prov="",$limit=null,$and="", $region){

    global $DB_site2;
  
    $chiffremois = 0;
    
    if($mois<10) $mois = "0".$mois;
    if(is_array($etat)) $etat = implode(",",$etat);
    
    //Remise HT = TOT HT hors remise x (Remise TTC / TOT TTC hors remise)
    $sql = "SELECT SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) as 'prixHT', MONTH(dateexpedition), f.prixport / (1 + f.tvaport / 100) as 'portHT', CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) + (f.prixport / (1 + f.tvaport / 100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END as remiseHT FROM facture f INNER JOIN lignefacture lf ON (lf.factureid = f.facturid) WHERE f.valide IN (5,8) AND cast(f.dateexpedition as date) BETWEEN '2016-".$mois."-01' AND '2016-".$mois."-31' GROUP BY factureid";
    $cams = $DB_site2->query($sql);

    while($cam = $DB_site2->fetch_array($cams)){
      $chiffremois += $cam['prixHT'] + $cam['portHT'] - $cam['remiseHT'];
    }

    return $chiffremois;
}

CUMUL DU CHIFFRE AFFAIRE

*/












/********************************************************************************************
 Récupère les montants des objectifs pour chaque mois - obj = objectif / montant = montant !*
********************************************************************************************/
function obj_montant($annee, $site_id)
{
  global $DB_site2;

        $sql = "";
        $cams = "";
        $sql = "SELECT obj_mois, obj_montant FROM `objectifs_factures` WHERE obj_annee='".$annee."' AND site_id='".$site_id."' GROUP BY obj_mois";
        $cams = $DB_site2->query($sql);

             while($row = $DB_site2->fetch_array($cams)){
              $chiffre[] = $row['obj_montant'];
            }
        return $chiffre;
}

/************************************************************************
Calcul du chiffre d'affaire sur le site fr ou en(com) selon le paramètre*
*************************************************************************/



function ca_mcvf_mwcom_old($mois,$annee,$etat,$prov="",$limit=null,$and="", $region){

    global $DB_site2;
  
    $chiffremois = 0;
    
    if($mois<10) $mois = "0".$mois;
    if(is_array($etat)) $etat = implode(",",$etat);
    
    //Remise HT = TOT HT hors remise x (Remise TTC / TOT TTC hors remise)
    $sql = "SELECT SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) as 'prixHT', MONTH(dateexpedition), f.prixport / (1 + f.tvaport / 100) as 'portHT', CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) + (f.prixport / (1 + f.tvaport / 100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END as remiseHT FROM facture f INNER JOIN lignefacture lf ON (lf.factureid = f.facturid) INNER JOIN utilisateur ON (f.userid = utilisateur.userid) WHERE f.valide IN (5,8) AND cast(f.dateexpedition as date) BETWEEN '".$annee."-".$mois."-01' AND '".$annee."-".$mois."-31'AND f.site_code IN('base', '') AND f.provenance='' AND utilisateur.langue ='".$region."' GROUP BY factureid";
    $cams = $DB_site2->query($sql);

    while($cam = $DB_site2->fetch_array($cams)){
      $chiffremois += $cam['prixHT'] + $cam['portHT'] - $cam['remiseHT'];
    }

    return $chiffremois;
}


function ca_uk_old($mois,$annee,$etat,$prov="",$limit=null,$and=""){

    global $DB_site2;
  
    $chiffremois = 0;
    
    if($mois<10) $mois = "0".$mois;
    if(is_array($etat)) $etat = implode(",",$etat);
    
    //Remise HT = TOT HT hors remise x (Remise TTC / TOT TTC hors remise)
    $sql = "SELECT SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) as 'prixHT', MONTH(dateexpedition), f.prixport / (1 + f.tvaport / 100) as 'portHT', CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) + (f.prixport / (1 + f.tvaport / 100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END as remiseHT FROM facture f INNER JOIN lignefacture lf ON (lf.factureid = f.facturid) INNER JOIN utilisateur ON (f.userid = utilisateur.userid) WHERE f.valide IN (5,8) AND cast(f.dateexpedition as date) BETWEEN '".$annee."-".$mois."-01' AND '".$annee."-".$mois."-31'AND f.site_code='uk' AND f.provenance='' AND utilisateur.langue ='EN' GROUP BY factureid";
    $cams = $DB_site2->query($sql);

    while($cam = $DB_site2->fetch_array($cams)){
      $chiffremois += $cam['prixHT'] + $cam['portHT'] - $cam['remiseHT'];
    }

    return $chiffremois;
}





function ca_mcvf_mwcom($annee,$etat,$prov="",$and="",$region){

    global $DB_site2;
    
    $sql = "SELECT SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) as 'prixHT', f.prixport / (1 + f.tvaport / 100) as 'portHT', CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) + (f.prixport / (1 + f.tvaport / 100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END as remiseHT";
    $sql .= " FROM facture f";
    $sql .= " INNER JOIN lignefacture lf ON (lf.factureid = f.facturid)";
    $sql .= " INNER JOIN utilisateur ON (f.userid = utilisateur.userid)";
    $sql .= " WHERE f.valide IN (".$etat.")";
    $sql .= " AND cast(f.dateexpedition as date) BETWEEN '".$annee."-01-01' AND '".$annee."-12-31'";
    $sql .= " AND f.provenance = '".$prov."'".$and;
    $sql .= " AND f.site_code IN('base', '') AND utilisateur.langue='".$region."'";
    $sql .= " GROUP BY MONTH(dateexpedition)";
    $cams = $DB_site2->query($sql);

    while($cam = $DB_site2->fetch_array($cams)){
      $chiffremois[] += $cam['prixHT'] + $cam['portHT'] - $cam['remiseHT'];
    }

    return $chiffremois;
}

/****************************************************
Calcul du chiffre d'affaire pour le site anglais(uk)*
*****************************************************/
function ca_uk($annee,$etat,$prov="",$and=""){

    global $DB_site2;
    
    $sql = "SELECT SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) as 'prixHT', f.prixport / (1 + f.tvaport / 100) as 'portHT', CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) + (f.prixport / (1 + f.tvaport / 100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END as remiseHT";
    $sql .= " FROM facture f";
    $sql .= " INNER JOIN lignefacture lf ON (lf.factureid = f.facturid)";
    $sql .= " INNER JOIN utilisateur ON (f.userid = utilisateur.userid)";
    $sql .= " WHERE f.valide IN (".$etat.")";
    $sql .= " AND cast(f.dateexpedition as date) BETWEEN '".$annee."-01-01' AND '".$annee."-12-31'";
    $sql .= " AND f.provenance = '".$prov."'".$and;
    $sql .= " AND f.site_code='uk' AND utilisateur.langue='EN'";
    $sql .= " GROUP BY MONTH(dateexpedition)";
    $cams = $DB_site2->query($sql);

    while($cam = $DB_site2->fetch_array($cams)){
      $chiffremois[] += $cam['prixHT'] + $cam['portHT'] - $cam['remiseHT'];
    }

    return $chiffremois;
}



/***********************************************************************************************************
Fonctions pour le calcul des variables, produits en croix pour les %, soustractions pour les chiffres bruts*
************************************************************************************************************/
function total_av_ret_objprcent($obj, $total, $lim_mois, $t)
{
  $j = date("j");
  $i = 1;
  $a = 0;
  $obj_cur = 0;
  while ($i < $lim_mois)  
  {
    $obj_cur += $obj[$a];
    $i++;
    $a++;
  }
  $valeur_obj = $obj[$a] / $t;
  $valeur_obj = $valeur_obj * $j;
  $obj_cur = $obj_cur + $valeur_obj;
  $retour = ($total * 100) / $obj_cur;
  return $retour;
}

function obj_mois_fmois($objectifs, $Caffaires, $lim_mois, $t)
{
  $j = date("j");
  $i = 1;
  $a = 0;
  while($i < $lim_mois)
  {
    $retour[$a] = ($Caffaires[$a] * 100) / $objectifs[$a];
    $i++;
    $a++;
  }
  $valeur_obj = $objectifs[$a] / $t;
  $valeur_obj = $valeur_obj * $j;
  $retour[$a] = ($Caffaires[$a] * 100) / $valeur_obj;
  return $retour;
}

function obj_mois_fmois_n($objectifs, $Caffaires, $lim_mois, $t)
{
  $j = date("j");
  $i = 1;
  $a = 0;
  while($i < $lim_mois)
  {
    $retour[$a] = ($Caffaires[$a] * 100) / $objectifs[$a] - 100;
    $i++;
    $a++;
  }
  $valeur_obj = $objectifs[$a] / $t;
  $valeur_obj = $valeur_obj * $j;
  $retour[$a] = ($Caffaires[$a] * 100) / $valeur_obj - 100;
  return $retour;
}

function av_ret_obj_fmois($objectifs, $Caffaires, $lim_mois, $t)
{
  $j = date("j");
  $i = 1;
  $a = 0;
  while ($i < $lim_mois)
  {
    $retour[$a] = $Caffaires[$a] - $objectifs[$a];
    $i++;
    $a++;
  }
  $valeur_obj = $objectifs[$a] / $t;
  $valeur_obj = $valeur_obj * $j;
  $retour[$a] = $Caffaires[$a] - $valeur_obj;
  return $retour;
}

function av_ret_objprcent_fmois($objectifs, $Caffaires, $lim_mois, $t)
{
  $j = date("j");
  $i = 1;
  $a = 0;
  while($i <= $lim_mois)
  {
    $retour[$a] = ($Caffaires[$a] * 100) / $objectifs[$a] - 100;
    $i++;
    $a++;
  }
  $valeur_obj = $Caffaires[$a] / $t;
  $valeur_obj = $valeur_obj * $j;
  $retour[$a] = ($Caffaires[$a] * 100) / $valeur_obj;
  return $retour;  
}

function obj_mois_f($objectifs, $Caffaires)
{
  $i = 1;
  $a = 0;
  while($i <= 12)
  {
    $retour[] = ($Caffaires[$a] * 100) / $objectifs[$a];
    $i++;
    $a++;
  }
  return $retour;
}

function obj_mois_f_n($objectifs, $Caffaires)
{
  $i = 1;
  $a = 0;
  while($i <= 12)
  {
    $retour[] = ($Caffaires[$a] * 100) / $objectifs[$a] - 100;
    $i++;
    $a++;
  }
  return $retour;
}

function av_ret_obj_f($objectifs, $Caffaires)
{
  $i = 1;
  $a = 0;
  while ($i <= 12)
  {
    $retour[] = $Caffaires[$a] - $objectifs[$a];
    $i++;
    $a++;
  }
  return $retour;
}

function av_ret_objprcent_f($objectifs, $Caffaires)
{
  $i = 1;
  $a = 0;
  while($i <= 12)
  {
    $retour[] = ($Caffaires[$a] * 100) / $objectifs[$a] - 100;
    $i++;
    $a++;
  }
  return $retour;  
}




/**************************************************************************
Calcul du nombre d'inscrits pour l'année EN COURS pour le site anglais(uk)*
***************************************************************************/
function c_inscrit_cur_uk($annee)
{
  global $DB_site2;

$j = date('j');
$d = date('d');
$t = date('t');
$m = date('m');


    $sql = "SELECT MONTH(dateinscription) MONTH, COUNT(*) COUNT";
    $sql .= " FROM utilisateur";
    $sql .= " WHERE cast(dateinscription as date) BETWEEN '".$annee."-01-01' AND '".$annee."-".$m."-".$d."'";
    $sql .= " AND site_code='uk' AND langue='EN'";
    $sql .= " AND infos NOT LIKE '%Contact mailing%'";
    $sql .= " GROUP BY MONTH(dateinscription)";
    $cams = $DB_site2->query($sql);

    while($cam = $DB_site2->fetch_array($cams)){
      $chiffremois[] = $cam['COUNT'];
    }
      return $chiffremois;
}


/*******************************************************************************************
Calcul du nombre d'inscrits sur l'année EN COURS pour le site fr ou en(com) selon paramètre*
********************************************************************************************/
function c_inscrit_cur_mwcom($annee, $region)
{
  global $DB_site2;

$j = date('j');
$d = date('d');
$t = date('t');
$m = date('m');


    $sql = "SELECT MONTH(dateinscription) MONTH, COUNT(*) COUNT";
    $sql .= " FROM utilisateur";
    $sql .= " WHERE cast(dateinscription as date) BETWEEN '".$annee."-01-01' AND '".$annee."-".$m."-".$d."'";
    $sql .= " AND site_code IN('base', '') AND langue='".$region."'";
    $sql .= " AND infos NOT LIKE '%Contact mailing%'";
    $sql .= " GROUP BY MONTH(dateinscription)";
    $cams = $DB_site2->query($sql);

    while($cam = $DB_site2->fetch_array($cams)){
      $chiffremois[] = $cam['COUNT'];
    }
      return $chiffremois;
}


/***********************************************************************************************
Calcul du nombre d'inscrits sur une année antérieure pour le site fr ou en(com) selon paramètre*
************************************************************************************************/
function inscrits_mcvfr_mwcom($annee, $region)
{
    global $DB_site2;

    $sql = "SELECT MONTH(dateinscription) MONTH, COUNT(*) COUNT FROM utilisateur WHERE YEAR(dateinscription)='".$annee."' AND site_code IN('base', '') AND langue='".$region."' AND infos NOT LIKE '%Contact mailing%' GROUP BY MONTH(dateinscription)";
    $cams = $DB_site2->query($sql);

      while($row = $DB_site2->fetch_array($cams)){
        $cam[] = $row['COUNT'];
      }

return $cam;
}


/*****************************************************************************
Calcul du nombre d'inscrits sur une année antérieure pour le site anglais(uk)*
******************************************************************************/
function inscrits_uk($annee)
{
    global $DB_site2;

    $sql = "SELECT MONTH(dateinscription) MONTH, COUNT(*) COUNT FROM utilisateur WHERE YEAR(dateinscription)='".$annee."' AND site_code='uk' AND langue='EN' AND infos NOT LIKE '%Contact mailing%' GROUP BY MONTH(dateinscription)";
    $cams = $DB_site2->query($sql);

      while($row = $DB_site2->fetch_array($cams)){
        $cam[] = $row['COUNT'];
      }

return $cam;
}


/***************************************************************************
Calcul du nombre de commandes sur l'année EN COURS pour le site anglais(uk)*
****************************************************************************/
function c_commande_cur_uk($annee)
{
  global $DB_site2;

$j = date('j');
$d = date('d');
$t = date('t');
$m = date('m');


    $sql = "SELECT MONTH(dateexpedition) MONTH, COUNT(*) COUNT FROM facture INNER JOIN utilisateur ON (facture.userid = utilisateur.userid) WHERE cast(dateexpedition as date) BETWEEN '".$annee."-01-01' AND '".$annee."-".$m."-".$d."' AND facture.site_code='uk' AND utilisateur.langue='EN' AND valide IN (5,8) GROUP BY MONTH(dateexpedition)";
    $cams = $DB_site2->query($sql);

    while($cam = $DB_site2->fetch_array($cams)){
      $chiffremois[] = $cam['COUNT'];
    }
      return $chiffremois;
}




/*********************************************************************************************
Calcul du nombre de commandes sur l'année EN COURS pour le site fr ou en(com) selon paramètre*
**********************************************************************************************/
function c_commande_cur_mwcom($annee, $region)
{
  global $DB_site2;

$j = date('j');
$d = date('d');
$t = date('t');
$m = date('m');


    $sql = "SELECT MONTH(dateexpedition) MONTH, COUNT(*) COUNT FROM facture INNER JOIN utilisateur ON (facture.userid = utilisateur.userid) WHERE cast(dateexpedition as date) BETWEEN '".$annee."-01-01' AND '".$annee."-".$m."-".$d."' AND facture.site_code IN('base', '') AND utilisateur.langue='".$region."' AND valide IN (5,8) GROUP BY MONTH(dateexpedition)";
    $cams = $DB_site2->query($sql);

    while($cam = $DB_site2->fetch_array($cams)){
      $chiffremois[] = $cam['COUNT'];
    }
      return $chiffremois;
}



/************************************************************************************************
Calcul du nombre de commandes sur une année antérieure pour le site fr ou en(com)selon paramètre*
*************************************************************************************************/
function commandes_mcvf_mwcom($annee, $region)
{
        global $DB_site2;

        $sql = "";
        $cams = "";
        $sql = "SELECT MONTH(dateexpedition) MONTH, COUNT(*) COUNT FROM facture INNER JOIN utilisateur ON (facture.userid = utilisateur.userid) WHERE YEAR(dateexpedition)='".$annee."' AND facture.site_code IN('base', '') AND utilisateur.langue='".$region."' AND valide IN (5,8) GROUP BY MONTH(dateexpedition)";
        $cams = $DB_site2->query($sql);

             while($row = $DB_site2->fetch_array($cams)){
              $cam[] = $row['COUNT'];
            }
          return $cam;
}


/*******************************************************************************
Calcul du nombre de commandes sur une année antérieure pour le site anglais(uk)*
********************************************************************************/
function commandes_uk($annee)
{
        global $DB_site2;

        $sql = "";
        $cams = "";
        $sql = "SELECT MONTH(dateexpedition) MONTH, COUNT(*) COUNT FROM facture INNER JOIN utilisateur ON (facture.userid = utilisateur.userid) WHERE YEAR(dateexpedition)='".$annee."' AND facture.site_code='uk' AND utilisateur.langue='EN' AND valide IN (5,8) GROUP BY MONTH(dateexpedition)";
        $cams = $DB_site2->query($sql);

             while($row = $DB_site2->fetch_array($cams)){
              $cam[] = $row['COUNT'];
            }
          return $cam;
}




/*************************************************************************************************************************************
Calcul du panier moyen sur une année donnée pour chacun des sites selon paramètre - pas de 'en cours' car non nécessaire pour moyenne*
**************************************************************************************************************************************/
function panier($annee,$etat,$prov="",$and="",$region)
{
  global $DB_site2;

  if ($region == "uk")
  {
    $sql = "SELECT SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) as 'prixHT', f.prixport / (1 + f.tvaport / 100) as 'portHT', CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) + (f.prixport / (1 + f.tvaport / 100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END as remiseHT";
    $sql .= " FROM facture f";
    $sql .= " INNER JOIN lignefacture lf ON (lf.factureid = f.facturid)";
    $sql .= " INNER JOIN utilisateur ON (f.userid = utilisateur.userid)";
    $sql .= " WHERE f.valide IN (".$etat.")";
    $sql .= " AND cast(f.dateexpedition as date) BETWEEN '".$annee."-01-01' AND '".$annee."-12-31'";
    $sql .= " AND f.provenance = '".$prov."'".$and;
    $sql .= " AND f.site_code='uk' AND utilisateur.langue='EN'";
    $sql .= " GROUP BY MONTH(dateexpedition)";
    $cams = $DB_site2->query($sql);

    while($cam = $DB_site2->fetch_array($cams)){
      $chiffremois[] += $cam['prixHT'] + $cam['portHT'] - $cam['remiseHT'];
    }


        $sql = "";
        $cams = "";
        $sql = "SELECT MONTH(dateexpedition) MONTH, COUNT(*) COUNT FROM facture INNER JOIN utilisateur ON (facture.userid = utilisateur.userid) WHERE YEAR(dateexpedition)='".$annee."' AND facture.site_code='uk' AND utilisateur.langue='EN' AND valide IN (5,8) GROUP BY MONTH(dateexpedition)";
        $cams = $DB_site2->query($sql);

             while($row = $DB_site2->fetch_array($cams)){
              $commande[] = $row['COUNT'];
            }
  }
  else
  {
    $sql = "SELECT SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) as 'prixHT', f.prixport / (1 + f.tvaport / 100) as 'portHT', CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) + (f.prixport / (1 + f.tvaport / 100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END as remiseHT";
    $sql .= " FROM facture f";
    $sql .= " INNER JOIN lignefacture lf ON (lf.factureid = f.facturid)";
    $sql .= " INNER JOIN utilisateur ON (f.userid = utilisateur.userid)";
    $sql .= " WHERE f.valide IN (".$etat.")";
    $sql .= " AND cast(f.dateexpedition as date) BETWEEN '".$annee."-01-01' AND '".$annee."-12-31'";
    $sql .= " AND f.provenance = '".$prov."'".$and;
    $sql .= " AND f.site_code IN('base', '') AND utilisateur.langue='".$region."'";
    $sql .= " GROUP BY MONTH(dateexpedition)";
    $cams = $DB_site2->query($sql);

    while($cam = $DB_site2->fetch_array($cams)){
      $chiffremois[] += $cam['prixHT'] + $cam['portHT'] - $cam['remiseHT'];
    }


        $sql = "";
        $cams = "";
        $sql = "SELECT MONTH(dateexpedition) MONTH, COUNT(*) COUNT FROM facture INNER JOIN utilisateur ON (facture.userid = utilisateur.userid) WHERE YEAR(dateexpedition)='".$annee."' AND facture.site_code IN('base', '') AND utilisateur.langue='".$region."' AND valide IN (5,8) GROUP BY MONTH(dateexpedition)";
        $cams = $DB_site2->query($sql);

             while($row = $DB_site2->fetch_array($cams)){
              $commande[] = $row['COUNT'];
            }
  }

if ($commande[0] > 0)
$chiffremois[0] = $chiffremois[0] / $commande[0];
else
$chiffremois[0] = 0;
if ($commande[1] > 0)
$chiffremois[1] = $chiffremois[1] / $commande[1];
else
$chiffremois[1] = 0;
if ($commande[2] > 0)
$chiffremois[2] = $chiffremois[2] / $commande[2];
else
$chiffremois[2] = 0;
if ($commande[3] > 0)
$chiffremois[3] = $chiffremois[3] / $commande[3];
else
$chiffremois[3] = 0;
if ($commande[4] > 0)
$chiffremois[4] = $chiffremois[4] / $commande[4];
else
$chiffremois[4] = 0;
if ($commande[5] > 0)
$chiffremois[5] = $chiffremois[5] / $commande[5];
else
$chiffremois[5] = 0;
if ($commande[6] > 0)
$chiffremois[6] = $chiffremois[6] / $commande[6];
else
$chiffremois[6] = 0;
if ($commande[7] > 0)
$chiffremois[7] = $chiffremois[7] / $commande[7];
else
$chiffremois[7] = 0;
if ($commande[8] > 0)
$chiffremois[8] = $chiffremois[8] / $commande[8];
else
$chiffremois[8] = 0;
if ($commande[9] > 0)
$chiffremois[9] = $chiffremois[9] / $commande[9];
else
$chiffremois[9] = 0;
if ($commande[10] > 0)
$chiffremois[10] = $chiffremois[10] / $commande[10];
else
$chiffremois[10] = 0;
if ($commande[11] > 0)
$chiffremois[11] = $chiffremois[11] / $commande[11];
else
$chiffremois[11] = 0;

    return $chiffremois;
}



/**********************************************************************************************************
Fonction servant à la mise en page dans les tableaux -> texte en rouge ou vert si positif ou negatif etc..*
***********************************************************************************************************/
function tdvalue($value,$sym="",$sign=null,$climit=0){
	if($value=="") return "<td></td>";
	return "<td class=\"ca_nb\" style=\"color:".($value>$climit?"green":"red").";\">".($sign&&$value>0?"+":"").number_format($value,2,',',' ').$sym."</td>";
}


function tdvalue_prc($value,$sym="",$sign=null,$climit=0){
  if($value=="") return "<td></td>";
  return "<td class=\"ca_prc\" style=\"color:".($value>$climit?"green":"red").";\">".($sign&&$value>0?"+":"").number_format($value,2,',',' ').$sym."</td>";
}

/*
                   .;;,.
                  ; '" ;\ \//
                 \|a (a|7 \//
                 j| ..  | ||/
                //'.--.')\-,/
              .-||- '' ||/  `-.
             ;  | \ |/ |/ L.  ,|
             f\ |\| Y  || \ '._\
            j | \|     (| |   | |
           |  L_\         L.__: |
            \(  '-.,-,    |   ; |
             |'-.'.L_rr>  f--f  |
.-=,,______,--------- J-. ;  ;__ 
   ``"-,__   |  |      h  |  f  '--.__
       `--;;--,_       h  f-j   |   __;==-.
            / `-''-,,__J,'  \_..--:'-'     '
            | |    `' --L7//'-'`|
            | ,     ||  h    |  (
            | ;     | \ J    j   |
            | L__   | |  L_.'    |
            |   |'-.| L.'h  |  : |
            |;  \     |  J ; : : |
            | :  (    \  'L| : : |
            | ;   \'.--|    \  : |
            | | : \    \-, /`\ : |
            L-'-;__\   \\ '  | | |
                    ;   \\   |'L_j
                    _>  _|   |
             snd   <___/ /-  \
                        /    /
                        '---'
*/
?>