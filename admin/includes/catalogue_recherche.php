<?php
$sql = "SELECT marqueid, libelle";
$sql .= " FROM marque";
$sql .= " ORDER BY libelle ASC";
$req = $DB_site2->query($sql);
while($row = $DB_site2->fetch_array($req)){
     $tabFiltreMarque[] = $row;
}

$sql = "SELECT id, societe";
$sql .= " FROM fournisseur_condition";
$sql .= " ORDER BY societe ASC";
$req = $DB_site2->query($sql);
while($row = $DB_site2->fetch_array($req)){
     $tabFiltreFournisseur[] = $row;
}

$sql = "SELECT typearticleid, libelle";
$sql .= " FROM typearticle";
$sql .= " ORDER BY libelle ASC";
$req = $DB_site2->query($sql);
while($row = $DB_site2->fetch_array($req)){
     $tabFiltreType[] = $row;
}

//Récupération de la liste des catégories mères
$sql = "SELECT catid, libelleFR";
$sql .= " FROM categorie";
$sql .= " WHERE parentid = 0";
$sql .= " ORDER BY libelleFR";
$req = $DB_site2->query($sql);
$cpt = 0;
while($tabCatMere = $DB_site2->fetch_array($req)){
     $tabFiltreCategorie[$cpt]['categorie_id']      = $tabCatMere['catid'];
     $tabFiltreCategorie[$cpt]['categorie_libelle'] = '[1]'.$tabCatMere['libelleFR'];
     $cpt++;
             
     //Récupération de la liste des catégories filles
     $sql = "SELECT catid, libelleFR";
     $sql .= " FROM categorie";
     $sql .= " WHERE parentid = ".$tabCatMere['catid'];
     $sql .= " ORDER BY libelleFR";
     $req2 = $DB_site2->query($sql);

     while($tabCatFille = $DB_site2->fetch_array($req2)){ 
          $tabFiltreCategorie[$cpt]['categorie_id']      = $tabCatFille['catid'];
          $tabFiltreCategorie[$cpt]['categorie_libelle'] = '[2]'.$tabCatMere['libelleFR'].' > '.$tabCatFille['libelleFR'];
          $cpt++;

          //Récupération de la liste des catégories petites-filles
          $sql = "SELECT catid, libelleFR";
          $sql .= " FROM categorie";
          $sql .= " WHERE parentid = ".$tabCatFille['catid'];
          $sql .= " ORDER BY libelleFR";
          $req3 = $DB_site2->query($sql);

          while($tabCatPetiteFille = $DB_site2->fetch_array($req3)){
               $tabFiltreCategorie[$cpt]['categorie_id']      = $tabCatPetiteFille['catid'];
               $tabFiltreCategorie[$cpt]['categorie_libelle'] = '[3]'.$tabCatMere['libelleFR'].' > '.$tabCatFille['libelleFR'].' > '.$tabCatPetiteFille['libelleFR'];
               $cpt++;
          }
     }
} 

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
                    $url_export .= $param.'[]='.$sub_value;
               }
          } else {
               $url_export .= $param.'='.$value;
          }
     }
}

foreach($_POST as $param => $value){

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
                    $url_export .= $param.'[]='.$sub_value;
               }
          } else {
               $url_export .= $param.'='.$value;
          }
     }
}

$affiche_creation = false;
if(!isset($_GET['catid']) || $_GET['catid'] == 0){
     $affiche_creation = true;
     $type_creation = 'catégorie';
} elseif(!empty($_GET['catid'])) {
     $affiche_creation = true;
     $type_creation = 'sous-catégorie';
}
?>
<div id="formurecherche" class="table_factures commande_detail">
     <div style="padding: 10px;">
          <?php if($affiche_creation || isset($tabCategorie['catid'])){ ?>
          <div style="float:right; display: block; text-align: right;">
               <?php if($affiche_creation){ ?>
               <a title="Créer une <?php echo $type_creation; ?>" href="ajouter_categorie.php?parentid=<?php echo $catid; ?>" class="nouvelle_rubrique" style=""><img alt="Créer une <?php echo $type_creation; ?>" src="<?php echo BACK_HOST; ?>/images/icon/db_add.png" class="icon" style="margin: 0 5px 0 0; padding: 0;">Créer une <?php echo $type_creation; ?></a>
               <?php } ?>
               <?php 
               if(isset($tabCategorie['catid'])){
                    if($tabCategorie['catid'] == 6 || $tabCategorie['parentid'] == 6){
                         $lien_creation = 'ajout_pack';
                    } else {
                         $lien_creation = 'ajout_produit';
                    }
               ?>
               <br>
               <a title="Créer une article" href="<?php echo $lien_creation; ?>.php?catid=<?php echo $tabCategorie['catid'].(isset($tabCategorie['parentid']) && !empty($tabCategorie['parentid']) ? '&parentid='.$tabCategorie['parentid'] : ''); ?>" class="nouvelle_rubrique" style=""><img alt="Créer une article" src="<?php echo BACK_HOST; ?>/images/icon/product_add_32.png" class="icon" style="margin: 0 5px 0 0; padding: 0;">Créer un article</a>
               <?php } ?>
               <br>
               <a title="Exporter la sélection" href="recherche_export2.php<?php echo $url_export; ?>" class="nouvelle_rubrique" style=""><img alt="Exporter la sélection" src="<?php echo BACK_HOST; ?>/images/icon/export.png" class="icon" style="margin: 0 5px 0 0; padding: 0;">Exporter la sélection</a>
          </div>
          <?php } ?>
          <form name="formu" method="post" action="rechercher.php" style="margin:0;">
               <table class="catalogue_recherche">
                    <tr>
                         <td class="intitule" style="vertical-align: top;"><strong>Rechercher</strong></td>
                         <td colspan="3">
                              <input type="text" placeholder="Libellé / Réf. interne / Réf. fabricant" name="recherche" size="70" value="<?php echo isset($recherche) && !empty($recherche) ? $recherche : ''; ?>" style="margin: 0;"> <input type="submit" value="Rechercher" class="skined" style="width: 110px;">
                              <br />
                              <input style="vertical-align: -2px;" type="checkbox" name="active[]" <?php echo (isset($_POST['active']) && in_array(1, $_POST['active'])) || (isset($_GET['active']) && in_array(1, $_GET['active'])) ? 'checked' : ''; ?> value="1"> Actifs 
                              <input style="vertical-align: -2px;" type="checkbox" name="active[]" <?php echo (isset($_POST['active']) && in_array(0, $_POST['active'])) || (isset($_GET['active']) && in_array(0, $_GET['active'])) ? 'checked' : ''; ?> value="0"> Actifs invisibles 
                              <input style="vertical-align: -2px;" type="checkbox" name="active[]" <?php echo (isset($_POST['active']) && in_array(2, $_POST['active'])) || (isset($_GET['active']) && in_array(2, $_GET['active'])) ? 'checked' : ''; ?> value="2"> Supprimés
                              <input style="vertical-align: -2px;" type="checkbox" name="active[]" <?php echo (isset($_POST['active']) && in_array(6, $_POST['active'])) || (isset($_GET['active']) && in_array(6, $_GET['active'])) ? 'checked' : ''; ?> value="6"> En création
                              <input style="vertical-align: -2px;" type="checkbox" name="active[]" <?php echo (isset($_POST['active']) && in_array(4, $_POST['active'])) || (isset($_GET['active']) && in_array(4, $_GET['active'])) ? 'checked' : ''; ?> value="4"> A contrôler
                              <input style="vertical-align: -2px;" type="checkbox" name="active[]" <?php echo (isset($_POST['active']) && in_array(5, $_POST['active'])) || (isset($_GET['active']) && in_array(5, $_GET['active'])) ? 'checked' : ''; ?> value="5"> Archiver temporairement
                              <input style="vertical-align: -2px;" type="checkbox" name="active[]" <?php echo (isset($_POST['active']) && in_array(3, $_POST['active'])) || (isset($_GET['active']) && in_array(3, $_GET['active'])) ? 'checked' : ''; ?> value="3"> Archiver définitivement

                         </td>
                    </tr>
                    <tr>
                         <td class="intitule"><strong>Type article</strong></td>
                         <td class="champs">
                              <select name="type">
                                   <option value="0">- Sélectionner -</option>
                                   <?php foreach($tabFiltreType as $le_type){ ?>
                                   <option value="<?php echo $le_type['typearticleid']; ?>" <?php echo (isset($_POST['type']) && $le_type['typearticleid'] == $_POST['type']) || (isset($_GET['type']) && $le_type['typearticleid'] == $_GET['type']) ? 'selected' : ''; ?>><?php echo $le_type['libelle']; ?></option>
                                   <?php } ?>
                              </select>
                         </td>
                         <td class="intitule"><strong>Catégorie</strong></td>
                         <td>
                              <select name="categorie" style="width:400px;">
                                   <option value="0">- Sélectionner -</option>
                                   <?php 
                                   $style = '';
                                   foreach($tabFiltreCategorie as $la_categorie){ 
                                        if(strpos($la_categorie['categorie_libelle'], '[1]') !== false){
                                             $la_categorie['categorie_libelle'] = str_replace('[1]', '', $la_categorie['categorie_libelle']);
                                             $style = 'font-weight: bold;';
                                        }
                                        
                                        if(strpos($la_categorie['categorie_libelle'], '[2]') !== false){
                                             $la_categorie['categorie_libelle'] = str_replace('[2]', '', $la_categorie['categorie_libelle']);
                                             $style = 'text-decoration: underline; padding-left: 20px;';
                                        }
                                        
                                        if(strpos($la_categorie['categorie_libelle'], '[3]') !== false){
                                             $la_categorie['categorie_libelle'] = str_replace('[3]', '', $la_categorie['categorie_libelle']);
                                             $style = 'padding-left: 40px;';
                                        }
                                   ?>
                                   <option <?php echo !empty($style) ? 'style="'.$style.'"' : ''; ?> value="<?php echo $la_categorie['categorie_id']; ?>" <?php echo (isset($_POST['categorie']) && $la_categorie['categorie_id'] == $_POST['categorie']) || (isset($_GET['categorie']) && $la_categorie['categorie_id'] == $_GET['categorie']) ? 'selected' : ''; ?>><?php echo $la_categorie['categorie_libelle']; ?></option>
                                   <?php } ?>
                              </select>
                         </td>
                    </tr>
                    <tr>
                         <td class="intitule"><strong>Marque</strong></td>
                         <td class="champs">
                              <select name="marque">
                                   <option value="0">- Sélectionner -</option>
                                   <?php foreach($tabFiltreMarque as $la_marque){ ?>
                                   <option value="<?php echo $la_marque['marqueid']; ?>" <?php echo (isset($_POST['marque']) && $la_marque['marqueid'] == $_POST['marque']) || (isset($_GET['marque']) && $la_marque['marqueid'] == $_GET['marque']) ? 'selected' : ''; ?>><?php echo $la_marque['libelle']; ?></option>
                                   <?php } ?>
                              </select>
                         </td>
                         <td class="intitule"><strong>Fournisseur</strong></td>
                         <td>
                              <select name="fournisseur">
                                   <option value="0">- Sélectionner -</option>
                                   <?php foreach($tabFiltreFournisseur as $le_fournisseur){ ?>
                                   <option value="<?php echo $le_fournisseur['id']; ?>" <?php echo (isset($_POST['fournisseur']) && $le_fournisseur['id'] == $_POST['fournisseur']) || (isset($_GET['fournisseur']) && $le_fournisseur['id'] == $_GET['fournisseur']) ? 'selected' : ''; ?>><?php echo $le_fournisseur['societe']; ?></option>
                                   <?php } ?>
                              </select>
                         </td>
                    </tr>
                    <tr>
                         <td colspan="4" style="height:30px; vertical-align: bottom;">
                              <input type="checkbox" name="reappro" <?php echo isset($_POST['reappro']) || isset($_GET['reappro']) ? 'checked' : ''; ?> value="1"> Réappro.
                              <input type="checkbox" name="cdc" <?php echo isset($_POST['cdc']) || isset($_GET['cdc']) ? 'checked' : ''; ?> value="1"> Coup de coeur
                              <input type="checkbox" name="promo" <?php echo isset($_POST['promo']) || isset($_GET['promo']) ? 'checked' : ''; ?> value="1"> Promotion
                              <input type="checkbox" name="idkdos" <?php echo isset($_POST['idkdos']) || isset($_GET['idkdos']) ? 'checked' : ''; ?> value="1"> Idée cadeau
                              <input type="checkbox" name="deselection" <?php echo isset($_POST['deselection']) || isset($_GET['deselection']) ? 'checked' : ''; ?> value="1"> Hors sélection
                              <input type="checkbox" name="erreurs" <?php echo isset($_POST['erreurs']) || isset($_GET['erreurs']) ? 'checked' : ''; ?> value="1"> Incomplet
                         </td>
                    </tr>
                    <tr>
                         <td colspan="4" style="height:30px; vertical-align: bottom;">
                              <a href="rechercher.php" class="recherche_init">Réinitialiser la recherche</a>
                         </td>
                    </tr>
               </table>
          </form>
    </div>
</div>
<div style="clear: both;"></div>
