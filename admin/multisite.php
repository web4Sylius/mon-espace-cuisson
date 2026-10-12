<?php 

// conversion BDD mono site => BDD multisite

include("admin_global.php");

$ok=isset($_GET['ok']);

// $req=$DB_site2->query("SELECT * FROM garantie");
// while($g=$DB_site2->fetch_array($req)) $DB_site2->query("UPDATE garantie SET prix='".serialize(array("base"=>$g['prix']))."', ppi='".serialize(array("base"=>$g['ppi']))."' WHERE garantieid=".$g['garantieid']);

// conversion prix vente
/*
$u=0;
$req=$DB_site2->query("SELECT * FROM article");
while($art=$DB_site2->fetch_array($req)){
  if($ok)
      $return=$DB_site2->query("INSERT INTO article_site SET artid=".$art['artid'].", site_code='base', prix=".$art['prix'].", prixconseilleht=".$art['prixconseilleht'].", tauxchoisi=".$art['tauxchoisi'].", active=".intval($art['active']));
      
  print $art['artid']." ".$return."<br>" ;
  $u++;
}
print $u;
*/
// conversion prix vente lot
/*
$u=0;
$req=$DB_site2->query("SELECT * FROM article_lot");
while($al=$DB_site2->fetch_array($req)){
  if($ok)
    $return=$DB_site2->query("INSERT INTO article_lot_site SET alid=".$al['id'].", site_code='base', prix=".$al['prix']);
    
  print $al['id']." ".$return."<br>";
  $u++;
}
print $u;
*/

// conversion prix temp
// $DB_site2->query("UPDATE temp_price SET site_code='base'");


// conversion prix vente options
/*
$DB_site2->query("ALTER TABLE article_caractval CHANGE `differenceprix` `differenceprix` VARCHAR( 100 ) NOT NULL");
$DB_site2->query("ALTER TABLE article_caractval CHANGE `differenceprixht` `differenceprixht` VARCHAR( 100 ) NOT NULL");
$u=0;
$req=$DB_site2->query("SELECT * FROM article_caractval");
while($ac=$DB_site2->fetch_array($req)){
    if($ok)
      $return=$DB_site2->query("UPDATE article_caractval SET differenceprixht='".serialize(array('base'=>$ac['differenceprixht']))."', differenceprix='".serialize(array('base'=>$ac['differenceprix']))."' WHERE artid=".$ac['artid']." AND caractvalid=".$ac['caractvalid']);
     
    print $ac['artid']." ".$return."<br>";
    $u++;
}
print $u;
*/

// mise à jour fournisseur principal
/*
$DB_site2->query("UPDATE article_fournisseur SET principal='base' WHERE principal='1'");
$DB_site2->query("UPDATE article_fournisseur SET principal='' WHERE principal='0'");
*/

// conversion prix vente garanties
/*
$DB_site2->query("ALTER TABLE article_garantie_liste CHANGE `prix` `prix` VARCHAR(100) NOT NULL");
$DB_site2->query("ALTER TABLE article_garantie_liste CHANGE `ppi` `ppi` VARCHAR(100) NOT NULL");
$u=0;
$req=$DB_site2->query("SELECT * FROM article_garantie_liste");
while($ag=$DB_site2->fetch_array($req)){
    if($ok)
      $return=$DB_site2->query("UPDATE article_garantie_liste SET prix='".serialize(array("base"=>$ag['prix'],"uk"=>$ag['prix']))."', ppi='".serialize(array("base"=>$ag['ppi'],"uk"=>$ag['ppi']))."' WHERE id=".$ag['id']);
     
    print $ag['id']." ".$return."<br>";
    $u++;
}
*/

// conversion onglets catégorie
/*
$DB_site2->query("ALTER TABLE `categorie` CHANGE `onglet` `onglet` VARCHAR( 50 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '0'");
$DB_site2->query("UPDATE categorie SET onglet='base' WHERE onglet='1'");
*/

// mise à jour idées cadeaux, slider
/*
$DB_site2->query("UPDATE idkdos SET site_code='base'");
$DB_site2->query("UPDATE slider SET site_code='base'");
*/



?>