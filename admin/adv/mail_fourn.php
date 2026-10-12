<?php

function mail_fourn($adv, $preview = false){
     
     global $DB_site2;
     global $rootpath;
     global $user_info;
     global $devises;
     global $host;
     
     $facturid = $adv->facturid;
     $devise   = $adv->devise;
    
     $template = new template("FR",$DB_site2);
     $stock    = new stock($DB_site2);
     
     $subject = "";
     $parent=0;
     
     $userid = $DB_site2->query_first("SELECT userid FROM facture WHERE facturid=".$facturid); 
     
     switch($adv->fields['typecmd']){
          
          case "new":
               
               $subject = "Commande #CDE".$facturid."#";
               $newcmd  = true;
               
               if($adv->fields['lieustock'] > 0){
                    
                    $txtstock = "sortie de stock pour la";
                    $subject  = "Sortie de stock #CDE".$facturid."#";
               }
               
               $text .= "Bonjour,<br>Veuillez trouver ci-joint une ".$txtstock." nouvelle commande #CDE".$facturid."#";
               
               $fin = "Nous vous remercions de votre envoi, par mail ou fax, de votre confirmation de commande.<br>Cordialement<br>".$user_info['nom_contact']." - ACILYON - www.ma-cave-a-vin.fr<br>Tél : + 33.4.26.03.05.44 -  Fax: +33.4.26.03.05.49";
               
               break;

          case "delete":
               
               $subject = "Annulation de la commande #CDE".$facturid."#";
               $newcmd  = false;
               
               $text .="Bonjour,<br>Merci d'annuler les produits suivants de la commande #CDE".$facturid."#";
               
               $fin = "Merci de nous confirmer l'annulation de ces produits par retour de ce mail.<br>Cordialement<br>".$user_info['nom_contact']." - ACILYON - www.ma-cave-a-vin.fr<br>Tél : + 33.4.26.03.05.44 -  Fax: +33.4.26.03.05.49";
        
               break;

          case "modif":
               
               $subject = "Modification de la commande #CDE".$facturid."#";
               $ef      = new envoi_fournisseur($DB_site2,$adv->efid);
               
               $newcmd=true;
               
               $text .= "Bonjour,<br>Cette modification de commande annule et remplace la commande #CDE".$facturid."# envoyée par mail le ".date("d/m/Y à H:i",$ef->date);
               
               $fin = "Merci de nous confirmer cette modification par retour de ce mail.<br>Cordialement<br>".$user_info['nom_contact']." - ACILYON - www.ma-cave-a-vin.fr<br>Tél : + 33.4.26.03.05.44 -  Fax: +33.4.26.03.05.49";
               
               break;

          default:
               
               $newcmd = true;
     }

     //Creation repertoire du fournisseur pour les pièces jointes.
     if(!$preview){
       if($adv->fields['typecmd'] == "delete" || $adv->fields['typecmd']=="modif"){
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
          $DB_site2->query("INSERT INTO user_files SET userid='".$userid[0]."', facturid='".$facturid."', parent=0, utilisateur='".$user_info['username']."', name='".mysqli_real_escape_string($DB_site2->link_id,$adv->destinataire)."', size=0, ext='',state=1, date='".time()."' ");
          $parent = $DB_site2->insert_id();
        }
      }
    }


     // Recuperation des info compte client pour le fournisseur en question 
     $sql = "SELECT fc.compte_client, sf.societe_facturation_nom";
     $sql .= " FROM fournisseur_condition fc";
     $sql .= " LEFT JOIN societe_facturation sf ON (sf.societe_facturation_id = fc.societe_facturation_id)";
     $sql .= " WHERE societe = '".mysqli_real_escape_string($DB_site2->link_id,$adv->destinataire)."'";
     $fournisseur_condition = $DB_site2->query_first($sql);

     if($fournisseur_condition['compte_client'] != ""){
          $text .= "<p><strong>COMPTE CLIENT</strong> : ".$fournisseur_condition['societe_facturation_nom']." - ".$fournisseur_condition['compte_client']."</p>";
     }

     if($adv->fields['infocomplement'] != "") $text .= "<p>".nl2br($adv->fields['infocomplement'])."</p>";

     $imperatif = 0;
     $tsindate  = $tsoutdate=0;
     
     switch($adv->fields['trsptmode']){
          case "parenlevement":

          if($adv->fields['parenlevindate'] != ""){

               $tsindate = $adv->date2ts($adv->fields['parenlevindate']);
          }
          break;

          case "parvossoins":

          if($adv->fields['parvossoinsindate'] != ""){

               $tsindate = $adv->date2ts($adv->fields['parvossoinsindate']);
          }

          if($adv->fields['parvossoinsoutdate']!=""){

               $tsoutdate=$adv->date2ts($adv->fields['parvossoinsoutdate']);
          }

          if($adv->fields['typedatelivrfourn']=="imperative") $imperatif = 1;
          break;

          case "parenlevementdestinataire":
          if($adv->fields['parenlevementdestinatairedate']!="")
              $tsindate=$adv->date2ts($adv->fields['parenlevementdestinatairedate']);
         break;

         case "intervparvossoins":
         if($adv->fields['trsptdateinterv']!="")
              $tsoutdate=$adv->date2ts($adv->fields['trsptdateinterv']);
         break;
     }

     // Ouverture tableau liste des articles commandés
     $text .= "\n\n<p><b>Produits</b><br>\n";
     $text .= "<table border=\"2\" cellpadding=\"5\" cellspacing=\"1\" width=\"100%\">";
     $text .= "\n<thead><tr><td style=\"font-weight:bold\">Qtités:</td><td style=\"font-weight:bold\">Références:</td><td align=\"left\" style=\"font-weight:bold\">&nbsp;Informations:</td><td style=\"font-weight:bold\">&nbsp;Prix Total HT:</td></tr></thead>";
     $text .= "\n<tbody>";
     
     $paht            = 0;
     $ecopart         = 0;
     $montantGarantie = 0;
     $insertids       = array();
     $annultxt;
     
     $sql = "SELECT u.paysid, f.site_code, f.langue, f.nom, f.prenom, f.facturid,f.userid";
     $sql .= " FROM facture f";
     $sql .= " INNER JOIN utilisateur u USING(userid)";
     $sql .= " WHERE facturid = ".$facturid;
     $facture = $DB_site2->query_first($sql);
     
     $compteur = 0;
     $monitoring_paht_fourn = array(); //Monitoring PA HT
     foreach($adv->lignes as $p => $qte){
          
          // pour chaque article
          $textGarantie = "";
          $textLieuStock = "";
          //$text .= "<div id=\"lf_".$p."\">";
          $text .= "\n<tr>";
          //if($compteur > 0) $text .= "<br><br>";
                    
          $sql = "SELECT *";
          $sql .= " FROM lignefacture l, article a, article_marque am, marque m";
          $sql .= " WHERE l.artid = a.artid AND l.artid = am.artid AND am.marqueid = m.marqueid";
          $sql .= " AND lignefactureid = ".$p;
      	  $lignefacture = $DB_site2->query_first($sql);

          // pour l'eco-part d'achat (fournisseur) et non de vente :
          $sql = "SELECT ecopartht FROM article_fournisseur WHERE artid = '".$lignefacture['artid']."'";
          $ecopartfourn = $DB_site2->query_first($sql);
                
      	  $artcode = $adv->fields['ref_'.$p];
	  
          if($adv->fields['lieustock'] > 0) $artcode = $lignefacture['artcode']." (".$adv->fields['ref_'.$p].")";
	  
          //Description de l'article
          
          $text .= "<td align=\"center\" valign=\"center\" width=\"8%\"><span id=\"qte_".$p."\">".$lignefacture['qte']."</span><br></td>";
          $text .= "<td align=\"center\" valign=\"center\" width=\"12%\"><span id=\"artcode_".$p."\">".$lignefacture['libelle']."<br>".$artcode."</span><br></td>";
          $text .= "<td width=\"65%\">".$adv->fields['desc_'.$p]."<br>";
	  
          //Récupération des différentes options
          $sql = "SELECT *";
          $sql .= " FROM lignefacturecaracteristique";
          $sql .= " WHERE lignefactureid = '".$p."'";
          $sql .= " ORDER BY libelle_caract";
          $caracts = $DB_site2->query($sql); 
          
          $total_option = 0;
          $tot_option[$p]=0;
          $option=false;
	        if($DB_site2->num_rows($caracts) > 0){
               
               $textGarantie.= "<u>Options</u> :<br>";
               
               while ($caract = $DB_site2->fetch_array($caracts)){
                    if($adv->fields['caract_'.$p.'['.$caract['caractvalid'].']'] !== ''){
                         if($caract['libelle_caract'] != "Garanties Sérénité"){
                          $tarif_option = $adv->fields['caractval_'.$p][$caract['caractvalid']] > 0 ? number_format($adv->fields['caractval_'.$p][$caract['caractvalid']],2,',',' ')." ".$devises->symbole($devise) : 'inclus';
                          $total_option += $adv->fields['lieustock'] == 0 ? $adv->fields['caractval_'.$p][$caract['caractvalid']] : 0;

                          $textGarantie.= " &nbsp;&nbsp;&nbsp;&nbsp;<span id=\"opt_".$p."_".$caract['caractvalid']."\">- ".$caract['libelle_caract']." : ".$caract['libelle_caractval']." ".$caract['ref']." (".$tarif_option.")</span><br>";
                          $option=true;
                         }
                    }
               }
               if($option==false){
                    $textGarantie="";
               }
          }

          $tot_option[$p] = $total_option;
	  
    	  //On regarde si le client a acheté une garantie
    	  require_once($rootpath."/class/garantie.class.php");
    	  require_once($rootpath.'/class/DAOgarantie.class.php');
    	  require_once($rootpath."/class/DAOlignefacturegarantie.class.php");
    	  require_once($rootpath."/class/DAOfournisseur.class.php");
    	  $cl_garantie = new DAOgarantie();
    	  $tabGarantie = $cl_garantie->getGarantiesByProduitCmd($DB_site2, $lignefacture['lignefactureid']);
    	  // CC //
    	  $resteGaranties=array();
    	  foreach($tabGarantie as $garantie){
          		  $type_client_garantie_id = $garantie['garantie_client_id'];
          	    if($garantie['utilisation_garantie']==0 && $adv->destinataire!='0' && $garantie['fournisseur']!=$adv->destinataire && $adv->fields['lieustock']==0)
          		    $DB_site2->query("DELETE FROM lignefacturegarantie WHERE lignefacturegarantie=".intval($garantie['lignefacturegarantie']));
          	    else
          		    $resteGaranties[]=$garantie['garantieid'];
    	  }
	     
    	  $newgaranties=DAOgarantie::loadAllGarantiesByFournArtPays($DB_site2,$facture['site_code'],$facture['langue'],$lignefacture['artid'],$facture['paysid'],$adv->destinataire,$type_client_garantie_id);
    	  foreach($newgaranties as $newgarantie){
               if(!in_array($newgarantie->garantieid,$resteGaranties)){
                   $codefournisseur=DAOfournisseur::loadCodeFournisseurByName($DB_site2,$newgarantie->fournisseur);
                   DAOlignefacturegarantie::insert($DB_site2,$facture['site_code'],$newgarantie->garantieid,$lignefacture['artid'],$lignefacture['lignefactureid'],"Garantie",$newgarantie->duree,$newgarantie->fournisseur,$codefournisseur,$facture['paysid'],$newgarantie->duree,$newgarantie->type_garantie);
               }
    	  }
	  
    	  $tabGarantie = $cl_garantie->getGarantiesByProduitCmd($DB_site2, $lignefacture['lignefactureid']);
    	  foreach($tabGarantie as $garantie){
          		if($garantie['fournisseur']==$adv->destinataire){
          		    $montantGarantie += $garantie['paht']*$qte;
          		    $textGarantie.="<u>Garantie</u> : ".$garantie['duree']." an(s) ".$garantie['libelle_type'].($garantie['paht'] > 0 ? " (".number_format($garantie['paht'],2,',',' ').$devises->symbole($devise).")" : " (inclus)<br>");
          		}
	      
	      }
        
        if($adv->fields['lieustock'] == 0 || !isset($adv->fields['lieustock'])){
               
              $affiche_total = false;
              $nbprix=count($adv->fields['listeprix_'.$p]);
              $total_prix_article_tabligne=""; 
              
              if($nbprix>0){
              		$textLieuStock.="<span id=\"paht_".$p."\"> Prix offre spéciale : ";
              		$pi=0;
              		krsort($adv->fields['listeprix_'.$p]);
              		foreach($adv->fields['listeprix_'.$p] as $pahtg=>$qteg){
              		      $pi++;
              		      $textLieuStock.=$qteg."x ".($pahtg>0?number_format($pahtg,2,',','').$devises->symbole($devise):"offert").($pi<$nbprix?" + ":"");
              		}
	             }else
		             $textLieuStock .= "<span id=\"paht_".$p."\"> Prix unitaire : ".$qte."x (".number_format($adv->fields['price1_'.$p],2,',',' ')." ".$devises->symbole($devise)."</span>";
                 $total_prix_article = $adv->fields['price1_'.$p]*$qte;
                
                 //Monitoring PA HT
                 $monitoring_paht_fourn[$p] = $adv->fields['price1_'.$p];

                 
                 //Inclusion du montant de l'ecopart
                 //recuperation de l'ecopart par rapport au fournisseur :
                 if($lignefacture['artid'] != 0){
                  $eco_part = $DB_site2->query_first("SELECT ecopartht FROM article_fournisseur WHERE principal = 'base' AND artid=".$lignefacture['artid']);
                 }

                 if($adv->fields['price1_'.$p] != 0 && $eco_part[0] != 0){ //&& $lignefacture['ecopartht'] != 0){
                      $affiche_total = true;
                      $textLieuStock .= " + ".number_format($eco_part[0],3,',',' ')." ".$devises->symbole($devise);
                      $total_prix_article += $eco_part[0]*$qte;
                  }
               
                 //Inclusion du montant des options
                 if($tot_option[$p] > 0){
                      $affiche_total = true;
                      $textLieuStock .= " + ".number_format($tot_option[$p],2,',',' ')." ".$devises->symbole($devise);
                      $total_prix_article += $tot_option[$p]*$qte;
                  }
               
               $textLieuStock.=")";
               if($affiche_total) $textLieuStock .= " soit ".number_format($total_prix_article,3,',',' ')." ".$devises->symbole($devise);
               
               $total_prix_article_tabligne = number_format($total_prix_article,2,',',' ')." ".$devises->symbole($devise);
               
	        } else {
               
                 if($lignefacture['coloris']!="") $textLieuStock.="<br>- Coloris : ".$template->translate($lignefacture['coloris']);
                 if($lignefacture['coloris_porte']!="") $textLieuStock.="<br>- Coloris cadre : ".$template->translate($lignefacture['coloris_porte']);
                 if($lignefacture['version_porte']!="") $textLieuStock.="<br>- Version porte : ".$template->translate($lignefacture['version_porte']);
	  
          }
          
          $text .= "".$textGarantie;
          //$text .= "<td colspan=\"2\">".$textGarantie;
          if ($textGarantie !="") $text .= ""; 
          $text .= $textLieuStock."<br></td>";
          $text .= "<td valign=\"center\" align=\"right\" width=\"15%\">".$total_prix_article_tabligne."</td>";
          $text .= "</tr>";
          
          //$text .="</div>";
                    
      	  $paht    += $lignefacture['qte'] * floatval($adv->fields['price1_'.$p] + $tot_option[$p]);
      	  $ecopart += $lignefacture['qte'] * $ecopartfourn['ecopartht'];
          
          $compteur++;
     }
    
    // fin des informations articles, fermeture du tableau liste commande en fin du if(newcmd)
        
    $textadv="";

    if($newcmd == true){
          
          if($adv->fields['uncolis'] == 1 || $adv->fields['palette'] == 1 || $adv->fields['suremballage'] == 1){
               
               $textadv .= "<p><b>Infos complémentaires produits</b>";
               
               if($adv->fields['uncolis'] == 1){
                    $textadv .= "<br><u>Attention</u> : mettre tous les articles dans 1 seul colis";
                    
               }
               
               if($adv->fields['palette'] == 1){
                    
                    $textadv .= "<br><u>Attention</u> : Article(s) à mettre sur palette + cerclage pour le transport";
               }
               
               if($adv->fields['suremballage'] == 1){
                    
                    $textadv .= "<br><u>Attention</u> :  Prévoir un suremballage du produit + palette + cerclage pour le transport";
               }
               
               $textadv .= "</p>";
          }
          
          if($adv->fields['nopj'] != "0"){
               
               $msg_type1 = "Identification à mettre sur le colis";
               $msg_type2 = "Pièce(s) jointe(s) de ce mail";
               
               $affiche_identification = false;
               $text_temp = '';
               if($adv->fields['blpj'] == 1){
                    $affiche_identification = true;
                    $text_temp .= "<br>- Bon de livraison";
               }
               
               if($adv->fields['facturepj'] == 1){
                    $affiche_identification = true;
                    $text_temp .= "<br>- Facture";
               }
               
               if($adv->fields['glspj'] == 1){
                    $affiche_identification = true;
                    $text_temp .= "<br>- Bon GLS ou FEDEX Express";
               }
               
               if($adv->fields['trsptpja4'] == 1 || $adv->fields['trsptpja5'] == 1){
                    $affiche_identification = true;
                    $text_temp .= "<br>- Note importante pour le transport<br>- Etiquette fragile";
               }
               
               if($affiche_identification){
                    $textadv .= "<p><b>".$msg_type1."</b>";
                    $textadv .= $text_temp;
                    $textadv .= "</p>";
               }
               
               if($adv->fields['otherpj'] == 1){
                    $textadv .= "<p><b>".$msg_type2."</b>";
                    $textadv .= !empty($adv->fields['otherpjname']) ? "<br>- ".$adv->fields['otherpjname'] : "";
                    $textadv .= !empty($adv->fields['otherpjname_bis']) ? "<br>- ".$adv->fields['otherpjname_bis'] : "";
                    $textadv.="</p>";
               }
          }

          $trsptval = 0;
          $livrid   = -1;

          if($adv->fields['trsptmode'] == "parenlevement"){ //Transport : "Enlèvement à notre charge en vos locaux"

               $textadv .= "<p><b>Délai de mise à disposition / livraison</b><br>";
               $textadv .= "Enlèvement à notre charge en vos locaux";
               $textadv .= "<br><u>Transporteur</u> : ".$adv->fields['selecttrspt'];
               if($adv->fields['trspt']){
                    switch ($adv->fields['trspt']) {
                         case 'fournisseur':
                              $textadv .= "<br><u>Adresse</u> : directement chez notre client (voir BL)<br>";

                              $livrid   = 0;
                              break;
                         
                         case 'asls':
                              $sql = "SELECT * FROM fournisseur WHERE id = ".intval($adv->fields['fourn2sls']);
                              $sls = $DB_site2->query_first($sql);

                              $textadv .= "<br><u>Adresse</u> :<br>";
                              $textadv .= $sls['societe']." ".$sls['ville']."<br>";
                              $textadv .= $sls['nom']." ".$sls['prenom']."<br>";
                              $textadv .= $sls['adresse1']."<br>";
                              $textadv .= !empty($sls['adresse2']) ? $sls['adresse2']."<br>" : "";
                              $textadv .= $sls['cp']." ".$sls['ville']."<br>";
                              $textadv .= !empty($sls['horaires']) ? "Horaires : ".$sls['horaires']."<br>" : "";
                              $textadv .= !empty($sls['telephone']) ? "Tél. ".$sls['telephone']."<br>" : "";
                              $textadv .= !empty($sls['portable']) ? "Port. ".$sls['portable']:"";

                              $blfourn  = $adv->fields['fourn2sls'];
                              $livrid   = $adv->fields['fourn2sls'];
                              break;

                         case 'livraisonmazet':
                              $sql = "SELECT *";
                              $sql .= " FROM fournisseur";
                              $sql .= " WHERE id = '".intval($adv->fields['selectmazet'])."'";
                              $lieutrspt = $DB_site2->query_first($sql);

                              $textadv .= "<br><u>Adresse</u> :<br>";
                              $textadv .= $lieutrspt['societe']."<br>";
                              $textadv .= $lieutrspt['adresse1']."<br>";
                              $textadv .= !empty($lieutrspt['adresse2']) ? $lieutrspt['adresse2']."<br>" : "";
                              $textadv .= $lieutrspt['cp']." ".$lieutrspt['ville']."<br>";
                              $textadv .= !empty($lieutrspt['horaires']) ? "Horaires : ".$lieutrspt['horaires']."<br>" : "";

                              $blfourn  = $adv->fields['selectmazet2'];
                              $livrid   = $adv->fields['selectmazet2'];
                              break;

                         case 'stockmcav':
                              $sql = "SELECT *";
                              $sql .= " FROM fournisseur";
                              $sql .= " WHERE id = '".(isset($adv->fields['selectstockmcav']) ? intval($adv->fields['selectstockmcav']) : intval($adv->fields['selectstockmcav2']))."'";
                              $lieustock = $DB_site2->query_first($sql);

                              $textadv .= "<br><u>Adresse</u> :<br>";
                              $textadv .= $lieustock['societe']."<br>";
                              $textadv .= $lieustock['adresse1']."<br>";
                              $textadv .= !empty($lieustock['adresse2']) ? $lieustock['adresse2']."<br>" : "";
                              $textadv .= $lieustock['cp']." ".$lieustock['ville']."<br>";
                              $textadv .= !empty($lieustock['horaires']) ? "Horaires : ".$lieustock['horaires']."<br>" : "";

                              $blfourn  = isset($adv->fields['selectstockmcav']) ? $adv->fields['selectstockmcav'] : $adv->fields['selectstockmcav2'];
                              $livrid   = isset($adv->fields['selectstockmcav']) ? $adv->fields['selectstockmcav'] : $adv->fields['selectstockmcav2'];
                              break;

                         case 'relaiscolis':
                              $textadv .= "<br><u>Adresse (relais colis)</u> :<br><br>";
                              $textadv .= nl2br($adv->fields['relaiscolis_adresse']);

                              $blfourn  = $adv->fields['relaiscolis_adresse2'];
                              $livrid   = $adv->fields['relaiscolis_adresse2'];
                              break;
                    }
               }
                    
                    
               $textadv .= !empty($adv->fields['parenlevindate']) ? "<br><span id=\"indate\"><u>Date</u> : ".$adv->fields['parenlevindate']."</span>" : "";

               if(isset($adv->fields['enlev2sls'])) $blfourn = $adv->fields['enlev2sls'];

               $advtrspt = $adv->fields['selecttrspt'];

          } elseif($adv->fields['trsptmode'] == "parenlevementdestinataire"){ //Transport : "Enlèvement par le destinataire final"

               $textadv .= "<p><b>Délai de mise à disposition / livraison</b><br>";
               $enlevement=$tsindate>0?" le ".date("d/m/Y",$tsindate):"";
               $textadv .= "Enlevement".$enlevement." par le destinataire final : ".$facture['prenom']." ".$facture['nom'];

          } elseif($adv->fields['trsptmode'] == "parvossoins"){ //Transport : "Livraison par vos soins par votre transporteur"

               $textadv .= "<p><b>Délai de mise à disposition / livraison</b><br>";
               $textadv .= "Livraison par vos soins, par votre transporteur<br>";

               $trsptval = floatval($adv->fields['mazetcout']);
               $txtlivr  = "au plus tard, le";

               //if($adv->fields['typedatelivrfourn'] == "max" || $adv->fields['intervtypedatelivrfourn'] == "imperative") $txtlivr = "<u>Impérativement</u> le";
               if($adv->fields['typedatelivrfourn'] == "imperative") $txtlivr = "<u>Impérativement</u> le";

               switch($adv->fields['trspt']){

                    case "fournisseur":

                         $textadv .= "<u>Adresse</u> : directement chez notre client (voir BL)<br>";
                         $textadv .= !empty($adv->fields['parvossoinsindate']) ? "<span id=\"indate\"><u>Date de départ</u> : ".$adv->fields['parvossoinsindate']."</span><br>" : "";
                         $textadv .= !empty($adv->fields['parvossoinsoutdate']) ? "<span id=\"outdate\"><u>Date de livraison</u> : ".$txtlivr." ".$adv->fields['parvossoinsoutdate']."</span>" : "";

                         $advtrspt = "transporteur fournisseur";
                         $livrid   = 0;

                         break;

                    case "asls":

                         $sql = "SELECT * FROM fournisseur WHERE id = ".intval($adv->fields['fournlivrsls']);
                         $sls = $DB_site2->query_first($sql);

                         $textadv .= "<u>Adresse</u> :<br>";
                         $textadv .= $sls['societe']." ".$sls['ville']."<br>";
                         $textadv .= $sls['nom']." ".$sls['prenom']."<br>";
                         $textadv .= $sls['adresse1']."<br>";
                         $textadv .= !empty($sls['adresse2']) ? $sls['adresse2']."<br>" : "";
                         $textadv .= $sls['cp']." ".$sls['ville']."<br>";
                         $textadv .= !empty($sls['horaires']) ? "Horaires : ".$sls['horaires']."<br>" : "";
                         $textadv .= !empty($sls['telephone']) ? "Tél. ".$sls['telephone']."<br>" : "";
                         $textadv .= !empty($sls['portable']) ? "Port. ".$sls['portable']:"";
                         $textadv .= "<br>";
                         $textadv .= !empty($adv->fields['parvossoinsindate']) ? "<span id=\"indate\"><u>Date de départ</u> : ".$adv->fields['parvossoinsindate']."</span><br>" : "";
                         $textadv .= !empty($adv->fields['parvossoinsoutdate']) ? "<span id=\"outdate\"><u>Date de livraison</u> : ".$txtlivr." ".$adv->fields['parvossoinsoutdate']."</span><br>" : "";

                         $blfourn  = $adv->fields['fournlivrsls'];
                         $advtrspt = "transporteur fournisseur";
                         $livrid   = $adv->fields['fournlivrsls'];

                         break;

                    case "livraisonmazet":

                         $sql = "SELECT *";
                         $sql .= " FROM fournisseur";
                         $sql .= " WHERE id = '".intval($adv->fields['selectmazet'])."'";
                         $lieutrspt = $DB_site2->query_first($sql);

                         $textadv .= "<u>Adresse</u> :<br>";
                         $textadv .= $lieutrspt['societe']."<br>";
                         $textadv .= $lieutrspt['adresse1']."<br>";
                         $textadv .= !empty($lieutrspt['adresse2']) ? $lieutrspt['adresse2']."<br>" : "";
                         $textadv .= $lieutrspt['cp']." ".$lieutrspt['ville']."<br>";
                         $textadv .= !empty($lieutrspt['horaires']) ? "Horaires : ".$lieutrspt['horaires']."<br>" : "";
                         $textadv .= "<br>";
                         $textadv .= !empty($adv->fields['parvossoinsindate']) ? "<span id=\"indate\"><u>Date de départ</u> : ".$adv->fields['parvossoinsindate']."</span><br>" : "";
                         $textadv .= !empty($adv->fields['parvossoinsoutdate']) ? "<span id=\"outdate\"><u>Date de livraison</u> : ".$txtlivr." ".$adv->fields['parvossoinsoutdate']."</span>" : "";

                         $blfourn  = $adv->fields['selectmazet'];
                         $advtrspt = "transporteur fournisseur";
                         $livrid   = $adv->fields['selectmazet'];

                         break;

                    case "stockmcav":

                         $sql = "SELECT *";
                         $sql .= " FROM fournisseur";
                         $sql .= " WHERE id = '".(isset($adv->fields['selectstockmcav']) ? intval($adv->fields['selectstockmcav']) : intval($adv->fields['selectstockmcav2']))."'";
                         $lieustock = $DB_site2->query_first($sql);

                         $textadv .= "<u>Adresse</u> :<br>";
                         $textadv .= $lieustock['societe']."<br>";
                         $textadv .= $lieustock['adresse1']."<br>";
                         $textadv .= !empty($lieustock['adresse2']) ? $lieustock['adresse2']."<br>" : "";
                         $textadv .= $lieustock['cp']." ".$lieustock['ville']."<br>";
                         $textadv .= !empty($lieustock['horaires']) ? "Horaires : ".$lieustock['horaires']."<br>" : "";
                         $textadv .= "<br>";
                         $textadv .= !empty($adv->fields['parvossoinsindate']) ? "<span id=\"indate\"><u>Date de départ</u> : ".$adv->fields['parvossoinsindate']."</span><br>" : "";
                         $textadv .= !empty($adv->fields['parvossoinsoutdate']) ? "<span id=\"outdate\"><u>Date de livraison</u> : ".$txtlivr." ".$adv->fields['parvossoinsoutdate']."</span>" : "";

                         $blfourn  = isset($adv->fields['selectstockmcav']) ? $adv->fields['selectstockmcav'] : $adv->fields['selectstockmcav2'];
                         $advtrspt = "transporteur fournisseur";
                         $livrid   = isset($adv->fields['selectstockmcav']) ? $adv->fields['selectstockmcav'] : $adv->fields['selectstockmcav2'];

                         break;
                         
                    case "fourn":
                        $sql = "SELECT *";
                        $sql .= " FROM fournisseur";
                        $sql .= " WHERE id = '".(intval($adv->fields['autrefourn']))."'";
                        $lieu = $DB_site2->query_first($sql);

                        $textadv.= "<u>Adresse</u> :<br>";
                        $textadv .= $lieu['societe']."<br>";
                        $textadv .= $lieu['adresse1']."<br>";
                        $textadv .= !empty($lieu['adresse2']) ? $lieu['adresse2']."<br>" : "";
                        $textadv .= $lieu['cp']." ".$lieu['ville']."<br>";
                        $textadv .= !empty($lieu['horaires']) ? "Horaires : ".$lieu['horaires']."<br>" : "";
                        $textadv .= "<br>";
                        $textadv .= !empty($adv->fields['parvossoinsindate']) ? "<span id=\"indate\"><u>Date de départ</u> : ".$adv->fields['parvossoinsindate']."</span><br>" : "";
                        $textadv .= !empty($adv->fields['parvossoinsoutdate']) ? "<span id=\"outdate\"><u>Date de livraison</u> : ".$txtlivr." ".$adv->fields['parvossoinsoutdate']."</span>" : "";

                        $blfourn = $livrid = $adv->fields['autrefourn'];
                        $advtrspt = "transporteur fournisseur";
                        break;

                    case "relaiscolis":

                         $textadv .= "<u>Adresse (relais colis)</u> :<br><br>";
                         $textadv .= nl2br($adv->fields['relaiscolis_adresse'])."<br><br>";
                         $textadv .= !empty($adv->fields['parvossoinsindate']) ? "<span id=\"indate\"><u>Date de départ</u> : ".$adv->fields['parvossoinsindate']."</span><br>" : "";
                         $textadv .= !empty($adv->fields['parvossoinsoutdate']) ? "<span id=\"outdate\"><u>Date de livraison</u> : ".$txtlivr." ".$adv->fields['parvossoinsoutdate']."</span><br>" : "";

                         $blfourn  = isset($adv->fields['selectstockmcav']) ? $adv->fields['selectstockmcav'] : $adv->fields['selectstockmcav2'];
                         $advtrspt = "transporteur fournisseur";
                         $livrid   = isset($adv->fields['selectstockmcav']) ? $adv->fields['selectstockmcav'] : $adv->fields['selectstockmcav2'];

                         break;

                    case "installsls":

                         $textadv .= " et installation par votre installateur<br><br>";
                         $textadv .= !empty($adv->fields['parvossoinsindate']) ? "<span id=\"indate\"><u>Date de départ</u> : ".$adv->fields['parvossoinsindate']."</span><br>" : "";
                         $textadv .= !empty($adv->fields['parvossoinsoutdate']) ? "<span id=\"outdate\"><u>Date de livraison</u> : ".$txtlivr." ".$adv->fields['parvossoinsoutdate']."</span><br>" : "";

                         $advtrspt="transporteur fournisseur";
                         $livrid=0;

                         break;
               }

          } elseif($adv->fields['trsptmode'] == "intervparvossoins"){ //Transport : "Intervention par vos soins"

               $oFacture = new facture($DB_site2, $adv->facturid);

               switch($oFacture->civilite){
                    case 0:
                         $civilite_livraison = 'Monsieur';
                         break;
                    case 1:
                         $civilite_livraison = 'Madame';
                         break;
                    case 2:
                         $civilite_livraison = 'Mademoiselle';
                         break;
               }

               $textadv .= "<p><b>Délai de mise à disposition / livraison</b><br>";
               $textadv .= "Intervention par vos soins, chez notre client :<br><br>";
               $textadv .= isset($oFacture->raisonsociale) && !empty($oFacture->raisonsociale) ? $oFacture->raisonsociale.'<br>' : '';
               $textadv .= $civilite_livraison.' '.$oFacture->nom.' '.$oFacture->prenom.'<br>';
               $textadv .= $oFacture->adresse.'<br>';
               $textadv .= $oFacture->codepostal.' '.$oFacture->ville.'<br>';
               $textadv .= $oFacture->telephone != 'inconnu' ? 'Tél. : '.$oFacture->telephone.'<br>' : '';
               $textadv .= isset($oFacture->mail) && !empty($oFacture->mail) ? 'Email : '.$oFacture->mail.'<br>' : '';
               $textadv .= isset($adv->fields['trsptdateinterv']) && !empty($adv->fields['trsptdateinterv']) ? '<br>Date d\'intervention souhaitée (au plus tard) : '.$adv->fields['trsptdateinterv'].'<br>' : '';

          }
     
          if($adv->fields['chkinfotrspt']==1) $textadv .= "<br><br><u>Informations pour le transporteur</u> : ".stripslashes($adv->fields['infofournothertxt']);
     
          $textadv.="</p>";

          //Calcul du montant total de la commande
          $total = $paht + $trsptval + $montantGarantie;
          if($adv->fields['lieustock'] == 0){
              
               $textPaHT=""; $valPaHT="";
               $textEcoPart=""; $valEcoPart="";
               $textTranspVal=""; $valTranspVal="";
               $textMntGuarant=""; $valMntGuarant="";
               
               $textPaHT = "&nbsp;(articles & options)";
               $valPaHT = "".number_format($paht,2,',',' ')." ".$devises->symbole($devise);
               
               if($paht != 0 && $ecopart != 0){
                   
                   $textEcoPart = "<br>+ "."(éco-participation)";
                   $valEcoPart = "".number_format($ecopart,2,',',' ')." ".$devises->symbole($devise);
                   $total += $ecopart;
               }
               
               if($trsptval != 0) { 
                  $textTranspVal = "<br>+ (transport)";
                  $valTranspVal = "<span id=\"trsptval\"> ".number_format($trsptval,2,',',' ')." ".$devises->symbole($devise)."</span>";
               }
               if($montantGarantie>0) {
                  $textMntGuarant = "<br>+ "."(garantie)";
                  $valMntGuarant = "".number_format($montantGarantie,2,',',' ')." ".$devises->symbole($devise);
               }
               
               // Ajout des lignes transport et garantie dans le tableau qd elle existent
               if ($textTranspVal!="")
                  $text .= "<tr><td align=\"center\">1</td><td></td><td>"."Transport"."</td><td align=\"right\">".$valTranspVal."</td>";
               if ($textMntGuarant!="")
                  $text .= "<tr><td align=\"center\">1</td><td></td><td>"."Garantie"."</td><td align=\"right\">".$valMntGuarant."</td>";
               
               // Ajout du montant total   
               $text .= "<tr><td colspan=\"3\" valign=\"center\" align=\"right\"><b>MONTANT TOTAL HT&nbsp;</b></td><td align=\"right\"><b>".number_format($total,2,',',' ')." ".$devises->symbole($devise)."</b></td></tr>";
               // Ajout du recapitulatif               
               $text .= "<tr><td colspan=\"3\" valign=\"top\" align=\"right\" style=\"color:#666666\"><u>Récapitulatif:</u>&nbsp;&nbsp;".$textPaHT.$textEcoPart.$textTranspVal.$textMntGuarant."</td>";
               $text .= "<td valign=\"top\" align=\"right\" style=\"color:#666666\">".$valPaHT."<br>".$valEcoPart."<br>".$valTranspVal."<br>".$valMntGuarant."</td></tr>";
               
          }
          
          // fermeture du tableau commande
          $text .="\n</tbody>\n</table></p>\n\n";
          // ajout des informations complementaires 
          $text .= $textadv;
     }
     // Fin if newcmd
    

     //photos articles
     $photos_sup = array_filter($adv->fields, "articlePhotoSup",ARRAY_FILTER_USE_KEY );
     $photos = array_filter($adv->fields, "articlePhoto",ARRAY_FILTER_USE_KEY );

     if(!empty($photos_sup) || !empty($photos)){
          $text.= "<br><br><strong>Merci de nous fournir le produit monté suivant la (ou les) photo(s) ci-jointe(s). </strong><br><br>";
     }

     $text .= $fin;



    if(!$preview){
	    $dest="facture ".$facturid;

    	if($adv->fields['typecmd']=="delete" || $adv->fields['typecmd']=="modif"){
    	    $ef=new envoi_fournisseur($DB_site2,$adv->efid);
    	    $ef->delete();
    	    $adv->efid=0;
    	    $adv->update();

    	    foreach($adv->lignes as $p=>$qte){
    		$lf=$adv->ligne_facture($p);
    		$stockartid=$stock->similaire($lf->artid);
    		$items2=$stock->search($stockartid,null,$dest,2);
    		if(count($items2)>0){
    		    foreach($items2 as $item){
    			$item->dest="";
    			$item->state=1;
    			$item->update();
    		    }
    		}
    		
    		$items2=$stock->search($stockartid,null,$dest,0);
    		if(count($items2)>0){
    		    foreach($items2 as $item){
    			if($item->origin=="fourn") $item->delete();
    		    }
    		}
    	    }
    	}

    	if($newcmd==true){ 
    	    $listefl=array();
    	    $eftype="fourn";
    	    if($adv->fields['lieustock']>0) $eftype="stock";
    	    
    	    foreach($adv->lignes as $p=>$qte){
                $factureligne="";
                if($adv->fields['lieustock']>0) $factureligne="STOCK";

                $efl=new envoi_fournisseur_ligne($DB_site2);
                $efl->indate=$tsindate;
                $efl->outdate=$tsoutdate;
                $efl->expediteur=$user_info['username'];
                $efl->imperatif=$imperatif;
                $efl->type=$eftype;
                if($adv->fields['fournisseur']!="0"){
                    $efl->paht=$adv->fields['price1_'.$p]+$tot_option[$p]+$adv->fields['garval_'.$p];
                    $efl->ecopart=$adv->fields['ecopart_'.$p];
                }
                $efl->devise=$adv->devise;
                $efl->qte=$qte;
                $efl->lignefactureid=$p;
                $efl->facture=$factureligne;
                $listefl[]=$efl;
                $srcdest=null;
                $lf=$adv->ligne_facture($p);

                if($adv->fields['lieustock']>0){
                    $pahttotal=0;
                    $stockartid=$stock->similaire($lf->artid);
                    $items2=$stock->search($stockartid,$adv->fields['lieustock'],$dest,2);
                    foreach($items2 as $item){
                        $pahttotal+=$item->paht;
                    }
                    $qtetotal=$DB_site2->query_first("SELECT SUM(qte) FROM lignefacture WHERE factureid=".$facturid." AND artid=".$lf->artid);
                    
                    if(count($items2)<$qtetotal[0]){
                        $items=$stock->search($stockartid,$adv->fields['lieustock'],$srcdest,1);
                        if(count($items)>=$qte){
                            $i=0;
                            foreach($items as $item){
                                $pahttotal+=$item->paht;
                                $i++;
                                $item->dest=$dest;
                                $item->state=2;
                                $item->update();
                                if($i==$qte) break;
                            }
                        }
                    }
                    $efl->paht=$pahttotal/$qtetotal[0];
                }
                
                if($adv->fields['trsptmode']=="parvossoins" && $adv->fields['trspt']=="stockmcav" && $lf->artid>0){
                    $stockartid=$stock->similaire($lf->artid);
                    
                    for($i=1;$i<=$qte;$i++){
                        $item=new stock_item($DB_site2);
                        $item->artid=$stockartid;
                        $item->paht=0;
                        $item->lieu=isset($adv->fields['selectstockmcav']) ? $adv->fields['selectstockmcav'] : $adv->fields['selectstockmcav2'];
                        $item->origin="fourn";
                        $item->dest="facture ".$facturid;
                        $item->utilisateur=$user_info['username'];
                        $item->date=time();
                        $item->date_enlevement_origin=$tsindate;
                        $item->date_livraison_origin=$tsoutdate;
                        $item->date_enlevement=$tsindate;
                        $item->date_livraison=$tsoutdate;
                        $item->state=0;
                        $item->insert();
                    }
                }
    	    }

    	    $ef=new envoi_fournisseur($DB_site2);
    	    $ef->facturid=$facturid;
    	    $ef->destinataire=$adv->destinataire;
    	    $ef->devise=$adv->devise;
    	    $ef->type=$eftype;
    	    $ef->trspt=$advtrspt;
    	    $ef->lieu_enlev=$adv->fields['lieustock'];
    	    $ef->lieu_livr=$livrid;
    	    $ef->create();
    	    $ef->create_lines($listefl);
    	    $ef->update_tarif($adv->fields['mazetcout']);
    	    $adv->efid=$ef->id;
    	    $adv->update();
    	}

  //Ajout du code tracking
  $text .= "<p style=\"display:none;font-size:1px;line-height:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden;mso-hide:all;\">#CDE$facturid#</p>";

  
  $arrContextOptions=array(
        "ssl"=>array(
            "verify_peer"=>false,
            "verify_peer_name"=>false,
        ),
    );
  
  if(!isset($_SERVER['DOCUMENT_ROOT'])) $_SERVER['DOCUMENT_ROOT']=$rootpath;
	$pj=array();
     $attachements=array();
	$mail=new email();
	$mail->destinataire=$adv->fields['fournisseur'];
	$mail->copymode="bcc";
	$mail->to=array("adv@mon-espace-cuisson.com");
	$mail->expediteur="adv@mon-espace-cuisson.com";
	$mail->expname="Ma Cave à Vin";
	$mail->subject=$subject ;
	$mail->body=$text;
	if($newcmd==true){
    $passe = $DB_site2->query_first("SELECT passefacture FROM facture WHERE facturid=".$facturid);
		
    if($adv->fields['facturepj']==1){
	    $data=file_get_contents(SITE_URL."/facture2.php?facturid=$facturid&nopub=1",FALSE,stream_context_create($arrContextOptions));
	    $mail->attachstring($data,"Facture $facturid.pdf","application/pdf");
	    $pj[]="Facture $facturid.pdf";
         $attachements[]=SITE_URL."/facture2.php?facturid=$facturid&nopub=1";
    }
            
	  if($adv->fields['blpj']==1){
		  $data=file_get_contents(SITE_URL."/facture2.php?facturid=$facturid&cde=1&produits[]=".implode("&produits[]=",array_keys($adv->lignes))."&passe=".$passe[0],FALSE,stream_context_create($arrContextOptions));
		  $size = strlen($data);
		  $mail->attachstring($data,"Bon $facturid.pdf","application/pdf");
		  $blfile = "BL-".$facturid."-".$adv->destinataire."-".date("Ymd-Hi").".pdf";

                $sql = "INSERT INTO user_files SET";
                $sql .= " userid 	   = ".$facture['userid']."";
                $sql .= ", facturid    = ".$facture['facturid']."";
                $sql .= ", date 	   = ".time()."";
                $sql .= ", utilisateur = '".$user_info['username']."'";
                $sql .= ", name 	   = '".mysqli_real_escape_string($DB_site2->link_id,$blfile)."'";
                $sql .= ", size 	   = ".$size;
                $sql .= ", ext 		   = 'pdf'";
                $sql .= ", state 	   = 1";
                $sql .= ", parent    = ".$parent;
                $DB_site2->query($sql);
                $fileid = $DB_site2->insert_id();
                $newname = __DIR__."/../userfiles/".$fileid.".pdf";
                file_put_contents($newname,$data);
           $pj[]="BL-".$facturid."-".$adv->destinataire."-".date("Ymd-Hi").".pdf";
           $attachements[]=SITE_URL."/facture2.php?facturid=$facturid&cde=1&produits[]=".implode("&produits[]=",array_keys($adv->lignes))."&passe=".$passe[0];
	  }
	    
	  if($adv->fields['blslspj']==1 && $blfourn!=""){
		  $data=file_get_contents(SITE_URL."/facture2.php?facturid=$facturid&nopub=1&cde=1&livr=".$blfourn."&produits[]=".implode("&produits[]=",array_keys($adv->lignes)),FALSE,stream_context_create($arrContextOptions));
		  $mail->attachstring($data,"Bon $facturid.pdf","application/pdf");
		  $pj[]="Bon $facturid.pdf";
	 	  $blfile = "BL-".$facturid."-".$adv->destinataire."-".date("Ymd-Hi").".pdf";
		  $size = strlen($data);
		
                $sql = "INSERT INTO user_files SET";
                $sql .= " userid 	   = ".$facture['userid']."";
                $sql .= ", facturid    = ".$facture['facturid']."";
                $sql .= ", date 	   = ".time()."";
                $sql .= ", utilisateur = '".$user_info['username']."'";
                $sql .= ", name 	   = '".mysqli_real_escape_string($DB_site2->link_id,$blfile)."'";
                $sql .= ", size 	   = ".$size;
                $sql .= ", ext 		   = 'pdf'";
                $sql .= ", state 	   = 1";
                $sql .= ", parent    = ".$parent;
                $DB_site2->query($sql);
                $fileid = $DB_site2->insert_id();
                $newname = __DIR__."/../userfiles/".$fileid.".pdf";
                file_put_contents($newname,$data);

          $attachements[]=SITE_URL."/facture2.php?facturid=$facturid&nopub=1&cde=1&livr=".$blfourn."&produits[]=".implode("&produits[]=",array_keys($adv->lignes));
	  }
	    
	    if($adv->fields['glspj']==1){
		$bon=$DB_site2->query_first("SELECT * FROM user_files WHERE id=".intval($adv->fields['bongls']));
		$path=$_SERVER['DOCUMENT_ROOT']."/admin/userfiles/".$bon['id'].".".$bon['ext'];
		if(file_exists($path)){
		  $data=file_get_contents($path);
		  $mail->attachstring($data,"Bon_transport.pdf","application/pdf");
            $pj[]="Bon_transport.pdf";
            $attachements[]=$_SERVER['DOCUMENT_ROOT']."/admin/userfiles/".$bon['id'].".".$bon['ext'];
		}
         }
            
	    if($adv->fields['trsptpja4']==1){
		$data=file_get_contents($_SERVER['DOCUMENT_ROOT']."/admin/pdf/fragile.pdf");
		$mail->attachstring($data,"fragile.pdf","application/pdf");
		$data=file_get_contents($_SERVER['DOCUMENT_ROOT']."/admin/pdf/transportA4.pdf");
		$mail->attachstring($data,"note_importante.pdf","application/pdf");
	    }
	    if($adv->fields['trsptpja5']==1){
		$data=file_get_contents($_SERVER['DOCUMENT_ROOT']."/admin/pdf/transportA5.pdf");
		$mail->attachstring($data,"note_importante.pdf","application/pdf");
	    }
	    if($adv->fields['trsptoptpj']==1){
                $pdf=$_SERVER['DOCUMENT_ROOT']."/admin/pdf/".$adv->fields['trsptetiquette'].".pdf";
                if(file_exists($pdf)){
                    $data=file_get_contents($pdf);
                    $mail->attachstring($data,$adv->fields['trsptetiquette'].".pdf","application/pdf");
                }
	    }

         //Pj photo article
         
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

          if($adv->fields['otherpj']==1){
               if(!empty($_FILES['otherpjfile']['name'])){ 
                    $mail->attachfile($_FILES['otherpjfile']['tmp_name'],$_FILES['otherpjfile']['name'],$_FILES['otherpjfile']['type']);
                    $extension = strtolower(pathinfo($_FILES['otherpjfile']['name'], PATHINFO_EXTENSION));
                    $path = $_SERVER['DOCUMENT_ROOT']."/admin/userfiles/";
                    $filename = $adv->fields['otherpjname']."_".date("d-m-Y").".".$extension;

                    $DB_site2->query("INSERT INTO user_files SET userid=".$userid[0].", facturid=".$facturid.", date=".time().", utilisateur='".$user_info['username']."', name='".mysqli_real_escape_string($DB_site2->link_id,$filename)."', size=".$_FILES['otherpjfile']['size'].", ext='".$extension."'");
                    $idf = $DB_site2->insert_id();

                    if(!copy($_FILES['otherpjfile']['tmp_name'], $path.$idf.".".$extension)){
                         $DB_site2->query("UPDATE user_files SET state=-1 WHERE id=$idf");
                    }else{
                         $DB_site2->query("UPDATE user_files SET state=1 WHERE id=$idf");
                    }

                    $pj[]=$_FILES['otherpjfile']['name'];
                    $attachements[]=$_SERVER['DOCUMENT_ROOT']."/admin/userfiles/".$idf.".".$extension;
               }
               if(!empty($_FILES['otherpjfile_bis']['name'])){
                    $mail->attachfile($_FILES['otherpjfile_bis']['tmp_name'],$_FILES['otherpjfile_bis']['name'],$_FILES['otherpjfile_bis']['type']);
                    $extension = strtolower(pathinfo($_FILES['otherpjfile_bis']['name'], PATHINFO_EXTENSION));
                    $path = $_SERVER['DOCUMENT_ROOT']."/admin/userfiles/";
                    $filename = $adv->fields['otherpjname_bis']."_".date("d-m-Y").".".$extension;

                    $DB_site2->query("INSERT INTO user_files SET userid=".$userid[0].", facturid=".$facturid.", date=".time().", utilisateur='".$user_info['username']."', name='".mysqli_real_escape_string($DB_site2->link_id,$filename)."', size=".$_FILES['otherpjfile_bis']['size'].", ext='".$extension."'");
                    $idf = $DB_site2->insert_id();

                    if(!copy($_FILES['otherpjfile_bis']['tmp_name'], $path.$idf.".".$extension)){
                         $DB_site2->query("UPDATE user_files SET state=-1 WHERE id=$idf");
                    }else{
                         $DB_site2->query("UPDATE user_files SET state=1 WHERE id=$idf");
                    }
               }
            }
          if(isset($adv->fields['pj'])){
               foreach($adv->fields['pj'] as $pjid=>$un){
                    $bon=$DB_site2->query_first("SELECT * FROM user_files WHERE id=".intval($pjid));
                    $year = date("Y",$bon['date']);
                    $path=$_SERVER['DOCUMENT_ROOT']."/admin/userfiles/".$bon['id'].".".$bon['ext'];
                    if(!file_exists($path)) $path = $_SERVER['DOCUMENT_ROOT']."/admin/userfiles/$year/".$bon['id'].".".$bon['ext'];
                    if(file_exists($path)){
                        $data=file_get_contents($path);
                        $pj[]=$bon['name'];
                         $attachements[]=$path;
                        $mail->attachstring($data,$bon['name'],"application/pdf");
                    }
               }
          }
	}

  //Monitoring PA HT
  foreach($monitoring_paht_fourn as $ligne_facture_id => $paht)
  {
      //Recherche du pris d'achat de base
      $sql = "SELECT af.prix, lf.artid, lf.factureid, a.artcode";
      $sql .= " FROM lignefacture lf";
      $sql .= " INNER JOIN article a ON (a.artid = lf.artid)";
      $sql .= " INNER JOIN article_fournisseur af ON (af.artid = lf.artid)";
      $sql .= " WHERE af.principal LIKE '%base%'";
      $sql .= " AND lf.lignefactureid = " . $ligne_facture_id;
      $paht_fourn = $DB_site2->query_first($sql);

      //TODO : si trop d'email reçus, prendre en compte les PA HT temporaires
      
      if($paht != $paht_fourn['prix'])
      {
          $mail2 = new email();
          $mail2->destinataire = "webmaster1@mon-espace-cuisson.com";
          $mail2->expediteur   = "contact@mon-espace-cuisson.com";
          $mail2->expname      = "Ma Cave à Vin";
          $mail2->subject      = "Monitoring prix d\'achat fournisseur HT";
          $mail2->body         = "Différence de prix d\'achat HT sur le produit " . $paht_fourn['artcode'] . " sur la commande #CDE".$paht_fourn['factureid']."# (PA HT transmis : " . $paht . " / PA HT réel : " . $paht_fourn['prix'] . ")";
         $mail2->sendmail();
      }
  }

	if($adv->fields['fournisseur']!="0"){
	    $result=$mail->sendmail();
	    
      $DB_site2->query("INSERT INTO mail_outbox SET date=".time().", utilisateur='".$user_info[username]."', `to`='".$mail->destinataire."', `from` = '".$user_info['mail_contactFR']."', subject='".$mail->subject."', body='".mysqli_real_escape_string($DB_site2->link_id,$mail->body)."', pj='".mysqli_real_escape_string($DB_site2->link_id,implode(',',$pj))."', facturid=".$facturid.", userid='".$userid[0]."', fournisseur='".mysqli_real_escape_string($DB_site2->link_id,$adv->destinataire)."', uniqueid='".$mail->uniqueid."', compta ='non'");

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

       $DB_site2->query("INSERT INTO suivi_commande SET facturid=$facturid, date=".time().", commercial='".$user_info[username]."', suivi='".addslashes("Passage de commande à ".$adv->destinataire."<br>".$mail->body)."'");

	}
	
    }else{
	apercu($adv->destinataire." (".$adv->fields['fournisseur'].")",$subject,$text);
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

