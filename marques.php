<?php


include("global.php");

if($_SERVER['REQUEST_URI']=="/marques.php") $template->redirect($template->translate("/site/url_marques"),301);

$cols=4;
$lm=$DB_site->query("SELECT marqueid,libelle FROM marque INNER JOIN display_marque USING(marqueid) ORDER BY libelle");
while($m=$DB_site->fetch_array($lm))
    $template->parts['listemarques'].="<option value=\"".$m['marqueid']."\">".$m['libelle']."</option>";

$m=new marque($DB_site,0,$template->langue);
$marques=$m->listall("libelle",($sitepro?2:1),$template->site_code());

$c=1;
foreach($marques as $marque){
    $template->parts["class2"]="";
    $template->parts["marquelink"]=$marque->link;
    $template->parts["marquelibelle"]=$marque->libelle;
    $template->parts["imglogo"]="<img src=\"".$marque->logo(150,50)."\" alt=\"".$marque->type."\" title=\"".$marque->libelle."\">";
    $template->evaltemplate("MarqueLink","articles");
    $c++;
}

$template->evaltemplate("MarquesDescription","description");
$template->parts["description_img"]="<img id=\"categorie_visuel\" class=\"hidden-xs\" src=\"/categorie/1_230_134.jpg\" alt=\"img-description\">";


$template->parts["title"]=$template->translate("/site/nos_marques","F");

$encarts=encarts($template->site_code(),$template->langue,"marque",0);
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

foreach($template->sites() as $site_code=>$site){
    foreach($site['hosts'] as $url=>$deflangue){
	if($site['actif'][$url])
	    $template->parts["alternate"].="<link rel=\"alternate\" hreflang=\"".$site['locales'][$deflangue]."\" href=\"https://www.".$url.$template->translate("/site/url_marques",null,$deflangue)."\">\n";
    }
}

$template->parts["pagetype"]="marque";
$template->parts["fil_ariane"]=" &gt; <span property=\"itemListElement\" typeof=\"ListItem\"><span property=\"name\">".$template->translate("/site/nos_marques","F")."</span><meta property=\"position\" content=\"2\"></span>";

$template->evaltemplate("KdosPage","main");

$template->pageparams($host,$template->translate("/site/nos_marques","F"),$template->translate("/site/nos_marques","F"));
$template->out();

?>