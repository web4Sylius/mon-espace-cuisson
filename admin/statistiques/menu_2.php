<?php
include "../menu.class.php";
$position=0;

$m_accueil=new menu("Accueil","index.php");
$m_accueil->directory="/admin";
$m_accueil->cssclass="ccmenu";
print $m_accueil->draw();

$m_new_stats=new menu("Nouvelles stats","menu_accueil.php");
$m_new_stats->position=$position;
$m_new_stats->directory="/admin/statistiques";
$m_new_stats->cssclass="ccmenu";
print $m_new_stats->draw();
?>