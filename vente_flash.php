<?php

include("global.php");

$urltrad=array("EN"=>"/flash_sale.html","FR"=>"/vente_flash.html","SP"=>"/venta_flash.html");
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
$vf=$DB_site->query_first("SELECT * FROM vente_flash WHERE site_code='".$template->site_code()."'");
// $vf=$DB_site->query_first("SELECT * FROM vente_flash WHERE 1");
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

    //$jsdate="var d=new Date(".date("Y",$vf['au']).",".(date("m",$vf['au'])-1).",".date("d",$vf['au']).",".date("H",$vf['au']).",".date("i",$vf['au']).",0);";
    $template->parts["reassurance"]="<div class=\"reassurance slide\"><div class=\"".$template->langue."\"></div></div><div class=\"clear\"></div>";
    //$template->evaltemplate("VFCompteurNew","chrono");
    $TemplateTxt=$vf['text1'.$template->langue];
    $template->parts["description_type"]="description_2t";

    $c=1;
	/*
    $vfarticles=$DB_site->query("SELECT a.artid, IF(a.typearticleid = 1, 0, 1) as triType, IF(am.marqueid = 82, 1, 0) as triMarque FROM vente_flash_article vfa INNER JOIN article a USING(artcode) INNER JOIN article_site a_s USING(artid) INNER JOIN article_marque am ON (am.artid = a.artid) WHERE vfid=".$vf['id']." AND a_s.site_code='".$template->site_code()."' AND a_s.active=1 ORDER BY triType, triMarque, vfa.prix");
	*/
	$vfarticles=$DB_site->query("SELECT a.artid,a.ventes, IF(a.typearticleid = 1, 0, 1) as triType, IF(am.marqueid = 82, 1, 0) as triMarque FROM vente_flash_article vfa INNER JOIN article a USING(artcode) INNER JOIN article_site a_s USING(artid) INNER JOIN article_marque am ON (am.artid = a.artid) WHERE vfid=".$vf['id']." AND a_s.site_code='".$template->site_code()."' AND a_s.active=1 ORDER BY a.ventes DESC ");
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
    $afficheTemplate="VFPageActive";
    $template->parts["text0"] = $vf['text0'.$template->langue];
    $template->evaltemplate("VFCompteurNew", "compteur");
}else{
	$afficheTemplate = "VFPage";
}    
/*}elseif($now<$vf['du']){
    $jsdate="d=new Date(".date("Y",$vf['du']).",".(date("m",$vf['du'])-1).",".date("d",$vf['du']).",".date("H",$vf['du']).",".date("i",$vf['du']).",0);";
    $template->parts["reassurance"]="<div class=\"reassurance slide\"><div class=\"".$template->langue."\"></div></div><div class=\"clear\"></div>";
//     $template->evaltemplate("VFCompteur","chrono");
    $TemplateTxt=$vf['text0'.$template->langue];
    $template->parts["description_type"]="description_full";
}elseif($now>$vf['au']){
    $TemplateTxt=$vf['text2'.$template->langue];
    $template->parts["description_type"]="description_full";
}*/

$template->parts["description"]=$TemplateTxt;

// var dn=new Date(".date("Y").",".(date("m")-1).",".date("d").",".date("H").",".date("i").",0);

/*$template->parts["jsvf"]="<script language=\"javascript\">
$jsdate
var dn=new Date();
var dstr={days:\"".$template->translate("/article/champs/jours")."\",hours:\"".$template->translate("/article/champs/heures")."\",minutes:\"".$template->translate("/article/champs/minutes")."\",seconds:\"".$template->translate("/article/champs/secondes")."\"};
if(typeof conversion_seconde_heure != 'function'){
	window.conversion_seconde_heure = function(time){ 
		var reste=time;
		var result='';
				
		var nbJours=Math.floor(reste/(3600*24));
		reste -= nbJours*24*3600;
					
		var nbHours=Math.floor(reste/3600);
		reste -= nbHours*3600;
					
		var nbMinutes=Math.floor(reste/60);
		reste -= nbMinutes*60;
	 
		var nbSeconds=reste;
	 
		if (nbJours>0) result=result+nbJours+dstr.days+' ';
		if (nbHours>0)result=result+nbHours+dstr.hours+' ';
		if (nbMinutes>0) result=result+nbMinutes+dstr.minutes+' ';
		if (nbSeconds>0) result=result+Math.round(nbSeconds)+dstr.seconds+' ';
					
		return result;
	};
}
if(typeof upddecompte != 'function'){
   window.upddecompte = function(){
	    if(!document.getElementById('vfdecompte'))
			return;
		dn.setTime(dn.getTime()+1000);
		var diff=d.getTime()-dn.getTime();
		if(diff<=1000)
			location.reload();
		document.getElementById('vfdecompte').innerHTML=conversion_seconde_heure(diff/1000);
		setTimeout('upddecompte()',1000);
   };
}

upddecompte();
</script>";
*/

include("barrefixe.php");

$template->pageparams($host,$vf['ref_title'.$template->langue],$vf['ref_description'.$template->langue]);
$template->evaltemplate($afficheTemplate,"main");

$template->out();

?>
