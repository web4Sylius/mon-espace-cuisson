<?php

include("admin_global.php");

$marqueid=33;
$fournisseur="ZHENDRE";

$f=$DB_site2->query_first("SELECT COUNT(*) FROM fournisseur WHERE societe='".mysqli_real_escape_string($DB_site2->link_id,$fournisseur)."'");
if($f[0]==0){
  print "fournisseur ".$fournisseur." introuvable";
  exit;
}

$sql="SELECT artid,artcode,paht,prixtrspt,delailivraison FROM article INNER JOIN article_marque USING(artid) WHERE marqueid=".$marqueid;
$req=$DB_site2->query($sql);
while($article=$DB_site2->fetch_array($req)){
    print "<br>".$article['artcode'];
    if(isset($_GET['save'])) $return=$DB_site2->query("INSERT INTO article_fournisseur SET fournisseur='".mysqli_real_escape_string($DB_site2->link_id,$fournisseur)."', principal=1, prix=".floatval($article['paht']).", trspt=".$article['prixtrspt'].", devise='EUR', artid=".$article['artid'].", delai=".$article['delailivraison']);
}