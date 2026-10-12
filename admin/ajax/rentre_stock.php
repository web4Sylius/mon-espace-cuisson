<?php 
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");
include($_SERVER['DOCUMENT_ROOT']."/class/email_client.php");
include($_SERVER['DOCUMENT_ROOT']."/class/stock.class.php");
include($_SERVER['DOCUMENT_ROOT']."/class/frais_port.php");
include($_SERVER['DOCUMENT_ROOT']."/class/facture.class.php");
include($_SERVER['DOCUMENT_ROOT']."/class/fournisseur.class.php");
include($_SERVER['DOCUMENT_ROOT']."/admin/marge.php");
require_once($_SERVER['DOCUMENT_ROOT']."/class/DAOgarantie.class.php");
require_once($_SERVER['DOCUMENT_ROOT']."/class/garantie.class.php");
require_once($_SERVER['DOCUMENT_ROOT']."/class/DAOfournisseur.class.php");
require_once($_SERVER['DOCUMENT_ROOT']."/class/DAOlignefacturegarantie.class.php");

$template = new template($user_info['langue'],$DB_site2);

//Vérification si champs remplis
if(!isset($_GET['fact']) || empty($_GET['fact'])) exit;
if(!isset($_GET['artid']) || empty($_GET['artid'])) exit;
if(!isset($_GET['qte'])   || empty($_GET['qte'])) exit;
if(!isset($_GET['lieu'])  || empty($_GET['lieu'])) exit;
if(!isset($_GET['comm'])  || empty($_GET['comm'])) exit;
if(!isset($_GET['conf'])  || empty($_GET['conf'])) exit;

//Ajout de l'article dans stock_mini SI inexistant
if(isset($_GET['mini']) && isset($_GET['delai'])){
	$stock = new stock($DB_site2);
	$stock->setmini($_GET['artid'],$_GET['qte'],$_GET['mini'],'',$_GET['lieu'],$_GET['delai']);
}

//INSERT stock_item
if(isset($_GET['fact']) && isset($_GET['artid']) && isset($_GET['qte']) && isset($_GET['lieu']) && isset($_GET['comm']) && isset($_GET['conf'])){

    $result = false;
    $msg = "Erreur lors de l'ajout";
	
	if($_GET['conf'] == 1){
		$conf = "PRODUIT CONFORME";
	}else{
		$conf = "PRODUIT NON CONFORME";
		
		//Article
		$r = $DB_site2->query_first("SELECT a.artid, m.libelle, a.artcode, a.artcode_eurocave
							FROM article a
							INNER JOIN article_marque am USING ( artid )
							INNER JOIN marque m USING ( marqueid )
							WHERE a.artid =".$_GET['artid']);
		$art = $r['libelle']." - ".$r['artcode']." ".$r['artcode_eurocave'];
		
		//Lieu de stockage
		$r = $DB_site2->query_first("SELECT f.societe,f.ville, f.id 
							FROM fournisseur f 
							INNER JOIN stock_mini ON(stock_mini.lieu=f.id) 
							WHERE f.id=".$_GET['lieu']." 
							GROUP By lieu ");
		$lieu = $r['societe']." ".$r['ville'];
		
		//Envoi d'un mail d'avertisement
		$vars = array(
			"article"	=> $art,
			"facturid"	=> $_GET['fact'],
			"qte" 		=> $_GET['qte'],
			"lieu" 		=> $lieu,
			"comm" 		=> $_GET['comm']
		);
		
		$ec = new email_client($DB_site2,$template,null,"conformite",$vars);
		
		$mail = new email();
		$mail->stylesheet($rootpath."/configurations/".$host."/mails.css");
		$mail->destinataire = "webmaster2@mon-espace-cuisson.com,". $user_info['mail_contact'];
		$mail->destname     = $user_info['nom_contact'];
		$mail->expediteur   = $template->translate("/site/[site_code]/mail_expediteur");
		$mail->expname      = $template->translate("/site/nom");
		$mail->to           = 'webmaster2@mon-espace-cuisson.com';
		$mail->copymode     = "bcc";
		$mail->subject      = $ec->subject;
		$mail->body         = $ec->body;
		$mail->sendmail();
		
		//Suivi
		$sql = "INSERT INTO mail_outbox SET";
		$sql .= " date         = ".time()."";
		$sql .= ", utilisateur = '".$user_info['username']."'";
		$sql .= ", `to`        = '".$mail->destinataire."'";
		$sql .= ", subject     = '".addslashes($mail->subject)."'";
		$sql .= ", body        = '".addslashes($mail->body)."'";
		$sql .= ", facturid     = '".$_GET['fact']."'";
		$sql .= ", uniqueid    ='".$mail->uniqueid."'";
		$sql .= ", compta      ='non'";
		$DB_site2->query($sql);     
	}
	
	//Lignefacture
	$lignefacture = $DB_site2->query_first("SELECT lignefactureid FROM lignefacture WHERE factureid =".$_GET['fact']." AND artid=".$_GET['artid']);
			
	//PAHT envoi_fournisseur
	/*$sql = "SELECT efl.id, ROUND((efl.paht + efl.ecopart),2) AS montantht,efl.ecopart";
	$sql.= " FROM envoi_fournisseur_ligne efl";
	$sql.= " INNER JOIN envoi_fournisseur ef ON (ef.id = efl.efid)";
	$sql.= " WHERE efl.ligne_facture_id = ".$lignefacture['lignefactureid'];
	$sql.= " AND efl.type IN ('stock','fourn')";
	$sql.= " AND ef.destinataire <> '0' AND ef.destinataire!=''";
	$sql.= " AND ef.facturid = ".$_GET['fact'];
	*/

	// prendre la valeur du produit
	$efl=$DB_site2->query_first("SELECT paht,ecopart FROM envoi_fournisseur_ligne WHERE ligne_facture_id=".$lignefacture['lignefactureid']." AND type IN ('stock','fourn')");
	//Lieu de stockage
	$r = $DB_site2->query_first("SELECT f.societe,f.ville, f.id 
							FROM fournisseur f 
							INNER JOIN stock_mini ON(stock_mini.lieu=f.id) 
							WHERE f.id=".$_GET['lieu']." 
							GROUP By lieu ");
	$lieu = $r['societe']." ".$r['ville'];
	
	//Si artids (stock_mini)
	$s = $DB_site2->query("SELECT artid, artids FROM stock_mini WHERE artids LIKE '%".$_GET['artid']."%'");
	if($DB_site2->num_rows($s) == 1){
		$a = $DB_site2->fetch_array($s);
		$artid = $a['artid'];
	}else{
		$artid = $_GET['artid'];
	}
	
	
    for($i=0;$i<intval($_GET['qte']);$i++){
		$item 				= new stock_item($DB_site2);
		$item->artid 		= $artid;
		$item->lieu	 	= $_GET['lieu'];
		$item->paht 		= $efl['paht'];
		$item->ecopartht        = $efl['ecopart'];
		$item->origin 		= "retour:".$_GET['fact'];
		$item->infos 		= "Commande ".$_GET['fact']." - ".$_GET['comm']." (".$conf.") - ".$lieu;
		$item->date 		= time();
		$item->utilisateur 	= $user_info['username'];
		$item->state 		= 1;
		$result			= $item->insert();

    }
	
    $margetotal=new marge($DB_site2,$_GET['fact']);
    $margett = $margetotal->margetotal($DB_site2,$_GET['fact']);
    $facture_user = new facture($DB_site2,$_GET['fact']);
    if($margett['totalfactureht'] != 0.00 && $margett['totalachat'] != 0.00) {
    	if($margett['parentid'] != '') {
            $DB_site2->query("UPDATE facture SET marge ='".$margett["margett_pc"]."' WHERE facturid ='".$margett['parentid']."'");
        }
    	$DB_site2->query("UPDATE facture SET marge ='".$margett["margett_pc"]."' WHERE facturid ='".$_GET['fact']."'");
	}
    if($margett["margett_pc"] < 20 && $margett["margett_pc"] != "") {
    	$mail=new email();
    	$mail->destinataire="pr@mon-espace-cuisson.com";
    	$mail->to = array("webmaster4@mon-espace-cuisson.com");
    	$mail->expediteur="contact@mon-espace-cuisson.com";
    	$mail->expname="Ma Cave à Vin";
    	$mail->subject="Analyse rentabilité";
    	$mail->body="Une analyse de rentabilité de commande est tombé sous les 20% :
    	<br>Commande N° : <a href='https://www.ma-cave-a-vin.fr/admin/facture.php?action=editer&facturid=".$_GET['fact']."#detail_marge'>".$_GET['fact']."</a>
    	<br>Nom : " .$facture_user->prenom . " " . $facture_user->nom;
    	$mail->sendmail();
    	$sql = "INSERT INTO suivi_commande SET";
    	$sql .= " facturid    = ".$_GET['fact'];
    	$sql .= ", date       = ".time();
    	$sql .= ", commercial = '".$user_info['username']."'";
    	$sql .= ", suivi      = 'l\'analyse de marge a trouvé une marge de ".$margett["margett_pc"]."%'";
    	$DB_site2->query($sql);
    }

	//Suivi_commande
	$body = "Rentrée de stock d'un $conf pour la commande ".$_GET['fact']." : 
			<ul>
				<li>Article : ".$article['artcode']."</li>
				<li>Quantité : ".$_GET['qte']."</li>
				<li>Lieu de stockage : ".$lieu."</li>
				<li>Commentaire(s) : ".$_GET['comm']."</li>
			</ul>";
	
	$sql_sc = "INSERT INTO suivi_commande SET";
    $sql_sc.= " facturid    = ".$_GET['fact']." ";
    $sql_sc.= ", date       = ".time()." ";
    $sql_sc.= ", commercial = '".$user_info['username']."' ";
    $sql_sc.= ", suivi      = \"".$body."\" ";
    $DB_site2->query($sql_sc);
	


	print "OK:".$_GET['fact'];

}
