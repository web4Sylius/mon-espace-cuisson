<?php
    require_once("admin_global.php");

    $monUrl = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
 ?>
<div>
<p>Mise à jour produits automatique du lundi au vendredi à 15h30</p>
<p>Produits désactivés si délai livraison > 7j</p>
<p>Ne tient pas compte des prix temporaires</p>
<p class="titre">&nbsp;Historique des fichiers précédemment exportés:</p>
</div>
<?php
if ($_GET['update'] == 1)
{
  ?>
    <p style="color: green; line-height: 40px; font-family: OpenSans; font-size:20;">&nbsp;La mise à jour a bien été effectuée</p>
  <?php
}

      $tab_fichier = array_diff(scandir(BACK_PATH."/comparateur/log/amazon"), array('..', '.'));
      $count1 = 0;
      $count2 = 0;
      if (!empty($tab_fichier))
      {
        foreach ($tab_fichier as $valeur)
        {
         $val = substr($valeur, 7, 1);
          if ($val == 'g')
            {
              $tab_global[$count1] = $valeur;
              $count1 ++;
            }
          else if ($val == 'r')
          {
             $tab_rapide[$count2] = $valeur;
             $count2 ++;
          }
        }
      }
?>
 <table style="border-collapse: collapse;">
   <tr>
      <th>Date</th>
      <th>Fichiers Exporté</th>
      <th>Supprimer</th>
    </tr>                                 
	<?php
	  if (!empty($tab_global))
	  {
      rsort($tab_global);
	    foreach ($tab_global as $valeur)
	    {
        if (strlen(substr($valeur, 13, strlen($valeur)-17)) == 8)
        {
          $valeur_epure = substr($valeur, 13, strlen($valeur)-17);
          $valeur_epure = substr($valeur_epure, 0, 2).'-'.substr($valeur_epure, 2, 2).'-'.substr($valeur_epure, 4, 4);
        }
        else
        {
          $valeur_epure = date("d-m-Y H:i",substr($valeur, 13, strlen($valeur)-17));
        }
	      $valeur = "/comparateur/log/amazon/".$valeur;
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
  <form name="lancer_script" id="lancer_script" method="post" action="/admin/comparateur/amazon/amazon.php">
    <input type="submit" value="Mise à jour complète des produits" class="jourbutton">
    <input type="hidden" name="URL" value="<?php echo($monUrl)?>"/>
  </form>
 </br>

 <table style="border-collapse: collapse;">
	 <tr>
    <th>Date</th>
    <th>Fichiers Exporté</th>
    <th>Supprimer</th>
  </tr>                                  
	<?php
	  if (!empty($tab_rapide))
	  {
      rsort($tab_rapide);
      foreach ($tab_rapide as $valeur)
	    {
        if (strlen(substr($valeur, 18, strlen($valeur)-22)) == 8)
        {
          $valeur_epure = substr($valeur, 18, strlen($valeur)-22);
          $valeur_epure = substr($valeur_epure, 0, 2).'-'.substr($valeur_epure, 2, 2).'-'.substr($valeur_epure, 4, 4);
        }
        else
        {
          $valeur_epure = date("d-m-Y H:i",substr($valeur, 18, strlen($valeur)-22));
        }
	      $valeur = "/comparateur/log/amazon/".$valeur;
	?>
          <tr>
            <td class="ligne"><?php echo($valeur_epure)?></td>
            <td class="ligne">
              <form method="post"style="margin: 0px;" align="center" type="button" action="/admin/includes/market_place/download.php">
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
 <form name="lancer_script" id="lancer_script" method="post" action="/admin/comparateur/amazon/amazon_prix.php">
    <input type="submit" value="Mise à jour rapide des produits" class="jourbutton">
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
      $sql .= " WHERE market_place = 'amazon'";
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
