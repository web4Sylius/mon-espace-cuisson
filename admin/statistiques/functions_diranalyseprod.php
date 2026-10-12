<?php



/*
FFFFFFF  OOOOO  NN   NN  CCCCC  TTTTTTT IIIII  OOOOO  NN   NN  SSSSS  
FF      OO   OO NNN  NN CC    C   TTT    III  OO   OO NNN  NN SS      
FFFF    OO   OO NN N NN CC        TTT    III  OO   OO NN N NN  SSSSS  
FF      OO   OO NN  NNN CC    C   TTT    III  OO   OO NN  NNN      SS 
FF       OOOO0  NN   NN  CCCCC    TTT   IIIII  OOOO0  NN   NN  SSSSS                                                                   
*/












function get_mini_value($reference, $concurrents)
{
  global $DB_site2;

  $max = sizeof($concurrents);
  $requete ="";
  for ($i = 0; $i < $max - 1; $i++)
  {
    $requete .= "site='".$concurrents[$i]."' AND artcode='".$reference."' OR ";
  }
  $i++;
  $requete .= "site='".$concurrents[$i]."' AND artcode='".$reference."'";

  $sql = "";
  $cams = "";
  $sql = "SELECT price,site FROM history WHERE ".$requete." ORDER BY price";
  $cams = $DB_site2->query($sql);

  $row = $DB_site2->fetch_array($cams);
  $ret[0] = $row['price'];
  $ret[1] = $row['site'];
  
  return $ret;  
}


function get_value_ttc($reference)
{
  global $DB_site2;

  $sql = "";
  $cams = "";
  $sql = "SELECT prix FROM article WHERE artcode='".$reference."'";
  $cams = $DB_site2->query($sql);

  while ($row = $DB_site2->fetch_array($cams))
  {
    $ret = $row['prix'];
  }
  
  return $ret;
}

function get_value_ht($reference)
{
  global $DB_site2;

  $sql = "";
  $cams = "";
  $sql = "SELECT prixht FROM article WHERE artcode='".$reference."'";
  $cams = $DB_site2->query($sql);

  while ($row = $DB_site2->fetch_array($cams))
  {
    $ret = $row['prixht'];
  }
  
  return $ret;  
}


function get_value_pahtheco($reference)
{
  global $DB_site2;

  $sql = "";
  $cams = "";
  $sql = "SELECT paht,ecopartht FROM article WHERE artcode='".$reference."'";
  $cams = $DB_site2->query($sql);

  while ($row = $DB_site2->fetch_array($cams))
  {
    $ret = $row['paht'];
    $ret += $row['ecopartht'];
  }
  
  return $ret;  
}


function get_stock($artid)
{
  global $DB_site2;

  $compteur = 0;
  $sql = "";
  $cams = "";
  $sql = "SELECT artid, state FROM stock_item WHERE artid='".$artid."' AND state='1'";
  $cams = $DB_site2->query($sql);

  while ($row = $DB_site2->fetch_array($cams))
  {
    $ret = $row['state'];
    $compteur++;
  }
  
  return $compteur;
}












function get_product_fork_qte_n($artid, $type, $du, $au)
{

    global $DB_site2;


    $sql = "";
    $cams = "";
    $sql = "SELECT ff.numero, ef.ligne_facture_id, lf.lignefactureid, am.artid, marque.marqueid, marque.libelle, sum(ef.qte) as quantite, sum(ef.paht) as paht, a.artcode, a.artcode_eurocave FROM facture_fournisseur ff inner join envoi_fournisseur_ligne ef on (ff.numero=ef.facture and FROM_UNIXTIME(ff.date_facture) BETWEEN '".$du."' AND '".$au."') left join lignefacture lf on (ef.ligne_facture_id = lf.lignefactureid) left join article a on (a.artid = lf.artid) left join article_marque am on (lf.artid = am.artid) left join marque on (am.marqueid = marque.marqueid) where ff.fournisseur='".$type."' and am.artid='".$artid."' group by (artid)";
  
    $cams = $DB_site2->query($sql);

      while ($row = $DB_site2->fetch_array($cams))
      {
        $qte = $row['quantite'];
      }
      return $qte;
}


function get_product_year_qte_n($artid, $type, $year, $moins, $TR)
{

    global $DB_site2;

    if ($TR == 1)
    {
      $lim = date("Y-m-d");
      $lim = substract_year($lim, $moins);
      $sql = "";
      $cams = "";
      $sql = "SELECT ff.numero, ef.ligne_facture_id, lf.lignefactureid, am.artid, marque.marqueid, marque.libelle, sum(ef.qte) as quantite, sum(ef.paht) as paht, a.artcode, a.artcode_eurocave FROM facture_fournisseur ff inner join envoi_fournisseur_ligne ef on (ff.numero=ef.facture and FROM_UNIXTIME(ff.date_facture) BETWEEN '".$year."-01-01' AND '".$lim."') left join lignefacture lf on (ef.ligne_facture_id = lf.lignefactureid) left join article a on (a.artid = lf.artid) left join article_marque am on (lf.artid = am.artid) left join marque on (am.marqueid = marque.marqueid) where ff.fournisseur='".$type."' and am.artid='".$artid."' group by (artid)";

        $cams = $DB_site2->query($sql);

          while ($row = $DB_site2->fetch_array($cams))
          {
            $qte = $row['quantite'];
          }
          return $qte;
    }
    else
    {
      $sql = "";
      $cams = "";
      $sql = "SELECT ff.numero, ef.ligne_facture_id, lf.lignefactureid, am.artid, marque.marqueid, marque.libelle, sum(ef.qte) as quantite, sum(ef.paht) as paht, a.artcode, a.artcode_eurocave FROM facture_fournisseur ff inner join envoi_fournisseur_ligne ef on (ff.numero=ef.facture and FROM_UNIXTIME(ff.date_facture) BETWEEN '".$year."-01-01' AND '".$year."-12-31') left join lignefacture lf on (ef.ligne_facture_id = lf.lignefactureid) left join article a on (a.artid = lf.artid) left join article_marque am on (lf.artid = am.artid) left join marque on (am.marqueid = marque.marqueid) where ff.fournisseur='".$type."' and am.artid='".$artid."' group by (artid)";

        $cams = $DB_site2->query($sql);

          while ($row = $DB_site2->fetch_array($cams))
          {
            $qte = $row['quantite'];
          }
          return $qte;
    }
}












function substract_year($date, $moins)
{
  $new_date = substr($date, 0, -6);
  $end_date = substr($date, 4);

  $to_num = intval($new_date);
  $to_num = $to_num - $moins;

  $finale_date = strval($to_num);
  $finale_date .= $end_date;



  return $finale_date;
}











function get_supplier_fork_compteur($type, $du, $au, $tri)
{
    global $DB_site2;

    $order = "ASC";
    if ($tri == "paht" || $tri == "quantite")
      $order = "DESC";

    $sql = "";
    $cams = "";
    $sql = "SELECT ff.numero, ef.ligne_facture_id, lf.lignefactureid, am.artid, marque.marqueid, marque.libelle, sum(ef.qte) as quantite, sum(ef.paht) as paht, a.artcode, a.artcode_eurocave FROM facture_fournisseur ff inner join envoi_fournisseur_ligne ef on (ff.numero=ef.facture and FROM_UNIXTIME(ff.date_facture) BETWEEN '".$du."' AND '".$au."') left join lignefacture lf on (ef.ligne_facture_id = lf.lignefactureid) left join article a on (a.artid = lf.artid) left join article_marque am on (lf.artid = am.artid) left join marque on (am.marqueid = marque.marqueid) where ff.fournisseur='".$type."' group by (artid) ORDER BY ".$tri." ".$order."";
  
    $cams = $DB_site2->query($sql);

      while ($row = $DB_site2->fetch_array($cams))
      {
        $marque[] = $row['libelle'];
        $compteur++;
      }
      return $compteur;
}


function get_supplier_fork_marque($type, $du, $au, $tri)
{
    global $DB_site2;

    $order = "ASC";
    if ($tri == "paht" || $tri == "quantite")
      $order = "DESC";

    $sql = "";
    $cams = "";
    $sql = "SELECT ff.numero, ef.ligne_facture_id, lf.lignefactureid, am.artid, marque.marqueid, marque.libelle, sum(ef.qte) as quantite, sum(ef.paht) as paht, a.artcode, a.artcode_eurocave FROM facture_fournisseur ff inner join envoi_fournisseur_ligne ef on (ff.numero=ef.facture and FROM_UNIXTIME(ff.date_facture) BETWEEN '".$du."' AND '".$au."') left join lignefacture lf on (ef.ligne_facture_id = lf.lignefactureid) left join article a on (a.artid = lf.artid) left join article_marque am on (lf.artid = am.artid) left join marque on (am.marqueid = marque.marqueid) where ff.fournisseur='".$type."' group by (artid) ORDER BY ".$tri." ".$order."";
  
    $cams = $DB_site2->query($sql);

      while ($row = $DB_site2->fetch_array($cams))
      {
        $marque[] = $row['libelle'];
      }

      return $marque;
}


function get_supplier_fork_refmcav($type, $du, $au, $tri)
{
    global $DB_site2;


    $order = "ASC";
    if ($tri == "paht" || $tri == "quantite")
      $order = "DESC";

    $sql = "";
    $cams = "";
    $sql = "SELECT ff.numero, ef.ligne_facture_id, lf.lignefactureid, am.artid, marque.marqueid, marque.libelle, sum(ef.qte) as quantite, sum(ef.paht) as paht, a.artcode, a.artcode_eurocave FROM facture_fournisseur ff inner join envoi_fournisseur_ligne ef on (ff.numero=ef.facture and FROM_UNIXTIME(ff.date_facture) BETWEEN '".$du."' AND '".$au."') left join lignefacture lf on (ef.ligne_facture_id = lf.lignefactureid) left join article a on (a.artid = lf.artid) left join article_marque am on (lf.artid = am.artid) left join marque on (am.marqueid = marque.marqueid) where ff.fournisseur='".$type."' group by (artid) ORDER BY ".$tri." ".$order."";
  
    $cams = $DB_site2->query($sql);

      while ($row = $DB_site2->fetch_array($cams))
      {
        $refmcav[] = $row['artcode'];
      }

      return $refmcav;
}


function get_supplier_fork_reffab($type, $du, $au, $tri)
{
    global $DB_site2;


    $order = "ASC";
    if ($tri == "paht" || $tri == "quantite")
      $order = "DESC";

    $sql = "";
    $cams = "";
    $sql = "SELECT ff.numero, ef.ligne_facture_id, lf.lignefactureid, am.artid, marque.marqueid, marque.libelle, sum(ef.qte) as quantite, sum(ef.paht) as paht, a.artcode, a.artcode_eurocave FROM facture_fournisseur ff inner join envoi_fournisseur_ligne ef on (ff.numero=ef.facture and FROM_UNIXTIME(ff.date_facture) BETWEEN '".$du."' AND '".$au."') left join lignefacture lf on (ef.ligne_facture_id = lf.lignefactureid) left join article a on (a.artid = lf.artid) left join article_marque am on (lf.artid = am.artid) left join marque on (am.marqueid = marque.marqueid) where ff.fournisseur='".$type."' group by (artid) ORDER BY ".$tri." ".$order."";
  
    $cams = $DB_site2->query($sql);

      while ($row = $DB_site2->fetch_array($cams))
      {
        $reffab[] = $row['artcode_eurocave'];
      }

            return $reffab;
}



function get_supplier_fork_qte($type, $du, $au, $tri)
{
    global $DB_site2;

    $order = "ASC";
    if ($tri == "paht" || $tri == "quantite")
      $order = "DESC";

    $sql = "";
    $cams = "";
    $sql = "SELECT ff.numero, ef.ligne_facture_id, lf.lignefactureid, am.artid, marque.marqueid, marque.libelle, sum(ef.qte) as quantite, sum(ef.paht) as paht, a.artcode, a.artcode_eurocave FROM facture_fournisseur ff inner join envoi_fournisseur_ligne ef on (ff.numero=ef.facture and FROM_UNIXTIME(ff.date_facture) BETWEEN '".$du."' AND '".$au."') left join lignefacture lf on (ef.ligne_facture_id = lf.lignefactureid) left join article a on (a.artid = lf.artid) left join article_marque am on (lf.artid = am.artid) left join marque on (am.marqueid = marque.marqueid) where ff.fournisseur='".$type."' group by (artid) ORDER BY ".$tri." ".$order."";
  
    $cams = $DB_site2->query($sql);

      while ($row = $DB_site2->fetch_array($cams))
      {
        $qte[] = $row['quantite'];
      }

      return $qte;
}


function get_supplier_fork_montant($type, $du, $au, $tri)
{
    global $DB_site2;


    $sql = "";
    $cams = "";
    $order = "ASC";
    if ($tri == "paht" || $tri == "quantite")
      $order = "DESC";
    $compteur= 0;
    $sql = "SELECT ff.numero, ef.ligne_facture_id, lf.lignefactureid, am.artid, marque.marqueid, marque.libelle, sum(ef.qte) as quantite, sum(ef.paht*ef.qte) + sum(ef.ecopart) as paht, a.artcode, a.artcode_eurocave FROM facture_fournisseur ff inner join envoi_fournisseur_ligne ef on (ff.numero=ef.facture and FROM_UNIXTIME(ff.date_facture) BETWEEN '".$du."' AND '".$au."') left join lignefacture lf on (ef.ligne_facture_id = lf.lignefactureid) left join article a on (a.artid = lf.artid) left join article_marque am on (lf.artid = am.artid) left join marque on (am.marqueid = marque.marqueid) where ff.fournisseur='".$type."' group by (artid) ORDER BY ".$tri." ".$order."";
  
    $cams = $DB_site2->query($sql);

      while ($row = $DB_site2->fetch_array($cams))
      {
        $montant[] = $row['paht'];
        $lignefactid[] = $row['ligne_facture_id'];
        $compteur++;
      }

    $i = 0;
    while ($i < $compteur)
    {
      $fraisport=$DB_site2->query_first("Select type, paht from envoi_fournisseur_ligne where ligne_facture_id='".$lignefactid[$i]."' AND type='garantie'");
      $fraisport2=$DB_site2->query_first("Select type, paht from envoi_fournisseur_ligne where ligne_facture_id='".$lignefactid[$i]."' AND type='trspt'");
      $fraisport3=$DB_site2->query_first("Select type, paht from envoi_fournisseur_ligne where ligne_facture_id='".$lignefactid[$i]."' AND type='sls'");
      
     // $montant[$i] += $fraisport['paht'] + $fraisport2['paht'] + $fraisport3['paht'];

        $i++;
    }



      return $montant;
}


function get_supplier_fork_artid($type, $du, $au, $tri)
{
    global $DB_site2;


    $order = "ASC";
    if ($tri == "paht" || $tri == "quantite")
      $order = "DESC";

    $sql = "";
    $cams = "";
    $sql = "SELECT ff.numero, ef.ligne_facture_id, lf.lignefactureid, am.artid, marque.marqueid, marque.libelle, sum(ef.qte) as quantite, sum(ef.paht) as paht, a.artcode, a.artcode_eurocave FROM facture_fournisseur ff inner join envoi_fournisseur_ligne ef on (ff.numero=ef.facture and FROM_UNIXTIME(ff.date_facture) BETWEEN '".$du."' AND '".$au."') left join lignefacture lf on (ef.ligne_facture_id = lf.lignefactureid) left join article a on (a.artid = lf.artid) left join article_marque am on (lf.artid = am.artid) left join marque on (am.marqueid = marque.marqueid) where ff.fournisseur='".$type."' group by (artid) ORDER BY ".$tri." ".$order."";
  
    $cams = $DB_site2->query($sql);

      while ($row = $DB_site2->fetch_array($cams))
      {
        $artid[] = $row['artid'];
      }

      return $artid;
}

function get_supplier_fork_numero($type, $du, $au, $tri)
{
    global $DB_site2;


    $order = "ASC";
    if ($tri == "paht" || $tri == "quantite")
      $order = "DESC";

    $sql = "";
    $cams = "";
    $sql = "SELECT ff.numero, ef.ligne_facture_id, lf.lignefactureid, am.artid, marque.marqueid, marque.libelle, sum(ef.qte) as quantite, sum(ef.paht) as paht, a.artcode, a.artcode_eurocave FROM facture_fournisseur ff inner join envoi_fournisseur_ligne ef on (ff.numero=ef.facture and FROM_UNIXTIME(ff.date_facture) BETWEEN '".$du."' AND '".$au."') left join lignefacture lf on (ef.ligne_facture_id = lf.lignefactureid) left join article a on (a.artid = lf.artid) left join article_marque am on (lf.artid = am.artid) left join marque on (am.marqueid = marque.marqueid) where ff.fournisseur='".$type."' group by (artid) ORDER BY ".$tri." ".$order."";
  
    $cams = $DB_site2->query($sql);

      while ($row = $DB_site2->fetch_array($cams))
      {
        $artid[] = $row['numero'];
      }

      return $artid;
}




























function get_supplier_year_compteur($type, $year, $tri)
{
    global $DB_site2;


    $order = "ASC";
    if ($tri == "paht" || $tri == "quantite")
      $order = "DESC";

      $sql = "";
      $cams = "";
      $sql = "SELECT ff.numero, ef.ligne_facture_id, lf.lignefactureid, am.artid, marque.marqueid, marque.libelle, sum(ef.qte) as quantite, sum(ef.paht) as paht, a.artcode, a.artcode_eurocave FROM facture_fournisseur ff inner join envoi_fournisseur_ligne ef on (ff.numero=ef.facture and FROM_UNIXTIME(ff.date_facture) BETWEEN '".$year."-01-01' AND '".$year."-12-31') left join lignefacture lf on (ef.ligne_facture_id = lf.lignefactureid) left join article a on (a.artid = lf.artid) left join article_marque am on (lf.artid = am.artid) left join marque on (am.marqueid = marque.marqueid) where ff.fournisseur='".$type."' group by (artid) ORDER BY ".$tri." ".$order."";
    
      $cams = $DB_site2->query($sql);

        while ($row = $DB_site2->fetch_array($cams))
        {
          $marque[] = $row['libelle'];
          $compteur++;
        }

      return $compteur;
}


function get_supplier_year_marque($type, $year, $tri)
{
    global $DB_site2;

    $order = "ASC";
    if ($tri == "paht" || $tri == "quantite")
      $order = "DESC";

      $sql = "";
      $cams = "";
      $sql = "SELECT ff.numero, ef.ligne_facture_id, lf.lignefactureid, am.artid, marque.marqueid, marque.libelle, sum(ef.qte) as quantite, sum(ef.paht) as paht, a.artcode, a.artcode_eurocave FROM facture_fournisseur ff inner join envoi_fournisseur_ligne ef on (ff.numero=ef.facture and FROM_UNIXTIME(ff.date_facture) BETWEEN '".$year."-01-01' AND '".$year."-12-31') left join lignefacture lf on (ef.ligne_facture_id = lf.lignefactureid) left join article a on (a.artid = lf.artid) left join article_marque am on (lf.artid = am.artid) left join marque on (am.marqueid = marque.marqueid) where ff.fournisseur='".$type."' group by (artid) ORDER BY ".$tri." ".$order."";
    
      $cams = $DB_site2->query($sql);

        while ($row = $DB_site2->fetch_array($cams))
        {
          $marque[] = $row['libelle'];
        }

      return $marque;
}


function get_supplier_year_refmcav($type, $year, $tri)
{
    global $DB_site2;
    $order = "ASC";
    if ($tri == "paht" || $tri == "quantite")
      $order = "DESC";

      $sql = "";
      $cams = "";
      $sql = "SELECT ff.numero, ef.ligne_facture_id, lf.lignefactureid, am.artid, marque.marqueid, marque.libelle, sum(ef.qte) as quantite, sum(ef.paht) as paht, a.artcode, a.artcode_eurocave FROM facture_fournisseur ff inner join envoi_fournisseur_ligne ef on (ff.numero=ef.facture and FROM_UNIXTIME(ff.date_facture) BETWEEN '".$year."-01-01' AND '".$year."-12-31') left join lignefacture lf on (ef.ligne_facture_id = lf.lignefactureid) left join article a on (a.artid = lf.artid) left join article_marque am on (lf.artid = am.artid) left join marque on (am.marqueid = marque.marqueid) where ff.fournisseur='".$type."' group by (artid) ORDER BY ".$tri." ".$order."";
    
      $cams = $DB_site2->query($sql);

        while ($row = $DB_site2->fetch_array($cams))
        {
          $refmcav[] = $row['artcode'];
        }

      return $refmcav;
}


function get_supplier_year_reffab($type, $year, $tri)
{
    global $DB_site2;
    $order = "ASC";
    if ($tri == "paht" || $tri == "quantite")
      $order = "DESC";

      $sql = "";
      $cams = "";
      $sql = "SELECT ff.numero, ef.ligne_facture_id, lf.lignefactureid, am.artid, marque.marqueid, marque.libelle, sum(ef.qte) as quantite, sum(ef.paht) as paht, a.artcode, a.artcode_eurocave FROM facture_fournisseur ff inner join envoi_fournisseur_ligne ef on (ff.numero=ef.facture and FROM_UNIXTIME(ff.date_facture) BETWEEN '".$year."-01-01' AND '".$year."-12-31') left join lignefacture lf on (ef.ligne_facture_id = lf.lignefactureid) left join article a on (a.artid = lf.artid) left join article_marque am on (lf.artid = am.artid) left join marque on (am.marqueid = marque.marqueid) where ff.fournisseur='".$type."' group by (artid) ORDER BY ".$tri." ".$order."";
    
      $cams = $DB_site2->query($sql);

        while ($row = $DB_site2->fetch_array($cams))
        {
          $reffab[] = $row['artcode_eurocave'];
        }

      return $reffab;
}


function get_supplier_year_qte($type, $year, $tri)
{
    global $DB_site2;
    $order = "ASC";
    if ($tri == "paht" || $tri == "quantite")
      $order = "DESC";

      $sql = "";
      $cams = "";
      $sql = "SELECT ff.numero, ef.ligne_facture_id, lf.lignefactureid, am.artid, marque.marqueid, marque.libelle, sum(ef.qte) as quantite, sum(ef.paht) as paht, a.artcode, a.artcode_eurocave FROM facture_fournisseur ff inner join envoi_fournisseur_ligne ef on (ff.numero=ef.facture and FROM_UNIXTIME(ff.date_facture) BETWEEN '".$year."-01-01' AND '".$year."-12-31') left join lignefacture lf on (ef.ligne_facture_id = lf.lignefactureid) left join article a on (a.artid = lf.artid) left join article_marque am on (lf.artid = am.artid) left join marque on (am.marqueid = marque.marqueid) where ff.fournisseur='".$type."' group by (artid) ORDER BY ".$tri." ".$order."";
    
      $cams = $DB_site2->query($sql);

        while ($row = $DB_site2->fetch_array($cams))
        {
          $qte[] = $row['quantite'];
        }

      return $qte;
}


function get_supplier_year_montant($type, $year, $tri)
{
    global $DB_site2;
    $order = "ASC";
    if ($tri == "paht" || $tri == "quantite")
      $order = "DESC";

      $sql = "";
      $cams = "";
      $sql = "SELECT ff.numero, ef.ligne_facture_id, lf.lignefactureid, am.artid, marque.marqueid, marque.libelle, sum(ef.qte) as quantite, sum(ef.paht*ef.qte) + sum(ef.ecopart) as paht, a.artcode, a.artcode_eurocave FROM facture_fournisseur ff inner join envoi_fournisseur_ligne ef on (ff.numero=ef.facture and FROM_UNIXTIME(ff.date_facture) BETWEEN '".$year."-01-01' AND '".$year."-12-31') left join lignefacture lf on (ef.ligne_facture_id = lf.lignefactureid) left join article a on (a.artid = lf.artid) left join article_marque am on (lf.artid = am.artid) left join marque on (am.marqueid = marque.marqueid) where ff.fournisseur='".$type."' group by (artid) ORDER BY ".$tri." ".$order."";
    
    $cams = $DB_site2->query($sql);

      while ($row = $DB_site2->fetch_array($cams))
      {
        $montant[] = $row['paht'];
        $lignefactid[] = $row['ligne_facture_id'];
        $compteur++;
      }

$i = 0;
    while ($i < $compteur)
    {
      $fraisport=$DB_site2->query_first("Select type, paht, ecopart from envoi_fournisseur_ligne where ligne_facture_id='".$lignefactid[$i]."' AND type='garantie'");
      $fraisport2=$DB_site2->query_first("Select type, paht, ecopart from envoi_fournisseur_ligne where ligne_facture_id='".$lignefactid[$i]."' AND type='trspt'");
      $fraisport3=$DB_site2->query_first("Select type, paht, ecopart from envoi_fournisseur_ligne where ligne_facture_id='".$lignefactid[$i]."' AND type='sls'");
      
      //$montant[$i] += $fraisport['paht'] + $fraisport2['paht'] + $fraisport3['paht'];

        $i++;
    }


      return $montant;
}


function get_supplier_year_artid($type, $year, $tri)
{
    global $DB_site2;
    $order = "ASC";
    if ($tri == "paht" || $tri == "quantite")
      $order = "DESC";

      $sql = "";
      $cams = "";
      $sql = "SELECT ff.numero, ef.ligne_facture_id, lf.lignefactureid, am.artid, marque.marqueid, marque.libelle, sum(ef.qte) as quantite, sum(ef.paht) as paht, a.artcode, a.artcode_eurocave FROM facture_fournisseur ff inner join envoi_fournisseur_ligne ef on (ff.numero=ef.facture and FROM_UNIXTIME(ff.date_facture) BETWEEN '".$year."-01-01' AND '".$year."-12-31') left join lignefacture lf on (ef.ligne_facture_id = lf.lignefactureid) left join article a on (a.artid = lf.artid) left join article_marque am on (lf.artid = am.artid) left join marque on (am.marqueid = marque.marqueid) where ff.fournisseur='".$type."' group by (artid) ORDER BY ".$tri." ".$order."";
    
      $cams = $DB_site2->query($sql);

        while ($row = $DB_site2->fetch_array($cams))
        {
          $artid[] = $row['artid'];
        }

      return $artid;
}


function get_supplier_year_numero($type, $year, $tri)
{
    global $DB_site2;
    $order = "ASC";
    if ($tri == "paht" || $tri == "quantite")
      $order = "DESC";

      $sql = "";
      $cams = "";
      $sql = "SELECT ff.numero, ef.ligne_facture_id, lf.lignefactureid, am.artid, marque.marqueid, marque.libelle, sum(ef.qte) as quantite, sum(ef.paht) as paht, a.artcode, a.artcode_eurocave FROM facture_fournisseur ff inner join envoi_fournisseur_ligne ef on (ff.numero=ef.facture and FROM_UNIXTIME(ff.date_facture) BETWEEN '".$year."-01-01' AND '".$year."-12-31') left join lignefacture lf on (ef.ligne_facture_id = lf.lignefactureid) left join article a on (a.artid = lf.artid) left join article_marque am on (lf.artid = am.artid) left join marque on (am.marqueid = marque.marqueid) where ff.fournisseur='".$type."' group by (artid) ORDER BY ".$tri." ".$order."";
    
      $cams = $DB_site2->query($sql);

        while ($row = $DB_site2->fetch_array($cams))
        {
          $artid[] = $row['numero'];
        }

      return $artid;
}











/* Fonction affichage ligne tableau */

function tdvalue($value,$sym="",$sign=null,$climit=0){
	if($value=="") return "<td></td>";
	return "<td class=\"ca_nb\" style=\"color:".($value>$climit?"green":"red").";\">".($sign&&$value>0?"+":"").number_format($value,2,',',' ').$sym."</td>";
}

function tdvalue_nocoma($value,$sym="",$sign=null,$climit=0){
  if($value=="") return "<td></td>";
  return "<td class=\"ca_nb\" style=\"color:".($value>$climit?"green":"red").";\">".($sign&&$value>0?"+":"").number_format($value,0,',',' ').$sym."</td>";
}

function tdvalue_nocolor($value,$sym=""){
  if($value==0) return "<td></td>";
  return "<td>".number_format($value,2,',',' ').$sym."</td>";
}

function tdvalue_nocolornocoma($value,$sym=""){
  if($value==0) return "<td></td>";
  return "<td>".number_format($value,0,',',' ').$sym."</td>";
}

?>