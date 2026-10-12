<?php

include("../admin_global.php");
include("xlexport.php");

if(!isset($_GET['site'])) exit;

$serverurl="http://88.184.216.175:1983/macave/sync.php?username=".$user_info['username']."&password=".$user_info['password'];


$xl=new xlexport("csv");
$xl->name="export_".date("Ymd")."_".$_GET['site'];
$xl->addrow(array("Référence Acilyon","Référence fabricant","URL","Prix ".$_GET['site'],"Prix ma cave","Prix achat","Mise à jour"));

$data= implode('',gzfile($serverurl."&action=siteurl&site=".urlencode($_GET['site'])));

$lines=explode("\r\n",$data);
foreach($lines as $line){
    $fields=explode("\",\"",$line);
    for($i=0;$i<count($fields);$i++) $fields[$i]=trim(str_replace("\"","",$fields[$i]));

    $r=$DB_site2->query("SELECT artcode_eurocave,prix,paht FROM article WHERE artcode LIKE '$fields[0]'");

    $article=$DB_site2->fetch_array($r);
    $date="?";
    if($fields[3]!="") $date=date("d/m/Y",$fields[3]);
    $xl->addrow(array($fields[0],$article[0],$fields[1],$fields[2],$article[1],round($article[2]*1.196,2),$date));
}

$xl->export();

?>