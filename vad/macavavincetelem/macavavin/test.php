<?php

$rootpath="/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/";

include($rootpath."configurations/www.ma-cave-a-vin.fr/config_mysql.php");
include($rootpath."mysql.php");
$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();
$DBpassword="";

$host = "www.ma-cave-a-vin.fr" ;
require_once($rootpath."admin/email.php");
include_once($rootpath."mailconfirmation.php");


?> 
