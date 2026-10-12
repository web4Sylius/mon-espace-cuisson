<?php
require ("global.php");

$template->parts["error"]="herror";
$template->parts["msgok"]="herror";
$remoteip = $_SERVER['REMOTE_ADDR'];
define("RECAPTCHA_V3_SECRET_KEY", '6Lfr2fUiAAAAAFusP3yDoC2htVdNsXBQbsFketsh');
$sitekey="6Lfr2fUiAAAAAKYKxFs7R0mRQYMXFzEmBDN03kn5";

if (isset($action) and $action == "perso"){

    $message = "" ;
    $id="";
    $erreur = false ;

    $token = $_POST['token'];
	$action = $_POST['action'];
	  
	// call curl to POST request
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,"https://www.google.com/recaptcha/api/siteverify");
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('secret' => RECAPTCHA_V3_SECRET_KEY, 'response' => $token)));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);
	curl_close($ch);
	$arrResponse = json_decode($response, true); 

	if($arrResponse["success"] && $arrResponse["action"] == $action && $arrResponse["score"] >= 0.5){
		$erreur=false;
	}else{
		$erreur = true;
	}
	foreach($_POST as $name=>$value) $template->parts[$name]=$value;

	$utel=strip_tags(preg_replace("/[\.|\ ]/","",$_POST['telephone']));
	$umail=mysqli_real_escape_string($DB_site->link_id,strip_tags($_POST['expediteur']));

	//on test si on peut identifier le client
	if(!is_null($user_info)){//connecté

		$id ="<p>Client <a href=\"http://".$host."/admin/utilisateur.php?action=editer&userid=".$user_info['userid']."\">".$user_info['prenom']." ".$user_infos['nom']." n°".$user_info['userid']."</a></p>";

	}elseif(strlen($utel)>5){//identifié par tel ou mail
	
		$utilisateur=$DB_site->query_first("SELECT * FROM utilisateur WHERE REPLACE(telephone,'.','') LIKE '".$utel."' OR REPLACE(portable,'.','') LIKE '".$utel."'");
    
    	if($utilisateur['userid']!=""){

	   		$id="<p>Client identifié par n° téléphone <a href=\"http://".$host."/admin/utilisateur.php?action=editer&userid=".$utilisateur['userid']."\">".$utilisateur['prenom']." ".$utilisateur['nom']." n°".$utilisateur['userid']."</a></p>";
    
    	}elseif(filter_var($umail, FILTER_VALIDATE_EMAIL)){
	
       		$utilisateur=$DB_site->query_first("SELECT * FROM utilisateur WHERE mail='".$umail."'");
	
       		if($utilisateur['userid']!=""){
    
            	$id="<p>Client identifié par email <a href=\"http://".$host."/admin/utilisateur.php?action=editer&userid=".$utilisateur['userid']."\">".$utilisateur['prenom']." ".$utilisateur['nom']." n°".$utilisateur['userid']."</a></p>";
       		}
    	}   	
	}

	$message = ""
	    . "<p>Bonjour,</p>"
	    . "<p>Une personne vient de remplir le formulaire de la page contacte de votre boutique</p>"
	    . "<p><b>Nom, prénom</b> : ".strip_tags($_POST['nom'])." ".strip_tags($_POST['prenom'])
	    . "<br><b>E-mail expéditeur</b> : ".strip_tags($_POST['expediteur'])
	    . "<br><b>Société / raison sociale</b> : ".strip_tags($_POST['societe'])
	    . "<br><b>Adresse</b> : ".strip_tags($_POST['adresse'])
	    . "<br><b>Complément d'adresse</b> : ".strip_tags($_POST['adresse2'])
	    . "<br><b>Code postal + ville</b> : ".strip_tags($_POST['code'])." ".strip_tags($_POST['ville'])
	    . "<br><b>Pays</b> : ".strip_tags($_POST['pays'])
	    . "<br><b>Comment nous avez-vous connu ?</b> ".strip_tags($_POST['connaissance'])
	    . "<br><b>Téléphone</b> : ".strip_tags($_POST['telephone'])
	    . "<br><b>Sujet</b> : ".strip_tags($_POST['sujet'])
	    . "<br><b>Message</b> : ".strip_tags($_POST['message'])
	    . "<br><b>Score captcha : ".$arrResponse["score"];

    if($_POST['nom']=="" || $_POST['prenom']=="" || $_POST['telephone']=="" || $_POST['ville']=="" || $_POST['pays']=="" || $_POST['message']=="" || $_POST['cgv']!='1' || !filter_var($_POST['expediteur'], FILTER_VALIDATE_EMAIL)) $erreur=true;

    if (!$erreur){
		$mail=new email();
		$mail->expediteur=MAIL_CONTACT;
		$mail->expname=NOM_CONTACT;
		$mail->destinataire=MAIL_CONTACT;
		//$mail->to=array(MAIL_COM1);
		$mail->subject="Mon Espace Cuisson : Réponse au formulaire de votre boutique.";
		$mail->body=$message.$id;
	    $mail->sendmail();

	    $sql = "INSERT INTO contact SET";
	    $sql .= " date 			 = ".time();
	    $sql .= ", nom 			 = '".mysqli_real_escape_string($DB_site->link_id,htmlentities($_POST['nom']))."'";
	    $sql .= ", prenom 		 = '".mysqli_real_escape_string($DB_site->link_id,htmlentities($_POST['prenom']))."'";
	    $sql .= ", raisonsociale = '".mysqli_real_escape_string($DB_site->link_id,htmlentities($_POST['societe']))."'";
	    $sql .= ", adresse 		 = '".mysqli_real_escape_string($DB_site->link_id,htmlentities($_POST['adresse']))."'";
	    $sql .= ", adresse2 	 = '".mysqli_real_escape_string($DB_site->link_id,htmlentities($_POST['adresse2']))."'";
	    $sql .= ", cp 		 	 = '".mysqli_real_escape_string($DB_site->link_id,htmlentities($_POST['code']))."'";
	    $sql .= ", ville 		 = '".mysqli_real_escape_string($DB_site->link_id,htmlentities($_POST['ville']))."'";
	    $sql .= ", pays 		 = '".mysqli_real_escape_string($DB_site->link_id,htmlentities($_POST['pays']))."'";
	    $sql .= ", mail 		 = '".mysqli_real_escape_string($DB_site->link_id,htmlentities($_POST['expediteur']))."'";
	    $sql .= ", telephone 	 = '".mysqli_real_escape_string($DB_site->link_id,htmlentities($_POST['telephone']))."'";
	    $sql .= ", sujet 		 = '".mysqli_real_escape_string($DB_site->link_id,htmlentities($_POST['sujet']))."'";
	    $sql .= ", message 		 = '".mysqli_real_escape_string($DB_site->link_id,$_POST['message'])."<br><br>".$id."'";
	    $sql .= ", connu 		 = '".mysqli_real_escape_string($DB_site->link_id,htmlentities($_POST['connaissance']))."'";
	    $sql .= ", gclid 		 = '".mysqli_real_escape_string($DB_site->link_id,htmlentities($_POST['gclid']))."'";
		$DB_site->query($sql);
		$template->parts["msgok"]="error";

		$_POST['nom'] = '';
		$_POST['prenom'] = '';
		$_POST['societe'] = '';
		$_POST['adresse'] = '';
		$_POST['adresse2'] = '';
		$_POST['code'] = '';
		$_POST['ville'] = '';
		$_POST['pays'] = '';
		$_POST['expediteur'] = '';
		$_POST['telephone'] = '';
		$_POST['sujet'] = '';
		$_POST['message'] = '';
		$_POST['connaissance'] = '';
    }else{
        $template->parts["error"]="error";
        if(!isset($_POST['cgv']))
        	$template->parts["styleCGV"] = 'border:solid 1px red;';

        foreach ($_POST as $key => $value) {
        	if($value==''){
	        	switch ($key) {
	        		case 'nom':
	        			$template->parts['styleNom'] = 'style="border:solid 1px red;"';
	        			break;
	        		
	        		case 'prenom':
	        			$template->parts['stylePrenom'] = 'style="border:solid 1px red;"';
	        			break;

	        		case 'telephone':
	        			$template->parts['styleTel'] = 'style="border:solid 1px red;"';
	        			break;

	        		case 'expediteur':
	        			$template->parts['styleExp'] = 'style="border:solid 1px red;"';
	        			break;
	        		case 'ville':	
	        			$template->parts['styleVille'] = 'style="border:solid 1px red;"';

	        		case 'pays':
	        			$template->parts['stylePays'] = 'style="border:solid 1px red;"';

	        		case 'message':
	        			$template->parts['styleMessage'] = 'style="border:solid 1px red;"';
	        			break;
	        	}
	        }
        }
    }
}

if(!is_null($user_info)){
	$template->parts["nom"] = $user_info['nom'];
	$template->parts["prenom"] = $user_info['prenom'];
	$template->parts["expediteur"] = $user_info['mail'];
	$template->parts["adresse"] = $user_info['adresse'];
	$template->parts["ville"] = $user_info['ville'];
	$template->parts["code"] = $user_info['codepostal'];
	$template->parts["telephone"] = $user_info['telephone'];
	$template->parts["raisonsociale"] = $user_info['societe'];
	$paysid = $user_info['paysid'];
}

$sql = "SELECT paysid, libelle FROM pays ORDER BY libelle";
$req = $DB_site->query($sql);
$template->parts["optspays"] = "<option value=\"\">".$template->translate("/contact/pays")." *</option>";
while($row = $DB_site->fetch_array($req)){
	$template->parts["optspays"] .= "<option value=\"".$row['libelle']."\" ".(isset($paysid) && $paysid == $row['paysid'] ? 'selected' : '').">".$row['libelle']."</option>";
}

$template->parts['js_page'] = "<script src='//www.google.com/recaptcha/api.js?render=".$sitekey."'></script>";
$title=$template->translate("/contact/nous_contacter");
if(isset($_GET['type'])){
    switch($_GET['type']){
	case "partenaire":
	$type="_partenaire";
	$title=$template->translate("/contact/nous_contacter");
	break;
	
	case "configurateur":
	$type="_configurateur";
	$title="Votre avis sur <br>le configurateur";
	break;
	
	default:
	$title=$template->translate("/contact/nous_contacter");
    }
}

$template->evaltemplate("Contacter".$type,"content");

$template->parts["fil_ariane"]=" &gt; <span property=\"itemListElement\" typeof=\"ListItem\"><span property=\"name\">".strip_tags($title)."</span><meta property=\"position\" content=\"1\"></span>";
$template->parts["title"]=ucfirst($title);
$template->parts["class2"]=" contact";

include("barrefixe.php");

foreach($template->sites() as $site_code=>$site){
	foreach($site['hosts'] as $url=>$deflangue){
	    if($site['actif'][$url]) {
	    	if($deflangue=='FR')
	    		$lien='/contacter.html';
	    	elseif($deflangue=='EN')
	    		$lien='/contact.html';

			$template->parts["alternate"].="<link rel=\"alternate\" hreflang=\"".$site['locales'][$deflangue]."\" href=\"https://www.".$url.$lien."\">\n";
	    }
	}
}

$template->evaltemplate("Footer1","footer1");

$template->pageparams($host,$title." - ".$template->translate("/site/nom"),"");
$template->evaltemplate("InstituPage","main");

$template->parts["remarketing"]=google_remarketing('',"siteview",'');

$template->out();

?>