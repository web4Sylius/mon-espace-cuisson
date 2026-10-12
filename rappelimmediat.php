<?php

include("global.php");

	if(isset($user_info) && !is_null($user_info) && !empty($user_info)){
		$pays = $DB_site->query_first("SELECT libelle FROM pays WHERE paysid=".intval($user_info['paysid']));
		$template->parts['nom'] = $user_info['nom']." ".$user_info["prenom"];
		$template->parts['mail'] = $user_info['mail'];

		if($user_info['portable'] != '')
			$template->parts['telephone'] = $user_info['portable'];
		else
			$template->parts['telephone'] = $user_info['telephone'];

		
		$template->parts['pays'] = $pays['libelle'];
	}

	$template->evaltemplate('RappelImmediat','rappel_immediat');
	print stripslashes($template->parts["rappel_immediat"]);






?>