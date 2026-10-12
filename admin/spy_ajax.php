<?php

require "admin_global.php";

include "spy.class.php";

$spy=new spy($user_info['username'],$user_info['password']);

if($_POST['action']=="add"){
    if($_POST['id']==""){
	$result=$spy->request("add",array("url"=>$_POST['url'],"artcode"=>$_POST['artcode']));
    }else{
	$result=$spy->request("update",array("id"=>$_POST['id'],"url"=>$_POST['url'],"artcode"=>$_POST['artcode']));
    }
}

if($_POST['action']=="del"){
    $result=$spy->request("del","&id=".$_POST['id']);
}

print $result;

?>