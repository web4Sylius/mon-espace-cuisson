<?php
require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

$type = $_GET['type'];
$facturid=$_GET['facturid'];

$facture = new facture($DB_site, $facturid);

if($facturid=="" || $type=="") exit;

$vars = array(
        "facturid"     => $facturid,
        "host"  => $host
    );

//on cherck le type pour afficher le bon texte du mail
switch ($type) {
	case 'tel':
		$ec = new email_client($DB_site,$template,null,"validation_devis_call",$vars);
		
    break;

    case 'mail':
    	 $ec = new email_client($DB_site,$template,null,"validation_devis_mail",$vars);
    break;    
}      

if(isset($ec)){
	$mail = new email();
	$mail->expediteur   = $template->translate("/site/[site_code]/mail_expediteur");
	$mail->expname      = $template->translate("/site/nom","F");
	$mail->destinataire = $template->translate("/site/[site_code]/mail_expediteur");
	$mail->to 			= array($facture->utilisateur->mail); 
	$mail->subject      = format_subject($DB_site,$ec->subject,$facturid); //"Ma cave à vin : demande de validation du devis #CDE$facturid#.";
	$mail->body         = $ec->body;
	$mail->sendmail();	
}

function format_subject($DB, $subject, $facturid){
	$pjt = $DB->query_first("SELECT projetid FROM projet WHERE facturid=".$facturid);
	if($pjt[0] != ''){
		return $subject." #PJT".$pjt[0]."#";
	}else{
		return $subject." #CDE$facturid#";
	}
}

?>