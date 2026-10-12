<?php
  include_once("./cdiscount_bis.inc"); //on recupere les donnees de $tabCommande_cdiscount e on les affiche
  include_once("../admin_global.php");
  include_once("../../class/facture.class.php");
  $alt = 0;
  if ($pasdispo == 1)
  {
     echo "<p style=\"color:red;font-size:15px\">Cdiscount est momentanément indisponible<p>";
  }
  else
  {
?>

<p style="text-align: center; margin: 10px 0; padding: 0; font-size: 13px; font-weight: bold;"><?php echo $titre; ?></p>
 <table cellspacing="0" width="800" align="center" style="margin-top:10px;" class="list">
      <tr>
           <td height="30" class="row_title" style="border-bottom: 1px solid #990000;">N° Cde</td>
           <td class="row_title" style="border-bottom: 1px solid #990000;">Date</td>
           <td class="row_title" style="border-bottom: 1px solid #990000;">Réf. article(s)</td>
           <td class="row_title" style="border-bottom: 1px solid #990000;">Prix Commande(TTC)</br>dont X€ de port</td>
           <td class="row_title" style="border-bottom: 1px solid #990000;">Adr. facturation</td>
           <td class="row_title" style="border-bottom: 1px solid #990000;">Adr. livraison</td>
           <td class="row_title" style="border-bottom: 1px solid #990000;">Etat</td>
           <td class="row_title" style="border-bottom: 1px solid #990000;">Création Commande</td>
      </tr>
      <?php 
      foreach($tabCommande_cdiscount as $la_commande){ 
           $date_commande = (string)$la_commande['commande_date'];
           $tabDate = explode('T', $date_commande);
           $tabHeure = explode('.', $tabDate[1]);
           $tabTemp = explode('-', $tabDate[0]);
           $date_commande = $tabTemp[2].'/'.$tabTemp[1].'/'.$tabTemp[0].' '.$tabHeure[0];
           $commande_site = $DB_site2->query_first("SELECT * FROM facture WHERE mp_facturid='".$la_commande['commande_id_cdiscount']."'");
           switch($la_commande['commande_statut_libelle']){
                case 'CancelledByCustomer':
                     $trColor = '#fcbebe';
                     break;
                case 'ShipmentRefusedBySeller':
                     $trColor = '#fcbebe';
                     break;
                case 'RefusedBySeller':
                     $trColor = '#fcbebe';
                     break;
                case 'Shipped':
                     $trColor = '#b4dbbc';
                     break;
                case 'WaitingForShipmentAcceptation':
                     $trColor = '#ffcba0';
                     break;
           }

           $lien_commande = BACK_URL."/facture.php?action=editer&facturid=".$commande_site['facturid'];
           $facture = new facture($DB_site2, $commande_site['facturid']);
           $prix = 0;
          for($i=0;$i<$la_commande['count'];$i++)
          { 
              $prix_article = (double)$la_commande['prix_article'][$i]; 
              $prix += $prix_article*$la_commande['article_qte'][$i];
                   
          }
          $prix_tot += (double)$prix_fdeport;
          if ($la_commande['INTERETBCA']  != '')
          {
              $prix += (double)$la_commande['INTERETBCA'];
          }

          if($la_commande['livraison_prenom'] != '')
          {
              $facturation_nom    = $la_commande['facturation_nom']; 
              $facturation_prenom = $la_commande['facturation_prenom'];
              $livraison_nom      = $la_commande['livraison_nom']; 
              $livraison_prenom   = $la_commande['livraison_prenom']; 
          }
          else
          {
              $facturation        = explode(' ', $la_commande['facturation_prenom_nom']); 
              $facturation_nom    = $facturation[1]; 
              $facturation_prenom = $facturation[0];
              
              $livraison          = explode(' ', $la_commande['livraison_prenom_nom']); 
              $livraison_nom      = $livraison[1]; 
              $livraison_prenom   = $livraison[0]; 
          }

        ?>
      <tr class="row<?php echo $alt; ?>" style="background-color: <?php echo $trColor; ?>; border-color: black;">
           <td height="20" style="padding:4px; text-align: center;border-bottom: 1px solid #990000;"><?php echo $la_commande['commande_id_cdiscount']; ?></br><a href="<?php echo($lien_commande) ?>" target="_blank"><?php echo $commande_site['facturid'];?></a></br><?php 
                if ($commande_site['facturid'])
                {
                   if (round($facture->totalttc,2) == round($prix + floatval($la_commande['shippingcharges']),2))
                    {
                       echo number_format($facture->totalttc,2,',',' ')."€";
                    }
                    else
                    {
                    ?><span style="color:red;">
                      <?php echo $facture->totalttc."€ Attention les prix ne correspondent pas"; ?>
                    </span><?php
                      }
                  }?></td>
           <td style="padding:4px; text-align: center;border-bottom: 1px solid #990000;"><?php echo $date_commande; ?></td>
           <td style="padding:4px; text-align: center;border-bottom: 1px solid #990000;">
              <?php 
                  for($i=0;$i<$la_commande['count'];$i++)
                    {
                      echo $la_commande['article_qte'][$i].' x '.$la_commande['article_ref_acilyon'][$i]." "; 
                      $prix_article = $la_commande['prix_article'][$i]; 
                      $prix = $prix_article + 0; 
                      echo "(".number_format($prix,2,',',' ')."€)".'</br>';
                    } 
              ?>
          </td>
           <td style="padding:4px; text-align: center;border-bottom: 1px solid #990000;">
              <?php 
                  $prix_tot = 0; 

                  $prix_fdeport = floatval($la_commande['shippingcharges']);
                  for($i=0;$i<$la_commande['count'];$i++)
                  { 
                    $prix_article = (double)$la_commande['prix_article'][$i]; 
                    $prix_tot += $prix_article*$la_commande['article_qte'][$i];
                  }
                  $prix_tot += (double)$prix_fdeport;
                  if ($la_commande['INTERETBCA']  != '')
                  {
                    $prix_tot += (double)$la_commande['INTERETBCA'];
                  }
                  echo number_format($prix_tot,2,',',' ')."€";
                  echo "</br>dont ".number_format($prix_fdeport,2,',',' ')." € de port";
                  if ($la_commande['INTERETBCA']  != '')
                  {
                    echo "</br>dont ".$la_commande['INTERETBCA']." € de charges INTERETBCA";
                  }
              ?>
          </td>
           <td style="padding:4px; text-align: center;border-bottom: 1px solid #990000;">
                <?php echo trim($la_commande['facturation_societe']) != '' ? $la_commande['facturation_societe'] : ''; ?>
                <?php echo trim($la_commande['facturation_prenom_nom']) != '' ? $la_commande['facturation_prenom_nom'] : ''; ?>
                <?php echo trim($la_commande['facturation_adresse']) != '' ? '<br>'.$la_commande['facturation_adresse'] : ''; ?>
                <?php echo trim($la_commande['facturation_cp']) != '' || trim($la_commande['facturation_ville']) != '' ? '<br>'.$la_commande['facturation_cp'].' '.$la_commande['facturation_ville'] : ''; ?>
                <?php echo trim($la_commande['facturation_pays']) != '' ? '<br>'.$la_commande['facturation_pays'] : ''; ?>
                <?php echo trim($la_commande['facturation_mobile']) != '' ? '<br>'.$la_commande['facturation_mobile'] : ''; ?>
                <?php echo trim($la_commande['facturation_domicile']) != '' ? '<br>'.$la_commande['facturation_domicile'] : ''; ?> 
           </td>
           <td style="padding:4px; text-align: center;border-bottom: 1px solid #990000;">
                <?php echo trim($la_commande['livraison_societe']) != '' ? $la_commande['livraison_societe'] : ''; ?>
                <?php echo trim($la_commande['livraison_prenom_nom']) != '' ? $la_commande['livraison_prenom_nom'] : ''; ?>
                <?php echo trim($la_commande['livraison_adresse']) != '' ? '<br>'.$la_commande['livraison_adresse'] : ''; ?>
                <?php echo trim($la_commande['livraison_cp']) != '' || trim($la_commande['livraison_ville']) != '' ? '<br>'.$la_commande['livraison_cp'].' '.$la_commande['livraison_ville'] : ''; ?>
                <?php echo trim($la_commande['livraison_pays']) != '' ? '<br>'.$la_commande['livraison_pays'] : ''; ?>
           </td>
           <?php
               if ($la_commande['facturation_mobile'] == ""){$facturation_tel = $la_commande['facturation_domicile'];} else {$facturation_tel = $la_commande['facturation_mobile'];}
           ?>
           <td style="padding:4px; text-align: center;border-bottom: 1px solid #990000;"><?php echo $la_commande['commande_statut_libelle']; ?></td>
           <td style="padding:4px; text-align: center;border-bottom: 1px solid #990000;">
              <?php
              
                    $nom=strtoupper($facturation_nom);
                    $nom_livraison=strtoupper($livraison_nom);
                    
                    $interetbca="";
                    if ($la_commande['INTERETBCA']  != ''){
                        $interetbca='<input type="hidden" name="interetbca" value="'.$la_commande['INTERETBCA'].'">';
                    }
                    
                    for ($i=0;$i<$la_commande['count'];$i++){
                        $articles.=<<<HTML
                              <input type="hidden" name="qte_art$i" value="{$la_commande[article_qte][$i]}">
                              <input type="hidden" name="nom_art$i" value="{$la_commande[article_ref_acilyon][$i]}"> 
                              <input type="hidden" name="prix_art$i" value="{$la_commande[prix_article][$i]}">
HTML;
                    } 
              
                    $creer=<<<HTML
                        <form action="index.php?create=1" method="post" id="formAuto">
                        <input type="hidden" name="id_commande" value="$la_commande[commande_id_cdiscount]">
                        <input type="hidden" name="date_commande" value="$date_commande">
                        <input type="hidden" name="market_place" value="CDiscount">
                        <input type="hidden" name="market_place_id" value="13">

                        <input type="hidden" name="facturation_societe" value="$la_commande[facturation_societe]">
                        <input type="hidden" name="facturation_prenom" value="$facturation_prenom">
                        <input type="hidden" name="facturation_nom" value="$nom">
                        <input type="hidden" name="facturation_adresse" value="$la_commande[facturation_adresse]">
                        <input type="hidden" name="facturation_cp" value="$la_commande[facturation_cp]">
                        <input type="hidden" name="facturation_ville" value="$la_commande[facturation_ville]">
                        <input type="hidden" name="facturation_pays" value="$la_commande[facturation_pays]">
                        <input type="hidden" name="facturation_tel" value="$facturation_tel">

                        <input type="hidden" name="livraison_societe" value="$la_commande[livraison_societe]">
                        <input type="hidden" name="livraison_prenom" value="$livraison_prenom">
                        <input type="hidden" name="livraison_nom" value="$nom_livraison">
                        <input type="hidden" name="livraison_adresse" value="$la_commande[livraison_adresse]">
                        <input type="hidden" name="livraison_cp" value="$la_commande[livraison_cp]">
                        <input type="hidden" name="livraison_ville" value="$la_commande[livraison_ville]">
                        <input type="hidden" name="livraison_pays" value="$la_commande[livraison_pays]">
                        <input type="hidden" name="livraison_fixe" value="$la_commande[livraison_tel_mobile]">
                        <input type="hidden" name="livraison_mobile" value="$la_commande[livraison_tel_domicile]">
                        <input type="hidden" name="livraison_tel" value="$facturation_tel">

                        <input type="hidden" name="frais_de_port" value="$prix_fdeport">
                        <input type="hidden" name="nbr_produit" value="$la_commande[count]">
                        <input type="hidden" name="commission" value="$la_commande[commission]">
                        $interetbca
                        $articles
                        <input type="submit" value="Créer">
                      </form>
HTML;
              
                    switch($la_commande['commande_statut_libelle']){
                        case "WaitingForShipmentAcceptation":
                        if ($commande_site['facturid'] == "")
                            print $creer;
                        else
                            print "Commande à accepter sur CDiscount";
                        break;
                        
                        case "PaymentInProgress":
                        if ($commande_site['facturid'] == "")
                            print $creer;
                        else
                            print "Paiement en cours sur CDiscount";
                        break;
                        
                        case "WaitingForShipmentAcceptation":
                        if ($commande_site['facturid'] == "")
                            print $creer;
                        else
                            print "Commande à expédier sur CDiscount";
                        break;
                        
                        case "Shipped":
                        print "Commande expédiée sur CDiscount";
                        break;
                        
                        case "RefusedBySeller":
                        case "PaymentRefused":
                        case "ShipmentRefusedBySeller":
                        case "RefusedNoShipment":
                        case "AutomaticCancellation":
                        case "CancelledByCustomer":
                        if ($commande_site['facturid'] != ""){
                            if(!in_array($facture->valide,array(2,3))){
                                echo "Commande à annuler";
                            }else{
                                echo "Commande annulée";
                            }
                        }
                        break;
                    }
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
}
 function formatValue($val){
      $tabTemp = explode('.',$val);
      return $tabTemp[0];
 }