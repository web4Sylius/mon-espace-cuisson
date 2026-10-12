<?php

ini_set("display_errors","on");

$rootpath="/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/";
// $rootpath="/media/disk/www/macave/site2012/";

include($rootpath."configurations/www.ma-cave-a-vin.fr/config_mysql.php");
include($rootpath."class/mysql.php");
include($rootpath."class/DAOgarantie.class.php");
include($rootpath."class/facture.class.php");
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
require_once($rootpath."class/email.php");
require_once($rootpath."class/devises.class.php");
require_once($rootpath."class/template.class.php");
include_once($rootpath."mailconfirmation.php");
include($rootpath."admin/dissociation.php");

$facture=new facture($DB_site,$_GET['transaction_id']);
$template=new template($facture->utilisateur->langue,$DB_site);
$template->path=$rootpath."templates/".$facture->utilisateur->langue;
mailconfirmation(1,$facture,null);

?>