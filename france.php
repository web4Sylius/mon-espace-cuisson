<?php

include("global.php");



if(!isset($order)) $order="cat";
$func="getpromo";
$template->parts["title"]=$template->translate("/categorie/madeinfrance_title");
$template->parts["pagetitle"]=$template->translate("/categorie/madeinfrance_title");
$template->parts["description"]=$template->translate("/categorie/madeinfrance_description");
$template->parts["fil_ariane"]=" &gt; <span property=\"itemListElement\" typeof=\"ListItem\"><span property=\"name\">".$template->translate("/categorie/madeinfrance_title")."</span><meta property=\"position\" content=\"2\"></span>";
$template->parts["description_img"]="<img src=\"images/templates/logomadeinfrance.jpg\" alt=\"Made in France\" class=\"img img-responsive\">";


$remartids=array();

foreach($main->getMenus() as $idmenu=>$menuinfos){
    $subcatids=$main->getMenu($idmenu);
    $subcatids=array_map(function($a){return $a['catid'];},$subcatids);
    $main->setsubcatids($subcatids);
    $main->setparam("pro",($sitepro?2:1));
    $main->setparam("site_code",$template->site_code());
    $main->setparam("origineids",array(0));
    $artcount=$main->articlecount($main->subcatids,false);
    if($artcount>0) {
		$i=1;
		$menulibelle=$menuinfos["libelle"];
		$template->parts["articles"].=<<<HTML
		<div class="titrearticles"><span>
            <h2 class="color_bg">$menulibelle</h2></span>
	</div>
HTML;
		foreach($subcatids as $menusubcatid){
			$pcat=new categorie($DB_site,$menusubcatid,$template->langue);
			$pcat->setparam("origineids",array(0));
			$pcat->setparam("pro",($sitepro?2:1));
			$pcat->setparam("site_code",$template->site_code());
			$pcat->setsubcatids($menusubcatid);
			$catartcount=$pcat->articlecount($pcat->subcatids,false);
			if($catartcount>0){
				//if($i == 1) $template->parts["class_sup"] = 'first';
				if($i == 3){
					//$template->parts["class_sup"] = 'last';
					$i = 0;
				}
				$urlCat = "https://".$template->host.$template->mkurl("categorie", $pcat->catid,$pcat)."?origineids=0";
				$template->parts["articles"].=vignetteCat($pcat,"",$catartcount,$urlCat);
				$template->parts["class_sup"] = '';
				$i++;
			}
		}
		// $template->parts["articles"].="<div class=\"clear\">&nbsp;</div>";
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

include("barrefixe.php");

$template->evaltemplate("KdosPage","main");
$template->pageparams($host,$template->translate("/categorie/madeinfrance_metatitle"),$template->translate("/categorie/madeinfrance_metadescription"));

$template->out();

?>