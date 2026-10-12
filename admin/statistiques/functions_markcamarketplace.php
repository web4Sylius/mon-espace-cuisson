<?php


/*
FFFFFFF  OOOOO  NN   NN  CCCCC  TTTTTTT IIIII  OOOOO  NN   NN  SSSSS  
FF      OO   OO NNN  NN CC    C   TTT    III  OO   OO NNN  NN SS      
FFFF    OO   OO NN N NN CC        TTT    III  OO   OO NN N NN  SSSSS  
FF      OO   OO NN  NNN CC    C   TTT    III  OO   OO NN  NNN      SS 
FF       OOOO0  NN   NN  CCCCC    TTT   IIIII  OOOO0  NN   NN  SSSSS                                                                   
*/





/********************************************************************************************
 Récupère les montants des objectifs pour chaque mois - obj = objectif / montant = montant !*
********************************************************************************************/
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



/*****************************************************************************************************
Calcul le chiffre d'affaire pour toutes les marketplaces réunies - tte = toute / ca = chiffre affaire*
******************************************************************************************************/
function tte_ca_marketplaces($annee, $etat)
{
      global $DB_site2;
  
    
    $sql = "SELECT SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) as 'prixHT', f.prixport / (1 + f.tvaport / 100) as 'portHT', CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) + (f.prixport / (1 + f.tvaport / 100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END as remiseHT";
    $sql .= " FROM facture f";
    $sql .= " INNER JOIN lignefacture lf ON (lf.factureid = f.facturid)";
    $sql .= " WHERE f.valide IN (".$etat.")";
    $sql .= " AND YEAR(datefacture)='".$annee."'";
    $sql .= " AND f.provenance = 'mp'";
    $sql .= " GROUP BY MONTH(datefacture)";
    $cams = $DB_site2->query($sql);

    while($cam = $DB_site2->fetch_array($cams)){
      $chiffremois[] += $cam['prixHT'] + $cam['portHT'] - $cam['remiseHT'];
    }

    return $chiffremois;

}



/*********************************************************************************************
Calcul le chiffre d'affaire pour UNE marketplace en particulier, déterminée selon le $site_id*
**********************************************************************************************/
function ca_marketplaces_detail($mois, $annee, $etat, $prov="",$limit=null,$and="", $site_id)
{
/*      global $DB_site2;
  
    
    $sql = "SELECT SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) as 'prixHT', f.prixport / (1 + f.tvaport / 100) as 'portHT', CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) + (f.prixport / (1 + f.tvaport / 100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END as remiseHT";
    $sql .= " FROM facture f";
    $sql .= " INNER JOIN lignefacture lf ON (lf.factureid = f.facturid)";
    $sql .= " WHERE f.valide IN (".$etat.")";
    $sql .= " AND YEAR(datefacture)='".$annee."'";
    $sql .= " AND f.provenance = 'mp'";
    $sql .= " AND f.provenanceid = '".$site_id."'";
    $sql .= " GROUP BY MONTH(datefacture)";
    $cams = $DB_site2->query($sql);

    while($cam = $DB_site2->fetch_array($cams)){
      $chiffremois[] += $cam['prixHT'] + $cam['portHT'] - $cam['remiseHT'];
    }

    return $chiffremois;
*/
 global $DB_site2;
  
    $chiffremois = 0;
    
    if($mois<10) $mois = "0".$mois;
    if(is_array($etat)) $etat = implode(",",$etat);
    
    //Remise HT = TOT HT hors remise x (Remise TTC / TOT TTC hors remise)
    $sql = "SELECT SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) as 'prixHT', MONTH(datefacture), f.prixport / (1 + f.tvaport / 100) as 'portHT', CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) + (f.prixport / (1 + f.tvaport / 100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END as remiseHT FROM facture f INNER JOIN lignefacture lf ON (lf.factureid = f.facturid) INNER JOIN utilisateur ON (f.userid = utilisateur.userid) WHERE f.valide IN (1,5,7,8) AND cast(f.datefacture as date) BETWEEN '".$annee."-".$mois."-01' AND '".$annee."-".$mois."-31' AND f.provenance='mp'  AND f.provenanceid = '".$site_id."' GROUP BY factureid";
    $cams = $DB_site2->query($sql);

    while($cam = $DB_site2->fetch_array($cams)){
      $chiffremois += $cam['prixHT'] + $cam['portHT'] - $cam['remiseHT'];
    }

    return $chiffremois;
}



/*****************************************
Calcul l'avance/retard de l'objectif en %*
******************************************/
function total_av_ret_objprcent($obj, $total, $lim_mois, $t)
{
  $j = date("j");
  $i = 1;
  $a = 0;

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
  $retour = $retour - 100;

  return $retour;
}



/*****************
Calculs % / brut *
******************/
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



/********************************************************************************************************************************************
Utilisé pour les calculs de totaux (toutes les additions X 12 pour les mois sur l'année, c'est laid donc vaut mieux utiliser cette fonction)*
********************************************************************************************************************************************/
function add_month($tab)
{
  $i = 0;
  $total;

  while ($i < 12)
  {
    $total += $tab[$i];
    $i++;
  }
  return $total;
}


/***********************************************************************************************
Calcul des commandes pour l'année EN COURS pour tout les sites réunis OU un site en particulier*
***********************************************************************************************/
function c_commande_cur_mwcom($annee, $site_id)
{
  global $DB_site2;

$j = date('j');
$d = date('d');
$t = date('t');
$m = date('m');



  if ($site_id == "all")
    {

        $sql = "";
        $cams = "";
        $sql = "SELECT month(facturid) month, count(*) COUNT";
        $sql .= " FROM facture";
        $sql .= " WHERE cast(datefacture as date) BETWEEN '".$annee."-01-01' AND '".$annee."-".$m."-".$d."'";
        $sql .= " AND valide IN (1,5,7,8)";
        $sql .= " AND provenance = 'mp'";
        $sql .= " GROUP BY MONTH(datefacture)";
        $cams = $DB_site2->query($sql);

             while($row = $DB_site2->fetch_array($cams)){
              $commande[] = $row['COUNT'];
            }

    }
    else
    {

        $sql = "";
        $cams = "";
        $sql = "SELECT MONTH(facturid) MONTH, COUNT(*) COUNT";
        $sql .= " FROM facture";
        $sql .= " WHERE cast(datefacture as date) BETWEEN '".$annee."-01-01' AND '".$annee."-".$m."-".$d."'";
        $sql .= " AND valide IN (1,5,7,8)";
        $sql .= " AND provenance = 'mp'";
        $sql .= " AND provenanceid = '".$site_id."'";
        $sql .= " GROUP BY MONTH(datefacture)";
        $cams = $DB_site2->query($sql);

             while($row = $DB_site2->fetch_array($cams)){
              $commande[] = $row['COUNT'];
            }

    }

      return $commande;
}



/***************************************************************************
*Calcul des commandes pour TOUTES les marketplaces sur une année antérieure*
***************************************************************************/
function tte_marketplaces_commandes($annee, $etat)
{
      global $DB_site2;
  
    
        $sql = "";
        $cams = "";
        $sql = "SELECT MONTH(facturid) MONTH, COUNT(*) COUNT";
        $sql .= " FROM facture f";
        $sql .= " WHERE f.valide IN (".$etat.")";
        $sql .= " AND YEAR(datefacture)='".$annee."'";
        $sql .= " AND f.provenance = 'mp'";
        $sql .= " GROUP BY MONTH(datefacture)";
        $cams = $DB_site2->query($sql);

             while($row = $DB_site2->fetch_array($cams)){
              $commande[] = $row['COUNT'];
            }

    return $commande;
}




/*********************************************************************************
Calcul commandes pour UN site marketplace en particulier sur une année antérieure*
*********************************************************************************/
function marketplaces_detail_commandes($annee, $etat, $site_id)
{
      global $DB_site2;
  
    
    $sql = "SELECT MONTH(facturid) MONTH, COUNT(*) COUNT";
    $sql .= " FROM facture f";
    $sql .= " WHERE f.valide IN (".$etat.")";
    $sql .= " AND YEAR(datefacture)='".$annee."'";
    $sql .= " AND f.provenance = 'mp'";
    $sql .= " AND f.provenanceid = '".$site_id."'";
    $sql .= " GROUP BY MONTH(datefacture)";
    $cams = $DB_site2->query($sql);

    while($cam = $DB_site2->fetch_array($cams)){
      $chiffremois[] = $cam['COUNT'];
    }

    return $chiffremois;

}




/**********************************************************************************************************************************************
Calcul du panier moyen sur l'année en cours ou antérieure, pour un site ou tout site reunis, c'est une moyenne donc pas besoin d'un temps réel*
***********************************************************************************************************************************************/
function panier($annee, $etat, $site_id)
{
  global $DB_site2;

  if ($site_id == "all")
  {
    $sql = "SELECT SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) as 'prixHT', f.prixport / (1 + f.tvaport / 100) as 'portHT', CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) + (f.prixport / (1 + f.tvaport / 100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END as remiseHT";
    $sql .= " FROM facture f";
    $sql .= " INNER JOIN lignefacture lf ON (lf.factureid = f.facturid)";
    $sql .= " WHERE f.valide IN (".$etat.")";
    $sql .= " AND YEAR(datefacture)='".$annee."'";
    $sql .= " AND f.provenance = 'mp'";
    $sql .= " GROUP BY MONTH(datefacture)";
    $cams = $DB_site2->query($sql);

    while($cam = $DB_site2->fetch_array($cams)){
      $chiffremois[] += $cam['prixHT'] + $cam['portHT'] - $cam['remiseHT'];
    }


        $sql = "";
        $cams = "";
        $sql = "SELECT MONTH(datefacture) MONTH, COUNT(*) COUNT";
        $sql .= " FROM facture f";
        $sql .= " INNER JOIN lignefacture lf ON (lf.factureid = f.facturid)";
        $sql .= " WHERE f.valide IN (".$etat.")";
        $sql .= " AND YEAR(datefacture)='".$annee."'";
        $sql .= " AND f.provenance = 'mp'";
        $sql .= " GROUP BY MONTH(datefacture)";
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
    $sql .= " WHERE f.valide IN (".$etat.")";
    $sql .= " AND YEAR(datefacture)='".$annee."'";
    $sql .= " AND f.provenance = 'mp'";
    $sql .= " AND f.provenanceid = '".$site_id."'";
    $sql .= " GROUP BY MONTH(datefacture)";
    $cams = $DB_site2->query($sql);

    while($cam = $DB_site2->fetch_array($cams)){
      $chiffremois[] += $cam['prixHT'] + $cam['portHT'] - $cam['remiseHT'];
    }


        $sql = "";
        $cams = "";
        $sql = "SELECT MONTH(datefacture) MONTH, COUNT(*) COUNT";
        $sql .= " FROM facture f";
        $sql .= " INNER JOIN lignefacture lf ON (lf.factureid = f.facturid)";
        $sql .= " WHERE f.valide IN (".$etat.")";
        $sql .= " AND YEAR(datefacture)='".$annee."'";
        $sql .= " AND f.provenance = 'mp'";
        $sql .= " AND f.provenanceid = '".$site_id."'";
        $sql .= " GROUP BY MONTH(datefacture)";
        $cams = $DB_site2->query($sql);

             while($row = $DB_site2->fetch_array($cams)){
              $commande[] = $row['COUNT'];
            }
  }

/************************************************************************************************************************
C'est assez laid mais ça évite de diviser par zéro, sinon on peut juste set les erreur php sur 0 et ça reviendra au même*
*************************************************************************************************************************/
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



/*********************************************************************
Toutes les fonctions suivantes servent au calcul de chiffre d'affaire*
**********************************************************************/
function ca($mois,$annee,$etat,$prov="",$limit=null,$and=""){

    global $DB_site2;
  
    $chiffremois = 0;
    
    if($mois<10) $mois = "0".$mois;
    if(is_array($etat)) $etat = implode(",",$etat);
    
    $sql = "SELECT SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) as 'prixHT', f.prixport / (1 + f.tvaport / 100) as 'portHT', CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) + (f.prixport / (1 + f.tvaport / 100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END as remiseHT";
    $sql .= " FROM facture f";
    $sql .= " INNER JOIN lignefacture lf ON (lf.factureid = f.facturid)";
    $sql .= " WHERE f.valide IN (".$etat.")";
    $sql .= " AND cast(f.datefacture as date) BETWEEN '".$annee."-".$mois."-01' AND '".$annee."-".$mois."-31'";
    $sql .= " AND f.provenance = '".$prov."'".$and;
    if(!is_null($limit)) $sql.=" AND f.datefacture <= '".$annee."-".$mois."-".$limit."'";
    $sql .= " GROUP BY f.facturid";
    $cams = $DB_site2->query($sql);

    while($cam = $DB_site2->fetch_array($cams)){
      $chiffremois += $cam['prixHT'] + $cam['portHT'] - $cam['remiseHT'];
    }

    return $chiffremois;

}

function ca_valide($mois,$annee,$prov=""){
  global $DB_site2;
  global $listfacture;

  if($mois<10) $mois="0".$mois;
  $chiffremois=array();
  if(is_array($etat)) $etat=implode(",",$etat);
  $sql="select * from facture WHERE valide IN (1,7) and heurefacture<".time()." AND provenance='".$prov."'";

  $cams=$DB_site2->query($sql);
  while ($cam=$DB_site2->fetch_array($cams)) {
        $f=new facture($DB_site2,$cam);
        if(date("Y",$f->datelivraison)==$annee) {
      $chiffremois[date("n",$f->datelivraison)] += $f->totalht ;
      $listfacture["valide"][$prov][date("n",$f->datelivraison)][]=$f;
        }
  }

  return $chiffremois;
}

function ca_attente($mois,$annee,$prov=""){
  global $DB_site2;
  global $listfacture;

  if($mois<10) $mois="0".$mois;
  $chiffremois=array();
  if(is_array($etat)) $etat=implode(",",$etat);
  $sql="select * from facture WHERE valide=0 and moyenid in (2,4,5) and heurefacture<".time()." AND provenance='".$prov."'";

  $cams=$DB_site2->query($sql);
  while ($cam=$DB_site2->fetch_array($cams)) {
        $f=new facture($DB_site2,$cam);
        if(date("Y",$f->datelivraison)==$annee) {
      $chiffremois[date("n",$f->datelivraison)] += $f->totalht ;
      $listfacture["attente"][$prov][date("n",$f->datelivraison)][]=$f;
        }
  }
  return $chiffremois;
}

function report($annee,$prov=""){
  global $DB_site2;
  global $listfacture;

  $jan1=mktime(0,0,0,1,1,$annee);
  $report=array();

  $sql="select * from facture where ((valide=0 and moyenid in (2,4,5)) or valide in (1,7)) and heurefacture<".$jan1." and provenance='".$prov."'";

  $cams=$DB_site2->query($sql);
  while ($cam=$DB_site2->fetch_array($cams)) {
        $f=new facture($DB_site2,$cam);
        if($f->valide==7) $f->valide=1;
        $report[$f->valide] += $f->totalht ;
        $listfacture["report".$f->valide][$prov][]=$f;
  }
  return $report;
}

function formatlist($type,$prov=null,$mois=null){
  global $listfacture;

  $html="<table>";
  if($prov===null){
     if(!is_array($listfacture[$type][""][$mois])) $listfacture[$type][""][$mois]=array();
     if(!is_array($listfacture[$type]["mp"][$mois])) $listfacture[$type]["mp"][$mois]=array();
     if(!is_array($listfacture[$type]["sav"][$mois])) $listfacture[$type]["sav"][$mois]=array();
     $list=array_merge($listfacture[$type][""][$mois],$listfacture[$type]["mp"][$mois]);
  }else
     $list=$listfacture[$type][$prov][$mois];

  if(is_null($list)) return "";
  
  foreach($list as $f)
      $html.="<tr>
        <td><a href=\"facture.php?action=editer&facturid=".$f->facturid."\" target=\"_blank\">".$f->facturid."</a></td>
        <td>".$f->utilisateur->nom."</td>
        <td>".date("d/m/Y",$f->datecommande)."</td>
        <td align=\"right\">".number_format($f->totalht,2,',','')." &euro;</td>
      </tr>";

  $html.="</table>";
  return $html;
  
}


/**********************************************************************************************************
Fonction servant à la mise en page dans les tableaux -> texte en rouge ou vert si positif ou negatif etc..*
***********************************************************************************************************/
function tdvalue($value,$sym="",$sign=null,$climit=0){
  if($value=="") return "<td></td>";
  return "<td class=\"ca_nb\" style=\"color:".($value>$climit?"green":"red").";\">".($sign&&$value>0?"+":"").number_format($value,2,',',' ').$sym."</td>";
}




/*                                          ,▄▄▄                              
                                       ▄▓█▀▀▀▀▀█▄                           
               ▄▄▓█`       ,▄▄▓▓▄▄▄▄▄@██▀!√√√√√└▀█▄                         
            .▓█▀██       #█▀▀└:.!╙▀▀██▀:√√√√√√√√√!▀▀█▓▓▄▄                   
           ╓█▀..▀█▓▄▄▄▄▓▀▀:√√√√√√√√√√√√√√√√√√√√√√√√√░░▀▀██▄                 
           ██.√√√!▀▀▀▀▀:√√√√√√√√√√√√√√√√√√√√√√√√√√√√╠░░░░▀█▄                
           █▌√√√√√√√√√√√√√▄▄▄▄▄.√√√√√√√╓▄▄▄.√√√√√√√√╠░░░░░╙█▄               
           ██.√√√√√√√√√▄#█▀╙`╙▀█▓▄▄▄@▓██████▄.√√√√√╠░░░░░░░╙█▓▄             
         ┌████:√√√√√(▄█▀╙       └▀▀▀▀└   └▀▀██,√√╓╢░░░░░░░░░░▀██▄           
         ██:√╙▀▓▄▄▓▓▀▀                      └██▄░░░░░░░░░░░░░░░██▄          
         █▌√√╓██▀  ▄▄@╕                       ▀▀█▓▀▀▀▀▀▀███▄░░░░██▄         
         ██▄▓█▀  ╙▀▀▀▀▀                 ,▄               ▀███░░░░██▄        
          ███`                         ▓███,     .        ███░░░░║██        
         ▓█▀     ,▄                     └▀██▄            ▄██▀░░░░░██`       
        ██▀     ███¼        ,              ▀▀        ╓@██▀▀░░░░░░░██        
       ██▀     ▐███       ╓█▀        ▄▄,          .  ▄╙▀█░░░░░░░░╟██        
      ▐█▌       ▀▀└     .▓█└        #███          .  ╙█▓,▀█░░░░░░██▌        
      ██              ▄▓█▀          ███▌          . .▄,▀█▄╙█░░░░███         
     ╟█▌            #██▀            ╙▀▀           .  ▀█▓,█▄╙█░░███          
     ██─            ███                             ▓▄,▀█▄█,█░███`          
     ██             ╙███                         .   ▀█▄╙█Ö█████            
     ██    ,#         ╙╙                         . ╙█▄ ▀ ╙████▀             
     ██  ╒███▄▄                  ▐█▄            .   ╙▀  .@███┘              
     ██▌  ██▄ └╙▀▀#╦▄▄▄▄▄▄▄▄▄▄▄▄#████▄         .         ╙███               
     ▐██   ▀ ▀▓▄,     `└╙└└ .      ███▌        .          ╟██               
      ██▌      ╙▀█▓▄▄▄,   .,▄▄▄▓▓▀▀╙██        .          .███               
      └██▄        └▀▀▀███▀▀▀▀╙"     ▀       ..          ▄███                
       ╙██▄       Ñ▓▓▓▓µ                   ..    ▄▓▓▓▓███▀`                 
        └██▄        `└└                  ..    ▄███▀└└                      
          ▀██▄                          .   ▄▓██▀└                          
            ▀█▓▄                     ..  ▄▓██▀╙                             
              ╙▀█▄,                .╓▄▓██████                               
                 ╙██▓▄         ...   '' ▄██▀                                
                  ╙█████▓▓▄▄▄▄      .▄▄██▀'                                 
                    ▀█████▄▄▄▄▄▄▄▄▓████▀                                    
                       ╙▀▀▀██████▀▀▀╙           */
?>
