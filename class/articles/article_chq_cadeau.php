<?php
//armoire refrigerateur congelateur
function article_chq_cadeau_fields() {
	return array(
    	array(
			'criteres_generaux'	=> array(
			  'type'				=> array(CHAMP_TRAD, 'Type', '',false,true),
			  'valeur_chq'	=> array(CHAMP_NUMERIC, 'Valeur du chèque-cadeau ', '€',false,true),
			  'format'			=> array(CHAMP_TRAD, 'Format', '',false,true),
			  'duree'				=> array(CHAMP_NUMERIC, 'Durée de validité', 'mois',false,true),
			),
      
    	));
  }

  function article_chq_cadeau_show($tab, $title) {
    article_global_show($tab, $title, true); // true = champs groupés
  }
  function article_chq_cadeau_update($tab, $title) {
    article_global_update($tab, $title, true); // true = champs groupés
  }
?>