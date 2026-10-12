<?php
include_once $rootpath."class/DAOarticle.class.php";
include_once $rootpath."class/garantie.class.php";
//include_once $rootpath."class/DAOgarantie.class.php";
?>
<script>
function affiche_cache(id){
	if(document.getElementById(id).style.display=='block'){
		document.getElementById(id).style.display='none';
	} else {
		document.getElementById(id).style.display='block';
	}
}
</script>
<?php


function couleur_marge($pct) {
	$pct_txt=number_format($pct, 1, ',', ' ');
	if($pct<=0){
		$return="<font color=\"#ff00ff\"><b>".$pct_txt." %</b></font>";
	}
	if($pct>0 && $pct<=20){
		$return="<font color=\"#ff0000\"><b>".$pct_txt." %</b></font>";
	}
	if($pct>20 && $pct<=25){
		$return="<font color=\"#ffaa00\"><b>".$pct_txt." %</b></font>";
	}
	if($pct>25 && $pct<=30){
		$return="<font color=\"#22CC22\"><b>".$pct_txt." %</b></font>";
	}
	if($pct>30 && $pct<=40){
		$return="<font color=\"#339933\"><b>".$pct_txt." %</b></font>";
	}
	if($pct>40){
		$return="<font color=\"#3333ff\"><b>".$pct_txt." %</b></font>";
	}
	return $return;
}

$sql = "SELECT *";
$sql .= " FROM pays";
$sql .= " WHERE paysid=57";
$tvafrance = $DB_site2->query_first($sql);

$tvanormal = (1+$tvafrance['TVAtauxnormal']/100);
/*
$tvareduit = (1+$tvafrance['TVAtauxreduit']/100);

$sql = "SELECT TVAtauxnormal, TVAtauxreduit";
$sql .= " FROM pays";
$sql .= " WHERE paysid=".$utilisateur['paysid'];
$tvapays = $DB_site2->query_first($sql);
*/

function article_vignette($artid, $infossup, $rubrique=""){
	global $DB_site2;
	global $utilisateur;
	global $tvanormal;
	$ret=array();
	foreach ($infossup as $key => $value) {
		if(!empty($value)) $ret[$key]=$value;
	}
	$article = new article($DB_site2);
	$article->load($artid);
	$article->setpays($utilisateur['paysid_livraison']);
	$frais_port = new frais_port($DB_site2,array($artid=>1),$utilisateur['paysid_livraison'],$article->prix, $article->paht + $article->ecopartht);
	$article_array=$DB_site2->query_first("SELECT prixconseilleht, commandable FROM article WHERE artid='".$artid."' ");
	$prixcaract = array(
		'achat' => 0,
		'prixht' => 0,
		'prix' => 0
	);
	if($ret['caractvalid'] == ""){
		$sqlcaract = "	SELECT differenceprix, differenceprixht, achat, libelleFR FROM caracteristiquevaleur AS cv
						INNER JOIN `article_caractval` USING(caractvalid)
						WHERE caractvalid=(
							SELECT caractvalid FROM caracteristiquevaleur 
							INNER JOIN `article_caractval` USING(caractvalid)
							WHERE artid=".$artid."
							AND caractid=cv.caractid
							ORDER BY position LIMIT 1
						)
						AND artid=".$artid;
	} else {
		$sqlcaract = "	SELECT differenceprix, differenceprixht, achat, cv.libelleFR, c.libelleFR AS caract FROM article_caractval
						INNER JOIN caracteristiquevaleur cv USING(caractvalid)
						INNER JOIN caracteristique c USING(caractid)
						WHERE artid=".$artid." AND caractvalid IN (".$ret['caractvalid'].")";
	}
	$caracts = $DB_site2->query($sqlcaract);

	$cpt = 0;
	$propositions_dispo = '<ul>';
	while($caract = $DB_site2->fetch_array($caracts)){
		$differenceprixht=unserialize($caract['differenceprixht']);
		$differenceprix=unserialize($caract['differenceprix']);
		$prixcaract['achat']  += $caract['achat'];
		$prixcaract['prixht'] += $differenceprixht['base'];
		$prixcaract['prix']   += $differenceprix['base'];
		$ret['options'][$cpt]['caracteristique'] = $caract['caract'];
		$ret['options'][$cpt]['libelle'] = $caract['libelleFR'];
		$ret['options'][$cpt]['differenceprix'] = $prixcaract['prix'];
		$propositions_dispo .= "<li>"
			. $caract['caract']." : ".$caract['libelleFR']
			. "</li>";
		$cpt++;
	}
	$propositions_dispo.="</ul>";
	
	$article_fournisseur=$DB_site2->query_first("SELECT ecopartht, trspt FROM article_fournisseur WHERE artid=".$artid." AND principal LIKE '%base%' AND devise='EUR' ");
	if($article_fournisseur['ecopartht']==""){
		$article_fournisseur['ecopartht']=0;
	}
	if($article_fournisseur['trspt']==""){
		$article_fournisseur['trspt']=0;
	}
	
	/*
	// $prixtotal = $frais_port->prixttc( ($tvanormal - 1) * 100) + $article->prix + $prixcaract['prix'];
	$prixtotal = $article->prix + $prixcaract['prix'];
	// $article_paht_total = $article->paht + $article->ecopartht + $frais_port->paht + $prixcaract['achat'];
	$article_paht_total = $article->paht + $article->ecopartht + $prixcaract['achat'];
	// $article_pv_ht_total = $article->prixht + $frais_port->prixht + $prixcaract['prixht'];
	$article_pv_ht_total = $article->prixht + $prixcaract['prixht'];
	*/
	
	$lesGaranties=DAOgarantie::loadMaxGarantieArtByPays($DB_site2,'base','FR','57',$artid,'',$article->prix);
	$i=0;
	$garantie=0;
	$garantie_txt="";
	if(!empty($lesGaranties)){
		foreach ($lesGaranties as $laGarantie) {
			$i++;
			if ($i==1){
				$garantie=round($laGarantie->paht);
				$garantie_txt.=$laGarantie->comm;
			}
			if ($laGarantie->cumul==1){
				$garantie+=round($laGarantie->paht);
				$garantie_txt.=$laGarantie->comm;
			}
		}
	}
	
	
	$sql="SELECT prix, paht FROM temp_price WHERE artid=".$artid." AND du < '".time()."' AND au > '".time()."' LIMIT 1";
	$temp_price=$DB_site2->query_first($sql);
	if($temp_price['prix']!=""){
		$article->prix = $temp_price['prix'];
	}
	
	$sql="	SELECT foa.offre, foa.value FROM fournisseur_offre AS fo
			INNER JOIN fournisseur_offre_article AS foa ON(fo.id=foa.offreid)
			WHERE foa.artid=".$artid." AND fo.du < '".time()."' AND fo.au > '".time()."' LIMIT 1";
	$offre_fournisseur=$DB_site2->query_first($sql);
	if($offre_fournisseur['offre']!=""){
		if($offre_fournisseur['offre']=="temp"){
			$article->paht = $offre_fournisseur['value'];
		}
		if($offre_fournisseur['offre']=="reduc"){
			$article->paht = $article->paht - $offre_fournisseur['value'];
		}
		if($offre_fournisseur['offre']=="pc"){
			$article->paht = $article->paht - ($article->paht * $offre_fournisseur['value'] / 100);
		}
	}
	
	$prixtotal = $article->prix + $prixcaract['prix'];
	$prixtotaltrspt = $prixtotal + ($frais_port->prixht * $tvanormal);
	$article_paht_total = $article->paht + $article->ecopartht + $article_fournisseur['trspt'] + $garantie + $frais_port->paht + $prixcaract['achat'];
	$article_pv_ht_total = $article->prixht + $prixcaract['prixht'] + $frais_port->prixht;
	$marge = round($article_pv_ht_total - $article_paht_total, 2);
	$marge_pct = 100 * $marge / $article_pv_ht_total;
	
	$stock = new stock($DB_site2);
	$items1 = $stock->search($article->artid, 217); // stock acilyon
	$items2 = $stock->search($article->artid, 630900); // stock ginko
	$items3 = $stock->search($article->artid, 632925); // stock ajd
	$delailivraison = delailivraison($article->artid, $ret['qte']);
	
	$ret['rows'] = array();
	$ret['artid']            = $artid;
	$ret['nb']               = $nb;
	$ret['img']              = "<a href=\"clientnavig.php?userid=".$utilisateur['userid']."&goto=art,".$article->artid.",-.html\" target=\"_blank\">";
	$ret['img']             .= file_exists($_SERVER['DOCUMENT_ROOT']."/configurations/www.mon-espace-cuisson.com/images/produits/br/".$article->artid.".jpg") ? "<img src=\"/configurations/www.mon-espace-cuisson.com/images/produits/br/".$article->artid.".jpg\" style=\"max-height: 70px; max-width: 70px;\" height=\"70\">" : "<img src=\"/admin/images/visuel_indisponible_vignette.jpg\" height=\"70\">";
	$ret['img']             .= "</a>";
	$ret['artcode']          = $article->artcode;
    $ret['libelleFR']        = $article->libelle;
	$ret['artcode_mcav']     = "<a href=\"ajout_produit.php?artid=".$article->artid."&prec=modif\" target=\"_blank\">";
	$ret['artcode_mcav']    .= $article->artcode;
	$ret['artcode_mcav']    .= "</a>";
	$ret['artcode_eurocave'].= strlen($article->artcode_eurocave) <= 10 ? $article->artcode_eurocave : substr($article->artcode_eurocave,0,10).'...';
	$ret['lien_fiche']       = "clientnavig.php?userid=".$utilisateur['userid']."&goto=art,".$article->artid.",-.html";
	$ret['marque']           = $article->marque;
	$ret['marque_visuel']    = file_exists($_SERVER['DOCUMENT_ROOT']."/configurations/www.mon-espace-cuisson.com/images/marques/br/".$article->marqueid.".jpg") ? "<img src=\"/configurations/www.mon-espace-cuisson.com/images/marques/br/".$article->marqueid.".jpg\" height=\"18\">" : "";
	$ret['prixtotal']        = number_format($prixtotal,2,',',' ')."&euro;";
	$ret['prix']             = $prixtotal;
	$ret['active']           = $article->active;
	
	if(intval($article_array['commandable']) == 0){
		$ret['rows']['PPI TTC']  = "Sur demande";
		$ret['rows']['Remise site'] = "NC";
		$ret['rows']['PV TTC'] = "Sur demande";
		$ret['rows']['Marge HT'] = "NC";

	}else{
		$ret['rows']['PPI TTC']  = number_format($article_array['prixconseilleht'] * $tvanormal,2,',',' ')."&euro;";
		if($article_array['prixconseilleht']>0){
			$ret['rows']['Remise site']  = "-".number_format( 100 - ($article->prix * 100 / ($article_array['prixconseilleht'] * $tvanormal)) ,1,',',' ')."%";
		}
		$ret['rows']['PV TTC']   = $ret['prixtotal'];
		$ret['prixtotaltrspt']   = number_format($prixtotaltrspt,2,',',' ')."&euro;";
		$ret['marge']            = number_format($marge,2,',',' ')."&euro;<br>".couleur_marge($marge_pct);
		$ret['rows']['Marge HT']   = $ret['marge'];
	}
	
	
	// si lot 
	$article_lots=$DB_site2->query("SELECT * FROM article_lot WHERE lotid=".$artid);
	$nb_article_lot=$DB_site2->num_rows($article_lots);
    if($nb_article_lot>0){
		$artids="";
		$virgule="";
		while($article_lot = $DB_site2->fetch_array($article_lots)){
			$artids.=$virgule.$article_lot['artid'];
			$virgule=",";
		}
		$DB_site2->data_seek(0,$article_lots);
		$ret['rows']['---Date réappro---']	= "";
		$i=0;
		while($article_lot = $DB_site2->fetch_array($article_lots)){
			$article2 = new article($DB_site2);
			$article2->load($article_lot['artid']);
			$article2->setpays($utilisateur['paysid_livraison']);
			if($article2->date_appro > 0){
				$ret['date_appro'] 	= date("d/m/Y",$article2->date_appro);
				$libelle=$article_lot['qte']." ".$article2->artcode." (".$article2->artcode_eurocave.")";
				$ret['rows'][$libelle]	= $ret['date_appro'];
				$i++;
			}
		}
		if($i==0){
			unset($ret['rows']['---Date réappro---']);
		}
		$ret['rows']['---Stock---']   		= $ret['stock'];
		$DB_site2->data_seek(0,$article_lots);
		while($article_lot = $DB_site2->fetch_array($article_lots)){
			$article2 = new article($DB_site2);
			$article2->load($article_lot['artid']);
			$article2->setpays($utilisateur['paysid_livraison']);
			$items1 = $stock->search($article_lot['artid'], 217); // stock acilyon
			$items2 = $stock->search($article_lot['artid'], 630900); // stock ginko
			$items3 = $stock->search($article->artid, 632925); // stock ajd
			$ret['stock']            	= count($items1+$items2+$items3);
			///pour avoir la qte réel instant', il faut soustraire les articles non réservés du calcul ret['stock'] :
			$cmdqte="SELECT factureid,qte FROM lignefacture INNER JOIN facture ON(facturid=factureid) WHERE (select count(*) from envoi_fournisseur_ligne where ligne_facture_id=lignefactureid AND type IN ('fourn','stock'))=0 and valide in ('1','7') and supprimee='0' and artid=".$article_lot['artid'];
			$reqcmd=$DB_site2->query($cmdqte);
			while($facture_cmd=$DB_site2->fetch_array($reqcmd)){
				$reserve=$DB_site2->query_first("SELECT COUNT(*) FROM stock_item WHERE artid=".$article_lot['artid']." AND dest='facture ".$facture_cmd['factureid']."' AND state=2");
				if($reserve[0]<$facture_cmd['qte']){
					$ret['stock'] -= 1;
				}
			}

			if($ret['stock']>0){
				$libelle=$article_lot['qte']." ".$article2->artcode." (".$article2->artcode_eurocave.")";
				$ret['rows'][' '.$libelle]	= $ret['stock'];
			}
		}
	}else{
		if($article->date_appro > 0){
			$ret['date_appro'] 	= date("d/m/Y",$article->date_appro);
			$ret['rows']['Date réappro']	= $ret['date_appro'];
		}
		$ret['stock']            = count($items1+$items2+$items3);
		if($ret['stock']>0){
			$ret['rows']['Stock']   		= $ret['stock'];
		}
	}
	$ret['livraison'] 		 = date("d/m/y",time()+$delailivraison['delailivraison']*86400);
	
	$ret['rows']['Date livr.']   		= $ret['livraison'];
	
	$ret['livraison_timestamp'] = time()+$delailivraison['delailivraison']*86400;
	
// 	if($nb_article_lot==0){
		$ret['dispo']     		 = "<input type=\"checkbox\" class=\"picto\" onclick=\"todispoart(this.checked,'".$article->artcode_eurocave."','1','".$article->paht."','0','".$ret['livraison_timestamp']."','".$article->date_appro."','".htmlentities($article->libelle).$propositions_dispo."');\">";
// 	}
	// setAction('demande_dispo', '".$article->artcode_eurocave."');
	if(isset($ret['propid']) && $ret['propid']!=""){
		$ret['suppr']    	 = "<a onclick=\"if(confirm('Etes-vous sur de vouloir supprimer cet article des propositions ?')){ location.href = 'utilisateur.php?action=editer&userid=".$_GET['userid']."&rmprop=".$ret['propid']."#propositions_articles'; } return false;\" href=\"javascript:void(0);\"><img src=\"images/icon/delete.png\" class=\"del picto\" title=\"Supprimer\"></a>";
    } else {
		$ret['suppr']    	 = "";
	}
	if($nb_article_lot>0){
		// c'est un lot
		$ret['rows']['Concurrents'] = "<a href=\"export/export_marge_article.php?artids=".$artids."&active[]=0&active[]=1&active[]=2&active[]=3\" target=\"_blank\">Voir</a>";
		$ret['spy']="";
	} else {
		$pc="";
		$spy="<a href=\"javascript:void(0)\" onclick=\"affiche_cache('spy_".$rubrique."_".$artid."');\">SPY</a>
				<table id=\"spy_".$rubrique."_".$artid."\" style=\"display:none; position:absolute; z-index:1002; background-color: white; width:300px; border: solid thin black; border-collapse: collapse;\">";
		$spy.="<tr class=\"td_alt1\">
				<td width=\"25%\" align=\"center\"><b>Site</b></td>
				<td width=\"25%\" align=\"center\"><b>PV TTC</b></td>
				<td align=\"center\" width=\"25%\"><b>Diff %</b></td>
				<td align=\"center\" width=\"25%\" nowrap><b>Marge alignée</b></td>
			</tr>
			<tr  class=\"td_alt2\">
				<td>MCAV</td>
				<td align=\"right\">".number_format($article->prix,2,',',' ')." &euro;</td>
				<td align=\"center\">-</td>
				<td align=\"center\">-</td>
			</tr>";
		$ret['spy_site']="-";
		$ret['spy_prix']="-";
		$prix_spy_ht_trspt=0;
		$i=0;
		$requete=$DB_site2->query("	SELECT h.* FROM history AS h 
									WHERE h.artcode='".$article->artcode."' 
									AND h.timestamp>".(time()-86400*2)." 
									GROUP BY site
									ORDER BY price ASC");
		while($data=$DB_site2->fetch_array($requete)){
			if($i==0){
				$ret['spy_site']=$data['site'];
				$ret['spy_prix']=floatval($data['price']);
			}
			if(floatval($article->prix)!=0){
				$pc=round(-(100-100*$data['price']/$article->prix),2)."%";
			}
			$prix_spy_ht_trspt = ($data['price'] / $tvanormal) + $frais_port->prixht;
			$marge_aligne_ht = $prix_spy_ht_trspt - $article_paht_total;
			$marge_aligne_pct = $marge_aligne_ht * 100 / $prix_spy_ht_trspt;
			$rowalt=($i%2)+1;
			$spy.="<tr class=\"td_alt".$rowalt."\">
					<td>".$data['site']."</td>
					<td align=\"right\">".number_format($data['price'],2,'.',' ')." &euro;</td>
					<td align=\"right\" style=\"color:". ($data['price'] < $article->prix ? "red" : "green") .";\">$pc</td>
					<td align=\"right\">".couleur_marge($marge_aligne_pct)."</td>
				</tr>";
			$i++;
		}
		$ret['spy_nb']=$i;
		if($ret['spy_nb']>0){
			if($ret['spy_prix']<$prixtotal){
				$ret['spy_marge']=0; // marge à calculer ....
				$prix_spy_ht_trspt = ($ret['spy_prix'] / $tvanormal) + $frais_port->prixht;
				$marge_aligne_ht = $prix_spy_ht_trspt - $article_paht_total;
				$marge_aligne_pct = $marge_aligne_ht * 100 / $prix_spy_ht_trspt;
				$ret['spy_marge']=number_format($marge_aligne_ht, 0, ',', ' ')." &euro;<br>".couleur_marge($marge_aligne_pct);
				$ret['spy_prix']=number_format($ret['spy_prix'],2,'.',' ');
				$ret['rows']['Concurrents']   		= $ret['spy_nb'] . " <a href=\"export/export_marge_article.php?artids=".$artid."&active[]=0&active[]=1&active[]=2&active[]=3\" target=\"_blank\">Voir</a>";
				$ret['rows']['Site + bas']   		= $ret['spy_site'];
				$ret['rows']['Prix + bas']   		= $ret['spy_prix'];
				$ret['rows']['Marge alignée']   	= $ret['spy_marge'];
			} else {
				$ret['spy_marge']="-";
				$ret['spy_prix']="-";
				$ret['spy_site']="-";
				$ret['rows']['Concurrents']   		= $ret['spy_nb'] . " <a href=\"export/export_marge_article.php?artids=".$artid."&active[]=0&active[]=1&active[]=2&active[]=3\" target=\"_blank\">Voir</a>";
			}
		} else {
			$ret['spy_marge']="-";
			$ret['rows']['Concurrents']   			= $ret['spy_nb'] . " <a href=\"export/export_marge_article.php?artids=".$artid."&active[]=0&active[]=1&active[]=2&active[]=3\" target=\"_blank\">Voir</a>";
		}
		$spy.="</table>";
		if($DB_site2->num_rows($requete)==0){
			$spy="-";
		}
		$ret['spy']=$spy;
	}
	
	$ret['lockit']="";
	if($article->arguments()){
		$ret['lockit']="<img src=\"images/lockit.jpg\" class=\"picto\" width=\"30\" title=\"".$article->argument."\">";
	}
	$ret['files']="";
	$nbfile=$DB_site2->query_first("SELECT COUNT(*) FROM article_files WHERE artid=".intval($article->artid));
	if($nbfile[0]>0){
		$ret['files'].="<img src=\"images/icon/pdf.png\" width=\"30\"  class=\"picto\" onclick=\"fb.show('article_files.php?artid=".$article->artid."&parent=0')\">";
	}
	return $ret;
}

?>
