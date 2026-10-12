<?php

//include("../admin_global.php");

$file=$_GET['file'];
$year=$_GET['year'];

if(substr($file,0,2)=="..") exit;

if(file_exists($year."/".$file)){
    $size = filesize($year."/".$file);
    header("Content-Type: application/force-download; name=\"" . $file . "\"");
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: $size");
    header("Content-Disposition: attachment; filename=\"" . $file . "\"");
    header("Expires: 0");
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");
    readfile($year."/".$file);
}
exit();

?>
