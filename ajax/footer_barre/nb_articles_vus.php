<?php
require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

$articles_vus = $main->articles_vus($s,6);
?>
<a href="javascript:void(0);"><i id="articlevusbarspan" class="fa fa-caret-up fa-lg"></i> <span class="nbartvus"><?php echo count($articles_vus); ?></span> <span><?php echo $template->translate('/footer/articles_vus','F'); ?></span></a>