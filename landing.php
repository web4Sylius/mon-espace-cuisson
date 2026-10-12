<?php

include("global.php");

if(isset($_GET['id']) || isset($_GET['url'])){
    if(isset($_GET['id'])) $sql="SELECT * FROM landing WHERE id=".intval($_GET['id']);
    if(isset($_GET['url'])) $sql="SELECT * FROM landing WHERE url".$template->langue."='".mysqli_real_escape_string($DB_site->link_id,$_GET['url'])."'";
    $landing=$DB_site->query_first($sql);
}
if((!isset($_GET['id']) && !isset($_GET['url'])) || $landing['id']==""){
    header("HTTP/1.1 301 Moved Permanently");
    header("location:/");
    header("Connection: close");
}

//paramétrage des balises heflang en fr et en
foreach($template->sites() as $site_code=>$site){
    foreach($site['hosts'] as $url=>$deflangue){    
        if($site['actif'][$url]){
          $url2  = $template->translate("/landing/index","L").$landing['url'.$deflangue].".html";
          $template->parts["alternate"].="<link rel=\"alternate\" hreflang=\"".$site['locales'][$deflangue]."\" href=\"https://www.".$url.$url2."\">\n";
        }
    }
}


$categorie=new categorie($DB_site);
$categorie->setparam("bonsplans","selection");
$categorie->setparam("site_code",$template->site_code());

if($landing['type']=="marque"){
    $indexlanding=$DB_site->query_first("SELECT * FROM landing WHERE type='index'");
    
    $clmarque=new marque($DB_site,$landing['landingid']);
    $categorie->setparam("marqueids",$clmarque->marqueid);
    
    $template->parts["fil_ariane"]=" &gt; <span property=\"itemListElement\" typeof=\"ListItem\"><a property=\"item\" typeof=\"WebPage\" href=\"".$template->translate("/landing/index","L").$indexlanding['url'.$template->langue].".html\"><span property=\"name\">".$indexlanding['libelle'.$template->langue]."</span></a><meta property=\"position\" content=\"2\"></span> &gt; <span property=\"itemListElement\" typeof=\"ListItem\"><span property=\"name\">".$clmarque->libelle."</span><meta property=\"position\" content=\"3\"></span>";
    $template->parts["description_img"]="<img class=\"img_desc\" style=\"max-width:180px;\" src=\"".$clmarque->logo(150,50)."\" alt=\"".trim($clmarque->type." ".$clmarque->libelle)."\">";
    
    $linkorigin="/".$clmarque->link;
}

if($landing['type']=="categorie"){
    $indexlanding=$DB_site->query_first("SELECT * FROM landing WHERE type='index'");
    
    $cat=new categorie($DB_site,$landing['landingid'],$template->langue);
    $cat->setparam("bonsplans","selection");
    $categorie->catid=$cat->catid;
    $template->parts["fil_ariane"]=" &gt; <span property=\"itemListElement\" typeof=\"ListItem\"><a property=\"item\" typeof=\"WebPage\" href=\"".$template->translate("/landing/index","L").$indexlanding['url'.$template->langue].".html\"><span property=\"name\">".$indexlanding['libelle'.$template->langue]."</span></a><meta property=\"position\" content=\"2\"></span> &gt; <span property=\"itemListElement\" typeof=\"ListItem\"><span property=\"name\">".$cat->libelle."</span><meta property=\"position\" content=\"3\"></span>";
    $template->parts["description_img"]="<img class=\"img_desc\" src=\"".$cat->image(230,134)."\" alt=\"img-description\">";
    //$linkorigin=$cat->url;
   
    $linkorigin=$landing['urlproduits'.$template->langue]; //****Mise a des url, Ancien code ligne 52****//
}

if($landing['type']=="index"){
    $template->parts["fil_ariane"]=" &gt; <span property=\"itemListElement\" typeof=\"ListItem\"><span property=\"name\">".$landing['libelle'.$template->langue]."</span><meta property=\"position\" content=\"2\"></span>";
    $template->parts["categorielist"]="<ul>";

    $req=$DB_site->query("SELECT landing.* FROM landing INNER JOIN categorie ON(catid=landingid) WHERE type='categorie' ORDER BY parentid,position");
    while($l=$DB_site->fetch_array($req)){
      if($l['url'.$template->langue] != '')
	      $template->parts["categorielist"].="<li><a href=\"".$template->translate("/landing/index","L").$l['url'.$template->langue].".html\">".ucfirst($l['libelle'.$template->langue])."</a></li>";
    }

    $template->parts["categorielist"].="</ul>";

    $template->parts["marquelist"]="<ul>";
    $req=$DB_site->query("SELECT landing.* FROM landing INNER JOIN marque ON(marqueid=landingid) WHERE type='marque' ORDER BY position");
    while($l=$DB_site->fetch_array($req)){
      if($l['url'.$template->langue] != '')
      	$template->parts["marquelist"].="<li><a href=\"".$template->translate("/landing/index","L").$l['url'.$template->langue].".html\">".ucfirst($l['libelle'.$template->langue])."</a></li>";
    }
    $template->parts["marquelist"].="</ul>";

    $template->evaltemplate("landingIndex","articles");

    
}

if($landing['type']=="slider"){
  $template->parts["fil_ariane"]=" &gt; <span property=\"itemListElement\" typeof=\"ListItem\"><span property=\"name\">".$landing['libelle'.$template->langue]."</span><meta property=\"position\" content=\"2\"></span>";
  $template->parts["description_img"]="<img class=\"img_desc\" src=\"/marques/0_150_50.jpg\" alt=\"\">";
}

$imgpath="configurations/".$host."/images/landing/".$template->langue."/".$landing['id'].".jpg";
$img="";
if(!$template->mobile && file_exists($imgpath)){
  //$img="<img src=\"/".$imgpath."\" alt=\"".$landing['imgtext'.$template->langue]."\" class=\"img690\">";
  //if($landing['imglink'.$template->langue]!="") $img="<a href=\"".$landing['imglink'.$template->langue]."\">".$img."</a>";
}



if($landing['linktxt'.$template->langue]!="")
    $template->parts["articles"]="
    <div class=\"linktxt\">
      <div class=\"liseret\" style=\"background-color:#716A64;\"><h2 class=\"color_bg\"><a href=\"".$linkorigin."\">".$landing['linktxt'.$template->langue]."</a></h2></div>
    </div>
    <div class=\"clear\"></div>";
    
$cdclist="";
$remarketing=array("libelle"=>array(),"price"=>array(),"category"=>array());
/*
if($landing['produits']!=""){
    $cdclist=explode(",",$landing['produits']);
    $p=0;
    foreach($cdclist as $cdcid){
      $p++;
      if($p==5) $template->parts["articles"].=$link;
      $template->parts["articles"].=vignette($cdcid);
      
      $article=$DB_site->query_first("SELECT c.libelle".$template->langue." AS categorie, a.libelle".$template->langue." AS libelle FROM categorie c INNER JOIN article a USING(catid) WHERE artid=".$cdcid);
      $remarketing['libelle'][]=utf8_encode(html_entity_decode($article['libelle']));
      $remarketing['category'][]=utf8_encode(html_entity_decode($article['categorie']));
      $remarketing['price'][]=article_price($cdcid);
    }

    if($p<5) $template->parts["articles"].=$link;
}
*/

$categorie->subcategories();

if($landing['produits']!=""){
      $artidlist=explode(",",$landing['produits']);
      $cpt=0;
      foreach($artidlist as $artid){
	  $prix = 1000 * round(article_price($artid,true,$template->site_code())) + $cpt;
	  $tri[$prix]=$artid;
	  $cpt++;
      }
      ksort($tri);
      $cdclist=array_slice($tri,0,20);
}else{
      $order=$categorie->sqlorder("ventes",time()-60*86400,20);
      $cdclist=$categorie->articles($order);
}
//on verifie si l'article est commandable.
$cdclist2=array();
foreach($cdclist as $art){
  $article = $DB_site->query_first("select commandable FROM article WHERE artid='".$art."'");
  if($article['commandable'] == "1")
      $cdclist2[] = $art;
}
$cdclist=array();
//on limite a 4 l'affichage de produits sur la page
if(count($cdclist2)>4)
  $count = 4;
else
  $count = count($cdclist2)-1 ;

for($i=0;$i<$count;$i++){
  $cdclist[$i]=$cdclist2[$i];
}


$c=1;
foreach($cdclist as $cdcid) {

      $class2="";
      //if($c==1) $class2="first";
      /*if($c==4){
	     $c=0;
	     $class2="last";
      }*/
      vignette($cdcid,"categorie",$class2);
      $c++;
      
      $article=$DB_site->query_first("SELECT c.libelle".$template->langue." AS categorie, a.libelle".$template->langue." AS libelle FROM categorie c INNER JOIN article a USING(catid) WHERE artid=".$cdcid);
      $remarketing['libelle'][]=utf8_encode(html_entity_decode($article['libelle'],ENT_COMPAT,"ISO-8859-15"));
      $remarketing['category'][]=utf8_encode(html_entity_decode($article['categorie'],ENT_COMPAT,"ISO-8859-15"));
      $remarketing['price'][]=article_price($cdcid);
}

	   

if($landing['produits']!=""){
    if(count($tri)>4)
	    $cdclist=array_slice($tri,20);
    else
	    $cdclist=array();
}else{
    
    $order=$categorie->sqlorder("articles_vus",time()-60*86400,20,$cdclist);
    $cdclist=$categorie->articles($order);
}

$article_stat=new article_stat($DB_site,$template,$cdclist);
$article_stat_vu=new article_stat($DB_site,$template,$cdclist);
$link="
	  ".$img."
	  <div class=\"description_bloc\">
	      ".$article_stat_vu->replace($template->short_links($landing['link'.$template->langue]))."
	  </div>";

//on verifie si l'article est commandable.
$cdclist2=array();
foreach($cdclist as $art){
  $article = $DB_site->query_first("select commandable FROM article WHERE artid='".$art."'");
  if($article['commandable'] == "1")
      $cdclist2[] = $art;
}

$cdclist=array();
if(count($cdclist2)>4)
  $count = 4;
else
  $count = count($cdclist2)-1 ;

for($i=0;$i<$count;$i++){
  $cdclist[$i]=$cdclist2[$i];
}

	
	
$c=1;
foreach($cdclist as $cdcid) {
      $class2="";
      //if($c==1) $class2="first";
      /*if($c==4){
	       $c=0;
	       $class2="last";
      }*/
      vignette($cdcid,"categorie",$class2);
      $c++;
      
      $article=$DB_site->query_first("SELECT c.libelle".$template->langue." AS categorie, a.libelle".$template->langue." AS libelle, a.commandable FROM categorie c INNER JOIN article a USING(catid) WHERE artid=".$cdcid);
      $remarketing['libelle'][]=utf8_encode(html_entity_decode($article['libelle'],ENT_COMPAT,"ISO-8859-15"));
      $remarketing['category'][]=utf8_encode(html_entity_decode($article['categorie'],ENT_COMPAT,"ISO-8859-15"));
      $remarketing['price'][]=article_price($cdcid);
}
//$template->parts["articles"].="<div class=\"clear\"></div>";
$template->parts["articles"].=$link;


$template->parts["description"]=$article_stat->replace($template->short_links($landing['description'.$template->langue]));
$template->parts["pagetitle"]=$landing['libelle'.$template->langue];


$template->parts["remarketing"]=google_remarketing($cdclist,"category",'',$remarketing['category'],$remarketing['libelle'],$remarketing['price']);

$encarts=encarts($template->site_code(),$template->langue,"landing",$landing['id']);
$i=1;
foreach($encarts as $encart){
  $template->parts['marginright0'] = '';
    $template->parts['boots'] = 'encarts_width';
    if($i==3) $template->parts['boots'] .= " hidden-xs hidden-sm";
    if($i==4){ 
      $template->parts['marginright0'] = 'margin-right: 0;';
      $template->parts['boots'] .= " hidden-xs hidden-sm hidden-md encarts_disp";
    }
  $template->evaltemplate($encart,"encarts");
  $i++;
}
    
    
include("barrefixe.php");

$template->evaltemplate("LandingPage","main");
$template->pageparams($template->parts["host"],$landing['ref_title'.$template->langue],$landing['ref_description'.$template->langue]);

$template->out();

?>