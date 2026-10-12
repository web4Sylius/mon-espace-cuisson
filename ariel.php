<?php
require_once __DIR__.'/secrets.php';
include "global.php";
//test

/*
//script pour nettoyer les commandes d'un utilisateur (pour les dev qui ont trop de commandes test)
$userid = 15834; //userid a mettre en dur


$req  = $DB_site->query("SELECT facturid FROM facture WHERE userid ='".$userid."'");

while( $facture = $DB_site->fetch_array($req)){
	$req2 = $DB_site->query("SELECT * FROM lignefacture WHERE factureid = '".$facture['facturid']."'");
	while($lfacture = $DB_site->fetch_array($req2)){
		$DB_site->query("DELETE FROM lignefacturecaracteristique WHERE lignefactureid='".$lfacture['lignefactureid']."' ");
		$DB_site->query("DELETE FROM lignefacturegarantie WHERE lignefactureid='".$lfacture['lignefactureid']."' ");
		$DB_site->query("DELETE FROM lignefacturegarantiecmd WHERE lignefactureid='".$lfacture['lignefactureid']."' ");
		if($lfacture['lignefacturedissid'] != 0){
			$DB_site->query("DELETE FROM lignefacturediss WHERE lignefactureid='".$lfacture['lignefacturedissid']."' ");
		}
		$DB_site->query("DELETE FROM lignefacture WHERE factureid='".$facture['facturid']."' ");
	}
	$DB_site->query("DELETE FROM facture WHERE facturid='".$facture['facturid']."' ");
	$DB_site->query("DELETE FROM suivi_commande WHERE facturid='".$facture['facturid']."' ");
}

$DB_site->query("DELETE FROM suivi_client WHERE userid='".$userid."' ");
$DB_site->query("DELETE FROM mail_inbox WHERE  userid='".$userid."' ");
$DB_site->query("DELETE FROM mail_outbox WHERE userid='".$userid."' ");
$DB_site->query("DELETE FROM article_vu WHERE userid='".$userid."' ");
$DB_site->query("DELETE FROM article_vu_arch WHERE userid='".$userid."' ");
$DB_site->query("DELETE FROM comparateur WHERE userid='".$userid."' ");
$DB_site->query("DELETE FROM stat WHERE user ='".$userid."'");

$req = $DB_site->query("SELECT * FROM projet WHERE userid ='".$userid."'");
while($projet = $DB_site->fetch_array($req)){
	$DB_site->query("DELETE FROM projet_relation WHERE projetid ='".$projet['projetid']."'");
}
$DB_site->query("DELETE FROM projet WHERE userid ='".$userid."'");


$req = $DB_site->query("SELECT * FROM configurateur WHERE userid='".$userid."'");
while($configurateur = $DB_site->fetch_array($req)){
	$DB_site->query("DELETE FROM configurateur_reponse WHERE configurateurid='".$configurateur['configurateurid']."' ");	
}
$DB_site->query("DELETE FROM configurateur WHERE userid='".$userid."' ");

$DB_site->query("DELETE FROM utilisateur WHERE userid='".$userid."' ");

*/

$email				= "contact@garantim.fr";


$plaintextpassword 	= ARIEL_TEST_PASSWORD;
$passwordtotry 		= ARIEL_TEST_PASSWORD;
$hash = password_hash($plaintextpassword, PASSWORD_BCRYPT);
echo "hash : ".$hash."<br><br>";
$connect_ok = password_verify($passwordtotry, $hash);
if($connect_ok){
	echo "connection :OK";
} else {
	echo "mauvais mot de passe";
}


?>