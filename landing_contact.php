<?php
include("global.php");
$erreur = false;
// traitement du formulaire
if(isset($_POST['valid'])){
	// verification des données
	if($_POST['nom']=="" || $_POST['prenom']=="" || $_POST['tel']=="" || !filter_var($_POST['mail'], FILTER_VALIDATE_EMAIL)) $erreur=true;

	//si aucune erreur on enregistre les donnees
	if(!$erreur){

	}
}


$template->evaltemplate("LandingContact","main");

$template->pageparams($template->parts["host"],"Contact","Contact - ".$template->parts["host"]);

$template->out();