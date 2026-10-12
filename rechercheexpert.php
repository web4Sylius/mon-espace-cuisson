<?php

require ("global.php");
$libelle="libelle".$template->parts["langue"];

$template->parts["categoriecolor"]="#B01D4A";
$catids=array();


if ($action == "rechercher") {
    
        if ($rechercherapide != "" and $rechercherapide != "Recherche rapide"){
	    $rechercherapide=strip_tags(trim($rechercherapide));
	    header("HTTP/1.1 301 Moved Permanently");
	    header("location:/recherche-rapide.php?action=".($rechercherapide));
	    header("Connection: close");
	}
}else {
      header("HTTP/1.1 301 Moved Permanently");
      header("location:/");
      header("Connection: close");
}



?>