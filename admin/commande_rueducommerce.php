<?php

require_once "admin_global.php";

include_once(BACK_PATH."/marketplace/rueducommerce_bis.inc");//on recupere tabCommande 

if ($pasdispo == 1)
{
   echo "<p style=\"color:red;\">Rue du commerce est momentanément indisponible<p>";
}
else
{

  $nbr_ToShip_rueducommerce = 0;
  $nbr_Atraiter_rueducommerce = 0;

  $rueducommercet_url = BACK_URL."/marketplace/index.php?marketplace=rueducommerce";

  foreach ($tabCommande_rueducommerce as $la_commande) {//boucle pour compter le nombre de commande a traiter
    $commande_site = $DB_site2->query_first("SELECT * FROM facture WHERE mp_facturid='".$la_commande['commande_id_rueducommerce']."'");
    if ($commande_site == "")
    {
      $nbr_Atraiter_rueducommerce ++;
    }
    else
    {
       if ($la_commande['commande_statut_libelle'] == "WaitingForShipmentAcceptation"  || $la_commande['commande_statut_libelle'] == "PaymentInProgress")
       {
          ++$nbr_ToShip_rueducommerce;
      }
    }
   
  }
  if ($nbr_ToShip_rueducommerce != 0 || $nbr_Atraiter_rueducommerce != 0)
  {
    $rueducommerce_display = '<p><strong style="color:black;">rueducommerce  </strong>';
    if ($nbr_Atraiter_rueducommerce != 0)//display en fonction de si il ya des commandes a +6traiter
    {
      $rueducommerce_display = $rueducommerce_display.'<a style="color:red;" target="_blank" href="'.$rueducommerce_url.'">à traiter ('.$nbr_Atraiter_rueducommerce.') </a>';
      if ($nbr_ToShip_rueducommerce !=0)
      {
        $rueducommerce_display = $rueducommerce_display.'|';
      }
    }
    if ($nbr_ToShip_rueducommerce != 0)//display en fonction de si il ya des commandes a traiter
    {
      $rueducommerce_display = $rueducommerce_display.'<a style="color:#ff9944;" target="_blank" href="'.$rueducommerce_url.'"> à expédier ('.$nbr_ToShip_rueducommerce.')</a>';
    }
    $rueducommerce_display = $rueducommerce_display.'</p>';
  }
  else
  {
    $rueducommerce_display = '<p style="color:#008000;"><strong style="color:black;">rueducommerce</strong> traité</p>';
  }

  echo $rueducommerce_display;
}
  ?>