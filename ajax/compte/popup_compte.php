<?php
require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

$template->evaltemplate("ComptePopup","popup_compte");
echo stripslashes($template->parts['popup_compte']);
?>