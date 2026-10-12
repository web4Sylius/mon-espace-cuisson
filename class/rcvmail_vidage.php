<?php

include_once("host_config_admin.php");
include_once($rootpath."configurations/".$host."/config_mail.php");
include($rootpath."class/email.php");

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
    	
	        //on supprime le mail de la boite une fois qu'il a été enregistré dans la bdd
            imap_delete($inbox, $email_number); //marque le mail pour effacement
            imap_expunge($inbox); //efface tous les mails marque pour effacement
    	}

    	//echo $output;
	}else
        print "Pas d'email\n";

	imap_close($inbox);
    
}else{

    print 'Connection failed'.imap_last_error(). "\n";
    
}




?>
