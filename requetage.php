<?php

include ("global.php");
include("class/requetage.class.php");

$categorie=new categorie($DB_site);
$categorie->setparam("site_code",$template->site_code());
$req=new requetage($DB_site,$template->langue,$categorie);
$requetes=$req->analyse($_GET['url']);
if($requetes==array() || !$req->valid_url()){
    $template->redirect("/".$template->translate("/requetage/path")."/".$template->translate("/requetage/index_url"),301);
    exit;
}

$conf=new configurateur($DB_site);
$conf->requetage($categorie,$requetes);

// var_dump($requetes);

$template->parts["titreh1"]=ucfirst($req->titre());
foreach($requetes as $type=>$requete){
    $template->parts["reponse"]=ucfirst($req->titre_part($type));
    $template->evaltemplate("Req".ucfirst($type),"reponses");
}


$articles_selection=$categorie->articles("prix",true);
$categorie->setparam("bonsplans",array("selection"));
$articles_total=$categorie->articles("prix",true);
$nbtotal=count($articles_total);
$articles_diff=array_diff($articles_total,$articles_selection);
if($nbtotal==0) $req->update_cache($nbtotal);

$sep="";
foreach($articles_selection as $artid){
    if($c==6){
	$vignettes_selection.="<div class=\"hsep\"></div>";
	$sep="";
	$c=1;
    }
    $template->parts["artsim"]="";
    vignette($artid,"similaire");
    $vignettes_selection.=stripslashes($template->parts["artsim"]);
    $c++;
}
if($vignettes_selection!="") $vignettes_selection.="<div class=\"clear\"></div>";
$template->parts["vignettes_selection"]=$vignettes_selection;

$sep="";
foreach($articles_diff as $artid){
    if($c==6){
	$vignettes_total.="<div class=\"hsep\"></div>";
	$sep="";
	$c=1;
    }
    $template->parts["artsim"]="";
    vignette($artid,"similaire");
    $vignettes_total.=stripslashes($template->parts["artsim"]);
    $c++;
}
if($vignettes_total!="") $vignettes_total.="<div class=\"clear\"></div>";
$template->parts["vignettes_total"]=$vignettes_total;

$template->parts["nb_selection"]=count($articles_selection);
$template->parts["nb_total"]=count($articles_diff);
if($template->parts["nb_total"]==0) $template->parts["onglet_total_style"]="display:none";

$template->parts["preview"]="";
if($articles_selection!=array()){
    $last=count($articles_selection)-1;
    $last=round($last/2);
    $template->parts["preview"]="<img src=\"/images/".$articles_selection[$last]."_170_220.jpg\">";
}

$categorie=new categorie($DB_site);
$categorie->setparam("site_code",$template->site_code());
$reqlink=new requetage($DB_site,$template->langue,$categorie);
$i=0;
$liens=array();

while($i<11){
    $categorie=new categorie($DB_site);
    $categorie->setparam("site_code",$template->site_code());
    $reqlink->random_link($requetes['typeproduit']);
    $cache=$reqlink->get_cache();
    if($cache===false){
	$conf->requetage($categorie,$reqlink->requetes);
	$articles=$categorie->articles("prix",true);
	$cache=count($articles);
	$reqlink->update_cache($cache);
    }
    if($cache>0){
	$i++;
	$liens[$reqlink->url()]=ucfirst($reqlink->titre());
    }
}

$i=0;
$template->parts["liens"]="<ul class=\"liens\">";
foreach($liens as $lien=>$txt){
    $template->parts["liens"].="<li><a href=\"/".$template->translate("/requetage/path")."/".$lien."\">".$txt."</a></li>";
    if($i==round(count($liens)/2)-1) $template->parts["liens"].="</ul><ul class=\"liens\">";
    $i++;
}
$template->parts["liens"].="</ul>";

// $categorie=new categorie($DB_site);
// $categorie->setparam("site_code",$template->site_code());
// $conf=new configurateur($DB_site);
// $conf->requetage($categorie,$lrequetes);

$template->parts["js"]="<script language=\"javascript\" src=\"/javascripts/configurateur.js\"></script>";

$template->evaltemplate("RappelInst","rappelinst");
$template->evaltemplate("MiniContact","minicontact");
$articles_vus=$categorie->articles_vus($s,6);
$c=0;
if(count($articles_vus)>0){
    foreach($articles_vus as $artid){
	$c++;
	if($c>1) $TemplateAV.="<div class=\"sep\"></div>";
	$TemplateAV.=article_vu($artid);
    }
    $template->parts["artvus"]=$TemplateAV;
    $template->evaltemplate("ArticlesVusBar","articlesvus");
}
$template->parts["nbartvus"]=count($articles_vus);
$template->evaltemplate("ArticleBarDevis","articlebardevis");
$template->evaltemplate("ArticleBar","articlebar");

$template->evaltemplate("ContactArticle","contactarticle");

$template->parts["fil_ariane"]="<span typeof=\"v:Breadcrumb\"><a href=\"/\" rel=\"v:url\" property=\"v:title\">".$template->translate("/site/nom")."</a></span> &gt; ";
$template->parts["fil_ariane"].="<span typeof=\"v:Breadcrumb\"><a href=\"/".$template->translate("/requetage/path")."/".$template->translate("/requetage/index_url")."\">".$template->translate("/requetage/link")."</a> &gt; ";
$template->parts["fil_ariane"].="<span typeof=\"v:Breadcrumb\">".$template->parts["titreh1"]."</span>";
$metadesc=sprintf($template->translate("/requetage/metadescription"),$req->texte());
$template->pageparams($host,$template->parts["titreh1"]." - ".$template->translate("/site/nom"),$metadesc);
$template->evaltemplate("ReqPage","main");

$template->out();

?>