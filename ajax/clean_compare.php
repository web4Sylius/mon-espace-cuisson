<?php
header('Content-Type: text/html; charset=iso-8859-1');
require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

$typearticleid = $_GET['typearticleid'];

if(!isset($typearticleid) || $typearticleid == ''){
     exit;
}

$comparateur=new comparateur($DB_site);
$delete=$comparateur->remove_articles($typearticleid);

$delartids = implode(",",$delete);

echo $delartids."|||".$template->translate("/comparateur/comparer","F");