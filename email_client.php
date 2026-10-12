<?php

include("global.php");

include("class/email_client.php");

$template->changesite("base","FR");
$template->pro=true;

$vars=array("prenom"=>"Cyril","nom"=>"charvier","email"=>"email","pass"=>"mdp","facturid"=>"0000","code"=>"XXX","prixtotal"=>$template->price_format(1234.56));
$ec=new email_client($DB_site,$template,96,null,$vars);
print $ec->body;

?>
