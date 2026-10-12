<?php
header('Content-Type: text/html; charset=utf-8');

require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");

// ini_set("display_errors",1);

require_once(BACK_PATH."/marketplace/article.php");
require_once(SITE_PATH."/class/stock.class.php");
require_once(SITE_PATH."/class/frais_port.php");
require_once(SITE_PATH."/class/garantie.class.php");
require_once(SITE_PATH."/class/DAOgarantie.class.php");
require_once(SITE_PATH."/class/DAOarticle.class.php");
require_once(SITE_PATH."/class/DAOtype.class.php");

//Retourne une liste de resultats au format XML suite ࡵne recherche
if(isset($_GET['txt'])){
	
	$stock = new stock($DB_site2);
	$prod  = new article($DB_site2);

	$src = $_GET['txt'];

	$tsstart = mktime(0,0,0,1,1,2010);
	$ts1an   = time()-365*86400;

	$ret       = "";
	$sqlActive = "";
	$sqlLot    = "";

	//Condition SQL qui recherche uniquement les produits actifs
    if(isset($_GET['active']) && isset($_GET['site_code'])){
    	$sqlActive = " AND (";
		$sqlActive .= "	SELECT COUNT(artid)";
		$sqlActive .= " 	FROM article_site a_s";
		$sqlActive .= " 	WHERE a_s.active = '1'";
		$sqlActive .= " 	AND site_code = '".$_GET['site_code']."'";
		$sqlActive .= " 	AND a_s.artid = article.artid";
		$sqlActive .= ") > 0";
		$sqlActive .= " AND commandable IN ('1','0')";
    } 

    //Condition SQL qui exlu les lots des r괵ltats de recherche
    if(isset($_GET['lot'])){
    	$sqlLot = " AND (";
		$sqlLot .= " 	SELECT COUNT(*)";
		$sqlLot .= " 	FROM article_lot";
		$sqlLot .= " 	WHERE lotid = article.artid";
		$sqlLot .= ") = 0";
    } 

    $sqlSearch = "(artcode LIKE '%".$src."%' OR artcode_eurocave LIKE '%".$src."%')".$sqlActive.$sqlLot;
    $list = $prod->search($sqlSearch,'');

	if(isset($_GET['pro']) && ($_GET['pro'] == 1 || $_GET['pro'] == 2)) $ret .= '<pro>'.$_GET['pro'].'</pro>';
    
    foreach($list as $rprod){
		
		$stockqte = 0;

		if($rprod->stock == 1){
			$si = $stock->in_stock($rprod->artid);
			if($si != false) $stockqte = $si->qte;
		}

		$ventes    = $rprod->ventes($tsstart);
		$ventes1an = $rprod->ventes($ts1an);

		$rprod->calcport();
		$rprod->caracteristiques();

		$article_site        = $rprod->article_site();
		$article_fournisseur = $rprod->article_fournisseur();

		$asxml = "";

		foreach($article_site as $site_code => $as){

			$af = $article_fournisseur[$site_code];

			$frais_port = new frais_port($DB_site2,array($rprod->artid=>1),$sites[$site_code]['paysid_ref'],$as['prix'],$af['paht']);
			$tauxref = $template->taux_tva($sites[$site_code]['paysid_ref']);
			$lesGaranties = DAOgarantie::loadMaxGarantieArtByPays($DB_site2,$site_code,'FR',$sites[$site_code]['paysid_ref'],$rprod->artid);

			$i        = 0;
			$garantie = 0;

			if(!empty($lesGaranties)){
				foreach($lesGaranties as $laGarantie){
					$i++;
					if ($i==1) $garantie=$laGarantie->paht;
					if ($laGarantie->cumul==1) $garantie+=$laGarantie->paht;
				}
			}

			$asxml .= ""
			. " price_".$site_code." 		 = \"".(intval($as['commandable'])==0?"Sur demande":$as['prix'])."\""
			. " strike_out_".$site_code." 	 = \"".$as['prixconseilleht']."\""
			. " active_".$site_code." 		 = \"".$as['active']."\""
			. " commandable_".$site_code."   = \"".$as['commandable']."\""
			. " tva_".$site_code." 			 = \"".$tauxref[$as['tauxchoisi']]."\""
			. " port_".$site_code." 		 = \"".round($frais_port->prixht,2)."\""
			. " achatport_".$site_code." 	 = \"".round($frais_port->paht,2)."\""
			. " achatgarantie_".$site_code." = \"".$garantie."\"";
		  
			$asxml .= ""
			. " paht_".$site_code." 		= \"".$af['paht']."\""
			. " prixtrspt_".$site_code." 	= \"".$af['trspt']."\""
			. " ecopartht_".$site_code." 	= \"".$af['ecopartht']."\""
			. " devise_achat_".$site_code." = \"".$af['devise']."\"";
		}
	      
		$ret .= "<prod"
			. " artid 		= \"".$rprod->artid."\""
			. " id 			= \"".trim($rprod->artcode)."\""
			. " ref 		= \"".htmlspecialchars($rprod->artcode_eurocave)."\""
			. " gamme 		= \"".$rprod->gamme."\""
			. " marque 		= \"".$rprod->marque."\""
			. " price 		= \"".(intval($rprod->commandable)==0?"Sur demande":$rprod->prix)."\""
			. " strike_out 	= \"".$rprod->strike_out."\""
			. " buyprice 	= \"".$rprod->prixachat."\""
			. " paht 		= \"".$rprod->paht."\""
			. " ecopartht 	= \"".$rprod->ecopartht."\""
			. " prixtrspt 	= \"".$rprod->prixtrspt."\""
			. " tva 		= \"".$rprod->tva."\"".$asxml.""
			. " caract_prix = \"".$rprod->caract_prix."\""
			. " caract_paht = \"".$rprod->caract_paht."\""
			. " port 		= \"".$rprod->fraisport."\""
			. " port_origin = \"".$rprod->fraisport_origin."\""
			. " achatport 	= \"".$rprod->achatport."\""
			. " portgratuit = \"".$rprod->portgratuit."\""
			. " barcode 	= \"\""
			. " stock 		= \"".$rprod->stock."\""
			. " stockqte 	= \"".$stockqte."\""
			. " lot 		= \"".$rprod->lot."\""
			. " visible 	= \"".$rprod->visible."\""
			. " active 		= \"".$rprod->active."\""
			. " ventes 		= \"".$ventes."\""
			. " ventes1an 	= \"".$ventes1an."\""
		. ">".str_replace("&","&amp;",$rprod->libelle)."</prod>\n";
    }
}

//Retourne les champs de d굡il d'un article au format XML
if(isset($_GET['artid'])){
    
	$prod = new article($DB_site2);

	$ret   = "";
	$artid = intval($_GET['artid']);
	$paysid = intval($_GET['paysid']);
	$price=isset($_GET['price']) ? floatval($_GET['price']) : 1;

	$list = $prod->caracteristiques($artid);
	foreach($list as $car => $listval){
		foreach($listval as $id=>$val){
			$ret.="<caract name=\"".htmlspecialchars(stripslashes($car))."\" id=\"".$id."\" value=\"".stripslashes($val)."\" />\n";
		}
	} 
     
	$lesGaranties = DAOgarantie::loadMaxGarantieArtByPays($DB_site2,"base",'FR',$paysid,$_GET['artid'],isset($_GET['destinataire']) ? $_GET['destinataire'] : '', $price);
	
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
		
		$txt = $garantie->duree." ans ".DAOtype::getTypeById($DB_site2,$garantie->type_garantie)." ".$garantie->fournisseur." ".$txt_destinataire;

		$ret .= "<garantie";
		$ret .= "	garantieid 	= \"".$garantie->garantieid."\"";
		$ret .= "	duree 		= \"".$garantie->duree."\"";
		$ret .= " 	type 		= \"".$garantie->type_garantie."\"";
		$ret .= " 	fournisseur = \"".$garantie->fournisseur."\"";
		$ret .= " 	prix 		= \"".$garantie->prix."\"";
		$ret .= " 	paht 		= \"".$garantie->paht."\"";
		$ret .= " 	utilisation = \"".$garantie->utilisation_garantie."\"";
		$ret .= " >".str_replace('&','&amp;',$txt)."</garantie>";
	}

	$lesGaranties = DAOgarantie::loadGarantiesPayArtByPays($DB_site2,"base",'FR',$paysid,$artid,isset($_GET['destinataire']) ? $_GET['destinataire'] : '', $price);

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
			
			$txt   = $plus.$garantie->duree." ans ".DAOtype::getTypeById($DB_site2,$garantie->type_garantie)." ".$garantie->fournisseur." ".$txt_destinataire;

			$ret  .= "<garantie";
			$ret  .= "	garantieid 	= \"".$garantie->garantieid."\"";
			$ret  .= "	duree 		= \"".$garantie->duree."\"";
			$ret  .= "	type 		= \"".$garantie->type_garantie."\"";
			$ret  .= "	fournisseur = \"".$garantie->fournisseur."\"";
			$ret  .= "	prix 		= \"".$garantie->prix."\"";
			$ret  .= "	paht 		= \"".$garantie->paht."\"";
			$ret  .= "	utilisation = \"".$garantie->utilisation_garantie."\"";
			$ret  .= " >".str_replace('&','&amp;',$txt)."</garantie>";
		}
        }
        
        if(isset($_GET['stock'])){
                $stock=new stock($DB_site2);
                $mini=$states=$artids=$lieux=array();
                $stockartid=$stock->similaire($artid);
                
                foreach($stock->artids() as $lieu=>$lieuartids){
                    if(isset($lieuartids[$stockartid])){
                            $mini[$lieu]=$lieuartids[$stockartid]->mini;
                            $artids[$lieu]=$lieuartids[$stockartid]->artids;
                            $lieux[]=$lieu;
                    }
                }
                
                $items=$stock->search($stockartid,null,null,array(0,1,2));
                foreach($items as $item){
                    $states[$item->lieu][$item->state]++;
                    $lieux[]=$item->lieu;
                }

                $ret.="<stock>";
                foreach(array_unique($lieux) as $lieu){
                    $ret.="<lieu id=\"".$lieu."\" mini=\"".$mini[$lieu]."\" artids=\"".$artids[$lieu]."\">";
                    if(isset($states[$lieu])){
                        foreach($states[$lieu] as $state=>$qte){
                            $ret.="<state id=\"".$state."\">".$qte."</state>";
                        }
                    }
                    $ret.="</lieu>";
                }
                $ret.="</stock>";
        }
}


if(isset($_GET['sim'])){
    $ret="";
    $prod=new article($DB_site2);
    $active="";
    $gidlist=array();
    if(isset($_GET['active'])) $active=" AND active IN ('0','1') AND commandable='1'";
    $list=$prod->search("(artcode LIKE '%$sim%' OR artcode_eurocave LIKE '%$sim%')".$active,'');
    foreach($list as $rprod){
	$group=$rprod->similaire();
	if($group==array()){
		  $ret.="<prod artid=\"".$rprod->artid."\" id=\"".$rprod->artcode."\" ref=\"".$rprod->artcode_eurocave."\" gamme=\"".$rprod->gamme."\" marque=\"".$rprod->marque."\" price=\"".$rprod->prix."\" strike_out=\"".$rprod->strike_out."\" buyprice=\"".$rprod->prixachat."\" paht=\"".$rprod->paht."\" ecopartht=\"".$rprod->ecopartht."\" tva=\"".$rprod->tva."\" barcode=\"\" stock=\"".$rprod->stock."\" lot=\"".$rprod->lot."\" visible=\"".$rprod->visible."\" active=\"".$rprod->active."\">".str_replace("&","&amp;",$rprod->libelle)."</prod>\n";
	}else{
	      foreach($group as $gid=>$artids){
		  if(!in_array($gid,$gidlist)){
		    $ret.="<group id=\"$gid\">";
		    foreach($artids as $artid){
			$a=new article($DB_site2);
			$a->load($artid);
			$ret.="<prod artid=\"".$a->artid."\" id=\"".$a->artcode."\" ref=\"".$a->artcode_eurocave."\" gamme=\"".$a->gamme."\" marque=\"".$a->marque."\">".str_replace("&","&amp;",$a->libelle)."</prod>";
		    }
		    $ret.="</group>";
		    $gidlist[]=$gid;
		  }
	      }
	}
    }
    
}

if(isset($_GET['codepromo'])){
   $ret="";
   $typecadeau=array(0=>"&#8364;",1=>"%");
   $portoffert=array(0=>"",1=>"Livraison",2=>"Installation",3=>"Livraison + installation");
   $req=$DB_site2->query("SELECT * FROM cadeau WHERE UPPER(code) LIKE '%".strtoupper($_GET['codepromo'])."%'");
   while($cadeau=$DB_site2->fetch_array($req)){
	$categorie_cadeau=$DB_site2->query_first("SELECT catid,libelleFR FROM categorie_cadeau INNER JOIN categorie USING(catid) WHERE cadeauid=".$cadeau['cadeauid']);
	$applicable="tout le site";
	if($categorie_cadeau!="") $applicable=$categorie_cadeau['libelleFR'];
	$article_cadeau=$DB_site2->query_first("SELECT artid,artcode FROM article_cadeau INNER JOIN article USING(artid) WHERE cadeauid=".$cadeau['cadeauid']);
	if($article_cadeau!="") $applicable=$article_cadeau['artcode'];
	$client="tous";
	if($cadeau['userid']>0){
	    $utilisateur=$DB_site2->query_first("SELECT nom,prenom,raisonsociale FROM utilisateur WHERE userid=".$cadeau['userid']);
	    $client=trim($utilisateur['raisonsociale']." ".$utilisateur['nom']." ".$utilisateur['prenom']);
	}
	$options="";
	if($cadeau['option']!=""){
	    $req=$DB_site2->query("SELECT libelleFR FROM caracteristiquevaleur WHERE caractvalid IN (".$cadeau['option'].")");
	    while($option=$DB_site2->fetch_array($req)) $options.=$option['libelleFR'].",";
	}
	$ret.="<codepromo cadeauid=\"".$cadeau['cadeauid']."\" code=\"".$cadeau['code']."\" valeur=\"".$cadeau['valeurcadeau'].$typecadeau[$cadeau['typecadeauid']]."\" active=\"".$cadeau['active']."\" datefin=\"".($cadeau['datefin']==0?"Illimité":date("d/m/Y",$cadeau['datefin']))."\" minimum=\"".$cadeau['minimum']."\" applicable=\"".$applicable."\" client=\"$client\" options=\"$options\" port=\"".$portoffert[$cadeau['port']]."\" />";
    }
    
    $req=$DB_site2->query("SELECT * FROM code_caviste WHERE UPPER(code) LIKE '%".strtoupper($_GET['codepromo'])."%'");
    while($code=$DB_site2->fetch_array($req)){
        $caviste=$DB_site2->query_first("SELECT raisonsociale FROM caviste WHERE cavisteid=".$code['caviste']);
        $ret.="<codecaviste code=\"".$code['code']."\" type=\"".$code['type']."\" caviste=\"".$caviste['raisonsociale']."\" pc=\"".$code['pc']."\" pcpromo=\"".$code['pcpromo']."\" mini=\"".$code['mini']."\" validite=\"".date("d/m/Y",$code['validite'])."\" />";
    }
}

if(isset($_GET['cde'])){
	$ret = "";
	$req = $DB_site2->query_first("SELECT * FROM facture WHERE mp_facturid='".trim($_GET['cde'])."'");
	if($req != ""){
		$mp = $DB_site2->query_first("SELECT market_place_nom AS nom FROM market_place WHERE market_place_site_id='".$req['provenanceid']."'");
		$ret = "<cde mp=\"".$mp['nom']."\" fac=\"".$req['facturid']."\">".$req['mp_facturid']."</cde>";
	}
}

if(isset($_GET['client'])){
	$ret = "";
	$client = mysqli_real_escape_string($DB_site2->link_id,$_GET['client']);
// 	$sql="SELECT `userid` AS `id`, CONCAT_WS(' ', `nom`, `prenom`) AS `label`, `mail`, `civilite`, `adresse`, `ville`, `codepostal` AS `cp`, `raisonsociale` AS `societe`, `telephone`, `nom`, `prenom`, `paysid` FROM `utilisateur` WHERE UPPER(CONCAT_WS(' ', `nom`, `prenom`)) LIKE UPPER('%".$client."%') OR `raisonsociale` LIKE UPPER('%".$client."%') ORDER BY `nom`, `prenom`";
        $and="";
        $sqlsrc="";
        foreach(explode(" ",$client) as $w){
            $sqlsrc.=$and."'".mysqli_real_escape_string($DB_site2->link_id,$w)."' IN (nom,prenom,raisonsociale)";
            $and=" AND ";
        }
	$sql="SELECT `userid` AS `id`, CONCAT_WS(' ', `nom`, `prenom`) AS `label`, `mail`, `civilite`, `adresse`, `ville`, `codepostal` AS `cp`, `raisonsociale` AS `societe`, `telephone`, `nom`, `prenom`, `paysid`, dateinscription FROM `utilisateur` WHERE ".$sqlsrc." ORDER BY `nom`, `prenom`";
	$req = $DB_site2->query($sql);
	while($row = $DB_site2->fetch_assoc($req)){
		$pays = $DB_site2->query_first("SELECT libelle FROM pays WHERE paysid=".$row['paysid']);
		$ret .= "<client"
			. " id			= \"".$row['id']."\""
			. " nom			= \"".htmlspecialchars($row['nom'])."\""
			. " prenom		= \"".htmlspecialchars($row['prenom'])."\""
			. " mail		= \"".$row['mail']."\""
			. " civilite 	= \"".$row['civilite']."\""
			. " cp			= \"".$row['cp']."\""
			. " adresse		= \"".htmlspecialchars($row['adresse'])."\""
			. " ville 		= \"".htmlspecialchars($row['ville'])."\""
			. " pays 		= \"".$pays['libelle']."\""
			. " tel			= \"".$row['telephone']."\""
			. " societe 	= \"".htmlspecialchars($row['societe'])."\""
			. " inscription = \"".$row['dateinscription']."\""
		. ">".$row['label']."</client>\n";
	}
}
if(isset($_GET['liersav'])){
	$ret="";
	$facturid = mysqli_real_escape_string($DB_site2->link_id,$_GET['liersav']);
	$clientID = $DB_site2->query_first("SELECT userid FROM facture WHERE facturid ='".$facturid."'");
	$clientInfos = $DB_site2->query_first("SELECT nom, prenom, CONCAT_WS(' ', nom, prenom) AS label FROM utilisateur where userid='".$clientID[0]."'");
	$ret .="<client" 
			. " id			= \"".$clientID[0]."\""
			. " nom			= \"".htmlspecialchars($clientInfos['nom'])."\""
			. " prenom		= \"".htmlspecialchars($clientInfos['prenom'])."\""
			. ">".$clientInfos['label']." </client>\n";
}

header("Cache-Control: no-cache");
header("Content-type:text/xml");

print "<?xml version=\"1.0\" encoding=\"utf-8\"?>
<xml>\n";

print $ret;

print "</xml>\n";

?>
