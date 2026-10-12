<?php
require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

$c=new configurateur($DB_site);
$conflist=$c->get_list($s,$_COOKIE['admin_username'],5);
echo count($conflist);