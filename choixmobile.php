<?php

include("global.php");

$template->parts["referer"]=$_SERVER['HTTP_REFERER'];
$template->out("ChoixMobile");

?>