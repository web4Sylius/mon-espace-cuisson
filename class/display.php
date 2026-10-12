<?php


function catcdc($artid){
    global $DB_site;
    global $template;
    global $sitepro;
    global $host;
    
    $article=$DB_site->query_first("SELECT a.prix,a.prixht,a.artcode,a.libelle".$template->langue." AS libelle,a.active,a.commandable,a.gamme".$template->langue.",a.capacite_mini,a.capacite_bouteilles,a.capacite_maxi,a.dimensions,a.nbr_temperature,a.prixconseilleht,a.image,a.modele,a.coloris,a.tauxchoisi, a.niveau_bruit_abcd FROM article a WHERE a.artid=$artid");
    if($article['active']=="" || $article['active']!=1) return "";

    $template->parts["img"]="";
    $template->parts["msg"]="";
    $template->parts["price"]="";
    
    $template->parts["img"]="<img src=\"/images/".$artid."_70_126.jpg\" alt=\"".$article['libelle']." ".$mq[0]."\">";

    $mq=$DB_site->query_first("select libelle,m.marqueid,image,type".$template->langue." AS mtype from marque m, article_marque am where m.marqueid = am.marqueid and am.artid = '$artid'") ;
    $template->parts["logomarque"]="";
    $imgm="configurations/$host/images/marques/br/".$mq[marqueid].".".$mq[image];
    if(file_exists($imgm) && $mq[image]!=""){
	  $size=getimagesize($imgm);
	  $dh=$size[1]*80/$size[0];
	  $style="style=\"width:80px;\"";
	  if($dh>28) $style="style=\"height:28px;\"";
	  $template->parts["logomarque"]="<img src=\"".$_SERVER['HTTP_HOST']."/".$imgm."\" alt=\"$mq[mtype]\" $style>";
    }
    
    $tvafrance=$DB_site->query_first("SELECT 0,TVAtauxnormal,TVAtauxreduit FROM pays WHERE paysid=57");
    $txtva=(1+$tvafrance[$article['tauxchoisi']]/100);

    $strikeout="<div class=\"estrikeout\"></div>";
    $price="";
    $msg="";
    switch($article['commandable']){
	case 1:
	$taxtxt=($sitepro?"/site/ht":"/site/ttc");
	$article_prices=article_prices($artid,$template->tva_ref(),$template->site_code(),$sitepro);
	
	$promo=$DB_site->query_first("SELECT COUNT(*) FROM promo WHERE artid=".$artid);
	if(round($article_prices['prix'])!=round($article_prices['prixconseille']) && ($promo[0]>0 || $secondchoix[0]>0)){
	   $strikeout="<div class=\"strikeout\">".$template->price_format($article_prices['prixconseille'])." <small>".$template->translate($taxtxt,"U")."</small></div>";
	   $template->parts["pc"]="<div class=\"pc\">-".round($article_prices['remise'])."%</div>";
	}
	
	if($article_prices['vf']){
	    $template->parts["pc"] = "<div class=\"pc\">-".round ($article_prices['remise'])."%</div>" ;
	    $strikeout="<div class=\"strikeout\">".$template->price_format($article_prices['prixconseille'])." <small>".$template->translate($taxtxt,"U")."</small></div>";
	    $venteflashimg="<img src=\"/configurations/www.mon-espace-cuisson.com/images/templates/".$template->langue."/venteflash.png\" style=\"position:absolute;\">";
	    $template->parts["vfimg"]="<img src=\"/images/templates/".$template->langue."/venteflash.png\" class=\"vfimg\">";
	}
    if($article_prices['vp']){
        $template->parts["pc"] = "<div class=\"pc\">-".round ($article_prices['remise'])."%</div>" ;
        $strikeout="<div class=\"strikeout\">".$template->price_format($article_prices['prixconseille'])." <small>".$template->translate($taxtxt,"U")."</small></div>";
        $venteflashimg="<img src=\"/configurations/www.mon-espace-cuisson.com/images/templates/".$template->langue."/venteprivee.png\" style=\"position:absolute;\">";
        $template->parts["vfimg"]="<img src=\"/images/templates/".$template->langue."/venteflash.png\" class=\"vfimg\">";
    }
	
	$price=$template->price_format($article_prices['prix'])." <small>".$template->translate($taxtxt,"U")."</small>";
	
	$template->parts["price"]="<div class=\"prices\">
	  $strikeout
	  <div class=\"price\">$price</div>
	</div>";
	break;

	case 2:
	$template->parts["msg"]="<div class=\"msg\">".$template->translate("/article/vignette/non_dispo","F")."</div>";
	break;

	case 0:
	$template->parts["msg"]="<div class=\"msg\">".$template->translate("/article/vignette/vendu_magasin","F")."</div>";
	break;
    }

    
    $template->parts["typearticle"]=$article['libelle']."<br>";
    $template->parts["artcode"]=$article['artcode'];
    $template->parts["url"]="/art,$artid,".url_rewrite($article['libelle']).".htm";
    $template->evaltemplate("vignetteCDC","catcdc_".$artid);

    return $template->parts["catcdc_".$artid];
}

/**
 * Génère une vignette article
 *
 * @param  integer $artid Identifiant du produit
 */
function vignette($artid, $variante = '',$classSup = '', $config=null){

    global $DB_site;
    global $template;
    global $sitepro;
    global $user_info;
    global $comparateur;

    $artid = intval($artid);

    $packs = new Pack($DB_site);
	if($sitepro)
		$kdopack = $packs->kdos_pack($artid,$sitepro);
	else
		$kdopack = $packs->kdos_pack($artid);

    $aChamps = array(
    	'artid',
    	'typearticleid',
    	'libelle'.$template->langue.' AS libelle',
        'libelle'.$template->langue,
    	'artcode',
    	'(SELECT active FROM article_site WHERE artid = '.$artid.' AND site_code = \''.$template->site_code().'\' LIMIT 1) AS active',
    	'commandable',
    	'gamme'.$template->langue,
    	'modele',
    	'prix',
    	'prixht',
    	'prixconseilleht',
    	'image',
    	'coloris',
    	'paysfabrication',
    );
    //Sélection du détail de l'article
    $sql = "SELECT ".implode(',', $aChamps);
    $sql .= " FROM article a";
    $sql .= " WHERE a.artid = ".intval($artid);
    $article = $DB_site->query_first($sql);
    if($article[0] == "") return "";

    //Sélection de la photo d'ambiance
    $sql = "SELECT articlephotoid";
    $sql .= " FROM articlephoto";
    $sql .= " WHERE artid = ".$article['artid'];
    $sql .= " AND langue IN ('','".$template->langue."')";
    $sql .= " AND visible = 0";
    $sql .= " AND ambiance = 1";
    $sql .= " ORDER BY position";
    $sql .= " LIMIT 0,1";
    $aPhotoAmbiance = $DB_site->query_first($sql);

    //Sélection détail marque
    $sql = "SELECT libelle, m.marqueid, image, m.type".$template->langue." AS mtype";
    $sql .= " FROM marque m, article_marque am";
    $sql .= " WHERE m.marqueid = am.marqueid";
    $sql .= " AND am.artid = ".$article['artid'];
    $tabMarque = $DB_site->query_first($sql);
    $article['marque']=$tabMarque['libelle'];


    $lstCarac = "";

    switch ($article['typearticleid']) {
        case '2':
            // cuisinière
            $plaques = $DB_site->query("SELECT type_table FROM article_table_cuisson WHERE artid=".$artid);
            $nb_plaques = $DB_site->num_rows($plaques);
            if($nb_plaques>1){
                $lstCarac .= "<li>".$template->translate("/article/caract_principales/foyers")." : ".$template->translate("/article/caract_principales/foyers2")."</li>";
            }else{
                if($nb_plaques > 0){
                    $nb_foyers = $DB_site->query_first("SELECT COUNT(table_id) FROM article_foyers af INNER JOIN article_table_cuisson atc ON af.table_id=atc.id WHERE atc.artid = '$artid'");
                    $lstCarac .= "<li>".$nb_foyers[0]." ".$template->translate("/article/vignette/nb_foyers")."</li>";
                }
            }
            $nb_fours = $DB_site->query_first("SELECT COUNT(id) FROM article_four WHERE artid = '$artid'");
            $lstCarac .= "<li>".$nb_fours[0]." ".$template->translate("/article/vignette/nb_fours")."</li>";

            $dimensions = $DB_site->query_first("SELECT hauteur, largeur, profondeur,couleur_fab, class_energetique FROM article_cuisiniere WHERE artid = '$artid'");
            $etiq_energie = $dimensions[class_energetique];
            $lstCarac .= "<li>".$template->translate("/article/vignette/dimensions")." H".$dimensions['hauteur']."xL".$dimensions['largeur']."xP".$dimensions['profondeur']." mm</li>";

            if($dimensions['couleur_fab'] != "") $lstCarac .= "<li>".$template->translate("/article/vignette/coloris","F")." : ".$template->translate($dimensions['couleur_fab'])."</li>";

        break;

        case '1':
            // piano de cuisson
            $plaques = $DB_site->query("SELECT type_table FROM article_table_cuisson WHERE artid=".$artid);
            $nb_plaques = $DB_site->num_rows($plaques);
            if($nb_plaques>1){
                $lstCarac .= "<li>".$template->translate("/article/caract_principales/foyers")." : ".$template->translate("/article/caract_principales/foyers2")."</li>";
            }else{
                if($nb_plaques > 0){
                    $nb_foyers = $DB_site->query_first("SELECT COUNT(table_id) FROM article_foyers af INNER JOIN article_table_cuisson atc ON af.table_id=atc.id WHERE atc.artid = '$artid'");
                    $lstCarac .= "<li>".$nb_foyers[0]." ".$template->translate("/article/vignette/nb_foyers")."</li>";
                }
            }

            $nb_fours = $DB_site->query_first("SELECT COUNT(id) FROM article_four WHERE artid = '$artid'");
            $lstCarac .= "<li>".$nb_fours[0]." ".$template->translate("/article/vignette/nb_fours")."</li>";

            $dimensions = $DB_site->query_first("SELECT hauteur, largeur, profondeur, couleur_fab, class_energetique FROM article_piano WHERE artid = '$artid'");
            $lstCarac .= "<li>".$template->translate("/article/vignette/dimensions")." H".$dimensions['hauteur']."xL".$dimensions['largeur']."xP".$dimensions['profondeur']." mm</li>";

            if($dimensions['couleur_fab'] != "") $lstCarac .= "<li>".$template->translate("/article/vignette/coloris","F")." : ".$template->translate($dimensions['couleur_fab'])."</li>";
            $etiq_energie = $dimensions[class_energetique];

        break;

        case '3':
            //plaque de cuisson
            $type_table = $DB_site->query_first("SELECT type_table FROM article_table_cuisson WHERE artid='$artid'");
            $type_table = explode(',', $type_table[0]);
            
            $txt="";
            foreach($type_table as $type){
                $txt.=$template->translate($type).",";
            }

            $txt = substr($txt,0, -1);
            $lstCarac .= "<li>".$txt."</li>";
            $nb_foyers = $DB_site->query_first("SELECT COUNT(table_id), atc.type_table FROM article_foyers af INNER JOIN article_table_cuisson atc ON af.table_id=atc.id WHERE atc.artid = '$artid'");
            $lstCarac .= "<li>".$nb_foyers[0]." ".$template->translate("/article/vignette/nb_foyers")."</li>";

            $dimensions = $DB_site->query_first("SELECT hauteur, largeur, profondeur,couleur_fab, class_energetique FROM article_table_cuisson WHERE artid = '$artid'");
            $lstCarac .= "<li>".$template->translate("/article/vignette/dimensions")." H".$dimensions['hauteur']."xL".$dimensions['largeur']."xP".$dimensions['profondeur']." mm</li>";

            if($dimensions['couleur_fab'] != "") $lstCarac .= "<li>".$template->translate("/article/vignette/coloris","F")." : ".$template->translate($dimensions['couleur_fab'])."</li>";
            $etiq_energie = $dimensions[class_energetique];

        break;

        case '4':
        case '17':
            //four
            $type_four = $DB_site->query_first("SELECT type_four,volume_cavite,hauteur,largeur,profondeur,couleur_fab, class_energetique FROM article_four WHERE artid='$artid'");
            $lstCarac .= "<li>".$template->translate($type_four[0])."</li>";
            $lstCarac .= "<li>".$template->translate("/article/vignette/volume")." : ".$type_four[1]."L</li>";
            $lstCarac .= "<li>".$template->translate("/article/vignette/dimensions")." H".$type_four['hauteur']."xL".$type_four['largeur']."xP".$type_four['profondeur']." mm</li>";
            if($type_four['couleur_fab'] != "") $lstCarac .= "<li>".$template->translate("/article/vignette/coloris","F")." : ".$template->translate($type_four['couleur_fab'])."</li>";
            $etiq_energie = $type_four[class_energetique];
        break;

        case '5':
            $hotte = $DB_site->query_first("SELECT type_aspiration, hauteur, largeur, profondeur, debit_air_max,couleur_fab, class_energetique FROM article_hotte WHERE artid='$artid'");
            $lstCarac .= "<li>".$template->translate($hotte['type_aspiration'])."</li>";
            $lstCarac .= "<li>".$template->translate("/article/vignette/debit_air")." : ".$hotte['debit_air_max']." m³/h</li>";
            $lstCarac .= "<li>".$template->translate("/article/vignette/dimensions")." H".$hotte['hauteur']."xL".$hotte['largeur']."xP".$hotte['profondeur']." mm</li>";
            if($hotte['couleur_fab'] != "") $lstCarac .= "<li>".$template->translate("/article/vignette/coloris","F")." : ".$template->translate($hotte['couleur_fab'])."</li>";
            $etiq_energie = $hotte[class_energetique];
        break;

        case '30':
            // rotissoire
            $rotissoire = $DB_site->query_first("SELECT nb_foyer, hauteur, largeur, profondeur,couleur_fab, class_energetique FROM article_rotissoire WHERE artid='$artid'");
            $lstCarac .= "<li>".$rotissoire['nb_foyer']." ".$template->translate("/article/vignette/nb_foyers")."</li>";
            $lstCarac .= "<li>".$template->translate("/article/vignette/dimensions")." H".$rotissoire['hauteur']."xL".$rotissoire['largeur']."xP".$rotissoire['profondeur']." mm</li>";

            if($rotissoire['couleur_fab'] != "") $lstCarac .= "<li>".$template->translate("/article/vignette/coloris","F")." : ".$template->translate($rotissoire['couleur_fab'])."</li>";
            $etiq_energie = $rotissoire[class_energetique];
        break;

        case '26';
            //combine froid capacite_max
            $combine = $DB_site->query_first("SELECT volume_cavite, hauteur, largeur, profondeur,couleur_fab, class_energetique FROM article_froid WHERE artid = '$artid'");
            $lstCarac .= "<li>".$template->translate("/article/vignette/volume")." : ".$combine['volume_cavite']."L</li>";
            $lstCarac .= "<li>".$template->translate("/article/vignette/dimensions")." H".$combine['hauteur']."xL".$combine['largeur']."xP".$combine['profondeur']." mm</li>";
            if($combine['couleur_fab'] != "") $lstCarac .= "<li>".$template->translate("/article/vignette/coloris","F")." : ".$template->translate($combine['couleur_fab'])."</li>";
            $etiq_energie = $combine[class_energetique];

        break;

        case '29':
            $tiroir = $DB_site->query_first("SELECT volume_cavite, hauteur, largeur, profondeur, couleur_fab, class_energetique FROM article_tiroir WHERE artid='$artid'");
            $lstCarac .= "<li>".$template->translate("/article/vignette/volume")." : ".$tiroir['volume_cavite']."L</li>";
            $lstCarac .= "<li>".$template->translate("/article/vignette/dimensions")." H".$tiroir['hauteur']."xL".$tiroir['largeur']."xP".$tiroir['profondeur']." mm</li>";
            if($tiroir['couleur_fab'] != "") $lstCarac .= "<li>".$template->translate("/article/vignette/coloris","F")." : ".$template->translate($tiroir['couleur_fab'])."</li>";
            $etiq_energie = $tiroir[class_energetique];
        break;

        case '21':
            $art = $DB_site->query_first("SELECT volume_cavite, hauteur, largeur, profondeur, couleur_fab, class_energetique FROM article_frigo WHERE artid='$artid'");
            $lstCarac .= "<li>".$template->translate("/article/vignette/volume")." : ".$art['volume_cavite']."L</li>";
            $lstCarac .= "<li>".$template->translate("/article/vignette/dimensions")." H".$art['hauteur']."xL".$art['largeur']."xP".$art['profondeur']." mm</li>";
            if($art['couleur_fab'] != "") $lstCarac .= "<li>".$template->translate("/article/vignette/coloris","F")." : ".$template->translate($art['couleur_fab'])."</li>";
            $etiq_energie = $art[class_energetique];
        break;

        case '23':
            $art = $DB_site->query_first("SELECT couverts, hauteur, largeur, profondeur, couleur_fab, class_energetique FROM article_lavevaisselle WHERE artid='$artid'");
            $lstCarac .= "<li>".$art['couverts']." ".$template->translate("/article/vignette/couverts")."</li>";
            $lstCarac .= "<li>".$template->translate("/article/vignette/dimensions")." H".$art['hauteur']."xL".$art['largeur']."xP".$art['profondeur']." mm</li>";
            if($art['couleur_fab'] != "") $lstCarac .= "<li>".$template->translate("/article/vignette/coloris","F")." : ".$template->translate($art['couleur_fab'])."</li>";
            $etiq_energie = $art[class_energetique];
        break;
        case '26':
            $art = $DB_site->query_first("SELECT capacite_lavage, hauteur, largeur, profondeur, couleur_fab, class_energetique FROM article_laveseche WHERE artid='$artid'");
            $lstCarac .= "<li>".$art['capacite_lavage']."KG</li>";
            $lstCarac .= "<li>".$template->translate("/article/vignette/dimensions")." H".$art['hauteur']."xL".$art['largeur']."xP".$art['profondeur']." mm</li>";
            if($art['couleur_fab'] != "") $lstCarac .= "<li>".$template->translate("/article/vignette/coloris","F")." : ".$template->translate($art['couleur_fab'])."</li>";
            $etiq_energie = $art[class_energetique];
        break;
        case '22':
            $art = $DB_site->query_first("SELECT volume_cavite, hauteur, largeur, profondeur, couleur_fab, class_energetique FROM article_congel WHERE artid='$artid'");
            $lstCarac .= "<li>".$template->translate("/article/vignette/volume")." : ".$art['volume_cavite']."L</li>";
            $lstCarac .= "<li>".$template->translate("/article/vignette/dimensions")." H".$art['hauteur']."xL".$art['largeur']."xP".$art['profondeur']." mm</li>";
            if($art['couleur_fab'] != "") $lstCarac .= "<li>".$template->translate("/article/vignette/coloris","F")." : ".$template->translate($art['couleur_fab'])."</li>";
            $etiq_energie = $art[class_energetique];
        break;
        case '38':
            $art = $DB_site->query_first("SELECT volume_cavite, hauteur, largeur, profondeur, couleur_fab, class_energetique FROM article_cellule_refroidissement WHERE artid='$artid'");
            $lstCarac .= "<li>".$template->translate("/article/vignette/volume")." : ".$art['volume_cavite']."L</li>";
            $lstCarac .= "<li>".$template->translate("/article/vignette/dimensions")." H".$art['hauteur']."xL".$art['largeur']."xP".$art['profondeur']." mm</li>";
            if($art['couleur_fab'] != "") $lstCarac .= "<li>".$template->translate("/article/vignette/coloris","F")." : ".$template->translate($art['couleur_fab'])."</li>";
            $etiq_energie = $art[class_energetique];
        break;
        case '25':
            $art = $DB_site->query_first("SELECT hauteur, largeur, profondeur, couleur_fab FROM article_meuble WHERE artid='$artid'");
            $lstCarac .= "<li>".$template->translate("/article/vignette/dimensions")." H".$art['hauteur']."xL".$art['largeur']."xP".$art['profondeur']." mm</li>";
            if($art['couleur_fab'] != "") $lstCarac .= "<li>".$template->translate("/article/vignette/coloris","F")." : ".$template->translate($art['couleur_fab'])."</li>";
        break;
        case '28':
            $art = $DB_site->query_first("SELECT hauteur, largeur, profondeur, couleur_fab FROM article_credence WHERE artid='$artid'");
            $lstCarac .= "<li>".$template->translate("/article/vignette/dimensions")." H".$art['hauteur']."xL".$art['largeur']."xP".$art['profondeur']." mm</li>";
            if($art['couleur_fab'] != "") $lstCarac .= "<li>".$template->translate("/article/vignette/coloris","F")." : ".$template->translate($art['couleur_fab'])."</li>";
        break;
        case '6':
            $art = $DB_site->query_first("SELECT hauteur, largeur, profondeur, couleur_fab, type_plancha, class_energetique FROM article_plancha WHERE artid='$artid'");
            $lstCarac .= "<li>".$template->translate("/article/vignette/volume")." : ".$art['type_plancha']."L</li>";
            $lstCarac .= "<li>".$template->translate("/article/vignette/dimensions")." H".$art['hauteur']."xL".$art['largeur']."xP".$art['profondeur']." mm</li>";
            if($art['couleur_fab'] != "") $lstCarac .= "<li>".$template->translate("/article/vignette/coloris","F")." : ".$template->translate($art['couleur_fab'])."</li>";
            $etiq_energie = $art[class_energetique];
        break;
        case '7':
            $art = $DB_site->query_first("SELECT hauteur, largeur, profondeur, couleur_fab,type_bbq, class_energetique FROM article_bbq WHERE artid='$artid'");
            $lstCarac .= "<li>".$template->translate($art['type_bbq'])."</li>";
            $lstCarac .= "<li>".$template->translate("/article/vignette/dimensions")." H".$art['hauteur']."xL".$art['largeur']."xP".$art['profondeur']." mm</li>";
            if($art['couleur_fab'] != "") $lstCarac .= "<li>".$template->translate("/article/vignette/coloris","F")." : ".$template->translate($art['couleur_fab'])."</li>";
            $etiq_energie = $art[class_energetique];
        break;
        case '27':
            $art = $DB_site->query_first("SELECT hauteur, largeur, profondeur, couleur_fab FROM article_divers WHERE artid='$artid'");
            if($art['hauteur']!= '0' || $art['largeur'] != '0' || $art['profondeur'] != '0'){
                $lstCarac .= "<li>".$template->translate("/article/vignette/dimensions")." H".$art['hauteur']."xL".$art['largeur']."xP".$art['profondeur']." mm</li>";
            }
            if($art['couleur_fab'] != "") $lstCarac .= "<li>".$template->translate("/article/vignette/coloris","F")." : ".$template->translate($art['couleur_fab'])."</li>";
        break;

    }


    //Sélection de l'article showroom
    $sql = "SELECT artid";
    $sql .= " FROM showroom_article";
    $sql .= " WHERE artid = ".$article['artid'];
    $aShowroom = $DB_site->query_first($sql);

    //Paramétrage des variables
    
    $template->parts['photo_ambiance'] = "<div class=\"ambiance\"></div>";
    $template->parts["hover_static"]="static";
    $template->parts["hover_static2"]="static2";
    $template->parts["height_ajust"]="height_ajust";
    if(!empty($aPhotoAmbiance)){
	    $template->parts["hover_static"]= "";
        $template->parts["hover_static2"]="";
        $template->parts["height_ajust"]="";
        $template->parts['photo_ambiance'] = "<a href=\"".$template->mkurl('article',$article['artid'],$article)."\"><img class=\"ambiance\" src=\"images/".$article['artid']."_".$aPhotoAmbiance['articlephotoid']."_260_223.jpg\" srcset=\"images/".$article['artid']."_".$aPhotoAmbiance['articlephotoid']."_360_223.jpg 275w\" width=\"360\" height=\"223\" alt=\"".htmlentities($article['libelle'])."\" ></a>";
        //$template->parts['photo_ambiance'] = "<a href=\"".$template->mkurl('article',$article['artid'],$article)."\"><img class=\"ambiance\" src=\"images/".$article['artid']."_".$aPhotoAmbiance['articlephotoid']."_630_365.jpg\" alt=\"".$article['libelle']."\"></a>";
    }
	
	//Tableau des dimensions de visuels pour les articles catégorie
    $aDimensions = array(
        'promo'                 => array(110,160),
        'top'                   => array(110,160),
        'cdc'                   => array(110,160),
        'selectionslide'        => array(110,160),
        'categorie'             => array(210,280),
        'categorie_zoom'        => array(220,300),
        'accessoires'           => array(210,280),
        'vins'                  => array(210,280),
        'accessoires_panier'    => array(300,280),
        'vins_panier'           => array(300,280),
        'accessoires_zoom'      => array(220,300),
        'similaire'             => array(210,280),
        'similaire_zoom'        => array(220,300),
        'selection'             => array(210,280),
        'selection_zoom'        => array(220,300),
        'accessoires_comp'      => array(210,280),
        'accessoires_comp_zoom' => array(220,300),
        'pack'                  => array(210,280),
        'pack_zoom'             => array(220,300),
        'packoffert'            => array(210,280),
        'packoffert_zoom'       => array(220,300)
    );

    $template->parts['artid']	= $article['artid'];
    $template->parts['libelle_trunk']    = Truncate($article['libelle'], 120, true);
    $template->parts['libelle']          = $article['libelle'];
    $template->parts["reference"]        = $article['artcode'];
    $template->parts["logomarque"]       = "<img class=\"lazy\" data-src=\"/marques/".$tabMarque['marqueid']."_100_30.jpg\" alt=\"".$tabMarque['mtype']."\" width=\"80\" height=\"24\">";
    $template->parts["marque"]	    	= $tabMarque['libelle'];
    if($config!=null){
        $template->parts["datalayer_fiche"]="onclick=\"datalayer('config','".str_replace(array("\r", "\n"), '', addslashes($article['libelle']))."','".$article['artcode']."','voir la fiche')\"";
        $template->parts["datalayer_apercu"]="datalayer('config','".str_replace(array("\r", "\n"), '',addslashes($article['libelle']))."','".$article['artcode']."','apercu');";
        //$template->parts["datalayer_compare"]="onclick=\"datalayer('config','".str_replace(array("\r", "\n"), '', addslashes($article['libelle']))."','".$article['artcode']."','comparer')\"";
    }
    $template->parts["url"]              = $template->mkurl('article',$article['artid'],$article).($config!=null?"#config":'');
    $template->parts["img"]="";
    if($variante == "categorie" || $variante == "similaire"){
        $template->parts["mobile_cat"] = "vignette_cat";
        $template->parts["img"]= "<a href=\"".$template->mkurl('article',$article['artid'],$article).($config!=null?"#config":'')."\"><img class=\"visuel kdopackimg lazy img_cat_mobile\" data-src=\"".article_image($article['artid'],$aDimensions[$variante][0]+100,$aDimensions[$variante][1]+100,$kdopack)."\" src=\"/images/templates/photo_non_dispo_210_280.jpg\" width=\"210\" height=\"280\" alt=\"".htmlentities($article['libelle'])." ".$tabMarque[0]."\"></a>";

    }else{

        $template->parts["img"]= "<a href=\"".$template->mkurl('article',$article['artid'],$article).($config!=null?"#config":'')."\"><img class=\"visuel kdopackimg lazy\" data-src=\"".article_image($article['artid'],$aDimensions[$variante][0],$aDimensions[$variante][1],$kdopack)."\" src=\"/images/templates/photo_non_dispo_210_280.jpg\" width=\"210\" height=\"280\" alt=\"".htmlentities($article['libelle'])." ".$tabMarque[0]."\"></a>";

    }
    //$template->parts["img"]= "<a href=\"".$template->mkurl('article',$article['artid'],$article)."\"><img class=\"visuel kdopackimg\" src=\"".article_image($article['artid'],$aDimensions[$variante][0],$aDimensions[$variante][1],$kdopack)."\" alt=\"".$article['libelle']." ".$tabMarque[0]."\"></a>";

    //$template->parts["ajout_panier"] = "<a onclick=\"ajaxAddProduct2(\"".$article['artid']."\", true); return false;\" href=\"javascript:void(0);\">AJOUTER AU PANIER </a>";
    $template->parts["img_zoom"]         = "<img class=\"visuel\" src=\"".article_image($article['artid'],$aDimensions[$variante.'_zoom'][0],$aDimensions[$variante.'_zoom'][1],$kdopack)."\" alt=\"".$article['libelle']." ".$tabMarque[0]."\">";
    //agrandissement des imgages pour les hover des vignettes accesoires sur les pages articles.
    $img2 = "<img class=\"visuel kdopackimg\" src=\"".article_image($article['artid'],300,280,$kdopack)."\" alt=\"".htmlentities($article['libelle'])." ".$tabMarque[0]."\">";
    $template->parts["img2"]	     = str_replace("visuel kdopackimg","hover_img",$img2);
    $template->parts["caracteristiques"] = $lstCarac;
    $template->parts["showroom"]         = !empty($aShowroom) ? '<p class="showroom">SHOW<br>ROOM</p>' : '';
    $template->parts["capacite"]         = $article['capacite_bouteilles'] != '' ? $template->translate("/article/vignette/capacite","F")." : <strong>".$article['capacite_bouteilles']."</strong> ".$template->translate($article['capacite_unite']) : '&nbsp;';
    $template->parts["hideapercu"]	= "hidden";
    $template->parts["price_reduc"]="";
    $template->parts["prix_normal"]	= "";
    $template->parts["prix_remise"]	= "";
    $template->parts["type_tva"]	= "";
    $template->parts['vignette_indic'] = '';
    $template->parts["pc"]="";
    
    //Calculs remise
    $prix_normal= '';
    $price_reduc='';

    if($article['active']==1 && in_array($article['commandable'],array(1,2))){
    
    	//Sélection de la garantie article
    	if(isset($user_info['paysid'])) {
    		$idPays = $user_info['paysid'];
    	} else {
    		$idPays = $template->pays_ref();
    	}
    	    
    	$tabGaranties  = DAOgarantie::loadMaxGarantieArtByPays($DB_site,$template->site_code(),'FR',$idPays,$article['artid'],!$sitepro ? 1 : 2);
    	$garantie      = $tabGaranties[0];
    	$dureeGarantie = '';
    	if(!empty($garantie)){
    		
    		//si la garantie est un extension
    		if($garantie->cumul == 1){

    			//on cherche dans le tableau qui contient toutes les garanties offertes celle du fournisseur des années de garanties avant l'extension et du même type
    			$pointer   = DAOgarantie::searchGarantieToAddExtension($DB_site,$garantie->type_garantie,$tabGaranties,$garantie->garantieid);
    			//on prend la durée de cette garantie pour l'ajouter à l'extension
    			$dureeBase = $tabGaranties[$pointer]->duree;
    			$duree     = $garantie->duree + $dureeBase;

    		} else {

    			$duree = $garantie->duree;
    		}

    		$dureeGarantie = $duree;
    	}
	
    	$classid="";
    	$delailivraison=0;
    	$si=delailivraison($article);
    	$delailivraison=$si['delailivraison'];
    	if($si['stock']!=false) $classid="2";

    	$template->parts["dispo"]="<div class=\"stock stockvert$classid\">".$template->translate("/article/vignette/en_stock","F")."</div>";
    	if($article["commandable"]==2) $template->parts["dispo"]="<div class=\"stock stockrouge\">".$template->translate("/article/vignette/sur_commande","F")."</div>";
    	if($si['date_appro']!=false){
    	    $class="stockorange";
    	    if($delailivraison+($si["date_appro"]-time())/86400<21) $class="stockvert".$classid;
    	    $template->parts["dispo"]="<div class=\"stock $class\">".$template->translate("/article/vignette/date_reappro","F")." ".date("d/m/Y",$si["date_appro"])."</div>";
    	}elseif($delailivraison>15)
    	    $template->parts["dispo"]="<div class=\"stock stockorange\">".sprintf($template->translate("/article/vignette/delai_x_semaines","F"),floor($delailivraison/7))."</div>";
	
    
    	//Tarif article
    	$article_prices = article_prices($article['artid'], $template->tva_ref(), $template->site_code(), $sitepro);
    	$taxtxt = ($sitepro ? "/site/ht" : "/site/ttc");

    	$sql = "SELECT COUNT(*)";
    	$sql .= " FROM promo";
    	$sql .= " WHERE artid = ".$article['artid'];
    	$sql .= " AND ((dbt_promo<".time()." AND fin_promo>".time().") OR (dbt_promo = 0 AND fin_promo = 0))";
    	$promo = $DB_site->query_first($sql);

    	$sql = "SELECT COUNT(*)";
    	$sql .= " FROM secondchoix";
    	$sql .= " WHERE artid = ".$article['artid'];
    	$secondchoix = $DB_site->query_first($sql);
    

    	if($promo[0] > 0 || $secondchoix[0] > 0){
    		if($article_prices['remise']>=5){
                $price_reduc='price_reduc';
    		    $prix_normal   =  "<span class=\"prix_barre\">".$template->price_format($article_prices['prixconseille'])."</span>";
    		    $percent_reduc = "<p class=\"reduc\">-".round($article_prices['remise'])."%</p>";
    		}
    		
    		if($promo[0]>0 )
    		    $template->parts['vignette_indic'] = "<p class=\"vignette_indic promo\">".$template->translate("/article/vignette/promo")."</p>";
    		if($secondchoix[0] > 0)
    		    $template->parts['vignette_indic'] = "<p class=\"vignette_indic second_choix\">".$template->translate("/article/vignette/second_choix")."</p>";
    	}
	

    	$template->parts["vfimg"]="";
    	if($article_prices['vf']){
    		if($article_prices['remise']>=5){
                $price_reduc='price_reduc';
    		    $prix_normal   = "<span class=\"prix_barre\">".$template->price_format($article_prices['prixconseille'])."</span>";
    		    $percent_reduc = "<p class=\"reduc vf\">-".round($article_prices['remise'])."%</p>";
    		}
        	$template->parts['vignette_indic'] = "<p class=\"vignette_indic vf\">French Days</p>";
            // $template->parts["vfimg"]="<img src=\"/images/templates/".$template->langue."/venteflash.png\" class=\"vfimg\">";
    	}
        $template->parts["vpimg"]="";
        if($article_prices['vp']){
            if($article_prices['remise']>=5){
                $price_reduc='price_reduc';
                $prix_normal   = "<span class=\"prix_barre\">".$template->price_format($article_prices['prixconseille'])."</span>";
                $percent_reduc = "<p class=\"reduc vf\">-".round($article_prices['remise'])."%</p>";
            }
            $template->parts['vignette_indic'] = "<p class=\"vignette_indic vf\">".$template->translate("/article/vignette/vente_mobar_dometic")."</p>";
            // $template->parts["vfimg"]="<img src=\"/images/templates/".$template->langue."/venteflash.png\" class=\"vfimg\">";
        }
	
    	if($kdopack>0 && is_null($article_prices['vf'])) $template->parts['vignette_indic'] = "<p class=\"vignette_indic promo\">".$template->translate("/article/vignette/promo")."</p>";

    	$template->parts["prix_normal"] = $prix_normal;
        $template->parts["price_reduc"] = $price_reduc;
    	$template->parts["pc"]          = $percent_reduc;
    	$template->parts["prix_remise"] = $template->price_format($article_prices['prix']);
    	$template->parts["type_tva"]    = $template->translate($taxtxt,"U");
    	$template->parts["garantie"]         = $dureeGarantie != '' ? '<p class="garantie"><span>'.$dureeGarantie.'</span><br>'.$template->translate("/article/garantie_type/an".($dureeGarantie>1?"s":"")).'</p>' : '';

        $template->parts["etiq_energie"]     = $etiq_energie != '' ? '<p class="sonore"><img class="picto" src="/images/templates/energie_'.$template->translate("/article/class_energetique/$etiq_energie").'.jpg" alt="niveau sonore"></p>' : '';

        $template->parts["etiq_niveausonore"]     = $article['niveau_bruit_abcd'] != '' ? '<p class="sonore"><img class="picto" src="/images/templates/niveausonore_'.$article['niveau_bruit_abcd'].'.png" alt="niveau sonore"></p>' : '';
    	$template->parts["fabrication"]      = ($article['paysfabrication']=='0'?"<p class='origine'><img src=\"/images/templates/origine-".$article['paysfabrication'].".jpg\" alt=\"".$template->translate("/article/origines/".$article['paysfabrication'],"F")."\" class=\"origine\"></p>":"");
    	if($variante!="pack") $template->parts["hideapercu"]	= "";
    }
    
    if($classSup != '') $template->parts["class_sup"] = $classSup;
    
    $template->parts["vignette_hover"]="";
    $hover="";
    if($article['typearticleid']==2 || $article['typearticleid']==6 || $article['typearticleid']==8){
        $template->parts["miseaupanier"]  = "";
        $objPdt = new produit($DB_site, $artid);
        if(!$objPdt->hasOptions() && $article['active']==1) {
            if($variante == 'accessoires_panier' || $variante == 'vins_panier')
               $template->evaltemplate("VignetteArticleHoverAccPanier2","miseaupanier"); 
            else    
                $template->evaltemplate("VignetteArticleHoverAccPanier","miseaupanier");
        }
    }

    switch($variante){
    	case 'promo':
	       $template->evaltemplate("IndexVignette","vignettespromo");
           break;

    	case 'top':
    	    $template->evaltemplate("IndexVignette","vignettesMV");
    	    break;
        case 'selectionslide':
            $template->evaltemplate("IndexVignetteSelectionResp","articles_slides");
            break;    
    	case 'cdc':
    	    $template->evaltemplate("IndexVignette","vignettesselection");
            $template->parts["comparateur"]="";
    	    break;	

    	case 'categorie':
    	    $template->evaltemplate("VignetteArticleHover","vignette_hover");
    	    $template->evaltemplate("VignetteArticle","articles");
            $template->parts["comparateur"]="";
    	    break;	

        case 'accessoires':
            $template->evaltemplate("VignetteArticleHover2Acc","vignette_hover");
            $template->evaltemplate("VignetteArticle2","artcons");
            break;

        case 'vins':
            $template->evaltemplate("VignetteArticleHover2Acc","vignette_hover");
            $template->evaltemplate("VignetteArticle2","vinscons");
            break;

        case 'accessoires_panier':
            $template->evaltemplate("VignetteArticleHoverPanier","vignette_hover");
            $template->evaltemplate("VignetteArticlePanier","artcons");
            break;

        case 'vins_panier':
            $template->evaltemplate("VignetteArticleHoverPanier","vignette_hover");
            $template->evaltemplate("VignetteArticlePanier","vinscons");
            break;

        case 'similaire':
            $template->parts["styleSimi"] = "style=\"margin-right: 39.5px; margin-left: 0px;\"";
            $template->evaltemplate("VignetteArticleHover".$hover,"vignette_hover");
            $template->evaltemplate("VignetteArticle","artsim");

            break;
        case 'selection':
            $template->evaltemplate("VignetteArticleHover".$hover,"vignette_hover");
            $template->evaltemplate("VignetteArticle","artselect");

            break;
        case 'accessoires_comp':
            $template->evaltemplate("VignetteArticleHover".$hover,"vignette_hover");
            $template->evaltemplate("VignetteArticle","articlescomp");
            
            break;
        case 'pack':
            $template->parts["targeturl"]=" target=\"_blank\"";
            $template->parts["hidebtn"]=" hidden";
            $template->evaltemplate("VignetteArticleHover".$hover,"vignette_hover");
            $template->evaltemplate("VignetteArticle","artinpack");
            
            break;
        case 'packoffert':
    	    
            $template->evaltemplate("ArticleAjoutPack2","cadeaupack");
            break;
    }

    $template->parts["class_sup"] = '';
}


function article_vu($artid){ 
    global $DB_site;
    global $template;
    global $sitepro;
    $table_article = table_typearticle($DB_site,$artid);
    $article=$DB_site->query_first("SELECT a.artid,libelle".$template->langue." AS libelle,libelle".$template->langue.",artcode,a_s.active,commandable,a.prix,prixht,a.prixconseilleht,image,dimensions,delailivraison,date_appro, spec.* FROM article a INNER JOIN $table_article as spec USING(artid) INNER JOIN article_site a_s ON (a_s.artid = a.artid) WHERE a.artid=".intval($artid)." AND a_s.site_code LIKE '".$template->site_code()."'");
    if($article[0]=="" || $article['active']!=1) return "";
    $mq=$DB_site->query_first("select libelle,m.marqueid,image,url".$template->langue." AS url from marque m, article_marque am where m.marqueid = am.marqueid and am.artid = '$artid'") ;
    $template->parts["logomarqueav"]="<img src=\"/marques/".$mq['marqueid']."_74_22.jpg\">";
    $article['marque']=$mq['libelle'];
    
    $taxtxt=($sitepro?"/site/ht":"/site/ttc");
    $article_prices=article_prices($artid,$template->tva_ref(),$template->site_code(),$sitepro);
    $price=$template->price_format($article_prices['prix'])." <small>".$template->translate($taxtxt,"U")."</small>";
	

    $autreimg=$DB_site->query_first("SELECT * FROM articlephoto WHERE artid=$artid AND visible=1 AND (langue='' OR langue='".$template->langue."')");
    $autre="";
    if($autreimg!="") $autre="_".$autreimg['articlephotoid'];
    $template->parts["img"]="<img src=\"/images/".$artid.$autre."_60_100.jpg\" alt=\"".htmlentities($article['libelle'])." ".$mq[0]."\">";
    $template->parts["url"]=$template->mkurl("article",$artid,$article);
    $template->parts["libelle_vu"]=htmlentities($article['libelle']);
    $template->parts["marque_vu"]=$mq['libelle'];
    $template->parts["ref_vu"]=$article['artcode'];

    $template->parts["price"]="";
    if($article["commandable"]==1) $template->parts["price"]="<p class=\"price\">".$price."</p>";
    $template->evaltemplate("ArticleVu","artvu_".$artid);
    return $template->parts["artvu_".$artid];
}

function artcons($artid){
    global $DB_site;
    global $template;
    global $sitepro;
    global $comparateur;
    
    if(isset($template->parts["artcons_".$artid])) return $template->parts["artcons_".$artid];
    
    $packs=new Pack($DB_site);
    $kdopack=$packs->kdos_pack($artid);
    
    $article=$DB_site->query_first("SELECT artid,artcode,libelle".$template->langue." AS libelle,a_s.active,commandable,gamme".$template->langue.",modele,image,nbr_temperature,capacite_bouteilles,capacite_mini,capacite_maxi,version_porte,coloris,dimensions,date_appro,typearticleid FROM article a INNER JOIN article_site a_s USING(artid) WHERE a.artid=".intval($artid));
    if($article[0]=="" || $article['active']!=1) return "";
    $template->parts["artid"]=$article['artid'];

    $marque=$DB_site->query_first("select libelle,m.marqueid,image from marque m, article_marque am where m.marqueid = am.marqueid and am.artid = '$artid'");

    $template->parts["img"]="<img src=\"".article_image($artid,130,130,$kdopack)."\" alt=\"".htmlentities($article['libelle'])." ".$article['artcode']."\">";
    
    $strikeout="<div class=\"estrikeout\"></div>";
    $template->parts["prices"]="";
    $template->parts["msg"]="";
    $template->parts["pc"]="";
    $template->parts["addbasket"]="";
    $msg="";
    switch($article['commandable']){
	case 1:
	$taxtxt=($sitepro?"/site/ht":"/site/ttc");
	$article_prices=article_prices($artid,$template->tva_ref(),$template->site_code(),$sitepro);
	
	$promo=$DB_site->query_first("SELECT COUNT(*) FROM promo WHERE artid=".$artid);
	if(round($article_prices['prix'])!=round($article_prices['prixconseille']) && $promo[0]>0){
	   $strikeout="<div class=\"strikeout\">".$template->price_format($article_prices['prixconseille'])." <small>".$template->translate($taxtxt,"U")."</small></div>";
	   $template->parts["pc"]="<div class=\"pc\">-".round($article_prices['remise'])."%</div>";
	}
	
	if($article_prices['vf']){
	    $template->parts["pc"] = "<div class=\"pc\">-".round ($article_prices['remise'])."%</div>" ;
	    $strikeout="<div class=\"strikeout\">".$template->price_format($article_prices['prixconseille'])." <small>".$template->translate($taxtxt,"U")."</small></div>";
	    $venteflashimg="<img src=\"/configurations/www.mon-espace-cuisson.com/images/templates/".$template->langue."/venteflash.png\" style=\"position:absolute;\">";
	    $template->parts["vfimg"]="<img src=\"/images/templates/".$template->langue."/venteflash.png\" class=\"vfimg\">";
	}
	
	$price=$template->price_format($article_prices['prix'])." <small>".$template->translate($taxtxt,"U")."</small>";
	
	$template->parts["prices"]="<div class=\"prices\">  
	    $strikeout
	    <div class=\"price\">$price</div>
	</div>";
	$template->evaltemplate("ArtConsBasket","addbasket");
	break;

	case 2:
	$template->parts["msg"]="<div class=\"msg\">".$template->translate("/article/vignette/non_dispo","F")."</div>";
	break;

	case 0:
	$template->parts["msg"]="<div class=\"msg\">".$template->translate("/article/vignette/prix_sur_demande","F")."</div>";
	break;
    }

    $classid="";
    $delailivraison=0;
    $si=delailivraison($article);
    $delailivraison=$si['delailivraison'];
    if($si['stock']!=false) $classid="2";
    
    $template->parts["dispo"]="<div class=\"stockvert$classid\"></div> ".$template->translate("/article/vignette/en_stock","F");
    if($article["commandable"]==0 || $article["commandable"]==2) $template->parts["dispo"]="<div class=\"stockrouge\"></div> ".$template->translate("/article/vignette/sur_commande","F");
    if($si['date_appro']!=false){
	$class="stockorange";
	if($delailivraison+($si["date_appro"]-time())/86400<21) $class="stockvert".$classid;
	$template->parts["dispo"]="<div class=\"stockorange\"></div> ".$template->translate("/article/vignette/date_reappro","F")." ".date("d/m/Y",$si["date_appro"]);
    }elseif($delailivraison>15)
	$template->parts["dispo"]="<div class=\"stockorange\"></div> ".sprintf($template->translate("/article/vignette/delai_x_semaines","F"),floor($delailivraison/7));

    $template->parts["comparateur"]="";
    /*if($comparateur->compare_type($article['typearticleid'])){
	if($comparateur->in_articles($artid)){
	    $classComp="btn_comparer_on";
	    $txtComp=$template->translate("/comparateur/ajoute_au_comparateur","F");
	}else{
	    $classComp="btn_comparer_off";
	    $txtComp=$template->translate("/comparateur/comparer","F");
	}
	$template->parts["comparateur"]=<<<HTML
	<a id="btn_comparaison$artid" class="$classComp btn_comparer" onclick="toggleCompare($artid);" href="javascript:void(0)">
	  <div class="case"></div>
	  <span id="span_comparaison$artid">$txtComp</span>
	</a>
HTML;
    }*/
	
    $template->parts["quickview"]="";
    $template->evaltemplate("vignetteQV","quickview");
    $template->parts["qvaction"]="onmouseenter=\"show_quickview(this,true)\" onmouseleave=\"show_quickview(this,false)\"";
	
    $template->parts["url"]="/art,$artid,".url_rewrite($article['libelle']).".htm";

    $template->parts["libelle"]=$article['libelle']." ".$marque['libelle'];
    $template->parts["artcode"]=$article['artcode'];
    
    $template->parts["artclass"]="artcons";
    $template->evaltemplate("ArtCons","artcons_".$artid);
    
    return $template->parts["artcons_".$artid];
}

function artsim($artid){
    global $DB_site;
    global $template;
    global $sitepro;
    
    $article=$DB_site->query_first("SELECT artid,artcode,libelle".$template->langue." AS libelle,active,commandable,gamme".$template->langue.",modele,prix,prixht,prixconseilleht,image,nbr_temperature,capacite_bouteilles,capacite_mini,capacite_maxi,version_porte,coloris,dimensions,tauxchoisi,date_appro,delailivraison FROM article WHERE artid=".intval($artid));
    if($article[0]=="" || $article['active']!=1) return "";
    $template->parts["artid"]=$article['artid'];

    $packs=new Pack($DB_site);
    $kdopack=$packs->kdos_pack($artid);
    
    $marque=$DB_site->query_first("select libelle,m.marqueid,image from marque m, article_marque am where m.marqueid = am.marqueid and am.artid = '$artid'");

    $template->parts["img"]="<img src=\"".article_image($artid,134,180,$kdopack)."\" alt=\"".$article['libelle']." ".$article['artcode']."\">";


    $strikeout="<div class=\"estrikeout\"></div>";
    $template->parts["prices"]="";
    $template->parts["msg"]="";
    $template->parts["pc"]="";
    $template->parts["addbasket"]="";
    $msg="";
    switch($article['commandable']){
	case 1:
	$taxtxt=($sitepro?"/site/ht":"/site/ttc");
	$article_prices=article_prices($artid,$template->tva_ref(),$template->site_code(),$sitepro);
	
	$promo=$DB_site->query_first("SELECT COUNT(*) FROM promo WHERE artid=".$artid);
	
	if(round($article_prices['prix'])!=round($article_prices['prixconseille']) && ($promo[0]>0 || $secondchoix[0]>0)){
	   $strikeout="<div class=\"strikeout\">".$template->price_format($article_prices['prixconseille'])." <small>".$template->translate($taxtxt,"U")."</small></div>";
	   $template->parts["pc"]="<div class=\"pc\">-".round($article_prices['remise'])."%</div>";
	}

	if($article_prices['vf']){
	    $template->parts["pc"] = "<div class=\"pc\">-".round ($article_prices['remise'])."%</div>" ;
	    $strikeout="<div class=\"strikeout\">".$template->price_format($article_prices['prixconseille'])." <small>".$template->translate($taxtxt,"U")."</small></div>";
	    $venteflashimg="<img src=\"/configurations/www.mon-espace-cuisson.com/images/templates/".$template->langue."/venteflash.png\" style=\"position:absolute;\">";
	    $template->parts["vfimg"]="<img src=\"/images/templates/".$template->langue."/venteflash.png\" class=\"vfimg\">";
	}
	
	$price=$template->price_format($article_prices['prix'])." <small>".$template->translate($taxtxt,"U")."</small>";

	$template->parts["prices"]="<div class=\"prices\">  
	    $strikeout
	    <div class=\"price\">$price</div>
	</div>";
	break;

	case 2:
	$template->parts["msg"]="<div class=\"msg\">".$template->translate("/article/vignette/non_dispo","F")."</div>";
	break;

	case 0:
	$template->parts["msg"]="<div class=\"msg\">".$template->translate("/article/vignette/prix_sur_demande","F")."</div>";
	break;
    }

    $classid="";
    $delailivraison=0;
    $si=delailivraison($article);
    $delailivraison=$si['delailivraison'];
    if($si['stock']!=false) $classid="2";

    $template->parts["dispo"]="<div class=\"stockvert$classid\"></div> ".$template->translate("/article/vignette/en_stock","F");
    if($article["commandable"]==2) $template->parts["dispo"]="<div class=\"stockrouge\"></div> ".$template->translate("/article/vignette/sur_commande","F");
    if($si['date_appro']!=false){
	$class="stockorange";
	if($delailivraison+($si["date_appro"]-time())/86400<21) $class="stockvert".$classid;
	$template->parts["dispo"]="<div class=\"stockorange\"></div> ".$template->translate("/article/vignette/date_reappro","F")." ".date("d/m/Y",$si["date_appro"]);
    }elseif($delailivraison>15)
	$template->parts["dispo"]="<div class=\"stockorange\"></div> ".sprintf($template->translate("/article/vignette/delai_x_semaines","F"),floor($delailivraison/7));

    $template->parts["url"]="/art,$artid,".url_rewrite($article['libelle']).".htm";

    $template->parts["libelle"]=Truncate($article['libelle'],80)." ".$marque['libelle'];
    $template->parts["artcode"]=$article['artcode'];
    
    $template->parts["quickview"]="";
    $template->evaltemplate("vignetteQV","quickview");
    $template->parts["qvaction"]="onmouseenter=\"show_quickview(this,true)\" onmouseleave=\"show_quickview(this,false)\"";
    
    
    $template->parts["artclass"]="artsim";
    $template->evaltemplate("ArtCons","artsim_".$artid);
    
    return $template->parts["artsim_".$artid];
}

function cdc_onglet($artid){
    global $DB_site;
    global $template;
    global $sitepro;
    
    if(isset($template->parts["cdc_".$artid])) return $template->parts["cdc_".$artid];
    
    $article=$DB_site->query_first("SELECT artid,artcode,libelle".$template->langue." AS libelle,active,commandable,gamme".$template->langue.",modele,prix,prixht,tauxchoisi,prixconseilleht,image,nbr_temperature,capacite_bouteilles,capacite_mini,capacite_maxi,version_porte,coloris,dimensions FROM article WHERE artid=".intval($artid));
    if($article[0]=="" || $article['active']!=1) return "";

    $price=number_format($article['prix'],2,',','');
    $strikeout="<div class=\"estrikeout\"></div>";
    $remise="";
    
    $taxtxt=($sitepro?"/site/ht":"/site/ttc");
    $article_prices=article_prices($artid,$template->tva_ref(),$template->site_code(),$sitepro);
    
    if(round($article_prices['prix'])!=round($article_prices['prixconseille'])){
	$strikeout="<div class=\"strikeout\">".$template->price_format($article_prices['prixconseille'])." <small>".$template->translate($taxtxt,"U")."</small></div>";
	$template->parts["pc"]="<div class=\"pc\">-".round($article_prices['remise'])."%</div>";
    }

    if($article_prices['vf']){
	$template->parts["pc"] = "<div class=\"pc\">-".round ($article_prices['remise'])."%</div>" ;
	$strikeout="<div class=\"strikeout\">".$template->price_format($article_prices['prixconseille'])." <small>".$template->translate($taxtxt,"U")."</small></div>";
	$venteflashimg="<img src=\"/configurations/www.mon-espace-cuisson.com/images/templates/".$template->langue."/venteflash.png\" style=\"position:absolute;\">";
	$template->parts["vfimg"]="<img src=\"/images/templates/".$template->langue."/venteflash.png\" class=\"vfimg\">";
    }
    
    $price=$template->price_format($article_prices['prix'])." <small>".$template->translate($taxtxt,"U")."</small>";
	

    $img="<img src=\"/images/".$article['artid']."_180_170.jpg\" alt=\"".$article[$libelle]."\" $style>";
    $url="/art,$artid,".url_rewrite($article['libelle']).".htm";

    $mq=$DB_site->query_first("select libelle,m.marqueid,image,type".$template->langue." AS mtype from marque m, article_marque am where m.marqueid = am.marqueid and am.artid = '$artid'") ;
    $logomarque="";
    $imgm="configurations/www.mon-espace-cuisson.com/images/marques/br/".$mq[marqueid].".".$mq[image];
    if(file_exists($imgm) && $mq[image]!=""){
	  $size=getimagesize($imgm);
	  $dh=$size[1]*90/$size[0];
	  $style="style=\"height:30px;\"";
	  $logomarque="<img src=\"/".$imgm."\" alt=\"$mq[mtype]\" $style>";
    }

    $template->parts["typeproduit"]=$article['libelle']."<br>";
    $template->parts["libelle"]=$article['libelle'];
    $template->parts["logomarque"]=$logomarque;
    $template->parts["img"]=$img;
    $template->parts["url"]=$url;
    $template->parts["artcode"]=$article['artcode'];
    $template->parts["strikeout"]=$strikeout;
    $template->parts["price"]=$price;
    $template->parts["pc"]=$remise;
    $template->evaltemplate("OngletCDC","cdc_".$artid);
    return $template->parts["cdc_".$artid];
}

function slider($artid){
    global $DB_site;
    global $template;
    global $sitepro;
    
//     if(isset($template->parts["cdc_".$artid])) return $template->parts["cdc_".$artid];
    
    $article=$DB_site->query_first("SELECT artid,artcode,libelle".$template->langue." AS libelle,active,commandable,gamme".$template->langue.",modele,prix,prixht,prixconseilleht,image,nbr_temperature,capacite_bouteilles,capacite_mini,capacite_maxi,capacite_unite,version_porte,coloris,dimensions,tauxchoisi FROM article WHERE artid=".intval($artid));
    if($article[0]=="" || $article['active']!=1) return "";

    $template->parts["prices"]="";
    if($article['commandable']==1){
	$taxtxt=($sitepro?"/site/ht":"/site/ttc");
	$strikeout="<div class=\"estrikeout\"></div>";
	$article_prices=article_prices($artid,$template->tva_ref(),$template->site_code(),$sitepro);
	
	$promo=$DB_site->query_first("SELECT COUNT(*) FROM promo WHERE artid=".$artid);
	
	if(round($article_prices['prix'])!=round($article_prices['prixconseille']) && $promo[0]>0){
	   $strikeout="<div class=\"strikeout\">".$template->price_format($article_prices['prixconseille'])." <small>".$template->translate($taxtxt,"U")."</small></div>";
	   $pc="<div class=\"pc\">-".round($article_prices['remise'])."%</div>";
	}
	
	if($article_prices['vf']){
	    $template->parts["pc"] = "<div class=\"pc\">-".round ($article_prices['remise'])."%</div>" ;
	    $strikeout="<div class=\"strikeout\">".$template->price_format($article_prices['prixconseille'])." <small>".$template->translate($taxtxt,"U")."</small></div>";
	    $venteflashimg="<img src=\"/configurations/www.mon-espace-cuisson.com/images/templates/".$template->langue."/venteflash.png\" style=\"position:absolute;\">";
	    $template->parts["vfimg"]="<img src=\"/images/templates/".$template->langue."/venteflash.png\" class=\"vfimg\">";
	}

	$price=$template->price_format($article_prices['prix'])." <small>".$template->translate($taxtxt,"U")."</small>";

	$template->parts["prices"]="<div class=\"prices\">  
	    $strikeout
	    <div class=\"price\">$price</div>
	</div>".$pc;
    }

    $autreimg=$DB_site->query_first("SELECT * FROM articlephoto WHERE artid=$artid AND visible=1 AND (langue='' OR langue='".$template->langue."')");
    $autre="";
    if($autreimg!="") $autre="_".$autreimg['articlephotoid'];
    $img="<img src=\"/images/".$article[artid].$autre."_217_200.jpg\" alt=\"".$article['libelle']."\">";
    $url="/art,$artid,".url_rewrite($article['libelle']).".htm";

    $mq=$DB_site->query_first("select libelle,m.marqueid,image,type".$template->langue." AS mtype from marque m, article_marque am where m.marqueid = am.marqueid and am.artid = '$artid'") ;
    $logomarque="<img src=\"/marques/".$mq['marqueid']."_155_45.jpg\" alt=\"$mq[mtype]\">";

    $desc="";
    if($article[nbr_temperature]!="") $desc.="<li>".$template->translate($article['nbr_temperature'])." ".$template->translate("/article/vignette/temperatures")."</li>";
    if($article[capacite_bouteilles]!="") $desc.="<li>".$template->translate("/article/vignette/capacite","F")." : $article[capacite_bouteilles] ".$template->translate($article['capacite_unite'])."</li>";
    if($article[coloris]!="") $desc.="<li>".$template->translate("/article/vignette/coloris","F")." : ".$template->translate($article[coloris])."</li>";
    if($article[dimensions]!="") $desc.="<li>".$template->translate("/article/vignette/dim","F")." : $article[dimensions] mm</li>";
    if(!$basket) $template->parts["liste"]="<ul>$desc</ul>";

    $template->parts["libelle"]=$article['libelle'];
    $template->parts["logomarque"]=$logomarque;
    $template->parts["img"]=$img;
    $template->parts["url"]=$url;
    $template->parts["artcode"]=$article['artcode'];
    $template->evaltemplate("SliderArticle","slider_".$artid);
    return $template->parts["slider_".$artid];
}


function vignetteCat($categorie,$prefix="",$artcount,$url=null,$type="promo") {
    global $DB_site;
    global $template;
    global $sitepro;


    if(is_null($url)) $url = $template->mkurl($prefix==""?"categorie":str_replace("_","",$prefix),$categorie->catid,$categorie);
    $template->parts["img"]="<img src=\"".$categorie->image(320,192)."\" alt=\"".$categorie->{$prefix."libelle"}."\">";
    $template->parts["libelle"]="<a href=\"".$url."\" id=\"link".$categorie->catid."\">".$categorie->{$prefix."libelle"}."</a>";
    $template->parts["link"]=$url;
    
    $taxtxt=($sitepro?"/site/ht":"/site/ttc");
    $maxpc=$categorie->max_pc();
    $template->parts["hide"]="";
    $template->parts["pc"]=-$maxpc[0]."%";
    if($maxpc[0]==0) $template->parts["hide"]="hidden";
    $template->parts["catid"]=$categorie->catid;
    $template->parts["artcount"]=$artcount;
    
    switch($type)
    {
        case 'promo':
            $template->evaltemplate("vignetteCat","vignette_".$categorie->catid);
            break;
        case 'classic':
             $template->parts["block_equip"]="catmere_flex";
            $template->evaltemplate("vignetteCatClassic","vignette_".$categorie->catid);
            break;
    }

    return $template->parts["vignette_".$categorie->catid];
}

function vignetteAcc($artid,$artcode="artcode"){
    global $DB_site;
    global $template;
    
    $article=$DB_site->query_first("SELECT artid,artcode,artcode_eurocave,libelle".$template->langue." AS libelle,gamme".$template->langue." FROM article WHERE artid=".intval($artid));
    if($article=="") return false;
    $mq=$DB_site->query_first("select libelle,m.marqueid,image,type".$template->langue." AS mtype from marque m, article_marque am where m.marqueid = am.marqueid and am.artid = '$artid'") ;
    
    $autreimg=$DB_site->query_first("SELECT * FROM articlephoto WHERE artid=$artid AND visible=1 AND (langue='' OR langue='".$template->langue."')");
    $autre="";
    if($autreimg!="") $autre="_".$autreimg['articlephotoid'];
    
    $reftype="artcode";
    if($artcode!="artcode") $reftype="artcode_eurocave";
    $template->parts["libelle"]=Truncate($article['libelle'],70);
    $template->parts["artcode"]=Truncate($article[$reftype],33);
    $template->parts["img"]="<img src=\"/images/".$artid.$autre."_130_130.jpg\" alt=\"".$article['libelle']." ".$article['artcode']."\">";
    
    $artcode_url=$article[$reftype];
    if($reftype=="artcode"){
	$sp=explode("-",$article[$reftype]);
	array_shift($sp);
	$artcode_url="aci-".implode("",$sp);
    }
    $template->parts["link"]="/".$template->translate("/article/accessoires/accessoires","L")."/".url_rewrite($article['libelle']."-".$mq['libelle'])."-".url_rewrite($artcode_url)."-art".$article['artid'].".html";
    
    $template->parts["vignacccave_".$artid]="";
    $template->evaltemplate("VignAccCave","vignacccave_".$artid);
    return $template->parts["vignacccave_".$artid];
}

/* 
 * Permet de récupérer l'affichage d'un produit pour intégration dans un pack
 * 
 * Entrée :
 * $artid (integer) : Identifiant de l'article
 * $type (string) : Type de template a afficher
 * $packid (integer) : Identifiant du pack
 * $qte (integer) : Quantité de l'article
 * $dispo ?
 * $dispoid ?
 * $smallPic (string) : Si le produit doit s'afficher en taille vignette ou classique
 * 
 * Sortie :
 * (string) : Chaîne de caractères contenant la structure d'un produit du pack
*/
function artpack($artid, $type = "ArtCons", $packid, $qte, &$dispo, &$dispoid, $smallPic=null){
    global $DB_site;
    global $template;
    
    //Récupération des informations sur l'article
    $sql = "SELECT artid, artcode, libelle".$template->langue." AS libelle, active, commandable, gamme".$template->langue.", prix, prixht, prixconseilleht, image, coloris,dimensions, tauxchoisi, date_appro, delailivraison";
    $sql .= " FROM article";
    $sql .= " WHERE artid = ".intval($artid);
    $article = $DB_site->query_first($sql);

    //Si l'article n'existe pass ou qu'il est désactivé, on ne retourne rien
    if($article[0] == ""){
         return "";
    }

    /*$template->parts["logomarqueav"]="";
    $mq=$DB_site->query_first("select libelle,m.marqueid,image from marque m, article_marque am where m.marqueid = am.marqueid and am.artid = '$artid'");
    $imgm="configurations/images/marques/br/".$mq[marqueid].".".$mq[image];
    if(file_exists($imgm) && $mq[image]!=""){
    $size=getimagesize($imgm);
    $dh=$size[1]*74/$size[0];
    $style="style=\"width:74px;\"";
    if($dh>22) $style="style=\"height:22px;\"";
    $template->parts["logomarqueav"]="<img src=\"/".$imgm."\" alt=\"$mq[url]\" class=\"logomarque\"$style>";
    }*/
    
    $sql = "SELECT *";
    $sql .= " FROM article_pack";
    $sql .= " WHERE artid = ".intval($artid);
    $tabCaracPack = $DB_site->query_first($sql);
    $caractvalid = $tabCaracPack['caractvalids'];
    
    $autre = $autreimg = "";
    
    //Construction de l'image en fonction de la couleur
    if($caractvalid != ""){
         //Récupération de la 1ère couleur associée à ce produit du pack
         $sql = "SELECT *";
         $sql .= " FROM article_caractval ac";
         $sql .= " INNER JOIN caracteristiquevaleur c ON (c.caractvalid = ac.caractvalid)";
         $sql .= " WHERE artid = ".$artid;
         $sql .= " AND (caractid = 1 OR caractid = 7)";
         $sql .= " AND c.caractvalid IN (".$caractvalid.")";
         $autreimg = $DB_site->query_first($sql);
    }
    
    //Si une couleur a été trouvé
    if($autreimg != ""){
         $autre = "_".$autreimg['articlephotoid'];
    }
    
    //Si l'image du produit avec cette couleur n'existe pas
    if(!file_exists("configurations/images/produits/".$artid.$autre.".jpg")){
         $autre = "";
    }
    
    //Si on souhaite afficher la taille vignette
    if($smallPic == "smallPicture"){
         $img = "<img id=\"picpack-".$artid."\" src=\"".$host."/images/".$artid.$autre."_90_90.jpg\" alt=\"".$article['libelle']." ".$article['artcode']."\">";
         $template->parts["style"] = "";
    //Sinon, grande taille
    } else {
         $img = "<img id=\"picpack-".$artid."\" src=\"".$host."/images/".$artid.$autre."_150_150.jpg\" alt=\"".$article['libelle']." ".$article['artcode']."\">";
         $template->parts["style"] = "style=\"width: 220px;\"";
    }
    $template->parts["img"] = $img;
    
   
    $classid = "";
    $delailivraison = 0;
    $si = delailivraison($article);
    $delailivraison = $si['delailivraison'];
    if($si['stock'] != false){
         $classid="2";
    }
    $dispoid = 1;
    $dispo = "<div class=\"stockvert".$classid."\"></div> ".$template->translate("/article/vignette/en_stock","F");
    
    //Produit commandable sur commande
     if($article["commandable"]==0 || $article["commandable"]==2) {
     $dispoid=5;
     $dispo="<div class=\"stockrouge\"></div> ".$template->translate("/article/vignette/sur_commande","F");
     }
     
     //Affichage du picto stock
     if($article["date_appro"]>0){
          $class = "stockorange";
          if($delailivraison + ($article["date_appro"] - time()) / 86400 < 21){
               $class="stockvert".$classid;
          }
          $dispoid = 2;
          $dispo = "<div class=\"stockorange\"></div> ".$template->translate("/article/vignette/date_reappro","F")." ".date("d/m/Y",$article["date_appro"]);
     }elseif($si['date_appro'] != false){
          $class="stockorange";
          if($delailivraison+($si["date_appro"]-time())/86400<21){
               $class="stockvert".$classid;
          }
          $dispoid=3;
          $dispo="<div class=\"stockorange\"></div> ".$template->translate("/article/vignette/date_reappro","F")." ".date("d/m/Y",$si["date_appro"]);
     }elseif($delailivraison > 15) {
          $dispoid = 4;
          $dispo = "<div class=\"stockorange\"></div> ".sprintf($template->translate("/article/vignette/delai_x_semaines","F"),floor($delailivraison / 7));
     }
    //$url="/art,$artid,".url_rewrite($article['libelle']).".htm";
    //$template->parts["url"]=$url;
    $template->parts["url"] = $url = "/art,$artid,".url_rewrite($article['libelle']).".htm";

    //$template->parts["libelle"]= ($smallPic=="smallPicture") ? cleanCut($article['libelle'],40) : $article['libelle']." ".$mq['libelle'];

    $template->parts["libelle"]= "<div class=\"libelle\" onMouseOver=\"document.getElementById('libHide".$artid.'_'.$packid."').style.display = 'block'\" onMouseOut=\"document.getElementById('libHide".$artid.'_'.$packid."').style.display = 'none'\"><a href=\"".$url."\">".Truncate($article['libelle'],60)."</a></div>";
    $template->parts["libelleHide"] = "<div class=\"libelleHide\" id=\"libHide".$artid.'_'.$packid."\">".$article['libelle']." ".$mq['libelle']."</div>";

    $template->parts["artcode"]=$article['artcode'];
    $template->parts["qtepdtoffertpack"]="";
    if($qte>1) {
      $template->parts["artcode"].=" x ".$qte;
      $template->parts["qtepdtoffertpack"]=" x".$qte;
    }
   
    $template->evaltemplate("ArticleVignette", "artpack_".$artid.'_'.$packid);

    return $template->parts["artpack_".$artid.'_'.$packid];
}

?>
