<?php

require_once($_SERVER['DOCUMENT_ROOT']."/paypal/CallerService.php");

if($facture->diminutif=="MC") $facture->diminutif="FR";
$returnURL = urlencode(SITE_HOST.'/paypal/DoExpressCheckoutPayment.php?currencyCodeType=EUR&paymentType='.$moyen.'&paymentAmount='.round($totalmontant,2)."&order=".$facture->facturid);
$cancelURL =$returnURL;

$nvpstr="&AMT=".round($totalmontant,2)."&PAYMENTACTION=sale&RETURNURL=".$returnURL."&CANCELURL=".$cancelURL."&HDRIMG=https://static.e-junkie.com/sslpic/120479.621fa5690eb60cd91d32a8b9f8aed538.jpg&CURRENCYCODE=EUR&INVNUM=".$facture->facturid."&SHIPTONAME=".urlencode($facture->prenom." ".$facture->nom)."&SHIPTOSTREET=".urlencode($facture->adresse)."&SHIPTOCITY=".urlencode($facture->ville)."&SHIPTOCOUNTRYCODE=".$facture->diminutif."&SHIPTOZIP=".$facture->codepostal."&ADDROVERRIDE=1&SOLUTIONTYPE=Sole";

$resArray=hash_call("SetExpressCheckout",$nvpstr);
$_SESSION['reshash']=$resArray;
$ack = strtoupper($resArray["ACK"]);

if($ack=="SUCCESS"){
     $token = urldecode($resArray["TOKEN"]);
     $lienfinal = PAYPAL_URL.$token;
     $pagepaiementpaypal="<a href=".$lienfinal."><img border=\"0\" width=\"91\" alt=\"Marque d'acceptation\" src=\"https://www.paypal.com/fr_FR/FR/i/logo/PayPal_mark_180x113.gif\"></a>";
     header("location:".PAYPAL_URL.$token);
}
