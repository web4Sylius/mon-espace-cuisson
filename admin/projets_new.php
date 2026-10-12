<?php
require_once("admin_global.php");
include_once("./header.php");
include_once("./menu.php");
include_once("../class/projet.class.php");
include_once("fonctions.php");
include_once("./adv/adv.class.php");

$projets=new projets();

?>

<h1>PROJETS :</h1>
<a href="projets_new.php" target="_blank" <?= (empty($_GET) ? 'style="text-decoration:none;background-color: #e4dede;color: black; padding: 3px; border-radius: 5px;"' : '')?>>Clients à traiter</a> &nbsp; <a href="projets_new.php?commercial=<?= $user_info['username'] ?>"  target="_blank" <?= (isset($_GET['commercial']) && empty($_GET['action']) ? 'style="text-decoration:none;background-color: #e4dede;color: black; padding: 3px; border-radius: 5px;"' : '')?>>Projets commerciaux</a> &nbsp; <a href="projets_new.php?type_page=_new&action=search&projet_etatid=2,3,7,8&commercial=<?= $user_info['username'] ?>" target="_blank" <?= (isset($_GET['action']) && $_GET['action'] !== 'suiviexterne' ? 'style="text-decoration:none;background-color: #e4dede;color: black; padding: 3px; border-radius: 5px;"' : '')?>>Projets en cours</a> &nbsp; <a href="projets_new.php?action=suiviexterne"  target="_blank" <?= (isset($_GET['action']) && $_GET['action'] == 'suiviexterne' ? 'style="text-decoration:none;background-color: #e4dede;color: black; padding: 3px; border-radius: 5px;"' : '')?>>Suivi externe</a>


<?php
//// TABLEAU DE BORD CLIENTS A TRAITER ////

//La fonction quand on clique sur la croix des cde non abouties / refusées. Ces commandes sont traitées et donc ne doivent plus apparaitre dans le tableau :
if($_GET['delete'] && empty($_GET['commercial'])){
	$sql = "UPDATE facture SET refuse_suivi_projet = '1' WHERE facturid = ".$_GET['delete'];
	$requete = $DB_site2->query($sql);
	echo "<script>window.location = 'projets_new.php';</script>";
}

//La fonction quand on clique sur la croix des rappels commandes, pour ne plus les afficher dans le tableau :
if($_GET['delete'] && isset($_GET['commercial'])){
	$sql = "UPDATE suivi_commande SET hide = '1' WHERE facturid = ".$_GET['delete'];
	$requete = $DB_site2->query($sql);
	echo "<script>window.location = 'projets_new.php?commercial=".$_GET['commercial']."';</script>";
}

if(empty($_GET) || (empty($_GET['commercial']) && empty($_GET['action']))){
	
	$nb_rapp_im = $nb_sans_proj = $nb_cmd_ref = $nb_form = $nb_form_pro = 0;
	$link_rapp_im = $link_sans_proj = $link_cmd_ref = $link_form = $link_form_pro = "";

//Rappels immédiat
	$sql = "SELECT * FROM contact WHERE date_reponse=0 AND sujet = 'Rappel immédiat' AND commercial = '' ORDER BY date";
	$requete = $DB_site2->query($sql);
	$nb_rapp_im = $DB_site2->num_rows($requete);
	if($DB_site2->num_rows($requete)>0){
		$i = 1;
		$max = isset($_GET['imm']) ? $_GET['imm'] + 1 : 11;
		while($contact=$DB_site2->fetch_array($requete)){
			if($i < $max){
				$date=date("d/m H:i",$contact['date']);
				$nom=trim($contact['raisonsociale']." ".$contact['nom']." ".$contact['prenom']);
				$link_rapp_im.="
				<div style='height:21px;overflow-y:hidden;padding:1px;'>".$date." -
				<a href='contacts.php?contactid=".$contact['id']."' target='_blank'>".$nom."</a>
				</div>";
				$i++;
			}
		}
	}

//Contacts sans projet
	$sql = "SELECT userid,dateinscription, nom, prenom, raisonsociale FROM utilisateur u LEFT JOIN projet USING(userid) WHERE `conseils` = 'oui' AND projetid IS NULL AND (SELECT COUNT(*) FROM projet_relation WHERE userid=u.userid)=0 AND (SELECT COUNT(*) FROM facture WHERE (valide IN (1,5,7,8) OR moyenid IN (2,5) OR moyenid = 17) AND userid=u.userid)=0 AND (SELECT COUNT(*) FROM suivi_client WHERE userid=u.userid AND etat=0)=0 ORDER BY dateinscription";
	$requete = $DB_site2->query($sql);
	$nb_sans_proj = $DB_site2->num_rows($requete);
	if($DB_site2->num_rows($requete)>0){
		$i = 1;
		$max = isset($_GET['ssproj']) ? $_GET['ssproj'] + 1 : 11;
		while($contact=$DB_site2->fetch_array($requete)){
			if($i < $max){
				$d=new datetime($contact['dateinscription']);
				$date=$d->format("d/m/y H:i");
				$nom=trim($contact['raisonsociale']." ".$contact['nom']." ".$contact['prenom']);
				$link_sans_proj.="
				<div style='height:21px;overflow-y:hidden;padding:1px;'>".$date." -
				<a href='utilisateur.php?action=editer&userid=".$contact['userid']."' target='_blank'>".$contact['userid']." - ".$nom."</a>
				</div>";
				$i++;
			}
		}
	}

//Cmd refusée / non aboutie
	$deux_mois = time() - (60*60*24)*61;
	$lendemain = time() - (60*60*24);
	$sql = "SELECT facturid, heurefacture, valide, userid FROM facture WHERE (valide = 3 OR (valide = 0 AND moyenid = 1) OR (moyenid = 17 AND num_dossier='' AND heurefacture < ".$lendemain.")) AND heurefacture > ".$deux_mois." AND refuse_suivi_projet = '0' ORDER BY heurefacture DESC";
	$requete = $DB_site2->query($sql);
	if($DB_site2->num_rows($requete)>0){
		$i = 1;
		$nb_cmd_ref = 0;
		$max = isset($_GET['cmdref']) ? $_GET['cmdref'] + 1 : 11;
		while($cmd=$DB_site2->fetch_array($requete)){
			///si le client a repassé une commande (validée, non traitée mais pas abandonnée, expédiée), on n'affiche pas sa commande dans le tableau :
			$sql_cde_valide = $DB_site2->query_first("SELECT facturid FROM facture WHERE userid = '".$cmd['userid']."' AND valide IN (1,5,7,8,0) AND moyenid != 0 AND heurefacture > ".$cmd['heurefacture']." ORDER BY heurefacture DESC");
			if($i < $max){	
				if(count($sql_cde_valide)==0){
					$facture = new facture($DB_site2,$cmd['facturid']);
					$date=date("d/m H:i",$cmd['heurefacture']);
					$etatfac = ($cmd['valide']==3?'refusée':'non aboutie');
					$link_cmd_ref.="
					<div style='height:21px;overflow-y:hidden;padding:1px;'><a href='projets_new.php?delete=".$cmd['facturid']."' style='color:red;cursor:pointer;text-decoration:none;'>X</a> ".$date." - 
					<a href='utilisateur.php?action=editer&userid=".$cmd['userid']."' target='_blank'>".$cmd['facturid']."</a> - ".$facture->totalttc."€ - ".$etatfac."
					</div>";
					$i++;
				}
			}
			if(count($sql_cde_valide)==0){
				$nb_cmd_ref++;
			}
		}
	}

//Contacts form
	$sql = "SELECT * FROM contact WHERE date_reponse=0 AND sujet != 'Rappel immédiat' AND commercial = '' ORDER BY date";
	$requete = $DB_site2->query($sql);
	$nb_form = $DB_site2->num_rows($requete);
	if($DB_site2->num_rows($requete)>0){
		$i = 1;
		$max = isset($_GET['form']) ? $_GET['form'] + 1 : 11;
		while($contact=$DB_site2->fetch_array($requete)){
			if($i < $max){
				$date=date("d/m H:i",$contact['date']);
				$nom=trim($contact['raisonsociale']." ".$contact['nom']." ".$contact['prenom']);
				$link_form.="
				<div style='height:21px;overflow-y:hidden;padding:1px;' onmouseover='affich_info(this)' onmouseout='affich_info(this,\"out\")'>".$date." -
				<a href='contacts.php?contactid=".$contact['id']."' target='_blank'>".substr($nom,0,18)."</a>
				<span style='display:none;position: absolute; padding: 3px; border: 2px solid black; border-top: none;min-width:200px;margin-left:-3px;'>".$contact['sujet']."</span>
				</div>";
				$i++;
			}
		}
	}

//Contacts form pro
	$sql = "SELECT * FROM contact_leads WHERE date_reponse=0 AND commercial = '' ORDER BY date";
	$requete = $DB_site2->query($sql);
	$nb_form_pro = $DB_site2->num_rows($requete);
	if($DB_site2->num_rows($requete)>0){
		$i = 1;
		$max = isset($_GET['formpro']) ? $_GET['formpro'] + 1 : 11;
		while($contact=$DB_site2->fetch_array($requete)){
			if($i < $max){
				$date=date("d/m H:i",$contact['date']);
				$nom=trim($contact['raisonsociale']." ".$contact['nom']." ".$contact['prenom']);
				$link_form_pro.="
				<div style='height:21px;overflow-y:hidden;padding:1px;'  onmouseover='affich_info(this)' onmouseout='affich_info(this,\"out\")'>".$date." -
				<a href='contacts_leads.php?contactid=".$contact['id']."' target='_blank'>".$nom."</a>
				<span style='display:none;position: absolute; padding: 3px; border: 2px solid black; border-top: none;min-width:200px;margin-left:-3px;'>".$contact['message']."</span>
				</div>";
				$i++;
			}
		}
	}

//Nouveaux visiteurs sans compte
	$sql_exist = "SELECT v.*, u.mail as cmpt_exist FROM visiteur v LEFT JOIN utilisateur u ON u.mail = v.mail WHERE u.mail is null AND archive != 'oui' ORDER BY v.visiteurid ASC";
	$liste_exist = $DB_site2->query($sql_exist);
	$nb_exist = $DB_site2->num_rows($liste_exist);

//Récupération de tous les commerciaux actifs dans les 30 derniers jours
	$sql =  "SELECT username FROM parametresgeneraux WHERE actif = '1' AND commissions ='1' ORDER BY username";
	$commercials = $DB_site2->query($sql);

	if($DB_site2->num_rows($commercials) > 0){
		$fromdate=new datetime("4 years ago");
		$veille = mktime(0,0,0,date('m'),date('d'),date('Y')) - 24*60*60;
		//Suivi interne sur 3 jours (jour même et 2 jours avant)
		$jour_meme = mktime(23,59,59,date('m'),date('d'),date('Y'));
		//si on est lundi ou mardi, on affiche les rappels de jeudi et vendredi, ou vendredi :
		if(date('w') == '1' || date('w') == '2'){
			$deuxj_av = mktime(0,0,0,date('m'),date('d'),date('Y')) - ((24*60*60) * 4);
		}
		else{
			$deuxj_av = mktime(0,0,0,date('m'),date('d'),date('Y')) - ((24*60*60) * 2);
		}
		while($commercial=$DB_site2->fetch_array($commercials)){
			$projets_comm = $projets->search($commercial['username'],array(2,3,7,8));
			$projets_encours = count($projets_comm);
			$budget = $projets->budget_total_encours($commercial['username'],array(2,3,7,8));
			$rdv = $DB_site2->query("SELECT * FROM suivi_client WHERE rappeltype ='rdv' AND pour = '".$commercial['username']."' AND rappel BETWEEN ".mktime('00','00','01',date("m"),date("d"),date("Y"))." AND ".mktime('23','59','59',date("m"),date("d"),date("Y")));
			$nb_rdv = $DB_site2->num_rows($rdv);
			$suivi_int = $DB_site2->query("SELECT * FROM projet WHERE rappel_commercial = '".$commercial['username']."' AND rappel_type != 'rdv' AND rappel BETWEEN ".$deuxj_av." AND ".$jour_meme);
			$nb_suivi_int = $DB_site2->num_rows($suivi_int);
			$rdv_retard = $DB_site2->query("SELECT p.*, u.nom FROM projet p INNER JOIN utilisateur u ON u.userid = p.userid WHERE p.rappel_commercial = '".$commercial['username']."' AND etatid IN ('1','2','3','4','7','8') AND rappel_type = 'rdv' AND rappel BETWEEN ".intval($fromdate->gettimestamp())." AND ".mktime('00','00','01',date("m"),date("d"),date("Y")));
			$nb_rdv_retard = $DB_site2->num_rows($rdv_retard);
			$suivi_int_retard = $DB_site2->query("SELECT p.*, u.nom FROM projet p INNER JOIN utilisateur u ON u.userid = p.userid WHERE p.rappel_commercial = '".$commercial['username']."' AND etatid IN ('1','2','3','4','7','8') AND rappel_type != 'rdv' AND rappel BETWEEN ".intval($fromdate->gettimestamp())." AND ".$deuxj_av);
			$nb_suivi_int_retard = $DB_site2->num_rows($suivi_int_retard);
			///On affiche seulement si aucun suivi n'a été fait dans les 7j avant aujourd'hui ET si aucun rappel futur
			$projets_prio = $DB_site2->query("SELECT p.*, u.nom, u.raisonsociale, u.prenom FROM projet p INNER JOIN utilisateur u ON u.userid = p.userid LEFT JOIN suivi_client sc ON sc.projetid = p.projetid WHERE p.commercial = '".$commercial['username']."' AND etatid IN ('1','2','3','4','7','8') AND echeance_livraison != 0 AND p.rappel < ".time()." AND sc.date NOT BETWEEN ".(time() - ((24*60*60)*7))." AND ".time()." GROUP BY p.projetid ORDER BY p.echeance_livraison");
			$nb_projets_prio = 0;
			if($DB_site2->num_rows($projets_prio) > 0){
				while($projet_prio=$DB_site2->fetch_array($projets_prio)){
					$date_delais = $projet_prio['echeance_livraison'] - $projet_prio['delai_cmd'] - ((24*60*60)*5);
					if($date_delais <= time()){
						$nb_projets_prio++;
					}
				}
			}
			$a_chiffrer = $DB_site2->query("SELECT * FROM projet WHERE commercial = '".$commercial['username']."' AND etatid = 3");
			$nb_a_chiffrer = $DB_site2->num_rows($a_chiffrer);
			$cdes_enregistrees = $DB_site2->query("SELECT p.facturid FROM projet p INNER JOIN facture f ON f.facturid = p.facturid WHERE p.commercial = '".$commercial['username']."' AND etatid = 5");
			$tt_cde_enregistre = 0;
			while($cde_enregistree=$DB_site2->fetch_array($cdes_enregistrees)){
				$facture = new facture($DB_site2,$cde_enregistree['facturid']);
				$tt_cde_enregistre += $facture->totalttc;
			}

			$tableau_comm .= "<div class='flex_div'>
			<div class='titre_flex'>".$commercial['username']."</div>
			<div style='height:21px;overflow-y:hidden;padding:1px;'>".$nb_rdv." RDV du jour - prioritaire</div>
			<div style='height:21px;overflow-y:hidden;padding:1px;'>".$nb_suivi_int." Relances clients sur 3 jours</div>
			<div style='height:21px;overflow-y:hidden;padding:1px;'>".$nb_rdv_retard." RDV en retard</div>
			<div style='height:21px;overflow-y:hidden;padding:1px;'>".$nb_suivi_int_retard." Relances clients en retard</div><br>
			<div style='height:21px;overflow-y:hidden;padding:1px;'>".$projets_encours." Projets en cours</div>
			<div style='height:21px;overflow-y:hidden;padding:1px;'>".$budget." € de budget</div><br>
			<div style='height:21px;overflow-y:hidden;padding:1px;'> ".$nb_projets_prio." Projets Prioritaires Cde proche</div>
			<div style='height:21px;overflow-y:hidden;padding:1px;'>".$nb_a_chiffrer." Projets à chiffrer</div><br>
			<div style='height:21px;overflow-y:hidden;padding:1px;'>".number_format($tt_cde_enregistre,2,',',' ')."€ Total Cde enregistrees</div>
			</div>";
		}
	}
	?>

	<div class="titre_projets">Tableau de Bord - Service Commercial</div>
	<span class="style_btn" style="display:block;margin:auto;width:max-content;" onclick="document.location=window.location.href;">Mettre à jour</span>
	<div class="flex_projets">
		<div class="flex_div">
			<div class="titre_flex"><span style="font-size:15px;"><a href='projets_new.php<?php echo (isset($_GET['imm']) ? '' : '?imm='.$nb_rapp_im) ?>'>voir tout</a> <?= $nb_rapp_im ?></span> Rappels Immédiat</div>
			<?= $link_rapp_im ?>
		</div>
		<div class="flex_div">
			<div class="titre_flex"><span style="font-size:15px;"><a href='projets_new.php<?php echo (isset($_GET['ssproj']) ? '' : '?ssproj='.$nb_sans_proj) ?>'>voir tout</a> <?= $nb_sans_proj ?></span> Nouveaux Contacts sans Projet</div>
			<?= $link_sans_proj ?>
		</div>
		<div class="flex_div">
			<div class="titre_flex" style="background-color:orange;"><a href='projets_new.php<?php echo (isset($_GET['cmdref']) ? '' : '?cmdref='.$nb_cmd_ref) ?>'>voir tout</a> <span style="font-size:15px;"><?= $nb_cmd_ref ?></span> Commandes non abouties / refusées</div>
			<?= $link_cmd_ref ?>
		</div>
	</div>
	<div class="flex_projets">
		<div class="flex_div">
			<div class="titre_flex"><span style="font-size:15px;"><a href='projets_new.php<?php echo (isset($_GET['form']) ? '' : '?form='.$nb_form) ?>'>voir tout</a> <?= $nb_form ?></span> Contacts Formulaire</div>
			<?= $link_form ?>
		</div>
		<div class="flex_div">
			<div class="titre_flex"><span style="font-size:15px;"><a href='projets_new.php<?php echo (isset($_GET['formpro']) ? '' : '?formpro='.$nb_form_pro) ?>'>voir tout</a> <?= $nb_form_pro ?></span> Contacts Formulaire Campagne</div>
			<?= $link_form_pro ?>
		</div>
		<div class="flex_div">
			<div class="titre_flex"><?= $nb_exist ?> Nouveaux Visiteurs sans Compte</div>
			<div style='height:21px;overflow-y:hidden;padding:1px;'><a href="visiteur.php" target="_blank">voir</a></div>
		</div>
	</div>

	<div class="flex_commercials">
		<?= $tableau_comm ?>
	</div>
	<?php
}

//// FIN ////


//// TABLEAU DE BORD PAR COMMERCIAL //// 
elseif(isset($_GET['commercial']) && $_GET['commercial'] !== '' && empty($_GET['action'])){

	$opt_comm = $liste_rdv = $liste_prio = $datecmd = $liste_a_chiffrer = $liste_suivi_int = $liste_suivi_int_retard = $liste_rappel_cmd = "";

	$fromdate=new datetime("4 years ago");

	//Récupération de tous les commerciaux actifs dans les 30 derniers jours
	$projets=new projets();

	$sql =  "SELECT username FROM parametresgeneraux WHERE actif = '1' AND commissions ='1' ORDER BY username";
	$commercials = $DB_site2->query($sql);
	if($DB_site2->num_rows($commercials) > 0){
		$i = 1;
		while($commercial=$DB_site2->fetch_array($commercials)){
			if($i < 11){
				$selected = ($_GET['commercial'] == $commercial['username'] ? "selected" : "");
				$opt_comm .= "<option value='".$commercial['username']."' ".$selected.">".$commercial['username']."</option>";
				$i++;
			}
			if($_GET['commercial'] == 'tous'){
				$sql_comm[] = $commercial['username'];
			}
		}
	}

	if($_GET['commercial'] == 'tous'){
		$sql_comm = implode("','",$sql_comm);
	}
	else{
		$sql_comm = $_GET['commercial'];
	}

	//RDV du jour
	$rdvs = $DB_site2->query("SELECT p.rappel, u.nom, u.raisonsociale, u.prenom, p.budget, p.dateline, p.projetid FROM projet p INNER JOIN utilisateur u ON u.userid = p.userid WHERE rappel_type ='rdv' AND rappel_commercial IN ('".$sql_comm."') AND p.rappel BETWEEN ".mktime('00','00','01',date("m"),date("d"),date("Y"))." AND ".mktime('23','59','59',date('m'),date('d'),date('Y'))." ORDER BY p.rappel");
	$nb_rdv = $DB_site2->num_rows($rdvs);
	if($DB_site2->num_rows($rdvs) > 0){
		$i = 1;
		while($rdv=$DB_site2->fetch_array($rdvs)){
			$type_ech = "";
			if($i < 11){
				$projet = new projet($rdv['projetid']);
				$datecmd=$projet->datecmd();
				if(!is_null($datecmd)){
					$datecmdcolor=$datecmd<new datetime('today 0:0:0')?"red":"";
					$datecmd=$datecmd->format("d/m/Y");
				}
				$nom=($rdv['raisonsociale']!='' ? $rdv['raisonsociale'] : $rdv['nom']." ".$rdv['prenom']);
				if(!is_null($rdv['echeance_type'])){
					if($rdv['echeance_type'] == '1'){
						$type_ech = " - <div title='info donné par le client' style='display:inline;'>C</div>";
					}
					else{
						$type_ech = " - <div title='estimé par mcav' style='display:inline;'>M</div>";
					}
				}
				$liste_rdv .= "<div style='height:21px;overflow-y:hidden;padding:1px;' onmouseover='affich_info(this)' onmouseout='affich_info(this,\"out\")'><strong style='color:red;'>".date('H:i', $rdv['rappel'])."</strong> - <a href='projet.php?projetid=".$rdv['projetid']."' target='_blank' ".($rdv['raisonsociale']!='' ? "style='color:#0393C2;font-weight:bold'" : "").">".substr($nom,0,18). "...</a> - ".$rdv['budget']."€ - ".$datecmd.$type_ech."
				<span style='display:none;position: absolute; padding: 3px; border: 2px solid black; border-top: none;min-width:200px;margin-left:-3px;'>".$projet->pjt." - ".$projet->etat." - ".$projet->libelle."</span></div>";
				$i++;
			}
		}
	}

	//Projets prio
	///On affiche seulement si aucun suivi n'a été fait dans les 7j avant aujourd'hui ET si aucun rappel futur
	$projets_prio = $DB_site2->query("SELECT p.*, u.nom, u.raisonsociale, u.prenom FROM projet p INNER JOIN utilisateur u ON u.userid = p.userid LEFT JOIN suivi_client sc ON sc.projetid = p.projetid WHERE p.commercial IN ('".$sql_comm."') AND etatid IN ('1','2','3','4','7','8') AND echeance_livraison != 0 AND p.rappel < ".time()." AND sc.date NOT BETWEEN ".(time() - ((24*60*60)*7))." AND ".time()." GROUP BY p.projetid ".(!is_null($_GET['tri_prio']) ? $_GET['tri_prio'] : "ORDER BY p.echeance_livraison"));
	$nb_projets_prio = 0;

	//trier par budget ou date
	if(empty($_GET['tri_prio']) || $_GET['tri_prio'] == 'ORDER BY p.budget DESC' || $_GET['tri_prio'] == 'ORDER BY p.echeance_livraison DESC, p.delai_cmd DESC'){
		$budget_tri = 'ORDER BY p.budget';
		$date_tri = 'ORDER BY p.echeance_livraison, p.delai_cmd';
		$lib_budg_tri = 'ASC';
		$lib_date_tri = 'ASC';
	}
	elseif($_GET['tri_prio'] == 'ORDER BY p.budget'){
		$budget_tri = 'ORDER BY p.budget DESC';
		$date_tri = 'ORDER BY p.echeance_livraison, p.delai_cmd';
		$lib_budg_tri = 'DESC';
		$lib_date_tri = 'ASC';
	}
	elseif($_GET['tri_prio'] == 'ORDER BY p.echeance_livraison, p.delai_cmd'){
		$budget_tri = 'ORDER BY p.budget';
		$date_tri = 'ORDER BY p.echeance_livraison DESC, p.delai_cmd DESC';
		$lib_budg_tri = 'ASC';
		$lib_date_tri = 'DESC';
	}

	if($DB_site2->num_rows($projets_prio) > 0){
		$i = 1;
		$max = isset($_GET['prio']) ? $_GET['prio'] + 1 : 11;
		$liste_prio .= "<div style='height:21px;overflow-y:hidden;padding:1px;text-align:center;'><a href='projets_new.php?commercial=".$_GET['commercial']."&tri_prio=".$budget_tri."'>Trier budget ".$lib_budg_tri."</a> - <a href='projets_new.php?commercial=".$_GET['commercial']."&tri_prio=".$date_tri."'>Trier date délai ".$lib_date_tri."</a></div>";
		while($projet_prio=$DB_site2->fetch_array($projets_prio)){
			$type_ech = "";
			//affiche les projets où les commandes doivent être passées maxi 5 jours après aujourd'hui
			$date_delais = $projet_prio['echeance_livraison'] - $projet_prio['delai_cmd'] - ((24*60*60)*5);
			if($i < $max){
				$projet = new projet($projet_prio['projetid']);
				$datecmd=$projet->datecmd();
				if(!is_null($datecmd)){
					$datecmdcolor=$datecmd<new datetime('today 0:0:0')?"red":"";
					$datecmd=$datecmd->format("d/m/Y");
				}
				if($date_delais <= time()){
					$nom=($projet_prio['raisonsociale']!='' ? $projet_prio['raisonsociale'] : $projet_prio['nom']." ".$projet_prio['prenom']);
					if(!is_null($projet_prio['echeance_type'])){
						if($projet_prio['echeance_type'] == '1'){
							$type_ech = " - <div title='info donné par le client' style='display:inline;'>C</div>";
						}
						else{
							$type_ech = " - <div title='estimé par mcav' style='display:inline;'>M</div>";
						}
					}
					$liste_prio .= "<div style='height:21px;overflow-y:hidden;padding:1px;' onmouseover='affich_info(this)' onmouseout='affich_info(this,\"out\")'><a href='projet.php?projetid=".$projet_prio['projetid']."' target='_blank' ".($projet_prio['raisonsociale']!='' ? "style='color:#0393C2;font-weight:bold'" : "").">".trim(substr($nom,0,18)). "...</a> - ".$projet_prio['budget']."€ - <div style='color:".$datecmdcolor.";display:inline;background-color:transparent;'>".$datecmd."</div>".$type_ech."
					<span style='display:none;position: absolute; padding: 3px; border: 2px solid black; border-top: none;min-width:200px;margin-left:-3px;'>".$projet->pjt." - ".$projet->etat." - ".$projet->libelle."</span></div>";
				}
			}
			if($date_delais <= time()){
				$nb_projets_prio++;
			}
			$i++;
		}
	}

	//Suivi interne sur 3 jours (jour même et 2 jours avant)
	$jour_meme = mktime(23,59,59,date('m'),date('d'),date('Y'));
	//si on est lundi ou mardi, on affiche les rappels de jeudi et vendredi, ou vendredi :
	if(date('w') == '1' || date('w') == '2'){
		$deuxj_av = mktime(0,0,0,date('m'),date('d'),date('Y')) - ((24*60*60) * 4);
	}
	else{
		$deuxj_av = mktime(0,0,0,date('m'),date('d'),date('Y')) - ((24*60*60) * 2);
	}

	$suivis_int = $DB_site2->query("SELECT p.*, u.nom, u.raisonsociale, u.prenom FROM projet p INNER JOIN utilisateur u ON u.userid = p.userid WHERE p.rappel_commercial IN ('".$sql_comm."') AND p.etatid IN ('1','2','3','4','7','8') AND rappel BETWEEN ".$deuxj_av." AND ".$jour_meme . " AND rappel_type != 'rdv' ".(!is_null($_GET['tri_suivi']) ? $_GET['tri_suivi'] : "ORDER BY p.rappel"));
	$nb_suivi_int = $DB_site2->num_rows($suivis_int);

	if(empty($_GET['tri_suivi']) || $_GET['tri_suivi'] == 'ORDER BY p.budget DESC' || $_GET['tri_suivi'] == 'ORDER BY p.echeance_livraison DESC, p.delai_cmd DESC'){
		$budget_tri = 'ORDER BY p.budget';
		$date_tri = 'ORDER BY p.echeance_livraison, p.delai_cmd';
		$lib_budg_tri = 'ASC';
		$lib_date_tri = 'ASC';
	}
	elseif($_GET['tri_suivi'] == 'ORDER BY p.budget'){
		$budget_tri = 'ORDER BY p.budget DESC';
		$date_tri = 'ORDER BY p.echeance_livraison, p.delai_cmd';
		$lib_budg_tri = 'DESC';
		$lib_date_tri = 'ASC';
	}
	elseif($_GET['tri_suivi'] == 'ORDER BY p.echeance_livraison, p.delai_cmd'){
		$budget_tri = 'ORDER BY p.budget';
		$date_tri = 'ORDER BY p.echeance_livraison DESC, p.delai_cmd DESC';
		$lib_budg_tri = 'ASC';
		$lib_date_tri = 'DESC';
	}

	if($DB_site2->num_rows($suivis_int) > 0){
		$i = 1;
		$max = isset($_GET['suiviint']) ? $_GET['suiviint'] + 1 : 11;
		$liste_suivi_int .= "<div style='height:21px;overflow-y:hidden;padding:1px;text-align:center;'><a href='projets_new.php?commercial=".$_GET['commercial']."&tri_suivi=".$budget_tri."'>Trier budget ".$lib_budg_tri."</a> - <a href='projets_new.php?commercial=".$_GET['commercial']."&tri_suivi=".$date_tri."'>Trier date délai ".$lib_date_tri."</a></div>";
		while($suivi_int=$DB_site2->fetch_array($suivis_int)){
			$type_ech = "";
			if($i < $max){
				$projet = new projet($suivi_int['projetid']);
				$datecmd=$projet->datecmd();
				if(!is_null($datecmd)){
					$datecmdcolor=$datecmd<new datetime('today 0:0:0')?"red":"";
					$datecmd=$datecmd->format("d/m/Y");
				}
				if(!is_null($suivi_int['echeance_type'])){
					if($suivi_int['echeance_type'] == '1'){
						$type_ech = " - <div title='info donné par le client' style='display:inline;'>C</div>";
					}
					else{
						$type_ech = " - <div title='estimé par mcav' style='display:inline;'>M</div>";
					}
				}
				$nom=($suivi_int['raisonsociale']!='' ? $suivi_int['raisonsociale'] : $suivi_int['nom']." ".$suivi_int['prenom']);
				$liste_suivi_int .= "<div style='height:21px;overflow-y:hidden;padding:1px;' onmouseover='affich_info(this)' onmouseout='affich_info(this,\"out\")'>".date('d/m', $suivi_int['rappel'])." - <a href='projet.php?projetid=".$suivi_int['projetid']."' target='_blank' ".($suivi_int['raisonsociale']!='' ? "style='color:#0393C2;font-weight:bold'" : "").">".substr($nom,0,13)."..</a> - ".$suivi_int['budget']."€ - ".$datecmd.$type_ech."
				<span style='display:none;position: absolute; padding: 3px; border: 2px solid black; border-top: none;min-width:200px;margin-left:-3px;'>".$projet->pjt." - ".$projet->etat." - ".$projet->libelle."</span></div>";
				$i++;
			}
		}
	}

	//Rappels immédiat
	$sql = "SELECT * FROM contact WHERE date_reponse=0 AND sujet = 'Rappel immédiat' AND commercial IN ('".$sql_comm."') ORDER BY date";
	$requete = $DB_site2->query($sql);
	$nb_rapp_im = $DB_site2->num_rows($requete);
	if($DB_site2->num_rows($requete)>0){
		$i = 1;
		$max = isset($_GET['rapim']) ? $_GET['rapim'] + 1 : 6;
		while($contact=$DB_site2->fetch_array($requete)){
			if($i < $max){
				$date=date("d/m H:i",$contact['date']);
				$nom=trim($contact['raisonsociale']." ".$contact['nom']." ".$contact['prenom']);
				$liste_rapp_im.="
				<div style='height:21px;overflow-y:hidden;padding:1px;'>".$date." -
				<a href='contacts.php?contactid=".$contact['id']."' target='_blank'>".$nom."</a>
				</div>";
				$i++;
			}
		}
	}


	//Contacts form
	$sql = "SELECT * FROM contact WHERE date_reponse=0 AND sujet != 'Rappel immédiat' AND commercial IN ('".$sql_comm."') ORDER BY date";
	$requete = $DB_site2->query($sql);
	$nb_form = $DB_site2->num_rows($requete);
	if($DB_site2->num_rows($requete)>0){
		$i = 1;
		$max = isset($_GET['form']) ? $_GET['form'] + 1 : 6;
		while($contact=$DB_site2->fetch_array($requete)){
			if($i < $max){
				$date=date("d/m H:i",$contact['date']);
				$nom=trim($contact['raisonsociale']." ".$contact['nom']." ".$contact['prenom']);
				$liste_form.="
				<div style='height:21px;overflow-y:hidden;padding:1px;' onmouseover='affich_info(this)' onmouseout='affich_info(this,\"out\")'>".$date." -
				<a href='contacts.php?contactid=".$contact['id']."' target='_blank'>".$nom."</a>
				<span style='display:none;position: absolute; padding: 3px; border: 2px solid black; border-top: none;min-width:200px;margin-left:-3px;'>".$contact['sujet']."</span>
				</div>";
				$i++;
			}
		}
	}

	//Contacts form pro
	$sql = "SELECT * FROM contact_leads WHERE date_reponse=0 AND commercial IN ('".$sql_comm."') ORDER BY date";
	$requete = $DB_site2->query($sql);
	$nb_form_pro = $DB_site2->num_rows($requete);
	if($DB_site2->num_rows($requete)>0){
		$i = 1;
		$max = isset($_GET['formpro']) ? $_GET['formpro'] + 1 : 6;
		while($contact=$DB_site2->fetch_array($requete)){
			if($i < $max){
				$date=date("d/m H:i",$contact['date']);
				$nom=trim($contact['raisonsociale']." ".$contact['nom']." ".$contact['prenom']);
				$liste_form_pro.="
				<div style='height:21px;overflow-y:hidden;padding:1px;' onmouseover='affich_info(this)' onmouseout='affich_info(this,\"out\")'>".$date." -
				<a href='contacts_leads.php?contactid=".$contact['id']."' target='_blank'>".$nom."</a>
				<span style='display:none;position: absolute; padding: 3px; border: 2px solid black; border-top: none;min-width:200px;margin-left:-3px;'>".$contact['message']."</span>
				</div>";
				$i++;
			}
		}
	}


	//A chiffrer
	$a_chiffrer = $DB_site2->query("SELECT p.*, u.nom, u.raisonsociale, u.prenom FROM projet p INNER JOIN utilisateur u ON u.userid = p.userid WHERE p.commercial IN ('".$sql_comm."') AND etatid = 3 ".(!is_null($_GET['tri_chiff']) ? $_GET['tri_chiff'] : "ORDER BY p.echeance_livraison"));
	$nb_a_chiffrer = $DB_site2->num_rows($a_chiffrer);

	if(empty($_GET['tri_chiff']) || $_GET['tri_chiff'] == 'ORDER BY p.budget DESC' || $_GET['tri_chiff'] == 'ORDER BY p.echeance_livraison DESC, p.delai_cmd DESC' || $_GET['tri_chiff'] == 'ORDER BY p.dateline DESC'){
		$budget_tri = 'ORDER BY p.budget';
		$date_tri = 'ORDER BY p.echeance_livraison, p.delai_cmd';
		$date_crea_tri = 'ORDER BY p.dateline';
		$lib_budg_tri = 'ASC';
		$lib_date_tri = 'ASC';
		$lib_date_crea_tri = 'ASC';
	}
	elseif($_GET['tri_chiff'] == 'ORDER BY p.budget'){
		$budget_tri = 'ORDER BY p.budget DESC';
		$date_tri = 'ORDER BY p.echeance_livraison, p.delai_cmd';
		$date_crea_tri = 'ORDER BY p.dateline';
		$lib_budg_tri = 'DESC';
		$lib_date_tri = 'ASC';
		$lib_date_crea_tri = 'ASC';
	}
	elseif($_GET['tri_chiff'] == 'ORDER BY p.echeance_livraison, p.delai_cmd'){
		$budget_tri = 'ORDER BY p.budget';
		$date_crea_tri = 'ORDER BY p.dateline';
		$date_tri = 'ORDER BY p.echeance_livraison DESC, p.delai_cmd DESC';
		$lib_budg_tri = 'ASC';
		$lib_date_crea_tri = 'ASC';
		$lib_date_tri = 'DESC';
	}
	elseif($_GET['tri_chiff'] == 'ORDER BY p.dateline'){
		$budget_tri = 'ORDER BY p.budget';
		$date_crea_tri = 'ORDER BY p.dateline DESC';
		$date_tri = 'ORDER BY p.echeance_livraison, p.delai_cmd';
		$lib_budg_tri = 'ASC';
		$lib_date_crea_tri = 'DESC';
		$lib_date_tri = 'ASC';
	}

	if($DB_site2->num_rows($a_chiffrer) > 0){
		$i = 1;
		$max = isset($_GET['chiff']) ? $_GET['chiff'] + 1 : 11;
		$liste_a_chiffrer .= "<div style='padding:1px;text-align:center;'><a href='projets_new.php?commercial=".$_GET['commercial']."&tri_chiff=".$budget_tri."'>Trier budget ".$lib_budg_tri."</a> - <a href='projets_new.php?commercial=".$_GET['commercial']."&tri_chiff=".$date_tri."'>Trier date délai ".$lib_date_tri."</a> - <a href='projets_new.php?commercial=".$_GET['commercial']."&tri_chiff=".$date_crea_tri."'>Trier date création ".$lib_date_crea_tri."</a></div>";
		while($chiffrer=$DB_site2->fetch_array($a_chiffrer)){
			$type_ech = "";
			if($i < $max){
				$projet = new projet($chiffrer['projetid']);
				$datecmd=$projet->datecmd();
				if(!is_null($datecmd)){
					$datecmdcolor=$datecmd<new datetime('today 0:0:0')?"red":"";
					$datecmd=$datecmd->format("d/m/Y");
				}
				if(!is_null($chiffrer['echeance_type'])){
					if($chiffrer['echeance_type'] == '1'){
						$type_ech = " - <div title='info donné par le client' style='display:inline;'>C</div>";
					}
					else{
						$type_ech = " - <div title='estimé par mcav' style='display:inline;'>M</div>";
					}
				}
				$nom=($chiffrer['raisonsociale']!='' ? $chiffrer['raisonsociale'] : $chiffrer['nom']." ".$chiffrer['prenom']);
				$liste_a_chiffrer .= "<div style='height:21px;overflow-y:hidden;padding:1px;' onmouseover='affich_info(this)' onmouseout='affich_info(this,\"out\")'>".date('d/m', $chiffrer['rappel'])." - <a href='projet.php?projetid=".$chiffrer['projetid']."' target='_blank' ".($chiffrer['raisonsociale']!='' ? "style='color:#0393C2;font-weight:bold'" : "").">".substr($nom,0,13)."..</a> - ".$chiffrer['budget']."€ - ".$datecmd.$type_ech."
				<span style='display:none;position: absolute; padding: 3px; border: 2px solid black; border-top: none;min-width:200px;margin-left:-3px;'>".$projet->pjt." - ".$projet->etat." - ".$projet->libelle."</span></div>";
				$i++;
			}
		}
	}

	//Rappel commande
	$rappels_cmd = $DB_site2->query("SELECT sc.*, u.nom, u.raisonsociale, u.prenom FROM suivi_commande sc INNER JOIN facture f ON f.facturid = sc.facturid INNER JOIN utilisateur u ON u.userid = f.userid WHERE pour IN ('".$sql_comm."') AND hide = 0 AND rappel > 0 AND rappel < ".mktime(23,59,59)." ORDER BY date DESC");

	$nb_rappel_cmd = $DB_site2->num_rows($rappels_cmd);
	if($DB_site2->num_rows($rappels_cmd) > 0){
		$i = 1;
		$max = isset($_GET['cde']) ? $_GET['cde'] + 1 : 11;
		while($rappel_cmd=$DB_site2->fetch_array($rappels_cmd)){
			$type_ech = "";
			if($i < $max){
				$nom=($rappel_cmd['raisonsociale']!='' ? $rappel_cmd['raisonsociale'] : $rappel_cmd['nom']." ".$rappel_cmd['prenom']);
				$liste_rappel_cmd .= "<div style='height:21px;padding:1px;overflow-y:auto;' onmouseover='affich_info(this)' onmouseout='affich_info(this,\"out\")'><a href='projets_new.php?commercial=".$_GET['commercial']."&delete=".$rappel_cmd['facturid']."' style='color:red;cursor:pointer;text-decoration:none;'>X</a> ".date('d/m', $rappel_cmd['rappel'])." - <a href='facture.php?action=editer&facturid=".$rappel_cmd['facturid']."' target='_blank' ".($rappel_cmd['raisonsociale']!='' ? "style='color:#0393C2;font-weight:bold'" : "").">".$rappel_cmd['facturid']. " - ".substr($nom,0,18). "</a>
				<span style='display:none;position: absolute; padding: 3px; border: 2px solid black; border-top: none;min-width:200px;margin-left:-3px;'>".stripslashes($rappel_cmd['suivi'])."</span>
				</div>";
				$i++;
			}
		}
	}

	//RDV en retard
	$rdvs_retard = $DB_site2->query("SELECT p.*, u.nom, u.raisonsociale, u.prenom FROM projet p INNER JOIN utilisateur u ON u.userid = p.userid WHERE p.rappel_commercial IN ('".$sql_comm."') AND rappel_type = 'rdv' AND etatid IN ('1','2','3','4','7','8') AND rappel BETWEEN ".intval($fromdate->gettimestamp())." AND ".mktime('00','00','00',date('m'),date('d'),date('Y')));
	$nb_rdv_retard = $DB_site2->num_rows($rdvs_retard);
	if($DB_site2->num_rows($rdvs_retard) > 0){
		$i = 1;
		while($rdv_retard=$DB_site2->fetch_array($rdvs_retard)){
			$type_ech = "";
			if($i < 11){
				$projet = new projet($rdv_retard['projetid']);
				$datecmd=$projet->datecmd();
				if(!is_null($datecmd)){
					$datecmdcolor=$datecmd<new datetime('today 0:0:0')?"red":"";
					$datecmd=$datecmd->format("d/m/Y");
				}
				if(!is_null($rdv_retard['echeance_type'])){
					if($rdv_retard['echeance_type'] == '1'){
						$type_ech = " - <div title='info donné par le client' style='display:inline;'>C</div>";
					}
					else{
						$type_ech = " - <div title='estimé par mcav' style='display:inline;'>M</div>";
					}
				}
				$nom=($rdv_retard['raisonsociale']!='' ? $rdv_retard['raisonsociale'] : $rdv_retard['nom']." ".$rdv_retard['prenom']);
				$liste_rdv_retard .= "<div style='height:21px;overflow-y:hidden;padding:1px;' onmouseover='affich_info(this)' onmouseout='affich_info(this,\"out\")'>".date('d/m H:i', $rdv_retard['rappel'])." - <a href='projet.php?projetid=".$rdv_retard['projetid']."' target='_blank' ".($rdv_retard['raisonsociale']!='' ? "style='color:#0393C2;font-weight:bold'" : "").">".substr($nom,0,11). "...</a> - ".$rdv_retard['budget']."€ - ".$datecmd."
				<span style='display:none;position: absolute; padding: 3px; border: 2px solid black; border-top: none;min-width:200px;margin-left:-3px;'>".$projet->pjt." - ".$projet->etat." - ".$projet->libelle."</span></div>";
				$i++;
			}
		}
	}

	//Suivi en retard
	$suivis_int_retard = $DB_site2->query("SELECT p.*, u.nom, u.raisonsociale, u.prenom FROM projet p INNER JOIN utilisateur u ON u.userid = p.userid WHERE p.rappel_commercial IN ('".$sql_comm."') AND rappel_type != 'rdv' AND etatid IN ('1','2','3','4','7','8') AND rappel BETWEEN ".intval($fromdate->gettimestamp())." AND ".$deuxj_av." ".(isset($_GET['tri_suiviret']) ? $_GET['tri_suiviret'] : ' ORDER BY p.rappel'));
	$nb_suivi_int_retard = $DB_site2->num_rows($suivis_int_retard);

	//trier par budget ou date
	if(empty($_GET['tri_suiviret']) || $_GET['tri_suiviret'] == 'ORDER BY p.budget DESC' || $_GET['tri_suiviret'] == 'ORDER BY p.echeance_livraison DESC, p.delai_cmd DESC'){
		$budget_tri = 'ORDER BY p.budget';
		$date_tri = 'ORDER BY p.echeance_livraison, p.delai_cmd';
		$lib_budg_tri = 'ASC';
		$lib_date_tri = 'ASC';
	}
	elseif($_GET['tri_suiviret'] == 'ORDER BY p.budget'){
		$budget_tri = 'ORDER BY p.budget DESC';
		$date_tri = 'ORDER BY p.echeance_livraison, p.delai_cmd';
		$lib_budg_tri = 'DESC';
		$lib_date_tri = 'ASC';
	}
	elseif($_GET['tri_suiviret'] == 'ORDER BY p.echeance_livraison, p.delai_cmd'){
		$budget_tri = 'ORDER BY p.budget';
		$date_tri = 'ORDER BY p.echeance_livraison DESC, p.delai_cmd DESC';
		$lib_budg_tri = 'ASC';
		$lib_date_tri = 'DESC';
	}

	if($DB_site2->num_rows($suivis_int_retard) > 0){
		$i = 1;
		$max = isset($_GET['suiviretard']) ? $_GET['suiviretard'] + 1 : 11;
		$liste_suivi_int_retard .= "<div style='height:21px;overflow-y:hidden;padding:1px;text-align:center;'><a href='projets_new.php?commercial=".$_GET['commercial']."&tri_suiviret=".$budget_tri."'>Trier budget ".$lib_budg_tri."</a> - <a href='projets_new.php?commercial=".$_GET['commercial']."&tri_suiviret=".$date_tri."'>Trier date délai ".$lib_date_tri."</a></div>";
		while($suivi_int_retard=$DB_site2->fetch_array($suivis_int_retard)){
			$type_ech = "";
			if($i < $max){
				$projet = new projet($suivi_int_retard['projetid']);
				$datecmd=$projet->datecmd();
				if(!is_null($datecmd)){
					$datecmdcolor=$datecmd<new datetime('today 0:0:0')?"red":"";
					$datecmd=$datecmd->format("d/m/Y");
				}
				if(!is_null($suivi_int_retard['echeance_type'])){
					if($suivi_int_retard['echeance_type'] == '1'){
						$type_ech = " - <div title='info donné par le client' style='display:inline;'>C</div>";
					}
					else{
						$type_ech = " - <div title='estimé par mcav' style='display:inline;'>M</div>";
					}
				}
				$nom=($suivi_int_retard['raisonsociale']!='' ? $suivi_int_retard['raisonsociale'] : $suivi_int_retard['nom']." ".$suivi_int_retard['prenom']);
				$liste_suivi_int_retard .= "<div style='height:21px;overflow-y:hidden;padding:1px;' onmouseover='affich_info(this)' onmouseout='affich_info(this,\"out\")'>".date('d/m', $suivi_int_retard['rappel'])." - <a href='projet.php?projetid=".$suivi_int_retard['projetid']."' target='_blank' ".($suivi_int_retard['raisonsociale']!='' ? "style='color:#0393C2;font-weight:bold'" : "").">".substr($nom,0,13)."..</a> - ".$suivi_int_retard['budget']."€ - ".$datecmd.$type_ech."
				<span style='display:none;position: absolute; padding: 3px; border: 2px solid black; border-top: none;min-width:200px;margin-left:-3px;'>".$projet->pjt." - ".$projet->etat." - ".$projet->libelle."</span></div>";
				$i++;
			}
		}
	}

	//Projets sans rappel
	$projets_ss_rappel = $DB_site2->query("SELECT p.*, u.nom, u.raisonsociale, u.prenom FROM projet p INNER JOIN utilisateur u ON u.userid = p.userid WHERE p.commercial IN ('".$sql_comm."') AND rappel = 0 AND etatid IN ('1','2','3','4','7','8') ".(isset($_GET['tri_ssrap']) ? $_GET['tri_ssrap'] : ''));
	$nb_projets_ss_rappel = $DB_site2->num_rows($projets_ss_rappel);

	if(empty($_GET['tri_ssrap']) || $_GET['tri_ssrap'] == 'ORDER BY p.budget DESC' || $_GET['tri_ssrap'] == 'ORDER BY p.echeance_livraison DESC, p.delai_cmd DESC'){
		$budget_tri = 'ORDER BY p.budget';
		$date_tri = 'ORDER BY p.echeance_livraison, p.delai_cmd';
		$lib_budg_tri = 'ASC';
		$lib_date_tri = 'ASC';
	}
	elseif($_GET['tri_ssrap'] == 'ORDER BY p.budget'){
		$budget_tri = 'ORDER BY p.budget DESC';
		$date_tri = 'ORDER BY p.echeance_livraison, p.delai_cmd';
		$lib_budg_tri = 'DESC';
		$lib_date_tri = 'ASC';
	}
	elseif($_GET['tri_ssrap'] == 'ORDER BY p.echeance_livraison, p.delai_cmd'){
		$budget_tri = 'ORDER BY p.budget';
		$date_tri = 'ORDER BY p.echeance_livraison DESC, p.delai_cmd DESC';
		$lib_budg_tri = 'ASC';
		$lib_date_tri = 'DESC';
	}

	if($DB_site2->num_rows($projets_ss_rappel) > 0){
		$i = 1;
		$max = isset($_GET['ssrap']) ? $_GET['ssrap'] + 1 : 11;
		$liste_projet_ss_rappel .= "<div style='height:21px;overflow-y:hidden;padding:1px;text-align:center;'><a href='projets_new.php?commercial=".$_GET['commercial']."&tri_ssrap=".$budget_tri."'>Trier budget ".$lib_budg_tri."</a> - <a href='projets_new.php?commercial=".$_GET['commercial']."&tri_ssrap=".$date_tri."'>Trier date délai ".$lib_date_tri."</a></div>";
		while($projet_ss_rappel=$DB_site2->fetch_array($projets_ss_rappel)){
			$type_ech = "";
			if($i < $max){
				$projet = new projet($projet_ss_rappel['projetid']);
				$datecmd=$projet->datecmd();
				if(!is_null($datecmd)){
					$datecmdcolor=$datecmd<new datetime('today 0:0:0')?"red":"";
					$datecmd=$datecmd->format("d/m/Y");
				}
				if(!is_null($projet_ss_rappel['echeance_type'])){
					if($projet_ss_rappel['echeance_type'] == '1'){
						$type_ech = " - <div title='info donné par le client' style='display:inline;'>C</div>";
					}
					else{
						$type_ech = " - <div title='estimé par mcav' style='display:inline;'>M</div>";
					}
				}
				$nom=($projet_ss_rappel['raisonsociale']!='' ? $projet_ss_rappel['raisonsociale'] : $projet_ss_rappel['nom']." ".$projet_ss_rappel['prenom']);
				$liste_projet_ss_rappel .= "<div style='height:21px;overflow-y:hidden;padding:1px;' onmouseover='affich_info(this)' onmouseout='affich_info(this,\"out\")'><a href='projet.php?projetid=".$projet_ss_rappel['projetid']."' target='_blank' ".($projet_ss_rappel['raisonsociale']!='' ? "style='color:#0393C2;font-weight:bold'" : "").">".substr($nom,0,18)."..</a> - ".$projet_ss_rappel['budget']."€ - ".$datecmd.$type_ech."
				<span style='display:none;position: absolute; padding: 3px; border: 2px solid black; border-top: none;min-width:200px;margin-left:-3px;'>#PJT".$projet_ss_rappel['projetid']."# - ".$projet->etat." - ".$projet->libelle."</span></div>";
				$i++;
			}
		}
	}

	//Attente info client
	$infos_client = $DB_site2->query("SELECT p.*, u.nom, u.raisonsociale, u.prenom FROM projet p INNER JOIN utilisateur u ON u.userid = p.userid WHERE p.commercial IN ('".$sql_comm."') AND etatid = 2 ".(isset($_GET['tri_info']) ? $_GET['tri_info'] : ''));
	$nb_infos_client = $DB_site2->num_rows($infos_client);

	if(empty($_GET['tri_info']) || $_GET['tri_info'] == 'ORDER BY p.budget DESC' || $_GET['tri_chiff'] == 'ORDER BY p.echeance_livraison DESC, p.delai_cmd DESC'){
		$budget_tri = 'ORDER BY p.budget';
		$date_tri = 'ORDER BY p.echeance_livraison, p.delai_cmd';
		$lib_budg_tri = 'ASC';
		$lib_date_tri = 'ASC';
	}
	elseif($_GET['tri_info'] == 'ORDER BY p.budget'){
		$budget_tri = 'ORDER BY p.budget DESC';
		$date_tri = 'ORDER BY p.echeance_livraison, p.delai_cmd';
		$lib_budg_tri = 'DESC';
		$lib_date_tri = 'ASC';
	}
	elseif($_GET['tri_info'] == 'ORDER BY p.echeance_livraison, p.delai_cmd'){
		$budget_tri = 'ORDER BY p.budget';
		$date_tri = 'ORDER BY p.echeance_livraison DESC, p.delai_cmd DESC';
		$lib_budg_tri = 'ASC';
		$lib_date_tri = 'DESC';
	}

	if($DB_site2->num_rows($infos_client) > 0){
		$i = 1;
		$max = isset($_GET['info']) ? $_GET['info'] + 1 : 11;
		$liste_info_client .= "<div style='height:21px;overflow-y:hidden;padding:1px;text-align:center;' ><a href='projets_new.php?commercial=".$_GET['commercial']."&tri_info=".$budget_tri."'>Trier budget ".$lib_budg_tri."</a> - <a href='projets_new.php?commercial=".$_GET['commercial']."&tri_info=".$date_tri."'>Trier date délai ".$lib_date_tri."</a></div>";
		while($info_client=$DB_site2->fetch_array($infos_client)){
			$type_ech = "";
			if($i < $max){
				$projet = new projet($info_client['projetid']);
				$datecmd=$projet->datecmd();
				if(!is_null($datecmd)){
					$datecmdcolor=$datecmd<new datetime('today 0:0:0')?"red":"";
					$datecmd=$datecmd->format("d/m/Y");
				}
				if(!is_null($info_client['echeance_type'])){
					if($info_client['echeance_type'] == '1'){
						$type_ech = " - <div title='info donné par le client' style='display:inline;'>C</div>";
					}
					else{
						$type_ech = " - <div title='estimé par mcav' style='display:inline;'>M</div>";
					}
				}
				$nom=($info_client['raisonsociale']!='' ? $info_client['raisonsociale'] : $info_client['nom']." ".$info_client['prenom']);
				$liste_info_client .= "<div style='height:21px;overflow-y:hidden;padding:1px;' onmouseover='affich_info(this)' onmouseout='affich_info(this,\"out\")'><a href='projet.php?projetid=".$info_client['projetid']."' target='_blank' ".($info_client['raisonsociale']!='' ? "style='color:#0393C2;font-weight:bold'" : "").">".substr($nom,0,18). "...</a> - ".$info_client['budget']."€ - ".$datecmd.$type_ech."
				<span style='display:none;position: absolute; padding: 3px; border: 2px solid black; border-top: none;min-width:200px;margin-left:-3px;'>".$projet->pjt." - ".$projet->etat." - ".$projet->libelle."</span></div>";
				$i++;
			}
		}
	}

	//Manque échéance
	$echeances = $DB_site2->query("SELECT p.*, u.nom, u.raisonsociale, u.prenom FROM projet p INNER JOIN utilisateur u ON u.userid = p.userid WHERE p.commercial IN ('".$sql_comm."') AND etatid IN ('1','2','3','4','7','8') AND echeance_livraison = 0 ".(isset($_GET['tri_ech']) ? $_GET['tri_ech'] : ''));
	$nb_echeances = $DB_site2->num_rows($echeances);

	if(empty($_GET['tri_ech']) || $_GET['tri_ech'] == 'ORDER BY p.budget DESC' || $_GET['tri_ech'] == 'ORDER BY p.dateline DESC'){
		$budget_tri = 'ORDER BY p.budget';
		$date_tri = 'ORDER BY p.dateline';
		$lib_budg_tri = 'ASC';
		$lib_date_tri = 'ASC';
	}
	elseif($_GET['tri_ech'] == 'ORDER BY p.budget'){
		$budget_tri = 'ORDER BY p.budget DESC';
		$date_tri = 'ORDER BY p.dateline';
		$lib_budg_tri = 'DESC';
		$lib_date_tri = 'ASC';
	}
	elseif($_GET['tri_ech'] == 'ORDER BY p.dateline'){
		$budget_tri = 'ORDER BY p.budget';
		$date_tri = 'ORDER BY p.dateline DESC';
		$lib_budg_tri = 'ASC';
		$lib_date_tri = 'DESC';
	}

	if($DB_site2->num_rows($echeances) > 0){
		$i = 1;
		$max = isset($_GET['ech']) ? $_GET['ech'] + 1 : 11;
		$liste_echeance .= "<div style='height:21px;overflow-y:hidden;padding:1px;text-align:center;'><a href='projets_new.php?commercial=".$_GET['commercial']."&tri_ech=".$budget_tri."'>Trier budget ".$lib_budg_tri."</a> - <a href='projets_new.php?commercial=".$_GET['commercial']."&tri_ech=".$date_tri."'>Trier date délai ".$lib_date_tri."</a></div>";
		while($echeance=$DB_site2->fetch_array($echeances)){
			$type_ech = "";
			if($i < $max){
				$projet = new projet($echeance['projetid']);
				$nom=($echeance['raisonsociale']!='' ? $echeance['raisonsociale'] : $echeance['nom']." ".$echeance['prenom']);
				$liste_echeance .= "<div style='height:21px;overflow-y:hidden;padding:1px;' onmouseover='affich_info(this)' onmouseout='affich_info(this,\"out\")'><a href='projet.php?projetid=".$echeance['projetid']."' target='_blank' ".($echeance['raisonsociale']!='' ? "style='color:#0393C2;font-weight:bold'" : "").">".date('d/m/y', $echeance['dateline'])." - ".substr($nom,0,18). "...</a> - ".$echeance['budget']."€
				<span style='display:none;position: absolute; padding: 3px; border: 2px solid black; border-top: none;min-width:200px;margin-left:-3px;'>".$projet->pjt." - ".$projet->etat." - ".$projet->libelle."</span></div>";
				$i++;
			}
		}
	}

	//Manque budget
	$budgets = $DB_site2->query("SELECT p.*, u.nom, u.raisonsociale, u.prenom FROM projet p INNER JOIN utilisateur u ON u.userid = p.userid WHERE p.commercial IN ('".$sql_comm."') AND etatid IN ('1','2','3','4','7','8') AND budget = 0 ".(isset($_GET['tri_budg']) ? $_GET['tri_budg'] : ''));
	$nb_budgets = $DB_site2->num_rows($budgets);

	if(empty($_GET['tri_budg']) || $_GET['tri_budg'] == 'ORDER BY p.echeance_livraison DESC, p.delai_cmd DESC'){
		$date_tri = 'ORDER BY p.echeance_livraison, p.delai_cmd';
		$lib_date_tri = 'ASC';
	}
	elseif($_GET['tri_budg'] == 'ORDER BY p.dateline'){
		$date_tri = 'ORDER BY p.echeance_livraison DESC, p.delai_cmd DESC';
		$lib_date_tri = 'DESC';
	}

	if($DB_site2->num_rows($budgets) > 0){
		$i = 1;
		$max = isset($_GET['budg']) ? $_GET['budg'] + 1 : 11;
		$liste_budget .= "<div style='height:21px;overflow-y:hidden;padding:1px;text-align:center;'><a href='projets_new.php?commercial=".$_GET['commercial']."&tri_budg=".$date_tri."'>Trier date délai ".$lib_date_tri."</a></div>";
		while($budget=$DB_site2->fetch_array($budgets)){
			$type_ech = "";
			if($i < $max){
				$projet = new projet($budget['projetid']);
				$datecmd=$projet->datecmd();
				if(!is_null($datecmd)){
					$datecmdcolor=$datecmd<new datetime('today 0:0:0')?"red":"";
					$datecmd=$datecmd->format("d/m/Y");
				}
				if(!is_null($budget['echeance_type'])){
					if($budget['echeance_type'] == '1'){
						$type_ech = " - <div title='info donné par le client' style='display:inline;'>C</div>";
					}
					else{
						$type_ech = " - <div title='estimé par mcav' style='display:inline;'>M</div>";
					}
				}
				$nom=($budget['raisonsociale']!='' ? $budget['raisonsociale'] : $budget['nom']." ".$budget['prenom']);
				$liste_budget .= "<div style='height:21px;overflow-y:hidden;padding:1px;' onmouseover='affich_info(this)' onmouseout='affich_info(this,\"out\")'><a href='projet.php?projetid=".$budget['projetid']."' target='_blank' ".($budget['raisonsociale']!='' ? "style='color:#0393C2;font-weight:bold'" : "").">".substr($nom,0,18). "...</a> - ".$datecmd.$type_ech."
				<span style='display:none;position: absolute; padding: 3px; border: 2px solid black; border-top: none;min-width:200px;margin-left:-3px;'>".$projet->pjt." - ".$projet->etat." - ".$projet->libelle."</span></div>";
				$i++;
			}
		}
	}

	//Manque délai livraison
	$delaislivr = $DB_site2->query("SELECT p.*, u.nom, u.raisonsociale, u.prenom FROM projet p INNER JOIN utilisateur u ON u.userid = p.userid WHERE p.commercial IN ('".$sql_comm."') AND etatid IN ('1','2','3','4','7','8') AND delai_cmd = 0 ".(isset($_GET['tri_delailiv']) ? $_GET['tri_delailiv'] : ''));
	$nb_delaislivr= $DB_site2->num_rows($delaislivr);

	if(empty($_GET['tri_delailiv']) || $_GET['tri_delailiv'] == 'ORDER BY p.budget DESC' || $_GET['tri_delailiv'] == 'ORDER BY p.echeance_livraison DESC'){
		$budget_tri = 'ORDER BY p.budget';
		$date_tri = 'ORDER BY p.echeance_livraison';
		$lib_budg_tri = 'ASC';
		$lib_date_tri = 'ASC';
	}
	elseif($_GET['tri_delailiv'] == 'ORDER BY p.budget'){
		$budget_tri = 'ORDER BY p.budget DESC';
		$date_tri = 'ORDER BY p.echeance_livraison';
		$lib_budg_tri = 'DESC';
		$lib_date_tri = 'ASC';
	}
	elseif($_GET['tri_delailiv'] == 'ORDER BY p.echeance_livraison'){
		$budget_tri = 'ORDER BY p.budget';
		$date_tri = 'ORDER BY p.echeance_livraison DESC';
		$lib_budg_tri = 'ASC';
		$lib_date_tri = 'DESC';
	}

	if($DB_site2->num_rows($delaislivr) > 0){
		$i = 1;
		$max = isset($_GET['delailivr']) ? $_GET['delailivr'] + 1 : 11;
		$liste_delailivr .= "<div style='height:21px;overflow-y:hidden;padding:1px;text-align:center;'><a href='projets_new.php?commercial=".$_GET['commercial']."&tri_delailiv=".$budget_tri."'>Trier budget ".$lib_budg_tri."</a> - <a href='projets_new.php?commercial=".$_GET['commercial']."&tri_delailiv=".$date_tri."'>Trier date délai ".$lib_date_tri."</a></div>";
		while($delailivr=$DB_site2->fetch_array($delaislivr)){
			$type_ech = "";
			if($i < $max){
				$projet = new projet($delailivr['projetid']);
				$datecmd=$projet->datecmd();
				if(!is_null($datecmd)){
					$datecmdcolor=$datecmd<new datetime('today 0:0:0')?"red":"";
					$datecmd=$datecmd->format("d/m/Y");
				}
				if(!is_null($delailivr['echeance_type'])){
					if($delailivr['echeance_type'] == '1'){
						$type_ech = " - <div title='info donné par le client' style='display:inline;'>C</div>";
					}
					else{
						$type_ech = " - <div title='estimé par mcav' style='display:inline;'>M</div>";
					}
				}
				$nom=($delailivr['raisonsociale']!='' ? $delailivr['raisonsociale'] : $delailivr['nom']." ".$delailivr['prenom']);
				$liste_delailivr .= "<div style='height:21px;overflow-y:hidden;padding:1px;' onmouseover='affich_info(this)' onmouseout='affich_info(this,\"out\")'><a href='projet.php?projetid=".$delailivr['projetid']."' target='_blank' ".($delailivr['raisonsociale']!='' ? "style='color:#0393C2;font-weight:bold'" : "").">".substr($nom,0,18). "...</a> - ".$delailivr['budget']."€ - ".$datecmd.$type_ech."
				<span style='display:none;position: absolute; padding: 3px; border: 2px solid black; border-top: none;min-width:200px;margin-left:-3px;'>".$projet->pjt." - ".$projet->etat." - ".$projet->libelle."</span></div>";
				$i++;
			}
		}
	}

	//// TABLEAU DES OBJECTIFS COMMERCIAUX (même que dans stats_objectifs_commerciaux.php) ////
	if($_GET['commercial'] !== "tous"){
		$year=isset($_GET['year'])?intval($_GET['year']):date("Y");
		$username = $_GET['commercial'];

		$list=ca($year,null,$username);
		$end=null;
		if($year==date("Y")) $end=mktime(date("G"),0,0,date("n"),date("j"),$year-1);
		$list1=ca($year-1,$end,$username);

		$objectif_commercial=array();
		$objectifs_sql=$DB_site2->query(" SELECT * FROM objectif_commercial WHERE username='".$username."' AND annee='".$year."' ORDER BY mois ASC ");
		while($objectif=$DB_site2->fetch_array($objectifs_sql)){
			$objectif_commercial[$year][$objectif['mois']]=$objectif['objectif_ca'];
		}

		$obj_du_jour = $objectif_commercial[$year][date("n")];

		$tableau_objectifs_commerciaux = "<table class=\"ca\" style=\"margin-top:10px;\">
		<tr><td colspan=\"20\" class=\"ca_title\">Commandes ".UCfirst($username)."</td></tr>
		<tr>
		<td class=\"ca_head\">Mois</td>
		<td class=\"ca_head\">Objectif mensuel HT</td>
		<td class=\"ca_head\">Nbre jours travaillés</td>
		<td class=\"ca_head\">Objectif journalier (*)</td>
		<td class=\"ca_head\">CA HT (validé)</td>
		<td class=\"ca_head\">Cdes en attente de règlement</td>
		<td class=\"ca_head\">Total Cdes enregistrées</td>
		<td class=\"ca_head\">% objectif du mois</td>
		<td class=\"ca_head\">Av/ret / objectif &euro;HT</td>
		<td class=\"ca_head\">Av/ret / objectif %</td>
		<td class=\"ca_head\">Av/ret / N-1 &euro;HT</td>
		<td class=\"ca_head\">% N-1</td>
		<td class=\"ca_head\">Nbre cdes</td>
		<td class=\"ca_head\">% N-1</td>
		<td class=\"ca_head\">Panier moyen</td>
		<td class=\"ca_head\">% N-1</td>
		<td class=\"ca_head\">Marge prev. HT</td>
		<td class=\"ca_head\">Marge prev. %</td>
		<td class=\"ca_head\">Remise accordée HT</td>
		<td class=\"ca_head\">Remise %</td>
		</tr>";

		$nb_jour_total=0;
		$anencours=0;
		$objtotal=0;
		$trim=$trim_encours=$tt_du_trim=0;

		if(date("n") < 4){
				for($month=1;$month<4;$month++){
					$nb_jour_month=getJours($year."-".$month."-"."01",$year."-".$month."-".date("t", mktime(0,0,0,$month, 1, $year)),$username);
					if($month !== intval(date("n")) && $month < intval(date("n"))){
						$nb_jour_month_tt=getJours($year."-".$month."-"."01",$year."-".$month."-".date("t", mktime(0,0,0,$month, 1, $year)),$username);
						$trim_encours += $objectif_commercial[$year][$month]*$nb_jour_month_tt;
					}
					elseif($month == intval(date("n"))){
						$nb_jour_month_tt=getJours($year."-".$month."-"."01",$year."-".$month."-".date("j"),$username);
						$trim_encours += $objectif_commercial[$year][$month]*$nb_jour_month_tt;
					}
					$trim += $objectif_commercial[$year][$month]*$nb_jour_month;
					$tt_du_trim += $list['ca'][$month] + $list['attente'][$month];
				}
			}
			elseif(date("n") < 7){
				for($month=4;$month<7;$month++){
					$nb_jour_month=getJours($year."-".$month."-"."01",$year."-".$month."-".date("t", mktime(0,0,0,$month, 1, $year)),$username);
					if($month !== intval(date("n")) && $month < intval(date("n"))){
						$nb_jour_month_tt=getJours($year."-".$month."-"."01",$year."-".$month."-".date("t", mktime(0,0,0,$month, 1, $year)),$username);
						$trim_encours += $objectif_commercial[$year][$month]*$nb_jour_month_tt;
					}
					elseif($month == intval(date("n"))){
						$nb_jour_month_tt=getJours($year."-".$month."-"."01",$year."-".$month."-".date("j"),$username);
						$trim_encours += $objectif_commercial[$year][$month]*$nb_jour_month_tt;
					}
					$trim += $objectif_commercial[$year][$month]*$nb_jour_month;
					$tt_du_trim += $list['ca'][$month] + $list['attente'][$month];
				}
			}
			elseif(date("n") < 10){
				for($month=7;$month<10;$month++){
					$nb_jour_month=getJours($year."-".$month."-"."01",$year."-".$month."-".date("t", mktime(0,0,0,$month, 1, $year)),$username);
					if($month !== intval(date("n")) && $month < intval(date("n"))){
						$nb_jour_month_tt=getJours($year."-".$month."-"."01",$year."-".$month."-".date("t", mktime(0,0,0,$month, 1, $year)),$username);
						$trim_encours += $objectif_commercial[$year][$month]*$nb_jour_month_tt;
					}
					elseif($month == intval(date("n"))){
						$nb_jour_month_tt=getJours($year."-".$month."-"."01",$year."-".$month."-".date("j"),$username);
						$trim_encours += $objectif_commercial[$year][$month]*$nb_jour_month_tt;
					}
					$trim += $objectif_commercial[$year][$month]*$nb_jour_month;
					$tt_du_trim += $list['ca'][$month] + $list['attente'][$month];
				}
			}
			else{
				for($month=10;$month<13;$month++){
					$nb_jour_month=getJours($year."-".$month."-"."01",$year."-".$month."-".date("t", mktime(0,0,0,$month, 1, $year)),$username);
					if($month !== intval(date("n")) && $month < intval(date("n"))){
						$nb_jour_month_tt=getJours($year."-".$month."-"."01",$year."-".$month."-".date("t", mktime(0,0,0,$month, 1, $year)),$username);
						$trim_encours += $objectif_commercial[$year][$month]*$nb_jour_month_tt;
					}
					elseif($month == intval(date("n"))){
						$nb_jour_month_tt=getJours($year."-".$month."-"."01",$year."-".$month."-".date("j"),$username);
						$trim_encours += $objectif_commercial[$year][$month]*$nb_jour_month_tt;
					}
					$trim += $objectif_commercial[$year][$month]*$nb_jour_month;
					$tt_du_trim += $list['ca'][$month] + $list['attente'][$month];
				}
			}


		for($mois=1;$mois<=12;$mois++){
			$total=$list['ca'][$mois];
			$encours=$pcencours="";

			$nb_jour=getJours($year."-".$mois."-"."01",$year."-".$mois."-".date("t", mktime(0,0,0,$mois, 1, $year)),$username);
			$nb_jour_total+=$nb_jour;

			if($mois==date("n") && $year==date("Y")){

				$obj_du_mois = $objectif_commercial[$year][$mois]*$nb_jour;
				$tt_du_mois = $list['ca'][$mois] + $list['attente'][$mois];
				$tt_du_jour = $list['ca_jour'][$mois.date("d")] + $list['attente_jour'][$mois.date("d")];

				$jour_en_cours=getJours($year."-".$mois."-"."01",$year."-".$mois."-".date("j"),$username);
				
				$objencours=$jour_en_cours*$objectif_commercial[$year][$mois];
				$encours=$total-$objencours;
				$pcencours=$objencours>0?100*($encours/$objencours):0;
				$anencours+=$objencours;
				$totalencours += $encours;
				if($mois<=date('n')) {
					$objmois +=$objencours;
					$encoursmois += $encours;
				}
			}else{
				$encours=$total-($objectif_commercial[$year][$mois]*$nb_jour);
				$pcencours=$objectif_commercial[$year][$mois]>0?100*($encours/($objectif_commercial[$year][$mois]*$nb_jour)):0;
				$anencours+=$objectif_commercial[$year][$mois]*$nb_jour;
				$totalencours += $encours;
				if($mois<=date('n')) {
					$objmois+=$objectif_commercial[$year][$mois]*$nb_jour;
					$encoursmois += $encours;
				}
			}

			$nm1="";
			if($total>0){
				if($list1['ca'][$mois]>0){
					$nm1=100*($total-$list1['ca'][$mois])/$list1['ca'][$mois];
				} else {
					$nm1=0;
				}
			}

			$pcnb1="";
			if($list['nb'][$mois]>0){
				if($list1['nb'][$mois]>0){
					$pcnb1=100*($list['nb'][$mois]-$list1['nb'][$mois])/$list1['nb'][$mois];
				} else {
					$pcnb1=0;
				}
			}

			$panier=$panier1=$pcpanier1=0;
			if($list['nb'][$mois]>0){
				$panier=$total/$list['nb'][$mois];
				if($list1['nb'][$mois]>0){
					$panier1=$list1['ca'][$mois]/$list1['nb'][$mois];
					$pcpanier1=100*($panier-$panier1)/$panier1;
				} else {
					$panier1=0;
					$pcpanier1=0;
				}
			}

			if($total>0){
				$remise_pct=100*$list['remise'][$mois]/$total;
				$marge_pct=100*$list['marge'][$mois]/$total;
			} else {
				$remise_pct=0;
				$marge_pct=0;
			}

			$objtotal+=$objectif_commercial[$year][$mois]*$nb_jour;

			$tableau_objectifs_commerciaux .= "<tr>
			<td align=\"center\"><a href=\"facture.php?commercial_attribution=".$username."&fromdate=".mktime(0,0,0,$mois,1,$year)."&todate=".mktime(23,59,59,$mois,cal_days_in_month(CAL_GREGORIAN,$mois,$year),$year)."&lst_trietat[]=1&lst_trietat[]=5&lst_trietat[]=7&lst_trietat[]=8\" target=\"_blank\">".$mois."/".$year."</a></td>
			<td class=\"ca_nb\">".number_format($objectif_commercial[$year][$mois]*$nb_jour,0,',',' ')." &euro;</td>
			<td class=\"ca_nb\">".$nb_jour."</td>
			<td class=\"ca_nb\">".number_format($objectif_commercial[$year][$mois],0,',',' ')." &euro;</td>

			".tdvalue($total,"&euro;",null,number_format($objectif_commercial[$year][$mois]*$nb_jour,0,',',' '), "showpopup")."
			<div class=\"popup\"><table>".$list['factid'][$mois]."</table></div></td>
			<td class=\"ca_nb showpopup\">".number_format($list['attente'][$mois],0,',',' ')." &euro;
			<div class=\"popup\"><table>".$list['factid_attente'][$mois]."</table></div></td>
			<td class=\"ca_nb\">".number_format($total+$list['attente'][$mois],0,',',' ')." &euro;</td>
			".tdvalue(($objencours == 0 ? 0 : $objectif_commercial[$year][$mois]?100*$total/($mois==date("n")&&$year==date("Y")?$objencours:($objectif_commercial[$year][$mois]*$nb_jour)):0),"%",false,100)
			.($mois<=date('n')||$year!=date("Y")?tdvalue($encours,"&euro;",true):"<td class=\"ca_nb\"></td>")  
			.($mois<=date('n')||$year!=date("Y")?tdvalue($pcencours,"%",true):"<td class=\"ca_nb\"></td>")
			.tdvalue($total-($list1['ca'][$mois]),"&euro;",true)
			.tdvalue($nm1,"%",true)
			."<td class=\"ca_nb\">".$list['nb'][$mois]."</td>"
			.tdvalue($pcnb1,"%",true)
			."<td class=\"ca_nb\">".number_format($panier,0,',',' ')." &euro;</td>"
			.tdvalue($pcpanier1,"%",true)
			."<td class=\"ca_nb\">".number_format($list['marge'][$mois],0,',',' ')." &euro;</td>
			<td class=\"ca_nb\">".number_format($marge_pct,1,',',' ')." %</td>
			<td class=\"ca_nb\">".number_format($list['remise'][$mois],0,',',' ')." &euro;</td>
			<td class=\"ca_nb\">".number_format($remise_pct,1,',',' ')." %</td>
			</tr>";
		}


		$total=array_sum($list['ca']);
		$total1=array_sum($list1['ca']);
		$nb=array_sum($list['nb']);
		$nb1=array_sum($list1['nb']);
		$remise=array_sum($list['remise']);
		$marge=array_sum($list['marge']);
		$attente=array_sum($list['attente']);
		$panier=0;

		if($nb>0){
			$panier=$total/$nb;
		} else {
			$panier=0;
		}
		if($nb1>0){
			$panier1=$total1/$nb1;
			$nb_pct=100*($nb-$nb1)/$nb1;
		} else {
			$panier1=0;
			$nb_pct=0;
		}
		if($total1>0){
			$total_pct=100*($total-$total1)/$total1;
		} else {
			$total_pct=0;
		}
		if($panier1>0){
			$panier_pct=100*($panier-$panier1)/$panier1;
		} else {
			$panier_pct=0;
		}

		$tableau_objectifs_commerciaux .= "<tr style=\"font-weight:bold;\">
		<td align=\"right\">Total</td>
		<td class=\"ca_nb\">".number_format($objtotal,0,',',' ')." &euro;</td>
		<td class=\"ca_nb\">".$nb_jour_total."</td>
		<td class=\"ca_nb\">".@number_format($objtotal/$nb_jour_total,0,',',' ')." &euro;</td>"
		.tdvalue($total,"&euro;",false,$anencours)
		."<td class=\"ca_nb\">".number_format($attente,0,',',' ')." &euro;</td>
		<td class=\"ca_nb\">".number_format($attente+$total,0,',',' ')." &euro;</td>"
		.($mois==12||$year!=date("Y")?tdvalue($anencours>0?100*$total/$anencours:0,"%",false,100):tdvalue($anencours>0?100*$total/$objmois:0,"%",false,100))
		.($mois==12||$year!=date("Y")?tdvalue($total-$anencours,"&euro;",true):tdvalue($encoursmois,"&euro;",true))
		.($mois==12||$year!=date("Y")?tdvalue($anencours>0?100*($total-$anencours)/$anencours:0,"%",true):tdvalue($anencours>0?100*$encoursmois/$objmois:0,"%",true))
		.tdvalue($total-$total1,"&euro;",true)
		.tdvalue($total_pct,"%",true)
		."<td class=\"ca_nb\">".$nb."</td>"
		.tdvalue($nb_pct,"%",true)
		."<td class=\"ca_nb\">".number_format($panier,0,',',' ')." &euro;</td>"
		.tdvalue($panier_pct,"%",true)
		."<td class=\"ca_nb\">".number_format($marge,0,',',' ')." &euro;</td>
		<td class=\"ca_nb\">".@number_format(100*$marge/$total,1,',',' ')." %</td>
		<td class=\"ca_nb\">".number_format($remise,0,',',' ')." &euro;</td>
		<td class=\"ca_nb\">".@number_format(100*$remise/$total,1,',',' ')." %</td>
		</tr></table>";
	}

	if(intval($obj_du_jour) !== 0){
		if($tt_du_jour < $obj_du_jour) {
			$diff_jour = "<span style='color:red;'>".number_format(($tt_du_jour - $obj_du_jour),0,',',' ')." €</span>";
			$pct_jour = "<span style='color:red;'>".number_format(($tt_du_jour - $obj_du_jour) * 100 / intval($obj_du_jour))." %</span>";

		}else{
			$diff_jour = "<span style='color:green;'>+".number_format(($tt_du_jour - $obj_du_jour),0,',',' ')." €</span>";
			$pct_jour = "<span style='color:green;'>+".number_format(($tt_du_jour - $obj_du_jour) * 100 / intval($obj_du_jour))." %</span>";
		}
	}
	if(intval($objencours) !== 0){
		if($tt_du_mois < $objencours) {
			$diff_mois = "<span style='color:red;'>".number_format(($tt_du_mois - $objencours),0,',',' ')." €</span>";
			$pct_mois = "<span style='color:red;'>".number_format(($tt_du_mois - $objencours) * 100 / intval($objencours))." %</span>";
		}else{
			$diff_mois = "<span style='color:green;'>+".number_format(($tt_du_mois - $objencours),0,',',' ')." €</span>";
			$pct_mois = "<span style='color:green;'>+".number_format(($tt_du_mois - $objencours) * 100 / intval($objencours))." %</span>";
		}
	}
	if(intval($trim_encours) !== 0){
		if($tt_du_trim < $trim_encours) {
			$diff_trim = "<span style='color:red;'>".number_format(($tt_du_trim - $trim_encours),0,',',' ')." €</span>";
			$pct_trim = "<span style='color:red;'>".number_format(($tt_du_trim - $trim_encours) * 100 / intval($trim_encours))." %</span>";
		}else{
			$diff_trim = "<span style='color:green;'>+".number_format(($tt_du_trim - $trim_encours),0,',',' ')." €</span>";
			$pct_trim = "<span style='color:green;'>+".number_format(($tt_du_trim - $trim_encours) * 100 / intval($trim_encours))." %</span>";
		}
	}
	if(intval($objmois) !== 0){
		if($total+$attente < $objmois) {
			$diff_ann = "<span style='color:red;'>".number_format(($total+$attente - $objmois),0,',',' ')." €</span>";
			$pct_ann = "<span style='color:red;'>".number_format(($total+$attente - $objmois) * 100 / intval($objmois))." %</span>";
		}else{
			$diff_ann = "<span style='color:green;'>+".number_format(($total+$attente - $objmois),0,',',' ')." €</span>";
			$pct_ann = "<span style='color:green;'>+".number_format(($total+$attente - $objmois) * 100 / intval($objmois))." %</span>";
		}
	}

	$projets_comm = $projets->search($_GET['commercial'],array(2,3,7,8));
	$projets_encours = count($projets_comm);
	$budget = $projets->budget_total_encours($_GET['commercial'],array(2,3,7,8));

	include("projets_src.php");
	?>

	
	<div class="header_comm">
		<div>
			<form method="get">
				<b>Commercial :</b><br>
				<select name="commercial">
					<option value="tous">Tous</option>
					<?= $opt_comm ?>
					<option value="adv">adv</option>
				</select>
				<img src="./images/icon/viewmag.png" onclick="this.closest('form').submit();" style="cursor:pointer;">
			</form>
		</div>
		<div>
			<?= $projets_encours ?> Projets en cours<br>
			<?= $budget ?> € de budget
			<span class="style_btn" style="display:block; margin-top:13px;" onclick="document.location=window.location.href;">Mettre à jour</span>
		</div>
		<div class="synthese_obj">
			<table>
				<tr><td colspan="5">Statistique Objectif en €HT<?php echo ($_GET['commercial']!==''? ' - '.$_GET['commercial'] :'')?></td></tr>
				<tr>
					<td></td>
					<td>du jour</td>
					<td>du mois</td>
					<td>du trimestre</td>
					<td>de l'année</td>
				</tr>
				<tr>
					<td>Objectif</td>
					<td style="background-color:white;"><?=  number_format($obj_du_jour,0,',',' ') ?> €</td>
					<td style="background-color:white;"><?=  number_format($obj_du_mois,0,',',' ') ?> €</td>
					<td style="background-color:white;"><?= number_format($trim,0,',',' ') ?> €</td>
					<td style="background-color:white;"><?= number_format($objtotal,0,',',' ') ?> €</td>
				</tr>
				<tr>
					<td>Objectif en temps réel</td>
					<td style="background-color:white;"><?=  number_format($obj_du_jour,0,',',' ') ?> €</td>
					<td style="background-color:white;"><?=  number_format($objencours,0,',',' ') ?> €</td>
					<td style="background-color:white;"><?= number_format($trim_encours,0,',',' ') ?> €</td>
					<td style="background-color:white;"><?= number_format($objmois,0,',',' ') ?> €</td>
				</tr>
				<tr>
					<td>Réalisé en temps réel</td>
					<td style="background-color:white"><?= number_format($tt_du_jour,0,',',' ') ?> €<br><?= $pct_jour ?></td>
					<td style="background-color:white"><?= number_format($tt_du_mois,0,',',' ') ?> €<br><?= $pct_mois ?></td>
					<td style="background-color:white"><?= number_format($tt_du_trim,0,',',' ') ?> €<br><?= $pct_trim ?></td>
					<td style="background-color:white"><?= number_format($total+$attente,0,',',' ') ?> €<br><?= $pct_ann ?></td>
				</tr>
				<tr>
					<td>Différence en temps réel</td>
					<td style="font-weight:bold;background-color:white"><?= $diff_jour ?></td>
					<td style="font-weight:bold;background-color:white"><?= $diff_mois ?></td>
					<td style="font-weight:bold;background-color:white"><?= $diff_trim ?></td>
					<td style="font-weight:bold;background-color:white"><?= $diff_ann ?></td>
				</tr>
			</table>
		</div>
	</div>
	

	<div class="flex_projets">
		<div class="flex_div">
			<div class="titre_flex"><span style="font-size:15px;"><a href='projets_new.php?commercial=<?=$_GET['commercial']?><?php echo (isset($_GET['rapim']) ? '' : '&rapim='.$nb_rapp_im) ?>'>voir tout</a> <?= $nb_rapp_im ?></span> Rappels immédiat</div>
			<?= $liste_rapp_im ?>
		</div>
		<div class="flex_div">
			<div class="titre_flex"><span style="font-size:15px;"><a href='projets_new.php?commercial=<?=$_GET['commercial']?><?php echo (isset($_GET['form']) ? '' : '&form='.$nb_form) ?>'>voir tout</a> <?= $nb_form ?></span> Contacts Formulaire</div>
			<?= $liste_form ?>
		</div>
		<div class="flex_div">
			<div class="titre_flex"><span style="font-size:15px;"><a href='projets_new.php?commercial=<?=$_GET['commercial']?><?php echo (isset($_GET['formpro']) ? '' : '&formpro='.$nb_form_pro) ?>'>voir tout</a> <?= $nb_form_pro ?></span> Contacts Formulaire Campagne</div>
			<?= $liste_form_pro ?>
		</div>
		<div class="flex_div">
			<div class="titre_flex"><span style="font-size:15px;"><?= $nb_rdv ?></span> RDV du jour - prioritaire</div>
			<?= $liste_rdv ?>
		</div>
		<div class="flex_div">
			<div class="titre_flex"><span style="font-size:15px;"><a href='projets_new.php?commercial=<?=$_GET['commercial']?><?php echo (isset($_GET['suiviint']) ? '' : '&suiviint='.$nb_suivi_int) ?>'>voir tout</a> <?= $nb_suivi_int ?></span> Relances clients sur 3 jours</div>
			<?= $liste_suivi_int ?>
		</div>
		<div class="flex_div">
			<div class="titre_flex"><span style="font-size:15px;"><a href='projets_new.php?commercial=<?=$_GET['commercial']?><?php echo (isset($_GET['prio']) ? '' : '&prio='.$nb_projets_prio) ?>'>voir tout</a> <?= $nb_projets_prio ?></span> Projets prioritaires<br>Commande proche / dépassée</div>
			<?=$liste_prio ?>
		</div>
		<div class="flex_div">
			<div class="titre_flex" style="background-color:red;"><span style="font-size:15px;"><?= $nb_rdv_retard ?></span> RDV en retard</div>
			<?= $liste_rdv_retard ?>
		</div>
		<div class="flex_div">
			<div class="titre_flex" style="background-color:red;"><a href='projets_new.php?commercial=<?=$_GET['commercial']?><?php echo (isset($_GET['suiviretard']) ? '' : '&suiviretard='.$nb_suivi_int_retard) ?>'>voir tout</a> <span style="font-size:15px;"><?= $nb_suivi_int_retard ?></span> Relances client en retard</div>
			<?= $liste_suivi_int_retard ?>
		</div>
		<div class="flex_div">
			<div class="titre_flex" style="background-color:red;"><a href='projets_new.php?commercial=<?=$_GET['commercial']?><?php echo (isset($_GET['cde']) ? '' : '&cde='.$nb_rappel_cmd) ?>'>voir tout</a> <span style="font-size:15px;"><?= $nb_rappel_cmd ?></span> Rappels commandes</div>
			<?= $liste_rappel_cmd ?>
		</div>
		<div class="flex_div">
			<div class="titre_flex" style="background-color:red;"><a href='projets_new.php?commercial=<?=$_GET['commercial']?><?php echo (isset($_GET['ssrap']) ? '' : '&ssrap='.$nb_projets_ss_rappel) ?>'>voir tout</a> <span style="font-size:15px;"><?= $nb_projets_ss_rappel ?></span> Projets sans rappel</div>
			<?= $liste_projet_ss_rappel ?>
		</div>
		<div class="flex_div">
			<div class="titre_flex" style="background-color:orange;"><a href='projets_new.php?commercial=<?=$_GET['commercial']?><?php echo (isset($_GET['chiff']) ? '' : '&chiff='.$nb_a_chiffrer) ?>'>voir tout</a> <span style="font-size:15px;"><?= $nb_a_chiffrer ?></span> Projets à chiffrer</div>
			<?= $liste_a_chiffrer ?>
		</div>
		<div class="flex_div">
			<div class="titre_flex" style="background-color:orange;"><a href='projets_new.php?commercial=<?=$_GET['commercial']?><?php echo (isset($_GET['info']) ? '' : '&info='.$nb_infos_client) ?>'>voir tout</a> <span style="font-size:15px;"><?= $nb_infos_client ?></span> Attente info client</div>
			<?= $liste_info_client ?>
		</div>
		

		<div class="flex_div">
			<div class="titre_flex" style="background-color:orange;"><a href='projets_new.php?commercial=<?=$_GET['commercial']?><?php echo (isset($_GET['ech']) ? '' : '&ech='.$nb_echeances) ?>'>voir tout</a> <span style="font-size:15px;"><?= $nb_echeances ?></span> Manque échéance</div>
			<?= $liste_echeance ?>
		</div>
		<div class="flex_div">
			<div class="titre_flex" style="background-color:orange;"><a href='projets_new.php?commercial=<?=$_GET['commercial']?><?php echo (isset($_GET['budg']) ? '' : '&budg='.$nb_budgets) ?>'>voir tout</a> <span style="font-size:15px;"><?= $nb_budgets ?></span> Manque budget</div>
			<?= $liste_budget ?>
		</div>
		<div class="flex_div">
			<div class="titre_flex" style="background-color:orange;"><a href='projets_new.php?commercial=<?=$_GET['commercial']?><?php echo (isset($_GET['delailivr']) ? '' : '&delailivr='.$nb_delaislivr) ?>'>voir tout</a> <span style="font-size:15px;"><?= $nb_delaislivr ?></span> Manque délai livraison</div>
			<?= $liste_delailivr ?>
		</div>
	</div>
	<?php

//// FIN ////

//// TABLEAU DES OBJECTIFS COMMERCIAUX (même que dans stats_objectifs_commerciaux.php) ////
	if($_GET['commercial'] !== "tous"){
		echo $tableau_objectifs_commerciaux;
	}
}

function ca($annee,$to=null,$username=""){
    global $DB_site2;
    
    if(is_null($to)) $to = mktime(23,59,59,12,31,$annee);
	$ca = $nb = $remise = $marge = $attente = $factid = array();
	$totalachat=$totalvente=0;
    $sql = "SELECT f.facturid, f.heurefacture, f.marge, f.provenance,
			SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) as 'prixHT',
			f.prixport / (1 + f.tvaport / 100) as 'portHT', u.nom, u.raisonsociale,
			CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) + (f.prixport / (1 + f.tvaport / 100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END as remiseHT";
    $sql .= " FROM facture f";
    $sql .= " INNER JOIN lignefacture lf ON (lf.factureid = f.facturid)";
    $sql .= " INNER JOIN utilisateur u ON (f.userid = u.userid)";
    $sql .= " WHERE f.heurefacture BETWEEN ".mktime(0,0,0,1,1,$annee)." AND ".$to;
    $sql .= " AND f.valide IN (1,5,7,8)";
    $sql .= " AND f.provenanceid = ''";
    $sql .= " AND f.commercial_attribution ='".$username."' ";
    $sql .= " GROUP BY f.facturid";

    $req = $DB_site2->query($sql);
    while($facture = $DB_site2->fetch_array($req)){
        $month = date("n",$facture['heurefacture']);
        $day = date("d",$facture['heurefacture']);
		// echo "<br><br>mois de ".$month."<br>";
		$totalvente = $facture['prixHT'] + $facture['portHT'] - $facture['remiseHT'];
		$totalachat=0;
		$ca_jour[$month.$day] += $totalvente;
        $ca[$month] += $totalvente;
		$remise[$month] += $facture['remiseHT'];
        $nb[$month]++;
		// echo "Cde NÂ° ".$facture[facturid]." : ".$totalvente."<br>";
		$totalachat=calcul_prix_achat_facture($facture[facturid]);
		$marge[$month]+=$totalvente-$totalachat;
		//affiche la marge en % dans les lignes popup
    	if($facture['marge']!='' && $facture['marge'] != 0.00 && number_format($totalvente,0,',',' ') != 0) {
    		$margepc_td = "<td title=\"marge\" ".($facture['marge']<=15 ? 'style=\'background-color:#e65858b3;\'' : '').">".$facture['marge']."%</td>";
    	}
    	else {
    		$margepc_td = "<td title=\"marge\"></td>";
    	}
		// echo "cumulÃ© de CA : ".$ca[$month]."<br>";
		$factid[$month] .= "<tr class=\"ligne_popup\">
        <td><a href=\"facture.php?action=editer&facturid=".$facture['facturid']."\" target=\"_blank\">".$facture['facturid']."</a></td>
        <td>".$facture['nom'].($facture['raisonsociale']?' - '.$facture['raisonsociale']:'')."</td>
        <td>".date("d/m/Y",$facture['heurefacture'])."</td>
        <td align=\"right\">".number_format($totalvente,0,',',' ')."</td>
        ".$margepc_td."
      </tr>";
    }
	// ici uniquement les commandes en attente de rÃ¨glement
	$sql = "SELECT f.facturid, f.heurefacture, f.provenance,
			SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) as 'prixHT',
			f.prixport / (1 + f.tvaport / 100) as 'portHT', f.nom,
			CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) + (f.prixport / (1 + f.tvaport / 100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END as remiseHT";
    $sql .= " FROM facture f";
    $sql .= " INNER JOIN lignefacture lf ON (lf.factureid = f.facturid)";
    $sql .= " WHERE f.heurefacture BETWEEN ".mktime(0,0,0,1,1,$annee)." AND ".$to;
    $sql .= " AND f.valide = 0";
    $sql .= " AND f.moyenid in (2,4,5,14,17,18,99)";
    $sql .= " AND f.provenanceid = ''";
    $sql .= " AND f.commercial_attribution ='".$username."' ";
    $sql .= " GROUP BY f.facturid";
    $req = $DB_site2->query($sql);
    while($facture = $DB_site2->fetch_array($req)){
        $month = date("n",$facture['heurefacture']);
        $day = date("d",$facture['heurefacture']);
		$totalattente = $facture['prixHT'] + $facture['portHT'] - $facture['remiseHT'];
        $attente[$month] += $totalattente;
        $attente_jour[$month.$day] += $totalattente;
        $factid_attente[$month] .= "<tr>
        <td><a href=\"facture.php?action=editer&facturid=".$facture['facturid']."\" target=\"_blank\">".$facture['facturid']."</a></td>
        <td>".$facture['nom']."</td>
        <td>".date("d/m/Y",$facture['heurefacture'])."</td>
        <td align=\"right\">".number_format($totalattente,0,',',' ')."</td>
      </tr>";
    }
	
    return array(
        "ca" => $ca,
        "ca_jour" => $ca_jour,
        "nb" => $nb,
		"remise" => $remise,
		"marge" => $marge,
		"attente" => $attente,
		"attente_jour" => $attente_jour,
		"factid" => $factid,
		"factid_attente" => $factid_attente
    );
}


function calcul_prix_achat_facture($facturid){
	global $DB_site2;
	$totalachat=0;
	
	$sql = "SELECT f.facturid, SUM(lf.paht * lf.qte) as 'prixht' FROM facture f";
    $sql .= " INNER JOIN lignefacture lf ON (lf.factureid = f.facturid)";
    $sql .= " WHERE f.facturid = ".$facturid;
	$requete = $DB_site2->query($sql);
	while($ligne = $DB_site2->fetch_array($requete)){
		$totalachat += $ligne['prixht'];
	}
	
	
	$sql = "SELECT ef.id AS efid, ef.facturid, ef.tarif, ef.type, efl.ligne_facture_id, efl.paht, efl.ecopart, efl.qte, efl.facture, efl.date";
	$sql .= " FROM envoi_fournisseur ef";
	$sql .= " INNER JOIN envoi_fournisseur_ligne efl ON (efl.efid = ef.id)";
	$sql .= " WHERE ef.destinataire <> '0'";
	$sql .= " AND ef.facturid = ".$facturid;
	$requete = $DB_site2->query($sql);
	while($ligne = $DB_site2->fetch_array($requete)){
		// l'achat des produits
// 		$totalachat += $ligne['qte'] * ($ligne['paht'] + $ligne['ecopart']);
		// echo "ligne 1 : + ".$ligne['qte']." * (".$ligne['paht']." + ".$ligne['ecopart'].") => ".$totalachat."<br>";
		
		// le "mazetcout" => le truc incomprÃ©hensible ....
		$adv=new adv($DB_site2);
		$adv->load_from_efid($ligne['efid']);
		if(isset($adv->fields['mazetcout']) && !isset($trsptfourn['mazetcout'.$adv->id])){
			$totalachat += floatval($adv->fields['mazetcout']);
			// echo "ligne mazetcout : + ".$adv->fields['mazetcout']."<br>";
		}
	}
	
	// les autres fournisseurs (installateurs ?)
	$sql = "SELECT *";
	$sql .= " FROM facture_fournisseur_autre";
	$sql .= " WHERE facturid = ".$facturid;
	$requete = $DB_site2->query($sql);
	while($ligne = $DB_site2->fetch_array($requete)){
		$totalachat += $ligne['prix'];
		// echo "ligne 2 : + ".$ligne['prix']." <br>";
	}
	
	// les avoirs
	$sql = "SELECT ffid, facturid, date, utilisateur, destinataire, article, paht, ecopart, qte, date_recu";
	$sql .= " FROM avoir_ligne";
	$sql .= " WHERE facturid = ".$facturid;
	$requete = $DB_site2->query($sql);
	while($ligne=$DB_site2->fetch_array($requete)){
		if($ligne['date_recu'] == 0){
			$totalachat -= ($ligne['paht']+$ligne['ecopart']);
			// echo "ligne 3 : - ( ".$ligne['paht']." + ".$ligne['ecopart']." ) <br>";
		}
	}
	
	// les retours
	$sql = "SELECT s.artid, a.artcode, s.date, s.utilisateur, a.paht, a.ecopart, COUNT(*) AS qte";
	$sql.= " FROM stock_item s";
	$sql.= " INNER JOIN article a USING(artid)";
	$sql.= " WHERE origin ='retour:".$facturid."'";
	$sql.= " GROUP BY date";
	$requete = $DB_site2->query($sql);
	while($retour = $DB_site2->fetch_array($requete)){
		$artids=array($retour['artid']);
		$si = $DB_site2->query("SELECT artid, artids FROM stock_mini WHERE artid=".$retour['artid']);
		if($DB_site2->num_rows($si) != 0){
			$a = $DB_site2->fetch_array($si);
			$artids = array_merge($artids,explode(",",$a['artids']));
		}
		$artids=array_filter($artids);
		$sql_lf = "SELECT lignefactureid";
		$sql_lf.= " FROM lignefacture";
		$sql_lf.= " WHERE factureid =".$facturid;
		$sql_lf.= " AND artid IN (".implode(",",$artids).")";
		$lignefacture = $DB_site2->query_first($sql_lf);
		if($lignefacture!=""){
            $sql = "SELECT efl.id, ROUND((efl.paht + efl.ecopart),2) AS montantht";
                    $sql.= " FROM envoi_fournisseur_ligne efl";
                    $sql.= " INNER JOIN envoi_fournisseur ef ON (ef.id = efl.efid)";
                    $sql.= " WHERE efl.ligne_facture_id = ".$lignefacture['lignefactureid'];
                    $sql.= " AND (efl.type = 'stock' OR efl.type = 'fourn')";
                    $sql.= " AND ef.destinataire <> '0'";
                    $sql.= " AND ef.facturid = ".$facturid;
            $ht = $DB_site2->query_first($sql);
            $totalachat -= ($retour['qte']*$ht['montantht']);
		}
		// echo "ligne 4 : - ( ".$retour['qte']." * ".$ht['montantht']." ) <br>";
	}
	// echo "ligne total : = ".$totalachat."<br>";
	return $totalachat;
}



function tdvalue($value,$sym="",$sign=null,$climit=0,$class=""){

	if($value=="")
		if($class=="") {
			return "<td></td>";
		}
		else {
			return "<td>";
		}
		if($sym=="&euro;"){
			$value=number_format($value,0,',',' ');
		} else {
			$value=number_format($value,1,',',' ');
		}
		if($class=="") {
			return "<td class=\"ca_nb\" style=\"color:".($value>$climit?"green":"red").";\">".($sign&&$value>0?"+":"").$value." ".$sym."</td>";
		}
		else {
			return "<td class=\"ca_nb showpopup\" style=\"color:".(intval(str_replace(" ", "", $value))>intval(str_replace(" ", "", $climit))?"green":"red").";\">".($sign&&$value>0?"+":"").$value." ".$sym;
		}
	}



//// FIN ////

//// RECHERCHE DE PROJETS ////


	if(isset($_GET['action']) && $_GET['action'] == 'search'){

		$projets=new projets();

		$action  = isset($_GET['action']) ? $_GET['action'] : '';
		$order  = isset($_GET['order']) ? $_GET['order'] : 'rappel ASC';

		$liste_etats_encours=array(1,2,3,4,7,8,9);
		$liste_etats_clos=array(5,6);

		if(is_null($_GET['projet_etatid'])){
			$_GET['projet_etatid'] = '1,2,3,4,7,8,9,5,6';
		}

		$commercial  = isset($_GET['commercial']) ? $_GET['commercial'] : '';
		$projet_etatid  = isset($_GET['projet_etatid']) ? $_GET['projet_etatid'] : '2,3,7,8';
		$projet_typeid  = isset($_GET['projet_typeid']) ? $_GET['projet_typeid'] : '';
		$rappel_com  = isset($_GET['rappel_com']) ? $_GET['rappel_com'] : '';
		$nobudget=isset($_GET['nobudget']);
		$noecheance=isset($_GET['noecheance']);
		$nocmddelai=isset($_GET['nocmddelai']);
		$srctxt=!empty($_GET['src']) ? $_GET['src'] : null;
		$fromecheance=!empty($_GET['fromecheance']) ? $_GET['fromecheance'] : null;
		$toecheance=!empty($_GET['toecheance']) ? $_GET['toecheance'] : null;
		$dateecheance=null;
		if(!is_null($fromecheance) && !is_null($toecheance)) $dateecheance=array($fromecheance,$toecheance);

		


 include("projets_src.php");
 ?>

<script type="text/javascript">
    function setorder(order,sens){
        document.getElementsByName("order")[0].value=order;
        document.getElementsByName("sensorder")[0].value=sens;
        document.forms['search'].submit();
    }
    function changerappel(){
        var periode=document.getElementsByName('rappel')[0].value;
        var displayuntil="none";
        switch(periode){
            case "rappels_jour":
            displayuntil="inline";
            document.getElementsByName('until')[0].value=0;
            break;
            
            case "rappels_futur":
            displayuntil="inline";
            document.getElementsByName('until')[0].querySelector('option:last-child').selected=true;
            break;
        }
        document.getElementById("rappel_until").style.display=displayuntil;
    }
</script>
<?php

$stats=$projets->stats($commercial);

$urlencours=rawurlencode(implode(",",$projets->encours));
$columns=array(2,3,8,7,4);
$totalcol=array();
$qtecol=array();
$tdtypebudget="";


foreach($projets->budget_types() as $btid=>$libelle){
    $td="";
    $qtetotal=0;
    $total=0;
    foreach($columns as $col){
        $qte=0;
        if(!isset($totalcol[$col])) $totalcol[$col]=0;
        if(!isset($qtecol[$col])) $qtecol[$col]=0;
        if(isset($stats['budget'][$btid][$col])){
            $qte=$stats['budget'][$btid][$col]['qte'];
            $qtecol[$col]+=$qte;
            $total+=$stats['budget'][$btid][$col]['total'];
            $totalcol[$col]+=$stats['budget'][$btid][$col]['total'];
        }
        $qtetotal+=$qte;
        $td.="<td align=\"center\" onclick=\"document.location='projets_new.php?type_page=_new&action=search&commercial=$commercial&projet_etatid=".$col."&projet_typeid=".$btid."&rappel=&rappel_com=';\">".$qte."</td>";
    }
    
    $total=number_format($total,0,',',' ');
    
    $sansbudget=isset($stats['infos'][$btid]['budget']) ? $stats['infos'][$btid]['budget'] : 0;
    if(!isset($qtecol2['budget'])) $qtecol2['budget']=0;
    $qtecol2['budget']+=$sansbudget;
    $sansecheance=isset($stats['infos'][$btid]['echeance']) ? $stats['infos'][$btid]['echeance'] : 0;
    if(!isset($qtecol2['echeance'])) $qtecol2['echeance']=0;
    $qtecol2['echeance']+=$sansecheance;
    $sansdelai=isset($stats['infos'][$btid]['delai_cmd']) ? $stats['infos'][$btid]['delai_cmd'] : 0;
    if(!isset($qtecol2['delai'])) $qtecol2['delai']=0;
    $qtecol2['delai']+=$sansdelai;
    $sansrappel=isset($stats['infos'][$btid]['rappel']) ? $stats['infos'][$btid]['rappel'] : 0;
    if(!isset($qtecol2['rappel'])) $qtecol2['rappel']=0;
    $qtecol2['rappel']+=$sansrappel;
    
    $tablebudget.=<<<HTML
    <tr>
        <td>$libelle</td>
        <td align="center" onclick="document.location='projets_new.php?type_page=_new&action=search&commercial=$commercial&projet_etatid=$urlencours&projet_typeid=$btid&rappel=&rappel_com=';">$qtetotal</td>
        $td
        <td align="right" onclick="document.location='projets_new.php?type_page=_new&action=search&commercial=$commercial&projet_etatid=$urlencours&projet_typeid=$btid&rappel=&rappel_com=';">$total&euro;</td>
        <td align="center" onclick="document.location='projets_new.php?type_page=_new&action=search&commercial=$commercial&projet_etatid=$urlencours&projet_typeid=$btid&rappel=&rappel_com=&nobudget=1';">$sansbudget</td>
        <td align="center" onclick="document.location='projets_new.php?type_page=_new&action=search&commercial=$commercial&projet_etatid=$urlencours&projet_typeid=$btid&rappel=&rappel_com=&noecheance=1';">$sansecheance</td>
        <td align="center" onclick="document.location='projets_new.php?type_page=_new&action=search&commercial=$commercial&projet_etatid=$urlencours&projet_typeid=$btid&rappel=&rappel_com=&nocmddelai=1';">$sansdelai</td>
        <td align="center" onclick="document.location='projets_new.php?type_page=_new&action=search&commercial=$commercial&projet_etatid=$urlencours&projet_typeid=$btid&rappel=sans_rappel&rappel_com=';">$sansrappel</td>
    </tr>
HTML;

    if($btid>0)
        $tdtypebudget.="<td colspan=\"2\" class=\"entete\">".$libelle."</td>";
}


$td="";
$tdtotal="";
$tdqte="";
foreach($columns as $col){
    $td.="<td class=\"entete\">".$projets->etats[$col]."</td>";
    $tdqte.="<td align=\"center\" onclick=\"document.location='projets_new.php?type_page=_new&action=search&commercial=$commercial&projet_etatid=$col&projet_typeid=&rappel=&rappel_com=';\">".$qtecol[$col]."</td>";
    $tdtotal.="<td align=\"right\">".number_format($totalcol[$col],0,',','&nbsp;')."&euro;</td>";
}
$total=number_format(array_sum($totalcol),0,',','&nbsp;');
$tdtotalqte=array_sum($qtecol);

$maxmonth=7;
$periodes=array("passe"=>"En retard","semaine"=>"Semaine en cours","semaineprochaine"=>"Semaine prochaine",date("Y-m")=>"Mois en cours");
$echeanceurl=array();
$echeanceurl["passe"]="&fromecheance=946681200&toecheance=".date_create("yesterday 23:59:59")->gettimestamp();

$echeanceurl["semaine"]="&fromecheance=".date_create((date("N")==1?"today":"last monday")." 0:0:0")->gettimestamp()."&toecheance=".date_create("next sunday 23:59:59")->gettimestamp();
$echeanceurl["semaineprochaine"]="&fromecheance=".date_create("monday next week 0:0:0")->gettimestamp()."&toecheance=".date_create("sunday next week 23:59:59")->gettimestamp();
$echeanceurl[date("Y-m")]="&fromecheance=".date_create("first day of this month 0:0:0")->gettimestamp()."&toecheance=".date_create("last day of this month 23:59:59")->gettimestamp();
for($i=1;$i<=$maxmonth;$i++){
    $d=new datetime("+".$i." month");
    $d->modify("first day of this month 0:0:0");
    $periodes[$d->format("Y-m")]=$d->format("m/Y");
    $d2=clone $d;
    $d2->modify("last day of this month 23:59:59");
    $echeanceurl[$d->format("Y-m")]="&fromecheance=".$d->gettimestamp()."&toecheance=".$d2->gettimestamp();
}
$periodes["futur"]="Futur";
$periodes["sans"]="Sans échéance";
$echeanceurl["sans"]="&noecheance=1";

$minmonth=new datetime("+".($maxmonth+1)." month");
$minmonth->modify("first day of this month 0:0:0");
$echeanceurl["futur"]="&fromecheance=".$minmonth->gettimestamp()."&toecheance=".date_create("+1000 days")->gettimestamp();
$futur=array();
foreach(preg_grep("/^\d+-\d+$/",array_keys($stats['delai'])) as $Ym){
    $d=new datetime($Ym."-01");
    if($d>=$minmonth){
        foreach($stats['delai'][$Ym] as $btid=>$bt){
            if(!isset($futur[$btid])) $futur[$btid]=array("qte"=>0,"total"=>0);
            $futur[$btid]['qte']+=$bt['qte'];
            $futur[$btid]['total']+=$bt['total'];
        }
            
    }
}
$stats['delai']['futur']=$futur;

$tableecheance="";
foreach($periodes as $key=>$libelle){
    $tableecheance.="<tr>
        <td>$libelle</td>";
    
    $totalligne=0;
    $qteligne=0;
    foreach($projets->budget_types() as $btid=>$lib){
        if($btid>0){
            $qte=$total=0;
            if(isset($stats['delai'][$key][$btid])){
                $qte=$stats['delai'][$key][$btid]['qte'];
                $total=$stats['delai'][$key][$btid]['total'];
            }
            $url="projets_new.php?type_page=_new&action=search&commercial=".$commercial."&projet_etatid=".$urlencours."&projet_typeid=".$btid."&rappel=&rappel_com=".$echeanceurl[$key];
            
            $tableecheance.="<td align=\"center\" onclick=\"document.location='".$url."'\">".$qte."</td>
            <td align=\"right\" onclick=\"document.location='".$url."'\">".number_format($total,0,","," ")."€</td>";
            $totalligne+=$total;
            $qteligne+=$qte;
        }
    }

    $url="projets_new.php?type_page=_new&action=search&commercial=".$commercial."&projet_etatid=".$urlencours."&projet_typeid=&rappel=&rappel_com=".$echeanceurl[$key];
    $tableecheance.="<td align=\"center\" onclick=\"document.location='".$url."'\">".$qteligne."</td>
    <td align=\"right\" onclick=\"document.location='".$url."'\">".number_format($totalligne,0,","," ")."€</td>
    </tr>";
}

print <<<HTML
    <table class="table_alternate_color" style="width:1200px;">
        <tr>
            <td class="entete reducer" onclick="this.closest('table').classList.toggle('reduce')">Projets en cours $commercial</td>
            <td class="entete">Qté totale</td>
            $td
            <td class="entete">Budget</td>
            <td class="entete">Sans budget</td>
            <td class="entete">Sans échéance</td>
            <td class="entete">Sans délai</td>
            <td class="entete">Sans rappel</td>
        </tr>
        <tr>
            <td>Nbre de projet en cours</td>
            <td align="center" onclick="document.location='projets_new.php?type_page=_new&action=search&commercial=$commercial&projet_etatid=$urlencours&projet_typeid=&rappel=&rappel_com=';">$tdtotalqte</td>
            $tdqte
            <td></td>
            <td align="center" onclick="document.location='projets_new.php?type_page=_new&action=search&commercial=$commercial&projet_etatid=$urlencours&projet_typeid=&rappel=&rappel_com=&nobudget=1';">$qtecol2[budget]</td>
            <td align="center" onclick="document.location='projets_new.php?type_page=_new&action=search&commercial=$commercial&projet_etatid=$urlencours&projet_typeid=&rappel=&rappel_com=&noecheance=1';">$qtecol2[echeance]</td>
            <td align="center" onclick="document.location='projets_new.php?type_page=_new&action=search&commercial=$commercial&projet_etatid=$urlencours&projet_typeid=&rappel=&rappel_com=&nocmddelai=1';">$qtecol2[delai]</td>
            <td align="center" onclick="document.location='projets_new.php?type_page=_new&action=search&commercial=$commercial&projet_etatid=$urlencours&projet_typeid=&rappel=sans_rappel&rappel_com=';">$qtecol2[rappel]</td>
        </tr>
        $tablebudget
        <tr>
            <td>Budget</td>
            <td></td>
            $tdtotal
            <td align="right">$total&euro;</td>
        </tr>
    </table>
    
    <br>
    <table class="table_alternate_color reduce" style="width:1200px;">
        <tr>
            <td class="entete reducer" onclick="this.closest('table').classList.toggle('reduce')">Echéance passage commande</td>
            $tdtypebudget
            <td class="entete">Qté totale</td>
            <td class="entete">Budget</td>
        </tr>
        $tableecheance
    </table>
HTML;

if($action=="search"){
    $function="search";
    switch($_GET['rappel']){
        
        case "rappels_retard":
        $fromdate=new datetime("4 years ago");
        $todate=new datetime("yesterday 23:59:59");
        break;
        
        case "rappels_futur":
        $fromdate=new datetime("tomorrow 0:0:0");
        $todate=new datetime("+".$_GET['until']." days");
        break;
        
        case "rappels_jour":
        $fromdate=new datetime();
        $fromdate->settime(0,0,0);
        $todate=clone $fromdate;
        $todate->modify("+".$_GET['until']." days");
        $todate->settime(23,59,59);
        break;
        
        case "sans_rappel":
        $function="norappel";
        break;
        
        default:
        $fromdate=$todate=null;
        break;
        //$fromdate=$todate=null;
    }

    if(!isset($_GET['order']) || empty($_GET['order'])) $_GET['order']="rappel";
    if(!isset($_GET['sensorder']) || empty($_GET['order'])) $_GET['sensorder']=0;

    $today=date("d/m/Y");
    
    $tr=array();
    $budget_total=0;
    foreach($projets->$function($commercial,$projet_etatid,$projet_typeid,$fromdate,$todate,$rappel_com,$nobudget,$noecheance,$nocmddelai,$srctxt,$dateecheance) as $projet){
        $prjusers="";
        foreach($projet->users() as $user){
            $prjusers.="<li style=\"".($user->userid==$projet->userid?"font-weight:bold;":"")."\"><a href=\"utilisateur.php?action=editer&userid=".$user->userid."\" target=\"_blank\">".trim($user->raisonsociale." ".$user->nom." ".$user->prenom)." (".$user->userid.")</a></li>";
        }
        
        $budget_total+=$projet->budget;
        $budget=number_format($projet->budget,2,",","&nbsp;");
        $creation=$projet->date->format("d/m/Y");
        if(!is_null($projet->dateecheance))
            $echeance=$projet->dateecheance->format("d/m/Y");
        else
            $echeance="";
            
        $orderkey=null;
        $rappels="";
        
        $listerappels=$projet->rappels();

        if($_GET['rappel']=="rappels_retard" || $_GET['rappel']=='' ) $listerappels=array_slice($listerappels,-1);

        foreach($listerappels as $rappel){
            if($_GET['order']=="rappel" && is_null($orderkey)) $orderkey=$rappel['ts'];
            $info=$rappel['origin']=="delai_cmd"?"Délai commande":$rappel['commercial'];
            $color=time()>=strtotime($rappel['date']->format("d-m-Y"))?"red":"";
            $rappels.="<span style=\"color:".$color."\">".$rappel['date']->format("d/m/Y".($rappel['date']->format("G")>0?" H:i":""))." ".$info."</span><br>";
        }

        switch($_GET['order']){
            case "date":
            $orderkey=$projet->date->gettimestamp();
            break;
            
            case "echeance":
            $orderkey=!is_null($projet->dateecheance)?$projet->dateecheance->gettimestamp():'';
            break;
            
            case "budget":
            $orderkey=$projet->budget;
            break;
        }

        //Affichage du date pour passer commande dans le tableau des projets
        $delai_date_cmd=$projet->datecmd();
        if(!is_null($delai_date_cmd)){
            $delai_date_cmd=$delai_date_cmd->format("d/m/Y");
        }
        
        $orderkey.=rand(10000,100000);

        $tr[$orderkey]=<<<HTML
            <tr id="flist">
                <td>$projet->commercial</td>
                <td>$creation $projet->pjt</td>
                <td><a href="projet.php?projetid=$projet->projetid" target="_blank">$projet->libelle</a></td>
                <td><ul>$prjusers</ul></td>
                <td style="text-align:right;">$budget&nbsp;€</td>
                <td>{$projet->budget_type_lib()}</td>
                <td>$projet->etat</td>                
                <td>$echeance</td>
                <td>$delai_date_cmd</td>
                <td>$rappels</td>
            </tr>
HTML;
    }

    if($_GET['sensorder']==0)
        ksort($tr);
    else
        krsort($tr);
    $table=implode("",$tr);

    $ordericon=array($_GET['order']=>$_GET['sensorder']==0?"⏶":"⏷");
    $newsens=abs(intval($_GET['sensorder'])-1);
    
    $budget_total=$budget_total>0?" (".number_format($budget_total,0,"."," ")."€)":"";
    
    echo "<center><h3>".count($tr)." résultat".(count($tr)>1?"s":"")." ".$budget_total."</h3></center><br>
    <table class=\"table_alternate_color\" style=\"width:1200px;\">
        <tr>
            <td class=\"entete\">Commercial</td>
            <td class=\"entete\" onclick=\"setorder('date','".$newsens."')\">Date création &<br>N° projet".(isset($ordericon['date'])?$ordericon['date']:"")."</td>
            <td class=\"entete\">Nom projet</td>
            <td class=\"entete\">Client</td>
            <td class=\"entete\" onclick=\"setorder('budget','".$newsens."')\">Budget".(isset($ordericon['budget'])?$ordericon['budget']:"")."</td>
            <td class=\"entete\">Type</td>
            <td class=\"entete\">Etat</td>            
            <td class=\"entete\" onclick=\"setorder('echeance','".$newsens."')\">Echéance livraison".(isset($ordericon['echeance'])?$ordericon['echeance']:"")."</td>
            <td class=\"entete\" onclick=\"setorder('delai_date_cmd','".$newsens."')\">Date pour passer cde".(isset($ordericon['delai_date_cmd'])?$ordericon['delai_date_cmd']:"")."</td>
            <td class=\"entete\" onclick=\"setorder('rappel','".$newsens."')\">Rappels".(isset($ordericon['rappel'])?$ordericon['rappel']:"")."</td>
        </tr>
        ".$table."
    </table>";
}

}

//// TABLEAU DE BORD SUIVI EXTERNE //// 
if($_GET['action'] == 'suiviexterne'){

	$fromdate=new datetime("4 years ago");

	//RDV externe
	$prappel = "p.rappel BETWEEN ".mktime('00','00','01',date("m"),date("d"),date("Y"))." AND ".mktime('23','59','59',date('m'),date('d'),date('Y'));
	if($_GET['rdvextall']){
		$prappel = "p.rappel >= ".mktime('00','00','01',date('m'),date('d'),date('Y'));
	}
	$sql = "SELECT p.*, u.prenom, u.nom, u.raisonsociale FROM projet p INNER JOIN utilisateur u ON u.userid = p.userid WHERE ".$prappel." AND rappel_commercial = 'externe' AND rappel_type = 'rdv' ORDER BY p.rappel";
	$requete = $DB_site2->query($sql);
	$nb_rdvext = $DB_site2->num_rows($requete);
	$liste_rdv = "";
	if($nb_rdvext > 0){
		$i = 1;
		$max = isset($_GET['rdvext']) ? $_GET['rdvext'] + 1 : 11;
		while($rdvext=$DB_site2->fetch_array($requete)){
			$type_ech = "";
			if($i < $max){
				$projet = new projet($rdvext['projetid']);
				$datecmd=$projet->datecmd();
				if(!is_null($datecmd)){
					$datecmdcolor=$datecmd<new datetime('today 0:0:0')?"red":"";
					$datecmd=$datecmd->format("d/m/Y");
				}
				$nom=($rdvext['raisonsociale']!='' ? $rdvext['raisonsociale'] : $rdvext['nom']." ".$rdvext['prenom']);
				if(!is_null($rdvext['echeance_type'])){
					if($rdvext['echeance_type'] == '1'){
						$type_ech = " - <div title='info donné par le client' style='display:inline;'>C</div>";
					}
					else{
						$type_ech = " - <div title='estimé par mcav' style='display:inline;'>M</div>";
					}
				}
				$liste_rdv .= "<div style='height:21px;overflow-y:hidden;padding:1px;' onmouseover='affich_info(this)' onmouseout='affich_info(this,\"out\")'><strong style='color:red;'>".date('d/m H:i', $rdvext['rappel'])."</strong> - <a href='projet.php?projetid=".$rdvext['projetid']."' target='_blank' ".($rdvext['raisonsociale']!='' ? "style='color:#0393C2;font-weight:bold'" : "").">".substr($nom,0,18). "...</a> - ".$rdvext['budget']."€ - ".$datecmd.$type_ech."
				<span style='display:none;position: absolute; padding: 3px; border: 2px solid black; border-top: none;min-width:200px;margin-left:-3px;'>".$projet->pjt." - ".$projet->etat." - ".$projet->libelle."</span></div>";
				$i++;
			}
		}
	}

	//Suivi externe sur 3 jours (jour même et 2 jours avant)
	$jour_meme = mktime(23,59,59,date('m'),date('d'),date('Y'));
	//si on est lundi ou mardi, on affiche les rappels de jeudi et vendredi, ou vendredi :
	if(date('w') == '1' || date('w') == '2'){
		$deuxj_av = mktime(0,0,0,date('m'),date('d'),date('Y')) - ((24*60*60) * 4);
	}
	else{
		$deuxj_av = mktime(0,0,0,date('m'),date('d'),date('Y')) - ((24*60*60) * 2);
	}

	$suivis_ext = $DB_site2->query("SELECT p.*, u.nom, u.raisonsociale, u.prenom FROM projet p INNER JOIN utilisateur u ON u.userid = p.userid WHERE p.rappel_commercial = 'externe' AND p.etatid IN ('1','2','3','4','7','8') AND rappel BETWEEN ".$deuxj_av." AND ".$jour_meme . " AND rappel_type != 'rdv' ".(!is_null($_GET['tri_suivi']) ? $_GET['tri_suivi'] : "ORDER BY p.rappel"));
	$nb_suivi_ext = $DB_site2->num_rows($suivis_ext);

	if(empty($_GET['tri_suivi']) || $_GET['tri_suivi'] == 'ORDER BY p.budget DESC' || $_GET['tri_suivi'] == 'ORDER BY p.echeance_livraison DESC, p.delai_cmd DESC'){
		$budget_tri = 'ORDER BY p.budget';
		$date_tri = 'ORDER BY p.echeance_livraison, p.delai_cmd';
		$lib_budg_tri = 'ASC';
		$lib_date_tri = 'ASC';
	}
	elseif($_GET['tri_suivi'] == 'ORDER BY p.budget'){
		$budget_tri = 'ORDER BY p.budget DESC';
		$date_tri = 'ORDER BY p.echeance_livraison, p.delai_cmd';
		$lib_budg_tri = 'DESC';
		$lib_date_tri = 'ASC';
	}
	elseif($_GET['tri_suivi'] == 'ORDER BY p.echeance_livraison, p.delai_cmd'){
		$budget_tri = 'ORDER BY p.budget';
		$date_tri = 'ORDER BY p.echeance_livraison DESC, p.delai_cmd DESC';
		$lib_budg_tri = 'ASC';
		$lib_date_tri = 'DESC';
	}
	$liste_suivi_ext = "";
	if($DB_site2->num_rows($suivis_ext) > 0){
		$i = 1;
		$max = isset($_GET['suiviext']) ? $_GET['suiviext'] + 1 : 11;
		$liste_suivi_ext .= "<div style='height:21px;overflow-y:hidden;padding:1px;text-align:center;'><a href='projets_new.php?action=suiviexterne&tri_suivi=".$budget_tri."'>Trier budget ".$lib_budg_tri."</a> - <a href='projets_new.php?action=suiviexterne&tri_suivi=".$date_tri."'>Trier date délai ".$lib_date_tri."</a></div>";
		while($suivi_ext=$DB_site2->fetch_array($suivis_ext)){
			$type_ech = "";
			if($i < $max){
				$projet = new projet($suivi_ext['projetid']);
				$datecmd=$projet->datecmd();
				if(!is_null($datecmd)){
					$datecmdcolor=$datecmd<new datetime('today 0:0:0')?"red":"";
					$datecmd=$datecmd->format("d/m/Y");
				}
				if(!is_null($suivi_ext['echeance_type'])){
					if($suivi_ext['echeance_type'] == '1'){
						$type_ech = " - <div title='info donné par le client' style='display:inline;'>C</div>";
					}
					else{
						$type_ech = " - <div title='estimé par mcav' style='display:inline;'>M</div>";
					}
				}
				$nom=($suivi_ext['raisonsociale']!='' ? $suivi_ext['raisonsociale'] : $suivi_ext['nom']." ".$suivi_ext['prenom']);
				$liste_suivi_ext .= "<div style='height:21px;overflow-y:hidden;padding:1px;' onmouseover='affich_info(this)' onmouseout='affich_info(this,\"out\")'>".date('d/m', $suivi_ext['rappel'])." - <a href='projet.php?projetid=".$suivi_ext['projetid']."' target='_blank' ".($suivi_ext['raisonsociale']!='' ? "style='color:#0393C2;font-weight:bold'" : "").">".substr($nom,0,13)."..</a> - ".$suivi_ext['budget']."€ - ".$datecmd.$type_ech."
				<span style='display:none;position: absolute; padding: 3px; border: 2px solid black; border-top: none;min-width:200px;margin-left:-3px;'>".$projet->pjt." - ".$projet->etat." - ".$projet->libelle."</span></div>";
				$i++;
			}
		}
	}

	//Projets prio
	///On affiche seulement si aucun suivi n'a été fait dans les 7j avant aujourd'hui ET si aucun rappel futur
	$projets_prio = $DB_site2->query("SELECT p.*, u.nom, u.raisonsociale, u.prenom FROM projet p INNER JOIN utilisateur u ON u.userid = p.userid LEFT JOIN suivi_client sc ON sc.projetid = p.projetid WHERE p.commercial != '' AND etatid IN ('1','2','3','4','7','8') AND echeance_livraison != 0 AND p.rappel < ".time()." AND sc.date NOT BETWEEN ".(time() - ((24*60*60)*7))." AND ".time()." GROUP BY p.projetid ".(!is_null($_GET['tri_prio']) ? $_GET['tri_prio'] : "ORDER BY p.echeance_livraison, p.delai_cmd"));
	$nb_projets_prio = 0;

	//trier par budget ou date
	if(empty($_GET['tri_prio']) || $_GET['tri_prio'] == 'ORDER BY p.budget DESC' || $_GET['tri_prio'] == 'ORDER BY p.echeance_livraison DESC, p.delai_cmd DESC'){
		$budget_tri = 'ORDER BY p.budget';
		$date_tri = 'ORDER BY p.echeance_livraison, p.delai_cmd';
		$lib_budg_tri = 'ASC';
		$lib_date_tri = 'ASC';
	}
	elseif($_GET['tri_prio'] == 'ORDER BY p.budget'){
		$budget_tri = 'ORDER BY p.budget DESC';
		$date_tri = 'ORDER BY p.echeance_livraison, p.delai_cmd';
		$lib_budg_tri = 'DESC';
		$lib_date_tri = 'ASC';
	}
	elseif($_GET['tri_prio'] == 'ORDER BY p.echeance_livraison, p.delai_cmd'){
		$budget_tri = 'ORDER BY p.budget';
		$date_tri = 'ORDER BY p.echeance_livraison DESC, p.delai_cmd DESC';
		$lib_budg_tri = 'ASC';
		$lib_date_tri = 'DESC';
	}
	$liste_prio = "";
	if($DB_site2->num_rows($projets_prio) > 0){
		$i = 1;
		$max = isset($_GET['prio']) ? $_GET['prio'] + 1 : 11;
		$liste_prio .= "<div style='height:21px;overflow-y:hidden;padding:1px;text-align:center;'><a href='projets_new.php?action=suiviexterne&tri_prio=".$budget_tri."'>Trier budget ".$lib_budg_tri."</a> - <a href='projets_new.php?action=suiviexterne&tri_prio=".$date_tri."'>Trier date délai ".$lib_date_tri."</a></div>";
		while($projet_prio=$DB_site2->fetch_array($projets_prio)){
			$type_ech = "";
			//affiche les projets où les commandes doivent être passées maxi 5 jours après aujourd'hui
			$date_delais = $projet_prio['echeance_livraison'] - $projet_prio['delai_cmd'] - ((24*60*60)*5);
			if($i < $max){
				$projet = new projet($projet_prio['projetid']);
				$datecmd=$projet->datecmd();
				if(!is_null($datecmd)){
					$datecmdcolor=$datecmd<new datetime('today 0:0:0')?"red":"";
					$datecmd=$datecmd->format("d/m/Y");
				}
				if($date_delais <= time()){
					$nom=($projet_prio['raisonsociale']!='' ? $projet_prio['raisonsociale'] : $projet_prio['nom']." ".$projet_prio['prenom']);
					if(!is_null($projet_prio['echeance_type'])){
						if($projet_prio['echeance_type'] == '1'){
							$type_ech = " - <div title='info donné par le client' style='display:inline;'>C</div>";
						}
						else{
							$type_ech = " - <div title='estimé par mcav' style='display:inline;'>M</div>";
						}
					}
					$liste_prio .= "<div style='height:21px;overflow-y:hidden;padding:1px;' onmouseover='affich_info(this)' onmouseout='affich_info(this,\"out\")'><a href='projet.php?projetid=".$projet_prio['projetid']."' target='_blank' ".($projet_prio['raisonsociale']!='' ? "style='color:#0393C2;font-weight:bold'" : "").">".trim(substr($nom,0,18)). "...</a> - ".$projet_prio['budget']."€ - <div style='color:".$datecmdcolor.";display:inline;background-color:transparent;'>".$datecmd."</div>".$type_ech."
					<span style='display:none;position: absolute; padding: 3px; border: 2px solid black; border-top: none;min-width:200px;margin-left:-3px;'>".$projet->pjt." - ".$projet->etat." - ".$projet->libelle."</span></div>";
				}
			}
			if($date_delais <= time()){
				$nb_projets_prio++;
			}
			$i++;
		}
	}

	//RDV en retard
	$rdvs_retard = $DB_site2->query("SELECT p.*, u.nom, u.raisonsociale, u.prenom FROM projet p INNER JOIN utilisateur u ON u.userid = p.userid WHERE p.rappel_commercial = 'externe' AND rappel_type = 'rdv' AND etatid IN ('1','2','3','4','7','8') AND rappel BETWEEN ".intval($fromdate->gettimestamp())." AND ".mktime('00','00','00',date('m'),date('d'),date('Y'))." ORDER BY p.rappel");
	$nb_rdv_retard = $DB_site2->num_rows($rdvs_retard);
	$liste_rdv_retard = "";
	if($DB_site2->num_rows($rdvs_retard) > 0){
		$i = 1;
		$max = isset($_GET['rdvret']) ? $_GET['rdvret'] + 1 : 11;
		while($rdv_retard=$DB_site2->fetch_array($rdvs_retard)){
			$type_ech = "";
			if($i < $max){
				$projet = new projet($rdv_retard['projetid']);
				$datecmd=$projet->datecmd();
				if(!is_null($datecmd)){
					$datecmdcolor=$datecmd<new datetime('today 0:0:0')?"red":"";
					$datecmd=$datecmd->format("d/m/Y");
				}
				if(!is_null($rdv_retard['echeance_type'])){
					if($rdv_retard['echeance_type'] == '1'){
						$type_ech = " - <div title='info donné par le client' style='display:inline;'>C</div>";
					}
					else{
						$type_ech = " - <div title='estimé par mcav' style='display:inline;'>M</div>";
					}
				}
				$nom=($rdv_retard['raisonsociale']!='' ? $rdv_retard['raisonsociale'] : $rdv_retard['nom']." ".$rdv_retard['prenom']);
				$liste_rdv_retard .= "<div style='height:21px;overflow-y:hidden;padding:1px;' onmouseover='affich_info(this)' onmouseout='affich_info(this,\"out\")'>".date('d/m H:i', $rdv_retard['rappel'])." - <a href='projet.php?projetid=".$rdv_retard['projetid']."' target='_blank' ".($rdv_retard['raisonsociale']!='' ? "style='color:#0393C2;font-weight:bold'" : "").">".substr($nom,0,11). "...</a> - ".$rdv_retard['budget']."€ - ".$datecmd."
				<span style='display:none;position: absolute; padding: 3px; border: 2px solid black; border-top: none;min-width:200px;margin-left:-3px;'>".$projet->pjt." - ".$projet->etat." - ".$projet->libelle."</span></div>";
				$i++;
			}
		}
	}

	//Suivi en retard
	$suivis_ext_retard = $DB_site2->query("SELECT p.*, u.nom, u.raisonsociale, u.prenom FROM projet p INNER JOIN utilisateur u ON u.userid = p.userid WHERE p.rappel_commercial = 'externe' AND rappel_type != 'rdv' AND etatid IN ('1','2','3','4','7','8') AND rappel BETWEEN ".intval($fromdate->gettimestamp())." AND ".$deuxj_av." ".(isset($_GET['tri_suiviret']) ? $_GET['tri_suiviret'] : ' ORDER BY p.rappel'));
	$nb_suivi_ext_retard = $DB_site2->num_rows($suivis_ext_retard);

	//trier par budget ou date
	if(empty($_GET['tri_suiviret']) || $_GET['tri_suiviret'] == 'ORDER BY p.budget DESC' || $_GET['tri_suiviret'] == 'ORDER BY p.echeance_livraison DESC, p.delai_cmd DESC'){
		$budget_tri = 'ORDER BY p.budget';
		$date_tri = 'ORDER BY p.echeance_livraison, p.delai_cmd';
		$lib_budg_tri = 'ASC';
		$lib_date_tri = 'ASC';
	}
	elseif($_GET['tri_suiviret'] == 'ORDER BY p.budget'){
		$budget_tri = 'ORDER BY p.budget DESC';
		$date_tri = 'ORDER BY p.echeance_livraison, p.delai_cmd';
		$lib_budg_tri = 'DESC';
		$lib_date_tri = 'ASC';
	}
	elseif($_GET['tri_suiviret'] == 'ORDER BY p.echeance_livraison, p.delai_cmd'){
		$budget_tri = 'ORDER BY p.budget';
		$date_tri = 'ORDER BY p.echeance_livraison DESC, p.delai_cmd DESC';
		$lib_budg_tri = 'ASC';
		$lib_date_tri = 'DESC';
	}
	$liste_suivi_ext_retard = "";
	if($DB_site2->num_rows($suivis_ext_retard) > 0){
		$i = 1;
		$max = isset($_GET['suiviretard']) ? $_GET['suiviretard'] + 1 : 11;
		$liste_suivi_ext_retard .= "<div style='height:21px;overflow-y:hidden;padding:1px;text-align:center;'><a href='projets_new.php?action=suiviexterne&tri_suiviret=".$budget_tri."'>Trier budget ".$lib_budg_tri."</a> - <a href='projets_new.php?action=suiviexterne&tri_suiviret=".$date_tri."'>Trier date délai ".$lib_date_tri."</a></div>";
		while($suivi_ext_retard=$DB_site2->fetch_array($suivis_ext_retard)){
			$type_ech = "";
			if($i < $max){
				$projet = new projet($suivi_ext_retard['projetid']);
				$datecmd=$projet->datecmd();
				if(!is_null($datecmd)){
					$datecmdcolor=$datecmd<new datetime('today 0:0:0')?"red":"";
					$datecmd=$datecmd->format("d/m/Y");
				}
				if(!is_null($suivi_ext_retard['echeance_type'])){
					if($suivi_ext_retard['echeance_type'] == '1'){
						$type_ech = " - <div title='info donné par le client' style='display:inline;'>C</div>";
					}
					else{
						$type_ech = " - <div title='estimé par mcav' style='display:inline;'>M</div>";
					}
				}
				$nom=($suivi_ext_retard['raisonsociale']!='' ? $suivi_ext_retard['raisonsociale'] : $suivi_ext_retard['nom']." ".$suivi_ext_retard['prenom']);
				$liste_suivi_ext_retard .= "<div style='height:21px;overflow-y:hidden;padding:1px;' onmouseover='affich_info(this)' onmouseout='affich_info(this,\"out\")'>".date('d/m', $suivi_ext_retard['rappel'])." - <a href='projet.php?projetid=".$suivi_ext_retard['projetid']."' target='_blank' ".($suivi_ext_retard['raisonsociale']!='' ? "style='color:#0393C2;font-weight:bold'" : "").">".substr($nom,0,13)."..</a> - ".$suivi_ext_retard['budget']."€ - ".$datecmd.$type_ech."
				<span style='display:none;position: absolute; padding: 3px; border: 2px solid black; border-top: none;min-width:200px;margin-left:-3px;'>".$projet->pjt." - ".$projet->etat." - ".$projet->libelle."</span></div>";
				$i++;
			}
		}
	}
 include("projets_src.php");
	?>
	
	<div class="flex_projets">
		<div class="flex_div">
			<div class="titre_flex"><span style="font-size:15px;"><a href='projets_new.php?action=suiviexterne<?php echo (isset($_GET['rdvext']) ? '' : '&rdvext='.$nb_rdvext) ?>'>voir tout</a> <?= $nb_rdvext ?></span> RDV<?= (empty($_GET['rdvextall']) ? ' du jour' : '') ?> suivi externe <a href='projets_new.php?action=suiviexterne<?php echo (isset($_GET['rdvextall']) ? '' : '&rdvextall=all') ?>'>voir <?= (empty($_GET['rdvextall']) ? 'tous les jours' : 'du jour') ?></a></div>
			<?= $liste_rdv ?>
		</div>
		<div class="flex_div">
			<div class="titre_flex"><span style="font-size:15px;"><a href='projets_new.php?action=suiviexterne<?php echo (isset($_GET['suiviext']) ? '' : '&suiviext='.$nb_suivi_ext) ?>'>voir tout</a> <?= $nb_suivi_ext ?></span> Suivi externe sur 3 jours</div>
			<?= $liste_suivi_ext ?>
		</div>
		<div class="flex_div">
			<div class="titre_flex" style="background-color: yellow;"><span style="font-size:15px;"><a href='projets_new.php?action=suiviexterne<?php echo (isset($_GET['prio']) ? '' : '&prio='.$nb_projets_prio) ?>'>voir tout</a> <?= $nb_projets_prio ?></span> Projets prioritaires<br>Commande proche / dépassée</div>
			<?= $liste_prio ?>
		</div>
		<div class="flex_div">
			<div class="titre_flex" style="background-color:red;"><span style="font-size:15px;"><a href='projets_new.php?action=suiviexterne<?php echo (isset($_GET['rdvret']) ? '' : '&rdvret='.$nb_rdv_retard) ?>'>voir tout</a> <?= $nb_rdv_retard ?></span> RDV en retard suivi externe</div>
			<?= $liste_rdv_retard ?>
		</div>
		<div class="flex_div">
			<div class="titre_flex" style="background-color:red;"><a href='projets_new.php?action=suiviexterne<?php echo (isset($_GET['suiviretard']) ? '' : '&suiviretard='.$nb_suivi_ext_retard) ?>'>voir tout</a> <span style="font-size:15px;"><?= $nb_suivi_ext_retard ?></span> Suivi externe en retard</div>
			<?= $liste_suivi_ext_retard ?>
		</div>
	</div>

	<?php
	//Récupération de tous les commerciaux actifs dans les 30 derniers jours
	$sql =  "SELECT username FROM parametresgeneraux WHERE actif = '1' AND commissions ='1' ORDER BY username";
	$commercials = $DB_site2->query($sql);

	if($DB_site2->num_rows($commercials) > 0){
		$fromdate=new datetime("4 years ago");
		$veille = mktime(0,0,0,date('m'),date('d'),date('Y')) - 24*60*60;
		//Suivi interne sur 3 jours (jour même et 2 jours avant)
		$jour_meme = mktime(23,59,59,date('m'),date('d'),date('Y'));
		//si on est lundi ou mardi, on affiche les rappels de jeudi et vendredi, ou vendredi :
		if(date('w') == '1' || date('w') == '2'){
			$deuxj_av = mktime(0,0,0,date('m'),date('d'),date('Y')) - ((24*60*60) * 4);
		}
		else{
			$deuxj_av = mktime(0,0,0,date('m'),date('d'),date('Y')) - ((24*60*60) * 2);
		}
		while($commercial=$DB_site2->fetch_array($commercials)){
			///Récupère les suivis externes donnés par les commerciaux :
			$suivis_ext = $DB_site2->query("SELECT p.*, u.nom, u.raisonsociale, u.prenom FROM projet p INNER JOIN utilisateur u ON u.userid = p.userid WHERE p.commercial = '".$commercial['username']."' AND rappel_commercial ='externe' AND etatid NOT IN ('5','6')");
			$nb_suivi_ext = $DB_site2->num_rows($suivis_ext);
			$liste_suiviext = "";
			if($nb_suivi_ext > 0){
				$i = 1;
				$max = isset($_GET[$commercial['username'].'_ext']) ? $_GET[$commercial['username'].'_ext'] + 1 : 11;
				while($suivi_ext=$DB_site2->fetch_array($suivis_ext)){
					$type_ech = "";
					if($i < $max){
						$projet = new projet($suivi_ext['projetid']);
						$datecmd=$projet->datecmd();
						if(!is_null($datecmd)){
							$datecmdcolor=$datecmd<new datetime('today 0:0:0')?"red":"";
							$datecmd=$datecmd->format("d/m/Y");
						}
						$nom=($suivi_ext['raisonsociale']!='' ? $suivi_ext['raisonsociale'] : $suivi_ext['nom']." ".$suivi_ext['prenom']);
						if(!is_null($suivi_ext['echeance_type'])){
							if($suivi_ext['echeance_type'] == '1'){
								$type_ech = " - <div title='info donné par le client' style='display:inline;'>C</div>";
							}
							else{
								$type_ech = " - <div title='estimé par mcav' style='display:inline;'>M</div>";
							}
						}
						$liste_suiviext .= "<div style='height:21px;overflow-y:hidden;padding:1px;' onmouseover='affich_info(this)' onmouseout='affich_info(this,\"out\")'><strong style='color:red;'>".date('d/m', $suivi_ext['rappel'])."</strong> - <a href='projet.php?projetid=".$suivi_ext['projetid']."' target='_blank' ".($suivi_ext['raisonsociale']!='' ? "style='color:#0393C2;font-weight:bold'" : "").">".substr($nom,0,18). "...</a> - ".$suivi_ext['budget']."€ - ".$datecmd.$type_ech."
						<span style='display:none;position: absolute; padding: 3px; border: 2px solid black; border-top: none;min-width:200px;margin-left:-3px;'>".$projet->pjt." - ".$projet->etat." - ".$projet->libelle."</span></div>";
						$i++;
					}
				}
			}

			$tableau_comm .= "<div class='flex_div'>
			<div class='titre_flex' style='background-color: yellow;'><span style='font-size:15px;'><a href='projets_new.php?action=suiviexterne".(isset($_GET[$commercial['username'].'_ext']) ? '' : '&'.$commercial['username'].'_ext='.$nb_suivi_ext)."'>voir tout</a> ".$nb_suivi_ext."</span> suivi externe ".$commercial['username']."</div>
			".$liste_suiviext."
			</div>";
		}
	}
	?>

	<div class="flex_projets">
		<?= $tableau_comm ?>
	</div>
<?php
}
//// FIN ////
include "./footer.php";

?>