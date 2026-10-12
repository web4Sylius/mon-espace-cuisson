<?php
require_once __DIR__.'/../../secrets.php';

function do_post_request($url, $data)
{
     $ch = curl_init();
     // Depending on your system, you may add other options or modify the following ones.
     curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
     curl_setopt($ch, CURLOPT_POST, 1);
     curl_setopt($ch, CURLOPT_URL, $url);
     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
     curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
     curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
     $response = curl_exec($ch);
     curl_close($ch);
     return ($response);
}

$partner_id = "8AC7076B-6D33-4E1F-903C-D558F35C3FD1";
$shop_id = "1F53469C-1EB1-0B9F-6E25-82E40F685BA5";
$key = FNAC_API_KEY;
$url = "https://vendeur.fnac.com/api.php/";
// STEP 1: Authenticate to the API 
// Generate the authentication request
$auth_request_xml = ""
. "<?xml version=\"1.0\" encoding=\"utf-8\"?>" . $passage_ligne
. "<auth xmlns=\"http://www.fnac.com/schemas/mp-dialog.xsd\">" . $passage_ligne
  . "<partner_id>" . $partner_id . "</partner_id>" . $passage_ligne
  . "<shop_id>" . $shop_id . "</shop_id>" . $passage_ligne
  . "<key>" . $key . "</key>" . $passage_ligne
."</auth>";


$response = do_post_request($url."auth", $auth_request_xml); //on recupere les donne API
$xmlResponse = simplexml_load_string(trim($response));


$token = $xmlResponse->token;

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




// Retrieve 10 orders that have been set in ToShip state between 2 given dates --> on parse la reponse
$xml_post_string = ""
. "<?xml version=\"1.0\" encoding=\"utf-8\"?>" . $passage_ligne
. "<orders_query results_count=\"100\" partner_id=\"".$partner_id."\" shop_id=\"".$shop_id."\" token=\"".$token."\" xmlns=\"http://www.fnac.com/schemas/mp-dialog.xsd\">" . $passage_ligne
  . "<paging>1</paging>" . $passage_ligne
  . "<date type=\"UpdatedAt\">" . $passage_ligne
    . "<min>".$minDate."</min>" . $passage_ligne
    . "<max>".$maxDate."</max>" . $passage_ligne
  . "</date>" . $passage_ligne
. "</orders_query>";

$response = do_post_request($url."orders_query", $xml_post_string);

$xmlResponse = simplexml_load_string(trim($response));
//nice($xmlResponse);
foreach($xmlResponse->order as $order){ //boucle pour chaque commande

      $date_commande = (string)$order->created_at;
      $tabTemp = explode('T', $date_commande);
      $tabTemp2 = explode('+', $tabTemp[1]);
      $tabDate = explode('-', $tabTemp[0]);
      $tabHeure = explode(':', $tabTemp2[0]);
      $dateTri = mktime($tabHeure[0],$tabHeure[1],$tabHeure[2],$tabDate[1],$tabDate[2],$tabDate[0]);
      $sql = "SELECT * FROM article WHERE artid=".$order->order_detail->offer_seller_id;
      $ref_acilyon = $DB_site2->query_first($sql);
      $tabCommande[$dateTri] = array(
           'commande_id_fnac'        => $order->order_id,
           'commande_date'           => $order->created_at,
           'commande_statut_libelle' => $order->state,
           'commission'              => $order->fees,
           
           'livraison_tel_domicile'  => $order->shipping_address->phone,
           'livraison_tel_mobile'    => $order->shipping_address->mobile,
           'livraison_nom'           => $order->shipping_address->lastname,
           'livraison_prenom'        => $order->shipping_address->firstname,
           'livraison_societe'       => $order->shipping_address->company,
           'livraison_adresse1'      => $order->shipping_address->address1,
           'livraison_adresse2'      => $order->shipping_address->address2,
           'livraison_addresse3'     => $order->shipping_address->address3,
           'livraison_cp'            => $order->shipping_address->zipcode,
           'livraison_ville'         => $order->shipping_address->city,
           'livraison_pays'          => $order->shipping_address->country,
           'facturation_nom'         => $order->billing_address->lastname,
           'facturation_prenom'      => $order->billing_address->firstname,
           'facturation_societe'     => $order->billing_address->company,
           'facturation_adresse1'    => $order->billing_address->address1,
           'facturation_adresse2'    => $order->billing_address->address2,
           'facturation_adresse3'    => $order->billing_address->address3,
           'facturation_cp'          => $order->billing_address->zipcode,
           'facturation_ville'       => $order->billing_address->city,
           'facturation_pays'        => $order->billing_address->country,
           'facturation_tel_domicile'  => $order->billing_address->phone,
           'facturation_tel_mobile'    => $order->billing_address->mobile,
           
           
      );
      $count = 0;
      foreach ($order->order_detail as $detail) { //pour chaque produit de la commande
          $sql = "SELECT * FROM article WHERE artid=".$order->order_detail[$count]->offer_seller_id;
          $ref_acilyon = $DB_site2->query_first($sql);
          $tabCommande[$dateTri]['article_qte'][$count] = $detail->quantity;
          $tabCommande[$dateTri]['ref_acilyon'][$count] = $ref_acilyon['artcode'];
          $tabCommande[$dateTri]['ref_eurocave'][$count] = $ref_acilyon['artcode_eurocave'];
          $tabCommande[$dateTri]['prix'][$count] = $detail->price;
          $tabCommande[$dateTri]['frais_de_port'][$count] = $detail->shipping_price;
          $count ++;
      }
      $tabCommande[$dateTri]['count'] = $count;

 }

if(is_null($tabCommande)){
  echo '<script>alert("Aucune commande pour ces dates");</script>';
  $tabCommande = array();
}else{
 krsort($tabCommande);
}

 $alt = 0;
 
 

?>
