<?php 
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");

$DB_site = clone($DB_site2);
require_once(SITE_PATH."/class/fonctions.php");

$utilisateur['userid']  = $_POST['utilisateur_id'];

if(isset($_POST['apercu_relance']) && $_POST['apercu_relance'] == 1) $apercu = true;

include($_SERVER['DOCUMENT_ROOT'].'/admin/includes/temp/last_relance.php');