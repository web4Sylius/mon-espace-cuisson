<?php
include("global.php");

// Archives - Listes des marques
if(!isset($_GET['url'])){

	$cols=4;

	$lm=$DB_site->query("SELECT marqueid,libelle FROM marque INNER JOIN display_marque USING(marqueid) ORDER BY libelle");
	while($m=$DB_site->fetch_array($lm))
		$template->parts['listemarques'].="<option value=\"".$m['marqueid']."\">".$m['libelle']."</option>";

	$m=new marque($DB_site,0,$template->langue);
	$marques=$m->listall("libelle",($sitepro?2:1),$template->site_code(),3);

	$template->parts["articles"]="";

	$c=0;
	foreach($marques as $marque){
		$marque->archive_load();
		$template->parts["marquelink"]=$marque->archive_url;
		$template->parts["marquelibelle"]=$marque->libelle;
		$template->parts["imglogo"]="<img src=\"".$marque->logo(150,50)."\" width=\"150\" height=\"50\" alt=\"".$marque->type."\" title=\"".$marque->libelle."\">";
		//$template->parts["articles"].="<td>";
		$template->evaltemplate("MarqueLink","articles");
		//$template->parts["articles"].="</td>";
		$c++;
	}

	// $template->parts["articles"].="</div>";
	$template->parts["title"]=$template->translate("/archives/h1","F");
	$template->parts["description_img"]="<img id=\"categorie_visuel\" src=\"/categorie/1_230_134.jpg\" class=\"img img-responsive\">";
	$template->parts["fil_ariane"]=" &gt; <span property=\"itemListElement\" typeof=\"ListItem\"><span property=\"name\">".$template->translate("/archives/fil","F")."</span><meta property=\"position\" content=\"2\"></span>";
	$template->evaltemplate("KdosPage","main");
	$template->pageparams($host,$template->translate("/archives/metatitle","F"),$template->translate("/archives/metatitle","F"));

// Archives - Listes produits d'une marque
}else{

	$marque=new marque($DB_site,0,$template->langue);
	$marque->archive_from_url($_GET['url']);

	if(!is_null($marque->archive_categorie)){
		$selectcat=new categorie($DB_site,0,$template->langue);
		$cattype="archive";
		$selectcat->load_from_url($marque->archive_categorie,$cattype);
		$filter_cat=$selectcat->catid;
	}

	$cat=new categorie($DB_site,0,$template->langue);
	$cat->setparam("marqueids",$marque->marqueid);
	$cat->setparam("pro",($sitepro?2:1));
	$cat->setparam("site_code",$template->site_code());
	$cat->setparam("active",array(3, 5));
	$cat->setparam("bonsplans",array("selection"));
	$subcategories=$cat->subcategories("position");

	$template->parts["li"]="<ul>";
	$cat->setparam("prix",array());

	foreach($subcategories as $p=>$subcategorie){
		$template->parts["articlescat"]="";
		$cat->setparam("subcatids",$subcategorie[0]->subcatids);
		$artids=$cat->articles("prix",false);
		$count=count($artids);

		if($count>0){
			if($subcategorie[0]->archive_load()) $template->parts["li"].="<li class=\"".($filter_cat==$subcategorie[0]->catid?"checked":"")."\"><a href=\"".$marque->archive_categorie_url($subcategorie[0])."\" onclick=\"this.className='checked';\">".$subcategorie[0]->libelle."</a></li>";

			if(is_null($filter_cat) || $filter_cat==$subcategorie[0]->catid){
				$c=1;
				foreach($artids as $artid){
					$class2="";
					if($c==1) $class2="first $artid";
					if($c==4){
						$c=0;
						$class2="last";
					}

					$template->parts["articlescomp"]="";
					vignette($artid,"accessoires_comp",$class2);
					$template->parts["articlescat"].=$template->parts["articlescomp"];
					$c++;
				}

				$template->parts["catid"]=$subcategorie[0]->catid;
				$template->parts["catlibelle"]=$subcategorie[0]->libelle." ".ucwords(strtolower($clmarque->libelle));
				$template->evaltemplate("KdosCategorie","articles");
			}
		}
	}
	// Liste déroulante Archives - REF ACILYON + REF FABRICANT
	$req = $DB_site->query("SELECT a.artid,artcode,artcode_eurocave,refs_fabricant FROM article a INNER JOIN article_site a_s USING(artid) INNER JOIN article_marque am USING(artid) INNER JOIN marque m USING(marqueid) WHERE m.marqueid=".$marque->marqueid." AND a_s.active=3 AND a_s.site_code='".$template->site_code()."' AND a.pro IN ('".($sitepro?2:1)."','3') ".$where." GROUP BY artcode ORDER BY artcode");
	$optaci = "<option value=\"0\" selected>Tous</option>";
	$optfab = "<option value=\"0\" selected>Tous</option>";
	$aci=$fab=array();
	while($row = $DB_site->fetch_array($req)){
		$aci[$row['artid']] = $row['artcode'];
		$artcode_fab = explode(" ", $row['artcode_eurocave']);
		$fab[$row['artid']] = $artcode_fab[0];
	}

	$template->parts["li"].="</ul>";
	$template->evaltemplate("CategorieMenuCategorie","menucategorie");

	if(is_null($selectcat)){
		$template->parts["title"]=$marque->archive_title;
		$template->parts["fil_ariane"]=" &gt; <span property=\"itemListElement\" typeof=\"ListItem\"><a property=\"item\" typeof=\"WebPage\" href=\"/".$template->translate("/archives/url")."\"><span property=\"name\">".$template->translate("/archives/fil","F")."</span></a><meta property=\"position\" content=\"2\"></span> &gt; <span property=\"itemListElement\" typeof=\"ListItem\"><span property=\"name\">".$marque->libelle."</span><meta property=\"position\" content=\"3\"></span>";
	}else{
		$template->parts["title"]=$selectcat->archive_title." ".$marque->libelle;
		$template->parts["fil_ariane"]=" &gt; <span property=\"itemListElement\" typeof=\"ListItem\"><a property=\"item\" typeof=\"WebPage\" href=\"/".$template->translate("/archives/url")."\"><span property=\"name\">".$selectcat->archive_title."</span></a><meta property=\"position\" content=\"2\"></span> &gt; <span property=\"itemListElement\" typeof=\"ListItem\"><span property=\"name\">".$marque->libelle."</span><meta property=\"position\" content=\"3\"></span>";
	}

	// FILTRE
	$template->parts["titre_filtre"] = "<div id=\"affinage\"><p>AFFINER VOTRE RECHERCHE</p></div> <div class=\"ligne\">";
	foreach($aci as $ref_id=>$ref_value){
		$template->parts["liste_choices"].="<li class=\"choix-0\"><a class=\"choice\" href=\"archive-art".$ref_id.".htm\">".$ref_value."</a></li>";
	}

	$template->parts["nom_filtre"] ="filtre_refids";
	$template->parts["filtre_nom"] = $template->translate("/categorie/filtres/reference","F");
	if($template->parts["all_choices"] == '') $template->parts["all_choices"] = $template->translate("/categorie/filtres/tous","F");
	$template->evaltemplate("FiltreSelect","les_filtres");

	$template->parts["liste_choices"]="";
	$template->parts["all_choices"]="";

	foreach($fab as $fab_id=>$fab_value){
		$template->parts["liste_choices"].="<li class=\"choix-1\"><a class=\"choice\" href=\"archive-art".$fab_id.".htm\">".$fab_value."</a></li>";
	}

	//paramétrage des balises hreflang en fr et en
	foreach($template->sites() as $site_code=>$site){
    	foreach($site['hosts'] as $url=>$deflangue){
		    if($site['actif'][$url]){	
		    	$mm=new marque($DB_site,0,strtoupper($site['locales'][$deflangue]));
		    	$mm->archive_from_url($_GET['url']);	
			    $template->parts["alternate"].="<link rel=\"alternate\" hreflang=\"".$site['locales'][$deflangue]."\" href=\"https://www.".$url."/".$mm->archive_url."\">\n";
			}		
	    }
	}


	$template->parts["nom_filtre"] ="filtre_fabids";
	$template->parts["filtre_nom"] = $template->translate("/categorie/filtres/fabricant","F");
	if($template->parts["all_choices"] == '') $template->parts["all_choices"] = $template->translate("/categorie/filtres/tous","F");
	$template->evaltemplate("FiltreSelect","les_filtres");
	$template->parts["titre_filtre"] .= "</div>";
	// FIN FILTRE

	$template->parts["description"]=$marque->archive_description;
	$template->parts["description_img"]="<img src=\"".$marque->logo(150,50)."\" alt=\"".trim($marque->type." ".$marque->libelle)."\" class=\"img img-responsive\">";
	$template->pageparams("www.mon-espace-cuisson.com",$template->parts["title"]." - ".$template->translate("/site/nom"),$template->parts["title"]." ".$template->translate("/index/meta_title","F"));
	$template->evaltemplate("KdosPage","main");
}

include("barrefixe.php");

$template->out();

?>