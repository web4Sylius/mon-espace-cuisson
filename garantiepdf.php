<?php

if(!isset($_GET['c'])) exit;

$facturid=($_GET['c']-10)/123;
$file="admin/sfg_pdf/$facturid.pdf";

if(file_exists($file)){
    header("Content-Type: application/force-download");
    header("Content-Length: ".filesize($file));
    header("Content-Disposition: attachment; filename=\"".urlencode($facturid).".pdf\"");
    header("Content-Transfer-Encoding: text/plain\n");
    header("Pragma: no-cache");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0, public");
    readfile($file);

}

?>