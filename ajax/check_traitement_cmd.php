<?php
require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

if(!isset($_GET['facture_id']) || !is_numeric($_GET['facture_id'])) return 'ko';

$aStatutsExclus = array(
	1, //Validée
	5, //Expédiée
	6, //Remboursée
	7, //Validée en attente de paiement
	8 //Expédiée en attente de paiement
);

$facture = new facture($DB_site, $_GET['facture_id']);
$facture->updateGclid($_GET['gclid']);

if(in_array($facture->valide, $aStatutsExclus))
{
	echo 'ko';
}
else
{
	echo 'ok';
}