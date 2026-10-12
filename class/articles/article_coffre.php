<?php
function article_coffre_fields() {
	return array(
    	array(
			'criteres_generaux'	=> array(
				'type_ouverture'			=> array(CHAMP_TRAD, 'Type d\'ouverture','',false,true),
				'coloris'							=> array(CHAMP_TRAD, 'Coloris principal',''),
				'volume'							=> array(CHAMP_NUMERIC, 'Volume utile', 'L',false,true),
				'verrouillage_coffre' => array(CHAMP_TRAD, 'Système de verrouillage', '',false,true),
				'compatible_pc'				=> array(CHAMP_TRAD, 'Compatibilité ordinateur portable', '',false,true),
				'ouverture_urgence'		=> array(CHAMP_TRAD, 'Ouverture d\'urgence', '',false,true),
				'sens_porte' 					=> array(CHAMP_TRAD, 'Sens de la porte ', '',false,true),
				'porte_reversible'		=> array(CHAMP_MULTI, 'Porte réversible', '',false,true),
			),
			'equipements'		=> array(
			  'affichage'			=> array(CHAMP_TRAD, 'Type et position de l’affichage','',false,true),
				'eclairage'			=> array(CHAMP_TRAD, 'Éclairage intérieur','',false,true),
			),

			'Design'		=> array(
			  'niveau_gamme'		=> array(CHAMP_MULTI, 'Niveau de gamme','',false,true),
				'habillage_ext'		=> array(CHAMP_TRAD, 'Habillage extérieur','',false,true),
			  'revetement_int'	=> array(CHAMP_TRAD, 'Habillage intérieur','',false,true),  
			),

			'dimensions' => array(
			    //'dimensions_hlp'			=> array(CHAMP_TXT,'Dimensions', 'HxLxP mm',false),
			    'hauteur' 					=> array(CHAMP_NUMERIC,'Hauteur nette', 'mm'),
			    'largeur'				 	=> array(CHAMP_NUMERIC,'Largeur nette', 'mm'),
			    'profondeur' 				=> array(CHAMP_NUMERIC,'Profondeur nette', 'mm'),
			    'poids' 					=> array(CHAMP_NUMERIC,'Poids net', 'kg'),			    
			),
			'installation'		=> array(
			  'type_pose'			=> array(CHAMP_MULTI, 'Installation(s) possible(s)','',false,true),
			  //'niche_dimensions'		=> array(CHAMP_TXT, 'Dimensions de la niche d\'encastrement', 'HxLxP mm'),
			  'hauteur_encastrement'	=> array(CHAMP_NUMERIC, 'Hauteur minimum de la niche d\'encastrement','mm',false,true),
			  'largeur_encastrement'	=> array(CHAMP_NUMERIC, 'Largeur minimum de la niche d\'encastrement','mm',false,true),
			  'profondeur_encastrement'	=> array(CHAMP_NUMERIC, 'Profondeur minimum de la niche d\'encastrement','mm',false,true),
			  'infos_encastrement'	=> array(CHAMP_TRAD, 'Infos pour encastrement','',false,true),
			),
			'technique'		=> array(
		    	'alim_elec' 			=> array(CHAMP_TRAD,'Alimentation électrique', '',false),
		    	'dispo_pieces' => array(CHAMP_NUMERIC,'Disponibilité des pièces détachées', 'ans'),
			),
      
    	));
  }

  function article_coffre_show($tab, $title) {
    article_global_show($tab, $title, true); // true = champs groupés
  }
  function article_coffre_update($tab, $title) {
    article_global_update($tab, $title, true); // true = champs groupés
  }
?>