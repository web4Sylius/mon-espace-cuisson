<?php
require ("global.php");

//mot de passe : 3n!9<8wVJ!
if(!isset($_GET['log']) || !isset($_GET['pass'])){
	header('location:/');
	exit;
}
if(isset($_GET['log']) && isset($_GET['pass'])){
	if($_GET['log'] != 'expizflx' && $_GET['pass'] != '3n!9<8wVJ!'){
		header('location:/');
		exit;
	}
}

$head = "id-produit|code-ean|denomination-concise|description-complète|url-article|urlphoto1|url-photo2|url-photo3|url-photo4|url-photo5|marque|classification|id-reference|disponibilite|statut-de-disponibilite|prix-ttc|ecotaxe|retractation|quantite|delai-de-reappro|delai-de-livraison|unite-delai-de-livraison|frais-de-port|prix-public-generalement-constate|mots-clefs|prix-ttc-avant-promotion|pourcentage-de-la-remise|type-de-promotion|garantie|poids|coloris|logistic-class|capacite-bouteille|hauteur|largeur|profondeur|dimensions\n";


//Récupération des articles, puis boucle sur chacun d'eux
$sql = "SELECT a.*, a.libelleFR libart, c.libelleFR libcat, mpc.enabled";
$sql .= " FROM market_place_champs mpc";
$sql .= " INNER JOIN article a ON (a.artcode = mpc.artcode)";
$sql .= " INNER JOIN categorie c ON (c.catid = a.catid)";
$sql .= " WHERE 1";
$sql .= " AND a.active = '1'";
$sql .= " AND mpc.market_place IN ('conforama','amazon','delamaison','rdc','cdiscount','amazon','fnac','redoute')";
$sql .= " AND mpc.enabled = 1";
$sql .= " GROUP BY a.artid";
$sql .= " ORDER BY a.catid, a.artcode";

$articles = $DB_site->query($sql);
$nbarticles = $DB_site->num_rows($articles) ;
$tabArt = array();
$flux ="";
while($article = $DB_site->fetch_array($articles)){

	$content = array();
	$art = new produit($DB_site,"FR","base",$template->tva_ref(),$article['artid']);

	//on ecrit les infos du produit
	$content[0] = $art->id;																//id-produit
	$content[1] = $art->code_barre;														//ean
	$content[2] = $art->libelle;														//denomination-concise
	$content[3] = html_entity_decode(strip_tags($art->description),ENT_QUOTES);			//description complete
	$content[3] = str_replace(array("\n","\r"), " ", $content[3]);
	$content[4] = "https://www.mon-espace-cuisson.com/".url_rewrite($art->libelle."-".$art->marque_libelle)."-art".$art->id.".htm"; //url-article
	url_photo($DB_site,$article['artid'],$article['image']);							//liens photos maximum 5
	$content[10] =$art->marque_libelle; 												//marque
	$content[11] = fil_ariane($DB_site,$art, $template);								//fil d'ariane	/ classification
	$content[12] = $art->ref_aci;														//id-reference : artcode		
	$content[13] = dispo($art->active); 												//disponibilite
	$content[14] = statut_dispo($art->id,$article); 									//statut de disponibilité
	$content[15] = prices($article,$template,'prixttc');								//prix ttc article	
	$content[16] = $article['ecopart']; 												//ecotaxe
	$content[17] = "Y"; 																//retractation Y/N
	$content[18] = qte($article);														//quantite
	$content[19] = delai_reappro($article);												//delai de reappro ( en nb de jours )
	$content[20] = livr($article);														//delai de livraison (en nb de jours)
	$content[21] = "D";																	//unite de delai de livraison (H:heure,D:jour,W:semaine,M:mois)
	$content[22] = fraisdeport($article);												//frais de port ttc. type : int. donc arrondi car 8.9 est un float
	$content[23] = prices($articles,$template,"ppc");									//prix public constate
 	$content[24] = motscles($article['sowine']);										//mots cles
 	$content[25] = prices($articles,$template,"prixbarre");								//prix ttc avant remise
 	$content[26] = prices($article,$template,'remise');									//pourcentage de remise

 	if(prices($article,$template,'remise') > 0) 										//type de promo. 0 pas en promo, 1 en promo
 		$content[27] = 1;
 	else
 		$content[27] = 0;

 	$content[28] = garantie($DB_site,$template,57,$article['artid']);					//garantie article
 	$content[29] = ($article['poid']/1000);										        //poids en kg
 	$content[30] = $template->translate($article['coloris']);						    //coloris
 	$content[31] = logistic_class($article['poid']);								    //logistic-class
 	$content[32] = $art->capacite;														//capcaite bouteille
 	$content[33] = $art->hauteur;														//hauteur
 	$content[34] = $art->largeur;														//largeur
 	$content[35] = $art->profondeur;													//profondeur
 	$content[36] = $art->dimensions."\n";												//dimensions	

 	$flux .= implode('|', $content);

 	unset($content);
}

$flux = $head.$flux;

// *******  Mise a jour du fichier texte  ********** // 

file_put_contents('iziflux/flux.txt', '');
file_put_contents('iziflux/flux.txt', $flux);

// ****** Fin du traitement du fichier *******//

// On force le telechargement du fichier
header("Content-Type: application/octet-stream");
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: attachment; filename=\"flux.txt\""); 

readfile("iziflux/flux.txt");

// on enregistre les fichiers pour les logs

copy("iziflux/flux.txt", "iziflux/log/flux_".time().".txt");


//****************   FONCTIONS   ***************************//


function dispo($art_active){
	if($art_active == 1)
		return "Y";
	else
		return "N";
}

function statut_dispo($artid,$article){
	$tabLivraison = delailivraison($artid);

	if(!$tabLivraison['date_appro'] && $article['date_appro'] > 0) $tabLivraison['date_appro'] = $article['date_appro'];
	
	if(!$tabLivraison['date_appro'])
		return "S";
	else
		return "R";
}

function delai_reappro($article){
	$tabLivraison = delailivraison($article['artid']);
	if(!$tabLivraison['date_appro'] && $article['date_appro'] > 0) $tabLivraison['date_appro'] = $article['date_appro'];
	
	if(!$tabLivraison['date_appro']){
		return 0; //mettre a 0 si produit dispo
	}else{
		return round(($article['date_appro']-mktime(0,0,0))/86400);
	}
}

function livr($article){
	$tabLivraison = delailivraison($article['artid']);
	return $tabLivraison['delailivraison'];
}

function qte($article){
	$tabLivraison = delailivraison($article['artid']);
	if($article['active'] != 1 || $article['commandable'] != 1 || $article['enabled'] != 1 || (!empty($tabLivraison['date_appro']) && mktime(0,0,0,date('m'),date('d')+7,date('Y')) < $tabLivraison['date_appro'])){

        return $quantity = 0;
    } else {
         return $quantity = 100;
    }
}

function fraisdeport($article){
	global $DB_site;
	$port = $DB_site->query_first("SELECT prix,gratuit FROM fraisport WHERE poiddebut <= ".$article[poid]." AND poidfin >= ".$article[poid]." and paysid = '57'");
	if($port['gratuit']>0 && $port['gratuit'] < $article['prix']) $port['prix']=0;
	return round($port['prix']);
}

function motscles($sowine){

	if($sowine == 0){
		return "cave vin,cave a vin,cave à vin,caves a vin,armoire vin,armoire a vin,armoire à vin,climadiff,artevino";
	}else{
		return "bar vin,bar vins,home bar,home bar vin,bars a vins,bars vins,bar à vins,servir vin,Sowine Brandt,Sowine,cave 2 bouteilles";
	}
}

function prices($article, $template,$version){
	$prices = article_prices($article['artid'],$template->tva_ref(),$template->site_code());
	switch ($version) {
		case 'prixttc':
			return $prices['prix'];
			break;
		
		case 'ppc':
		case 'prixbarre':
			if($prices['prixconseille'] == $prices['prix'])
				return "";
			else
				return round($prices['prixconseille']);
			break;

		case 'remise' :
			return round($prices['remise'],0,PHP_ROUND_HALF_UP);
			break;	
	}
}

function garantie($DB,$template,$paysid ,$artid){

	$tabGaranties=DAOgarantie::loadMaxGarantieArtByPays($DB,$template->site_code(),'FR',$paysid,$artid,1);
	$garantie=$tabGaranties[0];
	$dureeGarantie=0;
	if (!empty($garantie)) {
	  //si la garantie est un extension
	  if ($garantie->cumul==1) {
	    //on cherche dans le tableau qui contient toutes les garanties offertes celle du fournisseur des années de garanties avant l'extension et du même type
	    $pointer=DAOgarantie::searchGarantieToAddExtension($DB,$garantie->type_garantie,$tabGaranties,$garantie->garantieid);
	    //on prend la durée de cette garantie pour l'ajouter à l'extension
	    $dureeBase=$tabGaranties[$pointer]->duree;
	    $duree=$garantie->duree+$dureeBase;
	  } else {
	    $duree=$garantie->duree;
	  }
	  $dureeGarantie=$duree;
	  if ($duree!=99) {
	    $an=$template->translate("/article/garantie_type/an");
	    if ($duree>1) {
	      $an=$template->translate("/article/garantie_type/ans");
	    }
	    $libGarantie=$duree." ".$an;
	  } else {
	    $libGarantie=$template->translate("/article/garantie_type/a_vie",'F');
	  }
	  
	  $typeGarantie=$template->translate("/article/garantie_type/$garantie->type_garantie");
	  $garantie_incluse = $libGarantie . " " . $typeGarantie;
	  
	  return $garantie_incluse;

	}else{

		return 0; //si pas de garantie on renvoi 0
	}
}

function fil_ariane($DB, $art, $template){
	// ****************** Mise en place fil d'ariane ***************** //
	$fil_ariane = "";

	$categorie = new categorie($DB,$art->categorie[0]['categorie_id'],$template->langue);
	$menus=$categorie->getMenus();

	if($categorie->menu_appartenance>0) $menu=$menus[$categorie->menu_appartenance];

	if($art->active != 3 ){
		if($menu != 0) $fil_ariane .= $menu['libelle']." > ";
			$fil_ariane .= $categorie->libelle; 
	}else{
	    $fil_ariane .= $template->translate("/archives/fil")." > ".$art->marque_libelle;
	}
	//$fil_ariane .= " > ".$art->libelle." ".ucfirst(strtolower($art->marque_libelle));

	return $fil_ariane;
}

function url_photo($DB,$artid,$format){
	global $content;

	$url_img = array();
	//image de base
	$url_base = "https://www.mon-espace-cuisson.com/configurations/www.mon-espace-cuisson.com/images/produits/".$artid.".".$format;
	$url_img[] = $url_base;

	//4 photos supplémentaires max
	$sql = "SELECT * FROM articlephoto WHERE artid = ".$artid." ORDER BY articlephotoid LIMIT 4";

	$photos=$DB->query("SELECT * FROM articlephoto WHERE artid = ".$artid." ORDER BY articlephotoid LIMIT 5") ;
	
	while ($photo=$DB->fetch_array($photos)) {

		$url_img[] = "https://www.mon-espace-cuisson.com/configurations/www.mon-espace-cuisson.com/images/produits/".$artid."_".$photo[articlephotoid].".".$photo[image] ;
	}
	$y = 0;
	for($i=5;$i<=9;$i++){
		if(is_null($url_img[$y]))
			$content[$i] = "";
		else
			$content[$i] = $url_img[$y];
		$y++;
	}
}

function logistic_class($poids){
	if($poids<20000){
            $classe_logistique = "G";
    }
    if($poids>=20000 && $objArticle->poid<50000){
            $classe_logistique = "H";
    }
    if($poids>=50000 && $objArticle->poid<100000){
            $classe_logistique = "I";
    }
    if($poids>=100000){
            $classe_logistique = "J";
    }
    
    return $classe_logistique;
}