<?php
require_once __DIR__.'/../../secrets.php';
$rootpath="/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/";

require_once($rootpath."configurations/www.ma-cave-a-vin.fr/config_mysql.php");
require_once($rootpath."class/mysql.php");
require_once($rootpath."class/fonctions.php");
require_once($rootpath."class/devises.class.php");
require_once($rootpath."class/stock.class.php");

ini_set('display_errors',1);

$login     = CDISCOUNT_API_LOGIN;
$password  = CDISCOUNT_API_PASSWORD;
$login_url = base64_encode($login.':'.$password);
$url_call  = "https://sts.cdiscount.com/users/httpIssue.svc/?realm=https://wsvc.cdiscount.com/MarketplaceAPIService.svc";

//Récupération du token
$oCurl = curl_init();
//Option: Url to call       
curl_setopt( $oCurl, CURLOPT_URL, $url_call );
//Option: Do not display the result but store it in a variable
curl_setopt( $oCurl, CURLOPT_RETURNTRANSFER, 1);
//Option: Active HTTP Post
curl_setopt( $oCurl, CURLOPT_HTTPHEADER, array("Authorization : Basic=".$login_url) );
//Option: Set HTTP Parameters
$token = curl_exec( $oCurl );
$token = strip_tags($token);

//Construction de l'archive (avec zip.lib.php)

//Envoi de l'archive par post