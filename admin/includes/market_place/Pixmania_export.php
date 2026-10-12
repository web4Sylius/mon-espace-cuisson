<?php
    require_once("admin_global.php");

    $monUrl = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
 ?>
  <p class="titre">&nbsp;Historique des fichiers précédemment exportés:</p>
  <p class="flotte"> <img src="images/icon/avertissement.png"></p>
  <p style="color: red; line-height: 60px; font-family: OpenSans; font-size:12;">&nbsp;ATTENTION: La mise à jour des fichiers Produits et Offres se fait manuellement</p>
   <?php
if ($_GET['update'] == 1)
{
  ?>
    <p style="color: green; line-height: 40px; font-family: OpenSans; font-size:20;">&nbsp;La mise à jour a bien été effectuée</p>
  <?php
}
  ?>
  <p class="titre">&Eacute;tape1:</p>
    <p style="font-family: OpenSans; font-size:12;">Transmettre à Pixmania la liste des produits que nous souhaitons mettre en ligne</p>
  </br>
    <form name="lancer_script" id="lancer_script" method="post" action="/admin/comparateur/pixmania_etape1.php">
      <input type="submit" value="&Eacute;tape1" class="jourbutton">
      <input type="hidden" name="URL" value="<?php echo($monUrl)?>"/>
    </form>
  </br>
</br>
<p class="titre">&Eacute;tape2:</p>
  <p style="font-family: OpenSans; font-size:12;">Télécharger le CSV des produits inconnus chez Pixmania.</p>
  <p style="font-family: OpenSans; font-size:12;">Transmettre cette liste à leur service informatique.</p>
  <p style="color: red; font-family: OpenSans; font-size:12;">Attention : cette étape doit obligatoirement être lancée le lendemain de l’étape 1, sans quoi aucun fichier ne pourra être généré .</p>
</br>
 <table style="border-collapse: collapse;">
      <tr>
        <th>Date</th>
        <th>Fichier de la liste des Produits</th>
        <th>Supprimer</th>
      </tr>
      <?php
          $tab_fichier = array_diff(scandir(BACK_PATH."/export/pixmania"), array('..', '.'));
          if (!empty($tab_fichier))
          {
            rsort($tab_fichier);
            foreach ($tab_fichier as $valeur)
            {
              if (substr($valeur, 0, 16) == "rapport_reponse_")
              {
                if (strlen(substr($valeur, 16, strlen($valeur)-20)) == 8)
              {
                $valeur_epure = substr($valeur, 16, strlen($valeur)-20);
                $valeur_epure = substr($valeur_epure, 0, 2).'-'.substr($valeur_epure, 2, 2).'-'.substr($valeur_epure, 4, 4);
              }
              else
              {
                $valeur_epure = date("d-m-Y H:i",substr($valeur, 16, strlen($valeur)-20));
              }
                $valeur = "/export/pixmania/".$valeur;
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
      <form name="lancer_script" id="lancer_script" method="post" action="/admin/comparateur/pixmania_etape2.php">
         <input type="submit" value="&Eacute;tape2" class="jourbutton">
         <input type="hidden" name="URL" value="<?php echo($monUrl)?>"/>
       </form>
    </br>
    <p class="titre">&Eacute;tape3:</p>
    <p style="font-family: OpenSans; font-size:12;">Mettre à jour les offres.</p>
  </br>
      <form name="lancer_script" id="lancer_script" method="post" action="/admin/comparateur/pixmania_etape2.php">
         <input type="submit" value="&Eacute;tape3" class="jourbutton">
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
      $sql .= " WHERE market_place = 'pixmania'";
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
                  