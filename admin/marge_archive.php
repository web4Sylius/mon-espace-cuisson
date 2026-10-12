<?php

require "admin_global.php";

header('Content-Type: text/html; charset=utf-8');

// visualiser une archive
if (isset($action) and $action == "visu") {
    if (isset($marge_archiveid)) {
		$archive=$DB_site2->query_first("SELECT * FROM marge_archive WHERE marge_archiveid = '".$marge_archiveid."' ");
		if($archive[marge_archiveid]==$marge_archiveid){
			echo $archive[html];
		} else {
			echo "archive inexistante";
		}
	} else {
		echo "ID d'archive manquant";
	}
} elseif (isset($action) and $action == "delete") {
	if (isset($marge_archiveid)) {
		$archive=$DB_site2->query_first("SELECT * FROM marge_archive WHERE marge_archiveid = '".$marge_archiveid."' ");
		if($archive[marge_archiveid]==$marge_archiveid){
			$DB_site2->query("DELETE FROM marge_archive WHERE marge_archiveid = '".$archive[marge_archiveid]."' ");
			// echo "archive supprimée avec succès.";
			header("Location: marge_articles.php");
			exit;
		} else {
			echo "archive inexistante";
		}
	} else {
		echo "ID d'archive manquant";
	}
} else {
	echo "action manquante";
}


?>