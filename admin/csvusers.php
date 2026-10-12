<?php

require "admin_global.php";

$contenu="Numéro;Email;Mot de passe crypté;Civilité;Nom;Prénom;Adresse;CP;Ville;Tel;Raison sociale;Pays;Langue";
$users=$DB_site2->query("SELECT userid, mail, password, civilite, nom, prenom, codepostal, adresse, ville, telephone, raisonsociale, libelle,
						 langue from utilisateur u, pays p where u.paysid = p.paysid");
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



$filename = 'csv/utilisateurs.csv';
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
	fclose($handle);

	$file = $rootpath."admin/".$filename; 
	header('Content-Description: File Transfer'); 
	header('Content-Type: application/force-download'); 
	header('Content-Length: ' . filesize($file)); 
	header('Content-Disposition: attachment; filename=' . basename($file)); 
	readfile($file); 
	exit;
	} 
else 
	{
	echo "Le fichier $filename n'est pas accessible en écriture.";
	}

?>
