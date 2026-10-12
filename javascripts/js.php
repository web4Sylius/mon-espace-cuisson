<?php

ini_set("display_errors",1);

include "../class/jsmin.php";
include "../class/fonctions.php";

$cachefile="cache.js";

// header("Cache-Control: no-cache, must-revalidate");
// header("Expires: ".gmdate('D, d M Y H:i:s \G\M\T',$cachelimit));
header("Content-type:application/javascript");

$filelist=array("bootstrap.min.js","jquery.js","thickbox/thickbox.min.js","fonctions.js","cookies.js","ajax.js","ral.js",
"fly-to-basket2.js","smoothscroll.js","rr.min.js","articleprix.js","calendrier.min.js","bar.js",
"noUiSlider/nouislider.min.js","bowser.js","visiteur.js");


/*$mobile=array("jquery-1.11.3.min.js","jquery-migrate-1.2.1.min.js","ajax.js","fonctions.js",
"noUiSlider/nouislider.min.js","fly-to-basket2.js","thickbox/thickbox.js","rr.js","calendrier.js",
"articleprix.js","ral.js","smoothscroll.js","mobile.js");

if(isset($_GET['m'])){
    $filelist=$mobile;
    $cachefile="mcache.js";
}*/

$updatecache=true;
if(file_exists($cachefile)){
    $cachemodif=filemtime($cachefile);
    foreach($filelist as $jsfile)
    if(filemtime($jsfile)>$cachemodif) $updatecache=true;
}else
    $updatecache=true;


if($updatecache){
    $cachemodif=time();
    $js="";
    foreach($filelist as $jsfile){
      $data=$str = str_replace("\xEF\xBB\xBF",'',file_get_contents($jsfile)); 
      $js.=$data."\n";

    }

//     $js=JSMin::minify($js);
    $f=fopen($cachefile,"w");
    fputs($f,$js);
    fclose($f);

}else
    $js=file_get_contents($cachefile);

$etagFile=md5($js);
$etagHeader=(isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : false);
if($etagHeader == $etagFile){
    header("HTTP/1.1 304 Not Modified");
    exit;
}

header("Last-Modified: ".gmdate("D, d M Y H:i:s", $cachemodif)." GMT");
//set etag-header
header("Etag: $etagFile");
//make sure caching is turned on
header('Cache-Control: public');

print $js;

?>
