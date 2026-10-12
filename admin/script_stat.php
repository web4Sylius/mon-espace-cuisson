<?php

include_once("host_config_admin.php");

include_once($rootpath."class/email.php");

$destinataire="pr@ma-cave-a-vin.fr";

$t=mktime();
$t-=86400;
$dfile="log/src_".date("Y-m-d",$t).".log";

$data=readcsvfile($dfile);
if($data!=false){
    $data=fileheader($data);
    $mail=new email();
    $mail->destinataire=$destinataire;
    $mail->expediteur="info@ma-cave-a-vin.fr";
    $mail->expname="Ma cave à vin";
    $mail->to="webmaster@ma-cave-a-vin.fr";
    $mail->subject="Recherches effectuées le ".date("d/m/Y",$t);
    $mail->attachstring($data,"stat_mcav_".date("Y-m-d",$t).".csv","text/comma-separated-values");
    $result=$mail->sendmail();
}

var_dump($result);

if(date("w")==1){
    $tstart=$t-(86400*7);
    $wdata=fileheader();
    for($i=$tstart;$i<$t;$i+=86400){
	$data=readcsvfile("log/src_".date("Y-m-d",$i).".log");
	if($data!=false)
	    $wdata.="\n".$data;
	
    }

    $mail=new email();
    $mail->destinataire=$destinataire;
    $mail->expediteur="info@ma-cave-a-vin.fr";
    $mail->expname="Ma cave à vin";
    $mail->subject="Recherches effectuées la semaine ".date("W",$t);
    $mail->attachstring($wdata,"stat_mcav_".date("Y-m-d",$tstart)."_".date("Y-m-d",$t).".csv","text/comma-separated-values");
    $result=$mail->sendmail();
}


function readcsvfile($name){
    global $rootpath;

    $ret="";
    if(file_exists($rootpath.$name)){
	$f=fopen($rootpath.$name,'r');
	$ret=fread($f,filesize($rootpath.$name));
	fclose($f);
    }else
	return false;

    return $ret;
}

function fileheader($data=""){
    return "\"Entrée sur le site\",\"IP\",\"FAI\",\"Pays\",\"OS\",\"Navigateur\",\"Provenance\",\"Date recherche\",\"Résultats\",\"Recherche\"\n".$data;
}

?>