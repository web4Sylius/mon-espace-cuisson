<?php
    require_once("admin_global.php");
    require_once(ROOT_PATH."configurations/www.ma-cave-a-vin.fr/config.php");
    $monUrl = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
 ?>
  <div>
    <p>Mise à jour des produits du lundi au vendredi à 17h30</p>
    <p>Mise à jour des offres du lundi au vendredi à 0h30 (La mise à jour met 2h à 5h)</p>
    <p>Produits désactivés si date réappro > 7j</p>
    <p>Frais de port identiques au site</p>
    <p>Prix temporaires non pris en compte</p>
  </div>
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
    <th>Fichier "Autour du vin"</th>                                
    <th>Supprimer</th>
  </tr>
<?php
$tab_fichier = array_diff(scandir(BACK_PATH."/comparateur/log/laredoute/Autourduvin"), array('..', '.'));

  if (!empty($tab_fichier))
  {
    rsort($tab_fichier);
    foreach ($tab_fichier as $valeur)
    {
      $valeur_epure = substr($valeur, 23, 14);
      $chiffre = explode('-', $valeur_epure);
      $valeur_epure = $chiffre[2].'-'.$chiffre[1].'-20'.$chiffre[0].' '.$chiffre[3].':'.$chiffre[4];
      $valeur = "/comparateur/log/laredoute/Autourduvin/".$valeur;
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
<form method="post" name="lancer_script" id="lancer_script" action="/admin/comparateur/laredoute/laredoute_autourduvin_api.php">
   <input type="submit" name="test" value="Mettre à jour les fichier <?php echo(htmlspecialchars("\"Autour du vin\""))?>" class="jourbutton"/>
   <input type="hidden" name="URL" value="<?php echo($monUrl)?>"/>
</form>
</br>

<table style="border-collapse: collapse;">
  <tr>
    <th>Date</th>
    <th>Fichier "Caves à vin"</th>                                
    <th>Supprimer</th>
  </tr>
<?php
$tab_fichier = array_diff(scandir(BACK_PATH."/comparateur/log/laredoute/Caves"), array('..', '.'));

  if (!empty($tab_fichier))
  {
    rsort($tab_fichier);
    foreach ($tab_fichier as $valeur)
    {
      $valeur_epure = substr($valeur, 23, 14);
      $chiffre = explode('-', $valeur_epure);
      $valeur_epure = $chiffre[2].'-'.$chiffre[1].'-20'.$chiffre[0].' '.$chiffre[3].':'.$chiffre[4];
      $valeur = "/comparateur/log/laredoute/Caves/".$valeur;
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
<form method="post" name="lancer_script" id="lancer_script" action="/admin/comparateur/laredoute/laredoute_caves_api.php">
   <input type="submit" name="test" value="Mettre à jour les fichier <?php echo(htmlspecialchars("\"Caves à vin\""))?>" class="jourbutton"/>
   <input type="hidden" name="URL" value="<?php echo($monUrl)?>"/>
</form>
</br>


<table style="border-collapse: collapse;">
  <tr>
    <th>Date</th>
    <th>Fichier "Réfrigération"</th>                                
    <th>Supprimer</th>
  </tr>
<?php
$tab_fichier = array_diff(scandir(BACK_PATH."/comparateur/log/laredoute/Refrigeration"), array('..', '.'));

  if (!empty($tab_fichier))
  {
    rsort($tab_fichier);
    foreach ($tab_fichier as $valeur)
    {
      $valeur_epure = substr($valeur, 23, 14);
      $chiffre = explode('-', $valeur_epure);
      $valeur_epure = $chiffre[2].'-'.$chiffre[1].'-20'.$chiffre[0].' '.$chiffre[3].':'.$chiffre[4];
      $valeur = "/comparateur/log/laredoute/Refrigeration/".$valeur;
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
<form method="post" name="lancer_script" id="lancer_script" action="/admin/comparateur/laredoute/laredoute_refrigerateur_api.php">
   <input type="submit" name="test" value="Mettre à jour les fichier <?php echo(htmlspecialchars("\"Réfrigération\""))?>" class="jourbutton"/>
   <input type="hidden" name="URL" value="<?php echo($monUrl)?>"/>
</form>
</br>


<table style="border-collapse: collapse;">
  <tr>
    <th>Date</th>
    <th>Fichier "Offres"</th>                                
    <th>Supprimer</th>
  </tr>
<?php
$tab_fichier = array_diff(scandir(BACK_PATH."/comparateur/log/laredoute/Offres"), array('..', '.'));

  if (!empty($tab_fichier))
  {
    rsort($tab_fichier);
    foreach ($tab_fichier as $valeur)
    {
      $valeur_epure = substr($valeur, 26, 14);
      $chiffre = explode('-', $valeur_epure);
      $valeur_epure = $chiffre[2].'-'.$chiffre[1].'-20'.$chiffre[0].' '.$chiffre[3].':'.$chiffre[4];
      $valeur = "/comparateur/log/laredoute/Offres/".$valeur;
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
<form method="post" name="lancer_script" id="lancer_script" action="/admin/comparateur/laredoute/laredoute_offers.php">
   <input type="submit" name="test" value="Mettre à jour les fichier <?php echo(htmlspecialchars("\"Offres\""))?>" class="jourbutton"/>
   <input type="hidden" name="URL" value="<?php echo($monUrl)?>"/>
</form>
</br>
<p class="titre">Suivi des dernières actions:</p>
</br>
<?php
$ftp_server = $ftp_server_laredoute;
$login = $login_laredoute;
$password = $password_laredoute;
$remote = "sftp://$login:$password@$ftp_server/Reports/SellerOffer/";
$local = BACK_PATH."/comparateur/log/laredoute/SellerOffer/";
$curl = curl_init();
curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_SFTP);
curl_setopt($curl, CURLOPT_USERPWD, "$login:$password");
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
$data = curl_exec($curl);
curl_close($curl);
$explode  = explode("SellerOffer_IntegrationReport", $data); //On parse le contenu du dossier recuperer
$count = 0;
foreach ($explode as $key) {
  if (strlen($key) > 26)
  {
    $explode[$count] = "SellerOffer_IntegrationReport".substr($key, 0, strlen($key)-57);
  }
  else
  {
    $explode[$count] = "SellerOffer_IntegrationReport".rtrim($key);
  }
  $count ++;  
}
unset($explode[0]);
foreach ($explode as $key  ) { //On télécharge du dossier sftp vers le serveur
  $remote = "sftp://$login:$password@$ftp_server/Reports/SellerOffer/".$key;
  $local = BACK_PATH."/comparateur/log/laredoute/SellerOffer/".$key;
  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, $remote);
  curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_SFTP);
  curl_setopt($curl, CURLOPT_USERPWD, "$login:$password");
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  file_put_contents($local, curl_exec($curl));
  curl_close($curl);
}
?>
<table style="border-collapse: collapse;">
  <tr>
    <th>Date</th>
    <th>Fichier "SellerOffer"</th>
    <th>Supprimer</th>
  </tr>
<?php
$tab_fichier = array_diff(scandir(BACK_PATH."/comparateur/log/laredoute/SellerOffer"), array('..', '.'));
  if (!empty($tab_fichier))
  {
    rsort($tab_fichier);
    $array_diff = array_diff($tab_fichier, $explode);
    foreach ($tab_fichier as $valeur)
    {
      $save_valeur = $valeur;
      if (strlen($valeur) > 53)
      {
         $valeur_epure = substr($valeur, 36, 14);
      }
      else
      {
        $valeur_epure = substr($valeur, 36, 13);
      }
      $chiffre1 = substr($valeur_epure, 0, 4);
      $chiffre2 = substr($valeur_epure, 4, 2);
      $chiffre3 = substr($valeur_epure, 6, 2);
      $chiffre4 = substr($valeur_epure, 8, strlen($valeur_epure)-12);
      $valeur_epure = $chiffre3.'-'.$chiffre2.'-'.$chiffre1.' '.$chiffre4.':00';
      $valeur = "/comparateur/log/laredoute/SellerOffer/".$valeur;
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
      <?php
        foreach ($array_diff as $value) {
           if ($value == $save_valeur)
           {
             
            ?>
            <form method="post" style="margin: 0px" type="button" action="/admin/includes/market_place/delete.php">
              <input class="suprbutton" name="supprimer" type="submit" onclick="if(!confirm('Etes-vous sur de vouloir supprimer le fichier?')) return false;" value=" "/>
              <input type="hidden" name="page" value="<?php echo($valeur)?>"/>
              <input type="hidden" name="URL" value="<?php echo($monUrl)?>"/>
            </form>
            <?php
              
           }
         }        
      ?>
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
<?php
$ftp_server = $ftp_server_laredoute;
$login = $login_laredoute;
$password = $password_laredoute;
$remote = "sftp://$login:$password@$ftp_server/Reports/OpenListingReport/";
$local = BACK_PATH."/comparateur/log/laredoute/OpenListingReport/";
$curl = curl_init();
curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_SFTP);
curl_setopt($curl, CURLOPT_USERPWD, "$login:$password");
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
$data = curl_exec($curl);
curl_close($curl);
$explode1  = explode("openlistingreport", $data); //On parse le contenu du dossier recuperer
$limite = count($explode1);
$count = 0;
foreach ($explode1 as $key) {
  if ($count == $limite - 1)
  {
     $explode1[$count] = "openlistingreport".rtrim($key);
  }
  else
  {
    $explode1[$count] = "openlistingreport".substr($key, 0, strlen($key)-57); 
  }
  
  $count ++; 
  
}
unset($explode1[0]);
foreach ($explode1 as $key  ) { //On télécharge du dossier sftp vers le serveur
  $remote = "sftp://$login:$password@$ftp_server/Reports/OpenListingReport/".$key;
  $local = BACK_PATH."/comparateur/log/laredoute/OpenListingReport/".$key;
  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, $remote);
  curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_SFTP);
  curl_setopt($curl, CURLOPT_USERPWD, "$login:$password");
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  file_put_contents($local, curl_exec($curl));
  curl_close($curl);
}
?>
<table style="border-collapse: collapse;">
  <tr>
    <th>Date</th>
    <th>Fichier "OpenListingReport"</th>
    <th>Supprimer</th>
  </tr>
<?php
$tab_fichier = array_diff(scandir(BACK_PATH."/comparateur/log/laredoute/OpenListingReport"), array('..', '.'));

  if (!empty($tab_fichier))
  {
    rsort($tab_fichier);
    $array_diff = array_diff($tab_fichier, $explode1);
    foreach ($tab_fichier as $valeur)
    {
      $save_valeur = $valeur;
      if (strlen($valeur) > 42)
      {
         $valeur_epure = substr($valeur, 24, 15);
      }
      else
      {
        $valeur_epure = substr($valeur, 24, 14);
      }
      $chiffre1 = substr($valeur_epure, 0, 2);
      $chiffre2 = substr($valeur_epure, 2, 2);
      $chiffre3 = substr($valeur_epure, 4, 4);
      $chiffre4 = substr($valeur_epure, 8, 2);
      $valeur_epure = $chiffre1.'-'.$chiffre2.'-'.$chiffre3.' '.$chiffre4.':00';
      $valeur = "/comparateur/log/laredoute/OpenListingReport/".$valeur;
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
      <?php
        foreach ($array_diff as $value) {
           if ($value == $save_valeur)
           {
              
            ?>
            <form method="post" style="margin: 0px" type="button" action="/admin/includes/market_place/delete.php">
              <input class="suprbutton" name="supprimer" type="submit" onclick="if(!confirm('Etes-vous sur de vouloir supprimer le fichier?')) return false;" value=" "/>
              <input type="hidden" name="page" value="<?php echo($valeur)?>"/>
              <input type="hidden" name="URL" value="<?php echo($monUrl)?>"/>
            </form>
            <?php
              
           }
         }        
      ?>
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
<?php
$ftp_server = $ftp_server_laredoute;
$login = $login_laredoute;
$password = $password_laredoute;
$remote = "sftp://$login:$password@$ftp_server/Reports/Product/";
$local = BACK_PATH."/comparateur/log/laredoute/Product/";
$curl = curl_init();
curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_SFTP);
curl_setopt($curl, CURLOPT_USERPWD, "$login:$password");
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
$data = curl_exec($curl);
curl_close($curl);
/*$explode1  = explode("openlistingreport", $data); //On parse le contenu du dossier recuperer
$limite = count($explode1);
$count = 0;
foreach ($explode1 as $key) {
  if ($count == $limite - 1)
  {
     $explode1[$count] = "openlistingreport".rtrim($key);
  }
  else
  {
    $explode1[$count] = "openlistingreport".substr($key, 0, strlen($key)-57); 
  }
  
  $count ++; 
  
}
unset($explode1[0]);
foreach ($explode1 as $key  ) { //On télécharge du dossier sftp vers le serveur
  $remote = "sftp://$login:$password@$ftp_server/Reports/OpenListingReport/".$key;
  $local = BACK_PATH."/comparateur/log/laredoute/OpenListingReport/".$key;
  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, $remote);
  curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_SFTP);
  curl_setopt($curl, CURLOPT_USERPWD, "$login:$password");
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  file_put_contents($local, curl_exec($curl));
  curl_close($curl);
}*/
?>
<table style="border-collapse: collapse;">
  <tr>
    <th>Date</th>
    <th>Fichier "Product"</th>
    <th>Supprimer</th>
  </tr>
<?php
$tab_fichier = array_diff(scandir(BACK_PATH."/comparateur/log/laredoute/Product"), array('..', '.'));

  if (!empty($tab_fichier))
  {
    rsort($tab_fichier);
    $array_diff = array_diff($tab_fichier, $explode);
    foreach ($tab_fichier as $valeur)
    {
      $save_valeur = $valeur;
      /*if (strlen($valeur) > 42)
      {
         $valeur_epure = substr($valeur, 24, 15);
      }
      else
      {
        $valeur_epure = substr($valeur, 24, 14);
      }
      $chiffre1 = substr($valeur_epure, 0, 2);
      $chiffre2 = substr($valeur_epure, 2, 2);
      $chiffre3 = substr($valeur_epure, 4, 4);
      $chiffre4 = substr($valeur_epure, 8, 2);
      $valeur_epure = $chiffre1.'-'.$chiffre2.'-'.$chiffre3.' '.$chiffre4.':00';*/
      $valeur = "/comparateur/log/laredoute/Product/".$valeur;
?>
  <tr>
    <td class="ligne"><?php echo($valeur/*$valeur_epure*/)?></td>
    <td class="ligne">
      <form method="post" style="margin: 0px;" align="center" type="button" action="/admin/includes/market_place/download.php">
        <input type="submit" value=" " class="dlbutton"/>
        <input type="hidden" name="page" value="<?php echo($valeur)?>"/>
      </form>
    </td>
    <td class="ligne">
      <?php
        foreach ($array_diff as $value) {
           if ($value == $save_valeur)
           {
              
            ?>
            <form method="post" style="margin: 0px" type="button" action="/admin/includes/market_place/delete.php">
              <input class="suprbutton" name="supprimer" type="submit" onclick="if(!confirm('Etes-vous sur de vouloir supprimer le fichier?')) return false;" value=" "/>
              <input type="hidden" name="page" value="<?php echo($valeur)?>"/>
              <input type="hidden" name="URL" value="<?php echo($monUrl)?>"/>
            </form>
            <?php
              
           }
         }        
      ?>
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
          <p style="color: red;">Aucun enregistrement (Développement à finaliser)</p>
        </td>
      </tr> 
    <?php
  }
 ?>

</table>
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
      $sql .= " WHERE market_place = 'laredoute'";
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
                  
