<?php

require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");
include $_SERVER['DOCUMENT_ROOT']."/class/stock.class.php";
include $_SERVER['DOCUMENT_ROOT']."/class/DAOarticle.class.php";
include $_SERVER['DOCUMENT_ROOT']."/class/garantie.class.php";
include $_SERVER['DOCUMENT_ROOT']."/class/DAOgarantie.class.php";
include $_SERVER['DOCUMENT_ROOT']."/admin/spy.class.php";
include $_SERVER['DOCUMENT_ROOT']."/class/facture.class.php";

// Déclaration
$stock 	= new stock($DB_site2);
$now 	= time();
$exclu 	= array("googlebot","as29017","msn","voilabot","ahref","bingbot","acoon","google","yandex","exabot","torservers","as13285");
$exclip = array("80.12.82.212","88.184.216.175");
$from 	= mktime(0,0,0,date("n"),01,date("Y")); 
$to 	= mktime(23,59,59,date("n"),date("t"),date("Y"));

// Filtre Marque
if(isset($_GET['marq']) && $_GET['marq'] != ""){
    $sqlmarq = " AND am.marqueid=".$_GET['marq'];
}

// Filtre Type
if(isset($_GET['type']) && $_GET['type'] != ""){
    $sqltype = " AND a.typearticleid=".$_GET['type'];
}

// Liste des prix d'un article
$listeprix = array();
$sql = "SELECT a.artid, (SELECT CONCAT(CAST(prix AS CHAR),'|',CAST(prixconseilleht AS CHAR),'|',CAST(paht AS CHAR)) FROM temp_price WHERE artid=a.artid AND ".$now." BETWEEN du AND au LIMIT 1) AS temp, a_s.prix AS prix, a_s.prixconseilleht, a_s.tauxchoisi, a_s.active, a.emballage_poids, af.prix AS paht, af.ecopartht, af.trspt 
	FROM article a 
	INNER JOIN article_site a_s USING(artid) 
	INNER JOIN article_fournisseur af USING(artid) 
	INNER JOIN article_marque am USING(artid) 
	WHERE site_code='base' 
	AND af.principal LIKE '%base%' 
	AND a_s.active IN('1')";
$req = $DB_site2->query($sql);
while($article = $DB_site2->fetch_array($req)){	  
	if(!is_null($article['temp'])){
		list($tprix,$tprixconseilleht,$tpaht) = explode("|",$article['temp']);

		$article['prix'] 			= $tprix;
		$article['prixconseilleht'] = $tprixconseilleht;
		$article['paht']			= $tpaht;
	}
	  
	$tva = $template->tva_ref("taux".$article['tauxchoisi']);
	$prixconseille = round($article['prixconseilleht']*(1+$tva/100),2);
	  
	$fraisport = $DB_site2->query_first("SELECT * FROM fraisport WHERE paysid=57 AND ".(1000*$article['emballage_poids'])." BETWEEN poiddebut AND poidfin");
	if($article['prix'] > $fraisport['gratuit']) 
		$fraisport['prix'] = 0;
	$fraisport['prix'] = $fraisport['prix'] / (1+$tva/100);
	  
	//$pahtgarantie = 0;
	//$garanties = DAOgarantie::loadMaxGarantieArtByPays($DB_site2,"base","FR",57,$article['artid']);
	//foreach($garanties as $g) 
		//$pahtgarantie += $g->paht;
	  
	$totalachat = $article['paht'] + $article['ecopartht'] + $article['trspt'] + $fraisport['achat'] + $pahtgarantie;
	$totalvente = $article['prix'] / (1+$tva/100) + $fraisport['prix'];
	$marge = $totalvente - $totalachat;
	$pcmarge = 100*$marge / $totalvente;
	
	$listeprix[$article['artid']]					= $article;
	$listeprix[$article['artid']]['paht'] 			= $article['paht'];
	$listeprix[$article['artid']]['prixconseille']	= $prixconseille;
	$listeprix[$article['artid']]['pcremise']		= ($prixconseille!=0 ? round(-100*($prixconseille-$article['prix'])/$prixconseille) : 0);
	$listeprix[$article['artid']]['marge']			= $marge;
	$listeprix[$article['artid']]['pcmarge']		= $pcmarge;
	$listeprix[$article['artid']]['totalachat']		= $totalachat;
	$listeprix[$article['artid']]['prixport']		= $fraisport['prix'];
	$listeprix[$article['artid']]['tva']			= $tva;
}

// SPY
//$spytime=time()-2*86400;
$spytime = 1451631601;
$spylist = $spymini = $spyminisite = array();

$req = $DB_site2->query("SELECT artcode, CAST(substring_index(group_concat(price ORDER BY timestamp DESC),',', 1) AS char) AS price, site FROM history WHERE timestamp>".$spytime." GROUP BY artcode, site ORDER BY timestamp DESC");
while($spy = $DB_site2->fetch_array($req)){
	if($spy['price'] != ""){
		$artcode = $spy['artcode'];
		if($spy['price'] < $spymini[$artcode] || !isset($spymini[$artcode])){
			$spymini[$artcode] = $spy['price'];
			$spyminisite[$artcode] = $spy['site'];
		}
		$spylist[$spy['site']] = $spy['price'];
		$spylist[$artcode][$spy['site']] = $spy['price'];
	}
}

//TSI : Debug ? Affichage du contenu de la variable
//nice($spylist);

// Tableau
$tr=array();
switch($_GET['action']){

	// Tableau Stock
	case 'stock':
		$r = $stock->stockbyqte(array(1,2), null);
		$r = array_merge($r,$stock->stock0(array(1,2),null,true));
		ksort($r);

		$i = 0;
		$lignes[] = array();

		//Boucle item du stock
		foreach($r as $item){
			if($item->qte > 0){
				// Prix
				$prix = $listeprix[$item->artid];
				
				// Spy
				if(isset($spymini[$item->artcode])){
					$spyht = $spymini[$item->artcode] / (1+$prix['tva']/100);
					$margespy = $spyht - $prix['totalachat'];
					$pcmargespy = 100*$margespy / $spyht;
				}
				
				// Vendus 3mois
				$qte3 = 0;
				$sql3 = "SELECT l.qte 
					FROM article a 
					INNER JOIN `lignefacture` l ON (a.artid=l.artid) 
					INNER JOIN facture f ON (f.facturid=l.factureid) 
					WHERE l.prix>=0 AND lignefacturedissid=0 AND packid=0 
					AND f.valide IN(1,5,7,8) 
					AND provenance IN ('','mobile','mp','sav') 
					AND l.artid=".$item->artid." 
					AND UNIX_TIMESTAMP(f.datefacture)<=".$to." AND UNIX_TIMESTAMP(f.datefacture)>=".strtotime("-3 months",$from);
				$req3 = $DB_site2->query($sql3);
				while($lignefact3 = $DB_site2->fetch_array($req3)){
					$qte3 = $lignefact3[0];
				}
				
				// Vendus 6mois
				$qte6 = 0;
				$sql6 = "SELECT l.qte 
					FROM article a 
					INNER JOIN `lignefacture` l ON (a.artid=l.artid) 
					INNER JOIN facture f ON (f.facturid=l.factureid) 
					WHERE l.prix>=0 AND lignefacturedissid=0 AND packid=0 
					AND f.valide IN(1,5,7,8) 
					AND provenance IN ('','mobile','mp','sav') 
					AND l.artid=".$item->artid."
					AND UNIX_TIMESTAMP(f.datefacture)<=".$to." AND UNIX_TIMESTAMP(f.datefacture)>=".strtotime("-6 months",$from);
				$req6 = $DB_site2->query($sql6);
				while($lignefact6 = $DB_site2->fetch_array($req6)){
					$qte6 = $lignefact6[0];
				}

				// Liste spy
				$ulspy = "";
				$reqs = $DB_site2->query("SELECT artcode,CAST( substring_index( group_concat( price ORDER BY timestamp DESC ) , ',', 1 ) AS char ) AS price,site FROM history WHERE timestamp>".$spytime." AND artcode='".$item->artcode."' GROUP BY site ORDER BY price DESC");
				while($listspy = $DB_site2->fetch_array($reqs)){
					$ulspy .= "<li><b>".$listspy['site']."</b> - ".$listspy['price']."&euro;</li>";
				}

				// Article actif ou remplaçant
				$active = $DB_site2->query_first("SELECT active, redirect FROM article WHERE artid=".$item->artid);
				$tabAct = array('0','2','3');
				
				// Article vente flash
				$artVF = $DB_site2->query_first("SELECT * FROM vente_flash_article WHERE artcode='".$item->artcode."'");

				// Création des lignes
				if(!in_array($active[0], $tabAct) && $active[1] == ''){
					$tr[$item->marque][$item->artcode]="
					<tr class=\"tr_vf\" id=\"tr_".$i."\" ".($artVF['id'] != '' ? "style=\"background-color:#00d796;\"" : "").">
						<td><img src=\"".$item->img."\" width=\"50\"></td>
						<td>".$item->artcode."<br><b>".$item->marque."</b></td>
						<td>".$item->qte."</td>
						<td>".$qte3."</td>
						<td>".$qte6."</td>
						<td>".number_format($prix['prixconseille'],2,',','')."&euro;</td>
						<td>".number_format($prix['prix'],2,',','')."&euro;
							<br><span class=\"vf_paht\">".$prix['paht']."&euro;</span></td>
						<td>".round($prix['pcmarge'],1)."%</td>
						
						<td class=\"inner-addon right-addon\">
							<i class=\"fa fa-eur\"></i>
							<input type=\"text\" name=\"prix_$item->artcode\" id=\"num\" onkeyup=\"calcmarge($item->artid,this.value,".$prix['totalachat'].",".$prix['prixport'].",".$prix['tva'].");calcremise($item->artid,this.value,".$prix['prixconseille'].")\" size=\"6\" value=\"".($artVF['id'] != '' ? $artVF['prix'] : "")."\"/>
						</td>
						<td><span id=\"remise_$item->artid\">".($artVF['id'] != '' ? phpremise($artVF['prix'],$prix['prixconseille']) : '')."</td>
						<td><span id=\"marge_$item->artid\">".($artVF['id'] != '' ? phpmarge($artVF['prix'],$prix['totalachat'],$prix['prixport'],$prix['tva']) : '')."</td>
						
						<td>".($spymini[$item->artcode]!="" ? number_format($spymini[$item->artcode],2,',','')."&euro;" : "")."</td>
						<td>".(count($spylist[$item->artcode])!=0 ? count($spylist[$item->artcode]) : "")."</td>
						<td>
							<div class=\"tooltip\">".ucfirst($spyminisite[$item->artcode])."
									<span class=\"tooltiptext tooltip-bottom\">".$ulspy."</span>
							</div>
						</td>
						<td>".(isset($spymini[$item->artcode]) ? round($pcmargespy,1)."%" : "")."</td>
						
						<td><input type=\"checkbox\" id=\"checkvf_".$i."\" name=\"venteflashCheck[]\" value=\"$item->artcode\" ".($artVF['id'] != '' ? "checked" : "")." onclick=\"changeCheck(this.id); chckPrix('remise_".$item->artid."',".$i.");\"></td>
					</tr>";
					
					$i++;
				}

				if(!in_array($active[0], $tabAct) && $active[1] == ''){
					$lignes[] = array(
						$item->marque, 
						$item->artcode, 
						$item->qte, 
						$qte3, 
						$qte6, 
						(number_format($prix['prixconseille'],2,',','')), 
						(number_format($prix['prix'],2,',','')), 
						(round($prix['pcmarge'])."%"),
						($artVF['id'] != '' ? $artVF['prix'] : ""), 
						($artVF['id'] != '' ? phpremise($artVF['prix'],$prix['prixconseille']) : ''), 
						($artVF['id'] != '' ? phpmarge($artVF['prix'],$prix['totalachat'],$prix['prixport'],$prix['tva']) : ''), 
						($spymini[$item->artcode]!="" ? number_format($spymini[$item->artcode],2,',','') : ""), 
						(count($spylist[$item->artcode])!=0 ? count($spylist[$item->artcode]) : ""), 
						ucfirst($spyminisite[$item->artcode]), 
						(isset($spymini[$item->artcode]) ? round($pcmargespy)."%" : ""), 
						($artVF['id']!="" ? "X" : "")
					);
				}
			}
		}

		//Tri
		ksort($tr);

		if(isset($_GET['export']) && $_GET['export'] == 1){
			$filename = "VF_Stock_".date("d-m-Y",time());
			// CSV file header
			header("Content-type: application/vnd.ms-excel");
			header("Content-Encoding: UTF-8");
			header("Content-type: text/csv; charset=UTF-8");
			header("Content-disposition: csv" . date("Y-m-d") . ".csv");
			header("Content-disposition: filename=".$filename.".csv");

			// Création de la ligne d'en-tête
			$entete = array("Marque", "Ref Acilyon", "Stock", "Vendus 3 mois", "Vendus 6 mois", "PPI", "PV actuel", "Marge Av/Remise", "Prix VF", "Remise VF", "Marge Ap/Remise", "Prix + bas", "Nbr concurrents", "Concurrent", "Marge prix + bas", "Sélection VF");

			$separateur = ";";

			// Affichage de la ligne de titre, terminée par un retour chariot
			echo utf8_decode(implode($separateur, $entete)."\r\n");

			// Affichage du contenu du tableau
			foreach ($lignes as $ligne) {
				echo utf8_decode(implode($separateur, $ligne)."\r\n");
			}

			exit;
		}else{

		?>

			<form method="POST" action="vente_flash_tab.php" id="myForm">
				<table class="tab_vf">
					<thead>
						<tr>
						<!-- -->
							<td></td>
							<td>Réf. </td>
							<td>Stock</td>
							<td>Vendus 3 mois</td>
							<td>Vendus 6 mois</td>
							<td>PPI</td>
							<td>PV actuel</td>
							<td>Marge avant remise</td>
						<!-- -->
							<td>Prix Vente Flash</td>
							<td>Remise prix VF</td>
							<td>Marge après remise</td>
						<!-- -->	
							<td>Prix le + bas</td>
							<td>Nb concurrents</td>
							<td>Concurrent</td>
							<td>Marge si prix + bas</td>
						<!-- -->	
							<td></td>
						</tr>
					</thead>
					<tbody>

					<?php

					foreach($tr as $marque=>$artcodes){
						ksort($artcodes);
						print "
						<tr>
							<td class=\"tab_marq\" id=\"a_$marque\" colspan=\"21\">".$marque."</td>
						</tr>
						".implode("",$artcodes)." ";
					}

					?>

						<tr>
							<td class="tab_sep" colspan="20">&nbsp;</td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<td colspan="3"><b>Total :</b> <?php echo $i; ?></td>
							<td colspan="3"><b>Sélectionné(s) :</b> <span id="selec"></td>
						</tr>
					</tfoot>
					</table>
					<div align="right"><input type="submit" class="submitVF"/></div>
				</table>
			</from>

			<br>

			<div align="left">
				<a href="./includes/vente_flash/tableau.php?action=stock&export=1" class="excelVF"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Exporter en Excel</a>
			</div>
		<?php
		}
	break;

	// Tableau Vus
	case 'vus':
		$stock=new stock($DB_site2);
		$tr=array();
		$lignes[] = array();
		$i=0;

		$req=$DB_site2->query("SELECT * FROM marque");
		while($m=$DB_site2->fetch_array($req)) 
			$marques[$m['marqueid']]=$m['libelle'];
		asort($marques);

		/*
		$sql= "SELECT page,COUNT(page) 
			FROM stat s INNER JOIN stat_page sp ON (s.id=sp.statid) 
			WHERE s.timestamp BETWEEN ".strtotime("-3 months",$from)." AND ".$to." 
			AND sp.page LIKE '/art,%' AND ip<>host 
			AND NOT (host LIKE '%".implode("%' OR host LIKE '%",$exclu)."%') 
			AND NOT ip IN ('".implode("','",$exclip)."') 
			GROUP BY substr(page,1,10) 
			ORDER BY COUNT(*) DESC LIMIT 100";
		*/
		
		$sql="SELECT DISTINCT(a.artid) as artid , SUM(av.nb) as somme FROM article AS a INNER JOIN article_vu AS av USING (artid) WHERE av.date BETWEEN ".strtotime("-3 months",$from)." AND ".$to." GROUP BY artid ORDER BY somme DESC LIMIT 100";

		$req=$DB_site2->query($sql);
		while($page=$DB_site2->fetch_array($req)){
			if($i==100) break;
			$art=article($page[artid],$page[somme]);
			
			$prix=$listeprix[$art['artid']];

			if(isset($spymini[$art['artcode']])){
				$spyht=$spymini[$art['artcode']]/(1+$prix['tva']/100);
				$margespy=$spyht-$prix['totalachat'];
				$pcmargespy=100*$margespy/$spyht;
			}

			if($prix['prix']>0)
				$pcspymini=round(-100*($prix['prix']-$spymini[$code])/$prix['prix']);
			else
				$pcspymini="";
			
			$qte6 = 0;
			/*
			$sql6 = "SELECT page, COUNT(page) 
			FROM stat s 
			INNER JOIN stat_page sp ON (s.id=sp.statid) 
			WHERE s.timestamp BETWEEN ".strtotime("-6 months",$from)." AND ".time()." 
			AND sp.page LIKE '/art,".$art['artid']."%' 
			AND ip<>host 
			AND NOT (host LIKE '%".implode("%' OR host LIKE '%",$exclu)."%') 
			AND NOT ip IN ('".implode("','",$exclip)."')";
			*/
			$sql6="SELECT SUM(nb) as somme FROM article_vu WHERE artid='".$art['artid']."' AND date BETWEEN ".strtotime("-6 months",$from)." AND ".$to." ";
			$req6=$DB_site2->query($sql6); 
			while($lignefact6 = $DB_site2->fetch_array($req6)){
				$qte6 = $lignefact6[1];
			}

			$ulspy = "";
			$reqs=$DB_site2->query("SELECT artcode,CAST( substring_index( group_concat( price ORDER BY timestamp DESC ) , ',', 1 ) AS char ) AS price,site FROM history WHERE timestamp>".$spytime." AND artcode='".$art['artcode']."' GROUP BY site ORDER BY price DESC");
			while($listspy=$DB_site2->fetch_array($reqs)){
				$ulspy .= "<li><b>".$listspy['site']."</b> - ".$listspy['price']."&euro;</li>";
			}

			//Article vente flash
			$artVF = $DB_site2->query_first("SELECT * FROM vente_flash_article WHERE artcode='".$art['artcode']."'");
		    
		    $instock=$stock->in_stock(intval($art['artid']));
		  	$art['stock']=intval($instock->qte);

		  	// Article actif ou remplaçant
			$active = $DB_site2->query_first("SELECT active, redirect FROM article WHERE artid=".$art['artid']);
			$tabAct = array('0','2','3');

		  	if(!in_array($active[0], $tabAct)){
			    $tr[$art['count']][$art['artcode']]="
			    <tr class=\"tr_vf\" id=\"tr_".$i."\" ".($artVF['id'] != '' ? "style=\"background-color:#00d796;\"" : "").">
			    	<td><img src=\"../configurations/".$host."/images/produits/br/".$art['artid'].".jpg\" width=\"50\" alt=\"".$art['artcode']."\"></td>
			    	<td>".$art['artcode']."<br><b>".$marques[$art['marqueid']]."</b></td>
			    	<td>".$art['stock']."</td>
			    	<td>".$art['count']."</td>
					<td>".$qte6."</td>
			    	<td>".number_format($prix['prixconseille'],2,',','')."&euro;</td>
			    	<td>".number_format($prix['prix'],2,',','')."&euro;<br><span class=\"vf_paht\">".$prix['paht']."&euro;</span></td>
			    	<td>".round($prix['pcmarge'],1)."%</td>

			    	<td class=\"inner-addon right-addon\"><i class=\"fa fa-eur\"></i>
						<input type=\"text\" name=\"prix_$art[artcode]\" id=\"num\" onkeyup=\"calcmarge($art[artid],this.value,".$prix['totalachat'].",".$prix['prixport'].",".$prix['tva'].");calcremise($art[artid],this.value,".$prix['prixconseille'].")\" size=\"6\" value=\"".($artVF['id'] != '' ? $artVF['prix'] : "")."\"/>
					</td>
			    	<td><span id=\"remise_$art[artid]\">".($artVF['id'] != '' ? phpremise($artVF['prix'],$prix['prixconseille']) : '')."</td>
			    	<td><span id=\"marge_$art[artid]\">".($artVF['id'] != '' ? phpmarge($artVF['prix'],$prix['totalachat'],$prix['prixport'],$prix['tva']) : '')."</td>

			    	<td>".($spymini[$art['artcode']]!="" ? number_format($spymini[$art['artcode']],2,',','')."&euro;" : "")."</td>
			    	<td>".(count($spylist[$art['artcode']])!=0 ? count($spylist[$art['artcode']]) : "")."</td>
					<td>
						<div class=\"tooltip\">".ucfirst($spyminisite[$art['artcode']])."
								<span class=\"tooltiptext tooltip-bottom\">".$ulspy."</span>
						</div>
					</td>
					<td>".(isset($spymini[$art['artcode']]) ? round($pcmargespy,1)."%" : "")."</td>

			    	<td><input type=\"checkbox\" id=\"checkvf_".$i."\" name=\"venteflashCheck[]\" value=\"$art[artcode]\" ".($artVF['id'] != '' ? "checked" : "")." onclick=\"changeCheck(this.id); chckPrix('remise_".$art['artid']."',".$i.");\"></td>
			    </tr>";

			    $i++;
			}

			if(!in_array($active[0], $tabAct)){
				$lignes[] = array(
					$marques[$art['marqueid']], 
					$art['artcode'], 
					$art['stock'], 
					$art['count'], 
					$qte6, 
					(number_format($prix['prixconseille'],2,',','')), 
					(number_format($prix['prix'],2,',','')), 
					(round($prix['pcmarge'])."%"),
					($artVF['id'] != '' ? $artVF['prix'] : ""), 
					($artVF['id'] != '' ? phpremise($artVF['prix'],$prix['prixconseille']) : ''), 
					($artVF['id'] != '' ? phpmarge($artVF['prix'],$prix['totalachat'],$prix['prixport'],$prix['tva']) : ''), 
					($spymini[$art['artcode']]!="" ? number_format($spymini[$art['artcode']],2,',','') : ""), 
					(count($spylist[$art['artcode']])!=0 ? count($spylist[$art['artcode']]) : ""), 
					ucfirst($spyminisite[$art['artcode']]), 
					(isset($spymini[$art['artcode']]) ? round($pcmargespy)."%" : ""), 
					($artVF['id']!="" ? "X" : "")
				);
			}
		}

		//Tri
		ksort($tr);

		if(isset($_GET['export']) && $_GET['export'] == 1){
			$filename = "VF_Vus_".date("d-m-Y",time());
			// CSV file header
			header("Content-type: application/vnd.ms-excel");
			header("Content-Encoding: UTF-8");
			header("Content-type: text/csv; charset=UTF-8");
			header("Content-disposition: csv" . date("Y-m-d") . ".csv");
			header("Content-disposition: filename=".$filename.".csv");

			// Création de la ligne d'en-tête
			$entete = array("Marque", "Ref Acilyon", "Stock", "Vus 3 mois", "Vus 6 mois", "PPI", "PV actuel", "Marge Av/Remise", "Prix VF", "Remise VF", "Marge Ap/Remise", "Prix + bas", "Nbr concurrents", "Concurrent", "Marge prix + bas", "Sélection VF");

			$separateur = ";";

			// Affichage de la ligne de titre, terminée par un retour chariot
			echo utf8_decode(implode($separateur, $entete)."\r\n");

			// Affichage du contenu du tableau
			foreach ($lignes as $ligne) {
				echo utf8_decode(implode($separateur, $ligne)."\r\n");
			}

			exit;
		}else{

		?>

			<form method="POST" action="vente_flash_tab.php" id="myForm">
				<table class="tab_vf">
					<thead>
						<tr>
						<!-- -->
							<td></td>
							<td>Réf. </td>
							<td>Stock</td>
							<td>Vus 3 mois</td>
							<td>Vus 6 mois</td>
							<td>PPI</td>
							<td>PV actuel</td>
							<td>Marge avant remise</td>
						<!-- -->
							<td>Prix Vente Flash</td>
							<td>Remise prix VF</td>
							<td>Marge après remise</td>
						<!-- -->	
							<td>Prix le + bas</td>
							<td>Nb concurrents</td>
							<td>Concurrent</td>
							<td>Marge si prix + bas</td>
						<!-- -->	
							<td></td>
						</tr>
					</thead>
					<tbody>

					<?php

					foreach($tr as $qte=>$artcodes){
						ksort($artcodes);
						print " ".implode("",$artcodes)." ";
					}

					?>

						<tr>
							<td class="tab_sep" colspan="20">&nbsp;</td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<td colspan="3"><b>Total :</b> <?php echo $i; ?></td>
							<td colspan="3"><b>Sélectionné(s) :</b> <span id="selec"></td>
						</tr>
					</tfoot>
					</table>
					<div align="right"><input type="submit" class="submitVF"/></div>
				</table>
			</from>

			<br>

			<div align="left">
				<a href="./includes/vente_flash/tableau.php?action=vus&export=1" class="excelVF"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Exporter en Excel</a>
			</div>
		<?php
		}
	break;

	// TABLEAU VENDUS
	case 'vendus':
		$plist = $pmarq = $marq = $pmarqid = $ptype = array();
		$tr = array();
		$lignes[] = array();
		$i = 0;

		$sql = "SELECT l.artcode,f.facturid,l.qte,l.prix,l.tva,am.marqueid,m.libelle,a.artcode_eurocave,a.active,a.catid,a.artid,(SELECT 'article') AS type 
    		FROM article a INNER JOIN `lignefacture` l ON (a.artid=l.artid) 
		    INNER JOIN facture f ON (f.facturid=l.factureid) 
		    INNER JOIN article_marque am ON (l.artid=am.artid) 
		    INNER JOIN marque m ON (am.marqueid=m.marqueid)
		    WHERE l.prix>=0 AND lignefacturedissid=0 AND packid=0
		    AND f.valide IN(1,5,7,8)
		    AND provenance IN ('','mobile','mp','sav')";
		$sql.=" AND UNIX_TIMESTAMP(f.datefacture)<=".$to." AND UNIX_TIMESTAMP(f.datefacture)>=".strtotime("-6 months",$from).$sqlmarq.$sqltype;
    	$req=$DB_site2->query($sql);
    	while($prod = $DB_site2->fetch_array($req)){
			vendus($prod);
	    }

	    $sql = "SELECT l.artcode,f.facturid,l.qte,l.prix,l.tva,am.marqueid,m.libelle,a.artcode_eurocave,a.active,a.catid,a.artid,(SELECT 'lot') AS type 
    		FROM lignefacturediss l INNER JOIN article a ON (a.artid=l.artid) 
    		INNER JOIN facture f ON (f.facturid=l.factureid) 
    		INNER JOIN article_marque am ON (l.artid=am.artid) 
    		INNER JOIN marque m ON (am.marqueid=m.marqueid)
    		WHERE l.prix>=0
    		AND f.valide IN(1,5,7,8)
    		AND provenance IN ('','mobile','mp','sav')";
    	$sql.=" AND UNIX_TIMESTAMP(f.datefacture)<=".$to." AND UNIX_TIMESTAMP(f.datefacture)>=".strtotime("-6 months",$from).$sqlmarq.$sqltype;
	    $req=$DB_site2->query($sql);
	    while($prod=$DB_site2->fetch_array($req)){
			vendus($prod);
	    }

	    $sql = "SELECT ap.artid,ap.artcode,f.facturid,SUM(l.qte) AS qte,SUM(l.prix)/qte AS prix,l.tva,ap.active,(SELECT 'pack') AS type
    		FROM lignefacture l INNER JOIN article a ON (a.artid=l.artid) 
    		INNER JOIN facture f ON (f.facturid=l.factureid) 
    		INNER JOIN article_marque am ON (l.artid=am.artid) 
    		INNER JOIN marque m ON (am.marqueid=m.marqueid) 
    		INNER JOIN article ap ON(ap.artid=l.packid)
    		WHERE l.prix>=0 AND packid>0
    		AND f.valide IN(1,5,7,8)
    		AND provenance IN ('','mobile','mp','sav')";
    	$sql.=" AND UNIX_TIMESTAMP(f.datefacture)<=".$to." AND UNIX_TIMESTAMP(f.datefacture)>=".strtotime("-6 months",$from).$sqlmarq.$sqltype;
    	$sql.=" GROUP BY facturid,packid";
	    $req=$DB_site2->query($sql);
	    while($prod=$DB_site2->fetch_array($req)){
			vendus($prod);
	    }

     	$mainlist=array();
		$mainlist=$plist;
		arsort($mainlist);

		foreach($mainlist as $code => $value){
			if($i==100) break;
			$prix = $listeprix[$pcode[$code]];

			if(isset($spymini[$code])){
				$spyht=$spymini[$code]/(1+$prix['tva']/100);
				$margespy=$spyht-$prix['totalachat'];
				$pcmargespy=100*$margespy/$spyht;
			}

			// COUNT(vendus)
			$qte3 = 0;
			switch($ptype[$code]){
				case 'article':
					$sql3 = "SELECT l.qte FROM article a INNER JOIN `lignefacture` l ON (a.artid=l.artid) INNER JOIN facture f ON (f.facturid=l.factureid) WHERE l.prix>=0 AND lignefacturedissid=0 AND packid=0 AND f.valide IN(1,5,7,8) AND provenance IN ('','mobile','mp','sav') AND l.artid=".$pcode[$code]." AND UNIX_TIMESTAMP(f.datefacture)<=".$to." AND UNIX_TIMESTAMP(f.datefacture)>=".strtotime("-3 months",$from);
					break;
				case 'lot':
					$sql3 = "SELECT l.qte FROM lignefacturediss l INNER JOIN article a ON (a.artid=l.artid) INNER JOIN facture f ON (f.facturid=l.factureid) WHERE l.prix>=0 AND f.valide IN(1,5,7,8) AND provenance IN ('','mobile','mp','sav') AND l.artid=".$pcode[$code]." AND UNIX_TIMESTAMP(f.datefacture)<=".$to." AND UNIX_TIMESTAMP(f.datefacture)>=".strtotime("-3 months",$from);
					break;
				case 'pack':
					$sql3 = "SELECT l.qte FROM lignefacture l INNER JOIN article a ON (a.artid=l.artid) INNER JOIN facture f ON (f.facturid=l.factureid) INNER JOIN article ap ON(ap.artid=l.packid) WHERE l.prix>=0 AND packid>0 AND f.valide IN(1,5,7,8) AND provenance IN ('','mobile','mp','sav') AND ap.artid=".$pcode[$code]." AND UNIX_TIMESTAMP(f.datefacture)<=".$to." AND UNIX_TIMESTAMP(f.datefacture)>=".strtotime("-3 months",$from);
					break;
			}
			$req3 = $DB_site2->query($sql3);
			while($lignefact3 = $DB_site2->fetch_array($req3)){
				$qte3 += $lignefact3[0];
			}

			if($prix['prix']>0)
				$pcspymini=round(-100*($prix['prix']-$spymini[$code])/$prix['prix']);
			else
				$pcspymini="";

			$ulspy = "";
			$reqs=$DB_site2->query("SELECT artcode,CAST( substring_index( group_concat( price ORDER BY timestamp DESC ) , ',', 1 ) AS char ) AS price,site FROM history WHERE timestamp>".$spytime." AND artcode='".$code."' GROUP BY site ORDER BY price DESC");
			while($listspy=$DB_site2->fetch_array($reqs)){
				$ulspy .= "<li><b>".$listspy['site']."</b> - ".$listspy['price']."&euro;</li>";
			}

			//Article vente flash
			$artVF = $DB_site2->query_first("SELECT * FROM vente_flash_article WHERE artcode='".$code."'");
		    
		    $instock=$stock->in_stock(intval($pcode[$code]));
		  	$art['stock']=intval($instock->qte);

		  	// Article actif ou remplaçant
			$active = $DB_site2->query_first("SELECT active, redirect FROM article WHERE artid=".$pcode[$code]);
			$tabAct = array('0','2','3');

		  	if(!in_array($active[0], $tabAct)){
			    $tr[$plist[$code]][$code]="
			    <tr class=\"tr_vf\" id=\"tr_".$i."\" ".($artVF['id'] != '' ? "style=\"background-color:#00d796;\"" : "").">
			    	<td><img src=\"../configurations/".$host."/images/produits/br/".$pcode[$code].".jpg\" width=\"50\" alt=\"".$code."\"></td>
			    	<td>".$code."<br><b>".$pmarq[$code]."</b></td>
			    	<td>".$art['stock']."</td>
			    	<td>".$qte3."</td>
					<td>".$plist[$code]."</td>
			    	<td>".number_format($prix['prixconseille'],2,',','')."&euro;</td>
			    	<td>".number_format($prix['prix'],2,',','')."&euro;<br><span class=\"vf_paht\">".$prix['paht']."&euro;</span></td>
			    	<td>".round($prix['pcmarge'],1)."%</td>

			    	<td class=\"inner-addon right-addon\"><i class=\"fa fa-eur\"></i>
						<input type=\"text\" name=\"prix_$code\" id=\"num\" onkeyup=\"calcmarge($pcode[$code],this.value,".$prix['totalachat'].",".$prix['prixport'].",".$prix['tva'].");calcremise($pcode[$code],this.value,".$prix['prixconseille'].")\" size=\"6\" value=\"".($artVF['id'] != '' ? $artVF['prix'] : "")."\"/>
					</td>
			    	<td><span id=\"remise_$pcode[$code]\">".($artVF['id'] != '' ? phpremise($artVF['prix'],$prix['prixconseille']) : '')."</td>
			    	<td><span id=\"marge_$pcode[$code]\">".($artVF['id'] != '' ? phpmarge($artVF['prix'],$prix['totalachat'],$prix['prixport'],$prix['tva']) : '')."</td>

			    	<td>".($spymini[$code]!="" ? number_format($spymini[$code],2,',','')."&euro;" : "")."</td>
			    	<td>".(count($spylist[$code])!=0 ? count($spylist[$code]) : "")."</td>
					<td>
						<div class=\"tooltip\">".ucfirst($spyminisite[$code])."
								<span class=\"tooltiptext tooltip-bottom\">".$ulspy."</span>
						</div>
					</td>
					<td>".(isset($spymini[$code]) ? round($pcmargespy,1)."%" : "")."</td>

			    	<td><input type=\"checkbox\" id=\"checkvf_".$i."\" name=\"venteflashCheck[]\" value=\"$code\" ".($artVF['id'] != '' ? "checked" : "")." onclick=\"changeCheck(this.id); chckPrix('remise_".$pcode[$code]."',".$i.");\"></td>
			    </tr>";

			    $i++;
			}

			if(!in_array($active[0], $tabAct)){
				$lignes[] = array(
					$pmarq[$code], 
					$code, 
					$art['stock'], 
					$qte3, 
					$plist[$code], 
					(number_format($prix['prixconseille'],2,',','')), 
					(number_format($prix['prix'],2,',','')), 
					(round($prix['pcmarge'])."%"),
					($artVF['id'] != '' ? $artVF['prix'] : ""), 
					($artVF['id'] != '' ? phpremise($artVF['prix'],$prix['prixconseille']) : ''), 
					($artVF['id'] != '' ? phpmarge($artVF['prix'],$prix['totalachat'],$prix['prixport'],$prix['tva']) : ''), 
					($spymini[$code]!="" ? number_format($spymini[$code],2,',','') : ""), 
					(count($spylist[$code])!=0 ? count($spylist[$code]) : ""), 
					ucfirst($spyminisite[$code]), 
					(isset($spymini[$code]) ? round($pcmargespy)."%" : ""), 
					($artVF['id']!="" ? "X" : "")
				);
			}
		}

		if(isset($_GET['export']) && $_GET['export'] == 1){
			$filename = "VF_Vendus_".date("d-m-Y",time());
			// CSV file header
			header("Content-type: application/vnd.ms-excel");
			header("Content-Encoding: UTF-8");
			header("Content-type: text/csv; charset=UTF-8");
			header("Content-disposition: csv" . date("Y-m-d") . ".csv");
			header("Content-disposition: filename=".$filename.".csv");

			// Création de la ligne d'en-tête
			$entete = array("Marque", "Ref Acilyon", "Stock", "Vendus 3 mois", "Vendus 6 mois", "PPI", "PV actuel", "Marge Av/Remise", "Prix VF", "Remise VF", "Marge Ap/Remise", "Prix + bas", "Nbr concurrents", "Concurrent", "Marge prix + bas", "Sélection VF");

			$separateur = ";";

			// Affichage de la ligne de titre, terminée par un retour chariot
			echo utf8_decode(implode($separateur, $entete)."\r\n");

			// Affichage du contenu du tableau
			foreach ($lignes as $ligne) {
				echo utf8_decode(implode($separateur, $ligne)."\r\n");
			}

			exit;
		}else{

		?>

			<form method="POST" action="vente_flash_tab.php" id="myForm">
				<table class="tab_vf">
					<thead>
						<tr>
						<!-- -->
							<td></td>
							<td>Réf. </td>
							<td>Stock</td>
							<td>Vendus 3 mois</td>
							<td>Vendus 6 mois</td>
							<td>PPI</td>
							<td>PV actuel</td>
							<td>Marge avant remise</td>
						<!-- -->
							<td>Prix Vente Flash</td>
							<td>Remise prix VF</td>
							<td>Marge après remise</td>
						<!-- -->	
							<td>Prix le + bas</td>
							<td>Nb concurrents</td>
							<td>Concurrent</td>
							<td>Marge si prix + bas</td>
						<!-- -->	
							<td></td>
						</tr>
					</thead>
					<tbody>

					<?php

					foreach($tr as $qte=>$artcodes){
						ksort($artcodes);
						print " ".implode("",$artcodes)." ";
					}

					?>

						<tr>
							<td class="tab_sep" colspan="20">&nbsp;</td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<td colspan="3"><b>Total :</b> <?php echo $i; ?></td>
							<td colspan="3"><b>Sélectionné(s) :</b> <span id="selec"></td>
						</tr>
					</tfoot>
					</table>
					<div align="right"><input type="submit" class="submitVF"/></div>
				</table>
			</from>

			<br>

			<div align="left">
				<a href="./includes/vente_flash/tableau.php?action=vendus&export=1" class="excelVF"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Exporter en Excel</a>
			</div>
		<?php
		}
	break;
}

function phpmarge($prix, $totalachat, $frais, $tva){
	$totalvente = $prix/(1+$tva/100)+$frais;
	$marge 		= $totalvente-$totalachat;
	$margeap 	= round(100*$marge/$totalvente,1);
	return $margeap."%";
}

function phpremise($prix, $ppi){
	$total 	= ($prix*100)/$ppi;
	$remise = round(100-$total,1);
	return $remise."%";
}


function article($artid, $count){
    global $DB_site2;
	$article=$DB_site2->query_first("SELECT artid, artcode, artcode_eurocave, libelleFR, marqueid, prix, prixht, paht, ecopartht, emballage_poids, tauxchoisi, active FROM article INNER JOIN article_marque USING(artid) WHERE artid=".$artid);
	$article['count']=$count;
	if($article['artcode']!="") 
		return $article;
}


function vendus($prod){
    global $DB_site2;
    global $plist,$pmarq,$pcode,$pmarqid,$ptype;

    $plist[$prod['artcode']]		+=	$prod['qte'];
    $pcode[$prod['artcode']]		=	$prod['artid'];
    $pmarq[$prod['artcode']]		=	$prod['libelle'];
    $pmarqid[$prod['artcode']]		= 	$prod['marqueid'];
    $ptype[$prod['artcode']]		=	$prod['type'];
}