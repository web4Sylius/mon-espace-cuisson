<?php
ini_set("display_errors","on");

$suivih=date("H");
$suivid=time();
// $suivih=10;

$test=false;
if(isset($_GET['test'])){
  $suivih=$_GET['test'];
  $test=true;
  print "test suivi fournisseurs";
}

if(isset($_GET['suivid'])) $suivid=$_GET['suivid'];

$nostat=1;
include_once("host_config_admin.php");
include_once($rootpath."class/template.class.php");
include_once($rootpath."class/stock.class.php");
include_once($rootpath."class/email.php");
include($rootpath."admin/adv/adv.class.php");
include($rootpath."class/facture.class.php");
include_once($rootpath."class/devises.class.php");


$DB_site=clone $DB_site2;

$devises=new devises($DB_site);

$today=mktime(0,0,0,date("m",$suivid),date("d",$suivid),date("Y",$suivid));
$day2=$today+86400;
$day3=$today+172800;
if(date("w")==5){$day3+=86400;}

$style="<style>
.tbl { font-size:12px; width:100%; margin-top:10px; border-collapse:collapse; }
.tbl thead { font-weight:bold; text-align:center; }
.tbl td { border:solid thin #808080; }
.title { text-align:center; background-color:#cccccc; }
</style>";


$groupes=array();
$str="";
$requete=$DB_site->query("SELECT groupes,mail,societe FROM fournisseur WHERE mail<>'' AND type='fournadv'");
while($env=$DB_site->fetch_array($requete)){
    if($env['groupes']=="") $env['groupes']=$env['societe'];
    $str.=",".$env['groupes'];
}
$groupes=explode(',',$str);
$groupes=array_unique($groupes);
$groupes=array_filter($groupes);

// if(date("w")==4) $day3+=172800;

foreach($groupes as $groupe){

  $txt=suivi_fourn($groupe,$today,"indate","efl.indate","<=");
//   $txt.=suivi_fourn($groupe,$day2,"indate");
//   $txt.=suivi_fourn($groupe,$day3,"indate");
  
//   $txt.=suivi_fourn($groupe,$today,"outdate","efl.outdate","<=");
//   $txt.=suivi_fourn($groupe,$day2,"outdate");
//   $txt.=suivi_fourn($groupe,$day3,"outdate");
  
  if($txt!=""){
    $to=array();
    $contacts=$DB_site->query("SELECT nom,prenom,societe,mail FROM fournisseur WHERE suivi LIKE '%".addslashes(trim($groupe))."%' AND suivih=".$suivih);
    if($DB_site->num_rows($contacts)>0){
	while($contact=$DB_site->fetch_array($contacts)) $to[]=$contact['mail'];

	$txt=$style."Bonjour,<br><br>Suivi des commandes au ".date("d/m/Y",$today)." : ".$groupe."<br>"
	.$txt.
	"<br>
	<br>Cordialement,
	<br>ADV - Mon Espace Cuisson - www.mon-espace-cuisson.com
	<br>Tel +334.26.03.05.44 / fax : +33.4.26.03.05.49";
	$mail=new email();
	$mail->destinataire=implode(';',$to);
	$mail->expediteur="adv@mon-espace-cuisson.com";
	$mail->expname="Mon Espace Cuisson";
	$mail->copymode="bcc";
	$mail->to=array("pr@mon-espace-cuisson.com","adv@mon-espace-cuisson.com");
	$mail->subject="Suivi des commandes Mon Espace Cuisson au ".date("d/m/Y",$today);
	$mail->body=$txt;
	if(!$test) $mail->sendmail();
	if($test) print "<br><br>".$groupe." : ".implode(',',$to)."<br>".$txt;
    }
  }
}



$groupes=array();
$str="";
$requete=$DB_site->query("SELECT groupes,mail,societe FROM fournisseur WHERE mail<>'' AND type='trsptadv'");
while($env=$DB_site->fetch_array($requete)){
    if($env['groupes']=="") $env['groupes']=$env['societe'];
    $str.=",".$env['groupes'];
}
$groupes=explode(',',$str);
$groupes=array_unique($groupes);
$groupes=array_filter($groupes);

if(date("w")==5) $day2+=172800;

foreach($groupes as $groupe){

  $txt=suivi_intrspt($groupe,$today,"efl.indate","<=");
  $txt.=suivi_intrspt($groupe,$day2);

  if($txt!=""){
    $to=array();
    $contacts=$DB_site->query("SELECT nom,prenom,societe,mail FROM fournisseur WHERE suivi LIKE '%".addslashes(trim($groupe))."%' AND suivih=".$suivih);
    if($DB_site->num_rows($contacts)>0){
	while($contact=$DB_site->fetch_array($contacts)) $to[]=$contact['mail'];

	$txt=$style."Bonjour,<br><br>Suivi des commandes au ".date("d/m/Y",$today)." : ".$groupe
	.$txt.
	"<br>
	<br>Cordialement,
	<br>ADV - Mon Espace Cuisson - www.mon-espace-cuisson.com
	<br>Tel +334.26.03.05.44 / fax : +33.4.26.03.05.49";
	$mail=new email();
	$mail->destinataire=implode(';',$to);
	$mail->expediteur="adv@mon-espace-cuisson.com";
	$mail->expname="Mon Espace Cuisson";
	$mail->copymode="bcc";
	$mail->to=array("pr@mon-espace-cuisson.com","adv@mon-espace-cuisson.com");
	$mail->subject="Suivi des commandes Mon Espace Cuisson au ".date("d/m/Y",$today);
	$mail->body=$txt;
	if(!$test && count($to)>0) $mail->sendmail();
	if($test) print "<br>".$groupe." : ".implode(',',$to)."<br>".$txt;
    }
  }
}


$groupes=array();
$requete=$DB_site->query("SELECT groupes,mail,societe FROM fournisseur WHERE mail<>'' AND type='slsadv'");
while($env=$DB_site->fetch_array($requete)){
    if($env['groupes']=="") $env['groupes']=$env['societe'];
    $str.=",".$env['groupes'];
}
$groupes=explode(',',$str);
$groupes=array_unique($groupes);
$groupes=array_filter($groupes);

foreach($groupes as $groupe){
  
  $txt=suivi_insls($groupe,$today,"efl.indate","<=");
//   $txt.=suivi_insls($groupe,$day2);
//   $txt.=suivi_insls($groupe,$day3);
//   $txt.=suivi_outsls($groupe,$today,"efl.outdate","<=");
//   $txt.=suivi_outsls($groupe,$day2);
//   $txt.=suivi_outsls($groupe,$day3);

  if($txt!=""){
    $to=array();
    $contacts=$DB_site->query("SELECT nom,prenom,societe,mail FROM fournisseur WHERE suivi LIKE '%".addslashes(trim($groupe))."%' AND suivih=".$suivih);
    if($DB_site->num_rows($contacts)>0){
	while($contact=$DB_site->fetch_array($contacts)) $to[]=$contact['mail'];

	$txt=$style."Bonjour,<br><br>Suivi des commandes au ".date("d/m/Y",$today)." : ".$groupe
	.$txt.
	"<br>
	<br>Cordialement,
	<br>ADV - Mon Espace Cuisson - www.mon-espace-cuisson.com
	<br>Tel +334.26.03.05.44 / fax : +33.4.26.03.05.49";
	$mail=new email();
	$mail->destinataire=implode(';',$to);
	$mail->expediteur="adv@mon-espace-cuisson.com";
	$mail->expname="Mon Espace Cuisson";
	$mail->copymode="bcc";
	$mail->to=array("pr@mon-espace-cuisson.com","adv@mon-espace-cuisson.com");
	$mail->subject="Suivi des commandes Mon Espace Cuisson au ".date("d/m/Y",$today);
	$mail->body=$txt;
	if(!$test) $mail->sendmail();
	if($test) print "<br>".$groupe." : ".implode(',',$to)."<br>".$txt;
    }
  }
}



$adv=new adv($DB_site);

$liste=$adv->envoiauto($suivih,$suivid);
if($liste!=array()){
    include($rootpath."admin/adv/envoi_fournisseur.php");
    include($rootpath."admin/adv/mail_fourn.php");
    include($rootpath."admin/adv/mail_trspt.php");
    include($rootpath."admin/adv/mail_sls.php");
    foreach($liste as $adv){
	$user_info=$DB_site->query_first("SELECT * FROM parametresgeneraux WHERE username='".$adv->utilisateur."'");
	$adv_facture=$DB_site->query_first("SELECT * FROM facture f, pays p WHERE f.paysid=p.paysid AND f.facturid=".$adv->facturid);
	switch($adv->type){
	    case "fourn":
	    mail_fourn($adv,$test);
	    break;

	    case "trspt":
	    mail_trspt($adv,$test);
	    break;

	    case "sls":
	    mail_sls($adv,$test);
	    break;
	}
    }
}



function suivi_fourn($fourn,$ts,$typedate,$order="facturid",$late=null){
  global $DB_site,$types;
  
  $tomorrow=$ts+86399;
  if($typedate=="indate"){
      $txttitre="Liste des commandes Départ %s";
      $txtdate="Date enlev.";
      $enddate="enlevement";
      $tdlivr="";
  }
  if($typedate=="outdate"){
      $txttitre="Liste des commandes à livrer";
      $txtdate="Date livr.";
      $enddate="livraison";
      $tdlivr="<td>Lieu livraison</td>";
  }
  if($order=="") $order="facturid";

  $destinataire=$type="";
  $tr=array();
  $sql="SELECT ef.facturid,efl.indate,efl.outdate,ef.destinataire,lf.artcode,efl.expediteur,efl.type,ef.trspt,efl.qte,ef.id,efl.ligne_facture_id,ef.enlev,ef.livr FROM envoi_fournisseur_ligne efl INNER JOIN envoi_fournisseur ef ON (efl.efid=ef.id) INNER JOIN lignefacture lf ON (lf.lignefactureid=efl.ligne_facture_id) WHERE ";
  if($late==null){
    $sql.="(efl.$typedate BETWEEN $ts AND $tomorrow)";
    $txttitre.=" du ".date("d/m/Y",$ts);

  }else{
    $sql.=" efl.$typedate $late $ts AND efl.$typedate>0";
    if($late=="<=") $txttitre.=" en retard ou prévues ce jour ".date("d/m/Y",$ts);
    if($late==">") $txttitre.=" futur";
    
  }
  $sql.=" AND ef.$enddate=0 AND ef.destinataire='".addslashes($fourn)."' AND efl.type IN ('fourn','stock') ORDER BY ".$order;
  $requete=$DB_site->query($sql);
//   if($DB_site->num_rows($requete)==0) return false;
  

  while($envoi=$DB_site->fetch_array($requete)){
    $article=$DB_site->query_first("SELECT a.artcode_eurocave,a.libelleFR,m.libelle AS marque FROM article a INNER JOIN article_marque am ON(am.artid=a.artid) INNER JOIN marque m ON(m.marqueid=am.marqueid) WHERE a.artcode='$envoi[artcode]'");
    /*if($type!=$envoi['type']){
      $type=$envoi['type'];
      $ret.="<tr><td colspan=\"7\" class=\"tbltitle\">$txttitre $types[$type]</td></tr>";
    }*/

    
    if($envoi[trspt]=="transporteur fournisseur" || $envoi[trspt]=="colissimo") $envoi[trspt]="Par vos soins";
    if($typedate=="outdate"){
      if($envoi[livr]==-1){
	$txtlivr="<td></td>";
      }elseif($envoi[livr]==0){$tr[$envoi['enlev']].="<tr onmouseover=\"this.style.backgroundColor='#cccccc';\" onmouseout=\"this.style.backgroundColor='';\">
      <td>".date("d/m/Y",$envoi[$typedate])."</td>
      <td>$envoi[facturid]</td>
      <td>$envoi[qte]</td>
      <td title=\"".$article['libelleFR']."\">$article[artcode_eurocave]</td>
      <td>$article[marque]</td>
      <td>$envoi[trspt]</td>
      $txtlivr
      <td></td>
      </tr>";
	$facture=$DB_site->query_first("SELECT * FROM facture WHERE facturid=".$envoi['facturid']);
	$txtlivr="<td>".$facture['raisonsociale']." ".$facture['nom']." ".$facture['codepostal']."</td>";
      }else{
	$fourn=$DB_site->query_first("SELECT * FROM fournisseur WHERE id=".$envoi['livr']);
	if($fourn['id']!="") 
	    $txtlivr="<td>".$fourn['societe']." ".$fourn['ville']."</td>";
	else
	    $txtlivr="<td></td>";
      }
    }


    $tr[$envoi['enlev']].="<tr onmouseover=\"this.style.backgroundColor='#cccccc';\" onmouseout=\"this.style.backgroundColor='';\">
      <td>".date("d/m/Y",$envoi[$typedate])."</td>
      <td>$envoi[facturid]</td>
      <td>$envoi[qte]</td>
      <td title=\"".$article['libelleFR']."\">$article[artcode_eurocave]</td>
      <td>$article[marque]</td>
      <td>$envoi[trspt]</td>
      $txtlivr
      <td></td>
      </tr>";
  }

  $stock=new stock($DB_site);
  $listcmd=$stock->enlevements($fourn,$ts,true);
  foreach($listcmd as $stock_cmd){
      $stock_cmd->groupitems();
      foreach($stock_cmd->items as $item){
	  $tr[$stock_cmd->enlevement].="<tr onmouseover=\"this.style.backgroundColor='#cccccc';\" onmouseout=\"this.style.backgroundColor='';\">
	  <td>".date("d/m/Y",$item->date_enlevement)."</td>
	  <td>".$stock_cmd->id."</td>
	  <td>".$item->qte."</td>
	  <td title=\"".$item->libelle."\">".$item->ref."</td>
	  <td>".$item->marque."</td>
	  <td>$envoi[trspt]</td>
	  <td></td>
	  </tr>";
      }
  }

  foreach($tr as $enlev=>$lines){
      $depart="usine";
      if($enlev!=0){
	  $fourn=$DB_site->query_first("SELECT societe,ville FROM fournisseur WHERE id=".intval($enlev));
	  $depart=$fourn['societe']." ".$fourn['ville'];
      }
      $ret.="<table id=\"$tblid\" class=\"tbl\">
      <thead>
      <tr><td colspan=\"10\" class=\"title\">".sprintf($txttitre,$depart)."</td></tr>
      <tr><td>$txtdate</td><td>N° Cde</td><td>Qte</td><td>Article</td><td>Marque</td><td>Transporteur</td>$tdlivr<td style=\"color:red;\">Vos remarques</td></tr>
      </thead>".$lines."</table>";
  }

  return $ret;
}


function suivi_intrspt($fourn,$ts,$order="facturid",$late=null){
  global $DB_site,$types;

   $tomorrow=$ts+86399;
  if($order=="") $order="facturid";

  $destinataire=$type="";
  $sql="SELECT ef.facturid,efl.indate,efl.outdate,ef.destinataire,lf.artcode,lf.libelle AS artdesc,efl.expediteur,efl.type,ef.trspt,lf.qte,ef.id,efl.ligne_facture_id,ef.enlev,ef.livr FROM envoi_fournisseur_ligne efl INNER JOIN envoi_fournisseur ef ON (efl.efid=ef.id) INNER JOIN lignefacture lf ON (lf.lignefactureid=efl.ligne_facture_id) WHERE ";
  if($late==null){
    $sql.="(efl.indate BETWEEN $ts AND $tomorrow)";
    $txttitre=" du ".date("d/m/Y",$ts);
    $tblid="tbltrspt".$ts;
  }else{
    $sql.=" efl.indate $late $ts AND efl.indate>0";
    if($late=="<=") $txttitre=" en retard ou prévues ce jour ".date("d/m/Y");
    if($late==">") $txttitre=" futur";
    
  }
  $sql.=" AND ef.enlevement=0 AND ef.destinataire='".addslashes($fourn)."' AND efl.type='trspt' ORDER BY ".$order;

  $requete=$DB_site->query($sql);
  $cmd=array();

  while($envoi=$DB_site->fetch_array($requete)){
    $article=$DB_site->query_first("SELECT artcode_eurocave,libelleFR,(emballage_poids1+emballage_poids2+emballage_poids3) AS emballage_poids FROM article WHERE artcode='$envoi[artcode]'");

    $cmd['id']=$envoi['id'];
    $cmd['indate']=$envoi['indate'];
    $cmd['outdate']=$envoi['outdate'];
    $cmd['qte']=$envoi['qte'];
    if($envoi['livr']=="0"){
	$facture=$DB_site->query_first("SELECT f.facturid,f.nom,f.prenom,f.codepostal,f.paysid,p.libelle FROM facture f INNER JOIN pays p USING (paysid) WHERE facturid=".$envoi['facturid']);
	$cmd['dest']=trim($facture['raisonsociale']." ".$facture['nom']);
	$cmd['cp']=$facture['codepostal'];
	$cmd['pays']=$facture['libelle'];
    }elseif(intval($envoi['livr'])>0){
	$fournisseur=$DB_site->query_first("SELECT * FROM fournisseur f INNER JOIN pays p ON (p.paysid=f.paysid) WHERE f.id=".intval($envoi['livr']));
	$cmd['dest']=trim($fournisseur['societe']." ".$fournisseur['ville']);
	$cmd['cp']=$fournisseur['cp'];
	$cmd['pays']=$fournisseur['libelle'];
    }else
	$cmd['dest']="";

    if($envoi['enlev']==0){
	$enlev=$DB_site->query_first("SELECT * FROM envoi_fournisseur ef INNER JOIN envoi_fournisseur_ligne efl ON(efl.efid=ef.id) WHERE ef.type='fourn' AND efl.ligne_facture_id=".$envoi['ligne_facture_id']);
	$livr=$DB_site->query_first("SELECT * FROM fournisseur WHERE id=".$enlev['livr']);
	$cmd0[$envoi['facturid']]['id']=$cmd['id'];
	$cmd0[$envoi['facturid']]['indate']=$cmd['indate'];
	$cmd0[$envoi['facturid']]['outdate']=$cmd['outdate'];
	$cmd0[$envoi['facturid']]['qte']+=$cmd['qte'];
	$cmd0[$envoi['facturid']]['dest']=$cmd['dest'];
	$cmd0[$envoi['facturid']]['cp']=$cmd['cp'];
	$cmd0[$envoi['facturid']]['pays']=$cmd['pays'];
	$cmd0[$envoi['facturid']]['enl']=$enlev['destinataire'];
	$cmd0[$envoi['facturid']]['lieu']=$livr['societe']." ".$livr['ville'];
	$cmd0[$envoi['facturid']]['typetrspt']=$envoi['trspt'];
	$cmd0[$envoi['facturid']]['poids']+=$envoi['qte']*$article['emballage_poids'];
	$cmd0[$envoi['facturid']]['desc'].=$envoi['artdesc']."<br>";
    }else{
	$enlev=$DB_site->query_first("SELECT * FROM fournisseur WHERE id=".$envoi['enlev']);
	$cmd1[$envoi['facturid']]['id']=$cmd['id'];
	$cmd1[$envoi['facturid']]['indate']=$cmd['indate'];
	$cmd1[$envoi['facturid']]['outdate']=$cmd['outdate'];
	$cmd1[$envoi['facturid']]['qte']+=$cmd['qte'];
	$cmd1[$envoi['facturid']]['dest']=$cmd['dest'];
	$cmd1[$envoi['facturid']]['cp']=$cmd['cp'];
	$cmd1[$envoi['facturid']]['pays']=$cmd['pays'];
	$cmd1[$envoi['facturid']]['enl']=$enlev['societe']." ".$enlev['ville'];
	$cmd1[$envoi['facturid']]['typetrspt']=$envoi['trspt'];
	$cmd1[$envoi['facturid']]['poids']+=$envoi['qte']*$article['emballage_poids'];
	$cmd1[$envoi['facturid']]['desc'].=$envoi['artdesc']."<br>";
    }
  }  

  $stock=new stock($DB_site);
  $listcmd=$stock->enlevements($fourn,$ts);
  foreach($listcmd as $stock_cmd){
      $stock_cmd->groupitems();
      $enlev=$DB_site->query_first("SELECT * FROM fournisseur INNER JOIN pays USING(paysid) WHERE id=".$stock_cmd->enlevement);
      $livr=$DB_site->query_first("SELECT * FROM fournisseur INNER JOIN pays USING(paysid) WHERE id=".$stock_cmd->livraison);
      $cmd1[$stock_cmd->id]['dest']=$livr['societe'];
      $cmd1[$stock_cmd->id]['cp']=$livr['cp'];
      $cmd1[$stock_cmd->id]['pays']=$livr['libelle'];
      $cmd1[$stock_cmd->id]['enl']=$enlev['societe']." ".$enlev['ville'];
      $cmd1[$stock_cmd->id]['typetrspt']="Acilyon";
      foreach($stock_cmd->items as $item){
	  $cmd1[$stock_cmd->id]['indate']=$item->date_enlevement;
	  $cmd1[$stock_cmd->id]['outdate']=$item->date_livraison;
	  $cmd1[$stock_cmd->id]['qte']+=$item->qte;
	  $cmd1[$stock_cmd->id]['poids']+=$item->qte*$item->poids_brut;
	  $cmd1[$stock_cmd->id]['desc'].=$item->libelle."<br>";
      }
  }

  if(count($cmd0)>0){
    $ret.="<table class=\"tbl\">
    <thead>
    <tr><td colspan=\"11\" class=\"title\">Liste des commandes en attente de réception en vos locaux $txttitre</td></tr>
    <tr><td>Date réception</td><td>Lieu réception</td><td>N° Cde</td><td>Désignation</td><td>Poids total</td><td>Type</td><td>Provenance</td><td>Destination</td><td>CP</td><td>Pays</td><td style=\"color:red;\">Vos remarques</td></tr>
    </thead>";

    foreach($cmd0 as $facturid=>$efldata){
	
	$ret.="<tr onmouseover=\"this.style.backgroundColor='#cccccc';\" onmouseout=\"this.style.backgroundColor='';\">
	  <td>".date("d/m/Y",$efldata['indate'])."</td>
	  <td>".$efldata['lieu']."</td>
	  <td>".$facturid."</td>
	  <td>".$efldata['desc']."</td>
	  <td>".$efldata['poids']." kg</td>
	  <td>".$efldata['typetrspt']."</td>
	  <td>".$efldata['enl']."</td>
	  <td>".$efldata['dest']."</td>
	  <td>".$efldata['cp']."</td>
	  <td>".$efldata['pays']."</td>
	  <td></td>
	</tr>";
    }

    $ret.="</table>";
  }

  if(count($cmd1)>0){
    $ret.="<table class=\"tbl\">
    <thead>
    <tr><td colspan=\"10\" class=\"title\">Liste des commandes enlèvement par vos soins $txttitre</td></tr>
    <tr><td>Date enlèvement</td><td>Lieu enlèvement</td><td>N° Cde</td><td>Désignation</td><td>Poids total</td><td>Type</td><td>Destination</td><td>CP</td><td>Pays</td><td style=\"color:red;\">Vos remarques</td></tr>
    </thead>";

    foreach($cmd1 as $facturid=>$efldata){
	
	$ret.="<tr onmouseover=\"this.style.backgroundColor='#cccccc';\" onmouseout=\"this.style.backgroundColor='';\">
	  <td>".date("d/m/Y",$efldata['indate'])."</td>
	  <td>".$efldata['enl']."</td>
	  <td>".$facturid."</td>
	  <td>".$efldata['desc']."</td>
	  <td>".$efldata['poids']." kg</td>
	  <td>".$efldata['typetrspt']."</td>
	  <td>".$efldata['dest']."</td>
	  <td>".$efldata['cp']."</td>
	  <td>".$efldata['pays']."</td>
	  <td></td>
	</tr>";
    }

    $ret.="</table>";
  }
  return $ret;

}

function suivi_outtrspt($fourn,$ts,$order="facturid",$late=null){
  global $DB_site,$types;

   $tomorrow=$ts+86399;
  if($order=="") $order="facturid";

  $destinataire=$type="";
  $sql="SELECT ef.facturid,efl.indate,efl.outdate,ef.destinataire,lf.artcode,efl.expediteur,efl.type,ef.trspt,lf.qte,ef.id,efl.ligne_facture_id,ef.enlev,ef.livr FROM envoi_fournisseur_ligne efl INNER JOIN envoi_fournisseur ef ON (efl.efid=ef.id) INNER JOIN lignefacture lf ON (lf.lignefactureid=efl.ligne_facture_id) WHERE ";
  if($late==null){
    $sql.="(efl.outdate BETWEEN $ts AND $tomorrow)";
    $txttitre=" du ".date("d/m/Y",$ts);
    $tblid="tbltrspt".$ts;
  }else{
    $sql.=" efl.outdate $late $ts AND efl.outdate>0";
    if($late=="<=") $txttitre=" en retard ou prévues ce jour ".date("d/m/Y");
    if($late==">") $txttitre=" futur";
    
  }
  $sql.=" AND ef.livraison=0 AND ef.destinataire='".addslashes($fourn)."' AND efl.type='trspt' ORDER BY ".$order;

  $requete=$DB_site->query($sql);
  $cmd=array();

  while($envoi=$DB_site->fetch_array($requete)){
    $article=$DB_site->query_first("SELECT artcode_eurocave,libelleFR,(emballage_poids1+emballage_poids2+emballage_poids3) AS emballage_poids FROM article WHERE artcode='$envoi[artcode]'");
   
    $cmd[$envoi['facturid']]['id']=$envoi['id'];
    $cmd[$envoi['facturid']]['indate']=$envoi['indate'];
    $cmd[$envoi['facturid']]['outdate']=$envoi['outdate'];
    $cmd[$envoi['facturid']]['poids']+=$envoi['qte']*$article['emballage_poids'];
    $cmd[$envoi['facturid']]['desc'].=$article['libelleFR']."<br>";
    
    if($envoi['livr']=="0"){
	$facture=$DB_site->query_first("SELECT f.facturid,f.nom,f.prenom,f.codepostal,f.paysid,p.libelle FROM facture f INNER JOIN pays p USING (paysid) WHERE facturid=".$envoi['facturid']);
	$cmd[$envoi['facturid']]['dest']=$facture['nom'];
	$cmd[$envoi['facturid']]['cp']=$facture['codepostal'];
	$cmd[$envoi['facturid']]['pays']=$facture['libelle'];
    }elseif(intval($envoi['livr'])>0){
	$fournisseur=$DB_site->query_first("SELECT * FROM fournisseur f INNER JOIN pays p ON (p.paysid=f.paysid) WHERE f.id=".intval($envoi['livr']));
	$cmd[$envoi['facturid']]['dest']=trim($fournisseur['societe']." ".$fournisseur['ville']);
	$cmd[$envoi['facturid']]['cp']=$fournisseur['cp'];
	$cmd[$envoi['facturid']]['pays']=$fournisseur['libelle'];
    }else
	$cmd[$envoi['facturid']]['dest']=$envoi['trspt'];

    if($envoi['enlev']!=0){
	$enlev=$DB_site->query_first("SELECT * FROM fournisseur WHERE id=".$envoi['enlev']);
	$cmd[$envoi['facturid']]['enl']=$enlev['societe']." ".$enlev['ville'];
    }else
	$cmd[$envoi['facturid']]['enl']=$fourn;
  }  


  $ret="<table class=\"tbl\">
  <thead>
  <tr><td colspan=\"10\" class=\"title\">Liste des commandes en livraison $txttitre</td></tr>
  <tr><td>Date livraison</td><td>Lieu enlevement</td><td>N° Cde</td><td>Poids total</td><td>Destination</td><td>CP</td><td>Pays</td><td style=\"color:red;\">Vos remarques</td></tr>
  </thead>";

  foreach($cmd as $facturid=>$efldata){
	
	$ret.="<tr onmouseover=\"this.style.backgroundColor='#cccccc';\" onmouseout=\"this.style.backgroundColor='';\">
	  <td>".date("d/m/Y",$efldata['outdate'])."</td>
	  <td>".$efldata['enl']."</td>
	  <td>".$facturid."</td>
	  <td>".$efldata['poids']." kg</td>
	  <td>".$efldata['dest']."</td>
	  <td>".$efldata['cp']."</td>
	  <td>".$efldata['pays']."</td>
	  <td></td>
	</tr>";
  }

  $ret.="</table>";
  
  return $ret;

}

function suivi_insls($fourn,$ts,$order="facturid",$late=null){
  global $DB_site,$types;

   $tomorrow=$ts+86399;
  if($order=="") $order="facturid";

  $destinataire=$type="";
  $sql="SELECT ef.facturid,efl.indate,efl.outdate,ef.destinataire,lf.artcode,efl.expediteur,efl.type,ef.trspt,efl.qte,ef.id,efl.ligne_facture_id,ef.enlev FROM envoi_fournisseur_ligne efl INNER JOIN envoi_fournisseur ef ON (efl.efid=ef.id) INNER JOIN lignefacture lf ON (lf.lignefactureid=efl.ligne_facture_id) WHERE ";
  if($late==null){
    $sql.="(efl.indate BETWEEN $ts AND $tomorrow)";
    $txttitre=" du ".date("d/m/Y",$ts);
    $tblid="tbltrspt".$ts;
  }else{
    $sql.=" efl.indate $late $ts AND efl.indate>0";
    if($late=="<=") $txttitre=" en retard ou prévues ce jour ".date("d/m/Y");
    if($late==">") $txttitre=" futur";
    
  }
  $sql.=" AND ef.enlevement=0 AND ef.destinataire='".addslashes($fourn)."' AND efl.type='sls' ORDER BY ".$order;

  $requete=$DB_site->query($sql);
  if($DB_site->num_rows($requete)==0) return false;
  $cmd=array();

  while($envoi=$DB_site->fetch_array($requete)){
    $article=$DB_site->query_first("SELECT artcode_eurocave,libelleFR,(emballage_poids1+emballage_poids2+emballage_poids3) AS emballage_poids FROM article WHERE artcode='$envoi[artcode]'");
   
    $cmd[$envoi['facturid']]['id']=$envoi['id'];
    $cmd[$envoi['facturid']]['indate']=$envoi['indate'];
    $cmd[$envoi['facturid']]['outdate']=$envoi['outdate'];
    $cmd[$envoi['facturid']]['poids']+=$article['emballage_poids'];
    $cmd[$envoi['facturid']]['desc'].=$article['libelleFR']."<br>";
    if(strtolower($envoi['trspt'])=="client particulier"){
	$facture=$DB_site->query_first("SELECT f.facturid,f.nom,f.prenom,f.codepostal,f.paysid,p.libelle FROM facture f INNER JOIN pays p USING (paysid) WHERE facturid=".$envoi['facturid']);
	$cmd[$envoi['facturid']]['dest']=$facture['nom'];
	$cmd[$envoi['facturid']]['cp']=$facture['codepostal'];
	$cmd[$envoi['facturid']]['pays']=$facture['libelle'];
    }elseif(strtolower($envoi['trspt'])=="client professionnel"){
	$facture=$DB_site->query_first("SELECT f.facturid,f.raisonsociale,f.nom,f.prenom,f.codepostal,f.paysid,p.libelle FROM facture f INNER JOIN pays p USING (paysid) WHERE facturid=".$envoi['facturid']);
	$cmd[$envoi['facturid']]['dest']=$facture['raisonsociale']." ".$facture['nom'];
	$cmd[$envoi['facturid']]['cp']=$facture['codepostal'];
	$cmd[$envoi['facturid']]['pays']=$facture['libelle'];
    }else{
	$cmd[$envoi['facturid']]['dest']=$envoi['trspt'];
    }

    if($envoi['enlev']<>0){
	$enlev=$DB_site->query_first("SELECT * FROM fournisseur WHERE id=".$envoi['enlev']);
	$cmd[$envoi['facturid']]['enl']=$enlev['societe'];
    }

  }  


    $ret="<table class=\"tbl\">
    <thead>
    <tr><td colspan=\"10\" class=\"title\">Liste des commandes en attente de réception en vos locaux $txttitre</td></tr>
    <tr><td>Date réception</td><td>Lieu réception</td><td>N° Cde</td><td>Désignation</td><td>Poids total</td><td>Provenance</td><td>Destination</td><td>CP</td><td>Pays</td><td style=\"color:red;\">Vos remarques</td></tr>
    </thead>";

    foreach($cmd as $facturid=>$efldata){
	
	$ret.="<tr onmouseover=\"this.style.backgroundColor='#cccccc';\" onmouseout=\"this.style.backgroundColor='';\">
	  <td>".date("d/m/Y",$efldata['indate'])."</td>
	  <td>$fourn</td>
	  <td>".$facturid."</td>
	  <td>".$efldata['desc']."</td>
	  <td>".$efldata['poids']." kg</td>
	  <td>".$efldata['enl']."</td>
	  <td>".$efldata['dest']."</td>
	  <td>".$efldata['cp']."</td>
	  <td>".$efldata['pays']."</td>
	  <td></td>
	</tr>";
    }

    $ret.="</table>";
  
 

  return $ret;

}

function suivi_outsls($fourn,$ts,$order="facturid",$late=null){
  global $DB_site,$types;

   $tomorrow=$ts+86399;
  if($order=="") $order="facturid";

  $destinataire=$type="";
  $sql="SELECT ef.facturid,efl.indate,efl.outdate,ef.destinataire,lf.artcode,efl.expediteur,efl.type,ef.trspt,efl.qte,ef.id,efl.ligne_facture_id,ef.enlev FROM envoi_fournisseur_ligne efl INNER JOIN envoi_fournisseur ef ON (efl.efid=ef.id) INNER JOIN lignefacture lf ON (lf.lignefactureid=efl.ligne_facture_id) WHERE ";
  if($late==null){
    $sql.="(efl.outdate BETWEEN $ts AND $tomorrow)";
    $txttitre=" du ".date("d/m/Y",$ts);
  }else{
    $sql.=" efl.outdate $late $ts AND efl.indate>0";
    if($late=="<=") $txttitre=" en retard ou prévues ce jour ".date("d/m/Y");
    if($late==">") $txttitre=" futur";
    
  }
  $sql.=" AND ef.livraison=0 AND ef.enlevement>0 AND ef.destinataire='".addslashes($fourn)."' AND efl.type='sls' ORDER BY ".$order;

  $requete=$DB_site->query($sql);
  if($DB_site->num_rows($requete)==0) return false;
  $cmd=array();

  while($envoi=$DB_site->fetch_array($requete)){
    $article=$DB_site->query_first("SELECT artcode_eurocave,libelleFR,(emballage_poids1+emballage_poids2+emballage_poids3) AS emballage_poids FROM article WHERE artcode='$envoi[artcode]'");
   
    $cmd[$envoi['facturid']]['id']=$envoi['id'];
    $cmd[$envoi['facturid']]['indate']=$envoi['indate'];
    $cmd[$envoi['facturid']]['outdate']=$envoi['outdate'];
    $cmd[$envoi['facturid']]['poids']+=$article['emballage_poids'];
    $cmd[$envoi['facturid']]['desc'].=$article['libelleFR']."<br>";
    if(strtolower($envoi['trspt'])=="client particulier"){
	$facture=$DB_site->query_first("SELECT f.facturid,f.nom,f.prenom,f.codepostal,f.paysid,p.libelle FROM facture f INNER JOIN pays p USING (paysid) WHERE facturid=".$envoi['facturid']);
	$cmd[$envoi['facturid']]['dest']=$facture['nom'];
	$cmd[$envoi['facturid']]['cp']=$facture['codepostal'];
	$cmd[$envoi['facturid']]['pays']=$facture['libelle'];
    }elseif(strtolower($envoi['trspt'])=="client professionnel"){
	$facture=$DB_site->query_first("SELECT f.facturid,f.raisonsociale,f.nom,f.prenom,f.codepostal,f.paysid,p.libelle FROM facture f INNER JOIN pays p USING (paysid) WHERE facturid=".$envoi['facturid']);
	$cmd[$envoi['facturid']]['dest']=$facture['raisonsociale']." ".$facture['nom'];
	$cmd[$envoi['facturid']]['cp']=$facture['codepostal'];
	$cmd[$envoi['facturid']]['pays']=$facture['libelle'];
    }else{
	$cmd[$envoi['facturid']]['dest']=$envoi['trspt'];
    }

  }  


    $ret="<table class=\"tbl\">
    <thead>
    <tr><td colspan=\"10\" class=\"title\">Liste des commandes en livraison $txttitre</td></tr>
    <tr><td>Date livraison</td><td>N° Cde</td><td>Poids total</td><td>Destination</td><td>CP</td><td>Pays</td><td style=\"color:red;\">Vos remarques</td></tr>
    </thead>";

    foreach($cmd as $facturid=>$efldata){
	
	$ret.="<tr onmouseover=\"this.style.backgroundColor='#cccccc';\" onmouseout=\"this.style.backgroundColor='';\">
	  <td>".date("d/m/Y",$efldata['outdate'])."</td>
	  <td>".$facturid."</td>
	  <td>".$efldata['poids']." kg</td>
	  <td>".$efldata['dest']."</td>
	  <td>".$efldata['cp']."</td>
	  <td>".$efldata['pays']."</td>
	  <td></td>
	</tr>";
    }

    $ret.="</table>";
  
 

  return $ret;

}

function apercu($dest,$sujet,$text){
      $body="Destinataire:".$dest."<br>Sujet:".$sujet."<br>".$text;
      if($dest=="0") $body="Pas d'envoi de mail";
      print "<div style=\"border:solid thin black;\">
		$body
		</div>";
}

?>