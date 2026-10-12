<?php

require ("global.php");

$template->parts["pagetype"]="acc";
$metatitle=$template->parts["title"]=$template->translate("/article/accessoires/besoin_accessoires","F");
$template->evaltemplate("IndexAccessoires","description");

$metadesc=$metatitle.". ".$template->translate("/article/accessoires/metadescription","F");
$template->pageparams($host,$metatitle." - ".$template->translate("/site/nom"),$metadesc);

$template->evaltemplate("KdosPage","main");
$template->out();

?>