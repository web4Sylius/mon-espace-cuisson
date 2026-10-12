<?php






/*
 __  .__   __.      _______.  ______ .______       __  .___________.    _______.
|  | |  \ |  |     /       | /      ||   _  \     |  | |           |   /       |
|  | |   \|  |    |   (----`|  ,----'|  |_)  |    |  | `---|  |----`  |   (----`
|  | |  . `  |     \   \    |  |     |      /     |  |     |  |        \   \    
|  | |  |\   | .----)   |   |  `----.|  |\  \----.|  |     |  |    .----)   |   
|__| |__| \__| |_______/     \______|| _| `._____||__|     |__|    |_______/    
                                                                                
*/




//Calcul du % pour les inscrits par rapport a N1/N2

function c_ins_cur_av($annee, $pays_id, $inscur)
{
  global $DB_site2;

$j = date('j');
$d = date('d');
$t = date('t');
$m = date('m');


    $sql = "SELECT MONTH(dateinscription) MONTH, COUNT(*) COUNT";
    $sql .= " FROM utilisateur";
    $sql .= " INNER JOIN pays ON (utilisateur.paysid = pays.paysid)";
    $sql .= " WHERE cast(utilisateur.dateinscription as date) BETWEEN '".$annee."-01-01' AND '".$annee."-".$m."-".$d."'";
    $sql .= " AND pays.paysid='".$pays_id."'";
    $sql .= " AND infos NOT LIKE '%Contact mailing%'";
    $sql .= " GROUP BY MONTH(dateinscription)";
    $cams = $DB_site2->query($sql);

    while($cam = $DB_site2->fetch_array($cams)){
      $chiffremois += $cam['COUNT'];
    }

      	$result = $inscur - $chiffremois;

      if ($result == 0 && $chiffremois > 0)
      	$result = "same";
      else
      	$result = $inscur - $chiffremois;


      return $result;

   /*   if ($inscur == 0 && $chiffremois == 0)
      	return 0;
      if ($inscur > 0 && $chiffremois == 0)
      {
      	$inscur = $inscur + 1;
      	$chiffremois = 1;
      }
      if ($inscur == 0 && $chiffremois > 0)
      {
      	$inscur = 1;
      	$chiffremois = $chiffremois + 1;
      }

    $result = (100 * $inscur) / $chiffremois - 100;
    return $result;*/
}


/*******************************************************************************************
Calcul du nombre d'inscrits sur l'année EN COURS pour le site fr ou en(com) selon paramètre*
********************************************************************************************/
function c_inscrits_cur($annee, $pays_id)
{
  global $DB_site2;

$j = date('j');
$d = date('d');
$t = date('t');
$m = date('m');


    $sql = "SELECT MONTH(dateinscription) MONTH, COUNT(*) COUNT";
    $sql .= " FROM utilisateur";
    $sql .= " INNER JOIN pays ON (utilisateur.paysid = pays.paysid)";
    $sql .= " WHERE cast(utilisateur.dateinscription as date) BETWEEN '".$annee."-01-01' AND '".$annee."-".$m."-".$d."'";
    $sql .= " AND pays.paysid='".$pays_id."'";
    $sql .= " AND infos NOT LIKE '%Contact mailing%'";
    $sql .= " GROUP BY MONTH(dateinscription)";
    $cams = $DB_site2->query($sql);

    while($cam = $DB_site2->fetch_array($cams)){
      $chiffremois += $cam['COUNT'];
    }


      return $chiffremois;
}



function c_ins_av($annee, $pays_id, $inscur)
{
    global $DB_site2;

    $sql = "SELECT MONTH(dateinscription) MONTH, COUNT(*) COUNT";
    $sql .= " FROM utilisateur";
    $sql .= " INNER JOIN pays ON (utilisateur.paysid = pays.paysid)";
    $sql .= " WHERE YEAR(utilisateur.dateinscription)='".$annee."'";
    $sql .= " AND pays.paysid='".$pays_id."'";
    $sql .= " AND infos NOT LIKE '%Contact mailing%'";
    $sql .= " GROUP BY MONTH(dateinscription)";
    $cams = $DB_site2->query($sql);

      while($row = $DB_site2->fetch_array($cams)){
        $cam += $row['COUNT'];
      }

      	$result = $inscur - $cam;

      if ($result == 0 && $cam > 0)
      	$result = "same";
      else
      	$result = $inscur - $cam;


      return $result;
/*      if ($inscur == 0 && $cam == 0)
      	return 0;
      if ($inscur > 0 && $cam == 0)
      {
      	$inscur = $inscur + 1;
      	$cam = 1;
      }
      if ($inscur == 0 && $cam > 0)
      {
      	$inscur = 1;
      	$cam = $cam + 1;
      }


      $result = (100 * $inscur) / $cam - 100;
      return $result;*/
}


/***********************************************************************************************
Calcul du nombre d'inscrits sur une année antérieure selon paramètre*
************************************************************************************************/
function c_inscrits($annee, $pays_id)
{
    global $DB_site2;

    $sql = "SELECT MONTH(dateinscription) MONTH, COUNT(*) COUNT";
    $sql .= " FROM utilisateur";
    $sql .= " INNER JOIN pays ON (utilisateur.paysid = pays.paysid)";
    $sql .= " WHERE YEAR(utilisateur.dateinscription)='".$annee."'";
    $sql .= " AND pays.paysid='".$pays_id."'";
    $sql .= " AND infos NOT LIKE '%Contact mailing%'";
    $sql .= " GROUP BY MONTH(dateinscription)";
    $cams = $DB_site2->query($sql);

      while($row = $DB_site2->fetch_array($cams)){
        $cam += $row['COUNT'];
      }

return $cam;
}









/*
 _______    ___       ______ .___________. __    __  .______       _______ 
|   ____|  /   \     /      ||           ||  |  |  | |   _  \     |   ____|
|  |__    /  ^  \   |  ,----'`---|  |----`|  |  |  | |  |_)  |    |  |__   
|   __|  /  /_\  \  |  |         |  |     |  |  |  | |      /     |   __|  
|  |    /  _____  \ |  `----.    |  |     |  `--'  | |  |\  \----.|  |____ 
|__|   /__/     \__\ \______|    |__|      \______/  | _| `._____||_______|
                                                                           

*/




function c_factures($annee, $pays_id)
{
    global $DB_site2;

    $sql = "SELECT MONTH(dateexpedition) MONTH, COUNT(*) COUNT";
    $sql .= " FROM facture";
    $sql .= " INNER JOIN utilisateur ON (facture.userid = utilisateur.userid)";    
    $sql .= " INNER JOIN pays ON (utilisateur.paysid = pays.paysid)";
    $sql .= " WHERE YEAR(dateexpedition)='".$annee."'";
    $sql .= " AND pays.paysid='".$pays_id."'";
    $sql .= " AND valide IN (5,8)";
    $sql .= " GROUP BY MONTH(dateexpedition)";
    $cams = $DB_site2->query($sql);

      while($row = $DB_site2->fetch_array($cams)){
        $cam += $row['COUNT'];
      }

return $cam;

}


function c_fac_av($annee, $pays_id, $facur)
{
	    global $DB_site2;

    $sql = "SELECT MONTH(dateexpedition) MONTH, COUNT(*) COUNT";
    $sql .= " FROM facture";
    $sql .= " INNER JOIN utilisateur ON (facture.userid = utilisateur.userid)";    
    $sql .= " INNER JOIN pays ON (utilisateur.paysid = pays.paysid)";
    $sql .= " WHERE YEAR(dateexpedition)='".$annee."'";
    $sql .= " AND pays.paysid='".$pays_id."'";
    $sql .= " AND valide IN (5,8)";
    $sql .= " GROUP BY MONTH(dateexpedition)";
    $cams = $DB_site2->query($sql);

      while($row = $DB_site2->fetch_array($cams)){
        $cam += $row['COUNT'];
      }

      	$result[0] = $facur - $cam;

      if ($result[0] == 0 && $cam > 0)
      	$result[0] = "same";
      else
      	$result[0] = $facur - $cam;


      $result[1] = $cam;
      return $result;
}




function c_fac_cur_av($annee, $pays_id, $facur)
{
  global $DB_site2;

	$j = date('j');
	$d = date('d');
	$t = date('t');
	$m = date('m');


    $sql = "SELECT MONTH(dateexpedition) MONTH, COUNT(*) COUNT";
    $sql .= " FROM facture";
    $sql .= " INNER JOIN utilisateur ON (facture.userid = utilisateur.userid)";    
    $sql .= " INNER JOIN pays ON (utilisateur.paysid = pays.paysid)";
    $sql .= " WHERE cast(dateexpedition as date) BETWEEN '".$annee."-01-01' AND '".$annee."-".$m."-".$d."'";
    $sql .= " AND pays.paysid='".$pays_id."'";
    $sql .= " AND valide IN (5,8)";
    $sql .= " GROUP BY MONTH(dateexpedition)";
    $cams = $DB_site2->query($sql);

    while($cam = $DB_site2->fetch_array($cams)){
      $chiffremois += $cam['COUNT'];
    }

      	$result[0] = $facur - $chiffremois;

      if ($result[0] == 0 && $chiffremois > 0)
      	$result[0] = "same";
      else
      	$result[0] = $facur - $chiffremois;


      $result[1] = $chiffremois;
      return $result;
}


function c_factures_cur($annee, $pays_id)
{
	  global $DB_site2;

	$j = date('j');
	$d = date('d');
	$t = date('t');
	$m = date('m');


    $sql = "SELECT MONTH(dateexpedition) MONTH, COUNT(*) COUNT";
    $sql .= " FROM facture";
    $sql .= " INNER JOIN utilisateur ON (facture.userid = utilisateur.userid)";    
    $sql .= " INNER JOIN pays ON (utilisateur.paysid = pays.paysid)";
    $sql .= " WHERE cast(dateexpedition as date) BETWEEN '".$annee."-01-01' AND '".$annee."-".$m."-".$d."'";
    $sql .= " AND pays.paysid='".$pays_id."'";
    $sql .= " AND valide IN (5,8)";
    $sql .= " GROUP BY MONTH(dateexpedition)";
    $cams = $DB_site2->query($sql);

    while($cam = $DB_site2->fetch_array($cams)){
      $chiffremois += $cam['COUNT'];
    }
      return $chiffremois;
}





























function annee_prec($annee,$intra=true,$encours=false) {
    global $DB_site2;
    
    $return=array();
    
    $sqldate="LIKE '$annee%'";
    if($encours) $sqldate="BETWEEN '$annee-01-01' AND '$annee-".date("m-d")."'";
    $sql="SELECT f.* FROM facture f INNER JOIN utilisateur u USING(userid) INNER JOIN pays p ON(p.paysid=u.paysid) WHERE valide IN (5,8) AND p.tvaintracom=".($intra?"1":"0 AND TVAtauxnormal=0")." AND f.datefacture ".$sqldate;

    $req=$DB_site2->query($sql);
    while($f=$DB_site2->fetch_array($req)) {
  $facture=new facture($DB_site2,$f);
  $return[$facture->utilisateur->paysid]+=$facture->totalht;
    }
    
    return $return;
}

function tdvalue($value,$sym="",$sign=null,$climit=0){
  if($value=="") return "<td></td>";
  return "<td class=\"ca_nb\" style=\"color:".($value>$climit?"green":"red").";\">".($sign&&$value>0?"+":"").number_format($value,2,',',' ').$sym."</td>";
}


function tdvalue_prc($value,$sym="",$sign=null,$climit=0){
  if($value=="") return "<td></td>";
  return "<td class=\"ca_prc\" style=\"color:".($value>$climit?"green":"red").";\">".($sign&&$value>0?"+":"").number_format($value,2,',',' ').$sym."</td>";
}


function tdvalue_n($value,$sym="",$sign=null,$climit=0){
  if($value=="") return "<td></td>";
  if($value=="same") return "<td class=\"ca_nb\" style=\"color:green;\">+0</td>";
  return "<td class=\"ca_nb\" style=\"color:".($value>$climit?"green":"red").";\">".($value>0?"+":"").$value."</td>";
}


function tdvalue_n_prc($value,$sym="",$sign=null,$climit=0){
  if($value=="") return "<td></td>";
  if($value=="same") return "<td class=\"ca_prc\" style=\"color:green;\">+0</td>";
  return "<td class=\"ca_prc\" style=\"color:".($value>$climit?"green":"red").";\">".($value>0?"+":"").$value."</td>";
}


function ca($annee){
  global $DB_site2;

  $chiffremois=0;
  $etat="5,8";
  $sql="select * from facture WHERE valide in (".$etat.") and dateexpedition LIKE '".$annee."-%'";
  $cams=$DB_site2->query($sql);

  while ($cam=$DB_site2->fetch_array($cams)) {
        $f=new facture($DB_site2,$cam);
        $chiffremois += $f->totalht;
  }
  return $chiffremois;
}

function formatlist($paysid){
  global $listefacture;

  $html="<table>";

  $list=$listefacture[$paysid];

  if(is_null($list)) return "";
  
  foreach($list as $f)
      $html.="<tr>
        <td><a href=\"facture.php?action=editer&facturid=".$f->facturid."\" target=\"_blank\">".$f->facturid."</a></td>
        <td>".$f->utilisateur->nom."</td>
        <td>".date("d/m/Y",$f->dateexpedition)."</td>
        <td align=\"right\">".number_format($f->totalht,2,',','')." &euro;</td>
      </tr>";

  $html.="</table>";
  return $html;
  
}

?>