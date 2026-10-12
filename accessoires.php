<?php

require ("global.php"); 
if(!isset($_GET['artid'])) exit;
$artid=intval($_GET['artid']);
$refs=explode("-",$_GET['ref']);
$req_cave=$DB_site->query("SELECT * FROM article WHERE artid='$artid'");
$aci=count($refs)-2;
$refacilyon=strtolower($refs[$aci])=="aci";
if($refacilyon){
    $reftype="artcode";
    $desc="";
    
}else{
    $reftype="artcode_eurocave";
    $desc=" DESC";
}

$req_rgm=$DB_site->query("SELECT * FROM article_rangement ar INNER JOIN article a ON(a.artid=ar.artid_rangement) INNER JOIN article_site a_s ON(a_s.artid=a.artid) WHERE ar.artid='$artid' AND a_s.active='1' ORDER BY a.catid, a_s.prix".$desc); 
if($artid=="" || $DB_site->num_rows($req_cave)==0){
  $TemplateListeAccessoires="<div class=\"logging\" style=\"margin:auto;\">
      <div class=\"logging_title\">".$template->translate("/site/erreur","F")." !</div>
      <div style=\"margin-top:20px;\">".$template->translate("/article/accessoires/article_existe_pas","F")."<br><br><a href=\"javascript:history.back();\"><img src=\"configurations/$host/images/templates/retour.jpg\"></a></div>
      </div>";
}else{
  $art=$DB_site->fetch_array($req_cave);
  $marque=$DB_site->query_first("SELECT * FROM marque INNER JOIN article_marque am USING(marqueid) WHERE am.artid='$artid'");
  $marqueid = $marque['marqueid'];

  $autreimg=$DB_site->query_first("SELECT * FROM articlephoto WHERE artid=$artid AND visible=1 AND (langue='' OR langue='".$template->langue."')");
  $autreid="";
  if($autreimg!="") $autreid="_".$autreimg['articlephotoid'];
  $template->parts["image"]="<img src=\"/images/".$artid.$autreid."_".($template->mobile?100:200)."_100.jpg\" alt=\"".$marque['libelle']." ".$art['gamme'.$template->langue]." ".$art['artcode']."\" class=\"img-responsive\">";
  $template->parts["logomarqueart"]="<img src=\"/marques/$marque[marqueid]_150_50.jpg\" alt=\"".$template->translate("/article/accessoires/accessoires")." $marque[libelle]\" width=\"100\">";
  $template->parts["artcode"]=$art['artcode'];
  $template->parts["libelleart"]=$art['libelle'.$template->langue];
  
  $rem_catid = '';
  $i=0;
  while($acc=$DB_site->fetch_array($req_rgm)){
    $template->parts["class_sup"] = '';
      if($rem_catid != $acc['catid']){
        $rem_catid = $acc['catid'];
        $i = 0;
      } 
      $template->parts["articlescomp"] = '';
      if($i == 0) $template->parts["class_sup"] = 'first';
      if($i == 3) $template->parts["class_sup"] = 'last';
      vignette($acc['artid_rangement'],"accessoires_comp");
      $articles[$acc['catid']].=$template->parts["articlescomp"];
      $i++;
      if($i == 4) $i = 0;
  }
  $template->parts["articlescomp"] = '';
  
} 

if(count($articles)){
    $template->parts["li"]="<ul>";
    $i=0;
    foreach($articles as $catid=>$vignettes){
      
      $cat=new categorie($DB_site,$catid,$template->langue);
//       vignette($artid,"accessoires_comp"); 
      
      $template->parts["articlescat"]=$vignettes;
      $template->parts["catlibelle"]=$cat->libelle;
      $template->parts["catid"]=$cat->catid;
      $template->evaltemplate("KdosCategorie","articles");
      $template->parts["li"].="<li><a href=\"#scat_".$cat->catid."\" class=\"color".($i+1)."\">".$cat->libelle."</a></li>";
      $i++;
      
    }
    $template->parts["li"].="</ul>";
    $template->evaltemplate("CategorieMenuCategorie","menucategorie");
}else 
    $template->parts["articles"]="<div class=\"cathaut\"><p>".$template->translate("/article/accessoires/pas_accessoire","F")."</p></div>";

$lm=$DB_site->query("SELECT marqueid,libelle FROM marque INNER JOIN display_marque USING(marqueid) ORDER BY libelle");
while($m=$DB_site->fetch_array($lm))
    $template->parts['listemarques'].="<option value=\"".$m['marqueid']."\">".$m['libelle']."</option>";
$template->evaltemplate("srcAccessoires","srccave");

$lienarticle=$lienarticle2="";
if($art['active']==1){
  $lienarticle=$template->parts["lienarticle"]="<a href=\"/".url_rewrite($art['libelle'.$template->langue])."-".url_rewrite(strtolower($marque['libelle']))."-art".$art['artid'].".htm\">";
  $lienarticle2=$template->parts["lienarticle2"]="</a>";
}

if($refacilyon){
    $template->parts["description_img"] = ""
        . "<div class=\"menucategorielogo\">"
            . $lienarticle.$template->parts["image"].$lienarticle2
            . "<h1 class=\"titre_article\">".$lienarticle.$art['libelle'.$template->langue].$lienarticle2."</h1>"
            . "<span><strong>Réf. :</strong> ".$art[$reftype]."</span>"
            . "<span class=\"tiret\"></span>"
            . $lienarticle.$template->parts["logomarqueart"].$lienarticle2
        . "</div>";
}else{
    $template->parts["description_img"] = ""
        . "<div class=\"menucategorielogo\">"
            . $lienarticle.$template->parts["image"].$lienarticle2
            . "<h1 class=\"titre_article\">".$lienarticle.$art['libelle'.$template->langue].$lienarticle2."</h1>"
            . "<span><strong>Réf. :</strong> ".$art[$reftype]."</span>"
            . "<span class=\"tiret\"></span>"
            . $lienarticle.$template->parts["logomarqueart"].$lienarticle2
        . "</div>";
}



if(!$template->mobile)
    $template->parts["description"]=$template->translate("/article/accessoires/description".intval($refacilyon));
else{
    $template->parts["pagetype"]="acc";
    $template->evaltemplate("AccessoiresArticle","article");
    $template->parts["articles"]=$template->parts["article"].implode("",$articles);
}

$template->parts["title"]=sprintf($template->translate("/article/accessoires/title".intval($refacilyon),"F"),$marque['libelle']." ".$art[$reftype]);

if(!$refacilyon && $art['refs_fabricant']!=""){
    $refs=explode("\n",$art['refs_fabricant']);
    $comp=array();
    foreach($refs as $ref)
	if($ref!="" && trim($ref)!=$art['artcode_eurocave']) $comp[]=trim($ref);
    if($comp!=array()) $template->parts["description"]="<span>".$template->translate("/article/accessoires/compatible","F")." ".implode(", ",$comp)."</span>. ".$template->parts["description"];
}



if($refacilyon){
    $lienaccmarque="<span property=\"itemListElement\" typeof=\"ListItem\"><a property=\"item\" typeof=\"WebPage\" href=\"/".$template->translate("/article/accessoires/accessoires","L")."/".$marque['url'.$template->langue]."-".$marque['marqueid'].".htm\"><span property=\"name\">".sprintf($template->translate("/article/accessoires/accessoires_marque","F"),"")."</span></a><meta property=\"position\" content=\"2\"></span>"
    ." > <span property=\"itemListElement\" typeof=\"ListItem\"><span property=\"name\">".$art['libelle'.$template->langue]." ".$marque['libelle']." ".$art[$reftype]."</span><meta property=\"position\" content=\"3\"></span>";
    $metatitle=sprintf($template->translate("/article/accessoires/accessoires_marque","F"),$marque['libelle']." ".$art[$reftype]." : ".$art['libelle'.$template->langue]);
}else{
    $lienaccmarque="<span property=\"itemListElement\" typeof=\"ListItem\"><a property=\"item\" typeof=\"WebPage\" href=\"/".$template->translate("/article/accessoires/accessoires","L")."/".$marque['url'.$template->langue]."-".$marque['marqueid'].".htm\"><span property=\"name\">".sprintf($template->translate("/article/accessoires/accessoires_marque","F"),$marque['type'.$template->langue])."</span></a><meta property=\"position\" content=\"2\"></span>"
    ." > <span property=\"itemListElement\" typeof=\"ListItem\"><span property=\"name\">".sprintf($template->translate("/article/accessoires/accessoires_marque","F"),$art[$reftype])."</span><meta property=\"position\" content=\"3\"></span>";
    $metatitle=sprintf($template->translate("/article/accessoires/accessoires_marque","F"),$art['libelle'.$template->langue]." ".$marque['libelle']." ".$art[$reftype]);
}

include("barrefixe.php");

//paramétrage des balises heflang en fr et en
foreach($template->sites() as $site_code=>$site){
    foreach($site['hosts'] as $url=>$deflangue){    
        if($site['actif'][$url]){
          if($marqueid != '' || $marqueid != null){
            $articles=$DB_site->query_first("SELECT artcode,artcode_eurocave,a.artid,refs_fabricant,libelle".$deflangue." AS libelle,(SELECT COUNT(lotid) FROM article_lot WHERE lotid=am.artid) AS lot FROM article_marque am INNER JOIN article a ON (am.artid=a.artid) WHERE am.marqueid=".$marqueid." AND a.artid =".$artid." ORDER BY a.artcode");
              $url2  = "/".$template->translate("/article/accessoires/accessoires","L")."/".url_rewrite($articles['libelle'])."-".strtolower($articles['artcode'])."-art".$articles['artid'].".html";
              $template->parts["alternate"].="<link rel=\"alternate\" hreflang=\"".$site['locales'][$deflangue]."\" href=\"https://www.".$url.$url2."\">\n";
          }
        }else{
          $template->parts["alternate"].="";
        }
    }
}

$metadesc=$metatitle.". ".$template->translate("/article/accessoires/metadescription","F");
$template->parts["h2footer"]="<h2>".$metatitle." ".$template->translate("/site/nom")."</h2>";
$template->parts["fil_ariane"]=" &gt; ".$lienaccmarque;
$template->pageparams($host,$metatitle." - ".$template->translate("/site/nom"),$metadesc);

$template->evaltemplate("Footer1","footer1");
$template->evaltemplate("KdosPage","main");
$template->out();


?>