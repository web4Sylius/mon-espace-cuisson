<?php
require_once __DIR__.'/../../secrets.php';

ini_set("display_errors","1");
error_reporting(E_ALL);
include("../admin_global.php");


include("XMLCalls_inc.php");
$addr="83.136.166.238";
$port="4849";
$user_name="admin";
$password=IPBX_PASSWORD;
$ligne=array(101=>"10",105=>"10",104=>"13",102=>"14",103=>"19",106=>"19",100=>"00",109=>"00");

if($user_info['tel']==0){
    print "Téléphone utilisateur non configuré";
    log_call("Téléphone utilisateur non configuré");
    exit;
}

if(isset($_GET['tel'])){
    $net = new XMLCalls($addr, $port, $user_name, $password );
    if( !$net->Authenticate() ) {
	print "Erreur d'identification";
	log_call("Erreur d'identification");
	exit;
    }else{
	$tel=formattel($_GET['tel']);
	log_call("Appel ".$tel);
	$net->Transfer($ligne[$user_info['tel']],$user_info['tel'],$tel);
	print "Appel terminé";
	log_call("Appel terminé");
    }
}

function log_call($txt){
    global $user_info;
    $f=fopen("call.log","a");
    fwrite($f,date("d/m/Y H:i")." ".$user_info['username']." ".$txt."\n");
    fclose($f);
}

function formattel($tel){
    return preg_replace("/\.| |-/","",$tel);
}

?>