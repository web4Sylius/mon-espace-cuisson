<?php



/*
FFFFFFF  OOOOO  NN   NN  CCCCC  TTTTTTT IIIII  OOOOO  NN   NN  SSSSS  
FF      OO   OO NNN  NN CC    C   TTT    III  OO   OO NNN  NN SS      
FFFF    OO   OO NN N NN CC        TTT    III  OO   OO NN N NN  SSSSS  
FF      OO   OO NN  NNN CC    C   TTT    III  OO   OO NN  NNN      SS 
FF       OOOO0  NN   NN  CCCCC    TTT   IIIII  OOOO0  NN   NN  SSSSS                                                                   
*/



function get_supply($type)
{
  global $DB_site2;

        $sql = "";
        $cams = "";
        $sql = "SELECT societe FROM fournisseur_condition WHERE types='".$type."' ORDER BY societe ASC";
        $cams = $DB_site2->query($sql);

             while($row = $DB_site2->fetch_array($cams)){
              $supply[] = $row['societe'];
            }
   return $supply;
}

function get_fac($type, $year, $method)
{
  global $DB_site2;

    $sql = "";
    $cams = "";
    if($year == date(Y))
    {
      $sql = "SELECT fc.societe, IFNULL(SUM(ff.montant_ht),0) FROM fournisseur_condition fc LEFT JOIN facture_fournisseur ff ON (ff.fournisseur = fc.societe AND FROM_UNIXTIME(ff.date_facture) BETWEEN '".$year."-01-01' AND NOW()) WHERE fc.types = '".$type."' GROUP BY fc.societe ORDER BY ".$method."";
    }
    elseif($year < date(Y))
    {
      $sql = "SELECT fc.societe, IFNULL(SUM(ff.montant_ht),0) FROM fournisseur_condition fc LEFT JOIN facture_fournisseur ff ON (ff.fournisseur = fc.societe AND FROM_UNIXTIME(ff.date_facture) BETWEEN '".$year."-01-01' AND '".$year."-12-31') WHERE fc.types = '".$type."' GROUP BY fc.societe ORDER BY ".$method."";      
    }
  
    $cams = $DB_site2->query($sql);
    $i = 0;

      while ($row = $DB_site2->fetch_array($cams))
      {
        $fac[$i] = $row['societe'];
        $i++;
        $fac[$i] = $row['IFNULL(SUM(ff.montant_ht),0)'];
        $i++;
      }

    return $fac;
}

function get_fac_tr($type, $year, $method)
{
    global $DB_site2;


    $day = date(d);
    $month = date(m);

    $sql = "";
    $cams = "";
    $sql = "SELECT fc.societe, IFNULL(SUM(ff.montant_ht),0) FROM fournisseur_condition fc LEFT JOIN facture_fournisseur ff ON (ff.fournisseur = fc.societe AND FROM_UNIXTIME(ff.date_facture) BETWEEN '".$year."-01-01' AND '".$year."-".$month."-".$day."') WHERE fc.types = '".$type."' GROUP BY fc.societe ORDER BY ".$method."";
  
    $i = 0;
    $cams = $DB_site2->query($sql);

      while ($row = $DB_site2->fetch_array($cams))
      {
        $fac[$i] = $row['societe'];
        $i++;
        $fac[$i] = $row['IFNULL(SUM(ff.montant_ht),0)'];
        $i++;
      }

    return $fac;

}


function tdvalue($value,$sym="",$sign=null,$climit=0){
	if($value=="") return "<td></td>";
	return "<td class=\"ca_nb\" style=\"color:".($value>$climit?"green":"red").";\">".($sign&&$value>0?"+":"").number_format($value,2,',',' ').$sym."</td>";
}

function tdvalue_prc($value,$sym="",$sign=null,$climit=0){
  if($value=="") return "<td></td>";
  return "<td class=\"ca_prc\" style=\"color:".($value>$climit?"green":"red").";\">".($sign&&$value>0?"+":"").number_format($value,2,',',' ').$sym."</td>";
}

function tdvalue_nocoma($value,$sym="",$sign=null,$climit=0){
  if($value=="") return "<td></td>";
  return "<td class=\"ca_nb\" style=\"color:".($value>$climit?"green":"red").";\">".($sign&&$value>0?"+":"").number_format($value,0,',',' ').$sym."</td>";
}

function tdvalue_nocolor($value,$sym=""){
  if($value==0) return "<td></td>";
  return "<td style=\"text-align:right;\">".number_format($value,2,',',' ').$sym."</td>";
}


?>