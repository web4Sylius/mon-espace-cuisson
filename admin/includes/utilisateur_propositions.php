<?php

$props = array();

$sql = "SELECT * FROM user_prop WHERE userid = '".$utilisateur['userid']."' ORDER BY date DESC";
$req = $DB_site2->query($sql);
while($art = $DB_site2->fetch_array($req)) {
	$props[$art['artid']]=article_vignette($art['artid'],array("propid" => $art['id'], "caractvalid" => $art['caractvalid']), "propositions" );
}
$user_site_code=empty($utilisateur['site_code'])?"base":$utilisateur['site_code'];
$width_prop = count($props)*200;
?>
<a name="propositions_articles"></a>
<div class="commande_detail" id="utilisateur_propositions">
     <h2>PROPOSITIONS D'ARTICLES</h2>
     <div class="table_modules_boutiques no_border prop_article">
          <div class="overflow_auto">
               <div style="width: <?php echo $width_prop; ?>px;">
                    <?php 
                    foreach($props as $prop){
						?>
                         <div class="proposition" style="width:180px;">
                              <?php echo $prop['marque_visuel']; ?>
                              <?php echo $prop['img']; ?>
                              <p style=""><?php echo $prop['artcode_mcav']; ?></p>
                              <p class="pt">(<?php echo $prop['artcode_eurocave']; ?>)</p>
                              <?php if(isset($prop['options']) && !empty($prop['options'])){ ?>
                                   <a href="javascript:void(0);" class="lien" onclick="affiche_div('options_<?php echo $prop['propid']; ?>');">Afficher les options</a>
                                   <div id="options_<?php echo $prop['propid']; ?>" class="caracteristiques">
                                        <table cellpadding="0" cellspacing="0" border="0">
                                             <?php  foreach($prop['options'] as $option){ ?>
                                                  <tr>
                                                       <td class="td_intitule1 center"><strong><?php echo $option['caracteristique']; ?></strong></td>
                                                  </tr>
                                                  <tr>
                                                       <td class="td_alt1 center"><?php echo $option['libelle']; ?> <?php echo $option['differenceprix']; ?> €</td>
                                                  </tr>
                                             <?php } ?>
                                        </table>
                                   </div>
                              <?php } else { ?>
                                   <p class="pt rouge">Sans options</p>
                              <?php } ?>
                              <div id="carac_<?php echo $prop['propid']; ?>" class="caracteristiques" style="display:block;">
                                   <table cellpadding="0" cellspacing="0" border="0">
                                        <?php 
                                        $i = 0;
                                        foreach($prop['rows'] as $key => $caract){
                                            $classtd1 = $i%2 == 0 ? 'td_intitule1' : 'td_intitule2';
                                            $classtd2 = $i%2 == 0 ? 'td_alt1' : 'td_alt2';
											?>
                                               <tr>
                                                  <td class="<?php echo $classtd1; ?>"><?php echo $key; ?></td>
                                                  <td class="<?php echo $classtd2; ?> center" nowrap><?php echo $caract; ?></td>
                                               </tr>
											<?php
                                            $i++;
                                        }
                                        ?>
                                   </table>
                                   <p class="pt"><?php echo $prop['infos']; ?></p>
                              </div>
                              <a class="lien lien_fiche" href="<?php echo $prop['lien_fiche']; ?>" target="_blank">Sur site</a>
                              <?php echo $prop['dispo']; ?>
                              <?php echo $prop['files']; ?>
                              <?php echo $prop['lockit']; ?>
                              <?php echo $prop['suppr']; ?>
							  <?php echo $prop['spy']; ?>
                         </div>
						<?php
                    } 
                    ?>
               </div>
          </div>
          <div class="clear"></div>
          Ajouter une référence : <input type="text" onkeyup="srcsimilaire(this.value,'<?=$user_site_code?>');">
		  <div style="float:right;" class="bouton_dispo">
			<a class="lien lien_fiche" href="javascript:artmaildispo();">Demande de disponibilité</a>
		  </div><div class="clear"></div>
          <div id="results"></div>
     </div>
</div>
<div class="clear"></div>
