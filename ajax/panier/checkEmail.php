<?php
require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

$sql = 'SELECT mail FROM utilisateur u WHERE u.mail = "' . mysqli_real_escape_string($DB_site->link_id,$_GET['email']) . '"';
$res = $DB_site->query_first($sql);

if(!$res['mail']) echo 'no';
else echo $res['mail'];
