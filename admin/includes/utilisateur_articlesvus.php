<?php


$stat = new ccstat();
// $visits = $stat->get_visit($_GET['userid'],time(),'stat',false);
$visits = $stat->get_all_visit($_GET['userid']);



$td = $datevus = array();
$articlesvu  = array();
if(isset($_GET['userid'])) {
	$userid = $_GET['userid'];
	$dateinscr=new DateTime($utilisateur['dateinscription']);
	$archdate=new DateTime("6 month ago");
	$av_arch="";
	if($archdate>$dateinscr){
		$av_arch=" UNION SELECT * FROM article_vu_arch WHERE userid='".$userid."'";
	}

	$req_vus = $DB_site->query("SELECT * FROM article_vu WHERE userid = '".$userid."' ".$av_arch);
	while($vus = $DB_site->fetch_array($req_vus)) {
		$td[$vus['artid']]=article_vignette($vus['artid'],array("nb" => $vus['nb']), "articlesvus");
		$datevus[$vus['artid']]=$vus['date'];
	}
}


if(count($td) > 0){
    $width_vus = count($td)*200;
    ?>
	<a name="articles_vus"></a>
	<div class="commande_detail" id="utilisateur_articlesvus">
		 <h2>ARTICLES VUS</h2>
		 <div class="table_modules_boutiques no_border prop_article">
			  <div class="overflow_auto">
				   <div style="width: <?php echo $width_vus; ?>px;">
						<?php 
						foreach($td as $article_vu){
							?>
							 <div class="proposition" style="width:180px;">
							 	<span class="date_artvu">Fait le <?=date('d/m/Y',$datevus[$article_vu['artid']])?><br> 
							 	<?php
							 	///pour lier/délier un article vu à un projet :
							 	$projet_arts=$DB_site2->query_first("SELECT projetid FROM projet WHERE articles_vus LIKE '%\"".$article_vu['artid']."\"%' AND userid = ".$userid);
							 	if(isset($projet_arts['projetid'])) {
							 		?>
							 		<form method="post" style="display:inline">
							 			<input type='hidden' value='delier-projet-artvu' name='action'/>
							 			<input type='hidden' value='<?php echo $projet_arts['projetid'];?>' name='projetid_artvu'/>
							 			<input type='hidden' value="<?php echo $article_vu['artid'];?>" name='artvuid'/>
							 			<input type="submit" name="delier_artvu" value="enlever du projet <?php echo $projet_arts['projetid'];?>" class="conf_projet"><?php } else {
							 				$projets_liste=$DB_site2->query("SELECT * FROM projet WHERE userid = ".$userid." AND etatid IN (2, 3, 4, 5, 6, 7, 8)");
							 				if($DB_site2->num_rows($projets_liste) > 0){ ?>

							 					<form method="post" style="display:inline">
							 						<input type='hidden' value='lier-projet-artvu' name='action'/>
							 						<input type='hidden' value="<?php echo $article_vu['artid'];?>" name='artvuid'/>
							 						<select name="liste_projets" style="width: 110px">
							 							<option value="">-- Projets --</option>";
							 							<?php

							 							while($pjt=$DB_site2->fetch_array($projets_liste)){
							 								echo "<option value=".$pjt['projetid'].">PJT du ".date('d/m/Y', $pjt['dateline'])." n°".$pjt['projetid']." - " .substr(UCfirst($pjt['libelle']), 0, 60)."... - ".$pjt['commercial']."</option>";
							 							}
							 							?>
							 						</select>
							 						<input type="submit" name="lier_artvu" value="Lier l'article vu au projet" class="conf_projet"/>
							 					</form><?php } } ?></span>
								  <?php echo $article_vu['marque_visuel']; ?>
								  <?php echo $article_vu['img']; ?>
								  <p><?php echo $article_vu['artcode_mcav']; ?></p>
								  <p class="pt">(<?php echo $article_vu['artcode_eurocave']; ?>)</p>
								  <?php if(isset($article_vu['options']) && !empty($article_vu['options'])){ ?>
									   <a href="javascript:void(0);" class="lien" onclick="affiche_div('options_<?php echo $article_vu['artid']; ?>');">Afficher les options</a>
									   <p style="font-size:10px;font-style:italic;text-align:left;">Vu le <?php echo date("d-m-Y h:m",$datevus[$article_vu['artid']]); ?></p>
									   <div id="options_<?php echo $article_vu['artid']; ?>" class="caracteristiques">
											<table cellpadding="0" cellspacing="0" border="0">
												 <?php  foreach($article_vu['options'] as $option){ ?>
													  <tr>
														   <td class="td_intitule1 center"><strong><?php echo $option['caracteristique']; ?></strong></td>
													  </tr>
													  <tr>
														   <td class="td_alt1 center"><?php echo $option['libelle']; ?> <?php echo $option['differenceprix']; ?> €</td>
													  </tr>
												 <?php } ?>
											</table>
									   </div>
								  <?php } else { ?>
									   <p class="pt rouge">Sans options</p>
								  <?php } ?>
								  <div id="carac_<?php echo $article_vu['artid']; ?>" class="caracteristiques" style="display:block;">
									   <table cellpadding="0" cellspacing="0" border="0">
											<?php 
											$i = 0;
											foreach($article_vu['rows'] as $key => $caract){
												$classtd1 = $i%2 == 0 ? 'td_intitule1' : 'td_intitule2';
												$classtd2 = $i%2 == 0 ? 'td_alt1' : 'td_alt2';
												?>
												   <tr>
													  <td class="<?php echo $classtd1; ?>"><?php echo $key; ?></td>
													  <td class="<?php echo $classtd2; ?> center" nowrap><?php echo $caract; ?></td>
												   </tr>
												<?php
												$i++;
											}
											?>
									   </table>
								  </div>
								  <a class="lien lien_fiche" href="<?php echo $article_vu['lien_fiche']; ?>" target="_blank">Sur site</a>
								  <?php if(isset($article_vu['nb'])){ ?>
									<span class="nb_vues"><?php echo $article_vu['nb']; ?>x</span>
								  <?php } ?>
								  <?php echo $article_vu['dispo']; ?>
								  <?php echo $article_vu['files']; ?>
								  <?php echo $article_vu['lockit']; ?>
								  <?php echo $article_vu['spy']; ?>
							 </div>
							<?php
						}
						?>
				   </div>
			  </div>
		 </div>
		 	<div class="clear"></div>
		  <div style="float:right;" class="bouton_dispo">
			<a class="lien lien_fiche" href="javascript:artmaildispo();">Demande de disponibilité</a><br><br>
		  </div><div class="clear"></div>
	</div>
	<div class="clear"></div>
    <?php
}
