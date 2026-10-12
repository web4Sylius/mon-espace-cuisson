<?php

require ("global.php");
$libelle="libelle".$template->langue;
$contenu="contenu".$template->langue;
$description="description".$template->langue;
$resume="resume".$template->langue;

////////////////////////////////////////////////////////////////////////////////////////////////////////
// Function get the latest PDF name available
// TSI
////////////////////////////////////////////////////////////////////////////////////////////////////////
function getPressePDF($id,$host){
	$dirpdf="./configurations/$host/images/presse/";
	$namefilter = "#^".$id."_[a-zA-Z0-9-. _]*(\.pdf|\.PDF|\.Pdf)$#";
	$filespdf = preg_grep($namefilter, scandir($dirpdf,SCANDIR_SORT_DESCENDING));
	//$filespdf = preg_grep($namefilter, scandir("./images/presse",SCANDIR_SORT_DESCENDING));
	// print_r ($filespdf);
	// $myfilepdf = $id.".pdf";
	if(count($filespdf)>0){
		foreach ($filespdf as $key => $value) {
			return($value);
		}
	} else {
		return(false);
	}
};
/////////////////////////////////////// TSI ////////////////////////////////////////////////////////////

// recuperation de l'ID de la page a afficher en fonction de l'URL defini dans la BDD
$result=false;
if(isset($_GET['url'])){
	$presse=$DB_site->query_first("SELECT * FROM presse WHERE url".$template->langue."='".mysqli_real_escape_string($DB_site->link_id,$_GET['url'])."'");
	$result=($presse!=false);
	if($presse['active']==0){
		header('Location: /plan_site.php'); // page desactivÃ©e
		exit;
	}
}

// page non trouvÃ©e
if(!$result){
    header("HTTP/1.1 301 Moved Permanently");
    header("location: /plan_site.php");
    exit;
}

// mise a zero de l'affichage
$template->parts["presse"]="";
$template->parts["articles_presse"]="";

// Creation contenu, detail d'un article presse, 2 images et telechargement pdf
// niveau de page detail 'id' <> 0, definition dans la BDD.
if ($presse['parent_id']>0) {
	$couv = $presse['id']."-couv.jpg";	// image de l'article
	$vign = $presse['id']."-vign.jpg";	// image de la liste des articles
	if(file_exists("configurations/".$host."/images/presse/".$presse['id']."-couv.jpg")){ // si l'image de couverture existe, sinon on n'essaye pas de l'afficher
		$img_couv = "<img src=\"/images/presse/".$couv."\" width=\"400\" alt=\"".$presse['revue']."\">";
	}
	else {
		$img_couv = "";
	}

	$pdf = getPressePDF($presse['id'],$host);
	// affichage des images couverture, article et du lien telechargement pdf
	$template->parts["content"]="<div class=\"institutionnel\">"
								."<p align=\"center\">"
								."<img src=\"/images/presse/".$vign."\" width=\"400\" alt=\"".$presse['revue']."\">"
								."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;"
								.$img_couv
								."</p>";
	if($pdf){
		$template->parts["content"].="<a target=\"_blank\" href=\"/configurations/$host/images/presse/".$pdf."\" class=\"all_btn\" download=\"".$presse['revue']."_".$pdf."\"  >Télécharger l'article</a>"." <br>";
	}
	// Ajout du texte liÃ© a l'article
	$template->parts["content"].=$presse[$contenu];
	if($presse['lien'] != ""){
		$template->parts["content"] .= "<br><a href=\"".$presse['lien']."\" class=\"all_btn\" target=\"_blank\">Découvrir l'original</a>";
	}
	if($presse['auteur'] != "" && $presse['date_revu'] != "") {
			$template->parts["content"] .= "par <b>".$presse['auteur']."</b> - ".$presse['date_revu'];
	}
	$template->parts["content"].= "</div>";
}
$pos = 2;
// ID 0 : page principale (hors main/index), ID 3 : Liste article web, ID 4 : Liste article journaux, ID >4 et parent_id>0 detail d'un article
if($presse['id']>0) {
	// creation, modification du chemin, breadcrumb, avec le nom des pages parent
    if($presse['parent_id']>0){
		$parent=$DB_site->query_first("SELECT $libelle,url".$template->langue." FROM presse WHERE id=0");
		$template->parts["fil_ariane"].=" > <span property=\"itemListElement\" typeof=\"ListItem\"><a property=\"item\" typeof=\"WebPage\" href=\"/presse/".$parent['url'.$template->langue].".htm\"><span property=\"name\">".$parent[$libelle]."</span></a><meta property=\"position\" content=\"".$pos."\"></span>";  
		$pos++;  
    }
    $parent=$DB_site->query_first("SELECT $libelle,url".$template->langue." FROM presse WHERE id=".intval($presse['parent_id']));
    $template->parts["fil_ariane"].=" > <span property=\"itemListElement\" typeof=\"ListItem\"><a property=\"item\" typeof=\"WebPage\" href=\"/presse/".$parent['url'.$template->langue].".htm\"><span property=\"name\">".$parent[$libelle]."</span></a><meta property=\"position\" content=\"".$pos."\"></span>";
    $pos++;
    // Recuperation de la liste des articles disponibles, pour parent_ID 3 web, ID 4 presse
    $req=$DB_site->query("SELECT id, $libelle AS resume, url".$template->langue." AS url, $contenu, revue, parent_id FROM presse WHERE parent_id=".intval($presse['id'])." AND active=1 ORDER BY date_creation DESC");
    // affichage de la liste de tous les articles pour une catÃ©gorie parent_id=0
    if($DB_site->num_rows($req)>0){
		$listeurl="";
		$c=1;
		while($child=$DB_site->fetch_array($req)){
			// positionnement de l'article dans la liste "Dernier article vu dans la presse"

			if($child['parent_id'] == '3'){
				if(file_exists("configurations/".$host."/images/presse/".$child['id']."-logo.jpg")){
					$vign = "/images/presse/".$child['id']."-logo.jpg";
				}elseif(file_exists("configurations/".$host."/images/presse/".$child['id']."-logo.png")){
					$vign = "/images/presse/".$child['id']."-logo.png";
				}else{
					$vign = "/images/presse/".$child['id']."-vign.jpg";
				}
			}else{
				$vign = "/images/presse/".$child['id']."-vign.jpg";
			}
			//$img=img_in_html($child[$contenu]);
			$listeurl .= ""
			. "<div class=\"col-xs-12 col-sm-6 col-md-4 col-lg-2 no-padding\" ><div class=\"articlepresse\">"
				. "<a href=\"/presse/".$child['url'].".htm\" class=\"img\"><img src=\"".$vign."\" alt=\"".$child['revue']."\"></a>"
				. "<a href=\"/presse/".$child['url'].".htm\" class=\"revue\">".$child['revue']."</a>"
				. "<h2><a href=\"/presse/".$child['url'].".htm\">".$child['resume']."</a></h2>"
			. "</div></div>";
		}
		// Listes presses
		$listeurl.="<div class=\"clear\"></div>";
		// ajout de la description du type de la liste 
		$template->parts["content"]="<div class=\"institutionnel\">".$presse[$contenu]."</div>";
		// ajout de la liste
		$template->parts["content"].=$listeurl;
    }
    elseif($DB_site->num_rows($req)==0 && $presse['parent_id']==0) {
    	$template->parts["content"]="<div class=\"institutionnel\">".$presse[$contenu]."</div>";
    }
} else { // ID 0 : page principale (hors main/index), presentation des medias
	$template->parts["content"] = "<div class=\"institutionnel\">".$presse[$contenu]."</div>";
	// recuperation des infos pour parent_id = 0 => retrouve les ID 3 et 4...
   	$req=$DB_site->query("SELECT id,$libelle AS libelle,url".$template->langue." AS url,$contenu,revue FROM presse WHERE parent_id=0 AND active=1 ORDER BY date_creation DESC");
   	if($DB_site->num_rows($req) > 0){
		// Page Presse pour les lignes avec parent_id=0 (Ex: ID 3 Web et ID 4 Presse)
		while($child=$DB_site->fetch_array($req)){ 
		    	$template->parts["content"] .= ""
	    		. "<div class=\"presse\">"
	    				. "<a href=\"/presse/".$child['url'].".htm\" class=\"presse_title\">".$child['libelle']."</a>"
	    				. "<div class=\"presse_description\">".$child[$contenu]."</div>"
	    				. "<a href=\"/presse/".$child['url'].".htm\"><img src=\"/images/templates/presse".$child['id'].".jpg\" class=\"img\" alt=\"".$template->translate("/site/nom")." ".$child['libelle']."\"></a>"
	    				. "<div class=\"clear\"></div>"
	    				. "<a href=\"/presse/".$child['url'].".htm\" class=\"presse_decouvre\">Découvrir <i class=\"fa fa-chevron-right\"></i></a>"
	    				. "<div class=\"clear\"></div>"
				. "</div>";
 		}
 	}
}
//var_dump($presse);
$template->parts["fil_ariane"].=" &gt; <span property=\"itemListElement\" typeof=\"ListItem\"><span property=\"name\">".$presse[$libelle]."</span><meta property=\"position\" content=\"".$pos."\"></span>";
$template->parts["title"]=$presse[$libelle];
$template->parts["remarketing"]=google_remarketing('',"siteview",'');

//ici on fais afficher les balise hreflang pour FR et EN
foreach($template->sites() as $site_code=>$site){
    foreach($site['hosts'] as $url=>$deflangue){
    if($site['actif'][$url]){
        if($presse['url'.$deflangue]!='')
            $template->parts["alternate"].="<link rel=\"alternate\" hreflang=\"".$site['locales'][$deflangue]."\" href=\"https://www.".$url."/presse/".$presse['url'.$deflangue].".htm\">\n";
        }
    }
}
include("barrefixe.php");
$template->pageparams($host,$presse['ref_title'.$template->langue]." - ".$template->translate("/site/nom"),$presse['ref_description'.$template->langue]);
$template->evaltemplate("PressePage","main");
$template->out();
?>