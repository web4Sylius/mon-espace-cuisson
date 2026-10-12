<?php

include("global.php");

header('Content-Type: text/html; charset=utf-8');

print "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<products>";


// recuperation de la TVA
$tvafrance=$DB_site->query_first("SELECT 0,TVAtauxnormal,TVAtauxreduit FROM pays WHERE paysid=57");

// Recuperation des articles pour le flux next perf
$requete=$DB_site->query("SELECT a.*,c.libelleFR AS categorie,c.parentid FROM article a INNER JOIN display_categorie dc ON (dc.catid=a.catid) INNER JOIN categorie c ON(c.catid=a.catid) WHERE dc.display='nextperformance' AND a.active='1' AND commandable='1'");

// Recuperation marques et libelles et textes categrories
while($article=$DB_site->fetch_array($requete)){
      $mq=$DB_site->query_first("SELECT * FROM article_marque am INNER JOIN marque m ON (m.marqueid=am.marqueid) WHERE am.artid=".$article['artid']);

      while($article['parentid']!=0){
    	  $cat=$DB_site->query_first("SELECT catid,parentid,libelleFR FROM categorie WHERE catid=".$article['parentid']);
    	  $article['catid']=$cat['catid'];
    	  $article['parentid']=$cat['parentid'];
    	  $article['categorie']=$cat['libelleFR']." &gt; ".$article['categorie'];
      }
      
      //$article['categorie']=str_replace("&","&amp;",$article['categorie']);
      //$article['libelleFR']=str_replace("&","&amp;",$article['libelleFR']);

      $caract_valeur=$DB_site->query_first("SELECT ac.caractvalid, libelleFR, ac.differenceprix, cv.image 
                                 FROM caracteristiquevaleur cv, article_caractval ac 
                                 WHERE cv.caractvalid = ac.caractvalid
                                 AND caractid = '22'
                                 AND artid = '$article[artid]'
                                 ORDER BY cv.position ASC ");

      /*$caract_gar=$DB_site->query_first("SELECT ac.caractvalid, libelleFR, ac.differenceprix, cv.image, cv.caractid
                                           FROM caracteristiquevaleur cv, article_caractval ac WHERE cv.caractvalid = ac.caractvalid AND caractid IN (3,20) 
                                           AND artid = '$article[artid]' AND differenceprix=0 ORDER BY cv.position DESC");
      if($caract_gar['differenceprix']==0) $caract_valeur['libelleFR']=$caract_gar['libelleFR'];*/


      $description=strip_tags($article['usage'].", ".$article['capacite_bouteilles']." bouteilles, nbre temp. : ".$template->translate($article['nbr_temperature']).", coloris : ".$template->translate($article['coloris']).", garantie : ".$caract_valeur['libelleFR']);
  //  $description=strip_tags($article['descriptionFR']);
      $description=str_replace("&rsquo;","'",$description);
      $description=$description;
      $description=$description;
      $description = str_replace("\\\\", "", $description);
      $description=stripslashes(str_replace("\r\n"," ",$description));

      $retail="";
      $discount="";
      $txtva=1+$tvafrance[$article['tauxchoisi']]/100;
      $temp_price=$DB_site->query_first("SELECT * FROM temp_price WHERE artid=$article[artid] AND ".mktime(0,0,0)." BETWEEN du AND au");
      if($temp_price['id']!=""){
      	  $article['prix']=$temp_price['prix'];
      	  $article['prixht']=$article['prix']/$txtva;
      	  $article['prixconseilleht']=$temp_price['prixconseilleht'];
      }
      if(round($article[prixconseilleht],1) != round($article[prixht],1)){
        	$retail=round($article['prixconseilleht']*$txtva,2);
        	$discount=round(100-100*$article['prixht']/$article['prixconseilleht']);
     }
     
     $txtTitle=str_replace("&amp;","&",$mq['libelle'])." ".str_replace("&amp;","&",$article[libelleFR])." ".$article['artcode'];
     $txtCateg=str_replace("&amp;","&",$article[categorie]);
     
     print "<product>
                <id>$article[artid]</id>
                <title><![CDATA[$txtTitle]]></title>
                <url_image>http://www.mon-espace-cuisson.com/configurations/$host/images/produits/article/$article[artid].$article[image]</url_image>
                <url_product>http://www.mon-espace-cuisson.com/art,$article[artid],".url_rewrite($article['libelleFR']).".htm</url_product>
                <description><![CDATA[$description]]></description>
                <price>".floatval($article['prix'])."</price>
                <price_old>$retail</price_old>
                <category><![CDATA[$txtCateg]]></category>
            </product>\n";
}

print "</products>";

?>