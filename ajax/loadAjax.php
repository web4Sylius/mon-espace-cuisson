<?php
require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

$t=array();

////*******************************************************CONSENTEMENT COOKIES*********************************************************************/////
$cookieconsent=false;
if(isset($_COOKIE['cookieconsent'])){
	$rep = cookie_rep($_COOKIE['cookieconsent']);
	$group1 = cookie_repgroup($_COOKIE['cookieconsent'],'group1');
	$group2 = cookie_repgroup($_COOKIE['cookieconsent'],'group2');

	switch ($rep) {
        case 'all':
            $cookieconsent = true;
        break;
        case 'none':
            $cookieconsent = false;
        break;
        case 'param':
        	if(intval($group1) == 0){
                $group1=false;
            }else{
                $group1=true;
            }
            if(intval($group2) == 0){
                $group2=false;
            }else{
                $group2=true;
            }
            break;   
    }
}else{
	$cookieconsent = true;
}

// modif de benoit : on oblige le consent
$cookieconsent = true;

if(!isset($_COOKIE['cookieconsent'])){
	// $template->evaltemplate("BarreCookies","barrefixe");
	// $t['barrecookies'] = stripslashes($template->parts['barrefixe']);
}
if($cookieconsent){
	$template->evaltemplate("Pixel_FB", "pixel_fb");
 	$template->evaltemplate("GoogleGclid","googleGclid");
 	//$template->evaltemplate("GoogleTM","googleTM");
 	//$template->evaltemplate("GoogleTM2","googleTM2");
 	$template->evaltemplate("Analytics","analytics"); 
 	// $t['analytics'] = stripslashes($template->parts['analytics']);
 	//$t['analytics_gtm'] = "https://www.googletagmanager.com/gtag/js?id=AW-1035437288";
 	//$t['gtm'] = stripslashes($template->parts['googleTM']);
 	//$t['gtm2'] = stripslashes($template->parts['googleTM2']);
 	$t['gclid'] = stripslashes($template->parts['googleGclid']);
 	// $t['nextperformance'] = stripslashes($template->parts['nextperformance']);
 	// $t['remarketing'] = stripslashes($template->parts['remarketing']);
 	// $t['pixel_fb'] = stripslashes($template->parts['pixel_fb']);
}else{
    if($group1){
       	$template->evaltemplate("GoogleGclid","googleGclid");
       	//$template->evaltemplate("GoogleTM","googleTM");
       	//$template->evaltemplate("GoogleTM2","googleTM2");
       	$template->evaltemplate("Analytics","analytics"); 
       	// $t['analytics'] = stripslashes($template->parts['analytics']);
       	//$t['analytics_gtm'] = "https://www.googletagmanager.com/gtag/js?id=AW-1035437288";
       	//$t['gtm'] = stripslashes($template->parts['googleTM']);
       	//$t['gtm2'] = stripslashes($template->parts['googleTM2']);
       	$t['gclid'] = stripslashes($template->parts['googleGclid']);
       	// $t['nextperformance'] = stripslashes($template->parts['nextperformance']);
       	// $t['remarketing'] = stripslashes($template->parts['remarketing']);
    }
    if($group2){
       $template->evaltemplate("Pixel_FB", "pixel_fb"); 
       // $t['pixel_fb'] = stripslashes($template->parts['pixel_fb']);
    }
}


/////********************************************************** POPUP COMPTE ***********************************************************************////////
if(!isset($_COOKIE['userid'])){
	$template->parts['gclid1'] = $_GET['gclid'];
	$template->evaltemplate("ComptePopup","popup_compte");
	$t['popup_compte'] = stripslashes($template->parts['popup_compte']);
}


/////********************************************************** NB ARTICLES PANIER ****************************************************************////////
$t['nb_articles_panier']=$nb_articles_panier;


/////************************************************************ POPUP PANIER ********************************************************************////////
if(!is_null($nb_articles_panier)){
	$template->evaltemplate("PopupPanier","popup_panier");
	$t['popup_panier']=stripslashes($template->parts['popup_panier']);
	}


//on envoi les données

//if(ESPACE == 'preprod') $t = array_map("utf8_encode", $t);
echo json_encode($t);