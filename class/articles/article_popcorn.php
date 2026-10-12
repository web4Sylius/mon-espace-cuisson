<?php
  function article_popcorn_fields() {
    return array(
      array(
	'criteres_generaux'	=> array(
	  // champ			// traduire, texte du label
	  'fonctionnement_popcorn'	=> array(CHAMP_TRAD, 'Fonctionnement','',false,true),
	  'capacite'			=> array(CHAMP_TRAD, 'Capacité (L)','',false,true),
	  'capacite_mais'		=> array(CHAMP_TRAD, 'Capacité du maïs','',false,true),
	  'systeme_chauffe'		=> array(CHAMP_TRAD, 'Système de chauffe','',false,true),
	  'design'			=> array(CHAMP_TRAD, 'Design','',false,true),
	  'nettoyage_popcorn'		=> array(CHAMP_TRAD, 'Nettoyage','',false,true),
	  'temps_preparation'		=> array(CHAMP_NUMERIC, 'Temps de préparation (min)','',false,true),
	  'paroie_froide'		=> array(CHAMP_TRAD, 'Paroie froide','',false,true),
	  'interupteur_marche_arret'	=> array(CHAMP_TRAD, 'Interupteur marche/arret','',false,true),
	  'voyant_lumineux'		=> array(CHAMP_TRAD, 'Voyant lumineux','',false,true),
	  'pieds_antiderapants'		=> array(CHAMP_TRAD, 'Pieds anti-dérapants','',false,true),
	  'accessoires_inclus_popcorn'	=> array(CHAMP_TRAD, 'Accessoires inclus','',false,true),
	)
      )
    );
  }

  function article_popcorn_show($tab, $title) {
    article_global_show($tab, $title, true); // true = champs groupés
  }
  function article_popcorn_update($tab, $title) {
    article_global_update($tab, $title, true); // true = champs groupés
  }

?>