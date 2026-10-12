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

$document_root = '/home/mon-espace-cuisson.com';

require_once($document_root."/global.php");
require_once($document_root."/mailconfirmation.php");
require_once($document_root."/Aci-AdM/dissociation.php");
require_once($document_root."/paypal/CallerService.php");

session_start();

/* At this point, the buyer has completed in authorizing payment
   at PayPal.  The script will now call PayPal with the details
   of the authorization, incuding any shipping information of the
   buyer.  Remember, the authorization is not a completed transaction
   at this state - the buyer still needs an additional step to finalize
   the transaction
   */

$token = urlencode($_REQUEST['token']);
$orderid = $_REQUEST['order'];
$facture = new facture($DB_site, $orderid);

//Commande déjà traitée, on redirige sur la home
if($facture->valide > 0){
     header("Location: /");
     exit;
}

/* Build a second API request to PayPal, using the token as the
ID to get the details on the payment authorization
*/
$nvpstr = "&TOKEN=".$token;

/* Make the API call and store the results in an array.  If the
call was a success, show the authorization details, and provide
an action to complete the payment.  If failed, show the error
*/

$resArray = hash_call("GetExpressCheckoutDetails",$nvpstr);
$_SESSION['reshash'] = $resArray;
$ack = strtoupper($resArray["ACK"]);

//Par défaut, on indique que le paiement a été refusé
$valide = 3;
$redirection_action = 'refus';

//Création d'un id de transaction (utile uniquement en dernière étape)
$transaction_id = "EC".date('dmYhis');

//Transaction reconnue par Paypal, poursuite du traitement
if($ack=="SUCCESS"){
     $_SESSION['token'] = $_REQUEST['token'];
     $_SESSION['payer_id'] = $_REQUEST['PayerID'];
     $_SESSION['paymentAmount'] = $_REQUEST['paymentAmount'];
     $_SESSION['currCodeType'] = $_REQUEST['currencyCodeType'];
     $_SESSION['paymentType'] = $_REQUEST['paymentType'];

     $resArray = $_SESSION['reshash'];

     /* Gather the information to make the final call to
     finalize the PayPal payment.  The variable nvpstr
     holds the name value pairs
     */

     $token = urlencode( $_SESSION['token']);
     $paymentAmount = urlencode ($_SESSION['paymentAmount']);
     $paymentType = "sale";
     $currCodeType = urlencode($_SESSION['currCodeType']);
     $payerID = urlencode($_SESSION['payer_id']);
     $serverName = urlencode('www.mon-espace-cuisson.com');
     
     //Construction de l'url pour demande sur status de la commande à Paypal
     $nvpstr = '&TOKEN='.$token.'&PAYERID='.$payerID.'&PAYMENTACTION='.$paymentType.'&AMT='.$paymentAmount.'&CURRENCYCODE='.$currCodeType.'&IPADDRESS='.$serverName.'&INVNUM='.$orderid;

     /* Make the call to PayPal to finalize payment
     If an error occured, show the resulting errors
     */
     $resArray = hash_call("DoExpressCheckoutPayment",$nvpstr);

     /* Display the API response back to the browser.
     If the response from PayPal was a success, display the response parameters'
     If the response was an error, display the errors received using APIError.php.
     */
     $ack = strtoupper($resArray["ACK"]);
     $paymentstatus = strtoupper($resArray["PAYMENTSTATUS"]);
     
     //Paiement "valide" ou "en attente"
     if($ack=="SUCCESS"){
          //Récupération des infos de retour
          $_SESSION['reshash'] = $resArray;
          
          //Servira à définir sur quelle URL rediriger l'utilisateur
          $redirection_action = 'succes';
          
          //Paiement complété
          if($paymentstatus == "COMPLETED"){
               //Status "validé"
               $valide = 1;
               
               //Status transmis dans l'url de redirection
               $status = 'success';
               
               //Envoi du mail
               mailconfirmation(6,$facture);
               $diss = new dissociation($DB_site,$orderid);
          }
          
          //Paiement en cours de validation
          if($paymentstatus == "PENDING"){
               //Status "validé en attente de paiement"
               $valide = 7;
               
               //Status transmis dans l'url de redirection
               $status = 'pending';
               
               //TODO : Envoyer un mail pour commande avec status "En attente de validation par Paypal"
          }
     }
     
     //Mise à jour du status de la commande
     $sql = "UPDATE facture SET";
     $sql .= " valide = '".$valide."'";
     $sql .= ", transaction_id = '".$transaction_id."'";
     $sql .= " WHERE facturid='".$orderid."'";
     $DB_site->query($sql);
     
     $facture->add_historique($orderid, $valide);
     
     //Insertion de l'informations dans le suivi
     $htmlmess = "Reponse paypal : <br>".print_r($resArray, true);
     $sql = "INSERT INTO suivi_commande SET";
     $sql .= " facturid=".intval($orderid)."";
     $sql .= ", date=".(time() + 1); //Hack pour être certain de ne pas avoir exactement la même heure que pour le suivi du mail de confirmation
     $sql .= ", suivi='".mysql_real_escape_string($htmlmess)."'";
     $DB_site->query($sql);
}

//Insertion des informations dans fichier de log
$f = fopen($document_root."/paypal/ppresponse.log","a");
fwrite($f,date("d/m/Y H:i")." ".$orderid." ".$paymentstatus." ".$valide." ".$TemplateCde3."\n");
fclose($f);

//Mise en place de l'url de redirection
switch($redirection_action){
     case 'refus':
          $url_redirection = '/paiement_refuse.php?paiement=paypal';
          break;
     case 'succes':
          $commande_url = md5($orderid);
          $transaction_url = md5($transaction_id);
          
          $url_redirection = '/paiement_succes.php?paiement=paypal&status='.$status.'&cmd='.$commande_url.'&trans='.$transaction_url;
          break;
     default:
          $url_redirection = '/';
          break;
}

header('Location: '.$url_redirection);
exit;

?>