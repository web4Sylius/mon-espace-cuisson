<?php

function mailconfirmation($moyen,$facture,$user_info=null,$template=null){
    global $host;
    global $DB_site;
    global $sitepro;
    $myhost=$host;

    $template=new template("FR",$DB_site);
    $template->changesite($facture->site_code,$facture->utilisateur->langue);
    $valide=array(5,8);
    
    //On vérifie qu'un code analytique est bien associé à au pays de commande du client
    if($facture->provenance == 'mp' && !empty($facture->provenanceid)){
        $site_provenance = $facture->provenanceid;
    } else {
        $site_provenance = 'mec';
    }

    $facture->checkCompteAnalytique($site_provenance);
    
    //Récupération des classes énergétiques des produits
    $send_mail_classe = false;
    foreach($facture->lignes as $lignefactureid => $lignefacture){
        $sql = "SELECT *";
        $sql .= " FROM ".$lignefacture->articletable;
        $sql .= " WHERE artid = '".$lignefacture->artid."'";
        $req = $DB_site->query($sql);
        $classe_energetique = '';
        if($DB_site->num_rows($req) > 0){
            $classe_energetique = $DB_site->fetch_array($req)['class_energetique'];
            if($classe_energetique != ''&& !is_null($classe_energetique) && strpos($classe_energetique, 'A') === false) $send_mail_classe = true;
        } 
    }

    //Si le client a une adresse de livraison En France et un adresse de facturarion dans un autre pays
    if($facture->paysid == 57 && $facture->utilisateur->paysid != 57){
        $body = "Le client <a href=\"".(defined('SITE_URL') ? SITE_URL : 'https://'.$host)."/admin/utilisateur.php?action=editer&userid=".$facture->utilisateur->userid."\">".$facture->utilisateur->prenom." ".$facture->utilisateur->nom."</a>, vient de passer la commande <a href=\"".(defined('SITE_URL') ? SITE_URL : 'https://'.$host)."/admin/facture.php?action=editer&facturid=".$facture->facturid."&lst_trietat=\">#CDE".$facture->facturid."#</a> avec une livraison en France pour une adresse de facturation au grand export : ".$facture->utilisateur->pays." <br><br>Montant HT :".$facture->totalht."<br>Montant TTC :".$facture->totalttc."<br>TVA :".($facture->totalttc - $facture->totalht)."<br><br>";

        //Envoi du mail
        $mail =new email();
        $mail->expediteur   = $template->translate("/site/[site_code]/mail_expediteur");
        $mail->expname      = $template->translate("/site/nom");
        $mail->destinataire = $template->translate("/site/[site_code]/mail_commercial");
        $mail->to           = ["pr@mon-espace-cuisson.com", 'webmaster@ma-cave-a-vin.fr'];
        $mail->subject      = "Attention : Commande livraison France et facturation ".$facture->utilisateur->pays.". ";
        $mail->body         = $body;

        var_dump()

        $mail->sendmail();



        //Insertion dans le suivi
        $sql = "INSERT INTO suivi_commande SET";
        $sql .= " facturid = ".$facture->facturid."";
        $sql .= ", date    = ".time()."";
        $sql .= ", suivi   = '".mysqli_real_escape_string($DB_site->link_id,$body)."'";
        $DB_site->query($sql);
    }

    //Si le client est Suisse et que le produit a une classe inférieure à A, on envoi un email pour avertir de la commande, car des taxes énormes peuvent être appliquées
    if(($facture->paysid == 158 || $facture->utilisateur->paysid == 158) && $send_mail_classe){       
        $body = "Le client <a href=\"".(defined('SITE_URL') ? SITE_URL : 'https://'.$host)."/admin/utilisateur.php?action=editer&userid=".$facture->utilisateur->userid."\">".$facture->utilisateur->prenom." ".$facture->utilisateur->nom."</a>, vient de passer la commande <a href=\"".(defined('SITE_URL') ? SITE_URL : 'https://'.$host)."/admin/facture.php?action=editer&facturid=".$facture->facturid."&lst_trietat=\">#CDE".$facture->facturid."#</a> depuis, ou à destination, de la Suisse.<br><br>Cette dernière requiert une attention particulière car elle comporte au moins 1 produit de classe inférieure à A, ce qui risque d'impliquer des taxes supplémentaires.";

        //Envoi du mail
        $mail =new email();
        $mail->expediteur   = $template->translate("/site/[site_code]/mail_expediteur");
        $mail->expname      = $template->translate("/site/nom");
        $mail->destinataire = $template->translate("/site/[site_code]/mail_commercial");
        $mail->to           = array_merge(explode(",",$template->translate("/site/[site_code]/mail_commercial2")));
        $mail->subject      = "Attention : commande d'un produit de classe inférieure à A, en Suisse";
        $mail->body         = $body;
        $mail->sendmail();


        //Insertion dans le suivi
        $sql = "INSERT INTO suivi_commande SET";
        $sql .= " facturid = ".$facture->facturid."";
        $sql .= ", date    = ".time()."";
        $sql .= ", suivi   = '".mysqli_real_escape_string($DB_site->link_id,$body)."'";
        $DB_site->query($sql);
    }

    //si le client passe une commande avec le code postale 20... et que le pays n'est pas FRANCE CORSE
    $testCP = substr($facture->codepostal, 0,2);
    if($testCP == '20' && $facture->paysid == 57){
        $body = "Le client <a href=\"".(defined('SITE_URL') ? SITE_URL : 'https://'.$host)."/admin/utilisateur.php?action=editer&userid=".$facture->utilisateur->userid."\">".$facture->utilisateur->prenom." ".$facture->utilisateur->nom."</a>, vient de passer la commande <a href=\"".(defined('SITE_URL') ? SITE_URL : 'https://'.$host)."/admin/facture.php?action=editer&facturid=".$facture->facturid."&lst_trietat=\">#CDE".$facture->facturid."#</a> à destination, de la CORSE.<br><br>Cette dernière requiert une attention particulière car le client a indiqué comme pays de livraison France Continentale (hors Corse) et il n'a donc pas payé les frais de port.";

        //Envoi du mail
        $mail =new email();
        $mail->expediteur   = $template->translate("/site/[site_code]/mail_expediteur");
        $mail->expname      = $template->translate("/site/nom");
        $mail->destinataire = $template->translate("/site/[site_code]/mail_commercial");
        $mail->to           = MAIL_CONTACT.",".MAIL_ADV ;
        $mail->subject      = "Attention : commande pour la CORSE avec Frais de ports impayés";
        $mail->body         = $body;
        $mail->sendmail();


        //Insertion dans le suivi
        $sql = "INSERT INTO suivi_commande SET";
        $sql .= " facturid = ".$facture->facturid."";
        $sql .= ", date    = ".time()."";
        $sql .= ", suivi   = '".mysqli_real_escape_string($DB_site->link_id,$body)."'";
        $DB_site->query($sql);
    }

    //Vérification de TVA renseignée & 
    verif_tva($DB_site,$user_info,$template,$facture->facturid,$facture->utilisateur->userid);

    $template->parts["contact_telephone"]=$template->translate("/site/[site_code]/telephone");

    $modepaiement=$DB_site->query_first("SELECT libelle".$facture->utilisateur->langue." FROM moyenpaiement WHERE moyenid=".$moyen);

    $template->parts["liste"]="";

    if($facture->clientcde != '') $template->parts['num_commande'] = "(".$template->translate("/mail/confirmation/num_cde")." ".$facture->clientcde.")";
    
    //$sitepro=($facture->utilisateur->raisonsociale!="" && $template->site_code()=="base");
    $sitepro = false;
    $template->parts["pro"]=($sitepro?"_pro":"");
    $template->parts["border_pro"] = ($sitepro?"#0393c2":"#A79064");
    $template->parts["urlsite"]=str_replace("https://","",$template->translate("/site/url"));
    //if($sitepro) $template->parts["urlsite"]=str_replace("www","pro",$template->parts["urlsite"]);
    $template->parts["autocolor"]=($sitepro?"#0393c2":"#a88d5c");

    //si la cmd pas expedié on affiche dans le cas du renvoi du mail de confirmation, les articles non dissociés
    if(in_array(intval($facture->valide), $valide)) 
        $facturelignes = $facture->lignes;
    else
        $facturelignes = $facture->lignesnondiss;

    foreach($facturelignes as $ligne){
        $caracts=$ligne->caracteristiques25();
        $txtcaract=(count($caracts)>0?"<br>".implode("<br>",$caracts):"");
        $garantie=$ligne->garanties(true);
        $txtgarantie="";
        if(!is_null($garantie)){
            $intitule_destinataire = '';
            if($facture->raisonsociale != ''){
                switch($garantie['garantie_client_id']){
                    case 1:
                    $intitule_destinataire = $template->translate("/article/garantie_type/particulier");
                    break;
                    case 2:
                    $intitule_destinataire = $template->translate("/article/garantie_type/pro");
                    break;
                }
            }else
            $intitule_destinataire = $template->translate("/article/garantie_type/garantie","F");

            if($garantie['type_garantie'] == 6){
                $txtgarantie.="<br>".$intitule_destinataire;
            }else{
                if($garantie['duree']<99)
                    $txtgarantie.="<br>".$intitule_destinataire." ".$garantie['dureeAffiche']." ".$template->translate("/article/garantie_type/an".($garantie['duree']>1?"s":""));
                else
                    $txtgarantie.="<br>".$intitule_destinataire." ".$template->translate("/article/garantie_type/a_vie",'F');
            }

            $txtgarantie.=" ".$template->translate("/article/garantie_type/".$garantie['type_garantie'])." ".$facture->utilisateur->pays;
        }

        if($sitepro) {
            $prixconfirm = $template->price_format($ligne->prixht);
            $prixconfirmtotal = $template->price_format($ligne->totalht);
            $template->parts["libelleprix"] = $template->translate("/prix/ht");
            $template->parts["prixport"]=$template->price_format($facture->portht);
            $prixremise = $facture->cadeauht;
        }
        else {
            $prixconfirm = $template->price_format($ligne->prix);
            $prixconfirmtotal = $template->price_format($ligne->totalttc);
            $template->parts["libelleprix"] = $template->translate("/prix/ttc");
            $template->parts["prixport"]=$template->price_format($facture->portttc);
            $prixremise = $facture->cadeauttc;
        }

        $template->parts["liste"].="
        <tr>
        <td style=\"padding:4px 0;border-bottom:solid 1px black;\">".$ligne->libelle." <b>".$ligne->marque." - ".$ligne->artcode."</b>$txtcaract $txtgarantie</td>
        <td align=\"center\" style=\"border-bottom:solid 1px black;\">".$ligne->qte."</td>
        <td align=\"right\" style=\"border-bottom:solid 1px black;\">".$prixconfirm."</td>
        <td align=\"right\" style=\"border-bottom:solid 1px black;\">".$prixconfirmtotal."</td>
        </tr>";    
}

        if($facture->cadeautype!=""){
            switch($facture->cadeautype){
                case "article":
                case "rayon":
                case "general":
                if($facture->cadeauttc>0)
                    $libcadeau=sprintf($template->translate("/commande/bon_reduction_code","F"),$template->price_format($facture->cadeauttc),$facture->codepromo);
                else
                    $libcadeau=$template->translate("/commande/bon_reduction","F")." ".$facture->codepromo;
                break;

                case "caviste1":
                if($facture->cadeauttc>0)
                    $libcadeau=sprintf($template->translate("/commande/code_remise_partenaire","F"),$facture->codepromo);
                break;

                case "caviste2":
                $libcadeau=sprintf($template->translate("/commande/code_remise_partenaire","F"),$facture->codepromo);
                break;
            }

            $template->parts["liste"].="
            <tr style=\"border-bottom:solid 1px black;\">
            <td colspan=\"3\" style=\"padding:4px 0;\">$libcadeau</td>
            <td align=\"right\">".($facture->cadeauht>0?$template->price_format(-$prixremise):"")."</td>
            </tr>";
        }

        
        $template->parts["totalht"]=$template->price_format($facture->totalht);
        $template->parts["totaltva"]=$template->price_format($facture->totalttc-$facture->totalht);
        $template->parts["totalttc"]=$template->price_format($facture->totalttc);
        if($facture->incoterms==""){
            if($facture->modelivraison == 5)
                $template->parts["modelivraison"] = $template->translate("/commande/mode_livraison_".$facture->modelivraison)." - ".$facture->txtlivr;
            else
                $template->parts["modelivraison"]=$template->translate("/commande/mode_livraison_detail_".$facture->modelivraison);
        }
        else
            $template->parts["modelivraison"]="Incoterms : ".$facture->incoterms;

        $template->evaltemplate("MailconfirmationRecap","recap");

        if($facture->modelivraison==4) {
            $template->parts["articles_montage"]="";
            foreach($facturelignes as $ligne){
                if($ligne->montage_heures) $template->parts["articles_montage"].="<tr style=\"border-bottom:solid 1px black;\"><td>".$ligne->libelle."<br>".$ligne->artcode."</td><td align=\"center\">".$ligne->qte."</td></tr>";
            }
            $template->evaltemplate("MailconfirmationMontage","recap");
        }

        $template->parts["facturid"] = $facture->facturid;
        $template->parts["totalttc"] = $template->price_format($facture->totalttc);
        $template->parts["moyentxt"] = $modepaiement[0];
        $template->parts["nb_mensualites"] = $facture->mensualites;
        $template->parts["teg_annuel"] = $facture->teg_taux;
        $template->parts["no_dossier"] = $facture->no_dossier;

        if($template->langue == "FR") {
            $template->parts["date_commande"] = date('d/m/Y', $facture->datecommande);
        }
        else {
            $template->parts["date_commande"] = date('F j, Y', $facture->datecommande);
        }

        switch($moyen){      
            case 1:
            case 10:
            case 11:
            case 12:
            case 13:
            case 6:
            case 8:
            case 9:
            $template->evaltemplate("Mailconfirmation_cb","msg");
            break;

            case 17:
            case 18:
            $template->evaltemplate("Mailconfirmation_credit","msg");
            break;

            case 2:
            if($sitepro)
                $template->parts["stylevirm"] = "#0393c2";
            else
                $template->parts["stylevirm"] = "#a88d5c";

            $template->evaltemplate("Mailconfirmation_cheque","msg");
            break;

            case 5:
            if($sitepro)
                $template->parts["stylevirm"] = "#0393c2";
            else
                $template->parts["stylevirm"] = "#a88d5c";
            $template->evaltemplate("Mailconfirmation_virement","msg");
            break;

            case 4:
            $template->evaltemplate("Mailconfirmation_mandat","msg");
            break;

            case 14:
            $template->evaltemplate("Mailconfirmation_location","msg");
            break;

            case 20:
            $template->evaltemplate("Mailconfirmation_credoc","msg");
            break;

            case 99:
            $sql = "SELECT r.reglement_id, r.reglement_date, rd.reglement_delais_id, rd.reglement_delais_libelle, r.reglement_montant, m.libelle".$template->langue." AS libelle, r.reglement_moyen";
            $sql .= " FROM reglement r";
            $sql .= " INNER JOIN moyenpaiement m ON (m.moyenid = r.reglement_moyen)";
            $sql .= " INNER JOIN reglement_delais rd ON (rd.reglement_delais_id = r.reglement_delais_id)";
            $sql .= " WHERE r.facturid = ".$facture->facturid;
            $sql .= " ORDER BY r.reglement_id";
            $req = $DB_site->query($sql);
            $chq = false;

            $template->parts['liste_paiements'] = "<ul>";
            $reglement_type = array();
            while($reglement = $DB_site->fetch_array($req)){
                $payid++;

                $template->parts['liste_paiements'] .= "<li>".$template->translate("/commande/facilites/paiement","F")." ".$payid." : ".number_format($reglement['reglement_montant'],2,',','')." &euro; ".sprintf($template->translate("/commande/facilites/par_au"),$reglement['libelle'],date_create($reglement['reglement_date'])->format("d/m/Y"))." - ".$template->translate("/commande/facilites/delais".$reglement['reglement_delais_id'],"F")."</li>";

                $reglement_type[$reglement['reglement_moyen']][$payid]=$reglement;
            }

            $template->parts['liste_paiements'] .= "</ul><ul>";
            if($template->langue == 'FR'){
               $template->parts['paiement_intro'] = "<p>Votre commande a bien été enregistrée sous la référence #CDE".$facture->facturid."#  en date du ".date('d/m/Y', $facture->datecommande).".</p><p>Nous avons convenu de régler les ".$template->price_format($facture->totalttc)." TTC de la facon suivante :</p>";
               $template->parts['paiement_conclusion'] = "<p>Après validation des documents transmis, un délai de livraison sera défini et la commande sera expédiée.</p>";
           }else{
               $template->parts['paiement_intro'] = "<p>Your purchase has been confirmed under the reference #CDE".$facture->facturid."# dated ".date('F j, Y', $facture->datecommande).".</p><p>we agreed to pay ".$template->price_format($facture->totalttc)." TTC as follows :</p>";
               $template->parts['paiement_conclusion'] = "<p>After validation of the transmitted documents, a delivery time will be defined and the order will be shipped.</p>";
           }


           foreach($reglement_type as $moyenid => $reglements){
            switch($moyenid){
          case 1: //Carte bancaire
          $template->parts['liste_paiements'] .= "<li style=\"margin-top:10px;\"><b>".$template->translate("/commande/facilites/paiement_carte")."</b>";
          foreach($reglements as $payid=>$reglement){
              $urlpay="https://".$template->parts["urlsite"]."/payer-en-ligne.php?facturid=".$facture->facturid."&amp;reglement_id=".$reglement['reglement_id'];
              $template->parts['liste_paiements'].="<br>".$template->translate("/commande/facilites/paiement")." ".$payid." : ".number_format($reglement['reglement_montant'],2,',','')." &euro; ".$template->translate("/commande/facilites/par_carte_bancaire")." ".date_create($reglement['reglement_date'])->format("d/m/y")." <a href=\"".$urlpay."\" target=\"_blank\">".$template->translate("/commande/facilites/lien_securise")."</a>";
          }
          $template->parts['liste_paiements'].="</li>";
          break;

          case 2:  // chèque
          $aRecord = array();
          foreach($reglements as $payid=>$reglement){
              if(!in_array($reglement['reglement_delais_id'],$aRecord)){
                $aRecord[] = $reglement['reglement_delais_id'];
                if($reglement['reglement_delais_id'] == 4){
                  $template->parts['liste_paiements'] .= "<li style=\"margin-top:10px;\">".$template->translate("/commande/facilites/paiement_cheque_contre")."</li>";
                  $template->parts['paiement_conclusion'] = "";
              } else {
                  if(!$chq) $template->parts['liste_paiements'] .= "<li style=\"margin-top:10px;\">".$template->translate("/commande/facilites/paiement_cheque")."</li>";
                  $chq = true;
              }
          }
      }
      break;

          case 4 :  // mandat
          $template->parts['liste_paiements'].="<li style=\"margin-top:10px;\">"
          .$template->translate("/commande/facilites/paiement_mandat")
          ."</li>";
          break;

          case 5:  // virement
          if($sitepro)
              $template->parts['liste_paiements'].="<li style=\"margin-top:10px;\">".$template->translate("/commande/facilites/paiement_virement_pro");
          else
              $template->parts['liste_paiements'].="<li style=\"margin-top:10px;\">".$template->translate("/commande/facilites/paiement_virement");
          $template->parts['liste_paiements'].="</li>";
          break;

          case 6: // paypal
          $template->parts['liste_paiements'].="<li style=\"margin-top:10px;\">"
          .$template->translate("/commande/facilites/paiement_paypal");
          foreach($reglements as $payid=>$reglement){
              $urlpay="https://".$template->parts["urlsite"]."/payer-en-ligne.php?facturid=".$facture->facturid."&amp;reglement_id=".$reglement['reglement_id'];
              $template->parts['liste_paiements'].="<br>".$template->translate("/commande/facilites/paiement","F")." ".$payid." : ".number_format($reglement['reglement_montant'],2,',','')." &euro; ".sprintf($template->translate("/commande/facilites/par_au"),$reglement['libelle'],date_create($reglement['reglement_date'])->format("d/m/y"))." <a href=\"".$urlpay."\" target=\"_blank\">".$template->translate("/commande/facilites/lien_securise")."</a>";
          }
          $template->parts['liste_paiements'].="</li>";
          break;

          case 7: // espèce
          $template->parts['liste_paiements'].="<li style=\"margin-top:10px;\">"
          .$template->translate("/commande/facilites/paiement_espece");
          foreach($reglements as $payid=>$reglement){
              $template->parts['liste_paiements'].="<br>".$template->translate("/commande/facilites/paiement","F")." ".$payid." : ".number_format($reglement['reglement_montant'],2,',','')." &euro; ".sprintf($template->translate("/commande/facilites/par_au"),$reglement['libelle'],date_create($reglement['reglement_date'])->format("d/m/y"));
          }
          $template->parts['liste_paiements'].="</li>";
          break;

          case 15: // financement
          $template->parts['liste_paiements'].="<li style=\"margin-top:10px;\">"
          .$template->translate("/commande/facilites/paiement_financement");
          foreach($reglements as $payid=>$reglement){
              $template->parts['liste_paiements'].="<br>".$template->translate("/commande/facilites/paiement","F")." ".$payid." : ".number_format($reglement['reglement_montant'],2,',','')." &euro; ".sprintf($template->translate("/commande/facilites/par_au"),$reglement['libelle'],date_create($reglement['reglement_date'])->format("d/m/y"));
          }
          $template->parts['liste_paiements'].="</li>";
          break;

          case 17: //Sofinco : 4x sans frais ou financement
          case 18: //Sofinco : 10x sans frais ou financement
          $template->parts['liste_paiements'].="<li style=\"margin-top:10px;\">"
          .$template->translate("/commande/facilites/paiement_sofinco");
          foreach($reglements as $payid=>$reglement){
              $template->parts['liste_paiements'].="<br>".$template->translate("/commande/facilites/paiement","F")." ".$payid." : ".number_format($reglement['reglement_montant'],2,',','')." &euro; ".sprintf($template->translate("/commande/facilites/par_au"),$reglement['libelle'],date_create($reglement['reglement_date'])->format("d/m/y"));
          }
          $template->parts['liste_paiements'] .="<br>". $template->translate("/commande/facilites/paiement_sofinco_cl");
          $template->parts['liste_paiements'].="</li>";
          break;

          case 19: 
          $template->parts['paiement_intro'] = "Vous avez choisi de régler les ".$template->price_format($facture->totalttc)." TTC par financement professionnel.";
          $template->parts['liste_paiements'] = '<br><br>'.$template->translate("/commande/facilites/paiement_locam6x");
          $template->parts['paiement_conclusion'] = "<p>Après validation des documents transmis, un délai de livraison sera défini et la commande sera expédiée.</p>";
          break;
      }
  }

  $template->parts['liste_paiements'].="</ul>";
  $template->evaltemplate("Mailconfirmation_facilite","msg");
  break;
}

$template->parts["raisonsociale_utilisateur"]=($facture->utilisateur->raisonsociale!=""?$facture->utilisateur->raisonsociale."<br>":"");
$template->parts["nom_utilisateur"]=$facture->utilisateur->prenom." ".$facture->utilisateur->nom;
$template->parts["adresse_utilisateur"]=$facture->utilisateur->adresse;
$template->parts["ville_utilisateur"]=$facture->utilisateur->codepostal." ".$facture->utilisateur->ville;
$template->parts["pays_utilisateur"]=$facture->utilisateur->pays;
$template->parts["tel_utilisateur"]=$facture->utilisateur->telephone;
$template->parts["raisonsociale"]=($facture->raisonsociale!=""?$facture->raisonsociale."<br>":"");
$template->parts["nom"]=$facture->prenom." ".$facture->nom;
$template->parts["adresse"]=$facture->adresse;
$template->parts["ville"]=$facture->codepostal." ".$facture->ville;
$template->parts["pays"]=$facture->pays;
$template->parts["tel"]=$facture->telephone;
$template->parts["infotranspo"]=$facture->infotranspo;
$template->parts["datelivraison"]=date("d/m/Y",$facture->datelivraison);
$template->parts["subject"]=sprintf($template->translate("/commande/confirmation"),$template->translate("/site/nom"));
if(!$sitepro) $template->parts["retractation"]="
Vous disposez d'un délai de 14 jours, à compter de la date de réception de la commande, pour exercer votre droit de rétractation. Il faudra toutefois nous retourner au préalable le formulaire  de rétractation dûment rempli pour acceptation. Les produits doivent être retournés par vos propres moyens, dans les 14 jours qui suivent cet accord, dans leur emballage d'origine non endommagé, accompagnés de tous les accessoires et notices, afin qu'ils puissent de nouveau être mis en vente. Les frais de retour restent à votre charge. Ce droit de rétractation n’est applicable qu’aux clients particuliers, résidant en France métropolitaine et à Monaco. Pour exercer ce droit <a href=\"https://".$template->parts["urlsite"]."/fichiers/formulaire_retractation.pdf\" style=\"color:black;\">cliquez ici</a>.";

$template->parts['tracking']="<div style=\"display:none;font-size:1px;line-height:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden;mso-hide:all;\">#CDE".$facture->facturid."#</div>";

$template->evaltemplate("Mailconfirmation","htmlmess");

$body=stripslashes($template->parts["htmlmess"]);
$path=pathinfo($_SERVER['SCRIPT_NAME'],PATHINFO_DIRNAME);
if($path!="/"){
    $nbpar=substr_count($path,"/");
    $parentpath=str_repeat("../",$nbpar); 
        // $body=preg_replace('/< *img([^>]*)src *= *["]?([^"]*)"/i','<img$1src="'.$parentpath.'$2"',$body);
    $body=preg_replace('/< *img([^>]*)src *= *["]?([^"]*)"/i','<img$1src="$2"',$body);
}

$mail=new email();
$mail->expediteur=$template->translate("/site/mail_expediteur");
$mail->expname=$template->translate("/site/nom");
$mail->destinataire=$facture->utilisateur->mail;
$mail->destname=$facture->utilisateur->prenom." ".$facture->utilisateur->nom;
$mail->copymode="bcc";
//if(ESPACE == 'prod') {
$mail->to=["pr@mon-espace-cuisson.com", 'webmaster@ma-cave-a-vin.fr'];
//}
$mail->subject=$template->parts["subject"];
$mail->body=$body;

foreach($facture->lignesnondiss as $ligne){
    $conseils=$DB_site->query_first("SELECT COUNT(*) FROM article_nosconseils INNER JOIN article USING(artid) WHERE instructions=1 AND artid=".$ligne->artid);
    if($conseils[0]>0){
        $mail->attachfile("https://".$host."/admin/instructions.php?artid=".$ligne->artid."&langue=".$template->langue,$template->translate("/article/champs/conseils","F")."_".$ligne->artcode.".pdf","application/pdf");
    }
    $nbpj=1;
    $pj = $DB_site->query("SELECT * FROM article_cmddesc WHERE artid='".$ligne->artid."' AND active='1' AND langue='".$template->langue."'");
    while ($article_pj = $DB_site->fetch_array($pj)) {
        if($article_pj['img_ext']!==''){
            $img_pj = explode('.',$article_pj['img_ext']);
            $filename = $rootpath."configurations/".$host."/article_commande/".$article_pj['langue']."/".$article_pj['img_ext'];
            $mail->attachfile($filename,$template->translate("/devis/devis","F")." ".$facture->facturid."_$nbpj.".$img_pj[1],mime_content_type($filename));
        }else{
            $filename = $rootpath."configurations/".$host."/article_commande/".$article_pj['langue']."/".$article_pj['id'].".".$article_pj['ext'];
            $mail->attachfile($filename,$template->translate("/devis/devis","F")." ".$facture->facturid."_$nbpj.".$article_pj['ext'],mime_content_type($filename));
        }
        $nbpj++;
    }
}
$mail->sendmail();


$DB_site->query("INSERT INTO suivi_commande SET facturid=".$facture->facturid.", date=".time().", suivi='envoi du mail de confirmation'");

$sql = "INSERT INTO mail_outbox SET";
$sql .= " date         = ".time()."";
$sql .= ", utilisateur = '".$user_info['username']."'";
$sql .= ", `to`        = '".$mail->destinataire."'";
$sql .= ", `from`        = '".$mail->expediteur."'";
$sql .= ", subject     = '".addslashes($mail->subject)."'";
$sql .= ", body        = '".addslashes($mail->body)."'";
$sql .= ", userid      = '".$facture->utilisateur->userid."'";
$sql .= ", facturid    = '".$facture->facturid."'";
$sql .= ", uniqueid    = '".$mail->uniqueid."'";
$sql .= ", compta      = 'non'";
$sql .= ", auto        = 0 ";
$DB_site->query($sql);

if(isset($template->parts['msg'])) return $template->parts['msg'];
}
?>