<?php

require_once "admin_global.php";

include_once(BACK_PATH."/marketplace/cdiscount_bis.inc");//on recupere tabCommande 

if ($pasdispo == 1)
{
   echo "<p style=\"color:red;\">Cdiscount est momentanément indisponible<p>";
}
else
{

  $nbr_ToShip_cdiscount = 0;
  $nbr_Atraiter_cdiscount = 0;

  $cdiscount_url = BACK_URL."/marketplace/index.php?marketplace=cdiscount";

  foreach ($tabCommande_cdiscount as $la_commande) {//boucle pour compter le nombre de commande a traiter
    $commande_site = $DB_site2->query_first("SELECT * FROM facture WHERE mp_facturid='".$la_commande['commande_id_cdiscount']."'");
    if ($commande_site == "")
    {
      $nbr_Atraiter_cdiscount ++;
    }
    else
    {
       if ($la_commande['commande_statut_libelle'] == "WaitingForShipmentAcceptation"  || $la_commande['commande_statut_libelle'] == "PaymentInProgress")
       {
          ++$nbr_ToShip_cdiscount;
      }
    }
   
  }
  if ($nbr_ToShip_cdiscount != 0 || $nbr_Atraiter_cdiscount != 0)
  {
    $cdiscount_display = '<p><strong style="color:black;">Cdiscount  </strong>';
    if ($nbr_Atraiter_cdiscount != 0)//display en fonction de si il ya des commandes a +6traiter
    {
      $cdiscount_display = $cdiscount_display.'<a style="color:red;" target="_blank" href="'.$cdiscount_url.'">à traiter ('.$nbr_Atraiter_cdiscount.') </a>';
      if ($nbr_ToShip_cdiscount !=0)
      {
        $cdiscount_display = $cdiscount_display.'|';
      }
    }
    if ($nbr_ToShip_cdiscount != 0)//display en fonction de si il ya des commandes a traiter
    {
      $cdiscount_display = $cdiscount_display.'<a style="color:#ff9944;" target="_blank" href="'.$cdiscount_url.'"> à expédier ('.$nbr_ToShip_cdiscount.')</a>';
    }
    $cdiscount_display = $cdiscount_display.'</p>';
  }
  else
  {
    $cdiscount_display = '<p style="color:#008000;"><strong style="color:black;">Cdiscount</strong> traité</p>';
  }

  echo $cdiscount_display;
}
  ?>