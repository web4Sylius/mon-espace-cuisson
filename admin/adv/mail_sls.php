<?php

function mail_sls($adv,$preview=false){
    global $DB_site2;
    global $rootpath;
    global $user_info;
    global $adv_facture;
    global $devises;
    global $http_conf;
    global $host;
    
    $facturid=$adv->facturid;
    $facture=new facture($DB_site2,$facturid);

    $fournisseur_condition=$DB_site2->query_first("SELECT fc.compte_client,fc.langue,sf.societe_facturation_nom FROM fournisseur_condition fc LEFT JOIN societe_facturation sf ON (sf.societe_facturation_id = fc.societe_facturation_id) WHERE societe='".mysqli_real_escape_string($DB_site2->link_id,$adv->destinataire)."'");
    if($fournisseur_condition['langue']=="") $fournisseur_condition['langue']="FR";
    $template=new template($fournisseur_condition['langue'],$DB_site2);
    $template->newlangue($fournisseur_condition['langue']);

    $tvafrance=$DB_site2->query_first("SELECT TVAtauxnormal FROM pays WHERE paysid=57");
    $subject="";
    $text="";
    $envoiedi=false;
    $parent=0;

    $tsindate=$tsoutdate=0;
    $imperatif=0;
    if($adv->fields['slslivrdate']!=""){
        $tsindate=$adv->date2ts($adv->fields['slslivrdate']);
    }
    if($adv->fields['slsmaxdate']!=""){
        $tsoutdate=$adv->date2ts($adv->fields['slsmaxdate']);
    }
    if($adv->fields['maxslslivr']=="precis") $imperatif=1;
    
    foreach($adv->lignes as $p=>$qte){
        $lignefacture=$DB_site2->query_first("SELECT *, (a.emballage_poids1+a.emballage_poids2+a.emballage_poids3) as emballage_poids FROM lignefacture l, article a WHERE l.artid=a.artid AND lignefactureid=".$p);
        $poids+=$lignefacture['qte']*$lignefacture['emballage_poids'];
    }	      

    
    $nbpersonne=2;
    switch($adv->fields['slsobj']){
        case "cmd":
            $subject=$template->translate("/admin/adv_install/commande_n","F")." #CDE".$facturid."#";
            $text.=sprintf($template->translate("/admin/adv_install/commande_installation"),$facturid);
            if($adv->fields['slscoutctrremb'] == 1) $text.= ", en contre remboursement";
            $fin=sprintf($template->translate("/admin/adv_install/fin_commande","F"),$user_info[nom_contact]);
            $newcmd=true;
        break;
        case "install":
            $subject=$template->translate("/admin/adv_install/commande_n","F").$facturid;
            $text.=sprintf($template->translate("/admin/adv_install/commande_installation_montage"),$facturid);
            $fin=sprintf($template->translate("/admin/adv_install/fin_commande","F"),$user_info[nom_contact]);
            $newcmd=true;
        break;
        case "annule":
            $subject=$template->translate("/admin/adv_install/sujet_annulation_command","F")." #CDE".$facturid."#";
            $text.=sprintf($template->translate("/admin/adv_install/annulation_commande"),$facturid);
            $fin="<br><br>".sprintf($template->translate("/admin/adv_install/fin_annulation","F"),$user_info[nom_contact]);
            $newcmd=false;
        break;
        case "modif":
            $ef=new envoi_fournisseur($DB_site2,$adv->efid);
            $subject=$template->translate("/admin/adv_install/sujet_modification_commande","F")." #CDE".$facturid."#";
            $text.=sprintf($template->translate("/admin/adv_install/modification_commande","F"),$facturid,date("d/m/Y à H:i",$ef->date));
            $fin=sprintf($template->translate("/admin/adv_install/fin_modification","F"),$user_info[nom_contact]);
            $newcmd=true;
        break;
        default:
            $newcmd=true;
        break;
    }


    //Creation/suppression du repertoire du transporteur pour les pièces jointes lui correspondant
    if(!$preview){
        if($adv->fields['slsobj'] == "annule" || $adv->fields['slsobj']=="modif"){
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

    
    if($fournisseur_condition['compte_client']!=""){
          $text .= "<p>Compte client : ".$fournisseur_condition['societe_facturation_nom']." - ".$fournisseur_condition['compte_client']."</p>";
    }

    if($adv->fields['slscoutctrremb'] == 1){ 
          
        $sql = "SELECT libelleFR";
        $sql .= " FROM moyenpaiement";
        $sql .= " WHERE moyenid = ".$adv->fields['sls_ctrremb_moyen'];
        $moyen_ctr_remb = $DB_site2->query_first($sql);
        
        $text .= "<font color=\"red\"><strong>Attention : commande avec contre-remboursement, d'un montant de ".number_format($adv->fields['sls_ctrremb_montant'],2,',',' ')." ".$devises->symbole($adv->devise)." (".strtolower($moyen_ctr_remb['libelleFR']).")</strong></font><br>";
     }
    
    if($adv->fields['infocomplement']!="")
        $text.="<p>".nl2br($adv->fields['infocomplement'])."</p>";

    $insertids=array();
    $annultxt="";
    $text.="<p><b>".$template->translate("/admin/adv_install/produits","F")."</b>";
    foreach($adv->lignes as $p=>$qte){
        $lignefacture=$DB_site2->query_first("SELECT *, l.libelle as libellefac, l.artid as articleid,(a.emballage_poids1+a.emballage_poids2+a.emballage_poids3) as emballage_poids FROM lignefacture l, article a WHERE (l.artid=a.artid OR l.artid=0) AND lignefactureid=".$p);
        if($lignefacture['articleid']==0){
            $text.="<br>".$template->translate("/admin/adv_install/qte")." :".$lignefacture['qte']." - ".$lignefacture['libellefac']." (".$template->translate("/admin/adv_install/voir_bl").")";
        }
        else {
        $text.="<br>".$template->translate("/admin/adv_install/qte")." :".$lignefacture['qte']." - ".$lignefacture['libelle'.$template->langue]." ".$lignefacture['emballage_poids']." kg (".$template->translate("/admin/adv_install/voir_bl").")";
        }
        if($lignefacture['emballage_poids']>=120) $nbpersonne=4;
    }
    $text.="</p>";
    $text.="<p><b>".$template->translate("/admin/adv_install/poids_total","F")."</b> ".$poids." kg</p>";

    //Infos sur la commande transporteur
    $adv_trspt = new adv($DB_site2);
    $adv_trspt->load_from_lignefactureid($lfid_trspt,'trspt');

    //Récupération de l'adresse de livraison installateur
    $sls_adresse = '';
    if(substr($adv_trspt->fields['trsptlivr'],0,3) == "sls")
    {
		$slsid = substr($adv_trspt->fields['trsptlivr'],3);
		
		$sql = "SELECT *";
		$sql .= " FROM fournisseur";
		$sql .= " WHERE id = '".$slsid."'";
		$aSLS = $DB_site2->query_first($sql);
		
		$sls_adresse = ""
		. $aSLS['nom'] . "<br>"
		. $aSLS['adresse1'] . "<br>"
		. ($aSLS['adresse2'] != '' ? $aSLS['adresse2'] . "<br>" : '')
		. $aSLS['cp'] . ' ' . $aSLS['ville'];
    }

    if($newcmd==true){
        
        $enlev=0;

        switch($adv->fields['slslivr']){
            case "0":
            break;
            case "livrtrspt":
                $trspt=$DB_site2->query_first("SELECT * FROM fournisseur WHERE id=".intval($adv->fields['livrtrsptid']));
                $text.="<p><b>".sprintf($template->translate("/admin/adv_install/livraison_en_vos_entrepots","F"),$trspt['societe'],'<b>'.date("d/m/Y",$tsindate).'</b>')." ".($adv->fields['name_agence']!=''?$adv->fields['name_agence']:$adv->fields['livrslsname'])."</b><br>";
                //$text.=$sls_adresse."<br><br>";
                //$text.=$template->translate("/admin/adv_install/colis_directement_livre","F");
                $text.=" ".$template->translate("/admin/adv_install/controle_etat","F").".";
                $enlev=$adv->fields['livrtrsptid'];
            break;
            case "livrfourn":
                $trspt=$DB_site2->query_first("SELECT * FROM fournisseur WHERE id=".intval($adv->fields['livrfournid']));
                $text.="<p><b>".sprintf($template->translate("/admin/adv_install/livraison_en_vos_entrepots","F"),$trspt['societe'],'<b>'.date("d/m/Y",$tsindate).'</b>')." ".($adv->fields['name_agence']!=''?$adv->fields['name_agence']:$adv->fields['livrslsname'])."</b><br>";
                //$text.=$sls_adresse."<br><br>";
                //$text.=$template->translate("/admin/adv_install/colis_directement_livre","F");
                $text.=" ".$template->translate("/admin/adv_install/controle_etat","F").".";
                $enlev=$adv->fields['livrfournid'];
            break;
            case "livrstock":
                $lieu=$DB_site2->query_first("SELECT * FROM fournisseur WHERE id=".intval($adv->fields['livrstockid']));
                $text.="<p><b>".$template->translate("/admin/adv_install/lieu_enlevement","F")."</b><br>";
                $text.="$lieu[societe] $lieu[fonction]<br>$lieu[prenom] $lieu[nom]<br>$lieu[adresse1]".($lieu[adresse2]!=""?"<br>$lieu[adresse2]":"")."<br>$lieu[cp] $lieu[ville]<br>".$template->translate("/admin/adv_install/tel","F")." $lieu[telephone] / fax $lieu[fax]<br>$lieu[horaires]";
                $enlev=$adv->fields['livrstockid'];
            break;
            case "livrclient":
                $text.="<p><b>".$template->translate("/admin/adv_install/colis_livre_client","F")."</b></p>";
            break;
            case "other":
                $text.=$adv->fields['otherslslvr']." ".date("d/m/Y",$tsindate);
            break;
        }
        if($adv->fields['slsautreinfocheck']==1) $text.="<p>".$adv->fields['slsautreinfo']."</p>";
        
        $text.="</p>";
        $text.="<p><b>".$template->translate("/admin/adv_install/identification_colis","F")."</b>";
        if($adv->fields['slsidentbl']==1) $text.="<br>".$template->translate("/admin/adv_install/bl_fourni","F");
        if($adv->fields['slsidenttxt']==1) $text.="<br>".sprintf($template->translate("/admin/adv_install/texte_sur_colis","F"),$facturid,nl2br($adv->fields['slsidentpour']));
        if($adv->fields['slsidentimportant']==1) $text.="<br>".$template->translate("/admin/adv_install/documents_ebherhardt","F");
        if($adv->fields['trsptenldoc']==1) $text.="<br>".$template->translate("/admin/adv_install/enlever_documents","F");
        if($adv->fields['slsidentother']==1) $text.="<br>".$adv->fields['slsidentothertxt'];
        $text.="</p>";

        $advtrspt="";
        $text.="<p><b>".$template->translate("/admin/adv_install/adresse_livraison","F")."</b>:";
        
        $text.=" ".$template->translate("/admin/adv_install/prendre_rdv","F")." $adv_facture[telephone]
        <br>$adv_facture[raisonsociale] $adv_facture[nom] $adv_facture[prenom]<br>$adv_facture[adresse]<br>$adv_facture[codepostal] $adv_facture[ville]<br>$adv_facture[libelle]<br>$adv_facture[telephone]";
        $advtrspt="avec rdv";

        $text.="</p>";
        $text.="<p>";
        switch($adv->fields['maxslslivr']){
            case "precis":
                $text.=$template->translate("/admin/adv_install/livrer_exclusivement","F").date("d/m/Y",$tsoutdate);
            break;
            case "max":
                $text.="<b>".$template->translate("/admin/adv_install/date_installation_maxi","F")."</b>".date("d/m/Y",$tsoutdate);
            break;
            case "other":
                $text.=$adv->fields['slslivrotherdate'];
            break;
        }
        if($adv->fields['livraisonplustot']==1) $text.=". Voire la possibilité de livrer plus tôt";
        if(isset($adv->fields['slsinfoother'])) $text.="<br>".$template->translate("/admin/adv_install/infos_transporteur","F").": ".stripslashes($adv->fields['slsinfoothertxt']);
        $text.="</p>";
        
        if(!isset($adv->fields['slsinfo']) || empty($adv->fields['slsinfo'])){
            $text.="<p><b>".$template->translate("/admin/adv_install/infos_transporteur_installateur","F")."</b> :";
            if($template->langue=="FR" && $adv->fields['typetrspt'] == '0') $text.="<br>Livraison par $nbpersonne personnes";
            if($adv->fields['slsrdv']==1) $text.="<br>".$template->translate("/admin/adv_install/rdv_client","F");
            if($adv->fields['slsbranchement']==1) $text.="<br>".$template->translate("/admin/adv_install/ne_pas_brancher","F");
            if($adv->fields['slsmiseniveau']==1) $text.="<br>".$template->translate("/admin/adv_install/mise_a_niveau","F");
            if($adv->fields['slsmontage']==1) $text.="<br>".$template->translate("/admin/adv_install/montage_accessoires","F");
            if($adv->fields['slsemballage']==1) $text.="<br>".$template->translate("/admin/adv_install/recuperer_emballages","F");
            if($adv->fields['slsemballage2']==1) $text.="<br>".$template->translate("/admin/adv_install/laisser_emballages","F");
            if($adv->fields['slsinstallation']==1) $text.="<br>".$template->translate("/admin/adv_install/montage_mecanique","F");
            if($adv->fields['typetrspt'] != '0'){

                $oFournisseur = new Fournisseur($DB_site2);

                $aDetailsLivraison = $oFournisseur->getDetailsTypeLivraison($adv->fields['typetrspt']);

                $text .= '<br>' . $aDetailsLivraison['livr_type_nom'] . ': ' .$aDetailsLivraison['livr_type_desc'];
            }
            $text.="</p>";
        }

        $text.="<p><b>".$template->translate("/admin/adv_install/recuperation_produit","F")."</b> : ";
        if($adv->fields['recup']=="0") $text.=$template->translate("/admin/adv_install/aucune","F");
        if($adv->fields['recup']=="1") $text.=sprintf($template->translate("/admin/adv_install/recuperation_ancien_produit"),$adv->fields['recuppoids'])."</p>";
        if($adv->fields['recup']=="2"){
            $recupenvoi=$DB_site2->query_first("SELECT * FROM fournisseur WHERE id=".intval($adv->fields['recupretour']));
            $text.=$template->translate("/admin/adv_install/retour_ancien_produit","F").$recupenvoi['societe']."<br>".$recupenvoi['adresse1'].($recupenvoi['adresse2']!=""?"<br>".$recupenvoi['adresse2']:"")."<br>".$recupenvoi['cp']." ".$recupenvoi['ville']."<br>(".$adv->fields['recuppoids2']."kg)</p>";
        }
        if($adv->fields['recup']=="other") $text.=$adv->fields['recupother'];
        $text.="</p>";
        

        $totalsls=0;
        $assur_comp=0;
        $text.="<p><b>".$template->translate("/admin/adv_install/tarif","F")." :</b>";
        if($adv->fields['slscouttrspt']==1){
            $totalsls+=floatval($adv->fields['slscouttrsptval']);
            $text.="<br>".$adv->fields['slscouttrsptval']." ".$devises->symbole($adv->devise)." ".$template->translate("/admin/adv_install/cout_transport_installation","F");
        }
        if($adv->fields['slscoutctrremb']==1 && $adv->fields['slscoutctrrembval'] > 0) {
            $sql = "SELECT libelleFR";
            $sql .= " FROM moyenpaiement";
            $sql .= " WHERE moyenid = ".$adv->fields['sls_ctrremb_moyen'];
            $moyen_ctr_remb = $DB_site2->query_first($sql);

            $totalsls+=floatval($adv->fields['slscoutctrrembval']);
            $text.="<br>+ ".number_format($adv->fields['slscoutctrrembval'],2,',',' ')." ".$devises->symbole($adv->devise)." (contre remboursement)";
        }
        if($adv->fields['slscoutmontage']==1) {
            $totalsls+=floatval($adv->fields['slscoutmontageval']);
            $text.="<br>+ ".$adv->fields['slscoutmontageval']." ".$devises->symbole($adv->devise)." ".$template->translate("/admin/adv_install/montage_mecanique_assemblage","F");
        }
        if($adv->fields['slscoutcorse']==1){
            $totalsls+=floatval($adv->fields['slscorsetotal']);
            $text.="<br>+ ".$adv->fields['slscorsetotal']." ".$devises->symbole($adv->devise)." ".$template->translate("/admin/adv_install/passage_corse","F");
        }
        if($adv->fields['slscoutreprise']==1){
            $totalsls+=floatval($adv->fields['slscoutrepriseval']);
            $text.="<br>+ ".$adv->fields['slscoutrepriseval']." ".$devises->symbole($adv->devise)." ".$template->translate("/admin/adv_install/reprise_ancien_produit","F");
        }
        if($adv->fields['enigmacoutassur']==1){
            $totalsls+=floatval($adv->fields['enigmacoutassurval']);
            $text.="<br>+ ".$adv->fields['enigmacoutassurval']." ".$devises->symbole($adv->devise)." ".$template->translate("/admin/adv_install/assurance_complementaire","F");
        }
        if($adv->fields['slscoutassur']==1){
            $totalsls+=floatval($adv->fields['slscoutassurval']);
            $text.="<br>+ ".$adv->fields['slscoutassurval']." ".$devises->symbole($adv->devise)." ".$template->translate("/admin/adv_install/assurance_complementaire","F");
            $assur_comp=1;
        }
        if($adv->fields['enigmaprise']==1){
            $totalsls+=floatval($adv->fields['enigmapriseval']);
            $text.="<br>+ ".$adv->fields['enigmapriseval']." ".$devises->symbole($adv->devise)." ".$template->translate("/admin/adv_install/changement_prise","F");
        }
        if($adv->fields['slscoutother']==1){
            $totalsls+=floatval($adv->fields['slscoutotherval']);
            $text.="<br>+ ".$adv->fields['slscoutotherval']." ".$devises->symbole($adv->devise)." ".$adv->fields['slscoutothername'];
        }

        $text .= "<br>= ".$totalsls." ".$devises->symbole($adv->devise);
        if($adv->fields['slscoutassur'] == 1){
            $text .= "<br>".$template->translate("/admin/adv_install/valeur_declaree","F")." ".number_format(floatval($adv->fields['valeurtotalsls']),2,',','')." &euro; HT, ".number_format(floatval($adv->fields['valeurtotalsls'])*(1+$tvafrance[0]/100),2,',','')." &euro; TTC";
        }
        $text .= "</p>";
	}

    //photos articles
    $photos_sup = array_filter($adv->fields, "articlePhotoSup",ARRAY_FILTER_USE_KEY );
    $photos = array_filter($adv->fields, "articlePhoto",ARRAY_FILTER_USE_KEY );
	
    if(!empty($photos_sup) || !empty($photos)){
        $text.= "<br><br><strong>Merci de nous monter le(s) produit(s) monté(s) suivant la (ou les) photo(s) ci-jointe(s). </strong><br><br>";
    }

    $text.=$fin;
    
    
    if(!$preview){
        if($adv->fields['slsobj']=="annule" || $adv->fields['slsobj']=="modif"){
            $ef=new envoi_fournisseur($DB_site2,$adv->efid);
            $ef->delete();
            $adv->efid=0;
            $adv->update();
        }

        if($newcmd==true){
            $listefl=array();
            $pahtu=round($totalsls/count($adv->lignes),2);
            foreach($adv->lignes as $p=>$qte){
                $efl=new envoi_fournisseur_ligne($DB_site2);
                $efl->indate=$tsindate;
                $efl->outdate=$tsoutdate;
                $efl->imperatif=$imperatif;
                $efl->expediteur=$user_info['username'];
                $efl->type="sls";
                $efl->lignefactureid=$p;
                $efl->paht=$pahtu;
                $efl->devise=$adv->devise;
                $efl->qte=1;
                $listefl[]=$efl;
            }
            $ef=new envoi_fournisseur($DB_site2);
            $ef->facturid=$facturid;
            $ef->destinataire=$adv->destinataire;
            $ef->tarif=$totalsls;
            $ef->devise=$adv->devise;
            $ef->type="sls";
            $ef->lieu_enlev=$enlev;
            $ef->trspt=$advtrspt;
            $ef->assur_comp=$assur_comp;
            $ef->create();
            $ef->create_lines($listefl);
            $adv->efid=$ef->id;
            $adv->update();

        }

        if(!isset($_SERVER['DOCUMENT_ROOT'])) $_SERVER['DOCUMENT_ROOT']=$rootpath;
        $pj=array();
        $attachements=array();
        $mail=new email();
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
        
        if($newcmd==true){
            $colis   = $adv->colis();
            $nbcolis = count($colis);
    
            $prodparam = "";

            if($adv->fields['slscoutctrremb'] == 1){
                $prodparam.="&ctr_remboursement=".$adv->fields['sls_ctrremb_montant'] ;
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
                    
                    
                    //Si l'ADV a coché "Bon de transport", on génère les BL
                    $tmpfilelist=array();
                    if($adv->fields['bonslspj'] == 1){
                        $data          = file_get_contents(SITE_URL."/facture2.php?facturid=".$facturid."&cde=1".$prodparam,FALSE,stream_context_create($arrContextOptions));
                        $tmpfile       = tempnam("/tmp","bl");
                        $tmpfilelist[] = $tmpfile;

                        file_put_contents($tmpfile,$data);
                        
                        if($tmpfilelist != array()){

                            $blfile = "BL-".$facturid."-COLIS".$cpt."-".$adv->destinataire."-".date("Ymd-Hi").".pdf";
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
                            $attachements[]=SITE_URL."/facture2.php?facturid=".$facturid."&cde=1".$prodparam;
                        }
                    }

                    $cpt++;
                }
                    
                //CAS 1 SEUL COLIS
            } else {

                $prodparam .= "&produits[]=".implode("&produits[]=",array_keys($adv->lignes));
                
                if($adv->fields['bonslspj'] == 1){

                    $data   = file_get_contents(SITE_URL."/facture2.php?facturid=$facturid&cde=1".$prodparam,FALSE,stream_context_create($arrContextOptions));
                    $blfile = "BL-".$facturid."-".$adv->destinataire."-".date("Ymd-Hi").".pdf";
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
                    $attachements[]=SITE_URL."/facture2.php?facturid=$facturid&cde=1".$prodparam;
                }
            }
        
            if($adv->fields['factureslspj']==1){
                $data = file_get_contents(SITE_URL."/facture2.php?facturid=$facturid&nopub=1",FALSE,stream_context_create($arrContextOptions));
                $mail->attachstring($data,"Facture ".$facturid.".pdf","application/pdf");
                $pj[] = "Facture $facturid.pdf";
                $attachements[]=SITE_URL."/facture2.php?facturid=$facturid&nopub=1";
            }

            //photos images
            if(!empty($photos_sup)){
                   foreach($photos_sup as $photo){
                        if($photo != ""){
                             $artid = articleArtid($DB_site2,$photo);
                             $photoInfo = photoInfo($DB_site2,$photo,$rootpath,$artid);
                             if(file_exists($photoInfo['path'])){
                                  $mail->attachfile($photoInfo['path'],$photoInfo['name'], "image/jpg");
                                  $pj[] = $photoInfo['name'];
                                  $attachements[]=$photoInfo['path'];
                             }
                        }
                   }
             }

             if(!empty($photos)){
                foreach($photos as $photo){
                    if($photo != ""){
                        $path = $rootpath."configurations/".$host."/images/produits/".$photo.".jpg";
                        $name = $photo.".jpg";
                        if(file_exists($path)){
                           $mail->attachfile($path,$name, "image/jpg");
                           $pj[] = $name;
                           $attachements[]=$path;
                       }
                   }    
               }
           }

            if($adv->fields['otherslspj']==1 && !empty($_FILES['otherslspjfile']['name'])){
                $f=fopen($_FILES['otherslspjfile']['tmp_name'],"r");
                if($f!=false){
                    while(!feof($f))
                        $data.=fread($f,1024);
                    fclose($f);
                    $mail->attachstring($data,$_FILES['otherslspjfile']['name'],"application/octet-stream");
                    $extension = strtolower(pathinfo($_FILES['otherslspjfile']['name'], PATHINFO_EXTENSION));
                    $path = $_SERVER['DOCUMENT_ROOT']."/admin/userfiles/";
                    $filename = $_FILES['otherslspjfile']['name']."_".date("d-m-Y").".".$extension;

                    $DB_site2->query("INSERT INTO user_files SET userid=".$facture->userid.", facturid=".$facturid.", date=".time().", utilisateur='".$user_info['username']."', name='".mysqli_real_escape_string($DB_site2->link_id,$filename)."', size=".$_FILES['otherslspjfile']['size'].", ext='".$extension."'");
                    $idf = $DB_site2->insert_id();

                    if(!copy($_FILES['otherslspjfile']['tmp_name'], $path.$idf.".".$extension)){
                         $DB_site2->query("UPDATE user_files SET state=-1 WHERE id=$idf");
                    }else{
                         $DB_site2->query("UPDATE user_files SET state=1 WHERE id=$idf");
                    }

                    $pj[]=$_FILES['otherslspjfile']['name'];
                    $attachements[]=$_SERVER['DOCUMENT_ROOT']."/admin/userfiles/".$idf.".".$extension;
                }
            }
            if($adv->fields['otherslspj']==1 && !empty($_FILES['otherslspjfile_bis']['name'])){
                $f=fopen($_FILES['otherslspjfile_bis']['tmp_name'],"r");
                if($f!=false){
                    while(!feof($f))
                        $data.=fread($f,1024);
                    fclose($f);
                    $mail->attachstring($data,$_FILES['otherslspjfile_bis']['name'],"application/octet-stream");
                    $extension = strtolower(pathinfo($_FILES['otherslspjfile_bis']['name'], PATHINFO_EXTENSION));
                    $path = $_SERVER['DOCUMENT_ROOT']."/admin/userfiles/";
                    $filename = $_FILES['otherslspjfile_bis']['name']."_".date("d-m-Y").".".$extension;

                    $DB_site2->query("INSERT INTO user_files SET userid=".$facture->userid.", facturid=".$facturid.", date=".time().", utilisateur='".$user_info['username']."', name='".mysqli_real_escape_string($DB_site2->link_id,$filename)."', size=".$_FILES['otherslspjfile_bis']['size'].", ext='".$extension."'");
                    $idf = $DB_site2->insert_id();

                    if(!copy($_FILES['otherslspjfile_bis']['tmp_name'], $path.$idf.".".$extension)){
                         $DB_site2->query("UPDATE user_files SET state=-1 WHERE id=$idf");
                    }else{
                         $DB_site2->query("UPDATE user_files SET state=1 WHERE id=$idf");
                    }

                    $pj[]=$_FILES['otherslspjfile_bis']['name'];
                    $attachements[]=$_SERVER['DOCUMENT_ROOT']."/admin/userfiles/".$idf.".".$extension;
                }
            }
            if(isset($adv->fields['notice'])) {
                foreach($adv->fields['notice'] as $fileid) {
                    $file=$DB_site2->query_first("SELECT ff.*,a.artcode FROM article_files ff INNER JOIN article a USING(artid) WHERE id=".$fileid);
                    if($file!="") {
                        $filepath=$_SERVER['DOCUMENT_ROOT']."/admin/articlefiles/".$file['id'].".".$file['ext'];
                        if(file_exists($filepath)) {
                            $data=file_get_contents($filepath);
                            $mail->attachstring($data,"Notice ".$file['artcode'].".".$file['ext'],"application/octet-stream");
                            $pj[]="Notice ".$file['artcode'].".".$file['ext'];
                            $attachements[]=$_SERVER['DOCUMENT_ROOT']."/admin/articlefiles/".$file['id'].".".$file['ext'];;
                        }
                    }
                }
            }
                
            if($adv->destinataire=="AGEDISS"){
                $girard=new girard_agediss($DB_site2,$adv);
                $girard->etiquette(true, $parent);
                $envoiedi=$girard->send();
                
                $ef->trackid = $girard->trackid();
                $ef->update();
            }
        }
        
        //if(!$envoiedi)
        $mail->destinataire=$adv->fields['livrsls'];

        if($adv->fields['livrsls']!="0"){
            $mail->sendmail();    // on envoie le mail seulement s'il y a une livraison
            $DB_site2->query("INSERT INTO mail_outbox SET date=".time().", utilisateur='".$user_info[username]."', `to`='".$mail->destinataire."', `from` = '".$user_info['mail_contactFR']."', subject='".$mail->subject."', body='".mysqli_real_escape_string($DB_site2->link_id,$mail->body)."', pj='".mysqli_real_escape_string($DB_site2->link_id,implode(',',$pj))."', facturid=".$facturid.",userid='".$facture->userid."',fournisseur='".mysqli_real_escape_string($DB_site2->link_id,$adv->destinataire)."', uniqueid='".$mail->uniqueid."', compta ='non'");

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
        apercu($adv->fields['livrsls'],$subject,$text);
    }
}


function articlePhotoSup($key){
     if(strpos($key, "articlephotosup") !== false){
          return true;
     }
}
function articlePhoto($key){
     if(strpos($key, "articlephoto") !== false){
          return true;
     }
}
function articleArtid($DB, $photoid){
     $req = "SELECT artid FROM articlephoto WHERE articlephotoid = '$photoid'";
     $photo = $DB->query_first($req);
     return $photo['artid'];
}

function photoInfo($DB, $photoid, $rootpath, $artid){
     global $host;
     $req = "SELECT image FROM articlephoto WHERE articlephotoid = '$photoid'";
     $photo = $DB->query_first($req);
     $photo['path'] = $rootpath."configurations/".$host."/images/produits/".$artid."_".$photoid.".".$photo['image'];
     $photo['name'] = $photoid.".".$photo['image'];
     return $photo;
}

?>
