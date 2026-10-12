<?php
  
  include_once("./retif_bis.inc"); //on recupere les infos de l API stocke dans $tabCommande pour les afficher
  include_once("../admin_global.php");
  include_once("../../class/facture.class.php");

?>
 
<p style="text-align: center; margin: 10px 0; padding: 0; font-size: 13px; font-weight: bold;"><?php echo $titre; ?></p>
 <table cellspacing="0" width="800" align="center" style="margin-top:10px;" class="list">
      <tr>
           <td height="30" class="row_title" style="border-bottom: 1px solid #990000;">N° Cde</td>
           <td class="row_title" style="border-bottom: 1px solid #990000;">Date</td>
           <td class="row_title" style="border-bottom: 1px solid #990000;">Réf.AciLyon</td>
           <td class="row_title" style="border-bottom: 1px solid #990000;">Prix (TTC)</br>dont X€ de port</td>
           <td class="row_title" style="border-bottom: 1px solid #990000;">Adr. facturation</td>
           <td class="row_title" style="border-bottom: 1px solid #990000;">Adr. livraison</td>
           <td class="row_title" style="border-bottom: 1px solid #990000;">Etat</td>
           <td class="row_title" style="border-bottom: 1px solid #990000;">Création Commande</td>
      </tr>
      <?php 

      foreach($tabCommande as $la_commande){ 
           $date_commande = (string)$la_commande['commande_date'];
           $tabDate = explode('T', $date_commande);
           $tabHeure = explode('Z', $tabDate[1]);
           $tabTemp = explode('-', $tabDate[0]);
           $date_commande = $tabTemp[2].'/'.$tabTemp[1].'/'.$tabTemp[0].' '.$tabHeure[0];
                 
           $commande_site = $DB_site2->query_first("SELECT * FROM facture WHERE mp_facturid='".$la_commande['commande_id_retif']."'");
          switch($la_commande['commande_statut_libelle']){
                case 'REFUSED':
                case 'CANCELED':
                      $trColor = '#fcbebe';
                      break;
                case 'SHIPPED':
                case 'RECEIVED':
                     $trColor = '#b4dbbc';
                     break;
                case 'SHIPPING':
                     $trColor = '#ffcba0';
                     break;
                case 'Unshipped':
                      $trColor = '#ffcba0';
                      break;
                case 'Pending':
                      $trColor = '#ffcba0';
                      break;
           }
           if($commande_site['facturid']==""){
                $trColor = '#ffcba0';
           }
           $lien_commande = BACK_URL."/facture.php?action=editer&facturid=".$commande_site['facturid'];
           $facture = new facture($DB_site2, $commande_site['facturid']);
           $prix = 0;
           for ($i=0;$i<$la_commande['count'];$i++)
          {
              $prix += (double)$la_commande['prix'][$i]*$la_commande['article_qte'][$i];
          }
          $fdeport = (double)$la_commande['frais_de_port'][0];

      ?>
      
      <tr class="row<?php echo $alt; ?>" style="background-color: <?php echo $trColor; ?>; border-color: black; ">
           <td height="20" style="padding:4px; text-align: center; border-bottom: 1px solid #990000;"><?php echo $la_commande['commande_id_retif']; ?></br><a href="<?php echo($lien_commande) ?>" target="_blank"><?php echo $commande_site['facturid'];?></a></br><?php 
                if ($commande_site['facturid'])
                { 
                   if (round($facture->totalttc,2) == round($prix + floatval($fdeport),2)) 
                    {
                       echo number_format($facture->totalttc,2,',',' ')."€";
                    }
                    else
                    {
                    ?><span style="color:red;">
                      <?php
                        echo $facture->totalttc."€ Attention les prix ne correspondent pas";
                      ?>
                    </span><?php 
                      }
                  }?></td>
           <td style="padding:4px; text-align: center;border-bottom: 1px solid #990000;"><?php echo $date_commande; ?></td>
           <td style="padding:4px; text-align: center;border-bottom: 1px solid #990000;"><?php for ($i=0;$i<$la_commande['count'];$i++){ echo $la_commande['article_qte'][$i].' x '.$la_commande['ref_acilyon'][$i].' ('.$la_commande['prix'][$i].'€)<br>';} ?></td>
           <td style="padding:4px; text-align: center;border-bottom: 1px solid #990000;">
              <?php 
                  $prix_tot = 0;
                  for ($i=0;$i<$la_commande['count'];$i++)
                  {
                    $prix_tot += (double)$la_commande['prix'][$i]*$la_commande['article_qte'][$i];
                  }
                  $fdeport = (double)$la_commande['frais_de_port'][0];
                  $prix_tot += $fdeport + 0;
                  echo number_format($prix_tot,2,',',' ')."€";
                  echo "</br>dont ".$fdeport." € de port";
              ?>
            </td>
           <td style="padding:4px; text-align: center;border-bottom: 1px solid #990000;">
                <?php echo trim($la_commande['facturation_societe']) != '' ? $la_commande['facturation_societe'].'<br>' : ''; ?>
                <?php echo trim($la_commande['facturation_prenom']) != ''  ? $la_commande['facturation_prenom'] : ''; ?>
                <?php echo trim($la_commande['facturation_nom']) != '' ? $la_commande['facturation_nom'] : ''; ?>
                <?php echo trim($la_commande['facturation_adresse1']) != '' ? '<br>'.$la_commande['facturation_adresse1'] : ''; ?>
                <?php echo trim($la_commande['facturation_adresse2']) != '' ? '<br>'.$la_commande['facturation_adresse2'] : ''; ?>
                <?php echo trim($la_commande['facturation_cp']) != '' || trim($la_commande['facturation_ville']) != '' ? '<br>'.$la_commande['facturation_cp'].' '.$la_commande['facturation_ville'] : ''; ?>
                <?php echo trim($la_commande['facturation_pays']) != '' ? '<br>'.$la_commande['facturation_pays'] : ''; ?>
                <?php echo trim($la_commande['facturation_tel_domicile']) != '' ? '<br>'.$la_commande['facturation_tel_domicile'] : ''; ?>     
           </td>
           <td style="padding:4px; text-align: center;border-bottom: 1px solid #990000;">
                <?php echo trim($la_commande['livraison_societe']) != '' ? $la_commande['livraison_societe'].'<br>' : ''; ?>
                <?php echo trim($la_commande['livraison_prenom']) != ''  ? $la_commande['livraison_prenom'] : ''; ?>
                <?php echo trim($la_commande['livraison_nom']) != '' ? $la_commande['livraison_nom'] : ''; ?>
                <?php echo trim($la_commande['livraison_adresse1']) != '' ? '<br>'.$la_commande['livraison_adresse1'] : ''; ?>
                <?php echo trim($la_commande['livraison_adresse2']) != '' ? '<br>'.$la_commande['livraison_adresse2'] : ''; ?>
                <?php echo trim($la_commande['livraison_cp']) != '' || trim($la_commande['livraison_ville']) != '' ? '<br>'.$la_commande['livraison_cp'].' '.$la_commande['livraison_ville'] : ''; ?>
                <?php echo trim($la_commande['livraison_pays']) != '' ? '<br>'.$la_commande['livraison_pays'] : ''; ?>
                <?php echo trim($la_commande['livraison_tel_domicile']) != '' ? '<br>'.$la_commande['livraison_tel_domicile'] : ''; ?>    
           </td>
           <td style="padding:4px; text-align: center;border-bottom: 1px solid #990000;"><?php echo $la_commande['commande_statut_libelle']; ?></td>
           <td style="padding:4px; text-align: center;border-bottom: 1px solid #990000;">
        <?php

            $facturation_adresse = ''; if ($la_commande['facturation_adresse1'] != ''){$facturation_adresse .= $la_commande['facturation_adresse1'];} if ($la_commande['facturation_adresse2'] != ''){$facturation_adresse .= " ".$la_commande['facturation_adresse2'];}
            $livraison_adresse = ''; if ($la_commande['livraison_adresse1'] != ''){$livraison_adresse .= $la_commande['livraison_adresse1'];} if ($la_commande['livraison_adresse2'] != ''){$livraison_adresse .= " ".$la_commande['livraison_adresse2'];}

              $nom=strtoupper($la_commande['facturation_nom']);
              $nom_livraison=strtoupper($la_commande['livraison_nom']);
              
              $articles="";
              for ($i=0;$i<$la_commande['count'];$i++){
                    $qte=$la_commande['article_qte'][$i];
                    $ref=$la_commande['ref_acilyon'][$i];
                    $prix=$la_commande['prix'][$i];
                    
                    $articles.=<<<HTML
                        <input type="hidden" name="qte_art$i" value="$qte">
                        <input type="hidden" name="nom_art$i" value="$ref"> 
                        <input type="hidden" name="prix_art$i" value="$prix">
HTML;
              }
              $creer=<<<HTML
              <form action="index.php?create=1" method="post" id="formAuto">
                <input type="hidden" name="id_commande" value="$la_commande[commande_id_retif]">
                <input type="hidden" name="date_commande" value="$date_commande">
                <input type="hidden" name="market_place" value="retif">
                <input type="hidden" name="market_place_id" value="22">

                <input type="hidden" name="facturation_societe" value="$la_commande[facturation_societe]">
                <input type="hidden" name="facturation_prenom" value="$la_commande[facturation_prenom]">
                <input type="hidden" name="facturation_nom" value="$nom">
                <input type="hidden" name="facturation_adresse" value="$facturation_adresse">
                <input type="hidden" name="facturation_cp" value="$la_commande[facturation_cp]">
                <input type="hidden" name="facturation_ville" value="$la_commande[facturation_ville]">
                <input type="hidden" name="facturation_pays" value="$la_commande[facturation_pays]">
                <input type="hidden" name="facturation_tel" value="$la_commande[livraison_tel_domicile]">
                
                <input type="hidden" name="livraison_societe" value="$la_commande[livraison_societe]">
                <input type="hidden" name="livraison_prenom" value="$la_commande[livraison_prenom]">
                <input type="hidden" name="livraison_nom" value="$nom_livraison">
                <input type="hidden" name="livraison_adresse" value="$livraison_adresse">
                <input type="hidden" name="livraison_cp" value="$la_commande[livraison_cp]">
                <input type="hidden" name="livraison_ville" value="$la_commande[livraison_ville]">
                <input type="hidden" name="livraison_pays" value="$la_commande[livraison_pays]">
                <input type="hidden" name="livraison_tel" value="$la_commande[livraison_tel_domicile]">

                <input type="hidden" name="nbr_produit" value="$la_commande[count]">
                <input type="hidden" name="commission" value="$la_commande[commission]">
                $articles  
                <input type="submit" value="Créer">
                </form>
HTML;

                    if($commande_site['facturid']==""){
                        print $creer;
                    }
            
            print "<br><b>".$infoimportante."</b>";

            ?>
           </td>
      </tr>
      <?php 
           $alt++;
           if($alt == 2) $alt = 0;
      } 
      ?>
 </table>
 <?php
 function formatValue($val){
      $tabTemp = explode('.',$val);
      return $tabTemp[0];
 }
 ?>
