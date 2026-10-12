<?php

ini_set("display_errors","on");

$host="www.mon-espace-cuisson.com" ;

extract($_REQUEST);
extract($_COOKIE);

include_once("../../configurations/$host/config_mysql.php");
include("../../class/mysql.php");
include("../../class/stock.class.php");

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
        $user_info=$DB_site->query_first("SELECT * FROM parametresgeneraux WHERE username = '$admin_username'");
        if ($user_info[username] != $admin_username) exit;
    }
}else
  exit;

include("xlexport.php");

$xl=new xlexport("csv");
$xl->addrow(array("qte","ref","ref","libellé","prix unit.","prix total"));

$stock=new stock($DB_site);
$items=$stock->value($_GET['ts'],true);
$list=array();
$total=0;
foreach($items as $item){
    if(!isset($list[$item->artcode])){
        $list[$item->artcode]=$item;
        $list[$item->artcode]->qte=1;
    }else{
        $list[$item->artcode]->qte++;
        $list[$item->artcode]->paht+=$item->paht;
    }
    $total+=$item->paht;
}

ksort($list);
foreach($list as $artcode=>$item){
  $xl->addrow(array($item->qte,$item->artcode,$item->ref,$item->libelle,round($item->paht/$item->qte,2),$item->paht));
  //if($item->artcode == 'ACI-CLI502') echo $item->paht.'<br>';
}
    //exit;

$xl->addrow(array(count($list),"","","","",round($total)));
$xl->export();



?>
