<?php

include_once($_SERVER["DOCUMENT_ROOT"]."/mec/class/mysql.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/mec/class/host.php");

//on défini le HOST :
$host_conf = new Host();
$host=$host_conf->host;
include_once($_SERVER["DOCUMENT_ROOT"]."/mec/configurations/".$host_conf->path."/config_mysql.php");
include_once($_SERVER['DOCUMENT_ROOT']."/mec/configurations/".$host_conf->path."/config_variables.php");

$DB_site2=new DB_Sql;
$DB_site2->appname='admin boutique';
$DB_site2->appshortname='aro';
$DB_site2->database=$DBname;
$DB_site2->server=$db_server;
$DB_site2->user=$DBusername;
$DB_site2->password=$DBpassword;
$DB_site2->logpath=LOG_URL;
$DB_site2->connect();
$DBpassword="";
$DB_site2->password="";

$host_conf->initialise($DB_site2);