<?php

include_once("host_config_admin.php");
$host="www.mon-espace-cuisson.com";
include_once($rootpath."configurations/".$host."/config_mail.php");
include($rootpath."class/email.php");
include_once($rootpath."class/projet.class.php");

$start = $DB_site2->query_first("SELECT actif FROM cron WHERE nom='tracking_mail'");
if(intval($start['id']) !=0 ) exit ;

//on passe le script en actif
$DB_site2->query("UPDATE cron SET actif='1' WHERE nom ='tracking_mail'");

$message ="";
$txtlog = "";

$client = $cpt = $projet = $sav = $cmd = $fourn = $conf = false ;
$year = date("Y", time());

$hosts=array();
$req=$DB_site2->query("SELECT hosts FROM site");
while($site=$DB_site2->fetch_array($req)){
    $sitehosts=array_keys(unserialize($site['hosts']));
    $hosts=array_merge($hosts,$sitehosts);
}

//Connexion a la boite mail
print "Connexion imap\n";
if($inbox = imap_open($mailbox, $src_username,$src_password)){
    print "OK\n";
  	//on recupere tous les mails de la boite
  	$emails = imap_search($inbox,'ALL');

  	if($emails) {
        print count($emails)." email(s)\n";
    	//$output = '';

    	rsort($emails);

    	foreach($emails as $email_number) {
    	
	        $overview  = imap_fetch_overview($inbox,$email_number,0);
	        $structure = imap_fetchstructure($inbox, $email_number);
	        $header_info = imap_headerinfo($inbox, $email_number);
	        $messageID = messageID($overview); // permet d'identifer le mail de maniere unique
	        //$message = get_part($inbox, $email_number,"TEXT/PLAIN");decodage du corps du mail en texte simple
	        $message = get_part($inbox, $email_number,"TEXT/HTML"); //decodage du corps du mail en html
	        if(!$message){
                $message = get_part($inbox, $email_number,"TEXT/PLAIN");
                if(!$message)
                    print "\033[0;31mAucun corps trouvé\033[0m\n";
                else
                    print "Message text\n";
            }else
                print "Message HTML\n";
	        
	        $notagmsg=strip_tags($message);

	        if(property_exists($overview[0], "subject")){
	        	$objet = encodage_mail(imap_utf8($overview[0]->subject));
	        	$objet2 = str_replace(" ", "", $objet);
	        	//$objet2 = strtoupper($objet2);
	        }else{
	        	$objet = $objet2 = "SANS OBJET";
	        }
	        

	        $from = $header_info->from[0]->mailbox."@".$header_info->from[0]->host; // expediteur
	        $to = $header_info->to[0]->mailbox."@".$header_info->to[0]->host; //destinataire
	        $cc = null;
	        $cc = $header_info->cc; //copie à
	        $tab_cc= array();
	        $read  = $overview[0]->seen ;
	        $date  = utf8_decode(imap_utf8($overview[0]->date));
	        $time = strtotime($date);
	        $m = array();

	        if(!is_null($cc)){
	        	foreach ($cc as $copie) {
	        		$tab_cc[] = mysqli_real_escape_string($DB_site2->link_id,$copie->mailbox.'@'.$copie->host);
	        	}	
	        }
	        if(in_array($header_info->from[0]->host,$hosts)){
        		$tablemail = "mail_outbox";
        		$moyen = "mail_out";
        		$rep = "outbox";
        		$check = $to;
        	}else{
        		$tablemail = "mail_inbox";
        		$moyen = "mail_in";
        		$rep = "inbox";
        		$check = $from;
            }
            
            print "Message ".$messageID." ".$from." => ".$to." ".substr($objet,0,100)."\n";
      
        	//Filtre sur l'objet et corps du mail
        	//on commence par l'objet
	        if(preg_match("/#PJT([0-9]+)(-(\d+))?#/i", $objet2, $m) == 1) {//PROJET
	        	
	        	$projetid=$m[1];
	        	if(isset($m[3])) $sqlset.=", userid ='".$m[3]."'";
	        	$type = "suivi_client";
	        	$projet = checkid($projetid,'pjt'); 
	        	$sqlset = " , projetid = '".$projetid."', compta = 'non'";

	        }elseif( preg_match("/#CLI([0-9]{1,5})#/i", $objet2,$m) == 1) {//CLIENT
	        	
	        	$userid = userid($m[1]);
	        	$sqlset = " ,userid='".$userid."', compta = 'non'";
	        	$type = "suivi_client";
	        	$client = checkid($userid,'client');

	        }elseif(preg_match("/#SAV([0-9]{5,6})-([0-9]{1,4})#/i", $objet2,$m) == 1) {//SAV
	        	
	        	$facturid = $m[1];
	        	$savid = $m[2];
		       	$userid= userid2($DB_site2, 'facture',$facturid);
	        	$sqlset = " , facturid='".$facturid."', savid ='".$savid."', userid='".$userid."'";
	        	$type = "suivi_commande";
	        	$sav = checkid($savid,'sav');

	        }elseif(preg_match("/#CDE([0-9]{5,6})#/i", $objet2,$m) == 1) {//COMMANDE

	        	$facturid = $m[1];
	        	$userid= userid2($DB_site2, 'facture',$facturid);
	        	$sqlset = " , facturid='".$facturid."', userid='".$userid."'";
	        	$type = "suivi_commande";
	        	$cmd = checkid($facturid,'cde');

	        }elseif(preg_match("/#CDE([0-9]{4})#/i", $objet2,$m) == 1) {//COMMANDE STOCK

	        	$stock_cmd_id = $m[1];
	        	$sqlset = " , stock_cmd_id='".$stock_cmd_id."'";
	        	$type = "suivi_stock";
	        	$cmd = checkid($stock_cmd_id,'stock');

	        }elseif(preg_match("/#PJT([0-9]+)(-(\d+))?#/i", $notagmsg,$m) == 1) {//PROJET //CORPS DU MAIL 
	        	
	        	$projetid=$m[1];
	        	$sqlset = " , projetid = '".$projetid."', compta = 'non'";
	        	if(isset($m[3])) $sqlset.=", userid ='".$m[3]."'";
	        	$type = "suivi_client";
	        	$projet = checkid($projetid,'pjt');

	        }elseif(preg_match("/#SAV([0-9]{5,6})-([0-9]{1,4})#/i", $notagmsg, $m) == 1) {//SAV

	        	$facturid = $m[1];
	        	$savid = $m[2];
	        	$userid= userid2($DB_site2, 'facture',$facturid);
	        	$sqlset = " , facturid='".$facturid."', savid ='".$savid."', userid = '".$userid."'";
	        	$type = "suivi_commande";
	        	$sav = checkid($savid,'sav');

	        }elseif(preg_match("/#CDE([0-9]{5,6})#/i", $notagmsg,$m) == 1) {//COMMANDE
	        	
	        	$facturid = $m[1];
	        	$userid= userid2($DB_site2, 'facture',$facturid);
	        	$sqlset = " , facturid='".$facturid."', userid='".$userid."'";
	        	$type = "suivi_commande";
	        	$cmd = checkid($facturid,'cde');
	        	
	        }elseif(preg_match("/#CDE([0-9]{4})#/i", $notagmsg,$m) == 1) {//COMMANDE STOCK

	        	$stock_cmd_id = $m[1];
	        	$sqlset = " , stock_cmd_id='".$stock_cmd_id."'";
	        	$type = "suivi_stock";
	        	$cmd = checkid($stock_cmd_id,'stock');

	        }elseif(preg_match("/#CLI([0-9]{1,5})#/i", $notagmsg,$m) == 1) { //CLIENT

	        	$userid = $m[1];
	        	$sqlset = " ,userid='".$userid."', compta = 'non'";
	        	$type = "suivi_client";
	        	$client = checkid($userid,'client');

	        }elseif(stripos($objet, "#CONF#") !== false || stripos($objet, "#prive#") !== false || stripos($objet, "#CONFIDENTIEL#") !== false ) {
	        	//On supprime les messages privés et confidentiels
	        	$conf = true;

            }elseif(!is_null(is_fourn($DB_site2, $check)) && !strpos($check, $mail_admin)){
	        	$fourn_name = is_fourn($DB_site2, $check);
	        	$sqlset =" , fournisseur='".mysqli_real_escape_string($DB_site2->link_id,$fourn_name)."'";
	        	$type = "suivi_fournisseur"; 
	        	$fourn = true;
	        }elseif(!is_null(is_client($DB_site2, $check))){
	        	//Pas de code, on trie avec l'adresse mail.
	        	$userid = intval(is_client($DB_site2, $check));
	        	$sqlset = " ,userid='".$userid."', compta = 'non'";
	        	$type='suivi_client';
	        	$client=true;
	        }

	       	
	       	if(!$client && !$cpt && !$projet && !$sav && !$cmd && !$fourn && !$conf){
	       		if(!is_null(is_client($DB_site2, $check))){
	       			//Pas de code, on trie avec l'adresse mail.
		        	$userid = intval(is_client($DB_site2, $check));
		        	$sqlset = " ,userid='".$userid."', compta = 'non'";
		        	$type='suivi_client';
		        	$client=true;
	       		}
	       	}

	       	if(!$conf){
	       		//on enregistre le mail dans un fichier txt
	            $f=fopen($rootpath."configurations/".$host."/mails/$rep/".$from."_".$time.".txt","w+");
	            fputs($f,imap_fetchbody($inbox, $email_number,""));
	            fclose($f);
	       	}

        	/**** FIN DECODAGE DU CONTENU DU MAIL **********/

        	/*** GESTION DES PIECES JOINTES -- recuperation depuis le mail  ***/

        	$attachments=array();
	        if(isset($structure->parts) && count($structure->parts)) {
	        	$attachments = pj($structure->parts, $inbox, $email_number);	 
	       	}
			/* Fin gestion pieces jointes -- recuperation depuis le mail */

		    
			/* CODE DE TEST POUR AFFICHER LE CONTENU DES MAILS	
        	$output.= '<div class="toggle'.($read ? 'read' : 'unread').'">';
        	$output.= '<span class="from">From: '.$from.'</span>';
        	$output.= '<span class="date">on '.$date.'</span>';
        	$output.= '<br /><span class="subject">Subject('.$structure->encoding.'): '.$objet.'</span> ';
        	$output.= '</div>';
        	$output.= '<div class="body">'.$message.'</div><hr />';
			*/

        	$message = mysqli_real_escape_string($DB_site2->link_id,trim($message));
        	if($message == '') $message = mysqli_real_escape_string($DB_site2->link_id,trim(imap_fetchbody($inbox, $email_number, "")));
        	$objet = mysqli_real_escape_string($DB_site2->link_id,$objet);
        	
        	//gestion de la copie
        	if(!empty($tab_cc)){
	        	$cc = implode(';', $tab_cc);
	        	$to = $to .';' .$cc;
	        }
        	
        	if($client || $cpt || $projet || $sav || $cmd || $fourn){ // Si on a réussi a identifier le mail pour tri

        		//On vérifie si le mail vient d'un fournisseur.
        		if(!$fourn){
        			if(!is_null(is_fourn($DB_site2, $check)) && !strpos($check, $mail_admin) ){
	        			$fourn_name = is_fourn($DB_site2, $check);
	        			$sqlset .=" , fournisseur='".mysqli_real_escape_string($DB_site2->link_id,$fourn_name)."'";
	        		}
        		}

        		// on verifie si le mail n'a pas deja ete enregistre, EVITE LES DOUBLONS
        		$lastmail2 = $DB_site2->query_first("SELECT COUNT(*) FROM $tablemail WHERE uniqueid ='$messageID'");
        		if($lastmail2[0] == 0){

	        		// mise à jour de la date de reception du dernier mail de suivi client dans la table utilisateur
	        		if($client || $cpt || $projet){
	        			$sql = "UPDATE utilisateur SET date_".$tablemail."_suivi_client='".$time."' WHERE userid = '".$userid."' ";
                    	$DB_site2->query($sql);
	        		}

	        		$sql = "INSERT INTO $tablemail SET date = '".$time."',`from`='".mysqli_real_escape_string($DB_site2->link_id,$from)."', `to` ='".mysqli_real_escape_string($DB_site2->link_id,$to)."', subject='".$objet."', body='".$message."', uniqueid ='$messageID'";
	        		$sql .=$sqlset;
	        		$DB_site2->query($sql);
	        		$emailid = $DB_site2->insert_id();

	        		$txtlog = "############################\n SQL :".$sql."\n\n";
	        		$txtlog .="BODY ==> $message\n############################\n";
		        	//on enregistre le mail dans un fichier txt
		            /*$f=fopen($rootpath."configurations/".$host."/mails/logs.txt","a");
		            fwrite($f,$txtlog);
		            fclose($f);*/

                    print "\033[0;32m".$tablemail." ".$sqlset."\033[0m\n";

	        		//on enregistre la PJ. A modifier pour le chemin.
	        		if(count($attachments)!=0){
			        	foreach($attachments as $at){
			            	if($at['is_attachment'] !== false){ //on exclu la pièce jointe du logo mcav signature
			        			$at['ext'] = encodage_mail($at['ext']);
			        			$at['name'] = encodage_mail($at['name']);
			        			if(!pj_fourn($DB_site2,$at,$host,$rootpath)){//on verifie que ce n'est pas une image de signature
			        				$DB_site2->query("INSERT INTO mail_attachment SET moyen='$moyen', mailid=".$emailid.", name='".mysqli_real_escape_string($DB_site2->link_id,$at['name'])."', ext='".mysqli_real_escape_string($DB_site2->link_id,$at['ext'])."', size='".$at['size']."'");
			            			$attachment_id = $DB_site2->insert_id();
			                		file_put_contents($rootpath."configurations/".$host."/mails/attachments/$year/".$attachment_id.".".$at['ext'], $at['attachment']);
			        			}
			            	}
			        	}
                    }
                    
                    if(isset($projetid) && intval($projetid)>0 && $tablemail=="mail_inbox"){
                        $pjt=new projet($projetid);
                        $pjt->rappel(0,""); // on enlève le dernier rappel
                        $pjt->reactive();//si le projet etait en etat perdu on le reactive
                    }
                    
	        	}else{
                    print "\033[0;31mEmail dupliqué\033[0m\n";
	        	}
	        	
	        	$client = $cpt = $projet = $sav = $cmd = $fourn= false ;
	        	$type = "";

        	}else{ //mail non trié on l'enregistre dans la table mail_to_check
        		$lastmail2 = $DB_site2->query_first("SELECT COUNT(*) FROM mail_to_check WHERE uniqueid ='$messageID'");
        		if($lastmail2[0] == 0 && !$conf ){
	        		$sql = "INSERT INTO mail_to_check SET date = '".$time."', `to` ='".mysqli_real_escape_string($DB_site2->link_id,$to)."', `from` = '".mysqli_real_escape_string($DB_site2->link_id,$from)."', subject='".$objet."', body='".$message."', uniqueid ='".$messageID."', type='$moyen'";
	        		$DB_site2->query($sql);
	        		$emailid = $DB_site2->insert_id();

	        		//on enregistre la PJ. A modifier pour le chemin.
	        		//on enregistre pas pour le moment car non utilisé et prends bcp de place sur serveur
	        		/*if(count($attachments)!=0){
			        	foreach($attachments as $at){
			            	if($at['is_attachment'] !== false){ //on exclu la pièce jointe du logo mcav signature
			        			$at['ext'] = encodage_mail($at['ext']);
			        			if(!pj_fourn($DB_site2,$at,$host,$rootpath)){//on verifie que ce n'est pas une image de signature
			        				$DB_site2->query("INSERT INTO mail_attachment SET moyen='mail_to_check', mailid=".$emailid.", name='".mysqli_real_escape_string($DB_site2->link_id,$at['name'])."', ext='".mysqli_real_escape_string($DB_site2->link_id,$at['ext'])."', size='".$at['size']."'");
			            			$attachment_id = $DB_site2->insert_id();
			                		file_put_contents($rootpath."configurations/".$host."/mails/attachments/$year/".$attachment_id.".".$at['ext'], $at['attachment']);
			        			}
			            	}
			        	}
			    	}*/
			    	print "\033[0;33mMail to check\033[0m\n";
                }else{
                    print "\033[0;31mEmail dupliqué\033[0m\n";
	        	}
                

        		//on supprime le mail de la boite une fois qu'il a été enregistré dans la bdd
// 	        	imap_delete($inbox, $email_number); //marque le mail pour effacement
// 	        	imap_expunge($inbox); //efface tous les mails marque pour effacement

        	}
        	
        	if(!$test){
                //on supprime le mail de la boite une fois qu'il a été enregistré dans la bdd
                imap_delete($inbox, $email_number); //marque le mail pour effacement
                imap_expunge($inbox); //efface tous les mails marque pour effacement
            }

        	$conf = false;
        	$message = "";
    	}

    	//echo $output;
	}else
        print "Pas d'email\n";

	imap_close($inbox);
    
}else{

    print 'Connection failed'.imap_last_error(). "\n";
    
}

$DB_site2->query("UPDATE cron SET actif='0' WHERE nom = 'tracking_mail'");


function pj_fourn($DB,$attachment,$host,$rootpath){
	//fonction qui permet de determiné si la pj est enregistrée en tant que signature (admin/save_pjmail.php)
	$id = uniqid();
	$name = $id.".".$attachment['ext'];
	$path = $rootpath."configurations/".$host."/mails/attachments/tmp/";

	file_put_contents($path.$name, $attachment['attachment']);
	$md5 = md5_file($path.$name);
	unlink($path.$name);
	
	$result = $DB->query_first("SELECT * FROM pj_mail WHERE md5='".$md5."'");

	if(is_null($result))
		return false;
	else
		return true;
}

function cleanAtt($ext){
	$result = str_replace(array('?=','=?utf-8?Q?'), '', $ext);
	return $result;
}

function checkid($id, $type){
	global $DB_site2;

	switch ($type) {
		case 'client':

			$client = $DB_site2->query_first("SELECT userid FROM utilisateur WHERE userid =".intval($id));
			if($client[0] == intval($id))
				return true;
			else
				return false;

			break;
		
		case 'cde':

			$cde = $DB_site2->query_first("SELECT facturid FROM facture WHERE facturid =".intval($id));
			if($cde[0] == intval($id))
				return true;
			else
				return false;

			break;

		case 'pjt':

			$pjt = $DB_site2->query_first("SELECT projetid FROM projet WHERE projetid =".intval($id));
			if($pjt[0] == intval($id))
				return true;
			else
				return false;

			break;

		case 'sav':

			$sav = $DB_site2->query_first("SELECT id FROM sav WHERE id =".intval($id));
			if($sav[0] == intval($id))
				return true;
			else
				return false;

			break;

		case 'stock':
			$stock_cmd = $DB_site2->query_first("SELECT id FROM stock_cmd WHERE id=".intval($id));
			if($stock_cmd[0] == intval($id))
				return true;
			else
				return false;

			break;


	}
}

function messageID($mail_overview){

	$messageID = $mail_overview[0]->message_id;
    if(empty($messageID)){
        $messageID=md5($mail_overview[0]->date.$mail_overview[0]->subject);
    }else{
        $messageID = preg_replace("/<?([^@]+)@.+/","$1",$messageID);
    }

	return $messageID;
}

function is_fourn($DB,$adresse_mail){
	$adresse_mail = mysqli_real_escape_string($DB->link_id,$adresse_mail);
	$fournisseur = $DB->query_first("SELECT * FROM fournisseur WHERE mail ='$adresse_mail' GROUP BY societe");

	return $fournisseur['societe'];

}

function is_client($DB,$adresse_mail){

	$client = $DB->query_first("SELECT userid FROM utilisateur WHERE mail ='".mysqli_real_escape_string($DB->link_id,$adresse_mail)."' OR userid IN (SELECT userid FROM utilisateur_assoc WHERE utilisateur_assoc_email='".mysqli_real_escape_string($DB->link_id,$adresse_mail)."' )");
	return $client[0];

}

function decode_mail($mail_stream,$parts, $email_number){
	
	$messages = array();
	$i = 0;
	if (sizeof($parts) > 0 ) {
		foreach ($parts as $key => $value) {
			
			$message = imap_fetchbody($mail_stream,$email_number,"1.1"); //1.1 juste le contenu du mail, pas les content etc
	    	
	    	if($message == "") $message = imap_fetchbody($mail_stream,$email_number,"1");

		    switch ($value->encoding) {
		    	# 7BIT
			    case 0:
			        $messages[$key] = $message;
			        break;

			    #OTHER
			    case 5:
			        $messages[$key] = $message ;
			        break;

			    # 8BIT
			    case 1:
			        $messages[$key] = quoted_printable_decode(imap_8bit($message));
			        break;

			    # BINARY
			    case 2:
			        $messages[$key] = imap_binary($message);
			        break;

			    # BASE64
			    case 3:
			        $messages[$key] = imap_base64($message);
			        break;

			    # QUOTED-PRINTABLE
			    case 4:
			        $messages[$key] = quoted_printable_decode($message);
			        break;
			    
		    }
		    $i++;
		}
	}

	//$message = implode(" ", $messages);
	if(sizeof($parts)>0)
		$message = $messages[0];
	elseif(sizeof($parts)>1)
		$message = $messages[1];
	else
		$message = "";

    return $message;
}

function userid($userid){

	while(substr($userid, 0,1) == '0'){
		$userid = substr($userid, 1);
	}

	return $userid;
}

function userid2($DB,$type,$id){

	switch ($type) {
		case 'facture':
			$sql = "SELECT userid FROM facture WHERE facturid='".$id."'";
			break;
	}

	$result = $DB->query_first($sql);

	return $result[0];

}

function pj($parts, $mbox, $email_number){

	$attachments = array();

	for($i = 0; $i < count($parts); $i++) {
        $attachments[$i] = array(
          'is_attachment' => false,
          'filename' => '',
          'name' => '',
          'ext' => '',
          'size' => '',
          'attachment' => '');

        if($parts[$i]->ifdparameters) {
        	foreach($parts[$i]->dparameters as $object) {
        		
            	if(strtolower($object->attribute) == 'filename') {
             		$attachments[$i]['is_attachment'] = true;
             		$attachments[$i]['filename'] = encodage_mail($object->value);
             		$attachments[$i]['ext'] = pathinfo($attachments[$i]['filename'], PATHINFO_EXTENSION);
             		$attachments[$i]['size'] = $parts[$i]->bytes;
           		}
         	}
       	}

        if($parts[$i]->ifparameters) {
        	foreach($parts[$i]->parameters as $object) {
           		if(strtolower($object->attribute) == 'name') {
             		$attachments[$i]['is_attachment'] = true;
             		$attachments[$i]['name'] =encodage_mail($object->value);
             		$attachments[$i]['ext'] = pathinfo($attachments[$i]['name'], PATHINFO_EXTENSION);
             		$attachments[$i]['size'] = $parts[$i]->bytes;
             		$mime = imap_fetchmime($mbox, $email_number,$i);
           		}
         	}
       	}

       	if($attachments[$i]['is_attachment']) {
        	$attachments[$i]['attachment'] = imap_fetchbody($mbox, $email_number, $i+1);
         	if($parts[$i]->encoding == 3) { // 3 = BASE64
           		$attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
         	}
        	elseif($parts[$i]->encoding == 4) { // 4 = QUOTED-PRINTABLE
        		$attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
        	}
    	}             
	}

	return $attachments;
}
//https://fr.wikipedia.org/wiki/Percent-encoding
function encodage_mail($txt){
	$replace = array("=C3=80"=>"À","=C3=A0"=>"à","=C3=81"=>"Á","=C3=A1"=>"á","=C3=82"=>"Â","=C3=A2"=>"â","=C3=83"=>"Ã","=C3=A3"=>"ã","=C3=84"=>"Ä","=C3=A4"=>"ä","=C3=85"=>"Å","=C3=A5"=>"å","=C3=86"=>"Æ","=C3=A6"=>"æ","=C3=87"=>"Ç","=C3=A7"=>"ç","=C3=90"=>"Ð","=C3=B0"=>"ð","=C3=88"=>"È","=C3=89"=>"É","=C3=A8"=>"è","=C3=A9"=>"é","=C3=8A"=>"Ê","=C3=AA"=>"ê","=C3=8B"=>"Ë","=C3=AB"=>"ë","=C3=8C"=>"Ì","=C3=AC"=>"ì","=C3=8D"=>"Í","=C3=AD"=>"í","=C3=8E"=>"Î","=C3=AE"=>"î","=C3=8F"=>"Ï","=C3=AF"=>"ï","=C3=91"=>"Ñ","=C3=B1"=>"ñ","=C3=92"=>"Ò","=C3=B2"=>"ò","=C3=93"=>"Ó","=C3=B3"=>"ó","=C3=94"=>"Ô","=C3=B4"=>"ô","=C3=95"=>"Õ","=C3=B5"=>"õ","=C3=96"=>"Ö","=C3=B6"=>"ö","=C5=93"=>"œ","=C5=92"=>"Œ","=C3=98"=>"Ø","=C3=B8"=>"ø","=C3=9F"=>"ß","=C3=99"=>"Ù","=C3=B9"=>"ù","=C3=9A"=>"Ú","=C3=BA"=>"ú","=C3=9B"=>"Û","=C3=BB"=>"û","=C3=9C"=>"Ü","=C3=BC"=>"ü","=C3=9D"=>"Ý","=C3=BD"=>"ý","=C3=9E"=>"Þ","=C3=BE"=>"þ","=C5=B8"=>"Ÿ","=C3=BF"=>"ÿ","=2e"=>".","=23"=>"#","=24"=>"$","=26"=>"&","=27"=>"'","=28"=>"(","=29"=>")","=2A"=>"*","=2B"=>"+","=2C"=>",","=2F"=>"/","=3A"=>":","=3B"=>";","=3D"=>"=","=3F"=>"?","=40"=>"%","=5B"=>"[","=5D"=>"]","_"=>" ","=2D"=>"-","=E2=80=99"=>"’","=C2=B0"=>"°");

	$escape = array("=?","Q?","UTF-8?","?=","?");

	foreach ($replace as $key => $value) {
		$pos = stripos($txt, $key);
		if($pos !== false){
			$txt = str_ireplace($key, $value, $txt);
		}
	}

	$txt = str_ireplace($escape, '', $txt);

	return $txt;
}

function get_part($mbox, $i, $mime_type, $structure = false, $part_number = false) {
    if (!$structure) {
        $structure = imap_fetchstructure($mbox, $i);
    }
    if($structure) {
// var_dump(get_mime_type($structure->type));
        if($mime_type == get_mime_type($structure)) {

        	if(!$part_number) {
            	$part_number = "1";
        	}
	    	$text = imap_fetchbody($mbox, $i, $part_number);
            
	        if($structure->encoding == 3) {
	            return imap_base64($text);
	        } else if ($structure->encoding == 4) {
	            return imap_qprint($text);
	        } else {
	            return $text;
	        }
        }
        if ($structure->type == 1) { /* multipart */
            print "Content multipart\n";
            while (list($index, $sub_structure) = each($structure->parts)) {
                if ($part_number) {
                    $prefix = $part_number . '.';
                }
                $data = get_part($mbox, $i, $mime_type, $sub_structure, $prefix . (@$index + 1));
                if ($data) {
                    return $data;
                }
            }
        }
    }
    return false;
}  

function get_mime_type(&$structure) {
    $primary_mime_type = array("TEXT", "MULTIPART", "MESSAGE", "APPLICATION", "AUDIO", "IMAGE", "VIDEO", "OTHER");
    if($structure->subtype) {
        return $primary_mime_type[(int) $structure->type] . '/' . $structure->subtype;
    }
    return "TEXT/PLAIN";
}

?>