<?php

$oCategorie = new categorie($DB_site, 0, $template->langue);
$oCategorie->setparam("pro",($sitepro?2:1));
$oCategorie->setparam("site_code",$template->site_code());

$Menus=$oCategorie->getMenus();
foreach($Menus as $num => $le_menu){

    $template->parts["menunum"]     = $num;
    $template->parts["menudisplay"] = $le_menu['libelle'];
    $template->parts["listesousmenus"]="";

    $tabMenu = $oCategorie->getMenu($num);
    $nbproduits=0;
    
    if(!empty($tabMenu)){
	foreach($tabMenu as $le_menu2){
	    $oCatMere = new categorie($DB_site, $le_menu2['catid'], $template->langue);
	    $template->parts["sousmenuid"]      = $le_menu2['catid'];
	    $template->parts["sousmenulibelle"] = $le_menu2['libelle'];
	    $categorie_url  = $template->mkurl("categorie", $oCatMere->catid, $oCatMere);
	    $aProduits = $oCategorie->getArticles($le_menu2['catid']);
	    $nb=is_array($aProduits) ? count($aProduits) : 0;
	    $nbproduits+=$nb;
	    if($nb>0){
		$template->parts["listesousmenus"].=<<<HTML
		   <a href="$categorie_url" class="item">
		      <span>$le_menu2[libelle]</span>
		      <i class="fa fa-chevron-right" aria-hidden="true"></i>
		  </a>
HTML;
	    }
	}
	$template->evaltemplate("MenuSecondaire","sousmenus");
    }

    if($nbproduits>0){
	$class=$num%2==0?"right":"left";
	$template->parts["menuscategories"].=<<<HTML
	<a href="javascript:void(0)" onclick="toggleSousmenu($num)" class="item $class">
	    <i></i>
	    <span>$le_menu[libelle]</span>
	    <i class="fa fa-chevron-right" aria-hidden="true"></i>
	</a>
HTML;
    }
}

if($vfarticle!="") $template->evaltemplate("MenuVF","menu_vf");

$template->evaltemplate("Menu","menu");

$template->parts["sousmenus"]=preg_replace("/sous-menu-(\d)/","menu-index-\$1",$template->parts["sousmenus"]);
$template->parts["menuscategories"]=preg_replace("/toggleSousmenu\((\d)\)/","toggleIndexMenu(\$1)",$template->parts["menuscategories"]);

?>