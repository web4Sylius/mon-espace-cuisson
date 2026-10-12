<?php
function article_meubles_fields() {
	return array(
    	array(
			'capacite'	=> array(
			  'capacite'								=> array(CHAMP_NUMERIC, 'Capacité avec l\'équipement standard','bouteilles Bordeaux 0,75L'),
			  'capacite_brut'						=> array(CHAMP_NUMERIC, 'Capacité sans empilage','bouteilles Bordeaux 0,75L'),
			  'capacite_max'						=> array(CHAMP_NUMERIC, 'Capacité maximum','bouteilles Bordeaux 0,75L'),
			  'capacite_magnum'					=> array(CHAMP_NUMERIC, 'Capacité','bouteilles type Magnum 1,5L'),
			  'capacite_champagne'			=> array(CHAMP_NUMERIC, 'Capacité','bouteilles type Champagne'),
			  'capacite_verres'					=> array(CHAMP_NUMERIC, 'Capacité','verres'),
			  'longeur_max_bouteille'		=> array(CHAMP_NUMERIC, 'Longueur maximum des bouteilles','mm'),
			  'diametre_max_bouteille'	=> array(CHAMP_NUMERIC, 'Diamètre maximum des bouteilles','mm'),
			  //'diametre_caisses_max'		=> array(CHAMP_TXT, 'Dimensions maxi caisses-bois','HxLxP mm'),
			  'hauteur_caisses_max'			=> array(CHAMP_NUMERIC, 'Hauteur maxi Caisses-bois','mm'),
			  'longueur_caisses_max'		=> array(CHAMP_NUMERIC, 'Largeur maxi Caisses-bois','mm'),
			  'profondeur_caisses_max'	=> array(CHAMP_NUMERIC, 'Profondeur maxi Caisses-bois','mm'),
			),
			'Design'		=> array(
			  'coloris'					=> array(CHAMP_TRAD, 'Coloris principal',''),
			  'matiere'					=> array(CHAMP_TRAD, 'Matière',''),
			  'type_porte'			=> array(CHAMP_TRAD, 'Type de porte','',false,true),
			  'coloris_cadre'		=> array(CHAMP_TRAD, 'Coloris du cadre de la porte','',false,true),
			  'habillage_ext'		=> array(CHAMP_TRAD, 'Habillage extérieur','',false,true),
			  'parois_laterales'=> array(CHAMP_TRAD, 'Parois latérales intérieures','',false,true),
			  'parois_fond'			=> array(CHAMP_TRAD, 'Paroi du fond','',false,true),
			  'poignee'					=> array(CHAMP_TRAD, 'Type de Poignée','',false,true),
			  'niveau_gamme'		=> array(CHAMP_MULTI, 'Niveau de gamme','',false,true),	  
			),
			'dimensions' => array(
					//'dimensions_hlp'	=> array(CHAMP_TXT,'Dimensions', 'HxLxP mm',false),
			    'hauteur' 			=> array(CHAMP_NUMERIC,'Hauteur nette', 'mm'),
			    'largeur'				=> array(CHAMP_NUMERIC,'Largeur nette', 'mm'),
			    'profondeur' 		=> array(CHAMP_NUMERIC,'Profondeur nette', 'mm'),
			    'poids' 				=> array(CHAMP_NUMERIC,'Poids net', 'kg'),
			    'volume'				=> array(CHAMP_NUMERIC, 'Volume intérieur', 'L',false,true),
			),
			'equipements'		=> array(
			  'amenagement_int'		=> array(CHAMP_TRAD, 'Aménagement intérieur personnalisable', '',false,true),
			  'nb_etageres_max'		=> array(CHAMP_NUMERIC, 'Nombre d’étagères maxi par meuble', '',false,true),
			  'serrure'						=> array(CHAMP_TRAD, 'Serrure', '',false,true),
			  'serrure_recherche'	=> array(CHAMP_MULTI, 'Serrure (affinage recherche, non visible)', '',false,true),
			  'eclairage_led'			=> array(CHAMP_TRAD, 'Eclairage LED', '',false,true), 
			),
			'installation'					=> array(
			  'fixation_a_prevoir'	=> array(CHAMP_TRAD, 'Fixations à prévoir','',false,true),
			  'fixation_fournie'		=> array(CHAMP_MULTI, 'Fixations fournies', ''),
			  'pieds_reglables'			=> array(CHAMP_TRAD, 'Pieds réglables', '',false,true),
			  'roulettes'						=> array(CHAMP_TRAD, 'Roulettes', '',false,true),
			),    
    	));
  }

  function article_meubles_show($tab, $title) {
    article_global_show($tab, $title, true); // true = champs groupés
  }
  function article_meubles_update($tab, $title) {
    article_global_update($tab, $title, true); // true = champs groupés
  }
?>