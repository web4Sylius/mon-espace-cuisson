<?php

ini_set("display_errors","on");
include_once("host_config_admin.php");
include_once($rootpath."class/stock.class.php");
include_once($rootpath."class/fonctions.php");
include_once($rootpath."admin/export/xlexport.php");

$DB_site=clone $DB_site2;

$stock=new stock($DB_site);
if($_GET['lieu']=="") $_GET['lieu']=null;

$csv=new xlexport("csv");
if($_GET['lieu']!=='' && ($_GET['marque']=='' || empty($_GET['marque']))) {
	$csv->name = "stock_".htmlentities($_GET['lieu']);
}
elseif($_GET['marque']!=='' && ($_GET['lieu']=='' || empty($_GET['lieu']))){
	$csv->name = "stock_".htmlentities($_GET['marque']);
}
elseif($_GET['marque']!=='' && $_GET['lieu']!==''){
	$csv->name = "stock_".htmlentities($_GET['marque'])."_".htmlentities($_GET['lieu']);
}
else{
	$csv->name = "stock_global";
}
$csv->csvsep          = ";";
$csv->striptags       = false;
$filename_source      = $rootpath."admin/csv/stock/".$csv->name.".csv";
if($_GET['lieu']!=='' && ($_GET['marque']=='' || empty($_GET['marque']))) {
	$csv->addrow(array("Quantite","Marque","Ref Acilyon","Ref","Libelle","Hauteur brut","Largeur brut","Profondeur brut", "Poids brut","Valeur HT stock", "Stock sur affaire"));
}
elseif($_GET['marque']!=='' && ($_GET['lieu']=='' || empty($_GET['lieu']))){
	$csv->addrow(array("Quantite","Lieu","Ref Acilyon","Ref","Libelle","Hauteur brut","Largeur brut","Profondeur brut", "Poids brut","Valeur HT stock", "Stock sur affaire"));
}
elseif($_GET['marque']!=='' && $_GET['lieu']!==''){
	$csv->addrow(array("Quantite","Ref Acilyon","Ref","Libelle","Hauteur brut","Largeur brut","Profondeur brut", "Poids brut","Valeur HT stock", "Stock sur affaire"));
}
else{
	$csv->addrow(array("Quantite","Lieu","Marque","Ref Acilyon","Ref","Libelle","Hauteur brut","Largeur brut","Profondeur brut", "Poids brut","Valeur HT stock", "Stock sur affaire"));
}



$r1=$stock->stockbyqte(array(0,1,2,3),$_GET['lieu']);
$r=array_merge($r1,$stock->stock0(array(0,1,2),$_GET['lieu'],true));
ksort($r);
$r2=$stock->stockbyqte(2,$_GET['lieu']);
$r0=$stock->stockbyqte(0,$_GET['lieu']);
$r3=$stock->stockbyqte(3,$_GET['lieu']);

$tableau=array();
usort($r, "tri_marque_ref");
foreach($r as $item){

	if((isset($_GET['marque']) && $_GET['marque']==$item->marque) || empty($_GET['marque'])){

               //n'affiche pas les items plus en stock, sauf s'ils ont des articles similaires en stock, et sauf s'ils peuvent être commandés
		if(!empty($item->similaire)){
			$artids_list = array();
			foreach($item->similaire as $key_sim => $val_sim){
				$artids_list[] = $key_sim;
			}
			$artid_artids = "(sm.artid='".$item->artid."' OR sm.artid IN (".implode(",",$artids_list)."))";
		}
		else{
			$artid_artids = "sm.artid='".$item->artid."'";
		}
		$req_state3=$DB_site2->query("SELECT sm.artid FROM stock_mini sm LEFT JOIN stock_item si ON sm.artid = si.artid WHERE ".$artid_artids." AND (state IN ('1','2','3','0') OR state IS NULL) AND (sm.mini+sm.alert)>0".(!is_null($_GET['lieu'])?" AND sm.lieu =".$_GET['lieu']:""));
		if($stock->is_similaire($item->artid)==false && $DB_site2->num_rows($req_state3) == 0){
                    //si aucun article correspondant dans stock_mini, on regarde s'il n'y a pas d'articles actifs dans stock_item
			if(!empty($item->similaire)){
				$artids_list = array();
				foreach($item->similaire as $key_sim => $val_sim){
					$artids_list[] = $key_sim;
				}
				$artid_artids = "(si.artid='".$item->artid."' OR si.artid IN (".implode(",",$artids_list)."))";
			}
			else{
				$artid_artids = "si.artid='".$item->artid."'";
			}
			$req_autrelieu=$DB_site2->query("SELECT si.artid FROM stock_item si WHERE ".$artid_artids.(!is_null($_GET['lieu'])?" AND si.lieu =".$_GET['lieu']:"")." AND state IN ('1','2')");
                    //si pas d'articles, on continue la boucle sans afficher la ligne
			if($DB_site2->num_rows($req_autrelieu) == 0){
				continue;
			}    
		}


                //si l'item est un produit similaire on ne l'affiche pas, sinon ça fera doublon avec l'art principal :
		if(($stock->is_similaire($item->artid)==false && empty($_GET['marque'])) || $_GET['marque']!==''){

                //on additionne les qte et les paht des articles similaires
			$paht_r0_sim=$paht_r1_sim=$qte_r0_sim=$qte_r2_sim=0;
			$sim_artids=array();
			if(!empty($item->similaire)){
				foreach($item->similaire as $key_sim => $val_sim){
					$arts_simi=$stock->stockbyartid_sim($key_sim,$_GET['lieu'],array(1,2));
					$item->qte+=$arts_simi['qte'];
					if(!empty($arts_simi)){
						$paht_r0_sim+=$r0[$key_sim]->paht;
						$paht_r1_sim+=$r1[$key_sim]->paht-$r3[$key_sim]->paht;
						$qte_r0_sim+=$r0[$key_sim]->qte;
						$qte_r2_sim+=$r2[$key_sim]->qte;

					}
					$sim_artids[]=$key_sim;
				}
			}
			$sim_artcodes="";
			$simi_principal=null;
			if($stock->is_similaire($item->artid)!==false){
				$item_similaire = $stock->is_similaire($item->artid);
				$sql_sim="SELECT si.*,sm.mini,sm.alert,sm.delai,a.artcode FROM stock_item si LEFT JOIN stock_mini sm ON (sm.artids LIKE '%,si.artid%' OR sm.artids LIKE '%si.artid,%' OR sm.artids = 'si.artid') LEFT JOIN stock_mini sm2 ON sm2.lieu = 'si.lieu' INNER JOIN article a ON a.artid = si.artid WHERE si.artid='".$stock->is_similaire($item->artid)."' AND si.state IN ('1','2')";
				if(!is_null($_GET['lieu'])) $sql_sim.=" AND si.lieu=".$_GET['lieu'];
				$r=$DB_site2->query($sql_sim);
				if($DB_site2->num_rows($r) > 0){
					while($data=$DB_site2->fetch_array($r)){
						$item->qte+=1;
						$paht_r1_sim=$r1[$data['artid']]->paht-$r3[$data['artid']]->paht;
						$paht_r0_sim=$r0[$data['artid']]->paht;
						$item->artcode=$data['artcode'];
					}
				}

				$sql_artcode_sim="SELECT artids,mini,alert,delai FROM stock_mini sm WHERE artid=".$stock->is_similaire($item->artid)." AND (artids LIKE '%,".$item->artid."%' OR artids LIKE '%".$item->artid.",%' OR artids = '".$item->artid."')".(!is_null($_GET['lieu'])?' AND lieu ='.$_GET['lieu']:'');
				$r=$DB_site2->query($sql_artcode_sim);
				if($DB_site2->num_rows($r)>0){
					while($a=$DB_site2->fetch_array($r)){
						if($a['artids']!==''){
							$spartid=explode(",",$a['artids']);
						}
						$item->mini=$a['mini'];
						$item->delai=$a['delai'];
						$item->alert=$a['alert'];
					}
					$r=$DB_site2->query("SELECT artid,artcode FROM article WHERE artid IN ('".implode("','",$spartid)."')");
						while($a=$DB_site2->fetch_array($r)) $sim_artcodes.='<br>'.$a['artcode']; 
						$simi_principal = $stock->is_similaire($item->artid);
					}
				}



                    $lieux=$stock->lieux();

                    $qte=$item->qte-($r0[$item->artid]->qte+$qte_r0_sim)-$r3[$item->artid]->qte;
                    ($qte<0?$qte=0:'');
                    $stock_aff=0;
                    if($qtevirtuel==0&&($r2[$item->artid]->qte+$qte_r2_sim)==0&&is_null($item->alert)&&(($item->qte-($r0[$item->artid]->qte+$qte_r0_sim)-$r3[$item->artid]->qte)<0?0:($item->qte-($r0[$item->artid]->qte+$qte_r0_sim)-$r3[$item->artid]->qte))==0){
                    //si stock sur affaire et aucune commande en cours, on n'affiche pas la ligne
                    	continue;
                    }
                    else{

                    	//pour éviter les doublons :
                    	if(in_array($item->artcode,$tableau)){
                    		continue;
                    	}
                    	else{
                    	$tableau[] = $item->artcode;
                    	
                    	

                    	if($_GET['lieu']!=='' && ($_GET['marque']=='' || empty($_GET['marque']))) {
                    		$csv->addrow(array($qte,$item->marque,$item->artcode,$item->ref,utf8_decode($item->libelle),$item->hauteur_brut,$item->largeur_brut,$item->profondeur_brut,$item->poids_brut,number_format(($r1[$item->artid]->paht+$paht_r1_sim)-($r0[$item->artid]->paht+$paht_r0_sim)-$r3[$item->artid]->paht,2,',',''),$stock_aff));
                    	}
                    	elseif($_GET['marque']!=='' && ($_GET['lieu']=='' || empty($_GET['lieu']))){
                    		$csv->addrow(array($qte,strstr($lieux[$item->lieu]," ",true),$item->artcode,$item->ref,utf8_decode($item->libelle),$item->hauteur_brut,$item->largeur_brut,$item->profondeur_brut,$item->poids_brut,number_format(($r1[$item->artid]->paht+$paht_r1_sim)-($r0[$item->artid]->paht+$paht_r0_sim)-$r3[$item->artid]->paht,2,',',''),$stock_aff));
                    	}
                    	elseif($_GET['marque']!=='' && $_GET['lieu']!==''){
                    		$csv->addrow(array($qte,$item->artcode,$item->ref,utf8_decode($item->libelle),$item->hauteur_brut,$item->largeur_brut,$item->profondeur_brut,$item->poids_brut,number_format(($r1[$item->artid]->paht+$paht_r1_sim)-($r0[$item->artid]->paht+$paht_r0_sim)-$r3[$item->artid]->paht,2,',',''),$stock_aff));
                    	}
                    	else{
                    		$csv->addrow(array($qte,$item->lieu,$item->marque,$item->artcode,$item->ref,utf8_decode($item->libelle),$item->hauteur_brut,$item->largeur_brut,$item->profondeur_brut,$item->poids_brut,number_format(($r1[$item->artid]->paht+$paht_r1_sim)-($r0[$item->artid]->paht+$paht_r0_sim)-$r3[$item->artid]->paht,2,',',''),$stock_aff));
                    	}
                    }
                }
                }
            }
        }
		function tri_marque_ref($a, $b){ // tri par marque puis par ref fabricant
			if($a->marque == $b->marque) {
				return strcasecmp($a->ref, $b->ref);
			}
			else {
				return strcasecmp($a->marque, $b->marque);
			}  
		}


		@unlink($rootpath."admin/csv/stock/".$csv->name.".csv");

		$f=fopen($filename_source,"w+");
		fputs($f,$csv->buffer);
		fclose($f);
		$file = $rootpath."admin/csv/stock/".$csv->name.".csv"; 
		header('Content-Description: File Transfer'); 
		header('Content-Type: application/force-download'); 
		header('Content-Disposition: attachment; filename=' . basename($file)); 
		readfile($file); 
		exit;

	?>