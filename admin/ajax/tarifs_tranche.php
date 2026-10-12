<?php
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");
require_once($_SERVER['DOCUMENT_ROOT']."/class/garantie.class.php");

if(isset($_GET['action']) && isset($_GET['garantie_id'])){
     
     $garantieid = $_GET['garantie_id'];
     
     $objGarantie = new garantie($DB_site2, null, 'FR', $garantieid);
     
     switch($_GET['action']){
          
          case 'add':
               
               $objGarantie->addTranche($_GET);
               
               break;
          case 'delete':
               
               $objGarantie->deleteTranche($_GET);
               
               break;
     }
}

//Récupération des tranches de prix de la garantie
$sql = "SELECT *";
$sql .= " FROM garantie_tranche";
$sql .= " WHERE garantieid = ".$garantieid;
$sql .= " ORDER BY garantie_tranche_prix_mini ASC";
$req = $DB_site2->query($sql);
$tabTranches = array();
while($row = $DB_site2->fetch_array($req)){
     $tabTranches[] = $row;
}
?>

<table class="table_tarifs">
     <tr>
          <td class="titre">Tranche(s)</td>
          <td class="titre">Prix d'achat<br>HT</td>
          <td class="titre">Prix de vente<br>TTC</td>
          <td colspan="2" class="titre">PPI<br>TTC</td>                  
     </tr>
     <?php 
     $compteur = 0;
     foreach($tabTranches as $la_tranche){ 
          $class_td = $compteur%2 == 0 ? 'td_alt1' : '';
          
          $aPrix = @unserialize($la_tranche['garantie_tranche_prix']);
          $aPPI  = @unserialize($la_tranche['garantie_tranche_ppi']);
          
          if($aPrix === false) $aPrix['base'] = $la_tranche['garantie_tranche_prix'];
          if($aPPI === false) $aPPI['base']  = $la_tranche['garantie_tranche_ppi'];
     ?>
          <tr>
               <td class="<?php echo $class_td; ?> tranche">De <span><?php echo number_format($la_tranche['garantie_tranche_prix_mini'],2,',',' '); ?> &euro;</span> à <span><?php echo number_format($la_tranche['garantie_tranche_prix_maxi'],2,',',' '); ?> &euro;</span></td>
               <td class="<?php echo $class_td; ?>"><input type="text" name="paht[<?php echo $la_tranche['garantie_tranche_id']; ?>]" value="<?php echo $la_tranche['garantie_tranche_paht']; ?>"> &euro;</td>
               <td class="<?php echo $class_td; ?>">
                    <table cellspacing="0" cellpadding="0" width="100%">
                         <?php foreach($aPrix as $site_code => $valeur){ ?>
                         <tr>
                              <td style="border: none !important;"><?php echo $site_code; ?></td>
                              <td style="border: none !important;"><input type="text" name="prix[<?php echo $la_tranche['garantie_tranche_id']; ?>][<?php echo $site_code; ?>]" value="<?php echo $valeur; ?>"></td>
                         </tr>
                         <?php } ?>
                    </table>
               </td>
               <td class="<?php echo $class_td; ?>">
                    <table cellspacing="0" cellpadding="0" width="100%">
                         <?php foreach($aPPI as $site_code => $valeur){ ?>
                         <tr>
                              <td style="border: none !important;"><?php echo $site_code; ?></td>
                              <td style="border: none !important;"><input type="text" name="ppi[<?php echo $la_tranche['garantie_tranche_id']; ?>][<?php echo $site_code; ?>]" value="<?php echo $valeur; ?>"></td>
                         </tr>
                         <?php } ?>
                    </table>
               </td>
               <td class="<?php echo $class_td; ?>"><img src="images/icon/delete.png" style="cursor: pointer;" onclick="deleteTranche(<?php echo $la_tranche['garantie_tranche_id']; ?>)"></td>
          </tr>
     <?php 
          $compteur++;
     }
     ?>
     <tr>
          <td>&nbsp;</td>
     </tr>
     <tr>
          <td colspan="5" height="40" class="titre"><img class="add" src="images/icon/edit_add.png">Nouvelle tranche</td>
     </tr>
     <tr>
          <td class="td_alt1">De <input type="text" class="input_gd" id="prix_mini_tranche" value=""> &euro; à <input type="text" class="input_gd" id="prix_maxi_tranche" value=""> &euro;</td>
          <td class="td_alt1"><input type="text" id="paht_tranche[0]" value=""> &euro;</td>
          <td class="td_alt1">
               <table cellspacing="0" cellpadding="0" width="100%">
                    <tr>
                         <td style="border: none !important;">base</td>
                         <td style="border: none !important;"><input type="text" name="prix_tranche[0][base]" value=""> ¤</td>
                    </tr>
                    <tr>
                         <td style="border: none !important;">uk</td>
                         <td style="border: none !important;"><input type="text" name="prix_tranche[0][uk]" value=""> £</td>
                    </tr>
               </table>
          </td>
          <td class="td_alt1">
               <table cellspacing="0" cellpadding="0" width="100%">
                    <tr>
                         <td style="border: none !important;">base</td>
                         <td style="border: none !important;"><input type="text" name="ppi_tranche[0][base]" value=""> ¤</td>
                    </tr>
                    <tr>
                         <td style="border: none !important;">uk</td>
                         <td style="border: none !important;"><input type="text" name="ppi_tranche[0][uk]" value=""> £</td>
                    </tr>
               </table>
          <td class="td_alt1">&nbsp;</td>
     </tr>
     <tr>
          <td colspan="5"><input type="button" class="skined" style="width: 100px;" value="Ajouter" onclick="addTranche();"></td>
     </tr>
</table>