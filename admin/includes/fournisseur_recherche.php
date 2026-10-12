<div id="formurecherche" class="commande_detail">
     <div style="padding: 10px;">
          <a title="Nouveau fournisseur" href="fournisseurs_liste.php?action=add" class="nouveau_client" style=""><img alt="Nouveau fournisseur" src="images/icon/add_user.png" class="icon" style="margin: 0 5px 0 0; padding: 0;">Nouveau fournisseur</a>
          <form name="srcform" action="fournisseurs_liste.php" method="get" style="display:inline;">
               <strong>Rechercher :</strong>
               <input type="text" placeholder="Fournisseur" name="fournisseur_nom" size="70" value="<?php echo $_GET['src']; ?>"> <input type="submit" title="Rechercher" alt="Rechercher" value="" class="loupe">
               <br /><br />
               <strong>Type :</strong> 
               <select name="fournisseur_type">
                    <option value="">Tous</option>
                    <?php foreach($aTypeFourn as $fourn_type => $fourn_nb){ ?>
                    <option value="<?php echo $fourn_type; ?>" <?php echo isset($_GET['fournisseur_type']) && $_GET['fournisseur_type'] == $fourn_type ? 'selected' : ''; ?>><?php echo ucfirst(str_replace('é','e',$fourn_type))." (".$fourn_nb.")"; ?></option>
                    <?php } ?>
               </select>
               <div class="separateur_vertical"></div>
               <strong>Code 401 : </strong> 
               <select name="fournisseur_code401">
                    <option value="0">Tous</option>
                    <option value="1"<?php echo ($_GET['fournisseur_code401']==1?" selected":""); ?>>Avec</option>
                    <option value="2"<?php echo ($_GET['fournisseur_code401']==2?" selected":""); ?>>Sans</option>
               </select>
               <div class="separateur_vertical"></div>
               <strong>Etat : </strong>
               <select name="fournisseur_activite">
                    <option value="0">Tous</option>
                    <option value="1"<?php echo ($_GET['fournisseur_activite']==1?" selected":""); ?>>Actif</option>
                    <option value="2"<?php echo ($_GET['fournisseur_activite']==2?" selected":""); ?>>Inactif</option>
               </select>
               &nbsp;<img src="./images/rechercher.gif" onclick="document.forms.srcform.submit();" style="cursor:pointer; vertical-align:bottom;">
               <a href="fournisseurs_liste.php" class="recherche_init" style="float: right;">Réinitialiser la recherche</a>
          </form>
     </div>
</div>
<div class="clear"></div>