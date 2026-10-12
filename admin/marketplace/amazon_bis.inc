<?php 

define("AMAZON_XML_CACHE",ROOT_PATH."admin/comparateur/amazonapi/cache");

$tabCommande_amazon=array();

if(!isset($_GET['choixDate'])) $_GET['choixDate']='30j';

if($_GET['choixDate'] == '30j'){
    $startdt=new DateTime();
    $enddt=new DateTime("30 days ago");
    $titre = 'Commande(s) des 30 derniers jours';
}else{
    $startdt=new DateTime("30 days ago");
    $enddt=new DateTime("60 days ago");
    $titre = 'Commande(s) des 30 à 60 derniers jours';
}

$date=date("Y-m-d",strtotime("-60 days"));
$order_list=shell_exec("find ".AMAZON_XML_CACHE." -name '*_order' -newermt ".$date." -type f -printf '%T@\t%p\n' | sort -nr | cut -f 2-");
if(is_null($order_list))
    $order_list=array();
else
    $order_list=array_filter(explode("\n",$order_list));

foreach($order_list as $orderfile){
    $order = new SimpleXMLElement(file_get_contents($orderfile));
    $dt=new DateTime($order->PurchaseDate);
    if($dt>$startdt || $dt<$enddt) continue;

    $Commande_amazon = array(
            'commande_id_amazon'      => $order->AmazonOrderId->__toString(),
            'commande_date'           => $order->PurchaseDate->__toString(),
            'commande_statut_libelle' => $order->OrderStatus->__toString(),
            'facturation_nom'         => $order->BuyerName->__toString(),
            'facturation_mail'        => $order->BuyerEmail->__toString(),
            'facturation_adresse'     => "",
            'facturation_ville'       => "",
            'facturation_cp'          => "",
            'facturation_pays'        => "",
            'facturation_tel'         => "",
            'FulfillmentChannel'      => $order->FulfillmentChannel
    );

    if(!is_null($order->ShippingAddress) && !is_null($order->ShippingAddress->Name)){
        $Commande_amazon['livraison_nom']           = $order->ShippingAddress->Name->__toString();
        $Commande_amazon['livraison_adresse1']      = $order->ShippingAddress->AddressLine1->__toString();
        $Commande_amazon['livraison_adresse2']      = $order->ShippingAddress->AddressLine2->__toString();
        $Commande_amazon['livraison_addresse3']     = $order->ShippingAddress->Addressline3->__toString();
        $Commande_amazon['livraison_cp']            = $order->ShippingAddress->PostalCode->__toString();
        $Commande_amazon['livraison_ville']         = $order->ShippingAddress->City->__toString();
        $Commande_amazon['livraison_pays']          = $order->ShippingAddress->CountryCode->__toString();
        $Commande_amazon['livraison_tel']           = $order->ShippingAddress->Phone->__toString();
    }
    
    $Commande_amazon['livraison_adresse'] = trim($Commande_amazon['livraison_adresse1'].' '.$Commande_amazon['livraison_adresse2']);
    if($Commande_amazon['livraison_nom']==$Commande_amazon['facturation_nom']){
        $Commande_amazon['facturation_adresse']=$Commande_amazon['livraison_adresse'];
        $Commande_amazon['facturation_ville']=$Commande_amazon['livraison_ville'];
        $Commande_amazon['facturation_cp']=$Commande_amazon['livraison_cp'];
        $Commande_amazon['facturation_pays']=$Commande_amazon['livraison_pays'];
        $Commande_amazon['facturation_tel']=$Commande_amazon['livraison_tel'];
    }

    $cachefile=AMAZON_XML_CACHE."/".$order->AmazonOrderId."_items";
    if(file_exists($cachefile)){
        $XMLresponse_bis = new SimpleXMLElement(file_get_contents($cachefile));
//             nice($XMLresponse_bis);
        $count_bis = 0;
        foreach ($XMLresponse_bis->ListOrderItemsResult->OrderItems->OrderItem as $item) {//boucle pour chaque produit de la commande
            if($item->SellerSKU == "5N-H8W9-VD6E") $item->SellerSKU = 3079; //bidouille suite au pblm noel/Wineart
            $sql = "SELECT * FROM article WHERE artid=".$item->SellerSKU;
            $ref_acilyon = $DB_site2->query_first($sql);
            $Commande_amazon["ref_acilyon"][$count_bis] = $ref_acilyon['artcode']; //on recupere les nom de l article
            $Commande_amazon['nbr_art'][$count_bis] = intval($item->QuantityOrdered->__tostring()); //et le nombre
            if($item->QuantityOrdered > 0 && !is_null($item->ItemPrice->Amount))
                $Commande_amazon["prix"][$count_bis] = floatval($item->ItemPrice->Amount->__tostring()) / intval($item->QuantityOrdered->__tostring());
            if(isset($item->ShippingPrice->Amount)) $Commande_amazon["frais_de_port"][$count_bis] = floatval($item->ShippingPrice->Amount->__tostring());
            if(isset($item->ShippingDiscount)) $Commande_amazon["frais_de_port"][$count_bis]-=floatval($item->ShippingDiscount->Amount->__tostring());
            
            ++$count_bis;
            $Commande_amazon["count"] = $count_bis;
        }
    }

    ++$count;
    
    $tabCommande_amazon[$order->PurchaseDate->__toString()]=$Commande_amazon;
}

krsort($tabCommande_amazon);


$alt = 0;
?>
