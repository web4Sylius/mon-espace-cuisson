<?php

include("../../config.php");
include("order.php");

$admember=new member();

if(!$admember->veriflog() && !$admember->logged) exit;

$newsitedate=1238536800;
$when="";
if(isset($_GET['site'])) $when=$_GET['site'];

header("Content-Type: application/force-download");
header("Content-Transfer-Encoding: text/plain\n");
header("Pragma: no-cache");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0, public");
header("Expires: 0");
header("Content-disposition: attachment; filename=compta_marketplace_".($when=="old"?"AS":"NS")."_".date("Y-m-d").".csv");

$header="\"Market Place\",\"N° facture - N° commande\",\"Date\",\"Montant H.T.\",\"Frais de port\",\"Montant H.T. sans port\",\"LAMA H.T.\",\"Cout Market Place\",\"Marge H.T.\",\"Marge H.T. en %\"\n";

$orders=new mp_order();
$list=$orders->list_orders(array("2","3"),"billing_date");

$lastmonth="";
$sumhtwship=0;
$sumhtwoship=0;
$sumship=0;
$sumbuyprice=0;
$summpcost=0;
$summarge=0;
$sumorders=0;

$allhtwship=0;
$allhtwoship=0;
$allship=0;
$allbuyprice=0;
$allmpcost=0;
$allmarge=0;
$allorders=0;

foreach($list as $order){
       if(($when=="old" && $order->billing_date<$newsitedate) || ($when=="new" && $order->billing_date>=$newsitedate) || $when==""){
              $month=date("m",$order->billing_date);
              if($month!=$lastmonth){
                  if($lastmonth!=""){
                       print "\"\",\"".$sumorders." commandes\",\"\",\"".number_format($sumhtwship,"2",",","")."\",\"".number_format($sumship,2,",","")."\",\"".number_format($sumhtwoship,2,",","")."\",\"".number_format($sumbuyprice,2,",","")."\",\"".number_format($summpcost,2,",","")."\",\"".number_format($summarge,2,",","")."\",\"".number_format((100*$summarge/$sumhtwoship),2,",","")."\"\n";
                       $sumhtwship=0;
                       $sumhtwoship=0;
                       $sumship=0;
                       $sumbuyprice=0;
                       $summpcost=0;
                       $summarge=0;
                       $sumorders=0;
                  }
                  print "\n\"".date("F Y",$order->billing_date)."\"\n".$header;
              }
              $shipprice=8.95;
              $buyprice=$order->getbuyprice();
              $totalhtwship=($order->items_price+$order->shipping_price);
              $totalhtwoship=$totalhtwship-$shipprice;
              $mpcost=$order->commission;

              if($order->order_status==3){
                    $totalhtwship=0;
                    $totalhtwoship=0;
                    $buyprice=0;
                    $mpcost=0;
                    $shipprice=0;
              }

              $marge=$totalhtwoship-$buyprice-$mpcost;
              $margepc=0;
              if($totalhtwoship>0) $margepc=100*($totalhtwoship-$buyprice-$mpcost)/$totalhtwoship;
              print "\"".$order->market_place_id."\",\"".$order->billing_number." - ".$order->id.($order->order_status==3?" (annulée)":"")."\",\"".date("d/m/Y",$order->billing_date)."\",\"".number_format($totalhtwship,2,",","")."\",\"".number_format($shipprice,2,",","")."\",\"".number_format($totalhtwoship,2,",","")."\",\"".number_format($buyprice,2,",","")."\",\"".number_format($mpcost,2,",","")."\",\"".number_format($marge,2,",","")."\",\"".number_format($margepc,2,",","")."\"\n";
              $sumhtwship+=$totalhtwship;
              $sumhtwoship+=$totalhtwoship;
              $sumship+=8.95;
              $sumbuyprice+=$buyprice;
              $summpcost+=$mpcost;
              $summarge+=$marge;
              $sumorders++;

              $allhtwship+=$totalhtwship;
              $allhtwoship+=$totalhtwoship;
              $allship+=8.95;
              $allbuyprice+=$buyprice;
              $allmpcost+=$mpcost;
              $allmarge+=$marge;
              $allorders++;

              $lastmonth=$month;
       }
}

print "\n\"\",\"Total\"\n\"\",\"Commandes\",\"\",\"Montant H.T.\",\"Frais de port\",\"Montant H.T. sans port\",\"Lama H.T.\",\"Total cout market place\",\"Marge H.T.\",\"Marge en %\"\n";
print "\"\",\"".$allorders."\",\"\",\"".number_format($allhtwship,"2",",","")."\",\"".number_format($allship,2,",","")."\",\"".number_format($allhtwoship,2,",","")."\",\"".number_format($allbuyprice,2,",","")."\",\"".number_format($allmpcost,2,",","")."\",\"".number_format($allmarge,2,",","")."\",\"".number_format((100*$allmarge/$allhtwoship),2,",","")."\"\n";
              


?>