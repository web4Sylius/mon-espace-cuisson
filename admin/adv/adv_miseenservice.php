<div id="tabmiseenservice" style="<?php echo $_GET['type'] != "miseenservice" ? "visibility:hidden; position:absolute;" : ""; ?>">
     <h3><img src="images/icon/sav_picto.png" width="25" align="absmiddle"> Mise en service de matériel</h3>
     <form name="form4" action="facture.php?action=editer&facturid=<?php echo $facturid; ?>" method="post" enctype="multipart/form-data">
          <input type="hidden" name="actionadv" value="advfournisseur">
          <input type="hidden" name="fournisseurname">
          
          <div id="stepservice1" class="advgroup">
               <p style="padding:5px; margin:0;">1. Produits</p>
               <table class="table_alternate_color">
                    <tr>
                         <td class="entete"></td>
                         <td class="entete" align="center">Visuel</td>
                         <td class="entete" align="center">Qté.</td>
                         <td class="entete" align="center">Réf.</td>
                         <td class="entete" align="center">Libellé</td>
                    </tr>
                    <?php
                    
                    //Récupération de la liste des articles de la commande
                    $sql = "SELECT lf.*, a.typearticleid";
                    $sql .= " FROM lignefacture lf";
                    $sql .= " INNER JOIN article a USING (artid)";
                    $sql .= " WHERE lf.factureid = '".$facturid."'";
                    $sql .= " AND lf.artid <> 0";
                    $lignefactures = $DB_site2->query($sql);
                    
                    $compteur = 0;
                    $pa_ht_total = 0;
                    $tabMemoId = array();
                    while ($lignefacture = $DB_site2->fetch_array($lignefactures)){
                         
                         //On regarde si une demande d'installation a été passé pour cet article, si non, on ne l'affiche pas
                         $sql = "SELECT lignefactureinstallation_id, lignefactureinstallation_paht";
                         $sql .= " FROM lignefactureinstallation";
                         $sql .= " WHERE factureid = ".$lignefacture['factureid'];
                         $sql .= " AND lignefactureinstallation_typearticles LIKE '%|".$lignefacture['typearticleid']."|%'";
                         $tabInstallation = $DB_site2->query_first($sql);
                         
                         if(!empty($tabInstallation)){
                              
                              $style = "";
                              $img   = "";

                              //Récupération des information sur le produit
                              $sql = "SELECT *";
                              $sql .= " FROM article a, article_marque am, marque m";
                              $sql .= " WHERE am.artid = a.artid";
                              $sql .= " AND am.marqueid = m.marqueid";
                              $sql .= " AND a.artid = ".$lignefacture['artid'];
                              $article = $DB_site2->query_first($sql);
                              
                              //On regarde si la demande a déjà été passé pour ce produit
                              $sql = "SELECT *";
                              $sql .= " FROM envoi_fournisseur_ligne";
                              $sql .= " WHERE type = 'miseenservice'";
                              $sql .= " AND ligne_facture_id = ".$lignefacture['lignefactureid'];
                              $envoi = $DB_site2->query_first($sql);

                              //Si demande déjà effectuée pour ce produit, background vert
                              if($envoi['date']!="") $style = "background-color:green;";

                              //Visuel article
                              $path = "../configurations/images/produits/br/".$lignefacture['artid'].".jpg";
                              if(file_exists($path)){
                                   $img = "<img src=\"".$path."\" style=\"width:30px;\" onmouseover=\"this.style.position='absolute';this.style.width='200px';\" onmouseout=\"this.style.position='relative';this.style.width='30px';\">";
                              } else {
                                   $img = "<img src=\"../images/templates/photo_non_dispo.jpg\" style=\"width:30px;\">";
                              }

                              //Paramétrage checkbox
                              $checkbox = "<input type=\"checkbox\" style=\"vertical-align: 3px;\" name=\"produits3[]\" value=\"".$lignefacture['lignefactureid']."\" onclick=\"showstep('service2'); updligne(".$lignefacture['lignefactureid'].",(this.checked?".$lignefacture['qte'].":0),2)\" ".($adv->lignes[$lignefacture['lignefactureid']] > 0 ? "checked" : "").($adv->locked ? " disabled" : "").">";

                              //Récupération des action ADV menées sur le produit
                              $oadv = $adv->listadv($facturid,"miseenservice",$lignefacture['lignefactureid']);

                              //Si des actions ont été effectuées mais non finalisées, fond orange, et produit sélectionné par défaut
                              if(count($oadv)>0 && $oadv[0]->id != $adv->id){

                                   $checkbox = "";
                                   $style    = "background-color:#FFAA22;";
                              }
                              
                              //On ne compte pas le prix d'achat plusieurs fois, si jamais c'est un forfait
                              if(!in_array($tabInstallation['lignefactureinstallation_id'], $tabMemoId)){
                                   $tabMemoId[] = $tabInstallation['lignefactureinstallation_id'];
                                   
                                   //Calcul du prix d'achat total
                                   $pa_ht_total += $tabInstallation['lignefactureinstallation_paht'];
                              }
                              
                              $td_alt = $compteur%2 == 0 ? 'td_alt2' : 'td_alt3';

                              ?>
                              <tr>
                                   <td class="<?php echo $td_alt; ?> noborder" align="center" style="<?php echo $style; ?>">
                                        <input type="hidden" name="qte3_<?php echo $lignefacture['lignefactureid']; ?>" value="<?php echo $lignefacture['qte']; ?>"><input type="hidden" name="price3_<?php echo $lignefacture['lignefactureid']; ?>" value="<?php echo $article['paht']; ?>">
                                        <input type="hidden" name="ecopart3_<?php echo $lignefacture['lignefactureid']; ?>" value="<?php echo $article['ecopartht']; ?>">
                                        <?php echo $checkbox; ?>
                                   </td>
                                   <td class="<?php echo $td_alt; ?> noborder" align="center"><?php echo $img; ?></td>
                                   <td class="<?php echo $td_alt; ?> noborder" align="center"><?php echo $lignefacture['qte']; ?></td>
                                   <td class="<?php echo $td_alt; ?> noborder" align="center"><?php echo $lignefacture['artcode']; ?><br><?php echo $article['artcode_eurocave']; ?></td>
                                   <td class="<?php echo $td_alt; ?> noborder"><?php echo $article['libelleFR']; ?></td>
                              </tr>
                              <?php
                              $compteur++;
                         }
                    }
                    
                    if($compteur == 0){
                    ?>
                         <tr>
                              <td colspan="5" align="center"><strong>Aucun produit à commander</strong></td>
                         </tr>
                    <?php
                    }
                    
                    ?>
               </table>
          </div>
          <div id="stepservice2" class="advgroup" <?php echo (isset($adv->steps[2]) || $compteur == 0 ? "style=\"display:block;\"" : ""); ?>>
               <p style="padding:5px; margin:0;">2. Agence d'installation et de mise en service</p>
               <input type="hidden" name="livrservicename" value="0">
               <table class="table_alternate_color" <?php echo ($adv->modif ? "style=\"display:none;\"" : ""); ?>>
                    <tr>
                         <td class="noborder td_alt2">
                              <?php 
                              $name    = "livrservice";
                              $values  = array(
                                  "" => "0"
                              );
                              $actions = array(
                                  "document.getElementsByName('livrservicename')[0].value = '0'",
                                  "showstep('service11')",
                                  "fournisseur_save(0)",
                                  "adv_save('livrservice',11)"
                              );
                              echo $adv->radio_inputs($name, $values, implode(';', $actions)); 
                              ?> 
                              Pas de mise en service
                         </td>
                         <td class="noborder td_alt1">
                              <?php 
                              $name    = "livrservice";
                              $values  = array(
                                  "" => defined('MAIL_ADMIN') ? MAIL_ADMIN : 'webmaster1@mon-espace-cuisson.com'
                              );
                              $actions = array(
                                  "document.getElementsByName('livrservicename')[0].value = 'test'",
                                  "showstep('service3')",
                                  "fournisseur_save('test')",
                                  "adv_save('livrservice',3)"
                              );
                              echo $adv->radio_inputs($name, $values, implode(';', $actions)); ?> Mise en service de test</td>
                    </tr>
                    <tr>
                    <?php

                    $fourn = array();
                    
                    $sql = "SELECT *";
                    $sql .= " FROM fournisseur";
                    $sql .= " WHERE `type` = 'serviceadv'";
                    $sql .= " ORDER BY societe";
                    $list = $DB_site2->query($sql);
                    
                    while($advf = $DB_site2->fetch_array($list)){
                         
                         if($advf['groupes']!=""){
                              
                              $groups = explode(",",$advf['groupes']);
                              
                              foreach($groups as $group){
                                   $fourn[$group][]=$advf['mail'];
                              }
                                   
                         } else {
                              
                              $fourn[$advf['societe']][]=$advf['mail'];
                         }
                    }

                    foreach($fourn as $group => $mails){
                         
                         $td_alt = $i%2 == 0 ? 'td_alt2' : 'td_alt1';
                         
                         $i++;
                         $c++;
                         
                         $mails   = array_unique($mails);
                         $mailcsv = implode(";",$mails);
                         
                         ?>
                         <td class="<?php echo $td_alt; ?> noborder" title="<?php echo $mailcsv; ?>" <?php echo (strpos($group, $dep) > -1 ? "style=\"color:red;\"" : ""); ?>>
                              <?php 
                              $name   = "livrservice";
                              $values = array(
                                   "" => $mailcsv
                              );
                              $actions = array(
                                   "document.getElementsByName('livrservicename')[0].value = '".$group."'",
                                   "showstep('service3')",
                                   "fournisseur_save('".$group."')",
                                   "adv_save('livrservice',3)"
                              );
                              
                              echo $adv->radio($name, $values, implode(';', $actions)); ?> <?php echo ucfirst($group); 
                              ?>
                         </td>
                         <?php
                         
                         if($c == 2){
                              
                              $c = 0;
                              print "</tr><tr>";
                         }
                    }
                    
                    ?>
                    </tr>
               </table>
               <?php

               if($adv->modif) echo $adv->destinataire." (".$adv->fields['livrservice'].")";
               
          ?>
          </div>
          <div id="stepservice3" class="advgroup" <?php echo (isset($adv->steps[3]) ? "style=\"display:block;\"" : ""); ?>>
               <p style="padding:5px; margin:0;">3. Pièces jointes</p>
               <table class="table_alternate_color" <?php echo ($adv->modif ? "style=\"display:none;\"" : ""); ?>>
                    <tr>
                         <td class="td_alt1">
                              <?php 
                              $name = "servicepj";
                              $actions = array(
                                   "showstep('service4')",
                                   "adv_save(%name,4)"
                              );
                              $value = 0;
                              echo $adv->checkbox($name, implode(';', $actions), $value); 
                              ?> Aucune
                         </td>
                    </tr>
                    <tr>
                         <td class="td_alt2">
                              <?php 
                              $name = "noticeservicepj";
                              $actions = array(
                                   "showstep('service4')",
                                   "adv_save(%name,4)"
                              );
                              $value = 1;
                              echo $adv->checkbox($name, implode(';', $actions), $value); 
                              ?> Notice : <input type="text" name="noticeservicepjname" placeholder="Description de la pièce jointe" onblur="adv_save('noticeservicepjname',4)" size="50" > <input type="file" name="noticeservicepjfile">
                         </td>
                    </tr>
                    <tr>
                         <td class="td_alt1">
                              <?php 
                              $name = "autreservicepj";
                              $actions = array(
                                   "showstep('service4')",
                                   "adv_save(%name,4)"
                              );
                              $value = 1;
                              echo $adv->checkbox($name, implode(';', $actions), $value); 
                              ?> Autre : <input type="text" name="autreservicepjname" placeholder="Description de la pièce jointe" onblur="adv_save('autreservicepjname',3)" size="50" > <input type="file" name="autreservicepjfile">
                         </td>
                    </tr>
               </table>
          </div>
          <?php

          if(!$adv->locked && $adv->fields["serviceobj"] == "annule") $adv->save_field("serviceobj","cmd");
          if($adv->locked) $adv->save_field("serviceobj","annule");
          if($adv->modif) $adv->save_field("serviceobj","modif");

          ?>
          <div id="stepservice4" class="advgroup" <?php echo (isset($adv->steps[4]) ? "style=\"display:block;\"" : ""); ?>>
               <p style="padding:5px; margin:0;">4. Objet du mail</p>
               <table class="table_alternate_color">
                    <tr <?php echo ($adv->modif || $adv->locked ? " style=\"display:none;\"" : ""); ?>>
                         <td class="td_alt1">
                              <?php 
                              $name    = "serviceobj";
                              $values  = array(
                                  "" => "cmd"
                              );
                              $actions = array(
                                  "showstep('service5')",
                                  "adv_save('serviceobj',5)"
                              );
                              echo $adv->radio_inputs($name, $values, implode(';', $actions)); ?> Commande d'installation et de mise en service n°<?php echo $facturid; ?>
                         </td>
                    </tr>
                    <tr <?php echo ($adv->modif || !$adv->locked ? " style=\"display:none;\"" : ""); ?>>
                         <td class="td_alt2">
                              <?php 
                              $name   = "serviceobj";
                              $values = array(
                                  "" => "annule"
                              );
                              $actions = array(
                                   "showstep('service11')",
                                   "adv_save('serviceobj',11)"
                              );
                              echo $adv->radio($name, $values, implode(';', $actions)); ?> Annulation de la commande n°<?php echo $facturid; ?>
                         </td>
                    </tr>
                    <tr <?php echo (!$adv->modif ? " style=\"display:none;\"" : ""); ?>>
                         <td class="td_alt1">
                              <?php 
                              $name   = "serviceobj";
                              $values = array(
                                  "Modifier commande" => "modif"
                              );
                              $actions = array(
                                   "showstep('service5')",
                                   "adv_save('serviceobj',5)"
                              );
                              echo $adv->radio($name, $values, implode(';', $actions)); 
                              ?>
                         </td>
                    </tr>
               </table>
          </div>
          
          <?php
          //Récupération de l'adresse de facturation
          $sql = "SELECT u.*, p.libelle as 'pays_nom'";
          $sql .= " FROM utilisateur u";
          $sql .= " INNER JOIN pays p USING (paysid)";
          $sql .= " WHERE u.userid = ".$rs_facture['userid'];
          $tabUtilisateur = $DB_site2->query_first($sql);
          ?>
                    
          <div id="stepservice5" class="advgroup" <?php echo (isset($adv->steps[5]) ? "style=\"display:block;\"" : ""); ?>>
               <p style="padding:5px; margin:0;">5. Lieu d'intervention</p>
               <table class="table_alternate_color" <?php echo ($adv->modif ? "style=\"display:none;\"" : ""); ?>>
                    <tr>
                         <td class="td_alt1">
                              <?php 
                              $name = "servicelieuinterv";
                              $values = array(
                                  "<strong>Adresse de facturation</strong>" => "facturation"
                              );
                              $actions = array(
                                   "showstep('service6')",
                                   "adv_save('".$name."',6)"
                              );
                              echo $adv->radio_inputs($name, $values, implode(';', $actions));
                              ?>
                              <div style="text-align: left; padding: 0 0 5px 10px;">
                                   <?php
                                   if(!empty($tabUtilisateur['raisonsociale'])) echo '<br>'.$tabUtilisateur['raisonsociale'];
                                   echo '<br>'.$tabUtilisateur['prenom'].' '.$tabUtilisateur['nom'];
                                   echo '<br>'.$tabUtilisateur['adresse'];
                                   if(!empty($tabUtilisateur['adresse2'])) echo '<br>'.$tabUtilisateur['adresse2'];
                                   echo '<br>'.$tabUtilisateur['codepostal'].' '.$tabUtilisateur['ville'];
                                   echo '<br>'.$tabUtilisateur['pays_nom'];
                                   ?>
                              </div>
                         </td>
                    </tr>
                    <tr>
                         <td class="td_alt2">
                              <?php 
                              $name = "servicelieuinterv";
                              $values = array(
                                  "<strong>Adresse de livraison</strong>" => "livraison"
                              );
                              $actions = array(
                                   "showstep('service6')",
                                   "adv_save('".$name."',6)"
                              );
                              echo $adv->radio($name, $values, implode(';', $actions)); 
                              ?>
                              <div style="text-align: left; padding: 0 0 5px 10px;">
                                   <?php
                                   if(!empty($rs_facture['raisonsociale'])) echo '<br>'.$rs_facture['raisonsociale'];
                                   echo '<br>'.$rs_facture['prenom'].' '.$rs_facture['nom'];
                                   echo '<br>'.$rs_facture['adresse'];
                                   if(!empty($rs_facture['adresse2'])) echo '<br>'.$rs_facture['adresse2'];
                                   echo '<br>'.$rs_facture['codepostal'].' '.$rs_facture['ville'];
                                   echo '<br>'.$rs_facture['pays_nom'];
                                   ?>
                              </div>
                         </td>
                    </tr>
                    <tr>
                         <td class="td_alt1">
                              <?php 
                              $name = "servicelieuinterv";
                              $values = array(
                                  "<strong>Autre</strong>" => "autre"
                              );
                              $actions = array(
                                   "showstep('service6')",
                                   "adv_save('".$name."',6)"
                              );
                              echo $adv->radio($name, $values, implode(';', $actions));
                              ?>
                              <br>
                              <div style="text-align: left; padding: 15px 0 5px 10px;">
                              <?php
                              $name = "adresse_intervention";
                              $actions = array(
                                   "adv_autosave(%name)"
                              );
                              $size = array(
                                  "cols" => 50,
                                  "rows" => 5
                              );
                              echo $adv->textarea($name, implode(';', $actions), $size);
                              ?>
                              </div>
                         </td>
                    </tr>
               </table>
          </div>
          <div id="stepservice6" class="advgroup" <?php echo (isset($adv->steps[6]) ? "style=\"display:block;\"" : ""); ?>>
               <p style="padding:5px; margin:0;">6. Date d'intervention</p>
               <table class="table_alternate_color" <?php echo ($adv->modif ? "style=\"display:none;\"" : ""); ?>>
                    <tr>
                         <td class="td_alt1">
                              <?php 
                              $name = "serviceintervcontact";
                              $actions = array(
                                   "showstep('service7')",
                                   "showstep('service8')",
                                   "adv_save(%name,7)"
                              );
                              $value = 1;
                              echo $adv->checkbox($name, implode(';', $actions), $value); 
                              ?> Prendre contact avec le client. Téléphone : 
                              <?php 
                              $name = "serviceintervtelclient";
                              $actions = array(
                                   "adv_autosave(this.name)"
                              );
                              $value = $rs_facture['telephone'];
                              echo $adv->text_input($name,implode(';', $actions),"",$value); 
                              ?>
                         </td>
                    </tr>
                    <tr>
                         <td class="td_alt2">
                              <?php 
                              $name = "servicedateprecise";
                              $actions = array(
                                   "showstep('service7')",
                                   "showstep('service8')",
                                   "adv_save(%name,7)"
                              );
                              $value = 1;
                              echo $adv->checkbox($name, implode(';', $actions), $value); 
                              ?> Date précise : <?php echo $adv->cal_input("miseenservicedateprecise","adv_calsave"); ?>
                         </td>
                    </tr>
                    <tr>
                         <td class="td_alt1">
                              <?php 
                              $name = "servicedateapprox";
                              $actions = array(
                                   "showstep('service7')",
                                   "showstep('service8')",
                                   "adv_save(%name,7)"
                              );
                              $value = 1;
                              echo $adv->checkbox($name, implode(';', $actions), $value); 
                              ?> Aux environs du : <?php echo $adv->cal_input("miseenservicedateapprox","adv_calsave"); ?>
                         </td>
                    </tr>
               </table>
          </div>
          <div id="stepservice7" class="advgroup" <?php echo (isset($adv->steps[7]) ? " style=\"display:block;\"" : ""); ?>>
               <p style="padding:5px; margin:0;">7. Tarifs</p>
               <table class="table_alternate_color">
                    <tr>
                         <td class="td_alt1">
                              Cout de la mise en service : 
                              <?php 
                              $name = "coutmiseenservice";
                              $actions = array(
                                   "adv_autosave(this.name)"
                              );
                              $value = 1;
                              echo $adv->nb_input($name,"","",$pa_ht_total); ?> € HT
                         </td>
                    </tr>
                    <tr>
                         <td class="td_alt2">
                              Informations supplémentaires :
                              <br>
                              <textarea cols="80" onkeyup="adv_save('infocomplement')" name="infocomplement"></textarea>
                         </td>
                    </tr>
               </table>
          </div>
          
          <?php
          $envoidate = null;
          $henvoi    = "";
          if($adv->envoi>0) $envoidate = date("d/m/Y",$adv->envoi);
          if($adv->destinataire != ""){
               
               $sql = "SELECT *";
               $sql .= " FROM adv_heure";
               $sql .= " WHERE fournisseur = '".$adv->destinataire."'";
               $h = $DB_site2->query_first($sql);
               $henvoi = "à ".intval($h['heure'])."h";
          }
          ?>
          <div id="stepservice8" class="advgroup" <?php echo (isset($adv->steps[8]) ? "style=\"display:block;\"" : ""); ?>>
               <p style="padding:5px; margin:0;">8. Finalisation de la commande</p>
               <table class="table_alternate_color">
                    <tr>
                         <td>
                              <input type="button" value="Aperçu" class="preview" onclick="valid4(true);">
                              <div style="text-align:left; <?php echo ($adv->locked ? "display:none;" : ""); ?>">
                                   <br>
                                   <input type="radio" name="envoiauto" onclick="envoidate_save();" <?php echo ($adv->envoi > 0 ? "checked" : ""); ?>> Envoyer la commande automatiquement <span id="envoi1" style="<?php echo ($adv->envoi==0 ? "display:none;" : ""); ?>">le <input type="text" id="envoidate" value="<?php echo $envoidate; ?>" size="10" onclick="calendrier_show(this.id,this.value,undefined,envoidate_save);" readonly> <span id="henvoi"><?php echo $henvoi; ?></span><input type="button" value="Valider" class="clock" onclick="document.location='facture.php?action=editer&facturid=<?php echo $facturid; ?>#adv'"></span>
                                   <br><br>
                                   <input type="radio" name="envoiauto" onclick="envoidate_save();" <?php echo ($adv->envoi==0?"checked":""); ?>> Envoyer la commande immédiatement <span id="envoi0" style="<?php echo ($adv->envoi>0?"display:none;":""); ?>"><input type="button" value="Envoi immédiat" onclick="valid4()"></span>
                               </div>
                               <div style="<?php echo ($adv->locked?"":"display:none;"); ?>;margin-top:10px;"><input type="button" onclick="valid3()" value="Annuler la commande" style="color:red;font-weight:bold;" /></div>
                         </td>
                    </tr>
               </table>
          </div>
     </div>
     <input type="hidden" name="advid" value="<?php echo $adv->id; ?>">
</form>