<?php

include("admin_global.php");

$field="interrupteur";

$req=$DB_site2->query("SELECT path,FR FROM traduction WHERE path LIKE '/article/$field%'");
while($t=$DB_site2->fetch_array($req)) $trad[$t['path']]=$t['FR'];

print "<table>";
$rtrad=array_flip($trad);
$req=$DB_site2->query("SELECT artid,artcode,$field FROM article WHERE active IN ('0','1')");
while($art=$DB_site2->fetch_array($req)){
    if(in_array(trim($art[2]),$trad) || $art[2]==""){
      print "<tr><td>".$art['artcode']."</td><td>".$art[2]."</td><td>".$rtrad[$art[$field]]."</td></tr>";
      $DB_site2->query("UPDATE article SET $field='".$rtrad[$art[$field]]."' WHERE artid=".$art['artid']);
    }else
      print "<tr><td><b>".$art['artcode']."</b></td><td>".$art[2]."</td></tr>";
}
print "</table>";

?>