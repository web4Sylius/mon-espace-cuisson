<?php
require_once __DIR__.'/../secrets.php';
//SCRIPT de génération des fichiers CSV pour UK Warranty
//
//IMPORTANT : Le script a une "sécurité". Pour qu'il s'exécute correctement en prod, il faut lui passer le paramètre "prod=1". Sans ça, rien ne s'enverra à UK Warranty.
//
ini_set('display_errors',1);

header('Content-Type: text/html; charset=utf-8');

if(isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'localhost') !== false){
    $rootpath = "d:/wamp/www/ma-cave-a-vin/";
} else if(isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'test') !== false) {
    $rootpath = $_SERVER['DOCUMENT_ROOT']."/";
} else {
    $rootpath   = "/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/";
}

$host   = "www.ma-cave-a-vin.fr" ;
$nostat = 1;
require_once($rootpath."configurations/".$host."/config_mysql.php");
require_once($rootpath."class/mysql.php");
require_once($rootpath."admin/email.php");
require_once($rootpath."admin/class/sftp.php");
require_once($rootpath."class/garantie.class.php");
require_once($rootpath."admin/adv/adv.class.php");
require_once($rootpath."admin/adv/envoi_fournisseur.php");
require_once($rootpath."admin/export/xlexport.php");

if(is_null($DB_site)){
    $DB_site = new DB_Sql;
	$DB_site->appname      = 'boutique';
	$DB_site->appshortname = 'boutique';
	$DB_site->database     = $DBname;
	$DB_site->server       = $db_server;
	$DB_site->user         = $DBusername;
	$DB_site->password     = $DBpassword;
    $DB_site->connect();
}

$ftp_serveur  = '109.69.232.253';
$ftp_user     = 'MWC';
$ftp_password = UKWARRANTY_FTP_PASSWORD;

//Paramétrage fichiers
$dossier_distant = '';
$dossier_local   = 'admin/export/ukwarranty/';
$aEntetes = array(
	'Seller Code',
	'Sales Office',
	'Warranty URN',
	'Title',
	'First Name',
	'Last Name',
	'Company Name',
	'Email',
	'Adress 1',
	'Adress 2',
	'Town/City',
	'Region',
	'Postcode',
	'Telephone',
	'Mobile',
	'Mail Opt Out',
	'Retailer',
	'Place of Purchase',
	'Store Town',
	'Brand',
	'Serial Number',
	'Model',
	'Appliance',
	'Purchase Date',
	'Purchase Price',
	'Expire Date',
	'Warranty Code',
	'Warranty Price',
	'Warranty Start Date'
);

//Génération des fichiers CSV
$csvDA =  new xlexport("csv"); //2ans, sans gaz R134A ou R404
$csvDA->name          = date("d.m.Y")."_1_Acilyon_Domestic_Appliance_Sales";
$csvDA->csvsep        = "	";
$csvDA->striptags     = false;
$csvDA_filename_source      = $rootpath.$dossier_local.$csvDA->name.'.csv';
$csvDA_filename_destination = $dossier_distant.$csvDA->name.'.csv';
$csvDA->addrow($aEntetes, false);

$csvCA =  new xlexport("csv"); //2ans, avec gaz R134A ou R404
$csvCA->name          = date("d.m.Y")."_1_Acilyon_Commercial_Appliance_Sales";
$csvCA->csvsep        = "	";
$csvCA->striptags     = false;
$csvCA_filename_source      = $rootpath.$dossier_local.$csvCA->name.'.csv';
$csvCA_filename_destination = $dossier_distant.$csvCA->name.'.csv';
$csvCA->addrow($aEntetes, false);

$csvDW =  new xlexport("csv"); //+3ans, sans gaz R134A ou R404
$csvDW->name          = date("d.m.Y")."_1_Acilyon_Domestic_Warranty_Sales";
$csvDW->csvsep        = "	";
$csvDW->striptags     = false;
$csvDW_filename_source      = $rootpath.$dossier_local.$csvDW->name.'.csv';
$csvDW_filename_destination = $dossier_distant.$csvDW->name.'.csv';
$csvDW->addrow($aEntetes, false);

$csvCW =  new xlexport("csv"); //+3ans, sans gaz R134A ou R404
$csvCW->name          = date("d.m.Y")."_1_Acilyon_Commercial_Warranty_Sales";
$csvCW->csvsep        = "	";
$csvCW->striptags     = false;
$csvCW_filename_source      = $rootpath.$dossier_local.$csvCW->name.'.csv';
$csvCW_filename_destination = $dossier_distant.$csvCW->name.'.csv';
$csvCW->addrow($aEntetes, false);

//Récupération des commandes expédiées la veille
$expdate   = time()-86400;
$pasenvoi  = " AND efid=0"; //Evite de prendre en compte 2 fois la même garantie (efid != 0 si garantie déjà transmise à UK Warranty)
$writefile = false;
if(!isset($_GET['prod'])){
	$expdate  = intval($_GET['prod']);
	$pasenvoi = "";
}

$startmsg = "Garanties des commandes expédiées le ".date("d/m/Y",$expdate);
$listmsg  = "";

//Liste des commandes expédiées la veille, contenant au moins une garantie destinée à UK Warranty, qui n'ont pas déjà été transmises
$sql = "SELECT f.*, UNIX_TIMESTAMP(datefacture) AS tsdate, UNIX_TIMESTAMP(dateexpedition) AS tsenvoi,adv.id AS advid";
$sql .= " FROM facture f ";
$sql .= " INNER JOIN adv USING(facturid)";
$sql .= " WHERE 1";
$sql .= " AND adv.type 		   = 'garantie'";
$sql .= " AND adv.destinataire = 'UK Warranty'";
$sql .= isset($_GET['prod']) ? " AND dateexpedition = '".date("Y-m-d",$expdate)."'" : "";
$sql .= " AND valide IN (5,8)";
$sql .= $pasenvoi;
$reqfacture = $DB_site->query($sql);

while($facture = $DB_site->fetch_array($reqfacture)){

    $writecsv = false;

    //Coordonnées du client de la garantie
    $sql = "SELECT *
    		FROM utilisateur u, pays p 
    		WHERE 1
    		AND userid   = '".$facture['userid']."' 
    		AND u.paysid = p.paysid";
    $user = $DB_site->query_first($sql);
    
    //Si client présent dans facture_infos_garantie, il faut prendre ces informations là
    //Utile si un revendeur a revendu un de nos produit à un client => la garantie s'applique au client final, et non au revendeur qui sera pourtant indiqué en coordonnées de facturation dans l'admin 
    $tabInfosGarantie = array();
    $sql = "SELECT *
    		FROM facture_infos_garantie 
    		WHERE factureid = ".$facture['facturid'];
    $tabInfosGarantie = $DB_site->query_first($sql);
    
    //Remplacement des informations
    if(!empty($tabInfosGarantie)){
		$user['nom']        = $tabInfosGarantie['facture_infos_garantie_nom'];
		$user['prenom']     = $tabInfosGarantie['facture_infos_garantie_prenom'];
		$user['adresse']    = $tabInfosGarantie['facture_infos_garantie_adresse'];
		$user['codepostal'] = $tabInfosGarantie['facture_infos_garantie_cp'];
		$user['ville']      = $tabInfosGarantie['facture_infos_garantie_ville'];
		$user['telephone']  = $tabInfosGarantie['facture_infos_garantie_tel'];
		$user['mail']       = $tabInfosGarantie['facture_infos_garantie_mail'];
    }
    
    $adv = new adv($DB_site,$facture['advid']);

    //Sécurité pour vérifier que la commande est bien valide
    if(count($adv->lignes) > 0 && count($adv->fields) > 0) $writecsv = true;

	$datefacture = date("d/m/Y",$facture['tsdate']);
    
    if($writecsv){

        $writefile = true;
                  
        $listmsg .= "<br><br>Commande ".$facture['facturid']." du ".$datefacture."\n".$user['nom']." ".$user['prenom'];

		$listefl   = array();
		$totalpaht = 0;

		//Boucle sur chaque produit de la commande
        foreach($adv->lignes as $lignefactureid => $qte){

			//Informations sur l'article à garantir
			$sql = "SELECT lf.artid, lf.prix, lf.prix_revendeur, lfg.dureeReelle, lfg.differenceprix, a.artcode_eurocave, a.artcode, a.libelleFR, m.libelle AS marque, a.gaz";
			$sql .= " FROM lignefacture lf";
			$sql .= " INNER JOIN lignefacturegarantie lfg USING(lignefactureid)";
			$sql .= " INNER JOIN article a USING(artid)";
			$sql .= " INNER JOIN article_marque USING(artid)";
			$sql .= " INNER JOIN marque m USING (marqueid)";
			$sql .= " WHERE lignefactureid = ".$lignefactureid;
			$sql .= " AND lfg.fournisseur = 'UK Warranty'";
		    $article = $DB_site->query_first($sql);

		    //Récupération du type de gaz
		    $sql = "SELECT FR";
	    	$sql .= " FROM traduction";
	    	$sql .= " WHERE path = '".$article['gaz']."'";
	    	$type_gaz = $DB_site->query_first($sql)['FR'];

	    	if(strpos($type_gaz, 'R134') != false || strpos($type_gaz, 'R404') != false){ //commercial use
	    		$csv_seller_code = 'MWC';
	    	} else { //Domestic use
	    		$csv_seller_code = 'MWD';
	    	}

		    $garantieid = $adv->fields["garantieid_".$lignefactureid];
		    
		    //Informations sur la garantie achetée
		    $garantie = new garantie($DB_site,$facture['site_code'],$facture['langue'],$garantieid);

		    if($garantie->cumul == 2){ // pas d'extension

				$csv_warranty_code = 'PR02';
				$csv_prix_vente    = 0;
				$dureeGarantie     = $garantie->duree;
				$dureeExtGarantie  = 0;
				$date_effet 	   = date_create(date("Y-m-d",$facture[tsenvoi]));
				$dateEffetGarantie = date_format($date_effet,"d/m/Y"); 
				date_add($date_effet,date_interval_create_from_date_string("2 years"));
                date_add($date_effet,date_interval_create_from_date_string("-1 day"));
                $dateFinGarantie   = date_format($date_effet,"d/m/Y");
			
		    } else { // extension

				$csv_warranty_code = 'E23';
				$csv_prix_vente    = $article['differenceprix'];
				$dureeExtGarantie  = $garantie->duree;
				$dureeGarantie     = 0;
				$date_effet = date_create(date("Y-m-d",$facture[tsenvoi]));
                date_add($date_effet,date_interval_create_from_date_string("2 years"));
                date_add($date_effet,date_interval_create_from_date_string("-1 day"));
                $dateEffetGarantie = date_format($date_effet,"d/m/Y");
                date_add($date_effet,date_interval_create_from_date_string("3 years"));
                $dateFinGarantie = date_format($date_effet,"d/m/Y");

		    }

	        //Boucle pour gérer la quantité de chaque référence
		    for($i=0;$i<$qte;$i++){
				
				$csv_row = array(
					$csv_seller_code, //Seller Code
					'', //Sales Office
					'', //Warranty URN
					'', //Title
					str_replace(',','',$user['prenom']), //First name
					str_replace(',','',$user['nom']), //Last name
					str_replace(',','',$user['raisonsociale']), //Company Name
					$user['mail'], //Email
					str_replace(',','',$user['adresse']), //Adresse 1
					'', //Adresse 2
					str_replace(',','',$user['ville']), //Town/City
					'', //Region
					$user['codepostal'], //Postcode
					$user['telephone'], //Telephone
					'', //Mobile
					'No', //Mail Opt Out
					'', //Retailer
					'', //Place of Purchase
					'', //Store Town
					$article['marque'], //Brand
					'', //Serial Number
					$article['artcode']." / ".$article['artcode_eurocave'], //Model
					'Wine Cooler', //Appliance
					$dateEffetGarantie, //Purchase Date
					$article['prix_revendeur'] > 0 ? $article['prix_revendeur'] : $article['prix'], //Purchase Price
					$dateFinGarantie, //Expire Date
					$csv_warranty_code, //Warranty Code
					$csv_prix_vente, //Warranty Price
					$dateEffetGarantie //Warranty Start Date
				);
				
				if($csv_seller_code == 'MWD' && $csv_warranty_code == 'PR02'){ //Domestic Appliance
			    	$csvDA->addrow($csv_row, false);
			    } else if($csv_seller_code == 'MWD' && $csv_warranty_code == 'E23'){ //Commercial Appliance
			    	$csvCA->addrow($csv_row, false);
			    } else if($csv_seller_code == 'MWC' && $csv_warranty_code == 'PR02'){ //Domestic Warranty
			    	$csvDW->addrow($csv_row, false);
			    } else if($csv_seller_code == 'MWC' && $csv_warranty_code == 'E23') { //Commercial Warranty
			    	$csvCW->addrow($csv_row, false);
			    }
		    }

		    //Stockage de l'information comme quoi cette garantie a été commandée
		    $efl = new envoi_fournisseur_ligne($DB_site);
			$efl->type           = "garantie";
			$efl->qte            = $qte;
			$efl->paht           = $adv->fields["paht_".$lignefactureid];
			$efl->lignefactureid = $lignefactureid;
		    $listefl[] = $efl;
		    
		    $totalpaht += $adv->fields["paht_".$lignefactureid];
		    
		    $listmsg .= "<br>".$produit->artcode." (".html_entity_decode($article['libelleFR'],ENT_COMPAT,"UTF-8").") garantie ".($garantie->cumul==2?"+":"").$garantie->duree." ans à partir du ".$dateEffetGarantie;
		}
	
		//Stockage de l'information comme quoi cette garantie a été commandée
		$ef = new envoi_fournisseur($DB_site);
		$ef->facturid     = $facture[facturid];
		$ef->destinataire = 'UK WARRANTY';
		$ef->tarif        = $totalpaht;
		$ef->type         = 'garantie';
		
		//Finalisation de l'enregistrement de la commande de garanties
		if(isset($_GET['prod'])){
		    $ef->create();
		    $ef->create_lines($listefl);
		    $adv->efid=$ef->id;
		    $adv->update();
		}
    }
}

$msg = "";

if($writefile){

	//Création des fichiers sur le serveur
	$f = fopen($csvDA_filename_source,"w");
	fputs($f, $csvDA->buffer);
    fclose($f);

    $f2 = fopen($csvCA_filename_source,"w");
	fputs($f2, $csvCA->buffer);
    fclose($f2);

    $f3 = fopen($csvDW_filename_source,"w");
	fputs($f3, $csvDW->buffer);
    fclose($f3);

    $f4 = fopen($csvCW_filename_source,"w");
	fputs($f4, $csvCW->buffer);
    fclose($f4);

    //if(isset($_GET['prod'])){
		$ftp = new sftp($ftp_serveur,$ftp_user,$ftp_password);
		
		$mail = new email();
		$mail->destinataire = "webmaster@ma-cave-a-vin.fr,webmaster1@ma-cave-a-vin.fr";
		$mail->expediteur   = "webmaster@ma-cave-a-vin.fr";
		$mail->expname      = "Administrateur - MCAV";
		
		if($ftp->put($csvDA_filename_source, $csvDA_filename_destination) && $ftp->put($csvCA_filename_source, $csvCA_filename_destination) && $ftp->put($csvDW_filename_source, $csvDW_filename_destination) && $ftp->put($csvCW_filename_source, $csvCW_filename_destination))
		{
			$mail->subject = 'Notification de commandes de garanties UK Warranty';
			$mail->body    = 'Les commandes transférées ce jour se trouvent en pièce jointe';

			$nom_fichier1 = substr($csvDA_filename_source,strrpos($csvDA_filename_source,'/')+1,strlen($csvDA_filename_source));
			$mail->attachfile($csvDA_filename_source,$nom_fichier1);

			$nom_fichier2 = substr($csvCA_filename_source,strrpos($csvCA_filename_source,'/')+1,strlen($csvCA_filename_source));
			$mail->attachfile($csvDA_filename_source,$nom_fichier2);

			$nom_fichier3 = substr($csvDW_filename_source,strrpos($csvDW_filename_source,'/')+1,strlen($csvDW_filename_source));
			$mail->attachfile($csvDA_filename_source,$nom_fichier3);

			$nom_fichier4 = substr($csvCW_filename_source,strrpos($csvCW_filename_source,'/')+1,strlen($csvCW_filename_source));
			$mail->attachfile($csvDA_filename_source,$nom_fichier4);

			$mail->sendmail();
		}
		else
		{
			$mail->subject = "Erreur traitement garanties UK Warranty";
			$mail->body    = ""
			. "Il y a eu un problème lors de la commande de garantie chez UK Warranty."
			. "<br>"
			. "Pour constater les erreurs, vous pouvez tenter <a href=\"http://" . $host . "/admin/ukwarranty.php?test=1\">cette page</a>.";
		}		
	//}

	$startmsg .= $listmsg;

} else {
	$msg="Aucun produit n'a de garantie";
}

print "<br>".$msg."\n";
?>
