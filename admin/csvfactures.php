<?php

require "admin_global.php";

//ini_set("memory_limit","20M");

$filename = 'csv/factures.csv';
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
else 
	{
	echo "Le fichier $filename n'est pas accessible en écriture.";
	}

$handle = fopen($filename, 'a');
$contenu="Factureid;Date;Civilité;Nom;Prénom;Email;Adresse;CP;Ville;Telephone;Raison sociale;Pays;Langue;Prix Port;Prix gestion;Moyen de paiement;Etat;Montant remise;Montant cadeau,Numéro suivi (coliposte)";
fwrite($handle, stripslashes(html_entity_decode($contenu)));
$factures=$DB_site2->query("SELECT * FROM facture f, pays p where f.paysid = p.paysid");
while($facture=$DB_site2->fetch_array($factures)) 
	{
	$contenu = "" ;
	switch ($facture[civilite])
		{
		case '0':
			$facture[civilite] = "Monsieur" ;
			break ;
		case '1':
			$facture[civilite] = "Madame" ;
			break ;
		case '2':
			$facture[civilite] = "Mademoiselle" ;
			break ;
		case '3':
			$facture[civilite] = "Mademoiselle" ;
			break ;
		}
	switch ($facture[langue])
		{
		case 'FR':
			$facture[langue] = "Français" ;
			break ;
		case 'DE':
			$facture[langue] = "Allemand" ;
			break ;
		case 'EN':
			$facture[langue] = "Anglais" ;
			break ;
		case 'IT':
			$facture[langue] = "Italien" ;
			break ;
		case 'SP':
			$facture[langue] = "Espagnol" ;
			break ;
		}
	switch ($facture[moyenid])
		{
		case '0':
			$facture[moyenid] = "Abandon" ;
			break ;
		case '1':
			$facture[moyenid] = "Carte Bleue" ;
			break ;
		case '2':
			$facture[moyenid] = "Chèque" ;
			break ;
		case '3':
			$facture[moyenid] = "Contre remboursement" ;
			break ;
		case '4':
			$facture[moyenid] = "Mandat postal" ;
			break ;
		case '5':
			$facture[moyenid] = "Virement" ;
			break ;
		case '6':
			$facture[moyenid] = "Paypal" ;
			break ;
		}
	switch ($facture[valide])
		{
		case '0':
			$facture[valide] = "Non traitée" ;
			break ;
		case '1':
			$facture[valide] = "Validée" ;
			break ;
		case '2':
			$facture[valide] = "Refus commerçant" ;
			break ;
		case '3':
			$facture[valide] = "Refusée" ;
			break ;
		case '4':
			$facture[valide] = "Abandon internaute" ;
			break ;
		case '5':
			$facture[valide] = "Expediée" ;
			break ;
		}
	$contenu.="\n";	
	$contenu.=$facture[facturid]; 
	$contenu.=";";
	$contenu.=$facture[datefacture];
	$contenu.=";";
	$contenu.=$facture[civilite]; 
	$contenu.=";";
	$contenu.=$facture[nom]; 
	$contenu.=";";
	$contenu.=$facture[prenom]; 
	$contenu.=";";
	$contenu.=$facture[mail]; 
	$contenu.=";";
	$contenu.=$facture[adresse]; 
	$contenu.=";";
	$contenu.=$facture[codepostal]; 
	$contenu.=";";
	$contenu.=$facture[ville]; 
	$contenu.=";";
	$contenu.=$facture[telephone]; 
	$contenu.=";";
	$contenu.=$facture[raisonsociale]; 
	$contenu.=";";
	$contenu.=$facture[libelle]; 
	$contenu.=";";
	$contenu.=$facture[langue]; 
	$contenu.=";";
	$contenu.=$facture[prixport]; 
	$contenu.=";";
	$contenu.=$facture[prixgestion]; 
	$contenu.=";";
	$contenu.=$facture[moyenid]; 
	$contenu.=";";
	$contenu.=$facture[valide]; 
	$contenu.=";";
	$contenu.=$facture[montantremise]; 
	$contenu.=";";
	$contenu.=$facture[montantcadeau]; 
	$contenu.=";";
	$contenu.=$facture[numero_suivi]; 
	$lignesfacture=$DB_site2->query("SELECT * FROM lignefacture where factureid = '$facture[facturid]'");
	// en-tête des lignes facture
	if ($DB_site2->num_rows($lignesfacture))
		{
		$contenu.="\n";
		$contenu.=""; 
		$contenu.=";";
		$contenu.="Ligne";
		$contenu.=";";
		$contenu.="Id Article"; 
		$contenu.=";";
		$contenu.="Référence"; 
		$contenu.=";";
		$contenu.="Libellé"; 
		$contenu.=";";
		$contenu.="Quantité"; 
		$contenu.=";";
		$contenu.="Prix unitaire"; 
		$contenu.=";";
		$contenu.="TVA"; 
		$contenu.=";";
		$contenu.="Caractéristiques"; 
		$contenu.=";";
		$contenu.=""; 
		$contenu.=";";
		$contenu.=""; 
		$contenu.=";";
		$contenu.=""; 
		$contenu.=";";
		$contenu.=""; 
		$contenu.=";";
		$contenu.=""; 
		$contenu.=";";
		$contenu.=""; 
		$contenu.=";";
		$contenu.=""; 
		$contenu.=";";
		$contenu.=""; 
		$contenu.=";";
		$contenu.=""; 
		$contenu.=";";
		$contenu.=""; 
		$contenu.=";";
		$contenu.=""; 
		$contenu.=";";
		$contenu.=""; 
		} 
	while($lignefacture=$DB_site2->fetch_array($lignesfacture)) 
		{
		$caracts = "" ;
		$lignefacturecaracts=$DB_site2->query("SELECT * FROM lignefacturecaracteristique where lignefactureid = '$lignefacture[lignefactureid]'");
		while ($lignefacturecaract=$DB_site2->fetch_array($lignefacturecaracts))
			{
			$modif = "" ;
			if ($lignefacturecaract[differenceprix] != 0)
				{
				if (substr($lignefacturecaract[differenceprix], 0, 1) == "-")
					{
					$modif = "($lignefacturecaract[differenceprix] €)" ;
					}
				else
					{
					$modif = "(+$lignefacturecaract[differenceprix] €)" ;
					}
				}
			$caracts .= strtoupper($lignefacturecaract[libelle_caract]) . " : $lignefacturecaract[libelle_caractval] $modif" ;
			}
		$contenu.="\n";
		$contenu.=""; 
		$contenu.=";";
		$contenu.=$lignefacture[lignefactureid];
		$contenu.=";";
		$contenu.=$lignefacture[artid]; 
		$contenu.=";";
		$contenu.=$lignefacture[artcode]; 
		$contenu.=";";
		$contenu.=$lignefacture[libelle]; 
		$contenu.=";";
		$contenu.=$lignefacture[qte]; 
		$contenu.=";";
		$contenu.=$lignefacture[prix]; 
		$contenu.=";";
		$contenu.=$lignefacture[tva]; 
		$contenu.=";";
		$contenu.=$caracts; 
		$contenu.=";";
		$contenu.=""; 
		$contenu.=";";
		$contenu.=""; 
		$contenu.=";";
		$contenu.=""; 
		$contenu.=";";
		$contenu.=""; 
		$contenu.=";";
		$contenu.=""; 
		$contenu.=";";
		$contenu.=""; 
		$contenu.=";";
		$contenu.=""; 
		$contenu.=";";
		$contenu.=""; 
		$contenu.=";";
		$contenu.=""; 
		$contenu.=";";
		$contenu.=""; 
		$contenu.=";";
		$contenu.=""; 
		$contenu.=";";
		$contenu.=""; 
		}
	fwrite($handle, stripslashes(html_entity_decode($contenu)));
	}


	fclose($handle);
	$file = $rootpath."admin/".$filename; 
	header('Content-Description: File Transfer'); 
	header('Content-Type: application/force-download'); 
	header('Content-Length: ' . filesize($file)); 
	header('Content-Disposition: attachment; filename=' . basename($file)); 
	readfile($file); 
	exit;

?>
