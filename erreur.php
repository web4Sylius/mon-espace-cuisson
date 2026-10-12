<?php
header("HTTP/1.0 404 Not Found");
include_once($_SERVER['DOCUMENT_ROOT'].'/global.php');

$template->pageparams($host, $template->translate('/erreur/title_page_introuvable'), $template->translate('/erreur/desc_page_introuvable'));

$template->parts['erreur_message'] = $template->translate('/erreur/page_introuvable');
$template->parts['retour_accueil'] = $template->translate('/commande/retour_accueil');

$log_texte = date("d/m/Y") . ' ||| ' . date("h:i:s") . ' ||| Script : ' . $_SERVER['REQUEST_URI'] . ' ||| Referer : ' . $_SERVER['HTTP_REFERER']; 
$f = fopen(SITE_PATH . "/log/404errors_" . date("Y-m-d") . ".txt","a");
fputs($f, $log_texte . "\r\n\r\n");
fclose($f);

include("barrefixe.php");
$template->evaltemplate("404","main");
$template->out();