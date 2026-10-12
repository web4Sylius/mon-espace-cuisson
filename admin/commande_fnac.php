<?php

require_once "admin_global.php";
  include_once(BACK_PATH."/marketplace/fnac_bis.inc");//on recupere tabCommande 

$nbr_ToShip_fnac = 0;
$nbr_Atraiter_fnac = 0;

$fnac_url = BACK_URL."/marketplace/index.php?marketplace=fnac";
    
  foreach ($tabCommande as $la_commande) {//boucle pour compter le nombre de commande a traiter
    if(!in_array($la_commande['commande_statut_libelle'],array("Refused","Refunded"))){
        $commande_site = $DB_site2->query_first("SELECT * FROM facture WHERE mp_facturid='".$la_commande['commande_id_fnac']->__tostring()."'");
        if ($commande_site == "")
        {
        $nbr_Atraiter_fnac ++;
        }
        else
        {
            if ($la_commande['commande_statut_libelle'] == "ToShip")
            {
                    ++$nbr_ToShip_fnac;
            }
        }
    }
  }
  
  if ($nbr_ToShip_fnac != 0 || $nbr_Atraiter_fnac !=0)
  {
    $fnac_display = '<p><strong style="color:black;">Fnac </strong>';
    if ($nbr_Atraiter_fnac != 0)//display en fonction de si il ya des commandes a traiter
    {
      $fnac_display = $fnac_display.'<a style="color:red;" target="_blank" href="'.$fnac_url.'">à traiter ('.$nbr_Atraiter_fnac.') </a>';
      if ($nbr_ToShip_fnac !=0)
      {
        $fnac_display = $fnac_display.'|';
      }
    }
    if ($nbr_ToShip_fnac !=0)
    {
      $fnac_display = $fnac_display.'<a style="color:#ff9944;" target="_blank" href="'.$fnac_url.'"> à expédier ('.$nbr_ToShip_fnac.')</a>';
    }
    $fnac_display = $fnac_display.'</p>';
  }
  else
  {
  	$fnac_display = '<p style="color:#008000;"><strong style="color:black;">Fnac</strong> traité</p>';
  }

  echo $fnac_display;
?>