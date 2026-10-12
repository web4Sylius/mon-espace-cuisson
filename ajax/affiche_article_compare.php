<?php
require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

$sql = "SELECT a.artid, a.libelle".$template->langue." AS article_libelle, m.libelle as marque_libelle, a.artcode, a.commandable";
$sql .= " FROM article a";
$sql .= " INNER JOIN article_marque am USING (artid)";
$sql .= " INNER JOIN marque m USING (marqueid)";
$sql .= " WHERE a.artid = ".intval($artid);
$article = $DB_site->query_first($sql);

$article_prices = article_prices($article['artid'], $template->tva_ref(), $template->site_code(), $sitepro);

$return = ""
. "<img src=\"/images/".$article['artid']."_100_100.jpg\">"
. "<p>".$article['article_libelle']."</p>"
. "<span>".$article['marque_libelle']."</span>"
. "<span class=\"ref\">Réf: ".$article['artcode']."</span>";

if($article['commandable']==1)
    $return.="<span>".$article_prices['prix']." € <sup>TTC</sup></span>";

echo $return;