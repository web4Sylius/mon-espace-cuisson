<?php
require ("../../global.php");
if(!is_numeric($_GET['artid'])) exit;

$sql = "SELECT a.artcode as article_ref, a.consommation_an as article_conso, a.conso_kw * 365 as article_conso2, a.capacite_bouteilles as article_capacite, a.niveau_bruit as article_decibels, a.energy_label as article_labelenergetique, m.libelle as marque_nom";
$sql .= " FROM article a";
$sql .= " INNER JOIN article_marque am USING (artid)";
$sql .= " INNER JOIN marque m USING (marqueid)";
$sql .= " WHERE a.artid = ".$_GET['artid'];
$sql .= " LIMIT 0,1";
$tabArticle = $DB_site->query_first($sql);

$tabArticle['marque_nom']    = strtoupper($tabArticle['marque_nom']);
$tabArticle['article_conso'] = empty($tabArticle['article_conso2']) ? 'N/A' : round($tabArticle['article_conso2']);
$tabArticle['article_decibels'] = empty($tabArticle['article_conso']) ? 'N/A' : round($tabArticle['article_decibels']);

header("Content-type: image/jpeg");

$font = "../arial.ttf";
$bold = "../arial_bold.TTF";

$img   = imagecreatetruecolor(400,800);
$imgbg = imagecreatefromjpeg("fiche_energetique.jpg");
imagecopy($img,$imgbg,0,0,0,0,400,800);

$black = imagecolorallocate($img,0,0,0);
$white = imagecolorallocate($img,255,255,255);

$len_marque = strlen($tabArticle['marque_nom']);
$coor_poly = array(
     'A+++' => array(
          285, 181,
          365, 181,
          365, 231,
          285, 231,
          260, 206
     ),
     'A++' => array(
          285, 211,
          365, 211,
          365, 261,
          285, 261,
          260, 236
     ),
     'A+' => array(
          285, 241,
          365, 241,
          365, 291,
          285, 291,
          260, 266
     ),
     'A' => array(
          285, 271,
          365, 271,
          365, 321,
          285, 321,
          260, 296
     ),
     'B' => array(
          285, 301,
          365, 301,
          365, 351,
          285, 351,
          260, 326
     ),
     'C' => array(
          285, 331,
          365, 331,
          365, 381,
          285, 381,
          260, 356
     ),
     'D' => array(
          285, 361,
          365, 361,
          365, 411,
          285, 411,
          260, 386
     ),
     'E' => array(
          285, 391,
          365, 391,
          365, 441,
          285, 441,
          260, 416
     ),
     'F' => array(
          285, 421,
          365, 421,
          365, 471,
          285, 471,
          260, 446
     ),
     'G' => array(
          285, 451,
          365, 451,
          365, 501,
          285, 501,
          260, 476
     )
);
$x_coord_energy = $coor_poly[$tabArticle['article_labelenergetique']][0] - 5;
$y_coord_energy = $coor_poly[$tabArticle['article_labelenergetique']][5] - 13;

if($tabArticle['article_labelenergetique'] != ''){
     imagefilledpolygon($img,$coor_poly[$tabArticle['article_labelenergetique']],5,$black);
     imagettftext($img,26,0,$x_coord_energy,$y_coord_energy,$white,$bold,$tabArticle['article_labelenergetique']);
}
imagettftext($img,24,0,30,125,$black,$bold,$tabArticle['marque_nom']);
imagettftext($img,16,0,30,150,$black,$bold,$tabArticle['article_ref']);
imagettftext($img,24,0,285,565,$black,$bold,$tabArticle['article_conso']);
imagettftext($img,24,0,95,742,$black,$bold,$tabArticle['article_capacite']);
imagettftext($img,24,0,255,742,$black,$bold,$tabArticle['article_decibels']);

imagejpeg($img, null, 90);