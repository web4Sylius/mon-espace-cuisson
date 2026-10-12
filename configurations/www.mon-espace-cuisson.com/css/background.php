<?php
ini_set("display_errors","on");
$nostat=1;
include($_SERVER['DOCUMENT_ROOT']."/global.php");


$fond=$DB_site->query_first("SELECT * FROM fond WHERE site_code='".$template->site_code()."' AND langue='".$template->langue."' AND ".($sitepro?"pro":"part")."=1");
$season=season();

$etagFile=md5($fond['id'].$season);
$etagHeader=(isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : false);
if($etagHeader == $etagFile){
    header("HTTP/1.1 304 Not Modified");
    exit;
}

header("Etag: $etagFile");
header("cache-control:max-age=300");
header("content-type:text/css");
print ".main { width:100%; background:url('/images/templates/bg_".$season.".jpg') no-repeat bottom center; }
.pro .main { background:none; }";

if($fond=="")
  print ".main .bandeau { margin:0px; padding:0; }";
else
  print ".main .bandeau { background:url('/configurations/".$host."/images/fond/".$fond['id'].".jpg') no-repeat top center; margin:0px; padding:0; }";

print ".art_slider { background:url('/images/templates/".$template->langue."/slider_bg.jpg') no-repeat; }";

function season(){
  $SeasonDates = array('/10/21'=>'hiver','/09/21'=>'ete','/06/21'=>'ete','/03/21'=>'printemps','/01/01'=>'hiver');
  foreach ($SeasonDates AS $key => $value){
       $SeasonDate = date("Y").$key;
       if (strtotime("now") >= strtotime($SeasonDate)){
           return $value;
       }
  }
  return "ete";
}

?>