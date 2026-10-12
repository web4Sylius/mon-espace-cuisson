<?php
  function article_fondue_fields() {
    return array(
      array(
	'criteres_generaux'	=> array(
	  // champ			// traduire, texte du label
	  'fonction'			=> array(CHAMP_TRAD, 'Fonction','',true,true),
	  'energie'			=> array(CHAMP_TRAD, 'Énergie','',false,true),
	  'matiere_socle'		=> array(CHAMP_TRAD, 'Matière du socle','',false,true),
	  'forme'			=> array(CHAMP_TRAD, 'Forme','',false,true),
	  'nb_personnes'		=> array(CHAMP_TRAD, 'Nombre de personnes','',false,true),
	  'accessoires_inclus_fondue'	=> array(CHAMP_TRAD, 'Accessoires inclus','',false,true),
	  'thermostat_reglable'		=> array(CHAMP_TRAD, 'Thermostat réglable','',false,true),
	  'support_fourchette'		=> array(CHAMP_TRAD, 'Support rangement fourchette','',false,true),
	  'plateau_tournant'		=> array(CHAMP_TRAD, 'Plateau tournant','',false,true),
	  'autres_infos_fondue'		=> array(CHAMP_TRAD, 'Autres informations','',false,true),
	),
	'caquelon'		=> array(
	  'caquelon'			=> array(CHAMP_TRAD, 'Caquelon','',false,true),
	  'diametre_caquelon'		=> array(CHAMP_TRAD, 'Diamètre', 'cm',false,true),
	  'revetement_antiadhesif'	=> array(CHAMP_TRAD, 'Revetement anti-adhesif','',false,true),
	),
      )
    );
  }

  function article_fondue_show($tab, $title) {
    article_global_show($tab, $title, true); // true = champs groupés
  }
  function article_fondue_update($tab, $title) {
    article_global_update($tab, $title, true); // true = champs groupés
  }
?>