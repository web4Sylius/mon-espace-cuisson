<?php

$rootpath="../../";
$host="www.mon-espace-cuisson.com" ;
ini_set("memory_limit","1024M");
ini_set("max_execution_time",60);

// ini_set("display_errors", E_ALL);

include_once($rootpath."configurations/".$host."/config_mysql.php");
include($rootpath."class/mysql.php");
include($rootpath."configurations/".$host."/config.php");
include_once($rootpath."class/fonctions.php");
include_once($rootpath."class/stock.class.php");
// include($rootpath."class/categorie.class.php");
// include("../adv/adv.class.php");


$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();

$extend_html=false;
if($_GET['debug']==1){
	$extend_html=true;
}

// ini_set("display_errors", E_ALL);

function col($field,$var, $cdata=false){
	if(strpos($var,"&")!== false) {
		$cdata=true;
	}
	if($cdata==true){
		$var="<![CDATA[".$var."]]>";
	}
	return "<".$field.">".$var."</".$field.">\n";
}

function mydate($timestamp){
	if($timestamp==0){
		return "";
	} else {
		return date("Y-m-d H:i:s", $timestamp );
	}
}

function ouinon($bool){
	if($bool==0){
		return "non";
	} else {
		return "oui";
	}
}

function suivi_commande($suivi, $nom_element){
	// ce qui se trouve dans la table suivi_commande
	switch ($suivi['moyen']) {
		case "":
			$moyen="indéfini";
			break;
		case "tel_out":
			$moyen="Téléphone sortant";
			break;
		case "tel_in":
			$moyen="Téléphone entrant";
			break;
		case "mail_in":
			$moyen="Mail entrant";
			break;
		case "mail_out":
			$moyen="Mail sortant";
			break;
		case "contact":
			$moyen="Autre contact";
			break;
	}
	$xml="<".$nom_element." id=\"".$suivi['id']."\">\n";
		$xml.=col("moyen",					$moyen );
		$xml.=col("date_creation",			mydate($suivi['date']) );
		$xml.=col("administrateur",			$suivi['commercial'] );
		$xml.=col("contenu",				$suivi['suivi'], true );
		$xml.=col("type",					$suivi['type'] );
		$xml.=col("destinataire",			$suivi['destinataire'] );
		$xml.=col("rappel",					mydate($suivi['rappel']) );
		$xml.=col("rappeltype",				$suivi['rappeltype'] );
		$xml.=col("jour",					$suivi['jour'] );
		$xml.=col("rappel_administrateur",	$suivi['pour'] );
	$xml.="</".$nom_element.">\n";
	return $xml;
}

function suivi_client($suivi, $nom_element){
	// ce qui se trouve dans la table suivi_client
	switch ($suivi['moyen']) {
		case "":
			$moyen="indéfini";
			break;
		case "tel_out":
			$moyen="Téléphone sortant";
			break;
		case "tel_in":
			$moyen="Téléphone entrant";
			break;
		case "mail_in":
			$moyen="Mail entrant";
			break;
		case "mail_out":
			$moyen="Mail sortant";
			break;
		case "contact":
			$moyen="Autre contact";
			break;
	}
	$xml="<".$nom_element." id=\"".$suivi['id']."\">\n";
		$xml.=col("moyen",					$moyen );
		$xml.=col("date_creation",			mydate($suivi['date']) );
		$xml.=col("administrateur",			$suivi['commercial'] );
		$xml.=col("contenu",				$suivi['suivi'], true );
		$xml.=col("type",					$suivi['type'] );
		$xml.=col("rappel",					mydate($suivi['rappel']) );
		$xml.=col("rappeltype",				$suivi['rappeltype'] );
		$xml.=col("jour",					$suivi['jour'] );
		$xml.=col("rappel_administrateur",	$suivi['pour'] );
	$xml.="</".$nom_element.">\n";
	return $xml;
}

function mail_inbox($mail, $nom_element){
	global $DB_site;
	global $pj_user;
	global $extend_html;
	// ce qui se trouve dans la table mail_inbox
	switch ($mail['suivitype']) {
		case "":
			$suivitype="indéfini";
			break;
		case "suivi_client":
			$suivitype="suivi_client";
			break;
		case "suivi_fournisseur":
			$suivitype="suivi_fournisseur";
			break;
	}
	$xml="<".$nom_element." id=\"".$mail['id']."\">\n";
		$xml.=col("date_creation",			mydate($mail['date']) );
		$xml.=col("from",					$mail['from'] );
		$xml.=col("to",						$mail['to'] );
		$xml.=col("subject",				$mail['subject']);
		
		if(!$extend_html){
			$mail['body']=strip_tags($mail['body']);
			$mail['body'] = preg_replace_callback("/(&#[0-9]+;)/", function($m) { return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES"); }, $mail['body']);
			$mail['body'] = html_entity_decode($mail['body']);
			if(strlen($mail['body']>255)){
				$mail['body']=substr($mail['body'],0, 252)."...";
			}
		}
		$xml.=col("body",					$mail['body'], true );
		
		if($mail['pj']==""){
			// $piecejointe="";
		} else {
			$user_file=$DB_site->query_first("SELECT * FROM user_files WHERE userid='".$mail['userid']."' AND date='".$mail['date']."' AND size!='0' ");
			if($user_file['id']>0){
				$pj_user[]=$user_file['id'];
				$piecejointe="https://www.mon-espace-cuisson.com/admin/userfiles/index.php?file=".$user_file['id'].".".$user_file['ext'];
				$xml.="<piece_jointe id=\"".$user_file['id']."\">\n";
					$xml.=col("piece_jointe",			$piecejointe );
					$xml.=col("piece_jointe_size",		$user_file['size'] );
				$xml.="</piece_jointe>\n";
			}
		}
		$xml.=col("suivitype",				$suivitype );
		$xml.=col("suiviid",				$mail['suiviid']);
	$xml.="</".$nom_element.">\n";
	return $xml;
}



function mail_outbox($mail, $nom_element){
	global $DB_site;
	global $pj_user;
	// ce qui se trouve dans la table mail_outbox
	switch ($mail['suivitype']) {
		case "":
			$suivitype="indéfini";
			break;
		case "suivi_client":
			$suivitype="suivi_client";
			break;
		case "suivi_fournisseur":
			$suivitype="suivi_fournisseur";
			break;
	}
	
	$xml="<".$nom_element." id=\"".$mail['id']."\">\n";
		$xml.=col("date_creation",			mydate($mail['date']) );
		$xml.=col("from",					$mail['from'] );
		$xml.=col("to",						$mail['to'] );
		$xml.=col("subject",				$mail['subject']);
		
		if(!$extend_html){
			$mail['body']=strip_tags($mail['body']);
			$mail['body'] = preg_replace_callback("/(&#[0-9]+;)/", function($m) { return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES"); }, $mail['body']);
			$mail['body'] = html_entity_decode($mail['body']);
			if(strlen($mail['body']>255)){
				$mail['body']=substr($mail['body'],0, 252)."...";
			}
		}
		
		$xml.=col("body",					$mail['body'], true );
		if($mail['pj']==""){
			// $piecejointe="";
		} else {
			$user_file=$DB_site->query_first("SELECT * FROM user_files WHERE userid='".$mail['userid']."' AND date='".$mail['date']."' AND size!='0' ");
			if($user_file['id']>0){
				$pj_user[]=$user_file['id'];
				$piecejointe="https://www.mon-espace-cuisson.com/admin/userfiles/index.php?file=".$user_file['id'].".".$user_file['ext'];
				$xml.="<piece_jointe id=\"".$user_file['id']."\">\n";
					$xml.=col("piece_jointe",			$piecejointe );
					$xml.=col("piece_jointe_size",		$user_file['size'] );
				$xml.="</piece_jointe>\n";
			}
		}
		$xml.=col("suivitype",				$suivitype );
		$xml.=col("suiviid",				$mail['suiviid']);
	$xml.="</".$nom_element.">\n";
	return $xml;
}


function url_categ($catid, $langue){
	global $DB_site;
	$cat=$DB_site->query_first("SELECT url".$langue." AS url, parentid, menu_appartenance FROM categorie WHERE catid=".$catid." ");
	$url=$cat['url'];
	if($cat['parentid']!=0){
		$cat2=$DB_site->query_first("SELECT url".$langue." AS url, parentid, menu_appartenance FROM categorie WHERE catid=".$cat['parentid']." ");
		if($cat2['parentid']>0){
			$cat3=$DB_site->query_first("SELECT url".$langue." AS url, parentid, menu_appartenance FROM categorie WHERE catid=".$cat2['parentid']." ");
			$url=str_replace(".htm","/",$cat3['url']).$url;
			// 1/ cuisiniere-piano-cuisson/accessoires-piano-cuisiniere.htm
			// 2/ cuisiniere-piano-cuisson/accessoires-piano-cuisiniere/accessoires-cuisiniere-piano-GLEMGAS.htm
			// 3/ cuisiniere-piano-cuisson/accessoires-piano-cuisiniere/piano-cuisiniere-accessoires-cuisson-GLEMGAS.htm
		} else {
			$url=str_replace(".htm","/",$cat2['url']).$url;
		}
	}
	return $url;
}

function url_article($artid, $langue){
	global $DB_site;
	$art=$DB_site->query_first("SELECT libelle".$langue." AS libelle FROM article WHERE artid=".$artid." ");
	$marque=$DB_site->query_first("SELECT libelle FROM article_marque INNER JOIN marque USING(marqueid) WHERE artid='".$artid."' ");
	$url=url_rewrite($art['libelle']."-".$marque['libelle'])."-art".$artid.".htm";
	return $url;
}

$article_actif=array(	0 => "invisible",
						1 => "actif",
						2 => "supprimé",
						3 => "archivé définitivement",
						4 => "nouveau (à vérifier)",
						5 => "archivé temporairement",
						6 => "en cours de création");

$metas=array(	"article_accessoire",
				"article_bbq",
				"article_capacite",
				"article_cellule_refroidissement",
				"article_cave",
				"article_congel",
				"article_congel_dependant",
				"article_credence",
				"article_cuisiniere",
				"article_cuisinierepiano",
				"article_divers",
				"article_evier",
				"article_four",
				"article_foyers",
				"article_frigo",
				"article_frigo_dependant",
				"article_froid",
				"article_hotte",
				"article_laveseche",
				"article_lavevaisselle",
				"article_meuble",
				"article_piano",
				"article_piecedetache",
				"article_plancha",
				"article_prestation",
				"article_rotissoire",
				"article_table_cuisson",
				"article_tiroir");


/*
à exporter :
- clients
	>- devis/commande/facture (et suivi de commande, garantie)
	>- projets (et suivi de projet et PJ)
	>- sav (et suivi de sav et PJ)
	>- suivi de client
	>- PJ
	>- mails entrant/sortant
	>- ADV
	>- envois Fournisseurs
	
- catalogue
	>- catégories
	>- produits
	>- caracteristiques
	>- photos
	>- marque
	>- cross selling (article_conseil ; article_similaire)
	>- prix par fournisseurs (article_fournisseur)
	>- prix des concurrents (spy)
	>- lots
	>- articles de remplacement
	>- pj de devis (article_devisdesc)
	>- métas données (à rassembler) :
		- article_accessoire (?)
		- article_bbq
		- article_capacité
		- article_cave
		- article_congel
		- article_congel_dependant (???)
		- article_credence
		- article_cuisiniere
		- article_cuisinierepiano
		- article_divers
		- article_evier
		- article_four
		- article_foyer
		- article_frigo
		- article_frigo_dependant (???)
		- article_froid
		- article_hotte
		- article_laveseche
		- article_lavevaisselle
		- article_meuble
		- article_piano
		- article_piecedetache
		- article_plancha
		- article_prestation
		- article_rotissoire
		- article_table_cuisson
		- article_tiroir
- garanties
- stocks (et commande de stock)
- fournisseurs (et tous leurs contacts)


exemple :
<xml>
	<users>
		  <user id="1234">
				  <email>bob@bob.fr</email>
				   <adresse>123 bob</adresse>
		  </user>
	</users>
</xml>



*/

$xml="<?xml version=\"1.0\" encoding=\"UTF-8\"?><xml id=\"".time()."\">";

///////////////////////////////////////////////////////////////////////////////////
// 1 => clients
///////////////////////////////////////////////////////////////////////////////////

$xml.="<users>";
// $users=$DB_site->query(" SELECT * FROM utilisateur WHERE 1=1 AND userid NOT IN ('16085', '16076') AND userid IN(16107) ORDER BY userid DESC LIMIT 10 "); // à changer plus tard // AND userid='16156'
$users=$DB_site->query(" SELECT * FROM utilisateur WHERE 1=0 ORDER BY userid DESC LIMIT 20 "); // à changer plus tard // AND userid='16156'
while($user=$DB_site->fetch_array($users)){
	$pj_user=array();
	$pj_user[]=0;
	$xml.="<user id=\"".$user['userid']."\">\n";
		switch ($user['civilite']) {
			case 0:
				$civilite="Monsieur";
				break;
			case 1:
				$civilite="Madame";
				break;
			case 2:
				$civilite="Mademoiselle";
				break;
		}
		switch ($user['utilisateur_type_suivi_id']) {
			case 0:
			case 1:
				$type_suivi="Standard";
				break;
			case 2:
				$type_suivi="Prioritaire";
				break;
		}
		$pays=$DB_site->query_first(" SELECT libelle FROM pays WHERE paysid='".$user['paysid']."' ");
		$pays_livraison=$DB_site->query_first(" SELECT libelle FROM pays WHERE paysid='".$user['paysid_livraison']."' ");
		$typeclient_pro=$DB_site->query_first(" SELECT libelle FROM typeclient_pro WHERE id='".$user['typeclient_pro']."' ");
		
		$xml.=col("email",				$user['mail']);
		$xml.=col("civilite",			$civilite);
		
		$xml.=col("nom",				$user['nom']);
		$xml.=col("prenom",				$user['prenom']);
		$xml.=col("codepostal",			$user['codepostal']);
		$xml.=col("adresse",			$user['adresse']);
		$xml.=col("ville",				$user['ville']);
		$xml.=col("telephone",			$user['telephone']);
		$xml.=col("portable",			$user['portable']);
		$xml.=col("raisonsociale",		$user['raisonsociale']);
		$xml.=col("dateinscription",	$user['dateinscription']);
		$xml.=col("pays",				$pays['libelle']);
		$xml.=col("tvaintracom",		$user['tvaintracom']);
		$xml.=col("siret",				$user['siret']);
		$xml.=col("profession",			$user['profession']);
		$xml.=col("site",				$user['site']);
		$xml.=col("langue",				$user['langue']);
		
		
		$xml.=col("raisonsociale_livraison",	$user['raisonsociale_livraison']);
		$xml.=col("nom_livraison",				$user['nom_livraison']);
		$xml.=col("prenom_livraison",			$user['prenom_livraison']);
		$xml.=col("codepostal_livraison",		$user['codepostal_livraison']);
		$xml.=col("adresse_livraison",			$user['adresse_livraison']);
		$xml.=col("ville_livraison",			$user['ville_livraison']);
		$xml.=col("pays_livraison",				$pays['libelle']);
		$xml.=col("telephone_livraison",		$user['telephone_livraison']);
		$xml.=col("infotransporteur",			$user['infotranspo']);
		
		$xml.=col("allowemail",			$user['allowemail']);
		$xml.=col("allowsms",			$user['allowsms']);
		$xml.=col("contacthour",		$user['contacthour']);
		$xml.=col("infos",				$user['infos']);
		$xml.=col("connu",				$user['connu']);
		$xml.=col("vip",				$user['vip']);
		
		$xml.=col("administrateur",		$user['commercial']);
		$xml.=col("conseil",			$user['conseil']);
		
		
		// utilisateur.besoin 				=> à mettre qq part ?
		// utilisateur.echeance_projet 		=> à mettre qq part ?
		// utilisateur.livraison_projet 	=> à mettre qq part ?
		// utilisateur.projet_etatid 		=> à mettre qq part ?
		// utilisateur.projet_rappel 		=> à mettre qq part ?
		// utilisateur.budget_projet 		=> à mettre qq part ?
		
		
		$xml.=col("code_comptable",		$user['code411']);
		$xml.=col("provenance",			$user['provenance']);
		$xml.=col("type_suivi",			$type_suivi);
		$xml.=col("fuseau_horaire",		$user['fuseau_horaire']);
		$xml.=col("pourcentage_remise",	$user['pourcentage_remise']);
		
		$xml.=col("gclid",				$user['gclid']);
		$xml.=col("typeclient_pro",		$typeclient_pro['libelle']);
		
		// utilisateur_assoc
		$utilisateur_assocs=$DB_site->query(" SELECT * FROM utilisateur_assoc WHERE userid='".$user['userid']."' ");
		while($utilisateur_assoc=$DB_site->fetch_array($utilisateur_assocs)){
			$xml.="<assoc>\n";
				switch ($utilisateur_assoc['utilisateur_assoc_civilite']) {
					case 0:
						$civilite="Monsieur";
						break;
					case 1:
						$civilite="Madame";
						break;
					case 2:
						$civilite="Mademoiselle";
						break;
				}
				$xml.=col("fonction",		$utilisateur_assoc['utilisateur_assoc_fonction']);
				$xml.=col("civilite",		$civilite);
				$xml.=col("prenom",			$utilisateur_assoc['utilisateur_assoc_prenom']);
				$xml.=col("nom",			$utilisateur_assoc['utilisateur_assoc_nom']);
				$xml.=col("telephone",		$utilisateur_assoc['utilisateur_assoc_tel']);
				$xml.=col("portable",		$utilisateur_assoc['utilisateur_assoc_portable']);
				$xml.=col("email",			$utilisateur_assoc['utilisateur_assoc_email']);
			$xml.="</assoc>\n";
		}
		
		$factures=$DB_site->query(" SELECT * FROM facture WHERE userid='".$user['userid']."' ");
		while($facture=$DB_site->fetch_array($factures)){
			switch ($facture['valide']) {
				case 0:
				case 2:
				case 3:
					$type_document="devis";
					$numero_facture="";
					break;
				case 1:
				case 7:
					$type_document="commande";
					$numero_facture="";
					break;
				case 5:
				case 6:
				case 8:
					$type_document="facture";
					$numero_facture=$facture['numerofacture'];
					break;
			}
			$xml.="<".$type_document." id=\"".$facture['facturid']."\">\n";
				
				switch ($facture['civilite']) {
					case 0:
						$civilite="Monsieur";
						break;
					case 1:
						$civilite="Madame";
						break;
					case 2:
						$civilite="Mademoiselle";
						break;
				}
				$pays=$DB_site->query_first(" SELECT libelle FROM pays WHERE paysid='".$facture['paysid']."' ");
				$moyen_paiement=$DB_site->query_first(" SELECT libelleFR FROM moyenpaiement WHERE moyenid='".$facture['moyenid']."' ");
				$xml.=col("pdf_devis",			"https://www.mon-espace-cuisson.com/devis.php?devisid=". $facture['facturid'] . "&p=". strrev(md5($facture['paysid'] . "devis")) );  ///////////////////////////////////// à faire
				if($type_document=="facture"){
					$xml.=col("pdf_facture",	"https://www.mon-espace-cuisson.com/facture2.php?facturid=". $facture['facturid'] . "&passe=". $facture['passefacture'] . "&nopub=1" );
				}
				$xml.=col("moyen_paiement",		$moyen_paiement['libelleFR'] );
				$xml.=col("date_creation",		mydate($facture['heurefacture']) );
				$xml.=col("prix_port",			$facture['prix_port'] ); // ===
				$xml.=col("tva_port",			$facture['tva_port']); // ===
				// $xml.=col("total_ttc",""); // ===
				// $xml.=col("total_ht",""); // ===
				$xml.=col("date_livraison",		mydate($facture['datelivraison']) );
				$xml.=col("civilite",			$civilite );
				$xml.=col("nom",				$facture['nom'] );
				$xml.=col("prenom",				$facture['prenom']);
				$xml.=col("codepostal",			$facture['codepostal'] );
				$xml.=col("adresse",			$facture['adresse'] );
				$xml.=col("adresse2",			$facture['adresse2'] );
				$xml.=col("ville",				$facture['ville'] );
				$xml.=col("telephone",			$facture['telephone'] );
				$xml.=col("raisonsociale",		$facture['raisonsociale'] );
				$xml.=col("pays",				$pays['libelle']);
				$xml.=col("mail",				$facture['mail'] );
				$xml.=col("langue",				$facture['langue'] );
				$xml.=col("cadeau",""); 		// === => sous entité ?
				$xml.=col("montantcadeau",""); 	// ===
				$xml.=col("administrateur",		$facture['commercial']);
				$xml.=col("gclid",				$facture['gclid']);
				$xml.=col("mode_livraison",""); // ===
				$xml.=col("infotranspo",		$facture['infotranspo']);
				$xml.=col("info_importante",	$facture['info_importante']);
				$xml.=col("incoterms",			$facture['incoterms']);
				$xml.=col("provenance",			$facture['provenance']);
				$advlfids=array();
				$lignefactures=$DB_site->query(" SELECT * FROM lignefacture WHERE factureid='".$facture['facturid']."' ");
				while($lignefacture=$DB_site->fetch_array($lignefactures)){
					$xml.="<article id=\"".$lignefacture['artid']."\">\n";
						$xml.=col("ref_interne",			$lignefacture['artcode'] );
						$xml.=col("quantite",				$lignefacture['qte'] );
						$xml.=col("libelle",				$lignefacture['libelle'], true );
						$xml.=col("prix_vente_ttc",			$lignefacture['prix'] );
						$xml.=col("taux_tva",				$lignefacture['tva'] );
						$xml.=col("prix_achat_ht",			$lignefacture['paht'] );
						$xml.=col("ecopart_ttc",			$lignefacture['ecopart'] );
						$xml.=col("remboursement",			$lignefacture['remboursement'] );
						$xml.=col("date_remboursement",		$lignefacture['date_remboursement'] );
						$xml.=col("modif_par",				$lignefacture['modifby'] );
						// $xml.=col("dissociation_lot",""); // === => sous entité ?
						// $xml.=col("pack",""); // deprecated
						// $xml.=col("platinehome",""); // seulement sur MCAV
						$lignefacturecaracteristiques=$DB_site->query(" SELECT * FROM lignefacturecaracteristique WHERE lignefactureid='".$lignefacture['lignefactureid']."' ");
						while($lignefacturecaracteristique=$DB_site->fetch_array($lignefacturecaracteristiques)){
							// lignefacturecaracteristique
							$xml.="<option id=\"".$lignefacturecaracteristique['caractvalid']."\">\n";
								$xml.=col("libelle",				$lignefacturecaracteristique['libelle_caract'] );
								$xml.=col("valeur",					$lignefacturecaracteristique['libelle_caractval'] );
								$xml.=col("diff_prix_vente_ttc",	$lignefacturecaracteristique['differenceprix'] );
								$xml.=col("diff_prix_achat_ht",		$lignefacturecaracteristique['paht'] );
							$xml.="</option>\n";
						}
						$lignefacturegaranties=$DB_site->query(" SELECT * FROM lignefacturegarantie WHERE lignefactureid='".$lignefacture['lignefactureid']."' ");
						while($lignefacturegarantie=$DB_site->fetch_array($lignefacturegaranties)){
							// lignefacturegarantie
							$pays=$DB_site->query_first(" SELECT libelle FROM pays WHERE paysid='".$lignefacturegarantie['pays']."' ");
							$type=$DB_site->query_first(" SELECT libelle_type FROM garantie_type WHERE id_type='".$lignefacturegarantie['type']."' ");
							$xml.="<garantie id=\"".$lignefacturegarantie['garantieid']."\">\n";
								$xml.=col("libelle",				$lignefacturegarantie['libelle'] );
								$xml.=col("prix_vente_ttc",			$lignefacturegarantie['differenceprix'] );
								$xml.=col("prix_achat_ht",			$lignefacturegarantie['paht'] );
								$xml.=col("prix_public_ttc",		$lignefacturegarantie['ppi'] );
								$xml.=col("duree",					$lignefacturegarantie['duree'] );
								$xml.=col("fournisseur",			$lignefacturegarantie['fournisseur'] );
								$xml.=col("code_fournisseur",		$lignefacturegarantie['code_fournisseur'] );
								$xml.=col("pays",					$pays['libelle'] );
								$xml.=col("duree_reelle",			$lignefacturegarantie['dureeReelle'] );
								$xml.=col("type",					$type['libelle_type'] );
							$xml.="</garantie>\n";
						}
					$xml.="</article>\n";
				}
				
				
				///////// ADV !!!!!!!!!!!!!!!!!
				if($type_document!="devis"){
					// $adv=new adv($DB_site);
					// https://www.mon-espace-cuisson.com/admin/facture.php?action=editer&facturid=298728
					
					// $liste=$adv->listadv($facture['facturid'] , "fourn" );
					
					// Liste de types :
					// - fourn (fournisseur de produit)
					// - sls (installateur )
					// - stock (lieu de stockage)
					// - trspt (transporteur)
					// - garantie (SFG)
					// - miseenservice (encore SFG)
					
					// idfacturid
					// lignes (array :  id => qte )
					// date_creation
					// utilisateur
					// type
					// destinataire
					// devise
					// envoi
					// envoi_heure
					// efid   => envoi_fournisseur
					// fields ( array nom => valeur )
					// steps (array étape => array ( utilisateur , date ) )
					// maxstep
					// files
					// locked
					// modif
					// utfields (utilisateur de fields plus haut)
					// datefields (date de fields plus haut )
					
					$type_adv=array("fourn" => "fournisseur de produit" ,
									"sls" => "installateur",
									"stock" => "lieu de stockage",
									"trspt" => "transporteur",
									"garantie" => "prestataire de garantie",
									"miseenservice" => "prestataire de mise en service");
					
					
					foreach($type_adv as $tadv => $tadvvalue){
						$advs=$DB_site->query(" SELECT * FROM adv WHERE facturid='".$facture['facturid']."' AND type='".$tadv."' ");
						while($adv=$DB_site->fetch_array($advs)){
							$xml.="<achat id=\"".$adv['id']."\">\n";
								$xml.=col("type",				$tadvvalue );
								$xml.=col("fournisseur",		$adv['destinataire'] );
								$xml.=col("date_creation",		mydate($adv['date']) );
								$xml.=col("administrateur",		$adv['utilisateur'] );
								if($adv['efid']==0){
									$xml.=col("etat_commande",				"non commandé au fournisseur" );
								} else {
									$xml.=col("etat_commande",				"commandé au fournisseur" );
									$envoi_fournisseur=$DB_site->query_first(" SELECT * FROM envoi_fournisseur WHERE id='".$adv['efid']."'  ");
									$xml.=col("prix_achat_ht",			$envoi_fournisseur['tarif'] );
									$xml.=col("date_commande",			mydate($envoi_fournisseur['date']) );
									$xml.=col("transporteur",			$envoi_fournisseur['trspt'] );
									$xml.=col("lieu_destination",		$envoi_fournisseur['livr'] );
									
									$envoi_fournisseur_lignes=$DB_site->query(" SELECT * FROM envoi_fournisseur_ligne WHERE efid='".$adv['efid']."' ");
									while($envoi_fournisseur_ligne=$DB_site->fetch_array($envoi_fournisseur_lignes)){
										$xml.="<achat_ligne>\n";
											$xml.=col("article_id",			$envoi_fournisseur_ligne['ligne_facture_id'] );
											$xml.=col("date_creation",		mydate($envoi_fournisseur_ligne['date']) );
											$xml.=col("indate",				mydate($envoi_fournisseur_ligne['indate']) );
											$xml.=col("outdate",			mydate($envoi_fournisseur_ligne['outdate']) );
											$xml.=col("origin_in",			mydate($envoi_fournisseur_ligne['origin_in']) );
											$xml.=col("origin_out",			mydate($envoi_fournisseur_ligne['origin_out']) );
											$xml.=col("prix_achat_ht",		$envoi_fournisseur_ligne['paht'] );
											$xml.=col("ecopart_ht",			$envoi_fournisseur_ligne['ecopart'] );
										
										$xml.="</achat_ligne>\n";
									}
									$adv_fields=$DB_site->query(" SELECT * FROM adv_fields WHERE advid='".$adv['id']."' AND name NOT LIKE ('%\\_%') ");
									while($adv_field=$DB_site->fetch_array($adv_fields)){
										if($adv_field['name']=="mazetcout"){
											$adv_field['name']="prix_achat_ht_transport";
										}
										if($adv_field['name']=="fournisseur"){
											$adv_field['name']="fournisseur_email";
										}
										if(strpos($adv_field['name'],"[")!==false){
											$explodetmp=explode("[",substr($adv_field['name'],0,-1));
											$adv_field['name']=$explodetmp[0];
											$idtmp=$explodetmp[1];
											$xml.="<".$adv_field['name']." id=\"".$idtmp."\">".$adv_field['value']."</".$adv_field['name'].">\n";
										} else {
											$xml.=col($adv_field['name'],		$adv_field['value'] );
										}
									}
									
								}
							$xml.="</achat>\n";
						}
					}
				}
				
				
				$lignefactureinstallations=$DB_site->query(" SELECT * FROM lignefactureinstallation WHERE factureid='".$facture['facturid']."' ");
				while($lignefactureinstallation=$DB_site->fetch_array($lignefactureinstallations)){
					// lignefactureinstallation
					$type=$DB_site->query_first(" SELECT libelle_type FROM garantie_type WHERE id_type='".$lignefactureinstallation['lignefactureinstallation_type']."' ");
					$xml.="<garantie id=\"".$lignefacturegarantie['garantieid']."\">\n";
						$xml.=col("prix_vente_ttc",			$lignefacturegarantie['lignefactureinstallation_differenceprix'] );
						$xml.=col("prix_achat_ht",			$lignefacturegarantie['lignefactureinstallation_paht'] );
						$xml.=col("taux_tva",				$lignefacturegarantie['lignefactureinstallation_tauxtva'] );
						$xml.=col("description",			$lignefacturegarantie['lignefactureinstallation_description'] , true );
						$xml.=col("type",					$type['installation_libelle_type'] );
					$xml.="</garantie>\n";
				}
				
				
				// SAV !!!!!!!!!!!!!!!!!!!!!!!!!!!
				$savs=$DB_site->query(" SELECT * FROM sav WHERE facturid='".$facture['facturid']."' ");
				while($sav=$DB_site->fetch_array($savs)){
					// SAV
					switch ($sav['sav_analyse']) {
						case 1:
							$analyse="Analyser avant clôture";
							break;
						case 2:
							$analyse="Ne pas analyser avant clôture";
							break;
					}
					$xml.="<sav id=\"".$sav['id']."\">\n";
						$xml.=col("administrateur",			$sav['utilisateur'] );
						$xml.=col("contenu",				$sav['sav_etat'], true );
						$xml.=col("lignes",					$sav['lignes'] );
						$xml.=col("date_creation",			mydate($sav['date']) );
						$xml.=col("date_fin",				mydate($sav['fin']) );
						$xml.=col("date_demande_cloture",	$sav['demande_cloture'] );
						$xml.=col("priorite",				$sav['sav_priorite'] );
						$xml.=col("analyse",				$analyse );
						
						
						$sav_files=$DB_site->query("SELECT * FROM projet_files WHERE projetid='".$projet['projetid']."' AND size !='0' ");
						while($sav_file=$DB_site->fetch_array($sav_files)){
							$xml.="<piece_jointe id=\"".$sav_file['id']."\">\n";
								$piecejointe="https://www.mon-espace-cuisson.com/admin/projetfiles/index.php?file=". $sav_file['id'] .".".$sav_file['ext'];
								$xml.=col("piece_jointe",			$piecejointe );
								$xml.=col("piece_jointe_size",		$sav_file['size'] );
							$xml.="</piece_jointe>\n";
						}
						
						
						// suivi de SAV
						$suivi_savs=$DB_site->query(" SELECT * FROM suivi_commande WHERE facturid='".$facture['facturid']."' AND savid='".$sav['id']."' ");
						while($suivi_sav=$DB_site->fetch_array($suivi_savs)){
							$xml.=suivi_commande($suivi_sav, "suivi_sav");
						}
						
						// mail recu pour SAV
						$mail_savs=$DB_site->query(" SELECT * FROM mail_inbox WHERE savid='".$sav['id']."' ");
						while($mail_sav=$DB_site->fetch_array($mail_savs)){
							$xml.=mail_inbox($mail_sav, "mail_inbox");
						}
						
						// mail envoyé pour SAV
						$mail_savs=$DB_site->query(" SELECT * FROM mail_outbox WHERE savid='".$sav['id']."' ");
						while($mail_sav=$DB_site->fetch_array($mail_savs)){
							$xml.=mail_outbox($mail_sav, "mail_outbox");
						}
						
					$xml.="</sav>\n";
				}
				// suivi de commande HORS SAV
				$suivi_commandes=$DB_site->query(" SELECT * FROM suivi_commande WHERE facturid='".$facture['facturid']."' AND savid='0' ");
				while($suivi_commande=$DB_site->fetch_array($suivi_commandes)){
					$xml.=suivi_commande($suivi_commande, "suivi_commande");
				}
				
				// mail recu pour commande
				$mail_commandes=$DB_site->query(" SELECT * FROM mail_inbox WHERE facturid='".$facture['facturid']."' ");
				while($mail_commande=$DB_site->fetch_array($mail_commandes)){
					$xml.=mail_inbox($mail_commande, "mail_inbox");
				}
				
				// mail envoyé pour commande
				$mail_commandes=$DB_site->query(" SELECT * FROM mail_outbox WHERE facturid='".$facture['facturid']."' ");
				while($mail_commande=$DB_site->fetch_array($mail_commandes)){
					$xml.=mail_outbox($mail_commande, "mail_outbox");
				}
			$xml.="</".$type_document.">\n";
		}
		
		// projets !
		$projets=$DB_site->query(" SELECT * FROM projet WHERE userid='".$user['userid']."' ");
		while($projet=$DB_site->fetch_array($projets)){
			$xml.="<projet id=\"".$projet['projetid']."\">\n";
				
				switch ($projet['echeance_type']) {
					case 0:
						$echeance_type="Estimé par Acilyon";
						break;
					case 1:
						$echeance_type="Info du client";
						break;
				}
				
				$etat=$DB_site->query_first(" SELECT libelle FROM projet_etat WHERE etatid='".$projet['etatid']."' ");
				$type=$DB_site->query_first(" SELECT libelle FROM projet_type WHERE projet_typeid='".$projet['projet_typeid']."' ");
				
				$xml.=col("date_creation",		mydate($projet['dateline']) );
				$xml.=col("administrateur",		$projet['commercial'] );
				$xml.=col("rappel",				mydate($projet['rappel']) );
				$xml.=col("rappel_type",		$projet['rappel_type'] );
				$xml.=col("rappel_administrateur",$projet['rappel_commercial'] );
				$xml.=col("libelle",			$projet['libelle'] , true );
				$xml.=col("besoin",				$projet['besoin'] , true );
				$xml.=col("etat",				$etat['libelle'] );
				$xml.=col("utilisateur_type",	$projet['utilisateur_type'] );
				$xml.=col("type",				$type['libelle'] );
				$xml.=col("echeance_livraison",	mydate($projet['echeance_livraison']) );
				$xml.=col("echeance_type",		$echeance_type );
				$xml.=col("delai_commande",		$projet['delai_commande'] );
				$xml.=col("numero_commande",	$projet['facturid'] );
				
				
				$projet_files=$DB_site->query("SELECT * FROM projet_files WHERE projetid='".$projet['projetid']."' AND size !='0' ");
				while($projet_file=$DB_site->fetch_array($projet_files)){
					$xml.="<piece_jointe id=\"".$projet_file['id']."\">\n";
						$piecejointe="https://www.mon-espace-cuisson.com/admin/projetfiles/index.php?file=".$projet_file['id'].".".$projet_file['ext'];
						$xml.=col("piece_jointe",			$piecejointe );
						$xml.=col("piece_jointe_size",		$projet_file['size'] );
					$xml.="</piece_jointe>\n";
				}
				
				// liste des devis rattachés
				$devis=json_decode($projet['devis'], true);
				if(count($devis)>0){
					$xml.="<devis>\n";
					foreach ($devis as $v) {
						$xml.=col("devis",	$v );
					}
					$xml.="</devis>\n";
				}
				
				// liste des contacts rattachés
				$contacts=json_decode($projet['contacts'], true);
				if(count($contacts)>0){
					$xml.="<contact>\n";
					foreach ($contacts as $v) {
						$xml.=col("contact",	$v );
					}
					$xml.="</contact>\n";
				}
				
				// liste des configs rattachés
				$configs=json_decode($projet['configs'], true);
				if(count($configs)>0){
					$xml.="<config>\n";
					foreach ($configs as $v) {
						$xml.=col("config",	$v );
					}
					$xml.="</config>\n";
				}
				
				// liste des comparateur rattachés
				$comparateur=json_decode($projet['comparateur'], true);
				if(count($comparateur)>0){
					$xml.="<comparateur>\n";
					foreach ($comparateur as $v) {
						$xml.=col("comparateur",	$v );
					}
					$xml.="</comparateur>\n";
				}
				
				// liste des articles_vus rattachés
				$articles_vus=json_decode($projet['articles_vus'], true);
				if(count($articles_vus)>0){
					$xml.="<articles_vus>\n";
					foreach ($articles_vus as $v) {
						$xml.=col("articles_vus",	$v );
					}
					$xml.="</articles_vus>\n";
				}
				
				// liste des fournisseurs rattachés
				$fournisseurs=json_decode($projet['fournisseurs'], true);
				if(count($fournisseurs)>0){
					$xml.="<fournisseurs>\n";
					foreach ($fournisseurs as $v) {
						$xml.=col("fournisseurs",	$v );
					}
					$xml.="</fournisseurs>\n";
				}
				
				// Liste des relations
				$projet_relations=$DB_site->query(" SELECT * FROM projet_relation WHERE projetid='".$projet['projetid']."' ");
				while($projet_relation=$DB_site->fetch_array($projet_relations)){
					// projet_relation
					$xml.="<user_relations>\n";
						$xml.=col("user_id",			$projet_relation['userid'] );
					$xml.="</user_relations>\n";
				}
				// suivi de projet
				$suivi_projets=$DB_site->query(" SELECT * FROM suivi_client WHERE userid='".$user['userid']."' AND projetid='".$projet['projetid']."' ");
				while($suivi_projet=$DB_site->fetch_array($suivi_projets)){
					$xml.=suivi_client($suivi_projet, "suivi_projet");
				}
				
				// mail recu pour projet
				$mail_projets=$DB_site->query(" SELECT * FROM mail_inbox WHERE projetid='".$projet['projetid']."' ");
				while($mail_projet=$DB_site->fetch_array($mail_projets)){
					$xml.=mail_inbox($mail_projet, "mail_inbox");
				}
				
				// mail envoyés pour projet
				$mail_projets=$DB_site->query(" SELECT * FROM mail_outbox WHERE projetid='".$projet['projetid']."' ");
				while($mail_projet=$DB_site->fetch_array($mail_projets)){
					$xml.=mail_outbox($mail_projet, "mail_outbox");
				}
				
				
			$xml.="</projet>\n";
		}
		// suivi client
		$suivi_clients=$DB_site->query(" SELECT * FROM suivi_client WHERE userid='".$user['userid']."' AND projetid='0' ");
		while($suivi_client=$DB_site->fetch_array($suivi_clients)){
			$xml.=suivi_client($suivi_client, "suivi_client");
		}
		
		// mail recu pour client
		$mail_clients=$DB_site->query(" SELECT * FROM mail_inbox WHERE userid='".$user['userid']."' AND projetid='0' AND savid='0' AND facturid='0' ");
		while($mail_client=$DB_site->fetch_array($mail_clients)){
			$xml.=mail_inbox($mail_client, "mail_inbox");
		}
		
		// mail envoyé pour client
		$mail_clients=$DB_site->query(" SELECT * FROM mail_outbox WHERE userid='".$user['userid']."' AND projetid='0' AND savid='0' AND facturid='0' ");
		while($mail_client=$DB_site->fetch_array($mail_clients)){
			$xml.=mail_outbox($mail_client, "mail_outbox");
		}
		
		
		$user_files=$DB_site->query("SELECT * FROM user_files WHERE userid='".$user['userid']."' AND size !='0' AND id NOT IN (".implode(",",$pj_user).") ");
		while($user_file=$DB_site->fetch_array($user_files)){
			$xml.="<piece_jointe id=\"".$user_file['id']."\">\n";
				$piecejointe="https://www.mon-espace-cuisson.com/admin/userfiles/index.php?file=".$user_file['id'].".".$user_file['ext'];
				$xml.=col("piece_jointe",			$piecejointe );
				$xml.=col("piece_jointe_size",		$user_file['size'] );
			$xml.="</piece_jointe>\n";
		}
	$xml.="</user>";
}
$xml.="</users>";



///////////////////////////////////////////////////////////////////////////////////
// 1 => articles
///////////////////////////////////////////////////////////////////////////////////

$xml.="<catalogue>";

$categ_array=array();
$cat1s=$DB_site->query("SELECT catid FROM categorie WHERE 1=0 AND parentid='0' ORDER BY position ASC ");
while($cat1=$DB_site->fetch_array($cat1s)){
	$categ_array[]=$cat1['catid'];
	$cat2s=$DB_site->query("SELECT catid FROM categorie WHERE parentid='".$cat1['catid']."' ORDER BY position ASC ");
	while($cat2=$DB_site->fetch_array($cat2s)){
		$categ_array[]=$cat2['catid'];
		$cat3s=$DB_site->query("SELECT catid FROM categorie WHERE parentid='".$cat2['catid']."' ORDER BY position ASC ");
		while($cat3=$DB_site->fetch_array($cat3s)){
			$categ_array[]=$cat3['catid'];
			$cat4s=$DB_site->query("SELECT catid FROM categorie WHERE parentid='".$cat3['catid']."' ORDER BY position ASC ");
			while($cat4=$DB_site->fetch_array($cat4s)){
				$categ_array[]=$cat4['catid'];
			}
		}
	}
}

$i_categ=0;
foreach ($categ_array as $cat => $catid) {
	$i_categ++;
	if($i_categ>10){  break; }
	$categorie=$DB_site->query_first("SELECT * FROM categorie WHERE catid='".$catid."'  ");
	$xml.="<categorie id=\"".$categorie['catid']."\">";
		$xml.=col("parentid",				$categorie['parentid'] );
		$xml.=col("libelleFR",				$categorie['libelleFR'] );
		$xml.=col("libelleEN",				$categorie['libelleEN'] );
		$xml.=col("descriptionFR",			html_entity_decode($categorie['descriptionFR']) , true );
		$xml.=col("descriptionEN",			html_entity_decode($categorie['descriptionEN']) , true );
		$xml.=col("image",					"https://www.mon-espace-cuisson.com/categorie/".$categorie['catid']."_320_192.".$categorie['imageFR'] );
		$xml.=col("urlFR",					"https://www.mon-espace-cuisson.com/".url_categ($catid, "FR") );
		$xml.=col("urlEN",					"https://www.mon-espace-cuisson.com/".url_categ($catid, "EN") );
		$xml.=col("position",				$categorie['position'] );
		$xml.=col("menu",					$categorie['menu_appartenance'] );
		$xml.=col("filtres",				$categorie['filtres'] );
		$xml.=col("affichage_menu",			$categorie['affichage_menu'] );
		$articles=$DB_site->query(" SELECT * FROM article WHERE catid='".$categorie['catid']."' and active='1' ");
		while($article=$DB_site->fetch_array($articles)){
			$type_article=$DB_site->query_first(" SELECT libelle FROM typearticle WHERE typearticleid='".$article['typearticleid']."' ");
			$xml.="<article id=\"".$article['artid']."\">\n";
				$xml.=col("ref_interne",			$article['artcode'] );
				$xml.=col("libelleFR",				$article['libelleFR'] , true );
				$xml.=col("libelleEN",				$article['libelleEN'] , true );
				$xml.=col("descriptionFR",			html_entity_decode($article['descriptionFR']) , true);
				$xml.=col("descriptionEN",			html_entity_decode($article['descriptionEN']) , true);
				$xml.=col("urlFR",					"https://www.mon-espace-cuisson.com/".url_article($article['artid'], "FR"));
				$xml.=col("urlEN",					"https://www.mon-espace-cuisson.com/".url_article($article['artid'], "EN"));
				$xml.=col("etat",					$article_actif[$article['active']] );
				$xml.=col("commandable",			ouinon($article['commandable']) );
				// $xml.=col("type_article",			$type_article['libelle'] );
				$xml.="<type_article id=\"".$article['typearticleid']."\">\n".$type_article['libelle']."\n<type_article>\n";
				$xml.=col("prix_vente_ttc",			$article['prix'] );
				$xml.=col("prix_public_ht",			$article['prixconseilleht'] );
				$xml.=col("prix_vente_ht",			$article['prixht'] );
				$xml.=col("taux_tva",				$article['tva'] );
				$article_fournisseurs=$DB_site->query(" SELECT * FROM article_fournisseur WHERE artid='".$article['artid']."' AND principal='base' ");
				while($article_fournisseur=$DB_site->fetch_array($article_fournisseurs)){
					$xml.="<achat_fournisseur>\n";
						$xml.=col("fournisseur",			$article_fournisseur['fournisseur'] );
						if($article_fournisseur['principal']=="base"){
							$xml.=col("par_default",			"oui" );
						} else {
							$xml.=col("par_default",			"non" );
						}
						$xml.=col("prix_achat_ht",			$article_fournisseur['prix'] );
						$xml.=col("prix_transport",			$article_fournisseur['trspt'] );
						$xml.=col("ecopart_ht",				$article['ecopartht'] );
						$xml.=col("ecopart_ttc",			$article['ecopart'] );
						$xml.=col("delai_expedition",		$article['delai'] );
						$xml.=col("reappro",				$article['reappro'] );
					$xml.="</achat_fournisseur>\n";
				}
				
				$xml.=col("prix_achat_ht",			$article['paht'] );
				$xml.=col("ecopart_ttc",			$article['ecopart'] );
				$xml.=col("ecopart_ht",				$article['ecopartht'] );
				$xml.=col("prix_transport",			$article['prixtrspt'] );
				$xml.=col("delai_expedition",		$article['delailivraison'] );
				$xml.=col("ref_fabricant",			$article['artcode_eurocave'] );
				$xml.=col("autres_ref_fabricant",	str_replace("\n","|",$article['refs_fabricant']) );
				
				
				$xml.=col("image_url",				"https://www.mon-espace-cuisson.com/images/".$article['artid']."_800_600.".$article['image'] );
				$xml.=col("hauteur",				$article['hauteur'] );
				$xml.=col("largeur",				$article['largeur'] );
				$xml.=col("profondeur",				$article['profondeur'] );
				
				$xml.=col("emballage_hauteur1",		$article['emballage_hauteur1'] );
				$xml.=col("emballage_largeur1",		$article['emballage_largeur1'] );
				$xml.=col("emballage_profondeur1",	$article['emballage_profondeur1'] );
				$xml.=col("emballage_poids1",		$article['emballage_poids1'] );
				
				$xml.=col("emballage_hauteur2",		$article['emballage_hauteur2'] );
				$xml.=col("emballage_largeur2",		$article['emballage_largeur2'] );
				$xml.=col("emballage_profondeur2",	$article['emballage_profondeur2'] );
				$xml.=col("emballage_poids2",		$article['emballage_poids2'] );
				
				$xml.=col("emballage_hauteur3",		$article['emballage_hauteur3'] );
				$xml.=col("emballage_largeur3",		$article['emballage_largeur3'] );
				$xml.=col("emballage_profondeur3",	$article['emballage_profondeur3'] );
				$xml.=col("emballage_poids3",		$article['emballage_poids3'] );
				
				$xml.=col("pays_fabrication",		$article['paysfabrication'] );// décrire
				$xml.=col("paysid",					$article['paysid'] );// décrire
				$xml.=col("code_douanier",			$article['code_douanier'] );// décrire
				
				$xml.=col("code_youtube",			$article['video_codeFR'] );// décrire
				$xml.=col("gammeFR",				$article['gammeFR'] );// décrire
				$xml.=col("gammeEN",				$article['gammeEN'] );// décrire
				
				$articlephotos=$DB_site->query(" SELECT * FROM articlephoto WHERE artid='".$article['artid']."' ORDER BY position ASC ");
				while($articlephoto=$DB_site->fetch_array($articlephotos)){
					$xml.="<photo id=\"".$articlephoto['articlephotoid']."\">\n";
						$xml.=col("image_url",				"https://www.mon-espace-cuisson.com/images/".$article['artid']."_".$articlephoto['articlephotoid']."_800_600.".$article['image'] );
						$xml.=col("position",				$articlephoto['position'] );
						$xml.=col("visible",				$articlephoto['visible'] );
						$xml.=col("ambiance",				$articlephoto['ambiance'] );
						$xml.=col("diff_prix_achat_ht",		$articlephoto['paht'] );
					$xml.="</photo>\n";
				}
				
				
				// marque
				$marque=$DB_site->query_first(" SELECT * FROM article_marque
												INNER JOIN marque USING(marqueid)
												INNER JOIN marque_description USING(marqueid)
												WHERE artid='".$article['artid']."'  ");
				$xml.="<marque id=\"".$marque['marqueid']."\">\n";
					$xml.=col("libelle",				$marque['libelle'] );
					$xml.=col("photo_url",				"https://www.mon-espace-cuisson.com/configurations/www.mon-espace-cuisson.com/images/marques/".$marque['marqueid'].".".$marque['image'] );
					$xml.=col("urlFR",					"https://www.mon-espace-cuisson.com/".$marque['urlFR']."-".$marque['marqueid'].".htm" );
					$xml.=col("position",				$marque['position'] );
					$xml.=col("descriptionFR",			html_entity_decode($marque['descriptionFR']) , true);
					$xml.=col("meta_descriptionFR",		$marque['ref_descriptionFR'] , true);
					$xml.=col("meta_titleFR",			$marque['ref_titleFR'] , true);
					$xml.=col("libelle_footerFR",		$marque['libelleFooterFR'] , true);
				$xml.="</marque>\n";
				
				$caracteristiques=$DB_site->query(" 	SELECT DISTINCT(caractid), caracteristique.* FROM article_caractval 
														INNER JOIN caracteristiquevaleur USING(caractvalid)
														INNER JOIN caracteristique USING(caractid)
														WHERE artid='".$article['artid']."'
														ORDER BY caracteristique.position ASC, caracteristiquevaleur.position ASC	");
				while($caracteristique=$DB_site->fetch_array($caracteristiques)){
					$xml.="<caracteristique id=\"".$caracteristique['caractid']."\">\n";
						$xml.=col("libelleFR",				$caracteristique['libelleFR'] );
						$xml.=col("position",				$caracteristique['position'] );
						$xml.=col("description_interne",	$articlephoto['descriptioninterne'] );
						$caracteristiquevaleurs=$DB_site->query(" 	SELECT caracteristiquevaleur.*, article_caractval.* FROM article_caractval 
														INNER JOIN caracteristiquevaleur USING(caractvalid)
														INNER JOIN caracteristique USING(caractid)
														WHERE artid='".$article['artid']."' AND caractid='".$caracteristique['caractid']."'
														ORDER BY caracteristique.position ASC, caracteristiquevaleur.position ASC	");
						while($caracteristiquevaleur=$DB_site->fetch_array($caracteristiquevaleurs)){
							$xml.="<caracteristique_valeur id=\"".$caracteristiquevaleur['caractvalid']."\">\n";
								$xml.=col("libelleFR",				$caracteristiquevaleur['libelleFR'] );
								$xml.=col("position",				$caracteristiquevaleur['position'] );
								$xml.=col("prix_vente_ttc",			$caracteristiquevaleur['differenceprix'] );
								$xml.=col("prix_vente_ht",			$caracteristiquevaleur['differenceprixht'] );
								$xml.=col("prix_achat_ht",				$caracteristiquevaleur['achat'] );
								
								if($caracteristiquevaleur['articlephotoid']>0){
									$xml.=col("photo_url",				"https://www.mon-espace-cuisson.com/configurations/www.mon-espace-cuisson.com/images/produits/".$article['artid']."_".$caracteristiquevaleur['articlephotoid'].".jpg" );
								} else {
									if(file_exists($rootpath."/configurations/www.mon-espace-cuisson.com/images/caracteristiquevaleur/zoom/".$caracteristiquevaleur['caractvalid'].".jpg")){
										$xml.=col("photo_url",				"https://www.mon-espace-cuisson.com/configurations/www.mon-espace-cuisson.com/images/caracteristiquevaleur/zoom/".$caracteristiquevaleur['caractvalid'].".jpg" );
									}
								}
							$xml.="</caracteristique_valeur>\n";
						}
					$xml.="</caracteristique>\n";
				}
				
				
				// tous les différents cross selling
				$cross_sellings=$DB_site->query(" SELECT * FROM cross_selling WHERE artid='".$article['artid']."' ");
				while($cross_selling=$DB_site->fetch_array($cross_sellings)){
					$xml.="<cross_selling>\n";
						$xml.=col("type",				"assortiment" );
						$xml.=col("article_id",				$cross_selling['artid_cross'] );
					$xml.="</cross_selling>\n";
				}
				
				$cross_sellings=$DB_site->query(" SELECT * FROM cross_selling2 WHERE artid='".$article['artid']."' ");
				while($cross_selling=$DB_site->fetch_array($cross_sellings)){
					$xml.="<cross_selling>\n";
						$xml.=col("type",				"assortiment complémentaire" );
						$xml.=col("article_id",				$cross_selling['artid_cross'] );
					$xml.="</cross_selling>\n";
				}
				
				$cross_sellings=$DB_site->query(" SELECT * FROM article_rangement WHERE artid='".$article['artid']."' ");
				while($cross_selling=$DB_site->fetch_array($cross_sellings)){
					$xml.="<cross_selling>\n";
						$xml.=col("type",				"accessoire conseillé" );
						$xml.=col("article_id",				$cross_selling['artid_rangement'] );
					$xml.="</cross_selling>\n";
				}
				
				$similaire=$DB_site->query_first(" SELECT gid FROM article_similaire WHERE artid='".$article['artid']."' ");
				
				if($similaire['gid']>0){
					$cross_sellings=$DB_site->query(" SELECT distinct(artid) FROM article_similaire WHERE gid='".$similaire['gid']."' AND artid!='".$article['artid']."'	");
					while($cross_selling=$DB_site->fetch_array($cross_sellings)){
						$xml.="<cross_selling>\n";
							$xml.=col("type",					"même gamme" );
							$xml.=col("article_id",				$cross_selling['artid'] );
						$xml.="</cross_selling>\n";
					}
				}
				
				$categorie_virtuelles=$DB_site->query(" SELECT * FROM vcategorie WHERE artid='".$article['artid']."' ");
				while($categorie_virtuelle=$DB_site->fetch_array($categorie_virtuelles)){
					$xml.="<categorie_virtuelle>\n";
						$xml.=col("categorie_id",				$categorie_virtuelle['catid'] );
					$xml.="</categorie_virtuelle>\n";
				}
				
				// nos conseils
				$carticle_nosconseils=$DB_site->query(" SELECT * FROM article_nosconseils WHERE artid='".$article['artid']."' ");
				while($carticle_nosconseil=$DB_site->fetch_array($carticle_nosconseils)){
					$xml.="<nos_conseils>\n";
						$xml.=col("type",				$carticle_nosconseil['type'] );
						$traduction=$DB_site->query_first(" SELECT * FROM traduction WHERE path='".$carticle_nosconseil['path']."' ");
						if($traduction['FR']!=""){
							$xml.=col("conseilFR",		$traduction['FR'] , true);
						}
						if($traduction['EN']!=""){
							$xml.=col("conseilEN",		$traduction['EN'] , true);
						}
					$xml.="</nos_conseils>\n";
				}
				
				// icones
				$article_icones=$DB_site->query(" SELECT * FROM article_icon INNER JOIN icons ON(article_icon.iconid=icons.id) WHERE artid='".$article['artid']."' AND active='1' ");
				while($article_icone=$DB_site->fetch_array($article_icones)){
					$xml.="<icones>\n";
						$xml.=col("libelle",		$article_icone['libelle'] );
						$xml.=col("icone_urlFR",	"https://www.mon-espace-cuisson.com/images/icons/FR/".$article_icone['iconid'].".png" );
						$xml.=col("date_debut",		mydate($article_icone['du']) );
						$xml.=col("date_fin",		mydate($article_icone['au']) );
					$xml.="</icones>\n";
				}
				
				foreach ($metas as $key => $table) {
					$meta_valeurs=$DB_site->query(" SELECT * FROM ".$table." WHERE artid='".$article['artid']."' ");
					while($meta_valeur=$DB_site->fetch_array($meta_valeurs)){
						if($meta_valeur['artid']==$article['artid']){
							foreach ($meta_valeur as $meta_key => $meta_val) {
								if(!is_numeric($meta_key) && $meta_val!=""){
									$traduction=$DB_site->query_first(" SELECT * FROM traduction WHERE path='".$meta_val."' ");
									if($traduction['FR']!=""){
										$xml.=col(substr($table,8)."_".$meta_key."FR",		$traduction['FR'] , true);
									}
									if($traduction['EN']!=""){
										$xml.=col(substr($table,8)."_".$meta_key."EN",		$traduction['EN'] , true);
									}
								}
							}
							
						}
					}
				}
				
			$xml.="</article>\n";
		}
	$xml.="</categorie>";
}
$xml.="</catalogue>";

$xml.="<garanties>";
$garanties=$DB_site->query(" SELECT * FROM garantie WHERE 1=0 ORDER BY garantieid ASC ");
while($garantie=$DB_site->fetch_array($garanties)){
	$type=$DB_site->query_first(" SELECT libelle_type FROM garantie_type WHERE id_type='".$garantie['type_garantie']."' ");
	$xml.="<garantie id=\"".$garantie['garantieid']."\">\n";
		$xml.=col("type",					$type['libelle_type'] );
		$xml.=col("fournisseur",			$garantie['fournisseur'] );
		$xml.=col("duree",					$garantie['duree']." ans" );
		if($garantie['type_garantie']==1){
			$xml.=col("type_contrat",		"inclus fabriquant" );
		} else {
			$xml.=col("type_contrat",		"exclus fabriquant" );
		}
		$xml.=col("description",			$garantie['comm'] );
		if($garantie['garantie_client_id']==1){
			$xml.=col("type_user",			"particuliers" );
		} else {
			$xml.=col("type_user",			"professionnels" );
		}
		if($garantie['start']==1){
			$xml.=col("commencement",		"a l'expédition" );
		} else {
			$xml.=col("commencement",		"extension de garantie" );
		}
		$xml.="<marques>\n";
			$marqueids=explode(",",$garantie['marques']);
			foreach ($marqueids as $key => $marqueid) {
				$marque=$DB_site->query_first("SELECT libelle FROM marque WHERE marqueid='".$marqueid."' ");
				$xml.="<marque id=\"".$marqueid."\">".$marque['libelle']."</marque>\n";
			}
		$xml.="</marques>\n";
		$xml.="<type_articles>\n";
			$typeids=explode(",",$garantie['types_produits']);
			foreach ($typeids as $key => $typeid) {
				$type_article=$DB_site->query_first("SELECT libelle FROM typearticle WHERE typearticleid='".$typeid."' ");
				$xml.="<type_article id=\"".$typeid."\">".$type_article['libelle']."</type_article>\n";
			}
		$xml.="</type_articles>\n";
		$xml.="<pays_garanties>\n";
			$paysids=explode(",",$garantie['pays']);
			foreach ($paysids as $key => $paysid) {
				$pays=$DB_site->query_first(" SELECT libelle FROM pays WHERE paysid='".$paysid."' ");
				$xml.="<pays_garantie id=\"".$paysid."\">".$pays['libelle']."</pays_garantie>\n";
			}
		$xml.="</pays_garanties>\n";
		$xml.="<tranches>\n";
			$tranches=$DB_site->query(" SELECT * FROM garantie_tranche WHERE garantieid='".$garantie['garantieid']."' ORDER BY garantie_tranche_prix_mini ASC ");
			while($tranche=$DB_site->fetch_array($tranches)){
				$xml.="<tranche>\n";
					$json_prix=unserialize($tranche['garantie_tranche_prix']);
					$json_ppi=unserialize($tranche['garantie_tranche_ppi']);
					$xml.=col("article_prix_vente_TTC_debut",		$tranche['garantie_tranche_prix_mini'] );
					$xml.=col("article_prix_vente_TTC_fin",			$tranche['garantie_tranche_prix_maxi'] );
					$xml.=col("garantie_prix_achat_ht",				$tranche['garantie_tranche_paht'] );
					$xml.=col("garantie_prix_vente_ttc",			$json_prix['base'] );
					$xml.=col("garantie_prix_public_ttc",			$json_ppi['base'] );
				$xml.="</tranche>\n";
			}
		$xml.="</tranches>\n";
	$xml.="</garantie>\n";
}
$xml.="</garanties>";






/*
$xml.="<stocks>";

// ============> qté réservée : SELECT * FROM `stock_item` WHERE `state` = 2 AND `enlevement_fait` = 0 AND dest !='' ORDER BY `stock_item`.`artid` ASC;
$stocks=$DB_site->query(" SELECT * FROM `stock_item` WHERE `state` = 2 AND `enlevement_fait` = 0 AND dest !='' ORDER BY `stock_item`.`artid` ASC ");
while($stock=$DB_site->fetch_array($stocks)){
	$fournisseur=$DB_site->query_first(" SELECT * FROM fournisseur WHERE id='".$stock['lieu']."' ");
	$xml.="<stock>\n";
		$xml.=col("article_id",			$stock['artid'] );
		$xml.=col("etat",				"réservé" );
		$xml.=col("prix_achat_ht",		$stock['paht'] );
		$xml.=col("ecopart_ht",			$stock['ecopartht'] );
		$xml.=col("lieu_id",			$stock['lieu'] );
		$xml.=col("lieu_nom",			$fournisseur['societe'] );
		$xml.=col("date_entre",			mydate($stock['date']) );
		$xml.=col("origine",			$stock['origin'] );
		$xml.=col("origine_cmd",		$stock['stock_cmd_id'] );
		$xml.=col("origine_detail",		$stock['infos'] );
	$xml.="</stock>\n";
}

// ============> qté réelle instantanné : SELECT * FROM `stock_item` WHERE `state` = 1 ORDER BY `stock_item`.`artid` ASC;
$stocks=$DB_site->query(" SELECT * FROM `stock_item` WHERE `state` = 1 ORDER BY `stock_item`.`artid` ASC ");
while($stock=$DB_site->fetch_array($stocks)){
	$xml.="<stock>\n";
		$xml.=col("article_id",			$stock['artid'] );
		$xml.=col("etat",				"stocké" );
		$xml.=col("prix_achat_ht",		$stock['paht'] );
		$xml.=col("ecopart_ht",			$stock['ecopartht'] );
		$xml.=col("lieu_id",				$stock['lieu'] );
		$xml.=col("lieu_nom",			$fournisseur['societe'] );
		$xml.=col("date_entre",			mydate($stock['date']) );
		$xml.=col("origine",			$stock['origin'] );
		$xml.=col("origine_cmd",		$stock['stock_cmd_id'] );
		$xml.=col("origine_detail",		$stock['infos'] );
	$xml.="</stock>\n";
}
$xml.="</stocks>";
*/





$xml.="<fournisseurs>";
// suivi_fournisseur
// fournisseur_files
// 

$fournisseurs=$DB_site->query("SELECT DISTINCT(societe) FROM fournisseur ");
while($fournisseur=$DB_site->fetch_array($fournisseurs)){
	$xml.="<fournisseur>\n";
		$fournisseur_condition=$DB_site->query_first("SELECT * FROM fournisseur_condition WHERE societe='".addslashes($fournisseur['societe'])."' ");
		$xml.=col("id",						$fournisseur_condition['id'] );
		$xml.=col("raison_sociale",			$fournisseur['societe'] );
		$xml.=col("type",					$fournisseur_condition['types'] );
		$xml.=col("compte_client",			$fournisseur_condition['compte_client'] );
		$xml.=col("devise",					$fournisseur_condition['devise'] );
		$xml.=col("langue",					$fournisseur_condition['langue'] );
		
		$xml.=col("tarif",					strip_tags($fournisseur_condition['tarif']) , true );
		$xml.=col("commande",				strip_tags($fournisseur_condition['commande']) , true );
		$xml.=col("livraison",				strip_tags($fournisseur_condition['livraison']) , true );
		$xml.=col("sav",					strip_tags($fournisseur_condition['sav']) , true );
		$xml.=col("divers",					strip_tags($fournisseur_condition['divers']) , true );
		$xml.=col("export",					strip_tags($fournisseur_condition['export']) , true );
		$xml.=col("codes",					strip_tags("caché pour des raisons de sécurité") , true ); // $fournisseur_condition['codes']
		
		$xml.=col("activite",				$fournisseur_condition['activite'] );
		$xml.=col("pdts_vendus",			$fournisseur_condition['pdts_vendus'] );
		$xml.=col("accuse_de_reception",	$fournisseur_condition['accuse_de_reception'] );
		$xml.=col("tvaintra",				$fournisseur_condition['tvaintra'] );
		$xml.=col("fourn_admin",			$fournisseur_condition['fourn_admin'] );
		$xml.=col("fourn_note",				$fournisseur_condition['fourn_note'] );
		$xml.=col("tracking",				$fournisseur_condition['tracking'] );
		$xml.=col("societe_facturation_id",	$fournisseur_condition['societe_facturation_id'] );
		
		$fournisseur_contacts=$DB_site->query("SELECT * FROM fournisseur WHERE societe='".addslashes($fournisseur['societe'])."' ");
		while($fournisseur_contact=$DB_site->fetch_array($fournisseur_contacts)){
			$pays=$DB_site->query_first(" SELECT libelle FROM pays WHERE paysid='".$fournisseur_contact['paysid']."' ");
			$xml.="<contact id=\"".$fournisseur_contact['id']."\">\n";
				$xml.=col("type",			$fournisseur_contact['type'] );
				$xml.=col("nom",			$fournisseur_contact['nom'] );
				$xml.=col("prenom",			$fournisseur_contact['prenom'] );
				$xml.=col("fonction",		$fournisseur_contact['fonction'] );
				$xml.=col("mail",			$fournisseur_contact['mail'] );
				$xml.=col("groupes",		$fournisseur_contact['groupes'] );
				$xml.=col("telephone",		$fournisseur_contact['telephone'] );
				$xml.=col("portable",		$fournisseur_contact['portable'] );
				$xml.=col("fax",			$fournisseur_contact['fax'] );
				$xml.=col("adresse1",		$fournisseur_contact['adresse1'] );
				$xml.=col("adresse2",		$fournisseur_contact['adresse2'] );
				$xml.=col("cp",				$fournisseur_contact['cp'] );
				$xml.=col("ville",			$fournisseur_contact['ville'] );
				$xml.=col("pays",			$pays['libelle'] );
				$xml.=col("horaires",		$fournisseur_contact['horaires'] );
				$xml.=col("code",			$fournisseur_contact['code'] );
				$xml.=col("suivi",			$fournisseur_contact['suivi'] );
				$xml.=col("suivih",			$fournisseur_contact['suivih'] );
			$xml.="</contact>\n";
		}
		$fournisseur_files=$DB_site->query("SELECT * FROM fournisseur_files WHERE fournisseur='".addslashes($fournisseur['societe'])."' AND size>0 ");
		while($fournisseur_file=$DB_site->fetch_array($fournisseur_files)){
			$xml.="<fournisseur_file id=\"".$fournisseur_file['id']."\">\n";
				$xml.=col("name",			$fournisseur_file['name'] );
				$xml.=col("date",			mydate($fournisseur_file['date']) );
				$xml.=col("url",			"URL_DU_FICHIER/".$fournisseur_file['id'].".".$fournisseur_file['ext'] );
				$xml.=col("size",			$fournisseur_file['size'] );
			$xml.="</fournisseur_file>\n";
		}
	$xml.="</fournisseur>\n";
}
$xml.="</fournisseurs>";

$xml.="</xml>";
 header("Content-type: text/xml");
 echo $xml;
?>