<?php
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");
include($_SERVER['DOCUMENT_ROOT']."/admin/adv/adv.class.php");
include($_SERVER['DOCUMENT_ROOT']."/class/stock.class.php");
include($_SERVER['DOCUMENT_ROOT']."/class/email_client.php");

//Article
if(isset($_GET['artid']))
	$article = $DB_site2->query_first("SELECT m.libelle, a.artcode, a.artcode_eurocave FROM article a INNER JOIN article_marque am USING(artid) INNER JOIN marque m USING(marqueid) WHERE a.artid=".$_GET['artid']);

//Formulaire ACTION
switch($_GET['action']){
	/* * * * * * * * * * * */
	/* AJOUTER UN ARTICLE */
	case 'add':
		//Date
		if($_GET['date'] != ''){
			$d = explode("/",$_GET['date']);
			$date = mktime(0,0,0,$d[1],$d[0],$d[2]);
		}else
			$date = 0;
		
		//INSERT par QTE
		for($i=0;$i<intval($_GET['qte']);$i++){
			$DB_site2->query("INSERT INTO stock_item(stock_cmd_id, artid, paht, lieu, origin, utilisateur, date, date_livraison, state) 
			VALUES(".$_GET['cmd'].", ".$_GET['artid'].", ".$_GET['prix'].", ".$_GET['lieu'].", 'cmd', '".$user_info['username']."', ".time().", ".$date.", '-1')");
		}
		
		//Lieu
		$lieu = $DB_site2->query_first("SELECT f.societe,f.ville, f.id FROM fournisseur f INNER JOIN stock_mini ON(stock_mini.lieu=f.id) WHERE f.id=".$_GET['lieu']." GROUP By lieu ORDER BY f.societe");
		
		//Suivi_stock
		$body = "
		<ul>
			<li>Ajout d'un article</li>
			<li>Article : ".$article['artcode']."</li>
			<li>Quantité : ".$_GET['qte']."</li>
			<li>Prix Unitaire H.T : ".$_GET['prix']."</li>
			<li>Lieu de stockage : ".$lieu['societe']." ".$lieu['ville']."</li>
			<li>Date livraison souhaitée : ".($date == 0 ? 'Non renseignée' : date("d/m/Y",$date))."</li>
		</ul>";

		$sql = "INSERT INTO suivi_stock SET";
		$sql.= " stock_cmd_id 	= ".$_GET['cmd'];
		$sql.= ", date 			= ".time();
		$sql.= ", commercial 	= '".$user_info['username']."'";
		$sql.= ", suivi      	= \"".$body."\"";
		$DB_site2->query($sql);
		
		//Success
		echo "OK:".$_GET['action'].":".$_GET['cmd'];
	break;
	
	
	/* * * * * * * * * * * * */
	/* MODIFIER UN ARTICLE  */
	case 'edit':
		if($_GET['where'] == '' || $_GET['where'] == 0){
			$date = 0;
		}else
			$date = $_GET['where'];
		
		//On récupére les infos avant DELETE
		$old = $DB_site2->query_first("SELECT *, COUNT(*) AS qte FROM stock_item WHERE stock_cmd_id=".$_GET['cmd']." AND artid=".$_GET['artid']." AND date_livraison=".$date." AND paht=".$_GET['paht']);
		
		//DELETE (pour nouvel INSERT)
		$DB_site2->query("DELETE FROM stock_item WHERE stock_cmd_id=".$_GET['cmd']." AND artid=".$_GET['artid']." AND date_livraison=".$_GET['where']." AND paht=".$_GET['paht']);

		//INSERT xQTE
		for($i=0;$i<intval($_GET['qte']);$i++){
			$DB_site2->query("INSERT INTO stock_item(stock_cmd_id, artid, paht, lieu, origin, utilisateur, date, date_livraison, state) 
			VALUES(".$_GET['cmd'].", ".$_GET['artid'].", '".$_GET['prix']."', ".$old['lieu'].", 'cmd', '".$user_info['username']."', ".time().", ".$_GET['date'].", '-1')");
		}
		
		//Lieu
		$lieu = $DB_site2->query_first("SELECT f.societe,f.ville, f.id FROM fournisseur f INNER JOIN stock_mini ON(stock_mini.lieu=f.id) WHERE f.id=".$old['lieu']." GROUP By lieu ORDER BY f.societe");
		
		//Suivi_stock
		$body = " 
		<ul>
			<li>Modification d'un article</li>
			<li>Article : ".$article['artcode']."</li>
			<li>Quantité : ".$old['qte']." => ".$_GET['qte']."</li>
			<li>Prix Unitaire H.T : ".$old['paht']." => ".$_GET['prix']."</li>
			<li>Lieu de stockage : ".$lieu['societe']." ".$lieu['ville']."</li>
			<li>Date livraison souhaitée : ".($old['date_livraison']==0 ? 'Non renseignée' : date("d/m/Y",$old['date_livraison']))." => ".($date==0 ? 'Non renseignée' :date("d/m/Y",$date))."</li>
		</ul>";
		
		$sql = "INSERT INTO suivi_stock SET";
		$sql.= " stock_cmd_id 	= ".$_GET['cmd'];
		$sql.= ", date 			= ".time();
		$sql.= ", commercial 	= '".$user_info['username']."'";
		$sql.= ", suivi      	= \"".$body."\"";
		$DB_site2->query($sql);
		
		//Success
		echo "OK:".$_GET['action'].":".$_GET['cmd'];
	break;
	
	
	/* * * * * * * * * * * * */
	/* SUPPRIMER UN ARTICLE */
	case 'delete':
		//Lieu
		$supplieu = $DB_site2->query_first("SELECT * FROM stock_item WHERE stock_cmd_id=".$_GET['cmd']." AND artid=".$_GET['artid']);
		
		//On récupére les infos avant DELETE
		$old = $DB_site2->query_first("SELECT *, COUNT(*) AS qte FROM stock_item WHERE stock_cmd_id=".$_GET['cmd']." AND artid=".$_GET['artid']." AND date_livraison=".$date." AND paht=".$_GET['paht']);
		
		//Delete
		$DB_site2->query("DELETE FROM stock_item WHERE stock_cmd_id=".$_GET['cmd']." AND artid=".$_GET['artid']." AND date_livraison=".$_GET['date']." AND paht=".$_GET['paht']);
		
		//Lieu suivi
		$lieu = $DB_site2->query_first("SELECT f.societe,f.ville, f.id FROM fournisseur f INNER JOIN stock_mini ON(stock_mini.lieu=f.id) WHERE f.id=".$supplieu['lieu']." GROUP By lieu ORDER BY f.societe");
		
		//Suivi_stock
		$body = "
		<ul>
			<li>Suppression d'un article</li>
			<li>Article : ".$article['artcode']."</li>
			<li>Quantité : ".$old['qte']."</li>
			<li>Prix Unitaire H.T : ".$old['paht']."</li>
			<li>Lieu de stockage : ".$lieu['societe']." ".$lieu['ville']."</li>
			<li>Date livraison souhaitée : ".($old['date_livraison'] == 0 ? 'Non renseignée' : date("d/m/Y",$old['date_livraison']))."</li>
		</ul>";
		
		$sql = "INSERT INTO suivi_stock SET";
		$sql.= " stock_cmd_id 	= ".$_GET['cmd'];
		$sql.= ", date 			= ".time();
		$sql.= ", commercial 	= '".$user_info['username']."'";
		$sql.= ", suivi      	= \"".$body."\"";
		$DB_site2->query($sql);
		
		//Success
		echo "OK:".$_GET['action'].":".$_GET['cmd'];
	break;
	
	
	/* * * * * * * * * * * * */
	/* ANNULER LA COMMANDE  */
	case 'cancel':
		//Suppression
		$DB_site2->query("UPDATE stock_item SET state='-2' WHERE stock_cmd_id=".$_GET['cmd']);
		
		//Suivi_stock $body
		$body = "Commande ".$_GET['cmd']." ANNULE : <br>
		<table>";
			
		$stock_cmd = new stock_cmd($DB_site2, $_GET['cmd']);
		$stock_cmd->items();
		$stock_cmd->groupitems2();
		
		foreach($stock_cmd->items as $item){
			$lieu = $DB_site2->query_first("SELECT f.societe,f.ville, f.id FROM fournisseur f INNER JOIN stock_mini ON(stock_mini.lieu=f.id) WHERE f.id=".$item->lieu." GROUP By lieu ORDER BY f.societe");
			$body.= "<tr>
				<td>".$item->artcode." - </td>
				<td align='center'>".$item->qte." - </td>
				<td align='center'>'".$item->pu."&euro;' - </td>
				<td>".$lieu['societe']." ".$lieu['ville']." - </td>
				<td align='center'>".($item->date_livraison == 0 ? "Non renseignée": "".date("d/m/Y",$item->date_livraison)."")."</td>
			</tr>";
		}
		$body.= "</table>";
		
		$sql = "INSERT INTO suivi_stock SET";
		$sql.= " stock_cmd_id 	= ".$_GET['cmd'];
		$sql.= ", date 			= ".time();
		$sql.= ", commercial 	= '".$user_info['username']."'";
		$sql.= ", suivi      	= \"".$body."\"";
		$DB_site2->query($sql);
		
		//Succes
		echo "OK:".$_GET['action'];

		//Email (si on a annulé une commande fournisseur)
		if(isset($_GET['mail']) && $_GET['mail'] == 1) 
			mail_cancel($_GET['cmd']);
	break;
	
	case '':
	break;
}

// ****************************************************************************
function mail_cancel($cmd){
	  global $DB_site2;
    global $user_info;

    $stock_cmd = new stock_cmd($DB_site2,$cmd);
    $stock_cmd->items();
    $stock_cmd->groupitems();

    $advid = $DB_site2->query_first("SELECT * FROM adv WHERE facturid=".$cmd);
    $adv = new adv($DB_site2,$advid['id']);

    $subject="Annulation de la commande n°".$cmd;
  	$text.="Bonjour,<br>Merci d'annuler les produits suivants de la commande n° $cmd";
  	$fin="<br>Merci de nous confirmer l'annulation de ces produits par retour de ce mail.<br>Cordialement<br>$user_info[nom_contact] - ACILYON - www.ma-cave-a-vin.fr<br>Tél : + 33.4.26.03.05.44 -  Fax: +33.4.26.03.05.49";
  
  	$text.="<p><b>Produits :</b></p>";
	  foreach($stock_cmd->items as $item){
    		$text.="<div id=\"lf_".$item->artid."\">
    		<span id=\"qte_".$item->artid."\"><u>Quantité</u> : ".$item->qte."</span><br>
        <span id=\"artcode_".$item->artid."\"><u>Réf</u> : ".$item->ref."</span><br>
    		<u>Libellé</u> : ".$item->libelle."<br>";
        
        // TSI Attention item->ref contient la valeur artcode_eurocave .... 
        //<span id=\"artcode_".$item->artid."\"><u>Réf</u> : $artcode</span><br>
    	  
    		$text.="<span id=\"paht_".$item->artid."\"><u>Prix unitaire</u> : ".$item->paht."&euro;</span>";
    		
    		if($item->ecopartht>0) $text.=" + ".$item->ecopartht."&euro;";
    		$text .= "<br><br>";
    }

  	$ar = $DB_site2->query_first("SELECT id, date, numero FROM adv_ar WHERE advid=".$advid['id']);
  	if($ar['id'] != '')
  		$text .= "<p>Veuillez annuler l'AR n°".$ar['numero']." datant du ".$ar['date']."</p>";
  
    $text.=$fin;    
  
  	$pj=array();
  	$mail=new email();
  	$mail->destinataire=$adv->fields['fournisseur'];
  	$mail->copymode="bcc";
    // TSI correction mauvais expediteur et copies...
  	//$mail->to="webmaster2@mon-espace-cuisson.com";
  	//$mail->expediteur="webmaster2@mon-espace-cuisson.com";
    $mail->to=array("pr@mon-espace-cuisson.com","adv@mon-espace-cuisson.com");
  	$mail->expediteur="adv@mon-espace-cuisson.com";
  	$mail->expname="Ma Cave à Vin";
  	$mail->subject=$subject;
  	$mail->body=$text;
  	$mail->sendmail();

  	$time = time()+1;
  	$DB_site2->query("INSERT INTO mail_outbox SET date=".$time.", utilisateur='".$user_info[username]."', `to`='".$mail->destinataire."', subject='".$mail->subject."', body='".mysqli_real_escape_string($DB_site2->link_id,$mail->body)."', pj='".mysqli_real_escape_string($DB_site2->link_id,implode(',',$pj))."', stock_cmd_id=".$cmd.", uniqueid='".$mail->uniqueid."', compta ='non'");
}