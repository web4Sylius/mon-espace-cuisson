<?php

include("global.php");

header ("content-type: text/xml");

print "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<products>";

$requete=$DB_site->query("SELECT a.*,c.libelleFR AS categorie,c.parentid FROM article a INNER JOIN display_categorie dc ON (dc.catid=a.catid) INNER JOIN categorie c ON(c.catid=a.catid) WHERE dc.display='criteo' AND a.active='1' AND commandable='1'");

while($article=$DB_site->fetch_array($requete)){
      $mq=$DB_site->query_first("SELECT * FROM article_marque am INNER JOIN marque m ON (m.marqueid=am.marqueid) WHERE am.artid=".$article['artid']);

      while($article['parentid']!=0){
	  $cat=$DB_site->query_first("SELECT catid,parentid,libelleFR FROM categorie WHERE catid=".$article['parentid']);
	  $article['catid']=$cat['catid'];
	  $article['parentid']=$cat['parentid'];
	  $article['categorie']=$cat['libelleFR'];
      }
      $article['categorie']=str_replace("&","&amp;",html_entity_decode($article['categorie'],ENT_COMPAT,"ISO-8859-15"));


      $description=strip_tags($article['usage'].", ".$article['capacite_bouteilles']." bouteilles, nbre temp. : ".$template->translate($article['nbr_temperature']).", coloris : ".$template->translate($article['coloris']));
//       $description=strip_tags($article['descriptionFR']);
      $description=str_replace("&rsquo;","'",$description);
      $description=utf8_encode($description);
      $description=html_entity_decode($description,ENT_COMPAT, 'UTF-8');
      $description = str_replace("\\\\", "", $description);
      $description=stripslashes(str_replace("\r\n"," ",$description));
      
      $retail="";
      $discount="";
      $article_prices=article_prices($article['artid'],$template->tva_ref(),$template->site_code(),false);

      if(round($article_prices['prixconseille'],1) != round($article_prices['prix'],1)){
	$retail=$article_prices['prixconseille'];
	$discount=$article_prices['remise'];
     }
     $article[libelleFR]=html_entity_decode($article[libelleFR],ENT_COMPAT,"ISO-8859-15");
     $article[libelleFR]=str_replace("&","&amp;",$article[libelleFR]);
     print "<product id=\"$article[artid]\">
      <name>".utf8_encode($mq['libelle']." ".$article[libelleFR]." ".$article['artcode'])."</name>
      <smallimage>http://www.mon-espace-cuisson.com/configurations/$host/images/produits/article/$article[artid].$article[image]</smallimage>
      <bigimage>http://www.mon-espace-cuisson.com/configurations/$host/images/produits/article/$article[artid].$article[image]</bigimage>
      <producturl>http://www.mon-espace-cuisson.com/art,$article[artid],".url_rewrite($article['libelleFR']).".htm?utm_source=CRITEO&amp;utm_medium=Banner&amp;utm_term=$article[artid]&amp;utm_campaign=Product</producturl>
      <description><![CDATA[$description]]></description>
      <price>".floatval($article_prices['prix'])."</price>
      <retailprice>$retail</retailprice>
      <discount>$discount</discount>
      <recommendable>1</recommendable>
      <instock>1</instock>
      <categoryid1>".utf8_encode($article[categorie])."</categoryid1>
      </product>\n";
}

print "</products>";

?>