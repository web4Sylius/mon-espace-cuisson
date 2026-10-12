<?php
ini_set('memoty_limit', '900M');
include("admin_global.php");

//script pour supprimer les mails vieux de 60 jours du serveur. Pas besoin de les garder ils sont déjà sur le serveur.
//s'execute en tache cron tous les jours a 1h30

$dir_out = $rootpath.'configurations/'.$host.'/mails/outbox/';
$dir_in = $rootpath.'configurations/'.$host.'/mails/inbox/';;

$date_limit = time() - (86400 * 60);

//outbox
$list = scandir($dir_out);
foreach ($list as $file) {
	$name = explode("_", $file);

	if(count($name) > 1){
		$date = explode(".", $name[1]);
		$date = $date[0];

		if(is_numeric($date) && $date < $date_limit){
			//del fichier
			unlink($dir_out.$file);
		}


	}
}

//inbox
$list = scandir($dir_in);

foreach ($list as $file) {
	$name = explode("_", $file);

	if(count($name) > 1){
		$date = explode(".", $name[1]);
		$date = $date[0];

		if($date < $date_limit){
			//del fichier
			unlink($dir_in.$file);
		}


	}
}


