<?php

ini_set("display_errors","on");
include_once("host_config_admin.php");
include_once($rootpath."class/email.php");
include_once($rootpath."class/stock.class.php");
include_once($rootpath."class/fonctions.php");
include_once($rootpath."admin/export/xlexport.php");

$DB_site=clone $DB_site2;

$stock=new stock($DB_site);
$h=date("h");
$lieux=$stock->lieux();
foreach($lieux as $id=>$lieu){

	$txt="Bonjour,<br/>
	Veuillez trouver l'etat du stock de la plateforme : ".$lieu." au  ".date('d/m/Y',mktime(0,0,0,date('m'),date('j')-1,date('Y')))."<br/>
	Si vous voyez une anomalie dans cet état , merci de nous en faire part sous 48/72h.<br/>
	Cordialement<br/>
	L'equipe de Ma Cave à Vin";

	$csv=new xlexport("csv");
	$csv->name            = "stock_plateforme_".htmlentities($lieu)."_".date('Y-m-d',mktime(0,0,0,date('m'),date('j')-1,date('Y')));
	$csv->csvsep          = ";";
	$csv->striptags       = false;
	$filename_source      = $rootpath."admin/export/suivi_stock/".$csv->name.".csv";
	$csv->addrow(array("Quantite","Marque","Ref","Libelle","Hauteur brut","Largeur brut","Profondeur brut", "Poids brut", "Stock sur affaire"));


	$list=array();
	$r1=$stock->stockbyqte(array(0,1,2,3),$id);
	$r=array_merge($r1,$stock->stock0(array(0,1,2),$id,true));
	ksort($r);
	$r2=$stock->stockbyqte(2,$id);
	$r0=$stock->stockbyqte(0,$id);
	$r3=$stock->stockbyqte(3,$id);


	usort($r, "tri_marque_ref");
	foreach($r as $item){
			$req_state3=$DB_site2->query("SELECT state,artid FROM stock_item WHERE artid =".$item->artid." AND state IN ('1','2','0')");
			if($stock->is_similaire($item->artid)==false && empty($item->similaire) && $DB_site2->num_rows($req_state3) == 0){
				continue;
			}

			if($stock->is_similaire($item->artid)==false){
				if(!is_null($id)){
					$qte = 0;
					$paht = 0;
					$paht_r0_sim=$paht_r1_sim=$qte_r0_sim=$qte_r2_sim=0;
					$sim_artids=array();
					if(!empty($item->similaire)){

						foreach($item->similaire as $key_sim => $val_sim){
							$arts_simi=$stock->stockbyartid_sim($key_sim,$id,array(1,2));
							$item->qte+=$arts_simi['qte'];
							if(!empty($arts_simi)){
								$paht_r0_sim+=$r0[$key_sim]->paht;
								$paht_r1_sim+=$r1[$key_sim]->paht-$r3[$key_sim]->paht;
								$qte_r0_sim+=$r0[$key_sim]->qte;
								$qte_r2_sim+=$r2[$key_sim]->qte;
							}
						}
					}

					$simi_principal=null;
					if($stock->is_similaire($item->artid)!==false){
						$item_similaire = $stock->is_similaire($item->artid);
						$sql_sim="SELECT si.*,sm.mini,sm.alert,sm.delai,a.artcode FROM stock_item si LEFT JOIN stock_mini sm ON (sm.artids LIKE '%,si.artid%' OR sm.artids LIKE '%si.artid,%' OR sm.artids = 'si.artid') LEFT JOIN stock_mini sm2 ON sm2.lieu = 'si.lieu' INNER JOIN article a ON a.artid = si.artid WHERE si.artid='".$stock->is_similaire($item->artid)."' AND si.state IN ('1','2')";
						if(!is_null($id)) $sql_sim.=" AND si.lieu=".$id;
						$r=$DB_site2->query($sql_sim);
						if($DB_site2->num_rows($r) > 0){
							while($data=$DB_site2->fetch_array($r)){
								$item->qte+=1;
								$paht_r1_sim=$r1[$data['artid']]->paht-$r3[$data['artid']]->paht;
								$paht_r0_sim=$r0[$data['artid']]->paht;
							}
						}
					}

					$qte=$item->qte-($r0[$item->artid]->qte+$qte_r0_sim)-$r3[$item->artid]->qte;
					($qte<0?$qte=0:'');
					$csv->addrow(array($qte,$item->marque,$item->ref,utf8_decode($item->libelle),$item->hauteur_brut,$item->largeur_brut,$item->profondeur_brut,$item->poids_brut,$stock_aff));
				}
			}
	}

     //Suppression de la sauvegarde de l'année précédente
	@unlink($rootpath."admin/export/suivi_stock/stock_plateforme_".htmlentities($lieu)."_".date('Y-m-d',mktime(0,0,0,date('m'),date('j')-1,date('Y')-1)).'.csv');

	$f=fopen($filename_source,"w");
	fputs($f,$csv->buffer);
	fclose($f);

	// Liste des contacts tâche 6718
	$destinataire_to = array('adv@ma-cave-a-vin.fr','pr@ma-cave-a-vin.fr','webmaster4@ma-cave-a-vin.fr');
	if($id == '217' || $id == '631432' || $id == '632589' || $id == '632979') { /// Acilyon, Degustation selection, Gusmerini et Phase
		$destinataire = 'contact@ma-cave-a-vin.fr';
	} else if($id == '632306') { /// Frio
		$destinataire = 'administration@climadiff.com';
		$destinataire_to = array('achat-adv@climadiff.com','adv@ma-cave-a-vin.fr','pr@ma-cave-a-vin.fr','webmaster4@ma-cave-a-vin.fr');
	} else if($id == '631658' || $id == '631641') { /// Amazon Lille et Amazon Saran
		$destinataire = 'marketing1@ma-cave-a-vin.fr';
	} else if($id == '630900') { /// Ginko
		$destinataire = 'christophemaitre@ginkologistic.com';
		$destinataire_to = array('johndrean@ginkologistic.com','adv@ma-cave-a-vin.fr','pr@ma-cave-a-vin.fr','webmaster4@ma-cave-a-vin.fr');
	}
	else {
		$destinataire_to = '';
		$destinataire = '';
	}

	$mail=new email();
	$mail->expediteur="contact@ma-cave-a-vin.fr";
	$mail->expname="Ma Cave à Vin";
	$mail->destinataire=$destinataire;
	$mail->to= $destinataire_to;
	$mail->subject="Valeur stock Ma Cave à Vin";
	$mail->body=$txt;
	$mail->attachfile($filename_source,"stock_".htmlentities($lieu)."_".date('Y-m-d',mktime(0,0,0,date('m'),date('j')-1,date('Y'))).".csv","text/csv");

	$mail->sendmail();

}
////rajouter un if(artids vide, on laisse les art)

	function tri_marque_ref($a, $b){ // tri par marque puis par ref fabricant
		if($a->marque == $b->marque) {
			return strcasecmp($a->ref, $b->ref);
		}
		else {
			return strcasecmp($a->marque, $b->marque);
		}  
	}

	?>
