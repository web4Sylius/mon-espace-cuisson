<?php

include_once("global.php");


$showcat=false;
$idkdoscat=new categorie($DB_site,0,$template->langue);
$idkdoscat->idkdos_load();
$mainpage=$idkdoscat->idkdos_url;
 //pour le dernier lien du breadcrumb on ne met pas l'attribut typeof="v:Breadcrumb" sinon cela cause des erreurs d'explorations google
$template->parts["fil_ariane"].=" &gt; <span property=\"itemListElement\" typeof=\"ListItem\"><a property=\"item\" typeof=\"WebPage\" href=\"".$mainpage."\"><span property=\"name\">".$idkdoscat->idkdos_libelle."<span></a><meta property=\"position\" content=\"2\"></span>";
    
$libelletranche="";
if(isset($_GET['url'])) {
    $showcat=$idkdoscat->load_from_url($_GET['url']);
    if(!$showcat){
	$template->redirect($idkdoscat->idkdos_url,301);
    }
    $idkdoscat->subcategories();
    $idkdoscat->setparam("bonsplans",array("idkdos","selection"));
    $idkdoscat->setparam("pro",($sitepro?2:1));
    $idkdoscat->setparam("site_code",$template->site_code());
    
    if($idkdoscat->prix!=array()){
	$libelletranche=sprintf($template->translate("/categorie/entre_euro","L"),$template->price_format($idkdoscat->prix[0],0),$template->price_format($idkdoscat->prix[1],0));
	if($idkdoscat->prix[0]==0) $libelletranche=sprintf($template->translate("/categorie/inferieur_euro","L"),$template->price_format($idkdoscat->prix[1],0));
	if($idkdoscat->prix[0]==end($idkdoscat->listeprix)) $libelletranche=sprintf($template->translate("/categorie/superieur_euro","L"),$template->price_format($idkdoscat->prix[0],0));
	$libelletranche=" ".$libelletranche;
    }
    $template->parts["fil_ariane"].=" &gt; <span typeof=\"v:Breadcrumb\">".$idkdoscat->idkdos_libelle.$libelletranche."</span>";
}

$template->parts["title"]=$idkdoscat->idkdos_libelle.$libelletranche;
$template->parts["description"]=$idkdoscat->idkdos_description;


$logopromo=$DB_site->query_first("SELECT * FROM logopromo WHERE type='idkdos' AND `".($sitepro?"pro":"part")."`=1 AND langue='".$template->langue."'");
if($logopromo!="") $template->parts["description_img"]="<img src=\"/images/logopromo/".$logopromo['id'].".jpg\" alt=\"".$template->translate("/categorie/idees_cadeaux")."\" class=\"img img-responsive\">";


$remartids=array();

/*$template->parts["li"]="<ul>";
$main=new categorie($DB_site,0,$template->langue);
$maincats=$main->subcategories("position");
foreach($maincats as $maincat){
    $template->parts["articlescat"]="";
    $template->parts["catlibelle"]=$maincat[0]->libelle;
    $template->parts["catid"]=$maincat[0]->catid;
    
    $maincat[0]->setparam("bonsplans",array("idkdos","selection"));
    $maincat[0]->setparam("pro",($sitepro?2:1));
    $maincat[0]->setparam("site_code",$template->site_code());

    $artcount=$maincat[0]->articlecount($maincat[0]->getsubcatids($maincat[0]->catid));
    if($artcount>0) {
      $maincat[0]->idkdos_load();
      $class="";
      if($maincat[0]->catid==$idkdoscat->catid){
	  $class="checked";
	  $template->parts["catlink"]="<a href=\"".$idkdoscat->url."\">".$idkdoscat->libelle."</a>";
      }
    
      if($maincat[0]->idkdos_url!=""){
	  $template->parts["li"].="<li class=\"".$class."\"><a href=\"".$maincat[0]->idkdos_url."\">".$maincat[0]->libelle." (".$artcount.")</a></li>";
	  if(!$showcat) $template->parts["idkdosvigncat"].=vignetteCat($maincat[0],"idkdos_",$artcount);
      }
    }
}
$template->parts["li"].="</ul>";

$template->evaltemplate("CategorieMenuCategorie","menucategorie");


$tranches=$main->listeprix;
$template->parts["li"]="<ul>";
$t=0;
$template->parts["pricebtn"]="<ul class=\"pricebtns\">";
for($i=0;$i<count($tranches)-1;$i++){
    $template->parts["articlescat"]="";
    $catlibelle=sprintf($template->translate("/categorie/entre_euro","F"),$template->price_format($tranches[$i],0),$template->price_format($tranches[$i+1],0));
    if($i==0) $catlibelle=sprintf($template->translate("/categorie/inferieur_euro","F"),$template->price_format($tranches[$i+1],0));
    if($i==count($tranches)-2) $catlibelle=sprintf($template->translate("/categorie/superieur_euro","F"),$template->price_format($tranches[$i],0));
    $template->parts["catid"]=$i;
    $template->parts["catlibelle"]=$catlibelle;
    $cat=new categorie($DB_site,0,$template->langue);
    $cat->subcategories();
    $cat->setparam("prix",array($tranches[$i],$tranches[$i+1]));
    $cat->setparam("bonsplans","selection");
    $cat->setparam("pro",($sitepro?2:1));
    $cat->setparam("site_code",$template->site_code());
    $cdcids=$cat->getidkdos();
    $artcount=count($cdcids);
    if($cdcids!=false && $artcount>0){
      $cat->idkdos_load("prix");
      $class=($idkdoscat->prix[0]===$tranches[$i] ? "checked" : "");
      $template->parts["li"].="<li class=\"".$class."\"><a href=\"".$cat->idkdos_url."\">".$catlibelle." ($artcount)</a></li>";
      $template->parts["pricebtn"].="<li class=\"".(($t+1)%4==0 ? "last" : "")."\"><a href=\"".$cat->idkdos_url."\">".$catlibelle." ($artcount)</a></li>";
      $t++;
    }
}
$template->parts["li"].="</ul>";
$template->parts["pricebtn"].="<div class=\"clear\"></div></ul>";

$template->evaltemplate("CategorieMenuPrix","menucategorie");
*/


if($showcat) {
    
    $artids=$idkdoscat->articles("prix");
    if(count($artids)==0) $template->redirect($mainpage,302);
    $art_stat=new article_stat($DB_site,$template,$artids);
    $template->parts["description"]=$art_stat->replace($idkdoscat->idkdos_description);
    $template->parts["description_img"]="<img src=\"".$idkdoscat->image(230,134)."\">";

    $i=1;
    foreach($artids as $artid) {
	if($i==5 && $idkdoscat->catid>0) $template->evaltemplate("promoLink","articles");
	$class2="";
	if($i%4==1) $class2="first";
	if($i%4==0) $class2="last";
	vignette($artid,"categorie",$class2);
	$i++;
    }
    
    if($i<=4 && $idkdoscat->catid>0) $template->evaltemplate("promoLink","articles");
    
}else{
    foreach($main->getMenus() as $idmenu=>$menuinfos){
	$subcatids=$main->getMenu($idmenu);
	$subcatids=array_map(function($a){return $a['catid'];},$subcatids);
	$main->setsubcatids($subcatids);
	$main->setparam("bonsplans",array("idkdos","selection"));
	$main->setparam("pro",($sitepro?2:1));
	$main->setparam("site_code",$template->site_code());
	$artcount=$main->articlecount($main->subcatids,false);
	if($artcount>0) {
	    $i=1;
	    $menulibelle=$menuinfos["libelle"];
	    $template->parts["articles"].=<<<HTML
	    <div class="titrearticles kdos"><span>
            <h2 class="color_bg">$menulibelle</h2></span>
	    </div>
HTML;
	    foreach($subcatids as $menusubcatid){
		$pcat=new categorie($DB_site,$menusubcatid,$template->langue);
		$pcat->setparam("bonsplans",array("idkdos","selection"));
		$pcat->setparam("pro",($sitepro?2:1));
		$pcat->setparam("site_code",$template->site_code());
		$pcat->idkdos_load();
		$pcat->setsubcatids($menusubcatid);
		$catartcount=$pcat->articlecount($pcat->subcatids,false);
		
		if($catartcount>0){
		    if($i == 1) $template->parts["class_sup"] = 'first';
		    if($i == 3){
			$template->parts["class_sup"] = 'last';
			$i = 0;
		    }
		    $template->parts["articles"].=vignetteCat($pcat,"idkdos_",$catartcount);
		    $template->parts["class_sup"] = '';
		    $i++;
		}
	    }
	    // $template->parts["articles"].="<div class=\"clear\">&nbsp;</div>";
	}
    }
}


$remarketing=array("libelle"=>array(),"price"=>array(),"category"=>array());
foreach($remartids as $artid){
    $article=$DB_site->query_first("SELECT c.libelle".$template->langue." AS categorie, a.libelle".$template->langue." AS libelle FROM categorie c INNER JOIN article a USING(catid) WHERE artid=".$artid);
    $remarketing['libelle'][]=utf8_encode(html_entity_decode($article['libelle'],ENT_COMPAT,"ISO-8859-15"));
    $remarketing['category'][]=utf8_encode(html_entity_decode($article['categorie'],ENT_COMPAT,"ISO-8859-15"));
    $remarketing['price'][]=article_price($artid);
}
$template->parts["remarketing"]=google_remarketing($remartids,"category",'',$remarketing['category'],$remarketing['libelle'],$remarketing['price']);



if(!$showcat){
    foreach($template->sites() as $site_code=>$site){
	foreach($site['hosts'] as $url=>$deflangue){
	    if($site['actif'][$url]){
		$sitecategorie=new categorie($DB_site,0,$deflangue);
		$sitecategorie->idkdos_load();
		$template->parts["alternate"].="<link rel=\"alternate\" hreflang=\"".$site['locales'][$deflangue]."\" href=\"https://www.".$url.$sitecategorie->idkdos_url."\">\n";
	    }
	}
    }
}


$encarts=encarts($template->site_code(),$template->langue,"idkdos",0);
foreach($encarts as $encart)
    $template->evaltemplate($encart,"encarts");

include("barrefixe.php");

$template->pageparams($host,$idkdoscat->idkdos_ref_title.$libelletranche." - ".$template->translate("/site/nom"),$idkdoscat->idkdos_ref_title.". ".$template->parts["description"]);
$template->evaltemplate("KdosPage","main");

$template->out();

?>