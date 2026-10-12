<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/global.php");

if(!isset($_GET['catid']) || !is_numeric($catid)) header("Location: /");

$menu_id = $_GET['catid'];

$oMenu = new categorie($DB_site,0,$template->langue);
$aCat = $oMenu->getMenu($menu_id);
$detailsCat = $oMenu->getMenus();
$detailsCat = $detailsCat[$menu_id];

$template->parts["title"]           = $detailsCat['libelle'];
$template->parts["description"]     = $detailsCat['description'];
if(file_exists($_SERVER['DOCUMENT_ROOT'] . "/configurations/".$host."/images/categories/menu/" . $detailsCat['idmenu'] . ".jpg")) $template->parts["description_img"] = "<img src=\"" . SITE_URL . "/images/categories/menu/" . $detailsCat['idmenu'] . ".jpg\" class=\"img img-responsive\" alt=\"" . $template->parts["title"] . " - " . $template->translate("/site/nom") . "\">";

$template->parts["articles"] = '';
$i = 1;
foreach($aCat as $la_categorie)
{ 
	$objCat = new categorie($DB_site,$la_categorie['catid'],$template->langue);

	$objCat->setsubcatids($la_categorie['catid']);
	$objCat->setparam("site_code", $template->site_code());
	$objCat->setparam("pro",($sitepro ? 2 : 1));

	$catartcount = $objCat->articlecount($objCat->subcatids,true);

	if($catartcount > 0)
	{
		if($i == 1) $template->parts["class_sup"] = '';
	    if($i == 3){
			$template->parts["class_sup"] = '';
			$i = 0;
	    }
	    $template->parts["articles"] .= vignetteCat($objCat,"",$catartcount,null,'classic');
	    $template->parts["class_sup"] = '';
	    $i++;
	}
}

foreach($template->sites() as $site_code=>$site){
    foreach($site['hosts'] as $url=>$deflangue){    
        if($site['actif'][$url]){
            $menu = new categorie($DB_site,0,$deflangue);
			$cat = $menu->getMenu($menu_id);
			$detCat = $menu->getMenus();
			$detCat = $detCat[$menu_id];
            $template->parts["alternate"].="<link rel=\"alternate\" hreflang=\"".$site['locales'][$deflangue]."\" href=\"https://www.".$url."/".url_rewrite($detCat['url']).".htm\">\n";
        }
    }
}
$pos=2;
$template->parts["fil_ariane"] = "&gt; <span property=\"itemListElement\" typeof=\"ListItem\"><a property=\"item\" typeof=\"WebPage\" href=\"/" . $detailsCat['url'] . ".htm\"><span property=\"name\">" . $detailsCat['libelle'] . "</span></a><meta property=\"position\" content=\"".$pos."\"></span>";

include("barrefixe.php");

$template->pageparams($host, $template->parts["title"] . " - " . $template->translate("/site/nom"), $template->parts["title"] . ". " . $template->parts["description"]);
$template->evaltemplate("CategorieMerePage","main");

$template->out();