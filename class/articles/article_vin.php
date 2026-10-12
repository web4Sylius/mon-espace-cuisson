<?php
//armoire refrigerateur congelateur
function article_vin_fields() {
	return array(
    	array(
			'criteres_generaux'	=> array(
			  'type_article'						=> array(CHAMP_TRAD, 'Type d\'article', '',false,true),
			  'contenance'							=> array(CHAMP_TRAD, 'Contenance totale', '',false,true),
			  'nb_bouteilles_rouge'			=> array(CHAMP_NUMERIC, 'Nombre de bouteilles de vin rouge', '',false,true),
			  'nb_bouteilles_blanc'			=> array(CHAMP_NUMERIC,'Nombre de bouteilles de vin blanc', '',false),
			  'nb_bouteilles_rose' 			=> array(CHAMP_NUMERIC,'Nombre de bouteilles de vin rosé', ''),
			  'nb_bouteilles_petillant' => array(CHAMP_NUMERIC,'Nombre de bouteilles de vin pétillant', ''),
			  'niveau_gamme'						=> array(CHAMP_MULTI, 'Niveau de gamme', '',false,true),
			  'coloris'									=> array(CHAMP_TRAD, 'Couleur', '',false,true),
			  'pays'										=> array(CHAMP_TRAD, 'Pays', '',false,true),
			  'nom_region'							=> array(CHAMP_TRAD, 'Région', '',false,true),
			  'appellation'							=> array(CHAMP_TRAD, 'Appellation', '',false,true),
			  'producteur'							=> array(CHAMP_TRAD, 'Producteur/marque', '',false,true),
			  'cuvee'										=> array(CHAMP_TRAD, 'Cuvée', '',false,true),
			  'millesime'								=> array(CHAMP_TRAD, 'Millésime', '',false,true),
			  'conditionnement'					=> array(CHAMP_TRAD, 'Conditionnement', '',false,true),
			), 
    	));
  }

  function article_vin_show($tab, $title) {
    article_global_show($tab, $title, true); // true = champs groupés
  }
  function article_vin_update($tab, $title) {
    article_global_update($tab, $title, true); // true = champs groupés
  }
?>