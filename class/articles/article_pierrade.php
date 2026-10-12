<?php
  function article_pierrade_fields() {
    return array(
      array(
	'criteres_generaux'	=> array(
	  // champ				// traduire, texte du label
	  'fonction'			=> array(CHAMP_TRAD, 'Fonction','',true,true),
	  'energie'			=> array(CHAMP_TRAD, 'Énergie','',false,true),
	  'nettoyage_bbq'		=> array(CHAMP_TRAD, 'Nettoyage','',false,true),
	  'matiere_socle'		=> array(CHAMP_TRAD, 'Matière du socle','',false,true),
	  'nb_personnes'		=> array(CHAMP_TRAD, 'Nombre de personnes','',false,true),
	  'fonction_maintien_chaud'	=> array(CHAMP_TRAD, 'Fonction de maintien au chaud','',false,true),
	  'nb_bruleurs'			=> array(CHAMP_TRAD, 'Nombre de brûleurs','',false,true),
	  'type_bruleurs'		=> array(CHAMP_TRAD, 'Type de bruleurs','',false,true),
	  'resistances'			=> array(CHAMP_TRAD, 'Résistances','',false,true),
	  'securite'			=> array(CHAMP_TRAD, 'Sécurité','',false,true),
	  'autres_infos'		=> array(CHAMP_TRAD, 'Autres informations','',false,true),
	),
	'commandes'	=> array(
	  'interrupteur_marche_arret'	=> array(CHAMP_TRAD, 'Interrupteur marche/arrêt','',false,true),
	  'thermostat_reglable'		=> array(CHAMP_TRAD, 'Thermostat réglable','',false,true),
	),
	'surface_de_cuisson'	=> array(
	  'surface_de_cuisson'		=> array(CHAMP_TRAD, 'Surface de cuisson', 'cm2',false,true),
	  'dimensions_plaque'		=> array(CHAMP_TRAD, 'Dimensions de la surface de cuisson (L*l) cm','',false,true),
	  'matiere_plaque'		=> array(CHAMP_TRAD, 'Matière de la plaque','',false,true),
	  'plaque_antiadherente'	=> array(CHAMP_TRAD, 'Plaque anti-adherente','',false,true),
	  'plaque_amovible'		=> array(CHAMP_TRAD, 'Plaque amovible','',false,true),
	),
	'accessoires_inclus'	=> array(
	  'bac_recup_graisse'		=> array(CHAMP_TRAD, 'Bac récupérateur de graisse','',false,true),
	  'accessoires_inclus_cv'	=> array(CHAMP_TRAD, 'Accessoires inclus','',false,true),
	),
      )
    );
  }

  function article_pierrade_show($tab, $title) {
    article_global_show($tab, $title, true); // true = champs groupés
  }
  function article_pierrade_update($tab, $title) {
    article_global_update($tab, $title, true); // true = champs groupés
  }

?>