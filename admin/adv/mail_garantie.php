<?php

function mail_garantie($facturid,$lignefacturegarantieinfos){
    global $DB_site2;
    global $user_info;
    
    $insertlf=array();
    $fournisseur=array();
    
    foreach($lignefacturegarantieinfos as $lignefactureid=>$infos){
	
		$lignefactureid=intval($lignefactureid);
		$oldgarantieid=intval($infos['oldgarantieid']);
		$newgarantieid=intval($infos['newgarantieid']);
		
		$lignefacture=$DB_site2->query_first("SELECT lignefacture.*, article.prixht FROM lignefacture INNER JOIN article ON article.artid = lignefacture.artid WHERE lignefactureid=".$lignefactureid);
		if($newgarantieid>0){
		    $newgarantie[$lignefactureid]=new garantie($DB_site2,null,"FR",$newgarantieid,$lignefacture['artid'],$lignefacture['prixht']);
		    $fournisseur[]=$newgarantie[$lignefactureid]->fournisseur;
		    $devise=$newgarantie[$lignefactureid]->deviseFournisseur();
		}
		
		if($oldgarantieid!=$newgarantieid){
		    if($oldgarantieid>0){
			$oldinfo=DAOlignefacturegarantie::loadLigneFactureGarantieByLigneFacEtGarantie($DB_site2,$lignefactureid,$oldgarantieid);
			$DB_site2->query("DELETE FROM lignefacturegarantie WHERE lignefactureid=".$lignefactureid." AND garantieid=".$oldgarantieid);
		    }
		    if($newgarantieid>0){
			$codefournisseur=DAOfournisseur::loadCodeFournisseurByName($DB_site2,$newgarantie[$lignefactureid]->fournisseur);
			$result=DAOlignefacturegarantie::insert($DB_site2,null,$newgarantieid,null,$lignefactureid,"",$newgarantie[$lignefactureid]->duree,$newgarantie[$lignefactureid]->fournisseur,$codefournisseur,$oldinfo->pays,$newgarantie[$lignefactureid]->duree,$newgarantie[$lignefactureid]->type_garantie,0,0,$newgarantie[$lignefactureid]->paht);
			if($result==false) return false;
			
		    }
		}
		
		if($newgarantieid>0)
		    $insertlf[$lignefactureid]=$lignefacture['qte'];

	}

    $fournisseur=array_unique($fournisseur);

    if(count($fournisseur)==1){
		
		$adv = new adv($DB_site2);
		
		$adv->facturid     = $facturid;
		$adv->type         = "garantie";
		$adv->destinataire = $fournisseur[0];
		$adv->devise       = $devise;
		$adv->create();

		foreach($insertlf as $lfid=>$qte){
		    $adv->add_lignefacture($qte,$lfid);
		    $adv->save_field("paht_".$lfid,$newgarantie[$lfid]->paht);
		    $adv->save_field("garantieid_".$lfid,$newgarantie[$lfid]->garantieid);

		    if($adv->destinataire != 'SFG'){
		    	$efl  = new envoi_fournisseur_ligne($DB_site2);
				$efl->type           = "garantie";
				$efl->qte            = $lignefacture['qte'];
				$efl->paht           = $adv->fields["paht_".$lignefactureid];
				$efl->lignefactureid = $lignefactureid;

			    $listefl[]  = $efl;
			    $totalpaht += $adv->fields["paht_".$lignefactureid];
		    }
	    }
		    
	    if($adv->destinataire != 'SFG'){
		    $ef = new envoi_fournisseur($DB_site2);
			$ef->facturid     = $adv->facturid;
			$ef->destinataire = $adv->destinataire;
			$ef->tarif        = $totalpaht;
			$ef->type         = 'garantie';

			$ef->create();
		    $ef->create_lines($listefl);
		    $adv->efid=$ef->id;
		    $adv->update();
		}
	}
}


?>