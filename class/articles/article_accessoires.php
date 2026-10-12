<?php
  function article_accessoires_fields() {
    return array(
      array(
	'criteres_generaux'	=> array(
	  'type_accessoire'		=> array(CHAMP_TRAD, 'Type','',true,true)
	)
      )
    );
  }

  function article_accessoires_show($tab, $title) {
    article_global_show($tab, $title, true); // CHAMP_TRAD = champs groupés
  }
  function article_accessoires_update($tab, $title) {
    article_global_update($tab, $title, true); // CHAMP_TRAD = champs groupés
  }

?>