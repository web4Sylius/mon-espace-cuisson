<?php
include_once($rootpath."/class/host.php");
$host_conf = new Host();

$host=$host_conf->path;
include_once($rootpath."configurations/".$host."/config_variables.php");

extract($_REQUEST);
extract($_COOKIE);

include("../configurations/$host/config_mysql.php");
//include("../class/mysql.php");
//include("../class/facture.class.php");

$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();
$DBpassword="";

require_once("../class/email.php");
require_once("../class/template.class.php");
include_once("../mailconfirmation.php");

$facture=new facture($DB_site,$facturid);
$user_info=$DB_site->query_first("SELECT * FROM utilisateur WHERE userid=".$facture->utilisateur->userid);
$template=new template($facture->utilisateur->langue,$DB_site);
$template->path=$rootpath."templates/".$facture->utilisateur->langue;
mailconfirmation(1,$facture,$user_info,$template);

?>