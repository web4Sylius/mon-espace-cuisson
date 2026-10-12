<?php
//Affichage pour paiement de type "Facilité de paiement"

$fcl = new facture($DB_site2,$facturid);

// AJOUT HISTORIQUE STATUT
if(isset($_POST['reglement_rcpt_id'])){
     
     // Mise au bon format de la nouvelle date
     $d = date_create_from_format("d/m/Y",$_POST['reglement_reception_date']);
     
     $params = array(
          "reception_id"   => intval($_POST['reglement_reception']),
          "reglement_id"   => intval($_POST['reglement_rcpt_id']),
          "reception_date" => $d->format("Y-m-d")
     );

     $sql = "INSERT INTO reglement_historique (";
     $sql .= " reglement_reception_id";
     $sql .= ", reglement_id";
     $sql .= ", reglement_reception_date";
     $sql .= ", reglement_historique_date";
     $sql .= ", reglement_historique_username";
     $sql .= ") VALUES (";
     $sql .= $params['reception_id'];
     $sql .= ", '".$params['reglement_id']."'";
     $sql .= ", '".$params['reception_date']."'";
     $sql .= ", NOW()";
     $sql .= ", '".$_COOKIE['admin_username']."'";
     $sql .= ")";
     $DB_site2->query($sql);
     
     $params = array(
         'suivi' => 'Changement de statut du règlement n°'.$_POST['reglement_rcpt_id'].' : '.$fcl->getDetailsReception($_POST['reglement_reception'])
     );
     $fcl->addSuivi($params);
}

// AJOUT/EDITION PAIEMENT
if(isset($_POST['reglement_id'])){
     
     $sql = "SELECT *";
     $sql .= " FROM utilisateur";
     $sql .= " WHERE userid = ".$fcl->userid;
     $tab_user = $DB_site2->query_first($sql);
     
     // Mise au bon format de la nouvelle date
     $d = date_create_from_format("d/m/Y",$_POST['reglement_date']);

     $params = array(
          "montant"     => floatval($_POST['reglement_montant']),
          "moyen"       => intval($_POST['reglement_moyen']),
          "delais"      => intval($_POST['reglement_delais']),
          "date"        => $d->format("Y-m-d H:i:s"),
          "nocmd"       => $fcl->facturid,
          "commentaire" => $_POST['reglement_commentaire']
     );

     if($_POST['reglement_id'] == 0){ // Ajout d'un règlement

          $sql = "INSERT INTO reglement (";
          $sql .= "reglement_nom";
          $sql .= ", reglement_prenom";
          $sql .= ", reglement_societe";
          $sql .= ", reglement_email";
          $sql .= ", reglement_tel";
          $sql .= ", reglement_montant";
          $sql .= ", reglement_commentaires";
          $sql .= ", reglement_moyen";
          $sql .= ", reglement_date";
          $sql .= ", reglement_delais_id";
          $sql .= ", facturid";
          $sql .= ", userid";
          $sql .= ") VALUES (";
          $sql .= "'".mysqli_real_escape_string($DB_site2->link_id,strtoupper($params['nom']))."'";
          $sql .= ", '".mysqli_real_escape_string($DB_site2->link_id,$params['prenom'])."'";
          $sql .= ", '".mysqli_real_escape_string($DB_site2->link_id,$params['raisonsociale'])."'";
          $sql .= ", '".$params['mail']."'";
          $sql .= ", '".$params['telephone']."'";
          $sql .= ", '".$params['montant']."'";
          $sql .= ", '".mysqli_real_escape_string($DB_site2->link_id,$params['commentaire'])."'";
          $sql .= ", '".intval($params['moyen'])."'";
          $sql .= ", '".$params['date']."'";
          $sql .= ", '".$params['delais']."'";
          $sql .= ", '".$params['nocmd']."'";
          $sql .= ", '".$fcl->facturid."'";
          $sql .= ")";
          $DB_site2->query($sql);
          $reglement_id = $DB_site2->insert_id();
          
          $sql = "INSERT INTO reglement_historique (";
          $sql .= " reglement_reception_id";
          $sql .= ", reglement_id";
          $sql .= ", reglement_reception_date";
          $sql .= ", reglement_historique_date";
          $sql .= ", reglement_historique_username";
          $sql .= ") VALUES (";
          $sql .= "3";
          $sql .= ", ".$reglement_id;
          $sql .= ", NOW()";
          $sql .= ", NOW()";
          $sql .= ", '".$_COOKIE['admin_username']."'";
          $sql .= ")";
          $DB_site2->query($sql);
          
          $changements = "Nouveau règlement :\r\n";
          
     } else { // Mise à jour d'un règlement existant

          $params['reglement_id'] = $_POST['reglement_id'];
          //$params['statut']       = 0; // Statut "En attente" par défaut

          $sql = "UPDATE reglement SET";
          $sql .= " reglement_transaction_num = '".$params['reglement_num_transaction']."'";
          if(isset($params['statut'])) $sql .= ", reglement_statut_id      = ".$params['statut'];
          $sql .= ", reglement_date           = '".$params['date']."'";
          $sql .= ", reglement_moyen          = '".intval($params['moyen'])."'";
          $sql .= ", reglement_delais_id      = '".intval($params['delais'])."'";
          $sql .= ", reglement_montant        = '".$params['montant']."'";
          $sql .= ", reglement_commentaires   = '".mysqli_real_escape_string($DB_site2->link_id,$params['commentaire'])."'";
          $sql .= " WHERE reglement_id        = ".$params['reglement_id'];
          $DB_site2->query($sql);
          
          $changements = "Changement(s) règlement n°".$_POST['reglement_id']." :\r\n";
     }
     
     $changements .= ""
     . "Montant : ".$params['montant']."\r\n"
     . "Moyen : ".$fcl->getMoyen($params['moyen'])."\r\n"
     . "Modalités : ".$fcl->getModalite($params['delais'])."\r\n"
     . "Commentaire : ".$params['reglement_commentaire']."\r\n"
     . "Date : ".$params['date'];
     
     $fcl->addSuivi(array('suivi' => $changements));
}

// SUPPRESSION PAIEMENT
if(isset($_POST['rm_reglement'])){
     
     $sql = "SELECT *";
     $sql .= " FROM utilisateur";
     $sql .= " WHERE userid = ".$fcl->userid;
     $tab_user = $DB_site2->query_first($sql);
     
     $sql = "DELETE FROM reglement";
     $sql .= " WHERE facturid = ".intval($fcl->facturid);
     if(!is_null($_POST['rm_reglement'])) $sql.=" AND reglement_id = ".intval($_POST['rm_reglement']);
     $reponse = $DB_site2->query($sql);

     if(is_null($_POST['rm_reglement'])){
          $sql = "SELECT reglement_id";
          $sql .= " FROM reglement";
          $sql .= " WHERE facturid = ".intval($fcl->facturid);
          $req = $DB_site2->query($sql);
          while($row = $DB_site2->fetch_array($req)){
               $sql = "DELETE FROM reglement_historique";
               $sql .= " WHERE reglement_id = ".intval($row['reglement_id']);
               $DB_site2->query($sql);
          }
     } else {
          $sql = "DELETE FROM reglement_historique";
          $sql .= " WHERE reglement_id = ".intval($_POST['rm_reglement']);

          $DB_site2->query($sql);
     }
     
     $fcl->addSuivi(array('suivi' => 'Suppression du règlement n°'.$_POST['rm_reglement']));
}

// VALIDATION PAIEMENT
if(isset($_POST['valid_reglement_id'])){
     
     $sql = "SELECT *";
     $sql .= " FROM utilisateur";
     $sql .= " WHERE userid = ".$fcl->userid;
     $tab_user = $DB_site2->query_first($sql);
     
     //Récupération des informations concernant le règlement
     $sql = "SELECT *";
     $sql .= " FROM reglement";
     $sql .= " WHERE reglement_id = ".$_POST['valid_reglement_id'];
     $reglement = $DB_site2->query_first($sql);
     
     //Paramétrage puis mise à jour du statut du paiement en "valide"
     $params = array(
          "reglement_id" => $reglement['reglement_id'],
          "statut"       => 1,
          "date"         => $reglement['reglement_date'],
          "moyen"        => $reglement['reglement_moyen'],
          "delais"       => $reglement['reglement_delais_id'],
          "montant"      => $reglement['reglement_montant'],
          "commentaire"  => $reglement['reglement_commentaires']
     );
     
     $sql = "UPDATE reglement SET";
     $sql .= " reglement_transaction_num = '".$params['reglement_num_transaction']."'";
     if(isset($params['statut'])) $sql .= ", reglement_statut_id      = ".$params['statut'];
     $sql .= ", reglement_date           = '".$params['date']."'";
     $sql .= ", reglement_moyen          = '".intval($params['moyen'])."'";
     $sql .= ", reglement_delais_id      = '".intval($params['delais'])."'";
     $sql .= ", reglement_montant        = '".$params['montant']."'";
     $sql .= ", reglement_commentaires   = '".mysqli_real_escape_string($DB_site2->link_id,$params['commentaire'])."'";
     $sql .= " WHERE reglement_id        = ".$params['reglement_id'];
     $DB_site2->query($sql);
     
     $fcl->addSuivi(array('suivi' => 'Validation du règlement n°'.$_POST['valid_reglement_id']));
     
     //Envoi de l'email si option activée
     if($_POST['valid_reglement_email'] == 1){
          
          $sql = "SELECT libelleFR AS libelle";
          $sql .= " FROM moyenpaiement";
          $sql .= " WHERE moyenid = ".$reglement['reglement_moyen'];
          $moyenpaiement = $DB_site2->query_first($sql);

          
          $sql = "SELECT COUNT(*) as nb";
          $sql .= " FROM reglement";
          $sql .= " WHERE facturid = ".$reglement['facturid'];
          $tabNbReg = $DB_site2->query_first($sql);

          $templ = new template($fcl->utilisateur->langue,$DB_site2);
          $templ->changesite($fcl->site_code,$fcl->utilisateur->langue);
          
          $vars=array(
          "facturid"=>$fcl->facturid,
          "partiel"=>($tabNbReg['nb'] > 1 ? 'partiel' : ''),
          "reglement_id"=>$reglement['reglement_id'],
          "montant"=>$templ->price_format($reglement['reglement_montant']),
          "date"=>date("d/m/Y"),
          "moyen"=>$moyenpaiement['libelle'],
          "nom_utilisateur"=>$fcl->utilisateur->prenom." ".$fcl->utilisateur->nom);
          
          $templ->pro=$sitepro = ($fcl->utilisateur->raisonsociale!="" && $template->site_code()=="base");

          $ec = new email_client($DB_site2,$templ,283,null,$vars);
          
          $mail = new email();

          $mail->expediteur   = $templ->translate("/site/mail_expediteur");
          $mail->expname      = $templ->translate("/site/nom");
          $mail->destinataire = $tab_user['mail'];
          $mail->to           = MAIL_CONTACT;
          $mail->subject      = $ec->subject;
          $mail->body         = $ec->body;
          $mail->sendmail();

          //Insertion de l'email dans le suivi
          $sql = "INSERT INTO mail_outbox SET";
          $sql .= " facturid     = ".$fcl->facturid."";
          $sql .= ", userid      = ".$fcl->userid."";
          $sql .= ", date        = ".time();
          $sql .= ", `to`        = '".$mail->destinataire."'";
          $sql .= ",`from`       = '".$mail->expediteur."'";
          $sql .= ", utilisateur = '".$user_info['username']."'";
          $sql .= ", subject     = '".mysqli_real_escape_string($DB_site2->link_id,$mail->subject)."'";
          $sql .= ", body        = '".mysqli_real_escape_string($DB_site2->link_id,$mail->body)."'";
          $sql .= ", uniqueid    = '".$mail->uniqueid."'";
          $sql .= ", compta      ='oui'";
          $DB_site2->query($sql);
     }
}

// Récupération de la liste des règlements associés à la commande, triés par date
$sql = "SELECT *";
$sql .= " FROM reglement r";
$sql .= " LEFT JOIN reglement_delais rd USING (reglement_delais_id)";
$sql .= " WHERE r.facturid = ".$fcl->facturid;
$sql .= " ORDER BY r.reglement_date";
$reglements = $DB_site2->query($sql);

$total_reglements = 0;
if($rs_facture['moyenid'] == 99){
    
     $moyensfacilites = array(1,2,4,5,6,7,15);
    
     //Récupération de la liste des moyens de paiement disponibles sur le site
     $sql = "SELECT moyenid, libelleFR AS libelle";
     $sql .= " FROM moyenpaiement";
     $sql .= " WHERE moyenid IN (".implode(",",$moyensfacilites).")";
     $sql .= " ORDER BY libelleFR";
     $reqmoyens = $DB_site2->query($sql);
     $optmoyens = "";
     while($moyen = $DB_site2->fetch_array($reqmoyens)){
          
          $optmoyens .= "<option value=\"".$moyen['moyenid']."\">".$moyen['libelle']."</option>";
     }
     
     //Récupération de la liste des modalités de paiement
     $sql = "SELECT *";
     $sql .= " FROM reglement_delais";
     $sql .= " ORDER BY reglement_delais_id";
     $reqdelais = $DB_site2->query($sql);
     $optdelais = "";
     while($delais = $DB_site2->fetch_array($reqdelais)){
          
          $optdelais .= "<option value=\"".$delais['reglement_delais_id']."\">".$delais['reglement_delais_libelle']."</option>";
     }
     
     //Récupération de la liste des étape de réception
     $sql = "SELECT *";
     $sql .= " FROM  reglement_reception";
     $sql .= " reglement_reception_id";
     $reqreception = $DB_site2->query($sql);
     $optreception = "";
     while($reception = $DB_site2->fetch_array($reqreception)){
          
          $optreception .= "<option value=\"".$reception['reglement_reception_id']."\">".$reception['reglement_reception_libelle']."</option>";
     }
     
     $tbl = ""
     . "<tr>"
          . "<td align=\"center\" class=\"entete\">Paiement</td>"
          . "<td align=\"center\" class=\"entete\">Echéance</td>"
          . "<td align=\"center\" class=\"entete\">Modalités</td>"
          . "<td align=\"center\" class=\"entete\">Montant<br>TTC</td>"
          . "<td align=\"center\" class=\"entete\">Moyen<br>de paiement</td>"
          . "<td align=\"center\" class=\"entete\">Règlement</td>"
          . "<td align=\"center\" class=\"entete\">Statut</td>"
          . "<td align=\"center\" class=\"entete\">Commentaire(s)</td>"
          . "<td align=\"center\" class=\"entete\"></td>"
     . "</tr>";
     
     //Affichage du tableau des règlements
     $i = 0;
     $toutpaye = true;
     while($reglement = $DB_site2->fetch_array($reglements)){
          
          $i++;
          $r_date = date_create($reglement['reglement_date']);
          $color_date = "";
          
          // Si paiement en retard ou statut de paiement non défini, on affiche la date en rouge
          if($r_date->gettimestamp() < time() && $reglement['reglement_statut_id'] == 0){
               $color_date = "red";
          }
          
          // Récupération de l'intitulé du moyen de paiement
          $sql = "SELECT libelleFR AS libelle";
          $sql .= " FROM moyenpaiement";
          $sql .= " WHERE moyenid = ".$reglement['reglement_moyen'];
          $moyen = $DB_site2->query_first($sql);
          
          $total_reglements += $reglement['reglement_montant'];
          if($reglement['reglement_statut_id'] == 0) $toutpaye = false;
          
          // Mise en place du lien pour paiement par CB
          $lien_paiement = "https://".$_SERVER['HTTP_HOST']."/payer-en-ligne.php?facturid=".$rs_facture['facturid']."&reglement_id=".$reglement['reglement_id'];
          
          //Affichage à mettre en place selon type de paiement
          switch($reglement['reglement_moyen']){
               
               case 1: // Carte bancaire
               case 6: // Paypal
	    
                    if($reglement['reglement_statut_id'] == 0){ // Si paiement NON REGLE
                         
                         if($reglement['reglement_moyen']==1) $payimg = "<img src=\"../configurations/www.mon-espace-cuisson.com/images/templates/cb.jpg\">";
                         if($reglement['reglement_moyen']==6) $payimg = "<img src=\"images/PayPal.jpg\">";

                         $etat    = "Non réglé";
                         $bgcolor_etat = '#CC0000';
                         
                         $action_etat = ""
                         . "<a href=\"".$lien_paiement."\" target=\"_blank\" title=\"Régler\" class=\"reglement_link\">".$payimg."</a>"
                         . "<img src=\"images/icon/button_ok22.png\" class=\"icone_reg\" title=\"Valider le règlement\" onclick=\"valid_reglement(".$reglement['reglement_id'].",'".$moyen['libelle']."','".$reglement['reglement_montant']."¤','".preg_replace('/\n|\r/',' ',addslashes($reglement['reglement_commentaires']))."')\">";
                         $action_statut = ""
                         . "<img src=\"images/icon/edit.png\" class=\"icone_reg\" title=\"Editer le statut de réception du paiement\" onclick=\"edit_reception_paiement(".$reglement['reglement_id'].");\">"
                         . "<img src=\"images/icon/viewmag.png\" class=\"icone_reg\" title=\"Voir l'historique\" onmouseover=\"toggle_historique('historique_".$reglement['reglement_id']."');\" onmouseout=\"toggle_historique('historique_".$reglement['reglement_id']."');\">";
                         $delete = ""
                         . "<img src=\"images/icon/edit.png\" class=\"icone_reg\" title=\"Editer le règlement\" onclick=\"edit_reglement(".$reglement['reglement_id'].",".$reglement['reglement_montant'].",".$reglement['reglement_moyen'].",".$reglement['reglement_delais_id'].",'".$r_date->format("d/m/Y")."','".preg_replace('/\n|\r/',' ',addslashes($reglement['reglement_commentaires']))."')\">"
                         . "<img src=\"images/icon/delete.png\" class=\"icone_reg_bg\" title=\"Supprimer le règlement\" onclick=\"rm_reglement(".$reglement['reglement_id'].")\">";
                         
                    } else { // Si paiement EFFECTUE
                         
                         $etat    = 'Réglé';
                         $bgcolor_etat = '#028202';
                         $action_etat = '';
                         $action_statut = ""
                         . "<img src=\"images/icon/edit.png\" class=\"icone_reg\" title=\"Editer le statut de réception du paiement\" onclick=\"edit_reception_paiement(".$reglement['reglement_id'].");\">"
                         . "<img src=\"images/icon/viewmag.png\" class=\"icone_reg\" title=\"Voir l'historique\" onmouseover=\"toggle_historique('historique_".$reglement['reglement_id']."');\" onmouseout=\"toggle_historique('historique_".$reglement['reglement_id']."');\">";
                         $delete  = "<img src=\"images/icon/edit.png\" class=\"icone_reg\" title=\"Editer le règlement\" onclick=\"edit_reglement(".$reglement['reglement_id'].",".$reglement['reglement_montant'].",".$reglement['reglement_moyen'].",".$reglement['reglement_delais_id'].",'".$r_date->format("d/m/Y")."','".preg_replace('/\n|\r/',' ',addslashes($reglement['reglement_commentaires']))."')\">";
                    }
                     
                    break;
                    
               case 2: // Chèque
               case 4: // Mandat postal
               case 5: // Virement
               case 7: // Espèces
               case 15: // Financement
               case 19: // 6 fois sans frais pro (LOCAM)
                    
                    if($reglement['reglement_statut_id'] == 0){ // Si paiement NON REGLE
                         
                         $etat    = "Non réglé";
                         $bgcolor_etat = '#CC0000';
                         $action_etat  = ""
                         . "<img src=\"images/icon/button_ok22.png\" class=\"icone_reg\" title=\"Valider le règlement\" onclick=\"valid_reglement(".$reglement['reglement_id'].",'".$moyen['libelle']."','".$reglement['reglement_montant']."¤','".preg_replace('/\n|\r/',' ',addslashes($reglement['reglement_commentaires']))."')\">";
                         $action_statut = ""
                         . "<img src=\"images/icon/edit.png\" class=\"icone_reg\" title=\"Editer le statut de réception du paiement\" onclick=\"edit_reception_paiement(".$reglement['reglement_id'].");\">"
                         . "<img src=\"images/icon/viewmag.png\" class=\"icone_reg\" title=\"Voir l'historique\" onmouseover=\"toggle_historique('historique_".$reglement['reglement_id']."');\" onmouseout=\"toggle_historique('historique_".$reglement['reglement_id']."');\">";
                         $delete = ""
                         . "<img src=\"images/icon/edit.png\" class=\"icone_reg\" title=\"Editer le règlement\" onclick=\"edit_reglement(".$reglement['reglement_id'].",".$reglement['reglement_montant'].",".$reglement['reglement_moyen'].",".$reglement['reglement_delais_id'].",'".$r_date->format("d/m/Y")."','".preg_replace('/\n|\r/',' ',addslashes($reglement['reglement_commentaires']))."')\">"
                         . "<img src=\"images/icon/delete.png\" class=\"icone_reg_bg\" title=\"Supprimer le règlement\" onclick=\"rm_reglement(".$reglement['reglement_id'].")\">";
                        
                    } else { // Si paiement EFFECTUE
                         
                         $etat    = 'Réglé';
                         $bgcolor_etat = '#028202';
                         $action_etat = '';
                         $action_statut = ""
                         . "<img src=\"images/icon/edit.png\" class=\"icone_reg\" title=\"Editer le statut de réception du paiement\" onclick=\"edit_reception_paiement(".$reglement['reglement_id'].");\">"
                         . "<img src=\"images/icon/viewmag.png\" class=\"icone_reg\" title=\"Voir l'historique\" onmouseover=\"toggle_historique('historique_".$reglement['reglement_id']."');\" onmouseout=\"toggle_historique('historique_".$reglement['reglement_id']."');\">";
                         $delete = "<img src=\"images/icon/edit.png\" class=\"icone_reg\" title=\"Editer le règlement\" onclick=\"edit_reglement(".$reglement['reglement_id'].",".$reglement['reglement_montant'].",".$reglement['reglement_moyen'].",".$reglement['reglement_delais_id'].",'".$r_date->format("d/m/Y")."','".preg_replace('/\n|\r/',' ',addslashes($reglement['reglement_commentaires']))."')\">";
                    }
                    
                    break;    
          }
          
          $sql = "SELECT *, DATE_FORMAT(rh.reglement_reception_date, '%d/%m/%Y') as date_statut, DATE_FORMAT(rh.reglement_historique_date, '%d/%m/%Y à %Hh%i') as date_historique";
          $sql .= " FROM reglement_historique rh";
          $sql .= " INNER JOIN reglement_reception rr USING (reglement_reception_id)";
          $sql .= " WHERE rh.reglement_id = ".$reglement['reglement_id'];
          $sql .= " ORDER BY rh.reglement_historique_date";
          $req = $DB_site2->query($sql);
          
          $tabHistorique = "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">";
          
          while($row = $DB_site2->fetch_array($req)){
               
               $libelle_statut = $row['reglement_reception_libelle'];
               
               switch($row['reglement_reception_id']){
                    case 1:
                         $bgcolor_statut = '#028202';
                         break;
                    case 2:
                         $bgcolor_statut = '#FF5A08';
                         break;
                    case 3:
                         $bgcolor_statut = '#CC0000';
                         break;

               }
               
               $tabHistorique .= ""
               . "<tr>"
                    . "<td style=\"background-color:".$bgcolor_statut."; color: white;\">".$row['date_statut']."</td>"
                    . "<td style=\"background-color:".$bgcolor_statut."; color: white;\">".$row['reglement_reception_libelle']."</td>"
                    . "<td style=\"background-color:".$bgcolor_statut."; color: white;\">Ajouté le ".$row['date_historique']." par ".ucfirst($row['reglement_historique_username'])."</td>"
               . "</tr>";
          }
          
          $tabHistorique .= "</table>";
                    
          $td_alt = $i%2 == 0 ? 'td_alt2' : 'td_alt1';
          
          $tbl .= ""
          . "<tr>"
               . "<td align=\"center\" class=\"".$td_alt."\"><strong>".$i."<sup>".($i==1?"er":"ème")."</sup></strong></td>"
               . "<td align=\"center\" class=\"".$td_alt."\" style=\"color:$color_date;\">".$r_date->format("d/m/Y")."</td>"
               . "<td align=\"center\" class=\"".$td_alt."\">".$reglement['reglement_delais_libelle']."</td>"
               . "<td align=\"center\" class=\"".$td_alt."\">".number_format($reglement['reglement_montant'],2,',',' ')." &euro;</td>"
               . "<td align=\"center\" class=\"".$td_alt."\">".$moyen['libelle']."</td>"
               . "<td align=\"center\" class=\"".$td_alt." td_color\" style=\"background-color: ".$bgcolor_etat." !important; color: white;\">".$etat."<br>".$action_etat."</td>"
               . "<td align=\"center\" class=\"".$td_alt." td_color\" style=\"background-color: ".$bgcolor_statut." !important; color: white;\">"
                    . $libelle_statut
                    . "<br>"
                    . $action_statut
                    . "<div id=\"historique_".$reglement['reglement_id']."\" style=\"position: absolute; margin-top: -60px; margin-left: 85px; display: none;\">".$tabHistorique."</div>"
               . "</td>"
               . "<td align=\"center\" class=\"".$td_alt."\">".stripslashes($reglement['reglement_commentaires'])."</td>"
               . "<td align=\"center\" class=\"".$td_alt." icons\">".$delete."</td>"
          . "</tr>";
     }
     
     //Vérification de la cohérence entre total des paiements paramétrés et total commande (il faut que ce soit égal)
	 //Conversion en string car autrement dans certains cas la comparaison de nombre float ne fonctionne pas car ils ne sont pas "rééllement" égaux
     if((string)$total_reglements == (string)round($fcl->totalttc,2)){
          
          $totalcolor = "reglement_ok";
          $etat       = "";
          if($toutpaye) $etat="OK";
          $action     = "";
          
     } else {
          
         $totalcolor = "reglement_ko";
         $etat       = "Erreur total";
         $reste      = $fcl->totalttc-$total_reglements;
         $action     = "<a style=\"color: #363E42; text-decoration: none; font-weight: bold; font-size:13px; cursor: pointer;\" onclick=\"add_reglement(".$reste.")\"><img src=\"images/icon/edit_add_22.png\" style=\"vertical-align: -6px;\"> Ajouter un règlement</a>";
     }
    
     $tbl .= ""
     . "<tr>"
          . "<td colspan=\"6\" style=\"font-weight: bold; font-size: 16px; background-color: #CCCCCC;\">Total : <span class=\"".$totalcolor."\">".number_format($total_reglements,2,',',' ')." &euro;</span></td>"
          . "<td class=\"".$totalcolor."\" style=\"background-color: #CCCCCC; text-align: center; font-size:14px; font-weight: bold;\">".$etat."</td>"
          . "<td style=\"background-color: #CCCCCC;\"></td>"
          . "<td style=\"background-color: #CCCCCC; text-align: center;\">".$action."</td>"
     . "</tr>";
     
     echo ""
     . "<div class=\"commande_detail\">"
          . "<h2>Règlements</h2>"
          . "<p style=\"text-align: center; margin: 0; padding: 0; color: #CC0000; font-size:14px; font-weight: bold;\"><img src=\"images/icon/agt_update_critical.png\" style=\"vertical-align:-12px;\"> Réservé au service comptabilité</p>"
          . "<form method=\"post\" id=\"edit_reglement\" onsubmit=\"if(!checkFormulaire()){ return false; }\">"
                . "<img src=\"images/icon/erreur.png\" style=\"float:right; cursor: pointer;\" onclick=\"toggle_historique('edit_reglement');\">"
               . "<input type=\"hidden\" name=\"reglement_id\">"
               . "<table>"
                    . "<tr>"
                         . "<td>Echéance :</td>"
                         . "<td><input type=\"text\" id=\"reglement_date\" name=\"reglement_date\" value=\"\" onclick=\"calendrier_show(this.id,this.value)\" readonly></td>"
                         . "<td>Commentaire :</td>"
                    . "</tr>"
                    . "<tr>"
                         . "<td>Modalité :</td>"
                         . "<td><select id=\"reglement_delais\" name=\"reglement_delais\">".$optdelais."</select></td>"
                         . "<td rowspan=\"3\"><textarea id=\"reglement_commentaire\" name=\"reglement_commentaire\"></textarea></td>"
                    . "</tr>"
                    . "<tr>"
                         . "<td>Montant TTC :</td>"
                         . "<td><input type=\"text\" id=\"reglement_montant\" name=\"reglement_montant\">&euro;</td>"
                    . "</tr>"
                    . "<tr>"
                         . "<td>Moyen :</td>"
                         . "<td><select id=\"reglement_moyen\" name=\"reglement_moyen\">".$optmoyens."</select></td>"
                    . "</tr>"
               . "</table>"
               . "<input type=\"submit\" value=\"Envoyer\">"
          . "</form>"
          . "<form method=\"post\" id=\"edit_reception\" onsubmit=\"if(!checkStatutReglement()){ return false; }\">"
               . "<img src=\"images/icon/erreur.png\" style=\"float:right; cursor: pointer;\" onclick=\"toggle_historique('edit_reception');\">"
               . "<input type=\"hidden\" name=\"reglement_rcpt_id\">"
               . "<table>"
                    . "<tr>"
                         . "<td>Statut paiement :</td>"
                         . "<td><select name=\"reglement_reception\">".$optreception."</select></td>"
                    . "</tr>"
                    . "<tr>"
                         . "<td>Date :</td>"
                         . "<td><input type=\"text\" id=\"reglement_reception_date\" name=\"reglement_reception_date\" value=\"\" onclick=\"calendrier_show(this.id,this.value)\" readonly></td>"
                    . "</tr>"
               . "</table>"
               . "<input type=\"submit\" value=\"Valider\">"
          . "</form>"
          . "<form method=\"post\" name=\"form_rm_reglement\">"
               . "<input type=\"hidden\" name=\"rm_reglement\">"
          . "</form>"
          . "<form method=\"post\" name=\"form_valid_reglement\">"
               . "<input type=\"hidden\" name=\"valid_reglement_email\" value=\"0\">"
               . "<input type=\"hidden\" name=\"valid_reglement_id\" value=\"0\">"
          . "</form>"
          ."<div style=\"padding:0 5px 5px;\">"
               . "<table class=\"table_alternate_color\">"
                    . $tbl
               . "</table>"
          . "</div>"
     . "</div>";
}

?>
<script type="text/javascript">
     
     function checkFormulaire(){
          
          if(document.getElementById('reglement_date').value == '' || document.getElementById('reglement_delais').value == '' || document.getElementById('reglement_montant').value == '' || document.getElementById('reglement_moyen').value == ''){
               alert('Veuillez renseigner tous les champs');
               return false;
          }
          
          return true;
     }

     function checkStatutReglement(){
          if(document.getElementById('reglement_reception_date').value == ''){
               alert('Veuillez saisir une date');

               return false;
          }

          return true;
     }
     
</script>
<div style="clear: both;"></div>
