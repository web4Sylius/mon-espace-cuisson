<?php

$nostat=1;
include($_SERVER['DOCUMENT_ROOT']."/global.php");

$files_desktop=array("font-awesome.css","google-font.css","bootstrap.min.css","css-refonte.css","css-refonte-cc.css","../javascripts/thickbox/thickbox.min.css", "photoswipe.css","default-skin.css","magnific-popup.css","compteur.min.css","css-devis.css", "slider-ambiance.css", "configurateur.css"); // "nouislider.min.css" , "utils.css"
$files_mobile=array("mobile.css");
$files=$template->mobile?$files_mobile:$files_desktop;

$t=0;
foreach($files as $f)
    $t+=filemtime($f);

$etagFile=md5($t.$fond['id']);

$etagHeader=(isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : false);
if($etagHeader == $etagFile){
    header("HTTP/1.1 304 Not Modified");
    exit;
}

header("Etag: $etagFile");
header("cache-control:max-age=300");
header("content-type: text/css");

if(!ob_start("ob_gzhandler")) ob_start();

if(!$template->mobile){

    print "body { background-color:".$body_color."; }\n";
    print "h2.color_bg, p.color_bg { background-color:".$body_color." !important; }\n";

    if(time() >= $fond['date_dbt'] && time() <= $fond['date_fin']){
        if($fond != ""){
            //RÃ©cupÃ©ration des dimensions de l'image de fond
          $aDimensions = getimagesize($_SERVER['DOCUMENT_ROOT']."/configurations/$host/images/fond/".$fond['id'].".jpg");
          print ".wallpaper { display:block; background: url('/configurations/$host/images/fond/".$fond['id'].".jpg') no-repeat center 171px; height: ".($aDimensions[1] + 171)."px !important; width: 100% !important; position: absolute; }\n";
        }
    }
}

foreach($files as $f)
    readfile($f);

ob_end_flush();

?>
