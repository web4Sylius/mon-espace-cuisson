<?php
  function article_prestation_fields() {
    return array(
      array(
	'criteres_generaux'	=> array(
	  // champ			// traduire, texte du label
	  'type_accessoire'		=> array(CHAMP_TRAD, 'Type','',true,true),
	)
      )
    );
  }

  function article_prestation_show($tab, $title) {
    article_global_show($tab, $title, true); // CHAMP_TRAD = champs groupés
  }
  function article_prestation_update($tab, $title) {
    article_global_update($tab, $title, true); // CHAMP_TRAD = champs groupés
  }

?>