<?php

$sql = "SELECT configurateurid";
$sql .= " FROM configurateur";
$sql .= " WHERE userid = ".$_GET['userid'];
$sql .= " ORDER BY date DESC";
$configurateurs = $DB_site2->query($sql);


if($DB_site2->num_rows($configurateurs) > 0){  
?>
<div class="commande_detail" id="utilisateur_configurateur">
     <h2>CONFIGURATIONS</h2>
     <div class="table_modules_boutiques no_border" style="width:880px;">
	 <?php	    
     $first_conf=0;
     while($configurateurid = $DB_site2->fetch_array($configurateurs)){

          $configurateur = new configurateur($DB_site2,$configurateurid[0]);
          $faitpar = ($configurateur->commercial!=""?$configurateur->commercial:"le client");

		  ?>
          <p style="display:inline"><img src="<?php if($first_conf==0) {echo 'images/moins.jpg'; } else { echo 'images/plus.jpg';}?>" id="configplus<?php echo $configurateur->configurateurid; ?>" align="absmiddle" onclick="show_config_info(<?php echo $configurateur->configurateurid; ?>)"> <?php echo date("d/m/Y H:i", $configurateur->date); ?> fait par <?php echo $faitpar; ?>
          <?php
          $projet=$DB_site2->query_first("SELECT projetid FROM projet WHERE configs LIKE '%\"".$configurateur->configurateurid."\"%' AND userid = ".$_GET['userid']);
       if(isset($projet['projetid'])) {
          ?>
          <form method="post" style="display:inline">
         <input type='hidden' value='delier-projet-config' name='action'/>
         <input type='hidden' value='<?php echo $projet['projetid'];?>' name='projetid_config'/>
         <input type='hidden' value="<?php echo $configurateur->configurateurid;?>" name='configid'/>
          <input type="submit" name="delier_conf" value="enlever du projet <?php echo $projet['projetid'];?>" class="conf_projet"><?php } else {
                $projets=$DB_site2->query("SELECT * FROM projet WHERE userid = ".$_GET['userid']." AND etatid IN (2, 3, 4, 5, 6, 7, 8)");
                if($DB_site2->num_rows($projets) > 0){ ?>
 
          <form method="post" style="display:inline">
         <input type='hidden' value='lier-projet-config' name='action'/>
         <input type='hidden' value="<?php echo $configurateur->configurateurid;?>" name='configid'/>
         <select name="liste_projets" style="width: 110px">
          <option value="">-- Projets --</option>";
          <?php
         
          while($projet=$DB_site2->fetch_array($projets)){
            echo "<option value=".$projet['projetid'].">PJT du ".date('d/m/Y', $projet['dateline'])." n°".$projet['projetid']." - " .substr(UCfirst($projet['libelle']), 0, 60)."... - ".$projet['commercial']."</option>";
          }
          ?>
        </select>
        <input type="submit" name="lier_conf" value="Lier la config au projet" class="conf_projet"/>
      </form>
 <?php } else {  ?>
     <br/>
  <?php } } ?>
     </p>
          <table id="configinfo<?php echo $configurateur->configurateurid; ?>" class="tab_client_conf" style="display:<?php if($first_conf==0) {echo 'block'; } else { echo 'none';}?>">
          <tr><td>
		  <?php
          foreach($configurateur->reponses() as $nb => $reponse){
               $infos = $reponse->infos();
               if($infos!=""){
				   print "<b>".$reponse->type . " :</b> " . $infos."<br>";
			   }
          }
		  ?>
          </td><td colspan='3'><div style='overflow-x:auto;white-space: nowrap;background-color:#EBEBEB;max-width:600px;'>
		  <?php
          $tdrow = array();
          if(is_array($configurateur->artids) && count($configurateur->artids) > 0){
			   //$width_prop = count($configurateur->artids)*200;
                    foreach($configurateur->artids as $configartid){
						$configs=article_vignette($configartid,array( ),"configurateur" );
						?>
                         <div class="vignettes_client_config" style="width:180px;vertical-align:top;">
                              <?php echo $configs['marque_visuel']; ?>
                              <?php echo $configs['img']; ?>
                              <p style=""><?php echo $configs['artcode_mcav']; ?></p>
                              <p class="pt">(<?php echo $configs['artcode_eurocave']; ?>)</p>
                              <?php if(isset($configs['options']) && !empty($configs['options'])){ ?>
                                   <a href="javascript:void(0);" class="lien" onclick="affiche_div('options_<?php echo $configs['propid']; ?>');">Afficher les options</a>
                                   <div id="options_<?php echo $configs['propid']; ?>" class="caracteristiques">
                                        <table cellpadding="0" cellspacing="0" border="0">
                                             <?php  foreach($configs['options'] as $option){ ?>
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
                              <div id="carac_<?php echo $configs['propid']; ?>" class="caracteristiques" style="display:block;">
                                   <table cellpadding="0" cellspacing="0" border="0">
                                        <?php 
                                        $i = 0;
										foreach($configs['rows'] as $key => $caract){
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
                                   <p class="pt"><?php echo $configs['infos']; ?></p>
                              </div>
                              <a class="lien lien_fiche" href="<?php echo $configs['lien_fiche']; ?>" target="_blank">Sur site</a>
                              <?php echo $configs['dispo']; ?>
                              <?php echo $configs['files']; ?>
                              <?php echo $configs['lockit']; ?>
                              <?php echo $configs['suppr']; ?>
							  <?php echo $configs['spy']; ?>
                         </div>
						<?php
                    } 
                   
			   
			   /*
               foreach($configurateur->artids as $configartid){
				   $tdrow[$configartid]=article_vignette($configartid,array());
			   }
			   ?>
               <div style="width:100%; overflow:auto;">
                    <table>
			   <?php
                    foreach($rows as $row => $lbl){
					?>
                         <tr>
                              <td><?php echo $lbl; ?></td>
						 <?php
                         foreach($tdrow as $configartid => $rowdata){
                              ?><td><?php echo $rowdata[$row]; ?></td><?php
                         }
						 ?>
                         </tr>
					<?php
                    }
			   ?>
                    </table>
               </div>
		  <?php
				*/
          } else {
               ?><?php
          }
          ?>
		  </div>
          </td>
     </tr>
     </table>
		  <?php
            $first_conf++;
     }
?>

	<div class="clear"></div>
	  <div style="float:right;" class="bouton_dispo">
		<a class="lien lien_fiche" href="javascript:artmaildispo();">Demande de disponibilité</a>
	  </div><div class="clear"></div>
     </div>
</div>
<div class="clear"></div>
<?php
}
?>