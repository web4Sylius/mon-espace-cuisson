<?php
include_once("class/mysql.php");
include_once("class/host.php");

//on défini le HOST :
$host_conf = new Host();
$host=$host_conf->host;
include_once("configurations/".$host_conf->path."/config_mysql.php");
include_once("configurations/".$host_conf->path."/config_variables.php");

$DB_site = new DB_Sql;
$DB_site->appname      = 'boutique';
$DB_site->appshortname = 'boutique';
$DB_site->database     = $DBname;
$DB_site->server       = $db_server;
$DB_site->user         = $DBusername;
$DB_site->password     = $DBpassword;
$DB_site->logpath      = SITE_PATH."/log";
$DB_site->connect();

$host_conf->initialise($DB_site);