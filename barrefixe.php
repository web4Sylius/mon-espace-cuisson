<?php

$template->evaltemplate("PopupComparer","barrefixe");
$template->evaltemplate("PopupConfig","barrefixe");
// $template->evaltemplate("BarreFixeRappelInst","barrefixe");
// $template->evaltemplate("BarreFixeMiniContact","barrefixe");
$articles_vus=$main->articles_vus($s,6);
$c=0;
if(count($articles_vus) > 0) $template->evaltemplate("BarreFixeArticlesVus","barrefixe");
$template->evaltemplate("BarreFixe","barrefixe");


?>