<?php
$maGarantie = new garantie($DB_site2,'FR',$garantieid);

//Liste déroulante des fournisseurs
$optsoc = "<option value=\"\">-- Sélectionner --</option>";

$sql = "SELECT DISTINCT(societe)";
$sql .= " FROM fournisseur f";
$sql .= " WHERE f.type = 'fournadv'";
$sql .= " OR f.type = 'garadv'";
$sql .= " ORDER BY societe;";
$r = $DB_site2->query($sql);
while($soc = $DB_site2->fetch_array($r)){
	
     $selected = "";
     if($soc['societe'] == $maGarantie->fournisseur) $selected = "selected";
     
     $optsoc .= "<option value=\"".$soc['societe']."\" $selected>".$soc['societe']."</option>";
}

//Liste déroulante des types de garanties
$optgar .= "<option value=\"\">-- Sélectionner --</option>";

$sql = "SELECT id_type, libelle_type";
$sql .= " FROM garantie_type";
$r = $DB_site2->query($sql);
while($gar = $DB_site2->fetch_array($r)){
     
     $selected = "";
     if($gar['id_type'] == $maGarantie->type_garantie) $selected = "selected";
     
     $optgar .= "<option value=\"".$gar['id_type']."\" $selected>".$gar['libelle_type']."</option>";
}

//Checkboxes des marques
$sql = "SELECT marqueid, libelle";
$sql .= " FROM marque";
$sql .= " ORDER BY libelle";
$r = $DB_site2->query($sql);
$cptMarque = 0;
while($marques = $DB_site2->fetch_array($r)){
     
     $aCheckMarque[] = $marques;
}
$nbMarques     = count($aCheckMarque);
$nbMarqueCols  = ceil($nbMarques/3);

//Checkboxes des types d'articles
$sql = "SELECT typearticleid, libelle";
$sql .= " FROM typearticle";
$sql .= " ORDER BY libelle";
$r = $DB_site2->query($sql);
$cptType = 0;
while($types = $DB_site2->fetch_array($r)){
     
     $aCheckTypeArticle[] = $types;
}
$nbTypesArticles     = count($aCheckTypeArticle);
$nbTypesArticlesCols = ceil($nbTypesArticles/3);

//Checkboxes des pays
$sql = "SELECT paysid, libelle";
$sql .= " FROM pays WHERE typepays=\"EE\" ORDER BY libelle";
$r = $DB_site2->query($sql);
$cptPays = 0;
while($pays = $DB_site2->fetch_array($r)){
     
     $aCheckPays[] = $pays;
}
$nbPays     = count($aCheckPays)+2; //+2 pour "reste du monde" et "reste de l'europe"
$nbPaysCols = ceil($nbPays/3);

?>
<a style="color: #363E42; font-weight: bold; font-size: 13px; margin: 10px 0 0 10px; float: left;" href="garanties.php"><img src="<?php echo BACK_HOST; ?>/images/icon/previous.png" style="vertical-align: -8px; margin: 0 5px 0 0;">Retour à la liste</a><br><br>
<h3>Garantie n°<?php echo $maGarantie->garantieid; ?></h3>

<?php if(!empty($error)) echo "<span style=\"color:red\">".$error."</span>"; ?>

<form method="post" name="frmGarantie" onsubmit="return valideFrmGarantie();">
     <input type="hidden" name="modifBDD" value="modifBDD"/>
     <input type="hidden" name="garantieid" id="garantieid" value="<?php echo $maGarantie->garantieid; ?>"/>
        
     <div id="onglet_garantie" class="admin_onglet onglet_actif" onclick="affiche_div(this, 'garantie');">Garantie</div>
     <div id="onglet_tarifs" class="admin_onglet" onclick="affiche_div(this, 'tarifs');">Tarifs</div>
     <div id="onglet_marques" class="admin_onglet" onclick="affiche_div(this, 'marques');">Marques</div>
     <div id="onglet_typesarticles" class="admin_onglet" onclick="affiche_div(this, 'types_articles');">Types d'articles</div>
     <div id="onglet_pays" class="admin_onglet" onclick="affiche_div(this, 'pays');">Pays</div>
     <div class="clear"></div>

     <!-- DEBUT ENCART GARANTIE -->
     <div id="garantie" class="contenus" style="display: block;">
          <table style="width: 100%;">
               <tr>
                    <td style="width: 120px;"><strong>Prestataire</strong> :</td>
                    <td>
                          <input type="radio" name="utilisation_garantie" value="0" <?php echo $maGarantie->utilisation_garantie == 0 ? 'checked' : ''; ?>/> Fournisseur *
                          <input type="radio" name="utilisation_garantie" value="1" <?php echo $maGarantie->utilisation_garantie == 1 ? 'checked' : ''; ?>/> Complémentaire **
                    </td>
               </tr>
               <tr>
                    <td><strong>Fournisseur</strong> : </td>
                    <td><select name="fournisseur"><?php echo $optsoc; ?></select></td>
               </tr>
               <tr>
                    <td><strong>Destinataires</strong> :</td>
                    <td>
                          <input type="radio" name="destinataire_garantie" value="1" <?php echo $maGarantie->destinataire_garantie == 1 ? 'checked' : ''; ?>/> Particuliers 
                          <input type="radio" name="destinataire_garantie" value="2" <?php echo $maGarantie->destinataire_garantie == 2 ? 'checked' : ''; ?>/> Professionnels
                    </td>
               </tr>
               <tr>
                    <td>&nbsp;</td>
               </tr>
               <tr>
                    <td><strong>Extension</strong> :</td>
                    <td>
                         <input type="radio" name="cumul" value="1" <?php echo $maGarantie->cumul == 1 ? 'checked' : ''; ?>/> Oui 
                         <input type="radio" name="cumul" value="2" <?php echo $maGarantie->cumul == 2 ? 'checked' : ''; ?>/> Non
                    </td>
               </tr>
               <tr>
                    <td><strong>Durée</strong> :</td>
                    <td>
                         <input type="text" name="duree" style="width:25px" value="<?php echo $maGarantie->duree; ?>" onkeypress="nbonly(event,true,this);"> ans(s) (à vie = 99)
                    </td>
               </tr>
               <tr>
                    <td><strong>Couverture</strong> :</td>
                    <td>
                         <select name="type_garantie"><?php echo $optgar; ?></select>
                    </td>
               </tr>
               <tr>
                    <td>&nbsp;</td>
               </tr>
               <tr>
                    <td style="vertical-align: top;"><strong>Commentaire(s)</strong> :</td>
                    <td>
                         <textarea  name="comm" rows="4"><?php echo $maGarantie->comm; ?></textarea>
                    </td>
               </tr>
               <tr>
                    <td height="30">&nbsp;</td>
               </tr>
               <tr>
                    <td colspan="2">
                         * Fournisseur de l'article garantie (Novy, ILVE, Steel, etc...)
                         <br>
                         ** Autre organisme (SFG, Enigma, etc...)
                         <br>
                         *** Articles dont le prix de vente TTC est compris dans cette plage (0 pour aucune)
                    </td>
               </tr>
          </table>
     </div>
     <!-- FIN ENCART GARANTIE -->
     
     <!-- DEBUT ENCART TARIFS -->
     <div id="tarifs" class="contenus">
     <?php include(BACK_PATH.'/ajax/tarifs_tranche.php'); ?>
     </div>
     <!-- FIN ENCART TARIFS -->

     <!-- DEBUT ENCART MARQUES -->
     <div id="marques" class="contenus">
          <table class="tbl_checkbox">
               <tr>
                    <td>
                         <?php
                         $compteur = 0;
                         $nbMax = $nbMarqueCols;
                         for($i=$compteur;$i<$nbMax;$i++){

                              $checked = "";
                              $hidden  = "";
                              $gras    = "";

                              if(in_array($aCheckMarque[$i]['marqueid'], $maGarantie->marques)) {

                                   $checked = "checked";
                                   $hidden  = "style=\"visibility:hidden;\"";
                                   $gras    = "style=\"font-weight:bold;\"";
                              }

                              $compteur++;

                              echo "<input type=\"checkbox\" name=\"marques[]\" id=\"marques_".$compteur."\" value=\"".$aCheckMarque[$i]['marqueid']."\" ".$hidden." ".$checked." /><span ".$gras.">".$aCheckMarque[$i]['libelle']."</span><br/>";
                         }

                         ?>
                    </td>
                    <td>
                         <?php
                         $nbMax = $nbMarqueCols + $compteur;
                         for($i=$compteur;$i<$nbMax;$i++){

                              $checked = "";
                              $hidden  = "";
                              $gras    = "";

                              if(in_array($aCheckMarque[$i]['marqueid'], $maGarantie->marques)) {

                                   $checked = "checked";
                                   $hidden  = "style=\"visibility:hidden;\"";
                                   $gras    = "style=\"font-weight:bold;\"";
                              }

                              $compteur++;

                              echo "<input type=\"checkbox\" name=\"marques[]\" id=\"marques_".$compteur."\" value=\"".$aCheckMarque[$i]['marqueid']."\" ".$hidden." ".$checked." /><span ".$gras.">".$aCheckMarque[$i]['libelle']."</span><br/>";
                         }

                         ?>
                    </td>
                    <td>
                         <?php
                         $nbMax = $nbMarqueCols + $compteur > count($aCheckMarque) ? count($aCheckMarque) : $nbMarqueCols + $compteur;
                         for($i=$compteur;$i<$nbMax;$i++){

                              $checked = "";
                              $hidden  = "";
                              $gras    = "";

                              if(in_array($aCheckMarque[$i]['marqueid'], $maGarantie->marques)) {

                                   $checked = "checked";
                                   $hidden  = "style=\"visibility:hidden;\"";
                                   $gras    = "style=\"font-weight:bold;\"";
                              }

                              $compteur++;

                              echo "<input type=\"checkbox\" name=\"marques[]\" id=\"marques_".$compteur."\" value=\"".$aCheckMarque[$i]['marqueid']."\" ".$hidden." ".$checked." /><span ".$gras.">".$aCheckMarque[$i]['libelle']."</span><br/>";
                         }

                         ?>
                    </td>
               </tr>
          </table>
     </div>
     <!-- FIN ENCART MARQUES -->

     <!-- DEBUT ENCART TYPES ARTICLES -->
     <div id="types_articles" class="contenus">
          <table class="tbl_checkbox">
               <tr>
                    <td>
                         <?php
                         $compteur = 0;
                         $nbMax = $nbTypesArticlesCols;
                         for($i=$compteur;$i<$nbMax;$i++){

                              $checked = "";
                              $hidden  = "";
                              $gras    = "";

                              if(in_array($aCheckTypeArticle[$i]['typearticleid'], $maGarantie->types_produits)){

                                   $checked = "checked";
                                   $hidden  = "style=\"visibility:hidden;\"";
                                   $gras    = "style=\"font-weight:bold;\"";
                              }

                              $compteur++;

                              echo "<input type=\"checkbox\" name=\"types_produits[]\" id=\"types_produits_".$compteur."\" value=\"".$aCheckTypeArticle[$i]['typearticleid']."\" ".$hidden." ".$checked." /><span ".$gras.">".$aCheckTypeArticle[$i]['libelle']."</span><br/>";
                         }

                         ?>
                    </td>
                    <td>
                         <?php
                         $nbMax = $nbTypesArticlesCols + $compteur;
                         for($i=$compteur;$i<$nbMax;$i++){

                              $checked = "";
                              $hidden  = "";
                              $gras    = "";

                              if(in_array($aCheckTypeArticle[$i]['typearticleid'], $maGarantie->types_produits)){

                                   $checked = "checked";
                                   $hidden  = "style=\"visibility:hidden;\"";
                                   $gras    = "style=\"font-weight:bold;\"";
                              }

                              $compteur++;

                              echo "<input type=\"checkbox\" name=\"types_produits[]\" id=\"types_produits_".$compteur."\" value=\"".$aCheckTypeArticle[$i]['typearticleid']."\" ".$hidden." ".$checked." /><span ".$gras.">".$aCheckTypeArticle[$i]['libelle']."</span><br/>";
                         }

                         ?>
                    </td>
                    <td>
                         <?php
                         $nbMax = $nbTypesArticlesCols + $compteur > count($aCheckTypeArticle) ? count($aCheckTypeArticle) : $nbTypesArticlesCols + $compteur;
                         for($i=$compteur;$i<$nbMax;$i++){

                              $checked = "";
                              $hidden  = "";
                              $gras    = "";

                              if(in_array($aCheckTypeArticle[$i]['typearticleid'], $maGarantie->types_produits)){

                                   $checked = "checked";
                                   $hidden  = "style=\"visibility:hidden;\"";
                                   $gras    = "style=\"font-weight:bold;\"";
                              }

                              $compteur++;

                              echo "<input type=\"checkbox\" name=\"types_produits[]\" id=\"types_produits_".$compteur."\" value=\"".$aCheckTypeArticle[$i]['typearticleid']."\" ".$hidden." ".$checked." /><span ".$gras.">".$aCheckTypeArticle[$i]['libelle']."</span><br/>";
                         }

                         ?>
                    </td>
               </tr>
          </table>
     </div>
     <!-- FIN ENCART TYPES ARTICLES -->

     <!-- DEBUT ENCART PAYS -->
     <div id="pays" class="contenus">
          <table class="tbl_checkbox">
               <tr>
                    <td><input type="checkbox" onClick="checkAll(this,'pays[]')"><strong>Tout cocher</strong><br/></td>
               </tr>
               <tr>
                    <td>
                         <?php
                         $compteur = 0;
                         $nbMax = $nbPaysCols;
                         for($i=$compteur;$i<$nbMax;$i++){

                              $checked   = "";
                              $checkedRE = "";
                              $checkedRM = "";

                              if(in_array($aCheckPays[$i]['paysid'], $maGarantie->pays)) $checked = "checked";
                              if(in_array("RE", $maGarantie->pays)) $checkedRE = "checked";
                              if(in_array("RM", $maGarantie->pays))  $checkedRM = "checked";

                              $compteur++;

                              echo "<input type=\"checkbox\" name=\"pays[]\" id=\"pays_".$compteur."\" value=\"".$aCheckPays[$i]['paysid']."\" ".$checked." />".$aCheckPays[$i]['libelle']."<br/>";
                         }

                         ?>
                    </td>
                    <td>
                         <?php
                         $nbMax = $nbPaysCols + $compteur;
                         for($i=$compteur;$i<$nbMax;$i++){

                              $checked   = "";
                              $checkedRE = "";
                              $checkedRM = "";

                              if(in_array($aCheckPays[$i]['paysid'], $maGarantie->pays)) $checked = "checked";
                              if(in_array("RE", $maGarantie->pays)) $checkedRE = "checked";
                              if(in_array("RM", $maGarantie->pays))  $checkedRM = "checked";

                              $compteur++;

                              echo "<input type=\"checkbox\" name=\"pays[]\" id=\"pays_".$compteur."\" value=\"".$aCheckPays[$i]['paysid']."\" ".$checked." />".$aCheckPays[$i]['libelle']."<br/>";
                         }

                         ?>
                    </td>
                    <td>
                         <?php
                         $nbMax = $nbPaysCols + $compteur > count($aCheckPays) ? count($aCheckPays) : $nbPaysCols + $compteur;
                         for($i=$compteur;$i<$nbMax;$i++){

                              $checked   = "";
                              $checkedRE = "";
                              $checkedRM = "";

                              if(in_array($aCheckPays[$i]['paysid'], $maGarantie->pays)) $checked = "checked";
                              if(in_array("RE", $maGarantie->pays)) $checkedRE = "checked";
                              if(in_array("RM", $maGarantie->pays))  $checkedRM = "checked";

                              $compteur++;

                              echo "<input type=\"checkbox\" name=\"pays[]\" id=\"pays_".$compteur."\" value=\"".$aCheckPays[$i]['paysid']."\" ".$checked." />".$aCheckPays[$i]['libelle']."<br/>";
                         }

                         ?>
                         <input type="checkbox" name="pays[]" value="RE" id="pays_<?php echo $cptPays+1; ?>" <?php echo $checkedRE; ?>/>Reste de l'Europe<br/>
                         <input type="checkbox" name="pays[]" value="RM" id="pays_<?php echo $cptPays+2; ?>" <?php echo $checkedRM; ?>/>Reste du Monde
                    </td>
               </tr>
          </table>
     </div>
     <!-- FIN ENCART PAYS -->
     
     <p style="width: 600px; text-align: center;"><input type="submit" class="skined" style="width:230px;" value="Enregistrer les modifications"></p>
</form>

<br/><a style="color: #363E42; font-weight: bold; font-size: 13px; margin: 10px 0 0 10px; float: left;" href="garanties.php"><img src="<?php echo BACK_HOST; ?>/images/icon/previous.png" style="vertical-align: -8px; margin: 0 5px 0 0;">Retour à la liste</a>