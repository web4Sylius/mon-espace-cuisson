<?php 

ini_set("display_errors","on");
include_once("../host_config_admin.php");
define("ROOT_PATH",$rootpath);

$DB_site=clone $DB_site2;

include_once(ROOT_PATH."admin/comparateur/amazonapi/src/MarketplaceWebServiceOrders/Client.php");
include_once(ROOT_PATH."admin/comparateur/amazonapi/src/MarketplaceWebServiceOrders/Model.php");
include_once(ROOT_PATH."admin/comparateur/amazonapi/src/MarketplaceWebServiceOrders/Model/ListOrdersRequest.php");
include_once(ROOT_PATH."admin/comparateur/amazonapi/src/MarketplaceWebServiceOrders/Model/ListOrderItemsRequest.php");
//     include (ROOT_PATH."admin/comparateur/amazonapi/src/MarketplaceWebServiceOrders/Samples/ListOrdersSample.php");
require_once(ROOT_PATH.'admin/comparateur/amazonapi/src/MarketplaceWebServiceOrders/.config.inc.php');

$serviceUrl = "https://mws-eu.amazonservices.com/Orders/2013-09-01"; //serviceUrl $config et $service sont ici pour creer ListOrderItemsRequest pour recupere les noms des produit

$marketplace_ids=array("ES"=>"A1RKKUPIHCS9HS","UK"=>"A1F83G8C2ARO7P","FR"=>"A13V1IB3VIYZZH","DE"=>"A1PA6795UKMFR9","IT"=>"APJ6JRA9NG5V4");
unset($marketplace_ids["UK"]); // noelia fait le uk à la main

/*if(isset($argv[1]))
    $mpid=$marketplace_ids[$argv[1]];
else
    $mpid=current(array_slice($marketplace_ids,floor(date("i")/10),1));
if(!in_array($mpid,$marketplace_ids)) exit;*/
//$mpid = "A13V1IB3VIYZZH";
foreach ($marketplace_ids as $pays => $mpid) {

    print "Market place ID ".$mpid."<br>";

    $config = array (
        'ServiceURL' => $serviceUrl,
        'ProxyHost' => null,
        'ProxyPort' => -1,
        'ProxyUsername' => null,
        'ProxyPassword' => null,
        'MaxErrorRetry' => 3,
    );

    $service = new MarketplaceWebServiceOrders_Client(
        AWS_ACCESS_KEY_ID,
        AWS_SECRET_ACCESS_KEY,
        APPLICATION_NAME,
        APPLICATION_VERSION,
        $config);

    $request = new MarketplaceWebServiceOrders_Model_ListOrdersRequest();
    $request->setSellerId(MERCHANT_ID);
    // $request->setCreatedAfter("2020-04-02T00:00:00+02:00");
    //$dt=new DateTime("1 hour ago");
    $dt=new DateTime("1 day ago");
    $request->setLastUpdatedAfter($dt->format("c"));
    $request->setMarketplaceId($mpid);

    try {
            $response = $service->ListOrders($request);

    } catch (MarketplaceWebServiceOrders_Exception $ex) {
            echo("Caught Exception: " . $ex->getMessage() . "\n");
            echo("Response Status Code: " . $ex->getStatusCode() . "\n");
            echo("Error Code: " . $ex->getErrorCode() . "\n");
            echo("Error Type: " . $ex->getErrorType() . "\n");
            echo("Request ID: " . $ex->getRequestId() . "\n");
            echo("XML: " . $ex->getXML() . "\n");
            echo("ResponseHeaderMetadata: " . $ex->getResponseHeaderMetadata() . "\n");
            exit;
    }


    $XMLresponse = new SimpleXMLElement($response->toXML());

    foreach ($XMLresponse->ListOrdersResult->Orders->Order as $order) {
        $cachefile=AMAZON_XML_CACHE."/".$order->AmazonOrderId."_items";
        if(!file_exists($cachefile)){
            $orderrequest = new MarketplaceWebServiceOrders_Model_ListOrderItemsRequest();
            $orderrequest->setSellerId(MERCHANT_ID);
            $orderrequest->setAmazonOrderId($order->AmazonOrderId); // ici on set le ListOrderItem avec le l'id que l'on vient de recuperer
            $response = $service->ListOrderItems($orderrequest);
            $XMLresponse_bis = new SimpleXMLElement($response->toXML());
            if(strtolower($order->OrderStatus->__toString())!="pending")
                file_put_contents($cachefile,$response->toXML());
        }
        
        print "Save ".$order->AmazonOrderId."<br>";
        $cachefile=AMAZON_XML_CACHE."/".$order->AmazonOrderId."_order";
        file_put_contents($cachefile,$order->asXML());
    }
    print "<br><br>";
}



?>
