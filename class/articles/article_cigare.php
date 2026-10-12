<?php
  function article_cigare_fields() {
    return array(
      array(
	'capacite'	=> array(
	  'capacite'									=> array(CHAMP_NUMERIC, 'Capacité maximale','Cigares'),
	  'volume'										=> array(CHAMP_NUMERIC,'Volume utile','L',false,true),
	  'dimension_interieur_h'			=> array(CHAMP_NUMERIC, 'Hauteur intérieure','mm'),
	  'dimension_interieur_l'			=> array(CHAMP_NUMERIC, 'Largeur intérieure','mm'),
	  'dimension_interieur_p'			=> array(CHAMP_NUMERIC, 'Profondeur intérieure','mm'),
	),
	'Design'		=> array(
	  'coloris'							=> array(CHAMP_TRAD, 'Coloris',''),
	  'type_ouverture'			=> array(CHAMP_TRAD, 'Type d\'ouverture','',false,true),
	  'coloris_cadre'				=> array(CHAMP_TRAD, 'Coloris du cadre de la porte','',false,true),
	  'caract_vitrage'			=> array(CHAMP_TRAD, 'Caractéristique vitrage','',false,true),
	  'antiuv'							=> array(CHAMP_MULTI, 'Traitement anti-UV','',false,true),
	  'coloris_vitrage'			=> array(CHAMP_TRAD, 'Coloris vitrage','',false,true),
	  'habillage_ext'				=> array(CHAMP_TRAD, 'Habillage extérieur','',false,true),
	  'revetement_int'			=> array(CHAMP_TRAD, 'Revêtement intérieur','',false,true),
	  'coloris_int'					=> array(CHAMP_TRAD, 'Coloris intérieur','',false,true),
	  'eclairage'						=> array(CHAMP_TRAD, 'Éclairage intérieur','',false,true),
	  'coloris_eclairage'		=> array(CHAMP_TRAD, 'Coloris de l\'éclairage','',false,true),
	  'position_eclairage'	=> array(CHAMP_TRAD, 'Position de l\'éclairage','',false,true),
	  'ouverture_porte'			=> array(CHAMP_TRAD, 'Ouverture de la porte','',false,true),
	  'porte_reversible'		=> array(CHAMP_MULTI, 'Porte réversible','',false,true),
	  'poignee'							=> array(CHAMP_TRAD, 'Poignée/Ouverture','',false,true),
	  'niveau_gamme'				=> array(CHAMP_MULTI, 'Niveau de gamme','',false,true),
	),
	'technique_regulation'	=> array(
	  'humidification'				=> array(CHAMP_TRAD, 'Système d\'humidification ','',false,true),
	  'affichage_hygro'				=> array(CHAMP_TRAD, 'Affichage de l\'hygrométrie',''),
	  'regulation_temp'				=> array(CHAMP_TRAD, 'Régulation de température','',false,true),
	  'regulation_thermostat'	=> array(CHAMP_TRAD, 'Régulation/Thermostat','',false,true),
	  'reglage_temp'					=> array(CHAMP_TRAD, 'Plage de réglage de la température','',false,true),
	  'affichage_temp'				=> array(CHAMP_TRAD, 'Affichage de la température','',false,true),
	  'position_thermostat'		=> array(CHAMP_TRAD, 'Position thermostat','',false,true),
	  'btn_reglage'						=> array(CHAMP_TRAD, 'Boutons de réglage','',false,true),
	  'degivrage_auto'				=> array(CHAMP_TRAD, 'Dégivrage automatique','',false,true),
	  'antivib'								=> array(CHAMP_TRAD, 'Système anti-vibrations','',false,true),
	  'technologie_specifique'	=> array(CHAMP_TRAD, 'Technologie(s) spécifique(s)','',false,true),
	  'technologie_specifique1'	=> array(CHAMP_TRAD, 'Technologie(s) spécifique(s)','',false,true),
	  'technologie_specifique2'	=> array(CHAMP_TRAD, 'Technologie(s) spécifique(s)','',false,true),
	  'technologie_specifique3'	=> array(CHAMP_TRAD, 'Technologie(s) spécifique(s)','',false,true),
	),
	'equipements'		=> array(
	  'amenagement_int'			=> array(CHAMP_TRAD, 'Aménagement intérieur personnalisable', '',false,true),
	  'reglage_clayettes'		=> array(CHAMP_NUMERIC, 'Hauteur de réglage des clayettes', 'mm',false,true),
	  'serrure'							=> array(CHAMP_TRAD, 'Serrure', '',false,true),
	  'serrure_recherche'		=> array(CHAMP_MULTI, 'Serrure (affinage recherche, non visible)', '',false,true),
	  'filtre_charbon'			=> array(CHAMP_TRAD, 'Filtre à charbon', '',false,true),
	  'alarme'							=> array(CHAMP_TRAD, 'Alarme(s)', '',false,true),
	  'fonction_hiver'			=> array(CHAMP_TRAD, 'Fonction Hiver', '',false,true),
	  'pieds_reglables'			=> array(CHAMP_TRAD, 'Pieds réglables', '',false,true),
	  'equipement_specifique'	=> array(CHAMP_TRAD, 'Équipement(s) spécifique(s)','',false,true),
	  'equipement_specifique1'	=> array(CHAMP_TRAD, 'Équipement(s) spécifique(s)','',false,true),
	  'equipement_specifique2'	=> array(CHAMP_TRAD, 'Équipement(s) spécifique(s)','',false,true),
	  'equipement_specifique3'	=> array(CHAMP_TRAD, 'Équipement(s) spécifique(s)','',false,true),
	),
	'dimensions' => array(
    //'dimensions_hlp'	=> array(CHAMP_TXT,'Dimensions', 'HxLxP mm',false),
    'hauteur' 				=> array(CHAMP_NUMERIC,'Hauteur nette', 'mm'),
    'largeur'				 	=> array(CHAMP_NUMERIC,'Largeur nette', 'mm'),
    'profondeur' 			=> array(CHAMP_NUMERIC,'Profondeur nette', 'mm'),
    'poids' 					=> array(CHAMP_NUMERIC,'Poids net', 'kg'),
	),
	'installation2'		=> array(
	  'type_pose'						=> array(CHAMP_MULTI, 'Installation(s) possible(s)','',false,true),
	  'infos_encastrement'	=> array(CHAMP_TRAD, 'Infos pour encastrement','',false,true),
	  'niche_hauteur'				=> array(CHAMP_NUMERIC, 'Hauteur minimum de la niche d\'encastrement', 'mm'),
	  'niche_largeur'				=> array(CHAMP_NUMERIC, 'Largeur minimum de la niche d\'encastrement', 'mm'),
	  'niche_profondeur'		=> array(CHAMP_NUMERIC, 'Profondeur minimum de la niche d\'encastrement', 'mm'),
	  //'temp_piece'					=> array(CHAMP_NUMERIC, 'Température de la pièce d\'installation mini/maxi', '°C'),
	  'temp_instal_max'			=> array(CHAMP_LISTE_TEMP, 'Température d\'installation mini (°C)'),
	  'temp_instal_min'			=> array(CHAMP_LISTE_TEMP, 'Température d\'installation maxi (°C)'),
	),
	'technique_performances'		=> array(
		'class_energetique' => array(CHAMP_LISTE_EN,'Classe énergétique ', false,false),
	  'conso_an'		=> array(CHAMP_NUMERIC, 'Consommation annuelle', 'Kwh/an'),
	  //'conso_kw'		=> array(CHAMP_NUMERIC, 'Consommation journalière', 'Kwh/24h',false,true),
	  'conso_euro'		=> array(CHAMP_NUMERIC, 'Coût de fonctionnement annuel', '"€/an – 0.17 € kW/h',false,true),
	  'niveau_bruit'		=> array(CHAMP_NUMERIC, 'Niveau sonore', 'dB',false,true),
	  'niveau_bruit_abcd'		=> array(CHAMP_TRAD, 'Niveau sonore (ABCD)', '',false,true),
	  'puissance_totale' => array(CHAMP_NUMERIC,'Puissance absorbée', 'W'),
    'intensite_electrique' => array(CHAMP_NUMERIC,'Intensité absorbée', 'A',false),
    'classe_climatique' => array(CHAMP_TXT,'Classe climatique', '', false),
    'gaz_froid' => array(CHAMP_TRAD,'Fluide réfrigérant', '', false),
    'alim_elec' => array(CHAMP_TRAD,'Alimentation électrique', '',false),
    'fiche_energie' => array(CHAMP_PDF,'Étiquette énergie', '',false),
	),	 
 'service' => array(
      'dispo_pieces' => array(CHAMP_NUMERIC,'Disponibilité des pièces détachées', 'ans'),
    ),
      )
    );
  }
  function article_cigare_show($tab, $title) {
    article_global_show($tab, $title, true); // true = champs groupés
  }
  function article_cigare_update($tab, $title) {
    article_global_update($tab, $title, true); // true = champs groupés
  }
?>