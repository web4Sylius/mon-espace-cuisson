<?php
header('Content-Type: text/html; charset=iso-8859-1');
require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

$session = $_GET['s'];
$session = mysqli_real_escape_string($DB_site->link_id,$_GET['s']);
$sqlwhere = "AND s='$session'";

if(isset($_COOKIE['admin_username']) && $_COOKIE['admin_username'] != ""){
	$sqlwhere = "AND commercial='".mysqli_real_escape_string($DB_site->link_id,$_COOKIE['admin_username'])."'";
}
$DB_site->query("DELETE FROM configurateur WHERE 1 $sqlwhere");
echo "popup_config";
