<?php

$preview=isset($_GET['preview']);

if($preview){
  require_once "admin_global.php";
  include_once "../class/stock.class.php";
}

$adv_facture=$DB_site2->fetch_array($DB_site2->query("SELECT * FROM facture f, pays p WHERE f.paysid=p.paysid AND f.facturid=".$facturid));

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

if(isset($_GET['efl']) && isset($_GET['ts'])){
    $envoi=$DB_site2->query_first("SELECT efl.indate,efl.outdate,efl.type,lf.artcode,ef.destinataire FROM envoi_fournisseur_ligne efl INNER JOIN lignefacture lf ON (lf.lignefactureid=efl.ligne_facture_id) INNER JOIN envoi_fournisseur ef ON (ef.id=efl.efid) WHERE efl.id=".$_GET['efl']);
    $DB_site2->query("UPDATE envoi_fournisseur_ligne SET ".$_GET['upddatetype']."date=".intval($_GET['ts'])." WHERE id=".intval($_GET['efl']));
    $suivitxt="Changement date ".($_GET['upddatetype']=="in"?"enl�vement":"livraison")." commande ".$envoi['destinataire']." ".$envoi['artcode']." : ".date("d/m/Y",$envoi[$_GET['upddatetype']."date"])." remplac� par ".date("d/m/Y",$_GET['ts']);
    $DB_site2->query("INSERT INTO suivi_commande SET facturid=$facturid, date=".time().", commercial='".$user_info[username]."', suivi='$suivitxt'");
}
if(isset($_GET['deladvcmd'])){
    $DB_site2->query("DELETE FROM envoi_fournisseur WHERE id=".$_GET['deladvcmd']);
    $DB_site2->query("DELETE FROM envoi_fournisseur_ligne WHERE efid=".$_GET['deladvcmd']);
}
if(isset($_GET['comment']) && isset($_GET['efid'])){
    $DB_site2->query("UPDATE envoi_fournisseur SET comment='".addslashes($_GET['comment'])."' WHERE id=".$_GET['efid']);
}
if(isset($_GET['trackid']) && isset($_GET['efid'])){
    $DB_site2->query("UPDATE envoi_fournisseur SET trackid='".$_GET['trackid']."' WHERE id=".$_GET['efid']);
}

if(isset($_GET['lid']) && isset($_GET['type'])){

     $req=$DB_site2->query("SELECT * FROM calendrier WHERE type='".$_GET['type']."' AND objid=".$_GET['lid']);
     if(isset($_GET['txt'])) $sql2="txt='".$_GET['txt']."'";
     if(isset($_GET['ts'])) $sql2.="`".$_GET['typedate']."`=".intval($_GET['ts']);
     if($DB_site2->num_rows($req)==0)
	$sql="INSERT INTO calendrier SET calendrier.type='".$_GET['type']."', calendrier.objid=".$_GET['lid'].", expediteur='".$user_info['username']."',".$sql2;
     else
	$sql="UPDATE calendrier SET ".$sql2." WHERE type='".$_GET['type']."' AND objid=".$_GET['lid'];
	
     $DB_site2->query($sql);

}

if(isset($_GET['datelivr'])){
    $DB_site2->query("UPDATE envoi_fournisseur SET livraison=".$_GET['datelivr']." WHERE id=".$_GET['efid']);
}
if(isset($_GET['dateenlev'])){
    $DB_site2->query("UPDATE envoi_fournisseur SET enlevement=".$_GET['dateenlev']." WHERE id=".$_GET['efid']);
}
if(isset($_GET['newtrspt']) && isset($_GET['efid']))
    $DB_site2->query("UPDATE envoi_fournisseur SET trspt='".addslashes($_GET['newtrspt'])."' WHERE id=".$_GET['efid']);

    // envoi commande au fournisseur
if($actionadv=="advfournisseur" || $actionadv=="advfournisseur2"){
    $stock=new stock($DB_site2);
    $subject="";
    switch($_POST['typecmd']){
	case "new":
	$subject="Commande n�".$facturid;
	$newcmd=true;
	if($_POST['lieustock']>0) $txtstock="sortie de stock pour la";
	$text.="Bonjour,<br>Veuillez trouver ci-joint une $txtstock nouvelle commande n� $facturid";
	$fin="Nous vous remercions de votre envoi, par mail ou fax, de votre confirmation de commande.<br>Cordialement<br>$user_info[nom_contact] - ACILYON - www.ma-cave-a-vin.fr<br>T�l : + 33.4.26.03.05.44 -  Fax: +33.4.26.03.05.49";
	break;

	case "delete":
	$subject="Annulation de la commande n�".$facturid;
	$newcmd=false;
	$text.="Bonjour,<br>Merci d'annuler les produits suivants de la commande n� $facturid";
	$fin="Merci de nous confirmer l'annulation de ces produits par retour de ce mail.<br>Cordialement<br>$user_info[nom_contact] - ACILYON - www.ma-cave-a-vin.fr<br>T�l : + 33.4.26.03.05.44 -  Fax: +33.4.26.03.05.49";
	break;

	default:
	$newcmd=true;
    }

    if($_POST['infocomplement']!="")
      $text.="<p>".nl2br($_POST['infocomplement'])."</p>";

    $imperatif=0;
    $tsindate=$tsoutdate=0;
    if($_POST['trsptmode']=="parenlevement"){
	  if($_POST['parenlevindate']!=""){
		  $sp=explode('/',$_POST['parenlevindate']);
		  $tsindate=mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
	  }
    }elseif($_POST['trsptmode']=="parvossoins"){
	  if($_POST['parvossoinsindate']!=""){
		$sp=explode('/',$_POST['parvossoinsindate']);
		$tsindate=mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
	  }
	  if($_POST['parvossoinsoutdate']!=""){
		$sp=explode('/',$_POST['parvossoinsoutdate']);
		$tsoutdate=mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
	  }
	  if($_POST['typedatelivrfourn']=="imperative") $imperatif=1;
    }

    $text.="<p><b>Produits :</b>";
    $paht=0;
    $ecopart=0;
    $garantie=0;
    $insertids=array();
    $annultxt;
    foreach($_POST['produits'] as $p){
            $lignefacture=$DB_site2->query_first("SELECT * FROM lignefacture l, article a WHERE l.artid=a.artid AND lignefactureid=".$p);
	    $artcode=$_POST['ref_'.$p];
	    if($_POST['lieustock']>0) $artcode=$lignefacture['artcode']." (".$_POST['ref_'.$p].")";
            $text.="<br><br>qte : ".$lignefacture['qte']." - ".$_POST['desc_'.$p]." R�f : $artcode";
            $caracts=$DB_site2->query("SELECT * FROM lignefacturecaracteristique WHERE lignefactureid = '$p' ORDER BY libelle_caract");    
	    if($DB_site2->num_rows($caracts)>0){
	      while ($caract=$DB_site2->fetch_array($caracts))
                  if($caract['libelle_caract']!="Garanties S�r�nit�") $text.=" - $caract[libelle_caract] : $caract[libelle_caractval]";
	    }
	    if($_POST['lieustock']==0){
	      if(isset($_POST['gar_'.$p])){
		  $garantie+=$_POST['garval_'.$p];
		  $text.="<br>+ Garantie fournisseur";
	      }
	      $text.=" (Prix Unit. ".$_POST['price1_'.$p]." &euro; + ".$lignefacture['ecopartht']." &euro;)";
	    }else{
	      if($lignefacture['coloris']!="") $text.="<br>- Coloris : ".$lignefacture['coloris'];
	      if($lignefacture['coloris_porte']!="") $text.="<br>- Coloris cadre : ".$lignefacture['coloris_porte'];
	      if($lignefacture['version_porte']!="") $text.="<br>- Version porte : ".$lignefacture['version_porte'];
	    }
	    $prixopt=0;
	    if(isset($_POST['caract_'.$p])){
		foreach($_POST['caract_'.$p] as $caractvalid){
		    $lfcaracteristique=$DB_site2->query_first("SELECT * FROM lignefacturecaracteristique WHERE lignefactureid=$p AND caractvalid=$caractvalid");
		    $text.="<br>+".$lfcaracteristique['libelle_caract']." ".$lfcaracteristique['libelle_caractval']." (".$_POST['caractval'][$caractvalid]." &euro;)";
		    if($_POST['lieustock']==0) $prixopt+=$_POST['caractval'][$caractvalid];
		}
	    }
            $paht+=$lignefacture['qte']*floatval($_POST['price1_'.$p]+$prixopt);
            $ecopart+=$lignefacture['qte']*$lignefacture['ecopartht'];
            if(!$preview){
	      if($newcmd==true){
		  $factureligne="";
		  if($_POST['lieustock']>0) $factureligne="STOCK";
		  $DB_site2->query("INSERT INTO envoi_fournisseur_ligne SET date=".mktime().", indate=$tsindate, outdate=$tsoutdate, origin_in=$tsindate, origin_out=$tsoutdate, expediteur='".$user_info['username']."', imperatif=".intval($imperatif).", type='fourn', paht=".(floatval($_POST['price1_'.$p])+$prixopt+floatval($_POST['garval_'.$p])).", ecopart=".floatval($lignefacture['ecopartht']).", qte=".intval($lignefacture['qte']).", ligne_facture_id=".$p.", facture='$factureligne'");
		  $insertids[]=mysqli_insert_id($DB_site2->link_id);
		  $srcdest=null;
		  $dest="facture ".$lignefacture['factureid'];
		  $stockstate=2;
	      }else{
		  $lf=$DB_site2->query_first("SELECT * FROM lignefacture WHERE lignefactureid=".$p);
		  $annultxt.=$lf['qte']." ".$lf['artcode']."<br>";
		  $DB_site2->query("DELETE FROM envoi_fournisseur_ligne WHERE type='fourn' AND ligne_facture_id=".$p);
		  $srcdest="facture ".$lignefacture['factureid'];
		  $dest="";
		  $stockstate=1;
	      }

	      if($_POST['lieustock']>0){
		      $stockartid=$stock->similaire($lignefacture['artid']);
		      $items=$stock->search($stockartid,$_POST['lieustock'],$srcdest,($stockstate==1?2:1));
		      if(count($items)>=$lignefacture['qte']){
			  $i=0;
			  foreach($items as $item){
			      $i++;
			      $item->dest=$dest;
			      $item->state=$stockstate;
			      $item->update();
			      if($i==$lignefacture['qte']) break;
			  }
		      }
		}
	   }
    }
    $text.="</p>";
    
    if($newcmd==true){
	$text.="<p><b>Infos produits</b>";
	if(isset($_POST['uncolis'])) $text.="<br>Attention : mettre tous les articles dans 1 seul colis";
	if(isset($_POST['palette'])) $text.="<br>Attention : Article(s) � mettre sur palette + cerclage pour le transport";
	if(isset($_POST['suremballage'])) $text.="<br>Attention :  Pr�voir un suremballage du produit + palette + cerclage pour le transport";
	$text.="</p>";

	if(!isset($_POST['nopj'])){
	    $text.="<p><b>Identification � mettre sur le colis</b>";
	    if(isset($_POST['blpj'])) $text.="<br>Bon de livraison";
	    if(isset($_POST['facturepj'])) $text.="<br>Facture";
	    if(isset($_POST['tntpj'])) $text.="<br>Bon FEDEX Express";
	    if(isset($_POST['glspj'])) $text.="<br>Bon GLS ou FEDEX Express";
	    if(isset($_POST['trsptpja4']) || isset($_POST['trsptpja5'])) $text.="<br>Note importante pour le transport<br>Etiquette fragile";
	    if(isset($_POST['otherpj'])) $text.="<br>".$_POST['otherpjname'];
	    if(isset($_POST['identpj'])) $text.="<br>Texte sur colis : Commande N� $facturid De Ma cave � vin - ACILYON pour $adv_facture[nom] $adv_facture[prenom] $adv_facture[codepostal] $adv_facture[ville] $adv_facture[libelle]";
	    $text.="</p>";
	}
    
	$trsptval=0;
	$livrid=-1;
	$text.="<p><b>D�lai de mise � disposition / livraison</b><br>";


	$tsindate=$tsoutdate=0;
	if($_POST['trsptmode']=="parenlevement"){
	    $text.="Enlevement � notre charge en vos locaux par ".$_POST['selecttrspt']." le ".$_POST['parenlevindate'];
	    if(isset($_POST['enlev2sls'])) $blfourn=$_POST['enlev2sls'];
	    $advtrspt=$_POST['selecttrspt'];
	}elseif($_POST['trsptmode']=="parvossoins"){
	  $txtlivr="Livraison au plus tard";
	  if($_POST['typedatelivrfourn']=="imperative") $txtlivr="Livraison <u>imp�rative</u>";
	  switch($_POST['trspt']){
	    case "fournisseur":
	    $text.="Livraison par vos soins par votre transporteur directement chez notre client professionnel
	    <br>$txtlivr : ".$_POST['parvossoinsoutdate'];
	    $advtrspt="transporteur fournisseur";
	    $livrid=0;
	    break;
	    case "colissimo":
	    $text.="Livraison par vos soins par colissimo directement chez notre client
	    <br>D�part maximum : ".$_POST['parvossoinsindate']."
	    <br>$txtlivr : ".$_POST['parvossoinsoutdate'];
	    $advtrspt="colissimo";
	    $livrid=0;
	    break;
	    case "asls":
	    $sls=$DB_site2->query_first("SELECT * FROM fournisseur WHERE id=".$_POST['fourn2sls']);
	    $text.="Livraison par vos soins par votre transporteur directement chez $sls[societe] $sls[ville]<br>$sls[nom] $sls[prenom]<br>$sls[adresse1]<br>$sls[adresse2]<br>$sls[cp] $sls[ville]<br>$sls[horaires]<br>T�l. $sls[telephone] $sls[portable]
	    <br>D�part maximum : ".$_POST['parvossoinsindate']."
	    <br>$txtlivr : ".$_POST['parvossoinsoutdate'];	
	    $blfourn=$_POST['fourn2sls'];
	    $advtrspt="transporteur fournisseur";
	    $livrid=$_POST['fourn2sls'];
	    break;
	    case "livraisonmazet":
	    $lieutrspt=$DB_site2->query_first("SELECT * FROM fournisseur WHERE id='$_POST[selectmazet]'");
	    $text.="Livraison par vos soins par votre transporteur directement chez $lieutrspt[societe]<br>$lieutrspt[adresse1]<br>$lieutrspt[adresse2]<br>$lieutrspt[cp] $lieutrspt[ville]<br>$lieutrspt[horaires]
	    <br>D�part maximum : ".$_POST['parvossoinsindate']."
	    <br>$txtlivr : ".$_POST['parvossoinsoutdate'];
	    $blfourn=$_POST['selectmazet'];
	    $advtrspt="transporteur fournisseur";
	    $livrid=$_POST[selectmazet];
	    break;
	    case "installsls":
	    $text.="Livraison par vos soins avec installation par votre installateur
	    <br>D�part maximum : ".$_POST['parvossoinsindate']."
	    <br>$txtlivr : ".$_POST['parvossoinsoutdate'];
	    $advtrspt="transporteur fournisseur";
	    $livrid=0;
	    break;
	    case "acilyon":
	    $text.="Livraison par vos soins directement chez ACILYON<br>150 rue paul Bert<br>69003 Lyon<br>T�l : 04.26.03.05.40 / fax :04.26.03.05.49
	    <br>$txtlivr : ".$_POST['parvossoinsoutdate'];
	    $advtrspt="transporteur fournisseur";
	    break;
	  }
	}
	if(isset($_POST['chkinfotrspt'])) $text.="<br>Info pour transporteur : ".stripslashes($_POST['infofournothertxt']);
	$text.="</p>";
    
	$trsptval=floatval($_POST['mazetcout']);
    
	
	$total=$paht+$ecopart+$trsptval+$garantie;
	if($_POST['lieustock']==0){
	    $text.="<p><b>Prix total :</b> $paht &euro; HT";
	    if($ecopart!=0) $text.="<br>+ $ecopart &euro; HT �co part.";
	    if($trsptval!=0) $text.="<br>+ $trsptval &euro; HT transport";
	    if($garantie!=0) $text.="<br>+ $garantie &euro; HT garantie constructeur";
	    $text.="<br>=<b>$total &euro; HT</b></p>";
	}
    }

    $text.=$fin;

    if(!$preview){
      
	if($newcmd==true){
	    $DB_site2->query("INSERT INTO envoi_fournisseur SET facturid=$facturid, date=".time().", destinataire='".$_POST['fournisseurname']."', tarif=".floatval($total).", type='fourn', trspt='$advtrspt', enlev='".intval($_POST['lieustock'])."',livr=".intval($livrid));
	    $insertid=mysqli_insert_id($DB_site2->link_id);
	    foreach($insertids as $eflid) $DB_site2->query("UPDATE envoi_fournisseur_ligne SET efid=$insertid WHERE id=$eflid");
	}else{
	     $annultxt="Annulation article : <br>".$annultxt;
	     $DB_site2->query("INSERT INTO suivi_commande SET facturid=$facturid, date=".time().", commercial='".$user_info[username]."', suivi='$annultxt'");
	}

	$pj=array();
        $mail=new email();
        $mail->destinataire=$_POST[fournisseur];
	$mail->copymode="bcc";
	$mail->to=array("pr@mon-espace-cuisson.com","adv@mon-espace-cuisson.com");
	$mail->expediteur="adv@mon-espace-cuisson.com";
        $mail->expname="Ma Cave � Vin";
        $mail->subject=$subject;
        $mail->body=$text;
        if(!isset($_POST['nopj']) && $newcmd==true){
            if(isset($_POST['facturepj'])){
                $f=fopen("http://www.ma-cave-a-vin.fr/facture2.php?facturid=$facturid&nopub=1","r");
		$data="";
		while(!feof($f)) $data.=fgets($f,1024);
		fclose($f);
		$mail->attachstring($data,"Facture $facturid.pdf","application/pdf");
		$pj[]="Facture $facturid.pdf";
            }
            if(isset($_POST['blpj'])){
		$f=fopen("http://www.ma-cave-a-vin.fr/facture2.php?facturid=$facturid&cde=1&produits[]=".implode("&produits[]=",$_POST['produits']),"r");
		$data="";
		while(!feof($f)) $data.=fgets($f,1024);
		fclose($f);
		$mail->attachstring($data,"Bon $facturid.pdf","application/pdf");

            }
	    if(isset($_POST['blslspj']) && $blfourn!=""){
		$f=fopen("http://www.ma-cave-a-vin.fr/facture2.php?facturid=$facturid&nopub=1&cde=1&livr=".$blfourn."&produits[]=".implode("&produits[]=",$_POST['produits']),"r");
		$data="";
		while(!feof($f)) $data.=fgets($f,1024);
		fclose($f);
                $mail->attachstring($data,"Bon $facturid.pdf","application/pdf");
		$pj[]="Bon $facturid.pdf";
            }
            if(isset($_POST['tntpj'])){
                $f=fopen($_FILES['bontnt']['tmp_name'],"r");
                $data="";
                while(!feof($f))
                     $data.=fread($f,1024);
                fclose($f);
                $mail->attachstring($data,"Bon FEDEX Express","application/pdf");
		$pj[]="Bon FEDEX Express";
            }
	    if(isset($_POST['glspj'])){
                $f=fopen($_FILES['bongls']['tmp_name'],"r");
                $data="";
                if($f!=false){
		  while(!feof($f))
		      $data.=fread($f,1024);
		  fclose($f);
		  $mail->attachstring($data,"Bon_transport.pdf","application/pdf");
		}
            }
            if(isset($_POST['trsptpja4'])){
                $f=fopen("pdf/fragile.pdf","r");
                $data="";
                while(!feof($f))
                    $data.=fread($f,1024);
                fclose($f);
                $mail->attachstring($data,"fragile.pdf","application/pdf");
		$f=fopen("pdf/transportA4.pdf","r");
                $data="";
                while(!feof($f))
                    $data.=fread($f,1024);
                fclose($f);
                $mail->attachstring($data,"note_importante.pdf","application/pdf");
		$pj[]="note_importante.pdf";
            }
	    if(isset($_POST['trsptpja5'])){
                $f=fopen("pdf/transportA5.pdf","r");
                $data="";
                while(!feof($f))
                    $data.=fread($f,1024);
                fclose($f);
                $mail->attachstring($data,"note_importante.pdf","application/pdf");
		$pj[]="Bon $facturid.pdf";
            }
        }

        if($_POST['fournisseur']!="0"){
	    	$result=$mail->sendmail();
	    	$DB_site2->query("INSERT INTO mail_outbox SET date=".time().", utilisateur='".$user_info[username]."', `to`='".$mail->destinataire."', subject='".$mail->subject."', body='".mysqli_real_escape_string($DB_site2->link_id,$mail->body)."', pj='".mysqli_real_escape_string($DB_site2->link_id,implode(',',$pj))."', facturid=".$facturid.",userid='".$adv_facture[userid]."', uniqueid='".$mail->uniqueid."', compta ='non'");
	    	$DB_site2->query("INSERT INTO suivi_commande SET facturid=$facturid, date=".time().", commercial='".$user_info[username]."', suivi='".addslashes("Passage de commande � ".$_POST['fournisseurname']."<br>".$mail->body)."'");
		}
		$action="editer";
    }else{
	if($_POST["fournisseur"]=="0") $_POST['fournisseur']="stock";
        print "<html>
		<head>
		<title>Aper�u</title>
		<META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=iso-8859-15\">
		</head>
		<body>
		sujet:$subject<br>destinataire:$_POST[fournisseur]<br><br>$text
		</body>
		</html>";
    }
}

// envoi commande au transporteur
if($actionadv=="advtransport"){
    $subject="";
    $text="";

    switch($_POST['trspttype']){
	case "rdv":
	$advtrspt="avec rdv";
	$trspttypetxt=" AVEC prise de RDV";
	$text.="<br>Merci de prendre RDV au $adv_facture[telephone]";
	break;

	case "nordv":
	$advtrspt="sans rdv";
	$trspttypetxt=" directe SANS RDV";
	break;

	case "gold":
	$advtrspt="express";
	$trspttypetxt=" *** EXPRESS GOLD ***";
	break;
    }

    switch($_POST['trsptobj']){
         case "cmd":
         $subject="Commande$trspttypetxt n�".$facturid;
	 $text.=" Bonjour,<br>Veuillez trouver ci-joint une nouvelle commande de transport$trspttypetxt n�$facturid";
	 $fin="Nous vous remercions de votre envoi, par mail ou par fax, de votre confirmation de commande<br><br>Cordialement<br>$user_info[nom_contact] - Acilyon - www.ma-cave-a-vin.fr<br>T�l : + 33.4.26.03.05.44 - Fax : + 33.4.26.03.05.49";
	 $newcmd=true;
         break;
	 case "avarie":
         $subject="Commande en service$trspttypetxt n�".$facturid." - Suite avarie commande n�".$_POST['provenanceid'];
	 $text.=" Bonjour,<br>Veuillez trouver ci-joint une nouvelle commande de transport$trspttypetxt n�$facturid, cette livraison ne doit pas nous �tre factur�e suite avarie de transport commande ".$_POST['provenanceid'];
	 $fin="Nous vous remercions de votre envoi, par mail ou par fax, de votre confirmation de commande<br><br>Cordialement<br>$user_info[nom_contact] - Acilyon - www.ma-cave-a-vin.fr<br>T�l : + 33.4.26.03.05.44 - Fax : + 33.4.26.03.05.49";
	 $newcmd=true;
         break;
	 case "annule":
	 $subject="Annulation de la commande n�".$facturid;
	 $text.="Bonjour,<br>Merci d'annuler le transport sur la commande n�$facturid et nous le confirmer par retour de ce mail";
	 $fin="<br><br>Cordialement<br>$user_info[nom_contact] - Acilyon - www.ma-cave-a-vin.fr<br>T�l : + 33.4.26.03.05.44 - Fax : + 33.4.26.03.05.49";
	 $newcmd=false;
	 break;
	 default:
	 $newcmd=true;
    }


    $text.="<p><b>Produits :</b>";
    $paht=0;
    $ecopart=0;
    $poids=0;
    $tsindate=$tsoutdate=0;
    $annultxt="";
    $imperatif=0;
    if($_POST['trsptenlfourndate']!=""){
	    $sp=explode('/',$_POST['trsptenlfourndate']);
	    $tsindate=mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
    }
    if($_POST['trsptotherdate']!=""){
	    $sp=explode('/',$_POST['trsptotherdate']);
	    $tsoutdate=mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
    }
    if($_POST['trsptdatemax']=="trsptdateauto") $imperatif=1;

    $insertids=array();
    foreach($_POST['produits2'] as $p){
            $lignefacture=$DB_site2->query_first("SELECT * FROM lignefacture l, article a WHERE l.artid=a.artid AND lignefactureid=".$p);
            $text.="<br>qte : ".$lignefacture['qte']." - ".$lignefacture['libelleFR']." ".$lignefacture['emballage_poids']." kg";
            $poids+=$lignefacture['qte']*$lignefacture['emballage_poids'];
	    if(!$preview){
	      if($newcmd){
		  $DB_site2->query("INSERT INTO envoi_fournisseur_ligne SET date=".mktime().", indate=$tsindate, outdate=$tsoutdate, origin_in=$tsindate, origin_out=$tsoutdate, imperatif=".intval($imperatif).", expediteur='".$user_info['username']."', qte=1, type='trspt', ligne_facture_id=".$p);
		  $insertids[]=mysqli_insert_id($DB_site2->link_id);
	      }else{
		  $lf=$DB_site2->fetch_array($DB_site2->query("SELECT * FROM lignefacture WHERE lignefactureid=".$p));
		  $annultxt.=$lf['qte']." ".$lf['artcode']."<br>";
		  $DB_site2->query("DELETE FROM envoi_fournisseur_ligne WHERE type='trspt' AND ligne_facture_id=".$p);
	      }
	    }
    }
    $text.="</p>";
    $text.="<p><b>Poids total :</b> ".$poids." kg</p>";

    if($newcmd==true){
	if(isset($_POST['uncolis'])) $text.="<p>Attention : un seul colis pour tous les articles</p>";
	if(isset($_POST['hayon'])) $text.="<p>Camion avec hayon indispensable</p>";
	$text.="<p><b>Lieu d'enl�vement :</b><br>";
	if(substr($_POST['lieutrspt'],0,5)=="fourn"){
	    $fournid=substr($_POST['lieutrspt'],5);
	    $fourn=$DB_site2->query_first("SELECT * FROM fournisseur WHERE id='$fournid'");
	    $enlev=$fournid;
	    $text.="$fourn[societe] $fourn[fonction]<br>$fourn[prenom] $fourn[nom]<br>$fourn[adresse1]".($fourn[adresse2]!=""?"<br>$fourn[adresse2]":"")."<br>$fourn[cp] $fourn[ville]<br>Tel $fourn[telephone] / fax $fourn[fax]<br>$fourn[horaires]";
	}
	if($_POST['lieutrspt']=="liebherr"){
	    $lieutrspt=$DB_site2->query_first("SELECT * FROM fournisseur WHERE id='$_POST[livraisoneb]'");
	    $enlev=0;
	    $text.="Le ou les colis vous seront livr�s directement � votre entrep�t de $lieutrspt[ville] (".substr($lieutrspt['cp'],0,2).") par le transporteur de la soci�t� ".$_POST['livrpar'];
	}
	if($_POST['lieutrspt']=="other")
	    $text.=$_POST['otherlieutrspt'];
    
	$text.="</p>";

	switch($_POST['trsptenl']){
         case "0":

         break;
         case "fournisseur";
         $text.="<p><b>Date d'enl�vement / mise � disposition : </b> ".date("d/m/Y",$tsindate)."</p>";
         break;
         case "eberhardt":
         $text.="<p>Date livraison en vos locaux par le transporteur du fournisseur : ".date("d/m/Y",$tsindate);
         break;
         case "other":
         $text.=$_POST['trsptenlother'];
         break;
	}

	$text.="<p><b>Identification � mettre sur le colis</b> :";
	if(isset($_POST['trsptbl'])) $text.="<br>BL fournit ci-joint";
	if(isset($_POST['trsptfacture'])) $text.="<br>Facture ci-jointe";
	if(isset($_POST['trsptfragile'])) $text.="<br>Etiquette Fragile ci-jointe";
	if(isset($_POST['trspttext'])) $text.="<br>Texte sur le colis : commande n�$facturid de Ma Cave � Vin - Acilyon pour $adv_facture[nom] $adv_facture[prenom] $adv_facture[codepostal] $adv_facture[ville] $adv_facture[libelle]";
	if(isset($_POST['trsptimportant'])) $text.="<br>Important : enlever tous les documents de livraison du fournisseur";
	if(isset($_POST['trspttextother'])) $text.="<br>".$_POST['trsptothertext'];
	$text.="</p>";

    
	switch($_POST['trsptdatemax']){
         case "0":

         break;
         case "trsptdateauto":
         $text.="<p><b>� livrer <u>pr�cis�ment et exclusivement</u> le</b> : ".date("d/m/Y",$tsoutdate)."</p>";
         break;
         case "trsptdateother":
         $text.="<p><b>Date livraison maximum</b> : ".date("d/m/Y",$tsoutdate)."</p>";
         break;
	}
	if(isset($_POST['livraisonplustot'])) $text.="Voir la possibilit� de livrer plus t�t";

	$text.="<p><b>Type de livraison : ".$trspttypetxt."</b></p>";

	$text.="<p><b>Adresse livraison : </b>";
	$livrid=-1;
	$blsls=false;


	if(substr($_POST['trsptlivr'],0,3)=="sls"){
	  $slsid=substr($_POST['trsptlivr'],3);
	  $sls=$DB_site2->query_first("SELECT * FROM fournisseur WHERE id='$slsid'");
	  $text.="$sls[societe] $sls[ville]<br>$sls[adresse1]".($sls[adresse2]!=""?"<br>$sls[adresse2]":"")."<br>$sls[cp] $sls[ville]<br>t�l: $sls[telephone] fax: $sls[fax]<br>$sls[mail]<br>$sls[horaires]";
// 	  $advtrspt="$sls[societe] $sls[ville]";
	  $blsls=$slsid;
	  $livrid=$slsid;
	}elseif($_POST['trsptlivr']=="client"){
	  $text.="<br>$adv_facture[raisonsociale]<br>$adv_facture[nom] $adv_facture[prenom]<br>$adv_facture[adresse]<br>$adv_facture[codepostal] $adv_facture[ville]<br>$adv_facture[libelle]<br>$adv_facture[telephone]";
	  $livrid=0;
	}

	if(isset($_POST['infotrspt'])) $text.="<p><b>Informations pour transporteur</b> : ".stripslashes($_POST['othertrsptinfo'])."</p>";


	$text.="<p><b>Tarif :</b>";

	$tvafrance=$DB_site2->query_first("SELECT TVAtauxnormal FROM pays WHERE paysid=57");
	$tarif=0;
	$assur_comp=0;
	if(isset($_POST['trsptcout'])){ $tarif+=floatval($_POST['trsptcoutval']); $text.=$tarif." �"; }
	if(isset($_POST['chkopt'])){
	  foreach($_POST['chkopt'] as $key=>$value){
	      $text.="<br>+ ".$_POST['priceopt'][$key]." &euro; (".$_POST['optname'][$key].")";
	      $tarif+=floatval($_POST['priceopt'][$key]);
	  }
	}
// 	if(isset($_POST['trsptsaison'])){ $tarifsaison=$tarif*(1+floatval($_POST['trsptsaison']/100)); $tarif+=$tarifsaison; $text.="<br>+ ".$tarifsaison." &euro; (taxe saisonni�re)"; }
// 	if(isset($_POST['trsptecotax'])){ $tarif+=floatval($_POST['trsptecotax']); $text.="<br>+ $_POST[trsptecotax] � (ecotax)"; }
// 	if(isset($_POST['trsptenlext'])){ $tarif+=floatval($_POST['trsptenlext']); $text.="<br>+ $_POST[trsptenlext] � (enl�vement ext.)"; }
// 	if(isset($_POST['trsptparis'])){ $tarif+=floatval($_POST['trsptparis']); $text.="<br>+ $_POST[trsptparis] � (grande m�tropole Paris)"; }
// 	if(isset($_POST['trsptlyon'])){ $tarif+=floatval($_POST['trsptlyon']); $text.="<br>+ $_POST[trsptlyon] � (grande m�tropole Lyon)"; }
// 	if(isset($_POST['trsptmarseille'])){ $tarif+=floatval($_POST['trsptmarseille']); $text.="<br>+ $_POST[trsptmarseille] � (grande m�tropole Marseille)"; }
// 	if(isset($_POST['trsptalloinville'])){ $tarif+=floatval($_POST['trsptalloinville']); $text.="<br>+ ".$_POST['trsptalloinville']." &euro; (centre ville)"; }
// 	if(isset($_POST['trsptcorse'])){ $tarif+=floatval($_POST['trsptcorse']); $text.="<br>+ ".$_POST['trsptcorse']." &euro; (frais Corse)"; }
// 	if(isset($_POST['trsptclientpart'])){ $tarif+=floatval($_POST['trsptclientpart']); $text.="<br>+ $_POST[trsptclientpart] (1 client particulier pour RDV)"; }
// 	if(isset($_POST['trsptclientpro'])){ $tarif+=floatval($_POST['trsptclientpro']); $text.="<br>+ $_POST[trsptclientpro] (1 client professionnel)"; }
// 	if(isset($_POST['trsptetage'])){ $tarif+=floatval($_POST['trsptetage']); $text.="<br>+ $_POST[trsptetage] � (livraison �tage simple)"; }
// 	if(isset($_POST['trsptassurcomp'])){ $tarif+=floatval($_POST['trsptassurcompval']); $text.="<br>+ $_POST[trsptassurcompval] (assurance : valeur d�clar�e : $_POST[valeurdeclaree])"; $assur_comp=1; }
// 	if(isset($_POST['trsptcoutother'])){ $tarif+=floatval($_POST['trsptcoutotherval']); $text.="<br>+ $_POST[trsptcoutotherval] ($_POST[trsptcoutothername])"; }
	$text.="<br><b>= $tarif &euro; HT (hors taxe gas oil)</b>
	<br>valeur d�clar�e : ".$_POST[valeurdeclaree]." &euro; HT, ".number_format($_POST['valeurdeclaree']*(1+$tvafrance[0]/100),2,',','')." &euro; TTC</p>";
    }

    $text.=$fin;

    if($_POST['typetrspt']=="othertypetrspt") $_POST['typetrspt']=$_POST['othertrspttype'];
    if(!$preview){
	if($newcmd==true){
	    $DB_site2->query("INSERT INTO envoi_fournisseur SET facturid=$facturid, date=".time().", destinataire='".$_POST['trsptname']."', tarif=".$tarif.", type='trspt', enlev='".intval($enlev)."', trspt='".addslashes($advtrspt)."',livr=$livrid, assur_comp=".intval($assur_comp));
	    $insertid=mysqli_insert_id($DB_site2->link_id);
	    $nblignes=count($insertids);
	    foreach($insertids as $eflid) $DB_site2->query("UPDATE envoi_fournisseur_ligne SET efid=$insertid, paht=".round($tarif/$nblignes,2)." WHERE id=$eflid");
	}else{
	     $annultxt="Annulation transport : <br>".$annultxt;
	     $DB_site2->query("INSERT INTO suivi_commande SET facturid=$facturid, date=".time().", commercial='".$user_info[username]."', suivi='$annultxt'");
	}

	$pj=array();
        $mail=new email();
        $mail->destinataire=$_POST['typetrspt'];
// 		$mail->destinataire="mr.cc@free.fr";
		$mail->copymode="bcc";
		$mail->to=array("pr@mon-espace-cuisson.com","adv@mon-espace-cuisson.com");
        $mail->expediteur="adv@mon-espace-cuisson.com";
        $mail->expname="Ma Cave � Vin";
        $mail->subject=$subject;
        $mail->body=$text;

        if(!isset($_POST['notrsptpj']) && $newcmd==true){
            if(isset($_POST['facturetrsptpj'])){
                //$_GET['join']=true;
                //$mail->attachstring(include("../facture2.php"),"Facture $facturid.pdf","application/pdf");
		$f=fopen("http://www.ma-cave-a-vin.fr/facture2.php?facturid=$facturid&nopub=1","r");
		$data="";
		while(!feof($f)) $data.=fgets($f,1024);
		fclose($f);
		$mail->attachstring($data,"Facture $facturid.pdf","application/pdf");
		$pj[]="Facture $facturid.pdf";
            }
            if(isset($_POST['bonstrsptpj'])){
		$f=fopen("http://www.ma-cave-a-vin.fr/facture2.php?facturid=$facturid&cde=1&produits[]=".implode("&produits[]=",$_POST['produits2']),"r");
		$data="";
		while(!feof($f)) $data.=fgets($f,1024);
		fclose($f);
		$mail->attachstring($data,"Bon $facturid.pdf","application/pdf");
		$pj[]="Bon $facturid.pdf";
            }
	    if($blsls){
		$_POST['produits']=$_POST['produits2'];
		$f=fopen("http://www.ma-cave-a-vin.fr/facture2.php?facturid=$facturid&nopub=1&cde=1&livr=$blsls&produits[]=".implode("&produits[]=",$_POST['produits2']),"r");
		$data="";
		while(!feof($f)) $data.=fgets($f,1024);
		fclose($f);
                $mail->attachstring($data,"Bon installateur $facturid.pdf","application/pdf");
		$pj[]="Bon installateur $facturid.pdf";
            }
            if(isset($_POST['othertrsptpj']) && !empty($_FILES['othertrsptpjfile'])){
                $f=fopen($_FILES['othertrsptpjfile']['tmp_name'],"r");
                if($f!=false){
                 while(!feof($f))
                      $data.=fread($f,1024);
                 fclose($f);
                 $mime=$DB_site2->fetch_array($DB_site2->query("SELECT * FROM MimeType WHERE g_extension='".pathinfo($_FILES['othertrsptpjfile']['tmp_name'], PATHINFO_EXTENSION)."'"));
                 $mail->attachstring($data,$_FILES['othertrsptpjfile']['name'],$mime['g_mimeType']);
		 $pj[]=$_FILES['othertrsptpjfile']['name'];
                }
            }
        }
        $action="editer";
        if($_POST['typetrspt']!="0"){
	    //$mail->sendmail();  // on envoie pas de mail s'il n'y a pas de transport
	    $DB_site2->query("INSERT INTO mail_outbox SET date=".time().", utilisateur='".$user_info[username]."', `to`='".$mail->destinataire."', subject='".$mail->subject."', body='".mysqli_real_escape_string($DB_site2->link_id,$mail->body)."', pj='".mysqli_real_escape_string($DB_site2->link_id,implode(',',$pj))."', facturid=".$facturid.",userid='".$adv_facture[userid]."', uniqueid='".$mail->uniqueid."', compta ='non'");
		//$DB_site2->query("INSERT INTO suivi_commande SET facturid=$facturid, date=".time().", commercial='".$user_info[username]."', suivi='".addslashes("Passage de commande � ".$_POST['trsptname']."<br>".$mail->body)."'");
	}
    }else{
        if($_POST['typetrspt']=="0")
            print "Pas d'envoi de mail";
        else
            print "<html>
		<head>
		<title>Aper�u</title>
		<META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=iso-8859-15\">
		</head>
		<body>
		Destinataire:".$_POST['typetrspt']."<br>Sujet:".$subject."<br>".$text."
		</body>
		</html>";
    }
}

// envoi commande sls
if($actionadv=="advsls"){
	$tvafrance=$DB_site2->query_first("SELECT TVAtauxnormal FROM pays WHERE paysid=57");
        $subject="";
	$text="";
        
	$tsindate=$tsoutdate=0;
	$imperatif=0;
	if($_POST['slslivrdate']!=""){
	      $sp=explode('/',$_POST['slslivrdate']);
	      $tsindate=mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
	}
	if($_POST['slsmaxdate']!=""){
	      $sp=explode('/',$_POST['slsmaxdate']);
	      $tsoutdate=mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
	}
	if($_POST['maxslslivr']=="precis") $imperatif=1;
	
	foreach($_POST['produits3'] as $p){
	      $lignefacture=$DB_site2->query_first("SELECT * FROM lignefacture l, article a WHERE l.artid=a.artid AND lignefactureid=".$p);
	      $poids+=$lignefacture['qte']*$lignefacture['emballage_poids'];
	}	      

	
	$nbpersonne=2;
	if($poids>120) $nbpersonne=4;
        switch($_POST['slsobj']){
         case "cmd":
         $subject="Commande n�".$facturid;
	 $text.="Bonjour,<br>Veuillez trouver ci-joint une nouvelle commande pour livraison et installation par $nbpersonne personnes n�".$facturid;
	 $fin="Nous vous remercions de votre envoi, par mail ou par fax, de votre confirmation de commande.<br>Cordialement<br>$user_info[nom_contact] - Acilyon - www.ma-cave-a-vin.fr<br>T�l : +33.4.26.03.05.44 - fax : +33.4.26.03.05.49";
	 $newcmd=true;
	 break;
	 case "annule":
	 $subject="Annulation de la commande n�".$facturid;
	 $text.="Bonjour,<br>Merci d'annuler l'installation de la commande n�$facturid et nous le confirmer par retour de ce mail";
	 $fin="<br><br>Cordialement<br>$user_info[nom_contact] - Acilyon - www.ma-cave-a-vin.fr<br>T�l : + 33.4.26.03.05.44 - Fax : + 33.4.26.03.05.49";
	 $newcmd=false;
         break;
	 default:
	 $newcmd=true;
        }


	$insertids=array();
	$annultxt="";
	$text.="<p><b>Produits :</b>";
	foreach($_POST['produits3'] as $p){
	      $lignefacture=$DB_site2->query_first("SELECT * FROM lignefacture l, article a WHERE l.artid=a.artid AND lignefactureid=".$p);
	      $text.="<br>qte :".$lignefacture['qte']." - ".$lignefacture['libelleFR']." ".$lignefacture['emballage_poids']." kg (Voir BL ci-joint)";
	      if(!$preview){
		if($newcmd){
		  $DB_site2->query("INSERT INTO envoi_fournisseur_ligne SET date=".mktime().", indate=$tsindate, outdate=$tsoutdate, origin_in=$tsindate, origin_out=$tsoutdate, imperatif=".intval($imperatif).", expediteur='".$user_info['username']."', type='sls', qte=1, ligne_facture_id=".$p);
		  $insertids[]=mysqli_insert_id($DB_site2->link_id);
		}else{
		  $lf=$DB_site2->fetch_array($DB_site2->query("SELECT * FROM lignefacture WHERE lignefactureid=".$p));
		  $annultxt.=$lf['qte']." ".$lf['artcode']."<br>";
		  $DB_site2->query("DELETE FROM envoi_fournisseur_ligne WHERE type='sls' AND ligne_facture_id=".$p);
		}
	      }
	}
	$text.="</p>";
	$text.="<p><b>Poids total :</b> ".$poids." kg</p>";

	if($newcmd==true){
	    
	    $enlev=0;

	    switch($_POST['slslivr']){
	      case "0":
	      break;
	      case "livrtrspt":
	      $trspt=$DB_site2->query_first("SELECT * FROM fournisseur WHERE id=".$_POST['livrtrsptid']);
	      $text.="<p><b>Livraison en vos entrepots de ".$_POST['livrslsname']."</b><br>";
	      $text.="Le ou les colis vous seront livr�s directement � votre entrepot par ".$trspt['societe']." le ".date("d/m/Y",$tsindate);
	      $text.=" Merci de contr�ler l'�tat du produit en pr�sence du chauffeur.";
	      $enlev=$_POST['livrtrsptid'];
	      break;
	      case "livrfourn":
	      $trspt=$DB_site2->query_first("SELECT * FROM fournisseur WHERE id=".$_POST['livrfournid']);
	      $text.="<p><b>Livraison en vos entrepots de ".$_POST['livrslsname']."</b><br>";
	      $text.="Le ou les colis vous seront livr�s directement � votre entrepot par transporteur de ".$trspt['societe']." le ".date("d/m/Y",$tsindate);
	      $text.=" Merci de contr�ler l'�tat du produit en pr�sence du chauffeur.";
	      $enlev=$_POST['livrfournid'];
	      break;
	      case "livrstock":
	      $lieu=$DB_site2->query_first("SELECT * FROM fournisseur WHERE id=".$_POST['livrstockid']);
	      $text.="Le ou les colis sont � prendre dans le stock Acilyon";
	      $enlev=$_POST['livrstockid'];
	      break;
	      case "livrclient":
	      $text.="<p><b>Le ou les colis ont d�ja �t� livr�s chez le client par un transporteur</b></p>";
	      break;
	      case "other":
	      $text.=$_POST['otherslslvr']." ".date("d/m/Y",$tsindate);
	      break;
	    }
	    if(isset($_POST['slsautreinfocheck'])) $text.="<p>".$_POST['slsautreinfo']."</p>";
	    
	    $text.="</p>";
	    $text.="<p><b>Identification sur le colis</b>";
	    if(isset($_POST['slsidentbl'])) $text.="<br>BL fournit ci-joint";
	    if(isset($_POST['slsidenttxt'])) $text.="<br>Texte sur colis : commande n�$facturid de Ma Cave � Vin - Acilyon pour ".nl2br($_POST['slsidentpour']);
	    if(isset($_POST['slsidentimportant'])) $text.="<br>Important : enlever tous les documents de livraison de EBERHARDT Fr�res";
	    if(isset($_POST['trsptenldoc'])) $text.="<br>Important : enlever tous les documents de livraison du fournisseur et mettre le Bon de livraison ci-joint a la commande";
	    if(isset($_POST['slsidentother'])) $text.="<br>".$_POST['slsidentothertxt'];
	    $text.="</p>";

	    $advtrspt="";
	    $text.="<p><b>Adresse de livraison </b>:";
	    
	    $text.=" Merci de prendre RDV au $adv_facture[telephone]
	    <br>$adv_facture[raisonsociale] $adv_facture[nom] $adv_facture[prenom]<br>$adv_facture[adresse]<br>$adv_facture[codepostal] $adv_facture[ville]<br>$adv_facture[libelle]<br>$adv_facture[telephone]";
	    $advtrspt="avec rdv";

	    $text.="</p>";
	    $text.="<p>";
	    switch($_POST['maxslslivr']){
	      case "precis":
	      $text.="<b>A livrer <u>pr�cis�ment et exclusivement</u> le</b> : ".date("d/m/Y",$tsoutdate);
	      break;
	      case "max":
	      $text.="<b>Date installation maximum : </b>".date("d/m/Y",$tsoutdate);
	      break;
	      case "other":
	      $text.=$_POST['slslivrotherdate'];
	      break;
	    }
	    if(isset($_POST['slsinfoother'])) $text.="<br>Info pour transporteur : ".stripslashes($_POST['slsinfoothertxt']);
	    $text.="</p>";

	    if(!isset($_POST['slsinfo'])){
	      $text.="<p><b>Informations pour transporteur et installateur</b> :";
	      $text.="<br>Livraison par $nbpersonne personnes";
	      if(isset($_POST['slsrdv'])) $text.="<br>Prendre RDV avec le client pour livraison";
	      if(isset($_POST['slsbranchement'])) $text.="<br>Important : ne pas brancher �lectriquement le produit (branchement a effectuer par le client 48h apr�s)";
	      if(isset($_POST['slsmiseniveau'])) $text.="<br>Important : mettre � niveau le produit � l'aide des pieds r�glables";
	      if(isset($_POST['slsmontage'])) $text.="<br>Important : Si besoin, assurer le montage des accessoires (poign�e de porte, clayettes, etc...)";
	      if(isset($_POST['slsemballage'])) $text.="<br>Important : bien penser a r�cup�rer les emballages pour destruction";
	      if(isset($_POST['slsemballage2'])) $text.="<br>Important : merci de laisser les emballages chez le client";
	      $text.="</p>";
	    }

	    $text.="<p><b>R�cup�ration ancien produit</b> : ";
	    if($_POST['recup']=="0") $text.="Aucune";
	    if($_POST['recup']=="1") $text.="Attention : r�cup�ration de l'ancien produit, pour destruction (poids de ".$_POST['recuppoids']."kg)</p>";
	    if($_POST['recup']=="2"){
	      $recupenvoi=$DB_site2->query_first("SELECT * FROM fournisseur WHERE id=".$_POST['recupretour']);
	      $text.="Attention : r�emballage et r�cup�ration de l'ancien produit pour retour chez : ".$recupenvoi['societe']."<br>".$recupenvoi['adresse1'].($recupenvoi['adresse2']!=""?"<br>".$recupenvoi['adresse2']:"")."<br>".$recupenvoi['cp']." ".$recupenvoi['ville']."<br>(poids de ".$_POST['recuppoids2']."kg)</p>";
	    }
	    if($_POST['recup']=="other") $text.=$_POST['recupother'];
	    $text.="</p>";
        
    
	    $totalsls=0;
	    $assur_comp=0;
	    $text.="<p><b>Tarif SLS :</b>";
	    if(isset($_POST['slscouttrspt'])){
		$totalsls+=floatval($_POST['slscouttrsptval']);
		$text.="<br>".$_POST['slscouttrsptval']." &euro;";
	    }
	    if(isset($_POST['slscoutcorse'])){
		$totalsls+=floatval($_POST['slscorsetotal']);
		$text.="<br>+ ".$_POST['slscorsetotal']." &euro; Passage Corse";
	    }
	    if(isset($_POST['slscoutreprise'])){
		$totalsls+=floatval($_POST['slscoutrepriseval']);
		$text.="<br>+ ".$_POST['slscoutrepriseval']." &euro; Reprise ancien produit pour destruction ou relivraison";
	    }
	    if(isset($_POST['slscoutassur'])){
		$totalsls+=floatval($_POST['slscoutassurval']);
		$text.="<br>+ ".$_POST['slscoutassurval']." &euro; Assurance compl�mentaire";
		$assur_comp=1;
	    }
	    if(isset($_POST['slscoutother'])){
		$totalsls+=floatval($_POST['slscoutotherval']);
		$text.="<br>+ ".$_POST['slscoutotherval']." &euro; ".$_POST['slscoutothername'];
	    }
	    $text.="<br>= ".$totalsls." &euro;
	    <br>valeur d�clar�e : ".number_format(floatval($_POST['valeurtotalsls']),2,',','')." &euro; HT, ".number_format(floatval($_POST['valeurtotalsls'])*(1+$tvafrance[0]/100),2,',','')." &euro; TTC</p>";
	}

	$text.=$fin;
        if(!$preview){
	    if($newcmd==true){
		$DB_site2->query("INSERT INTO envoi_fournisseur SET facturid=$facturid, date=".time().", destinataire='".$_POST['livrslsname']."', tarif=".$totalsls.", type='sls', enlev='$enlev', trspt='$advtrspt', assur_comp=".intval($assur_comp));
		$insertid=mysqli_insert_id($DB_site2->link_id);
		$nblignes=count($insertids);
		foreach($insertids as $eflid) $DB_site2->query("UPDATE envoi_fournisseur_ligne SET efid=$insertid, paht=".round($totalsls/$nblignes,2)." WHERE id=$eflid");
	    }else{
	     $annultxt="Annulation installation : <br>".$annultxt;
	     $DB_site2->query("INSERT INTO suivi_commande SET facturid=$facturid, date=".time().", commercial='".$user_info[username]."', suivi='$annultxt'");
	    }

	    $pj=array();
            $mail=new email();
            $mail->destinataire=$_POST['livrsls'];
	    $mail->copymode="bcc";
            $mail->to=array("pr@mon-espace-cuisson.com","adv@mon-espace-cuisson.com");
            $mail->expediteur="adv@mon-espace-cuisson.com";
            $mail->expname="Ma Cave � Vin";
            $mail->subject=$subject;
            $mail->body=$text;
            if(!isset($_POST['slspj']) && $newcmd==true){
                if(isset($_POST['factureslspj'])){
                    $f=fopen("http://www.ma-cave-a-vin.fr/facture2.php?facturid=$facturid&nopub=1","r");
		    $data="";
		    while(!feof($f)) $data.=fgets($f,1024);
		    fclose($f);
                }
                if(isset($_POST['bonslspj'])){
		    $f=fopen("http://www.ma-cave-a-vin.fr/facture2.php?facturid=$facturid&cde=1","r");
		    $data="";
		    while(!feof($f)) $data.=fgets($f,1024);
		    fclose($f);
		    $mail->attachstring($data,"Bon $facturid.pdf","application/pdf");
		    $pj[]="Bon $facturid.pdf";
                }
                if(isset($_POST['otherslspj']) && !empty($_FILES['otherslspjfile'])){
                    $f=fopen($_FILES['otherslspjfile']['tmp_name'],"r");
                    if($f!=false){
		      while(!feof($f))
			$data.=fread($f,1024);
		      fclose($f);
		      $mail->attachstring($data,$_FILES['otherslspjfile']['name'],"application/octet-stream");
		      $pj[]=$_FILES['otherslspjfile']['name'];
                    }
                }
            }
            $action="editer";
            if($_POST['livrsls']!="0"){
		$mail->sendmail();    // on envoie le mail seulement s'il y a une livraison
		$DB_site2->query("INSERT INTO mail_outbox SET date=".time().", utilisateur='".$user_info[username]."', `to`='".$mail->destinataire."', subject='".$mail->subject."', body='".mysqli_real_escape_string($DB_site2->link_id,$mail->body)."', pj='".mysqli_real_escape_string($DB_site2->link_id,implode(',',$pj))."', facturid=".$facturid.",userid='".$adv_facture[userid]."', uniqueid='".$mail->uniqueid."', compta ='non'");
		//$DB_site2->query("INSERT INTO suivi_commande SET facturid=$facturid, date=".time().", commercial='".$user_info[username]."', suivi='".addslashes("Passage de commande � ".$_POST['livrslsname']."<br>".$mail->body)."'");
	    }
        }else{
           if($_POST['livrsls']=="0")
               print "Pas d'envoi de mail";
           else
               print "<html>
		<head>
		<title>Aper�u</title>
		<META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=iso-8859-15\">
		</head>
		<body>
		Destinataire:".$_POST['livrslsname']." ".$_POST['livrsls']."<br>Sujet:".$subject."<br>".$text."
		</body>
		</html>";
       }

}


?>