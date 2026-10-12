<?php

include_once("../class/stock.class.php");
include_once("adv/adv.class.php");

/*
if($_SERVER['PHP_SELF']=="/admin/script_adv.php"){
  include("../global.php");
  $DB_site2=$DB_site;
  $mail=true;
}
*/

$types=array("fourn"=>"Fournisseurs","trspt"=>"Transporteurs","sls"=>"Installateur");
$today=mktime(0,0,0,date("m"),date("d"),date("Y"));

//******************************************************************************
//*
function suivi_fourn($ts,$typedate,$order="facturid",$late=null,$days=1,$a_venir=null){
    global $DB_site2,$types;

    $tr=array();
    $tomorrow=$ts+$days*86400;
    switch($typedate){
      case "indate":
      $enddate="enlevement";
      $txttitre="Enlèvement fournisseur";
      break;

      case "outdate":
      $enddate="livraison";
      $txttitre="Livraison fournisseur";
      break;
    }
    if($order=="") $order="facturid";

    $destinataire=$type="";
    $sql="SELECT ef.facturid,efl.indate,efl.outdate,ef.destinataire,lf.artcode,efl.expediteur,efl.type,ef.trspt,efl.qte,efl.imperatif,ef.id,ef.enlevement,efl.ligne_facture_id,efl.id AS eflid, ef.date_relance FROM envoi_fournisseur_ligne efl INNER JOIN envoi_fournisseur ef ON (efl.efid=ef.id) INNER JOIN lignefacture lf ON (lf.lignefactureid=efl.ligne_facture_id) WHERE ";
    if($late==null){
      if($a_venir!==null){
        $sql.="(efl.$typedate >= $ts)";
        $txttitre.=" à partir du ".date("d/m/Y",$ts);
        $tblid="tblfourna_venir";
      }
      else {
        $sql.="(efl.$typedate >= $ts AND efl.$typedate < $tomorrow)";
        $txttitre.=" du ".date("d/m/Y",$ts);
        if($days>1) $txttitre.=" au ".date("d/m/Y",$tomorrow-1);
        $tblid="tblfourn".$ts;
      }
    }else{
      switch($late){
        case "<":
        $sql.=" efl.$typedate $late $ts AND efl.$typedate>0 AND ef.enlevement != '0'";
        $txttitre.=" en retard";
        $tblid="tblfournlate";
        break;

        case ">":
        $sql.=" efl.$typedate $late $ts AND efl.$typedate>0";
        $txttitre.=" futur";
        $tblid="tblfournfutur";
        break;

        case "imp":
        $sql.=" efl.$typedate<".($ts+3*86400)." AND efl.$typedate>0 AND imperatif=1";
        $txttitre.=" impératif";
        $tblid="tblfournimp";
      }
    }
    $sql.=" AND ef.$enddate=0 AND ef.destinataire<>'0' AND efl.type IN ('fourn','stock') ORDER BY ef.destinataire,".$order;

    $requete=$DB_site2->query($sql);

    while($envoi=$DB_site2->fetch_array($requete)){
      $utilisateur=$DB_site2->query_first("SELECT userid,vip FROM utilisateur INNER JOIN facture USING(userid) WHERE facturid=".$envoi['facturid']);
      $vip=usericon($utilisateur['vip'],20);
      $article=$DB_site2->query_first("SELECT artcode_eurocave,libelleFR FROM article WHERE artcode='$envoi[artcode]'");
    /*if($type!=$envoi['type']){
      $type=$envoi['type'];
      $ret.="<tr><td colspan=\"7\" class=\"tbltitle\">$txttitre $types[$type]</td></tr>";
    }*/

    $action="onmouseover=\"toggletool(this,'visible');\" onmouseout=\"toggletool(this,'hidden');\"";
    $cmenu="";
    if($envoi['enlevement']==0)
      $cmenu="<div class=\"menutools\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updefldate);modifdate='in';eflid=$envoi[eflid]\" title=\"Modifier la date\"><img src=\"images/supprimer.gif\" title=\"Supprimer la date\" onclick=\"cal_timestamp=0;modifdate='in';eflid=$envoi[eflid];updefldate();\">".($envoi['indate']>0?"<img src=\"images/coche_verte.svg\" width=\"20\" title=\"Valider l'enlèvement\" onclick=\"calendrier_show(undefined,'',false,validadvdate);modifdate='in';efid=$envoi[id]\">":"")."</div>";
    else
      $cmenu="<img src=\"images/coche_verte.svg\" width=\"20\" title=\"Enlèvement effectué le ".date("d/m/Y",$envoi['enlevement'])."\">";
    $cmenu2="<div class=\"menutools\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updefldate);modifdate='out';eflid=$envoi[eflid]\" title=\"Modifier la date\"><img src=\"images/supprimer.gif\" title=\"Supprimer la date\" onclick=\"cal_timestamp=0;modifdate='out';eflid=$envoi[eflid];updefldate();\">".($envoi['outdate']>0?"<img src=\"images/coche_verte.svg\" width=\"20\" title=\"Valider la livraison\" onclick=\"calendrier_show(undefined,'',false,validadvdate);modifdate='out';efid=$envoi[id]\"><img src=\"images/icon-x.svg\" width=\"20\" title=\"Livraison annulée\" onclick=\"document.location='suivi_adv.php?efid=".$envoi['id']."&validadvdate=-1&modifdate=out&tdate=".$_GET['tdate']."&scrolltop='+document.body.scrollTop\">":"")."</div>";

    $imperatif=($envoi['imperatif']==1?" <small>Impératif</small>":"");
    $tr[$envoi['destinataire']].="<tr onmouseover=\"this.style.backgroundColor='#cccccc';\" onmouseout=\"this.style.backgroundColor='';\">
    <td onmouseover=\"toggletool(this,'visible');\" onmouseout=\"toggletool(this,'hidden');\">$envoi[facturid] $vip<div class=\"popup\">Commande n°$envoi[facturid]".history("commande",$envoi['facturid'])."</div></td>
    <td>$envoi[qte]</td>
    <td title=\"".$article['libelleFR']."\">$article[artcode_eurocave]</td>
    <td".($envoi['indate']<$tomorrow?" style=\"color:red;\"":"")." $action>".$cmenu.($envoi[indate]>0?date("d/m/Y",$envoi[indate]):"")."</td>
    <td".($envoi['outdate']<$tomorrow?" style=\"color:red;\"":"")." $action>".$cmenu2.($envoi['outdate']>0?date("d/m/Y",$envoi['outdate']):"-").$imperatif."</td>
    <td>$envoi[trspt]</td>
    <td><a href=\"facture.php?action=editer&facturid=$envoi[facturid]#adv\" target=\"_blank\">Cde</a></td>
    ".($late=='<'?"<td>".($envoi['date_relance']!=='0'?date("d/m/Y",$envoi['date_relance']):'')."</td>":'')."
    </tr>";
  }


  $stock=new stock($DB_site2);
  $listcmd=$stock->enlevements(null,$ts,$late,$days,$typedate);
  foreach($listcmd as $stock_cmd){
    $stock_cmd->groupitems();
    foreach($stock_cmd->items as $item){
     $action="onmouseover=\"toggletool(this,'visible');\" onmouseout=\"toggletool(this,'hidden');\"";
     $cmenu="<div class=\"menutools\"><img src=\"images/editer.gif\" onclick=\"modifstockdate(".$stock_cmd->id.",".$item->artid.",".$item->date_enlevement.",'in')\" title=\"Modifier la date\"></div>";
     $cmenu2="<div class=\"menutools\"><img src=\"images/editer.gif\" onclick=\"modifstockdate(".$stock_cmd->id.",".$item->artid.",".$item->date_livraison.",'out')\" title=\"Modifier la date\"></div>";

     $tr[$stock_cmd->destinataire].="<tr onmouseover=\"this.style.backgroundColor='#cccccc';\" onmouseout=\"this.style.backgroundColor='';\">
     <td onmouseover=\"toggletool(this,'visible');\" onmouseout=\"toggletool(this,'hidden');\">".$stock_cmd->id."</td>
     <td>".$item->qte."</td>
     <td title=\"".$item->libelle."\">".$item->ref."</td>
     <td".($item->date_enlevement<$tomorrow?" style=\"color:red;\"":"")." $action>".$cmenu.($item->date_enlevement>0?date("d/m/Y",$item->date_enlevement):"")."</td>
     <td".($item->date_livraison<$tomorrow?" style=\"color:red;\"":"")." $action>".$cmenu2.($item->date_livraison>0?date("d/m/Y",$item->date_livraison):"-")."</td>
     <td>".$item->trspt."</td>
     <td><a href=\"stock.php?action=cmd&stock_cmd_id=".$stock_cmd->id."#adv\" target=\"_blank\">Cde</a></td>
     </tr>";
   }
 }

 $ret="<br><table id=\"$tblid\" class=\"tbl\" style=\"background-color:#f7fadf\">
 <thead>
 <tr><td colspan=\"10\" class=\"tbltitle\"><img src=\"images/cave.png\" width=\"25\" align=\"absmiddle\"> $txttitre</td></tr>
 <tr><td>N° Cde</td><td>Qte</td><td>Article</td><td>Enlevement</td><td>Livraison</td><td>Transporteur</td><td></td>".($late=='<'?"<td>Relance faite le</td>":'')."</tr>
 </thead>";

 if(count($tr)>0){
  ksort($tr);
  foreach($tr as $destinataire=>$lines) $ret.="<tr>
    <td style=\"background-color:#cccccc;color:black;\" colspan=\"8\">".$destinataire."</td>
  </tr>".$lines;
}else
$ret.="<tr><td colspan=\"8\" align=\"center\">Pas de commande</td></tr>";

$ret.="</table>";
return $ret;
}

//******************************************************************************
//*
function suivi_trspt($ts,$typedate,$order="facturid",$late=null,$days=1,$a_venir=null){
  global $DB_site2,$types;

  $tomorrow=$ts+($days*86400);
  switch($typedate){
    case "indate":
      $enddate="enlevement";
      $txttitre="Enlèvement transporteur";
      break;

    case "outdate":
      $enddate="livraison";
      $txttitre="Livraison transporteur";
      break;
  }

  if($order=="") $order="facturid";

  $destinataire=$type="";
  $sql="SELECT ef.facturid,efl.indate,efl.outdate,ef.destinataire,lf.artcode,efl.expediteur,efl.type,ef.livr,ef.trspt,efl.qte,ef.id,efl.ligne_facture_id,imperatif,ef.date_relance FROM envoi_fournisseur_ligne efl INNER JOIN envoi_fournisseur ef ON (efl.efid=ef.id) INNER JOIN lignefacture lf ON (lf.lignefactureid=efl.ligne_facture_id) WHERE ";
  if($late==null){
    if($a_venir!==null){
      $sql.="(efl.$typedate >= $ts)";
      $txttitre.=" à partir du ".date("d/m/Y",$ts);
      $tblid="tbltrspta_venir";
    }
    else{
      $sql.="(efl.$typedate >= $ts AND efl.$typedate < $tomorrow)";
      $txttitre.=" du ".date("d/m/Y",$ts);
      if($days>1) $txttitre.=" au ".date("d/m/Y",$tomorrow-1);
      $tblid="tbltrspt".$ts;
    }
  }else{
    switch($late){
      case "<":
        $sql.=" efl.$typedate $late $ts AND efl.$typedate>0 AND ef.enlevement != '0'";
        $txttitre.=" en retard";
        $tblid="tbltrsptlate";
        break;

      case ">":
        $sql.=" efl.$typedate $late $ts AND efl.$typedate>0";
        $txttitre.=" futur";
        $tblid="tbltrsptfutur";
        break;

      case "imp":
        $sql.=" efl.$typedate<".($ts+3*86400)." AND efl.$typedate>0 AND imperatif=1";
        $txttitre.=" impératif";
        $tblid="tbltrsptimp";
        break;
    }
  }
  $sql.=" AND ef.$enddate=0 AND ef.destinataire<>'0' AND efl.type='trspt' ORDER BY ef.destinataire,".$order;

  $requete=$DB_site2->query($sql);
  $cmd=array();

  while($envoi=$DB_site2->fetch_array($requete)){
    $article=$DB_site2->query_first("SELECT artcode_eurocave,libelleFR,(emballage_poids1+emballage_poids2+emballage_poids3) AS emballage_poids FROM article WHERE artcode='$envoi[artcode]'");
    $enlev=$DB_site2->query_first("SELECT * FROM envoi_fournisseur_ligne efl INNER JOIN envoi_fournisseur ef ON (ef.id=efl.efid) WHERE efl.ligne_facture_id=".$envoi['ligne_facture_id']." AND efl.type='fourn' ORDER BY efl.date DESC LIMIT 1");
    if($typedate=="outdate" || $enlev['livraison']==0){
      $cmd[$envoi['destinataire']][$envoi['facturid']]['id']=$envoi['id'];
      $cmd[$envoi['destinataire']][$envoi['facturid']]['indate']=$envoi['indate'];
      $cmd[$envoi['destinataire']][$envoi['facturid']]['outdate']=$envoi['outdate'];
      $cmd[$envoi['destinataire']][$envoi['facturid']]['poids']+=$article['emballage_poids'];
      $cmd[$envoi['destinataire']][$envoi['facturid']]['qte']+=$envoi['qte'];
      $cmd[$envoi['destinataire']][$envoi['facturid']]['enl']=$enlev['destinataire'];
      $cmd[$envoi['destinataire']][$envoi['facturid']]['imperatif']=$envoi['imperatif'];
      $cmd[$envoi['destinataire']][$envoi['facturid']]['date_relance']=$envoi['date_relance'];
      if($envoi['livr']==0){
       $facture=$DB_site2->query_first("SELECT f.facturid,f.nom,f.prenom,f.codepostal,f.paysid,p.libelle FROM facture f INNER JOIN pays p USING (paysid) WHERE facturid=".$envoi['facturid']);
       $cmd[$envoi['destinataire']][$envoi['facturid']]['dest']=$facture['prenom']." ".$facture['nom']."<br>".$envoi['trspt'];
       $cmd[$envoi['destinataire']][$envoi['facturid']]['pays']=$facture['libelle'];
       if($facture['paysid']==57 || $facture['paysid']==181) $cmd[$envoi['destinataire']][$envoi['facturid']]['cp']=substr($facture['codepostal'],0,2);
     }else{
       $fourn=$DB_site2->query_first("SELECT societe,ville,cp,libelle FROM fournisseur INNER JOIN pays USING(paysid) WHERE id=".intval($envoi['livr']));
       $cmd[$envoi['destinataire']][$envoi['facturid']]['dest']=$fourn['societe']." ".$fourn['ville']."<br>".$envoi['trspt'];
       $cmd[$envoi['destinataire']][$envoi['facturid']]['cp']=$fourn['cp'];
       $cmd[$envoi['destinataire']][$envoi['facturid']]['pays']=$fourn['libelle'];
     }
   }
 }  

 ksort($cmd);
 foreach($cmd as $trspt=>$factures){    
  foreach($factures as $facturid=>$efldata){
   $utilisateur=$DB_site2->query_first("SELECT userid,vip FROM utilisateur INNER JOIN facture USING(userid) WHERE facturid=".$facturid);
   $vip=usericon($utilisateur['vip'],20);

   $action="onmouseover=\"toggletool(this,'visible');\" onmouseout=\"toggletool(this,'hidden');\"";
   $cmenu="<div class=\"menutools\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updefdate);modifdate='in';efid=$efldata[id]\" title=\"Modifier la date\"><img src=\"images/supprimer.gif\" title=\"Supprimer la date\" onclick=\"cal_timestamp=0;modifdate='in';eflid=$efldata[id];updefdate();\"></div>";
   $cmenu2="<div class=\"menutools\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updefdate);modifdate='out';efid=$efldata[id]\" title=\"Modifier la date\"><img src=\"images/supprimer.gif\" title=\"Supprimer la date\" onclick=\"cal_timestamp=0;modifdate='out';eflid=$efldata[id];updefdate();\">".($efldata['outdate']>0?"<img src=\"images/coche_verte.svg\" width=\"20\" title=\"Valider la livraison\" onclick=\"calendrier_show(undefined,'',false,validadvdate);modifdate='out';efid=$efldata[id]\"><img src=\"images/icon-x.svg\" width=\"20\" title=\"Livraison annulée\" onclick=\"document.location='suivi_adv.php?efid=".$efldata['id']."&validadvdate=-1&modifdate=out&tdate=".$_GET['tdate']."&scrolltop='+document.body.scrollTop\">":"")."</div>";

   $imperatif=($efldata['imperatif']==1?" <small>Impératif</small>":"");
   $tr[$trspt].="<tr onmouseover=\"this.style.backgroundColor='#cccccc';\" onmouseout=\"this.style.backgroundColor='';\">
   <td onmouseover=\"toggletool(this,'visible');\" onmouseout=\"toggletool(this,'hidden');\">".$facturid.$vip."<div class=\"popup\">Commande n°$facturid".history("commande",$facturid)."</div></td>
   <td>".$efldata['qte']."</td>
   <td>".$efldata['poids']."</td>
   <td>".$efldata['enl']."</td>
   <td".($efldata['indate']<$tomorrow?" style=\"color:red;\"":"")." $action>".$cmenu.($envoi[indate]>0?date("d/m/Y",$efldata['indate']):"")."</td>
   <td>".$efldata['dest']."</td>
   <td".($efldata['outdate']<$tomorrow?" style=\"color:red;\"":"")." $action>".$cmenu2.($efldata['outdate']!=0?date("d/m/Y",$efldata['outdate']):"").$imperatif."</td>
   <td>".$efldata['cp']."</td>
   <td>".$efldata['pays']."</td>
   <td><a href=\"facture.php?action=editer&facturid=$facturid#adv\" target=\"_blank\">Cde</a></td>
   ".($late=='<'?"<td>".($efldata['date_relance']!=='0'?date("d/m/Y",$efldata['date_relance']):'')."</td>":'')."
   </tr>";
 }
}

$stock=new stock($DB_site2);
$listcmd=$stock->enlevements(null,$ts,$late,$days,$typedate);
foreach($listcmd as $stock_cmd){
  $stock_cmd->groupitems();
  $enlev=$DB_site2->query_first("SELECT * FROM fournisseur INNER JOIN pays USING(paysid) WHERE id=".$stock_cmd->enlevement);
  $livr=$DB_site2->query_first("SELECT * FROM fournisseur INNER JOIN pays USING(paysid) WHERE id=".$stock_cmd->livraison);
  foreach($stock_cmd->items as $item){
   $action="onmouseover=\"toggletool(this,'visible');\" onmouseout=\"toggletool(this,'hidden');\"";
   $cmenu="<div class=\"menutools\"><img src=\"images/editer.gif\" onclick=\"modifstockdate(".$stock_cmd->id.",".$item->artid.",".$item->date_enlevement.",'in')\" title=\"Modifier la date\"></div>";
   $cmenu2="<div class=\"menutools\"><img src=\"images/editer.gif\" onclick=\"modifstockdate(".$stock_cmd->id.",".$item->artid.",".$item->date_livraison.",'out')\" title=\"Modifier la date\"></div>";

   if($item->trspt!=""){
     $tr[$item->trspt].="<tr onmouseover=\"this.style.backgroundColor='#cccccc';\" onmouseout=\"this.style.backgroundColor='';\">
     <td onmouseover=\"toggletool(this,'visible');\" onmouseout=\"toggletool(this,'hidden');\">".$stock_cmd->id."</td>
     <td>".$item->qte."</td>
     <td>".($item->qte*$item->poids_brut)."</td>
     <td>".$enlev['societe']."</td>
     <td".($item->date_enlevement<$tomorrow?" style=\"color:red;\"":"")." $action>".$cmenu.($item->date_enlevement>0?date("d/m/Y",$item->date_enlevement):"")."</td>
     <td>".$livr['societe']." ".$livr['prenom']." ".$livr['nom']."</td>
     <td".($item->date_livraison<$tomorrow?" style=\"color:red;\"":"")." $action>".$cmenu2.($item->date_livraison>0?date("d/m/Y",$item->date_livraison):"-")."</td>
     <td>".$livr['cp']."</td>
     <td>".$livr['libelle']."</td>
     <td><a href=\"stock.php?action=cmd&stock_cmd_id=".$stock_cmd->id."#adv\" target=\"_blank\">Cde</a></td>
     </tr>";
   }
 }
}

$ret="<br><table id=\"$tblid\" class=\"tbl\" style=\"background-color:#EEFAFF;\">
<tr><td colspan=\"10\" class=\"tbltitle\"><img src=\"images/livraison.png\" width=\"25\" align=\"absmiddle\"> $txttitre</td></tr>
<tr><td>N° Cde</td><td>Qte</td><td>Poids total</td><td>Départ de</td><td>Enlèvement</td><td>Pour livraison</td><td>Livraison</td><td>CP</td><td>Pays</td><td></td>".($late=='<'?"<td>Relance faite le</td>":'')."</tr>
";

if(count($tr)>0){
  foreach($tr as $trspt=>$lines)
    $ret.="<tr><td colspan=\"10\" style=\"background-color:#cccccc;\">$trspt</td></tr>".$lines;
}else
$ret.="<tr><td colspan=\"10\" align=\"center\" height=\"30\">Pas de commande</td></tr>";

$ret.="</table>";
return $ret;

}


//******************************************************************************
//*
function suivi_sls($ts,$typedate,$order="facturid",$late=null,$days=1,$a_venir=null){
  global $DB_site2,$types;

  $tomorrow=$ts+($days*86400);
  switch($typedate){
    case "indate":
    $enddate="enlevement";
    $txttitre="Enlèvement installateur";
    break;

    case "outdate":
    $enddate="livraison";
    $txttitre="Livraison installateur";
    break;
  }
  if($order=="") $order="facturid";

  $destinataire=$type="";
  $sql="SELECT ef.facturid,efl.indate,efl.outdate,ef.destinataire,lf.artcode,efl.expediteur,efl.type,ef.trspt,efl.qte,ef.id,efl.ligne_facture_id,imperatif,ef.date_relance FROM envoi_fournisseur_ligne efl INNER JOIN envoi_fournisseur ef ON (efl.efid=ef.id) INNER JOIN lignefacture lf ON (lf.lignefactureid=efl.ligne_facture_id) WHERE ";
  if($late==null){
    if($a_venir!==null){
      $sql.="(efl.$typedate >= $ts)";
      $txttitre.=" à partir du ".date("d/m/Y",$ts);
      $tblid="tblslsa_venir";
    }
    else{
      $sql.="(efl.$typedate >= $ts AND efl.$typedate < $tomorrow)";
      $txttitre.=" du ".date("d/m/Y",$ts);
      if($days>1) $txttitre.=" au ".date("d/m/Y",$tomorrow-1);
      $tblid="tblsls".$ts;
    }
  }else{
    switch($late){
      case "<":
      $sql.=" efl.$typedate $late $ts AND efl.$typedate>0 AND ef.enlevement != '0'";
      $txttitre.=" en retard";
      $tblid="tblslslate";
      break;

      case ">":
      $sql.=" efl.$typedate $late $ts AND efl.$typedate>0";
      $txttitre.=" futur";
      $tblid="tblslsfutur";
      break;

      case "imp":
      $sql.=" efl.$typedate<".($ts+3*86400)." AND efl.$typedate>0 AND imperatif=1";
      $txttitre.=" impératif";
      $tblid="tblslsimp";
      break;
    }
  }
  $sql.=" AND ef.$enddate=0 AND ef.destinataire<>'0' AND efl.type='sls' ORDER BY ef.destinataire,".$order;

  $requete=$DB_site2->query($sql);
  $cmd=array();

  while($envoi=$DB_site2->fetch_array($requete)){
    $article=$DB_site2->query_first("SELECT artcode_eurocave,libelleFR,(emballage_poids1+emballage_poids2+emballage_poids3) AS emballage_poids FROM article WHERE artcode='$envoi[artcode]'");
    $enlev=$DB_site2->query_first("SELECT * FROM envoi_fournisseur_ligne efl INNER JOIN envoi_fournisseur ef ON (ef.id=efl.efid) WHERE efl.ligne_facture_id=".$envoi['ligne_facture_id']." AND efl.type='trspt' ORDER BY efl.date DESC LIMIT 1");
    
    $cmd[$envoi['destinataire']][$envoi['facturid']]['id']=$envoi['id'];
    $cmd[$envoi['destinataire']][$envoi['facturid']]['indate']=$envoi['indate'];
    $cmd[$envoi['destinataire']][$envoi['facturid']]['outdate']=$envoi['outdate'];
    $cmd[$envoi['destinataire']][$envoi['facturid']]['poids']+=$article['emballage_poids'];
    $cmd[$envoi['destinataire']][$envoi['facturid']]['qte']+=$envoi['qte'];
    $cmd[$envoi['destinataire']][$envoi['facturid']]['enl']=$enlev['destinataire'];
    $cmd[$envoi['destinataire']][$envoi['facturid']]['imperatif']=$envoi['imperatif'];
    $cmd[$envoi['destinataire']][$envoi['facturid']]['date_relance']=$envoi['date_relance'];
  }  

   $ret="<br><table id=\"$tblid\" class=\"tbl\" style=\"background-color:#ffdbdb\">
 <thead>
 <tr><td colspan=\"10\" class=\"tbltitle\"><img src=\"images/carton.png\" width=\"25\" align=\"absmiddle\"> $txttitre</td></tr>
 <tr><td>N° Cde</td><td>Qte</td><td>Poids total</td><td>Départ de</td><td>Enlèvement</td><td>Pour livraison</td><td>Livraison</td><td>CP</td><td>Pays</td><td></td>".($late=='<'?"<td>Relance faite le</td>":'')."</tr>
 </thead>";

  ksort($cmd);
  foreach($cmd as $trspt=>$factures){
    $ret.="<tr><td colspan=\"10\" style=\"background-color:#cccccc;\">$trspt</td></tr>";
    
    foreach($factures as $facturid=>$efldata){
     $facture=$DB_site2->query_first("SELECT f.facturid,f.nom,f.prenom,f.codepostal,f.paysid,p.libelle,u.vip FROM facture f INNER JOIN utilisateur u USING(userid) INNER JOIN pays p ON(p.paysid=f.paysid) WHERE facturid=".$facturid);
     $vip=usericon($facture['vip'],20);

     $cp="";
     if($facture['paysid']==57 || $facture['paysid']==181) $cp=substr($facture['codepostal'],0,2);

     $action="onmouseover=\"toggletool(this,'visible');\" onmouseout=\"toggletool(this,'hidden');\"";
     $cmenu="<div class=\"menutools\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updefdate);modifdate='in';efid=$efldata[id]\" title=\"modifier\"><img src=\"images/supprimer.gif\" title=\"supprimer\" onclick=\"cal_timestamp=0;modifdate='in';efid=$efldata[id];updefdate();\"></div>";
     $cmenu2="<div class=\"menutools\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updefdate);modifdate='out';efid=$efldata[id]\" title=\"modifier\"><img src=\"images/supprimer.gif\" title=\"supprimer\" onclick=\"cal_timestamp=0;modifdate='out';efid=$efldata[id];updefdate();\">".($efldata['outdate']>0?"<img src=\"images/coche_verte.svg\" width=\"20\" title=\"Valider la livraison\" onclick=\"calendrier_show(undefined,'',false,validadvdate);modifdate='out';efid=$efldata[id]\"><img src=\"images/icon-x.svg\" width=\"20\" title=\"Livraison annulée\" onclick=\"document.location='suivi_adv.php?efid=".$efldata['id']."&validadvdate=-1&modifdate=out&tdate=".$_GET['tdate']."&scrolltop='+document.body.scrollTop\">":"")."</div>";

     $imperatif=($efldata['imperatif']==1?" <small>Impératif</small>":"");
     $ret.="<tr onmouseover=\"this.style.backgroundColor='#cccccc';\" onmouseout=\"this.style.backgroundColor='';\">
     <td onmouseover=\"document.getElementById('div$efldata[id]').style.visibility='visible';\" onmouseout=\"document.getElementById('div$efldata[id]').style.visibility='hidden';\">".$facturid.$vip."<div id=\"div$efldata[id]\" class=\"popup\">Commande n°$facturid".history("commande",$facturid)."</div></td>
     <td>".$efldata['qte']."</td>
     <td>".$efldata['poids']."</td>
     <td>".$efldata['enl']."</td>
     <td".($efldata['indate']<$tomorrow?" style=\"color:red;\"":"")." $action>".$cmenu.($envoi[indate]>0?date("d/m/Y",$efldata['indate']):"")."</td>
     <td>".$facture['prenom']." ".$facture['nom']."</td>
     <td".($efldata['outdate']<$tomorrow?" style=\"color:red;\"":"")." $action>".$cmenu2.($efldata['outdate']!=0?date("d/m/Y",$efldata['outdate']):"").$imperatif."</td>
     <td>$cp</td>
     <td>".$facture['libelle']."</td>
     <td><a href=\"facture.php?action=editer&facturid=$facturid#adv\" target=\"_blank\">Cde</a></td>
     ".($late=='<'?"<td>".($efldata['date_relance']!=='0'?date("d/m/Y",$efldata['date_relance']):'')."</td>":'')."
     </tr>";
   }
 }

 if($DB_site2->num_rows($requete)==0) $ret.="<tr><td colspan=\"10\" align=\"center\" height=\"30\">Pas de commande</td></tr>";

 $ret.="</table>";
 return $ret;

}


//******************************************************************************
//*
function nocmd($ts,$order){
  global $DB_site2;
  global $template;

  if($order==null) $order="cmddate";
  $stock=new stock($DB_site2);
  $adv=new adv($DB_site2);
  $today=mktime(0,0,0);
  $today2=mktime(23,59,59);

  // TABLE FOURNISSEUR
  $a=array();
  $req=$DB_site2->query("SELECT lf.factureid,lf.qte,lf.artid,a.artcode,a.artcode_eurocave,f.userid,lf.lignefactureid,a.libelleFR,a.delailivraison,a.date_appro,a.ecopartht,lf.paht,f.datelivraison,f.changelivraison,m.libelle AS marque,f.provenance,f.provenanceid,f.site_code 
    FROM lignefacture lf 
    INNER JOIN facture f ON (f.facturid=lf.factureid) 
    INNER JOIN article a ON (a.artcode=lf.artcode) 
    INNER JOIN article_marque am ON(am.artid=a.artid) 
    INNER JOIN marque m USING(marqueid) 
    WHERE (SELECT COUNT(ligne_facture_id) FROM envoi_fournisseur_ligne WHERE ligne_facture_id=lf.lignefactureid AND type IN ('fourn','stock'))=0 
    AND f.valide IN (1,5,7,8)
    AND f.datefacture >= '2022-12-01' 
    GROUP BY lf.lignefactureid 
    ORDER BY lf.factureid ASC");

  if($DB_site2->num_rows($req)>0){
    // Entete liste "Articles non commandés au fournisseur"
    $txt.="<table id=\"tblfourn\" class=\"tbl\" style=\"background-color:#f7fadf\">
    <tr>
      <td colspan=\"14\" class=\"tbltitlebis\"><a name=\"fourncmd\"></a>ARTICLES NON COMMANDÉS AU FOURNISSEUR</td>
    </tr>
    <tr class=\"tbltitle\">
      <td rowspan=\"2\">N° client</td>
      <td rowspan=\"2\">Site</td>
      <td rowspan=\"2\">N° facture</td>
      <td rowspan=\"2\">SAV</td>
      <td rowspan=\"2\">Qte</td>
      <td colspan=\"7\" >Article<div id=\"dispodest\"></div><button onclick=\"maildispo();\" style=\"float:right;\">Vérifier disponibilité</button></td>
      <td rowspan=\"2\">Date pour<br>passer cmd</td>
      <td rowspan=\"2\">Date souhaitée<br>enlèvement</td>
      <td rowspan=\"2\">Date souhaitée<br>livraison fournisseur</td>
      <td rowspan=\"2\">Date livraison<br>annoncée au client</td>
      <td rowspan=\"2\">Envoi automatique</td>
      <td rowspan=\"2\"></td>
    </tr>
    <tr class=\"tbltitle\"><td>Ref. Acilyon</td><td>Ref. fabricant</td><td>Marque</td><td>Dispo.</td><td>Stock/cde</td><td>Date réappro</td><td>Délai (j)</td></tr>";

    // Contenu de la liste
    while($nocmd=$DB_site2->fetch_array($req)){
      $r=$DB_site2->query("SELECT * FROM calendrier WHERE type='fourncmd' AND objid=".$nocmd['lignefactureid']);
      if($DB_site2->num_rows($r)==0) $r=$DB_site2->query("SELECT * FROM calendrier WHERE type='stockcmd' AND objid=".$nocmd['lignefactureid']);
      $calendrier=$DB_site2->fetch_array($r);
      $datecmd=$fcmd=$cmenu=$dateappro="";
      $trtype="unk";

      $cmenu="<div class=\"menutools\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);modifdate='reappro';calid=0;type='fourncmd';objid=".$nocmd['lignefactureid'].";\"></div>";
      $cmenu2="<div class=\"menutools\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);modifdate='date';calid=0;type='fourncmd';objid=".$nocmd['lignefactureid'].";\"></div>";
      $cmenu3="<div class=\"menutools\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);modifdate='enlevement';calid=0;type='fourncmd';objid=".$nocmd['lignefactureid'].";\"></div>";
      $cmenu4="<div class=\"menutools\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);modifdate='livraison';calid=0;type='fourncmd';objid=".$nocmd['lignefactureid'].";\"></div>";
      $action="onmouseover=\"toggletool(this,'visible');\" onmouseout=\"toggletool(this,'hidden');\"";
      $coche="";

      if($nocmd['date_appro']!=0) 
        $dateappro=date("d/m/Y",$nocmd['date_appro']);

      if($calendrier!=false){
        if($calendrier['date']!=0){
          $datecmd=date("d/m/Y",$calendrier['date']);
          if($calendrier['date']<$today) 
            $trtype="late";
          elseif($calendrier['date']>=$today && $calendrier['date']<=$today2) 
            $trtype="today";
          elseif($calendrier['date']>$today2) 
            $trtype="futur";
        }

        $fcmd=$calendrier['txt']; 
        $nocmd['cmddate']=$calendrier['date'];
        $nocmd['fourn']=$calendrier['txt'];

        $cmenu="<div class=\"menutools\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);modifdate='reappro';calid=$calendrier[id];\" title=\"modifier\"><img src=\"images/supprimer.gif\" title=\"supprimer\" onclick=\"cal_timestamp=0;modifdate='reappro';calid=$calendrier[id];updadvdate();\"></div>";
        $cmenu2="<div class=\"menutools\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);modifdate='date';calid=$calendrier[id];\" title=\"modifier\"><img src=\"images/supprimer.gif\" title=\"supprimer\" onclick=\"cal_timestamp=0;modifdate='date';calid=$calendrier[id];updadvdate();\"></div>";
        $cmenu3="<div class=\"menutools\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);modifdate='enlevement';calid=$calendrier[id];\" title=\"modifier\"><img src=\"images/supprimer.gif\" title=\"supprimer\" onclick=\"cal_timestamp=0;modifdate='enlevement';calid=$calendrier[id];updadvdate();\"></div>";
        $cmenu4="<div class=\"menutools\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);modifdate='livraison';calid=$calendrier[id];\" title=\"modifier\"><img src=\"images/supprimer.gif\" title=\"supprimer\" onclick=\"cal_timestamp=0;modifdate='livraison';calid=$calendrier[id];updadvdate();\"></div>";

        if($calendrier['reappro']!=0) $dateappro=date("d/m/Y",$calendrier['reappro']);
        if($calendrier['dispo']==1) $coche=" <img src=\"images/coche_verte.svg\" width=\"16\">";
      }

      $caracts=$DB_site2->query("SELECT * FROM lignefacturecaracteristique INNER JOIN caracteristiquevaleur cv USING(caractvalid) WHERE caractid<>3 AND lignefactureid=".$nocmd['lignefactureid']);
      while($caract=$DB_site2->fetch_array($caracts)) $nocmd['libelleFR'].="<br>".$caract['libelle_caract']." : ".$caract['libelleFR'];
      $items=$stock->search($nocmd['artid']);
      $cdeitems=$stock->search($nocmd['artid'],null,null,0);
      $stocklivrdate=array();
      $stocklivr="";
      foreach($cdeitems as $cdeitem) $stocklivrdate[$cdeitem->date_livraison]=date("d/m/Y",$cdeitem->date_livraison);
      if($stocklivrdate!=array()) $stocklivr="<br>".implode("<br>",$stocklivrdate);

      $lot=$DB_site2->query_first("SELECT COUNT(*) FROM article_lot WHERE lotid=".$nocmd['artid']);
      $sav="";
      if($nocmd['provenance']=="sav" || $nocmd['provenance']=="avoir") $sav="<a href=\"facture.php?action=editer&facturid=$nocmd[provenanceid]\" target=\"_blank\">".$nocmd['provenanceid']."</a>";

      $linestyle="";
      $advlist=$adv->listadv($nocmd['factureid'],"fourn",$nocmd['lignefactureid']);
      $advtxt="";
      if(count($advlist)>0){
        $adv1=$advlist[0];
        $advurl="facture.php?action=editer&facturid=".$nocmd['factureid']."&advid=".$adv1->id."&type=fourn#adv";
        $advtxt="<a href=\"$advurl\" target=\"_blank\">Non programmé</a>";
        if($adv1->envoi>0) $advtxt="<a href=\"$advurl\" target=\"_blank\">".date("d/m/Y",$adv1->envoi)."</a>";
      }

      $utilisateur=$DB_site2->query_first("SELECT userid,vip,langue FROM utilisateur WHERE userid=".$nocmd['userid']);
      $vip=usericon($utilisateur['vip'],20);
      $urlcode=$template->urlcode($nocmd['site_code'],$utilisateur['langue']);

      $artfourn=array();
      $reqfourn=$DB_site2->query("SELECT fournisseur FROM article_fournisseur WHERE artid='".$nocmd['artid']."'");
      while($fourn=$DB_site2->fetch_array($reqfourn)){
        $artfourn[]=addslashes($fourn['fournisseur']);
      }

      $cmd_deja_affich[] = $nocmd['factureid'];

      if(!isset($a[$nocmd[$order]])) $linestyle=" style=\"border-top:solid thin #888;\"";
      if($ts==null || ($ts<=$calendrier['date'] && ($ts+86400)>$calendrier['date'])){ $a[$nocmd[$order]].= "
      <tr name=\"".$trtype."[]\" onmouseover=\"this.style.backgroundColor='#cccccc';\" onmouseout=\"this.style.backgroundColor='';\"$linestyle>
        <td onmouseover=\"toggletool(this,'visible');\" onmouseout=\"toggletool(this,'hidden');\">".$nocmd['userid'].$vip."<div class=\"popup\">Client n°$nocmd[userid]".history("client",$nocmd['userid'])."</div></td>
        <td>".$urlcode."</td>
        <td onmouseover=\"toggletool(this,'visible');\" onmouseout=\"toggletool(this,'hidden');\"><a href=\"facture.php?action=editer&facturid=$nocmd[factureid]#adv\" target=\"_blank\">".$nocmd['factureid']."</a><div class=\"popup\">Commande n°$nocmd[factureid]".history("commande",$nocmd['factureid'])."</div></td>
        <td>$sav</td>
        <td>".$nocmd['qte']."</td>
       <td>".$nocmd['artcode']."</td>
        <td title=\"".$nocmd['libelleFR']."\">".$nocmd['artcode_eurocave']."</td>
        <td>".$nocmd['marque']."</td>  
        <td title=\"".$nocmd['libelleFR']."\" align=\"center\"><input type=\"checkbox\" onclick=\"todispo(this.checked,$nocmd[factureid],$nocmd[lignefactureid],'$nocmd[artcode_eurocave]','$nocmd[qte]','$nocmd[paht]".($nocmd['ecopartht']>0?"+".$nocmd['ecopartht']:"")."','".$calendrier['enlevement']."','".$calendrier['livraison']."','".$dateappro."','".str_replace("'","&#8217;",$nocmd[libelleFR])."',new Array('".implode("','",$artfourn)."'));\"></td>
        <td>".count($items)." / ".count($cdeitems).$stocklivr.$coche."</td>
        <td $action style=\"".(substr($dateappro,0,10)==date("d/m/Y")?"color:red;":"")."\" nowrap>".$cmenu.$dateappro."</td>
        <td>".$nocmd['delailivraison']."</td>
        <td $action style=\"".($datecmd<=date("d/m/Y")?"color:red;":"")."\">".$cmenu2.$datecmd."</td>
        <td $action>".$cmenu3.($calendrier['enlevement']!=0?date("d/m/Y",$calendrier['enlevement']):"")."</td>
        <td $action>".$cmenu4.($calendrier['livraison']!=0?date("d/m/Y",$calendrier['livraison']):"")."</td>
        <td>".($nocmd['datelivraison']!=0?$nocmd['changelivraison']." ".date("d/m/Y",$nocmd['datelivraison']):"?")."</td>
        <td name=\"progcmd\">".$advtxt."</td>
        <td>".$fcmd."</td>	
      </tr>";
    }
    }

    $reservestock=$stock->search(null,null,"facture%",2);
    foreach($reservestock as $item){
      $item->destination();
      //on vérifie que la commande n'est pas déjà affichée, sinon ça fera doublon dans le tableau:
      if(!in_array($item->facturid,$cmd_deja_affich)){
        if(!isset($reserves[$item->facturid][$item->artid]))
          $reserves[$item->facturid][$item->artid]=$item;
        else
          $reserves[$item->facturid][$item->artid]->qte++;
      }
    }

    foreach($reserves as $facturid=>$artids){
      $facture=new facture($DB_site2,$facturid);

      if(in_array($facture->valide,array(1,5,7,8))){
var_dump($facture->valide);
var_dump($facturid);
        $vip=usericon($facture->utilisateur->vip,20);

        foreach($artids as $artid=>$item){
          $qtecmd=0;
          $lf=false;

          $req=$DB_site2->query("SELECT lignefactureid,qte FROM lignefacture WHERE artid=".$artid." AND factureid=".$facturid);
          while($ligne=$DB_site2->fetch_array($req)){
            $efl=$DB_site2->query_first("SELECT efl.qte FROM envoi_fournisseur_ligne efl WHERE ligne_facture_id=".$ligne['lignefactureid']." AND efl.type='stock'");
            $qtecmd+=$efl['qte'];
            $lf=$ligne;
          }

          if($lf && $item->qte!=$qtecmd){
            $calendrier=$DB_site2->query_first("SELECT * FROM calendrier WHERE type='stockcmd' AND objid=".$lf['lignefactureid']);
            $datecmd=$fcmd=$cmenu=$dateappro="";
            $trtype="unk";
            $cmenu="<div class=\"menutools\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);modifdate='reappro';calid=0;type='stockcmd';objid=".$lf['lignefactureid'].";\"></div>";
            $cmenu2="<div class=\"menutools\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);modifdate='date';calid=0;type='stockcmd';objid=".$lf['lignefactureid'].";\"></div>";
            $cmenu3="<div class=\"menutools\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);modifdate='enlevement';calid=0;type='stockcmd';objid=".$lf['lignefactureid'].";\"></div>";
            $cmenu4="<div class=\"menutools\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);modifdate='livraison';calid=0;type='stockcmd';objid=".$lf['lignefactureid'].";\"></div>";
            $action="onmouseover=\"toggletool(this,'visible');\" onmouseout=\"toggletool(this,'hidden');\"";

            if($calendrier!=false){
              if($calendrier['date']!=0){
                $datecmd=date("d/m/Y",$calendrier['date']);
                if($calendrier['date']<$today) $trtype="late";
                elseif($calendrier['date']>=$today && $calendrier['date']<=$today2) $trtype="today";
                elseif($calendrier['date']>$today2) $trtype="futur";
              }

              $fcmd=$calendrier['txt']; 
              $nocmd['cmddate']=$calendrier['date'];
              $nocmd['fourn']=$calendrier['txt'];
              $cmenu="<div class=\"menutools\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);modifdate='reappro';calid=$calendrier[id];\" title=\"modifier\"><img src=\"images/supprimer.gif\" title=\"supprimer\" onclick=\"cal_timestamp=0;modifdate='reappro';calid=$calendrier[id];updadvdate();\"></div>";
              $cmenu2="<div class=\"menutools\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);modifdate='date';calid=$calendrier[id];\" title=\"modifier\"><img src=\"images/supprimer.gif\" title=\"supprimer\" onclick=\"cal_timestamp=0;modifdate='date';calid=$calendrier[id];updadvdate();\"></div>";
              $cmenu3="<div class=\"menutools\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);modifdate='enlevement';calid=$calendrier[id];\" title=\"modifier\"><img src=\"images/supprimer.gif\" title=\"supprimer\" onclick=\"cal_timestamp=0;modifdate='enlevement';calid=$calendrier[id];updadvdate();\"></div>";
              $cmenu4="<div class=\"menutools\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);modifdate='livraison';calid=$calendrier[id];\" title=\"modifier\"><img src=\"images/supprimer.gif\" title=\"supprimer\" onclick=\"cal_timestamp=0;modifdate='livraison';calid=$calendrier[id];updadvdate();\"></div>";
              if($calendrier['reappro']!=0) $dateappro=date("d/m/Y",$calendrier['reappro']);
            }

            $urlcode=$template->urlcode($facture->site_code,$facture->utilisateur->langue);

            $a[$nocmd[$order]].="<tr name=\"".$trtype."[]\" onmouseover=\"this.style.backgroundColor='#cccccc';\" onmouseout=\"this.style.backgroundColor='';\"$linestyle>
            <td>".$facture->userid.$vip."</td>
            <td>".$urlcode."</td>
            <td><a href=\"facture.php?action=editer&facturid=$facturid#adv\" target=\"_blank\">".$facturid."</a></td>
            <td></td>
            <td>".$item->qte."</td>
            <td>".$item->artcode."</td>
            <td>".$item->ref."</td>
            <td>".$item->marque."</td>
            <td></td>
            <td></td>
            <td $action style=\"".(substr($dateappro,0,10)==date("d/m/Y")?"color:red;":"")."\" nowrap>".$cmenu.$dateappro."</td>
            <td>".$item->delailivraison."</td>
            <td $action style=\"".($datecmd<=date("d/m/Y")?"color:red;":"")."\">".$cmenu2.$datecmd."</td>
            <td $action>".$cmenu3.($calendrier['enlevement']!=0?date("d/m/Y",$calendrier['enlevement']):"")."</td>
            <td $action>".$cmenu4.($calendrier['livraison']!=0?date("d/m/Y",$calendrier['livraison']):"")."</td>
            <td>".($facture->datelivraison>0?date("d/m/Y",$facture->datelivraison):"?")."</td>
            <td></td>
            <td>".$fcmd."</td>
            </tr>";
          }
        }
      }
    }

    $listcmd=$stock->non_commande("fourn");
    foreach($listcmd as $stock_cmd){
      $stock_cmd->groupitems();
      foreach($stock_cmd->items as $item){
        $datecmd=$fcmd=$cmenu=$dateappro=$coche="";
        $trtype="unk";
        
        $objid=$stock_cmd->id."_".$item->artid;
        $cmenu="<div class=\"menutools\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);modifdate='reappro';calid=0;type='fourncmd';objid='".$objid."';\"></div>";
        $cmenu2="<div class=\"menutools\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);modifdate='date';calid=0;type='fourncmd';objid='".$objid."';\"></div>";
        $cmenu3="<div class=\"menutools\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);modifdate='enlevement';calid=0;type='fourncmd';objid='".$objid."';\"></div>";
        $cmenu4="<div class=\"menutools\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);modifdate='livraison';calid=0;type='fourncmd';objid='".$objid."';\"></div>";
        $action="onmouseover=\"toggletool(this,'visible');\" onmouseout=\"toggletool(this,'hidden');\"";

        $calendrier=$DB_site2->query_first("SELECT * FROM calendrier WHERE type='fourncmd' AND objid='".$objid."'");
        if($calendrier!=false){
          if($calendrier['date']!=0){
            $datecmd=date("d/m/Y",$calendrier['date']);
            if($calendrier['date']<$today) $trtype="late";
            elseif($calendrier['date']>=$today && $calendrier['date']<=$today2) $trtype="today";
            elseif($calendrier['date']>$today2) $trtype="futur";
          }

          $fcmd=$calendrier['txt']; 
          $nocmd['cmddate']=$calendrier['date'];
          $nocmd['fourn']=$calendrier['txt'];
          $cmenu="<div class=\"menutools\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);modifdate='reappro';calid=$calendrier[id];\" title=\"modifier\"><img src=\"images/supprimer.gif\" title=\"supprimer\" onclick=\"cal_timestamp=0;modifdate='reappro';calid=$calendrier[id];updadvdate();\"></div>";
          $cmenu2="<div class=\"menutools\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);modifdate='date';calid=$calendrier[id];\" title=\"modifier\"><img src=\"images/supprimer.gif\" title=\"supprimer\" onclick=\"cal_timestamp=0;modifdate='date';calid=$calendrier[id];updadvdate();\"></div>";
          $cmenu3="<div class=\"menutools\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);modifdate='enlevement';calid=$calendrier[id];\" title=\"modifier\"><img src=\"images/supprimer.gif\" title=\"supprimer\" onclick=\"cal_timestamp=0;modifdate='enlevement';calid=$calendrier[id];updadvdate();\"></div>";
          $cmenu4="<div class=\"menutools\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);modifdate='livraison';calid=$calendrier[id];\" title=\"modifier\"><img src=\"images/supprimer.gif\" title=\"supprimer\" onclick=\"cal_timestamp=0;modifdate='livraison';calid=$calendrier[id];updadvdate();\"></div>";
          if($calendrier['reappro']!=0) $dateappro=date("d/m/Y",$calendrier['reappro']);
          if($calendrier['dispo']==1) $coche=" <img src=\"images/coche_verte.svg\" width=\"16\">";
        }

        $a[$stock_cmd->id].="<tr name=\"".$trtype."[]\" onmouseover=\"this.style.backgroundColor='#cccccc';\" onmouseout=\"this.style.backgroundColor='';\"$linestyle>
          <td></td>
          <td></td>
          <td><a href=\"stock.php?action=cmd&stock_cmd_id=".$stock_cmd->id."\" target=\"_blank\">".$stock_cmd->id."</a></td>
          <td></td>
          <td>".$item->qte."</td>
          <td>".$item->artcode.$coche."</td>
          <td title=\"".$item->libelle."\">".$item->ref."</td>
          <td>".$item->marque."</td>
          <td align=\"center\" title=\"".$item->libelle."\"><input type=\"checkbox\" onclick='todispo(this.checked,".$stock_cmd->id.",\"".$objid."\",\"".$item->ref."\",\"".$item->qte."\",\"".$item->artpaht.($item->ecopartht>0?"+".$item->ecopartht:"")."\",\"".$calendrier['enlevement']."\",\"".$calendrier['livraison']."\",\"".$dateappro."\",\"".str_replace("'","&#8217;",$item->libelle)."\");'></td>
          <td></td>
          <td $action>".$cmenu.$dateappro."</td>
          <td>".$item->delailivraison."</td>
          <td $action style=\"".($datecmd<=date("d/m/Y")?"color:red;":"")."\">".$cmenu2.$datecmd."</td>
          <td $action>".$cmenu3.($calendrier['enlevement']!=0?date("d/m/Y",$calendrier['enlevement']):"")."</td>
          <td $action>".$cmenu4.($calendrier['livraison']!=0?date("d/m/Y",$calendrier['livraison']):"")."</td>
          <td>".($stock_cmd->date_livraison>0?date("d/m/Y",$stock_cmd->date_livraison):"?")."</td>
          <td></td>
          <td></td>
        </tr>";
      }
    }

    ksort($a);
    $txt.=implode("\n",$a);
    $txt.="</table>";
  }

  // TABLE STOCK
  /*$a=array();
  $req=$DB_site2->query("");
  if($DB_site2->num_rows($req)>0){
  }*/

  // TABLE TRANSPORTEUR
  $a=array();
  $req=$DB_site2->query("SELECT lf.factureid,lf.qte,a.artcode,a.artcode_eurocave,f.userid,lf.lignefactureid,f.datelivraison,f.changelivraison,f.provenance,f.provenanceid,f.site_code FROM lignefacture lf INNER JOIN facture f ON (f.facturid=lf.factureid) INNER JOIN article a ON (a.artcode=lf.artcode) WHERE (SELECT COUNT(ligne_facture_id) FROM envoi_fournisseur_ligne WHERE ligne_facture_id=lf.lignefactureid AND type='trspt')=0 AND f.valide IN (1,7) GROUP BY lf.lignefactureid ORDER BY lf.factureid ASC ");

  if($DB_site2->num_rows($req)>0){
    $txt.="<br>
    <table id=\"tbltrspt\" class=\"tbl\" style=\"background-color:#EEFAFF;\">
      <tr>
        <td colspan=\"12\" class=\"tbltitlebis\"><a name=\"trsptcmd\"></a>ARTICLES NON COMMANDÉS AU TRANSPORTEUR</td>
      </tr>
      <tr class=\"tbltitle\">
        <td>N° client</td>
        <td>Site</td>
        <td>N° facture</td>
        <td>SAV</td>
        <td>Qte</td>
        <td colspan=\"2\">Article</td>
        <td>Date pour passer cmd</td>
        <td>Date livraison annoncée au client</td>
        <td>Envoi automatique</td>
        <td></td>
      </tr>";

    while($nocmd=$DB_site2->fetch_array($req)){
      $r=$DB_site2->query("SELECT * FROM calendrier WHERE type='trsptcmd' AND objid=".$nocmd['lignefactureid']);
      $calendrier=$DB_site2->fetch_array($r);
      $datecmd=$fcmd=$action=$cmenu=$dateappro="";
      $cmenu2="<div id=\"addtrsptcmd$nocmd[lignefactureid]\" class=\"menutools\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);modifdate='date';calid=0;type='trsptcmd';objid=".$nocmd['lignefactureid'].";\"></div>";
      $action="onmouseover=\"toggletool(this,'visible');\" onmouseout=\"toggletool(this,'hidden');\"";
      $trtype="unk";

      if($calendrier!=false){
        if($calendrier['date']!=0){
          $datecmd=date("d/m/Y",$calendrier['date']);
          if($calendrier['date']<$today) $trtype="late";
          elseif($calendrier['date']>=$today && $calendrier['date']<=$today2) $trtype="today";
          elseif($calendrier['date']>$today2) $trtype="futur";
        }

        $fcmd=$calendrier['txt']; 
        $nocmd['cmddate']=$calendrier['date'];
        $nocmd['fourn']=$calendrier['txt'];
        $cmenu2="<div class=\"menutools\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);modifdate='date';calid=$calendrier[id]\" title=\"modifier\"><img src=\"images/supprimer.gif\" title=\"supprimer\" onclick=\"cal_timestamp=0;modifdate='date';calid=$calendrier[id];updadvdate();\"></div>";
        if($calendrier['reappro']!=0) $dateappro=date("d/m/Y",$calendrier['reappro']);
      }

      $sav="";
      if($nocmd['provenance']=="sav" || $nocmd['provenance']=="avoir") $sav="<a href=\"facture.php?action=editer&facturid=$nocmd[provenanceid]\" target=\"_blank\">".$nocmd['provenanceid']."</a>";
      $linestyle="";
      $advlist=$adv->listadv($nocmd['factureid'],"trspt",$nocmd['lignefactureid']);
      $advtxt="";

      if(count($advlist)>0){
        $adv1=$advlist[0];
        $advurl="facture.php?action=editer&facturid=".$nocmd['factureid']."&advid=".$adv1->id."&type=trspt#adv";
        $advtxt="<a href=\"$advurl\" target=\"_blank\">Non programmé</a>";
        if($adv1->envoi>0) $advtxt="<a href=\"$advurl\" target=\"_blank\">".date("d/m/Y",$adv1->envoi)."</a>";
      }

      $utilisateur=$DB_site2->query_first("SELECT userid,vip,langue FROM utilisateur WHERE userid=".$nocmd['userid']);
      $vip=usericon($utilisateur['vip'],20);
      $urlcode=$template->urlcode($nocmd['site_code'],$utilisateur['langue']);

      if(!isset($a[$nocmd[$order]])) $linestyle=" style=\"border-top:solid thin #888;\"";

      if($ts==null || ($ts<=$calendrier['date'] && ($ts+86400)>$calendrier['date'])) $a[$nocmd[$order]].= "
      <tr name=\"".$trtype."[]\" onmouseover=\"this.style.backgroundColor='#cccccc';\" onmouseout=\"this.style.backgroundColor='';\"$linestyle>
        <td onmouseover=\"toggletool(this,'visible');\" onmouseout=\"toggletool(this,'hidden');\">".$nocmd['userid'].$vip."
        <div class=\"popup\">Client n°$nocmd[userid]".history("client",$nocmd['userid'])."</div>
        </td>
        <td>".$urlcode."</td>
        <td onmouseover=\"toggletool(this,'visible');\" onmouseout=\"toggletool(this,'hidden');\">
        <a href=\"facture.php?action=editer&facturid=$nocmd[factureid]#adv\" target=\"_blank\">".$nocmd['factureid']."</a>
        <div class=\"popup\">Commande n°$nocmd[factureid]".history("commande",$nocmd['factureid'])."</div>
        </td>
        <td>$sav</td>
        <td>".$nocmd['qte']."</td><td>".$nocmd['artcode']."</td>
        <td>".$nocmd['artcode_eurocave']."</td>
        <td $action style=\"".($datecmd<=date("d/m/Y")?"color:red;":"")."\">".$cmenu2.$datecmd."</td>
        <td>".($nocmd['datelivraison']!=0?$nocmd['changelivraison']." ".date("d/m/Y",$nocmd['datelivraison']):"?")."</td>
        <td name=\"progcmd\">".$advtxt."</td>
        <td>".$fcmd."</td>
      </tr>";
    }

    $listcmd=$stock->non_commande("trspt");
    foreach($listcmd as $stock_cmd){
      $stock_cmd->groupitems();
      foreach($stock_cmd->items as $item){
        if($stock_cmd->id == 0){
          $exp = explode(" ", $item->dest);
          if($exp[1]!=''){
            $pas_commande=$DB_site2->query_first("SELECT destinataire FROM envoi_fournisseur WHERE facturid=".$exp[1]." AND type='trspt'");
          }
          $url = "<a href=\"facture.php?action=editer&facturid=".$exp[1]."\" target=\"_blank\">(".$exp[1].")</a>";
        }else
          $url = "<a href=\"stock.php?action=cmd&stock_cmd_id=".$stock_cmd->id."\" target=\"_blank\">".$stock_cmd->id."</a>";

          if($pas_commande['destinataire']!=0){
            $a[$stock_cmd->id].="
            <tr name=\"unk[]\" onmouseover=\"this.style.backgroundColor='#cccccc';\" onmouseout=\"this.style.backgroundColor='';\"$linestyle>
            <td></td>
            <td></td>
            <td>aa".$url."</td>
            <td></td>
            <td>".$item->qte."</td>
            <td>".$item->artcode."</td>
            <td>".$item->ref."</td>
            <td></td>
            <td>".($stock_cmd->date_livraison>0?date("d/m/Y",$stock_cmd->date_livraison):"?")."</td>
            <td></td>
            <td></td>
            </tr>";
          }
      }
    }

    ksort($a);
    $txt.=implode("\n",$a);
    $txt.="</table>";
  }

  // TABLE INSTALLATEUR
  $a=array();
  $req=$DB_site2->query("SELECT lf.factureid,lf.qte,a.artcode,a.artcode_eurocave,f.userid,lf.lignefactureid,f.datelivraison,f.changelivraison,f.provenance,f.provenanceid,f.site_code FROM lignefacture lf INNER JOIN facture f ON (f.facturid=lf.factureid) INNER JOIN article a ON (a.artcode=lf.artcode) WHERE (SELECT COUNT(ligne_facture_id) FROM envoi_fournisseur_ligne WHERE ligne_facture_id=lf.lignefactureid AND type='sls')=0 AND f.valide IN (1,7) GROUP BY lf.lignefactureid ORDER BY lf.factureid ASC");

  if($DB_site2->num_rows($req)>0){
    $txt.="<br><table id=\"tblsls\" class=\"tbl\" style=\"background-color:#ffdbdb;\">
    <tr>
      <td colspan=\"12\" class=\"tbltitlebis\"><a name=\"slscmd\"></a>ARTICLES NON COMMANDÉS À L'INSTALLATEUR</td>
    </tr>
    <tr class=\"tbltitle\">
      <td>N° client</td>
      <td>Site</td>
      <td>N° facture</td>
      <td>SAV</td>
      <td>Qte</td>
      <td colspan=\"2\">Article</td>
      <td>Date pour passer cmd</td>
      <td>Date livraison annoncée au client</td>
      <td>Envoi automatique</td>
      <td></td>
    </tr>";

    while($nocmd=$DB_site2->fetch_array($req)){
      $r=$DB_site2->query("SELECT * FROM calendrier WHERE type='slscmd' AND objid=".$nocmd['lignefactureid']);
      $calendrier=$DB_site2->fetch_array($r);
      $datecmd=$fcmd=$action=$action2=$cmenu=$dateappro="";
      $cmenu2="<div class=\"menutools\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);modifdate='date';calid=0;type='slscmd';objid=".$nocmd['lignefactureid'].";\"></div>";
      $action="onmouseover=\"toggletool(this,'visible');\" onmouseout=\"toggletool(this,'hidden');\"";
      $trtype="unk";

      if($calendrier!=false){
        if($calendrier['date']!=0){
          $datecmd=date("d/m/Y",$calendrier['date']);
          if($calendrier['date']<$today) $trtype="late";
          elseif($calendrier['date']>=$today && $calendrier['date']<=$today2) $trtype="today";
          elseif($calendrier['date']>$today2) $trtype="futur";
        }

        $fcmd=$calendrier['txt']; 
        $nocmd['cmddate']=$calendrier['date'];
        $nocmd['fourn']=$calendrier['txt'];
        $cmenu2="<div class=\"menutools\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);modifdate='date';calid=$calendrier[id]\" title=\"modifier\"><img src=\"images/supprimer.gif\" title=\"supprimer\" onclick=\"cal_timestamp=0;modifdate='date';calid=$calendrier[id];updadvdate();\"></div>";
      }

      $sav="";
      if($nocmd['provenance']=="sav" || $nocmd['provenance']=="avoir") $sav="<a href=\"facture.php?action=editer&facturid=$nocmd[provenanceid]\" target=\"_blank\">".$nocmd['provenanceid']."</a>";
      $linestyle="";
      $advlist=$adv->listadv($nocmd['factureid'],"sls",$nocmd['lignefactureid']);
      $advtxt="";

      if(count($advlist)>0){
        $adv1=$advlist[0];
        $advurl="facture.php?action=editer&facturid=".$nocmd['factureid']."&advid=".$adv1->id."&type=sls#adv";
        $advtxt="<a href=\"$advurl\" target=\"_blank\">Non programmé</a>";
        if($adv1->envoi>0) $advtxt="<a href=\"$advurl\" target=\"_blank\">".date("d/m/Y",$adv1->envoi)."</a>";
      }

      $utilisateur=$DB_site2->query_first("SELECT userid,vip,langue FROM utilisateur WHERE userid=".$nocmd['userid']);
      $vip=usericon($utilisateur['vip'],20);
      $urlcode=$template->urlcode($nocmd['site_code'],$utilisateur['langue']);

      if(!isset($a[$nocmd[$order]])) $linestyle=" style=\"border-top:solid thin #888;\"";
      if($ts==null || ($ts<=$calendrier['date'] && ($ts+86400)>$calendrier['date'])) $a[$nocmd[$order]].= "<tr name=\"".$trtype."[]\" onmouseover=\"this.style.backgroundColor='#cccccc';\" onmouseout=\"this.style.backgroundColor='';\"$linestyle>
        <td onmouseover=\"toggletool(this,'visible');\" onmouseout=\"toggletool(this,'hidden');\">".$nocmd['userid'].$vip."<div class=\"popup\">Client n°$nocmd[userid]".history("client",$nocmd['userid'])."</div></td>
        <td>".$urlcode."</td>
        <td onmouseover=\"toggletool(this,'visible');\" onmouseout=\"toggletool(this,'hidden');\"><a href=\"facture.php?action=editer&facturid=$nocmd[factureid]#adv\" target=\"_blank\">".$nocmd['factureid']."</a><div class=\"popup\">Commande n°$nocmd[factureid]".history("commande",$nocmd['factureid'])."</div></td>
        <td>$sav</td>
        <td>".$nocmd['qte']."</td>
        <td>".$nocmd['artcode']."</td>
        <td>".$nocmd['artcode_eurocave']."</td>
        <td $action style=\"".($datecmd<=date("d/m/Y")?"color:red;":"")."\">".$cmenu2.$datecmd."</td>
        <td>".($nocmd['datelivraison']!=0?$nocmd['changelivraison']." ".date("d/m/Y",$nocmd['datelivraison']):"?")."</td>
        <td name=\"progcmd\">".$advtxt."</td>
        <td>".$fcmd."</td>
      </tr>";
    }

    ksort($a);
    $txt.=implode("\n",$a);
    $txt.="</table>";
  }

  //***************\\
     //MISE EN SERVICE\\
     //***************\\
     
     $a = array();

     $sql = "SELECT lf.factureid,lf.qte,a.artcode,a.artcode_eurocave,f.userid,lf.lignefactureid,f.datelivraison,f.changelivraison,f.provenance,f.provenanceid";
     $sql .= " FROM lignefacture lf";
     $sql .= " INNER JOIN facture f ON (f.facturid=lf.factureid)";
     $sql .= " INNER JOIN article a ON (a.artcode=lf.artcode)";
     $sql .= " INNER JOIN lignefactureinstallation lfi ON (lfi.factureid = f.facturid)";
     $sql .= " WHERE (SELECT COUNT(ligne_facture_id) FROM envoi_fournisseur_ligne WHERE ligne_facture_id=lf.lignefactureid AND type='service')=0";
     $sql .= " AND f.valide IN (1,7)";
     $sql .= " AND lfi.lignefactureinstallation_typearticles LIKE CONCAT('%|',a.typearticleid,'|%')";
     $sql .= " GROUP BY lf.lignefactureid";
     $req = $DB_site2->query($sql);

     if($DB_site2->num_rows($req)>0){
               
          $txt .= ""
          . "<br>"
          . "<table id=\"tblsls\" class=\"tbl\" style=\"background-color:#FFDDB8;\">"
               . "<tr>"
                    . "<td colspan=\"12\" class=\"tbltitle\"><a name=\"slscmd\"></a>Articles non commandés pour mise en service</td>"
               . "</tr>"
               . "<tr class=\"tbltitle\">"
                    . "<td>N° client</td>"
                    . "<td>N° facture</td>"
                    . "<td>SAV</td>"
                    . "<td>Qte</td>"
                    . "<td colspan=\"2\">Article</td>"
                    . "<td>Date pour passer cmd</td>"
                    . "<td>Date livraison annoncée au client</td>"
                    . "<td>Envoi automatique</td>"
                    . "<td></td>"
               . "</tr>";
          
          while($nocmd = $DB_site2->fetch_array($req)){
               
               $sql = "SELECT *";
               $sql .= " FROM calendrier";
               $sql .= " WHERE type = 'servicecmd'";
               $sql .= " AND objid = ".$nocmd['lignefactureid'];
               $r = $DB_site2->query($sql);
               $calendrier = $DB_site2->fetch_array($r);
               
               $datecmd = $fcmd = $action = $action2 = $cmenu = $dateappro = "";
               
               $cmenu2 = ""
               . "<div class=\"menutools\">"
                    . "<img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);modifdate='date';calid=0;type='servicecmd';objid=".$nocmd['lignefactureid'].";\">"
               . "</div>";
               
               $action = "onmouseover=\"toggletool(this,'visible');\" onmouseout=\"toggletool(this,'hidden');\"";
               $trtype = "unk";
               
               if($calendrier != false){
                    
                    if($calendrier['date'] != 0){
                         
                         $datecmd = date("d/m/Y",$calendrier['date']);
                         if($calendrier['date'] < $today){
                              $trtype = "late";
                         } elseif($calendrier['date'] >= $today && $calendrier['date']<=$today2){
                              $trtype = "today";
                         } elseif($calendrier['date']>$today2){
                              $trtype = "futur";
                         }
                    }
                    
                    $fcmd             = $calendrier['txt']; 
                    $nocmd['cmddate'] = $calendrier['date'];
                    $nocmd['fourn']   = $calendrier['txt'];
                    
                    $cmenu2 = ""
                    . "<div class=\"menutools\">"
                         . "<img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);modifdate='date';calid=".$calendrier['id']."\" title=\"modifier\">"
                         . "<img src=\"images/supprimer.gif\" title=\"supprimer\" onclick=\"cal_timestamp=0;modifdate='date';calid=".$calendrier['id'].";updadvdate();\">"
                    . "</div>";
               }
               
               $sav = "";
               
               if($nocmd['provenance']=="sav" || $nocmd['provenance']=="avoir"){
                    $sav = "<a href=\"facture.php?action=editer&facturid=".$nocmd['provenanceid']."\">".$nocmd['provenanceid']."</a>";
               }
                    
               $linestyle = "";
               $advlist   = $adv->listadv($nocmd['factureid'],"miseenservice",$nocmd['lignefactureid']);
               $advtxt    = "";
               if(count($advlist) > 0){
                   
                    $adv1   = $advlist[0];
                    $advurl = "facture.php?action=editer&facturid=".$nocmd['factureid']."&advid=".$adv1->id."&type=service#adv";
                    $advtxt = "<a href=\"$advurl\" target=\"_blank\">Non programmé</a>";
                    
                    if($adv1->envoi>0) $advtxt="<a href=\"$advurl\" target=\"_blank\">".date("d/m/Y",$adv1->envoi)."</a>";
               }

               $sql = "SELECT userid, vip";
               $sql .= " FROM utilisateur";
               $sql .= " WHERE userid = ".$nocmd['userid'];
               $utilisateur = $DB_site2->query_first($sql);
               
               $vip = usericon($utilisateur['vip'],20);

               if(!isset($a[$nocmd[$order]])){
                    $linestyle = " style=\"border-top:solid thin #888;\"";
               }
               
               if($ts==null || ($ts<=$calendrier['date'] && ($ts+86400)>$calendrier['date'])){ 
                    
                    $a[$nocmd[$order]].= ""
                    . "<tr name=\"".$trtype."[]\" onmouseover=\"this.style.backgroundColor='#cccccc';\" onmouseout=\"this.style.backgroundColor='';\"".$linestyle.">"
                         . "<td onmouseover=\"toggletool(this,'visible');\" onmouseout=\"toggletool(this,'hidden');\">"
                              . $nocmd['userid'].$vip
                              . "<div class=\"popup\">Client n°".$nocmd['userid'].history("client",$nocmd['userid'])."</div>"
                         . "</td>"
                         . "<td onmouseover=\"toggletool(this,'visible');\" onmouseout=\"toggletool(this,'hidden');\">"
                              . "<a href=\"facture.php?action=editer&facturid=".$nocmd['factureid']."#adv\">".$nocmd['factureid']."</a>"
                              . "<div class=\"popup\">Commande n°".$nocmd['factureid'].history("commande",$nocmd['factureid'])."</div>"
                         . "</td>"
                         . "<td>".$sav."</td>"
                         . "<td>".$nocmd['qte']."</td>"
                         . "<td>".$nocmd['artcode']."</td>"
                         . "<td>".$nocmd['artcode_eurocave']."</td>"
                         . "<td ".$action." style=\"".($datecmd==date("d/m/Y")?"color:red;":"")."\">".$cmenu2.$datecmd."</td>"
                         . "<td>".($nocmd['datelivraison']!=0?$nocmd['changelivraison']." ".date("d/m/Y",$nocmd['datelivraison']):"?")."</td>"
                         . "<td name=\"progcmd\">".$advtxt."</td>"
                         . "<td>".$fcmd."</td>"
                         . "</tr>";
               }
          }
   
          ksort($a);
          $txt .= implode("\n",$a);
          $txt .="</table>";
     }

  return $txt;
}


//******************************************************************************
//*
function history($type,$id){
  global $DB_site2;
  if($type=="client") $field="userid";
  if($type=="commande") $field="facturid";

  $req=$DB_site2->query("SELECT * FROM suivi_$type WHERE $field='$id' ORDER BY date DESC");

  $ret="<table>";
  while($suivi=$DB_site2->fetch_array($req)){
    $ret.="<tr><td>".date("d/m/Y",$suivi['date'])."</td><td>".$suivi['commercial']."</td><td>".tidy_repair_string($suivi['suivi'],array(),'utf8')."</td></tr>";
  }
  $ret.="</table>";

  return $ret;
}

?>