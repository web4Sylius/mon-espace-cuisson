<?php
  function article_accessoires_entretien_fields() {
    return array(
      array(
	'criteres_generaux'	=> array(
	  // champ			// traduire, texte du label
	  'type_produit'		=> array(CHAMP_TRAD, 'Type','',true,true),
	  'contenance' 			=> array(CHAMP_TRAD, 'Contenance (mL)','',false,true),
	  'utilisation_entretien' 	=> array(CHAMP_TRAD, 'Utilisation','',false,true),
	  'ecologie'			=> array(CHAMP_TRAD, 'Écologie','',false,true),
	  'pulverisateur'		=> array(CHAMP_TRAD, 'Pulvérisateur','',false,true),
	  'matiere'			=> array(CHAMP_TRAD, 'Matière','',false,true),
	  'autres_infos_entretien'	=> array(CHAMP_TRAD, 'Autres informations','',false,true)
	)
      )
    );
  }

  function article_accessoires_entretien_show($tab, $title) {
    article_global_show($tab, $title, true); // CHAMP_TRAD = champs groupés
  }
  function article_accessoires_entretien_update($tab, $title) {
    article_global_update($tab, $title, true); // CHAMP_TRAD = champs groupés
  }

?>