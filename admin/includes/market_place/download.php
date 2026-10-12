<?php
header('Content-Description: File Transfer'); 
header('Content-Type: application/force-download'); 
header('Content-Disposition: attachment; filename="'.basename($_POST['page']).'"'); 
readfile("../..".$_POST['page']);
?>