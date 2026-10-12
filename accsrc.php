<?php

include("global.php");

if(!isset($_GET['mid'])) exit;

header("Cache-Control: no-cache");
header("Content-type:text/xml");

print "<?xml version=\"1.0\" encoding=\"iso-8859-1\" ?>
<xml>\n";

$list=$list2=array();
$articles=$DB_site->query("SELECT artcode,artcode_eurocave,a.artid,refs_fabricant,libelle".$template->langue." AS libelle,(SELECT COUNT(lotid) FROM article_lot WHERE lotid=am.artid) AS lot FROM article_marque am INNER JOIN article a ON (am.artid=a.artid) WHERE am.marqueid=".$_GET['mid']." AND a.artid = (SELECT DISTINCT artid FROM article_rangement ar WHERE ar.artid = a.artid ) ORDER BY a.artcode");
while($article=$DB_site->fetch_array($articles)){
    $list[$article['artcode']]="/".$template->translate("/article/accessoires/accessoires","L")."/".url_rewrite($article['libelle'])."-".strtolower($article['artcode'])."-art".$article['artid'].".html";
    if($article['lot']==0){
	$refs=explode("\n",$article['refs_fabricant']);
	foreach($refs as $ref) if($ref!="") $list2[$ref]="/".$template->translate("/article/accessoires/accessoires","L")."/".url_rewrite($article['libelle'])."-".url_rewrite(strtolower($article['artcode_eurocave']))."-art".$article['artid'].".html";
	
    }
}

ksort($list);
foreach($list as $artcode=>$url)
    print "<artcode url=\"$url\">".$artcode."</artcode>\n";
   
ksort($list2);
foreach($list2 as $ref=>$url)
    print "<ref url=\"$url\">".$ref."</ref>\n";
   
//foreach($fab as $artid=>$fartcode)
//  print "<artcode id=\"$artid\">$fartcode</artcode>\n";

print "</xml>";

?>