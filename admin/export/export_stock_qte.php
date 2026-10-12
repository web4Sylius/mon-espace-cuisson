<?php

ini_set("display_errors","on");

$host="www.mon-espace-cuisson.com" ;

extract($_REQUEST);
extract($_COOKIE);

include_once("../../configurations/$host/config_mysql.php");
include("../../class/mysql.php");
include("../../class/stock.class.php");
include("../../class/fonctions.php");

$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();

if (isset($admin_username) && $admin_username != "") {
    if($admin_username!="9d3bb895f22bf0afa958d68c2a58ded7"){
      $ip=$_SERVER['REMOTE_ADDR'];
      $acces=$DB_site->query_first("SELECT * FROM acces_admin WHERE ip='$ip' AND user='$admin_username' AND connecte=1 ORDER BY timestamp DESC LIMIT 0,1");
      if($acces['timestamp']==false || $acces['timestamp']<time()-3600){
         exit;
      }else{
        $user_info=$DB_site->query_first("SELECT * FROM parametresgeneraux WHERE username = '$admin_username'");
        if ($user_info[username] != $admin_username) exit;
      }
    }
}else
  exit;

header('Content-Type: text/html; charset=utf-8');

$stock=new stock($DB_site);
echo "<html><head>
	<title>Instantanné de quantité en stock par lieu, par marque et par type de produit</title>
	<style>
	#stock_table { border-collapse:collapse; font-size:11px; }
	#stock_table td { border:solid thin #cccccc; }
	.td_factures_alt1{ background-color: #E0E0E0; vertical-align: middle;  text-align:left;  font-family:Verdana; font-size: 10px;  color: #000000;}
	.td_factures_alt2{ background-color: #EFEFEF; vertical-align: middle;  text-align:left;  font-family:Verdana; font-size: 10px;  color: #000000;}
	</style>
	</head><body>";
echo "<h3>Instantanné de quantité en stock par lieu, par marque et par type de produit</h3>";
$lieux=$stock->lieux();
foreach($lieux as $id=>$lieu){
	tableau_stock_marque_type($id, "pour le lieu ".$lieu);
}
tableau_stock_marque_type(null, " TOTAL GLOBAL ");
echo "</body></html>";


?>