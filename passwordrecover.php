<?php


require ("global.php");

$template->parts["lasturl"]=$_SERVER['HTTP_REFERER'];

if ($action == "envoimail"){
	
	// modif TSI pour eviter de tester des requetes SQL !!!!
	$regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
	if (preg_match($regex, $txt_mail) && strlen($txt_mail)<=50) {
		$emailValid=true;
		$utilisateur = $DB_site->query_first( "SELECT userid,nom,prenom,mail FROM utilisateur WHERE mail='$txt_mail'" );
	} else {
		$emailValid=false;
	}
	
	//$utilisateur = $DB_site->query_first( "SELECT userid,nom,prenom,mail FROM utilisateur WHERE mail='$txt_mail'" );
	if( $utilisateur[userid] != "" && trim($txt_mail)!="" && $emailValid){
	//if( $utilisateur[userid] != "" && trim($txt_mail)!=""){
			// Si utilisateur valide on cr�� un nouveau mot de passe et on le mail
		$adresse_utilisateur = $txt_mail;
		$code = ""; 
		$chars = array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z"); 
		$count = count($chars) - 1; 
		srand((float) microtime()*1000000);
		for ($i = 0; $i < 8; $i++) $code .= $chars[rand(0, $count)];
		$DB_site->query( "UPDATE utilisateur SET password='".md5($code)."'  WHERE userid='$utilisateur[userid]'" );
		
		$vars=array("code"=>$code);
		$ec=new email_client($DB_site,$template,null,"mdpperdu",$vars);
		
		$mail=new email();
		$mail->expname=$template->translate("/site/nom");
		$mail->expediteur=$template->translate("/site/[site_code]/mail_expediteur");
		$mail->destinataire=$txt_mail;
		$mail->copymode="bcc";
		$mail->to="pr@mon-espace-cuisson.com";
		$mail->subject=$ec->subject;
		$mail->body=$ec->body;
		$mail->sendmail();

		$adminmsg="[infos_client]<br> Utilisateur : $utilisateur[prenom] $utilisateur[nom]<br>Mail : $utilisateur[mail]<br>Nouveau mdp : $code";

		$template->parts["mail"]=$utilisateur[mail];
		$template->evaltemplate("PasswordRecover2","pagecommande");

		$commercial="client";
		if(isset($_COOKIE['admin_username']) && $_COOKIE['admin_username']!="") $commercial=$_COOKIE['admin_username'];
		$DB_site->query("INSERT INTO suivi_client SET userid=".$utilisateur['userid'].", date=".time().", commercial='".$commercial."', suivi='".mysqli_real_escape_string($DB_site->link_id, $mailtxt[1]."<br>".$adminmsg)."', etat=1");
	}else{	// Mot de passe invalide	

		$template->parts["raison"] = $template->translate("/client/indiquer_mail_valide","F");
		$template->evaltemplate("PasswordRecover","pagecommande");
	}
}else{	// Affichage du formulaire
	$template->evaltemplate("PasswordRecover","pagecommande");
}


$template->parts["fil"]="<a href=\"/\">".$template->translate("/site/nom")."</a> &gt; ".$template->translate("/client/recuperation_mdp","F");
$template->parts["title"]=$template->translate("/client/recuperation_mdp","F");


$template->pageparams($host,$template->translate("/client/recuperation_mdp","F"),"");
$template->evaltemplate("PanierPage","main");

$template->out();

?>