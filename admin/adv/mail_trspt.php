<?php


function mail_trspt($adv,$preview=false){
    global $DB_site2;
    global $rootpath;
    global $user_info;
    global $adv_facture;
    global $devises;
    global $js_popup_pj;
    global $host;
    global $host_conf;
    
    $facturid=$adv->facturid;
    $facture=new facture($DB_site2,$facturid);

    $subject="";
    $text="";
    $livrtype="client";
    $rdv_tel=$adv_facture[telephone];
    $edi = false;
    $parent = 0;
    
    $stock    = new stock($DB_site2);
    
    if(substr($adv->fields['trsptlivr'],0,3)=="sls"){
	  $slsid=substr($adv->fields['trsptlivr'],3);
	  $sls=$DB_site2->query_first("SELECT * FROM fournisseur WHERE id='$slsid'");
	  $livrtype="sls";
	  $rdv_tel=$sls['telephone'];
    }

    if(substr($adv->fields['trsptlivr'],0,5)=="stock"){
	  $slsid=substr($adv->fields['trsptlivr'],5);
	  $sls=$DB_site2->query_first("SELECT * FROM fournisseur WHERE id='$slsid'");
	  $livrtype="stock";
	  $rdv_tel=$sls['telephone'];
    }

    if(substr($adv->fields['trsptlivr'],0,5)=="fourn"){
	  $slsid=substr($adv->fields['trsptlivr'],5);
	  $sls=$DB_site2->query_first("SELECT * FROM fournisseur WHERE id='$slsid'");
	  $livrtype="fourn";
	  $rdv_tel=$sls['telephone'];
    }
    
    switch($adv->fields['trspttype']){
		case "rdv":
			$advtrspt="avec rdv";
			$trspttypetxt=" AVEC prise de RDV";
			$rdvtxt="<br>Merci de prendre RDV au ".$rdv_tel;
		break;

		case "nordv":
			$advtrspt="sans rdv";
			$trspttypetxt=" directe SANS RDV";
			$rdvtxt="";
		break;

		case "gold":
			$advtrspt="express";
			$trspttypetxt=" *** EXPRESS GOLD ***";
			$rdvtxt="";
		break;

		case "ondemand":
			$advtrspt="express";
			$trspttypetxt=" *** ON DEMAND ***";
			$rdvtxt="";
		break;
		
		case "stardate":
			$advtrspt="stardate";
			$trspttypetxt=" \"STAR DATE\"";
			$rdvtxt="";
		break;

		case "stardate13h":
			$advtrspt="stardate13h";
			$trspttypetxt=" \"STAR DATE avant 13h\"";
			$rdvtxt="";
		break;
		
		case "starprio":
			$advtrspt="starpriority";
			$trspttypetxt=" \"STAR PRIORITY\"";
			$rdvtxt="";
		break;

		case "starprio13h":
			$advtrspt="starpriority13h";
			$trspttypetxt=" \"STAR PRIORITY avant 13h\"";
			$rdvtxt="";
		break;

		case "rdvpro":
			$advtrspt="rdvpro";
			$trspttypetxt=" \"RENDEZ-VOUS\"";
			$rdvtxt="";
		break;

		case "rdvpart":
			$advtrspt="rdvpart";
			$trspttypetxt=" \"PARTICULIER\"";
			$rdvtxt="";
		break;
		
		case "top18":
			$advtrspt="top18";
			$trspttypetxt=" \"Top18\"";
			$rdvtxt="";
		break;
		
		case "top13":
			$advtrspt="top13";
			$trspttypetxt=" \"Top13\"";
			$rdvtxt="";
		break;
    }

    switch($adv->fields['trsptobj']){
        case "cmd":
        	$subject="Commande$trspttypetxt #CDE".$facturid."#";
	 		$text.=" Bonjour,<br>Veuillez trouver ci-joint une nouvelle commande de transport$trspttypetxt #CDE".$facturid."#";
        	if($adv->fields['trspt_ctr_remb'] == 1) $text.= ", en contre remboursement";
	 		$fin="Nous vous remercions de votre envoi, par mail ou par fax, de votre confirmation de commande<br><br>Cordialement<br>$user_info[nom_contact] - Acilyon - www.ma-cave-a-vin.fr<br>Tél : + 33.4.26.03.05.44 - Fax : + 33.4.26.03.05.49";
	 		$newcmd=true;
        break;
	 	case "avarie":
	        $subject="Commande en service$trspttypetxt #CDE".$facturid."# - Suite avarie commande n°".$adv_facture['provenanceid'];
		 	$text.=" Bonjour,<br>Veuillez trouver ci-joint une nouvelle commande de transport$trspttypetxt #CDE$facturid#, cette livraison ne doit pas nous être facturée suite avarie de transport commande ".$adv_facture['provenanceid'];
		 	$fin="Nous vous remercions de votre envoi, par mail ou par fax, de votre confirmation de commande<br><br>Cordialement<br>$user_info[nom_contact] - Acilyon - www.ma-cave-a-vin.fr<br>Tél : + 33.4.26.03.05.44 - Fax : + 33.4.26.03.05.49";
		 	$newcmd=true;
        break;
		case "annule":
			$subject="Annulation de la commande #CDE".$facturid."#";
			$text.="Bonjour,<br>Merci d'annuler le transport sur la commande #CDE$facturid# et nous le confirmer par retour de ce mail";
			$fin="<br><br>Cordialement<br>$user_info[nom_contact] - Acilyon - www.ma-cave-a-vin.fr<br>Tél : + 33.4.26.03.05.44 - Fax : + 33.4.26.03.05.49";
			$newcmd=false;
		break;
		case "modif":
			$ef=new envoi_fournisseur($DB_site2,$adv->efid);
			$subject="Modification de la commande #CDE".$facturid."#";
			$text.="Bonjour,<br>Cette modification de commande annule et remplace la commande #CDE".$facturid."# envoyée par mail le ".date("d/m/Y à H:i",$ef->date);
			$fin="Merci de nous confirmer cette modification par retour de ce mail.<br>Cordialement<br>$user_info[nom_contact] - ACILYON - www.ma-cave-a-vin.fr<br>Tél : + 33.4.26.03.05.44 -  Fax: +33.4.26.03.05.49";
			$newcmd=true;
		break;
		default:
			$newcmd=true;
		break;	
    }


    //Creation du repertoire du transporteur pour les pièces jointes lui correspondant
    if(!$preview){
	    if($adv->fields['trsptobj'] == "annule" || $adv->fields['trsptobj']=="modif"){
	    	$file = $DB_site2->query_first("SELECT id FROM user_files WHERE facturid='$facturid' AND name ='".mysqli_real_escape_string($DB_site2->link_id,$adv->destinataire)."' AND parent=0");
	        $files = $DB_site2->query("SELECT * FROM user_files WHERE facturid='$facturid' AND parent='".$file[0]."'");
	        while($the_file = $DB_site2->fetch_array($files)){
	            $name = $the_file[id].".".$the_file[ext];
	            unlink(__DIR__."/../userfiles/".$name);
	        }
	        $DB_site2->query("DELETE FROM user_files WHERE facturid='$facturid' AND  parent='".$file[0]."'");
	        $DB_site2->query("DELETE FROM user_files WHERE facturid='$facturid' AND name ='".mysqli_real_escape_string($DB_site2->link_id,$adv->destinataire)."' AND parent=0");

	    }else{
	    	$file = $DB_site2->query_first("SELECT COUNT(*) FROM user_files WHERE facturid='$facturid' AND name ='".mysqli_real_escape_string($DB_site2->link_id,$adv->destinataire)."' AND parent=0");
	    	if($file[0] == 0){
	    		$DB_site2->query("INSERT INTO user_files SET userid='".$facture->userid."', facturid='".$facturid."', parent=0, utilisateur='".$user_info['username']."', name='".mysqli_real_escape_string($DB_site2->link_id,$adv->destinataire)."', size=0, ext='',state=1, date='".time()."' ");
	    		$parent = $DB_site2->insert_id();
	   		}
	    }
    }
    


    $sql = "SELECT fc.compte_client, sf.societe_facturation_nom";
    $sql .= " FROM fournisseur_condition fc";
    $sql .= " LEFT JOIN societe_facturation sf ON (sf.societe_facturation_id = fc.societe_facturation_id)";
    $sql .= " WHERE fc.societe='".mysqli_real_escape_string($DB_site2->link_id,$adv->destinataire)."'";
    $fournisseur_condition = $DB_site2->query_first($sql);
    if($fournisseur_condition['compte_client']!=""){
        $text .= "<p>Compte client : ".$fournisseur_condition['societe_facturation_nom']." - ".$fournisseur_condition['compte_client']."</p>";
    }
    
    if($adv->fields['trspt_ctr_remb'] == 1){ 
          
        $sql = "SELECT libelleFR";
        $sql .= " FROM moyenpaiement";
        $sql .= " WHERE moyenid = ".$adv->fields['ctr_remb_moyen'];
        $moyen_ctr_remb = $DB_site2->query_first($sql);
         
        $text .= "<font color=\"red\"><strong>Attention : commande avec contre-remboursement, d'un montant de ".number_format($adv->fields['ctr_remb_montant'],2,',',' ')." ".$devises->symbole($adv->devise)." (".strtolower($moyen_ctr_remb['libelleFR']).")</strong></font><br>";
    }
    
    if($adv->fields['infocomplement']!="")
	$text.="<p>".nl2br($adv->fields['infocomplement'])."</p>";
    $text.=$rdvtxt;

    $text.="<p><b>Produits :</b>";
    $paht=0;
    $ecopart=0;
    $poids=0;
    $tsindate=$tsoutdate=0;
    $annultxt="";
    $imperatif=0;
    if($adv->fields['trsptenlfourndate']!=""){
	    $tsindate=$adv->date2ts($adv->fields['trsptenlfourndate']);
    }
    if($adv->fields['trsptotherdate']!=""){
	    $tsoutdate=$adv->date2ts($adv->fields['trsptotherdate']);
    }
    if($adv->fields['trsptdatemax']=="trsptdateauto") $imperatif=1;

    $insertids=array();
    foreach($adv->lignes as $p=>$qte){
            $lignefacture=$DB_site2->query_first("SELECT *,(a.emballage_poids1+a.emballage_poids2+a.emballage_poids3) as emballage_poids FROM lignefacture l, article a WHERE l.artid=a.artid AND lignefactureid=".$p);
            $text.="<br>qte : ".$lignefacture['qte']." - ".$lignefacture['libelleFR']." ".$lignefacture['emballage_poids']." kg";
            $poids+=$lignefacture['qte']*$lignefacture['emballage_poids'];
    }
    $text.="</p>";
    $text.="<p><b>Poids total :</b> ".$poids." kg</p>";

    if($newcmd==true){
		if($adv->fields['uncolis']==1) $text.="<p>Attention : un seul colis pour tous les articles</p>";
		if($adv->fields['hayon']==1) $text.="<p>Camion avec hayon indispensable</p>";
		$text.="<p><b>Lieu d'enlèvement :</b><br>";
		if(substr($adv->fields['lieutrspt'],0,5)=="fourn"){
	    	$fournid=substr($adv->fields['lieutrspt'],5);
	    	$fourn=$DB_site2->query_first("SELECT * FROM fournisseur WHERE id='$fournid'");
	    	$enlev=$fournid;
	    	$text.="$fourn[societe]<br>$fourn[prenom] $fourn[nom]<br>$fourn[adresse1]".($fourn[adresse2]!=""?"<br>$fourn[adresse2]":"")."<br>$fourn[cp] $fourn[ville]<br>Tel $fourn[telephone] / fax $fourn[fax]<br>$fourn[horaires]";
		}
		if($adv->fields['lieutrspt']=="liebherr"){
	    	$lieutrspt=$DB_site2->query_first("SELECT * FROM fournisseur WHERE id='".$adv->fields['livraisoneb']."'");
	    	$enlev=0;
	    	$text.="Le ou les colis vous seront livrés directement à votre entrepôt de $lieutrspt[ville] (".substr($lieutrspt['cp'],0,2).") par le transporteur de la société ".$_POST['livrpar'];
		}
		if($adv->fields['lieutrspt']=="other")
	   		$text.=$adv->fields['otherlieutrspt'];
    
		$text.="</p>";

		switch($adv->fields['trsptenl']){
	        case "0":

	        break;
	        case "fournisseur";
	        	$text.="<p><b>Date d'enlèvement / mise à disposition : </b> ".date("d/m/Y",$tsindate)."</p>";
	        break;
	        case "eberhardt":
	        	$text.="<p>Date livraison en vos locaux par le transporteur du fournisseur : ".date("d/m/Y",$tsindate);
	        break;
	        case "other":
	        	$text.=$adv->fields['trsptenlother'];
	        break;
		}

		$text.="<p><b>Identification à mettre sur le colis</b> :";
		if($adv->fields['trsptbl']==1) $text.="<br>BL fournit ci-joint";
		if($adv->fields['trsptfacture']==1) $text.="<br>Facture ci-jointe";
		if($adv->fields['trsptfragile']==1) $text.="<br>Etiquette Fragile ci-jointe";
		if($adv->fields['trspttext']==1) $text.="<br>Texte sur le colis : commande #CDE$facturid# de Ma Cave à Vin - Acilyon pour $adv_facture[nom] $adv_facture[prenom] $adv_facture[codepostal] $adv_facture[ville] $adv_facture[libelle]";
		if($adv->fields['trsptimportant']==1) $text.="<br>Important : enlever tous les documents de livraison du fournisseur";
		if($adv->fields['trspttextother']==1) $text.="<br>".$adv->fields['trsptothertext'];
		$text.="</p>";

    
		switch($adv->fields['trsptdatemax']){
	         case "0":

	        break;
	        case "trsptdateauto":
	        	$text.="<p><b>à livrer <u>précisément et exclusivement</u> le</b> : ".date("d/m/Y",$tsoutdate)."</p>";
	        break;
	        case "trsptdateother":
	        	$text.="<p><b>Date livraison maximum</b> : ".date("d/m/Y",$tsoutdate)."</p>";
	        break;
		}
		if($adv->fields['livraisonplustot']==1) $text.="Voir la possibilité de livrer plus tôt";

		if($trspttypetxt != '') $text.="<p><b>Type de livraison : ".$trspttypetxt."</b></p>";

		$text.="<p><b>Adresse livraison : </b>";
		$livrid=-1;
		$blsls=false;


		switch($livrtype){
		    case "sls":
		    case "stock":
		    case "fourn":
		    	$text.="$sls[societe] $sls[ville]<br>$sls[adresse1]".($sls[adresse2]!=""?"<br>$sls[adresse2]":"")."<br>$sls[cp] $sls[ville]<br>tél: $sls[telephone] fax: $sls[fax]<br>$sls[mail]<br>$sls[horaires]";
		    	$blsls=$slsid;
		    	$livrid=$slsid;
		    break;
		
		    case "client":
		    	$text.="<br>$adv_facture[raisonsociale]<br>$adv_facture[nom] $adv_facture[prenom]<br>$adv_facture[adresse]<br>$adv_facture[codepostal] $adv_facture[ville]<br>$adv_facture[libelle]<br>$adv_facture[telephone]";
		    	$livrid=0;
		    break;
		}

		if($adv->fields['infotrspt']=="other") $text.="<p><b>Informations pour transporteur</b> : ".stripslashes($adv->fields['othertrsptinfo'])."</p>";


	    $text .= "<p><b>Tarif :</b>";

	    $sql = "SELECT TVAtauxnormal";
	    $sql .= " FROM pays";
	    $sql .= " WHERE paysid = 57";
	    $tvafrance = $DB_site2->query_first($sql);
	     
	    $tarif          = 0;
	    $assur_comp     = 0;
	    $valeurdeclaree = false;
	     
	    if($adv->fields['trsptcout'] == 1){ 
	        $tarif += floatval($adv->fields['trsptcoutval']);
	        $text .= $tarif."&nbsp;".$devises->symbole($adv->devise); 
	    }
     
	    if($adv->fields['trspt_ctr_remb'] == 1 && $adv->fields['ctr_remb_coutval'] > 0){ 
	          
	        $cout_service = $adv->fields['ctr_remb_coutval'];
	        $valeur_remb  = $adv->fields['ctr_remb_montant'];
	          
	        $sql = "SELECT libelleFR";
	        $sql .= " FROM moyenpaiement";
	        $sql .= " WHERE moyenid = ".$adv->fields['ctr_remb_moyen'];
	        $moyen_ctr_remb = $DB_site2->query_first($sql);
	          
	        $tarif += floatval($cout_service);
	        $text .= "<br>+ ".number_format($cout_service,2,',',' ')." ".$devises->symbole($adv->devise)." (Contre remboursement)"; 
	    }
     
	    if(isset($adv->fields['chkopt'])){
	        foreach($adv->fields['chkopt'] as $key=>$value){
	            if($value==1){
	                $text.="<br>+ ".$adv->fields['priceopt'][$key]." ".$devises->symbole($adv->devise)." (".$adv->fields['optname'][$key].")";
	                $tarif+=floatval($adv->fields['priceopt'][$key]);
	                if(strpos(strtolower($adv->fields['optname'][$key]),"assurance")>-1) $valeurdeclaree=true;
	            }
	        }
	    }
			// 	if(isset($_POST['trsptsaison'])){ $tarifsaison=$tarif*(1+floatval($_POST['trsptsaison']/100)); $tarif+=$tarifsaison; $text.="<br>+ ".$tarifsaison." &euro; (taxe saisonnière)"; }
			// 	if(isset($_POST['trsptecotax'])){ $tarif+=floatval($_POST['trsptecotax']); $text.="<br>+ $_POST[trsptecotax] € (ecotax)"; }
			// 	if(isset($_POST['trsptenlext'])){ $tarif+=floatval($_POST['trsptenlext']); $text.="<br>+ $_POST[trsptenlext] € (enlèvement ext.)"; }
			// 	if(isset($_POST['trsptparis'])){ $tarif+=floatval($_POST['trsptparis']); $text.="<br>+ $_POST[trsptparis] € (grande métropole Paris)"; }
			// 	if(isset($_POST['trsptlyon'])){ $tarif+=floatval($_POST['trsptlyon']); $text.="<br>+ $_POST[trsptlyon] € (grande métropole Lyon)"; }
			// 	if(isset($_POST['trsptmarseille'])){ $tarif+=floatval($_POST['trsptmarseille']); $text.="<br>+ $_POST[trsptmarseille] € (grande métropole Marseille)"; }
			// 	if(isset($_POST['trsptalloinville'])){ $tarif+=floatval($_POST['trsptalloinville']); $text.="<br>+ ".$_POST['trsptalloinville']." &euro; (centre ville)"; }
			// 	if(isset($_POST['trsptcorse'])){ $tarif+=floatval($_POST['trsptcorse']); $text.="<br>+ ".$_POST['trsptcorse']." &euro; (frais Corse)"; }
			// 	if(isset($_POST['trsptclientpart'])){ $tarif+=floatval($_POST['trsptclientpart']); $text.="<br>+ $_POST[trsptclientpart] (1 client particulier pour RDV)"; }
			// 	if(isset($_POST['trsptclientpro'])){ $tarif+=floatval($_POST['trsptclientpro']); $text.="<br>+ $_POST[trsptclientpro] (1 client professionnel)"; }
			// 	if(isset($_POST['trsptetage'])){ $tarif+=floatval($_POST['trsptetage']); $text.="<br>+ $_POST[trsptetage] € (livraison étage simple)"; }
			// 	if(isset($_POST['trsptassurcomp'])){ $tarif+=floatval($_POST['trsptassurcompval']); $text.="<br>+ $_POST[trsptassurcompval] (assurance : valeur déclarée : $_POST[valeurdeclaree])"; $assur_comp=1; }
			// 	if(isset($_POST['trsptcoutother'])){ $tarif+=floatval($_POST['trsptcoutotherval']); $text.="<br>+ $_POST[trsptcoutotherval] ($_POST[trsptcoutothername])"; }
		$text.="<br><b>= $tarif ".$devises->symbole($adv->devise)." HT (hors taxe gas oil)</b></p>";
	
		if($valeurdeclaree) $text.="<p>valeur déclarée : ".$adv->fields['valeurdeclaree']." &euro; HT, ".number_format($adv->fields['valeurdeclaree']*(1+$tvafrance[0]/100),2,',','')." &euro; TTC</p>";
    }

    $text.=$fin;

    if(!$preview){
		if($adv->fields['trsptobj']=="annule" || $adv->fields['trsptobj']=="modif"){
			//Si le transporteur est GLS on va supprimer la commande de transporteur via l'API :
			$info_ef = $DB_site2->query_first("SELECT trackid, destinataire, type FROM envoi_fournisseur WHERE id=".$adv->efid);
      		if($info_ef['destinataire']=='GLS' && $info_ef['type']=='trspt' && $info_ef['trackid']!==''){
        		$gls=new gls($DB_site2,$adv,$clientid);
        		$gls->cancelShipitapi(false,$info_ef['trackid']);
      		}
      		
	    	$ef=new envoi_fournisseur($DB_site2,$adv->efid);
		    $ef->delete();
		    $adv->efid=0;
		    $adv->update();
		}

		if($newcmd==true){
	    	$listefl=array();
		    $pahtu=round($tarif/count($adv->lignes),2);
		    foreach($adv->lignes as $ligne=>$qte){

            	$efl=new envoi_fournisseur_ligne($DB_site2);
	            $efl->indate=$tsindate;
	            $efl->outdate=$tsoutdate;
	            $efl->imperatif=$imperatif;
	            $efl->expediteur=$user_info['username'];
	            $efl->type="trspt";
	            $efl->lignefactureid=$ligne;
	            $efl->paht=$pahtu;
	            $efl->devise=$adv->devise;
	            $efl->qte=1;
	            $listefl[]=$efl;
	            $lf=$adv->ligne_facture($ligne);
            
	            if($livrtype=="stock" && $lf->artid>0){
	                $stockartid=$stock->similaire($lf->artid);
	                for($i=1;$i<=$qte;$i++){
	                    $item=new stock_item($DB_site2);
	                    $item->artid=$stockartid;
	                    $item->paht=0;
	                    $item->lieu=$livrid;
	                    $item->origin="fourn";
	                    $item->dest="facture ".$facturid;
	                    $item->utilisateur=$user_info['username'];
	                    $item->date=time();
	                    $item->date_enlevement=$tsindate;
	                    $item->date_livraison=$tsoutdate;
	                    $item->date_enlevement_origin=$tsindate;
                        $item->date_livraison_origin=$tsoutdate;
	                    $item->state=0;
	                    $item->insert();
	                }
	            }
	    	}
	    
		    $ef=new envoi_fournisseur($DB_site2);
		    $ef->facturid=$facturid;
		    $ef->destinataire=$adv->destinataire;
		    $ef->tarif=$tarif;
		    $ef->devise=$adv->devise;
		    $ef->type="trspt";
		    $ef->lieu_enlev=$enlev;
		    $ef->trspt=$advtrspt;
		    $ef->lieu_livr=$livrid;
		    $ef->assur_comp=$assur_comp;
		    $ef->create();
		    $ef->create_lines($listefl);
		    $adv->efid=$ef->id;
		    $adv->update();
	    
	    
	    
	    	if($adv->fields["envoiedi"]=="1"){

                //Génération de l'étiquette par webservice GLS
                if($adv->destinataire == "GLS"){
                	include_once($rootpath."configurations/".$host."/transporteurs/gls/gls.php");
                    $gls = new gls($DB_site2,$adv,$clientid);
                    $gls->send(false,$parent);
                    $edi = true;
                    
                    if($gls->error() !== false){
                        $sql = "INSERT INTO suivi_commande SET";
                        $sql .= " facturid    = ".$facturid."";
                        $sql .= ", commercial = '".$adv->utilisateur."'";
                        $sql .= ", date  	  = ".time()."";
                        $sql .= ", suivi 	  = '".mysqli_real_escape_string($DB_site2->link_id,"Erreur EDI GLS\n".$gls->error())."'";
                        $DB_site2->query($sql);
                        $js_popup_pj="var w=document.open();w.document.write(\"Erreur EDI GLS<br>".$gls->error()."\");";
                    }else{
                        $ef->trackid=$gls->trackid();
                        $ef->update();
                        $js_popup_pj="window.open('userfiles/".$gls->pjid().".pdf')";
                    }
                }

                //Génération de l'étiquette par webservice FEDEX Express
                if($adv->destinataire == "FEDEX Express"){
                	//on inclut les variables en fonction du site.
                	include_once($rootpath."configurations/".$host."/transporteurs/tnt/tnt.php");
                    $tnt = new tnt($DB_site2, $adv,'STDA4',$username,$pass);

                    if(ESPACE != 'prod') $tnt->test_mode = true; //Permet d'éviter de faire la demande de ramassage à TNT
                    $tnt->expedition();
                    $edi = true;
                    
                    if($tnt->getError() !== false){

                            $sql = "INSERT INTO suivi_commande SET";
                        $sql .= " facturid    = ".$facturid."";
                        $sql .= ", commercial = '".$adv->utilisateur."'";
                        $sql .= ", date  	  = ".time()."";
                        $sql .= ", suivi 	  = '".mysqli_real_escape_string($DB_site2->link_id,"Erreur EDI FEDEX Express\n".$tnt->getError())."'";
                        $DB_site2->query($sql);

                        $js_popup_pj = "var w=document.open();w.document.write(\"Erreur EDI FEDEX Express<br>".$tnt->getError()."\");";

                    } else {

                            $tnt->etiquette(true, $parent);
                            $ef->trackid = $tnt->trackid();
                            $ef->update();

                            $js_popup_pj = "window.open('userfiles/".$tnt->pjid().".pdf')";

                    }
                }

                //Génération de l'étiquette par WEBTEL dans le cas de Schenker
                if($adv->destinataire == "DB Schenker Lyon" || $adv->destinataire == "DB Schenker France"){
                                	
					//EDI fusion
					include_once($rootpath."configurations/".$host."/transporteurs/schenker/schenker.php");
					$schenker = new schenker($DB_site2,$adv,$compte_facturation,$ftp,$host);
					$schenker->etiquette(true, $parent);
            		$schenker->send();
                	$ef->trackid = $schenker->trackid();
                    $ef->update();
                    $edi = true;

                    //verif si edi s'envoie bien
                    $edi_envoie_verif="pas d'erreur";
                    if($schenker->send()==false){
                    	$edi_envoie_verif="error";
                    }
                    $mail=new email();
                    $mail->destinataire="webmaster4@mon-espace-cuisson.com";
                    $mail->expediteur="webmaster4@mon-espace-cuisson.com";
                    $mail->expname="Ma Cave à Vin";
                    $mail->subject="edi envoyé à schenker";
                    $mail->body="un edi a normalement été envoyé à schenker. A vérifier. commande ".$facturid. " : ".$edi_envoie_verif;
                    $mail->sendmail();

                    //station chargeur
                    /*$webtel = new webtel($DB_site2,$adv,'schenkernat');
                    
                    $webtel->filename = "expeditions_schenker";
                    $webtel->ext      = "txt";
                    $webtel->dossier  = '/configurations/'.$host.'/exports/';

                    $webtel->lignes = array(
                        "S", //Séparateur de champs
                        $webtel->date_exp, //Date expédition
                        "01", //Code transporteur/prestation
                        $webtel->export?"60":$webtel->trspttype, //Code produit transport/facturation
                        "01", //Code expéditeur
                        "", //Libellé expéditeur
                        "", //Code destinataire
                        $webtel->raison_sociale, //Nom 1
                        $webtel->compl_nom,	//Nom 2
                        "",	//Nom 3
                        $webtel->adresse, //Adresse 1
                        $webtel->telephone != '' ? 'Tél : '.$webtel->telephone : '', //Adresse 2
                        "",	//Adresse 3
                        $webtel->pays, //Pays
                        $webtel->codepostal, //Code postal
                        $webtel->ville, //Localité
                        $webtel->interlocuteur,	//Contact
                        $webtel->interlocuteur != '' ? $webtel->telephone : '',	//Téléphone
                        "",	//Récépissé
                        $webtel->facturid, //Référence d'expédition
                        "P",	//Port ou Incoterm
                        $webtel->nb_manu, //Nombre d'UM
                        $webtel->nb_colis, //Nombre de colis
                        $webtel->nb_manu_consigne, //Nombre de supports consignés
                        $webtel->manu_consigne_type,	//Type de supports consignés
                        $webtel->poids,	//Poids réel
                        "", //Unités taxables
                        "",	//Type d'unités taxables
                        $webtel->volume,	//Volume
                        $webtel->ctr, //Contre-remboursement
                        $webtel->ctr != '' ? 'EUR' : '',	//Devise C-R
                        "", //$webtel->valeur_declaree, //Valeur déclarée
                        $webtel->valeur_declaree != '' ? 'EUR' : '',	//Devise V D
                        "",	//Non utilisé actuellement
                        "",	//Non utilisé actuellement
                        $webtel->nature,	//Nature de marchandises
                        $webtel->info_trspt,	//Instructions de livraison
                        $webtel->info_comp,	//Remarques de livraison
                        $webtel->date_imperative,	//Date impérative de livraison
                        "",	//Code imprimante
                        $webtel->particulier, //Livraison particulier
                        $webtel->annonce, //Annonce au destinataire
                        $webtel->interlocuteur != '' ? $webtel->portable : '',	//Téléphone n°2 (portable)
                        $webtel->interlocuteur != '' ? $webtel->email : '',	//Adresse mail
                        "",	//Mètre plancher
                        "",	//Date de début livraison souhaitée
                        "",	//Heure de début livraison souhaitée
                        "",	//Date de fin livraison souhaitée
                        ""	//Heure de fin livraison souhaitée
                    );

                    $webtel->makeCSV();*/
                    
                    
                }

                //Génération de l'étiquette par WEBTEL dans le cas d'Heppner
                if($adv->destinataire == "HEPPNER Le Mans" || $adv->destinataire == "HEPPNER LYON"){

                    //EDI pour fusion
                    include_once($rootpath."configurations/".$host."/transporteurs/heppner/heppner.php");
                    $heppner=new heppner($DB_site2,$adv,$ftp,$host);
                    $heppner->etiquette(true, $parent);
                    $heppner->send();
                    
                    $ef->trackid = $heppner->trackid();
                    $ef->update();
                    $edi = true ;
                }
            }
		}

		$pj=array();
		$attachements=array();
        $mail=new email();
        if($adv->fields["envoiedi"]!="1"){
	        $txt_mail_to = $adv->fields['typetrspt'];
        }else{
        	$txt_mail_to = "EDI ".$adv->fields['trsptname'];
        }
        $mail->destinataire=$adv->fields['typetrspt'];
		$mail->copymode="bcc";
		$mail->to=array("adv@mon-espace-cuisson.com");
	    $mail->expediteur="adv@mon-espace-cuisson.com";
	    $mail->expname="Ma Cave à Vin";
	    $mail->subject=$subject;
	    $mail->body=$text;

        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        );
	    
        if($newcmd == true){
	    	$passe = $DB_site2->query_first("SELECT passefacture FROM facture WHERE facturid=".$facturid);
	    	
	    	//Récupération des différents colis
                $colis   = $adv->colis();
                $nbcolis = count($colis);
                $prodparam = "";
                if($adv->fields['trspt_ctr_remb'] == 1){
                        $prodparam.="&ctr_remboursement=".$adv->fields['ctr_remb_montant'] ;
                }
	    	
	    	//CAS MULTI COLIS
	    	if($nbcolis > 0){
				
                    //Génération des différents BL
                    $cpt = 1;
                    foreach($colis as $colisnb => $lines){

                        $prodparam="";
                        foreach($lines as $lid => $qte){
                                $prodparam .= "&produits[]=".$qte."-".$lid;
                        }
                        $prodparam .= "&colis=".$colisnb."-".$nbcolis;
                        $colis_h=intval($adv->fields['colis_h_'.$colisnb]);
                        $colis_l=intval($adv->fields['colis_l_'.$colisnb]);
                        $colis_p=intval($adv->fields['colis_p_'.$colisnb]);
                        if($colis_h>0 && $colis_l>0 && $colis_p>0) $prodparam.="&dimensions=".$colis_h."-".$colis_l."-".$colis_p;

                        
                        //Si l'ADV a coché "Bon de transport", on génère les BL
                        $tmpfilelist=array();
                        if($adv->fields['bonstrsptpj'] == 1){
                                $data          = file_get_contents(SITE_URL."/facture2.php?facturid=".$facturid."&cde=1&passe=".$passe[0].$prodparam,FALSE,stream_context_create($arrContextOptions));
                                $tmpfile       = tempnam("/tmp","bl");
                                $tmpfilelist[] = $tmpfile;

                                file_put_contents($tmpfile,$data);
                                
                                if($tmpfilelist != array()){
    
                                        $blfile = "BL-".$facturid."-COLIS".$cpt."-".date("Ymd-Hi").".pdf";
                                        $tmpbl  = tempnam("/tmp","bl");
                                        shell_exec("pdftk ".implode(" ",$tmpfilelist)." output ".$tmpbl);
                                        $size = filesize($tmpbl);

                                        $sql = "INSERT INTO user_files SET";
                                        $sql .= " userid 	   = ".$facture->userid."";
                                        $sql .= ", facturid    = ".$facture->facturid."";
                                        $sql .= ", date 	   = ".time()."";
                                        $sql .= ", utilisateur = '".$user_info['username']."'";
                                        $sql .= ", name 	   = '".mysqli_real_escape_string($DB_site2->link_id,$blfile)."'";
                                        $sql .= ", size 	   = ".$size;
                                        $sql .= ", ext 		   = 'pdf'";
                                        $sql .= ", state 	   = 1";
                                        $sql .= ", parent  	   =".$parent;
                                        $DB_site2->query($sql);
                                        $fileid = $DB_site2->insert_id();
                                        
                                        $data    = file_get_contents($tmpbl);
                                        $newname = __DIR__."/../userfiles/".$fileid.".pdf";
                                        rename($tmpbl,$newname);
                                        chmod($newname, 0644);
                                        $mail->attachstring($data,$blfile,"application/pdf");
                                        $pj[] = $blfile;
                                        $attachements[]=SITE_URL."/facture2.php?facturid=".$facturid."&cde=1&passe=".$passe[0].$prodparam;
                                }
                        }
                        
                        //Dans le cas d'un BL avec livraison chez un installateur, remplace l'adresse du client par celle de l'installateur
                        $tmpfilelist=array();
                        if($blsls){
                                $data          = file_get_contents(SITE_URL."/facture2.php?facturid=".$facturid."&cde=1&passe=".$passe[0]."&livr=".$blsls.$prodparam,FALSE,stream_context_create($arrContextOptions));
                                $tmpfile       = tempnam("/tmp","bl");
                                $tmpfilelist[] = $tmpfile;

                                file_put_contents($tmpfile,$data);
                                
                                if($tmpfilelist != array()){
    
                                        $blfile = "BL-".$facturid."-COLIS".$cpt."-installateur-".date("Ymd-Hi").".pdf";
                                        $tmpbl  = tempnam("/tmp","bl");
                                        shell_exec("pdftk ".implode(" ",$tmpfilelist)." output ".$tmpbl);
                                        $size = filesize($tmpbl);

                                        $sql = "INSERT INTO user_files SET";
                                        $sql .= " userid 	   = ".$facture->userid."";
                                        $sql .= ", facturid    = ".$facture->facturid."";
                                        $sql .= ", date 	   = ".time()."";
                                        $sql .= ", utilisateur = '".$user_info['username']."'";
                                        $sql .= ", name 	   = '".mysqli_real_escape_string($DB_site2->link_id,$blfile)."'";
                                        $sql .= ", size 	   = ".$size;
                                        $sql .= ", ext 		   = 'pdf'";
                                        $sql .= ", state 	   = 1";
                                        $sql .= ", parent      = ".$parent;
                                        $DB_site2->query($sql);
                                        $fileid = $DB_site2->insert_id();
                                        
                                        $data    = file_get_contents($tmpbl);
                                        $newname = __DIR__."/../userfiles/".$fileid.".pdf";
                                        rename($tmpbl,$newname);
                                        chmod($newname, 0644);
                                        $mail->attachstring($data,$blfile,"application/pdf");
                                        $pj[] = $blfile;
                                        $attachements[]=SITE_URL."/facture2.php?facturid=".$facturid."&cde=1&passe=".$passe[0]."&livr=".$blsls.$prodparam;
                                }
                        }
                       
	                $cpt++;
                    }
                
            //CAS 1 SEUL COLIS
            } else {

                        $prodparam .= "&produits[]=".implode("&produits[]=",array_keys($adv->lignes));
                        $colis_h=intval($adv->fields['colis_h_']);
						$colis_l=intval($adv->fields['colis_l_']);
						$colis_p=intval($adv->fields['colis_p_']);
						if($colis_h>0 && $colis_l>0 && $colis_p>0) $prodparam.="&dimensions=".$colis_h."-".$colis_l."-".$colis_p;
                        if($adv->fields['bonstrsptpj'] == 1){

                                $data   = file_get_contents(SITE_URL."/facture2.php?facturid=$facturid&cde=1&passe=".$passe[0].$prodparam,FALSE,stream_context_create($arrContextOptions));
                                $blfile = "BL-".$facturid."-".date("Ymd-Hi").".pdf";
                                $size   = strlen($data);

                                $sql = "INSERT INTO user_files SET";
                                $sql .= " userid 	   = ".$facture->userid;
                                $sql .= ", facturid    = ".$facture->facturid;
                                $sql .= ", date 	   = ".time();
                                $sql .= ", utilisateur = '".$user_info['username']."'";
                                $sql .= ", name 	   = '".mysqli_real_escape_string($DB_site2->link_id,$blfile)."'";
                                $sql .= ", size 	   = ".$size;
                                $sql .= ", ext 		   = 'pdf'";
                                $sql .= ", state 	   = 1";
                                $sql .= ", parent      = ".$parent;
                                $DB_site2->query($sql);
                                $fileid = $DB_site2->insert_id();

                                $newname = __DIR__."/../userfiles/".$fileid.".pdf";
                                file_put_contents($newname,$data);
                                chmod($newname, 0644);
                                $mail->attachstring($data,$blfile,"application/pdf");
                                $pj[] = $blfile;
                                $attachements[]=SITE_URL."/facture2.php?facturid=$facturid&cde=1&passe=".$passe[0].$prodparam;
                        }

                        if($blsls){

                                $data   = file_get_contents(SITE_URL."/facture2.php?facturid=".$facturid."&nopub=1&cde=1&passe=".$passe[0]."&livr=".$blsls.$prodparam,FALSE,stream_context_create($arrContextOptions));
                                $blfile = "BL-".$facturid."-installateur-".date("Ymd-Hi").".pdf";
                                $size   = strlen($data);

                                $sql = "INSERT INTO user_files SET";
                                $sql .= " userid 	   = ".$facture->userid;
                                $sql .= ", facturid    = ".$facture->facturid;
                                $sql .= ", date 	   = ".time();
                                $sql .= ", utilisateur = '".$user_info['username']."'";
                                $sql .= ", name 	   = '".mysqli_real_escape_string($DB_site2->link_id,$blfile)."'";
                                $sql .= ", size 	   = ".$size."";
                                $sql .= ", ext 		   = 'pdf'";
                                $sql .= ", state 	   = 1";
                                $sql .= ", parent      = ".$parent;
                                $DB_site2->query($sql);
                                $fileid = $DB_site2->insert_id();

                                $newname = __DIR__."/../userfiles/".$fileid.".pdf";
                                file_put_contents($newname,$data);
                                chmod($newname, 0644);
                                $mail->attachstring($data,$blfile,"application/pdf");
                                $pj[] = $blfile;
                                $attachements[]=SITE_URL."/facture2.php?facturid=".$facturid."&nopub=1&cde=1&passe=".$passe[0]."&livr=".$blsls.$prodparam;
                        }
                        
            }
	    
            if($adv->fields['facturetrsptpj'] == 1){
				
				//$_GET['join']=true;
				//$mail->attachstring(include("../facture2.php"),"Facture $facturid.pdf","application/pdf");
				$data = file_get_contents("http://".$host_conf->http_host."/facture2.php?facturid=".$facturid."&nopub=1&passe=".$passe[0],FALSE,stream_context_create($arrContextOptions));
				$mail->attachstring($data,"Facture ".$facturid.".pdf","application/pdf");
				$pj[] = "Facture ".$facturid.".pdf";
				$attachements[] = "http://".$host_conf->http_host."/facture2.php?facturid=".$facturid."&nopub=1&passe=".$passe[0];
            }

            if($adv->fields['othertrsptpj']==1 && !empty($_FILES['othertrsptpjfile'])){
				
				$data = file_get_contents($_FILES['othertrsptpjfile']['tmp_name']);
				$mime = $DB_site2->fetch_array($DB_site2->query("SELECT * FROM MimeType WHERE g_extension='".pathinfo($_FILES['othertrsptpjfile']['tmp_name'], PATHINFO_EXTENSION)."'"));
				$mail->attachstring($data,$_FILES['othertrsptpjfile']['name'],$mime['g_mimeType']);
				$pj[]=$_FILES['othertrsptpjfile']['name'];
				$attachements[] = $_FILES['othertrsptpjfile']['tmp_name'];
            }
        }

        $action="editer";
        if($edi){ // envoi par edi
        	$dest = $DB_site2->query_first("SELECT id FROM fournisseur WHERE societe ='".$adv->destinataire."' AND type='trsptadv'");
        	
        	$DB_site2->query("INSERT INTO suivi_commande SET facturid=$facturid, date=".time().",destinataire ='".$dest[0]."',type='pro', commercial='".$user_info[username]."', suivi='".addslashes("Passage de commande à ".$_POST['trsptname']."<br>".$mail->body)."'");

        }elseif($adv->fields['typetrspt'] != "0" && $adv->fields['envoiedi']=='0'){
            $mail->sendmail();  // on envoie pas de mail s'il n'y a pas de transport

        	$DB_site2->query("INSERT INTO mail_outbox SET date=".time().", utilisateur='".$user_info[username]."', `to`='".$txt_mail_to."', `from` = '".$user_info['mail_contactFR']."', subject='".$mail->subject."', body='".mysqli_real_escape_string($DB_site2->link_id,$mail->body)."', pj='".mysqli_real_escape_string($DB_site2->link_id,implode(',',$pj))."', facturid=".$facturid.",userid='".$facture->userid."',fournisseur='".mysqli_real_escape_string($DB_site2->link_id,$adv->destinataire)."', uniqueid='".$mail->uniqueid."', compta ='non'");
       		/// enregistre les pièces jointes :
        	$mailid = $DB_site2->insert_id();
        	$i=0;
        	foreach($attachements as $attachement){
        		$data = file_get_contents($attachement,FALSE,stream_context_create($arrContextOptions));
        		$ext = strtolower(pathinfo($pj[$i], PATHINFO_EXTENSION));
        		$sql = "INSERT INTO mail_attachment SET moyen='mail_out', mailid='$mailid', name='".mysqli_real_escape_string($DB_site2->link_id,$pj[$i])."', ext='$ext', size='".strlen($data)."'";
        		$DB_site2->query($sql);
        		$pjid = $DB_site2->insert_id();
        		file_put_contents($rootpath."configurations/".$host."/mails/attachments/".date("Y",time())."/".$pjid.".".$ext,$data);
        		$i++;
        	}        
        }

    }else{
		apercu(($edi?'':$adv->fields['typetrspt']),$subject,$pj[0] ."-".$text);
        
    }

}

?>
