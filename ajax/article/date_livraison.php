<?php
require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

//Sélection détail de l'article
$sql = "SELECT *";
$sql .= " FROM article";
$sql .= " WHERE artid = '" . $_GET['artid'] . "'";
$article = $DB_site->query_first($sql);

$d = delailivraison($article['artid']);

// echo $d['delailivraison'] . " " . $template->translate("/article/champs/jours_transport_inclus");