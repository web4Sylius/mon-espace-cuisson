<?php
require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

$template->evaltemplate("PopupPanier","popup_panier");
echo stripslashes($template->parts['popup_panier']);
?>