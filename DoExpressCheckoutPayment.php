<?php
/**********************************************************
DoExpressCheckoutPayment.php

This functionality is called to complete the payment with
PayPal and display the result to the buyer.

The code constructs and sends the DoExpressCheckoutPayment
request string to the PayPal server.

Called by GetExpressCheckoutDetails.php.

Calls CallerService.php and APIError.php.

**********************************************************/

include("global.php");

include_once("mailconfirmation.php");
include("admin/dissociation.php");

require_once 'vad/macavavin/CallerService.php';
session_start();



         /* At this point, the buyer has completed in authorizing payment
            at PayPal.  The script will now call PayPal with the details
            of the authorization, incuding any shipping information of the
            buyer.  Remember, the authorization is not a completed transaction
            at this state - the buyer still needs an additional step to finalize
            the transaction
            */

$token =urlencode( $_REQUEST['token']);
$orderid=$_REQUEST['order'];
$facture=new facture($DB_site,$orderid);
if($facture->valide>0) header("location:/");


$valide=3;

         /* Build a second API request to PayPal, using the token as the
            ID to get the details on the payment authorization
            */
$nvpstr="&TOKEN=".$token;

         /* Make the API call and store the results in an array.  If the
            call was a success, show the authorization details, and provide
            an action to complete the payment.  If failed, show the error
            */

$resArray=hash_call("GetExpressCheckoutDetails",$nvpstr);
$_SESSION['reshash']=$resArray;
$ack = strtoupper($resArray["ACK"]);

           //changed GetExpressCheckoutDetails.php for DoExpressCheckoutPayment.php
if($ack=="SUCCESS"){

    $_SESSION['token']=$_REQUEST['token'];
    $_SESSION['payer_id'] = $_REQUEST['PayerID'];

    $_SESSION['paymentAmount']=$_REQUEST['paymentAmount'];
    $_SESSION['currCodeType']=$_REQUEST['currencyCodeType'];
    $_SESSION['paymentType']=$_REQUEST['paymentType'];

    $resArray=$_SESSION['reshash'];

/* Gather the information to make the final call to
   finalize the PayPal payment.  The variable nvpstr
   holds the name value pairs
   */
 
   $token =urlencode( $_SESSION['token']);
   $paymentAmount =urlencode ($_SESSION['paymentAmount']);
   $paymentType = "sale";
   $currCodeType = urlencode($_SESSION['currCodeType']);
   $payerID = urlencode($_SESSION['payer_id']);
   $serverName = urlencode($_SERVER['SERVER_NAME']);

   $nvpstr='&TOKEN='.$token.'&PAYERID='.$payerID.'&PAYMENTACTION='.$paymentType.'&AMT='.$paymentAmount.'&CURRENCYCODE='.$currCodeType.'&IPADDRESS='.$serverName.'&INVNUM='.$orderid;

 /* Make the call to PayPal to finalize payment
    If an error occured, show the resulting errors
    */
    $resArray=hash_call("DoExpressCheckoutPayment",$nvpstr);

/* Display the API response back to the browser.
   If the response from PayPal was a success, display the response parameters'
   If the response was an error, display the errors received using APIError.php.
   */
   $ack = strtoupper($resArray["ACK"]);
   $paymentstatus=strtoupper($resArray["PAYMENTSTATUS"]);

     if($ack=="SUCCESS"){ //Paiement "valide" ou "en cours de validation"
       
          $_SESSION['reshash']=$resArray;
       
          if($paymentstatus=="COMPLETED"){               
               $valide = 1;
               
               $sql = "SELECT *";
               $sql .= " FROM utilisateur";
               $sql .= " WHERE userid = ".$facture->utilisateur->userid;
               $user_info = $DB_site->query_first($sql);

               mailconfirmation(6,$facture,$user_info);
               
               $diss = new dissociation($DB_site,$orderid);

               //Si commande à l'étranger et qu'un article n'a pas de code douanier ni d'origine enregistrée
                $facture = new facture($DB_site,$facture->facturid); //on recréé la variable pour prendre les lignes dissociées
                verif_export($facture,$template);
          }
	  
          if($paymentstatus=="PENDING"){
               
               $valide = 3;
          }
          
          $params = array(
              'valide'  => $valide,
              'ventes'  => true
          );
          $facture->update($params);
          
          $htmlmess = "Réponse paypal : <br>".print_r($resArray,true);
          
          $sql = "INSERT INTO suivi_commande SET";
          $sql .= " facturid = ".intval($orderid);
          $sql .= ", date = ".time();
          $sql .= ", suivi = '".mysqli_real_escape_string($DB_site->link_id,$htmlmess)."'";
          $DB_site->query($sql);
     }

     $liendevis = "javascript:popdevis(".$orderid.")";
}

$f=fopen("vad/ppresponse.log","a");
fwrite($f,date("d/m/Y H:i")." ".$orderid." ".$paymentstatus." ".$valide." ".$TemplateCde3."\n");
fclose($f);

$template->redirect("cde3.php?factureid=".$orderid."&paymentstatus=".$paymentstatus."&moyen=6",302);

?>
