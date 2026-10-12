<?php

ini_set("display_errors","on");

define("ROOT_PATH",__DIR__."/../../");

include_once(ROOT_PATH."configurations/www.ma-cave-a-vin.fr/config_mysql.php");
include(ROOT_PATH."class/mysql.php");

$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();

require_once(ROOT_PATH.'admin/comparateur/amazonapi/MarketplaceWebService/config.inc.php');
chdir(ROOT_PATH."admin/comparateur/amazonapi/");
include_once(ROOT_PATH."admin/comparateur/amazonapi/MarketplaceWebService/Client.php");

$serviceUrl = "https://mws.amazonservices.fr";

$config = array (
  'ServiceURL' => $serviceUrl,
  'ProxyHost' => null,
  'ProxyPort' => -1,
  'MaxErrorRetry' => 3,
);

$service = new MarketplaceWebService_Client(
     AWS_ACCESS_KEY_ID, 
     AWS_SECRET_ACCESS_KEY, 
     $config,
     APPLICATION_NAME,
     APPLICATION_VERSION);
     

$subid=false;

$fromdate=new datetime();
$fromdate->settime(0,0,0);

$request = new MarketplaceWebService_Model_GetFeedSubmissionListRequest($parameters);
$request = new MarketplaceWebService_Model_GetFeedSubmissionListRequest();
$request->setMerchant(MERCHANT_ID);
$request->setSubmittedFromDate($fromdate);
$response = $service->getFeedSubmissionList($request);

foreach($response->getGetFeedSubmissionListResult()->getFeedSubmissionInfoList() as $feed){
//     var_dump($feed->getSubmittedDate());
//     var_dump($feed);
    if($feed->getFeedType()=="_POST_FLAT_FILE_LISTINGS_DATA_" && $feed->getFeedProcessingStatus()=="_DONE_"){
        $subid=$feed->getFeedSubmissionId();
        
        $filename = '/tmp/report.csv';
        $handle = fopen($filename, 'w+');

        $request = new MarketplaceWebService_Model_GetFeedSubmissionResultRequest();
        $request->setMerchant(MERCHANT_ID);
        $request->setFeedSubmissionId($subid);
        $request->setFeedSubmissionResult($handle);
        $response = $service->getFeedSubmissionResult($request);

        rewind($handle);
        
        $begindata=false;
        while (($data = fgetcsv($handle, 1000, "\t")) !== FALSE) {
            if($begindata && !empty($data[1])){
                $article=$DB_site->query_first("SELECT artcode FROM article WHERE artid=".intval($data[1]));
                $DB_site->query("INSERT INTO market_place_error SET date=".time().", market_place='amazon', artcode='".$article['artcode']."', error='".mysqli_real_escape_string($DB_site->link_id,utf8_encode($data[4]))."'");
            }
            if($data[1]=="SKU") $begindata=true;
        }
        
        fclose($handle);
        unlink($filename);
    }
}

