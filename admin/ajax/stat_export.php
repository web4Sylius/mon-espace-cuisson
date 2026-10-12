<?php 

require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");
include $_SERVER['DOCUMENT_ROOT']."/class/stock.class.php";
include $_SERVER['DOCUMENT_ROOT']."/class/facture.class.php";

$current_url = explode("?", $_SERVER['REQUEST_URI']);

//Action
switch($_GET['action']){
	//Export Stat Articles Vendus
	case 'vendu':
		//Date
		$tsfrom = $tsto = 0;
		if(isset($_GET['from']) && $_GET['from'] != ""){
		    $sp = explode("/",$_GET['from']);
		    $tsfrom = mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
		}

		if(isset($_GET['to']) && $_GET['to'] != ""){
		    $sp = explode("/",$_GET['to']);
		    $tsto = mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
		}

		if(isset($_GET['prov']) && !is_array($_GET['prov'])) $_GET['prov']=array($_GET['prov']);
		if(!isset($_GET['valide'])) $_GET['valide']=array("1,7","5,8");
		if(!is_array($_GET['valide'])) $_GET['valide']=array($_GET['valide']);

		$rq=$DB_site2->query("SELECT * FROM marque ORDER BY libelle");
		while($m=$DB_site2->fetch_array($rq)){
		    $marques[$m['marqueid']]=$m['libelle'];
		}

		//Requête 1
		$sql = "SELECT l.artcode,f.facturid,l.qte,l.prix,l.tva,am.marqueid,m.libelle,a.artcode_eurocave,a.active,a.catid,a.artid,c.libelleFR 
    			FROM article a INNER JOIN `lignefacture` l ON (a.artid=l.artid) 
    			INNER JOIN facture f ON (f.facturid=l.factureid) 
    			INNER JOIN article_marque am ON (l.artid=am.artid) 
    			INNER JOIN marque m ON (am.marqueid=m.marqueid)
    			INNER JOIN categorie c ON (c.catid = a.catid)
    			WHERE l.prix>=0 AND lignefacturedissid=0 AND packid=0";

    	if(isset($_GET['valide'])) $sql.=" AND f.valide IN (".implode(",",$_GET['valide']).")";
    	$sql.=" AND UNIX_TIMESTAMP(f.datefacture)<=".$tsto." AND UNIX_TIMESTAMP(f.datefacture)>=".$tsfrom;

    	if($_GET['temp']==1) 
			$sql.=" AND ((a.artid IN (SELECT artid FROM temp_price WHERE (du<=".$tsfrom." AND au>=".$tsto.") OR (du<".$tsfrom." AND au>=".$tsto.") OR (du>=".$tsfrom." AND au<".$tsto."))) OR a.artcode IN (SELECT vfa.artcode FROM vente_flash_article vfa INNER JOIN vente_flash vf ON(vf.id=vfa.vfid) WHERE (du<=".$tsfrom." AND au>=".$tsto.") OR (du<".$tsfrom." AND au>=".$tsto.") OR (du>=".$tsfrom." AND au<".$tsto.")))";
    
    	if(isset($marque) && $marque!="") $sql.=" AND am.marqueid=".$marque;
    	if(isset($_GET['prov'])) $sql.=" AND provenance IN ('".implode("','",$_GET['prov'])."')";
		
    	//Requête 2
		$sql2 = "SELECT l.artcode,f.facturid,l.qte,l.prix,l.tva,am.marqueid,m.libelle,a.artcode_eurocave,a.active,a.artid,a.catid,c.libelleFR 
    			FROM lignefacturediss l INNER JOIN article a ON (a.artid=l.artid) 
    			INNER JOIN facture f ON (f.facturid=l.factureid) 
    			INNER JOIN article_marque am ON (l.artid=am.artid) 
    			INNER JOIN marque m ON (am.marqueid=m.marqueid)
    			INNER JOIN categorie c ON (c.catid = a.catid)
    			WHERE l.prix>=0";

    	if(isset($_GET['valide'])) $sql2.=" AND f.valide IN (".implode(",",$_GET['valide']).")";
    	$sql2.=" AND UNIX_TIMESTAMP(f.datefacture)<=".$tsto." AND UNIX_TIMESTAMP(f.datefacture)>=".$tsfrom;

    	if($_GET['temp']==1) 
			$sql2.=" AND ((a.artid IN (SELECT artid FROM temp_price WHERE (du<=".$tsfrom." AND au>=".$tsto.") OR (du<".$tsfrom." AND au>=".$tsto.") OR (du>=".$tsfrom." AND au<".$tsto."))) OR a.artcode IN (SELECT vfa.artcode FROM vente_flash_article vfa INNER JOIN vente_flash vf ON(vf.id=vfa.vfid) WHERE (du<=".$tsfrom." AND au>=".$tsto.") OR (du<".$tsfrom." AND au>=".$tsto.") OR (du>=".$tsfrom." AND au<".$tsto.")))";
    
    	if(isset($marque) && $marque!="") $sql2.=" AND am.marqueid=".$marque;
    	if(isset($_GET['prov'])) $sql2.=" AND provenance IN ('".implode("','",$_GET['prov'])."')";

    	//Requête 3
    	$sql3 = "SELECT ap.artid,ap.artcode,f.facturid,SUM(l.qte)/(select SUM(qte) FROM article_pack where packid=ap.artid) AS qte,SUM(l.prix)/qte AS prix,l.tva,ap.active,c.libelleFR
	    		FROM lignefacture l INNER JOIN article a ON (a.artid=l.artid) 
	    		INNER JOIN facture f ON (f.facturid=l.factureid) 
	    		INNER JOIN article_marque am ON (l.artid=am.artid) 
	    		INNER JOIN marque m ON (am.marqueid=m.marqueid) 
	    		INNER JOIN article ap ON(ap.artid=l.packid)
	    		INNER JOIN categorie c ON (c.catid = a.catid)
	    		WHERE l.prix>=0 AND packid>0";

	    if(isset($_GET['valide'])) $sql3.=" AND f.valide IN (".implode(",",$_GET['valide']).")";
	    $sql3.=" AND UNIX_TIMESTAMP(f.datefacture)<=".$tsto." AND UNIX_TIMESTAMP(f.datefacture)>=".$tsfrom;

	    if(isset($marque) && $marque!="") $sql3.=" AND am.marqueid=".$marque;
	    if(isset($_GET['prov'])) $sql3.=" AND provenance IN ('".implode("','",$_GET['prov'])."')";
	    $sql3.=" GROUP BY facturid,packid";

		$cat = array();
		$plist=$pcat=$pca=$pcaht=$pcahtcp=$pmarq=$pfab=$pactive=$pcat=$pcatqte=$marq=$marqqte=array();
		$visibletxt=array(0=>"Inactif",1=>"Actif",2=>"Supprimé",3=>"Archivé");

		$req = $DB_site2->query($sql);
		$req2 = $DB_site2->query($sql2);
		$req3 = $DB_site2->query($sql3);

		while($prod = $DB_site2->fetch_array($req)){
		    tableaux($prod);
		}

		while($prod = $DB_site2->fetch_array($req2)){
		    tableaux($prod);
		}

		while($prod = $DB_site2->fetch_array($req3)){
		    tableaux($prod);
		}

		$mainlist=array();
		$mainlist=$plist;
    	arsort($mainlist);

		//Création des lignes du tableau
		switch ($_GET['export']) {
			case 'art':
				// Création de la ligne d'en-tête
				$entete = array("Réf. Acilyon","Artcode","Categorie","Etat","Marque","Qté","CA","CA HT","CA HT Réduc");
				foreach($mainlist as $code=>$value){
				    if(in_array(substr($code,0,3),array("PAK","ACI"))){
				      	$lignes[] = array(
							$code, 
							$pfab[$code],
							$pcat[$code],
							$visibletxt[$pactive[$code]],
							$pmarq[$code],
							$plist[$code],
							number_format($pca[$code],2,',',''),
							number_format($pcaht[$code],2,',',''),
							number_format($pcahtcp[$code],2,',','')
						);
				    }
				}
				break;

			case 'categ':
				// Création de la ligne d'en-tête
				$entete = array("Catégorie","Qté","CA HT");

				foreach($cat as $catid=>$mca){
					$catname=$DB_site2->query_first("SELECT * FROM categorie WHERE catid=".intval($catid));
					if($catname['parentid']==0) $catname['parentid']=$catid;
					$pcat[$catname['parentid']]+=$mca;
					$pcatqte[$catname['parentid']]+=$catqte[$catid];
					$parentcat[$catname['parentid']][]=$catid;
				}

				$mainlist=$pcat;

				foreach($mainlist as $catid=>$ca){
					$catname = $DB_site2->query_first("SELECT * FROM categorie WHERE catid=".intval($catid));
					$lignes[] = array(
						html_entity_decode($catname['libelleFR']),
						$pcatqte[$catid],
						number_format($pcat[$catid],2,',','')
					);
				}
				break;

			case 'marq':
				// Création de la ligne d'en-tête
				$entete = array("Marque","Qté","CA HT",);

				$marqlist=$marq;
				arsort($marqlist);

				foreach($marqlist as $marqueid=>$ca){
					$lignes[] = array(
						html_entity_decode($marques[$marqueid]),
						$marqqte[$marqueid],
						number_format($marq[$marqueid],2,',','')
					);
				}
				break;
		}
		break;

	//Export Stat Articles Vus
	case 'vu':
		// Création de la ligne d'en-tête
		$entete = array("Réf. Acilyon","Artcode","Catégorie","Etat","Marque","Nb vues","Prix vente","% Marge", "Nb Spy", "Prix mini spy", "Site", "% Marge spy", "Ventes", "Ventes/Vus", "Devis", "Devis/Vus", "Qté Stock");

		//Date
		if(isset($_GET['from']) && isset($_GET['to'])){
		    $sp=explode("/",$_GET['from']);
		    $from=mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
		    $sp=explode("/",$_GET['to']);
		    $to=mktime(23,59,59,$sp[1],$sp[0],$sp[2]);
		}

		$exclu=array("googlebot","as29017","msn","voilabot","ahref","bingbot","acoon","google","yandex","exabot","torservers","as13285");
		$exclip=array("80.12.82.212","88.184.216.175");
		$txfrance=$DB_site2->query_first("SELECT 0,TVAtauxnormal,TVAtauxreduit FROM pays WHERE paysid=57");

		$stock=new stock($DB_site2);

		$req=$DB_site2->query("SELECT * FROM marque");
		while($m=$DB_site2->fetch_array($req)) $marques[$m['marqueid']]=$m['libelle'];
		asort($marques);

		$visibletxt=array(0=>"Inactif",1=>"Actif",2=>"Supprimé",3=>"Archivé");

		//Requête
		/*
		$sql="SELECT page,COUNT(page) FROM stat s INNER JOIN stat_page sp ON (s.id=sp.statid) WHERE s.timestamp BETWEEN ".$from." AND ".$to." AND sp.page LIKE '/art,%' AND ip<>host AND NOT (host LIKE '%".implode("%' OR host LIKE '%",$exclu)."%') AND NOT ip IN ('".implode("','",$exclip)."') GROUP BY substr(page,1,10) ORDER BY COUNT(*) DESC LIMIT 500";
		*/
		$sql="SELECT DISTINCT(a.artid) as artid , SUM(av.nb) as somme FROM article AS a INNER JOIN article_vu AS av USING (artid) WHERE av.date BETWEEN ".$from." AND ".$to." GROUP BY artid ORDER BY somme DESC LIMIT 500";
		$req=$DB_site2->query($sql);
		while($page=$DB_site2->fetch_array($req)){

		    $art=article($page[artid],$page[somme],$from,$to);

		    if($_GET['marque']==0 || $_GET['marque']==$art['marqueid']){
		      	$instock=$stock->in_stock(intval($art['artid']));
		      	$art['stock']=intval($instock->qte);

		      	$lignes[] = array(
			      	$art['artcode'],
			      	$art['artcode_eurocave'],
			      	$art['libelleFR'],
			      	$visibletxt[$art['active']],
			      	$marques[$art['marqueid']],
			      	$page[1],
			      	number_format($art['prix'],2,',','')."&euro;",
			      	round($art['pcmarge'],2)."%",
		    		$art['spynb'],
		    		$art['spymini'],
		    		$art['spysite'],
		    		round($art['spymarge'],2)."%",
		    		$art['ventes'],
		    		round($art['ventesvu'],2)."%",
		    		$art['devis'],
		    		round($art['devisvu'],2)."%",
		    		$art['stock']
	    		);
		    }
		}
		break;
}

//Filename
$filename = "Stat_".$_GET['file']."_".date("d-m-Y",time());

// CSV file header
header("Content-type: application/vnd.ms-excel");
header("Content-Encoding: UTF-8");
header("Content-type: text/x-csv; charset=UTF-8");
header("Content-disposition: csv" . date("Y-m-d") . ".csv");
header("Content-disposition: filename=".$filename.".csv");

$separateur = ";";

// Affichage de la ligne de titre, terminée par un retour chariot
echo utf8_decode(implode($separateur, $entete)."\r\n");

// Affichage du contenu du tableau
foreach ($lignes as $ligne) {
	echo utf8_decode(implode($separateur, $ligne)."\r\n");
}

//FUNCTION
function article($artid,$count,$from,$to){
	global $DB_site2;
    global $txfrance;

    $spytime=time()-7*86400;
	$article=$DB_site2->query_first("SELECT a.artid,a.artcode,a.artcode_eurocave,a.libelleFR,am.marqueid,a.prix,a.prixht,a.paht,a.ecopartht,a.emballage_poids,a.tauxchoisi,a.active,c.libelleFR FROM article a INNER JOIN article_marque am USING(artid) INNER JOIN categorie c ON (c.catid = a.catid) WHERE a.artid=".$artid);

	$txtva=(1+$txfrance[$article['tauxchoisi']]/100);
	$fraisport=$DB_site2->query_first("SELECT * FROM fraisport WHERE paysid=57 AND ".(1000*$article['emballage_poids'])." BETWEEN poiddebut AND poidfin");

	if($article['prix']>$fraisport['gratuit']) $fraisport['prix']=0;
	$fraisport['prix']=$fraisport['prix']/$txtva;

	$garantie=0;
	$garanties=$DB_site2->query("SELECT (select achat from article_caractval inner join caracteristiquevaleur using(caractvalid) where artid=$article[artid] and caractid=cv.caractid order by position limit 1) FROM article_caractval ac INNER JOIN caracteristiquevaleur cv USING (caractvalid) WHERE artid=$article[artid] group by caractid");
	while($g=$DB_site2->fetch_array($garanties)) $garantie+=$g[0];

	$article['pcmarge']=100*($article['prixht']+$fraisport['prix']-$article['paht']-$article['ecopartht']-$fraisport['achat']-$garantie)/$article['prixht'];
	$article['count']=$count;

	$req=$DB_site2->query("SELECT price,site FROM history WHERE artcode='".$article['artcode']."' AND timestamp>".$spytime." GROUP BY site ORDER BY price");
	$spy=$DB_site2->fetch_array($req);
	if($spy['price']!=""){
		$article['spynb']=$DB_site2->num_rows($req);
		$article['spymini']=$spy['price'];
		$article['spysite']=$spy['site'];
		$article['spymarge']=round(100*(($spy['price']/$txtva)+$fraisport['prix']-$article['paht']-$article['ecopartht']-$fraisport['achat']-$garantie)/$article['prixht'],2);
	}

	$ventes=$DB_site2->query_first("SELECT SUM(qte) FROM lignefacture lf INNER JOIN facture f ON(f.facturid=lf.factureid) WHERE artid=".$article['artid']." AND valide>0 AND provenance='' AND heurefacture BETWEEN $from AND $to");
	$venteslot=$DB_site2->query_first("SELECT SUM(qte) FROM lignefacturediss lf INNER JOIN facture f ON(f.facturid=lf.factureid) WHERE artid=".$article['artid']." AND valide>0 AND provenance='' AND heurefacture BETWEEN $from AND $to");
	$devis=$DB_site2->query_first("SELECT SUM(qte) FROM lignefacture lf INNER JOIN facture f ON(f.facturid=lf.factureid) WHERE artid=".$article['artid']." AND valide=0 AND provenance='' AND heurefacture BETWEEN $from AND $to");
	$article['ventes']=intval($ventes[0])+intval($venteslot[0]);
	$article['devis']=intval($devis[0]);
	$article['ventesvu']=100*$ventes[0]/$count;
	$article['devisvu']=100*$devis[0]/$count;

	if($article['artcode']!="") return $article;
    
}

function tableaux($prod){
    global $DB_site2;
    global $plist,$pcat,$pca,$pcaht,$pcahtcp,$pmarq,$pfab,$pactive,$cat,$catqte,$marq,$marqqte,$img;
    
    $facture=new facture($DB_site2,$prod['facturid']);

    $plist[$prod['artcode']]	+= 	$prod['qte'];
    $pca[$prod['artcode']]		+= 	$prod['qte']*$prod['prix'];
    $prixht						= 	$prod['qte']*$prod['prix']/(1+$prod['tva']/100);
    $pcaht[$prod['artcode']]	+=	$prixht;
    if($facture->totallignesht>0) 
    	$pcahtcp[$prod['artcode']]	+=	$prixht-$facture->cadeauht*($prixht/$facture->totallignesht);
    $pmarq[$prod['artcode']]	=	$prod['libelle'];
    $pfab[$prod['artcode']]		=	$prod['artcode_eurocave'];
    $pcat[$prod['artcode']]		=	$prod['libelleFR'];
    $pactive[$prod['artcode']]	=	$prod['active'];

    $cat[$prod['catid']]		+=	$prod['qte']*$prod['prix']/(1+$prod['tva']/100);
    $catqte[$prod['catid']]		+=	$prod['qte'];

    $marq[$prod['marqueid']]	+=	$prod['qte']*$prod['prix']/(1+$prod['tva']/100);
    $marqqte[$prod['marqueid']]	+=	$prod['qte'];
}

exit;
?>
