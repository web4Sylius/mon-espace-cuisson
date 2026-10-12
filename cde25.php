<?php


require ("global.php");


//ici on fais afficher les balise hreflang pour fr et en
foreach($template->sites() as $site_code=>$site){
	foreach($site['hosts'] as $url=>$deflangue){
		if($site['actif'][$url])
			$template->parts["alternate"].="<link rel=\"alternate\" hreflang=\"".$site['locales'][$deflangue]."\" href=\"https://www.".$url."/cde25.php\">\n";
	}
}
//redirection si pro
/*if(($user_info['raisonsociale'] != "" && !$sitepro) || ($user_info['raisonsociale'] == "" && $sitepro)){ 
	$userid=$user_info['userid'];
	$s=$_COOKIE['s'];
	$prenom=$user_info['prenom'];
	redirectionPro($_SERVER['SCRIPT_NAME'], $_SERVER['HTTP_HOST'], $template,$userid,$s,$prenom);
}*/


$libelle="libelle".$template->langue;

$pays_sfg=array("57","181","111","99","17","158");
$pays_gcp=array("57","17");
$sql="";

if(isset($_SESSION['facture_id']) && $_SESSION['facture_id'] != ''){
	 
	 $factureid = $_SESSION['facture_id'];
	 //unset($_SESSION['facture_id']);
	 
} else {
	 
	 $factureid = $_POST['factureid'];
}

if(intval($factureid)==0) header("location:/");

$facture=$DB_site->query_first("SELECT * FROM facture WHERE facturid=".intval($factureid));
if($facture['facturid']!=$factureid || $facture['userid']!=$user_info['userid']) header("location:/");


$prixtotalpanier=$totalpaht=$poidtotal=0;
$poidscaves=array();
$artids=array();
$prixtypeinstall="";
$user_info=$DB_site->query_first("SELECT u.*,p.tvaintracom AS paysintracom,p.TVAtauxnormal FROM utilisateur u INNER JOIN pays p USING(paysid) WHERE userid = '$user_info[userid]'");
$lignefactures=$DB_site->query("SELECT lf.artid,lf.prix,qte,lf.paht,(a.emballage_poids1 + a.emballage_poids2 + a.emballage_poids3) AS emballage_poids,a.typearticleid FROM lignefacture lf INNER JOIN article a USING(artid) WHERE factureid=".intval($factureid));
while($lf=$DB_site->fetch_array($lignefactures)){
	$poidtotal+=1000*floatval(str_replace(",",".",$lf['emballage_poids']))* $lf['qte'];
	$prixtotalpanier+=$lf[prix]*$lf['qte'];
	$totalpaht+=$lf['paht']*$lf['qte'];
	$artids[$lf['artid']]+=$lf['qte'];
	if($lf['typearticleid']==1) $poidscaves[round($lf['emballage_poids'])]+=$lf[qte];
	install_type($lf['artid']);
}

if(isset($_POST['action2']) && $_POST['action2']=="cadeau"){
	$okvalide = 0 ;
	$okcorrespond = 0 ;
	$okdate = 0 ;
	$type_du_cadeau = "" ;
	$erreur_cadeau = "";

	if($facture['cadeauid']!=""){
		$cadeau=$DB_site->query_first("SELECT * FROM cadeau WHERE cadeauid='".$facture['cadeauid']."'");
		if($cadeau['cadeau_artid']!=0 && $cadeau!=""){
			$suppart=$DB_site->query_first("SELECT lignefactureid,artid,artcode,prix FROM lignefacture WHERE qte=1 AND factureid=$factureid AND artid=".$cadeau['cadeau_artid']);
			if($suppart['artid']!="") $DB_site->query("DELETE FROM lignefacture WHERE lignefactureid=".$suppart['lignefactureid']);
		}
		if($cadeau['code']!="") $DB_site->query("DELETE FROM lignefacture WHERE libelle LIKE '%".$cadeau['code']."%' AND artid=0 AND factureid=".$factureid);
		if($cadeau['port']!=0){
			$tarifsls=0;
			$fraisport=$DB_site->query_first("SELECT * FROM fraisport WHERE poiddebut <= '$poidtotal' AND poidfin >= '$poidtotal' and paysid = '".$user_info['paysid_livraison']."'");
			if($fraisport['gratuit']<=$prixtotalpanier) $fraisport['prix']=0;
			$prixport=$fraisport['prix'];
			$install=pays_install($user_info['paysid_livraison']);
			$payssls=payssls();
			if ($facture['modelivraison']==1 && $install!=false){
				if($install=="sls") $tarifsls=prixsls($user_info['codepostal_livraison'],$poidtotal,$user_info['paysid_livraison'],$prixtypeinstall);
				if($install=="enigma") $tarifsls=prix_enigma($poidscaves,$template->site_code());
			}
			$prixport=floatval($prixport+$tarifsls);

			$DB_site->query("UPDATE facture SET prixport='$prixport' WHERE facturid=".intval($factureid));
		}
		$DB_site->query("UPDATE facture SET montantcadeau=0, cadeauid=0 WHERE facturid=".$factureid);
		$DB_site->query("DELETE FROM lignefacture WHERE artcode='Code caviste' AND factureid=".$factureid);
	}

	if (!isset($txt_code) || $txt_code == ""){
		$erreur_cadeau = $template->translate("/commande/code_pas_saisi","F"); 
	} else { // Code saisit
		$txt_code = mysqli_real_escape_string($DB_site->link_id,$txt_code);
		$cadeau_en_cours=$DB_site->query_first("SELECT * FROM cadeau WHERE code=\"".$txt_code."\" AND site_code='".$template->site_code()."'");
		if ($cadeau_en_cours[cadeauid] != "" && ($cadeau_en_cours['userid']==0 || $cadeau_en_cours['userid']==$user_info['userid'])){ // Code existe
			if ($cadeau_en_cours[active] == '1'){ // Cadeau actif
				if ($cadeau_en_cours[datefin] == 0){ // pas de datefin
					$okdate = 1 ;
				}elseif ($cadeau_en_cours[datefin] != 0 and $cadeau_en_cours[datefin] > time()){ // datefin > ajd
					$okdate = 1 ;
				}else{
					$erreur_cadeau = $template->translate("/commande/code_perime","F");
				}

				if($cadeau_en_cours['minimum']>$prixtotalpanier){
					$okminimum=0;
					$erreur_cadeau=sprintf($template->translate("/commande/code_minimum","F"),$cadeau_en_cours['minimum']);
				}else
				$okminimum=1;

				if ($okdate == 1 && $okminimum==1){
					if ($cadeau_en_cours[nbrmaxi] != 0){ // nb fois total max
						$nb_fois_tot=$DB_site->query_first("SELECT count(*) FROM facture WHERE cadeauid = '$cadeau_en_cours[cadeauid]' AND facturid!='$factureid' AND valide IN (1,5)");
						if ($nb_fois_tot[0] < $cadeau_en_cours[nbrmaxi]){
							$okvalide = 1 ;
						}else{
							$erreur_cadeau = sprintf($template->translate("/commande/code_utlise_x_fois","F"),$cadeau_en_cours[nbrmaxi]);
							$okvalide=0;
						}
					}
					
					if ($cadeau_en_cours[nbrfois] != 0){ // nb fois total
						$nb_fois_user=$DB_site->query_first("SELECT count(*) FROM facture WHERE cadeauid = '$cadeau_en_cours[cadeauid]' AND userid = '$user_info[userid]' AND facturid != '$factureid' AND valide IN (1,5)" );
						
						if ($nb_fois_user[0] < $cadeau_en_cours[nbrfois]){ // nb fois par ustilisateur
							$okvalide = 1 ;
						}else{
							$erreur_cadeau = sprintf($template->translate("/commande/code_utlise_x_fois","F"),$cadeau_en_cours[nbrmaxi]);
							$okvalide=0;
						}
					}
					
					if (($cadeau_en_cours[nbrmaxi] == 0) and ($cadeau_en_cours[nbrfois] == 0)){
						$okvalide = 1 ;
					}
				}
				
			} else { // code existe pas
				$erreur_cadeau = $template->translate("/commande/code_invalide","F");
			}
			
		} else {
			$erreur_cadeau = $template->translate("/commande/code_invalide","F");
		}

		if($cadeau_en_cours==false && $okvalide==0 && $type_du_cadeau==""){
			$code_caviste=$DB_site->query_first("SELECT * FROM code_caviste WHERE code='$txt_code'");
			if($code_caviste['code']==$txt_code && $code_caviste['validite']>time()){
				$type_du_cadeau = "CAVISTE";
				$erreur_cadeau ="";
				$okvalide=$okcorrespond=1;
			}else
			$erreur_cadeau = $template->translate("/commande/code_invalide","F");
		}
	}

	if ($okvalide == 1 && $type_du_cadeau != "CAVISTE"){
		$categ_cadeau=$DB_site->query_first("select catid, cadeauid from categorie_cadeau where cadeauid = '$cadeau_en_cours[cadeauid]'");
		$article_cadeau=$DB_site->query_first("select artid, cadeauid from article_cadeau where cadeauid = '$cadeau_en_cours[cadeauid]'");
		$liste_cats_cde=$DB_site->query("select catid from lignefacture where factureid = '$factureid'") ;
		$liste_arts_cde=$DB_site->query("select artid from lignefacture where factureid = '$factureid'") ;
		$arts_cde = array() ;
		
		while ($liste_art_cde=$DB_site->fetch_array($liste_arts_cde)){
			$art_en_cours = $liste_art_cde[artid] ;
			$arts_cde[$art_en_cours] = $art_en_cours ; 
		}
		
		if ($categ_cadeau[cadeauid] == $cadeau_en_cours[cadeauid]){ // cadeau sur une categorie
			categories_filles ($categ_cadeau[catid]) ;
			$cat_en_cours = $categ_cadeau[catid] ;
			$cats_cde[$cat_en_cours] = $cat_en_cours ;
			$passe = 0 ;
			
			while ($liste_cat_cde=$DB_site->fetch_array($liste_cats_cde)){
				if (in_array ($liste_cat_cde[catid], $cats_cde)){
					$okcorrespond = 1 ;
					$passe = 1 ;
				}
			}
			
			if ($passe == 0)
				$erreur_cadeau = $template->translate("/commande/code_correspond_pas_article","F") ;
			else
				$type_du_cadeau = "CATEGORIE" ;
			
		}elseif($article_cadeau[cadeauid] == $cadeau_en_cours[cadeauid]) { // cadeau sur un article
			if (in_array ($article_cadeau[artid], $arts_cde)) {
				$okcorrespond = 1 ;
				$type_du_cadeau = "ARTICLE" ;
			} else {
				$erreur_cadeau = $template->translate("/commande/code_correspond_pas_article","F");
			}
		} else {
			$okcorrespond = 1 ;
			$type_du_cadeau = "GENERAL" ;
		}
	}

	if(!$venteflashok) eval( "\$TemplateCde2CommandeCadeau .= \"$templatecache[Cde2CommandeCadeau]\";" );

	if(($okvalide == 1) and ($okcorrespond == 1)){
		$prixreduction = 0 ;
		$cadeau=$DB_site->query_first("SELECT * FROM cadeau WHERE cadeauid = '$cadeau_en_cours[cadeauid]'");
		$tauxfrance=$DB_site->query_first("SELECT TVAtauxnormal, TVAtauxreduit, cee, tvaintracom FROM pays WHERE paysid = '57'");

		switch ($type_du_cadeau){
			case "CATEGORIE":
				$categ_cadeau=$DB_site->query_first("SELECT catid, cadeauid from categorie_cadeau where cadeauid = '$cadeau_en_cours[cadeauid]'");
				$sql="SELECT lignefacture.catid, lignefacture.prix, lignefacture.qte, lignefacture.lignefactureid, article.prixconseilleht, article.tva
					FROM lignefacture
					INNER JOIN article on (lignefacture.artid=article.artid)
					INNER JOIN article_marque ON(article.artid=article_marque.artid)
					WHERE factureid = '$factureid'";
				if($cadeau['marqueid']!=0) $sql.=" AND marqueid IN (".$cadeau['marqueid'].")";

				$liste_cats_cde=$DB_site->query($sql) ;
				categories_filles ($categ_cadeau[catid]) ;
				$cat_en_cours = $categ_cadeau[catid] ;
				$cats_cde[$cat_en_cours] = $cat_en_cours ;

				while ($liste_cat_cde=$DB_site->fetch_array($liste_cats_cde)){
					if (in_array ($liste_cat_cde[catid], $cats_cde)){
						$optionsttc=$DB_site->query_first("SELECT SUM(differenceprix) FROM lignefacturecaracteristique WHERE lignefactureid=$liste_cat_cde[lignefactureid]");
						$prix_de_la_ligne = ($liste_cat_cde[prix]-$optionsttc[0]) * $liste_cat_cde[qte] ;
						
						switch ($cadeau[typecadeauid]){
							case 0:
								if ($cadeau[valeurcadeau] < $prix_de_la_ligne)
									$prixreduction += $cadeau[valeurcadeau] ;
								else
									$prixreduction += $prix_de_la_ligne ;
								break ;
								
							case 1:
								if($cadeau[appliquer]=="PPC"){
									$modifcaract=0;
									$modifprixcaracts=$DB_site->query("SELECT lfc.differenceprix as prixcarac FROM article_caractval as ac inner join lignefacturecaracteristique as lfc on (ac.caractvalid=lfc.caractvalid) WHERE lfc.lignefactureid='$liste_cat_cde[lignefactureid]' and artid='$liste_cat_cde[artid]' ");
									while($modifprixcaract=$DB_site->fetch_array($modifprixcaracts)){
										$modifcaract+=$modifprixcaract['prixcarac'];
									}
									
									$garanties=$DB_site->query_first("SELECT SUM(differenceprix) AS differenceprixTotale FROM `lignefacturegarantie` WHERE `lignefactureid`='$liste_cat_cde[lignefactureid]'");
									$modifcaract+=$garanties['differenceprixTotale'];
									$prix_remise = round(($liste_art_cde[prixconseilleht] * (1+$liste_art_cde['tva']/100) + $modifcaract) * $liste_art_cde[qte] * ($cadeau[valeurcadeau] / 100),2);
									if($prix_remise<($liste_cat_cde[prix] * $liste_cat_cde[qte])){
										$reducligne=$prix_remise;
										$prixreduction += $prix_remise;
									} else {
										// on garde le prix de vente car la remise est désavantageuse
									}
								} else {
									$prixreduction += ($prix_de_la_ligne * $cadeau[valeurcadeau] / 100) ;

								}
								break ;
						}
			  
					}
				}
				if($prixreduction==0) $erreur_cadeau = $template->translate("/commande/code_correspond_pas_article","F");
				break ;
				
			case "ARTICLE":
				$article_cadeau=$DB_site->query_first("SELECT artid, cadeauid FROM article_cadeau WHERE cadeauid = '$cadeau_en_cours[cadeauid]'");
				$liste_arts_cde=$DB_site->query("SELECT lignefacture.artid, lignefacture.prix, lignefacture.qte, lignefacture.lignefactureid, article.prixconseilleht,lignefacture.tva,article.tauxchoisi FROM lignefacture INNER JOIN article ON(lignefacture.artid=article.artid) WHERE factureid = '$factureid'") ;
				while ($liste_art_cde=$DB_site->fetch_array($liste_arts_cde)){
					if ($liste_art_cde[artid] == $article_cadeau[artid]){
						$optionsttc=$DB_site->query_first("SELECT SUM(differenceprix) FROM lignefacturecaracteristique WHERE lignefactureid=$liste_art_cde[lignefactureid]");
						$prix_de_la_ligne = ($liste_art_cde[prix]-$optionsttc[0]) * $liste_art_cde[qte] ;
						switch ($cadeau[typecadeauid]){
							case 0:
								if ($cadeau[valeurcadeau] < $prix_de_la_ligne)
								$prixreduction += $cadeau[valeurcadeau] ;
								else
								$prixreduction += $prix_de_la_ligne ;
								break ;
							case 1:
								if($cadeau[appliquer]=="PPC"){ 
									$modifcaract=0;
									$modifprixcaracts=$DB_site->query("SELECT lfc.differenceprix FROM article_caractval as ac inner join lignefacturecaracteristique as lfc on (ac.caractvalid=lfc.caractvalid) WHERE lfc.lignefactureid='$liste_art_cde[lignefactureid]' and artid='$liste_art_cde[artid]' ");
									while($modifprixcaract=$DB_site->fetch_array($modifprixcaracts)){
										$modifcaract+=$modifprixcaract[differenceprix];
									}
									$garanties=$DB_site->query_first("SELECT SUM(differenceprix) AS differenceprixTotale FROM `lignefacturegarantie` WHERE `lignefactureid`='$liste_cat_cde[lignefactureid]'");
									$modifcaract+=$garanties['differenceprixTotale'];
									$prix_remise = round(($liste_art_cde[prixconseilleht] * (1+$liste_art_cde['tva']/100) + $modifcaract) * $liste_art_cde[qte] * ($cadeau[valeurcadeau] / 100),2);
									if($prix_remise<($liste_art_cde[prix] * $liste_art_cde[qte] * (1+$liste_art_cde['tva']/100))){
										$reducligne=$prix_remise;
										$prixreduction += $prix_remise;
									} else {
										// on garde le prix de vente car la remise est désavantageuse
									}
								} else {
								$prixreduction += ($prix_de_la_ligne * $cadeau[valeurcadeau] / 100) ;
								}
								  
						}
						break ;
					}
				}
				break ;
	
			case "GENERAL":
				$prixtotalpanier_sansfrais = 0;
				$sql = "SELECT lignefacture.artid, lignefacture.prix, lignefacture.qte, lignefacture.lignefactureid, article.prixconseilleht
					FROM lignefacture INNER JOIN article ON (lignefacture.artid=article.artid) INNER JOIN article_marque ON(article.artid=article_marque.artid) 
					WHERE factureid = '$factureid'";
				if($cadeau['marqueid'] != 0) $sql.= " AND marqueid IN (".$cadeau['marqueid'].")";
		
				$liste_arts_cde = $DB_site->query($sql) ;
				while ($liste_art_cde = $DB_site->fetch_array($liste_arts_cde)){
					$optionsttc = $DB_site->query_first("SELECT SUM(differenceprix) FROM lignefacturecaracteristique WHERE lignefactureid=$liste_art_cde[lignefactureid]");
					$prix_de_la_ligne = ($liste_art_cde[prix] - $optionsttc[0]) * $liste_art_cde[qte];
					$prixtotalpanier_sansfrais += $prix_de_la_ligne;
				}
				
				switch ($cadeau[typecadeauid]){
					case 0: // En euros
						if ($cadeau[valeurcadeau] < $prixtotalpanier_sansfrais){
							$prixreduction += $cadeau[valeurcadeau] ;
						}else{
							$prixreduction += $prixtotalpanier_sansfrais ;
						}
						if($DB_site->num_rows($liste_arts_cde)==0) $erreur_cadeau = $template->translate("/commande/code_correspond_pas_article","F");
						break ;
						
					case 1: // En pourcent
						if($cadeau[totalite] != 0){ // Appliquer sur la totalité de la commande
							if($cadeau[totalite] == 1){ // remise avec transport

								$fcl=new facture($DB_site,$factureid);
								$fcl->lignes();
								$prixreduction += $fcl->totalttc * $cadeau[valeurcadeau] / 100;
							
							}elseif ($cadeau[totalite] == 2) { //remise hors transport
								$fcl=new facture($DB_site,$factureid);
								$fcl->lignes();
								$prixreduction += ($fcl->totalttc - $fcl->portttc )* $cadeau[valeurcadeau] / 100;
							}
						} elseif($cadeau[appliquer] == "PPC"){ // Appliquer sur le PCC
							$sql = "SELECT lf.prix, lf.qte, lf.lignefactureid,lf.tva as vat, a.prixconseilleht, a.artid, a.tva, asite.tauxchoisi";
							$sql .= " FROM lignefacture lf";
							$sql .= " INNER JOIN article a ON (a.artid = lf.artid)";
							$sql .= " INNER JOIN article_marque am ON (am.artid = a.artid)";
							$sql .= " INNER JOIN article_site asite ON (asite.artid = a.artid)";
							$sql .= " WHERE lf.factureid = '".$factureid."'";
							$sql .= " AND asite.site_code = '".$template->site_code()."'";
							if($cadeau['marqueid'] != 0) $sql .= " AND marqueid IN (".$cadeau['marqueid'].")";
							$prix_normaux = $DB_site->query($sql);
							while($prix_normal = $DB_site->fetch_array($prix_normaux)){
								$modifcaract = 0;
								$modifprixcaracts=$DB_site->query("SELECT lfc.differenceprix as prixcarac,lfc.*,ac.* FROM article_caractval as ac inner join lignefacturecaracteristique as lfc on (ac.caractvalid=lfc.caractvalid) WHERE lfc.lignefactureid='$prix_normal[lignefactureid]' and artid='$prix_normal[artid]' ");
								while($modifprixcaract = $DB_site->fetch_array($modifprixcaracts)){
									$differenceprixht = unserialize($modifprixcaract[differenceprixht]);
									$modifcaract += intval($modifprixcaract['prixcarac']);
								}
								$garanties = $DB_site->query_first("SELECT SUM(differenceprix) AS differenceprixTotale FROM `lignefacturegarantie` WHERE `lignefactureid`='$prix_normal[lignefactureid]'");
								$modifcaract += $garanties['differenceprixTotale'];
								//$bonprix = round( ($prix_normal['prixconseilleht'] + $modifcaract) * $prix_normal['qte'] * (1+ $template->tva_ref($prix_normal['tauxchoisi'])/100) * (1 - ($cadeau['valeurcadeau'] / 100) ),2) ;
								$prix_remise = round(($prix_normal[prixconseilleht] * (1+$template->tva_ref($prix_normal['tauxchoisi'])/100) + $modifcaract) * $prix_normal[qte] * ($cadeau[valeurcadeau] / 100),2) ;
								if($prix_remise < ($prix_normal[prix] * $prix_normal[qte])){
									$reducligne = $prix_remise;
									$prixreduction += $prix_remise;
								} else {
									// on garde le prix de vente car la remise est désavantageuse
								}
							}
							if($DB_site->num_rows($prix_normaux) == 0) $erreur_cadeau = $template->translate("/commande/code_correspond_pas_article","F");
						} else { // Appliquer sur le PV
							$sql = "SELECT lignefacture.prix, lignefacture.qte, lignefacture.lignefactureid, article.prixconseilleht, article.artid 
								FROM lignefacture 
								INNER JOIN article ON (lignefacture.artid=article.artid)
								INNER JOIN article_marque ON (article.artid=article_marque.artid)
								WHERE factureid = '$factureid'";
							if($cadeau['marqueid'] != 0) $sql.= " AND marqueid IN (".$cadeau['marqueid'].")";
						
							$lignes = $DB_site->query($sql);
							while($ligne = $DB_site->fetch_array($lignes)){
								$optionsttc = $DB_site->query_first("SELECT SUM(differenceprix) FROM lignefacturecaracteristique WHERE lignefactureid=$ligne[lignefactureid]");
								$garanties = $DB_site->query_first("SELECT SUM(differenceprix) AS differenceprixTotale FROM `lignefacturegarantie` WHERE `lignefactureid`='$ligne[lignefactureid]'");
								//$prixreduction += $ligne[qte] * ($ligne[prix] - $optionsttc[0] - $garanties[0]) * $cadeau[valeurcadeau] / 100; pour enlever le prix de l'option dans la garantie
								$prixreduction += $ligne[qte] * ($ligne[prix] - $garanties[0]) * $cadeau[valeurcadeau] / 100;
							}
							if($DB_site->num_rows($lignes)==0) $erreur_cadeau = $template->translate("/commande/code_correspond_pas_article","F");
							//$prixreduction += ($prixtotalpanier_sansfrais * $cadeau[valeurcadeau] / 100) ;
						}
				}
				break ;
		}

	if($cadeau['option']!=""){
		$options=explode(',',$cadeau['option']);
		$r=$DB_site->query("SELECT lfc.caractvalid,lfc.differenceprix,lfc.lignefacturecaracteristiqueid,lfc.libelle_caract,lf.lignefactureid,lf.prix,lf.artcode,lf.tva,lf.qte FROM lignefacture lf INNER JOIN lignefacturecaracteristique lfc ON(lfc.lignefactureid=lf.lignefactureid) WHERE lf.factureid=$factureid");
		while($lfc=$DB_site->fetch_array($r)){
		if(in_array($lfc['caractvalid'],$options)){
			$totalttc[$lfc['tva']]-=($lfc['qte']*$lfc['differenceprix']);
			$prixtva-=$lfc['qte']*($lfc['differenceprix']-($lfc['differenceprix']/(1+$lfc['tva']/100)));
			$cadeauoffert.="<br>Option offerte : ".$lfc['libelle_caract']." ".$lfc['artcode'];
			$DB_site->query("INSERT INTO lignefacture SET factureid=$factureid, artid=0, catid=0, artcode='', qte=$lfc[qte], libelle='".$template->translate("/commande/bon_reduction","F")." $cadeau[code] : ".$template->translate("/commande/en_cadeau")." $lfc[libelle_caract] $lfc[artcode]', prix=".(-$lfc['differenceprix']).", tva=$lfc[tva]");
		}
		}
	}

	if($cadeau['port']!=""){
		  $port=$DB_site->query_first("SELECT prixport,modelivraison FROM facture WHERE facturid=".$factureid);
		  if(($cadeau['port']==1 || $cadeau['port']==3) && $port['modelivraison']==0){
		  $tarif=0;
		  $DB_site->query("UPDATE facture SET prixport = '$tarif' WHERE facturid = '$factureid'");
		  }
		  if(($cadeau['port']==2 || $cadeau['port']==3) && $port['modelivraison']==1){
		  $tarif=0;
		  $DB_site->query("UPDATE facture SET prixport = '$tarif' WHERE facturid = '$factureid'");
		  }
		  
	}

	if($cadeau['cadeau_artid']!=0){
		$artoffert=$DB_site->query_first("SELECT * FROM article WHERE artid=".$cadeau['cadeau_artid']);
		$tauxfrance=$DB_site->query_first("SELECT TVAtauxnormal, TVAtauxreduit,cee,tvaintracom FROM pays WHERE paysid = '57'");
		
		if($artoffert['artid']!=""){
		$taux=$DB_site->query_first("SELECT TVAtauxnormal AS taux1, TVAtauxreduit AS taux2,cee,tvaintracom FROM pays WHERE paysid = '$user_info[paysid]'");
		$prix_cadeau=article_prices($cadeau['cadeau_artid'],$taux,$template->site_code(),$user_info['tvaintracom']!="" && $taux['tvaintracom']==1);
		

		$DB_site->query("INSERT INTO lignefacture SET factureid=$factureid, artid=$artoffert[artid], catid=$artoffert[catid], artcode='$artoffert[artcode]', qte=1, libelle='".mysqli_real_escape_string($DB_site->link_id,$artoffert[libelleFR])."', prix=$prix_cadeau[prix], tva=$prix_cadeau[tauxtva], paht=$artoffert[paht]");
		$DB_site->query("INSERT INTO lignefacture SET factureid=$factureid, artid=0, catid=$artoffert[catid], artcode='$artoffert[artcode]', qte=1, libelle='".addslashes($template->translate("/commande/bon_reduction","F")." $cadeau[code] : ".$template->translate("/commande/en_cadeau")." $artoffert[libelleFR] $artoffert[artcode]")."', prix=".(-$prix_cadeau[prix]).", tva=$prix_cadeau[tauxtva]");
		$cadeauoffert.="<br>".$template->translate("/commande/en_cadeau","F")." : $artoffert[libelleFR]";
		$texteecopart="";
		$libellerewrite = url_rewrite($artoffert[$libelle]) ;

		}
	}

	$cadeau_existant=$DB_site->query_first("select montantcadeau from facture where facturid = '$factureid'");
	if ($cadeau_existant[montantcadeau] != 0){
		if ($prixreduction > $cadeau_existant[montantcadeau]){
			$prixreduction  = number_format($prixreduction, 2, ".", "");
			$DB_site->query("update facture set cadeauid = '$cadeau_en_cours[cadeauid]', montantcadeau = '$prixreduction' where facturid = '$factureid'");
		}else{
			$prixreduction = $cadeau_existant[montantcadeau] ;
		}
	}else{
		$DB_site->query("update facture set cadeauid = '$cadeau_en_cours[cadeauid]', montantcadeau = '$prixreduction' where facturid = '$factureid'");
	}
	if ($prixreduction == 0)
		$libelle_supp = "$multilangue[noncumulable]" ;

	$prixtotalpanier -= $prixreduction;

	$prixreductva=$prixreduction;
	$montantcadeau=$prixreduction;
	$prixreduction  = number_format($prixreduction, 2, ",", " ") ;

	// gestion code caviste
	if($type_du_cadeau == "CAVISTE"){
		$valeur=0;
		$valeurorigine=0;
		$caviste=$DB_site->query_first("SELECT * FROM caviste WHERE cavisteid='$code_caviste[caviste]'");
		$liste_arts_cde=$DB_site->query("select * from lignefacture where factureid = '$factureid'") ;
		while ($liste_art_cde=$DB_site->fetch_array($liste_arts_cde)){
				 
				 //On regarde s'il y a un pris temporaire appliqué au produit
				 $sql = "SELECT a.artcode, tp.prix, ROUND(tp.prix / (1 + (".$tauxfrance['TVAtauxnormal']."/100)),2) as prixht, tp.prixconseilleht";
				 $sql .= " FROM temp_price tp";
				 $sql .= " INNER JOIN article a USING (artid)";
				 $sql .= " WHERE tp.site_code = 'base'";
				 $sql .= " AND a.artcode LIKE '".$liste_art_cde['artcode']."'";
				 $sql .= " AND UNIX_TIMESTAMP(NOW()) BETWEEN tp.du AND tp.au";
				 $sql .= " LIMIT 0,1";
				 $article_temp = $DB_site->query_first($sql);
				 
				 if(empty($article_temp)){ //Si pas de prix temporaire en cours, on récupère le tarif classique
					  
					$sql = "SELECT artcode, prix, prixht, prixconseilleht";
					$sql .= " FROM article";
					$sql .= " WHERE artcode = '".$liste_art_cde['artcode']."'";
					$article = $DB_site->query_first($sql);
					  
				 } else { //Sinon, on prend en considération le prix temporaire pour les différents calculs
					  
					  $article = $article_temp;
					  unset($article_temp);
					  
				 }
		  
		  $pc="pc";
		  if($article['prixht']<$article['prixconseilleht']) $pc="pcpromo";
		  $optionsttc=$DB_site->query_first("SELECT SUM(differenceprix) FROM lignefacturecaracteristique WHERE lignefactureid=$liste_art_cde[lignefactureid]");
		  $valeur+=$liste_art_cde['qte']*($liste_art_cde['prix']-$optionsttc[0])*($code_caviste[$pc]/100);
		  $valeurorigine+=$liste_art_cde['qte']*$liste_art_cde['prix'];
		}

	    if($code_caviste['type']==1){
		  if($valeur<$code_caviste['mini']){
			  $prixtotalpanier-=$valeur;
			  $prixreductva=$valeur;
			  $montantcadeau=$valeur;
			  $DB_site->query("UPDATE facture SET cadeauid='$code_caviste[code]', montantcadeau=$montantcadeau WHERE facturid=$factureid");
		  }else{
		      $libellekdo=mysqli_real_escape_string($DB_site->link_id,sprintf($template->translate("/commande/caviste_bon_achat","F"),$code_caviste['code'],number_format($valeur,2,',',''),$caviste['raisonsociale'],ucfirst($caviste['ville'])));
		      $DB_site->query("INSERT INTO lignefacture SET factureid='$factureid', artid='0', catid='0', artcode='Code caviste', qte='1', libelle='".$libellekdo."', prix='0', tva='".$template->tva_ref("taux1")."'");
		      $DB_site->query("UPDATE facture SET cadeauid='$code_caviste[code]', montantcadeau=$valeur WHERE facturid=$factureid");
		  }
	    }
	    if($code_caviste['type']==2){
		  $prixtotalpanier-=$valeur;
		  $prixreductva=$valeur;
		  $montantcadeau=$valeur;
		  $DB_site->query("UPDATE facture SET cadeauid='$code_caviste[code]', montantcadeau=$montantcadeau WHERE facturid=$factureid");
	    }

	}
    }

    $template->parts["errorpromo"]=$erreur_cadeau;
}


$fcl=new facture($DB_site,$factureid);
$fcl->lignes();

$template->parts["artstatic"]=" static";

//Boucle sur les articles de la commande
$colisage = true;
$tabArticlesColisage = array();
$nxtckitems = array();
foreach($fcl->lignes as $lfid=>$lignefacture){
     $edition_garantie = false;
	 
     $TemplateCde1BitGarantieBit=$TemplateCde1BitCaracteristiquesBit = "";
     $image = "";
     $wide = "wide";

     //Si l'article n'est pas une promotion, ni un frais supplémentaire quelconque appliqué (ex: frais de gestion)
     if($lignefacture->artid > 0){  
         
	$sql = "SELECT artid, artcode,  emballage_hauteur1, emballage_largeur1, emballage_profondeur1, emballage_poids1";
	$sql .= " FROM article";
	$sql .= " WHERE artid = ".$lignefacture->artid;
	$tabColisage = $DB_site->query_first($sql);

	if((empty($tabColisage['emballage_hauteur1']) || empty($tabColisage['emballage_largeur1']) || empty($tabColisage['emballage_profondeur1']) || empty($tabColisage['emballage_poids1']))){
	      $colisage = false;
	      $tabArticlesColisage[$tabColisage['artcode']] = $tabColisage['artid'];
	}
         
	$autreimg=$DB_site->query_first("SELECT * FROM articlephoto WHERE artid=".$lignefacture->artid." AND visible=1 AND (langue='' OR langue='".$template->langue."')");
	$autre="";
	if($autreimg!="") $autre="_".$autreimg['articlephotoid'];
	$image="<img src=\"/images/".$lignefacture->artid.$autre."_47_84.jpg\">";
	$wide="";
	
	$tabGarantie = DAOgarantie::getBestGarantiesByProduitCmd($DB_site, $lignefacture->lignefactureid);
	$lignefacturegarantie = $DB_site->query_first("SELECT SUM(duree) as duree FROM lignefacturegarantie WHERE lignefactureid=".$lignefacture->lignefactureid);
	if(!empty($tabGarantie)){
	      
		  $garantiesPossibles = DAOgarantie::loadGarantiesPayArtByPays($DB_site,$template->site_code(),'FR',$user_info['paysid'],$lignefacture->artid, !empty($user_info['raisonsociale']) ? 2 : 1, $lignefacture->prix_nogarantie,$lignefacture->tva);
		  
		   if(count($garantiesPossibles) > 0) $edition_garantie = true;
		  
	      $duree = $tabGarantie['duree'] == 3 && $tabGarantie['cumul'] == 1 ? 5 : $tabGarantie['duree'];
	      $an    = $template->translate("/article/garantie_type/an");
	      $type  = $tabGarantie['type_garantie'];
	      //Si abonnement platine home
	      if($type == 6){
	      	$prix_abo = $DB_site->query_first("SELECT offre_prix, count FROM platinehome WHERE lignefactureid ='".$lignefacture->lignefactureid."'");

	      	if(intval($lignefacture->qte) > 1) // quantité multiple
            	$prix = ($lignefacture->qte * $prix_abo['count']) . " x " . $prix_abo['offre_prix'];
            else{
            	$count ="";
            	if($prix_abo['count'] > 1) $count = $prix_abo['count'] . " x " ;
            	$prix = $count . $prix_abo[0];
            }
	      
	      }else{
	      	//garantie normale
	      	$prix  = $tabGarantie['prix'];
	      }

	      if ($duree > 1){
		  $an = $template->translate("/article/garantie_type/ans");
	      }
	      
	      $intitule_destinataire = '';
	      if($fcl->raisonsociale != ''){
		  switch($tabGarantie['garantie_client_id']){
			case 1:
			    $intitule_destinataire = '"PARTICULIER"';
			    break;
			case 2:
			    $intitule_destinataire = '"PRO"';
			    break;
		  }
	      }
	      if($type == 6){
	      	$TemplateCde1BitGarantieBit .= $template->translate("/garanties/garantie",'F'). " ".$template->translate("/article/garantie_type/".$type)." <span class=\"prix\" style=\"margin-left:10px;\">".$prix."€ /".$template->translate("/garanties/mois");
	      }else{
	      	$TemplateCde1BitGarantieBit .= $template->translate("/garanties/garantie",'F').' '.$duree." ".$an." ".$template->translate("/article/garantie_type/".$type)." <span class=\"prix\">(".($prix > 0 ? $template->price_format($prix) : $template->translate("/garanties/offerte",'F')).")</span>";
	      }
	      
	}
	
	/*if ($garantieDeBaseAffiche!="") {
		//cocher par defaut pas de garantie sup
		$checked='checked';
		$TemplateCde1BitGarantieBit.="<form method=\"post\" name=\"frmchoixGarantie".$lignefacture->lignefactureid."\" action=\"cde25.php#ancre".$lignefacture->artid."\">";
		$TemplateCde1BitGarantieBit.="<input type='hidden' value='$factureid' name='factureid'>";
		$TemplateCde1BitGarantieBit.="<input type='hidden' value='garantie' name='action2'>";
		$TemplateCde1BitGarantieBit.="<input type='hidden' value='".$lignefacture->artid."' name='numArt'>";
		$TemplateCde1BitGarantieBit.="<input type='hidden' value='".$lignefacture->lignefactureid."' name='IDLigne'>";
		$date = new DateTime(date('Y-m-d'));
		if ($dureeDeBaseAf!="") {
			$date->modify('+'.$dureeDeBaseAf.' year');
			$date->modify('-1 day');
		}
		$date = $template->translate("/site/mois/".$date->format('n'))." ".$date->format('Y');
		
		$garantiesPossibles=DAOgarantie::loadGarantiesPayArtByPays($DB_site,$template->site_code(),'FR',$user_info['paysid'],$lignefacture->artid);
		$nbgarantiesPossibles=count($garantiesPossibles);
		$TemplateCde1BitGarantieBit.="<input type=\"".($nbgarantiesPossibles>0?"radio":"hidden")."\" onClick=\"document.frmchoixGarantie".$lignefacture->lignefactureid.".submit()\" name=\"choixGarantie\" value=\"0\"/ ".$checked."/>".$garantieDeBaseAffiche." (".$template->translate("/article/garantie_type/valable_jusqu_en")." ".$date.") - <span style=\"font-weight:bold;\">".$template->translate('/article/garantie_type/offerte','F')."</span>".$auLieuDe."<br/>";

		if ($nbgarantiesPossibles>0) {
			$TemplateCde1BitGarantieBit.="<div class=\"tranquillite\">
			<div class=\"conseil\">".$template->translate("/article/garantie_type/conseil_expert","F")."</div>
			<p class=\"plustranquille\">".$template->translate("/article/garantie_type/tranquillite","F")."</p>";
			for ($i=0; $i < count($garantiesPossibles); $i++) {
				$duree=$garantiesPossibles[$i]->duree;
				$ppi=$garantiesPossibles[$i]->ppi;
				$prix=$garantiesPossibles[$i]->prix;

				if ($garantiesPossibles[$i]->cumul==1) {
					$allGaranties=DAOgarantie::loadGarantiesArtByPaysSaufExtensions($DB_site,$template->site_code(),'FR',$user_info['paysid'],$lignefacture->artid);
					$pointer=DAOgarantie::searchGarantieToAddExtension($DB_site,$garantiesPossibles[$i]->type_garantie,$allGaranties,$garantiesPossibles[$i]->garantieid);
					if ($allGaranties[$pointer]->prix) {
						$ppi+=$allGaranties[$pointer]->ppi;
					}
					$duree+=$allGaranties[$pointer]->duree;
					$prix+=$allGaranties[$pointer]->prix;
				}

				$an=$template->translate("/article/garantie_type/an");
				if ($duree>1) $an=$template->translate("/article/garantie_type/ans");
				
				$checked='';
				$auLieuDe="";
				if ($ppi>$prix) {
					$auLieuDe=" ".$template->translate("/article/garantie_type/au_lieu_de")." ".$template->price_format($ppi,0);
				}
				if (DAOlignefacturegarantie::loadLigneFactureGarantieByLigneFacEtGarantie($DB_site,$lignefacture->lignefactureid,$garantiesPossibles[$i]->garantieid)!==false) {
					$checked='checked';
				}
				$date = new DateTime(date('Y-m-d'));
				$date->modify('+'.$duree.' year');
				$date->modify('-1 day');
				$date = $template->translate("/site/mois/".$date->format('n'))." ".$date->format('Y');
				$TemplateCde1BitGarantieBit.="<input type='hidden' value='$duree' name='duree'>";
				$TemplateCde1BitGarantieBit.="<input type=\"radio\" onClick=\"document.frmchoixGarantie".$lignefacture->lignefactureid.".submit()\" name=\"choixGarantie\" value=\"".$garantiesPossibles[$i]->garantieid."\" ".$checked."/>".$duree." ".$an." ".$template->translate("/article/garantie_type/".$garantiesPossibles[$i]->type_garantie)." (".$template->translate("/article/garantie_type/valable_jusqu_en")." ".$date.") "..$garantiesPossibles[$i]->getPays()." - <span style=\"font-weight:bold;\">".$template->price_format($prix,0)."</span>".$auLieuDe."<br/>";
			}
			$TemplateCde1BitGarantieBit.="</div>";
		}
		$TemplateCde1BitGarantieBit.="</form>";
	}*/
    }
    $template->parts["options"]="";
    $caracts=$lignefacture->caracteristiques25(true);
    foreach($caracts as $caractid=>$txt){
	    $template->parts["options"] .= "<div class=\"opt_ligne\">
	    <p class=\"libelle_option\">
	      <b>$txt[0]</b>
	      <span>$txt[1]</span>
	    </p>
	    </div>";
    }

    $gaitems.="pageTracker._addItem('".$fcl->facturid."','".$lignefacture->artcode."','".html_entity_decode($lignefacture->libelle)."','','".$lignefacture->prix."','".$lignefacture->qte."');\n";
    $itmnb++;
//     $criteoitems.="&i$itmnb=".$lignefacture->artid."&p$itmnb=".number_format($lignefacture->prix,2,'.','')."&q$itmnb=".$lignefacture->qte;
    $criteoitems[]=array("id"=>strval($lignefacture->artid),"price"=>(float)number_format($lignefacture->prix,2,'.',''),"quantity"=>(int)$lignefacture->qte);
    
    //Tracking NextPerformance
    $nxtckitems[] = $lignefacture->artid;
    
    if ($TemplateCde1BitGarantieBit!="") {
	    $template->parts["options"].="<div class=\"opt_ligne\">
	    <p class=\"libelle_option\">
	      <b>".$template->translate("/article/garantie_type/vos_garanties",'F')." : ".($edition_garantie ? "<span class=\"fa fa-pencil-square-o\" title=\"Modifier\" onclick=\"etape4();\"></span>" : "")."</b>
	      <span style=\"margin-left:0;\">$TemplateCde1BitGarantieBit</span>
	    </p>
	    </div>";
    }
    
    $template->parts["td1"]=
	$image
	."<div class=\"libelle $wide\">"
	  .$lignefacture->libelle
	  ."<span class=\"marque\">".$lignefacture->marque."</span>
	  <span class=\"ref\">".$lignefacture->artcode."</span>
	</div>";
	

    $template->parts["lignepu"]=$template->price_format($lignefacture->prix);
    $template->parts["prixligne"]=$template->price_format($lignefacture->prix*$lignefacture->qte);
    $template->parts["ligneqte"]=$lignefacture->qte;
  
    $template->parts["lignes"].="<div class=\"row\">
        <div class=\"col-lg-8 col-sm-6 col-xs-12\">
	      <div class=\"options col-lg-4 col-sm-6 col-xs-12 no-padding\">".$template->parts["td1"]."</div>
	      <div class=\"options col-lg-8 col-sm-6 col-xs-12\">".$template->parts["options"]."</div>
        </div>
        <div class=\"col-lg-4 col-sm-6 col-xs-12\">
	      <div class=\"price  col-md-4 col-sm-4 col-xs-4\">".$template->parts["lignepu"]."</div>
	      <div class=\"qte  col-md-2 col-sm-2 col-xs-2\">".$template->parts["ligneqte"]."</div>
	      <div class=\"price col-md-6 col-sm-6 col-xs-6 right\">".$template->parts["prixligne"]."</div>
	    </div>
      </div>";
}

if($fcl->cadeautype!=null){
    switch($fcl->cadeautype){
      case "general":
      case "article":
      case "rayon":
      $template->parts["codepromo"]=$cadeau['code'];
      $template->parts["codebon"]=$template->translate("/commande/bon_reduction","F")." ".$cadeau['code'];
      $template->parts["reducval"]=$template->price_format(-$fcl->cadeauttc);
      break;

      case "caviste1":
      if($fcl->cadeauttc>0){
	$template->parts["codebon"]=sprintf($template->translate("/commande/code_remise_partenaire","F"),$code_caviste['code']);
	$template->parts["reducval"]=$template->price_format(-$fcl->cadeauttc);
      }else{
	$template->parts["codebon"]=sprintf($template->translate("/commande/code_bon_achat","F"),$code_caviste['code']);
	$template->parts["reducval"]="";
      }
      $template->parts["codepromo"]=$code_caviste['code'];
      break;
      case "caviste2":
      $template->parts["codepromo"]=$code_caviste['code'];
      $template->parts["codebon"]=sprintf($template->translate("/commande/code_remise_partenaire","F"),$code_caviste['code']);
      $template->parts["reducval"]=$template->price_format(-$fcl->cadeauttc);
      break;
    }
    if($fcl->cadeauttc>0) $template->evaltemplate("LigneCadeau","lignes".($template->mobile?"_mobile":""));
}
if($fcl->modelivraison == 5){
	$template->parts["txtport"] = $template->translate("/commande/mode_livraison_".$fcl->modelivraison,"F")." - ".$fcl->txtlivr;
}else
	$template->parts["txtport"]=$template->translate("/commande/mode_livraison_".$fcl->modelivraison,"F");

if($fcl->installation_ttc > 0) $template->parts["txt_installation"] = '<tr class="ht"><td>Installation & mise en service :</td><td class="price">'.number_format($fcl->installation_ttc,2,',','').' &euro;</td></tr>';	

$template->parts["prixport"]=$template->price_format($fcl->portttc);
$template->parts["prixtotalht"]=$template->price_format($fcl->totalht);
$totaltva=$fcl->totalttc-$fcl->totalht;
$template->parts["prixtva"]=$template->price_format($totaltva);
$template->parts["prixtotalpanier"]=$template->price_format($fcl->totalttc);
$template->parts["facturid"]=$fcl->facturid;
$template->parts["datelivraison"]=date("d/m/Y",$fcl->datelivraison);

$rs_facture_coordonnees=$DB_site->query_first("SELECT raisonsociale, nom, prenom, adresse, codepostal, ville, pays.paysid,
						      pays.libelle,telephone FROM facture INNER JOIN pays ON (facture.paysid=pays.paysid)
						      WHERE userid='$user_info[userid]' AND facturid='$factureid'" );


$ecommerce="pageTracker._addTrans('".$fcl->facturid."','Ma Cave à vin', '".$fcl->totalttc."','','".$fcl->pportttc."','".$fcl->ville."','','".$fcl->pays."');
$gaitems
pageTracker._trackTrans();";
if($itmnb>0) $ecommerce.="\ndocument.write(\"<img src='\"+document.location.protocol+\"//\"+((document.location.protocol=='http:')?\"macaveavinfr.widget\":\"sslwidget\")+\".criteo.com/pmo/display.js?p1=\"+escape(\"v=2&wi=7713655&s=1&t=".$facture->facturid.$criteoitems."\")+\"&t1=transaction&resptype=gif' width='1' height='1' />\");";

$jsoncriteoitems=json_encode($criteoitems);

$template->parts["criteo"].="<script src=\"http://nxtck.com/act.php?tag=36949;id=".$fcl->facturid.";mt=".$fcl->totallignesht."\"></script>";

///pour afficher une alert devis pour les clients, si pas cookie admin, au lieu de la fenetre (tache 7095) :
if(empty($_COOKIE['admin_username']) || $_COOKIE['admin_username']==''){
	$template->parts["client"] = ",'client'";
}

$moyen_order=array(1,16,13,6,4,5,2,14,17,18);

// moyens de paiement   
$moyens_paiement=$DB_site->query("select * from moyenpaiement where accepte = '1'");
if ($DB_site->num_rows($moyens_paiement) > 0){

      //On récupère la liste des références présentes dans la commande
      $affiche_sofinco = true;
      foreach($fcl->lignes as $ligne_facture_id => $le_produit){

	      if($le_produit->artid > 0){

		      $oArticle = new produit($DB_site, $template->langue, $template->site_code(), $template->tva_ref(), $le_produit->artid);
		      //on ne peut pas financer le vin. On affiche sofinco pour le vin seulement si il est offert
		      if($oArticle->categorie[count($oArticle->categorie)-1]['categorie_id'] == 182 && intval($le_produit->prix) > 0 ){
			      $affiche_sofinco = false;
		      }
	      }
      }
      
     $btninfo="";
     
     while ($moyen_paiement=$DB_site->fetch_array($moyens_paiement)){
	  switch($moyen_paiement[moyenid]){
	  case 1:
	  if (($mesparametresgeneraux[montant_max_cb] == '0' || $prixtotalpanier < 6000) && $template->site_code()=="base"){
	      $TemplateCde2MoyenContenu1[$moyen_paiement[moyenid]]= "
			    <div class=\"moyenimg col-xs-5 col-sm-4 col-md-4 col-lg-3\"><img src=\"images/templates/cb.jpg\" width=\"80\"></div>
			    <div class=\"moyenopt col-xs-1 col-sm-1 col-md-1 col-lg-1\"><input type=\"radio\" name=\"moyen\" value=\"$moyen_paiement[moyenid]\"></div>
			    <div class=\"moyenname col-xs-5 col-sm-7 col-md-7 col-lg-8 no-padding\">".$moyen_paiement['display'.$template->langue].$btninfo."<span id=\"moyendesc".$moyen_paiement[moyenid]."\">".$moyen_paiement['description'.$template->langue]."</span></div>";
	  }
	  break;
	  
	  case 16:
	  if (($mesparametresgeneraux[montant_max_cb] == '0' || $prixtotalpanier < 6000) && $template->site_code()=="uk"){
	      $TemplateCde2MoyenContenu1[$moyen_paiement[moyenid]]= "
			    <div class=\"moyenimg col-xs-5 col-sm-4 col-md-4 col-lg-3\"><img src=\"images/templates/adyen.jpg\" width=\"80\"></div>
			    <div class=\"moyenopt col-xs-1 col-sm-1 col-md-1 col-lg-1\"><input type=\"radio\" name=\"moyen\" value=\"$moyen_paiement[moyenid]\"></div>
			    <div class=\"moyenname col-xs-5 col-sm-7 col-md-7 col-lg-8 no-padding\">".$moyen_paiement['display'.$template->langue].$btninfo."<span id=\"moyendesc".$moyen_paiement[moyenid]."\">".$moyen_paiement['description'.$template->langue]."</span></div>";
	  }
	  break;

	  case 2: //cheque
	  if (($rs_facture_coordonnees[paysid] == '57' || $rs_facture_coordonnees[paysid] == '181' || $rs_facture_coordonnees[paysid] == '111') && $prixtotalpanier < 3000){
		$TemplateCde2MoyenContenu1[$moyen_paiement[moyenid]]="
			    <div class=\"moyenimg col-xs-5 col-sm-4 col-md-4 col-lg-3\"><img src=\"images/templates/cheque.jpg\" width=\"80\"></div>
			    <div class=\"moyenopt col-xs-1 col-sm-1 col-md-1 col-lg-1\"><input type=\"radio\" name=\"moyen\" value=\"$moyen_paiement[moyenid]\"><input type=\"hidden\" id=\"delpanier$moyen_paiement[moyenid]\" value=\"1\"></div>
			    <div class=\"moyenname col-xs-5 col-sm-7 col-md-7 col-lg-8 no-padding\">".$moyen_paiement['display'.$template->langue].$btninfo."<span id=\"moyendesc".$moyen_paiement[moyenid]."\">".$moyen_paiement['description'.$template->langue]."</span></div>";
	  }
	  break;
		      
	  case 5:  //virement
	  if ($prixtotalpanier < 100000){
			$TemplateCde2MoyenContenu1[$moyen_paiement[moyenid]]="
			  <div class=\"moyenimg col-xs-5 col-sm-4 col-md-4 col-lg-3\"><img src=\"images/templates/virement.jpg\" width=\"80\"></div>
			  <div class=\"moyenopt col-xs-1 col-sm-1 col-md-1 col-lg-1\"><input type=\"radio\" name=\"moyen\" value=\"$moyen_paiement[moyenid]\"><input type=\"hidden\" id=\"delpanier$moyen_paiement[moyenid]\" value=\"1\"></div>
			  <div class=\"moyenname col-xs-5 col-sm-7 col-md-7 col-lg-8 no-padding\">".$moyen_paiement['display'.$template->langue].$btninfo."<span id=\"moyendesc".$moyen_paiement[moyenid]."\">".$moyen_paiement['description'.$template->langue]."</span></div>";
	  }
	  break;
		  
	  case 6: //paypal
	  if ($prixtotalpanier < 3500){
			  $TemplateCde2MoyenContenu1[$moyen_paiement[moyenid]]="
			    <div class=\"moyenimg col-xs-5 col-sm-4 col-md-4 col-lg-3\"><img src=\"https://www.paypal.com/fr_FR/FR/i/logo/PayPal_mark_180x113.gif\" width=\"80\"></div>
			    <div class=\"moyenopt col-xs-1 col-sm-1 col-md-1 col-lg-1\"><input type=\"radio\" name=\"moyen\" value=\"$moyen_paiement[moyenid]\"></div>
			    <div class=\"moyenname col-xs-5 col-sm-7 col-md-7 col-lg-8 no-padding\">".$moyen_paiement['display'.$template->langue].$btninfo."<span id=\"moyendesc".$moyen_paiement[moyenid]."\">".$moyen_paiement['description'.$template->langue]."</span></div>";
	  }
	  break;

	  case 13: // american express
	  if(($prixtotalpanier<15000 && in_array($rs_facture_coordonnees['paysid'],array(57,111,181,4))) || $prixtotalpanier<5000){
	      if($template->site_code()=="base")
		  $TemplateCde2MoyenContenu1[$moyen_paiement[moyenid]]="
		    <div class=\"moyenimg col-xs-5 col-sm-4 col-md-4 col-lg-3\"><img src=\"images/templates/amex.jpg\" width=\"70\"></div>
		    <div class=\"moyenopt col-xs-1 col-sm-1 col-md-1 col-lg-1\"><input type=\"radio\" name=\"moyen\" value=\"1\"></div>
		    <div class=\"moyenname col-xs-5 col-sm-7 col-md-7 col-lg-8 no-padding\">".$moyen_paiement['display'.$template->langue].$btninfo."<span id=\"moyendesc".$moyen_paiement[moyenid]."\">".$moyen_paiement['description'.$template->langue]."</span></div>";
	  }

	  break;

	  case 4: // mandat cash
	  if($prixtotalpanier<=1000 && ($rs_facture_coordonnees[paysid] == '57' || $rs_facture_coordonnees[paysid] == '181')){
			$TemplateCde2MoyenContenu1[$moyen_paiement[moyenid]]="
			  <div class=\"moyenimg col-xs-5 col-sm-4 col-md-4 col-lg-3\"><img src=\"images/templates/logo-mandat.jpg\" width=\"80\"></div>
			  <div class=\"moyenopt col-xs-1 col-sm-1 col-md-1 col-lg-1\"><input type=\"radio\" name=\"moyen\" value=\"$moyen_paiement[moyenid]\"><input type=\"hidden\" id=\"delpanier$moyen_paiement[moyenid]\" value=\"1\"></div>
			  <div class=\"moyenname col-xs-5 col-sm-7 col-md-7 col-lg-8 no-padding\">".$moyen_paiement['display'.$template->langue].$btninfo."<span id=\"moyendesc".$moyen_paiement[moyenid]."\">".$moyen_paiement['description'.$template->langue]."</span></div>";
	  }
	  break;

	  case 14:
	  if(in_array($fcl->utilisateur->paysid,array(57,181)) && $fcl->utilisateur->raisonsociale!=""){
	      $TemplateCde2MoyenContenu1[$moyen_paiement[moyenid]]="
			  <div class=\"moyenimg col-xs-5 col-sm-4 col-md-4 col-lg-3\"><img src=\"images/templates/logo_location.jpg\" width=\"80\"></div>
			  <div class=\"moyenopt col-xs-1 col-sm-1 col-md-1 col-lg-1\"><input type=\"radio\" name=\"moyen\" value=\"$moyen_paiement[moyenid]\"><input type=\"hidden\" id=\"delpanier$moyen_paiement[moyenid]\" value=\"1\"></div>
			  <div class=\"moyenname col-xs-5 col-sm-7 col-md-7 col-lg-8 no-padding\">".$moyen_paiement['display'.$template->langue].$btninfo."<span id=\"moyendesc".$moyen_paiement[moyenid]."\">".$moyen_paiement['description'.$template->langue]."</span></div>";
	  }
	  break;
	  
          case 17: //Sofinco : 4 à 6x sans frais ou financement jusqu'à 60 mois
          
               //On affiche le paiement que s'il entre dans la fourchette 150 <> 25000
               if ($fcl->utilisateur->raisonsociale == '' && $prixtotalpanier > 150 && $prixtotalpanier <= 25000 && $affiche_sofinco){
                    
                    $debutcp = substr($rs_facture_coordonnees['codepostal'],0,2);
                    
                    //Uniquement pour France (hors Monaco) & DOM
                    if(in_array($fcl->utilisateur->paysid, array(57,181,68,135,107,59,111))){

						$TemplateCde2MoyenContenu1[$moyen_paiement['moyenid']] = ""
						. "<div class=\"moyenimg col-xs-5 col-sm-4 col-md-4 col-lg-3\"><img src=\"images/templates/sofinco_partner.jpg\" style=\"width:102px;\"></div>"
						. "<div class=\"moyenopt col-xs-1 col-sm-1 col-md-1 col-lg-1\"><input type=\"radio\" name=\"moyen\" value=\"".$moyen_paiement['moyenid']."\"></div>"
						. "<div class=\"moyenname col-xs-5 col-sm-7 col-md-7 col-lg-8 no-padding\">".$moyen_paiement['display'.$template->langue]
							.$btninfo
							."<span id=\"moyendesc".$moyen_paiement['moyenid']."\" >"
								."<div id=\"sofinco_content".$moyen_paiement['moyenid']."\" style=\"text-align: center;\"><img src=\"images/templates/ajax-loader.gif\" style=\"margin-top: 15px;\"></div>"
							. "</span>"
						. "</div>"
						."<input type=\"hidden\" id=\"facturid_sofinco\" value=\"".$fcl->facturid."\">"
						."<input type=\"hidden\" id=\"mensualites_sofinco\" value=\"4\">"
						."<input type=\"hidden\" id=\"moyenpaiement_sofinco\" value=\"".$moyen_paiement['moyenid']."\">";
                    }
               }
               break;

          }
    }

    $i=0;
    $template->parts["paytbl"]="<div class=\"col-xs-12 col-sm-8 no-padding\" id=\"paytbl\">";
    foreach($moyen_order as $moyenid){
		if(isset($TemplateCde2MoyenContenu1[$moyenid])){
		    $i++;
		    $template->parts["paytbl"].="<div".($i%2==1?" class=\"alttr col-xs-12 moypm\"":" class=\"col-xs-12 moypm\"")." id=\"moyen".$moyenid."\" onmouseover=\"moyen_description(".$moyenid.")\" onclick=\"select_moyen(".$moyenid.")\">".$TemplateCde2MoyenContenu1[$moyenid]."</div>";
		}
    }
    
    //Paiement "Facilés de paiement" accessibles uniquement aux administrateurs du site
     if(isset($_COOKIE['admin_username'])){
          
          $moyensfacilites = array(1,2,4,5,6,7,15,19);
          
          $sql = "SELECT moyenid, libelleFR AS libelle";
          $sql .= " FROM moyenpaiement";
          $sql .= " WHERE moyenid IN (".implode(",",$moyensfacilites).")";
          $sql .= " ORDER BY libelleFR";
          $reqmoyens = $DB_site->query($sql);
          while($moyen = $DB_site->fetch_array($reqmoyens)){
               $template->parts["optmoyensfac"] .= "<option value=\"".$moyen['moyenid']."\" ".($moyen['moyenid'] == 1 ? 'selected' : '').">".$moyen['libelle']."</option>";
          }
          
          $sql = "SELECT *";
          $sql .= " FROM reglement_delais";
          $reqdelais = $DB_site->query($sql);
          while($delai = $DB_site->fetch_array($reqdelais)){
               $template->parts["optdelaisfac"] .= "<option value=\"".$delai['reglement_delais_id']."\">".$delai['reglement_delais_libelle']."</option>";
          }
                    
          $i++;
          
          $template->parts["faciliteclass"] = ($i%2 == 1 ? "alttr" : "");
          $template->parts["prixtotalpanierfacilite"] = $fcl->totalttc;
          $template->evaltemplate("PanierPaiementFacilite","paytbl");
     }
    
    $template->parts["paytbl"].="</div>";

}

$template->parts["adresselivr"]="";
if($fcl->raisonsociale!="") $template->parts["adresselivr"]=$fcl->raisonsociale."<br>";
$template->parts["adresselivr"].="<b>" .$fcl->nom." ".$fcl->prenom."</b><br>".$fcl->adresse."<br>".$fcl->codepostal." ".$fcl->ville."<br>".$fcl->pays."<br><br>".$fcl->telephone."<br>".$fcl->infotranspo;

$template->parts["adressefact"]="";
if($fcl->utilisateur->raisonsociale!="") $template->parts["adressefact"]=$fcl->utilisateur->raisonsociale."<br>";
$template->parts["adressefact"].="<b>" .$fcl->utilisateur->nom." ".$fcl->utilisateur->prenom."</b><br>".$fcl->utilisateur->adresse."<br>".$fcl->utilisateur->codepostal." ".$fcl->utilisateur->ville."<br>".$fcl->utilisateur->pays."<br><br>".$fcl->utilisateur->telephone."<br>".$fcl->utilisateur->portable;

//Contrôle sur pas de provenance et saisie des informations de colisage des produits
$template->evaltemplate("ValiderCommande","valider_commande");

if(!$colisage){
     
     $txt_colisage = "<u>Références concernées</u> :";
     foreach($tabArticlesColisage as $colisage_ref => $colisage_id){
          $txt_colisage .= "<br><a href=\"".BACK_URL."/ajout_produit.php?artid=".$colisage_id."&prec=modif\">".$colisage_ref."</a>";
     }

     if($fcl->paysid != 57){
          
          $template->parts["valider_commande"] = "";
          $template->parts["paysimpossible"] = $fcl->pays;
          $template->evaltemplate("TxtColisage","texte_colisage");
          
          $mail_contenu = ""
               . "Le client \"".$fcl->utilisateur->prenom." ".$fcl->utilisateur->nom."\" souhaite se faire livrer en \"".$fcl->pays."\", mais les informations de colisage ne sont pas renseignées sur au moins 1 des produits de sa commande."
               . "<br><br>"
               . $txt_colisage
               . "<br><br>"
               . "Accès fiche client <a href=\"".BACK_URL."/utilisateur.php?action=editer&userid=".$fcl->userid."\">ici</a>"
               . "<br>"
               . "Accès commande <a href=\"".BACK_URL."/facture.php?action=editer&facturid=".$fcl->facturid."&lst_trietat=\">ici</a>";
     } else {
          
          $mail_contenu = ""
               . "Attention, 1 des produits de <a href=\"".BACK_URL."/facture.php?action=editer&facturid=".$fcl->facturid."&lst_trietat=\">cette commande</a> n'a pas de colisage renseigné."
               . "<br><br>"
               . $txt_colisage
               . "<br><br>"
               . "Cette dernière étant destinée à une livraison en France métropolitaine, l'étape du règlement n'a pas été bloqué.";
          
     }
     
     //Envoi d'un email de notification pour demande de création des informations de colisage du produit
     $email = new email();
     $email->destinataire = MAIL_CONTACT;
     $email->expediteur   = MAIL_CONTACT;
	 $email->expname	  = $template->translate("/site/nom");
     $email->subject      = SITE_NOM." : Informations de colisage manquantes";
     $email->body         = $mail_contenu;
     $email->sendmail();
     
}

$template->parts["taxtxt"]=$template->translate(($sitepro?"/site/ht":"/site/ttc"),"U");

//BarreCookie
$template->parts["stylePanier"]="style=\"bottom:0;\"";

$template->evaltemplate("PanierPaiement","pagecommande");
$template->parts["etape"]="<img src=\"images/templates/commande5.jpg\" alt=\"Paiement\" class=\"img img-responsive\">";

$template->parts["remarketing"]=google_remarketing('',"purchase",'');
$template->evaltemplate("PanierPage","main");

//Paramétrage des META référencement de la page
if($sitepro)
{   
    $title_trad = '/index/meta_title_pro';
    $title_desc = '/index/meta_description_pro';
}
else
{
    $title_trad = '/index/meta_title';
    $title_desc = '/index/meta_description';
}

$template->pageparams($host, $template->translate($title_trad), $template->translate($title_desc));

$template->out();

?>
