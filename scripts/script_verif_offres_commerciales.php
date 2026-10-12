<?php
/* 
Ce script vérifie que nous avons bien acheté nos produits au montant établis par les offres fournisseurs 
*/
header('Content-Type: text/html; charset=utf-8');

require_once($_SERVER['DOCUMENT_ROOT'].'/global.php');

if(!empty($_POST)){
	
	$aOffre 		= array();
	$aFacturation 	= array();
	$aMemoOffre		= array();
	$nb_erreur 		= 0;
	$val_erreur 	= 0;
	$annee_verif 	= $_POST['annee_offres'];

	switch($_POST['cmd_offres']){

		//COMMANDES CLASSIQUES
		case 1:

			//Récupération de la liste des offres fournisseurs octroyées durant l'année sélectionnée
			$sql = "SELECT *";
			$sql .= " FROM fournisseur_offre fo";
			$sql .= " INNER JOIN fournisseur_offre_article foa ON (foa.offreid = fo.id)";
			$sql .= " WHERE YEAR(FROM_UNIXTIME(fo.du)) = ".$annee_verif;
			$sql .= " OR YEAR(FROM_UNIXTIME(fo.au)) = ".$annee_verif; 
			$req = $DB_site->query($sql);
			if($DB_site->num_rows($req) > 0){
				while($row = $DB_site->fetch_array($req)){
					$aOffre[] = $row;
				}
			} else {
				echo "<p style=\"color: red;\">Aucune offre commerciale durant cette année là.</p>";
			}

			if(count($aOffre) > 0){

				//Pour chaque offre, récupération des commandes passées au fournisseur dans la tranche de date
				$cpt = 0;
				foreach($aOffre as $l_offre){

					$sql = "SELECT efl.paht, ff.numero, efl.qte, ef.facturid, lf.artcode, lf.artid, ef.date as date_cmd_fourn, DATE_FORMAT(FROM_UNIXTIME(f.heurefacture), '%d/%m/%Y') as date_cmd";
					$sql .= " FROM envoi_fournisseur ef";
					$sql .= " INNER JOIN envoi_fournisseur_ligne efl ON (efl.efid = ef.id)";
					$sql .= " INNER JOIN lignefacture lf ON (lf.lignefactureid = efl.ligne_facture_id)";
					$sql .= " INNER JOIN facture f ON (f.facturid = lf.factureid)";
					$sql .= " INNER JOIN facture_fournisseur ff ON (ff.numero = efl.facture)";
					$sql .= " WHERE ef.type = 'fourn'";
					$sql .= " AND ef.date >= ".$l_offre['du'];
					$sql .= " AND ef.date <= ".$l_offre['au'];
					$sql .= " AND YEAR(FROM_UNIXTIME(ef.date)) = ".$annee_verif;
					$sql .= " AND ef.destinataire = '".mysql_real_escape_string($l_offre['fournisseur'])."'";
					$sql .= " AND lf.artid = ".$l_offre['artid'];
					$sql .= " AND f.valide IN (1,5,7,8)"; 
					$sql .= " AND efl.paht > 0";
					$sql .= " ORDER BY f.heurefacture";
					$req = $DB_site->query($sql);
					
					if($DB_site->num_rows($req) > 0){
						
						while($row = $DB_site->fetch_array($req)){
							
							$aFacturation[$cpt]['offre_id']               = $l_offre['offreid'];
							$aFacturation[$cpt]['offre_debut']            = $l_offre['du'];
							$aFacturation[$cpt]['offre_fin']              = $l_offre['au'];
							$aFacturation[$cpt]['offre_fournisseur']      = $l_offre['fournisseur'];
							$aFacturation[$cpt]['offre_type']             = $l_offre['offre'];
							$aFacturation[$cpt]['offre_valeur']           = $l_offre['value'];
							$aFacturation[$cpt]['article_cmd']            = $row['facturid'];
							$aFacturation[$cpt]['article_cmd_date']       = $row['date_cmd'];
							$aFacturation[$cpt]['article_cmd_date_fourn'] = $row['date_cmd_fourn'];
							$aFacturation[$cpt]['article_paht']           = $row['paht'];
							$aFacturation[$cpt]['article_qte']            = $row['qte'];
							$aFacturation[$cpt]['article_id']             = $row['artid'];
							$aFacturation[$cpt]['article_ref']            = $row['artcode'];
							$aFacturation[$cpt]['facture_num']            = $row['numero'];
							
							$cpt++;
						}
					}
				}

				if(count($aFacturation) > 0){
					
					foreach($aFacturation as $ligne){
						
						if(!in_array($ligne['offre_id'],$aMemoOffre)){
							$aMemoOffre[] = $ligne['offre_id'];
							$affiche_ligne = false;
						}
						
						//Récupération du prix d'achat HT chez le fabricant (PROBLEME : si ce montant a changé entretemps, la comparaison est fausse...)
						$sql = "SELECT prix";
						$sql .= " FROM article_fournisseur";
						$sql .= " WHERE fournisseur = '".mysql_real_escape_string($ligne['offre_fournisseur'])."'";
						$sql .= " AND principal LIKE '%base%'";
						$sql .= " AND artid = ".$ligne['article_id'];
						$paht_pc = $DB_site->query_first($sql)[0];
						
						$sql="SELECT value FROM article_history WHERE `key` LIKE 'paht' AND artid=".$ligne['article_id']." AND date<".$ligne['article_cmd_date_fourn']." ORDER BY date DESC";
						$ah=$DB_site->query_first($sql);
						if(preg_match("/base.+=>[^\d]+(\d+)/",$ah['value'],$m)){
                                                    $paht_pc=floatval($m[1]);
						}else{
                                                    $paht_pc=floatval($ah['value']);
						}
						

						
						$color = '';
						if($ligne['offre_type'] == 'temp' && $ligne['offre_valeur'] != $ligne['article_paht']){
							$nb_erreur++;
							$paht_val = $ligne['offre_valeur'];
							$val_erreur += $ligne['article_paht'] - $ligne['offre_valeur'];
							$color = "style=\"font-weight:bold; font-size: 18px; color: red;\"";
						}
						if($ligne['offre_type'] == 'pc'){

							//On regarde s'il y avait un prix temporaire d'achat à ce moment là
							//Sinon on utilise le prix actuel... ce qui peut ne pas correspondre à celui qui existait au moment de la commande
							$sql = "SELECT paht";
							$sql .= " FROM temp_price";
							$sql .= " WHERE artid = ".$ligne['article_id'];
							$sql .= " AND site_code = 'base'";
							$sql .= " AND du <= ".$ligne['article_cmd_date_fourn'];
							$sql .= " AND au >= ".$ligne['article_cmd_date_fourn'];
// 							$aPaHT = $DB_site->query_first($sql);
                                                        
							if(!$aPaHT){
								$paht_val = round($paht_pc - ($paht_pc * ($ligne['offre_valeur'] / 100)),2);
							} else {
								$paht_val = round($aPaHT['paht'] - ($aPaHT['paht'] * ($ligne['offre_valeur'] / 100)),2);
                                                        }
							
							if(round($paht_val,2) < round($ligne['article_paht'],2)){
								$nb_erreur++;
								$val_erreur += $ligne['article_paht'] - $paht_val;
								$color = "style=\"font-weight:bold; font-size: 18px; color: red;\"";
							}
						}
						
						if($color != ''){
							
							if(!$affiche_ligne){
								$tableau[] = ""
								. "<tr>"
									. "<td colspan=\"7\" style=\"font-size: 13px; background-color: #1563A3; color: white; padding: 5px; font-weight: bold;\">Offre n°".$ligne['offre_id']." de ".$ligne['offre_fournisseur']." valable du ".(date('d/m/Y', $ligne['offre_debut']))." au ".(date('d/m/Y', $ligne['offre_fin']))."</td>"
								. "</tr>";
								
								$affiche_ligne = true;
							}
							
							$tableau[] = ""
							. "<tr>"
								. "<td style=\"background-color: %BGCOLOR%; color: black; padding: 2px 5px;\"><a href=\"".BACK_URL."/facture.php?action=editer&facturid=".$ligne['article_cmd']."&lst_trietat=\" target=\"_blank\">".$ligne['article_cmd']."</a></td>"
								. "<td style=\"background-color: %BGCOLOR%; color: black; padding: 2px 5px;\">".$ligne['article_cmd_date']."</td>"
								. "<td style=\"background-color: %BGCOLOR%; color: black; padding: 2px 5px;\"><a href=\"".BACK_URL."/ajout_produit.php?artid=".$ligne['article_id']."&prec=modif\" target=\"_blank\">".$ligne['article_ref']."</a></td>"
								. "<td style=\"background-color: %BGCOLOR%; color: black; padding: 2px 5px;\">".($ligne['offre_type'] == 'pc' ? 'Remise '.$ligne['offre_valeur'].'%' : 'Remise montant')."</td>"
								. "<td style=\"font-size: 13px; font-weight: bold; background-color: %BGCOLOR%; color: black; padding: 2px 5px;\">".number_format($paht_val,2,',',' ')." &euro;</td>"
								. "<td style=\"font-size: 13px; font-weight: bold; background-color: %BGCOLOR%; color: black; padding: 2px 5px;\">".number_format($ligne['article_paht'],2,',',' ')." &euro;</td>"
								. "<td style=\"background-color: %BGCOLOR%; color: black; padding: 2px 5px;\"><a href=\"".BACK_URL."/fournisseur.php?action=editer&facturid=".$ligne['facture_num']."\" target=\"_blank\">".$ligne['facture_num']."</a></td>"
							. "</tr>";
						}
					}
					
				} else {
					echo "<p style=\"color: red;\">Aucune commande fournisseur trouvée.</p>";
				}
			}

			break;

		case 2:

			//Récupération des commandes de stock de l'année demandée
			//Récupération de tous les id produit présents dans la commande, séparés par des |
			$sql_articles = "SELECT GROUP_CONCAT(DISTINCT(si.artid) SEPARATOR '|')";
			$sql_articles .= " FROM stock_item si";
			$sql_articles .= " WHERE si.stock_cmd_id = sc.id";
			$sql_articles .= " AND si.state IN (1,2,3)";

			$sql_factures = "SELECT GROUP_CONCAT(DISTINCT(si.facture) SEPARATOR '|')";
			$sql_factures .= " FROM stock_item si";
			$sql_factures .= " WHERE si.stock_cmd_id = sc.id";
			$sql_factures .= " AND si.state IN (1,2,3)";

			$sql = "SELECT sc.id, sc.date, sc.destinataire, DATE_FORMAT(FROM_UNIXTIME(sc.date), '%d/%m/%Y') as cmd_date, (".$sql_articles.") as cmd_articles, (".$sql_factures.") as cmd_factures";
			$sql .= " FROM stock_cmd sc";
			$sql .= " WHERE YEAR(FROM_UNIXTIME(sc.date)) = ".$annee_verif;
			$sql .= " ORDER BY sc.destinataire";
			$req = $DB_site->query($sql);

			$nb_erreur = 0;
			$val_erreur = 0;
			$aRemDest = array();
			while($la_commande = $DB_site->fetch_array($req)){

				$cmd_paht        = 0;
				$cmd_refs        = '';
				$cmd_type_offres = '';
				$has_offre       = false;
				$aMemoFacture 	 = array();
				$aFacturesFourn  = array();
				$facture_montant = 0;

				$aFactures = explode('|',$la_commande['cmd_factures']);

				//Récupération des différents articles
				$aProduits = explode('|',$la_commande['cmd_articles']);

				//Pour chaque produit, on recherche une offre potentielle lors du passage de la commande
				foreach($aProduits as $article_id){

					if($article_id != ''){

						$sql = "SELECT fo.id, foa.offre, foa.value, DATE_FORMAT(FROM_UNIXTIME(fo.du), '%d/%m/%Y') as debut_offre, DATE_FORMAT(FROM_UNIXTIME(fo.au), '%d/%m/%Y') as fin_offre";
						$sql .= " FROM fournisseur_offre fo";
						$sql .= " INNER JOIN fournisseur_offre_article foa ON (foa.offreid = fo.id)";
						$sql .= " WHERE fo.fournisseur = '".mysql_real_escape_string($la_commande['destinataire'])."'";
						$sql .= " AND ".$la_commande['date']." >= fo.du";
						$sql .= " AND ".$la_commande['date']." <= fo.au";
						$sql .= " AND foa.artid = ".$article_id; 
						$aOffre = $DB_site->query_first($sql);

						$sql = "SELECT si.paht, af.prix, a.ecopartht, a.artid, a.artcode, ff.numero";
						$sql .= " FROM stock_item si";
						$sql .= " INNER JOIN article a ON (a.artid = si.artid)";
						$sql .= " INNER JOIN article_fournisseur af ON (af.artid = a.artid)";
						$sql .= " INNER JOIN facture_fournisseur ff ON (ff.numero = si.facture)";
						$sql .= " WHERE si.stock_cmd_id = ".$la_commande['id'];
						$sql .= " AND si.artid = ".$article_id;
						$sql .= " AND af.principal LIKE '%base%'";
						$sql .= " AND af.fournisseur = '".mysql_real_escape_string($la_commande['destinataire'])."'"; 
						$req_articles = $DB_site->query($sql);

						$article_paht=$paht_unitaire=0;
                                                $article_qte=0;
						while($l_article = $DB_site->fetch_array($req_articles)){

							if(!in_array($l_article['numero'],$aMemoFacture)){
								$aFacturesFourn[] = "<a href=\"".BACK_URL."/fournisseur.php?action=editer&facturid=".$l_article['numero']."\" target=\"_blank\">".$l_article['numero']."</a>";
								$aMemoFacture[] = $l_article['numero'];
							}

							$facture_montant += $l_article['paht'] + $l_article['ecopartht'];

							//Aucune offre, on récupère le PA HT de base
							if(!$aOffre){

								$article_ref = '';
								$article_paht += round($l_article['paht'] + $l_article['ecopartht'], 2);

							} else {
								
								$has_offre = true;
								$article_ref = "<a href=\"".BACK_URL."/ajout_produit.php?artid=".$l_article['artid']."&prec=modif\" target=\"_blank\">".$l_article['artcode']."</a>";
								
								$sql="SELECT value FROM article_history WHERE `key` LIKE 'paht' AND artid=".$article_id." AND date<".$la_commande['date']." ORDER BY date DESC";
                                                                $ah=$DB_site->query_first($sql);
                                                                if(preg_match("/base.+=>[^\d]+(\d+)/",$ah['value'],$m)){
                                                                    $paht_pc=floatval($m[1]);
                                                                }else{
                                                                    $paht_pc=floatval($ah['value']);
                                                                }
								
								switch($aOffre['offre']){

									case 'temp':
										
										$paht_unitaire = $aOffre['value'] + $l_article['ecopartht'];

										break;

									case 'pc': 

										//On regarde s'il y avait un prix temporaire d'achat à ce moment là
										//Sinon on utilise le prix actuel... ce qui peut ne pas correspondre à celui qui existait au moment de la commande
										/*$sql = "SELECT paht";
										$sql .= " FROM temp_price";
										$sql .= " WHERE artid = ".$article_id;
										$sql .= " AND site_code = 'base'";
										$sql .= " AND du <= ".$la_commande['date'];
										$sql .= " AND au >= ".$la_commande['date'];
 										$aPaHT = $DB_site->query_first($sql);


										if(!$aPaHT){
											$paht_unitaire = $l_article['prix'] - ($l_article['prix'] * ($aOffre['value'] / 100)) + $l_article['ecopartht'];
											$article_paht += $paht_unitaire;
										} else {
											$paht_unitaire = $aPaHT['paht'] + $l_article['ecopartht'];
											$article_paht += $paht_unitaire;
										}*/
										
										$paht_unitaire=$paht_pc - ($paht_pc * ($aOffre['value'] / 100)) + $l_article['ecopartht'];

										break;
										
                                                                        case 'reduc':
                                                                                $paht_unitaire=$paht_pc-$aOffre['value'];
                                                                        break;
								}
								$article_paht += $paht_unitaire;
                                                        }
                                                        $article_qte++;
						}

						$cmd_paht        += $article_paht;
						if($article_ref != '') $cmd_refs .= ($cmd_refs != '' ? '<br>' : '') . $article_qte."x " . $article_ref . ' (PU : '.number_format($paht_unitaire,2,',',' ').' €)';
						if($article_ref != '') $cmd_type_offres .= ($cmd_type_offres != '' ? '<br>' : '') . ($aOffre['offre'] == 'pc' ? 'Remise ' . $aOffre['value'] . '%' : 'Remise montant') . ' (Offre n°'.$aOffre['id'].' du '.$aOffre['debut_offre'].' au '.$aOffre['fin_offre'].')';

					}
				}

				if($has_offre){

					$cmd_paht        = round($cmd_paht,2);
					$facture_montant = round($facture_montant,2);
					if($cmd_paht < $facture_montant){

						$nb_erreur++;
						$val_erreur += $facture_montant - $cmd_paht;
						
						if(!in_array($la_commande['destinataire'],$aRemDest)){
							$aRemDest[] = $la_commande['destinataire'];
							$tableau[] = ""
							. "<tr>"
								. "<td colspan=\"7\" style=\"font-size: 13px; background-color: #1563A3; color: white; padding: 5px; font-weight: bold;\">".strtoupper($la_commande['destinataire'])."</td>"
							. "</tr>";
						}
						
						$tableau[] = ""
						. "<tr>"
							. "<td style=\"background-color: %BGCOLOR%; color: black; padding: 2px 5px;\"><a href=\"".BACK_URL."/stock.php?action=cmd&stock_cmd_id=".$la_commande['id']."\" target=\"_blank\">".$la_commande['id']."</a></td>"
							. "<td style=\"background-color: %BGCOLOR%; color: black; padding: 2px 5px;\">".$la_commande['cmd_date']."</td>"
							. "<td style=\"background-color: %BGCOLOR%; color: black; padding: 2px 5px;\">".$cmd_refs."</td>"
							. "<td style=\"background-color: %BGCOLOR%; color: black; padding: 2px 5px;\">".$cmd_type_offres."</td>"
							. "<td style=\"font-size: 13px; font-weight: bold; background-color: %BGCOLOR%; color: black; padding: 2px 5px;\">".number_format($cmd_paht,2,',',' ')." &euro;</td>"
							. "<td style=\"font-size: 13px; font-weight: bold; background-color: %BGCOLOR%; color: black; padding: 2px 5px;\">".number_format($facture_montant,2,',',' ')." &euro;</td>"
							. "<td style=\"background-color: %BGCOLOR%; color: black; padding: 2px 5px;\">".implode('<br>',$aFacturesFourn)."</td>"
						. "</tr>";

					}
				}
			}

			break;
	}
	
	echo ""
	. "<table cellpadding=\"0\" cellspacing=\"0\" style=\"width: 1000px; font-size: 12px; font-family: Arial, Helvetica, sans-serif; text-align: center; border: 1px solid;\">"
		. "<tr>"
			. "<td style=\"font-size: 16px; background-color: #1563A3; color: white; font-weight: bold; padding: 2px 5px;\">N° CMD</td>"
			. "<td style=\"font-size: 16px; background-color: #1563A3; color: white; font-weight: bold; padding: 2px 5px;\">Date CMD</td>"
			. "<td style=\"font-size: 16px; background-color: #1563A3; color: white; font-weight: bold; padding: 2px 5px;\">Article</td>"
			. "<td style=\"font-size: 16px; background-color: #1563A3; color: white; font-weight: bold; padding: 2px 5px;\">Type offre</td>"
			. "<td style=\"font-size: 16px; background-color: #1563A3; color: white; font-weight: bold; padding: 2px 5px;\">Montant dû</td>"
			. "<td style=\"font-size: 16px; background-color: #1563A3; color: white; font-weight: bold; padding: 2px 5px;\">Montant facturé</td>"
			. "<td style=\"font-size: 16px; background-color: #1563A3; color: white; font-weight: bold; padding: 2px 5px;\">N° facture</td>"
		. "</tr>";
	
	$cpt = 0;
	foreach($tableau as $la_ligne){
		$bgcolor = $cpt%2 == 0 ? 'white' : '#F9F9F9';
		
		echo str_replace('%BGCOLOR%',$bgcolor,$la_ligne);
		$cpt++;
	}
	
	echo "</table>";
	
	echo "<p style=\"font-weight: bold;\">Nombre d'erreurs : ".$nb_erreur." (soit ".$val_erreur."€)</p>";
	echo "<p style=\"font-size: 12px;\">Attention, montant calculé sur la base du prix d\'achat actuel de l\'article. Il peut donc différer de celui en place au moment de la commande.</p>";
}

echo "<form method=\"post\" action=\"\">";
echo "<label for=\"annee_verif\">Année à vérifier :</label><select id=\"annee_verif\" name=\"annee_offres\">";
for($i=2015;$i<=date('Y')+1;$i++){
	echo "<option value=\"".$i."\">".$i."</option>";
}
echo "</select>";
echo "<br><br>";
echo ""
. "<label for=\"cmd_verif\">Commandes à vérifier :</label>"
. "<select id=\"cmd_verif\" name=\"cmd_offres\">"
	. "<option value=\"1\">Classiques</option>"
	. "<option value=\"2\">Stock</option>"
. "</select>";
echo "<br><br>";
echo "<input type=\"submit\" value=\"Valider\">";
echo "</form>";