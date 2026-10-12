<?php
require_once($_SERVER['DOCUMENT_ROOT']."/global.php");
require_once($_SERVER['DOCUMENT_ROOT']."/vad/macavavin/CallerService.php");

if(isset($_GET['facturid']) && isset($_GET['reglement_id'])){ //Arrivée sur la page
     
    $tabErreur = array();
	
    $facture = new facture($DB_site, $_GET['facturid']);
	
    $sql = "SELECT *";
    $sql .= " FROM reglement";
    $sql .= " WHERE reglement_id = ".intval($_GET['reglement_id']);
    $reglement = $DB_site->query_first($sql);
    
    if($reglement['facturid'] == $_GET['facturid'] && $reglement['reglement_statut_id'] == 0){
        
        $sql = "SELECT mail";
        $sql .= " FROM utilisateur";
        $sql .= " WHERE userid = ".$reglement['userid'];
        $userinfos = $DB_site->query_first($sql);
        switch($reglement['reglement_moyen']){
            
            case 1:  // CB
            case 13: // AMEX
            
                //Passage sur l'interface de paiement de la banque
                $reglement_id       = $reglement['reglement_id'];
                $reglement_total    = $totalmontant    = floatval($reglement['reglement_montant']);
                $reglement_mail     = $userinfos['mail'];
                $reglement_facturid = $reglement['facturid'];
                
                $pagepaiement="vad/macavavin/pagepaiementcbbis.php";
                $pagepaiement="vad/macavavin/paybox.php";
                include_once($pagepaiement);
                
                $message=$template->translate("/commande/choix_carte","F");
                
                $template->parts["paiement"]  = <<<HTML
                <div class="frame">
                $pagepaiementcb
                </div>
HTML;
                break;
            
            case 6: // Paypal
				$reglement_id       = $reglement['reglement_id'];
                $reglement_total    = $totalmontant    = floatval($reglement['reglement_montant']);
                $reglement_mail     = $userinfos['mail'];
                $reglement_facturid = $reglement['facturid'];
				$pagepaiement = "vad/macavavin/pagepaiementcbbis.php"; 
				include ($pagepaiement);
			//	$template->parts["paiement"] = $pagepaiementpaypal;
			//	$template->parts["ecommerce"]=$ecommerce;
				$template->parts["paiement"]  = <<<HTML
                <div class="frame">
                $pagepaiementpaypal
                </div>
HTML;
                break;
            default:
                
                $template->parts["paiement"] .= <<<HTML
                        <div class="frame">Moyen de paiement non disponible</div>
HTML;
        }
          
     //Erreur dans le formulaire
     } else {

          $template->parts["txt_reglement"] = "Ce paiement a déjà été effectué ou n'est plus valable";

     }
     
} elseif(isset($_REQUEST['token'])) {  // retour de paypal
     
    $token        = urlencode($_REQUEST['token']);
    $reglement_id = substr($_REQUEST['order'], 1);
    $orderid=$_REQUEST['order'];
    

    //Récupération du détail du règlement
    $sql = "SELECT *";
    $sql .= " FROM reglement";
    $sql .= " WHERE reglement_id = ".$reglement_id;
    $tabReglement = $DB_site->query_first($sql);
    
    $facturid = $tabReglement['facturid'];
    $facture = new facture($DB_site, $facturid);
    
    $sql = "SELECT *";
    $sql .= " FROM utilisateur";
    $sql .= " WHERE userid = ".$tabReglement['userid'];
    $tabUser = $DB_site->query_first($sql);

    //Commande déjà réglée, on redirige sur la home
    if($facture->valide == 1 || $facture->valide == 5){
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
    if(isset($_GET['test'])) $ack="SUCCESS";

    //Par défaut, on indique que le paiement a été refusé
    $valide = 3;
    $redirection_action = 'refus';

    //Création d'un id de transaction (utile uniquement en dernière étape)
    $transaction_id = "MCAV".date('dmYhis');

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
    	$serverName = urlencode($_SERVER['SERVER_NAME']);
	
    	//Construction de l'url pour demande sur status de la commande à Paypal
    	$nvpstr = '&TOKEN='.$token.'&PAYERID='.$payerID.'&PAYMENTACTION='.$paymentType.'&AMT='.$paymentAmount.'&CURRENCYCODE='.$currCodeType.'&IPADDRESS='.$serverName.'&INVNUM='.rawurlencode($orderid);

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
    	if(isset($_GET['test'])){
    	      $ack="SUCCESS";
    	      $paymentstatus="COMPLETED";
    	}
	
    	$template->parts['color_message'] = "#CB1404";
    	$template->parts['txt_reglement'] = "Votre paiement a été refusé";

    	//Paiement "valide" ou "en attente"
    	if($ack=="SUCCESS"){
    	      //Récupération des infos de retour
    	      $_SESSION['reshash'] = $resArray;
    	      
    	      
    	      //Paiement complété
    	      if($paymentstatus == "COMPLETED"){
    		  //Status "validé"
    		  $valide = 1;
    		  $template->parts['color_message'] = "#00AA00";
    		  $template->parts['txt_reglement'] = "Votre paiement a été validé, nous vous en remercions";


    		  $sql = "SELECT COUNT(*) as nb";
    		  $sql .= " FROM reglement";
    		  $sql .= " WHERE facturid = ".$facturid;
    		  $tabNbReg = $DB_site->query_first($sql);
                       
    		  $moyenpaiement=$DB_site->query_first("SELECT libelleFR AS libelle FROM moyenpaiement WHERE moyenid=6");
                      
                      $vars=array(
                      "prenom"=>$facture->utilisateur->prenom,
                      "nom"=>$facture->utilisateur->nom,
                      "montant"=>$template->price_format($_REQUEST['paymentAmount']),
                      "reglement_id"=>$tabReglement['reglement_id'],
                      "facturid"=>$facturid,
                      "date"=>date("d/m/Y"),
                      "moyen"=>$moyenpaiement['libelle'],
                      "partiel"=>($tabNbReg['nb'] > 1 ? 'partiel' : ''));
                      
                      $template->pro=$sitepro = ($facture->utilisateur->raisonsociale!="" && $template->site_code()=="base");

    		  $mail = new email();
    		  $ec = new email_client($DB_site,$template,283,null,$vars);
    		  
    		  $mail->expediteur   = $template->translate("/site/mail_expediteur");
    		  $mail->expname      = $template->translate("/site/nom");
    		  $mail->destinataire = $facture->utilisateur->mail;
    		  $mail->destname     = $facture->utilisateur->prenom." ".$facture->utilisateur->nom;
    		  $mail->to           = 'contact@mon-espace-cuisson.com,cecile@mon-espace-cuisson.com';
    		  $mail->subject      = $ec->subject;
    		  $mail->body         = $ec->body;

    		  $mail->sendmail();
    		  
    		  $DB_site->query("INSERT INTO mail_outbox SET facturid=".$facturid.",userid='".$tabUser['userid']."', date=".time().", `to`='".$tabUser['mail']."', `from`='".$mail->expediteur."', utilisateur='', subject='".mysqli_real_escape_string($DB_site->link_id,$mail->subject)."', body='".mysqli_real_escape_string($DB_site->link_id,$mail->body)."'");

                        $sql = "UPDATE reglement SET";
                        $sql .= " reglement_date            = '".$tabReglement['reglement_date']."'";
                        $sql .= ", reglement_statut_id      = ".$valide;
                        $sql .= ", reglement_moyen          = '".intval($tabReglement['reglement_moyen'])."'";
                        $sql .= ", reglement_montant        = '".$tabReglement['reglement_montant']."'";
                        $sql .= " WHERE reglement_id        = ".$tabReglement['reglement_id'];
                        $DB_site->query($sql);

    	      }
    	      
    	      //Paiement en cours de validation
    	      if($paymentstatus == "PENDING"){
    		  //Status "validé en attente de paiement"
    		  $valide = 7;
    		  
    		  //TODO : Envoyer un mail pour commande avec status "En attente de validation par Paypal"
    	      }
    	}
    	
    	//Insertion des informations dans fichier de log
    	$f = fopen($_SERVER['DOCUMENT_ROOT']."/vad/macavavin/ppresponse.log","a");
    	fwrite($f,date("d/m/Y H:i")." ".$_REQUEST['order']." ".$paymentstatus." ".$valide." ".$TemplateCde3."\n");
    	fclose($f);
    	
    	$template->redirect("cde3.php?factureid=".$facturid."&paymentstatus=".$paymentstatus."&moyen=6",302);
    }
	    
     
} else { //Affichage par défaut de la page

     if(isset($_SESSION['identifiants']['email'])){
          $template->parts['email_value'] = $_SESSION['identifiants']['email'];
          unset($_SESSION['identifiants']);
     }

}



$template->pageparams($host,$metatitle,$metadescription);
$template->evaltemplate("PanierPaiement2","pagecommande");
$template->evaltemplate("PanierPage","main");
$template->out();
