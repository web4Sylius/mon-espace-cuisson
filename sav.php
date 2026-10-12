<?php
//Si utilisateur non enregistré, on n'affiche pas le module
if(!isset($user_info)){
 header("Location: /");
 exit;
}
if($user_info['userid']==''){
	header("Location: /");
	exit;
}



//Affichage par défaut (si le client ne vient pas de soumettre un SAV)
if(!isset($_POST['facturid']))
{
	//Recherche toutes les commandes avec paiement validé au moins, où il n'y a pas de SAV en cours
	$sql = "SELECT f.facturid, f.heurefacture, f.prixport, f.montantcadeau";
	$sql .= " FROM facture f";
	$sql .= " WHERE ";
	$sql .= "(";
	$sql .= "	SELECT COUNT(id)";
	$sql .= " 	FROM sav";
	$sql .= " 	WHERE facturid = f.facturid";
	$sql .= " 	AND fin = 0";
	$sql .= ") = 0";
	$sql .= " AND userid = ".$user_info['userid'];
	$sql .= " AND valide IN (1,5,7,8)";
	$sql .= " ORDER BY heurefacture";
	$factures = $DB_site->query($sql);
 	$facturenb = $DB_site->num_rows($factures);

 	//S'il y a au moins un résultat, on affiche une liste de résultats avec les commandes pour que le client puissent choisir
	if($facturenb > 1)
	{
		$template->parts["choixfacture"] = "<select name=\"facturid\" onchange=\"changefacturid();\">";
		
		while($facture = $DB_site->fetch_array($factures))
		{
			$template->parts["choixfacture"] .= "<option value=\"" . $facture['facturid'] . "\">" . $facture['facturid'] . " " . date("d/m/Y",$facture['heurefacture']) . "</option>";
		}

		$template->parts["choixfacture"] .= "</select>";

		$template->evaltemplate("ClientSAV1","content");
	}
	//S'il n'y a qu'une seule commande possible, input hidden
	elseif($facturenb == 1)
	{
		$facture = $DB_site->fetch_array($factures);

		$template->parts["choixfacture"] = "<input type=\"hidden\" name=\"facturid\" value=\"" . $facture['facturid'] . "\">" . $facture['facturid'] . " " . date("d/m/Y",$facture['heurefacture']);
		
		$template->evaltemplate("ClientSAV1","content");
	}
	//Si aucune commande possible
	else
	{
		//Recherche toute les commandes avec SAV en cours
		$sql = "SELECT f.facturid, f.heurefacture, f.prixport, f.montantcadeau, sav.date";
		$sql .= " FROM facture f";
		$sql .= " INNER JOIN sav USING (facturid)";
		$sql .= " WHERE sav.fin = 0";
		$sql .= " AND userid = " . $user_info['userid'];
		$sql .= " AND valide IN (1,5,7,8)";
		$sql .= " ORDER BY heurefacture";
		$factures = $DB_site->query($sql);

		$template->parts["encours"] = "";
		
		//Affichage de la liste des SAV en cours s'il y en a
		while($facture = $DB_site->fetch_array($factures))
		{
			$template->parts["encours"] .= "<p>n°" . $facture['facturid'] . " du " . date("d/m/Y",$facture['heurefacture']) . "</p>";
		}

		$template->evaltemplate("ClientSAV0","content");
	}

}
//Si l'utilisateur vient de soumettre un SAV, on le crée
else
{
	$facturid = $_POST['facturid'];

	//Récupération du numéro de la commande et le n° client associé
	$sql = "SELECT facturid, userid";
	$sql .= " FROM facture";
	$sql .= " WHERE facturid = " . intval($facturid);
	$facture = $DB_site->query_first($sql);

	//Si l'utilisateur de la commande ne correspond pas à l'utilisateur loggé, on stoppe le traitement
	if($facture['userid'] != $user_info['userid']) exit;

	//Récupération du nombre de SAV en cours sur la commande
	$sql = "SELECT COUNT(*)";
	$sql .= " FROM sav";
	$sql .= " WHERE fin = 0";
	$sql .= " AND facturid = " . intval($facturid);
	$sav = $DB_site->query_first($sql);
	
	//S'il y a au moins 1 SAV en cours, on stoppe le traitement
	if($sav[0] > 0) exit;

	//Récupération des produits de la commande
	$sql = "SELECT lignefactureid";
	$sql .= " FROM lignefacture";
	$sql .= " WHERE factureid = " . intval($facturid);
	$requete = $DB_site->query($sql);
	while($ligne = $DB_site->fetch_array($requete)) $lignes[] = $ligne[0];
     
    //Paramétrage des variables
	$ligneqte    = array();
	$ligneqte2   = array();
	$utilisateur = "client";
	$savid       = false;

	//Boucle sur le POST, pour récupérer les produits concernés par le SAV
	foreach($_POST as $name => $value)
	{
		if(substr($name,0,3) == "qte")
		{
			$ligneid = substr($name,3);

			//Si le produit a une quantité > 1, on stock pour traitement
			if(in_array($ligneid,$lignes) && $value > 0) $ligneqte[$ligneid] = $value;
		}
	}

	//Boucle sur les produits récupérés précédemment pour formatage avant insertion en BDD
	foreach($ligneqte as $ligneid => $qte) $ligneqte2[] = $qte."-".$ligneid;

	//Si c'est un administrateur qui fait la manipulation à la place du client, l'utilisateur "client" est remplacé par le nom de l'admin en cours (ex: "qualite")
	if(isset($_COOKIE['admin_username']) && $_COOKIE['admin_username'] != "") $utilisateur=$_COOKIE['admin_username'];

	//Création du SAV en BDD
	$sql = "INSERT INTO sav SET";
	$sql .= " facturid = " . intval($facturid);
	$sql .= ", lignes = '" . implode(",",$ligneqte2) . "'";
	$sql .= ", date = " . time();
	$sql .= ", utilisateur = '" . $utilisateur . "'";
	$DB_site->query($sql);
	$savid = $DB_site->insert_id();

	$sql = "INSERT INTO sav_fields SET";
	$sql .= " savid = " . intval($savid);
	$sql .= ", name = 'sav_typelitige'";
	$sql .= ", value = '" . intval($_POST['typelitige']) . "'";
	$sql .= ", utilisateur = '" . $utilisateur . "'";
	$sql .= ", date = " . time();
	$DB_site->query($sql);

	//Boucle sur les valeurs saisies dans le formulaire => completion du SAV
	foreach($_POST as $key => $value)
	{
		if(substr($key,0,4)=="sav_")
		{
			$sql = "INSERT INTO sav_fields SET";
			$sql .= " savid = " . intval($savid);
			$sql .= ", name = '" . addslashes($key) . "'";
			$sql .= ", value = '" . addslashes(strip_tags($value)) . "'";
			$sql .= ", utilisateur = '" . $sav['utilisateur'] . "'";
			$sql .= ", date = " . time();
			$DB_site->query($sql);
		} 
	}
     
    //Entier random généré dans le formulaire de SAV (fichier sav_ajax.php)
    //Intérêt ???
  	if(isset($_POST['uniq']))
  	{
  		$sql = "UPDATE sav_files SET";
  		$sql .= " savid = " . $savid;
  		$sql .= " WHERE savid = " . intval($_POST['uniq']);
  		$DB_site->query($sql);
  	} 

  	$TemplateResume = ""
  	. "<p><b>" . $template->translate("/sav/commande_n","F") . " : </b>" . intval($_POST['facturid']) . "</p>"
  	. "<p><b>" . $template->translate("/sav/article_concerne","F") . " :</b>";

  	//Récupération du détail sur les produits sélectionnés par l'utilisateur dans le SAV
  	$sql = "SELECT lignefactureid, lf.libelle, artcode, m.libelle AS marque";
  	$sql .= " FROM lignefacture lf";
  	$sql .= " INNER JOIN article_marque am USING(artid)";
  	$sql .= " INNER JOIN marque m USING(marqueid)";
  	$sql .= " WHERE factureid = '" . intval($facturid)."'";
  	$sql .= " AND lignefactureid IN (" . implode(",",array_keys($ligneqte)) . ")";
  	$lignes = $DB_site->query($sql);

  	//Affichage des produits
	while($ligne = $DB_site->fetch_array($lignes)) $TemplateResume .= "<br>" . $ligneqte[$ligne['lignefactureid']] . " " . $ligne['libelle'] . " - " . $ligne['artcode'] . " " . $ligne['marque'];
	$TemplateResume .= "</p>";

	//Récupère la liste des fichiers/photos transmises par le client
	$sql = "SELECT name";
	$sql .= " FROM sav_files";
	$sql .= " WHERE savid = " . $savid;
	$fichiers = $DB_site->query($sql);
	if($DB_site->num_rows($fichiers) > 0)
	{
		//Ajout de la liste des fichiers/photos au template général
		$TemplateResume .= "<p><b>" . $template->translate("/sav/fichiers_envoyes","F") . " :</b>";
		while($fichier = $DB_site->fetch_array($fichiers)) $TemplateResume .= "<br>" . $fichier['name'];
		$TemplateResume .= "</p>";
	}

	//Inclus les détails du litige dans le template principal
	//Le contenu est fonction du type de litige sélectionné
	$TemplateResume .= resume($_POST['typelitige']);

	$noms = array(
		0 => $template->translate("/sav/avarie_produit","F"),
		1 => $template->translate("/sav/panne_produit","F"),
		2 => $template->translate("/sav/erreur_produit","F"),
		3 => $template->translate("/sav/retractation","F"),
		4 => $template->translate("/sav/manque_produit","F")
	);
	$nomlitige = $noms[$_POST['typelitige']];

	//Récupération du contenu du mail de création de SAV
	$sql = "SELECT sujet" . $template->parts["langue"] . " AS sujet, contenu" . $template->parts["langue"] . " AS contenu";
	$sql .= " FROM emails_clients";
	$sql .= " WHERE emailcode = 'creationsavbis'";
	$sql .= " AND site_code = '" . $template->site_code() . "'";
	$email = $DB_site->query_first($sql);
	$email['contenu'] = addslashes($email['contenu']);

	eval("\$sujet=\"$email[sujet]\";");
	eval("\$body=\"$email[contenu]\";");

	//Envoi de l'email au SAV
	$mail = new email();
	$mail->copymode     = "bcc";
	$mail->to           = MAIL_DIRECTION;
	$mail->destname     = "SAV";
	$mail->destinataire = MAIL_SAV;
	$mail->expediteur   = $template->translate("/site/mail_expediteur");
	$mail->expname      = $template->translate("/site/nom");
	$mail->subject      = $template->translate("/site/nom")." - #SAV$facturid-$savid#";
	$mail->body         = $body;
	$mail->sendmail();
	//Si le client a demandé à recevoir une copie du mail
	if($_POST['mail']==1)
	{
		$mail2 = clone $mail;
		$mail->to = "";
		$mail2->destname     = $user_info['prenom'] . " " . $user_info['nom'];
		$mail2->destinataire = $user_info['mail'];
		$mail2->sendmail();
	}

	$template->parts["TemplateResume"] = $TemplateResume;

	//Affichage template général
	$template->evaltemplate("ClientSAVfin","content");      
}

function resume($typelitige)
{
	global $template;

	$return = "";

	switch($typelitige)
	{
		case 0:

			$return .= ""
			. "<p><b>" . $template->translate("/sav/date_livraison","F") . " : </b>" . strip_tags($_POST['sav_datelivr']) . "</p>"
			. "<p><b>" . $template->translate("/sav/numero_bon_transport","F") . " : </b>" . ($_POST['sav_nbon'] != "" ? strip_tags($_POST['sav_nbon']) : $template->translate("/sav/sait_pas")) . "</p>"
			. "<p><b>" . $template->translate("/sav/nom_transporteur","F") . " : </b>" . ($_POST['sav_nomtrspt'] != "" ? strip_tags($_POST['sav_nomtrspt']) : $template->translate("/sav/sait_pas")) . "</p>"
			. "<p><b>" . $template->translate("/sav/conserve_marchandise") . " : </b>";
			
			if(intval($_POST['sav_marchandise']) == 1)
			{
				$return .= " "
				. $template->translate("/sav/oui","F") . "</p>"
				. "<p><b>" . $template->translate("/sav/numero_serie","F") . " : </b>" . ($_POST['sav_nbserie'] != "" ? strip_tags($_POST['sav_nbserie']) : $template->translate("/sav/sait_pas")) . "</p>"
				. "<p><b>" . $template->translate("/sav/conserve_emballage","F") . " : </b>" . (intval($_POST['sav_emballage']) == 0 ? $template->translate("/sav/non","F") : $template->translate("/sav/oui","F")) . "</p>";
			}
			else
			{
				$return .= " " . $template->translate("/sav/non","F") . "</p>";
			}
			    
			$return .= "<p><b>" . $template->translate("/sav/reserves_bon_transport","F") . " : </b>";

			if($_POST['sav_reserve'] == 1)
			{
				$return .= " " 
				. $template->translate("/sav/oui","F") . "</p>"
				. "<p>"
					. "<b>" . $template->translate("/sav/reserves_emises","F") . " :</b>"
					. "<br>"
					. strip_tags($_POST['sav_reserves'])
				. "</p>";
			}  
			else
			{
				$return .= " " . $template->translate("/sav/non","F") . "</p>";
			}
			    
			$return .= "<p><b>" . $template->translate("/sav/detail_precis","F") . " :</b><br>" . strip_tags($_POST['sav_demande']) . "</p>";

		break;

		case 1:

			$return .= ""
			. "<p><b>" . $template->translate("/sav/date_livraison","F") . " : </b>" . ($_POST['sav_datelivr'] == "" ? $template->translate("/sav/sait_pas") : strip_tags($_POST['sav_datelivr'])) . "</p>"
			. "<p><b>" . $template->translate("/sav/date_panne","F") . " : </b>" . strip_tags($_POST['sav_datelitige'])."</p>"
			. "<p><b>" . $template->translate("/sav/numero_serie","F") . " : </b>" . ($_POST['sav_nbserie']!=""?strip_tags($_POST['sav_nbserie']):$template->translate("/sav/sait_pas"))."</p>"
			. "<p><b>" . $template->translate("/sav/conserve_emballage","F") . " : </b>" . ($_POST['sav_emballage']==1?$template->translate("/sav/oui","F"):$template->translate("/sav/non","F"))."</p>"
			. "<p><b>" . $template->translate("/sav/pays_produit","F") . " : </b>" . strip_tags($_POST['sav_paysproduit'])."</p>"
			. "<p><b>" . $template->translate("/sav/detail_panne","F") . " :</b><br>" . strip_tags($_POST['sav_detail1'])."</p>"
			. "<p><b>" . $template->translate("/sav/autres_infos","F") . " :</b><br>" . strip_tags($_POST['sav_detail2'])."</p>";

		break;

		case 2:

			$return .= ""
			. "<p><b>" . $template->translate("/sav/date_livraison","F") . " : </b>" . strip_tags($_POST['sav_datelivr']) . "</p>"
			. "<p><b>" . $template->translate("/sav/numero_bon_transport","F") . " : </b>" . ($_POST['sav_nbon'] != "" ? strip_tags($_POST['sav_nbon']) : $template->translate("/sav/sait_pas")) . "</p>"
			. "<p><b>" . $template->translate("/sav/nom_transporteur","F") . " : </b>" . ($_POST['sav_nomtrspt'] != "" ? strip_tags($_POST['sav_nomtrspt']) : $template->translate("/sav/sait_pas")) . "</p>"
			. "<p><b>" . $template->translate("/sav/conserve_marchandise") . " : </b>";

			if(intval($_POST['sav_marchandise']) == 1)
			{
				$return .= " " . $template->translate("/sav/oui","F") . "</p>
			    <p><b>" . $template->translate("/sav/conserve_emballage","F") . " : </b>" . (intval($_POST['sav_emballage']) == 0 ? $template->translate("/sav/non","F") : $template->translate("/sav/oui","F")) . "</p>";
			}
			else
			{
				$return .= " " . $template->translate("/sav/non","F") . "</p>";
			}
			    
			$return .= ""
			. "<p>"
				. "<b>" . $template->translate("/sav/reserves_bon_transport","F") . " : </b>";
			
			if($_POST['sav_reserve'] == 1)
			{
				$return .= " " 
					. $template->translate("/sav/oui","F") . "</p>"
				. "<p>"
					. "<b>" . $template->translate("/sav/reserves_emises","F") . " :</b>"
					. "<br>" . strip_tags($_POST['sav_reserves'])
				. "</p>";
			}
			else
			{
				$return .= " " . $template->translate("/sav/non","F")."</p>";
			}
			    
			$return .= "<p><b>".$template->translate("/sav/detail_precis","F")." :</b><br>".strip_tags($_POST['sav_demande'])."</p>";

		break;

		case 3:

			$return .= ""
			. "<p><b>" . $template->translate("/sav/date_livraison","F") . " : </b>" . strip_tags($_POST['sav_datelivr']) . "</p>"
			. "<p><b>" . $template->translate("/sav/numero_bon_transport","F") . " : </b>" . ($_POST['sav_nbon'] != "" ? strip_tags($_POST['sav_nbon']) : $template->translate("/sav/sait_pas")) . "</p>"
			. "<p><b>" . $template->translate("/sav/nom_transporteur","F") . " : </b>" . ($_POST['sav_nomtrspt'] != "" ? strip_tags($_POST['sav_nomtrspt']) : $template->translate("/sav/sait_pas")) . "</p>"
			. "<p><b>" . $template->translate("/sav/numero_serie","F") . " : </b>" . ($_POST['sav_nbserie'] != "" ? strip_tags($_POST['sav_nbserie']) : $template->translate("/sav/sait_pas")) . "</p>"
			. "<p><b>" . $template->translate("/sav/conserve_marchandise") . " : </b>";

			if(intval($_POST['sav_marchandise']) == 1)
			{
				$return .= " " 
					. $template->translate("/sav/oui","F") . "</p>"
			    . "<p><b>" . $template->translate("/sav/conserve_emballage","F") . " : </b>" . (intval($_POST['sav_emballage']) == 0 ? $template->translate("/sav/non","F") : $template->translate("/sav/oui","F")) . "</p>";
			}
			else
			{
				$return .= " ".$template->translate("/sav/non","F") . "</p>";
			}
			    
			$return .= "<p><b>".$template->translate("/sav/reserves_bon_transport","F") . " : </b>";

			if($_POST['sav_reserve'] == 1)
			{
				$return .= " " . $template->translate("/sav/oui","F") . "</p><p><b>" . $template->translate("/sav/reserves_emises","F") . " :</b><br>" . strip_tags($_POST['sav_reserves']) . "</p>";
			}
			else
			{
				$return .= " " . $template->translate("/sav/non","F") . "</p>";
			}
			    
			$return .= "<p><b>" . $template->translate("/sav/detail_precis","F") . " :</b><br>" . strip_tags($_POST['sav_demande']) . "</p>";

		break;

		case 4:

			$return .= ""
			. "<p><b>" . $template->translate("/sav/date_livraison","F") . " : </b>" . strip_tags($_POST['sav_datelivr'])."</p>"
			. "<p><b>" . $template->translate("/sav/numero_bon_transport","F") . " : </b>" . ($_POST['sav_nbon'] != ""?strip_tags($_POST['sav_nbon']) : $template->translate("/sav/sait_pas")) . "</p>"
			. "<p><b>" . $template->translate("/sav/nom_transporteur","F") . " : </b>" . ($_POST['sav_nomtrspt'] != ""?strip_tags($_POST['sav_nomtrspt']) : $template->translate("/sav/sait_pas")) . "</p>"
			. "<p><b>" . $template->translate("/sav/numero_serie","F") . " : </b>" . ($_POST['sav_nbserie'] != ""?strip_tags($_POST['sav_nbserie']) : $template->translate("/sav/sait_pas")) . "</p>"
			. "<p><b>" . $template->translate("/sav/conserve_marchandise") . " : </b>";

			if(intval($_POST['sav_marchandise']) == 1)
			{
				$return .= " " . $template->translate("/sav/oui","F") . "</p>";
			}
			else
			{
				$return .= " " . $template->translate("/sav/non","F") . "</p>";
			}
			    
			$return .= "<p><b>" . $template->translate("/sav/reserves_bon_transport","F") . " : </b>";

			if($_POST['sav_reserve'] == 1)
			{
				$return.=" ".$template->translate("/sav/oui","F")."</p><p><b>".$template->translate("/sav/reserves_emises","F")." :</b><br>".strip_tags($_POST['sav_reserves'])."</p>";
			} 
			else
			{
				$return.=" ".$template->translate("/sav/non","F")."</p>";
			}
			    
			$return .= "<p><b>" . $template->translate("/sav/detail_precis","F") . " :</b><br>" . strip_tags($_POST['sav_demande']) . "</p>";

		break;
	}

	return $return;
}