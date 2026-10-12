<?php

include("global.php");


$template->parts["fil_ariane"]="<span typeof=\"v:Breadcrumb\"><a href=\"/\" rel=\"v:url\" property=\"v:title\">".$template->translate("/site/nom")."</a></span> &gt; ";

$promolanding=$DB_site->query_first("SELECT libelle".$template->langue." AS libelle, description".$template->langue." AS description,
ref_title".$template->langue." AS ref_title, ref_description".$template->langue." AS ref_description,
link".$template->langue." AS link,produits FROM landing WHERE type='promos'");

$order="cat";
$template->parts["title"]=$promolanding['libelle'];
$template->parts["description"]=$promolanding['description'];
$template->parts["linktxt"]=$promolanding['link'];
$template->parts["fil_ariane"].=$promolanding['libelle'];


$remartids=array();
$remarketing=array("libelle"=>array(),"price"=>array(),"category"=>array());
if($promolanding['produits']!=""){
    $cdclist=explode(",",$promolanding['produits']);
    foreach($cdclist as $cdcid){
      $template->parts["articles"].=vignette($cdcid,"promo");
      
      $article=$DB_site->query_first("SELECT c.libelle".$template->langue." AS categorie, a.libelle".$template->langue." AS libelle FROM categorie c INNER JOIN article a USING(catid) WHERE artid=".$cdcid);
      $remarketing['libelle'][]=utf8_encode(html_entity_decode($article['libelle'],ENT_COMPAT,"ISO-8859-15"));
      $remarketing['category'][]=utf8_encode(html_entity_decode($article['categorie'],ENT_COMPAT,"ISO-8859-15"));
      $remarketing['price'][]=article_price($artid);
    }
}

$template->parts["remarketing"]=google_remarketing($remartids,"category",'',$remarketing['category'],$remarketing['libelle'],$remarketing['price']);


include("barrefixe.php");

$template->evaltemplate("promoPage","main");
$template->pageparams($host,$promolanding['ref_title'],$promolanding['ref_description']);

$template->out();

?>