<?php
ini_set('memory_limit','600M');
require ("global.php");
$libelle="libelle".$template->langue;
$lienretour = "index.htm" ;

//ici on fais afficher les balise hreflang pour fr et en
foreach($template->sites() as $site_code=>$site){
	foreach($site['hosts'] as $url=>$deflangue){
		if($site['actif'][$url])
			$template->parts["alternate"].="<link rel=\"alternate\" hreflang=\"".$site['locales'][$deflangue]."\" href=\"https://www.".$url."/plan_site.php\">\n";
	}
}
$template->parts['js_page']="<script src=\"/javascripts/masonry-desandro.js\" defer></script>";

$oCategorie = new categorie($DB_site, 0, $template->langue);
$oCategorie->setparam("pro",($sitepro?2:1));
$oCategorie->setparam("site_code",$template->site_code());

$template->parts["categories"]="";
foreach($oCategorie->getMenus() as $idmenu=>$menuinfos){
	$subcatids=$main->getMenu($idmenu);
	$subcatids=array_map(function($a){return $a['catid'];},$subcatids);
	$template->parts["categories"].="<div class=\"bloclink col-xs-12 col-sm-6 col-md-4 col-lg-3\">
	<a href=\"".$menuinfos['url'].".htm\" ><div class=\"maincat\">".$menuinfos["libelle"]."</div></a>";
	foreach($subcatids as $menusubcatid){
		$maincat=new categorie($DB_site,$menusubcatid,$template->langue);
		$catlist=$maincat->subcategories("position");
		$aProduits = $maincat->getArticles(intval($menusubcatid));
		if(count($aProduits)>0){
			$template->parts["categories"].="<p><a href=\"".$maincat->url()['url']."\">".$maincat->libelle."</a></p>";
			if($menusubcatid!=1){
				$template->parts["categories"].="<ul>";
				foreach($catlist as $p=>$sscat){
					$re=$DB_site->query("SELECT c.libelle".$template->langue." as 'libelle',c.catid FROM categorie c INNER JOIN article a ON c.catid = a.catid WHERE parentid = ".$sscat[0]->catid." AND a.active = '1' GROUP BY c.catid");
					if($DB_site->num_rows($re) == 0) {
						$r2=$DB_site->query_first("SELECT c.libelle".$template->langue." as 'libelle',c.catid FROM categorie c INNER JOIN article a ON c.catid = a.catid WHERE c.catid = ".$sscat[0]->catid." AND a.active = '1' GROUP BY c.catid ORDER BY position ASC");
						if($r2 != "") {
							$template->parts["categories"].="<li><a href=\"".$sscat[0]->url()['url']."\">".$sscat[0]->libelle."</a></li>";
						}
						else {
							$template->parts["categories"].="";
						}
					}
					else {
						$template->parts["categories"].="<li><a href=\"".$sscat[0]->url()['url']."\">".$sscat[0]->libelle."</a></li>";

						$template->parts["categories"].="<li><ul>";
						foreach($re as $p2=>$sscat2){

							$Cat = new categorie($DB_site, $sscat2['catid'], $template->langue);
							$souscategorie_url = $template->mkurl("categorie", $sscat2['catid'], $Cat);
							$template->parts["categories"].="<li><a href=\"".$souscategorie_url."\">".$sscat2['libelle']."</a></li>";
						}
						$template->parts["categories"].="</ul></li>";
					}
				}
				$template->parts["categories"].="</ul>";
			}else{
				//$capacites=$maincat->getcapacitetranche();
				if(count($capacites)>0){
					$template->parts["categories"].="<ul>";
					foreach($capacites as $capaciteid=>$param){
						$txt=ucfirst(strtolower($catlist[0]->libelle)).sprintf($template->translate("/menu/a_blles"),$param->from,$param->to);
						$template->parts["categories"].="<li><a href=\"/categorie.php?catid=".$menusubcatid."&amp;capaciteids=".$capaciteid."\">".$txt."</a></li>";
					}
					$template->parts["categories"].="</ul>";
				}
			}
		}
	}
	$template->parts["categories"].="</div>";
}


$m=new marque($DB_site);
$marques=$m->listall("libelle",($sitepro?2:1),$template->site_code(),array(1)); //1 = actifs, 3 = archivés
$nbm=count($marques);
$nb=0;
$template->parts["marques"]="";
foreach($marques as $marque){
	$template->parts["marques"].="<div class=\"bloclink_marque col-xs-12 col-sm-6 col-md-4 col-lg-3\">
	<div class=\"maincat\">
	<a href=\"".$marque->link."\">".$marque->libelle."</a>
	</div>
	</div>";
}


$instits=$DB_site->query("select * from institutionnel where active='1' and menuid_instit = '1' AND libelle".$template->langue."<>'' AND site_code='".$template->site_code()."' ORDER BY position");
$template->parts["institutionnelles1"]="<div class=\"bloclink2\">
<ul>";
$mid=floor($DB_site->num_rows($instits)/2);
$i=0;
while ($instit=$DB_site->fetch_array($instits)){
	$i++;
	$url=$template->mkurl("institutionnel",$instit[institutionnelid],$instit);
	$template->parts["institutionnelles1"].="<li><a href=\"".$url."\">".$instit[$libelle]."</a></li>";
	if($i==$mid){
		$template->parts["institutionnelles1"].="</ul>
		</div>
		<div class=\"bloclink2\">
		<ul>";
	}
}
$template->parts["institutionnelles1"].="</ul>
</div>";

$instits=$DB_site->query("select * from institutionnel where active='1' and menu = '3' AND site_code='".$template->site_code()."'");
$template->parts["institutionnelles3"]="<div class=\"bloclink2\">
<ul>";
$mid=floor($DB_site->num_rows($instits)/2);
$i=0;
while ($instit=$DB_site->fetch_array($instits)){
	$i++;
	$url=$template->mkurl("institutionnel",$instit[institutionnelid],$instit);
	$template->parts["institutionnelles3"].="<li><a href=\"".$url."\">".$instit[$libelle]."</a></li>";
	if($i==$mid)
		$template->parts["institutionnelles3"].="</ul>
	</div>
	<div class=\"bloclink2\">
	<ul>";
}
$template->parts["institutionnelles3"].="</ul>
</div>";

$instits=$DB_site->query("select * from institutionnel where active='1' and menu = '4' AND site_code='".$template->site_code()."'");
$template->parts["institutionnelles4"]="<div class=\"bloclink2\">
<ul>";
$mid=floor($DB_site->num_rows($instits)/2);
$i=0;
while ($instit=$DB_site->fetch_array($instits)){
	$i++;
	$url=$template->mkurl("institutionnel",$instit[institutionnelid],$instit);
	$template->parts["institutionnelles4"].="<li><a href=\"".$url."\">".$instit[$libelle]."</a></li>";
	if($i==$mid)
		$template->parts["institutionnelles4"].="</ul>
	</div>
	<div class=\"bloclink2\">
	<ul>";
}

if($template->translate("/requetage/link")!="")
	$template->parts["institutionnelles4"].="<li".($i>=$nblivisible?" class=\"hidden\"":"")."><a href=\"/".$template->translate("/requetage/path")."/".$template->translate("/requetage/index_url")."\">".$template->translate("/requetage/link","F")."</a></li>";

$template->parts["institutionnelles4"].="</ul>
</div>";

if($sitepro)
	$table_conseil = "conseil_pro";
else
	$table_conseil = "conseil_part";

$req=$DB_site->query("SELECT $libelle, conseilid FROM ".$table_conseil." WHERE conseilparent=0 AND site_code='".$template->site_code()."' ORDER BY position");
$template->parts["conseil2"]="";
$i = 1;
$template->parts["conseil2"].="<div class=\"bloclink\">";
while($conseil=$DB_site->fetch_array($req)){
	$url=$template->mkurl("conseil",$conseil['conseilid'],$conseil);
	$template->parts["conseil2"].="<ul>";
	$template->parts["conseil2"].="<li><a href=\"".$url."\">".$conseil[$libelle]."</a></li>";
	$req2=$DB_site->query("SELECT $libelle, conseilid FROM ".$table_conseil." WHERE conseilparent=".$conseil['conseilid']." ORDER BY position");
	if($DB_site->num_rows($req2)>0){
		$template->parts["conseil2"].="<li><ul>\n";
		while($ssconseil=$DB_site->fetch_array($req2)){
			$url=$template->mkurl("conseil",$ssconseil['conseilid'],$ssconseil);
			$template->parts["conseil2"].="<li><a href=\"".$url."\">".$ssconseil[$libelle]."</a></li>\n";
		}
		$template->parts["conseil2"].="</ul></li>\n";
	}
	$template->parts["conseil2"].="</ul>";
	if($i%3 == 0 ) $template->parts["conseil2"].="</div><div class=\"bloclink\">";
	$i++;
}
$template->parts["conseil2"].="</div>";

$template->parts["presse2"]="";
$req=$DB_site->query("SELECT $libelle AS libelle,id,url".$template->langue." AS url FROM presse WHERE parent_id=0 ORDER BY position");
while($presse=$DB_site->fetch_array($req)){
	$template->parts["presse2"].="<div class=\"bloclink2\">
	<a href=\"/presse/".$presse['url'].".htm\">".$presse['libelle']."</a>";
	$req2=$DB_site->query("SELECT $libelle AS libelle,id,url".$template->langue." AS url FROM presse WHERE parent_id=".$presse['id']." AND active=1 ORDER BY position");
	$template->parts["presse2"].="<ul>";
	while($child=$DB_site->fetch_array($req2)){
		$template->parts["presse2"].="<li><a href=\"/presse/".$child['url'].".htm\">".$child['libelle']."</a></li>";
	}
	$template->parts["presse2"].="</ul>
	</div>";
}

include("barrefixe.php");

$template->parts["fil_ariane"]=" &gt; <span property=\"itemListElement\" typeof=\"ListItem\"><span property=\"name\">".$template->translate("/site/plan_site","F")."</span><meta property=\"position\" content=\"2\"></span>";
//$template->parts["title"]=$template->translate("/site/plan_site","F");
//Paramétrage des META référencement de la page
if($sitepro)
	$title_desc = '/index/meta_description_pro';
else
	$title_desc = '/index/meta_description';

$template->pageparams($host,$template->translate("/site/plan_site","F"),$template->translate($title_desc,"F"));
$template->evaltemplate("PlanSite","main");

$template->out();

?>