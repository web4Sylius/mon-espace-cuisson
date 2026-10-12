<?php

ini_set("display_errors","on");

include_once("host_config_admin.php");
include_once($rootpath."class/email.php");

$site_url = 'https://www.mon-espace-cuisson.com';
$dossier_admin = '/admin';

$DB_site=clone $DB_site2;

$dayofweek=date("w");
$dayofmonth=intval(date("j"));
//$dayofweek=1;
if($dayofweek==1){
    $tm1s=time()-(86400*9);
    //$tm1s=1656670287;
    $yesterday=$tm1s+(86400*6);
    //$yesterday=time();

    $ducheque=time()-(86400*6);
    //$ducheque=time()-(86400*2);
    $aucheque=time();

    $mail=new email();
    $mail->expediteur="contact@mon-espace-cuisson.com";
    $mail->expname="Mon Espace Cuisson";
    $mail->destinataire="j3mexp@gmail.com";
    $mail->to=array("pr@mon-espace-cuisson.com","webmaster4@ma-cave-a-vin.fr");
    $mail->subject="Comptabilité Mon Espace Cuisson";
    $mail->body="";
    $mail->attachfile($site_url.$dossier_admin."/export/export_fournisseur.php?du=".date("d/m/Y",$tm1s)."&au=".date("d/m/Y",$yesterday)."&admin_username=9d3bb895f22bf0afa958d68c2a58ded7","acilyondev-fournisseurs-acilyon-".date("Ymd",$tm1s)."-au-".date("md",$yesterday).".csv","text/csv");
    $mail->attachfile($site_url.$dossier_admin."/export/export_fournisseur2.php?du=".date("d/m/Y",$tm1s)."&au=".date("d/m/Y",$yesterday)."&admin_username=9d3bb895f22bf0afa958d68c2a58ded7","acilyondev-fournisseurs-acilyondev-".date("Ymd",$tm1s)."-au-".date("md",$yesterday).".csv","text/csv");
    $mail->attachfile($site_url.$dossier_admin."/export/export_frais.php?du=".date("d/m/Y",$tm1s)."&au=".date("d/m/Y",$yesterday)."&admin_username=9d3bb895f22bf0afa958d68c2a58ded7","acilyondev-frais-".date("Ymd",$tm1s)."-au-".date("md",$yesterday).".csv","text/csv");
    $mail->attachfile($site_url.$dossier_admin."/export/export_compta2.php?du=".date("d/m/Y",$tm1s)."&au=".date("d/m/Y",$yesterday)."&admin_username=9d3bb895f22bf0afa958d68c2a58ded7","acilyondev-clients-".date("Ymd",$tm1s)."-au-".date("md",$yesterday).".csv","text/csv");
    $mail->attachfile($site_url.$dossier_admin."/export/export_cheque.php?du=".date("d/m/Y",$ducheque)."&au=".date("d/m/Y",$aucheque)."&admin_username=9d3bb895f22bf0afa958d68c2a58ded7","acilyondev-cheques-".date("Ymd",$ducheque)."-au-".date("md",$aucheque).".csv","text/csv");
    
    //cutoff    
    if(date("d")<=7){
    $mail->attachfile($site_url.$dossier_admin."/export/export_cutoff.php?du=1/1/".date("Y")."&au=".date("d/m/Y",$yesterday)."&admin_username=9d3bb895f22bf0afa958d68c2a58ded7","acilyondev-FNP-".date("Ym",$yesterday).".ods","application/vnd.oasis.opendocument.spreadsheet");
    }
    $mail->sendmail();
}

if($dayofweek==6){
    $tm1s=time()-(86400*7);
    //$tm1s=1667257201;
    $yesterday=$tm1s+(86400*6);
    //$yesterday=1669113074;

    $client=file_get_contents($site_url.$dossier_admin."/diag_compta.php?du=".date("d/m/Y",$tm1s)."&au=".date("d/m/Y",$yesterday)."&type=client&admin_username=9d3bb895f22bf0afa958d68c2a58ded7");
    $fourn=file_get_contents($site_url.$dossier_admin."/diag_compta.php?du=".date("d/m/Y",$tm1s)."&au=".date("d/m/Y",$yesterday)."&type=fourn&admin_username=9d3bb895f22bf0afa958d68c2a58ded7");
    
    $mail=new email();
    $mail->expediteur = "contact@mon-espace-cuisson.com";
    $mail->expname = "Mon Espace Cuisson";
    $mail->destinataire = "webmaster4@ma-cave-a-vin.fr";
    $mail->to = array("pr@mon-espace-cuisson.com","cecile@mon-espace-cuisson.com");
    $mail->subject = "Comptabilité Mon Espace Cuisson";
    $mail->body = date("D d/m/Y",$tm1s)." à ".date("D d/m/Y",$yesterday)."<p><b>Factures client :</b></p>$client<p><b>Factures fournisseur</b></p>$fourn";
    $mail->sendmail();
}



// Execution du script seulement le 1er du mois, remonte les données du mois precedent 
/*if(date("d")==1) // Si date = 22/02 mettre 22 pour tester
{
    $lastMonth = "".date("Ym") - 1; 
    $mail=new email();
    $mail->expediteur="contact@mon-espace-cuisson.com";
    $mail->expname="Mon Espace Cuisson";
    //$mail->destinataire="webmaster3@mon-espace-cuisson.com";
    $mail->destinataire="j3mexp@gmail.com";
    $mail->to=array("pr@mon-espace-cuisson.com");
    $mail->subject="Comptabilité Mon Espace Cuisson, facilités de paiement";
    $mail->body="";
    $mail->attachfile("https://www.mon-espace-cuisson.com/admin/export/export_facilite_paiement.php?"."admin_username=9d3bb895f22bf0afa958d68c2a58ded7","FacilitePaiement_".$lastMonth.".ods","application/vnd.oasis.opendocument.spreadsheet-template");
    $mail->sendmail();
}*/

/*
if(date("d")==1){
    $yesterday=time()-(86400*date("d"));
    $lastm1=mktime(0,0,0,date("n",$yesterday),1,date("Y",$yesterday));
    $lastmend=mktime(23,59,59,date("n",$yesterday),date("t",$yesterday),date("Y",$yesterday));

    $mail=new email();
    $mail->expediteur="contact@mon-espace-cuisson.com";
    $mail->expname="Ma Cave à Vin";
    $mail->destinataire="jm.coubes@acilyon.com";
    $mail->to=array("webmaster@mon-espace-cuisson.com","pr@mon-espace-cuisson.com");
    $mail->subject="Comptabilité Ma Cave à Vin";
    $mail->body="";
    $mail->attachfile("https://www.mon-espace-cuisson.com/admin/export/export_compta2.php?du=".date("d/m/Y",$lastm1)."&au=".date("d/m/Y",$lastmend)."&admin_username=9d3bb895f22bf0afa958d68c2a58ded7","factures_clients_".date("Y-m",$lastm1).".csv","text/csv");
    $mail->sendmail();

}*/



?>
