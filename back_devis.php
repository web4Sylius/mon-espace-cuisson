<?php
require_once ("global.php");

/******************************************** LIEN DE PAIEMENT DEVIS **************************************/
//Le client a cliqué sur panier
if(isset($_GET['back_payment'])){
    $facturid = mysqli_real_escape_string($DB_site->link_id,$_GET['back_payment']);
    //On verifie si la facture existe bien
    $facture = $DB_site->query_first("SELECT panierid FROM facture WHERE facturid='".$facturid."'");
    if(is_null($facture)) {
    	header("Location: /");
    	exit;
    }
    $panierid = $facture['panierid'];

    /*if(exist_panier($DB_site,$panierid)){

    	//pas besoin de recréer un panier car il existe deja
    	//on vide le panier si il y en a un
	    if(isset($_COOKIE['s'])){
	    	delPanier($DB_site,$_COOKIE['s'],intval($panierid));
	 		//on remet les articles au panier
	 		$DB_site->query("UPDATE panier SET sessionid='".mysqli_real_escape_string($DB_site->link_id,$_COOKIE['s'])."' WHERE panierid =".intval($panierid));
	    }
    }else{*/
    	//on recréé le panier
    	$sessionid = mysqli_real_escape_string($DB_site->link_id,$_COOKIE['s']);
    	//on vérifie si un panier existe avec cette session
    	if(exist_panier($DB_site,null,$sessionid)){
    		delPanier($DB_site,$sessionid,intval($panierid));
    	}
    	//création panier
    	$DB_site->query("INSERT INTO panier SET sessionid='".$sessionid."', datepanier=NOW()");
    	$panierid= $DB_site->insert_id();
    	//mise a jour du panierid dans la facture/devis
    	$DB_site->query("UPDATE facture SET panierid='".$panierid."' WHERE facturid='".$facturid."'");

    	//boucles sur les lignes de la facture pour les créer en lignepanier
    	$lignesfactures = $DB_site->query("SELECT lignefactureid,artid,qte FROM lignefacture WHERE factureid='".$facturid."' ORDER BY lignefactureid ASC");
    	while($lignefacture = $DB_site->fetch_array($lignesfactures)){
    		$DB_site->query("INSERT INTO lignepanier (artid,qte,panierid) VALUES ('".$lignefacture['artid']."','".$lignefacture['qte']."','".$panierid."')");
    		$lignepanierid = $DB_site->insert_id();

    		//on ajoute les options
    		$lignesfacturescaract = $DB_site->query("SELECT caractvalid FROM lignefacturecaracteristique WHERE lignefactureid = '".$lignefacture['lignefactureid']."'");
    		while($lignefacturecaract = $DB_site->fetch_array($lignesfacturescaract)){
    			$DB_site->query("INSERT INTO lignepaniercaracteristique (lignepanierid,caractvalid) VALUES ('".$lignepanierid."','".$lignefacturecaract['caractvalid']."')");
    		}
    	}
    //}

    	
}

//Le client a cliqué sur etre rappelé
if(isset($_GET['back_call'])){
 	//on affiche le message etape 1
 	$param = "?back_call=1&facturid=".$facturid; 
}
//le client a cliqué sur envoyer un mail
if(isset($_GET['back_mail'])){
    //on affiche le message etape 1
    $param = "?back_mail=1&facturid=".$facturid; 
}

header("Location: /cde1.php".$param);

function delPanier($DB,$sessionid,$panierid){

    //panier correspondant a la session s
	$panierid_s = $DB->query_first("SELECT panierid FROM panier WHERE sessionid='".mysqli_real_escape_string($DB->link_id,$sessionid)."'");
	
    //$panierid : panierid enregistré dans la facture
	if(/*$panierid !== intval($panierid_s['panierid']) && $panierid !== 0 &&*/ !is_null($panierid_s['panierid'])){
		
		$lignespanier_s = $DB->query("SELECT lignepanierid FROM lignepanier WHERE panierid=".intval($panierid_s['panierid']));

		while ($lignepanier_s = $DB->fetch_array($lignespanier_s)) {
			$DB->query("DELETE FROM lignepaniercaracteristique WHERE lignepanierid=".intval($lignepanier_s['lignepanierid']));
		}

		$DB->query("DELETE FROM panier WHERE panierid =".intval($panierid_s['panierid']));
		$DB->query("DELETE FROM lignepanier WHERE panierid=".intval($panierid_s['panierid']));
	}
	

}

function exist_panier($DB,$panierid=null,$sessionid=null){
	if(!is_null($panierid)){
		$panier = $DB->query_first("SELECT * FROM panier WHERE panierid ='".$panierid."'");
	}elseif(!is_null($sessionid)){
		$panier = $DB->query_first("SELECT * FROM panier WHERE sessionid ='".$sessionid."'");
	}

	if(is_null($panier))
		return false;
	else 
		return true;
}


?>