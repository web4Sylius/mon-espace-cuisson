<?

function email($destinataire, $sujet ,$messhtml, $mailcontact) {
	global $host, $fichierjoint, $fichierjoint2 ;
	
	$tab_nom_sans_www = explode(".", $host) ;
	$nom_sans_www = $tab_nom_sans_www[1] . "." . $tab_nom_sans_www[2] ;
	$mailfrom = "noreply@$nom_sans_www" ;
	$namefrom = $nom_sans_www ;
	$replyto = $mailcontact ;
	
	$boundary = "_".md5 (uniqid (rand()));
	$entete = "MIME-Version: 1.0\n";
	$entete .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\n";
	$entete .= "X-Sender: <$host>\n";
	$entete .= "X-Mailer: PHP\n";
	$entete .= "X-auth-smtp-user: $mailcontact \n";
	$entete .= "X-abuse-contact: abuse@$nom_sans_www \n";
	$entete .= "Reply-to: $replyto\n";
	$entete .= "From:$namefrom <www-data@ns202967.ovh.net>\n";
	$message .= "--$boundary\n";
	$message .= "Content-Type: text/html; charset=\"iso-8859-1\"\n"; 
	$message .= "Content-Transfer-Encoding: 8bit\n\n"; 
	$message .= $messhtml; 
	if ($fichierjoint[type]!=""){$message .= "\n--$boundary\n";$message .= "Content-Type: $fichierjoint[type]; name=\"$fichierjoint[name]\"\n";$message .= "Content-Transfer-Encoding: base64\n";$message .= "\n";$message .= $fichierjoint[contenu] . "\n";}
	if ($fichierjoint2[type]!=""){$message .= "\n--$boundary\n";$message .= "Content-Type: $fichierjoint2[type]; name=\"$fichierjoint2[name]\"\n";$message .= "Content-Transfer-Encoding: base64\n";$message .= "\n";$message .= $fichierjoint2[contenu] . "\n";}
	$message .="\n\n";
	return @mail($destinataire, $sujet, $message, $entete); 
	}
	
$mesparametresgeneraux = $DB_site->query_first("SELECT mail_contact FROM parametresgeneraux");
$email_marchand = $mesparametresgeneraux[mail_contact];	

?>