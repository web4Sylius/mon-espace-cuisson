<?php
require "admin_global.php";
include "./header.php";
include "./menu.php";
$path = "../configurations/".$host."/images/bloc/";
$conseilid = $_GET['conseilid'];
$l = $_GET['lg'];
$typeid = $_GET['typeid'];

$typeBloc = $DB_site2->query_first("SELECT * FROM conseil_bloc_type WHERE cbtid='".$typeid"' ");
if($typeBloc['bloc_contenu'] == 'oui' && $typeBloc['bloc_image'] == 'oui'){}
	
echo "<a href=\"/admin/conseil_new.php?action=editer&conseilid=".$_GET['conseilid']."&site_code=base\">Retourner sur la page</a>";


?>
