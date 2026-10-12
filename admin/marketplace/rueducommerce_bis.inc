
<?php

ini_set("display_errors",1);


$dateDebut = date("Y-m-d",strtotime("-1 month"));
$dateFin = date("Y-m-d");

$baseurl="https://marchands.rueducommerce.fr/merchant/syndication/orders/ma%20cave/";

$listurl=array(
"encours"=>'processing_orders_248a0a1bd20c59d74ef20eef3e365d578558d8fa.xml',
"annule"=>'cancelled_orders_248a0a1bd20c59d74ef20eef3e365d578558d8fa.xml?start='.$dateDebut.'&end='.$dateFin,
"rest"=>'restocking_orders_248a0a1bd20c59d74ef20eef3e365d578558d8fa.xml',
"ship"=>'shipped_orders_248a0a1bd20c59d74ef20eef3e365d578558d8fa.xml?start='.$dateDebut.'&end='.$dateFin,
"new"=>'new_orders_248a0a1bd20c59d74ef20eef3e365d578558d8fa.xml'
);

if(isset($requests)){
    foreach($listurl as $type=>$url)
        if(!in_array($type,$requests)) unset($listurl[$type]);
}

$orders=array();

foreach($listurl as $type=>$url){
    $o=loadxmlfile($baseurl.$url,$orders);
    if($o===false)
        $pasdispo=1;
    else
        if(count($o)>0) $orders[$type]=$o;
}



function loadxmlfile($url,&$orders){
// echo $url . '<br>';
    // data to be sent, please refer to xml_echange_marchands.sample.xml. Note that array's key must be requests_xml.
$flux  =  array('requests_xml'=>'<?xml version="1.0" encoding="UTF-8"?>
  <mmie version="2.0">
    <orders>
      <sent morid="MOR-01M1234" modid="MOD-12141M987" datetime="0000-00-00T00:00:00Z">
        <tracking></tracking>
      </sent>
      <sent morid="MOR-01M1234" modid="MOD-53544M988" datetime="0000-00-00T00:00:00Z">
        <tracking></tracking>
      </sent>

    </orders>
  </mmie>');


    $oCurlNew = curl_init();
    curl_setopt($oCurlNew, CURLOPT_HEADER, false);
    curl_setopt($oCurlNew, CURLOPT_POST, TRUE);
    curl_setopt($oCurlNew, CURLOPT_POSTFIELDS, $flux);
    curl_setopt($oCurlNew, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($oCurlNew, CURLOPT_URL, $url );
    curl_setopt($oCurlNew, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($oCurlNew, CURLOPT_SSL_VERIFYPEER, 0);
    $iResultNew =  curl_exec($oCurlNew);
    $httpCode = curl_getinfo($oCurlNew, CURLINFO_HTTP_CODE);
    curl_close($oCurlNew);

    if($httpCode==200){ 
        $xmlNew = new SimpleXMLElement($iResultNew);
        return $xmlNew->order;
    }
    return false;
}

?>