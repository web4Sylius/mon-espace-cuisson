<?php
require_once($_SERVER['DOCUMENT_ROOT']."/global.php");
$idVendeur='';
$facturid='';
$adresse="";
$codePays = "FR"; 
$codepostal="";
$xml = ""
	."<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>"
		."<ewp:applications xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:ewp=\"http://www.wertgarantie.de\" xsi:schemaLocation=\"http://www.wertgarantie.de/XMLSchema/ewp_application.xsd\">"
			."<application>"
				"<agent_number>$idVendeur</agent_number>"
			   ."<record_number>$facturid</record_number>"
			   ."<client_data>"
					."<address>"
						."<salutation>$civilite</salutation>"
						."<first_name>$prenom</first_name>"
						."<last_name>$nom</last_name>"
						."<house_number>Numéro de rue</house_number>"
					 	."<street><![CDATA[ $adresse ]]></street>"
						."<country_code>$codePays</country_code>"
						."<postal_code>$codepostal</postal_code>"
						."<city>$ville</city>"
					."</address>"
					."<birthdate>$datenaissance</birthdate>"
					."<phone1>$telephone</phone1>"
					."<mobile>$telephone2</mobile>"
					."<email>$email</email>"
					."<language_mark>FR</language_mark>"
				."</client_data>"
				."<sepa_information>"
					."<account_holder>Nom de famille du client</account_holder>"
					."<IBAN>Numéro IBAN</IBAN>"
				."</sepa_information>"
				."<object_data>"
					."<manufacturer>Marque appareil</manufacturer>"
					."<model>Modèle appareil</model>"
					."<object_code>Voir fichier de correspondances. Utiliser les valeurs de la colonne \"Code\"</object_code>"
					."<number>Numéro de série</number>"
					."<purchase_date>Date d'achat de l'appareil format JJ.MM.AAAA</purchase_date>"
					."<purchase_price>Prix d'achat de l'appareil</purchase_price>"
				."</object_data>"
				."<policy_type>EWP_SFG</policy_type>"
				."<product_type>Selon l'offre : ONE_ELECTRO_1 ou ONE_ELECTRO_2 ou ONE_ELECTRO_3</product_type>"
				."<application_code>EWP FR ATM 0416_2</application_code>"
				."<payment_schedule>1</payment_schedule>"
				."<payment_method>EINZUG</payment_method>"
				."<application_date>Date de souscription du contrat format JJ.MM.AAAA</application_date>"
				."<export_date>Date d'export du fichier XML format JJ.MM.AAAA</export_date>"
			."</application>"
		."</ewp:applications>";








?>