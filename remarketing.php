<?php

require ("global.php");

$template->evaltemplate("Remarketing","content");

$template->parts["title"]="Besoin d'aide ou de conseils ?";

$template->evaltemplate("RappelRemarketing","srccave");
$template->evaltemplate("Conseil","conseil");

$template->evaltemplate("RappelInst","rappelinst");
$template->evaltemplate("MiniContact","minicontact");
$articles_vus=$maincat->articles_vus($s,6);
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
$template->evaltemplate("ArticleBar","articlebar");

$template->parts["fil"]="<span typeof=\"v:Breadcrumb\"><a href=\"/\" rel=\"v:url\" property=\"v:title\">".$template->translate("/site/nom")."</a></span> &gt; ".$template->parts["title"];
$template->pageparams($host,$template->parts["title"]." - ".$template->translate("/site/nom"),$template->translate("/site/meta_description"));
$template->evaltemplate("InstituPage","main");

$template->out();

?>