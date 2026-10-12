<?php

$hostid = intval($_GET['id']);
$url = '/';
if ($hostid > 0) {
	include(dirname(__FILE__).'/host.php');
	$content = host($hostid);//Find logo corresponds to the remote site information
	if(count($content) == 6) {
		$url = $content[0];
		header('Location:'.$url);
	}
}
