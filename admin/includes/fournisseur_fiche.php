<?php
$aEtat = array(
     1 => 'green',
     2 => 'red',
     3 => 'blue'
);

?>
<script type="text/javascript">
     function valide_check(obj){
          if(obj.checked){
               update_admin_ajax(1);
          } else {
               update_admin_ajax(0);
          }
     }
     
     function update_admin_ajax(value){
          ajaxrequest('ajax/maj_utilisateur.php?champs=utilisateur_admin&value='+value+'&userid=<?php echo $utilisateur['userid']; ?>');
     }
</script>
<div class="commande_detail">
     <table class="table_modules_boutiques no_border">
          <tr>
               <td>
                    <h2><?php echo $fournisseur_nom; ?></h2>
               </td>
          </tr>
          <tr>
               <td align="center">
                    <table class="fourn_detail">
                         <tr>
                              <td class="titre">Type</td>
                              <td class="titre">Etat</td>
                              <td class="titre">Langue</td>
                              <td class="titre">Devise</td>
                              <td class="titre">Compte client</td>
                              <td class="titre">N° TVA intra.</td>
                              <td class="titre">Code fournisseur</td>
                              <td class="titre">Comptes de charge</td>
                              <td class="titre">Code analytique</td>
                              <td class="titre">Activité</td>
                              <td class="titre"></td>
                         </tr>
                         <tr>
                              <td><?php echo ucfirst(str_replace('é','e',$conditions['types'])); ?></td>
                              <td <?php if ($conditions['fourn_admin']==1) echo " style=\"background-color:yellow\""?>>
                                   <?php if(!empty($conditions['activite'])){ ?><img src="images/icon/<?php echo $aEtat[$conditions['activite']]; ?>.png" width="18"><?php } ?>
                              </td>
                              <td>
                                   <?php if($conditions['langue'] != "") echo "<img src=\"images/drapeaux/br/".$conditions['langue'].".gif\">"; ?>
                              </td>
                              <td><?php echo $conditions['devise']; ?></td>
                              <td><?php echo $conditions['compte_client']; ?></td>
                              <td><?php echo $conditions['tvaintra']; ?></td>
                              <td><?php echo $code411; ?></td>
                              <td><?=implode("<br>",$comptescharge); ?></td>
                              <td><?php echo $analytiq; ?></td>
                              <td><?php echo $conditions['pdts_vendus']; ?></td>
                              <td><img style="cursor: pointer;" src="images/icon/edit.png" onclick="if(document.forms.form_infos.style.display != 'block'){ document.forms.form_infos.style.display='block'; } else { document.forms.form_infos.style.display='none'; }"></td>
                         </tr>
                    </table>
               </td>
          </tr>
     </table>
</div>
<div class="clear"></div>
