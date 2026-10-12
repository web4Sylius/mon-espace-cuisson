<?php
/* Tâche 6876 historique configurateur */
include("admin_global.php");
include_once("../class/configurateur.class.php");
include_once("../class/configurateur/complet.php");
include_once("../class/categorie.class.php");
include_once("../class/frais_port.php");
include_once("../class/garantie.class.php");
include "../class/DAOgarantie.class.php";
include "../class/DAOarticle.class.php";
include "./header.php";
include "./menu.php";

$DB_site = $DB_site2;
$categorie=new categorie($DB_site);
$categorie->setparam("site_code",$template->site_code());
$categorie->setparam("secondchoix",0);
$categorie->setparam("active",1);
$categorie->setparam("typearticleids",1);

$sql = "SELECT *";
$sql .= " FROM pays";
$sql .= " WHERE paysid=57";
$tvafrance = $DB_site->query_first($sql);
$tvanormal = (1+$tvafrance['TVAtauxnormal']/100);


$valueau=$valuedu=$check=$check2=$userid="";
if(isset($_GET['action']) && $_GET['action'] == "search"){
	if(isset($_GET['du']) && isset($_GET['au'])){
		$du = new DateTime($_GET['du']);
		$au = new DateTime($_GET['au']);

		$timestampdu = $du->getTimestamp();
		$timestampau = $au->getTimestamp();
		$valuedu = $du->format('Y-m-d');
		$valueau = $au->format('Y-m-d');

		if(isset($_GET['userid'])){
			$userid = " AND c.userid!=0";
			$check2 = "checked";
		}

		$configs=$DB_site->query("SELECT c.*, u.nom, u.prenom, u.raisonsociale, u.paysid_livraison FROM configurateur c LEFT JOIN utilisateur u ON u.userid = c.userid INNER JOIN configurateur_reponse cr ON cr.configurateurid = c.configurateurid WHERE cr.configurateurid != '' ".$userid." AND c.date BETWEEN '".$timestampdu."' AND '".$timestampau."' GROUP BY cr.configurateurid");

		foreach($configs as $config) {
			$rep="";
			$image="";
			$vignettes="";
			$configurateur = new configurateur($DB_site,$config['configurateurid']);
			foreach($configurateur->reponses() as $nb => $reponse){
				$infos = $reponse->infos();
				if($infos!=""){
					$rep.="<b>".$reponse->type . " :</b> " . $infos."<br>";
				}
			}
			if(is_array($configurateur->artids) && count($configurateur->artids) > 0){
				foreach($configurateur->artids as $configartid){
					if(file_exists($_SERVER['DOCUMENT_ROOT']."/configurations/www.ma-cave-a-vin.fr/images/produits/br/".$configartid.".jpg")){
						$art=$DB_site->query_first("SELECT * FROM article a INNER JOIN article_marque am ON am.artid = a.artid WHERE a.artid =".$configartid);
						$prixcaract = array(
								'achat' => 0,
								'prixht' => 0,
								'prix' => 0
							);
//// /////***** règle marge ****//////
						if($_GET['nouvelles_regles']=='1'){
							$check="checked";
							$frais_port = new frais_port($DB_site,array($configartid=>1),$config['paysid_livraison'],$art['prix'], $art['paht'] + $art['ecopartht']);
							$sqlcaract = "SELECT differenceprix, differenceprixht, achat, libelleFR FROM caracteristiquevaleur AS cv
							INNER JOIN `article_caractval` USING(caractvalid)
							WHERE caractvalid=(
								SELECT caractvalid FROM caracteristiquevaleur 
								INNER JOIN `article_caractval` USING(caractvalid)
								WHERE artid=".$configartid."
								AND caractid=cv.caractid
								ORDER BY position LIMIT 1
								)
								AND artid=".$configartid;

								$caracts = $DB_site->query($sqlcaract);

								while($caract = $DB_site->fetch_array($caracts)){
									$differenceprixht=unserialize($caract['differenceprixht']);
									$differenceprix=unserialize($caract['differenceprix']);
									$prixcaract['achat']  += $caract['achat'];
									$prixcaract['prixht'] += $differenceprixht['base'];
									$prixcaract['prix']   += $differenceprix['base'];
								}
								$sql="SELECT foa.offre, foa.value FROM fournisseur_offre AS fo
								INNER JOIN fournisseur_offre_article AS foa ON(fo.id=foa.offreid)
								WHERE foa.artid=".$configartid." AND fo.du < '".time()."' AND fo.au > '".time()."' LIMIT 1";
								$offre_fournisseur=$DB_site->query_first($sql);
								if($offre_fournisseur['offre']!=""){
									if($offre_fournisseur['offre']=="temp"){
										$art['paht'] = $offre_fournisseur['value'];
									}
									if($offre_fournisseur['offre']=="reduc"){
										$art['paht'] = $art['paht'] - $offre_fournisseur['value'];
									}
									if($offre_fournisseur['offre']=="pc"){
										$art['paht'] = $art['paht'] - ($art['paht'] * $offre_fournisseur['value'] / 100);
									}
								}
								$article_fournisseur=$DB_site->query_first("SELECT ecopartht, trspt FROM article_fournisseur WHERE artid=".$configartid." AND principal LIKE '%base%' AND devise='EUR' ");
								if($article_fournisseur['ecopartht']==""){
									$article_fournisseur['ecopartht']=0;
								}
								if($article_fournisseur['trspt']==""){
									$article_fournisseur['trspt']=0;
								}
								$lesGaranties=DAOgarantie::loadMaxGarantieArtByPays($DB_site,'base','FR','57',$configartid,'',$art['prix']);
								$i=0;
								$garantie=0;
								if(!empty($lesGaranties)){
									foreach ($lesGaranties as $laGarantie) {
										$i++;
										if ($i==1){
											$garantie=round($laGarantie->paht);
										}
										if ($laGarantie->cumul==1){
											$garantie+=round($laGarantie->paht);
										}
									}
								}
								$prixtotal = $art['prix'] + $prixcaract['prix'];
								$prixtotaltrspt = $prixtotal + ($frais_port->prixht * $tvanormal);
								$article_paht_total = $art['paht'] + $art['ecopartht'] + $article_fournisseur['trspt'] + $garantie + $frais_port->paht + $prixcaract['achat'];
								$article_pv_ht_total = $art['prixht'] + $prixcaract['prixht'] + $frais_port->prixht;
								$marge = round($article_pv_ht_total - $article_paht_total, 2);
								$marge_pct = 100 * $marge / $article_pv_ht_total;
							}
//// /////***** fin règle marge ****//////
							else {
								$sqlcaract = "SELECT differenceprix FROM caracteristiquevaleur AS cv
								INNER JOIN `article_caractval` USING(caractvalid)
								WHERE caractvalid=(
									SELECT caractvalid FROM caracteristiquevaleur 
									INNER JOIN `article_caractval` USING(caractvalid)
									WHERE artid=".$configartid."
									AND caractid=cv.caractid
									ORDER BY position LIMIT 1
									)
									AND artid=".$configartid;

									$caracts = $DB_site->query($sqlcaract);

									while($caract = $DB_site->fetch_array($caracts)){
										$differenceprix=unserialize($caract['differenceprix']);
										$prixcaract['prix'] += $differenceprix['base'];
									}
									$prixtotal = $art['prix'] + $prixcaract['prix'];
								}

							if($marge_pct >= 30 || $_GET['nouvelles_regles'] != '1') {
								$vignettes.="<div class=\"vignettes_histo_config\" >";
								if(file_exists($_SERVER['DOCUMENT_ROOT']."/configurations/www.ma-cave-a-vin.fr/images/marques/br/".$art['marqueid'].".jpg")) {
									$vignettes.="<img src=\"/configurations/www.ma-cave-a-vin.fr/images/marques/br/".$art['marqueid'].".jpg\" height=\"18\" style=\"margin-bottom:3px;\"><br>";
								}
								$vignettes.="<a href=\"ajout_produit.php?artid=".$configartid."&prec=modif\" target=\"_blank\"><img src=\"/configurations/www.ma-cave-a-vin.fr/images/produits/br/".$configartid.".jpg\" style=\"max-height: 50px; max-width: 50px;\" height=\"50\"></a>
								<p>".$art['artcode'].
								"</p><p class=\"pt\">(".(strlen($art['artcode_eurocave']) <= 10 ? $art['artcode_eurocave'] : substr($art['artcode_eurocave'],0,10)."...").")</p>
								<table><tr><td class=\"td_intitule1\">PPI TTC</td><td class=\"td_alt1\">".number_format($art['prixconseilleht'] * $tvanormal,2,',',' ')."&euro;"."</td></tr>
								<tr><td class=\"td_intitule2\">PV TTC</td><td class=\"td_alt2\">".number_format($prixtotal,2,',',' ')."&euro;"."</td></tr></table>
								</div>";
							}
							else {
								$vignettes.="";
							}
						}
					}
				}
				$tr_conf.="<tr><td>";
				if($config['userid']!=0){
					$tr_conf.="<a href=\"utilisateur.php?action=editer&userid=".$config['userid']."\" target=\"_blank\">".$config['configurateurid']." - ".$config['prenom']. " " .$config['nom']."</a></td>
					<td>".($config['raisonsociale']==''?'PART':'PRO')."</td>";
				}
				else {
					$tr_conf.=$config['configurateurid']."</td><td></td>";
				}
				$tr_conf.="<td>".date("d-m-Y \à H:m:s",$config['date'])."</td>
				</tr>
				<tr><td>".$rep."</td><td colspan='3'><div style='overflow-x:auto;white-space: nowrap;background-color:#EBEBEB;max-width:600px;'>".$vignettes."</div></td></tr>";
			}
		}
	}

	print '
	<h1>Historique des configurations utilisateurs</h1>
	<form method="get" action="#" name="search">
	<input type="hidden" name="action" value="search">
	<p>Choix de la date : Début <input type="date" id="du" name="du" value="'.$valuedu.'" required>   Fin <input type="date" id="au" name="au" value="'.$valueau.'" required>
	<input type="checkbox" id="nouvelles_regles" name="nouvelles_regles" value="1" '.$check.'>
	<label for="nouvelles_regles">Activer nouvelles règles</label>
	<input type="checkbox" id="user" name="userid" value="1" '.$check2.'>
	<label for="user">Utilisateurs identifiés seulement</label><br>
	<input type="submit" value="Valider"></p>
	</form>

	<table class="tab_histo_conf">
	<tr>
	<th>ID config - Prénom Nom</th><th>PRO ou PART</th><th>Date config</th>
	</tr>
	'.$tr_conf.'
	</table>';


	include "./footer.php";