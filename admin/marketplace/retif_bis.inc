<?php
require_once __DIR__.'/../../secrets.php';

use Mirakl\MMP\Shop\Client\ShopApiClient as Client;
use Mirakl\MCI\Shop\Request\Hierarchy\GetHierarchiesRequest;

$apiUrl = 'https://marketplace.retif.eu/api/orders'; 
$apiKey = RETIF_API_KEY;
$headers = array("Authorization: ".$apiKey);
$curl = curl_init($apiUrl);
curl_setopt($curl, CURLOPT_URL, $apiUrl);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
$resp = curl_exec($curl);
curl_close($curl);


$response = json_decode($resp,TRUE);

$minDate = date('Y-m-d',mktime(0,0,0,date('n')-1,date('j'),date('Y')))."T".date('H:i:s'); //Commandes du dernier mois
$maxDate = date('Y-m-d')."T".date('H:i:s');
$titre = 'Commande(s) des 30 derniers jours';

//si une date a été selectionnée dans le choix de date du ==> au
if(isset($_GET['duDate']) && isset($_GET['auDate'])){
  $sp = explode("/",$_GET['duDate']);
  $du1 = mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
  $du = date('Y-m-d',$du1)."T".date('H:i:s');
  $sp = explode("/",$_GET['auDate']);
  $au1 = mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
  $au = date('Y-m-d',$au1)."T".date('H:i:s');
  if($du1 == $au1){ // si dans le choix des date on choisis le meme jour du = au
   $du =  date('Y-m-d',$du1)."T".date('H:i:s',strtotime("today"));
   $au =  date('Y-m-d',strtotime("tomorrow"))."T".date('H:i:s', strtotime("tomorrow")); 
  }
  $minDate = $du ;
  $maxDate = $au;
  $titre = 'Commande(s) du '.date('Y-m-d',$du1).' au '.date('Y-m-d',$au1) ;
}
//si une option 30j ou 60j a été cochée
if(isset($_GET['choixDate'])){
  if($_GET['choixDate'] == '30j'){
    $minDate = date('Y-m-d',mktime(0,0,0,date('n')-1,date('j'),date('Y')))."T".date('H:i:s');
    $titre = 'Commande(s) des 30 derniers jours';
  }else{
    $minDate = date('Y-m-d',mktime(0,0,0,date('n')-2,date('j'),date('Y')))."T".date('H:i:s');
    $maxDate = date('Y-m-d',mktime(0,0,0,date('n')-1,date('j'),date('Y')))."T".date('H:i:s');
    $titre = 'Commande(s) des 30 au 60 derniers jours';
  }
}

foreach($response as $order){ //boucle pour chaque commande
  if(!is_null($order[0]['created_date'])) {
    $date_commande = $order[0]['created_date'];
    $tabTemp = explode('T', $date_commande);
    $tabTemp2 = explode('+', $tabTemp[1]);
    $tabDate = explode('-', $tabTemp[0]);
    $tabHeure = explode(':', $tabTemp2[0]);
    $dateTri = mktime($tabHeure[0],$tabHeure[1],$tabHeure[2],$tabDate[1],$tabDate[2],$tabDate[0]);
    $tabCommande[$dateTri] = array(
     'commande_id_retif'        => $order[0]['order_id'],
     'commande_date'           => $order[0]['created_date'],
     'commande_statut_libelle' => $order[0]['order_state'],
     'commission'              => $order[0]['total_commission'],

     'livraison_tel_domicile'  => $order[0]['customer']['shipping_address']['phone'],
     'livraison_nom'           => $order[0]['customer']['shipping_address']['lastname'],
     'livraison_prenom'        => $order[0]['customer']['shipping_address']['firstname'],
     'livraison_societe'       => $order[0]['customer']['shipping_address']['company'],
     'livraison_adresse1'      => $order[0]['customer']['shipping_address']['street_1'],
     'livraison_adresse2'      => $order[0]['customer']['shipping_address']['street_2'],
     'livraison_cp'            => $order[0]['customer']['shipping_address']['zip_code'],
     'livraison_ville'         => $order[0]['customer']['shipping_address']['city'],
     'livraison_pays'          => $order[0]['customer']['shipping_address']['country'],
     'facturation_nom'         => $order[0]['customer']['billing_address']['lastname'],
     'facturation_prenom'      => $order[0]['customer']['billing_address']['firstname'],
     'facturation_societe'     => $order[0]['customer']['billing_address']['company'],
     'facturation_adresse1'    => $order[0]['customer']['billing_address']['street_1'],
     'facturation_adresse2'    => $order[0]['customer']['billing_address']['street_2'],
     'facturation_cp'          => $order[0]['customer']['billing_address']['zip_code'],
     'facturation_ville'       => $order[0]['customer']['billing_address']['city'],
     'facturation_pays'        => $order[0]['customer']['billing_address']['country'],
     'facturation_tel_domicile'  => $order[0]['customer']['billing_address']['phone'],
   );
    $count = 0;
      foreach ($order[0]['order_lines'] as $line) { //pour chaque produit de la commande
        $artcode=str_replace("a_","",$line['offer_sku']);
        $sql = "SELECT * FROM article WHERE artcode='".$artcode."'";
        $ref_acilyon = $DB_site2->query_first($sql);
        $tabCommande[$dateTri]['article_qte'][$count] = $line['quantity'];
        $tabCommande[$dateTri]['ref_acilyon'][$count] = $artcode;
        $tabCommande[$dateTri]['ref_eurocave'][$count] = $ref_acilyon['artcode_eurocave'];
        $tabCommande[$dateTri]['prix'][$count] = $line['price'];
        $tabCommande[$dateTri]['frais_de_port'][$count] = $line['shipping_price'];
        $count ++;
      }
      $tabCommande[$dateTri]['count'] = $count;
    }
  }

if(is_null($tabCommande)){
  echo '<script>alert("Aucune commande pour ces dates");</script>';
  $tabCommande = array();
}else{
 krsort($tabCommande);
}

 $alt = 0;
 
 

?>
