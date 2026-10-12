<?php

require "admin_global.php";

header('Content-type: text/csv');

// $datedebut=1356994800;


echo "catid;menu;parent1;parent2;parent3;libelleFR;nb_art;nb_art_actif;nb_art_virtuel;lien;descriptionFR\n";

/*
$req=$DB_site2->query("SELECT * FROM categorie ORDER BY catid ASC ");
while($res1=$DB_site2->fetch_array($req)){
	$parent1="";
	$parent2="";
	$parent3="";
	$menu_libelle="";
	if($res1[parentid]>0){
		
		
		$res2=$DB_site2->query_first("SELECT parentid, libelleFR, menu_appartenance FROM categorie WHERE catid='".$res1[parentid]."' ");
		$parent1=$res2[libelleFR];
		if($res2[parentid]>0){
			
			
			$res3=$DB_site2->query_first("SELECT parentid, libelleFR, menu_appartenance FROM categorie WHERE catid='".$res2[parentid]."' ");
			$parent2=$parent1;
			$parent1=$res3[libelleFR];
			if($res3[parentid]>0){
				
			
				$res4=$DB_site2->query_first("SELECT parentid, libelleFR, menu_appartenance FROM categorie WHERE catid='".$res3[parentid]."' ");
				$parent3=$parent2;
				$parent2=$parent1;
				$parent1=$res4[libelleFR];
			} else {
				if($res3[menu_appartenance]==0){
					$menu_libelle="invisible";
				} else {
					$menu=$DB_site2->query_first("SELECT libelleFR FROM menus WHERE idmenu='".$res3[menu_appartenance]."' ");
					$menu_libelle=$menu[libelleFR];
				}
				$parent3="";
			}
		} else {
			if($res2[menu_appartenance]==0){
				$menu_libelle="invisible";
			} else {
				$menu=$DB_site2->query_first("SELECT libelleFR FROM menus WHERE idmenu='".$res2[menu_appartenance]."' ");
				$menu_libelle=$menu[libelleFR];
			}
			$parent2="";
		}
	} else {
		if($res1[menu_appartenance]==0){
			$menu_libelle="invisible";
		} else {
			$menu=$DB_site2->query_first("SELECT libelleFR FROM menus WHERE idmenu='".$res1[menu_appartenance]."' ");
			$menu_libelle=$menu[libelleFR];
		}
		$parent1="";
	}
	
	$nb_article=$DB_site2->query_first("SELECT COUNT(*) FROM article WHERE catid='".$res1[catid]."' ");
	$nb_article_actif=$DB_site2->query_first("SELECT COUNT(*) FROM article WHERE catid='".$res1[catid]."' AND active='1' ");
	// $res1[libelleFR]=htmlspecialchars_decode($res1[libelleFR], ENT_QUOTES);
	echo utf8_decode($res1[catid].";".html_entity_decode($menu_libelle).";".html_entity_decode($parent1).";".html_entity_decode($parent2).";".html_entity_decode($parent3).";".html_entity_decode($res1[libelleFR]).";".$nb_article[0].";".$nb_article_actif[0].";".strip_tags(html_entity_decode($res1[descriptionFR]))."\n");
}
*/

$req=$DB_site2->query("SELECT * FROM categorie WHERE menu_appartenance>0 ORDER BY menu_appartenance, position ASC ");
while($res1=$DB_site2->fetch_array($req)){
	$parent1="";
	$parent2="";
	$parent3="";
	$menu=$DB_site2->query_first("SELECT libelleFR FROM menus WHERE idmenu='".$res1[menu_appartenance]."' ");
	$menu_libelle=$menu[libelleFR];
	$nb_article=$DB_site2->query_first("SELECT COUNT(*) FROM article WHERE catid='".$res1[catid]."' ");
	$nb_article_actif=$DB_site2->query_first("SELECT COUNT(*) FROM article WHERE catid='".$res1[catid]."' AND active='1' ");
	$nb_article_actif_virtuel=$DB_site2->query_first("	SELECT COUNT(*) FROM vcategorie AS v
													INNER JOIN article AS a USING(artid)
													WHERE v.catid='".$res1[catid]."' AND a.active='1' ");
	$res1[libelleFR]=htmlspecialchars_decode($res1[libelleFR], ENT_QUOTES);
	$res1[descriptionFR]=str_replace("&rsquo;","'",$res1[descriptionFR]);
	echo utf8_decode($res1[catid].";".html_entity_decode($menu_libelle).";".html_entity_decode($parent1).";".html_entity_decode($parent2).";".html_entity_decode($parent3).";".html_entity_decode($res1[libelleFR]).";".$nb_article[0].";".$nb_article_actif[0].";".$nb_article_actif_virtuel[0].";https://".$host."/cat,".$res1[catid].",a.htm;".strip_tags(html_entity_decode($res1[descriptionFR]))."\n");
	$req2=$DB_site2->query("SELECT * FROM categorie WHERE parentid='".$res1[catid]."' ");
	while($res2=$DB_site2->fetch_array($req2)){
		$parent1=$res1[libelleFR];
		$parent2="";
		$parent3="";
		$nb_article=$DB_site2->query_first("SELECT COUNT(*) FROM article WHERE catid='".$res2[catid]."' ");
		$nb_article_actif=$DB_site2->query_first("SELECT COUNT(*) FROM article WHERE catid='".$res2[catid]."' AND active='1' ");
		$nb_article_actif_virtuel=$DB_site2->query_first("	SELECT COUNT(*) FROM vcategorie AS v
														INNER JOIN article AS a USING(artid)
														WHERE v.catid='".$res2[catid]."' AND a.active='1' ");
		$res2[libelleFR]=htmlspecialchars_decode($res2[libelleFR], ENT_QUOTES);
		$res2[descriptionFR]=str_replace("&rsquo;","'",$res2[descriptionFR]);
		echo utf8_decode($res2[catid].";".html_entity_decode($menu_libelle).";".html_entity_decode($parent1).";".html_entity_decode($parent2).";".html_entity_decode($parent3).";".html_entity_decode($res2[libelleFR]).";".$nb_article[0].";".$nb_article_actif[0].";".$nb_article_actif_virtuel[0].";https://".$host."/cat,".$res2[catid].",a.htm;".strip_tags(html_entity_decode($res2[descriptionFR]))."\n");
		$req3=$DB_site2->query("SELECT * FROM categorie WHERE parentid='".$res2[catid]."' ");
		while($res3=$DB_site2->fetch_array($req3)){
			$parent2=$res2[libelleFR];
			$parent3="";
			$nb_article=$DB_site2->query_first("SELECT COUNT(*) FROM article WHERE catid='".$res3[catid]."' ");
			$nb_article_actif=$DB_site2->query_first("SELECT COUNT(*) FROM article WHERE catid='".$res3[catid]."' AND active='1' ");
			$nb_article_actif_virtuel=$DB_site2->query_first("	SELECT COUNT(*) FROM vcategorie AS v
															INNER JOIN article AS a USING(artid)
															WHERE v.catid='".$res3[catid]."' AND a.active='1' ");
			$res3[libelleFR]=htmlspecialchars_decode($res3[libelleFR], ENT_QUOTES);
			$res3[descriptionFR]=str_replace("&rsquo;","'",$res3[descriptionFR]);
			echo utf8_decode($res3[catid].";".html_entity_decode($menu_libelle).";".html_entity_decode($parent1).";".html_entity_decode($parent2).";".html_entity_decode($parent3).";".html_entity_decode($res3[libelleFR]).";".$nb_article[0].";".$nb_article_actif[0].";".$nb_article_actif_virtuel[0].";https://".$host."/cat,".$res3[catid].",a.htm;".strip_tags(html_entity_decode($res3[descriptionFR]))."\n");
			$req4=$DB_site2->query("SELECT * FROM categorie WHERE parentid='".$res3[catid]."' ");
			while($res4=$DB_site2->fetch_array($req4)){
				$parent3=$res3[libelleFR];
				$nb_article=$DB_site2->query_first("SELECT COUNT(*) FROM article WHERE catid='".$res4[catid]."' ");
				$nb_article_actif_virtuel=$DB_site2->query_first("	SELECT COUNT(*) FROM vcategorie AS v
																INNER JOIN article AS a USING(artid)
																WHERE v.catid='".$res4[catid]."' AND a.active='1' ");
				$nb_article_actif=$DB_site2->query_first("SELECT COUNT(*) FROM article WHERE catid='".$res4[catid]."' AND active='1' ");
				$res4[libelleFR]=htmlspecialchars_decode($res4[libelleFR], ENT_QUOTES);
				$res4[descriptionFR]=str_replace("&rsquo;","'",$res4[descriptionFR]);
				echo utf8_decode($res4[catid].";".html_entity_decode($menu_libelle).";".html_entity_decode($parent1).";".html_entity_decode($parent2).";".html_entity_decode($parent3).";".html_entity_decode($res4[libelleFR]).";".$nb_article[0].";".$nb_article_actif[0].";".$nb_article_actif_virtuel[0].";https://".$host."/cat,".$res4[catid].",a.htm;".strip_tags(html_entity_decode($res4[descriptionFR]))."\n");
			}
		}
	}
}

?>