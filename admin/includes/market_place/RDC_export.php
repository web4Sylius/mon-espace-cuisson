<?php
    require_once("admin_global.php");

    $monUrl = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
 ?>
<div>
 <p>La mise à jour complète se fait de manière automatique tous les jours a 15h</p>
 <p>Frais de port du site ajoutés au PV</p>
 <p>Prix temporaires non pris en compte, sauf pour certains soldes en dur</p>
 <p>Produit désactivé si date réappro > 7j</p>
 <p class="titre">&nbsp;Historique des fichiers précédemment exportés:</p>
</div>
<?php 
if ($_GET['update'] == 1)
{
  ?>
    <p style="color: green; line-height: 40px; font-family: OpenSans; font-size:20;">&nbsp;La mise à jour a bien été effectuée</p>
  <?php
}
  $tab_fichier = array_diff(scandir(BACK_PATH."/comparateur/log/RDC"), array('..', '.'));
?>
 <table style="border-collapse: collapse;">
	<tr>
    <th>Date</th>
    <th>Fichiers Exporté</th>
    <th>Supprimer</th>
  </tr>                                 
	<?php
	  if (!empty($tab_fichier))
	  {
      rsort($tab_fichier);
      foreach ($tab_fichier as $valeur)
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
	      $valeur = "/comparateur/log/RDC/".$valeur;
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
 <form name="lancer_script" id="lancer_script" method="post" action="/admin/comparateur/rueducommerce.php">
       <input type="submit" value="Mettre à jour les produits" class="jourbutton">
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
      $sql .= " WHERE market_place = 'rueducommerce'";
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