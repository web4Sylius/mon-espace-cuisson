<?php
require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

$comparateur=new comparateur($DB_site);

echo count($comparateur->articles());