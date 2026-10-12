<?php
if($facture->diminutif=="MC") $facture->diminutif="FR";

$returnURL =(defined('SITE_HOST') ? SITE_HOST : 'http://www.mon-espace-cuisson.com').'/DoExpressCheckoutPayment.php?currencyCodeType=EUR&paymentType='.$moyen.'&paymentAmount='.round($totalmontant,2)."&order=".$facture->facturid;
        $cancelURL =$returnURL;
	$nvpstr="&AMT=".round($totalmontant,2)."&PAYMENTACTION=sale&RETURNURL=".$returnURL."&CANCELURL=".$cancelURL."&HDRIMG=https://static.e-junkie.com/sslpic/14692.35c68aeaa0d1006a5e78e5a67045633e.jpg&CURRENCYCODE=EUR&INVNUM=".$facture->facturid."&SHIPTONAME=".urlencode($facture->prenom." ".$facture->nom)."&SHIPTOSTREET=".urlencode($facture->adresse)."&SHIPTOCITY=".urlencode($facture->ville)."&SHIPTOCOUNTRYCODE=".$facture->diminutif."&SHIPTOZIP=".$facture->codepostal."&ADDROVERRIDE=1&SOLUTIONTYPE=Sole";

          $nvpStr = "&BUTTONCODE=HOSTED";
$nvpStr = $nvpStr."&BUTTONTYPE=BUYNOW";
$nvpStr = $nvpStr."&BUTTONIMAGEURL=https://www.paypal.com/en_US/i/btn/btn_paynowCC_LG.gif";
$nvpStr = $nvpStr."&L_BUTTONVAR0=amount=".round($totalmontant,2);
$nvpStr = $nvpStr."&L_BUTTONVAR1=tax=";
$nvpStr = $nvpStr."&L_BUTTONVAR2=shipping=";
$nvpStr = $nvpStr."&L_BUTTONVAR3=handling=";
$nvpStr = $nvpStr."&L_BUTTONVAR4=first_name=".$facture->prenom;
$nvpStr = $nvpStr."&L_BUTTONVAR5=last_name=".$facture->nom;
$nvpStr = $nvpStr."&L_BUTTONVAR6=address1=".$facture->adresse;
$nvpStr = $nvpStr."&L_BUTTONVAR7=address2=";
$nvpStr = $nvpStr."&L_BUTTONVAR8=city=".$facture->ville;
$nvpStr = $nvpStr."&L_BUTTONVAR9=state=";
$nvpStr = $nvpStr."&L_BUTTONVAR10=zip=".$facture->codepostal;
$nvpStr = $nvpStr."&L_BUTTONVAR11=country=".($facture->diminutif == "MC" ? 'FR' : $facture->diminutif);
$nvpStr = $nvpStr."&L_BUTTONVAR12=item_name=Facture ".$facture->numerofacture;
$nvpStr = $nvpStr."&L_BUTTONVAR13=item_number=Facture ".$facture->numerofacture;
$nvpStr = $nvpStr."&L_BUTTONVAR20=night_phone_a=";
$nvpStr = $nvpStr."&L_BUTTONVAR21=night_phone_b=";
$nvpStr = $nvpStr."&L_BUTTONVAR22=night_phone_c=";
$nvpStr = $nvpStr."&L_BUTTONVAR23=notify_url=".(defined('SITE_HOST') ? SITE_HOST : 'http://www.mon-espace-cuisson.com')."/paypal/ipn3.php";
$nvpStr = $nvpStr."&L_BUTTONVAR24=custom=custom1";
$nvpStr = $nvpStr."&L_BUTTONVAR25=cancel_return=".(defined('SITE_HOST') ? SITE_HOST : 'http://www.mon-espace-cuisson.com')."/DoExpressCheckoutPayment.php?currencyCodeType=EUR&paymentType=".$moyen.'&paymentAmount='.round($totalmontant,2)."&order=".$facture->facturid;
$nvpStr = $nvpStr."&L_BUTTONVAR26=paymentaction=sale";
$nvpStr = $nvpStr."&L_BUTTONVAR27=currency_code=EUR";
$nvpStr = $nvpStr."&L_BUTTONVAR29=invoice=";
$nvpStr = $nvpStr."&L_BUTTONVAR30=lc=GB";
$nvpStr = $nvpStr."&L_BUTTONVAR37=bn=EUR";
$nvpStr = $nvpStr."&L_BUTTONVAR38=cbt=cliquer ici ";
$nvpStr = $nvpStr."&L_BUTTONVAR39=address_override=1";
$nvpStr = $nvpStr."&L_BUTTONVAR40=cpp_header_image=http://www.tincheung.net/test/hss/logo.png";
$nvpStr = $nvpStr."&L_BUTTONVAR47=bodyBgImg=http://www.tincheung.net/test/hss/bg.png";
$nvpStr = $nvpStr."&L_BUTTONVAR48=bodyBgColor=#333333";
$nvpStr = $nvpStr."&L_BUTTONVAR58=footerTextColor=#333333";
$nvpStr = $nvpStr."&L_BUTTONVAR59=footerTextlinkColor=#00f00f";
$nvpStr = $nvpStr."&L_BUTTONVAR61=return=http://www.votresite.fr";
$nvpStr = $nvpStr."&L_BUTTONVAR61=paymentaction=authorization";

// Set the curl parameters.
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $API_Endpoint);
curl_setopt($ch, CURLOPT_VERBOSE, 1);

// Turn off the server and peer verification (TrustManager Concept).
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);

// Set the API operation, version, and API signature in the request.
$nvpreq = "METHOD=$methodName&VERSION=$version&PWD=$API_Password&USER=$API_UserName&SIGNATURE=$API_Signature$nvpStr";

// Set the request as a POST FIELD for curl.
curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);

// Get response from the server.
$httpResponse = curl_exec($ch);

if(!$httpResponse) {
          exit("$methodName_ failed: ".curl_error($ch).'('.curl_errno($ch).')');
}

// Extract the response details.
$httpResponseAr = explode("&", $httpResponse);

$httpParsedResponseAr = array();
foreach ($httpResponseAr as $i => $value) {
          $tmpAr = explode("=", $value);
          if(sizeof($tmpAr) > 1) {
                    $httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
          }
}

// Execute the API operation
if("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"])) {
          // Redirect to paypal.com.



          $emaillink = urldecode($httpParsedResponseAr["EMAILLINK"]);
          echo "<br/><h2>Link:</h2><a href=\"".urldecode($emaillink)."\">PAYER</a><br/><br/>";


          exit;
} else  {
          exit('BMCreateButton failed: ' . print_r($httpParsedResponseAr, true));
}

?>