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








/***********************************************
Cacul le nombre d'inscrits sur l'année EN COURS*
************************************************/
function c_inscrit_cur($annee)
{
  global $DB_site2;

$j = date('j');
$d = date('d');
$t = date('t');
$m = date('m');


    $sql = "SELECT MONTH(dateinscription) MONTH, COUNT(*) COUNT";
    $sql .= " FROM utilisateur";
    $sql .= " WHERE cast(dateinscription as date) BETWEEN '".$annee."-01-01' AND '".$annee."-".$m."-".$d."'";
    $sql .= " AND infos NOT LIKE '%Contact mailing%'";
    $sql .= " GROUP BY MONTH(dateinscription)";
    $cams = $DB_site2->query($sql);

    while($cam = $DB_site2->fetch_array($cams)){
      $chiffremois[] = $cam['COUNT'];
    }
      return $chiffremois;
}






/***************************************************
Cacul le nombre d'inscrits sur une année antérieure*
****************************************************/
function c_inscrit($annee)
{
  global $DB_site2;

    $sql = "SELECT MONTH(dateinscription) MONTH, COUNT(*) COUNT";
    $sql .= " FROM utilisateur";
    $sql .= " WHERE YEAR(dateinscription)='".$annee."'";
    $sql .= " AND infos NOT LIKE '%Contact mailing%'";
    $sql .= " GROUP BY MONTH(dateinscription)";
    $cams = $DB_site2->query($sql);

    while($cam = $DB_site2->fetch_array($cams)){
      $chiffremois[] = $cam['COUNT'];
    }
      return $chiffremois;
}



/******************************************************
Calcul le nombre de commandes sur une année antérieure*
*******************************************************/
function c_commande($annee)
{
  global $DB_site2;

        $sql = "";
        $cams = "";
        $sql = "SELECT MONTH(datefacture) MONTH, COUNT(*) COUNT FROM facture WHERE YEAR(datefacture)='".$annee."' AND valide IN (1,5,7,8) GROUP BY MONTH(datefacture)";
        $cams = $DB_site2->query($sql);

             while($cam = $DB_site2->fetch_array($cams)){
              $chiffremois[] = $cam['COUNT'];
            }
       return $chiffremois; 
    }





/**************************************************
Calcul le nombre de commandes sur l'année EN COURS*
***************************************************/
function c_commande_cur($annee)
{
  global $DB_site2;

$j = date('j');
$d = date('d');
$t = date('t');
$m = date('m');


    $sql = "SELECT MONTH(datefacture) MONTH, COUNT(*) COUNT";
    $sql .= " FROM facture";
    $sql .= " WHERE cast(datefacture as date) BETWEEN '".$annee."-01-01' AND '".$annee."-".$m."-".$d."'";
    $sql .= " AND valide IN (1,5,7,8)";
    $sql .= " GROUP BY MONTH(datefacture)";
    $cams = $DB_site2->query($sql);

    while($cam = $DB_site2->fetch_array($cams)){
      $chiffremois[] = $cam['COUNT'];
    }
      return $chiffremois;
}





/*******************************************
Calcul le panier moyen sur une année donnée*
*******************************************/




function panier_old($mois,$annee,$etat,$prov="",$limit=null,$and=""){

    global $DB_site2;
  
    $chiffremois = 0;
    
    if($mois<10) $mois = "0".$mois;
    if(is_array($etat)) $etat = implode(",",$etat);
    
    //Remise HT = TOT HT hors remise x (Remise TTC / TOT TTC hors remise)
    $sql = "SELECT SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) as 'prixHT', MONTH(datefacture), f.prixport / (1 + f.tvaport / 100) as 'portHT', CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) + (f.prixport / (1 + f.tvaport / 100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END as remiseHT FROM facture f INNER JOIN lignefacture lf ON (lf.factureid = f.facturid) INNER JOIN utilisateur ON (f.userid = utilisateur.userid) WHERE f.valide IN (1,5,7,8) AND cast(f.datefacture as date) BETWEEN '".$annee."-".$mois."-01' AND '".$annee."-".$mois."-31' AND f.provenance='' GROUP BY factureid";
    $cams = $DB_site2->query($sql);

    while($cam = $DB_site2->fetch_array($cams)){
      $chiffremois += $cam['prixHT'] + $cam['portHT'] - $cam['remiseHT'];
    }


    $sql = "";
    $cams = "";
    $sql = "SELECT MONTH(datefacture) MONTH, COUNT(*) COUNT FROM facture WHERE cast(datefacture as date) BETWEEN '".$annee."-".$mois."-01' AND '".$annee."-".$mois."-31' AND valide IN (5,8)";
    $cams = $DB_site2->query($sql);

        while($row = $DB_site2->fetch_array($cams)){
            $commande = $row['COUNT'];
        }


if ($commande > 0)
$chiffremois = $chiffremois / $commande;
else
$chiffremois = 0;

    return $chiffremois;
}








function c_panier($annee)
{
  global $DB_site2;

    $sql = "";
    $cams = "";
    $sql = "SELECT SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) as 'prixHT', SUM(f.prixport / (1 + f.tvaport / 100)) as 'portHT', CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) + (f.prixport / (1 + f.tvaport / 100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END as remiseHT";
    $sql .= " FROM facture f";
    $sql .= " INNER JOIN lignefacture lf ON (lf.factureid = f.facturid)";
    $sql .= " WHERE f.valide IN (1,5,7,8)";
    $sql .= " AND YEAR(f.datefacture)='".$annee."'";
    $sql .= " GROUP BY MONTH(datefacture)";
    $cams = $DB_site2->query($sql);

    while($cam = $DB_site2->fetch_array($cams)){
      $chiffremois[] = $cam['prixHT'] + $cam['portHT'] - $cam['remiseHT'];
    }

    $sql = "";
    $cams = "";
    $sql = "SELECT MONTH(datefacture) MONTH, COUNT(*) COUNT FROM facture WHERE YEAR(datefacture)='".$annee."' AND valide IN (1,5,7,8)";
    $sql .= " GROUP BY MONTH(datefacture)";
    $cams = $DB_site2->query($sql);

        while($row = $DB_site2->fetch_array($cams)){
            $commande[] = $row['COUNT'];
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

/*
                       \ : /
                    '-: __ :-'
                    -:  )(_ :--
                    -' |r-_i'-
            ,sSSSSs,   (2-,7
            sS';:'`Ss   )-j
           ;K e (e s7  /  (
            S, ''  SJ (  ;/
            sL_~~_;(S_)  _7
|,          'J)_.-' />'-' `Z
j J         /-;-A'-'|'--'-j\
 L L        )  |/   :    /  \
  \ \       | | |    '._.'|  L
   \ \      | | |       | \  J
    \ \    _/ | |       |  ',|
     \ L.,' | | |       |   |/
    _;-r-<_.| \=\    __.;  _/
      {_}"  L-'  '--'   / /|
            |   ,      |  \|
            |   |      |   ")
            L   ;|     |   /|
           /|    ;     |  / |
          | |    ;     |  ) |
         |  |    ;|    | /  |
         | ;|    ||    | |  |
         L-'|____||    )/   |
             % %/ '-,- /    /
     snd     |% |   \%/_    |
          ___%  (   )% |'-; |
        C;.---..'   >%,(   "'
                   /%% /
                  Cccc'
*/
?>