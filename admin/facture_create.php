<?php
//On autorise l'affichage que si l'utilisateur a accès à cette page
if($aaccess != false && !in_array("facture_create.php",$aaccess)) exit;


include_once("../class/fonctions.php");
require_once(SITE_PATH."/class/DAOarticle.class.php");
require_once(SITE_PATH."/class/DAOtype.class.php");

if(isset($_POST['modif'])){
	if($_POST['modif']==0){
	$utilisateur=$DB_site2->query_first("SELECT * FROM utilisateur WHERE userid=".$_POST['selectuserid']);

	$origin=array();
	if(isset($_POST['provenanceid'])){
	    $provenance="sav";
	    $provenanceid=intval($_POST['provenanceid']);
	    $origin=$DB_site2->query_first("SELECT * FROM facture WHERE facturid='".$provenanceid."'");
	}
	if(empty($origin['langue'])) $origin['langue']="FR";
	if(empty($origin['site_code'])) $origin['site_code']="base";
	
	$taux=$DB_site2->query_first("SELECT port, gestion, TVAtauxnormal FROM pays WHERE paysid = '$utilisateur[paysid]'");
        if ($taux[port] == '0')
		$tauxport = 0 ;
        else
                $tauxport = $taux['TVAtauxnormal'] ;
       
	$sql="INSERT INTO facture SET userid=".$utilisateur['userid'].", datefacture=NOW(), heurefacture=".time().", moyenid=".intval($_POST['moyen']).", valide=7, nom='".mysqli_real_escape_string($DB_site2->link_id,$_POST['shipping_last_name'])."', prenom='".mysqli_real_escape_string($DB_site2->link_id,$_POST['shipping_first_name'])."', codepostal='".$_POST['shipping_zip']."', adresse='".mysqli_real_escape_string($DB_site2->link_id,$_POST['shipping_address_1'])."', ville='".mysqli_real_escape_string($DB_site2->link_id,$_POST['shipping_city'])."', telephone='".$_POST['shipping_phone']."', raisonsociale='".mysqli_real_escape_string($DB_site2->link_id,$_POST['shipping_company'])."', paysid=".intval($_POST['shipping_country']).", mail='".$_POST['shipping_mail']."', langue='".$origin['langue']."', site_code='".$origin['site_code']."', infotranspo='".mysqli_real_escape_string($DB_site2->link_id,$_POST['infotranspo'])."', tvaport=".floatval($tauxport).", provenance='$provenance', provenanceid=$provenanceid";
	$DB_site2->query($sql);
	$facturid=$DB_site2->insert_id();
	
	$suivitxt="Création commande ".$facturid." validée en attente de paiement
	<br>Livraison : ".$_POST['shipping_company']." ".$_POST['shipping_last_name']." ".$_POST['shipping_first_name']."<br>".$_POST['shipping_address_1']."<br>".$_POST['shipping_zip']." ".$_POST['shipping_city']."
	<br>".$_POST['shipping_phone']."
	<br>".$_POST['shipping_mail']."
	<br>Info : ".$_POST['infotranspo'];
	$DB_site2->query("INSERT INTO suivi_commande SET facturid=".$facturid.", date=".time().", commercial='".$user_info[username]."', suivi='".mysqli_real_escape_string($DB_site2->link_id,$suivitxt)."'");
    
     }else{
	$sql="UPDATE facture SET nom='".mysqli_real_escape_string($DB_site2->link_id,mb_strtoupper($_POST['shipping_last_name']))."', prenom='".mysqli_real_escape_string($DB_site2->link_id,$_POST['shipping_first_name'])."', codepostal='".$_POST['shipping_zip']."', adresse='".mysqli_real_escape_string($DB_site2->link_id,$_POST['shipping_address_1'])."', ville='".mysqli_real_escape_string($DB_site2->link_id,mb_strtoupper($_POST['shipping_city']))."', telephone='".$_POST['shipping_phone']."', raisonsociale='".mysqli_real_escape_string($DB_site2->link_id,$_POST['shipping_company'])."', paysid=".intval($_POST['shipping_country']).", mail='".$_POST['shipping_mail']."', infotranspo='".mysqli_real_escape_string($DB_site2->link_id,$_POST['infotranspo'])."' WHERE facturid=".$_POST['modif'];
	$DB_site2->query($sql);
	
	$suivitxt="Modification commande
	<br>Livraison : ".$_POST['shipping_company']." ".$_POST['shipping_last_name']." ".$_POST['shipping_first_name']."<br>".$_POST['shipping_address_1']."<br>".$_POST['shipping_zip']." ".$_POST['shipping_city']."
	<br>".$_POST['shipping_phone']."
	<br>".$_POST['shipping_mail']."
	<br>Info : ".$_POST['infotranspo'];
	$DB_site2->query("INSERT INTO suivi_commande SET facturid=".intval($_POST['modif']).", date=".time().", commercial='".$user_info[username]."', suivi='".mysqli_real_escape_string($DB_site2->link_id,$suivitxt)."'");
	
	if($_POST['useraction']==1){ // modification info facturation
	    $utilisateur=$DB_site2->query_first("SELECT * FROM utilisateur WHERE mail='".$_POST['billing_mail']."'");
	    if($utilisateur==""){ // création nouvel utilisateur
		if($_POST['tvaintracom']!="" && $_POST['billing_company']!=""){
		    $result=checkvatnumber($_POST['tvaintracom']);
		    if($result->valid==false) $_POST['tvaintracom']="";
		}else
		    $_POST['tvaintracom']="";
		    
		$pass=mkpassword();
		$sql="INSERT INTO utilisateur SET mail='".$_POST['billing_mail']."', password='".md5($pass)."', civilite=0, nom='".$_POST['billing_last_name']."', prenom='".$_POST['billing_first_name']."', codepostal='".$_POST['billing_zip']."', adresse='".$_POST['billing_address_1']."', ville='".$_POST['billing_city']."', telephone='".$_POST['billing_phone']."', raisonsociale='".$_POST['billing_company']."', dateinscription=NOW(), paysid=".intval($_POST['billing_country']).", tvaintracom='".$_POST['tvaintracom']."', langue='FR', allowemail=0, provenance='administrateur',
		nom_livraison='".$_POST['billing_last_name']."', prenom_livraison='".$_POST['billing_first_name']."', codepostal_livraison='".$_POST['billing_zip']."', adresse_livraison='".$_POST['billing_address_1']."', ville_livraison='".$_POST['billing_city']."', telephone_livraison='".$_POST['billing_phone']."', raisonsociale_livraison='".$_POST['billing_company']."', paysid_livraison=".intval($_POST['billing_country']);
		$DB_site2->query($sql);
		$userid=$DB_site2->insert_id();
		$utilisateur=$DB_site2->query_first("SELECT * FROM utilisateur WHERE userid=".intval($userid));
		
		$suivitxt="Création utilisateur ".$userid." pour la commande ".$_POST['modif']."
		<br>".$_POST['billing_company']." ".$_POST['billing_last_name']." ".$_POST['billing_first_name']."
		<br>".$_POST['billing_address_1']."
		<br>".$_POST['billing_zip']." ".$_POST['billing_city']."
		<br>".$_POST['billing_phone']."
		<br>".$_POST['billing_mail']."
		<br>mdp : ".$pass;
		$DB_site2->query("INSERT INTO suivi_commande SET facturid=".$_POST['modif'].", date=".time().", commercial='".$user_info[username]."', suivi='".mysqli_real_escape_string($DB_site2->link_id,$suivitxt)."'");
		
		$DB_site2->query("UPDATE facture SET userid=$userid WHERE facturid=".$_POST['modif']);
	    }else{  // modification utilisateur
		$userid=$DB_site2->query_first("SELECT userid FROM facture WHERE facturid=".$_POST['modif']);
		if($_POST['tvaintracom']!="" && $_POST['billing_company']!=""){
			$result=checkvatnumber($_POST['tvaintracom']);
			if($result->valid==false) $_POST['tvaintracom']="";
		    }else
			$_POST['tvaintracom']="";
			
		$suivitxt="Modification utilisateur ".$userid[0]." pour la commande ".$_POST['modif']."
		<br>".$_POST['billing_company']." ".$_POST['billing_last_name']." ".$_POST['billing_first_name']."
		<br>".$_POST['billing_address_1']."
		<br>".$_POST['billing_zip']." ".$_POST['billing_city']."
		<br>".$_POST['billing_phone']."
		<br>".$_POST['billing_mail'];
		$DB_site2->query("INSERT INTO suivi_commande SET facturid=".$_POST['modif'].", date=".time().", commercial='".$user_info[username]."', suivi='".mysqli_real_escape_string($DB_site2->link_id,$suivitxt)."'");
		
		$sql="UPDATE utilisateur SET mail='".$_POST['billing_mail']."', nom='".mb_strtoupper($_POST['billing_last_name'])."', prenom='".$_POST['billing_first_name']."', codepostal='".$_POST['billing_zip']."', adresse='".$_POST['billing_address_1']."', ville='".mb_strtoupper($_POST['billing_city'])."', telephone='".$_POST['billing_phone']."', raisonsociale='".$_POST['billing_company']."', paysid=".intval($_POST['billing_country']).", tvaintracom='".$_POST['tvaintracom']."' WHERE userid=".$userid[0];
		$DB_site2->query($sql);
	    }
	}
	if($_POST['useraction']==2){  // changement compte facturation
	    $facture=$DB_site2->query_first("SELECT userid FROM facture WHERE facturid=".$_POST['modif']);
	    $DB_site2->query("UPDATE facture SET userid=".$_POST['selectuserid']." WHERE facturid=".$_POST['modif']);
	    $suivitxt="Changement compte facturation : ".$facture['userid']." => ".$_POST['selectuserid'];
	    $DB_site2->query("INSERT INTO suivi_commande SET facturid=".$_POST['modif'].", date=".time().", commercial='".$user_info[username]."', suivi='".mysqli_real_escape_string($DB_site2->link_id,$suivitxt)."'");
	}
	$facturid=$_POST['modif'];
    }
    
    print "<script type=\"text/javascript\">
        document.location='facture.php?action=creer&facturid=".$facturid."';
    </script>";
}

if(isset($_GET['copy'])){
    $orig=$DB_site2->query_first("SELECT * FROM facture WHERE facturid=".$_GET['copy']);
    $DB_site2->query("INSERT INTO facture SET userid=".$orig['userid'].", datefacture=NOW(), heurefacture=".time().", prixport=".$orig['prixport'].", tvaport=".$orig['tvaport'].", civilite=".$orig['civilite'].", nom='".$orig['nom']."', prenom='".$orig['prenom']."', codepostal='".$orig['codepostal']."', adresse='".$orig['adresse']."', ville='".$orig['ville']."', telephone='".$orig['telephone']."', raisonsociale='".$orig['raisonsociale']."', paysid=".$orig['paysid'].", mail='".$orig['mail']."', site_code='".$orig['site_code']."', langue='".$orig['langue']."', cadeauid='".$orig['cadeauid']."', montantcadeau=".$orig['montantcadeau'].", modelivraison='".$orig['modelivraison']."', infotranspo='".$orig['infotranspo']."', provenance='".$orig['provenance']."', provenanceid='".$orig['provenanceid']."', mp_facturid='".$orig['mp_facturid']."', commercial='".$orig['commercial']."'");
    $newfacturid=$DB_site2->insert_id();
    
    $lignes=$DB_site2->query("SELECT * FROM lignefacture WHERE factureid=".$_GET['copy']);
    while($ligne=$DB_site2->fetch_array($lignes)){
	$DB_site2->query("INSERT INTO lignefacture SET factureid=".$newfacturid.", artid=".$ligne['artid'].", catid=".$ligne['catid'].", artcode='".$ligne['artcode']."', qte=".$ligne['qte'].", libelle='".$ligne['libelle']."', prix=".$ligne['prix'].", tva=".$ligne['tva'].", paht=".$ligne['paht'].", ecopart=".$ligne['ecopart']);
	$newligneid=$DB_site2->insert_id();
	$caracts=$DB_site2->query("SELECT * FROM lignefacturecaracteristique WHERE lignefactureid=".$ligne['lignefactureid']);
	while($caract=$DB_site2->fetch_array($caracts)){
	    $DB_site2->query("INSERT INTO lignefacturecaracteristique SET lignefactureid=".$newligneid.", caractvalid=".$caract['caractvalid'].", libelle_caract='".mysqli_real_escape_string($DB_site2->link_id,$caract['libelle_caract'])."', libelle_caractval='".mysqli_real_escape_string($DB_site2->link_id,$caract['libelle_caractval'])."', differenceprix=".$caract['differenceprix'].", paht=".$caract['paht']);
	}
    }
    $facturid=$newfacturid;
}


if(isset($_POST['prod'])){

    if(isset($_POST['artid']))  $article=$DB_site2->query_first("SELECT tauxchoisi,paht,ecopartht FROM article WHERE artid=".$_POST['artid']);
    if(!isset($_POST['tva']) || $_POST['tva']==""){
      $tvapays=$DB_site2->query_first("SELECT p.*,u.tvaintracom AS userintracom FROM pays p INNER JOIN utilisateur u ON(p.paysid=u.paysid) INNER JOIN facture f ON (u.userid=f.userid) WHERE f.facturid=".$_POST['facturid']);
      if($article[0]=="1") $tva=$tvapays['TVAtauxnormal'];
      if($article[0]=="2") $tva=$tvapays['TVAtauxreduit'];
      if($tvapays['tvaintracom']==1 && $tvapays['userintracom']!=""){
	  $tvafrance=$DB_site2->query_first("SELECT * FROM pays WHERE paysid=57");
	  switch($article[0]){
	  case "1":
	  $_POST['price']=round($_POST['price']/(1+$tvafrance['TVAtauxnormal']/100),2);
	  break;
	  case "2":
	  $_POST['price']=round($_POST['price']/(1+$tvapays['TVAtauxreduit']/100),2);
	  break;
	  }
      }
    }else
      $tva=$_POST['tva'];

     if($_POST['prod'] == 'recup'){
     	$libelle = "Récupération d\'une ancienne cave pour destruction ".$_POST['dimensions']." ".$_POST['poids']."kg";
     	$DB_site2->query("INSERT INTO lignefacture SET factureid=".$_POST['facturid'].", artid=0, artcode='zzz', qte=1, libelle='".$libelle."', prix=".floatval($_POST['price']).", tva=".floatval($tva));
	    $lfid=$DB_site2->insert_id();
	    
	    $suivitxt="Ajout  ".$libelle." ".floatval($_POST['price'])."&euro;TTC";
	    $DB_site2->query("INSERT INTO suivi_commande SET facturid=".$_POST['facturid'].", date=".time().", commercial='".$user_info[username]."', suivi='".mysqli_real_escape_string($DB_site2->link_id,$suivitxt)."'");

     }else{
     	$DB_site2->query("INSERT INTO lignefacture SET factureid=".$_POST['facturid'].", artid=".intval($_POST['artid']).", qte=".$_POST['qtt'].", artcode='".$_POST['prod']."', libelle='".mysqli_real_escape_string($DB_site2->link_id,$_POST['description'])."', prix=".floatval($_POST['price']).", tva=".floatval($tva).", paht=".floatval($article['paht']).", ecopart=".round($article['ecopartht']*(1+$tva/100),2));
	    $lfid=$DB_site2->insert_id();
	    
	    $suivitxt="Ajout ".$_POST['qtt']." ".$_POST['prod']." ".$_POST['description']." ".floatval($_POST['price'])."&euro;TTC";
	    $DB_site2->query("INSERT INTO suivi_commande SET facturid=".$_POST['facturid'].", date=".time().", commercial='".$user_info[username]."', suivi='".mysqli_real_escape_string($DB_site2->link_id,$suivitxt)."'");
     } 
    
    
    $i=0;
    $nbcaract=$DB_site2->query_first("SELECT COUNT(caractid) FROM caracteristique");
    $DB_site2->query("DELETE FROM lignefacturecaracteristique WHERE lignefactureid=".$lfid);
    for($i=0;$i<=$nbcaract[0];$i++){
	if(isset($_POST['caract_'.$i])){
          $caractlibelle=$DB_site2->query_first("SELECT libelleFR FROM caracteristiquevaleur WHERE caractvalid=".$_POST['caract_'.$i]);
	  $article_caract=$DB_site2->query_first("SELECT * FROM article_caractval WHERE caractvalid=".$_POST['caract_'.$i]." AND artid=".intval($_POST['artid']));
	  
	  $caract_prix_type=$_POST['caract_prix_type'];
	  if(isset($article_caract[$caract_prix_type])){
                $prixcaract=$article_caract[$caract_prix_type];
                if(!is_numeric($prixcaract)) $prixcaract=unserialize($prixcaract)['base'];
                $prixcaract=floatval($prixcaract*(1+($tva/100)));
                $DB_site2->query("UPDATE lignefacture SET prix=prix+".$prixcaract." WHERE lignefactureid=".$lfid);
	  }else
                $prixcaract=0;

          $DB_site2->query("INSERT INTO lignefacturecaracteristique SET lignefactureid=".$lfid.", caractvalid=".$_POST['caract_'.$i].", libelle_caract='".mysqli_real_escape_string($DB_site2->link_id,$_POST['caractname_'.$i])."', libelle_caractval='".mysqli_real_escape_string($DB_site2->link_id,$caractlibelle[0])."', differenceprix=".$prixcaract.", paht=".floatval($article_caract['achat']));
	}
	$i++;
    }
    
    $user=$DB_site2->query_first("SELECT u.paysid FROM utilisateur u INNER JOIN facture f ON (u.userid=f.userid) WHERE f.facturid=".$_POST['facturid']);
    
    if(isset($_POST['gar_fourn']) && $_POST['gar_fourn'] != 0){
	$garantie=new garantie($DB_site2,"base","FR",$_POST['gar_fourn'],$_POST['artid']);
	$codefourn=DAOfournisseur::loadCodeFournisseurByName($DB_site2,$garantie->fournisseur);
	DAOlignefacturegarantie::insert($DB_site2,"base",$garantie->garantieid,$_POST['artid'],$lfid,"Garantie",$garantie->duree,$garantie->fournisseur,$codefourn,$user['paysid'],$garantie->duree,$garantie->type_garantie,$garantie->prix,$garantie->ppi,$garantie->paht);
        if($garantie->prix>0){
            $DB_site2->query("UPDATE lignefacture SET prix=prix+".$garantie->prix." WHERE lignefactureid=".$lfid);
        }
    }
         
    if(isset($_POST['garantie']) && $_POST['garantie'] > 0){
	$garantie=new garantie($DB_site2,"base","FR",$_POST['garantie'],$_POST['artid'],$_POST['price']);
	$codefourn=DAOfournisseur::loadCodeFournisseurByName($DB_site2,$garantie->fournisseur);
	DAOlignefacturegarantie::insert($DB_site2,"base",$garantie->garantieid,$_POST['artid'],$lfid,"Garantie",$garantie->duree,$garantie->fournisseur,$codefourn,$user['paysid'],$garantie->duree,$garantie->type_garantie,$garantie->prix,$garantie->ppi,$garantie->paht);
	if($garantie->prix>0){
            $DB_site2->query("UPDATE lignefacture SET prix=prix+".$garantie->prix." WHERE lignefactureid=".$lfid);
	}
    }
}

if(isset($_POST['cadeauid'])){
	$total_facture = $DB_site2->query("SELECT prix, qte FROM facture f INNER JOIN lignefacture lf ON lf.factureid = f.facturid WHERE f.facturid=".$_GET['facturid']);
	$total_fac = 0;
	while($total=$DB_site2->fetch_array($total_facture)){
		$total_fac += $total['prix'] * $total["qte"];
	}
	//si le montant cadeau est en % on fait le calcul pour le mettre en euros :
	if($_POST['typecadeau'] == '1'){
		$montant_cadeau = $total_fac * floatval($_POST['montantcadeau']) / 100;
	}
	else{
		$montant_cadeau = floatval($_POST['montantcadeau']);
	}
    $DB_site2->query("UPDATE facture SET cadeauid='".mysqli_real_escape_string($DB_site2->link_id,$_POST['cadeauid'])."', montantcadeau=".number_format($montant_cadeau,2,".","")." WHERE facturid=".$_GET['facturid']);
}

if(isset($_POST['shippingorder'])){
	$suivi="Modification commande";

	///Pour modifier une ligne : \\\
	if(isset($_POST['update'])){
		//ancienne ligne
		$old=$DB_site2->query_first("SELECT lf.* FROM lignefacture lf WHERE lf.lignefactureid=".intval($_POST['shippingorder']));
		//ancienne ligne garantie fournisseur
		$old_garantie_fourn=$DB_site2->query_first("SELECT lf.* FROM lignefacturegarantie lf INNER JOIN garantie g ON g.garantieid = lf.garantieid WHERE lf.lignefactureid=".intval($_POST['shippingorder'])." AND g.utilisation_garantie = '0'");
		//ancienne ligne garantie complémentaire
		$old_garantie_compl=$DB_site2->query_first("SELECT lf.* FROM lignefacturegarantie lf INNER JOIN garantie g ON g.garantieid = lf.garantieid WHERE lf.lignefactureid=".intval($_POST['shippingorder'])." AND g.utilisation_garantie = '1'");

		//infos de l'article de la ligne
		if(isset($_POST['artid'])) {$article=$DB_site2->query_first("SELECT tauxchoisi,paht,ecopartht,prix FROM article WHERE artid=".$_POST['artid']);}
	
		$suivi.=" article ".$old['artcode'];

		//Modifier la qte
		if(isset($_POST['qtt']) && $_POST['qtt']!=$old['qte']){
			$suivi .="<br>Quantité : ".$old['qte']." => ".$_POST['qtt'];
			$DB_site2->query("UPDATE lignefacture SET qte=".$_POST['qtt']." WHERE lignefactureid=".intval($_POST['shippingorder']));
		} 

		//Modifier le prix
		if(isset($_POST['prixtype'])){
			//Si prix personnalisé
			if($_POST['prixtype'] == 'price_choice'){
				$_POST['prixtype'] = $_POST['price_choice'];
			}

			//Pour gérer le taux de TVA
			if(!isset($_POST['tva']) || $_POST['tva']==""){
				$tvapays=$DB_site2->query_first("SELECT p.*,u.tvaintracom AS userintracom FROM pays p INNER JOIN utilisateur u ON(p.paysid=u.paysid) INNER JOIN facture f ON (u.userid=f.userid) WHERE f.facturid=".$_POST['facturid']);
				if($article[0]=="1") $tva=$tvapays['TVAtauxnormal'];
				if($article[0]=="2") $tva=$tvapays['TVAtauxreduit'];
				if($tvapays['tvaintracom']==1 && $tvapays['userintracom']!=""){
					$tvafrance=$DB_site2->query_first("SELECT * FROM pays WHERE paysid=57");
					switch($article[0]){
						case "1":
						$tva=$tvafrance['TVAtauxnormal'];
						break;
						case "2":
						$tva=$tvapays['TVAtauxreduit'];
						break;
					}
				}
			}else{
				$tva=$_POST['tva'];
			}

			$suivi .="<br>Prix : ".$old['prix']." => ".$_POST['prixtype'];
			$DB_site2->query("UPDATE lignefacture SET prix=".$_POST['prixtype'].", tva=".floatval($tva)." WHERE lignefactureid=".intval($_POST['shippingorder']));
		}

		//Pour modifier la garantie fournisseur
		if(isset($_POST['garantie_fourn']) && $_POST['garantie_fourn']!=$old_garantie_fourn['garantieid']){
			//les infos de la nouvelle garantie :
			$garantie=$DB_site2->query_first("SELECT g.*, g.paht, g.prix,g.ppi, cf.code,gt.garantie_tranche_prix,gt.garantie_tranche_ppi FROM garantie g INNER JOIN garantie_tranche gt ON g.garantieid = gt.garantieid INNER JOIN fournisseur_code cf ON cf.fournisseur = g.fournisseur WHERE g.garantieid=".$_POST['garantie_fourn']." AND '".$article['prix']."' BETWEEN gt.garantie_tranche_prix_mini AND gt.garantie_tranche_prix_maxi");
			if(isset($garantie)){
				$suivi .="<br>Garantie : ".$old_garantie_compl['fournisseur']." ".$old_garantie_compl['duree']." ans => ".$garantie['comm'];
				$DB_site2->query("UPDATE lignefacturegarantie SET garantieid =".$garantie['garantieid'].",differenceprix = ".unserialize($garantie['garantie_tranche_prix'])['base'].",ppi=".unserialize($garantie['garantie_tranche_ppi'])['base'].", paht=".($garantie['paht']==''?0:$garantie['paht']).", duree=".$garantie['duree'].", dureeReelle=".$garantie['duree']." , fournisseur='".$garantie['fournisseur']."', codeFournisseur = '".$garantie['code']."', type=".$garantie['type_garantie']." WHERE lignefactureid=".intval($_POST['shippingorder'])." AND garantieid =".$old_garantie_fourn['garantieid']);
				$DB_site2->query("UPDATE lignefacture SET prix=".(intval($old['prix']) - intval($old_garantie_fourn['differenceprix']) + intval(unserialize($garantie['garantie_tranche_prix'])['base']))." WHERE lignefactureid=".intval($_POST['shippingorder']));
			}
		}

		//Pour modifier la garantie complémentaire
		if(isset($_POST['garantie_compl']) && $_POST['garantie_compl']!=$old_garantie_compl['garantieid']){
			//les infos de la nouvelle garantie :
			$garantie=$DB_site2->query_first("SELECT g.*, g.paht, g.prix,g.ppi, cf.code,gt.garantie_tranche_prix,gt.garantie_tranche_ppi FROM garantie g INNER JOIN garantie_tranche gt ON g.garantieid = gt.garantieid INNER JOIN fournisseur_code cf ON cf.fournisseur = g.fournisseur WHERE g.garantieid=".$_POST['garantie_compl']." AND '".$article['prix']."' BETWEEN gt.garantie_tranche_prix_mini AND gt.garantie_tranche_prix_maxi");
			if(isset($garantie)){
				$suivi .="<br>Garantie : ".$old_garantie_compl['fournisseur']." ".$old_garantie_compl['duree']." ans => ".$garantie['comm'];
				$DB_site2->query("UPDATE lignefacturegarantie SET garantieid =".$garantie['garantieid'].",differenceprix = ".unserialize($garantie['garantie_tranche_prix'])['base'].",ppi=".unserialize($garantie['garantie_tranche_ppi'])['base'].", paht=".($garantie['paht']==''?0:$garantie['paht']).", duree=".$garantie['duree'].", dureeReelle=".$garantie['duree']." , fournisseur='".$garantie['fournisseur']."', codeFournisseur = '".$garantie['code']."', type=".$garantie['type_garantie']." WHERE lignefactureid=".intval($_POST['shippingorder'])." AND garantieid =".$old_garantie_compl['garantieid']);
				$DB_site2->query("UPDATE lignefacture SET prix=".(intval($old['prix']) - intval($old_garantie_compl['differenceprix']) + intval(unserialize($garantie['garantie_tranche_prix'])['base']))." WHERE lignefactureid=".intval($_POST['shippingorder']));
			}
		}

		//Pour modifier les options
		if(isset($_POST['options'])){
			//On récupère les valeurs de tous les select de toutes les caractéristiques et on récupère 2 caractid ($matches[0][0] = l'ancien et $matches[0][2] = le nouveau)
			foreach($_POST['options'] as $opts){
				preg_match_all('/[0-9]*/',$opts,$matches);
				$old_options=$DB_site2->query_first("SELECT * FROM lignefacturecaracteristique lf WHERE lf.lignefactureid=".intval($_POST['shippingorder']). " AND lf.caractvalid = ".$matches[0][0]);
				$options=$DB_site2->query_first("SELECT cv.*,c.libelleFR as libellecarac, ac.achat, ac.differenceprix FROM caracteristiquevaleur cv INNER JOIN caracteristique c ON c.caractid = cv.caractid INNER JOIN article_caractval ac ON ac.caractvalid = cv.caractvalid WHERE cv.caractvalid =".$matches[0][2]);
				//Si le prix de l'option est fixe :
				if($options['prix_fixe'] == '1'){
					$paht = $options['paht'];
					//Selon le bouton sélectionné "prix d'achat/vente/offert" :
					if($_POST['caract_prix_type'] == 'achat'){
						$diffprix = $options['paht'];
						if($old_options['differenceprix'] !== $options['paht']){
							$DB_site2->query("UPDATE lignefacture SET prix=".(intval($old['prix']) - intval($old_options['differenceprix']) + intval($options['paht']))." WHERE lignefactureid=".intval($_POST['shippingorder']));
						}
					}
					elseif($_POST['caract_prix_type'] == 'free'){
						$diffprix = 0;
						if($old_options['differenceprix'] !== 0){
							$DB_site2->query("UPDATE lignefacture SET prix=".(intval($old['prix']) - intval($old_options['differenceprix']))." WHERE lignefactureid=".intval($_POST['shippingorder']));
						}
					}
					else{
						$diffprix = $options['pvttc'];
						if($old_options['differenceprix'] !== $options['pvttc']){
							$DB_site2->query("UPDATE lignefacture SET prix=".(intval($old['prix']) - intval($old_options['differenceprix']) + intval($options['pvttc']))." WHERE lignefactureid=".intval($_POST['shippingorder']));
						}
					}
				}
				//Si le prix de l'option n'est pas fixe :
				else{
					$paht = $options['achat'];
					//Selon le bouton sélectionné "prix d'achat/vente/offert" :
					if($_POST['caract_prix_type'] == 'achat'){
						$diffprix = $options['achat'];
						if($old_options['differenceprix'] !== $options['achat']){
							$DB_site2->query("UPDATE lignefacture SET prix=".(intval($old['prix']) - intval($old_options['differenceprix']) + intval($options['achat']))." WHERE lignefactureid=".intval($_POST['shippingorder']));
						}
					}
					elseif($_POST['caract_prix_type'] == 'free'){
						$diffprix = 0;
						if($old_options['differenceprix'] !== 0){
							$DB_site2->query("UPDATE lignefacture SET prix=".(intval($old['prix']) - intval($old_options['differenceprix']))." WHERE lignefactureid=".intval($_POST['shippingorder']));
						}
					}
					else{
						$diffprix = unserialize($options['differenceprix'])['base'];
						if($old_options['differenceprix'] !== $options['differenceprix']){
							$DB_site2->query("UPDATE lignefacture SET prix=".(intval($old['prix']) - intval($old_options['differenceprix']) + intval($diffprix))." WHERE lignefactureid=".intval($_POST['shippingorder']));
						}
					}
				}
				$suivi .="<br>Options : ".$old_options['libelle_caractval']." (".$old_options['differenceprix']."€) => ".$options['libelleFR'] ." (".$diffprix."€)";
				$DB_site2->query("UPDATE lignefacturecaracteristique SET caractvalid =".$options['caractvalid'].", libelle_caract = '".addslashes($options['libellecarac'])."', libelle_caractval = '".addslashes($options['libelleFR'])."', ref='".$options['ref']."', paht=".$paht.", differenceprix = ".$diffprix." WHERE lignefactureid=".intval($_POST['shippingorder'])." AND caractvalid =".$old_options['caractvalid']);
			}
		}
		//On change le shippingorder qui était la lignefacturid par la facturid, car on en a besoin pour le suivi :
		$_POST['shippingorder'] = $_POST['facturid'];
	}
	///FIN Pour modifier une ligne\\\

	///Pour ajouter une ligne :\\\
	else {
		$where_etat="";
		$old=$DB_site2->query_first("SELECT f.*, ef.libelleFR FROM facture f INNER JOIN etatfacture ef ON f.valide = ef.valudeid WHERE f.facturid=".intval($_POST['shippingorder']));
		if($_POST['shipprice']!=$old['prixport']) $suivi.="<br>Frais port : ".$old['prixport']." => ".floatval($_POST['shipprice']);
		if($_POST['tvaport']!=$old['tvaport']) $suivi.="<br>TVA port : ".$old['tvaport']." => ".floatval($_POST['tvaport']);
		if($_POST['modelivraison']!=$old['modelivraison']) $suivi.="<br>Mode de livraison : ".$old['modelivraison']." => ".$_POST['modelivraison'];
		if($_POST['moyen']!=$old['moyenid']) $suivi.="<br>Moyen de paiement : ".$old['moyenid']." => ".$_POST['moyen'];
		if($_POST['etat']!=""){
			$suivi.="<br>Etat de la commande : ".$old['libelleFR']." => Non traité";
			$where_etat = "valide=".intval($_POST['etat']).",";
		}
		$incoterms=trim($_POST['incoterms']." ".$_POST['incoterms2']);
		if($incoterms!=$old['incoterms']) $suivi.="<br>Incoterms : ".$old['incoterms']." => ".$incoterms;

		$DB_site2->query("UPDATE facture SET prixport=".floatval($_POST['shipprice']).", tvaport=".floatval($_POST['tvaport']).", modelivraison='".$_POST['modelivraison']."', moyenid=".intval($_POST['moyen']).", ".$where_etat." incoterms='".mysqli_real_escape_string($DB_site2->link_id,$incoterms)."', txtlivr='".mysqli_real_escape_string($DB_site2->link_id,$_POST['txtlivr'])."' WHERE facturid=".intval($_POST['shippingorder']));
	}

	$DB_site2->query("INSERT INTO suivi_commande SET facturid=".intval($_POST['shippingorder']).", date=".time().", commercial='".$user_info['username']."', suivi='".mysqli_real_escape_string($DB_site2->link_id,$suivi)."'");
}

if(isset($_GET['del'])){
    $lf=$DB_site2->query_first("SELECT * FROM lignefacture WHERE lignefactureid=".intval($_GET['del']));
    if($lf!=""){
	$suivitxt="Suppression ".$lf['qte']." ".$lf['artcode'];
	$DB_site2->query("INSERT INTO suivi_commande SET facturid=".$lf['factureid'].", date=".time().", commercial='".$user_info[username]."', suivi='".mysqli_real_escape_string($DB_site2->link_id,$suivitxt)."'");
    }
    $DB_site2->query("DELETE FROM lignefacture WHERE lignefactureid=".$_GET['del']);
    if(intval($lf['lignefacturedissid']) != 0) $DB_site2->query("DELETE FROM lignefacturediss WHERE lignefactureid=".$lf['lignefacturedissid']);
    $DB_site2->query("DELETE FROM lignefacturecaracteristique WHERE lignefactureid=".$_GET['del']);
}

if(isset($_GET['createavoir'])){
    $lignes=explode(',',$_GET['createavoir']);
    $oldfacture=$DB_site2->query_first("SELECT f.* FROM facture f INNER JOIN lignefacture lf ON (f.facturid=lf.factureid) WHERE lf.lignefactureid=".$lignes[0]);
    $provenance="avoir";
    $provenanceid=$oldfacture['facturid'];
    $mp_facturid="";
    if($oldfacture['provenance']=="mp"){
        $provenance="mp";
        $mp_facturid=$oldfacture['mp_facturid'];
        $provenanceid=$oldfacture['provenanceid'];
    }
    $sql="INSERT INTO facture SET userid=".$oldfacture['userid'].", datefacture=NOW(), heurefacture=".time().", moyenid=2, valide=7, nom='".$oldfacture['nom']."', prenom='".$oldfacture['prenom']."', codepostal='".$oldfacture['codepostal']."', adresse='".mysqli_real_escape_string($DB_site2->link_id,$oldfacture['adresse'])."', ville='".mysqli_real_escape_string($DB_site2->link_id,$oldfacture['ville'])."', telephone='".$oldfacture['telephone']."', raisonsociale='".mysqli_real_escape_string($DB_site2->link_id,$oldfacture['raisonsociale'])."', paysid=".$oldfacture['paysid'].", mail='".$oldfacture['mail']."', langue='FR', infotranspo='', tvaport=".$oldfacture['tvaport'].", provenance='".$provenance."', provenanceid='".$provenanceid."', mp_facturid='".$mp_facturid."'";
	
    $DB_site2->query($sql);
    $facturid=$DB_site2->insert_id();
    $_GET['facturid']=$facturid;
    
    $totalttc=array();
    foreach($lignes as $ligneid){
	$ligne=$DB_site2->query_first("SELECT * FROM lignefacture WHERE lignefactureid=".$ligneid);
	$DB_site2->query("INSERT INTO lignefacture SET factureid=$facturid, artid=".$ligne['artid'].", artcode='avoir', catid=".$ligne['catid'].", qte=".$ligne['qte'].", libelle='Remboursement ".addslashes($ligne['libelle'])." ".$ligne['artcode']."', prix=".(-$ligne['prix']).", tva=".$ligne['tva'].", ecopart=".floatval($ligne['ecopart']));
	$totalttc[$ligne['tva']]+=$ligne['qte']*$ligne['prix'];
    }
    if($_GET['fdp']=="1" && $oldfacture['prixport']>0){
	$DB_site2->query("INSERT INTO lignefacture SET factureid=$facturid, artid=0, artcode='avoir', catid=0, qte=1, libelle='Remboursement frais de port', prix=".(-$oldfacture['prixport']).", tva=".$oldfacture['tvaport']);
    }
    if($_GET['cadeau']=="1"){
	$infoscadeau=$DB_site2->query_first("SELECT * FROM cadeau WHERE cadeauid = '$oldfacture[cadeauid]'");
	if($infoscadeau['code']!=""){
	    foreach($totalttc as $tx=>$ttc){
		$txmontant=round($oldfacture['montantcadeau']*$ttc/array_sum($totalttc),2);
		$DB_site2->query("INSERT INTO lignefacture SET factureid=$facturid, artid=0, artcode='avoir', catid=0, qte=1, libelle='Code promo $infoscadeau[code]', prix=".$txmontant.", tva=".$tx);
	    }
	}
	$infocaviste=$DB_site2->query_first("SELECT * FROM code_caviste WHERE code='$oldfacture[cadeauid]'");
	if(($infocaviste['type']==1 && $infocaviste['mini']>$oldfacture['montantcadeau']) || $infocaviste['type']==2){
	    foreach($totalttc as $tx=>$ttc){
		$txmontant=round($oldfacture['montantcadeau']*$ttc/array_sum($totalttc),2);
		$DB_site2->query("INSERT INTO lignefacture SET factureid=$facturid, artid=0, artcode='avoir', catid=0, qte=1, libelle='Code réduction $infocaviste[code]', prix=".$txmontant.", tva=".$tx);
	    }
	}
    }
}

?>
<script language="javascript">

	//fonction pour afficher la bonne div d'update
	function modif_ligne(id,lignefac){
		if(jQuery("td#"+id+"_"+lignefac).css('backgroundColor')=='rgba(0, 0, 0, 0)'){
			jQuery("td#"+id+"_"+lignefac).css('backgroundColor','rgb(221, 221, 221)');
		}
		else{
			jQuery("td#"+id+"_"+lignefac).css('backgroundColor','rgba(0, 0, 0, 0)');
		}
		jQuery(".td_select").not("td#"+id+"_"+lignefac).css('backgroundColor','rgba(0, 0, 0, 0)');
		jQuery(".divmodif_").not("#divmodif_"+id+"_"+lignefac).hide();
		jQuery("#divmodif_"+id+"_"+lignefac).toggle();
	}

	function srccontact(txt){
	    if(txt.length<3) return;
	    document.getElementById('contactresult').innerHTML='Recherche en cours ...';
	    document.getElementById('contactresult').style.display='block';
	    document.getElementById('showresults').style.display='none';
	    ajaxrequest('facture_ajax.php?contact='+txt,contactcb);
	}

	function contactcb(httprequest){
	    document.getElementById('contactresult').innerHTML=httprequest.responseText;
	}

	function selectcontact(type,id){
	    if(id=='') return;
	    ajaxrequest('facture_ajax.php?contactinfo='+id+'&type='+type,selectcontactcb);
	}

	function selectcontactcb(httprequest){
	    data=httprequest.responseText.split('|');
	    document.getElementsByName('billing_company')[0].value=data[0];
	    document.getElementsByName('billing_last_name')[0].value=data[1];
	    document.getElementsByName('billing_first_name')[0].value=data[2];
	    document.getElementsByName('billing_mail')[0].value=data[3];
	    document.getElementsByName('billing_phone')[0].value=data[4];
	    document.getElementsByName('billing_address_1')[0].value=data[5];
	    document.getElementsByName('billing_zip')[0].value=data[6];
	    document.getElementsByName('billing_city')[0].value=data[7];
	    document.getElementsByName('billing_country')[0].value=data[8];
	    document.getElementById('contactresult').style.display='none';
	}

	var prices            = new Array();
	var htprices          = new Array();
	var strike_out_prices = new Array();
	var buyprices         = new Array();
	var pahtprices        = new Array();
	var descriptions      = new Array();
	var barcodes          = new Array();
	var stocks            = new Array();
	var artids            = new Array();

	function srcprod(){
	  	
		var src       = document.getElementById('newsrc').value;
		var site_code = document.getElementById('site_code').value;

		var destinataire_id = '';
		if(document.getElementById('destinataire_id')) destinataire_id = document.getElementById('destinataire_id').value;

		if(src=='') return;

		prices            = new Array();
		htprices          = new Array();
		strike_out_prices = new Array();
		buyprices         = new Array();
		pahtprices        = new Array();
		descriptions      = new Array();
		barcodes          = new Array();
		stocks            = new Array();
		artids            = new Array();

		document.getElementById('mpprodresults').style.display='table-cell';
		document.getElementById('mpprodresults').innerHTML="<p align='center'><img src='../configurations/<?php echo $host; ?>/images/templates/circle_animation.gif'></p>";
		document.getElementById('addprod2').style.display='none';
		document.getElementsByName('tva')[0].selectedIndex=0;

		var url = 'marketplace/src.php?txt='+src+'&lot=0&site_code='+site_code;
		ajaxrequest(url,srcCallback);
	}

	function srcCallback(httprequest){
		
		var xml  = httprequest.responseXML.documentElement;
		var html = "";

		var vistxt = new Array();
		vistxt[0] = "<br><span style='color:red;'>Invisible</span>";
		vistxt[1] = "<br>Visible";
		
		if(xml.getElementsByTagName('prod')[0]){
            
            html += "<table width='100%' style='border:solid thin #808080;'><tr>";
            
            for(i=0;i<xml.getElementsByTagName('prod').length;i++){
				
				var description = xml.getElementsByTagName('prod')[i].firstChild.nodeValue;
				var id          = xml.getElementsByTagName('prod')[i].getAttribute('id');
				var ref         = xml.getElementsByTagName('prod')[i].getAttribute('ref');
				var gamme       = xml.getElementsByTagName('prod')[i].getAttribute('gamme');
				var tva         = xml.getElementsByTagName('prod')[i].getAttribute('tva');
				var price       = xml.getElementsByTagName('prod')[i].getAttribute('price');
				var strike_out  = xml.getElementsByTagName('prod')[i].getAttribute('strike_out');
				var buyprice    = xml.getElementsByTagName('prod')[i].getAttribute('buyprice');
				var barcode     = xml.getElementsByTagName('prod')[i].getAttribute('barcode');
				var stock       = xml.getElementsByTagName('prod')[i].getAttribute('stock');
				var artid       = xml.getElementsByTagName('prod')[i].getAttribute('artid');
				var active      = xml.getElementsByTagName('prod')[i].getAttribute('active');
                         
				html += "<tr>";
				html += "	<td style='border-bottom:solid thin #cccccc;'>"+id+"<br>"+ref+" "+gamme+vistxt[active]+"</td>";
				html += "	<td style='border-bottom:solid thin #cccccc;'>"+description+"<br><span style='color:red'>"+stock+"</span></td>";
				html += "	<td style='border-bottom:solid thin #cccccc;'><a href=javascript:selectprod('"+id+"')>Sélectionner</a></td>";
				html += "</tr>";
				
				prices[id]            = price;
				htprices[id]          = Math.round(100*price/(1+tva/100))/100;
				strike_out_prices[id] = strike_out;
				buyprices[id]         = buyprice;
				pahtprices[id]        = Math.round(100*buyprice/(1+tva/100))/100;
				descriptions[id]      = description;
				barcodes[id]          = barcode;
				stocks[id]            = stock;
				artids[id]            = artid;
			}
            
            html += "</table>";

		} else {

			html = "Pas de résultat";
		}

		html += "<input type='button' value='Annuler' onclick=document.getElementById('mpprodresults').style.display='none'; document.getElementById('addprod2').style.display='table-cell';>";
		
		document.getElementById('mpprodresults').innerHTML=html;      
    }

	function selectprod(comp_id){

		document.getElementById('mpprodresults').innerHTML = '';
		document.getElementById('addprod').style.display   = 'table-cell';

		document.getElementsByName('prod')[0].value            = comp_id;
		document.getElementsByName('buyprice')[0].value        = buyprices[comp_id];
		document.getElementsByName('selprice')[0].value        = prices[comp_id];
		document.getElementsByName('price')[0].value           = buyprices[comp_id];
		document.getElementsByName('strikeout_price')[0].value = strike_out_prices[comp_id];
		document.getElementsByName('description')[0].value     = descriptions[comp_id];
		document.getElementsByName('artid')[0].value           = artids[comp_id];
		document.getElementsByName('prixtype_add')[1].checked      = true;

		caractload();
        }
        
        function caractload(){
                var comp_id=document.getElementsByName('prod')[0].value;
                document.getElementById('divopts').innerHTML = "<p align='center'><img src='../configurations/<?php echo $host; ?>/images/templates/circle_animation.gif'></p>";

		var destinataire_id = '';
		if(document.getElementById('destinataire_id')) destinataire_id = document.getElementById('destinataire_id').value;

		var paysid = parseInt(document.getElementById('paysid').value);
		var price=parseFloat(document.getElementsByName('price')[0].value);
		var url    = "marketplace/src.php?artid="+artids[comp_id]+"&paysid="+paysid+"&price="+price;
		if(destinataire_id==1) url+="&destinataire="+destinataire_id;
		
		ajaxrequest(url,caractCallback);
        }

	function caractCallback(httprequest){

		document.getElementById('divopts').innerHTML = "";

		var options = new Array();
		var xml     = httprequest.responseXML.documentElement;
		
		if(xml.getElementsByTagName('caract')[0]){

			for(i=0;i<xml.getElementsByTagName('caract').length;i++){
				var id=xml.getElementsByTagName('caract')[i].getAttribute('id');
				var name=xml.getElementsByTagName('caract')[i].getAttribute('name');
				var libelle=xml.getElementsByTagName('caract')[i].getAttribute('value');
				options.push({id:id,name:name,libelle:libelle});
			}

			var i=0;

			var caractname=new Array();
			for(i=0;i<options.length;i++){
				if(caractname[options[i].name]==undefined){
				  caractname[options[i].name]=i;
				              var newselect = document.createElement('select');
				              newselect.name="caract_"+i;
				              var newname=document.createElement('input');
				              newname.type='hidden';
				              newname.name='caractname_'+i;
				              newname.value=options[i].name;
				  var p=document.createElement('p');
				              p.style.margin='0px;'
				              var txt=document.createElement('span');
				              txt.innerHTML=options[i].name+" : ";
				              p.appendChild(txt);
				              p.appendChild(newname);
				              p.appendChild(newselect);
				              document.getElementById('divopts').appendChild(p);
				}

				var selectid=caractname[options[i].name];
				          var opt=new Option(options[i].libelle,options[i].id);
				document.getElementsByName('caract_'+selectid)[0].options[newselect.length]=opt;

			}
			var p=document.createElement('p');
			p.innerHTML="Prix à appliquer sur les options : <input type='radio' name='caract_prix_type' value='achat'> achat &nbsp; <input type='radio' name='caract_prix_type' value='differenceprixht' checked> vente &nbsp; <input type='radio' name='caract_prix_type' value='free'> offert";
			document.getElementById('divopts').appendChild(p);
		      
		}
               
		if(xml.getElementsByTagName("garantie")[0]){
			for(i=0;i<xml.getElementsByTagName('garantie').length;i++){
				var garantiexml=xml.getElementsByTagName('garantie')[i];
				var utilisation=garantiexml.getAttribute("utilisation");
				if(utilisation==0){
                                    if(selectfourn==undefined){
                                        var selectfourn=document.createElement("select");
                                        selectfourn.name="gar_fourn";
                                        var pfourn=document.createElement("p");
                                        pfourn.innerHTML="Garantie fournisseur : ";
                                        pfourn.appendChild(selectfourn);
                                        document.getElementById('divopts').appendChild(pfourn);
                                    }
                                    var opt=new Option(garantiexml.firstChild.nodeValue,garantiexml.getAttribute("garantieid"));
                                    selectfourn.options[selectfourn.length]=opt;
				}else{
                                    var opt=new Option(garantiexml.firstChild.nodeValue+" ("+garantiexml.getAttribute("prix")+"€)",garantiexml.getAttribute("garantieid"));
                                    if(selectcomp==undefined){
                                        var selectcomp=document.createElement('select');
                                        selectcomp.name="garantie";
                                        var opt1=new Option("Aucune garantie complémentaire",0);
                                        selectcomp.options[selectcomp.length]=opt1;
                                        var pcomp=document.createElement("p");
                                        pcomp.innerHTML="Garantie complémentaire : ";
                                    }
                                    selectcomp.options[selectcomp.length]=opt;
				}
	  		}
			if(selectcomp!=undefined){
				pcomp.appendChild(selectcomp);
				document.getElementById('divopts').appendChild(pcomp);
			}
			
		}
        }
        
        function cadeausrc(txt){
                if(txt.length>=3){
                        var url="marketplace/src.php?codepromo="+txt;
                        ajaxrequest(url,function(httprequest){
                                var xml     = httprequest.responseXML.documentElement;
                                var html="<table style='border:solid thin #808080;'>";
                                var codes=xml.getElementsByTagName("codepromo");
                                for(i=0;i<codes.length;i++){
                                    var cadeauid=codes[i].getAttribute("cadeauid");
                                    var code=codes[i].getAttribute("code");
                                    var valeur=codes[i].getAttribute("valeur");
                                    var applicable=codes[i].getAttribute("applicable");
                                    var client=codes[i].getAttribute("client");
                                    var actif=codes[i].getAttribute("active")==0?" <span style=\"color:red\">inactif</span>":"";
                                    var mini=codes[i].getAttribute("minimum");
                                    var minimum=mini>0?" minimum "+mini+"€":"";

                                    html+="<tr>";
                                    html+="<td>"+code+"</td>";
                                    html+="<td><b>"+valeur+"</b>"+minimum+"<br>applicable sur "+applicable+" pour "+client+actif+"</td>";
                                    html+="<td><a href=\"javascript:selectcp('"+cadeauid+"','"+code+"','"+valeur+"')\">Sélectionner</a></td>";
                                    html+="</tr>";
                                }
                                
                                var codes=xml.getElementsByTagName("codecaviste");
                                for(i=0;i<codes.length;i++){
                                    var cadeauid=codes[i].getAttribute("code");
                                    var code=cadeauid;
                                    var caviste=codes[i].getAttribute("caviste");
                                    var valeur=codes[i].getAttribute("pc")+"% (promo "+codes[i].getAttribute("pcpromo")+"%)";
                                    var mini=codes[i].getAttribute("mini");
                                    var minimum=mini>0?" minimum "+mini+"€":"";
                                    var validite=codes[i].getAttribute("validite");
                                    
                                    html+="<tr>";
                                    html+="<td>"+code+"</td>";
                                    html+="<td><b>"+valeur+"</b>"+minimum+"<br>valable chez "+caviste+" jusqu'au "+validite+"</td>";
                                    html+="<td><a href=\"javascript:selectcp('"+cadeauid+"','"+code+"')\">Sélectionner</a></td>";
                                    html+="</tr>";
                                }
                                
                                html+="</table>";
                                
                                document.getElementById("cpresults").innerHTML=html;
                        });
                }
        }
        
        function selectcp(cadeauid,code,valeur){
                document.getElementById("cpresults").innerHTML="";
                document.getElementsByName("codepromo")[0].value=code;
                document.getElementsByName("cadeauid")[0].value=cadeauid;
                document.getElementsByName("montantcadeau")[0].value=valeur.replace("€","");
                document.getElementsByName("montantcadeau")[0].value=valeur.replace("%","");
                document.getElementById('delcp').style.display=code==""?"none":"inline";
                document.getElementById("submitcp").style.display="inline";
        }

        //fonction pour la changer la TVA quand on update un prix :
        function changeTVAmodif(ligne,value){
        	var tva = jQuery("form[name=updateline_prix_"+ligne+"]").find('#changetva').val();
        	jQuery("form[name=updateline_prix_"+ligne+"]").find('input[name=prixtype]').each(function(){
        		var valeur = jQuery(this).val();
        		if(tva==='0'){
        			if(valeur == 'price_choice'){
        				valeur = jQuery(this).next().val();
        				var prix = jQuery(this).next().val((valeur / (1 + value / 100)).toFixed(1));
        			}
        			else{
        				jQuery(this).val((valeur / (1 + value / 100)).toFixed(1));
        				jQuery(this).next().val((valeur / (1 + value / 100)).toFixed(1));
        			}
        		}
        		else{
        			if(valeur == 'price_choice'){
        				valeur = jQuery(this).next().val();
        				var prix = jQuery(this).next().val((valeur * (1 + value / 100)).toFixed(1));
        			}
        			else{
        				jQuery(this).val((valeur * (1 + value / 100)).toFixed(1));
        				jQuery(this).next().val((valeur * (1 + value / 100)).toFixed(1));
        			}
        		}
        	});
        }

	function changeTVA(value){
	      var artcode=document.getElementsByName('prod')[0].value;
	      switch(value){
		case '0':
		document.getElementsByName('buyprice')[0].value=pahtprices[artcode];
		document.getElementsByName('selprice')[0].value=htprices[artcode];
		break;

		case '':
		document.getElementsByName('buyprice')[0].value=buyprices[artcode];
		document.getElementsByName('selprice')[0].value=prices[artcode];
		break;
	      }

	      if(document.getElementsByName('prixtype_add')[0].checked) document.getElementsByName('price')[0].value=document.getElementsByName('buyprice')[0].value;
	      if(document.getElementsByName('prixtype_add')[1].checked) document.getElementsByName('price')[0].value=document.getElementsByName('selprice')[0].value;
	}

	function verifaddline(){
	      if(isNaN(document.getElementById('price2').value)){
		    alert('La somme n\'est pas un nombre');
		    return false;
	      }
	      if(document.getElementById('prodtype').value=='avoir' && document.getElementById('price2').value>0){
		    alert('Pour un avoir, la somme doit être négative');
		    return false;
	      }
	      if(document.getElementById('prodtype').value!='avoir' && document.getElementById('price2').value<0){
		    alert('la somme ne peut pas être négative');
		    return false;
	      }
	      document.forms.addline2.submit();
	}

	function choosemoyen(moyenid){
	      for(i=1;i<14;i++) if(document.getElementById('moyen'+i)) document.getElementById('moyen'+i).style.display='none';
	      if(document.getElementById('moyen'+moyenid)) document.getElementById('moyen'+moyenid).style.display='inline';
	}

	function showfacturation(elem){
	      document.getElementById('selectaccount').style.display='none';
	      document.getElementById('accountinfos').style.display='none';
	      if(elem=='selectaccount') document.getElementById('selectaccount').style.display='block';
	      if(elem=='accountinfos') document.getElementById('accountinfos').style.display='table';
	}
	function recup_cave(){
		document.getElementById('descriptionl').style.display='none';
		document.getElementById('dimensionsl').style.display='block';
		document.getElementById('poidsl').style.display='block';
	}
</script>

<p>Créer une commande :</p>
<?php

if(isset($_GET['modif1']) || isset($_GET['createfrom']) || isset($_GET['createfromuser'])){
	
	$facture     = array();
	$utilisateur = array();
	$modifopt    = "<input type=\"hidden\" name=\"useraction\" value=\"1\">";
	$optfu       = "<option></option>";

	//Création d'une commande de SAV
	if(isset($_GET['createfrom'])){

		//On regarde si la commande existe
		//Si non, c'est qu'il faut chercher dans les commandes de stock
		$sql = "SELECT *";
		$sql .= " FROM facture";
		$sql .= " WHERE facturid = ".$_GET['createfrom'];
		$facture = $DB_site2->query_first($sql);
		if($facture){ //SAV de commande classique
			
			$sql = "SELECT userid, nom, prenom";
			$sql .= " FROM utilisateur";
			$sql .= " WHERE userid = ".$facture['userid'];
			$utilisateur = $DB_site2->query_first($sql);

			$optfu .= ""
			. "<option value=\"".$utilisateur['userid']."\" style=\"color:blue;\">"
				. "Client ".$utilisateur['prenom']." ".$utilisateur['nom']
			. "</option>";

		} else { //SAV de commande de stock

			$sql = "SELECT *";
			$sql .= " FROM fournisseur";
			$sql .= " WHERE societe = 'Acilyon'";
			$sql .= " AND type = 'fournadv'";
			$fournisseur = $DB_site2->query_first($sql);

			$facture['raisonsociale'] = $fournisseur['societe'];
			$facture['nom']           = $fournisseur['nom'];
			$facture['prenom']        = $fournisseur['prenom'];
			$facture['adresse']       = $fournisseur['adresse1'];
			$facture['codepostal']    = $fournisseur['cp'];
			$facture['ville']         = $fournisseur['ville'];
			$facture['paysid']     	  = $fournisseur['paysid'];
			$facture['telephone']     = $fournisseur['telephone'];
			$facture['mail']          = $fournisseur['mail'];

		}
	}

        if(isset($_GET['createfromuser'])){
                $facture=$DB_site2->query_first("SELECT * FROM facture WHERE facturid=".$_GET['createfromuser']);
                $utilisateur=$DB_site2->query_first("SELECT * FROM utilisateur WHERE userid=".$facture['userid']);
                $optfu.="<option value=\"".$utilisateur['userid']."\" style=\"color:blue;\">Client ".$utilisateur['prenom']." ".$utilisateur['nom']."</option>";
                $_GET['createfrom']=$_GET['createfromuser'];
        }
        
        if(isset($_GET['modif1'])){
                $facture=$DB_site2->query_first("SELECT * FROM facture WHERE facturid=".$_GET['modif1']);
                $utilisateur=$DB_site2->query_first("SELECT * FROM utilisateur WHERE userid=".$facture['userid']);
                if($facture['provenance']=="sav"){
                    $orig_user=$DB_site2->query_first("SELECT * FROM utilisateur INNER JOIN facture USING(userid) WHERE facturid=".$facture['provenanceid']);
                    $optfu.="<option value=\"".$orig_user['userid']."\" style=\"color:blue;\">Client ".$orig_user['prenom']." ".$orig_user['nom']."</option>";
                }

                $modifopt="<p>Action sur le compte à facturer :
                <br><input type=\"radio\" name=\"useraction\" value=\"0\" onclick=\"showfacturation()\" checked> Aucune
                <br><input type=\"radio\" name=\"useraction\" value=\"1\" onclick=\"showfacturation('accountinfos')\"> Modifier les infos du compte ".$utilisateur['userid']." (".$utilisateur['prenom']." ".$utilisateur['nom'].") en le gardant dans la commande
                <br><input type=\"radio\" name=\"useraction\" value=\"2\" onclick=\"showfacturation('selectaccount')\"> Changer le compte client à facturer pour celui-ci :</p>";
                $showfacturation=" style=\"display:none;\"";
        }

        $requete=$DB_site2->query("SELECT paysid,libelle FROM pays");
        while($pays=$DB_site2->fetch_array($requete)){
                $optbcountries.="<option value=\"$pays[paysid]\"".($utilisateur[paysid]==$pays[paysid]?" selected":"").">$pays[libelle]</option>";
                $optscountries.="<option value=\"$pays[paysid]\"".($facture[paysid]==$pays[paysid]?" selected":"").">$pays[libelle]</option>";
        }

        $futilisateurs=$DB_site2->query("SELECT userid,nom,raisonsociale FROM utilisateur WHERE code411<>''");
        while($fu=$DB_site2->fetch_array($futilisateurs))
            $optfu.="<option value=\"$fu[userid]\">$fu[raisonsociale] $fu[nom]</option>";

        print "<div class=\"table_factures\">
        <form action=\"facture.php?action=creer\" method=\"post\">
        <input type=\"hidden\" name=\"modif\" value=\"".intval($_GET['modif1'])."\">
        <input type=\"hidden\" name=\"provenanceid\" value=\"".intval($_GET['createfrom'])."\">
        $modifopt
        <div id=\"selectaccount\"$showfacturation>Rechercher la personne à facturer : <select name=\"selectuserid\" onchange=\"selectcontact('user',this.value)\">$optfu</select></div>
        <table id=\"accountinfos\" style=\"display:none;\">
                <tr><td colspan=\"2\" height=\"25\" class=\"frametitle\">Adresse de facturation <span style=\"color:red;\">&#9888; Sera reporté sur toutes les commandes de ce compte</span></td></tr>
                <tr><td>Société</td><td><input type=\"text\" name=\"billing_company\" size=\"40\" value=\"$utilisateur[raisonsociale]\"></td></tr>
                <tr><td>Nom</td><td><input type=\"text\" name=\"billing_last_name\" size=\"40\" value=\"$utilisateur[nom]\"></td></tr>
                <tr><td>Prénom</td><td><input type=\"text\" name=\"billing_first_name\" size=\"40\" value=\"$utilisateur[prenom]\"></td></tr>
                <tr><td>Adresse</td><td><input type=\"text\" name=\"billing_address_1\" size=\"40\" value=\"$utilisateur[adresse]\"></td></tr>
                <tr><td>Code postal</td><td><input type=\"text\" name=\"billing_zip\" size=\"10\" value=\"$utilisateur[codepostal]\"></td></tr>
                <tr><td>Ville</td><td><input type=\"text\" name=\"billing_city\" size=\"40\" value=\"$utilisateur[ville]\"></td></tr>
                <tr><td>Pays</td><td><select name=\"billing_country\">$optbcountries</select></td></tr>
                <tr><td>N° TVA intracommunautaire</td><td><input type=\"text\" name=\"tvaintracom\" value=\"$utilisateur[tvaintracom]\"></td></tr>
                <tr><td>Téléphone</td><td><input type=\"text\" name=\"billing_phone\" size=\"40\" value=\"$utilisateur[telephone]\"></td></tr>
                <tr><td>E mail</td><td><input type=\"text\" name=\"billing_mail\" size=\"40\" value=\"$utilisateur[mail]\"></td></tr>
        </table>
        <br>
        <table>
                <tr><td colspan=\"2\" height=\"25\" class=\"frametitle\">Adresse de livraison</td></tr>
                <tr><td>Société</td><td><input type=\"text\" name=\"shipping_company\" size=\"40\" value=\"$facture[raisonsociale]\"></td></tr>
                <tr><td>Nom</td><td><input type=\"text\" name=\"shipping_last_name\" size=\"40\" value=\"$facture[nom]\"></td></tr>
                <tr><td>Prénom</td><td><input type=\"text\" name=\"shipping_first_name\" size=\"40\" value=\"$facture[prenom]\"></td></tr>
                <tr><td>Adresse</td><td><input type=\"text\" name=\"shipping_address_1\" size=\"40\" value=\"$facture[adresse]\"></td></tr>
                <tr><td>Code postal</td><td><input type=\"text\" name=\"shipping_zip\" size=\"10\" value=\"$facture[codepostal]\"></td></tr>
                <tr><td>Ville</td><td><input type=\"text\" name=\"shipping_city\" size=\"40\" value=\"$facture[ville]\"></td></tr>
                <tr><td>Pays</td><td><select name=\"shipping_country\">$optscountries</select></td></tr>
                <tr><td>Téléphone</td><td><input type=\"text\" name=\"shipping_phone\" size=\"40\" value=\"$facture[telephone]\"></td></tr>
                <tr><td>E mail</td><td><input type=\"text\" name=\"shipping_mail\" size=\"40\" value=\"$facture[mail]\"></td></tr>
                <tr><td>Info transporteur</td><td><textarea name=\"infotranspo\" cols=\"35\">".$facture['infotranspo']."</textarea></td></tr>
        </table>
        <input type=\"submit\" value=\"Valider\">
        </form>
        </div>";
}

if(isset($_GET['facturid'])){

	$facture=new facture($DB_site2,$_GET['facturid']);

	$utilisateur=$facture->utilisateur;

   	//Sécurité : on ne peut éditer une commande expédiée ou expédiée sans paiement
	if($facture->valide==5 || $facture->valide==8){
		print "Il est impossible de modifier une commande expédiée<br><a href=\"facture.php?action=editer&facturid=".$facture->facturid."\">Retour</a>";
		exit;
	}
  
  	$validecolors = array(
  		"black",
  		"blue",
  		"red",
  		"red",
  		"black",
  		"green",
  		"red",
  		"blue",
  		"green"
  	);

	$prixlignes    = 0;
	$totalht       = array();
	$ligne_article = $ligne_article_selec = '';

	foreach($facture->lignes as $ligne){
		
		$caracttxt = "";
		$ids_carac = array();
		
		foreach($ligne->caracteristiques() as $caractid=>$libelle){
			$caracttxt .= "<br>- ".$libelle;
			$ids_carac[] = $caractid;
		} 

		//si l'article a des caractéristiques, on va récupérer des infos (libelle de la carac, libelle de l'option, l'id de l'option, et l'id de la carac)
		if(!empty($ids_carac)){
			$caracteristiques=$DB_site2->query("SELECT c.caractid, cv.caractvalid, c.libelleFR as libellecat, cv.libelleFR as libelleval, c.marqueid, ac.differenceprixht, cv.pvht,cv.prix_fixe,cv.paht,ac.achat FROM lignefacture lf INNER JOIN article_caractval ac ON ac.artid = lf.artid INNER JOIN caracteristiquevaleur cv ON cv.caractvalid = ac.caractvalid INNER JOIN caracteristique c ON c.caractid = cv.caractid WHERE lf.lignefactureid =".$ligne->lignefactureid." AND c.caractid IN (".implode(',',$ids_carac).")");
			$libelles_carac=array();
			while($carac=$DB_site2->fetch_array($caracteristiques)){
				$libelles_carac[$carac['libellecat']][] = array($carac['libelleval'],$carac['caractvalid'],$carac['caractid']);
			}
		}
		else{
			$libelles_carac=array();
		}

		//on récupère les infos de la garantie choisie dans la commande
		$garanties = DAOgarantie::getGarantiesByProduitCmd($DB_site2,$ligne->lignefactureid);
        
        $liste_garantie =  '';
        $garanties_ligne = array();
	  	foreach($garanties as $garantie){
			
			$intitule_destinataire = '';
			if($facture->raisonsociale != ''){
			    switch($garantie['garantie_client_id']){
			         case 1:
			              $intitule_destinataire = '" - Particulier"';
			              break;
			         case 2:
			              $intitule_destinataire = '" - Pro"';
			              break;
			    }
			}
			$garanties_ligne[] = $garantie['garantieid'];

			$liste_garantie .= $intitule_destinataire." ".($garantie['cumul']==1?"+":"").$garantie['duree']." an(s) ".$garantie['libelle_type']." ".$garantie['fournisseur']."<br>";
		}

		//On liste les garanties gratuites dispo pour cet article
		$lesGaranties = DAOgarantie::loadMaxGarantieArtByPays($DB_site2,"base",'FR',$facture->paysid,$ligne->artid,$facture->raisonsociale!='' ? 2 : 1, $ligne->prix);
		$garanties_select_fourn = $garanties_select_compl = $garanties_select = $complementaire = "";
		if(count($lesGaranties) > 0){

			foreach($lesGaranties as $garantie){
				$txt_destinataire = '';
				switch($garantie->destinataire_garantie){
					case 1:
					$txt_destinataire = '- Particulier';
					break;
					case 2:
					$txt_destinataire = '- Pro';
					break;
				}

				//pour sélectionner la garantie de la commande dans le select :
				$check = "";
				if(in_array($garantie->garantieid,$garanties_ligne)){
					$check = "selected";
				}

				//garanties fournisseurs :
				if($garantie->utilisation_garantie == '0'){
					$garanties_select_fourn .= "<option value='".$garantie->garantieid."' ".$check.">".($garantie->cumul==1?"+":"").$garantie->duree." an(s) ".$garantie->libelle_type." ".$garantie->fournisseur." ".$txt_destinataire." (".$garantie->prix." €)</option>";
				}
				//garanties complémentaires :
				else{
					$complementaire=true;
					$garanties_select_compl .= "<option value='".$garantie->garantieid."' ".$check.">".($garantie->cumul==1?"+":"").$garantie->duree." an(s) ".$garantie->libelle_type." ".$garantie->fournisseur." ".$txt_destinataire." (".$garantie->prix." €)</option>";
				}
			}

			//On liste les garanties payantes dispo pour cet article
			$lesGaranties = DAOgarantie::loadGarantiesPayArtByPays($DB_site2,"base",'FR',$facture->paysid,$ligne->artid,$facture->raisonsociale!='' ? 2 : 1, $ligne->prix);
			if(count($lesGaranties) > 0){
				foreach($lesGaranties as $garantie){
					$plus  = ($garantie->cumul == 1 ? "+" : "");
					$txt_destinataire = '';
					switch($garantie->destinataire_garantie){
						case 1:
						$txt_destinataire = '- Particulier';
						break;
						case 2:
						$txt_destinataire = '- Pro';
						break;
					}

					//pour sélectionner la garantie de la commande dans le select :
					$check = "";
					if(in_array($garantie->garantieid,$garanties_ligne)){
						$check = "selected";
					}

					//garanties fournisseurs :
					if($garantie->utilisation_garantie == '0'){
						$garanties_select_fourn .= "<option value='".$garantie->garantieid."' ".$check.">".($garantie->cumul==1?"+":"").$garantie->duree." an(s) ".$garantie->libelle_type." ".$garantie->fournisseur." ".$txt_destinataire." (".$garantie->prix." €)</option>";
					}
					//garanties complémentaires :
					else{
						$complementaire=true;
						$garanties_select_compl .= "<option value='".$garantie->garantieid."' ".$check.">".($garantie->cumul==1?"+":"").$garantie->duree." an(s) ".$garantie->libelle_type." ".$garantie->fournisseur." ".$txt_destinataire." (".$garantie->prix." €)</option>";
					}
				}
			}

			$garanties_select .= "<b>Garantie fournisseur :</b> <select name='garantie_fourn'>".$garanties_select_fourn."</select>";
			if($complementaire==true){
				$garanties_select .= "<br><b>Garantie complémentaire :</b> <select name='garantie_compl'>".$garanties_select_compl."</select>";
			}
		}

		//On récupère la tva de l'article
		$prix_art = $DB_site2->query_first("SELECT prixconseilleht, prix, tauxchoisi FROM article WHERE artid =".$ligne->artid);
		$tvapays=$DB_site2->query_first("SELECT p.*,u.tvaintracom AS userintracom FROM pays p INNER JOIN utilisateur u ON(p.paysid=u.paysid) INNER JOIN facture f ON (u.userid=f.userid) WHERE f.facturid=".$facture->facturid);
		if($prix_art[2]=="1") {$tva=$tvapays['TVAtauxnormal'];}
		if($prix_art[2]=="2") {$tva=$tvapays['TVAtauxreduit'];}
		if(($tvapays['tvaintracom']==1 && $tvapays['userintracom']!="")){
			$tvafrance=$DB_site2->query_first("SELECT * FROM pays WHERE paysid=57");
			switch($article[0]){
				case "1":
				$tva = $tvafrance['TVAtauxnormal'];
				break;
				case "2":
				$tva = $tvapays['TVAtauxreduit'];
				break;
			}
		}
		$ppi = round($prix_art['prixconseilleht']+round($prix_art['prixconseilleht']*$tva/100));


		//les infos du tableau Détail des produits commandés :
		$ligne_article .= ""

		. "<tr>"
			. "<td>".$ligne->artcode."</td>"
			. "<td>".$ligne->ref."</td>";

			if(!empty($libelles_carac)){
			$ligne_article .= "<td id='opt_".$ligne->lignefactureid."' class='td_select'>".$ligne->libelle."<span style=\"font-size:10px;\">".$caracttxt."</span><p><img src=\"images/icon/edit.png\" onclick=\"modif_ligne('opt',".$ligne->lignefactureid.")\" style=\"width:15px;\"></p></td>";
			}
			else{
				$ligne_article .= "<td>".$ligne->libelle."<span style=\"font-size:10px;\">".$caracttxt."</span></td>";
			}

			$ligne_article .= "<td id='qte_".$ligne->lignefactureid."' class='td_select'>".$ligne->qte." <p><img src=\"images/icon/edit.png\" onclick=\"modif_ligne('qte',".$ligne->lignefactureid.")\" style=\"width:15px;\"></p></td>";

			if($liste_garantie!==''){
				$ligne_article .= "<td id='gar_".$ligne->lignefactureid."' class='td_select'>".$liste_garantie." <p><img src=\"images/icon/edit.png\" onclick=\"modif_ligne('gar',".$ligne->lignefactureid.")\" style=\"width:15px;\"></p></td>";
			}
			else{
				$ligne_article .= "<td>".$liste_garantie."</td>";
			}
			$ligne_article .= "<td id='prix_".$ligne->lignefactureid."' class='td_select'>".number_format($ligne->prix,2,',',' ')." <p><img src=\"images/icon/edit.png\" onclick=\"modif_ligne('prix',".$ligne->lignefactureid.")\" style=\"width:15px;\"></p></td>"
			. "<td>".number_format($ligne->totalttc,2,',',' ')."</td>"
			. "<td><a href=\"javascript:void(0)\" onclick=\"if(confirm('Supprimer cet article ?')){ location.href = 'facture.php?action=creer&facturid=".$facture->facturid."&del=".$ligne->lignefactureid."'; } return false;\"><img src=\"images/supprimer.gif\" alt=\"Supprimer\" border=\"0\"></a></td>"
		. "</tr>";

		//la div pour modifier la quantité de la ligne :
		$ligne_article_selec .= "<tr id=\"divmodif_qte_".$ligne->lignefactureid."\" class=\"divmodif_\" style=\"display: none;height:40px; background-color:#dddddd;text-align:center;\"><td>
				<p><b>Modification de ".$ligne->artcode."</b></p>
				<form name=\"updateline_qte_".$ligne->lignefactureid."\" method=\"post\" action=\"facture.php?action=modifier&facturid=".$facture->facturid."\">
					<input type=\"hidden\" name=\"shippingorder\" value=\"".$ligne->lignefactureid."\">
					<input type=\"hidden\" name=\"update\" value=\"true\">
					<input type=\"hidden\" name=\"facturid\" value='".$facture->facturid."'>
					&nbsp;Quantité <input type=\"text\" name=\"qtt\" size=\"4\" value=\"".$ligne->qte."\"> 
					<div><input type=\"submit\" value=\"Modifier la commande\"></div> 
					</form>
					</td>
					</tr>";

		//la div pour modifier le prix de la ligne :
		$ligne_article_selec .= "<tr id=\"divmodif_prix_".$ligne->lignefactureid."\" class=\"divmodif_\" style=\"display: none;height:40px; background-color:#dddddd;text-align:center;\"><td>
				<p><b>Modification de ".$ligne->artcode."</b></p>
				<form name=\"updateline_prix_".$ligne->lignefactureid."\" method=\"post\" action=\"facture.php?action=modifier&facturid=".$facture->facturid."\">
					<input type=\"hidden\" name=\"shippingorder\" value=\"".$ligne->lignefactureid."\">
					<input type=\"hidden\" name=\"update\" value=\"true\">
					<input type=\"hidden\" name=\"facturid\" value='".$facture->facturid."'>
					<input type=\"hidden\" name=\"artid\" value=\"".$ligne->artid."\">
					<p>Taux TVA : 
					<select name=\"tva\" id=\"changetva\" onchange=\"changeTVAmodif(".$ligne->lignefactureid.",".$tva.")\">
						<option value=''>Taux article</option>
						<option value=\"0\">0%</option>
					</select></p>
					<input type=\"radio\" name=\"prixtype\" value='price_choice'>
					Prix unit. TTC <input type=\"text\" name=\"price_choice\" size=\"6\" value='".$ligne->prix."'> 
					&nbsp;<input type=\"radio\" name=\"prixtype\" value='".($ligne->paht+($ligne->paht*$tva/100))."' ".($prix_art['prix']==($ligne->paht+($ligne->paht*$tva/100))?"checked":"")."> Prix d'achat TTC
					<input type=\"text\" value='".($ligne->paht+($ligne->paht*$tva/100))."' size=\"6\" style=\"background-color: #e9e5e5;\" readonly> 
					&nbsp;
					<input type=\"radio\" name=\"prixtype\" value='".$prix_art['prix']."' ".($prix_art['prix']==$ligne->prix?"checked":"")."> Prix de vente TTC
					<input type=\"text\" value='".$prix_art['prix']."' size=\"6\" style=\"background-color: #e9e5e5;\" readonly>
					&nbsp;
					<input type=\"radio\" name=\"prixtype\" value='".$ppi."' ".($ligne->prix==$ppi?"checked":"")."> Prix PPI TTC
					<input type=\"text\" value='".$ppi."' size=\"6\" style=\"background-color: #e9e5e5;\" readonly>
					<div><input type=\"submit\" value=\"Modifier la commande\" ></div> 
					</form>
					</td>
					</tr>";

		//la div pour modifier les options, si existantes :
		if(!empty($libelles_carac)){
			$ligne_article_selec .= "<tr id=\"divmodif_opt_".$ligne->lignefactureid."\" class=\"divmodif_\" style=\"display: none;height:40px; background-color:#dddddd;text-align:center;\"><td>
			<p><b>Modification de ".$ligne->artcode."</b></p>
			<form name=\"updateline_opt_".$ligne->lignefactureid."\" method=\"post\" action=\"facture.php?action=modifier&facturid=".$facture->facturid."\">
			<input type=\"hidden\" name=\"shippingorder\" value=\"".$ligne->lignefactureid."\">
			<input type=\"hidden\" name=\"update\" value=\"true\">
			<input type=\"hidden\" name=\"facturid\" value='".$facture->facturid."'>
			<div id=\"divopts_".$ligne->lignefactureid."\">";
			foreach($ligne->caracteristiquesval() as $caracs){
				//on récupère toutes les options qui sont dans la même caractéristique que les options de la commande, pour comparer les prix et savoir quel btn radio doit être coché :
				$carac=$DB_site2->query_first("SELECT prix_fixe, pvttc, paht, achat, differenceprix FROM caracteristiquevaleur cv INNER JOIN article_caractval ac ON(ac.caractvalid=cv.caractvalid) WHERE cv.caractvalid = ".$caracs);
				$carac_ligne=$DB_site2->query_first("SELECT differenceprix FROM lignefacturecaracteristique lf WHERE lf.caractvalid = ".$caracs." AND lignefactureid=".$ligne->lignefactureid);
				if($carac['prix_fixe'] == '1'){
					if($carac['pvttc'] == $carac_ligne['differenceprix']){
						$check_achat = "";
						$check_vente = "checked";
						$check_free = "";
					}
					elseif($carac['paht'] == $carac_ligne['differenceprix']){
						$check_vente = "";
						$check_achat = "checked";
						$check_free = "";
					}
					else{
						$check_vente = "";
						$check_achat = "";
						$check_free = "checked";
					}
				}
			}
			$ligne_article_selec .= "<p>Prix à appliquer sur les options : <input type='radio' name='caract_prix_type' value='achat' required $check_achat> achat &nbsp; <input type='radio' name='caract_prix_type' value='differenceprixht' $check_vente required> vente &nbsp; <input type='radio' name='caract_prix_type' value='free' $check_free required> offert</p>";
			//on affiche les select avec les options :
			foreach($libelles_carac as $libelle_carac => $libelles_val){
				$ligne_article_selec .= "<b>".$libelle_carac." :</b><select name=\"options[]\">";
				$selected = "";
				foreach($libelles_val as $libelle_val){
					if($ligne->caracteristiquesval()[$libelle_val[2]]==$libelle_val[1]){
						$selected = "selected";
					}
					else{
						$selected = "";
					}
					$ligne_article_selec .= "<option value=\"".$ligne->caracteristiquesval()[$libelle_val[2]]."_".$libelle_val[1]."\" ".$selected.">".$libelle_val[0]."
					</option>";
				}
				$ligne_article_selec .= "</select><br>";
			}
			$ligne_article_selec .= "<br><input type=\"submit\" value=\"Modifier la commande\" > </form><td></tr>";
		}
					
		//la div pour modifier les garanties :
		$ligne_article_selec .= "<tr id=\"divmodif_gar_".$ligne->lignefactureid."\" class=\"divmodif_\" style=\"display: none;height:40px; background-color:#dddddd;text-align:center;\"><td>
		<p><b>Modification de ".$ligne->artcode."</b></p>
		<form name=\"updateline_prix_".$ligne->lignefactureid."\" method=\"post\" action=\"facture.php?action=modifier&facturid=".$facture->facturid."\">
		<input type=\"hidden\" name=\"artid\" value=\"".$ligne->artid."\">
		<input type=\"hidden\" name=\"shippingorder\" value=\"".$ligne->lignefactureid."\">
		<input type=\"hidden\" name=\"update\" value=\"true\">
		<input type=\"hidden\" name=\"facturid\" value='".$facture->facturid."'>
		".$garanties_select."
		<div><input type=\"submit\" value=\"Modifier la commande\"></div> 
		</form>
		</td>
		</tr>";
		
	}

  	?>
  	<table class="table_factures">
	  	<tr>
	  		<td class="frametitle">Commande n°<?php echo $facture->facturid; ?></td>
	  		<td style="color:<?php echo $validecolors[$facture->valide]; ?>"><?php echo $facture->validetxt; ?></td>
	  	</tr>
	  	<tr>
	  		<td></td>
	  		<td><?php echo ($facture->numerofacture > 556500 ? "n° facture : ".$facture->numerofacture : ""); ?></td>
	  	</tr>
		<tr>
			<td><?php echo ($facture->provenance == "sav" ? "SAV commande ".$facture->provenanceid : ""); ?></td>
			<td>Date : <?php echo date("d/m/Y",$facture->datecommande); ?></td>
		</tr>
	  	<tr>
	  		<td><a href="facture.php?facturid=<?php echo $facture->facturid; ?>&action=editer">Traiter la commande</a></td>
	  	</tr>
	</table>

	<br>

	<table class="table_factures">
		<tr>
			<td colspan="2" class="frametitle">Informations client</td>
		</tr>
		<tr>
			<td>
				<b>Facturation</b><input type="hidden" id="paysid" value="<?php echo $utilisateur->paysid; ?>">
				<br>
				<?php echo $utilisateur->raisonsociale; ?>
				<br>
				<?php echo $utilisateur->nom; ?> <?php echo $utilisateur->prenom; ?>
				<br>
				<?php echo $utilisateur->adresse; ?>
				<br>
				<?php echo $utilisateur->codepostal; ?> <?php echo $utilisateur->ville; ?>
				<br>
				<?php echo $utilisateur->pays; ?>
				<br>
				<?php echo $utilisateur->telephone; ?>
			</td>
			<td>
				<b>Livraison</b>
				<br>
				<?php echo $facture->raisonsociale; ?>
				<br>
				<?php echo $facture->nom; ?> <?php echo $facture->prenom; ?>
				<br>
				<?php echo $facture->adresse; ?>
				<br>
				<?php echo $facture->codepostal; ?> <?php echo $facture->ville; ?>
				<br>
				<?php echo $facture->pays; ?>
				<br>
				<?php echo $facture->telephone; ?>
			</td>
		</tr>
		<tr>
			<td colspan="2">Info trspt : <?php echo $facture->infotranspo; ?></td>
		</tr>
		<tr>
			<td><a href="facture.php?action=creer&modif1=<?php echo $facture->facturid; ?>">Modifier les infos de facturation et livraison</a></td>
		</tr>
	</table>

	<br>

	<table class="table_factures">
		<tr>
			<td colspan="2" style="font-weight:bold;">Détail des produits commandés</td>
		</tr>
		<tr>
			<td colspan="2">
				<table cellspacing="0" cellpadding="0" class="table_liste_produits" style="margin-bottom: 10px;">
          			<tr>
          				<td class="entete">Référence</td>
          				<td class="entete">Réf Fournisseur</td>
          				<td class="entete">Description</td>
          				<td class="entete">Quantité</td>
          				<td class="entete">Garantie</td>
          				<td class="entete">Prix Unit. T.T.C</td>
          				<td class="entete">Total T.T.C</td>
          				<td class="entete">&nbsp;</td>
          			</tr>
          			<?php echo $ligne_article; ?>
      			</table>
  			</td>	
  		</tr>
  		
  			<?php echo $ligne_article_selec; ?>

  			<tr><td style="padding:5px;"></td></tr>

	  	<tr>
	  		<td colspan="2" height="30" style="background-color:#dddddd;">
	  			<b>Ajouter un produit :</b> 
	  			<input type="text" name="src" id="newsrc">
	  			<input type="hidden" name="destinataire_id" id="destinataire_id" value="<?php echo $utilisateur->raisonsociale != '' ? 2 : 1; ?>">
	  			<input type="hidden" id="site_code" value="<?php echo $facture->site_code; ?>">
	  			<input type="button" value="OK" onclick="srcprod()"> 
	  			<span style="color:red">&#9888; Pour les lots mettre les articles inclus</span>
	  		</td>
	  	</tr>
		<tr>
			<td colspan="2" id="mpprodresults"></td>
		</tr>
		<tr>
			<td colspan="2" id="addprod" style="height:40px; display:none; background-color:#dddddd;">
				<form name="addline" method="post" action="facture.php?action=creer&facturid=<?=$facture->facturid; ?>">

					<input type="hidden" name="facturid" value="<?php echo $facture->facturid; ?>">
					<input type="hidden" name="artid" value="<?php echo $ligne->artid; ?>">
					<input type="hidden" name="description" value="">
					<input type="hidden" name="barcode" value="">
					<input type="hidden" name="strikeout_price" size="6" value=""> 

					<input type="text" name="prod" value="" readonly> 
					&nbsp;Quantité <input type="text" name="qtt" size="4" value="1"> 
					Taux TVA : 
					<select name="tva" onchange="changeTVA(this.value)">
						<option value="">Taux article</option>
						<option value="0">0%</option>
					</select>

					<br>
					
					Prix unit. TTC <input type="text" name="price" size="6" value="" onchange="caractload()"> 
					&nbsp;<input type="radio" name="prixtype_add" onclick="document.getElementsByName('price')[0].value=document.getElementsByName('buyprice')[0].value;" onchange="caractload()"> Prix d'achat TTC <input type="text" name="buyprice" value="" size="6" readonly> 
					&nbsp;<input type="radio" name="prixtype_add" onclick="document.getElementsByName('price')[0].value=document.getElementsByName('selprice')[0].value;" onchange="caractload()" selected> Prix de vente TTC <input type="text" name="selprice" value="" size="6" readonly>

					<div id="divopts"></div>

					<input type="button" value="Ajouter à la commande" onclick="document.forms.addline.submit()"> 
					<input type="button" value="Annuler" onclick="document.getElementById('addprod').style.display='none';document.getElementById('addprod2').style.display='table-cell';">
				</form>
			</td>
		</tr>
  		
        <?php       
    
        $tvapays=$DB_site2->query_first("SELECT p.*,u.tvaintracom AS uintracom FROM pays p INNER JOIN utilisateur u ON(p.paysid=u.paysid) WHERE u.userid=".$utilisateur->userid);
        $optionstva="<option value=\"$tvapays[TVAtauxnormal]\">$tvapays[TVAtauxnormal]%</option><option value=\"$tvapays[TVAtauxreduit]\">$tvapays[TVAtauxreduit]%</option>";
        $optionstva.="<option value=\"0\">0%</option>";

        print "<tr><td colspan=\"2\" id=\"addprod2\" style=\"height:40px;background-color:#dddddd;\">
        <b>Ajouter une ligne :</b>
        <form name=\"addline2\" method=\"post\" action=\"facture.php?action=creer&facturid=".$facture->facturid."\">
        <input type=\"hidden\" name=\"facturid\" value=\"".$facture->facturid."\">
        <input type=\"hidden\" id=\"prodtype\">
        <input type=\"hidden\" name=\"artid\" value=\"0\">Type : <input type=\"radio\" name=\"prod\" value=\"compensation\" onclick=\"document.getElementById('prodtype').value=this.value;\" checked> Dédommagement &nbsp; <input type=\"radio\" name=\"prod\" value=\"port\" onclick=\"document.getElementById('prodtype').value=this.value;\"> Transport &nbsp; <input type=\"radio\" name=\"prod\" value=\"avoir\" onclick=\"document.getElementById('prodtype').value=this.value;\"> Avoir &nbsp; <input type=\"radio\" name=\"prod\" value=\"frais\" onclick=\"document.getElementById('prodtype').value=this.value;\"> Frais bancaires &nbsp; <input type=\"radio\" name=\"prod\" value=\"recup\" onclick=\"document.getElementById('prodtype').value=this.value;recup_cave();\"> Récupération de cave
        <br><span id=\"descriptionl\">Description : <input type=\"text\" name=\"description\" value=\"\" size=\"70\"></span>
        <span id=\"dimensionsl\" style=\"display:none;\"> Dimensions : <input type=\"text\" name=\"dimensions\" value=\"\" size=\"20\"></span>
        <span id=\"poidsl\" style=\"display:none;\"> Poids (kg) : <input type=\"text\" name=\"poids\" value=\"\" size=\"10\"></span>
        <span id=\"qttl\"><input type=\"hidden\" name=\"qtt\" size=\"4\" value=\"1\"></span>
        <br>Montant T.T.C : <input type=\"text\" id=\"price2\" name=\"price\" size=\"7\" value=\"\"> Taux TVA : <select name=\"tva\">$optionstva</select>
        <p><input type=\"button\" value=\"Ajouter à la commande\" onclick=\"verifaddline()\"></p>
        </form>
        </td></tr>";
        
        $code="";
        $delcpstyle="none";
        if($facture->cadeauid!="0"){
            if(is_numeric($facture->cadeauid)){
                    $cadeau=$DB_site2->query_first("SELECT * FROM cadeau WHERE cadeauid=".$facture->cadeauid);
                    $code=$cadeau['code'];
                    $delcpstyle="";
            }else{
                    $code=$facture->cadeauid;
                    $delcpstyle="";
            }
        }
          
        print "<tr>
        <td colspan=\"2\">
        <p><b>Modification commande :</b></p>
            <form method=\"post\">
                <input type=\"hidden\" name=\"cadeauid\" value=\"".$facture->cadeauid."\">
                 <input type=\"hidden\" name=\"typecadeau\" value=\"".$cadeau['typecadeauid']."\">
                Code promo : <input type=\"text\" name=\"codepromo\" value=\"".$code."\" onkeyup=\"cadeausrc(this.value)\"> 
                valeur : <input type=\"text\" size=\"5\" name=\"montantcadeau\" value=\"".$facture->cadeauttc."\">€ T.T.C
                <img src=\"images/supprimer.gif\" id=\"delcp\" title=\"Supprimer le code\" onclick=\"selectcp('',0)\" style=\"display:".$delcpstyle.";\"> 
                <input type=\"submit\" id=\"submitcp\" value=\"Enregistrer\" style=\"display:none;\">
                <div id=\"cpresults\"></div>
            </form>
        </td>
        </tr>";
        
        
        $inccode=$incinfos="";
        if(preg_match("/([A-Z]{3})/",$facture->incoterms,$m)==1){
            $inccode=$m[1];
            //Permet d'afficher le contenu de l'input text incoterm s'il existe
            $incinfos=preg_replace("/$inccode/",'',$facture->incoterms);
        }
        $optincoterms="<option value=\"\">Aucun</option>";
        $req=$DB_site2->query("SELECT * FROM traduction WHERE path LIKE '/commande/incoterms_%' ORDER BY path");
        while($inc=$DB_site2->fetch_array()){
            preg_match("/incoterms_(.+)$/",$inc['path'],$m);
            $optincoterms.="<option value=\"".$m[1]."\"".($m[1]==$inccode?" selected":"").">".$m[1]." ".$inc['FR']."</option>";
        }
        
        echo "<tr>"
                . "<td colspan=\"2\" style=\"padding-top:10px;\">"
                    . "<form action=\"facture.php?action=creer&facturid=".$facture->facturid."\" method=\"post\">"
                            . "<input type=\"hidden\" name=\"shippingorder\" value=\"".$facture->facturid."\">"
                            . "<table cellspacing=\"0\" cellpadding=\"4\" class=\"plist\">"
                                    . "<tr>"
                                            . "<td>Mode de livraison</td>"
                                            . "<td>"
                                                    . "<select name=\"modelivraison\" onchange=\"document.getElementById('txtlivr').style.display=this.value==5?'table-row':'none';\">"
                                                            . "<option value=\"0\"".($facture->modelivraison==0?" selected":"").">Eco (normal)</option>"
                                                            . "<option value=\"1\"".($facture->modelivraison==1?" selected":"").">Confort</option>"
                                                            . "<option value=\"7\"".($facture->modelivraison==7?" selected":"").">Confort & mise en service</option>"
                                                            . "<option value=\"5\"".($facture->modelivraison==5?" selected":"").">Spécialisée</option>"
                                                    . "</select>"
                                            . "</td>"
                                    . "</tr>"
                                    . "<tr id=\"txtlivr\" style=\"display:".($facture->modelivraison!=5?"none":"table-row")."\">"
                                        . "<td>Texte livraison spécialisée</td>"
                                        . "<td>"
                                            . "<textarea name=\"txtlivr\" style=\"width:100%;\">".$facture->txtlivr."</textarea>"
                                        . "</td>"
                                    . "</tr>"
                                    . "<tr>"
                                            . "<td>Incoterms</td>"
                                            . "<td><select name=\"incoterms\">".$optincoterms."</select><input type=\"text\" name=\"incoterms2\" size=\"50\" value=\"".$incinfos."\"></td>"
                                    . "</tr>"
                                    . "<tr>"
                                            . "<td>Port T.T.C</td>"
                                            . "<td>"
                                                    . "<input type=\"text\" name=\"shipprice\" id=\"shipprice\" size=\"5\" value=\"".$facture->portttc."\"> "
                                                    . "<select name=\"tvaport\">".select_option($optionstva,$facture->porttva)."</select>"
                                            . "</td>"
                                    . "</tr>";

	      				foreach($facture->totaltauxht as $tx=>$total){
					    	echo ""
					    	. "<tr>"
						    	. "<td>Total H.T. ($tx %)</td>"
						    	. "<td>".number_format($total,2,',','')."</td>"
					    	. "</tr>"
					    	. "<tr>"
						    	. "<td>T.V.A. ($tx %)</td>"
						    	. "<td>".number_format($total*($tx/100),2,',','')."</td>"
					    	. "</tr>";
                                        }
		  
                                        if($facture->valide==0) $moyenaction="onchange=\"choosemoyen(this.value);\"";
                                        $montant = number_format($facture->totalttc, 2, '.', '');
    

                                        include($rootpath."vad/macavavin/CallerService.php");

                                        $returnURL =urlencode('http://'.$host.'/DoExpressCheckoutPayment.php?currencyCodeType=EUR&paymentType=6&paymentAmount='.round($montant,2)."&order=".$facture->facturid);
                                        $cancelURL =$returnURL;
                                        $nvpstr="&AMT=".round($montant,2)."&PAYMENTACTION=sale&RETURNURL=".$returnURL."&CANCELURL=".$cancelURL."&HDRIMG=https://static.e-junkie.com/sslpic/14692.35c68aeaa0d1006a5e78e5a67045633e.jpg&CURRENCYCODE=EUR&INVNUM=".$facture->facturid."&SHIPTONAME=".urlencode("$facture->prenom $facture->nom")."&SHIPTOSTREET=".urlencode($facture->adresse)."&SHIPTOCITY=".urlencode($facture->ville)."&SHIPTOCOUNTRYCODE=".$facture->diminutif."&SHIPTOZIP=".$facture->codepostal."&ADDROVERRIDE=1&SOLUTIONTYPE=Sole";

                                        $resArray=hash_call("SetExpressCheckout",$nvpstr);
                                        $_SESSION['reshash']=$resArray;
                                        $ack = strtoupper($resArray["ACK"]);

                                        if($ack=="SUCCESS"){
                                                $token = urldecode($resArray["TOKEN"]);
                                                $lienfinal=PAYPAL_URL.$token;
                                                $pagepaiementpaypal="<a href=".$lienfinal."><img border=\"0\" width=\"91\" alt=\"Marque d'acceptation\" src=\"https://www.paypal.com/fr_FR/FR/i/logo/PayPal_mark_180x113.gif\"></a>";
                                        }

                                        $paiements = ""
                                        . "<div id=\"moyen1\" style=\"display:none;\"><a href=\"facture_pay.php?montant=$montant&facturid=$facture->facturid&moyen=1\">Effectuer le paiement</a></div>"
                                        . "<div id=\"moyen2\" style=\"display:none;\">Le paiement devra être validé</div>"
                                        . "<div id=\"moyen3\" style=\"display:none;\"></div>"
                                        . "<div id=\"moyen4\" style=\"display:none;\">Le paiement devra être validé</div>"
                                        . "<div id=\"moyen5\" style=\"display:none;\">Le paiement devra être validé</div>"
                                        . "<div id=\"moyen6\" style=\"display:none;\"><iframe>$pagepaiementpaypal</iframe></div>"
                                        . "<div id=\"moyen7\" style=\"display:none;\"></div>"
                                        . "<div id=\"moyen8\" style=\"display:none;\"><a href=\"facture_pay.php?montant=$montant&facturid=$facture->facturid&moyen=8\">Effectuer le paiement</a></div>"
                                        . "<div id=\"moyen9\" style=\"display:none;\"><a href=\"facture_pay.php?montant=$montant&facturid=$facture->facturid&moyen=9\">Effectuer le paiement</a></div>"
                                        . "<div id=\"moyen10\" style=\"display:none;\"></div>"
                                        . "<div id=\"moyen12\" style=\"display:none;\"></div>"
                                        . "<div id=\"moyen13\" style=\"display:none;\"></div>";

                                        echo ""
                                        . "<tr>"
                                                . "<td>Total T.T.C.</td>"
                                                . "<td>".number_format($facture->totalttc,2,',','')."</td>"
                                        . "</tr>"
                                        . "<tr>"
                                                . "<td>Moyen de paiement</td>"
                                                . "<td>"
                                                        . "<select name=\"moyen\" $moyenaction>";

                                                        $sql = "SELECT libelleFR, moyenid";
                                                        $sql .= " FROM moyenpaiement";
                                                        $sql .= " ORDER BY moyenid";
                                                        $r = $DB_site2->query($sql);
                                                        while($moyen = $DB_site2->fetch_array($r)){
                                                                echo "<option value=\"$moyen[moyenid]\" ".($facture->moyenid==$moyen['moyenid'] ? "selected" : "").">$moyen[libelleFR]</option>";
                                                        }

	  	echo ""
                                                        . "</select>"
                                                        . $paiements
                                                . "</td>"
                                        . "</tr>";
                                        ///on ne peut changer l'état de la commande que si elle est annulée ou refusée :
                                        if($facture->valide==2 || $facture->valide==3){
                                       echo  "<tr>"
                                        	. "<td>Etat de commande</td>"
                                        	. "<td>" 
                                        		. "<select name=\"etat\">"
                                        			 
                                        			."<option value=\"\" selected></option>
                                        			  <option value=\"0\">Non traitée</option>"
                                        		. "</select>"
                                        	. "</td>"
                                        . "</tr>";
                                        }
                                        echo""
                                . "</table>"
                                . "<input type=\"submit\" value=\"Enregistrer\">"
                        . "</form>"
                . "</td>"
        . "</tr>"
. "</table>";
}

function mkpassword(){
      $chars="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
      $maxchars=count($chars);
      for($i=0;$i<7;$i++){
	  $rdm=rand(0,$maxchars-1);
	  $chr=substr($chars,$rdm,1);
	  $result.=$chr;
      }
      return $result;
}

function select_option($opt_str,$value){
      $opt_pos=strpos($opt_str,"value=\"".$value."\"");
      if($opt_pos==-1) return $opt_str;
      $select_pos=$opt_pos+8+strlen($value);
      return substr($opt_str,0,$select_pos)." selected".substr($opt_str,$select_pos);
}

?>
