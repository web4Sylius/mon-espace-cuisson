<?php

require ("global.php");
$types=array("restaurants-bars-a-vin"=>"Restaurants bars à vin","club-d-oenologie"=>"Club d'oenologie","cavistes"=>"Cavistes","producteurs-de-vin"=>"Producteurs de vin","epicerie-fine"=>"Epicerie fine");


$contenu="<div align=\"center\"><img src=\"/images/templates/france_departements_mono.png\" usemap=\"#france\"></div>
<map name=\"france\">";
$c=0;

$req=$DB_site->query("SELECT * FROM departement WHERE 1 ORDER BY code");
while($dep=$DB_site->fetch_array($req)){
    $contenu.="<area shape=\"poly\" coords=\"".$dep['map']."\" title=\"".$dep['nom_departement']."\" alt=\"".$type." ".$dep['nom_departement']."\" href=\"/partenaires/".$dep['code'].".html\">";
    /*$contenu.="<br><a href=\"/partenaires/".$_GET['type']."/".$dep['code'].".html\">".$type." ".$dep['nom_departement']." (".$dep['code'].")</a>";
    $c++;
    if($c==51){
	$c=0;
	$contenu.="</td><td>";
    }*/
}
$contenu.="</map>";
$template->parts["content"]=$contenu;
$template->parts["fil_ariane"]=" &gt; <span property=\"itemListElement\" typeof=\"ListItem\"><a property=\"item\" typeof=\"WebPage\" href=\"/partenaires\"><span property=\"name\">Nos partenaires</span></a><meta property=\"position\" content=\"2\"></span>";
$template->parts["title"]="Nos partenaires en France";

include("barrefixe.php");

$template->pageparams($host,"Nos partenaires en France - ".$template->translate("/site/nom"),$type);
$template->evaltemplate("InstituPage","main");

$template->out();

?>