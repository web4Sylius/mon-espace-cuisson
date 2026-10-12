<?php

require ("global.php");

$showroom=new showroom($DB_site,$template->langue);
$showroom->load(1);

$template->parts["fil_ariane"]=" &gt; <span property=\"itemListElement\" typeof=\"ListItem\"><span property=\"name\">".$showroom->title."</span><meta property=\"position\" content=\"2\"></span>";
$template->parts["title"]=$showroom->title;
foreach($template->sites() as $site_code=>$site){
    foreach($site['hosts'] as $url=>$deflangue){
	if($site['actif'][$url])
	    $template->parts["alternate"].="<link rel=\"alternate\" hreflang=\"".$site['locales'][$deflangue]."\" href=\"https://www.".$url.$_SERVER['REQUEST_URI']."\">\n";
    }
}


$groups=$showroom->articles(true);
$artcat=array();
foreach($groups as $catid=>$sr_articles){
    $categorie=new categorie($DB_site,$catid,$template->langue);
    $position=$categorie->position;

    $listarticles=array();
    ksort($sr_articles);
    $c=0;
    foreach($sr_articles as $sr_article){
	vignette($sr_article->artid,"categorie");
	$template->parts["description"]=$sr_article->description;

	$linklist=array();
	$template->parts["showlinks"]="style=\"display:none;\"";
	if($sr_article->links!=array()) {
	    foreach($sr_article->links as $linkartid){
	      $article=$DB_site->query_first("SELECT a.libelle".$template->langue." ,a.artcode,a.active, m.libelle as marque FROM article a INNER JOIN article_marque am USING(artid) INNER JOIN marque m ON m.marqueid=am.marqueid WHERE a.artid=".intval($linkartid));
	      if($article['active']==1)
		  $linklist[]="<div class=\"artcompat\">
		    <a href=\"".$template->mkurl('article',$linkartid,$article)."\">
		      <img src=\"images/".$linkartid."_50_50.jpg\" alt=\"".$article['libelle'.$template->langue]."\">
		      <br>".$article['artcode'].
		    "</a>
		  </div>";
// 		  $linklist[]="<a href=\"/art,".$linkartid.",".url_rewrite($article['libelle']).".htm\">".$article['artcode']."</a>";
	    }
	    if(count($linklist)>0) {
	      $template->parts["links"]=implode("",array_slice($linklist,0,12));
	      $template->parts["showlinks"]="";
	    }
	}
	
	$template->evaltemplate("sr_article","sr_article".$sr_article->artid);
	if($c%2==1) $template->parts["sr_article".$sr_article->artid].="<div class=\"clear\"></div>";
	$template->parts["articles"]="";
	$listarticles[]=$template->parts["sr_article".$sr_article->artid];
	$c++;
    }
    $artcat[$position].="<div class=\"sr_bloc\">
      <div class=\"linktxt\"><div class=\"liseret\" style=\"background-color:#716A64;\"><h2 class=\"color_bg\">".$categorie->libelle."</h2></div></div>"
      .implode("",$listarticles).
      "
    </div>";
}
    
ksort($artcat);
$template->parts["articles"]=implode("",$artcat);
$showroom->contenu=str_replace("[articles]",$articles,$showroom->contenu);
$template->parts["description"]=$showroom->contenu;

$template->pageparams($host,$showroom->metatitle,$showroom->metadescription);
$template->evaltemplate("ShowroomPage","main");

include("barrefixe.php");

$template->parts["remarketing"]=google_remarketing('',"siteview",'');

$template->out();

?>
