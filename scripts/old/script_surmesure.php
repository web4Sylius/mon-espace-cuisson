<?php
set_time_limit(120) ; 

include "/home/arobases/www/config.php";
include_once "/home/arobases/www/mysql.php";
$DB_site=new DB_Sql;
$DB_site->appname='arobases';
$DB_site->appshortname='arobases';
$DB_site->database=$dbname;
$DB_site->server=$servername;
$DB_site->user=$dbusername;
$DB_site->password=$dbpassword;
$DB_site->connect();
$dbpassword="";
$DB_site->password="";

$domaine = "surmesure.vendre-en-ligne.fr" ;

if (isset($domaine))
	{
	$passession = "oui" ;
	include "/home/boutique/www/configurations/$domaine/config.php";
	$DB_site2=new DB_Sql;
	$DB_site2->appname='boutique';
	$DB_site2->appshortname='boutique';
	$DB_site2->database=$DBname;
	$DB_site2->server=$db_server;
	$DB_site2->user=$DBusername;
	$DB_site2->password=$DBpassword;
	$DB_site2->connect();
	$DBpassword="";
	


	/* VIDAGE */
	
		
		$DB_site2->query("truncate table affilie");
		$DB_site2->query("truncate table affilie_honoraires");
		$DB_site2->query("truncate table affilie_lot");
		$DB_site2->query("truncate table articlephoto");
		$DB_site2->query("truncate table articlevote");
		$DB_site2->query("truncate table article");
		$DB_site2->query("truncate table article_cadeau");
		$DB_site2->query("truncate table article_caractval");
		$DB_site2->query("truncate table article_champ");
		$DB_site2->query("truncate table article_champ_valeur");
		$DB_site2->query("truncate table article_marque");
		$DB_site2->query("truncate table article_piece");
		$DB_site2->query("truncate table cadeau");
		$DB_site2->query("truncate table caracteristique");
		$DB_site2->query("truncate table caracteristiquevaleur");
		$DB_site2->query("truncate table categorie");
		$DB_site2->query("truncate table categorie_cadeau");
		$DB_site2->query("truncate table categorie_caracteristique");
		$DB_site2->query("truncate table coupdecoeur");
		$DB_site2->query("truncate table document");
		$DB_site2->query("truncate table emails_clients");
		$DB_site2->query("truncate table facture");
		$DB_site2->query("truncate table faq");
		$DB_site2->query("truncate table formulaire");
		$DB_site2->query("truncate table formulaire_champ");
		$DB_site2->query("truncate table formulaire_champ_valeur");
		$DB_site2->query("truncate table fraisgestion");
		$DB_site2->query("truncate table fraisport");
		$DB_site2->query("truncate table groupe_utilisateur");
		$DB_site2->query("truncate table historique_recherches");
		$DB_site2->query("truncate table institutionnel");
		$DB_site2->query("truncate table liens");
		$DB_site2->query("truncate table liens_articles");
		$DB_site2->query("truncate table lignefacture");
		$DB_site2->query("truncate table lignefacturecaracteristique");
		$DB_site2->query("truncate table lignefacturechamp");
		$DB_site2->query("truncate table lignepanier");
		$DB_site2->query("truncate table lignepaniercaracteristique");
		$DB_site2->query("truncate table lignepanierchamp");
		$DB_site2->query("truncate table mailing");
		$DB_site2->query("truncate table mails_newsletter");
		$DB_site2->query("truncate table marque");
		$DB_site2->query("truncate table news");
		$DB_site2->query("truncate table newsletter");
		$DB_site2->query("truncate table panier");
		$DB_site2->query("truncate table partenaire");
		$DB_site2->query("truncate table promotion");
		$DB_site2->query("truncate table recherche_prix");
		$DB_site2->query("truncate table remise");
		$DB_site2->query("truncate table remisearticle");
		$DB_site2->query("truncate table remisecategorie");
		$DB_site2->query("truncate table sms");
		$DB_site2->query("truncate table stock");
		$DB_site2->query("truncate table stock_caract");
		$DB_site2->query("truncate table stock_caractval");
		$DB_site2->query("truncate table support");
		$DB_site2->query("truncate table tauxtva");
		$DB_site2->query("truncate table temp_produits_internautes");
		$DB_site2->query("truncate table temp_top_ventes");
		$DB_site2->query("truncate table treeview_temporaire");
		$DB_site2->query("truncate table uploads");
		$DB_site2->query("truncate table utilisateur");
		$DB_site2->query("truncate table wysiwyg");


	
		// articles
		$folder = "/home/boutique/www/configurations/$domaine/images/produits";
		$folder_article = "/home/boutique/www/configurations/$domaine/images/produits/article";
		$folder_br = "/home/boutique/www/configurations/$domaine/images/produits/br";
		$folder_categorie = "/home/boutique/www/configurations/$domaine/images/produits/categorie";
		$folder_cdc_categ = "/home/boutique/www/configurations/$domaine/images/produits/cdc_categ";
		$folder_cdc_index = "/home/boutique/www/configurations/$domaine/images/produits/cdc_index";
		$folder_nouveautes_categ = "/home/boutique/www/configurations/$domaine/images/produits/nouveautes_categ";
		$folder_nouveautes_index = "/home/boutique/www/configurations/$domaine/images/produits/nouveautes_index";
		$folder_promos_categ = "/home/boutique/www/configurations/$domaine/images/produits/promos_categ";
		$folder_promos_index = "/home/boutique/www/configurations/$domaine/images/produits/promos_index";
		$folder_topventes_categ = "/home/boutique/www/configurations/$domaine/images/produits/topventes_categ";
		$folder_topventes_index = "/home/boutique/www/configurations/$domaine/images/produits/topventes_index";
		$dossier = opendir($folder);
		while ($fichier = readdir($dossier))
			{
			if ($fichier != "." && $fichier != ".." && $fichier != "br" && $fichier != "index.htm" && $fichier != "index.php"
			 && $fichier != "article" && $fichier != "categorie" && $fichier != "cdc_categ" && $fichier != "cdc_index" 
			 && $fichier != "nouveautes_categ" && $fichier != "nouveautes_index" && $fichier != "topventes_categ" 
			 && $fichier != "topventes_index" && $fichier != "promos_categ" && $fichier != "promos_index")	
				{
				unlink($folder."/".$fichier);
				if (file_exists($folder_article."/".$fichier)) // article
					{
					unlink($folder_article."/".$fichier);
					}
				if (file_exists($folder_br."/".$fichier)) // br
					{
					unlink($folder_br."/".$fichier);
					}
				if (file_exists($folder_categorie."/".$fichier)) // categorie
					{
					unlink($folder_categorie."/".$fichier);
					}
				if (file_exists($folder_cdc_categ."/".$fichier)) // cdc_categ
					{
					unlink($folder_cdc_categ."/".$fichier);
					}
				if (file_exists($folder_cdc_index."/".$fichier)) // cdc_index
					{
					unlink($folder_cdc_index."/".$fichier);
					}
				if (file_exists($folder_nouveautes_categ."/".$fichier)) // nouveautes_categ
					{
					unlink($folder_nouveautes_categ."/".$fichier);
					}
				if (file_exists($folder_nouveautes_index."/".$fichier)) // nouveautes_index
					{
					unlink($folder_nouveautes_index."/".$fichier);
					}
				if (file_exists($folder_promos_categ."/".$fichier)) // promos_categ
					{
					unlink($folder_promos_categ."/".$fichier);
					}
				if (file_exists($folder_promos_index."/".$fichier)) // promos_index
					{
					unlink($folder_promos_index."/".$fichier);
					}
				if (file_exists($folder_topventes_categ."/".$fichier)) // topventes_categ
					{
					unlink($folder_topventes_categ."/".$fichier);
					}
				if (file_exists($folder_topventes_index."/".$fichier)) // topventes_index
					{
					unlink($folder_topventes_index."/".$fichier);
					}
				}
			}
	
		// catégories
			// FR
			$folder = "/home/boutique/www/configurations/$domaine/images/categories/FR";
			$folder_br = "/home/boutique/www/configurations/$domaine/images/categories/FR/br";
			$folder_description = "/home/boutique/www/configurations/$domaine/images/categories/FR/description";
			$folder_liste = "/home/boutique/www/configurations/$domaine/images/categories/FR/liste";
			$dossier = opendir($folder) ;
			while ($fichier = readdir($dossier))
				{
				if ($fichier != "." && $fichier != ".." && $fichier != "br" && $fichier != "index.htm" && $fichier != "index.php"
																				 && $fichier != "description" && $fichier != "liste")	
					{
					unlink($folder."/".$fichier);
					if (file_exists($folder_br."/".$fichier)) // br
						{
						unlink($folder_br."/".$fichier);
						}
					if (file_exists($folder_description."/".$fichier)) // description
						{
						unlink($folder_description."/".$fichier);
						}
					if (file_exists($folder_liste."/".$fichier)) // liste
						{
						unlink($folder_liste."/".$fichier);
						}
					}
				}
		
			// DE
			$folder = "/home/boutique/www/configurations/$domaine/images/categories/DE";
			$folder_br = "/home/boutique/www/configurations/$domaine/images/categories/DE/br";
			$folder_description = "/home/boutique/www/configurations/$domaine/images/categories/DE/description";
			$folder_liste = "/home/boutique/www/configurations/$domaine/images/categories/DE/liste";
			$dossier = opendir($folder);
			while ($fichier = readdir($dossier))
				{
				if ($fichier != "." && $fichier != ".." && $fichier != "br" && $fichier != "index.htm" && $fichier != "index.php"
																				 && $fichier != "description" && $fichier != "liste")	
					{
					unlink($folder."/".$fichier);
					if (file_exists($folder_br."/".$fichier)) // br
						{
						unlink($folder_br."/".$fichier);
						}
					if (file_exists($folder_description."/".$fichier)) // description
						{
						unlink($folder_description."/".$fichier);
						}
					if (file_exists($folder_liste."/".$fichier)) // liste
						{
						unlink($folder_liste."/".$fichier);
						}
					}
				}
		
			// SP
			$folder = "/home/boutique/www/configurations/$domaine/images/categories/SP";
			$folder_br = "/home/boutique/www/configurations/$domaine/images/categories/SP/br";
			$folder_description = "/home/boutique/www/configurations/$domaine/images/categories/SP/description";
			$folder_liste = "/home/boutique/www/configurations/$domaine/images/categories/SP/liste";
			$dossier = opendir($folder);
			while ($fichier = readdir($dossier))
				{
				if ($fichier != "." && $fichier != ".." && $fichier != "br" && $fichier != "index.htm" && $fichier != "index.php"
																				 && $fichier != "description" && $fichier != "liste")	
					{
					unlink($folder."/".$fichier);
					if (file_exists($folder_br."/".$fichier)) // br
						{
						unlink($folder_br."/".$fichier);
						}
					if (file_exists($folder_description."/".$fichier)) // description
						{
						unlink($folder_description."/".$fichier);
						}
					if (file_exists($folder_liste."/".$fichier)) // liste
						{
						unlink($folder_liste."/".$fichier);
						}
					}
				}
		
			// EN
			$folder = "/home/boutique/www/configurations/$domaine/images/categories/EN";
			$folder_br = "/home/boutique/www/configurations/$domaine/images/categories/EN/br";
			$folder_description = "/home/boutique/www/configurations/$domaine/images/categories/EN/description";
			$folder_liste = "/home/boutique/www/configurations/$domaine/images/categories/EN/liste";
			$dossier = opendir($folder);
			while( $fichier = readdir($dossier) )
				{
				if ($fichier != "." && $fichier != ".." && $fichier != "br" && $fichier != "index.htm" && $fichier != "index.php"
																				 && $fichier != "description" && $fichier != "liste")	
					{
					unlink($folder."/".$fichier);
					if (file_exists($folder_br."/".$fichier)) // br
						{
						unlink($folder_br."/".$fichier);
						}
					if (file_exists($folder_description."/".$fichier)) // description
						{
						unlink($folder_description."/".$fichier);
						}
					if (file_exists($folder_liste."/".$fichier)) // liste
						{
						unlink($folder_liste."/".$fichier);
						}
					}
				}
		
			// IT
			$folder = "/home/boutique/www/configurations/$domaine/images/categories/IT";
			$folder_br = "/home/boutique/www/configurations/$domaine/images/categories/IT/br";
			$folder_description = "/home/boutique/www/configurations/$domaine/images/categories/IT/description";
			$folder_liste = "/home/boutique/www/configurations/$domaine/images/categories/IT/liste";
			$dossier = opendir($folder);
			while ($fichier = readdir($dossier))
				{
				if ($fichier != "." && $fichier != ".." && $fichier != "br" && $fichier != "index.htm" && $fichier != "index.php"
																				 && $fichier != "description" && $fichier != "liste")	
					{
					unlink($folder."/".$fichier);
					if (file_exists($folder_br."/".$fichier)) // br
						{
						unlink($folder_br."/".$fichier);
						}
					if (file_exists($folder_description."/".$fichier)) // description
						{
						unlink($folder_description."/".$fichier);
						}
					if (file_exists($folder_liste."/".$fichier)) // liste
						{
						unlink($folder_liste."/".$fichier);
						}
					}
				}
	
		// documents à télécharger
		$folder = "/home/boutique/www/configurations/$domaine/images/docs";
		$dossier = opendir($folder) ;
		while ($fichier = readdir($dossier))
			{
			if ($fichier != "." && $fichier != "..")	
				{
				unlink($folder."/".$fichier);
				}
			}
	
		// liens (commerciaux...)
		$folder = "/home/boutique/www/configurations/$domaine/images/liens";
		$dossier = opendir($folder) ;
		while ($fichier = readdir($dossier))
			{
			if ($fichier != "." && $fichier != "..")	
				{
				unlink($folder."/".$fichier);
				}
			}
	
		// marques
		$folder = "/home/boutique/www/configurations/$domaine/images/marques";
		$dossier = opendir($folder) ;
		while ($fichier = readdir($dossier))
			{
			if ($fichier != "." && $fichier != "..")	
				{
				unlink($folder."/".$fichier);
				}
			}
	
		// partenaires
		$folder = "/home/boutique/www/configurations/$domaine/images/bannieres";
		$dossier = opendir($folder) ;
		while ($fichier = readdir($dossier))
			{
			if ($fichier != "." && $fichier != "..")	
				{
				unlink($folder."/".$fichier);
				}
			}
	
	
	/* REMPLISSAGE */
	
		// paramètres
		$DB_site2->query("update parametresgeneraux set pourcentage_default_affilie = '10', prix_contre_remboursement = '6.3', 
		avoir_minimum_affilie = '2', nb_news_par_page = '1', nb_art_toparticles = '3', top_ventes_type_affichage = 'top', 
		nouveautes_nb_jours_art_recents = '3', date_inscription = '2006-01-01', mail_contact = 'c.jullien@arobases.fr', taux_tva = '19.6', 
		position_coupsdecoeur = '2', position_topventes = '1', position_nouveautes = '4', position_promotions = '3', 
		affich_factures_abandonnees = '0', affiche_catalogue_partout = '0', minimum_frais_port_gratuit = '250', gestion_commentaires = 'auto', 
		onglets_activables = '1', onglets_actifs = '1', nb_onglets_max = '3', montant_max_cb = '1500', paypal_sup = '0', 
		contactFR = '<P align=\"center\"> Arobases<br>1, Imp. Jean Jacques ROUSSEAU<br>69740 Genas<br><br>Pour nous contacter : appelez-nous ou envoyez nous un fax au 08 72 26 83 05 (n° identique).<br>Nous vous repondrons dans les plus brefs délais !</P>', 
		contactDE = '<P align=\"center\"> Arobases<br>1, Imp. Jean Jacques ROUSSEAU<br>69740 Genas<br><br>Pour nous contacter : appelez-nous ou envoyez nous un fax au 08 72 26 83 05 (n° identique).<br>Nous vous repondrons dans les plus brefs délais !</P>', 
		contactEN = '<P align=\"center\"> Arobases<br>1, Imp. Jean Jacques ROUSSEAU<br>69740 Genas<br><br>Pour nous contacter : appelez-nous ou envoyez nous un fax au 08 72 26 83 05 (n° identique).<br>Nous vous repondrons dans les plus brefs délais !</P>', 
		contactIT = '<P align=\"center\"> Arobases<br>1, Imp. Jean Jacques ROUSSEAU<br>69740 Genas<br><br>Pour nous contacter : appelez-nous ou envoyez nous un fax au 08 72 26 83 05 (n° identique).<br>Nous vous repondrons dans les plus brefs délais !</P>', 
		contactSP = '<P align=\"center\"> Arobases<br>1, Imp. Jean Jacques ROUSSEAU<br>69740 Genas<br><br>Pour nous contacter : appelez-nous ou envoyez nous un fax au 08 72 26 83 05 (n° identique).<br>Nous vous repondrons dans les plus brefs délais !</P>', 
		marque_affichage = '1', marque_max = '10', marque_nb_ligne = '5', marque_tri = '0', typeaffichagepanier = '1', pagedocuments_active = '1',
		user_option_actif = '0', user_option_lib = '', user_option_obligatoire = '0', afficher_pdts_stock_epuise = '1'");
	
	
		$index = '[boutiquetitre] : Boutique en ligne' ;
		$categorie = '${$title} : $pagetitle_catname' ;
		$article = '${$title} : $article[$libelle]' ;
		$institutionnel = '${$title} : $institutionnel[$libelle]' ;
		$rechercher = '${$title} : Rechercher un article' ;
		$client = '${$title} : Espace client  	' ;
		$passwordrecover = '${$title} : Recuperation de mot de passe' ;
		$cde1 = '${$title} : Commander un article > etape 1' ;
		$cde2 = '${$title} : Commander un article > etape 2' ;
		$cde3 = '${$title} : Commander un article > etape 3' ;
		$contacter = '${$title} : Nous contacter' ;
		// descriptions
		$DB_site2->query("update description set titleFR = '$index', titleDE = '$index', titleSP = '$index', titleEN = '$index', titleIT = '$index' where scriptname = '/index.php'");
		$DB_site2->query("update description set titleFR = '$categorie', titleDE = '$categorie', titleSP = '$categorie', titleEN = '$categorie', titleIT = '$categorie' where scriptname = '/categorie.php'");
		$DB_site2->query("update description set titleFR = '$article', titleDE = '$article', titleSP = '$article', titleEN = '$article', titleIT = '$article' where scriptname = '/article.php'");
		$DB_site2->query("update description set titleFR = '$institutionnel', titleDE = '$institutionnel', titleSP = '$institutionnel', titleEN = '$institutionnel', titleIT = '$institutionnel' where scriptname = '/institutionnel.php'");
		$DB_site2->query("update description set titleFR = '$rechercher', titleDE = '$rechercher', titleSP = '$rechercher', titleEN = '$rechercher', titleIT = '$rechercher' where scriptname = '/rechercher.php'");
		$DB_site2->query("update description set titleFR = '$client', titleDE = '$client', titleSP = '$client', titleEN = '$client', titleIT = '$client' where scriptname = '/client.php'");
		$DB_site2->query("update description set titleFR = '$passwordrecover', titleDE = '$passwordrecover', titleSP = '$passwordrecover', titleEN = '$passwordrecover', titleIT = '$passwordrecover' where scriptname = '/passwordrecover.php'");
		$DB_site2->query("update description set titleFR = '$cde1', titleDE = '$cde1', titleSP = '$cde1', titleEN = '$cde1', titleIT = '$cde1' where scriptname = '/cde1.php'");
		$DB_site2->query("update description set titleFR = '$cde2', titleDE = '$cde2', titleSP = '$cde2', titleEN = '$cde2', titleIT = '$cde2' where scriptname = '/cde2.php'");
		$DB_site2->query("update description set titleFR = '$cde3', titleDE = '$cde3', titleSP = '$cde3', titleEN = '$cde3', titleIT = '$cde3' where scriptname = '/cde3.php'");
		$DB_site2->query("update description set titleFR = '$contacter', titleDE = '$contacter', titleSP = '$contacter', titleEN = '$contacter', titleIT = '$contacter' where scriptname = '/contacter.php'");
	
		// keywords
		$DB_site2->query("update keywords set titleFR = '$index', titleDE = '$index', titleSP = '$index', titleEN = '$index', titleIT = '$index' where scriptname = '/index.php'");
		$DB_site2->query("update keywords set titleFR = '$categorie', titleDE = '$categorie', titleSP = '$categorie', titleEN = '$categorie', titleIT = '$categorie' where scriptname = '/categorie.php'");
		$DB_site2->query("update keywords set titleFR = '$article', titleDE = '$article', titleSP = '$article', titleEN = '$article', titleIT = '$article' where scriptname = '/article.php'");
		$DB_site2->query("update keywords set titleFR = '$institutionnel', titleDE = '$institutionnel', titleSP = '$institutionnel', titleEN = '$institutionnel', titleIT = '$institutionnel' where scriptname = '/institutionnel.php'");
		$DB_site2->query("update keywords set titleFR = '$rechercher', titleDE = '$rechercher', titleSP = '$rechercher', titleEN = '$rechercher', titleIT = '$rechercher' where scriptname = '/rechercher.php'");
		$DB_site2->query("update keywords set titleFR = '$client', titleDE = '$client', titleSP = '$client', titleEN = '$client', titleIT = '$client' where scriptname = '/client.php'");
		$DB_site2->query("update keywords set titleFR = '$passwordrecover', titleDE = '$passwordrecover', titleSP = '$passwordrecover', titleEN = '$passwordrecover', titleIT = '$passwordrecover' where scriptname = '/passwordrecover.php'");
		$DB_site2->query("update keywords set titleFR = '$cde1', titleDE = '$cde1', titleSP = '$cde1', titleEN = '$cde1', titleIT = '$cde1' where scriptname = '/cde1.php'");
		$DB_site2->query("update keywords set titleFR = '$cde2', titleDE = '$cde2', titleSP = '$cde2', titleEN = '$cde2', titleIT = '$cde2' where scriptname = '/cde2.php'");
		$DB_site2->query("update keywords set titleFR = '$cde3', titleDE = '$cde3', titleSP = '$cde3', titleEN = '$cde3', titleIT = '$cde3' where scriptname = '/cde3.php'");
		$DB_site2->query("update keywords set titleFR = '$contacter', titleDE = '$contacter', titleSP = '$contacter', titleEN = '$contacter', titleIT = '$contacter' where scriptname = '/contacter.php'");
		
		// moyenpaiement
		$DB_site2->query("update moyenpaiement set accepte = '0' where moyenid = '0'");
		$DB_site2->query("update moyenpaiement set accepte = '0' where moyenid = '1'");
		$DB_site2->query("update moyenpaiement set accepte = '1' where moyenid = '2'");
		$DB_site2->query("update moyenpaiement set accepte = '1' where moyenid = '3'");
		$DB_site2->query("update moyenpaiement set accepte = '1' where moyenid = '4'");
		$DB_site2->query("update moyenpaiement set accepte = '0' where moyenid = '5'");
		
		// pagetitle
		$index = '${$title} : $multilangue[boutiqueenligne]' ;
		$categorie = '${$title} : $pagetitle_catname' ;
		$article = '${$title} : $article[$libelle]' ;
		$institutionnel = '${$title} : $institutionnel[$libelle]' ;
		$rechercher = '${$title} : Rechercher un article' ;
		$client = '${$title} : Espace client  	' ;
		$passwordrecover = '${$title} : Recuperation de mot de passe' ;
		$cde1 = '${$title} : Commander un article > etape 1' ;
		$cde2 = '${$title} : Commander un article > etape 2' ;
		$cde3 = '${$title} : Commander un article > etape 3' ;
		$contacter = '${$title} : Nous contacter' ;
		$DB_site2->query("update pagetitle set titleFR = '$index', titleDE = '$index', titleSP = '$index', titleEN = '$index', titleIT = '$index' where scriptname = '/index.php'");
		$DB_site2->query("update pagetitle set titleFR = '$categorie', titleDE = '$categorie', titleSP = '$categorie', titleEN = '$categorie', titleIT = '$categorie' where scriptname = '/categorie.php'");
		$DB_site2->query("update pagetitle set titleFR = '$article', titleDE = '$article', titleSP = '$article', titleEN = '$article', titleIT = '$article' where scriptname = '/article.php'");
		$DB_site2->query("update pagetitle set titleFR = '$institutionnel', titleDE = '$institutionnel', titleSP = '$institutionnel', titleEN = '$institutionnel', titleIT = '$institutionnel' where scriptname = '/institutionnel.php'");
		$DB_site2->query("update pagetitle set titleFR = '$rechercher', titleDE = '$rechercher', titleSP = '$rechercher', titleEN = '$rechercher', titleIT = '$rechercher' where scriptname = '/rechercher.php'");
		$DB_site2->query("update pagetitle set titleFR = '$client', titleDE = '$client', titleSP = '$client', titleEN = '$client', titleIT = '$client' where scriptname = '/client.php'");
		$DB_site2->query("update pagetitle set titleFR = '$passwordrecover', titleDE = '$passwordrecover', titleSP = '$passwordrecover', titleEN = '$passwordrecover', titleIT = '$passwordrecover' where scriptname = '/passwordrecover.php'");
		$DB_site2->query("update pagetitle set titleFR = '$cde1', titleDE = '$cde1', titleSP = '$cde1', titleEN = '$cde1', titleIT = '$cde1' where scriptname = '/cde1.php'");
		$DB_site2->query("update pagetitle set titleFR = '$cde2', titleDE = '$cde2', titleSP = '$cde2', titleEN = '$cde2', titleIT = '$cde2' where scriptname = '/cde2.php'");
		$DB_site2->query("update pagetitle set titleFR = '$cde3', titleDE = '$cde3', titleSP = '$cde3', titleEN = '$cde3', titleIT = '$cde3' where scriptname = '/cde3.php'");
		$DB_site2->query("update pagetitle set titleFR = '$contacter', titleDE = '$contacter', titleSP = '$contacter', titleEN = '$contacter', titleIT = '$contacter' where scriptname = '/contacter.php'");
	
		// pays
		$DB_site2->query("update pays set TVAtauxnormal = '0', TVAtauxreduit = '0', port = '0', gestion = '0'");
	
		// formulaire
		$DB_site2->query("insert into formulaire (nom, description, actif) values ('Contacter', 'Formulaire de la page \'Contacter\' de la boutique.', '1')");
		$DB_site2->query("insert into formulaire (nom, description, actif) values ('Perso', 'Formulaire personnel.', '0')");
	
		// formulaire_champ  \\ formulaire_champ_valeur
		$DB_site2->query("insert into formulaire_champ (formulaireid, libelleFR, libelleDE, libelleEN, libelleIT, libelleSP, nom, type, obligatoire, position) values ('1', 'Expéditeur', 'Absender', 'Sender', 'Expéditeur', 'Expéditeur', 'expediteur', '4', '1', '1')");
		$insertid=$DB_site2->insert_id() ;
		$DB_site2->query("insert into formulaire_champ_valeur (formulairechampid, libelle, description, valeur) values ('$insertid', 'maxlength', 'Taille', '100')");
		$DB_site2->query("insert into formulaire_champ_valeur (formulairechampid, libelle, description, valeur) values ('$insertid', 'size', 'Longueur', '30')");
	
		$DB_site2->query("insert into formulaire_champ (formulaireid, libelleFR, libelleDE, libelleEN, libelleIT, libelleSP, nom, type, obligatoire, position) values ('1', 'Sujet', 'Thema', 'Subject', 'Sujet', 'Sujeto', 'sujet', '4', '1', '2')");
		$insertid=$DB_site2->insert_id() ;
		$DB_site2->query("insert into formulaire_champ_valeur (formulairechampid, libelle, description, valeur) values ('$insertid', 'size', 'Longueur', '53')");
		$DB_site2->query("insert into formulaire_champ_valeur (formulairechampid, libelle, description, valeur) values ('$insertid', 'maxlength', 'Taille', '150')");
	
		$DB_site2->query("insert into formulaire_champ (formulaireid, libelleFR, libelleDE, libelleEN, libelleIT, libelleSP, nom, type, obligatoire, position) values ('1', 'Message', 'Mitteilung', 'Message', 'Message', 'Mesage', 'message', '1', '1', '3')");
		$insertid=$DB_site2->insert_id() ;
		$DB_site2->query("insert into formulaire_champ_valeur (formulairechampid, libelle, description, valeur) values ('$insertid', 'cols', 'Largeur', '40')");
		$DB_site2->query("insert into formulaire_champ_valeur (formulairechampid, libelle, description, valeur) values ('$insertid', 'rows', 'Hauteur', '6')");

	
		// utilisateur
		$pass = md5("azerty") ;
		$DB_site2->query("insert into utilisateur (mail, password, civilite, nom, prenom, codepostal, adresse, ville, telephone, 
		raisonsociale, dateinscription, paysid, langue, allowemail, groupeid)
		values ('c.jullien@arobases.fr', '$pass', '2', 'Jullien', 'Caroline', '69003', '11 Quai Victor Augagneur', 'LYON', '0478602589', 'Arobases',
		'2006-01-01', '57', 'FR', '0', '0')");
		
		// institutionnel
		$lib = "A propos" ;
		$contenu = "<p align=\"center\">Cette page a été créée depuis le paneau d\'administration dans la rubrique <strong>Pages institutionnelles</strong>.<br>
		Vous pouvez créer autant de pages que vous le souhaitez et les personnaliser à souhait en y ajoutant des <font color=\"red\">cou</font>l<font color=\"blue\">eurs</font>, des images...<br>
		Vous choisissez vos <font face=\"Verdana, Arial, Helvetica, sans-serif\" size=\"6\">polices</font>.<br><br>
		Une fois vos pages créées, vous pouvez choisir de les activer ou non.</p>" ;
		$DB_site2->query("insert into institutionnel (active, position, libelleFR, libelleDE, libelleSP, libelleEN, libelleIT, contenuFR,
		contenuDE, contenuSP, contenuEN, contenuIT)	values ('1', '1', '$lib', '$lib', '$lib', '$lib', '$lib', '$contenu', '$contenu', '$contenu',
		'$contenu', '$contenu')");
	
		// fraisport
		$DB_site2->query("insert into fraisport (poiddebut, poidfin, prix, paysid) values ('0', '10000', '10', '57')");
		
		// marque
		$lib = "Levis" ;
		$type_fichier = "jpg" ;
		$DB_site2->query("insert into marque (libelle, image, position) values ('$lib', '$type_fichier', '1')");
		$levis=$DB_site2->insert_id() ;
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/marquelevis.jpg";
		$destination0="/home/boutique/www/configurations/$domaine/images/marques/$levis.$type_fichier";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		
		$lib = "Lee Cooper" ;
		$type_fichier = "gif" ;
		$DB_site2->query("insert into marque (libelle, image, position) values ('$lib', '$type_fichier', '1')");
		$leecooper=$DB_site2->insert_id() ;
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/marque_lee_cooper.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/marques/$leecooper.$type_fichier";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
	
		// news
		$ajd = date ('U') ;
		$libFR = "Exemple de design \'Sur mesure\'" ;
		$contFR = "<font size=\"-1\">Bonjour,<br><br>
		Bienvenue sur notre démonstration de design \'Sur mesure\' !<br><br>
		Vous souhaitez tester notre panneau d\'administration ?<br>
		Connectez vous ici : <a href=\"http://www.vendre-en-ligne.fr/connexion.php\" target=\"_blank\">http://www.vendre-en-ligne.fr/connexion.php</a><br>
		Login : surmesure<br>
		Mot de passe : arobases<br><br>
		N\'hésitez pas, faites le tour et modifiez tout ce que vous voulez* !<br>
		*Les modifications sont écrasées tous les jours.</font>" ;
		$libDE = "Designbeispiel \'Sur mesure\'" ;
		$contDE = "<font size=\"-1\">Guten Tag,<br><br>
		Willkommen auf unserer Designdemonstration  \'Sur mesure\' !<br><br>
		Sie wünschen, unser Verwaltungsbrett zu testen ?<br>
		Hier verbinden Sie Sie : <a href=\"http://www.vendre-en-ligne.fr/connexion.php\" target=\"_blank\">http://www.vendre-en-ligne.fr/connexion.php</a><br>
		Login : surmesure<br>
		Kennwort : arobases<br><br>
		Sind nicht unentschlossen gemacht die Umdrehung und ändern alles, was Sie wollen* !<br>
		*Die änderungen werden alle Tage gelöscht.</font>" ;
		$libEN = "Example of design \'Sur mesure\'" ;
		$contEN = "<font size=\"-1\">Hello,<br><br>
		Welcome on our demonstration of design \'Sur mesure\' !<br><br>
		You wish to test our administration panel ?<br>
		Connect here : <a href=\"http://www.vendre-en-ligne.fr/connexion.php\" target=\"_blank\">http://www.vendre-en-ligne.fr/connexion.php</a><br>
		Login : surmesure<br>
		Password : arobases<br><br>
		Do not hesitate, make the turn and modify all that you want* !<br>
		*The modifications are crushed every day.</font>" ;
		$libIT = "Exemple de design \'Sur mesure\'" ;
		$contIT = "<font size=\"-1\">Bonjour,<br><br>
		Bienvenue sur notre démonstration de design \'Sur mesure\' !<br><br>
		Vous souhaitez tester notre panneau d\'administration ?<br>
		Connectez vous ici : <a href=\"http://www.vendre-en-ligne.fr/connexion.php\" target=\"_blank\">http://www.vendre-en-ligne.fr/connexion.php</a><br>
		Login : surmesure<br>
		Mot de passe : arobases<br><br>
		N\'hésitez pas, faites le tour et modifiez tout ce que vous voulez* !<br>
		*Les modifications sont écrasées tous les jours.</font>" ;
		$libSP = "Exemple de design \'Sur mesure\'" ;
		$contSP = "<font size=\"-1\">Hola,<br><br>
		Bienvenidos en nuestra dmonstracion de design \'Sur mesure\' !<br><br>
		? Quiere testar el panel de administracion ?<br>
		Se connecte aqui : <a href=\"http://www.vendre-en-ligne.fr/connexion.php\" target=\"_blank\">http://www.vendre-en-ligne.fr/connexion.php</a><br>
		Login : surmesure<br>
		Mot de passe : arobases<br><br>
		N\'hésitez pas, faites le tour et modifiez tout ce que vous voulez* !<br>
		*Les modifications sont écrasées tous les jours.</font>" ;
		$DB_site2->query("insert into news (datenews, libelleFR, contenuFR, libelleDE, contenuDE, libelleEN, contenuEN, libelleIT, contenuIT,
		 libelleSP, contenuSP) values ('$ajd', '$libFR', '$contFR', '$libDE', '$contDE', '$libEN', '$contEN', '$libIT', '$contIT', '$libSP',
		  '$contSP')");

		// caracteristique 
		$DB_site2->query("insert into caracteristique (libelleFR, libelleDE, libelleEN, libelleIT, libelleSP, description) values ('Couleur', 'Couleur', 'Couleur', 'Couleur', 'Couleur', 'Couleur de l\'article')");
		$couleur=$DB_site2->insert_id() ;
		
		$DB_site2->query("insert into caracteristique (libelleFR, libelleDE, libelleEN, libelleIT, libelleSP, description) values ('Taille', 'Taille', 'Taille', 'Taille', 'Taille', 'Taille de l\'article')");
		$taille=$DB_site2->insert_id() ;
		
		// caracteristiquevaleur
		$DB_site2->query("insert into caracteristiquevaleur (caractid, libelleFR, libelleDE, libelleEN, libelleIT, libelleSP) values ('$couleur', 'Noir', 'Noir', 'Noir', 'Noir', 'Noir')");
		$noir=$DB_site2->insert_id() ;
		$DB_site2->query("insert into caracteristiquevaleur (caractid, libelleFR, libelleDE, libelleEN, libelleIT, libelleSP) values ('$couleur', 'Bleu', 'Bleu', 'Bleu', 'Bleu', 'Bleu')");
		$bleu=$DB_site2->insert_id() ;
		$DB_site2->query("insert into caracteristiquevaleur (caractid, libelleFR, libelleDE, libelleEN, libelleIT, libelleSP) values ('$couleur', 'Vert', 'Vert', 'Vert', 'Vert', 'Vert')");
		$vert=$DB_site2->insert_id() ;
		$DB_site2->query("insert into caracteristiquevaleur (caractid, libelleFR, libelleDE, libelleEN, libelleIT, libelleSP) values ('$couleur', 'Rouge', 'Rouge', 'Rouge', 'Rouge', 'Rouge')");
		$rouge=$DB_site2->insert_id() ;
	
		$DB_site2->query("insert into caracteristiquevaleur (caractid, libelleFR, libelleDE, libelleEN, libelleIT, libelleSP) values ('$taille', '34', '34', '34', '34', '34')");
		$taille34=$DB_site2->insert_id() ;
		$DB_site2->query("insert into caracteristiquevaleur (caractid, libelleFR, libelleDE, libelleEN, libelleIT, libelleSP) values ('$taille', '36', '36', '36', '36', '36')");
		$taille36=$DB_site2->insert_id() ;
		$DB_site2->query("insert into caracteristiquevaleur (caractid, libelleFR, libelleDE, libelleEN, libelleIT, libelleSP) values ('$taille', '38', '38', '38', '38', '38')");
		$taille38=$DB_site2->insert_id() ;
		$DB_site2->query("insert into caracteristiquevaleur (caractid, libelleFR, libelleDE, libelleEN, libelleIT, libelleSP) values ('$taille', '40', '40', '40', '40', '40')");
		$taille40=$DB_site2->insert_id() ;
		
		// categorie
		$lib = "Maillots de bain" ;
		$desc = "Maillots de bain" ;
		$img = "gif" ;
		$DB_site2->query("insert into categorie (parentid, libelleFR, descriptionFR, imageFR, libelleDE, descriptionDE, imageDE, libelleEN,
		descriptionEN, imageEN, libelleIT, descriptionIT, imageIT, libelleSP, descriptionSP, imageSP, onglet, visible_treeview) 
		values ('0', '$lib', '$desc', '$img', '$lib', '$desc', '$img','$lib', '$desc', '$img','$lib', '$desc', '$img','$lib', '$desc', 
		'$img', '1', '1')");
		$maillotsdebain=$DB_site2->insert_id() ;
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/maillot2pieces.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/FR/$maillotsdebain.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/FR/liste/".$maillotsdebain.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/FR/description/".$maillotsdebain.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/FR/br/".$maillotsdebain.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/maillot2pieces.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/DE/$maillotsdebain.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/DE/liste/".$maillotsdebain.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/DE/description/".$maillotsdebain.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/DE/br/".$maillotsdebain.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/maillot2pieces.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/EN/$maillotsdebain.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/EN/liste/".$maillotsdebain.".".$img; 
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/EN/description/".$maillotsdebain.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/EN/br/".$maillotsdebain.".".$img; 
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/maillot2pieces.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/IT/$maillotsdebain.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/IT/liste/".$maillotsdebain.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/IT/description/".$maillotsdebain.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/IT/br/".$maillotsdebain.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/maillot2pieces.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/SP/$maillotsdebain.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/SP/liste/".$maillotsdebain.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/SP/description/".$maillotsdebain.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/SP/br/".$maillotsdebain.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
	
		$lib = "Tops" ;
		$desc = "Tops" ;
		$img = "gif" ;
		$DB_site2->query("insert into categorie (parentid, libelleFR, descriptionFR, imageFR, libelleDE, descriptionDE, imageDE, libelleEN,
		descriptionEN, imageEN, libelleIT, descriptionIT, imageIT, libelleSP, descriptionSP, imageSP, onglet, visible_treeview) 
		values ('0', '$lib', '$desc', '$img', '$lib', '$desc', '$img','$lib', '$desc', '$img','$lib', '$desc', '$img','$lib', '$desc', 
		'$img', '0', '1')");
		$tops=$DB_site2->insert_id() ;
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/topmanchescourtes.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/FR/$tops.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/FR/liste/".$tops.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/FR/description/".$tops.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/FR/br/".$tops.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/topmanchescourtes.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/DE/$tops.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/DE/liste/".$tops.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/DE/description/".$tops.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/DE/br/".$tops.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/topmanchescourtes.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/EN/$tops.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/EN/liste/".$tops.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/EN/description/".$tops.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/EN/br/".$tops.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/topmanchescourtes.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/IT/$tops.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/IT/liste/".$tops.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/IT/description/".$tops.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/IT/br/".$tops.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/topmanchescourtes.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/SP/$tops.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/SP/liste/".$tops.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/SP/description/".$tops.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/SP/br/".$tops.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
	
		$lib = "Pantalons" ;
		$desc = "Pantalons" ;
		$img = "jpg" ;
		$DB_site2->query("insert into categorie (parentid, libelleFR, descriptionFR, imageFR, libelleDE, descriptionDE, imageDE, libelleEN,
		descriptionEN, imageEN, libelleIT, descriptionIT, imageIT, libelleSP, descriptionSP, imageSP, onglet, visible_treeview) 
		values ('0', '$lib', '$desc', '$img', '$lib', '$desc', '$img','$lib', '$desc', '$img','$lib', '$desc', '$img','$lib', '$desc', 
		'$img', '0', '1')");
		$pantalons=$DB_site2->insert_id() ;
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/pantalons.jpg";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/FR/$pantalons.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/FR/liste/".$pantalons.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/FR/description/".$pantalons.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/FR/br/".$pantalons.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/pantalons.jpg";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/DE/$pantalons.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/DE/liste/".$pantalons.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/DE/description/".$pantalons.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/DE/br/".$pantalons.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/pantalons.jpg";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/EN/$pantalons.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/EN/liste/".$pantalons.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/EN/description/".$pantalons.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/EN/br/".$pantalons.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/pantalons.jpg";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/IT/$pantalons.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/IT/liste/".$pantalons.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/IT/description/".$pantalons.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/IT/br/".$pantalons.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/pantalons.jpg";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/SP/$pantalons.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/SP/liste/".$pantalons.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/SP/description/".$pantalons.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/SP/br/".$pantalons.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
	
		$lib = "Jeans" ;
		$desc = "Jeans" ;
		$img = "gif" ;
		$DB_site2->query("insert into categorie (parentid, libelleFR, descriptionFR, imageFR, libelleDE, descriptionDE, imageDE, libelleEN,
		descriptionEN, imageEN, libelleIT, descriptionIT, imageIT, libelleSP, descriptionSP, imageSP, onglet, visible_treeview) 
		values ('$pantalons', '$lib', '$desc', '$img', '$lib', '$desc', '$img','$lib', '$desc', '$img','$lib', '$desc', '$img','$lib', 
		'$desc', '$img', '1', '1')");
		$jeans=$DB_site2->insert_id() ;
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/jeans.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/FR/$jeans.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/FR/liste/".$jeans.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/FR/description/".$jeans.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/FR/br/".$jeans.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/jeans.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/DE/$jeans.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/DE/liste/".$jeans.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/DE/description/".$jeans.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/DE/br/".$jeans.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/jeans.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/EN/$jeans.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/EN/liste/".$jeans.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/EN/description/".$jeans.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/EN/br/".$jeans.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/jeans.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/IT/$jeans.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/IT/liste/".$jeans.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/IT/description/".$jeans.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/IT/br/".$jeans.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/jeans.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/SP/$jeans.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/SP/liste/".$jeans.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/SP/description/".$jeans.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/SP/br/".$jeans.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		
		$lib = "Pantalons taille basse" ;
		$desc = "Pantalons taille basse" ;
		$img = "jpg" ;
		$DB_site2->query("insert into categorie (parentid, libelleFR, descriptionFR, imageFR, libelleDE, descriptionDE, imageDE, libelleEN,
		descriptionEN, imageEN, libelleIT, descriptionIT, imageIT, libelleSP, descriptionSP, imageSP, onglet, visible_treeview) 
		values ('$pantalons', '$lib', '$desc', '$img', '$lib', '$desc', '$img','$lib', '$desc', '$img','$lib', '$desc', '$img','$lib', 
		'$desc', '$img', '0', '1')");
		$pantalonstaillebasse=$DB_site2->insert_id() ;
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/pantalontaillebasse.jpg";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/FR/$pantalonstaillebasse.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/FR/liste/".$pantalonstaillebasse.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/FR/description/".$pantalonstaillebasse.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/FR/br/".$pantalonstaillebasse.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/pantalontaillebasse.jpg";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/DE/$pantalonstaillebasse.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/DE/liste/".$pantalonstaillebasse.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/DE/description/".$pantalonstaillebasse.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/DE/br/".$pantalonstaillebasse.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/pantalontaillebasse.jpg";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/EN/$pantalonstaillebasse.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/EN/liste/".$pantalonstaillebasse.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/EN/description/".$pantalonstaillebasse.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/EN/br/".$pantalonstaillebasse.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/pantalontaillebasse.jpg";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/IT/$pantalonstaillebasse.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/IT/liste/".$pantalonstaillebasse.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/IT/description/".$pantalonstaillebasse.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/IT/br/".$pantalonstaillebasse.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/pantalontaillebasse.jpg";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/SP/$pantalonstaillebasse.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/SP/liste/".$pantalonstaillebasse.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/SP/description/".$pantalonstaillebasse.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/SP/br/".$pantalonstaillebasse.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		
		$lib = "Jupes" ;
		$desc = "Jupes" ;
		$img = "jpg" ;
		$DB_site2->query("insert into categorie (parentid, libelleFR, descriptionFR, imageFR, libelleDE, descriptionDE, imageDE, libelleEN,
		descriptionEN, imageEN, libelleIT, descriptionIT, imageIT, libelleSP, descriptionSP, imageSP, onglet, visible_treeview) 
		values ('0', '$lib', '$desc', '$img', '$lib', '$desc', '$img','$lib', '$desc', '$img','$lib', '$desc', '$img','$lib', '$desc', 
		'$img', '0', '1')");
		$jupes=$DB_site2->insert_id() ;
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/jupes.jpg";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/FR/$jupes.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/FR/liste/".$jupes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/FR/description/".$jupes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/FR/br/".$jupes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/jupes.jpg";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/DE/$jupes.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/DE/liste/".$jupes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/DE/description/".$jupes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/DE/br/".$jupes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/jupes.jpg";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/EN/$jupes.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/EN/liste/".$jupes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/EN/description/".$jupes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/EN/br/".$jupes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/jupes.jpg";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/IT/$jupes.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/IT/liste/".$jupes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/IT/description/".$jupes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/IT/br/".$jupes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/pantalontaillebasse.jpg";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/SP/$jupes.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/SP/liste/".$jupes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/SP/description/".$jupes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/SP/br/".$jupes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
	
		$lib = "Vestes" ;
		$desc = "Vestes" ;
		$img = "gif" ;
		$DB_site2->query("insert into categorie (parentid, libelleFR, descriptionFR, imageFR, libelleDE, descriptionDE, imageDE, libelleEN,
		descriptionEN, imageEN, libelleIT, descriptionIT, imageIT, libelleSP, descriptionSP, imageSP, onglet, visible_treeview) 
		values ('0', '$lib', '$desc', '$img', '$lib', '$desc', '$img','$lib', '$desc', '$img','$lib', '$desc', '$img','$lib', '$desc', 
		'$img', '0', '1')");
		$vestes=$DB_site2->insert_id() ;
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/vestes.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/FR/$vestes.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/FR/liste/".$vestes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/FR/description/".$vestes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/FR/br/".$vestes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/vestes.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/DE/$vestes.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/DE/liste/".$vestes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/DE/description/".$vestes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/DE/br/".$vestes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/vestes.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/EN/$vestes.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/EN/liste/".$vestes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/EN/description/".$vestes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/EN/br/".$vestes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/vestes.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/IT/$vestes.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/IT/liste/".$vestes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/IT/description/".$vestes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/IT/br/".$vestes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/vestes.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/SP/$vestes.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/SP/liste/".$vestes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/SP/description/".$vestes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/SP/br/".$vestes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		
		$lib = "Cardigans" ;
		$desc = "Cardigans" ;
		$img = "jpg" ;
		$DB_site2->query("insert into categorie (parentid, libelleFR, descriptionFR, imageFR, libelleDE, descriptionDE, imageDE, libelleEN,
		descriptionEN, imageEN, libelleIT, descriptionIT, imageIT, libelleSP, descriptionSP, imageSP, onglet, visible_treeview) values ('$vestes', '$lib', '$desc',
		 '$img', '$lib', '$desc', '$img','$lib', '$desc', '$img','$lib', '$desc', '$img','$lib', '$desc', '$img', '0', '1')");
		$cardigans=$DB_site2->insert_id() ;
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/cardigan.jpg";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/FR/$cardigans.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/FR/liste/".$cardigans.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/FR/description/".$cardigans.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/FR/br/".$cardigans.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/cardigan.jpg";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/DE/$cardigans.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/DE/liste/".$cardigans.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/DE/description/".$cardigans.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/DE/br/".$cardigans.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/cardigan.jpg";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/EN/$cardigans.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/EN/liste/".$cardigans.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/EN/description/".$cardigans.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/EN/br/".$cardigans.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/cardigan.jpg";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/IT/$cardigans.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/IT/liste/".$cardigans.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/IT/description/".$cardigans.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/IT/br/".$cardigans.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/cardigan.jpg";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/SP/$cardigans.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/SP/liste/".$cardigans.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/SP/description/".$cardigans.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/SP/br/".$cardigans.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		
		$lib = "Vestes de tailleur" ;
		$desc = "Vestes de tailleur" ;
		$img = "gif" ;
		$DB_site2->query("insert into categorie (parentid, libelleFR, descriptionFR, imageFR, libelleDE, descriptionDE, imageDE, libelleEN,
		descriptionEN, imageEN, libelleIT, descriptionIT, imageIT, libelleSP, descriptionSP, imageSP, onglet, visible_treeview) 
		values ('$vestes', '$lib', '$desc', '$img', '$lib', '$desc', '$img','$lib', '$desc', '$img','$lib', '$desc', '$img','$lib', 
		'$desc', '$img', '1', '1')");
		$vestestailleurs=$DB_site2->insert_id() ;
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/vestetailleur.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/FR/$vestestailleurs.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/FR/liste/".$vestestailleurs.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/FR/description/".$vestestailleurs.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/FR/br/".$vestestailleurs.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/vestetailleur.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/DE/$vestestailleurs.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/DE/liste/".$vestestailleurs.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/DE/description/".$vestestailleurs.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/DE/br/".$vestestailleurs.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/vestetailleur.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/EN/$vestestailleurs.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/EN/liste/".$vestestailleurs.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/EN/description/".$vestestailleurs.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/EN/br/".$vestestailleurs.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/vestetailleur.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/IT/$vestestailleurs.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/IT/liste/".$vestestailleurs.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/IT/description/".$vestestailleurs.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/IT/br/".$vestestailleurs.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/vestetailleur.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/SP/$vestestailleurs.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/SP/liste/".$vestestailleurs.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/SP/description/".$vestestailleurs.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/SP/br/".$vestestailleurs.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		
		$lib = "Autres vestes" ;
		$desc = "Autres vestes" ;
		$img = "gif" ;
		$DB_site2->query("insert into categorie (parentid, libelleFR, descriptionFR, imageFR, libelleDE, descriptionDE, imageDE, libelleEN,
		descriptionEN, imageEN, libelleIT, descriptionIT, imageIT, libelleSP, descriptionSP, imageSP, onglet, visible_treeview) 
		values ('$vestes', '$lib', '$desc', '$img', '$lib', '$desc', '$img','$lib', '$desc', '$img','$lib', '$desc', '$img','$lib', 
		'$desc', '$img', '0', '1')");
		$autresvestes=$DB_site2->insert_id() ;
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/vesteblanche.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/FR/$autresvestes.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/FR/liste/".$autresvestes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/FR/description/".$autresvestes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/FR/br/".$autresvestes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/vesteblanche.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/DE/$autresvestes.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/DE/liste/".$autresvestes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/DE/description/".$autresvestes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/DE/br/".$autresvestes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/vesteblanche.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/EN/$autresvestes.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/EN/liste/".$autresvestes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/EN/description/".$autresvestes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/EN/br/".$autresvestes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/vesteblanche.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/IT/$autresvestes.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/IT/liste/".$autresvestes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/IT/description/".$autresvestes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/IT/br/".$autresvestes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/vesteblanche.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/SP/$autresvestes.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/SP/liste/".$autresvestes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/SP/description/".$autresvestes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/SP/br/".$autresvestes.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		
		$lib = "Shorts" ;
		$desc = "Shorts" ;
		$img = "gif" ;
		$DB_site2->query("insert into categorie (parentid, libelleFR, descriptionFR, imageFR, libelleDE, descriptionDE, imageDE, libelleEN,
		descriptionEN, imageEN, libelleIT, descriptionIT, imageIT, libelleSP, descriptionSP, imageSP, onglet, visible_treeview) 
		values ('0', '$lib', '$desc', '$img', '$lib', '$desc', '$img','$lib', '$desc', '$img','$lib', '$desc', '$img','$lib', '$desc', 
		'$img', '0', '1')");
		$shorts=$DB_site2->insert_id() ;
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/bermuda.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/FR/$shorts.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/FR/liste/".$shorts.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/FR/description/".$shorts.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/FR/br/".$shorts.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/bermuda.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/DE/$shorts.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/DE/liste/".$shorts.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/DE/description/".$shorts.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/DE/br/".$shorts.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/bermuda.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/EN/$shorts.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/EN/liste/".$shorts.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/EN/description/".$shorts.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/EN/br/".$shorts.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/bermuda.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/IT/$shorts.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/IT/liste/".$shorts.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/IT/description/".$shorts.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/IT/br/".$shorts.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/bermuda.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/SP/$shorts.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/SP/liste/".$shorts.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/SP/description/".$shorts.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/SP/br/".$shorts.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		
		$lib = "Bermudas" ;
		$desc = "Bermudas" ;
		$img = "gif" ;
		$DB_site2->query("insert into categorie (parentid, libelleFR, descriptionFR, imageFR, libelleDE, descriptionDE, imageDE, libelleEN,
		descriptionEN, imageEN, libelleIT, descriptionIT, imageIT, libelleSP, descriptionSP, imageSP, onglet, visible_treeview) 
		values ('$shorts', '$lib', '$desc', '$img', '$lib', '$desc', '$img','$lib', '$desc', '$img','$lib', '$desc', '$img','$lib', 
		'$desc', '$img', '0', '1')");
		$bermudas=$DB_site2->insert_id() ;
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/bermuda.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/FR/$bermudas.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/FR/liste/".$bermudas.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/FR/description/".$bermudas.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/FR/br/".$bermudas.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/bermuda.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/DE/$bermudas.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/DE/liste/".$bermudas.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/DE/description/".$bermudas.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/DE/br/".$bermudas.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/bermuda.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/EN/$bermudas.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/EN/liste/".$bermudas.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/EN/description/".$bermudas.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/EN/br/".$bermudas.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/bermuda.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/IT/$bermudas.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/IT/liste/".$bermudas.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/IT/description/".$bermudas.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/IT/br/".$bermudas.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/bermuda.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/SP/$bermudas.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/SP/liste/".$bermudas.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/SP/description/".$bermudas.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/SP/br/".$bermudas.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		
		$lib = "Shorty" ;
		$desc = "Shorty" ;
		$img = "gif" ;
		$DB_site2->query("insert into categorie (parentid, libelleFR, descriptionFR, imageFR, libelleDE, descriptionDE, imageDE, libelleEN,
		descriptionEN, imageEN, libelleIT, descriptionIT, imageIT, libelleSP, descriptionSP, imageSP, onglet, visible_treeview) 
		values ('$shorts', '$lib', '$desc', '$img', '$lib', '$desc', '$img','$lib', '$desc', '$img','$lib', '$desc', '$img','$lib', 
		'$desc', '$img', '0', '1')");
		$shortys=$DB_site2->insert_id() ;
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/shorty.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/FR/$shortys.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/FR/liste/".$shortys.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/FR/description/".$shortys.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/FR/br/".$shortys.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/shorty.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/DE/$shortys.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/DE/liste/".$shortys.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/DE/description/".$shortys.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/DE/br/".$shortys.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/shorty.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/EN/$shortys.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/EN/liste/".$shortys.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/EN/description/".$shortys.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/EN/br/".$shortys.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/shorty.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/IT/$shortys.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/IT/liste/".$shortys.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/IT/description/".$shortys.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/IT/br/".$shortys.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/shorty.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/categories/SP/$shortys.$img";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination="/home/boutique/www/configurations/$domaine/images/categories/SP/liste/".$shortys.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_liste_largeur,$categ_liste_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/SP/description/".$shortys.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		$destination="/home/boutique/www/configurations/$domaine/images/categories/SP/br/".$shortys.".".$img;
		redimentionner_image($nom_fic,$destination,$categ_description_largeur,$categ_description_hauteur);
		
		// categorie_caracteristique
		$DB_site2->query("insert into categorie_caracteristique (catid, caractid, visible, filtrable) values ('$jeans', '$taille', '1', '1')");
		$DB_site2->query("insert into categorie_caracteristique (catid, caractid, visible, filtrable) values ('$jeans', '$couleur', '1', '1')");
		
		$DB_site2->query("insert into categorie_caracteristique (catid, caractid, visible, filtrable) values ('$pantalonstaillebasse', '$taille', '1', '1')");
		$DB_site2->query("insert into categorie_caracteristique (catid, caractid, visible, filtrable) values ('$pantalonstaillebasse', '$couleur', '1', '1')");
		
		$DB_site2->query("insert into categorie_caracteristique (catid, caractid, visible, filtrable) values ('$jupes', '$taille', '1', '1')");
		$DB_site2->query("insert into categorie_caracteristique (catid, caractid, visible, filtrable) values ('$jupes', '$couleur', '1', '1')");
		
		$DB_site2->query("insert into categorie_caracteristique (catid, caractid, visible, filtrable) values ('$tops', '$taille', '1', '1')");
		$DB_site2->query("insert into categorie_caracteristique (catid, caractid, visible, filtrable) values ('$tops', '$couleur', '1', '1')");
		
		// article
		$lib = "Maillot de bains" ;
		$type_fichier = "gif" ;
		$desc = "<p align=\"center\">Couleurs acidulées à l\'image de l\'été.</p>" ;
		$DB_site2->query("insert into article (artcode, catid, active, prix, tva, tauxchoisi, poid, fraisgestionid, image, legende,
		libelleFR, descriptionFR, libelleDE, descriptionDE, libelleEN, descriptionEN, libelleIT, descriptionIT, libelleSP, descriptionSP,
		upload_images, upload_images_oblig, upload_comm, upload_comm_oblig, commandable) values ('MB01', '$maillotsdebain', '1', '43', '19.6', '1', '150', '0', 
		'$type_fichier', 'Légende perso', '$lib', '$desc', '$lib', '$desc', '$lib', '$desc', '$lib', '$desc', '$lib', '$desc', '0', '0', '0', '0', '1')");
		$maillotdebains=$DB_site2->insert_id() ;
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/maillot2pieces.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/produits/$maillotdebains.$type_fichier";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination0="/home/boutique/www/configurations/$domaine/images/produits/article/$maillotdebains.$type_fichier";
		redimentionner_image($nom_fic,$destination0,$article_largeur,$article_hauteur);
		$destination1="/home/boutique/www/configurations/$domaine/images/produits/categorie/$maillotdebains.$type_fichier";
		redimentionner_image($nom_fic,$destination1,$categorie_largeur,$categorie_hauteur);
		$destination2="/home/boutique/www/configurations/$domaine/images/produits/cdc_categ/$maillotdebains.$type_fichier";
		redimentionner_image($nom_fic,$destination2,$cdc_categ_largeur,$cdc_categ_hauteur);
		$destination3="/home/boutique/www/configurations/$domaine/images/produits/cdc_index/$maillotdebains.$type_fichier";
		redimentionner_image($nom_fic,$destination3,$cdc_index_largeur,$cdc_index_hauteur);
		$destination4="/home/boutique/www/configurations/$domaine/images/produits/nouveautes_categ/$maillotdebains.$type_fichier";
		redimentionner_image($nom_fic,$destination4,$nouveautes_categ_largeur,$nouveates_categ_hauteur);
		$destination5="/home/boutique/www/configurations/$domaine/images/produits/nouveautes_index/$maillotdebains.$type_fichier";
		redimentionner_image($nom_fic,$destination5,$nouveautes_index_largeur,$nouveates_index_hauteur);
		$destination6="/home/boutique/www/configurations/$domaine/images/produits/promos_categ/$maillotdebains.$type_fichier";
		redimentionner_image($nom_fic,$destination6,$promos_categ_largeur,$promos_categ_hauteur);
		$destination7="/home/boutique/www/configurations/$domaine/images/produits/promos_index/$maillotdebains.$type_fichier";
		redimentionner_image($nom_fic,$destination7,$promos_index_largeur,$promos_index_hauteur);
		$destination8="/home/boutique/www/configurations/$domaine/images/produits/topventes_categ/$maillotdebains.$type_fichier";
		redimentionner_image($nom_fic,$destination8,$topventes_categ_largeur,$topventes_categ_hauteur);
		$destination9="/home/boutique/www/configurations/$domaine/images/produits/topventes_index/$maillotdebains.$type_fichier";
		redimentionner_image($nom_fic,$destination9,$topventes_index_largeur,$topventes_index_hauteur);
		$destination10="/home/boutique/www/configurations/$domaine/images/produits/br/$maillotdebains.$type_fichier";
		redimentionner_image($nom_fic,$destination10,$largeurimages,$hauteurimages);
		
		$lib = "Top manche courtes" ;
		$type_fichier = "gif" ;
		$desc = "<p align=\"center\">Dos nu décontracté pour la saison estivale.<br><bR>100% coton, lavable à 30°, tailles 34 à 52.</p>" ;
		$DB_site2->query("insert into article (artcode, catid, active, prix, tva, tauxchoisi, poid, fraisgestionid, image, legende,
		libelleFR, descriptionFR, libelleDE, descriptionDE, libelleEN, descriptionEN, libelleIT, descriptionIT, libelleSP, descriptionSP,
		upload_images, upload_images_oblig, upload_comm, upload_comm_oblig, commandable) values ('TMC01', '$tops', '1', '23', '19.6', '1', '150', '0', 
		'$type_fichier', 'Légende perso', '$lib', '$desc', '$lib', '$desc', '$lib', '$desc', '$lib', '$desc', '$lib', '$desc', '0', '0', '0', '0', '1')");
		$topmanchescourtes=$DB_site2->insert_id() ;
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/topmanchescourtes.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/produits/$topmanchescourtes.$type_fichier";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination0="/home/boutique/www/configurations/$domaine/images/produits/article/$topmanchescourtes.$type_fichier";
		redimentionner_image($nom_fic,$destination0,$article_largeur,$article_hauteur);
		$destination1="/home/boutique/www/configurations/$domaine/images/produits/categorie/$topmanchescourtes.$type_fichier";
		redimentionner_image($nom_fic,$destination1,$categorie_largeur,$categorie_hauteur);
		$destination2="/home/boutique/www/configurations/$domaine/images/produits/cdc_categ/$topmanchescourtes.$type_fichier";
		redimentionner_image($nom_fic,$destination2,$cdc_categ_largeur,$cdc_categ_hauteur);
		$destination3="/home/boutique/www/configurations/$domaine/images/produits/cdc_index/$topmanchescourtes.$type_fichier";
		redimentionner_image($nom_fic,$destination3,$cdc_index_largeur,$cdc_index_hauteur);
		$destination4="/home/boutique/www/configurations/$domaine/images/produits/nouveautes_categ/$topmanchescourtes.$type_fichier";
		redimentionner_image($nom_fic,$destination4,$nouveautes_categ_largeur,$nouveates_categ_hauteur);
		$destination5="/home/boutique/www/configurations/$domaine/images/produits/nouveautes_index/$topmanchescourtes.$type_fichier";
		redimentionner_image($nom_fic,$destination5,$nouveautes_index_largeur,$nouveates_index_hauteur);
		$destination6="/home/boutique/www/configurations/$domaine/images/produits/promos_categ/$topmanchescourtes.$type_fichier";
		redimentionner_image($nom_fic,$destination6,$promos_categ_largeur,$promos_categ_hauteur);
		$destination7="/home/boutique/www/configurations/$domaine/images/produits/promos_index/$topmanchescourtes.$type_fichier";
		redimentionner_image($nom_fic,$destination7,$promos_index_largeur,$promos_index_hauteur);
		$destination8="/home/boutique/www/configurations/$domaine/images/produits/topventes_categ/$topmanchescourtes.$type_fichier";
		redimentionner_image($nom_fic,$destination8,$topventes_categ_largeur,$topventes_categ_hauteur);
		$destination9="/home/boutique/www/configurations/$domaine/images/produits/topventes_index/$topmanchescourtes.$type_fichier";
		redimentionner_image($nom_fic,$destination9,$topventes_index_largeur,$topventes_index_hauteur);
		$destination10="/home/boutique/www/configurations/$domaine/images/produits/br/$topmanchescourtes.$type_fichier";
		redimentionner_image($nom_fic,$destination10,$largeurimages,$hauteurimages);
		
		$lib = "Jeans Levis 501" ;
		$type_fichier = "jpg" ;
		$desc = "<p align=\"center\">Super jean qui a déjà fait ses peurves...<br>Il est connu et porté dans le monde entier !</p>" ;
		$DB_site2->query("insert into article (artcode, catid, active, prix, tva, tauxchoisi, poid, fraisgestionid, image, legende,
		libelleFR, descriptionFR, libelleDE, descriptionDE, libelleEN, descriptionEN, libelleIT, descriptionIT, libelleSP, descriptionSP,
		upload_images, upload_images_oblig, upload_comm, upload_comm_oblig, commandable) values ('Ref01', '$jeans', '1', '123', '19.6', '1', '200', '0', 
		'$type_fichier', 'Légende perso', '$lib', '$desc', '$lib', '$desc', '$lib', '$desc', '$lib', '$desc', '$lib', '$desc', '0', '0', '0', '0', '1')");
		$jeanlevis501=$DB_site2->insert_id() ;
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/levis501.jpg";
		$destination0="/home/boutique/www/configurations/$domaine/images/produits/$jeanlevis501.$type_fichier";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination0="/home/boutique/www/configurations/$domaine/images/produits/article/$jeanlevis501.$type_fichier";
		redimentionner_image($nom_fic,$destination0,$article_largeur,$article_hauteur);
		$destination1="/home/boutique/www/configurations/$domaine/images/produits/categorie/$jeanlevis501.$type_fichier";
		redimentionner_image($nom_fic,$destination1,$categorie_largeur,$categorie_hauteur);
		$destination2="/home/boutique/www/configurations/$domaine/images/produits/cdc_categ/$jeanlevis501.$type_fichier";
		redimentionner_image($nom_fic,$destination2,$cdc_categ_largeur,$cdc_categ_hauteur);
		$destination3="/home/boutique/www/configurations/$domaine/images/produits/cdc_index/$jeanlevis501.$type_fichier";
		redimentionner_image($nom_fic,$destination3,$cdc_index_largeur,$cdc_index_hauteur);
		$destination4="/home/boutique/www/configurations/$domaine/images/produits/nouveautes_categ/$jeanlevis501.$type_fichier";
		redimentionner_image($nom_fic,$destination4,$nouveautes_categ_largeur,$nouveates_categ_hauteur);
		$destination5="/home/boutique/www/configurations/$domaine/images/produits/nouveautes_index/$jeanlevis501.$type_fichier";
		redimentionner_image($nom_fic,$destination5,$nouveautes_index_largeur,$nouveates_index_hauteur);
		$destination6="/home/boutique/www/configurations/$domaine/images/produits/promos_categ/$jeanlevis501.$type_fichier";
		redimentionner_image($nom_fic,$destination6,$promos_categ_largeur,$promos_categ_hauteur);
		$destination7="/home/boutique/www/configurations/$domaine/images/produits/promos_index/$jeanlevis501.$type_fichier";
		redimentionner_image($nom_fic,$destination7,$promos_index_largeur,$promos_index_hauteur);
		$destination8="/home/boutique/www/configurations/$domaine/images/produits/topventes_categ/$jeanlevis501.$type_fichier";
		redimentionner_image($nom_fic,$destination8,$topventes_categ_largeur,$topventes_categ_hauteur);
		$destination9="/home/boutique/www/configurations/$domaine/images/produits/topventes_index/$jeanlevis501.$type_fichier";
		redimentionner_image($nom_fic,$destination9,$topventes_index_largeur,$topventes_index_hauteur);
		$destination10="/home/boutique/www/configurations/$domaine/images/produits/br/$jeanlevis501.$type_fichier";
		redimentionner_image($nom_fic,$destination10,$largeurimages,$hauteurimages);
		
		$lib = "Jeans Lee Cooper" ;
		$type_fichier = "jpg" ;
		$desc = "<p align=\"center\">Jean très simple, tout ce qu\'il y a de plus basique.<br>Très agréble à porter.</p>" ;
		$DB_site2->query("insert into article (artcode, catid, active, prix, tva, tauxchoisi, poid, fraisgestionid, image, legende,
		libelleFR, descriptionFR, libelleDE, descriptionDE, libelleEN, descriptionEN, libelleIT, descriptionIT, libelleSP, descriptionSP,
		upload_images, upload_images_oblig, upload_comm, upload_comm_oblig, commandable) values ('Ref02', '$jeans', '1', '145', '19.6', '1', '200',
		'0', '$type_fichier', 'Légende perso', '$lib', '$desc', '$lib', '$desc', '$lib', '$desc', '$lib', '$desc', '$lib', '$desc', '0', '0', '0', '0', '1')");
		$jeanleecooper=$DB_site2->insert_id() ;
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/leecooper.jpg";
		$destination0="/home/boutique/www/configurations/$domaine/images/produits/$jeanleecooper.$type_fichier";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination0="/home/boutique/www/configurations/$domaine/images/produits/article/$jeanleecooper.$type_fichier";
		redimentionner_image($nom_fic,$destination0,$article_largeur,$article_hauteur);
		$destination1="/home/boutique/www/configurations/$domaine/images/produits/categorie/$jeanleecooper.$type_fichier";
		redimentionner_image($nom_fic,$destination1,$categorie_largeur,$categorie_hauteur);
		$destination2="/home/boutique/www/configurations/$domaine/images/produits/cdc_categ/$jeanleecooper.$type_fichier";
		redimentionner_image($nom_fic,$destination2,$cdc_categ_largeur,$cdc_categ_hauteur);
		$destination3="/home/boutique/www/configurations/$domaine/images/produits/cdc_index/$jeanleecooper.$type_fichier";
		redimentionner_image($nom_fic,$destination3,$cdc_index_largeur,$cdc_index_hauteur);
		$destination4="/home/boutique/www/configurations/$domaine/images/produits/nouveautes_categ/$jeanleecooper.$type_fichier";
		redimentionner_image($nom_fic,$destination4,$nouveautes_categ_largeur,$nouveates_categ_hauteur);
		$destination5="/home/boutique/www/configurations/$domaine/images/produits/nouveautes_index/$jeanleecooper.$type_fichier";
		redimentionner_image($nom_fic,$destination5,$nouveautes_index_largeur,$nouveates_index_hauteur);
		$destination6="/home/boutique/www/configurations/$domaine/images/produits/promos_categ/$jeanleecooper.$type_fichier";
		redimentionner_image($nom_fic,$destination6,$promos_categ_largeur,$promos_categ_hauteur);
		$destination7="/home/boutique/www/configurations/$domaine/images/produits/promos_index/$jeanleecooper.$type_fichier";
		redimentionner_image($nom_fic,$destination7,$promos_index_largeur,$promos_index_hauteur);
		$destination8="/home/boutique/www/configurations/$domaine/images/produits/topventes_categ/$jeanleecooper.$type_fichier";
		redimentionner_image($nom_fic,$destination8,$topventes_categ_largeur,$topventes_categ_hauteur);
		$destination9="/home/boutique/www/configurations/$domaine/images/produits/topventes_index/$jeanleecooper.$type_fichier";
		redimentionner_image($nom_fic,$destination9,$topventes_index_largeur,$topventes_index_hauteur);
		$destination10="/home/boutique/www/configurations/$domaine/images/produits/br/$jeanleecooper.$type_fichier";
		redimentionner_image($nom_fic,$destination10,$largeurimages,$hauteurimages);
				 
		$lib = "Taille basse" ;
		$type_fichier = "jpg" ;
		$desc = "<p align=\"center\">Pantalon taille basse très joli !!!</p>" ;
		$DB_site2->query("insert into article (artcode, catid, active, prix, tva, tauxchoisi, poid, fraisgestionid, image, legende,
		libelleFR, descriptionFR, libelleDE, descriptionDE, libelleEN, descriptionEN, libelleIT, descriptionIT, libelleSP, descriptionSP,
		upload_images, upload_images_oblig, upload_comm, upload_comm_oblig, commandable) values ('Ref03', '$pantalonstaillebasse', '1', '64', '19.6', '1', '400',
		'0', '$type_fichier', 'Légende perso', '$lib', '$desc', '$lib', '$desc', '$lib', '$desc', '$lib', '$desc', '$lib', '$desc', '0', '0', '0', '0', '1')");
		$taillebasse=$DB_site2->insert_id() ;
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/taillebasse.jpg";
		$destination0="/home/boutique/www/configurations/$domaine/images/produits/$taillebasse.$type_fichier";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination0="/home/boutique/www/configurations/$domaine/images/produits/article/$taillebasse.$type_fichier";
		redimentionner_image($nom_fic,$destination0,$article_largeur,$article_hauteur);
		$destination1="/home/boutique/www/configurations/$domaine/images/produits/categorie/$taillebasse.$type_fichier";
		redimentionner_image($nom_fic,$destination1,$categorie_largeur,$categorie_hauteur);
		$destination2="/home/boutique/www/configurations/$domaine/images/produits/cdc_categ/$taillebasse.$type_fichier";
		redimentionner_image($nom_fic,$destination2,$cdc_categ_largeur,$cdc_categ_hauteur);
		$destination3="/home/boutique/www/configurations/$domaine/images/produits/cdc_index/$taillebasse.$type_fichier";
		redimentionner_image($nom_fic,$destination3,$cdc_index_largeur,$cdc_index_hauteur);
		$destination4="/home/boutique/www/configurations/$domaine/images/produits/nouveautes_categ/$taillebasse.$type_fichier";
		redimentionner_image($nom_fic,$destination4,$nouveautes_categ_largeur,$nouveates_categ_hauteur);
		$destination5="/home/boutique/www/configurations/$domaine/images/produits/nouveautes_index/$taillebasse.$type_fichier";
		redimentionner_image($nom_fic,$destination5,$nouveautes_index_largeur,$nouveates_index_hauteur);
		$destination6="/home/boutique/www/configurations/$domaine/images/produits/promos_categ/$taillebasse.$type_fichier";
		redimentionner_image($nom_fic,$destination6,$promos_categ_largeur,$promos_categ_hauteur);
		$destination7="/home/boutique/www/configurations/$domaine/images/produits/promos_index/$taillebasse.$type_fichier";
		redimentionner_image($nom_fic,$destination7,$promos_index_largeur,$promos_index_hauteur);
		$destination8="/home/boutique/www/configurations/$domaine/images/produits/topventes_categ/$taillebasse.$type_fichier";
		redimentionner_image($nom_fic,$destination8,$topventes_categ_largeur,$topventes_categ_hauteur);
		$destination9="/home/boutique/www/configurations/$domaine/images/produits/topventes_index/$taillebasse.$type_fichier";
		redimentionner_image($nom_fic,$destination9,$topventes_index_largeur,$topventes_index_hauteur);
		$destination10="/home/boutique/www/configurations/$domaine/images/produits/br/$taillebasse.$type_fichier";
		redimentionner_image($nom_fic,$destination10,$largeurimages,$hauteurimages);
	
		$lib = "Mini-jupe" ;
		$type_fichier = "jpg" ;
		$desc = "<p align=\"center\">Super sexy !!!</p>" ;
		$DB_site2->query("insert into article (artcode, catid, active, prix, tva, tauxchoisi, poid, fraisgestionid, image, legende,
		libelleFR, descriptionFR, libelleDE, descriptionDE, libelleEN, descriptionEN, libelleIT, descriptionIT, libelleSP, descriptionSP,
		upload_images, upload_images_oblig, upload_comm, upload_comm_oblig, commandable) values ('Ref04', '$jupes', '1', '31', '19.6', '1', '400',
		'0', '$type_fichier', 'Légende perso', '$lib', '$desc', '$lib', '$desc', '$lib', '$desc', '$lib', '$desc', '$lib', '$desc', '0', '0', '0', '0', '1')");
		$minijupe=$DB_site2->insert_id() ;
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/minijupe.jpg";
		$destination0="/home/boutique/www/configurations/$domaine/images/produits/$minijupe.$type_fichier";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination0="/home/boutique/www/configurations/$domaine/images/produits/article/$minijupe.$type_fichier";
		redimentionner_image($nom_fic,$destination0,$article_largeur,$article_hauteur);
		$destination1="/home/boutique/www/configurations/$domaine/images/produits/categorie/$minijupe.$type_fichier";
		redimentionner_image($nom_fic,$destination1,$categorie_largeur,$categorie_hauteur);
		$destination2="/home/boutique/www/configurations/$domaine/images/produits/cdc_categ/$minijupe.$type_fichier";
		redimentionner_image($nom_fic,$destination2,$cdc_categ_largeur,$cdc_categ_hauteur);
		$destination3="/home/boutique/www/configurations/$domaine/images/produits/cdc_index/$minijupe.$type_fichier";
		redimentionner_image($nom_fic,$destination3,$cdc_index_largeur,$cdc_index_hauteur);
		$destination4="/home/boutique/www/configurations/$domaine/images/produits/nouveautes_categ/$minijupe.$type_fichier";
		redimentionner_image($nom_fic,$destination4,$nouveautes_categ_largeur,$nouveates_categ_hauteur);
		$destination5="/home/boutique/www/configurations/$domaine/images/produits/nouveautes_index/$minijupe.$type_fichier";
		redimentionner_image($nom_fic,$destination5,$nouveautes_index_largeur,$nouveates_index_hauteur);
		$destination6="/home/boutique/www/configurations/$domaine/images/produits/promos_categ/$minijupe.$type_fichier";
		redimentionner_image($nom_fic,$destination6,$promos_categ_largeur,$promos_categ_hauteur);
		$destination7="/home/boutique/www/configurations/$domaine/images/produits/promos_index/$minijupe.$type_fichier";
		redimentionner_image($nom_fic,$destination7,$promos_index_largeur,$promos_index_hauteur);
		$destination8="/home/boutique/www/configurations/$domaine/images/produits/topventes_categ/$minijupe.$type_fichier";
		redimentionner_image($nom_fic,$destination8,$topventes_categ_largeur,$topventes_categ_hauteur);
		$destination9="/home/boutique/www/configurations/$domaine/images/produits/topventes_index/$minijupe.$type_fichier";
		redimentionner_image($nom_fic,$destination9,$topventes_index_largeur,$topventes_index_hauteur);
		$destination10="/home/boutique/www/configurations/$domaine/images/produits/br/$minijupe.$type_fichier";
		redimentionner_image($nom_fic,$destination10,$largeurimages,$hauteurimages);
	
		$lib = "Cardigan" ;
		$type_fichier = "jpg" ;
		$desc = "<p align=\"center\">Très pratique pour l\'été et en + au top de la mode !!!</p>" ;
		$DB_site2->query("insert into article (artcode, catid, active, prix, tva, tauxchoisi, poid, fraisgestionid, image, legende,
		libelleFR, descriptionFR, libelleDE, descriptionDE, libelleEN, descriptionEN, libelleIT, descriptionIT, libelleSP, descriptionSP,
		upload_images, upload_images_oblig, upload_comm, upload_comm_oblig, commandable) values ('Ref05', '$cardigans', '1', '74', '19.6', '1', '600',
		'0', '$type_fichier', 'Légende perso', '$lib', '$desc', '$lib', '$desc', '$lib', '$desc', '$lib', '$desc', '$lib', '$desc', '0', '0', '0', '0', '1')");
		$cardigan=$DB_site2->insert_id() ;
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/cardigan.jpg";
		$destination0="/home/boutique/www/configurations/$domaine/images/produits/$cardigan.$type_fichier";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination0="/home/boutique/www/configurations/$domaine/images/produits/article/$cardigan.$type_fichier";
		redimentionner_image($nom_fic,$destination0,$article_largeur,$article_hauteur);
		$destination1="/home/boutique/www/configurations/$domaine/images/produits/categorie/$cardigan.$type_fichier";
		redimentionner_image($nom_fic,$destination1,$categorie_largeur,$categorie_hauteur);
		$destination2="/home/boutique/www/configurations/$domaine/images/produits/cdc_categ/$cardigan.$type_fichier";
		redimentionner_image($nom_fic,$destination2,$cdc_categ_largeur,$cdc_categ_hauteur);
		$destination3="/home/boutique/www/configurations/$domaine/images/produits/cdc_index/$cardigan.$type_fichier";
		redimentionner_image($nom_fic,$destination3,$cdc_index_largeur,$cdc_index_hauteur);
		$destination4="/home/boutique/www/configurations/$domaine/images/produits/nouveautes_categ/$cardigan.$type_fichier";
		redimentionner_image($nom_fic,$destination4,$nouveautes_categ_largeur,$nouveates_categ_hauteur);
		$destination5="/home/boutique/www/configurations/$domaine/images/produits/nouveautes_index/$cardigan.$type_fichier";
		redimentionner_image($nom_fic,$destination5,$nouveautes_index_largeur,$nouveates_index_hauteur);
		$destination6="/home/boutique/www/configurations/$domaine/images/produits/promos_categ/$cardigan.$type_fichier";
		redimentionner_image($nom_fic,$destination6,$promos_categ_largeur,$promos_categ_hauteur);
		$destination7="/home/boutique/www/configurations/$domaine/images/produits/promos_index/$cardigan.$type_fichier";
		redimentionner_image($nom_fic,$destination7,$promos_index_largeur,$promos_index_hauteur);
		$destination8="/home/boutique/www/configurations/$domaine/images/produits/topventes_categ/$cardigan.$type_fichier";
		redimentionner_image($nom_fic,$destination8,$topventes_categ_largeur,$topventes_categ_hauteur);
		$destination9="/home/boutique/www/configurations/$domaine/images/produits/topventes_index/$cardigan.$type_fichier";
		redimentionner_image($nom_fic,$destination9,$topventes_index_largeur,$topventes_index_hauteur);
		$destination10="/home/boutique/www/configurations/$domaine/images/produits/br/$cardigan.$type_fichier";
		redimentionner_image($nom_fic,$destination10,$largeurimages,$hauteurimages);
	
		$lib = "Veste de tailleur" ;
		$type_fichier = "gif" ;
		$desc = "<p align=\"center\">Très pratique pour l\'été et en + au top de la mode !!!</p>" ;
		$DB_site2->query("insert into article (artcode, catid, active, prix, tva, tauxchoisi, poid, fraisgestionid, image, legende,
		libelleFR, descriptionFR, libelleDE, descriptionDE, libelleEN, descriptionEN, libelleIT, descriptionIT, libelleSP, descriptionSP,
		upload_images, upload_images_oblig, upload_comm, upload_comm_oblig, commandable) values ('Ref05', '$vestestailleurs', '1', '74', '19.6', '1', '600',
		'0', '$type_fichier', 'Légende perso', '$lib', '$desc', '$lib', '$desc', '$lib', '$desc', '$lib', '$desc', '$lib', '$desc', '0', '0', '0', '0', '1')");
		$vestetaill=$DB_site2->insert_id() ;
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/vestetailleur.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/produits/$vestetaill.$type_fichier";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination0="/home/boutique/www/configurations/$domaine/images/produits/article/$vestetaill.$type_fichier";
		redimentionner_image($nom_fic,$destination0,$article_largeur,$article_hauteur);
		$destination1="/home/boutique/www/configurations/$domaine/images/produits/categorie/$vestetaill.$type_fichier";
		redimentionner_image($nom_fic,$destination1,$categorie_largeur,$categorie_hauteur);
		$destination2="/home/boutique/www/configurations/$domaine/images/produits/cdc_categ/$vestetaill.$type_fichier";
		redimentionner_image($nom_fic,$destination2,$cdc_categ_largeur,$cdc_categ_hauteur);
		$destination3="/home/boutique/www/configurations/$domaine/images/produits/cdc_index/$vestetaill.$type_fichier";
		redimentionner_image($nom_fic,$destination3,$cdc_index_largeur,$cdc_index_hauteur);
		$destination4="/home/boutique/www/configurations/$domaine/images/produits/nouveautes_categ/$vestetaill.$type_fichier";
		redimentionner_image($nom_fic,$destination4,$nouveautes_categ_largeur,$nouveates_categ_hauteur);
		$destination5="/home/boutique/www/configurations/$domaine/images/produits/nouveautes_index/$vestetaill.$type_fichier";
		redimentionner_image($nom_fic,$destination5,$nouveautes_index_largeur,$nouveates_index_hauteur);
		$destination6="/home/boutique/www/configurations/$domaine/images/produits/promos_categ/$vestetaill.$type_fichier";
		redimentionner_image($nom_fic,$destination6,$promos_categ_largeur,$promos_categ_hauteur);
		$destination7="/home/boutique/www/configurations/$domaine/images/produits/promos_index/$vestetaill.$type_fichier";
		redimentionner_image($nom_fic,$destination7,$promos_index_largeur,$promos_index_hauteur);
		$destination8="/home/boutique/www/configurations/$domaine/images/produits/topventes_categ/$vestetaill.$type_fichier";
		redimentionner_image($nom_fic,$destination8,$topventes_categ_largeur,$topventes_categ_hauteur);
		$destination9="/home/boutique/www/configurations/$domaine/images/produits/topventes_index/$vestetaill.$type_fichier";
		redimentionner_image($nom_fic,$destination9,$topventes_index_largeur,$topventes_index_hauteur);
		$destination10="/home/boutique/www/configurations/$domaine/images/produits/br/$vestetaill.$type_fichier";
		redimentionner_image($nom_fic,$destination10,$largeurimages,$hauteurimages);
	
		$lib = "Veste en lin" ;
		$type_fichier = "gif" ;
		$desc = "<p align=\"center\">Confortable à toutes occasions</p>" ;
		$DB_site2->query("insert into article (artcode, catid, active, prix, tva, tauxchoisi, poid, fraisgestionid, image, legende,
		libelleFR, descriptionFR, libelleDE, descriptionDE, libelleEN, descriptionEN, libelleIT, descriptionIT, libelleSP, descriptionSP,
		upload_images, upload_images_oblig, upload_comm, upload_comm_oblig, commandable) values ('Ref05', '$autresvestes', '1', '74', '19.6', '1', '600',
		'0', '$type_fichier', 'Légende perso', '$lib', '$desc', '$lib', '$desc', '$lib', '$desc', '$lib', '$desc', '$lib', '$desc', '0', '0', '0', '0', '1')");
		$vesteenlin=$DB_site2->insert_id() ;
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/vestelin.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/produits/$vesteenlin.$type_fichier";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination0="/home/boutique/www/configurations/$domaine/images/produits/article/$vesteenlin.$type_fichier";
		redimentionner_image($nom_fic,$destination0,$article_largeur,$article_hauteur);
		$destination1="/home/boutique/www/configurations/$domaine/images/produits/categorie/$vesteenlin.$type_fichier";
		redimentionner_image($nom_fic,$destination1,$categorie_largeur,$categorie_hauteur);
		$destination2="/home/boutique/www/configurations/$domaine/images/produits/cdc_categ/$vesteenlin.$type_fichier";
		redimentionner_image($nom_fic,$destination2,$cdc_categ_largeur,$cdc_categ_hauteur);
		$destination3="/home/boutique/www/configurations/$domaine/images/produits/cdc_index/$vesteenlin.$type_fichier";
		redimentionner_image($nom_fic,$destination3,$cdc_index_largeur,$cdc_index_hauteur);
		$destination4="/home/boutique/www/configurations/$domaine/images/produits/nouveautes_categ/$vesteenlin.$type_fichier";
		redimentionner_image($nom_fic,$destination4,$nouveautes_categ_largeur,$nouveates_categ_hauteur);
		$destination5="/home/boutique/www/configurations/$domaine/images/produits/nouveautes_index/$vesteenlin.$type_fichier";
		redimentionner_image($nom_fic,$destination5,$nouveautes_index_largeur,$nouveates_index_hauteur);
		$destination6="/home/boutique/www/configurations/$domaine/images/produits/promos_categ/$vesteenlin.$type_fichier";
		redimentionner_image($nom_fic,$destination6,$promos_categ_largeur,$promos_categ_hauteur);
		$destination7="/home/boutique/www/configurations/$domaine/images/produits/promos_index/$vesteenlin.$type_fichier";
		redimentionner_image($nom_fic,$destination7,$promos_index_largeur,$promos_index_hauteur);
		$destination8="/home/boutique/www/configurations/$domaine/images/produits/topventes_categ/$vesteenlin.$type_fichier";
		redimentionner_image($nom_fic,$destination8,$topventes_categ_largeur,$topventes_categ_hauteur);
		$destination9="/home/boutique/www/configurations/$domaine/images/produits/topventes_index/$vesteenlin.$type_fichier";
		redimentionner_image($nom_fic,$destination9,$topventes_index_largeur,$topventes_index_hauteur);
		$destination10="/home/boutique/www/configurations/$domaine/images/produits/br/$vesteenlin.$type_fichier";
		redimentionner_image($nom_fic,$destination10,$largeurimages,$hauteurimages);
	
		$lib = "Veste blanche" ;
		$type_fichier = "gif" ;
		$desc = "<p align=\"center\">Soie et coton</p>" ;
		$DB_site2->query("insert into article (artcode, catid, active, prix, tva, tauxchoisi, poid, fraisgestionid, image, legende,
		libelleFR, descriptionFR, libelleDE, descriptionDE, libelleEN, descriptionEN, libelleIT, descriptionIT, libelleSP, descriptionSP,
		upload_images, upload_images_oblig, upload_comm, upload_comm_oblig, commandable) values ('Ref05', '$autresvestes', '1', '47', '19.6', '1', '600',
		'0', '$type_fichier', 'Légende perso', '$lib', '$desc', '$lib', '$desc', '$lib', '$desc', '$lib', '$desc', '$lib', '$desc', '0', '0', '0', '0', '1')");
		$vesteblanche=$DB_site2->insert_id() ;
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/vesteblanche.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/produits/$vesteblanche.$type_fichier";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination0="/home/boutique/www/configurations/$domaine/images/produits/article/$vesteblanche.$type_fichier";
		redimentionner_image($nom_fic,$destination0,$article_largeur,$article_hauteur);
		$destination1="/home/boutique/www/configurations/$domaine/images/produits/categorie/$vesteblanche.$type_fichier";
		redimentionner_image($nom_fic,$destination1,$categorie_largeur,$categorie_hauteur);
		$destination2="/home/boutique/www/configurations/$domaine/images/produits/cdc_categ/$vesteblanche.$type_fichier";
		redimentionner_image($nom_fic,$destination2,$cdc_categ_largeur,$cdc_categ_hauteur);
		$destination3="/home/boutique/www/configurations/$domaine/images/produits/cdc_index/$vesteblanche.$type_fichier";
		redimentionner_image($nom_fic,$destination3,$cdc_index_largeur,$cdc_index_hauteur);
		$destination4="/home/boutique/www/configurations/$domaine/images/produits/nouveautes_categ/$vesteblanche.$type_fichier";
		redimentionner_image($nom_fic,$destination4,$nouveautes_categ_largeur,$nouveates_categ_hauteur);
		$destination5="/home/boutique/www/configurations/$domaine/images/produits/nouveautes_index/$vesteblanche.$type_fichier";
		redimentionner_image($nom_fic,$destination5,$nouveautes_index_largeur,$nouveates_index_hauteur);
		$destination6="/home/boutique/www/configurations/$domaine/images/produits/promos_categ/$vesteblanche.$type_fichier";
		redimentionner_image($nom_fic,$destination6,$promos_categ_largeur,$promos_categ_hauteur);
		$destination7="/home/boutique/www/configurations/$domaine/images/produits/promos_index/$vesteblanche.$type_fichier";
		redimentionner_image($nom_fic,$destination7,$promos_index_largeur,$promos_index_hauteur);
		$destination8="/home/boutique/www/configurations/$domaine/images/produits/topventes_categ/$vesteblanche.$type_fichier";
		redimentionner_image($nom_fic,$destination8,$topventes_categ_largeur,$topventes_categ_hauteur);
		$destination9="/home/boutique/www/configurations/$domaine/images/produits/topventes_index/$vesteblanche.$type_fichier";
		redimentionner_image($nom_fic,$destination9,$topventes_index_largeur,$topventes_index_hauteur);
		$destination10="/home/boutique/www/configurations/$domaine/images/produits/br/$vesteblanche.$type_fichier";
		redimentionner_image($nom_fic,$destination10,$largeurimages,$hauteurimages);
	
		$lib = "Bermuda" ;
		$type_fichier = "gif" ;
		$desc = "<p align=\"center\">Décontracté et mode à la fois.</p>" ;
		$DB_site2->query("insert into article (artcode, catid, active, prix, tva, tauxchoisi, poid, fraisgestionid, image, legende,
		libelleFR, descriptionFR, libelleDE, descriptionDE, libelleEN, descriptionEN, libelleIT, descriptionIT, libelleSP, descriptionSP,
		upload_images, upload_images_oblig, upload_comm, upload_comm_oblig, commandable) values ('Ref05', '$bermudas', '1', '47', '19.6', '1', '600',
		'0', '$type_fichier', 'Légende perso', '$lib', '$desc', '$lib', '$desc', '$lib', '$desc', '$lib', '$desc', '$lib', '$desc', '0', '0', '0', '0', '1')");
		$bermud=$DB_site2->insert_id() ;
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/bermuda.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/produits/$bermud.$type_fichier";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination0="/home/boutique/www/configurations/$domaine/images/produits/article/$bermud.$type_fichier";
		redimentionner_image($nom_fic,$destination0,$article_largeur,$article_hauteur);
		$destination1="/home/boutique/www/configurations/$domaine/images/produits/categorie/$bermud.$type_fichier";
		redimentionner_image($nom_fic,$destination1,$categorie_largeur,$categorie_hauteur);
		$destination2="/home/boutique/www/configurations/$domaine/images/produits/cdc_categ/$bermud.$type_fichier";
		redimentionner_image($nom_fic,$destination2,$cdc_categ_largeur,$cdc_categ_hauteur);
		$destination3="/home/boutique/www/configurations/$domaine/images/produits/cdc_index/$bermud.$type_fichier";
		redimentionner_image($nom_fic,$destination3,$cdc_index_largeur,$cdc_index_hauteur);
		$destination4="/home/boutique/www/configurations/$domaine/images/produits/nouveautes_categ/$bermud.$type_fichier";
		redimentionner_image($nom_fic,$destination4,$nouveautes_categ_largeur,$nouveates_categ_hauteur);
		$destination5="/home/boutique/www/configurations/$domaine/images/produits/nouveautes_index/$bermud.$type_fichier";
		redimentionner_image($nom_fic,$destination5,$nouveautes_index_largeur,$nouveates_index_hauteur);
		$destination6="/home/boutique/www/configurations/$domaine/images/produits/promos_categ/$bermud.$type_fichier";
		redimentionner_image($nom_fic,$destination6,$promos_categ_largeur,$promos_categ_hauteur);
		$destination7="/home/boutique/www/configurations/$domaine/images/produits/promos_index/$bermud.$type_fichier";
		redimentionner_image($nom_fic,$destination7,$promos_index_largeur,$promos_index_hauteur);
		$destination8="/home/boutique/www/configurations/$domaine/images/produits/topventes_categ/$bermud.$type_fichier";
		redimentionner_image($nom_fic,$destination8,$topventes_categ_largeur,$topventes_categ_hauteur);
		$destination9="/home/boutique/www/configurations/$domaine/images/produits/topventes_index/$bermud.$type_fichier";
		redimentionner_image($nom_fic,$destination9,$topventes_index_largeur,$topventes_index_hauteur);
		$destination10="/home/boutique/www/configurations/$domaine/images/produits/br/$bermud.$type_fichier";
		redimentionner_image($nom_fic,$destination10,$largeurimages,$hauteurimages);
	
		$lib = "Shorty" ;
		$type_fichier = "gif" ;
		$desc = "<p align=\"center\">Sportif et sexy</p>" ;
		$DB_site2->query("insert into article (artcode, catid, active, prix, tva, tauxchoisi, poid, fraisgestionid, image, legende,
		libelleFR, descriptionFR, libelleDE, descriptionDE, libelleEN, descriptionEN, libelleIT, descriptionIT, libelleSP, descriptionSP,
		upload_images, upload_images_oblig, upload_comm, upload_comm_oblig, commandable) values ('Ref05', '$shortys', '1', '47', '19.6', '1', '600',
		'0', '$type_fichier', 'Légende perso', '$lib', '$desc', '$lib', '$desc', '$lib', '$desc', '$lib', '$desc', '$lib', '$desc', '0', '0', '0', '0', '1')");
		$shorty=$DB_site2->insert_id() ;
		$nom_fic="/home/boutique/www/configurations/$domaine/images/template/shorty.gif";
		$destination0="/home/boutique/www/configurations/$domaine/images/produits/$shorty.$type_fichier";
		redimentionner_image($nom_fic,$destination0,'10000','10000');
		$destination0="/home/boutique/www/configurations/$domaine/images/produits/article/$shorty.$type_fichier";
		redimentionner_image($nom_fic,$destination0,$article_largeur,$article_hauteur);
		$destination1="/home/boutique/www/configurations/$domaine/images/produits/categorie/$shorty.$type_fichier";
		redimentionner_image($nom_fic,$destination1,$categorie_largeur,$categorie_hauteur);
		$destination2="/home/boutique/www/configurations/$domaine/images/produits/cdc_categ/$shorty.$type_fichier";
		redimentionner_image($nom_fic,$destination2,$cdc_categ_largeur,$cdc_categ_hauteur);
		$destination3="/home/boutique/www/configurations/$domaine/images/produits/cdc_index/$shorty.$type_fichier";
		redimentionner_image($nom_fic,$destination3,$cdc_index_largeur,$cdc_index_hauteur);
		$destination4="/home/boutique/www/configurations/$domaine/images/produits/nouveautes_categ/$shorty.$type_fichier";
		redimentionner_image($nom_fic,$destination4,$nouveautes_categ_largeur,$nouveates_categ_hauteur);
		$destination5="/home/boutique/www/configurations/$domaine/images/produits/nouveautes_index/$shorty.$type_fichier";
		redimentionner_image($nom_fic,$destination5,$nouveautes_index_largeur,$nouveates_index_hauteur);
		$destination6="/home/boutique/www/configurations/$domaine/images/produits/promos_categ/$shorty.$type_fichier";
		redimentionner_image($nom_fic,$destination6,$promos_categ_largeur,$promos_categ_hauteur);
		$destination7="/home/boutique/www/configurations/$domaine/images/produits/promos_index/$shorty.$type_fichier";
		redimentionner_image($nom_fic,$destination7,$promos_index_largeur,$promos_index_hauteur);
		$destination8="/home/boutique/www/configurations/$domaine/images/produits/topventes_categ/$shorty.$type_fichier";
		redimentionner_image($nom_fic,$destination8,$topventes_categ_largeur,$topventes_categ_hauteur);
		$destination9="/home/boutique/www/configurations/$domaine/images/produits/topventes_index/$shorty.$type_fichier";
		redimentionner_image($nom_fic,$destination9,$topventes_index_largeur,$topventes_index_hauteur);
		$destination10="/home/boutique/www/configurations/$domaine/images/produits/br/$shorty.$type_fichier";
		redimentionner_image($nom_fic,$destination10,$largeurimages,$hauteurimages);
	
		// article_marque
		$DB_site2->query("insert into article_marque (artid, marqueid) values ('$jeanlevis501', '$levis')");
		
		$DB_site2->query("insert into article_marque (artid, marqueid) values ('$jeanleecooper', '$leecooper')");
	   
		// articlephoto
		$DB_site2->query("insert into articlephoto (artid, image, legende) values ('$jeanlevis501', 'jpg', 'Légende supl.')");
		$insertphoto=$DB_site2->insert_id() ;
		$nom_fic2="/home/boutique/www/configurations/$domaine/images/template/levis501sup.jpg";
		$destination0="/home/boutique/www/configurations/$domaine/images/produits/".$jeanlevis501."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination0,'10000','10000');
		$destination0="/home/boutique/www/configurations/$domaine/images/produits/article/".$jeanlevis501."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination0,$article_largeur,$article_hauteur);
		$destination1="/home/boutique/www/configurations/$domaine/images/produits/categorie/".$jeanlevis501."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination1,$categorie_largeur,$categorie_hauteur);
		$destination2="/home/boutique/www/configurations/$domaine/images/produits/cdc_categ/".$jeanlevis501."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination2,$cdc_categ_largeur,$cdc_categ_hauteur);
		$destination3="/home/boutique/www/configurations/$domaine/images/produits/cdc_index/".$jeanlevis501."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination3,$cdc_index_largeur,$cdc_index_hauteur);
		$destination4="/home/boutique/www/configurations/$domaine/images/produits/nouveautes_categ/".$jeanlevis501."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination4,$nouveautes_categ_largeur,$nouveates_categ_hauteur);
		$destination5="/home/boutique/www/configurations/$domaine/images/produits/nouveautes_index/".$jeanlevis501."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination5,$nouveautes_index_largeur,$nouveates_index_hauteur);
		$destination6="/home/boutique/www/configurations/$domaine/images/produits/promos_categ/".$jeanlevis501."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination6,$promos_categ_largeur,$promos_categ_hauteur);
		$destination7="/home/boutique/www/configurations/$domaine/images/produits/promos_index/".$jeanlevis501."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination7,$promos_index_largeur,$promos_index_hauteur);
		$destination8="/home/boutique/www/configurations/$domaine/images/produits/topventes_categ/".$jeanlevis501."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination8,$topventes_categ_largeur,$topventes_categ_hauteur);
		$destination9="/home/boutique/www/configurations/$domaine/images/produits/topventes_index/".$jeanlevis501."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination9,$topventes_index_largeur,$topventes_index_hauteur);
		$destination10="/home/boutique/www/configurations/$domaine/images/produits/br/".$jeanlevis501."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination10,$largeurimages,$hauteurimages);
		
		$DB_site2->query("insert into articlephoto (artid, image, legende) values ('$jeanleecooper', 'jpg', 'Légende supl. 1')");
		$insertphoto=$DB_site2->insert_id() ;
		$nom_fic2="/home/boutique/www/configurations/$domaine/images/template/leecoopersup.jpg";
		$destination0="/home/boutique/www/configurations/$domaine/images/produits/".$jeanleecooper."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination0,'10000','10000');
		$destination0="/home/boutique/www/configurations/$domaine/images/produits/article/".$jeanleecooper."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination0,$article_largeur,$article_hauteur);
		$destination1="/home/boutique/www/configurations/$domaine/images/produits/categorie/".$jeanleecooper."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination1,$categorie_largeur,$categorie_hauteur);
		$destination2="/home/boutique/www/configurations/$domaine/images/produits/cdc_categ/".$jeanleecooper."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination2,$cdc_categ_largeur,$cdc_categ_hauteur);
		$destination3="/home/boutique/www/configurations/$domaine/images/produits/cdc_index/".$jeanleecooper."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination3,$cdc_index_largeur,$cdc_index_hauteur);
		$destination4="/home/boutique/www/configurations/$domaine/images/produits/nouveautes_categ/".$jeanleecooper."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination4,$nouveautes_categ_largeur,$nouveates_categ_hauteur);
		$destination5="/home/boutique/www/configurations/$domaine/images/produits/nouveautes_index/".$jeanleecooper."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination5,$nouveautes_index_largeur,$nouveates_index_hauteur);
		$destination6="/home/boutique/www/configurations/$domaine/images/produits/promos_categ/".$jeanleecooper."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination6,$promos_categ_largeur,$promos_categ_hauteur);
		$destination7="/home/boutique/www/configurations/$domaine/images/produits/promos_index/".$jeanleecooper."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination7,$promos_index_largeur,$promos_index_hauteur);
		$destination8="/home/boutique/www/configurations/$domaine/images/produits/topventes_categ/".$jeanleecooper."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination8,$topventes_categ_largeur,$topventes_categ_hauteur);
		$destination9="/home/boutique/www/configurations/$domaine/images/produits/topventes_index/".$jeanleecooper."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination9,$topventes_index_largeur,$topventes_index_hauteur);
		$destination10="/home/boutique/www/configurations/$domaine/images/produits/br/".$jeanleecooper."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination10,$largeurimages,$hauteurimages);
		$DB_site2->query("insert into articlephoto (artid, image, legende) values ('$jeanleecooper', 'jpg', 'Légende supl. 2')");
		$insertphoto=$DB_site2->insert_id() ;
		$nom_fic2="/home/boutique/www/configurations/$domaine/images/template/leecoopersupl.jpg";
		$destination0="/home/boutique/www/configurations/$domaine/images/produits/".$jeanleecooper."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination0,'10000','10000');
		$destination0="/home/boutique/www/configurations/$domaine/images/produits/article/".$jeanleecooper."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination0,$article_largeur,$article_hauteur);
		$destination1="/home/boutique/www/configurations/$domaine/images/produits/categorie/".$jeanleecooper."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination1,$categorie_largeur,$categorie_hauteur);
		$destination2="/home/boutique/www/configurations/$domaine/images/produits/cdc_categ/".$jeanleecooper."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination2,$cdc_categ_largeur,$cdc_categ_hauteur);
		$destination3="/home/boutique/www/configurations/$domaine/images/produits/cdc_index/".$jeanleecooper."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination3,$cdc_index_largeur,$cdc_index_hauteur);
		$destination4="/home/boutique/www/configurations/$domaine/images/produits/nouveautes_categ/".$jeanleecooper."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination4,$nouveautes_categ_largeur,$nouveates_categ_hauteur);
		$destination5="/home/boutique/www/configurations/$domaine/images/produits/nouveautes_index/".$jeanleecooper."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination5,$nouveautes_index_largeur,$nouveates_index_hauteur);
		$destination6="/home/boutique/www/configurations/$domaine/images/produits/promos_categ/".$jeanleecooper."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination6,$promos_categ_largeur,$promos_categ_hauteur);
		$destination7="/home/boutique/www/configurations/$domaine/images/produits/promos_index/".$jeanleecooper."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination7,$promos_index_largeur,$promos_index_hauteur);
		$destination8="/home/boutique/www/configurations/$domaine/images/produits/topventes_categ/".$jeanleecooper."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination8,$topventes_categ_largeur,$topventes_categ_hauteur);
		$destination9="/home/boutique/www/configurations/$domaine/images/produits/topventes_index/".$jeanleecooper."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination9,$topventes_index_largeur,$topventes_index_hauteur);
		$destination10="/home/boutique/www/configurations/$domaine/images/produits/br/".$jeanleecooper."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination10,$largeurimages,$hauteurimages);
		
		$DB_site2->query("insert into articlephoto (artid, image, legende) values ('$cardigan', 'jpg', 'Légende supl. 1')");
		$insertphoto=$DB_site2->insert_id() ;
		$nom_fic2="/home/boutique/www/configurations/$domaine/images/template/cardigansup.jpg";
		$destination0="/home/boutique/www/configurations/$domaine/images/produits/".$cardigan."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination0,'10000','10000');
		$destination0="/home/boutique/www/configurations/$domaine/images/produits/article/".$cardigan."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination0,$article_largeur,$article_hauteur);
		$destination1="/home/boutique/www/configurations/$domaine/images/produits/categorie/".$cardigan."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination1,$categorie_largeur,$categorie_hauteur);
		$destination2="/home/boutique/www/configurations/$domaine/images/produits/cdc_categ/".$cardigan."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination2,$cdc_categ_largeur,$cdc_categ_hauteur);
		$destination3="/home/boutique/www/configurations/$domaine/images/produits/cdc_index/".$cardigan."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination3,$cdc_index_largeur,$cdc_index_hauteur);
		$destination4="/home/boutique/www/configurations/$domaine/images/produits/nouveautes_categ/".$cardigan."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination4,$nouveautes_categ_largeur,$nouveates_categ_hauteur);
		$destination5="/home/boutique/www/configurations/$domaine/images/produits/nouveautes_index/".$cardigan."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination5,$nouveautes_index_largeur,$nouveates_index_hauteur);
		$destination6="/home/boutique/www/configurations/$domaine/images/produits/promos_categ/".$cardigan."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination6,$promos_categ_largeur,$promos_categ_hauteur);
		$destination7="/home/boutique/www/configurations/$domaine/images/produits/promos_index/".$cardigan."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination7,$promos_index_largeur,$promos_index_hauteur);
		$destination8="/home/boutique/www/configurations/$domaine/images/produits/topventes_categ/".$cardigan."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination8,$topventes_categ_largeur,$topventes_categ_hauteur);
		$destination9="/home/boutique/www/configurations/$domaine/images/produits/topventes_index/".$cardigan."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination9,$topventes_index_largeur,$topventes_index_hauteur);
		$destination10="/home/boutique/www/configurations/$domaine/images/produits/br/".$cardigan."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination10,$largeurimages,$hauteurimages);
		$DB_site2->query("insert into articlephoto (artid, image, legende) values ('$cardigan', 'jpg', 'Légende supl. 2')");
		$insertphoto=$DB_site2->insert_id() ;
		$nom_fic2="/home/boutique/www/configurations/$domaine/images/template/cardigansupl.jpg";
		$destination0="/home/boutique/www/configurations/$domaine/images/produits/".$cardigan."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination0,'10000','10000');
		$destination0="/home/boutique/www/configurations/$domaine/images/produits/article/".$cardigan."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination0,$article_largeur,$article_hauteur);
		$destination1="/home/boutique/www/configurations/$domaine/images/produits/categorie/".$cardigan."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination1,$categorie_largeur,$categorie_hauteur);
		$destination2="/home/boutique/www/configurations/$domaine/images/produits/cdc_categ/".$cardigan."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination2,$cdc_categ_largeur,$cdc_categ_hauteur);
		$destination3="/home/boutique/www/configurations/$domaine/images/produits/cdc_index/".$cardigan."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination3,$cdc_index_largeur,$cdc_index_hauteur);
		$destination4="/home/boutique/www/configurations/$domaine/images/produits/nouveautes_categ/".$cardigan."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination4,$nouveautes_categ_largeur,$nouveates_categ_hauteur);
		$destination5="/home/boutique/www/configurations/$domaine/images/produits/nouveautes_index/".$cardigan."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination5,$nouveautes_index_largeur,$nouveates_index_hauteur);
		$destination6="/home/boutique/www/configurations/$domaine/images/produits/promos_categ/".$cardigan."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination6,$promos_categ_largeur,$promos_categ_hauteur);
		$destination7="/home/boutique/www/configurations/$domaine/images/produits/promos_index/".$cardigan."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination7,$promos_index_largeur,$promos_index_hauteur);
		$destination8="/home/boutique/www/configurations/$domaine/images/produits/topventes_categ/".$cardigan."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination8,$topventes_categ_largeur,$topventes_categ_hauteur);
		$destination9="/home/boutique/www/configurations/$domaine/images/produits/topventes_index/".$cardigan."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination9,$topventes_index_largeur,$topventes_index_hauteur);
		$destination10="/home/boutique/www/configurations/$domaine/images/produits/br/".$cardigan."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination10,$largeurimages,$hauteurimages);
		$DB_site2->query("insert into articlephoto (artid, image, legende) values ('$cardigan', 'jpg', 'Légende supl. 3')");
		$insertphoto=$DB_site2->insert_id() ;
		$nom_fic2="/home/boutique/www/configurations/$domaine/images/template/cardigansupll.jpg";
		$destination0="/home/boutique/www/configurations/$domaine/images/produits/".$cardigan."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination0,'10000','10000');
		$destination0="/home/boutique/www/configurations/$domaine/images/produits/article/".$cardigan."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination0,$article_largeur,$article_hauteur);
		$destination1="/home/boutique/www/configurations/$domaine/images/produits/categorie/".$cardigan."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination1,$categorie_largeur,$categorie_hauteur);
		$destination2="/home/boutique/www/configurations/$domaine/images/produits/cdc_categ/".$cardigan."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination2,$cdc_categ_largeur,$cdc_categ_hauteur);
		$destination3="/home/boutique/www/configurations/$domaine/images/produits/cdc_index/".$cardigan."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination3,$cdc_index_largeur,$cdc_index_hauteur);
		$destination4="/home/boutique/www/configurations/$domaine/images/produits/nouveautes_categ/".$cardigan."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination4,$nouveautes_categ_largeur,$nouveates_categ_hauteur);
		$destination5="/home/boutique/www/configurations/$domaine/images/produits/nouveautes_index/".$cardigan."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination5,$nouveautes_index_largeur,$nouveates_index_hauteur);
		$destination6="/home/boutique/www/configurations/$domaine/images/produits/promos_categ/".$cardigan."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination6,$promos_categ_largeur,$promos_categ_hauteur);
		$destination7="/home/boutique/www/configurations/$domaine/images/produits/promos_index/".$cardigan."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination7,$promos_index_largeur,$promos_index_hauteur);
		$destination8="/home/boutique/www/configurations/$domaine/images/produits/topventes_categ/".$cardigan."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination8,$topventes_categ_largeur,$topventes_categ_hauteur);
		$destination9="/home/boutique/www/configurations/$domaine/images/produits/topventes_index/".$cardigan."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination9,$topventes_index_largeur,$topventes_index_hauteur);
		$destination10="/home/boutique/www/configurations/$domaine/images/produits/br/".$cardigan."_".$insertphoto.".jpg";
		redimentionner_image($nom_fic2,$destination10,$largeurimages,$hauteurimages);
	
		// article_caractval
		$DB_site2->query("insert into article_caractval (artid, caractvalid, differenceprix) values ('$jeanlevis501', '$noir', '3')");
		$DB_site2->query("insert into article_caractval (artid, caractvalid, differenceprix) values ('$jeanlevis501', '$bleu', '')");
		$DB_site2->query("insert into article_caractval (artid, caractvalid, differenceprix) values ('$jeanlevis501', '$vert', '-1')");
		$DB_site2->query("insert into article_caractval (artid, caractvalid, differenceprix) values ('$jeanlevis501', '$rouge', '')");
		$DB_site2->query("insert into article_caractval (artid, caractvalid, differenceprix) values ('$jeanlevis501', '$taille34', '0')");
		$DB_site2->query("insert into article_caractval (artid, caractvalid, differenceprix) values ('$jeanlevis501', '$taille36', '0')");
		$DB_site2->query("insert into article_caractval (artid, caractvalid, differenceprix) values ('$jeanlevis501', '$taille38', '0')");
		$DB_site2->query("insert into article_caractval (artid, caractvalid, differenceprix) values ('$jeanlevis501', '$taille40', '0')");
		
		$DB_site2->query("insert into article_caractval (artid, caractvalid, differenceprix) values ('$jeanleecooper', '$noir', '0')");
		$DB_site2->query("insert into article_caractval (artid, caractvalid, differenceprix) values ('$jeanleecooper', '$bleu', '0')");
		$DB_site2->query("insert into article_caractval (artid, caractvalid, differenceprix) values ('$jeanleecooper', '$vert', '0')");
		$DB_site2->query("insert into article_caractval (artid, caractvalid, differenceprix) values ('$jeanleecooper', '$rouge', '3')");
		$DB_site2->query("insert into article_caractval (artid, caractvalid, differenceprix) values ('$jeanleecooper', '$taille34', '-4')");
		$DB_site2->query("insert into article_caractval (artid, caractvalid, differenceprix) values ('$jeanleecooper', '$taille36', '0')");
		$DB_site2->query("insert into article_caractval (artid, caractvalid, differenceprix) values ('$jeanleecooper', '$taille38', '0')");
		$DB_site2->query("insert into article_caractval (artid, caractvalid, differenceprix) values ('$jeanleecooper', '$taille40', '0')");
	
		$DB_site2->query("insert into article_caractval (artid, caractvalid, differenceprix) values ('$taillebasse', '$noir', '0')");
		$DB_site2->query("insert into article_caractval (artid, caractvalid, differenceprix) values ('$taillebasse', '$taille36', '0')");
		$DB_site2->query("insert into article_caractval (artid, caractvalid, differenceprix) values ('$taillebasse', '$taille38', '0')");
		$DB_site2->query("insert into article_caractval (artid, caractvalid, differenceprix) values ('$taillebasse', '$taille40', '0')");
		
		$DB_site2->query("insert into article_caractval (artid, caractvalid, differenceprix) values ('$minijupe', '$noir', '0')");
		$DB_site2->query("insert into article_caractval (artid, caractvalid, differenceprix) values ('$minijupe', '$rouge', '3')");
		$DB_site2->query("insert into article_caractval (artid, caractvalid, differenceprix) values ('$minijupe', '$taille38', '0')");
	
		$DB_site2->query("insert into article_caractval (artid, caractvalid, differenceprix) values ('$cardigan', '$noir', '0')");
		$DB_site2->query("insert into article_caractval (artid, caractvalid, differenceprix) values ('$cardigan', '$bleu', '-1')");
		$DB_site2->query("insert into article_caractval (artid, caractvalid, differenceprix) values ('$cardigan', '$vert', '0')");
		$DB_site2->query("insert into article_caractval (artid, caractvalid, differenceprix) values ('$cardigan', '$rouge', '3')");
		$DB_site2->query("insert into article_caractval (artid, caractvalid, differenceprix) values ('$cardigan', '$taille34', '-2')");
		$DB_site2->query("insert into article_caractval (artid, caractvalid, differenceprix) values ('$cardigan', '$taille36', '0')");
		$DB_site2->query("insert into article_caractval (artid, caractvalid, differenceprix) values ('$cardigan', '$taille38', '0')");
		$DB_site2->query("insert into article_caractval (artid, caractvalid, differenceprix) values ('$cardigan', '$taille40', '0')");
	
		$DB_site2->query("insert into article_caractval (artid, caractvalid, differenceprix) values ('$topmanchescourtes', '$taille34', '-2')");
		$DB_site2->query("insert into article_caractval (artid, caractvalid, differenceprix) values ('$topmanchescourtes', '$taille36', '0')");
		$DB_site2->query("insert into article_caractval (artid, caractvalid, differenceprix) values ('$topmanchescourtes', '$taille38', '0')");
		$DB_site2->query("insert into article_caractval (artid, caractvalid, differenceprix) values ('$topmanchescourtes', '$taille40', '0')");
	
		// stock
		$DB_site2->query("insert into stock (artid, nombre, seuil, delaiappro) values ('$maillotdebains', '143', '22', '9')");
		
		$DB_site2->query("insert into stock (artid, nombre, seuil, delaiappro) values ('$topmanchescourtes', '500', '80', '12')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$topmanchescourtes', '100')");
				$stock_topmanchescourtes_1=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_topmanchescourtes_1', '$taille34')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$topmanchescourtes', '50')");
				$stock_topmanchescourtes_2=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_topmanchescourtes_2', '$taille36')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$topmanchescourtes', '150')");
				$stock_topmanchescourtes_3=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_topmanchescourtes_3', '$taille38')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$topmanchescourtes', '200')");
				$stock_topmanchescourtes_4=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_topmanchescourtes_4', '$taille40')");

		$DB_site2->query("insert into stock (artid, nombre, seuil, delaiappro) values ('$jeanlevis501', '100', '14', '4')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$jeanlevis501', '3')");
				$stock_jeanlevis501_1=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanlevis501_1', '$taille34')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanlevis501_1', '$noir')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$jeanlevis501', '7')");
				$stock_jeanlevis501_2=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanlevis501_2', '$taille34')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanlevis501_2', '$bleu')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$jeanlevis501', '4')");
				$stock_jeanlevis501_3=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanlevis501_3', '$taille34')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanlevis501_3', '$vert')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$jeanlevis501', '9')");
				$stock_jeanlevis501_4=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanlevis501_4', '$taille34')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanlevis501_4', '$rouge')");
			
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$jeanlevis501', '10')");
				$stock_jeanlevis501_5=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanlevis501_5', '$taille36')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanlevis501_5', '$noir')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$jeanlevis501', '10')");
				$stock_jeanlevis501_6=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanlevis501_6', '$taille36')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanlevis501_6', '$bleu')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$jeanlevis501', '12')");
				$stock_jeanlevis501_7=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanlevis501_7', '$taille36')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanlevis501_7', '$vert')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$jeanlevis501', '2')");
				$stock_jeanlevis501_8=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanlevis501_8', '$taille36')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanlevis501_8', '$rouge')");
			
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$jeanlevis501', '6')");
				$stock_jeanlevis501_9=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanlevis501_9', '$taille38')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanlevis501_9', '$noir')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$jeanlevis501', '5')");
				$stock_jeanlevis501_10=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanlevis501_10', '$taille38')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanlevis501_10', '$bleu')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$jeanlevis501', '8')");
				$stock_jeanlevis501_11=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanlevis501_11', '$taille38')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanlevis501_11', '$vert')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$jeanlevis501', '10')");
				$stock_jeanlevis501_12=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanlevis501_12', '$taille38')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanlevis501_12', '$rouge')");
			
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$jeanlevis501', '6')");
				$stock_jeanlevis501_13=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanlevis501_13', '$taille40')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanlevis501_13', '$noir')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$jeanlevis501', '3')");
				$stock_jeanlevis501_14=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanlevis501_14', '$taille40')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanlevis501_14', '$bleu')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$jeanlevis501', '1')");
				$stock_jeanlevis501_15=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanlevis501_15', '$taille40')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanlevis501_15', '$vert')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$jeanlevis501', '4')");
				$stock_jeanlevis501_16=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanlevis501_16', '$taille40')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanlevis501_16', '$rouge')");

		$DB_site2->query("insert into stock (artid, nombre, seuil, delaiappro) values ('$jeanleecooper', '63', '9', '6')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$jeanleecooper', '3')");
				$stock_jeanleecooper_1=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanleecooper_1', '$taille34')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanleecooper_1', '$noir')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$jeanlevis501', '7')");
				$stock_jeanleecooper_2=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanleecooper_2', '$taille34')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanleecooper_2', '$bleu')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$jeanlevis501', '4')");
				$stock_jeanleecooper_3=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanleecooper_3', '$taille34')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanleecooper_3', '$vert')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$jeanlevis501', '9')");
				$stock_jeanleecooper_4=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanleecooper_4', '$taille34')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanleecooper_4', '$rouge')");
			
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$jeanleecooper', '10')");
				$stock_jeanleecooper_5=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanleecooper_5', '$taille36')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanleecooper_5', '$noir')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$jeanlevis501', '10')");
				$stock_jeanleecooper_6=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanleecooper_6', '$taille36')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanleecooper_6', '$bleu')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$jeanlevis501', '12')");
				$stock_jeanleecooper_7=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanleecooper_7', '$taille36')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanleecooper_7', '$vert')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$jeanlevis501', '2')");
				$stock_jeanleecooper_8=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanleecooper_8', '$taille36')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanleecooper_8', '$rouge')");
			
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$jeanleecooper', '6')");
				$stock_jeanleecooper_9=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanleecooper_9', '$taille38')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanleecooper_9', '$noir')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$jeanlevis501', '5')");
				$stock_jeanleecooper_10=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanleecooper_10', '$taille38')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanleecooper_10', '$bleu')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$jeanlevis501', '8')");
				$stock_jeanleecooper_11=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanleecooper_11', '$taille38')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanleecooper_11', '$vert')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$jeanlevis501', '10')");
				$stock_jeanleecooper_12=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanleecooper_12', '$taille38')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanleecooper_12', '$rouge')");
			
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$jeanleecooper', '6')");
				$stock_jeanleecooper_13=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanleecooper_13', '$taille40')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanleecooper_13', '$noir')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$jeanlevis501', '3')");
				$stock_jeanleecooper_14=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanleecooper_14', '$taille40')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanleecooper_14', '$bleu')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$jeanlevis501', '1')");
				$stock_jeanleecooper_15=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanleecooper_15', '$taille40')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanleecooper_15', '$vert')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$jeanlevis501', '4')");
				$stock_jeanleecooper_16=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanleecooper_16', '$taille40')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_jeanleecooper_16', '$rouge')");

		$DB_site2->query("insert into stock (artid, nombre, seuil, delaiappro) values ('$taillebasse', '23', '5', '8')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$taillebasse', '12')");
				$stock_taillebasse_1=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_taillebasse_1', '$noir')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_taillebasse_1', '$taille36')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$taillebasse', '4')");
				$stock_taillebasse_2=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_taillebasse_2', '$noir')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_taillebasse_2', '$taille38')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$taillebasse', '5')");
				$stock_taillebasse_3=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_taillebasse_3', '$noir')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_taillebasse_3', '$taille40')");

		$DB_site2->query("insert into stock (artid, nombre, seuil, delaiappro) values ('$minijupe', '160', '40', '12')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$minijupe', '100')");
				$stock_minijupe_1=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_minijupe_1', '$noir')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_minijupe_1', '$taille38')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$minijupe', '60')");
				$stock_minijupe_2=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_minijupe_2', '$rouge')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_minijupe_2', '$taille38')");

		$DB_site2->query("insert into stock (artid, nombre, seuil, delaiappro) values ('$cardigan', '97', '3', '1')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$cardigan', '10')");
				$stock_cardigan_1=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_cardigan_1', '$noir')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_cardigan_1', '$taille34')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$cardigan', '10')");
				$stock_cardigan_2=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_cardigan_2', '$noir')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_cardigan_2', '$taille36')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$cardigan', '10')");
				$stock_cardigan_3=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_cardigan_3', '$noir')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_cardigan_3', '$taille38')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$cardigan', '5')");
				$stock_cardigan_4=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_cardigan_4', '$noir')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_cardigan_4', '$taille40')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$cardigan', '5')");
				$stock_cardigan_5=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_cardigan_5', '$bleu')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_cardigan_5', '$taille34')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$cardigan', '5')");
				$stock_cardigan_6=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_cardigan_6', '$bleu')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_cardigan_6', '$taille36')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$cardigan', '5')");
				$stock_cardigan_7=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_cardigan_7', '$bleu')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_cardigan_7', '$taille38')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$cardigan', '5')");
				$stock_cardigan_8=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_cardigan_8', '$bleu')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_cardigan_8', '$taille40')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$cardigan', '5')");
				$stock_cardigan_9=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_cardigan_9', '$vert')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_cardigan_9', '$taille34')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$cardigan', '5')");
				$stock_cardigan_10=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_cardigan_10', '$vert')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_cardigan_10', '$taille36')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$cardigan', '5')");
				$stock_cardigan_11=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_cardigan_11', '$vert')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_cardigan_11', '$taille38')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$cardigan', '5')");
				$stock_cardigan_12=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_cardigan_12', '$vert')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_cardigan_12', '$taille40')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$cardigan', '5')");
				$stock_cardigan_13=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_cardigan_13', '$rouge')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_cardigan_13', '$taille34')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$cardigan', '5')");
				$stock_cardigan_14=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_cardigan_14', '$rouge')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_cardigan_14', '$taille36')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$cardigan', '5')");
				$stock_cardigan_15=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_cardigan_15', '$rouge')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_cardigan_15', '$taille38')");
			$DB_site2->query("insert into stock_caractval (artid, stock) values ('$cardigan', '7')");
				$stock_cardigan_16=$DB_site2->insert_id() ;
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_cardigan_16', '$rouge')");
				$DB_site2->query("insert into stock_caract (stockid, caractvalid) values ('$stock_cardigan_16', '$taille40')");

		$DB_site2->query("insert into stock (artid, nombre, seuil, delaiappro) values ('$vestetaill', '6', '12', '9')");

		$DB_site2->query("insert into stock (artid, nombre, seuil, delaiappro) values ('$vesteenlin', '1', '5', '14')");

		$DB_site2->query("insert into stock (artid, nombre, seuil, delaiappro) values ('$vesteblanche', '14', '13', '10')");

		$DB_site2->query("insert into stock (artid, nombre, seuil, delaiappro) values ('$bermud', '45', '16', '5')");
	
		$DB_site2->query("insert into stock (artid, nombre, seuil, delaiappro) values ('$shorty', '175', '41', '16')");
	
		// coupdecoeur
		$DB_site2->query("insert into coupdecoeur (artid, position) values ('$jeanlevis501', '1')");
		
		$DB_site2->query("insert into coupdecoeur (artid, position) values ('$taillebasse', '2')");
		
		// promotion
		$DB_site2->query("insert into promotion (artid, prix, datedebut, datefin) values ('$jeanleecooper', '130', '2006-01-01', '2006-12-31')");
	
		$DB_site2->query("insert into promotion (artid, prix, datedebut, datefin) values ('$cardigan', '60', '2006-01-01', '2006-12-31')");
	
		$DB_site2->query("insert into promotion (artid, prix, datedebut, datefin) values ('$shorty', '30', '2006-01-01', '2006-12-31')");
	
		$DB_site2->query("insert into promotion (artid, prix, datedebut, datefin) values ('$vesteenlin', '60', '2006-01-01', '2006-12-31')");
	
		$DB_site2->query("insert into promotion (artid, prix, datedebut, datefin) values ('$vesteblanche', '20', '2006-01-01', '2006-12-31')");
	
		// top ventes
		$DB_site2->query("insert into temp_top_ventes (catid, artid, nb) values ('$vestestailleurs', '$vestetaill', '1')");
		
		$DB_site2->query("insert into temp_top_ventes (catid, artid, nb) values ('$cardigans', '$cardigan', '2')");
	
		$DB_site2->query("insert into temp_top_ventes (catid, artid, nb) values ('$jupes', '$minijupe', '3')");
		
		$DB_site2->query("insert into temp_top_ventes (catid, artid, nb) values ('$tops', '$topmanchescourtes', '4')");
	}
	
function redimentionner_image ($nom_fichier, $destination, $largeur, $hauteur)
	{
	if (substr ($destination, -3) == "jpg") 
		{
		$imagetmp = imagecreatefromjpeg ($nom_fichier) ;
		}
	if (substr ($destination, -3) == "gif") 
		{
		$imagetmp = imagecreatefromgif ($nom_fichier) ;
		}
	if (substr ($destination, -3) == "png") 
		{
		$imagetmp = imagecreatefrompng ($nom_fichier) ;
		}
	$imagesize = getimagesize ($nom_fichier) ;
	if ($imagesize[0] > $largeur or $imagesize[1] > $hauteur)
		{
		if ($imagesize[0] > $imagesize[1]) 
			{
			$width = $largeur ;
			$height = floor ($largeur * $imagesize[1] / $imagesize[0]) ;
			} 
		else 
			{
			$width = floor($largeur * $imagesize[0] / $imagesize[1]) ;
			$height = $largeur ;
			}
		$image = imagecreatetruecolor ($width, $height) ;
		imagecopyresampled ($image, $imagetmp, 0, 0, 0, 0, $width, $height, $imagesize[0], $imagesize[1]) ;
		if (substr ($destination, -3) == "jpg") 
			{
			imagejpeg ($image, $destination , 80) ;
			}
		if (substr ($destination, -3) == "gif") 
			{
			imagegif ($image, $destination , 80) ;
			}
		if (substr ($destination, -3) == "png") {
			
			imagepng ($image, $destination , 80) ;
			}
		chmod ($destination, 0777) ;
		imagedestroy ($image) ;
		} 
	else 
		{
		$image = imagecreatetruecolor ($imagesize[0], $imagesize[1]) ;
		imagecopyresampled ($image, $imagetmp, 0, 0, 0, 0, $imagesize[0], $imagesize[1], $imagesize[0], $imagesize[1]) ;
		if (substr ($destination, -3) == "jpg") 
			{
			imagejpeg($image, $destination, 80) ;
			}
		if (substr ($destination, -3) == "gif") 
			{
			imagegif ($image, $destination, 80) ;
			}
		if (substr ($destination, -3) == "png") 
			{
			imagepng ($image, $destination, 80) ;
			}
		chmod ($destination, 0777) ;
		imagedestroy ($image) ;
		}
	}
?>
