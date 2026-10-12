<?php
require ("global.php");

if($action == 'contact_leads') {
	if($_POST['nom']=="" || $_POST['prenom']=="" || $_POST['tel']=="" || $_POST['message']=="" || $_POST['email']=="" || $_POST['accept_contact']=="" || $_POST['societe']==""|| $_GET['utm_source'] == "") $erreur=true;

	if (!$erreur){
		$tag=$DB_site->query_first("SELECT id FROM leads_utm WHERE utm_source = '".$_GET['utm_source']."' AND utm_medium = '".$_GET['utm_medium']."' AND utm_campagne = '".$_GET['utm_id']."'" );

		$sql = "INSERT INTO contact_leads SET";
		$sql .= " date 			 = ".time();
		$sql .= ", nom 			 = '".mysqli_real_escape_string($DB_site->link_id,htmlentities($_POST['nom']))."'";
		$sql .= ", prenom 		 = '".mysqli_real_escape_string($DB_site->link_id,htmlentities($_POST['prenom']))."'";
		$sql .= ", societe 		 = '".mysqli_real_escape_string($DB_site->link_id,htmlentities($_POST['societe']))."'";
		$sql .= ", mail 		 = '".mysqli_real_escape_string($DB_site->link_id,htmlentities($_POST['email']))."'";
		$sql .= ", telephone 	 = '".mysqli_real_escape_string($DB_site->link_id,htmlentities($_POST['tel']))."'";
		$sql .= ", message 		 = '".mysqli_real_escape_string($DB_site->link_id,utf8_decode($_POST['message']))."<br><br>".$id."'";
		$sql .= ", utm_source	 	 = '".$tag['id']."'";
		$DB_site->query($sql);
    	

    	$message_mail = ""
	    . "<p>Bonjour,</p>"
	    . "<p>Une personne vient de remplir le formulaire de la page formulaire-contact </p>"
	    . "<br><b>Nom, prénom</b> : ".strip_tags($_POST['nom'])." ".strip_tags($_POST['prenom'])
	    . "<br><b>Société</b> : ".strip_tags($_POST['societe'])
	    . "<br><b>E-mail expéditeur</b> : ".strip_tags($_POST['email'])
	    . "<br><b>Téléphone</b> : ".strip_tags($_POST['tel'])
	    . "<br><b>Message</b> : ".strip_tags($_POST['message'])
	    . "<br><b>Source du contact</b> : ".$_GET['utm_source'];

		$mail=new email();
		$mail->expediteur="contact@mon-espace-cuisson.com";
		$mail->expname="contact@mon-espace-cuisson.com";
		$mail->destinataire="contact@mon-espace-cuisson.com";
		$mail->to="marketing1@mon-espace-cuisson.com";
		$mail->subject="Mon espace cuisson : Réponse au formulaire de contact.";
		$mail->body=$message_mail;
	    $mail->sendmail();

	 	
	    $template->parts['validChamp']='style="display:block;"';
	}

	else {
		$template->parts['nom'] = strip_tags($_POST['nom']);
		$template->parts['prenom'] = strip_tags($_POST['prenom']);
		$template->parts['societe'] = strip_tags($_POST['societe']);
		$template->parts['email'] = strip_tags($_POST['email']);
		$template->parts['tel'] = strip_tags($_POST['tel']);
		$template->parts['message'] = strip_tags($_POST['message']);
		$template->parts['errorChamp'] = 'style="display:block;"';

		if(empty($_POST['accept_contact'])) {
			$template->parts['styleAccept'] = ' color: red;';
		}

		foreach ($_POST as $key => $value) {
			if($value==''){
				switch ($key) {
					case 'nom':
					$template->parts['styleNom'] = 'style="border:solid 1px red;"';
					break;

					case 'prenom':
					$template->parts['stylePrenom'] = 'style="border:solid 1px red;"';
					break;

					case 'tel':
					$template->parts['styleTel'] = 'style="border:solid 1px red;"';
					break;

					case 'email':
					$template->parts['styleEmail'] = 'style="border:solid 1px red;"';
					break;

					case 'societe':
					$template->parts['styleSociete'] = "border:solid 1px red;";
					break;

					case 'message':
					$template->parts['styleMess'] = 'style="border:solid 1px red;"';
					break;
				}
			}

		}
	}
}



	$template->evaltemplate("FormulaireContact","content");
	$template->evaltemplate("Footer1","footer1");

	$template->pageparams($host,$title." - ".$template->translate("/site/nom"),"");
	$template->evaltemplate("InstituPage","main");

//$template->parts["remarketing"]=google_remarketing('',"siteview",'');

	$template->out();