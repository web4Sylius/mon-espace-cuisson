<?php

require ("global.php");

$libelle="libelle".$template->langue;
$contenu="contenu".$template->langue;
$description="description".$template->langue;
$ref_title="ref_title".$template->langue;
$ref_description = "ref_description".$template->langue ;

$param=strpos($_SERVER['REQUEST_URI'],".php");
if($param!==false) $template->redirect("/",301);
if (isset($id) and $id!=""){

        $institutionnel = $DB_site->query_first(" SELECT $contenu,$libelle,$ref_title,$ref_description,institutionnelid, bandeau_defilant FROM institutionnel WHERE institutionnelid = '$id' ");
}

$template->parts["content"]=$institutionnel[$contenu];
$template->parts["fil_ariane"]=" &gt; <span typeof=\"v:Breadcrumb\">".$institutionnel[$libelle]."</span>";
$template->parts["title"]=$institutionnel[$libelle];

include("barrefixe.php");

$template->pageparams($host,$institutionnel[$ref_title],$institutionnel[$ref_description]);
$template->evaltemplate("InstituPage","main");

$template->parts["remarketing"]=google_remarketing('',"siteview",'');

$template->out();

?>