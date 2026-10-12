<?php

require "admin_global.php";
include "../class/facture.class.php";

if(!isset($_GET['txt'])) exit;

if(preg_match("/[0-9]{5}/",$_GET['txt'])==1){
  $f=new facture($DB_site2,$_GET['txt']);
  print "<table>
  <tr><td>".$f->facturid."</td><td>".$f->utilisateur->nom."</td><td>".$f->validetxt."</td><td>".number_format($f->totalttc,2,',','')."&euro;</td><td><a href=\"javascript:selectfacture(".$_GET['id'].",".$f->facturid.",".$f->totalttc.")\">S&eacute;lectionner</a></td></tr>
  </table>";
}else{
  $f=new facture($DB_site2);
  $list=$f->search("nom LIKE '%".$_GET['txt']."%' OR userid IN (SELECT userid FROM utilisateur WHERE nom LIKE '%".$_GET['txt']."%')");
  print "<table>";
  foreach($list as $facture){
    print "<tr><td>".$facture->facturid."</td><td>".$facture->utilisateur->nom."</td><td>".$facture->validetxt."</td><td>".number_format($facture->totalttc,2,',','')."&euro;</td><td><a href=\"javascript:selectfacture(".$_GET['id'].",".$facture->facturid.",".$facture->totalttc.")\">S&eacute;lectionner</a></td></tr>";
  }
  print "</table>";
}


?>