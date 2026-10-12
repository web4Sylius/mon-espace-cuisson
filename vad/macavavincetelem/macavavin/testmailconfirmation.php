<?php

ini_set("dipslay_errors",1);

$rootpath="/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/";

$host = "www.ma-cave-a-vin.fr" ;
require_once($rootpath."class/email.php");
require_once($rootpath."class/devises.class.php");
require_once($rootpath."class/template.class.php");
require_once($rootpath."class/garantie.class.php");
require_once($rootpath."class/DAOgarantie.class.php");
require_once($rootpath."class/DAOarticle.class.php");
require_once($rootpath."class/lignefacturegarantie.class.php");
require_once($rootpath."class/DAOlignefacturegarantie.class.php");
require_once($rootpath."class/DAOpays.class.php");
require_once($rootpath."class/DAOfournisseur.class.php");
include_once($rootpath."mailconfirmation.php");
include($rootpath."admin/dissociation.php");
include($rootpath."configurations/www.ma-cave-a-vin.fr/config_mysql.php");
include($rootpath."class/mysql.php");
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

require_once($rootpath."class/template.class.php");

include_once($rootpath."mailconfirmation.php");

$template = new template("FR",$DB_site);

$facture = new facture($DB_site, 89434);

mailconfirmation(1,$facture,$user_info);


?>