
<?php
require_once __DIR__.'/../../secrets.php';
   
   $user = CDISCOUNT_MP_API_LOGIN;
    $pass = CDISCOUNT_MP_API_PASSWORD;
    $auto = $user.":".$pass;
    $authentication = base64_encode($auto); 
    $httpheader = array("Authorization: Basic ".$authentication);
    $itemsFeedURL = "https://sts.cdiscount.com/users/httpIssue.svc/?realm=https://wsvc.cdiscount.com/MarketplaceAPIService.svc";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $itemsFeedURL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_MAXREDIRS,5);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
    $response = curl_exec($ch);
    curl_close($ch);
   
   
    $xmlResponse = simplexml_load_string(trim($response));

    $token = $xmlResponse;
    $blabla = "POST http://{ENV}/MarketplaceAPIService.svc HTTP/1.1
Accept-Encoding: gzip,deflate
Content-Type: text/xml;charset=UTF-8
SOAPAction: \"http://www.cdiscount.com/IMarketplaceAPIService/GetOrderList\"";

$begin = date("Y-m-d", strtotime('-1 month'))."T00:00:00.00";
$end = date("Y-m-d")."T23:59:59.99";
$titre = 'Commande(s) des 30 derniers jours';

//si une date a été selectionnée dans le choix de date du ==> au
if(isset($_GET['duDate']) && isset($_GET['auDate'])){
  $sp = explode("/",$_GET['duDate']);
  $du1 = mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
  $du = date('Y-m-d',$du1)."T00:00:00.00";
  $sp = explode("/",$_GET['auDate']);
  $au1 = mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
  $au = date('Y-m-d',$au1)."T00:00:00.00";
  if($du1 == $au1){ // si dans le choix des date on choisis le meme jour du = au
   $du =  date('Y-m-d',$du1)."T00:00:00.00";
   $au =  date('Y-m-d',($au1+86400))."T00:00:00.00"; 
  }
  $begin = $du ;
  $end = $au;
  $titre = 'Commande(s) du '.date('Y-m-d',$du1).' au '.date('Y-m-d',$au1) ;
}
//si une option 30j ou 60j a été cochée
if(isset($_GET['choixDate'])){
  if($_GET['choixDate'] == '30j'){
    $begin = date('Y-m-d',mktime(0,0,0,date('n')-1,date('j'),date('Y')))."T00:00:00.00";
    $titre = 'Commande(s) des 30 derniers jours';
  }else{
    $begin = date('Y-m-d',mktime(0,0,0,date('n')-2,date('j'),date('Y')))."T00:00:00.00";
    $end = date('Y-m-d',mktime(0,0,0,date('n')-1,date('j'),date('Y')))."T00:00:00.00";
    $titre = 'Commande(s) des 30 au 60 derniers jours';
  }
}

   

   $envelope = "
<s:Envelope xmlns:s=\"http://schemas.xmlsoap.org/soap/envelope/\">
    <s:Body>
        <GetOrderList xmlns=\"http://www.cdiscount.com\">
            <headerMessage xmlns:a=\"http://schemas.datacontract.org/2004/07/Cdiscount.Framework.Core.Communication.Messages\" xmlns:i=\"http://www.w3.org/2001/XMLSchema-instance\">
                <a:Context>
                    <a:CatalogID>1</a:CatalogID>
                    <a:CustomerPoolID>1</a:CustomerPoolID>
                    <a:SiteID>100</a:SiteID>
                </a:Context>
                <a:Localization>
                    <a:Country>Fr</a:Country>
                    <a:Currency>Eur</a:Currency>
                    <a:DecimalPosition>2</a:DecimalPosition>
                    <a:Language>Fr</a:Language>
                </a:Localization>
                <a:Security>
                    <a:DomainRightsList i:nil=\"true\" />
                    <a:IssuerID i:nil=\"true\" />
                    <a:SessionID i:nil=\"true\" />
                    <a:SubjectLocality i:nil=\"true\" />
                    <a:TokenId>".$token."</a:TokenId>
                    <a:UserName i:nil=\"true\" />
                </a:Security>
                <a:Version>1.0</a:Version>
            </headerMessage>
            <orderFilter xmlns:i=\"http://www.w3.org/2001/XMLSchema-instance\">
                <BeginCreationDate>".$begin."</BeginCreationDate>
                <EndCreationDate>".$end."</EndCreationDate>
                <FetchOrderLines>true</FetchOrderLines>
                 <States>
                    <OrderStateEnum>CancelledByCustomer</OrderStateEnum>
                    <OrderStateEnum>WaitingForSellerAcceptation</OrderStateEnum>
                    <OrderStateEnum>AcceptedBySeller</OrderStateEnum>
                    <OrderStateEnum>PaymentInProgress</OrderStateEnum>
                    <OrderStateEnum>WaitingForShipmentAcceptation</OrderStateEnum>
                    <OrderStateEnum>Shipped</OrderStateEnum>
                    <OrderStateEnum>RefusedBySeller</OrderStateEnum>
                    <OrderStateEnum>AutomaticCancellation</OrderStateEnum>
                    <OrderStateEnum>PaymentRefused</OrderStateEnum>
                    <OrderStateEnum>ShipmentRefusedBySeller</OrderStateEnum>
                    <OrderStateEnum>RefusedNoShipment</OrderStateEnum>
                </States>
            </orderFilter>
        </GetOrderList>
    </s:Body>
</s:Envelope>";

$callHeaderHttp = array('Content-Type: text/xml;charset=UTF-8', 'SOAPAction:'.'"http://www.cdiscount.com/IMarketplaceAPIService/GetOrderList"');
// Dump de l'appel SOAP
$tuCurl = curl_init();
curl_setopt($tuCurl, CURLOPT_URL, "https://wsvc.cdiscount.com/MarketplaceAPIService.svc"); 
curl_setopt($tuCurl, CURLOPT_VERBOSE, false);
curl_setopt($tuCurl, CURLOPT_HEADER, true);
curl_setopt($tuCurl, CURLOPT_POST, true);
curl_setopt($tuCurl, CURLOPT_SSLVERSION, 1); //parametrer en SSLVersion1 (contrairement à la doc qui dit de mettre la version 3)
curl_setopt($tuCurl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($tuCurl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($tuCurl, CURLOPT_POSTFIELDS, $envelope);
curl_setopt($tuCurl, CURLOPT_HTTPHEADER, $callHeaderHttp);
$response = curl_exec($tuCurl);

$response_tab = explode('</TokenId>', $response); //on parse pour récupérer 
$response1 = str_replace('</GetOrderListResult></GetOrderListResponse></s:Body></s:Envelope>', "", $response_tab[1]);
$response1 = str_replace('i:nil="true"', "", $response1); 
// Libération des ressources
curl_close($tuCurl);

$xmlResponse = simplexml_load_string(trim($response1));
//nice($xmlResponse);
$pasdispo = 0;
    
if ($xmlResponse != null)
{
  foreach($xmlResponse->Order as $order)
  {

      $date_commande = (string)$order->CreationDate;
      $tabTemp = explode('T', $date_commande);
      $tabTemp2 = explode('+', $tabTemp[1]);
      $tabDate = explode('-', $tabTemp[0]);
      $tabHeure = explode(':', $tabTemp2[0]);
      $dateTri = mktime($tabHeure[0],$tabHeure[1],$tabHeure[2],$tabDate[1],$tabDate[2],$tabDate[0]);
      $shippingcharges=$order->ShippedTotalShippingCharges->__tostring()!="0"?$order->ShippedTotalShippingCharges->__tostring():$order->ValidatedTotalShippingCharges->__tostring();
      $tabCommande_cdiscount[$dateTri] = array(
           'commande_id_cdiscount'   => $order->OrderNumber,
           'commande_date'           => $order->CreationDate,
           'commande_statut_libelle' => $order->OrderState,

            'livraison_societe'    => $order->BillingAddress->CompanyName,
            'livraison_prenom_nom' => $order->ShippingAddress->FirstName.' '.$order->ShippingAddress->LastName,
            'livraison_adresse'    => $order->ShippingAddress->Street,
            'livraison_cp'         => $order->ShippingAddress->ZipCode,
            'livraison_ville'      => $order->ShippingAddress->City,
            'livraison_pays'       => $order->ShippingAddress->Country,

            'facturation_societe'    => $order->BillingAddress->CompanyName,
            'facturation_prenom_nom' => $order->BillingAddress->FirstName.' '.$order->BillingAddress->LastName,
            'facturation_adresse'    => $order->BillingAddress->Street,
            'facturation_cp'         => $order->BillingAddress->ZipCode,
            'facturation_ville'      => $order->BillingAddress->City,
            'facturation_pays'       => $order->BillingAddress->Country,
            'shippingcharges'        => $shippingcharges,
            'facturation_mobile'     => $order->Customer->MobilePhone,
            'facturation_domicile'   => $order->Customer->Phone,
            'commission'             => $order->SiteCommissionValidatedAmount,
      );

      if($order->ShippingAddress->FirstName != '' && $order->ShippingAddress->LastName != '')
      {
          $tabCommande_cdiscount[$dateTri]['livraison_prenom']   = $order->ShippingAddress->FirstName;
          $tabCommande_cdiscount[$dateTri]['livraison_nom']      = $order->ShippingAddress->LastName;
          $tabCommande_cdiscount[$dateTri]['facturation_prenom'] = $order->BillingAddress->FirstName;
          $tabCommande_cdiscount[$dateTri]['facturation_nom']    = $order->BillingAddress->LastName;
      }
      else
      {
          $tabCommande_cdiscount[$dateTri]['livraison_prenom']   = '';
          $tabCommande_cdiscount[$dateTri]['livraison_nom']      = '';
          $tabCommande_cdiscount[$dateTri]['facturation_prenom'] = '';
          $tabCommande_cdiscount[$dateTri]['facturation_nom']    = '';
      }

    $count = 0;
    foreach ($order->OrderLineList->OrderLine as $product)
    {
      if ($product->SellerProductId != "")
      {
           $sql2 = "SELECT * FROM article WHERE artid=".$product->SellerProductId;
           $ref_acilyon = $DB_site2->query_first($sql2);
          $tabCommande_cdiscount[$dateTri]['article_ref_acilyon'][$count] = $ref_acilyon['artcode'];
          $tabCommande_cdiscount[$dateTri]['article_qte'][$count] = $product->Quantity->__tostring();
          if ($product->Quantity != '0')
          {
            $tabCommande_cdiscount[$dateTri]['prix_article'][$count] = floatval($product->PurchasePrice->__toString()) / intval($product->Quantity->__toString());
          }
          else
          {
            $tabCommande_cdiscount[$dateTri]['prix_article'][$count] = 0;
          }
         ++$count;
         $tabCommande_cdiscount[$dateTri]['count'] = $count;
      }
      else
      {
        if ($product->ProductId == "INTERETBCA")
        {
          $tabCommande_cdiscount[$dateTri]['INTERETBCA'] = $product->PurchasePrice->__tostring();
        }
      }
    }
  }
  if(is_null($tabCommande_cdiscount)){
    echo '<script>alert("Aucune commande pour ces dates");</script>';
    $tabCommande_cdiscount = array();
  }
}
else
{
  $pasdispo = 1;
}

?>