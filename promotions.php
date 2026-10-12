<?php

ini_set("display_errors",1);
include_once("global.php");

$main=new categorie($DB_site,0,$template->langue);

$showcat=false;
$promocat=new categorie($DB_site,0,$template->langue);
$promocat->promo_load();

$template->parts["fil_ariane"]="&gt; <span property=\"itemListElement\" typeof=\"ListItem\"><a property=\"item\" typeof=\"WebPage\" href=\"".$promocat->promo_url."\" ><span property=\"name\">".$template->translate("/categorie/promotions_moment","F")."</span></a><meta property=\"position\" content=\"2\"></span>";

if(isset($_GET['url'])) {
    $showcat=$promocat->load_from_url($_GET['url']);
    $promocat->subcategories();
    $promocat->setparam("bonsplans",array("promo","selection"));
    $promocat->setparam("pro",($sitepro?2:1));
    $promocat->setparam("site_code",$template->site_code());
    $url_get = $_GET['url'];
}

$template->parts["pagetype"]="promo";
$template->parts["title"]=$promocat->promo_libelle;
$template->parts["description"]=$promocat->promo_description;
$logopromo=$DB_site->query_first("SELECT * FROM logopromo WHERE type='promo' AND `".($sitepro?"pro":"part")."`=1 AND langue='".$template->langue."'");
if($logopromo!="") $template->parts["description_img"]="<img src=\"/images/logopromo/".$logopromo['id'].".jpg\" alt=\"".$template->translate("/categorie/promotions_moment")."\" class=\"img img-responsive\">";



foreach($main->getMenus() as $idmenu=>$menuinfos){
    $subcatids=$main->getMenu($idmenu);
    $subcatids=array_map(function($a){return $a['catid'];},$subcatids);
    $main->setsubcatids($subcatids);
    $main->setparam("bonsplans",array("promo","selection"));
    $main->setparam("pro",($sitepro?2:1));
    $main->setparam("site_code",$template->site_code());
    $artcount=$main->articlecount($main->subcatids);
    if($artcount>0) {
	$i=1;
	if(!$showcat){
	    $menulibelle=$menuinfos["libelle"];
	    $template->parts["articles"].=<<<HTML
	    <div class="col-xs-12 titrearticles promo"><div>
            <h2 class="color_bg">$menulibelle</h2></div>
	    </div>
HTML;
	}
	foreach($subcatids as $menusubcatid){
	    $pcat=new categorie($DB_site,$menusubcatid,$template->langue);
	    $pcat->setparam("bonsplans",array("promo","selection"));
	    $pcat->setparam("pro",($sitepro?2:1));
	    $pcat->setparam("site_code",$template->site_code());
	    $pcat->promo_load();
	    $pcat->setsubcatids($menusubcatid);
	    $catartcount=$pcat->articlecount($pcat->subcatids,false);
	    $class="";
	    
	    if($catartcount>0){
			if($pcat->catid==$promocat->catid){
				$class=" checked";
				$template->parts["all_choices"]=ucfirst($pcat->promo_libelle);
				$template->parts["description_img"]="<img src=\"".$promocat->image(230,134)."\" class=\"img img-responsive\" alt=\"description\">";
                                                if($sitepro) {
                        $template->parts["style_check"] = "style_check_pro";
                      }
                      else {
                      $template->parts["style_check"] = "";
                    }
			}
			$template->parts["liste_choices"].="<li class=\"choix-0\"><a href=\"".$pcat->promo_url."\" class=\"choice".$class."\">".ucfirst($pcat->promo_libelle)." (".$catartcount.")</a></li>";
			
			if(!$showcat){
				/*if($i == 1) $template->parts["class_sup"] = 'first';
				if($i == 3){
				$template->parts["class_sup"] = 'last';
				$i = 0;
				}*/
				$template->parts["articles"].=vignetteCat($pcat,"promo_",$catartcount);
				$template->parts["class_sup"] = '';
				$i++;
			}
	    }
	}
	//if(!$showcat) $template->parts["articles"].="<div class=\"clear\">&nbsp;</div>";
    }
}

$template->parts["filtregamme"] = "filtregamme";
$template->parts["nom_filtre"]="filtre_subcatids";
$template->parts["filtre_nom"]       = $template->translate("/categorie/filtres/categorie","F");
if($template->parts["all_choices"] == '') $template->parts["all_choices"] = $template->translate("/categorie/filtres/categorie","F");
//$template->parts["selectstyle"]="dark";
$template->evaltemplate("FiltreSelect","les_filtres");

$main=new categorie($DB_site,0,$template->langue);
$maincats=$main->subcategories("position");
$main->setparam("bonsplans",array("promo","selection"));
$main->setparam("site_code",$template->site_code());
$marques=$main->getmarques();

$template->parts["liste_choices"]="";
$template->parts["all_choices"]="";

foreach($marques as $param) {
    $marque=new marque($DB_site,$param->id,$template->langue);
    $marque->promo_load();
    $class="";
    if($promocat->marqueids[0]==$param->id){
	$class=" checked";
	$template->parts["catlink"]="<a href=\"/".$marque->link."\">".$marque->type."</a>";
	$template->parts["all_choices"]=ucfirst($marque->promo_link);
	$template->parts["description_img"]="<img src=\"".$marque->logo(150,50)."\">";
     if($sitepro) {
                        $template->parts["style_check"] = "style_check_pro";
                      }
                      else {
                      $template->parts["style_check"] = "";
                    }
    }
    if(!is_null($marque->promo_url)) $template->parts["liste_choices"].="<li class=\"choix-1\"><a href=\"/".$marque->promo_url."\" class=\"choice".$class."\">".ucfirst($marque->promo_link)." (".$param->qte.")</a></li>";
}


$template->parts["nom_filtre"]="filtre_marqueids";
$template->parts["filtre_nom"]       = $template->translate("/categorie/filtres/marque","F");
if($template->parts["all_choices"] == '') $template->parts["all_choices"] = $template->translate("/categorie/filtres/marque","F");
$template->parts["selectstyle"]="";
$template->evaltemplate("FiltreSelect","les_filtres");


if($showcat) {
    $artids=$promocat->articles("prix",false);
    $art_stat=new article_stat($DB_site,$template,$artids);
    $template->parts["description"]=$art_stat->replace($promocat->promo_description);
    //if($art_stat->max_pc>0) $promocat->promo_ref_title=$template->translate("/categorie/jusqua","F")." -".$art_stat->max_pc."% : ".$promocat->promo_ref_title;
    if($template->parts["catlink"]=="") $template->parts["catlink"]="<a href=\"".$promocat->url."\">".$promocat->libelle."</a>";

    $i=1;
    foreach($artids as $artid) {
	   $class2="";
	   //if($i%4==1) $class2="first";
	   //if($i%4==0) $class2="last";
	   vignette($artid,"categorie",$class2);
	   $i++;
    }
    
    //$template->evaltemplate("promoLink","articles");
    
    $template->parts["fil_ariane"].=" &gt; <span property=\"itemListElement\" typeof=\"ListItem\"><span property=\"name\">".$promocat->promo_libelle."</span><meta property=\"position\" content=\"3\"></span>";
    foreach($template->sites() as $site_code=>$site){
		foreach($site['hosts'] as $url=>$deflangue){
		    if($site['actif'][$url]){
		    	$prom = new categorie($DB_site,$promocat->catid,$deflangue);
		    	$prom->promo_load();
				$template->parts["alternate"].="<link rel=\"alternate\" hreflang=\"".$site['locales'][$deflangue]."\" href=\"https://www.".$url.$prom->promo_url."\">\n";
		    }
		}
    }
    
    if($promocat->catid>0 || $promocat->marqueids!=array()){
        $categorie=new categorie($DB_site,$promocat->catid,$template->langue);
        $categorie->setparam("pro",($sitepro?2:1));
        $categorie->setparam("site_code",$template->site_code());
        $categorie->setparam("marqueids",$promocat->marqueids);
        $categorie->subcategories();
        if($promocat->catid>0)
            $touturl=$template->mkurl("categorie",$categorie->catid,$categorie);
        else
            $touturl=$template->mkurl("marque",0,new marque($DB_site,$promocat->marqueids[0],$template->langue));

        $nbarticle=count($categorie->articles("prix"));
        $template->parts["selection"]="<div class=\"filtres_select\"><a href=\"".$touturl."\">".$template->translate("/categorie/filtres/tous_produits","F")." (".$nbarticle.")</a></div>";
        
        if(!$template->pro){
            $gammelink=array();
            foreach(array("access","premium") as $gamme){
                $gammecat=new categorie($DB_site,$promocat->catid,$template->langue);
                $gammecat->subcategories();
                $gammecat->setparam("pro",($sitepro?2:1));
                $gammecat->setparam("site_code",$template->site_code());
                $gammecat->setparam("pagegamme",$gamme);
                $gammecat->setparam("marqueids",$promocat->marqueids);
                $gammecat->gamme_load($promocat->marqueids!=array()?"marque":"categorie");
                $gammeurl=$template->mkurl("gamme",0,$gammecat);

                $nbarticle=count($gammecat->articles("prix",false));
                if($nbarticle>0){
                    $gammecat->catid=0;
                    $gammecat->gamme_load();
                    $gammelink[$gammecat->pagegamme[0]]="<div class=\"filtres_select\"><a href=\"".$gammeurl."\">".$gammecat->gamme_libelle." (".$nbarticle.")</a></div>";
                }
            }
            krsort($gammelink);
            $template->parts["selection"].=implode("",$gammelink);
        }
        
        $catartcount=$promocat->articlecount($promocat->subcatids,false);
        //si il n'y a pas de produits en promos par rapport a la recherche on redirige vers l'accueil
        if($catartcount==0){
            header("Location: /");
            exit();
        }

        $template->parts["selection"].="<div class=\"filtres_select\"><a class=\"checked\" style=\"text-align:center;\" href=\"".$promocat->promo_url."\" ><i class=\"fas fa-check\"></i> ".$template->translate("/admin/index/promotions","F")." (".$catartcount.")</a></div>";
        
        $template->evaltemplate("FiltreSelection","filtre_selection");
        
        $tailledutexte = (strlen($promocat->promo_libelle)+12)*10; //style="width:{$tailledutexte}px;"
        $produits=$template->translate("/categorie/article".($catartcount>1?"s":""));

        $template->parts["titre_promo_gamme"]=<<<HTML
         </div><div class="barre_filtre">
                   <h2 class="titre_result titre_seul" style="text-align: center;">$promocat->promo_libelle : <span class="count_prod">$catartcount $produits</span></h2>
            
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
				$sitecategorie->promo_load();
				$template->parts["alternate"].="<link rel=\"alternate\" hreflang=\"".$site['locales'][$deflangue]."\" href=\"https://www.".$url.$sitecategorie->promo_url."\">\n";
		    }
		}
    }
}

$template->pageparams($host,$promocat->promo_ref_title." - ".$template->translate("/site/nom"),$promocat->promo_ref_title.". ".$template->parts["description"]);
$template->evaltemplate("KdosPage","main");

$template->out();

?>