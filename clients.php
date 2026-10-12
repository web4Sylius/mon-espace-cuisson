<?php

// include("../admin_global.php");

$rootpath="/var/www/vhosts/mon-espace-cuisson.com/httpdocs/";
$rootpath="/media/disk/www/macave/site2012/";

$host="www.mon-espace-cuisson.com" ;
$nostat=1;
include_once($rootpath."configurations/$host/config_mysql.php");
include($rootpath."class/mysql.php");
include($rootpath."configurations/$host/config.php");
include_once($rootpath."class/fonctions.php");

include_once("xlexport.php");

$DB_site2=new DB_Sql;
$DB_site2->appname='boutique';
$DB_site2->appshortname='boutique';
$DB_site2->database=$DBname;
$DB_site2->server=$db_server;
$DB_site2->user=$DBusername;
$DB_site2->password=$DBpassword;
$DB_site2->connect();

$admin_username=$_COOKIE['admin_username'];
if(isset($_GET['admin_username'])) $admin_username=$_GET['admin_username'];

if (isset($admin_username) && $admin_username != "") {
    if($admin_username!="9d3bb895f22bf0afa958d68c2a58ded7"){
      $ip=$_SERVER['REMOTE_ADDR'];
      $acces=$DB_site2->query_first("SELECT * FROM acces_admin WHERE ip='$ip' AND user='$admin_username' AND connecte=1 ORDER BY timestamp DESC LIMIT 0,1");
      if($acces['timestamp']==false || $acces['timestamp']<time()-3600){
         exit;
      }else{
        $user_info=$DB_site2->query_first("SELECT * FROM parametresgeneraux WHERE username = '$admin_username'");
        if ($user_info[username] != $admin_username) exit;
      }
    }
}else
  exit;


$xl=new xlexport("csv");
$xl->name="export_clients_".date("Ymd");
$xl->addrow(array("email","téléphone","société","nom","prenom","adresse","cp","ville","pays","inscription"));

$sql="SELECT * FROM utilisateur u INNER JOIN pays p ON(p.paysid=u.paysid) WHERE u.allowemail='1' AND vip<>-1";
$clients=$DB_site2->query($sql);
while($client=$DB_site2->fetch_array($clients)){
    $xl->addrow(array($client['mail'],$client['telephone'],$client['raisonsociale'],$client['nom'],$client['prenom'],$client['adresse'],$client['codepostal'],$client['ville'],$client['libelle'],$client['dateinscription'],"http://www.mon-espace-cuisson.com/admin/utilisateur.php?action=editer&userid=".$client['userid']));
}

$xl->export();

?>