<?php


$preview=isset($_GET['preview']);

if($preview){
  require_once "../admin_global.php";
  include_once "../../class/stock.class.php";
  include_once "../../class/template.class.php";
  include_once "../../class/facture.class.php";
  include_once "../../class/fournisseur.class.php";
  include_once "adv.class.php";
  include_once "envoi_fournisseur.php";
}

$adv_facture=$DB_site2->query_first("SELECT * FROM facture f, pays p WHERE f.paysid=p.paysid AND f.facturid=".$facturid);

    // enregistrement suivi commande
if(isset($suivitxt)){
     $advdate1=$advdate2=$advdate3=0;
     if($_POST['advdate1']!=""){
	$sp=preg_split('/[- :\/h]/',$_POST['advdate1']);
	$advdate1=mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
     }
     if($_POST['advdate2']!=""){
	$sp=preg_split('/[- :\/h]/',$_POST['advdate2']);
	$advdate2=mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
     }
     if($_POST['advdate3']!=""){
	$sp=preg_split('/[- :\/h]/',$_POST['advdate3']);
	$advdate3=mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
     }
     if(!$preview) $DB_site2->query("UPDATE facture SET suivi2='".$_POST['suivitxt']."',advdate1=$advdate1, advdate2=$advdate2, advdate3=$advdate3 WHERE facturid=".$facturid);
     $action="editer";
}

if(isset($_GET['ef']) && isset($_GET['ts'])){
    $envoi=$DB_site2->query_first("SELECT efl.indate,efl.outdate,efl.type,lf.artcode,ef.destinataire FROM envoi_fournisseur_ligne efl INNER JOIN lignefacture lf ON (lf.lignefactureid=efl.ligne_facture_id) INNER JOIN envoi_fournisseur ef ON (ef.id=efl.efid) WHERE ef.id=".$_GET['ef']);
    $DB_site2->query("UPDATE envoi_fournisseur SET enlevement=".intval($_GET['ts'])." WHERE id=".intval($_GET['ef']));
    $suivitxt="Changement date ".($_GET['upddatetype']=="enlevement"?"enlèvement":"livraison")." commande ".$envoi['destinataire']." : ".date("d/m/Y",$envoi[$_GET['upddatetype']])." remplacé par ".date("d/m/Y",$_GET['ts']);
    $DB_site2->query("INSERT INTO suivi_commande SET facturid=$facturid, date=".time().", commercial='".$user_info[username]."', suivi='".mysqli_real_escape_string($DB_site2->link_id,$suivitxt)."'");

}
if(isset($_GET['efl']) && isset($_GET['ts'])){
    $envoi=$DB_site2->query_first("SELECT efl.indate,efl.outdate,efl.type,lf.artcode,ef.destinataire FROM envoi_fournisseur_ligne efl INNER JOIN lignefacture lf ON (lf.lignefactureid=efl.ligne_facture_id) INNER JOIN envoi_fournisseur ef ON (ef.id=efl.efid) WHERE efl.id=".$_GET['efl']);
    $DB_site2->query("UPDATE envoi_fournisseur_ligne SET ".$_GET['upddatetype']."date=".intval($_GET['ts'])." WHERE id=".intval($_GET['efl']));
    $suivitxt="Changement date ".($_GET['upddatetype']=="in"?"enlèvement":"livraison")." commande ".$envoi['destinataire']." ".$envoi['artcode']." : ".date("d/m/Y",$envoi[$_GET['upddatetype']."date"])." remplacé par ".date("d/m/Y",$_GET['ts']);
    $DB_site2->query("INSERT INTO suivi_commande SET facturid=$facturid, date=".time().", commercial='".$user_info[username]."', suivi='".mysqli_real_escape_string($DB_site2->link_id,$suivitxt)."'");
}
if(isset($_GET['eflall']) && isset($_GET['ts'])){
    $envois=explode(",",$_GET['eflall']);
    foreach($envois as $envoi){
        $elf=$DB_site2->query_first("SELECT efl.indate,efl.outdate,efl.type,lf.artcode,ef.destinataire FROM envoi_fournisseur_ligne efl INNER JOIN lignefacture lf ON (lf.lignefactureid=efl.ligne_facture_id) INNER JOIN envoi_fournisseur ef ON (ef.id=efl.efid) WHERE efl.id=".$envoi);
    $DB_site2->query("UPDATE envoi_fournisseur_ligne SET ".$_GET['upddatetype']."date=".intval($_GET['ts'])." WHERE id=".intval($envoi));
    $suivitxt="Changement date ".($_GET['upddatetype']=="in"?"enlèvement":"livraison")." commande ".$elf['destinataire']." ".$elf['artcode']." : ".date("d/m/Y",$elf[$_GET['upddatetype']."date"])." remplacé par ".date("d/m/Y",$_GET['ts']);
    $DB_site2->query("INSERT INTO suivi_commande SET facturid=$facturid, date=".time().", commercial='".$user_info[username]."', suivi='$suivitxt'");
    }
}
if(isset($_GET['comment']) && isset($_GET['efid'])){
    $DB_site2->query("UPDATE envoi_fournisseur SET comment='".mysqli_real_escape_string($DB_site2->link_id,$_GET['comment'])."' WHERE id=".$_GET['efid']);
}

if(isset($_GET['advcomment'])){
    $DB_site2->query("INSERT INTO suivi_commande SET type='adv".$_GET['commenttype']."', facturid=".$facturid.", date=".time().", commercial='".$user_info['username']."', suivi='".mysqli_real_escape_string($DB_site2->link_id,$_GET['advcomment'])."'");
}

if(isset($_GET['trackid']) && isset($_GET['efid'])){
    $DB_site2->query("UPDATE envoi_fournisseur SET trackid='".$_GET['trackid']."' WHERE id=".$_GET['efid']);
}

if(isset($_GET['lid']) && isset($_GET['mtype']) && isset($_GET['cal_all'])){
    $lignesfac=explode(",",$_GET['lid']);
    foreach($lignesfac as $lignefac){
     $sql2="";
     $req=$DB_site2->query("SELECT * FROM calendrier WHERE type='".$_GET['mtype']."' AND objid=".$lignefac);
     if(isset($_GET['txt'])) $sql2="txt='".addslashes($_GET['txt'])."'";
     if(isset($_GET['ts'])) $sql2.="`".$_GET['typedate']."`=".intval($_GET['ts']);
     if($DB_site2->num_rows($req)==0){
        $sql="INSERT INTO calendrier SET calendrier.type='".$_GET['mtype']."', calendrier.objid=".$lignefac.", expediteur='".$user_info['username']."',".$sql2;
     }
     else{
        $sql="UPDATE calendrier SET ".$sql2." WHERE type='".$_GET['mtype']."' AND objid=".$lignefac;
     }
     $DB_site2->query($sql);
    }
}

if(isset($_GET['lid']) && isset($_GET['mtype']) && empty($_GET['cal_all'])){
     $req=$DB_site2->query("SELECT * FROM calendrier WHERE type='".$_GET['mtype']."' AND objid=".$_GET['lid']);
     if(isset($_GET['txt'])) $sql2="txt='".addslashes($_GET['txt'])."'";
     if(isset($_GET['ts'])) $sql2.="`".$_GET['typedate']."`=".intval($_GET['ts']);
     if($DB_site2->num_rows($req)==0)
	$sql="INSERT INTO calendrier SET calendrier.type='".$_GET['mtype']."', calendrier.objid=".$_GET['lid'].", expediteur='".$user_info['username']."',".$sql2;
     else
	$sql="UPDATE calendrier SET ".$sql2." WHERE type='".$_GET['mtype']."' AND objid=".$_GET['lid'];
	
     $DB_site2->query($sql);

}

if(isset($_GET['datelivr']) && isset($_GET['efid'])){
    $ef=new envoi_fournisseur($DB_site2,$_GET['efid']);
    $ef->date_livr=$_GET['datelivr'];
    $ef->update();
    
    $ef->loadlignes();
    if($ef->lignes) {
	    foreach($ef->lignes as $efl){
			$lf=$efl->lignefacture();
			$stock=new stock($DB_site2);
			$stockitems=$stock->search($lf->artid,$ef->lieu_livr,"facture ".$facturid,0);
			foreach($stockitems as $item){
			    $item->state=2;
			    $item->update();
			}
	    }
	}
}
if(isset($_GET['dateenlev']) && isset($_GET['efid'])){
    $DB_site2->query("UPDATE envoi_fournisseur SET enlevement=".$_GET['dateenlev']." WHERE id=".$_GET['efid']);
    
    $req=$DB_site2->query("SELECT artid,lf.qte FROM envoi_fournisseur_ligne INNER JOIN lignefacture lf ON (lignefactureid=ligne_facture_id) AND facture='STOCK' AND efid=".intval($_GET['efid']));
    while($efl=$DB_site2->fetch_array($req)) {
	$stock=new stock($DB_site2);
	$stockitems=$stock->search($efl['artid'],null,"facture ".$facturid,2);
	$qte=0;
	foreach($stockitems as $item){
	    $item->state=3;
	    $item->update();
	    $qte++;
	    if($qte==$efl['qte']) break;
	}
    }
}
if(isset($_GET['newtrspt']) && isset($_GET['efid']))
    $DB_site2->query("UPDATE envoi_fournisseur SET trspt='".addslashes($_GET['newtrspt'])."' WHERE id=".$_GET['efid']);

// Enregistrement AR 
if(isset($_GET['ar']) && isset($_POST['ar_valide'])){

    $ar=new adv_ar($DB_site2);
    $ar->advid=$_GET['ar'];
    $d=date_create_from_format("d/m/Y",$_POST['ar_date']);
    $ar->date_ar=$d->getTimestamp();
    $d=date_create_from_format("d/m/Y",$_POST['ar_date_recu']);
    $ar->date=$d->getTimestamp();
    $ar->utilisateur=$user_info['username'];
    $ar->numero=$_POST['ar_num'];
    $ar->valide=$_POST['ar_valide'];
    $ar->save();
    
    $adv=new adv($DB_site2,$ar->advid);
        
    $updindate=0;
    $updoutdate=0;
    $updprice=false;
    $suivi="";

    $lfids=explode(",",$_POST['arlfids']);
    $postkeys=array_keys($_POST);
    foreach($lfids as $lfid){
		$ar->save_field("artcode_".$lfid,$_POST['artcode_'.$lfid]);
		$ar->save_field("qte_".$lfid,$_POST['qte_'.$lfid]);
		$ar->save_field("date_".$lfid,$_POST['date_'.$lfid]);
		if(isset($_POST['livr_'.$lfid])) 
			$ar->save_field("livr_".$lfid,$_POST['livr_'.$lfid]);
		if(isset($_POST['trspt_'.$lfid])) 
			$ar->save_field("trspt_".$lfid,$_POST['trspt_'.$lfid]);
		$ar->save_field("paht_".$lfid,$_POST['paht_'.$lfid]);
		$ar->save_field("info_".$lfid,$_POST['info_'.$lfid]);

		$kopts=preg_grep("/^option_[0-9]+_".$lfid."$/",$postkeys);
		foreach($kopts as $kopt){
		    $ar->save_field($kopt,$_POST[$kopt]);
                }
    }
    
    $updateef=false;
    $envoi_fournisseur=new envoi_fournisseur($DB_site2,$adv->efid);
    foreach($envoi_fournisseur->lignes as $efl){
        if($_POST['ardate_'.$efl->lignefactureid]!=""){
            $d=date_create_from_format("d/m/Y",$_POST['ardate_'.$efl->lignefactureid]);
            if($efl->origin_in==0) $efl->origin_in=$efl->indate;
            $suivi.="<br>Date expédition ".$adv->fields['ref_'.$efl->lignefactureid]." : ".date("d/m/Y",$efl->indate)." => ".date("d/m/Y",$d->getTimestamp());
            $efl->indate=$d->getTimestamp();
            $updateef=true;
        }
        if($_POST['arlivr_'.$efl->lignefactureid]!=""){
            $d=date_create_from_format("d/m/Y",$_POST['arlivr_'.$efl->lignefactureid]);
            if($efl->origin_out==0) $efl->origin_out=$efl->outdate;
            $suivi.="<br>Date livraison ".$adv->fields['ref_'.$efl->lignefactureid]." : ".date("d/m/Y",$efl->outdate)." => ".date("d/m/Y",$d->getTimestamp());
            $efl->outdate=$d->getTimestamp();
            $updateef=true;
        }
        if($_POST['newpriceval_'.$efl->lignefactureid]!=""){
            $suivi.="<br>Prix achat ".$adv->fields['ref_'.$efl->lignefactureid]." : ".$efl->paht." => ".$_POST['newpriceval_'.$efl->lignefactureid];
            $efl->paht=$_POST['newpriceval_'.$efl->lignefactureid];
            $adv->save_field('price1_'.$efl->lignefactureid,$efl->paht);
            $updateef=true;
        }
        if($_POST['newtrsptval_'.$efl->lignefactureid]!=""){
            $suivi.="<br>Prix transport ".$adv->fields['mazetcout']." => ".$_POST['newtrsptval_'.$efl->lignefactureid];
            $adv->save_field('mazetcout',floatval($_POST['newtrsptval_'.$efl->lignefactureid]));
            $updateef=true;
        }

        $efl->update();
    }

    if($updateef){
        $envoi_fournisseur->update_tarif();
        $DB_site2->query("INSERT INTO suivi_commande SET facturid=".$_GET ['facturid'].", date=".time().", commercial='".$user_info['username']."', suivi='".mysqli_real_escape_string($DB_site2->link_id,$suivi)."'");
    }
    
    unset($suivi);

    //Header location impossible puisque déjà défini
    ?>
    <script type="text/javascript"> window.location = "facture.php?action=editer&facturid=<?php echo $_GET ['facturid']; ?>#adv";</script>
    <?php
}

//Modification AR
if(isset($_POST['ar_id2']) && isset($_POST['ar_valide2'])){
	$ar=new adv_ar($DB_site2,$_POST['ar_id2']);
        $ar->advid=$_POST['adv_id2'];
        $d=date_create_from_format("d/m/Y",$_POST['ar_date2']);
        $ar->date_ar=$d->getTimestamp();
        $d=date_create_from_format("d/m/Y",$_POST['ar_date_recu2']);
        $ar->date=$d->getTimestamp();
        $ar->utilisateur=$user_info['username'];
        $ar->numero=$_POST['ar_num2'];
        $ar->valide=$_POST['ar_valide2'];
        $ar->save();
        
        $adv=new adv($DB_site2,$ar->advid);
            
        $updindate=0;
        $updoutdate=0;
        $updprice=false;
        $suivi="";

        $lfids=explode("-",$_POST['arlfids2']);
        $postkeys=array_keys($_POST);
    

        $updprice=false;
        $envoi_fournisseur=new envoi_fournisseur($DB_site2,$adv->efid);
        foreach($envoi_fournisseur->lignes as $efl){
        
            if($_POST['ardate2_'.$efl->lignefactureid]!=""){
                    $d=date_create_from_format("d/m/Y",$_POST['ardate2_'.$efl->lignefactureid]);
                    if($efl->origin_in==0) $efl->origin_in=$efl->indate;
                    $suivi.="<br>Date expédition ".$adv->fields['ref_'.$efl->lignefactureid]." : ".date("d/m/Y",$efl->indate)." => ".date("d/m/Y",$d->getTimestamp());
                    $efl->indate=$d->getTimestamp();
            }

            if($_POST['arlivr2_'.$efl->lignefactureid]!=""){
                    $d=date_create_from_format("d/m/Y",$_POST['arlivr2_'.$efl->lignefactureid]);
                    if($efl->origin_out==0) $efl->origin_out=$efl->outdate;
                    $suivi.="<br>Date livraison ".$adv->fields['ref_'.$efl->lignefactureid]." : ".date("d/m/Y",$efl->outdate)." => ".date("d/m/Y",$d->getTimestamp());
                    $efl->outdate=$d->getTimestamp();
            }

            if($_POST['newpriceval2_'.$efl->lignefactureid]!=""){
                    $suivi.="<br>Prix achat ".$adv->fields['ref_'.$efl->lignefactureid]." : ".$efl->paht." => ".$_POST['newpriceval2_'.$efl->lignefactureid];
                    $efl->paht=$_POST['newpriceval2_'.$efl->lignefactureid];
                    $adv->save_field('price1_'.$efl->lignefactureid,$efl->paht);
                    $updprice=true;
            }

            $efl->update();
        }
        
        $trsptval=0;
        if($_POST['newtrsptval2_'.$_POST['ar_id2']]!=""){
            $trsptval=floatval($_POST['newtrsptval2_'.$_POST['ar_id2']]);
            $suivi.="<br>Prix transport ".$adv->fields['mazetcout']." => ".$_POST['newtrsptval2_'.$_POST['ar_id2']];
            $adv->save_field('mazetcout',$trsptval);
            $updprice=true;
        }

        if($updprice) $envoi_fournisseur->update_tarif($trsptval);
        $DB_site2->query("INSERT INTO suivi_commande SET facturid=".$_GET ['facturid'].", date=".time().", commercial='".$user_info['username']."', suivi='".mysqli_real_escape_string($DB_site2->link_id,$suivi)."'");
        
        unset($suivi);
}
    
if(isset($_GET['offre']) || isset($_GET['offres'])){
    if(isset($_GET['offres']))
	$offreids=explode(",",$_GET['offres']);
    else
	$offreids=array($_GET['offre']);
    
    $adv=new adv($DB_site2,$_GET['advid']);
    $adv->lignes=array();
    
    if(isset($_GET['offre']) && $_GET['offre']==0){
	$adv->delete(true,true,false);
    }else{
	foreach($offreids as $offreid){
	    $offre=new offre_fournisseur($DB_site2,$offreid);
	    
	    $artids=$offre->artids_facture($facturid,true);
	    $pahtart=$offre->paht_articles($artids);
	    foreach($pahtart as $artid=>$listeprix){
		$total=0;
		$reqlf=$DB_site2->query("SELECT lignefactureid,qte FROM lignefacture WHERE factureid=".$facturid." AND artid=".$artid);
		$article=$DB_site2->query_first("SELECT artcode_eurocave,ecopartht,libelleFR FROM article WHERE artid=".$artid);
		
		while($lf=$DB_site2->fetch_array($reqlf)){
		    $adv->add_lignefacture($lf['qte'],$lf['lignefactureid']);
		    foreach($listeprix as $paht=>$qte){
			$listesprix[$lf['lignefactureid']][$paht]=$qte;
		    }
			
		    
		    $adv->save_field("desc_".$lf['lignefactureid'],$article['libelleFR']);
		    $adv->save_field("ref_".$lf['lignefactureid'],$article['artcode_eurocave']);
		    $adv->save_field("ecopartht_".$lf['lignefactureid'],$article['ecopartht']);
		}
	    }
	}

	foreach($adv->lignes as $lfid=>$qte){
	    $remainqte=$qte;
	    $total=0;
	    while($remainqte>0){
		$pahtmin=min(array_keys($listesprix[$lfid]));
		$qtemax=$listesprix[$lfid][$pahtmin];
		$subqte=($remainqte>$qtemax?$qtemax:$remainqte);
		$remainqte-=$subqte;
		$listesprix[$lfid][$pahtmin]-=$subqte;
		if($listesprix[$lfid][$pahtmin]==0) unset($listesprix[$lfid][$pahtmin]);
		$adv->save_field("listeprix_".$lfid."[".$pahtmin."]",$subqte);
		$total+=$pahtmin*$subqte;
	    }
	    $adv->save_field("price1_".$lfid,$total/$qte);
	}
	
	$mails=array();
	$req=$DB_site2->query("SELECT *,fc.devise FROM fournisseur LEFT OUTER JOIN fournisseur_condition fc USING(societe) WHERE `type`='fournadv' AND societe='".mysqli_real_escape_string($DB_site2->link_id,$offre->fournisseur)."'");
	while($f=$DB_site2->fetch_array($req)){
	    $mails[]=$f['mail'];
	    $devise=$f['devise'];
	}
	
	$adv->destinataire=$offre->fournisseur;
	$adv->devise=$devise;
	$adv->update();
	$adv->save_field("offre",implode(",",$offreids));
	$adv->save_field("lieustock","0");
	$adv->save_field("fournisseur",implode(";",$mails));
	$adv->update_step(2);
	$adv->update_step(3);
	
    }
}

    // envoi commande au fournisseur
if($actionadv=="advfournisseur" || $actionadv=="advfournisseur2"){
    include_once(SITE_PATH."/class/fpdf.php");
    include_once("pdf.class.php");
    include_once(SITE_PATH."/class/php-barcode.php");
    include_once("gls.class.php"); //GLS
    include_once("webtel.class.php"); //Schenker & Heppner
    include_once(SITE_PATH."/class/tnt.class.php"); //TNT
    include_once("heppner.class.php");
    include_once("girard_agediss.class.php");
    include_once("schenker.class.php");
    //include_once(SITE_PATH."/class/girard.class.php"); //Girard Agediss

    $adv=new adv($DB_site2,$advid);

    switch($adv->type){
		case "fourn":
		case "stock":
			include("mail_fourn.php");
			mail_fourn($adv,$preview);
			break;

		case "trspt":
			include("mail_trspt.php");
			mail_trspt($adv,$preview);
			break;

		case "sls":
			include("mail_sls.php");
			mail_sls($adv,$preview);
			break;

		case "miseenservice":
		   include("mail_service.php");
		   mail_service($adv,$preview);
		   break;
    }

    unset($advid);
    unset($type);
}

function apercu($dest,$sujet,$text){
      $body="Destinataire:".$dest."<br>Sujet:".$sujet."<br>".$text;
      if($dest=="0") $body="Pas d'envoi de mail";
      print "<html>
		<head>
		<title>Aperçu</title>
		<META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=utf8\">
		</head>
		<body>
		$body
		</body>
		</html>";
}

function pj($fields, $name, $tmp, $size, $userid){
	global $DB_site2;
	global $user_info;
	global $facturid;

	$extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $path = $_SERVER['DOCUMENT_ROOT']."/admin/userfiles/";
    $filename = $fields."_".date("d-m-Y").".".$extension;

    $DB_site2->query("INSERT INTO user_files SET userid=".$userid.", facturid=".$facturid.", date=".time().", utilisateur='".$user_info['username']."', name='".mysqli_real_escape_string($DB_site2->link_id,$filename)."', size=".$size.", ext='".$extension."'");
    $idf = $DB_site2->insert_id();

    if(!move_uploaded_file($tmp, $path.$idf.".".$extension)){
         $DB_site2->query("UPDATE user_files SET state=-1 WHERE id=$idf");
    }else{
         $DB_site2->query("UPDATE user_files SET state=1 WHERE id=$idf");
    }
}

?>
