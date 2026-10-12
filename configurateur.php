<?php

// if(!isset($_COOKIE["admin_username"])) header("location:/");

include_once("global.php");
include_once("class/configurateur_display.php");
include_once("class/configurateur/complet.php");
include_once("class/configurateur/artevino.php");

$template->parts["js"]="<script language=\"javascript\" src=\"/javascripts/configurateur.js\"></script>";

if(isset($_GET['type'])) $template->parts["configtype"]=$_GET['type'];

if(isset($_GET['id'])){
    $confid=intval($_GET['id']);
    if($confid>0){
	$configurateur=new configurateur($DB_site,$confid);
	if($configurateur->session!=$s && !isset($_COOKIE['admin_username']))
	  unset($configurateur);
	else
	  $reload=true;
    }else{
	$configurateur=new configurateur($DB_site);
	$configurateur->clear();
    }
}

if(is_null($configurateur)){
    $configurateur=new configurateur($DB_site);
    $reload=$configurateur->loadcookie();
}

if($reload){
    $confid=$configurateur->configurateurid;
    $template->parts["js"].=<<<JS
    <script language="javascript">
    if (window.addEventListener){
      window.addEventListener('load', function(){ reload_config($confid); }, false); // NB **not** 'onload'
    }else if (window.attachEvent){
      window.attachEvent('onload', function(){ reload_config($confid); });
    }
    
    </script>
JS;
}

$h=new horaire($DB_site,$template->site_code());
$template->parts["contactstyle"]=($h->ouvert() ? "" : "style=\"display:none\"");

//ici on fais afficher les balise hreflang pour FR et EN
foreach($template->sites() as $site_code=>$site){
    foreach($site['hosts'] as $url=>$deflangue){
        if($site['actif'][$url])
            $template->parts["alternate"].="<link rel=\"alternate\" hreflang=\"".$site['locales'][$deflangue]."\" href=\"https://www.".$url."/configurateur.php\">\n";
    }
}

include("barrefixe.php");

$template->pageparams($host,"Configurateur - ".$template->translate("/site/nom", $template->langue),"");
$template->evaltemplate("ConfigPage","main");
$template->evaltemplate("RappelConfig","rappel_config");

$template->out();

?>