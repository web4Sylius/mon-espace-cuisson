<?php

ini_set("display_errors","on");
include_once("host_config_admin.php");
include_once($rootpath."class/email.php");
include_once($rootpath."class/stock.class.php");

$DB_site=clone $DB_site2;

$stock=new stock($DB_site);
$h=date("h");
$txt="Valeur du stock au ".date("d/m/Y",time()-(($h+1)*3600))." : <b>".number_format($stock->value(),2,',','')."&euro; HT</b>";


$mail=new email();
$mail->expediteur="contact@mon-espace-cuisson.com";
$mail->expname="Mon Espace Cuisson";
$mail->destinataire="j3mexp@gmail.com";
$mail->to=array("webmaster4@ma-cave-a-vin.fr","pr@ma-cave-a-vin.fr");
$mail->subject="Valeur stock Mon Espace Cuisson";
$mail->body=$txt;

$mail->attachfile("https://www.mon-espace-cuisson.com/admin/export/export_stock.php?admin_username=9d3bb895f22bf0afa958d68c2a58ded7","Stock ma cave a vin ".date("Y-m-d",time()-7200).".csv","text/csv");

$mail->attachfile("https://www.mon-espace-cuisson.com/admin/export/export_stock_qte.php?admin_username=9d3bb895f22bf0afa958d68c2a58ded7","Stock ma cave a vin ".date("Y-m-d",time()-7200)." qte marque type.htm","text/html");

$dt1=new DateTime("first day of last month");
$dt2=new DateTime("last day of last month");
$mail->attachfile("https://www.mon-espace-cuisson.com/admin/export/export_sorties_stock.php?admin_username=9d3bb895f22bf0afa958d68c2a58ded7&du=".$dt1->format("d/m/Y")."&au=".$dt2->format("d/m/Y"),"Sorties stock ma cave a vin ".$dt1->format("Y-m").".csv","text/csv");


print $txt;
$mail->sendmail();
?>
