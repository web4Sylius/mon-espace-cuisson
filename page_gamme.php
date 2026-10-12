<?php

include_once("global.php");

$main=new categorie($DB_site,0,$template->langue);

$showcat=false;
$gammecat=new categorie($DB_site,0,$template->langue);


if(isset($_GET['url'])) {
    $gammecat->load_from_url($_GET['url']);
    $gammecat->subcategories();
    $gammecat->setparam("pro",($sitepro?2:1));
    $gammecat->setparam("site_code",$template->site_code());
    $showcat=$gammecat->catid>0 || count($gammecat->marqueids)>0;
}

$gammeindex = clone $gammecat;
$gammeindex->catid=0;
$gammeindex->gamme_load();

$template->parts["pagetype"]="gamme";
$template->parts["title"]=$gammecat->gamme_libelle;
$template->parts["description"]=$gammecat->gamme_description;
$template->parts["description_img"]="<img src=\"/images/templates/gamme-".$gammecat->pagegamme[0].".jpg\" alt=\"".$gammecat->gamme_libelle."\" class=\"img-responsive\">";
$lamarque = new marque($DB_site,$gammeindex->marqueids[0]);
$template->parts["fil_ariane"]="&gt; <span property=\"itemListElement\" typeof=\"ListItem\"><a property=\"item\" typeof=\"WebPage\" href=\"/".$lamarque->link."\"><span property=\"name\">".$gammeindex->gamme_libelle."</span></a><meta property=\"position\" content=\"2\"></span>";


foreach($main->getMenus() as $idmenu=>$menuinfos){
    $subcatids=$main->getMenu($idmenu);
    $subcatids=array_map(function($a){return $a['catid'];},$subcatids);
    $main->setsubcatids($subcatids);
    $main->setparam("pagegamme",$gammecat->pagegamme[0]);
    $main->setparam("pro",($sitepro?2:1));
    $main->setparam("site_code",$template->site_code());
    $artcount=$main->articlecount($main->subcatids,false);
    if($artcount>0) {
	$i=1;
	if(!$showcat){
	    $menulibelle=$menuinfos["libelle"];
	    $template->parts["articles"].=<<<HTML
	    <div class="titrearticles promo"><span>
            <h2 class="color_bg">$menulibelle</h2></span>
	    </div>
HTML;
	}
	foreach($subcatids as $menusubcatid){
	    $pcat=new categorie($DB_site,$menusubcatid,$template->langue);
	    $pcat->setparam("pagegamme",$gammecat->pagegamme[0]);
	    $pcat->setparam("pro",($sitepro?2:1));
	    $pcat->setparam("site_code",$template->site_code());
	    $pcat->gamme_load();
	    $pcat->setsubcatids($menusubcatid);
	    $catartcount=$pcat->articlecount($pcat->subcatids,false);
	    $class="";
	    
	    if($catartcount>0){
		if($pcat->catid==$gammecat->catid){
		    $class=" checked";
		    $template->parts["all_choices"]=ucfirst($pcat->gamme_libelle)." (".$catartcount.")";
		    $template->parts["description_img"]="<img src=\"".$gammecat->image(230,134)."\" class=\"img-responsive\">";
		    $curcatartcount=$catartcount;
		}
		$template->parts["liste_choices"].="<li class=\"choix-0\"><a href=\"".$pcat->gamme_url."\" class=\"choice".$class."\">".ucfirst($pcat->gamme_libelle)." (".$catartcount.")</a></li>";
	    
		if(!$showcat){
		    $template->parts["articles"].=vignetteCat($pcat,"gamme_",$catartcount,null,"classic");
		    $template->parts["class_sup"] = '';
		    $i++;
		}
	    }
	}
	if(!$showcat) $template->parts["articles"].="<div class=\"clear\">&nbsp;</div>";
    }
}

$template->parts["filtregamme"] = "filtregamme";
$template->parts["nom_filtre"]="filtre_subcatids";
$template->parts["filtre_nom"] = $template->translate("/categorie/filtres/categorie","F");
if($template->parts["all_choices"] == '') $template->parts["all_choices"] = $template->translate("/categorie/filtres/categorie","F");
//$template->parts["selectstyle"]="dark";
$template->evaltemplate("FiltreSelect","les_filtres");

$main=new categorie($DB_site,0,$template->langue);
$maincats=$main->subcategories("position");
$main->setparam("pagegamme",$gammecat->pagegamme);
$main->setparam("site_code",$template->site_code());
$marques=$main->getmarques();

$template->parts["liste_choices"]="";
$template->parts["all_choices"]="";

foreach($marques as $param) {
    $marque=new marque($DB_site,$param->id,$template->langue);
    $marque->gamme_load($gammecat->pagegamme[0]);
    $class="";
    if($gammecat->marqueids[0]==$param->id){
	$class=" checked";
	$template->parts["catlink"]="<a href=\"/".$marque->gamme_url."\">".$marque->type."</a>";
	$template->parts["all_choices"]=ucfirst($marque->libelle);
	$template->parts["description_img"]="<img src=\"".$marque->logo(150,50)."\" class=\"img-responsive\">";
	$curcatartcount=$param->qte;
    }
    if(!is_null($marque->gamme_url)) $template->parts["liste_choices"].="<li class=\"choix-1\"><a href=\"/".$marque->gamme_url."\" class=\"choice".$class."\">".ucfirst($marque->libelle)." (".$param->qte.")</a></li>";
}

$template->parts["filtre_nom"]       = $template->translate("/categorie/filtres/marque","F");
if($template->parts["all_choices"] == '') $template->parts["all_choices"] = $template->translate("/categorie/filtres/marque","F");
$template->parts["selectstyle"]="";
$template->evaltemplate("FiltreSelect","les_filtres");


$template->parts["liste_choices"]="";
$template->parts["all_choices"]="";


if($showcat) {
    
    $artids=$gammecat->articles("prix",false);
    $art_stat=new article_stat($DB_site,$template,$artids);
    $template->parts["description"]=$art_stat->replace($gammecat->gamme_description);
   
    if($template->parts["catlink"]=="") $template->parts["catlink"]="<a href=\"".$gammecat->url."\">".$gammecat->libelle."</a>";

    $i=1;
    foreach($artids as $artid) {
	   	$class2="";
    	vignette($artid,"categorie",$class2);
    	$i++;
    }
    $template->evaltemplate("promoLink","articles");
    
    
    $template->parts["fil_ariane"].=" &gt; <span property=\"itemListElement\" typeof=\"ListItem\"><span property=\"name\">".ucwords($gammecat->gamme_libelle)."</span><meta property=\"position\" content=\"3\"></span>";

    if($gammecat->catid>0 || $gammecat->marqueids!=array()){
        $categorie=new categorie($DB_site,$gammecat->catid,$template->langue);
        $categorie->setparam("pro",($sitepro?2:1));
        $categorie->setparam("site_code",$template->site_code());
        if($gammecat->marqueids!=array()){
            $marque=new marque($DB_site,$gammecat->marqueids[0],$template->langue);
            $marque->promo_load();
            $categorie->setparam("marqueids",$gammecat->marqueids);
        }
        $categorie->subcategories();

        $nbarticle=count($categorie->articles("prix"));
        if($gammecat->catid>0)
            $touturl=$template->mkurl("categorie",$categorie->catid,$categorie);
        else
            $touturl=$template->mkurl("marque",0,$marque,$template->langue);

        $template->parts["selection"]="<div class=\"filtres_select\"><a href=\"".$touturl."\">".$template->translate("/categorie/filtres/tous_produits","F")." (".$nbarticle.")</a></div>";
        
        $gammelink=array();
        $gammelink[$gammecat->pagegamme[0]]="<div class=\"filtres_select\"><a href=\"".$gammecat->gamme_url."\" class=\"checked\"><i class=\"fas fa-check\"></i> ".$gammeindex->gamme_libelle." (".$curcatartcount.")</a></div>";
        $othergamme=$gammecat->pagegamme[0]=="access"?"premium":"access";
        $othergammecat=clone $gammecat;
        $othergammecat->setparam("pagegamme",$othergamme);
        $othergammecat->setparam("marqueids",$gammecat->marqueids);
        $othergammecat->gamme_load($gammecat->marqueids!=array()?"marque":"categorie");
        $othergammeurl=$template->mkurl("gamme",0,$othergammecat);
        
        $nbarticle=count($othergammecat->articles("prix",false));
        if($nbarticle>0){
            $othergammecat->catid=0;
            $othergammecat->gamme_load();
            $gammelink[$othergammecat->pagegamme[0]]="<div class=\"filtres_select\"><a href=\"".$othergammeurl."\">".$othergammecat->gamme_libelle." (".$nbarticle.")</a></div>";
        }
        krsort($gammelink);
        $template->parts["selection"].=implode("",$gammelink);
        
        $categorie->setparam("bonsplans",array("promo"));
        $categorie->promo_load();
        $nbarticle=count($categorie->articles("prix",false));
        if($nbarticle>0){
            if($gammecat->catid>0)
                $promourl=$template->mkurl("promo",0,$categorie);
            else
                $promourl=$template->mkurl("promo",0,$marque);
            
            $categorie->promo_load();
            $template->parts["selection"].="<div class=\"filtres_select\"><a href=\"".$promourl."\">".$template->translate("/admin/index/promotions","F")." (".$nbarticle.")</a></div>";
        }
        
        $template->evaltemplate("FiltreSelection","filtre_selection");
        
        $tailledutexte = (strlen($gammecat->gamme_libelle)+12)*10;
        //style="width:{$tailledutexte}px;"
        $produits=$template->translate("/categorie/article".($curcatartcount>1?"s":""));

$template->parts["titre_promo_gamme"]=<<<HTML
         </div><div class="barre_filtre">
                   <h2 class="titre_result titre_seul" style="text-align: center;">$gammecat->gamme_libelle : <span class="count_prod">$curcatartcount $produits</span></h2>
            
HTML;
    }
}

$template->parts["filtresimple"]=" simple";
$template->evaltemplate("CategorieFiltres","liste_filtres");

include("barrefixe.php");


if(!$showcat){
    foreach($template->sites() as $site_code=>$site){
	foreach($site['hosts'] as $url=>$deflangue){
	    if($site['actif'][$url]){
		$sitecategorie=new categorie($DB_site,0,$deflangue);
		$sitecategorie->gamme_load();
		$template->parts["alternate"].="<link rel=\"alternate\" hreflang=\"".$site['locales'][$deflangue]."\" href=\"http://www.".$url.$sitecategorie->gamme_url."\">\n";
	    }
	}
    }
}


$template->pageparams($host,$gammecat->gamme_ref_title." - ".$template->translate("/site/nom"),$gammecat->gamme_ref_title.". ".$template->parts["description"]);
$template->evaltemplate("KdosPage","main");

$template->out();

?>