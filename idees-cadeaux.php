<?php

include_once("global.php");


$showcat=false;
$idkdoscat=new categorie($DB_site,0,$template->langue);
$idkdoscat->idkdos_load();
if($template->langue=="EN"){
	$mainpage="gift-ideas.php";
} else {
	$mainpage="idees-cadeaux.php";
}
$template->parts["fil_ariane"].=" &gt; <span property=\"itemListElement\" typeof=\"ListItem\"><a property=\"item\" typeof=\"WebPage\" href=\"".$mainpage."\"><span property=\"name\">".$idkdoscat->idkdos_libelle."</span></a><meta property=\"position\" content=\"2\"></span>";
    
$libelletranche="";

$template->parts["title"]=$idkdoscat->idkdos_libelle.$libelletranche;
$template->parts["description"]=$idkdoscat->idkdos_description;

$logopromo=$DB_site->query_first("SELECT * FROM logopromo WHERE type='idkdos' AND `".($sitepro?"pro":"part")."`=1 AND langue='".$template->langue."'");
if($logopromo!="") $template->parts["description_img"]="<img src=\"/images/logopromo/".$logopromo['id'].".jpg\" alt=\"".$template->translate("/categorie/idees_cadeaux")."\" class=\"img img-responsive\">";



$ranges=array(
			array("start"=>0,	"end"=>50),
			array("start"=>50,	"end"=>100),
			array("start"=>100,	"end"=>200),
			array("start"=>200,	"end"=>300),
			array("start"=>300,	"end"=>500),
			array("start"=>500,	"end"=>1000),
			array("start"=>1000,"end"=>2000)
		);
$template->parts["articles"]="";
$artidsaffiches=array(0);
foreach($ranges as $range){
	$i=0;
	// tiens compte des prix temporaires et des prix vente flash ...
	/*
	$kdos = $DB_site->query("									
								SELECT DISTINCT(artid),article.* FROM idkdos
								INNER JOIN article USING(artid)
								LEFT JOIN temp_price AS tp USING(artid)
								LEFT JOIN vente_flash AS vf ON idkdos.site_code=vf.site_code
								LEFT JOIN vente_flash_article AS vfa ON vf.id=vfa.vfid
								WHERE idkdos.site_code='".$template->site_code()."'
								AND article.artcode=vfa.artcode
								AND artid NOT IN(".implode(",",$artidsaffiches).")
								AND (
									( article.prix>".$range['start']." AND article.prix<=".$range['end']." )
									OR
									( tp.prix>".$range['start']." AND tp.prix<=".$range['end']." AND tp.du<".time()." AND tp.au>".time()." AND tp.site_code='".$template->site_code()."')
									OR
									( vfa.prix>".$range['start']." AND vfa.prix<=".$range['end'].")
								)
								ORDER BY article.prix ASC "
								);
	*/
	$kdos = $DB_site->query("	SELECT * FROM idkdos
								INNER JOIN article USING(artid)
								WHERE site_code='".$template->site_code()."'
								AND article.prix>".$range['start']." AND article.prix<=".$range['end']." 
								ORDER BY article.prix ASC");
	
	while($kdo=$DB_site->fetch_array($kdos)){
		$artidsaffiches[]=$kdo['artid'];
		if($i==0){
			if($template->langue=="EN"){
				if($range['start']==0){
					$template->parts["articles"].=	"<div class=\"description_bloc\">
													<h3 align=\"left\" style=\"font-weight: 700\">Gift ideas under ".$range['end']." euros</h3>
												</div><div class=\"clear\">&nbsp;</div>";
				} else {
					$template->parts["articles"].=	"<div class=\"description_bloc\">
													<h3 align=\"left\" style=\"font-weight: 700\">Gift ideas from ".$range['start']." to ".$range['end']." euros</h3>
												</div><div class=\"clear\">&nbsp;</div>";
				}
			} else {
				if($range['start']==0){
					$template->parts["articles"].=	"<div class=\"description_bloc\">
													<h3 align=\"left\" style=\"font-weight: 700\">Idées cadeaux à moins de ".$range['end']." euros</h3>
												</div><div class=\"clear\">&nbsp;</div>";
				} else {
					$template->parts["articles"].=	"<div class=\"description_bloc\">
													<h3 align=\"left\" style=\"font-weight: 700\">Idées cadeaux de ".$range['start']." à ".$range['end']." euros</h3>
												</div><div class=\"clear\">&nbsp;</div>";
				}
			}
			$i++;
		}
		$template->parts["class_sup"] = '';
		$noncommandable = "";
		$sql = "SELECT commandable, libelle".$template->langue." AS libelle";
		$sql .= " FROM article";
		$sql .= " WHERE artid = ".intval($kdo[artid]);
		$article = $DB_site->query_first($sql);
		$template->parts["articles"].=vignette($kdo[artid],"categorie");
	}
}






if(!$showcat){
    foreach($template->sites() as $site_code=>$site){
		foreach($site['hosts'] as $url=>$deflangue){
			if($site['actif'][$url]){
			$sitecategorie=new categorie($DB_site,0,$deflangue);
			$sitecategorie->idkdos_load();
			$template->parts["alternate"].="<link rel=\"alternate\" hreflang=\"".$site['locales'][$deflangue]."\" href=\"https://www.".$url.$sitecategorie->idkdos_url."\">\n";
			}
		}
    }
}


$encarts=encarts($template->site_code(),$template->langue,"idkdos",0);
foreach($encarts as $encart)
    $template->evaltemplate($encart,"encarts");

include("barrefixe.php");

$template->pageparams($host,$idkdoscat->idkdos_ref_title.$libelletranche." - ".$template->translate("/site/nom"),$idkdoscat->idkdos_ref_title.". ".$template->parts["description"]);
$template->evaltemplate("KdosPage","main");

$template->out();

?>