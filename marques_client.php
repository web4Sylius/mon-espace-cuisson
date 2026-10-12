<?php 

include("global.php");

$marques_client=new marque_client($DB_site);

$cols=4;
$c=1;
foreach($marques_client->getlist("nom") as $mc){
    $template->parts["class2"]="";
    if($c==$cols){
	$c=0;
	$template->parts["class2"]=" last";
    }
    $template->parts["clientlibelle"]=$mc->nom;
    $template->parts["imglogo"]="<img src=\"".$mc->logo_url(200,70)."\" alt=\"".$mc->nom."\" title=\"".$mc->nom."\">";
    $template->evaltemplate("LogoClient","articles");
    $c++;
}

include("barrefixe.php");

foreach($template->sites() as $site_code=>$site){
    foreach($site['hosts'] as $url=>$deflangue){
	if($site['actif'][$url])
	    $template->parts["alternate"].="<link rel=\"alternate\" hreflang=\"".$site['locales'][$deflangue]."\" href=\"https://www.".$url.$template->translate("/site/url_marques",null,$deflangue)."\">\n";
    }
}

$template->parts["fil_ariane"]=" &gt; <span property=\"itemListElement\" typeof=\"ListItem\"><span property=\"name\">".$template->translate("/site/nos_clients","F")."</span><meta property=\"position\" content=\"2\"></span>";

$template->parts["title"]=$template->translate("/site/nos_clients","F");
$template->parts["description_img"]="<img id=\"categorie_visuel\" class=\"hidden-xs\" src=\"/categorie/1_230_134.jpg\">";

$template->evaltemplate("KdosPage","main");
$template->pageparams($host,$template->translate("/site/nos_clients","F"),$template->translate("/site/nos_clients","F"));

$template->out();

?>