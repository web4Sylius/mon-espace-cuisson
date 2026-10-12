<?php

ini_set("display_errors",1);

$rootpath=dirname(dirname(__DIR__));

include($rootpath."/admin/admin_global.php");

include($rootpath."/class/cocktel.class.php");

$cocktel=new cocktel("http://acilyon.cocktel.fr",$user_info['username'],$user_info['cocktelpass'],"json","/tmp"); 

$cocktel->ComposerNumeroTelephone($cocktel->encodeNum($_GET['dest']),isset($_GET['caller'])?$_GET['caller']:null);

sleep(1);

$json=json_decode($cocktel->AppelsEnCours(true));

foreach($json->phones as $phone){
    foreach($phone->calls as $call){
        if($call->local->num==$_GET['caller'] && $call->dlgstate==2){
            print $call->callid;
            exit;
        }
    }
}

print 0;
