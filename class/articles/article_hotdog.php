<?php
  function article_hotdog_fields() {
    return array(
      array(
	'criteres_generaux'	=> array(
	  // champ			// traduire, texte du label
	  'thermostat_reglable'		=> array(CHAMP_TRAD, 'Thermostat réglable','',false,true),
	  'nettoyage_hotdog'		=> array(CHAMP_TRAD, 'Nettoyage','',false,true),
	  'commandes'			=> array(CHAMP_TRAD, 'Commandes','',false,true),
	  'interrupteur_marche_arret'	=> array(CHAMP_TRAD, 'Interrupteur marche/arret','',false,true),
	  'voyant_lumineux'		=> array(CHAMP_TRAD, 'Voyant lumineux','',false,true),
	  'support_cuisson'		=> array(CHAMP_TRAD, 'Support','',false,true),
	  'matiere_support_pain'	=> array(CHAMP_TRAD, 'Support pain','',false,true),
	  'support_amovible'		=> array(CHAMP_TRAD, 'Support amovible','',false,true),
	  'dimensions_compartiment'	=> array(CHAMP_TRAD, 'Dimensions du compartiment (diamètre/hauteur)', 'cm',false,true),
	  'dimensions_piques'		=> array(CHAMP_TRAD, 'Hauteur des support pain', 'cm',false,true),
	  'infos_comp_hotdog'		=> array(CHAMP_TRAD, 'Informations complémentaires','',false,true),
     	),
	'cuisson'	=> array(
	  'fonction_chauffe'		=> array(CHAMP_TRAD, 'Fonction cuisson','',false,true),
	  'fonction_maintien_chaud'	=> array(CHAMP_TRAD, 'Fonction maintien chaud','',false,true),
// 	  'support_cuisson_saucisse'	=> array(CHAMP_TRAD, 'Support de cuisson saucisses'),
// 	  'matiere_support_saucisse'	=> array(CHAMP_TRAD, 'Matière du support saucisse'),
	),
      )
    );
  }

  function article_hotdog_show($tab, $title) {
    article_global_show($tab, $title, true); // true = champs groupés
  }
  function article_hotdog_update($tab, $title) {
    article_global_update($tab, $title, true); // true = champs groupés
  }
?>