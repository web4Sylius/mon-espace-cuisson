<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";

if(isset($_POST['zone'])){
    $DB_site2->query("DELETE FROM tnt_grille");
    foreach($_POST['zone'] as $zone=>$tranches){
	foreach($tranches as $poids=>$paht) {
	    list($from,$to)=explode("-",$poids);
	    $DB_site2->query("INSERT INTO tnt_grille SET zone=".intval($zone).", debut=".intval($from).", fin=".intval($to).", paht=".floatval($paht));
	}
    }
}


$paht=array();
$grille=$DB_site2->query("SELECT * FROM tnt_grille");
while($tranche=$DB_site2->fetch_array($grille))
    $paht[$tranche['zone']][$tranche['debut']]=$tranche['paht'];
   
print "<h1>Grille tarif FEDEX Express</h1>";

$zonenb=10;

print "<form method=\"post\">";

print "<table id=\"grille\">
<thead>
<tr>
  <td>Poids</td>";
for($z=1;$z<=$zonenb;$z++)
	print "<td>Zone $z</td>";
print "</tr>
</thead>";

for($i=0;$i<=30;$i++) {
    $zones="";
    for($z=1;$z<=$zonenb;$z++)
	$zones.="<td><input type=\"text\" name=\"zone[$z][".(1000*$i)."-".(1000*($i+1)-1)."]\" value=\"".$paht[$z][$i*1000]."\"></td>";
  $i2 = $i+1;
    print <<<HTML
    <tr>
      <td>$i $i2 kg</td>
      $zones
    </tr>
HTML;
}

for($i=31;$i<=100;$i+=5) {
    $zones="";
    $to=$i+4;
    for($z=1;$z<=$zonenb;$z++)
	$zones.="<td><input type=\"text\" name=\"zone[$z][".(1000*$i)."-".(1000*($to+1)-1)."]\" value=\"".$paht[$z][$i*1000]."\"></td>";
    print <<<HTML
    <tr>
      <td>$i à $to kg</td>
      $zones
    </tr>
HTML;
}

for($i=101;$i<=200;$i+=20) {
    $zones="";
    $to=$i+19;
    for($z=1;$z<=$zonenb;$z++)
	$zones.="<td><input type=\"text\" name=\"zone[$z][".(1000*$i)."-".(1000*($to+1)-1)."]\" value=\"".$paht[$z][$i*1000]."\"></td>";
    print <<<HTML
    <tr>
      <td>$i à $to kg</td>
      $zones
    </tr>
HTML;
}

$zones="";
$to=$i+4;
for($z=1;$z<=$zonenb;$z++)
    $zones.="<td><input type=\"text\" name=\"zone[$z][1000000-1000000]\" value=\"".$paht[$z][1000000]."\"></td>";
print <<<HTML
    <tr>
      <td>Au delà par 20kg</td>
      $zones
    </tr>
HTML;

print "</table>";

print "<input type=\"submit\" value=\"Enregistrer\">";

print "</form>";


print <<<CSS
<style>
#grille { border-collapse:collapse; }
#grille td { padding:4px; }
#grille thead td { text-align:center; font-weight:bold; background:yellow; }
#grille tr:nth-child(even) { background-color:#DEDEDE; }
#grille tr:nth-child(odd) { background-color:#CDCDCD; }
#grille input { width:50px; border:solid 1px black; background:none; }
</style>
CSS;

include "./footer.php";


?>