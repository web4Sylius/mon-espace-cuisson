<?php
require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

$sql1 = 'SELECT mail FROM utilisateur u WHERE u.mail = "' . mysqli_real_escape_string($DB_site->link_id,$_GET['email']) . '"';
$res1 = $DB_site->query_first($sql1);

if(!$res1['mail']) echo 'no';
else {

$sql2 = 'SELECT password FROM utilisateur u WHERE u.mail = "' . mysqli_real_escape_string($DB_site->link_id,$_GET['email']) . '" AND u.password = "' . MD5(mysqli_real_escape_string($DB_site->link_id,$_GET['password'])) . '"';
$res2 = $DB_site->query_first($sql2);

if(!$res2['password']) echo 'nopassword';
else echo $res2['password'];
}