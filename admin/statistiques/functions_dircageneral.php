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
        $sql = "SELECT obj_mois, obj_montant FROM `objectifs_factures` WHERE obj_annee='".$annee."' AND site_id='".$site_id."' GROUP BY obj_mois";
        $cams = $DB_site2->query($sql);

             while($row = $DB_site2->fetch_array($cams)){
              $chiffre[] = $row['obj_montant'];
            }
        return $chiffre;
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




/******************************************************
Calcul le nombre de commandes sur une année antérieure*
*******************************************************/
function c_commande($annee)
{
  global $DB_site2;

        $sql = "";
        $cams = "";
        $sql = "SELECT MONTH(dateexpedition) MONTH, COUNT(*) COUNT FROM facture WHERE YEAR(dateexpedition)='".$annee."' AND valide IN (5,8) GROUP BY MONTH(dateexpedition)";
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


    $sql = "SELECT MONTH(dateexpedition) MONTH, COUNT(*) COUNT";
    $sql .= " FROM facture";
    $sql .= " WHERE cast(dateexpedition as date) BETWEEN '".$annee."-01-01' AND '".$annee."-".$m."-".$d."'";
    $sql .= " AND valide IN (5,8)";
    $sql .= " GROUP BY MONTH(dateexpedition)";
    $cams = $DB_site2->query($sql);

    while($cam = $DB_site2->fetch_array($cams)){
      $chiffremois[] = $cam['COUNT'];
    }
      return $chiffremois;
}









function ca($mois,$annee,$etat,$prov="",$limit=null,$and=""){

    global $DB_site2;
  
    $chiffremois = 0;
    
    if($mois<10) $mois = "0".$mois;
    if(is_array($etat)) $etat = implode(",",$etat);
    
    //Remise HT = TOT HT hors remise x (Remise TTC / TOT TTC hors remise)
    $sql = "SELECT SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) as 'prixHT', f.prixport / (1 + f.tvaport / 100) as 'portHT', CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) + (f.prixport / (1 + f.tvaport / 100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END as remiseHT";
    $sql .= " FROM facture f";
    $sql .= " INNER JOIN lignefacture lf ON (lf.factureid = f.facturid)";
    $sql .= " WHERE f.valide IN (".$etat.")";
    $sql .= " AND cast(f.dateexpedition as date) BETWEEN '".$annee."-".$mois."-01' AND '".$annee."-".$mois."-31'";
    $sql .= " AND f.provenance = '".$prov."'".$and;
    if(!is_null($limit)) $sql.=" AND f.dateexpedition <= '".$annee."-".$mois."-".$limit."'";
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

// 	$report=report($annee,$prov);
	return $chiffremois;
}

function ca_attente($mois,$annee,$prov=""){
	global $DB_site2;
	global $listfacture;

	if($mois<10) $mois="0".$mois;
	$chiffremois=array();
	if(is_array($etat)) $etat=implode(",",$etat);
	/*$sql="select factureid, SUM(prix*qte)/(1+tva/100) montant, prixport/(1+tvaport/100) prixport, montantremise, montantcadeau from lignefacture lf inner join facture f 
                        on (f.facturid = lf.factureid) where remboursement = '0' and moyenid in (2,4,5) and supprimee='0' and valide=0 and datefacture LIKE '".$annee."-".$mois."-%' AND provenance<>'mp' group by factureid";*/
	$sql="select * from facture WHERE valide=0 and moyenid in (2,4,5) and heurefacture<".time()." AND provenance='".$prov."'";

	$cams=$DB_site2->query($sql);
	while ($cam=$DB_site2->fetch_array($cams)) {
	      $f=new facture($DB_site2,$cam);
	      if(date("Y",$f->datelivraison)==$annee) {
		  $chiffremois[date("n",$f->datelivraison)] += $f->totalht ;
		  $listfacture["attente"][$prov][date("n",$f->datelivraison)][]=$f;
	      }
	}
// 	$report=report($annee,$prov);
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






function tdvalue($value,$sym="",$sign=null,$climit=0){
	if($value=="") return "<td></td>";
	return "<td class=\"ca_nb\" style=\"color:".($value>$climit?"green":"red").";\">".($sign&&$value>0?"+":"").number_format($value,2,',',' ').$sym."</td>";
}

function tdvalue_nocoma($value,$sym="",$sign=null,$climit=0){
  if($value=="") return "<td></td>";
  return "<td class=\"ca_nb\" style=\"color:".($value>$climit?"green":"red").";\">".($sign&&$value>0?"+":"").number_format($value,0,',',' ').$sym."</td>";
}


/*
  |||            _.'   _      _.-. |        | |--
 \|||         _.'    -    _.-'  _|-|       -| |__
  ||;-,    _.'   '-  _.-'' _.-''|  |-'      | `._
 -'| / \_,' _    _.-'   _.' |   |  |    -|  |
 ----|,`   |  _.'   _.-' | ,| ,'| _| |      |_   
    _:  _   ,'   .-'    _|/ \ | | -|  _|_   | '
   | |    ,'  .-'     , )|)-( |_|  |-       |    -
 -   |  ,'  ,'(   `  /_\||`.'   |- |   -|_  |
 ___-| /  ,'   )     `.'||.-| _ | ||        | '-'
 __( |;  /    / ,-    | ||  |/ \|_ | _|    -|    
   | :  ; ,-.-)       | ||  || ||  |   |_   |    _
  _| | :/` _..\  `-.  | ||__||_|;--;--------:  ,'`
   | | |,-'  _/       |,-/\_|  /__/__________\::::
   |-| |   ,' \, ` ___|||||-|  |  |  _|_     ||___
 _ | | | ,'   (   ;   :'''' /| ||_|       _  ||---
 - | | |/     ;  /     :   : | |  |   _|_    ||---
   | | |      |,'______|-..| | |_||      |  _||---
   | | |      ||_      |   | | |_ |  -      -|----   
 _|| | |      ;|-:  _  |   | |,|- |-   _|  _ |--,'
   | | |______\| |,' `.|`-.| |:|  | _|    |  |,','
   |-| | ~   ~|| ||__|||! !| | ;--;----__---,','|
   | | |,._,~_|:.||-'|||! !| |/__/____/\_\,','|\|
 -.| | ;     _.-'|| - ||`.!| ||  |    ||_|,'| | |,
  || ;'|_,-''    -    - `.`| ||  | ___|| ||\| |,',
 , | | |    -     -     -  ) '|__||\  || | \|,','
   ; | | -     -      -      |\    \\ || |_,',' 
  /| | ;    -     -           \\    \\|| |','
 / | |/                        \\    \|| |' 
*/
?>