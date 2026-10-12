<?php

ini_set("display_errors","on");

require_once($_SERVER['DOCUMENT_ROOT']."/global.php");
include_once($_SERVER['DOCUMENT_ROOT']."/mailconfirmation.php");
include($_SERVER['DOCUMENT_ROOT']."/admin/dissociation.php");


$adyen_ip=array("82.199.90.163","91.212.42.11");

// ils répondent avec des ip différentes des fois donc ça risque de bloquer
// if(strpos($_SERVER['REMOTE_HOST'],"adyen.com")===false && !in_array($adyen_ip,$_SERVER['REMOTE_ADDR'])) exit;

$facturid=$_POST['merchantReference'];
$facture=new facture($DB_site,$facturid);
if(is_null($facture->facturid)) exit;
$template=new template($facture->utilisateur->langue,$DB_site);
$template->path=$rootpath."templates/".$facture->utilisateur->langue;

switch($_POST['eventCode']){

    case 'AUTHORISATION':
    // Handle AUTHORISATION notification.
    // Confirms that the payment was authorised successfully.
    if($_POST['success']=="true"){
	$DB_site->query("UPDATE facture SET valide='1', moyenid=16 WHERE facturid=".$facture->facturid);
	mailconfirmation(1,$facture,null);
	$diss=new dissociation($DB_site,$facture->facturid);
    }else{
	$DB_site->query("UPDATE facture SET valide = '3',moyenid=16 WHERE facturid = '".$facture->facturid."'");
    }
    break;

    case 'CANCELLATION':
    // Handle CANCELLATION notification.
    // Confirms that the payment was cancelled successfully.
    $DB_site->query("UPDATE facture SET valide = '3',moyenid=16 WHERE facturid = '".$facture->facturid."'");
    
//     $DB_site->query("INSERT INTO suivi_commande SET facturid=".intval($facture->facturid).", date=".time().", suivi='".mysql_real_escape_string($htmlmess)."'");
    break;

    case 'REFUND':
    // Handle REFUND notification.
    // Confirms that the payment was refunded successfully.
    break;

    case 'CANCEL_OR_REFUND':
    // Handle CANCEL_OR_REFUND notification.
    // Confirms that the payment was refunded or cancelled successfully.
    break;

    case 'CAPTURE':
    // Handle CAPTURE notification.
    // Confirms that the payment was successfully captured.
    break;

    case 'REFUNDED_REVERSED':
    // Handle REFUNDED_REVERSED notification.
    // Tells you that the refund for this payment was successfully reversed.
    break;

    case 'CAPTURE_FAILED':
    // Handle AUTHORISATION notification.
    // Tells you that the capture on the authorised payment failed.
    break;

    case 'REQUEST_FOR_INFORMATION':
    // Handle REQUEST_FOR_INFORMATION notification.
    // Information requested for this payment .
    break;

    case 'NOTIFICATION_OF_CHARGEBACK':
    // Handle NOTIFICATION_OF_CHARGEBACK notification.
    // Chargeback is pending, but can still be defended
    break;

    case 'CHARGEBACK':
    // Handle CHARGEBACK notification.
    // Payment was charged back. This is not sent if a REQUEST_FOR_INFORMATION or
    // NOTIFICATION_OF_CHARGEBACK notification has already been sent.
    break;

    case 'CHARGEBACK_REVERSED':
    // Handle CHARGEBACK_REVERSED notification.
    // Chargeback has been reversed (cancelled).
    break;

    case 'REPORT_AVAILABLE':
    // Handle REPORT_AVAILABLE notification.
    // There is a new report available, the URL of the report is in the "reason" field.
    break;
}

 /**
* Returning [accepted], please make sure you always
* return [accepted] to us, this is essential to let us
* know that you received the notification. If we do NOT receive
* [accepted] we try to send the notification again which
* will put all other notification in a queue.
*/
 print "[accepted]";

$logdata=date("d/m/Y H:i:s")." ".$_POST['merchantReference']." ".$_POST['eventCode']." ".$_POST['paymentMethod']." ".$_POST['success']." ".$_POST['reason']." ".$_POST['amount']."\n";

$logfile=$_SERVER['DOCUMENT_ROOT']."/vad/macavavin/adyen.log";
$fp=fopen($logfile,"a");
fwrite($fp,$logdata);
fclose($fp);
 
?>