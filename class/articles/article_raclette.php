<?php
  function article_raclette_fields() {
    return array(
      array(
	'criteres_generaux'	=> array(
	  // champ				// traduire, texte du label
	  'fonction'			=> array(CHAMP_TRAD, 'Fonction','',false,true),
	  'matiere_socle'		=> array(CHAMP_TRAD, 'Matière du socle','',false,true),
	  'nb_personnes'		=> array(CHAMP_TRAD, 'Nombre de personnes','',false,true),
	  'fonction_maintien_chaud'	=> array(CHAMP_TRAD, 'Fonction de maintien au chaud','',false,true),
	  'autres_infos'		=> array(CHAMP_TRAD, 'Autres informations','',false,true),
	),
	'commandes'	=> array(
	  'interrupteur_marche_arret'	=> array(CHAMP_TRAD, 'Interrupteur marche/arrêt','',false,true),
	  'thermostat_reglable'		=> array(CHAMP_TRAD, 'Thermostat réglable','',false,true),
	),
	'surface_de_cuisson'	=> array(
	  'matiere_plaque'		=> array(CHAMP_TRAD, 'Matière de la plaque','',false,true),
	  'plaque_antiadherente'	=> array(CHAMP_TRAD, 'Plaque anti-adherente','',false,true),
	  'plaque_amovible'		=> array(CHAMP_TRAD, 'Plaque amovible','',false,true),
	),
	'accessoires_inclus'	=> array(
	  'accessoires_inclus_cv'	=> array(CHAMP_TRAD, 'Accessoires inclus','',false,true),
	),
      )
    );
  }

  function article_raclette_show($tab, $title) {
    article_global_show($tab, $title, true); // true = champs groupés
  }
  function article_raclette_update($tab, $title) {
    article_global_update($tab, $title, true); // true = champs groupés
  }
?>