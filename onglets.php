<?php
//Instanciations

$oCategorie = new categorie($DB_site, 0, $template->langue);
$oCategorie->setparam("pro",($sitepro?2:1));
$oCategorie->setparam("site_code",$template->site_code());

$aFinitionsCouleurs = array(
	1 => 'noir',
	2 => 'gris',
	3 => 'marron',
	4 => 'beige'
);

$aFiltresTermes = array(
	'cave de service',
	'cave a vin',
	'cave &agrave; vin',
	'cave ? vin',
	'cave multi-usages',
	'cave à vin'
);


$Menus=$oCategorie->getMenus();
$template->parts["sousmenusRespCache"] = "";
//R?cup?ration du menu
$calcul = 0;

foreach($Menus as $num => $le_menu){

// 	$template->parts["sousmenus"]     = "";
	$template->parts["sousmenusResp"] = "";
	$template->parts["sousmenusbloc"] = "";
	$template->parts["sousmenusbloc2"] = "";

	$template->parts["menunum"]     = $num;
	$template->parts["menulibelle"] = $le_menu['display'];
	$template->parts["menulibelle2"]= $le_menu['displayResp'];
	if($sitepro) {
		$classpro = "hover-pro";
		$classrondpro = "rond-pro";
		$template->parts["classpro"] = "hover-pro";
		$template->parts["backgroundcolorpro"] = "style='background-color: #ADC0CE;'";
	}

	////MENU DESKTOP////
	
	$i = 1;
	$style = "";
	//On fait une boucle pour les 4 colonnes de chaque Menu
	while($i < 5){
		
		//On récupère les données de menu_gestion.php
		$previs= $DB_site->query("SELECT * FROM menu_bloc mb INNER JOIN categorie c ON mb.id_categorie = c.catid WHERE id_menu = ".$num." AND colonne = ".$i." ORDER BY ordre");
		
		$div_colonne = $div_colonne_resp = "";
		$compteur_cat = 1;

		while($previ=$DB_site->fetch_array($previs)){

			//On affiche que les catégories/souscat/etc qui ont des articles actifs ET qui doivent être affichées
			if(count($oCategorie->getArticles($previ['catid'])) > 0 && $previ['affichage_menu'] == '1'){

				$style = "";
				//Les sous catégories principales :
				if($previ['menu_appartenance'] !== '0'){
					if($compteur_cat > 1){
						$style = 'margin-top: 13px;';
					}

					$oSousCat = new categorie($DB_site, $previ['catid'], $template->langue);
					$souscategorie_url = $template->mkurl("categorie", $previ['catid'], $oSousCat);
					$div_colonne .= "<div class=\"niveau2 $classpro\" style=\"".$style."\"><p><a href=\"".$souscategorie_url."\" class=\"titre\">".$previ['libelleFR']."</a></p></div>";
					//Pour le mobile :
					$div_colonne_resp .= "<div class=\"niveau2-2\"><p><a href=\"".$souscategorie_url."\" class=\"titre\">".$previ['libelleFR']."</a></p></div>";

				}
				//Les sous-sous catégories
				else{
					$oSousSousCat = new categorie($DB_site, $previ['catid'], $template->langue);
					$soussouscategorie_url = $template->mkurl("categorie", $previ['catid'], $oSousSousCat);

					$div_colonne .= "<div class=\"niveau2 $classpro\"><li class='souscat $classrondpro'><a href='".$soussouscategorie_url."'><span class='rond ".$classrondpro."'>&#9679;</span> ".$previ['libelleFR']."</a></li>";

					$req_sousCatNiv3=$DB_site->query("SELECT c.libelle".$template->langue." as 'libelle',c.catid FROM categorie c INNER JOIN article a ON c.catid = a.catid WHERE parentid = ".$previ['catid']." AND a.active = '1' GROUP BY c.catid ORDER BY position ASC");

					// et catégories niveau inférieur :
					while($res_sousCatNiv3=$DB_site->fetch_array($req_sousCatNiv3)){

						$oSousCatNiv3 = new categorie($DB_site, $res_sousCatNiv3['catid'], $template->langue);
						$oSousCatNiv3_url = $template->mkurl("categorie", $res_sousCatNiv3['catid'], $oSousCatNiv3);

						$div_colonne .= "<li class='souslibelle'><a href='".$oSousCatNiv3_url."'>".$res_sousCatNiv3['libelle'] ."</a></li>";
					}
					$div_colonne .= "</div>";
				}
				$compteur_cat++;
			}
		}
		$i++;
		$template->parts['sousmenusbloc'] .= "<div class=\"menu_colonne\">".$div_colonne."</div>";
		//Pour le mobile :
		$template->parts['sousmenusbloc2'] .= $div_colonne_resp;
	}


	////MENU RESPONSIVE TABLETTE
	$i = 1;
	$style = $template->parts['sousmenusblocResp'] = "";


	//On fait une boucle pour les 3 colonnes de chaque Menu resp
	while($i < 4){
		
		//On récupère les données de menu_gestion.php
		$previs= $DB_site->query("SELECT * FROM menu_bloc mb INNER JOIN categorie c ON mb.id_categorie = c.catid WHERE id_menu = ".$num." AND colonne_resp = ".$i." ORDER BY ordre_resp");
		
		$div_colonne = "";
		$compteur_cat = 1;

		while($previ=$DB_site->fetch_array($previs)){

			//On affiche que les catégories/souscat/etc qui ont des articles actifs ET qui doivent être affichées
			if(count($oCategorie->getArticles($previ['catid'])) > 0 && $previ['affichage_menu'] == '1'){

				$style = "";
				//Les sous catégories principales :
				if($previ['menu_appartenance'] !== '0'){
					if($compteur_cat > 1){
						$style = 'margin-top: 13px;';
					}

					$oSousCat = new categorie($DB_site, $previ['catid'], $template->langue);
					$souscategorie_url = $template->mkurl("categorie", $previ['catid'], $oSousCat);
					$div_colonne .= "<div class=\"niveau2 $classpro\" style=\"".$style."\"><p><a href=\"".$souscategorie_url."\" class=\"titre\">".$previ['libelleFR']."</a></p></div>";

				}
				//Les sous-sous catégories
				else{
					$oSousSousCat = new categorie($DB_site, $previ['catid'], $template->langue);
					$soussouscategorie_url = $template->mkurl("categorie", $previ['catid'], $oSousSousCat);

					$div_colonne .= "<div class=\"niveau2 $classpro\"><li class='souscat $classrondpro'><a href='".$soussouscategorie_url."'><span class='rond ".$classrondpro."'>&#9679;</span> ".$previ['libelleFR']."</a></li>";

					$div_colonne .= "</div>";
				}
				$compteur_cat++;
			}
		}
		$i++;
		$template->parts['sousmenusblocResp'] .= "<div class=\"menu_colonne\">".$div_colonne."</div>";
	}
	
	$template->evaltemplate("MenuSecondaire","sousmenus");
	//Pour le mobile :
	$template->evaltemplate("MenuSecondaireResp", "sousmenusResp");

	$template->evaltemplate("Menu","menu");
	//Pour le mobile :
	$template->evaltemplate("MenuResp","menuresp");

}


?>