<?php

include("global.php");

/*
$req=$DB_site->query("SELECT * FROM ral ORDER BY ral");
while($ral=$DB_site->fetch_array($req)){
    $DB_site->query("INSERT INTO caracteristiquevaleur SET caractid=33, libelleFR='".$ral['ral']." brillant : ".addslashes($ral['FR'])."', libelleEN='".$ral['ral']." glossy: ".$ral['EN']."', libelleSP='".$ral['ral']." brillante:".$ral['SP']."'");
    $caractvalid=$DB_site->insert_id();
    $DB_site->query("INSERT INTO caractval_ral SET caractvalid=".$caractvalid.", ral='".$ral['ral']."'");

    $DB_site->query("INSERT INTO caracteristiquevaleur SET caractid=34, libelleFR='".$ral['ral']." mat : ".addslashes($ral['FR'])."', libelleEN='".$ral['ral']." matt: ".$ral['EN']."', libelleSP='".$ral['ral']." mate:".$ral['SP']."'");
    $caractvalid=$DB_site->insert_id();
    $DB_site->query("INSERT INTO caractval_ral SET caractvalid=".$caractvalid.", ral='".$ral['ral']."'");
}
*/

include("class/ral.class.php");

$ral=new ral($DB_site);
$ral->caractid=33;

print $ral->generate();

?>