<?php
require_once __DIR__.'/../../secrets.php';
/*** Récupération liste de commandes LaRedoute ***/

$vendeur_redoute_id = '11604'; //Num?ro vendeur ? ajouter quand on nous le transmet

//Data, connection, auth
$soapUrl = "https://api-gateway.laredoute.com/prod/seller/orders/1.3"; // asmx URL of WSDL
$login = LAREDOUTE_MP_LOGIN;
$password = LAREDOUTE_MP_PASSWORD;

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
$apikey_commande = LAREDOUTE_MP_API_KEY_COMMANDE;

$xml_post_string = ""
     . "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:ns=\"http://RedouteFrance/seller/order/1.2\">".$passage_ligne
          . "<soapenv:Header/>".$passage_ligne
          . "<soapenv:Body>".$passage_ligne
               . "<ns:RetrieveByPeriodRequest>".$passage_ligne
                    . "<apikey>".$apikey_commande."</apikey>".$passage_ligne
                    . "<Period>".$passage_ligne
                         . "<minDate>".$minDate."</minDate>".$passage_ligne
                         . "<maxDate>".$maxDate."</maxDate>".$passage_ligne
                    . "</Period>".$passage_ligne
               . "</ns:RetrieveByPeriodRequest>".$passage_ligne
          . "</soapenv:Body>".$passage_ligne
     . "</soapenv:Envelope>";

$headers = array(
    "Content-type: text/xml;charset=\"utf-8\"",
                        "Accept: text/xml",
                        "Cache-Control: no-cache",
                        "Pragma: no-cache",
                        "SOAPAction: http://RedouteFrance/seller/order/1.2/RetrieveByPeriodRequest", 
                        "Content-length: ".strlen($xml_post_string),
 ); //SOAPAction: your op URL

$url = $soapUrl;
//$fp = fopen(dirname(__FILE__).'/error-curl-redoute.txt', 'w');

// PHP cURL  for https connection
$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERPWD, $login.":".$password);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//curl_setopt($ch, CURLOPT_STDERR, $fp);

$response = curl_exec($ch);

curl_close($ch);
//on parse la réponse pour récupérer la partie "commande"
$response1 = str_replace("<S:Body>", "",$response);
$response2 = str_replace("</S:Body>","",$response1);
$response3 = str_replace("<S:Envelope xmlns:S=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:new=\"http://RedouteFrance/seller/order/1.2\" xmlns:ns=\"http://Redcats/Order/SellerOrder/2.0/RetrieveSellerOrder/6.0\" xmlns:ns1=\"http://Redcats/Order/SellerOrder/2.0/xsdHUB/6.0\">", "", $response2);
$response4 = str_replace("</S:Envelope>", "", $response3);
$response5 = str_replace("<new:RetrieveByPeriodRequestResponse>", "", $response4);
$response6 = str_replace("</new:RetrieveByPeriodRequestResponse>", "", $response5);
$response7 = str_replace("ns1:", "", $response6);

$simpleXMLObject = @simplexml_load_string($response7);


$pasdispo = 0;
// nice($simpleXMLObject); //affiche tous les parametres récupérés par API (pareil pour tous les autres fichiers bis)
if ($simpleXMLObject != null)
{
  foreach($simpleXMLObject->Order as $order){

      $date_commande = (string)$order->attributes()->OrderDate;
      $tabTemp = explode('T', $date_commande);
      $tabTemp2 = explode('+', $tabTemp[1]);
      $tabDate = explode('-', $tabTemp[0]);
      $tabHeure = explode(':', $tabTemp2[0]);
      $dateTri = mktime($tabHeure[0],$tabHeure[1],$tabHeure[2],$tabDate[1],$tabDate[2],$tabDate[0]);
      
      $tabCommande_laredoute[$dateTri] = array(
           'commande_id_laredoute'   => $order->attributes()->OrderID,
           'commande_date'           => $order->attributes()->OrderDate,
           'commande_devise'         => $order->attributes()->CurrencyCode,
           'commande_statut_id'      => $order->OrderLine->attributes()->OrderLineID,
           'commande_statut_libelle' => $order->OrderLine->attributes()->StatusCode,
           'commande_statut_date'    => $order->OrderLine->attributes()->StatusDate,
           'article_code_laredoute'  => $order->OrderLine->Item->attributes()->ItemRedouteID,
           'article_ean_acilyon'     => $order->OrderLine->Item->attributes()->ItemStandardID,
           'article_ref_laredoute'   => $order->OrderLine->Item->Offer->attributes()->OfferID,
          
           'livraison_tel'           => $order->OrderLine->Item->Offer->Delivery->PhoneNumber,
           'livraison_civilite'      => $order->OrderLine->Item->Offer->Delivery->DeliveryAddress->Title,
           'livraison_prenom_nom'    => $order->OrderLine->Item->Offer->Delivery->DeliveryAddress->Name1,
           'livraison_societe'       => $order->OrderLine->Item->Offer->Delivery->DeliveryAddress->Name2,
           'livraison_adresse1'      => $order->OrderLine->Item->Offer->Delivery->DeliveryAddress->Address1,
           'livraison_adresse2'      => $order->OrderLine->Item->Offer->Delivery->DeliveryAddress->Address2,
           'livraison_cp'            => $order->OrderLine->Item->Offer->Delivery->DeliveryAddress->ZIPCode,
           'livraison_ville'         => $order->OrderLine->Item->Offer->Delivery->DeliveryAddress->City,
           'livraison_pays'          => $order->OrderLine->Item->Offer->Delivery->DeliveryAddress->CountryCode,
           'livraison_email'         => $order->OrderLine->Item->Offer->Delivery->AliasEmail,
           'facturation_civilite'    => $order->BillingAddress->Title,
           'facturation_prenom_nom'  => $order->BillingAddress->Name1,
           'facturation_societe'     => $order->BillingAddress->Name2,
           'facturation_adresse1'    => $order->BillingAddress->Address1,
           'facturation_adresse2'    => $order->BillingAddress->Address2,
           'facturation_cp'          => $order->BillingAddress->ZIPCode,
           'facturation_ville'       => $order->BillingAddress->City,
           'facturation_pays'        => $order->BillingAddress->CountryCode,
           'frais_de_port'           => $order->OrderLine->Item->Offer->Delivery->DeliveryFee
      );
      
      foreach($order->OrderLine->Item->Offer->Delivery->PhoneNumber as $phonenumber){
            switch($phonenumber->attributes()->PhoneType){
                case "Home":
                $tabCommande_laredoute[$dateTri]['livraison_tel_domicile']="0".$phonenumber[0];
                $tabCommande_laredoute[$dateTri]['facturation_tel_domicile']="0".$phonenumber[0];
                break;
                
                case "Mobile":
                $tabCommande_laredoute[$dateTri]['livraison_tel_mobile']="0".$phonenumber[0];
                $tabCommande_laredoute[$dateTri]['facturation_tel_mobile']="0".$phonenumber[0];
                break;
            }
      }
  $count = 0;
  foreach ($order->OrderLine as $product){
     $tabCommande_laredoute[$dateTri]['article_ref_acilyon'][$count] = $product->Item->Offer->attributes()->SellerOfferID;
      $tabTemp = explode('.',$product->Quantity);
      $tabCommande_laredoute[$dateTri]['article_qte'][$count] = $tabTemp[0];
      $tabCommande_laredoute[$dateTri]['prix_article'][$count] = $product->Item->Offer->SellingPrice;
      $tabCommande_laredoute[$dateTri]['article_ecotaxe'][$count] = $product->Item->Offer->WEEEFee;
     ++$count;
     $tabCommande_laredoute[$dateTri]['count'] = $count;
   } 
 }

 
}
else
{
  $pasdispo = 1;
}
krsort($tabCommande_laredoute);

 $alt = 0;
 ?>
 
