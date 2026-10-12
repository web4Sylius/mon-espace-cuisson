<?php

$sql = "SELECT artid";
$sql .= " FROM comparateur";
$sql .= "  WHERE userid = ".$_GET['userid'];
$sql .= "  AND remove = 0";
$sql .= "  ORDER BY date";
$comparateur = $DB_site2->query($sql);

if($DB_site2->num_rows($comparateur) > 0){
	$width_prop = $DB_site2->num_rows($comparateur)*200;
?>
<div class="commande_detail" id="utilisateur_comparateur">
     <h2>COMPARATEUR</h2>
		<div class="table_modules_boutiques no_border" style="width:880px;">
          <div style="width:100%; overflow:auto;">
		  <div style="width: <?php echo $width_prop; ?>px;">
					<?php
					
                    while($art=$DB_site2->fetch_array($comparateur)){
                         $tdrow=article_vignette($art['artid'],array(),"comparateur");
						 ?>
                         <div class="proposition" style="width:180px;">
                              <?php echo $tdrow['marque_visuel']; ?>
                              <?php echo $tdrow['img']; ?>
                              <p style=""><?php echo $tdrow['artcode_mcav']; ?></p>
                              <p class="pt">(<?php echo $tdrow['artcode_eurocave']; ?>)</p>
                              <?php if(isset($tdrow['options']) && !empty($tdrow['options'])){ ?>
                                   <a href="javascript:void(0);" class="lien" onclick="affiche_div('options_<?php echo $tdrow['propid']; ?>');">Afficher les options</a>
                                   <div id="options_<?php echo $tdrow['propid']; ?>" class="caracteristiques">
                                        <table cellpadding="0" cellspacing="0" border="0">
                                             <?php  foreach($tdrow['options'] as $option){ ?>
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
                              <div id="carac_<?php echo $tdrow['propid']; ?>" class="caracteristiques" style="display:block;">
                                   <table cellpadding="0" cellspacing="0" border="0">
                                        <?php 
                                        $i = 0;
										foreach($tdrow['rows'] as $key => $caract){
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
                                   <p class="pt"><?php echo $tdrow['infos']; ?></p>
                              </div>
                              <a class="lien lien_fiche" href="<?php echo $tdrow['lien_fiche']; ?>" target="_blank">Sur site</a>
                              <?php echo $tdrow['dispo']; ?>
                              <?php echo $tdrow['files']; ?>
                              <?php echo $tdrow['lockit']; ?>
							  <?php echo $tdrow['spy']; ?>
                         </div>
						<?php
                    }
                    

					
					/*
                    foreach($rows as $row=>$lbl){
					?>
                         <tr>
                              <td><?php echo $lbl; ?></td>
                         <?php foreach($tdrow as $artid=>$rowdata){ ?>
                              <td><?php echo $rowdata[$row]; ?></td>
                         <?php } ?>
                         </tr>
                    <?php
					}
					*/
					?>
			</div>
		</div>
	</div>
	 <div class="clear"></div>
	  <div style="float:right;" class="bouton_dispo">
		<a class="lien lien_fiche" href="javascript:artmaildispo();">Demande de disponibilité</a><br><br>
	  </div><div class="clear"></div>
</div>
<div class="clear"></div>
<?php }