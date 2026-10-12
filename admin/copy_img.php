<?php
require "admin_global.php";

 
$new_options = $DB_site2->query("SELECT caractvalid FROM `caracteristiquevaleur` WHERE caractid = '110' ORDER BY position");
$options = $DB_site2->query("SELECT caractvalid FROM `caracteristiquevaleur` WHERE caractid = '71' ORDER BY position");
foreach($options as $option){
	$options_old[] = $option['caractvalid'];
}

$i=0;
foreach($new_options as $new_option){
	$source=$_SERVER['DOCUMENT_ROOT']."/configurations/".$host."/images/caracteristiquevaleur/".$options_old[$i].".jpg";
	$destination=$_SERVER['DOCUMENT_ROOT']."/configurations/".$host."/images/caracteristiquevaleur/".$new_option['caractvalid'].".jpg";
	if(file_exists($source)){
		copy($source,$destination);
	}
	$i++;
}

 
