<?php
/*** Fichier qui gère l'envoi auto des fichier EDI HEPPNER, en tache cron ***/
/*** L'heure d'envoi est mise manuellement sur le cron **********************/

include_once("../host_config_admin.php");
include($rootpath."admin/adv/heppner.class.php");
include_once($rootpath."configurations/".$host."/transporteurs/heppner/heppner.php");

$heppner=new heppner($DB_site2,null,$ftp,$host);

$heppner->send();