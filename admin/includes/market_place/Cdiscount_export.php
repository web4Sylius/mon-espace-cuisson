<?php
    require_once("admin_global.php");

    $monUrl = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
 ?>
  
  <p>Mise à jour des produits :
  <ul>
        <li>Se connecter sur l'interface vendeur <a href="https://seller.cdiscount.com/login" target="_blank">https://seller.cdiscount.com/login</a></li>
        <li>Aller dans "Stock > Demander la création de produits en masse</li>
        <li>Télécharger le modèle de fichier dans l'étape 2 (cliquer sur "Demande de création Produits")</li>
        <li>Cliquer sur "Mettre à jour les produits" dans l'admin MCAV ("Places de marché > Exports > Cdiscount")</li>
        <li>Ouvrir le fichier fraichement généré <u>avec excel</u> puis transposer les colonnes (sans les intitulés de colonnes) dans le fichier modèle de Cdiscount, <u>enregistrer les fichier en xlsx</u></li>
        <li>Charger le fichier dans sur Cdiscount (leur fichier évoluant régulièrement, il est possible qu'il faille adapter quelques champs dans l'export - analyser le rapport d'erreur)</li>
  </ul></p>
  <p>Mise à jour des offres <u>bien vérifier que les produits sont créés avant</u> :
  <ul>
        <li>Se connecter sur l'interface vendeur <a href="https://seller.cdiscount.com/login" target="_blank">https://seller.cdiscount.com/login</a></li>
        <li>Aller dans "Stock > Ajouter/Modifier des offres en masse"</li>
        <li>Télécharger le modèle d'intégration ("complet" s'il y a des offres à créer, sinon "simplifié")</li>
        <li>Cliquer sur "Mise à jour des offres" (export complet) ou "Mise à jour rapide des offres" (export simplifié) dans l'admin MCAV ("Places de marché > Exports > Cdiscount")</li>
        <li>Ouvrir le fichier fraichement généré <u>dans excel</u> puis transposer les colonnes (sans les intitulés de colonnes) dans le fichier modèle de Cdiscount, <u>l'enregistrer en xlxs</u></li>
        <li>Charger le fichier dans sur Cdiscount (en "complet" ou "simplifié" selon le cas)</li>
        <li>Analyser le rapport d'erreur de temps en temps</li>
  </ul></p>
  <p>Les produits avec une date de réappro > 7j ou avec delai de livraison > 12j sont désactivés</p>
  <p>Les prix temporaires ne sont pas pris en compte</p>
  <p class="titre">&nbsp;Historique des fichiers précédemment exportés:</p>
   <?php
if ($_GET['update'] == 1)
{
  ?>
    <p style="color: green; line-height: 40px; font-family: OpenSans; font-size:20;">&nbsp;La mise à jour a bien été effectuée</p>
  <?php
}
      $tab_fichier = array_diff(scandir(BACK_PATH."/export/cdiscount"), array('..', '.'));
      $count1 = 0;
      $count2 = 0;
      $count3 = 0;
      $count4 = 0;
      if (!empty($tab_fichier))
      {
        foreach ($tab_fichier as $valeur)
        {
          $val = substr($valeur, 17, 1);
          $val2 = substr($valeur, 24,1);
          if ($val == 'o')
            {
              $tab_offres[$count1] = $valeur;
              $count1 ++;
            }
          elseif ($val == 'p')
          {
             $tab_produits[$count2] = $valeur;
             $count2 ++;
          }
          elseif ($val == 'r' && $val2 == 'o')
          {
            $tab_offres_rapide[$count3] = $valeur;
            $count3 ++;
          }
          elseif ($val == 'r' && $val2 == 'p')
          {
            $tab_produits_rapide[$count4] = $valeur;
            $count4 ++;
          }
       }
      }
    ?>
       <table style="border-collapse: collapse;">
        <tr>
          <th>Date</th>
          <th>Fichiers Produit</th>
          <th>Supprimer</th>
        </tr>                                
        <?php
          if (!empty($tab_produits))
          {
            rsort($tab_produits);
            foreach ($tab_produits as $valeur)
            {
              if (strlen(substr($valeur, 26, strlen($valeur)-30)) == 8)
              {
                $valeur_epure = substr($valeur, 26, strlen($valeur)-30);
                $valeur_epure = substr($valeur_epure, 0, 2).'-'.substr($valeur_epure, 2, 2).'-'.substr($valeur_epure, 4, 4);
              }
              else
              {
                $valeur_epure = date("d-m-Y H:i",substr($valeur, 26, strlen($valeur)-30));
              }
              $valeur = "/export/cdiscount/".$valeur;
        ?>
          <tr>
            <td class="ligne"><?php echo($valeur_epure)?></td>
            <td class="ligne">
              <form method="post" style="margin: 0px;" align="center" type="button" action="/admin/includes/market_place/download.php">
                <input type="submit" value=" " class="dlbutton"/>
                <input type="hidden" name="page" value="<?php echo($valeur)?>"/>
              </form>
            </td>
            <td class="ligne">
              <form method="post" style="margin: 0px" type="button" action="/admin/includes/market_place/delete.php">
                <input class="suprbutton" name="supprimer" type="submit" onclick="if(!confirm('Etes-vous sur de vouloir supprimer le fichier?')) return false;" value=" "/>
                <input type="hidden" name="page" value="<?php echo($valeur)?>"/>
                <input type="hidden" name="URL" value="<?php echo($monUrl)?>"/>
              </form>
            </td>
          </tr> 
        <?php
            }
          }
          else
          {
            ?>
              <tr>
                <td>
                </td>
                <td>
                  <p style="color: red;">Aucun enregistrement</p>
                </td>
              </tr> 
            <?php
          }
         ?> 
        </table>
           </br>
            <form method="post" name="lancer_script" id="lancer_script" action="/admin/comparateur/cdiscount_produits.php">
               <input type="submit" value="Mettre à jour les produits" class="jourbutton">
               <input type="hidden" name="URL" value="<?php echo($monUrl)?>"/>
            </form>
           </br>
        <table style="border-collapse: collapse;">
            <tr>
              <th>Date</th>
              <th>Fichiers Offre</th>
              <th>Supprimer</th>
            </tr>
            <?php
                if (!empty($tab_offres))
                {
                  rsort($tab_offres);
                  foreach ($tab_offres as $valeur)
                  {
                    if (strlen(substr($valeur, 24, strlen($valeur)-28)) == 8)
                    {
                      $valeur_epure = substr($valeur, 24, strlen($valeur)-28);
                      $valeur_epure = substr($valeur_epure, 0, 2).'-'.substr($valeur_epure, 2, 2).'-'.substr($valeur_epure, 4, 4);
                    }
                    else
                    {
                      $valeur_epure = date("d-m-Y H:i",substr($valeur, 24, strlen($valeur)-28));
                    }
                    $valeur = "/export/cdiscount/".$valeur;
            ?>
            <tr>
              <td class="ligne"><?php echo($valeur_epure)?></td>
              <td class="ligne">
                <form method="post" style="margin: 0px" type="button" value="télécharger" action="/admin/includes/market_place/download.php">
                  <input type="submit" value=" " class="dlbutton"/>
                  <input type="hidden" name="page" value="<?php echo($valeur)?>"/>
                </form>
              </td>
              <td class="ligne">
                <form method="post" style="margin: 0px" type="button" action="/admin/includes/market_place/delete.php">
                  <input class="suprbutton" name="supprimer" type="submit" onclick="if(!confirm('Etes-vous sur de vouloir supprimer le fichier?')) return false;" value=" "/>
                  <input type="hidden" name="page" value="<?php echo($valeur)?>"/>
                  <input type="hidden" name="URL" value="<?php echo($monUrl)?>"/>
                </form>
              </td>
            </tr> 
              <?php
                  }
                }
                else
                {
                  ?>
                    <tr>
                      <td>
                      </td>
                      <td>
                        <p style="color: red;">Aucun enregistrement</p>
                      </td>
                    </tr>
                  <?php
                }
              ?>
           </table>                                                                                  
           </br>
            <form name="lancer_script" id="lancer_script" method="post" action="/admin/comparateur/cdiscount_offres.php">
               <input type="submit" value="Mettre à jour les offres" class="jourbutton">
               <input type="hidden" name="URL" value="<?php echo($monUrl)?>"/>
             </form>
       </br>
       <table style="border-collapse: collapse;">
            <tr>
              <th>Date</th>
              <th>Fichiers Offre</th>
              <th>Supprimer</th>
            </tr>
            <?php
                if (!empty($tab_offres_rapide))
                {
                  rsort($tab_offres_rapide);
                  foreach ($tab_offres_rapide as $valeur)
                  {
                    if (strlen(substr($valeur, 31, strlen($valeur)-35)) == 8)
                    {
                      $valeur_epure = substr($valeur, 31, strlen($valeur)-35);
                      $valeur_epure = substr($valeur_epure, 0, 2).'-'.substr($valeur_epure, 2, 2).'-'.substr($valeur_epure, 4, 4);
                    }
                    else
                    {
                      $valeur_epure = date("d-m-Y H:i",substr($valeur, 31, strlen($valeur)-35));
                    }
                    $valeur = "/export/cdiscount/".$valeur;
            ?>
            <tr>
              <td class="ligne"><?php echo($valeur_epure)?></td>
              <td class="ligne">
                <form method="post" style="margin: 0px" type="button" value="télécharger" action="/admin/includes/market_place/download.php">
                  <input type="submit" value=" " class="dlbutton"/>
                  <input type="hidden" name="page" value="<?php echo($valeur)?>"/>
                </form>
              </td>
              <td class="ligne">
                <form method="post" style="margin: 0px" type="button" action="/admin/includes/market_place/delete.php">
                  <input class="suprbutton" name="supprimer" type="submit" onclick="if(!confirm('Etes-vous sur de vouloir supprimer le fichier?')) return false;" value=" "/>
                  <input type="hidden" name="page" value="<?php echo($valeur)?>"/>
                  <input type="hidden" name="URL" value="<?php echo($monUrl)?>"/>
                </form>
              </td>
            </tr> 
              <?php
                  }
                }
                else
                {
                  ?>
                    <tr>
                      <td>
                      </td>
                      <td>
                        <p style="color: red;">Aucun enregistrement</p>
                      </td>
                    </tr>
                  <?php
                }
              ?>
           </table>                                                                                  
           </br>
            <form name="lancer_script" id="lancer_script" method="post" action="/admin/comparateur/cdiscount_offres_rapide.php">
               <input type="submit" value="Mise à jour rapide des offres" class="jourbutton">
               <input type="hidden" name="URL" value="<?php echo($monUrl)?>"/>
             </form>
       </br>
      <p class="titre">Suivi des dernières actions:</p>
    </br>

     <table style="border-collapse: collapse;">
        <tr>
          <th>Date</th>
          <th>Utilisateur</th>
          <th>Action</th>
          <th>Place de marché</th>
        </tr>  

    <?php
      $sql = "SELECT *";
      $sql .= " FROM suivi_export";
      $sql .= " WHERE market_place = 'cdiscount'";
      $sql .= " ORDER BY id DESC";
      $result = $DB_site2->query($sql);
      while($row = $DB_site2->fetch_array($result)){
        $tab_export[] = $row;
      }
      for ($i=0;$i<3;$i++)
      {
        if (!empty($tab_export[$i]))
        {
          ?>
            <tr>
              <td class="ligne"><?php echo($tab_export[$i]['time'])?></td>
              <td class="ligne"><?php echo($tab_export[$i]['user'])?></td>
              <td class="ligne"><?php echo($tab_export[$i]['action'])?></td>
              <td class="ligne"><?php echo($tab_export[$i]['market_place'])?></td>
            </tr>
         <?php
        }        
      }
    ?>
    </table>
   