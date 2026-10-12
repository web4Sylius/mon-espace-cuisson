<?php

require "admin_global.php";

if(isset($_POST['artcode'])){
    $DB_site2->query("INSERT INTO market_place_champs SET market_place='rdc', artcode='".$_POST['artcode']."', champ='mcid', valeur='".$_POST['mcid']."', limite='50', enabled=1");
}

$liste=$liste2=array();

$requete=$DB_site2->query("SELECT * FROM market_place_champs WHERE market_place='rdc' group by artcode");
while($row=$DB_site2->fetch_array($requete)){
    $liste[]=$row['artcode'];
}

$requete=$DB_site2->query("SELECT * FROM market_place_champs WHERE market_place='rdc' and champ='mcid'");
while($row=$DB_site2->fetch_array($requete)){
    $liste2[]=$row['artcode'];
}

foreach($liste as $artcode){
    if(!in_array($artcode,$liste2)) print "<br>$artcode";
}

print "<form method=\"post\">
artcode <input type=\"text\" name=\"artcode\">
<br>mcid <input type=\"text\" name=\"mcid\">
<br><input type=\"submit\">
</form>";

?>