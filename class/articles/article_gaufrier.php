<?php
  function article_gaufrier_fields() {
    return array(
      array(
	'criteres_generaux'	=> array(
	  // champ			// traduire, texte du label
	  'fonction'			=> array(CHAMP_TRAD, 'Fonction','',true,true),
	  'energie'			=> array(CHAMP_TRAD, 'Énergie','',false,true),
	  'type_resistance'		=> array(CHAMP_TRAD, 'Type de résistance','',false,true),
	  'type_bruleur'		=> array(CHAMP_TRAD, 'Type de brûleur','',false,true),
	  'nb_gauffres'			=> array(CHAMP_TRAD, 'Nombre de gaufres','',false,true),
	  'voyant_lumineux'		=> array(CHAMP_TRAD, 'Voyant lumineux de contrôle','',false,true),
	  'thermostat_gauffre'		=> array(CHAMP_TRAD, 'Thermostat','',false,true),
	  'pieds'			=> array(CHAMP_TRAD, 'Pieds','',false,true),
	  'minuterie_elec'		=> array(CHAMP_TRAD, 'Minuterie électronique','',false,true),
	  'bac_recup'			=> array(CHAMP_TRAD, 'Bac de récupétation','',false,true),
	  'autres_infos_gauffre'	=> array(CHAMP_TRAD, 'Autres informations','',false,true),
	),
	'plaques'		=> array(
	  'type_plaque'			=> array(CHAMP_TRAD, 'Nombre et type jeux de plaques','',false,true),
	  'nb_plaque'			=> array(CHAMP_TRAD, 'Nombre de plaques','',false,true),
	  'matiere_plaque'		=> array(CHAMP_TRAD, 'Matière de la plaque','',false,true),
	  'plaque_antiadherentes'	=> array(CHAMP_TRAD, 'Plaques anti-adhérentes','',false,true),
	),
	'securite'		=> array(
	  'securite_gauffre'		=> array(CHAMP_TRAD, 'Sécurité','',false,true),
	),
      )
    );
  }

function article_gaufrier_show($tab, $title) {
    article_global_show($tab, $title, true); // true = champs groupés
  }
  function article_gaufrier_update($tab, $title) {
    article_global_update($tab, $title, true); // true = champs groupés
  }
?>