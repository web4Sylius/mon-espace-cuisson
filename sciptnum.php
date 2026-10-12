<?php
//script pour modifier les numero de tel dans la table utilisateur

require ("global.php");

$sql="SELECT userid, telephone FROM utilisateur WHERE telephone!=''";
$req = $DB_site->query($sql);
while ($row = mysql_fetch_array($req)) {
	$telephone = str_replace(array(" ",".","-"), "", $row['telephone']);
	if($telephone != $row['telephone']){
		$DB_site->query("UPDATE utilisateur SET telephone='".$telephone."' WHERE userid ='".$row['userid']."'");
	}
}

header('Location:https://www.mon-espace-cuisson.com/');



















