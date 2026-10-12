<?php

  // Filtre les onglets en fonction du type d'article
  function filter_onglets($db, $type, $ref_tabs)
  {
    $query = $db->query(
      "SELECT `onglets`
       FROM `typearticle`
       WHERE `typearticleid` = '$type'
       ORDER BY `libelle`");
    $tab = $db->fetch_array($query);
    $tabs = explode(',', $tab['onglets']);
    $disp_tabs = array();
    foreach($tabs as $disp_tab)
      if (array_key_exists($disp_tab, $ref_tabs))
		$disp_tabs = array_merge(
	  $disp_tabs,
	  array($disp_tab => $ref_tabs[$disp_tab])
	);
    return ($disp_tabs);
  }

  // Génération du menu d'onglets pour toutes les catégories
  function print_onglets($tabs, $color, $current)
  {
    foreach($tabs as $key => $text) {
      if ($key == $current)
	echo '<div class="onglet_courant">' . $text . '</div>';
      else
	echo '<div class="onglet_'. $color . '"><a href="' . "javascript:clique('$key','$current');" . '">' . $text . '</a></div>';
    }
  }

  function gen_dynamics_tabs($tab, $text) {
    global $DB_site2, $artid;
    if (is_null($artid) || !is_numeric($artid))
      return array();
    $query = $DB_site2->query(
      "SELECT `id` FROM `article_$tab` WHERE `artid` = $artid"
    );
    $tabs = array();
    if ($data = $DB_site2->fetch_array($query))
      $tabs["dyn_${tab}_$data[id]"] = "$text";
    $i = 2;
    while ($data = $DB_site2->fetch_array($query)) {
      $tabs["dyn_${tab}_$data[id]"] = "$text $i";
      $i++;
    }
    return ($tabs);
  }

  // Ajoute un élement pour les articles simples
  function insert_special_tabs($type) {
    global $DB_site2, $artid, $rootpath;

    if (empty($artid) || !is_numeric($artid))
      return false;

    // Plaque de cuisson
    if ($type == 3) {
      include_once($rootpath.'class/articles/article_table_cuisson.php');
      if (article_table_cuisson_count() == 0){
		article_table_cuisson_add();
	  }
    }
    // Four
    if ($type == 4 || $type == 17) {
      include_once($rootpath.'class/articles/article_four.php');
      if (article_four_count() == 0){
		article_four_add();
	  }
    }
    // Congel tout seul
    if ($type == 22) {
      include_once($rootpath.'class/articles/article_congel.php');
      if (article_congel_count() == 0){
		article_congel_add();
	  }
    }
    // Lave vaisselle
    if ($type == 23) {
      include_once($rootpath.'class/articles/article_lavevaisselle.php');
      if (article_lavevaisselle_count() == 0){
		article_lavevaisselle_add();
	  }
    }
    return true;
  }
?>