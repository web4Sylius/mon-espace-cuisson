<?php

require_once "admin_global.php";

$tabCommande_amazon = array();
$count = 0;
////**************Modification faite par Ariel le 11/10/2018 Tâche SB#4397 ***************/////
define("AMAZON_XML_CACHE",ROOT_PATH."admin/comparateur/amazonapi/cache");

$tabCommande_amazon=array();
$startdt=new datetime("-60 days");

$order_list=shell_exec("find ".AMAZON_XML_CACHE." -name '*_order' -newermt ".$startdt->format("Y-m-d")." -type f -printf '%T@\t%p\n' | sort -nr | cut -f 2-");
if(is_null($order_list))
    $order_list=array();
else
    $order_list=array_filter(explode("\n",$order_list));

foreach($order_list as $orderfile){
   $order = new SimpleXMLElement(file_get_contents($orderfile));
   $dt=new DateTime($order->PurchaseDate);
   if($dt<$startdt) continue;
    
   $tabCommande_amazon[$count] = array(
        'commande_statut_libelle'      => $order->OrderStatus,
        'commande_id_amazon'           => $order->AmazonOrderId
   );
   ++$count;
}
//////////////*******************************/////////////////////


 /*$XMLresponse = new SimpleXMLElement($response);
   foreach ($XMLresponse->ListOrdersResult->Orders->Order as $order) { //ici on recupere les informations voulu    
      $tabCommande_amazon[$count] = array(
           'commande_statut_libelle'      => $order->OrderStatus,
           'commande_id_amazon'           => $order->AmazonOrderId->__tostring()
      );
    ++$count;
      
 }*/

 krsort($tabCommande_amazon);

  $nbr_ToShip_amazon = 0;
  $nbr_Atraiter_amazon = 0;

  $amazon_url = BACK_URL."/marketplace/index.php?marketplace=amazon";

  foreach ($tabCommande_amazon as $la_commande) {//boucle pour compter le nombre de commande a traiter
    $commande_site = $DB_site2->query_first("SELECT * FROM facture WHERE mp_facturid='".$la_commande['commande_id_amazon']."'");

    if ($commande_site == "")
    {
      if($la_commande['commande_statut_libelle'] != "Canceled") // ajout de la condition le 11/10/18 par Ariel
      {
        ++$nbr_Atraiter_amazon;
      }
    }
    else
    {
      if ($la_commande['commande_statut_libelle'] == "Unshipped")
      {
        ++$nbr_ToShip_amazon;
      }
    }
    
  }

  if ($nbr_Atraiter_amazon != 0 || $nbr_ToShip_amazon != 0)
  {
    $amazon_display = '<p><strong style="color:black;">Amazon </strong>';
    if ($nbr_Atraiter_amazon != 0)//display en fonction de si il ya des commandes a traiter
    {
      $amazon_display = $amazon_display.'<a style="color:red;" target="_blank" href="'.$amazon_url.'"> à traiter ('.$nbr_Atraiter_amazon.') </a>';
      if ($nbr_ToShip_amazon != 0)//display en fonction de si il ya des commandes a traiter
      {
        $amazon_display = $amazon_display.'|';
      }
    }
    if ($nbr_ToShip_amazon != 0)//display en fonction de si il ya des commandes a traiter
    {
      $amazon_display = $amazon_display.'<a style="color:#ff9944;" target="_blank" href="'.$amazon_url.'"> à expédier ('.$nbr_ToShip_amazon.')</a>';
    }
    $amazon_display = $amazon_display.'</p>';
  }
 
  else
  {
    //$amazon_display = '<p style="color:#008000;"><strong style="color:black;">Amazon</strong> traité</p>';
    $amazon_display = '<p style="color:#008000;"><strong style="color:black;">Amazon</strong>traité</p>';
  }

  echo $amazon_display;

?>
