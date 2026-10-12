<?php

include("../admin_global.php");
include("article.php");

if(isset($_GET['txt'])){
    $src=$_GET['txt'];

    $ret="";
    $prod=new article();
    $list=$prod->search("artcode LIKE '%$src%'",'');
    foreach($list as $rprod){
              $ret.="<prod artid=\"".$rprod->artid."\" id=\"".$rprod->artcode."\" price=\"".$rprod->prixht."\" strike_out=\"".$rprod->strike_out."\" buyprice=\"\" barcode=\"\" stock=\"\">".html_entity_decode($rprod->libelle,ENT_COMPAT,"ISO-8859-15")."</prod>\n";
    }
}

if(isset($_GET['artid'])){
    $ret="";
     $prod=new article();
     $list=$prod->caracteristiques($_GET['artid']);
     foreach($list as $car=>$listval){
                   foreach($listval as $id=>$val)
                                    $ret.="<caract name=\"$car\" id=\"$id\" value=\"$val\" />\n";
     }
}


header("Cache-Control: no-cache");
header("Content-type:text/xml");

print "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>
<xml>\n";

print $ret;

print "</xml>\n";

?>