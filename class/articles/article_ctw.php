<?php
  function article_ctw_fields() {
    return array(
      array(
	'criteres_generaux'	=> array(
	  // champ			// traduire, texte du label
	  'fonction'			=> array(CHAMP_TRAD, 'Fonction','',true,true),
	  'nb_personnes'		=> array(CHAMP_TRAD, 'Nombre de personnes','',false,true),
	  'nb_elements'			=> array(CHAMP_TRAD, 'Nombre de niveaux','',false,true),
	  'matiere'			=> array(CHAMP_TRAD, 'Matière','',false,true),
	  'forme'			=> array(CHAMP_TRAD, 'Forme','',false,true),
	  'diametre'			=> array(CHAMP_NUMERIC, 'Diamètre (cm)','',false,true),
	  'capacite'			=> array(CHAMP_NUMERIC, 'Capacité totale (L)','',false,true),
	  'thermostat_reglable'		=> array(CHAMP_TRAD, 'Thermostat réglable','',false,true),
	  'voyant_lumineux'		=> array(CHAMP_TRAD, 'Voyant lumineux','',false,true),
	  'revetement_antiadhesif'	=> array(CHAMP_TRAD, 'Revetement anti-adhésif','',false,true),
    //       'plaque_cuisson_amovible'=> array(CHAMP_TRAD, 'Plaque cuisson amovible'),
	  'fonction_maintien_chaud'	=> array(CHAMP_TRAD, 'Fonction maintien chaud','',false,true),
	  'infos_complementaires'	=> array(CHAMP_TRAD, 'Informations complémentaires','',false,true),
	)
      )
    );
  }

  function article_ctw_show($tab, $title) {
    article_global_show($tab, $title, true); // true = champs groupés
  }
  function article_ctw_update($tab, $title) {
    article_global_update($tab, $title, true); // true = champs groupés
  }

?>