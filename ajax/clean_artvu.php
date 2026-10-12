<?php
header('Content-Type: text/html; charset=iso-8859-1');
require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

$session = $_GET['s'];

if(strlen($session)==32){
	$DB_site->query("DELETE FROM article_vu WHERE s='".$session."'");
}	
echo "articlevusbar";