<?php



/*
FFFFFFF  OOOOO  NN   NN  CCCCC  TTTTTTT IIIII  OOOOO  NN   NN  SSSSS  
FF      OO   OO NNN  NN CC    C   TTT    III  OO   OO NNN  NN SS      
FFFF    OO   OO NN N NN CC        TTT    III  OO   OO NN N NN  SSSSS  
FF      OO   OO NN  NNN CC    C   TTT    III  OO   OO NN  NNN      SS 
FF       OOOO0  NN   NN  CCCCC    TTT   IIIII  OOOO0  NN   NN  SSSSS                                                                   
*/


function get_fac($type, $year)
{
  global $DB_site2;

    $sql = "";
    $cams = "";

    if($year == date(Y))
    {
      $sql = "SELECT fc.societe, IFNULL(SUM(ff.montant_ht),0) FROM fournisseur_condition fc LEFT JOIN facture_fournisseur ff ON (ff.fournisseur = fc.societe AND FROM_UNIXTIME(ff.date_facture) BETWEEN '".$year."-01-01' AND NOW()) WHERE fc.societe = '".$type."' GROUP BY MONTH(FROM_UNIXTIME(ff.date_facture))";
    }
    elseif($year < date(Y))
    {
      $sql = "SELECT fc.societe, IFNULL(SUM(ff.montant_ht),0) FROM fournisseur_condition fc LEFT JOIN facture_fournisseur ff ON (ff.fournisseur = fc.societe AND FROM_UNIXTIME(ff.date_facture) BETWEEN '".$year."-01-01' AND '".$year."-12-31') WHERE fc.societe = '".$type."' GROUP BY MONTH(FROM_UNIXTIME(ff.date_facture))";
    }
  
    $cams = $DB_site2->query($sql);

      while ($row = $DB_site2->fetch_array($cams))
      {
        $fac[] = $row['IFNULL(SUM(ff.montant_ht),0)'];
      }

    return $fac;
}

function get_fac_tr($type, $year)
{
    global $DB_site2;


    $day = date(d);
    $month = date(m);

    $sql = "";
    $cams = "";
    $sql = "SELECT fc.societe, IFNULL(SUM(ff.montant_ht),0) FROM fournisseur_condition fc LEFT JOIN facture_fournisseur ff ON (ff.fournisseur = fc.societe AND FROM_UNIXTIME(ff.date_facture) BETWEEN '".$year."-01-01' AND '".$year."-".$month."-".$day."') WHERE fc.societe = '".$type."' GROUP BY MONTH(FROM_UNIXTIME(ff.date_facture))";
  
    $cams = $DB_site2->query($sql);

      while ($row = $DB_site2->fetch_array($cams))
      {
        $fac[] = $row['IFNULL(SUM(ff.montant_ht),0)'];
      }

    return $fac;

}


function get_nbrfac($type, $year)
{
  global $DB_site2;

  $sql = "";
  $cams = "";

  if ($year == date(Y))
  {
    $sql = "SELECT COUNT(ff.montant_ht) AS factures FROM fournisseur_condition fc LEFT JOIN facture_fournisseur ff ON (ff.fournisseur = fc.societe AND FROM_UNIXTIME(ff.date_facture) BETWEEN '".$year."-01-01' AND NOW()) WHERE fc.societe = '".$type."' GROUP BY MONTH(FROM_UNIXTIME(ff.date_facture))";
  }
  elseif($year < date(Y))
  {
    $sql = "SELECT COUNT(ff.montant_ht) AS factures FROM fournisseur_condition fc LEFT JOIN facture_fournisseur ff ON (ff.fournisseur = fc.societe AND FROM_UNIXTIME(ff.date_facture) BETWEEN '".$year."-01-01' AND '".$year."-12-31') WHERE fc.societe = '".$type."' GROUP BY MONTH(FROM_UNIXTIME(ff.date_facture))";
  }
  
    $cams = $DB_site2->query($sql);
    while ($row = $DB_site2->fetch_array($cams))
    {
      $fac[] = $row['factures'];
    }

    return $fac;
}


function get_nbrfac_tr($type, $year)
{
  global $DB_site2;  

    $day = date(d);
    $month = date(m);

    $sql = "";
    $cams = "";

    $sql = "SELECT COUNT(ff.montant_ht) AS factures FROM fournisseur_condition fc LEFT JOIN facture_fournisseur ff ON (ff.fournisseur = fc.societe AND FROM_UNIXTIME(ff.date_facture) BETWEEN '".$year."-01-01' AND '".$year."-".$month."-".$day."') WHERE fc.societe = '".$type."' GROUP BY MONTH(FROM_UNIXTIME(ff.date_facture))";

    $cams = $DB_site2->query($sql);

      while ($row = $DB_site2->fetch_array($cams))
      {
        $fac[] = $row['factures'];
      }

    return $fac;
}





function sum_twelve_ht($tab, $cur_year, $year, $type)
{

 $i = 0;
 $count = 0;

  if ($cur_year == date(Y))
  {

    global $DB_site2;  

    $day = date(d);
    $month = date(m);
    $sql = "";
    $cams = "";

    $sql = "SELECT fc.societe, IFNULL(SUM(ff.montant_ht),0) FROM fournisseur_condition fc LEFT JOIN facture_fournisseur ff ON (ff.fournisseur = fc.societe AND FROM_UNIXTIME(ff.date_facture) BETWEEN '".$year."-01-01' AND '".$year."-".$month."-".$day."') WHERE fc.societe = '".$type."' GROUP BY MONTH(FROM_UNIXTIME(ff.date_facture))";

    $cams = $DB_site2->query($sql);

      while ($row = $DB_site2->fetch_array($cams))
      {
        $fac[] = $row['IFNULL(SUM(ff.montant_ht),0)'];
      }

    while ($i < $month)
    {
        $count += $fac[$i];
        $i++;    
    }
  }
  elseif ($cur_year < date(Y))
  {
      while ($i <= 12)
      {
        $count += $tab[$i];
        $i++;
      }
  }

    return $count;
}


function sum_twelve_basic($tab, $year)
{
  $i = 0;
  $count = 0;

  while ($i <= 12)
  {
    $count += $tab[$i];
    $i++;
  }

return $count;

}


function sum_twelve_fac($tab, $cur_year, $year, $type)
{

 $i = 0;
 $count = 0;

  if ($cur_year == date(Y))
  {

    global $DB_site2;  

    $day = date(d);
    $month = date(m);
    $sql = "";
    $cams = "";

    $sql = "SELECT COUNT(ff.montant_ht) AS factures FROM fournisseur_condition fc LEFT JOIN facture_fournisseur ff ON (ff.fournisseur = fc.societe AND FROM_UNIXTIME(ff.date_facture) BETWEEN '".$year."-01-01' AND '".$year."-".$month."-".$day."') WHERE fc.societe = '".$type."' GROUP BY MONTH(FROM_UNIXTIME(ff.date_facture))";

    $cams = $DB_site2->query($sql);

      while ($row = $DB_site2->fetch_array($cams))
      {
        $fac[] = $row['factures'];
      }

    while ($i < $month)
    {
        $count += $fac[$i];
        $i++;    
    }
  }
  elseif ($cur_year < date(Y))
  {
      while ($i <= 12)
      {
        $count += $tab[$i];
        $i++;
      }
  }

    return $count;
}












function tdvalue($value,$sym="",$sign=null,$climit=0){
	if($value=="") return "<td></td>";
	return "<td class=\"ca_nb\" style=\"color:".($value>$climit?"green":"red").";\">".($sign&&$value>0?"+":"").number_format($value,2,',',' ').$sym."</td>";
}

function tdvalue_nocoma($value,$sym="",$sign=null,$climit=0){
  if($value=="") return "<td></td>";
  return "<td style=\"text-align:center; color:".($value>$climit?"green":"red").";\">".($sign&&$value>0?"+":"").number_format($value,0,',',' ').$sym."</td>";
}

function tdvalue_nocolor($value,$sym=""){
  if($value==0) return "<td></td>";
  return "<td class=\"ca_nb\">".number_format($value,2,',',' ').$sym."</td>";
}

function tdvalue_nocolornocoma($value,$sym=""){
  if($value==0) return "<td></td>";
  return "<td style=\"text-align:center;\">".number_format($value,0,',',' ').$sym."</td>";
}

?>