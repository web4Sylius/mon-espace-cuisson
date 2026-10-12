<?php

require ("global.php");


$marque=new marque($DB_site,intval($_GET['mid']),$template->langue);
if($marque->marqueid!=$_GET['mid'])
    $template->redirect("/",301);

$pageurl="/".$template->translate("/article/accessoires/accessoires","L")."/".$marque->link;
if($_SERVER['REQUEST_URI']!=$pageurl){
    $template->redirect($pageurl,301);
}

$site_code=$template->site_code();

$sql="SELECT ar.artid, a.artcode, a.artcode_eurocave, (SELECT COUNT(artid) FROM article_lot WHERE lotid=ar.artid) AS lot FROM article_rangement ar 
INNER JOIN article a USING(artid) 
INNER JOIN article_marque USING(artid) 
INNER JOIN article_site a_s ON(a_s.artid=ar.artid_rangement)
INNER JOIN article_site ac_s ON(ac_s.artid=ar.artid)
WHERE a_s.active=1 AND ac_s.active IN (0,1,3) AND a_s.site_code='".$site_code."' AND marqueid=".$marque->marqueid." AND (SELECT COUNT(artid) FROM secondchoix WHERE artid=ar.artid)=0
GROUP BY ar.artid";

$vignlist=array();
$req=$DB_site->query($sql);
while($art=$DB_site->fetch_array($req)){
    $vignlist[$art['artcode']]=$art;
    if($art['lot']==0) $vignlist[$art['artcode_eurocave']]=$art;
}

ksort($vignlist);

$c=1;
$sep="";
$template->parts["articles"]="";
foreach($vignlist as $art){
 //   switch($c){
//	case 1:
//	$template->parts["class2"]="first";
//	$c++;
//	break;
	
//	case 6:
//	$template->parts["class2"]="last";
//	$c=1;
//	break;
	
//	default:
//	$template->parts["class2"]="";
//	$c++;
//    }
	$template->parts["class2"]="block-justified";
    $template->parts["articles"].=vignetteAcc($art['artid'],$art['lot']==0?"artcode_eurocave":"");
}


include("barrefixe.php");

$template->parts["pagetype"]="acc";
$template->parts["h2footer"]="<h2>".sprintf($template->translate("/article/accessoires/marque_baseline","F"),preg_replace("/(".$marque->libelle.")/i",$marque->libelle,$marque->type))."</h2>";
$template->parts["fil_ariane"]=" &gt; <span property=\"itemListElement\" typeof=\"ListItem\"><span property=\"name\" >".sprintf($template->translate("/article/accessoires/accessoires_marque","F"),$marque->type)."</span><meta property=\"position\" content=\"2\"></span>";
$template->parts["marque"]=$marque->libelle;
$template->parts["description_img"]="<img src=\"".$marque->logo(150,50)."\" alt=\"".trim($marque->type." ".$marque->libelle)."\" class=\"img img-responsive\">";
    
$template->evaltemplate("AccessoiresMarque","main");
$metatitle=ucfirst(sprintf($template->translate("/article/accessoires/accessoires_marque","F"),ucwords($marque->type)));
$metadesc=$metatitle." : ".$template->translate("/article/accessoires/marque_metadescription","F");
$template->pageparams($host,$metatitle." - ".$template->translate("/site/nom"),$metadesc);
$template->out();

?>