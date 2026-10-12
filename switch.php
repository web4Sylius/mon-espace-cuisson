<?php
require ("global.php");

$testcat=new categorie($DB_site,0,$template->langue);

if(!$template->check_utf8($_GET['url'])) {
    $_GET['url'] = '';
}

$test=$testcat->verif_url($_GET['url']);

if($test==false){ 
    $new_url=$testcat->new_url($_GET['url']);
    if($new_url==false) $new_url="/";
    $template->redirect($new_url,301); 
    exit;
}


switch($test){
    case "categorie":
    include("categorie.php");
    break;
    
    case "promo": 
    include("promotions.php");
    break;

    case "idkdos":
    include("kdos.php");
    break;
    
    case "gamme":
    include("page_gamme.php");
    break;
    
    default:
	@mail ("contact@garantim.fr","Erreur redirection $host","[ ".date("j-m-Y H:i")." ]	[ ".$this->errdesc." ]		[ $host ]	[ Script: " . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . " ]	[ Referer: " . $_SERVER['HTTP_REFERER'] . " ]\n ".$_GET['url']." ","From: webmaster3@mon-espace-cuisson.com");
    $template->redirect("/",301);
}
?>