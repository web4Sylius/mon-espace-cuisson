<?php

function PPHttpPost($methodName_, $nvpStr_) {
	global $environment;
          global $API_UserName;
          global $API_Password;
          global $environment;
          global $API_Endpoint;
          global $API_Signature;

	$version = urlencode('82.0');

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
	$nvpreq = "METHOD=$methodName_&VERSION=$version&PWD=$API_Password&USER=$API_UserName&SIGNATURE=$API_Signature$nvpStr_";

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

	if((0 == sizeof($httpParsedResponseAr)) || !array_key_exists('ACK', $httpParsedResponseAr)) {
		exit("Invalid HTTP Response for POST request($nvpreq) to $API_Endpoint.");
	}

	return $httpParsedResponseAr;
}

if(empty($_GET)){
     // Add request-specific fields to the request string.
     $returnURL =(defined('SITE_HOST') ? SITE_HOST : 'http://www.mon-espace-cuisson.com').'/paypal/DoExpressCheckoutPayment.php?currencyCodeType=EUR&paymentType='.$moyen.'&paymentAmount='.round($totalmontant,2)."&order=".$facture->facturid;
             $cancelURL =$returnURL;
               $nvpstr="&AMT=".round($totalmontant,2)."&PAYMENTACTION=sale&RETURNURL=".$returnURL."&CANCELURL=".$cancelURL."&HDRIMG=https://static.e-junkie.com/sslpic/14692.35c68aeaa0d1006a5e78e5a67045633e.jpg&CURRENCYCODE=EUR&INVNUM=".$facture->facturid."&SHIPTONAME=".urlencode($facture->prenom." ".$facture->nom)."&SHIPTOSTREET=".urlencode($facture->adresse)."&SHIPTOCITY=".urlencode($facture->ville)."&SHIPTOCOUNTRYCODE=".$facture->diminutif."&SHIPTOZIP=".$facture->codepostal."&ADDROVERRIDE=1&SOLUTIONTYPE=Sole";

     $nvpStr = "&BUTTONCODE=ENCRYPTED";
     $nvpStr .= "&BUTTONTYPE=BUYNOW";
     $nvpStr .= "&L_BUTTONVAR0=amount=".round($totalmontant,2);
     $nvpStr .= "&L_BUTTONVAR1=currency_code=EUR";
     $nvpStr .= "&L_BUTTONVAR3=paymentaction=sale";
     $nvpStr .= "&L_BUTTONVAR4=image_url=".(defined('SITE_HOST') ? SITE_HOST : 'http://www.mon-espace-cuisson.com')."/images/Logo_MEC_150x40_2.jpg";
     $nvpStr .= "&L_BUTTONVAR5=shipping=";
     $nvpStr .= "&L_BUTTONVAR6=item_name=commande ".$facture->numerofacture;
     $nvpStr .= "&L_BUTTONVAR7=on0=";
     $nvpStr .= "&L_BUTTONVAR8=on1=";
     $nvpStr .= "&L_BUTTONVAR9=os0=";
     $nvpStr .= "&L_BUTTONVAR10=os1=";
     $nvpStr .= "&L_BUTTONVAR11=first_name=".$facture->prenom;
     $nvpStr .= "&L_BUTTONVAR12=last_name=".$facture->nom;
     $nvpStr .= "&L_BUTTONVAR13=email=".$facture->mail;
     $nvpStr .= "&L_BUTTONVAR14=address1=".$facture->ville;
     $nvpStr .= "&L_BUTTONVAR15=address2=";
     $nvpStr .= "&L_BUTTONVAR16=lc=".$facture->langue;
     $nvpStr .= "&L_BUTTONVAR17=brandname=".$facture->raisonsociale;
     $nvpStr .= "&L_BUTTONVAR18=zip=".$facture->codepostal;
     $nvpStr .= "&L_BUTTONVAR19=city=".$facture->ville;
     $nvpStr .= "&L_BUTTONVAR20=BUTTONIMAGE=https://www.paypalobjects.com/webstatic/mktg/logo-center/logo_paypal_moyens_paiement_fr.jpg";
     $nvpStr .= "&L_BUTTONVAR23=notify_url=".(defined('SITE_HOST') ? SITE_HOST : 'http://www.mon-espace-cuisson.com')."/paypal/ipn3.php";
     $nvpStr .= "&L_BUTTONVAR25=cancel_return=".$returnURL."&HDRIMG=https://static.e-junkie.com/sslpic/14692.35c68aeaa0d1006a5e78e5a67045633e.jpg&CURRENCYCODE=EUR&INVNUM=".$facture->facturid."&SHIPTONAME=".urlencode($facture->prenom." ".$facture->nom)."&SHIPTOSTREET=".urlencode($facture->adresse)."&SHIPTOCITY=".urlencode($facture->ville)."&SHIPTOCOUNTRYCODE=".$facture->diminutif."&SHIPTOZIP=".$facture->codepostal."&ADDROVERRIDE=1&SOLUTIONTYPE=Sole";
     $nvpStr .= "&L_BUTTONVAR61=return=".$returnURL;

     // Execute the API operation; see the PPHttpPost function above.
     $httpParsedResponseAr = PPHttpPost('BMCreateButton', $nvpStr);

     if("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"])) {
               /*echo "<pre>";
               print_r($httpParsedResponseAr);
               echo "</pre>";*/


               echo urldecode($httpParsedResponseAr['WEBSITECODE']);

               /*echo "<script type=\"text/javascript\">";
               echo "document.form.cmd.submit();";
               echo "</script>";*/

               exit();


     } else  {
               exit('BMCreateButton failed: ' . print_r($httpParsedResponseAr, true));
     }
}
?>