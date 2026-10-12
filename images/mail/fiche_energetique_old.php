<?php
require ("../../global.php");
if(!is_numeric($_GET['artid'])) exit;

$sql = "SELECT a.artcode as article_ref, a.consommation_an_old as article_conso, a.conso_kw_old * 365 as article_conso2, a.capacite_bouteilles as article_capacite, a.niveau_bruit_old as article_decibels, a.energy_label_old as article_labelenergetique, m.libelle as marque_nom";
$sql .= " FROM article a";
$sql .= " INNER JOIN article_marque am USING (artid)";
$sql .= " INNER JOIN marque m USING (marqueid)";
$sql .= " WHERE a.artid = ".$_GET['artid'];
$sql .= " LIMIT 0,1";
$tabArticle = $DB_site->query_first($sql);

/*$etiq_energie = '';
$tabArticle['article_labelenergetique'] = strtoupper(preg_replace('~[^a-gA-G\+]+~', '', $tabArticle['article_labelenergetique']));
$labels_energetiques = preg_split('@(?=A|B|C|D|E|F|G)@', $tabArticle['article_labelenergetique']);
$label_energetique = str_replace('+','plus',strtolower($labels_energetiques[1]));
$etiq_energie="<img alt=\"".$template->translate("/article/champs/classe_energetique","F")." ".$label_energetique."\" src=\"/images/templates/energie_".$label_energetique.".jpg\">";
$l1 = substr($label_energetique,0,1);
$lbl_energie = $l1;
if(count($labels_energetiques) > 2) {
  foreach ($labels_energetiques as $key => $value) {
    if($key > 1) {
      $label_energetique2 = str_replace('+','plus',strtolower($labels_energetiques[$key]));
      $etiq_energie2="<img alt=\"".$template->translate("/article/champs/classe_energetique","F")." ".$label_energetique2."\" src=\"/images/templates/energie_".$label_energetique2.".jpg\">";
      $l2 = substr($label_energetique2,0,1);
      if($l1 < $l2) {
        $etiq_energie = $etiq_energie2;
        $lbl_energie = $l2;
        $l1 = $l2;
      } else if($l1 === $l2) {
        if(strlen($label_energetique) > strlen($label_energetique2)) {
          $etiq_energie = $etiq_energie2;
          $lbl_energie = $l2;
          $l1 = $l2;
        }
      }
    }
  }
}*/


$tabArticle['marque_nom']    = strtoupper($tabArticle['marque_nom']);
$tabArticle['article_conso'] = empty($tabArticle['article_conso2']) ? 'N/A' : round($tabArticle['article_conso2']);
$tabArticle['article_decibels'] = empty($tabArticle['article_conso']) ? 'N/A' : round($tabArticle['article_decibels']);

$lbl_energie = strtoupper($tabArticle['article_labelenergetique']);

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
$x_coord_energy = $coor_poly[$lbl_energie][0] - 5;
$y_coord_energy = $coor_poly[$lbl_energie][5] - 13;

if($lbl_energie != ''){
     imagefilledpolygon($img,$coor_poly[$lbl_energie],5,$black);
     imagettftext($img,26,0,$x_coord_energy,$y_coord_energy,$white,$bold,$lbl_energie);
}
imagettftext($img,24,0,30,125,$black,$bold,$tabArticle['marque_nom']);
imagettftext($img,16,0,30,150,$black,$bold,$tabArticle['article_ref']);
imagettftext($img,24,0,285,565,$black,$bold,$tabArticle['article_conso']);
imagettftext($img,24,0,95,742,$black,$bold,$tabArticle['article_capacite']);
imagettftext($img,24,0,255,742,$black,$bold,$tabArticle['article_decibels']);

imagejpeg($img, null, 90);