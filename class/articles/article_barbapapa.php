<?php
  function article_barbapapa_fields() {
    return array(
      // champ				// traduire, texte du label
      'fonction'			=> array(CHAMP_TRAD, 'Fonction','',true,true),
      'fonctionnement'			=> array(CHAMP_TRAD, 'Fonctionnement','',false,true),
      'interrupteur_marche_arret'	=> array(CHAMP_TRAD, 'Intérrupteur marche/arrêt','',false,true),
      'rebords_anti_projection'	=> array(CHAMP_TRAD, 'Rebords anti-projection','',false,true),
      'pieds_antiderapants'		=> array(CHAMP_TRAD, 'Pieds anti-dérapants','',false,true),
      'cuve'				=> array(CHAMP_TRAD, 'Cuve','',false,true),
      'diametre_cuve'			=> array(CHAMP_NUMERIC, 'Diamètre de la cuve', 'cm',false,true),
      'autres_infos_bbp'		=> array(CHAMP_TRAD, 'Autres informations','',false,true),
    );
  }
?>