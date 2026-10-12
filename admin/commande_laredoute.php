<?php

require_once "admin_global.php";

include_once(BACK_PATH."/marketplace/laredoute_bis.inc");//on recupere tabCommande 

if ($pasdispo == 1)
{
   echo "<p style=\"color:red;\">LaRedoute est momentanément indisponible<p>";
}
else
{

  $nbr_ToShip_laredoute = 0;
  $nbr_Atraiter_laredoute = 0;

  $laredoute_url = BACK_URL."/marketplace/index.php?marketplace=laredoute";

  foreach ($tabCommande_laredoute as $la_commande) {//boucle pour compter le nombre de commande a traiter
   $commande_site = $DB_site2->query_first("SELECT * FROM facture WHERE mp_facturid='".$la_commande['commande_id_laredoute']."'");
    if ($commande_site == "")
    {
        if($la_commande['commande_statut_libelle'] != "OutOfStock" && $la_commande['commande_statut_libelle'] != "Refused") {
          $nbr_Atraiter_laredoute ++;
        }
    }
    else
    {
    	if ($la_commande['commande_statut_libelle'] == "ToShip" )
    	{
    		++$nbr_ToShip_laredoute;
    	}
    }
  }
  if ($nbr_ToShip_laredoute != 0 || $nbr_Atraiter_laredoute != 0)
  {
    $laredoute_display = '<p><strong style="color:black;">Laredoute  </strong>';
    if ($nbr_Atraiter_laredoute != 0)//display en fonction de si il ya des commandes a traiter
    {
      $laredoute_display = $laredoute_display.'<a style="color:red;" target="_blank" href="'.$laredoute_url.'">à traiter ('.$nbr_Atraiter_laredoute.') </a>';
      if ($nbr_ToShip_laredoute !=0)
      {
        $laredoute_display = $laredoute_display.'|';
      }
    }
    if ($nbr_ToShip_laredoute != 0)//display en fonction de si il ya des commandes a traiter
    {
      $laredoute_display = $laredoute_display.'<a style="color:#ff9944;" target="_blank" href="'.$laredoute_url.'"> à expédier ('.$nbr_ToShip_laredoute.')</a>';
    }
    $laredoute_display = $laredoute_display.'</p>';
  }
  else
  {
  	$laredoute_display = '<p style="color:#008000;"><strong style="color:black;">Laredoute</strong> traité</p>';
  }

  echo $laredoute_display;
}
  ?>