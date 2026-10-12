<?php
header('Content-Type: text/html; charset=utf-8');

$url_simulation = "https://creditpartner.fr/creditpartner/?q6=web_em&x1=simu_vac";
$a9 = "99102122272"; //Num vendeur
$n2 = "290"; //nature du bien 
 
require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

if(isset($_GET['type'])){

	switch($_GET['type']){

		case 'article':

			$article_id = $_GET['artid'];

			$article_prices = article_prices($article_id,$template->tva_ref(),$template->site_code(),$sitepro);

			$oArticle = new produit($DB_site, $template->langue, $template->site_code(), $template->tva_ref(), $article_id);

			$s3 = $article_prices['prix'];
			$url_simulation = $url_simulation."&s3=".$s3;

			$mensualites  = $article_prices['prix'] <= 2000 ? 4 : 10; 
			$prix_finance = number_format($article_prices['prix'] / $mensualites,2,',',' ');

			$template->parts['prix_finance'] = number_format($article_prices['prix'] / $mensualites,2,',',' ');

				
			$taeg 				 = "0,000%";
			$total_financement   = number_format($article_prices['prix'],2,',',' ');   
			$total_du            = number_format($article_prices['prix'],2,',',' ');   // $montant_total;
			$url_finance         = "/financement_sofinco.php?montant=".$article_prices['prix']."&mensualites=".$mensualites;
			$url_simulation      = $sofinco_url_pt."&a9=".$a9."&n2=290&s3=".$total_du;
			$mention_pedagogique = "Un crédit vous engage et doit être remboursé. Vérifiez vos capacités de remboursement avant de vous engager.";

			$template->evaltemplate("PaiementSofinco","paiement_sofinco");
			?>

			<div id="sofinco_pt" style="display:none;">
			     <p>Payez en <span><?php echo $mensualites; ?> fois sans frais</span> avec</p>
			     <img src="../images/templates/sofinco_petit.jpg" class="sofinco"> <span class="mensualites"><?php echo $mensualites; ?> x <?php echo $prix_finance; ?>&euro;</span>
			     <p>Ou jusqu'en 84 fois <a rel="nofollow" href="javascript:void(0);" onclick="$('#sofinco_pt').toggle(); $('#sofinco_gd').toggle();" rel="nofollow">En savoir plus</a></p>
			</div>
			<div id="sofinco_gd">
				<p style="float: left; margin: 0; padding: 0; display: inline; width: 120px; font-size: 18px; font-weight: 700;">Solutions de<br><span style="font-weight: 400; color: #a88d5c;">financement</span></p>
				<img src="/images/templates/sofinco_petit.jpg" style="float: right;">
				<div class="clear"></div>
				<div style="text-align: left;">
					<ul>
						<li>
							<span class="intitule">En <?php echo $mensualites; ?> fois sans frais :</span>
							<p>Financez votre achat de <span class="mensualites"><?php echo $total_financement; ?> &euro;</span></p>
							<p>TAEG Fixe <span class="mensualites"><?php echo $taeg; ?></span></p>
							<p>Montant total dû à l'emprunteur : <span class="mensualites"><?php echo $total_du; ?></span> hors assurance facultative</p>
							<p style="margin: 5px 0 2px 5px;"><a href="javascript:void(0);" style="text-decoration: underline;" onclick="tb_show('Financer mon achat de <?php echo $total_financement; ?> &euro;','<?php echo $url_finance ;?>',false);" rel="nofollow">En savoir plus</a></p>
							<img src="../images/templates/FR/sans_frais.jpg">
							<p style="margin: 5px 0 2px 5px;">
							<span class="reglement">Régler en</span>
							<br>
							<span class="mensualites"><small><?php echo $mensualites; ?> x</small> <?php echo $prix_finance; ?> &euro;</span>
							</p>
							<hr />
						</li>
						<li>
							<span class="intitule">Jusqu'en 84 fois :</span>
							<p class="reglement" style="margin: 8px 0 8px 5px;">
								<a rel="nofollow" href="javascript:void(0)" onclick="window.open('<?php echo $url_simulation; ?>','Simulation de financement','width=800, height=800, scrollbars=yes');" class="simulation"></a>
								Régler votre achat entre <span class="mensualites">12</span> et jusqu'à  <span class="mensualites">84</span> mensualités
							</p>
						</li>
					</ul>
					<p class="mentions_legales"><?php echo $mention_pedagogique; ?></p>
					<a href="javascript:void(0);" onclick="$('#sofinco_pt').toggle(); $('#sofinco_gd').toggle();">Fermer</a>
				</div>
			</div>

			<?php
			break;

		case 'commande':

		$commande_id = $_GET['facturid'];
		$mensualites = $_GET['mensualites'];
		$moyenpaiement = $_GET['moyenpaiement'];

		$fcl = new facture($DB_site, $commande_id);

		$url_simulation      = $sofinco_url_pt."&a9=".$a9."&n2=290&s3=".number_format($fcl->totalttc,2,'.','');

		$sql = "
		SELECT description".$template->langue." 
		FROM moyenpaiement 
		WHERE moyenid = ".$moyenpaiement;
		$moyen_paiement = $DB_site->query_first($sql);

		$mention_pedagogique = "Un crédit vous engage et doit être remboursé. Vérifiez vos capacités de remboursement avant de vous engager.";
		$template->parts["total_sofinco"]  = number_format($fcl->totalttc,2,',',' ');
		$template->parts["simulation_achat"]="<p class=\"reglement\" style=\"margin: 8px 0 8px 5px;\">
				<a href=\"javascript:void(0)\" onclick=\"window.open('".$url_simulation."','Simulation de financement','width=800, height=800, scrollbars=yes');\" class=\"simulation\"><img src=''>Simulez votre achat</a>
				En cliquant sur ce lien vous allez être redirigé vers le site sécurisé de notre partenaire SOFINCO
			</p>
			<p>Un crédit vous engage et doit être remboursé. Vérifiez vos capacités de remboursement avant de vous engager</p>";
		$template->evalvartemplate($moyen_paiement['description'.$template->langue],"desc_sofinco");
		echo $template->parts["desc_sofinco"];

		break;

	}
} 