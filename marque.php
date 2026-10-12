<?php

include("global.php");
$libelle="libelle".$template->langue;
//$contenu="contenu".$template->langue;
$contenu="description".$template->langue;
if(!isset($order)) $order="cat";

$marqueid=intval($_GET['marqueid']);

$clmarque=new marque($DB_site,$marqueid,$template->langue);

if($clmarque->marqueid!=$marqueid){
    header("location:toutes-nos-marques.html");
    exit;
}

if(substr($_SERVER['REDIRECT_URL'],1)!=$clmarque->link){
    header("HTTP/1.1 301 Moved Permanently");
    header("location:".$clmarque->link);
    header("Connection: close");
}


$cat=new categorie($DB_site,0,$template->langue);
$tranches=$cat->listeprix;
$cat->setparam("marqueids",$marqueid);
$cat->setparam("pro",($sitepro?2:1));
$cat->setparam("site_code",$template->site_code());
$cat->subcategories();
$remartids=array();

$nbarticle=count($cat->articles("prix",false));

$template->parts["liste_choices"]="";
$cat->setparam("prix",array());
foreach($cat->getMenus() as $idmenu=>$menuinfos){
    $subcatids=$cat->getMenu($idmenu);
    $subcatids=array_map(function($a){return $a['catid'];},$subcatids);
    $cat->setsubcatids($subcatids);

    $template->parts["articlescat"]="";

      
    foreach($subcatids as $menusubcatid){
	  $pcat=new categorie($DB_site,$menusubcatid,$template->langue);
	  $pcat->setparam("bonsplans",array("selection"));
	  $pcat->setparam("pro",($sitepro?2:1));
	  $pcat->setparam("site_code",$template->site_code());
	  $pcat->setparam("marqueids",$marqueid);
	  $pcat->setsubcatids($menusubcatid);
	  $artids=$pcat->articles("prix",false);
	  if(count($artids)>0){
	      $url=$pcat->mkurl(array("catid"=>$menusubcatid,"marqueids"=>$marqueid));
	      $template->parts["liste_choices"].="<li><a href=\"".$url['url']."\" class=\"choice\">".$pcat->libelle."</a></li>";
	      if($order=="cat"){
			  $template->parts["articles"].="<div class=\"barre_filtre\">
			    <h2 class=\"titre_result titre_seul\">".$pcat->libelle." ".ucwords(strtolower($clmarque->libelle))."</h2>
			  </div>";

			  $remartids=array_merge($remartids,$artids);
			  
			  foreach($artids as $artid){
			      $class2="";
			      vignette($artid,"categorie",$class2);
			  }

			  $template->parts["catid"]=$subcategorie[0]->catid;
			  $template->parts["articles"].="<div class=\"clear\"></div>";
	      }
	  }
      }
}


if(!is_null($promolink)) $template->parts["menucategorie"].=$promolink;
$template->parts["liste_choices2"]="";
$subcategories=$cat->subcategories("position");
for($i=0;$i<count($tranches)-1;$i++){
      $template->parts["articlescat"]="";
      $cat->setparam("prix",array($tranches[$i],$tranches[$i+1]));
      $artids=$cat->articles("prix",false);
      $artcount=count($artids);
      if($artids!=false && $artcount>0){
	$catlibelle=sprintf($template->translate("/categorie/entre_euro","F"),$template->price_format($tranches[$i],0),$template->price_format($tranches[$i+1],0));
	if($i==0) $catlibelle=sprintf($template->translate("/categorie/inferieur_euro","F"),$template->price_format($tranches[$i+1],0));
	if($i==count($tranches)-2) $catlibelle=sprintf($template->translate("/categorie/superieur_euro","F"),$template->price_format($tranches[$i],0));
	$template->parts["liste_choices2"].="<li><a href=\"".$_SERVER['REDIRECT_URL']."?order=price#scat_$i\" class=\"choice\">".$catlibelle." ($artcount)</a></li>";

	if($order=="price"){
	    $template->parts["articles"].="<a name=\"scat_$i\"></a>
	    <div class=\"titrearticles marque\"><div>
		    <h2 class=\"color_bg\">".$catlibelle."</h2></div>
	    </div>";
	    $remartids=array_merge($remartids,$artids);
	    foreach($artids as $artid){
			$class2="";
			vignette($artid,"categorie",$class2);
	    }
	    $template->parts["articles"].="<div class=\"clear\"></div>";
	}
	
	$li.="<a href=\"".$clmarque->link."?order=price#scat_$i\">$catlibelle</a>";
      }
}


$template->parts["selection"]="<div class=\"filtres_select\"><a href=\"".$template->mkurl("marque",0,$clmarque)."\" class=\"checked\"><i class=\"fas fa-check\"></i> ".$template->translate("/categorie/filtres/tous_produits","F")." (".$nbarticle.")</a></div>";

$promocat=new categorie($DB_site,0,$template->langue);
$promocat->setparam("marqueids",$marqueid);
$promocat->setparam("pro",($sitepro?2:1));
$promocat->setparam("site_code",$template->site_code());
$promocat->setparam("bonsplans",array("promo"));
$promocat->subcategories();
$nbarticle=count($promocat->articles("prix",false));

if($nbarticle>0){
    $cat->promo_load("marque");
    $template->parts["selection"].="<div class=\"filtres_select\"><a href=\"".$template->mkurl("promo",0,$cat)."\">".$template->translate("/admin/index/promotions","F")." (".$nbarticle.")</a></div>";
}

$template->evaltemplate("FiltreSelection","filtre_selection");


if($template->parts["articles"]==""){
// $template->evaltemplate("MarqueVide","articles");
    $archives=$DB_site->query_first("SELECT COUNT(artid) FROM article_site INNER JOIN article_marque USING(artid) WHERE marqueid=".$marqueid." AND active=3 AND site_code='".$template->site_code()."'");
    if($archives[0]>0){
		$clmarque->archive_load();
		$template->redirect($clmarque->archive_url,302);
    }else
	$template->redirect("toutes-nos-marques.html?bob",302);
}

$encarts=encarts($template->site_code(),$template->langue,"marque",$marqueid);
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
    
//La jointure sert uniquement à sortir en 1er les pages instit associées à la marque
/*$sql = "SELECT i." . $libelle . ", i." . $contenu . ", i.ref_title" . $template->langue . " AS ref_title, i.ref_description" . $template->langue . " AS ref_description, IFNULL(im.marqueid, 0) as Tri";
$sql .= " FROM institutionnel i";
$sql .= " LEFT JOIN institutionnel_marque im ON (im.institutionnelid = i.institutionnelid AND im.marqueid = " . $clmarque->marqueid . ")";
$sql .= " WHERE i.libelleFR LIKE '%" . addslashes($clmarque->libelle) . "%'";
$sql .= " AND i.site_code = '" . $template->site_code() . "'";
$sql .= " ORDER BY Tri DESC";
$sql .= " LIMIT 1";*/
$sql = "SELECT md." . $libelle . ", md." . $contenu . ", md.ref_title" . $template->langue . " AS ref_title, md.ref_description" . $template->langue . " AS ref_description";
$sql .= " FROM marque_description md INNER JOIN marque m USING(marqueid)";
//$sql .= " LEFT JOIN institutionnel_marque im ON (im.institutionnelid = i.institutionnelid AND im.marqueid = " . $clmarque->marqueid . ")";
$sql .= " WHERE m.libelle LIKE '%" . addslashes($clmarque->libelle) . "%'";
//$sql .= " AND i.site_code = '" . $template->site_code() . "'";
//$sql .= " ORDER BY Tri DESC";
$sql .= " LIMIT 1";
$int = $DB_site->query_first($sql);
$int[1]=addslashes($int[1]);
// eval("\$Templateinstitutionnel=stripslashes(\"$templatecache[MarqueDescription]\");");
$pagedescription=$int[$contenu];
// $pagedescription=strip_tags($pagedescription);
$pagedescription=str_replace("&nbsp;"," ",$pagedescription);
$pagedescription=trim(preg_replace("/\r|\n|\r\n|\t/","",$pagedescription));
$pagedescription=str_replace("  ","",$pagedescription);
$pagedescription=preg_replace("/&rsquo;|\"/","'",$pagedescription);
// if(strlen($pagedescription)>300) $pagedescription=substr($pagedescription,0,strpos($pagedescription," ",300));
$template->parts["description_img"]="<img class=\"img_desc\" style=\"max-width:180px;\" src=\"".$clmarque->logo(150,50)."\" alt=\"".trim($clmarque->type." ".$clmarque->libelle)."\">";
$template->parts["description"]=$pagedescription;

$template->parts["pagetitle"]=$clmarque->libelle." - ".$template->translate("/site/nom");
$template->parts["title"]=$clmarque->libelle;

foreach($template->sites() as $site_code=>$site){
    foreach($site['hosts'] as $url=>$deflangue){
    $marque=new marque($DB_site,$marqueid,strtoupper($site['locales'][$deflangue]));	
	if($site['actif'][$url])
	    $template->parts["alternate"].="<link rel=\"alternate\" hreflang=\"".$site['locales'][$deflangue]."\" href=\"https://www.".$url."/".$marque->link."\">\n";
    }
}


$template->parts["fil_ariane"]=" &gt; <span property=\"itemListElement\" typeof=\"ListItem\"><a property=\"item\" typeof=\"WebPage\" href=\"".$template->translate("/site/url_marques")."\"><span property=\"name\">".$template->translate("/site/nos_marques","F")."</span></a><meta property=\"position\" content=\"2\"></span> &gt; <span property=\"itemListElement\" typeof=\"ListItem\"><span property=\"name\">".$clmarque->libelle."</span><meta property=\"position\" content=\"3\"></span>";


$remarketing=array("libelle"=>array(),"price"=>array(),"category"=>array());
foreach($remartids as $artid){
    $article=$DB_site->query_first("SELECT c.libelle".$template->langue." AS categorie, a.libelle".$template->langue." AS libelle FROM categorie c INNER JOIN article a USING(catid) WHERE artid=".$artid);
    $remarketing['libelle'][]=utf8_encode(html_entity_decode($article['libelle'],ENT_COMPAT,"ISO-8859-15"));
    $remarketing['category'][]=utf8_encode(html_entity_decode($article['categorie'],ENT_COMPAT,"ISO-8859-15"));
    $remarketing['price'][]=article_price($artid);
}
$template->parts["remarketing"]=google_remarketing($remartids,"category",'',$remarketing['category'],$remarketing['libelle'],$remarketing['price']);

include("barrefixe.php");

$template->evaltemplate("marquePage","main");
$template->pageparams($template->parts["host"],$int['ref_title'],$int['ref_description']);

$template->out();

?>
