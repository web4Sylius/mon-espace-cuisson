<?php
  include_once("./laredoute_bis.inc"); //on recupere les donnees de $tabCommande_laedoute e on les affiche
  include_once("../admin_global.php");
  include_once("../../class/facture.class.php");

  if ($pasdispo == 1)
  {
     echo "<p style=\"color:red;font-size:15px\">LaRedoute est momentanément indisponible ou Aucune commande aux dates choisies<p>";
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
      foreach($tabCommande_laredoute as $la_commande){ 
           $date_commande = (string)$la_commande['commande_date'];
           $tabDate = explode('T', $date_commande);
           $tabHeure = explode('+', $tabDate[1]);
           $tabTemp = explode('-', $tabDate[0]);
           $date_commande = $tabTemp[2].'/'.$tabTemp[1].'/'.$tabTemp[0].' '.$tabHeure[0];
          $commande_site = $DB_site2->query_first("SELECT * FROM facture WHERE mp_facturid='".$la_commande['commande_id_laredoute']."'");
           switch($la_commande['commande_statut_libelle']){
                case 'Refused':
                     $trColor = '#fcbebe';
                     break;
                case 'Shipped':
                     $trColor = '#b4dbbc';
                     break;
                case 'ToShip':
                     $trColor = '#ffcba0';
                     break;
           }
       $facturation = explode(' ', $la_commande['facturation_prenom_nom']); $facturation_nom = $facturation[0]; $facturation_prenom = $facturation[1];
              $livraison = explode(' ', $la_commande['livraison_prenom_nom']); $livraison_nom = $livraison[0]; $livraison_prenom = $livraison[1];

            $lien_commande = BACK_URL."/facture.php?action=editer&facturid=".$commande_site['facturid'];
           $facture = new facture($DB_site2, $commande_site['facturid']);
            $prix_tot = 0; 
                  $fdeport=explode('E',$la_commande['frais_de_port']);
                  $prix_fdeport = $fdeport[0]*pow('10',$fdeport[1]);
                  for($i=0;$i<$la_commande['count'];$i++)
                  { 
                    $prix_article = explode('E',$la_commande['prix_article'][$i]); 
                    $prix_WEEE = explode('E',$la_commande['article_ecotaxe'][$i]); 
                    $prix_tot += ($prix_article[0]*pow('10',$prix_article[1])+$prix_WEEE[0]*pow('10',$prix_WEEE[1]))*$la_commande['article_qte'][$i];
                    
                  }
              $prix_tot += $prix_fdeport;

               ?>
      <tr class="row<?php echo $alt; ?>" style="background-color: <?php echo $trColor; ?>; border-color: black;">
           <td height="20" style="padding:4px; text-align: center;border-bottom: 1px solid #990000;"><?php echo $la_commande['commande_id_laredoute']; ?></br><a target="_blank" href="<?php echo $lien_commande?>"><?php echo $commande_site['facturid'];?></a></br><?php 
                if ($commande_site['facturid'])
                { 
                   if ($facture->totalttc == $prix_tot) 
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
                      $prix_article = explode('E',$la_commande['prix_article'][$i]); 
                      $prix_WEEE = explode('E',$la_commande['article_ecotaxe'][$i]); 
                      $prix = $prix_article[0]*pow('10',$prix_article[1])+$prix_WEEE[0]*pow('10',$prix_WEEE[1]); echo "(".number_format($prix,2,',',' ')."€)".'</br>';
                    } 
              ?>
          </td>
           <td style="padding:4px; text-align: center;border-bottom: 1px solid #990000;">
              <?php
                  $prix_tot = 0; 
                  $fdeport=explode('E',$la_commande['frais_de_port']);
                  $prix_fdeport = $fdeport[0]*pow('10',$fdeport[1]);
                  for($i=0;$i<$la_commande['count'];$i++)
                  { 
                    $prix_article = explode('E',$la_commande['prix_article'][$i]); 
                    $prix_WEEE = explode('E',$la_commande['article_ecotaxe'][$i]); 
                    $prix_tot += ($prix_article[0]*pow('10',$prix_article[1])+$prix_WEEE[0]*pow('10',$prix_WEEE[1]))*$la_commande['article_qte'][$i];
                    
                  }
                  $prix_tot += $prix_fdeport;
                  echo number_format($prix_tot,2,',',' ')."€";
                  echo "</br>dont ".number_format($prix_fdeport,2,',',' ')." € de port";
                  
                $facturation_adresse = ''; if ($la_commande['facturation_adresse1'] != ''){$facturation_adresse .= $la_commande['facturation_adresse1'];} if ($la_commande['facturation_adresse2'] != ''){$facturation_adresse .= " ".$la_commande['facturation_adresse2'];} if ($la_commande['facturation_adresse3'] != ''){$facturation_adresse .= " ".$la_commande['facturation_adresse3'];}
                $livraison_adresse = ''; if ($la_commande['livraison_adresse1'] != ''){$livraison_adresse .= $la_commande['livraison_adresse1'];} if ($la_commande['livraison_adresse2'] != ''){$livraison_adresse .= " ".$la_commande['livraison_adresse2'];} if ($la_commande['livraison_adresse3'] != ''){$livraison_adresse .= " ".$la_commande['livraison_adresse3'];}
                
                $facturation_tel=$livraison_tel="";
                if ($la_commande['facturation_tel_mobile'] == ""){$facturation_tel = $la_commande['facturation_tel_domicile'];} else {$facturation_tel = $la_commande['facturation_tel_mobile'];} 
                if ($la_commande['livraison_tel_mobile'] == ""){$livraison_tel = $la_commande['livraison_tel_domicile'];} else {$livraison_tel = $la_commande['livraison_tel_mobile'];}

            ?>
          </td>
           <td style="padding:4px; text-align: center;border-bottom: 1px solid #990000;">
                <?php echo trim($la_commande['facturation_societe']) != '' ? $la_commande['facturation_societe'].'<br>' : ''; ?>
                <?php echo trim($la_commande['facturation_prenom_nom']) != '' ? $la_commande['facturation_civilite'].' '.$la_commande['facturation_prenom_nom'] : ''; ?>
                <?php echo trim($la_commande['facturation_adresse1']) != '' ? '<br>'.$la_commande['facturation_adresse1'] : ''; ?>
                <?php echo trim($la_commande['facturation_adresse2']) != '' ? '<br>'.$la_commande['facturation_adresse2'] : ''; ?>
                <?php echo trim($la_commande['facturation_cp']) != '' || trim($la_commande['facturation_ville']) != '' ? '<br>'.$la_commande['facturation_cp'].' '.$la_commande['facturation_ville'] : ''; ?>
                <?php echo trim($la_commande['facturation_pays']) != '' ? '<br>'.$la_commande['facturation_pays'] : ''; ?>
           </td>
           <td style="padding:4px; text-align: center;border-bottom: 1px solid #990000;">
                <?php echo trim($la_commande['livraison_societe']) != '' ? $la_commande['livraison_societe'].'<br>' : ''; ?>
                <?php echo trim($la_commande['livraison_prenom_nom']) != '' ? $la_commande['livraison_civilite'].' '.$la_commande['livraison_prenom_nom'] : ''; ?>
                <?php echo trim($la_commande['livraison_adresse1']) != '' ? '<br>'.$la_commande['livraison_adresse1'] : ''; ?>
                <?php echo trim($la_commande['livraison_adresse2']) != '' ? '<br>'.$la_commande['livraison_adresse2'] : ''; ?>
                <?php echo trim($la_commande['livraison_cp']) != '' || trim($la_commande['livraison_ville']) != '' ? '<br>'.$la_commande['livraison_cp'].' '.$la_commande['livraison_ville'] : ''; ?>
                <?php echo trim($la_commande['livraison_pays']) != '' ? '<br>'.$la_commande['livraison_pays'] : ''; ?>
                <?php echo trim($livraison_tel) != '' ? '<br>'.$livraison_tel : ''; ?>
               
                
           </td>
           <td style="padding:4px; text-align: center;border-bottom: 1px solid #990000;"><?=$la_commande['commande_statut_libelle']; ?></td>
           <td style="padding:4px; text-align: center;border-bottom: 1px solid #990000;">
              <?php 
              if($la_commande['commande_statut_libelle']!="Refused"){
                    if ($commande_site['facturid'] == ""){ ?>
                <form action="index.php?create=1" method="post" id="formAuto">
                  <input type="hidden" name="id_commande" value="<?=$la_commande['commande_id_laredoute'];?>">
                  <input type="hidden" name="date_commande" value="<?=$date_commande;?>">
                  <input type="hidden" name="market_place" value="LaRedoute">
                  <input type="hidden" name="market_place_id" value="14">

                  <input type="hidden" name="facturation_societe" value="<?=$la_commande['facturation_societe']; ?>">
                  <input type="hidden" name="facturation_prenom" value="<?=$facturation_prenom; ?>">
                  <input type="hidden" name="facturation_nom" value="<?=strtoupper($facturation_nom); ?>">
                  <input type="hidden" name="facturation_adresse" value="<?=$facturation_adresse; ?>">
                  <input type="hidden" name="facturation_cp" value="<?=$la_commande['facturation_cp']; ?>">
                  <input type="hidden" name="facturation_ville" value="<?=$la_commande['facturation_ville']; ?>">
                  <input type="hidden" name="facturation_pays" value="<?=$la_commande['facturation_pays']; ?>">
                    <input type="hidden" name="facturation_tel" value="<?=$facturation_tel; ?>">

                  <input type="hidden" name="livraison_societe" value="<?=$la_commande['livraison_societe']; ?>">
                  <input type="hidden" name="livraison_prenom" value="<?=$livraison_prenom; ?>">
                  <input type="hidden" name="livraison_nom" value="<?=strtoupper($livraison_nom); ?>">
                  <input type="hidden" name="livraison_adresse" value="<?=$livraison_adresse; ?>">
                  <input type="hidden" name="livraison_cp" value="<?=$la_commande['livraison_cp']; ?>">
                  <input type="hidden" name="livraison_ville" value="<?=$la_commande['livraison_ville']; ?>">
                  <input type="hidden" name="livraison_pays" value="<?=$la_commande['livraison_pays']; ?>">
                  <input type="hidden" name="livraison_tel" value="<?=$livraison_tel; ?>">

                  <input type="hidden" name="frais_de_port" value="<?=$prix_fdeport;?>">
                  <input type="hidden" name="nbr_produit" value="<?=$la_commande['count']; ?>">
                  <?php for ($i=0;$i<$la_commande['count'];$i++)
                    { 
                        $prix_article = explode('E',$la_commande['prix_article'][$i]); 
                      $prix_WEEE = explode('E',$la_commande['article_ecotaxe'][$i]); 
                      $prix = $prix_article[0]*pow('10',$prix_article[1])+$prix_WEEE[0]*pow('10',$prix_WEEE[1])
                      ?>
                        <input type="hidden" name="qte_art<?=$i; ?>" value="<?=$la_commande['article_qte'][$i]; ?>">
                        <input type="hidden" name="nom_art<?=$i; ?>" value="<?=$la_commande['article_ref_acilyon'][$i]; ?>"> 
                        <input type="hidden" name="prix_art<?=$i; ?>" value="<?=$prix; ?>"> 
                  <?php } ?>     
                  <input type="submit" value="Créer">
                </form>
              <?php }
                    else
                    {
                      echo "Commande traitée";
                    }
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
