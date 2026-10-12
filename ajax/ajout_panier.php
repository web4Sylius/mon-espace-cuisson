<?php
header('Content-Type: text/html; charset=utf-8');

require_once($_SERVER['DOCUMENT_ROOT']."/global.php");


$artid = intval($_GET['artid']);
if($artid==0) exit;
$table_article = table_typearticle($DB_site,$artid);
$packs=new Pack($DB_site);
if(!$packs->isPack($artid)){ //article simple
	if($sitepro)
		$kdopack = $packs->kdos_pack($artid,$sitepro);
	else
		$kdopack = $packs->kdos_pack($artid);

    $tabArticle=$DB_site->query_first("SELECT a.*,spec.*,a.libelle".$template->langue." AS libelle, a_s.active AS active FROM article a INNER JOIN article_site a_s USING(artid) INNER JOIN $table_article AS spec USING(artid) WHERE artid=".$artid);
    if($tabArticle=="") exit;
    $article_prices=article_prices($tabArticle[artid],$template->tva_ref(),$template->site_code(),$sitepro);
    $marque=$DB_site->query_first("SELECT m.* FROM marque m INNER JOIN article_marque USING(marqueid) WHERE artid=".intval($tabArticle['artid']));
    $options=$DB_site->query_first("SELECT COUNT(*) FROM article_caractval WHERE artid=".intval($tabArticle['artid']));

    //Visuel article
    $template->parts["imgart"] = "<img src=\"".article_image($artid,280,280,$kdopack)."\" alt=\"".$tabArticle[$libelle]."\" class=\"visuel\">";


    //Visuel marque
    if ($tabArticle['image'] != ""){
	$template->parts["logomarque"] = "<img src=\"/marques/".$marque['marqueid']."_100_28.jpg\" alt=\"".$marque['type'.$template->langue]."\">" ;
    }

    //***********************************//
    // DEBUT GESTION DELAIS DE LIVRAISON //
    //***********************************//
    $d = delailivraison($tabArticle);
    $jourlivraison = mktime(0,0,0)+(86400*$d['delailivraison']);
    $template->parts["delaitxt"]      = $d['delailivraison']." ".$template->translate("/article/champs/jours_transport_inclus");
    $template->parts["datelivraison"] = date("d/m/Y",$jourlivraison);

    //En stock
    if($d['stock'] != false) $classid = "2";
    $template->parts["stock_class"]   = "stockvert".$classid;
    $template->parts["stock_libelle"] = $template->translate("/article/vignette/en_stock","F");

    //Avec délai
    if($d['delailivraison'] > 15){
	$template->parts["stock_class"]   = "stockorange";
	$template->parts["stock_libelle"] = str_replace('[nb_jours]',$d['delailivraison'],$template->translate("/article/stock/livraison_sous","F"));
    } 

    //En réappro
    if($d['date_appro'] != false){
	$template->parts["stock_libelle"]   = $template->translate("/article/stock/reappro_le")." ".date("d/m/Y",$d['date_appro']);
	//$template->parts["datelivraison"] = date("d/m/Y",$jourlivraison);
    }
    
    //Variables
    $template->parts["marque_libelle"] = $marque['libelle'];
    $template->parts["article_libelle"] = $tabArticle['libelle'];
    $template->parts["artcode"] = $tabArticle["artcode"];
    $template->parts["gamme"] = "";

    $finition=$DB_site->query_first("SELECT finitionid,nom".$template->langue." FROM articlerecherche_finition INNER JOIN recherche_finition USING(finitionid) WHERE artid=".$tabArticle['artid']);
    if($finition[1]!="") $template->parts['caractprincipales'] .= "<li><b>".$template->translate("/article/champs/niveau_gamme","F")." : </b>$finition[1]</li>";
    if($tabArticle[couleur_fab]!="") $template->parts['caractprincipales'] .= "<li><b>".$template->translate("/article/champs/coloris_modele","F")." : </b>".$template->translate($tabArticle[couleur_fab])."</li>";
    $template->parts['caractprincipales'] .= "<li><b>".$template->translate("/article/champs/dimensions","F")." : </b>".sprintf($template->translate("/article/champs/dimensions_format"),$tabArticle[hauteur],$tabArticle[largeur],$tabArticle[profondeur])."</li>";
    if($tabArticle['class_energetique']!="") $template->parts['caractprincipales'] .= "<li><b>".$template->translate("/article/champs/classe_energetique","F")." : </b>".$template->translate($tabArticle['class_energetique'])."</li>";



    $template->parts["strikeout"]="<div class=\"estrikeout\"></div>";
    $template->parts["pc"]="";

    $template->parts["pcprice"]=round($article_prices['remise'])."%";
    $template->parts["pricediff"]=$template->price_format($article_prices['prixconseille']-$article_prices['prix']);
    $template->parts["soprice"]=$template->price_format($article_prices['prixconseille']);
    $template->parts["pricetax"]=$template->translate($sitepro?"/site/ht":"/site/ttc","U");
    $template->parts["price"]=$template->price_format($article_prices['prix']);
    if(isset($_GET['prix'])){
        $price = $template->price_format($_GET['prix']);
        $template->parts["price"] = $template->price_format($_GET['prix']);
    }
    else{
        $template->parts["price"]=$template->price_format($article_prices['prix']);
    }
    $template->parts["lien_fiche"]=$template->mkurl("article",$tabArticle['artid'],$tabArticle);

    if($tabArticle['energy_label'] != ""){
	$label_energetique = str_replace('+','plus',strtolower($tabArticle['energy_label']));
	if(file_exists("../images/templates/energie_".$label_energetique.".jpg")){
	    $iconenergie = "<img class=\"picto\" alt=\"Classe energétique ".$label_energetique."\" src=\"/images/templates/energie_".$label_energetique.".jpg\">";
	}
    }

    if (isset($user_info['paysid'])) {
      $idPays=$user_info['paysid'];
    } else {
      $idPays=$template->pays_ref();
    }
    $tabGaranties=DAOgarantie::loadMaxGarantieArtByPays($DB_site,$template->site_code(),'FR',$idPays,$tabArticle['artid']);
    $garantie=$tabGaranties[0];
    if ($garantie->cumul==1) {
      //on cherche dans le tableau qui contient toutes les garanties offertes celle du fournisseur des années de garanties avant l'extension et du même type
      $pointer=DAOgarantie::searchGarantieToAddExtension($DB_site,$garantie->type_garantie,$tabGaranties,$garantie->garantieid);
      //on prend la durée de cette garantie pour l'ajouter à l'extension
      $dureeBase=$tabGaranties[$pointer]->duree;
      $duree=$garantie->duree+$dureeBase;
    } else {
      $duree=$garantie->duree;
    }
    $dureeGarantie=$duree;
    if(!empty($garantie)){
	if(file_exists("../images/templates/".$template->langue."/garantie_".$dureeGarantie.".jpg")){
	    $icongarantie = "<img class=\"picto\" src=\"/images/templates/".$template->langue."/garantie_".$dureeGarantie.".jpg\">";
	} 
    }

    if(file_exists("../images/templates/origine-gd-".$tabArticle['paysfabrication'].".jpg")){
	$template->parts["icons"] .= "<img class=\"picto\" src=\"/images/templates/origine-gd-".$tabArticle['paysfabrication'].".jpg\" alt=\"".$template->translate("/article/origines/".$tabArticle['paysfabrication'],"F")."\" align=\"absmiddle\">";
    }


    $promo=$DB_site->query_first("SELECT COUNT(*) FROM promo WHERE artid=".$tabArticle[artid]);
    $secondchoix=$DB_site->query_first("SELECT COUNT(*) FROM secondchoix WHERE artid=".$tabArticle[artid]);
    if (round($article_prices['prix']) != round($article_prices['prixconseille'])){
	if($promo[0]>0 || $secondchoix[0]>0){
	    $template->parts["pc"]="<p class=\"reduc\">-".round($article_prices['remise'])."%</p>";
	    $template->parts["strikeout"]="<p class=\"prix_barre\">".$template->price_format($article_prices['prixconseille'])."</p>";
	}
	$template->evaltemplate("ArticlePrixBarre","prixbarre");
    }


    if($article_prices['vf']) {

	$template->parts["pc"]="<div class=\"pc\">".round(-$article_prices['remise'])."%</div>";
	$template->parts["strikeout"]="<div class=\"strikeout\"><strike>".$template->price_format($article_prices['prixconseille'])."</strike></div>";
	$template->parts["venteflash"]="<div class=\"title_vf\">".$template->translate("/article/champs/fin_vente_flash")." <span id=\"vfdecompte\" class=\"compt_vf\"></span></div>";
    }

    if($article['ecopart']>0){
      $template->parts["ecopart"]=($sitepro?$article['ecopartht']:$article["ecopart"]);
      $template->evaltemplate("ArticleEcopart","ecoparttxt");
    }

    $template->parts["artid"] = $artid;
    $template->evaltemplate("ArticleBlocProduit","bloc_left");

}else{ //pack
    
    $sep="";
    $delaipack=0;
    $artinpack=$packs->getProduits($artid);
    $infopack=$packs->getDetails($artid);
    
    $template->parts["artcode"]=$infopack['artcode'];
    
    foreach($artinpack as $art_pack){
	$typeoffre=explode('-', $art_pack['offre'])[0];
	if ($typeoffre=='pdtoffert' && $art_pack['prixpack']==0) {
            $template->parts["pdtoffertpack"]="<img src=\"../images/templates/".$template->langue."/offert.png\"/>";

            $prices=article_prices($art_pack['artid'],$template->tva_ref(),$template->site_code(),$sitepro);
            $template->parts["prixpdtoffert"]=$template->translate("/article/autres_infos/soit",'F')." ".$template->price_format($prices['prix'])." ".$template->translate("/article/autres_infos/offert");
            $template->parts["kdopack"] = "";
                
            vignette($art_pack['artid'],'packoffert');
            //$template->parts["kdopack"]="<div class=\"packsepkdo\">=</div>".artpack($art_pack['artid'],"ArtPack",$pack['artid'],$art_pack['qte'],$dispo,$dispoid);
            $template->parts["prixpdtoffert"]="";
            if ($dispoid>$olddispoid) {
                $olddispoid=$dispoid;
                $template->parts["dispopack"]=$dispo;
            }

            $template->parts["pdtoffertpack"]="";
        } else {
            //$template->parts["artinpack"].=$sep.artpack($art_pack['artid'],"ArtPack",$pack['artid'],$art_pack['qte'],$dispo,$dispoid);
            $template->parts["artinpack"] .= $sep;
            vignette($art_pack['artid'],'pack');
            $sep = "<div class=\"sep_conteneur\"><span>+</span></div>";
            
            if ($dispoid>$olddispoid) {
                $olddispoid=$dispoid;
                $template->parts["dispopack"]=$dispo;
            }
        }
        
        $d=delailivraison($art_pack,$art_pack['qte']);
        if($d['delailivraison']>$delaipack) $delaipack=$d['delailivraison'];
    }
    
    $jourlivraison = mktime(0,0,0)+(86400*$delaipack);
    $template->parts["delaitxt"]      = $delaipack." ".$template->translate("/article/champs/jours_transport_inclus");
    $template->parts["datelivraison"] = date("d/m/Y",$jourlivraison);

    $template->parts["stock_class"]   = "stockvert";
    $template->parts["stock_libelle"] = $template->translate("/article/vignette/en_stock","F");

    //Avec délai
    if($delaipack > 15){
	$template->parts["stock_class"]   = "stockorange";
	$template->parts["stock_libelle"] = str_replace('[nb_jours]',$delaipack,$template->translate("/article/stock/livraison_sous","F"));
    } 

    
    $pack_prices=$packs->prices($artid,$template->tva_ref(),$sitepro);
    
    $template->parts["nomPack"]=$pack['artcode'];
    $taxtxt=($sitepro?"/site/ht":"/site/ttc");
    $strikeout="<div class=\"strikeout\"><strike>".$template->price_format($pack_prices['prixconseille'])." <small>".$template->translate($taxtxt,"U")."</small></strike></div>";

    $template->parts['pc']="<p class=\"reduc\">-".round($pack_prices['remise'])."%</p>";
    $price=$template->price_format($pack_prices['prix'],2,',','',' ')." <sup>".$template->translate($taxtxt,"U")."</sup>";
    $eurosoffert=$template->price_format($pack_prices['remisepack']);
    $template->parts["strikeout"]="<p class=\"prix_barre\">".$template->price_format($pack_prices['prixconseille'])."</p>";
    if(isset($_GET['prix'])){
        $price = $_GET['prix'];
    }
    $template->parts["price"]=$price;
    
    if(!empty($template->parts["cadeaupack"])){
	$template->parts["artinpack"] .= "<div class=\"sep_conteneur\"><span>=</span></div>";
	$template->parts["artinpack"] .= $template->parts["cadeaupack"];
    } else {

	if ($typeoffre=='euro') {
	    $reducTxt="<span class=\"reducTxt\">-".$template->price_format($pack_prices['remisepack'],0)."</span>";
	}
	if ($typeoffre=='pourcent') {
	    $reducTxt="<span class=\"reducTxt\">-".explode('-', $art_pack['offre'])[1]."%</span>";
	}
	
	$template->parts["packprices"] = ""
	. "<div class=\"sep_conteneur\"><span>=</span></div>"
	. "<div class=\"artpack\">"
	    . $reducTxt
	    . "<div class=\"prices\">"
		. $strikeout
		. "<div class=\"price\">".$price."</div>"
	    . "</div>"
	     . "<div class=\"clear\"></div>"
	. "</div>";
    }
    
    $template->evaltemplate("ArticleBlocPack","bloc_left");
}


/*
 * GENERATION DES TEMPLATES
 */

//Si le produit n'est pas archivé, on affiche les templates
if($tabArticle['active'] != 3){

     //Template "Prix barré"
     if($affiche_prix_barre){
          $template->evaltemplate("ArticlePrixBarre","prixbarre");
     }
     
     //Template "Prix"
     $template->evaltemplate("ArticlePrix","prices");
     
     
     //Template "EcoPart" (si montant > à 0)
     if($tabArticle['ecopart'] > 0){
          $template->evaltemplate("ArticleEcopart","ecoparttxt");
     }
     
     //Livraison offerte
      $fraisport=$DB_site->query_first("SELECT gratuit FROM fraisport WHERE paysid=57 LIMIT 1");
      $totallivr=$fraisport[0];
      if($tabArticle['prix'] >= $totallivr){
	  $template->evaltemplate("ArticleLivraisonOfferte","article_livraison_offerte");
	  //$iconlivr = "<div class=\"iconlivraison\"><img src=\"images/templates/".$template->langue."/livraison.png\"></div>";
      }
      $template->evaltemplate("ArticleLivraison","article_livraison");
     
      if($icongarantie) $template->parts["icons"] .= $icongarantie;
      if($iconenergie) $template->parts["icons"] .= $iconenergie;
}

$template->parts["popup_panier"]=preg_replace("/[\t|\n|\r]/","",addslashes($template->parts["popup_panier"]));

/*
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link href="/css/css-refonte.css" rel="stylesheet" type="text/css">
    <link href="/css/css-refonte-cc.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css" type="text/css">
</head>
<body>
*/


?>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link href="/css/css.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php 

echo $template->evaltemplate("AjoutPanierPopup","html");
echo stripslashes($template->parts["html"]);

?>
</body>