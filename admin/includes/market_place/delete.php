<?php
if(isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'localhost') !== false){
    $backpath = "d:/wamp/www/ma-cave-a-vin/admin";
} else if(isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'test2015') !== false) {
    $backpath = "/home/ma-cave-a-vin-2015/admin";
} else if(isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'test') !== false) {
    $backpath = $_SERVER['DOCUMENT_ROOT']."/admin";
} else {
    $backpath = "/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/admin";
}

$tab = $backpath.$_POST['page'];
if (file_exists($tab))
{
	unlink($tab);
	header('Location: '.$_POST['URL']);
	exit;
}
else
{
	echo "Fichier non existant";
}

?>