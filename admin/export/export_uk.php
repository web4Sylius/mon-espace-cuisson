<?php

include("../admin_global.php");

include("xlexport.php");

$xl=new xlexport("csv");
$xl->name="export_uk_products_".date("Y-m-d");

$marques=array();
$req=$DB_site2->query("SELECT * FROM marque");
while($m=$DB_site2->fetch_array($req)) $marques[$m['marqueid']]=$m['libelle'];

$sql="SELECT artcode,artcode_eurocave,libelleEN,marqueid,hauteur,largeur,profondeur,poid 
      FROM article a INNER JOIN article_site a_s USING(artid) INNER JOIN article_marque am USING(artid) 
      WHERE a_s.site_code='uk' AND a_s.active IN('0','1')
      ORDER BY artcode";
      

$xl->addrow(array("Acilyon reference","Manufacturer reference","Product name","Brand","Dimensions (H*W*D) mm","Weight kg"));
      
$req=$DB_site2->query($sql);
while($art=$DB_site2->fetch_array($req)){
    $line=array($art['artcode'],$art['artcode_eurocave'],$art['libelleEN'],$marques[$art['marqueid']],$art['hauteur']."*".$art['largeur']."*".$art['profondeur'],($art['poid']/1000));
    $xl->addrow($line);
}
      
$xl->export(); 
     
?>