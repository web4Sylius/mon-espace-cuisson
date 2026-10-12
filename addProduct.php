<?php

/* 	Input to this file $_GET['productId'] 

This file outputs a string in this format

productId|||productDescription|||price,

i.e. ID of product followed by 3 pipes followed by a description of the product followed by 3 pipes followed by the price

*/

/* This is code only for the demo - You would most likely use a database for this */

ob_start();

header('Content-Type: text/html; charset=utf-8');

require ("global.php");

ob_end_clean();
$accesPanier = $_GET['accesPanier'];
$libelle="libelle".$template->langue;
if (isset($_GET['productId']) and isset($_GET['qte']) and is_numeric($_GET['qte'])){
	
	$productId = htmlspecialchars($_GET['productId']);	
	$add = 0 ;
	$artid = mysqli_real_escape_string($DB_site->link_id, $productId) ;
	$datepanier = date('Y-m-d') ;
	$panier=$DB_site->query_first("SELECT * FROM panier where sessionid = '$s'");
	$temppanierid = $panier['panierid'] ;
	$DB_site->query("INSERT IGNORE INTO panier (sessionid, datepanier, prixtva, prixgestion, affilieid) VALUES ('$s', '$datepanier', 0, 0, 0)");
	if ($temppanierid == "")
		{
		$temppanierid = $DB_site->insert_id() ;
		}
	//stats
// 	include(dirname(__FILE__)."/stat_panier.php");
	$existe=$DB_site->query_first("select count(*) from lignepanier where panierid = '$temppanierid' and artid = '$artid'") ;
	if ($existe[0] == 0)
		{
		$passe = 0 ;
		$mycaractvalids = array() ;
		foreach ($_GET as $cle=>$valeur)
			{
			if (substr($cle, 0, 4) == "lst_")
				{
				$autres=$DB_site->query_first("select count(*) from article_caractval ac inner join caracteristiquevaleur cv on (ac.caractvalid = cv.caractvalid) 
				where ac.artid = '$artid' and cv.caractid IN (select caractid from caracteristiquevaleur where caractvalid = '$valeur')") ;
				if ($autres[0] > 1)
					{
					$passe++ ;
					}
				array_push($mycaractvalids, $valeur) ;
				}
			}
		if ($passe > 0)
			{
			if (!in_array("4", $modules) || stock_get($DB_site, $artid, $mycaractvalids) >= $qte)
				{
				$DB_site->query("INSERT INTO lignepanier (panierid, artid, qte) VALUES ('$temppanierid', '$artid', '$qte')") ;
				$templignepanierid = $DB_site->insert_id() ;
				// Caractéristiques
				foreach ($_GET as $cle=>$valeur)
					{
					if(substr($cle, 0, 7) == "lst_ral")
						{
							foreach($valeur as $cle=>$val){
								$DB_site->query("INSERT INTO lignepaniercaracteristique (lignepanierid, caractvalid)
										  VALUES ('$templignepanierid', '$val')");
							}
						}
						elseif (substr($cle, 0, 4) == "lst_")
						{   // Pour chaque zone de liste on met la valeur séléctionnée dans lignepaniercaracteristique
						$DB_site->query("INSERT INTO lignepaniercaracteristique (lignepanierid, caractvalid)
										  VALUES ('$templignepanierid', '$valeur')");
						}
					}
				$add = 1 ;
				}
			else
				{
				$add = 2 ;
				}
			}
		else
			{
			if (!in_array("4", $modules) || stock_get($DB_site, $artid) >= $qte)
				{
				$DB_site->query("INSERT INTO lignepanier (panierid, artid, qte) VALUES ('$temppanierid', '$artid', '$qte')") ;
				$templignepanierid = $DB_site->insert_id() ;
				// Caractéristiques
				foreach ($_GET as $cle=>$valeur)
					{
						if(substr($cle, 0, 7) == "lst_ral")
						{
							foreach($valeur as $cle=>$val){
								$DB_site->query("INSERT INTO lignepaniercaracteristique (lignepanierid, caractvalid)
										  VALUES ('$templignepanierid', '$val')");
							}
						}
						elseif (substr($cle, 0, 4) == "lst_")
						{   // Pour chaque zone de liste on met la valeur séléctionnée dans lignepaniercaracteristique
						$DB_site->query("INSERT INTO lignepaniercaracteristique (lignepanierid, caractvalid)
										  VALUES ('$templignepanierid', '$valeur')");
						}
					}
				$add = 1 ;
				}
			else
				{
				$add = 2 ;
				}
			}
		}
	else
		{
		$from ="" ;
		$where = "" ;
		$nbre = 0 ;
		// Caractéristiques
		foreach ($_GET as $cle=>$valeur)
			{
			if (substr($cle, 0, 4) == "lst_")
				{
				$nbre++ ;
				$from .= ", lignepaniercaracteristique lpc$nbre" ;
				$where .= " and lp.lignepanierid = lpc$nbre.lignepanierid " ;
				$where .= " and lpc$nbre.caractvalid = '$valeur'" ;
				}
			}
			// TODO revoir la vérification des options car fait planter le serveur mysql quand on arrivee à 10
// 		$article_correspond=$DB_site->query_first("select * from lignepanier lp $from where artid = '$artid' and panierid = '$temppanierid' $where") ;
		if (false && $article_correspond[0] != "")
			{
			$passe = 0 ;
			$mycaractvalids = array() ;
			foreach ($_GET as $cle=>$valeur)
				{
				if (substr($cle, 0, 4) == "lst_")
					{
					$autres=$DB_site->query_first("select count(*) from article_caractval ac inner join caracteristiquevaleur cv on (ac.caractvalid = cv.caractvalid) 
					where ac.artid = '$artid' and cv.caractid IN (select caractid from caracteristiquevaleur where caractvalid = '$valeur')") ;
					if ($autres[0] > 1)
						{
						$passe++ ;
						}
					array_push($mycaractvalids, $valeur) ;
					}
				}
			if ($passe > 0)
				{
				if (!in_array("4", $modules) || stock_get($DB_site, $artid, $mycaractvalids) >= ($qte + $article_correspond[qte]))
					{
					$DB_site->query("UPDATE lignepanier set qte = (qte + $qte) where lignepanierid = '$article_correspond[lignepanierid]'") ;
					$templignepanierid=$article_correspond[lignepanierid] ;
					}
				else
					{
					$add = 2 ;
					}
				}
			else
				{
				if (!in_array("4", $modules) || stock_get($DB_site, $artid) >= ($qte + $article_correspond[qte]))
					{
					$DB_site->query("UPDATE lignepanier set qte = (qte + $qte) where lignepanierid = '$article_correspond[lignepanierid]'") ;
					$templignepanierid=$article_correspond[lignepanierid] ;
					}
				else
					{
					$add = 2 ;
					}
				}
			}
		else
			{
			$passe = 0 ;
			$mycaractvalids = array() ;
			foreach ($_GET as $cle=>$valeur)
				{
				if (substr($cle, 0, 4) == "lst_")
					{
					$autres=$DB_site->query_first("select count(*) from article_caractval ac inner join caracteristiquevaleur cv on (ac.caractvalid = cv.caractvalid) 
					where ac.artid = '$artid' and cv.caractid IN (select caractid from caracteristiquevaleur where caractvalid = '$valeur')") ;
					if ($autres[0] > 1)
						{
						$passe++ ;
						}
					array_push($mycaractvalids, $valeur) ;
					}
				}
			if ($passe > 0)
				{
				if (!in_array("4", $modules) || stock_get($DB_site, $artid, $mycaractvalids) >= $qte)
					{
					$DB_site->query("INSERT INTO lignepanier (panierid, artid, qte) VALUES ('$temppanierid', '$artid', '$qte')") ;
					$templignepanierid = $DB_site->insert_id() ;
					// Caractéristiques
					foreach ($_GET as $cle=>$valeur)
						{
						if(substr($cle, 0, 7) == "lst_ral")
						{
							foreach($valeur as $cle=>$val){
								$DB_site->query("INSERT INTO lignepaniercaracteristique (lignepanierid, caractvalid)
										  VALUES ('$templignepanierid', '$val')");
							}
						}
						elseif (substr($cle, 0, 4) == "lst_")
						{   // Pour chaque zone de liste on met la valeur séléctionnée dans lignepaniercaracteristique
						$DB_site->query("INSERT INTO lignepaniercaracteristique (lignepanierid, caractvalid)
										  VALUES ('$templignepanierid', '$valeur')");
						}
						}
					$add = 1 ;
					}
				else
					{
					$add = 2 ;
					}
				}
			else
				{
				if (!in_array("4", $modules) || stock_get($DB_site, $artid) >= $qte)
					{
					$DB_site->query("INSERT INTO lignepanier (panierid, artid, qte) VALUES ('$temppanierid', '$artid', '$qte')") ;
					$templignepanierid = $DB_site->insert_id() ;
					// Caractéristiques
					foreach ($_GET as $cle=>$valeur)
						{
						if(substr($cle, 0, 7) == "lst_ral")
						{
							foreach($valeur as $cle=>$val){
								$DB_site->query("INSERT INTO lignepaniercaracteristique (lignepanierid, caractvalid)
										  VALUES ('$templignepanierid', '$val')");
							}
						}
						elseif (substr($cle, 0, 4) == "lst_")
						{   // Pour chaque zone de liste on met la valeur séléctionnée dans lignepaniercaracteristique
						$DB_site->query("INSERT INTO lignepaniercaracteristique (lignepanierid, caractvalid)
										  VALUES ('$templignepanierid', '$valeur')");
						}
						}
					$add = 1 ;
					}
				else
					{
					$add = 2 ;
					}
				}
			}
		}
	$prixtotalpanier = 0;
	$nbart=0;
	$listepanier = "" ;
	$lignepaniertmps=$DB_site->query("SELECT l.qte , a.artid, a.prix, a.tauxchoisi, a.fraisgestionid, l.lignepanierid, a.$libelle 
	FROM lignepanier l INNER JOIN article a on (a.artid = l.artid) WHERE panierid = '$temppanierid' ORDER BY l.lignepanierid ASC");
	while ($lignepaniertmp=$DB_site->fetch_array($lignepaniertmps)) {

		// mofidication du prix en f° des caractéristiques
		$sql = "SELECT c.".$libelle." as libelle_caract, cv.".$libelle." as libelle_caractval, cv.ref, lpc.caractvalid";
		$sql .= " FROM lignepaniercaracteristique lpc";
		$sql .= " INNER JOIN caracteristiquevaleur cv ON (lpc.caractvalid = cv.caractvalid)";
		$sql .= " INNER JOIN caracteristique as c ON (cv.caractid = c.caractid)";
		$sql .= " WHERE lignepanierid = '".$lignepaniertmp['lignepanierid']."'";
		$rq_lignepaniercaracteristique = $DB_site->query($sql);
		$modif_px=0;
		while ($rs_lignepaniercaracteristique = $DB_site->fetch_array($rq_lignepaniercaracteristique)){   
			$liste_ral=$DB_site->query_first("SELECT caractvalid FROM caractval_ral WHERE caractvalid = '".$rs_lignepaniercaracteristique['caractvalid']."'");
                ///si l'option est une couleur RAL :
			if(!is_null($liste_ral)){
				$caracteid = $DB_site->query_first("SELECT caractid FROM `caracteristiquevaleur` WHERE `caractvalid` = '".$liste_ral['caractvalid']."'");
				$caracvals = $DB_site->query("SELECT caractvalid FROM `caracteristiquevaleur` WHERE `subcaractid` = ".$caracteid['caractid']);
				$caracvals_liste=array();
				foreach($caracvals as $caracv){
					$caracvals_liste[]=$caracv['caractvalid'];
				}
				$caracvals=implode("','",$caracvals_liste);
				$caracval = $DB_site->query_first("SELECT caractvalid FROM `article_caractval` WHERE `artid` = '".$lignepaniertmp['artid']."' AND `caractvalid` IN ('".$caracvals."')");
				$sql = "SELECT ac.differenceprix, ac.achat, cv.prix_fixe, cv.pvttc, cv.pvht, cv.paht, c.libelleFR as libelleral";
				$sql .= " FROM article_caractval ac INNER JOIN caracteristiquevaleur cv ON cv.caractvalid = ac.caractvalid INNER JOIN caracteristique c ON cv.caractid = c.caractid";
				$sql .= " WHERE ac.caractvalid = '".$caracval['caractvalid']."'";
				$sql .= " AND ac.artid = '".$lignepaniertmp['artid']."'";
				$caract_diff_px = $DB_site->query_first($sql);
			}
			else{
				$sql = "SELECT ac.differenceprix, ac.achat, cv.prix_fixe, cv.pvttc, cv.pvht, cv.paht, c.libelleFR as libelleral";
				$sql .= " FROM article_caractval ac INNER JOIN caracteristiquevaleur cv ON cv.caractvalid = ac.caractvalid INNER JOIN caracteristique c ON cv.caractid = c.caractid";
				$sql .= " WHERE ac.caractvalid = '".$rs_lignepaniercaracteristique['caractvalid']."'";
				$sql .= " AND ac.artid = '".$lignepaniertmp['artid']."'";

				$caract_diff_px = $DB_site->query_first($sql);
			}
              //Si lot, on additionne les prix
			$lot_prix=array();
			$caractvaltotal=array();
			$req_lot=$DB_site->query("SELECT artid,qte FROM article_lot WHERE lotid=".$lignepaniertmp['artid']);
			while($art_lot=$DB_site->fetch_array($req_lot)){
				$lot_prix[]=array($art_lot['artid'],$art_lot['qte']);
			}
			if($lot_prix!=array()){
				foreach($lot_prix as $articleid => $art_val){
					$art=$DB_site->query_first("SELECT differenceprixht, differenceprix, cv.prix_fixe, cv.pvht, cv.pvttc, cv.paht FROM article_caractval INNER JOIN caracteristiquevaleur as cv USING(caractvalid)WHERE caractvalid=".$rs_lignepaniercaracteristique['caractvalid']." AND artid=".intval($art_val[0]));
					if($art!=""){
                        if($art['prix_fixe'] == '1'){
                            $caractvaltotal["prixlot"]=$caractvaltotal["prixlot"] + $art['pvttc']*$art_val[1];
                        }
                        else{
                            $diffprix = unserialize($art["differenceprix"]);
                            $caractvaltotal["prixlot"]=$caractvaltotal["prixlot"] + $diffprix['base']*$art_val[1];
                        }
                    }
                }
            }
            if(isset($caractvaltotal["prixlot"])){
                $caract_prix=round($caractvaltotal["prixlot"],4);
            }
            else{
                if($caract_diff_px['prix_fixe'] == '1'){
                    $caract_prix=$caract_diff_px['pvttc'];
                }
                else{
                    $caract_prix=unserialize($caract_diff_px['differenceprix'])['base'];
                }
            }
           
            $modif_px+=$caract_prix;

        }
 

		$lignepaniertmp[prix] += $modif_px ;
		// promotion, remise tt cat : modif du prix
		$promo_en_crs=$DB_site->query_first("select promotionid from promotion p, article a where a.artid = p.artid
		and a.artid = $lignepaniertmp[artid] and p.datedebut <= '$datedujour' and p.datefin >= '$datedujour'");
		if (isset($remise_sur_tout_catalogue) and $remise_sur_tout_catalogue > 0) // remise tt catalogue
			{
			if ($promo_en_crs[promotionid] != NULL) // promo
				{
				$rs_promo = $DB_site->query_first("SELECT prix FROM promotion WHERE promotionid = '$promo_en_crs[promotionid]'
				AND datedebut <= '$datedujour' AND datefin > '$datedujour'");
				$rs_promo[prix] += $modif_px ;
				if ($rs_promo[prix] != "")
					{
					$tmp_prix = $rs_promo[prix] * (1 - $remise_sur_tout_catalogue / 100) ;
					}
				else
					{
					$tmp_prix = $lignepaniertmp[prix] * (1 - $remise_sur_tout_catalogue / 100) ;
					}
				}
			else // pas de promo
				{
				$tmp_prix = $lignepaniertmp[prix] * (1 - $remise_sur_tout_catalogue / 100) ;
				}
			}
		else // pas de remise tt cat.
			{
			if ($promo_en_crs[promotionid] != NULL) // promo
				{
				$rs_promo = $DB_site->query_first("SELECT prix FROM promotion WHERE promotionid = '$promo_en_crs[promotionid]'
				AND datedebut <= '$datedujour' AND datefin > '$datedujour'");
				if ($rs_promo[prix] != "")
					{
					$tmp_prix = $rs_promo[prix];
					}
				else
					{
					$tmp_prix = $lignepaniertmp[prix];
					}
				}
			else // pas promo
				{
				$tmp_prix = $lignepaniertmp[prix];
				}
			}
		$prixligne = $tmp_prix * $lignepaniertmp[qte];
		// pays pour définir le taux de TVA
		if ($HTTP_COOKIE_VARS[userid] != "")
			{
			$pays_user=$DB_site->query_first("select paysid from utilisateur where userid = '$HTTP_COOKIE_VARS[userid]'");
			$paysid = $pays_user[paysid] ;
			}
		elseif ($user_info[userid] != "")
			{
			$pays_user=$DB_site->query_first("select paysid from utilisateur where userid = '$user_info[userid]'");
			$paysid = $pays_user[paysid] ;
			}
		else
			{
			$paysid = "57" ;
			}
		switch ($lignepaniertmp[tauxchoisi])
			{
			case '0' :
				$prixtva += 0 ;
				break ;
			case '1' : // si on est connecté on regarde tout de suite le pays !!!
				$montauxtva=$DB_site->query_first("select TVAtauxnormal from pays where paysid = '$paysid'");
				$prixtva += $prixligne - ($prixligne / (1 + ($montauxtva[TVAtauxnormal]/100))) ;
				break ;
			case '2' : // si on est connecté on regarde tout de suite le pays !!!
				$montauxtva=$DB_site->query_first("select TVAtauxreduit from pays where paysid = '$paysid'");
				$prixtva += $prixligne - ($prixligne / (1 + ($montauxtva[TVAtauxreduit]/100))) ;
				break ;
			}
		$nbart+=$lignepaniertmp[qte];
		$prixtotalpanier += $prixligne ;

		$listepanier .= "&&$lignepaniertmp[artid]<>$lignepaniertmp[$libelle]<>".number_format($prixligne, 2, ",", " ")."<>$lignepaniertmp[lignepanierid]&&" ;
		}
	$prixtotalpanier_enforme = number_format($prixtotalpanier, 2, ",", " ") . $bidule;

	$popup=(int)!$template->mobile;
	//$listepanier = urlencode($listepanier);
	if ($add == 0) {
		die ("OK|||NON|||$prixtotalpanier_enforme|||$imagesupprimer|||$listepanier|||$nbart|||$popup|||$accesPanier");
		exit;
		}
	elseif ($add == 2) {
		die ("OK|||STOCK|||$prixtotalpanier_enforme|||$imagesupprimer|||$listepanier|||$nbart|||$popup|||$accesPanier");
		exit;
		}
	else {
		die ("OK|||OUI|||$prixtotalpanier_enforme|||$imagesupprimer|||$listepanier|||$nbart|||$popup|||$accesPanier|||$test");
		exit;
		}
	}
else
	{
	die ("KO");
	exit;
	}

?>
