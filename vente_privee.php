<?php

include("global.php");

$urltrad=array("EN"=>"/private_sale.html","FR"=>"/vente_privee.html");
if($urltrad[$template->langue]!=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH)){
    header('HTTP/1.1 301 Moved Permanently', false, 301);
    header('Location: '.$urltrad[$template->langue]);
    exit();
}
//ici on fais afficher les balise hreflang pour fr et en
foreach($template->sites() as $site_code=>$site){
    foreach($site['hosts'] as $url=>$deflangue){
    if($site['actif'][$url])
        $template->parts["alternate"].="<link rel=\"alternate\" hreflang=\"".$site['locales'][$deflangue]."\" href=\"https://www.".$url.$urltrad[$deflangue]."\">\n";
    }
}

$template->parts['js_page'] .= "<script src=\"/javascripts/TweenMax.min.js\" defer></script>";

$now=time();
$vf=$DB_site->query_first("SELECT * FROM vente_privee WHERE site_code='".$template->site_code()."'");

if($vf['du']<$now && $vf['au']>$now){
	//Calcul des heures minutes secondes pour le timer
	$reste = (intval($vf['au']) - time());
	$nbjours = floor($reste/(3600*24));
	$reste = $reste - $nbjours*24*3600;
	$nbhours = floor($reste/3600);
	$reste -= $nbhours*3600;
	$nbminutes = floor($reste/60);
	$reste -=$nbminutes * 60;
	$nbsecondes = $reste ;

	//if($nbjours>0)  $nbhours = $nbhours + ($nbjours*24);
	//affichage des jours
	if(strlen(strval($nbjours)) == 2){
		$template->parts['jour1'] = intval(substr(strval($nbjours), 0,1));
		$template->parts['jour2'] = intval(substr(strval($nbjours), 1,2));
	}else{
		$template->parts['jour1'] = 0;
		$template->parts['jour2'] = $nbjours;
		if($nbjours == 0){ 
			$template->parts['jourVisible'] = "style=\"display:none;\"";
			$template->parts['secVisible'] = "style=\"display:block;\"";
		}else{
			$template->parts["styleminute"] = "margincdwn";
		}
	}

	//affichage des heures
	if(strlen(strval($nbhours)) == 2){
		$template->parts['heure1'] = intval(substr(strval($nbhours), 0,1));
		$template->parts['heure2'] = intval(substr(strval($nbhours), 1,2));
	}else{
		$template->parts['heure1'] = 0;
		$template->parts['heure2'] = $nbhours;
	}

	//affichage des minutes 
	if(strlen(strval($nbminutes)) == 2){
		$template->parts['minutes1'] = intval(substr(strval($nbminutes), 0,1));
		$template->parts['minutes2'] = intval(substr(strval($nbminutes), 1,2));
	}else{
		$template->parts['minutes1'] = 0;
		$template->parts['minutes2'] = $nbminutes;
	}

	//affichage des secondes 
	if(strlen(strval($nbsecondes)) == 2){
		$template->parts['secondes1'] = intval(substr(strval($nbsecondes), 0,1));
		$template->parts['secondes2'] = intval(substr(strval($nbsecondes), 1,2));
	}else{
		$template->parts['secondes1'] = 0;
		$template->parts['secondes2'] = $nbsecondes;
	}
	// valeures pour initialisé le decompte.
	$template->parts['jour'] = $nbjours;
	$template->parts['heure'] = $nbhours;
	$template->parts['minutes'] = $nbminutes;
	$template->parts['secondes'] = $nbsecondes;

    $template->parts["reassurance"]="<div class=\"reassurance slide\"><div class=\"".$template->langue."\"></div></div><div class=\"clear\"></div>";
    $TemplateTxt=$vf['text1'.$template->langue];
    $template->parts["description_type"]="description_2t";

    $c=1;
	$vfarticles=$DB_site->query("SELECT a.artid,a.ventes FROM vente_privee_article vfa INNER JOIN article a USING(artcode) INNER JOIN article_site a_s USING(artid) INNER JOIN article_marque am ON (am.artid = a.artid) WHERE vfid=".$vf['id']." AND a_s.site_code='base' AND a_s.active=1 ORDER BY am.marqueid, a.artcode");
    while($vfarticle=$DB_site->fetch_array($vfarticles)){
	  $class2="";
	  if($c==1) $class2="first";
	  if($c==4){
	      $c=0;
	      $class2="last";
	  }
	  vignette($vfarticle['artid'],"categorie",$class2)  ;
	  $c++;
    }
    $afficheTemplate="VPPageActive";
    $template->parts["text0"] = $vf['text0'.$template->langue];
    $template->evaltemplate("VPCompteurNew", "compteur");
}else{
	$afficheTemplate = "VPPage";
}

$template->parts["description"]=$TemplateTxt;

include("barrefixe.php");

$template->pageparams($host,$vf['ref_title'.$template->langue],$vf['ref_description'.$template->langue]);
$template->evaltemplate($afficheTemplate,"main");

$template->out();

?>
