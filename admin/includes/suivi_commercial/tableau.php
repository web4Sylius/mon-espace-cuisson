<?php
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");
header('Content-Type: text/html; charset=utf-8');
set_time_limit(2000);

//Récupération des différents filtres pour intégration dans requête
$sqlpour      = isset($_GET['user_commercial']) && $_GET['user_commercial'] != '' ? " u.commercial = '".$_GET['user_commercial']."'" : '';
if($_GET['user_commercial']=="na"){ // non attribué
	$sqlpour = "u.commercial = '' ";
	$sclcommercialsigne=" = ";
} else {
	$sclcommercialsigne=" <> ";
}


switch($_GET['tableau']){
	case 'nouveau':
	case 'nrappel':
	case 'norap':
		if(isset($_GET['user_rappel']) && $_GET['user_rappel'] != ''){
			$sqlrappel = " AND 1 = 0 ";
		} else {
			$sqlrappel = "";
		}
		break;
	default :
		if(isset($_GET['user_rappel']) && $_GET['user_rappel'] != ''){
			$sqlrappel = " AND ("; //Récupération du dernier rappel dans une certaine tranche de dates
			$sqlrappel .= "      SELECT pour ";
			$sqlrappel .= "      FROM suivi_client FORCE INDEX (userid)";
			$sqlrappel .= "      WHERE userid = u.userid";
			$sqlrappel .= "      AND rappel>0 ";
			$sqlrappel .= "      ORDER BY date DESC";
			$sqlrappel .= "      LIMIT 0,1";
			$sqlrappel .= " ) = '".$_GET['user_rappel']."' ";
		} else {
			$sqlrappel = "";
		}
		break;
}



switch($_GET['tableau']){
	case 'nouveau':
	case 'nrappel':
	case 'norap':
		if(isset($_GET['etat']) && $_GET['etat'] != ''){
			$sqletat = " AND 1 = 0 ";
		} else {
			$sqletat = "";
		}
		break;
	default :
		if(isset($_GET['etat']) && $_GET['etat'] != ''){
			$sqletat = "(". $_GET['etat'] .")";
		} else {
			$sqletat = " (1,2,3) ";
		}
		break;
}



/*
if(isset($_GET['user_commercial']) && $_GET['user_commercial'] != '' && $_GET['tableau']=="nouveau"){
	$sqldeny = " AND 1 = 0 ";
} else {
	$sqldeny = "";
}
*/

$sqltypesuivi = isset($_GET['type_suivi']) && $_GET['type_suivi'] != '' ? " u.utilisateur_type_suivi_id = ".$_GET['type_suivi'] : '';


$liste_ids=$_GET['liste_ids'];
if($liste_ids==""){
	$liste_ids="0";
}


$sqlconseils = '';
if(isset($_GET['conseils'])){
     switch ($_GET['conseils']) {
          case '0':
               $sqlconseils = " u.conseils = 'non'";    
               break;
          case '1': 
               $sqlconseils = " u.conseils ='oui'";
               break;
          case '2':
               $sqlconseils = " u.conseils = ''";
               break;
          case '':
               $sqlconseils = " u.conseils IN('oui', 'non', '')";
               break;     
     }
}

$sqltype      = '';
if(isset($_GET['type'])){
     switch($_GET['type']){
          case "part":
               $sqltype = " u.raisonsociale = ''";
               break;
          case "pro":
               $sqltype = " u.raisonsociale <> ''";
               break;
     }
}
$sqlvip       = isset($_GET['vip']) && $_GET['vip'] != '' ? "u.vip = ".$_GET['vip'] : '';  



     
//Requête différente selon le tableau à afficher
switch($_GET['tableau']){

     case 'nouveau': //Nouveaux contacts - Prioritaire (inscription > 2ans)

          $intitule_tableau = "Nouveaux contacts - Prioritaire";

          //SOUS-REQUETE : Récupération du nombre d'états du client, affecté à un commercial
          $sqlSub1  = "SELECT COUNT(*)";
          $sqlSub1 .= " FROM suivi_client sc1 USE INDEX (userid)";
          $sqlSub1 .= " WHERE sc1.userid = u.userid";
          $sqlSub1 .= " AND sc1.suivi NOT LIKE '%[infos_client]%'";
          // $sqlSub1 .= " AND sc1.commercial ".$sclcommercialsigne." ''";
		  $sqlSub1 .= " AND sc1.commercial <> ''";
		  $sqlSub1 .= " AND sc1.etat <>1 ";

          //SOUS-REQUETE : Récupération du statut de la dernière commande, non supprimée, passée par le client
          $sqlSub2  = "SELECT valide";
          $sqlSub2 .= " FROM facture USE INDEX (userid)";
          $sqlSub2 .= " WHERE userid = u.userid";
          $sqlSub2 .= " AND supprimee = '0'";
          $sqlSub2 .= " ORDER BY heurefacture DESC";
          $sqlSub2 .= " LIMIT 1";
		  

          //SOUS-REQUETE : Comptage du nombre de commandes passées par le client
          $sqlSub3  = "SELECT COUNT(*)";
          $sqlSub3 .= " FROM facture USE INDEX (userid)";
          $sqlSub3 .= " WHERE userid = u.userid";
          $sqlSub3 .= " AND supprimee = '0'";
          
		  /*
          //Récupération de la date du dernier mail envoyé
          $sqlSub4 = "SELECT date";
          $sqlSub4 .= " FROM mail_outbox";
          $sqlSub4 .= " WHERE suiviid = u.userid";
          $sqlSub4 .= " AND suivitype LIKE 'suivi_client'";
          $sqlSub4 .= " ORDER BY date DESC";
          $sqlSub4 .= " LIMIT 1";
		  */
		  $sqlSub4 = " u.date_mail_outbox_suivi_client ";
          
          //Récupération de la date du dernier suivi client
          $sqlSub5  = "SELECT date";
          $sqlSub5 .= " FROM suivi_client FORCE INDEX (userid)";
          $sqlSub5 .= " WHERE userid = u.userid";
          $sqlSub5 .= " AND suivi NOT LIKE '[infos_client]%'";
          $sqlSub5 .= " ORDER BY date DESC";
          $sqlSub5 .= " LIMIT 1";
		  
		  //SOUS-REQUETE : Sélection du dernier rappel (non caché) du client
          $sqlSub6  = "SELECT rappel";
          $sqlSub6 .= " FROM suivi_client FORCE INDEX (userid)";
          $sqlSub6 .= " WHERE userid = u.userid";
          $sqlSub6 .= " AND suivi NOT LIKE '[infos_client]%'";
		  $sqlSub6 .= " AND hide ='0'";
          $sqlSub6 .= " ORDER BY date DESC";
          $sqlSub6 .= " LIMIT 1";
		  

          //REQUETE de filtration à insérer dans la requête générale
          $sqlInsert  = " AND (".$sqlSub1.") = 0"; // Client affecté à un commercial, et qui n'a aucun "état" de suivi (pas encore traité) 
          $sqlInsert .= " AND ((".$sqlSub2.") NOT IN (1,5,7,8) OR (".$sqlSub3.") = 0)"; //Statut de dernière commande du client : "validée" ou "expédiée" ou "validée SP" ou "expédiée SP OU Aucune commande ni devis passé"
          $sqlInsert .= " AND UNIX_TIMESTAMP(dateinscription) > ".(time()-63072000); //Limite sur les 2 dernières années
          $sqlInsert .= " AND ((".$sqlSub4.") > (".$sqlSub5.")"; //Le dernier suivi est un email envoyé au client
          $sqlInsert .= " OR ";
         // $sqlInsert .= "(".$sqlSub4.") IS NULL)"; //Aucun email envoyé
		  $sqlInsert .= " ".$sqlSub4."=0 )"; //Aucun email envoyé
		  $sqlInsert .= " AND (".$sqlSub6.")< ".time(); // date de rappel pas encore passée

          //REQUETE de filtration
          $sqlInsert2  = " AND (("; //Dernière commande passée autrement que par virement, chèque, mandat cash
          $sqlInsert2 .= "    SELECT f3.moyenid";
          $sqlInsert2 .= "    FROM facture f3 USE INDEX (userid)";
          $sqlInsert2 .= "    WHERE f3.userid = u.userid";
          $sqlInsert2 .= "    AND f3.supprimee = '0'";
          $sqlInsert2 .= "    ORDER BY f3.heurefacture DESC";
          $sqlInsert2 .= "    LIMIT 0,1";
          $sqlInsert2 .= ") NOT IN (2,4,5)"; 
          $sqlInsert2 .= " OR ("; //OU aucune commande passée
          $sqlInsert2 .= "    SELECT COUNT(*)";
          $sqlInsert2 .= "    FROM facture f4 USE INDEX (userid)";
          $sqlInsert2 .= "    WHERE f4.userid = u.userid";
          $sqlInsert2 .= "    AND f4.supprimee = '0'";
          $sqlInsert2 .= ") = 0)"; 

          //REQUETE de sélection du montant total de la dernière commande
          $sqlGet  = "SELECT SUM(lf.qte * lf.prix) + f2.prixport - f2.montantcadeau";
          $sqlGet .= " FROM facture f2 USE INDEX (userid)";
          $sqlGet .= " INNER JOIN lignefacture lf ON (lf.factureid = f2.facturid)";
          $sqlGet .= " WHERE f2.userid = u.userid";
          $sqlGet .= " AND f2.supprimee = '0'";
          $sqlGet .= " GROUP BY f2.facturid";
          $sqlGet .= " ORDER BY f2.heurefacture DESC";
          $sqlGet .= " LIMIT 1";
          
		  
		  $sqlUnique .= " AND u.userid NOT IN (".$liste_ids.") ";
		  
		  
          //REQUETE FINALE
          $sql  = "SELECT u.userid, u.nom, u.prenom, u.raisonsociale, u.userid, DATE_FORMAT(u.dateinscription, '%d/%m/%Y<br>%Hh%i') as date_inscription, u.commercial, u.echeance_projet, u.budget_projet, u.vip, IFNULL((".$sqlGet."), u.budget_projet) as montantTotalCmd, IF(u.vip = 1, 1, 0) as rang, u.vip as tag, 1 as rappel";
          $sql .= " FROM utilisateur AS u  USE INDEX (commercial_2)";
          $sql .= " WHERE 1";
          $sql .= " AND u.code411 = ''"; //Ne pas prendre en compte les fournisseurs
		  $sql .= "	AND NOT conseils='non' "; // ne pas prendre les nouvelles inscriptions sans conseils
          $sql .= " AND u.utilisateur_admin = 0"; //Ne pas prendre en compte les comptes administrateurs
          $sql .= $sqlpour != '' ? " AND ".$sqlpour : '';
          $sql .= $sqltype != '' ? " AND ".$sqltype : '';
          $sql .= $sqltypesuivi != '' ? " AND ".$sqltypesuivi : '';
          $sql .= $sqlvip != '' ? " AND ".$sqlvip : '';
          $sql .= " AND ".$sqlconseils;
		  $sql .= $sqlrappel;
		  $sql .= $sqletat;
          $sql .= $sqlInsert;
          $sql .= $sqlInsert2;
		  $sql .= $sqlUnique;
          $sql .= " GROUP BY u.userid";
          
          break;

     case 'nrappel': //Contacts en cours sans rappel - Prioritaire

          $intitule_tableau = "Contacts en cours sans rappel - Prioritaire";
		  
		  /*
          //Création d'une table temporaire pour éviter la requête trop lourde
          $sql = "CREATE TEMPORARY TABLE mail_outbox_bis (";
          $sql .= "date INT";
          $sql .= ", suiviid INT";
          $sql .= ");";
          $DB_site2->query($sql);

          //Insertion des donnés qui nous intéressent
          $sql = "INSERT INTO mail_outbox_bis";
          $sql .= " SELECT date, suiviid";
          $sql .= " FROM mail_outbox";
          $sql .= " WHERE suivitype = 'suivi_client'";
          $DB_site2->query($sql);
		  
		  $DB_site2->query("ALTER TABLE  `mail_outbox_bis` ADD INDEX (  `suiviid` ) ;");
		  $DB_site2->query("ALTER TABLE  `mail_outbox_bis` ADD INDEX (  `date` ) ;");
		  

          //Création d'une table temporaire pour éviter la requête trop lourde
		  
          $sql = "CREATE TEMPORARY TABLE mail_inbox_bis (";
          $sql .= "date INT";
          $sql .= ", suiviid INT";
          $sql .= ");";
          $DB_site2->query($sql);

          //Insertion des donnés qui nous intéressent
          $sql = "INSERT INTO mail_inbox_bis";
          $sql .= " SELECT date, suiviid";
          $sql .= " FROM mail_inbox";
          $sql .= " WHERE suivitype = 'suivi_client'";
          $DB_site2->query($sql);
		  
		  $DB_site2->query("ALTER TABLE  `mail_inbox_bis` ADD INDEX (  `suiviid` ) ;");
		  $DB_site2->query("ALTER TABLE  `mail_inbox_bis` ADD INDEX (  `date` ) ;");
		  */
		  
		  
		  
		  
          //SOUS-REQUETE : Sélection du dernier "état" du client
          $sqlSub1 = "SELECT etat";
          $sqlSub1 .= " FROM suivi_client FORCE INDEX (userid)";
          $sqlSub1 .= " WHERE userid = u.userid";
          $sqlSub1 .= " AND suivi NOT LIKE '[infos_client]%'";
          $sqlSub1 .= " ORDER BY date DESC";
          $sqlSub1 .= " LIMIT 1";
          
          //SOUS-REQUETE : Sélection du dernier rappel du client
          $sqlSub2 = "SELECT rappel";
          $sqlSub2 .= " FROM suivi_client FORCE INDEX (userid)";
          $sqlSub2 .= " WHERE userid = u.userid";
          $sqlSub2 .= " AND suivi NOT LIKE '[infos_client]%'";
          $sqlSub2 .= " ORDER BY date DESC";
          $sqlSub2 .= " LIMIT 1";
          
          //SOUS-REQUETE : Sélection du statut de la dernière commande du client, non supprimée
          $sqlSub3 = "SELECT valide";
          $sqlSub3 .= " FROM facture USE INDEX (userid)";
          $sqlSub3 .= "  WHERE userid = u.userid";
          $sqlSub3 .= "  AND supprimee = '0'";
          $sqlSub3 .= "  ORDER BY heurefacture DESC";
          $sqlSub3 .= "  LIMIT 1";

          //SOUS-REQUETE : Sélection du nombre de commande du client
          $sqlSub4 = "SELECT COUNT(facturid)";
          $sqlSub4 .= " FROM facture USE INDEX (userid)";
          $sqlSub4 .= " WHERE userid = u.userid";
          $sqlSub4 .= " AND supprimee = '0'";

          //SOUS-REQUETE : Sélection du statut de la dernière commande du client, non supprimée et non de type "sav"
          $sqlSub5 = "SELECT valide";
          $sqlSub5 .= " FROM facture USE INDEX (userid)";
          $sqlSub5 .= " WHERE userid = u.userid";
          $sqlSub5 .= " AND provenance <> 'sav'";
          $sqlSub5 .= " AND supprimee = '0'";
          $sqlSub5 .= " ORDER BY heurefacture DESC";
          $sqlSub5 .= " LIMIT 1";

          //SOUS-REQUETE : Sélection de la date de la dernière commande du client, non supprimée et non de type "sav"
          $sqlSub6 = "SELECT heurefacture";
          $sqlSub6 .= " FROM facture USE INDEX (userid)";
          $sqlSub6 .= " WHERE userid = u.userid";
          $sqlSub6 .= " AND provenance <> 'sav'";
          $sqlSub6 .= " AND supprimee = '0'";
          $sqlSub6 .= " ORDER BY heurefacture DESC";
          $sqlSub6 .= " LIMIT 1";

          //SOUS-REQUETE : Sélection de la date du dernier suivi client
          $sqlSub7 = "SELECT date";
          $sqlSub7 .= " FROM suivi_client FORCE INDEX (userid)";
          $sqlSub7 .= " WHERE userid = u.userid";
          //$sqlSub7 .= " AND suivi NOT LIKE '[infos_client]%'";
          $sqlSub7 .= " ORDER BY date DESC";
          $sqlSub7 .= " LIMIT 1";
          
          //SOUS-REQUETE : Récupération du nombre d'états du client, affecté à un commercial
          $sqlSub8 = "SELECT COUNT(sc1.etat)";
          $sqlSub8 .= " FROM suivi_client AS sc1 USE INDEX (userid)";
          $sqlSub8 .= " WHERE sc1.userid = u.userid";
          //$sqlSub8 .= " AND sc1.suivi NOT LIKE '[infos_client]%'";
          $sqlSub8 .= " AND sc1.commercial ".$sclcommercialsigne." '' ";
          
          //Récupération de la date du dernier mail envoyé
		  /*
          $sqlSub9 = "SELECT date";
          // $sqlSub9 .= " FROM mail_outbox_bis";
		  $sqlSub9 .= " FROM mail_outbox_bis";
          $sqlSub9 .= " WHERE suiviid = u.userid";
		 // $sqlSub9 .= " AND suivitype LIKE 'suivi_client'";
          $sqlSub9 .= " ORDER BY date DESC";
          $sqlSub9 .= " LIMIT 1";
		*/
		  $sqlSub9 = " u.date_mail_outbox_suivi_client ";
		  
          //Récupération de la date du dernier mail reçu
		  /*
          $sqlSub12 = "SELECT date";
        //  $sqlSub12 .= " FROM mail_inbox_bis";
		  $sqlSub12 .= " FROM mail_inbox_bis";
          $sqlSub12 .= " WHERE suiviid = u.userid";
		//  $sqlSub12 .= " AND suivitype LIKE 'suivi_client' ";
          $sqlSub12 .= " ORDER BY date DESC";
          $sqlSub12 .= " LIMIT 1";
		  */
		  $sqlSub12 = " u.date_mail_inbox_suivi_client ";
          
          //SOUS-REQUETE : Récupération du nombre d'états du client, affecté à un commercial
          $sqlSub10 = "SELECT COUNT(*)";
          $sqlSub10 .= " FROM suivi_client AS sc1 USE INDEX (userid)";
          $sqlSub10 .= " WHERE sc1.userid = u.userid";
          $sqlSub10 .= " AND sc1.commercial ".$sclcommercialsigne." ''";
          
          //SOUS-REQUETE : Récupération du nombre d'états du client, affecté à un commercial
          $sqlSub11 = "SELECT etat";
          $sqlSub11 .= " FROM suivi_client FORCE INDEX (userid)";
          $sqlSub11 .= " WHERE userid = u.userid";
          $sqlSub11 .= " ORDER BY date DESC";
          $sqlSub11 .= " LIMIT 0,1";
          
          //SOUS-REQUETE : Sélection de la date du dernier suivi client
          $sqlSub13 = "SELECT date";
          $sqlSub13 .= " FROM suivi_client FORCE INDEX (userid)";
          $sqlSub13 .= " WHERE userid = u.userid";
          $sqlSub13 .= " AND suivi NOT LIKE '[infos_client]%'";
          $sqlSub13 .= " ORDER BY date DESC";
          $sqlSub13 .= " LIMIT 1";

          //REQUETE de sélection du montant total de la dernière commande
          $sqlGet = "SELECT SUM(lf.qte * lf.prix) + f2.prixport - f2.montantcadeau";
          $sqlGet .= " FROM facture f2 USE INDEX (userid)";
          $sqlGet .= " INNER JOIN lignefacture lf ON (lf.factureid = f2.facturid)";
          $sqlGet .= " WHERE f2.userid = u.userid";
          $sqlGet .= " AND f2.supprimee = '0'";
          $sqlGet .= " GROUP BY f2.facturid";
          $sqlGet .= " ORDER BY f2.heurefacture DESC";
          $sqlGet .= " LIMIT 1";
          
		  
          $sqlInsert = " AND ((";
          $sqlInsert .=        "((".$sqlSub1.") IN (1,2,3) OR (".$sqlSub8.") = 0) AND ((".$sqlSub3.") NOT IN (1,5,7,8) OR (".$sqlSub4.") = 0)"; //(Dernier état = 1,2,3 OU aucun suivi) ET sans rappel ET (avec dernière commande non validée ou expédiée OU sans aucune commande)
          $sqlInsert .= " ) OR (";
          $sqlInsert .= "      (".$sqlSub5.") NOT IN (1,5,7,8) AND (".$sqlSub6.") > (".$sqlSub7.")"; //Avec dernière commande non validée ou expédiée ET date de cette commande plus récente que date du dernier suivi
          $sqlInsert .= " ))";
          $sqlInsert .= " AND (".$sqlSub10.") > 0"; // Client affecté à un commercial
          $sqlInsert .= " AND ((".$sqlSub2.") = 0 OR (".$sqlSub9.") > (".$sqlSub13.") OR (".$sqlSub12.") > (".$sqlSub13."))"; // Client sans rappel
          //$sqlInsert .= " AND (".$sqlSub11.") = 1"; // Client toujours en état "en cours"
          
		  
		  
          //REQUETE de filtration
          $sqlInsert2 = " AND (("; //Dernière commande passée autrement que par virement, chèque, mandat cash, facilités de paiement, sofinco
          $sqlInsert2 .= "    SELECT f3.moyenid";
          $sqlInsert2 .= "    FROM facture f3 USE INDEX (userid)";
          $sqlInsert2 .= "    WHERE f3.userid = u.userid";
          $sqlInsert2 .= "    AND f3.supprimee = '0'";
          $sqlInsert2 .= "    ORDER BY f3.heurefacture DESC";
          $sqlInsert2 .= "    LIMIT 0,1";
          $sqlInsert2 .= ") NOT IN (2,4,5,17,18,99)";
          $sqlInsert2 .= " OR ("; //OU dernier statut de commande annulé
          $sqlInsert2 .= "    SELECT f5.valide";
          $sqlInsert2 .= "    FROM facture f5 USE INDEX (userid)";
          $sqlInsert2 .= "    WHERE f5.userid = u.userid";
          $sqlInsert2 .= "    AND f5.supprimee = '0'";
          $sqlInsert2 .= "    ORDER BY f5.heurefacture DESC";
          $sqlInsert2 .= "    LIMIT 0,1";
          $sqlInsert2 .= ") = 2"; 
          $sqlInsert2 .= " OR ("; //OU aucune commande passée
          $sqlInsert2 .= "    SELECT COUNT(*)";
          $sqlInsert2 .= "    FROM facture f4 USE INDEX (userid)";
          $sqlInsert2 .= "    WHERE f4.userid = u.userid";
          $sqlInsert2 .= "    AND f4.supprimee = '0'";
          $sqlInsert2 .= ") = 0)"; 
          

          //IF((".$sqlGet.")=0, u.budget_projet, (".$sqlGet.")) as montantTotalCmd
          //REQUETE FINALE
          $sql = "SELECT u.userid, u.nom, u.prenom, u.raisonsociale, DATE_FORMAT(u.dateinscription, '%d/%m/%Y<br>%Hh%i') as date_inscription, u.commercial, u.echeance_projet, u.budget_projet, u.vip, IFNULL((".$sqlGet."), u.budget_projet) as montantTotalCmd, IF(u.vip = 1, 1, 0) as rang, u.vip as tag,
				(".$sqlSub1.") as rappel";
          $sql .= " FROM utilisateur AS u  USE INDEX (commercial_2)";
          $sql .= " WHERE 1";
          $sql .= " AND u.code411 = ''"; //Ne pas prendre en compte les fournisseurs
          $sql .= " AND u.utilisateur_admin = 0"; //Ne pas prendre en compte les comptes administrateurs
          $sql .= $sqlpour != '' ? " AND ".$sqlpour : '';
          $sql .= $sqltype != '' ? " AND ".$sqltype : '';
          $sql .= $sqltypesuivi != '' ? " AND ".$sqltypesuivi : '';
          $sql .= $sqlvip != '' ? " AND ".$sqlvip : '';
          $sql .= " AND ".$sqlconseils;
		  $sql .= $sqlrappel;
		  $sql .= $sqletat;
          $sql .= $sqlInsert;
          $sql .= $sqlInsert2;
          $sql .= " GROUP BY u.userid";
          
		  // echo $sql;
		  
          break;
     
     case 'norap':

          $intitule_tableau      = "Dossiers sous-traités";

          //SOUS-REQUETE : Récupération du dernier état du client
          $sqlSub1 = "SELECT etat";
          $sqlSub1 .= " FROM suivi_client FORCE INDEX (userid)";
          $sqlSub1 .= " WHERE userid = u.userid";
          //$sqlSub1 .= " AND suivi NOT LIKE '[infos_client]%'";
          $sqlSub1 .= " ORDER BY date desc";
          $sqlSub1 .= " LIMIT 1";

          $sqlSub2 = "SELECT valide";
          $sqlSub2 .= " FROM facture USE INDEX (userid)";
          $sqlSub2 .= " WHERE userid = u.userid";
          $sqlSub2 .= " AND supprimee = '0'";
          $sqlSub2 .= " ORDER BY heurefacture desc";
          $sqlSub2 .= " LIMIT 1";

          $sqlSub3 = "SELECT COUNT(*)";
          $sqlSub3 .= " FROM facture USE INDEX (userid)";
          $sqlSub3 .= " WHERE userid = u.userid";
          $sqlSub3 .= " AND supprimee = '0'";

          $sqlInsert = " AND (".$sqlSub1.") = 4";
          $sqlInsert .= " AND (";
          $sqlInsert .=        "(".$sqlSub2.") NOT IN (1,5,7,8)";
          $sqlInsert .=        " OR ";
          $sqlInsert .=        "(".$sqlSub3.") = 0";
          $sqlInsert .= " )";
          
          //REQUETE de filtration
          $sqlInsert2 = " AND (("; //Dernière commande passée autrement que par virement, chèque, mandat cash
          $sqlInsert2 .= "    SELECT f3.moyenid";
          $sqlInsert2 .= "    FROM facture f3  USE INDEX (userid)";
          $sqlInsert2 .= "    WHERE f3.userid = u.userid";
          $sqlInsert2 .= "    AND f3.supprimee = '0'";
          $sqlInsert2 .= "    ORDER BY f3.heurefacture DESC";
          $sqlInsert2 .= "    LIMIT 0,1";
          $sqlInsert2 .= ") NOT IN (2,4,5)"; 
          $sqlInsert2 .= " OR ("; //OU aucune commande passée
          $sqlInsert2 .= "    SELECT COUNT(*)";
          $sqlInsert2 .= "    FROM facture f4 USE INDEX (userid)";
          $sqlInsert2 .= "    WHERE f4.userid = u.userid";
          $sqlInsert2 .= "    AND f4.supprimee = '0'";
          $sqlInsert2 .= ") = 0)"; 
          
          //REQUETE de sélection du montant total de la dernière commande
          $sqlGet = "SELECT SUM(lf.qte * lf.prix) + f2.prixport - f2.montantcadeau";
          $sqlGet .= " FROM facture f2";
          $sqlGet .= " INNER JOIN lignefacture lf ON (lf.factureid = f2.facturid)";
          $sqlGet .= " WHERE f2.userid = u.userid";
          $sqlGet .= " AND f2.supprimee = '0'";
          $sqlGet .= " GROUP BY f2.facturid";
          $sqlGet .= " ORDER BY f2.heurefacture DESC";
          $sqlGet .= " LIMIT 1";
          
          //REQUETE FINALE
          $sql = "SELECT u.userid, u.nom, u.prenom, u.raisonsociale, DATE_FORMAT(u.dateinscription, '%d/%m/%Y<br>%Hh%i') as date_inscription, u.commercial, u.echeance_projet, u.budget_projet, u.vip, IFNULL((".$sqlGet."), u.budget_projet) as montantTotalCmd, IF(u.vip = 1, 1, 0) as rang, u.vip as tag, '0' as rappel";
          $sql .= " FROM utilisateur AS u  USE INDEX (commercial_2)";
          $sql .= " WHERE 1";
          $sql .= " AND u.code411 = ''"; //Ne pas prendre en compte les fournisseurs
          $sql .= " AND u.utilisateur_admin = 0"; //Ne pas prendre en compte les comptes administrateurs
          $sql .= $sqlpour != '' ? " AND ".$sqlpour : '';
          $sql .= $sqltype != '' ? " AND ".$sqltype : '';
          $sql .= $sqltypesuivi != '' ? " AND ".$sqltypesuivi : '';
          $sql .= $sqlvip != '' ? " AND ".$sqlvip : '';
          $sql .= " AND ".$sqlconseils;
		  $sql .= $sqlrappel;
		  $sql .= $sqletat;
          $sql .= $sqlInsert;
          $sql .= $sqlInsert2;
          $sql .= " GROUP BY u.userid";
          
          break;
     
     case 'retard':
     case 'futur':
     case 'demain':
     case 'jour':
          
          if($_GET['tableau'] == 'retard'){
               $intitule_tableau = "Rappels en retard";
               $date_debut = mktime(0,0,0,date('n'),date('j'),date('Y')-2); //Début de jour précédent
               $date_fin   = mktime(23,59,59,date('n'),date('j')-1,date('Y')); //Début de J - 2ans
          } else if($_GET['tableau'] == 'futur'){
               $intitule_tableau = "Rappels futurs";
               $date_debut = mktime(0,0,0,date('n'),date('j')+2,date('Y')); //Début de surlendemain
               $date_fin   = mktime(23,59,59,date('n'),date('j')+182,date('Y')); //Fin de J + 182
          } else if($_GET['tableau'] == 'demain'){
               $intitule_tableau = "Rappels de demain";
               $date_debut = mktime(0,0,0,date('n'),date('j')+1,date('Y')); //Début de jour suivant
               $date_fin   = mktime(23,59,59,date('n'),date('j')+1,date('Y')); //Fin de jour suivant
          } else if($_GET['tableau'] == 'jour'){
               $intitule_tableau   = "Rappels du jour";
               $date_debut = mktime(0,0,0); //Début de journée atuelle
               $date_fin   = mktime(23,59,59); //Fin de journée actuelle
          }
          
          //REQUETE de sélection du montant total de la dernière commande
          $sqlGet = "SELECT SUM(lf.qte * lf.prix) + f2.prixport - f2.montantcadeau";
          $sqlGet .= " FROM facture f2";
          $sqlGet .= " INNER JOIN lignefacture lf ON (lf.factureid = f2.facturid)";
          $sqlGet .= " WHERE f2.userid = u.userid";
          $sqlGet .= " AND f2.supprimee = '0'";
          $sqlGet .= " GROUP BY f2.facturid";
          $sqlGet .= " ORDER BY f2.heurefacture DESC";
          $sqlGet .= " LIMIT 1";
          
          //SOUS-REQUETE : Sélection de la date du dernier rappel de la fiche client
          $sqlSub = "SELECT rappel";
          $sqlSub .= " FROM suivi_client FORCE INDEX (userid)";
          $sqlSub .= " WHERE userid = u.userid";
          $sqlSub .= " AND suivi NOT LIKE '[infos_client]%'";
          $sqlSub .= " ORDER BY date DESC";
          $sqlSub .= " LIMIT 0,1";
          
          //SOUS-REQUETE : Sélection de la date du dernier rappel de la fiche client
          $sqlSub2 = "SELECT jour";
          $sqlSub2 .= " FROM suivi_client FORCE INDEX (userid)";
          $sqlSub2 .= " WHERE userid = u.userid";
          $sqlSub2 .= " AND suivi NOT LIKE '[infos_client]%'";
          $sqlSub2 .= " ORDER BY date DESC";
          $sqlSub2 .= " LIMIT 0,1";
          
          //SOUS-REQUETE : Sélection de la date de la dernière commande du client, validé ou expédiée, non supprimée et non de type "sav"
          $sqlSub3 = "SELECT MAX(heurefacture)";
          $sqlSub3 .= " FROM facture USE INDEX (userid)";
          $sqlSub3 .= " WHERE userid = u.userid";
          $sqlSub3 .= " AND provenance <> 'sav'";
          $sqlSub3 .= " AND supprimee = '0'";
          $sqlSub3 .= " AND valide IN (1,5,7,8)";
          
          //SOUS-REQUETE : Sélection de la date du dernier rappel de la fiche client
          $sqlSub4 = "SELECT MAX(date)";
          $sqlSub4 .= " FROM suivi_client FORCE INDEX (userid)";
          $sqlSub4 .= " WHERE userid = u.userid";
          $sqlSub4 .= " AND suivi NOT LIKE '[infos_client]%'";
          
          $aChampsSQL = array(
              "u.nom",
              "u.prenom",
              "u.raisonsociale",
              "u.userid",
              "DATE_FORMAT(u.dateinscription, '%d/%m/%Y<br>%Hh%i') as date_inscription",
              "u.commercial",
              "u.echeance_projet",
              "u.vip",
              "IFNULL((".$sqlGet."), u.budget_projet) as montantTotalCmd",
              "IF(u.vip = 1, 1, 0) as rang",
              "u.vip as tag",
              "(".$sqlSub.") as rappel",
              "(".$sqlSub2.") as jour",
              "'suivi' as type_requete"
          );
          
          $sql = "SELECT ".implode(',', $aChampsSQL);
          $sql .= " FROM utilisateur AS u  USE INDEX (commercial_2)";
          $sql .= " WHERE 1";
          $sql .= " AND u.code411 = ''";
          $sql .= " AND u.utilisateur_admin = 0";
          $sql .= " AND ("; //Récupération du dernier rappel dans une certaine tranche de dates
          $sql .= "      SELECT rappel";
          $sql .= "      FROM suivi_client FORCE INDEX (userid)";
          $sql .= "      WHERE userid = u.userid";
          $sql .= "      AND suivi NOT LIKE '[infos_client]%'";
          $sql .= "      ORDER BY date DESC";
          $sql .= "      LIMIT 0,1";
          $sql .= " ) BETWEEN ".$date_debut." AND ".$date_fin;
          $sql .= " AND ("; //Récupération du dernier rappel dans une certaine tranche de dates
          $sql .= "      SELECT etat";
          $sql .= "      FROM suivi_client FORCE INDEX (userid)";
          $sql .= "      WHERE userid = u.userid";
          $sql .= "      AND suivi NOT LIKE '[infos_client]%'";
          $sql .= "      ORDER BY date DESC";
          $sql .= "      LIMIT 0,1";
          $sql .= " ) IN ".$sqletat." ";
		  $sql .= $sqlrappel;
          if($_GET['tableau'] != 'jour' && $_GET['tableau'] != 'futur'){
               $sql .= " AND (";
               $sql .= "      (".$sqlSub3.") < (".$sqlSub4.")"; //Dernière commande plus ancienne que date à laquelle le dernier rappel a été posé
               $sql .= "      OR ";
               $sql .= "      (".$sqlSub3.") IS NULL"; //Aucune commande
               $sql .= ")";
          }
          $sql .= $sqlpour != '' ? " AND ".$sqlpour : '';
          $sql .= $sqltype != '' ? " AND ".$sqltype : '';
          $sql .= $sqltypesuivi != '' ? " AND ".$sqltypesuivi : '';
          $sql .= $sqlvip != '' ? " AND ".$sqlvip : '';
          $sql .= " AND ".$sqlconseils;

          /*$sql .= " AND (";
          $sql .= "      (".$sqlSub5.") < (".$sqlSub4.")"; //Dernier mail envoyé plus ancien que date à laquelle le dernier rappel a été posé
          $sql .= "      OR ";
          $sql .= "      (".$sqlSub5.") IS NULL"; //Aucun mail envoyé au client
          $sql .= ")";
          $sql .= " AND (";
          $sql .= "      (".$sqlSub6.") < (".$sqlSub4.")"; //Dernier mail reçu plus ancien que date à laquelle le dernier rappel a été posé
          $sql .= "      OR ";
          $sql .= "      (".$sqlSub6.") IS NULL"; //Aucun mail reçu du client
          $sql .= ")";*/
          
          break;
}

//Par défaut, tri par "statut" (vip > normal)
if(!isset($_GET['tri'])) $_GET['tri'] = 'tag';
if(!isset($_GET['order'])) $_GET['order'] = 'desc';

//Défini si la flèche de tri doit être affiché pour chaque colonne. Par défaut, caché
$tabAfficheTri['tag']             = false;
$tabAfficheTri['societe']         = false;
$tabAfficheTri['nom']             = false;
$tabAfficheTri['dateinscr']       = false;
$tabAfficheTri['commercial']      = false;
$tabAfficheTri['totalcmd']        = false;
$tabAfficheTri['totalpjt']        = false;
$tabAfficheTri['date_rappel']     = false;
$tabAfficheTri['echeance_projet'] = false;

//Défini l'ordre de tri par défaut pour chaque colonne, asc partout sauf pour tag qui est le tri de base
$tabOrder['societe']         = 'asc';
$tabOrder['nom']             = 'asc';
$tabOrder['dateinscr']       = 'asc';
$tabOrder['commercial']      = 'asc';
$tabOrder['totalcmd']        = 'asc';
$tabOrder['totalpjt']        = 'asc';
$tabOrder['echeance_projet'] = 'asc';

//Attribution du bon tri
switch($_GET['tri']){
     case 'tag':
          $tabAfficheTri['tag'] = true;
          $tabOrder['tag']      = $_GET['order'] == 'asc' ? 'desc' : 'asc';
          $sql .= " ORDER BY rang ".strtoupper($_GET['order']);
          break;
     case 'societe':
          $tabAfficheTri['societe'] = true;
          $tabOrder['societe']      = $_GET['order'] == 'asc' ? 'desc' : 'asc';
          $sql .= " ORDER BY raisonsociale ".strtoupper($_GET['order']);
          break;
     case 'nom':
          $tabAfficheTri['nom'] = true;
          $tabOrder['nom']      = $_GET['order'] == 'asc' ? 'desc' : 'asc';
          $sql .= " ORDER BY nom ".strtoupper($_GET['order']);
          break;
     case 'dateinscr':
          $tabAfficheTri['dateinscr'] = true;
          $tabOrder['dateinscr']      = $_GET['order'] == 'asc' ? 'desc' : 'asc';
          $sql .= " ORDER BY dateinscription ".strtoupper($_GET['order']);
          break;
     case 'commercial':
          $tabAfficheTri['commercial'] = true;
          $tabOrder['commercial']      = $_GET['order'] == 'asc' ? 'desc' : 'asc';
          $sql .= " ORDER BY commercial ".strtoupper($_GET['order']);
          break;
     case 'totalcmd':
          $tabAfficheTri['totalcmd'] = true;
          $tabOrder['totalcmd']      = $_GET['order'] == 'asc' ? 'desc' : 'asc';
          $sql .= " ORDER BY montantTotalCmd ".strtoupper($_GET['order']);
          break;
     /*case 'totalpjt':
          $tabAfficheTri['totalpjt'] = true;
          $tabOrder['totalpjt']      = $_GET['order'] == 'asc' ? 'desc' : 'asc';
          $sql .= " ORDER BY montantTotalCmd ".strtoupper($_GET['order']);
          break;*/     
     case 'date_rappel':
          $tabAfficheTri['date_rappel'] = true;
          $tabOrder['date_rappel']      = $_GET['order'] == 'asc' ? 'desc' : 'asc';
          $sql .= " ORDER BY rappel ".strtoupper($_GET['order']).", jour ".strtoupper($_GET['order']);
          break;
     case 'echeance_projet':
          $tabAfficheTri['echeance_projet'] = true;
          $tabOrder['echeance_projet']      = $_GET['order'] == 'asc' ? 'desc' : 'asc';
          $sql .= " ORDER BY echeance_projet ".strtoupper($_GET['order']);
          break;
}

switch($_GET['tableau']){
     case 'nouveau':
     case 'nrappel':
     case 'norap':
          $tabOrder['tag']    = 'desc';
          $tabOrder['rappel'] = 'asc';
          if($_GET['tri'] != 'totalcmd') $sql .= ", montantTotalCmd DESC";
          $display_default = 'tag';
          break;
     case 'jour':
     case 'retard':
     case 'demain':
     case 'futur':
          $tabOrder['tag']    = 'asc';
          $tabOrder['rappel'] = 'desc';
          if($_GET['tri'] != 'date_rappel') $sql .= ", rappel";
          $display_default = 'date_rappel';
          break;
}

//Finition de la requête
$sql .= ", u.userid DESC";
// echo $sql;
$rq = $DB_site2->query($sql);
?>
<a name="<?php echo $_GET['tableau']; ?>"></a>
<table class="table_alternate_color" align="left" style="width: 100%;">
     <tr>
          <td class="type_fournisseur" align="center" colspan="12" style="font-size: 15px; padding: 5px 0;">
               <?php
			   /*
               switch($_GET['display']){
                    case 'block':
                         ?>
                         <span style="float:left; font-weight: bold; font-size: 13px; padding: 0 0 0 10px;line-height: 23px;"><a href="javascript:void(0);" onclick="ajaxReloadTri('<?php echo $_GET['tableau']; ?>','<?php echo $display_default; ?>','<?php echo $tabOrder['tag']; ?>','none')" style="color: white;">Réduire</a></span>
                         <?php
                         break;
                    case 'none':
                         ?>
                         <span style="float:left; font-weight: bold; font-size: 13px; padding: 0 0 0 10px;line-height: 23px;"><a href="javascript:void(0);" onclick="ajaxReloadTri('<?php echo $_GET['tableau']; ?>','<?php echo $display_default; ?>','<?php echo $tabOrder['tag']; ?>','block')" style="color: white;">Agrandir</a></span>
                         <?php
                         break;
               }
			   */
               ?>
                         <b><?php echo $intitule_tableau; ?></b>
				<?php
				/*
						 <img src="images/icon/refresh.png" onclick="ajaxReloadTri('<?php echo $_GET['tableau']; ?>','<?php echo $display_default; ?>','<?php echo $tabOrder['tag']; ?>','block')" style="cursor:pointer; width: 26px; margin: 0 0 0 10px; vertical-align: -8px;">
				*/
				?>
          </td>
     </tr>
     <?php if($_GET['tableau'] == 'nouveau'){ ?>
	 <tr class="flist">
          <td align="center"><strong>Tag</strong></a></td>
          <td align="center"><strong>Société</strong></a></td>
          <td align="center"><strong>Client</strong></a></td>
		<td align="center"><strong>Projet</strong></a></td>
          <td align="center"><strong>Date inscr.</strong></a></td>
          <td align="center"><strong>Etat</strong></td>
          <td align="center"><strong>Commercial</strong></a></td>
          <td align="center"><strong>Date échéance</strong></a></td>
          <td align="center"><strong>Date rappel</strong></a></td>
          <td align="center"><strong>Total cmd</strong></a></td>
          <td align="center"><strong>Budget PJT</strong></a></td>
          <td align="center"></td>
     </tr>
	 <?php } else { ?>
     <tr class="flist">
          <td align="center"><a href="javascript:void(0);" onclick="ajaxReloadTri('<?php echo $_GET['tableau']; ?>','tag','<?php echo $tabOrder['tag']; ?>','block')"><strong>Tag</strong></a><?php if($tabAfficheTri['tag']){ ?><br><span style="color:red;"><?php echo $_GET['order'] == 'desc' ? '&#9660;' : '&#9650;'; ?></span><?php } ?></td>
          <td align="center"><a href="javascript:void(0);" onclick="ajaxReloadTri('<?php echo $_GET['tableau']; ?>','societe','<?php echo $tabOrder['societe']; ?>','block')"><strong>Société</strong></a><?php if($tabAfficheTri['societe']){ ?><br><span style="color:red;"><?php echo $_GET['order'] == 'desc' ? '&#9660;' : '&#9650;'; ?></span><?php } ?></td>
          <td align="center"><a href="javascript:void(0);" onclick="ajaxReloadTri('<?php echo $_GET['tableau']; ?>','nom','<?php echo $tabOrder['nom']; ?>','block')"><strong>Client</strong></a><?php if($tabAfficheTri['nom']){ ?><br><span style="color:red;"><?php echo $_GET['order'] == 'desc' ? '&#9660;' : '&#9650;'; ?></span><?php } ?></td>
		<td align="center"><strong>Projet</strong></td>
          <td align="center"><a href="javascript:void(0);" onclick="ajaxReloadTri('<?php echo $_GET['tableau']; ?>','dateinscr','<?php echo $tabOrder['dateinscr']; ?>','block')"><strong>Date inscr.</strong></a><?php if($tabAfficheTri['dateinscr']){ ?><br><span style="color:red;"><?php echo $_GET['order'] == 'desc' ? '&#9660;' : '&#9650;'; ?></span><?php } ?></td>
          <td align="center"><strong>Etat</strong></td>
          <td align="center"><a href="javascript:void(0);" onclick="ajaxReloadTri('<?php echo $_GET['tableau']; ?>','commercial','<?php echo $tabOrder['commercial']; ?>','block')"><strong>Commercial</strong></a><?php if($tabAfficheTri['commercial']){ ?><br><span style="color:red;"><?php echo $_GET['order'] == 'desc' ? '&#9660;' : '&#9650;'; ?></span><?php } ?></td>
          <!--<td align="center"><a href="javascript:void(0);" onclick="ajaxReloadTri('<?php echo $_GET['tableau']; ?>','interlocuteur','<?php echo $tabOrder['interlocuteur']; ?>','block')"><strong>Interloc.</strong></a><?php if($tabAfficheTri['interlocuteur']){ ?><br><span style="color:red;"><?php echo $_GET['order'] == 'desc' ? '&#9660;' : '&#9650;'; ?></span><?php } ?></td>-->
          <td align="center"><a href="javascript:void(0);" onclick="ajaxReloadTri('<?php echo $_GET['tableau']; ?>','echeance_projet','<?php echo $tabOrder['echeance_projet']; ?>','block')"><strong>Date échéance</strong></a><?php if($tabAfficheTri['echeance_projet']){ ?><br><span style="color:red;"><?php echo $_GET['order'] == 'desc' ? '&#9660;' : '&#9650;'; ?></span><?php } ?></td>
          <td align="center"><a href="javascript:void(0);" onclick="ajaxReloadTri('<?php echo $_GET['tableau']; ?>','date_rappel','<?php echo $tabOrder['date_rappel']; ?>','block')"><strong>Date rappel</strong></a><?php if($tabAfficheTri['date_rappel']){ ?><br><span style="color:red;"><?php echo $_GET['order'] == 'desc' ? '&#9660;' : '&#9650;'; ?></span><?php } ?></td>
          <!--<td align="center"><strong>N° cmd</strong></td>
          <td align="center"><strong>Date cmd</strong></td>-->
          <td align="center"><a href="javascript:void(0);" onclick="ajaxReloadTri('<?php echo $_GET['tableau']; ?>','totalcmd','<?php echo $tabOrder['totalcmd']; ?>','block')"><strong>Total cmd</strong></a><?php if($tabAfficheTri['totalcmd']){ ?><br><span style="color:red;"><?php echo $_GET['order'] == 'desc' ? '&#9660;' : '&#9650;'; ?></span><?php } ?></td>
          <td align="center"><strong>Budget PJT</strong></td>
          <td align="center"></td>
     </tr>
     <?php 
     }
          $cpt = 0;
          $montant_total = 0;
          $montant_total_pjt= 0;
          $nb_contact    = 0;
          while($row = $DB_site2->fetch_array($rq)){ 
               
			   $liste_ids.=",".$row[userid];
			   
               $cpt++;
               
               $tag  = usericon($row['tag'],20);
               
			   /*
               //Récupération d'informations sur la dernière commande, non supprimée, du client
               $sql = "SELECT CASE f.valide 
						WHEN '' THEN 'Aucune commande'
						WHEN 0 THEN 'Non abouti'
						WHEN 2 THEN 'Refusée'
						WHEN 3 THEN 'Refus commerçant' END
						as etat, m.libelleFR as moyenPaiement";
               $sql .= " FROM facture f";
               $sql .= " INNER JOIN moyenpaiement m ON (m.moyenid = f.moyenid)";
               $sql .= " WHERE f.userid = ".$row['userid'];
               $sql .= " AND f.supprimee = '0'";
               $sql .= " ORDER BY f.heurefacture DESC";
               $sql .= " LIMIT 1";
               $facture = $DB_site2->query_first($sql);
               */ 
               //Uniquement possible dans les suivis du jour, en retard, de demain, futurs
               //if(isset($row['type_requete']) && $row['type_requete'] == 'suivi'){
                    
                    //Récupération du dernier suivi du client
                    $sql = "SELECT date, etat, pour, hide, rappeltype, projetid";
                    $sql .= " FROM suivi_client";
                    $sql .= " WHERE suivi NOT LIKE '[infos_client]%'";
					$sql .= " AND hide ='0'";
                    $sql .= " AND userid = ".$row['userid'];
                    $sql .= " ORDER BY date DESC";
                    $sql .= " LIMIT 0,1";
                    $tabResult = $DB_site2->query_first($sql);

                    //recuperation du libelle du projet
                    $row['projet_libelle'] = "";
                    $row['projetid']       = 0;
                    $row['montantTotalPjt']= 0; 
                    $projet_txt="";

                    $pjt = $DB_site2->query("SELECT projetid, libelle, budget FROM projet WHERE userid='".$row['userid']."' AND etatid NOT IN (4,5,6)");
                    while ($pjts = $DB_site2->fetch_array($pjt)) {
                   
                         $row['projet_libelle'] = $pjts['libelle'];
                         $row['projetid'] = $pjts['projetid'];
                         $row['montantTotalPjt'] += intval($pjts['budget']);
                         $projet_txt .="<a href=\"/admin/utilisateur.php?action=editer&userid=".$row['userid']."\" target=\"_blank\" title=\"N° ".$row['projetid']."\">".$row['projet_libelle']."</a>\n";
                    }
                    $montant_total_pjt += $row['montantTotalPjt'];

                    if($row['projetid']==0) $projet_txt="--";
					
                    $row['date']            = '';
                    $row['etat']            = 'aucun';
                    $row['pour']            = '';
                    $row['hide']            = '';
                    $row['rappeltype']      = '';
                    //$row['projetid']       = 0;
				

                    if(!empty($tabResult)){
                         $row['date']            = $tabResult['date'];
                         $row['etat']            = $tabResult['etat'];
                         $row['pour']            = $tabResult['pour'];
                         $row['hide']            = $tabResult['hide'];
					$row['rappeltype']      = $tabResult['rappeltype'];
					//$row['projetid']        = $tabResult['projetid'];
                    }
                    
				/*if($row['projetid']>0){
					// on va chercher le projet_libelle
					$sql = "SELECT * FROM projet WHERE projetid='".$row['projetid']."'";
					$tabResult = $DB_site2->query_first($sql);
					$row['projet_libelle']=$tabResult[libelle];
				} else {
					$row['projet_libelle'] = '';
				}*/
                    //Chargement trop long dans le cas des rappels futurs...
					
					
                    if($_GET['tableau'] != 'nrappel'){ // nrappel
						$sql1="SELECT date_mail_outbox_suivi_client FROM utilisateur WHERE userid = '".$row['userid']."' ";
						$tabOutbox = $DB_site2->query_first($sql1); 
						/*
                         $sql1 = "SELECT MAX(date) as date";
                         $sql1 .= " FROM mail_outbox USE INDEX (suiviid)";
                         $sql1 .= " WHERE suiviid = ".$row['userid'];
                         $sql1 .= " AND suivitype LIKE 'suivi_client'";
                         $tabOutbox = $DB_site2->query_first($sql1);
						 */
						 $sql2="SELECT date_mail_inbox_suivi_client FROM utilisateur WHERE userid = '".$row['userid']."' ";
						$tabInbox = $DB_site2->query_first($sql1); 
						/*
                         $sql2 = "SELECT MAX(date) as date";
                         $sql2 .= " FROM mail_inbox USE INDEX (suiviid)";
                         $sql2 .= " WHERE suiviid = ".$row['userid'];
                         $sql2 .= " AND suivitype LIKE 'suivi_client'";
                         $tabInbox = $DB_site2->query_first($sql2);
						*/
						
                         if($tabOutbox['date_mail_outbox_suivi_client'] > $row['date']) continue;
                         if($tabInbox['date_mail_inbox_suivi_client'] > $row['date']) continue;
                    }
					
               //}
               
               $suivitxt = '<font color=\"#000088\">aucun</span>';
               if(isset($row['etat'])){
                    switch($row['etat']){
                         case 0:
                              $suivitxt = "<font color=\"red\">Perdu</font>";
                              break;
                         case 1:
                              $suivitxt = "<font color=\"#0000AA\">&Agrave; suivre</font>";
                              break;
                         case 2:
                              $suivitxt = "<font color=\"#0000FF\">Attente info client</span>";
                              break;
                         case 3:
                              $suivitxt = "<font color=\"#000088\">&Agrave; chiffrer</span>";
                              break;
                    }
               }
               
               if(isset($row['jour']) && $row['jour'] != 0){
                    $d = getdate($row['rappel']);
                    if($row['jour'] == 1) $h = 9;
                    if($row['jour'] == 2) $h = 14;
                    $row['rappel'] = mktime($h,0,0,$d['mon'],$d['mday'],$d['year']);
               }
               
               $rappeltype = "";
               if($row['rappeltype'] != ""){
                    $rappeltype = "<br><span class=\"".rappeltype_class($row['rappeltype'])."\">".rappeltype_txt($row['rappeltype'])."</span>";
               }
			   
     
               if(!isset($row['jour']) || (isset($row['jour']) )){ // HEIN !!!! je comprend pas cette condition !!!!
                    $budget = $row['budget_projet'];
                    if($row['etat']!=0 && $row['etat']!=5){ // on affiche pas les 'perdu'
						$montant_total += $row['montantTotalCmd'];

						$nb_contact++;
						?>
						<tr class="flist <?php echo $class; ?>">
							 <td align="center"><?php echo $tag; ?></td>
							 <td align="center"><?php echo $row['raisonsociale']; ?></td>
							 <td align="center"><a href="/admin/utilisateur.php?action=editer&userid=<?php echo $row['userid']; ?>" target="_blank"><?php echo $row['nom']." ".$row['prenom']; ?></a></td>
							 <td align="center" style="overflow: hidden;"><?php echo $projet_txt; ?></td>
							 <td align="center"><?php echo $row['date_inscription']; ?></td>
							 <td align="center"><?php echo $suivitxt; ?></td>
							 <td align="center"><?php echo $row['commercial']; ?></td>
							 <td align="center"><?php if($row['echeance_projet'] && $row['echeance_projet'] !== "0000-00-00"){ $date =  date_create($row['echeance_projet']); echo date_format($date, 'd/m/Y'); }?></td>
							 <!--<td align="center"><?php echo isset($row['pour']) ? $row['pour'] : ''; ?></td>-->
							 <td align="center" style="<?php echo $row['hide'] == 0 ? "color:red;" : ""; ?>"><?php echo isset($row['jour']) ? rappel_heure($row['rappel'],$row['jour']).$rappeltype.'<br>'.$row['pour'] : ''; ?></td>
							 <!--<td align="center"><a href="facture.php?action=editer&facturid=<?php echo $facture['facturid']; ?>" target="_blank"><?php echo $facture['facturid']; ?></a></td>
							 <td align="center"><?php echo $facture['date_cmd']; ?></td>-->
							 <td align="center"><?php echo number_format($row['montantTotalCmd'],2,',',' ');?> &euro;</td>
                                    <td align="center" ><?php echo number_format($row['montantTotalPjt'],2,',',' ');?> &euro;</td>
							 <td align="center"><a href="utilisateur.php?action=editer&userid=<?php echo $row['userid']; ?>" target="_blank"><img src="images/icon/viewmag.png"></a></td>
						</tr>
						<?php 
					}
               }
          }
          
          //Aucun résultat
               if($cpt == 0){
          ?>
               <tr class="flist">
                    <td align="center" colspan="12">Aucun résultat</td>
               </tr>
          <?php
               } else {
          ?>
               <tr class="flist">
                    <td colspan="9"></td>
                    <td align="center" style="font-size: 16px; font-weight: bold;"><?php echo number_format($montant_total,2,',',' '); ?> &euro;</td>
                    <td align="center" style="font-size: 16px; font-weight: bold;"><?php echo number_format($montant_total_pjt,2,',',' '); ?> &euro;</td>
                    <td></td>
               </tr>
          <?php
               }
     ?>
</table>
<input type="hidden" id="tab_nb_<?php echo $_GET['tableau']; ?>" name="tab_nb_<?php echo $_GET['tableau']; ?>" value="<?php echo $nb_contact; ?> contacts">
<input type="hidden" id="tab_valeur_<?php echo $_GET['tableau']; ?>" name="tab_valeur_<?php echo $_GET['tableau']; ?>" value="<?php echo number_format($montant_total,2,',',' '); ?> &euro;">
<input type="hidden" id="tab_valeur_brut_<?php echo $_GET['tableau']; ?>" name="tab_valeur_brut_<?php echo $_GET['tableau']; ?>" value="<?php echo $montant_total; ?> &euro;">

<input type="hidden" id="tab_ids_<?php echo $_GET['tableau']; ?>" name="tab_ids_<?php echo $_GET['tableau']; ?>" value="<?php echo $liste_ids; ?>">

<div style="clear: both;"></div>