<?php
require_once($_SERVER['DOCUMENT_ROOT']."/global.php");
$txt = htmlspecialchars($_GET['txt']);
if(!isset($_COOKIE['admin_username']) && !in_array($_SERVER['REMOTE_ADDR'], $aAdminIPS) ){
		saveMotCle($txt);
	}

?>