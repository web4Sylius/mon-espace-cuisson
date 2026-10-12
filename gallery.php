<?php

include("global.php");

$photopath=$template->translate("/site/gallery_path");
$imgw=321;
$imgh=240;
$gallery=new gallery($DB_site,$template->langue);
$gallery->path=$photopath;
if(isset($_GET['url']))
    $return=$gallery->loadUrl($_GET['url']);
else
    $return=$gallery->loadIndex();

if($return==false){
    header("HTTP/1.1 301 Moved Permanently");
    header("location:/plan_site.php");
    header("Connection: close");
}
$gindex=new gallery($DB_site,$template->langue);
$gindex->path=$photopath;
$gindex->loadIndex();

//ici on fais afficher les balise hreflang pour fr et en
foreach($template->sites() as $site_code=>$site){
    foreach($site['hosts'] as $url=>$deflangue){
	    if($site['actif'][$url]){
	       	$hreflang=new gallery($DB_site, $deflangue);
	       	$hreflang->path=$template->translate("/site/gallery_path",null,$deflangue);
			$hreflang->loadUrlHreflang($_GET['url'],$template->langue);
			$template->parts["alternate"].="<link rel=\"alternate\" hreflang=\"".$site['locales'][$deflangue]."\" href=\"https://www.".$url."".$hreflang->getFullUrl()."\">\n";
	    }
    }
}
$template->parts["fil_ariane"]=" &gt; <span property=\"itemListElement\" typeof=\"ListItem\"><a property=\"item\" typeof=\"WebPage\" href=\"".$gindex->getFullUrl()."\"><span property=\"name\">".$gindex->libelle."</span></a><meta property=\"position\" content=\"2\"></span>";
if($gallery->type!="index") $template->parts["fil_ariane"].=" &gt; <span property=\"itemListElement\" typeof=\"ListItem\"><span property=\"name\">".$gallery->libelle."</span><meta property=\"position\" content=\"3\"></span>";

$photos=$gallery->load_photos();
$template->parts["articles"]="";

$c=1;
foreach($photos as $photo){
    $a=$a2="";
    if($photo->link!=""){
    $artid = strtok($photo->link,"[]article-"); //on decoupe la chaine pour recupérer l'artid
    $artid = intval($artid);

    $sql = "SELECT a.*, m.libelle AS marque";
	$sql .= " FROM article a";
	$sql .= " INNER JOIN article_marque am USING(artid)";
	$sql .= " INNER JOIN marque m USING(marqueid)";
	$sql .= " WHERE artid = '".$artid."'";
	$article = $DB_site->query_first($sql);

	$a="<a href=\"".$template->mkurl("article",$artid,$article)."\">";
	$a2="</a>";
    }
    switch($c){
	case 1:
	$class2="";
	$c++;
	break;
	
	case 3:
	$class2="";
	$c=1;
	break;
	
	default:
	$class2="";
	$c++;
    }
    $template->parts["articles"].="<div class=\"col-xs-12 col-sm-6 col-md-4 col-lg-3\"> <div class=\"gallery_photo $class2\">
      $a<img src=\"/".$photo->getpath($photopath,$imgw,$imgh)."\" alt=\"".$photo->libelle."\">$a2
      <p class=\"legende\">".ucfirst($photo->legende)."</p>
      <p class=\"libelle\">".$a.ucfirst($photo->libelle).$a2."</p>
    </div></div>";
}

if($gallery->type=="index"){
    $template->parts["description_img"]="<img id=\"categorie_visuel\" class=\"hidden-xs\" src=\"/categorie/1_230_134.jpg\" alt=\"img-description\">";
    $types=$gallery->getTypes();
    $template->parts["articles"].="<div class=\"institutionnel\">";
    foreach($types as $type){
	if($type!="index"){
	    $list=$gallery->getListByType($type);
	    $template->parts["articles"].= "<ul>";
	    foreach($list as $g){
		$p1=array_shift($g->load_photos());
		$template->parts["articles"].="
		    <li><a href=\"".$g->getFullUrl()."\">".$g->libelle."</a></li>";
	    }
	    $template->parts["articles"].="</ul>";
	}
	
    }
    $template->parts["articles"].="</div>";

    $template->parts["liste_choices"]="";
    $list=$gallery->getListByType("categorie");
    foreach($list as $gal){
	$template->parts["liste_choices"].="<li><a href=\"".$gal->getFullUrl()."\">".ucfirst($gal->libelle)."</a></li>";
    }

    $list=$gallery->getListByType("marque");
    foreach($list as $gal){
	$template->parts["liste_choices"].="<li><a href=\"".$gal->getFullUrl()."\">".ucfirst($gal->libelle)."</a></li>";
    }
}

if($gallery->type=="categorie"){
    $cat=new categorie($DB_site,$gallery->type_id,$template->langue);
    $template->parts["description_img"]="<img id=\"categorie_visuel\" class=\"hidden-xs\" src=\"".$cat->image(230,134)."\" alt=\"img-description\">";
    $cat->setparam("bonsplans","selection");
    $template->parts["liste_choices"]="";
    $subcategories=$cat->subcategories("position");
    if(count($subcategories)>0 && $subcategories){
	foreach($subcategories as $p=>$subcategorie){
	    $cat->setparam("subcatids",$subcategorie[0]->subcatids);
	    $artids=$cat->articles("prix",false);
	    $count=count($artids);
	    if($count>0){
	      $url=$cat->mkurl(array("catid"=>$subcategorie[0]->catid));
	     //MISE EN COMMENTAIRE LE 30/04/18 TACHE SI#3889  /////// $template->parts["liste_choices"].="<li><a href=\"".$url['url']."\" rel=\"".$url['rel']."\" class=\"choice\">".$subcategorie[0]->libelle."</a></li>";


	      /*if(count($subcategorie)>0){
		$template->parts["li"].="<ul>";
		foreach($subcategorie as $p2=>$subcategorie2){
		  if($p2>0) $count2=$cat->articlecount($subcategorie2[0]->subcatids);
		  if($p2>0 && $count2>0) $template->parts["li"].="<li><a href=\"/categorie.php?catid=".$subcategorie[0]->catid."&subcatids=".$subcategorie2[0]->catid."\">".$subcategorie2[0]->libelle."</a></li>";
		}
		$template->parts["li"].="</ul>";
	      }*/
	    }
	}
    }elseif($cat->parentid==0){
	$capacites=$cat->getcapacitetranche();
	foreach($capacites as $capaciteid=>$param){
	  $txt=sprintf($template->translate("/categorie/entre_bouteilles","F"),$param->from,$param->to);
	  if($capaciteid==0) $txt=sprintf($template->translate("/categorie/inferieur_bouteilles","F"),$param->to);
	  if($capaciteid==(count($categorie->capacites)-2)) $txt=sprintf($template->translate("/categorie/superieur_bouteilles","F"),$param->from);
	  $url=$cat->mkurl(array("catid"=>$cat->catid,"capaciteids"=>$param->id));
	  //MISE EN COMMENTAIRE LE 30/04/18 TACHE SI#3889  ///////  $template->parts["liste_choices"].="<li class=\"".($param->checked?"checked":"")."\"><a href=\"".$url['url']."\" rel=\"".$url['rel']."\" id=\"cap".$capaciteid."\" class=\"choice\">".$txt." (".$param->qte.")</a></li>";  
	}
    }

}

if($gallery->type=="marque"){
    $clmarque=new marque($DB_site,$gallery->type_id);
    $template->parts["description_img"]="<img src=\"".$clmarque->logo(150,50)."\" alt=\"".trim($clmarque->type." ".$clmarque->libelle)."\">";

    $cat=new categorie($DB_site,0,$template->langue);
    $cat->setparam("marqueids",$gallery->type_id);
    $cat->setparam("bonsplans","selection");
    $subcategories=$cat->subcategories("position");
    $template->parts["liste_choices"]="";
    foreach($subcategories as $p=>$subcategorie){
	$cat->setparam("subcatids",$subcategorie[0]->subcatids);
	$artids=$cat->articles("prix",false);
	$count=count($artids);
	if($count>0){
	  $template->parts["liste_choices"].="<li><a href=\"".$cat->mkurl(array("catid"=>$subcategorie[0]->catid,"marqueids"=>$clmarque->marqueid))['url']."\" class=\"choice\">".$subcategorie[0]->libelle."</a></li>";
	  /*if(count($subcategorie)>0){
	    $template->parts["li"].="<ul>";
	    foreach($subcategorie as $p2=>$subcategorie2){
	      if($p2>0) $count2=$cat->articlecount($subcategorie2[0]->subcatids);
	      if($p2>0 && $count2>0) $template->parts["li"].="<li><a href=\"/categorie.php?catid=".$subcategorie[0]->catid."&subcatids=".$subcategorie2[0]->catid."&marqueids=".$clmarque->marqueid."\">".$subcategorie2[0]->libelle."</a></li>";
	    }
	    $template->parts["li"].="</ul>";
	  }*/
	}
    }
}


$template->parts["description"]=$gallery->description;
$template->parts["pagetitle"]=$gallery->libelle;
$template->parts["title"]=$gallery->libelle;

include("barrefixe.php");

$template->evaltemplate("galleryPage","main");
$metatitle=$gallery->libelle." - ".$template->translate("/site/nom");
$template->pageparams($template->parts["host"],$metatitle,$gallery->description);

$template->out();

?>