<?php
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");
require_once(SITE_PATH."/class/facture.class.php");
require_once(BACK_PATH."/adv/adv.class.php");
require_once(SITE_PATH."/class/stock.class.php");
require_once(BACK_PATH."/adv/webtel.class.php"); //Schenker & Heppner
require_once(BACK_PATH."/export/xlexport.php");

$transporteur = $_GET['transporteur'];
$adv_id       = $_GET['advid'];

$adv    = new adv($DB_site2, $adv_id);
$webtel = new webtel($DB_site2,$adv,$transporteur);

switch($transporteur){
	case 'schenkernat':
	case 'schenker':
		$webtel->filename = "expe_schenker_".$adv->facturid;
		$webtel->ext      = "txt";

		$webtel->lignes = array(
		    "S", //Séparateur de champs
		    $webtel->date_exp, //Date expédition
		    "01", //Code transporteur/prestation
                    $webtel->export?"60":$webtel->trspttype, //Code produit transport/facturation
		    "01", //Code expéditeur
		    "", //Libellé expéditeur
		    "", //Code destinataire
		    $webtel->raison_sociale, //Nom 1
		    $webtel->compl_nom,	//Nom 2
		    "",	//Nom 3
		    $webtel->adresse, //Adresse 1
		    $webtel->telephone != '' ? 'Tél : '.$webtel->telephone : '', //Adresse 2
		    "",	//Adresse 3
		    $webtel->pays, //Pays
		    $webtel->codepostal, //Code postal
		    $webtel->ville, //Localité
		    $webtel->interlocuteur,	//Contact
		    $webtel->interlocuteur != '' ? $webtel->telephone : '',	//Téléphone
		    "",	//Récépissé
		    $webtel->facturid, //Référence d'expédition
		    "P",	//Port ou Incoterm
		    $webtel->nb_manu, //Nombre d'UM
		    $webtel->nb_colis, //Nombre de colis
		    $webtel->nb_manu_consigne, //Nombre de supports consignés
		    $webtel->manu_consigne_type,	//Type de supports consignés
		    $webtel->poids,	//Poids réel
		    "", //Unités taxables
		    "",	//Type d'unités taxables
		    "",	//Volume
		    $webtel->ctr, //Contre-remboursement
		    $webtel->ctr != '' ? 'EUR' : '',	//Devise C-R
		    $webtel->valeur_declaree, //Valeur déclarée
		    $webtel->valeur_declaree != '' ? 'EUR' : '',	//Devise V D
		    "",	//Non utilisé actuellement
		    "",	//Non utilisé actuellement
		    "",	//Nature de marchandises
		    $webtel->info_trspt,	//Instructions de livraison
		    $webtel->info_comp,	//Remarques de livraison
		    $webtel->date_imperative,	//Date impérative de livraison
		    "",	//Code imprimante
		    $webtel->particulier, //Livraison particulier
		    $webtel->annonce, //Annonce au destinataire
		    $webtel->interlocuteur != '' ? $webtel->portable : '',	//Téléphone n°2 (portable)
		    $webtel->interlocuteur != '' ? $webtel->email : '',	//Adresse mail
		    "",	//Mètre plancher
		    "",	//Date de début livraison souhaitée
		    "",	//Heure de début livraison souhaitée
		    "",	//Date de fin livraison souhaitée
		    ""	//Heure de fin livraison souhaitée
		);
		break;
	case 'heppner':
		$webtel->filename = "expe_heppner_".$adv->facturid;
		$webtel->ext      = "txt";

		$webtel->lignes = array(
            $webtel->date_exp, //Date expédition
            "C3375", //Code expéditeur
            "HEPP", //Code transporteur
            "01", //Produit du transporteur (01 France, 04 international)
            "", //Code destinataire
            $webtel->raison_sociale, //Nom destinataire
            $webtel->compl_nom,	//Complément nom
            $webtel->interlocuteur,	//Interlocuteur
            "", //N° rue
            $webtel->adresse, //Rue
            $webtel->telephone != '' ? 'Tél : '.$webtel->telephone : '',	//Adresse 1
            "",	//Adresse 2
            $webtel->pays, //Pays
            $webtel->codepostal, //Code postal
            $webtel->ville, //Localité
            $webtel->nb_manu, //Nombre d'UM
            $webtel->nb_colis_strict, //Nombre de colis
            "", //Nombre unité de taxation
            "", //Type unité de taxation
            $webtel->poids,	//Poids réel
            "P",	//Port ou Incoterm
            $webtel->ctr, //Contre-remboursement
            $webtel->ctr != '' ? 'EUR' : '',	//Devise C-R
            $webtel->valeur_declaree, //Valeur déclarée
            $webtel->valeur_declaree != '' ? 'EUR' : '',	//Devise V D
            $webtel->facturid,	//Référence expédition 1
            "",	//Référence expédition 2
            $webtel->info_trspt, //Instructions de livraison
            $webtel->date_imperative,	//Date impérative de livraison
            "",	//Montant valeur marchandise totale
            "",	//Devise valeur marchandise
            $webtel->manu_consigne_type,	//Type de supports consignés
            $webtel->nb_manu_consigne, //Nombre de supports consignés
            $webtel->volume, //Volume total (INTERNATIONAL) 
            "", //Marque colis (UM non consignés) (INTERNATIONAL) 
            $webtel->nature, //Nature marchandise colis (INTERNATIONAL) 
            $webtel->poidsnet, //Poids net colis (INTERNATIONAL) 
            $webtel->poids, //Poids brut colis (INTERNATIONAL) 
            "", //Montant valeur marchandise colis
            "", //Devise valeur marchandise colis
            "", //Marque UM consignés (INTERNATIONAL)
            "", //Nature marchandise UM consignés (INTERNATIONAL) 
            "", //Poids net UM consignés (INTERNATIONAL)
            "", //Poids brut UM consignés (INTERNATIONAL)
            "", //Montant valeur marchandise UM consignés
            "", //Devise valeur marchandise UM consignés
            "", //Numéro récépissé
            "", //Livraison samedi
            $webtel->trspttype, //Services livraison
            $webtel->trspttypecontact //Tel ou date, selon service de livraison choisi
    	);
		break;
}        

$webtel->getCSV();