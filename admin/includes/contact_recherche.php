<?php

//Reconstruction de l'url pour l'export
$url_export = '';
foreach($_GET as $param => $value){

     if($param != 'page'){
          if(substr($url_export,-1) != '?' && substr($url_export,-1) != '&'){
               if(empty($url_export)){
                    $url_export .= '?';
               } else {
                    $url_export .= '&';
               }
          }
          if(is_array($value)){
               foreach($value as $sub_value){
                    if ($sub_value != '')
                         $url_export .= $param.'[]='.$sub_value;
               }
          } else {
               if ($value!='')
                    $url_export .= $param.'='.$value;
          }
     }
}

$optpays="<option value=\"\">Pays</option>";
$req=$DB_site2->query("SELECT * FROM pays ORDER BY libelle");
while($pays=$DB_site2->fetch_array($req)){
 $optpays.=sprintf("<option value=\"%u\"%s>%s</option>",$pays['paysid'],(empty($_GET['paysid']) === false && $_GET['paysid']==$pays['paysid']?" selected":""),$pays['libelle']);
}

?>
<div id="formurecherche" class="commande_detail">
     <div class="commande_detail_bis" style="padding: 10px;">
          <a title="Nouveau client" href="utilisateur.php?action=new" class="nouveau_client" style=""><img alt="Nouveau client" src="images/icon/add_user.png" class="icon" style="margin: 0 5px 0 0; padding: 0;">Nouveau client</a>
          
          <form name="srcform" action="utilisateur.php" method="get" style="margin-top: 20px;">
               <input type="hidden" name="orderby" value="<?php echo empty($_GET['orderby']) ? '' : $_GET['orderby']; ?>
               <input type="hidden" name="ordersens" value="<?php echo empty($_GET['ordersens']) ? '' : $_GET['ordersens']; ?>
               <strong>Recherche Client :</strong><br>
               <div class="bloc_recherche" style="border-bottom:2px solid #5088A1;">

                    <input type="text" placeholder="Nom" name="nom" size="35" value="<?php echo empty($_GET['nom']) ? '' : $_GET['nom']; ?>" style="width:120px;">
                    <input type="text" placeholder="Prénom" name="prenom" size="35" value="<?php echo empty($_GET['prenom']) ? '' : $_GET['prenom']; ?>" style="width:120px;">
                    <input type="text" placeholder="Société / Email / Tél. / Client" name="src" class="source" size="70" value="<?php echo empty($_GET['src']) ? '' : $_GET['src']; ?>" style="width:165px;">
                    <input type="text" placeholder="N° dép. / CP" name="dpt" size="16" value="<?php echo empty($_GET['dpt']) ? '' : $_GET['dpt']; ?>" style="width:100px">
                    <select name="paysid"><?=$optpays; ?></select>
                    <input type="submit" title="Rechercher" alt="Rechercher" value="OK">
                    <br /><br />

                    <p style="cursor:pointer; font-weight:bold;" onclick="affich_recherche(this)">Recherche avancée +</p>     
                    <div id="recherche_avancee" style="display:none;">
                         <table class="tab_client">
                              <tr>
                                   <td><strong>Commercial</strong> :</td>
                                   <td><strong>Statut client</strong> :</td>
                                   <td><strong>Type de client</strong> :</td>
                                   <td><strong>Profil client</strong> :</td>
                                   <td><strong>Code 401</strong> :</td>
                                   <td><strong>Provenance</strong> :</td>
                                   <td><strong>Type de suivi</strong> :</td>


                                   <!--<td rowspan="2" style="border:none;"><a href="javascript:void(0);" onclick="document.forms.srcform.submit();" class="tab_client_search"><i class="fa fa-search" aria-hidden="true"></i> Rechercher</a></td>-->
                              </tr>

                              <tr>
                                   <td><select name="commercial" style="width: 75px;"><?php if($optcom != " "){echo $optcom;}?></select></td>
                                   <td><select name="encours" style="width: 75px;">
                                        <option value="">Tous</option>
                                        <option value="11"<?php echo ($_GET['encours']==11?" selected":""); ?>>A suivre</option>
                                        <option value="12"<?php echo ($_GET['encours']==12?" selected":""); ?>>Attente info client</option>
                                        <option value="13"<?php echo ($_GET['encours']==13?" selected":""); ?>>A chiffrer</option>

                                        <option value="2"<?php echo ($_GET['encours']==2?" selected":""); ?>>En Commande</option>
                                        <option value="3"<?php echo ($_GET['encours']==3?" selected":""); ?>>Suivi automatique</option>
                                        <option value="4"<?php echo ($_GET['encours']==4?" selected":""); ?>>Dossier sous-traité</option>
                                        <option value="0"<?php echo ($_GET['encours']=="0"?" selected":""); ?>>Perdu</option>
                                        <option value="nf"<?php echo ($_GET['encours']=="nf"?" selected":""); ?>>Non fidélisé</option>
                                        <option value="fid"<?php echo ($_GET['encours']=="fid"?" selected":""); ?>>En fidelisation</option>
                                   </select></td>
                                   <td><select name="typeclient" style="width: 75px;">
                                        <option value="">Tous</option>
                                        <option value="part"<?php echo ($_GET['typeclient']=="part"?" selected":""); ?>>Particuliers</option>
                                        <option value="pro"<?php echo ($_GET['typeclient']=="pro"?" selected":""); ?>>Pro</option>
                                   </select></td>
                                   <td><select name="vip" style="width: 75px;">
                                        <option value="">Tous</option>
                                        <option value="-1"<?php echo ($_GET['vip']==-1?"selected":""); ?>>Client blacklisté</option>
                                        <option value="-2"<?php echo ($_GET['vip']==-2?"selected":""); ?>>Client difficile</option>
                                        <option value="0"<?php echo ($_GET['vip']=="0"?"selected":""); ?>>Client normal</option>
                                        <option value="1"<?php echo ($_GET['vip']==1?"selected":""); ?>>Client VIP</option>
                                        <option value="2"<?php echo ($_GET['vip']==2?"selected":""); ?>>Partenaire</option>
                                   </select></td>
                                   <td><select name="code401" style="width: 75px;">
                                        <option value="">Tous</option>
                                        <option value="1"<?php echo ($_GET['code401']==1?"selected":""); ?>>Avec</option>
                                        <option value="2"<?php echo ($_GET['code401']==2?"selected":""); ?>>Sans</option>
                                   </select></td>
                                   <td><select name="prov" style="width: 75px;">
                                        <option value="">Tous</option>
                                        <option value="1"<?php echo ($_GET['prov']==1?"selected":""); ?>>Site</option>
                                        <option value="2"<?php echo ($_GET['prov']==2?"selected":""); ?>>Mobile</option>
                                   </select></td>
                                   <td><select name="suivi" style="width: 75px;">
                                        <option value="">Tous</option>
                                        <option value="0" <?php echo ($_GET['suivi'] === '0' ? "selected" : ""); ?>>Non défini</option>
                                        <?php foreach($tabTypeSuivi as $le_type_suivi){ ?>
                                             <option value="<?php echo $le_type_suivi['utilisateur_type_suivi_id']; ?>" <?php echo ($_GET['suivi'] == $le_type_suivi['utilisateur_type_suivi_id'] ? "selected" : ""); ?>><?php echo $le_type_suivi['utilisateur_type_suivi_nom']; ?></option>
                                        <?php } ?>
                                   </select></td>
                              </tr>
                              <tr>
                                   <td><strong>Inscrip. newsletter</strong> :</td>
                                   <td><strong>Inscrit au SMS</strong> :</td>
                                   <td colspan="5"></td>
                              </tr>
                              <tr>
                                   <td><select name="newsletter" style="width: 75px;">
                                        <option value="">Tous</option>
                                        <option value="1"<?php echo ($_GET['newsletter']==1?"selected":""); ?>>Oui</option>
                                        <option value="2"<?php echo ($_GET['newsletter']==2?"selected":""); ?>>Non</option>
                                   </select></td>
                                   <td><select name="SMS" style="width: 75px;">
                                        <option value="">Tous</option>
                                        <option value="1"<?php echo ($_GET['SMS']==1?"selected":""); ?>>Oui</option>
                                        <option value="2"<?php echo ($_GET['SMS']==2?"selected":""); ?>>Non</option>
                                   </select></td>
                                   <td><input type="checkbox" name="noprojet" value="1"<?=isset($_GET['noprojet'])?" checked":""; ?>> Sans projet</td>
                                   <td colspan="4">
                                        <strong>Inscrit entre </strong>
                                        <input type="text" size="14" name="orderdate" id="orderdate" value="<?php echo($orderdate) ?>" onclick="calendrier_show(this.id,this.value,true)" readonly>
                                        <strong> et </strong>
                                        <input type="text" size="14" name="deliverdate" id="deliverdate" value="<?php echo($deliverdate)?>" onclick="calendrier_show(this.id,this.value,true, null, null, 1)"readonly>
                                   </td>
                              </tr>
                         </table>
                    </div>
               </div>              
          </form>
          <div class="flex_rech" style="justify-content: space-between;">
               <!-- TSI Ajout d'une recherche sur les commandes --> 
               <form name="Rechform" action="facture.php" target="_blank" method="post" style="display:inline;">
                    <strong>Recherche Commande:</strong>
                    <div class="bloc_recherche" style="width:max-content;margin-right:10px;">
                         <input name="action" value="rechercher" type="hidden">
                         <input type="text" placeholder="Cde / Fact / Nom / Société / Mail / N° tel" name="ref" size="70" value="" style="width:220px;margin-right:5px;">
                         <input type="checkbox" name="suppr" style="vertical-align:top;"<?=isset($suppr)?"checked":""?>> Supprimées  
                         <input type="submit" title="Rechercher" alt="Rechercher" value="OK">
                    </div>

               </form>
               <div>
                    <!-- Recherche par article -->
                    <form method="post" action="facture.php" style="display:inline;" >
                      <input type="hidden" name="action" value="article">
                      <b>&nbsp;Recherche article  :</b>
                      <div class="bloc_recherche" style="width:max-content;margin-right:10px;">
                           <input type="text" placeholder="Code article" name="ref" value="<?= $action=="article"?$ref:"";?>" style=" width:140px;margin-right:5px;"><input type="checkbox" name="valid"
                           <?= isset($valid)?" checked":"";?> style="vertical-align: sub;"> Cde validées 
                           <input type="submit" value="OK"><?php echo empty($liens) ? '' : $liens; ?>
                      </div>
                 </div>
            </form>

       <div>
          <!-- Recherche par reference transporteur -->
          <form method="get" style="display:inline;" action="facture.php" >
            <input type="hidden" name="action" value="trspt">
            <b>N° transporteur :</b>
            <div class="bloc_recherche" style="width:max-content;">
                 <input type="text" placeholder="Réf trspt / inst" name="ref" size="60" value="<?=$action=="trspt"?$ref:"";?>" style="width:110px;margin-right:5px;">&nbsp;
                 <input type="submit" value="OK"><?php echo empty($liens) ? '' : $liens; ?>
            </div>
       </form>
  </div>
</div>

<form method="get" action="projets_new.php">
    <input type="hidden" name="action" value="search">
    <input type="hidden" name="type_page" value="<?php echo empty($type_page) ? '' : $type_page; ?>"
    <td>
     <strong>Recherche Projet :</strong>
     <div class="bloc_recherche">
          <input type="text" placeholder="Nom  / Société / N° client / Libellé / N° projet" name="src" size="70" value="<?php empty($_GET['src']) === false ? $_GET['src'] : ''; ?>">
     </td>
     <td style="border:none;"><input type="submit" title="Rechercher" alt="Rechercher" value="OK"></td>
</div>
</form> 
</div>       
<div style="padding: 10px;">   
     <a href="utilisateur.php" class="recherche_init"><i class="fa fa-times" aria-hidden="true"></i> Réinitialiser</a>
</div>
</div>
<div class="clear"></div>
