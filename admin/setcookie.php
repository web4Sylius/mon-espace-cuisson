<?php

$rootpath="../";
include_once "../configurations/www.mon-espace-cuisson.com/config.php";
include_once "../class/mysql.php";

$DB_site2=new DB_Sql;
$DB_site2->appname      = 'admin boutique';
$DB_site2->appshortname = 'aro';
$DB_site2->database     = $DBname;
$DB_site2->server       = $db_server;
$DB_site2->user         = $DBusername;
$DB_site2->password     = $DBpassword;
$DB_site2->logpath      = "../../log";
$DB_site2->connect();

$sql="SELECT username,password FROM parametresgeneraux 
INNER JOIN acces_admin ON(username=user)
WHERE MD5(CONCAT(connectts,username,password))='".$_GET['d']."' AND connectts>".(time()-5);

$log=$DB_site2->query_first($sql);

if($log!=false){
    $cookietime=intval($_GET['cookietime']);
    $cookie_domain=$_SERVER['HTTP_HOST'];
    setcookie("admin_username",$log['username'],time()+$cookietime,"/",$cookie_domain);
    setcookie("admin_passcode",$log['password'],time()+$cookietime,"/",$cookie_domain);
}


?>