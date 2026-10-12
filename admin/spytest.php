<?php

require "admin_global.php";
include "spy.class.php";

$spy=new spy($user_info['username'],$user_info['password']);

$url=$_GET['url'];
$host=parse_url($url,PHP_URL_HOST);

// $data=implode('',@gzfile($serverurl."&action=pattern&url=".$host));
$data=$spy->request("pattern","&url=".$host);
$lines=explode("\r\n",$data);

// var_dump($lines);

// $result=preg_split('~,(?=\s*(".*?,.*?"|"[^"]*"))~', $lines[0]);


if($lines[0]==""){
    affiche("Ce site n'est pas reconnu","red");
}

$regex=str_replace("/","\\/",$lines[0]);
$regex="/".$regex."/i";

$ch=curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
if(($html=curl_exec($ch))===false) affiche(curl_error($ch),"red");
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if($httpCode==404) affiche("Page introuvable","red");

preg_match($regex,$html,$price);
if(is_null($price[1])){
    $print="Prix introuvable";
    $color="red";
}else{
    $print="Prix identifié : <b>".$price[1]."</b>";
    $color="green";
}

affiche($print,$color);

function affiche($print,$color){ 
    
    print "<span style=\"color:$color;\">$print</span>";
    exit;
}
    
?>