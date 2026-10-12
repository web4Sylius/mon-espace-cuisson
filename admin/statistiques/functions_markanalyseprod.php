<?php



/*
FFFFFFF  OOOOO  NN   NN  CCCCC  TTTTTTT IIIII  OOOOO  NN   NN  SSSSS  
FF      OO   OO NNN  NN CC    C   TTT    III  OO   OO NNN  NN SS      
FFFF    OO   OO NN N NN CC        TTT    III  OO   OO NN N NN  SSSSS  
FF      OO   OO NN  NNN CC    C   TTT    III  OO   OO NN  NNN      SS 
FF       OOOO0  NN   NN  CCCCC    TTT   IIIII  OOOO0  NN   NN  SSSSS                                                                   
*/








function get_brand_ca($prov, $year)
{
    global $DB_site2;
  
  $Y = date("Y");
  $i = 0;
  $sql = "SELECT marqueid, libelle FROM marque ORDER BY marque.libelle ASC";
  $cams = $DB_site2->query($sql);
       while($cam = $DB_site2->fetch_array($cams))
       {
         $sql2 = "";
         $cams2 = "";
         $cam2 = "";
         $marque_id[$i] = $cam['marqueid'];
         $marque_infos[$i] = $cam['libelle'];

        if ($year == $Y)
          {
           $sql2 = "SELECT SUM((lf.prix * lf.qte) / ( 1 + ( lf.tva /100))) AS 'prixHT', f.prixport / (1 + f.tvaport /100) AS 'portHT',CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM((lf.prix * lf.qte) / ( 1 + ( lf.tva /100))) + (f.prixport / (1 + f.tvaport /100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END AS remiseHT FROM facture f INNER JOIN lignefacture lf ON (lf.factureid = f.facturid) INNER JOIN article_marque am ON ( am.artid = lf.artid) INNER JOIN marque ON (marque.marqueid = am.marqueid) WHERE f.valide IN (1,5,7,8) AND marque.marqueid = '".$marque_id[$i]."' AND cast(f.dateexpedition AS date) BETWEEN '".$year."-01-01' AND NOW() ".$prov."GROUP BY f.facturid";
                $cams2 = $DB_site2->query($sql2);
                $i++;
                while($cam2 = $DB_site2->fetch_array($cams2))
                {
                  $marque_infos[$i] += $cam2['prixHT'] + $cam2['portHT'] - $cam2['remiseHT'];
                }
         }
         elseif ($year < $Y)
         {
           $sql2 = "SELECT SUM((lf.prix * lf.qte) / ( 1 + ( lf.tva /100))) AS 'prixHT', f.prixport / (1 + f.tvaport /100) AS 'portHT',CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM((lf.prix * lf.qte) / ( 1 + ( lf.tva /100))) + (f.prixport / (1 + f.tvaport /100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END AS remiseHT FROM facture f INNER JOIN lignefacture lf ON (lf.factureid = f.facturid) INNER JOIN article_marque am ON ( am.artid = lf.artid) INNER JOIN marque ON (marque.marqueid = am.marqueid) WHERE f.valide IN (1,5,7,8) AND marque.marqueid = '".$marque_id[$i]."' AND cast(f.dateexpedition AS date) BETWEEN '".$year."-01-01' AND '".$year."-12-31' ".$prov."GROUP BY f.facturid";
                $cams2 = $DB_site2->query($sql2);
                $i++;
                while($cam2 = $DB_site2->fetch_array($cams2))
                {
                  $marque_infos[$i] += $cam2['prixHT'] + $cam2['portHT'] - $cam2['remiseHT'];
                }
         }
         $i++;
       }

      return $marque_infos;
}



function get_brand_ca_compare_current_time($prov, $year)
{
  global $DB_site2;

  $d = date('d');
  $m = date('m');

  $i = 0;
  $sql = "SELECT marqueid, libelle FROM marque ORDER BY marque.libelle ASC";
  $cams = $DB_site2->query($sql);
       while($cam = $DB_site2->fetch_array($cams))
       {
         $sql2 = "";
         $cams2 = "";
         $cam2 = "";
         $marque_id[$i] = $cam['marqueid'];
         $marque_infos[$i] = $cam['libelle'];
 
         $sql2 = "SELECT SUM((lf.prix * lf.qte) / ( 1 + ( lf.tva /100))) AS 'prixHT', f.prixport / (1 + f.tvaport /100) AS 'portHT',CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM((lf.prix * lf.qte) / ( 1 + ( lf.tva /100))) + (f.prixport / (1 + f.tvaport /100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END AS remiseHT FROM facture f INNER JOIN lignefacture lf ON (lf.factureid = f.facturid) INNER JOIN article_marque am ON ( am.artid = lf.artid) INNER JOIN marque ON (marque.marqueid = am.marqueid) WHERE f.valide IN (1,5,7,8) AND marque.marqueid = '".$marque_id[$i]."' AND cast(f.dateexpedition AS date) BETWEEN '".$year."-01-01' AND '".$year."-".$m."-".$d."' ".$prov."GROUP BY f.facturid";
                $cams2 = $DB_site2->query($sql2);
                $i++;
                while($cam2 = $DB_site2->fetch_array($cams2))
                {
                  $marque_infos[$i] += $cam2['prixHT'] + $cam2['portHT'] - $cam2['remiseHT'];
                }
            $i++;
        }
      return $marque_infos;
}



function get_brand_qte_current_time($prov, $year)
{
  global $DB_site2;

  $d = date('d');
  $m = date('m');

  $i = 0;
  $sql = "SELECT marqueid, libelle FROM marque ORDER BY marque.libelle ASC";
  $cams = $DB_site2->query($sql);
       while($cam = $DB_site2->fetch_array($cams))
       {
         $sql2 = "";
         $cams2 = "";
         $cam2 = "";
         $marque_id[$i] = $cam['marqueid'];

         $sql2 = "SELECT COUNT(*) nbr FROM facture f INNER JOIN lignefacture lf ON (lf.factureid=f.facturid) INNER JOIN article_marque am ON (am.artid=lf.artid) INNER JOIN marque ON (marque.marqueid=am.marqueid) WHERE cast(f.dateexpedition AS date) BETWEEN '".$year."-01-01' AND '".$year."-".$m."-".$d."' ".$prov."AND f.valide IN (1,5,7,8) AND marque.marqueid='".$marque_id[$i]."'";
         $cams2 = $DB_site2->query($sql2);
         while($cams2 = $DB_site2->fetch_array($cams2))
           $marque_qte[$i] = $cams2['nbr'];
         $i++;       
       }
    return $marque_qte;
}




function get_brand_qte($prov, $year)
{
  global $DB_site2;

  $i = 0;
  $sql = "SELECT marqueid, libelle FROM marque ORDER BY marque.libelle ASC";
  $cams = $DB_site2->query($sql);
       while($cam = $DB_site2->fetch_array($cams))
       {
         $sql2 = "";
         $cams2 = "";
         $cam2 = "";
         $marque_id[$i] = $cam['marqueid'];

         $sql2 = "SELECT COUNT(*) nbr FROM facture f INNER JOIN lignefacture lf ON (lf.factureid=f.facturid) INNER JOIN article_marque am ON (am.artid=lf.artid) INNER JOIN marque ON (marque.marqueid=am.marqueid) WHERE YEAR(f.dateexpedition)='".$year."' ".$prov."AND f.valide IN (1,5,7,8) AND marque.marqueid='".$marque_id[$i]."'";
         $cams2 = $DB_site2->query($sql2);
         while($cams2 = $DB_site2->fetch_array($cams2))
           $marque_qte[$i] = $cams2['nbr'];
         $i++;
      }
    return $marque_qte;
}


















//    FONCTIONS POUR LES CALCULS POUR MARQUE UNIQUE - PAS DE CAT SELECTIONNEES



function get_ca_month($prov, $year, $brand_id)
{
  global $DB_site2;

  $ca_month[0]=0;$ca_month[1]=0;$ca_month[2]=0;$ca_month[3]=0;$ca_month[4]=0;$ca_month[5]=0;$ca_month[6]=0;$ca_month[7]=0;$ca_month[8]=0;$ca_month[9]=0;$ca_month[10]=0;$ca_month[11] = 0;

  $i = 0;

  $sql = "SELECT MONTH(f.dateexpedition), SUM((lf.prix * lf.qte) / ( 1 + ( lf.tva /100))) AS 'prixHT', f.prixport / (1 + f.tvaport /100) AS 'portHT',CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM((lf.prix * lf.qte) / ( 1 + ( lf.tva /100))) + (f.prixport / (1 + f.tvaport /100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END AS remiseHT FROM facture f INNER JOIN lignefacture lf ON (lf.factureid = f.facturid) INNER JOIN article_marque am ON ( am.artid = lf.artid) INNER JOIN marque ON (marque.marqueid = am.marqueid) WHERE f.valide IN (1,5,7,8) AND marque.marqueid = '".$brand_id."' AND cast(f.dateexpedition AS date) BETWEEN '".$year."-01-01' AND '".$year."-12-31' ".$prov."GROUP BY MONTH(f.dateexpedition)";
  $cams = $DB_site2->query($sql);
        while ($cam = $DB_site2->fetch_array($cams))
        {
          $ca_month[$i] = $cam['prixHT'] + $cam['portHT'] - $cam['remiseHT'];
          $i++;
        }

        return $ca_month;
}


function get_ca_month_cr($prov, $year, $brand_id)
{
  global $DB_site2;

  $ca_month[0]=0;$ca_month[1]=0;$ca_month[2]=0;$ca_month[3]=0;$ca_month[4]=0;$ca_month[5]=0;$ca_month[6]=0;$ca_month[7]=0;$ca_month[8]=0;$ca_month[9]=0;$ca_month[10]=0;$ca_month[11] = 0;

  $d = date('d');
  $m = date('m');

  $i = 0;

  $sql = "SELECT MONTH(f.dateexpedition), SUM((lf.prix * lf.qte) / ( 1 + ( lf.tva /100))) AS 'prixHT', f.prixport / (1 + f.tvaport /100) AS 'portHT',CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM((lf.prix * lf.qte) / ( 1 + ( lf.tva /100))) + (f.prixport / (1 + f.tvaport /100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END AS remiseHT FROM facture f INNER JOIN lignefacture lf ON (lf.factureid = f.facturid) INNER JOIN article_marque am ON ( am.artid = lf.artid) INNER JOIN marque ON (marque.marqueid = am.marqueid) WHERE f.valide IN (1,5,7,8) AND marque.marqueid = '".$brand_id."' AND cast(f.dateexpedition AS date) BETWEEN '".$year."-01-01' AND '".$year."-".$m."-".$d."' ".$prov."GROUP BY MONTH(f.dateexpedition)";
  $cams = $DB_site2->query($sql);
        while ($cam = $DB_site2->fetch_array($cams))
        {
          $ca_month[$i] = $cam['prixHT'] + $cam['portHT'] - $cam['remiseHT'];
          $i++;
        }

        return $ca_month;
}


function get_qte_month($prov, $year, $brand_id)
{
  global $DB_site2;

  $ca_month[0]=0;$ca_month[1]=0;$ca_month[2]=0;$ca_month[3]=0;$ca_month[4]=0;$ca_month[5]=0;$ca_month[6]=0;$ca_month[7]=0;$ca_month[8]=0;$ca_month[9]=0;$ca_month[10]=0;$ca_month[11] = 0;


  $i = 0;

  $sql = "SELECT MONTH(f.dateexpedition), COUNT(*) nbr FROM facture f INNER JOIN lignefacture lf ON (lf.factureid=f.facturid) INNER JOIN article_marque am ON (am.artid=lf.artid) INNER JOIN marque ON (marque.marqueid=am.marqueid) WHERE YEAR(f.dateexpedition)='".$year."' ".$prov."AND f.valide IN (1,5,7,8) AND marque.marqueid='".$brand_id."' GROUP BY MONTH(f.dateexpedition)";
  $cams = $DB_site2->query($sql);
        while ($cam = $DB_site2->fetch_array($cams))
        {
          $ca_month[$i] = $cam['nbr'];
          $i++;
        }

        return $ca_month;
}


function get_qte_month_cr($prov, $year, $brand_id)
{
  global $DB_site2;
  
  $d = date('d');
  $m = date('m');
  $ca_month[0]=0;$ca_month[1]=0;$ca_month[2]=0;$ca_month[3]=0;$ca_month[4]=0;$ca_month[5]=0;$ca_month[6]=0;$ca_month[7]=0;$ca_month[8]=0;$ca_month[9]=0;$ca_month[10]=0;$ca_month[11] = 0;


  $i = 0;

  $sql = "SELECT MONTH(f.dateexpedition), COUNT(*) nbr FROM facture f INNER JOIN lignefacture lf ON (lf.factureid=f.facturid) INNER JOIN article_marque am ON (am.artid=lf.artid) INNER JOIN marque ON (marque.marqueid=am.marqueid) WHERE cast(f.dateexpedition AS date) BETWEEN '".$year."-01-01' AND '".$year."-".$m."-".$d."' ".$prov."AND f.valide IN (1,5,7,8) AND marque.marqueid='".$brand_id."' GROUP BY MONTH(f.dateexpedition)";
  $cams = $DB_site2->query($sql);
        while ($cam = $DB_site2->fetch_array($cams))
        {
          $ca_month[$i] = $cam['nbr'];
          $i++;
        }

        return $ca_month;
}























//    FONCTIONS POUR LES CALCULS POUR MARQUE UNIQUE - 1 CAT SELECTIONNEE, PAS DE SOUS CAT



function get_ca_month_cat($prov, $year, $brand_id, $categorie)
{
  global $DB_site2;

  $ca_month[0]=0;$ca_month[1]=0;$ca_month[2]=0;$ca_month[3]=0;$ca_month[4]=0;$ca_month[5]=0;$ca_month[6]=0;$ca_month[7]=0;$ca_month[8]=0;$ca_month[9]=0;$ca_month[10]=0;$ca_month[11] = 0;

  $i = 0;

  $sql = "SELECT MONTH(f.dateexpedition), SUM((lf.prix * lf.qte) / ( 1 + ( lf.tva /100))) AS 'prixHT', f.prixport / (1 + f.tvaport /100) AS 'portHT',CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM((lf.prix * lf.qte) / ( 1 + ( lf.tva /100))) + (f.prixport / (1 + f.tvaport /100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END AS remiseHT FROM facture f INNER JOIN lignefacture lf ON (lf.factureid = f.facturid) INNER JOIN article_marque am ON ( am.artid = lf.artid) INNER JOIN marque ON (marque.marqueid = am.marqueid) INNER JOIN article a ON (a.artid=am.artid) INNER JOIN categorie ON (categorie.catid=a.catid) WHERE f.valide IN (1,5,7,8) AND marque.marqueid = '".$brand_id."' AND a.typearticleid='".$categorie."' AND cast(f.dateexpedition AS date) BETWEEN '".$year."-01-01' AND '".$year."-12-31' ".$prov."GROUP BY MONTH(f.dateexpedition)";
  $cams = $DB_site2->query($sql);
        while ($cam = $DB_site2->fetch_array($cams))
        {
          $ca_month[$i] = $cam['prixHT'] + $cam['portHT'] - $cam['remiseHT'];
          $i++;
        }

        return $ca_month;
}


function get_ca_month_cr_cat($prov, $year, $brand_id, $categorie)
{
  global $DB_site2;

  $ca_month[0]=0;$ca_month[1]=0;$ca_month[2]=0;$ca_month[3]=0;$ca_month[4]=0;$ca_month[5]=0;$ca_month[6]=0;$ca_month[7]=0;$ca_month[8]=0;$ca_month[9]=0;$ca_month[10]=0;$ca_month[11] = 0;

  $d = date('d');
  $m = date('m');

  $i = 0;

  $sql = "SELECT MONTH(f.dateexpedition), SUM((lf.prix * lf.qte) / ( 1 + ( lf.tva /100))) AS 'prixHT', f.prixport / (1 + f.tvaport /100) AS 'portHT',CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM((lf.prix * lf.qte) / ( 1 + ( lf.tva /100))) + (f.prixport / (1 + f.tvaport /100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END AS remiseHT FROM facture f INNER JOIN lignefacture lf ON (lf.factureid = f.facturid) INNER JOIN article_marque am ON ( am.artid = lf.artid) INNER JOIN marque ON (marque.marqueid = am.marqueid) INNER JOIN article a ON (a.artid=am.artid) INNER JOIN categorie ON (categorie.catid=a.catid) WHERE f.valide IN (1,5,7,8) AND marque.marqueid = '".$brand_id."' AND a.typearticleid='".$categorie."' AND cast(f.dateexpedition AS date) BETWEEN '".$year."-01-01' AND '".$year."-".$m."-".$d."' ".$prov."GROUP BY MONTH(f.dateexpedition)";
  $cams = $DB_site2->query($sql);
        while ($cam = $DB_site2->fetch_array($cams))
        {
          $ca_month[$i] = $cam['prixHT'] + $cam['portHT'] - $cam['remiseHT'];
          $i++;
        }

        return $ca_month;
}


function get_qte_month_cat($prov, $year, $brand_id, $categorie)
{
  global $DB_site2;

  $ca_month[0]=0;$ca_month[1]=0;$ca_month[2]=0;$ca_month[3]=0;$ca_month[4]=0;$ca_month[5]=0;$ca_month[6]=0;$ca_month[7]=0;$ca_month[8]=0;$ca_month[9]=0;$ca_month[10]=0;$ca_month[11] = 0;


  $i = 0;

  $sql = "SELECT MONTH(f.dateexpedition), COUNT(*) nbr FROM facture f INNER JOIN lignefacture lf ON (lf.factureid=f.facturid) INNER JOIN article_marque am ON (am.artid=lf.artid) INNER JOIN marque ON (marque.marqueid=am.marqueid) INNER JOIN article a ON (a.artid=am.artid) INNER JOIN categorie ON (categorie.catid=a.catid) WHERE YEAR(f.dateexpedition)='".$year."' ".$prov."AND f.valide IN (1,5,7,8) AND marque.marqueid='".$brand_id."' AND a.typearticleid='".$categorie."' GROUP BY MONTH(f.dateexpedition)";
  $cams = $DB_site2->query($sql);
        while ($cam = $DB_site2->fetch_array($cams))
        {
          $ca_month[$i] = $cam['nbr'];
          $i++;
        }

        return $ca_month;
}


function get_qte_month_cr_cat($prov, $year, $brand_id, $categorie)
{
  global $DB_site2;
  
  $d = date('d');
  $m = date('m');
  $ca_month[0]=0;$ca_month[1]=0;$ca_month[2]=0;$ca_month[3]=0;$ca_month[4]=0;$ca_month[5]=0;$ca_month[6]=0;$ca_month[7]=0;$ca_month[8]=0;$ca_month[9]=0;$ca_month[10]=0;$ca_month[11] = 0;


  $i = 0;

  $sql = "SELECT MONTH(f.dateexpedition), COUNT(*) nbr FROM facture f INNER JOIN lignefacture lf ON (lf.factureid=f.facturid) INNER JOIN article_marque am ON (am.artid=lf.artid) INNER JOIN marque ON (marque.marqueid=am.marqueid) INNER JOIN article a ON (a.artid=am.artid) INNER JOIN categorie ON (categorie.catid=a.catid) WHERE cast(f.dateexpedition AS date) BETWEEN '".$year."-01-01' ".$prov."AND '".$year."-".$m."-".$d."' AND f.valide IN (1,5,7,8) AND marque.marqueid='".$brand_id."' AND a.typearticleid='".$categorie."' GROUP BY MONTH(f.dateexpedition)";
  $cams = $DB_site2->query($sql);
        while ($cam = $DB_site2->fetch_array($cams))
        {
          $ca_month[$i] = $cam['nbr'];
          $i++;
        }

        return $ca_month;
}


























//    FONCTIONS POUR LES CALCULS POUR MARQUE UNIQUE - 1 CAT SELECTIONNEE, 1 SOUS CAT



function get_ca_month_sscat($prov, $year, $brand_id, $categorie, $sscat)
{
  global $DB_site2;

  $ca_month[0]=0;$ca_month[1]=0;$ca_month[2]=0;$ca_month[3]=0;$ca_month[4]=0;$ca_month[5]=0;$ca_month[6]=0;$ca_month[7]=0;$ca_month[8]=0;$ca_month[9]=0;$ca_month[10]=0;$ca_month[11] = 0;

  $i = 0;

  $sql = "SELECT MONTH(f.dateexpedition), SUM((lf.prix * lf.qte) / ( 1 + ( lf.tva /100))) AS 'prixHT', f.prixport / (1 + f.tvaport /100) AS 'portHT',CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM((lf.prix * lf.qte) / ( 1 + ( lf.tva /100))) + (f.prixport / (1 + f.tvaport /100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END AS remiseHT FROM facture f INNER JOIN lignefacture lf ON (lf.factureid = f.facturid) INNER JOIN article_marque am ON ( am.artid = lf.artid) INNER JOIN marque ON (marque.marqueid = am.marqueid) INNER JOIN article a ON (a.artid=am.artid) INNER JOIN categorie ON (categorie.catid=a.catid) WHERE f.valide IN (1,5,7,8) AND marque.marqueid = '".$brand_id."' AND a.typearticleid='".$categorie."' AND categorie.catid ".$sscat." AND cast(f.dateexpedition AS date) BETWEEN '".$year."-01-01' AND '".$year."-12-31' ".$prov."GROUP BY MONTH(f.dateexpedition)";
  $cams = $DB_site2->query($sql);
        while ($cam = $DB_site2->fetch_array($cams))
        {
          $ca_month[$i] = $cam['prixHT'] + $cam['portHT'] - $cam['remiseHT'];
          $i++;
        }

        return $ca_month;
}


function get_ca_month_cr_sscat($prov, $year, $brand_id, $categorie, $sscat)
{
  global $DB_site2;

  $ca_month[0]=0;$ca_month[1]=0;$ca_month[2]=0;$ca_month[3]=0;$ca_month[4]=0;$ca_month[5]=0;$ca_month[6]=0;$ca_month[7]=0;$ca_month[8]=0;$ca_month[9]=0;$ca_month[10]=0;$ca_month[11] = 0;

  $d = date('d');
  $m = date('m');

  $i = 0;

  $sql = "SELECT MONTH(f.dateexpedition), SUM((lf.prix * lf.qte) / ( 1 + ( lf.tva /100))) AS 'prixHT', f.prixport / (1 + f.tvaport /100) AS 'portHT',CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM((lf.prix * lf.qte) / ( 1 + ( lf.tva /100))) + (f.prixport / (1 + f.tvaport /100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END AS remiseHT FROM facture f INNER JOIN lignefacture lf ON (lf.factureid = f.facturid) INNER JOIN article_marque am ON ( am.artid = lf.artid) INNER JOIN marque ON (marque.marqueid = am.marqueid) INNER JOIN article a ON (a.artid=am.artid) INNER JOIN categorie ON (categorie.catid=a.catid) WHERE f.valide IN (1,5,7,8) AND marque.marqueid = '".$brand_id."' AND a.typearticleid='".$categorie."' AND categorie.catid ".$sscat." AND cast(f.dateexpedition AS date) BETWEEN '".$year."-01-01' AND '".$year."-".$m."-".$d."' ".$prov."GROUP BY MONTH(f.dateexpedition)";
  $cams = $DB_site2->query($sql);
        while ($cam = $DB_site2->fetch_array($cams))
        {
          $ca_month[$i] = $cam['prixHT'] + $cam['portHT'] - $cam['remiseHT'];
          $i++;
        }

        return $ca_month;
}


function get_qte_month_sscat($prov, $year, $brand_id, $categorie, $sscat)
{
  global $DB_site2;

  $ca_month[0]=0;$ca_month[1]=0;$ca_month[2]=0;$ca_month[3]=0;$ca_month[4]=0;$ca_month[5]=0;$ca_month[6]=0;$ca_month[7]=0;$ca_month[8]=0;$ca_month[9]=0;$ca_month[10]=0;$ca_month[11] = 0;


  $i = 0;

  $sql = "SELECT MONTH(f.dateexpedition), COUNT(*) nbr FROM facture f INNER JOIN lignefacture lf ON (lf.factureid=f.facturid) INNER JOIN article_marque am ON (am.artid=lf.artid) INNER JOIN marque ON (marque.marqueid=am.marqueid) INNER JOIN article a ON (a.artid=am.artid) INNER JOIN categorie ON (categorie.catid=a.catid) WHERE YEAR(f.dateexpedition)='".$year."' ".$prov."AND f.valide IN (1,5,7,8) AND marque.marqueid='".$brand_id."' AND a.typearticleid='".$categorie."' AND categorie.catid ".$sscat." GROUP BY MONTH(f.dateexpedition)";
  $cams = $DB_site2->query($sql);
        while ($cam = $DB_site2->fetch_array($cams))
        {
          $ca_month[$i] = $cam['nbr'];
          $i++;
        }

        return $ca_month;
}


function get_qte_month_cr_sscat($prov, $year, $brand_id, $categorie, $sscat)
{
  global $DB_site2;
  
  $d = date('d');
  $m = date('m');
  $ca_month[0]=0;$ca_month[1]=0;$ca_month[2]=0;$ca_month[3]=0;$ca_month[4]=0;$ca_month[5]=0;$ca_month[6]=0;$ca_month[7]=0;$ca_month[8]=0;$ca_month[9]=0;$ca_month[10]=0;$ca_month[11] = 0;


  $i = 0;

  $sql = "SELECT MONTH(f.dateexpedition), COUNT(*) nbr FROM facture f INNER JOIN lignefacture lf ON (lf.factureid=f.facturid) INNER JOIN article_marque am ON (am.artid=lf.artid) INNER JOIN marque ON (marque.marqueid=am.marqueid) INNER JOIN article a ON (a.artid=am.artid) INNER JOIN categorie ON (categorie.catid=a.catid) WHERE cast(f.dateexpedition AS date) BETWEEN '".$year."-01-01' AND '".$year."-".$m."-".$d."' ".$prov."AND f.valide IN (1,5,7,8) AND marque.marqueid='".$brand_id."' AND a.typearticleid='".$categorie."' AND categorie.catid ".$sscat." GROUP BY MONTH(f.dateexpedition)";
  $cams = $DB_site2->query($sql);
        while ($cam = $DB_site2->fetch_array($cams))
        {
          $ca_month[$i] = $cam['nbr'];
          $i++;
        }

        return $ca_month;
}

















//    FONCTIONS POUR LES CALCULS POUR MARQUE UNIQUE - 1 CAT SELECTIONNEE, TRI PAR REFERENCE PRODUIT



function get_ca_cat_prod($prov, $year, $brand_id, $categorie)
{
  global $DB_site2;

  $i = 0;

  $sql = "SELECT DISTINCT(a.artcode), SUM((lf.prix * lf.qte) / ( 1 + ( lf.tva /100))) AS 'prixHT', f.prixport / (1 + f.tvaport /100) AS 'portHT',CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM((lf.prix * lf.qte) / ( 1 + ( lf.tva /100))) + (f.prixport / (1 + f.tvaport /100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END AS remiseHT FROM facture f INNER JOIN lignefacture lf ON (lf.factureid = f.facturid) INNER JOIN article_marque am ON ( am.artid = lf.artid) INNER JOIN marque ON (marque.marqueid = am.marqueid) INNER JOIN article a ON (a.artid=am.artid) INNER JOIN categorie ON (categorie.catid=a.catid) WHERE f.valide IN (1,5,7,8) AND marque.marqueid = '".$brand_id."' AND a.typearticleid='".$categorie."' AND cast(f.dateexpedition AS date) BETWEEN '".$year."-01-01' AND '".$year."-12-31' ".$prov."GROUP BY(a.artcode)";
  $cams = $DB_site2->query($sql);
        while ($cam = $DB_site2->fetch_array($cams))
        {
          $ca_month[$i] = $cam['artcode'];
          $ca_month[$i+1] = $cam['prixHT'] + $cam['portHT'] - $cam['remiseHT'];
          $i = $i+2;
        }

        return $ca_month;
}


function get_ca_cr_cat_prod($prov, $year, $brand_id, $categorie)
{
  global $DB_site2;


  $d = date('d');
  $m = date('m');

  $i = 0;

  $sql = "SELECT DISTINCT(a.artcode), SUM((lf.prix * lf.qte) / ( 1 + ( lf.tva /100))) AS 'prixHT', f.prixport / (1 + f.tvaport /100) AS 'portHT',CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM((lf.prix * lf.qte) / ( 1 + ( lf.tva /100))) + (f.prixport / (1 + f.tvaport /100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END AS remiseHT FROM facture f INNER JOIN lignefacture lf ON (lf.factureid = f.facturid) INNER JOIN article_marque am ON ( am.artid = lf.artid) INNER JOIN marque ON (marque.marqueid = am.marqueid) INNER JOIN article a ON (a.artid=am.artid) INNER JOIN categorie ON (categorie.catid=a.catid) WHERE f.valide IN (1,5,7,8) AND marque.marqueid = '".$brand_id."' AND a.typearticleid='".$categorie."' AND cast(f.dateexpedition AS date) BETWEEN '".$year."-01-01' AND '".$year."-".$m."-".$d."' ".$prov."GROUP BY(a.artcode)";
  $cams = $DB_site2->query($sql);
        while ($cam = $DB_site2->fetch_array($cams))
        {
          $ca_month[$i] = $cam['artcode'];
          $ca_month[$i+1] = $cam['prixHT'] + $cam['portHT'] - $cam['remiseHT'];
          $i = $i+2;
        }

        return $ca_month;
}


function get_qte_cat_prod($prov, $year, $brand_id, $categorie)
{
  global $DB_site2;


  $i = 0;

  $sql = "SELECT DISTINCT(a.artcode), COUNT(*) nbr FROM facture f INNER JOIN lignefacture lf ON (lf.factureid=f.facturid) INNER JOIN article_marque am ON (am.artid=lf.artid) INNER JOIN marque ON (marque.marqueid=am.marqueid) INNER JOIN article a ON (a.artid=am.artid) INNER JOIN categorie ON (categorie.catid=a.catid) WHERE YEAR(f.dateexpedition)='".$year."' ".$prov."AND f.valide IN (1,5,7,8) AND marque.marqueid='".$brand_id."' AND a.typearticleid='".$categorie."' GROUP BY(a.artcode)";
  $cams = $DB_site2->query($sql);
        while ($cam = $DB_site2->fetch_array($cams))
        {
          $ca_month[$i] = $cam['artcode'];
          $ca_month[$i+1] = $cam['nbr'];
          $i = $i+2;
        }

        return $ca_month;
}


function get_qte_cr_cat_prod($prov, $year, $brand_id, $categorie)
{
  global $DB_site2;
  
  $d = date('d');
  $m = date('m');

  $i = 0;

  $sql = "SELECT DISTINCT(a.artcode), COUNT(*) nbr FROM facture f INNER JOIN lignefacture lf ON (lf.factureid=f.facturid) INNER JOIN article_marque am ON (am.artid=lf.artid) INNER JOIN marque ON (marque.marqueid=am.marqueid) INNER JOIN article a ON (a.artid=am.artid) INNER JOIN categorie ON (categorie.catid=a.catid) WHERE cast(f.dateexpedition AS date) BETWEEN '".$year."-01-01' AND '".$year."-".$m."-".$d."' ".$prov."AND f.valide IN (1,5,7,8) AND marque.marqueid='".$brand_id."' AND a.typearticleid='".$categorie."' GROUP BY(a.artcode)";
  $cams = $DB_site2->query($sql);
        while ($cam = $DB_site2->fetch_array($cams))
        {
          $ca_month[$i] = $cam['artcode'];
          $ca_month[$i+1] = $cam['nbr'];
          $i = $i+2;
        }

        return $ca_month;
}












function get_array_month()
{
  $m[0] = "Janvier";
  $m[1] = "Février";
  $m[2] = "Mars";
  $m[3] = "Avril";
  $m[4] = "Mai";
  $m[5] = "Juin";
  $m[6] = "Juillet";
  $m[7] = "Août";
  $m[8] = "Septembre";
  $m[9] = "Octobre";
  $m[10] = "Novembre";
  $m[11] = "Décembre";

  return $m;
}

/* Fonction affichage ligne tableau */


function tdvalue($value,$sym="",$sign=null,$climit=0){
  if($value=="") return "<td></td>";
  return "<td nowrap class=\"ca_nb\" style=\"color:".($value>$climit?"green":"red").";\">".($sign&&$value>0?"+":"").number_format($value,2,',',' ').$sym."</td>";
}

function tdvalue_prc($value,$sym="",$sign=null,$climit=0){
  if($value=="") return "<td></td>";
  return "<td nowrap class=\"ca_prc\" style=\"color:".($value>$climit?"green":"red").";\">".($sign&&$value>0?"+":"").number_format($value,2,',',' ').$sym."</td>";
}

function tdvalue_nocoma($value,$sym="",$sign=null,$climit=0){
  if($value=="") return "<td></td>";
  return "<td nowrap class=\"ca_nb\" style=\"color:".($value>$climit?"green":"red").";\">".($sign&&$value>0?"+":"").number_format($value,0,',',' ').$sym."</td>";
}

function tdvalue_nocolor($value,$sym=""){
  if($value==0) return "<td></td>";
  return "<td nowrap style=\"text-align:right;\">".number_format($value,2,',',' ').$sym."</td>";
}



?>