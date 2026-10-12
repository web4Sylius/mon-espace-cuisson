<?php

function mail_service($adv,$preview=false){
     global $DB_site2;
     global $user_info;
     global $adv_facture;
     
     $facturid=$adv->facturid;
     // $facture=new facture($DB_site2,$facturid);
	 
	 
	 
     //Récupération des informations de la commande
     $sql = "SELECT *";
     $sql .= " FROM facture f";
     $sql .= " INNER JOIN pays p USING (paysid)";
     $sql .= " WHERE f.facturid = ".$facturid;
     $tabFacture = $DB_site2->query_first($sql);
     
     $subject   = "";
     $text      = "";
     $tsindate  = 0;
     $tsoutdate = 0;
     $imperatif = 0;

     if(isset($adv->fields['miseenservicedateprecise']) && $adv->fields['miseenservicedateprecise'] != "") $tsindate = $adv->date2ts($adv->fields['miseenservicedateprecise']);
     if(isset($adv->fields['miseenservicedateapprox']) && $adv->fields['miseenservicedateapprox'] != "") $tsindate  = $adv->date2ts($adv->fields['miseenservicedateapprox']);
     if(isset($adv->fields['servicedateprecise']) && $adv->fields['servicedateprecise'] == 1) $imperatif  = 1;
     
     switch($adv->fields['serviceobj']){
          
          case "cmd":
               
               $subject = "Commande d'installation et de mise en service n°".$facturid;
               $text    = ""
               . "Bonjour,"
               . "<br>"
               . "Veuillez trouver ci-joint une nouvelle commande pour installation et mise en service n°".$facturid;
               $presta  = "<br><br><br>"
               . "<b><u>Prestation</u></b>"
               . "<br><br>"
               . "<b>Votre prestation comprend, dans tous les cas</b> :<br>"
               . "- La prise de contact par téléphone avec le client pour :<br>"
               . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp. S'assurer que le client ait bien reçu le ou les produits à installer et en bon état.<br>"
               . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;. Si nécessaire : vérifier le type de tuyau gaz à fournir (par vos soins).<br>"
               . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;. Si nécessaire : vérifier le type de prise électrique + câble nécessaires en fonction de la puissance à fournir (par vos soins).<br>"
               . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;. S'assurer auprès du client que tout est prêt pour votre intervention : installation de gaz, électricité,  meubles conformes, etc .. énergies installées, adaptées et opérationnelles à proximité de votre intervention.<br>"
               . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;. Prendre RDV (date et heure approximatives d'intervention à définir en accord avec le client).<br>"
               . "- Le déplacement, la main d'oeuvre et les petites fournitures :"
               . "<br><br>"
               . "<b>Votre prestation ne comprend pas, dans tous les cas</b> :"
               . "<br>"
               . "Tous travaux d'électricité, plomberie, gaz, maçonnerie, menuiserie, etc... <strong>soumis à responsabilité décennale</strong>. (Ex : La réalisation de l'arrivée électrique et de gaz à proximité, un conduit de cheminées pour une hotte, la réalisation/modification des meubles de cuisine pour les éléments encastrables, plan de travail pré-découpé, etc...)"
               . "<br><br>"
               . "<b>Vous pouvez toutefois facturer directement au client</b> :<br>"
                    . "- Les raccords et accessoires non prévus<br>"
                    . "- Tous travaux supplémentaires que le client souhaiterait vous faire réaliser (avec votre accord et sous votre responsabilité)"
               . "<br><br>";
               
               $fin = ""
               . "<br><br>"
               . "Cordialement,"
               . "<br>"
               . $user_info['nom_contact']." - ".(defined('NOM_SOCIETE') ? NOM_SOCIETE : 'ACILYON DEVELOPPEMENTS')." - ".(defined('SITE_URL') ? SITE_URL : 'www.mon-espace-cuisson.com')
               . "<br>"
               . "Tél : ".(defined('CODE_TEL_ADV') ? CODE_TEL_ADV : '+33.4.81.91.90.41')." - Fax : ".(defined('CODE_FAX') ? CODE_FAX : '+33.4.26.03.05.49');
               
               $newcmd = true;
               
               break;
          
          case "annule":
               
               $subject = "Annulation de la commande d'installation et de mise en service n°".$facturid;
               $text    = ""
               . "Bonjour,"
               . "<br>"
               . "Merci d'annuler la commande d'installation et de mise en service n°".$facturid." et de nous le confirmer par retour de ce mail";
               $presta = '';
               $fin     = ""
               . "<br><br>"
               . "Cordialement,"
               . "<br>"
               . $user_info['nom_contact']." - ".(defined('NOM_SOCIETE') ? NOM_SOCIETE : 'ACILYON DEVELOPPEMENTS')." - ".(defined('SITE_URL') ? SITE_URL : 'www.mon-espace-cuisson.com')
               . "<br>"
               . "Tél : ".(defined('CODE_TEL_ADV') ? CODE_TEL_ADV : '+33.4.81.91.90.41')." - Fax : ".(defined('CODE_FAX') ? CODE_FAX : '+33.4.26.03.05.49');
               
               $newcmd = false;
          
               break;
          
          case "modif":
                    
               $ef = new envoi_fournisseur($DB_site2,$adv->efid);
               
               $subject = "Modification de la commande n°".$facturid;
               $text    = ""
               . "Bonjour,"
               . "<br>"
               . "Cette modification de commande annule et remplace la commande n°".$facturid." envoyée par mail le ".date("d/m/Y à H:i",$ef->date);  
               $presta = '';
               $fin     = ""
               . "Merci de nous confirmer cette modification par retour de ce mail."
               . "<br>"
               . "Cordialement"
               . "<br>"
               . $user_info['nom_contact']." - ".(defined('NOM_SOCIETE') ? NOM_SOCIETE : 'ACILYON DEVELOPPEMENTS')." - ".(defined('SITE_URL') ? SITE_URL : 'www.mon-espace-cuisson.com')
               . "<br>"
               . "Tél : ".(defined('CODE_TEL_ADV') ? CODE_TEL_ADV : '+33.4.81.91.90.41')." -  Fax: ".(defined('CODE_FAX') ? CODE_FAX : '+33.4.26.03.05.49');
               
               $newcmd = true;
               
               break;
               
               default:
               $newcmd = true;
     }
    
     $sql = "SELECT fc.compte_client, sf.societe_facturation_nom";
     $sql .= " FROM fournisseur_condition fc";
     $sql .= " LEFT JOIN societe_facturation sf ON (sf.societe_facturation_id = fc.societe_facturation_id)";
     $sql .= " WHERE fc.societe='".mysqli_real_escape_string($DB_site2->link_id,$adv->destinataire)."'";
     $fournisseur_condition = $DB_site2->query_first($sql);
     if($fournisseur_condition['compte_client'] != ""){
          $text .= "<p>Compte client : ".$fournisseur_condition['societe_facturation_nom']." - ".$fournisseur_condition['compte_client']."</p>";
     }
    
     if($adv->fields['infocomplement'] != "")
          $text .= "<p>".nl2br($adv->fields['infocomplement'])."</p>";

     $insertids = array();
     $annultxt  = "";
     
     $text .= $presta;
     
     //********************\\
     // LISTE DES PRODUITS \\
     //********************\\
     
     $text .= "<p><b><u>Produits :</u></b><br><br>";
     
     $cpt = 0;
     foreach($adv->lignes as $p => $qte){
          
          if($cpt > 0) $text .= '<br>';
          
          $sql = "SELECT l.*, a.artcode_eurocave, a.libelleFR, a.typearticleid, m.libelle as marque_nom";
          $sql .= " FROM lignefacture l";
          $sql .= " INNER JOIN article a ON (a.artid = l.artid)";
          $sql .= " INNER JOIN article_marque am ON (am.artid = a.artid)";
          $sql .= " INNER JOIN marque m ON (m.marqueid = am.marqueid)";
          $sql .= " WHERE l.lignefactureid = ".$p;
          $lignefacture = $DB_site2->query_first($sql);
          
          //Description de l'article
          $text .= "<u>Quantité</u> : ".$lignefacture['qte']."<br>";
          $text .= "<u>Réf.</u> : ".$lignefacture['artcode']." (".$lignefacture['artcode_eurocave'].")<br>";
          $text .= "<u>Marque</u> : ".$lignefacture['marque_nom']."<br>";
          $text .= "<u>Libellé</u> : ".$lignefacture['libelleFR']."<br>";
          
          $sql = "SELECT installation_commande";
          $sql .= " FROM installation";
          $sql .= " WHERE installation_typearticle LIKE '|".$lignefacture['typearticleid']."|'";
          $desc_installation = $DB_site2->query_first($sql);
          
          if(!empty($desc_installation['installation_commande'])) $text .= "<u>Prestation</u> : <br>".$desc_installation['installation_commande'];
          
          $cpt++;
     }

     $text .= "</p>";
     
     //****************\\
     // PIECES JOINTES \\
     //****************\\
     
     $text .= "<p><b><u>Pièces jointes :</u></b><br>";
     
     if(isset($adv->fields['servicepj']) && $adv->fields['servicepj'] == 0) $text .= "<br>Aucune";
     if(isset($adv->fields['noticeservicepj']) && $adv->fields['noticeservicepj'] == 1){
          
          $text .= "<br>Notice de montage";
          
          if(isset($adv->fields['noticeservicepjname']) && $adv->fields['noticeservicepjname'] != '') $text .= " : ".$adv->fields['noticeservicepjname'];
     }
     if(isset($adv->fields['autreservicepj']) && $adv->fields['autreservicepj'] == 1){
          
          if(isset($adv->fields['autreservicepjname']) && $adv->fields['autreservicepjname'] != '') $text .= "<br>".$adv->fields['autreservicepjname'];
     }     
     
     $text .= "</p>";
     
     //****************************\\
     // DATE & LIEU D'INTERVENTION \\
     //****************************\\
     
     $text .= "<p><b><u>Date & lieu d'intervention :</u></b><br>";
          
     //Prise de contact avec le client
     if(isset($adv->fields['serviceintervcontact']) && $adv->fields['serviceintervcontact'] == 1){

          $text .= "<br>Prendre contact avec le client, au <b>".$adv->fields['serviceintervtelclient']."</b>";
     }

     //Date d'intervention précise
     if(isset($adv->fields['servicedateprecise']) && $adv->fields['servicedateprecise'] == 1){

          $text .= "<br>Intervention à effectuer impérativement le <b>".$adv->fields['miseenservicedateprecise']."</b> à l'adresse :";
     }

     //Date d'intervention approximative
     if(isset($adv->fields['servicedateapprox']) && $adv->fields['servicedateapprox'] == 1){

          $text .= "<br>Intervention à effectuer aux alentours du <b>".$adv->fields['miseenservicedateapprox']."</b> à l'adresse :<br>";
     }
     
     //Adresse d'installation
     switch($adv->fields['servicelieuinterv']){
          
          case 'facturation':

               //Récupération de l'adresse de facturation
               $sql = "SELECT u.*, p.libelle as 'pays_nom'";
               $sql .= " FROM utilisateur u";
               $sql .= " INNER JOIN pays p USING (paysid)";
               $sql .= " WHERE u.userid = ".$tabFacture['userid'];
               $tabUtilisateur = $DB_site2->query_first($sql);

               if(!empty($tabUtilisateur['raisonsociale'])) $text .= '<br>'.$tabUtilisateur['raisonsociale'];
               $text .=  '<br>'.$tabUtilisateur['prenom'].' '.$tabUtilisateur['nom'];
               $text .=  '<br>'.$tabUtilisateur['adresse'];
               if(!empty($tabUtilisateur['adresse2'])) $text .=  '<br>'.$tabUtilisateur['adresse2'];
               $text .=  '<br>'.$tabUtilisateur['codepostal'].' '.$tabUtilisateur['ville'];
               $text .=  '<br>'.$tabUtilisateur['pays_nom'];

               break;
          case 'livraison':

               if(!empty($rs_facture['raisonsociale'])) $text .= '<br>'.$tabFacture['raisonsociale'];
               $text .= '<br>'.$tabFacture['prenom'].' '.$tabFacture['nom'];
               $text .= '<br>'.$tabFacture['adresse'];
               if(!empty($tabFacture['adresse2'])) $text .= '<br>'.$tabFacture['adresse2'];
               $text .= '<br>'.$tabFacture['codepostal'].' '.$tabFacture['ville'];
               $text .= '<br>'.$tabFacture['pays_nom'];

               break;
          case 'autre':

               $text .= nl2br($adv->fields['adresse_intervention']);

               break;
     }
     
     $text.="</p>";
     
     //***********************\\
     // PRIX D'ACHAT TOTAL HT \\
     //***********************\\
     
     $text .= "<p><b><u>Montant total HT de la prestation</u></b> :</b> ".number_format($adv->fields['coutmiseenservice'],2,',',' ')." € HT</p>";
     
     //***********\\
     // FIN EMAIL \\
     //***********\\
     
     $text .= $fin;
     
     if(!$preview){
          
          if($adv->fields['serviceobj'] == "annule" || $adv->fields['serviceobj'] == "modif"){
               
	    $ef = new envoi_fournisseur($DB_site2,$adv->efid);
	    $ef->delete();
	    $adv->efid = 0;
	    $adv->update();
          }

          if($newcmd == true){
	    
               $listefl = array();
               
               $pahtu = round($adv->fields['coutmiseenservice'] / count($adv->lignes),2);
	    
               foreach($adv->lignes as $p => $qte){
                    
                    $efl = new envoi_fournisseur_ligne($DB_site2);
                    $efl->indate         = $tsindate;
                    $efl->outdate        = $tsoutdate;
                    $efl->imperatif      = $imperatif;
                    $efl->expediteur     = $user_info['username'];
                    $efl->type           = "service";
                    $efl->lignefactureid = $p;
                    $efl->paht           = $pahtu;
                    $efl->qte            = 1;
                    $listefl[] = $efl;
               }
               
               $ef = new envoi_fournisseur($DB_site2);
               $ef->facturid     = $facturid;
               $ef->destinataire = $adv->destinataire;
               $ef->tarif        = $adv->fields['coutmiseenservice'];
               $ef->type         = "service";
               $ef->create();
               $ef->create_lines($listefl);
               $adv->efid=$ef->id;
               $adv->update();

          }

          $pj = array();
          $mail = new email();
          $mail->destinataire = $adv->fields['livrservice'];
          $mail->copymode     = "bcc";
          $mail->to           = array(defined('MAIL_CONTACT') ? MAIL_CONTACT : 'contact@mon-espace-cuisson.com',defined('MAIL_ADV') ? MAIL_ADV : 'adv@mon-espace-cuisson.com');
          $mail->expediteur   = defined('MAIL_ADV') ? MAIL_ADV : 'adv@mon-espace-cuisson.com';
          $mail->expname      = defined('NOM_CONTACT') ? NOM_CONTACT : 'Mon Espace Cuisson';
          $mail->subject      = $subject;
          $mail->body         = $text;
	
          if($newcmd == true){
               $passefacture = "";
               if($adv->fields['noticeservicepj'] == 1){
                    $f = fopen($_FILES['noticeservicepjfile']['tmp_name'],"r");
                    if($f != false){
                         while(!feof($f))
                              $data .= fread($f,1024);
                         fclose($f);
                         $mail->attachstring($data,$_FILES['noticeservicepjfile']['name'],"application/octet-stream");
                         $pj[] = $_FILES['noticeservicepjfile']['name'];
                    }
               }
               
               if($adv->fields['autreservicepj'] == 1){
                   $f = fopen($_FILES['autreservicepjfile']['tmp_name'],"r");
                    if($f != false){
                         while(!feof($f))
                              $data .= fread($f,1024);
                         fclose($f);
                         $mail->attachstring($data,$_FILES['autreservicepjfile']['name'],"application/octet-stream");
                         $pj[] = $_FILES['autreservicepjfile']['name'];
                    }
               }
          }

          if($adv->fields['livrservice'] != "0"){
              $mail->sendmail();    // on envoie le mail seulement s'il y a une livraison
              $sql = "INSERT INTO mail_outbox SET";
              $sql .= " date         = ".time()."";
              $sql .= ", utilisateur = '".$user_info['username']."'";
              $sql .= ", `to`        = '".$mail->destinataire."'";
              $sql .= ", subject     = '".mysqli_real_escape_string($DB_site2->link_id,$mail->subject)."'";
              $sql .= ", body        = '".mysqli_real_escape_string($DB_site2->link_id,$mail->body)."'";
              $sql .= ", pj          = '".mysqli_real_escape_string($DB_site2->link_id,implode(',',$pj))."'";
			  $sql .= ", facturid    = ".$facturid;
			  $sql .= ", userid      = ".$tabFacture['userid'];
			  $sql .= ", fournisseur = '".mysqli_real_escape_string($DB_site2->link_id,$adv->destinataire)."'";
			  $sql .= ", uniqueid    = '".$mail->uniqueid."'";
			  $sql .= ", compta      = 'non'";
              $DB_site2->query($sql);
          }
     } else {
          apercu($adv->fields['livrservice'],$subject,$text);
     }
}