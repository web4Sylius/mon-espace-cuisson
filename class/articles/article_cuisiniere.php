<?php
  include_once($rootpath.'class/articles/article_four.php');
  include_once($rootpath.'class/articles/article_table_cuisson.php');

  function article_cuisiniere_fields($typearticle=2) {
    $fields = array(
      array(
  'criteres_generaux'   => array(
    'type_pose'     => array(CHAMP_TRAD, 'Type de pose','',false,true),
    'nb_fours'    => array(CHAMP_TRAD, 'Nombre de fours', '',false,true),
    'tiroir'    => array(CHAMP_TRAD, 'Type de rangement',false,true),
    'horloge'   => array(CHAMP_MULTI,  'Horloge','',false,true),
    'couvercle'   => array(CHAMP_MULTI, 'Couvercle','',false,true),
    'tiroir_rangement'   => array(CHAMP_MULTI, 'Tiroir de rangement','',false,true),
    'tiroir_chauffant'   => array(CHAMP_MULTI, 'Tiroir chauffant','',false,true),
    
  ),
  'performances' => array(
    'puissance_totale' => array(CHAMP_NUMERIC,'Puissance totale', 'W'),
    'intensite_electrique' => array(CHAMP_TRAD,'Intensité Electrique', 'A',false),
    'tension_electrique' => array(CHAMP_TRAD,'Tension éléctrique', 'V', false),
    'frequence' => array(CHAMP_TRAD,'Fréquence', 'Hz',false),
    'cordon_alim'=>array(CHAMP_TRAD,'Cordon d\'alimentation','',false),
    'class_energetique' => array(CHAMP_LISTE_EN,'Classe énergétique', false,false),
  ),
    'dimensions' => array(
      'hauteur' => array(CHAMP_NUMERIC,'Hauteur', 'mm'),
      'largeur' => array(CHAMP_NUMERIC,'Largeur', 'mm'),
      'profondeur' => array(CHAMP_NUMERIC,'Profondeur', 'mm'),
      'poids' => array(CHAMP_NUMERIC,'Poids', 'g'),
    ),
    'coloris' => array(
      'couleur' => array(CHAMP_MULTI,'Couleur', '',false),
      'couleur_fab' => array(CHAMP_TRAD,'Couleur fabricant', ''),
    'matiere' => array(CHAMP_TRAD,'Matière', ''),
    )
      )
    );
    return ($fields);
  }

  function article_cuisiniere_show($tab, $title) {
    $nb_fours = article_four_count();
    $nb_tables = article_table_cuisson_count();
    
    article_global_show('cuisiniere', $title, true); // true = champs groupés
    echo '<h2>Nombre de four et plaque</h2>';
    echo '<table width="100%">';
    echo '<tr><td><input type="button" value="Ajouter un four" onclick="itemaction(\''.$tab.'\', \'four_add\')" />';
    echo '</td></tr>';
    echo '<tr><td>';
    //echo '<label for="nb_tables">Nombre de tables de cuisson :</label></td><td>';
    //echo '<input type="text" name="nb_tables" value="'.$nb_tables.'"  disabled /></td><td>';
    echo '<input type="button" value="Ajouter une plaque" onclick="itemaction(\''.$tab.'\',\'table_add\')" />';
    echo '</td></tr>';
   // echo '<tr><td><input type="button" value="Ajouter un lave vaisselle" onclick="itemaction(\''.$tab.'\',\'lavevaisselle_add\')"></td></tr>';
    echo '</table>';
    echo '<input type="hidden" name="item_action" />';
    echo '<input type="hidden" name="item_id" />';
  }
  function article_cuisiniere_update($tab, $title) {
    if ($_POST['item_action'] == 'four_add')
      article_four_add();
    if ($_POST['item_action'] == 'table_add')
      article_table_cuisson_add();

    article_global_update('cuisiniere', $title, true);
  }
?>