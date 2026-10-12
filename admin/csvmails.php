<?php

require "admin_global.php";

$contenu="Email;Type (client ou newsletter);Souhaite recevoir la newsletter\n";
$filename = 'csv/mails.csv';
if (is_writable($filename)) 
	{
	if (!$handle = fopen($filename, 'w')) 
		{
		echo "Impossible d'ouvrir le fichier ($filename)";
		exit;
		}
	if (fwrite($handle, stripslashes(html_entity_decode($contenu))) === FALSE) 
		{
		echo "Impossible d'écrire dans le fichier ($filename)";
		exit;
		}
	}

$tabusers = array();
$i = 0 ;
$users=$DB_site2->query("SELECT mail, allowemail from utilisateur");
while ($user=$DB_site2->fetch_array($users))
	{
	$tabusers[$i]['mail'] = $user[mail] ;
	$tabusers[$i]['type'] = "client" ;
	if ($user[allowemail] == 0)
		$user[allowemail] = "non" ;
	else
		$user[allowemail] = "oui" ;
	$tabusers[$i]['allow'] = $user[allowemail] ;
	$i++ ;
	}
$users=$DB_site2->query("SELECT adresse_mail, allow_email from mails_newsletter");
while ($user=$DB_site2->fetch_array($users))
	{
	$tabusers[$i]['mail'] = $user[adresse_mail] ;
	$tabusers[$i]['type'] = "newsletter" ;
	if ($user[allow_email] == 0)
		$user[allow_email] = "non" ;
	else
		$user[allow_email] = "oui" ;
	$tabusers[$i]['allow'] = $user[allow_email] ;
	$i++ ;
	}


/*
$tabusers = array(
       array("mail1", "mail2"), 
       array("client", "client")
      );*/
sort($tabusers);

foreach($tabusers as $key => $value)
	{
	$contenu = "" ;
	foreach($value as $key2 => $value2)
		$contenu .= "$value2;" ; 
	fwrite($handle, stripslashes(html_entity_decode($contenu)."\n")) ;
	}


/*
while($user=$DB_site2->fetch_array($users)) 
	{
	switch ($user[civilite])
		{
		case '0':
			$user[civilite] = "Monsieur" ;
			break ;
		case '1':
			$user[civilite] = "Madame" ;
			break ;
		case '2':
			$user[civilite] = "Mademoiselle" ;
			break ;
		}
	switch ($user[langue])
		{
		case 'FR':
			$user[langue] = "Français" ;
			break ;
		case 'DE':
			$user[langue] = "Allemand" ;
			break ;
		case 'EN':
			$user[langue] = "Anglais" ;
			break ;
		case 'IT':
			$user[langue] = "Italien" ;
			break ;
		case 'SP':
			$user[langue] = "Espagnol" ;
			break ;
		}
	$contenu.="\n";	
	$contenu.=$user[userid]; 
	$contenu.=";";
	$contenu.=$user[mail];
	$contenu.=";";
	$contenu.=$user[password]; 
	$contenu.=";";
	$contenu.=$user[civilite]; 
	$contenu.=";";
	$contenu.=$user[nom]; 
	$contenu.=";";
	$contenu.=$user[prenom]; 
	$contenu.=";";
	$contenu.=$user[adresse]; 
	$contenu.=";";
	$contenu.=$user[codepostal]; 
	$contenu.=";";
	$contenu.=$user[ville]; 
	$contenu.=";";
	$contenu.=$user[telephone]; 
	$contenu.=";";
	$contenu.=$user[raisonsociale]; 
	$contenu.=";";
	$contenu.=$user[libelle]; 
	$contenu.=";";
	$contenu.=$user[langue]; 
	}
*/



	fclose($handle);

	$file = $rootpath."admin/".$filename; 
	header('Content-Description: File Transfer'); 
	header('Content-Type: application/force-download'); 
	header('Content-Length: ' . filesize($file)); 
	header('Content-Disposition: attachment; filename=' . basename($file)); 
	readfile($file); 
	exit;
?>
