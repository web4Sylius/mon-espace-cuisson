<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/global.php');

if(isset($_GET['artid']) && is_numeric($_GET['artid'])){
	$file_url = SITE_URL.'/images/mail/fiche_energetique.php?artid='.$_GET['artid'];
	header('Content-Type: application/octet-stream');
	header("Content-Transfer-Encoding: Binary"); 
	header("Content-disposition: attachment; filename=\"" . basename($file_url) . "\""); 
	readfile($file_url);
}