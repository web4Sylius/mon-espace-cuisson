<?php
$rootpath="../../";
include($rootpath."admin/admin_global.php");
include($rootpath."admin/adv/adv.class.php");
include($rootpath."admin/export/xlexport.php");

$DB_site=$DB_site2;

   $ts=1577836860; //01/01/2020
   $tomorrow=mktime(0,0,0,12,31,2025);
  if($order=="") $order="facturid";

  $destinataire=$type="";
  $sql="SELECT ef.facturid,f.datefacture,f.raisonsociale,efl.indate,efl.outdate,ef.destinataire,lf.artcode,efl.expediteur,efl.type,ef.trspt,lf.qte,ef.id,efl.ligne_facture_id,ef.enlev,ef.livr,efl.paht FROM envoi_fournisseur_ligne efl INNER JOIN envoi_fournisseur ef ON (efl.efid=ef.id) INNER JOIN lignefacture lf ON (lf.lignefactureid=efl.ligne_facture_id) INNER JOIN facture f ON (f.facturid=ef.facturid) WHERE ";
  $sql.="(efl.outdate BETWEEN $ts AND $tomorrow)";
  $sql.=" AND efl.type='trspt' ORDER BY ".$order;

  $requete=$DB_site->query($sql);
  if(mysqli_num_rows($requete)==0) return false;
  $cmd=array();
  $names = array("N° Cde","date facture","Transporteur","Poids total","Code postal","Ville","Pays","societe livraison","PA HT","colis");
  $xl=new xlexport("csv");
  $xl->name="transports";
  $xl->addrow($names);

  while($envoi=$DB_site->fetch_array($requete)){
    $article=$DB_site->query_first("SELECT artcode_eurocave,libelleFR,(emballage_poids1+emballage_poids2+emballage_poids3) AS emballage_poids FROM article WHERE artcode='$envoi[artcode]'");
    $adv = $DB_site->query_first("SELECT id FROM adv WHERE efid = '".$envoi['id']."' ");
    $adv = new adv($DB_site,$adv[0]);
    $colis = $adv->colis();
    $colis = count($colis);
    if($colis == 0) $colis =1;
   
    $cmd[$envoi['facturid']]['id']=$envoi['id'];
    $cmd[$envoi['facturid']]['date']= $envoi['datefacture'];
    //$cmd[$envoi['facturid']]['indate']=$envoi['indate'];
    //$cmd[$envoi['facturid']]['outdate']=$envoi['outdate'];
    $cmd[$envoi['facturid']]['poids']+=$envoi['qte']*$article['emballage_poids'];
    $cmd[$envoi['facturid']]['paht']+=$envoi['paht'];
    //$cmd[$envoi['facturid']]['paht']+=$envoi['qte']*$envoi['paht'];
    //$cmd[$envoi['facturid']]['desc'].=$article['libelleFR']."<br>";
    //$cmd[$envoi['facturid']]['qte']+=$envoi['qte'];
    $cmd[$envoi['facturid']]['trspt']=$envoi['destinataire'];
    
    if($envoi['livr']=="0"){
  $facture=$DB_site->query_first("SELECT f.facturid,f.nom,f.prenom,f.codepostal,f.ville,f.paysid,p.libelle FROM facture f INNER JOIN pays p USING (paysid) WHERE facturid=".$envoi['facturid']);
  $cmd[$envoi['facturid']]['dest']=$facture['nom'];
  $cmd[$envoi['facturid']]['ville']=$facture['ville'];
  $cmd[$envoi['facturid']]['cp']=$facture['codepostal'];
  $cmd[$envoi['facturid']]['pays']=$facture['libelle'];
    }elseif(intval($envoi['livr'])>0){
  $fourn=$DB_site->query_first("SELECT * FROM fournisseur f INNER JOIN pays p ON (p.paysid=f.paysid) WHERE f.id=".intval($envoi['livr']));
  $cmd[$envoi['facturid']]['dest']=trim($fourn['societe']." ".$fourn['ville']);
  $cmd[$envoi['facturid']]['ville']=$fourn['ville'];
  $cmd[$envoi['facturid']]['cp']=$fourn['cp'];
  $cmd[$envoi['facturid']]['pays']=$fourn['libelle'];
  $cmd[$envoi['facturid']]['raisonsociale']=$envoi['raisonsociale'];
    }else
  $cmd[$envoi['facturid']]['dest']=$envoi['trspt'];

    if($envoi['enlev']!=0){
      $enlev=$DB_site->query_first("SELECT * FROM fournisseur WHERE id=".$envoi['enlev']);
      $cmd[$envoi['facturid']]['enl']=$enlev['societe']." ".$enlev['ville'];
    }else
      $cmd[$envoi['facturid']]['enl']=$fourn['societe'];

    $cmd[$envoi['facturid']]['colis'] = $colis ;  
  }  

  $ret="N° Cde;Transporteur;Poids total;Code postal;Ville;Pays;PA HT;colis\n";

  foreach($cmd as $facturid => $efldata){
    $data = array($facturid, $efldata['date'], $efldata['trspt'], $efldata['poids']."kg", $efldata['cp'],$efldata['ville'],$efldata['pays'],$efldata['raisonsociale'],$efldata['paht'],$efldata['colis']);
    $xl->addrow($data);
  }

  $xl->export();


?>
