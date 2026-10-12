<?php
  require_once("admin_global.php");
  require_once(ROOT_PATH."configurations/www.ma-cave-a-vin.fr/config.php");
   $monUrl = "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
?>
<p>Mise à jour des produits PEM automatique le vendredi à 13h15</p>
<p>Mise à jour des produits GEM automatique le vendredi à 13h00</p>
<p>Mise à jour des Offres automatique tous les jours à 00h15</p>
<p>Les prix temporaires ne sont pas pris en compte, sauf pour certains produits en dur</p>
<p>Frais de port du site ajoutés au PV</p>
<p class="titre">&nbsp;Historique des fichiers précédemment exportés:</p>
<?php 
if ($_GET['update'] == 1)
{
  ?>
    <p style="color: green; line-height: 40px; font-family: OpenSans; font-size:20;">&nbsp;La mise à jour a bien été effectuée</p>
  <?php
}
?>
<table style="border-collapse: collapse;">
  <tr>
    <th>Date</th>
    <th>Fichier GEM</th>                                
    <th>Supprimer</th>
    <th>Fichier Integrity associé</th>
    <th>Fichier Integration associé</th>
  </tr>
<?php
$tab_fichier = array_diff(scandir(BACK_PATH."/export/fnac"), array('..', '.'));
$count1 = 0;
$count2 = 0;
if (!empty($tab_fichier))
{
  foreach ($tab_fichier as $valeur)
  {
    $val = substr($valeur, 12, 1);
    if ($val == 'g')
    {
      $tab_gem[$count1] = $valeur;
      $count1 ++;
    }
    else if ($val == 'p')
    {
      $tab_pem[$count2] = $valeur;
      $count2 ++;
    }
  }
}
//connexion au serveur fnac pour recupere les fichier associer aux premiers
$ftp_server = $ftp_server_fnac;
$login = $login_fnac;
$password = $password_fnac;
$connect = ftp_connect($ftp_server);
$log = ftp_login($connect, $login, $password);
$tab_integrity = ftp_nlist($connect, "/catalog_imports/reports/integrity");
$tab_integration = ftp_nlist($connect, "/catalog_imports/reports/integration");
if (!empty($tab_gem))
{
  rsort($tab_gem);
  foreach ($tab_gem as $valeur)
  {
    $valeur_comp = "gem_".date("dmY",substr($valeur, 16, strlen($valeur)-20));
    $agarder_integrity = 0;
    $agarder_integration = 0;
    foreach ($tab_integrity as $val_integrity) 
    {
      $val_integrity_comp = substr($val_integrity, -25, 12);
      if ($val_integrity_comp == $valeur_comp)  
      {
        $val_integrity_epure = substr($val_integrity, 35);
        $path_integrity = BACK_PATH."/export/fnac/log/".$val_integrity_epure;
        $handle = fopen($path_integrity, 'w');
        ftp_fget($connect, $handle, $val_integrity, FTP_BINARY, 0);
        $agarder_integrity = 1;
        fclose($handle);
      }
    }
    foreach ($tab_integration as $val_integration) 
    {
      $val_integration_comp = substr($val_integration, -25, 12);
      if ($val_integration_comp == $valeur_comp)
      {
        $val_integration_epure = substr($val_integration, 35);
        $path_integration = BACK_PATH."/export/fnac/log/".$val_integration_epure;
        $handle = fopen($path_integration, w);
        ftp_get($connect, ".", $val_integration, FTP_BINARY);
        $agarder_integration = 1;
        fclose($handle);
      }
    }
    if (strlen(substr($valeur, 16, strlen($valeur)-20)) == 8)
    {
      $valeur_epure = substr($valeur, 16, strlen($valeur)-20);
      $valeur_epure = substr($valeur_epure, 0, 2).'-'.substr($valeur_epure, 2, 2).'-'.substr($valeur_epure, 4, 4);
    }
    else
    {
      $valeur_epure = date("d-m-Y H:i",substr($valeur, 16, strlen($valeur)-20));
    }
    
    $valeur = "/export/fnac/".$valeur;
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
    <?php
      if ($agarder_integrity == 1)
      {
        $path = "/admin/export/fnac/log/".$val_integrity_epure;                                       
    ?>
        <td class="ligne">
          <form style="margin: 0px" type="button" value="télécharger" action="<?php echo($path)?>">
            <input type="submit" value=" " class="dlbutton"/>
          </form>
        </td>
    <?php
      }
      else
      {
    ?>
        <td class="ligne"></td>
    <?php
      }                                            
      if ($agarder_integration == 1)
      {
        $path = "/admin/export/fnac/".$val_integration_epure;
    ?>
        <td class="ligne">
          <form style="margin: 0px" type="button" value="télécharger" action="<?php echo($path)?>">
            <input type="submit" value=" " class="dlbutton"/>
          </form>
        </td>
    <?php
      }
      else
      {
    ?>
        <td class="ligne"></td>
    <?php
      }
    ?>
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
<?php
?>
</br>
  <form name="lancer_script" id="lancer_script" method="post" action="/admin/comparateur/fnac/fnac_gem.php">
    <input type="submit" value="Mettre à jour les fichier GEM" class="jourbutton">
    <input type="hidden" name="URL" value="<?php echo($monUrl)?>"/>
  </form>
</br>
<table style="border-collapse: collapse;">
  <tr>
    <th>Date</th>
    <th>Fichier PEM</th>                                
    <th>Supprimer</th>
    <th>Fichier Integrity associé</th>
    <th>Fichier Integration associé</th>
  </tr>
<?php
if (!empty($tab_pem))
{ 
  rsort($tab_pem);
  foreach ($tab_pem as $valeur)
  {
    $valeur_comp = "pem_".date("dmY",substr($valeur, 16, strlen($valeur)-20));
    $agarder_integrity = 0;
    $agarder_integration = 0;
    foreach ($tab_integrity as $val_integrity) 
    {
      $val_integrity_comp = substr($val_integrity, -25, 12);
      if ($val_integrity_comp == $valeur_comp)
      {
        $val_integrity_epure = substr($val_integrity, 35);
        $path_integrity = BACK_PATH."/export/fnac/log/".$val_integrity_epure;
        $handle = fopen($path_integrity, 'w');
        ftp_fget($connect, $handle, $val_integrity, FTP_BINARY, 0);
        $agarder_integrity = 1;
        fclose($handle);
      }
    }
    foreach ($tab_integration as $val_integration) 
    {
      $val_integration_comp = substr($val_integration, -25, 12);
      if ($val_integration_comp == $valeur_comp)
      {
        $val_integration_epure = substr($val_integration, 35);
        $path_integration = BACK_PATH."/export/fnac/log/".$val_integration_epure;
        $handle = fopen($path_integration, w);
        ftp_get($connect, ".", $val_integration, FTP_BINARY);
        $agarder_integration = 1;
        fclose($handle);
      }
    }
    if (strlen(substr($valeur, 16, strlen($valeur)-20)) == 8)
    {
      $valeur_epure = substr($valeur, 16, strlen($valeur)-20);
      $valeur_epure = substr($valeur_epure, 0, 2).'-'.substr($valeur_epure, 2, 2).'-'.substr($valeur_epure, 4, 4);
    }
    else
    {
      $valeur_epure = date("d-m-Y H:i",substr($valeur, 16, strlen($valeur)-20));
    }
    $valeur = "/export/fnac/".$valeur;
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
  <?php
    if ($agarder_integrity == 1)
    {
      $path = "/admin/export/fnac/log/".$val_integrity_epure;
  ?>
      <td class="ligne">
        <form style="margin: 0px" type="button" value="télécharger" action="<?php echo($path)?>">
          <input type="submit" value=" " class="dlbutton"/>
        </form>
      </td>
  <?php
    }
    else
    {
  ?>
      <td class="ligne"></td>
  <?php
    }                                            
    if ($agarder_integration == 1)
    {
      $path = "/admin/export/fnac/".$val_integration_epure;
     
  ?>
      <td class="ligne">
        <form style="margin: 0px" type="button" value="télécharger" action="<?php echo($path)?>">
          <input type="submit" value=" " class="dlbutton"/>
        </form>
      </td>
  <?php
    }
    else
    {
  ?>
      <td class="ligne"></td>
  <?php
    }
  ?>
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
ftp_close($connect);
?>
</table>
</br>
  <form name="lancer_script" id="lancer_script" method="post" action="/admin/comparateur/fnac/fnac_pem.php">
    <input type="submit" value="Mettre à jour les fichier PEM" class="jourbutton">
    <input type="hidden" name="URL" value="<?php echo($monUrl)?>"/>
  </form>
</br> 
</br>
</br>
</br>
  <form name="lancer_script" id="lancer_script" method="post" action="/admin/comparateur/fnac_update.php">
    <input type="submit" value="Mettre à jour les Offres" class="jourbutton">
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
      $sql .= " WHERE market_place = 'fnac'";
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