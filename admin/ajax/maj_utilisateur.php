<?php
header('Content-Type: text/html; charset=iso-8859-1');

require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");

if(!isset($_GET['userid']) || empty($_GET['userid'])) exit;
if(!isset($_GET['champs']) || empty($_GET['champs'])) exit;
if(!isset($_GET['value']) || $_GET['value'] == '') exit;

$sql = "UPDATE utilisateur SET ";
$sql .= "`".$_GET['champs']."` = ".mysqli_real_escape_string($DB_site2->link_id,$_GET['value']);
$sql .= " WHERE userid = ".intval($_GET['userid']);
$DB_site2->query($sql);